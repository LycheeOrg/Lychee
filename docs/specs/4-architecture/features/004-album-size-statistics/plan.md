# Feature Plan 004 – Album Size Statistics Pre-computation

_Linked specification:_ `docs/specs/4-architecture/features/004-album-size-statistics/spec.md`
_Status:_ Draft
_Last updated:_ 2026-01-02

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

**User Value:** Dramatically reduce load times for storage statistics pages and user quota displays. Currently, fetching storage data for users with large galleries can take 5-10 seconds due to expensive aggregate queries across `size_variants`, `photo_album`, and nested album trees. After implementation, these queries should complete in <100ms (80%+ reduction).

**Success Signals:**
- `getFullSpacePerUser()` query time reduced from ~5s to <100ms for users with 10k photos
- `getTotalSpacePerAlbum()` query time reduced by 80%+  for albums with 1000+ photos
- User storage statistics page loads instantly
- Album size statistics update within 30 seconds of photo upload/deletion (eventual consistency)

**Quality Bars:**
- All computed sizes match current `Spaces.php` runtime calculations (±1 byte tolerance)
- Backfill completes for 100k albums in <10 minutes
- Zero data loss: FK cascades ensure orphaned statistics cleaned up automatically
- Job queue remains stable: Skip middleware prevents wasted CPU from concurrent updates

## Scope Alignment

**In scope:**
- New `album_size_statistics` table with per-variant size columns
- `RecomputeAlbumSizeJob` with Skip middleware (pattern from Feature 003)
- Event listeners for photo/album/variant mutations triggering recomputation
- Propagation logic: recompute parent albums recursively up to root
- Refactor `Spaces.php` methods to read from table instead of runtime aggregation
- Artisan command `lychee:recompute-album-sizes` for migration
- Artisan command `lychee:recompute-album-sizes {album_id}` for manual recovery
- Maintenance UI button to trigger backfill (similar to "Generate Size Variants")
- Fallback logic: if statistics row missing, use runtime calculation (defensive)

**Out of scope:**
- Storage quota enforcement (separate feature)
- Historical size tracking over time (only current state)
- Per-photo size caching (only per-album aggregates)
- Real-time updates (eventual consistency acceptable)
- Modifying Feature 003's `RecomputeAlbumStatsJob` (separate jobs)

## Dependencies & Interfaces

**Modules:**
- `app/Models/Album.php` - nested set tree structure (`_lft`, `_rgt`, `parent_id`)
- `app/Models/SizeVariant.php` - photo variant filesizes
- `app/Models/Photo.php` - photos table
- `app/Actions/Statistics/Spaces.php` - current runtime calculation logic (reference implementation)
- `app/Jobs/RecomputeAlbumStatsJob.php` - Skip middleware pattern to reuse
- `app/Constants/PhotoAlbum.php` - `photo_album` pivot table constants
- `app/Enum/SizeVariantType.php` - variant type enum (0-7, exclude PLACEHOLDER=7)

**External dependencies:**
- Laravel queue system (Feature 002 Worker Mode recommended but not required)
- Cache system (for Skip middleware job deduplication)
- Database supports `bigint unsigned` (18 exabyte max filesize)

**Telemetry contracts:**
- Log migration execution (TE-004-01)
- Log job dispatch, skip, propagation (TE-004-02)
- Log backfill progress (TE-004-03)

## Assumptions & Risks

**Assumptions:**
- Albums table has `id`, `_lft`, `_rgt`, `parent_id` columns (nested set model)
- SizeVariants table has `photo_id`, `type`, `filesize` columns
- Photo-album relationship via `photo_album` pivot table
- PLACEHOLDER variants (type 7) should be excluded from size calculations (verified in Spaces.php:46)
- Migration can run without downtime (schema change only, no long-running backfill)

**Risks / Mitigations:**
- **Risk:** Backfill takes too long for large installations (100k+ albums)
  - **Mitigation:** Chunk processing (1000 albums per batch), progress bar, idempotent (can resume)
- **Risk:** Job queue overwhelmed during backfill
  - **Mitigation:** Backfill runs outside normal traffic hours (operator-controlled timing)
- **Risk:** Statistics drift out of sync if events missed
  - **Mitigation:** Manual `lychee:recompute-album-sizes` command for recovery, fallback to runtime calculation
- **Risk:** Database contention during propagation (many parent updates)
  - **Mitigation:** Skip middleware deduplicates concurrent jobs, transactions isolate updates

## Implementation Drift Gate

**Evidence collection:**
- Before/after query performance benchmarks for `Spaces.php` methods (record in `docs/specs/4-architecture/features/004-album-size-statistics/performance-benchmarks.md`)
- Sample size verification: compare 1000 random albums' computed sizes against runtime calculation (tolerance ±1 byte)
- Test coverage report: ensure all scenarios S-004-01 through S-004-13 have passing tests

**Rerun commands:**
- `php artisan test --filter AlbumSizeStatisticsTest`
- `vendor/bin/php-cs-fixer fix app/Jobs/RecomputeAlbumSizeJob.php app/Actions/Statistics/Spaces.php`
- `make phpstan`

**Drift gate checklist:**
- [ ] Spec FR-004-01 through FR-004-05 requirements implemented
- [ ] NFR-004-01 through NFR-004-05 performance targets met
- [ ] All 13 scenarios pass integration tests
- [ ] No regressions in existing Spaces.php tests
- [ ] Backfill tested on staging clone (100k+ albums)

## Increment Map

### **I1 – Database Migration: Create album_size_statistics Table**
- **Goal:** Add new table with schema per FR-004-01, ensure reversibility
- **Preconditions:** None (foundational increment)
- **Steps:**
  1. Write migration `YYYY_MM_DD_HHMMSS_create_album_size_statistics_table.php`
  2. Schema: `album_id` (string PK, FK albums.id ON DELETE CASCADE), 7 size columns (bigint unsigned, default 0)
  3. Add indexes: primary key on `album_id`
  4. Write `down()` method: drop table
  5. Test: run migration up/down, verify table created/dropped
  6. Test: create album with statistics, delete album, verify FK cascade deletes statistics
- **Commands:**
  - `php artisan make:migration create_album_size_statistics_table`
  - `php artisan migrate`
  - `php artisan migrate:rollback`
- **Exit:** Migration runs cleanly in both directions, FK constraint verified

### **I2 – Eloquent Model: AlbumSizeStatistics**
- **Goal:** Create model for `album_size_statistics` table
- **Preconditions:** I1 complete
- **Steps:**
  1. Create `app/Models/AlbumSizeStatistics.php` model
  2. Set `$table = 'album_size_statistics'`, `$primaryKey = 'album_id'`, `public $incrementing = false`
  3. Define `$fillable`: all 7 size columns
  4. Define `$casts`: all size columns as `int`
  5. Define relationship: `belongsTo(Album::class, 'album_id')`
  6. Add inverse relationship to `Album.php`: `hasOne(AlbumSizeStatistics::class, 'album_id')`
  7. Write unit test: create statistics record, assert relationships work
- **Commands:**
  - `php artisan test --filter AlbumSizeStatisticsModelTest`
- **Exit:** Model created, relationships functional, unit test passes

### **I3 – RecomputeAlbumSizeJob: Core Job Logic**
- **Goal:** Implement job that computes size statistics for single album (no propagation yet), per FR-004-02
- **Preconditions:** I1, I2 complete
- **Steps:**
  1. Create `app/Jobs/RecomputeAlbumSizeJob.php` implementing `ShouldQueue`
  2. Add traits: `Dispatchable`, `InteractsWithQueue`, `Queueable`, `SerializesModels`
  3. Constructor: accept `string $album_id`, generate unique `$jobId`, store in cache per Q-004-03 resolution
  4. Implement `middleware()`: return `[Skip::when(fn() => $this->hasNewerJobQueued())]`
  5. Implement `hasNewerJobQueued()`: check cache key `album_size_latest_job:{album_id}`, log skip
  6. Implement `handle()`:
     - Clear cache key
     - Fetch album or return if not found
     - Query `size_variants` for photos in album (JOIN `photo_album`, WHERE `album_id`, exclude type 7)
     - GROUP BY `type`, SUM(`filesize`)
     - Build size array: initialize all 7 sizes to 0, populate from query results
     - `AlbumSizeStatistics::updateOrCreate(['album_id' => $album->id], $size_array)`
     - Wrap in DB transaction
  7. Set `public $tries = 3`
  8. Implement `failed(\Throwable $exception)`: log permanent failure
  9. Write unit test: mock album with photos, run job, assert statistics computed correctly
  10. Test PLACEHOLDER exclusion: add type 7 variant, verify not included in sizes
- **Commands:**
  - `php artisan test --filter RecomputeAlbumSizeJobTest`
- **Exit:** Job computes sizes correctly for single album, Skip middleware works, test passes

### **I4 – Propagation Logic: Dispatch Parent Job**
- **Goal:** Add propagation to parent after successful computation, per FR-004-02
- **Preconditions:** I3 complete
- **Steps:**
  1. In `RecomputeAlbumSizeJob::handle()`, after successful save:
     - Check `if ($album->parent_id !== null)`
     - Log: `Propagating to parent {parent_id}`
     - `self::dispatch($album->parent_id)`
  2. Write feature test: create 3-level nested album tree, dispatch job for leaf, assert all 3 levels updated (S-004-09)
  3. Test propagation stops on failure: mock exception, verify parent job not dispatched, `failed()` called
- **Commands:**
  - `php artisan test --filter AlbumSizePropagationTest`
- **Exit:** Propagation works up tree, stops on failure, test passes

### **I5 – Event Listeners: Trigger Recomputation on Mutations**
- **Goal:** Hook job dispatch into photo/album/variant events, per FR-004-02
- **Preconditions:** I3, I4 complete
- **Steps:**
  1. Identify existing events: `PhotoCreated`, `PhotoDeleted`, `PhotoMoved`, `AlbumCreated`, `AlbumDeleted`, `AlbumMoved`, `SizeVariantCreated`, `SizeVariantDeleted`, `SizeVariantRegenerated` (or equivalent)
  2. Create listener `app/Listeners/RecomputeAlbumSizeOnPhotoMutation.php`:
     - Listen to photo events
     - Extract `album_id` from event payload
     - `RecomputeAlbumSizeJob::dispatch($album_id)`
  3. Create listener `app/Listeners/RecomputeAlbumSizeOnVariantMutation.php`:
     - Listen to size variant events
     - Fetch variant's photo, get album_id from `photo_album` pivot
     - Dispatch job for each album photo belongs to
  4. Register listeners in `EventServiceProvider`
  5. Write feature test: upload photo, assert job dispatched, statistics updated (S-004-01)
  6. Test variant regeneration: regenerate variants, assert job dispatched (S-004-04)
  7. Test photo move: move photo between albums, assert both albums recomputed (S-004-05)
- **Commands:**
  - `php artisan test --filter AlbumSizeEventListenerTest`
- **Exit:** All mutation events trigger recomputation, tests pass

### **I6 – Refactor Spaces.php: getSpacePerAlbum**
- **Goal:** Replace runtime aggregation with table read, per FR-004-03
- **Preconditions:** I1, I2 complete (jobs not required for read refactor)
- **Steps:**
  1. Refactor `Spaces::getSpacePerAlbum()`:
     - Replace nested set + size_variants JOIN with simple `album_size_statistics` JOIN
     - Return breakdown: map DB columns to SizeVariantType keys
     - Add fallback: if statistics row NULL, use original query (defensive)
     - Log warning if fallback used
  2. Write feature test: create album with statistics, call method, assert correct breakdown
  3. Test fallback: delete statistics row, call method, assert original calculation used
  4. Compare output format: before/after must be identical (API compatibility)
- **Commands:**
  - `php artisan test --filter SpacesGetSpacePerAlbumTest`
- **Exit:** Method refactored, fallback works, test passes, output format unchanged

### **I7 – Refactor Spaces.php: getTotalSpacePerAlbum**
- **Goal:** Optimize total size query (including descendants)
- **Preconditions:** I6 complete
- **Steps:**
  1. Refactor `Spaces::getTotalSpacePerAlbum()`:
     - Use nested set to find all descendant albums: `WHERE _lft >= album._lft AND _rgt <= album._rgt`
     - JOIN `album_size_statistics` on descendants
     - SUM all 7 size columns, GROUP BY variant type
  2. Test: create nested album tree with photos, call method, assert total includes descendants (S-004-08)
  3. Benchmark: measure query time before/after
- **Commands:**
  - `php artisan test --filter SpacesGetTotalSpacePerAlbumTest`
- **Exit:** Method optimized, test passes, performance improved

### **I8 – Refactor Spaces.php: getFullSpacePerUser**
- **Goal:** Optimize user storage query, per NFR-004-02 (<100ms target)
- **Preconditions:** I6, I7 complete
- **Steps:**
  1. Refactor `Spaces::getFullSpacePerUser()`:
     - JOIN albums owned by user (WHERE `owner_id`)
     - JOIN `album_size_statistics`
     - SUM all 7 size columns across user's albums
  2. Test: create user with multiple albums, call method, assert total storage (S-004-07)
  3. Benchmark: test with 10k photos, verify <100ms (NFR-004-02)
- **Commands:**
  - `php artisan test --filter SpacesGetFullSpacePerUserTest`
- **Exit:** Method optimized, NFR-004-02 met, test passes

### **I9 – Refactor Spaces.php: Remaining Methods**
- **Goal:** Optimize `getSpacePerSizeVariantTypePerUser()`, `getSpacePerSizeVariantTypePerAlbum()`, `getPhotoCountPerAlbum()`, `getTotalPhotoCountPerAlbum()`
- **Preconditions:** I6, I7, I8 complete
- **Steps:**
  1. Refactor each method to use `album_size_statistics` where applicable
  2. Note: `getPhotoCountPerAlbum()` and `getTotalPhotoCountPerAlbum()` may not need changes (they count photos, not sizes) - verify with spec
  3. Write tests for each method, compare output before/after
- **Commands:**
  - `php artisan test --filter SpacesTest`
- **Exit:** All Spaces.php methods optimized, tests pass

### **I10 – Backfill Command: CLI Implementation**
- **Goal:** Implement `lychee:recompute-album-sizes` command, per FR-004-04
- **Preconditions:** I3, I4 complete (job must work)
- **Steps:**
  1. Create `app/Console/Commands/BackfillAlbumSizes.php` extending `Command`
  2. Signature: `lychee:recompute-album-sizes {--chunk=1000} {--album-id=}`
  3. Logic:
     - Query all albums ORDER BY `_lft` DESC (leaf-to-root)
     - If `--album-id` provided, filter to that album and ancestors
     - Chunk albums (default 1000)
     - For each chunk:
       - Dispatch `RecomputeAlbumSizeJob` for each album
       - Progress bar: `$this->output->progressBar($total)`
     - Wait for queue to drain (or run synchronously if preferred)
  4. Idempotent: safe to re-run (updateOrCreate)
  5. Write test: backfill 100 albums, verify all have statistics
  6. Test partial backfill: backfill half, re-run, verify idempotent
- **Commands:**
  - `php artisan lychee:recompute-album-sizes`
  - `php artisan test --filter BackfillAlbumSizesCommandTest`
- **Exit:** Command works, idempotent, test passes

### **I11 – Manual Recompute Command**
- **Goal:** Implement `lychee:recompute-album-sizes {album_id}` for recovery, per CLI-004-02
- **Preconditions:** I3, I4 complete
- **Steps:**
  1. Create `app/Console/Commands/RecomputeAlbumSizes.php` extending `Command`
  2. Signature: `lychee:recompute-album-sizes {album_id}`
  3. Logic:
     - Validate album exists
     - Dispatch `RecomputeAlbumSizeJob::dispatch($album_id)`
     - Output: "Dispatched recomputation for album {album_id}"
  4. Write test: run command, verify job dispatched
- **Commands:**
  - `php artisan lychee:recompute-album-sizes {album_id}`
  - `php artisan test --filter RecomputeAlbumSizesCommandTest`
- **Exit:** Command works, test passes

### **I12 – Maintenance UI: Backfill Button**
- **Goal:** Add admin UI button to trigger backfill, per FR-004-05
- **Preconditions:** I10 complete
- **Steps:**
  1. Locate existing maintenance page (likely `resources/js/components/admin/Maintenance.vue` or similar)
  2. Add button: "Backfill Album Size Statistics"
  3. Button click handler:
     - POST to `/api/admin/maintenance/backfill-album-sizes`
     - Disable button (loading state)
     - Poll for progress (or use websocket if available)
     - Show success/failure notification
  4. Backend: create controller method `MaintenanceController::backfillAlbumSizes()`
     - Dispatch `BackfillAlbumSizesJob` to queue (wrap command logic in queueable job)
     - Return job ID for progress tracking
  5. Progress tracking: store progress in cache, expose via `/api/admin/maintenance/backfill-status`
  6. Write feature test: POST endpoint, verify job dispatched
- **Commands:**
  - `npm run check` (frontend tests)
  - `php artisan test --filter MaintenanceBackfillTest`
- **Exit:** UI button works, job dispatched, progress trackable, tests pass

### **I13 – Integration Tests: End-to-End Scenarios**
- **Goal:** Verify all 13 scenarios from spec pass
- **Preconditions:** All previous increments complete
- **Steps:**
  1. Write integration test for each scenario S-004-01 through S-004-13
  2. Use real database (not mocks) with nested set tree, photos, variants
  3. Verify:
     - S-004-01: Upload photo to empty album
     - S-004-02: Delete last photo
     - S-004-03: Photo with partial variants
     - S-004-04: Regenerate variants
     - S-004-05: Move photo between albums
     - S-004-06: Create child album (sizes unchanged)
     - S-004-07: User storage query fast
     - S-004-08: Total space includes descendants
     - S-004-09: Nested propagation (3 levels)
     - S-004-10: Backfill matches runtime
     - S-004-11: Cover deletion unrelated to size
     - S-004-12: Concurrent jobs skip older
     - S-004-13: PLACEHOLDER excluded
- **Commands:**
  - `php artisan test --filter AlbumSizeStatisticsIntegrationTest`
- **Exit:** All scenarios pass

### **I14 – Performance Benchmarking**
- **Goal:** Validate NFR-004-01 through NFR-004-05 performance targets
- **Preconditions:** I13 complete
- **Steps:**
  1. Create staging database with realistic data: 100k albums, 1M photos, 5M size variants
  2. Benchmark before migration (runtime calculation):
     - `getFullSpacePerUser()` for user with 10k photos
     - `getTotalSpacePerAlbum()` for album with 1000 photos
     - Record baseline times
  3. Run migration + backfill
  4. Benchmark after migration (table reads):
     - Same queries as before
     - Record new times
  5. Calculate improvement: expect 80%+ reduction
  6. Verify job performance: `RecomputeAlbumSizeJob` completes in <2s for album with 1000 photos (NFR-004-01)
  7. Document results in `docs/specs/4-architecture/features/004-album-size-statistics/performance-benchmarks.md`
- **Commands:**
  - Custom benchmark script (use Laravel Telescope or custom timing logic)
- **Exit:** NFR targets met, results documented

### **I15 – Documentation & Knowledge Map Updates**
- **Goal:** Update documentation per spec deliverables
- **Preconditions:** All code complete
- **Steps:**
  1. Update `docs/specs/4-architecture/knowledge-map.md`:
     - Add `album_size_statistics` table entry
     - Document `RecomputeAlbumSizeJob` architecture
     - Link to Spaces.php refactoring
  2. Create ADR-0004: `docs/specs/6-decisions/ADR-0004-album-size-statistics-precomputation.md`
     - Decision: pre-compute vs. runtime calculation
     - Trade-offs: write complexity vs. read performance
     - Schema design: per-variant columns vs. normalized rows
     - Skip middleware pattern rationale
  3. Update README (if applicable): mention backfill command for new installations
- **Commands:**
  - Review PRs for documentation changes
- **Exit:** Knowledge map, ADR, README updated

## Scenario Tracking

| Scenario ID | Increment Reference | Notes |
|-------------|---------------------|-------|
| S-004-01 | I5, I13 | Upload photo to empty album: event triggers job, statistics created |
| S-004-02 | I5, I13 | Delete last photo: statistics updated to zeros |
| S-004-03 | I3, I13 | Photo with partial variants: only present variants counted |
| S-004-04 | I5, I13 | Regenerate variants: event triggers recomputation |
| S-004-05 | I5, I13 | Move photo: both source and destination updated |
| S-004-06 | I13 | Create child album: parent sizes unchanged (direct photos only) |
| S-004-07 | I8, I13 | User storage query: fast (<100ms) |
| S-004-08 | I7, I13 | Total space includes descendants: nested set query |
| S-004-09 | I4, I13 | Nested propagation: 3 levels updated |
| S-004-10 | I10, I13 | Backfill matches runtime: sample verification |
| S-004-11 | I13 | Cover deletion: size unaffected (separate concern) |
| S-004-12 | I3, I13 | Concurrent jobs: Skip middleware deduplicates |
| S-004-13 | I3, I13 | PLACEHOLDER excluded: type 7 filtered out |

## Analysis Gate

**Execution date:** To be scheduled after I13 complete
**Reviewer:** Lychee Team
**Findings:** To be recorded

**Checklist:**
- [ ] All FR requirements implemented and tested
- [ ] All NFR performance targets met (benchmarks documented)
- [ ] Skip middleware pattern correctly implemented (matches Feature 003)
- [ ] Fallback logic tested (runtime calculation when statistics missing)
- [ ] FK cascade tested (statistics deleted when album deleted)
- [ ] Backfill tested on staging clone (100k+ albums)
- [ ] No regressions in existing Spaces.php tests
- [ ] Code follows conventions (PSR-4, strict comparison, snake_case)
- [ ] License headers added to new files

## Exit Criteria

- [x] Specification approved (spec.md complete)
- [ ] All 15 increments complete
- [ ] All 13 scenarios pass integration tests (I13)
- [ ] Performance benchmarks meet NFR targets (I14)
- [ ] `php artisan test` passes (full test suite)
- [ ] `make phpstan` passes (no errors)
- [ ] `vendor/bin/php-cs-fixer fix` passes (code style)
- [ ] Documentation updated (knowledge map, ADR, README)
- [ ] Backfill tested on staging clone
- [ ] Analysis gate passed
- [ ] Roadmap updated to "Complete" status

## Follow-ups / Backlog

- **Optimization:** Add database indexes on `size_variants.photo_id`, `size_variants.type`, `photo_album.album_id` if query plans show table scans (defer to production monitoring)
- **Monitoring:** Add metrics dashboard for job queue depth, recomputation latency, backfill progress (Feature 00X)
- **Historical tracking:** Store size history over time for trend analysis (separate feature, requires time-series table)
- **Storage quota enforcement:** Use pre-computed sizes to enforce per-user limits (Feature 00Y)
- **Real-time updates:** Investigate websocket/polling for instant size updates in UI (currently eventual consistency)

---

*Last updated: 2026-01-02*
