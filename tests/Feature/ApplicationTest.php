<?php

declare(strict_types=1);

namespace PoultryTrak\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PoultryTrak\Support\Config;
use PoultryTrak\Support\Http\Application;

final class ApplicationTest extends TestCase
{
    private function application(string $environment = 'testing'): Application
    {
        return new Application(Config::fromArray([
            'APP_ENV' => $environment,
            'APP_URL' => 'https://example.test',
        ]));
    }

    public function testLivenessDoesNotClaimDatabaseOrBusinessReadiness(): void
    {
        $response = $this->application()->handle('GET', '/health/live?probe=1');
        self::assertSame(200, $response->status);
        self::assertSame(['status' => 'live'], json_decode($response->body, true, flags: JSON_THROW_ON_ERROR));
        self::assertSame('no-store', $response->headers['Cache-Control']);
    }

    #[DataProvider('privatePaths')]
    public function testPrivateAndUnimplementedPathsCannotBeServed(string $path): void
    {
        $response = $this->application()->handle('GET', $path);
        self::assertSame(404, $response->status);
        self::assertSame('application/problem+json', $response->headers['Content-Type']);
        self::assertStringNotContainsString($path, $response->body);
    }

    /** @return iterable<array{string}> */
    public static function privatePaths(): iterable
    {
        foreach (['/.env', '/composer.json', '/storage/test.pdf', '/api/v1/farmers', '/%2e%2e/.env'] as $path) {
            yield [$path];
        }
    }

    public function testUnsupportedMethodCannotInvokeAGetHandler(): void
    {
        $response = $this->application()->handle('POST', '/health/live');
        self::assertSame(405, $response->status);
        self::assertSame('GET, HEAD', $response->headers['Allow']);
    }

    public function testRequestIdsAreUniqueAndErrorEnvelopeMatchesHeader(): void
    {
        $first = $this->application()->handle('GET', '/missing');
        $second = $this->application()->handle('GET', '/missing');
        $error = json_decode($first->body, true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($first->headers['X-Request-ID'], $error['request_id']);
        self::assertNotSame($first->headers['X-Request-ID'], $second->headers['X-Request-ID']);
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $first->headers['X-Request-ID']);
    }

    public function testProductionSecurityHeadersApplyToErrorsToo(): void
    {
        $response = $this->application('production')->handle('GET', '/missing');
        self::assertSame('DENY', $response->headers['X-Frame-Options']);
        self::assertSame('nosniff', $response->headers['X-Content-Type-Options']);
        self::assertStringContainsString('preload', $response->headers['Strict-Transport-Security']);
        self::assertStringContainsString("frame-ancestors 'none'", $response->headers['Content-Security-Policy']);
    }

    public function testHeadHasGetHeadersButSendsNoBody(): void
    {
        $response = $this->application()->handle('HEAD', '/health/live');
        self::assertSame(200, $response->status);
        ob_start();
        $response->send(head: true);
        self::assertSame('', ob_get_clean());
    }
}
