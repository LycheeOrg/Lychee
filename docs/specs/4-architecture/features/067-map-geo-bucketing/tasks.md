# Feature 067 Tasks – Map Geo-Bucketing

_Status: Draft (spec/plan/tasks written; implementation not started)_
_Last updated: 2026-09-13_

> Keep this checklist aligned with `plan.md`'s increments. Stage tests before implementation,
> record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions.
> Update `roadmap.md`'s status when all tasks are done.

## Checklist

### I1 – `MapViewport` DTO + request validation

- [ ] T-067-01 – Write `MapViewportTest` covering `north`/`south`/`east`/`west`/`zoom` bounds
  validation, `cellSizeForZoom()` monotonicity (smaller cell at higher zoom), `snapToGrid()`
  idempotency (snapping an already-snapped viewport is a no-op) and outward-only behavior
  (FR-067-01, S-067-13).
  _Intent:_ Failing tests staged before `MapViewport` exists.
  _Verification commands:_ `php artisan test --filter=MapViewportTest` (expect failure/missing
  class).

- [ ] T-067-02 – Implement `App\DTO\MapViewport` (`north`/`south`/`east`/`west`/`zoom`,
  `cellSizeForZoom()`, `snapToGrid()`), Carbon-free (NFR-067-02).
  _Verification commands:_ `php artisan test --filter=MapViewportTest`;
  `vendor/bin/phpstan analyse`; grep touched file for `Carbon`/`DateTime` imports (expect none).

- [ ] T-067-03 – Add `HasMapViewportTrait` (bounds validation rules) and new
  `GetMapBucketsRequest`/`GetMapPhotosRequest`, both also using `HasAbstractAlbumTrait` for
  `album_id`/new `include_sub_albums` (F-067-02).
  _Intent:_ Malformed viewport/zoom params rejected with 422.
  _Verification commands:_ `vendor/bin/phpstan analyse`;
  `php artisan test --filter=MapViewportTest`.

### I2 – `ResolvesMapPhotoSource` + query resolution parity

- [ ] T-067-04 – Write `ResolvesMapPhotoSourceTest` asserting `resolveRootQuery()`/
  `resolveAlbumQuery()` return the exact same candidate photo ids as today's
  `Albums\PositionData::do()`/`Album\PositionData::get()` for identical fixtures, including the
  `include_sub_albums` branch (F-067-03, F-067-04, S-067-02).
  _Intent:_ Failing tests staged before the trait exists.
  _Verification commands:_ `php artisan test --filter=ResolvesMapPhotoSourceTest` (expect
  failure/missing class).

- [ ] T-067-05 – Implement `App\Actions\Map\ResolvesMapPhotoSource::resolveRootQuery()` —
  reproduces `PhotoQueryPolicy::applySearchabilityFilter()` + `hide_nsfw_in_map` + `origin: null`,
  minus eager loads/`->get()` (F-067-03).
  _Verification commands:_ `php artisan test --filter=ResolvesMapPhotoSourceTest`;
  `vendor/bin/phpstan analyse`.

- [ ] T-067-06 – Implement `resolveAlbumQuery()` — reproduces `$album->photos()`/
  `$album->all_photos()` branching on `$includeSubAlbums`, minus eager loads/`->get()`; confirm
  `AlbumPolicy::CAN_ACCESS_MAP` gate unchanged (F-067-04, S-067-03).
  _Verification commands:_ `php artisan test --filter=ResolvesMapPhotoSourceTest`.

### I3 – Grid bucket aggregation (`QueryMapBuckets`)

- [ ] T-067-07 – Write `QueryMapBucketsTest` covering: a simple in-view grid split, an
  antimeridian-crossing viewport (`west=170,east=-170`), negative lat/lng fixtures (southern/western
  hemisphere), an empty-scope fixture (all-empty arrays, not an error), and a large (order of
  thousands) single-region fixture asserting response row count equals distinct-cell count, not
  photo count (F-067-05..08, NFR-067-01, S-067-01, S-067-04, S-067-06).
  _Intent:_ Failing tests staged before `QueryMapBuckets` exists.
  _Verification commands:_ `php artisan test --filter=QueryMapBucketsTest` (expect
  failure/missing class).

- [ ] T-067-08 – Implement `App\Http\Resources\V3\MapBucketResource` (`bucket_ids[]`/`counts[]`/
  `centroid_latitudes[]`/`centroid_longitudes[]`) (DO-067-05).
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [ ] T-067-09 – Implement `App\Actions\Map\QueryMapBuckets::do()`: `snapToGrid()` first,
  antimeridian-aware `WHERE`, portable `GROUP BY FLOOR(latitude/$cell), FLOOR(longitude/$cell)` +
  `COUNT(*)`/`AVG(latitude)`/`AVG(longitude)`, `toBase()`-only, Carbon-free (F-067-05..08,
  NFR-067-01, NFR-067-02).
  _Verification commands:_ `php artisan test --filter=QueryMapBucketsTest`;
  `vendor/bin/phpstan analyse`; grep for `Carbon`/`DateTime` imports (expect none).

### I4 – Leaf-cell photo resolution (`QueryMapPhotos`)

- [ ] T-067-10 – Write `QueryMapPhotosTest` covering: a dense single-cell fixture (`count > 20`)
  asserted entirely absent from the Photos tier's response (no hydration triggered for it), a
  sparse fixture asserted fully present with field-accurate output, and `should_downgrade` parity
  against a viewer without `CAN_ACCESS_FULL_PHOTO` (F-067-09..12, NFR-067-05, S-067-06, S-067-07,
  S-067-09).
  _Intent:_ Failing tests staged before `QueryMapPhotos` exists.
  _Verification commands:_ `php artisan test --filter=QueryMapPhotosTest` (expect
  failure/missing class).

- [ ] T-067-11 – Implement `App\Http\Resources\V3\MapPhotoResource` — `ids[]`/`album_ids[]`/
  `titles[]`/`taken_ats[]` (preformatted via `date_format_sidebar_taken_at`)/`latitudes[]`/
  `longitudes[]`/`thumb_urls[]`/`thumb_urls_2x[]`/`small_urls[]`/`small_urls_2x[]` — no
  tags/rating/palette/statistics (F-067-09, F-067-11).
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [ ] T-067-12 – Implement `App\Actions\Map\QueryMapPhotos::do()`: aggregate pre-pass
  (`HAVING COUNT(*) <= 20`, `toBase()`-only) to resolve leaf cells, then bounded Eloquent hydration
  (`with(['size_variants' => ...whereBetween SMALL2X..THUMB...])`) restricted to those cells;
  `should_downgrade` via `Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, ...)` (F-067-10, F-067-12,
  NFR-067-05).
  _Verification commands:_ `php artisan test --filter=QueryMapPhotosTest`;
  `vendor/bin/phpstan analyse`.

### I5 – Routes, controller, DB index migration

- [ ] T-067-13 – Write `MapListingV3Test` (`tests/Feature_v3/Map/`) covering S-067-01 through
  S-067-13 end-to-end (buckets/Photos/tracks routes, validation 422s, gating 403s).
  _Intent:_ Failing tests staged before routes/controller exist.
  _Verification commands:_ `php artisan test --filter=MapListingV3Test` (expect failure).

- [ ] T-067-14 – Add new controller wiring `GET /api/v3/Map/buckets`, `GET /api/v3/Map/Photos`,
  `GET /api/v3/Map/tracks` (`tracks()` reuses existing `TrackResource` unchanged, `album_id`
  required) (F-067-05, F-067-09, F-067-13).
  _Verification commands:_ `php artisan test --filter=MapListingV3Test`;
  `vendor/bin/phpstan analyse`; `vendor/bin/php-cs-fixer fix --dry-run --diff`.

- [ ] T-067-15 – New migration adding a plain composite index on `photos(latitude, longitude)`,
  no partial clause, no spatial index type (F-067-14, Q-067-03).
  _Verification commands:_ `php artisan migrate`; `php artisan migrate:rollback` (confirm clean
  reversibility); `php artisan migrate` again.

### I6 – Cache wiring + invalidation

- [ ] T-067-16 – Write cache-key-stability test: two overlapping-but-distinct raw viewports inside
  the same snapped grid region produce an identical `mapBucketsKey()`/`mapPhotosKey()` value
  (NFR-067-04, S-067-05).
  _Intent:_ Failing test staged before the key methods exist.
  _Verification commands:_ `php artisan test --filter=CacheKeyProviderTest` (expect
  failure/missing method).

- [ ] T-067-17 – Add `CacheKeyProvider::mapBucketsKey()`/`mapPhotosKey()`/`mapTracksKey()`/
  `mapListingTag()` (scope + snapped-viewport + zoom + unlocked-albums-digest + user id) (F-067-15).
  _Verification commands:_ `php artisan test --filter=CacheKeyProviderTest`;
  `vendor/bin/phpstan analyse`.

- [ ] T-067-18 – Wrap the three controller endpoints in `ManagedCacheService::rememberIf()`, same
  pattern as `PhotoChildrenController` (F-067-15).
  _Verification commands:_ `php artisan test --filter=MapListingV3Test`.

- [ ] T-067-19 – Write invalidation test cases: a save/move to a geotagged photo evicts its
  scope(s)' coarse map-cache tag while an unrelated scope's warm entry survives; a delete evicts the
  same tag(s) (F-067-16, S-067-10, S-067-11).
  _Intent:_ Failing tests staged before the listener branches exist.
  _Verification commands:_ `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`
  (expect failure).

- [ ] T-067-20 – Add map-scope-aware branches to the existing `PhotoSaved`/`PhotoMoved`/
  `PhotoDeleted` listener (root tag always evicted; each containing album's tag evicted if warm)
  (F-067-16).
  _Verification commands:_ `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`.

### I7 – Frontend service + `MapState.ts`

- [ ] T-067-21 – New `resources/js/services/map-v3-service.ts`: `getBuckets()`/`getPhotos()`/
  `getTracks()`, axios-cache-interceptor conventions mirrored from
  `photo-children-v3-service.ts` (F-067-17).
  _Verification commands:_ `npm run check` — pass.

- [ ] T-067-22 – New `resources/js/stores/MapState.ts`: `bucketsV3`/`photosV3`/`tracksV3`,
  `isMapSoaActive` getter (existing `is_struct_of_array_enabled` flag), debounced
  `requestViewport(bounds, zoom)` with dedup of identical/in-flight snapped-viewport requests
  (F-067-18).
  _Verification commands:_ `npm run check` — pass.

- [ ] T-067-23 – Dev-console/code-review verification: panning/zooming triggers correct
  viewport-scoped fetches, no duplicate in-flight requests for an already-loading/identical snapped
  viewport.
  _Intent:_ Sanity check before building the visual layer on top.
  _Verification commands:_ Manual, browser devtools network tab (flag as pending if no dev
  environment available this session — `[[feedback_no_mariadb_mysql_access]]`).

### I8 – `Map.vue` SoA rendering path

- [ ] T-067-24 – Wire `moveend`/`zoomend` Leaflet listeners → `MapState.ts.requestViewport()`
  (debounced) (F-067-19, S-067-14).
  _Verification commands:_ `npm run check` — pass.

- [ ] T-067-25 – Render aggregate (count-badge) markers at cell centroid for cells above the leaf
  threshold; click handler zooms the map in, never fetches members (F-067-20, S-067-15).
  _Verification commands:_ `npm run check` — pass.

- [ ] T-067-26 – Render leaf-cell entries via the existing `clusterFunc()`/`.leaflet-marker-photo`
  marker+popup template, reused byte-for-byte from the v2 path (F-067-21, S-067-16).
  _Verification commands:_ `npm run check` — pass.

- [ ] T-067-27 – Move GPX track loading to a one-time `MapState.ts` fetch (via the new `tracks`
  endpoint), decoupled from viewport-change refetches; `L.GPX` rendering/layer-control/color-palette
  logic otherwise unchanged (F-067-22, S-067-17).
  _Verification commands:_ `npm run check` — pass.

- [ ] T-067-28 – Add `is_struct_of_array_enabled`-driven dispatcher in `Map.vue` between the old
  (`PositionData` fetch + `leaflet.markercluster`) and new (`MapState.ts` + aggregate/leaf markers)
  paths, mirroring `Timeline.vue`'s dispatcher pattern (F-067-23).
  _Verification commands:_ `npm run check` — pass.

- [ ] T-067-29 – Manual browser verification of S-067-14 through S-067-17 (pan/zoom-triggered
  refetch, aggregate-click-zooms, leaf marker visual/popup parity, decoupled track loading).
  _Verification commands:_ Manual. **Flag as PENDING if no browser/dev environment available this
  session (`[[feedback_no_mariadb_mysql_access]]**) — do not mark `[x]` on typecheck-pass alone.

### I9 – Flag gating + v2 coexistence verification

- [ ] T-067-30 – Diff review confirming zero changes to `routes/api_v2.php`'s `/Map`/`/Map::provider`
  entries, `MapController.php`, `App\Actions\Albums\PositionData`, `App\Actions\Album\PositionData`,
  and `Map.vue`'s v2 rendering branch (F-067-23, NFR-067-03, S-067-18).
  _Verification commands:_ `git diff --stat -- routes/api_v2.php app/Http/Controllers/Gallery/MapController.php app/Actions/Albums/PositionData.php app/Actions/Album/PositionData.php` — expect empty.

### I10 – Documentation

- [ ] T-067-31 – Update `docs/specs/3-reference/api-design.md` with the three new
  `/api/v3/Map/...` routes.
  _Verification commands:_ None (docs only).

- [ ] T-067-32 – Update `docs/specs/3-reference/database-schema.md` with the new
  `(latitude, longitude)` composite index on `photos`.
  _Verification commands:_ None (docs only).

- [ ] T-067-33 – Update `docs/specs/4-architecture/knowledge-map.md` with the grid-bucketing
  pattern (`MapViewport`, `cellSizeForZoom()`/`snapToGrid()`, two-pass leaf-cell resolution).
  _Verification commands:_ None (docs only).

- [ ] T-067-34 – Update `docs/specs/3-reference/frontend-gallery.md` with `MapState.ts` and the
  aggregate/leaf marker split.
  _Verification commands:_ None (docs only).

- [ ] T-067-35 – Move Feature 067's `roadmap.md` row to Completed with an accurate status note
  (including any manual-verification gaps still outstanding), mirroring this project's established
  convention.
  _Verification commands:_ None (docs only).

## Notes / TODOs

- S-067-14..17 (pan/zoom refetch, aggregate-click behavior, leaf marker parity, decoupled track
  loading) require a live browser/dev environment this authoring session does not have access to
  (`[[feedback_no_mariadb_mysql_access]]`) — each corresponding task above is explicitly flagged;
  do not mark these `[x]` on typecheck-pass alone, only after real manual verification.
- NFR-067-01's ~100,000-geotagged-photo scale verification (T-067-07's large-fixture case, plus a
  manual end-to-end check once I5 lands) needs a locally-generated fixture — no such fixture is
  committed to the repo; generate one locally per Feature 063/065/066's own precedent for
  uncommitted scale fixtures.
- The Analysis Gate (per `docs/specs/5-operations/analysis-gate-checklist.md`) has not yet been
  run against this spec/plan/tasks trio — run it before starting I1, and record the outcome in
  `plan.md`'s "Analysis Gate" section.
