# Feature 091 Tasks – Email as Login Username

_Status: Complete_  
_Last updated: 2026-10-13

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-091-01 – Config migration for `email_login_enabled` (FR-091-01).  
  _Intent:_ `database/migrations/2026_10_13_150000_add_email_login_config.php`, modelled on `2025_05_29_231404_user_registration_enabled`.  
  _Verification commands:_  
  - `php artisan migrate`

- [x] T-091-02 – Expose the flag and its strings (FR-091-10, S-091-14).  
  _Intent:_ `InitConfig::$is_email_login_enabled`, `resources/js/stores/LycheeState.ts`, `resources/js/lychee.d.ts`, `lang/*/all_settings.php` (`documentation` + `details`), `lang/*/dialogs.php` (`login.username_or_email`).  
  _Note:_ `LangTest::testLanguageConsistency` requires every `lang/en` key in all 22 other locales, so both dictionaries were extended everywhere — `dialogs.username_or_email` translated per locale, the two `all_settings` strings translated for `fr`/`de` and left in English elsewhere (as several existing `user_registration_enabled` entries are). `lang/fr/all_settings.php` lists `details` before `documentation`, unlike every other locale; the two inserts are placed by enclosing section, not by order.  
  _Verification commands:_  
  - `php artisan typescript:transform` (regenerated `lychee.d.ts` matches the hand-added field exactly)  
  - `php artisan test --filter=LangTest`

- [x] T-091-03 – Login feature test, red (FR-091-02 … FR-091-06, NFR-091-03, S-091-01 … S-091-08, S-091-14, S-091-15).  
  _Intent:_ `tests/Feature_v2/EmailLoginTest.php`.  
  _Verification commands:_  
  - `php artisan test --filter=EmailLoginTest` (red on the email cases only)

- [x] T-091-04 – Second `Auth::attempt()` on the email, gated on the config (FR-091-02, FR-091-03, FR-091-04, FR-091-05).  
  _Intent:_ `AuthController::login()` plus a private `attemptLocalLogin()` helper returning the matched identifier kind.  
  _Verification commands:_  
  - `php artisan test --filter=EmailLoginTest`

- [x] T-091-05 – Identifier kind in the login notice (FR-091-03, FR-091-05).  
  _Verification commands:_  
  - `php artisan test --filter=EmailLoginTest`  
  - `php artisan test --filter=AuthControllerTest`

- [x] T-091-06 – `Create` username-fallback unit test, red (FR-091-08, S-091-09, S-091-13).  
  _Intent:_ `tests/Unit/Actions/User/CreateUsernameFallbackTest.php`.  
  _Verification commands:_  
  - `php artisan test --filter=CreateUsernameFallbackTest` (red)

- [x] T-091-07 – `Actions\User\Create::resolveUsername()` (FR-091-08, FR-091-09).  
  _Verification commands:_  
  - `php artisan test --filter=CreateUsernameFallbackTest`

- [x] T-091-08 – Conditional `username` rule in `RegistrationRequest` + feature test (FR-091-07, S-091-09 … S-091-12).  
  _Intent:_ `tests/Feature_v2/User/RegistrationEmailLoginTest.php`, `app/Http/Requests/Profile/RegistrationRequest.php`.  
  _Verification commands:_  
  - `php artisan test --filter=RegistrationEmailLoginTest`

- [x] T-091-09 – Frontend: login label and registration mirroring (FR-091-11, FR-091-12).  
  _Intent:_ `resources/js/{v7,v8}/components/forms/auth/LoginForm.vue`, `resources/js/{v7,v8}/views/RegisterPage.vue`.  
  _Verification commands:_  
  - `npm run check`

- [x] T-091-09b – WebAuthn identifier resolution (FR-091-14, S-091-16).
  _Intent:_ `WebAuthnLoginController::resolveIdentifier()`, the v7/v8 `WebauthnModal.vue` labels, `tests/Feature_v2/WebAuthTest.php`.
  _Verification commands:_
  - `php artisan test --filter=WebAuthTest`

- [x] T-091-11 – CodeRabbit review round 1 on PR #4871 (FR-091-03, FR-091-05, FR-091-15, FR-091-16, S-091-08, S-091-17, S-091-18).  
  _Intent:_ `AuthController::resolveIdentifierAttribute()`, `RegistrationRequest` email `max:100`, `Actions\User\Create::assertIdentifierIsUnambiguous()`, strengthened `testUsernameWinsOverAnotherAccountsEmail`, three new registration tests.  
  _Verification commands:_  
  - `php artisan test --filter=EmailLoginTest`  
  - `php artisan test --filter=RegistrationEmailLoginTest`  
  - counter-check: the strengthened collision test fails against the pre-review `attemptLocalLogin()`

- [x] T-091-10 – Quality gate and drift gate (all FR/NFR).  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=EmailLoginTest`  
  - `php artisan test --filter=RegistrationEmailLoginTest`  
  - `php artisan test --filter=CreateUsernameFallbackTest`  
  - `php artisan test --filter=AuthControllerTest`  
  - `php artisan test --filter=AuthTest`  
  - `php artisan test --filter=WebAuthTest`  
  - `php artisan test --filter=LoginTest`  
  - `vendor/bin/phpunit --testsuite Unit` / `--testsuite Feature_v2` / `--testsuite Install`  
  - `php artisan test --filter=LangTest`  
  - `make phpstan`  
  - `npm run format` / `npm run check`
