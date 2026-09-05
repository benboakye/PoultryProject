# PoultryTrak

National Poultry Registration, Input Subsidy & Traceability System for Ghana.

Status: Stage 0 in progress. The HTTP/configuration skeleton is implemented; business modules are not yet available.

- [Run locally and check the code](docs/development.md)

- [Development stages and acceptance gates](docs/development-plan.md)
- [Requirements coverage and regression checklist](docs/requirements.md)
- [Specification decisions and sponsor questions](docs/decisions.md)
- [Original specification v1.0](docs/reference/PoultryTrak-Project-Documentation-v1.0.pdf)
- [Supplied V1 architecture](docs/reference/PoultryTrak-V1-System-Architecture.png)

Baseline: server-rendered PHP 8.2+, MySQL 8.0+, self-hosted Carbon DS vanilla v10.58, progressive enhancement and field/dealer PWAs. This is a registry, entitlement system and traceability ledger, not a general farm-management ERP.

Development uses small, tested commits on feature branches. See the plan for review and release gates.
