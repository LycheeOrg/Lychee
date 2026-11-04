# Feature 107 – WebAuthn Library Replacement

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-04 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #107 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
`laragear/webauthn` (v5.0.2) is archived and read-only, and Packagist lists it as abandoned in favour of `laravel/passkeys`. It implements WebAuthn itself (CBOR, COSE, signature checks), so nobody will fix its cryptographic code from now on. This feature moves the security key ceremonies to `web-auth/webauthn-lib` behind Lychee-owned code (ADR-107-01) and converts the credentials already stored, so nobody has to register again (ADR-107-02). The authenticator policy, the REST routes and the v7/v8 flows stay as they are. The only front-end change is the encoding the vendored helper uses for outgoing data.

Layers: `composer.json`, the `webauthn_credentials` table (two migrations), `App\Models\WebAuthnCredential`, `App\Services\WebAuthn\*`, the three `App\Http\Controllers\WebAuthn\*` controllers and their requests, `App\Models\User`, `config/auth.php`, `config/webauthn.php`, diagnostics, and `resources/js/vendor/webauthn/webauthn.ts`.

## Goals
- No `laragear/*` package left in `composer.lock` (`laragear/meta-model` goes with it); `composer audit` lists no abandoned package.
- Registration and login are validated by `web-auth/webauthn-lib` ^5.3 (Q-107-01 A).
- Every stored credential keeps working after the upgrade without re-registration (Q-107-02 A).
- Today's authenticator policy is kept, so U2F-only keys and keys without a PIN keep working (Q-107-03 A).
- The REST routes, request fields that clients send, the `WebAuthnResource` shape and the v7/v8 screens do not change.

## Non-Goals
- N-107-01: No change of policy: requiring user verification or discoverable credentials (Q-107-03 B) is a separate change.
- N-107-02: No attestation trust verification (FIDO Metadata Service, certificate chains). Lychee keeps asking for `attestation: none`.
- N-107-03: No new UI, no passkey autofill (conditional mediation), no authenticator names from AAGUIDs.
- N-107-04: No `laravel/passkeys`, no `@laravel/passkeys` npm package (Q-107-01 B rejected).
- N-107-05: No change to who may register a key. Today any signed-in user may, including accounts without `may_edit_own_settings` (logged as a plan follow-up).
- N-107-06: No replacement of the vendored JS helper by native `PublicKeyCredential.parse*FromJSON()`. Only its encoding changes (FR-107-10).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-107-01 | `POST /api/v2/WebAuthn::register/options` returns WebAuthn creation options for the signed-in user and keeps them in the session for one registration. | `200` JSON (W3C JSON form, base64url, `null` members left out): `rp` {`name` = `webauthn.relying_party.name`, `id` = relying party id (FR-107-06)}, `user` {`id` = user handle (FR-107-02), `name` = email, or `<name>#<host>` without email, `displayName` = `User::name`}, `challenge` = `webauthn.challenge.bytes` (32) random bytes, `pubKeyCredParams` = ES256 (`-7`), RS256 (`-257`), then EdDSA (`-8`) when `ext-sodium` is loaded, `timeout` = `webauthn.challenge.timeout` × 1000 ms (60000), `excludeCredentials` = the user's credentials (`[]` when none), `authenticatorSelection` = {`userVerification`: `discouraged`, `residentKey`: `preferred`} (`requireResidentKey` left out, which means false), `attestation`: `none`. Session: the serialised options, the id of the requesting user and an expiry timestamp (microseconds) under `webauthn.challenge.key` + `.attestation`. | Guest → `401` (Lychee's convention; Laragear's own request answered `403`). | – | None. | Q-107-03 A (today's `fastRegistration()`). |
| FR-107-02 | One user handle per user, the same for all their credentials. | Reuse the `user_handle` of the user's first credential. Without one, 32 random bytes. Stored base64url without padding in `webauthn_credentials.user_handle`. The handle never contains the user id, name or email. | – | – | – | WebAuthn L3 §5.4.3 (user handle must not carry personal data). |
| FR-107-03 | `POST /api/v2/WebAuthn::register` validates the browser's attestation and stores the credential. | The credential is validated with `AuthenticatorAttestationResponseValidator` against the session options. The session entry is consumed (single use). Accepted attestation statement formats: `none`, `fido-u2f`, `packed`, `tpm`, `android-key`, `apple`; no trust path or metadata check (N-107-02). One `webauthn_credentials` row is stored (FR-107-08 columns), `TaggedRouteCacheUpdated(USER)` is dispatched, `204`. | Request: `id`, `rawId`, `type` = `public-key`, `response.clientDataJSON`, `response.attestationObject` required strings, `response.transports` optional string list. Otherwise `422`. Guest → `401` (was `403` under Laragear). | No options in session, options requested by another account (the session signed in as someone else since), expired options (expiry passed), challenge, origin or relying party mismatch, unsupported format, credential id already stored → `422` with the reason; nothing stored. | None. | Today's `WebAuthTest` registration cases. |
| FR-107-04 | `POST /api/v2/WebAuthn::login/options` returns request options and keeps them in the session for one login. | `200` JSON: `challenge`, `timeout` 60000, `rpId`, `userVerification`: `preferred`, `allowCredentials` = the enabled credentials (`disabled_at` null) of the user given by `user_id` or `username`, as {`type`, `id`, `transports`}. `username` is resolved through `email` when `email_login_enabled` is on and no account carries it as username (FR-091-14). `allowCredentials` is left out when no user is given, the user is unknown or has no credential (discoverable login). Session key `webauthn.challenge.key` + `.assertion`. | `user_id` integer, `username` string, both optional; otherwise `422`. | `DISABLE_WEBAUTHN` → `403` (`WebAuthnDisabledExecption`). | None. | Q-107-03 A, Feature 091. |
| FR-107-05 | `POST /api/v2/WebAuthn::login` validates the assertion and signs the owner in. | The credential is found by `id` among the enabled credentials. The assertion is validated with `AuthenticatorAssertionResponseValidator` against the session options (consumed, single use), with the stored user handle as the expected handle. A response without a user handle (U2F keys) passes; a response with one must match it. When the options listed credentials, the credential must be one of them. On success `counter`, `backup_eligible`, `backup_status`, `uv_initialized` are saved and `Auth::login()` signs the owner in (session id regenerated), `204`. | Request: `id`, `rawId`, `type` = `public-key`, `response.authenticatorData`, `response.clientDataJSON`, `response.signature` required strings, `response.userHandle` nullable string. Lengths: `id`, `rawId` at most 1364 characters (a 1023-byte credential id), `response.userHandle` at most 86 (64 bytes), the other `response` members at most 16384. Otherwise `422`. | Unknown or disabled credential, missing or expired options, challenge, origin, relying party, signature, user handle, allowed-list or counter failure → `422` with the reason, still a guest. A credential that was not converted (FR-107-09) → `422` "This security key has to be removed and registered again." (registration excludes it, so the authenticator refuses to replace it while it is stored) `DISABLE_WEBAUTHN` → `403`. | None. | Today's `WebAuthnLoginController`. |
| FR-107-06 | Relying party and origin rule. | Relying party id = `WEBAUTHN_ID` (`webauthn.relying_party.id`), else the host of `APP_URL`. The browser origin must use `https` and its host must be the relying party id or a subdomain of it; port and the scheme written in `APP_URL` do not matter. When the relying party id is `localhost`, the origin must instead equal the `APP_URL` origin (scheme, host, port), which lets local installs use plain HTTP. Implemented with `CeremonyStepManagerFactory::setAllowedOrigins(WebAuthnFactory::allowedOrigins(), allowSubdomains: true)`: an empty list outside `localhost`. | – | Mismatch → `422` (FR-107-03/05). | – | Parity with Laragear `CheckRelyingPartyIdContained` (host compare, HTTPS or localhost). The library's `setSecuredRelyingPartyId()` is deprecated since 5.2, hence the `localhost` rule through an allowed origin. |
| FR-107-07 | `GET`, `PATCH`, `DELETE /api/v2/WebAuthn` keep their behaviour. | List the signed-in user's credentials as `WebAuthnResource` (`id`, `alias`, `created_at`); rename (alias 5–255 characters) by the owner or an administrator; delete the signed-in user's own credential; `TaggedRouteCacheUpdated(USER)` after edit/delete. | Unchanged (`ListCredentialsRequest`, `EditCredentialRequest`, `DeleteCredentialRequest`). | Unchanged (`401`/`403`/`404`). | – | Today's `WebAuthnManageController`. |
| FR-107-08 | `webauthn_credentials` holds what `web-auth/webauthn-lib` needs to rebuild a `CredentialRecord`. | Columns kept: `id` (credential id, base64url), `authenticatable_type`/`authenticatable_id`, `alias`, `counter`, `rp_id`, `origin`, `transports`, `aaguid`, `public_key` (encrypted with `APP_KEY`, now base64url COSE key), `attestation_format`, `certificates`, `disabled_at`, timestamps. Added: `user_handle` (string, not null), `backup_eligible`, `backup_status`, `uv_initialized` (nullable booleans). Dropped: `user_id` (its value moves to `user_handle`). A rebuilt record uses attestation type `none`, an empty trust path and the nil AAGUID when `aaguid` is null. | – | – | – | ADR-107-02. |
| FR-107-09 | Stored credentials are converted in place by migration. | Schema migration: each `user_id` becomes `user_handle` = the UUID without dashes, lower case. Laragear sent that 32-character hex string as `user.id` and the front end base64url-decoded it, so these are exactly the bytes the authenticators hold, base64url-encoded. Data migration: each `public_key` is decrypted, its PEM key is turned into a COSE key (EC P-256 → `kty` 2, `alg` −7, `crv` 1; RSA → `kty` 3, `alg` −257; Ed25519 → `kty` 1, `alg` −8, `crv` 6) and encrypted again. Re-running it changes nothing. Each schema step is skipped when it is already done, so a run cut short (MySQL commits every DDL statement on its own) can be started again; the same holds for `down()`. Rows are read in batches (`lazyById`). `down()` turns COSE back into PEM and `user_handle` back into `user_id`. | A row that is already COSE is skipped. | A row that cannot be decrypted (rotated `APP_KEY`) or parsed is left untouched and counted. One `Log::warning` gives the count and the credential ids, never key material. Such a row fails login (FR-107-05) and is reported by diagnostics (FR-107-12). | `Log::warning` (see Telemetry). | Q-107-02 A, ADR-107-02. |
| FR-107-10 | The vendored front-end helper sends the W3C JSON encoding. | `rawId` and every `response` member (`clientDataJSON`, `attestationObject`, `authenticatorData`, `signature`, `userHandle`) are sent as base64url without padding; a missing `userHandle` is sent as `null`. Decoding of the server options is unchanged. | – | – | – | `web-auth/webauthn-lib` rejects padded base64 for `clientDataJSON`, `authenticatorData`, `signature`. |
| FR-107-11 | Laragear is removed. | `composer.json` no longer requires `laragear/webauthn`. `User` no longer implements `WebAuthnAuthenticatable` and gets its own `webAuthnCredentials()` (`morphMany` on `authenticatable`) and `webAuthnUserName()`. `config/auth.php` uses the `eloquent` driver. No `Laragear\` reference is left in `app/`, `config/`, `database/`, `tests/`. | – | – | – | Q-107-01 A. |
| FR-107-12 | Diagnostics report credentials that are not usable. | `WebAuthnCredentialsCheck` (Errors pipe) adds one warning when credentials cannot be decrypted or are not COSE keys: "N WebAuthn credential(s) could not be read (was APP_KEY changed?). Their owners have to delete them in their profile, then register them again." No ids, no user names. Rows are read in batches (`lazyById`). | Skipped when the table does not exist or WebAuthn is disabled. | – | – | Q-107-02 A. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-107-01 | Lychee code does no WebAuthn parsing or signature checking of its own: CBOR, COSE, authenticator data, client data and signatures go through `web-auth/webauthn-lib` / `web-auth/cose-lib`. The only key handling Lychee owns is the one-time PEM ↔ COSE conversion in the migration, covered by round-trip and signature tests. | The point of the feature: maintained cryptographic code. | Code review; `grep -r "openssl_verify\|sodium_crypto_sign_verify" app/` empty. | `web-auth/webauthn-lib` ^5.3. | Q-107-01 A. |
| NFR-107-02 | Only the approved packages enter `composer.lock`: `web-auth/webauthn-lib`, `web-auth/cose-lib`, `spomky-labs/cbor-php`, `spomky-labs/pki-framework`, `symfony/serializer`, `symfony/property-info`, `symfony/property-access` (and their own dependencies, listed in the plan). | AGENTS.md dependency rule. | `composer show --locked` diff recorded in the plan; `composer audit` without abandoned packages. | – | Owner approval 2026-11-04 (Q-107-01 A). |
| NFR-107-03 | Behaviour parity: every scenario of today's `WebAuthTest` keeps its outcome (status codes, guest or authenticated). Only the error message texts may change, and guests on the two registration endpoints get `401` like every other Lychee endpoint instead of Laragear's `403` (the v7/v8 UI only calls them while signed in). | Q-107-03 A: a dependency swap, not a policy change. | `WebAuthTest` green with the same cases. | – | Q-107-03 A. |
| NFR-107-04 | No secret or key material in logs or responses: public keys, challenges and user handles are not logged; error responses give the validation reason only. Challenges are single use, belong to the account that asked for them (registration) and expire after `webauthn.challenge.timeout` seconds, compared to the microsecond. | Security. | Tests S-107-04, S-107-14; code review of `Log::` calls. | – | WebAuthn L3 §13.4.3. |
| NFR-107-05 | Migrations run on SQLite, MySQL and PostgreSQL. | Lychee supports all three. | Local SQLite tests; CI on MySQL/PostgreSQL. | – | Project convention. |

## UI / Interaction Mock-ups
No visible change: the v7/v8 login modal (`WebauthnModal`) and the profile security key list (`SetSecondFactor`, `SetSecondFactorLine`) keep their layout, texts and flows.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-107-01 | Signed-in admin asks for registration options → `200` with FR-107-01 members; session holds the options. |
| S-107-02 | Guest asks for registration options → `401`. |
| S-107-03 | Signed-in admin posts the recorded `none` attestation for the stored options → `204`; the credential is listed with its id. |
| S-107-04 | Registration with expired options → `422`; nothing stored. Registration without options in the session → `422`. Second registration with the same options → `422`. |
| S-107-05 | Guest posts an attestation → `401`; nothing stored. |
| S-107-06 | Registration of a credential id already stored → `422`. Registration of a second key reuses the user's handle (FR-107-02). |
| S-107-07 | Login options with `user_id` → `allowCredentials` holds the admin's credential. |
| S-107-08 | Login options with `username` → same. |
| S-107-09 | Login options with the email: `email_login_enabled` on → credential listed; off → `allowCredentials` left out. |
| S-107-10 | Login options without identifier → `allowCredentials` left out. |
| S-107-11 | Login with the recorded assertion (no user handle, U2F style) → `204`, authenticated; `counter` updated. |
| S-107-12 | Login with a wrong signature → `422`, guest. |
| S-107-13 | Login with a different challenge → `422`, guest. |
| S-107-14 | Login replayed with the same options (consumed) → `422`; login with expired options → `422`. |
| S-107-15 | Discoverable login: assertion carrying the stored user handle → `204`; carrying another handle → `422`. |
| S-107-16 | Login options for user A, assertion with user B's credential → `422` (not in `allowCredentials`). |
| S-107-17 | Login with a disabled or unknown credential → `422`, guest. |
| S-107-18 | `DISABLE_WEBAUTHN` → login options and login `403`. |
| S-107-19 | List / edit / delete cases of today's `WebAuthTest` keep their outcomes (owner, other user, admin, unknown id). |
| S-107-20 | Converter: PEM keys of the three types (EC P-256, RSA 2048, Ed25519) → COSE; a signature made with the original private key verifies with the converted key; COSE → PEM gives back the original key. |
| S-107-21 | Data migration on a Laragear row (encrypted PEM) → encrypted COSE; second run no change; a row encrypted with another key is left untouched, counted in the warning and reported by diagnostics. |
| S-107-22 | The recorded login fixture stored as a Laragear row (PEM) and converted by the migration → login `204`. |
| S-107-23 | Schema migration: Laragear `user_id` with dashes (PostgreSQL form) and without → `user_handle` = lower-case hex without dashes. `up()` on a Laragear-shaped table fills `user_handle` and drops `user_id`; `down()` restores `user_id`; a run cut short after its first column completes when started again. |
| S-107-24 | Front-end encoder: bytes → unpadded base64url; empty → `""`. Outgoing credential has no `+`, `/`, `=`. |
| S-107-25 | Browser, Chrome virtual authenticator (v8 and v7): register a key in the profile, log out, log in through the WebAuthn modal with and without username. |
| S-107-26 | Browser, U2F virtual authenticator (CTAP1, no resident key, no user verification): register, username-first login → success. |
| S-107-27 | Browser, upgrade path: key registered on `master` (Laragear), migrations run, login on this branch with the same authenticator → success. |
| S-107-28 | Registration options requested as the admin, the session then signs in as another account, that account posts the attestation → `422`, nothing stored. |
| S-107-29 | Login with a `response` member, `id` or `userHandle` longer than its FR-107-05 limit → `422`, guest. |

## Test Strategy
- **Models / Actions / Services:** Unit tests for the PEM ↔ COSE conversion of the data migration (S-107-20, the migration file is `require`d as in `TitleBackfillTest`) and the user-handle normalisation (S-107-23). A test helper `Tests\Support\WebAuthn\SoftAuthenticator` creates key pairs and signs authenticator data and client data, so login scenarios that no recorded fixture covers (S-107-15, S-107-16) can be built in the test.
- **REST API (v2):** `tests/Feature_v2/WebAuthTest.php` rewritten on the new session format with the same recorded fixtures re-encoded as base64url (S-107-01 … 19). `WebAuthnCredentialMigrationTest` (Feature_v2) for S-107-21/22.
- **Diagnostics:** `tests/Feature_v2/Diagnostics/WebAuthnCredentialsCheckTest.php` (S-107-21 diagnostics part), `AuthDisabledCheckTest` unchanged.
- **Artisan commands:** None.
- **Frontend (v7/v8):** Vitest for the base64url encoder (S-107-24); `vp run typecheck`. Browser check with Playwright and Chrome's virtual authenticator on a scratch instance (S-107-25 … 27).
- **Docs/Contracts:** `WebAuthnResource` keeps its fields, so `lychee.d.ts` does not change (checked with `php artisan typescript:transform`).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-107-01 | `App\Models\WebAuthnCredential`: table `webauthn_credentials`, string key `id`, casts `public_key` encrypted, `transports`/`certificates` array, `backup_*`/`uv_initialized` boolean, `disabled_at` datetime; `hasReadablePublicKey()`, `toCredentialRecord()`, `fillFromCredentialRecord()`, `toDescriptor()`. | models |
| DO-107-02 | `App\Services\WebAuthn\CeremonyStore`: puts and pulls creation/request options in the session with an expiry; creation options are tied to the requesting user id. | services |
| DO-107-03 | `App\Services\WebAuthn\WebAuthnFactory`: serializer (`toJson`, `toBrowserArray`, `fromJson`, `parseCredential`), attestation statement support (FR-107-03 formats), algorithm manager (ES256, RS256, EdDSA), validators with the origin rule (`allowedOrigins()`, FR-107-06), relying party id. | services |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-107-01 | REST POST /api/v2/WebAuthn::register/options | FR-107-01 | `RegisterOptionsRequest`, route name `webauthn.register.options` |
| API-107-02 | REST POST /api/v2/WebAuthn::register | FR-107-03 | `RegisterCredentialRequest`, `webauthn.register` |
| API-107-03 | REST POST /api/v2/WebAuthn::login/options | FR-107-04 | `LoginOptionsRequest`, `webauthn.login.options`, outside `gallery_password` |
| API-107-04 | REST POST /api/v2/WebAuthn::login | FR-107-05 | `LoginCredentialRequest`, `webauthn.login`, outside `gallery_password` |
| API-107-05 | REST GET/PATCH/DELETE /api/v2/WebAuthn | FR-107-07 | unchanged requests |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| – | – | None. |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-107-01 | `Log::warning` from the data migration | Count of unconverted credentials and their credential ids; no key, handle or user data. |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-107-01 | `tests/Feature_v2/WebAuthTest.php` constants | Recorded `none` attestation and assertion from today's tests, re-encoded as unpadded base64url, plus the fixture key as PEM and as COSE. |
| FX-107-02 | `tests/Support/WebAuthn/SoftAuthenticator.php` | Builds signed assertions for generated keys. |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| – | – | No change. |

## Telemetry & Observability
No new events. The data migration writes one `Log::warning` when rows could not be converted (TE-107-01). Diagnostics surface the same count (FR-107-12).

## Documentation Deliverables
- ADR-107-01 (library), ADR-107-02 (credential conversion).
- Roadmap row 107; knowledge-map entry for the WebAuthn module (routes, services, table, user handle rule).

## Fixtures & Sample Data
See FX-107-01 and FX-107-02.

## Spec DSL

```
domain_objects:
  - id: DO-107-01
    name: WebAuthnCredential
    table: webauthn_credentials
    fields:
      - { name: id, type: string, note: "credential id, base64url" }
      - { name: user_handle, type: string, note: "base64url, same for all credentials of a user" }
      - { name: public_key, type: text, note: "encrypted base64url COSE key" }
      - { name: counter, type: unsignedBigInteger, nullable: true }
      - { name: backup_eligible, type: boolean, nullable: true }
      - { name: backup_status, type: boolean, nullable: true }
      - { name: uv_initialized, type: boolean, nullable: true }
  - id: DO-107-02
    name: CeremonyStore
  - id: DO-107-03
    name: WebAuthnFactory
routes:
  - { id: API-107-01, method: POST, path: "/api/v2/WebAuthn::register/options" }
  - { id: API-107-02, method: POST, path: "/api/v2/WebAuthn::register" }
  - { id: API-107-03, method: POST, path: "/api/v2/WebAuthn::login/options" }
  - { id: API-107-04, method: POST, path: "/api/v2/WebAuthn::login" }
  - { id: API-107-05, method: "GET|PATCH|DELETE", path: "/api/v2/WebAuthn" }
cli_commands: []
telemetry_events:
  - { id: TE-107-01, event: "Log::warning (unconverted WebAuthn credentials)" }
fixtures:
  - { id: FX-107-01, path: tests/Feature_v2/WebAuthTest.php }
  - { id: FX-107-02, path: tests/Support/WebAuthn/SoftAuthenticator.php }
ui_states: []
```

## Appendix
- Laragear facts this spec relies on (v5.0.2): `AddAcceptedAlgorithms` offers ES256, RS256 and EdDSA (with sodium) only, `attestation: none`; `MakeWebAuthnCredential` stores `user_id` = the `user.id` sent in the options (a 32-character hex UUID); the vendored helper decodes `user.id` with base64url, so authenticators hold `base64url_decode(hex)` (24 bytes) as user handle; `public_key` is a PEM string under the `encrypted` cast.
- `web-auth/webauthn-lib` facts: `CheckUserHandle` with an expected handle accepts an empty response handle; `CheckAllowedCredentialList` enforces `allowCredentials` when it is not empty; `CheckBackupEligibilityIsUnchanged` is skipped while `backup_eligible` is null; the default algorithm manager has ES256 and RS256 only; `PublicKeyCredentialOptionsDenormalizer::normalize()` keeps an empty `allowCredentials`, so Lychee removes it (FR-107-04).
