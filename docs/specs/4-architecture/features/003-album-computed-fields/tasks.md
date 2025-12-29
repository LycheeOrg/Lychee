# Feature 003 Tasks – Album Computed Fields Pre-computation

_Status: Draft_
_Last updated: 2025-12-29_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (N/A here), and scenario IDs (`S-003-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment 1: Database Migration

- [x] T-003-01 – Create migration file for computed fields (FR-003-01).
  _Intent:_ Generate migration file with correct timestamp, set up up()/down() structure.
  _Verification commands:_
  - `ls database/migrations/*_add_computed_fields_to_albums.php` (file exists)
  _Notes:_ Migration must be reversible per Q-003-08 resolution.

- [x] T-003-02 – Implement migration up() method (FR-003-01).
  _Intent:_ Add 6 columns with correct types/nullability/defaults, add foreign key constraints.
  _Verification commands:_
  - `php artisan migrate --pretend` (preview SQL)
  - `php artisan migrate` (execute)
  - `php artisan db:show` (verify columns exist)
  - `make phpstan`
  _Notes:_ Columns: `max_taken_at` (datetime, nullable), `min_taken_at` (datetime, nullable), `num_children` (int, default 0), `num_photos` (int, default 0), `auto_cover_id_max_privilege` (string/char(24), nullable, FK to photos.id ON DELETE SET NULL), `auto_cover_id_least_privilege` (string/char(24), nullable, FK to photos.id ON DELETE SET NULL).

- [x] T-003-03 – Implement migration down() method (FR-003-01, Q-003-08).
  _Intent:_ Drop foreign keys first, then drop all 6 columns (reverse order).
  _Verification commands:_
  - `php artisan migrate:rollback --pretend` (preview SQL)
  - `php artisan migrate:rollback` (execute rollback)
  - `php artisan migrate` (re-migrate)
  - `make phpstan`
  _Notes:_ Must cleanly reverse migration for rollback safety.

- [x] T-003-04 – Update Album model properties and casts (FR-003-01).
  _Intent:_ Add 6 new properties to Album model with correct types, add casts for datetime/integer/string.
  _Verification commands:_
  - `make phpstan` (verify no type errors)
  - `php artisan test --filter=AlbumModelTest` (if model test exists)
  _Notes:_ Casts: `max_taken_at` => 'datetime', `min_taken_at` => 'datetime', `num_children` => 'integer', `num_photos` => 'integer', cover IDs as strings.

- [x] T-003-05 – Write unit test for migration idempotency (FR-003-01).
  _Intent:_ Test that migration up/down can be run multiple times without errors.
  _Verification commands:_
  - `php artisan test --filter=AddComputedFieldsToAlbumsMigrationTest`
  - `make phpstan`
  _Notes:_ Create test file `tests/Unit/Database/Migrations/AddComputedFieldsToAlbumsMigrationTest.php`.

### Increment 2: RecomputeAlbumStatsJob Core Logic

- [ ] T-003-06 – Create RecomputeAlbumStatsJob with WithoutOverlapping middleware (FR-003-02, NFR-003-01).
  _Intent:_ Generate job class, configure middleware to prevent concurrent execution for same album.
  _Verification commands:_
  - `ls app/Jobs/RecomputeAlbumStatsJob.php` (file exists)
  - `make phpstan`
  _Notes:_ Middleware keyed by `album_id` parameter.

- [ ] T-003-07 – Implement num_children and num_photos computation (FR-003-02).
  _Intent:_ Use nested set queries to count direct children, count photos directly in album (not descendants).
  _Verification commands:_
  - `php artisan test --testsuite=Unit --filter=RecomputeAlbumStatsJobTest::testCountComputation`
  - `make phpstan`
  _Notes:_ Unit test in `tests/Unit/Jobs/RecomputeAlbumStatsJobTest.php` with mocked data. `num_children`: `SELECT COUNT(*) FROM albums WHERE parent_id = :album_id`. `num_photos`: `SELECT COUNT(*) FROM photos WHERE album_id = :album_id`.

- [ ] T-003-08 – Implement max_taken_at and min_taken_at computation (FR-003-02).
  _Intent:_ Use nested set JOIN with photos table, compute MIN/MAX of taken_at (ignoring NULL).
  _Verification commands:_
  - `php artisan test --testsuite=Unit --filter=RecomputeAlbumStatsJobTest::testDateComputation`
  - `make phpstan`
  _Notes:_ Query album + descendants via `_lft`/`_rgt`, JOIN photos, aggregate MIN/MAX. Match existing AlbumBuilder logic (lines 109-128).

- [ ] T-003-09 – Add transaction and retry logic to job (FR-003-02).
  _Intent:_ Wrap computation in database transaction, configure 3 retries with exponential backoff.
  _Verification commands:_
  - `php artisan test --testsuite=Unit --filter=RecomputeAlbumStatsJobTest::testTransactionRollback`
  - `make phpstan`
  _Notes:_ Use Laravel DB::transaction(). Configure `$tries = 3` on job class.

- [ ] T-003-10 – Write feature test for single album recomputation (FR-003-02).
  _Intent:_ Create album with photos in real DB, dispatch job, assert computed values correct.
  _Verification commands:_
  - `php artisan test --testsuite=Precomputing --filter=RecomputeAlbumStatsJobTest`
  - `make phpstan`
  _Notes:_ Test file `tests/Precomputing/RecomputeAlbumStatsJobTest.php` extending `BasePrecomputingTest`. Verify against existing AlbumBuilder virtual column values.

### Increment 3: Dual Cover Selection Logic

- [ ] T-003-11 – Implement computeMaxPrivilegeCover helper (FR-003-04, NFR-003-05).
  _Intent:_ Query for best photo (recursive descendants) with NO access filters, ordered by is_starred DESC, taken_at DESC, id ASC.
  _Verification commands:_
  - `php artisan test --filter=CoverSelectionTest::testMaxPrivilegeCover`
  - `make phpstan`
  _Notes:_ Replicate HasAlbumThumb logic (lines 193-211) without searchability/accessibility filters. Return photo ID or NULL.

- [ ] T-003-12 – Implement computeLeastPrivilegeCover helper (FR-003-04, NFR-003-05).
  _Intent:_ Same query WITH PhotoQueryPolicy::appendSearchabilityConditions and AlbumQueryPolicy::appendAccessibilityConditions.
  _Verification commands:_
  - `php artisan test --filter=CoverSelectionTest::testLeastPrivilegeCover`
  - `make phpstan`
  _Notes:_ Apply existing policy classes to filter public photos only. Return photo ID or NULL.

- [ ] T-003-13 – Implement NSFW context detection for covers (FR-003-04, S-003-14, S-003-15, S-003-16).
  _Intent:_ Add helper to check if album or any parent has is_nsfw=true using nested set query. Apply NSFW filtering to both cover selection methods.
  _Verification commands:_
  - `php artisan test --filter=CoverSelectionTest::testNSFWContextDetection`
  - `make phpstan`
  _Notes:_ Query: `SELECT COUNT(*) FROM base_albums WHERE is_nsfw=1 AND _lft <= :album_lft AND _rgt >= :album_rgt`. If count > 0, album is in NSFW context (allow NSFW photos). Otherwise exclude NSFW photos.

- [ ] T-003-14 – Write tests for NSFW boundary scenarios (S-003-14, S-003-15, S-003-16).
  _Intent:_ Test that non-NSFW albums exclude NSFW sub-album photos, NSFW albums allow NSFW photos, NSFW parent context applies to children.
  _Verification commands:_
  - `php artisan test --testsuite=Precomputing --filter=CoverSelectionNSFWTest`
  - `make phpstan`
  _Notes:_ Test file `tests/Precomputing/CoverSelectionNSFWTest.php` extending `BasePrecomputingTest`. Cover scenarios S-003-14, S-003-15, S-003-16.

### Increment 4: PHPUnit Test Suite Configuration

- [ ] T-003-15 – Update phpunit.ci.xml with Precomputing test suite.
  _Intent:_ Add new `<testsuite name="Precomputing">` entry after ImageProcessing suite.
  _Verification commands:_
  - `php artisan test --testsuite=Precomputing` (verify suite recognized)
  - `make phpstan`
  _Notes:_ Add: `<testsuite name="Precomputing"><directory suffix="Test.php">./tests/Precomputing</directory></testsuite>` after ImageProcessing section.

- [ ] T-003-16 – Update phpunit.pgsql.xml with Precomputing test suite.
  _Intent:_ Add new `<testsuite name="Precomputing">` entry after ImageProcessing suite (same as ci.xml).
  _Verification commands:_
  - `grep -A 2 "Precomputing" phpunit.pgsql.xml` (verify entry exists)
  - `make phpstan`
  _Notes:_ Keep both config files in sync.

- [ ] T-003-17 – Fix ImportFromServerBrowseTest expected directory list.
  _Intent:_ Update expected array in testBrowseEndpointAsOwner to include 'Precomputing' directory.
  _Verification commands:_
  - `php artisan test tests/ImageProcessing/Import/ImportFromServerBrowseTest.php`
  - `make phpstan`
  _Notes:_ Update line 41: change array to `['..', 'Constants', 'Feature_v2', 'ImageProcessing', 'Install', 'Precomputing', 'Samples', 'Traits', 'Unit', 'Webshop', 'docker']` (sorted order).

- [ ] T-003-18 – Create BasePrecomputingTest base class.
  _Intent:_ Create `tests/Precomputing/Base/BasePrecomputingTest.php` extending `BaseApiWithDataTest` for shared test infrastructure.
  _Verification commands:_
  - `ls tests/Precomputing/Base/BasePrecomputingTest.php`
  - `make phpstan`
  _Notes:_ All Precomputing tests will extend this base class. Provides access to albums, photos, users from `BaseApiWithDataTest`.

### Increment 5: Job Propagation System

- [ ] T-003-15 – Add parent propagation to RecomputeAlbumStatsJob (FR-003-02, FR-003-03, NFR-003-02).
  _Intent:_ After saving album, dispatch job for parent (if exists), continue to root.
  _Verification commands:_
  - `php artisan test --filter=PropagationTest::testPropagationToRoot`
  - `make phpstan`
  _Notes:_ Check `parent_id`, dispatch `RecomputeAlbumStatsJob($parent_id)`. Use job chaining or dispatch in `handle()` after transaction commits.

- [ ] T-003-16 – Implement propagation failure handling (FR-003-02, NFR-003-02).
  _Intent:_ On job failure (after 3 retries), log error and STOP propagation (do not dispatch parent job).
  _Verification commands:_
  - `php artisan test --filter=PropagationTest::testPropagationStopsOnFailure`
  - `make phpstan`
  _Notes:_ Use job `failed()` method to log. Ensure parent job NOT dispatched on failure.

- [ ] T-003-17 – Add telemetry logging for propagation (TE-003-02).
  _Intent:_ Log job dispatch, propagation to parent, propagation stop on failure.
  _Verification commands:_
  - `php artisan test --filter=PropagationTest::testTelemetryLogging`
  - `make phpstan`
  _Notes:_ Log messages: "Recomputing stats for album {album_id}", "Propagating to parent {parent_id}", "Propagation stopped at album {album_id} due to failure". No PII.

- [ ] T-003-18 – Write test for deep nesting propagation (NFR-003-02, S-003-11).
  _Intent:_ Create 5-level (or 25-level for stress test) nested album tree, mutate leaf, verify all ancestors recomputed within 60 seconds.
  _Verification commands:_
  - `php artisan test --filter=PropagationTest::testDeepNesting`
  - `make phpstan`
  _Notes:_ Use fixture FX-003-01 (album-tree-5-levels.json). Assert propagation completes, all ancestors have correct values.

### Increment 5: Event Listeners

- [ ] T-003-19 – Identify and document existing photo/album events (FR-003-02, FR-003-03).
  _Intent:_ List all mutation events that should trigger recomputation (photo created/deleted/moved/starred, album created/deleted/moved).
  _Verification commands:_
  - Review existing event/observer classes
  - Document in code comments or this task's notes
  _Notes:_ Check `app/Observers/`, `app/Events/`, `app/Listeners/` for existing patterns.

- [ ] T-003-20 – Create photo event listeners (FR-003-02, S-003-01, S-003-02, S-003-03, S-003-04, S-003-08, S-003-13).
  _Intent:_ Hook photo created/deleted/updated events to dispatch RecomputeAlbumStatsJob(album_id).
  _Verification commands:_
  - `php artisan test --filter=PhotoEventListenerTest`
  - `make phpstan`
  _Notes:_ Listen for: created (S-003-01), deleted (S-003-02), taken_at changed (S-003-03, S-003-04), is_starred changed (S-003-08), NSFW flag changed (S-003-13). Dispatch job for photo's album_id.

- [ ] T-003-21 – Create album event listeners (FR-003-03, S-003-05, S-003-06, S-003-07).
  _Intent:_ Hook album created/deleted/moved events to dispatch RecomputeAlbumStatsJob(parent_id).
  _Verification commands:_
  - `php artisan test --filter=AlbumEventListenerTest`
  - `make phpstan`
  _Notes:_ Listen for: created (S-003-05), deleted (S-003-07), moved (S-003-06, dispatch for both old and new parent). Dispatch job for parent_id.

- [ ] T-003-22 – Register listeners in EventServiceProvider (FR-003-02, FR-003-03).
  _Intent:_ Add event-listener mappings to EventServiceProvider or use observer pattern.
  _Verification commands:_
  - `php artisan event:list` (verify listeners registered)
  - `make phpstan`
  _Notes:_ Ensure listeners are auto-discovered or manually registered.

- [ ] T-003-23 – Write feature tests for mutation scenarios (S-003-01 through S-003-11).
  _Intent:_ Test each scenario: upload photo to empty album, delete last photo, upload with older/newer taken_at, create/move/delete album, star photo, nested album mutations.
  _Verification commands:_
  - `php artisan test --filter=AlbumMutationScenariosTest`
  - `make phpstan`
  _Notes:_ Test file `tests/Feature/AlbumMutationScenariosTest.php`. Each test creates scenario, triggers event, asserts computed values correct.

### Increment 6: Cover Display Logic

- [ ] T-003-24 – Implement ownership check helper (FR-003-07).
  _Intent:_ Create method to check if user owns album or any ancestor using nested set query.
  _Verification commands:_
  - `php artisan test --filter=OwnershipCheckTest`
  - `make phpstan`
  _Notes:_ Query: `SELECT COUNT(*) FROM base_albums WHERE owner_id = :user_id AND _lft <= :album_lft AND _rgt >= :album_rgt`. Return true if count > 0.

- [ ] T-003-25 – Update HasAlbumThumb cover display logic (FR-003-07).
  _Intent:_ Modify thumb() relation to select cover based on: explicit cover_id > max-privilege (if admin/owner) > least-privilege.
  _Verification commands:_
  - `php artisan test --filter=CoverDisplayLogicTest`
  - `make phpstan`
  _Notes:_ Update `app/Relations/HasAlbumThumb.php`. Logic: if cover_id not null, return it; else if user.may_administrate OR user_owns_album_or_ancestor, return auto_cover_id_max_privilege; else return auto_cover_id_least_privilege.

- [ ] T-003-26 – Write tests for explicit cover scenarios (S-003-09, S-003-10).
  _Intent:_ Test user sets/clears explicit cover_id, verify automatic covers used correctly.
  _Verification commands:_
  - `php artisan test --filter=ExplicitCoverTest`
  - `make phpstan`
  _Notes:_ S-003-09: explicit cover takes precedence. S-003-10: NULL cover_id uses automatic covers.

- [ ] T-003-27 – Write tests for permission-based cover display (S-003-17, S-003-18).
  _Intent:_ Test admin sees max-privilege cover, owner sees max-privilege, shared user sees least-privilege, non-owner sees different cover.
  _Verification commands:_
  - `php artisan test --filter=CoverDisplayPermissionTest`
  - `make phpstan`
  _Notes:_ Test file `tests/Feature/CoverDisplayPermissionTest.php`. Multi-user scenarios with different permission levels.

### Increment 7: AlbumBuilder Virtual Column Removal

- [ ] T-003-28 – Remove virtual column methods from AlbumBuilder (FR-003-05).
  _Intent:_ Delete addVirtualMaxTakenAt(), addVirtualMinTakenAt(), addVirtualNumChildren(), addVirtualNumPhotos() methods.
  _Verification commands:_
  - `grep -r "addVirtual" app/Models/Builders/AlbumBuilder.php` (should return no matches)
  - `make phpstan`
  _Notes:_ Update `getModels()` to remove virtual column additions (lines 189-208).

- [ ] T-003-29 – Search codebase for removed method usages (FR-003-05).
  _Intent:_ Verify no code calls the removed virtual column methods.
  _Verification commands:_
  - `grep -r "addVirtualMaxTakenAt\|addVirtualMinTakenAt\|addVirtualNumChildren\|addVirtualNumPhotos" app/ tests/`
  - `make phpstan`
  _Notes:_ Should find zero usages. If found, update callers to rely on physical columns.

- [ ] T-003-30 – Run full test suite for regressions (FR-003-05).
  _Intent:_ Ensure no API breakage from virtual column removal.
  _Verification commands:_
  - `php artisan test` (full suite must pass)
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  _Notes:_ All existing tests should pass without modification. If failures, investigate and fix.

### Increment 8: Backfill Command

- [ ] T-003-31 – Create BackfillAlbumFields artisan command (FR-003-06, CLI-003-01).
  _Intent:_ Generate command class with signature `lychee:backfill-album-fields`.
  _Verification commands:_
  - `php artisan list | grep backfill-album-fields`
  - `make phpstan`
  _Notes:_ Command file: `app/Console/Commands/BackfillAlbumFields.php`.

- [ ] T-003-32 – Implement backfill handle() method (FR-003-06, NFR-003-03).
  _Intent:_ Load albums ordered by _lft ASC, process in chunks, compute stats, save. Show progress bar.
  _Verification commands:_
  - `php artisan lychee:backfill-album-fields --dry-run` (preview mode)
  - `php artisan lychee:backfill-album-fields --chunk=10` (test run)
  - `make phpstan`
  _Notes:_ Options: `--dry-run` (preview only), `--chunk=N` (batch size, default 1000). Use progress bar. Make idempotent (check if already computed, skip or recompute).

- [ ] T-003-33 – Add telemetry logging for backfill (TE-003-03).
  _Intent:_ Log backfill progress (processed count, total count, percentage).
  _Verification commands:_
  - `php artisan lychee:backfill-album-fields --chunk=10` (check logs)
  - `make phpstan`
  _Notes:_ Log messages: "Backfilled {count}/{total} albums ({percentage}%)". No PII.

- [ ] T-003-34 – Write test for backfill command (FR-003-06, S-003-12).
  _Intent:_ Create albums, run backfill, verify computed values correct.
  _Verification commands:_
  - `php artisan test --filter=BackfillAlbumFieldsCommandTest`
  - `make phpstan`
  _Notes:_ Test file `tests/Feature/Console/BackfillAlbumFieldsCommandTest.php`. Verify idempotency (can re-run safely).

### Increment 9: Manual Recovery Command

- [ ] T-003-35 – Create RecomputeAlbumStats artisan command (CLI-003-02).
  _Intent:_ Generate command class with signature `lychee:recompute-album-stats {album_id}`.
  _Verification commands:_
  - `php artisan list | grep recompute-album-stats`
  - `make phpstan`
  _Notes:_ Command file: `app/Console/Commands/RecomputeAlbumStats.php`.

- [ ] T-003-36 – Implement recovery command handle() method (CLI-003-02).
  _Intent:_ Accept album_id argument, dispatch RecomputeAlbumStatsJob or run sync, output status.
  _Verification commands:_
  - `php artisan lychee:recompute-album-stats {test_album_id}` (test with real album)
  - `make phpstan`
  _Notes:_ Useful for manual intervention after propagation failures.

- [ ] T-003-37 – Write test for recovery command (CLI-003-02).
  _Intent:_ Verify command dispatches job correctly.
  _Verification commands:_
  - `php artisan test --filter=RecomputeAlbumStatsCommandTest`
  - `make phpstan`
  _Notes:_ Test file `tests/Feature/Console/RecomputeAlbumStatsCommandTest.php`.

### Increment 10: Security Test Suite

- [ ] T-003-38 – Create AlbumCoverSecurityTest class (NFR-003-05).
  _Intent:_ Set up test file for comprehensive security testing.
  _Verification commands:_
  - `ls tests/Feature/AlbumCoverSecurityTest.php`
  - `make phpstan`
  _Notes:_ Test file: `tests/Feature/AlbumCoverSecurityTest.php`.

- [ ] T-003-39 – Test admin sees max-privilege cover with private photo (S-003-13).
  _Intent:_ Create private album with private photo, verify admin user sees max-privilege cover (not null).
  _Verification commands:_
  - `php artisan test --filter=AlbumCoverSecurityTest::testAdminSeesMaxPrivilegeCover`
  - `make phpstan`
  _Notes:_ Admin has `may_administrate = true`.

- [ ] T-003-40 – Test NSFW boundary scenarios (S-003-14, S-003-15, S-003-16).
  _Intent:_ Test non-NSFW album excludes NSFW sub-album photos, NSFW album allows NSFW photos, NSFW parent context applies to children.
  _Verification commands:_
  - `php artisan test --filter=AlbumCoverSecurityTest::testNSFWBoundaries`
  - `make phpstan`
  _Notes:_ Create nested NSFW/non-NSFW album structures, verify cover selection respects boundaries.

- [ ] T-003-41 – Test shared user sees least-privilege cover (S-003-17).
  _Intent:_ Create album with AccessPermission for user, verify user sees least-privilege cover (not max).
  _Verification commands:_
  - `php artisan test --filter=AlbumCoverSecurityTest::testSharedUserSeesLeastPrivilegeCover`
  - `make phpstan`
  _Notes:_ User has shared access but does not own album.

- [ ] T-003-42 – Test non-owner sees different/null cover (S-003-18).
  _Intent:_ Create album with private photos, verify non-owner restricted user sees least-privilege cover (may be NULL if no public photos).
  _Verification commands:_
  - `php artisan test --filter=AlbumCoverSecurityTest::testNonOwnerSeesDifferentCover`
  - `make phpstan`
  _Notes:_ Compare owner view vs non-owner view, assert covers differ (or non-owner sees NULL).

- [ ] T-003-43 – Audit cover selection queries against policies (NFR-003-05).
  _Intent:_ Manually review computeLeastPrivilegeCover query, verify PhotoQueryPolicy and AlbumQueryPolicy correctly applied.
  _Verification commands:_
  - Code review of cover selection methods
  - `make phpstan`
  _Notes:_ Ensure least-privilege cover NEVER contains photos invisible to restricted users. Document review in this task's notes.

### Increment 11: Performance Benchmarking

- [ ] T-003-44 – Create performance benchmark script or test (NFR-003-01).
  _Intent:_ Measure album list query time with virtual columns (baseline) vs physical columns (current).
  _Verification commands:_
  - `php artisan benchmark:album-list` (if custom command) OR run benchmark test
  - Document results in plan.md
  _Notes:_ Compare query times for 50+ album list. Target ≥50% reduction. May need to check out old commit for baseline measurement.

- [ ] T-003-45 – Verify performance improvement target met (NFR-003-01).
  _Intent:_ Analyze benchmark results, confirm ≥50% query time reduction achieved.
  _Verification commands:_
  - Review benchmark output
  - Update plan.md "Performance Benchmark Results" section
  _Notes:_ If target not met, investigate slow queries, consider adding indexes.

### Increment 12: Regression Test Suite

- [ ] T-003-46 – Run full test suite and verify zero regressions (Test Strategy).
  _Intent:_ Execute all existing tests, ensure 100% pass rate.
  _Verification commands:_
  - `php artisan test` (full suite)
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  _Notes:_ All tests must pass. Investigate and fix any failures before proceeding.

- [ ] T-003-47 – Manually test album list API endpoints (Test Strategy).
  _Intent:_ Compare JSON output before/after migration for admin user, verify identical structure/values.
  _Verification commands:_
  - `curl` or Postman requests to album list endpoints
  - Compare response JSON (may use diff tool)
  _Notes:_ Focus on computed fields (max_taken_at, min_taken_at, num_children, num_photos) and cover selection.

- [ ] T-003-48 – Test edge cases (Test Strategy).
  _Intent:_ Verify empty albums, albums with NULL taken_at photos, deeply nested trees work correctly.
  _Verification commands:_
  - `php artisan test --filter=AlbumEdgeCasesTest` (if specific test exists)
  - Manual testing
  _Notes:_ Edge cases: empty album (all fields NULL or 0), photos with no taken_at (use created_at for cover ordering), 20+ level nesting.

### Increment 13: Documentation Updates

- [ ] T-003-49 – Update knowledge-map.md (Documentation Deliverables).
  _Intent:_ Remove AlbumBuilder virtual column details, add computed fields documentation, document event-driven architecture.
  _Verification commands:_
  - Review `docs/specs/4-architecture/knowledge-map.md` for accuracy
  - `make phpstan` (ensure no broken references)
  _Notes:_ Sections to update: Album model (add computed fields), AlbumBuilder (remove virtual columns), Event architecture (add job propagation flow).

- [ ] T-003-50 – Verify ADR-0003 is current (Documentation Deliverables).
  _Intent:_ Review ADR-0003 to ensure it reflects final implementation, update if needed.
  _Verification commands:_
  - Review `docs/specs/6-decisions/ADR-0003-album-computed-fields-precomputation.md`
  _Notes:_ ADR should cover: pre-computation decision, propagation strategy, dual-cover strategy (Q-003-09 resolution), rollback strategy (Q-003-08 resolution).

- [ ] T-003-51 – Update roadmap.md (Documentation Deliverables).
  _Intent:_ Mark Feature 003 status as "Complete" in roadmap.
  _Verification commands:_
  - Review `docs/specs/4-architecture/roadmap.md`
  _Notes:_ Update status, completion date, link to spec/plan/tasks.

- [ ] T-003-52 – Update plan.md status to Complete (Documentation Deliverables).
  _Intent:_ Mark plan as complete, update last updated date.
  _Verification commands:_
  - Review `docs/specs/4-architecture/features/003-album-computed-fields/plan.md`
  _Notes:_ Change status from "Draft" to "Complete", update "Last updated" field.

## Notes / TODOs

- **Fixture Creation:** Tasks reference FX-003-01 (album-tree-5-levels.json) and FX-003-02 (album-with-100-photos.json). Create these fixtures before/during testing if they don't exist.
- **Dual-Read Fallback (Phase 2):** Plan mentions deploying code with dual-read support (fallback to virtual calculation if computed column NULL). This is implicit in early testing but should be removed in Increment 7 (virtual column removal). If needed, add explicit task for fallback logic implementation.
- **Queue Workers:** Tests involving job dispatch will require queue workers running OR use `Queue::fake()` for unit tests. Feature tests may need `php artisan queue:work --once` or sync queue driver.
- **PHPStan Level:** Verify project uses PHPStan level 6+ (mentioned in AGENTS.md and plan). Run `make phpstan` after every code change.
- **Commit Protocol:** After all tasks complete, follow AGENTS.md commit protocol: stage files, run static analysis/tests/formatting, prepare conventional commit message with `Spec impact:` line, present to operator.
- **Open Questions:** All high-/medium-impact questions resolved per spec (Q-003-01 through Q-003-09). If new questions arise during implementation, add to `docs/specs/4-architecture/open-questions.md` and pause for clarification.
- **Test Timing:** Tests should be written BEFORE implementation per SDD cadence. Each increment's tasks are ordered: tests first, then implementation.
