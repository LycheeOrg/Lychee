# Feature Plan 091 – Bucketing Refactor

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Draft  
_Last updated:_ 2026-10-12

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

Bucket ids and labels always agree with the settings in effect, capture dates are bucketed in the zone the photo was taken in, and no listing computes buckets in PHP from every row (G1–G5). Success signals:

- S-091-01 … S-091-18 covered by green tests (S-091-16 by a browser check).
- NFR-091-01: bucket list of a 60,000-photo album ≤ 45 ms (non-admin) / ≤ 20 ms (admin) on the scratch SQLite benchmark.
- `lychee.d.ts` unchanged after `php artisan typescript:transform` (NFR-091-06).

## Scope Alignment

- **In scope:** stored keys (`photos.taken_key`, `photos.title_date_key`, `photo_album.bucket_key`, `albums.bucket_key`), migrations and backfill, key computation on every write path, read-time scheme for every photo source and the album children/root listings, recompute triggers and cache flushes, `lychee:recompute-buckets`, Timeline cache invalidation, v8 date scrubber day.
- **Out of scope:** hour label default format, scrubber granularity, v2 Timeline endpoints, map geo buckets, capture-local album dates (spec Non-Goals).

## Dependencies & Interfaces

- Features 061/062/064/066 (bucket tiers and their response shapes), Feature 067 (map, untouched), Feature 071 (scrubber, ADR-071-01), Feature 060 (`TitleSplitter`, backfill precedent with `database/migrations/TemporaryModels/`).
- `ManagedCacheService` tags (`CacheKeyProvider`), `ManagedCachePhotoListingInvalidator`, `SettingsController` config-key lists.
- `mavinoo/laravelBatch` (`batch()->update()`) for `photos`/`albums` backfills, already a dependency.

## Assumptions & Risks

- **Assumptions:** `taken_at_orig_tz` is set whenever `taken_at` is (enforced by `DateTimeWithTimezoneCast`); MariaDB ≥ 10.5 / MySQL 8 / PostgreSQL / SQLite ≥ 3.25 support `RENAME COLUMN`; `SUBSTR(x, 1, n)` counts characters on every driver.
- **Risks / Mitigations:**
  - A missed reader of the old column → renaming the column (Q-091-03 A) makes it fail in tests.
  - Collation differences when comparing keys on MySQL/PostgreSQL → date keys are fixed-width digits and hyphens, so range and order agree under any collation; alphabetical windows use prefix equality, not ranges.
  - `ROUND()` returns different types per driver → rating ids are normalised in PHP per group and per row.
  - Large backfills on upgrade → chunked by 1,000 rows with a progress bar (Feature 060 precedent).
  - Existing tests encode the removed triggers (timeline change → recompute) → updated to the new behaviour, not deleted.

## Implementation Drift Gate

Run after all tasks are `[x]`: map each FR/NFR to classes and tests in a table appended here, rerun every touched test class, `make phpstan`, `vendor/bin/php-cs-fixer fix`, `npm run check`, and the scratch benchmark; record results and lessons below.

## Increment Map

1. **I1 – Schema and backfill**
   - _Goal:_ FR-091-16.
   - _Steps:_ migration adding `taken_key` (indexed) and `title_date_key`; migration backfilling them (temporary copies of the key helpers); migration renaming `bucket_id` → `bucket_key` on `photo_album` and `albums` with their composite indexes; migration backfilling every bucket key (self-contained resolution of the effective sort column from `base_albums`/`albums` and `configs`). Verified on a populated scratch SQLite copy (no migration tests, by convention).
   - _Commands:_ `php artisan test --filter=PhotoUploadBucketTest` (migrations run on the test database).
   - _Exit:_ migrations run up and down on SQLite; scratch copy shows backfilled keys.
2. **I2 – Key helpers**
   - _Goal:_ FR-091-01, FR-091-02 as pure functions (`App\Services\BucketKeys`: taken key from a cast `taken_at`, from a raw UTC value plus zone; title date key).
   - _Steps:_ unit tests first (zones, DST, `NULL`, invalid dates), then code.
   - _Commands:_ `php artisan test --filter=BucketKeysTest`.
3. **I3 – Photo key write sites**
   - _Goal:_ FR-091-01, FR-091-02, FR-091-10 (D-091-06).
   - _Steps:_ tests for upload, `PATCH /Photo`, rename, bulk renamer, `lychee:takedate`; assign `taken_key`/`title_date_key` at each site; dispatch `RecomputePhotoBucketsJob` from the bulk renamer and `lychee:takedate`.
   - _Commands:_ `php artisan test --filter=PhotoKeysWriteTest`, `--filter=PhotoUploadBucketTest`.
4. **I4 – Photo bucket keys on write paths**
   - _Goal:_ FR-091-03, FR-091-14 for photos.
   - _Steps:_ update `PhotoBucketComputerTest`, `RecomputePhotoBucketsJobTest`, `RecomputeAlbumPhotoBucketsJobTest` (incl. 1,001 rows), `PhotoUploadBucketTest`; rewrite `PhotoBucketComputer` to compute the key from row values (no Carbon); `SetParent`, `MoveOrDuplicate`, both jobs (chunked).
   - _Commands:_ the four classes above.
5. **I5 – Album bucket keys and all-row jobs**
   - _Goal:_ FR-091-04, FR-091-14, FR-091-15; jobs for FR-091-13.
   - _Steps:_ update `RecomputeAlbumStatsJobBucketTest`, `RecomputeChildAlbumBucketsJobTest`, `RecomputeBucketsCommandTest`; new `RecomputeAllAlbumBucketsJobTest`, `RecomputeAllPhotoBucketsJobTest`; rewrite `AlbumBucketComputer`; `RecomputeAlbumStatsJob`, `RecomputeChildAlbumBucketsJob` (chunked), `RecomputeAllAlbumBucketsJob` (replaces `RecomputeRootAlbumBucketsJob`, Q-091-03), `RecomputeAllPhotoBucketsJob`; the command shares the all-row routines.
   - _Commands:_ the five classes above.
6. **I6 – Read-time scheme**
   - _Goal:_ FR-091-05, FR-091-07, FR-091-08 as one helper (`App\Services\BucketScheme`): prefix length, SQL id expression for a stored key or a photo column, window filter, next id per precision, `NULL`-last ordering, labels by precision.
   - _Steps:_ unit tests first, then code.
   - _Commands:_ `php artisan test --filter=BucketSchemeTest`.
7. **I7 – Regular-album photo tiers**
   - _Goal:_ FR-091-05, FR-091-07, FR-091-09 (S-091-01, S-091-03, S-091-11, S-091-15).
   - _Steps:_ tests in `PhotoBucketsV3Test`, `PhotoRatiosV3Test`, `PhotoDetailsV3Test`; `QueryPhotoBuckets` reads `photo_album` only with the validation subquery; `QueryPhotoRatios`/`QueryPhotoDetails` window and order through the scheme.
   - _Commands:_ the three classes above.
8. **I8a – Timeline on `taken_key`**
   - _Goal:_ FR-091-06 for the Timeline (S-091-04, S-091-07).
   - _Steps:_ tests in `TimelineAlbumTest`; Timeline buckets and windows through the scheme on `taken_key`/`created_at`.
   - _Commands:_ `php artisan test --filter=TimelineAlbumTest`.
9. **I8b – Tag, person and smart albums in SQL**
   - _Goal:_ FR-091-06 (S-091-05, S-091-06); removes the PHP live path (`liveBucketId`, `resolveLiveBucket…`).
   - _Steps:_ tests for a tag album per sort column and a smart album; implement through the scheme.
   - _Commands:_ `php artisan test --filter=PhotoBucketsV3Test`, `--filter=PhotoRatiosV3Test`, `--filter=PhotoDetailsV3Test`.
10. **I9 – Album listings**
    - _Goal:_ FR-091-05, FR-091-08 for albums (S-091-10).
    - _Steps:_ tests in `AlbumBucketsV3Test`, `AlbumChildrenDataV3Test`, `AlbumRootV3Test`; `AlbumChildrenController`, `AlbumRootController`, `QueryChildrenForAlbum`, `BuildAlbumDataResource` through the scheme.
    - _Commands:_ the three classes above.
11. **I10 – Triggers and cache**
    - _Goal:_ FR-091-11, FR-091-12, FR-091-13, FR-091-17 (S-091-02, S-091-08, S-091-09, S-091-18).
    - _Steps:_ update `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest`, `UpdateSettingsTest`, `ManagedCachePhotoListingInvalidatorTest`, `CacheKeyProviderTest`; new Feature_v3 class for settings-driven granularity changes; photo-listing global tag and flush event; `AlbumController`, `SettingsController`; Timeline invalidation from `taken_key`.
    - _Commands:_ the classes above.
12. **I11 – Date scrubber day**
    - _Goal:_ FR-091-18 (S-091-16).
    - _Steps:_ `tileDayKey()` reads `taken_at` in the tile's `taken_at_orig_tz`; browser check.
    - _Commands:_ `npm run format`, `npm run check`.
13. **I12 – Documentation and quality gate**
    - _Goal:_ knowledge map, roadmap, Feature 064/066/071 spec references, NFR benchmark, drift gate.
    - _Commands:_ every class touched above, `vendor/bin/php-cs-fixer fix`, `make phpstan`, `php artisan typescript:transform` (no diff), scratch benchmark.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-091-01 | I7 / T-091-11 | `PhotoBucketsV3Test` |
| S-091-02 | I10 / T-091-17 | new Feature_v3 settings class |
| S-091-03 | I7 / T-091-11 | `PhotoBucketsV3Test` |
| S-091-04 | I3, I8a / T-091-04, T-091-13 | `PhotoKeysWriteTest`, `TimelineAlbumTest` |
| S-091-05 | I8b / T-091-14 | `PhotoBucketsV3Test`, `PhotoRatiosV3Test`, `PhotoDetailsV3Test` |
| S-091-06 | I8b / T-091-14 | idem |
| S-091-07 | I8a / T-091-13 | `TimelineAlbumTest` |
| S-091-08 | I10 / T-091-16 | `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest` |
| S-091-09 | I10 / T-091-17 | `UpdateSettingsTest`, new Feature_v3 settings class |
| S-091-10 | I5, I9, I10 / T-091-08, T-091-15, T-091-16 | album job tests, `AlbumBucketsV3Test`, `AlbumChildrenDataV3Test`, `AlbumRootV3Test` |
| S-091-11 | I7 / T-091-11 | `PhotoBucketsV3Test` |
| S-091-12 | I4, I5 / T-091-06, T-091-08 | 1,001-row cases |
| S-091-13 | I3 / T-091-04 | `PhotoKeysWriteTest` |
| S-091-14 | I2 / T-091-02 | `BucketKeysTest` |
| S-091-15 | I7, I8b / T-091-11, T-091-14 | both directions |
| S-091-16 | I11 / T-091-19 | browser check |
| S-091-17 | I5 / T-091-08 | `RecomputeBucketsCommandTest` |
| S-091-18 | I10 / T-091-18 | `ManagedCachePhotoListingInvalidatorTest` |

## Analysis Gate

Run 2026-10-12 (agent), against spec, plan, tasks and open questions:

1. Specification completeness — pass: goals, FR-091-01 … 18, NFR-091-01 … 06 populated; Q-091-01/02 folded into FRs and ADR-091-01/02; scrubber mock-up included.
2. Open questions review — pass: Q-091-01 … 03 resolved; ADR-091-01/02 exist and are linked.
3. Plan alignment — pass: plan links spec and tasks; success criteria use the spec's IDs.
4. Tasks coverage — pass: every FR maps to a task (table in tasks.md notes); tests precede code in each task; increments ≤ 90 minutes.
5. Working agreements — pass: spec-first, test-first, no new dependency, no hooks (explicit key writes), no Carbon in new code, no new Feature_v2 tests (existing v2 classes are updated only); helpers keep branching in `BucketKeys`/`BucketScheme`; ADR-071-01 reviewed (rail/header agreement kept by FR-091-18).
6. Tooling readiness — pass: commands per increment above.

## Approvals

- 2026-10-12, owner (Q-091-03 A): persistence changes (`photos.taken_key`, `photos.title_date_key`, `photo_album.bucket_id` and `albums.bucket_id` renamed to `bucket_key`, backfill migrations) and removal of `app/Jobs/RecomputeRootAlbumBucketsJob.php` and `tests/Precomputing/CoverSelection/RecomputeRootAlbumBucketsJobTest.php`.

## Exit Criteria

- All tasks `[x]`; every touched test class green; `make phpstan`, `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check` clean.
- `php artisan typescript:transform` leaves `resources/js/lychee.d.ts` unchanged.
- NFR-091-01/02 measured on the scratch benchmark and recorded here.
- Roadmap, knowledge map, `_current-session.md` updated; drift gate report appended.

## Follow-ups / Backlog

- Default `timeline_photo_date_format_hour` (`g:i`) shows neither date nor am/pm.
- Date scrubber granularity follows the timeline granularity (`Todo.todo`).
- MySQL/MariaDB and PostgreSQL behaviour through CI only (no local server).
