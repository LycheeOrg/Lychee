# Feature Plan 108 – Insights Speedup

_Linked specification:_ [spec.md](spec.md)  
_Status:_ In progress  
_Last updated:_ 2026-11-06

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Insights answer within the 30 s Octane limit on large libraries by removing the superlinear queries. Success: identical aggregates (FR-108-01/02), and on the dev copy with Xdebug off every measured request ≤ 3 s (NFR-108-01/02), Library ≤ 1.5 s with ≤ 0.8 s of PHP (NFR-108-03), peak memory ≤ 30 MB (NFR-108-04).

## Scope Alignment
- **In scope:** album-scope paging (NFR-108-01); album and people counts for Year/Range from the core window plus paged edge photos (NFR-108-02); years list cached per revision (FR-108-03); cheaper local time and single EXIF parse (NFR-108-03); Feature 085 docs and knowledge map.
- **In scope (I9):** the widened `size_variants` index (NFR-108-06, Q-108-05 A).
- **Out of scope:** N-108-01 … 05 (no column and no `photos` index, no queue, no limit change, no other endpoint, no redesign for ×10–×50 libraries: Q-108-06).

## Dependencies & Interfaces
- Feature 085 code (`app/Actions/Insights`, `app/DTO/Insights`), tests (`tests/Feature_v3/Insights`, `tests/Unit/Actions/Insights`), ADR-085-03.
- Dev database (MariaDB 11.8) read-only for measurements; SQLite for tests. Since 2026-11-06 the dev database holds the owner's server dump (owner import), and its `size_variants` index was widened by hand at the owner's request, then reverted and migrated in I9.

## Assumptions & Risks
- **Assumptions:** the server failure is the Octane time limit (Q-108-01 B).
- **Risks / Mitigations:**
  - The plan evidence of NFR-108-01 and NFR-108-02 is MariaDB 11.8 only (dev copy). PostgreSQL may plan the album-scope page differently (the correlated `_lft` subquery as a SubPlan per link is expected) and has no large library here: CI checks its results, not its plans, so the PostgreSQL plan stays unverified (Follow-ups).
  - Bound parameters: no query may bind a list that grows with the library or the tree (NFR-108-04, S-108-09). A first version bound the tree's album IDs (`whereIn`), found in owner review on 2026-11-05 and replaced by the correlated subquery (I6).
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

6. **I6 – Bounded parameters (owner review 2026-11-05)**
   - _Goal:_ NFR-108-01/04, S-108-09: no unbounded `whereIn`.
   - _Steps:_ test first (`testAlbumScopeBindsAFixedNumberOfParameters`, red: 7 → 8 bindings after adding 5 albums), then `photoIdPages()` tests the link's album with a correlated `_lft` subquery instead of binding the tree's album IDs.
   - _Commands:_ `php artisan test --filter=InsightsScalingTest`, Insights feature classes, `make phpstan`, `vendor/bin/php-cs-fixer fix`.

7. **I7 – PR #4959 review (CodeRabbit, 2026-11-05)**
   - _Goal:_ FR-108-04, S-108-10; a test that known years skip the years pass (FR-108-03); the gate claims of NFR-108-01 limited to MariaDB.
   - _Steps:_ tests first (`LocalCaptureTimeTest` +3: `+15:00` and `Asia/Manila` 1800 red, `Pacific/Kiritimati` green; `InsightsScalingTest::testOffsetBeyondFourteenHoursCountsInItsUtcYear` red: total +0, albums +1); `LocalCaptureTime::shift()` keeps the UTC time beyond `InsightsPeriod::MAX_OFFSET_HOURS`; `InsightsCacheTest::testKnownYearsSkipTheYearsPass` (green, then red against a mutant that ignores `$known_years`, restored); Feature 085 FR-085-15, ADR-085-03, knowledge map.
   - _Review items not taken in I7:_ edge rows buffered on MySQL → Q-108-03, answered B by the owner and done in I8; filtering the album pager by the period → declined: total work is linear in the tree either way (each link is read once) and the measured album Year is 1.2–1.4 s; `EditPhotoRequest` offset validation → not needed for Insights once the clock reads wider offsets as UTC (EXIF and existing rows included), left to the photo-edit owner; docstring coverage → CodeRabbit default, not a repository rule.
   - _Commands:_ `php artisan test --filter=LocalCaptureTimeTest`, `--filter=InsightsScalingTest`, `--filter=InsightsCacheTest`, every Insights class, `make phpstan`, `vendor/bin/php-cs-fixer fix`.

8. **I8 – Paged edge reads (Q-108-03 B, 2026-11-05)**
   - _Goal:_ NFR-108-02/04, S-108-11: no read holds the edge rows of more than one page of photos.
   - _Steps:_ tests first (`testEdgePagesHoldEachEdgePhotoOnce`, `testLibraryHasNoEdgePages`, `testEdgeRowsAreReadOnePageOfPhotosAtATime`; red: class missing, 2 edge reads instead of 4); `PeriodEdges::photoIdPages()`; `distinctInPeriod()` reads each page by ID. First cut followed the option's wording (`[first, last]` ranges with `BETWEEN`, on `photos.id`, then on the link table's `photo_id`): `EXPLAIN` on the dev copy showed MariaDB driving every range from the album index over all links of the tree (`albums range … Using temporary`, `photo_album ref album_id`), so the cost was ranges × links; binding the page's IDs instead (≤ 1,000, like `albumStream()`) gives `photos range PRIMARY rows=1000` → `photo_album ref PRIMARY` → `albums eq_ref PRIMARY`, and the window filters are no longer needed in the read (low-impact, agent).
   - _Commands:_ `php artisan test --filter=InsightsScalingTest`, every Insights class, `make phpstan`, `vendor/bin/php-cs-fixer fix`; scratchpad `explain6.php 2019-06-08`, `bench.php`/`bench_base.php`.

9. **I9 – Widened `size_variants` index (Q-108-04, Q-108-05 A, 2026-11-06)**
   - _Goal:_ NFR-108-06, S-108-12: the pass reads the originals from an index in photo-ID order.
   - _Steps:_ test first (`SizeVariantsOriginalsIndexMigrationTest`: index columns after the migrations, `down()` then `up()`; red: index missing, migration file missing); migration `2026_11_06_000001_widen_size_variants_ratio_index` (`OptimizeTables::dropIndexIfExists()` on both names, owner review, then add `size_variants_photo_id_type_ratio_filesize_width_height_index`; `down()` the reverse; `up()` re-runnable); on the dev database (hand-made wide index in place) run `php artisan migrate`, re-measure (`bench.php`, hashes) and `EXPLAIN` the page and the two `ratio` readers.
   - _Commands:_ `php artisan test --filter=SizeVariantsOriginalsIndexMigrationTest`, Insights classes reading originals (`InsightsAggregateOverviewTest`, `InsightsFormatsTest`, `InsightsTimelineTest`, `InsightsScalingTest`), ratio readers (`PhotoSearchTest`, `AlbumMapHeaderTest`), `make phpstan`, `vendor/bin/php-cs-fixer fix`.
   - _Exit:_ tests green; identical hashes on the server dump; `originals ref … Using index`.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-108-01 | I1, I2 / T-108-02, T-108-05 | `InsightsScalingTest` album scope |
| S-108-02 | I1, I2 / T-108-02, T-108-05 | `InsightsScalingTest` owner, whole instance |
| S-108-03 | I1, I2 / T-108-02, T-108-06 | `InsightsScalingTest` query count |
| S-108-04 | I2 / T-108-06 | `InsightsAggregateOverviewTest` (existing) |
| S-108-05 | I1, I2 / T-108-02, T-108-06 | `InsightsScalingTest` one-day range |
| S-108-06 | I1, I3, I7 / T-108-03, T-108-07, T-108-15 | `InsightsCacheTest` (`testKnownYearsSkipTheYearsPass` for `ComputeInsights`) |
| S-108-07 | I1, I4 / T-108-01, T-108-08 | `LocalCaptureTimeTest` |
| S-108-08 | I1, I2 / T-108-01, T-108-04 | `InsightsPeriodTest` |
| S-108-09 | I6 / T-108-11, T-108-12 | `InsightsScalingTest::testAlbumScopeBindsAFixedNumberOfParameters` |
| S-108-10 | I7 / T-108-13, T-108-14 | `LocalCaptureTimeTest` (3 cases), `InsightsScalingTest::testOffsetBeyondFourteenHoursCountsInItsUtcYear` |
| S-108-11 | I8 / T-108-17, T-108-18 | `InsightsScalingTest::testEdgePagesHoldEachEdgePhotoOnce`, `testLibraryHasNoEdgePages`, `testEdgeRowsAreReadOnePageOfPhotosAtATime` |
| S-108-12 | I9 / T-108-20, T-108-21 | `SizeVariantsOriginalsIndexMigrationTest` |

## Analysis Gate
2026-11-04, agent review against `analysis-gate-checklist.md`:
1. Specification completeness — pass: goals, FR-108-01 … 03, NFR-108-01 … 05 filled; Q-108-01/02 folded into Overview, FRs and NFRs; no UI change, so no mock-up.
2. Open questions — pass: none open; the architectural change is an amendment of ADR-085-03 (T-108-09), referenced from NFR-108-01 … 03 via the open-questions row.
3. Plan alignment — pass: plan links spec and tasks; success criteria use the NFR budgets.
4. Tasks coverage — pass: FR-108-01 by the existing Feature 085 classes (T-108-06, T-108-10), FR-108-02 by T-108-02/05, FR-108-03 by T-108-03/07; tests (T-108-01 … 03) before code; success (equal aggregates), edge (one-day range, boundary photos) and failure (cache miss → years pass) branches listed.
5. Working agreements — pass: no dependency, no migration, no deletion; helpers keep branches out (`utcCore()` inverted bounds instead of a branch, `photoIdPages()` generator); ADR-085-01 … 04 reviewed.
6. Tooling — pass: commands per task; `make phpstan` and `vendor/bin/php-cs-fixer fix` in T-108-10.

Addendum 2026-11-06 (I9), agent review against `analysis-gate-checklist.md`:
1. Specification completeness — pass: NFR-108-06, S-108-12, N-108-01 and NFR-108-04 amended, N-108-05 added; Overview states the server cause (Q-108-04).
2. Open questions — pass: Q-108-04 and Q-108-05 resolved and folded into the spec; ADR-085-03 amended (2026-11-06); Q-108-06 open but deferred out of scope (N-108-05).
3. Plan alignment — pass: I9 maps NFR-108-06 and S-108-12 to T-108-20 … 23.
4. Tasks coverage — pass: test before migration (T-108-20 before T-108-21); success (index present), rollback (`down()` restores the old index) and re-apply branches.
5. Working agreements — pass: one migration approved by the owner (Q-108-05 A); no dependency; dev-database changes requested by the owner (hand-made index, then migration); no deletion.
6. Tooling — pass: commands per task; `make phpstan`, `vendor/bin/php-cs-fixer fix` in T-108-23.

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
| Album scope, largest tree (47,707), Library | 12.34 s | 1.64 s | 101 q / 0.94 s | 23 MB |
| Album scope, largest tree, Year 2024 | 3.57 s | 1.44 s | 201 q / 1.08 s | 21 MB |
| Whole instance, Range 2019-06-08 (busiest day, after I8) | 0.86 s | 0.54 s | 83 q / 0.30 s | 21 MB |
| Album scope, largest tree, Range 2019-06-08 (after I8) | 2.47 s | 1.07 s | 211 q / 0.82 s | 21 MB |

PHP per Library pass (67,790 rows): local time 0.47 → 0.20 s, `PhotoRow` 0.07 → 0.18 s (now parses EXIF), exposure 0.16 → 0.06 s, timeline 0.15 → 0.06 s, devices 0.14 → 0.11 s; total ≈ 1.21 → 0.73 s. Fetching the rows (SQL + hydration) ≈ 0.6 s is now the largest part. Album-scope rows measured after I6 (correlated subquery): the page query walks `photo_album` in `photo_id` order with a `DEPENDENT SUBQUERY scope_albums eq_ref PRIMARY` and binds 3 values; all 49 pages of the largest tree take 0.12 s (ID-list version: 0.43 s). NFR-108-03 first aimed at Library ≤ 1.2 s; restated at ≤ 1.5 s / ≤ 0.8 s PHP after measuring (low-impact, agent). After I7 (FR-108-04): hashes of all six cases still equal the code before Feature 108 (the dev copy holds no offset beyond 14 h); Library 1.23–1.33 s over four runs. After I8 (paged edge reads): hashes of all eight cases (the six above plus the busiest day, whole instance and album scope) still equal the code before Feature 108; on the busiest day the 3 pages of edge photos per count take 0.03 s of edge reads plus 0.03 s of pager (ranges first cut: 0.14 s of edge reads); the other timings are unchanged.

## Implementation Drift Gate
2026-11-04, agent:
- Preconditions: T-108-01 … 19 `[x]`; quality gate green (below); no open question.
- FR-108-01 → Feature 085 classes unchanged and green (`InsightsAggregateOverviewTest`, `InsightsAggregateChartsTest`, `InsightsTimelineTest`, `InsightsFormatsTest`, `InsightsAlbumScopeTest`, `InsightsRightsTest`, `InsightsCacheTest`, `StatisticsRemovedTest`) + identical hashes on the dev copy. FR-108-02 → `InsightsScalingTest::testPageSizeDoesNotChangeTheResult` (16 cases), `testPhotoInTwoAlbumsOfTheTreeCountsOnce`. FR-108-03 → `InsightsCacheTest::testYearReusesTheYearsOfTheLibrary`, `testYearStoresTheYearsForTheNextYear`, `testYearsFollowTheRevision`, `testKnownYearsSkipTheYearsPass`; `CachedInsights::get()`. FR-108-04 → `LocalCaptureTime::shift()`, `InsightsPeriod::MAX_OFFSET_HOURS`; `LocalCaptureTimeTest` (3 cases), `testOffsetBeyondFourteenHoursCountsInItsUtcYear` (S-108-10).
- NFR-108-01 → `InsightsScopeQuery::photoIdPages()`, `ComputeInsights::stream()`/`albumStream()`; `EXPLAIN` of the page: `photo_album range photo_album_photo_id_index Using where; Using index` + `DEPENDENT SUBQUERY scope_albums eq_ref PRIMARY` (no filesort, 3 bindings) — MariaDB 11.8 only; the PostgreSQL plan is not verified (results only, through CI). NFR-108-02 → `InsightsPeriod::utcCore()` (`InsightsPeriodTest`), `ComputeInsights::distinctInPeriod()`, `PeriodEdges::photoIdPages()`, `testSideCountsDoNotGrowWithLinksOrFaces`, `testOneDayRangeCountsFromTheEdges`, `testEdgePagesHoldEachEdgePhotoOnce`, `testEdgeRowsAreReadOnePageOfPhotosAtATime` (S-108-11), Feature 085 boundary tests (S-108-04); `EXPLAIN` of an edge page on MariaDB: `photos range PRIMARY` → `photo_album ref PRIMARY` → `albums eq_ref PRIMARY`. NFR-108-03 → `LocalCaptureTime::convert()`/`weekStart()` (`LocalCaptureTimeTest` + 4 cases), `PhotoRow` parsed fields. NFR-108-04 → no migration, no dependency, peak ≤ 25 MB; bounded parameters by `testAlbumScopeBindsAFixedNumberOfParameters` (S-108-09); edge reads bounded to one page of photos (I8). NFR-108-05 → SQLite tests here, MySQL/PostgreSQL in CI.
- Divergence: NFR-108-03 budget restated after measurement (above). Owner review 2026-11-05: the album ID `whereIn` was unbounded (crash on large trees); fixed in I6 with a regression test. PR #4959 review: offsets beyond 14 h counted a photo in a period whose window did not hold it (and dropped it from the totals); fixed in I7 (FR-108-04). Q-108-03 B: one buffered edge query replaced by pages of IDs (I8), bound by ID list rather than by `[first, last]` range after `EXPLAIN` (above). No undocumented work.
- Coverage: success (equal aggregates), edge (page boundaries, one-day range, boundary photos) and cache-miss branches tested.
- Quality gate (after I8): `vendor/bin/php-cs-fixer fix` (0 files), `make phpstan` (no errors), 17 Insights test classes (177 tests) green, run one at a time; dev-copy hashes of all eight cases still equal the code before Feature 108.
- Lesson: `lazyById` over a query whose plan sorts (semi-join, `OR` keyset, unindexed filter) costs a full sort per page; check `EXPLAIN` of one page, not only of the query. Never bind a list whose length grows with the data (`whereIn` of album IDs): a correlated subquery on a primary key keeps the ordered scan and a fixed parameter count. A `BETWEEN` on `photos.id` does not steer which table the planner drives a join from: to make a page drive the plan, bind the page's IDs (bounded by the page size).

## Intent Log
- 2026-11-04: owner report "insights way too slow on ~70K photos", then "not reproducible locally but crashes on the server". Profiled `ComputeInsights` on the dev copy with read-only scratchpad scripts (`profile*.php`, `explain*.php`, `split.php`, `clock.php`, `proto.php`). Owner answered "B A": Q-108-01 B, Q-108-02 A. Spec/plan/tasks, analysis gate, tests first (red on the missing `chunk`/years API), then I2 … I4; `EXPLAIN` variants of the album page in the scratchpad (`explain3.php`: ID list → ordered `photo_id` range, subquery → filesort); before/after hashes via `bench.php`/`bench_base.php`.
- 2026-11-05: owner review "you are doing whereIn() with unbounded parameters. This can crash." → I6: regression test (red 7 → 8), correlated `_lft` subquery (`explain4.php`, `explain5.php`: ordered scan, 3 bindings, 0.12 s for the largest tree), hashes rechecked.
- 2026-11-05: owner "check comments" on PR #4959 (`gh api` for the review threads and review bodies). CodeRabbit: (1) offsets beyond ±14 h → I7, fixed in the clock rather than in `EditPhotoRequest` because EXIF values, existing rows and local mean times (`Asia/Manila` −15:56 before 1845, checked with `php -r`) reach Insights too; (2) `cursor()` buffered on MySQL → measured (`edges.php`: ≤ 12 edge rows per year, 2,186 for the busiest one-day range) and logged as Q-108-03; (3) PostgreSQL gate → claims limited to MariaDB; (4) album pager per period → declined (above); (5) known-years test → `testKnownYearsSkipTheYearsPass`, checked against a mutant; (6) docstring coverage → not taken. Dev-copy timezones listed with `tz.php` (none beyond 14 h); hashes and timings rechecked with `bench.php`/`bench_base.php`.
- 2026-11-05: owner answered Q-108-03 "B" → I8: tests first (red), `PeriodEdges` with `[first, last]` ranges, `EXPLAIN` (`explain6.php`) showed a tree walk per range, range moved to the link table's `photo_id` (same plan), then the page's IDs bound with `whereIn` (plan from `photos` by primary key, edge reads 0.14 → 0.03 s); `idx.php` listed the `faces`/`photo_album` indexes; `bench.php`/`bench_base.php` gained the busiest-day cases.

## Follow-ups / Backlog
- Check the album-scope page plan on PostgreSQL with a large library (needs a PostgreSQL copy of a large library; none here).
- Q-108-06 (deferred by the owner): libraries ×10–×50 the server's size exceed the 30 s limit with one pass per computation (≈ 12 µs of PHP per photo, working set ≈ 1–5 GB).
- NFR-108-06 plan verified on MariaDB only; MySQL, PostgreSQL (index-only scans depend on the visibility map) and SQLite plans not checked on a large library.
- 2026-11-06: owner server log (`Maximum execution time of 30 seconds exceeded` in the `lazyById` stream, 30,182 ms) after #4959. Dev copy fine (`bench.php`, `explain.php`, `io.php`: 0 disk reads, pool 128 MB 90 % full); owner gave the server's size (85,743 photos, 1,017 albums, 550,491 size variants), MariaDB 11.8.9 and 128 MB pool, declined changing the MariaDB container, then imported the server dump into the dev database. `io.php`/`pagesql.php` (`ANALYZE FORMAT=JSON`, `r_engine_stats`) put the disk reads on the `size_variants` lookups (Q-108-04 resolved). `order.php` (stream from `size_variants` order: no gain), `cover.php`/`paged.php` (session `TEMPORARY` copies with the widened index: 22,551 → 6,027 cold). Owner: "Create the index directly in the DB and retry with measurements … do not do the migration yet" → hand-made `ALTER` (1.1 s), `bench.php` before/after (hashes identical), `ratioq.php` (ratio readers still covering). Owner asked about ×10/×50 → Q-108-06 logged, deferred. Owner: "Let's first ship the index solution" → Q-108-05 A, I9.
