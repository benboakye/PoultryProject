# Database migrations

Requires MySQL >=8.0.16 and PHP pdo_mysql. Only an operator/CI migration identity performs schema changes; do not grant DDL rights to the application identity. The database must already exist. This runner does not create/drop an application database, perform a rollback, or initialize personal records.

1. Set DB_HOST, DB_PORT, DB_DATABASE and APP_ENV, plus explicit MIGRATION_DB_USERNAME and MIGRATION_DB_PASSWORD. Keep credentials outside Git. Staging/production also require DB_SSL_CA for server verification.
2. Run `php scripts/migrate.php --plan`. Review pending filenames. This read-only plan is not a SQL validation test.
3. Run the real MySQL integration suite on an isolated test server. Confirm backup/recovery and required deployment review for any environment containing data.
4. Run `php scripts/migrate.php --apply`. Re-running after success should apply zero migrations.
5. Save the change/validation evidence with the release. A local migration does not authorize production deployment.

## Failure and drift

Do not edit an applied migration, delete ledger history, or blindly mark a failed migration applied. A failed/interrupted DDL operation can leave schema changes behind. Inspect the failing filename and checksum/status in `schema_migrations` using migration credentials, inspect the actual schema, and compare it with the reviewed SQL. Retain the incident evidence. Restore/rebuild a disposable test database as appropriate, or prepare a reviewed production repair/recovery procedure before changing ledger state. The runner intentionally has no automatic repair command.

On lock contention, wait for the other migration process to finish; do not bypass the lock. Locks are scoped to the selected database and released when the connection closes. Application queries do not use this administrative lock.

## Integration tests

Use a disposable MySQL server with synthetic data. Set TEST_DB_HOST, TEST_DB_PORT, TEST_DB_USERNAME and TEST_DB_PASSWORD. The test account must be able to connect to `mysql` and create/drop databases named `poultrytrak_test_*`. Never point these tests at a live production server.

Run `composer check` with those values set. Each integration test generates its own `poultrytrak_test_<random hex>` database and drops only that generated database in teardown; it never accepts a database name to overwrite. SQL fixture copies live under ignored `build/`. If a test process is killed, a test database may remain; inspect its exact generated name before cleanup.

Without TEST_DB_HOST the database group is explicitly skipped. Skips are not evidence of passing integration tests. CI always sets the database variables and runs a MySQL service. `vendor/bin/phpunit --group database` selects only these tests.
