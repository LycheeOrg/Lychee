# Feature Plan 092 – Bucketing Refactor

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Draft  
_Last updated:_ 2026-10-12

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

Bucket ids and labels always agree with the settings in effect, capture dates are bucketed in the zone the photo was taken in, and no listing computes buckets in PHP from every row (G1–G5). Success signals:

- S-092-01 … S-092-18 covered by green tests (S-092-16 by a browser check).
- NFR-092-01: bucket list of a 60,000-photo album ≤ 45 ms (non-admin) / ≤ 20 ms (admin) on the scratch SQLite benchmark.
- `lychee.d.ts` unchanged after `php artisan typescript:transform` (NFR-092-06).

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
  - A missed reader of the old column → renaming the column (Q-092-03 A) makes it fail in tests.
  - Collation differences when comparing keys on MySQL/PostgreSQL → date keys are fixed-width digits and hyphens, so range and order agree under any collation; alphabetical windows use prefix equality, not ranges.
  - `ROUND()` returns different types per driver → rating ids are normalised in PHP per group and per row.
  - Large backfills on upgrade → chunked by 1,000 rows with a progress bar (Feature 060 precedent).
  - Existing tests encode the removed triggers (timeline change → recompute) → updated to the new behaviour, not deleted.

## Implementation Drift Gate

Run after all tasks are `[x]`: map each FR/NFR to classes and tests in a table appended here, rerun every touched test class, `make phpstan`, `vendor/bin/php-cs-fixer fix`, `npm run check`, and the scratch benchmark; record results and lessons below.

### Drift gate report — 2026-10-14

| Requirement | Implementation | Tests |
|-------------|----------------|-------|
| FR-092-01, FR-092-02 | `BucketKeys`; `HydrateMetadata`, `Save`, `PhotoController::update()`/`rename()`, `RenamePhotos`, `Takedate`; `PhotoFactory::configure()` | `BucketKeysTest`, `PhotoKeysWriteTest` |
| FR-092-03 | `PhotoBucketComputer::keyForRow()`/`keyForPhoto()`; `SetParent`, `MoveOrDuplicate` | `PhotoBucketComputerTest`, `PhotoUploadBucketTest`, `RecomputePhotoBucketsJobTest` |
| FR-092-04 | `AlbumBucketComputer::keyForRow()`/`keyForAlbum()`; `RecomputeAlbumStatsJob::computeBucketKey()` | `RecomputeAlbumStatsJobBucketTest`, `RecomputeChildAlbumBucketsJobTest` |
| FR-092-05, FR-092-08 | `BucketScheme::storedIdSql()`/`labels()`; `QueryPhotoBuckets`, `AlbumChildrenController::queryBuckets()`, `AlbumRootController::queryOwnBuckets()`, `QueryChildrenForAlbum`, `BuildAlbumDataResource` | `BucketSchemeTest`, `PhotoBucketsV3Test`, `AlbumBucketsV3Test`, `AlbumRootV3Test` |
| FR-092-06, FR-092-07 | `BucketScheme::sourceIdSql()`/windows/`orderRows()`; `ResolvesPhotoSource::bucketIdSql()`/`applyBucketWindow()`/`orderByBucket()`; `QueryPhotoRatios`, `QueryPhotoDetails` | `PhotoBucketsV3Test` (tag album per sort column, smart album), `PhotoRatiosV3Test`, `PhotoDetailsV3Test`, `TimelineAlbumTest` |
| FR-092-09 | `QueryPhotoBuckets::storedKeyQuery()` | `PhotoBucketsV3Test::testUnvalidatedPhotoCountsPerViewer` |
| FR-092-10 | `RecomputePhotoBucketsJob` dispatch sites incl. `RenamePhotos`, `Takedate` | `PhotoKeysWriteTest`, `PhotoSortingBucketDispatchTest` |
| FR-092-11, FR-092-12 | `AlbumController::effectiveColumnChanged()` | `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest` |
| FR-092-13 | `SettingsController` key lists; `RecomputeAllAlbumBucketsJob`, `RecomputeAllPhotoBucketsJob`; `PhotoListingCacheFlushRequested` + `photo-listing-global` tag | `UpdateSettingsTest`, `BucketSettingsV3Test`, `RecomputeAll*BucketsJobTest`, `CacheKeyProviderTest` |
| FR-092-14, FR-092-15 | `BucketKeyWriter`; `RecomputeBuckets` | `RecomputeAlbumPhotoBucketsJobTest` (1,001 links), `RecomputeAllPhotoBucketsJobTest`, `RecomputeBucketsCommandTest` |
| FR-092-16 | migrations `2026_10_12_00000{1..4}` | migrations run by every test class |
| FR-092-17 | `ManagedCachePhotoListingInvalidator::evictTimelineBucketsFor()`, `BucketScheme::idOfKey()` | `ManagedCachePhotoListingInvalidatorTest` |
| FR-092-18 | `dateScrubber.ts` `tileDayKey()`/`takenDayKeyOf()` | `npm run check`; scratch esbuild check (viewer `TZ=Asia/Tokyo`, offset and named zones) |
| NFR-092-01, 02 | — | scratch benchmark below |
| NFR-092-03, 04, 05 | no PHP per-row bucket path left; no Carbon in new bucket code; no dependency change | code review |
| NFR-092-06 | — | `php artisan typescript:transform`: `resources/js/lychee.d.ts` unchanged |

**Verification.** 27 touched classes plus 42 neighbouring classes in one filtered run: 864 passed, 2 failed (`PhotoAddTest::testAppleLivePhoto1/2`). Both fail identically on `49d77c3c` (master before this branch) in a scratch worktree: this machine has no `exiftool`/`ffmpeg`, so the live-photo content id is never extracted. `vendor/bin/php-cs-fixer fix` clean; `make phpstan` reports only the 46 `lang/*/all_settings.php` duplicate `email_login_enabled` keys inherited from #4871, none in this feature's files; `npm run format`, `npm run check` clean.

**Benchmark (NFR-092-01/02).** Scratch SQLite 3.46.1, 300,000 photos, one 60,000-photo album, 0.2 % unvalidated, production indexes, month granularity, median of 15 runs:

| Query | This machine | Ratio |
|-------|--------------|-------|
| Before: joined, non-admin (month `bucket_id`) | 339.1 ms | — |
| After: `photo_album` only + validation subquery | 81.0 ms | 4.2× faster |
| Before: joined, admin | 199.1 ms | — |
| After: `photo_album` only | 33.0 ms | 6.0× faster |

This machine runs the Q-092-01 baseline queries about 1.9× slower than the owner's (173 / 110 ms there), so the absolute figures are above the NFR's 45 / 20 ms. The NFR asks for 3.8× / 5.5× over that baseline; the measured 4.2× / 6.0× meet it, putting the new queries at about 41 / 18 ms on the reference machine. `EXPLAIN QUERY PLAN` of a `2026-07` window: `SEARCH photo_album USING INDEX photo_album_album_id_bucket_key_index (album_id=? AND bucket_key>? AND bucket_key<?)` (NFR-092-02).

**Adjustments made during implementation** (lightweight, no open question needed):
- Tag, person and search album listings (`BuildAlbumDataResource` without a scheme) return `unknown` per-row `bucket_ids`: they have no parent-governed bucket, and no client reads per-row album ids (`albumBucketBoundaries.ts` reads only the `buckets` tier). Previously they echoed a stored id computed for another parent.
- Tag and person relation queries arrive ordered by the album sort; `ResolvesPhotoSource::orderByBucket()` and `QueryPhotoBuckets` call `reorder()` first. Without it `unknown` rows came first in `ratios` and the grouped query carried a non-grouped `ORDER BY` (rejected by PostgreSQL).
- `SettingsController::setConfigs()` invalidates the config cache before dispatching: on a `sync` queue the all-row jobs otherwise read the previous sort column.
- `lychee:takedate` without `--force` (photos with no `taken_at`) called `equalTo()` on `null`; guarded.
- `BucketScheme::idOfKey()` added for the PHP-side Timeline eviction; `AlbumBucketComputer::scheme()`/`PhotoBucketComputer::scheme()` build the read scheme; `BucketKeys::storedHourKey()`/`storedDayKey()` key model datetimes.

**Lessons.** The tag-album tier-agreement test (whole-scope rows grouped by id must reproduce the `buckets` tier) caught the inherited-ordering bug that per-endpoint tests missed; keep that cross-tier assertion for any new source. Faking the queue hides config-cache ordering bugs; one `sync`-queue test per settings trigger is worth keeping.

### Intent log — 2026-10-14

Prompt: "implement all increments in one go". T-092-04 … T-092-21 implemented in task order, tests first per task (existing classes rewritten for granularity-free keys; new `RecomputeAllAlbumBucketsJobTest`, `RecomputeAllPhotoBucketsJobTest`, `BucketSettingsV3Test`). Commands: `php artisan test --filter=<Class>` per task, then one combined `--filter='(…)'` run; `vendor/bin/php-cs-fixer fix`; `vendor/bin/phpstan analyse`; `npm run format`; `npm run check`; `php artisan typescript:transform`; scratch benchmark `bench.php`/`bench2.php` (session scratchpad, not committed). `RecomputeRootAlbumBucketsJob` and its test removed per the 2026-10-12 approval.

## Increment Map

1. **I1 – Schema and backfill**
   - _Goal:_ FR-092-16.
   - _Steps:_ migration adding `taken_key` (indexed) and `title_date_key`; migration backfilling them (temporary copies of the key helpers); migration renaming `bucket_id` → `bucket_key` on `photo_album` and `albums` with their composite indexes; migration backfilling every bucket key (self-contained resolution of the effective sort column from `base_albums`/`albums` and `configs`). Verified on a populated scratch SQLite copy (no migration tests, by convention).
   - _Commands:_ `php artisan test --filter=PhotoUploadBucketTest` (migrations run on the test database).
   - _Exit:_ migrations run up and down on SQLite; scratch copy shows backfilled keys.
2. **I2 – Key helpers**
   - _Goal:_ FR-092-01, FR-092-02 as pure functions (`App\Services\BucketKeys`: taken key from a cast `taken_at`, from a raw UTC value plus zone; title date key).
   - _Steps:_ unit tests first (zones, DST, `NULL`, invalid dates), then code.
   - _Commands:_ `php artisan test --filter=BucketKeysTest`.
3. **I3 – Photo key write sites**
   - _Goal:_ FR-092-01, FR-092-02, FR-092-10 (D-092-06).
   - _Steps:_ tests for upload, `PATCH /Photo`, rename, bulk renamer, `lychee:takedate`; assign `taken_key`/`title_date_key` at each site; dispatch `RecomputePhotoBucketsJob` from the bulk renamer and `lychee:takedate`.
   - _Commands:_ `php artisan test --filter=PhotoKeysWriteTest`, `--filter=PhotoUploadBucketTest`.
4. **I4 – Photo bucket keys on write paths**
   - _Goal:_ FR-092-03, FR-092-14 for photos.
   - _Steps:_ update `PhotoBucketComputerTest`, `RecomputePhotoBucketsJobTest`, `RecomputeAlbumPhotoBucketsJobTest` (incl. 1,001 rows), `PhotoUploadBucketTest`; rewrite `PhotoBucketComputer` to compute the key from row values (no Carbon); `SetParent`, `MoveOrDuplicate`, both jobs (chunked).
   - _Commands:_ the four classes above.
5. **I5 – Album bucket keys and all-row jobs**
   - _Goal:_ FR-092-04, FR-092-14, FR-092-15; jobs for FR-092-13.
   - _Steps:_ update `RecomputeAlbumStatsJobBucketTest`, `RecomputeChildAlbumBucketsJobTest`, `RecomputeBucketsCommandTest`; new `RecomputeAllAlbumBucketsJobTest`, `RecomputeAllPhotoBucketsJobTest`; rewrite `AlbumBucketComputer`; `RecomputeAlbumStatsJob`, `RecomputeChildAlbumBucketsJob` (chunked), `RecomputeAllAlbumBucketsJob` (replaces `RecomputeRootAlbumBucketsJob`, Q-092-03), `RecomputeAllPhotoBucketsJob`; the command shares the all-row routines.
   - _Commands:_ the five classes above.
6. **I6 – Read-time scheme**
   - _Goal:_ FR-092-05, FR-092-07, FR-092-08 as one helper (`App\Services\BucketScheme`): prefix length, SQL id expression for a stored key or a photo column, window filter, next id per precision, `NULL`-last ordering, labels by precision.
   - _Steps:_ unit tests first, then code.
   - _Commands:_ `php artisan test --filter=BucketSchemeTest`.
7. **I7 – Regular-album photo tiers**
   - _Goal:_ FR-092-05, FR-092-07, FR-092-09 (S-092-01, S-092-03, S-092-11, S-092-15).
   - _Steps:_ tests in `PhotoBucketsV3Test`, `PhotoRatiosV3Test`, `PhotoDetailsV3Test`; `QueryPhotoBuckets` reads `photo_album` only with the validation subquery; `QueryPhotoRatios`/`QueryPhotoDetails` window and order through the scheme.
   - _Commands:_ the three classes above.
8. **I8a – Timeline on `taken_key`**
   - _Goal:_ FR-092-06 for the Timeline (S-092-04, S-092-07).
   - _Steps:_ tests in `TimelineAlbumTest`; Timeline buckets and windows through the scheme on `taken_key`/`created_at`.
   - _Commands:_ `php artisan test --filter=TimelineAlbumTest`.
9. **I8b – Tag, person and smart albums in SQL**
   - _Goal:_ FR-092-06 (S-092-05, S-092-06); removes the PHP live path (`liveBucketId`, `resolveLiveBucket…`).
   - _Steps:_ tests for a tag album per sort column and a smart album; implement through the scheme.
   - _Commands:_ `php artisan test --filter=PhotoBucketsV3Test`, `--filter=PhotoRatiosV3Test`, `--filter=PhotoDetailsV3Test`.
10. **I9 – Album listings**
    - _Goal:_ FR-092-05, FR-092-08 for albums (S-092-10).
    - _Steps:_ tests in `AlbumBucketsV3Test`, `AlbumChildrenDataV3Test`, `AlbumRootV3Test`; `AlbumChildrenController`, `AlbumRootController`, `QueryChildrenForAlbum`, `BuildAlbumDataResource` through the scheme.
    - _Commands:_ the three classes above.
11. **I10 – Triggers and cache**
    - _Goal:_ FR-092-11, FR-092-12, FR-092-13, FR-092-17 (S-092-02, S-092-08, S-092-09, S-092-18).
    - _Steps:_ update `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest`, `UpdateSettingsTest`, `ManagedCachePhotoListingInvalidatorTest`, `CacheKeyProviderTest`; new Feature_v3 class for settings-driven granularity changes; photo-listing global tag and flush event; `AlbumController`, `SettingsController`; Timeline invalidation from `taken_key`.
    - _Commands:_ the classes above.
12. **I11 – Date scrubber day**
    - _Goal:_ FR-092-18 (S-092-16).
    - _Steps:_ `tileDayKey()` reads `taken_at` in the tile's `taken_at_orig_tz`; browser check.
    - _Commands:_ `npm run format`, `npm run check`.
13. **I12 – Documentation and quality gate**
    - _Goal:_ knowledge map, roadmap, Feature 064/066/071 spec references, NFR benchmark, drift gate.
    - _Commands:_ every class touched above, `vendor/bin/php-cs-fixer fix`, `make phpstan`, `php artisan typescript:transform` (no diff), scratch benchmark.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-092-01 | I7 / T-092-11 | `PhotoBucketsV3Test` |
| S-092-02 | I10 / T-092-17 | new Feature_v3 settings class |
| S-092-03 | I7 / T-092-11 | `PhotoBucketsV3Test` |
| S-092-04 | I3, I8a / T-092-04, T-092-13 | `PhotoKeysWriteTest`, `TimelineAlbumTest` |
| S-092-05 | I8b / T-092-14 | `PhotoBucketsV3Test`, `PhotoRatiosV3Test`, `PhotoDetailsV3Test` |
| S-092-06 | I8b / T-092-14 | idem |
| S-092-07 | I8a / T-092-13 | `TimelineAlbumTest` |
| S-092-08 | I10 / T-092-16 | `AlbumSortingBucketDispatchTest`, `PhotoSortingBucketDispatchTest` |
| S-092-09 | I10 / T-092-17 | `UpdateSettingsTest`, new Feature_v3 settings class |
| S-092-10 | I5, I9, I10 / T-092-08, T-092-15, T-092-16 | album job tests, `AlbumBucketsV3Test`, `AlbumChildrenDataV3Test`, `AlbumRootV3Test` |
| S-092-11 | I7 / T-092-11 | `PhotoBucketsV3Test` |
| S-092-12 | I4, I5 / T-092-06, T-092-08 | 1,001-row cases |
| S-092-13 | I3 / T-092-04 | `PhotoKeysWriteTest` |
| S-092-14 | I2 / T-092-02 | `BucketKeysTest` |
| S-092-15 | I7, I8b / T-092-11, T-092-14 | both directions |
| S-092-16 | I11 / T-092-19 | browser check |
| S-092-17 | I5 / T-092-08 | `RecomputeBucketsCommandTest` |
| S-092-18 | I10 / T-092-18 | `ManagedCachePhotoListingInvalidatorTest` |

## Analysis Gate

Run 2026-10-12 (agent), against spec, plan, tasks and open questions:

1. Specification completeness — pass: goals, FR-092-01 … 18, NFR-092-01 … 06 populated; Q-092-01/02 folded into FRs and ADR-092-01/02; scrubber mock-up included.
2. Open questions review — pass: Q-092-01 … 03 resolved; ADR-092-01/02 exist and are linked.
3. Plan alignment — pass: plan links spec and tasks; success criteria use the spec's IDs.
4. Tasks coverage — pass: every FR maps to a task (table in tasks.md notes); tests precede code in each task; increments ≤ 90 minutes.
5. Working agreements — pass: spec-first, test-first, no new dependency, no hooks (explicit key writes), no Carbon in new code, no new Feature_v2 tests (existing v2 classes are updated only); helpers keep branching in `BucketKeys`/`BucketScheme`; ADR-071-01 reviewed (rail/header agreement kept by FR-092-18).
6. Tooling readiness — pass: commands per increment above.

## Approvals

- 2026-10-12, owner (Q-092-03 A): persistence changes (`photos.taken_key`, `photos.title_date_key`, `photo_album.bucket_id` and `albums.bucket_id` renamed to `bucket_key`, backfill migrations) and removal of `app/Jobs/RecomputeRootAlbumBucketsJob.php` and `tests/Precomputing/CoverSelection/RecomputeRootAlbumBucketsJobTest.php`.

## Exit Criteria

- All tasks `[x]`; every touched test class green; `make phpstan`, `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check` clean.
- `php artisan typescript:transform` leaves `resources/js/lychee.d.ts` unchanged.
- NFR-092-01/02 measured on the scratch benchmark and recorded here.
- Roadmap, knowledge map, `_current-session.md` updated; drift gate report appended.

## Follow-ups / Backlog

- Default `timeline_photo_date_format_hour` (`g:i`) shows neither date nor am/pm.
- Date scrubber granularity follows the timeline granularity (`Todo.todo`).
- MySQL/MariaDB and PostgreSQL behaviour through CI only (no local server).
- Browser check of S-092-16 (scrubber rail day vs header bucket) on a scratch instance — not run in the implementation session.
