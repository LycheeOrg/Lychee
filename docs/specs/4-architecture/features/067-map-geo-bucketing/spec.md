# Feature 067 – Map Geo-Bucketing

| Field | Value |
|-------|-------|
| Status | Implemented (all 37 original tasks green; Photos-tier design amended post-implementation per Q-067-16..19 — see Appendix; S-067-14..17 manual browser verification and the ~100k-photo scale check flagged outstanding, no dev environment this session) |
| Last updated | 2026-09-16 |
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
  (`hide_nsfw_in_map`, `map_include_subalbums`, `AlbumPolicy::CAN_ACCESS_MAP`) — unchanged, not
  reinterpreted. `CAN_ACCESS_FULL_PHOTO`-gated size-variant downgrade does not carry over to the new
  tier at all: leaf-tier imagery is served by the existing Asset endpoint, which never performs that
  split for thumbnail-class variants in the first place (Q-067-12).
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
| FR-067-02 | `GetMapBucketsRequest`/`GetMapPhotosRequest` (sharing a `HasMapViewportTrait`) validate `north`/`south` (`numeric\|between:-90,90`), `east`/`west` (`numeric\|between:-180,180`), `zoom` (`integer\|between:0,24`), plus the existing `album_id` (`sometimes`, `RandomIDRule`) reusing `HasAbstractAlbumTrait`. No `include_sub_albums` request parameter — sub-album inclusion stays server-derived from the album's `map_include_subalbums` config, exactly like `MapController::getData()` today (Q-067-08). | Well-formed viewport request resolves to a `MapViewport` + optional `AbstractAlbum`. | Any bound outside its numeric range, or `zoom` outside `[0,24]`, is rejected. | Missing any of `north`/`south`/`east`/`west`/`zoom` → 422. | None. | This feature; Q-067-08. |
| FR-067-03 | A new `App\Actions\Map\ResolvesMapPhotoSource` trait's `resolveRootQuery(?User $user): Builder<Photo>` reproduces `Albums\PositionData::do()`'s exact filter — `PhotoQueryPolicy::applySearchabilityFilter()`, `origin: null`, `hide_nsfw_in_map`-driven `include_nsfw` — minus the eager loads and `->get()`. | Root-scope candidate query is byte-identical in *rows matched* to today's `GET /api/Map` (no `album_id`). | N/A (query construction only). | N/A. | None. | Verified against `app/Actions/Albums/PositionData.php:40-63`. |
| FR-067-04 | The same trait's `resolveAlbumQuery(AbstractAlbum $album, bool $includeSubAlbums): Builder<Photo>` reproduces `Album\PositionData::get()`'s exact `$album->photos()`/`$album->all_photos()` branching, minus eager loads and `->get()`; the controller supplies `$includeSubAlbums` from `$request->configs()->getValueAsBool('map_include_subalbums')`, not from any client-supplied parameter (Q-067-08). `AlbumPolicy::CAN_ACCESS_MAP` gate is checked exactly as `MapDataRequest` does today, unchanged. | Album-scope candidate query (± sub-albums) matches today's `GET /api/Map?album_id=...` row set exactly. | N/A. | Guest/unauthorized viewer → 403, same gate as today. | None. | Verified against `app/Actions/Album/PositionData.php:19-55`, `app/Policies/AlbumPolicy.php` (`CAN_ACCESS_MAP`); Q-067-08. |
| FR-067-05 | `GET /api/v3/Map/buckets` (`MapBucketController::buckets()` or a method on a new `MapController`-sibling) returns a `MapBucketResource` — parallel arrays `bucket_ids[]` (opaque `"{lat_cell}:{lng_cell}"` string), `counts[]`, `centroid_latitudes[]`, `centroid_longitudes[]` — computed via one driver-portable SQL `GROUP BY FLOOR(latitude/$cell), FLOOR(longitude/$cell)` + `COUNT(*)`/`AVG(latitude)`/`AVG(longitude)`, `toBase()`-only (no Eloquent hydration). | Response size and query cost are bounded by the number of distinct grid cells intersecting the (snapped) viewport, never by total geotagged-photo count in scope. | Viewport/zoom validated per FR-067-02. | Empty scope (no geotagged photos in view) → all-empty arrays, not an error. | None. | This feature; NFR-067-01. |
| FR-067-06 | Grid cell size is a pure function of `zoom` only: `cellSizeForZoom(int $zoom): float { return 360.0 / (2 ** ($zoom + 6)); }` — a 1/64th-tile-width in decimal degrees at Web-Mercator zoom `$zoom` (halves exactly once per zoom level, ties cell boundaries to a deeper level of the same grid Leaflet's own tiles already use; `+6` amended per Q-067-13). No admin-configurable grid formula (Q-067-02, Q-067-13). | Deeper zoom → smaller cells → finer clustering, with no config to manage; e.g. zoom 11 → ~0.0027° cells (~300 m at the equator, block-sized), zoom 18 → ~0.0000215° (~2.4 m). | N/A. | N/A. | None. | This feature; Q-067-13. |
| FR-067-07 | The requested bounding box is snapped outward to whole grid cells at the resolved cell size (`MapViewport::snapToGrid(): self`) before it is used for both the SQL `WHERE` and the cache key. | Two viewport requests that fall inside the same snapped region produce byte-identical queries and cache keys. | N/A. | N/A. | None. | This feature; NFR-067-04; Q-067-05. |
| FR-067-08 | The bounding-box `WHERE` clause correctly handles antimeridian-crossing viewports (`west > east`): longitude filter becomes `(longitude >= west OR longitude <= east)` instead of `whereBetween`. | A viewport straddling the ±180° meridian (e.g. Fiji/NZ) returns photos on both sides correctly. | N/A. | N/A. | None. | This feature; Q-067-06. |
| FR-067-09 | `GET /api/v3/Map/Photos` returns a new `MapPhotoResource` — parallel arrays `ids[]`, `album_ids[]` (each photo's own real containing album id — see FR-067-10 for the resolution rule — never the request's/scope's own `album_id`), `titles[]`, `taken_ats[]` (preformatted display strings, reusing `date_format_sidebar_taken_at`), `latitudes[]`, `longitudes[]` — populated with **every** distinct photo in the viewport, but **only** when the viewport's total count is `<=` `QueryMapPhotos::MAX_VIEWPORT_PHOTOS = 500` (Q-067-16, amended - no more per-cell grouping). No thumbnail/URL fields: the frontend fetches marker imagery from the existing `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` endpoint (Feature 056) via `ThumbAssetService`, exactly like every other v8 tile, keyed on `ids[]`/`album_ids[]` (Q-067-12). | Only viewports small enough for Leaflet's own client-side clustering to reasonably lay out are ever sent individual photos. | Same viewport/zoom validation as `buckets`. | A viewport over the cap → empty arrays (no error), frontend falls back to `buckets`. | None. | This feature; NFR-067-05; Q-067-12; Q-067-16. |
| FR-067-10 | `QueryMapPhotos::do()` (Q-067-16, amended) builds one `SELECT DISTINCT` bounding-box query (`buildDistinctPhotoRowsQuery()`, `toBase()`-only, no grid grouping), bounded by `->limit(MAX_VIEWPORT_PHOTOS + 1)`, and returns its rows only if the fetched count is `<= MAX_VIEWPORT_PHOTOS` (a separate `count()` pre-check followed by an unbounded fetch would leave a TOCTOU window where a concurrent write pushes the real count past the cap between the two queries); either way, a separate join resolves each returned photo's `album_ids[i]` (Q-067-15): **album scope** (± sub-albums) constrains the join to albums within the query's own already-authorized scope (the requested album alone, or its `_lft`/`_rgt` subtree when `include_sub_albums` — no separate per-sub-album access re-check, matching `all_photos()`'s own existing, unchecked-per-descendant semantics) — deterministic tie-break (lowest `_lft`) if more than one in-scope album contains the row; **root scope** has no such "natural" album (candidates come from a cross-library `Photo::query()`, not a specific album join), so it joins `photo_album` → `base_albums` → `computed_access_permissions`, applies `AlbumQueryPolicy::appendAccessibilityConditions()` (the pure query-builder form of `AlbumPolicy::canAccess()`, already designed to run against exactly these two joined tables — no `Album` model needed), then collapses the resulting one-row-per-membership fan-out back to one row per photo via `GROUP BY photos.id` + `MIN(photo_album.album_id)` — the same collapse-after-join principle `ResolvesPhotoSource::resolvePhotoQuery()`'s `BaseSmartAlbum` branch already uses to keep a photo-to-many-albums join from duplicating rows (there via a `whereIn` id-subquery that only tests existence; here via `MIN()` because the *value* of the winning album id must survive, not just a yes/no). Fully `toBase()`, no `size_variants` join, no Eloquent hydration anywhere in either scope (Q-067-12). | Row-fetch cost is bounded by `MAX_VIEWPORT_PHOTOS`, never by total scope size; the root-scope tie-break adds one join+aggregate, not a hydration step. | N/A. | N/A. | None. | This feature; Q-067-12; Q-067-15; Q-067-16. |
| FR-067-11 | `MapPhotoResource` is purpose-built, not a reuse of `PhotoResource` — it never eager-loads or exposes `tags`/`rating`/`statistics`/`palette`/`size_variants`, none of which the Map marker/popup UI renders or needs (verified against `Map.vue`'s own `MapPhotoEntry` type; imagery comes from the Asset endpoint, Q-067-12). | Per-photo payload for the leaf tier is materially smaller than today's full `PhotoResource` — five scalar arrays, no joined image metadata. | N/A. | N/A. | None. | This feature; verified against `resources/js/v8/views/gallery-panels/Map.vue`. |
| FR-067-12 | `MapPhotoResource` never computes `should_downgrade` or exposes any size-variant URL — leaf-tier marker imagery is fetched by the frontend directly from the existing v3 Asset endpoint (`GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}`), which — per its own `GetPhotoAssetRequest` docblock — never performs a full-photo-access split for thumbnail-class size variants (`SizeVariantAssetType`) in the first place. | A viewer sees exactly the imagery the Asset endpoint would already serve them for that photo/album, with zero duplicated permission logic in this tier. | N/A. | An inaccessible `album_id` 403s at Asset-fetch time (that one marker's thumbnail fails to load), independent of the Map `buckets`/`Photos` endpoints' own success. | None. | Resolved via Q-067-12 (supersedes the earlier, incorrect `should_downgrade` design); verified against `app/Http/Requests/Photo/GetPhotoAssetRequest.php`'s own docblock and `app/Http/Controllers/Gallery/PhotoAssetController.php`'s fallback chain. |
| FR-067-13 | `GET /api/v3/Map/tracks?album_id=...` returns the album's existing `TrackResource[]` unchanged, independent of viewport/zoom — `album_id` is required here (422 if missing); there is no root-scope equivalent (root has no tracks, matching today's `Albums\PositionData::do()` returning an empty `tracks` collection). | Track list loads once per album context, not re-fetched on pan/zoom. | `album_id` required, `RandomIDRule`. | Missing `album_id` → 422; unauthorized album → 403 via the same `CAN_ACCESS_MAP` gate. | None. | This feature. |
| FR-067-14 | A new migration adds a plain composite index on `photos(latitude, longitude)` — no partial/`WHERE`-conditional clause (not portably expressible across mysql/mariadb/sqlite/pgsql in one migration), no spatial index type (Q-067-03). | Bounding-box `WHERE` + grid `GROUP BY` queries use the index instead of a full table scan. | N/A. | N/A. | None. | This feature; NFR-067-01. |
| FR-067-15 | New `CacheKeyProvider::mapBucketsKey()`/`mapPhotosKey()`/`mapTracksKey()` methods key on scope (root, or `album_id` alone — `include_sub_albums` is a single global config value at any point in time, not a per-request axis, so it is never part of the key; a change to it is handled by FR-067-24's flush instead, Q-067-08/Q-067-14), the **snapped** viewport + zoom, the unlocked-albums digest, and user id — mirroring the existing `photoBucketsKey()`/`photoRatiosKey()` pattern. Tagged coarsely per scope (`mapListingTag(scope)`), no fine per-cell tag. | Identical/overlapping requests within the same snapped region hit cache; a save/move/delete anywhere in scope invalidates the whole scope's map cache (coarse), same cost class as today's per-album photo-listing coarse tag. | N/A. | N/A. | None. | This feature; FR-067-07. |
| FR-067-16 | `ManagedCachePhotoListingInvalidator` (or a new sibling listener) reacts to the existing `PhotoSaved`/`PhotoMoved`/`PhotoDeleted` events and evicts the touched photo's scope's coarse map cache tag(s) — root scope's tag always evicted (its tag is coarse/scope-wide, not per-photo, so it covers every possible root-scope viewer regardless of which photo changed), plus each of the photo's containing albums' tag if album-scoped map caches for those albums are warm. Whatever today already causes one of these three events to fire (title/date edits, moves, deletes — no endpoint currently edits `latitude`/`longitude` post-upload) drives this invalidation; this feature does not add a new trigger condition to any of them. | Whatever already fires `PhotoSaved`/`PhotoMoved`/`PhotoDeleted` today invalidates exactly the map caches that could show the affected photo, no more. | N/A. | N/A. | None. | This feature. |
| FR-067-17 | New `resources/js/services/map-v3-service.ts` exposes `getBuckets()`/`getPhotos()`/`getTracks()`, mirroring `photo-children-v3-service.ts`'s axios-cache-interceptor conventions. | Frontend has one small, typed service module for the new endpoints. | N/A. | N/A. | None. | This feature. |
| FR-067-18 | A new `resources/js/stores/MapState.ts` Pinia store holds `bucketsV3`, `photosV3`, `tracksV3`, an `isMapSoaActive` getter (same existing `is_struct_of_array_enabled` flag), and a debounced `requestViewport(bounds, zoom)` action that dedupes identical/in-flight snapped-viewport requests. `fetchViewportNow()` fetches `/Map/Photos` first and only fetches `/Map/buckets` if that response is empty (Q-067-17, amended - avoids paying for a bucket/count query whose result would just be discarded whenever the viewport is under the cap); it also tracks the real (unsnapped) bounds of the last exhaustive Photos fetch and skips the request entirely when a later viewport is fully contained within them (Q-067-18, amended - a zoom-in within an already-fully-fetched area needs no re-fetch at all). | Panning/zooming triggers at most one in-flight request per distinct snapped viewport, and zero requests when zooming into an area already exhaustively held client-side. | Gated by `isMapSoaActive`; flag off → store unused. | N/A. | None. | This feature; mirrors `TimelineState.ts`'s dedup pattern (FR-066-11); Q-067-17; Q-067-18. |
| FR-067-19 | `Map.vue`'s SoA path listens to Leaflet `moveend`/`zoomend`, computes the current bounds+zoom, and calls `MapState.ts.requestViewport()` (debounced) instead of fetching once via `PositionData` on load. | Buckets/leaf-photos refresh as the user pans/zooms, never re-fetching the whole scope. | N/A. | N/A. | None. | This feature. |
| FR-067-20 | Buckets render as plain aggregate markers (count badge) at `(centroid_latitudes[i], centroid_longitudes[i])` only when `/Map/Photos` came back empty (Q-067-16, amended - viewport over `MAX_VIEWPORT_PHOTOS`, not a per-cell threshold); clicking one zooms the map in (e.g. `map.setView(centroid, zoom+2)`), it does not fetch member photos. | A cheap, always-populated `bucketsV3` never renders once the viewport is small enough for individual photos. | N/A. | N/A. | None. | This feature; Q-067-16. |
| FR-067-21 | Below `MAX_VIEWPORT_PHOTOS`, every photo in the viewport is handed to `clusterFunc()`/`Cluster.add()` (Q-067-16, amended - no more per-cell grid split) - the exact same `leaflet.markercluster` client-side clustering, marker/popup template, and `.leaflet-marker-photo` CSS the v2 (non-SoA) path already uses. A photo's thumbnail is fetched lazily, not eagerly (Q-067-19, amended): a cluster badge fetches only its representative child's thumbnail, inside a custom `iconCreateFunction`, the first time that badge is actually drawn; a standalone/spiderfied marker fetches its own thumbnail from its own Leaflet `'add'` event, fired exactly when that specific marker (not a cluster standing in for it) is placed on the map. A photo that stays hidden inside a cluster the whole time is never fetched at all. | Cluster sizes and thumbnails match the v2 path's graduated, pixel-radius clustering exactly, instead of a fixed-grid approximation of it, and only the photos actually rendered are ever fetched. | N/A. | N/A. | None. | This feature; Q-067-16; Q-067-19; verified against `Map.vue`'s existing `open()`/template code. |
| FR-067-22 | GPX tracks are fetched once via `MapState.ts` when an album context is present and `isMapSoaActive`, independent of viewport changes; `L.GPX` layer rendering, the fixed color palette, and the Leaflet layers control are unchanged from today. | Track overlay behavior is unaffected by this feature. | N/A. | N/A. | None. | This feature; verified against `Map.vue`'s existing track-rendering block. |
| FR-067-23 | The v2 Map route, controller, both `PositionData` action classes, and the v2 `leaflet.markercluster` rendering path in `Map.vue` remain fully intact and reachable when the SoA flag is off. | Flag off → v2 Map behavior byte-identical to pre-feature. | N/A. | N/A. | None. | Mirrors Feature 065/066's own coexistence precedent. |
| FR-067-24 | A config-change listener flushes every warm map-cache tag (root's, plus every warm album scope's) whenever any of `hide_nsfw_in_map`, `map_include_subalbums`, `map_display`, or `map_display_public` changes — mirroring Q-053-05's precedent for the sibling album-listing cache. | An admin toggling any of these four settings sees the map reflect it immediately, not after a full TTL. | N/A. | N/A. | None. | Resolved via Q-067-14. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-067-01 | Both new query tiers' cost must be bounded, never by total geotagged-photo count in scope: buckets tier by distinct-grid-cells-in-viewport; Photos tier (Q-067-16, amended) by a single `->limit(MAX_VIEWPORT_PHOTOS + 1)` fetch, so an over-cap viewport never pays for row hydration past the cap — and never for a separate, unbounded `COUNT(*)` over a potentially huge bounding box at low zoom either. | Direct motivation for this feature: at ~100,000 geotagged photos, today's unconditional `->get()` exhausts PHP memory before a response is produced. | Manual/scoped timing + row-count check against a large local fixture; query plan review (no PHP loop over the full candidate set in either tier). | FR-067-05, FR-067-09, FR-067-10, FR-067-14 (index). | This feature; direct owner-reported production concern (memory exhaustion at 100k photos); Q-067-16. |
| NFR-067-02 | No Carbon usage anywhere in the Map feature's backend, including `MapPhotoResource`'s `taken_ats[]` formatting. Since Q-067-12 removed all Eloquent hydration from the leaf tier, `taken_ats[]` can no longer reuse `PreformattedPhotoData`'s `$photo->taken_at?->format(...)` pattern (a Carbon-cast model attribute) the way `QueryPhotoDetails` does — `taken_at` arrives as a raw string column from a `toBase()` row. Formatting must use native PHP (`date()`/`DateTime::createFromFormat()`) against that raw string instead. | `[[feedback_avoid_carbon_server_side]]` — this project's owner has flagged Carbon as too resource-costly for new server-side arithmetic. The Map tier's own architecture (Q-067-12) removed the one carve-out (`QueryPhotoDetails`-style hydrated-model formatting) that would otherwise have applied here. | Code review / grep for `Carbon`/`DateTime` imports across every new Map backend file, including the leaf-tier resource builder — no exception remains. | `[[feedback_avoid_carbon_server_side]]`; Q-067-12. | Direct owner instruction (memory); the `QueryPhotoDetails` carve-out this NFR originally cited no longer applies once the leaf tier stopped hydrating `Photo` models. |
| NFR-067-03 | Zero behavior change to the v2 Map path (`GET /api/Map`, `MapController::getData()`, both `PositionData` classes, `Map.vue`'s v2 branch) when `is_struct_of_array_enabled` is off. | Coexistence requirement, mirrors Feature 065/066. | Manual/regression check of `/map` and `/map/{albumId}` with the flag off; diff review of the four v2 files (expect zero changes). | Feature flag machinery already shipped by Feature 065. | Mirrors Feature 065/066's own NFR precedent. |
| NFR-067-04 | Viewport bounds must be snapped to the zoom-derived grid before being used as a cache key; an un-snapped (raw float) cache key would have a near-zero hit rate for ordinary small pans, making the cache layer pointless. | Direct design correction made during spec authoring (see Q-067-05) — a viewport-keyed cache is only worth building if it can actually hit. | Unit test: two overlapping but distinct raw viewports inside the same grid cell produce identical cache keys. | `MapViewport::snapToGrid()`. | This feature. |
| NFR-067-05 | The Photos tier's row-fetch is a plain `toBase()` pass throughout — no `size_variants` join (Q-067-12), and no Eloquent hydration anywhere, including the root-scope `album_ids[i]` tie-break, which resolves via `AlbumQueryPolicy::appendAccessibilityConditions()` joined against `base_albums`/`computed_access_permissions` directly, collapsed with `GROUP BY`/`MIN()` (Q-067-15) rather than any per-candidate model check. Cost must stay bounded by `MAX_VIEWPORT_PHOTOS`, structurally (the row-fetch itself carries `->limit(MAX_VIEWPORT_PHOTOS + 1)`, not a separate pre-check query), not merely by convention (Q-067-16, amended — supersedes the original per-cell `HAVING COUNT(*) <= LEAF_THRESHOLD` bound). | Same class of structural-bound guarantee `QueryPhotoDetails` relies on for its own bounded tier, applied here to a cheaper `toBase()` row-fetch instead of Eloquent hydration. | Test asserting a synthetic `MAX_VIEWPORT_PHOTOS + 1`-photo viewport is excluded from the response entirely (`QueryMapPhotosTest::testViewportOverCapReturnsEmptyResponse`). | FR-067-10. | This feature; Q-067-12; Q-067-16. |
| NFR-067-06 | The lat/lng grid is not equal-area (cells narrow visually near the poles in unprojected degree-space); this is an accepted, documented trade-off for SQL-portability and cost-boundedness, not a defect to work around in this feature. | Direct trade-off already discussed and accepted with the feature owner before drafting (grid/`FLOOR` vs geohash vs pixel-space clustering). | N/A — documented acceptance, not tested. | Q-067-01. | This feature; owner-accepted trade-off. |

## UI / Interaction Mock-ups

```
Map (flag on, viewport-driven) — viewport OVER MAX_VIEWPORT_PHOTOS
┌──────────────────────────────────────────────────────────┐
│                                                            │
│        ○12                                                │
│     (aggregate,                    ○47                    │
│      count badge,                (aggregate)               │
│      click → zoom in)                                     │
│                                                            │
└──────────────────────────────────────────────────────────┘
  /Map/Photos comes back empty (over cap) → /Map/buckets is
  fetched and its counts render as plain badges; clicking one
  only zooms in, never fetches members.

Map (flag on, viewport-driven) — viewport UNDER MAX_VIEWPORT_PHOTOS
┌──────────────────────────────────────────────────────────┐
│                                                            │
│      ⌗8  ⌗3            ⌗25                                 │
│  (graduated Leaflet clusters, real thumbnails,             │
│   lazily loaded per representative child)      ⌗2  ⌗1      │
│                                                            │
└──────────────────────────────────────────────────────────┘
  /Map/Photos returns every photo in the viewport (buckets is
  never fetched at all - Q-067-17); the raw list is handed to
  the same leaflet.markercluster client-side clustering the v2
  path already uses, so cluster sizes/thumbnails match it
  exactly instead of approximating it with a server-side grid.

  pan/zoom → debounced requestViewport() re-fetches Photos
  first; buckets is only fetched if that comes back empty
  (Q-067-17). A zoom-in fully inside an already-exhaustively-
  fetched area triggers no request at all (Q-067-18).
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-067-01 | Root scope, ~100,000 geotagged photos in the library: `GET /api/v3/Map/buckets` response time/row count is bounded by distinct cells in the requested viewport, not 100,000. |
| S-067-02 | Album scope, `map_include_subalbums` config off vs on (server-derived, no request param, Q-067-08) — matches `Album\PositionData::get()`'s existing distinction exactly. |
| S-067-03 | Guest without `CAN_ACCESS_MAP` on the target album → 403 on both new endpoints. |
| S-067-04 | Viewport crossing the antimeridian (`west=170, east=-170`) returns photos correctly on both sides. |
| S-067-05 | Two overlapping-but-not-identical raw viewport requests that fall inside the same snapped grid region produce an identical cache key (cache hit on the second). |
| S-067-06 | A viewport whose total distinct-photo count is `> MAX_VIEWPORT_PHOTOS` gets an empty `Photos` response; `buckets` still returns its full aggregate grid for that same viewport (Q-067-16, amended). |
| S-067-07 | A viewport whose total distinct-photo count is `<= MAX_VIEWPORT_PHOTOS` has every one of its photos returned by `Photos`, unfiltered/ungrouped (Q-067-16, amended). |
| S-067-08 | `hide_nsfw_in_map=true` hides NSFW photos from both tiers at root scope, matching today's `PositionData` behavior. |
| S-067-09 | `QueryMapPhotos` only ever resolves a leaf photo's `album_ids[i]` from an album the current viewer can access; the Asset endpoint's own, pre-existing authorization then governs the actual thumbnail fetch — no `should_downgrade`-style logic exists in this tier at all (Q-067-12). |
| S-067-10 | Editing a geotagged photo's title/date (firing `PhotoSaved`) or moving it (firing `PhotoMoved`) invalidates its scope's coarse map-cache tag(s); an unrelated scope's warm cache entry survives. |
| S-067-11 | Deleting a geotagged photo invalidates the same coarse tag(s). |
| S-067-12 | `GET /api/v3/Map/tracks?album_id=...` returns the album's tracks, unaffected by viewport/zoom params (ignored if passed). |
| S-067-13 | Missing `album_id` on the tracks endpoint → 422; missing any of `north`/`south`/`east`/`west`/`zoom` on `buckets`/`Photos` → 422. |
| S-067-14 | Frontend: panning/zooming the map triggers `requestViewport()`, debounced, with in-flight/identical-snapped-viewport requests deduped (no duplicate network calls). |
| S-067-15 | Frontend: clicking an aggregate (count-badge) marker zooms the map in; it never triggers a photo fetch for that cluster's members. |
| S-067-16 | Frontend: an individual photo marker renders with the exact same thumbnail image and popup content (title, camera-date icon, formatted date) as today's v2 rendering, fetched lazily — a photo that never becomes standalone/a cluster representative never triggers a thumbnail request at all (Q-067-19, amended). |
| S-067-17 | Frontend: GPX tracks load once per album and are unaffected by subsequent pan/zoom-triggered `requestViewport()` calls. |
| S-067-18 | `is_struct_of_array_enabled` off → old full-fetch `PositionData` + `leaflet.markercluster` path used, backend v2 routes hit, unchanged from pre-feature. |
| S-067-19 | Toggling `hide_nsfw_in_map` (or `map_include_subalbums`/`map_display`/`map_display_public`) flushes every warm map-cache tag; the very next request for a previously-cached viewport recomputes rather than serving the stale entry (FR-067-24). |
| S-067-20 | Frontend: for a viewport under `MAX_VIEWPORT_PHOTOS`, `requestViewport()` issues exactly one request (`/Map/Photos`) — `/Map/buckets` is never called, since its result would just be discarded (Q-067-17, amended). |
| S-067-21 | Frontend: zooming in on a viewport whose bounds are fully contained within the last exhaustively-fetched (under-cap) viewport triggers no network request at all — Leaflet simply doesn't draw the now off-screen markers (Q-067-18, amended). |
| S-067-22 | Frontend: a photo that is a cluster's representative child has its thumbnail fetched exactly once even if that cluster's icon is redrawn multiple times (e.g. across zoom steps); a photo hidden inside a cluster for its entire time on screen is never fetched (Q-067-19, amended). |

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
  scope-tag eviction on save/move/delete (S-067-10, S-067-11), cache-key stability under
  viewport snapping (NFR-067-04), and config-change cache flush (S-067-19, FR-067-24).
- **UI (JS):** `npm run check` (vue-tsc + eslint) for all changed/new frontend files; unit tests for
  the pure `cellSizeForZoom()`/viewport-snapping-adjacent frontend helpers where practical. No
  frontend unit-test infrastructure exists for Pinia stores in this codebase yet, so
  `MapState.ts`'s sequential-fetch (Q-067-17) and bounds-containment skip (Q-067-18) logic, and
  `Map.vue`'s lazy thumbnail loading (Q-067-19), have no dedicated automated coverage — verified by
  code review and `npm run check`/`npm run build` only, same gap class as the manual-verification
  items below. Pan/zoom-triggered fetch behavior, marker rendering, and popup content (S-067-14..17,
  S-067-20..22) require manual browser verification — no dev environment is available in the
  authoring session
  (`[[feedback_no_mariadb_mysql_access]]`), flagged as pending exactly like Feature 063/065/066.
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` updated for the three new routes;
  `docs/specs/3-reference/database-schema.md` updated for the new `(latitude, longitude)` index.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-067-01 | `App\DTO\MapViewport` — `north`/`south`/`east`/`west`/`zoom`, `cellSizeForZoom()`, `snapToGrid()`. | Backend |
| DO-067-02 | `App\Actions\Map\ResolvesMapPhotoSource` — trait providing `resolveRootQuery()`/`resolveAlbumQuery()`, unresolved `Builder<Photo>`. | Backend |
| DO-067-03 | `App\Actions\Map\QueryMapBuckets` — grid `GROUP BY` + centroid/count aggregation. | Backend |
| DO-067-04 | `App\Actions\Map\QueryMapPhotos` — one `SELECT DISTINCT` bounding-box query, `->limit(MAX_VIEWPORT_PHOTOS + 1)`-bounded, `toBase()`-only throughout (no Eloquent hydration, Q-067-12; no per-cell grouping and no separate count pre-check, Q-067-16, amended). | Backend |
| DO-067-05 | `App\Http\Resources\V3\MapBucketResource` — `bucket_ids[]`/`counts[]`/`centroid_latitudes[]`/`centroid_longitudes[]`. | Backend |
| DO-067-06 | `App\Http\Resources\V3\MapPhotoResource` — thin, purpose-built per-photo marker/popup fields (no tags/rating/palette/statistics/size_variants; imagery resolved client-side via the Asset endpoint, Q-067-12). | Backend |
| DO-067-07 | `CacheKeyProvider::mapBucketsKey()`/`mapPhotosKey()`/`mapTracksKey()`/`mapListingTag()`. | Backend |
| DO-067-08 | `resources/js/services/map-v3-service.ts` — `getBuckets()`/`getPhotos()`/`getTracks()`. | Frontend |
| DO-067-09 | `resources/js/stores/MapState.ts` — `bucketsV3`/`photosV3`/`tracksV3`/`isMapSoaActive`/`requestViewport()`. | Frontend |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-067-01 | REST `GET /api/v3/Map/buckets?album_id=&north=&south=&east=&west=&zoom=` | Coarse geo-bucket grid (count + centroid per cell) for the current viewport. No `include_sub_albums` param — server-derived from `map_include_subalbums` (Q-067-08). | New route. |
| API-067-02 | REST `GET /api/v3/Map/Photos?album_id=&north=&south=&east=&west=&zoom=` | Individual photo marker data for leaf (already-small) cells only. No `include_sub_albums` param (Q-067-08). | New route. |
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
| UI-067-01 | Aggregate (cluster) marker | Viewport total `>` `MAX_VIEWPORT_PHOTOS` (Q-067-16, amended) → count-badge marker at centroid; click zooms in, no fetch. Below the cap, individual photos are clustered client-side by Leaflet instead (graduated sizes, real thumbnails). |
| UI-067-02 | Individual (clustered client-side) marker | Viewport total `<=` `MAX_VIEWPORT_PHOTOS` (Q-067-16, amended) → real photo-thumbnail marker(s)/graduated Leaflet clusters, same popup as today; thumbnails load lazily, only for what's actually rendered (Q-067-19, amended). |
| UI-067-03 | Pan/zoom in progress | Debounced `requestViewport()` fires once settled; in-flight/duplicate snapped-viewport requests deduped. |
| UI-067-04 | v2 fallback | Flag off → old full-fetch `PositionData` + `leaflet.markercluster` path, unchanged. |

## Telemetry & Observability

None — no telemetry events are introduced by this feature.

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md` — document the three new `/api/v3/Map/...` routes.
- `docs/specs/3-reference/database-schema.md` — document the new `(latitude, longitude)` composite
  index on `photos`.
- `docs/specs/4-architecture/knowledge-map.md` — record the grid-bucketing pattern (`MapViewport`,
  `cellSizeForZoom()`/`snapToGrid()`) as a sibling to the existing 1D bucket pattern entries, and
  the Photos tier's viewport-total-cap design (Q-067-16, amended) superseding it.
- `docs/specs/3-reference/frontend-gallery.md` — document `MapState.ts` and the aggregate-badge vs.
  client-side-clustered-individual-photo split (Q-067-16, amended).
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
      - name: MAX_VIEWPORT_PHOTOS
        value: 500
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

**Q-067-08 — Server-derived `map_include_subalbums` config, or a client-supplied `include_sub_albums` request parameter?**

- **Context:** The first draft's FR-067-02 added a client-supplied `include_sub_albums` boolean to `GetMapBucketsRequest`/`GetMapPhotosRequest`. But `MapController::getData()` derives this value server-side today, from a single **global**, admin-only config row (`map_include_subalbums` — not even a per-album override), via `$request->configs()->getValueAsBool('map_include_subalbums')`. No other `include_sub_albums`-shaped setting in this codebase (e.g. `flow_include_sub_albums`) is client-controlled either.
- **Decision:** Drop the request parameter. `resolveAlbumQuery()`'s `$includeSubAlbums` argument is supplied by the controller from `map_include_subalbums`, exactly like `MapController::getData()` does today — zero behavior change, no new per-viewer toggle. If a future UI wants a per-viewing sub-album toggle, that is a new, separately-scoped capability, not something to smuggle into this feature's request validation.
- **Resolution date:** 2026-09-15 (owner: "Q-67-08: A").
- **Spec impact:** FR-067-02, FR-067-04.

**Q-067-12 — Bake size-variant URLs (with a `should_downgrade` gate) into `MapPhotoResource`, or delegate imagery entirely to the existing v3 Asset endpoint?**

- **Context:** The first draft's FR-067-12 specced `should_downgrade` via `Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, ...)`, mirroring `QueryPhotoDetails`. But that check is genuinely wrong for this tier on inspection: `GetPhotoAssetRequest`'s own docblock states that `size_variant` is restricted to thumbnail-class tokens (`SizeVariantAssetType`), so "unlike a plain `PhotoPolicy` check, there is no thumb-vs-full-photo access split to make" for exactly the variants Map ever shows (`small`/`small2x`/`thumb`/`thumb2x`) — `PhotoAssetController` already has its own missing-variant fallback chain, and an established frontend pattern (`thumb-asset-service.ts`/`ThumbAssetService.acquire(albumId, photoId, type)`, used by the existing `<Thumb>` component) already fetches exactly this class of asset, authenticated, without a plain `<img src>`.
- **Options considered:** (A) Keep baking full size-variant URLs into `MapPhotoResource`, computing `should_downgrade` server-side per the original draft. (B) Drop all URL fields from `MapPhotoResource`; the frontend fetches leaf-tier marker imagery from `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` directly, keyed on `ids[]`/`album_ids[]`.
- **Decision:** (B), per owner direction ("can't we use the asset endpoint instead? that way we don't even need the size variant join"). This removes `QueryMapPhotos`'s entire second-pass Eloquent hydration — the leaf tier becomes a single `toBase()`-only pass, no `size_variants` join, no `should_downgrade` computation, no duplicated permission logic (the Asset endpoint's own, already-tested authorization governs the actual fetch). The one hard new requirement this creates: `album_ids[]` must carry each photo's **real, viewer-accessible containing album id** (see Q-067-11 in `open-questions.md`), since `GetPhotoAssetRequest::isPhotoOfAlbum()` checks direct pivot membership against exactly the `album_id` given, no subtree walk — the scope-uniform `null`-at-root value `PositionDataResource` uses today would never work here.
- **Resolution date:** 2026-09-15.
- **Spec impact:** FR-067-09, FR-067-10, FR-067-11, FR-067-12, NFR-067-02, NFR-067-05, DO-067-06, S-067-09; supersedes the `should_downgrade` framing this Appendix's own FR-067-12 originally had. The album-id tie-break this decision made mandatory is resolved separately by Q-067-15 below.

**Q-067-13 — Concrete `cellSizeForZoom()` constants: pin now, or tune empirically at implementation time?**

- **Context:** FR-067-06's first draft described the grid-cell-size formula only qualitatively ("e.g. halving per zoom level"), with no base cell size or exact function anywhere in spec/plan/tasks — yet S-067-01, NFR-067-01's scale verification, and `MapViewportTest`'s monotonicity assertions all need real numbers to test against (tracked as Q-067-10 in `open-questions.md`).
- **Options considered:** (A) Pin concrete constants in the spec now — tie cell size directly to Leaflet's own Web-Mercator tile-grid geometry (`360° / 2^zoom`, one tile-width per cell), so grid boundaries line up with the map's own zoom levels rather than an arbitrary independent sequence. (B) Leave exact constants to implementation-time tuning once the ~100k-photo fixture exists.
- **Decision:** (A) — `cellSizeForZoom(int $zoom): float { return 360.0 / (2 ** $zoom); }`. This reuses the exact halving progression FR-067-06 already committed to, ties it to a geometry Leaflet callers already understand (no independent "why this number" to justify), and needs no fixture to define, only to spot-check. Concrete examples: zoom 10 → ~0.35° (~35 km at the equator), zoom 18 → ~0.0014° (~150 m), zoom 0 → 360° (whole world, one cell). Empirical spot-check against the ~100k-photo local fixture is deferred to I3/I4 implementation — flagged pending like this feature's other manual-verification gaps (no dev environment in the authoring session, `[[feedback_no_mariadb_mysql_access]]`), not blocking the constant's inclusion in the spec now.
- **Resolution date:** 2026-09-15 (owner: "Q-67-10: A").
- **Spec impact:** FR-067-06.
- **Amendment (2026-09-16):** One-tile-per-cell proved too coarse in practice: a 256px-wide bucket routinely lumped together photos that read as clearly separate on screen, and (combined with Leaflet's real max zoom being lower than the validated range) some real-world clusters could never zoom-split into individual leaf markers. `GRID_ZOOM_OFFSET` was added and tuned twice against owner feedback in the same session ("the clustering threshold is too wide", then "definitely not tight enough" against real screenshots at a measured ~zoom 11-12) - first `2` (quarter-tile), then `6`: `cellSizeForZoom(int $zoom): float { return 360.0 / (2 ** ($zoom + 6)); }`, a 1/64th-tile per cell, still halving once per zoom level and still tied to Leaflet's own tile grid, just a deeper level of it. Zoom 11 now ~300 m (block-sized) instead of ~5 km (town-sized); zoom 18 now ~2.4 m instead of ~150 m. `MapViewportTest`'s pinned-formula assertions and the frontend mirror (`MapState.ts`) were updated to match. Superseded for the Photos tier specifically by Q-067-16 below - this grid is now only used by `/Map/buckets`' aggregate tier.

**Q-067-14 — Map cache invalidation on config change: add it, or accept TTL-only staleness?**

- **Context:** FR-067-15/16's first draft wired map-cache invalidation only to `PhotoSaved`/`PhotoMoved`/`PhotoDeleted`. Nothing evicted the cache when a map-relevant candidate-scope config changed (`hide_nsfw_in_map`, `map_include_subalbums`, `map_display`, `map_display_public`) — unlike this project's own Q-053-05 precedent, which added exactly this kind of coarse "flush all on global config change" rule for the sibling album-listing cache (tracked as Q-067-09 in `open-questions.md`).
- **Options considered:** (A) Add a config-change listener mirroring Q-053-05, flushing every warm map-cache tag (root's, plus every warm album scope's) on any of the four config keys changing. (B) Accept TTL-only staleness for this dimension, documented as an accepted gap.
- **Decision:** (A), per owner direction. New FR-067-24; new scenario S-067-19.
- **Resolution date:** 2026-09-15 (owner: "Q-67-9: A").
- **Spec impact:** FR-067-24, S-067-19.

**Q-067-15 — `album_ids[]` tie-break: first viewer-accessible album, or prefer the album that produced this photo in the current query's own scope?**

- **Context:** Q-067-12 made `album_ids[]` load-bearing (it feeds the Asset endpoint's `{album_id}` path segment, which checks direct `photo_album` membership against exactly that id, no subtree walk). A photo belonging to multiple albums, or an album-scope query with `include_sub_albums=true` where the photo lives in a different sub-album than the one requested, both need a tie-break rule (tracked as Q-067-11 in `open-questions.md`).
- **Options considered:** (A) Always pick the first viewer-accessible album found via the `photo_album` join, deterministic tie-break, with no preference for "which scope asked." (B) Prefer the album that actually produced this photo in the *current* query's own scope, falling back to Option A's rule only where no such "natural" album exists.
- **Decision:** (B), per owner direction, resolved concretely as: **album scope** (± sub-albums) constrains the `photo_album` join to albums within the query's own already-authorized scope — the requested album alone, or its `_lft`/`_rgt` subtree when `include_sub_albums` — with no separate per-sub-album access re-check, exactly mirroring `all_photos()`'s own existing, unchecked-per-descendant behavior; deterministic tie-break (lowest `_lft`) if more than one in-scope album contains the row. **Root scope** has no "natural" album at all — its candidates come from a cross-library `Photo::query()`, not a specific album join — so it always falls back to Option A's rule, resolved entirely in SQL: join `photo_album` → `base_albums` → `computed_access_permissions`, apply `AlbumQueryPolicy::appendAccessibilityConditions()` (the query-builder form of `AlbumPolicy::canAccess()`; its own docblock already documents it as designed to run against exactly these two joined tables, no `Album` model required), then collapse the resulting one-row-per-membership fan-out to one row per photo via `GROUP BY photos.id` + `MIN(photo_album.album_id)` — mirroring `ResolvesPhotoSource::resolvePhotoQuery()`'s existing `BaseSmartAlbum` branch, which collapses the same kind of photo-to-many-albums fan-out via a `whereIn` id-subquery; the difference here is that the winning album id itself must be kept, not just tested for existence, hence `MIN()` over `whereIn`. No Eloquent hydration anywhere in this tier, in either scope.
- **Resolution date:** 2026-09-15 (owner: "Q-67-11: B"; join+collapse mechanism refined 2026-09-15 — no `Album` hydration needed after all, per owner: "You can resolve that with joins and collapse. See on timeline v3 photo selection.").
- **Spec impact:** FR-067-10, NFR-067-05.

**Q-067-16 — Photos tier over the leaf threshold: keep approximating pixel-radius clustering with a finer SQL grid, or fetch a viewport-capped individual-photo list and let Leaflet cluster it client-side?**

- **Context:** FR-067-09/FR-067-20/FR-067-21's original design grouped photos into the same grid `QueryMapBuckets` uses, showing a plain count-badge for any cell over `LEAF_THRESHOLD` and individual thumbnail markers only for cells at/under it. In practice (owner, comparing live screenshots against the v2/non-SoA map): this binary per-cell split never reproduced the v2 path's graduated cluster sizes with real photo thumbnails (`leaflet.markercluster`'s pixel-radius clustering, `composables/photo.ts`'s `clusterFunc()`) - a cluster just above threshold would have every one of its sub-cells drop below threshold in the very next zoom step (cell *area* quarters per zoom level), popping instantly from one badge to a scatter of individual markers instead of splitting gradually. Tightening the grid further (Q-067-13's `GRID_ZOOM_OFFSET`) could shrink the affected area but could never reproduce genuine graduated clustering - that's an inherent property of a fixed grid plus a single count threshold, not a tuning problem.
- **Options considered:** (A) Keep the per-cell grid/threshold split, tuning `GRID_ZOOM_OFFSET`/`LEAF_THRESHOLD` further and layering in extra grid resolutions for intermediate cluster sizes. (B) Drop per-cell grouping for the Photos tier entirely: `/Map/Photos` returns *every* distinct photo in the viewport, but only when the viewport's total count is `<=` a new `MAX_VIEWPORT_PHOTOS` cap; above it, it returns empty and the frontend falls back to `/Map/buckets`. The frontend hands a non-empty response straight to `clusterFunc()`/`Cluster.add()` - the exact same `leaflet.markercluster` client-side clustering the v2 path already uses - instead of pre-bucketing photos into a grid server-side.
- **Decision:** (B), per owner direction ("a mix between using the clustering of backend and using the Leaflet clustering"). The backend's only remaining job for this tier is the binary "is the viewport's total photo count small enough to ship individually" decision (`MAX_VIEWPORT_PHOTOS = 500`, chosen as a reasonable DOM-marker-count ceiling for Leaflet's own clustering, not derived from any grid geometry); actual clustering (graduated sizes, real thumbnails) is Leaflet's job, unchanged from v2. This deletes `QueryMapPhotos`'s entire leaf-cell join mechanism (`buildLeafCellsQuery()`/`resolveLeafRows()`) in favor of one `SELECT DISTINCT` query, reused once for a `COUNT()` and once (only if under cap) for the actual fetch. Frontend-side, `renderLeafMarkers()`'s manual per-marker placeholder-icon-then-swap logic is replaced by `renderIndividualPhotos()`, which does the same lazy `ThumbAssetService.acquire()` swap but per-marker via `L.Marker`'s own `refreshIconOptions(_, true)` (added by `leaflet.markercluster`), so a swapped-in thumbnail also redraws that marker's parent cluster if it's currently grouped - `MapPhotoResource` still supplies no thumbnail URL at all (Q-067-12 unaffected by this decision).
- **Resolution date:** 2026-09-16.
- **Spec impact:** FR-067-09, FR-067-20, FR-067-21, NFR-067-05, UI-067-01; supersedes the per-cell leaf-threshold framing FR-067-09/20/21 originally had. `LEAF_THRESHOLD` (Q-067-02) is retired for the Photos tier; `GRID_ZOOM_OFFSET` (Q-067-13) remains in use for the Buckets tier only.
- **Amendment (2026-09-16, review finding):** the initial implementation of this decision ran a separate `count()` pre-check before an unbounded row-fetch — a TOCTOU race where a photo added/moved into the viewport between the two queries could push the actual fetched row count past `MAX_VIEWPORT_PHOTOS`, both violating the documented cap and, at low zoom, forcing an unbounded `COUNT(*)` over a potentially huge bounding box regardless. Fixed by bounding the single row-fetch itself with `->limit(MAX_VIEWPORT_PHOTOS + 1)` and checking the fetched count against the cap afterward, eliminating both the race and the separate count query entirely.

**Q-067-17 — Now that the Photos tier can own the whole view (Q-067-16), should `fetchViewportNow()` keep fetching `buckets` in parallel on every viewport change?**

- **Context:** `fetchViewportNow()` fetched `/Map/buckets` and `/Map/Photos` via `Promise.all` unconditionally. Once Q-067-16 made `renderAggregateMarkers()` skip rendering entirely whenever `photosV3` is non-empty, every under-cap viewport (owner: "what is the point of bucket and counts if it is not used?") paid for a full backend bucket/count query and response body that was immediately discarded.
- **Options considered:** (A) Keep the parallel `Promise.all` fetch, accept the waste as the cost of not adding request sequencing. (B) Fetch `/Map/Photos` first; only fetch `/Map/buckets` if that response comes back empty (i.e. the viewport is actually over the cap and the aggregate view is genuinely needed).
- **Decision:** (B). The two fetches were never independent in the first place — `bucketsV3` is only ever rendered when `photosV3` is empty — so sequencing them costs nothing correctness-wise and removes a whole request+query in what the owner described as the common case. Trades a small latency increase in the rarer over-cap path (two sequential requests instead of two parallel ones) for no wasted work in the common one.
- **Resolution date:** 2026-09-16.
- **Spec impact:** FR-067-18; S-067-20.

**Q-067-18 — Should zooming in within an area already exhaustively fetched (Photos tier, under cap) trigger a fresh backend request?**

- **Context:** `Map.vue`'s `moveend`/`zoomend` listener calls `requestViewport()` on every settle, including a pure zoom-in with no pan. When the previous fetch already returned every photo in a *larger* area than the new (zoomed-in) viewport, every one of those photos is already sitting in `photosV3` — the new, smaller viewport's own photos are a strict subset of what's already held client-side (owner: "if we already have all the pictures in the current zoom selection, it does not make sense to requery again as we zoom in").
- **Options considered:** (A) Keep re-fetching on every settled viewport change, relying only on the existing snapped-key dedup (which only catches an *identical* snapped region, not a smaller one nested inside a previous fetch). (B) Track the real (unsnapped) bounds of the last exhaustive Photos fetch, and skip the request entirely when the new viewport is fully contained within them.
- **Decision:** (B). Leaflet already only draws markers within the current view, so photos now outside the smaller viewport simply aren't rendered — no client-side filtering is needed, just skipping the fetch. Antimeridian-crossing bounds are excluded from the containment check (treated as "not contained", falling through to a normal fetch) rather than getting the wraparound comparison right, since that's a rare case for this optimization to matter for. The tracked bounds are cleared whenever a fetch isn't exhaustive (over cap, or genuinely empty) and on album-scope change/`reset()`, so a later, larger-than-that viewport is never wrongly skipped.
- **Resolution date:** 2026-09-16.
- **Spec impact:** FR-067-18; S-067-21.

**Q-067-19 — Now that individual photos are handed to Leaflet's own clustering (Q-067-16), should every photo's thumbnail still be fetched eagerly?**

- **Context:** `renderIndividualPhotos()` (post Q-067-16) fetched every returned photo's thumbnail immediately via `ThumbAssetService.acquire()`, regardless of whether it ended up as a visible standalone marker or hidden inside a cluster badge — which only ever displays one representative child's image. Under a few hundred photos, most fetches were wasted (owner: "we are loading all the images even though some are clustered... shouldn't we only load the images when needed?").
- **Options considered:** (A) Keep eager fetching, accept the waste as the cost of simplicity. (B) Fetch a photo's thumbnail only once something on screen actually needs it: a cluster's representative child, or a standalone/spiderfied marker.
- **Decision:** (B). A custom `iconCreateFunction` (passed into `clusterFunc()`'s options, overriding only that one option via `L.Util.setOptions()`'s shallow merge — `composables/photo.ts` needed no changes) lazily fetches a cluster's representative child's thumbnail the first time that badge is actually drawn, then redraws it via `cluster.setIcon()` once resolved. A standalone/spiderfied marker's own Leaflet `'add'` event — fired exactly when that specific marker (not a cluster standing in for it) is placed on the map, whether that's immediately (synchronously, inside the same `.add()` call) or later on a future zoom — triggers the same fetch for it; both the immediate-add case (checked via the marker's own `_map` presence right after `.add()`) and the future case (`.once('add', ...)`) are handled, since `leaflet.markercluster`'s bulk-add path can place an already-standalone marker on the map before an event listener could be attached. A `requestedPhotoIds` guard prevents a photo shared between a standalone marker and a cluster badge (or several, across zoom levels) from leaking one `ThumbAssetService.acquire()`/`release()` pair per redundant call; `ThumbAssetService`'s own module-level cache already dedupes the underlying network request regardless. The `UNRESOLVED_THUMBNAIL` sentinel guards this lazy path from also firing for the v2 (non-SoA) path, which shares the same `Cluster`/`iconCreateFunction` machinery but already has a real, synchronously-available thumbnail URL and must never pay for a redundant fetch.
- **Resolution date:** 2026-09-16.
- **Spec impact:** FR-067-21; S-067-16, S-067-22.
- **Related compatibility fix:** The much higher volume of dynamic marker add/remove/icon-redraw activity this change (and Q-067-16/17/18) introduced exposed a pre-existing `leaflet@1.9.4` / `leaflet.markercluster@1.5.3` (its last release, 2021) incompatibility — a zoom-out cluster-merge animation could call a marker's zoom-animation callback after Leaflet had already cleared that marker's map reference mid-transition (`Cannot read properties of null (reading '_latLngToNewLayerPoint')`). Fixed by setting `animate: false` on the shared `Cluster` class (both v7 and v8 non-SoA share it too), switching `leaflet.markercluster` to its non-animated internal code path (`MarkerClusterNonAnimated`, confirmed via its own source) that never hooks the zoom-transition machinery responsible for the crash. Not spec-impacting on its own (a third-party library bug workaround, not a behavior change this feature specified), noted here for traceability.
