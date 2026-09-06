<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use PoultryTrak\Support\Database\ConnectionFactory;
use PoultryTrak\Support\Database\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$mode = $argv[1] ?? '';
if (count($argv) !== 2 || !in_array($mode, ['--plan', '--apply'], true)) {
    fwrite(STDERR, "Usage: php scripts/migrate.php --plan|--apply\n");
    exit(2);
}
try {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
    $values = [];
    foreach (
        ['APP_ENV', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_SSL_CA',
        'MIGRATION_DB_USERNAME', 'MIGRATION_DB_PASSWORD'] as $key
    ) {
        $process = getenv($key);
        $value = $process !== false ? $process : ($_ENV[$key] ?? $_SERVER[$key] ?? null);
        if (is_string($value)) {
            $values[$key] = $value;
        }
    }
    // Never silently use application credentials for DDL.
    $values['DB_USERNAME'] = $values['MIGRATION_DB_USERNAME'] ?? '';
    $values['DB_PASSWORD'] = $values['MIGRATION_DB_PASSWORD'] ?? '';
    $migrator = new Migrator(ConnectionFactory::connect($values), dirname(__DIR__) . '/database/migrations');
    if ($mode === '--plan') {
        foreach ($migrator->plan() as $migration) {
            echo $migration['status'] . ' ' . $migration['name'] . PHP_EOL;
        }
    } else {
        $names = $migrator->migrate();
        foreach ($names as $name) {
            echo 'applied ' . $name . PHP_EOL;
        }
        echo count($names) . " migration(s) applied.\n";
    }
} catch (Throwable $exception) {
    // Connection/SQL exceptions may include identifiers or credentials; omit them.
    fwrite(STDERR, "Migration command failed. Check configuration, connectivity and the migration ledger.\n");
    exit(1);
}
