# ADR: Forward-only migrations and initial identity schema

Date: 2026-09-06. Status: implemented for the database foundation slice.
References: specification 4.1, 6.2, 14.2, 14.6, 17.5, 21.1, 22.4; requirements R-02, R-03, R-21 and R-26; decision D-06.

Use MySQL 8.0.16 or newer, because CHECK constraints must actually be enforced. PDO uses native prepared statements, exception handling, disabled multi-statement execution, utf8mb4, strict mode and UTC. Staging/production connections require a readable CA file, certificate verification and a negotiated TLS cipher. This does not implement production credentials or key management.

Migration files contain one SQL statement each, with sortable numeric filenames and SHA-256 checksums normalized to LF. The runner acquires a connection-scoped MySQL advisory lock before checking/applying migrations. A durable ledger marks applying, applied or failed. MySQL DDL can implicitly commit, so there is no promise of transactional rollback across a migration batch. Failed/interrupted runs block further migration until inspected. Changed/missing historical files and inserted older migrations are rejected.

`--plan` only reads files/schema metadata; it neither creates the ledger nor validates SQL by execution. Real-MySQL integration tests provide the migration dry-run by applying migrations to isolated disposable databases. Forward changes use new files. There is no automatic down/reset operation.

Initial schema: regions, districts, users, roles, user_roles, permissions and role_permissions. Region/district composite foreign keys and a CHECK prevent inconsistent user geography, including the nullable-composite-FK escape when region is omitted. Role grants and permission mappings have foreign keys. Grantor history is retained by restricting deletion rather than cascading through identity grants.

The section 14 role scope enum omits farm and facility even though section 4 requires them for workers and processors; add both explicitly. Seed only the ten authenticated role definitions from section 4.1. Public verification is unauthenticated, so there is no public login role. Default veterinary scope is district; authorized regional assignments must be designed in the authorization slice. No role gets permission grants yet, and this catalogue alone does not enforce authorization.

Do not fabricate an official Ghana region/district catalogue. These tables remain empty until an authoritative versioned dataset is selected; test fixtures use unmistakably synthetic geography only. Farmer/farm tables, pending-registration ID handling, audit, sessions and application authorization remain subsequent work. D-06 is partially addressed, not closed.

Source: [MySQL implicit commits](https://dev.mysql.com/doc/refman/8.4/en/implicit-commit.html), [MySQL locking functions](https://dev.mysql.com/doc/refman/8.4/en/locking-functions.html).
