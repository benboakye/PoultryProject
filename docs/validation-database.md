# Database foundation validation

Date: 2026-09-06. Local runtime: PHP 8.4.25, MySQL Community 8.4.11, Composer 2.10.3, Windows.

- Composer strict validation, PSR-12 and maximum-level PHPStan: passed.
- PHPUnit with explicit TEST_DB_HOST and real MySQL: 39 tests, 87 assertions, no skipped tests. Includes 11 database integration tests plus connection-validation tests and the existing HTTP/configuration suite.
- Migration checks cover no-write planning, apply/replay, checksum modification, missing historical file, out-of-order insertion, line-ending normalization, failed-run refusal and contention between separate connections.
- Database checks cover region/district mismatch, missing-region rejection, grant foreign keys, native prepares, UTC/strict mode and rejection of multi-statement SQL.
- Separate CLI smoke check: plan left the disposable database empty; apply completed eight migrations; repeat applied zero; final plan listed eight applied entries.
- Composer lock refresh: no package version changes and no security vulnerability advisories reported.
- Test-created databases were dropped by teardown; the loopback-only temporary MySQL server was shut down after validation. No production or farmer data was used.

CI now provisions MySQL 8.0 and PHP 8.2/8.4. Those remote results are not claimed by this local evidence. Production TLS validation logic exists but has not been exercised against a production certificate/host. Authentication, live permission enforcement, scope repositories and audit remain pending; Stage 0 is not complete.
