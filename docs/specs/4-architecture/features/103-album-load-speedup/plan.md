# Feature Plan 103 – Album Load Speedup

_Linked specification:_ [spec.md](spec.md)  
_Status:_ In progress  
_Last updated:_ 2026-10-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Opening an album of 5,000–7,000 photos stops paying for every photo in requests that only describe the album, and v8 stops touching every tile through Vue's reactivity before showing the first one. Success is NFR-103-01 on the scratch instance (`/gallery/{album_id}` and `Album::head` within 15 ms of an empty album, first tile ≤ 800 ms, from 196/198 ms and ≈1,070 ms), NFR-103-02 in a feature test, and no visible change.

## Scope Alignment
- **In scope:** FR-103-01/02 (backend: frame flag and header without loading photos), FR-103-03 … 06 (v8: tile adaptation, navigation links, page map, lazy tile dates, index-based layout, cached scrubber keys), FR-103-07/08 (v8 photo view), FR-103-09/10 (Timeline queries and layout), FR-103-11/12 (stored recursive NSFW flag, ADR-103-01), FR-103-13 … 15 (v8 bulk album edit: icon headers, capture range, Q-103-05).
- **Out of scope:** request order of the album-open sequence, the photo tiers and their payloads, the WASM layout primitives, tile mounting/rendering, v2 photo listing.

## Dependencies & Interfaces
- Backend: `AlbumConfig`, `HasHeaderUrl` (used by the four `Head*AlbumResource` and `App\View\Components\Meta`), the photo relations `HasManyChildPhotos`, `HasManyPhotosByTag`, `HasManyPhotosByPerson` (visibility applied in `addConstraints()`, sorting only in `getResults()`), `BaseSmartAlbum::photos()` (visibility-filtered query); all reached through `AbstractAlbum::photos()`.
- Frontend: `AlbumState.loadPhotosV3()`, `PhotosState` (`setPhotos`, `appendPhotos`, `prependPhotos`, `recordPhotoPages`, `rebuildNavigationLinks`), `adaptPhotoTile()` and its callers (album, timeline, search, flow, photo view), `analyticPhotoLayout.computeVisiblePhotoLayout()`, `PhotoGridVirtual.vue`, `dragAndSelect.getPhotoBoxesV3()`, `dateScrubber.tileKey()`.
- Stored NSFW flag (FR-103-11/12): writers `Album\Create`, `Album\Move`, `Album\Merge`, `Album\Transfer`, `Album\SetProtectionPolicy`, `Admin\BulkEditAlbumsAction`, `Jobs\ApplyNsfwAlbumSensitivityJob`, `Console\Commands\FixTree`, `Admin\Maintenance\FullTree`, `AlbumFactory`; readers `PhotoQueryPolicy::appendSensitivityConditions()`, `RSS\Generate`, `Albums\Flow`, `FlowListController`, `FlowItemResource`; `AlbumBuilder::addVirtualIsRecursiveNSFW()` goes away.
- Bulk album edit (FR-103-13 … 15): `AlbumListController::queryAlbums()` bulk-edit select and `toBulkEditResource()`, `AlbumListBulkEditFieldsResource` (Spatie Data, regenerated into `resources/js/lychee.d.ts` by `php artisan typescript:transform`, then `vp fmt`), v8 `BulkAlbumEdit.vue` (`UTable` `#<id>-header` and `#created_at-cell` slots, `UTooltip`, `UIcon`), `v8/utils/takenRange.ts`, `bulk_album_edit.taken_range` in 23 `lang/<locale>/bulk_album_edit.php` + `php artisan lang:json`.
- Measurement: scratch instance in the session scratchpad (`bench/`: `env.sh`, `seed.php`, `seed_more.php`, `get.sh`, `trace.php`, `open.mjs`, `prof.py`, `segments.py`, `owners.py`, `count.mjs`), SQLite, PHP built-in server `PHP_CLI_SERVER_WORKERS=8 taskset -c 0-7 php -d xdebug.mode=off -d opcache.enable_cli=1 -S 127.0.0.1:8123 …/server.php` from `public/`, Playwright with `/usr/bin/chromium`. The scratchpad lives in `/tmp`: a reboot wipes it; `seed.php` rebuilds the 7,000-photo album in ≈45 s.

## Assumptions & Risks
- **Assumptions:** the relations' constraint queries can run as `exists`/id subqueries without their sorting; Vue reactive proxies call own accessor properties with the proxy as receiver, so lazily formatted dates stay reactive and assignable.
- **Risks / Mitigations:**
  - Header pick differs from today's candidate set (visibility) → S-103-01 … 08 check guest visibility, fallback, tag, person and smart albums. Smart albums now pick among all their visible photos instead of their first page (FR-103-02).
  - Tiles rendered from a non-reactive object stop updating in place → the layout returns indexes only and the grid reads the store's reactive photo (FR-103-05); S-103-13 checked in the browser.
  - A caller assigns `preformatted.*` dates or copies tiles → accessors are own, enumerable and writable (FR-103-04 tests cover spread and assignment).
  - `?page=N` links change → FR-103-03 keeps recording page 1; S-103-11.
  - A writer of `is_nsfw` or of the tree misses the sync and the stored flag goes stale (NSFW shown or hidden wrongly) → one sync action, called explicitly at every writer listed in FR-103-11; `AlbumRecursiveNsfwTest` drives each writer and compares every album's flag with the computed ancestry rule; existing NSFW classes rerun.
  - Migration backfill wrong on MySQL/MariaDB/PostgreSQL → range `UPDATE`s only (no self-referencing subquery, which MySQL rejects), checked on the scratch instance against the computed condition; CI runs all three databases.

## Implementation Drift Gate
In T-103-13: walk FR-103-01 … 10, NFR-103-01 … 03 and S-103-01 … 22 against code and tests, rerun the harness, record the numbers below, and append the drift report here.

**Report (2026-10-28, agent):**

| Requirement | Code | Tests / evidence |
|-------------|------|------------------|
| FR-103-01 | `AlbumConfig::hasFramePhotos()` | `AlbumHeadPhotoCostTest` (frame cases, cost), `FrameSubAlbumsTest` |
| FR-103-02 | `HasHeaderUrl::getHeaderUrl()`, `randomPhotoIdWithVariant()` | `AlbumHeadPhotoCostTest` (S-103-01 … 10), `AlbumMapHeaderTest`, `AlbumSetHeaderTest`, `PersonAlbumTest` |
| FR-103-03 | `linkNeighbours()`, `AlbumState.loadPhotosV3()`, `PhotosState.recordPhotoPages()` | `adaptPhotoTile.test.ts`, `PhotosState.test.ts`, browser S-103-11 |
| FR-103-04 | `adaptPhotoTile()` lazy accessors | `adaptPhotoTile.test.ts` (6 cases) |
| FR-103-05 | `computeVisiblePhotoLayout()`, `PhotoGridVirtual.tilesForChunk()`, `dragAndSelect.getPhotoBoxesV3()` | `analyticPhotoLayout.test.ts`, browser S-103-13/14 |
| FR-103-06 | `useTileKeys()`, `PhotoGridVirtual.scrubberLayout` | `useTileKeys.test.ts`, browser S-103-15 |
| FR-103-07 | `PhotoBox` loading/error state, `PhotoState.afterDetails()`, `utils/photoView.isDetailPending()` | `photoView.test.ts`, browser S-103-16 (video from grid and after reload), S-103-17 (60/30 ms key presses: no broken image) |
| FR-103-08 | `utils/photoView.detailWindowIds()`/`preloadSourceOf()`, `utils/detailRequests`, `PhotoState.load()` | `photoView.test.ts`, `detailRequests.test.ts`, browser S-103-19 (one 11-id request, then one id per step; neighbours' images from cache) |
| FR-103-09 | `ResolvesPhotoSource::resolvePhotoQuery()` scope, `QueryPhotoRatios/Details/Urls`, `QueryPhotoBuckets::smartAlbumQuery()` | `TimelineScopedQueryTest`, 13 related Feature_v3 classes |
| FR-103-10 | `computeTimelinePhotoLayout()` `placeholderHeights` | `analyticPhotoLayout.test.ts` placeholder case |
| FR-103-11 | `albums.is_recursive_nsfw` (migration `2026_10_28_000001`), `SyncRecursiveNsfw::do()`/`isRecursiveNsfw()`/`outermostRanges()`, calls in `Create`, `Move`, `Merge`, `Transfer`, `SetProtectionPolicy`, `BulkEditAlbumsAction`, `ApplyNsfwAlbumSensitivityJob`, `FixTree`, `FullTree`, `AlbumFactory` | `AlbumRecursiveNsfwTest` (11, every writer, stale parent flag), `SyncRecursiveNsfwTest` (5); backfill equal to the correlated condition on the scratch instance (nested NSFW albums) |
| FR-103-12 | `PhotoQueryPolicy::appendSensitivityConditions()` (`origin` null), `RSS\Generate`, `Albums\Flow`, `FlowListController`, `ApplyNsfwAlbumSensitivityJob`; `AlbumBuilder::addVirtualIsRecursiveNSFW()` removed | `TimelineNsfwFlagTest` (4, query shape), `FlowNsfwFlagTest` (2), 44 related classes green (T-103-22) |
| FR-103-13 | v8 `BulkAlbumEdit.vue` `switchHeaders`, `#<id>-header` slots | Browser (scratch, non-SE): five icon headers, tooltips "Sensitive", "Download" |
| FR-103-14 | `AlbumListController` bulk-edit select and `toBulkEditResource()`, `AlbumListBulkEditFieldsResource` | `AlbumListV3Test` (range over album and sub-album, null for an empty album) |
| FR-103-15 | `#created_at-cell` slot, `v8/utils/takenRange.ts`, `bulk_album_edit.taken_range` (23 locales) | `takenRange.test.ts` (4), browser: "Taken: Jan 1, 2015 – Oct 25, 2026", "No data" on an empty album, `LangTest`, `check_translations.php` |
| NFR-103-01 | — | First tile 656 ms (≤ 800 ✓); HTML/`head` +17 ms over an empty album with a 99,000-photo library (+9/+11 with 7,000): 2 ms over 15 |
| NFR-103-02 | — | `AlbumHeadPhotoCostTest` cost cases |
| NFR-103-04 | — | `AlbumListV3Test::testCaptureRangeIsReadByTheListingQuery` |
| NFR-103-05 | — | Icons from the bundled `lucide` set already used by v8 |
| NFR-103-03 | — | Window 24 ms, `urls` 26 ms (≤ 50 ✓), first tile 936 ms (≤ 1,000 ✓); `buckets` 756 → 408 ms with FR-103-12 on the same data, measured on battery (see Measurements): the ≤ 250 ms target is not yet checked on AC power |

Findings: none undocumented. Not covered by automated tests: S-103-12 in the browser (Vitest compares the strings), FR-103-07 failure path in the browser (no fault injection; `detailRequests.test.ts` covers failure propagation). Open: Q-103-04. v7 photo view is out of scope (its own `PhotoBox`); the details window and preloading in `PhotoState.load()` apply to v7 as well since the store is shared.

## Measurements
Scratch instance of the spec's Baseline (PHP server pinned to CPUs 0–7, median of 7 requests, ms). The 10-photo album separates the fixed cost of a non-empty album (covers, header variant) from per-photo work.

| Request | Empty | 10 photos | 7,000 photos, before | 7,000 photos, after I1 (library of 7,000) | 7,000 photos, final (library of 99,000, 21 requests) |
|---------|-------|-----------|----------------------|------------------------|------------------------|
| `GET /gallery/{album_id}` | 26 | 27 | 196 | 35 | 43 |
| `GET /api/v2/Album::head` | 28 | 29 | 198 | 39 | 46 |

After I1, the remaining per-photo work of both requests is the random header pick: `count` then `offset` over the album's visible photos, one `EXISTS` probe of `size_variants(photo_id, type, ratio)` per photo. Query plan (SQLite): `photo_album` by `album_id`, then index probes: the cost follows the album's photo count, and only the index depth follows the library (+17 ms over an empty album with 99,000 photos in the library against +9/+11 with 7,000; NFR-103-01 asks for 15).

Random pick alone, in-process, admin, 7,000 photos, SQLite, 30 runs (median, p10–p90, ms). Not measured on MySQL/MariaDB or PostgreSQL; `count` + `offset` is kept there because `ORDER BY RAND()` computes and sorts a random value per candidate on MySQL (laravel/framework#14263, Q-103-03).

| Candidate set | `count` + `offset` | `ORDER BY RANDOM() LIMIT 1` |
|---------------|--------------------|-----------------------------|
| Variant rows (≈4 per photo) | 19.8 (17.3–22.9) | 15.4 (15.1–15.6) |
| Photos with `EXISTS` (shipped) | 9.1 (7.0–11.0) | 7.0 (6.4–7.5) |

Timeline, ≈99,000 photos in 47 albums (6,143 day buckets), PHP server pinned to CPUs 0–7, median of 5 requests / 3 page loads (ms):

| | Before | After I6 (FR-103-09) |
|---|---|---|
| `Photos/buckets` | 650 | 430 |
| `Photos` (2-day window) | 620 | 24 |
| `Photos/urls` (30 ids) | 495 | 26 |
| `Photos/details` (16 ids) | ≈450 | 33 |
| First tile on `/timeline` | ≈1,760 | ≈930 (with FR-103-10) |

Stored NSFW flag (FR-103-11/12), rebuilt scratch instance (99,010 photos, 48 albums, 6,143 day buckets), laptop **on battery** (`powersave` governor, cores at 0.4–1.0 GHz), so absolute numbers are higher than the rows above; both columns on identical data and machine state, median of 7 requests (ms):

| | Correlated condition | Stored flag |
|---|---|---|
| `Photos/buckets` request | 756 | 408 |
| its SQL (`bucketvariants.php`, median of 5) | 687 | 394 |

Remaining SQL cost of `buckets` with the stored flag (same run): without the access-permission join 308, with `COUNT(*)` instead of `COUNT(DISTINCT)` 285, both 275, photos alone without joins 43: the `photo_album`/`albums` joins over 99,000 rows are what remains.

SQLite plans: the smart-album id subquery listed every visible photo of the library (sensitivity `NOT EXISTS` per row) before the outer window or id filter applied. `buckets` with `COUNT(DISTINCT)` keeps the per-row sensitivity check (Q-103-04: uncorrelated form measured at 170 ms).

First tile ("first `a.photo` inserted", in-page `MutationObserver`; median of 3–5 full page loads, ms):

| Page | Before | After I1–I3, I6 |
|------|--------|-----------------|
| Album, 7,000 photos | ≈1,070 | ≈650 |
| `/timeline`, ≈99,000 photos | ≈1,760 | ≈930 |

## Increment Map
1. **I1 – Backend: no per-photo work in `Album::head` and the HTML page** (FR-103-01, FR-103-02, NFR-103-02)
   - _Preconditions:_ spec FR-103-01/02, S-103-01 … 10.
   - _Steps:_ `AlbumHeadPhotoCostTest` (Feature_v3) red for S-103-10 (query count 2 vs 20 photos, `Photo` models retrieved), the other scenarios green against today's code; `AlbumConfig::hasFramePhotos()` → `exists()` on the photo relation; `HasHeaderUrl` → `exists()` + id subquery for both random picks; rerun related classes.
   - _Commands:_ `php artisan test --filter=AlbumHeadPhotoCostTest`, `--filter=AlbumMapHeaderTest`, `--filter=FrameSubAlbumsTest`, `--filter=AlbumHeadEndpointTest`, `--filter=AlbumSetHeaderTest`, `--filter=PersonAlbumTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Exit:_ tests green, `trace.php` shows no `photos.*` load and no per-photo bindings, request medians recorded.
2. **I2 – v8 tile data path** (FR-103-03, FR-103-04)
   - _Steps:_ Vitest `adaptPhotoTile.test.ts` and `PhotosState.test.ts` red; lazy date accessors in `adaptPhotoTile()`; navigation ids during adaptation in `loadPhotosV3()`; one-assignment page map in `PhotosState`.
   - _Commands:_ `vp test`, `vp check`, `vp run typecheck`.
   - _Exit:_ tests green, profile shows no `rebuildNavigationLinks`/`recordPhotoPages`/date formatting for unseen tiles.
3. **I3 – v8 layout and scrubber** (FR-103-05, FR-103-06)
   - _Steps:_ Vitest `analyticPhotoLayout.test.ts` red (indexes, rating filter); `computeVisiblePhotoLayout()` on raw arrays returning indexes; `PhotoGridVirtual` and `dragAndSelect` read the store by index; scrubber key cache.
   - _Commands:_ `vp test`, `vp check`, `vp run typecheck`, `vp build`.
   - _Exit:_ tests green, logpoint counts unchanged, tile keys computed once per load.
4. **I5 – v8 photo view** (FR-103-07, FR-103-08)
   - _Steps:_ reproduce on the scratch instance with imported photos and videos (`lychee:sync` of `tests/Samples` copies, served same-origin through `router.php`); Vitest `photoPreload.test.ts` red; pure helpers (pending check, details window, preload source, view mode); `PhotoBox` loading/error state; `PhotoState.load()` window + preload.
   - _Commands:_ `vp test`, `vp check`, `vp run typecheck`, `vp build`, Playwright.
   - _Exit:_ S-103-16 … 19 in the browser.
5. **I7 – Stored recursive NSFW flag** (FR-103-11, FR-103-12, NFR-103-03, ADR-103-01)
   - _Preconditions:_ Q-103-04 resolved (Option C), spec FR-103-11/12, S-103-23 … 28.
   - _Steps:_ red tests: `SyncRecursiveNsfwTest` (unit, outermost ranges), `AlbumRecursiveNsfwTest` (every writer, S-103-23 … 26), `TimelineNsfwFlagTest` (S-103-27, query shape); migration adding and backfilling `albums.is_recursive_nsfw`; `SyncRecursiveNsfw` action and its calls at every writer and in `AlbumFactory`; readers switched (FR-103-12), computed select removed; existing tests that write `is_nsfw` directly call the sync; existing NSFW classes rerun one at a time; scratch instance migrated, backfill compared with the computed condition, `buckets` re-measured.
   - _Commands:_ `php artisan test --filter=SyncRecursiveNsfwTest`, `--filter=AlbumRecursiveNsfwTest`, `--filter=TimelineNsfwFlagTest`, then the classes listed in T-103-22, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Exit:_ tests green, backfill equal to the computed condition on the scratch instance, `buckets` ≤ 250 ms (NFR-103-03).
6. **I8 – v8 bulk album edit hints** (FR-103-13 … 15, NFR-103-04/05, Q-103-05)
   - _Steps:_ `AlbumListV3Test` cases red (range over album and sub-album, nulls, one listing query reads the columns); select and resource fields, `typescript:transform` + `vp fmt`; Vitest `takenRange.test.ts` red; helper, header slots, date tooltip, translations; browser check.
   - _Commands:_ `php artisan test --filter=AlbumListV3Test`, `vp test`, `vp check` (no `--fix`), `vp fmt` on the changed files, `vp run typecheck`, `php artisan test --filter=LangTest`, `php scripts/check_translations.php`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Exit:_ tests green, page checked in the browser on the scratch instance.
7. **I4 – Verification and docs** (NFR-103-01, S-103-11 … 19)
   - _Steps:_ browser checks on the scratch instance; harness medians; knowledge map, roadmap; drift gate.
   - _Commands:_ harness (`get.sh`, `open.mjs`), `vp build`.
   - _Exit:_ NFR-103-01 met and recorded, drift gate passed.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-103-01 … 08 | I1 / T-103-01 … 03 | `AlbumHeadPhotoCostTest` |
| S-103-09 | I1 / T-103-01, T-103-03 | `AlbumHeadPhotoCostTest` (meta tags) |
| S-103-10 | I1 / T-103-01 … 03 | red before |
| S-103-11 | I2 / T-103-05, T-103-07, T-103-11 | Vitest + browser |
| S-103-12 | I2 / T-103-05, T-103-06, T-103-11 | Vitest + browser |
| S-103-13 | I3 / T-103-09, T-103-11 | browser |
| S-103-14 | I3 / T-103-08, T-103-09, T-103-11 | Vitest + browser |
| S-103-15 | I3 / T-103-08, T-103-10, T-103-11 | Vitest + browser |

## Analysis Gate
2026-10-28, agent self-review against [analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md):
1. Specification completeness — pass: goals, FR-103-01 … 06, NFR-103-01/02 populated; Q-103-01/02 folded into Goals, Non-Goals, FRs and NFR-103-01; no visual change, so no mock-up beyond UI-103-01.
2. Open questions — pass: none open; no ADR (no cross-feature boundary or contract change: responses unchanged).
3. Plan alignment — pass: links to spec and tasks; success criteria are NFR-103-01/02.
4. Tasks coverage — pass: every FR maps to a task; tests precede implementation in each increment; S-103-10 and the Vitest suites are red first.
5. Working agreements — pass: no dependency change, no new endpoint, explicit writes only; small helpers (`hasVisiblePhotos`, header id subquery, lazy date accessor, key cache).
6. Tooling — pass: commands listed per increment; harness described under Dependencies.

I7 (2026-10-28, agent): 1. pass — FR-103-11/12, S-103-23 … 28, DB-103-01 and NFR-103-03 updated from Q-103-04 (Option C). 2. pass — no open question; ADR-103-01 records the persistence and visibility decision. 3. pass — I7 in the increment map. 4. pass — T-103-20 … 23, tests (T-103-20) before code (T-103-21/22). 5. pass — no dependency change, no endpoint change, explicit writes only (no model events), outermost-range helper pure and unit-tested; migration without a test of its own (owner rule), checked on the scratch instance. 6. pass — commands in T-103-20 … 23.

I8 (2026-10-28, agent): 1. pass — FR-103-13 … 15, NFR-103-04/05, S-103-29 … 33, UI mock-ups, Q-103-05 folded into Overview, Goals, Non-Goals. 2. pass — no open question; no ADR (two additive response fields). 3. pass — I8 in the increment map. 4. pass — T-103-24 … 27, API and Vitest cases red before code. 5. pass — no dependency change, no new endpoint, new response fields required, pure label helper. 6. pass — commands in I8.

## Exit Criteria
- `php artisan test` on the I1 classes, `make phpstan`, `vendor/bin/php-cs-fixer fix` clean.
- `vp check --fix` (changed files only, see the frontend gate gotchas), `vp run typecheck`, `vp test`, `vp build` clean.
- NFR-103-01 medians recorded below; S-103-11 … 15 checked in the browser.
- Roadmap row and knowledge map updated; drift gate report appended.

## Follow-ups / Backlog
- Request the photo tiers alongside `Album::head` (Q-103-01 Option C), if production numbers still call for it.
- A batched WASM layout call for many small buckets (`@lychee-org/layouts`).
