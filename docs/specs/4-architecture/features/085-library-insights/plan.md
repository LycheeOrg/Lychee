# Feature Plan 085 – Library Insights

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Draft  
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

## Implementation Drift Gate
After the last task: map every FR/NFR to code and tests in a table under this section, rerun the quality gate (`vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, every test class listed in tasks, `make phpstan`), confirm the `vite build` chunk split, and record findings and lessons here.

## Increment Map

1. **I1 – Pure helpers** (FR-085-09, FR-085-12, FR-085-13, FR-085-15; NFR-085-03)
   - _Steps:_ failing unit tests, then `app/Actions/Insights/Helpers/`: `LocalCaptureTime` (UTC + tz → local date, ISO week, month, weekday, hour; fallback on bad tz), `StreakCalculator` (sorted local dates → longest daily streak, longest weekly streak, longest break, days with photos), `DistributionSummary` (value→count map → median, mean, mode, min, max, buckets), `ShutterParser`, `DeviceNormaliser` + enum `DeviceCategory` (camera, mobile, other).
   - _Commands:_ `php artisan test --filter=LocalCaptureTimeTest`, `…StreakCalculatorTest`, `…DistributionSummaryTest`, `…ShutterParserTest`, `…DeviceNormaliserTest`, `make phpstan`.
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
   - _Steps:_ failing `StatisticsRemovedTest` (404 for the two endpoints); delete the listed backend files and tests, `getFullSpacePerUser`, routes; `php artisan typescript:transform`; rerun the kept Statistics test classes.
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
| S-085-12 | I1, I3b / T-085-03, T-085-10, T-085-11 | `DistributionSummaryTest`, `ShutterParserTest`, `InsightsAggregateChartsTest` |
| S-085-13 | I1, I3a / T-085-01, T-085-02, T-085-08, T-085-09 | `StreakCalculatorTest` |
| S-085-14 | I5, I10 / T-085-14, T-085-15, T-085-21, T-085-25 | `StatisticsRemovedTest`, kept Statistics tests, drawer in browser |
| S-085-15 | I1, I3b / T-085-01, T-085-02, T-085-10, T-085-11 | `LocalCaptureTimeTest`, `InsightsAggregateChartsTest` |
| S-085-16 | I3a / T-085-08, T-085-09 | `InsightsAggregateOverviewTest` |
| S-085-17 | I4 / T-085-12, T-085-13 | `InsightsCacheTest` |
| S-085-18 | I1, I3b / T-085-04, T-085-05, T-085-10, T-085-11 | `DeviceNormaliserTest`, `InsightsAggregateChartsTest` |
| S-085-19 | I3a, I7 / T-085-08, T-085-09, T-085-17 | `InsightsAggregateOverviewTest`, browser run |

## Analysis Gate
Completed 2026-10-05 (agent self-review; awaiting owner acknowledgement).

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
