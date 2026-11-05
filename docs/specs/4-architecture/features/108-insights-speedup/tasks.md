# Feature 108 Tasks – Insights Speedup

_Status: Done_  
_Last updated: 2026-11-05_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-108-01 – Unit tests for `utcCore()` and local time (S-108-07, S-108-08).  
  _Intent:_ `tests/Unit/DTO/InsightsPeriodTest.php` (red: method missing); `LocalCaptureTimeTest` cases for a week before 1970 and a +14:00 shift (green, characterisation).  
  _Verification commands:_  
  - `php artisan test --filter=InsightsPeriodTest`  
  - `php artisan test --filter=LocalCaptureTimeTest`

- [x] T-108-02 – `InsightsScalingTest` (FR-108-02, NFR-108-02, S-108-01, S-108-02, S-108-03, S-108-05).  
  _Intent:_ page size 1 vs 1,000 for owner, whole instance and album scope over Library, Year 2025, Range; query count of a Year computation unchanged after linking photos to more albums and adding faces; one-day range counts. Red: `ComputeInsights` has no page size argument.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsScalingTest`

- [x] T-108-03 – `InsightsCacheTest` years cases (FR-108-03, S-108-06).  
  _Intent:_ Year after Library receives the known years; after adding a photo it receives none. Red: no years argument.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsCacheTest`

- [x] T-108-04 – `InsightsPeriod::utcCore()` (NFR-108-02, S-108-08).  
  _Intent:_ shared bounds helper for window and core.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsPeriodTest`

- [x] T-108-05 – Album-scope paging (NFR-108-01, S-108-01, S-108-02).  
  _Intent:_ `InsightsScopeQuery::photoIdPages()`; `ComputeInsights` page size argument and scope-aware stream used by the photo pass and the years pass.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsScalingTest`  
  - `php artisan test --filter=InsightsAlbumScopeTest`

- [x] T-108-06 – Two-query album and people counts (NFR-108-02, S-108-03, S-108-04, S-108-05).  
  _Intent:_ `ComputeInsights::distinctInPeriod()` for `albums()` and `people()`.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsScalingTest`  
  - `php artisan test --filter=InsightsAggregateOverviewTest`

- [x] T-108-07 – Years cache (FR-108-03, S-108-06).  
  _Intent:_ `ComputeInsights::do()` known-years argument; `CachedInsights` stores and reads `insights-years:<scope>:<revision>`.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsCacheTest`

- [x] T-108-08 – Per-photo PHP (NFR-108-03, S-108-07).  
  _Intent:_ `LocalCaptureTime` single shift/format and day arithmetic; `PhotoRow` parsed exposure numbers; accumulators read them.  
  _Verification commands:_  
  - `php artisan test --filter=LocalCaptureTimeTest`  
  - `php artisan test --filter=ExposureParserTest`  
  - every Insights feature class

- [x] T-108-09 – Docs and measurements.  
  _Intent:_ NFR-085-06, ADR-085-03 amendment, knowledge map, roadmap; after-measurements in plan.  
  _Verification commands:_ scratchpad measurement scripts on the dev copy (read-only).

- [x] T-108-10 – Quality gate and drift gate.  
  _Intent:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`, all Insights test classes, drift gate in plan, commit command for the owner.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-108-11 – Bounded-parameters test (NFR-108-04, S-108-09).  
  _Intent:_ `InsightsScalingTest::testAlbumScopeBindsAFixedNumberOfParameters`: adding 5 albums to the tree leaves the largest binding count of every query unchanged. Red before T-108-12 (7 → 8).  
  _Verification commands:_  
  - `php artisan test --filter=InsightsScalingTest`

- [x] T-108-12 – Album-scope pager without an album ID list (NFR-108-01, NFR-108-04, S-108-09).  
  _Intent:_ `InsightsScopeQuery::photoIdPages()` tests the link's album with a correlated `_lft` subquery (builder-compiled, 3 bindings); docs (spec, plan, ADR-085-03 amendment, knowledge map, roadmap).  
  _Verification commands:_  
  - `php artisan test --filter=InsightsScalingTest`  
  - Insights feature classes, `make phpstan`, `vendor/bin/php-cs-fixer fix`

- [x] T-108-13 – Tests for offsets beyond 14 h (FR-108-04, S-108-10).  
  _Intent:_ `LocalCaptureTimeTest`: `+15:00` and `Asia/Manila` in 1800 keep the stored time (red), `Pacific/Kiritimati` (+14:00) is shifted (green); `InsightsScalingTest::testOffsetBeyondFourteenHoursCountsInItsUtcYear`: a `+15:00` photo at local 2026-01-01 00:30 adds one to the 2025 total and album count (red: total +0, albums +1).  
  _Verification commands:_  
  - `php artisan test --filter=LocalCaptureTimeTest`  
  - `php artisan test --filter=InsightsScalingTest`

- [x] T-108-14 – Clock reads offsets beyond 14 h as UTC (FR-108-04, S-108-10).  
  _Intent:_ `LocalCaptureTime::shift()` keeps the UTC time when the zone's offset at that instant exceeds `InsightsPeriod::MAX_OFFSET_HOURS` (now public, shared with the windows).  
  _Verification commands:_  
  - `php artisan test --filter=LocalCaptureTimeTest`  
  - `php artisan test --filter=InsightsScalingTest`, every Insights class, `make phpstan`

- [x] T-108-15 – Known years skip the years pass (FR-108-03, S-108-06).  
  _Intent:_ `InsightsCacheTest::testKnownYearsSkipTheYearsPass`: `do()` with known years returns them as given and runs one query fewer. Green on the current code; red against a mutant that ignores `$known_years` (restored).  
  _Verification commands:_  
  - `php artisan test --filter=InsightsCacheTest`

- [x] T-108-16 – PR #4959 review docs.  
  _Intent:_ plan (I7, gate claims of NFR-108-01 limited to MariaDB, declined review items), Q-108-03 logged, Feature 085 FR-085-15, ADR-085-03 amendment, knowledge map, roadmap; `distinctInPeriod()` docblock no longer claims memory grows with the distinct values only.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-108-17 – Tests for paged edge reads (NFR-108-02, NFR-108-04, S-108-11).  
  _Intent:_ `InsightsScalingTest::testEdgePagesHoldEachEdgePhotoOnce` (page sizes 1 and 2), `testLibraryHasNoEdgePages`, `testEdgeRowsAreReadOnePageOfPhotosAtATime` (page size 1: one edge read per edge photo and count). Red: class missing, 2 edge reads instead of 4.  
  _Verification commands:_  
  - `php artisan test --filter=InsightsScalingTest`

- [x] T-108-18 – Edge photos by pages of IDs (NFR-108-02, NFR-108-04, S-108-11; Q-108-03 B).  
  _Intent:_ `PeriodEdges::photoIdPages()` (keyset over `photos.id` with the edge filter, five bindings); `ComputeInsights::distinctInPeriod()` reads each page with `photos.id IN` the page instead of one `cursor()` over the edge strips. A first cut with `[first, last]` ranges was dropped after `EXPLAIN` (MariaDB walked every link of the tree per range).  
  _Verification commands:_  
  - `php artisan test --filter=InsightsScalingTest`, every Insights class, `make phpstan`, `vendor/bin/php-cs-fixer fix`  
  - scratchpad `explain6.php 2019-06-08`, `bench.php`/`bench_base.php` (8 cases)

- [x] T-108-19 – Q-108-03 docs and measurements.  
  _Intent:_ Q-108-03 resolved, spec NFR-108-02/04 and S-108-11, ADR-085-03 amendment, knowledge map, roadmap, plan (I8, drift gate, measurements, intent log).  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

## Notes / TODOs
- Insights test classes: `InsightsAggregateChartsTest`, `InsightsAggregateOverviewTest`, `InsightsAlbumScopeTest`, `InsightsCacheTest`, `InsightsFormatsTest`, `InsightsRightsTest`, `InsightsTimelineTest`, `StatisticsRemovedTest`, `InsightsScalingTest`; unit: `DeviceNormaliserTest`, `LocalCaptureTimeTest`, `DistributionTest`, `StreakCalculatorTest`, `ExposureParserTest`, `AspectRatioClassifierTest`, `MilestoneFinderTest`, `InsightsPeriodTest`.
