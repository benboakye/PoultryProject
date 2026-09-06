<?php

declare(strict_types=1);

namespace PoultryTrak\Support\Database;

use PDO;
use PDOStatement;
use RuntimeException;
use Throwable;

final readonly class Migrator
{
    public function __construct(private PDO $pdo, private string $directory)
    {
    }

    /** @return list<array{name: string, checksum: string, sql: string, status: string}> */
    public function plan(): array
    {
        $files = glob($this->directory . '/*.sql');
        if ($files === false || $files === []) {
            throw new RuntimeException('Migration directory has no SQL files.');
        }
        sort($files, SORT_STRING);
        $rows = [];
        $exists = $this->query(
            "SELECT COUNT(*) FROM information_schema.tables "
            . "WHERE table_schema = DATABASE() AND table_name = 'schema_migrations'"
        )->fetchColumn();
        if ((int) $exists > 0) {
            $rows = $this->query('SELECT name, checksum, status FROM schema_migrations')->fetchAll();
        }
        $recorded = [];
        foreach ($rows as $row) {
            if (
                !is_array($row) || !is_string($row['name'] ?? null)
                || !is_string($row['checksum'] ?? null) || !is_string($row['status'] ?? null)
            ) {
                throw new RuntimeException('Invalid migration ledger.');
            }
            $recorded[$row['name']] = ['checksum' => $row['checksum'], 'status' => $row['status']];
        }
        $plan = [];
        $pendingSeen = false;
        foreach ($files as $file) {
            $name = basename($file);
            if (preg_match('/\A[0-9]{4}_[a-z0-9_]+\.sql\z/', $name) !== 1) {
                throw new RuntimeException('Invalid migration filename.');
            }
            $sql = file_get_contents($file);
            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException('Migration file is empty or unreadable.');
            }
            // Make Git line-ending conversion immaterial to migration identity.
            $sql = str_replace("\r\n", "\n", $sql);
            $checksum = hash('sha256', $sql);
            $record = $recorded[$name] ?? null;
            if ($record !== null) {
                if ($record['checksum'] !== $checksum || $record['status'] !== 'applied' || $pendingSeen) {
                    throw new RuntimeException('Migration drift, incomplete run or out-of-order migration: ' . $name);
                }
                unset($recorded[$name]);
            } else {
                $pendingSeen = true;
            }
            $plan[] = ['name' => $name, 'checksum' => $checksum, 'sql' => $sql,
                'status' => $record === null ? 'pending' : 'applied'];
        }
        if ($recorded !== []) {
            throw new RuntimeException('Previously recorded migration files are missing.');
        }
        return $plan;
    }

    /** @return list<string> */
    public function migrate(): array
    {
        // Lock is connection-scoped: DDL implicit commits cannot release it.
        $database = $this->query('SELECT DATABASE()')->fetchColumn();
        if (!is_string($database) || $database === '') {
            throw new RuntimeException('A database must be selected.');
        }
        $lock = 'pt_migrate_' . substr(hash('sha256', $database), 0, 40);
        $statement = $this->pdo->prepare('SELECT GET_LOCK(:name, 0)');
        $statement->execute(['name' => $lock]);
        if ((int) $statement->fetchColumn() !== 1) {
            throw new RuntimeException('Another migration process holds the database lock.');
        }
        try {
            $plan = $this->plan();
            $this->pdo->exec(
                "CREATE TABLE IF NOT EXISTS schema_migrations ("
                . "name VARCHAR(190) PRIMARY KEY, checksum CHAR(64) NOT NULL, "
                . "status ENUM('applying','applied','failed') NOT NULL, "
                . "started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, applied_at TIMESTAMP NULL"
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci'
            );
            $applied = [];
            foreach ($plan as $migration) {
                if ($migration['status'] === 'applied') {
                    continue;
                }
                $insert = $this->pdo->prepare(
                    "INSERT INTO schema_migrations (name, checksum, status) VALUES (:name, :checksum, 'applying')"
                );
                $insert->execute(['name' => $migration['name'], 'checksum' => $migration['checksum']]);
                try {
                    // One statement per file. Multi-statement execution is disabled by the connection.
                    $this->pdo->exec($migration['sql']);
                    $update = $this->pdo->prepare(
                        "UPDATE schema_migrations SET status = 'applied', applied_at = CURRENT_TIMESTAMP "
                        . 'WHERE name = :name'
                    );
                    $update->execute(['name' => $migration['name']]);
                } catch (Throwable $exception) {
                    $update = $this->pdo->prepare("UPDATE schema_migrations SET status = 'failed' WHERE name = :name");
                    $update->execute(['name' => $migration['name']]);
                    throw new RuntimeException('Migration failed; inspect and repair before retrying.', 0, $exception);
                }
                $applied[] = $migration['name'];
            }
            return $applied;
        } finally {
            $release = $this->pdo->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => $lock]);
        }
    }

    private function query(string $sql): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $statement;
    }
}
