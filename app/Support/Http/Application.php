<?php

declare(strict_types=1);

namespace PoultryTrak\Support\Http;

use PoultryTrak\Support\Config;

final readonly class Application
{
    public function __construct(private Config $config)
    {
    }

    public function handle(string $method, string $uri): Response
    {
        // Always generate locally: arbitrary client correlation IDs are not trusted.
        $requestId = bin2hex(random_bytes(16));
        $headers = [
            'X-Request-ID' => $requestId,
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'no-referrer',
            'Permissions-Policy' => 'camera=(self), geolocation=(self), microphone=()',
            'Content-Security-Policy' => "default-src 'self'; frame-ancestors 'none'; "
                . "base-uri 'self'; form-action 'self'",
        ];
        if (in_array($this->config->environment, ['staging', 'production'], true)) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        $path = explode('?', $uri, 2)[0];
        if (!in_array($path, ['/', '/health/live'], true)) {
            return $this->problem(404, 'Not Found', 'Route not found.', $requestId, $headers);
        }
        if (!in_array($method, ['GET', 'HEAD'], true)) {
            $headers['Allow'] = 'GET, HEAD';
            return $this->problem(405, 'Method Not Allowed', 'Use GET or HEAD.', $requestId, $headers);
        }

        if ($path === '/health/live') {
            return new Response(200, '{"status":"live"}', $headers + ['Content-Type' => 'application/json']);
        }

        // Temporary foundation page; the Carbon UI is delivered in a separate slice.
        $body = '<!doctype html><html lang="en"><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>PoultryTrak</title><body><a href="#main">Skip to content</a>'
            . '<main id="main"><h1>PoultryTrak</h1>'
            . '<p>National Poultry Registration, Input Subsidy &amp; Traceability System</p>'
            . '<p>Development is underway. Registration and verification are not yet available.</p>'
            . '</main></body></html>';
        return new Response(200, $body, $headers + ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /** @param array<string, string> $headers */
    private function problem(int $status, string $title, string $detail, string $requestId, array $headers): Response
    {
        $body = json_encode([
            'type' => 'about:blank',
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'request_id' => $requestId,
        ], JSON_THROW_ON_ERROR);
        return new Response($status, $body, $headers + ['Content-Type' => 'application/problem+json']);
    }
}
