# Feature Plan 010 – LDAP Authentication Support

_Linked specification:_ `docs/specs/4-architecture/features/010-ldap-support/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-01-26

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

Enable enterprise users to authenticate against LDAP/Active Directory servers, allowing seamless integration with existing directory infrastructure. Success is measured by:

- **Functional:** LDAP users can log in and are auto-provisioned with correct roles (FR-010-01 through FR-010-07)
- **Performance:** LDAP authentication completes within 2 seconds p95 (NFR-010-02)
- **Security:** No LDAP passwords stored or logged; TLS enforced (NFR-010-05, FR-010-07)
- **Compatibility:** Backward compatible with basic auth; existing users unaffected (NFR-010-03)
- **Observability:** All auth attempts logged with sanitized usernames (TE-010-01 through TE-010-08)
- **Documentation:** Complete .env.example and setup guide available

## Scope Alignment

- **In scope:**
  - LDAP authentication service (connection, bind, attribute retrieval, group membership)
  - User auto-provisioning from LDAP attributes
  - LDAP group to admin/user role mapping
  - Environment variable configuration for all LDAP settings
  - Auth method selection (LDAP first, fallback to basic if both enabled)
  - Connection pooling and error handling
  - TLS/SSL enforcement
  - Telemetry events for all LDAP operations
  - Documentation (.env.example, setup guide, knowledge map)
  - Unit, integration, and feature tests

- **Out of scope:**
  - UI-based LDAP configuration (power users edit .env)
  - LDAP write operations (read-only auth and attribute retrieval)
  - Full user group sync (only admin/user role mapping)
  - SAML/OAuth integration
  - Local password fallback for LDAP users
  - CLI commands for LDAP testing (future enhancement)

## Dependencies & Interfaces

- **Package:** `ldaprecord/laravel` (requires approval for composer dependency)
  - Modern replacement for archived Adldap2 package
  - `App\Services\Auth\LdapService` wraps LdapRecord as facade/adapter (Q-010-07: wrapper pattern for testability and abstraction)
  - Requires `php-ldap` extension
- **Modules:**
  - `App\Services\Auth\LdapService` (new) - Service layer wrapping LdapRecord Connection and query builder
  - `App\DTO\LdapConfiguration` (new) - Validates/transforms .env values before passing to LdapRecord (Q-010-08)
  - `App\DTO\LdapUser` (new) - LDAP user data transfer object
  - `App\Actions\User\ProvisionLdapUser` (new) - User provisioning action
  - `App\Http\Controllers\AuthController` (modified)
  - `App\Providers\AuthServiceProvider` (modified)
  - `App\Models\User` (potentially modified for LDAP user tracking)
- **External:**
  - LDAP/Active Directory server (test environment required)
  - TLS certificates for secure LDAP connections
- **Fixtures:**
  - `tests/Fixtures/ldap-users.php`
  - `tests/Fixtures/ldap-groups.php`
- **Telemetry:**
  - Log channel configuration for LDAP events

## Assumptions & Risks

- **Assumptions:**
  - Power users can configure .env variables correctly
  - LDAP server is accessible from Lychee server (network configuration)
  - php-ldap extension is installed (LdapRecord requires it)
  - Testing uses LdapRecord's test utilities (DirectoryEmulator), no Docker LDAP server needed (Q-010-10)
  - Single `LDAP_USE_TLS` flag covers both LDAPS (port 636) and StartTLS (port 389) based on port number (Q-010-12)

- **Risks / Mitigations:**
  - **Risk:** LDAP server downtime breaks auth for LDAP-only deployments
    - _Mitigation:_ Document fallback strategy; recommend enabling both basic+LDAP for resilience
  - **Risk:** Diverse LDAP schemas require extensive attribute mapping configuration
    - _Mitigation:_ Provide sensible defaults (uid, mail, displayName) plus .env overrides; document common schemas (AD, OpenLDAP)
  - **Risk:** TLS certificate validation issues in development environments
    - _Mitigation:_ Provide `LDAP_TLS_VERIFY_PEER` option (default true for production, false for dev)
  - **Risk:** Performance degradation with slow LDAP servers
    - _Mitigation:_ Implement connection timeout (5s default), connection pooling, caching of group membership

## Implementation Drift Gate

Before implementation begins, verify:
1. All open questions (Q-010-01 through Q-010-12) are resolved ✅
2. Spec reviewed and approved (functional requirements, NFRs, scenarios)
3. Composer dependency approval for `ldaprecord/laravel` ✅
4. Architecture decisions documented: wrapper pattern (Q-010-07), validation DTO (Q-010-08), LdapRecord built-in connection management (Q-010-09), search-first auth flow (Q-010-11), single TLS flag (Q-010-12)
5. Branch coverage upfront: failing tests for all scenarios (S-010-01 through S-010-10) staged before implementation

**Evidence:** Analysis gate checklist in [docs/specs/5-operations/analysis-gate-checklist.md](../../5-operations/analysis-gate-checklist.md)

## Increment Map

### I1 – Dependency Setup and Configuration Structure (30 min)

- _Goal:_ Install LDAP library, create configuration structure, add database column for display names
- _Preconditions:_ User approval for composer dependency
- _Spec coverage:_ NFR-010-01, ENV-010-01 through ENV-010-15, COL-010-01
- _Steps:_
  1. Install LdapRecord: `composer require ldaprecord/laravel`
  2. Publish LdapRecord config: `php artisan vendor:publish --provider="LdapRecord\Laravel\LdapRecordServiceProvider"`
  3. Configure `config/ldap.php` to load from .env variables
  4. Create migration `2026_01_25_add_display_name_to_users_table.php` to add `display_name` column (nullable string(200))
  5. Update `App\Models\User` model:
     - Add `@property string|null $display_name` to docblock
     - Add `'display_name'` to `$fillable` array
  6. Create `App\DTO\LdapConfiguration` value object (DO-010-01) as validation/transformation layer (Q-010-08):
     - `LdapConfiguration::fromEnv()` validates all .env values
     - Type-safe properties (string $host, int $port, bool $enabled, etc.)
     - Throws exceptions for invalid config when LDAP enabled
     - Values passed to LdapRecord config after validation
  7. Update `.env.example` with all LDAP variables (ENV-010-01 through ENV-010-15)
  8. Document TLS configuration: `LDAP_USE_TLS=true` with port 636 = LDAPS, port 389 = StartTLS (Q-010-12)
  9. Document username vs display_name: username = unique login ID (uid/sAMAccountName), display_name = friendly UI name (displayName/cn)
- _Commands:_ `composer require ldaprecord/laravel`, `php artisan vendor:publish --provider="LdapRecord\Laravel\LdapRecordServiceProvider"`, `php artisan migrate`
- _Exit:_ Configuration structure exists; .env.example documented; display_name column added

### I2 – LDAP Service Core (Connection, Search, and Bind) (60 min)

- _Goal:_ Implement LDAP connection, TLS, search-first pattern, and credential validation foundation for authenticate() method
- _Preconditions:_ I1 complete
- _Spec coverage:_ FR-010-01, FR-010-06, FR-010-07, NFR-010-04
- _Steps:_
  1. Create `App\Services\Auth\LdapService` class as wrapper/facade over LdapRecord (Q-010-07)
  2. Inject `LdapConfiguration` DTO into constructor
  3. Implement `connect()` private method wrapping LdapRecord Connection with TLS enforcement (FR-010-07)
  4. Configure LdapRecord's built-in connection pooling/timeout (no custom pooling needed, Q-010-09)
  5. Create `App\DTO\LdapUser` DTO with fields: username, userDn, email (nullable), display_name (nullable)
     - Note: groups NOT included in DTO; queried separately in ProvisionLdapUser
  6. Implement core `authenticate(string $username, string $password): ?LdapUser` method skeleton:
     - Step 1: Search LDAP using `LDAP_USER_FILTER` to find user entry
     - Step 2: Extract userDn from search result
     - Step 3: Bind with userDn + password to validate credentials
     - Return null on failure (user not found or bind failed)
     - Return minimal LdapUser(username, userDn) on success (attributes added in I3)
  7. Implement `searchUser(string $username): ?string` private helper returning userDn
  8. Write unit tests using LdapRecord's test utilities (DirectoryEmulator, Q-010-10)
- _Tests:_
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testConnectSuccess`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testConnectTlsRequired`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testSearchUserSuccess`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testSearchUserNotFound`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testAuthenticateBindSuccess`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testAuthenticateBindFailure`
- _Commands:_ `php artisan test --filter=LdapServiceTest`
- _Exit:_ LDAP connection, search, and bind working; authenticate() returns minimal LdapUser; tests green

### I3 – Add Attribute Retrieval to authenticate() (60 min)

- _Goal:_ Extend authenticate() to retrieve and populate user attributes in LdapUser DTO
- _Preconditions:_ I2 complete (authenticate() returns minimal LdapUser with username and userDn; DTO created)
- _Spec coverage:_ FR-010-02, DO-010-02
- _Steps:_
  1. Implement `retrieveAttributes(string $userDn): array` private helper method
     - Fetch attributes using LdapRecord query
     - Use configurable attribute mappings from .env (mail, displayName)
     - Handle missing attributes gracefully (email nullable, display_name fallback to username)
  2. Update `authenticate()` method to call `retrieveAttributes()` after successful bind:
     - After bind succeeds and userDn is known
     - Populate email and display_name in LdapUser DTO
     - Return LdapUser(username, userDn, email, display_name)
  3. Write unit tests for attribute retrieval with various LDAP responses
- _Tests:_
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testAuthenticateWithAttributes`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testAuthenticateMissingEmail`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testAuthenticateMissingDisplayName`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testAuthenticateCustomAttributeMapping`
- _Commands:_ `php artisan test --filter=LdapServiceTest`
- _Exit:_ authenticate() returns LdapUser with username, userDn, email, display_name; attribute fallback logic verified

### I4 – LdapService Group Membership Helper (45 min)

- _Goal:_ Create group membership query helper for use by ProvisionLdapUser action
- _Preconditions:_ I3 complete (authenticate() returns LdapUser with username, email, display_name)
- _Spec coverage:_ FR-010-03
- _Steps:_
  1. Implement `queryGroups(string $userDn): array` public helper method
     - Queries group membership using userDn (Q-010-11)
     - Returns array of group DNs the user belongs to
     - Can be called by ProvisionLdapUser when creating/updating users
  2. Implement `isUserInAdminGroup(array $groupDns): bool` public helper method
     - Checks if any group DN matches `LDAP_ADMIN_GROUP_DN` config
     - Returns true if admin group match found, false otherwise
     - Handles case where no admin group configured (returns false)
  3. Note: `authenticate()` does NOT call these methods - groups only queried during provisioning
  4. Write unit tests for group membership queries using LdapRecord test utilities
- _Tests:_
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testQueryGroupsSuccess`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testIsUserInAdminGroupTrue`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testIsUserInAdminGroupFalse`
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testIsUserInAdminGroupNoConfigured`
- _Commands:_ `php artisan test --filter=LdapServiceTest`
- _Exit:_ Group membership helpers available; authenticate() complete and returns LdapUser(username, email, display_name); tests green

### I5 – User Auto-Provisioning with Group-Based Role Assignment (75 min)

- _Goal:_ Create or update Lychee user from LdapUser DTO and query groups to assign roles
- _Preconditions:_ I4 complete (LdapService::queryGroups() and isUserInAdminGroup() available)
- _Spec coverage:_ FR-010-04, FR-010-03, S-010-01, S-010-02
- _Steps:_
  1. Create `App\Actions\User\ProvisionLdapUser` action accepting LdapUser DTO and LdapService
  2. Implement logic to find existing user by username or create new user
  3. Sync attributes from LdapUser DTO:
     - `username` (unique identifier, required)
     - `email` (nullable)
     - `display_name` (already has fallback from I3)
  4. Query LDAP groups and assign role:
     - Extract userDn from LdapUser DTO
     - Call `LdapService::queryGroups(userDn)` to get user's group memberships
     - Call `LdapService::isUserInAdminGroup(groups)` to determine admin role
     - Set `may_administrate` on User model based on result
  5. Respect `LDAP_AUTO_PROVISION` config (reject if disabled and user doesn't exist)
  6. Write unit tests for first-time provisioning, existing user updates, and role assignment
- _Tests:_
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testCreateNewUser`
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testCreateNewUserWithDisplayName`
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testCreateNewUserDisplayNameFallback`
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testUpdateExistingUser`
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testUpdateExistingUserRoleChange`
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testAutoProvisionDisabled`
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testAdminRoleAssignedFromGroup`
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testRegularUserRole`
- _Commands:_ `php artisan test --filter=ProvisionLdapUserTest`
- _Exit:_ User provisioning working; groups queried during provisioning; role assignment from LDAP groups working; auto-provision toggle tested

### I6 – Authentication Controller Integration (60 min)

- _Goal:_ Integrate LDAP authentication into login flow using LdapService::authenticate()
- _Preconditions:_ I5 complete (ProvisionLdapUser action ready)
- _Spec coverage:_ FR-010-01, FR-010-05, API-010-01
- _Steps:_
  1. Modify `AuthController::login()` to attempt LDAP auth if enabled
  2. Implement auth method selection logic (LDAP first, fallback to basic if both enabled)
  3. Call `LdapService::authenticate(username, password)` for LDAP authentication:
     - Returns LdapUser DTO (username, email, display_name, userDn) or null
     - Does NOT include groups (groups queried in ProvisionLdapUser)
  4. On successful LDAP authentication:
     - Pass LdapUser DTO AND LdapService instance to `ProvisionLdapUser` action
     - ProvisionLdapUser queries groups and assigns role
     - Provision/update user in database
     - Establish session for authenticated user
  5. Maintain backward compatibility with basic auth flow
- _Tests:_
  - Tests deferred to I7 (feature tests)
- _Commands:_ (code only, tests in next increment)
- _Exit:_ AuthController modified; authenticate() integration complete; LdapService passed to provisioning; ready for feature testing

### I7 – Feature Tests for LDAP Authentication (90 min)

- _Goal:_ End-to-end tests for all LDAP scenarios
- _Preconditions:_ I6 complete
- _Spec coverage:_ S-010-01 through S-010-10
- _Steps:_
  1. Create `tests/Feature_v2/Auth/LdapAuthenticationTest.php`
  2. Mock LDAP server responses using LdapRecord's DirectoryEmulator test utilities (Q-010-10)
  3. Write tests for all scenarios:
     - S-010-01: First-time LDAP user login (auto-provision)
     - S-010-02: Existing LDAP user login (attribute sync)
     - S-010-03: Invalid LDAP credentials
     - S-010-04: LDAP server unreachable, basic auth fallback
     - S-010-05: LDAP server unreachable, LDAP-only mode
     - S-010-06: LDAP user in admin group
     - S-010-07: LDAP user not in admin group
     - S-010-08: Auto-provision disabled, unknown user
     - S-010-09: Custom attribute mapping
     - S-010-10: TLS required but unavailable
- _Tests:_
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testFirstTimeLdapUserLogin`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testExistingLdapUserLogin`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testInvalidLdapCredentials`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testLdapServerUnreachableWithFallback`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testLdapServerUnreachableLdapOnlyMode`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testLdapUserInAdminGroup`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testLdapUserNotInAdminGroup`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testAutoProvisionDisabled`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testCustomAttributeMapping`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testTlsRequiredButUnavailable`
- _Commands:_ `php artisan test --filter=LdapAuthenticationTest`
- _Exit:_ All LDAP scenarios tested and passing

### I8 – Telemetry and Logging (45 min)

- _Goal:_ Add telemetry events for LDAP operations
- _Preconditions:_ I7 complete
- _Spec coverage:_ TE-010-01 through TE-010-08
- _Steps:_
  1. Add log events in LdapService and AuthController:
     - `ldap.auth.attempt` on login attempt
     - `ldap.auth.success` on successful bind and provisioning
     - `ldap.auth.failure` on auth failure (with reason enum)
     - `ldap.user.created` on new user provisioning
     - `ldap.user.sync` on attribute synchronization
     - `ldap.role.assigned` on role mapping
     - `ldap.connection.timeout` on connection issues
     - `ldap.tls.required` on TLS validation
  2. Ensure all log events sanitize usernames (no passwords, DNs, or sensitive data)
  3. Write tests verifying log events are emitted correctly
- _Tests:_
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testLogEventsEmitted`
- _Commands:_ `php artisan test --filter=LdapServiceTest::testLogEvents`
- _Exit:_ All telemetry events implemented and tested

### I9 – Error Handling and Graceful Degradation (45 min)

- _Goal:_ Robust error handling for LDAP failures without retry logic
- _Preconditions:_ I8 complete
- _Spec coverage:_ NFR-010-06, FR-010-06
- _Steps:_
  1. Implement timeout handling for LDAP connections (5s default, configured via LDAP_CONNECTION_TIMEOUT)
  2. Implement graceful degradation when LDAP unavailable:
     - If LDAP server unreachable and basic auth enabled: fallback to basic auth
     - If LDAP server unreachable and LDAP-only mode: return service unavailable error
  3. Return user-friendly error messages (no LDAP implementation details exposed)
  4. Handle connection failures with appropriate error logging
  5. Write tests for timeout scenarios, connection failures, and fallback logic
- _Tests:_
  - `tests/Unit/Services/Auth/LdapServiceTest.php::testConnectionTimeout`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testGracefulDegradationToBasicAuth`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testLdapOnlyModeServiceUnavailable`
- _Commands:_ `php artisan test --filter=LdapServiceTest::testConnection`
- _Exit:_ Error handling robust; timeout configured; graceful degradation working; no retry logic

### I10 – Documentation and Examples (45 min)

- _Goal:_ Complete documentation for LDAP setup
- _Preconditions:_ I9 complete
- _Spec coverage:_ Documentation Deliverables section
- _Steps:_
  1. Update `.env.example` with all LDAP variables and inline comments
  2. Verify `docker-compose.yaml` LDAP section is complete (already added) ✅
  3. Create `docs/ldap-setup.md` guide:
     - LDAP/Active Directory configuration examples
     - Attribute mapping examples (AD vs OpenLDAP)
     - TLS configuration: LDAPS (port 636) vs StartTLS (port 389) distinction (Q-010-12)
     - Troubleshooting section
     - Security best practices
  4. Update `docs/specs/4-architecture/knowledge-map.md`:
     - Add LDAP service module (wrapper pattern over LdapRecord)
     - Document auth flow integration (search-first pattern)
     - Note php-ldap extension dependency
     - Note architecture decisions: Q-010-07 (wrapper), Q-010-08 (validation DTO), Q-010-11 (search-first)
  5. Update `README.md` to mention LDAP support
- _Exit:_ All documentation complete and reviewed

### I11 – LDAP User Differentiation (60 min)

- _Goal:_ Add `is_ldap` column, expose to frontend, disable credential editing for LDAP users
- _Preconditions:_ I10 complete
- _Spec coverage:_ FR-010-08, COL-010-02, UI-010-04, UI-010-05, S-010-11, S-010-12, S-010-13
- _Steps:_
  1. Migration already created: `2026_01_26_add_is_ldap_to_users_table.php` ✅
  2. Update `App\Models\User`:
     - Add `@property bool $is_ldap` to docblock
     - Confirm `'is_ldap' => 'boolean'` in `$casts` array (already done)
  3. Update `App\Actions\User\ProvisionLdapUser`:
     - Set `is_ldap = true` when creating new LDAP users
     - Keep `is_ldap = true` when updating existing LDAP users
  4. Update `App\Http\Resources\Models\UserResource`:
     - Add `public bool $is_ldap` property
     - Set in constructor: `$this->is_ldap = $user?->is_ldap ?? false`
  5. Update `App\Http\Requests\UserManagement\SetUserSettingsRequest`:
     - Add validation rule to reject username/password changes when user `is_ldap = true`
     - Return appropriate validation error message
  6. Run `php artisan typescript:transform` to generate TypeScript types
  7. Update frontend profile page (search for profile/settings components):
     - Check `userStore.user?.is_ldap` flag
     - Disable username/password input fields if `is_ldap === true`
     - Show message: "User login information are LDAP managed"
  8. Write tests:
     - Unit test: LDAP provisioning sets `is_ldap = true`
     - Feature test: `/api/Auth::user` includes `is_ldap` in response
     - Feature test: Updating LDAP user credentials returns validation error
     - Feature test: Updating non-LDAP user credentials succeeds
- _Tests:_
  - `tests/Unit/Actions/User/ProvisionLdapUserTest.php::testSetsIsLdapFlag`
  - `tests/Feature_v2/Auth/LdapAuthenticationTest.php::testAuthUserIncludesIsLdapFlag`
  - `tests/Feature_v2/UserManagement/SetUserSettingsRequestTest.php::testRejectsLdapUserCredentialChanges`
  - `tests/Feature_v2/UserManagement/SetUserSettingsRequestTest.php::testAllowsNonLdapUserCredentialChanges`
- _Commands:_
  - `php artisan migrate`
  - `php artisan typescript:transform`
  - `php artisan test --filter=ProvisionLdapUserTest::testSetsIsLdapFlag`
  - `php artisan test --filter=LdapAuthenticationTest::testAuthUserIncludesIsLdapFlag`
  - `php artisan test --filter=SetUserSettingsRequestTest`
- _Exit:_ `is_ldap` column added; flag set during provisioning; frontend disables credential editing; validation rejects LDAP user updates; all tests pass

### I12 – Quality Gate and Final Testing (60 min)

- _Goal:_ Run full quality gate and verify all requirements
- _Preconditions:_ I11 complete
- _Spec coverage:_ All FRs, NFRs, Scenarios
- _Steps:_
  1. Run full test suite: `php artisan test`
  2. Run PHPStan: `make phpstan`
  3. Run PHP CS Fixer: `vendor/bin/php-cs-fixer fix`
  4. Run frontend checks: `npm run check`, `npm run format`
  5. Verify all scenarios (S-010-01 through S-010-13) covered by tests
  6. Review telemetry events in logs
  7. Test against actual LDAP server (if available)
  8. Security review: ensure no passwords logged or stored
- _Commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `php artisan test`
  - `npm run check`
  - `make phpstan`
- _Exit:_ All quality gates pass; feature complete

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-010-01 | I7 - testFirstTimeLdapUserLogin | First-time LDAP user, auto-provision |
| S-010-02 | I7 - testExistingLdapUserLogin | Existing user, attribute sync |
| S-010-03 | I7 - testInvalidLdapCredentials | Auth failure handling |
| S-010-04 | I7 - testLdapServerUnreachableWithFallback | Fallback to basic auth |
| S-010-05 | I7 - testLdapServerUnreachableLdapOnlyMode | LDAP-only mode failure |
| S-010-06 | I7 - testLdapUserInAdminGroup | Admin role from LDAP group |
| S-010-07 | I7 - testLdapUserNotInAdminGroup | Regular user role |
| S-010-08 | I7 - testAutoProvisionDisabled | Provisioning toggle |
| S-010-09 | I7 - testCustomAttributeMapping | Configurable attributes |
| S-010-10 | I7 - testTlsRequiredButUnavailable | TLS enforcement |
| S-010-11 | I11 - Frontend test (manual/E2E) | LDAP user profile credential editing disabled |
| S-010-12 | I11 - testRejectsLdapUserCredentialChanges | Admin updating LDAP user validation |
| S-010-13 | I11 - testAllowsNonLdapUserCredentialChanges | Local user credential editing works |
| S-010-11 | I11 - Frontend test (manual/E2E) | LDAP user profile credential editing disabled |
| S-010-12 | I11 - testRejectsLdapUserCredentialChanges | Admin updating LDAP user validation |
| S-010-13 | I11 - testAllowsNonLdapUserCredentialChanges | Local user credential editing works |

## Analysis Gate

**Status:** Pending

**Checklist:**
- [x] All open questions resolved (Q-010-01 through Q-010-12) ✅
  - Q-010-01 through Q-010-06: User decisions captured
  - Q-010-07: Wrapper pattern (LdapService wraps LdapRecord)
  - Q-010-08: LdapConfiguration as validation DTO
  - Q-010-09: Use LdapRecord's built-in connection management
  - Q-010-10: LdapRecord test utilities (no Docker)
  - Q-010-11: Search-first auth flow
  - Q-010-12: Single TLS flag (port determines protocol)
- [ ] Spec reviewed and approved
- [x] Composer dependency approval: `ldaprecord/laravel`
- [x] Testing strategy determined: LdapRecord test utilities
- [ ] Failing tests staged for all scenarios before implementation
8) implemented and tested
- [ ] All non-functional requirements (NFR-010-01 through NFR-010-06) verified
- [ ] All scenarios (S-010-01 through S-010-13) covered by passing tests
- [ ] All telemetry events (TE-010-01 through TE-010-08) implemented
- [ ] Full test suite passes (`php artisan test`)
- [ ] Frontend checks pass (`npm run check`)
- [ ] PHPStan level 6 passes (`make phpstan`)
- [ ] Code formatting passes (`vendor/bin/php-cs-fixer fix`, `npm run format`)
- [ ] TypeScript types generated (`php artisan typescript:transform`)
- [ ] Documentation complete (`.env.example`, `docs/ldap-setup.md`, knowledge map, README)
- [ ] Security review: no passwords logged or stored
- [ ] Backward compatibility verified: existing basic auth users unaffected
- [ ] LDAP user credential editing disabled in profile UI
- [ ] Validation rejects LDAP user credential changes via API
- [ ] Full test suite passes (`php artisan test`)
- [ ] PHPStan level 6 passes (`make phpstan`)
- [ ] Code formatting passes (`vendor/bin/php-cs-fixer fix`)
- [ ] Documentation complete (`.env.example`, `docs/ldap-setup.md`, knowledge map, README)
- [ ] Security review: no passwords logged or stored
- [ ] Backward compatibility verified: existing basic auth users unaffected

## Follow-ups / Backlog

- **CLI command for LDAP testing:** `php artisan ldap:test-connection` to verify LDAP configuration (CLI-010-01)
- **LDAP user import command:** Batch import users from LDAP directory
- **LDAP group sync caching:** Cache group memberships to reduce LDAP queries
- **Multi-LDAP server support:** Failover to secondary LDAP servers
- **LDAP attribute caching:** Cache user attributes to improve login performance
- **Admin UI for LDAP diagnostics:** Show LDAP connection status, recent auth attempts
- **OpenTelemetry tracing:** Distributed tracing for LDAP operations

---

*Last updated: 2026-01-26*
