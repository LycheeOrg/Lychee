# Feature 085 – Library Insights

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-10-05 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #085 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
A v8 Insights page describes one photographer's library: how much they shoot, when, with which devices and settings, and how their storage is spent. It replaces the Statistics page ([ADR-085-01](../../../6-decisions/ADR-085-01-insights-replaces-statistics-page.md)): the storage panels move into an Insights "Storage" section, and the activity punch card becomes the Insights calendar heatmap. Insights cover the viewer's own photos; administrators also choose any single user or the whole instance ([ADR-085-02](../../../6-decisions/ADR-085-02-owner-scoped-insights.md)). The whole page is a Supporter Edition (SE) feature, with the existing SE preview in core.

The section catalogue follows a dashboard built for another famous photo app. This feature delivers phase 1; phases 2 and 3 are follow-up features (see Non-Goals).

Layers: one aggregate action that streams a narrow photo projection once per scope and period, cached per library revision ([ADR-085-03](../../../6-decisions/ADR-085-03-insights-single-pass-aggregation.md)); a pure device normaliser; one resource; `GET /api/v3/Insights`; removal of `Statistics::getCountsOverTime` and `Statistics::userSpace`; v8 frontend (new `Insights` view and section components drawn with ECharts loaded as a lazy chunk ([ADR-085-04](../../../6-decisions/ADR-085-04-echarts-chart-library.md)), left-menu entry); removal of the v7 and v8 Statistics views.

## Goals
- G1: one page answers "what does my library look like" for the whole library, one year, or a date range.
- G2: storage figures from the Statistics page stay available, inside Insights.
- G3: a user never sees aggregates over another owner's photos unless they are an administrator.
- G4: phase 1 sections (FR-085-05 … FR-085-13) ship together.

## Non-Goals
- v7 frontend: v7 loses the Statistics menu entry and view; it gets no Insights page.
- Phase 2 (follow-up feature): milestone timeline, evolution by year (cumulative counts, MB per file, resolution, device streamgraph), tendencies radar.
- Phase 3 (follow-up feature): photo-profile indices, exposure triangle, image-dimension frame chart, CSV/PDF/JPG/SQLite export.
- "For perspective" fun facts and focal-length field-of-view cones.
- Aggregates over photos shared with the viewer but owned by someone else.
- Changes to the per-photo/per-album counters (`statistics` table) and live metrics.
- Changes to the album statistics drawer, and insights for an album subtree.
- Drill-down: chart elements do not open the matching photos.
- Per-user device override rules.
- Country or continent breakdown (Lychee stores no country).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-085-01 | Insights page in v8 at `/insights`, entered from the left menu where Statistics was. | SE enabled and logged in: the page loads the default scope (own library, Library period). | Not logged in: redirect to the gallery, as Statistics did. | SE disabled and `enable_se_preview` on: the menu entry shows the SE tag and the page renders every section from sample data generated in the browser (`composables/preview/getPreviewInfo.ts`), under the preview notice, without calling the API. SE disabled: API → 402 (`support:se`). | None. | Q-085-01, Q-085-02, Q-085-04 |
| FR-085-02 | The Statistics page is removed. | Deleted: `/statistics` web route and router entries; `resources/js/v7/views/Statistics.vue`, `resources/js/v8/views/Statistics.vue`; `Activity.vue`, `PunchCard.vue`, `PunchCardCaption.vue` under `resources/js/v7/components/statistics/` and `resources/js/v8/components/statistics/`; `resources/js/v7/components/statistics/AlbumsTable.vue`; `GET /api/v2/Statistics::getCountsOverTime` and `GET /api/v2/Statistics::userSpace` with their controller methods, `CountsRequest`, `SpacePerUserRequest`, the `Counts` action, the `CountType` enum, the `CountsData`, `DayCount` and `UserSpace` resources, `getFullSpacePerUser` in `Spaces`, `getCountsOverTime`/`getUserSpace` in `statistics-service.ts`, `tests/Feature_v2/Statistics/CountsOverTimeTest.php`, `tests/Feature_v2/Statistics/UserSpaceTest.php`, and the `statistics.punch_card` lang keys. | — | — | None. | Q-085-01 |
| FR-085-03 | `Statistics::sizeVariantSpace`, `Statistics::albumSpace` and `Statistics::totalAlbumSpace` stay: the album statistics drawer (v7 and v8) and the Insights Storage section use them. | Unchanged behaviour and gating. | Unchanged. | Unchanged. | None. | Q-085-01 |
| FR-085-04 | Scope selector. Every user sees their own photos ("My library"). An administrator also picks any user or "Whole instance". | The page and every section reload for the chosen scope. | A non-administrator requesting another owner or the whole instance → 403. Unknown user ID → 422. | — | None. | Q-085-03, ADR-085-02 |
| FR-085-05 | Period selector: Library (all photos), Year (picker listing years that have photos), Range (from–to, both inclusive). | Every section except Storage (FR-085-07) is computed for the period. | Range with from > to → 422. Year outside the listed years → empty sections. | — | None. | Q-085-05 |
| FR-085-06 | Overview cards: total photos, split photos / videos / other; highlighted photos; albums (all owned albums for Library; albums with at least one photo in the period for Year/Range); photos in no album. | Counts shown with their split. | — | — | None. | Q-085-05 |
| FR-085-07 | Storage section: total size of originals, average size per photo and per video, size-variant meter, album space table. The meter and table are period-independent and shown for the Library period only; administrators see every owner's rows in "Whole instance" scope. | Values from the existing Storage endpoints (FR-085-03) and the period aggregate. | Photos without `filesize` are counted as "size unknown", not as 0. | — | None. | Q-085-01, Q-085-05 |
| FR-085-08 | People section: photos with at least one face, distinct people, total faces, average people per photo with people. | Values for the scope and period. | Section hidden when face recognition has never run (no `faces` rows for the scope). | — | None. | Q-085-05 |
| FR-085-09 | Time-span section: first and last capture (thumbnail), busiest day (thumbnail), days with photos and their share of calendar days in the span, undated photo count, longest break, longest daily streak, longest weekly streak. | Values for the scope and period. | Fewer than two dated photos: span, break and streak cards show "—". | — | None. | Q-085-05 |
| FR-085-10 | Calendar heatmap: a day grid for the Year period, a week grid for Library and Range. Cell colours use `low/medium/high_number_of_shoots_per_day`. | One cell per day/week with its count in a tooltip. | — | — | None. | Q-085-01, Q-085-05 |
| FR-085-11 | Rhythm: a 7 × 24 weekday × hour heatmap and bar charts by month, weekday and hour. | Counts for the scope and period. | — | — | None. | Q-085-05 |
| FR-085-12 | Devices: photo counts by device (normalised maker + model), by manufacturer and by lens, with a metric switch (all, photos, videos, highlighted, located, with people) and a category filter (camera, mobile device, other). A built-in normaliser maps EXIF maker spellings to one manufacturer ("NIKON CORPORATION", "Nikon" → Nikon) and classifies each device as camera (known camera makers), mobile device (known phone makers, or model keywords such as iPhone, Pixel, Galaxy) or other. | Top entries with the rest grouped as "Other". | Photos without `make`/`model`/`lens` counted as "Unknown"; unknown makers keep their trimmed EXIF spelling and the "other" category. | — | None. | Q-085-05, Q-085-10 |
| FR-085-13 | Exposure: histograms of ISO, focal length, shutter time, aperture and video length, with median, mean, mode, min and max, and a linear/log scale switch. Technique cards: most common ISO, focal length, shutter, aperture; total and average video duration. | Distributions for the scope and period. | Photos missing a value are excluded from that histogram; the excluded count is shown. | — | None. | Q-085-05 |
| FR-085-14 | Places: count and share of photos with `latitude` and `longitude`, with a link to the existing map view. | Values for the scope and period. | — | Map disabled (`map_display` off): no link. | None. | Q-085-11 |
| FR-085-15 | Every time-based value (days, weeks, months, weekdays, hours, years, streaks, period filters) uses the local capture time: `taken_at` converted to `taken_at_orig_tz`. Photos with no `taken_at` are left out of time-based values and counted as "undated". | A photo taken at 20:30 in Tokyo counts at hour 20 and on its Tokyo date. | Null or unparsable `taken_at_orig_tz`: the stored `taken_at` is used as is. | — | None. | Q-085-08 |
## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-085-01 | No request to an external host at runtime. | Lychee runs offline. | Code review; no URL outside the instance in the Insights code. | — | Owner directive |
| NFR-085-02 | Aggregates never include photos outside the requested scope. | Privacy between owners. | Feature tests per scope and role (S-085-02 … S-085-04). | ADR-085-02 | Q-085-03 |
| NFR-085-03 | Date arithmetic in new backend code uses native PHP date functions, not Carbon. | Resource cost per request. | Code review. | — | Owner directive |
| NFR-085-04 | All new strings in `lang/<locale>/*.php` for every locale; layout works in RTL. | i18n. | `php artisan lang:json`; RTL visual check. | — | Coding conventions |
| NFR-085-05 | MySQL/MariaDB, PostgreSQL and SQLite return identical aggregates. | Supported drivers. | Feature tests on SQLite locally, CI on the others. | — | Coding conventions |
| NFR-085-06 | Aggregates come from one streamed pass (`DB::table` cursor in chunks, no Eloquent models) over the projection `id`, `taken_at`, `taken_at_orig_tz`, `type`, `make`, `model`, `lens`, `iso`, `aperture`, `shutter`, `focal`, `filesize`, `duration`, `face_count`, `latitude`, `longitude`, `is_highlighted` and an `EXISTS` flag for album membership. SQL narrows a Year or Range period to the UTC window widened by 14 hours on each side; PHP keeps the photos whose local date falls in the period. Albums with a photo in the period and distinct people come from two more streams over `photo_album` and `faces` joined to the same windowed photos, filtered the same way and collected as ID sets (for the Library period, plain `COUNT` queries). The result is cached under (scope, period, revision); the revision is the photo count and latest `updated_at` of the scope's photos and albums. Cache entries expire after one hour. Memory stays flat in the library size: accumulators only, no per-photo array. | Large libraries; three drivers; local time. | Feature tests compare against expected aggregates; a cached response is returned when the revision is unchanged and recomputed after a photo is added, edited or deleted. | Laravel cache | Q-085-07, ADR-085-03 |
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
├─ RHYTHM ─────────────────────────────────────────────────────────────────┤
│       00 02 04 06 08 10 12 14 16 18 20 22    By month   ▁▂▃▅▇█▇▅▃▂▁▁     │
│ Mon   ░░░░░░░▒▒▓▓▒▒▒▓▓▒░░                    By weekday ▅▄▄▄▅▇█          │
│ …                                            By hour    ▁▁▁▂▅▇▇▆▆▇█▅     │
├─ DEVICES ────────────────────────────────────────────────────────────────┤
│ (Device | Manufacturer | Lens)  Metric [All ▾]  (All|Camera|Mobile|Other)│
│ iPhone 13 Pro   ███████████████  12 400                                  │
│ X-T4            ████████          6 210                                  │
│ Other           ██                1 020                                  │
├─ EXPOSURE ───────────────────────────────────────────────────────────────┤
│ (ISO | Focal | Shutter | Aperture | Video length)  (Linear | Log)        │
│ ▁▃█▇▅▃▂▁▁                 median 200 · mean 412 · mode 100 · 32–12800   │
│ Excluded (no value): 1 830                                               │
└──────────────────────────────────────────────────────────────────────────┘
```

UI states: SE preview (UI-085-01), loading skeleton per section (UI-085-02), empty scope/period (UI-085-03), forbidden scope never offered to non-administrators (UI-085-04).

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
| S-085-11 | Photos without `filesize` are reported as "size unknown" and excluded from averages. |
| S-085-12 | Photos without an exposure value are excluded from that histogram and counted as excluded. |
| S-085-13 | Streaks: consecutive capture days produce the expected longest daily streak, longest weekly streak and longest break. |
| S-085-14 | Removed endpoints `Statistics::getCountsOverTime` and `Statistics::userSpace` → 404; the album drawer still loads its storage figures. |
| S-085-15 | A photo stored as 2024-12-31 23:30 UTC with `taken_at_orig_tz` `+09:00` counts on 2025-01-01 at hour 08, in year 2025. |
| S-085-16 | Undated photos are excluded from time-based values and reported in the undated count. |
| S-085-17 | Second request with an unchanged library returns the cached aggregate; after a photo edit the aggregate is recomputed. |
| S-085-18 | "NIKON CORPORATION" and "Nikon" photos are grouped under one manufacturer; an iPhone is classified as mobile device, a Canon EOS as camera, an unknown maker as other. |
| S-085-19 | Places: located count and share match the photos with coordinates; no map link when the map is disabled. |

## Test Strategy
- **Models / Actions:** unit tests for the pure helpers (streaks, breaks, histogram statistics, mode/median, local-time conversion, device normaliser) in `tests/Unit`.
- **REST API:** `tests/Feature_v3` feature tests for S-085-02 … S-085-14 (scope rights, periods, aggregates on seeded photos).
- **Frontend (v8):** `npm run check`; manual browser check of every section, dark mode and RTL.
- **Docs/Contracts:** new resources exported with `#[TypeScript]`; regenerated types.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-085-01 | Insights scope: own library \| user ID (admin) \| whole instance (admin). | requests, policies |
| DO-085-02 | Insights period: library \| year (YYYY) \| range (from, to as `Y-m-d`, inclusive). | requests, actions |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-085-01 | REST GET /api/v3/Insights | Phase-1 aggregates for a scope and period. Query: `owner_id` (admin only) or `whole_instance` (admin only), `period` (`library`\|`year`\|`range`), `year`, `from`, `to`. | SE-gated (`support:se`), login required. |
| API-085-02 | REST GET /api/v2/Statistics::sizeVariantSpace, ::albumSpace, ::totalAlbumSpace | Unchanged; used by the Storage section and the album drawer. | FR-085-03 |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-085-01 | SE preview | SE disabled with `enable_se_preview` on: preview with SE tag. |
| UI-085-02 | Loading | Section skeletons while the aggregate loads. |
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
Seeded photos with fixed `taken_at`, `taken_at_orig_tz`, EXIF and `filesize` values built in the feature tests; no new sample files.

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
