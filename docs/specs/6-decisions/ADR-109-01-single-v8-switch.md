# ADR-109-01: One Switch for v8 (`V8_ENABLED`)

- **Status:** Accepted
- **Date:** 2026-11-07
- **Related features/specs:** Feature 109 (docs/specs/4-architecture/features/109-v8-struct-of-arrays-only/spec.md), Feature 051 (admin setup page), Features 058–069, 079, 093 (v3 struct-of-arrays listings)
- **Related open questions:** Q-109-01, Q-109-02

## Context

Three independent environment switches decided what an installation ran:
- `V8_ENABLED` (`features.v8`): v8 features on the server (breadcrumbs, local geodecoding, v8 and AI Vision settings).
- `NUXT_UI_ENABLED` (`features.nuxt_ui`): which bundle `vueapp.blade.php` serves (`app-v8.ts` or `app.ts`), the SVG icon, the first-admin redirect target (ADR-0007).
- `STRUCT_OF_ARRAY_ENABLED` (`features.struct-of-array`): the v3 struct-of-arrays listing requests (19 FormRequests) and, through `InitConfig`, whether the shared Pinia stores take their v3 or v2 path.

No code tied them together, so every v8 listing view carried a v2 and a v3 branch, and combinations such as "v8 bundle without v3 listings" or "v8 features on the v7 bundle" had to keep working. The owner wants v8 to run on struct-of-arrays only, as the first step of a settings simplification.

## Decision

- `features.nuxt_ui` / `NUXT_UI_ENABLED` and `features.struct-of-array` / `STRUCT_OF_ARRAY_ENABLED` are removed.
- `features.v8` / `V8_ENABLED` is the only switch: it serves the v8 bundle (and its icon and first-admin page), enables the v8 server features, and gates the 19 v3 listing requests and quick naming (403 / not offered without it). v3 endpoints that were never gated stay ungated.
- v8 always takes the v3 listing path: `app-v8.ts` sets `LycheeState.is_struct_of_array_enabled = true` at boot; `InitConfig` no longer carries the field. v7 never sets it, so the shared stores keep serving v7 through v2.

## Consequences

### Positive
- One setting to run v8; no installation can serve v8 against disabled v3 endpoints.
- v8 components lose their v2 branches; the v2 store paths are reached by v7 only and can go when v7 is retired.
- Test configuration matches production: `phpunit.xml` already sets `V8_ENABLED=true`.

### Negative
- v8 features on the v7 bundle (v7 face recognition, AI Vision settings, breadcrumbs, local geodecoding) are no longer possible: `V8_ENABLED=true` always serves v8.
- No switch remains to fall back to v2 listings in v8 while troubleshooting.
- v3 listing endpoints are unavailable to API clients on instances that serve v7.

## Alternatives Considered

- **Remove the SoA flag, v3 always on (Q-109-01 A):** simplest API, but keeps `nuxt_ui` and `v8` as two switches and exposes v3 on v7 instances. Not chosen by the owner.
- **Gate v3 on `nuxt_ui` (Q-109-01 B):** couples the API to the bundle while keeping two v8 switches.
- **Keep the SoA flag with default `true` (Q-109-01 C):** a switch that breaks v8 when used.

## Security / Privacy Impact

- No secret handling change. The gated requests keep every rights, AI Vision and SE check; only the feature-flag condition changes.
- Attack surface shrinks on v7 instances (v3 listings answer 403) and is unchanged on v8 instances that already ran with the SoA flag on.
- ADR-0007's admin-setup trust boundary is unchanged; its condition reads `features.v8` instead of `features.nuxt_ui`.

## Operational Impact

- Upgrading installations with `NUXT_UI_ENABLED=true` but `V8_ENABLED` unset fall back to v7 until `V8_ENABLED=true` is set; installations with `V8_ENABLED=true` and `NUXT_UI_ENABLED` unset switch to v8. Release notes must say so.
- Leftover `NUXT_UI_ENABLED` / `STRUCT_OF_ARRAY_ENABLED` lines in `.env` are ignored.

## Links

- Related spec sections: `docs/specs/4-architecture/features/109-v8-struct-of-arrays-only/spec.md` (FR-109-03, FR-109-04, FR-109-05)
- Related ADRs: ADR-0007 (admin setup gate bypass, condition amended), ADR-0009 (v3 response shape precedent)
- Related issues / PRs: –
