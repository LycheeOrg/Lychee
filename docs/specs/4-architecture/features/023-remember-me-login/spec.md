# Feature 023 – Remember Me Login

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-02-28 |
| Owners | — |
| Linked plan | `docs/specs/4-architecture/features/023-remember-me-login/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/023-remember-me-login/tasks.md` |
| Roadmap entry | #023 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

Lychee currently requires users to log in every time they open the site because the session cookie expires after the configured `SESSION_LIFETIME` (default: 120 minutes). Users must re-enter credentials frequently, which degrades the user experience — especially on personal or home-hosted instances where the site is accessed repeatedly throughout the day.

This feature adds a "Remember Me" checkbox to the login form. When checked, a long-lived remember-me cookie is set alongside the session cookie, allowing the user to remain authenticated across browser restarts and session expiry. Laravel's built-in remember-me infrastructure (`remember_token` column, `SessionGuard::viaRemember()`) is already partially wired in `SessionOrTokenGuard` but is never activated because the login flow always passes `$remember = false`.

**Affected modules:** Login form (Vue3 frontend), `auth-service.ts` (API service layer), `LoginRequest` (request validation), `AuthController` (login orchestration), `SessionOrTokenGuard` (guard — already supports remember), `RequestAttribute` (constants), session/auth configuration, translations (22 languages).

**GitHub Issue:** [#3532](https://github.com/LycheeOrg/Lychee/issues/3532)

## Goals

1. **Persistent authentication:** When the user checks "Remember Me" at login, their session persists across browser restarts and beyond the normal session lifetime.
2. **Opt-in only:** Remember-me is opt-in via an explicit checkbox. The default login behaviour (session-only, expiring after `SESSION_LIFETIME`) remains unchanged.
3. **Leverage existing infrastructure:** Use Laravel's built-in `remember_token` + remember cookie mechanism already partially wired in `SessionOrTokenGuard`. No custom token scheme.
4. **LDAP compatibility:** Remember-me works for both local and LDAP-authenticated users (the provisioned local user record holds the `remember_token`).
5. **Secure by default:** The remember cookie uses `httpOnly`, `secure` (when configured), and `SameSite` attributes inherited from the session cookie configuration.

## Non-Goals

- Configurable remember duration via admin UI (use the `REMEMBER_LIFETIME` env variable; no settings page control). Default is 4 weeks (40320 minutes), loaded by `config/auth.php` guard config (Q-023-01 resolved → Option C).
- "Remember Me" for WebAuthn or OAuth login flows (those have their own session management).
- Server-side session extension (the session lifetime itself is unchanged; only the remember cookie provides persistence).
- "Keep me logged in forever" — the remember cookie has a finite duration (default: 4 weeks, configurable via `REMEMBER_LIFETIME` env variable).
- Per-user remember-me enable/disable toggle (all users who check the box get the feature).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-023-01 | Add a `remember` boolean parameter to the login API endpoint (`POST /Auth::login`). | When `remember = true`, `Auth::attempt()` or `Auth::login()` is called with `$remember = true`, setting a long-lived remember cookie. | `LoginRequest` validates `remember` as optional boolean (defaults to `false`). | Invalid type → validation error (422). Missing field → defaults to `false` (existing behaviour preserved). | Login log entry includes whether remember was requested. | Issue #3532 |
| FR-023-02 | Add a "Remember Me" checkbox to the login form (`LoginForm.vue`). | Checkbox is rendered below the password field. When checked, `remember: true` is sent with the login request. | Checkbox defaults to unchecked. Only visible when basic auth is enabled. | — | — | Issue #3532 |
| FR-023-03 | Pass the `remember` parameter through the auth service to the backend API. | `AuthService.login(username, password, remember)` includes the boolean in the POST body. | TypeScript enforces the parameter type. | — | — | FR-023-01 |
| FR-023-04 | `AuthController::login()` passes the `remember` flag to `Auth::attempt()` for local auth and `Auth::login($user, $remember)` for LDAP auth. | Remember cookie is set when `remember = true`. Session-only auth when `remember = false`. | Backend logs whether remember was used. | Auth failure behaves identically regardless of `remember` value. | Login log: "User (X) has logged in from IP [remember=true/false]". | FR-023-01 |
| FR-023-05 | On logout, the remember cookie is invalidated. The `remember_token` in the database is rotated (Laravel default behaviour via `Auth::logout()`). | After logout, the old remember cookie no longer authenticates the user. | Closing browser and reopening after logout → user is not authenticated. | — | — | Security |
| FR-023-06 | Add a `REMEMBER_ME_ATTRIBUTE` constant to `RequestAttribute`. | Constant `REMEMBER_ME_ATTRIBUTE = 'remember_me'` available for use in `LoginRequest`. | — | — | — | Convention |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-023-01 | Remember cookie must use `httpOnly`, `secure` (when HTTPS), and `SameSite` attributes consistent with session cookie configuration. Cookie duration defaults to 4 weeks (40320 minutes), configurable via `REMEMBER_LIFETIME` env variable (Q-023-01 resolved → Option C). | Security | Cookie attributes verified in browser dev tools during manual testing. Duration verified via `config('auth.guards.lychee.remember')`. | `config/auth.php` guard config, `config/session.php` cookie settings. | OWASP, Q-023-01 |
| NFR-023-02 | The `remember_token` column already exists in the `users` table. No migration needed for the token itself. | Backward compatibility | `remember_token` column present in User model `$fillable` / schema. | Existing migration. | Laravel default |
| NFR-023-03 | Existing sessions and login flows must not be affected when `remember` is absent or `false`. | Backward compatibility | All existing login tests pass without modification. | — | Regression prevention |
| NFR-023-04 | The feature must work with both session-based and session-or-token guard configurations. | Guard compatibility | Tests pass with the `session-or-token` guard (Lychee's custom guard). | `SessionOrTokenGuard` already supports `$remember` in `login()`. | Architecture |
| NFR-023-05 | Translation strings must be added for all 22 supported languages. | Internationalization | `lang/<locale>/*.php` files contain the new key. | Existing translation infrastructure. | Convention |

## UI / Interaction Mock-ups

### Login Form – With Remember Me Checkbox

```
┌─────────────────────────────────────────────┐
│                                             │
│  [🔑] [GitHub] [Google]                     │
│                                             │
│  ┌─────────────────────────────────────────┐│
│  │  Username                               ││
│  └─────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────┐│
│  │  Password                            🔒 ││
│  └─────────────────────────────────────────┘│
│  ⚠ Unknown user or invalid password         │
│                                             │
│  ☐ Remember me                              │
│                                             │
│              Lychee SE                      │
│  ┌───────────────┬─────────────────────────┐│
│  │    Cancel      │       Sign In           ││
│  └───────────────┴─────────────────────────┘│
└─────────────────────────────────────────────┘
```

**States:**
- Checkbox unchecked (default): login creates session-only cookie.
- Checkbox checked: login creates session cookie + remember-me cookie.
- Checkbox hidden when `is_basic_auth_enabled === false` (WebAuthn/OAuth-only mode).

### Login Form – No Basic Auth (unchanged)

```
┌─────────────────────────────────────────────┐
│  [🔑] [GitHub] [Google]                     │
│                                             │
│  ┌───────────────┬─────────────────────────┐│
│  │    Cancel      │       Sign In           ││
│  └───────────────┴─────────────────────────┘│
└─────────────────────────────────────────────┘
```

No "Remember Me" checkbox in this mode (no password-based login).

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-023-01 | Login with `remember = false` (default) → session cookie only, no remember cookie. User logged out when session expires. |
| S-023-02 | Login with `remember = true` → session cookie + remember cookie set. User remains authenticated after browser restart. |
| S-023-03 | Login with `remember = true`, session expires, user revisits → re-authenticated via remember cookie. |
| S-023-04 | Login with `remember = true`, then logout → remember cookie invalidated, `remember_token` rotated. User cannot re-authenticate with old cookie. |
| S-023-05 | Login with `remember` field absent → defaults to `false`, identical to S-023-01 (backward compatible). |
| S-023-06 | Login with invalid credentials + `remember = true` → auth fails, no remember cookie set. |
| S-023-07 | LDAP login with `remember = true` → LDAP authentication succeeds, local user provisioned, remember cookie set for the local user record. |
| S-023-08 | LDAP login with `remember = true`, LDAP server unreachable → falls back to local auth with remember. |
| S-023-09 | Login form checkbox defaults to unchecked. |
| S-023-10 | Login form checkbox hidden when `is_basic_auth_enabled === false`. |
| S-023-11 | Login request with `remember` as non-boolean value → validation error (422). |

## Test Strategy

- **Core (Unit):** Test `LoginRequest` validation accepts `remember_me` as optional boolean, defaults to `false`.
- **Application (Feature):** Test login with `remember_me = true` sets remember cookie; login with `remember_me = false` does not. Test logout clears remember cookie. Test LDAP + remember. Test backward compatibility (absent field).
- **REST (API):** Test `POST /Auth::login` with and without `remember_me` parameter. Verify cookie presence in response headers.
- **UI (Frontend):** Verify checkbox renders when basic auth enabled, hidden otherwise. Verify `AuthService.login()` sends `remember_me` parameter.
- **Docs/Contracts:** Verify translation strings present in all 22 languages.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-023-01 | `RequestAttribute::REMEMBER_ME_ATTRIBUTE = 'remember_me'` — new constant for the remember-me form field | Requests, Controller |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-023-01 | `POST /Auth::login` | Extended to accept optional `remember_me` boolean parameter | Default: `false`. Existing clients unaffected. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-023-01 | Remember Me checkbox visible (unchecked) | Basic auth enabled → checkbox rendered below password field, unchecked by default |
| UI-023-02 | Remember Me checkbox checked | User clicks checkbox → `remember_me: true` sent with login request |
| UI-023-03 | Remember Me checkbox hidden | `is_basic_auth_enabled === false` → checkbox not rendered |

## Telemetry & Observability

No new telemetry events. The existing login log channel (`Log::channel('login')`) is extended to include whether `remember` was requested:
- Success: `"User (X) has logged in from IP [remember=true]"`
- Failure: unchanged (remember flag irrelevant on failure).

## Documentation Deliverables

- Update knowledge map with remember-me login flow.
- Update roadmap with Feature 023 entry.

## Fixtures & Sample Data

No new fixtures needed. Existing test user accounts are sufficient.

## Spec DSL

```yaml
domain_objects:
  - id: DO-023-01
    name: RequestAttribute::REMEMBER_ME_ATTRIBUTE
    fields:
      - name: value
        type: string
        constraints: "'remember_me'"

routes:
  - id: API-023-01
    method: POST
    path: /Auth::login
    parameters:
      - name: username
        type: string
        required: true
      - name: password
        type: string
        required: true
      - name: remember_me
        type: boolean
        required: false
        default: false

ui_states:
  - id: UI-023-01
    description: Remember Me checkbox visible (unchecked default)
  - id: UI-023-02
    description: Remember Me checkbox checked
  - id: UI-023-03
    description: Remember Me checkbox hidden (no basic auth)
```

---

*Last updated: 2026-02-28*
