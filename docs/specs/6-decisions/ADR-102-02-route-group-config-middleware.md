# ADR-102-02: Config, verification and locale middleware run per route group

- **Status:** Accepted
- **Date:** 2026-10-21
- **Related features/specs:** Feature 102 (docs/specs/4-architecture/features/102-asset-endpoint-speedup/spec.md)
- **Related open questions:** Q-102-05

## Context

`ResolveConfigs` (loads the whole `configs` table, registers the scoped `ConfigManager` and `Watermarker`), `ResolveVerify` (builds the Lychee SE `Verify`, 3 queries) and `SetLocale` are listed in `App\Http\Kernel::$middleware`. Laravel runs global middleware before routing, so no route can opt out of them. Feature 102 needs `/Asset` and `/Faces/{face_id}/photo/{variant}` to skip SE verification (FR-102-01). They keep the full config load (FR-102-03): a per-route key list would have to follow every key any membership, watermark or smart-album path reads, and a missed key would only show as a 500 in production.

A code survey (2026-10-21) found nothing outside the route groups that reads the `configs`/`verify` request attributes or the locale: the exception handler, error views, maintenance mode and the honeypot catch-all route are clean. The group-less routes are log-viewer's API (only with `LOG_VIEWER_ENABLED`) and clockwork (dev only). Laravel builds a route's controller, with its constructor dependencies, before the route's group middleware runs, and `AlbumFactory`, `MoneyService` and services such as `AlbumBucketComputer` take `ConfigManager` in their constructors. Once `ResolveConfigs` is no longer global, these would get a `ConfigManager` other than the one it loads and a settings change invalidates.

## Decision

1. `ResolveConfigs`, `ResolveVerify` and `SetLocale` leave `Kernel::$middleware` and become the first entries, in that order, of the `web`, `web-admin` and `api` groups. `web-install` gets `ResolveConfigs` only. log-viewer's API middleware list (`config/log-viewer.php`) gains `ResolveConfigs`.
2. `/Asset` and `/Faces/{face_id}/photo/{variant}` list `ResolveVerify` in `withoutMiddleware()`. They resolve configs and the locale like every other route.
3. `image/{path}` keeps `ResolveConfigs` (its `SecurePathController` reads configs).
4. `ResolveConfigs`, `ResolveVerify` and `SetLocale` lead `Kernel::$middlewarePriority`.
6. `ConfigManager` is a scoped binding (`AppServiceProvider::register()`), and so are `AlbumFactory` and `MoneyService`, which hold it. Whatever resolves it before or after `ResolveConfigs` gets the same instance within a request, and a queue worker gets a fresh one per job.
5. The Embed routes, which used `withoutMiddleware('api')`, list the `api` group entries they exclude instead: excluding the group would also strip the three, and Laravel never re-adds an excluded middleware.

## Consequences

### Positive
- Middleware cost is decided per route, where the routes are declared; other binary routes can reuse the same pattern.
- `/Asset` drops 3 SE verification queries.
- One `ConfigManager` per request or job, wherever it is resolved: a settings change invalidates the instance every service reads.

### Negative
- Every request's middleware stack changes; a future route registered outside the groups gets no configs, verification or locale unless it lists them.
- Group order matters: these three must stay first in their groups, and they lead the priority list.
- Tests that run two commands or jobs in one process share the scoped `ConfigManager`; they must change settings through `Configs::set()`, which invalidates it, not through raw `configs` table updates.
- A route excluding a whole group (`withoutMiddleware('api')`) loses them; it has to list the excluded entries instead.

## Alternatives Considered

- **Load only the keys the binary routes read** (`ConfigManager::loadOnly()` behind a separate route middleware): saves about 0.35 ms per image, but the key list must track every path those routes reach, and a missed key throws in production.
- **Keep them global and skip by path** (a helper recognising `api/v3/Asset/`): local change, no ordering risk, but global middleware would hard-code a route path, and renaming the route would silently lose the optimisation.

## Security / Privacy Impact

- No change to authentication or authorization. `ResolveVerify` only gates supporter features, and the routes that drop it serve no such feature.

## Operational Impact

- Fewer queries per image request. No configuration change.

## Links

- Related spec sections: `docs/specs/4-architecture/features/102-asset-endpoint-speedup/spec.md#functional-requirements` (FR-102-01, FR-102-03, FR-102-07)
- Related ADRs: ADR-102-01
