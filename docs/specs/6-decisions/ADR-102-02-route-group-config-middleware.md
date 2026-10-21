# ADR-102-02: Config, verification and locale middleware run per route group

- **Status:** Accepted
- **Date:** 2026-10-21
- **Related features/specs:** Feature 102 (docs/specs/4-architecture/features/102-asset-endpoint-speedup/spec.md)
- **Related open questions:** Q-102-05

## Context

`ResolveConfigs` (loads the whole `configs` table, registers the scoped `ConfigManager` and `Watermarker`), `ResolveVerify` (builds the Lychee SE `Verify`, 3 queries) and `SetLocale` are listed in `App\Http\Kernel::$middleware`. Laravel runs global middleware before routing, so no route can opt out of them. Feature 102 needs `/Asset` and `/Faces/{face_id}/photo/{variant}` to skip SE verification (FR-102-01) and to load only the config keys they read (FR-102-03).

A code survey (2026-10-21) found nothing outside the route groups that reads the `configs`/`verify` request attributes or the locale: the exception handler, error views, maintenance mode and the honeypot catch-all route are clean. The group-less routes are log-viewer's API (only with `LOG_VIEWER_ENABLED`) and clockwork (dev only). The `AlbumFactory` and `MoneyService` singletons capture `ConfigManager` at construction, so `ResolveConfigs` must run before any middleware that resolves them (`UnlockWithPassword`, `ResolveAlbumSlug`, `LegacyLocalIdRedirect`, `LoginRequired`).

## Decision

1. `ResolveConfigs`, `ResolveVerify` and `SetLocale` leave `Kernel::$middleware` and become the first entries, in that order, of the `web`, `web-admin` and `api` groups. `web-install` gets `ResolveConfigs` only. log-viewer's API middleware list (`config/log-viewer.php`) gains `ResolveConfigs`.
2. `/Asset` and `/Faces/{face_id}/photo/{variant}` list `ResolveConfigs` and `ResolveVerify` in `withoutMiddleware()` and add `ResolveAssetConfigs`, which loads the FR-102-03 key list through `ConfigManager` and registers the same scoped `ConfigManager` and `Watermarker`. It is a separate class, because `withoutMiddleware()` also strips subclasses.
3. `image/{path}` keeps `ResolveConfigs` (its `SecurePathController` reads configs).
4. `ResolveConfigs`, `ResolveAssetConfigs`, `ResolveVerify` and `SetLocale` lead `Kernel::$middlewarePriority`: route middleware runs after group middleware, so without it `SetLocale` (group) would run before `ResolveAssetConfigs` (route) and load the whole table.
5. The Embed routes, which used `withoutMiddleware('api')`, list the `api` group entries they exclude instead: excluding the group would also strip the three, and Laravel never re-adds an excluded middleware.

## Consequences

### Positive
- Middleware cost is decided per route, where the routes are declared; other binary routes can reuse the same pattern.
- `/Asset` drops 3 SE verification queries and the full config load.

### Negative
- Every request's middleware stack changes; a future route registered outside the groups gets no configs, verification or locale unless it lists them.
- Group order matters: these three must stay first in their groups, and they lead the priority list.
- A route excluding a whole group (`withoutMiddleware('api')`) loses them; it has to list the excluded entries instead.

## Alternatives Considered

- **Keep them global and skip by path** (a helper recognising `api/v3/Asset/`): local change, no ordering risk, but global middleware would hard-code a route path, and renaming the route would silently lose the optimisation.

## Security / Privacy Impact

- No change to authentication or authorization. `ResolveVerify` only gates supporter features, and the routes that drop it serve no such feature.

## Operational Impact

- Fewer queries per image request. No configuration change.

## Links

- Related spec sections: `docs/specs/4-architecture/features/102-asset-endpoint-speedup/spec.md#functional-requirements` (FR-102-01, FR-102-03, FR-102-07)
- Related ADRs: ADR-102-01
