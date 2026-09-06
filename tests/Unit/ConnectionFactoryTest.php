<?php

declare(strict_types=1);

namespace PoultryTrak\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PoultryTrak\Support\Database\ConnectionFactory;

final class ConnectionFactoryTest extends TestCase
{
    /** @param array<string, string> $overrides */
    #[DataProvider('invalidSettings')]
    public function testInvalidSettingsFailBeforeConnecting(array $overrides): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConnectionFactory::connect($overrides + [
            'APP_ENV' => 'testing', 'DB_HOST' => '127.0.0.1', 'DB_DATABASE' => 'poultrytrak', 'DB_USERNAME' => 'pt_app',
        ]);
    }

    /** @return iterable<string, array{array<string, string>}> */
    public static function invalidSettings(): iterable
    {
        yield 'host DSN injection' => [['DB_HOST' => 'localhost;dbname=other']];
        yield 'database DSN injection' => [['DB_DATABASE' => 'poultrytrak;host=other']];
        yield 'invalid port' => [['DB_PORT' => '65536']];
        yield 'missing user' => [['DB_USERNAME' => '']];
        yield 'production missing TLS' => [['APP_ENV' => 'production']];
        yield 'staging missing TLS' => [['APP_ENV' => 'staging']];
        yield 'unreadable CA' => [['DB_SSL_CA' => '/nonexistent/poultrytrak-ca.pem']];
    }
}
