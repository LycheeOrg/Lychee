# Open Questions – Feature 010

Open questions for [Feature 010](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-010-01: LDAP Authentication Method~~ ✅ RESOLVED

**Decision:** Option C - Both basic auth and LDAP independently configurable via .env
**Rationale:** Maximum flexibility; allows deployments to use LDAP-only, basic-only, or both. LDAP enablement controlled by .env variables.
**Updated in spec:** FR-010-05, authentication method selection

---

### ~~Q-010-02: User Provisioning~~ ✅ RESOLVED

**Decision:** Option C - User provisioning configurable via .env
**Rationale:** Flexibility for different deployment scenarios; allows auto-create or pre-existing-only mode via configuration.
**Updated in spec:** FR-010-04, user provisioning behavior

---

### ~~Q-010-03: LDAP Group Mapping~~ ✅ RESOLVED

**Decision:** Option B - Map LDAP groups to Lychee roles (admin/user)
**Rationale:** Allows admin role assignment via LDAP groups; provides automatic role sync without complex user group management.
**Updated in spec:** FR-010-03, role mapping configuration

---

### ~~Q-010-04: User Attribute Mapping~~ ✅ RESOLVED

**Decision:** Option C - Defaults with optional override via .env
**Rationale:** Provides sensible defaults (uid→username, mail→email, displayName→display_name) with .env configuration for LDAP schemas that differ.
**Updated in spec:** FR-010-02, attribute mapping configuration

---

### ~~Q-010-05: Password Storage~~ ✅ RESOLVED

**Decision:** Option A - Don't store LDAP passwords
**Rationale:** Most secure approach; authenticate only against LDAP server without password duplication.
**Updated in spec:** FR-010-01, authentication flow, security model

---

### ~~Q-010-06: Configuration Method~~ ✅ RESOLVED

**Decision:** Option A - Environment variables only
**Rationale:** LDAP is an expert/power-user setting; .env configuration is appropriate and avoids database complexity.
**Updated in spec:** All configuration options use .env variables, NFR-010-01

---

### ~~Q-010-07: LdapRecord Integration Strategy~~ ✅ RESOLVED

**Decision:** Option A - Service layer wrapping LdapRecord
**Rationale:** Better separation of concerns and testability. `LdapService` acts as facade/adapter over LdapRecord's Connection and query builder. Business logic abstracted from LDAP library details. Easier to test (mock LdapService interface) and swap libraries if needed.
**Updated in spec:** I2-I5 architecture, LdapService design as wrapper pattern

---

### ~~Q-010-08: LdapConfiguration DTO Purpose~~ ✅ RESOLVED

**Decision:** Option A - LdapConfiguration validates/transforms .env values
**Rationale:** Clean validation layer providing type-safe value object. Single source of truth: .env → LdapConfiguration::fromEnv() validates → values passed to LdapRecord config. Prevents invalid config, provides testability.
**Updated in spec:** I1 LdapConfiguration DTO implementation, validation strategy

---

### ~~Q-010-09: Connection Pooling Implementation~~ ✅ RESOLVED

**Decision:** Option A - Configure LdapRecord's built-in connection management
**Rationale:** Leverage existing, tested library features. Configure timeouts and connection caching via LdapRecord config. No custom pooling code needed.
**Updated in spec:** I2 implementation approach, NFR-010-04

---

### ~~Q-010-10: Testing Strategy~~ ✅ RESOLVED

**Decision:** Option A - LdapRecord testing utilities for unit tests, skip Docker integration tests
**Rationale:** Fast unit tests using LdapRecord's `DirectoryEmulator` or test helpers. Mock LDAP responses at service boundary. Docker integration tests deferred to future enhancement.
**Updated in spec:** I2-I7 test implementation, no Docker CI configuration needed

---

### ~~Q-010-11: Authentication Flow Sequence~~ ✅ RESOLVED

**Decision:** Option A - Search-first pattern (username → search → DN → bind → groups)
**Rationale:** Flexible approach supporting diverse LDAP schemas. Flow: 1) User submits username+password, 2) Search LDAP using `LDAP_USER_FILTER`, 3) Get userDn from search result, 4) Bind with userDn+password, 5) Query groups using userDn, 6) Retrieve user attributes.
**Updated in spec:** FR-010-01, I2 LdapService `authenticate()` method, I4 `getUserGroups()` signature

---

### ~~Q-010-12: TLS/StartTLS Configuration~~ ✅ RESOLVED

**Decision:** Option A - Single `LDAP_USE_TLS` flag, protocol determined by port
**Rationale:** Simpler configuration with fewer env vars. Protocol auto-detected: port 636 = LDAPS, port 389 = StartTLS. Documentation in .env.example clarifies both scenarios.
**Updated in spec:** ENV-010-13, I10 documentation deliverables
