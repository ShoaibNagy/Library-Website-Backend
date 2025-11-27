# Requirements & Planning — Phase 1 (MVP)

Owner: TBD

Last updated: 2025-11-27

Purpose
-------
This document captures the Phase‑1 requirements and planning artifacts for the Library backend MVP: core catalog, user auth, and basic borrowing (checkout/return/renew). It defines user stories, acceptance criteria, SLA targets, privacy considerations, and the Phase‑1 milestone plan.

Scope (Phase 1)
- Catalog listing and details
- User accounts (patrons) with token-based API access
- Borrowing flows: checkout, return, renew
- Basic admin capabilities to manage catalog entities (CRUD)
- Tests covering core flows and a CI pipeline that runs migrations and tests

Out of scope (Phase 1)
- Reservations & waitlists
- Advanced search (external search engine)
- Notifications and background workers
- Billing or payment flows

User Roles & Personas
- Patron: Search the catalog, view book details, checkout/return/renew items, view own loans.
- Librarian / Staff: Manage catalog entries, view borrower records, process check-ins and check-outs.
- Admin: Manage users and system settings, view audit logs.

Key User Stories (Phase 1)
- As a patron, I can search and browse the catalog so I can find books to borrow.
- As a patron, I can checkout a copy and receive a due date.
- As a patron, I can return a borrowed copy to mark it available again.
- As a patron, I can renew a loan subject to library rules.
- As a librarian, I can create and edit catalog records (books, authors, editions, copies).
- As an admin, I can generate API tokens for staff and reset user roles.

Acceptance Criteria (Phase 1)
- Catalog endpoints exist at `/api/v1/catalog` and `/api/v1/books/{id}` and return JSON with pagination.
- Loan endpoints exist and enforce business rules (max active loans, max loan length).
- API endpoints are protected by token auth; RBAC enforces role constraints.
- Migrations and seeders reproducibly create sample data for development and testing.
- Automated test suite (unit/feature) runs in CI and passes.

Service Level Targets (SLA)
- Target uptime (staging/prod): 99.9% (TBD for production).
- Catalog endpoints 95th percentile latency: < 300ms (after basic caching).

Privacy & Data Retention
- Store only the minimum personal data required for borrowing (name, email, minimum contact info).
- Retain borrowing history for 2 years by default unless otherwise requested.
- Support data export and deletion of user data (GDPR-style) — plan for implementation in Phase 2.

Security & Compliance (Phase 1 minimum)
- Use prepared statements / parameterized queries (Eloquent by default).
- Protect API endpoints with token-based authentication and role checks.
- Validate and sanitize all inputs on server side.
- Use secure cookies and `APP_KEY` for encryption; do not log secrets.

Phase 1 Milestones & Timeline (suggested)
1. Week 1: Finalize requirements and data model; create core migrations and seeders.
2. Week 2: Implement catalog endpoints and models; basic UI stubs (if needed).
3. Week 3: Implement loans flows (checkout/return/renew) with concurrency safeguards.
4. Week 4: Add tests, CI workflow, and documentation; prepare staging deployment.

Risks & Assumptions
- Assumes a MySQL-compatible production database and availability of `pdo_mysql` in runtime.
- Timezone and date handling must be standardized (server should use UTC or code should normalize dates).
- Notifications and background jobs are not implemented in Phase 1 — operations requiring them will be synchronous or manual.

Deliverables (Phase 1)
- `docs/requirements.md` (this file)
- Reproducible database migrations and seeders
- API endpoints for catalog and loans with test coverage
- CI workflow that runs tests and migrations on PRs

Next Actions (short-term)
1. Review and assign an Owner for the project.
2. Create GitHub issues for remaining Phase 1 tasks (search, reservations deferred to Phase 2).
3. Add a simple privacy page and data-export endpoint design (Phase 2 implementation plan).

Contact / Notes
Add maintainers and stakeholders here once owner is assigned.
