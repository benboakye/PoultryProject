# Local development

Requires PHP 8.2+ and Composer 2. PHP extensions for this slice: JSON; development tools also require DOM, XML, XMLWriter and mbstring. OpenSSL and ZIP support dependency installation. Later database work requires MySQL 8.0+ and pdo_mysql; SQLite will not substitute for database integration tests.

```sh
composer install
cp .env.example .env
composer check
composer serve
```

PowerShell users can copy configuration with `Copy-Item .env.example .env`. Open `http://127.0.0.1:8080`. Use `GET /health/live` for liveness only. The PHP development server binds to loopback and routes all paths through the front controller; it is not a production server. Stop it with Ctrl+C.

For production, use Nginx/PHP-FPM and expose only `public/`. Do not serve the repository root, private storage, configuration or vendor directory. The production deployment configuration is still pending. Runtime environment values override `.env` values. Never commit `.env`, credentials, keys, real farmer data or private uploads.

Checks: `composer validate --strict`, `composer check` (PSR-12, maximum-level PHPStan and PHPUnit), and `composer audit --locked`. Commit composer.lock for reproducibility. CI is configured for PHP 8.2/8.4; a local run does not imply GitHub CI has run.

## Current slice

Implemented: Composer skeleton, validated application configuration, public front controller, temporary home page, liveness endpoint, method/route error envelopes, security headers, correlation IDs and sanitized failure response.

Not implemented: database/schema, migrations, authentication, permissions, audit ledger, Carbon assets, domain modules or production infrastructure. Consequently no section 23.2 business regression scenario or field milestone is marked passed.

Next slice: reviewed MySQL migrations and reference/identity schema, migration dry-run and real database integration harness; then authentication, scope enforcement and append-only audit.

Runtime provenance for the initial local check: [official PHP Windows downloads](https://www.php.net/downloads.php?os=windows&version=8.4) and [official Composer downloads](https://getcomposer.org/download/). Runtime binaries stay outside the repository.
