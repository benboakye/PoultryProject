# Foundation slice validation

Date: 2026-09-05. Runtime: PHP 8.4.25, Composer 2.10.3 on Windows.

- `composer validate --strict`: passed.
- `composer check`: passed PSR-12, PHPStan maximum level and PHPUnit (21 tests, 42 assertions).
- `composer audit --locked`: no security vulnerability advisories found at the time of the check.
- Local HTTP smoke checks: GET home and liveness 200; HEAD liveness 200 with no body; POST liveness 405; private `.env` and `composer.json` paths 404.
- With process environment overriding local `.env`, unsafe production HTTP configuration returns sanitized 503, without configuration/exception details in the response.
- `git diff --check`: passed.

Limitations: PHP 8.2 execution is configured in CI but was not run locally. GitHub CI has not run yet. No MySQL/database tests, domain coverage claim, Carbon visual/accessibility audit or Stage 0 acceptance claim applies to this slice. The configured CI is an initial subset; migration dry-run, database integration and later release checks will be added when those capabilities exist.
