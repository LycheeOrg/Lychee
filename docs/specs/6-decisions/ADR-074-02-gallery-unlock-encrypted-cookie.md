# ADR-074-02: Gallery Unlock Stored in an Encrypted Cookie

- **Status:** Accepted
- **Date:** 2026-09-27
- **Related features/specs:** Feature 074 (docs/specs/4-architecture/features/074-global-password/spec.md)
- **Related open questions:** Q-074-04

## Context

[ADR-074-01](ADR-074-01-gallery-password-visitor-unlock.md) makes the gallery password a per-visitor unlock. The unlock state needs a place to live. Server-side sessions for anonymous visitors are unreliable: they can expire, be garbage-collected, or fail to persist, which would lock a visitor out again at random.

## Decision

The unlock state lives in its own cookie, `lychee_gallery_unlock`, not in the server-side session:
- The value is `{"f": hash_hmac('sha3-256', <stored gallery_password hash>, APP_KEY), "exp": <unix timestamp or null>}`.
- The cookie is encrypted and signed by Laravel's `EncryptCookies` middleware (already in the `api` and `web` groups) with `APP_KEY`, so a client can neither read nor forge it. It is set `HttpOnly`, `SameSite=Lax`, `Secure` on HTTPS, and path `/`.
- The gate compares `f` with the current fingerprint using `hash_equals`, and checks `exp` on the server. A missing, tampered, stale or expired value means locked. bcrypt never runs per request.
- Lifetime comes from the expert config `gallery_password_cookie_lifetime` (days, default 30, `0` = expires when the browser closes). See Q-074-04.

## Consequences

### Positive
- The unlock does not depend on the server-side session lifecycle for anonymous visitors.
- Changing or clearing the password invalidates every unlock, because the fingerprint stops matching.
- Rotating `APP_KEY` also invalidates every unlock, which is acceptable.

### Negative
- The unlock cannot be revoked for one visitor alone. Only a password change revokes it, for everyone.
- The unlock is independent of login, so logging out does not relock the gallery.

## Alternatives Considered

- **Server-side session:** consistent with the album-unlock pattern, but unreliable for anonymous visitors. Rejected.
- **Plain signed cookie (no encryption):** equivalent protection, but it bypasses the existing `EncryptCookies` convention. Not chosen.

## Security / Privacy Impact

- The cookie reveals nothing: it is encrypted, and even decrypted it holds only an HMAC-SHA3-256 of a bcrypt hash.
- Knowing the stored hash (for example from a database dump) is not enough to forge a cookie. That needs `APP_KEY` twice: to compute the HMAC and to encrypt the cookie. Anyone holding `APP_KEY` plus the database can already forge Laravel sessions, so this adds no new exposure.
- Expiry is enforced on the server through `exp`, so a replayed cookie does not outlive its lifetime even if the client ignores `Max-Age`.
- A stolen cookie is a bearer token: it grants access until it expires or the password changes. This is the same exposure as any session or "remember me" cookie, reduced by `HttpOnly`, `Secure` and `SameSite=Lax`. Changing the password revokes access for everyone.

## Operational Impact

- No server-side storage. No extra database query per request.

## Links

- Related spec sections: `docs/specs/4-architecture/features/074-global-password/spec.md` FR-074-02, FR-074-07, FR-074-13, FR-074-14
- Related ADRs: ADR-074-01
