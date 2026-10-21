# Open Questions – Feature 102

Open questions for [Feature 102](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-102-01~~ | 102 – Asset Endpoint Speedup | High | Does `GET /api/v3/Asset` keep its album access + photo membership check? | Resolved (Option A — lean check, same rules; FR-102-02) | 2026-10-20 | 2026-10-21 |
| ~~Q-102-02~~ | 102 – Asset Endpoint Speedup | Medium | Does `GET /api/v3/Asset` keep loading the whole `configs` table? | Resolved (Option A — only the keys `/Asset` reads; FR-102-03) | 2026-10-20 | 2026-10-21 |
| ~~Q-102-03~~ | 102 – Asset Endpoint Speedup | High | Which HTTP caching headers does `GET /api/v3/Asset` send? | Resolved (Option A — `private, max-age=3600` + `304`; FR-102-04) | 2026-10-20 | 2026-10-21 |
| ~~Q-102-04~~ | 102 – Asset Endpoint Speedup | High | Should tiles get direct thumbnail URLs so they skip `/Asset`, and through which tier? | Resolved (Option A — viewport-scoped URL tier; FR-102-05, FR-102-06, ADR-102-01) | 2026-10-20 | 2026-10-21 |
| ~~Q-102-05~~ | 102 – Asset Endpoint Speedup | Medium | How does `/Asset` drop global middleware (SE verification, full config load)? | Resolved (Option A — route-group middleware; FR-102-07, ADR-102-02) | 2026-10-21 | 2026-10-21 |
| ~~Q-102-06~~ | 102 – Asset Endpoint Speedup | Medium | For an admin, does `/Asset` still answer 404 for an unknown album and 403 for a photo outside the album? | Resolved (Option A — admins skip both; FR-102-02) | 2026-10-21 | 2026-10-21 |

## Question Details

### ~~Q-102-01~~ · Album access and photo membership check on `/Asset` ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-102-02.  
**Feature:** F-102  
**Priority:** High

`GetPhotoAssetRequest` loads the album through `AlbumFactory::findAbstractAlbumOrFail()`, runs `AlbumPolicy::CAN_ACCESS`, then checks that the photo belongs to the album (FR-056-08). Loading the album hydrates its default relations: cover and auto covers, owner and their groups, access permissions (twice), statistics. That is 11 of the 15 queries and about half of a warm request (≈4.5 ms of 9.2 ms on SQLite). It is worse on MySQL/PostgreSQL, where every query is a network round trip. Admins pass `AlbumPolicy::before()` without the check, but the album is still loaded.

What the check protects: photo ids are 24 random characters, so they cannot be guessed, but they can leak (an album that was public and is now private, a revoked share, browser history). The `X-Mac` temporary-link signature authenticates a time step only, not a photo (ADR-0008). Any guest holding a valid MAC can therefore fetch any photo whose id they know. Today the check is the only thing stopping that.

- **Option A (recommended): keep the check, made lean.** Same rules, read with narrow queries: album row columns plus access permissions for the caller in one or two queries, membership folded into the size-variant query, no model hydration of covers, owner, groups or statistics. Admins skip the album read entirely. Expected ≈4 queries per request instead of 15. No security change.
- **Option B: drop the check (capability URLs).** Serve whatever `photo_id` names, behind the MAC/session only. Fewest queries (the size variant only), and `album_id` leaves the route. Revoking access or making an album private no longer protects already-known photo ids. Any MAC holder can read any known photo.
- **Option C: keep the check as it is.** Take only the other gains (no SE verification, Q-102-02). Smallest change, but leaves the largest cost in place.

---

### ~~Q-102-02~~ · Loading the `configs` table on `/Asset` ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-102-03.  
**Feature:** F-102  
**Priority:** Medium

The global `ResolveConfigs` middleware loads all ~300 `configs` rows on every request (one query, ≈0.55 ms on SQLite). `/Asset` reads only a handful of them: the three `temporary_image_link_*` switches, the watermark switches, `temporary_image_link_life_in_seconds` for S3, and `lang` (`SetLocale`, used only by error bodies). Under Octane each request rebuilds the `ConfigManager`, so the load happens on every request. Once Q-102-01 Option A lands, a request should take ≈3–4 ms, so this load is ≈15 % of it, paid once per thumbnail.

- **Option A (recommended): load only the keys `/Asset` needs.** One `WHERE key IN (…)` query (≈0.2 ms). `ResolveConfigs` and `ResolveVerify` leave the global middleware for the route groups (needed anyway for FR-102-01, since global middleware cannot be dropped per route), and `/Asset` uses a variant that loads its key list. A read outside the list throws `ConfigurationKeyMissingException`, so a missed key fails loudly in tests.
- **Option B: keep the single full load.** No new code path; ≈0.35 ms more per thumbnail than Option A.
- **Option C: cache `configs` across requests.** Store the table in the cache store and invalidate it on every config write. Benefits every endpoint, but adds invalidation across Octane workers and is a separate, wider feature.

---

### ~~Q-102-03~~ · HTTP caching of `/Asset` responses ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-102-04.  
**Feature:** F-102  
**Priority:** High

The fastest request is the one never sent. Today `/Asset` answers `Cache-Control: public` with a `Last-Modified` date, no `max-age`, and never answers `304`. So browsers fall back to heuristic freshness, and every reload or new tab fetches every thumbnail again. `public` also lets a shared cache (reverse proxy, CDN) store a private thumbnail and serve it to anyone. The v8 front end already keeps fetched thumbnails in memory for one hour per tab (`thumb-asset-service.ts`, `TTL_MS`), but that cache does not survive a reload.

- **Option A (recommended): `Cache-Control: private, max-age=3600` plus `304` on `If-Modified-Since`.** Reloads and new tabs within an hour send no request at all; later ones revalidate (authorization still runs, no bytes sent). One hour matches the front end's in-memory TTL, so a rotated or regenerated thumbnail is stale for no longer than it already is today. `private` keeps shared caches out.
- **Option B: versioned URLs, cached for a year.** The listing adds a version token (for example a hash of `short_path`) to each thumbnail URL, and `/Asset` answers `private, max-age=31536000, immutable`. Never stale and never re-fetched, but it touches the v3 listing resources and the front end, which makes it a wider change.
- **Option C: `private, no-cache` plus `304`.** Every view still reaches the server and runs authorization; only the bytes are saved.

---

### ~~Q-102-04~~ · Thumbnail URLs in the photo ratios listing ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-102-05, FR-102-06 and ADR-102-01.  
**Feature:** F-102  
**Priority:** High

Owner suggestion (2026-10-20): send the thumbnail URLs with `GET /api/v3/Albums/{album_id}/Photos` (`PhotoRatioResource`) instead of resolving each tile through `/Asset`. Today the listing carries ids only, and every tile (`PhotoThumbVirtual`: `small2x`; `PhotoListItemVirtual`: `thumb`) costs one PHP request with authorization. The listing has already authorized the album for the whole page.

Constraint (owner, 2026-10-20): v8 does not rely on `SecurePathController` (the signed/encrypted `image/{path}` route). Protected images go through the MAC approach (`X-Mac` header, ADR-0008). A header cannot ride on a plain `<img src>`, so any image that needs the MAC has to stay on `/Asset`.

A direct URL can only be sent when the file needs no Lychee-side protection. That is a local disk with secure links off and no temporary link required for this viewer (`UrlGenerator::shouldNotUseSignedUrl()`), giving the plain `/uploads/...` URL. It is also S3, giving the bucket URL or S3's own presigned URL. Otherwise the tile stays on `/Asset` with the MAC, made lean per Q-102-01. The server resolves the fallback chain when a variant is missing (`small2x` → `small` → `thumb2x` → `thumb`), and the watermarked path when it applies.

Payload, measured 2026-10-20 on a 7,000-photo album (scratch instance): the ratios response is 1,197 KB raw / 229 KB gzip and takes ≈370 ms. File paths are 128 random bits each and do not compress. One URL array adds ≈150 KB gzip (+66 %), two arrays (grid + list) add ≈300 KB gzip (+130 %). Relative paths instead of absolute URLs save almost nothing after gzip. One shared stem per photo (variants of a photo share `aa/bb/<28 hex>`) covers both grid and list for ≈147 KB gzip.

- **Option A (recommended): a viewport-scoped URL tier.** Intent: check access once per screenful instead of once per image, then let the web server hand out the files. On an instance without link protection the `/uploads/...` files are already reachable by path (the photo view hands out the same kind of URL for `medium`/`original`), so PHP guarding each thumbnail protects nothing the web server would not serve anyway. A new `GET /api/v3/Albums/{album_id}/Photos/urls?photo_ids[]=…` (capped like the other `photo_ids[]` scopes) returns `ids`, grid URLs and list URLs for the tiles about to render. The client batches visible ids the way `loadPhotoDetails()` already does.
  - **Payload:** grows with what is viewed, not with album size (≈300 tiles ≈ 6–7 KB gzip). The ratios tier stays unchanged.
  - **Speed:** one PHP request per window of tiles instead of one per tile; the files themselves are served by the web server and cached by the browser.
  - **Correctness:** fallbacks, watermarks, S3 and per-viewer protection are resolved server-side, row by row.
  - **Costs:** a new endpoint, client batching, and one round trip before a window's images start loading.
- **Option B: one stem per photo in the ratios tier.** `asset_base_url` once plus `stems[i]` (`aa/bb/<28 hex>.ext`); the client builds `{base}small2x/{stem}` and `{base}thumb/{stem}`. URLs are known at first render with no extra request, but ≈147 KB gzip on a 7,000-photo album is paid upfront even when only the first screen is viewed. It ties the client to the naming strategy: legacy paths, watermark suffixes, a missing variant or a per-variant format all need `null` and a fall back to `/Asset`.
- **Option C: keep every tile on `/Asset`.** Lean authorization (Q-102-01 A) plus browser caching (Q-102-03 A). One path, ADR-0008's per-request authorization for every thumbnail, but each tile costs ≈3–4 ms of PHP on first view.

---

### ~~Q-102-05~~ · Dropping global middleware on `/Asset` ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-102-07 and ADR-102-02.  
**Feature:** F-102  
**Priority:** Medium

FR-102-01 (no SE verification) and FR-102-03 (only the needed config keys) touch `ResolveVerify` and `ResolveConfigs`. Both are global middleware (`Kernel::$middleware`), and Laravel runs global middleware before routing, so `withoutMiddleware()` on a route cannot drop them. Survey of the code base (2026-10-21):
- Nothing outside the route groups reads the `configs`/`verify` request attributes or the locale: the exception handler, error views, maintenance mode and the honeypot catch-all are clean.
- Group-less package routes: log-viewer's API routes (only with `LOG_VIEWER_ENABLED`, default off) and clockwork (dev). They read no configs, but would lose the shared `ConfigManager` (each `app(ConfigManager::class)` would then build and load its own instance).
- `AlbumFactory` and `MoneyService` singletons capture `ConfigManager` at construction, so `ResolveConfigs` must stay ahead of `UnlockWithPassword`, `ResolveAlbumSlug`, `LegacyLocalIdRedirect` and `LoginRequired`.

- **Option A (recommended): move them into the route groups.** `ResolveConfigs`, `ResolveVerify`, `SetLocale` become the first entries of `web`, `web-admin` and `api` (`ResolveConfigs` alone in `web-install`), and the log-viewer API middleware list gains `ResolveConfigs`. `/Asset` and `/Faces/{face_id}/photo/{variant}` then list `ResolveVerify` and `ResolveConfigs` in `withoutMiddleware()` and add a separate `ResolveAssetConfigs` (not a subclass: `withoutMiddleware()` strips subclasses too) that loads the FR-102-03 keys. Route-level control, reusable for other binary routes. Touches every request's middleware stack; the feature test suites cover it.
- **Option B: keep them global and skip by path.** A tiny helper recognises `api/v3/Asset/` and the global middleware skip or partial-load on it. Local change, no ordering risk, but the global middleware hard-code a route path: renaming the route silently loses the optimisation.

---

### ~~Q-102-06~~ · Admin requests on `/Asset` ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-102-02.  
**Feature:** F-102  
**Priority:** Medium

Q-102-01 Option A says admins skip the album read entirely. Today an admin passes `AlbumPolicy::before()`, but `/Asset` still loads the album (unknown `album_id` → 404) and still runs `isPhotoOfAlbum()` (photo outside the album → 403, same as for any user). Skipping the album read entirely changes both answers for admins, which Feature 056 never spelled out. No existing test covers either case for an admin.

- **Option A (recommended): skip both for admins.** An admin request costs the session user, groups and the size-variant lookup only (≤3 queries). `album_id` is ignored for admins: an unknown album or a photo outside it is served (200). It protects nothing today, since an admin may read every photo anyway.
- **Option B: existence check only.** One extra `exists` query keeps the 404 for an unknown album; a photo outside the album is served (200). ≤4 queries.
- **Option C: same answers as today.** Album existence and membership both checked for admins too (no admin shortcut). ≤6 queries, like any other user.

