# ADR-074-01: Gallery Password as a Per-Visitor Unlock, Not a Guest Account

- **Status:** Accepted
- **Date:** 2026-09-27
- **Related features/specs:** Feature 074 (docs/specs/4-architecture/features/074-global-password/spec.md)
- **Related open questions:** Q-074-01, Q-074-02, Q-074-03

## Context

Feature 074 adds one shared password that must be entered before an anonymous visitor can see anything in the gallery. It can be built either as an "unlocked" state carried by the anonymous visitor, or by logging the visitor in as a special shared "guest" `User`.

About 65 call sites branch on `Auth::check()`, `Auth::guest()` or `Auth::user() !== null`. These include `AlbumPolicy`, `InitConfig`, `LoginRequired`, and the rating, upload, profile and sharing code. Access rules such as "public" and "shared with all users" depend on whether someone is a registered user.

## Decision

The gallery password is a **per-visitor unlock**. The visitor stays `Auth::guest()` throughout:
- The config `gallery_password` holds a bcrypt hash, or `''` when the feature is off. Its type is the write-only `password` config type ([ADR-074-03](ADR-074-03-password-config-type.md)).
- `POST Gallery::unlock` checks the password with bcrypt and hands the visitor an unlock token. The token is stored in an encrypted cookie ([ADR-074-02](ADR-074-02-gallery-unlock-encrypted-cookie.md)).
- The middleware `GalleryPasswordRequired` sits in the `api` group, so every route is covered unless it opts out. It lets a request through when the feature is off, when the user is logged in, or when the unlock token is valid.

The gate is strict: direct album links are not exempt (Q-074-03). Media files served directly by the web server are out of scope, and the setting's help text says so (Q-074-02).

## Consequences

### Positive
- No existing permission rule changes meaning. An unlocked visitor has exactly the rights of any anonymous visitor.
- No fake account shows up in user lists, sharing pickers or statistics, and none can be given rights by mistake.
- Changing or clearing the password invalidates every unlock, because the token is derived from the stored hash.
- New API routes are protected by default because the gate lives in the `api` group.

### Negative
- The feature needs its own unlock screen and endpoint instead of reusing the login dialog.
- Every public-by-design route (auth, AI callbacks) must opt out explicitly. A route missing from that list stays locked, which fails closed.

## Alternatives Considered

- **Guest user plus guest login page:** reuses the login flow, but the visitor becomes a registered user at every one of the ~65 checks. Rejected: large and ongoing security surface.
- **Password-only mode inside `LoginRequired`:** the smallest diff, but it mixes two concepts, and the login modal does not fit a password-only prompt. Rejected.

## Security / Privacy Impact

- The password is stored only as a bcrypt hash. Neither the hash nor the password is returned by any endpoint.
- Unlock attempts are throttled (10 per minute).
- Media under `uploads/` is not protected unless secure image links are enabled, and the setting's help text says so.

## Operational Impact

- No extra database query per request: the check uses the cached config and the request cookie.
- Operators who serve media directly should enable secure image links for full protection.

## Links

- Related spec sections: `docs/specs/4-architecture/features/074-global-password/spec.md#functional-requirements`
- Related ADRs: ADR-074-02, ADR-074-03
