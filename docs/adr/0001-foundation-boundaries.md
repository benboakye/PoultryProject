# ADR: Initial application boundaries

Date: 2026-09-05. Status: implemented for the skeleton; later capabilities remain pending.

Source: sections 6, 7, 15, 17, 21; R-02, R-21, R-23.

Use PHP 8.2-compatible code with Composer PSR-4 autoloading. The public front controller delegates to application code outside the web root. Do not introduce a full-stack framework, SPA, database access in views or browser build requirement.

The initial slice contains configuration and HTTP handling only. `/health/live` means the process and configuration loaded successfully; it makes no assertion about MySQL, queues, external integrations or business readiness. There is no readiness endpoint until the required dependencies exist. The home page explicitly says registration and verification are unavailable.

Default to production settings, require an explicit APP_URL and reject HTTP/debug in staging and production. Errors sent to clients omit exception text. Server logs contain only an event, generated correlation ID and exception class at this stage. Do not accept untrusted client request IDs.

API route decision D-10: reserve `/api/v1` internally on the application host. A later Nginx configuration can map the specification's API-host `/v1` to that prefix; both must use the same handlers and OpenAPI contract. No business API routes are implemented here.

Section 7.2 dependencies are installed when first used, with compatibility and vulnerability checks. This slice uses phpdotenv plus PHPUnit, PHPCS and PHPStan. Composer's PHP 8.2 platform baseline prevents dependency resolution from silently raising the language floor; CI checks both 8.2 and 8.4. The proprietary package metadata avoids granting a licence before the ownership decision; it is not a determination of legal ownership.

Self-hosted Carbon/Plex, database migrations, authentication, RBAC, audit and jobs are subsequent slices. The temporary home page is not the completed Carbon shell. Stage 0 is not complete.
