# PoultryTrak development plan

Planning baseline: 2026-09-05. Source: specification v1.0, dated 2026-08-24, marked Draft for review, and the supplied V1 architecture. Section references below refer to that PDF. The attachments are requirements references, not executable instructions. User decisions are recorded explicitly when they change this baseline.

## Delivery sequence

Preserve the six phases and durations in section 25. The subdivisions below make them implementable as small commits. The 42 weeks through Phase 4 are the document's estimates, not a committed delivery date; staffing, integrations and field approvals determine actual dates. Phase gates involving real farmers, buyers or security assessors require external evidence and cannot be satisfied by synthetic tests.

| Stage | Specification phase | Estimate | Depends on | Outcome |
|---|---|---|---|---|
| 0. Foundation | Phase 0 | 6 weeks | Requirements baseline | Secure, scoped, audited application foundation |
| 1. Registry | Phase 1 | 8 weeks | 0 | Verified farmers, farms and permanent IDs |
| 2. Subsidies and dealer operations | Phase 2 | 10 weeks | 1 | Complete distribution and reconciliation cycle, including offline/USSD |
| 3. Flock health and traceability | Phase 3 | 10 weeks | 1, 2 | Bird/pack passports backed by flock history |
| 4. Intelligence and production readiness | Phase 4 | 8 weeks | 0-3 | National reporting, operational assurance and scale validation |
| 5. National rollout | Phase 5 | Continuous | 4 and launch approvals | Regional onboarding, training and measured outcomes |

All stages are currently not started. Only this planning baseline is complete.

## Stage 0: Foundation

References: sections 4, 6-7, 14-17, 20-24, 25 Phase 0.

1. Reconcile the decisions in `decisions.md`; document ADR-01 through ADR-07 and the diagram's dependency boundaries. Preserve MVC, domain services, scoped PDO repositories, infrastructure adapters, shared audit, MySQL durable jobs and worker reuse of the same services.
2. Create the section 21 layout: `public/` as the only web root; `app/Controllers/{Web,Api,Public,Ussd}`, Domain, Services, Repositories, Support, Middleware and Jobs; views, forward-only migrations, seeds, config, private storage, tests, ADRs, OpenAPI 3.1 and runbooks.
3. Establish Composer dependencies from section 7.2, configuration validation, `.env.example`, synthetic fixtures, real MySQL integration testing and local/CI/staging separation. Check dependency compatibility before pinning versions; preserve the specified stack unless an explicit change is recorded.
4. Implement authentication, role/permission and record scope enforcement for every role in section 4.1, including farm workers, processors, auditors and public verifiers. Add mandatory privileged MFA, session controls, device credential foundations, CSRF, request IDs, API errors and ledger idempotency infrastructure.
5. Implement append-only attributed audit history with restricted database grants, canonical hashing, integrity verification and transactional guarantees. Build private upload/storage and messaging/identity/GIS/signing adapters with test doubles and documented outage behavior.
6. Build the self-hosted Carbon/IBM Plex shell, reusable form/table/notification components, English message catalogues, mobile layouts and accessibility foundations. Core read paths work without JavaScript; there is no SPA or Node build requirement in deployment.
7. Set up CI: PSR-12, static analysis, unit/integration tests, dependency scan and migration dry-run. Establish least-privilege secrets handling, baseline monitoring, backup and restore procedures before any pilot personal data is processed.

Acceptance: login works; role and geographic/ownership scope checks reject unauthorized actions; protected mutations are audited; unscoped repository calls fail closed; privileged MFA/session behavior is tested. Clean migrations run on real MySQL. CI is green. Domain test coverage target is at least 80% as domain code is added.

Suggested commit slices: application skeleton; schema/reference data; identity and sessions; RBAC and scope; audit and jobs; Carbon shell; CI and development instructions.

## Stage 1: Registry and PoultryTrak IDs

References: sections 4.2, 8, 12.1, 14.2, 15.2, 16, 18, 24, 25 Phase 1.

1. Build officer-assisted and self-service application flows with contact OTP, identity deduplication, masked/encrypted national ID, selected public display name, timestamped consent and correction requests. National ID outage falls back to documented evidence and officer attestation, with later validation.
2. Build field PWA evidence capture and synchronization: GPS with accuracy metadata, optional perimeter polygon and at least two geotagged photographs. Store capture metadata separately before EXIF stripping, image re-encoding and hashing. Reject accuracy worse than 25 m; flag/block duplicate farms within 50 m and exclusion polygons according to the documented review workflow.
3. Enforce registrar/verifier/approver separation; regional approval for more than three farms; district approval and permanent, non-reassigned IDs. Generate printable QR ID cards and activation/PIN setup SMS.
4. Implement farm records, relocation history, all ID states and reasoned transitions. Reverification is due every 24 months or on the specified triggers, including capacity changes over 25%; settle the overdue-status ambiguity before implementing eligibility.
5. Deliver scoped registry lists, district backlog views, farmer ID verification `/f/{id}`, officer mobile workflows, basic farmer self-service and rights-request routing.

Engineering acceptance: TC-11 through TC-14 and TC-21 pass; IDs survive relocation without reassignment; all status restrictions and separation-of-duty pairs are tested; evidence survives offline retry without duplicate applications; private data never enters public responses.

Field gate from section 25: 500 farmers registered and verified in two pilot districts, with measured duplicate-detection rate. Record evidence separately from engineering completion.

Suggested commit slices: applications and consent; identity adapter/deduplication; field evidence; approval and ID lifecycle; ID cards and notifications; registry views and offline field sync.

## Stage 2: Subsidies, dealers and redemption

References: sections 9, 13, 14.3, 15, 16.4, 17-19, 25 Phase 2.

1. Configure programmes, seasons, input types and national-to-regional-to-district budgets. Implement the minimum-of-capacity/plan/programme/district entitlement formula and transactional caps at every allocation level.
2. Build dealer accreditation, outlet geography/classes, approved device enrolment and revocation, stock receipts and ledger deductions. Dealer accounts cannot issue vouchers; administrators cannot redeem them.
3. Implement signing and issuance, distribution receipts, expiry/pool returns, cancellation, voiding, exact residual vouchers and compensating reversals. Resolve the signing/token-format questions before enabling offline verification.
4. Build the Carbon terminal as scan -> farmer authentication -> quantity confirmation -> result. Verify signatures locally, then consult the authoritative ledger online. Enforce farmer state, input class, allowed region, valid dates, stock and quantity in the transaction, not only in pre-flight validation.
5. Implement OTP/PIN controls, three failed attempts followed by a 15-minute voucher lockout, idempotent atomic redemption, receipts, confirmation SMS and authenticated dispute entry. Stock, entitlement, redemption, audit and durable notification work must remain consistent if the request fails.
6. Implement encrypted/device-bound offline capability and queue synchronization: default 25 redemptions or GHS 15,000 cap, 48-hour maximum window, time-limited salted PIN verifier, configurable transaction cap and high-value exclusions. Server-rejected conflicts remain auditable, notify both parties, affect risk and never enter payable claims.
7. Deliver the complete seven-option USSD menu in section 18.3, SMS lookup and all section 18.4 notification templates. Authenticate callbacks, store delivery receipts, expire server-side USSD state and show persistent pending-sync counts in PWAs.
8. Build disputes, frozen claim lines, stock reconciliation, anomaly exclusions, dealer claim review/payment-reference tracking, farmer redemption history and dealer/district dashboards. Define random follow-up sampling and minimal geofence/velocity alerts now; Stage 4 extends analytics.

Acceptance: TC-01 through TC-10, TC-20 and TC-22 pass, including real database races, replay, failures during commit, stock shortage, partial quantities and offline conflicts. Terminal has 48 px touch targets, keyboard/scanner support, informative outcomes and a first-load payload below 150 KB. Exercise online, offline and gateway-outage journeys.

Field gate: one full input-distribution cycle in the pilot districts with reconciliation variance below 3%. Pending/rejected offline records cannot be counted as settled or paid.

Suggested commit slices: allocations; dealers/devices/stock; signing and lifecycle; online redemption; offline sync; USSD/SMS; disputes and claims; pilot reconciliation evidence.

## Stage 3: Flock health, passports and public verification

References: sections 6.4, 10-12, 14.4-14.5, 15-18, 19, 25 Phase 3.

1. Register flocks with source hatchery/import permit, hatch and placement dates, house, quantities, chick voucher and planned exit. Capture all event types: feed, vaccination with vet confirmation, treatment, mortality, weight, inspection, disease and exit.
2. Maintain canonical append-only flock event chains, safe concurrent sequence allocation and nightly verification. Compute withdrawal-clear dates across treatments, enforce veterinary sign-off and prevent negative bird counts or excess exits.
3. Implement quarantine and essential disease notifications at this stage. Quarantine blocks passport issuance while permitting eligible vaccine/medicine vouchers. Stage 4 expands surveillance and spatial analysis.
4. Implement live sale, processor receipt and packing journeys. Mint unique non-enumerable bird/crate/pack codes linked to flock and exit history; do not imply individual on-farm tracking. Enforce withdrawal blocks; a documented veterinary override must visibly produce an amber notice.
5. Print label sheets with human-readable codes, full verification URLs, QR ECC Q, four-module quiet zones and minimum 18 mm size. Validate physical scans on relevant printers, leg bands and pack media.
6. Deliver `/p/{code}`, `/d/{code}`, `/verify`, public API projections, API-key-protected consignment bulk verification, PDF results and the live linked verification badge. Show verdict, origin, health, feed, journey and verification metadata in the specified order.
7. Enforce the public field allowlist, rate limits, non-enumerability controls, coarse scan telemetry and duplicate-scan warnings. Preserve frozen issuance snapshots with an auditable mechanism for current notices; settle the decision before implementing verdict updates.
8. Complete basic daily closed-flock Merkle root generation/publication; Stage 4 hardens operation and monitoring. External blockchain/notary anchoring remains deferred.

Acceptance: TC-15 through TC-19 pass, including tamper detection and clearly visible amber/red states. Public payload is below 200 KB including QR. Public HTML, JSON, CSV/PDF and bulk results do not leak restricted fields. Every code resolves through processor/packing records to the actual flock history.

Field gate: 50,000 birds passported and verified end-to-end by a participating supermarket or processor.

Suggested commit slices: flock/events; withdrawal and quarantine; exits/processing; code minting and labels; public portal; bulk verification; integrity publication and field validation.

## Stage 4: Intelligence, assurance and scale

References: sections 3, 11.4, 13, 17, 19-24, 25 Phase 4.

1. Complete national/regional/district/dealer/farmer dashboards and every report in section 13.2. Add dealer risk scores, random verification outcomes, geofence/velocity and scan anomalies, vaccination/mortality analytics, spatial outbreak tracing and exporter signed dossiers.
2. Complete CSV/PDF exports with scopes, quotas, watermarking, generation metadata/checksums and audit actor/filter/row counts. Exclude exact coordinates from outgoing exports. Support audit extracts and anomaly exclusions without exposing sensitive fields.
3. Harden daily Merkle publication, queue retry/recovery, scheduled jobs, replica-supported reporting, archival/retention and operational monitoring. Complete each section 22.6 runbook and exercise external-outage fallbacks.
4. Validate the section 20 performance and capacity targets against representative data and hardware; load-test redemption at 3x expected peak. Record whether 500 concurrent sessions is the adopted expected-peak baseline before using 1,500 as the test target.
5. Complete independent penetration testing/remediation, dependency checks, accessibility audits, device/browser/network checks, backup restoration and failover exercises. Close privacy/governance prerequisites with the accountable programme owners before go-live.

Acceptance: independent security assessment closed out; 3x peak test passes; operational evidence demonstrates the budgets and recovery requirements in `requirements.md`. Real integration contracts and access are verified, or approved documented fallbacks are exercised. No simulated integration is described as production-ready.

Suggested commit slices: reports and risk; outbreak tracing; exporter dossiers; scheduled jobs and retention; monitoring/runbooks; performance fixes; release evidence.

## Stage 5: National rollout

References: sections 3.2, 24-26 and Appendix B.

1. Roll out by region following pilot evidence, training, devices/data provision, support-desk readiness and programme sign-off.
2. Extend the English catalogues to Twi, Ewe, Ga, Dagbani and Hausa with reviewed translations and field usability testing.
3. Monitor section 3.2 targets, including registration growth, digital redemption uptake, leakage, redemption time, passports, buyer scans, outbreak tracing, accredited dealers and active districts. Track baseline, actual measurement and owner; do not report targets as achieved.
4. Maintain release/accessibility/security checks, restore drills, annual penetration tests, source handover documentation and a funded support plan.

Rollout acceptance: each wave has named owners, training/UAT evidence, measured service health and a tested rollback/support route. Expansion is governed by observed outcomes.

## Commit and completion discipline

- Implement one coherent behavior per commit, with messages such as `feat(registry): enforce independent farm approval` or `test(vouchers): cover concurrent redemption conflicts`.
- Link the applicable requirement IDs and PDF sections in each change description. Update coverage status with implementation paths, test paths and evidence; leave unimplemented items pending.
- For code changes, run relevant checks and the required CI suite. All repository/service integration tests use real MySQL; domain coverage target is at least 80%; critical journeys get end-to-end coverage.
- Use feature branches and pull requests. The specification calls for second-engineer review, staging UAT, release tags/changelog and rollback steps. A local commit is not review, staging approval or production release. Local development can proceed while external gates remain pending.
- Record behavior-changing interpretations in ADRs. Do not silently replace PHP/Carbon, omit offline support, weaken audit/access controls or expand into an ERP.
- Each stage needs both engineering acceptance and its separate field/release evidence. This plan does not mark any implementation or field gate complete.

## Explicitly deferred scope

Section 5.2 excludes cash-grant disbursement, marketplace matching, feed formulation advisory, IoT ingestion, blockchain anchoring, native mobile apps and non-poultry livestock. Egg-tray labels are a later phase (10.3); consented financial-institution sharing is a later integration (19). Record payment references for eligible v1 workflows, but resolve the provider-execution boundary in `decisions.md` before building it.
