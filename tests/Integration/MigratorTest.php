<?php

declare(strict_types=1);

namespace PoultryTrak\Tests\Integration;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use PoultryTrak\Support\Database\ConnectionFactory;
use PoultryTrak\Support\Database\Migrator;
use RuntimeException;

#[Group('database')]
final class MigratorTest extends TestCase
{
    private ?PDO $admin = null;
    private ?PDO $pdo = null;
    private string $database = '';
    private string $directory = '';
    /** @var array<string, string> */
    private array $values = [];

    protected function setUp(): void
    {
        if (getenv('TEST_DB_HOST') === false) {
            self::markTestSkipped('Set TEST_DB_HOST to explicitly enable isolated real-MySQL tests.');
        }
        $this->values = [
            'APP_ENV' => 'testing',
            'DB_HOST' => (string) getenv('TEST_DB_HOST'),
            'DB_PORT' => (string) (getenv('TEST_DB_PORT') ?: '3306'),
            'DB_USERNAME' => (string) getenv('TEST_DB_USERNAME'),
            'DB_PASSWORD' => (string) getenv('TEST_DB_PASSWORD'),
            'DB_DATABASE' => 'mysql',
        ];
        $this->admin = ConnectionFactory::connect($this->values);
        // This test owns only this newly generated database, never a supplied DB name.
        $this->database = 'poultrytrak_test_' . bin2hex(random_bytes(8));
        $this->admin->exec('CREATE DATABASE `' . $this->database . '` CHARACTER SET utf8mb4');
        $this->values['DB_DATABASE'] = $this->database;
        $this->pdo = ConnectionFactory::connect($this->values);
        $this->directory = dirname(__DIR__, 2) . '/build/' . $this->database;
        mkdir($this->directory, 0777, true);
        foreach (glob(dirname(__DIR__, 2) . '/database/migrations/*.sql') as $file) {
            copy($file, $this->directory . '/' . basename($file));
        }
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
        if ($this->admin !== null && preg_match('/\Apoultrytrak_test_[a-f0-9]{16}\z/', $this->database) === 1) {
            $this->admin->exec('DROP DATABASE `' . $this->database . '`');
        }
        if ($this->directory !== '' && is_dir($this->directory)) {
            foreach (glob($this->directory . '/*.sql') as $file) {
                unlink($file);
            }
            rmdir($this->directory);
        }
    }

    private function migrator(): Migrator
    {
        self::assertInstanceOf(PDO::class, $this->pdo);
        return new Migrator($this->pdo, $this->directory);
    }

    public function testPlanMakesNoSchemaChangesAndApplyIsRepeatable(): void
    {
        self::assertCount(8, $this->migrator()->plan());
        self::assertSame([], $this->pdo->query('SHOW TABLES')->fetchAll());
        self::assertCount(8, $this->migrator()->migrate());
        self::assertSame([], $this->migrator()->migrate());
        self::assertSame(10, (int) $this->pdo->query('SELECT COUNT(*) FROM roles')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn());
        self::assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM role_permissions')->fetchColumn());
    }

    public function testChangedAppliedMigrationIsRejected(): void
    {
        $this->migrator()->migrate();
        file_put_contents($this->directory . '/0001_regions.sql', "\n-- changed", FILE_APPEND);
        $this->expectException(RuntimeException::class);
        $this->migrator()->migrate();
    }

    public function testMissingAppliedMigrationIsRejected(): void
    {
        $this->migrator()->migrate();
        unlink($this->directory . '/0001_regions.sql');
        $this->expectException(RuntimeException::class);
        $this->migrator()->plan();
    }

    public function testLateInsertionBeforeAppliedMigrationIsRejected(): void
    {
        $this->migrator()->migrate();
        file_put_contents($this->directory . '/0000_late.sql', 'CREATE TABLE late (id INT);');
        $this->expectException(RuntimeException::class);
        $this->migrator()->migrate();
    }

    public function testLineEndingConversionDoesNotChangeChecksum(): void
    {
        $this->migrator()->migrate();
        $file = $this->directory . '/0001_regions.sql';
        $sql = str_replace("\r\n", "\n", file_get_contents($file));
        file_put_contents($file, str_replace("\n", "\r\n", $sql));
        self::assertSame([], $this->migrator()->migrate());
    }

    public function testFailureIsRecordedAndCannotBeAutomaticallyRetried(): void
    {
        file_put_contents($this->directory . '/0009_failure.sql', 'CREATE TABLE roles (id INT);');
        try {
            $this->migrator()->migrate();
            self::fail('Expected failing DDL.');
        } catch (RuntimeException) {
            self::assertSame('failed', $this->pdo->query(
                "SELECT status FROM schema_migrations WHERE name = '0009_failure.sql'"
            )->fetchColumn());
        }
        $this->expectException(RuntimeException::class);
        $this->migrator()->migrate();
    }

    public function testSecondConnectionCannotMigrateWhileLockIsHeld(): void
    {
        $lock = 'pt_migrate_' . substr(hash('sha256', $this->database), 0, 40);
        $statement = $this->pdo->prepare('SELECT GET_LOCK(:name, 0)');
        $statement->execute(['name' => $lock]);
        self::assertSame(1, (int) $statement->fetchColumn());
        try {
            $other = ConnectionFactory::connect($this->values);
            $this->expectException(RuntimeException::class);
            (new Migrator($other, $this->directory))->migrate();
        } finally {
            $release = $this->pdo->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => $lock]);
        }
    }

    public function testDistrictRegionMismatchIsBlockedInDatabase(): void
    {
        $this->migrator()->migrate();
        $this->pdo->exec("INSERT INTO regions (id,code,name) VALUES (1,'ZZ','Synthetic A'),(2,'ZY','Synthetic B')");
        $this->pdo->exec("INSERT INTO districts (id,region_id,code,name) VALUES (1,1,'TST','Synthetic district')");
        $this->expectException(PDOException::class);
        $this->pdo->exec("INSERT INTO users (uuid,full_name,msisdn,region_id,district_id) "
            . "VALUES ('synthetic-uuid','Synthetic officer','+233000000001',2,1)");
    }

    public function testDistrictCannotBeSetWithoutRegion(): void
    {
        $this->migrator()->migrate();
        $this->expectException(PDOException::class);
        $this->pdo->exec("INSERT INTO users (uuid,full_name,msisdn,district_id) "
            . "VALUES ('synthetic-uuid','Synthetic officer','+233000000001',1)");
    }

    public function testRoleGrantCannotReferenceMissingUser(): void
    {
        $this->migrator()->migrate();
        $this->expectException(PDOException::class);
        $this->pdo->exec('INSERT INTO user_roles (user_id,role_id) VALUES (99999,1)');
    }

    public function testConnectionUsesNativePreparesUtcAndStrictMode(): void
    {
        self::assertFalse($this->pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES));
        self::assertSame('+00:00', $this->pdo->query('SELECT @@session.time_zone')->fetchColumn());
        self::assertStringContainsString('STRICT_TRANS_TABLES', $this->pdo->query('SELECT @@sql_mode')->fetchColumn());
        $this->expectException(PDOException::class);
        $this->pdo->exec('CREATE TABLE one_statement (id INT); CREATE TABLE forbidden_second (id INT);');
    }
}
