# Specification decisions and external dependencies

The PDF is a draft. These are identified ambiguities and proposed resolutions, not approved specification amendments. Continue independent work; resolve each decision before implementing the affected contract. Preserve the source documents unchanged. Record accepted decisions as ADRs with source section, rationale, affected requirements and test consequences.

| ID | Source / issue | Proposed handling and decision gate |
|---|---|---|
| D-01 | 4.2 forbids registrar=verifier; 8.2 forbids verifier=approver; 14.6/TC-14 forbids registrar=approver | Apply all three restrictions together, including users with multiple roles. Confirm role workflow during Stage 0 rather than choosing one pair. |
| D-02 | 9.2 distributes an HMAC signing secret to offline terminals; 17.4 mentions full server-side tags while printed tags are only 40 bits | A holder of a symmetric verification key can also forge tags. Review device compromise, token size, full-tag representation, expiry and revocation constraints before Stage 2. An asymmetric alternative would be a documented architecture change, not a silent substitution. |
| D-03 | 9.1/9.4 permit OTP or PIN; 17.2 describes OTP as farmer second factor; 18.2 requires PIN-only offline | Specify factors by action/channel and rate limits before Stage 2; do not remove offline support or silently weaken sensitive-change authentication. |
| D-04 | 8.4 says overdue farm reverification blocks new allocation but existing vouchers remain redeemable; 8.5 describes overdue farmers as suspended with no redemption | Define farm-level eligibility versus farmer suspension and triggering policy before Stage 1 lifecycle implementation. |
| D-05 | 8.3 calls the check symbol Damm/mod-97 but gives a letter example; token field widths/quantity encoding also need exact definitions | Select and document test-vector-backed ID/check/token encodings, units, bounds, precision and rollover before minting permanent identifiers. |
| D-06 | 14 SQL is incomplete relative to prose and includes illustrative inconsistencies | Produce reviewed migrations, not pasted PDF SQL. Cover pending applicants before permanent ID minting, hatch dates/houses, stock movements, claim lines, transfers, processors, consent, refresh/OTP state and durable jobs. Validate partition/unique-key compatibility, nullable allocation uniqueness, foreign keys and constraints on real MySQL. |
| D-07 | 9.3 diagram includes settled/reissued/returned_to_pool but table/enums do not; reversal columns coexist with compensating-entry rules | Define ledger state versus claim/settlement state and exact residual/reissue lineage before Stage 2. Preserve original financial events and append compensation. |
| D-08 | 10.5 says append-only; 14.4 freezes public snapshots; TC-18 requires later scan warnings | Separate immutable issuance evidence from audited current notices/verdict evaluation. Define recall/quarantine and warning precedence before Stage 3. Never overwrite provenance silently. |
| D-09 | 16.1 mandates self-hosted assets but the sample links Google Fonts | Follow the explicit self-hosting requirement for Carbon and IBM Plex. Record this interpretation in the UI ADR. |
| D-10 | 6/21 use /api/v1; 15 uses api-host /v1 | Define canonical internal route prefix and host/reverse-proxy mapping in OpenAPI before Stage 0 API contracts. Avoid two incompatible APIs. |
| D-11 | 20 specifies hourly incremental backup and 15-minute RPO | Design binlog/PITR shipping frequent enough to meet RPO; prove restoration rather than claiming hourly backups meet 15 minutes. Resolve in operations design. |
| D-12 | 16.4/20 strict payload budgets coexist with self-hosted Carbon/Plex; TC-22 refers to 2G latency | Establish compressed-wire measurement, font/asset inclusion and cold-cache conditions. Keep full journey timing separate from server p95; verify a representative terminal early. Do not silently relax budgets. |
| D-13 | 5.2 excludes cash grants while 19 lists mobile-money co-payment/reimbursement | Confirm v1 provider execution versus payment-reference recording with programme owners. Do not build a payment processor or assume cash grants are included. |
| D-14 | 19 says outages must not block legitimate redemption; 18.2 mandates hard offline limits | Document supported fallback and honest pending/refusal states when safety limits are exhausted. Do not bypass limits to promise unlimited availability. |
| D-15 | 25 places advanced anomalies/surveillance/roots in Phase 4, but earlier modules rely on these controls | Implement essential claim exclusions, quarantine and tamper/scan warnings with their modules; expand analytics/operations in Stage 4. Never ship a pilot with those controls merely promised later. |

## Sponsor questions (Appendix B)

Track owner, answer and date when available. These do not block writing the plan or developing with synthetic data and adapters.

- National ID API availability and data-sharing agreement; attestation and later-validation procedure.
- Dealer reimbursement turnaround, accreditation/de-accreditation authority and appeals process.
- Register ownership, registered data controller and programme/veterinary responsibilities.
- Two pilot districts, available officers/devices, at least three dealers, one hatchery and one processor.
- Formal buyers committed to actual scanning during the traceability pilot.
- Funding horizon, support operation and sustainability model.
- Existing register migration needs and source quality.

Before live processing, accountable owners must settle the source specification's data protection/controller, consent, retention/breach procedure, source ownership/escrow and deployment approvals. This is a delivery dependency register, not a claim that legal compliance or institutional approval has already been established.
