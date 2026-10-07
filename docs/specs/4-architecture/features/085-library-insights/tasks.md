# Feature 085 Tasks – Library Insights

_Status: Implemented (owner check on an SE instance pending)_  
_Last updated: 2026-10-05_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Pure helpers

- [x] T-085-01 – Failing `LocalCaptureTimeTest` and `StreakCalculatorTest` (FR-085-09, FR-085-15, S-085-13, S-085-15).  
  _Intent:_ `tests/Unit/Actions/Insights/LocalCaptureTimeTest.php` (`AbstractTestCase`): offset tz, identifier tz with DST, date crossing a year boundary (S-085-15), null and garbage tz → stored value, ISO week 53. `StreakCalculatorTest`: single day, gaps, daily streak across a month end, weekly streak across a year end, longest break, days-with-photos share. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=LocalCaptureTimeTest` (expected red)  
  - `php artisan test --filter=StreakCalculatorTest` (expected red)

- [x] T-085-02 – `LocalCaptureTime` and `StreakCalculator` (FR-085-09, FR-085-15, NFR-085-03).  
  _Intent:_ `app/Actions/Insights/Helpers/LocalCaptureTime.php` (native `DateTimeImmutable`/`DateTimeZone`, timezone objects cached per string, returns a readonly record: date, year, month, ISO year-week, weekday, hour); `StreakCalculator.php` returning a readonly `StreakSummary`. License headers.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=LocalCaptureTimeTest` (green)  
  - `php artisan test --filter=StreakCalculatorTest` (green)  
  - `make phpstan`

- [x] T-085-03 – Failing `DistributionTest` and `ExposureParserTest` (FR-085-13, S-085-12).  
  _Intent:_ `DistributionTest`: odd/even median from a value→count map, mean, mode tie (lowest value), min/max, empty map. `ExposureParserTest`: shutter `1/250 s`, `1/250`, `2 s`, `0.5 s`, `30"`; ISO `100`, `ISO 400`; aperture `f/2.8`, `2.8`; focal `200 mm`, `24.5mm`; duration `12.5`; empty, zero, garbage → null. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=DistributionTest` (expected red)  
  - `php artisan test --filter=ExposureParserTest` (expected red)

- [x] T-085-04 – `Distribution`, `DistributionSummary` and `ExposureParser` (FR-085-13).  
  _Intent:_ `app/Actions/Insights/Helpers/Distribution.php` (accumulator) returning a readonly `DistributionSummary`, `ExposureParser.php` (stored ISO, aperture, focal, shutter and duration strings → number or null).  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=DistributionTest` (green)  
  - `php artisan test --filter=ExposureParserTest` (green)  
  - `make phpstan`

- [x] T-085-05 – `DeviceNormaliser` test first, then implementation (FR-085-12, S-085-18).  
  _Intent:_ `tests/Unit/Actions/Insights/DeviceNormaliserTest.php`: "NIKON CORPORATION"/"Nikon" → Nikon (camera); "Apple" + "iPhone 13 Pro" → mobile; "samsung" + "SM-G991B" → mobile; "Canon" + "Canon EOS R6" → model de-duplicated, camera; "OLYMPUS IMAGING CORP." → OM/Olympus camera; unknown maker → trimmed spelling, other; null make/model → Unknown. Confirm red, then `app/Enum/DeviceCategory.php` and `app/Actions/Insights/Helpers/DeviceNormaliser.php` (const maps for maker aliases, camera makers, phone makers, phone model keywords).  
  _Verification commands:_  
  - `php artisan test --filter=DeviceNormaliserTest` (red, then green)  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

### I2 – Route, request, rights

- [x] T-085-06 – Failing `InsightsRightsTest` (FR-085-04, FR-085-05, S-085-02, S-085-03, S-085-07, S-085-09, S-085-10).  
  _Intent:_ `tests/Feature_v3/Insights/InsightsRightsTest.php` (`BaseApiWithDataTest`, `RequireSE`): guest → 401; SE off → 402; user own scope → 200; user with `owner_id` of another user → 403; user with `whole_instance` → 403; admin with `owner_id` → 200; unknown `owner_id` → 422; `period=range` with from > to → 422; `period=year` without `year` → 422. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsRightsTest` (expected red)

- [x] T-085-07 – DTOs, request, controller, route (FR-085-04, FR-085-05, DO-085-01, DO-085-02, API-085-01).  
  _Intent:_ `app/DTO/Insights/InsightsScope.php`, `InsightsPeriod.php` (readonly; period computes the widened UTC window); `app/Http/Requests/Insights/GetInsightsRequest.php` (rules; `authorize()` → logged in, and administrator when `owner_id` or `whole_instance` is set); `app/Http/Controllers/InsightsController.php::index`; `routes/api_v3.php` `Route::get('/Insights', …)->middleware(['support:se'])`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=InsightsRightsTest` (green)  
  - `make phpstan`

### I3a – Aggregation: overview, storage, people, places, time span

- [x] T-085-08 – Failing `InsightsAggregateOverviewTest` (FR-085-06 … FR-085-09, FR-085-14, S-085-01, S-085-04 … S-085-06, S-085-08, S-085-11, S-085-13, S-085-16, S-085-19).  
  _Intent:_ `tests/Feature_v3/Insights/InsightsAggregateOverviewTest.php`: seeded photos for two owners with fixed `taken_at`/`taken_at_orig_tz`, `type`, `filesize` (one null), coordinates, `is_highlighted`, album membership, faces (one dismissed); assert photo/video/other split, highlighted, albums (library vs year), photos in no album, storage totals and averages with "size unknown", people counts, located count/share, first/last/busiest IDs, streaks, undated count; owner isolation, admin user scope, whole instance, empty library. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsAggregateOverviewTest` (expected red)

- [x] T-085-09 – `ComputeInsights` (overview part) and resources (FR-085-06 … FR-085-09, FR-085-14, NFR-085-05, NFR-085-06).  
  _Intent:_ `app/Actions/Insights/ComputeInsights.php` (scope filter, widened-window cursor via `DB::table('photos')->select(...)->lazyById(1000)`, local-date filter, `EXISTS` album flag); accumulator classes in `app/Actions/Insights/Accumulators/`; album and face ID streams for Year/Range, `COUNT` queries for Library; thumbnail URLs for three photo IDs; `app/Http/Resources/Insights/InsightsResource.php` + `OverviewData`, `StorageData`, `PeopleData`, `PlacesData`, `TimeSpanData` (`#[TypeScript]`).  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=InsightsAggregateOverviewTest` (green)  
  - `php artisan test --filter=InsightsRightsTest`  
  - `make phpstan`

### I3b – Aggregation: calendar, rhythm, devices, exposure

- [x] T-085-10 – Failing `InsightsAggregateChartsTest` (FR-085-10 … FR-085-13, FR-085-15, S-085-12, S-085-15, S-085-18).  
  _Intent:_ `tests/Feature_v3/Insights/InsightsAggregateChartsTest.php`: day counts (year) and week counts (library); 7 × 24 grid in local time (S-085-15 photo lands on 2025-01-01 08:00); month/weekday/hour bars; devices by device, manufacturer and lens with metric breakdowns and categories; ISO/focal/shutter/aperture/video-length distributions with summaries and excluded counts; technique cards. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsAggregateChartsTest` (expected red)

- [x] T-085-11 – Remaining accumulators and resources (FR-085-10 … FR-085-13, FR-085-15).  
  _Intent:_ calendar, rhythm, device and distribution accumulators; `CalendarData`, `RhythmData`, `DevicesData`, `ExposureData`, `DistributionData`; `php artisan typescript:transform`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=InsightsAggregateChartsTest` (green)  
  - `php artisan test --filter=InsightsAggregateOverviewTest`  
  - `make phpstan`  
  - `npm run check`

### I4 – Cache and revision

- [x] T-085-12 – Failing `InsightsCacheTest` (NFR-085-06, S-085-17).  
  _Intent:_ `tests/Feature_v3/Insights/InsightsCacheTest.php`: second request with unchanged library hits the cache (spy on `ComputeInsights` or count queries); photo edit, upload and delete produce a new revision; another owner's change does not invalidate the scope; admin scope keys differ from own scope keys. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsCacheTest` (expected red)

- [x] T-085-13 – `InsightsRevision` and cached lookup (NFR-085-06).  
  _Intent:_ `app/Actions/Insights/InsightsRevision.php` (count + max `updated_at` of photos and albums for the scope); controller wraps `ComputeInsights` in `Cache::remember(key, 3600, …)` after the request authorised the scope.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=InsightsCacheTest` (green)  
  - `php artisan test --filter=InsightsRightsTest`  
  - `make phpstan`

### I5 – Backend removal

- [x] T-085-14 – Failing `StatisticsRemovedTest`, then delete backend files (FR-085-02, FR-085-03, S-085-14).  
  _Intent:_ `tests/Feature_v3/Insights/StatisticsRemovedTest.php`: `Statistics::getCountsOverTime` and `Statistics::userSpace` no longer registered (red). **Owner approves the exact paths before deletion:** `app/Actions/Statistics/Counts.php`, `app/Enum/CountType.php`, `app/Http/Requests/Statistics/CountsRequest.php`, `app/Http/Requests/Statistics/SpacePerUserRequest.php`, `app/Http/Resources/Statistics/CountsData.php`, `app/Http/Resources/Statistics/DayCount.php`, `app/Http/Resources/Statistics/UserSpace.php`, `tests/Feature_v2/Statistics/CountsOverTimeTest.php`, `tests/Feature_v2/Statistics/UserSpaceTest.php`. Edit: the two routes in `routes/api_v2.php`, `StatisticsController::getSpacePerUser` and `getPhotoCountOverTime` (`Spaces::getFullSpacePerUser` stays: upload quota and user management use it). `php artisan typescript:transform`.  
  _Verification commands:_  
  - `php artisan test --filter=StatisticsRemovedTest` (green)  
  - `php artisan test --filter=SizeVariantSpaceTest`  
  - `php artisan test --filter=AlbumSpaceTest`  
  - `php artisan test --filter=TotalAlbumSpaceTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-085-15 – Grep for leftovers (FR-085-02).  
  _Intent:_ no reference to `CountType`, `CountsData`, `DayCount`, `UserSpace`, `getCountsOverTime`, `userSpace` remains in `app/`, `routes/`, `tests/`, `resources/js/lychee.d.ts`.  
  _Verification commands:_  
  - `grep -rn "CountType\|CountsData\|DayCount\|UserSpace\|getCountsOverTime\|userSpace" app routes tests resources/js/lychee.d.ts`

### I6 – Frontend groundwork

- [x] T-085-16 – Dependencies, ECharts registration, theme, service, route, menu, lang, view shell (FR-085-01, FR-085-04, FR-085-05, NFR-085-04, NFR-085-07, UI-085-02 … UI-085-04).  
  _Intent:_ `npm install echarts@^6.1.0 vue-echarts@^8.3.1`; `resources/js/v8/utils/insights/echarts.ts`; `resources/js/v8/composables/insights/useInsightsTheme.ts` (tokens → ECharts theme, reactive on colour mode); `resources/js/services/insights-service.ts`; `insights` in `resources/js/router/paths.ts`, `resources/js/v8/router/routes.ts` (lazy import), `routes/web_v2.php` `/insights`; v8 left-menu entries replace Statistics (SE and preview); `lang/<locale>/insights.php` for every locale (English text in other locales) + `php artisan lang:json`; `resources/js/v8/views/Insights.vue` (scope selector fed by `GET /UserManagement` for administrators, period selector with year list and range, loading and empty states).  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint <touched files>`  
  - `php artisan test --filter=LangTest`

### I7 – Sections: overview, storage, people, places, time span

- [x] T-085-17 – Section components (FR-085-06 … FR-085-09, FR-085-14, S-085-08, S-085-19).  
  _Intent:_ `resources/js/v8/components/insights/OverviewSection.vue`, `StorageSection.vue` (reuses `statistics/SizeVariantMeter.vue`, `statistics/AlbumsTable.vue`; Library period only), `PeopleSection.vue` (hidden without faces), `PlacesSection.vue` (map link when `map_display`), `TimeSpanSection.vue`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint <touched files>`

### I8 – Sections: calendar, rhythm, devices, exposure

- [x] T-085-18 – Chart sections (FR-085-10 … FR-085-13).  
  _Intent:_ option builders `resources/js/v8/utils/insights/options/{calendar,rhythm,devices,exposure}.ts` (pure functions, escaped tooltip formatters, RTL-aware axis direction); `CalendarSection.vue`, `RhythmSection.vue`, `DevicesSection.vue`, `ExposureSection.vue` using `vue-echarts` `VChart`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint <touched files>`

- [x] T-085-19 – Lazy chunk check (NFR-085-07).  
  _Intent:_ `vite build`; ECharts modules appear only in the Insights chunk.  
  _Verification commands:_  
  - `npx vite build` and inspect the manifest / chunk names

### I9 – SE preview

- [x] T-085-20 – Preview data (FR-085-01, S-085-09, UI-085-01).  
  _Intent:_ `getInsightsData()` in `resources/js/composables/preview/getPreviewInfo.ts` returning a typed `InsightsResource` sample; `Insights.vue` renders it under `insights.preview_text` when `is_se_preview_enabled`, without calling the service.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

### I10 – Frontend removal

- [x] T-085-21 – Delete the Statistics page frontend (FR-085-02).  
  _Intent:_ **Owner approves the exact paths before deletion:** `resources/js/v7/views/Statistics.vue`, `resources/js/v8/views/Statistics.vue`, `resources/js/v7/components/statistics/Activity.vue`, `resources/js/v7/components/statistics/PunchCard.vue`, `resources/js/v7/components/statistics/PunchCardCaption.vue`, `resources/js/v7/components/statistics/AlbumsTable.vue`, `resources/js/v8/components/statistics/Activity.vue`, `resources/js/v8/components/statistics/PunchCard.vue`, `resources/js/v8/components/statistics/PunchCardCaption.vue`. Edit: `statistics` entries in `resources/js/router/paths.ts`, both `routes.ts`, `routes/web_v2.php`; Statistics entries in `resources/js/composables/contextMenus/leftMenu.ts` (v7); `getCountsOverTime`/`getUserSpace` in `statistics-service.ts`; `statistics.punch_card` in every `lang/<locale>/statistics.php` + `php artisan lang:json`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `php artisan test --filter=LangTest`  
  - `grep -rn "punch_card\|views/Statistics\|PunchCard\|/statistics" resources/js routes`

### I11 – Verification and docs

- [x] T-085-22 – Full quality gate.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `npm run format`  
  - `npm run check`  
  - every test class listed above, one at a time  
  - `make phpstan`

- [x] T-085-23 – Knowledge map and roadmap.  
  _Intent:_ Insights module (action, helpers, resource, route, v8 view and ECharts chunk); Statistics page removal; roadmap row.

- [x] T-085-24 – Implementation drift gate in [plan.md](plan.md).

- [x] T-085-25 – Browser run on a scratch instance (S-085-01, S-085-08, S-085-09, S-085-14, S-085-19).  
  _Intent:_ Playwright against a scratch instance (SQLite, scratchpad storage, SE on/off): every section renders for own scope, admin user scope, whole instance; Library/Year/Range; light, dark, RTL; preview without SE; album drawer storage figures; timing of a first whole-instance load on a seeded library.

### I12 – Calendar days, image formats, focal per device

- [x] T-085-26 – Failing tests (FR-085-10, FR-085-17, FR-085-18, S-085-24, S-085-25, S-085-27).  
  _Intent:_ `InsightsAggregateChartsTest`: calendar per local day for Library; focal per device. New `tests/Feature_v3/Insights/InsightsFormatsTest.php`: orientation counts per media, aspect-ratio groups, top dimensions, unknown without original. Unit `AspectRatioGroupTest`.  
  _Verification commands:_ `php artisan test --filter=InsightsFormatsTest`, `php artisan test --filter=AspectRatioGroupTest`, `php artisan test --filter=InsightsAggregateChartsTest` (expected red)

- [x] T-085-27 – Implementation (FR-085-10, FR-085-17, FR-085-18).  
  _Intent:_ `Helpers/AspectRatioGroup.php`, `Accumulators/FormatAccumulator.php`, focal per device in `DeviceAccumulator`, day calendar, `FormatsData`/`OrientationCountData`/`AspectRatioData`/`DimensionsData`/`DeviceFocalData`; `php artisan typescript:transform`.  
  _Verification commands:_ the three test classes (green), `vendor/bin/php-cs-fixer fix`, `make phpstan`

### I13 – Timeline events

- [x] T-085-28 – Failing `InsightsTimelineTest` (FR-085-19, S-085-26).  
  _Verification commands:_ `php artisan test --filter=InsightsTimelineTest` (expected red)

- [x] T-085-29 – Timeline (FR-085-19, DO-085-03).  
  _Intent:_ `TimelineCategory`, `TimelineEventKind`, `Accumulators/TimelineAccumulator.php`, milestones and break/streak events in `ComputeInsights`, `TimelineEventData`.  
  _Verification commands:_ `php artisan test --filter=InsightsTimelineTest` (green), `vendor/bin/php-cs-fixer fix`, `make phpstan`

### I14 – Album scope

- [x] T-085-30 – Failing `InsightsAlbumScopeTest` (FR-085-16, S-085-20 … S-085-23).  
  _Verification commands:_ `php artisan test --filter=InsightsAlbumScopeTest` (expected red)

- [x] T-085-31 – Album scope (FR-085-16, DO-085-01, API-085-01).  
  _Intent:_ `GetInsightsRequest` `album_id`; `InsightsScope::album()`; scoped photo/album/face queries and revision; `id` on `App\Http\Resources\Statistics\Album`.  
  _Verification commands:_ `php artisan test --filter=InsightsAlbumScopeTest` (green), `InsightsRightsTest`, `InsightsCacheTest`, `AlbumSpaceTest`, `make phpstan`

### I15 – Frontend: calendar squares, loader, album selector

- [x] T-085-32 – Calendar squares, loader, album selector (FR-085-10, FR-085-16, FR-085-21).  
  _Verification commands:_ `npm run format`, `npm run check`, `npx eslint <touched files>`

### I16 – Frontend: formats, focal cones, timeline, space diagram

- [x] T-085-33 – Formats and focal sections (FR-085-17, FR-085-18).
- [x] T-085-34 – Timeline section (FR-085-19).
- [x] T-085-35 – Space diagram (FR-085-20).  
  _Verification commands (each):_ `npm run format`, `npm run check`, `npx eslint <touched files>`, `php artisan test --filter=LangTest`

### I17 – Verification and docs

- [x] T-085-36 – Quality gate, `vite build` chunk check, scratch-instance Playwright run (album scope, new sections, loader).
- [x] T-085-37 – Drift gate addendum, knowledge map, roadmap.

### I18 – v7 keeps the Statistics page

- [x] T-085-38 – Restore the v7 Statistics page (FR-085-02, FR-085-03, S-085-14).  
  _Intent:_ restore `Counts`, `CountType`, `CountsRequest`, `CountsData`, `DayCount`, `CountsOverTimeTest`, the v7 view and components (`Activity`, `PunchCard`, `PunchCardCaption`, `AlbumsTable`), the v7 menu entries and router entry, the `/statistics` web route and router path, `Statistics::getCountsOverTime` (route, controller method, service function) and the `statistics.punch_card` lang keys; `StatisticsRemovedTest` asserts only `userSpace` is gone.  
  _Verification commands:_ `php artisan test --filter=StatisticsRemovedTest`, `php artisan test --filter=CountsOverTimeTest`, `npm run check`, `make phpstan`

- [x] T-085-39 – Browser check: v7 Statistics page with punch card, no Insights entry; v8 unchanged.

## Notes / TODOs
- FR coverage: FR-085-01 (T-085-06/07, 16, 20), FR-085-02 (T-085-14/15, 21, 38), FR-085-03 (T-085-14, 17), FR-085-04 (T-085-06/07, 16), FR-085-05 (T-085-06/07, 16), FR-085-06 … 09 and 14 (T-085-08/09, 17), FR-085-10 … 13 (T-085-10/11, 18), FR-085-15 (T-085-01/02, 10/11).
- Deletions in T-085-14 and T-085-21 wait for the owner's approval of the listed paths in the session that runs them.
- No JS unit runner: option builders are checked through `npm run check` and the browser run.
