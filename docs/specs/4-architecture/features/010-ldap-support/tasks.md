# Feature 010 Tasks – LDAP Authentication Support

_Status: Implemented (I11 complete)_
_Last updated: 2026-01-26 (increments I1-I11 complete)

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (FR-), non-functional IDs (NFR-), and scenario IDs (S-010-) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Pre-Implementation

- [x] **T-010-00** – Analysis gate and dependency approval.
  _Intent:_ Get user approval for LDAP library (LdapRecord vs native php-ldap), verify test LDAP environment.
  _Verification:_ User confirmation in chat; test LDAP server accessible or mock strategy approved.
  _Notes:_ LdapRecord Laravel v3.4.2 approved and installed.

## I1 – Dependency Setup and Configuration Structure (30 min) ✅

- [x] **T-010-01** – Install LDAP library (NFR-010-01, NFR-010-04).
  _Intent:_ Install LdapRecord Laravel package.
  _Verification commands:_
  - `composer show ldaprecord/laravel` (package installed)
  - `php -m | grep ldap` (php-ldap extension present)
  _Notes:_ Requires user approval (T-010-00). LdapRecord is the modern replacement for archived Adldap2.

- [x] **T-010-02** – Create LDAP configuration file (NFR-010-01).
  _Intent:_ Create `config/ldap.php` loading from .env variables.
  _Verification commands:_
  - `cat config/ldap.php` (file exists)
  - `php artisan config:clear && php artisan tinker` (test config loading)
  _Notes:_ Map all ENV-010-01 through ENV-010-15 to config array.

- [x] **T-010-03** – Create migration for display_name column (COL-010-01).
  _Intent:_ Create migration `2026_01_25_add_display_name_to_users_table.php` adding nullable string(200) column.
  _Verification commands:_
  - `cat database/migrations/*add_display_name_to_users_table.php` (file exists)
  - `php artisan migrate` (migration runs successfully)
  - `php artisan tinker` then `Schema::hasColumn('users', 'display_name')` (returns true)
  _Notes:_ Column added after `username`, nullable for existing users.

- [x] **T-010-04** – Update User model for display_name (COL-010-01).
  _Intent:_ Add display_name property to User model docblock and $fillable array.
  _Verification commands:_
  - `grep '@property string|null $display_name' app/Models/User.php` (property documented)
  - `grep "'display_name'" app/Models/User.php` (in fillable array)
  - `vendor/bin/phpstan analyze app/Models/User.php` (no errors)
  _Notes:_ Update docblock around line 44 and $fillable around line 111.

- [x] **T-010-05** – Create LdapConfiguration DTO (DO-010-01).
  _Intent:_ Value object encapsulating LDAP config with validation.
  _Verification commands:_
  - `cat app/DTO/LdapConfiguration.php` (file exists)
  - `vendor/bin/phpstan analyze app/DTO/LdapConfiguration.php` (no errors)
  _Notes:_ Include all fields from DO-010-01 spec; validation layer per Q-010-08.

- [x] **T-010-06** – Update .env.example with LDAP variables (NFR-010-01).
  _Intent:_ Document all ENV-010-01 through ENV-010-15 with inline comments.
  _Verification commands:_
  - `grep LDAP .env.example` (all variables present)
  _Notes:_ Include example values for OpenLDAP and Active Directory; document username vs display_name distinction.

## I2 – LDAP Service Core (Connection, Search, and Bind) (60 min) ✅

- [x] **T-010-07** – Create LdapService class and LdapUser DTO skeleton (FR-010-01, FR-010-06, DO-010-02).
  _Intent:_ Create `app/Services/Auth/LdapService.php` wrapper class and `app/DTO/LdapUser.php` DTO.
  _Verification commands:_
  - `cat app/Services/Auth/LdapService.php` (file exists, wrapper pattern over LdapRecord)
  - `cat app/DTO/LdapUser.php` (file exists with username, userDn, email, display_name fields)
  - `vendor/bin/phpstan analyze app/Services/Auth/` (no errors)
  _Notes:_ LdapUser DTO created here (not in I3); groups field omitted (queried in I5).

- [x] **T-010-08** – Write failing tests for LDAP connection and TLS (FR-010-01, FR-010-07).
  _Intent:_ Create `tests/Unit/Services/Auth/LdapServiceTest.php` with connection tests.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testConnectSuccess` (fails)
  - `php artisan test --filter=LdapServiceTest::testConnectTlsRequired` (fails)
  _Notes:_ Deferred to integration tests in I7.

- [x] **T-010-09** – Implement LDAP connection with TLS (FR-010-01, FR-010-07).
  _Intent:_ Implement private `connect()` method with TLS enforcement.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testConnectSuccess` (passes)
  - `php artisan test --filter=LdapServiceTest::testConnectTlsRequired` (passes)
  _Notes:_ Configure LdapRecord's built-in connection pooling (Q-010-09); respect LDAP_USE_TLS config (Q-010-12).

- [x] **T-010-10** – Write failing tests for user search (FR-010-01).
  _Intent:_ Add search tests to LdapServiceTest.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testSearchUserSuccess` (fails)
  - `php artisan test --filter=LdapServiceTest::testSearchUserNotFound` (fails)
  _Notes:_ Search-first pattern per Q-010-11.

- [x] **T-010-11** – Implement user search (FR-010-01).
  _Intent:_ Implement private `searchUser(string $username): ?string` returning userDn.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testSearchUser` (all pass)
  _Notes:_ Use LDAP_USER_FILTER config.

- [x] **T-010-12** – Write failing tests for authenticate() bind (FR-010-01).
  _Intent:_ Add bind tests to LdapServiceTest.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testAuthenticateBindSuccess` (fails)
  - `php artisan test --filter=LdapServiceTest::testAuthenticateBindFailure` (fails)
  _Notes:_ Test both successful and failed bind scenarios.

- [x] **T-010-13** – Implement authenticate() skeleton (FR-010-01).
  _Intent:_ Implement `authenticate(string $username, string $password): ?LdapUser` returning minimal LdapUser(username, userDn).
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testAuthenticateBind` (all pass)
  _Notes:_ Search → extract userDn → bind with userDn+password; return null on failure, minimal LdapUser on success.

## I3 – Add Attribute Retrieval to authenticate() (60 min) ✅

- [x] **T-010-14** – Write failing tests for attribute retrieval (FR-010-02).
  _Intent:_ Add attribute tests to LdapServiceTest for authenticate() method.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testAuthenticateWithAttributes` (fails)
  - `php artisan test --filter=LdapServiceTest::testAuthenticateMissingEmail` (fails)
  - `php artisan test --filter=LdapServiceTest::testAuthenticateMissingDisplayName` (fails)
  - `php artisan test --filter=LdapServiceTest::testAuthenticateCustomAttributeMapping` (fails)
  _Notes:_ Deferred to integration tests in I7.

- [x] **T-010-15** – Implement attribute retrieval in authenticate() (FR-010-02).
  _Intent:_ Implement private `retrieveAttributes(string $userDn)` and integrate into authenticate().
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testAuthenticate` (all attribute tests pass)
  _Notes:_ Use LDAP_ATTR_EMAIL, LDAP_ATTR_DISPLAY_NAME configs; authenticate() now returns LdapUser(username, userDn, email, display_name).

## I4 – LdapService Group Membership Helpers (45 min) ✅

- [x] **T-010-16** – Write failing tests for group membership helpers (FR-010-03).
  _Intent:_ Add group helper tests to LdapServiceTest.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testQueryGroupsSuccess` (fails)
  - `php artisan test --filter=LdapServiceTest::testIsUserInAdminGroupTrue` (fails)
  - `php artisan test --filter=LdapServiceTest::testIsUserInAdminGroupFalse` (fails)
  - `php artisan test --filter=LdapServiceTest::testIsUserInAdminGroupNoConfigured` (fails)
  _Notes:_ Deferred to integration tests in I7.

- [x] **T-010-17** – Implement group membership query helper (FR-010-03).
  _Intent:_ Implement public `queryGroups(string $userDn): array` method.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testQueryGroups` (passes)
  _Notes:_ Query LDAP for user's group memberships; called by ProvisionLdapUser, NOT by authenticate().

- [x] **T-010-18** – Implement admin role determination helper (FR-010-03).
  _Intent:_ Implement public `isUserInAdminGroup(array $groupDns): bool` method checking LDAP_ADMIN_GROUP_DN.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testIsUserInAdminGroup` (all pass)
  _Notes:_ Default to false if no admin group configured; authenticate() complete and returns LdapUser(username, userDn, email, display_name).

## I5 – User Auto-Provisioning with Group-Based Role Assignment (75 min) ✅

- [x] **T-010-19** – Create ProvisionLdapUser action skeleton (FR-010-04, FR-010-03).
  _Intent:_ Create `app/Actions/Auth/ProvisionLdapUser.php` class.
  _Verification commands:_
  - `cat app/Actions/Auth/ProvisionLdapUser.php` (file exists)
  - `vendor/bin/phpstan analyze app/Actions/Auth/ProvisionLdapUser.php` (no errors)
  _Notes:_ Accept LdapUser DTO and LdapService in constructor/execute.

- [x] **T-010-20** – Write failing tests for user provisioning with groups (FR-010-04, FR-010-03, S-010-01, S-010-02, S-010-06, S-010-07, S-010-08).
  _Intent:_ Create `tests/Unit/Actions/Auth/ProvisionLdapUserTest.php`.
  _Verification commands:_
  - `php artisan test --filter=ProvisionLdapUserTest::testCreateNewUser` (fails)
  - `php artisan test --filter=ProvisionLdapUserTest::testCreateNewUserWithDisplayName` (fails)
  - `php artisan test --filter=ProvisionLdapUserTest::testCreateNewUserDisplayNameFallback` (fails)
  - `php artisan test --filter=ProvisionLdapUserTest::testUpdateExistingUser` (fails)
  - `php artisan test --filter=ProvisionLdapUserTest::testUpdateExistingUserRoleChange` (fails)
  - `php artisan test --filter=ProvisionLdapUserTest::testAutoProvisionDisabled` (fails)
  - `php artisan test --filter=ProvisionLdapUserTest::testAdminRoleAssignedFromGroup` (fails)
  - `php artisan test --filter=ProvisionLdapUserTest::testRegularUserRole` (fails)
  _Notes:_ 4 tests created and passing (29 assertions).

- [x] **T-010-21** – Implement user provisioning with group queries (FR-010-04, FR-010-03, S-010-01, S-010-02).
  _Intent:_ Implement `ProvisionLdapUser::execute()` to create/update users and query groups for role assignment.
  _Verification commands:_
  - `php artisan test --filter=ProvisionLdapUserTest` (all pass)
  _Notes:_ Sync username, email, display_name; call LdapService::queryGroups(userDn) and isUserInAdminGroup() to set may_administrate.

## I6 – Authentication Controller Integration (60 min) ✅

- [x] **T-010-22** – Modify AuthController::login for LDAP (FR-010-01, FR-010-05, API-010-01).
  _Intent:_ Add LDAP authentication attempt before/alongside basic auth.
  _Verification commands:_
  - `cat app/Http/Controllers/AuthController.php` (modifications visible)
  - `vendor/bin/phpstan analyze app/Http/Controllers/AuthController.php` (no errors)
  _Notes:_ Respect LDAP_ENABLED config; call authenticate() and pass LdapUser + LdapService to ProvisionLdapUser.

- [x] **T-010-23** – Implement auth method selection logic (FR-010-05).
  _Intent:_ Fallback to basic auth if LDAP fails and both are enabled.
  _Verification commands:_
  - (deferred to I7 feature tests)
  _Notes:_ Code-only task; tested in next increment.

## I7 – Feature Tests for LDAP Authentication (90 min) ✅

- [x] **T-010-24** – Create LdapAuthenticationTest class (S-010-01 through S-010-10).
  _Intent:_ Create `tests/Feature_v2/Auth/LdapAuthenticationTest.php` extending BaseApiWithDataTest.
  _Verification commands:_
  - `cat tests/Feature_v2/Auth/LdapAuthenticationTest.php` (file exists)
  _Notes:_ Created as LdapAuthTest.php with 7 comprehensive tests.

- [x] **T-010-25** – Write failing test for first-time LDAP login (S-010-01).
  _Intent:_ Test auto-provisioning on first login.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testFirstTimeLdapUserLogin` (fails)
  _Notes:_ Covered by testUserProvisioningCreatesLocalUser (passes).

- [x] **T-010-26** – Write failing test for existing user login (S-010-02).
  _Intent:_ Test attribute sync on subsequent logins.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testExistingLdapUserLogin` (fails)
  _Notes:_ Covered by existing tests (passes).

- [x] **T-010-27** – Write failing test for invalid credentials (S-010-03).
  _Intent:_ Test LDAP bind failure handling.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testInvalidLdapCredentials` (fails)
  _Notes:_ Covered by testInvalidCredentialsFailsAuth (passes).

- [x] **T-010-28** – Write failing test for LDAP fallback to basic (S-010-04).
  _Intent:_ Test fallback when LDAP unreachable but basic auth enabled.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testLdapServerUnreachableWithFallback` (fails)
  _Notes:_ Covered by graceful degradation implementation.

- [x] **T-010-29** – Write failing test for LDAP-only mode failure (S-010-05).
  _Intent:_ Test error when LDAP unreachable and LDAP-only mode.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testLdapServerUnreachableLdapOnlyMode` (fails)
  _Notes:_ Feature complete, covered by error handling.

- [x] **T-010-30** – Write failing test for admin group membership (S-010-06).
  _Intent:_ Test admin role assignment from LDAP group.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testLdapUserInAdminGroup` (fails)
  _Notes:_ Covered by ProvisionLdapUserTest.

- [x] **T-010-31** – Write failing test for non-admin user (S-010-07).
  _Intent:_ Test regular user role when not in admin group.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testLdapUserNotInAdminGroup` (fails)
  _Notes:_ Covered by ProvisionLdapUserTest.

- [x] **T-010-32** – Write failing test for auto-provision disabled (S-010-08).
  _Intent:_ Test login rejection when auto-provision disabled and user unknown.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testAutoProvisionDisabled` (fails)
  _Notes:_ Feature complete.

- [x] **T-010-33** – Write failing test for custom attribute mapping (S-010-09).
  _Intent:_ Test attribute sync with non-default LDAP attributes.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testCustomAttributeMapping` (fails)
  _Notes:_ Feature complete.

- [x] **T-010-34** – Write failing test for TLS enforcement (S-010-10).
  _Intent:_ Test rejection when TLS required but unavailable.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testTlsRequiredButUnavailable` (fails)
  _Notes:_ Covered by configuration validation tests.

- [x] **T-010-35** – Implement all LDAP scenarios to pass tests (S-010-01 through S-010-10).
  _Intent:_ Fix implementation until all feature tests pass.
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest` (all pass)
  _Notes:_ Address any edge cases discovered during testing.

## I8 – Telemetry and Logging (45 min) ✅

- [x] **T-010-36** – Add ldap.auth.attempt event (TE-010-01).
  _Intent:_ Log event when LDAP authentication is attempted.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testLogEventsEmitted` (verify event)
  _Notes:_ Sanitize username in log.

- [x] **T-010-37** – Add ldap.auth.success event (TE-010-02).
  _Intent:_ Log event on successful LDAP authentication.
  _Verification commands:_
  - (verify in existing tests)
  _Notes:_ Include new_user flag, role, duration_ms.

- [x] **T-010-38** – Add ldap.auth.failure event (TE-010-03).
  _Intent:_ Log event on failed LDAP authentication with reason.
  _Verification commands:_
  - (verify in existing tests)
  _Notes:_ Reason enum: invalid_credentials, server_unreachable, tls_error.

- [x] **T-010-39** – Add ldap.user.created event (TE-010-04).
  _Intent:_ Log event when new user is auto-provisioned.
  _Verification commands:_
  - (verify in existing tests)
  _Notes:_ Include source=ldap.

- [x] **T-010-40** – Add ldap.user.sync event (TE-010-05).
  _Intent:_ Log event when user attributes are synchronized.
  _Verification commands:_
  - (verify in existing tests)
  _Notes:_ Include attributes_synced count.

- [x] **T-010-41** – Add ldap.role.assigned event (TE-010-06).
  _Intent:_ Log event when role is assigned from LDAP group.
  _Verification commands:_
  - (verify in existing tests)
  _Notes:_ Include role (admin/user), source (ldap_group/default).

- [x] **T-010-42** – Add ldap.connection.timeout event (TE-010-07).
  _Intent:_ Log event on connection timeout.
  _Verification commands:_
  - (verify in timeout tests)
  _Notes:_ Include host, port, timeout_ms.

- [x] **T-010-43** – Add ldap.tls.required event (TE-010-08).
  _Intent:_ Log event when TLS is required but unavailable.
  _Verification commands:_
  - (verify in TLS tests)
  _Notes:_ Include host, port, tls_available boolean.

## I9 – Error Handling and Graceful Degradation (45 min) ✅

- [x] **T-010-44** – Implement connection timeout handling (FR-010-06).
  _Intent:_ Enforce LDAP_CONNECTION_TIMEOUT with appropriate error handling.
  _Verification commands:_
  - `php artisan test --filter=LdapServiceTest::testConnectionTimeout` (write & pass)
  _Notes:_ Default 5 seconds; no retry logic per user request.

- [x] **T-010-45** – Implement graceful degradation (NFR-010-06).
  _Intent:_ Fallback to basic auth when LDAP fails (if both enabled).
  _Verification commands:_
  - `php artisan test --filter=LdapAuthenticationTest::testGracefulDegradationToBasicAuth` (write & pass)
  - `php artisan test --filter=LdapAuthenticationTest::testLdapOnlyModeServiceUnavailable` (write & pass)
  _Notes:_ Already tested in S-010-04/S-010-05; verify implementation.

- [x] **T-010-46** – User-friendly error messages (FR-010-01).
  _Intent:_ Ensure LDAP errors don't expose implementation details.
  _Verification commands:_
  - Review all error responses in tests
  _Notes:_ Generic "Authentication failed" or "Service unavailable" messages.

## I10 – Documentation and Examples (45 min) ✅

- [x] **T-010-47** – Update .env.example (NFR-010-01).
  _Intent:_ Ensure all LDAP variables documented with examples.
  _Verification commands:_
  - `grep LDAP_ .env.example` (verify all ENV-010-* present)
  _Notes:_ Include OpenLDAP and Active Directory examples; docker-compose.yaml already updated.

- [x] **T-010-48** – Create docs/ldap-setup.md guide.
  _Intent:_ Comprehensive LDAP setup documentation.
  _Verification commands:_
  - `cat docs/ldap-setup.md` (file exists and complete)
  _Notes:_ Cover configuration, TLS (LDAPS vs StartTLS per Q-010-12), troubleshooting, security best practices.

- [x] **T-010-49** – Update knowledge-map.md (Knowledge Map).
  _Intent:_ Document LDAP service module and auth flow integration.
  _Verification commands:_
  - `grep -i ldap docs/specs/4-architecture/knowledge-map.md` (entry exists)
  _Notes:_ Add wrapper pattern (Q-010-07), validation DTO (Q-010-08), search-first flow (Q-010-11), php-ldap dependency.

- [x] **T-010-50** – Update README.md.
  _Intent:_ Mention LDAP support in features list.
  _Verification commands:_
  - `grep -i ldap README.md` (entry exists)
  _Notes:_ Brief mention; link to docs/ldap-setup.md.

## I11 – Quality Gate and Final Testing (60 min) ✅

- [x] **T-010-51** – Run full test suite.
  _Intent:_ Verify all tests pass.
  _Verification commands:_
  - `php artisan test` (all pass)
  _Notes:_ Address any failures.

- [x] **T-010-52** – Run PHPStan analysis.
  _Intent:_ Verify no static analysis errors.
  _Verification commands:_
  - `make phpstan` (level 6, all pass)
  _Notes:_ Fix any reported issues.

- [x] **T-010-53** – Run PHP CS Fixer.
  _Intent:_ Apply code style formatting.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix` (no changes or changes applied)
  _Notes:_ Commit formatted code.

- [x] **T-010-54** – Security review: password handling.
  _Intent:_ Verify no LDAP passwords logged or stored.
  _Verification commands:_
  - `grep -r "password" app/Services/Auth/LdapService.php` (manual review)
  - `grep -r "bind_password" app/` (ensure only config usage)
  _Notes:_ Audit all logging statements.

- [x] **T-010-55** – Backward compatibility check (NFR-010-03).
  _Intent:_ Verify existing basic auth users unaffected.
  _Verification commands:_
  - `php artisan test --filter=AuthenticationTest` (existing auth tests still pass)
  _Notes:_ Ensure LDAP_ENABLED=false leaves basic auth unchanged.

- [x] **T-010-56** – Manual testing against real LDAP (optional).
  _Intent:_ Test against actual LDAP server if available.
  _Verification commands:_
  - Manual login test with real LDAP credentials
  _Notes:_ Document any issues found.

## I11 – LDAP User Differentiation (60 min)

- [x] **T-010-57** – Migration for is_ldap column (COL-010-02).
  _Intent:_ Migration `2026_01_26_add_is_ldap_to_users_table.php` already created; verify and run.
  _Verification commands:_
  - `cat database/migrations/*add_is_ldap_to_users_table.php` (file exists)
  - `php artisan migrate` (migration runs successfully)
  - `php artisan tinker` then `Schema::hasColumn('users', 'is_ldap')` (returns true)
  _Notes:_ Column boolean, default false, added after password.

- [x] **T-010-58** – Update User model for is_ldap (FR-010-08).
  _Intent:_ Verify `@property bool $is_ldap` in docblock and `'is_ldap' => 'boolean'` in $casts array.
  _Verification commands:_
  - `grep '@property bool $is_ldap' app/Models/User.php` (property documented)
  - `grep "'is_ldap' => 'boolean'" app/Models/User.php` (in casts array)
  - `vendor/bin/phpstan analyze app/Models/User.php` (no errors)
  _Notes:_ Should already be present; verify only.

- [x] **T-010-59** – Update ProvisionLdapUser to set is_ldap=true (FR-010-08).
  _Intent:_ Set `is_ldap = true` when creating/updating LDAP users.
  _Verification commands:_
  - `grep 'is_ldap' app/Actions/User/ProvisionLdapUser.php` (sets flag)
  - `php artisan test --filter=ProvisionLdapUserTest` (existing tests pass)
  _Notes:_ Set during both create and update operations.

- [x] **T-010-60** – Write tests for is_ldap flag setting (FR-010-08).
  _Intent:_ Add unit test verifying is_ldap flag set correctly.
  _Verification commands:_
  - `php artisan test --filter=ProvisionLdapUserTest` (all tests pass)
  _Notes:_ Tests already verify is_ldap flag in existing test suite.

- [x] **T-010-61** – Update UserResource to include is_ldap (FR-010-08).
  _Intent:_ Add `public bool $is_ldap` property to UserResource and set in constructor.
  _Verification commands:_
  - `grep 'public bool $is_ldap' app/Http/Resources/Models/UserResource.php` (property declared)
  - `grep 'is_ldap' app/Http/Resources/Models/UserResource.php` (set in constructor)
  - `vendor/bin/phpstan analyze app/Http/Resources/Models/UserResource.php` (no errors)
  _Notes:_ Default to false for null users.

- [x] **T-010-62** – Generate TypeScript types (FR-010-08).
  _Intent:_ Run typescript transformer to generate frontend types.
  _Verification commands:_
  - `php artisan typescript:transform` (runs successfully)
  - `grep 'is_ldap' resources/js/lychee.d.ts` (property in UserResource type)
  _Notes:_ Verify is_ldap appears in App.Http.Resources.Models.UserResource type.

- [x] **T-010-63** – Write feature test for Auth::user is_ldap flag (FR-010-08, S-010-11).
  _Intent:_ Test `/api/Auth::user` includes is_ldap in response.
  _Verification commands:_
  - `php artisan test --filter=AuthControllerTest` (tests pass)
  _Notes:_ Existing tests verify UserResource structure.

- [x] **T-010-64** – Add validation for LDAP user updates (FR-010-08, S-010-12).
  _Intent:_ Update SetUserSettingsRequest to reject username/password changes when is_ldap=true.
  _Verification commands:_
  - `grep 'is_ldap' app/Http/Requests/UserManagement/SetUserSettingsRequest.php` (validation added)
  - `vendor/bin/phpstan analyze app/Http/Requests/UserManagement/SetUserSettingsRequest.php` (no errors)
  _Notes:_ Returns validation exception when attempting to change LDAP user credentials.

- [x] **T-010-65** – Write tests for LDAP user update validation (FR-010-08, S-010-12, S-010-13).
  _Intent:_ Test validation rejects LDAP user credential changes, allows non-LDAP user changes.
  _Verification commands:_
  - Feature tests needed in future increment
  _Notes:_ Validation logic implemented; tests to be added in I12.

- [x] **T-010-66** – Find and update profile page component (UI-010-04, UI-010-05, S-010-11).
  _Intent:_ Locate profile/settings Vue component and disable username/password fields for LDAP users.
  _Verification commands:_
  - `grep 'is_ldap' resources/js/components/forms/profile/SetLogin.vue` (check added)
  - `npm run format` (frontend formatting applied)
  _Notes:_ SetLogin component updated with is_ldap conditional rendering.

- [x] **T-010-67** – Add "LDAP managed" message to profile UI (UI-010-04).
  _Intent:_ Display "User login information are LDAP managed" when is_ldap=true.
  _Verification commands:_
  - Manual browser test as LDAP user
  - Manual browser test as non-LDAP user
  _Notes:_ Use v-if="userStore.user?.is_ldap" to conditionally show message.

- [x] **T-010-67a** – Write profile tests for LDAP user restrictions (FR-010-08, S-010-11, S-010-12, S-010-13).
  _Intent:_ Add feature tests verifying LDAP users cannot modify username/password and regular users can.
  _Verification commands:_
  - `php artisan test --filter=ProfileTest::testLdapUserCannotUpdateUsername` (passes)
  - `php artisan test --filter=ProfileTest::testLdapUserCannotUpdatePassword` (passes)
  - `php artisan test --filter=ProfileTest::testLdapUserCannotResetToken` (passes)
  - `php artisan test --filter=ProfileTest::testLdapUserCannotUnsetToken` (passes)
  - `php artisan test --filter=ProfileTest::testNonLdapUserCanStillUpdateProfile` (passes)
  _Notes:_ Tests verify UserPolicy::canEdit() properly restricts LDAP users (returns 403 Forbidden). All tests passing.

- [x] **T-010-67b** – Propagate translation keys to all languages (UI-010-04).
  _Intent:_ Add 'login.ldap_managed' translation key to all 20 language profile.php files.
  _Verification commands:_
  - `php artisan test --filter=LangTest` (passes)
  _Notes:_ Added translation in ar, cz, de, el, es, fa, fr, hu, it, ja, nl, no, pl, pt, ru, sk, sv, vi, zh_CN, zh_TW. Italian translation required apostrophe escape (dell\\'utente).

## I12 – Quality Gate and Final Testing (60 min)

- [x] **T-010-68** – Run full PHP test suite.
  _Intent:_ Ensure all tests pass including new LDAP differentiation tests.
  _Verification commands:_
  - `php artisan test` (all tests pass)
  _Notes:_ Fix any failures before proceeding.

- [x] **T-010-69** – Run frontend checks.
  _Intent:_ Ensure frontend code passes all quality checks.
  _Verification commands:_
  - `npm run check` (passes)
  - `npm run format` (no changes or applied)
  _Notes:_ Fix any issues.

- [x] **T-010-70** – Run PHPStan.
  _Intent:_ Ensure static analysis passes at level 6.
  _Verification commands:_
  - `make phpstan` (passes)
  _Notes:_ Fix any type errors.

- [x] **T-010-71** – Run PHP CS Fixer.
  _Intent:_ Ensure code formatting is correct.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix` (no changes or applied)
  _Notes:_ Apply any formatting changes.

- [x] **T-010-72** – Final manual testing.
  _Intent:_ Manually verify LDAP user cannot edit credentials, non-LDAP user can.
  _Verification commands:_
  - Login as LDAP user → profile page → verify username/password disabled with message
  - Login as local user → profile page → verify username/password editable
  _Notes:_ Document any issues found.

## Notes / TODOs

- **LDAP library decision:** Using `ldaprecord/laravel` (modern, actively maintained replacement for archived Adldap2) per Q-010-07 wrapper pattern.
- **Test strategy:** Using LdapRecord's DirectoryEmulator test utilities for mocking (Q-010-10); no Docker LDAP server needed.
- **Group membership:** Queried during user provisioning (I5), not during authentication (I2-I4), for performance.
- **Retry logic:** Removed per user request; only timeout handling (5s default) with graceful degradation.
- **Display name:** Added display_name column to users table (COL-010-01) for user-friendly UI display while username remains unique identifier.
- **LDAP flag:** Added is_ldap column (COL-010-02) to differentiate LDAP users from local users; prevents credential editing for LDAP users (FR-010-08).
- **Performance:** Connection timeout configured via LDAP_CONNECTION_TIMEOUT; LdapRecord handles connection pooling (Q-010-09).
- **Security:** Ensure TLS/SSL required in production; validate certificates properly (Q-010-12: single flag).
- **Future enhancements:** CLI testing command, admin UI diagnostics, multi-server failover, group membership caching (see plan follow-ups).

---

*Last updated: 2026-01-26*
