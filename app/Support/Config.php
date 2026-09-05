<?php

declare(strict_types=1);

namespace PoultryTrak\Support;

use InvalidArgumentException;

final readonly class Config
{
    private function __construct(public string $environment, public string $url, public bool $debug)
    {
    }

    /** @param array<string, string> $values */
    public static function fromArray(array $values): self
    {
        $environment = $values['APP_ENV'] ?? 'production';
        if (!in_array($environment, ['local', 'testing', 'staging', 'production'], true)) {
            throw new InvalidArgumentException('APP_ENV is invalid.');
        }

        $url = $values['APP_URL'] ?? '';
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (
            filter_var($url, FILTER_VALIDATE_URL) === false
            || !in_array($scheme, ['http', 'https'], true)
            || parse_url($url, PHP_URL_USER) !== null
            || parse_url($url, PHP_URL_PASS) !== null
            || parse_url($url, PHP_URL_QUERY) !== null
            || parse_url($url, PHP_URL_FRAGMENT) !== null
        ) {
            throw new InvalidArgumentException(
                'APP_URL must be an HTTP(S) URL without credentials, query or fragment.'
            );
        }

        $rawDebug = $values['APP_DEBUG'] ?? 'false';
        if (!in_array($rawDebug, ['true', 'false'], true)) {
            throw new InvalidArgumentException('APP_DEBUG must be true or false.');
        }
        $debug = $rawDebug === 'true';
        if (in_array($environment, ['staging', 'production'], true) && ($debug || $scheme !== 'https')) {
            throw new InvalidArgumentException('Staging and production require HTTPS and disabled debug.');
        }

        return new self($environment, rtrim($url, '/'), $debug);
    }
}
