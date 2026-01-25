# Feature 010 – LDAP Authentication Support

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-01-25 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/010-ldap-support/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/010-ldap-support/tasks.md` |
| Roadmap entry | Feature #010 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Add LDAP (Lightweight Directory Access Protocol) authentication support to Lychee, enabling enterprise users to authenticate against existing LDAP/Active Directory servers instead of or alongside traditional username/password authentication. This affects the application layer (authentication services), REST layer (login endpoints), and potentially the UI (login form). The feature is configured entirely through environment variables (.env), targeting power users and enterprise deployments with existing directory infrastructure.

## Goals
- Enable LDAP authentication as an alternative to basic auth
- Support auto-provisioning of users from LDAP on first login
- Map LDAP groups to Lychee admin/user roles automatically
- Provide configurable LDAP attribute mapping with sensible defaults
- Maintain security by not storing LDAP passwords in Lychee database
- Configure all LDAP settings via environment variables for expert users

## Non-Goals
- UI-based LDAP configuration (power users expected to edit .env)
- Database-backed LDAP configuration (configs table not used)
- Local password fallback for LDAP users
- Full LDAP group sync to Lychee user groups (only admin/user role mapping)
- SAML or OAuth integration (separate features)
- LDAP write operations (read-only authentication and attribute retrieval)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-010-01 | LDAP authentication attempt | When LDAP enabled and user submits credentials, bind to LDAP server with provided username/password; on success, create/update user and establish session | Validate LDAP connection settings exist; verify LDAP server is reachable | Log LDAP connection errors, bind failures; return "Invalid credentials" to user without exposing LDAP details | Log `ldap.auth.attempt`, `ldap.auth.success`, `ldap.auth.failure` with sanitized username | Enterprise auth requirements |
| FR-010-02 | User attribute synchronization | On successful LDAP authentication, retrieve configured attributes (email, display_name) from LDAP and update user record; store username (uid/sAMAccountName) for unique login identifier, display_name (displayName/cn) for friendly UI display | Use default attribute mappings (uid→username, mail→email, displayName→display_name) or .env overrides; username must be unique (enforced by LDAP uid/sAMAccountName), display_name can have duplicates; validate retrieved values | If required attributes missing, use fallback values (username for display_name) or fail provisioning based on config | Log `ldap.user.sync` with attribute count; redact actual values | Directory integration best practices; display_name added for user-friendly UI (Q-010-13) |
| FR-010-03 | LDAP group to role mapping | On successful LDAP authentication, query user's LDAP group memberships; set `may_administrate` based on admin group match | Check if user DN is member of configured admin group(s); default to regular user if no admin group match | If group query fails, log warning and use default role (non-admin) | Log `ldap.role.assigned` with role (admin/user) but not group names | Role-based access control |
| FR-010-04 | User auto-provisioning | When LDAP user authenticates for first time and auto-provision enabled, create new User record with LDAP attributes | Check if username already exists; validate LDAP attributes meet User model constraints | If auto-provision disabled and user doesn't exist, reject login with appropriate error | Log `ldap.user.created` for new users | Just-in-time provisioning pattern |
| FR-010-05 | Authentication method selection | Support basic auth and LDAP independently based on .env configuration; try LDAP first if enabled, fall back to basic auth if both enabled | Check `LDAP_ENABLED` and basic auth enablement flags; validate user exists for basic auth fallback | If both disabled, reject all logins; if LDAP-only and LDAP server unreachable, return service unavailable | Log `auth.method.selected` with method (ldap/basic) | Multi-auth deployment flexibility |
| FR-010-06 | LDAP connection pooling | Reuse LDAP connections efficiently; implement connection timeout and retry logic | Validate connection pool configuration; test LDAP server connectivity on startup | Handle connection pool exhaustion, timeouts; implement exponential backoff for retries | Log `ldap.connection.acquired`, `ldap.connection.timeout` | Performance and reliability |
| FR-010-07 | Secure credential handling | Never log or store LDAP passwords; bind credentials only transmitted to LDAP server over TLS | Enforce LDAPS (LDAP over TLS) or StartTLS; reject plaintext LDAP connections | If TLS unavailable and required, fail authentication with security error | Log `ldap.tls.required`, `ldap.tls.established` | Security best practices |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-010-01 | All LDAP configuration via environment variables | Power user target audience; avoid database schema changes | All LDAP settings documented in .env.example; no configs table entries for LDAP | Laravel config system | Product decision (Q-010-06) |
| NFR-010-02 | LDAP authentication latency < 2 seconds (p95) | Acceptable user experience for network-bound auth | Measure time from credential submission to session establishment | LDAP server response time, network latency | Industry standard |
| NFR-010-03 | Backward compatibility with existing basic auth | Existing users must continue working; LDAP is additive | All existing tests pass; basic auth flow unchanged when LDAP disabled | Auth guard implementation | Product stability requirement |
| NFR-010-04 | Support Active Directory and OpenLDAP | Broad enterprise compatibility | Test against both AD and OpenLDAP servers | LDAP library (e.g., php-ldap, Adldap2) | Market coverage |
| NFR-010-05 | Security: No plaintext password storage or logs | Compliance and security best practices | Code audit: grep for password logging/storage; PHPStan rule | Logging framework configuration | Security policy |
| NFR-010-06 | Graceful degradation when LDAP unavailable | Service availability if LDAP server down | When LDAP enabled but unreachable, basic auth still functions (if enabled) | Error handling, auth method fallback | Reliability requirement |

## UI / Interaction Mock-ups

No UI changes required initially. Login form remains unchanged; LDAP authentication is transparent to the user. Future enhancement could add LDAP/Basic auth selector if both are enabled, but this is a non-goal for v1.

```
┌─────────────────────────────────────┐
│  Lychee Login                       │
├─────────────────────────────────────┤
│                                     │
│  Username: [________________]       │
│  Password: [________________]       │
│                                     │
│  [Login]                            │
│                                     │
│  (LDAP auth happens transparently   │
│   based on .env configuration)      │
└─────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-010-01 | LDAP user logs in successfully for first time → user auto-created, role assigned from LDAP group, session established |
| S-010-02 | LDAP user logs in successfully (existing user) → attributes synced, role updated from LDAP group, session established |
| S-010-03 | LDAP authentication fails (invalid credentials) → error logged, "Invalid credentials" message returned to user |
| S-010-04 | LDAP server unreachable, basic auth enabled → fallback to basic auth succeeds |
| S-010-05 | LDAP server unreachable, LDAP-only mode → service unavailable error returned |
| S-010-06 | LDAP user in admin group → `may_administrate` set to true on login |
| S-010-07 | LDAP user not in admin group → `may_administrate` set to false on login |
| S-010-08 | Auto-provision disabled, unknown LDAP user → login rejected with appropriate error |
| S-010-09 | LDAP attribute mapping (custom) → attributes synced using .env-configured mappings |
| S-010-10 | TLS/SSL required but unavailable → authentication rejected with security error |

## Test Strategy

- **Core:** 
  - Unit tests for LDAP service (connection, bind, attribute retrieval, group membership)
  - Mock LDAP server for deterministic testing
  - Test attribute mapping with various configurations
  - Test role assignment logic with different group memberships

- **Application:** 
  - Integration tests for user provisioning flow
  - Test authentication method selection logic
  - Test user attribute synchronization on repeated logins
  - Test error handling (LDAP server down, invalid config, etc.)

- **REST:** 
  - Feature tests for `/api/Session::login` with LDAP credentials
  - Test auth method fallback scenarios
  - Test error responses for various LDAP failure modes

- **CLI:** 
  - N/A (no CLI changes for v1)

- **UI (JS/Selenium):** 
  - Optional E2E test against mock LDAP server verifying transparent auth

- **Docs/Contracts:** 
  - Document all .env variables in `.env.example`
  - Update authentication documentation with LDAP setup guide

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-010-01 | LdapConfiguration - Value object encapsulating .env LDAP settings (host, port, base_dn, bind_dn, bind_password, attribute mappings, admin group) | core, application |
| DO-010-02 | LdapUser - DTO representing user data retrieved from LDAP (username, email, display_name, groups) | core, application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-010-01 | REST POST /api/Session::login | Modified to support LDAP authentication; no schema change visible to clients | Transparent implementation change |

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-010-01 | (future) `php artisan ldap:test-connection` | Test LDAP connectivity and configuration | Non-goal for v1; documented for future |

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-010-01 | ldap.auth.attempt | `username` (sanitized), `method=ldap`, `timestamp` |
| TE-010-02 | ldap.auth.success | `username` (sanitized), `new_user` (boolean), `role` (admin/user), `duration_ms` |
| TE-010-03 | ldap.auth.failure | `username` (sanitized), `reason` (enum: invalid_credentials, server_unreachable, tls_error), `duration_ms` |
| TE-010-04 | ldap.user.created | `username` (sanitized), `source=ldap` |
| TE-010-05 | ldap.user.sync | `username` (sanitized), `attributes_synced` (count) |
| TE-010-06 | ldap.role.assigned | `username` (sanitized), `role` (admin/user), `source` (ldap_group/default) |
| TE-010-07 | ldap.connection.timeout | `host`, `port`, `timeout_ms` |
| TE-010-08 | ldap.tls.required | `host`, `port`, `tls_available` (boolean) |

### Environment Variables

| ID | Variable | Default | Description |
|----|----------|---------|-------------|
| ENV-010-01 | LDAP_ENABLED | false | Enable LDAP authentication |
| ENV-010-02 | LDAP_HOST | - | LDAP server hostname |
| ENV-010-03 | LDAP_PORT | 389 | LDAP server port (636 for LDAPS) |
| ENV-010-04 | LDAP_BASE_DN | - | Base DN for LDAP searches (e.g., dc=example,dc=com) |
| ENV-010-05 | LDAP_BIND_DN | - | DN for bind user (e.g., cn=admin,dc=example,dc=com) |
| ENV-010-06 | LDAP_BIND_PASSWORD | - | Password for bind user |
| ENV-010-07 | LDAP_USER_FILTER | (&(objectClass=person)(uid=%s)) | LDAP filter for user search (%s replaced with username) |
| ENV-010-08 | LDAP_ATTR_USERNAME | uid | LDAP attribute for unique username identifier (uid for OpenLDAP, sAMAccountName for Active Directory) - must be unique in LDAP directory |
| ENV-010-09 | LDAP_ATTR_EMAIL | mail | LDAP attribute for email |
| ENV-010-10 | LDAP_ATTR_DISPLAY_NAME | displayName | LDAP attribute for display name (user-friendly name for UI display; can have duplicates unlike username) |
| ENV-010-11 | LDAP_ADMIN_GROUP_DN | - | DN of LDAP group for admin users (e.g., cn=lychee-admins,ou=groups,dc=example,dc=com) |
| ENV-010-12 | LDAP_AUTO_PROVISION | true | Auto-create users on first LDAP login |
| ENV-010-13 | LDAP_USE_TLS | true | Require TLS/SSL for LDAP connections |
| ENV-010-14 | LDAP_TLS_VERIFY_PEER | true | Verify TLS certificate |
| ENV-010-15 | LDAP_CONNECTION_TIMEOUT | 5 | LDAP connection timeout (seconds) |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-010-01 | Login success (LDAP) | User submits credentials, LDAP auth succeeds → redirect to gallery |
| UI-010-02 | Login failure (LDAP) | User submits credentials, LDAP auth fails → show "Invalid credentials" error message |
| UI-010-03 | Service unavailable (LDAP) | LDAP server unreachable, no fallback → show "Authentication service temporarily unavailable" error |

## Telemetry & Observability

- **Success metrics:** `ldap.auth.success` events track successful LDAP logins; `new_user=true` indicates auto-provisioning
- **Error tracking:** `ldap.auth.failure` events with `reason` enum enable troubleshooting auth issues
- **Performance:** `duration_ms` fields in auth events monitor LDAP response times
- **Security audit:** All LDAP auth attempts logged with sanitized usernames for security auditing
- **Redaction:** Never log passwords, full DNs (only sanitized usernames), or sensitive LDAP attributes

## Documentation Deliverables

- Update `.env.example` with all LDAP configuration variables and inline comments
- Update `docker-compose.yaml` with commented LDAP environment variables in the `x-common-env` section
- Create `docs/ldap-setup.md` guide with:
  - LDAP/Active Directory configuration examples
  - Attribute mapping examples for common LDAP schemas
  - Troubleshooting section (connection issues, TLS problems, attribute mapping)
  - Security best practices (bind user permissions, TLS requirements)
- Update `docs/specs/4-architecture/knowledge-map.md`:
  - Add LDAP service module
  - Document auth flow integration with existing SessionOrTokenGuard
  - Note dependencies on php-ldap extension
- Update `README.md` to mention LDAP support in features list

## Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-010-01 | tests/Fixtures/ldap-users.php | Sample LDAP user data for mocking LDAP responses |
| FX-010-02 | tests/Fixtures/ldap-groups.php | Sample LDAP group data for role mapping tests |

## Database Schema Changes

| ID | Table | Column | Type | Constraints | Purpose |
|----|-------|--------|------|-------------|----------|
| COL-010-01 | users | display_name | string(200) | nullable | User-friendly display name from LDAP (displayName/cn attribute); fallback to username if LDAP attribute missing; can have duplicates unlike username |

**Migration:** `2026_01_25_add_display_name_to_users_table.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('display_name', 200)->nullable()->after('username');
});
```

**Note:** 
- `username` remains unique identifier for login (from LDAP uid/sAMAccountName)
- `display_name` added for UI display purposes (from LDAP displayName/cn)
- If LDAP displayName missing, ProvisionLdapUser sets display_name = username as fallback
- Existing non-LDAP users will have display_name = null (can be manually set later)

## Spec DSL

```yaml
domain_objects:
  - id: DO-010-01
    name: LdapConfiguration
    fields:
      - name: host
        type: string
        required: true
      - name: port
        type: integer
        default: 389
      - name: base_dn
        type: string
        required: true
      - name: bind_dn
        type: string
        required: true
      - name: bind_password
        type: string
        required: true
        redact: true
      - name: user_filter
        type: string
        default: "(&(objectClass=person)(uid=%s))"
      - name: attr_username
        type: string
        default: "uid"
      - name: attr_email
        type: string
        default: "mail"
      - name: attr_display_name
        type: string
        default: "displayName"
      - name: admin_group_dn
        type: string
        nullable: true
      - name: auto_provision
        type: boolean
        default: true
      - name: use_tls
        type: boolean
        default: true
      - name: tls_verify_peer
        type: boolean
        default: true
      - name: connection_timeout
        type: integer
        default: 5

  - id: DO-010-02
    name: LdapUser
    fields:
      - name: username
        type: string
        required: true
        description: "Unique LDAP identifier (uid/sAMAccountName) for login"
      - name: email
        type: string
        nullable: true
      - name: display_name
        type: string
        nullable: true
        description: "User-friendly name from LDAP displayName/cn; may have duplicates; fallback to username if missing"
      - name: groups
        type: array<string>
        description: "List of group DNs the user belongs to"

routes:
  - id: API-010-01
    method: POST
    path: /api/Session::login
    description: "Modified to support LDAP authentication transparently"
    changes: "Internal implementation only; no API contract changes"

telemetry_events:
  - id: TE-010-01
    event: ldap.auth.attempt
    fields:
      - username (sanitized)
      - method: ldap
      - timestamp
  - id: TE-010-02
    event: ldap.auth.success
    fields:
      - username (sanitized)
      - new_user (boolean)
      - role (admin/user)
      - duration_ms
  - id: TE-010-03
    event: ldap.auth.failure
    fields:
      - username (sanitized)
      - reason (enum)
      - duration_ms
  - id: TE-010-04
    event: ldap.user.created
    fields:
      - username (sanitized)
      - source: ldap
  - id: TE-010-05
    event: ldap.user.sync
    fields:
      - username (sanitized)
      - attributes_synced (count)
  - id: TE-010-06
    event: ldap.role.assigned
    fields:
      - username (sanitized)
      - role (admin/user)
      - source (ldap_group/default)
  - id: TE-010-07
    event: ldap.connection.timeout
    fields:
      - host
      - port
      - timeout_ms
  - id: TE-010-08
    event: ldap.tls.required
    fields:
      - host
      - port
      - tls_available (boolean)

fixtures:
  - id: FX-010-01
    path: tests/Fixtures/ldap-users.php
    purpose: "Sample LDAP user data for mocking"
  - id: FX-010-02
    path: tests/Fixtures/ldap-groups.php
    purpose: "Sample LDAP group data for role mapping tests"

environment_variables:
  - id: ENV-010-01
    name: LDAP_ENABLED
    default: false
    description: "Enable LDAP authentication"
  - id: ENV-010-02
    name: LDAP_HOST
    required_if: LDAP_ENABLED=true
    description: "LDAP server hostname"
  - id: ENV-010-03
    name: LDAP_PORT
    default: 389
    description: "LDAP server port (636 for LDAPS)"
  - id: ENV-010-04
    name: LDAP_BASE_DN
    required_if: LDAP_ENABLED=true
    description: "Base DN for LDAP searches"
  - id: ENV-010-05
    name: LDAP_BIND_DN
    required_if: LDAP_ENABLED=true
    description: "DN for bind user"
  - id: ENV-010-06
    name: LDAP_BIND_PASSWORD
    required_if: LDAP_ENABLED=true
    redact: true
    description: "Password for bind user"
  - id: ENV-010-07
    name: LDAP_USER_FILTER
    default: "(&(objectClass=person)(uid=%s))"
    description: "LDAP filter for user search"
  - id: ENV-010-08
    name: LDAP_ATTR_USERNAME
    default: "uid"
    description: "LDAP attribute for username"
  - id: ENV-010-09
    name: LDAP_ATTR_EMAIL
    default: "mail"
    description: "LDAP attribute for email"
  - id: ENV-010-10
    name: LDAP_ATTR_DISPLAY_NAME
    default: "displayName"
    description: "LDAP attribute for display name"
  - id: ENV-010-11
    name: LDAP_ADMIN_GROUP_DN
    nullable: true
    description: "DN of LDAP group for admin users"
  - id: ENV-010-12
    name: LDAP_AUTO_PROVISION
    default: true
    description: "Auto-create users on first LDAP login"
  - id: ENV-010-13
    name: LDAP_USE_TLS
    default: true
    description: "Require TLS/SSL for LDAP connections"
  - id: ENV-010-14
    name: LDAP_TLS_VERIFY_PEER
    default: true
    description: "Verify TLS certificate"
  - id: ENV-010-15
    name: LDAP_CONNECTION_TIMEOUT
    default: 5
    description: "LDAP connection timeout (seconds)"
```

## Appendix

### LDAP Library Selection

Use **LdapRecord/Laravel** (`ldaprecord/laravel`) package for Laravel integration:
- Actively maintained (Adldap2 is archived/read-only)
- Supports both Active Directory and OpenLDAP
- Provides connection management, TLS support, user/group queries
- Eloquent-style query builder for LDAP
- Laravel-friendly API with service provider and facades
- Handles attribute mapping and group membership checks

Alternative: **php-ldap extension** directly (more control, more work)

**Package:** `ldaprecord/laravel`  
**Documentation:** https://ldaprecord.com/docs/laravel

### Sample .env Configuration

```bash
# LDAP Authentication
LDAP_ENABLED=true
LDAP_HOST=ldap.example.com
LDAP_PORT=389
LDAP_BASE_DN=dc=example,dc=com
LDAP_BIND_DN=cn=lychee-bind,ou=services,dc=example,dc=com
LDAP_BIND_PASSWORD=securepassword
LDAP_USER_FILTER=(&(objectClass=person)(uid=%s))
LDAP_ATTR_USERNAME=uid
LDAP_ATTR_EMAIL=mail
LDAP_ATTR_DISPLAY_NAME=displayName
LDAP_ADMIN_GROUP_DN=cn=lychee-admins,ou=groups,dc=example,dc=com
LDAP_AUTO_PROVISION=true
LDAP_USE_TLS=true
LDAP_TLS_VERIFY_PEER=true
LDAP_CONNECTION_TIMEOUT=5
```

### Active Directory Configuration Example

```bash
# Active Directory
LDAP_ENABLED=true
LDAP_HOST=ad.corp.example.com
LDAP_PORT=389
LDAP_BASE_DN=dc=corp,dc=example,dc=com
LDAP_BIND_DN=CN=Lychee Service,OU=Service Accounts,DC=corp,DC=example,DC=com
LDAP_BIND_PASSWORD=SecureP@ssw0rd
LDAP_USER_FILTER=(&(objectClass=user)(sAMAccountName=%s))
LDAP_ATTR_USERNAME=sAMAccountName
LDAP_ATTR_EMAIL=userPrincipalName
LDAP_ATTR_DISPLAY_NAME=displayName
LDAP_ADMIN_GROUP_DN=CN=Lychee Administrators,OU=Security Groups,DC=corp,DC=example,DC=com
LDAP_AUTO_PROVISION=true
LDAP_USE_TLS=true
LDAP_TLS_VERIFY_PEER=true
LDAP_CONNECTION_TIMEOUT=5
```

### Security Considerations

1. **Bind user permissions:** The LDAP bind user should have minimal permissions (read-only access to user/group attributes)
2. **TLS enforcement:** Always use TLS/SSL in production; set `LDAP_USE_TLS=true`
3. **Certificate validation:** Verify TLS certificates unless using self-signed certs in dev (set `LDAP_TLS_VERIFY_PEER=false` only in dev)
4. **Password handling:** Never log LDAP bind password or user passwords; ensure they're only used for LDAP bind operations
5. **Connection security:** Ensure firewall rules allow LDAP/LDAPS traffic only from Lychee server
6. **Audit logging:** Enable `ldap.auth.*` event logging for security auditing and compliance

---

*Last updated: 2026-01-25*
