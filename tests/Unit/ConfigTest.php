<?php

declare(strict_types=1);

namespace PoultryTrak\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PoultryTrak\Support\Config;

final class ConfigTest extends TestCase
{
    public function testProductionIsTheDefaultAndDebugIsOff(): void
    {
        $config = Config::fromArray(['APP_URL' => 'https://example.test/']);
        self::assertSame('production', $config->environment);
        self::assertFalse($config->debug);
        self::assertSame('https://example.test', $config->url);
    }

    /** @param array<string, string> $values */
    #[DataProvider('invalidSettings')]
    public function testUnsafeOrAmbiguousSettingsAreRejected(array $values): void
    {
        $this->expectException(InvalidArgumentException::class);
        Config::fromArray($values);
    }

    /** @return iterable<string, array{array<string, string>}> */
    public static function invalidSettings(): iterable
    {
        yield 'missing URL' => [[]];
        yield 'unknown environment' => [['APP_ENV' => 'prod', 'APP_URL' => 'https://example.test']];
        yield 'production HTTP' => [['APP_URL' => 'http://example.test']];
        yield 'production debug' => [['APP_URL' => 'https://example.test', 'APP_DEBUG' => 'true']];
        yield 'staging HTTP' => [['APP_ENV' => 'staging', 'APP_URL' => 'http://example.test']];
        yield 'ambiguous boolean' => [['APP_URL' => 'https://example.test', 'APP_DEBUG' => 'yes']];
        yield 'URL credentials' => [['APP_URL' => 'https://user:secret@example.test']];
        yield 'URL query' => [['APP_URL' => 'https://example.test?secret=value']];
        yield 'non-HTTP scheme' => [['APP_URL' => 'ftp://example.test']];
    }

    public function testLocalHttpIsSupported(): void
    {
        $config = Config::fromArray(['APP_ENV' => 'local', 'APP_URL' => 'http://127.0.0.1:8080']);
        self::assertSame('local', $config->environment);
    }
}
