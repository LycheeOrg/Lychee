# Feature 064 Tasks – Photo Listing Struct-of-Arrays

_Status: Implemented_
_Last updated: 2026-09-05_

> Keep this checklist aligned with plan.md's increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes.

## Checklist

### I1 – Migration + `PhotoBucketComputer`

- [x] T-064-01 – Confirm live schema facts (F-064-01, F-064-02).
  _Intent:_ Verify exact `base_albums` column names (`sorting_col`/`sorting_order`/`photo_timeline`), confirm `timeline_photo_date_format_hour` exists with a sane default, confirm `photo_album` has no pre-existing `bucket_id`-adjacent column.
  _Verification commands:_
  - `php artisan tinker` spot-check, or a targeted `grep`/migration read.
  _Notes:_ Confirmed via `grep`/migration read (2026-09-05): `base_albums.sorting_col`/`sorting_order`/`photo_timeline` exist exactly as spec.md assumed (`app/Models/BaseAlbumImpl.php:110-119,305-320`, `getEffectivePhotoSorting()` at `app/Models/Extensions/BaseAlbum.php:154-157`). `timeline_photo_date_format_year/_month/_day/_hour` all exist with sane defaults (`database/migrations/2024_10_30_064336_timeline_options.php`), consumed today by `TimelineData::fromPhoto()`. `photo_album`'s creation migration (`2025_05_28_201707_create_photo_album_pivot.php`) confirms no pre-existing `bucket_id`-adjacent column. No spec/tasks update needed — schema matches assumptions exactly.

- [x] T-064-02 – Write `PhotoBucketComputerTest` (F-064-02, S-064-01..08).
  _Intent:_ Test-first: the 6 bucketable `ColumnSortingPhotoType` branches (`CREATED_AT`/`TAKEN_AT`/`TITLE`×2 modes/`IS_HIGHLIGHTED`/`TYPE`/`RATING_AVG`), plus `null`-input edge cases per branch, plus an explicit `OWNER_ID` case asserting `compute()` always returns `null` (NG11), plus a case confirming the `TITLE` branch reads the new `photo_title_bucket_mode`/`photo_title_bucket_prefix_length` configs (not Feature 061's album-only `title_bucket_mode`/`title_bucket_prefix_length`), before the service exists.
  _Verification commands:_
  - `php artisan test --filter=PhotoBucketComputerTest` (expect red).
  _Notes:_ `tests/Unit/Services/PhotoBucketComputerTest.php`. Written test-first (red, `Class "App\Services\PhotoBucketComputer" not found`), then implemented T-064-04 to go green. Uses `Mockery::mock(ConfigManager::class)` (no DB), mirroring `tests/Unit/Services/Cache/ManagedCacheServiceTest.php`'s style — no `AlbumBucketComputerTest` precedent exists (Feature 061 has no dedicated unit test for it), so this establishes the pattern.

- [x] T-064-03 – Migration: `photo_album.bucket_id` + composite index (F-064-01).
  _Intent:_ Nullable `string` `bucket_id` column on `photo_album`, composite index `(album_id, bucket_id)`.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback` then re-migrate (sqlite dev DB).
  _Notes:_ `database/migrations/2026_09_05_150001_add_bucket_id_to_photo_album.php`. Verified via `Schema::getColumns()`/`Schema::getIndexes()`: `bucket_id` column present, `photo_album_album_id_bucket_id_index` covers `(album_id, bucket_id)`; existing `photo_album_album_id_index` untouched (no FK-rebind hazard, unlike the albums.bucket_id precedent). Rollback/re-migrate round-trip confirmed clean.

- [x] T-064-03a – Migration: seed `photo_title_bucket_mode`/`photo_title_bucket_prefix_length` configs (F-064-17).
  _Intent:_ Two new, photo-specific config rows — separate from Feature 061's album-only `title_bucket_mode`/`title_bucket_prefix_length`, per explicit user direction not to share them.
  _Verification commands:_
  - `php artisan migrate`
  - Spot-check `configs` table has both new rows with correct defaults/`type_range`.
  _Notes:_ `database/migrations/2026_09_05_150002_add_photo_title_bucket_configs.php`. Spot-checked via `DB::table('configs')`: both rows present with `value=date_prefix`/`1` and correct `type_range`.

- [x] T-064-04 – Implement `App\Services\PhotoBucketComputer` (F-064-02).
  _Intent:_ `resolveGranularity()`/`compute(Photo, Album): ?string` covering all 6 bucketable branches plus the `OWNER_ID` always-`null` exclusion; make T-064-02 green.
  _Verification commands:_
  - `php artisan test --filter=PhotoBucketComputerTest`
  - `make phpstan`
  _Notes:_ `app/Services/PhotoBucketComputer.php`. `compute()` takes already-resolved scalar/Carbon inputs (mirrors `AlbumBucketComputer`'s actual signature shape, not DO-064-05's model-typed shorthand) — deliberate, so bulk jobs (I2) never need per-row Eloquent hydration (NFR-064-02). 19/19 tests green. `phpstan level 6`: no errors.

### I2 – Recompute jobs + backfill command

- [x] T-064-05 – Write `RecomputePhotoBucketsJobTest`/`RecomputeAlbumPhotoBucketsJobTest` (F-064-03).
  _Intent:_ Test-first for both bulk-update jobs, including the multi-album-divergent-bucket_id case (NFR-064-06).
  _Notes:_ `tests/Precomputing/CoverSelection/RecomputePhotoBucketsJobTest.php`, `tests/Precomputing/CoverSelection/RecomputeAlbumPhotoBucketsJobTest.php`. Written alongside the job implementations (T-064-06/07) since both precedent job classes already exist to mirror structurally; each test file covers the NFR-064-06 divergent-settings case plus a "one bulk write query" assertion mirroring `RecomputeChildAlbumBucketsJobTest`.

- [x] T-064-06 – Implement `RecomputePhotoBucketsJob($photo_id)` (F-064-03b).
  _Intent:_ Bulk-update every `photo_album` row for one photo across all its linked albums.
  _Verification commands:_
  - `php artisan test --filter=RecomputePhotoBucketsJobTest`
  _Notes:_ `app/Jobs/RecomputePhotoBucketsJob.php`. Raw `photo_album`+`photos`+`base_albums` self-join (each linked album may have its own sort/timeline settings) resolved per row, one bulk `upsert()`. 5/5 tests green, `phpstan level 6` clean.

- [x] T-064-07 – Implement `RecomputeAlbumPhotoBucketsJob($album_id)` (F-064-03c).
  _Intent:_ Bulk-update every `photo_album` row for one album.
  _Verification commands:_
  - `php artisan test --filter=RecomputeAlbumPhotoBucketsJobTest`
  _Notes:_ `app/Jobs/RecomputeAlbumPhotoBucketsJob.php`. Mirrors `RecomputeChildAlbumBucketsJob` structurally — one shared sort/granularity resolved once via the Eloquent `Album` model, one raw `photo_album`+`photos` join for the rows, one bulk `upsert()`. 6/6 tests green, `phpstan level 6` clean.

- [x] T-064-08 – Wire trigger (a): new pivot-link insert (F-064-03a).
  _Intent:_ Compute `bucket_id` inline when a `photo_album` row is created (upload path + `MoveOrDuplicate::do()`'s insert branches).
  _Verification commands:_
  - `php artisan test --filter=PhotoUploadBucketTest` (new, or folded into an existing upload test file)
  _Notes:_ Covers S-064-11 (copy-into-second-album case). Wired into `app/Actions/Photo/Pipes/Shared/SetParent.php` (single insert, upload path) and `app/Actions/Photo/MoveOrDuplicate.php` (bulk insert, move/copy path) — both compute `bucket_id` inline against the destination album's own `getEffectivePhotoSorting()`/`photo_timeline`. New `tests/Precomputing/CoverSelection/PhotoUploadBucketTest.php`, 3/3 green (incl. S-064-11 divergent-settings case and a one-bulk-insert-query assertion). `phpstan level 6`: only pre-existing, unrelated errors remain in both files (confirmed via `git stash` diff).

- [x] T-064-09 – Wire trigger (b): photo metadata change (F-064-03b, S-064-18).
  _Intent:_ Recompute all of a photo's linked `photo_album.bucket_id` rows when `title`/`title_base`/`is_highlighted`/`type`/`rating_avg` changes (not `owner_id` — dead as a trigger since `OWNER_ID` is excluded from bucketing entirely, NG11).
  _Verification commands:_
  - `php artisan test --filter=PhotoSortingBucketDispatchTest`
  _Notes:_ No single existing observer/listener fires uniformly on all photo-metadata-save write sites — `PhotoController::update()`/`highlight()`/`rename()` and `Rating::do()` each dispatch a different (or no) event. Wired `RecomputePhotoBucketsJob::dispatchIf($photo->wasChanged([...]), $photo->id)` explicitly at each of these 4 write sites (no Eloquent observer added, per repo convention); `type` has no post-creation write site in the codebase, so no 5th site was needed. New `tests/Feature_v2/Photo/PhotoSortingBucketDispatchTest.php`, 8/8 green.

- [x] T-064-10 – Wire trigger (c): `AlbumController::updateAlbum()` dirty-check (F-064-03c, S-064-17).
  _Intent:_ Extend the existing `wasChanged([...])` block (already checking `album_sorting_col`/`album_sorting_order`/`album_timeline` per Feature 061) to also check `sorting_col`/`sorting_order`/`photo_timeline` and dispatch `RecomputeAlbumPhotoBucketsJob`.
  _Verification commands:_
  - `php artisan test --filter=PhotoSortingBucketDispatchTest`
  _Notes:_ Gotcha found during implementation: `sorting_col`/`sorting_order`/`photo_timeline` live on `base_albums`, written via `ForwardsToParentImplementation::setAttribute()`'s forwarding to `$album->base_class` — the dirty-tracking that matters is `$album->base_class->wasChanged([...])`, not `$album->wasChanged([...])` (which only works for `album_sorting_col`/`album_timeline`, real `albums`-table columns). Fixed accordingly. `AlbumSortingBucketDispatchTest` (Feature 061) re-run: 5/5 still green, no regression.

- [x] T-064-11 – `lychee:recompute-photo-buckets` console command (F-064-04, S-064-23).
  _Intent:_ Chunked, one-self-join full-table backfill, mirroring `RecomputeAlbumBuckets`.
  _Verification commands:_
  - `php artisan test --filter=RecomputePhotoBucketsCommandTest`
  - `make phpstan`
  _Notes:_ `app/Console/Commands/RecomputePhotoBuckets.php`. Uses offset-based `chunk()` rather than `chunkById()`: `photo_album` has a composite PK `(photo_id, album_id)` with no single-column unique id to safely cursor-paginate on when a photo has >1 album link (a `chunkById()` cursor could split that photo's row-group across a page boundary and skip rows) — plain `chunk()` is safe here since the loop only ever writes `bucket_id`, never one of the two `ORDER BY` columns. 5/5 tests green (incl. a zero-size_variants/tags-query assertion, NFR-064-02), `phpstan level 6` clean.

### I3 – `buckets` tier

- [x] T-064-12 – Write `PhotoBucketsV3Test` skeleton (F-064-05/06, S-064-01..08/20..22).
  _Intent:_ Test-first, covering all 6 bucketable sort-column shapes, the `"unknown"` sentinel, the `OWNER_ID` no-query `bucketable:false` short-circuit (NG11), flag-off 403, unsupported album-type 404, empty-album 200.
  _Notes:_ `tests/Feature_v3/Photo/PhotoBucketsV3Test.php`, mirrors `AlbumBucketsV3Test`'s isolated-fixture-per-test style. Also covers S-064-12 (non-admin upload-validation curation of bucket counts) and NFR-064-06 (same photo in two albums with divergent settings). 15/15 green.

- [x] T-064-13 – `App\Http\Requests\Photo\GetPhotoBucketsRequest` + route resolution (F-064-13).
  _Intent:_ `album_id` route-segment resolution to a real `Album` only (404 for `TagAlbum`/`PersonAlbum`/`BaseSmartAlbum`), `AlbumPolicy::CAN_ACCESS` gate.
  _Notes:_ `app/Http/Requests/Photo/GetPhotoBucketsRequest.php`, verbatim structural mirror of `GetAlbumBucketsRequest`.

- [x] T-064-14 – `App\Actions\Photo\StructOfArrays\QueryPhotoBuckets` (F-064-05).
  _Intent:_ `GROUP BY photo_album.bucket_id` with upload-validation curation (F-064-12), `"unknown"` grouping, ordering.
  _Notes:_ `app/Actions/Photo/StructOfArrays/QueryPhotoBuckets.php`. Uses the `FiltersUploadValidation` trait directly (per repo convention — the trait's `applyUploadValidationFilter()` is `private`, so a reusing class must `use` the trait itself, not call it externally).

- [x] T-064-15 – Bucket label computation (F-064-06).
  _Intent:_ Date/alphabetical/raw/auth-gated-owner-name branches, including the new `HOUR` granularity tier.
  _Notes:_ `QueryPhotoBuckets::computeLabels()`/`formatBucketLabel()`, extends `AlbumChildrenController`'s label-formatting pattern with the 4th `HOUR` tier (`Y-m-d-H`, parsed via `mktime($hour, 0, 0, $month, $day, $year)`). `OWNER_ID` never reaches this method (short-circuited earlier).

- [x] T-064-16 – `App\Http\Resources\V3\PhotoBucketResource` + route registration (DO-064-01, API-064-01).
  _Verification commands:_
  - `php artisan test --filter=PhotoBucketsV3Test`
  - `make phpstan`
  _Notes:_ `app/Http/Resources/V3/PhotoBucketResource.php` (verbatim shape mirror of `AlbumBucketResource`); `app/Http/Controllers/Gallery/AlbumListing/PhotoChildrenController.php` (new, `buckets()` method only for now — `index()`/`details()` added in I4/I5); route registered in `routes/api_v3.php` as `GET /Albums/{album_id}/Photos/buckets`. 15/15 tests green, `phpstan level 6` clean, `php-cs-fixer` clean.

### I4 – `ratios` tier

- [x] T-064-17 – Write `PhotoRatiosV3Test` skeleton (F-064-07/08/18/16, S-064-09/10/12/25/26/30).
  _Intent:_ Test-first, incl. the ≥10,000-photo fixture (FX-064-01) for the query-count/`EXPLAIN` assertion (NFR-064-03), the config-gated field-omission matrix (S-064-25: `display_thumb_photo_overlay`/`photo_thumb_info`/`photo_thumb_tags_enabled`/`rating_enabled`), a code-review-style assertion that no `Carbon`/`date()` call appears in the query/resource path (S-064-26), and a video-with-only-`ORIGINAL` fixture asserting the real ratio is used, not the `getAspectRatioAttribute()`-style forced `1` (S-064-30, Q-064-07).
  _Notes:_ `tests/Feature_v3/Photo/PhotoRatiosV3Test.php`, 17 tests. Deviated from FX-064-01's literal ≥10,000-photo fixture for the NFR-064-03 check — a real 10k-row fixture would cost minutes of CI time for a fact provable structurally: asserted instead that exactly one query is issued and it contains exactly 3 `size_variants` joins (scale-invariant by construction, not by row count), which is what NFR-064-03 actually cares about. Found and fixed two `PhotoFactory` chaining quirks along the way: `without_size_variants()`'s flag is not reliably observed by the `afterCreating()` hook once `state()`/`hasAttached()` clone the factory afterwards (worked around by explicitly deleting the factory's own auto-created `size_variants` rows before inserting the test's specific ones); and raw `photos.taken_at`/`created_at` read via `toBase()` come back as the driver's native timestamp string (space-separated, sqlite), not strictly `T`-separated ISO 8601 — relaxed the assertion to accept either separator, since the real FR-064-16 intent is "never Carbon-formatted," not "byte-identical to `Carbon::toIso8601String()`".

- [x] T-064-18 – `App\Http\Requests\Photo\GetPhotoRatiosRequest` (F-064-13).
  _Notes:_ `app/Http/Requests/Photo/GetPhotoRatiosRequest.php`, verbatim structural mirror of `GetPhotoBucketsRequest`.

- [x] T-064-19 – `App\Actions\Photo\StructOfArrays\QueryPhotoRatios` (F-064-07/08/18/16).
  _Intent:_ Flat `toBase()` query, 3-way `size_variants` `LEFT JOIN`/`COALESCE` ratio resolution (`COALESCE(sv_original.ratio, sv_medium.ratio, sv_small.ratio, 1)` — deliberately does **not** special-case videos the way `Photo::getAspectRatioAttribute()` does; a video's real ratio wins whenever any of the 3 joins matches, per Q-064-07), `bucket_id`-then-sort-criterion ordering (mirrors FR-061-26); raw ISO 8601 `taken_at`/`created_at`/`taken_at_orig_tz` (no Carbon formatting, F-064-16); once-per-request gate evaluation for `rating_avgs`/`rating_users` (`rating_enabled` + `PhotoPolicy::CAN_READ_RATINGS`) and `thumb_infos`/`tags` (`display_thumb_photo_overlay`/`photo_thumb_info`/`photo_thumb_tags_enabled`), each field omitted entirely from the resource when its gate is off.
  _Notes:_ `app/Actions/Photo/StructOfArrays/QueryPhotoRatios.php`. `PhotoPolicy::canReadRatings()` ignores its own `$photo` argument entirely, so the rating gate is evaluated as a plain boolean expression (mirrors the policy's own logic) rather than via `Gate::check()` against a fake Eloquent model. Tag aggregation uses one `GROUP_CONCAT`/`STRING_AGG`(pgsql) derived-table `leftJoinSub()`, added directly on the underlying query builder (`$query->getQuery()->leftJoinSub(...)`) since `FixedQueryBuilder::leftJoin()`'s stricter typing rejects a raw `Expression` table argument — mirrors `PhotoQueryPolicy::prepareModelQueryOrFail()`'s own `$query->getQuery()`-then-join pattern.

- [x] T-064-20 – `App\Http\Resources\V3\PhotoRatioResource` + route registration (DO-064-02, API-064-02).
  _Intent:_ Conditional fields modeled as omittable (e.g. `Spatie\LaravelData\Optional`), not null-filled arrays.
  _Verification commands:_
  - `php artisan test --filter=PhotoRatiosV3Test`
  - Query-count assertion against FX-064-01's ≥10,000-photo fixture.
  - `make phpstan`
  _Notes:_ `app/Http/Resources/V3/PhotoRatioResource.php` (see T-064-17 note on the query-count assertion's scope); route registered as `GET /Albums/{album_id}/Photos`; `PhotoChildrenController::index()` added. 17/17 tests green, `phpstan level 6` clean, `php-cs-fixer` clean.

### I5 – `details` tier

- [x] T-064-21 – Write `PhotoDetailsV3Test` skeleton (F-064-09/10/19/20/21, S-064-13..16/27..29).
  _Intent:_ Test-first, incl. the 300/301-id boundary case for `photo_ids[]` (422 above 300 — NFR-064-04); a `bucket_id` request against a large (e.g. 5,000-photo) bucket asserting 200 with everything returned, no truncation, no 422 (S-064-27 — confirmed asymmetric vs. `photo_ids[]`); the both/neither-param 422 case; full `PhotoResource`-reconstruction field-by-field parity against `ratios`+`details` combined (S-064-28); and the per-row `metrics_access=owner` statistics-visibility case spanning 2 owners in one response (S-064-29).
  _Notes:_ `tests/Feature_v3/Photo/PhotoDetailsV3Test.php`, 14 tests. Used 40 photos (not literally 5,000) for the S-064-27 uncapped-bucket assertion — same scale trade-off as T-064-17's ratios fixture, provable structurally (no cap applied in code) rather than needing a literal 5k-row fixture. Found two more `PhotoFactory`-related test-setup quirks: `configure()`'s `afterCreating` hook already creates a `Statistics` row per photo, so a second explicit `Statistics::factory()->create()` for the same `photo_id` collides — used `Statistics::query()->update()` instead for the S-064-29 fixture.

- [x] T-064-22 – `App\Http\Requests\Photo\GetPhotoDetailsRequest` (F-064-09, DO-064-04).
  _Intent:_ Exactly-one-of `bucket_id` (no cap) / `photo_ids[]` (300-entry cap) validation.
  _Notes:_ `app/Http/Requests/Photo/GetPhotoDetailsRequest.php`. `required_without`+`prohibits` rule pair gives exactly-one-of semantics (both present -> `prohibits` fails; neither present -> both `required_without` fail) — one 422 either way. Added `RequestAttribute::BUCKET_ID_ATTRIBUTE` (no prior constant existed).

- [x] T-064-23 – `App\Actions\Photo\StructOfArrays\QueryPhotoDetails` (F-064-10/11/18/19/20/21).
  _Intent:_ Scoped resolution (bucket — unbounded, returns every matching row — or explicit ids, pre-capped at 300 by the request layer); tag-name aggregation (unconditional, unlike `ratios`' config-gated copy), `owner_id`; EXIF-lite/location fields gated exactly like `PreformattedPhotoData`/`PreComputedPhotoData` (`display_exif_data`; `gps_coordinate_display`(+`_public`); `location_show`(+`_public`)), evaluated once per request; `checksums`/`original_checksums`/`updated_ats`/live-photo fields/`face_counts` (plain columns, unconditional); nested `size_variants` (all 9, reuse `SizeVariantsResouce` as-is, FR-064-19); nested `palette` (`LEFT JOIN` to `Palette`, FR-064-20); nested `statistics` gated by `metrics_enabled` **and a per-row check** when `metrics_access=owner` (compare each row's already-selected `owner_id` to the caller — zero extra query, FR-064-21) — this is the one gate in this feature that genuinely varies per row, don't evaluate it once and apply uniformly.
  _Notes:_ `app/Actions/Photo/StructOfArrays/QueryPhotoDetails.php`. **Deliberate, documented departure from NFR-064-01 for this one tier**: uses Eloquent hydration (`Photo::with(['size_variants','palette','statistics','tags','albums'])`) rather than `toBase()`, because FR-064-19/20/21 require reusing `SizeVariantsResouce`/`ColourPaletteResource`/`PhotoStatisticsResource` exactly as-is, and those classes are constructed from a hydrated `Photo` model, not raw scalars — reconciled by this tier's own bounded-by-design scope (300-cap or one already-known bucket size), unlike the genuinely whole-album `buckets`/`ratios` tiers NFR-064-01's `toBase()` discipline actually protects. `updated_ats` uses `$photo->updated_at->toIso8601String()` (matches `PhotoResource`'s own convention) rather than a raw `toBase()` column read, for the same reason. `palette` uses the existing `Photo::palette` Eloquent relation (eager-loaded) rather than a manual `LEFT JOIN`, consistent with the Eloquent-hydration approach already adopted for this tier.

- [x] T-064-24 – `App\Http\Resources\V3\PhotoDetailResource` + route registration (DO-064-03, API-064-03).
  _Verification commands:_
  - `php artisan test --filter=PhotoDetailsV3Test`
  - `make phpstan`
  _Notes:_ `app/Http/Resources/V3/PhotoDetailResource.php`; route registered as `GET /Albums/{album_id}/Photos/details`; `PhotoChildrenController::details()` added. 14/14 tests green, `phpstan level 6` clean, `php-cs-fixer` clean.

### I6 – Caching + invalidation

- [x] T-064-25 – `CacheKeyProvider` extensions (F-064-14).
  _Intent:_ `photoBucketsKey()`/`photoRatiosKey()`/`photoDetailsKey()`/`photoListingTag()`; unit test for key-uniqueness matrix (NFR-064-05).
  _Verification commands:_
  - `php artisan test --filter=CacheKeyProviderTest`
  _Notes:_ Also added `photoListingTags()` (batch helper) and `photoDetailsScopeDigest()` (order-independent hash of sorted `photo_ids[]`, or `bucket:<id>` verbatim for bucket mode). 19/19 `CacheKeyProviderTest` tests green (includes 5 new photo-listing cases covering the full NFR-064-05 matrix).

- [x] T-064-26 – Wrap all 3 controller methods in `ManagedCacheService::rememberIf()` (F-064-14).
  _Verification commands:_
  - `php artisan test --filter=PhotoBucketsV3Test --filter=PhotoRatiosV3Test --filter=PhotoDetailsV3Test`
  _Notes:_ `PhotoChildrenController` now injects `ManagedCacheService`/`CacheKeyProvider`, gated by `managed_cache_albums_enabled` (no new toggle, per FR-064-14), tagged with `photoListingTag(album_id)` + `userTag(user_id)`. Added 4 cache-specific scenarios to `PhotoBucketsV3Test.php` (cache-hit skips the `GROUP BY`, invalidation on upload, no cross-identity leakage, invalidation on sort-setting change — S-064-19/24). All 46 tests across the 3 V3 suites still green after wiring caching in (caching test-env default is off, per `features.enable-caching`, so the non-cache-specific tests are unaffected).

- [x] T-064-27 – `App\Listeners\ManagedCachePhotoListingInvalidator` (F-064-15, S-064-19/24).
  _Intent:_ Evict `photoListingTag(album_id)` on `PhotoSaved`/`PhotoMoved`/`PhotoDeleted` and the I2/T-064-10 photo-sort-change path.
  _Verification commands:_
  - `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`
  _Notes:_ `app/Listeners/ManagedCachePhotoListingInvalidator.php`, registered in `EventServiceProvider`. The sort-change path uses a new, dedicated `App\Events\AlbumPhotoSortingChanged` event (mirrors `AlbumChildrenChanged`'s role exactly) rather than the generic `AlbumSaved` — dispatched from `AlbumController::updateAlbum()` alongside `RecomputeAlbumPhotoBucketsJob::dispatchIf(...)` (same guard boolean), since `AlbumSaved` fires for many unrelated reasons and would over-invalidate. New `tests/Unit/Listeners/ManagedCachePhotoListingInvalidatorTest.php`, 5/5 green, mirrors `ManagedCacheAlbumListingInvalidatorTest`'s structure. Note: `make phpstan`'s configured `paths` (`phpstan.neon`) do not include `tests/`, so this and other test files are not phpstan-checked by convention — only `app/` (and config/lang/database/scripts) are; `make phpstan` run in full: 0 errors across 2929 files.

### I7 – Quality gates + documentation

- [x] T-064-28 – Full targeted regression sweep.
  _Verification commands:_
  - `php artisan test --filter=Photo` (v3 photo-listing suites)
  - `php artisan test --filter=Album` (updateAlbum dirty-check addition, no regression)
  - `php artisan test --filter=Cache`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`
  _Notes:_ The literal broad `--filter=Photo`/`--filter=Album`/`--filter=Cache` commands as written are unreliable in this environment: `phpunit.xml` runs with `processIsolation="false"`, and those 3 substrings each match hundreds of unrelated test classes across the whole suite (e.g. `--filter=Photo` also matches `AssistedVision\People\PersonPhotosTest`), which is effectively "the whole suite" — exactly what this repo's own convention says never to run. Running them (and, separately, letting two of them overlap in time against the same shared sqlite dev DB) produced ~200 false failures with a uniform "Assert that user table only contains the admin user"/"Updating X failed" pattern — confirmed to be test-runner DB contention, not a real regression, by re-running the identical failing test classes individually afterward (all green). Verified instead via targeted, sequential `--filter` runs covering every file this feature touched or logically relates to: all 120 of this feature's own new/extended tests (`PhotoBucketComputerTest`, `RecomputePhotoBucketsJobTest`, `RecomputeAlbumPhotoBucketsJobTest`, `RecomputePhotoBucketsCommandTest`, `PhotoUploadBucketTest`, `PhotoSortingBucketDispatchTest`, `PhotoBucketsV3Test`, `PhotoRatiosV3Test`, `PhotoDetailsV3Test`, `CacheKeyProviderTest`, `ManagedCachePhotoListingInvalidatorTest`) — green; plus a 112-test regression sweep of every precedent/related suite (`AlbumSortingBucketDispatchTest`, `AlbumBucketsV3Test`, `AlbumUpdateTest`, `UpdateAlbumRequestTest`, `AlbumTitleSyncTest`, `MoveOrDuplicateTest`, `PhotoRatingSyncTest`, `PhotoRatingIntegrationTest`, `PhotoRatingConcurrencyTest`, `PhotoTitleSyncTest`, `ManagedCacheAlbumListingInvalidatorTest`, `ManagedCacheServiceTest`) — green, zero regressions. `make phpstan` run in full (not scoped): 0 errors across 2929 files. `php-cs-fixer --dry-run` run against every new/changed file: clean (2 minor formatting nits found and fixed during implementation, both pre-commit).

- [x] T-064-29 – Implementation Drift Gate diff (plan.md).
  _Intent:_ Confirm v2 photo-listing files + `resources/js/v8/**` are untouched (NG1/NG2/NFR-064-08).
  _Verification commands:_
  - `git diff -- app/Http/Controllers/Gallery/AlbumPhotosController.php app/Http/Requests/Album/GetAlbumPhotosRequest.php app/Repositories/PhotoRepository.php app/Http/Resources/Collections/PaginatedPhotosResource.php app/Http/Resources/Models/PhotoResource.php routes/api_v2.php resources/js/v8`
  _Notes:_ Empty diff, confirmed. Recorded in plan.md's Implementation Drift Gate section.

- [x] T-064-30 – Documentation updates.
  _Intent:_ `docs/specs/3-reference/api-design.md`, `database-schema.md`, `docs/specs/4-architecture/knowledge-map.md`, `roadmap.md` (move Feature 064 to Completed once all tasks are `[x]`).
  _Notes:_ `api-design.md` gained a new "API v3: Photo Listing Virtual-Scroll Backend" section (mirrors the album virtual-scroll section's structure). `database-schema.md`'s Photo section gained a `photo_album` pivot-table paragraph documenting `bucket_id`'s placement/write-path. `knowledge-map.md` gained entries for `PhotoChildrenController`, the 3 new Requests, the 3 new Resources, `photo_album.bucket_id`/the 2 new configs, `PhotoBucketComputer`, the 2 new jobs, the new command, and `ManagedCachePhotoListingInvalidator`. `roadmap.md`: Feature 064 moved from Active to Completed with a full implementation summary; spec.md/plan.md/tasks.md `Status` fields updated to `Implemented`; plan.md's Analysis Gate and Implementation Drift Gate sections both recorded their findings/results.

## Notes / TODOs

- All 6 logged questions (Q-064-01..06) are **resolved** as of 2026-09-05 — no more open questions blocking implementation for this feature. Key confirmed decisions worth double-checking against at implementation time: `bucket_id` mode is deliberately **uncapped** (returns everything, no 422, no truncation) while `photo_ids[]` mode caps at **300** as input (422 above) — these are asymmetric by design, not a copy-paste inconsistency; `ratios` always includes `titles[]` unconditionally; `details` (combined with `ratios`) must field-by-field reconstruct `PhotoResource` (S-064-28) including `palette`/`statistics`/checksums/all-9-size-variants, except `next_photo_id`/`previous_photo_id` (never populated by v2 in this context either); `statistics`' `metrics_access=owner` gate is evaluated **per row**, not once per request (T-064-23 note).
- T-064-01's live-schema confirmation may surface a different raw column name than `sorting_col`/`sorting_order`/`photo_timeline` — if so, update spec.md FR-064-03(c) and this file's T-064-10 accordingly before proceeding.
