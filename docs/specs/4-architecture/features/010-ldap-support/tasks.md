# Feature 010 Tasks – LDAP Authentication Support

_Status: Draft_  
_Last updated: 2026-01-25 (revised after plan updates)

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (FR-), non-functional IDs (NFR-), and scenario IDs (S-010-) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Pre-Implementation

- [ ] **T-010-00** – Analysis gate and dependency approval.  
  _Intent:_ Get user approval for LDAP library (LdapRecord vs native php-ldap), verify test LDAP environment.  
  _Verification:_ User confirmation in chat; test LDAP server accessible or mock strategy approved.  
  _Notes:_ Block all implementation tasks until this completes. LdapRecord is actively maintained; Adldap2 is archived.

## I1 – Dependency Setup and Configuration Structure (30 min)

- [ ] **T-010-01** – Install LDAP library (NFR-010-01, NFR-010-04).  
  _Intent:_ Install LdapRecord Laravel package.  
  _Verification commands:_  
  - `composer show ldaprecord/laravel` (package installed)  
  - `php -m | grep ldap` (php-ldap extension present)  
  _Notes:_ Requires user approval (T-010-00). LdapRecord is the modern replacement for archived Adldap2.

- [ ] **T-010-02** – Create LDAP configuration file (NFR-010-01).  
  _Intent:_ Create `config/ldap.php` loading from .env variables.  
  _Verification commands:_  
  - `cat config/ldap.php` (file exists)  
  - `php artisan config:clear && php artisan tinker` (test config loading)  
  _Notes:_ Map all ENV-010-01 through ENV-010-15 to config array.

- [ ] **T-010-03** – Create migration for display_name column (COL-010-01).  
  _Intent:_ Create migration `2026_01_25_add_display_name_to_users_table.php` adding nullable string(200) column.  
  _Verification commands:_  
  - `cat database/migrations/*add_display_name_to_users_table.php` (file exists)  
  - `php artisan migrate` (migration runs successfully)  
  - `php artisan tinker` then `Schema::hasColumn('users', 'display_name')` (returns true)  
  _Notes:_ Column added after `username`, nullable for existing users.

- [ ] **T-010-04** – Update User model for display_name (COL-010-01).  
  _Intent:_ Add display_name property to User model docblock and $fillable array.  
  _Verification commands:_  
  - `grep '@property string|null $display_name' app/Models/User.php` (property documented)  
  - `grep "'display_name'" app/Models/User.php` (in fillable array)  
  - `vendor/bin/phpstan analyze app/Models/User.php` (no errors)  
  _Notes:_ Update docblock around line 44 and $fillable around line 111.

- [ ] **T-010-05** – Create LdapConfiguration DTO (DO-010-01).  
  _Intent:_ Value object encapsulating LDAP config with validation.  
  _Verification commands:_  
  - `cat app/DTO/LdapConfiguration.php` (file exists)  
  - `vendor/bin/phpstan analyze app/DTO/LdapConfiguration.php` (no errors)  
  _Notes:_ Include all fields from DO-010-01 spec; validation layer per Q-010-08.

- [ ] **T-010-06** – Update .env.example with LDAP variables (NFR-010-01).  
  _Intent:_ Document all ENV-010-01 through ENV-010-15 with inline comments.  
  _Verification commands:_  
  - `grep LDAP .env.example` (all variables present)  
  _Notes:_ Include example values for OpenLDAP and Active Directory; document username vs display_name distinction.

## I2 – LDAP Service Core (Connection, Search, and Bind) (60 min)

- [ ] **T-010-07** – Create LdapService class and LdapUser DTO skeleton (FR-010-01, FR-010-06, DO-010-02).  
  _Intent:_ Create `app/Services/Auth/LdapService.php` wrapper class and `app/DTO/LdapUser.php` DTO.  
  _Verification commands:_  
  - `cat app/Services/Auth/LdapService.php` (file exists, wrapper pattern over LdapRecord)  
  - `cat app/DTO/LdapUser.php` (file exists with username, userDn, email, display_name fields)  
  - `vendor/bin/phpstan analyze app/Services/Auth/` (no errors)  
  _Notes:_ LdapUser DTO created here (not in I3); groups field omitted (queried in I5).

- [ ] **T-010-08** – Write failing tests for LDAP connection and TLS (FR-010-01, FR-010-07).  
  _Intent:_ Create `tests/Unit/Services/Auth/LdapServiceTest.php` with connection tests.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testConnectSuccess` (fails)  
  - `php artisan test --filter=LdapServiceTest::testConnectTlsRequired` (fails)  
  _Notes:_ Use LdapRecord's DirectoryEmulator for mocking (Q-010-10).

- [ ] **T-010-09** – Implement LDAP connection with TLS (FR-010-01, FR-010-07).  
  _Intent:_ Implement private `connect()` method with TLS enforcement.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testConnectSuccess` (passes)  
  - `php artisan test --filter=LdapServiceTest::testConnectTlsRequired` (passes)  
  _Notes:_ Configure LdapRecord's built-in connection pooling (Q-010-09); respect LDAP_USE_TLS config (Q-010-12).

- [ ] **T-010-10** – Write failing tests for user search (FR-010-01).  
  _Intent:_ Add search tests to LdapServiceTest.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testSearchUserSuccess` (fails)  
  - `php artisan test --filter=LdapServiceTest::testSearchUserNotFound` (fails)  
  _Notes:_ Search-first pattern per Q-010-11.

- [ ] **T-010-11** – Implement user search (FR-010-01).  
  _Intent:_ Implement private `searchUser(string $username): ?string` returning userDn.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testSearchUser` (all pass)  
  _Notes:_ Use LDAP_USER_FILTER config.

- [ ] **T-010-12** – Write failing tests for authenticate() bind (FR-010-01).  
  _Intent:_ Add bind tests to LdapServiceTest.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testAuthenticateBindSuccess` (fails)  
  - `php artisan test --filter=LdapServiceTest::testAuthenticateBindFailure` (fails)  
  _Notes:_ Test both successful and failed bind scenarios.

- [ ] **T-010-13** – Implement authenticate() skeleton (FR-010-01).  
  _Intent:_ Implement `authenticate(string $username, string $password): ?LdapUser` returning minimal LdapUser(username, userDn).  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testAuthenticateBind` (all pass)  
  _Notes:_ Search → extract userDn → bind with userDn+password; return null on failure, minimal LdapUser on success.

## I3 – Add Attribute Retrieval to authenticate() (60 min)

- [ ] **T-010-14** – Write failing tests for attribute retrieval (FR-010-02).  
  _Intent:_ Add attribute tests to LdapServiceTest for authenticate() method.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testAuthenticateWithAttributes` (fails)  
  - `php artisan test --filter=LdapServiceTest::testAuthenticateMissingEmail` (fails)  
  - `php artisan test --filter=LdapServiceTest::testAuthenticateMissingDisplayName` (fails)  
  - `php artisan test --filter=LdapServiceTest::testAuthenticateCustomAttributeMapping` (fails)  
  _Notes:_ Cover default and custom attribute mappings; test display_name fallback to username.

- [ ] **T-010-15** – Implement attribute retrieval in authenticate() (FR-010-02).  
  _Intent:_ Implement private `retrieveAttributes(string $userDn)` and integrate into authenticate().  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testAuthenticate` (all attribute tests pass)  
  _Notes:_ Use LDAP_ATTR_EMAIL, LDAP_ATTR_DISPLAY_NAME configs; authenticate() now returns LdapUser(username, userDn, email, display_name).

## I4 – LdapService Group Membership Helpers (45 min)

- [ ] **T-010-16** – Write failing tests for group membership helpers (FR-010-03).  
  _Intent:_ Add group helper tests to LdapServiceTest.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testQueryGroupsSuccess` (fails)  
  - `php artisan test --filter=LdapServiceTest::testIsUserInAdminGroupTrue` (fails)  
  - `php artisan test --filter=LdapServiceTest::testIsUserInAdminGroupFalse` (fails)  
  - `php artisan test --filter=LdapServiceTest::testIsUserInAdminGroupNoConfigured` (fails)  
  _Notes:_ Cover admin group match, no match, and no admin group configured scenarios.

- [ ] **T-010-17** – Implement group membership query helper (FR-010-03).  
  _Intent:_ Implement public `queryGroups(string $userDn): array` method.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testQueryGroups` (passes)  
  _Notes:_ Query LDAP for user's group memberships; called by ProvisionLdapUser, NOT by authenticate().

- [ ] **T-010-18** – Implement admin role determination helper (FR-010-03).  
  _Intent:_ Implement public `isUserInAdminGroup(array $groupDns): bool` method checking LDAP_ADMIN_GROUP_DN.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testIsUserInAdminGroup` (all pass)  
  _Notes:_ Default to false if no admin group configured; authenticate() complete and returns LdapUser(username, userDn, email, display_name).

## I5 – User Auto-Provisioning with Group-Based Role Assignment (75 min)

- [ ] **T-010-19** – Create ProvisionLdapUser action skeleton (FR-010-04, FR-010-03).  
  _Intent:_ Create `app/Actions/Auth/ProvisionLdapUser.php` class.  
  _Verification commands:_  
  - `cat app/Actions/Auth/ProvisionLdapUser.php` (file exists)  
  - `vendor/bin/phpstan analyze app/Actions/Auth/ProvisionLdapUser.php` (no errors)  
  _Notes:_ Accept LdapUser DTO and LdapService in constructor/execute.

- [ ] **T-010-20** – Write failing tests for user provisioning with groups (FR-010-04, FR-010-03, S-010-01, S-010-02, S-010-06, S-010-07, S-010-08).  
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
  _Notes:_ Cover new user, existing user, role assignment from groups, auto-provision disabled scenarios.

- [ ] **T-010-21** – Implement user provisioning with group queries (FR-010-04, FR-010-03, S-010-01, S-010-02).  
  _Intent:_ Implement `ProvisionLdapUser::execute()` to create/update users and query groups for role assignment.  
  _Verification commands:_  
  - `php artisan test --filter=ProvisionLdapUserTest` (all pass)  
  _Notes:_ Sync username, email, display_name; call LdapService::queryGroups(userDn) and isUserInAdminGroup() to set may_administrate.

## I6 – Authentication Controller Integration (60 min)

- [ ] **T-010-22** – Modify AuthController::login for LDAP (FR-010-01, FR-010-05, API-010-01).  
  _Intent:_ Add LDAP authentication attempt before/alongside basic auth.  
  _Verification commands:_  
  - `cat app/Http/Controllers/AuthController.php` (modifications visible)  
  - `vendor/bin/phpstan analyze app/Http/Controllers/AuthController.php` (no errors)  
  _Notes:_ Respect LDAP_ENABLED config; call authenticate() and pass LdapUser + LdapService to ProvisionLdapUser.

- [ ] **T-010-23** – Implement auth method selection logic (FR-010-05).  
  _Intent:_ Fallback to basic auth if LDAP fails and both are enabled.  
  _Verification commands:_  
  - (deferred to I7 feature tests)  
  _Notes:_ Code-only task; tested in next increment.

## I7 – Feature Tests for LDAP Authentication (90 min)

- [ ] **T-010-24** – Create LdapAuthenticationTest class (S-010-01 through S-010-10).  
  _Intent:_ Create `tests/Feature_v2/Auth/LdapAuthenticationTest.php` extending BaseApiWithDataTest.  
  _Verification commands:_  
  - `cat tests/Feature_v2/Auth/LdapAuthenticationTest.php` (file exists)  
  _Notes:_ Mock LDAP responses using LdapRecord's DirectoryEmulator (Q-010-10).

- [ ] **T-010-25** – Write failing test for first-time LDAP login (S-010-01).  
  _Intent:_ Test auto-provisioning on first login.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testFirstTimeLdapUserLogin` (fails)  
  _Notes:_ User should be created with LDAP attributes and display_name.

- [ ] **T-010-26** – Write failing test for existing user login (S-010-02).  
  _Intent:_ Test attribute sync on subsequent logins.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testExistingLdapUserLogin` (fails)  
  _Notes:_ User attributes (including display_name) should be updated from LDAP.

- [ ] **T-010-27** – Write failing test for invalid credentials (S-010-03).  
  _Intent:_ Test LDAP bind failure handling.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testInvalidLdapCredentials` (fails)  
  _Notes:_ Should return "Invalid credentials" error.

- [ ] **T-010-28** – Write failing test for LDAP fallback to basic (S-010-04).  
  _Intent:_ Test fallback when LDAP unreachable but basic auth enabled.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testLdapServerUnreachableWithFallback` (fails)  
  _Notes:_ Basic auth should succeed.

- [ ] **T-010-29** – Write failing test for LDAP-only mode failure (S-010-05).  
  _Intent:_ Test error when LDAP unreachable and LDAP-only mode.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testLdapServerUnreachableLdapOnlyMode` (fails)  
  _Notes:_ Should return service unavailable error.

- [ ] **T-010-30** – Write failing test for admin group membership (S-010-06).  
  _Intent:_ Test admin role assignment from LDAP group.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testLdapUserInAdminGroup` (fails)  
  _Notes:_ User should have may_administrate=true.

- [ ] **T-010-31** – Write failing test for non-admin user (S-010-07).  
  _Intent:_ Test regular user role when not in admin group.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testLdapUserNotInAdminGroup` (fails)  
  _Notes:_ User should have may_administrate=false.

- [ ] **T-010-32** – Write failing test for auto-provision disabled (S-010-08).  
  _Intent:_ Test login rejection when auto-provision disabled and user unknown.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testAutoProvisionDisabled` (fails)  
  _Notes:_ Should return error indicating user not found.

- [ ] **T-010-33** – Write failing test for custom attribute mapping (S-010-09).  
  _Intent:_ Test attribute sync with non-default LDAP attributes.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testCustomAttributeMapping` (fails)  
  _Notes:_ Configure custom LDAP_ATTR_* values in test.

- [ ] **T-010-34** – Write failing test for TLS enforcement (S-010-10).  
  _Intent:_ Test rejection when TLS required but unavailable.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testTlsRequiredButUnavailable` (fails)  
  _Notes:_ Should return security error.

- [ ] **T-010-35** – Implement all LDAP scenarios to pass tests (S-010-01 through S-010-10).  
  _Intent:_ Fix implementation until all feature tests pass.  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest` (all pass)  
  _Notes:_ Address any edge cases discovered during testing.

## I8 – Telemetry and Logging (45 min)

- [ ] **T-010-36** – Add ldap.auth.attempt event (TE-010-01).  
  _Intent:_ Log event when LDAP authentication is attempted.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testLogEventsEmitted` (verify event)  
  _Notes:_ Sanitize username in log.

- [ ] **T-010-37** – Add ldap.auth.success event (TE-010-02).  
  _Intent:_ Log event on successful LDAP authentication.  
  _Verification commands:_  
  - (verify in existing tests)  
  _Notes:_ Include new_user flag, role, duration_ms.

- [ ] **T-010-38** – Add ldap.auth.failure event (TE-010-03).  
  _Intent:_ Log event on failed LDAP authentication with reason.  
  _Verification commands:_  
  - (verify in existing tests)  
  _Notes:_ Reason enum: invalid_credentials, server_unreachable, tls_error.

- [ ] **T-010-39** – Add ldap.user.created event (TE-010-04).  
  _Intent:_ Log event when new user is auto-provisioned.  
  _Verification commands:_  
  - (verify in existing tests)  
  _Notes:_ Include source=ldap.

- [ ] **T-010-40** – Add ldap.user.sync event (TE-010-05).  
  _Intent:_ Log event when user attributes are synchronized.  
  _Verification commands:_  
  - (verify in existing tests)  
  _Notes:_ Include attributes_synced count.

- [ ] **T-010-41** – Add ldap.role.assigned event (TE-010-06).  
  _Intent:_ Log event when role is assigned from LDAP group.  
  _Verification commands:_  
  - (verify in existing tests)  
  _Notes:_ Include role (admin/user), source (ldap_group/default).

- [ ] **T-010-42** – Add ldap.connection.timeout event (TE-010-07).  
  _Intent:_ Log event on connection timeout.  
  _Verification commands:_  
  - (verify in timeout tests)  
  _Notes:_ Include host, port, timeout_ms.

- [ ] **T-010-43** – Add ldap.tls.required event (TE-010-08).  
  _Intent:_ Log event when TLS is required but unavailable.  
  _Verification commands:_  
  - (verify in TLS tests)  
  _Notes:_ Include host, port, tls_available boolean.

## I9 – Error Handling and Graceful Degradation (45 min)

- [ ] **T-010-44** – Implement connection timeout handling (FR-010-06).  
  _Intent:_ Enforce LDAP_CONNECTION_TIMEOUT with appropriate error handling.  
  _Verification commands:_  
  - `php artisan test --filter=LdapServiceTest::testConnectionTimeout` (write & pass)  
  _Notes:_ Default 5 seconds; no retry logic per user request.

- [ ] **T-010-45** – Implement graceful degradation (NFR-010-06).  
  _Intent:_ Fallback to basic auth when LDAP fails (if both enabled).  
  _Verification commands:_  
  - `php artisan test --filter=LdapAuthenticationTest::testGracefulDegradationToBasicAuth` (write & pass)  
  - `php artisan test --filter=LdapAuthenticationTest::testLdapOnlyModeServiceUnavailable` (write & pass)  
  _Notes:_ Already tested in S-010-04/S-010-05; verify implementation.

- [ ] **T-010-46** – User-friendly error messages (FR-010-01).  
  _Intent:_ Ensure LDAP errors don't expose implementation details.  
  _Verification commands:_  
  - Review all error responses in tests  
  _Notes:_ Generic "Authentication failed" or "Service unavailable" messages.

## I10 – Documentation and Examples (45 min)

- [ ] **T-010-47** – Update .env.example (NFR-010-01).  
  _Intent:_ Ensure all LDAP variables documented with examples.  
  _Verification commands:_  
  - `grep LDAP_ .env.example` (verify all ENV-010-* present)  
  _Notes:_ Include OpenLDAP and Active Directory examples; docker-compose.yaml already updated.

- [ ] **T-010-48** – Create docs/ldap-setup.md guide.  
  _Intent:_ Comprehensive LDAP setup documentation.  
  _Verification commands:_  
  - `cat docs/ldap-setup.md` (file exists and complete)  
  _Notes:_ Cover configuration, TLS (LDAPS vs StartTLS per Q-010-12), troubleshooting, security best practices.

- [ ] **T-010-49** – Update knowledge-map.md (Knowledge Map).  
  _Intent:_ Document LDAP service module and auth flow integration.  
  _Verification commands:_  
  - `grep -i ldap docs/specs/4-architecture/knowledge-map.md` (entry exists)  
  _Notes:_ Add wrapper pattern (Q-010-07), validation DTO (Q-010-08), search-first flow (Q-010-11), php-ldap dependency.

- [ ] **T-010-50** – Update README.md.  
  _Intent:_ Mention LDAP support in features list.  
  _Verification commands:_  
  - `grep -i ldap README.md` (entry exists)  
  _Notes:_ Brief mention; link to docs/ldap-setup.md.

## I11 – Quality Gate and Final Testing (60 min)

- [ ] **T-010-51** – Run full test suite.  
  _Intent:_ Verify all tests pass.  
  _Verification commands:_  
  - `php artisan test` (all pass)  
  _Notes:_ Address any failures.

- [ ] **T-010-52** – Run PHPStan analysis.  
  _Intent:_ Verify no static analysis errors.  
  _Verification commands:_  
  - `make phpstan` (level 6, all pass)  
  _Notes:_ Fix any reported issues.

- [ ] **T-010-53** – Run PHP CS Fixer.  
  _Intent:_ Apply code style formatting.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix` (no changes or changes applied)  
  _Notes:_ Commit formatted code.

- [ ] **T-010-54** – Security review: password handling.  
  _Intent:_ Verify no LDAP passwords logged or stored.  
  _Verification commands:_  
  - `grep -r "password" app/Services/Auth/LdapService.php` (manual review)  
  - `grep -r "bind_password" app/` (ensure only config usage)  
  _Notes:_ Audit all logging statements.

- [ ] **T-010-55** – Backward compatibility check (NFR-010-03).  
  _Intent:_ Verify existing basic auth users unaffected.  
  _Verification commands:_  
  - `php artisan test --filter=AuthenticationTest` (existing auth tests still pass)  
  _Notes:_ Ensure LDAP_ENABLED=false leaves basic auth unchanged.

- [ ] **T-010-56** – Manual testing against real LDAP (optional).  
  _Intent:_ Test against actual LDAP server if available.  
  _Verification commands:_  
  - Manual login test with real LDAP credentials  
  _Notes:_ Document any issues found.

## Notes / TODOs

- **LDAP library decision:** Using `ldaprecord/laravel` (modern, actively maintained replacement for archived Adldap2) per Q-010-07 wrapper pattern.
- **Test strategy:** Using LdapRecord's DirectoryEmulator test utilities for mocking (Q-010-10); no Docker LDAP server needed.
- **Group membership:** Queried during user provisioning (I5), not during authentication (I2-I4), for performance.
- **Retry logic:** Removed per user request; only timeout handling (5s default) with graceful degradation.
- **Display name:** Added display_name column to users table (COL-010-01) for user-friendly UI display while username remains unique identifier.
- **Performance:** Connection timeout configured via LDAP_CONNECTION_TIMEOUT; LdapRecord handles connection pooling (Q-010-09).
- **Security:** Ensure TLS/SSL required in production; validate certificates properly (Q-010-12: single flag).
- **Future enhancements:** CLI testing command, admin UI diagnostics, multi-server failover, group membership caching (see plan follow-ups).

---

*Last updated: 2026-01-25*
