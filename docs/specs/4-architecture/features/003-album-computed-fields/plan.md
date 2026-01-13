# Feature Plan 003 – Album Computed Fields Pre-computation

_Linked specification:_ `docs/specs/4-architecture/features/003-album-computed-fields/spec.md`
_Status:_ In Progress
_Last updated:_ 2026-01-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:** Eliminate runtime album virtual column calculations that currently execute expensive subqueries on every album fetch. Users will experience significantly faster album list and gallery view performance, especially for large collections and deeply nested album hierarchies.

**Measurable Success Signals:**
- Album list query time reduced by ≥50% for large albums (measured via benchmarking before/after)
- Recomputation jobs complete within 5 seconds for albums with <1000 photos, <100 children (NFR-003-01)
- Propagation to root completes within 60 seconds for 20+ level nesting (NFR-003-02)
- Eventual consistency achieved within 30 seconds of mutation (NFR-003-04)
- Zero private photo leakage via cover selection (NFR-003-05, verified via security tests)
- All 20 scenarios (S-003-01 through S-003-20) pass with identical behavior to current virtual columns

**Quality Bars:**
- Full test coverage for all scenarios in Branch & Scenario Matrix
- Dual-cover privilege separation prevents photo leakage (security tests mandatory)
- Migration reversible via `down()` method (FR-003-06, Q-003-08)
- Backfill command idempotent and resumable
- Telemetry captures all recomputation events, propagation paths, and failures

## Scope Alignment

**In scope:**
- Database schema changes: add 6 computed columns to `albums` table (FR-003-01)
- Event-driven recomputation system: `RecomputeAlbumStatsJob` with propagation (FR-003-02, FR-003-03)
- Dual automatic cover ID selection with privilege-based logic (FR-003-04, FR-003-07)
- NSFW context handling for cover selection (S-003-14, S-003-15, S-003-16)
- Album ownership checking for cover display (nested set query for ancestor ownership)
- AlbumBuilder virtual column removal (FR-003-05)
- Backfill command `lychee:recompute-album-fields` (FR-003-06)
- Manual recovery command `lychee:recompute-album-stats` (CLI-003-02)
- Migration rollback strategy (Q-003-08 resolution)
- Test coverage for all 18 scenarios plus security/performance tests
- Documentation updates (knowledge-map, ADR-0003, roadmap)

**Out of scope:**
- User-facing cover selection API changes (manual `cover_id` setting unchanged)
- Real-time updates (eventual consistency acceptable per NFR-003-04)
- Retroactive backfill for soft-deleted albums (only active albums)
- Query plan optimization beyond pre-computation (indexes, etc. handled separately)
- Changes to photo/album mutation APIs (existing events used as triggers)

## Dependencies & Interfaces

**Module Dependencies:**
- Album model: `app/Models/Album.php` (add 6 new properties, casts)
- AlbumBuilder: `app/Models/Builders/AlbumBuilder.php` (remove virtual column methods)
- HasAlbumThumb relation: `app/Relations/HasAlbumThumb.php` (update cover display logic)
- Nested set queries: album tree structure (`_lft`, `_rgt` columns)
- PhotoQueryPolicy, AlbumQueryPolicy: access control filters for least-privilege cover
- BaseAlbumImpl: `owner_id`, `is_nsfw` fields

**External Specs:**
- Feature 002 (Worker Mode): complements async job processing (referenced in NFR-003-02)
- ADR-0003: architectural decisions for pre-computation strategy (already created per spec)

**Telemetry Contracts:**
- Migration execution events (TE-003-01)
- Album stats recomputation events (TE-003-02)
- Backfill progress events (TE-003-03)

**Fixtures:**
- FX-003-01: Deeply nested album tree (5+ levels) for propagation testing
- FX-003-02: Large album (100+ photos) for performance testing
- Existing test fixtures must work identically post-migration (regression requirement)

**Tooling:**
- Laravel migrations, queue system, job middleware (`WithoutOverlapping`)
- PHPStan (level 6+), php-cs-fixer, PHPUnit
- New test suite: `tests/Precomputing/` (added to phpunit.ci.xml and phpunit.pgsql.xml)
- Benchmark harness for performance measurement

## Assumptions & Risks

**Assumptions:**
- Laravel queue system is configured and workers are running (or will be for production)
- Database supports `ON DELETE SET NULL` foreign key constraints
- Nested set model (`_lft`, `_rgt`) is correctly maintained by existing album operations
- PhotoQueryPolicy and AlbumQueryPolicy correctly implement current access control logic
- Operator will manually run backfill command during maintenance window (not automatic)

**Risks / Mitigations:**

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Job propagation failure leaves stale data | High | `WithoutOverlapping` middleware prevents concurrent updates. Manual recovery command `lychee:recompute-album-stats` available. Stop propagation after 3 retries to prevent cascade failures. |
| Backfill timeout on large installations | Medium | Process in chunks (configurable batch size). Make idempotent/resumable. Test on staging clone with 100k+ albums first. |
| Cover selection leaks private photos | Critical | Dual-cover strategy (least-privilege vs max-privilege). Comprehensive security test suite (S-003-13 through S-003-18). Audit both cover queries against existing policies. |
| Migration breaks existing functionality | High | Phased rollout with dual-read fallback (Phase 2). Full test suite must pass. Reversible migration via `down()`. |
| NSFW boundary handling incorrect | Medium | Explicit NSFW context rules documented in spec. Test scenarios S-003-14, S-003-15, S-003-16 verify correct behavior. |
| Performance regression from job overhead | Low | Job processing is async (doesn't block user operations). Benchmark to verify read-time performance gain outweighs write-time overhead. |

## Implementation Drift Gate

Execute drift gate checklist from [docs/specs/5-operations/analysis-gate-checklist.md](docs/specs/5-operations/analysis-gate-checklist.md) after all increments complete, before declaring feature done.

**Evidence Required:**
- Spec/plan/tasks alignment verified (no drift between documents)
- All FR/NFR requirements mapped to tasks
- All 18 scenarios mapped to test cases
- Security test coverage documented
- Performance benchmark results recorded (before/after comparison)

**Recording Results:**
- Update this plan's "Analysis Gate" section with findings
- Document any deferred follow-ups in roadmap or feature backlog

## Increment Map

### I1 – Database Migration (FR-003-01, Q-003-08)
**Goal:** Add 6 computed columns to `albums` table with foreign key constraints, ensure migration is reversible.

**Preconditions:** None (fresh schema change)

**Steps:**
1. Create migration file `YYYY_MM_DD_HHMMSS_add_computed_fields_to_albums.php`
2. In `up()`: add 6 columns (`max_taken_at`, `min_taken_at`, `num_children`, `num_photos`, `auto_cover_id_max_privilege`, `auto_cover_id_least_privilege`) with correct types/nullability/defaults
3. Add foreign key constraints for both cover ID columns (`ON DELETE SET NULL`)
4. In `down()`: drop foreign keys, then drop all 6 columns (reverse order)
5. Update Album model: add properties, casts (datetime for dates, integer for counts, string for cover IDs)
6. Write unit test verifying migration up/down idempotency

**Commands:**
- `php artisan migrate` (run migration)
- `php artisan migrate:rollback` (test rollback)
- `make phpstan` (verify no type errors)
- `php artisan test --filter=AlbumMigrationTest` (unit test)

**Exit:** Migration runs cleanly, rollback works, Album model recognizes new columns, PHPStan passes.

**Estimated Duration:** 60 minutes

---

### I2 – RecomputeAlbumStatsJob Core Logic (FR-003-02, FR-003-03, NFR-003-01)
**Goal:** Implement job that recomputes all 6 fields for a single album using nested set queries.

**Preconditions:** I1 complete (schema ready), Album model updated.

**Steps:**
1. Create `app/Jobs/RecomputeAlbumStatsJob.php` with `WithoutOverlapping` middleware (keyed by `album_id`)
2. Implement handle() method:
   - Load album by ID
   - Compute `num_children` (count direct children via nested set)
   - Compute `num_photos` (count photos directly in this album, not descendants)
   - Compute `max_taken_at` / `min_taken_at` (MIN/MAX of `photos.taken_at` for album + descendants via nested set JOIN, ignoring NULL)
   - Compute both cover IDs (call helper methods, see I3)
   - Save album with transaction
3. Add retry logic: 3 attempts, exponential backoff
4. Write unit test in `tests/Unit/Jobs/RecomputeAlbumStatsJobTest.php` with mocked album/photos verifying correct SQL queries
5. Write feature test in `tests/Precomputing/RecomputeAlbumStatsJobTest.php`: create album with photos, dispatch job, assert computed values correct

**Commands:**
- `php artisan test --testsuite=Unit --filter=RecomputeAlbumStatsJobTest` (unit tests)
- `php artisan test --testsuite=Precomputing --filter=RecomputeAlbumStatsJobTest` (feature tests)
- `make phpstan`

**Exit:** Job correctly computes stats for single album (no propagation yet), tests pass.

**Estimated Duration:** 90 minutes

---

### I3 – Dual Cover Selection Logic (FR-003-04, NFR-003-05, S-003-14, S-003-15, S-003-16, S-003-19, S-003-20)
**Goal:** Implement two cover selection methods (max-privilege and least-privilege) with NSFW context handling and album sorting respect.

**Preconditions:** I2 complete (job can call these methods).

**Steps:**
1. In `RecomputeAlbumStatsJob`, add helper method `computeMaxPrivilegeCover(Album $album): ?string`
   - Query: recursive descendants via nested set, no access filters
   - Check NSFW context: query if album or any parent has `is_nsfw=true`
   - If NSFW context: allow NSFW photos; else exclude NSFW photos
   - **CRITICAL - Two-level ordering:**
     1. Primary: `is_starred DESC` (starred photos always come first)
     2. Secondary: Album's `photo_sorting` criterion as tie-breaker: call `$album->getEffectivePhotoSorting()` to get `PhotoSortingCriterion`, apply sorting using `SortingDecorator` or equivalent
   - Return first photo ID or NULL
2. Add helper method `computeLeastPrivilegeCover(Album $album): ?string`
   - Same query WITH `PhotoQueryPolicy::appendSearchabilityConditions` and `AlbumQueryPolicy::appendAccessibilityConditions`
   - Same NSFW context rules
   - Same two-level ordering (`is_starred DESC`, then album `photo_sorting`)
   - Return first photo ID or NULL
3. Write unit tests in `tests/Unit/Jobs/` verifying NSFW context detection (mocked queries)
4. Write feature tests in `tests/Precomputing/CoverSelectionTest.php` for scenarios S-003-14, S-003-15, S-003-16, S-003-19, S-003-20 (NSFW boundary + sorting respect with real DB)

**Commands:**
- `php artisan test --testsuite=Unit --filter=CoverSelectionTest`
- `php artisan test --testsuite=Precomputing --filter=CoverSelectionTest`
- `make phpstan`

**Exit:** Both cover selection methods work correctly, NSFW boundaries respected, album sorting preferences respected, tests pass.

**Estimated Duration:** 90 minutes

---

### I4 – PHPUnit Test Suite Configuration
**Goal:** Add `Precomputing` test suite to PHPUnit configurations, update existing tests.

**Preconditions:** I1 complete (basic structure ready).

**Steps:**
1. Update `phpunit.ci.xml`: add `Precomputing` testsuite after `ImageProcessing`
2. Update `phpunit.pgsql.xml`: add `Precomputing` testsuite after `ImageProcessing`
3. Update `tests/ImageProcessing/Import/ImportFromServerBrowseTest.php`: fix expected array to include 'Precomputing' in sorted directory list
4. Create base test class `tests/Precomputing/Base/BasePrecomputingTest.php` extending `BaseApiWithDataTest`

**Commands:**
- `php artisan test --testsuite=Precomputing` (verify suite recognized, no tests yet)
- `php artisan test tests/ImageProcessing/Import/ImportFromServerBrowseTest.php` (verify fix)
- `make phpstan`

**Exit:** Test suite configured, existing tests pass with updated directory list.

**Estimated Duration:** 30 minutes

---

### I5 – Job Propagation System (FR-003-02, FR-003-03, NFR-003-02, NFR-003-04)
**Goal:** Add parent propagation to job, handle propagation failure gracefully.

**Preconditions:** I2-I3 complete (job computes single album, cover selection ready).

**Steps:**
1. In `RecomputeAlbumStatsJob::handle()`, after saving album:
   - Check if album has parent (`parent_id` not null)
   - If yes, dispatch `RecomputeAlbumStatsJob($parent_id)` to queue
   - On job failure (after 3 retries), log error and STOP propagation (do not dispatch parent job)
2. Add telemetry logging: job dispatch, propagation, propagation stop
3. Write feature test in `tests/Precomputing/PropagationTest.php`: 5-level nested album tree, mutate leaf album, verify all ancestors recomputed
4. Write failure test: simulate database error, verify propagation stops after 3 retries

**Commands:**
- `php artisan test --testsuite=Precomputing --filter=PropagationTest`
- `make phpstan`

**Exit:** Propagation reaches root for successful jobs, stops on failure, tests pass.

**Estimated Duration:** 75 minutes

---

### I6 – Event Listeners (FR-003-02, FR-003-03)
**Goal:** Hook photo/album mutation events to dispatch recomputation jobs.

**Preconditions:** I5 complete (job with propagation ready).

**Steps:**
1. Identify existing photo/album events (created, updated, deleted, moved)
2. Create event listeners or observers:
   - Photo created/deleted/moved → dispatch `RecomputeAlbumStatsJob(album_id)`
   - Photo `is_starred` changed → dispatch job (cover may change)
   - Album created/deleted/moved → dispatch `RecomputeAlbumStatsJob(parent_id)`
3. Register listeners in EventServiceProvider
4. Write feature tests in `tests/Precomputing/EventListenerTest.php` for scenarios S-003-01 through S-003-11 (mutations trigger recomputation)

**Commands:**
- `php artisan test --testsuite=Precomputing --filter=EventListenerTest`
- `make phpstan`

**Exit:** All mutation events correctly trigger jobs, scenarios S-003-01 through S-003-11 pass.

**Estimated Duration:** 90 minutes

---

### I7 – Cover Display Logic (FR-003-07, S-003-17, S-003-18)
**Goal:** Update HasAlbumThumb to select cover based on user permissions.

**Preconditions:** I3 complete (cover IDs computed).

**Steps:**
1. In `HasAlbumThumb::thumb()` relation method (or equivalent):
   - If `cover_id` not null, return explicit cover
   - Else, check user permissions:
     - If admin OR user owns album/ancestor, return `auto_cover_id_max_privilege`
     - Else, return `auto_cover_id_least_privilege`
2. Implement ownership check helper: nested set query for `owner_id` matching user in album or ancestors
3. Write feature tests in `tests/Precomputing/CoverDisplayTest.php` for scenarios S-003-09, S-003-10, S-003-17, S-003-18
4. Write security test: verify non-owner restricted user never sees max-privilege cover

**Commands:**
- `php artisan test --testsuite=Precomputing --filter=CoverDisplayTest`
- `make phpstan`

**Exit:** Cover display respects permissions, scenarios pass, no photo leakage.

**Estimated Duration:** 75 minutes

---

### I8 – AlbumBuilder Virtual Column Removal (FR-003-05)
**Goal:** Remove virtual column methods from AlbumBuilder, rely on physical columns.

**Preconditions:** I1-I7 complete (computed columns populated, events working).

**Steps:**
1. In `AlbumBuilder.php`, remove methods: `addVirtualMaxTakenAt()`, `addVirtualMinTakenAt()`, `addVirtualNumChildren()`, `addVirtualNumPhotos()`
2. Update `getModels()` to return columns without virtual additions
3. Search codebase for usages of removed methods, verify none exist
4. Run full test suite to ensure no regressions

**Commands:**
- `grep -r "addVirtual" app/` (verify no usages)
- `php artisan test` (full suite, including Precomputing)
- `make phpstan`

**Exit:** Virtual column methods removed, all tests pass, no API breakage.

**Estimated Duration:** 45 minutes

---

### I9 – Backfill Command (FR-003-06, NFR-003-03)
**Goal:** Create artisan command to populate computed fields for existing albums (bulk mode without album_id).

**Preconditions:** I2-I3 complete (job logic ready).

**Steps:**
1. Create `app/Console/Commands/BackfillAlbumFields.php`
2. Implement handle():
   - Load all albums ordered by `_lft` ASC (respects tree order: parents before children)
   - Process in chunks (configurable `--chunk` option, default 1000)
   - For each album, dispatch `RecomputeAlbumStatsJob` or compute inline (sync for backfill speed)
   - Show progress bar
   - Make idempotent (safe to re-run)
   - Add `--dry-run` option
3. Register command in Kernel
4. Write test in `tests/Precomputing/BackfillCommandTest.php`: create albums, run backfill, verify computed values correct

**Commands:**
- `php artisan lychee:recompute-album-fields --dry-run`
- `php artisan lychee:recompute-album-fields --chunk=100` (test run)
- `php artisan test --testsuite=Precomputing --filter=BackfillCommandTest`
- `make phpstan`

**Exit:** Backfill command works, idempotent, progress tracked, tests pass.

**Estimated Duration:** 75 minutes

**Status:** COMPLETE (will be merged into unified command in I15)

---

### I10 – Manual Recovery Command (CLI-003-02)
**Goal:** Create command for debugging/recovery after propagation failures (single-album mode with album_id).

**Preconditions:** I2-I5 complete (job logic ready).

**Steps:**
1. Create `app/Console/Commands/RecomputeAlbumStats.php`
2. Implement handle() accepting `{album_id}` argument
3. Dispatch `RecomputeAlbumStatsJob(album_id)` or run sync with `--sync` flag
4. Output status message
5. Write test in `tests/Precomputing/RecomputeAlbumStatsCommandTest.php`: verify command dispatches job correctly

**Commands:**
- `php artisan lychee:recompute-album-stats {album_id}`
- `php artisan lychee:recompute-album-stats {album_id} --sync`
- `php artisan test --testsuite=Precomputing --filter=RecomputeAlbumStatsCommandTest`
- `make phpstan`

**Exit:** Recovery command works, useful for manual intervention.

**Estimated Duration:** 30 minutes

**Status:** COMPLETE (will be merged into unified command in I15)

---

### I11 – Security Test Suite (NFR-003-05, S-003-13 through S-003-18)
**Goal:** Comprehensive security tests verifying no photo leakage via cover selection.

**Preconditions:** I7 complete (cover display logic ready).

**Steps:**
1. Create `tests/Precomputing/AlbumCoverSecurityTest.php` extending `BasePrecomputingTest`
2. Test scenarios:
   - S-003-13: Admin sees max-privilege cover with private photo
   - S-003-14: Non-NSFW album excludes NSFW sub-album photos from cover
   - S-003-15: NSFW album allows NSFW photos in cover
   - S-003-16: NSFW parent context applies to non-NSFW children
   - S-003-17: Shared user sees least-privilege cover
   - S-003-18: Non-owner sees different cover than owner (or NULL if no public photos)
3. Audit cover selection queries against PhotoQueryPolicy/AlbumQueryPolicy
4. Verify least-privilege cover NEVER contains photos invisible to restricted users

**Commands:**
- `php artisan test --testsuite=Precomputing --filter=AlbumCoverSecurityTest`
- `make phpstan`

**Exit:** All security scenarios pass, no photo leakage detected.

**Estimated Duration:** 90 minutes

---

### I12 – Performance Benchmarking (NFR-003-01, Test Strategy)
**Goal:** Measure query time reduction for album list queries.

**Preconditions:** I8 complete (virtual columns removed, physical columns in use).

**Steps:**
1. Create benchmark script or test in `tests/Precomputing/PerformanceBenchmarkTest.php`:
   - Loads album list with 50+ albums (nested structure)
   - Measures query time with virtual columns (baseline from git history or separate branch)
   - Measures query time with physical columns (current implementation)
   - Calculates percentage improvement
2. Verify ≥50% reduction in query time
3. Document results in this plan

**Commands:**
- `php artisan test --testsuite=Precomputing --filter=PerformanceBenchmarkTest` (or custom benchmark command)
- Record results in "Performance Benchmark Results" section below

**Exit:** Performance improvement verified, documented.

**Estimated Duration:** 60 minutes

---

### I13 – Regression Test Suite (Test Strategy)
**Goal:** Ensure all existing album/photo operations still work identically.

**Preconditions:** I1-I8 complete (all code changes done).

**Steps:**
1. Run full test suite (all existing tests must pass, including new Precomputing suite)
2. Manually test album list API endpoints (compare JSON output before/after for admin user)
3. Verify cover selection matches previous behavior for typical cases (no explicit cover set)
4. Test edge cases: empty albums, albums with NULL `taken_at` photos, deeply nested trees

**Commands:**
- `php artisan test` (full suite: Unit, Feature_v2, Install, ImageProcessing, Webshop, Precomputing)
- `make phpstan`

**Exit:** Zero regressions, all existing tests pass.

**Estimated Duration:** 45 minutes

---

### I14 – Documentation Updates (Documentation Deliverables)
**Goal:** Update knowledge-map, roadmap, and verify ADR-0003 is current.

**Preconditions:** I1-I13 complete (feature fully implemented).

**Steps:**
1. Update [docs/specs/4-architecture/knowledge-map.md](../../knowledge-map.md):
   - Remove AlbumBuilder virtual column details
   - Add computed fields to Album model documentation
   - Document event-driven update architecture (events → jobs → propagation)
   - Document new Precomputing test suite
2. Verify ADR-0003 ([docs/specs/6-decisions/ADR-0003-album-computed-fields-precomputation.md](../../6-decisions/ADR-0003-album-computed-fields-precomputation.md)) is current
3. Update [docs/specs/4-architecture/roadmap.md](../../roadmap.md): mark Feature 003 as complete
4. Update this plan's status to "Complete"

**Commands:**
- Review documentation files for accuracy
- No automated commands (manual review)

**Exit:** All documentation current, roadmap updated.

**Estimated Duration:** 45 minutes

---

### I15 – Merge Commands (FR-003-06)
**Goal:** Merge BackfillAlbumFields and RecomputeAlbumStats commands into single unified `lychee:recompute-album-stats` command.

**Preconditions:** I9-I10 complete (both commands exist and work independently).

**Steps:**
1. Update `RecomputeAlbumStats.php` to accept optional album_id argument
2. Implement dual behavior:
   - **With album_id:** Single-album recompute mode (existing behavior from I10)
     - Supports `--sync` flag for synchronous execution
     - Validates album exists, dispatches job or runs synchronously
   - **Without album_id:** Bulk backfill mode (behavior from BackfillAlbumFields I9)
     - Iterates all albums ordered by `_lft` ASC
     - Supports `--dry-run` and `--chunk=N` options
     - Shows progress bar, dispatches jobs for each album
3. Update command signature: `lychee:recompute-album-stats {album_id? : Optional album ID for single-album mode}`
4. Delete `BackfillAlbumFields.php` command file
5. Update command registration in Kernel (remove BackfillAlbumFields)
6. Update tests:
   - Merge test cases from both command test files
   - Test both modes (with/without album_id)
   - Test all flags (`--sync`, `--dry-run`, `--chunk`)
7. Update spec.md FR-003-06 to document merged command (already done)

**Commands:**
- `php artisan lychee:recompute-album-stats` (bulk mode)
- `php artisan lychee:recompute-album-stats --dry-run` (preview bulk)
- `php artisan lychee:recompute-album-stats --chunk=100` (custom chunk size)
- `php artisan lychee:recompute-album-stats {album_id}` (single album, async)
- `php artisan lychee:recompute-album-stats {album_id} --sync` (single album, sync)
- `php artisan test --testsuite=Precomputing --filter=RecomputeAlbumStatsCommandTest`
- `make phpstan`

**Exit:** Single unified command handles both use cases, old command deleted, tests pass.

**Estimated Duration:** 60 minutes

---

## Scenario Tracking

| Scenario ID | Increment / Task Reference | Notes |
|-------------|---------------------------|-------|
| S-003-01 | I6 / T-003-20 | Upload photo to empty album (event listener triggers job) |
| S-003-02 | I6 / T-003-21 | Delete last photo (event listener, values set to NULL) |
| S-003-03 | I6 / T-003-22 | Upload photo with older `taken_at` (min_taken_at updates) |
| S-003-04 | I6 / T-003-23 | Upload photo with newer `taken_at` (max_taken_at updates) |
| S-003-05 | I6 / T-003-24 | Create child album (parent num_children increments, propagates) |
| S-003-06 | I6 / T-003-25 | Move album (old/new parent num_children updates, propagates both branches) |
| S-003-07 | I6 / T-003-26 | Delete album with photos (parent stats recomputed, propagates) |
| S-003-08 | I6 / T-003-27 | Star photo (cover IDs update if starred photo becomes new candidate) |
| S-003-09 | I7 / T-003-28 | User sets explicit cover_id (automatic covers ignored) |
| S-003-10 | I7 / T-003-29 | User clears cover_id (automatic covers used, privilege-based) |
| S-003-11 | I5 / T-003-18 | Nested album propagation (3 levels deep, all update) |
| S-003-12 | I9 / T-003-34 | Backfill command (populate existing albums) |
| S-003-13 | I6 / T-003-28 | Photo mutation triggers full recomputation (all 6 fields) |
| S-003-14 | I3, I11 / T-003-14, T-003-40 | Non-NSFW album excludes NSFW sub-album photos (NSFW boundary) |
| S-003-15 | I3, I11 / T-003-14, T-003-40 | NSFW album allows NSFW photos in cover |
| S-003-16 | I3, I11 / T-003-14, T-003-40 | NSFW parent context applies to children |
| S-003-17 | I7, I11 / T-003-27, T-003-41 | Shared user sees least-privilege cover |
| S-003-18 | I7, I11 / T-003-27, T-003-42 | Non-owner sees different cover (or NULL) |

## Analysis Gate

**Status:** Pending (to be executed after I14 complete)

**Reviewer:** TBD

**Findings:** (To be filled after gate execution)

## Exit Criteria

- [ ] All 14 increments (I1-I14) complete and verified
- [ ] All tasks in `tasks.md` marked `[x]`
- [ ] Full test suite passes (`php artisan test`)
- [ ] PHPStan level 6+ passes (`make phpstan`)
- [ ] PHP-CS-Fixer passes (`vendor/bin/php-cs-fixer fix`)
- [ ] All 18 scenarios (S-003-01 through S-003-18) tested and passing
- [ ] Security test suite passes (no photo leakage)
- [ ] Performance benchmark shows ≥50% query time reduction
- [ ] Migration reversible via `down()` (tested)
- [ ] Backfill command works on staging clone with 100k+ albums
- [ ] Documentation updated (knowledge-map, roadmap, ADR-0003 verified)
- [ ] Analysis gate completed with findings addressed
- [ ] Roadmap status updated to "Complete"

## Performance Benchmark Results

(To be filled after I11 complete)

**Baseline (virtual columns):**
- Query time for 50-album list: TBD ms
- Number of subqueries: TBD

**After pre-computation (physical columns):**
- Query time for 50-album list: TBD ms
- Number of subqueries: TBD (should be 0)
- Improvement: TBD%

## Follow-ups / Backlog

- Consider adding database indexes on `taken_at`, `is_starred` if recomputation queries become slow (monitor in production)
- Explore mutation-specific recomputation (only update affected fields) if full recomputation proves expensive for large albums (current approach simpler, validate performance first)
- Add monitoring/alerting for job queue lag (ensure NFR-003-04 consistency target met)
- Evaluate soft-delete backfill if requirement emerges (currently out of scope per spec)
