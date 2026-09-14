# Feature 067 – Map Geo-Bucketing

| Field | Value |
|-------|-------|
| Status | Draft (spec/plan/tasks written; implementation not started) |
| Last updated | 2026-09-13 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/067-map-geo-bucketing/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/067-map-geo-bucketing/tasks.md` |
| Roadmap entry | #067 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications.

## Overview

The v8 Map page (`/map`, `/map/{albumId}`) still fetches every geotagged photo in scope in one
unbounded request: `App\Actions\Albums\PositionData::do()` (root, cross-library) and
`App\Actions\Album\PositionData::get()` (one album ± sub-albums) both call `->get()` on a query
eager-loading `size_variants`/`statistics`/`palette`/`tags`/`rating` for every row matching
`whereNotNull('latitude')->whereNotNull('longitude')`, with no pagination and no bound on result
size. `Map.vue` then renders every returned photo as a `Leaflet.Photo` marker and lets
`leaflet.markercluster` cluster them **client-side**, entirely in the browser, after the full
payload has already landed. At real-world scale (tens or hundreds of thousands of geotagged
photos) this exhausts PHP memory server-side before a response is ever produced, and floods the
browser with tens of thousands of marker objects even before clustering runs.

This feature brings the Map onto a bucket-tiered API, the same architectural principle already
proven for date/title/rating buckets (Features 062/064/066): query cost bounded by **distinct
bucket count**, never by total candidate-row count. The bucketing key is different in kind —
coordinates are two-dimensional and continuous, and the "distance that looks clustered" depends on
the current map viewport and zoom level, not a static admin-configured granularity — so a new,
purpose-built grid-bucketing mechanism is introduced rather than reusing
`PhotoBucketComputer`/`QueryPhotoBuckets` as-is. Backend and frontend are delivered together, in
one effort (mirroring Feature 066's own Q-066-03 precedent for a single combined doc when the
owner scopes work that way — see Q-067-04 below).

## Goals

- Server-side cost for both the aggregate ("how many geotagged photos, roughly where") and detail
  ("show me these specific photos") tiers is bounded by what is currently visible in the map's
  viewport at its current zoom level, never by the total number of geotagged photos in scope.
- The existing visual behavior — clustered pins with a count badge at low zoom/high density,
  individual photo-thumbnail markers with a popup at high zoom/low density — is preserved, just
  computed server-side instead of client-side after a full fetch.
- Root (cross-library) and per-album (± sub-albums) scopes both work, reusing the exact
  visibility/config semantics `Albums\PositionData`/`Album\PositionData` already implement today
  (`hide_nsfw_in_map`, `map_include_subalbums`, `AlbumPolicy::CAN_ACCESS_MAP`,
  `CAN_ACCESS_FULL_PHOTO`-gated size-variant downgrade) — unchanged, not reinterpreted.
- GPX track loading/rendering for an album is unaffected in behavior, just served by its own small,
  viewport-independent endpoint instead of being bundled into the same payload as photos.
- The v2 `GET /api/Map` endpoint and the existing client-side `leaflet.markercluster` rendering
  path remain fully intact and reachable, coexisting behind the same `is_struct_of_array_enabled`
  flag Feature 065/066 already introduced.
- A composite database index exists on `photos(latitude, longitude)` — today there is none at all
  (verified against every `photos`-table migration), so a bounding-box filter at scale would be a
  full table scan regardless of how well the query itself is written.

## Non-Goals

- Removing or deprecating `GET /api/Map`, `MapController::getData()`, `App\Actions\Albums\PositionData`,
  `App\Actions\Album\PositionData`, or the v2 `leaflet.markercluster` rendering path in `Map.vue` —
  they stay, flag-gated, exactly like Feature 065/066 kept their own v2 paths.
- Any change to GPX track upload, storage, or the `Track`/`TrackResource` models themselves — only
  how (not what) track data is fetched for the map page changes.
- A driver-specific spatial index type (MySQL `SPATIAL INDEX`, PostgreSQL `GiST`/PostGIS) — this
  project supports sqlite/mysql/mariadb/pgsql uniformly, and sqlite has no built-in spatial index
  without the SpatiaLite extension, which cannot be assumed present. A plain composite B-tree index
  is the portable choice (Q-067-03).
- An admin-configurable cluster-to-individual-marker threshold or grid-cell-size formula — both are
  fixed constants in this feature (Q-067-02); making either configurable is a documented Follow-up,
  not built speculatively here.
- Any change to `AlbumPolicy::CAN_ACCESS_MAP`'s access predicate, `hide_nsfw_in_map`,
  `map_include_subalbums`, `grants_full_photo_access`, or any other existing Map-related config
  key's semantics — all reused as-is.
- Perfect geographic accuracy of cluster shape (a fixed-size lat/lng grid is not equal-area near
  the poles, unlike `leaflet.markercluster`'s pixel-space clustering) — accepted as a visual
  trade-off for SQL-portable, cost-bounded server-side aggregation; documented, not silently
  ignored (see NFR-067-06).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-067-01 | A new `App\DTO\MapViewport` value object holds `north`/`south`/`east`/`west` (decimal degrees) + `zoom` (int), shared by every new request/query class. | Constructed once per request from validated input, passed by value. | N/A (validation lives in the request classes, FR-067-02). | N/A. | None. | This feature. |
| FR-067-02 | `GetMapBucketsRequest`/`GetMapPhotosRequest` (sharing a `HasMapViewportTrait`) validate `north`/`south` (`numeric\|between:-90,90`), `east`/`west` (`numeric\|between:-180,180`), `zoom` (`integer\|between:0,24`), plus the existing `album_id` (`sometimes`, `RandomIDRule`) and a new `include_sub_albums` (`sometimes\|boolean`, album scope only) reusing `HasAbstractAlbumTrait`. | Well-formed viewport request resolves to a `MapViewport` + optional `AbstractAlbum`. | Any bound outside its numeric range, or `zoom` outside `[0,24]`, is rejected. | Missing any of `north`/`south`/`east`/`west`/`zoom` → 422. | None. | This feature. |
| FR-067-03 | A new `App\Actions\Map\ResolvesMapPhotoSource` trait's `resolveRootQuery(?User $user): Builder<Photo>` reproduces `Albums\PositionData::do()`'s exact filter — `PhotoQueryPolicy::applySearchabilityFilter()`, `origin: null`, `hide_nsfw_in_map`-driven `include_nsfw` — minus the eager loads and `->get()`. | Root-scope candidate query is byte-identical in *rows matched* to today's `GET /api/Map` (no `album_id`). | N/A (query construction only). | N/A. | None. | Verified against `app/Actions/Albums/PositionData.php:40-63`. |
| FR-067-04 | The same trait's `resolveAlbumQuery(AbstractAlbum $album, bool $includeSubAlbums): Builder<Photo>` reproduces `Album\PositionData::get()`'s exact `$album->photos()`/`$album->all_photos()` branching, minus eager loads and `->get()`. `AlbumPolicy::CAN_ACCESS_MAP` gate is checked exactly as `MapDataRequest` does today, unchanged. | Album-scope candidate query (± sub-albums) matches today's `GET /api/Map?album_id=...` row set exactly. | N/A. | Guest/unauthorized viewer → 403, same gate as today. | None. | Verified against `app/Actions/Album/PositionData.php:19-55`, `app/Policies/AlbumPolicy.php` (`CAN_ACCESS_MAP`). |
| FR-067-05 | `GET /api/v3/Map/buckets` (`MapBucketController::buckets()` or a method on a new `MapController`-sibling) returns a `MapBucketResource` — parallel arrays `bucket_ids[]` (opaque `"{lat_cell}:{lng_cell}"` string), `counts[]`, `centroid_latitudes[]`, `centroid_longitudes[]` — computed via one driver-portable SQL `GROUP BY FLOOR(latitude/$cell), FLOOR(longitude/$cell)` + `COUNT(*)`/`AVG(latitude)`/`AVG(longitude)`, `toBase()`-only (no Eloquent hydration). | Response size and query cost are bounded by the number of distinct grid cells intersecting the (snapped) viewport, never by total geotagged-photo count in scope. | Viewport/zoom validated per FR-067-02. | Empty scope (no geotagged photos in view) → all-empty arrays, not an error. | None. | This feature; NFR-067-01. |
| FR-067-06 | Grid cell size is a pure function of `zoom` only (`cellSizeForZoom(int $zoom): float`, e.g. halving per zoom level) — no admin-configurable grid formula (Q-067-02). | Deeper zoom → smaller cells → finer clustering, with no config to manage. | N/A. | N/A. | None. | This feature. |
| FR-067-07 | The requested bounding box is snapped outward to whole grid cells at the resolved cell size (`MapViewport::snapToGrid(): self`) before it is used for both the SQL `WHERE` and the cache key. | Two viewport requests that fall inside the same snapped region produce byte-identical queries and cache keys. | N/A. | N/A. | None. | This feature; NFR-067-04; Q-067-05. |
| FR-067-08 | The bounding-box `WHERE` clause correctly handles antimeridian-crossing viewports (`west > east`): longitude filter becomes `(longitude >= west OR longitude <= east)` instead of `whereBetween`. | A viewport straddling the ±180° meridian (e.g. Fiji/NZ) returns photos on both sides correctly. | N/A. | N/A. | None. | This feature; Q-067-06. |
| FR-067-09 | `GET /api/v3/Map/Photos` returns a new `MapPhotoResource` — parallel arrays `ids[]`, `album_ids[]`, `titles[]`, `taken_ats[]` (preformatted display strings, reusing `date_format_sidebar_taken_at`), `latitudes[]`, `longitudes[]`, `thumb_urls[]`, `thumb_urls_2x[]`, `small_urls[]`, `small_urls_2x[]` — populated **only** for grid cells whose own aggregate count is `<=` a fixed leaf threshold constant (`QueryMapPhotos::LEAF_THRESHOLD = 20`). | Only photos belonging to already-small (near-unclustered) cells are ever individually hydrated/returned. | Same viewport/zoom validation as `buckets`. | A viewport with every cell above the threshold → empty arrays (no error). | None. | This feature; NFR-067-05. |
| FR-067-10 | `QueryMapPhotos::do()` resolves leaf cells via a first aggregate pass (`GROUP BY` grid cell `HAVING COUNT(*) <= 20`, `toBase()`-only), then a second, bounded Eloquent-hydrating pass (`with(['size_variants' => ...whereBetween SMALL2X..THUMB...])`) restricted to exactly those cells' rows — mirroring `QueryPhotoDetails`'s own established precedent that bounded-scale Eloquent hydration is acceptable when the bound is enforced structurally, not merely assumed. | Hydration cost is bounded by `(leaf cells in viewport) × 20`, never by total scope size. | N/A. | N/A. | None. | This feature; mirrors `app/Actions/Photo/StructOfArrays/QueryPhotoDetails.php`'s documented precedent. |
| FR-067-11 | `MapPhotoResource` is purpose-built, not a reuse of `PhotoResource` — it never eager-loads or exposes `tags`/`rating`/`statistics`/`palette`, none of which the Map marker/popup UI renders (verified against `Map.vue`'s own `MapPhotoEntry` type, which never reads any of them). | Per-photo payload for the leaf tier is materially smaller than today's full `PhotoResource`. | N/A. | N/A. | None. | This feature; verified against `resources/js/v8/views/gallery-panels/Map.vue`. |
| FR-067-12 | `should_downgrade` (`Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, ...)`) gates which size-variant URLs `MapPhotoResource` exposes, identically to today's `PositionData`-driven behavior. | A viewer without full-photo access sees the same size-capped thumbnails as today, no regression. | N/A. | N/A. | None. | Verified against `app/Actions/Album/PositionData.php:51`, `app/Actions/Albums/PositionData.php:70`. |
| FR-067-13 | `GET /api/v3/Map/tracks?album_id=...` returns the album's existing `TrackResource[]` unchanged, independent of viewport/zoom — `album_id` is required here (422 if missing); there is no root-scope equivalent (root has no tracks, matching today's `Albums\PositionData::do()` returning an empty `tracks` collection). | Track list loads once per album context, not re-fetched on pan/zoom. | `album_id` required, `RandomIDRule`. | Missing `album_id` → 422; unauthorized album → 403 via the same `CAN_ACCESS_MAP` gate. | None. | This feature. |
| FR-067-14 | A new migration adds a plain composite index on `photos(latitude, longitude)` — no partial/`WHERE`-conditional clause (not portably expressible across mysql/mariadb/sqlite/pgsql in one migration), no spatial index type (Q-067-03). | Bounding-box `WHERE` + grid `GROUP BY` queries use the index instead of a full table scan. | N/A. | N/A. | None. | This feature; NFR-067-01. |
| FR-067-15 | New `CacheKeyProvider::mapBucketsKey()`/`mapPhotosKey()`/`mapTracksKey()` methods key on scope (root, or `album_id`+`include_sub_albums`), the **snapped** viewport + zoom, the unlocked-albums digest, and user id — mirroring the existing `photoBucketsKey()`/`photoRatiosKey()` pattern. Tagged coarsely per scope (`mapListingTag(scope)`), no fine per-cell tag. | Identical/overlapping requests within the same snapped region hit cache; a save/move/delete anywhere in scope invalidates the whole scope's map cache (coarse), same cost class as today's per-album photo-listing coarse tag. | N/A. | N/A. | None. | This feature; FR-067-07. |
| FR-067-16 | `ManagedCachePhotoListingInvalidator` (or a new sibling listener) reacts to the existing `PhotoSaved`/`PhotoMoved`/`PhotoDeleted` events and evicts the touched photo's scope's coarse map cache tag(s) — root scope's tag always evicted (every photo is always in the root map's candidate set), plus each of the photo's containing albums' tag if album-scoped map caches for those albums are warm. | A location/visibility-affecting edit anywhere invalidates exactly the map caches that could show it, no more. | N/A. | N/A. | None. | This feature. |
| FR-067-17 | New `resources/js/services/map-v3-service.ts` exposes `getBuckets()`/`getPhotos()`/`getTracks()`, mirroring `photo-children-v3-service.ts`'s axios-cache-interceptor conventions. | Frontend has one small, typed service module for the new endpoints. | N/A. | N/A. | None. | This feature. |
| FR-067-18 | A new `resources/js/stores/MapState.ts` Pinia store holds `bucketsV3`, `photosV3`, `tracksV3`, an `isMapSoaActive` getter (same existing `is_struct_of_array_enabled` flag), and a debounced `requestViewport(bounds, zoom)` action that dedupes identical/in-flight snapped-viewport requests. | Panning/zooming triggers at most one in-flight request per distinct snapped viewport. | Gated by `isMapSoaActive`; flag off → store unused. | N/A. | None. | This feature; mirrors `TimelineState.ts`'s dedup pattern (FR-066-11). |
| FR-067-19 | `Map.vue`'s SoA path listens to Leaflet `moveend`/`zoomend`, computes the current bounds+zoom, and calls `MapState.ts.requestViewport()` (debounced) instead of fetching once via `PositionData` on load. | Buckets/leaf-photos refresh as the user pans/zooms, never re-fetching the whole scope. | N/A. | N/A. | None. | This feature. |
| FR-067-20 | Grid cells whose `counts[i]` exceeds the leaf threshold render as a plain aggregate marker (count badge) at `(centroid_latitudes[i], centroid_longitudes[i])`; clicking it zooms the map in (e.g. `map.setView(centroid, zoom+2)`), it does not fetch member photos. | Visual behavior matches today's clustered-pin appearance, without ever downloading the cluster's members. | N/A. | N/A. | None. | This feature. |
| FR-067-21 | Grid cells at/under the leaf threshold render as individual `Leaflet.Photo` thumbnail markers, sourced from `photosV3`, reusing the existing marker/popup template (`clusterFunc()`, the `.leaflet-marker-photo` CSS, the popup HTML) byte-for-byte. | Individual-photo markers look and behave exactly as they do today. | N/A. | N/A. | None. | This feature; verified against `Map.vue`'s existing `open()`/template code. |
| FR-067-22 | GPX tracks are fetched once via `MapState.ts` when an album context is present and `isMapSoaActive`, independent of viewport changes; `L.GPX` layer rendering, the fixed color palette, and the Leaflet layers control are unchanged from today. | Track overlay behavior is unaffected by this feature. | N/A. | N/A. | None. | This feature; verified against `Map.vue`'s existing track-rendering block. |
| FR-067-23 | The v2 Map route, controller, both `PositionData` action classes, and the v2 `leaflet.markercluster` rendering path in `Map.vue` remain fully intact and reachable when the SoA flag is off. | Flag off → v2 Map behavior byte-identical to pre-feature. | N/A. | N/A. | None. | Mirrors Feature 065/066's own coexistence precedent. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-067-01 | Both new query tiers' cost must be bounded by distinct-grid-cells-in-viewport (buckets tier) or leaf-cell-member-count (Photos tier), never by total geotagged-photo count in scope. | Direct motivation for this feature: at ~100,000 geotagged photos, today's unconditional `->get()` exhausts PHP memory before a response is produced. | Manual/scoped timing + row-count check against a large local fixture; query plan review (no PHP loop over the full candidate set in either tier). | FR-067-05, FR-067-09, FR-067-10, FR-067-14 (index). | This feature; direct owner-reported production concern (memory exhaustion at 100k photos). |
| NFR-067-02 | No Carbon usage in the new grid/bbox/cache-key computation (`MapViewport`, `cellSizeForZoom()`, `snapToGrid()`, the antimeridian-aware `WHERE` builder). Reading/formatting an already-hydrated `Photo` model's `taken_at` in the bounded leaf tier (`MapPhotoResource`'s `taken_ats[]`) is unaffected by this constraint — it mirrors `QueryPhotoDetails`'s own existing, accepted use of Carbon-typed model attributes at that tier's bounded scale. | `[[feedback_avoid_carbon_server_side]]` — this project's owner has flagged Carbon as too resource-costly for new server-side arithmetic; the existing codebase already accepts Carbon for simple hydrated-model formatting elsewhere. | Code review / grep for `Carbon`/`DateTime` imports in the new grid/bbox files specifically (not the leaf-tier resource builder). | `[[feedback_avoid_carbon_server_side]]`. | Direct owner instruction (memory); scoped precisely to avoid over-applying it where the codebase's own precedent (`QueryPhotoDetails`) already disagrees. |
| NFR-067-03 | Zero behavior change to the v2 Map path (`GET /api/Map`, `MapController::getData()`, both `PositionData` classes, `Map.vue`'s v2 branch) when `is_struct_of_array_enabled` is off. | Coexistence requirement, mirrors Feature 065/066. | Manual/regression check of `/map` and `/map/{albumId}` with the flag off; diff review of the four v2 files (expect zero changes). | Feature flag machinery already shipped by Feature 065. | Mirrors Feature 065/066's own NFR precedent. |
| NFR-067-04 | Viewport bounds must be snapped to the zoom-derived grid before being used as a cache key; an un-snapped (raw float) cache key would have a near-zero hit rate for ordinary small pans, making the cache layer pointless. | Direct design correction made during spec authoring (see Q-067-05) — a viewport-keyed cache is only worth building if it can actually hit. | Unit test: two overlapping but distinct raw viewports inside the same grid cell produce identical cache keys. | `MapViewport::snapToGrid()`. | This feature. |
| NFR-067-05 | The Photos (leaf) tier's Eloquent hydration must stay bounded by `(leaf cells actually present in the viewport) × LEAF_THRESHOLD`, structurally (via the `HAVING COUNT(*) <= 20` pre-pass), not merely by convention. | Same class of guarantee `QueryPhotoDetails` already relies on for its own bounded Eloquent-hydration tier. | Test asserting a synthetic 10,000-photo single-cell fixture is excluded from the leaf tier's hydration entirely (count > threshold). | FR-067-10. | This feature. |
| NFR-067-06 | The lat/lng grid is not equal-area (cells narrow visually near the poles in unprojected degree-space); this is an accepted, documented trade-off for SQL-portability and cost-boundedness, not a defect to work around in this feature. | Direct trade-off already discussed and accepted with the feature owner before drafting (grid/`FLOOR` vs geohash vs pixel-space clustering). | N/A — documented acceptance, not tested. | Q-067-01. | This feature; owner-accepted trade-off. |

## UI / Interaction Mock-ups

```
Map (flag on, viewport-driven)
┌──────────────────────────────────────────────────────────┐
│                                                            │
│        ○12                                                │
│     (aggregate,                    ○47                    │
│      count badge,                (aggregate)               │
│      click → zoom in)                                     │
│                                                            │
│                    ┌───┐┌───┐                              │
│                    │▓▓▓││▓▓▓│  ← leaf cell (count ≤ 20):   │
│                    └───┘└───┘     individual photo         │
│                       ┌───┐       thumbnail markers,       │
│                       │▓▓▓│       same popup as today      │
│                       └───┘                                │
│                                                            │
└──────────────────────────────────────────────────────────┘
  pan/zoom → debounced requestViewport() re-fetches buckets
  (always) + Photos (leaf cells only) for the new snapped
  viewport; aggregate markers never trigger a photo fetch,
  only a zoom.
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-067-01 | Root scope, ~100,000 geotagged photos in the library: `GET /api/v3/Map/buckets` response time/row count is bounded by distinct cells in the requested viewport, not 100,000. |
| S-067-02 | Album scope, `include_sub_albums=false` vs `true` — matches `Album\PositionData::get()`'s existing distinction exactly. |
| S-067-03 | Guest without `CAN_ACCESS_MAP` on the target album → 403 on both new endpoints. |
| S-067-04 | Viewport crossing the antimeridian (`west=170, east=-170`) returns photos correctly on both sides. |
| S-067-05 | Two overlapping-but-not-identical raw viewport requests that fall inside the same snapped grid region produce an identical cache key (cache hit on the second). |
| S-067-06 | A grid cell with count `> 20` appears in `buckets` (aggregate) but contributes zero entries to `Photos` (leaf tier). |
| S-067-07 | A grid cell with count `<= 20` has every one of its member photos returned by `Photos`, matching `buckets`' count for that cell exactly. |
| S-067-08 | `hide_nsfw_in_map=true` hides NSFW photos from both tiers at root scope, matching today's `PositionData` behavior. |
| S-067-09 | A viewer without `CAN_ACCESS_FULL_PHOTO` receives the same size-capped (`should_downgrade`) thumbnail URLs as today's v2 endpoint. |
| S-067-10 | Editing a geotagged photo's location invalidates its scope's coarse map-cache tag(s); an unrelated scope's warm cache entry survives. |
| S-067-11 | Deleting a geotagged photo invalidates the same coarse tag(s). |
| S-067-12 | `GET /api/v3/Map/tracks?album_id=...` returns the album's tracks, unaffected by viewport/zoom params (ignored if passed). |
| S-067-13 | Missing `album_id` on the tracks endpoint → 422; missing any of `north`/`south`/`east`/`west`/`zoom` on `buckets`/`Photos` → 422. |
| S-067-14 | Frontend: panning/zooming the map triggers `requestViewport()`, debounced, with in-flight/identical-snapped-viewport requests deduped (no duplicate network calls). |
| S-067-15 | Frontend: clicking an aggregate (count-badge) marker zooms the map in; it never triggers a photo fetch for that cluster's members. |
| S-067-16 | Frontend: a leaf-cell photo marker renders with the exact same thumbnail image and popup content (title, camera-date icon, formatted date) as today's v2 rendering. |
| S-067-17 | Frontend: GPX tracks load once per album and are unaffected by subsequent pan/zoom-triggered `requestViewport()` calls. |
| S-067-18 | `is_struct_of_array_enabled` off → old full-fetch `PositionData` + `leaflet.markercluster` path used, backend v2 routes hit, unchanged from pre-feature. |

## Test Strategy

- **Core (query/action layer):** New PHPUnit coverage for `MapViewport` (bounds validation,
  `cellSizeForZoom()`, `snapToGrid()`, antimeridian-aware `WHERE` construction — all pure/unit
  testable), `ResolvesMapPhotoSource`'s root/album query resolution (row-set parity against
  today's `PositionData` classes), `QueryMapBuckets`, `QueryMapPhotos` (leaf-threshold boundary
  cases) — scoped `--filter=` runs against this repo's existing SQLite test setup, per
  `[[feedback_no_full_test_suite]]`.
- **REST:** New `tests/Feature_v3/Map/` suite covering S-067-01 through S-067-13; existing
  `tests/Feature_v2/Map/MapTest.php` regression-run unmodified to prove NFR-067-03.
- **Cache:** New tests mirroring `ManagedCachePhotoListingInvalidatorTest`'s structure, asserting
  scope-tag eviction on save/move/delete (S-067-10, S-067-11) and cache-key stability under
  viewport snapping (NFR-067-04).
- **UI (JS):** `npm run check` (vue-tsc + eslint) for all changed/new frontend files; unit tests for
  the pure `cellSizeForZoom()`/viewport-snapping-adjacent frontend helpers where practical.
  Pan/zoom-triggered fetch behavior, marker rendering, and popup content (S-067-14..17) require
  manual browser verification — no dev environment is available in the authoring session
  (`[[feedback_no_mariadb_mysql_access]]`), flagged as pending exactly like Feature 063/065/066.
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` updated for the three new routes;
  `docs/specs/3-reference/database-schema.md` updated for the new `(latitude, longitude)` index.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-067-01 | `App\DTO\MapViewport` — `north`/`south`/`east`/`west`/`zoom`, `cellSize()`, `snapToGrid()`. | Backend |
| DO-067-02 | `App\Actions\Map\ResolvesMapPhotoSource` — trait providing `resolveRootQuery()`/`resolveAlbumQuery()`, unresolved `Builder<Photo>`. | Backend |
| DO-067-03 | `App\Actions\Map\QueryMapBuckets` — grid `GROUP BY` + centroid/count aggregation. | Backend |
| DO-067-04 | `App\Actions\Map\QueryMapPhotos` — two-pass leaf-cell resolution + bounded hydration; `LEAF_THRESHOLD = 20`. | Backend |
| DO-067-05 | `App\Http\Resources\V3\MapBucketResource` — `bucket_ids[]`/`counts[]`/`centroid_latitudes[]`/`centroid_longitudes[]`. | Backend |
| DO-067-06 | `App\Http\Resources\V3\MapPhotoResource` — thin, purpose-built per-photo marker/popup fields (no tags/rating/palette/statistics). | Backend |
| DO-067-07 | `CacheKeyProvider::mapBucketsKey()`/`mapPhotosKey()`/`mapTracksKey()`/`mapListingTag()`. | Backend |
| DO-067-08 | `resources/js/services/map-v3-service.ts` — `getBuckets()`/`getPhotos()`/`getTracks()`. | Frontend |
| DO-067-09 | `resources/js/stores/MapState.ts` — `bucketsV3`/`photosV3`/`tracksV3`/`isMapSoaActive`/`requestViewport()`. | Frontend |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-067-01 | REST `GET /api/v3/Map/buckets?album_id=&include_sub_albums=&north=&south=&east=&west=&zoom=` | Coarse geo-bucket grid (count + centroid per cell) for the current viewport. | New route. |
| API-067-02 | REST `GET /api/v3/Map/Photos?album_id=&include_sub_albums=&north=&south=&east=&west=&zoom=` | Individual photo marker data for leaf (already-small) cells only. | New route. |
| API-067-03 | REST `GET /api/v3/Map/tracks?album_id=` | Album's GPX track list, viewport-independent. | New route. |

### CLI Commands / Flags

None.

### Telemetry Events

None — mirrors Feature 063/065/066's own "no telemetry introduced" precedent.

### Fixtures & Sample Data

No new committed fixtures. A locally-generated large (order of 100,000 geotagged photos, spread
across a realistic geographic distribution rather than a single point) library is used for manual
scale verification of NFR-067-01, mirroring Feature 063/065/066's own precedent for uncommitted
scale fixtures.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-067-01 | Aggregate (cluster) marker | Cell count `>` leaf threshold → count-badge marker at centroid; click zooms in, no fetch. |
| UI-067-02 | Leaf (individual) marker | Cell count `<=` leaf threshold → real photo-thumbnail marker(s), same popup as today. |
| UI-067-03 | Pan/zoom in progress | Debounced `requestViewport()` fires once settled; in-flight/duplicate snapped-viewport requests deduped. |
| UI-067-04 | v2 fallback | Flag off → old full-fetch `PositionData` + `leaflet.markercluster` path, unchanged. |

## Telemetry & Observability

None — no telemetry events are introduced by this feature.

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md` — document the three new `/api/v3/Map/...` routes.
- `docs/specs/3-reference/database-schema.md` — document the new `(latitude, longitude)` composite
  index on `photos`.
- `docs/specs/4-architecture/knowledge-map.md` — record the grid-bucketing pattern (`MapViewport`,
  `cellSizeForZoom()`/`snapToGrid()`, the two-pass leaf-cell resolution) as a sibling to the
  existing 1D bucket pattern entries.
- `docs/specs/3-reference/frontend-gallery.md` — document `MapState.ts` and the aggregate/leaf
  marker split.
- `docs/specs/4-architecture/roadmap.md` — Feature 067's entry (already added as part of this spec
  being drafted; update on implementation completion).

## Spec DSL

```
domain_objects:
  - id: DO-067-01
    name: MapViewport
    fields:
      - name: north
        type: float
      - name: south
        type: float
      - name: east
        type: float
      - name: west
        type: float
      - name: zoom
        type: int
  - id: DO-067-02
    name: ResolvesMapPhotoSource
  - id: DO-067-03
    name: QueryMapBuckets
  - id: DO-067-04
    name: QueryMapPhotos
    constants:
      - name: LEAF_THRESHOLD
        value: 20
  - id: DO-067-05
    name: MapBucketResource
    fields:
      - name: bucket_ids
        type: array<string>
      - name: counts
        type: array<int>
      - name: centroid_latitudes
        type: array<float>
      - name: centroid_longitudes
        type: array<float>
  - id: DO-067-06
    name: MapPhotoResource
    fields:
      - name: ids
        type: array<string>
      - name: album_ids
        type: array<string|null>
      - name: titles
        type: array<string>
      - name: taken_ats
        type: array<string|null>
      - name: latitudes
        type: array<float>
      - name: longitudes
        type: array<float>
      - name: thumb_urls
        type: array<string|null>
      - name: thumb_urls_2x
        type: array<string|null>
      - name: small_urls
        type: array<string|null>
      - name: small_urls_2x
        type: array<string|null>
  - id: DO-067-07
    name: CacheKeyProvider map-listing methods
  - id: DO-067-08
    name: map-v3-service.ts
  - id: DO-067-09
    name: MapState.ts
routes:
  - id: API-067-01
    method: GET
    path: /api/v3/Map/buckets
  - id: API-067-02
    method: GET
    path: /api/v3/Map/Photos
  - id: API-067-03
    method: GET
    path: /api/v3/Map/tracks
cli_commands: []
telemetry_events: []
fixtures: []
ui_states:
  - id: UI-067-01
    description: Aggregate (cluster) marker
  - id: UI-067-02
    description: Leaf (individual) marker
  - id: UI-067-03
    description: Pan/zoom in progress
  - id: UI-067-04
    description: v2 fallback
```

## Appendix

### Decision Cards

**Q-067-01 — Grid (`FLOOR(lat/cell)`) truncation, or geohash bit-interleaving, for the coordinate
bucket key?**

- **Context:** The existing 1D bucket mechanism (`QueryPhotoBuckets::queryPushdownBuckets()`)
  truncates a date column per-driver (`strftime`/`DATE_FORMAT`/`to_char`). Coordinates are 2D and
  continuous; the direct analog is either a geohash-style interleaved-bit string, or a plain
  rectangular grid via `FLOOR(column / cell_size)`.
- **Options considered:** (A) Plain grid: `FLOOR(latitude/$cell)`, `FLOOR(longitude/$cell)`,
  standard `GROUP BY`. (B) Geohash: encode `(lat,lng)` into a base-32 string, truncate to N
  characters, `GROUP BY` on the prefix. (C) Client-side-only clustering (status quo), just on a
  thinner payload.
- **Decision:** (A). `FLOOR()` is a standard SQL function supported identically by
  sqlite/mysql/mariadb/pgsql with zero driver-specific branching (unlike the existing date
  truncation, which needs a 3-way `match()`). Geohash (B) requires either a driver-specific
  extension or computing/storing the geohash in PHP per row (defeating the SQL-pushdown goal this
  feature exists to achieve) and buys interop with external geohash-based tooling this project has
  no need for. (C) was the status quo and does not solve NFR-067-01 at all — the entire reason
  this feature exists.
- **Resolution date:** 2026-09-13.
- **Spec impact:** FR-067-05, FR-067-06, NFR-067-06.

**Q-067-02 — Fixed constants, or new admin-configurable settings, for the leaf-cluster threshold
and grid-cell-size formula?**

- **Context:** Many bucket-adjacent behaviors elsewhere in this codebase are admin-configurable
  (`photo_title_bucket_prefix_length`, `timeline_photos_granularity`). Both the leaf threshold
  (when a cell "breaks apart" into individual markers) and the grid formula (how cell size maps to
  zoom) are candidates for the same treatment.
- **Decision:** Fixed constants (`QueryMapPhotos::LEAF_THRESHOLD = 20`, a fixed `cellSizeForZoom()`
  formula) for this feature. Neither was requested; adding configurability not asked for expands
  surface area (settings UI, migration-seeded default config rows, documentation) for a tuning knob
  with no expressed demand yet. Revisit as a Follow-up if real usage shows the fixed values are
  wrong for some deployments.
- **Resolution date:** 2026-09-13.
- **Spec impact:** FR-067-06, FR-067-09; Follow-ups in `plan.md`.

**Q-067-03 — Plain composite B-tree index, or a driver-specific spatial index type, on
`(latitude, longitude)`?**

- **Context:** MySQL/MariaDB support `SPATIAL INDEX` (requires `NOT NULL` + a `POINT`-typed
  column, which `latitude`/`longitude` are not); PostgreSQL supports `GiST`/PostGIS; SQLite has no
  built-in spatial index without the SpatiaLite extension, which cannot be assumed present on every
  deployment.
- **Options considered:** (A) Plain composite B-tree index on the existing `decimal` columns,
  identical migration syntax across all four supported drivers. (B) Driver-specific spatial index,
  conditionally created per-driver in the migration. (C) No index (status quo).
- **Decision:** (A). This project explicitly supports sqlite/mysql/mariadb/pgsql uniformly with a
  single migration path; (B) would require per-driver branching in the migration itself, changing
  the column type (`POINT`) for MySQL's `SPATIAL INDEX` specifically, and gains nothing on SQLite
  deployments (the majority of installs) without SpatiaLite. (A) is not a perfect 2D range index
  (only the leading column range-scans efficiently) but is vastly better than today's full table
  scan, and portable everywhere with zero new runtime dependency — consistent with
  `[[feedback_offline_only]]`'s spirit of never assuming an optional extension is present. (C) is
  the exact problem this feature exists to fix.
- **Resolution date:** 2026-09-13.
- **Spec impact:** FR-067-14, NFR-067-01.

**Q-067-04 — One combined backend+frontend feature doc, or a 067/068-style split (mirroring
061/063, 064/065)?**

- **Context:** Those precedent splits exist because their backend and frontend halves were
  genuinely separate efforts shipped at different times. Feature 066 established the alternative
  precedent (Q-066-03): one combined doc when the owner explicitly scopes the work as full-stack,
  done together.
- **Decision:** One combined doc (this one) — the feature owner explicitly asked for "a new
  feature... include backend and Frontend," the same explicit full-stack framing that justified
  Feature 066's own combined-doc precedent. The Increment Map in `plan.md` still orders backend
  increments before frontend ones.
- **Resolution date:** 2026-09-13.
- **Spec impact:** Doc structure only; no FR/NFR impact.

**Q-067-05 — Cache key on the raw requested viewport, or on a grid-snapped viewport?**

- **Context:** A naive viewport-keyed cache (raw `north`/`south`/`east`/`west` floats from
  continuous mouse-drag panning) would almost never repeat exactly, making the cache layer
  effectively a no-op — a correction made during spec authoring, not carried over from any
  existing precedent.
- **Decision:** Snap the requested bounding box outward to whole grid cells at the resolved zoom's
  cell size (`MapViewport::snapToGrid()`) before it is used for both the SQL query and the cache
  key. This makes small pans within the same snapped region produce byte-identical, cacheable
  requests, at the cost of each query fetching a slightly larger area than the exact viewport (the
  snapped-out margin) — an acceptable, bounded overfetch (at most one extra cell-width in each
  direction), not proportional to scope size.
- **Resolution date:** 2026-09-13.
- **Spec impact:** FR-067-07, NFR-067-04.

**Q-067-06 — Handle antimeridian-crossing viewports correctly, or accept them as a known
limitation?**

- **Context:** A viewport can span the ±180° meridian (e.g. panning across the Pacific near
  Fiji/New Zealand). A naive `whereBetween('longitude', [west, east])` silently returns nothing
  useful when `west > east` in that case.
- **Decision:** Handle it — `west > east` switches the longitude predicate to
  `(longitude >= west OR longitude <= east)`. This is a small, contained conditional in one query
  builder method, not a structural complication, and this project's own conventions favor fixing a
  known-reachable correctness gap over documenting around it when the fix is this cheap
  (`[[feedback_no_defensive_dead_code_docs]]`'s inverse: this is a *reachable* branch, not a
  defensive one, so it gets handled, not just noted).
- **Resolution date:** 2026-09-13.
- **Spec impact:** FR-067-08.
