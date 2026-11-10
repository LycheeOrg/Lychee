# Feature Plan 102 – Asset Endpoint Speedup

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Draft  
_Last updated:_ 2026-10-29

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A gallery page issues one image request per tile, so per-request cost multiplies by thousands on large albums. Success (NFR-102-01):
- `/Asset` on a regular album, non-admin: ≤6 queries (config query excluded), down from 15; admin: ≤3 queries for any album id; no SE verification; one full config query, as on every route (FR-102-01 … 03, FR-102-07).
- Reloads within an hour send no `/Asset` request; later ones get `304` (FR-102-04).
- On an unprotected instance, album and timeline grid tiles send no `/Asset` request (FR-102-05, FR-102-06, ADR-102-01).
- Harness numbers recorded under Exit Criteria.

## Scope Alignment
- **In scope:** `GetPhotoAssetRequest` authorization reads; route-group middleware (ADR-102-02); cache headers in `ServesSizeVariantAssetTrait`; `GET /api/v3/Albums/{album_id}/Photos/urls`; v8 grid batching and the `Thumb` direct-`src` path.
- **Out of scope:** bootstrap cost; covers, Flow, map, search and face tiles (stay on `/Asset`); server-side caching of the URL tier; v2/v7, apart from the store reset on login and logout (FR-102-08).

## Dependencies & Interfaces
- `GetPhotoAssetRequest`, `ResolvesSizeVariantAssetTrait`, `ServesSizeVariantAssetTrait`, `AlbumFactory`, `AlbumPolicy` (Feature 056, ADR-0008); `FacePhotoAssetController` and its request (Feature 093, ADR-093-01).
- `App\Http\Kernel` groups, `ResolveConfigs`, `ResolveVerify`, `SetLocale`, `ConfigManager`, `Watermarker`, `config/log-viewer.php`, `routes/web_v2.php` (`image/{path}`).
- `PhotoChildrenController`, `GetPhotoDetailsRequest`, `ResolvesPhotoSource`, `UrlGenerator` (Feature 064/066 tiers).
- Front end: `PhotoGridVirtual.vue` (`virtualChunks`, `tilesForChunk()`), `PhotoThumbVirtual.vue`, `PhotoListItemVirtual.vue`, `Thumb.vue`, `thumb-asset-service.ts`, `AlbumState.ts`, `TimelineState.ts`, `photo-children-v3-service.ts`, `useRoute()` route names (`album`, `flow-album`, `timeline`), `UserState.ts`.
- Scratch instance (`run-lychee` skill) and the profiling harness in the scratchpad of session 432f6b23: `mkids.php` (ids + admin session cookie), `mkuser.php` (non-admin `viewer` the album is shared with, album made public for guests), `prof.php` and `run.sh` (cold), `loop.php` (warm, Octane-like, modes `admin`/`user`/`guest`; `TRACE=1` for a phase trace), `warm.sh` (all three modes), `ab.sh` (HEAD vs working copy), `boot.php`, `seedbig.php` + `payload.php` (7,000-photo payload).

## Assumptions & Risks
- **Assumptions:** production runs Octane with config/route caches (Docker image); on MySQL/PostgreSQL each saved query is a network round trip.
- **Risks / Mitigations:**
  - Lean album load diverges from `AlbumPolicy` semantics → keep real models (album, `base_class`, `access_permissions`) and only strip the unused eager loads; the 35 `PhotoAssetV3Test` tests stay green.
  - Folding membership into the size-variant query turns a 403 into a 404 → select it as an `exists` column, never as a `WHERE` (FR-102-02).
  - Middleware move breaks a route relying on global placement → survey in Q-102-05/ADR-102-02; keep the three first in each group; CI runs the full suites.
  - A controller's constructor dependencies are built before route middleware, so a `ConfigManager` they resolve would not be the one `ResolveConfigs` loads and invalidates → `ConfigManager`, and the `AlbumFactory`/`MoneyService` holding it, are scoped bindings (FR-102-07).
  - Direct URL points at a missing or expired file (S3 presign) → `Thumb` falls back to `/Asset` on the image `error` event (FR-102-06).

## Implementation Drift Gate
After the last task: walk FR-102-01 … 08 and S-102-01 … 15 against tests and code, rerun the harness and record numbers below, confirm ADR-102-01/02 match the code, and append the drift report here.

## Increment Map
1. **I1 – Lean `/Asset` authorization (FR-102-02)** — T-102-01, T-102-02.
   - _Steps:_ Query-count tests (owner, shared user, public guest; admin on a regular album, an unknown album id and a photo outside the album) and a 403-vs-404 guard test; confirm they fail. In `GetPhotoAssetRequest`, load regular/tag/person albums with only `base_class` and `access_permissions`; select membership as an `exists` column of the size-variant query for regular albums; admins skip the album read and the membership check (FR-102-02).
   - _Commands:_ `php artisan test --filter=PhotoAssetV3Test`, `php artisan test --filter=AssetMiddlewareV3Test`, `make phpstan`.
2. **I2 – Route-group middleware (FR-102-01, FR-102-03, FR-102-07)** — T-102-03 … T-102-06.
   - _Steps:_ Tests first (no SE verification on both binary routes, which still resolve configs and the locale; a v2 and a v3 JSON endpoint still see configs, verify and locale). Move the three middleware into the groups; log-viewer API; scoped `ConfigManager` binding; route changes.
   - _Commands:_ `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest|ResolveConfigsTest|ResolveVerifyTest|SetLocaleTest'`, `make phpstan`.
   - _Notes:_ `withoutMiddleware('api')` on the Embed routes would also strip the three moved middleware (excluded middleware cannot be re-added), so those routes move to `routes/api_v2_embed.php` under an `embed` group (the three plus `HandleCors`).
3. **I3 – HTTP caching (FR-102-04)** — T-102-07, T-102-08.
   - _Steps:_ Tests first (headers on 200, 304 / 200 by `If-Modified-Since`, errors unchanged); implement in `ServesSizeVariantAssetTrait::responseFile()` with the request at hand.
   - _Commands:_ `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest'`, `make phpstan`.
4. **I4 – URL tier backend (FR-102-05)** — T-102-09 … T-102-12.
   - _Steps:_ `PhotoUrlsV3Test` first; `GetPhotoUrlsRequest`; `QueryPhotoUrls` (scoped via `ResolvesPhotoSource`, `LEFT JOIN`s on `small2x`/`small`/`thumb`, one tiny pure helper deciding direct-or-null per row); `PhotoUrlResource` (`#[TypeScript]`); route and `PhotoChildrenController::urls()`; `php artisan typescript:transform`.
   - _Commands:_ `php artisan test --filter=PhotoUrlsV3Test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.
5. **I5 – Front-end URL loader (FR-102-06)** — T-102-13, T-102-14.
   - _Steps:_ Vitest first for the loader (dedup resolved and in-flight ids, chunks ≤300, non-empty all-null disables, error → `null`). `PhotoChildrenV3Service.getUrls()`. The loader is the `TileUrlsState` Pinia store (see I10).
   - _Commands:_ `vp test resources/js/v8/composables/photo/tileUrlLoader.test.ts`, `vp check --fix`, `vp run typecheck`.
   - _Notes:_ `vp check --fix` (project-local `npx vp`) also rewrites five files this feature does not touch (`contextMenu.ts` ×2, `EmbedCodeDialog.vue` ×2, `useSpotlightRemoteSearch.ts`); those changes are reverted, not committed.
6. **I6 – Grid wiring and `Thumb` (FR-102-06)** — T-102-15 … T-102-18.
   - _Steps:_ Vitest first for the visible-id composable (ids of `virtualChunks` → `tilesForChunk()`, stable key) and for `Thumb` (string → direct `src`, `undefined` → placeholder, `null` → `ThumbAssetService`, `error` → `ThumbAssetService`). Then the composable in `PhotoGridVirtual.vue`, tiles passing their URL, `Thumb` props.
   - _Commands:_ `vp test` (scoped), `vp check --fix`, `vp run typecheck`.
   - _Notes:_ `Thumb` takes `directUrl` (string → direct `src`, `null`/absent → `/Asset`) and `pending` (placeholder only), so covers, search and other callers keep today's behaviour without passing anything. Tiles read `useTileUrlsStore().urlsOf()` themselves rather than receiving URLs through `PhotoGridVirtual`. `useTileUrlRequests` keys on the rendered ids and on the store's `generation`, which a source or viewer change bumps, so an unchanged id set is asked again.
7. **I7 – Verification and docs (NFR-102-01)** — T-102-19.
   - _Steps:_ Browser check on the scratch instance (album, timeline, smart album; unprotected and temporary-link instance; network panel). Harness rerun. Knowledge map, roadmap, Feature 056 spec cross-references to FR-102-02/04.
8. **I8 – `ETag` on `/Asset` (FR-102-04)** — T-102-20, T-102-21.
   - _Steps:_ Tests first (`ETag` on 200, matching `If-None-Match` → 304, watermark switched on under the same URL → 200 with the watermarked file). `ETag` of path, mtime and size in `ServesSizeVariantAssetTrait::responseFile()`.
   - _Commands:_ `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest'`, `make phpstan`.
   - _Notes:_ Prompted by the PR #4912 review (`304` for another selected file).
9. **I9 – Grid tile variant by density (FR-102-05, FR-102-06)** — T-102-22 … T-102-24.
   - _Steps:_ Tests first (`density=1|2` chains, default 2, 422 otherwise; loader query and tile fallback type). `GetPhotoUrlsRequest` validates `density`; `QueryPhotoUrls` picks the chain; `PhotoUrlResource.grid_urls`; regenerate types; loader and `PhotoThumbVirtual`.
   - _Commands:_ `php artisan test --filter=PhotoUrlsV3Test`, `make phpstan`, `vp test`, `vp check --fix`, `vp run typecheck`.
   - _Notes:_ Owner remark 2026-10-21 (Q-102-09): 1x screens downloaded `small2x` on both tiers.
10. **I10 – Tile URL store driven by the route (FR-102-06)** — T-102-25 … T-102-27.
   - _Steps:_ Vitest first for `tileUrlSource()` (route → album id, `timeline` or none) and for the `TileUrlsState` store (batching, per-viewer map kept across sources, missed ids per source, all-null disables per source, viewer change empties it). `useTileUrlRequests()` in `PhotoGridVirtual` sets the source from `useRoute()` and the viewer from `UserState`; `PhotoThumbVirtual` and `PhotoListItemVirtual` read the store; `AlbumPanel.vue` and `Timeline.vue` no longer build a loader.
   - _Notes:_ No `provide`/`inject` for tile URLs (Q-102-10). URLs are keyed by photo id because a photo's file URL is the same in every album for one viewer; a URL gone stale (rotation, regenerated variant, expired S3 link) fails to load and `Thumb` falls back to `/Asset`. `MOTION_URL_LOADER` (Feature 100) keeps its injection.
11. **I11 – Front-end store reset on login and logout (FR-102-08)** — T-102-29 … T-102-32.
   - _Steps:_ Vitest first for `ThumbAssetService.clear()` (fetches again, revokes the object URLs nothing renders, revokes a rendered one at its last release) and `useClearViewerState()` (stores, timeline init data, dates and struct-of-arrays state, user, album list, axios cache, `ThumbAssetService`). Then the shared composable `resources/js/composables/useClearViewerState.ts`, called by `useLogout()` (v8), the v7 left-menu logout and the v7/v8 `LoginForm` and `WebauthnModal`. Remove `ClearBrowserCache`, `ClearBrowserCacheOnAuthChange` and their `AuthTest` cases (Q-102-11).
   - _Commands:_ `vp test resources/js/services resources/js/composables`, `vp check` (changed files), `vp run typecheck`, `php artisan test --filter='Tests\\Feature_v2\\AuthTest'`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Notes:_ Login opens only while no photo is shown, and every `logged-in` handler reloads its view's data (`refresh()`, `onLoggedIn()`, `handleLoggedIn()`, `goBack()`), so the stores are cleared before it. Emptying the album and albums stores also unmounts the rendered tiles, so the reloaded view asks `ThumbAssetService` again. `TimelineState.reset()` clears the init data (`load()` returns early once `layout` is set, so the rights and config would outlive the viewer), the v2 `dates` (`loadDates()` returns early once filled) and calls `resetV3()` (`ensureBucketsV3()` returns early once `bucketsV3` is set, so would the tiles); `initialLoad()` rewrites the v2 pages. The Timeline and Tag views mount their login modals while the user is not logged in (`!userStore.isLoggedIn`, as Album and Albums do): `clearViewerState()` empties the user, so a guard on `isGuest` would unmount the modal before it emits `logged-in` and the view would never refresh.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-102-01 … 03 | I1 / T-102-01, T-102-02 | `PhotoAssetV3Test` |
| S-102-04, S-102-05 | I2 / T-102-03 … T-102-06 | `PhotoAssetV3Test`, `AssetMiddlewareV3Test`, faces photo test |
| S-102-06, S-102-07 | I3 / T-102-07, T-102-08 | `PhotoAssetV3Test` |
| S-102-08 … 12 | I4 / T-102-09 … T-102-12 | `PhotoUrlsV3Test` |
| S-102-13 | I5, I10 / T-102-13, T-102-14, T-102-25 … T-102-27 | tile URL store Vitest |
| S-102-14 | I6 / T-102-15 … T-102-18, I7 / T-102-19 | composable + `Thumb` Vitest, browser check |
| S-102-06, S-102-07 | I8 / T-102-20, T-102-21 | `PhotoAssetV3Test` |
| S-102-15 | I11 / T-102-29 … T-102-32 | `thumb-asset-service.test.ts`, `useClearViewerState.test.ts` |

## Analysis Gate
2026-10-21, agent self-review requested by the owner ("check for consistencies, ambiguities").

| Item | Result | Notes |
|------|--------|-------|
| 1. Spec completeness | Pass | FR-102-01 … 07, NFR-102-01, scenarios, UI note (no visual change). |
| 2. Open questions | Pass | Q-102-01 … 06 resolved and folded into the spec; ADR-102-01, ADR-102-02 written. |
| 3. Plan alignment | Pass | Success criteria match FR/NFR wording. |
| 4. Tasks coverage | Pass | Every FR maps to tasks; each implementation task follows its test task, front end included. |
| 5. Working agreements | Pass | No new dependency; direct-or-null decision isolated in a pure helper; ADR-0008/0009/093-01 reviewed. |
| 6. Tooling | Pass | Commands listed per increment and task. |

Fixed during the review:
- Spec claimed `/Asset` headers stay as Feature 056 defines them, contradicting FR-102-04.
- Spec admin rule ("existence check only") contradicted Q-102-01 Option A ("skip the album read entirely") → Q-102-06 (Option A: admins skip both checks).
- Folding membership into the size-variant `WHERE` would have turned 403 into 404 → `exists` column.
- Query budget "≤6" held only for regular albums; config query was ambiguous → scoped and excluded explicitly.
- Faces photo route: covered by Q-102-05 Option A but missing from FR-102-01/03.
- FR-102-06 pending state, reset on reload, and the vacuous "all null" (empty response) case were unspecified.
- FR-102-05 omitted the struct-of-arrays gate and the 0-ids case.
- Front-end tasks had no tests before code → Vitest tasks added (jsdom + `@vue/test-utils` already present).
- Broken FR table (blank line), NFR placement, stale "Last updated".

## Measurements
Warm loop (`ab.sh 400`, Octane-like, SQLite, `thumb` of a photo in a regular album), median per request and queries per request outside the config load and SE verification:

| Caller | HEAD | After I1 |
|--------|------|----------|
| Admin | 10.4 ms, 15 queries | 4.7 ms, 3 queries |
| Shared user | 12.4 ms, 20 queries | 6.4 ms, 6 queries |
| Public guest | 10.9 ms, 18 queries | 5.1 ms, 4 queries |

After I2, with the scoped `ConfigManager`, `Watermarker` and `Verify` bindings dropped after each request as Octane does, so the config load and SE verification are counted (`ab.sh 400` in the scratchpad of session 7590879e, HEAD = I1):

| Caller | After I1 | After I2 |
|--------|----------|----------|
| Admin | 6.2 ms, 7 queries | 5.2 ms, 4 queries |
| Shared user | 7.9 ms, 10 queries | 6.9 ms, 7 queries |
| Public guest | 6.4 ms, 8 queries | 5.5 ms, 5 queries |

Final code (I1 … I6), same loop: admin 5.3 ms / 4 queries, shared user 7.1 ms / 7, public guest 5.6 ms / 5 (config load and SE verification included), against 10.4 / 12.4 / 10.9 ms and 15 / 20 / 18 queries (excluding them) at the baseline.

Browser check (I7, 2026-10-21, scratch instance; uploads served same-origin-allowed from a second port with `LYCHEE_UPLOADS_URL` and `SECURITY_HEADER_CSP_IMG_SRC`, since `php artisan serve` maps `/uploads` to the developer's `public/uploads`):

| View | Unprotected | Temporary links required (`enabled`, `when_logged_in`, `when_admin`) |
|------|-------------|------------------------------------------------------------------------|
| Album "Family" (4 photos) | 1 `/Photos/urls`, 4 direct, 0 `/Asset` | 1 `/Photos/urls` (all `null`), 0 direct, 4 `/Asset` |
| Smart album `recent` (30 rendered) | 1 `/Photos/urls`, 30 direct; the 26 payload photos without files fall back to `/Asset` (404, placeholder) | 0 direct, all `/Asset` |
| Album → `recent` in-app navigation | loader reset, 1 new `/Photos/urls`, 30 direct | — |

Found: with `when_logged_in=0` and `when_admin=1`, an admin still gets direct URLs (`shouldNotUseSignedUrl()`), while `/Asset` requires the admin's signature → Q-102-07.

Browser check (I11, 2026-10-29, scratch instance, root gallery in v8 and v7): login and logout answer `204` without `Clear-Site-Data`; after login the root listings reload as the admin; logout lands on `/gallery`; no console error apart from the security advisory check, which needs the network.

## Exit Criteria
- Full quality gate green (php-cs-fixer, `vp check --fix`, `vp run typecheck`, scoped tests, `make phpstan`); `php artisan typescript:transform` rerun.
- Harness numbers recorded here; roadmap and knowledge map updated; ADR-102-01/02 match the code; drift gate report appended.

## Drift Gate Report
2026-10-21.

| Item | Result | Notes |
|------|--------|-------|
| FR-102-01, 03, 07 | Pass | Kernel groups and priority, scoped `ConfigManager`, `embed` group, log-viewer; `AssetMiddlewareV3Test`. |
| FR-102-02 | Pass | I1, query budgets in `PhotoAssetV3Test`. |
| FR-102-04 | Pass | `ServesSizeVariantAssetTrait::responseFile()`; S-102-06/07 tests. |
| FR-102-05 | Pass, Q-102-07 open | `PhotoUrlsV3Test` (20 tests). The admin rule follows `shouldNotUseSignedUrl()` as specified; Q-102-07 asks whether to align it with `/Asset`. |
| FR-102-06 | Pass | Loader, composable and `Thumb` Vitest (21 tests); browser check above. |
| NFR-102-01 | Pass | Measurements above. |
| ADR-102-01/02 | Pass | Match the code (ADR-102-02 items 4–5 added in I2). |

## Follow-ups / Backlog
- Search grid tiles (different album per tile) could get a `/Search/Photos/urls` tier.
- `PhotoThumbVirtual` always fetches `small2x`, even on 1x screens.
- Session user load (user + groups, 2 queries) on every `/Asset` request.
