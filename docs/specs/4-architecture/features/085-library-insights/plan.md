# Feature Plan 085 – Library Insights

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented (owner check on an SE instance pending)  
_Last updated:_ 2026-10-05

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A photographer opens Insights and sees, for their whole library, one year or a date range, how much they shoot, when (in the local time of each photo), with which devices and settings, and how their storage is spent. Administrators get the same view for any user or the whole instance. The Statistics page is gone; its storage panels live on in Insights.

Success signals:
- `GET /api/v3/Insights` returns the phase-1 aggregates for every scope and period, with rights enforced (S-085-01 … S-085-08, S-085-10 … S-085-13, S-085-15 … S-085-19).
- A second request on an unchanged library is served from cache; an edit invalidates it (S-085-17).
- The v8 Insights page renders every phase-1 section with ECharts, in light, dark and RTL, and the SE preview without an API call (S-085-09).
- ECharts is absent from the main bundle and every other route chunk (NFR-085-07).
- The removed endpoints answer 404 and the album drawer still shows its storage figures (S-085-14).

## Scope Alignment
- **In scope:** FR-085-01 … FR-085-15, NFR-085-01 … NFR-085-07.
- **Out of scope:** the spec's Non-Goals (v7 Insights, phases 2 and 3, fun facts, focal cones, shared photos, counters and live metrics, album scope and drawer changes, drill-down, device override rules, countries).

## Dependencies & Interfaces
- **New npm dependencies (approved by the owner through Q-085-06 and Q-085-13, 2026-10-05):** `echarts` `^6.1.0` (Apache-2.0) and `vue-echarts` `^8.3.1` (MIT, peers `vue ^3.3.0`, `echarts ^6.0.0`, both satisfied). Rationale: every chart of phases 1–3 is built in (calendar heatmap, treemap, themeRiver streamgraph, radar), SVG renderer, one theme object; measured tree-shaken cost 250 KB gzipped, confined to the Insights chunk ([ADR-085-04](../../../6-decisions/ADR-085-04-echarts-chart-library.md)).
- `support:se` middleware (`LycheeVerify\Http\Middleware\VerifySupporterStatus`, 402 when SE is missing); `RequireSE` test trait (`requireSe()` / `resetSe()`).
- Kept Statistics endpoints `Statistics::sizeVariantSpace`, `::albumSpace`, `::totalAlbumSpace` and v8 components `statistics/SizeVariantMeter.vue`, `AlbumsTable.vue`, `TotalCard.vue` (FR-085-03, FR-085-07).
- `GET /api/v2/UserManagement` for the administrator user selector (FR-085-04).
- `usePreviewData` (`resources/js/composables/preview/getPreviewInfo.ts`) for SE preview data (FR-085-01).
- `FileExtensionService::SUPPORTED_VIDEO_MIME_TYPES` and the photo MIME list for the photo / video / other split.
- `faces` (`photo_id`, `person_id`, `is_dismissed`) for the People section.
- Configs `low/medium/high_number_of_shoots_per_day` (calendar colours), `map_display` (Places link).
- Laravel cache (ADR-085-03).

## Assumptions & Risks
- **Assumptions:**
  - `taken_at_orig_tz` holds a value `DateTimeZone` accepts (offset like `+02:00` or an identifier); anything else falls back to the stored `taken_at` (FR-085-15).
  - Timezone offsets lie within −12 h … +14 h, so a 14-hour widening of the UTC window catches every local date in the period (NFR-085-06).
  - CI runs MySQL/MariaDB and PostgreSQL; locally only SQLite is run (NFR-085-05).
- **Risks / Mitigations:**
  - Whole-instance first load on a very large library takes seconds. Mitigation: chunked cursor with accumulators only; measured on a seeded scratch database in T-085-25; precomputed tables are the documented next step (ADR-085-03).
  - Floating-point aggregates (mean of aperture, focal) differ across drivers when computed in SQL. Mitigation: values are read raw and aggregated in PHP, so drivers only return rows.
  - `shutter` is stored as text (`1/250 s`, `2 s`). Mitigation: the distribution helper parses it to seconds and keeps the original label for display; unparsable values count as excluded (S-085-12).
  - ECharts colours from CSS variables do not follow a theme switch automatically. Mitigation: the theme builder is reactive on the colour-mode ref and re-applied to every chart.
  - Tooltips render EXIF strings. Mitigation: tooltip formatters escape HTML (ADR-085-04).
  - `lang/<locale>/statistics.php` is shared with live metrics. Mitigation: only the `punch_card` block is removed.

## Approvals
- 2026-10-05: owner approved deleting the 18 paths listed in T-085-14 and T-085-21.
- 2026-10-07: owner approved deleting `app/Enum/InsightsGranularity.php` (unused once the calendar returns days for every period).

## Implementation Drift Gate
After the last task: map every FR/NFR to code and tests in a table under this section, rerun the quality gate (`vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, every test class listed in tasks, `make phpstan`), confirm the `vite build` chunk split, and record findings and lessons here.

### Drift Gate Report – 2026-10-05

**Verification evidence**
- Test classes, run one at a time, all green (117 tests): `LocalCaptureTimeTest` (6), `StreakCalculatorTest` (5), `DistributionTest` (7), `ExposureParserTest` (26), `DeviceNormaliserTest` (19), `InsightsRightsTest` (13), `InsightsAggregateOverviewTest` (10), `InsightsAggregateChartsTest` (6), `InsightsCacheTest` (7), `StatisticsRemovedTest` (3), and the kept `SizeVariantSpaceTest` (2), `AlbumSpaceTest` (4), `TotalAlbumSpaceTest` (2), `LangTest` (2).
- `vendor/bin/php-cs-fixer fix`, `make phpstan`, `npm run format`, `npm run check`, eslint on every touched frontend file: clean.
- `vite build`: `echarts-*.js` (184 kB gzip) and `vue-echarts-*.js` are imported statically only by `Insights-*.js`; `app-v8` reaches them through the route's dynamic import (NFR-085-07).
- Scratch instance (SQLite, uploads and job directories in the agent scratchpad, PHP built-in server, `V8_ENABLED`/`NUXT_UI_ENABLED`), 10 sample files imported with `lychee:sync` (LG, Canon, Nikon EXIF, `+01:00`/`+02:00` timezones, one video), driven with Playwright + system Chromium: 20 checks passing — every section renders, charts as SVG, People hidden without faces, manufacturers normalised (`NIKON CORPORATION` → Nikon, `LGE` → LG), shutter notation in the exposure summary, Year period switches to the day calendar, storage meter only in the administrator's whole-instance scope, Insights in the left menu and no Statistics entry, no console errors. Screenshots reviewed in dark and light mode.

**FR → implementation**
- FR-085-01: `routes/api_v3.php` (`support:se`), `routes/web_v2.php` `/insights`, `resources/js/router/paths.ts`, `v8/router/routes.ts`, `v8/composables/contextMenus/leftMenu.ts`, `v8/views/Insights.vue`, `usePreviewData().getInsightsData()` — `InsightsRightsTest`, browser run.
- FR-085-02/03: deleted Statistics files (approved list), `StatisticsController`, `routes/api_v2.php`, `statistics-service.ts`, v7 left menu, `statistics.punch_card` keys — `StatisticsRemovedTest`, kept Statistics tests.
- FR-085-04/05: `GetInsightsRequest`, `InsightsScope`, `InsightsPeriod`, `InsightsPeriodType` — `InsightsRightsTest`, `InsightsAggregateOverviewTest`.
- FR-085-06 … 09, 14: `ComputeInsights`, `Accumulators\OverviewAccumulator`, `Accumulators\TimeAccumulator`, `StreakCalculator`, `ResolveInsightsThumbs`, overview/storage/people/places/time-span components — `InsightsAggregateOverviewTest`, `StreakCalculatorTest`.
- FR-085-10 … 13, 15: `TimeAccumulator`, `DeviceAccumulator`, `ExposureAccumulator`, `LocalCaptureTime`, `DeviceNormaliser`, `Distribution`, `ExposureParser`, calendar/rhythm/devices/exposure components and option builders — `InsightsAggregateChartsTest`, helper unit tests.
- NFR-085-06: `CachedInsights`, `InsightsRevision` — `InsightsCacheTest`.

**Low-impact divergences, folded into the spec**
- `photos.filesize` is never written by the upload pipeline; sizes are read from the ORIGINAL size variant (NFR-085-06, FR-085-07, S-085-11). Found in the browser run (all sizes "unknown"); the test dataset now sets the variant size.
- The storage meter and album table come from endpoints without an owner parameter, so they show for a user's own library and for administrators in whole-instance scope only (FR-085-07).
- People reports faces per photo with faces rather than distinct people per photo (FR-085-08).
- Removed endpoints fall through to the web catch-all (406 for JSON requests) instead of 404; `StatisticsRemovedTest` asserts they are no longer registered (S-085-14).
- `Spaces::getFullSpacePerUser` stays: the upload quota and user management use it.
- `ShutterParser` became `ExposureParser` (ISO, aperture, focal, shutter, duration); `InsightsGranularity` enum added for the calendar cell size.

**Outstanding**
- Owner check on an SE-licensed instance: real thumbnails in the time-span cards (the scratch instance did not serve uploads), RTL locale, MySQL/MariaDB and PostgreSQL through CI.

**Lessons**
- The PHP built-in server must run from `public/` (the router resolves the public path from the working directory), otherwise it serves the root guard page.
- A test seeding the column the code reads proves nothing about where production writes it; the browser run against an import found the unused `photos.filesize`.

### Drift Gate Addendum – 2026-10-07 (I12 … I17)

**Verification evidence**
- Test classes, one at a time, all green (162 tests): the fourteen classes above plus `AspectRatioClassifierTest` (12), `MilestoneFinderTest` (3), `InsightsFormatsTest` (4), `InsightsTimelineTest` (6), `InsightsAlbumScopeTest` (8).
- `vendor/bin/php-cs-fixer fix`, `make phpstan`, `npm run format`, `npm run check`, eslint on every touched file: clean. `vite build`: `echarts-*.js` (219 kB gzip with scatter, line, sunburst, treemap, dataZoom) still imported only by `Insights-*.js`.
- Scratch instance rebuilt (SQLite, scratchpad uploads, `lychee:sync` of a nested import → album tree import / Home / Travels / Germany, Mongolia), Playwright + system Chromium, 20 checks passing: loading indicator while the API is held, every section rendered, space diagram in the administrator's own library, 631 calendar squares, five focal cones, nine dimension frames, timeline list with device events, album scope on Travels (5 items), no console errors. Screenshots reviewed: GitHub-style day grid (Mon … Sun) and years × weeks grid, three-ring sunburst, single-hue treemap with drill-down, timeline labels without collisions.

**FR → implementation**
- FR-085-10: `TimeAccumulator::toCalendar()` (days), `options/calendar.ts` (`dayGridOption`, `weekGridOption`, square cells with gaps in the card colour), `CalendarSection.vue` — `InsightsAggregateChartsTest`, browser run.
- FR-085-16: `GetInsightsRequest` (`album_id`), `InsightsScope::album()`, `InsightsScopeQuery`, `ComputeInsights::albumLinks()`, `InsightsRevision`, `Statistics\Album::$id`, album selector in `Insights.vue` — `InsightsAlbumScopeTest`.
- FR-085-17: `AspectRatioClassifier`, `AspectRatioGroup`, `ImageOrientation`, `FormatAccumulator`, `FormatsSection.vue` — `AspectRatioClassifierTest`, `InsightsFormatsTest`.
- FR-085-18: per-device focal distributions in `DeviceAccumulator`, `FocalSection.vue` — `InsightsAggregateChartsTest`, browser run.
- FR-085-19: `TimelineAccumulator`, `MilestoneFinder`, `TimelineCategory`, `TimelineEventKind`, `ComputeInsights::timeline()`, `options/timeline.ts`, `TimelineSection.vue` — `InsightsTimelineTest`, `MilestoneFinderTest`.
- FR-085-20: `options/space.ts` (`spaceTree`, `spaceOption`), `SpaceDiagram.vue`, `SizeVariantMeter.vue` optional `album-id` — browser run.
- FR-085-21: `LycheeLoadingIcon` in `Insights.vue` — browser run.

**Low-impact divergences, folded into the spec**
- The space diagram is shown in every scope, filtered client-side by owner name (FR-085-20).
- Timeline labels are laid out on six lanes by the client; labels without a free lane stay hidden and the list below shows every event (FR-085-19).
- ECharts `dayLabel.nameMap` is Sunday-first whatever `firstDay` is.

**Lessons**
- Build trees from nested-set rows before filtering empty nodes: dropping albums without own photos first turns their children into roots.
- ECharts `labelLayout.hideOverlap` did not hide labels across scatter items here; deterministic lane assignment is predictable and testable.

## Increment Map

1. **I1 – Pure helpers** (FR-085-09, FR-085-12, FR-085-13, FR-085-15; NFR-085-03)
   - _Steps:_ failing unit tests, then `app/Actions/Insights/Helpers/`: `LocalCaptureTime` (UTC + tz → local date, ISO week, month, weekday, hour; fallback on bad tz), `StreakCalculator` (sorted local dates → longest daily streak, longest weekly streak, longest break, days with photos), `DistributionSummary` (value→count map → median, mean, mode, min, max, buckets), `ExposureParser`, `DeviceNormaliser` + enum `DeviceCategory` (camera, mobile, other).
   - _Commands:_ `php artisan test --filter=LocalCaptureTimeTest`, `…StreakCalculatorTest`, `…DistributionTest`, `…ExposureParserTest`, `…DeviceNormaliserTest`, `make phpstan`.
2. **I2 – Route, request, rights** (FR-085-04, FR-085-05; S-085-02, S-085-03, S-085-07, S-085-09, S-085-10; DO-085-01, DO-085-02)
   - _Steps:_ failing `InsightsRightsTest`; `InsightsScope` and `InsightsPeriod` DTOs; `GetInsightsRequest` (rules, `authorize` via a `SettingsPolicy`/`UserPolicy` check that only administrators may set `owner_id` / `whole_instance`); `InsightsController::index` returning an empty resource; `Route::get('/Insights', …)->middleware(['support:se'])` in `routes/api_v3.php`.
3. **I3a – Aggregation: overview, storage, people, places, time span** (FR-085-06 … FR-085-09, FR-085-14; NFR-085-05, NFR-085-06)
   - _Steps:_ failing `InsightsAggregateOverviewTest`; `ComputeInsights` action (windowed cursor, `EXISTS` album flag, album/face ID streams); accumulators; `InsightsResource` and nested `#[TypeScript]` resources; first/last/busiest thumbnails resolved for three photo IDs.
4. **I3b – Aggregation: calendar, rhythm, devices, exposure** (FR-085-10 … FR-085-13, FR-085-15)
   - _Steps:_ failing `InsightsAggregateChartsTest`; remaining accumulators and resources; `php artisan typescript:transform`.
5. **I4 – Cache and revision** (NFR-085-06; S-085-17)
   - _Steps:_ failing `InsightsCacheTest`; `InsightsRevision` (photo and album counts and latest `updated_at` for the scope); `Cache::remember` keyed on (scope, period, revision) with a one-hour TTL; rights checked before lookup.
6. **I5 – Backend removal** (FR-085-02, FR-085-03; S-085-14)
   - _Preconditions:_ owner approval of the exact deletion list in T-085-14.
   - _Steps:_ failing `StatisticsRemovedTest` (the two endpoints no longer registered); delete the listed backend files and tests and the two routes (`Spaces::getFullSpacePerUser` stays for the upload quota and user management); `php artisan typescript:transform`; rerun the kept Statistics test classes.
7. **I6 – Frontend groundwork** (FR-085-01, FR-085-04, FR-085-05; NFR-085-04, NFR-085-07)
   - _Steps:_ `npm install echarts@^6.1.0 vue-echarts@^8.3.1`; `v8/utils/insights/echarts.ts` (core registration: Bar, Line, Pie, Heatmap charts; Grid, Tooltip, Calendar, VisualMap, Legend, Dataset components; SVGRenderer); `v8/composables/insights/useInsightsTheme.ts`; `services/insights-service.ts`; `/insights` in `router/paths.ts`, v8 `routes.ts`, `routes/web_v2.php`; v8 left menu entries (SE / preview) replacing Statistics; `lang/<locale>/insights.php` for every locale + `php artisan lang:json`; `v8/views/Insights.vue` shell with scope and period selectors and loading/empty states.
8. **I7 – Sections: overview, storage, people, places, time span** (FR-085-06 … FR-085-09, FR-085-14; UI-085-02, UI-085-03)
   - _Steps:_ `v8/components/insights/` `OverviewSection.vue`, `StorageSection.vue` (reuses `SizeVariantMeter`, `AlbumsTable`), `PeopleSection.vue`, `PlacesSection.vue`, `TimeSpanSection.vue`.
9. **I8 – Sections: calendar, rhythm, devices, exposure** (FR-085-10 … FR-085-13)
   - _Steps:_ `CalendarSection.vue` (ECharts calendar + heatmap, thresholds from configs), `RhythmSection.vue` (7 × 24 heatmap, three bar charts), `DevicesSection.vue` (grouping, metric and category switches), `ExposureSection.vue` (tabs, histogram, scale switch, summary line); option builders in `v8/utils/insights/options/*.ts` with escaped tooltip formatters.
10. **I9 – SE preview** (FR-085-01; S-085-09, UI-085-01)
    - _Steps:_ `getInsightsData()` in `usePreviewData`; `Insights.vue` uses it when `is_se_preview_enabled`.
11. **I10 – Frontend removal** (FR-085-02)
    - _Preconditions:_ owner approval of the exact deletion list in T-085-21.
    - _Steps:_ delete the listed views and components; remove v7 menu entries (`resources/js/composables/contextMenus/leftMenu.ts`), router entries, `getCountsOverTime`/`getUserSpace`; remove `statistics.punch_card` in every locale + `php artisan lang:json`.
12. **I11 – Verification and docs**
    - _Steps:_ full quality gate; `vite build` chunk check; scratch-instance browser run (Playwright, as Feature 082) across sections, scopes, periods, dark mode, RTL and preview; timing of a whole-instance first load on a seeded scratch database; knowledge map, roadmap, drift gate.

13. **I12 – Calendar days, image formats, focal per device** (FR-085-10, FR-085-17, FR-085-18; S-085-24, S-085-25, S-085-27)
    - _Steps:_ failing assertions in `InsightsAggregateChartsTest` and `InsightsFormatsTest`; `Helpers\AspectRatioGroup`; `Accumulators\FormatAccumulator`; per-device focal distributions in `DeviceAccumulator`; calendar always per day; original `width`/`height` in the projection; resources; `php artisan typescript:transform`.
14. **I13 – Timeline events** (FR-085-19; S-085-26)
    - _Steps:_ failing `InsightsTimelineTest`; `TimelineCategory`/`TimelineEventKind` enums; `Accumulators\TimelineAccumulator` (firsts, per-device first/last, records); milestones from the day counts; break/streak bounds from the time span; `TimelineEventData`.
15. **I14 – Album scope** (FR-085-16; S-085-20 … S-085-23)
    - _Steps:_ failing `InsightsAlbumScopeTest`; `album_id` in `GetInsightsRequest` (ownership or administrator); `InsightsScope::album()` with nested-set bounds; scoped queries in `ComputeInsights` and `InsightsRevision`; `id` on the Statistics `Album` resource for the selector.
16. **I15 – Frontend: calendar squares, loader, album selector** (FR-085-10, FR-085-16, FR-085-21)
    - _Steps:_ square cells for both calendar grids; `LycheeLoadingIcon` while loading; searchable album selector fed by `Statistics::albumSpace`; `SizeVariantMeter` optional `album-id` prop.
17. **I16 – Frontend: formats, focal cones, timeline, space diagram** (FR-085-17 … FR-085-20)
    - _Steps:_ `FormatsSection.vue` (orientation cards, aspect-ratio bars, SVG frame chart and list); `FocalSection.vue` (SVG cones); `TimelineSection.vue` (ECharts time axis with dataZoom, density area, category filter, list); space diagram (ECharts sunburst/treemap) in `StorageSection.vue`; ECharts registration gains scatter, line, sunburst, treemap, dataZoom; lang keys in every locale.
18. **I17 – Verification and docs**
    - _Steps:_ quality gate; `vite build` chunk check; scratch-instance Playwright run with album scope; drift gate addendum; knowledge map and roadmap.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-085-01 | I3a, I3b, I7, I8 / T-085-08, T-085-09, T-085-17, T-085-18, T-085-25 | `InsightsAggregateOverviewTest`, browser run |
| S-085-02 | I2 / T-085-06, T-085-07 | `InsightsRightsTest` |
| S-085-03 | I2 / T-085-06, T-085-07 | `InsightsRightsTest` |
| S-085-04 | I3a / T-085-08, T-085-09 | `InsightsAggregateOverviewTest` |
| S-085-05 | I3a / T-085-08, T-085-09 | `InsightsAggregateOverviewTest` |
| S-085-06 | I3a / T-085-08, T-085-09 | `InsightsAggregateOverviewTest` |
| S-085-07 | I2 / T-085-06, T-085-07 | `InsightsRightsTest` |
| S-085-08 | I3a, I7 / T-085-08, T-085-09, T-085-17 | empty library |
| S-085-09 | I2, I9 / T-085-06, T-085-07, T-085-20, T-085-25 | 402 test; preview in browser |
| S-085-10 | I2 / T-085-06, T-085-07 | `InsightsRightsTest` |
| S-085-11 | I3a / T-085-08, T-085-09 | `InsightsAggregateOverviewTest` |
| S-085-12 | I1, I3b / T-085-03, T-085-10, T-085-11 | `DistributionTest`, `ExposureParserTest`, `InsightsAggregateChartsTest` |
| S-085-13 | I1, I3a / T-085-01, T-085-02, T-085-08, T-085-09 | `StreakCalculatorTest` |
| S-085-14 | I5, I10 / T-085-14, T-085-15, T-085-21, T-085-25 | `StatisticsRemovedTest`, kept Statistics tests, drawer in browser |
| S-085-15 | I1, I3b / T-085-01, T-085-02, T-085-10, T-085-11 | `LocalCaptureTimeTest`, `InsightsAggregateChartsTest` |
| S-085-16 | I3a / T-085-08, T-085-09 | `InsightsAggregateOverviewTest` |
| S-085-17 | I4 / T-085-12, T-085-13 | `InsightsCacheTest` |
| S-085-18 | I1, I3b / T-085-04, T-085-05, T-085-10, T-085-11 | `DeviceNormaliserTest`, `InsightsAggregateChartsTest` |
| S-085-19 | I3a, I7 / T-085-08, T-085-09, T-085-17 | `InsightsAggregateOverviewTest`, browser run |
| S-085-20 … S-085-23 | I14 / T-085-30, T-085-31 | `InsightsAlbumScopeTest` |
| S-085-24, S-085-25, S-085-27 | I12 / T-085-26, T-085-27 | `InsightsFormatsTest`, `InsightsAggregateChartsTest` |
| S-085-26 | I13 / T-085-28, T-085-29 | `InsightsTimelineTest` |

## Analysis Gate
Completed 2026-10-05 (agent self-review; owner acknowledged by asking to implement).

1. Specification completeness — pass: goals, FR-085-01 … 15, NFR-085-01 … 07, ASCII mock-up, scenarios S-085-01 … 19; all thirteen answers folded into normative sections.
2. Open questions — pass: no open entry in [open-questions.md](open-questions.md); ADR-085-01 … 04 record the architecturally significant answers (Q-085-01/02, 03, 07/08, 06/13).
3. Plan alignment — pass: plan links spec and tasks; dependencies (`echarts`, `vue-echarts`, kept Statistics endpoints, `support:se`) match the spec.
4. Tasks coverage — pass: every FR maps to at least one task (see tasks notes); every backend increment starts with failing tests; frontend increments are verified by `npm run check` and the scripted browser run because no JS unit runner exists.
5. Working-agreement compliance — pass: spec-first; dependencies approved by the owner (Q-085-06, Q-085-13); time, streak, distribution, shutter and device logic isolated in pure helpers returning records or enums; deletions gated on explicit path approval (T-085-14, T-085-21); new tests in `tests/Feature_v3` and `tests/Unit`; no Carbon; ADR-085-01 … 04 reviewed.
6. Tooling readiness — pass: commands listed per increment and per task.

## Exit Criteria
- All tasks `[x]`; quality gate green (`vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, every listed test class, `make phpstan`).
- `php artisan typescript:transform` and `php artisan lang:json` rerun.
- `vite build`: ECharts only in the Insights chunk.
- Knowledge map and roadmap updated; drift gate recorded above.

## Follow-ups / Backlog
- Phase 2 feature: milestone timeline, evolution by year, tendencies radar.
- Phase 3 feature: photo-profile indices, exposure triangle, dimension frame chart, export.
- Drill-down from chart elements to photos.
- Precomputed summary tables if whole-instance first loads are too slow (ADR-085-03).
- Album statistics drawer: aperture rendered twice, focal length never shown (v7 and v8), separate fix.
- `photos.filesize` is a legacy column the upload pipeline never writes; drop it or start maintaining it.
