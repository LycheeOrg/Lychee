# Open Questions – Feature 086

Open questions for [Feature 086](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-086-01~~ | 086 – Album map header | High | Which UI versions get the map header? (A v8 only, v7 shows such albums as compact · B v7 and v8) | Resolved (Option A — v8 only; owner, 2026-10-07; spec Non-Goals) | 2026-10-07 | 2026-10-07 |
| ~~Q-086-02~~ | 086 – Album map header | High | How interactive is the header map? (A drag/pinch and zoom buttons, no wheel zoom, point opens the photo · B static preview linking to the Map page · C same as the Map page) | Resolved (Option A, no dragging on touch devices; owner, 2026-10-07; spec FR-086-07, FR-086-08) | 2026-10-07 | 2026-10-07 |
| ~~Q-086-03~~ | 086 – Album map header | Medium | What is drawn on the map? (A dots, count dots for dense areas, plus GPX tracks · B photo-thumbnail clusters as on the Map page, plus tracks · C dots only) | Resolved (Option A — dots and GPX tracks, no count dots (no clustering, Q-086-04); owner, 2026-10-07; spec FR-086-07) | 2026-10-07 | 2026-10-07 |
| ~~Q-086-04~~ | 086 – Album map header | High | Where does the header get its extent and points? (A extent in the album payload, points from the Feature 067 viewport endpoints · B new extent endpoint, same points · C v2 `/Map` unbounded endpoint) | Resolved (Option B, amended — new `GET /api/v3/Map/album` returning every photo point of the album scope, no clustering; owner, 2026-10-07; spec FR-086-05, FR-086-06, NFR-086-01, NFR-086-02; ADR-086-01) | 2026-10-07 | 2026-10-07 |
| ~~Q-086-05~~ | 086 – Album map header | High | What shows when the map cannot be shown? (A compact, including when `use_album_compact_header` is on · B compact, but `use_album_compact_header` does not suppress map headers · C random-photo header) | Resolved (Option C — random-photo header; `use_album_compact_header` still forces compact; owner, 2026-10-07; spec FR-086-03, FR-086-04, NFR-086-03; ADR-086-01) | 2026-10-07 | 2026-10-07 |
| ~~Q-086-06~~ | 086 – Album map header | Medium | How tall is the map header? (A always half screen · B follow `album_header_size` · C a smaller fixed band) | Resolved (Option C — 30svh, at least 16rem; owner, 2026-10-07; spec FR-086-09) | 2026-10-07 | 2026-10-07 |

## Question Details

### ~~Q-086-01~~ – UI versions

**Context:** v8 replaces v7; new UI defaults to v8 only. Both versions decide on `preFormattedData.url`: when it is `null`, the hero renders the title in the card (compact layout). The backend returns `null` for map-header albums (FR-086-03), so v7 shows them as compact without any v7 change. The v7 properties form does not know the `map` value: its header selector shows nothing selected, and saving the form sends `header_id: null, is_compact: false`, which resets the header to a random photo.

- **Option A (chosen):** v8 only.
  - ✅ One Leaflet header component (Nuxt UI only); v7 degrades to compact by itself.
  - ❌ Saving album properties from v7 resets a map header to a random photo.
- **Option B:** v7 and v8.
  - ✅ Parity, no reset from v7.
  - ❌ Selector entry and header component written twice (PrimeVue and Nuxt UI).

**Resolution:** Option A. v7 shows map-header albums in its compact layout, its properties form has no Map entry, and its album update always sends `is_map_header: false` (the field is required, owner, 2026-10-07). Recorded in [spec.md](spec.md) Non-Goals, FR-086-02.

### ~~Q-086-02~~ – Interaction model

**Context:** The header sits at the top of a scrolling page. Leaflet zooms on the mouse wheel by default, which captures page scrolling while the pointer is over the map. On touch devices one-finger drag pans the map instead of scrolling the page. The hero already has a map icon linking to `/map/{albumId}`.

- **Option A (chosen):** Explorable. Drag (mouse) and pinch/drag (touch) pan, `+`/`−` buttons zoom, wheel zoom off. Clicking a point opens that photo; clicking a count dot zooms in. Points reload on pan/zoom.
  - ✅ Useful on its own; wheel scrolling still scrolls the page.
  - ❌ On touch devices, a drag that starts on the map pans it instead of scrolling the page.
- **Option B:** Static preview. No pan or zoom; clicking anywhere on the map opens `/map/{albumId}`.
  - ✅ No scroll conflict on any device; one data load.
  - ❌ The header is a picture only; exploring needs the Map page.
- **Option C:** Same behaviour as the Map page (wheel zoom, photo popups).
  - ✅ Familiar from the Map page.
  - ❌ The wheel stops scrolling the page while the pointer is over the header.

**Resolution:** Option A with one change: on touch devices the map cannot be dragged (pinch-zoom keeps the centre), so a drag scrolls the page. Dots open their photo; with no clustering (Q-086-04) there are no count dots and nothing reloads on pan or zoom. Recorded in [spec.md](spec.md) FR-086-07, FR-086-08.

### ~~Q-086-03~~ – What the map draws

**Context:** The Map page draws photo thumbnails clustered by `leaflet.markercluster`, fetching each visible thumbnail lazily, plus the album's GPX tracks (Feature 055). A header loads on every visit of the album.

- **Option A (chosen):** Small dots in the primary colour, one per photo; where the server aggregates (over `MAX_VIEWPORT_PHOTOS`), a larger dot with the count. The album's GPX tracks are drawn too.
  - ✅ Reads as a header, no thumbnail requests, light rendering.
  - ❌ You cannot tell which photo a dot is before clicking it.
- **Option B:** Photo thumbnails clustered as on the Map page, plus tracks.
  - ✅ Visual, same look as the Map page.
  - ❌ Thumbnail requests on every album visit; busy at header size.
- **Option C:** Dots only, no tracks.
  - ✅ Simplest.
  - ❌ Albums with a recorded route lose the most telling line on the map.

**Resolution:** Option A: one primary-colour dot per photo plus the album's GPX tracks. Count dots are dropped because nothing is aggregated (Q-086-04). Recorded in [spec.md](spec.md) FR-086-07, NFR-086-06.

### ~~Q-086-04~~ – Data source

**Context:** The header must know the album's extent to fit the map, and whether there is any visible geotagged photo at all (Q-086-05). On the v8 struct-of-arrays album path, coordinates arrive lazily per bucket and are gated by `gps_coordinate_display`, so the album page has no complete coordinate list. Feature 067 provides bounded viewport endpoints (`/Map/Photos` up to 500 photos, `/Map/buckets` aggregated above that, `/Map/tracks`) with the Map page's visibility filter (`ResolvesMapPhotoSource`). The v2 `/Map` endpoint returns every geotagged photo with size variants, unbounded.

- **Option A:** The album payload carries the extent (`north`, `south`, `east`, `west`) of the album's visible geotagged photos, computed only for map-header albums by one MIN/MAX query on the `ResolvesMapPhotoSource` filter; `null` when there is none. Points and tracks come from the Feature 067 endpoints for the fitted viewport.
  - ✅ Map or fallback decided at first render (no layout shift); bounded payloads; reuses Feature 067 access rules.
  - ❌ One aggregate query more when loading a map-header album.
- **Option B (chosen):** New `GET /api/v3/Map/extent?album_id=` endpoint; points as in A.
  - ✅ Album payload untouched.
  - ❌ Extra round trip before the map can be placed; the header appears or disappears after the page has rendered.
- **Option C:** v2 `GET /Map?album_id=`, extent computed in the browser.
  - ✅ No backend work.
  - ❌ Unbounded: the scale problem Feature 067 solved for the Map page.

**Resolution:** Option B, amended by the owner: a new v3 endpoint `GET /api/v3/Map/album?album_id=` returns every geotagged photo of the album (sub-albums when `map_include_subalbums` is on, with their visibility filtering), without clustering and without a cap; the extent is computed in the browser from the points. Not gated by the `struct-of-array` flag. Recorded in [spec.md](spec.md) FR-086-05, FR-086-06, NFR-086-01, NFR-086-02 and [ADR-086-01](../../../6-decisions/ADR-086-01-album-map-points-endpoint.md).

### ~~Q-086-05~~ – Fallback when the map cannot be shown

**Context:** The map header cannot be shown when the map is disabled (`map_display` off), the visitor is a guest and `map_display_public` is off, or the album has no visible geotagged photo. Separately, the admin setting `use_album_compact_header` today forces compact for every album, overriding photo headers.

- **Option A:** Show the compact layout in all these cases, including when `use_album_compact_header` is on. The album keeps `header_id = 'map'`, so the map appears once the condition clears.
  - ✅ Same layout the map header already uses (title in the card), so nothing jumps; the global setting keeps its meaning "no big headers".
  - ❌ An owner who picks Map on an instance with `use_album_compact_header` on sees no map.
- **Option B:** As A, but `use_album_compact_header` does not suppress map headers.
  - ✅ The per-album Map choice always wins.
  - ❌ The global setting no longer guarantees compact headers everywhere.
- **Option C (chosen):** Fall back to the random-photo header.
  - ✅ The album still gets a visual header.
  - ❌ The owner chose a map and gets a random photo; title moves back onto the image.

**Resolution:** Option C: when the map cannot be shown (map disabled, guest without public map, no geotagged photo in scope) the album gets the random-photo header. The backend decides this while building the album head (one `EXISTS` query, only for map-header albums), so the page never switches header after rendering. `use_album_compact_header` keeps forcing compact for every album, map headers included. Recorded in [spec.md](spec.md) FR-086-03, FR-086-04, NFR-086-03 and [ADR-086-01](../../../6-decisions/ADR-086-01-album-map-points-endpoint.md).

### ~~Q-086-06~~ – Header height

**Context:** Photo headers use `album_header_size`: `half_screen` (50svh) or `full_screen` (100svh). With the title below the map, a full-screen map pushes every album detail below the fold, and on touch devices (Q-086-02 A) a full-screen map leaves no area to scroll from.

- **Option A:** Always half screen (50svh), whatever `album_header_size` says.
  - ✅ Content reachable; touch scrolling possible below the map.
  - ❌ Ignores the admin's full-screen preference for this header type.
- **Option B:** Follow `album_header_size` like photo headers.
  - ✅ One rule for all headers.
  - ❌ Full screen hides the album content and traps touch scrolling.
- **Option C (chosen):** A smaller fixed band (about 30svh, at least 16rem).
  - ✅ More of the album visible at once.
  - ❌ Small for exploring (Q-086-02 A).

**Resolution:** Option C: a band of 30svh, at least 16rem, whatever `album_header_size` says. Recorded in [spec.md](spec.md) FR-086-09.

