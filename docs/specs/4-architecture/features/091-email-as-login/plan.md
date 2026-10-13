# Feature Plan 091 – Email as Login Username

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Complete (owner review pending)  
_Last updated:_ 2026-10-13

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
With `email_login_enabled` on, a user signs in with either their username or their email (S-091-03, S-091-04), an account with no email still signs in with its username (S-091-07), and self-registration needs only an email because the username falls back to it (S-091-09). With the setting off, every existing login and registration path behaves exactly as before (S-091-01, S-091-02, S-091-11, NFR-091-02).

## Scope Alignment
- **In scope:** the `email_login_enabled` config row (FR-091-01); the second `Auth::attempt()` in `AuthController::login()` (FR-091-02 … FR-091-06); conditional `username` rule in `RegistrationRequest` (FR-091-07); the username fallback in `Actions\User\Create` (FR-091-08, FR-091-09); `is_email_login_enabled` in `InitConfig`/`LycheeState`/`lychee.d.ts` (FR-091-10); the v7 and v8 login label (FR-091-11) and registration mirroring (FR-091-12); the WebAuthn identifier resolution and modal labels (FR-091-14); the `all_settings` and `dialogs` strings in all 23 locales; tests; knowledge map and roadmap.
- **Out of scope (spec Non-Goals):** LDAP, OAuth, and every part of WebAuthn except resolving the typed identifier (FR-091-14); the profile (FR-091-13 is "no change"); admin user management and install-time admin setup; making the email mandatory anywhere; schema changes.

## Dependencies & Interfaces
- `Illuminate\Support\Facades\Auth::attempt()` with the Eloquent user provider: any credential key other than `password` becomes a `where` clause, so `['email' => …, 'password' => …]` works without a custom provider.
- `users.email` is `nullable()->unique()` (`2021_12_04_181200_refactor_models`), so the email attempt can match at most one row.
- `BaseApiRequest::configs()` / `ConfigManager` for reading the new key inside `LoginRequest` and `RegistrationRequest`.
- `BaseConfigMigration` for seeding the `configs` row.
- `Spatie\TypeScriptTransformer` regenerates `resources/js/lychee.d.ts` from `InitConfig`.

## Assumptions & Risks
- **Assumptions:** `UsernameRule` (non-empty string, ≤100 chars) already admits every email that can exist in `users.email` (`char(100)`), so the login validation needs no change; the `login` throttle middleware counts requests, not `Auth::attempt()` calls, so the second attempt cannot change the rate limit (NFR-091-04).
- **Risks / Mitigations:**
  - *User enumeration through timing or message drift.* Mitigation: both attempts end in the same `UnauthenticatedException` with the same message, asserted by a test comparing the three failure cases (NFR-091-03, S-091-05, S-091-06).
  - *A username colliding with another account's email.* Mitigation: the order is fixed and specified (username first, FR-091-05) and covered by S-091-08.
  - *Mirroring the email into the username could overwrite a value the user typed.* Mitigation: the mirroring is gated on an `isUsernameMirrored` flag that flips off on manual input and back on when the field is cleared (FR-091-12).
  - *Relaxing the `username` rule unconditionally would let API clients create accounts with no username while the feature is off.* Mitigation: the rule is relaxed only when the config is on; `Actions\User\Create` still throws when both username and email are empty (FR-091-08, S-091-13).

## Increments
1. **T-091-01 … T-091-02 – config row + exposure.** Seed the key, expose `is_email_login_enabled`, regenerate the TypeScript contract, add the setting strings.
2. **T-091-03 … T-091-05 – login path.** Red feature test, then the second `Auth::attempt()` and the identifier-kind log line.
3. **T-091-06 … T-091-08 – creation path.** Red unit + feature tests, then the `Create` fallback and the conditional `RegistrationRequest` rule.
4. **T-091-09, T-091-09b – frontend and WebAuthn.** Login label and registration mirroring in v7 and v8; the identifier resolution in `WebAuthnLoginController::options()` and the matching modal labels.
5. **T-091-10 – gates.** `php-cs-fixer`, scoped tests, `make phpstan`, `npm run format`, `npm run check`; knowledge map and roadmap.

## Implementation Drift Gate
Run after T-091-09b: map FR-091-01 … FR-091-14 and S-091-01 … S-091-16 to code and tests, rerun the T-091-10 classes, record the result below.

**Report 2026-10-13** (agent):

| Requirement | Code | Tests |
|-------------|------|-------|
| FR-091-01 | `database/migrations/2026_10_13_150000_add_email_login_config.php` | migration applied on sqlite; `LangTest` pins the two `all_settings` strings in all 23 locales |
| FR-091-02 | the config guard in `AuthController::attemptLocalLogin()` | `testUsernameLoginWithSettingOff`, `testEmailLoginWithSettingOff`, `testLoginWithEmailWhenEmailLoginIsDisabled` |
| FR-091-03 | the second `Auth::attempt(['email' => …])` | `testUsernameLoginWithSettingOn`, `testEmailLoginWithSettingOn`, `testLoginWithEmailWhenEmailLoginIsEnabled` |
| FR-091-04 | username attempted first | `testAccountWithoutEmailUsesItsUsername` |
| FR-091-05 | same ordering | `testUsernameWinsOverAnotherAccountsEmail` |
| FR-091-06, NFR-091-03 | unchanged `UnauthenticatedException` | `testFailuresAreIndistinguishable` (compares status and message across the three failure shapes) |
| FR-091-07 | conditional `username` rule in `RegistrationRequest` | `testRegistrationWithoutUsernameUsesTheEmail`, `testRegistrationWithEmptyUsernameUsesTheEmail`, `testRegistrationWithoutUsernameIsRejectedWhenSettingOff`, plus the untouched `RegisterTest::testRegistrationRequiresValidData` |
| FR-091-08 | `Actions\User\Create::resolveUsername()` | `CreateUsernameFallbackTest` (5) |
| FR-091-09 | uniqueness checks left after the resolution | `testFallbackUsernameMustBeUnique`, `testRegistrationWithoutUsernameRejectsATakenIdentifier` |
| FR-091-10 | `InitConfig::$is_email_login_enabled`, `LycheeState.ts`, `lychee.d.ts` | `testInitExposesTheFlag`; `php artisan typescript:transform` reproduces the hand-added field byte for byte |
| FR-091-11, FR-091-12 | `LoginForm.vue`, `RegisterPage.vue`, `WebauthnModal.vue` (v7 + v8) | `npm run check` (types); no view-test harness in this repo, so the mirroring itself is owner-verifiable only |
| FR-091-13 | no change to the profile | `ProfileTest` and the rest of Feature_v2 unchanged |
| FR-091-14 | `WebAuthnLoginController::resolveIdentifier()` | `testWebAuthLoginOptionsEmailWhenEmailLoginEnabled`/`Disabled` |
| NFR-091-01 | no table-altering migration in this feature | — (`users.email` was already `nullable()->unique()`) |
| NFR-091-02 | default `'0'` | every pre-existing auth/registration test passes without touching the key |
| NFR-091-04 | one extra `Auth::attempt()`, config-gated | `LoginTest::testLoginRateMiddleware` still trips at the 11th request |

Gates: `vendor/bin/php-cs-fixer fix` → 0 of 2905 files changed; `vendor/bin/phpstan analyze` → no errors; `--testsuite Unit` → 1368 tests OK; `--testsuite Feature_v2` → 1242 tests, 69724 assertions OK; `--testsuite Install` → 12 OK; `npm run format`/`npm run check` → clean.

One defect found and fixed during the gate: `lang/en` gained two `all_settings` keys that `LangTest::testLanguageConsistency` then demanded in the other 22 locales. Inserting them by hit order put the `fr` pair in the wrong sections (that file alone lists `details` before `documentation`); they are now placed by enclosing section.

Not verified here: MySQL and PostgreSQL (CI), and an owner pass over the registration-form mirroring and the relabelled login/WebAuthn fields in a browser.
