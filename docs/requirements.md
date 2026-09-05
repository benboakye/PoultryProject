# Requirements coverage register

Source: `reference/PoultryTrak-Project-Documentation-v1.0.pdf` and the supplied architecture PNG. This register is a coverage index; detailed source rules remain applicable even when summarized here. Every row is **pending implementation**. Add code/test paths and verification evidence when work is completed; a passing test does not establish a field pilot outcome.

| ID | Source | Required coverage | Stage |
|---|---|---|---|
| R-01 | 3-5, 25-26 | Registry/entitlement/traceability scope, all role populations, non-goals, pilot milestones, risks and measured success metrics | All |
| R-02 | 6-7, 21; diagram | PHP 8.2+, MySQL 8+, Nginx/PHP-FPM, MVC/vanilla Carbon v10.58, scoped PDO, private storage, Redis/session fallback, DB queue, replica, adapters and shared services | 0 |
| R-03 | 4, 17.2-17.3 | All roles and self/farm/outlet/facility/district/region/national boundaries; no unscoped access; no registrar/verifier/approver conflicts or dealer issuer/admin redeemer overlap | 0-2 |
| R-04 | 14.5, 17.6 | Attributed append-only audit, before/after/reason/request/device, SHA-256 chain, INSERT/SELECT-only application grants, nightly critical alerts, seven-year retention | 0 onward |
| R-05 | 8.2, 24.2 | Application/contact OTP, identity encryption/deduplication/attestation fallback, consent/public display name and correction requests | 1 |
| R-06 | 8.2-8.4 | GPS <=25 m, optional polygon, >=2 hashed photos, 50 m duplicate review, exclusions, >3 farms regional approval, independent approval | 1 |
| R-07 | 8.3-8.5 | Permanent ID/check symbol, QR card, issuance geography preserved on transfer, all six states, 24-month and trigger-based reverification, reasoned/escalated changes | 1 |
| R-08 | 9.6 | Programmes/seasons/input classes, hierarchy caps and minimum-of-four entitlement formula; no allocation overdraft | 2 |
| R-09 | 9.1-9.3, 17.4 | Farmer/input/quantity/class/geography/time/single-use bindings, signing and key lifecycle, distribution, partial residual, expiry/pool return, reversal/reissue, cancellation/voiding | 2 |
| R-10 | 9.4-9.5, 15.3 | Authenticated, transactional, idempotent redemption; stock balance; precise error/replay semantics, receipts and farmer SMS | 2 |
| R-11 | 9.5, 14.3 | Dealer accreditation/outlets/devices, stock receipts, geofence/velocity controls, sampled verification, disputes/claim freeze, reconciliation and claims excluding failed records | 2, 4 |
| R-12 | 16.6, 18.2; diagram | Field/dealer PWA only: local evidence/cache/queue, time-limited PIN verification, 25 or GHS 15,000 cap, 48 h window, high-value exclusions, authoritative conflict resolution and pending banner | 1-2 |
| R-13 | 18.3-18.4 | Seven USSD functions, SMS passport lookup, server TTL sessions, approval/issuance/redemption/expiry/dispute/quarantine messages and delivery receipts | 1-3 |
| R-14 | 11.1-11.2 | Verified-farm flocks; hatchery/import, dates, house, counts; feed/vaccine/treatment/mortality/weight/inspection/disease/exit events; vet confirmations | 3 |
| R-15 | 8.5, 11.3-11.4 | Withdrawal calculation/block/justified vet override with amber notice; quarantine passport block and vaccine/medicine eligibility; disease escalation and spatial contacts | 3-4 |
| R-16 | 6.4, 10.1-10.3 | Flock provenance, unique non-enumerable bird/crate/pack codes, exit/processor binding, human-readable labels, ECC Q, 4-module quiet zone, >=18 mm QR | 3 |
| R-17 | 10.4-10.6, 12 | Allowlisted public projection, three verdicts, ordered public cards/timeline, metadata, farmer/dealer checks, manual entry, bulk API-key quotas, PDF summary, live badge and dossiers | 1-4 |
| R-18 | 10.5, 14.4, 17.7 | Canonical flock chains, nightly checking, daily closed-flock Merkle root publication; external anchoring reserved, not built | 3-4 |
| R-19 | 12.2, 17.1 | Per-IP/code/token abuse limits, enumeration detection, hashed-IP/coarse-region telemetry, distant duplicate scan warnings and alerting | 0, 3-4 |
| R-20 | 13 | Five dashboard audiences; reconciliation, claim, leakage/anomaly, registry integrity, traceability, health and audit reports; CSV/PDF, checksum, timestamp, quota, watermark and export audit | 1-4 |
| R-21 | 14-15, 21 | Reviewed forward-only schema, database constraints, real MySQL locking, OpenAPI 3.1 for every listed endpoint; JWT/refresh, problem+json, Idempotency-Key, UTC/ISO timestamps, rate headers | 0-4 |
| R-22 | 16 | Self-hosted Carbon/Plex, Gray 10 tokens/grid/spacing, specified screen components, no chart library on redemption, 360 px-first layout, 48 px terminal targets, keyboard/scanner, WCAG 2.1 AA | All |
| R-23 | 17.1-17.5 | All T1-T12 controls; Argon2id parameters, MFA, session timeouts, RS256/rotation, KMS encryption, TLS/security headers, prepared statements, escaping, CSRF, safe uploads and redacted logs | 0 onward |
| R-24 | 19 | Identity, SMS/USSD, GIS, veterinary, suppliers/hatcheries, processors and export adapters; contract tests, fallbacks, reference-only GS1 alignment; payment boundary decision | 0-4 |
| R-25 | 20, 22 | Performance/availability/device/scale targets; scheduled jobs, observability, off-site backups, replica/failover, retention, restore tests and runbooks | 0-4 |
| R-26 | 23 | >=80% domain unit coverage; all repositories/services integration-tested; critical end-to-end, release security/accessibility, 3x-peak performance and field UAT; TC-01 to TC-22 | All |
| R-27 | 24 | Consent/rights/corrections, 14-day rights-service target, privacy/export restrictions, retention, breach process, controller/governance ownership, evidence/appeals, licences/attribution and handover | 1-5 |
| R-28 | 22.4, 25, Appendix B | Incremental commits, feature PRs/review, CI/staging/UAT, tags/changelog/rollback, pilot participation, training, support and sponsor decisions | All |

## Mandatory regression scenarios (section 23.2)

All scenarios are pending. Keep these identifiers in test names or test metadata.

| Test | Expected outcome | Stage |
|---|---|---|
| TC-01 | Sequential second redemption rejected with prior-use details | 2 |
| TC-02 | Concurrent requests: exactly one redemption succeeds, other receives conflict | 2 |
| TC-03 | Same idempotency key replays original result without another row | 2 |
| TC-04 | Out-of-permitted-region dealer rejected | 2 |
| TC-05 | Expired voucher rejected and entitlement returned to pool | 2 |
| TC-06 | Suspended farmer redemption rejected | 2 |
| TC-07 | Over-entitlement quantity rejected | 2 |
| TC-08 | Partial redemption creates exact residual and notifies farmer | 2 |
| TC-09 | Already-spent offline redemption rejected on sync, both notified, claim excluded | 2 |
| TC-10 | Offline cap refuses additional redemptions | 2 |
| TC-11 | Farm inside duplicate radius blocked and flagged for review | 1 |
| TC-12 | GPS above accuracy threshold rejected | 1 |
| TC-13 | Duplicate national ID blocked before ID minting | 1 |
| TC-14 | Registering officer cannot approve own registration | 1 |
| TC-15 | Withdrawal blocks minting except justified vet override with amber passport | 3 |
| TC-16 | Duplicate passport code impossible under unique constraint | 3 |
| TC-17 | Direct flock-event tamper triggers critical chain alert | 3 |
| TC-18 | Distant-region scans within an hour trigger anomaly and visible warning | 3 |
| TC-19 | Sequential code enumeration throttled and alerted | 3 |
| TC-20 | District over-allocation blocked at issuance | 2 |
| TC-21 | Out-of-district officer lookup returns empty result and is audited | 1 |
| TC-22 | Redemption succeeds over 2G within the agreed measured latency budget | 2, 4 |

Additional tests must cover multi-role separation bypasses, PIN/OTP expiry and lockout, replay with changed payload, stock/allocation races, quarantine rules, offline expiry/revocation/restart behavior, concurrent chain appends, public-field leakage, rights-request access, export scope and restoration. These supplement the 22 scenarios rather than replacing them.

## Quantitative acceptance budgets

These are specification targets, not measured results.

| Area | Source target |
|---|---|
| Availability | 99.5% core monthly; 99.9% public passport |
| Latency | Validation p95 <400 ms; redemption p95 <800 ms; passport lookup p95 <300 ms; dashboards <2 s |
| First-load payload | Terminal <150 KB; public passport <200 KB including QR |
| Scale | 60,000 farmers; 90,000 farms; 400,000 flocks/year; 30M passports/year; 5M redemptions/year |
| Peak | 500 concurrent redemption sessions; major-release load test at 3x expected peak |
| Device/browser | Android 8, 2 GB RAM, 5-inch screen, 2G; Chrome/Android 90+, Safari 14+, Firefox 90+; no-JS core reads |
| Retention | Registry active lifetime +10 years; vouchers/audit 7 years; public passports 5 years after exit; scan telemetry 24 months |
| Recovery | RPO 15 minutes; RTO 4 hours; hourly incremental/nightly full; 30-day PITR; monthly written restore drill and daily sample verification |
| Sessions | Privileged idle 30 minutes; absolute 8 hours; secure/HttpOnly/SameSite=Lax; privilege rotation and logout invalidation |
| Password/PIN | Argon2id memory 65536, time 4, threads 2; farmer PIN 6 digits; officer/admin passwords >=12 characters and breach-list checked |

Measure server response time separately from full counter journey and 2G transfer time. Document the test method and resolve the payload/recovery ambiguities before claiming compliance.

## Operational schedules and alarms

Section 22.3: hourly expiry and session/OTP cleanup; 07:00 daily expiry reminders; 01:00 nightly chain verification; 02:00 roots; 03:00 reconciliation; anomaly detection every 15 minutes; dashboard refresh every 10 minutes; weekly reverification sweep; daily backup sample verification. Specify scheduler timezone explicitly while storing UTC.

Section 22.5 alarms: redemption errors >2%/15 min; redemption p95 >1.5 s/10 min; any terminal unsynced >24 h; any chain mismatch critical; SMS failures >10%/30 min; logins failing at 5x baseline; replication lag >30 s; disk >80%; any backup failure critical. Operational alarm thresholds do not relax acceptance latency targets.

Required runbooks: suspected fraudulent dealer; duplicate passport label; national ID outage; SMS outage; database failover; backup restore; key rotation; mass voucher reversal. Add breach handling, retention and rights-request procedures from section 24.
