# Feature Plan 023 – Remember Me Login

_Linked specification:_ `docs/specs/4-architecture/features/023-remember-me-login/spec.md`
_Status:_ Draft
_Last updated:_ 2026-02-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

**User value:** Users can check a "Remember Me" box during login to stay authenticated across browser restarts and session expiry, eliminating the need to re-enter credentials on every visit to their Lychee instance.

**Success signals:**
- Login with "Remember Me" checked sets a long-lived remember cookie that survives browser restart.
- Login without "Remember Me" (or with absent field) behaves identically to the current implementation.
- Logout properly invalidates the remember cookie and rotates the `remember_token`.
- LDAP login with "Remember Me" works identically to local login.
- All existing login tests pass without modification (backward compatibility).
- Frontend checkbox visible only when basic auth is enabled.

## Scope Alignment

- **In scope:**
  - `LoginRequest` validation: add optional `remember_me` boolean field.
  - `RequestAttribute` constant: `REMEMBER_ME_ATTRIBUTE = 'remember_me'`.
  - `AuthController::login()`: pass `remember` flag to `Auth::attempt()` (local) and `Auth::login()` (LDAP).
  - `auth-service.ts`: send `remember_me` in POST body.
  - `LoginForm.vue`: add checkbox with `remember_me` ref, pass to login service.
  - Translation strings for "Remember me" in 22 languages.
  - Feature tests for remember cookie presence/absence.

- **Out of scope:**
  - Admin-configurable remember duration via settings UI (use `REMEMBER_LIFETIME` env variable; default 4 weeks).
  - WebAuthn/OAuth remember-me support.
  - Remember-me for the migration/setup authentication flow.
  - Session lifetime configuration changes.

## Dependencies & Interfaces

- **Laravel SessionGuard** — Provides the remember cookie mechanism. `SessionOrTokenGuard` extends this and already accepts `$remember` in `login()`.
- **`remember_token` column** — Already exists in the `users` table (Laravel default migration). No new migration needed.
- **`SessionOrTokenGuard::login()`** — Already accepts `$remember = false` parameter (line 274). Just needs to be called with `true`.
- **PrimeVue Checkbox** — Use PrimeVue's `Checkbox` component for the login form.
- **Translation system** — 22 language files under `lang/<locale>/`.

## Assumptions & Risks

- **Assumptions:**
  - The `remember_token` column in the `users` table is functional (Laravel default).
  - `SessionOrTokenGuard`'s `recaller()` method (inherited from Laravel's `SessionGuard`) correctly handles remember-me cookies.
  - The `remember` duration defaults to 4 weeks (40320 minutes), set via `config/auth.php` guard config with `REMEMBER_LIFETIME` env override (Q-023-01 resolved → Option C).

- **Risks / Mitigations:**
  - **R1: Remember cookie not set due to guard misconfiguration.** Mitigation: Write a feature test that asserts the cookie is present in the response.
  - **R2: LDAP users may not persist `remember_token` correctly.** Mitigation: LDAP users are provisioned as local `User` records which have the `remember_token` column. Test explicitly.
  - **R3: Checkbox accessibility.** Mitigation: Use PrimeVue's accessible `Checkbox` component with proper `aria-label` and `id`/`label` binding.

## Implementation Drift Gate

After each increment, verify:
1. `make phpstan` — Zero errors
2. `php artisan test` — All tests pass
3. `vendor/bin/php-cs-fixer fix --dry-run` — Code style clean
4. `npm run check` — TypeScript/Vue checks pass
5. Check `tasks.md` checkboxes match actual progress

Record drift findings in this plan's Follow-ups section.

## Increment Map

### I1 – Backend: LoginRequest + AuthController + RequestAttribute (~45 min)

- _Goal:_ Wire the `remember_me` parameter through the backend login flow.
- _Preconditions:_ Clean test suite, understanding of `SessionOrTokenGuard` remember behavior.
- _Steps:_
  1. Write failing test: POST `/Auth::login` with `remember_me = true` → verify remember cookie in response.
  2. Write failing test: POST `/Auth::login` with `remember_me = false` → verify no remember cookie.
  3. Write failing test: POST `/Auth::login` without `remember_me` → verify no remember cookie (backward compat).
  4. Add `REMEMBER_ME_ATTRIBUTE = 'remember_me'` to `RequestAttribute`.
  5. Add `HasRememberMe` contract interface and `HasRememberMeTrait` trait (or add directly to `LoginRequest`), with validation rule: `'remember_me' => ['sometimes', 'boolean']` defaulting to `false`.
  6. Update `AuthController::login()`:
     - Read `$remember = $request->remember()` (or similar accessor).
     - Pass to `Auth::attempt([...], $remember)` for local auth.
     - Pass to `Auth::login($user, $remember)` for LDAP auth.
  7. Update login log messages to include remember flag.
  8. Run `make phpstan` and `php artisan test`.
- _Commands:_ `make phpstan`, `XDEBUG_MODE=off php artisan test --no-coverage`
- _Exit:_ Login with `remember_me = true` sets remember cookie. Login without or with `false` does not. All existing tests green.
- _Refs:_ FR-023-01, FR-023-04, FR-023-05, FR-023-06, S-023-01 through S-023-08, S-023-11, NFR-023-02, NFR-023-03, NFR-023-04

### I2 – Frontend: Checkbox + Auth Service (~45 min)

- _Goal:_ Add the "Remember Me" checkbox to the login form and wire it to the API.
- _Preconditions:_ I1 complete (backend accepts `remember_me`).
- _Steps:_
  1. Update `auth-service.ts`: add `remember_me` parameter to `login()` method.
  2. Update `LoginForm.vue`:
     - Add a `remember_me` ref (default `false`).
     - Add PrimeVue `Checkbox` below the password field, bound to `remember_me`.
     - Pass `remember_me.value` to `AuthService.login()`.
     - Checkbox only rendered inside the `v-if="is_basic_auth_enabled"` block.
  3. Verify checkbox defaults to unchecked.
  4. Verify checkbox hidden when basic auth is not enabled.
  5. Run `npm run check` and `npm run format`.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Login form shows "Remember Me" checkbox. Checking it sends `remember_me: true` to the backend.
- _Refs:_ FR-023-02, FR-023-03, S-023-09, S-023-10, UI-023-01, UI-023-02, UI-023-03

### I3 – Translations (~30 min)

- _Goal:_ Add translation strings for "Remember me" in all 22 supported languages.
- _Preconditions:_ I2 complete (knows the translation key needed).
- _Steps:_
  1. Add English translation: `'remember_me' => 'Remember me'` in the appropriate dialog/login section of `lang/en/lychee.php` (or equivalent).
  2. Add placeholder translations for all 21 other languages using the English string.
  3. Run `php artisan test` to verify translations don't break.
- _Commands:_ `XDEBUG_MODE=off php artisan test --no-coverage`, `vendor/bin/php-cs-fixer fix --dry-run`
- _Exit:_ Translation key available in all 22 languages.
- _Refs:_ NFR-023-05

### I4 – Integration Tests & Cleanup (~30 min)

- _Goal:_ End-to-end verification and documentation updates.
- _Preconditions:_ I1, I2, I3 complete.
- _Steps:_
  1. End-to-end test: login with remember → close session → request with remember cookie → authenticated.
  2. End-to-end test: login with remember → logout → request with old remember cookie → not authenticated.
  3. Verify backward compatibility: existing login tests pass unchanged.
  4. Run full quality gate.
  5. Update knowledge map.
  6. Update roadmap.
- _Commands:_ `make phpstan`, `XDEBUG_MODE=off php artisan test --no-coverage`, `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`
- _Exit:_ All quality gates pass, feature complete.
- _Refs:_ All scenarios

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-023-01 | I1 | Login without remember → session only |
| S-023-02 | I1 | Login with remember → remember cookie set |
| S-023-03 | I1 / I4 | Session expires, remember cookie re-authenticates |
| S-023-04 | I1 / I4 | Logout invalidates remember cookie |
| S-023-05 | I1 | Absent field → backward compatible |
| S-023-06 | I1 | Invalid credentials + remember → no cookie |
| S-023-07 | I1 | LDAP + remember |
| S-023-08 | I1 | LDAP unreachable + remember fallback |
| S-023-09 | I2 | Checkbox defaults to unchecked |
| S-023-10 | I2 | Checkbox hidden without basic auth |
| S-023-11 | I1 | Non-boolean remember → 422 |

## Analysis Gate

Not yet completed. Will be run after spec, plan, and tasks agree.

## Exit Criteria

- [ ] `LoginRequest` validates optional `remember_me` boolean (defaults to `false`)
- [ ] `AuthController::login()` passes `remember` to `Auth::attempt()` and `Auth::login()`
- [ ] LDAP login respects `remember` flag
- [ ] Login log messages include remember flag
- [ ] Frontend checkbox renders conditionally (basic auth only)
- [ ] `AuthService.login()` sends `remember_me` parameter
- [ ] Remember cookie set on `remember_me = true`, absent on `false`
- [ ] Logout invalidates remember cookie
- [ ] All existing login tests pass unchanged (backward compatibility)
- [ ] Translation strings present in all 22 languages
- [ ] PHPStan, php-cs-fixer, npm check/format all clean
- [ ] Knowledge map and roadmap updated

## Follow-ups / Backlog

- **Admin-configurable remember duration via UI** — Consider adding a settings UI control for the remember cookie lifetime (currently configurable via `REMEMBER_LIFETIME` env variable, default 4 weeks).
- **"Remember Me" for WebAuthn** — Investigate if WebAuthn sessions can benefit from a similar persistence mechanism.
- **Session management UI** — Allow users to see and revoke active remember-me sessions (list of devices/tokens).

---

*Last updated: 2026-02-28*
