# Feature 103 – Album Load Speedup

| Field | Value |
|-------|-------|
| Status | In progress |
| Last updated | 2026-10-28 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #103 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Opening an album of 5,000–7,000 photos in v8 with struct-of-arrays takes more than 4 s on the owner's server (Xeon E3-1270 v6, 4 cores / 8 threads, 32 GB, Docker image on Octane). Two requests on the critical path that describe the album, not its photos, load every visible photo of the album: the HTML page `/gallery/{album_id}` (Open Graph image) and `GET /api/v2/Album::head` (frame availability, header image). This feature removes that per-photo work, and cuts the v8 main-thread work done for every tile between the photo tiers' responses and the first tile (Q-103-01). The order of the album-open requests stays as it is. By owner directive (2026-10-28) the feature also covers the v8 photo view on struct-of-arrays sources (a video opened from the grid fails until reload, fast navigation shows broken images, too little is preloaded) and the Timeline page with a large library, and the v8 bulk album edit page (icon headers for the switch columns, capture range of each album on its creation date; v8 only, Q-103-05).

## Baseline
Measured 2026-10-28 on a scratch instance: SQLite, opcache on, Xdebug off, `CACHE_STORE=array` (every request misses the managed cache), PHP built-in server with 8 workers pinned to CPUs 0–7 (4 cores / 8 threads of an i7-13700H, each faster than a core of the production Xeon). Logged-in admin, regular root album, photos from the factories with `taken_at` spread over 2015–2026 (3,451 day buckets), timeline at day granularity, `mod_frame_enabled` on, no `header_id`. Production build of the frontend, Chromium via Playwright, 1600×1000 viewport.

Requests alone (median of 5):

| Request | Empty album | 7,000 photos |
|---------|-------------|--------------|
| `GET /gallery/{album_id}` (HTML) | 25 ms | 196 ms |
| `GET /api/v2/Album::head` | 32 ms | 198 ms |
| `GET /api/v3/Albums/{album_id}/Photos/buckets` | 22 ms | 30 ms (99 KB) |
| `GET /api/v3/Albums/{album_id}/Photos` | 21 ms | 79 ms (1.25 MB) |

Full page load of the 7,000-photo album (median of 5, ms since navigation; "first tile" is when a `MutationObserver` in the page sees the first `a.photo` inserted, which immediately precedes the `Photos/urls` request):

| Step | Start → end |
|------|-------------|
| HTML page | 0 → 196 |
| JS bundles and boot requests (`Gallery::Init`, `Auth::user`, …) | 196 → ≈430 |
| `Album::head` | 430 → 631 |
| Work after `Album::head`, then `Photos/buckets` + `Photos` (12 other requests in flight) | 631 → 845 |
| Tile adaptation, layout, date scrubber | 845 → ≈1,070 |
| First tile in the DOM | ≈1,070 |
| `Photos/urls`, then the tile images | ≈1,070 → ≈1,230 |

Where the per-photo time of the HTML page and `Album::head` goes (same instance, one in-process request at 6,000 photos; MariaDB 11.4 took 133 ms of SQL for `Album::head` against 58 ms on SQLite):

| Caller | Query | ≈ms |
|--------|-------|-----|
| `AlbumConfig::hasFramePhotos()` (`Album::head`, when `mod_frame_enabled`) | `$album->get_photos()->count() > 0`: every visible photo of the album, sorted and hydrated | 37 (SQL) + hydration |
| `HasHeaderUrl::getHeaderUrl()` (`Album::head` and `Meta::getAlbumOgImageUrl()`) | `$album->get_photos()->isEmpty()`: same load when not done yet | as above |
| `HasHeaderUrl::getByQuery()` | `count(*)` then `offset` on `size_variants` with `whereBelongsTo($photos)`: two queries binding one id per photo | 14 + 7 |

Main-thread work in the browser after the photo tiers arrive (the three long tasks after `Photos`, ≈385 ms with `Photos/urls`): `loadPhotosV3()` adapting 7,000 tiles (dates parsed and formatted for every tile, `rebuildNavigationLinks()`, `setPhotos()`), `computeVisiblePhotoLayout()` (one WASM layout call per bucket), the date scrubber (`buildRailTicks()`, `separatorDotTops()`, day keys of every tile), and Vue reactivity on every tile object.

## Goals
- `/gallery/{album_id}` and `Album::head` do no work proportional to the album's photo count.
- v8 does no per-tile work through Vue reactive proxies, and formats no date of a tile it does not show, between the photo tiers' responses and the first tile.
- The targets of NFR-103-01 are met on the scratch instance.
- The Timeline's requests cost in proportion to what they return, except the whole-library `buckets` count.
- No photo or album query without an origin checks the NSFW ancestry of each album row with a correlated subquery: the answer is stored on the album (ADR-103-01).
- The v8 photo view never renders a photo, video or PDF element without its URL: a loading indicator shows until the photo's details and image are there, and the photos ahead are fetched and preloaded early enough for fast navigation.
- On the v8 bulk album edit page, the six switch columns are headed by an icon with the column's label as tooltip, and hovering an album's creation date shows the earliest and latest `taken_at` of its photos, sub-albums included.

## Non-Goals
- The photo tiers (`Photos/buckets`, `Photos`, `Photos/details`, `Photos/urls`) and their payloads; Feature 102 owns `/Asset` and `Photos/urls`.
- Requests fired at page boot that do not depend on the album (`Gallery::Init`, `Gallery::Footer`, `LandingPage`, `Import`, …).
- Which photo becomes the header or Open Graph image: the selection rules stay as they are (FR-103-02).
- v2 photo listing (`Album::photos`) and the struct-of-arrays-off path.
- The order of the album-open requests: the photo tiers are still requested after `Album::head` (Q-103-01).
- The WASM layout primitives (`@lychee-org/layouts`): still one call per bucket.
- Vue component mounting and rendering of the tiles in view.
- The v7 bulk album edit page and `GET /api/v2/BulkAlbumEdit` (`BulkAlbumResource`); editing capture dates; the other bulk edit headers (Q-103-05).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-103-01 | `AlbumConfig` decides frame availability with an `exists` query over the album's photos visible to the caller (`AbstractAlbum::photos()`: the photo relation, or a smart album's query, with its visibility filter, no sorting, no hydration). `mod_frame_include_subalbums` keeps its `all_photos()` `exists` query. | `is_mod_frame_enabled` true when `mod_frame_enabled` is on and at least one visible photo exists; no `Photo` model is loaded. | — | — | — | Baseline, owner report 2026-10-27 |
| FR-103-02 | `HasHeaderUrl::getHeaderUrl()` picks the header without loading the album's photos: emptiness is an `exists` query, and the header is one random photo drawn in SQL from the album's visible photos (`AbstractAlbum::photos()`) that have a landscape (`ratio > 1`) `medium2x`, `medium`, `small2x` or `small` variant (`EXISTS` per photo), else from those with any such variant, by `count` then a random `offset` with `limit 1` (no `ORDER BY RAND()`, Q-103-03); its first variant above the original is returned. No bound id list. Every photo has the same chance (not weighted by its number of variants). Every album type goes through `photos()`, smart albums included, so a smart album's candidates are all its visible photos rather than its first page. The rules for `header_id`, `compact`, the map header and `use_album_compact_header` are unchanged. Applies to `Album::head` (all four head resources) and the Open Graph image of `/gallery/{album_id}` (`sm_card_album_source = header`). | Same header candidates as before; no `Photo` model loaded, no query binding one parameter per photo. | — | No visible photo, or none with such a variant → no header (as before). | — | Baseline, owner report 2026-10-27 |
| FR-103-03 | v8 struct-of-arrays album path: `loadPhotosV3()` adapts the `Photos` rows into plain tiles that already carry `next_photo_id`/`previous_photo_id` from array order (FR-065-15) and hands them to the photos store in one assignment, with no navigation-link pass over the store's array. `PhotosState` records the page of a batch of photos in `photoPageMap` by assigning one new map, not one reactive write per photo, for every writer (`setPhotos`, `appendPhotos`, `prependPhotos`). | Same `next_photo_id`/`previous_photo_id` and the same `?page=N` photo links as before. | — | — | — | Q-103-01 |
| FR-103-04 | `adaptPhotoTile()` formats `preformatted.created_at`, `preformatted.taken_at` and `preformatted.date_overlay` the first time each is read, then keeps the string; assigning one replaces it. The properties stay own and enumerable, so spreading or serializing a tile yields the formatted strings. Same formats (`Gallery::Init`) and zones as today. Applies to every caller (album, timeline, search, flow, photo view). | A tile never shown formats none of its dates; a shown tile gets the same strings as before. | — | — | — | Q-103-01 |
| FR-103-05 | `computeVisiblePhotoLayout()` lays the album's tiles out from the raw (non-reactive) photo array and returns, for each positioned tile, its index in `PhotosState.photos` instead of the photo object; `PhotoGridVirtual` passes the store's reactive photo of that index to each rendered tile and `dragAndSelect`'s hit-testing reads ids by index. No reactive proxy or dependency is created per photo by the layout. Rating filter (FR-065-19) and drag selection behave as today; the layout still recomputes when the photo array is replaced or the rating filter changes. | Same boxes, headers and selection as before; a star, rating or detail change on a shown tile still updates it in place. | — | — | — | Q-103-01 |
| FR-103-06 | The album date scrubber (Feature 071) computes the day or hour key of each tile once per photo array, scrubber field and granularity; a new layout of the same photos (width change, rating filter) re-pairs those keys with the new tile tops without recomputing them. | Same rail entries and labels as before. | — | — | — | Q-103-01 |
| FR-103-07 | v8 photo view (`PhotoBox`), every struct-of-arrays source (album, timeline, search, flow): while the shown photo's details are not merged (its `size_variants` has neither `original` nor `thumb`, the state `adaptPhotoTile()` leaves), the view shows a loading indicator and mounts no `<img>`, `<video>` or `<embed>`; once merged, the element mounts with its URL, so a video's `<source>` carries its URL from creation. While a shown image loads, the loading indicator stays over the small-variant background until the image's `load` event. | A video opened from the grid plays without reload; fast navigation shows the indicator, never a broken image. | — | Details request failed for the shown photo → error placeholder instead of the indicator; showing the photo again retries the request. Image `error` event → error placeholder. | — | Owner report 2026-10-28 |
| FR-103-08 | Every photo shown through `PhotoState.load()` (album, timeline, search) requests, in one details request with the shown photo, the details of the 10 next and 5 previous photos in grid order (`photosStore.photos`) that are not merged or in flight, instead of the two direct neighbours. Once their details are merged, the browser preloads the image the view would show (same `srcset`/`sizes` or original as `PhotoBox`) for the 3 next photos and the previous one. Videos and PDFs are not preloaded. | Navigating forward within the window shows each photo from cache. | — | Failed details request → those ids stay unmerged and are requested again by the next `load()`. | — | Owner report 2026-10-28 |
| FR-103-09 | Smart-album sources (the Timeline included) of the struct-of-arrays queries (`ResolvesPhotoSource`): the album's visible-photo id subquery carries the request's own scope. `Photos` with `bucket_ids[]` or `photo_ids[]`, `Photos/details` and `Photos/urls` apply their bucket window or id list inside that subquery as well as on the outer query; `Photos/buckets` counts `DISTINCT photos.id` over the album's visible-photo query directly, without the id subquery. | Same rows, counts and order as before; a photo in several albums appears and counts once. A window or id lookup costs in proportion to its size, not to the library. | — | — | — | Owner report 2026-10-28 (Timeline) |
| FR-103-10 | v8 Timeline layout (`computeTimelinePhotoLayout()`): the placeholder height of a bucket whose photos are not loaded is laid out once per distinct photo count in a layout pass, not once per bucket (the uniform `1.0`-ratio placeholder of NFR-066-05 depends only on the count, mode, width, target and gap). | Same heights as before; a library of 6,143 day buckets with 30 distinct counts runs 30 placeholder layouts. | — | — | — | Owner report 2026-10-28 (Timeline) |
| FR-103-11 | `albums.is_recursive_nsfw` (boolean, not null, default false) is stored and is true exactly when the album or one of its ancestors has `is_nsfw` (ADR-103-01, Q-103-04). It is written explicitly, never by model events or mutators, by one action (`App\Actions\Album\SyncRecursiveNsfw::do()`) that reads the `_lft`/`_rgt` of every NSFW album, keeps the outermost ranges, and in two `UPDATE` statements clears the flag of flagged albums outside them and sets it on unflagged albums inside them, so only changed flags are written and any stale flag is repaired: a new album is flagged when its parent or one of the parent's ancestors is NSFW, read from `base_albums.is_nsfw` along the tree with the parent's bounds reloaded under the tree lock, never from the parent's stored flag, so a stale parent flag is not passed on (`Album\Create`, which every creator goes through; `SyncRecursiveNsfw::isRecursiveNsfw()`); the sync runs after every change of `is_nsfw` (`SetProtectionPolicy` when the value changes, `BulkEditAlbumsAction` when `is_nsfw` is in the payload, `ApplyNsfwAlbumSensitivityJob`), of an album's parent (`Move`, `Merge`, `Transfer` to root) and of the tree (`lychee:fix-tree`, maintenance full-tree update); the migration that adds the column backfills it from the same ranges. Tag and person albums have no ancestry and no such column. | After each write, every album's flag equals the ancestry rule. | — | — | — | Q-103-04 |
| FR-103-12 | Sensitivity checks without an origin read `albums.is_recursive_nsfw` on the album row they already join instead of `AlbumQueryPolicy::appendRecursiveSensitiveAlbumsCondition()`: the photo condition of `PhotoQueryPolicy` with `origin = null` passes a row whose album is not flagged or that has no album (Timeline, search, smart, tag and person albums, tag listing, map, landing page, RSS and embed photos); the RSS album list (`hide_nsfw_in_rss`); Flow without `flow_base` (`hide_nsfw_in_flow`); the `is_recursive_nsfw` of Flow items and of `ApplyNsfwAlbumSensitivityJob`, which read the stored column (no computed select). Checks with an origin (photos below an album, album covers and statistics, frame sub-albums, Flow with `flow_base`) keep the correlated condition, since NSFW albums at or above the origin do not count there. | Same photos and albums visible, in the same order, as with the correlated condition; a photo in an NSFW album and in a non-NSFW album stays visible through the latter. | — | — | — | Q-103-04 |
| FR-103-13 | On the v8 bulk album edit page (`resources/js/v8/views/BulkAlbumEdit.vue`), the header of each switch column is an icon with a tooltip whose text is the column's label (`bulk_album_edit.col_*`, unchanged): Sensitive `lucide:triangle-alert`, Public `lucide:eye`, Link `lucide:link`, Full Photo `lucide:app-window`, Download `lucide:cloud-download`, Upload `lucide:upload`. The icons are the ones the v8 album visibility form (warning), album thumb badges (public) and Sharing page (full photo, download, upload) already use for the same concept. The icon carries the label as its accessible name (`role="img"`, `aria-label`), as on the Sharing page. | Same columns, widths and switches; header shows the icon, hovering it shows the label. | — | — | — | Owner request 2026-10-28, Q-103-05 |
| FR-103-14 | The album list for bulk edit (`GET /api/v3/Albums?for_bulk_edit=1`, `AlbumListBulkEditFieldsResource`) carries `min_taken_ats` and `max_taken_ats`: one stored datetime string (same format as `created_ats`) or `null` per album, from `albums.min_taken_at` / `albums.max_taken_at` (photos of the album and of its sub-albums, kept by `RecomputeAlbumStatsJob`). | Arrays aligned with `ids`. | — | Album without photo with `taken_at` → `null` for both. | — | Owner request 2026-10-28 |
| FR-103-15 | The creation date cell of the v8 bulk album edit page shows, on hover, a tooltip `bulk_album_edit.taken_range` ("Taken: :range", translated in every locale) where `:range` is "from – to" (sub-albums included), both dates formatted as the cell's own date, or a single date when both fall on the same day (`v8/utils/takenRange.ts`). Without capture date the tooltip reads `bulk_album_edit.taken_none` ("No data", same wording per locale as the album statistics' "No data"). | Information only, no action. | — | — | — | Owner request 2026-10-28 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-103-01 | On the scratch instance of the Baseline (7,000-photo album, PHP server pinned to CPUs 0–7, median of 5 runs): `/gallery/{album_id}` and `Album::head` take at most 15 ms more than the same requests on an empty album, and the first tile is in the DOM ≤ 800 ms after navigation on a full page load. The owner compares on the production server after deployment. | Albums of 5,000–7,000 photos on a 4-core server. | The Baseline harness, rerun after each increment, numbers recorded in the plan. | Scratch instance | Q-103-02 |
| NFR-103-03 | Timeline on the scratch instance (≈99,000 photos in 47 albums, 6,143 day buckets, PHP server pinned to CPUs 0–7, median of 3–5 runs): one `Photos` window and one `Photos/urls` request ≤ 50 ms each (620 / 495 ms before), `Photos/buckets` ≤ 250 ms (650 ms before), first tile in the DOM ≤ 1,000 ms after navigation (≈1,760 ms before). | Libraries of 100,000+ photos on a 4-core server. | The Baseline harness on `/timeline`, numbers recorded in the plan. | Scratch instance | Owner report 2026-10-28 |
| NFR-103-02 | The number of queries of `Album::head` and `/gallery/{album_id}` does not grow with the album's photo count, and no query binds one parameter per photo. | Per-photo work on the critical path. | Feature test: same query count for an album of 2 and of 20 visible photos; zero `Photo` models retrieved. | — | Baseline |
| NFR-103-04 | Bulk album edit: no extra request and no extra query for the capture range; the two dates are selected by the existing bulk-edit list query. | Pages of thousands of albums. | Feature test: one listing query reads the columns. | — | Owner request 2026-10-28 |
| NFR-103-05 | Bulk album edit icons work offline: they come from the bundled icon set v8 already uses. | Offline-only requirement. | No new icon set or remote asset. | — | Owner rule |

## UI / Interaction Mock-ups
No visual change for the album page: the album header, the frame entry and the Open Graph card show the same things as today.

v8 bulk album edit page (FR-103-13 … 15). Header row, before and after (switch columns only; widths unchanged):

```
before:
| ☐ | Title          | Owner | License | Sensitive | Public | Link | Full Photo | Download | Upload | Photo Sort | Album Sort | Created     |   |
after:
| ☐ | Title          | Owner | License |    ⚠      |   👁   |  🔗  |    ▭       |    ☁↓    |   ↑    | Photo Sort | Album Sort | Created     |   |
                                            │
                                     ┌──────┴──────┐
                                     │ Sensitive   │   ← tooltip on hover of the icon
                                     └─────────────┘
```

Creation date cell, on hover:

```
| … | Album Sort | Created      |   |
| … | Title ↑    | 12 Mar 2024  |   |
                   │
          ┌────────┴──────────────────────────┐
          │ Taken: 3 Jun 2019 – 28 Aug 2023   │   ← sub-albums included
          └───────────────────────────────────┘
Same day:   "Taken: 3 Jun 2019"
No date:    "No data"
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-103-01 | Regular album with visible landscape photos, no `header_id`: `Album::head` returns a header URL of one of them; no `Photo` model retrieved. |
| S-103-02 | Same album as a guest with access, some photos not visible to guests (e.g. unvalidated): the header is picked among the guest's visible photos only. |
| S-103-03 | Album whose visible photos have no landscape `medium2x`/`medium`/`small2x`/`small` variant: the header falls back to any visible photo with such a variant. |
| S-103-04 | Album with `header_id` set to a photo: that photo's variant, as today. |
| S-103-05 | Empty album, or no photo visible to the caller: no header; `is_mod_frame_enabled` false. |
| S-103-06 | `mod_frame_enabled` on with photos: `is_mod_frame_enabled` true; `mod_frame_include_subalbums` on: decided over the album tree as today. |
| S-103-07 | Tag album and person album: header and frame flag as today, without loading their photos. |
| S-103-08 | Smart album: header picked among its visible photos. |
| S-103-09 | `/gallery/{album_id}` with `sm_card_album_source = header`: Open Graph image from FR-103-02; with `cover`, unchanged. |
| S-103-10 | Album of 2 and of 20 visible photos: `Album::head` and `/gallery/{album_id}` run the same number of queries (NFR-103-02). |
| S-103-11 | v8 struct-of-arrays album: next/previous photo in the photo view follow the grid order; photo links carry `?page=1`. |
| S-103-12 | A tile's overlay date, and the photo view's taken/uploaded dates, read the same strings as before; a tile never shown has formatted none. |
| S-103-13 | Star, rating or details loaded for a shown tile: the tile updates in place. |
| S-103-14 | Rating filter on and off: tiles, headers and counts as before (FR-065-19); drag selection selects the tiles under the rectangle. |
| S-103-15 | Window resized: the date scrubber shows the same entries at the new tops; tile keys are not recomputed. |
| S-103-16 | v8 struct-of-arrays album: a video opened from the grid shows the loading indicator, then plays, without reload. |
| S-103-17 | Photo view, navigating forward faster than images load: each photo shows the loading indicator, then its image; never a broken image. |
| S-103-18 | Details request fails for the shown photo: error placeholder; showing it again requests its details again. |
| S-103-19 | Opening photo `i`: one details request carries `i` and the not-yet-merged photos among `i+1 … i+10` and `i-5 … i-1`; the images of `i+1 … i+3` and `i-1` are requested by the browser once their details are merged; no video or PDF is preloaded. |
| S-103-20 | Timeline, photo in two albums: one row in its window, counted once in its bucket. |
| S-103-21 | Timeline window, details and urls requests: the visible-photo id subquery is restricted to the window or ids (query log). |
| S-103-22 | Timeline layout pass over unloaded buckets of sizes 3, 3, 5, 3, 5, 1: three placeholder layouts, heights unchanged. |
| S-103-23 | Album created at the root, under a non-NSFW album, under an NSFW album and under a sub-album of one: flag false, false, true, true. Parent below an NSFW album with a stale `false` flag: the new album is flagged. |
| S-103-24 | Album marked NSFW (protection policy, bulk edit, NSFW album job): it and all its sub-albums flagged; unmarked: flags cleared except below another NSFW album. |
| S-103-25 | Album with sub-albums moved under an NSFW album, moved back to the root, merged into an NSFW album (sub-albums move), transferred (becomes root): the subtree's flags follow the new ancestry. |
| S-103-26 | Flags out of date before `lychee:fix-tree` or the maintenance full-tree update: recomputed for every album. |
| S-103-27 | `hide_nsfw_in_timeline`/`hide_nsfw_in_search` on: photos below an NSFW album hidden, a photo also in a non-NSFW album shown once, a photo without album shown as before; the photo query has no correlated sensitivity subquery (query log). |
| S-103-28 | Flow with `hide_nsfw_in_flow` off: items below an NSFW album have `is_nsfw` true; on without `flow_base`: those items absent. RSS with `hide_nsfw_in_rss`: albums below an NSFW album not listed for a photo. |
| S-103-29 | Bulk album edit page, non-SE: five icon headers (no Upload column); SE or SE preview: six. Each tooltip reads the column's label. |
| S-103-30 | Bulk edit list of an album with photos taken on different days, one with a sub-album holding older photos: `min_taken_ats`/`max_taken_ats` give the range over both. |
| S-103-31 | Album without photos, or whose photos have no `taken_at`: both `null`, the tooltip on its date reads "No data". |
| S-103-32 | Album whose photos were all taken the same day: the tooltip shows one date. |
| S-103-33 | Bulk edit list: the capture range is read by the single listing query. |

## Test Strategy
- **REST API (v2):** new `tests/Feature_v3` class on `GET /api/v2/Album::head` (no new Feature_v2 tests): S-103-01 … 08 and S-103-10 (query count, `eloquent.retrieved: App\Models\Photo` count). Red before FR-103-01/02 for S-103-10.
- **Web:** S-103-09 through the HTML page's meta tags.
- **Existing:** the head, header, frame and Open Graph test classes are rerun (listed in the plan).
- **Bulk album edit:** `AlbumListV3Test` (Feature_v3) for S-103-30, 31, 33; Vitest `v8/utils/takenRange.test.ts` for S-103-31/32; S-103-29 in the browser on the scratch instance; `LangTest` and `scripts/check_translations.php` for the new string.
- **Stored NSFW flag:** new `tests/Feature_v3/Album/AlbumRecursiveNsfwTest` drives every write of FR-103-11 through its endpoint, action, job or command and compares each album's stored flag with the ancestry rule (S-103-23 … 26); new `tests/Feature_v3/Timeline/TimelineNsfwFlagTest` covers S-103-27; `tests/Unit/Actions/Album/SyncRecursiveNsfwTest` covers the outermost-range helper. The existing NSFW classes (Timeline, search, smart albums, tags, map, RSS, Flow, frame, cover selection, NSFW album job) are rerun; tests that set `is_nsfw` outside these writers call the sync action. The migration's backfill has no test of its own; it is checked against the computed condition on the scratch instance (plan).
- **Frontend (v8):** Vitest (`vp test`), next to the modules: `adaptPhotoTile.test.ts` (FR-103-04: same strings as eager formatting, nothing formatted before a read, assignment, spread), `PhotosState.test.ts` (FR-103-03: page map and navigation links), `analyticPhotoLayout.test.ts` (FR-103-05: indexes with and without rating filter), `dateScrubber.test.ts` or the grid's key cache (FR-103-06), the details window and preload choice of FR-103-08 (pure helpers), the pending-details check of FR-103-07. S-103-11 … 19 in the browser on the scratch instance (Playwright) with imported photos and videos.

## Interface & Contract Catalogue

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-103-01 | Album open | Same rendering as today, first tile sooner. |
| UI-103-02 | Bulk edit icon header tooltip | Hover of a switch column header → its label. |
| UI-103-03 | Bulk edit capture range tooltip | Hover of a creation date → "Taken: from – to", or "No data" without capture date. |

### Persistence
| ID | Change | Notes |
|----|--------|-------|
| DB-103-01 | `albums.is_recursive_nsfw` boolean, not null, default false, backfilled from the tree | FR-103-11, ADR-103-01 |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-103-01 | REST GET /api/v2/Album::head | Response unchanged; no per-photo work. | `AlbumHeadController`, `AlbumConfig`, `Head*AlbumResource` |
| API-103-02 | WEB GET /gallery/{album_id} | Page unchanged; no per-photo work for the Open Graph image. | `VueController::gallery`, `Meta` |
| API-103-03 | REST GET /api/v3/Albums?for_bulk_edit=1 | `bulk_edit` gains `min_taken_ats`, `max_taken_ats` (FR-103-14). | `AlbumListController`, `AlbumListBulkEditFieldsResource` |

## Telemetry & Observability
None.

## Documentation Deliverables
- Roadmap row 103.
- ADR-103-01 (stored recursive NSFW flag).
- Knowledge map: `GET /Album::head` entry notes that header and frame availability are read without loading photos; the v8 photo grid entry notes that the layout positions tiles by store index and that tile dates are formatted on first read; the album NSFW-ancestry flag and its writers (FR-103-11/12); the v3 album list entry notes the bulk-edit capture range fields (FR-103-14).

## Fixtures & Sample Data
None; tests build albums with the factories.

## Spec DSL

```
routes:
  - id: API-103-01
    method: GET
    path: /api/v2/Album::head
  - id: API-103-02
    method: GET
    path: /gallery/{album_id}
  - id: API-103-03
    method: GET
    path: /api/v3/Albums
    query: for_bulk_edit=1
```
