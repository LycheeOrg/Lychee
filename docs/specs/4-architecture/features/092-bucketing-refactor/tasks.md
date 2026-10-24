# Feature 092 Tasks – Bucketing Refactor

_Status: Testing_  
_Last updated: 2026-10-15_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

- [x] T-092-01 – Schema and backfill migrations (F-092-16).  
  _Intent:_ add `photos.taken_key` (indexed) and `photos.title_date_key`; backfill them; rename `photo_album.bucket_id` and `albums.bucket_id` to `bucket_key` with their composite indexes; backfill every bucket key in the FR-092-03/04 format. Chunks of 1,000, progress bar, helpers copied under `database/migrations/TemporaryModels/`. Verified up/down on SQLite and on a populated scratch copy.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoUploadBucketTest`  
  - `make phpstan`

- [x] T-092-02 – Key helper tests (F-092-01, F-092-02, S-092-14).  
  _Intent:_ `tests/Unit/Services/BucketKeysTest.php`: taken key from a cast value and from raw UTC plus zone (named zone, fixed offset, DST, `NULL`); title date key (`YYYY`, `YYYY-MM`, `YYYY-MM-DD`, leading spaces, no date, invalid month/day). Red before T-092-03.  
  _Verification commands:_  
  - `php artisan test --filter=BucketKeysTest`

- [x] T-092-03 – Key helpers (F-092-01, F-092-02).  
  _Intent:_ `App\Services\BucketKeys` without Carbon construction (`DateTimeImmutable` for raw values).  
  _Verification commands:_  
  - `php artisan test --filter=BucketKeysTest`  
  - `make phpstan`

- [x] T-092-04 – Photo key write sites (F-092-01, F-092-02, F-092-10, S-092-04, S-092-13).  
  _Intent:_ tests first (`tests/Feature_v3/Photo/PhotoKeysWriteTest.php` for `PATCH /Photo`, rename, bulk renamer, `lychee:takedate`; `PhotoUploadBucketTest` for upload); then assign the keys in `HydrateMetadata`, `Save`, `PhotoController::update()`/`rename()`, `RenamePhotos`, `Takedate`, and dispatch `RecomputePhotoBucketsJob` from `RenamePhotos` and `Takedate`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoKeysWriteTest`  
  - `php artisan test --filter=PhotoUploadBucketTest`

- [x] T-092-05 – Photo bucket key computation (F-092-03).  
  _Intent:_ update `PhotoBucketComputerTest` for keys per sort column (no granularity input), then rewrite `PhotoBucketComputer` to compute keys from row values (`taken_key`, raw `created_at`, `title_date_key`, `title_base`, `is_highlighted`, `type`, `rating_avg`), no Carbon.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBucketComputerTest`

- [x] T-092-06 – Photo write paths and chunking (F-092-03, F-092-10, F-092-14, S-092-12).  
  _Intent:_ update `RecomputePhotoBucketsJobTest`, `RecomputeAlbumPhotoBucketsJobTest` (1,001 links), `PhotoUploadBucketTest`; then `SetParent`, `MoveOrDuplicate`, `RecomputePhotoBucketsJob`, `RecomputeAlbumPhotoBucketsJob` (chunks of 1,000).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputePhotoBucketsJobTest`  
  - `php artisan test --filter=RecomputeAlbumPhotoBucketsJobTest`  
  - `php artisan test --filter=PhotoUploadBucketTest`

- [x] T-092-07 – Album bucket keys (F-092-04, F-092-14).  
  _Intent:_ update `RecomputeAlbumStatsJobBucketTest`, `RecomputeChildAlbumBucketsJobTest`; rewrite `AlbumBucketComputer`; `RecomputeAlbumStatsJob`, `RecomputeChildAlbumBucketsJob` (chunked).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeAlbumStatsJobBucketTest`  
  - `php artisan test --filter=RecomputeChildAlbumBucketsJobTest`

- [x] T-092-08 – All-row jobs and command (F-092-13, F-092-14, F-092-15, S-092-10, S-092-12, S-092-17).  
  _Intent:_ tests `RecomputeAllAlbumBucketsJobTest` (children of parents without override included), `RecomputeAllPhotoBucketsJobTest` (1,001 links), `RecomputeBucketsCommandTest` updated; then `RecomputeAllAlbumBucketsJob`, `RecomputeAllPhotoBucketsJob` and `lychee:recompute-buckets` sharing the chunked routines.  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeAllAlbumBucketsJobTest`  
  - `php artisan test --filter=RecomputeAllPhotoBucketsJobTest`  
  - `php artisan test --filter=RecomputeBucketsCommandTest`

- [x] T-092-09 – Read scheme tests (F-092-05, F-092-07, F-092-08).  
  _Intent:_ `tests/Unit/Services/BucketSchemeTest.php`: prefix length per column and granularity, title date cap, alphabetical length, categorical whole key, next id per precision (year/month/day/hour, month and year rollover), labels by precision, `unknown`. Red before T-092-10.  
  _Verification commands:_  
  - `php artisan test --filter=BucketSchemeTest`

- [x] T-092-10 – Read scheme (F-092-05, F-092-07, F-092-08).  
  _Intent:_ `App\Services\BucketScheme`: SQL id expression over a stored key or a photo column, window filter, `NULL`-last ordering, id normalisation, labels.  
  _Verification commands:_  
  - `php artisan test --filter=BucketSchemeTest`  
  - `make phpstan`

- [x] T-092-11 – Regular-album `buckets` tier (F-092-05, F-092-08, F-092-09, S-092-01, S-092-03, S-092-11, S-092-15).  
  _Intent:_ tests in `PhotoBucketsV3Test` (granularity switch without job, title date at hour, unvalidated photos per viewer, `unknown` last both directions); then `QueryPhotoBuckets` on `photo_album` only with the validation subquery.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBucketsV3Test`

- [x] T-092-12 – Regular-album `ratios` and `details` windows (F-092-05, F-092-07).  
  _Intent:_ tests in `PhotoRatiosV3Test`, `PhotoDetailsV3Test` (window per id kind, per-row ids, ordering); then `QueryPhotoRatios`, `QueryPhotoDetails` through the scheme.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoRatiosV3Test`  
  - `php artisan test --filter=PhotoDetailsV3Test`

- [x] T-092-13 – Timeline on `taken_key` (F-092-06, S-092-04, S-092-07).  
  _Intent:_ tests in `TimelineAlbumTest` (capture-local hour buckets, windows, dateless photos excluded); then Timeline buckets/windows through the scheme.  
  _Verification commands:_  
  - `php artisan test --filter=TimelineAlbumTest`

- [x] T-092-14 – Tag, person and smart albums in SQL (F-092-06, F-092-07, S-092-05, S-092-06, S-092-15).  
  _Intent:_ tests for a tag album per sort column and a smart album (buckets, ratios windows and per-row ids, details windows); then the SQL path through the scheme; remove `liveBucketId()`, `liveTitleBucket()`, `truncateRawDate()` and the `resolveLiveBucket…` helpers.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBucketsV3Test`  
  - `php artisan test --filter=PhotoRatiosV3Test`  
  - `php artisan test --filter=PhotoDetailsV3Test`

- [x] T-092-15 – Album children and root listings (F-092-05, F-092-08, S-092-10).  
  _Intent:_ tests in `AlbumBucketsV3Test`, `AlbumChildrenDataV3Test`, `AlbumRootV3Test` (prefix ids per granularity without recompute, per-row ids, labels); then `AlbumChildrenController`, `AlbumRootController`, `QueryChildrenForAlbum`, `BuildAlbumDataResource`.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumBucketsV3Test`  
  - `php artisan test --filter=AlbumChildrenDataV3Test`  
  - `php artisan test --filter=AlbumRootV3Test`

- [x] T-092-16 – Album edit triggers (F-092-11, F-092-12, S-092-08, S-092-10).  
  _Intent:_ update `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest` (job only on sort column change; order/timeline change flushes the listing); then `AlbumController`.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumSortingBucketDispatchTest`  
  - `php artisan test --filter=PhotoSortingBucketDispatchTest`

- [x] T-092-17 – Settings triggers and photo-listing flush (F-092-13, S-092-02, S-092-09).  
  _Intent:_ tests in `UpdateSettingsTest`, `CacheKeyProviderTest` and a new `tests/Feature_v3/Settings/BucketSettingsV3Test.php` (granularity change visible in an inheriting album without a job; sort/mode changes dispatch the all-row jobs); then the `photo-listing-global` tag on every photo-listing entry, its flush event, `SettingsController` key lists.  
  _Verification commands:_  
  - `php artisan test --filter=UpdateSettingsTest`  
  - `php artisan test --filter=CacheKeyProviderTest`  
  - `php artisan test --filter=BucketSettingsV3Test`

- [x] T-092-18 – Timeline cache invalidation (F-092-17, S-092-18).  
  _Intent:_ update `ManagedCachePhotoListingInvalidatorTest`; then resolve old/new Timeline buckets from `taken_key`/`created_at` (`PhotoSaved` previous values carry `taken_key`).  
  _Verification commands:_  
  - `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`

- [x] T-092-19 – Date scrubber capture-local day (F-092-18, S-092-16).  
  _Intent:_ `tileDayKey()` reads `taken_at` in the tile's `taken_at_orig_tz`; browser check on a scratch instance.  
  _Status:_ browser check done 2026-10-14: scratch SQLite instance, album sorted by `taken_at` at day granularity, photo taken 2026-07-01 23:30 UTC in `+02:00` plus two controls; Playwright with viewer zones `America/New_York` and `Asia/Tokyo`: rail days equal header days (`20 Jun`, `1 Jul`, `2 Jul 2026`), late photo on `2 Jul` in both, no console errors.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-092-20 – Documentation.  
  _Intent:_ knowledge map, roadmap, Feature 064/066/071 spec references to FR-092-xx, `_current-session.md`.  
  _Verification commands:_ none.

- [x] T-092-21 – Quality gate, benchmark, drift gate (NFR-092-01 … 06).  
  _Intent:_ every class above, php-cs-fixer, phpstan, `typescript:transform` without diff, scratch benchmark of the generated queries, drift gate report in plan.md.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`  
  - `php artisan typescript:transform`

- [x] T-092-22 – Album title bulk rename recomputes album keys (F-092-12, S-092-19).  
  _Intent:_ test first in `tests/Feature_v3/Album/AlbumKeysWriteTest.php` (bulk renamer gives a child of a `date_prefix` title-sorted parent a dated title → child's `bucket_key` is the title date, `AlbumSaved` dispatched for it); then `RenameAlbums` dispatches `AlbumSaved` for albums whose title changed (Q-092-04).  
  _Verification commands:_  
  - `php artisan test --filter=AlbumKeysWriteTest`  
  - `make phpstan`

- [x] T-092-23 – `sorting_photos_order` flushes photo listings (F-092-13, S-092-20).  
  _Intent:_ test first in `BucketSettingsV3Test` (change → `PhotoListingCacheFlushRequested`, no all-row job); then add the key to `SettingsController::PHOTO_LISTING_COARSE_FLUSH_CONFIGS` (Q-092-07).  
  _Verification commands:_  
  - `php artisan test --filter=BucketSettingsV3Test`  
  - `php artisan test --filter=UpdateSettingsTest`

- [x] T-092-24 – Title date reads `YYYYMMDD` (F-092-02, S-092-21).  
  _Intent:_ test first in `BucketKeysTest` and `TimelineDataTest` (`20240315_123456` → `2024-03-15`, `202403 x` → `2024-01-01`, `20241345 x` and `2023-12-32` → `NULL`); then a separate eight-digit branch in `BucketKeys`, `BucketKeyBackfill`, `TimelineData::parseDateFromTitle()` and `dateScrubber.ts`, each rejecting a non-calendar date (Q-092-08).  
  _Verification commands:_  
  - `php artisan test --filter=BucketKeysTest`  
  - `php artisan test --filter=TimelineDataTest`  
  - `npm run check`  
  - `make phpstan`

- [x] T-092-25 – Instance settings flush every cached listing (F-092-08, F-092-13, S-092-22).  
  _Intent:_ test first in `BucketSettingsV3Test` (warm photo and child-album `buckets` caches, change a `timeline_photo_date_format_*` / `timeline_album_date_format_*` key or `timeline_albums_granularity` → new labels and ids, no job); then add the four photo keys to `SettingsController::PHOTO_LISTING_COARSE_FLUSH_CONFIGS`, the three album keys to `ALBUM_LISTING_COARSE_FLUSH_CONFIGS`, and `albumListingGlobalTag()` to the child `buckets`/`data`/`rights` cache entries of `AlbumChildrenController`.  
  _Verification commands:_  
  - `php artisan test --filter=BucketSettingsV3Test`  
  - `php artisan test --filter=UpdateSettingsTest`  
  - `make phpstan`

- [x] T-092-26 – Ids written for another scheme are their own labels (F-092-08, F-092-13, S-092-23).  
  _Intent:_ test first in `BucketSchemeTest` (a date scheme labels `alph`, `2020-03 Tr` and `2` as is, date-shaped ids keep their formats) and `AlbumRootV3Test` (root sorted by title, alphabetical keys, `title_bucket_mode` switched to `date_prefix` without the job → ids and labels `2019`, `alph`; after the job → `2019`, `unknown`); then `BucketScheme::label()` formats only date-shaped ids (Q-092-09).  
  _Verification commands:_  
  - `php artisan test --filter=BucketSchemeTest`  
  - `php artisan test --filter=AlbumRootV3Test`  
  - `make phpstan`

- [x] T-092-27 – `lychee:recompute-buckets` flushes listings (F-092-15, S-092-17).  
  _Intent:_ test first in `RecomputeBucketsCommandTest` (the command dispatches `AlbumListingCacheFlushRequested` and `PhotoListingCacheFlushRequested`); then `RecomputeBuckets::handle()` dispatches both after the album and `photo_album` writes.  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeBucketsCommandTest`  
  - `make phpstan`

## Notes / TODOs

- FR coverage: FR-01/02 → T-02…04, T-24; FR-03 → T-05, T-06; FR-04 → T-07; FR-05 → T-09…11, T-15; FR-06 → T-13, T-14; FR-07 → T-09, T-10, T-12, T-14; FR-08 → T-09…11, T-15, T-26; FR-09 → T-11; FR-10 → T-04, T-06; FR-11/12 → T-16, T-22; FR-13 → T-08, T-17, T-23, T-25, T-26; FR-14 → T-06…08; FR-15 → T-08, T-27; FR-16 → T-01; FR-17 → T-18; FR-18 → T-19.
- Test classes in `tests/Feature_v2` are updated where they encode the old triggers; no new class is added there.
- Drift gate, benchmark and adjustments: plan.md § Implementation Drift Gate.
