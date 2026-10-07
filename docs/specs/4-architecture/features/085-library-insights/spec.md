# Feature 085 – Library Insights

| Field | Value |
|-------|-------|
| Status | Implemented (owner check on an SE instance pending) |
| Last updated | 2026-10-05 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #085 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
A v8 Insights page describes one photographer's library: how much they shoot, when, with which devices and settings, and how their storage is spent. In v8 it replaces the Statistics page ([ADR-085-01](../../../6-decisions/ADR-085-01-insights-replaces-statistics-page.md)): the storage panels move into an Insights "Storage" section, and the activity punch card becomes the Insights calendar heatmap. v7 keeps its Statistics page unchanged and has no access to Insights. Insights cover the viewer's own photos; administrators also choose any single user or the whole instance ([ADR-085-02](../../../6-decisions/ADR-085-02-owner-scoped-insights.md)). The whole page is a Supporter Edition (SE) feature, with the existing SE preview in core.

The section catalogue follows a dashboard built for another famous photo app. This feature delivers phase 1 plus the image-format, focal-length and timeline sections of the later phases; the remaining sections are follow-up features (see Non-Goals). Insights can also be narrowed to one album tree.

Layers: one aggregate action that streams a narrow photo projection once per scope and period, cached per library revision ([ADR-085-03](../../../6-decisions/ADR-085-03-insights-single-pass-aggregation.md)); a pure device normaliser; one resource; `GET /api/v3/Insights`; removal of the unused `Statistics::userSpace`; v8 frontend (new `Insights` view and section components drawn with ECharts loaded as a lazy chunk ([ADR-085-04](../../../6-decisions/ADR-085-04-echarts-chart-library.md)), left-menu entry); removal of the v8 Statistics view.

## Goals
- G1: one page answers "what does my library look like" for the whole library, one year, or a date range.
- G2: storage figures from the Statistics page stay available, inside Insights.
- G3: a user never sees aggregates over another owner's photos unless they are an administrator.
- G4: phase 1 sections (FR-085-05 … FR-085-14) ship together with image formats, focal-length comparison, the timeline and the space diagram (FR-085-17 … FR-085-20).
- G5: an album owner can read the same insights for one album tree (FR-085-16).

## Non-Goals
- v7 frontend: v7 keeps its Statistics page (view, punch card, menu entry, `Statistics::getCountsOverTime`) unchanged and gets no Insights page.
- Phase 2 (follow-up feature): evolution by year (cumulative counts, MB per file, resolution, device streamgraph), tendencies radar.
- Phase 3 (follow-up feature): photo-profile indices, exposure triangle, CSV/PDF/JPG/SQLite export.
- "For perspective" fun facts.
- 35 mm-equivalent focal lengths (not stored); the focal-length comparison uses a full-frame reference.
- Aggregates over photos shared with the viewer but owned by someone else.
- Changes to the per-photo/per-album counters (`statistics` table) and live metrics.
- Changes to the album statistics drawer.
- Album scope for albums the viewer does not own, tag albums and smart albums.
- Drill-down: chart elements do not open the matching photos.
- Per-user device override rules.
- Country or continent breakdown (Lychee stores no country).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-085-01 | Insights page in v8 at `/insights`, entered from the left menu where Statistics was. | SE enabled and logged in: the page loads the default scope (own library, Library period). | Not logged in: redirect to the gallery, as Statistics did. | SE disabled and `enable_se_preview` on: the menu entry shows the SE tag and the page renders every section from sample data generated in the browser (`composables/preview/getPreviewInfo.ts`), under the preview notice, without calling the API. SE disabled: API → 402 (`support:se`). | None. | Q-085-01, Q-085-02, Q-085-04 |
| FR-085-02 | The v8 Statistics page is replaced by Insights; v7 keeps its Statistics page. | Deleted: `resources/js/v8/views/Statistics.vue`; `Activity.vue`, `PunchCard.vue`, `PunchCardCaption.vue` under `resources/js/v8/components/statistics/`; the unused `GET /api/v2/Statistics::userSpace` with its controller method, `SpacePerUserRequest` and the `UserSpace` resource; `getUserSpace` in `statistics-service.ts`; `tests/Feature_v2/Statistics/UserSpaceTest.php`. The v8 left menu shows Insights instead of Statistics. The `/statistics` web route, the `statistics` router path (v7 component only), the v7 view and components, the v7 menu entries, `Statistics::getCountsOverTime` and the `statistics.punch_card` lang keys stay. | — | — | None. | Q-085-01, Q-085-02 |
| FR-085-03 | `Statistics::sizeVariantSpace`, `Statistics::albumSpace`, `Statistics::totalAlbumSpace` and `Statistics::getCountsOverTime` stay: the v7 Statistics page, the album statistics drawer (v7 and v8) and the Insights Storage section use them. | Unchanged behaviour and gating. | Unchanged. | Unchanged. | None. | Q-085-01 |
| FR-085-04 | Scope selector. Every user sees their own photos ("My library"). An administrator also picks any user or "Whole instance". An album chosen with FR-085-16 replaces the owner scope. | The page and every section reload for the chosen scope. | A non-administrator requesting another owner or the whole instance → 403. Unknown user ID → 422. | — | None. | Q-085-03, ADR-085-02 |
| FR-085-05 | Period selector: Library (all photos), Year (picker listing years that have photos), Range (from–to, both inclusive). | Every section except Storage (FR-085-07) is computed for the period. | Range with from > to → 422. Year outside the listed years → empty sections. | — | None. | Q-085-05 |
| FR-085-06 | Overview cards: total photos, split photos / videos / other; highlighted photos; albums (all owned albums for Library; albums with at least one photo in the period for Year/Range); photos in no album. | Counts shown with their split. | — | — | None. | Q-085-05 |
| FR-085-07 | Storage section: total size of originals, average size per photo and per video, size-variant meter, album space table. The meter and table are period-independent and come from the kept Statistics endpoints, which answer for the caller's own photos or, for administrators, every owner: they are shown for the Library period in a user's own scope, and for administrators in "Whole instance" scope only. | Values from the existing Storage endpoints (FR-085-03) and the period aggregate. | Photos whose original size variant has no file size are counted as "size unknown", not as 0. | — | None. | Q-085-01, Q-085-05 |
| FR-085-08 | People section: photos with at least one face (`face_count` > 0, dismissed faces excluded), distinct people with a face on a photo of the period, total faces, faces per photo with faces. | Values for the scope and period. | Section hidden when face recognition has never run (no `faces` rows for the scope). | — | None. | Q-085-05 |
| FR-085-09 | Time-span section: first and last capture (thumbnail), busiest day (thumbnail), days with photos and their share of calendar days in the span, undated photo count, longest break, longest daily streak, longest weekly streak. | Values for the scope and period. | Fewer than two dated photos: span, break and streak cards show "—". | — | None. | Q-085-05 |
| FR-085-10 | Calendar heatmap of small rounded squares: for the Year period a GitHub-style day grid (weeks as columns, Monday … Sunday as rows); for Library and Range one row per local year from the first to the last photo (oldest on top) and one column per ISO week, every cell drawn. The API returns photos per local day; the client sums weeks. Cell colours use `low/medium/high_number_of_shoots_per_day` (×7 for weeks), empty cells the surface colour. | One cell per day/week with its count in a tooltip. | — | — | None. | Q-085-01, Q-085-05 |
| FR-085-11 | Rhythm: a 7 × 24 weekday × hour heatmap drawn like the calendar (rounded squares with gaps, empty cells in the surface colour, four colour steps at a quarter, half and three quarters of the busiest hour, "Less … More" legend), and bar charts by month, weekday and hour. | Counts for the scope and period. | — | — | None. | Q-085-05 |
| FR-085-12 | Devices: photo counts by device (normalised maker + model), by manufacturer and by lens, with a metric switch (all, photos, videos, highlighted, located, with people) and a category filter (camera, mobile device, other). A built-in normaliser maps EXIF maker spellings to one manufacturer ("NIKON CORPORATION", "Nikon" → Nikon) and classifies each device as camera (known camera makers), mobile device (known phone makers, or model keywords such as iPhone, Pixel, Galaxy) or other. | Top entries with the rest grouped as "Other". | Photos without `make`/`model`/`lens` counted as "Unknown"; unknown makers keep their trimmed EXIF spelling and the "other" category. | — | None. | Q-085-05, Q-085-10 |
| FR-085-13 | Exposure: histograms of ISO, focal length, shutter time, aperture and video length, with median, mean, mode, min and max, and a linear/log scale switch. Technique cards: most common ISO, focal length, shutter, aperture; total and average video duration. | Distributions for the scope and period. | Photos missing a value are excluded from that histogram; the excluded count is shown. | — | None. | Q-085-05 |
| FR-085-14 | Places: count and share of photos with `latitude` and `longitude`, with a link to the existing map view. | Values for the scope and period. | — | Map disabled (`map_display` off): no link. | None. | Q-085-11 |
| FR-085-15 | Every time-based value (days, weeks, months, weekdays, hours, years, streaks, period filters) uses the local capture time: `taken_at` converted to `taken_at_orig_tz`. Photos with no `taken_at` are left out of time-based values and counted as "undated". | A photo taken at 20:30 in Tokyo counts at hour 20 and on its Tokyo date. | Null or unparsable `taken_at_orig_tz`: the stored `taken_at` is used as is. | — | None. | Q-085-08 |
| FR-085-16 | Album scope: an album selector lists the albums the viewer owns (administrators: every album), indented by depth. With an album chosen, every section covers the photos in that album and its descendants, whoever uploaded them; the album count is the number of albums in the tree (Library) or in the tree with a photo of the period. The Storage space diagram and size-variant meter cover the same tree. | Sections reload for the album tree. | A non-administrator naming an album they do not own → 403; unknown or non-regular album (tag, smart) → 422. | — | None. | Q-085-14, ADR-085-02 |
| FR-085-17 | Image formats: orientation counts (portrait, landscape, square, unknown) for all items, photos and videos, with shares; aspect-ratio groups independent of orientation (1:1, 5:4, 4:3, 3:2, 16:9, 2:1 within 3 %, panorama ≥ 2.2:1, other); the 200 most frequent pixel dimensions drawn as centred nested frames coloured by frequency (linear or log), with a list of the most common dimensions. Dimensions are the width and height of the ORIGINAL size variant (stored after auto-rotation). | Values for the scope and period. | Items without an original size variant or with a zero side count as "unknown". | — | None. | Owner request 2026-10-07 |
| FR-085-18 | Focal-length comparison: a device selector (all devices, or one device with focal values) and the five most frequent focal lengths of that selection, each drawn as a cone whose opening is the diagonal angle of view `2·atan(43.27 mm / (2·f))` of a full-frame sensor, with a legend (focal length, angle, photo count) and a note that no 35 mm equivalent is stored. | Cones for the scope and period. | Fewer than five focal lengths: as many cones as values; none: empty message. | — | None. | Owner request 2026-10-07 |
| FR-085-19 | Timeline of notable moments in the period, in local capture time: first and last capture, first video, first located photo, first photo with people, first highlighted photo; first and last photo of each device; milestones at the 100th, 1 000th, 5 000th, 10 000th, 25 000th, 50 000th, 100 000th, 250 000th and 500 000th dated item; records (longest video, largest original, highest ISO, longest exposure, widest aperture, longest focal length); start and end of the longest break and of the longest daily streak. Drawn on a horizontal time axis over a photos-per-month area, one symbol per category (first & last, devices, milestones, records, breaks & streaks), coloured green for every "first" event (including first photo per device), red for every "last" event, yellow for milestones and in the accent colour otherwise (semantic `success`/`error`/`warning` tokens, same colours on the list icons), labels hidden where they overlap, wheel zoom and drag pan, a category filter, and a chronological list below. | Events for the scope and period. | Undated items never produce events; records ignore missing values. | — | None. | Owner request 2026-10-07 |
| FR-085-20 | Space diagram in the Storage section: the album space per album (FR-085-03 `Statistics::albumSpace`) drawn as a sunburst or a treemap (toggle), nested by album (nested set) and, for administrators in "Whole instance" scope, by owner first; each node's value is its own size plus its descendants'. Shown for the Library period in every scope: in album scope for that tree, for administrators in "Whole instance" scope grouped by owner, otherwise filtered to the scope's owner (the endpoint returns every owner to administrators). Unlike the size-variant meter and album table, it is therefore also shown in an administrator's own or single-user scope. | Click to drill down, breadcrumb back. | Albums without photos have no area. | — | None. | Owner request 2026-10-07 |
| FR-085-21 | While the aggregate loads, the page shows the configured loading indicator (`LycheeLoadingIcon`: Lychee logo, unbranded ring or the configured custom image) centred under the controls. | — | — | — | None. | Owner request 2026-10-07 |
## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-085-01 | No request to an external host at runtime. | Lychee runs offline. | Code review; no URL outside the instance in the Insights code. | — | Owner directive |
| NFR-085-02 | Aggregates never include photos outside the requested scope. | Privacy between owners. | Feature tests per scope and role (S-085-02 … S-085-04). | ADR-085-02 | Q-085-03 |
| NFR-085-03 | Date arithmetic in new backend code uses native PHP date functions, not Carbon. | Resource cost per request. | Code review. | — | Owner directive |
| NFR-085-04 | All new strings in `lang/<locale>/*.php` for every locale; layout works in RTL. | i18n. | `php artisan lang:json`; RTL visual check. | — | Coding conventions |
| NFR-085-05 | MySQL/MariaDB, PostgreSQL and SQLite return identical aggregates. | Supported drivers. | Feature tests on SQLite locally, CI on the others. | — | Coding conventions |
| NFR-085-06 | Aggregates come from one streamed pass (`DB::table` cursor in chunks, no Eloquent models) over the projection `id`, `taken_at`, `taken_at_orig_tz`, `type`, `make`, `model`, `lens`, `iso`, `aperture`, `shutter`, `focal`, `duration`, the `filesize`, `width` and `height` of the ORIGINAL size variant (left join on `size_variants`; `photos.filesize` is not maintained), `face_count`, `latitude`, `longitude`, `is_highlighted` and an `EXISTS` flag for album membership. SQL narrows a Year or Range period to the UTC window widened by 14 hours on each side; PHP keeps the photos whose local date falls in the period. Albums with a photo in the period and distinct people come from two more streams over `photo_album` and `faces` joined to the same windowed photos, filtered the same way and collected as ID sets (for the Library period, plain `COUNT` queries). The result is cached under (scope, period, revision); the revision is the photo count and latest `updated_at` of the scope's photos and albums. Cache entries expire after one hour. Memory stays flat in the library size: accumulators only, no per-photo array. | Large libraries; three drivers; local time. | Feature tests compare against expected aggregates; a cached response is returned when the revision is unchanged and recomputed after a photo is added, edited or deleted. | Laravel cache | Q-085-07, ADR-085-03 |
| NFR-085-07 | Charts are drawn with `echarts` 6.1 and `vue-echarts` 8.3, importing from `echarts/core` only the chart types and components in use, with the SVG renderer. Both load only with the Insights view (lazy chunk) and use one shared theme built from the Nuxt UI colour tokens, switched with light and dark mode. | Chart breadth across phases 1–3. | `vite build`: ECharts absent from the main bundle and from every other route chunk; visual check in both themes and in RTL. | echarts, vue-echarts; ADR-085-04 | Q-085-06, Q-085-13 |

## UI / Interaction Mock-ups

```
┌──────────────────────────────────────────────────────────────────────────┐
│ ☰  Insights                         [My library ▾]  (admin only)          │
│     ( Library | Year [2025 ▾] | Range [2024-01-01 → 2024-12-31] )        │
├──────────────────────────────────────────────────────────────────────────┤
│ ┌─────────────── YOUR MEDIA ───────────────┐ ┌──────────┐ ┌──────────┐   │
│ │  32 651                       ┌───────┐  │ │Highlight.│ │ Albums   │   │
│ │  [32 599 photos] [52 videos]  │newest │  │ │   412    │ │   87     │   │
│ │                               └───────┘  │ └──────────┘ └──────────┘   │
│ └──────────────────────────────────────────┘ ┌──────────┐                │
│                                              │No album  │                │
│                                              │  1 203   │                │
│                                              └──────────┘                │
├─ STORAGE ────────────────────────────────────────────────────────────────┤
│ 412 GB originals · 12.6 MB / photo · 88 MB / video · 14 size unknown     │
│ [████████ original ███ medium2x ██ medium █ small ▏thumb]   (Library only)│
│ Album            Owner   Photos  Sub-albums  Size        [ ] incl. sub   │
├─ PEOPLE ─────────────────────────────────────────────────────────────────┤
│ With people 8 120 · People 64 · Faces 15 302 · 1.9 per photo with people │
├─ PLACES ─────────────────────────────────────────────────────────────────┤
│ With location 21 480 (66 %)                              [Open map →]    │
├─ TIME SPAN ──────────────────────────────────────────────────────────────┤
│ First [▣] 2009-03-14   Last [▣] 2025-09-30   Busiest day [▣] 2019-07-21  │
│ Days with photos 2 140 (36 %) · Undated 210                              │
│ Longest break 94 d · Longest daily streak 17 d · Longest weekly 23 w     │
├─ CALENDAR ───────────────────────────────────────────────────────────────┤
│  Jan       Feb       Mar  …                                     Dec      │
│ ░▒▓█░░▒░▒▓░░░▒█▓▒░░░▒▓░░…                                                │
├─ TIMELINE ───────────────────────────────────────────────────────────────┤
│ (All|First & last|Devices|Milestones|Records|Breaks)                      │
│      ●First D850   ◆1 000th          ▲Longest video       ■Break ends     │
│ ▁▁▂▃▅▃▂▁▁▁▁▁▂▃▆▇▅▂▁▁▁▁▂▃▄▃▂▁   (wheel = zoom, drag = pan)              │
│ 2019        2020        2021        2022                                 │
│ 2019-04-02  First photo with Nikon D850                                  │
├─ RHYTHM ─────────────────────────────────────────────────────────────────┤
│       00 02 04 06 08 10 12 14 16 18 20 22    By month   ▁▂▃▅▇█▇▅▃▂▁▁     │
│ Mon   ░░░░░░░▒▒▓▓▒▒▒▓▓▒░░                    By weekday ▅▄▄▄▅▇█          │
│ …                                            By hour    ▁▁▁▂▅▇▇▆▆▇█▅     │
├─ DEVICES ────────────────────────────────────────────────────────────────┤
│ (Device | Manufacturer | Lens)  Metric [All ▾]  (All|Camera|Mobile|Other)│
│ iPhone 13 Pro   ███████████████  12 400                                  │
│ X-T4            ████████          6 210                                  │
│ Other           ██                1 020                                  │
├─ IMAGE FORMATS ──────────────────────────────────────────────────────────┤
│ Portrait 9 207 (28 %) · Landscape 22 890 (70 %) · Square 554 · Unknown 0  │
│ ┌──────────┐   6000 × 4000   12 400                                       │
│ │ ┌──────┐ │   4000 × 3000    8 210     3:2 ███████  4:3 ████  16:9 ██    │
│ │ └──────┘ │   4000 × 6000    2 522                                       │
│ └──────────┘                                                               │
├─ FOCAL LENGTHS ──────────────────────────────────────────── [Device ▾] ──┤
│   📷 ◁ 18 mm 100.5°   ◁ 24 mm 84.1°   ◁ 50 mm 46.8°   ◁ 85 mm   ◁ 200 mm  │
├─ EXPOSURE ───────────────────────────────────────────────────────────────┤
│ (ISO | Focal | Shutter | Aperture | Video length)  (Linear | Log)        │
│ ▁▃█▇▅▃▂▁▁                 median 200 · mean 412 · mode 100 · 32–12800   │
│ Excluded (no value): 1 830                                               │
└──────────────────────────────────────────────────────────────────────────┘
```

UI states: SE preview (UI-085-01), loading indicator (UI-085-02), empty scope/period (UI-085-03), forbidden scope never offered to non-administrators (UI-085-04).

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-085-01 | Logged-in user with SE opens `/insights`: own library, Library period, all phase-1 sections rendered. |
| S-085-02 | Non-administrator requests another owner's scope → 403. |
| S-085-03 | Non-administrator requests "Whole instance" → 403. |
| S-085-04 | Administrator requests a user's scope: aggregates match that user's photos only. |
| S-085-05 | Administrator requests "Whole instance": aggregates cover every owner. |
| S-085-06 | Year period: only photos captured in that year are counted; album card counts albums with a photo in that year. |
| S-085-07 | Range period with from > to → 422. |
| S-085-08 | Empty library: sections render empty states, no errors. |
| S-085-09 | SE disabled, preview on: preview shown from sample data, API → 402. |
| S-085-10 | Guest calls the Insights API → 401. |
| S-085-11 | Photos whose original size variant has no file size are reported as "size unknown" and excluded from averages. |
| S-085-12 | Photos without an exposure value are excluded from that histogram and counted as excluded. |
| S-085-13 | Streaks: consecutive capture days produce the expected longest daily streak, longest weekly streak and longest break. |
| S-085-14 | `Statistics::userSpace` is no longer registered; `Statistics::getCountsOverTime` and the storage endpoints still answer (v7 Statistics page, album drawer). |
| S-085-15 | A photo stored as 2024-12-31 23:30 UTC with `taken_at_orig_tz` `+09:00` counts on 2025-01-01 at hour 08, in year 2025. |
| S-085-16 | Undated photos are excluded from time-based values and reported in the undated count. |
| S-085-17 | Second request with an unchanged library returns the cached aggregate; after a photo edit the aggregate is recomputed. |
| S-085-18 | "NIKON CORPORATION" and "Nikon" photos are grouped under one manufacturer; an iPhone is classified as mobile device, a Canon EOS as camera, an unknown maker as other. |
| S-085-19 | Places: located count and share match the photos with coordinates; no map link when the map is disabled. |
| S-085-20 | Album owner scopes Insights to their album: photos of the album and its descendants are counted, including photos uploaded by others. |
| S-085-21 | A non-administrator naming an album they do not own → 403; an administrator may name any album. |
| S-085-22 | Unknown album ID or a tag album → 422. |
| S-085-23 | Album scope is cached apart from the owner scope and recomputed when a photo is added to the tree. |
| S-085-24 | Orientation, aspect-ratio groups and dimensions match the original size variants; items without one count as unknown. |
| S-085-25 | Focal values are reported per device, each device sorted by count. |
| S-085-26 | Timeline lists first/last capture, first and last photo per device, the 100th-item milestone date, records with their values, and the longest break and streak bounds, in date order. |
| S-085-27 | The calendar returns photos per local day for every period. |

## Test Strategy
- **Models / Actions:** unit tests for the pure helpers (streaks, breaks, histogram statistics, mode/median, local-time conversion, device normaliser) in `tests/Unit`.
- **REST API:** `tests/Feature_v3` feature tests for S-085-02 … S-085-14 (scope rights, periods, aggregates on seeded photos).
- **Frontend (v8):** `npm run check`; manual browser check of every section, dark mode and RTL.
- **Docs/Contracts:** new resources exported with `#[TypeScript]`; regenerated types.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-085-01 | Insights scope: own library \| user ID (admin) \| whole instance (admin) \| album tree (owner or admin). | requests, policies |
| DO-085-02 | Insights period: library \| year (YYYY) \| range (from, to as `Y-m-d`, inclusive); `App\Enum\InsightsPeriodType`. | requests, actions |
| DO-085-03 | `App\Enum\DeviceCategory` (camera, mobile, other), `App\Enum\TimelineCategory` (first_last, device, milestone, record, break) and `App\Enum\TimelineEventKind`, exported to TypeScript. | resources, frontend |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-085-01 | REST GET /api/v3/Insights | Phase-1 aggregates for a scope and period. Query: `owner_id` (admin only), `whole_instance` (admin only) or `album_id` (album owner or admin), `period` (`library`\|`year`\|`range`), `year`, `from`, `to`. | SE-gated (`support:se`), login required. |
| API-085-02 | REST GET /api/v2/Statistics::sizeVariantSpace, ::albumSpace, ::totalAlbumSpace | Unchanged; used by the Storage section and the album drawer. | FR-085-03 |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-085-01 | SE preview | SE disabled with `enable_se_preview` on: preview with SE tag. |
| UI-085-02 | Loading | The configured loading indicator while the aggregate loads (FR-085-21). |
| UI-085-03 | Empty | No photos in scope/period: each section shows an empty message. |
| UI-085-04 | Scope selector | Shown to administrators only. |

## Telemetry & Observability
None.

## Documentation Deliverables
- Roadmap row for 085.
- Knowledge map: Insights module, removal of the Statistics page.
- ADR-085-01, ADR-085-02, ADR-085-03, ADR-085-04.
- Dependency rationale for `echarts` and `vue-echarts` in the plan.

## Fixtures & Sample Data
Seeded photos with fixed `taken_at`, `taken_at_orig_tz`, EXIF and original size-variant `filesize` values built in the feature tests; no new sample files.

## Spec DSL

```
domain_objects:
  - id: DO-085-01
    name: InsightsScope
    values: [own, user, instance]
  - id: DO-085-02
    name: InsightsPeriod
    values: [library, year, range]
routes:
  - id: API-085-01
    method: GET
    path: /api/v3/Insights
  - id: API-085-02
    method: GET
    path: /api/v2/Statistics::sizeVariantSpace | ::albumSpace | ::totalAlbumSpace
ui_states:
  - id: UI-085-01
    description: SE preview
  - id: UI-085-02
    description: Loading skeletons
  - id: UI-085-03
    description: Empty scope or period
  - id: UI-085-04
    description: Administrator scope selector
```
