# Feature 004 – Album Size Statistics Pre-computation

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-01-02 |
| Owners | Lychee Team |
| Linked plan | `docs/specs/4-architecture/features/004-album-size-statistics/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/004-album-size-statistics/tasks.md` |
| Roadmap entry | #004 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Refactor album size statistics from runtime calculation to pre-computed database table. Currently, [Spaces.php](app/Actions/Statistics/Spaces.php) executes expensive aggregate queries across `size_variants`, `photo_album`, and nested album trees every time size statistics are requested. This feature creates a dedicated `album_size_statistics` table storing size breakdowns per album per size variant type, enabling fast lookups for user storage quotas, album space usage, and storage analytics. The computation is event-driven: when photos/albums change, a job recomputes affected albums and propagates changes up the album tree.

## Goals
- Create `album_size_statistics` table with columns: `album_id`, `size_thumb`, `size_thumb2x`, `size_small`, `size_small2x`, `size_medium`, `size_medium2x`, `size_original`
- Replace expensive `Spaces.php` aggregate queries with simple table reads and SUM operations
- Implement event-driven update system: when photos/albums/size-variants change, recompute affected albums and propagate to parents
- Maintain correctness: computed values must match current `Spaces.php` calculation results
- Improve performance: user storage queries should complete in <100ms (currently can take seconds for large galleries)
- Enable fast analytics: total storage per user = SUM of their albums' statistics

## Non-Goals
- Changing user-facing statistics API endpoints (same request/response format)
- Real-time updates (eventual consistency within job processing time is acceptable)
- Storage quota enforcement (separate feature)
- Per-photo size tracking (only per-album aggregates)
- Historical size tracking over time (only current state)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-004-01 | Database must have `album_size_statistics` table with schema: `album_id` (string, PK, FK to albums.id), `size_thumb` (bigint unsigned, default 0), `size_thumb2x` (bigint unsigned, default 0), `size_small` (bigint unsigned, default 0), `size_small2x` (bigint unsigned, default 0), `size_medium` (bigint unsigned, default 0), `size_medium2x` (bigint unsigned, default 0), `size_original` (bigint unsigned, default 0) | Migration creates table. Foreign key constraint `ON DELETE CASCADE` ensures orphaned records are cleaned up when album deleted. | Migration must be reversible. Verify column types support large values (bigint unsigned for filesizes in bytes up to ~18 exabytes). Test FK constraint deletes statistics when album deleted. | Migration failure rolls back transaction. Log error, halt deployment. | Log migration execution: `Creating album_size_statistics table`. No PII. | User requirement, Spaces.php line 45 (excludes type 7/PLACEHOLDER), SizeVariantType enum |
| FR-004-02 | When photo added/removed/moved to album, OR size variant created/deleted/regenerated, trigger job to recompute album's size statistics and propagate to parent albums | Photo/album/variant mutation events dispatch `RecomputeAlbumSizeJob(album_id)` to default queue. Job uses `Skip` middleware with cache-based deduplication (pattern from RecomputeAlbumStatsJob.php lines 76-93): each job gets unique ID (`uniqid('job_', true)`), stores latest job ID in cache key `album_size_latest_job:{album_id}` (TTL 1 day), middleware checks `Skip::when(fn() => hasNewerJobQueued())` to skip if newer job queued for same album. Job queries all size_variants for photos in album (direct children only, NOT descendants), groups by variant type, sums filesize. Updates/creates `album_size_statistics` row via `firstOrCreate`. On success, dispatches job for parent if parent exists. Propagation continues to root. PLACEHOLDER variants (type 7) excluded from all size calculations. Retry attempts: 3. | Job must use database transactions. Test with deeply nested albums (5+ levels). Verify computed sizes match current Spaces.php `getSpacePerAlbum()` output. Test variant regeneration triggers update. Test Skip middleware: concurrent jobs for same album should skip older jobs. | If job fails (database error), retry up to 3 times. After 3 failures, STOP propagation (do not dispatch parent job), log error with album_id and exception. Manual `php artisan lychee:recompute-album-sizes {album_id}` command available for recovery. | Log job dispatch: `Recomputing sizes for album {album_id} (job {job_id})`. Log job skip: `Skipping job {job_id} for album {album_id} due to newer job {newer_job_id} queued`. Log propagation: `Propagating to parent {parent_id}`. Log propagation stop: `Propagation stopped at album {album_id} due to failure`. | Q-004-01 (Option B), Q-004-03 (Option D), Spaces.php lines 176-184, RecomputeAlbumStatsJob.php lines 56-93 |
| FR-004-03 | `Spaces.php` methods must be refactored to read from `album_size_statistics` table instead of computing at runtime | `getSpacePerAlbum()`: JOIN `album_size_statistics`, return breakdown. `getTotalSpacePerAlbum()`: Use nested set query to find all descendant albums, JOIN their `album_size_statistics`, SUM by variant type. `getFullSpacePerUser()`: JOIN albums owned by user, SUM all variant columns from `album_size_statistics`. Similar optimizations for other methods. | Compare query performance before/after (expect 80%+ reduction in execution time). Verify output format unchanged (API compatibility). Run full test suite. | If statistics row missing (NULL), fall back to runtime calculation (defensive programming during migration period). Log warning: `Missing size statistics for album {album_id}, using fallback`. | Log query performance: `Spaces query completed in {ms}ms (before: {old_ms}ms)`. | Spaces.php all methods, performance requirement |
| FR-004-04 | Backfill command must populate size statistics for all existing albums | Artisan command `php artisan lychee:backfill-album-sizes` iterates all albums (leaf-to-root order using nested set _lft DESC to ensure children computed before parents), computes size breakdown, saves to `album_size_statistics` table. Progress bar shows completion. Idempotent (safe to re-run). Migration creates table only; operator must manually run backfill during maintenance window. | Command must complete without errors on production data. Verify computed values match current Spaces.php runtime calculations (sample check). Run on staging clone before production. Migration must be reversible via `down()` method (drops `album_size_statistics` table). | If database error mid-backfill, transaction rolls back for that album. Log error, continue to next album. Operator can re-run to fill gaps. | Log backfill progress: `Backfilled {count}/{total} albums`. | Q-004-02 (Option A + maintenance UI button), Feature 003 backfill pattern (CLI-003-01) |
| FR-004-05 | Admin maintenance UI must provide button to trigger backfill | Maintenance page (similar to existing "Generate Size Variants" button) includes "Backfill Album Size Statistics" button. Clicking triggers backfill command asynchronously via job queue. Progress displayed via polling or websocket. Button disabled while backfill running. Success/failure notification shown on completion. | Test button triggers backfill correctly. Verify progress updates. Test concurrent backfill prevention (button disabled if job already running). | Backfill job must be queueable. Frontend must handle long-running operation (progress polling). | Backfill job fails: display error notification, log error server-side. | Log UI trigger: `Backfill triggered from maintenance UI by user {user_id}`. | Q-004-02 resolution, admin UX requirement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-004-01 | Recomputation jobs must process within 2 seconds for albums with <1000 direct photos | Users expect near-immediate UI updates after photo upload/deletion | Measure job execution time in staging with realistic data volumes. Profile slow queries. | Database indexes on `size_variants.photo_id`, `size_variants.type`, `photo_album.album_id` | Performance requirement |
| NFR-004-02 | User storage queries must complete within 100ms for users with <10k photos | Storage quota UI should load instantly | Benchmark `getFullSpacePerUser()` before/after. Expect 80%+ reduction from current multi-second queries. | Indexed `album_size_statistics.album_id`, indexed `albums.owner_id` | User requirement, current performance pain point |
| NFR-004-03 | Migration must complete within 5 minutes for installations with 100k albums | Deployment downtime must be minimal | Run migration on staging clone with 100k+ albums. Schema change only (table creation), no backfill during migration. | Database migration tools | Production deployment constraints |
| NFR-004-04 | Computed sizes must maintain eventual consistency within 30 seconds of mutation | Stale data is acceptable for brief window; correctness required after propagation | Monitor job queue lag. Alert if queue depth exceeds threshold. Test: upload photo, verify album size updates within 30s. | Job queue reliability, worker processes (Feature 002) | User experience, data integrity |
| NFR-004-05 | Table must support albums up to 10TB total size (original + all variants) | Large galleries with RAW photos can exceed terabytes | bigint unsigned supports up to 18 exabytes (18,446,744,073,709,551,615 bytes). Test with mock albums >10TB. | Database column type selection | Real-world usage patterns |

## UI / Interaction Mock-ups
Not applicable – this is a backend performance optimization. User-facing behavior (storage statistics displays) remains identical. No UI changes.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-004-01 | Upload photo to empty album: `album_size_statistics` row created with sizes populated for each variant type, zeros for missing variants |
| S-004-02 | Delete last photo from album: `album_size_statistics` row updated with all sizes set to 0 (row remains, not deleted) |
| S-004-03 | Upload photo with only ORIGINAL and THUMB variants: corresponding size columns populated, other variant columns remain 0 |
| S-004-04 | Regenerate size variants for photo (e.g., admin triggers `GenSizeVariants`): album sizes recomputed, updated values reflect new variant filesizes |
| S-004-05 | Move photo between albums: source album sizes decremented, destination album sizes incremented, both propagate to respective parents |
| S-004-06 | Create child album with photos: parent album sizes unchanged (only direct photo sizes counted, NOT descendants) |
| S-004-07 | User queries their total storage via `getFullSpacePerUser()`: query sums `album_size_statistics` for user's owned albums, returns quickly (<100ms) |
| S-004-08 | Admin queries `getTotalSpacePerAlbum()` for album with 3 levels of sub-albums: nested set query finds all descendants, sums their `album_size_statistics`, returns total including sub-albums |
| S-004-09 | Nested album (3 levels deep): photo added to leaf album triggers recomputation of leaf, parent, grandparent statistics (all three get updated sizes) |
| S-004-10 | Backfill command run on existing installation: all albums get `album_size_statistics` rows populated, values match current Spaces.php runtime calculations |
| S-004-11 | Photo deleted that was a cover for album: size statistics updated, unrelated to cover logic (Feature 003 handles cover separately) |
| S-004-12 | Upload 10 photos simultaneously to same album: WithoutOverlapping middleware ensures only one RecomputeAlbumSizeJob runs, later jobs skip (eventual consistency maintained) |
| S-004-13 | Size variant PLACEHOLDER (type 7) created: excluded from size calculations, all size columns unchanged |

## Test Strategy
- **Unit:** Test `RecomputeAlbumSizeJob` logic in isolation (mocked album, verify SQL queries, assert correct variant type filtering)
- **Feature:** Test each scenario S-004-01 through S-004-13 with real database
  - Create album hierarchy, perform mutations, assert `album_size_statistics` table correct
  - Verify propagation reaches root
  - Test PLACEHOLDER exclusion
- **Integration:** Test with existing Spaces.php API usage
  - Before migration: response uses runtime calculation
  - After migration: response uses table reads
  - Assert identical numerical output (tolerance for rounding: ±1 byte)
- **Performance:** Benchmark `getFullSpacePerUser()` and `getTotalSpacePerAlbum()` before/after (expect 80%+ reduction in query time)
- **Regression:** Full test suite must pass (ensure no breakage in storage statistics endpoints)
- **Data Migration:** Backfill test on staging clone (100k+ albums), verify correctness via sampling (compare 1000 random albums against runtime calculation)

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-004-01 | AlbumSizeStatistics table schema: `album_id` (string PK FK), `size_thumb` (bigint unsigned), `size_thumb2x` (bigint unsigned), `size_small` (bigint unsigned), `size_small2x` (bigint unsigned), `size_medium` (bigint unsigned), `size_medium2x` (bigint unsigned), `size_original` (bigint unsigned) | Migration, AlbumSizeStatistics model, Spaces.php |

### API Routes / Services
No API changes – existing Spaces.php methods return same data structure, sourced from `album_size_statistics` table instead of runtime aggregation.

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-004-01 | `php artisan lychee:backfill-album-sizes` | Populates `album_size_statistics` for all existing albums. Idempotent, shows progress bar. Operator must run manually after migration during maintenance window. |
| CLI-004-02 | `php artisan lychee:recompute-album-sizes {album_id}` | Manually recomputes size statistics for single album (debugging/recovery after propagation failure). |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-004-01 | Migration execution | `migration_name`, `duration_ms`. No PII. |
| TE-004-02 | Album size recomputation | `album_id`, `trigger_event` (photo.created, variant.regenerated, etc.), `duration_ms`, `total_size_bytes`. No PII. |
| TE-004-03 | Backfill progress | `processed_count`, `total_count`, `batch_num`. No PII. |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-004-01 | `tests/Fixtures/album-with-mixed-variants.json` | Album with photos having different variant combinations (some missing MEDIUM, some missing THUMB2X, etc.) |
| FX-004-02 | `tests/Fixtures/album-10TB-mock.json` | Mock album data simulating 10TB total size for boundary testing |

### UI States
Not applicable – no UI changes.

## Telemetry & Observability
- **Migration logs:** Duration, success/failure, table creation confirmation
- **Job execution logs:** Album ID, recomputation trigger, duration, total size computed, propagation path
- **Backfill progress:** Batch processing stats, estimated completion time, errors encountered
- **Performance metrics:** Query time reduction for Spaces.php methods (before/after comparison), job queue depth

## Documentation Deliverables
1. Update [knowledge-map.md](../../knowledge-map.md):
   - Add `album_size_statistics` table documentation
   - Document event-driven size update architecture
   - Link to Spaces.php refactoring
2. Create ADR-0004: [ADR-0004-album-size-statistics-precomputation.md](../../6-decisions/ADR-0004-album-size-statistics-precomputation.md) covering:
   - Decision to pre-compute vs. continue runtime calculation
   - Trade-offs: increased write complexity for improved read performance
   - Table schema design (per-variant columns vs. normalized rows)
   - Integration with Feature 003 vs. separate architecture (Q-004-01 resolution)
3. Update [roadmap.md](../../roadmap.md) with Feature 004 entry

## Fixtures & Sample Data
- Migration test fixtures: albums with various states (empty, with photos, mixed variant types)
- Performance test fixtures: large album sets (1k, 10k, 100k albums)
- Regression test fixtures: existing test data must work identically post-migration

## Spec DSL

```yaml
domain_objects:
  - id: DO-004-01
    name: AlbumSizeStatistics
    table: album_size_statistics
    columns:
      - name: album_id
        type: string
        primary_key: true
        foreign_key: albums.id
        on_delete: CASCADE
      - name: size_thumb
        type: bigint unsigned
        default: 0
        description: Total bytes for THUMB variants (type 6) in album
      - name: size_thumb2x
        type: bigint unsigned
        default: 0
        description: Total bytes for THUMB2X variants (type 5) in album
      - name: size_small
        type: bigint unsigned
        default: 0
        description: Total bytes for SMALL variants (type 4) in album
      - name: size_small2x
        type: bigint unsigned
        default: 0
        description: Total bytes for SMALL2X variants (type 3) in album
      - name: size_medium
        type: bigint unsigned
        default: 0
        description: Total bytes for MEDIUM variants (type 2) in album
      - name: size_medium2x
        type: bigint unsigned
        default: 0
        description: Total bytes for MEDIUM2X variants (type 1) in album
      - name: size_original
        type: bigint unsigned
        default: 0
        description: Total bytes for ORIGINAL variants (type 0) in album

cli_commands:
  - id: CLI-004-01
    command: php artisan lychee:backfill-album-sizes
    behavior: Populate size statistics for all albums (leaf-to-root order)
    flags:
      - --chunk=1000: Batch size for processing
      - --album-id=<id>: Backfill single album and ancestors
  - id: CLI-004-02
    command: php artisan lychee:recompute-album-sizes {album_id}
    behavior: Manually recompute size statistics for single album

telemetry_events:
  - id: TE-004-01
    event: migration.album_size_statistics
    fields: [migration_name, duration_ms]
  - id: TE-004-02
    event: album.size_recomputed
    fields: [album_id, trigger_event, duration_ms, total_size_bytes]
  - id: TE-004-03
    event: backfill.album_sizes
    fields: [processed_count, total_count, batch_num]

fixtures:
  - id: FX-004-01
    path: tests/Fixtures/album-with-mixed-variants.json
  - id: FX-004-02
    path: tests/Fixtures/album-10TB-mock.json
```

## Appendix

### Size Variant Type Mapping
Per [SizeVariantType.php](app/Enum/SizeVariantType.php), the enum values are:
- 0: ORIGINAL
- 1: MEDIUM2X
- 2: MEDIUM
- 3: SMALL2X
- 4: SMALL
- 5: THUMB2X
- 6: THUMB
- 7: PLACEHOLDER (excluded from size calculations)

### Current Spaces.php Query Pattern
[Spaces.php](app/Actions/Statistics/Spaces.php) line 176-184 shows the current `getSpacePerAlbum()` implementation:
```php
->joinSub(
    query: DB::table('size_variants')
        ->select(['size_variants.id', 'size_variants.photo_id', 'size_variants.filesize'])
        ->where('size_variants.type', '!=', 7),  // Exclude PLACEHOLDER
    as: 'size_variants',
    first: 'size_variants.photo_id',
    operator: '=',
    second: PA::PHOTO_ID
)
```

This pattern is repeated across multiple methods, each performing expensive nested set joins and aggregations. Feature 004 eliminates these runtime queries by pre-computing into `album_size_statistics`.

### Integration with Feature 003
**Decision (Q-004-01 resolved - Option B):** Separate `RecomputeAlbumSizeJob` will be created, independent from Feature 003's `RecomputeAlbumStatsJob`. This decouples the features and allows independent optimization. However, the job implementation will reuse the proven Skip middleware pattern from Feature 003 (see [RecomputeAlbumStatsJob.php](app/Jobs/RecomputeAlbumStatsJob.php:56-93)) for consistency in the codebase.

### Job Deduplication Pattern
**Decision (Q-004-03 resolved - Option D):** Reuses cache-based Skip middleware pattern from Feature 003:
1. Each `RecomputeAlbumSizeJob` instance gets unique ID via `uniqid('job_', true)` in constructor
2. Constructor stores job ID in cache: `Cache::put('album_size_latest_job:' . $album_id, $jobId, ttl: 1 day)`
3. `middleware()` method returns `[Skip::when(fn() => $this->hasNewerJobQueued())]`
4. `hasNewerJobQueued()` checks if cache key `album_size_latest_job:{album_id}` contains different job ID
5. If newer job exists, older job is skipped (logged and not executed)
6. Successful job execution clears cache key via `Cache::forget()`

This pattern is simpler than `WithoutOverlapping` and guarantees the most recent update eventually processes.

### Backfill Strategy
**Decision (Q-004-02 resolved - Option A + Maintenance UI):** Two-pronged approach:
1. **CLI command** `php artisan lychee:backfill-album-sizes` for server operators with shell access
2. **Maintenance UI button** for operators using web interface (triggers same backfill logic via queued job)

Migration creates table schema only; backfill is manual step run during maintenance window.

---

*Last updated: 2026-01-02*
