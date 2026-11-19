# ADR-074-03: A Write-Only `password` Config Type

- **Status:** Accepted
- **Date:** 2026-09-27
- **Related features/specs:** Feature 074 (docs/specs/4-architecture/features/074-global-password/spec.md)
- **Related open questions:** Q-074-05

## Context

Feature 074 stores a gallery password in `configs`. The generic settings pipeline writes values verbatim (`SettingsController::setConfigs`) and returns them verbatim (`ConfigResource`). `is_secret` only hides a value from diagnostics.

## Decision

Add `ConfigType::PASSWORD = 'password'`:
- **Write:** in `setConfigs`, a non-empty value is stored as `Hash::make(value)`, explicitly at the write site with no model mutator. `''` clears it, and a config that is not sent stays unchanged. `SetConfigsRequest` enforces a minimum length of 4.
- **Read:** `ConfigResource` always returns `value = ''` for this type, plus `is_set: bool`.
- **UI:** both v7 and v8 `ConfigGroup` render a `PasswordField` (Set / Not set badge, never pre-filled, Clear).
- **Verification cost:** consumers must not call `Hash::check` per request. Feature 074 verifies with bcrypt only at unlock and then relies on an HMAC fingerprint (NFR-074-06, ADR-074-02).

A reversible `encrypted` type is deferred until a setting actually needs it.

## Consequences

### Positive
- No one-off endpoint or widget. Any future check-only secret gets hashing and masking for free.
- A setting of this type never reaches the admin UI, even as a hash.

### Negative
- The type changes the generic settings save and read path, so regression tests on `setConfigs` are required.
- Help text is static, so it cannot react to other settings.

## Alternatives Considered

- **Also add an `encrypted` type:** no consumer yet, and it needs an `APP_KEY` rotation story. Deferred.
- **Dedicated endpoint and widgets:** one-off code in three places. Rejected.

## Security / Privacy Impact

- The stored value is a bcrypt hash and is never serialised to clients.

## Operational Impact

- bcrypt runs only when the value is written (and, for Feature 074, at unlock).

## Links

- Related spec sections: FR-074-10, FR-074-11, FR-074-12, NFR-074-06
- Related ADRs: ADR-074-01, ADR-074-02
