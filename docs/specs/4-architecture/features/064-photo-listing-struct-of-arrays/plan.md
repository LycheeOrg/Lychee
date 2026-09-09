# Feature Plan 064 – Photo Listing Struct-of-Arrays

_Linked specification:_ `docs/specs/4-architecture/features/064-photo-listing-struct-of-arrays/spec.md`
_Status:_ Implemented
_Last updated:_ 2026-09-05

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

Give a client everything it needs to render a virtualized, justified-layout photo grid inside one album — bucket counts for sticky headers/scrollbar sizing, whole-album lightweight render data (aspect ratio being the one field the layout math actually needs, plus a handful of config-gated fields mirroring exactly what the v2 tile conditionally renders), and a bounded, on-demand `details` tier scoped to what's needed when a user is about to open a photo (description/tags/rating/EXIF-lite/watermark-aware size-variant URLs, per FR-064-19) — without ever hydrating Eloquent models or querying at a cost that scales with an album's full photo count (confirmed real-world albums reach five-figure photo counts). Success = all 30 scenarios (S-064-01..30) green, `photo_album.bucket_id` correctly diverges per album for a multi-album-linked photo (NFR-064-06), `details`' asymmetric caps correct (`bucket_id` mode uncapped/no truncation; `photo_ids[]` capped at 300 as input, NFR-064-04), `ratios`+`details` combined field-by-field reconstruct `PhotoResource` (S-064-28), `make phpstan`/`php-cs-fixer` clean, zero `git diff` on any v2 photo-listing file (NG2/NFR-064-08).

## Scope Alignment

- **In scope:** `photo_album.bucket_id` migration + index; `PhotoBucketComputer` service; `RecomputePhotoBucketsJob`/`RecomputeAlbumPhotoBucketsJob`; `lychee:recompute-photo-buckets` command; 3 new REST routes (`buckets`/index-as-`ratios`/`details`) under a new `PhotoChildrenController`; 3 new FormRequests; 3 new V3 Resources; 3 new `App\Actions\Photo\StructOfArrays\*` query actions; `CacheKeyProvider`/`ManagedCachePhotoListingInvalidator` extensions; dispatch-site wiring in `AlbumController::updateAlbum()` (photo-sort-setting change) and `MoveOrDuplicate::do()`/upload pipes (new pivot link).
- **Out of scope:** Any v8 frontend component/store/composable (NG1); v2 photo-listing changes (NG2); `TagAlbum`/`PersonAlbum`/`BaseSmartAlbum` support (NG3); recursive photos (NG4); a standalone `GET /Photo/{id}` endpoint and `next_photo_id`/`previous_photo_id` navigation specifically (NG5, resolved — everything else in `PhotoResource`, including `palette`/`statistics`/`checksum`/size-variant URLs, is now in scope via `ratios`+`details` combined, per Q-064-06); thumbnail/pixel resolution beyond `details`' own size-variant URLs (NG6); a new feature flag (NG7); tag/person filter query params on the new tiers (NG8); a dedicated photo `/rights` endpoint (NG9 — resolved, confirmed not building one).

## Dependencies & Interfaces

- `App\Services\AlbumBucketComputer` — direct structural precedent for `PhotoBucketComputer` (`resolveGranularity()`/`compute()` shape).
- `App\Http\Controllers\Gallery\AlbumListing\AlbumChildrenController` — direct structural precedent for `PhotoChildrenController` (cache-wrapped `buckets()`/`index()`/`rights()`-shaped methods, `Auth::user()` + `ManagedCacheService::rememberIf()` pattern).
- `App\Policies\AlbumPolicy::CAN_ACCESS` / `App\Models\Extensions\FiltersUploadValidation` — reused verbatim for visibility/upload-validation curation (FR-064-12).
- `App\Repositories\PhotoRepository::getPhotosForAlbumPaginated()` — the v2 baseline this feature's query logic must stay visibility-equivalent to (never a superset or subset of what it curates).
- `App\Actions\Photo\MoveOrDuplicate::do()` — existing `PhotoSaved`/`PhotoMoved`/`PhotoDeleted`/`AlbumSaved` dispatch sites this feature's cache invalidation and bucket-recompute-on-link-change hook into.
- `App\Console\Commands\RecomputeAlbumBuckets` — direct structural precedent for the new `lychee:recompute-photo-buckets` command.
- `App\Services\Cache\CacheKeyProvider` / `App\Services\Cache\ManagedCacheService` / `App\Listeners\ManagedCacheAlbumListingInvalidator` — extended with photo-listing-specific key/tag methods and a sibling listener.
- `App\Enum\ColumnSortingPhotoType` / `App\Enum\TimelinePhotoGranularity` / `App\DTO\PhotoSortingCriterion` / `BaseAlbum::getEffectivePhotoSorting()` — the sorting/timeline surface `PhotoBucketComputer` reads.
- `Photo::getAspectRatioAttribute()` (`app/Models/Photo.php:480-491`) — the priority rule FR-064-08's SQL `COALESCE` must reproduce exactly.

## Assumptions & Risks

- **Assumptions:**
  - `timeline_photo_date_format_year/_month/_day/_hour` config keys exist and are populated with sane defaults today (confirmed present in `TimelineData::fromPhoto()`; the `_hour` key's exact default format string is confirmed via the Analysis Gate, T-064-01 — it is consumed by I3's label computation, FR-064-06, not I1/I2).
  - The raw `base_albums` columns `sorting_col`/`sorting_order` (photo sort) and `photo_timeline` are the correct dirty-check targets for FR-064-03(c) — confirmed via migration history; final column names re-verified against the live `BaseAlbumImpl` model via the same Analysis Gate (T-064-01), ahead of I1's `PhotoBucketComputer` and I2's dirty-check wiring, both of which consume them.
  - `photo_album` has no existing `bucket_id`-adjacent column to collide with (confirmed via migration read).
- **Risks / Mitigations:**
  - **Risk:** A photo linked into N albums requires recomputing N pivot rows on every bucket-relevant metadata edit (FR-064-03b) — for a photo linked into unusually many albums this could be a wider write than Feature 061 ever needed. _Mitigation:_ bulk `UPDATE ... WHERE photo_id = ?` (one query, N rows), not N individual writes; in practice N is small (a handful of albums per photo, never album-count-scale).
  - **Risk:** The `details` tier's dual-mode (`bucket_id` vs `photo_ids[]`) scoping (Q-064-01) may be revised once the user weighs in, changing FR-064-09/DO-064-04's shape after implementation has started. _Mitigation:_ implement the request-validation layer (`GetPhotoDetailsRequest`) as an isolated, easily-swappable unit; do not couple the underlying `QueryPhotoDetails` action's SQL to the exact param names.
  - **Risk:** Folding rights into `details` (Q-064-02) may need to be split into a fourth endpoint later if a richer per-photo grant model is requested. _Mitigation:_ keep `QueryPhotoDetails`'s owner-id resolution as an isolated concern within the action, so lifting it into a new `QueryPhotoRights` action later is a pure extraction, not a rewrite.
  - **Risk:** `size_variants.photo_id` is nullable (a size variant can outlive its photo during certain delete-order edge cases) — the 3 ratio `LEFT JOIN`s must tolerate this without producing spurious rows. _Mitigation:_ explicit `AND sv_x.photo_id = photos.id` join condition (not a bare `whereIn`) already excludes orphaned size-variant rows naturally.

## Implementation Drift Gate

At completion, diff these files/trees against their pre-feature state and confirm the only changes are additive: `app/Http/Controllers/Gallery/AlbumPhotosController.php`, `app/Http/Requests/Album/GetAlbumPhotosRequest.php`, `app/Repositories/PhotoRepository.php`, `app/Http/Resources/Collections/PaginatedPhotosResource.php`, `app/Http/Resources/Models/PhotoResource.php`, `routes/api_v2.php`, `resources/js/v8/**` (should be empty — NG1). Record the diff output (or its absence) in this section before marking the feature complete.

**Result (2026-09-05, T-064-29):** `git diff -- app/Http/Controllers/Gallery/AlbumPhotosController.php app/Http/Requests/Album/GetAlbumPhotosRequest.php app/Repositories/PhotoRepository.php app/Http/Resources/Collections/PaginatedPhotosResource.php app/Http/Resources/Models/PhotoResource.php routes/api_v2.php resources/js/v8` — **empty**. None of the 7 paths appear in `git status` either. Drift gate clean.

## Increment Map

1. **I1 – Migrations + `PhotoBucketComputer`**
   - _Goal:_ Add `photo_album.bucket_id` + composite index; seed the two new **photo-specific** `photo_title_bucket_mode`/`photo_title_bucket_prefix_length` configs (FR-064-17 — deliberately not a reuse of Feature 061's album-only config pair); implement bucket-key derivation for the 6 bucketable `ColumnSortingPhotoType` values (`OWNER_ID` excluded, NG11).
   - _Preconditions:_ Analysis Gate (T-064-01) complete — exact `base_albums` column names (`sorting_col`/`sorting_order`/`photo_timeline`) and `timeline_photo_date_format_hour`'s existence/default confirmed against the live schema.
   - _Steps:_ Write `PhotoBucketComputerTest` first (6 bucketable branches + `null`/`"unknown"` cases per FR-064-02, plus a case asserting `OWNER_ID` always returns `null`/never computed per NG11, plus a case asserting the `TITLE` branch reads `photo_title_bucket_mode`/`_prefix_length` and is unaffected by the album-only `title_bucket_mode`/`title_bucket_prefix_length`); the `photo_album.bucket_id` migration; the new config-seed migration (FR-064-17); `App\Services\PhotoBucketComputer::resolveGranularity()`/`compute(Photo, Album)`.
   - _Commands:_ `php artisan migrate`; `php artisan test --filter=PhotoBucketComputerTest`; `make phpstan`.
   - _Exit:_ `PhotoBucketComputerTest` green for all 6 bucketable sort columns plus the `OWNER_ID` exclusion case; both migrations reversible-free (one-way is fine, mirrors precedent) and clean on sqlite/mysql/pgsql.

2. **I2 – Recompute jobs + backfill command**
   - _Goal:_ FR-064-03(a/b/c) triggers + FR-064-04 backfill command.
   - _Preconditions:_ I1 complete.
   - _Steps:_ `RecomputePhotoBucketsJob($photo_id)` (bulk update every linked `photo_album` row); `RecomputeAlbumPhotoBucketsJob($album_id)` (bulk update every row for that album); wire trigger (a) into the photo-upload/`MoveOrDuplicate::do()` link-insert path; wire trigger (b) into whichever existing photo-metadata-save listener/observer already exists (or add one, mirroring `RecomputeAlbumStatsOnPhotoChange`'s pattern); wire trigger (c) into `AlbumController::updateAlbum()`'s existing `wasChanged([...])` dirty-check block, alongside Feature 061's own `album_sorting_col`/`album_sorting_order`/`album_timeline` check; `lychee:recompute-photo-buckets` console command (chunked, one self-join query).
   - _Commands:_ `php artisan test --filter=RecomputePhotoBucketsJobTest`; `--filter=RecomputeAlbumPhotoBucketsJobTest`; `--filter=RecomputePhotoBucketsCommandTest`; `--filter=PhotoSortingBucketDispatchTest`.
   - _Exit:_ S-064-11/17/18/23 all pass; NFR-064-06's two-albums-different-settings regression test passes.

3. **I3 – `buckets` tier (API-064-01)**
   - _Goal:_ `GET /Albums/{album_id}/Photos/buckets`.
   - _Preconditions:_ I1/I2 complete.
   - _Steps:_ `App\Actions\Photo\StructOfArrays\QueryPhotoBuckets`; `App\Http\Resources\V3\PhotoBucketResource` (DO-064-01); `App\Http\Requests\Photo\GetPhotoBucketsRequest`; the `OWNER_ID` no-query short-circuit (FR-064-05, NG11); label computation (FR-064-06, incl. the new `HOUR` tier); route registration.
   - _Commands:_ `php artisan test --filter=PhotoBucketsV3Test`; `make phpstan`.
   - _Exit:_ S-064-01..08/20..22 green.

4. **I4 – `ratios` tier (API-064-02)**
   - _Goal:_ `GET /Albums/{album_id}/Photos`.
   - _Preconditions:_ I3 complete (reuses its visibility-curation helper).
   - _Steps:_ `App\Actions\Photo\StructOfArrays\QueryPhotoRatios` (the 3-way `size_variants` `LEFT JOIN`/`COALESCE` from FR-064-08; ordering per FR-064-07); `App\Http\Resources\V3\PhotoRatioResource` (DO-064-02); `App\Http\Requests\Photo\GetPhotoRatiosRequest`; route registration.
   - _Commands:_ `php artisan test --filter=PhotoRatiosV3Test`; query-count/`EXPLAIN` assertion against the ≥10,000-photo fixture (NFR-064-03).
   - _Exit:_ S-064-09/10/12/30 green; NFR-064-03's query-count assertion passes.

5. **I5 – `details` tier (API-064-03)**
   - _Goal:_ `GET /Albums/{album_id}/Photos/details`.
   - _Preconditions:_ I3/I4 complete.
   - _Steps:_ `App\Http\Requests\Photo\GetPhotoDetailsRequest` (exactly-one-of `bucket_id` (no cap — resolved, Q-064-04) / `photo_ids[]` (300-cap validation at the request layer, 422 above)); `App\Actions\Photo\StructOfArrays\QueryPhotoDetails` (tag aggregation, EXIF-lite/GPS/location fields each gated per FR-064-18, owner_id, watermark-aware size-variant URLs — all 9, nested `SizeVariantsResouce` per FR-064-19 — plus nested `palette`/`ColourPaletteResource` (FR-064-20) and nested `statistics`/`PhotoStatisticsResource` gated by `metrics_enabled` + **per-row** `metrics_access` when `=owner` (FR-064-21 — the one per-row, not per-request, gate in this feature) — full field list resolved via Q-064-06, reconstructs `PhotoResource`); `App\Http\Resources\V3\PhotoDetailResource` (DO-064-03); route registration.
   - _Commands:_ `php artisan test --filter=PhotoDetailsV3Test`.
   - _Exit:_ S-064-13..16/27..29 green.

6. **I6 – Caching + invalidation**
   - _Goal:_ FR-064-14/15.
   - _Preconditions:_ I3/I4/I5 complete.
   - _Steps:_ `CacheKeyProvider::photoBucketsKey()`/`photoRatiosKey()`/`photoDetailsKey()`/`photoListingTag()`; wrap all 3 controller methods in `ManagedCacheService::rememberIf()`; new `ManagedCachePhotoListingInvalidator` listener wired to `PhotoSaved`/`PhotoMoved`/`PhotoDeleted`/the `AlbumSaved`-on-photo-sort-change path from I2.
   - _Commands:_ `php artisan test --filter=CacheKeyProviderTest`; `--filter=ManagedCachePhotoListingInvalidatorTest`.
   - _Exit:_ S-064-19/24 green; NFR-064-05's key-uniqueness matrix test passes.

7. **I7 – Quality gates + documentation**
   - _Goal:_ Final regression, docs, Implementation Drift Gate.
   - _Preconditions:_ I1-I6 complete.
   - _Steps:_ Full targeted regression (`--filter=Photo*V3Test`, `--filter=Album` for the `updateAlbum()` dirty-check addition, `--filter=Cache`); `make phpstan`; `php-cs-fixer fix --dry-run`; update `api-design.md`/`database-schema.md`/`knowledge-map.md`/`roadmap.md`; execute the Implementation Drift Gate diff.
   - _Commands:_ `make phpstan`; `vendor/bin/php-cs-fixer fix --dry-run`; targeted `php artisan test --filter=...` runs (never the full unfiltered suite, per repo convention).
   - _Exit:_ All 29 scenarios green; drift gate empty; docs updated; roadmap entry moved from Active to Completed.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-064-01..08 | I3 | Bucket derivation per sort column. |
| S-064-09/10/12 | I4 | Ratios correlation, ratio fallback, upload-validation curation. |
| S-064-11/17/18/23 | I2 | Bucket recompute triggers + backfill command. |
| S-064-13..16 | I5 | Details scoping. |
| S-064-19/24 | I6 | Caching + invalidation. |
| S-064-20..22 | I3 (shared gate/404/empty-result checks apply to all 3 routes) | Flag gating, unsupported album types, empty results. |
| S-064-25/26 | I4 | Config-gated field omission in `ratios`; raw (non-Carbon) date fields. |
| S-064-27..29 | I5 | `details` overflow/cap behavior, full `PhotoResource` reconstruction parity, per-row `metrics_access=owner` visibility. |
| S-064-30 | I4 | Video real-ratio-over-forced-1 divergence from `getAspectRatioAttribute()` (Q-064-07). |

## Analysis Gate

Pending — run before implementation begins, as tasks.md's `T-064-01`: confirm (a) exact `base_albums` sorting-column names against the live model (consumed by I1's `PhotoBucketComputer` and I2's dirty-check wiring, FR-064-03c), (b) `timeline_photo_date_format_hour`'s existence/default (consumed by I3's label computation, FR-064-06), (c) which existing observer/listener (if any) already fires on `Photo` metadata save, to decide whether FR-064-03(b)'s trigger is new wiring or an extension of an existing one (consumed by I2). This is the single authoritative pass for all three checks — I1's own Preconditions reference back to it rather than re-running it. Record findings here once run.

**Findings (2026-09-05):** (a) confirmed — `base_albums.sorting_col`/`sorting_order`/`photo_timeline` exist exactly as assumed (`app/Models/BaseAlbumImpl.php:110-119,305-320`); `getEffectivePhotoSorting()` at `app/Models/Extensions/BaseAlbum.php:154-157`. (b) confirmed — `timeline_photo_date_format_year/_month/_day/_hour` all exist with sane defaults (`database/migrations/2024_10_30_064336_timeline_options.php`). (c) no single existing observer/listener fires uniformly on all 4 photo-metadata-save write sites (`PhotoController::update()`/`rename()`/`highlight()`, `Rating::do()`) — each dispatches a different (or no) event — so FR-064-03(b)'s trigger was wired as 4 explicit `RecomputePhotoBucketsJob::dispatchIf($photo->wasChanged([...]), $photo->id)` call sites, not an Eloquent observer. No spec/tasks updates were needed — the live schema matched every assumption.

## Exit Criteria

- All 30 Branch & Scenario Matrix rows (S-064-01..30) pass.
- NFR-064-01..08 all verified (query-count/`EXPLAIN` assertions, cache-key-uniqueness matrix, drift-gate diff, phpstan/php-cs-fixer clean).
- All 7 logged questions (Q-064-01..07) are resolved — confirmed in `open-questions.md` and reflected in this spec's normative sections.
- `docs/specs/3-reference/api-design.md`, `database-schema.md`, `docs/specs/4-architecture/knowledge-map.md`, `roadmap.md` updated.

## Follow-ups / Backlog

- Frontend adoption feature (mirrors Feature 063) — virtualized, justified-row-packing photo grid consuming all 3 tiers; will also need a `getPhotoBoxesV3()`-equivalent geometry rewrite of `dragAndSelect.ts`'s photo-selection branch, since photos (unlike uniform-aspect-ratio album tiles) need variable-row-height geometry.
- `GET /Photo/{id}` full-detail endpoint, or `next_photo_id`/`previous_photo_id` navigation support (NG5) — the only `PhotoResource` fields `ratios`+`details` still can't reconstruct, and only because v2 itself never populates them in a listing context either.
- `TagAlbum`/`PersonAlbum` matching-photo support (NG3) — mirrors Feature 061's own same-day follow-up pattern, if requested.
- Tag/person filter query params on the new tiers (NG8), if the frontend adoption feature needs album-photo-filtering parity with v2.
