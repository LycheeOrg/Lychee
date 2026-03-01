# Feature 023 Tasks – Remember Me Login

_Status: Draft_
_Last updated: 2026-02-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections and, when required, ADRs reflect the clarified behaviour.

---

## I1 – Backend: LoginRequest + AuthController + RequestAttribute

- [x] T-023-01 – Add `REMEMBER_ME_ATTRIBUTE = 'remember_me'` constant to `RequestAttribute` (FR-023-06).
  _Intent:_ New constant for the remember-me form field, following existing convention for attribute constants.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ File: `app/Contracts/Http/Requests/RequestAttribute.php`.

- [x] T-023-02 – Update `LoginRequest` to validate optional `remember_me` boolean field (FR-023-01, S-023-05, S-023-11).
  _Intent:_ Add `'remember_me' => ['sometimes', 'boolean']` to validation rules. Add accessor method (e.g., `rememberMe(): bool`) that returns `false` when absent. Update `processValidatedValues()` to extract the value.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ File: `app/Http/Requests/Session/LoginRequest.php`. Consider adding a `HasRememberMe` contract and trait for consistency, or add directly to `LoginRequest` for simplicity.

- [x] T-023-03 – Write failing tests for remember-me login behavior (FR-023-01, FR-023-04, S-023-01, S-023-02, S-023-05, S-023-06, S-023-11, NFR-023-03).
  _Intent:_ Red tests covering:
  (a) Login with `remember_me = true` → response contains remember cookie.
  (b) Login with `remember_me = false` → no remember cookie.
  (c) Login without `remember_me` field → no remember cookie (backward compat).
  (d) Login with invalid credentials + `remember_me = true` → 401, no cookie.
  (e) Login with `remember_me = 'not_a_boolean'` → 422 validation error.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`
  _Notes:_ Test extends appropriate base test class (Feature_v2). Check for cookie named `remember_web_*` or similar in response.

- [x] T-023-04 – Update `AuthController::login()` to pass `remember` flag to auth calls (FR-023-04, S-023-02, S-023-07, S-023-08).
  _Intent:_ Read `$remember = $request->rememberMe()`. Pass as second argument to `Auth::attempt([...], $remember)` for local auth. Pass to `Auth::login($user, $remember)` for LDAP auth.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  _Notes:_ File: `app/Http/Controllers/AuthController.php`. Both `Auth::attempt()` and `Auth::login()` accept `$remember` as their second parameter.

- [x] T-023-05 – Update login log messages to include remember flag (FR-023-04).
  _Intent:_ Append `[remember=true/false]` to existing log messages in `AuthController::login()` and `attemptLdapLogin()`.
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [x] T-023-06 – Write test: logout after remember-me login clears cookie and rotates token (FR-023-05, S-023-04).
  _Intent:_ Login with `remember_me = true` → logout → verify remember cookie is cleared in response. Verify `remember_token` changed in database.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`
  _Notes:_ `Auth::logout()` already handles cookie invalidation and token rotation via Laravel's `SessionGuard`. Just need to verify it works with our guard.

- [x] T-023-07 – Green: all backend tests pass including new remember-me tests (NFR-023-03, NFR-023-04).
  _Intent:_ All T-023-03 and T-023-06 tests pass. All pre-existing login tests remain green.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I2 – Frontend: Checkbox + Auth Service

- [x] T-023-08 – Update `auth-service.ts` to accept and send `remember_me` parameter (FR-023-03).
  _Intent:_ Modify `login(username, password)` to `login(username, password, remember_me = false)`. Include `remember_me` in the POST body.
  _Verification commands:_
  - `npm run check`
  _Notes:_ File: `resources/js/services/auth-service.ts`. Use `remember_me` as the parameter name to match the backend.

- [x] T-023-09 – Add "Remember Me" checkbox to `LoginForm.vue` (FR-023-02, UI-023-01, UI-023-02, UI-023-03, S-023-09, S-023-10).
  _Intent:_ Add a `remember_me` ref (default `false`). Add PrimeVue `Checkbox` component below the password field and above the "Lychee SE" text. Bind to `remember_me` ref. Pass to `AuthService.login()`. Only render inside the `is_basic_auth_enabled` template block.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  _Notes:_ File: `resources/js/components/forms/auth/LoginForm.vue`. Use PrimeVue `Checkbox` with proper label binding for accessibility.

- [x] T-023-10 – Green: frontend checks pass (FR-023-02, FR-023-03).
  _Intent:_ TypeScript compilation succeeds, no lint errors.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

---

## I3 – Translations

- [x] T-023-11 – Add English translation string for "Remember me" (NFR-023-05).
  _Intent:_ Add `'remember_me' => 'Remember me'` to the login dialog section in the English language file.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  _Notes:_ File: `lang/en/lychee.php` (or the appropriate file where `dialogs.login.*` keys live). Never modify `lang/php_*.json` files.

- [x] T-023-12 – Add placeholder translations for 21 other languages (NFR-023-05).
  _Intent:_ Add the same key with English text as placeholder in all other language files.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`
  _Notes:_ Files: `lang/<locale>/lychee.php` for each supported locale. Use English as placeholder.

---

## I4 – Integration Tests & Cleanup

- [x] T-023-13 – End-to-end test: remember cookie re-authenticates after session flush (S-023-03).
  _Intent:_ Login with `remember_me = true` → flush session → make authenticated request using remember cookie → verify user is authenticated.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-023-14 – End-to-end test: logout invalidates remember cookie (S-023-04).
  _Intent:_ Login with `remember_me = true` → logout → attempt request with old remember cookie → verify 401.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-023-15 – Run full quality gate (all scenarios).
  _Intent:_ Final quality gate before feature completion.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `make phpstan`

- [x] T-023-16 – Update knowledge map with remember-me login flow.
  _Intent:_ Add entry for remember-me in the LDAP/auth section of `docs/specs/4-architecture/knowledge-map.md`.
  _Verification commands:_
  - Visual review of knowledge-map.md.

- [x] T-023-17 – Update roadmap with Feature 023 completion status.
  _Intent:_ Move Feature 023 to appropriate status in `docs/specs/4-architecture/roadmap.md`.
  _Verification commands:_
  - Visual review of roadmap.md.

---

## Notes / TODOs

- **Remember cookie name:** Laravel auto-generates the cookie name as `remember_web_{sha1(guard_name)}`. For Lychee's `lychee` guard, the cookie will be named `remember_web_{sha1('lychee')}`. Tests should search for a cookie with prefix `remember_web_` rather than an exact name.
- **Guard configuration:** The `SessionOrTokenGuard::login()` method (line 274) already accepts `$remember = false`. The `recaller()` method is inherited from Laravel's `SessionGuard` and handles remember cookie reading. No changes to the guard are needed.
- **Remember duration (Q-023-01 resolved → Option C):** `config/auth.php` sets `'remember' => (int) env('REMEMBER_LIFETIME', 40320)` in the lychee guard config. Default is 4 weeks (40320 minutes). `SessionOrTokenGuard::createGuard()` reads this via `setRememberDuration()`. `.env.example` documents the `REMEMBER_LIFETIME` env variable.
- **No migration needed:** The `remember_token` column already exists in the `users` table from Laravel's default migration. `SessionOrTokenGuard` already calls `setRememberDuration()` in `createGuard()`.
- **Existing logout behavior:** `AuthController::logout()` calls `Auth::logout()` which already invalidates the remember cookie and rotates the token in the database (Laravel default behavior).
