# Feature 086 – Album Map Header

| Field | Value |
|-------|-------|
| Status | Testing |
| Last updated | 2026-10-07 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #086 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

An album's header style is stored in `albums.header_id` (`char(24)`): a photo id (that photo is the header), the sentinel `compact` (`AlbumController::COMPACT_HEADER`, no header image), or `NULL` (a random landscape photo of the album). This feature adds a third style, **map**: the header band shows a Leaflet map fitted to the album's geotagged photos, one dot per photo, with the album's GPX tracks. As in compact mode, the album title, dates and the rest of the hero sit below the header, in the hero card.

Affected layers: `SetHeader` action and `PATCH /Album` request (new sentinel), `HeadAlbumResource`/`PreFormattedAlbumData` (map-or-image decision), a new v3 endpoint `GET /api/v3/Map/album` built on Feature 067's `ResolvesMapPhotoSource`, and the v8 album hero and album properties form. v7 only sends `is_map_header: false` with its album update (Q-086-01).

## Goals

- An album editor can pick **Map** as the album header in the v8 album properties.
- Visitors of such an album see a map of the album's geotagged photos as the header, with the title and hero details below it.
- The map uses the configured tile provider (`map_provider`) and shows exactly the photos the album's Map page (`/map/{albumId}`) shows.
- When the map cannot be shown, the album gets the random-photo header instead.

## Non-Goals

- v7 support: v7 shows map-header albums in its compact layout, has no Map entry in its properties form, and always sends `is_map_header: false`, so saving its properties replaces a map header (Q-086-01).
- Changing the Map page (`/map`, `/map/{albumId}`) or the Feature 067 viewport endpoints.
- Clustering or aggregating photos, server-side or client-side (Q-086-04).
- Header styles for smart, tag or person albums (they have no `header_id`).
- New tile providers or offline tiles.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-086-01 | `albums.header_id = 'map'` selects the map header. The sentinel is `AlbumController::MAP_HEADER`, next to `COMPACT_HEADER`. Setting it clears `header_photo_focus`. The value is compared after `trim()` (`char(24)` pads on PostgreSQL). | `SetHeader` stores `map`. | `map` is never looked up as a photo id. | — | — | Owner request 2026-10-07 |
| FR-086-02 | `PATCH /Album` requires the boolean `is_map_header`; `true` stores the map header. The v8 frontend sends the selector state, the v7 frontend always sends `false`. | `is_map_header: true` → `header_id = 'map'`, `header_photo_focus = null`. | `is_map_header` missing → 422. `is_map_header: true` together with `is_compact: true` → 422. | — | — | Owner request 2026-10-07 |
| FR-086-03 | The album head payload carries `preFormattedData.is_map_header`: `true` only when the map is drawn (FR-086-04). Then `preFormattedData.url` is `null`, and the hero renders the title in the card as in compact mode. | Map drawn; title, dates, counts and actions in the hero card below it. | — | — | — | Owner request 2026-10-07 |
| FR-086-04 | The map is drawn when all hold: `header_id = 'map'`; `use_album_compact_header` is off; the viewer may access the album's map (`AlbumPolicy::CAN_ACCESS_MAP`: `map_display`, and `map_display_public` for guests); the album has at least one geotagged photo in the Map page's scope (`ResolvesMapPhotoSource` album query, `map_include_subalbums`). | `is_map_header = true`, `url = null`. | — | Otherwise `is_map_header = false` and `url` is the random-photo header (as for `header_id = NULL`); with `use_album_compact_header` on, `url = null` (compact, unchanged global rule). | — | Q-086-05 |
| FR-086-05 | `GET /api/v3/Map/album?album_id=` returns every geotagged photo of the album in the Map page's scope: the album's own photos, plus sub-album photos when `map_include_subalbums` is on, with the visibility filtering `all_photos()` applies. Each photo appears once. Struct-of-arrays: `ids`, `album_ids`, `latitudes`, `longitudes`. No clustering, no cap. Not gated by the `struct-of-array` feature flag. | 200 with the arrays (possibly empty). | `album_id` missing or malformed → 422; unknown album → 404. | Viewer may not access the album's map → 403 (401 for a guest when login is required). | — | Q-086-04 |
| FR-086-06 | `album_ids[i]` is the containing album of photo `i` to open it in: the album with the lowest `_lft` in scope (the requested album itself when the photo is directly in it). | — | — | — | — | Feature 067 Q-067-15 |
| FR-086-07 | The header map draws one dot per photo in the theme's primary colour and the album's GPX tracks (from the album head payload, Map page colour palette). It is fitted to the bounding box of the dots, with padding and a maximum zoom so a single photo does not zoom to street level. | Clicking a dot opens that photo (`/gallery/{album_ids[i]}/{ids[i]}`). | — | Endpoint error → the map keeps its world view; the global API error handling applies. Track load error → toast, as on the Map page. | — | Q-086-02, Q-086-03 |
| FR-086-08 | Interaction: `+`/`−` zoom buttons; mouse-wheel zoom off. On pointer devices the map can be dragged. On touch devices (`isTouchDevice()`) the map cannot be dragged and pinch-zoom keeps the centre, so a drag scrolls the page. Pressing or dragging anywhere on the map (tiles, dots, tracks, controls) never starts the album's drag selection and keeps the current selection (`data-stop-drag-select`). | — | — | — | — | Q-086-02, owner 2026-10-07 |
| FR-086-09 | The map header is a band of 30svh, at least 16rem, regardless of `album_header_size`. | — | — | — | — | Q-086-06 |
| FR-086-10 | The v8 album properties header selector lists **Map** (icon `lucide:map`) after **Compact** when the album's map is accessible (`config.is_map_accessible`), and always shows it selected when `header_id = 'map'`. Choosing a photo, Compact or Map replaces the previous choice. | Saving sends `is_map_header: true`, `is_compact: false`, `header_id: null`. | — | — | — | Owner request 2026-10-07 |
| FR-086-11 | Setting a photo as header from the photo context menu replaces the map header (existing `SetAsHeaderRequest`, unchanged). The v8 page updates `is_map_header` to `false` locally together with the new header image. | — | — | — | — | Owner request 2026-10-07 |
| FR-086-12 | Social cards (`sm_card_album_source = header`) of a map-header album use the random-photo header image. | `og:image` set from a random photo. | — | — | — | Consequence of FR-086-03 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-086-01 | The header shows exactly the photos of the album's Map page: same `ResolvesMapPhotoSource::resolveAlbumQuery()` scope, same `CAN_ACCESS_MAP` gate. | Privacy parity | Feature tests: a sub-album photo is excluded when `map_include_subalbums` is off; a guest is refused when `map_display_public` is off | Feature 067 trait | Q-086-04 |
| NFR-086-02 | `GET /Map/album` reads `toBase()` rows only (`id`, `latitude`, `longitude`, `SELECT DISTINCT`), with no size variant join, and is cached through `ManagedCacheService` with the Map tags (`mapListingTag(album_id)`, `mapListingGlobalTag()`, `userTag()`), so Feature 067's invalidation covers it. | Payload grows with the album (no cap, Q-086-04) | Code review; cache test | `CacheKeyProvider` | Q-086-04 |
| NFR-086-03 | The map-or-image decision (FR-086-04) costs at most one `EXISTS` query per album head request, run only when `header_id = 'map'`. | Album page latency | Code review | — | Q-086-05 |
| NFR-086-04 | No new network origin besides the admin-configured tile provider; marker styling is bundled. | Offline-only requirement | No new external URL in the album page | — | Owner directive |
| NFR-086-05 | The map header component is a lazy chunk, loaded only when an album shows the map header; it adds no static import to the album page (Leaflet is already reachable there through the photo sidebar map). | Album page bundle size | `npm run build` manifest: `AlbumMapHeader` is a dynamic entry | Leaflet (existing dependency) | — |
| NFR-086-06 | Dots are drawn on a canvas renderer so thousands of points stay responsive. | No cap on points | Manual check with a large album | Leaflet `L.canvas()` | Q-086-04 |

## UI / Interaction Mock-ups

Map header (desktop), title in the hero card:

```
+------------------------------------------------------------------+
| ←  Summer in Brittany                              🔍  ＋  ✎  ⋮  |  ← AlbumHeader toolbar (unchanged)
+------------------------------------------------------------------+
| [+]                                                              |  ↑
| [−]     ●  ●                                                     |  │ 30svh, min 16rem
|       ●  ●●  ●      ~~~~ GPX track ~~~~                          |  │ drag to pan (not on touch)
|          ●              ~~~~~~~~                                 |  │ wheel scrolls the page
|                                   ●                              |  │ click ● → photo
|                                             © tile provider      |  ↓
+------------------------------------------------------------------+
| Summer in Brittany                     ⇣  ⤴  </>  📈  🗺  ▶       |  ← hero card, title as in compact mode
| Created 2026-07-02                                               |
| 4 subalbums · 312 images — CC BY 4.0                             |
| Description …                                                    |
+------------------------------------------------------------------+
| [thumb] [thumb] [thumb] [thumb] [thumb] [thumb]                  |
```

Fallback (map not accessible, no geotagged photo): the regular random-photo header with the title on the image.

Album properties header selector (v8):

```
Header  [ Map                ▾ ]
        ┌──────────────────────┐
        │ ⤡  Compact           │
        │ 🗺  Map        ✓      │   ← only when the album's map is accessible
        │ ▣  IMG_0012          │
        │ ▣  IMG_0013          │
        └──────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-086-01 | `PATCH /Album` with `is_map_header: true` → `header_id = 'map'`, `header_photo_focus` cleared. |
| S-086-02 | `PATCH /Album` with `is_compact` and `is_map_header` both `true` → 422. |
| S-086-03 | `PATCH /Album` with `is_map_header: false` (v7 payload) on a map-header album → `header_id` follows `is_compact`/`header_id`. |
| S-086-04 | Album head of a map-header album with a geotagged photo, map accessible → `is_map_header = true`, `url = null`. |
| S-086-05 | Same album, no geotagged photo in scope → `is_map_header = false`, `url` = random-photo header. |
| S-086-06 | Same album, `map_display` off → `is_map_header = false`, `url` = random-photo header. |
| S-086-07 | Same album, guest with `map_display_public` off → `is_map_header = false`, `url` = random-photo header. |
| S-086-08 | Same album, `use_album_compact_header` on → `is_map_header = false`, `url = null`. |
| S-086-09 | Geotagged photo only in a sub-album: `map_include_subalbums` on → map drawn, `/Map/album` lists it with the sub-album's id; off → fallback, `/Map/album` empty. |
| S-086-10 | `/Map/album` returns each photo once, with `album_ids` = the requested album for its own photos. |
| S-086-11 | `/Map/album` as a guest with `map_display_public` off → 401/403; with `map_display` off → 403. |
| S-086-12 | `/Map/album` without `album_id` → 422. |
| S-086-13 | `/Map/album` works with the `struct-of-array` flag off. |
| S-086-14 | Setting a photo as header on a map-header album replaces `map` with the photo id. |
| S-086-15 | Social card of a map-header album uses a random photo. |
| S-086-16 | v8: selecting Map in the properties shows the map header after saving; touch devices cannot drag the map; clicking a dot opens the photo. (manual) |
| S-086-17 | `PATCH /Album` without `is_map_header` → 422. |
| S-086-18 | Desktop: dragging the map (on tiles or on a track line) pans it, draws no selection rectangle and keeps the selected photos. (manual) |

## Test Strategy

- **REST API (Feature_v3):** `AlbumMapHeaderTest` — `PATCH /Album` (S-086-01 … S-086-03, S-086-17), album head decision (S-086-04 … S-086-09), set-as-header (S-086-14). `MapAlbumPointsTest` — `/Map/album` (S-086-09 … S-086-13).
- **Unit:** `SetHeader` map branch if not covered by the feature tests.
- **Social card:** `MetaTest` case for S-086-15 if the existing suite builds album metas; otherwise covered by `getHeaderUrl` ignoring the sentinel.
- **Frontend (v7, v8):** `npm run check` (the shared `UpdateAbumData` type makes `is_map_header` mandatory in both properties forms); manual browser check (S-086-16, S-086-18).
- **Contracts:** `php artisan typescript:transform` for `PreFormattedAlbumData` and the new resource.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-086-01 | `AlbumController::MAP_HEADER = 'map'` sentinel in `albums.header_id` | actions, resources |
| DO-086-02 | `UpdateAlbumRequest::$is_map_header` (required boolean, `declined_if:is_compact,true`) | REST |
| DO-086-03 | `PreFormattedAlbumData::$is_map_header` (bool) | resources, TypeScript |
| DO-086-04 | `App\Http\Resources\V3\MapPointResource` — `ids: string[]`, `album_ids: (string\|null)[]`, `latitudes: float[]`, `longitudes: float[]` | resources, TypeScript |
| DO-086-05 | `App\Actions\Map\QueryAlbumMapPoints` — `do(AbstractAlbum, ?User, bool $include_sub_albums): MapPointResource`, `hasPoints(AbstractAlbum, bool $include_sub_albums): bool` | actions |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-086-01 | REST PATCH /api/v2/Album | Adds required `is_map_header` | `UpdateAlbumRequest` |
| API-086-02 | REST GET /api/v3/Map/album?album_id= | Album map points (FR-086-05) | `GetMapAlbumRequest`, `MapListingController::album()`, ADR-086-01 |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-086-01 | Map header shown | `preFormattedData.is_map_header = true` |
| UI-086-02 | Fallback header | `header_id = 'map'` but `is_map_header = false` → random-photo header (or compact under `use_album_compact_header`) |
| UI-086-03 | Touch device | Dragging off, pinch keeps the centre |

## Telemetry & Observability

None.

## Documentation Deliverables

- Roadmap row #086.
- Knowledge map: header styles and `GET /Map/album`.
- ADR-086-01 (album map points endpoint and server-side map-or-image decision).

## Fixtures & Sample Data

None; tests set `latitude`/`longitude` on photos directly.

## Spec DSL

```
domain_objects:
  - id: DO-086-01
    name: AlbumController::MAP_HEADER
    value: "map"
  - id: DO-086-02
    name: UpdateAlbumRequest.is_map_header
    type: boolean
    constraints: "required, declined_if:is_compact,true"
  - id: DO-086-03
    name: PreFormattedAlbumData.is_map_header
    type: boolean
  - id: DO-086-04
    name: MapPointResource
    fields: [ids, album_ids, latitudes, longitudes]
  - id: DO-086-05
    name: QueryAlbumMapPoints
routes:
  - id: API-086-01
    method: PATCH
    path: /api/v2/Album
  - id: API-086-02
    method: GET
    path: /api/v3/Map/album
ui_states:
  - id: UI-086-01
    description: Map header shown, title in the hero card
  - id: UI-086-02
    description: Fallback to the random-photo header
  - id: UI-086-03
    description: Touch device, no dragging
```
