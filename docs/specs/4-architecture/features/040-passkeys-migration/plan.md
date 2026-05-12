# Feature Plan 040 – Passkeys Migration (laragear/webauthn → laravel/passkeys)

_Linked specification:_ `docs/specs/4-architecture/features/040-passkeys-migration/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-05-12

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Replace the abandoned `laragear/webauthn` package with the official first-party `laravel/passkeys` package, preserving all existing passkey behaviours (registration, login, list, alias edit, delete) and migrating existing credential data, so that Lychee's passkey authentication remains secure and maintainable.

**Success signals:**
- `php artisan test --filter=WebAuthTest` passes (0 failures) against the new package.
- `make phpstan` exits 0 on all modified PHP files.
- `npm run check` exits 0 on all modified TypeScript/Vue files.
- `vendor/bin/php-cs-fixer fix --dry-run` exits 0 on modified PHP files.
- All 17 scenarios in the Branch & Scenario Matrix are covered by passing tests.
- Roadmap entry 040 moved to Completed.

## Scope Alignment

- **In scope:**
  - Composer dependency swap (`laragear/webauthn` out, `laravel/passkeys` in).
  - npm dependency swap (vendored `webauthn.ts` out, `@laravel/passkeys` in or updated custom client).
  - Database migration: rename `webauthn_credentials` → `passkeys`, map columns.
  - User model: swap trait and contract.
  - Controllers, requests, and resources updated for new model.
  - Config: `config/webauthn.php` replaced/updated for `config/passkeys.php`.
  - Route file: `routes/api_v2.php` updated to use new controllers; `Passkeys::ignoreRoutes()` called.
  - `AuthServiceProvider`, `AuthDisabledCheck`, `AppUrlMatchCheck` updated for new table/package.
  - Tests: `WebAuthTest.php` and `RequiresEmptyWebAuthnCredentials` updated for new package.
  - Frontend: `webauthn-service.ts`, `WebauthnModal.vue`, `SetSecondFactor*.vue` updated.
  - `.env.example` updated with `PASSKEYS_USER_HANDLE_SECRET`.
  - Knowledge map updated.

- **Out of scope:**
  - New passkey UX features.
  - OAuth or password authentication changes.
  - `laravel/passkeys` confirmation/re-auth flow.
  - Renaming existing `.env` variables (deferred to separate deprecation task).

## Dependencies & Interfaces

- `laravel/passkeys` Composer package (first-party, actively maintained).
- `@laravel/passkeys` npm package.
- `webauthn` PHP extension (required by underlying `web-auth/webauthn-lib`).
- Existing `webauthn_credentials` table data must be preserved.
- `CacheTag::USER` route cache invalidation remains in place.
- `AuthServiceProvider::isWebAuthnEnabled()` guard remains in place.

## Assumptions & Risks

- **Assumptions:**
  - `laravel/passkeys` ships a `passkeys` migration with a schema compatible enough for a column-mapping migration from `webauthn_credentials`.
  - The `name` column in `laravel/passkeys`'s `Passkey` model can serve as the `alias` field (or a custom column can be added via a custom model).
  - The `laravel/passkeys` package exposes internal action classes (`GenerateRegistrationOptions`, `GenerateVerificationOptions`, `StorePasskey`, `VerifyPasskey`) that can be used directly in Lychee's custom controllers.
  - `Passkeys::ignoreRoutes()` effectively suppresses all automatic route registration.

- **Risks / Mitigations:**
  - **Schema incompatibility:** The `webauthn_credentials` schema may not map cleanly to `passkeys`. Mitigation: inspect the published migration before writing the data-migration script; raise Q-040-02 for resolution before I2.
  - **Test vector invalidation:** Existing pre-computed challenge/assertion bytes in `WebAuthTest.php` are tied to `laragear`'s internal `Challenge`/`ByteBuffer`. Mitigation: check for `laravel/passkeys` test helpers; regenerate or re-mock as needed (Q-040-03).
  - **Alias field absence:** `laravel/passkeys` may not expose an `alias` column directly. Mitigation: extend the `Passkey` model with a custom `alias` (nullable string) column via a follow-up migration (Q-040-01).
  - **Frontend API differences:** `@laravel/passkeys` JS client targets the `/passkeys/*` routes, not Lychee's `/api/v2/WebAuthn::*` routes. Mitigation: either configure the client's base URLs or keep using a custom JS client that mirrors the existing logic but talks to the new backend.

## Implementation Drift Gate

After each increment, run:
```
vendor/bin/php-cs-fixer fix
php artisan test --filter=WebAuthTest
make phpstan
npm run check
```
Record results in the relevant `tasks.md` checkbox. If any gate is red, the increment is not done.

## Increment Map

1. **I1 – Resolve open questions & inspect laravel/passkeys internals** (≤60 min)
   - _Goal:_ Answer Q-040-01 through Q-040-04 by inspecting the published package source, migration, and JS client. Record answers in spec normative sections.
   - _Preconditions:_ spec.md draft complete.
   - _Steps:_
     - Read `vendor/laravel/passkeys` (after `composer require --dry-run` or via GitHub) to confirm table schema, model columns, and available test helpers.
     - Verify `@laravel/passkeys` JS API and whether it supports custom base URLs.
     - Update spec.md FR/NFR sections with confirmed answers.
     - Mark Q-040-01 through Q-040-04 resolved in `open-questions.md`.
   - _Commands:_ `composer info laravel/passkeys` (after install), `cat vendor/laravel/passkeys/database/migrations/*.php`
   - _Exit:_ All four open questions resolved; spec normative sections updated.

2. **I2 – Composer & npm swap + vulnerability check** (≤30 min)
   - _Goal:_ Install `laravel/passkeys` and `@laravel/passkeys`; confirm no known vulnerabilities; remove `laragear/webauthn`.
   - _Preconditions:_ I1 complete.
   - _Steps:_
     - Run `gh-advisory-database` check for `laravel/passkeys` (Composer) and `@laravel/passkeys` (npm) before installing.
     - `composer remove laragear/webauthn && composer require laravel/passkeys`.
     - `npm uninstall` the vendored approach (or `npm install @laravel/passkeys`).
     - Publish passkeys config: `php artisan vendor:publish --tag=passkeys-config`.
     - Call `Passkeys::ignoreRoutes()` in `AppServiceProvider` or equivalent.
   - _Commands:_ `composer require laravel/passkeys`, `npm install @laravel/passkeys`
   - _Exit:_ `composer install` and `npm install` succeed; no known vulnerabilities.

3. **I3 – Database migration** (≤60 min)
   - _Goal:_ Write a Laravel migration that creates the `passkeys` table (via `php artisan vendor:publish --tag=passkeys-migrations` or manual equivalent), then copies data from `webauthn_credentials` mapping columns correctly, then drops the old table.
   - _Preconditions:_ I1 (schema confirmed), I2 (package installed).
   - _Steps:_
     - Publish `laravel/passkeys` migrations and review schema.
     - Write new migration: create `passkeys`, INSERT … SELECT from `webauthn_credentials` with column mapping, drop `webauthn_credentials`.
     - Write `down()` to recreate `webauthn_credentials` from `passkeys`.
     - Write a Feature_v2 test that seeds `webauthn_credentials`, runs migration, and asserts rows exist in `passkeys`.
   - _Commands:_ `php artisan test --filter=PasskeysMigrationTest`, `make phpstan`
   - _Exit:_ Migration test passes; `webauthn_credentials` data appears in `passkeys`.

4. **I4 – User model & PHP class updates** (≤60 min)
   - _Goal:_ Update `User.php`, `WebAuthnResource.php`, `EditCredentialRequest.php`, `AuthDisabledCheck.php`, and `AppUrlMatchCheck.php` to use `laravel/passkeys` model/contracts.
   - _Preconditions:_ I3 complete.
   - _Steps:_
     - Replace `WebAuthnAuthenticatable` + `WebAuthnAuthentication` + `WebAuthnData` with `PasskeyUser` + `PasskeyAuthenticatable` in `User.php`.
     - Update `User::webAuthnData()` → `getPasskeyDisplayName()` / `getPasskeyUsername()` overrides as needed (email-optional path).
     - Update `User::delete()` to clean up `passkeys` rows.
     - Update `WebAuthnResource` constructor/factory to use `Passkey` model.
     - Update `EditCredentialRequest` to query `Passkey` model.
     - Update `AuthDisabledCheck` schema check from `webauthn_credentials` → `passkeys`.
     - Update `AppUrlMatchCheck` if it references the old table name.
   - _Commands:_ `make phpstan`, `vendor/bin/php-cs-fixer fix`
   - _Exit:_ PHPStan exits 0; no style violations.

5. **I5 – Controllers & routes update** (≤60 min)
   - _Goal:_ Rewrite the three WebAuthn controllers to delegate to `laravel/passkeys` action classes; update `routes/api_v2.php` and `config/webauthn.php`.
   - _Preconditions:_ I4 complete.
   - _Steps:_
     - Update `WebAuthnRegisterController::options()` to use `GenerateRegistrationOptions` action.
     - Update `WebAuthnRegisterController::register()` to use `StorePasskey` action.
     - Update `WebAuthnLoginController::options()` to use `GenerateVerificationOptions` action.
     - Update `WebAuthnLoginController::login()` to use `VerifyPasskey` action.
     - Update `WebAuthnManageController` to query `Passkey` model.
     - Update `DeleteCredentialRequest` to use `Passkey` model.
     - Replace `config/webauthn.php` content with `config/passkeys.php` mappings (or keep both, bridging env vars).
     - Confirm `Passkeys::ignoreRoutes()` in service provider.
     - Update `routes/api_v2.php` imports.
   - _Commands:_ `make phpstan`, `vendor/bin/php-cs-fixer fix`
   - _Exit:_ PHPStan exits 0; no style violations.

6. **I6 – Tests update** (≤60 min)
   - _Goal:_ Update `WebAuthTest.php` and supporting traits/helpers for the new package; all 17 scenarios pass.
   - _Preconditions:_ I5 complete.
   - _Steps:_
     - Update `RequiresEmptyWebAuthnCredentials` to reference `passkeys` table.
     - Rewrite `WebAuthTest::createCredentials()` using `laravel/passkeys` test helpers or direct model creation.
     - Replace `Challenge` + `ByteBuffer` session seeding with package equivalent or mock.
     - Regenerate or adapt pre-computed assertion test vectors if required.
     - Confirm all 17 scenario IDs are covered.
   - _Commands:_ `php artisan test --filter=WebAuthTest`
   - _Exit:_ All WebAuthn tests pass, 0 failures.

7. **I7 – Frontend update** (≤60 min)
   - _Goal:_ Replace the vendored `resources/js/vendor/webauthn/webauthn.ts` with `@laravel/passkeys` JS client (or an updated wrapper); update service and components.
   - _Preconditions:_ I5 complete (API routes confirmed).
   - _Steps:_
     - Replace or update `resources/js/vendor/webauthn/webauthn.ts` / `webauthn-service.ts` to use `@laravel/passkeys` or a custom client targeting Lychee's existing route paths.
     - Update `WebauthnModal.vue` if login API call signature changes.
     - Update `SetSecondFactor.vue` and `SetSecondFactorLine.vue` if registration API call changes.
     - Update `lychee.d.ts` type references if needed.
   - _Commands:_ `npm run format`, `npm run check`
   - _Exit:_ `npm run check` exits 0.

8. **I8 – Documentation & quality gates** (≤45 min)
   - _Goal:_ Update `.env.example`, `config/passkeys.php` comments, knowledge map, and roadmap; run full quality pipeline.
   - _Preconditions:_ I6 + I7 complete.
   - _Steps:_
     - Add `PASSKEYS_USER_HANDLE_SECRET=` to `.env.example` with an explanatory comment.
     - Update `docs/specs/4-architecture/knowledge-map.md`.
     - Run full pipeline: `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`, `npm run format`, `npm run check`.
     - Move roadmap row 040 from Active to Completed.
   - _Commands:_ `php artisan test`, `make phpstan`, `npm run check`
   - _Exit:_ All gates green; roadmap updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-040-01 | I5 / T-040-11, tested in I6 / T-040-13 | Register passkey, cache tag invalidated |
| S-040-02 | I5 / T-040-11, tested in I6 / T-040-13 | Unauthenticated registration → 403 |
| S-040-03 | I5 / T-040-11, tested in I6 / T-040-13 | Expired challenge → 422 |
| S-040-04 | I5 / T-040-12, tested in I6 / T-040-14 | Login with user_id scope |
| S-040-05 | I5 / T-040-12, tested in I6 / T-040-14 | Login with username scope |
| S-040-06 | I5 / T-040-12, tested in I6 / T-040-14 | Anonymous login options |
| S-040-07 | I5 / T-040-12, tested in I6 / T-040-14 | Wrong signature → 422 |
| S-040-08 | I5 / T-040-12, tested in I6 / T-040-14 | Wrong challenge → 422 |
| S-040-09 | I5 / T-040-10, tested in I6 / T-040-15 | List passkeys |
| S-040-10 | I5 / T-040-10, tested in I6 / T-040-15 | Unauthenticated list → 401 |
| S-040-11 | I5 / T-040-10, tested in I6 / T-040-15 | Edit alias |
| S-040-12 | I5 / T-040-10, tested in I6 / T-040-15 | Unauthenticated edit/delete → 401 |
| S-040-13 | I5 / T-040-10, tested in I6 / T-040-15 | Delete passkey |
| S-040-14 | I4 / T-040-08, tested in I6 / T-040-16 | DISABLE_WEBAUTHN flag |
| S-040-15 | I3 / T-040-05, tested in I3 / T-040-06 | Data migration correctness |
| S-040-16 | I4 / T-040-09, tested in I6 / T-040-17 | AuthDisabledCheck queries passkeys |
| S-040-17 | I4 / T-040-08, tested in I6 / T-040-17 | User.delete() cleans up passkeys |

## Analysis Gate

_Not yet run. To be completed before implementation begins._

## Exit Criteria

- [ ] `php artisan test` passes (all tests including `WebAuthTest`).
- [ ] `make phpstan` exits 0.
- [ ] `vendor/bin/php-cs-fixer fix --dry-run` exits 0 on modified PHP files.
- [ ] `npm run check` exits 0.
- [ ] All 17 scenario IDs covered by passing tests.
- [ ] `laragear/webauthn` no longer in `composer.json`.
- [ ] `webauthn_credentials` table no longer referenced in application code.
- [ ] `.env.example` updated with `PASSKEYS_USER_HANDLE_SECRET`.
- [ ] Knowledge map updated.
- [ ] Roadmap row 040 moved to Completed.

## Follow-ups / Backlog

- Consider deprecating `WEBAUTHN_NAME` / `WEBAUTHN_ID` env vars in favour of `PASSKEYS_*` equivalents in a future minor release.
- Evaluate adopting the `laravel/passkeys` automatic routes for consistency if Lychee's routing strategy changes.
- Add passkey confirmation/re-authentication flow if a privileged-action confirmation UX is introduced.
- Review `laravel/passkeys` `PasskeyRegistered`, `PasskeyVerified`, `PasskeyDeleted` events as replacement telemetry hooks.

---

*Last updated: 2026-05-12*
