# Feature 091 Tasks – Bucketing Refactor

_Status: Draft_  
_Last updated: 2026-10-12_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

- [x] T-091-01 – Schema and backfill migrations (F-091-16).  
  _Intent:_ add `photos.taken_key` (indexed) and `photos.title_date_key`; backfill them; rename `photo_album.bucket_id` and `albums.bucket_id` to `bucket_key` with their composite indexes; backfill every bucket key in the FR-091-03/04 format. Chunks of 1,000, progress bar, helpers copied under `database/migrations/TemporaryModels/`. Verified up/down on SQLite and on a populated scratch copy.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoUploadBucketTest`  
  - `make phpstan`

- [x] T-091-02 – Key helper tests (F-091-01, F-091-02, S-091-14).  
  _Intent:_ `tests/Unit/Services/BucketKeysTest.php`: taken key from a cast value and from raw UTC plus zone (named zone, fixed offset, DST, `NULL`); title date key (`YYYY`, `YYYY-MM`, `YYYY-MM-DD`, leading spaces, no date, invalid month/day). Red before T-091-03.  
  _Verification commands:_  
  - `php artisan test --filter=BucketKeysTest`

- [x] T-091-03 – Key helpers (F-091-01, F-091-02).  
  _Intent:_ `App\Services\BucketKeys` without Carbon construction (`DateTimeImmutable` for raw values).  
  _Verification commands:_  
  - `php artisan test --filter=BucketKeysTest`  
  - `make phpstan`

- [ ] T-091-04 – Photo key write sites (F-091-01, F-091-02, F-091-10, S-091-04, S-091-13).  
  _Intent:_ tests first (`tests/Feature_v3/Photo/PhotoKeysWriteTest.php` for `PATCH /Photo`, rename, bulk renamer, `lychee:takedate`; `PhotoUploadBucketTest` for upload); then assign the keys in `HydrateMetadata`, `Save`, `PhotoController::update()`/`rename()`, `RenamePhotos`, `Takedate`, and dispatch `RecomputePhotoBucketsJob` from `RenamePhotos` and `Takedate`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoKeysWriteTest`  
  - `php artisan test --filter=PhotoUploadBucketTest`

- [ ] T-091-05 – Photo bucket key computation (F-091-03).  
  _Intent:_ update `PhotoBucketComputerTest` for keys per sort column (no granularity input), then rewrite `PhotoBucketComputer` to compute keys from row values (`taken_key`, raw `created_at`, `title_date_key`, `title_base`, `is_highlighted`, `type`, `rating_avg`), no Carbon.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBucketComputerTest`

- [ ] T-091-06 – Photo write paths and chunking (F-091-03, F-091-10, F-091-14, S-091-12).  
  _Intent:_ update `RecomputePhotoBucketsJobTest`, `RecomputeAlbumPhotoBucketsJobTest` (1,001 links), `PhotoUploadBucketTest`; then `SetParent`, `MoveOrDuplicate`, `RecomputePhotoBucketsJob`, `RecomputeAlbumPhotoBucketsJob` (chunks of 1,000).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputePhotoBucketsJobTest`  
  - `php artisan test --filter=RecomputeAlbumPhotoBucketsJobTest`  
  - `php artisan test --filter=PhotoUploadBucketTest`

- [ ] T-091-07 – Album bucket keys (F-091-04, F-091-14).  
  _Intent:_ update `RecomputeAlbumStatsJobBucketTest`, `RecomputeChildAlbumBucketsJobTest`; rewrite `AlbumBucketComputer`; `RecomputeAlbumStatsJob`, `RecomputeChildAlbumBucketsJob` (chunked).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeAlbumStatsJobBucketTest`  
  - `php artisan test --filter=RecomputeChildAlbumBucketsJobTest`

- [ ] T-091-08 – All-row jobs and command (F-091-13, F-091-14, F-091-15, S-091-10, S-091-12, S-091-17).  
  _Intent:_ tests `RecomputeAllAlbumBucketsJobTest` (children of parents without override included), `RecomputeAllPhotoBucketsJobTest` (1,001 links), `RecomputeBucketsCommandTest` updated; then `RecomputeAllAlbumBucketsJob`, `RecomputeAllPhotoBucketsJob` and `lychee:recompute-buckets` sharing the chunked routines.  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeAllAlbumBucketsJobTest`  
  - `php artisan test --filter=RecomputeAllPhotoBucketsJobTest`  
  - `php artisan test --filter=RecomputeBucketsCommandTest`

- [x] T-091-09 – Read scheme tests (F-091-05, F-091-07, F-091-08).  
  _Intent:_ `tests/Unit/Services/BucketSchemeTest.php`: prefix length per column and granularity, title date cap, alphabetical length, categorical whole key, next id per precision (year/month/day/hour, month and year rollover), labels by precision, `unknown`. Red before T-091-10.  
  _Verification commands:_  
  - `php artisan test --filter=BucketSchemeTest`

- [x] T-091-10 – Read scheme (F-091-05, F-091-07, F-091-08).  
  _Intent:_ `App\Services\BucketScheme`: SQL id expression over a stored key or a photo column, window filter, `NULL`-last ordering, id normalisation, labels.  
  _Verification commands:_  
  - `php artisan test --filter=BucketSchemeTest`  
  - `make phpstan`

- [ ] T-091-11 – Regular-album `buckets` tier (F-091-05, F-091-08, F-091-09, S-091-01, S-091-03, S-091-11, S-091-15).  
  _Intent:_ tests in `PhotoBucketsV3Test` (granularity switch without job, title date at hour, unvalidated photos per viewer, `unknown` last both directions); then `QueryPhotoBuckets` on `photo_album` only with the validation subquery.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBucketsV3Test`

- [ ] T-091-12 – Regular-album `ratios` and `details` windows (F-091-05, F-091-07).  
  _Intent:_ tests in `PhotoRatiosV3Test`, `PhotoDetailsV3Test` (window per id kind, per-row ids, ordering); then `QueryPhotoRatios`, `QueryPhotoDetails` through the scheme.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoRatiosV3Test`  
  - `php artisan test --filter=PhotoDetailsV3Test`

- [ ] T-091-13 – Timeline on `taken_key` (F-091-06, S-091-04, S-091-07).  
  _Intent:_ tests in `TimelineAlbumTest` (capture-local hour buckets, windows, dateless photos excluded); then Timeline buckets/windows through the scheme.  
  _Verification commands:_  
  - `php artisan test --filter=TimelineAlbumTest`

- [ ] T-091-14 – Tag, person and smart albums in SQL (F-091-06, F-091-07, S-091-05, S-091-06, S-091-15).  
  _Intent:_ tests for a tag album per sort column and a smart album (buckets, ratios windows and per-row ids, details windows); then the SQL path through the scheme; remove `liveBucketId()`, `liveTitleBucket()`, `truncateRawDate()` and the `resolveLiveBucket…` helpers.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBucketsV3Test`  
  - `php artisan test --filter=PhotoRatiosV3Test`  
  - `php artisan test --filter=PhotoDetailsV3Test`

- [ ] T-091-15 – Album children and root listings (F-091-05, F-091-08, S-091-10).  
  _Intent:_ tests in `AlbumBucketsV3Test`, `AlbumChildrenDataV3Test`, `AlbumRootV3Test` (prefix ids per granularity without recompute, per-row ids, labels); then `AlbumChildrenController`, `AlbumRootController`, `QueryChildrenForAlbum`, `BuildAlbumDataResource`.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumBucketsV3Test`  
  - `php artisan test --filter=AlbumChildrenDataV3Test`  
  - `php artisan test --filter=AlbumRootV3Test`

- [ ] T-091-16 – Album edit triggers (F-091-11, F-091-12, S-091-08, S-091-10).  
  _Intent:_ update `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest` (job only on sort column change; order/timeline change flushes the listing); then `AlbumController`.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumSortingBucketDispatchTest`  
  - `php artisan test --filter=PhotoSortingBucketDispatchTest`

- [ ] T-091-17 – Settings triggers and photo-listing flush (F-091-13, S-091-02, S-091-09).  
  _Intent:_ tests in `UpdateSettingsTest`, `CacheKeyProviderTest` and a new `tests/Feature_v3/Settings/BucketSettingsV3Test.php` (granularity change visible in an inheriting album without a job; sort/mode changes dispatch the all-row jobs); then the `photo-listing-global` tag on every photo-listing entry, its flush event, `SettingsController` key lists.  
  _Verification commands:_  
  - `php artisan test --filter=UpdateSettingsTest`  
  - `php artisan test --filter=CacheKeyProviderTest`  
  - `php artisan test --filter=BucketSettingsV3Test`

- [ ] T-091-18 – Timeline cache invalidation (F-091-17, S-091-18).  
  _Intent:_ update `ManagedCachePhotoListingInvalidatorTest`; then resolve old/new Timeline buckets from `taken_key`/`created_at` (`PhotoSaved` previous values carry `taken_key`).  
  _Verification commands:_  
  - `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`

- [ ] T-091-19 – Date scrubber capture-local day (F-091-18, S-091-16).  
  _Intent:_ `tileDayKey()` reads `taken_at` in the tile's `taken_at_orig_tz`; browser check on a scratch instance.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [ ] T-091-20 – Documentation.  
  _Intent:_ knowledge map, roadmap, Feature 064/066/071 spec references to FR-091-xx, `_current-session.md`.  
  _Verification commands:_ none.

- [ ] T-091-21 – Quality gate, benchmark, drift gate (NFR-091-01 … 06).  
  _Intent:_ every class above, php-cs-fixer, phpstan, `typescript:transform` without diff, scratch benchmark of the generated queries, drift gate report in plan.md.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`  
  - `php artisan typescript:transform`

## Notes / TODOs

- FR coverage: FR-01/02 → T-02…04; FR-03 → T-05, T-06; FR-04 → T-07; FR-05 → T-09…11, T-15; FR-06 → T-13, T-14; FR-07 → T-09, T-10, T-12, T-14; FR-08 → T-09…11, T-15; FR-09 → T-11; FR-10 → T-04, T-06; FR-11/12 → T-16; FR-13 → T-08, T-17; FR-14 → T-06…08; FR-15 → T-08; FR-16 → T-01; FR-17 → T-18; FR-18 → T-19.
- Test classes in `tests/Feature_v2` are updated where they encode the old triggers; no new class is added there.
