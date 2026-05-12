# Feature 040 Tasks – Passkeys Migration (laragear/webauthn → laravel/passkeys)

_Status: Draft_  
_Last updated: 2026-05-12_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-040-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes.

## Checklist

### I1 – Resolve open questions & inspect laravel/passkeys internals

- [ ] T-040-01 – Inspect `laravel/passkeys` published migration and confirm `passkeys` table schema (Q-040-01, Q-040-02).  
  _Intent:_ Review `vendor/laravel/passkeys/database/migrations` to confirm column names (`name` vs `alias`, `user_id` morph or direct FK, etc.), and whether data from `webauthn_credentials` can be mapped without data loss.  
  _Verification commands:_  
  - `cat vendor/laravel/passkeys/database/migrations/*.php` (after package install)  
  _Notes:_ Update spec.md FR-040-06 and DO-040-01 with confirmed field list. Resolve Q-040-01 and Q-040-02 in `open-questions.md`.

- [ ] T-040-02 – Inspect `@laravel/passkeys` JS client API and confirm custom base URL support (Q-040-03, Q-040-04).  
  _Intent:_ Determine whether the JS client can be configured to call Lychee's `/api/v2/WebAuthn::*` routes, or whether a thin wrapper is needed.  
  _Verification commands:_  
  - `cat node_modules/@laravel/passkeys/src/**` (after npm install)  
  _Notes:_ Resolve Q-040-03 and Q-040-04. Update spec.md UI-040-01 and UI-040-02. Update plan I7 if a wrapper is needed.

- [ ] T-040-03 – Confirm `laravel/passkeys` test helpers availability (Q-040-03).  
  _Intent:_ Determine whether the package ships test helpers (Challenge mock, credential factory) that can replace `Laragear\WebAuthn\ByteBuffer` and `Laragear\WebAuthn\Challenge\Challenge` used in `WebAuthTest.php`.  
  _Verification commands:_  
  - `find vendor/laravel/passkeys -name '*Test*' -o -name '*Fake*' -o -name '*Factory*'`  
  _Notes:_ If no helpers exist, plan I6 must mock or regenerate test vectors. Resolve Q-040-03 in `open-questions.md`.

---

### I2 – Composer & npm swap + vulnerability check

- [ ] T-040-04 – Check `laravel/passkeys` and `@laravel/passkeys` for known vulnerabilities and install them; remove `laragear/webauthn` (NFR-040-05).  
  _Intent:_ Use `gh-advisory-database` to verify no known CVEs, then install both packages and publish passkeys config. Call `Passkeys::ignoreRoutes()` in the application service provider to suppress automatic route registration.  
  _Verification commands:_  
  - `composer require laravel/passkeys`  
  - `npm install @laravel/passkeys`  
  - `php artisan vendor:publish --tag=passkeys-config`  
  - `composer install`  
  - `npm install`  
  _Notes:_ If vulnerabilities are found, stop and report to the team before proceeding.

---

### I3 – Database migration

- [ ] T-040-05 – Write failing migration test that seeds `webauthn_credentials` and asserts rows appear in `passkeys` after migration runs (S-040-15, FR-040-06, NFR-040-06).  
  _Intent:_ Test-first: create `tests/Feature_v2/PasskeysMigrationTest.php` that seeds the old table, calls `artisan migrate`, and verifies data in the new table. Test must fail until I3 implementation is complete.  
  _Verification commands:_  
  - `php artisan test --filter=PasskeysMigrationTest` (expect failure)  
  _Notes:_ Use `BaseApiWithDataTest` as base class per project conventions.

- [ ] T-040-06 – Write data migration: create `passkeys`, copy rows from `webauthn_credentials` with column mapping, drop old table (FR-040-06, NFR-040-06, S-040-15).  
  _Intent:_ Create a new Laravel migration file. `up()` creates `passkeys` (from published schema), copies data, drops `webauthn_credentials`. `down()` recreates `webauthn_credentials` and copies back.  
  _Verification commands:_  
  - `php artisan test --filter=PasskeysMigrationTest` (expect pass)  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ Pay particular attention to the `alias` → `name` (or vice versa) column rename and morphable vs. direct FK for `user_id`.

---

### I4 – User model & PHP class updates

- [ ] T-040-07 – Update `User.php`: swap `WebAuthnAuthentication` / `WebAuthnAuthenticatable` / `WebAuthnData` for `PasskeyAuthenticatable` / `PasskeyUser`; update `delete()` to clean up `passkeys`; override `getPasskeyDisplayName()` / `getPasskeyUsername()` for email-optional path (FR-040-01, FR-040-02, S-040-17).  
  _Intent:_ Remove all `Laragear\WebAuthn` imports from `User.php`. Add `Laravel\Passkeys` imports. Remove `webAuthnData()` method, add overrides for display name and username if email is null (Lychee email is optional). Update `delete()` to reference the `Passkey` model.  
  _Verification commands:_  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ The `username()` method used by Larapass may no longer be needed; verify and remove if so.

- [ ] T-040-08 – Update `AuthServiceProvider::isWebAuthnEnabled()` and `config/webauthn.php` / `config/passkeys.php` to preserve `DISABLE_WEBAUTHN` flag (FR-040-07, S-040-14).  
  _Intent:_ The `features.disable-webauthn` config key must still be read by `AuthServiceProvider::isWebAuthnEnabled()`. Map env vars from the old `config/webauthn.php` (`WEBAUTHN_NAME`, `WEBAUTHN_ID`) to `config/passkeys.php` (`relying_party_id`, etc.) or bridge them.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Keep `config/features.php` `disable-webauthn` key unchanged. Config bridging or replacement is acceptable.

- [ ] T-040-09 – Update `AuthDisabledCheck.php` to check `passkeys` table instead of `webauthn_credentials`; update `webauthnCheck()` to use `Passkey` model relation (FR-040-08, S-040-16).  
  _Intent:_ `Schema::hasTable('webauthn_credentials')` → `Schema::hasTable('passkeys')`. `User::query()->has('webAuthnCredentials')` → `User::query()->has('passkeys')`.  
  _Verification commands:_  
  - `php artisan test --filter=AuthDisabledCheckTest`  
  - `make phpstan`  
  _Notes:_ Also check `AppUrlMatchCheck.php` for any `webauthn_credentials` references.

- [ ] T-040-10 – Update `WebAuthnResource`, `EditCredentialRequest`, `DeleteCredentialRequest`, `ListCredentialsRequest` to use the `Passkey` model (FR-040-03, FR-040-04, FR-040-05, S-040-09, S-040-11, S-040-13).  
  _Intent:_ Replace all `Laragear\WebAuthn\Models\WebAuthnCredential` references. Update `EditCredentialRequest` to query `Passkey::query()->findOrFail($id)`. Update `WebAuthnResource` to read `name` (or `alias`) from the new model.  
  _Verification commands:_  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  

---

### I5 – Controllers & routes update

- [ ] T-040-11 – Rewrite `WebAuthnRegisterController` to use `GenerateRegistrationOptions` and `StorePasskey` actions (FR-040-01, S-040-01, S-040-02, S-040-03).  
  _Intent:_ `options()` calls `app(GenerateRegistrationOptions::class)(Auth::user())` and returns the challenge JSON. `register()` calls `app(StorePasskey::class)(...)` and dispatches `CacheTag::USER`. Remove `AttestationRequest`/`AttestedRequest` imports.  
  _Verification commands:_  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  

- [ ] T-040-12 – Rewrite `WebAuthnLoginController` to use `GenerateVerificationOptions` and `VerifyPasskey` actions (FR-040-02, S-040-04, S-040-05, S-040-06, S-040-07, S-040-08).  
  _Intent:_ `options()` calls `app(GenerateVerificationOptions::class)($authenticatable)` where `$authenticatable` is resolved from the optional `user_id`/`username` input. `login()` calls `app(VerifyPasskey::class)(...)` and calls `Auth::login()` on success.  
  _Verification commands:_  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ Preserve the `checkEnabled()` guard for `FR-040-07`.

- [ ] T-040-13 – Update `routes/api_v2.php` to remove stale imports; add `Passkeys::ignoreRoutes()` call in service provider (FR-040-01, FR-040-02).  
  _Intent:_ Remove `Laragear\WebAuthn` controller imports from routes file. Ensure `Laravel\Passkeys\Passkeys::ignoreRoutes()` is called in `AppServiceProvider::boot()` (or equivalent) so that `/passkeys/*` and `/user/passkeys/*` are not registered.  
  _Verification commands:_  
  - `php artisan route:list | grep -E 'passkeys|webauthn'`  
  - `make phpstan`  

---

### I6 – Tests update

- [ ] T-040-14 – Update `RequiresEmptyWebAuthnCredentials` trait to reference `passkeys` table (S-040-01 through S-040-13).  
  _Intent:_ Change `assertDatabaseCount('webauthn_credentials', 0)` and `DB::table('webauthn_credentials')->truncate()` to reference `passkeys`.  
  _Verification commands:_  
  - `php artisan test --filter=WebAuthTest`  

- [ ] T-040-15 – Rewrite `WebAuthTest::createCredentials()` and session challenge seeding for new package (S-040-01 through S-040-13, NFR-040-01).  
  _Intent:_ Replace `$this->admin->makeWebAuthnCredential([...])` with `Passkey::factory()->create([...])` or direct model creation. Replace `new Challenge(ByteBuffer::..., ...)` + `Session::put(config('webauthn.challenge.key'), ...)` with the `laravel/passkeys` equivalent challenge session key and format.  
  _Verification commands:_  
  - `php artisan test --filter=WebAuthTest`  
  _Notes:_ Regenerate pre-computed `attestationObject`, `clientDataJSON`, `signature`, etc. if the challenge format changed, or mock the validation at the package boundary.

- [ ] T-040-16 – Verify `DISABLE_WEBAUTHN` flag integration test returns 403 on all passkey endpoints (FR-040-07, S-040-14).  
  _Intent:_ Add or update a test that sets `config(['features.disable-webauthn' => true])` and asserts each passkey endpoint returns HTTP 403.  
  _Verification commands:_  
  - `php artisan test --filter=WebAuthTest`  

- [ ] T-040-17 – Verify `AuthDisabledCheckTest` and `User.delete()` passkey cleanup (FR-040-08, S-040-16, S-040-17).  
  _Intent:_ Confirm `AuthDisabledCheckTest` tests still pass. Add/update a test that deletes a `User` with associated passkeys and asserts the `passkeys` rows are deleted.  
  _Verification commands:_  
  - `php artisan test --filter=AuthDisabledCheckTest`  
  - `make phpstan`  

---

### I7 – Frontend update

- [ ] T-040-18 – Replace or update `resources/js/vendor/webauthn/webauthn.ts` and `webauthn-service.ts` to use `@laravel/passkeys` or an updated custom client targeting Lychee's API routes (UI-040-01, UI-040-02).  
  _Intent:_ If `@laravel/passkeys` supports custom base URLs: configure it to call `/api/v2/WebAuthn::login`, `/api/v2/WebAuthn::login/options`, etc. Otherwise, keep the vendored client but remove laragear-specific bits. Update `webauthn-service.ts` accordingly.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  

- [ ] T-040-19 – Update `WebauthnModal.vue`, `SetSecondFactor.vue`, `SetSecondFactorLine.vue` for any API call signature changes (UI-040-01, UI-040-02).  
  _Intent:_ Ensure `WebAuthnService.login()` and `WebAuthnService.register()` calls still match the updated service interface. Update TypeScript types if `WebAuthnResource` shape changed (`alias` field rename).  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  

- [ ] T-040-20 – Update `lychee.d.ts` if `App.Http.Resources.Models.WebAuthnResource` type shape changed.  
  _Intent:_ Run `php artisan typescript:transform` (or equivalent) to regenerate TypeScript types from the updated `WebAuthnResource` Data class, then verify `npm run check` passes.  
  _Verification commands:_  
  - `npm run check`  

---

### I8 – Documentation & quality gates

- [ ] T-040-21 – Update `.env.example` with `PASSKEYS_USER_HANDLE_SECRET` and bridge comments for `WEBAUTHN_NAME` / `WEBAUTHN_ID`.  
  _Intent:_ Add `PASSKEYS_USER_HANDLE_SECRET=` (empty default, must be set in production). Add comment noting `WEBAUTHN_NAME`/`WEBAUTHN_ID` are deprecated aliases if bridged, or document new env var names.  
  _Verification commands:_ Manual review.

- [ ] T-040-22 – Update `docs/specs/4-architecture/knowledge-map.md` to replace `laragear/webauthn` with `laravel/passkeys` entry.  
  _Intent:_ Reflect the new dependency in the knowledge map so future agents pick up the correct package.  
  _Verification commands:_ Manual review.

- [ ] T-040-23 – Run full quality pipeline and confirm all gates green (NFR-040-01 through NFR-040-04).  
  _Intent:_ Final verification pass: `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`, `npm run format`, `npm run check`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test`  
  - `make phpstan`  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ All must exit 0 before the roadmap row is moved.

- [ ] T-040-24 – Move roadmap entry 040 from Active to Completed in `docs/specs/4-architecture/roadmap.md`.  
  _Intent:_ Close out the feature in the roadmap with a completion summary.  
  _Verification commands:_ Manual review.

---

## Notes / TODOs

- **T-040-15 test vectors:** If `laravel/passkeys` uses a different internal `Challenge` structure than `laragear/webauthn`, the pre-computed `clientDataJSON` / `attestationObject` / `signature` bytes in `WebAuthTest.php` will be invalid. The safest approach is to mock the action classes (`GenerateVerificationOptions`, `VerifyPasskey`) in tests that do not need full cryptographic validation, and keep only a minimal smoke test with real attestation. Decide in T-040-03.
- **Alias vs. name column:** `laravel/passkeys` uses `name` for the credential friendly name. If the column is renamed in the `passkeys` table, the `WebAuthnResource` and `EditCredentialRequest` must use `name` instead of `alias`. This may require a minor UI string update. Resolved by T-040-01.
- **Route naming:** Existing named routes (`webauthn.register.options`, `webauthn.register`, etc.) in `routes/api_v2.php` should be preserved or updated with care, as they may be referenced in tests or other tooling.
