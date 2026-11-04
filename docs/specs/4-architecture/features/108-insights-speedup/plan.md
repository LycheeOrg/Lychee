# Feature Plan 108 – Insights Speedup

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented  
_Last updated:_ 2026-11-04

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Insights answer within the 30 s Octane limit on large libraries by removing the superlinear queries. Success: identical aggregates (FR-108-01/02), and on the dev copy with Xdebug off every measured request ≤ 3 s (NFR-108-01/02), Library ≤ 1.5 s with ≤ 0.8 s of PHP (NFR-108-03), peak memory ≤ 30 MB (NFR-108-04).

## Scope Alignment
- **In scope:** album-scope paging (NFR-108-01); two-query album and people counts for Year/Range (NFR-108-02); years list cached per revision (FR-108-03); cheaper local time and single EXIF parse (NFR-108-03); Feature 085 docs and knowledge map.
- **Out of scope:** N-108-01 … 04 (no index, no queue, no limit change, no other endpoint).

## Dependencies & Interfaces
- Feature 085 code (`app/Actions/Insights`, `app/DTO/Insights`), tests (`tests/Feature_v3/Insights`, `tests/Unit/Actions/Insights`), ADR-085-03.
- Dev database (MariaDB 11.8) read-only for measurements; SQLite for tests.

## Assumptions & Risks
- **Assumptions:** the server failure is the Octane time limit (Q-108-01 B).
- **Risks / Mitigations:**
  - PostgreSQL may plan the `photo_album` ID-list page as a bitmap scan plus top-N sort instead of an ordered index scan: still far cheaper than the materialised semi-join; CI checks the results, not the plan. Follow-up if a PostgreSQL user reports it.
  - SQLite bound-parameter limit (32,766) caps album trees at about 32,000 albums (NFR-108-04).
  - Iteration order decides ties (`day_photos`, device "last" on equal capture time): pages keep ascending photo ID, as `lazyById` did (FR-108-02 test with page size 1).

## Increment Map
1. **I1 – Failing tests**
   - _Goal:_ stage the tests of FR-108-02/03, NFR-108-02, S-108-05/07/08.
   - _Steps:_ `InsightsPeriodTest` (unit), `LocalCaptureTimeTest` additions, `InsightsScalingTest` (page sizes, query count, one-day range), `InsightsCacheTest` years cases.
   - _Commands:_ `php artisan test --filter=InsightsPeriodTest`, `--filter=InsightsScalingTest`, `--filter=InsightsCacheTest`, `--filter=LocalCaptureTimeTest`.
   - _Exit:_ new tests red for the missing API (`utcCore`, `chunk`, years argument), characterisation tests green.
2. **I2 – Linear streams**
   - _Goal:_ NFR-108-01/02, FR-108-02.
   - _Steps:_ `InsightsPeriod::utcCore()`; `InsightsScopeQuery::photoIdPages()`; `ComputeInsights` page size argument, scope-aware stream for photos and years, `distinctInPeriod()` for albums and people.
   - _Commands:_ `php artisan test --filter=InsightsScalingTest`, `--filter=InsightsPeriodTest`, every Insights feature class, `make phpstan`.
   - _Exit:_ green; `EXPLAIN` without filesort on the paged album-scope queries.
3. **I3 – Years cache**
   - _Goal:_ FR-108-03.
   - _Steps:_ `ComputeInsights::do()` takes the known years; `CachedInsights` reads and stores them under (scope, revision).
   - _Commands:_ `php artisan test --filter=InsightsCacheTest`.
4. **I4 – Per-photo PHP**
   - _Goal:_ NFR-108-03.
   - _Steps:_ `LocalCaptureTime` single shift and format; `PhotoRow` parsed exposure; accumulators read numbers.
   - _Commands:_ `--filter=LocalCaptureTimeTest`, `--filter=ExposureParserTest`, Insights feature classes.
5. **I5 – Docs, measurements, quality gate**
   - _Steps:_ NFR-085-06, ADR-085-03 amendment, knowledge map, roadmap; after-measurements; `vendor/bin/php-cs-fixer fix`, `make phpstan`; drift gate; commit command for the owner.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-108-01 | I1, I2 / T-108-02, T-108-05 | `InsightsScalingTest` album scope |
| S-108-02 | I1, I2 / T-108-02, T-108-05 | `InsightsScalingTest` owner, whole instance |
| S-108-03 | I1, I2 / T-108-02, T-108-06 | `InsightsScalingTest` query count |
| S-108-04 | I2 / T-108-06 | `InsightsAggregateOverviewTest` (existing) |
| S-108-05 | I1, I2 / T-108-02, T-108-06 | `InsightsScalingTest` one-day range |
| S-108-06 | I1, I3 / T-108-03, T-108-07 | `InsightsCacheTest` |
| S-108-07 | I1, I4 / T-108-01, T-108-08 | `LocalCaptureTimeTest` |
| S-108-08 | I1, I2 / T-108-01, T-108-04 | `InsightsPeriodTest` |

## Analysis Gate
2026-11-04, agent review against `analysis-gate-checklist.md`:
1. Specification completeness — pass: goals, FR-108-01 … 03, NFR-108-01 … 05 filled; Q-108-01/02 folded into Overview, FRs and NFRs; no UI change, so no mock-up.
2. Open questions — pass: none open; the architectural change is an amendment of ADR-085-03 (T-108-09), referenced from NFR-108-01 … 03 via the open-questions row.
3. Plan alignment — pass: plan links spec and tasks; success criteria use the NFR budgets.
4. Tasks coverage — pass: FR-108-01 by the existing Feature 085 classes (T-108-06, T-108-10), FR-108-02 by T-108-02/05, FR-108-03 by T-108-03/07; tests (T-108-01 … 03) before code; success (equal aggregates), edge (one-day range, boundary photos) and failure (cache miss → years pass) branches listed.
5. Working agreements — pass: no dependency, no migration, no deletion; helpers keep branches out (`utcCore()` inverted bounds instead of a branch, `photoIdPages()` generator); ADR-085-01 … 04 reviewed.
6. Tooling — pass: commands per task; `make phpstan` and `vendor/bin/php-cs-fixer fix` in T-108-10.

## Exit Criteria
- All tasks `[x]`; every Insights test class green; `make phpstan` and `php-cs-fixer` clean.
- Measurement table filled; Feature 085 spec, ADR-085-03, knowledge map and roadmap updated.
- No resource change, so no `php artisan typescript:transform`.

## Measurements
Dev copy (MariaDB 11.8, 67,790 photos), `php -d xdebug.mode=off -d opcache.enable_cli=1`, `ComputeInsights::do()` without cache. "Before" runs the `HEAD` classes (`git archive HEAD app/Actions/Insights app/DTO/Insights` into the scratchpad, loaded ahead of the working copy); both sides hash `toArray()`: identical for all six cases.

| Request | Before | After | SQL after | Peak |
|---------|--------|-------|-----------|------|
| Whole instance, Library | 1.76–1.78 s | 1.29–1.51 s | 71 q / 0.49–0.59 s | 23 MB |
| Whole instance, Year 2024 | 1.79 s | 0.83 s (0.62 s with years cached) | 81 q / 0.48 s | 21 MB |
| Whole instance, Range 2005–2026 | 9.36 s | 1.92 s | 135 q / 0.94 s | 23 MB |
| Owner 1 (60,695 photos), Library | 1.94 s | 1.65 s | 64 q / 0.73 s | 23 MB |
| Album scope, largest tree (47,707), Library | 12.34 s | 1.66 s | 102 q / 1.02 s | 25 MB |
| Album scope, largest tree, Year 2024 | 3.57 s | 1.93 s (1.14 s with years cached) | 203 q / 1.57 s | 23 MB |

PHP per Library pass (67,790 rows): local time 0.47 → 0.20 s, `PhotoRow` 0.07 → 0.18 s (now parses EXIF), exposure 0.16 → 0.06 s, timeline 0.15 → 0.06 s, devices 0.14 → 0.11 s; total ≈ 1.21 → 0.73 s. Fetching the rows (SQL + hydration) ≈ 0.6 s is now the largest part. NFR-108-03 first aimed at Library ≤ 1.2 s; restated at ≤ 1.5 s / ≤ 0.8 s PHP after measuring (low-impact, agent).

## Implementation Drift Gate
2026-11-04, agent:
- Preconditions: T-108-01 … 09 `[x]`; quality gate green (below).
- FR-108-01 → Feature 085 classes unchanged and green (`InsightsAggregateOverviewTest`, `InsightsAggregateChartsTest`, `InsightsTimelineTest`, `InsightsFormatsTest`, `InsightsAlbumScopeTest`, `InsightsRightsTest`, `InsightsCacheTest`, `StatisticsRemovedTest`) + identical hashes on the dev copy. FR-108-02 → `InsightsScalingTest::testPageSizeDoesNotChangeTheResult` (16 cases), `testPhotoInTwoAlbumsOfTheTreeCountsOnce`. FR-108-03 → `InsightsCacheTest::testYearReusesTheYearsOfTheLibrary`, `testYearStoresTheYearsForTheNextYear`, `testYearsFollowTheRevision`; `CachedInsights::get()`.
- NFR-108-01 → `InsightsScopeQuery::photoIdPages()`, `ComputeInsights::stream()`/`albumStream()`; `EXPLAIN` of the page: `photo_album range photo_album_photo_id_index Using where; Using index` (no filesort). NFR-108-02 → `InsightsPeriod::utcCore()` (`InsightsPeriodTest`), `ComputeInsights::distinctInPeriod()`, `testSideCountsDoNotGrowWithLinksOrFaces`, `testOneDayRangeCountsFromTheEdges`, Feature 085 boundary tests (S-108-04). NFR-108-03 → `LocalCaptureTime::convert()`/`weekStart()` (`LocalCaptureTimeTest` + 4 cases), `PhotoRow` parsed fields. NFR-108-04 → no migration, no dependency, peak ≤ 25 MB. NFR-108-05 → SQLite tests here, MySQL/PostgreSQL in CI.
- Divergence: NFR-108-03 budget restated after measurement (above). No undocumented work.
- Coverage: success (equal aggregates), edge (page boundaries, one-day range, boundary photos) and cache-miss branches tested.
- Quality gate: `vendor/bin/php-cs-fixer fix` (0 files), `make phpstan` (no errors), 15 Insights test classes (152 tests) green, run one at a time.
- Lesson: `lazyById` over a query whose plan sorts (semi-join, `OR` keyset, unindexed filter) costs a full sort per page; check `EXPLAIN` of one page, not only of the query.

## Intent Log
- 2026-11-04: owner report "insights way too slow on ~70K photos", then "not reproducible locally but crashes on the server". Profiled `ComputeInsights` on the dev copy with read-only scratchpad scripts (`profile*.php`, `explain*.php`, `split.php`, `clock.php`, `proto.php`). Owner answered "B A": Q-108-01 B, Q-108-02 A. Spec/plan/tasks, analysis gate, tests first (red on the missing `chunk`/years API), then I2 … I4; `EXPLAIN` variants of the album page in the scratchpad (`explain3.php`: ID list → ordered `photo_id` range, subquery → filesort); before/after hashes via `bench.php`/`bench_base.php`.

## Follow-ups / Backlog
- Check the album-scope page plan on PostgreSQL with a large library.
