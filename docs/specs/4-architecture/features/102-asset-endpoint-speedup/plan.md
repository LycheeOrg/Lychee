# Feature Plan 102 – Asset Endpoint Speedup

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Draft  
_Last updated:_ 2026-10-21

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A gallery page issues one image request per tile, so per-request cost multiplies by thousands on large albums. Success (NFR-102-01):
- `/Asset` on a regular album, non-admin: ≤6 queries (config query excluded), down from 15; admin: ≤3 queries for any album id; no SE verification; one partial config query (FR-102-01 … 03, FR-102-07).
- Reloads within an hour send no `/Asset` request; later ones get `304` (FR-102-04).
- On an unprotected instance, album and timeline grid tiles send no `/Asset` request (FR-102-05, FR-102-06, ADR-102-01).
- Harness numbers recorded under Exit Criteria.

## Scope Alignment
- **In scope:** `GetPhotoAssetRequest` authorization reads; route-group middleware (ADR-102-02) and `ResolveAssetConfigs`; cache headers in `ServesSizeVariantAssetTrait`; `GET /api/v3/Albums/{album_id}/Photos/urls`; v8 grid batching and the `Thumb` direct-`src` path.
- **Out of scope:** bootstrap cost; covers, Flow, map, search and face tiles (stay on `/Asset`); server-side caching of the URL tier; v2/v7.

## Dependencies & Interfaces
- `GetPhotoAssetRequest`, `ResolvesSizeVariantAssetTrait`, `ServesSizeVariantAssetTrait`, `AlbumFactory`, `AlbumPolicy` (Feature 056, ADR-0008); `FacePhotoAssetController` and its request (Feature 093, ADR-093-01).
- `App\Http\Kernel` groups, `ResolveConfigs`, `ResolveVerify`, `SetLocale`, `ConfigManager`, `Watermarker`, `config/log-viewer.php`, `routes/web_v2.php` (`image/{path}`).
- `PhotoChildrenController`, `GetPhotoDetailsRequest`, `ResolvesPhotoSource`, `UrlGenerator` (Feature 064/066 tiers).
- Front end: `PhotoGridVirtual.vue` (`virtualChunks`, `tilesForChunk()`), `PhotoThumbVirtual.vue`, `PhotoListItemVirtual.vue`, `Thumb.vue`, `thumb-asset-service.ts`, `AlbumState.ts`, `TimelineState.ts`, `photo-children-v3-service.ts`, the `MOTION_URL_LOADER` provide/inject pattern (`AlbumPanel.vue`, `Timeline.vue`).
- Scratch instance (`run-lychee` skill) and the profiling harness in the scratchpad of session 432f6b23: `mkids.php` (ids + admin session cookie), `mkuser.php` (non-admin `viewer` the album is shared with, album made public for guests), `prof.php` and `run.sh` (cold), `loop.php` (warm, Octane-like, modes `admin`/`user`/`guest`; `TRACE=1` for a phase trace), `warm.sh` (all three modes), `ab.sh` (HEAD vs working copy), `boot.php`, `seedbig.php` + `payload.php` (7,000-photo payload).

## Assumptions & Risks
- **Assumptions:** production runs Octane with config/route caches (Docker image); on MySQL/PostgreSQL each saved query is a network round trip.
- **Risks / Mitigations:**
  - Lean album load diverges from `AlbumPolicy` semantics → keep real models (album, `base_class`, `access_permissions`) and only strip the unused eager loads; the 35 `PhotoAssetV3Test` tests stay green.
  - Folding membership into the size-variant query turns a 403 into a 404 → select it as an `exists` column, never as a `WHERE` (FR-102-02).
  - Middleware move breaks a route relying on global placement → survey in Q-102-05/ADR-102-02; keep the three first in each group; CI runs the full suites.
  - `/Asset` key list misses a key on an untested path → 500 by design (FR-102-03). Build the list by recording `ConfigManager` reads across the whole `PhotoAssetV3Test`, `AssetMiddlewareV3Test` and faces photo suites (T-102-04), not by reading code.
  - `ResolveAssetConfigs` forgets the scoped `Watermarker` → it registers both bindings like `ResolveConfigs::resolve_configs()`.
  - Direct URL points at a missing or expired file (S3 presign) → `Thumb` falls back to `/Asset` on the image `error` event (FR-102-06).

## Implementation Drift Gate
After the last task: walk FR-102-01 … 07 and S-102-01 … 14 against tests and code, rerun the harness and record numbers below, confirm ADR-102-01/02 match the code, and append the drift report here.

## Increment Map
1. **I1 – Lean `/Asset` authorization (FR-102-02)** — T-102-01, T-102-02.
   - _Steps:_ Query-count tests (owner, shared user, public guest; admin on a regular album, an unknown album id and a photo outside the album) and a 403-vs-404 guard test; confirm they fail. In `GetPhotoAssetRequest`, load regular/tag/person albums with only `base_class` and `access_permissions`; select membership as an `exists` column of the size-variant query for regular albums; admins skip the album read and the membership check (FR-102-02).
   - _Commands:_ `php artisan test --filter=PhotoAssetV3Test`, `php artisan test --filter=AssetMiddlewareV3Test`, `make phpstan`.
2. **I2 – Route-group middleware and partial configs (FR-102-01, FR-102-03, FR-102-07)** — T-102-03 … T-102-06.
   - _Steps:_ Tests first (no SE verification read and one key-restricted config query on both binary routes; a v2 and a v3 JSON endpoint still see configs, verify and locale). Move the three middleware into the groups; log-viewer API; `ConfigManager::loadOnly(array $keys)`; record the key list; `ResolveAssetConfigs`; route changes.
   - _Commands:_ `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest|ResolveConfigsTest|ResolveVerifyTest|SetLocaleTest'`, `make phpstan`.
   - _Notes:_ Key list recorded with a temporary `ConfigManager` recorder on the three suites plus a probe over every smart album type and caller; keys of membership paths the probe could not reach (rating albums, person albums) taken from `app/SmartAlbums` and `AlbumPolicy`. Laravel tests share one container across requests, so `AssertsAssetConfigLoad` drops the scoped `ConfigManager` binding before the measured request. `withoutMiddleware('api')` on the Embed routes would also strip the three moved middleware (excluded middleware cannot be re-added), so those routes list the excluded entries.
3. **I3 – HTTP caching (FR-102-04)** — T-102-07, T-102-08.
   - _Steps:_ Tests first (headers on 200, 304 / 200 by `If-Modified-Since`, errors unchanged); implement in `ServesSizeVariantAssetTrait::responseFile()` with the request at hand.
   - _Commands:_ `php artisan test --filter='PhotoAssetV3Test|AssetMiddlewareV3Test|FacePhotoAssetTest'`, `make phpstan`.
4. **I4 – URL tier backend (FR-102-05)** — T-102-09 … T-102-12.
   - _Steps:_ `PhotoUrlsV3Test` first; `GetPhotoUrlsRequest`; `QueryPhotoUrls` (scoped via `ResolvesPhotoSource`, `LEFT JOIN`s on `small2x`/`small`/`thumb`, one tiny pure helper deciding direct-or-null per row); `PhotoUrlResource` (`#[TypeScript]`); route and `PhotoChildrenController::urls()`; `php artisan typescript:transform`.
   - _Commands:_ `php artisan test --filter=PhotoUrlsV3Test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.
5. **I5 – Front-end URL loader (FR-102-06)** — T-102-13, T-102-14.
   - _Steps:_ Vitest first for the loader (dedup resolved and in-flight ids, chunks ≤300, stale-album guard, non-empty all-null disables, error → `null`, reset on reload). `PhotoChildrenV3Service.getUrls()`; loader provided from `AlbumPanel.vue` and `Timeline.vue` through an injection key.
   - _Commands:_ `vp test resources/js/v8/composables/photo/tileUrlLoader.test.ts`, `vp check --fix`, `vp run typecheck`.
   - _Notes:_ `vp check --fix` (project-local `npx vp`) also rewrites five files this feature does not touch (`contextMenu.ts` ×2, `EmbedCodeDialog.vue` ×2, `useSpotlightRemoteSearch.ts`); those changes are reverted, not committed. The loader resets when `albumStore.albumId`/`photoRatiosV3` (album) or `timelineStore.bucketsV3` (timeline) change.
6. **I6 – Grid wiring and `Thumb` (FR-102-06)** — T-102-15 … T-102-18.
   - _Steps:_ Vitest first for the visible-id composable (ids of `virtualChunks` → `tilesForChunk()`, stable key) and for `Thumb` (string → direct `src`, `undefined` → placeholder, `null` → `ThumbAssetService`, `error` → `ThumbAssetService`). Then the composable in `PhotoGridVirtual.vue`, tiles passing their URL, `Thumb` props.
   - _Commands:_ `vp test` (scoped), `vp check --fix`, `vp run typecheck`.
7. **I7 – Verification and docs (NFR-102-01)** — T-102-19.
   - _Steps:_ Browser check on the scratch instance (album, timeline, smart album; unprotected and temporary-link instance; network panel). Harness rerun. Knowledge map, roadmap, Feature 056 spec cross-references to FR-102-02/04.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-102-01 … 03 | I1 / T-102-01, T-102-02 | `PhotoAssetV3Test` |
| S-102-04, S-102-05 | I2 / T-102-03 … T-102-06 | `PhotoAssetV3Test`, `AssetMiddlewareV3Test`, faces photo test |
| S-102-06, S-102-07 | I3 / T-102-07, T-102-08 | `PhotoAssetV3Test` |
| S-102-08 … 12 | I4 / T-102-09 … T-102-12 | `PhotoUrlsV3Test` |
| S-102-13 | I5 / T-102-13, T-102-14 | loader Vitest |
| S-102-14 | I6 / T-102-15 … T-102-18, I7 / T-102-19 | composable + `Thumb` Vitest, browser check |

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
- FR-102-03 key list was vague → one constant built from recorded reads.
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

## Exit Criteria
- Full quality gate green (php-cs-fixer, `vp check --fix`, `vp run typecheck`, scoped tests, `make phpstan`); `php artisan typescript:transform` rerun.
- Harness numbers recorded here; roadmap and knowledge map updated; ADR-102-01/02 match the code; drift gate report appended.

## Follow-ups / Backlog
- Search grid tiles (different album per tile) could get a `/Search/Photos/urls` tier.
- `PhotoThumbVirtual` always fetches `small2x`, even on 1x screens.
- Session user load (user + groups, 2 queries) on every `/Asset` request.
