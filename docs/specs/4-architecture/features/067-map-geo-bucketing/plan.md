# Feature Plan 067 – Map Geo-Bucketing

_Linked specification:_ `docs/specs/4-architecture/features/067-map-geo-bucketing/spec.md`
_Status:_ Draft (spec/plan/tasks written; implementation not started)
_Last updated:_ 2026-09-15

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs
> from `spec.md` where relevant, log any new high- or medium-impact questions in
> [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications
> are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

The Map page stops being the one surface in this codebase where response cost still scales
linearly with total candidate-row count. Success signals: (1) `buckets`/`Photos` response
time/row-count for a given viewport is bounded by cells-in-view, demonstrably not by total
geotagged-photo count, verified against a ~100,000-photo local fixture; (2) visual clustering
behavior (aggregate pins with counts, individual thumbnail markers once zoomed/dense enough) is
preserved from the user's perspective; (3) a new `(latitude, longitude)` index exists and is used
by the new queries; (4) the v2 Map path is provably untouched when the SoA flag is off; (5) GPX
track loading is decoupled from photo fetching without behavior change.

## Scope Alignment

- **In scope:** `MapViewport` DTO; `ResolvesMapPhotoSource` trait (root + album query resolution);
  `QueryMapBuckets`/`QueryMapPhotos` with grid aggregation, antimeridian handling, viewport
  snapping, leaf-threshold hydration; `MapBucketResource`/`MapPhotoResource`; three new v3 routes;
  new DB index migration; cache key/tag wiring + invalidation; frontend service + `MapState.ts`
  store; `Map.vue` SoA rendering path (aggregate vs leaf markers, viewport-driven refetch, decoupled
  track loading); flag gating; documentation updates.
- **Out of scope:** Removing/altering v2 Map; GPX track model/storage changes; a driver-specific
  spatial index; admin-configurable leaf threshold or grid formula; any change to
  `AlbumPolicy::CAN_ACCESS_MAP`/existing Map config key semantics.

## Dependencies & Interfaces

- `App\Actions\Albums\PositionData`/`App\Actions\Album\PositionData` — read as the source of truth
  for exact root/album query semantics to reproduce in `ResolvesMapPhotoSource`; left otherwise
  untouched (v2 fallback).
- `App\Policies\AlbumPolicy::CAN_ACCESS_MAP`, `App\Policies\AlbumPolicy::CAN_ACCESS` (for
  `album_ids[]` resolution, Q-067-15) — reused unchanged. `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` is
  **not** used by this feature at all (Q-067-12: leaf-tier imagery is delegated to the existing
  Asset endpoint, which never performs that check for thumbnail-class variants).
- `App\Services\Cache\CacheKeyProvider`/`ManagedCacheService`/`ManagedCachePhotoListingInvalidator`
  — extended with map-specific key/tag methods and event-listener branches.
- `App\Models\Track`/`App\Http\Resources\Models\TrackResource` — reused unchanged for the new
  tracks endpoint.
- Existing `is_struct_of_array_enabled` flag machinery (Feature 065) — reused, no new flag.
- `[[feedback_avoid_carbon_server_side]]`, `[[feedback_no_hooks_explicit_writes]]`,
  `[[feedback_no_full_test_suite]]`, `[[feedback_no_mariadb_mysql_access]]`,
  `[[feedback_no_defensive_dead_code_docs]]`, `[[feedback_offline_only]]` — all apply throughout.

## Assumptions & Risks

- **Assumptions:** A fixed `cellSizeForZoom()` formula (halving per zoom level) produces visually
  reasonable clustering across the zoom range Leaflet actually exposes (0–19 typically, validated
  to 24 for margin) — to verify manually in I2/I7. `FLOOR()` is available and behaves identically
  (including for negative longitudes, relevant west of the prime meridian / south of the equator)
  across sqlite/mysql/mariadb/pgsql — to verify in I2's test matrix.
- **Risks / Mitigations:**
  - *Risk:* A fixed `LEAF_THRESHOLD=20` may render too many or too few individual markers at once
    depending on real-world photo geographic density. *Mitigation:* documented as a Follow-up to
    make configurable if real usage shows it wrong (Q-067-02) — not solved speculatively now.
  - *Risk:* Grid-snapped viewport overfetch (querying a slightly larger area than the exact
    viewport, per Q-067-05) could matter at very coarse zoom levels (large cells) if that also
    correlates with very dense scopes. *Mitigation:* NFR-067-01's fixture-based verification should
    include a coarse-zoom, dense-scope case specifically, not only a deep-zoom case.
  - *Risk:* No local dev/browser environment available in the authoring session — all
    pan/zoom-triggered fetch behavior, marker rendering, and popup content must be manually
    verified later, not assumed correct from typechecks alone (mirrors Feature 063/065/066's own
    documented gap).
  - *Risk:* Adding a new index to an existing, potentially very large `photos` table could take a
    non-trivial lock/build time on some deployments. *Mitigation:* a plain, standard
    `Schema::table(...)->index(...)` migration, no `CONCURRENTLY`/online-index trick attempted (not
    portably expressible across all four drivers) — documented as a standard operational
    consideration for large existing installs, consistent with how every other migration in this
    codebase is already applied.

## Implementation Drift Gate

Before marking any increment complete, re-read the touched files' current state (not just the diff
being authored) and confirm: (a) no Eloquent model events/mutators were introduced
(`[[feedback_no_hooks_explicit_writes]]`); (b) no Carbon import was introduced in the grid/bbox/
cache-key files specifically (NFR-067-02 — the leaf-tier resource builder is explicitly exempted);
(c) `make phpstan` / `vendor/bin/php-cs-fixer fix --dry-run --diff` / `npm run check` are clean for
every changed file before moving to the next increment, not batched at the end. Record any drift
found and its fix directly in this file's increment entries, not a separate log.

## Increment Map

1. **I1 – `MapViewport` DTO + request validation**
   - _Goal:_ FR-067-01, FR-067-02.
   - _Preconditions:_ None (first code increment).
   - _Steps:_ New `App\DTO\MapViewport` (`north`/`south`/`east`/`west`/`zoom`,
     `cellSizeForZoom(int $zoom): float { return 360.0 / (2 ** $zoom); }` (Q-067-13), `snapToGrid()`
     — all pure, unit-testable). New `HasMapViewportTrait` (bounds validation) reused
     by `GetMapBucketsRequest`/`GetMapPhotosRequest`; both reuse `HasAbstractAlbumTrait` for
     `album_id` only — no `include_sub_albums` request parameter (Q-067-08: stays server-derived
     from `map_include_subalbums`, exactly like `MapController::getData()` today). Tests first: `MapViewportTest` covering bounds validation,
     `cellSizeForZoom()` monotonicity against the pinned formula, `snapToGrid()` idempotency/stability.
   - _Commands:_ `php artisan test --filter=MapViewportTest`, `vendor/bin/phpstan analyse`,
     `vendor/bin/php-cs-fixer fix --dry-run --diff`.
   - _Exit:_ Malformed viewport/zoom params correctly rejected (S-067-13); `MapViewport` pure
     functions fully unit-tested.

2. **I2 – `ResolvesMapPhotoSource` + query resolution parity**
   - _Goal:_ FR-067-03, FR-067-04.
   - _Preconditions:_ I1.
   - _Steps:_ New `App\Actions\Map\ResolvesMapPhotoSource` trait; `resolveRootQuery()` reproduces
     `Albums\PositionData::do()`'s filter exactly (searchability, `hide_nsfw_in_map`, no owner
     restriction); `resolveAlbumQuery()` reproduces `Album\PositionData::get()`'s
     `photos()`/`all_photos()` branch. Tests first: assert row-set parity against the existing v2
     `PositionData` classes for the same fixtures (regression-style, S-067-02).
   - _Commands:_ `php artisan test --filter=ResolvesMapPhotoSourceTest`,
     `vendor/bin/phpstan analyse`.
   - _Exit:_ New query-resolution methods return exactly the same candidate rows as today's v2
     action classes, for both root and album (± sub-albums) scope.

3. **I3 – Grid bucket aggregation (`QueryMapBuckets`)**
   - _Goal:_ FR-067-05, FR-067-06, FR-067-07, FR-067-08, NFR-067-01, NFR-067-02, NFR-067-04.
   - _Preconditions:_ I1, I2.
   - _Steps:_ `QueryMapBuckets::do(Builder $query, MapViewport $viewport): MapBucketResource` —
     `snapToGrid()` first, antimeridian-aware `WHERE`, `GROUP BY FLOOR(latitude/$cell),
     FLOOR(longitude/$cell)` + `COUNT(*)`/`AVG(latitude)`/`AVG(longitude)`, `toBase()`-only. New
     `MapBucketResource`. Tests first (failing): boundary counts, antimeridian fixture, negative
     lat/lng fixture, empty-scope fixture.
   - _Commands:_ `php artisan test --filter=QueryMapBucketsTest`, `vendor/bin/phpstan analyse`.
   - _Exit:_ S-067-01, S-067-04, S-067-06 (aggregate half) pass; query plan reviewed (no PHP loop
     over full candidate set).

4. **I4 – Leaf-cell photo resolution (`QueryMapPhotos`)**
   - _Goal:_ FR-067-09, FR-067-10, FR-067-11, FR-067-12, NFR-067-05.
   - _Preconditions:_ I3 (reuses the same grid/snapping logic).
   - _Steps:_ `QueryMapPhotos::do()` — aggregate pre-pass with `HAVING COUNT(*) <= 20` to resolve
     leaf cells, then a second, still `toBase()`-only pass restricted to those cells joining
     `photo_album` to resolve each photo's `album_ids[i]` (Q-067-15): album scope constrains the
     join to albums within the query's own already-authorized scope (requested album, or its
     `_lft`/`_rgt` subtree when `include_sub_albums`, no extra per-sub-album access check, mirroring
     `all_photos()`); root scope has no such natural album, so it falls back to the first album
     among the photo's containing albums that passes accessibility — resolved entirely in SQL, no
     `Album` hydration: join `photo_album` → `base_albums` → `computed_access_permissions`, apply
     `AlbumQueryPolicy::appendAccessibilityConditions()` (query-builder form of
     `AlbumPolicy::canAccess()`, designed to run against exactly these two joined tables), collapse
     via `GROUP BY photos.id` + `MIN(photo_album.album_id)` — mirroring
     `ResolvesPhotoSource::resolvePhotoQuery()`'s existing `BaseSmartAlbum` branch, which collapses
     the same photo-to-many-albums fan-out via a `whereIn` id-subquery (here `MIN()` instead, since
     the winning album id itself must survive, not just an existence test) (Q-067-15). No
     `size_variants` join at all (Q-067-12: leaf-tier imagery is fetched by the frontend from the
     existing v3 Asset endpoint instead, so no `should_downgrade` computation exists in this tier);
     no Eloquent hydration anywhere in this tier (NFR-067-05). New
     `MapPhotoResource` (thin fields only, per FR-067-11). Tests first: a synthetic dense
     single-cell fixture (`count > 20`) asserted absent from the response entirely (NFR-067-05); a
     sparse fixture asserted fully present and field-accurate; a multi-album-membership fixture
     exercising both scopes' tie-break rules.
   - _Commands:_ `php artisan test --filter=QueryMapPhotosTest`, `vendor/bin/phpstan analyse`.
   - _Exit:_ S-067-06 (leaf half), S-067-07, S-067-09 pass.

5. **I5 – Routes, controller, DB index migration**
   - _Goal:_ FR-067-05, FR-067-09, FR-067-13, FR-067-14.
   - _Preconditions:_ I3, I4.
   - _Steps:_ New controller (e.g. `MapListingController`) wiring `buckets()`/`photos()`/`tracks()`
     to the three new routes (`/api/v3/Map/buckets`, `/api/v3/Map/Photos`, `/api/v3/Map/tracks`);
     `tracks()` reuses existing `TrackResource` unchanged. New migration adding a plain composite
     index on `photos(latitude, longitude)`.
   - _Commands:_ `php artisan test --filter=MapListingV3Test`, `php artisan migrate`,
     `php artisan migrate:rollback` (verify reversibility), `vendor/bin/phpstan analyse`.
   - _Exit:_ All three endpoints reachable end-to-end; migration up/down verified clean; S-067-12,
     S-067-13 pass.

6. **I6 – Cache wiring + invalidation**
   - _Goal:_ FR-067-15, FR-067-16, FR-067-24.
   - _Preconditions:_ I5.
   - _Steps:_ `CacheKeyProvider::mapBucketsKey()`/`mapPhotosKey()`/`mapTracksKey()`/`mapListingTag()`
     (scope + snapped-viewport + zoom + unlocked-albums-digest + user id). Controller wraps each
     endpoint in `ManagedCacheService::rememberIf()`, same pattern as `PhotoChildrenController`.
     `ManagedCachePhotoListingInvalidator` (or a new sibling listener) gains map-scope-aware
     branches on `PhotoSaved`/`PhotoMoved`/`PhotoDeleted`. A new config-change listener (Q-067-14)
     flushes every warm map-cache tag (root's, plus every warm album scope's) when
     `hide_nsfw_in_map`/`map_include_subalbums`/`map_display`/`map_display_public` changes,
     mirroring Q-053-05's precedent. Tests first: cache-key stability under snapping (NFR-067-04);
     scope-tag eviction on save/move/delete, unrelated scope survives (S-067-10, S-067-11); the four
     config keys flush every warm tag (S-067-19).
   - _Commands:_ `php artisan test --filter=MapListingV3Test`,
     `php artisan test --filter=ManagedCachePhotoListingInvalidatorTest`,
     `vendor/bin/phpstan analyse`.
   - _Exit:_ S-067-05, S-067-10, S-067-11, S-067-19 pass.

7. **I7 – Frontend service + `MapState.ts`**
   - _Goal:_ FR-067-17, FR-067-18.
   - _Preconditions:_ I5 (needs the three live endpoints).
   - _Steps:_ New `resources/js/services/map-v3-service.ts` (`getBuckets()`/`getPhotos()`/
     `getTracks()`, axios-cache-interceptor conventions mirrored from `photo-children-v3-service.ts`).
     New `resources/js/stores/MapState.ts`: `bucketsV3`/`photosV3`/`tracksV3`, `isMapSoaActive`
     getter (existing flag), debounced `requestViewport(bounds, zoom)` deduping identical/in-flight
     snapped-viewport requests.
   - _Commands:_ `npm run check`.
   - _Exit:_ Dev-console-verified (where possible)/code-review-verified: `requestViewport()` dedup
     logic skips an already-loading/identical snapped viewport.

8. **I8 – `Map.vue` SoA rendering path**
   - _Goal:_ FR-067-19, FR-067-20, FR-067-21, FR-067-22, FR-067-23.
   - _Preconditions:_ I7.
   - _Steps:_ Wire `moveend`/`zoomend` Leaflet listeners → `MapState.ts.requestViewport()`
     (debounced). Render aggregate markers (count badge at centroid, click → zoom in, no fetch) for
     cells above the leaf threshold; render leaf-cell entries via the existing `clusterFunc()`/
     `.leaflet-marker-photo` marker+popup template, byte-for-byte reused except image `src`
     resolution, which now goes through `ThumbAssetService.acquire()` (Q-067-12) instead of a
     backend-supplied URL, resolved asynchronously per marker. Track loading moved to a
     one-time `MapState.ts` fetch, decoupled from viewport changes. `is_struct_of_array_enabled`-driven
     dispatcher between old (`PositionData` fetch + `leaflet.markercluster`) and new paths, mirroring
     `Timeline.vue`'s own dispatcher pattern.
   - _Commands:_ `npm run check`.
   - _Exit:_ Manual browser verification of S-067-14 through S-067-17 — flagged pending if no
     browser available this session, not claimed done.

9. **I9 – Flag gating + v2 coexistence verification**
   - _Goal:_ FR-067-23, NFR-067-03.
   - _Preconditions:_ I1–I8.
   - _Steps:_ Diff review confirming zero changes to `routes/api_v2.php`'s `/Map`/`/Map::provider`
     entries, `MapController.php`, `App\Actions\Albums\PositionData`, `App\Actions\Album\PositionData`,
     and `Map.vue`'s v2 branch.
   - _Commands:_ `git diff --stat -- routes/api_v2.php app/Http/Controllers/Gallery/MapController.php app/Actions/Albums/PositionData.php app/Actions/Album/PositionData.php` — expect empty; `npm run check`.
   - _Exit:_ Flag off ⇒ provably byte-identical v2 behavior (S-067-18).

10. **I10 – Documentation**
    - _Goal:_ Documentation Deliverables in spec.md.
    - _Preconditions:_ I1–I9 complete.
    - _Steps:_ Update `api-design.md`, `database-schema.md`, `knowledge-map.md`,
      `frontend-gallery.md`; move Feature 067's roadmap.md row to Completed with an accurate status
      note (including any manual-verification gaps still outstanding).
    - _Commands:_ None (docs only).
    - _Exit:_ All four docs updated; roadmap.md reflects true completion status.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-067-01 | I3 | Buckets tier, root scope, scale check. |
| S-067-02 | I2 | Root/album/sub-album query parity. |
| S-067-03 | I2, I5 | `CAN_ACCESS_MAP` gating. |
| S-067-04 | I3 | Antimeridian handling. |
| S-067-05 | I6 | Viewport-snapped cache-key stability. |
| S-067-06 | I3, I4 | Aggregate-vs-leaf split at the threshold boundary. |
| S-067-07 | I4 | Leaf tier completeness for small cells. |
| S-067-08 | I2, I3, I4 | `hide_nsfw_in_map` isolation. |
| S-067-09 | I4 | `album_ids[]` resolves only to viewer-accessible albums; Asset-endpoint delegation (Q-067-12). |
| S-067-10 | I6 | Save/move cache invalidation. |
| S-067-11 | I6 | Delete cache invalidation. |
| S-067-12 | I5 | Tracks endpoint, viewport-independent. |
| S-067-13 | I1, I5 | Request validation, 422s. |
| S-067-14 | I8 | Debounced/deduped viewport refetch. |
| S-067-15 | I8 | Aggregate marker click → zoom, no fetch. |
| S-067-16 | I8 | Leaf marker visual/popup parity. |
| S-067-17 | I8 | Track loading decoupled from viewport. |
| S-067-18 | I9 | v2 coexistence. |
| S-067-19 | I6 | Config-change cache flush (Q-067-14). |

## Analysis Gate

Not yet run. To be completed once I1–I6 (backend) land, before I7 begins, per this project's
established pattern of running the Analysis Gate checklist once the spec/plan have stabilized
against real implementation — record findings here, not in a separate file.

## Exit Criteria

- All 10 increments' exit criteria met.
- `vendor/bin/phpstan analyse` and `vendor/bin/php-cs-fixer fix --dry-run --diff` clean across all
  touched backend files.
- `npm run check` clean across all touched frontend files.
- All new/changed scoped PHPUnit test filters pass; existing `tests/Feature_v2/Map/MapTest.php`
  regression-passes unmodified (NFR-067-03).
- Manual browser verification of S-067-14..17 completed (or explicitly flagged as outstanding, not
  silently assumed) before the feature is marked Complete in `roadmap.md`.
- Manual scale verification of NFR-067-01 against a ~100,000-geotagged-photo local fixture,
  documented with actual measurements (not merely "should be bounded").
- Documentation Deliverables (I10) applied.

## Follow-ups / Backlog

- Consider making `LEAF_THRESHOLD` and/or the `cellSizeForZoom()` formula admin-configurable if
  real deployments show the fixed values need per-instance tuning (Q-067-02).
- Consider a driver-specific spatial index (MySQL `SPATIAL`, PostgreSQL `GiST`) as an
  opt-in/advanced-deployment enhancement if the plain composite B-tree index proves insufficient at
  very large scale on a specific driver (Q-067-03).
- Consider whether the same grid-bucketing mechanism should power a future "map view" entry point
  from within album browsing itself, if one is ever requested — out of scope for this feature,
  which targets only the existing dedicated `/map` page.
