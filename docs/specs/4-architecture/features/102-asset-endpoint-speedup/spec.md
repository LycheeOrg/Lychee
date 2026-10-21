# Feature 102 – Asset Endpoint Speedup

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-10-21 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #102 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
`GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` (Feature 056) is called once per rendered thumbnail, so a gallery page fires dozens of requests at once and albums of 5,000–7,000 photos multiply every millisecond. This feature makes each `/Asset` request cheaper (fewer queries, no hydration or middleware the response does not need, browser caching), and lets v8 album and timeline tiles skip `/Asset` entirely on instances without link protection, through a viewport-scoped URL tier (ADR-102-01). Statuses, error bodies, file bytes and the size fallback chain of `/Asset` stay as Feature 056 defines them; the response headers gain caching (FR-102-04).

## Baseline
Measured 2026-10-20 on a scratch instance (SQLite, opcache on, dev dependencies installed, config and routes not cached). Logged-in admin, `thumb` of a photo in a regular album.

| Mode | Time per request | Queries |
|------|------------------|---------|
| Cold, one bootstrap per request (PHP-FPM) | ≈120 ms (≈60 ms of it bootstrap) | 18 |
| Warm, one kernel serving many requests (Octane, the Docker image) | ≈9.2 ms median | 15 (+4 for configs and SE verification, which Octane reruns per request) |

Where the warm request spends its time:

| Phase | ≈ms | Queries |
|-------|-----|---------|
| Global middleware + routing | 0.6 | 0 (configs + SE verification under Octane: 4) |
| Session read, user + groups | 1.6 | 2 |
| `findAbstractAlbumOrFail()` (album + default relations) | 4.5 | 11 |
| Size variant, policy, membership | 0.7 | 2 |
| File response | 1.0 | 0 |

The 7,000-photo ratios response (`GET /api/v3/Albums/{album_id}/Photos`) is 1,197 KB raw / 229 KB gzip; one array of file URLs would add ≈150 KB gzip to it.

## Goals
- Drop every query and middleware step `/Asset` does not need, without changing who may read what.
- Let the browser reuse `/Asset` responses across reloads.
- On instances without link protection, serve v8 album and timeline tiles without a PHP request per tile.

## Non-Goals
- Framework bootstrap cost (dev-only providers, config/route caching). Production images already cache config and routes and run Octane.
- Direct URLs for album covers, Flow cards, the map, search results and face photos: they stay on `/Asset`. Covers need `/Asset`'s cover exceptions; search tiles carry a different album id per tile.
- Server-side caching of the URL tier (S3 presigned URLs expire).
- v2 endpoints and v7.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-102-01 | `/Asset` and `/Faces/{face_id}/photo/{variant}` do not resolve the Lychee SE verification (`ResolveVerify`). Nothing on these routes reads it. | No `license_key`/`email` config read and no `Verify` construction on these routes. | — | — | — | Owner directive 2026-10-20, Q-102-05 |
| FR-102-02 | `/Asset` authorizes with the same rules as Feature 056 (signature first, FR-056-04/05; then `AlbumPolicy::CAN_ACCESS` on the album; then photo membership with the cover exceptions, FR-056-08/09), read with narrow queries: the album is loaded with its base record and access permissions only, never its covers, owner, owner groups or statistics; membership is read in the same query as the size variant, as a selected `exists` column, so "no size variant" (404) and "not in the album" (403) stay distinct. Admins (`may_administrate`) pass the signature check as any user (`temporary_image_link_when_admin`), then skip the album read and the membership check: `album_id` is validated but never resolved, so for an admin an unknown album or a photo outside the album is served (this narrows FR-056-08 to non-admin callers). | Regular album, non-admin caller: at most 6 queries (session user, user groups, album, base album, access permissions, size variant with membership). Admin, any album id: at most 3 queries (session user, user groups, size variant). Neither counts the config query of FR-102-03. Tag, person and smart albums keep their Feature 056 membership branches with the same eager-load reduction. | Same 422 rules as Feature 056 (`album_id`, `photo_id`, `size_variant`), admins included. | Unknown album (non-admin) or size variant → 404; denied → 403; missing/invalid required signature → 401. | — | Q-102-01, Q-102-06 |
| FR-102-03 | `/Asset` and `/Faces/{face_id}/photo/{variant}` load the whole `configs` table through `ResolveConfigs`, like every other route. No per-route key list: a partial load would have to track every key any membership, watermark or smart-album path reads, and a missed key only shows as a 500 in production. | One full config query, shared with every other request. | — | — | — | Q-102-02 |
| FR-102-04 | A successful file response of `/Asset` (and of `/Faces/{face_id}/photo/{variant}`, which shares the serving code) carries `Cache-Control: private, max-age=3600`, `Last-Modified` (the served file's mtime) and an `ETag` derived from the served file's path, mtime and size, so a URL that selects another file (watermarked or not, per viewer and settings) never validates against the other one. A request whose validators still match (`If-None-Match` equal to the `ETag` when sent, `If-Modified-Since` not older than the mtime) gets `304 Not Modified` with no body, after authorization. S3 redirects are unchanged. | Reloads within the hour send no request; later ones revalidate and get `304` when the file has not changed. | — | 401/403/404 keep their bodies and are not cacheable. | — | Q-102-03 |
| FR-102-05 | New `GET /api/v3/Albums/{album_id}/Photos/urls?photo_ids[]=…&density=1|2` returns `ids` plus, index-aligned to it, `grid_urls` (grid tiles) and `thumb_urls` (list tiles). `album_id` accepts regular, tag, person and smart albums, `timeline` included. Each entry is the direct URL of the watermark-aware file of the first existing size-variant row along the chain: for `grid_urls`, `small` → `small2x` → `thumb` at `density=1` and `small2x` → `small` → `thumb` at `density=2` (`thumb` is a square crop, so it comes last); `thumb` alone for `thumb_urls`. It is `null` when no row exists, or when the file needs Lychee-side protection for this viewer. Direct means: on a local disk, `secure_image_link_enabled` is off and `UrlGenerator::shouldNotUseSignedUrl()` is true for the viewer, giving the disk's plain URL; on S3, the bucket URL or S3's own presigned URL. Never a `SecurePathController` URL (ADR-102-01). | 200 with the three arrays. Ids not in the album, or not visible to the caller, are left out (same scoping as `/Photos/details`). | `photo_ids` required, 1–300 entries, each a `RandomIDRule` id; `density` optional, `1` or `2`, default `2`; `album_id` per `AlbumIDRule(false)`. Otherwise 422. | Unknown album → 404. Struct-of-arrays disabled, or no album access → 401 (guest) / 403 (signed in), as for `/Photos/details`. | — | Q-102-04, Q-102-09, ADR-102-01 |
| FR-102-06 | v8 grid and list tiles of album views (regular, tag, person, smart) and of the timeline load their image through FR-102-05. The grid batches the ids of its rendered tiles (visible plus overscan) into FR-102-05 requests of ≤300 ids, each id at most once per load of the album's photos, with `density=2` when `window.devicePixelRatio > 1` and `density=1` otherwise, read once per page. Grid tiles falling back to `/Asset` ask for `small2x` or `small` by the same rule (`Thumb`'s default), never a fixed variant. While a tile's entry is pending it shows the placeholder; with a URL it renders it directly; with `null`, or when the direct URL fails to load, it falls back to `/Asset` with the MAC (today's `Thumb` behaviour). After a non-empty response whose entries are all `null`, the view stops calling FR-102-05 until its photos are reloaded, and every tile uses `/Asset`. | Unprotected instance: no `/Asset` request per tile. | — | FR-102-05 error → the affected tiles use `/Asset`. | — | Q-102-04, Q-102-09, ADR-102-01 |
| FR-102-07 | `ResolveConfigs`, `ResolveVerify` and `SetLocale` run as route-group middleware instead of global middleware: first entries of `web`, `web-admin` and `api`, `ResolveConfigs` alone in `web-install`, and `ResolveConfigs` added to log-viewer's API middleware. `/Asset` and `/Faces/{face_id}/photo/{variant}` exclude `ResolveVerify` only. The three lead `Kernel::$middlewarePriority`. `ConfigManager` is a scoped binding, and so are `AlbumFactory` and `MoneyService`, which hold it: a controller's constructor dependencies are built before route middleware, so they share the instance `ResolveConfigs` then loads. `image/{path}` keeps `ResolveConfigs`. The Embed routes exclude the `api` group entry by entry instead of the group, keeping the three. | Every other route keeps reading configs, verify and locale exactly as before. | — | — | — | Q-102-05, ADR-102-02 |
| FR-102-08 | A response of the `web`, `web-admin` and `api` groups during which a user logged in or out (`Login`/`Logout` auth events: password, LDAP, OAuth, WebAuthn) carries `Clear-Site-Data: "cache"`, so the next viewer of the browser never reuses assets privately cached for the previous one (FR-102-04). | The browser drops its HTTP cache for the origin. | — | A failed login and every other response carry no `Clear-Site-Data`. | — | Q-102-08 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-102-01 | Each backend increment lowers or keeps the warm `/Asset` query count and median time against the baseline; the counts of FR-102-02 are met; an unprotected instance shows zero `/Asset` requests for album/timeline grid tiles. | One request per thumbnail, thousands of photos per album. | The baseline harness (warm loop, cold run, 7,000-photo payload) rerun after each backend increment, numbers recorded in the plan; browser network panel for the tile count. | Scratch instance (`run-lychee` skill) | Owner directive 2026-10-20 |

## UI / Interaction Mock-ups
No visual change: tiles, placeholders and layouts look as today. Only where each tile's image bytes come from changes.

```
Unprotected instance                       Protected viewer (MAC required / secure links)
┌ grid renders chunk (visible+overscan) ┐   ┌ grid renders chunk ┐
│ GET /Photos/urls?photo_ids[]=… (≤300) │   │ GET /Photos/urls   │ → all null → stop asking
│ <img src="/uploads/small(2x)/…"> × N │   │ /Asset + X-Mac × N │ (Feature 056)
└───────────────────────────────────────┘   └────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-102-01 | Owner, shared user or public guest fetches a thumb of a regular album: 200, ≤6 queries (config query excluded). |
| S-102-02 | Admin fetches a thumb: 200 with ≤3 queries (config query excluded), also for an unknown album id and for a photo outside the album; a required signature (`temporary_image_link_when_admin`) is still enforced (401); a missing size variant is still 404. |
| S-102-03 | Photo not in the album (403), no size variant (404), cover exceptions, smart/tag/person albums: same outcomes as Feature 056. |
| S-102-04 | `/Asset` and the faces photo route run no SE verification; other routes still resolve configs, verify and locale. |
| S-102-05 | `/Asset` and the faces photo route resolve configs and the locale like every other route; temporary links, watermark, S3, smart, tag and person albums still behave. |
| S-102-06 | 200 carries `Cache-Control: private, max-age=3600`, `Last-Modified` and `ETag`. |
| S-102-07 | `If-Modified-Since` ≥ mtime (and matching `If-None-Match`) → 304, empty body; older → 200; the URL now selecting another file (watermark) → 200 with that file; 401/403/404 unchanged. |
| S-102-08 | `/Photos/urls` as owner on an unprotected instance: direct URLs; `grid_urls` falls back `small2x` → `small` → `thumb` (density 2, the default) or `small` → `small2x` → `thumb` (density 1); `null` when no row; any other `density` → 422. |
| S-102-09 | Temporary link required for the viewer, or secure links on: all entries `null`. |
| S-102-10 | Watermark applies to the viewer: URLs point at the watermarked files. S3 disk: S3 URLs. |
| S-102-11 | Ids outside the album are left out; 0 or >300 ids → 422; guest on private album → 401; other user → 403; unknown album → 404. |
| S-102-12 | Smart album and `timeline` ids work. |
| S-102-13 | Grid batches rendered tile ids (≤300 per call, each id once); a non-empty all-`null` response stops further calls; a reload of the album's photos starts over. |
| S-102-14 | Tile with a URL renders it with no `/Asset` request; pending shows the placeholder; `null` or a load error falls back to `/Asset` with `small` on a 1x screen and `small2x` above; the loader sends the screen's `density`. |
| S-102-15 | Successful login and logout responses carry `Clear-Site-Data: "cache"`; a failed login and other requests do not. |

## Test Strategy
- **REST API (v3):** `PhotoAssetV3Test` and `AssetMiddlewareV3Test` gain query-count, config and cache-header tests (S-102-01 … 07); new `PhotoUrlsV3Test` (S-102-08 … 12). **REST API (v2):** `AuthTest` covers `Clear-Site-Data` (S-102-15). Middleware unit tests (`ResolveConfigsTest`, `ResolveVerifyTest`, `SetLocaleTest`) stay green; CI runs the full Feature_v2/Feature_v3 suites for FR-102-07.
- **Frontend (v8):** Vitest (`vp test`, jsdom, `@vue/test-utils`) for the tile URL loader (S-102-13), the visible-id batching and `Thumb` (S-102-14); browser check on the scratch instance.
- **Performance:** the baseline harness (NFR-102-01).

## Interface & Contract Catalogue

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-102-01 | REST GET `/api/v3/Albums/{album_id}/Photos/urls?photo_ids[]=…` | Direct tile URLs for up to 300 photos (FR-102-05). | `GetPhotoUrlsRequest`, `PhotoUrlResource` |
| API-056-01 | REST GET `/api/v3/Asset/{album_id}/{photo_id}/{size_variant}` | Leaner authorization, no SE verification, cache headers (FR-102-01 … 04). | `GetPhotoAssetRequest` |

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-102-01 | `PhotoUrlResource`: `ids: string[]`, `grid_urls: (string\|null)[]`, `thumb_urls: (string\|null)[]`, index-aligned (ADR-0009). | REST, TypeScript types |
