# Open Questions – Feature 067

Open questions for [Feature 067](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-067-21~~ | 067 – Map Geo-Bucketing | Medium | At high zoom (e.g. 14, ~38 m cells) a viewport holding slightly more than `MAX_VIEWPORT_PHOTOS` (564 vs 500 in the owner's sample) falls back to count-badge clusters although the area is small enough to show photos. When should the buckets tier stop clustering and return the photos themselves? | Resolved (Option B — `/Map/buckets` returns every photo in `singleton_photos` from zoom 14 up, capped at 2000; owner: "B"; FR-067-26) | 2026-10-08 | 2026-10-08 |
| ~~Q-067-20~~ | 067 – Map Geo-Bucketing | Medium | Above `MAX_VIEWPORT_PHOTOS`, the aggregate badges include many count-1 cells, drawn as a "1" cluster instead of a photo point. `MapBucketResource` has no photo/album id, so the frontend can't draw it as a photo. Where does the singleton's photo data come from? | Resolved (Option A — buckets response carries `singleton_photos`; owner, 2026-09-27; FR-067-25) | 2026-09-27 | 2026-09-27 |
| ~~Q-067-07~~ | 067 – Map Geo-Bucketing | High | FR-067-12 specs `should_downgrade` via `Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, ...)`, but today's `PositionData` classes use no Gate at all at root scope (raw `grants_full_photo_access` config) and `AlbumPolicy::CAN_ACCESS_FULL_PHOTO` (one check per request, not per photo) at album scope. | Resolved (superseded, not just fixed — Q-067-12: `should_downgrade` removed from this tier entirely; leaf-tier imagery is fetched from the existing v3 Asset endpoint, which never performs a full-vs-thumb split for the thumbnail-class variants Map uses) | 2026-09-13 | 2026-09-15 |
| ~~Q-067-08~~ | 067 – Map Geo-Bucketing | High | FR-067-02 specs a client-supplied `include_sub_albums` request boolean, but v2's `MapController::getData()` derives it server-side from the single **global** admin config `map_include_subalbums` (no per-album override row exists). | Resolved (Option A — drop the request param, stay config-derived; owner: "Q-67-08: A") | 2026-09-13 | 2026-09-15 |
| ~~Q-067-09~~ | 067 – Map Geo-Bucketing | Medium | FR-067-15/16 wire cache invalidation only to `PhotoSaved`/`PhotoMoved`/`PhotoDeleted`, with no invalidation path for map-relevant **candidate-scope** config changes (`hide_nsfw_in_map`, `map_include_subalbums`, `map_display`, `map_display_public`). | Resolved (Option A — add config-change invalidation mirroring Q-053-05, new FR-067-24/S-067-19; owner: "Q-67-9: A") | 2026-09-13 | 2026-09-15 |
| ~~Q-067-10~~ | 067 – Map Geo-Bucketing | Medium | FR-067-06's `cellSizeForZoom(int $zoom): float` is only described qualitatively ("e.g. halving per zoom level") — no base cell size, units, or concrete formula is pinned anywhere in spec/plan/tasks. | Resolved (Option A — pinned now: `cellSizeForZoom(int $zoom): float { return 360.0 / (2 ** $zoom); }`, one tile-width in degrees at Web-Mercator zoom `$zoom`; owner: "Q-67-10: A") | 2026-09-13 | 2026-09-15 |
| ~~Q-067-11~~ | 067 – Map Geo-Bucketing | High | Q-067-12 made `MapPhotoResource.album_ids[]` structurally required to be each photo's real, viewer-accessible containing album id — needed a tie-break rule for multi-album photos and sub-album exactness. | Resolved (Option B — album scope constrains the `photo_album` join to the query's own already-authorized subtree, no extra access re-check; root scope joins `base_albums`/`computed_access_permissions`, applies `AlbumQueryPolicy::appendAccessibilityConditions()`, and collapses via `GROUP BY`/`MIN()` — no `Album` model hydration at all, mirroring `ResolvesPhotoSource::resolvePhotoQuery()`'s `BaseSmartAlbum` collapse pattern; owner: "Q-67-11: B", mechanism refined same day: "You can resolve that with joins and collapse. See on timeline v3 photo selection.") | 2026-09-13 | 2026-09-15 |

## Question Details

### ~~Q-067-21~~ · Stop clustering at high zoom and return the photos ✅ RESOLVED

**Status:** Resolved by the owner, 2026-10-08 — Option B. Encoded in spec FR-067-26, S-067-24 and decision card Q-067-21.  
**Feature:** F-067  
**Priority:** Medium

Owner report (2026-10-08): `GET /api/v3/Map/buckets?north=51.9186&south=51.8700&east=4.4807&west=4.3932&zoom=14` returned 142 buckets (402 photos) plus 162 `singleton_photos`, i.e. 564 photos. The viewport is over `QueryMapPhotos::MAX_VIEWPORT_PHOTOS` (500), so `/Map/Photos` returned empty and the frontend rendered count badges (FR-067-20). At zoom 14 the user is looking at a few kilometres of city, and the owner expects photos, not clusters.

- **Option A (recommended) — a zoom-dependent cap on `/Map/Photos`.** Keep the two-tier flow (Photos first, buckets only when Photos is empty) but make the cap a pure function of zoom, like `cellSizeForZoom()`: for example `MAX_VIEWPORT_PHOTOS` stays 500 below zoom 14 and rises to 2000 from zoom 14 up (constants in `QueryMapPhotos`, no admin setting, per Q-067-02). The `->limit(cap + 1)` bound is kept, so NFR-067-01 still holds structurally. The frontend needs no change except that Leaflet's client-side clustering now lays out more markers. *Pros:* smallest change, no new response shape, cache keys already include zoom. *Cons:* the 2000 figure is a guess, and a dense viewport can still exceed it and fall back to badges (that is the intended safety net).
- **Option B — a zoom threshold in `/Map/buckets` itself.** From zoom N upward `QueryMapBuckets` skips the grid grouping and returns every photo in `singleton_photos` with empty bucket arrays (still capped). *Pros:* the frontend keeps one request path for aggregate data. *Cons:* a second photo-returning endpoint, duplicate cap logic, and it still costs two round trips (empty Photos, then buckets) for exactly the case that should need one.
- **Option C — an admin-configurable cap.** Same as A, but the cap is a config value. *Pros:* tunable per instance. *Cons:* reverses Q-067-02 (fixed constants), new config row, translations and docs for a value users cannot reason about.


### ~~Q-067-20~~ · Count-1 aggregate buckets should render as photo points ✅ RESOLVED

**Status:** Resolved by the owner, 2026-09-27 — Option A. Encoded in spec FR-067-05, FR-067-20, FR-067-25, S-067-23 and decision card Q-067-20.  
**Feature:** F-067  
**Priority:** Medium

Owner report (2026-09-27, screenshot of Rotterdam): once the viewport holds more than `QueryMapPhotos::MAX_VIEWPORT_PHOTOS` (500) photos, `/Map/Photos` returns empty and `Map.vue`'s `renderAggregateMarkers()` draws a count badge for every grid cell (FR-067-20), including many cells with a single photo. Those should be photo points (thumbnail marker, popup, click-through), not a "1" cluster. `MapBucketResource` only carries `bucket_ids`/`counts`/`centroid_*`, so the frontend has no photo id or album id for the Asset endpoint (`ThumbAssetService.acquire(album_id, photo_id)`).

- **Option A (chosen) — the buckets response also returns its singleton cells' photos.** `QueryMapBuckets` collects `MIN(id)` for cells with `COUNT(*) = 1`, drops those cells from the bucket arrays, and returns their photos in a new `singleton_photos` field with the same SoA shape as `MapPhotoResource` (`ids`/`album_ids`/`titles`/`taken_ats`/`latitudes`/`longitudes`). `album_ids` resolution (Q-067-15) moves from `QueryMapPhotos` into the shared `ResolvesMapPhotoSource` trait. The frontend feeds them into the existing photo-marker path (lazy thumbnails, popup, `.leaflet-marker-photo`), drawn next to the remaining count badges. *Pros:* real photo points that look and behave exactly like the below-cap path. Cost stays bounded by cell count (one extra `whereIn` fetch of at most one photo per cell), and the cache keys don't change. *Cons:* a backend + resource + TS type change. Neighbouring singletons can still be merged by Leaflet's own pixel-radius clustering into a thumbnail cluster (v2 look), which is arguably correct.
- **Option B — frontend only, a plain pin for count-1 badges.** `renderAggregateMarkers()` draws the default Leaflet marker instead of a "1" badge. A count-1 cell's centroid is the photo's exact position. Clicking still zooms in. *Pros:* a few lines, no API change. *Cons:* no thumbnail, no popup, no click-through, so it is not really a photo point.
- **Option C — ids only on the bucket arrays.** Add index-aligned `photo_ids[]`/`album_ids[]` to `MapBucketResource` (null unless `counts[i] === 1`), and render a thumbnail marker that opens the photo on click. *Pros:* a smaller payload change than A. *Cons:* no title/date for the popup, so it differs from the below-cap photo markers. It is also a second rendering path to maintain.

---

### ❓ Q-067-07 · `should_downgrade` gate class/granularity mismatch in the leaf tier ✅ RESOLVED (superseded)

**Status:** Resolved — superseded by Q-067-12 (spec.md Appendix), 2026-09-15: `should_downgrade` removed from `MapPhotoResource` entirely. The Asset endpoint's own `GetPhotoAssetRequest` docblock confirms there is no full-vs-thumb access split for thumbnail-class variants (`SizeVariantAssetType`) in the first place — the choice between Option A/B below became moot once the tier stopped computing `should_downgrade` at all. Neither option was taken; see spec.md's Q-067-12 for the actual resolution.  
**Feature:** F-067 – Map Geo-Bucketing  
**Preferred option:** 🅰️ (**recommended**) Option A – Reproduce the real per-scope mechanism

**Question**  
FR-067-12 specifies `should_downgrade` via `Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, ...)` "identically to today's `PositionData`-driven behavior." But `app/Actions/Albums/PositionData.php:70` (root scope) computes it from the raw config `grants_full_photo_access` — no `Gate::check()` call at all — and `app/Actions/Album/PositionData.php:51` (album scope) calls `Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album])`, a *single* check per request, not per photo. `PhotoPolicy::canAccessFullPhoto(?User, Photo)` (`app/Policies/PhotoPolicy.php:182`) is a genuinely different, more expensive check — it delegates to `AlbumPolicy::canAccessFullPhoto()` once per album the photo belongs to and reduces the results, which can disagree with the single-scope check for a photo shared into multiple albums with different permission grants. FR-067-10's own "mirrors `QueryPhotoDetails`'s documented precedent" framing points at `QueryPhotoDetails.php:214`, which *does* use the per-photo `PhotoPolicy` check — so the spec may have simply copied that precedent without checking whether `PositionData`'s actual mechanism matches it. It doesn't.

---

#### 🅰️ (**recommended**) Option A – Reproduce today's real per-scope mechanism
- **Idea:** Root scope: `should_downgrade = !config('grants_full_photo_access')`, no Gate call. Album scope: one `Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, $album])`, computed once per request and applied uniformly to every leaf photo in the response — never per-photo.
- **Spec impact:** FR-067-12, FR-067-10, DO-067-06 corrected to name `AlbumPolicy::CAN_ACCESS_FULL_PHOTO` (album scope) / raw config (root scope), not `PhotoPolicy::CAN_ACCESS_FULL_PHOTO`; NFR-067-05's cross-reference to `QueryPhotoDetails` narrowed to "bounded hydration," not "permission-check granularity."
- **Pros:**  
  - ✅ Byte-identical to today's v2 behavior — actually satisfies FR-067-12's own stated goal.  
  - ✅ One cheap check per request instead of one Gate call per photo in the hydration loop.
- **Cons:**  
  - ❌ A photo shared into multiple albums with differing grants keeps today's coarser (arguably less correct) single-scope answer.

---

#### 🅱️ Option B – Genuine per-photo `PhotoPolicy::CAN_ACCESS_FULL_PHOTO`, as literally specced
- **Idea:** Keep FR-067-12 exactly as written — call `Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo])` for each hydrated leaf photo.
- **Spec impact:** A deliberate, called-out behavior change from v2's `PositionData` classes, not mere parity — needs its own FR/NFR framing instead of being presented as "identical."
- **Pros:**  
  - ✅ More correct for photos shared into multiple albums with different grants.  
  - ✅ Consistent with `QueryPhotoDetails`'s own precedent for bounded per-row tiers.
- **Cons:**  
  - ❌ N Gate calls in the leaf-tier hydration loop, each walking `$photo->albums`.  
  - ❌ Contradicts FR-067-12's own "identically to today's `PositionData`-driven behavior" framing — silently changes visible size-variant access for some viewers.

---

**Next action**  
Feature owner to choose A or B before I4 (`QueryMapPhotos::do()`) implementation; update FR-067-10/FR-067-12/DO-067-06 accordingly.

---

### ❓ Q-067-08 · Client-controlled `include_sub_albums`, or server-derived from the existing global config? ✅ RESOLVED

**Status:** Resolved (Option A, 2026-09-15 — owner: "Q-67-08: A"; encoded in spec.md's FR-067-02/FR-067-04 and Appendix Q-067-08)  
**Feature:** F-067 – Map Geo-Bucketing  
**Preferred option:** 🅰️ (**recommended**) Option A – Drop the request param, stay config-derived

**Question**  
FR-067-02 specifies a new client-supplied `include_sub_albums` (`sometimes|boolean`) request parameter for `GetMapBucketsRequest`/`GetMapPhotosRequest`. But `MapController::getData()` today (`app/Http/Controllers/Gallery/MapController.php:51`) derives this value server-side from `$request->configs()->getValueAsBool('map_include_subalbums')` — and that key is a single **global**, admin-only config row (`database/migrations/2019_10_07_0900_config_map_include_sub_albums.php`), not a per-album override and not something any client has ever been able to set per-request. No other `include_sub_albums`-shaped setting in this codebase (e.g. `flow_include_sub_albums`, `app/Actions/Albums/Flow.php:61`) is client-controlled either — they're all config-only. FR-067-02 would turn a global admin toggle into a per-viewer, per-request choice with no stated rationale for the change.

---

#### 🅰️ (**recommended**) Option A – Drop the request param, stay config-derived
- **Idea:** `resolveAlbumQuery()`'s `$includeSubAlbums` bool comes from `$request->configs()->getValueAsBool('map_include_subalbums')` server-side, exactly like today; `GetMapBucketsRequest`/`GetMapPhotosRequest` drop the `include_sub_albums` rule entirely.
- **Spec impact:** FR-067-02 loses the `include_sub_albums` validation rule; FR-067-04 unchanged in substance.
- **Pros:**  
  - ✅ Zero behavior change from v2.  
  - ✅ Consistent with every other config-derived toggle in this codebase.
- **Cons:**  
  - ❌ No way for a future frontend UI to offer a per-viewing sub-album toggle without another spec change.

---

#### 🅱️ Option B – Keep it client-controlled, as specced
- **Idea:** Ship FR-067-02 as written — the request always decides, the global config is no longer consulted for the v3 map path.
- **Spec impact:** A genuinely new capability (per-request sub-album toggle) requires its own FR framing and frontend UI decision (where does the toggle live in `Map.vue`?), not just a validation-rule line.
- **Pros:**  
  - ✅ More flexible; a future UI toggle "needs no further backend change.
- **Cons:**  
  - ❌ Unrequested scope expansion — turns a global admin setting into per-viewer state with no UI ever specced to set it (FR-067-19 never mentions such a control).  
  - ❌ First client-controlled `include_sub_albums` anywhere in the codebase, no precedent to follow.

---

#### 🅲 Option C – Optional param, falls back to the config default
- **Idea:** Accept `include_sub_albums` when the client sends it, otherwise fall back to `map_include_subalbums`'s current value — matches FR-067-02's `sometimes` rule literally.
- **Spec impact:** Same UI-control gap as Option B (nothing in FR-067-17..22 ever sends this param), but degrades safely to today's behavior when omitted.
- **Pros:**  
  - ✅ Backward-compatible default.  
  - ✅ Leaves the door open for a future toggle.
- **Cons:**  
  - ❌ Dead capability today — nothing in the frontend increments (I7/I8) is specced to ever populate it.

---

**Next action**  
Feature owner to confirm whether a per-request sub-album toggle is actually wanted before I1 (`GetMapBucketsRequest`/`GetMapPhotosRequest`); if not, correct FR-067-02.

---

### ❓ Q-067-09 · No map-cache invalidation path for config changes ✅ RESOLVED

**Status:** Resolved (Option A, 2026-09-15 — owner: "Q-67-9: A"; encoded in spec.md's new FR-067-24/S-067-19 and Appendix Q-067-14)  
**Feature:** F-067 – Map Geo-Bucketing  
**Preferred option:** 🅰️ (**recommended**) Option A – Add config-change invalidation

**Question**  
FR-067-15/16 wire the new map caches' invalidation only to `PhotoSaved`/`PhotoMoved`/`PhotoDeleted`. Nothing evicts the map cache when a map-relevant **candidate-scope config** changes — `hide_nsfw_in_map`, `map_include_subalbums`, `map_display`, `map_display_public`. This project has an explicit precedent for exactly this gap: Q-053-05 added a coarse "flush all on global config change" rule for the sibling album-listing cache, for the same reason (no per-row hook to invalidate against a global setting). (This question originally also raised a `grants_full_photo_access`/per-share-permission-grant angle, driving `should_downgrade` staleness — that angle is now moot: Q-067-12 removed `should_downgrade` from this tier entirely, and the Asset endpoint's own authorization is checked fresh on every request, never cached, so a permission-grant change can never leave a stale *authorization* result in the map cache. Only the candidate-scope config keys above still matter.) Separately — a smaller, related precision gap: FR-067-16's own motivating example ("a location ... edit ... invalidates its scope's ... cache") cannot happen today. No endpoint writes `latitude`/`longitude` on an existing photo (only `app/Actions/Photo/Pipes/Shared/GeodecodeLocation.php`/`HydrateMetadata.php` set it, at import time); and the one `PhotoSaved` dispatch site that fires on user edits, `PhotoController::update()` (`app/Http/Controllers/Gallery/PhotoController.php:217`), gates on `$photo->wasChanged(['title', 'title_base', 'created_at', 'taken_at'])` — latitude/longitude aren't in that list, so even a hypothetical future location-edit reusing this same call site wouldn't fire `PhotoSaved` unless one of those four columns also changed.

---

#### 🅰️ (**recommended**) Option A – Add config-change invalidation, mirroring Q-053-05
- **Idea:** A config-change listener (or extending the existing one, if any) evicts the coarse root map-cache tag plus every warm album-scope map-cache tag whenever any of the four listed config keys change.
- **Spec impact:** New FR under I6, alongside FR-067-15/16; a coarse "flush everything warm" tag list, same cost class as Q-053-05's `album-listing-global` tag.
- **Pros:**  
  - ✅ No indefinitely-stale NSFW-visibility/candidate-scope results after an admin changes a relevant setting.  
  - ✅ Directly reuses an already-accepted pattern in this codebase (Q-053-05).
- **Cons:**  
  - ❌ A config change now does more work (cache flush) than today's "just update the row."

---

#### 🅱️ Option B – Accept TTL-only staleness for this dimension
- **Idea:** Document that config-driven map staleness resolves only at TTL expiry, same as any other un-invalidated cache dimension.
- **Spec impact:** NFR-067-XX documenting the accepted gap explicitly, not silently.
- **Pros:**  
  - ✅ No new invalidation code to write/test.
- **Cons:**  
  - ❌ `hide_nsfw_in_map` toggled off leaves NSFW photos hidden from the map cache until TTL — the exact kind of visibility bug Q-053-05 was written to prevent for the sibling feature.

---

**Next action**  
Feature owner to choose A or B before I6 (`CacheKeyProvider`/`ManagedCachePhotoListingInvalidator` work); if B, correct FR-067-16's location-edit wording to not overclaim reachability it doesn't have.

---

### ❓ Q-067-10 · `cellSizeForZoom()` has no concrete formula anywhere ✅ RESOLVED

**Status:** Resolved (Option A, 2026-09-15 — owner: "Q-67-10: A"; pinned as `cellSizeForZoom(int $zoom): float { return 360.0 / (2 ** $zoom); }`, encoded in spec.md's FR-067-06 and Appendix Q-067-13. Empirical spot-check against the ~100k-photo fixture deferred to I3/I4 implementation, flagged pending like this feature's other manual-verification gaps.)  
**Feature:** F-067 – Map Geo-Bucketing  
**Preferred option:** 🅰️ (**recommended**) Option A – Pin concrete constants in the spec now

**Question**  
FR-067-06 requires `cellSizeForZoom(int $zoom): float` to be "a pure function of `zoom` only (e.g. halving per zoom level)" — but no base cell size, unit convention, or exact formula appears anywhere in spec.md, plan.md, or tasks.md. Every other fixed constant this feature introduces (`LEAF_THRESHOLD = 20`, Q-067-02) got a real number; this one didn't. Yet S-067-01 ("bounded by distinct cells in the requested viewport"), NFR-067-01's scale verification, and T-067-01's `cellSizeForZoom()` monotonicity test all require concrete values to actually test against.

---

#### 🅰️ (**recommended**) Option A – Pin concrete constants in the spec now
- **Idea:** Decide and write down, e.g., a base cell size in decimal degrees at `zoom=0` and a halving exponent (`cellSize = BASE_DEGREES / 2 ** zoom`), chosen so typical real-world photo density keeps leaf-cell counts near `LEAF_THRESHOLD` — sanity-checked against the ~100k-photo fixture already planned for NFR-067-01.
- **Spec impact:** FR-067-06 gains real numbers; T-067-01/S-067-01 become concretely testable as specced.
- **Pros:**  
  - ✅ Matches this spec's own pattern of deciding fixed constants upfront (Q-067-02) rather than deferring.  
  - ✅ Testable before any fixture exists — the formula's shape doesn't require live data to define, only to tune.
- **Cons:**  
  - ❌ The chosen numbers are still a guess until validated against real density; may need a follow-up correction.

---

#### 🅱️ Option B – Leave exact constants to implementation-time tuning
- **Idea:** Spec keeps only the qualitative "halving per zoom" shape; I1/I3 picks and empirically tunes real numbers once the 100k-photo fixture exists.
- **Spec impact:** None now; plan.md's I1/I3 steps gain an explicit "choose and record `BASE_DEGREES`" sub-step.
- **Pros:**  
  - ✅ Avoids committing to numbers before any real density data exists.
- **Cons:**  
  - ❌ T-067-01's monotonicity test and S-067-01 can't be written as "failing tests staged before implementation" (this plan's own stated TDD convention) without picking numbers first anyway — Option B mostly just moves the same decision one increment later without saving real effort.

---

**Next action**  
Feature owner to pin `BASE_DEGREES`/halving formula (or explicitly defer per Option B) before I1's `MapViewportTest` is written.

---

### ❓ Q-067-11 · `album_ids[]` per-photo resolution — now load-bearing, not just a UX nicety ✅ RESOLVED

**Status:** Resolved (Option B, 2026-09-15 — owner: "Q-67-11: B"; encoded in spec.md's FR-067-10 and Appendix Q-067-15. Concretely: album scope constrains the `photo_album` join to the query's own already-authorized scope — requested album, or its `_lft`/`_rgt` subtree when `include_sub_albums`, no extra per-sub-album access check, mirroring `all_photos()`'s existing behavior; root scope joins `photo_album` → `base_albums` → `computed_access_permissions`, applies `AlbumQueryPolicy::appendAccessibilityConditions()` (the query-builder form of `AlbumPolicy::canAccess()`, no `Album` model required), and collapses the resulting one-row-per-membership fan-out to one row per photo via `GROUP BY photos.id` + `MIN(photo_album.album_id)` — mirroring `ResolvesPhotoSource::resolvePhotoQuery()`'s existing `BaseSmartAlbum` branch, which collapses the same kind of fan-out via a `whereIn` existence test; `MIN()` is used here instead since the winning album id itself must survive. No Eloquent hydration anywhere in this tier, at either scope — mechanism corrected same day per owner follow-up: "You can resolve that with joins and collapse. See on timeline v3 photo selection.")  
**Feature:** F-067 – Map Geo-Bucketing  
**Preferred option:** 🅰️ (**recommended**) Option A – First viewer-accessible album via the `photo_album` join

**Question**  
Originally framed as a UX-quality gap (today's `PositionDataResource` stamps the scope's own `$album_id` — `null` at root — onto every photo, rather than its real containing album). Q-067-12 (spec.md Appendix) upgraded this from optional to **mandatory**: `MapPhotoResource.album_ids[]` now feeds directly into `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` requests (Q-067-12's Asset-endpoint delegation), and `GetPhotoAssetRequest::isPhotoOfAlbum()` checks direct `photo_album` pivot membership against exactly the `album_id` given — no subtree walk, no fallback to "root". A `null` (or wrong) `album_id` there doesn't degrade gracefully to today's behavior; it 404s that marker's thumbnail outright. Two concrete sub-problems remain: (1) a photo belonging to multiple albums needs a tie-break when more than one is viewer-accessible; (2) at album scope with `include_sub_albums=true`, the resolved id must be the photo's own direct (sub-)album — not the originally-requested top album — since `isPhotoOfAlbum()` doesn't walk descendants either.

---

#### 🅰️ (**recommended**) Option A – First viewer-accessible album via the `photo_album` join
- **Idea:** In `QueryMapPhotos`'s leaf-cell `toBase()` pass, join `photo_album` and pick one album per photo — the first one both (a) present in that join and (b) passing `AlbumPolicy::CAN_ACCESS` for the current viewer. Deterministic tie-break (e.g. lowest album `id`/`_lft`) when more than one qualifies.
- **Spec impact:** FR-067-10 gains the `photo_album` join + an access filter; `album_ids[]` is `null` only if genuinely no accessible album exists (shouldn't occur, since the photo already passed the scope's own searchability filter).
- **Pros:**  
  - ✅ Always resolves to something the Asset endpoint will actually accept — no silent 404s.  
  - ✅ One join, still bounded by `(leaf cells) × LEAF_THRESHOLD` — no new unbounded query.
- **Cons:**  
  - ❌ The tie-break is arbitrary when a photo is genuinely multi-album — the "wrong" (but still valid/accessible) album may be shown in a breadcrumb-adjacent context later, if one is ever added.

---

#### 🅱️ Option B – Prefer the album that produced this photo in the current query's own scope
- **Idea:** Same join, but when resolving album scope's own subtree (or root's `resolveRootQuery()`), prefer the album that actually matched *this request's* candidate query over an unrelated album the photo also happens to belong to — falls back to Option A's rule only when the "natural" album isn't independently resolvable cheaply within the same `toBase()` pass.
- **Spec impact:** Same join as Option A, plus scope-aware preference logic.
- **Pros:**  
  - ✅ More intuitive result for the common case (album-scope browsing, sub-albums included).
- **Cons:**  
  - ❌ More logic for a case (root scope, multi-album photo) where "the natural album" isn't well-defined anyway — Option A already resolves correctly for the actually-load-bearing requirement (a working Asset URL), just with a less curated tie-break.

---

**Next action**  
Feature owner to choose before I4 (`QueryMapPhotos::do()`) — this is now a hard implementation blocker for FR-067-09/Q-067-12, not an optional refinement.
