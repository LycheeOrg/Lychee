# Feature 091 – Email as Login Username

| Field | Value |
|-------|-------|
| Status | Testing (owner review pending) |
| Last updated | 2026-10-13 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #091 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Lychee authenticates on `users.username`: `LoginRequest` validates a username and `AuthController::login()` calls `Auth::attempt(['username' => …, 'password' => …])`. `users.email` exists, is `nullable()->unique()` since `2021_12_04_181200_refactor_models`, and is used only for notifications and the contact form.

This feature adds the configuration key `email_login_enabled`. When it is on, the login endpoint accepts the submitted identifier as **either** a username **or** an email address (Q-091-01 B), and self-registration may omit the username, in which case the email becomes the username (Q-091-03). Accounts with no email keep signing in with their username (Q-091-02 A), and the email stays optional and clearable in the profile (Q-091-04 B). The setting is off by default, so an upgrade changes nothing. No schema change.

## Goals
- A `email_login_enabled` boolean setting in the `Users Management` category, off by default.
- With the setting on, one credential field accepts a username or an email, and the login form says so.
- With the setting on, self-registration needs only an email, and the registration form shows the email it will use as the username while the user types.
- No account that can sign in today loses access when the setting is switched on or off.

## Non-Goals
- LDAP authentication (`LdapService`, `ProvisionLdapUser`): it keeps its own configured identifier attribute and is still attempted before local authentication, with the submitted value passed through unchanged.
- OAuth flows, which never look at the username. WebAuthn's assertion step is in scope only for resolving the identifier (FR-091-14); nothing else about it changes.
- Email verification, password reset, and notification delivery.
- Renaming or removing `users.username`.
- Admin user management (`AddUserRequest`, `SetUserSettingsRequest`, `UserManagementResource`, `CreateEditUser.vue`) and the install-time admin setup (`SetUpAdminController`, `ProfileController::store()`, `SetUpAdminRequest`): neither collects an email today, so both keep requiring a username. Exposing user emails to administrators is a separate, privacy-relevant change. The username fallback of FR-091-09 lives in `Actions\User\Create`, so these surfaces pick it up for free if they ever gain an email field.
- Making the email mandatory anywhere.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-091-01 | New config key `email_login_enabled`, category `Users Management`, `type_range` bool, value `0`, `order` 2, `is_expert` false, `is_secret` false (a boolean toggle carries nothing confidential, so it shows up in the Diagnostics configuration dump where a login problem is investigated) — seeded by a `BaseConfigMigration` like `2025_05_29_231404_user_registration_enabled`. | Admins toggle it in Settings → Users Management. | The generic settings endpoint validates the bool type range. | Key missing (migration not run) → `ConfigurationKeyMissingException` from `ConfigManager`, as for any other key. | — | Owner directive 2026-10-13 |
| FR-091-02 | With the setting **off**, `AuthController::login()` behaves exactly as today: one `Auth::attempt(['username' => …])`. | Unchanged. | Unchanged. | Unchanged. | Unchanged log lines. | Q-091-01 B |
| FR-091-03 | With the setting **on**, local authentication attempts `['username' => $identifier]` first and, only if that fails, `['email' => $identifier]`. The first match wins. | A user signs in with either their username or their email and the same password. | `LoginRequest` keeps validating the identifier as a non-empty string of at most 100 characters (`UsernameRule`); both usernames and emails are capped at 100 in the schema, so no rule change is needed. | Neither attempt matches → `UnauthenticatedException` with the existing generic message. | `Log::channel('login')` notice names the identifier kind that matched (`username` or `email`). | Q-091-01 B |
| FR-091-04 | Accounts with no email keep signing in with their username, including the admin created by the installer. | Username attempt succeeds before the email attempt is reached. | — | — | — | Q-091-02 A |
| FR-091-05 | If one account's `username` equals another account's `email`, the username owner is authenticated, because the username is attempted first. | Deterministic resolution. | — | — | The notice records `username`, so the log is unambiguous about which identifier matched. | Q-091-01 B |
| FR-091-06 | Failed logins keep the generic `Unknown user or invalid password` message and the existing error log line, which records the submitted identifier verbatim. | — | — | — | No new log channel or level. | NFR-091-03 |
| FR-091-07 | With the setting **on**, `RegistrationRequest` makes `username` optional (`sometimes`, nullable); `email` stays required. With it off, `username` stays required. | A registration payload with only email + password succeeds. | Username present but empty/over 100 chars → 422 from `UsernameRule`. | — | — | Q-091-03 |
| FR-091-08 | `Actions\User\Create::do()` resolves the username before any uniqueness check: a non-empty `$username` wins; otherwise the email is used; if both are empty it throws `InvalidPropertyException`. | `Create::do('', 'pw', 'a@b.c')` creates a user whose `username` and `email` are both `a@b.c`. | — | Both empty → `InvalidPropertyException('Username is required when no email is given')`. | — | Q-091-03 |
| FR-091-09 | Uniqueness is unchanged: the resolved username and the email are each checked against existing rows and raise `ConflictingPropertyException`. | — | — | Registering an email already used as another account's username or email → 409. | — | Q-091-03 |
| FR-091-10 | `InitConfig` exposes `is_email_login_enabled` so both frontends can read the flag; it is mirrored in `LycheeState` and in the generated `lychee.d.ts`. | — | — | — | — | Q-091-01 B |
| FR-091-11 | The v7 and v8 login forms label the credential field `dialogs.login.username_or_email` when the flag is on and `dialogs.login.username` when it is off. | — | — | — | — | Q-091-01 B |
| FR-091-12 | When the flag is on, the v7 and v8 `RegisterPage` mirror the email into the username field as the user types, for as long as the username field has not been edited by hand. Editing it stops the mirroring; clearing it resumes the mirroring. Form validity no longer requires a username when the flag is on. | Typing `a@b.c` into the email field shows `a@b.c` in the username field; typing `jane` over it keeps `jane` while the email keeps changing; clearing the username resumes mirroring. | The submit button stays disabled while email, password, or confirmation are empty or the confirmation mismatches. | — | — | Owner directive 2026-10-13 (Q-091-03) |
| FR-091-13 | The profile is unchanged: `UpdateProfileRequest` keeps the email optional, and `UpdateLogin::updateEmail()` keeps accepting `null` (which still deletes the user's notifications). A user who clears their email falls back to username login per FR-091-04. | — | — | — | — | Q-091-04 B |
| FR-091-14 | With the setting **on**, `WebAuthnLoginController::options()` resolves the submitted identifier to a username before narrowing the credential list: if no account carries that username, the account whose `email` matches supplies it. The v7 and v8 WebAuthn modals use the same label as the login form. | A user who signs in with their email finds their security key. | The identifier stays `sometimes` + `string`; an empty field is `null` after `ConvertEmptyStringsToNull` and keeps the discoverable-credential flow. | No match for either attribute → the identifier is passed through unchanged, as before. | — | Owner directive 2026-10-13 (consequence of Q-091-01 B) |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-091-01 | No database schema change. | `users.email` is already `nullable()->unique()`. | No table-altering migration in this feature; only a `configs` row. | `2021_12_04_181200_refactor_models` | Q-091-01 B |
| NFR-091-02 | Off by default, so upgrading changes no behaviour. | Backward safety of an auth path. | `AuthTest`/`LoginTest` pass unchanged without touching the new key. | — | Owner directive 2026-10-13 |
| NFR-091-03 | No user enumeration: the response and its status (401) are identical whether the identifier is unknown, is a known username with a wrong password, or is a known email with a wrong password. | Security. | Feature test asserting equal status and message across the three cases. | — | Owner directive 2026-10-13 |
| NFR-091-04 | At most one additional `Auth::attempt()` per login request, and only when the setting is on and the username attempt failed. | Keeping the cost of the login path bounded; the existing `login` rate-limit middleware is per request and is unaffected. | Code review of `AuthController::login()`; `LoginTest::testLoginRateMiddleware` still trips at the 11th request. | `routes/api_v2.php` login throttle | Q-091-01 B |

## UI / Interaction Mock-ups

Login form, setting **off** (unchanged):

```
+------------------------------------------+
|  Username                                |
|  [ ..................................... ]|
|  Password                                |
|  [ ..................................... ]|
|  [ ] Remember me                         |
|            [ Cancel ]   [  Sign-In  ]    |
+------------------------------------------+
```

Login form, setting **on**:

```
+------------------------------------------+
|  Username or email                       |
|  [ ..................................... ]|
|  Password                                |
|  [ ..................................... ]|
|  [ ] Remember me                         |
|            [ Cancel ]   [  Sign-In  ]    |
+------------------------------------------+
```

Registration page, setting **on** — the username mirrors the email while untouched:

```
  (1) user types the email               (2) user overrides the username
+-----------------------------+        +-----------------------------+
| Username                    |        | Username                    |
| [ jane@example.com        ] |        | [ jane                    ] |
|   ^ mirrors the email       |        |   ^ edited: mirroring stops |
| Email                       |        | Email                       |
| [ jane@example.com        ] |        | [ jane@example.org        ] |
| New password                |        | New password                |
| [ •••••••••               ] |        | [ •••••••••               ] |
| Confirm new password        |        | Confirm new password        |
| [ •••••••••               ] |        | [ •••••••••               ] |
|        [    Sign up      ]  |        |        [    Sign up      ]  |
+-----------------------------+        +-----------------------------+
```

Clearing the username field returns to state (1).

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-091-01 | Setting off, login with a correct username → 204, session established (unchanged). |
| S-091-02 | Setting off, login with a correct email → 401. |
| S-091-03 | Setting on, login with a correct username → 204. |
| S-091-04 | Setting on, login with a correct email → 204, same user. |
| S-091-05 | Setting on, login with a correct email and a wrong password → 401, generic message. |
| S-091-06 | Setting on, login with an unknown identifier → 401, same status and message as S-091-05. |
| S-091-07 | Setting on, an account with `email = null` signs in with its username → 204. |
| S-091-08 | Setting on, account A's username equals account B's email → A is authenticated. |
| S-091-09 | Setting on, registration with email + password and no username → 201, `username === email`. |
| S-091-10 | Setting on, registration with email + password and an explicit username → 201, the explicit username is kept. |
| S-091-11 | Setting off, registration without a username → 422. |
| S-091-12 | Setting on, registration whose email already exists as another account's username → 409. |
| S-091-13 | `Create::do()` with an empty username and a null email → `InvalidPropertyException`. |
| S-091-14 | `Auth::config`/`init` exposes `is_email_login_enabled` reflecting the config value. |
| S-091-15 | The login rate limiter still returns 429 on the 11th failed attempt with the setting on. |
| S-091-16 | Setting on, `POST WebAuthn::login/options` with the account's email returns the same `allowCredentials` as with its username; setting off, it narrows nothing. |

## Test Strategy
- **Models / Actions:** `tests/Unit/Actions/User/CreateUsernameFallbackTest.php` covers FR-091-08 and S-091-09, S-091-13 directly on `Actions\User\Create`.
- **REST API (v2):** `tests/Feature_v2/EmailLoginTest.php` covers S-091-01 … S-091-08, S-091-14, S-091-15 against `Auth::login` and the init payload; `tests/Feature_v2/User/RegistrationEmailLoginTest.php` covers S-091-09 … S-091-12.
- **Artisan commands:** none affected.
- **REST API (v2), WebAuthn:** `tests/Feature_v2/WebAuthTest.php` gains `testWebAuthLoginOptionsEmailWhenEmailLoginEnabled`/`Disabled` for S-091-16.
- **Frontend (v7/v8):** no unit-test harness for these views in this repo; covered by `npm run check` (types) and manual verification of the mirroring described in FR-091-12.
- **Docs/Contracts:** `lychee.d.ts` regains the new `InitConfig` field via `php artisan typescript:transform`; `lang/en/all_settings.php` gains the `documentation` and `details` strings for the new key.
