# Feature 103 Tasks – Album Load Speedup

_Status: In progress (I7)_  
_Last updated: 2026-10-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Backend
- [x] T-103-01 – `AlbumHeadPhotoCostTest` (Feature_v3) (FR-103-01, FR-103-02, NFR-103-02, S-103-01 … 10).  
  _Intent:_ Header and frame flag for regular, tag, person and smart albums, guest visibility, fallback to non-landscape, `header_id`, empty album, Open Graph image of `/gallery/{album_id}`; query count of `Album::head` and `/gallery/{album_id}` equal for 2 and 20 photos and zero `Photo` models retrieved (red before).  
  _Verification commands:_  
  - `php artisan test --filter=AlbumHeadPhotoCostTest`

- [x] T-103-02 – Frame flag without loading photos (FR-103-01, S-103-05, S-103-06).  
  _Intent:_ `AlbumConfig::hasFramePhotos()` uses `exists()` on the album's photo relation.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumHeadPhotoCostTest`  
  - `php artisan test --filter=FrameSubAlbumsTest`

- [x] T-103-03 – Header without loading photos (FR-103-02, S-103-01 … 04, S-103-07 … 10).  
  _Intent:_ `HasHeaderUrl`: emptiness via `exists()`, both random picks and the final read filtered by an id subquery of the album's visible photos; smart albums keep their page.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumHeadPhotoCostTest`  
  - `php artisan test --filter=AlbumMapHeaderTest`

- [x] T-103-04 – Backend gate and numbers (NFR-103-01, NFR-103-02).  
  _Intent:_ Related classes green, static analysis, style; `trace.php` and request medians recorded in the plan.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumHeadEndpointTest` (and `AlbumSetHeaderTest`, `AlbumSetCoverTest`, `TagAlbumSetCoverTest`, `PersonAlbumTest`, `FrameSubAlbumsTest`, `AlbumMapHeaderTest`, `GalleryPasswordWebTest`, `LoadingIndicatorWebTest`), one at a time  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

### I2 – v8 tile data path
- [x] T-103-05 – Vitest for tile dates and the photos store (FR-103-03, FR-103-04, S-103-11, S-103-12).  
  _Intent:_ `adaptPhotoTile.test.ts` (same strings as eager formatting, nothing formatted before a read, assignment, spread); `PhotosState.test.ts` (one page-map assignment per batch, navigation ids). Red before.  
  _Verification commands:_  
  - `vp test`

- [x] T-103-06 – Lazy tile dates (FR-103-04, S-103-12).  
  _Intent:_ Own, enumerable, writable accessors for the three `preformatted` dates in `adaptPhotoTile()`.  
  _Verification commands:_  
  - `vp test`  
  - `vp run typecheck`

- [x] T-103-07 – Navigation ids at adaptation, one-assignment page map (FR-103-03, S-103-11).  
  _Intent:_ `loadPhotosV3()` sets `next_photo_id`/`previous_photo_id` on the plain tiles and drops its `rebuildNavigationLinks()` call; `PhotosState.recordPhotoPages()` assigns one new map.  
  _Verification commands:_  
  - `vp test`  
  - `vp run typecheck`

### I3 – v8 layout and scrubber
- [x] T-103-08 – Vitest for the index-based layout and the key cache (FR-103-05, FR-103-06, S-103-14, S-103-15).  
  _Intent:_ `analyticPhotoLayout.test.ts`: indexes into the photo array with and without rating filter; key cache reused across layouts of the same array. Red before.  
  _Verification commands:_  
  - `vp test`

- [x] T-103-09 – Index-based layout (FR-103-05, S-103-13, S-103-14).  
  _Intent:_ `computeVisiblePhotoLayout()` on raw arrays returning indexes; `PhotoGridVirtual` renders the store's reactive photo by index; `dragAndSelect.getPhotoBoxesV3()` reads ids by index.  
  _Verification commands:_  
  - `vp test`  
  - `vp run typecheck`

- [x] T-103-10 – Scrubber key cache (FR-103-06, S-103-15).  
  _Intent:_ Tile keys computed once per photo array, field and granularity; layouts re-pair them with tops.  
  _Verification commands:_  
  - `vp test`  
  - `vp run typecheck`

### I5 – v8 photo view
- [x] T-103-14 – Vitest for the photo view helpers (FR-103-07, FR-103-08, S-103-17, S-103-19).  
  _Intent:_ `photoPreload.test.ts`: pending-details check, details window around an index (10 next, 5 previous, unmerged only), preload source per photo (medium `srcset`/`sizes`, original, none for video/PDF/raw) matching the view mode. Red before.  
  _Verification commands:_  
  - `vp test`

- [x] T-103-15 – Photo view loading state (FR-103-07, S-103-16 … 18).  
  _Intent:_ `PhotoBox` shows `LycheeLoadingIcon` while details are pending or the image has not loaded, mounts `<img>`/`<video>`/`<embed>` only with a URL, error placeholder on failed details or image error; `PhotoState` view-mode getters use the extracted pure helpers; the details loaders report failure to `PhotoState`.  
  _Verification commands:_  
  - `vp test`  
  - `vp run typecheck`

- [x] T-103-16 – Details window and image preload (FR-103-08, S-103-19).  
  _Intent:_ `PhotoState.load()` requests the details window and preloads the images of the 3 next photos and the previous one once merged.  
  _Verification commands:_  
  - `vp test`  
  - `vp run typecheck`

### I6 – Timeline
- [x] T-103-17 – `TimelineScopedQueryTest` (Feature_v3) (FR-103-09, S-103-20, S-103-21).  
  _Intent:_ A photo in two albums counts and appears once (green before); `buckets` runs no id subquery, window/details/urls restrict it (3 red before).  
  _Verification commands:_  
  - `php artisan test --filter=TimelineScopedQueryTest`

- [x] T-103-18 – Scoped smart-album queries (FR-103-09, NFR-103-03).  
  _Intent:_ `resolvePhotoQuery()` takes the request scope and applies it inside the smart-album id subquery; ratios, details and urls pass it; `QueryPhotoBuckets` counts `DISTINCT photos.id` on the smart album's own query.  
  _Verification commands:_  
  - `php artisan test --filter=TimelineScopedQueryTest` (and `PhotoBucketsV3Test`, `PhotoDetailsV3Test`, `PhotoRatiosV3Test`, `PhotoUrlsV3Test`, `TimelineAlbumTest`, `TimelineFullPhotoAccessTest`, `BucketSettingsV3Test`, `AlbumSlugV3Test`, `Photo360ResourcesTest`, `MapListingV3Test`, `SearchV3ParityTest`, `SearchV3FullPhotoAccessTest`, `SearchV3GatingTest`), one at a time  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-103-19 – Placeholder height per distinct count (FR-103-10, S-103-22).  
  _Intent:_ `analyticPhotoLayout.test.ts` placeholder case (red before: 6 layouts for 3 sizes); per-call `placeholderHeights` map in `computeTimelinePhotoLayout()`.  
  _Verification commands:_  
  - `vp test`

### I7 – Stored recursive NSFW flag
- [x] T-103-20 – Red tests for the stored flag (FR-103-11, FR-103-12, S-103-23 … 27).  
  _Intent:_ `tests/Unit/Actions/Album/SyncRecursiveNsfwTest` (outermost ranges of nested `_lft`/`_rgt` pairs); `tests/Feature_v3/Album/AlbumRecursiveNsfwTest` (create under root/plain/NSFW/NSFW-descendant parent, protection policy on/off, bulk edit, NSFW album job, move in/out, merge, transfer, `lychee:fix-tree`, maintenance full-tree update; each compares every album's stored flag with the computed ancestry rule); `tests/Feature_v3/Timeline/TimelineNsfwFlagTest` (hidden below NSFW, shown once through a non-NSFW album, photo without album, no correlated sensitivity subquery in the query log); `tests/Feature_v3/Flow/FlowNsfwFlagTest` (S-103-28). Red before T-103-21/22: the unit and writer cases and the query-shape case; the visibility cases pass on the correlated condition and guard parity.  
  _Verification commands:_  
  - `php artisan test --filter=SyncRecursiveNsfwTest`  
  - `php artisan test --filter=AlbumRecursiveNsfwTest`  
  - `php artisan test --filter=TimelineNsfwFlagTest`

- [x] T-103-21 – Column, sync action and writers (FR-103-11, DB-103-01).  
  _Intent:_ Migration adding `albums.is_recursive_nsfw` and backfilling it with range updates; `App\Actions\Album\SyncRecursiveNsfw` (`do()` over the outermost NSFW ranges, writing changed flags only; `isRecursiveNsfw()` for a new album's parent; pure `outermostRanges()`); explicit calls in `Create`, `Move`, `Merge`, `Transfer`, `SetProtectionPolicy`, `BulkEditAlbumsAction`, `ApplyNsfwAlbumSensitivityJob`, `FixTree` command, maintenance `FullTree`, `AlbumFactory`; `Album::$attributes` default.  
  _Verification commands:_  
  - `php artisan test --filter=SyncRecursiveNsfwTest`  
  - `php artisan test --filter=AlbumRecursiveNsfwTest`

- [x] T-103-22 – Readers on the stored flag (FR-103-12, S-103-27, S-103-28).  
  _Intent:_ `PhotoQueryPolicy::appendSensitivityConditions()` reads the column when `origin` is null; RSS album list, Flow without base, `FlowListController`, `ApplyNsfwAlbumSensitivityJob` read it; `AlbumBuilder::addVirtualIsRecursiveNSFW()` removed; tests that write `is_nsfw` directly call the sync.  
  _Verification commands:_  
  - `php artisan test --filter=TimelineNsfwFlagTest` and, one at a time, `TimelineAlbumTest`, `SearchPhotoSourceTest`, `FrameSubAlbumsTest`, `RssTest`, `FlowTest`, `FlowInitTest`, `FlowV3Test`, `FlowNsfwFlagTest`, `ApplyNsfwAlbumSensitivityJobTest`, `RecomputeAlbumStatsJobTest`, `CoverSelectionNsfwTest`, `AlbumCoverSecurityTest`, `EventPropagationIntegrationTest`, `OverridePermissionsTest`, `GetTagsTest`, `PatchTest`, `SetProtectionPolicyTest`, `ProtectionPolicyOwnershipTest`, `CoverageTest`, `TimelineScopedQueryTest`, `AlbumHeadPhotoCostTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [ ] T-103-23 – Scratch check and docs (NFR-103-03).  
  _Intent:_ Migrate the scratch instance, compare the backfilled flags with the computed condition, re-measure Timeline `buckets`; knowledge map, roadmap, drift gate rows for FR-103-11/12.  
  _Status:_ backfill checked, docs done, `buckets` 756 → 408 ms on identical data; the absolute NFR-103-03 check waits for a run on AC power (the laptop ran on battery).  
  _Verification commands:_  
  - harness `get.sh`

### I8 – v8 bulk album edit hints
- [x] T-103-24 – Red API tests (FR-103-14, NFR-103-04, S-103-30, S-103-31, S-103-33).  
  _Intent:_ `AlbumListV3Test`: an album and its sub-album with photos taken on different days, after `RecomputeAlbumStatsJob`, give the range over both in `min_taken_ats`/`max_taken_ats`; an album without photos gives `null` for both; with the cache off, the columns are read by the single listing query.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumListV3Test`

- [x] T-103-25 – Select and resource fields (FR-103-14).  
  _Intent:_ `albums.min_taken_at`/`max_taken_at` in the bulk-edit select of `AlbumListController::queryAlbums()`, `min_taken_ats`/`max_taken_ats` in `toBulkEditResource()` and `AlbumListBulkEditFieldsResource`; `php artisan typescript:transform`, then `vp fmt resources/js/lychee.d.ts`.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumListV3Test`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-103-26 – Range label, icon headers and date tooltip (FR-103-13, FR-103-15, NFR-103-05, S-103-29, S-103-31, S-103-32).  
  _Intent:_ Vitest for the pure range-label helper (both dates, same day, no date, one date) red first; helper; `#<column>-header` slots with `UTooltip` + `UIcon` for the six switch columns; `UTooltip` on the creation date cell; `bulk_album_edit.taken_range` in every `lang/<locale>/bulk_album_edit.php`, `php artisan lang:json`.  
  _Verification commands:_  
  - `vp test`  
  - `vp check` and `vp fmt` on the changed files  
  - `vp run typecheck`  
  - `php artisan test --filter=LangTest`  
  - `php scripts/check_translations.php`

- [x] T-103-27 – Browser check and docs (S-103-29).  
  _Intent:_ Bulk album edit page on the scratch instance (non-SE: five icons; tooltip texts; date tooltip); knowledge map, roadmap, drift gate rows.  
  _Verification commands:_  
  - Playwright on the scratch instance

### I4 – Verification and docs
- [x] T-103-11 – Frontend gate and browser checks (S-103-11 … 15).  
  _Intent:_ `vp check` on the changed files, typecheck, tests, build; Playwright on the scratch instance: navigation order and `?page=1`, dates, star/rating in place, rating filter and drag selection, resize with scrubber.  
  _Verification commands:_  
  - `vp check --fix` (changed files only)  
  - `vp run typecheck`  
  - `vp test`  
  - `vp build`

- [x] T-103-12 – NFR-103-01 numbers (NFR-103-01).  
  _Intent:_ Request medians and 5 full page loads on the scratch instance, recorded in the plan.  
  _Verification commands:_  
  - harness `get.sh`, `open.mjs`

- [x] T-103-13 – Docs and drift gate.  
  _Intent:_ Knowledge map (`Album::head`, v8 photo grid), roadmap row, drift gate report in the plan.  
  _Verification commands:_  
  - review of spec ↔ code ↔ tests

## Notes / TODOs
- The scratch instance and harness live in the session scratchpad under `/tmp`; a reboot wipes them (rebuild with `seed.php`).
