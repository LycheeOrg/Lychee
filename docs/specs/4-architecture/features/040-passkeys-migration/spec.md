# Feature 040 – Passkeys Migration (laragear/webauthn → laravel/passkeys)

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-05-12 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/040-passkeys-migration/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/040-passkeys-migration/tasks.md` |
| Roadmap entry | #040 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The `laragear/webauthn` PHP package is abandoned and no longer maintained, introducing security and compatibility risks as the Lychee application evolves. This feature migrates the WebAuthn/passkey authentication layer from `laragear/webauthn ^5.0` to the official first-party `laravel/passkeys` package. The migration touches the Composer dependency, database schema, PHP models, controllers, routes, configuration, and the TypeScript/Vue frontend client.

## Goals

1. Remove the abandoned `laragear/webauthn` dependency and replace it with the maintained `laravel/passkeys` package.
2. Preserve all existing user-facing passkey behaviours: registration, login (with optional username/user-id pre-selection), credential listing, alias editing, and credential deletion.
3. Provide a data migration path so that existing `webauthn_credentials` rows are moved to the new `passkeys` table without requiring users to re-register their devices.
4. Update all affected PHP classes, routes, config, tests, frontend services, and Vue components.
5. Maintain the `DISABLE_WEBAUTHN` feature flag and the `AuthServiceProvider::isWebAuthnEnabled()` guard.
6. Keep the diagnostic check (`AuthDisabledCheck`) referencing the correct table name.

## Non-Goals

- Adding new passkey UX features beyond what the current laragear implementation exposes (e.g., device management UI redesign).
- Migrating password-based or OAuth authentication; only the passkey flow is in scope.
- Supporting the `laravel/passkeys` confirmation/re-authentication flow (the `GET /passkeys/confirm/options` + `POST /passkeys/confirm` routes) — Lychee does not use that pattern today.
- Changing the existing `.env` variable names (`DISABLE_WEBAUTHN`, `WEBAUTHN_NAME`, `WEBAUTHN_ID`) until a separate deprecation plan is agreed.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-040-01 | Users can register a new passkey for their account. | Authenticated user calls the registration options endpoint, browser creates a credential, calls the registration endpoint; HTTP 201/204 returned and credential persisted in `passkeys` table. | Request must include valid attestation response fields. | Invalid or expired challenge returns HTTP 422. | No new telemetry beyond existing cache-tag invalidation on `CacheTag::USER`. | Existing behaviour. |
| FR-040-02 | Users can log in using a previously registered passkey. | Unauthenticated user calls the login options endpoint (optionally scoped to a username or user-id), browser produces an assertion, calls the login endpoint; user session established. | `username` or `user_id` are optional query filters. Assertion must pass signature and challenge verification. | Invalid signature or expired challenge returns HTTP 422; user remains unauthenticated. | None. | Existing behaviour. |
| FR-040-03 | Authenticated users can list their registered passkeys. | `GET /api/v2/WebAuthn` returns a JSON array of `WebAuthnResource` objects (`id`, `alias`, `created_at`). | Requires authenticated session. | Unauthenticated request returns HTTP 401. | None. | Existing behaviour. |
| FR-040-04 | Authenticated users can rename (alias) a passkey. | `PATCH /api/v2/WebAuthn` with `id` and `alias` updates the passkey's friendly name. | Alias must be 5–255 characters. | Record not found → HTTP 404; validation failure → HTTP 422. | `CacheTag::USER` cache invalidation dispatched. | Existing behaviour. |
| FR-040-05 | Authenticated users can delete a passkey. | `DELETE /api/v2/WebAuthn` with `id` removes the credential. | Requires authenticated session and ownership. | Record not found → HTTP 404. | `CacheTag::USER` cache invalidation dispatched. | Existing behaviour. |
| FR-040-06 | Existing passkey credential data is migrated from `webauthn_credentials` to `passkeys` without requiring users to re-register. | Migration script maps old columns to new schema and copies all rows. | Migration is reversible (down drops new table, recreates old). | If a row cannot be mapped, migration fails and rolls back. | None. | Data continuity requirement. |
| FR-040-07 | The `DISABLE_WEBAUTHN` feature flag continues to work. | Setting `DISABLE_WEBAUTHN=true` causes `AuthServiceProvider::isWebAuthnEnabled()` to return `false`, blocking all passkey endpoints. | Passkey endpoints return HTTP 403 when the flag is set. | None beyond the existing behaviour. | None. | Existing behaviour. |
| FR-040-08 | The diagnostic check `AuthDisabledCheck` correctly identifies the presence of registered passkeys for admin users. | Check queries the `passkeys` table (new name) instead of `webauthn_credentials`. | Diagnostic panel shows an error when basic-auth is disabled and no admin has an OAuth or passkey credential. | None. | None. | Existing behaviour. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-040-01 | All existing `WebAuthTest` tests pass against the new implementation. | Test regression prevention. | `php artisan test --filter=WebAuthTest` reports 0 failures. | `laravel/passkeys` test helpers or equivalent fixtures. | Quality gate. |
| NFR-040-02 | PHPStan level-6 analysis passes with 0 errors on all modified files. | Static-analysis quality gate. | `make phpstan` exits 0. | Updated type declarations on new model/contract. | Quality gate. |
| NFR-040-03 | `php-cs-fixer` reports no style violations on modified PHP files. | Code style. | `vendor/bin/php-cs-fixer fix --dry-run` exits 0. | Existing `.php-cs-fixer.php` ruleset. | Coding conventions. |
| NFR-040-04 | Frontend TypeScript compiles without errors. | Type safety. | `npm run check` exits 0. | `@laravel/passkeys` npm package or updated custom client. | Quality gate. |
| NFR-040-05 | The `laravel/passkeys` package must not have known high/critical vulnerabilities at the time of installation. | Security. | `gh-advisory-database` check before composer install. | GitHub Advisory Database. | Security policy. |
| NFR-040-06 | Data migration is idempotent: running it twice does not corrupt data. | Reliability. | Integration test with a seeded `webauthn_credentials` table. | SQLite test database. | Quality gate. |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-040-01 | Authenticated user registers a passkey → credential stored, cache tag invalidated, HTTP 204 returned. |
| S-040-02 | Unauthenticated user attempts passkey registration → HTTP 403 returned. |
| S-040-03 | Passkey registration with expired challenge → HTTP 422 returned, no credential stored. |
| S-040-04 | User logs in with a valid passkey (user_id scoped) → session established, HTTP 204 returned. |
| S-040-05 | User logs in with a valid passkey (username scoped) → session established, HTTP 204 returned. |
| S-040-06 | User logs in with anonymous options (no user scope) → options returned with all credentials. |
| S-040-07 | Login attempt with wrong signature → HTTP 422, user remains unauthenticated. |
| S-040-08 | Login attempt with wrong challenge → HTTP 422, user remains unauthenticated. |
| S-040-09 | Authenticated user lists passkeys → JSON array returned. |
| S-040-10 | Unauthenticated user attempts to list passkeys → HTTP 401. |
| S-040-11 | Authenticated user edits passkey alias → alias updated, cache tag invalidated. |
| S-040-12 | Unauthenticated user attempts edit/delete → HTTP 401. |
| S-040-13 | Authenticated user deletes a passkey → credential removed, cache tag invalidated. |
| S-040-14 | `DISABLE_WEBAUTHN=true` → all passkey endpoints return HTTP 403. |
| S-040-15 | Data migration: rows from `webauthn_credentials` appear in `passkeys` with correct field mapping. |
| S-040-16 | `AuthDisabledCheck` diagnostic correctly queries `passkeys` table and reports missing admin credentials. |
| S-040-17 | User model `delete()` cleans up passkeys rows for the deleted user. |

## Test Strategy

- **Core / Unit:** No pure-domain logic changes; no unit tests added.
- **Application:** Update `RequiresEmptyWebAuthnCredentials` trait to reference `passkeys` table. Update `WebAuthTest.php` to use new package fixtures/stubs.
- **REST (Feature_v2):** All 12 existing `WebAuthTest` scenarios re-validated against new package. Migration test validates column mapping.
- **CLI:** No CLI commands added.
- **UI (JS):** `npm run check` validates TypeScript compilation. No new Vitest tests required unless frontend service logic changes significantly.
- **Docs/Contracts:** `config/passkeys.php` published artefact documented. `.env.example` updated to reflect any renamed/new env vars.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-040-01 | `Passkey` model (`Laravel\Passkeys\Passkey` or custom extension) — replaces `WebAuthnCredential`. Fields: `id`, `user_id` (morphable), `name`/`alias`, `public_key`, `counter`, `rp_id`, `origin`, `transports`, `aaguid`, `attestation_format`, `disabled_at`, `created_at`, `updated_at`. | app/Models, app/Http/Resources |
| DO-040-02 | `WebAuthnResource` Data class — updated constructor to accept `Passkey` instead of `WebAuthnCredential`. | app/Http/Resources/Models |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-040-01 | `POST /api/v2/WebAuthn::register/options` | Get passkey registration challenge for authenticated user. | Internally delegates to `laravel/passkeys` `GenerateRegistrationOptions` action. |
| API-040-02 | `POST /api/v2/WebAuthn::register` | Submit passkey attestation for authenticated user. | Internally delegates to `laravel/passkeys` `StorePasskey` action. |
| API-040-03 | `POST /api/v2/WebAuthn::login/options` | Get passkey assertion challenge, optionally scoped to a user. | Internally delegates to `laravel/passkeys` `GenerateVerificationOptions` action. |
| API-040-04 | `POST /api/v2/WebAuthn::login` | Submit passkey assertion to authenticate. | Internally delegates to `laravel/passkeys` `VerifyPasskey` action. |
| API-040-05 | `GET /api/v2/WebAuthn` | List authenticated user's passkeys. | Custom controller; `laravel/passkeys` does not expose a list route. |
| API-040-06 | `PATCH /api/v2/WebAuthn` | Rename a passkey (alias). | Custom controller; `laravel/passkeys` does not expose an edit route. |
| API-040-07 | `DELETE /api/v2/WebAuthn` | Delete a passkey by id. | Custom controller or delegate to `laravel/passkeys` `DeletePasskey` action. |

> **Note:** The existing Lychee API routes (`/api/v2/WebAuthn::*`) are preserved verbatim to avoid breaking frontend clients. The `laravel/passkeys` automatic routes (`/passkeys/*`, `/user/passkeys/*`) should be disabled via `Passkeys::ignoreRoutes()` since Lychee manages its own routing.

### CLI Commands / Flags

None introduced by this feature.

### Telemetry Events

No new telemetry events. Existing `TaggedRouteCacheUpdated::dispatch(CacheTag::USER)` calls are preserved.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-040-01 | Passkey registration form (SetSecondFactor.vue) | User clicks "Add passkey" → calls `WebAuthnService.register()` which now uses `@laravel/passkeys` client or updated custom client. |
| UI-040-02 | Passkey login dialog (WebauthnModal.vue) | User opens passkey login → calls `WebAuthnService.login()` with new service routes. |

## Telemetry & Observability

No changes to telemetry. The existing `CacheTag::USER` invalidation is preserved on all write operations.

## Documentation Deliverables

1. Update `config/passkeys.php` (published) with Lychee-specific defaults (`relying_party_id`, `allowed_origins`, `timeout`).
2. Update `.env.example` to document `PASSKEYS_USER_HANDLE_SECRET` (required by `laravel/passkeys`).
3. Update `docs/specs/4-architecture/knowledge-map.md` to replace the `laragear/webauthn` entry with `laravel/passkeys`.
4. Update roadmap to mark feature 040 complete after all tasks pass.

## Fixtures & Sample Data

The existing test fixtures in `WebAuthTest.php` (pre-computed challenge data and credential bytes) will need to be regenerated or adapted for `laravel/passkeys`'s internal `Challenge` and `ByteBuffer` equivalents (or the package's own testing helpers).

## Spec DSL

```yaml
domain_objects:
  - id: DO-040-01
    name: Passkey
    fields:
      - name: id
        type: string
        constraints: "primary key, base64url"
      - name: user_id
        type: integer
        constraints: "foreign key → users.id"
      - name: name
        type: string
        constraints: "nullable, alias for credential"
      - name: public_key
        type: text
        constraints: "encrypted"
      - name: counter
        type: integer
        constraints: "nullable, unsigned"
      - name: rp_id
        type: string
      - name: origin
        type: string
      - name: transports
        type: json
        constraints: "nullable"
      - name: aaguid
        type: uuid
        constraints: "nullable"
      - name: attestation_format
        type: string
        constraints: "default: none"
      - name: disabled_at
        type: datetime
        constraints: "nullable"
  - id: DO-040-02
    name: WebAuthnResource
    fields:
      - name: id
        type: string
      - name: alias
        type: string
        constraints: "nullable"
      - name: created_at
        type: string
        constraints: "ISO 8601"

routes:
  - id: API-040-01
    method: POST
    path: /api/v2/WebAuthn::register/options
  - id: API-040-02
    method: POST
    path: /api/v2/WebAuthn::register
  - id: API-040-03
    method: POST
    path: /api/v2/WebAuthn::login/options
  - id: API-040-04
    method: POST
    path: /api/v2/WebAuthn::login
  - id: API-040-05
    method: GET
    path: /api/v2/WebAuthn
  - id: API-040-06
    method: PATCH
    path: /api/v2/WebAuthn
  - id: API-040-07
    method: DELETE
    path: /api/v2/WebAuthn

ui_states:
  - id: UI-040-01
    description: Passkey registration form
  - id: UI-040-02
    description: Passkey login dialog
```

## Appendix

### Package Comparison

| Aspect | laragear/webauthn ^5.0 | laravel/passkeys |
|--------|------------------------|-----------------|
| Composer package | `laragear/webauthn` | `laravel/passkeys` |
| npm package | vendored `webauthn.ts` (MIT, Italo Cabrera) | `@laravel/passkeys` |
| DB table | `webauthn_credentials` | `passkeys` |
| User trait | `WebAuthnAuthentication` | `PasskeyAuthenticatable` |
| User contract | `WebAuthnAuthenticatable` | `PasskeyUser` |
| Credential model | `Laragear\WebAuthn\Models\WebAuthnCredential` | `Laravel\Passkeys\Passkey` |
| Challenge model | `Laragear\WebAuthn\Challenge\Challenge` | Internal to package |
| ByteBuffer | `Laragear\WebAuthn\ByteBuffer` | Internal to package |
| Config file | `config/webauthn.php` | `config/passkeys.php` |
| Route registration | Manual (routes/api_v2.php) | Auto via service provider; disabled with `Passkeys::ignoreRoutes()` |
| Alias field | `alias` column | `name` column (to be mapped) |
| Maintainer | Abandoned | Official Laravel first-party |

### Open Questions

All questions are logged in [docs/specs/4-architecture/open-questions.md](../open-questions.md):
- **Q-040-01 (Medium):** Does `laravel/passkeys` expose the `Passkey` model's `name` column that maps to Lychee's `alias` concept, or must we add a custom column?
- **Q-040-02 (High):** Is there a supported path to migrate `webauthn_credentials` rows to the `passkeys` schema without losing users' enrolled devices?
- **Q-040-03 (Medium):** Does `laravel/passkeys` ship testing helpers (fake challenge injection, credential stubs) equivalent to `laragear`'s `Challenge` + `ByteBuffer` to allow the existing test vectors in `WebAuthTest` to be reused?
- **Q-040-04 (Low):** Should the `WEBAUTHN_NAME` and `WEBAUTHN_ID` env vars be preserved as aliases for `laravel/passkeys` config, or deprecated in favour of `PASSKEYS_*` equivalents?

---

*Last updated: 2026-05-12*
