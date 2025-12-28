# Feature 003 – Album Computed Fields Pre-computation

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2025-12-28 |
| Owners | Lychee Team |
| Linked plan | `docs/specs/4-architecture/features/003-album-computed-fields/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/003-album-computed-fields/tasks.md` |
| Roadmap entry | #003 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Refactor album virtual computed fields (`max_taken_at`, `min_taken_at`, `num_children`, `num_photos`) and automatic cover selection from runtime calculation to pre-computed database columns. This addresses the application layer (Laravel models, query builders), domain layer (album aggregates), and persistence layer (database schema, migrations). Currently, [AlbumBuilder.php](app/Models/Builders/AlbumBuilder.php) executes expensive subqueries on every album fetch. This feature moves computation to event-driven updates triggered by photo/album mutations, propagating changes up the album tree. The result is significantly reduced database load at read time, improving album list and gallery view performance.

## Goals
- Add five physical columns to `albums` table: `max_taken_at`, `min_taken_at`, `num_children`, `num_photos`, `computed_cover_id`
- Replace AlbumBuilder virtual column logic with simple column reads
- Implement event-driven update system: when photos/albums change, recompute affected albums and propagate to parents
- Maintain cover selection logic: `cover_id` remains user-settable; `computed_cover_id` stores automatic selection when `cover_id` is null
- Ensure correctness: computed values must match current virtual column results
- Improve performance: eliminate expensive nested set subqueries from album list queries

## Non-Goals
- Changing user-facing cover selection API (manual `cover_id` setting remains unchanged)
- Real-time updates (eventual consistency within job processing time is acceptable)
- Retroactive backfill for soft-deleted albums (only active albums)
- Performance optimization beyond pre-computation (query plan optimization, indexes handled separately)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-003-01 | Album model must have five new nullable columns: `max_taken_at`, `min_taken_at`, `num_children`, `num_photos`, `computed_cover_id` | Migration adds columns with default NULL. Existing albums populate via backfill job. New albums compute on first photo/child addition. | Migration must be reversible. Verify column types: `max_taken_at`/`min_taken_at` (datetime, nullable), `num_children`/`num_photos` (integer, default 0), `computed_cover_id` (string, nullable, foreign key to photos.id). | Migration failure rolls back transaction. Log error, halt deployment. | Log migration execution: `Adding computed fields to albums table`. No PII. | AlbumBuilder.php lines 100-174, Album model property definitions |
| FR-003-02 | When photo added/removed/moved to album, trigger job to recompute album's `max_taken_at`, `min_taken_at`, `num_photos`, `computed_cover_id` and propagate to parent albums | Photo create/delete/update events dispatch `RecomputeAlbumStatsJob(album_id)` to default queue. Job uses `WithoutOverlapping` middleware (keyed by album_id) to prevent concurrent execution for same album. Job recalculates all four fields using nested set queries (excluding soft-deleted photos via `whereNull('photos.deleted_at')`), saves album. On success, dispatches job for parent if parent exists. Propagation continues to root. Photo deletion event triggers recomputation for all parent albums to update `computed_cover_id` if deleted photo was the cover. | Job must use database transactions. Verify computed values match existing AlbumBuilder virtual column logic (SQL MIN/MAX ignores NULL `taken_at`). Test with deeply nested albums (5+ levels). `computed_cover_id` uses ON DELETE SET NULL foreign key constraint. | If job fails (database error), retry up to 3 times. After 3 failures, STOP propagation (do not dispatch parent job), log error with album_id and exception. Manual `php artisan lychee:recompute-album-stats {album_id}` command available for recovery. | Log job dispatch: `Recomputing stats for album {album_id}`. Log propagation: `Propagating to parent {parent_id}`. Log propagation stop: `Propagation stopped at album {album_id} due to failure`. | Event-driven architecture requirement, Q-003-01, Q-003-03, Q-003-04, Q-003-05, Q-003-06, Q-003-07 |
| FR-003-03 | When album added/removed/moved, trigger job to recompute parent's `num_children` and propagate | Album create/delete/move events dispatch `RecomputeAlbumStatsJob(parent_id)`. Job recalculates `num_children` (count of direct children), propagates to parent. | Verify nested set updates (`_lft`, `_rgt` changes) trigger recomputation. Test album move between parents (decrements old parent, increments new parent). | Same retry/logging as FR-003-02. | Same as FR-003-02. | Nested set model, album tree operations |
| FR-003-04 | `computed_cover_id` selection must replicate current HasAlbumThumb logic when `cover_id` is null | Query finds first photo (recursive descendants) ordered by: 1) `is_starred DESC`, 2) `taken_at DESC` (or `created_at` if `taken_at` null), 3) `id ASC`. Respects visibility/searchability filters. Saves photo ID to `computed_cover_id`. | Test: albums without explicit cover must show same cover before/after migration. Verify with nested albums, starred photos, NSFW filtering. | If no photos visible, `computed_cover_id` remains NULL (empty album). | Log cover computation: `Computed cover {photo_id} for album {album_id}`. | [HasAlbumThumb.php](app/Relations/HasAlbumThumb.php) lines 193-201, existing cover selection algorithm |
| FR-003-05 | AlbumBuilder virtual column methods must be deprecated and removed | Remove `addVirtualMaxTakenAt()`, `addVirtualMinTakenAt()`, `addVirtualNumChildren()`, `addVirtualNumPhotos()`. Update `getModels()` to simply return columns (no virtual additions). Existing API consumers read physical columns. | Search codebase for usages of virtual methods. Verify all album queries return correct data. Run full test suite. | If API consumers break, tests will fail. Address before merge. | N/A (code removal) | AlbumBuilder.php lines 109-174, 189-208 |
| FR-003-06 | Backfill command must populate computed fields for all existing albums | Artisan command `php artisan lychee:backfill-album-fields` iterates all albums (oldest to newest to respect tree order), computes values, saves. Progress bar shows completion. Idempotent (safe to re-run). Migration adds columns only; operator must manually run backfill during maintenance window. Phase 2 code includes dual-read fallback (uses old virtual columns if computed columns are NULL). | Command must complete without errors on production data. Verify computed values match current runtime values (sample check). Run on staging clone before production. Migration must be reversible via `down()` method (drops all 5 columns). | If database error mid-backfill, transaction rolls back chunk. Log error, skip album, continue. Operator can re-run to fill gaps. | Log backfill progress: `Backfilled {count}/{total} albums`. | Data migration requirement, Q-003-02, Q-003-08 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-003-01 | Recomputation jobs must process within 5 seconds for albums with <1000 direct photos, <100 children | Users expect near-immediate UI updates after photo upload/deletion | Measure job execution time in staging with realistic data volumes. Profile slow queries. | Database indexes on `_lft`, `_rgt`, `taken_at`, `is_starred` | Performance requirement |
| NFR-003-02 | Propagation to root must handle deep nesting (20+ levels) without stack overflow or timeout | Some users organize albums in deep hierarchies | Test with 25-level nested album tree. Job chain must complete within 60 seconds total. Use job queue for async processing, not recursive calls. | Laravel queue system (Feature 002 Worker Mode complements this) | Real-world usage patterns |
| NFR-003-03 | Migration must complete within 10 minutes for installations with 100k albums | Deployment downtime must be minimal | Run migration on staging clone with 100k+ albums. Optimize batch size. Schema changes must be non-blocking (add columns, backfill offline). | Database migration tools, chunk processing | Production deployment constraints |
| NFR-003-04 | Computed values must maintain eventual consistency within 30 seconds of mutation | Stale data is acceptable for brief window; correctness required after propagation | Monitor job queue lag. Alert if queue depth exceeds threshold. Test: upload photo, verify album stats update within 30s. | Job queue reliability, worker processes | User experience, data integrity |
| NFR-003-05 | Cover selection must respect user permissions and visibility filters | Cover must not leak private photos | Audit computed_cover_id selection query against existing AlbumQueryPolicy, PhotoQueryPolicy. Unit test with private albums, shared albums, NSFW content. | Existing policy classes | Security, privacy |

## UI / Interaction Mock-ups
Not applicable – this is a backend performance optimization. User-facing behavior (album counts, date ranges, covers) remains identical. No UI changes.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-003-01 | Upload photo to empty album: `num_photos` increments from 0 to 1, `max_taken_at`/`min_taken_at` set to photo's `taken_at`, `computed_cover_id` set to photo ID |
| S-003-02 | Delete last photo from album: `num_photos` decrements to 0, `max_taken_at`/`min_taken_at` set to NULL, `computed_cover_id` set to NULL |
| S-003-03 | Upload photo with `taken_at` older than album's `min_taken_at`: `min_taken_at` updates to older date, `max_taken_at` unchanged |
| S-003-04 | Upload photo with `taken_at` newer than album's `max_taken_at`: `max_taken_at` updates to newer date, `min_taken_at` unchanged |
| S-003-05 | Create child album: parent's `num_children` increments, propagates to grandparent (all ancestors) |
| S-003-06 | Move album to new parent: old parent's `num_children` decrements, new parent's `num_children` increments, propagates up both branches |
| S-003-07 | Delete album with photos: parent's `num_children` decrements, parent's `max_taken_at`/`min_taken_at`/`num_photos` recomputed (excluding deleted album's photos), propagates |
| S-003-08 | Star photo: if photo becomes new cover candidate (starred photos prioritized), `computed_cover_id` updates |
| S-003-09 | User sets explicit `cover_id`: `computed_cover_id` ignored in API responses, explicit cover takes precedence |
| S-003-10 | User clears explicit `cover_id` (sets to NULL): `computed_cover_id` used for cover display |
| S-003-11 | Nested album (3 levels deep): photo added to leaf album triggers recomputation of leaf, parent, grandparent (all three get updated `max_taken_at`, `num_photos` changes) |
| S-003-12 | Backfill command run on existing installation: all albums get computed fields populated, values match current AlbumBuilder virtual columns |

## Test Strategy
- **Unit:** Test `RecomputeAlbumStatsJob` logic in isolation (mocked album, verify SQL queries)
- **Feature:** Test each scenario S-003-01 through S-003-12 with real database
  - Create album hierarchy, perform mutations, assert computed columns correct
  - Verify propagation reaches root
- **Integration:** Test with existing album list API endpoints
  - Before migration: response uses virtual columns
  - After migration: response uses physical columns
  - Assert identical JSON output
- **Performance:** Benchmark album list query before/after (expect 50%+ reduction in query time for large albums)
- **Regression:** Full test suite must pass (ensure no breakage in album/photo operations)
- **Data Migration:** Backfill test on staging clone (100k+ albums), verify correctness via sampling

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-003-01 | Album computed fields: `max_taken_at`, `min_taken_at` (datetime, nullable) | Album model, migrations, AlbumBuilder |
| DO-003-02 | Album computed fields: `num_children`, `num_photos` (integer, default 0) | Album model, migrations, AlbumBuilder |
| DO-003-03 | Album computed field: `computed_cover_id` (string, nullable, FK to photos.id) | Album model, HasAlbumThumb, cover selection |

### API Routes / Services
No API changes – existing endpoints return same data structure, sourced from physical columns instead of virtual.

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-003-01 | `php artisan lychee:backfill-album-fields` | Populates computed fields for all existing albums. Idempotent, shows progress bar. Operator must run manually after migration during maintenance window. |
| CLI-003-02 | `php artisan lychee:recompute-album-stats {album_id}` | Manually recomputes stats for single album (debugging/recovery after propagation failure). |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-003-01 | Migration execution | `migration_name`, `duration_ms`. No PII. |
| TE-003-02 | Album stats recomputation | `album_id`, `trigger_event` (photo.created, album.moved, etc.), `duration_ms`. No PII. |
| TE-003-03 | Backfill progress | `processed_count`, `total_count`, `batch_num`. No PII. |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-003-01 | `tests/Fixtures/album-tree-5-levels.json` | Deeply nested album tree for propagation testing |
| FX-003-02 | `tests/Fixtures/album-with-100-photos.json` | Large album for performance testing |

### UI States
Not applicable – no UI changes.

## Telemetry & Observability
- **Migration logs:** Duration, success/failure, number of columns added
- **Job execution logs:** Album ID, recomputation trigger, duration, propagation path
- **Backfill progress:** Batch processing stats, estimated completion time
- **Performance metrics:** Query time reduction for album list endpoints (before/after comparison)

## Documentation Deliverables
1. Update [knowledge-map.md](../../knowledge-map.md):
   - Remove AlbumBuilder virtual column details
   - Add computed fields to Album model documentation
   - Document event-driven update architecture
2. Update [album-tree-structure.md](../../4-architecture/album-tree-structure.md):
   - Explain computed fields maintenance
   - Document propagation mechanism
3. Create ADR: `ADR-0001-album-computed-fields-precomputation.md` covering:
   - Decision to pre-compute vs. continue runtime calculation
   - Trade-offs: increased write complexity for improved read performance
   - Propagation strategy (jobs vs. immediate updates)
4. Update [roadmap.md](../../roadmap.md) with Feature 003 entry

## Fixtures & Sample Data
- Migration test fixtures: albums with various states (empty, with photos, nested)
- Performance test fixtures: large album sets (1k, 10k, 100k albums)
- Regression test fixtures: existing test data must work identically post-migration

## Spec DSL

```yaml
domain_objects:
  - id: DO-003-01
    name: Album.max_taken_at
    type: datetime
    nullable: true
    description: Maximum taken_at timestamp of all photos in album and descendants
  - id: DO-003-02
    name: Album.min_taken_at
    type: datetime
    nullable: true
    description: Minimum taken_at timestamp of all photos in album and descendants
  - id: DO-003-03
    name: Album.num_children
    type: integer
    default: 0
    description: Count of direct child albums
  - id: DO-003-04
    name: Album.num_photos
    type: integer
    default: 0
    description: Count of photos directly in this album (excludes descendants)
  - id: DO-003-05
    name: Album.computed_cover_id
    type: string
    nullable: true
    foreign_key: photos.id
    description: Automatically selected cover photo ID when cover_id is null

cli_commands:
  - id: CLI-003-01
    command: php artisan lychee:backfill-album-fields
    options:
      - --dry-run: Preview changes without writing
      - --chunk=1000: Batch size for processing
    description: Manually run after migration to populate computed fields
  - id: CLI-003-02
    command: php artisan lychee:recompute-album-stats {album_id}
    description: Manually trigger recomputation for debugging/recovery after propagation failure

telemetry_events:
  - id: TE-003-01
    event: migration.albums.add_computed_fields
    fields:
      - migration_name: string
      - duration_ms: integer
  - id: TE-003-02
    event: album.stats.recomputed
    fields:
      - album_id: string
      - trigger_event: string
      - duration_ms: integer
      - propagated: boolean
  - id: TE-003-03
    event: backfill.progress
    fields:
      - processed: integer
      - total: integer
      - percentage: float

jobs:
  - id: JOB-003-01
    class: RecomputeAlbumStatsJob
    queue: default
    retry: 3
    timeout: 60
    middleware: WithoutOverlapping (keyed by album_id)
    description: Recomputes all stats for single album and propagates to parent. Stops propagation on permanent failure (after 3 retries).

fixtures:
  - id: FX-003-01
    path: tests/Fixtures/album-tree-5-levels.json
    description: Nested album hierarchy for propagation testing
  - id: FX-003-02
    path: tests/Fixtures/album-with-100-photos.json
    description: Performance testing fixture
```

## Appendix

### Current AlbumBuilder Virtual Column Logic

**Location:** [app/Models/Builders/AlbumBuilder.php](app/Models/Builders/AlbumBuilder.php)

**Virtual columns added in `getModels()` (lines 189-208):**
```php
if ($columns === ['*'] && $base_query->columns === null) {
    $this->addVirtualMaxTakenAt();
    $this->addVirtualMinTakenAt();
    $this->addVirtualNumChildren();
    $this->addVirtualNumPhotos();
}
```

**Expensive subqueries:**
- `min_taken_at`/`max_taken_at` (lines 109-128): JOIN with albums via nested set (`_lft`, `_rgt`), JOIN photos, aggregate MIN/MAX
- `num_children` (lines 139-151): Correlated subquery counting direct children with visibility filters
- `num_photos` (lines 161-174): Correlated subquery counting photos with visibility filters

**Performance impact:** For album list with 50 albums, each album executes 4 subqueries = 200 total subqueries. With nested set ranges and joins, this creates significant database load.

### Cover Selection Logic

**Location:** [app/Relations/HasAlbumThumb.php](app/Relations/HasAlbumThumb.php)

**Current behavior (lines 86-96, 193-201):**
- If `cover_id` set: use explicit cover
- Else: query for best photo (recursive descendants)
  - Order: `is_starred DESC`, `taken_at DESC`, `id ASC`
  - Apply searchability filters (visibility, NSFW)
  - LIMIT 1

**Migration requirement:** `computed_cover_id` must replicate this exact logic, saved to database column.

### Event Propagation Example

```
Photo uploaded to Album C (child of B, child of A)
 ↓
RecomputeAlbumStatsJob(C) dispatched
 ↓ (recomputes C's stats)
RecomputeAlbumStatsJob(B) dispatched
 ↓ (recomputes B's stats, which aggregate C's data)
RecomputeAlbumStatsJob(A) dispatched
 ↓ (recomputes A's stats, which aggregate B+C data)
Complete
```

All jobs run asynchronously via queue. Parent jobs only dispatch after child completes (using job chaining).

### Migration Strategy

**Phase 1: Schema Change (migration up())**
```sql
ALTER TABLE albums ADD COLUMN max_taken_at DATETIME NULL;
ALTER TABLE albums ADD COLUMN min_taken_at DATETIME NULL;
ALTER TABLE albums ADD COLUMN num_children INT DEFAULT 0 NOT NULL;
ALTER TABLE albums ADD COLUMN num_photos INT DEFAULT 0 NOT NULL;
ALTER TABLE albums ADD COLUMN computed_cover_id CHAR(24) NULL;
ALTER TABLE albums ADD FOREIGN KEY (computed_cover_id) REFERENCES photos(id) ON DELETE SET NULL;
```

**Phase 2: Code Deploy**
- Deploy code with dual-read support: if computed column NULL, fall back to virtual calculation
- Deploy event listeners and jobs (`RecomputeAlbumStatsJob` with `WithoutOverlapping` middleware)
- Deploy manual recovery command `lychee:recompute-album-stats`

**Phase 3: Backfill (Manual Operator Action)**
- Operator runs `php artisan lychee:backfill-album-fields` during maintenance window
- Progress tracked, resumable, idempotent

**Phase 4: Cleanup**
- Remove virtual column methods from AlbumBuilder
- Remove fallback logic (rely solely on physical columns)

**Rollback Strategy (Q-003-08 Resolution)**

If issues discovered after Phase 1, full rollback via migration `down()`:

```sql
-- Migration down() method
ALTER TABLE albums DROP FOREIGN KEY albums_computed_cover_id_foreign;
ALTER TABLE albums DROP COLUMN computed_cover_id;
ALTER TABLE albums DROP COLUMN num_photos;
ALTER TABLE albums DROP COLUMN num_children;
ALTER TABLE albums DROP COLUMN min_taken_at;
ALTER TABLE albums DROP COLUMN max_taken_at;
```

**Rollback Constraints:**
- Safe to rollback during Phase 1-2 (before backfill)
- If Phase 3 backfill already ran, rollback discards computed data (acceptable, can be regenerated)
- **CRITICAL:** Do NOT rollback after Phase 4 cleanup (virtual column code removed). If issues found post-cleanup, fix forward instead of rolling back.
- Requires database write access during rollback

**Decision Rationale (ADR-0003):** Full rollback provides clean schema restoration and simple one-command rollback. Trade-off: data loss if backfill completed, but computed values can be regenerated. Safer than forward-only approach for early-stage deployment issues.
