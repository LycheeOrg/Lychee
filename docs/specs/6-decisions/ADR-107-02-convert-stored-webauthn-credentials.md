# ADR-107-02: Convert stored WebAuthn credentials in place

- **Status:** Accepted
- **Date:** 2026-11-04
- **Related features/specs:** Feature 107 (docs/specs/4-architecture/features/107-webauthn-replacement/spec.md)
- **Related open questions:** Q-107-02

## Context

`webauthn_credentials` was written by Laragear. Each row holds the public key as PEM under Laravel's `encrypted` cast (`APP_KEY`), plus the user handle as a `uuid` column `user_id`. `web-auth/webauthn-lib` (ADR-107-01) rebuilds a `CredentialRecord` from a COSE-encoded key and the raw user handle bytes. Dropping the rows would lock out every user whose only sign-in is a security key, including administrators on instances with `DISABLE_BASIC_AUTH`.

Two facts make a lossless conversion possible:
- Laragear only ever accepted ES256 on P-256, RS256 and Ed25519 keys (`AddAcceptedAlgorithms`) with `attestation: none`, so every key type maps to exactly one COSE algorithm.
- Laragear sent `user.id` as a 32-character hex UUID, which the vendored front-end helper base64url-decoded. Authenticators therefore hold `base64url_decode(hex)` (24 bytes) as the user handle, and base64url-encoding those bytes gives the hex string back.

## Decision

Keep the table and convert rows with two migrations:
1. Schema: add `user_handle` (string), `backup_eligible`, `backup_status`, `uv_initialized` (nullable booleans). Fill `user_handle` with `user_id` lower-cased and without dashes, then make it not null and drop `user_id`.
2. Data: decrypt each `public_key`, convert the PEM key to a COSE key (EC2/ES256/P-256, RSA/RS256, OKP/EdDSA/Ed25519) and encrypt it again. Rows already in COSE form are skipped, so a second run is a no-op. Rows that cannot be decrypted or parsed are left as they are and listed (ids only) in one `Log::warning`. `down()` converts COSE back to PEM.

The conversion code lives inside the migration file, so later changes to app classes cannot break it. Diagnostics (`WebAuthnCredentialsCheck`) report rows that are still unusable, and logging in with one returns a "removed and registered again" error. Each step of the schema migration (and of its `down()`) is skipped when it is already done, so a run cut short on MySQL, where every DDL statement commits on its own, can be started again.

## Consequences

### Positive
- Nobody has to register a key again, and WebAuthn-only administrators keep access.
- No compatibility code remains at runtime: after the migration every convertible row has the new format. Rows the migration cannot read stay unusable, and their owners delete them and register again.
- Rollback with `migrate:rollback` restores the Laragear format.

### Negative
- Conversion depends on the `APP_KEY` the rows were written with. After a key rotation without `APP_PREVIOUS_KEYS`, the affected rows stay unusable and their owners re-register.
- Lychee owns PEM ↔ COSE code for three key types, which round-trip and signature tests cover.

## Alternatives Considered

- **B – drop the rows, users re-register:** no conversion code, but it locks out key-only users and administrators. Recovery would need `php artisan lychee:reset_admin`.
- **New table, read both:** keeps old rows, but adds a permanent legacy read path, against the greenfield stance.

## Security / Privacy Impact

- Public keys stay encrypted at rest with `APP_KEY`, as before.
- No key material, user handle or user data is logged, only credential ids and counts.

## Operational Impact

- One-time migration over a small table, no measurable cost.
- Administrators see unusable credentials in diagnostics.

## Links

- Related spec sections: `docs/specs/4-architecture/features/107-webauthn-replacement/spec.md` FR-107-08, FR-107-09, FR-107-12
- Related ADRs: ADR-107-01
