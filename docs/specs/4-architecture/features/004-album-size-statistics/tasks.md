# Feature 004 Tasks – Album Size Statistics Pre-computation

_Status: Draft_
_Last updated: 2026-01-02_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`NFR-`), and scenario IDs (`S-004-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Database Migration

- [x] T-004-01 – Create migration file for album_size_statistics table (FR-004-01).
  _Intent:_ Generate migration stub with correct naming convention.
  _Verification commands:_
  - `php artisan make:migration create_album_size_statistics_table`
  - Verify file created in `database/migrations/`

- [x] T-004-02 – Implement up() migration: create table with schema (FR-004-01).
  _Intent:_ Add table with `album_id` PK FK, 7 size columns (bigint unsigned), indexes, FK constraint ON DELETE CASCADE.
  _Note:_ **DO NOT add timestamps()** – table should have exactly 8 columns (album_id + 7 size columns), no created_at/updated_at.
  _Verification commands:_
  - `php artisan migrate`
  - Verify table exists: `php artisan db:show`
  - Check schema: `DESCRIBE album_size_statistics`
  - Verify exactly 8 columns, no timestamp columns

- [x] T-004-03 – Implement down() migration: drop table (FR-004-01).
  _Intent:_ Ensure migration reversibility.
  _Verification commands:_
  - `php artisan migrate:rollback`
  - Verify table dropped
  - Re-run: `php artisan migrate`

- [x] T-004-04 – Test FK CASCADE: delete album, verify statistics deleted (FR-004-01).
  _Intent:_ Verify ON DELETE CASCADE constraint works.
  _Verification commands:_
  - Create test album with statistics row
  - Delete album
  - Verify statistics row deleted automatically

### I2 – Eloquent Model

- [x] T-004-05 – Create AlbumSizeStatistics model class (DO-004-01).
  _Intent:_ Model for album_size_statistics table with correct properties.
  _Verification commands:_
  - Create `app/Models/AlbumSizeStatistics.php`
  - Set `$table`, `$primaryKey`, `$incrementing`, `$fillable`, `$casts`

- [x] T-004-06 – Define relationships: belongsTo Album, inverse hasOne (DO-004-01).
  _Intent:_ Two-way relationship between Album and AlbumSizeStatistics.
  _Verification commands:_
  - Add `belongsTo(Album::class)` in AlbumSizeStatistics
  - Add `hasOne(AlbumSizeStatistics::class)` in Album

- [ ] T-004-07 – Write unit test for AlbumSizeStatistics model.
  _Intent:_ Test model creation, relationships, fillable fields.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsModelTest`

### I3 – RecomputeAlbumSizeJob Core

- [ ] T-004-08 – Create RecomputeAlbumSizeJob class skeleton (FR-004-02).
  _Intent:_ Job class with constructor, traits, properties.
  _Verification commands:_
  - Create `app/Jobs/RecomputeAlbumSizeJob.php`
  - Implement ShouldQueue, add traits

- [ ] T-004-09 – Implement constructor with unique job ID and cache storage (FR-004-02, Q-004-03 resolution).
  _Intent:_ Generate `uniqid('job_', true)`, store in cache `album_size_latest_job:{album_id}` with TTL 1 day.
  _Verification commands:_
  - Test job construction
  - Verify cache key set

- [ ] T-004-10 – Implement Skip middleware with hasNewerJobQueued() (FR-004-02, Q-004-03 resolution).
  _Intent:_ Reuse Feature 003 pattern: `Skip::when(fn() => hasNewerJobQueued())`.
  _Verification commands:_
  - Implement `middleware()` method
  - Implement `hasNewerJobQueued()` checking cache
  - Log skip events

- [ ] T-004-11 – Implement handle(): fetch album, compute sizes (FR-004-02, S-004-01, S-004-13).
  _Intent:_ Core computation logic: query size_variants for album's photos, GROUP BY type, SUM filesize, exclude PLACEHOLDER (type 7).
  _Verification commands:_
  - Implement handle() method
  - Query: `size_variants JOIN photo_album WHERE album_id, WHERE type != 7, GROUP BY type, SUM filesize`
  - Build size array (initialize all 7 to 0, populate from query)

- [ ] T-004-12 – Implement updateOrCreate for album_size_statistics (FR-004-02).
  _Intent:_ Save computed sizes via `AlbumSizeStatistics::updateOrCreate()`.
  _Verification commands:_
  - Wrap in DB transaction
  - Call `updateOrCreate(['album_id' => $album->id], $size_array)`

- [ ] T-004-13 – Implement failed() method and retry logic (FR-004-02).
  _Intent:_ Set `$tries = 3`, log permanent failure in `failed()`.
  _Verification commands:_
  - Set `public $tries = 3`
  - Implement `failed(\Throwable $exception)` with log

- [ ] T-004-14 – Write unit test for job: compute sizes correctly (S-004-01, S-004-03, S-004-13).
  _Intent:_ Mock album with photos/variants, run job, assert statistics computed.
  _Verification commands:_
  - `php artisan test --filter RecomputeAlbumSizeJobTest`
  - Test scenarios: empty album, partial variants, PLACEHOLDER exclusion

### I4 – Propagation Logic

- [ ] T-004-15 – Add parent propagation after successful save (FR-004-02, S-004-09).
  _Intent:_ Dispatch job for parent album after updating current album.
  _Verification commands:_
  - Check `if ($album->parent_id !== null)`
  - Log propagation
  - `self::dispatch($album->parent_id)`

- [ ] T-004-16 – Write feature test for 3-level nested propagation (S-004-09).
  _Intent:_ Create grandparent→parent→child tree, dispatch job for child, verify all 3 updated.
  _Verification commands:_
  - `php artisan test --filter AlbumSizePropagationTest`

- [ ] T-004-17 – Test propagation stops on failure (FR-004-02).
  _Intent:_ Mock exception during save, verify parent job NOT dispatched, failed() called.
  _Verification commands:_
  - Test with mock exception
  - Assert propagation stopped

### I5 – Event Listeners

- [ ] T-004-18 – Create listener for photo mutation events (FR-004-02, S-004-01, S-004-02, S-004-05).
  _Intent:_ Listen to PhotoCreated, PhotoDeleted, PhotoMoved; dispatch job.
  _Verification commands:_
  - Create `app/Listeners/RecomputeAlbumSizeOnPhotoMutation.php`
  - Extract album_id from event payload
  - `RecomputeAlbumSizeJob::dispatch($album_id)`

- [ ] T-004-19 – Create listener for size variant mutation events (FR-004-02, S-004-04).
  _Intent:_ Listen to SizeVariantCreated, SizeVariantDeleted, SizeVariantRegenerated; dispatch job.
  _Verification commands:_
  - Create `app/Listeners/RecomputeAlbumSizeOnVariantMutation.php`
  - Fetch variant's photo, get album_id(s), dispatch jobs

- [ ] T-004-20 – Register listeners in EventServiceProvider.
  _Intent:_ Hook listeners to events.
  _Verification commands:_
  - Edit `app/Providers/EventServiceProvider.php`
  - Add listener mappings

- [ ] T-004-21 – Write feature tests for event-driven recomputation (S-004-01, S-004-04, S-004-05).
  _Intent:_ Upload photo, regenerate variant, move photo; verify jobs dispatched and statistics updated.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeEventListenerTest`

### I6 – Refactor Spaces.php: getSpacePerAlbum

- [ ] T-004-22 – Refactor getSpacePerAlbum() to use album_size_statistics table (FR-004-03).
  _Intent:_ Replace runtime aggregation with table JOIN, return breakdown.
  _Verification commands:_
  - Edit `app/Actions/Statistics/Spaces.php`
  - Replace query with JOIN on `album_size_statistics`

- [ ] T-004-23 – Add fallback to runtime calculation if statistics missing (FR-004-03).
  _Intent:_ Defensive programming during migration period.
  _Verification commands:_
  - Check if statistics row NULL
  - Use original query as fallback
  - Log warning: `Missing size statistics for album {album_id}, using fallback`

- [ ] T-004-24 – Write feature test for getSpacePerAlbum() (FR-004-03).
  _Intent:_ Test refactored method, verify output format unchanged.
  _Verification commands:_
  - `php artisan test --filter SpacesGetSpacePerAlbumTest`
  - Compare output before/after refactor

### I7 – Refactor Spaces.php: getTotalSpacePerAlbum

- [ ] T-004-25 – Refactor getTotalSpacePerAlbum() with nested set query (FR-004-03, S-004-08).
  _Intent:_ Find descendants via nested set, JOIN their statistics, SUM.
  _Verification commands:_
  - Edit method
  - Nested set query: `WHERE _lft >= album._lft AND _rgt <= album._rgt`
  - JOIN album_size_statistics, SUM columns

- [ ] T-004-26 – Write feature test for getTotalSpacePerAlbum() (S-004-08).
  _Intent:_ Create nested tree with photos, verify total includes descendants.
  _Verification commands:_
  - `php artisan test --filter SpacesGetTotalSpacePerAlbumTest`

- [ ] T-004-27 – Benchmark getTotalSpacePerAlbum() performance improvement.
  _Intent:_ Measure query time before/after, document reduction.
  _Verification commands:_
  - Use Telescope or custom timing
  - Compare before/after on large album (1000+ photos)

### I8 – Refactor Spaces.php: getFullSpacePerUser

- [ ] T-004-28 – Refactor getFullSpacePerUser() for <100ms target (FR-004-03, NFR-004-02, S-004-07).
  _Intent:_ JOIN user's albums, SUM their statistics.
  _Verification commands:_
  - Edit method
  - JOIN albums WHERE owner_id, JOIN album_size_statistics, SUM

- [ ] T-004-29 – Write feature test for getFullSpacePerUser() (S-004-07).
  _Intent:_ User with multiple albums, verify total storage.
  _Verification commands:_
  - `php artisan test --filter SpacesGetFullSpacePerUserTest`

- [ ] T-004-30 – Benchmark getFullSpacePerUser() with 10k photos (NFR-004-02).
  _Intent:_ Verify <100ms performance target.
  _Verification commands:_
  - Test with user owning albums totaling 10k photos
  - Measure query time, assert <100ms

### I9 – Refactor Spaces.php: Remaining Methods

- [ ] T-004-31 – Refactor getSpacePerSizeVariantTypePerUser() (FR-004-03).
  _Intent:_ Use album_size_statistics for variant breakdown.
  _Verification commands:_
  - Edit method, test output

- [ ] T-004-32 – Refactor getSpacePerSizeVariantTypePerAlbum() (FR-004-03).
  _Intent:_ Use album_size_statistics for variant breakdown.
  _Verification commands:_
  - Edit method, test output

- [ ] T-004-33 – Verify getPhotoCountPerAlbum() and getTotalPhotoCountPerAlbum() unaffected.
  _Intent:_ These count photos, not sizes; may not need changes.
  _Verification commands:_
  - Review methods
  - Run existing tests

- [ ] T-004-34 – Run full Spaces.php test suite after refactoring.
  _Intent:_ Ensure no regressions.
  _Verification commands:_
  - `php artisan test --filter SpacesTest`

### I10 – Backfill Command

- [ ] T-004-35 – Create BackfillAlbumSizes command class (FR-004-04, CLI-004-01).
  _Intent:_ Artisan command `lychee:backfill-album-sizes`.
  _Verification commands:_
  - Create `app/Console/Commands/BackfillAlbumSizes.php`
  - Signature: `lychee:backfill-album-sizes {--chunk=1000} {--album-id=}`

- [ ] T-004-36 – Implement command logic: query albums, dispatch jobs (FR-004-04).
  _Intent:_ Iterate albums leaf-to-root (ORDER BY _lft DESC), chunk processing, progress bar.
  _Verification commands:_
  - Query albums with chunking
  - Dispatch RecomputeAlbumSizeJob for each
  - Progress bar: `$this->output->progressBar($total)`

- [ ] T-004-37 – Test backfill idempotency (FR-004-04).
  _Intent:_ Safe to re-run (updateOrCreate).
  _Verification commands:_
  - Run backfill on 100 albums
  - Re-run, verify no errors

- [ ] T-004-38 – Write feature test for backfill command (S-004-10).
  _Intent:_ Backfill albums, verify all have statistics matching runtime calculation.
  _Verification commands:_
  - `php artisan test --filter BackfillAlbumSizesCommandTest`

### I11 – Manual Recompute Command

- [ ] T-004-39 – Create RecomputeAlbumSizes command class (CLI-004-02).
  _Intent:_ Artisan command `lychee:recompute-album-sizes {album_id}`.
  _Verification commands:_
  - Create `app/Console/Commands/RecomputeAlbumSizes.php`
  - Signature: `lychee:recompute-album-sizes {album_id}`

- [ ] T-004-40 – Implement command logic: validate album, dispatch job (CLI-004-02).
  _Intent:_ Validate album exists, dispatch RecomputeAlbumSizeJob.
  _Verification commands:_
  - Validate album_id
  - Dispatch job
  - Output confirmation message

- [ ] T-004-41 – Write feature test for recompute command.
  _Intent:_ Run command, verify job dispatched.
  _Verification commands:_
  - `php artisan test --filter RecomputeAlbumSizesCommandTest`

### I12 – Maintenance UI Button

- [ ] T-004-42 – Add "Backfill Album Size Statistics" button to maintenance page (FR-004-05).
  _Intent:_ UI element in admin maintenance section.
  _Verification commands:_
  - Locate maintenance component (e.g., `resources/js/components/admin/Maintenance.vue`)
  - Add button with click handler

- [ ] T-004-43 – Create backend endpoint: POST /api/admin/maintenance/backfill-album-sizes (FR-004-05).
  _Intent:_ Trigger backfill via HTTP request.
  _Verification commands:_
  - Create controller method in `MaintenanceController`
  - Dispatch BackfillAlbumSizesJob to queue
  - Return job ID

- [ ] T-004-44 – Implement progress tracking: cache-based or job status (FR-004-05).
  _Intent:_ Store progress in cache, expose via GET endpoint.
  _Verification commands:_
  - Progress updates written to cache during backfill
  - GET `/api/admin/maintenance/backfill-status` returns progress

- [ ] T-004-45 – Frontend: poll for progress, display in UI (FR-004-05).
  _Intent:_ Show progress bar, disable button during backfill, notification on completion.
  _Verification commands:_
  - Poll progress endpoint
  - Update UI with progress
  - Show success/failure notification

- [ ] T-004-46 – Write feature test for maintenance UI endpoint.
  _Intent:_ POST endpoint triggers job, GET endpoint returns status.
  _Verification commands:_
  - `php artisan test --filter MaintenanceBackfillTest`

- [ ] T-004-47 – Frontend test: button triggers backfill.
  _Intent:_ UI test for button click flow.
  _Verification commands:_
  - `npm run check`

### I13 – Integration Tests

- [ ] T-004-48 – Write integration test for S-004-01: Upload photo to empty album.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testUploadPhotoToEmptyAlbum`

- [ ] T-004-49 – Write integration test for S-004-02: Delete last photo.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testDeleteLastPhoto`

- [ ] T-004-50 – Write integration test for S-004-03: Photo with partial variants.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testPartialVariants`

- [ ] T-004-51 – Write integration test for S-004-04: Regenerate variants.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testRegenerateVariants`

- [ ] T-004-52 – Write integration test for S-004-05: Move photo between albums.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testMovePhoto`

- [ ] T-004-53 – Write integration test for S-004-06: Create child album (sizes unchanged).
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testCreateChildAlbum`

- [ ] T-004-54 – Write integration test for S-004-07: User storage query fast.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testUserStorageQuery`

- [ ] T-004-55 – Write integration test for S-004-08: Total space includes descendants.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testTotalSpaceIncludesDescendants`

- [ ] T-004-56 – Write integration test for S-004-09: Nested propagation (3 levels).
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testNestedPropagation`

- [ ] T-004-57 – Write integration test for S-004-10: Backfill matches runtime.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testBackfillMatchesRuntime`

- [ ] T-004-58 – Write integration test for S-004-11: Cover deletion unrelated to size.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testCoverDeletionUnrelated`

- [ ] T-004-59 – Write integration test for S-004-12: Concurrent jobs skip older.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testConcurrentJobsSkip`

- [ ] T-004-60 – Write integration test for S-004-13: PLACEHOLDER excluded.
  _Verification commands:_
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest::testPlaceholderExcluded`

### I14 – Performance Benchmarking

- [ ] T-004-61 – Create staging database with realistic data (100k albums, 1M photos).
  _Intent:_ Performance testing environment.
  _Verification commands:_
  - Seed staging database
  - Verify album/photo/variant counts

- [ ] T-004-62 – Benchmark queries before migration (baseline).
  _Intent:_ Record current performance of getFullSpacePerUser(), getTotalSpacePerAlbum().
  _Verification commands:_
  - Measure query time for user with 10k photos
  - Measure query time for album with 1000 photos
  - Document in `docs/specs/4-architecture/features/004-album-size-statistics/performance-benchmarks.md`

- [ ] T-004-63 – Run migration + backfill on staging.
  _Intent:_ Execute migration, backfill all albums.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan lychee:backfill-album-sizes`

- [ ] T-004-64 – Benchmark queries after migration (verify improvement).
  _Intent:_ Measure same queries, calculate reduction percentage.
  _Verification commands:_
  - Re-measure query times
  - Assert 80%+ reduction
  - Document results

- [ ] T-004-65 – Verify job performance: <2s for album with 1000 photos (NFR-004-01).
  _Intent:_ Measure RecomputeAlbumSizeJob execution time.
  _Verification commands:_
  - Dispatch job for large album
  - Measure duration, assert <2s

### I15 – Documentation

- [ ] T-004-66 – Update knowledge-map.md with album_size_statistics table.
  _Intent:_ Document new table, job architecture.
  _Verification commands:_
  - Edit `docs/specs/4-architecture/knowledge-map.md`
  - Add entry for album_size_statistics
  - Link to Spaces.php refactoring

- [ ] T-004-67 – Create ADR-0004 for album size statistics precomputation.
  _Intent:_ Document architectural decision, trade-offs, rationale.
  _Verification commands:_
  - Create `docs/specs/6-decisions/ADR-0004-album-size-statistics-precomputation.md`
  - Use template from `docs/specs/templates/adr-template.md`
  - Document decision, trade-offs, Skip middleware pattern

- [ ] T-004-68 – Update README (if applicable): mention backfill command.
  _Intent:_ Operator documentation for new installations.
  _Verification commands:_
  - Edit README.md or operator docs
  - Add note about backfill command requirement

### Final Quality Gate

- [ ] T-004-69 – Run full test suite: php artisan test.
  _Verification commands:_
  - `php artisan test`
  - All tests pass

- [ ] T-004-70 – Run PHPStan: make phpstan.
  _Verification commands:_
  - `make phpstan`
  - No errors

- [ ] T-004-71 – Run code formatter: vendor/bin/php-cs-fixer fix.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - No style violations

- [ ] T-004-72 – Frontend checks: npm run check.
  _Verification commands:_
  - `npm run check`
  - All checks pass

- [ ] T-004-73 – Update roadmap status to "Complete".
  _Verification commands:_
  - Edit `docs/specs/4-architecture/roadmap.md`
  - Move Feature 004 from Active to Completed

## Notes / TODOs

- **Database indexes:** After deployment, monitor query plans for `size_variants` and `photo_album` JOINs. Add indexes if table scans detected.
- **Queue worker:** Feature 002 Worker Mode recommended for production (handles job restarts, queue priority).
- **Backfill timing:** Run backfill during maintenance window (low traffic) to avoid queue contention.
- **Cache driver:** Skip middleware requires cache (Redis recommended for multi-worker setups, file cache OK for single-worker).
- **License headers:** Remember to add SPDX license headers to all new PHP files per coding conventions.

---

*Last updated: 2026-01-02*
