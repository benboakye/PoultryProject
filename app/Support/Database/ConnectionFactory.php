<?php

declare(strict_types=1);

namespace PoultryTrak\Support\Database;

use InvalidArgumentException;
use PDO;
use RuntimeException;

final class ConnectionFactory
{
    /** @param array<string, string> $values */
    public static function connect(array $values): PDO
    {
        $host = $values['DB_HOST'] ?? '127.0.0.1';
        $database = $values['DB_DATABASE'] ?? '';
        $port = $values['DB_PORT'] ?? '3306';
        $username = $values['DB_USERNAME'] ?? '';
        if (
            preg_match('/\A[a-zA-Z0-9.:-]+\z/', $host) !== 1
            || preg_match('/\A[a-zA-Z][a-zA-Z0-9_]{0,63}\z/', $database) !== 1
            || !ctype_digit($port) || (int) $port < 1 || (int) $port > 65535
            || $username === ''
        ) {
            throw new InvalidArgumentException('Invalid database connection settings.');
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        ];
        $ca = $values['DB_SSL_CA'] ?? '';
        $environment = $values['APP_ENV'] ?? 'production';
        if (!in_array($environment, ['local', 'testing'], true) && $ca === '') {
            throw new InvalidArgumentException('Staging and production require a database TLS CA.');
        }
        if ($ca !== '') {
            if (!is_file($ca) || !is_readable($ca)) {
                throw new InvalidArgumentException('Database TLS CA is not readable.');
            }
            $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
            $username,
            $values['DB_PASSWORD'] ?? '',
            $options
        );
        $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        if (!is_string($version) || str_contains($version, 'MariaDB') || version_compare($version, '8.0.16', '<')) {
            throw new RuntimeException('MySQL 8.0.16 or newer is required for enforced CHECK constraints.');
        }
        $pdo->exec("SET SESSION time_zone = '+00:00'");
        $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
        if ($ca !== '') {
            $statement = $pdo->prepare("SHOW SESSION STATUS LIKE 'Ssl_cipher'");
            $statement->execute();
            $cipher = $statement->fetch();
            if (!is_array($cipher) || ($cipher['Value'] ?? '') === '') {
                throw new RuntimeException('Database TLS was required but not negotiated.');
            }
        }
        return $pdo;
    }
}
