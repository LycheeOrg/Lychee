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
- Add six physical columns to `albums` table: `max_taken_at`, `min_taken_at`, `num_children`, `num_photos`, `auto_cover_id_least_privilege`, `auto_cover_id_max_privilege`
- Replace AlbumBuilder virtual column logic with simple column reads
- Implement event-driven update system: when photos/albums change, recompute affected albums and propagate to parents
- Maintain cover selection logic: `cover_id` remains user-settable; automatic cover selection uses dual-privilege approach (least-privilege cover for restricted users, max-privilege cover for admin/owner)
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
| FR-003-01 | Album model must have six new nullable columns: `max_taken_at`, `min_taken_at`, `num_children`, `num_photos`, `auto_cover_id_least_privilege`, `auto_cover_id_max_privilege` | Migration adds columns with default NULL. Existing albums populate via backfill job. New albums compute on first photo/child addition. | Migration must be reversible. Verify column types: `max_taken_at`/`min_taken_at` (datetime, nullable), `num_children`/`num_photos` (integer, default 0), `auto_cover_id_least_privilege`/`auto_cover_id_max_privilege` (string, nullable, foreign keys to photos.id). | Migration failure rolls back transaction. Log error, halt deployment. | Log migration execution: `Adding computed fields to albums table`. No PII. | AlbumBuilder.php lines 100-174, Album model property definitions, Q-003-09 resolution |
| FR-003-02 | When photo added/removed/moved to album, trigger job to recompute album's `max_taken_at`, `min_taken_at`, `num_photos`, `auto_cover_id_least_privilege`, `auto_cover_id_max_privilege` and propagate to parent albums | Photo create/delete/update events dispatch `RecomputeAlbumStatsJob(album_id)` to default queue. Job uses `WithoutOverlapping` middleware (keyed by album_id) to prevent concurrent execution for same album. Job recalculates all six fields using nested set queries. **NSFW context determination:** Album is NSFW if it has `is_nsfw=true` OR any parent has `is_nsfw=true`. Album is safe if all parents are safe (no NSFW parent in hierarchy). **Cover computation runs twice:** (1) **`auto_cover_id_max_privilege`**: NO access control filters. NSFW handling: safe albums ALWAYS exclude NSFW photos; NSFW albums allow NSFW photos. (2) **`auto_cover_id_least_privilege`**: WITH `PhotoQueryPolicy::appendSearchabilityConditions` and `AlbumQueryPolicy::appendAccessibilityConditions` (public photos only). NSFW handling: safe albums ALWAYS exclude NSFW photos; NSFW albums allow NSFW photos. Both use same two-level ordering: `is_starred DESC` (starred photos first), then album's `photo_sorting` criterion as tie-breaker. On success, dispatches job for parent if parent exists. Propagation continues to root. Photo deletion event triggers recomputation for all parent albums to update cover IDs if deleted photo was either cover. | Job must use database transactions. Verify computed values match existing AlbumBuilder virtual column logic (SQL MIN/MAX ignores NULL `taken_at`). Test with deeply nested albums (5+ levels). Test NSFW album scenarios. Both cover ID columns use ON DELETE SET NULL foreign key constraints. | If job fails (database error), retry up to 3 times. After 3 failures, STOP propagation (do not dispatch parent job), log error with album_id and exception. Manual `php artisan lychee:recompute-album-stats {album_id}` command available for recovery. | Log job dispatch: `Recomputing stats for album {album_id}`. Log propagation: `Propagating to parent {parent_id}`. Log propagation stop: `Propagation stopped at album {album_id} due to failure`. | Event-driven architecture requirement, Q-003-01, Q-003-03, Q-003-04, Q-003-05, Q-003-06, Q-003-07, Q-003-09 |
| FR-003-03 | When album added/removed/moved, trigger job to recompute parent's `num_children` and propagate | Album create/delete/move events dispatch `RecomputeAlbumStatsJob(parent_id)`. Job recalculates `num_children` (count of direct children), propagates to parent. | Verify nested set updates (`_lft`, `_rgt` changes) trigger recomputation. Test album move between parents (decrements old parent, increments new parent). | Same retry/logging as FR-003-02. | Same as FR-003-02. | Nested set model, album tree operations |
| FR-003-04 | Dual automatic cover ID selection must replicate current HasAlbumThumb logic with different privilege levels when `cover_id` is null | **NSFW context:** Safe albums (no NSFW parent) ALWAYS exclude NSFW photos for BOTH max and least privilege. NSFW albums (album itself OR any parent is NSFW) allow NSFW photos for both privilege levels. **Max privilege (`auto_cover_id_max_privilege`):** Query finds first photo (recursive descendants) with NO access control filters (admin view), **ordered by two-level criterion: (1) `is_starred DESC` (starred photos first), (2) album's `photo_sorting` criterion** (`sorting_col` and `sorting_order` from `base_albums` table) as tie-breaker among starred photos or for non-starred photos. Use `getEffectivePhotoSorting()` to get sorting (falls back to default if null). NSFW photos excluded if album is safe. **Least privilege (`auto_cover_id_least_privilege`):** Same query WITH `PhotoQueryPolicy::appendSearchabilityConditions` and `AlbumQueryPolicy::appendAccessibilityConditions` (public photos only), **ordered by same two-level criterion (`is_starred DESC`, then `photo_sorting`)**. NSFW photos excluded if album is safe. Saves both photo IDs. | Test: albums without explicit cover must show same cover before/after migration for admin users. Verify with nested albums, starred photos, NSFW/non-NSFW albums, private albums, different sorting criteria (title ASC/DESC, taken_at ASC/DESC, created_at ASC/DESC, etc.). Test that restricted users see least-privilege cover (may be NULL if no public photos). Test safe albums ALWAYS exclude NSFW photos for both privilege levels. Test cover selection respects two-level ordering (starred first, then album sorting). | If no photos visible for a given privilege level, corresponding cover ID remains NULL. Both can be NULL for empty albums. | Log cover computation: `Computed covers max={max_photo_id}, least={least_photo_id} for album {album_id} (NSFW_context={safe\|nsfw}) using is_starred DESC + {sorting_col}_{sorting_order}`. | [HasAlbumThumb.php](app/Relations/HasAlbumThumb.php) lines 193-211, 226-229, Q-003-09 resolution (Option D), BaseAlbumImpl.php photo_sorting, PhotoSortingCriterion |
| FR-003-07 | Cover display logic must select appropriate cover ID based on user permissions | When `cover_id` is null, HasAlbumThumb returns: **If user is admin OR user owns the album:** use `auto_cover_id_max_privilege`. **Otherwise:** use `auto_cover_id_least_privilege`. Album ownership determined by: (1) `user.may_administrate === true` (admin), OR (2) checking if `user_id` matches `base_albums.owner_id` of current album OR any parent in tree via nested set query: `SELECT COUNT(*) FROM base_albums WHERE owner_id = :user_id AND _lft <= :album_lft AND _rgt >= :album_rgt`. If count > 0, user owns album or ancestor. | Test: admin sees max-privilege cover, album owner sees max-privilege cover, user owning parent album sees max-privilege cover, other users see least-privilege cover. Verify with shared albums, nested private albums, deeply nested ownership. | If selected cover ID is NULL, album displays with no cover (empty album or no visible photos for user's privilege level). | Log cover selection: `Selected cover {photo_id} (privilege_level={max\|least}) for album {album_id}, user {user_id}`. | Q-003-09 resolution (Option D), BaseAlbumImpl.php (owner_id), nested set ownership check |
| FR-003-05 | AlbumBuilder virtual column methods must be deprecated and removed | Remove `addVirtualMaxTakenAt()`, `addVirtualMinTakenAt()`, `addVirtualNumChildren()`, `addVirtualNumPhotos()`. Update `getModels()` to simply return columns (no virtual additions). Existing API consumers read physical columns. | Search codebase for usages of virtual methods. Verify all album queries return correct data. Run full test suite. | If API consumers break, tests will fail. Address before merge. | N/A (code removal) | AlbumBuilder.php lines 109-174, 189-208 |
| FR-003-06 | Backfill command must populate computed fields for all existing albums | Artisan command `php artisan lychee:backfill-album-fields` iterates all albums (oldest to newest to respect tree order), computes values, saves. Progress bar shows completion. Idempotent (safe to re-run). Migration adds columns only; operator must manually run backfill during maintenance window. Phase 2 code includes dual-read fallback (uses old virtual columns if computed columns are NULL). | Command must complete without errors on production data. Verify computed values match current runtime values (sample check). Run on staging clone before production. Migration must be reversible via `down()` method (drops all 6 columns). | If database error mid-backfill, transaction rolls back chunk. Log error, skip album, continue. Operator can re-run to fill gaps. | Log backfill progress: `Backfilled {count}/{total} albums`. | Data migration requirement, Q-003-02, Q-003-08 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-003-01 | Recomputation jobs must process within 5 seconds for albums with <1000 direct photos, <100 children | Users expect near-immediate UI updates after photo upload/deletion | Measure job execution time in staging with realistic data volumes. Profile slow queries. | Database indexes on `_lft`, `_rgt`, `taken_at`, `is_starred` | Performance requirement |
| NFR-003-02 | Propagation to root must handle deep nesting (20+ levels) without stack overflow or timeout | Some users organize albums in deep hierarchies | Test with 25-level nested album tree. Job chain must complete within 60 seconds total. Use job queue for async processing, not recursive calls. | Laravel queue system (Feature 002 Worker Mode complements this) | Real-world usage patterns |
| NFR-003-03 | Migration must complete within 10 minutes for installations with 100k albums | Deployment downtime must be minimal | Run migration on staging clone with 100k+ albums. Optimize batch size. Schema changes must be non-blocking (add columns, backfill offline). | Database migration tools, chunk processing | Production deployment constraints |
| NFR-003-04 | Computed values must maintain eventual consistency within 30 seconds of mutation | Stale data is acceptable for brief window; correctness required after propagation | Monitor job queue lag. Alert if queue depth exceeds threshold. Test: upload photo, verify album stats update within 30s. | Job queue reliability, worker processes | User experience, data integrity |
| NFR-003-05 | Cover selection must respect user permissions and visibility filters to prevent photo leakage | Cover must not leak private photos to unauthorized users | Dual-cover approach ensures: `auto_cover_id_least_privilege` NEVER exposes photos invisible to restricted users. `auto_cover_id_max_privilege` only shown to admin/owner. Audit both cover selection queries against existing AlbumQueryPolicy, PhotoQueryPolicy. Unit test with private albums, shared albums, NSFW content, multi-user scenarios. | Existing policy classes, album ownership checking | Security, privacy, Q-003-09 resolution |

## UI / Interaction Mock-ups
Not applicable – this is a backend performance optimization. User-facing behavior (album counts, date ranges, covers) remains identical. No UI changes.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-003-01 | Upload photo to empty album: `num_photos` increments from 0 to 1, `max_taken_at`/`min_taken_at` set to photo's `taken_at`, both cover IDs set to photo ID |
| S-003-02 | Delete last photo from album: `num_photos` decrements to 0, `max_taken_at`/`min_taken_at` set to NULL, both cover IDs set to NULL |
| S-003-03 | Upload photo with `taken_at` older than album's `min_taken_at`: `min_taken_at` updates to older date, `max_taken_at` unchanged |
| S-003-04 | Upload photo with `taken_at` newer than album's `max_taken_at`: `max_taken_at` updates to newer date, `min_taken_at` unchanged |
| S-003-05 | Create child album: parent's `num_children` increments, propagates to grandparent (all ancestors) |
| S-003-06 | Move album to new parent: old parent's `num_children` decrements, new parent's `num_children` increments, propagates up both branches |
| S-003-07 | Delete album with photos: parent's `num_children` decrements, parent's `max_taken_at`/`min_taken_at`/`num_photos` recomputed (excluding deleted album's photos), propagates |
| S-003-08 | Star photo: if photo becomes new cover candidate (starred photos prioritized), both cover IDs update |
| S-003-09 | User sets explicit `cover_id`: automatic cover IDs ignored in API responses, explicit cover takes precedence |
| S-003-10 | User clears explicit `cover_id` (sets to NULL): automatic cover IDs used for cover display (privilege-based selection) |
| S-003-11 | Nested album (3 levels deep): photo added to leaf album triggers recomputation of leaf, parent, grandparent (all three get updated `max_taken_at`, `num_photos` changes) |
| S-003-12 | Backfill command run on existing installation: all albums get computed fields populated, values match current AlbumBuilder virtual columns |
| S-003-13 | Photo added/removed/NSFW flag changed: full recomputation of all six fields triggered (simpler logic, no dependency tracking needed) |
| S-003-14 | Safe album (no NSFW parent) with NSFW sub-album: NSFW sub-album photos excluded from parent's cover selection for BOTH max and least privilege (safe albums ALWAYS have safe covers) |
| S-003-15 | NSFW album (is_nsfw=true OR any parent is NSFW): both `auto_cover_id_max_privilege` and `auto_cover_id_least_privilege` MAY include NSFW photos (sensitive content expected in NSFW context) |
| S-003-16 | NSFW album with non-NSFW sub-albums: all photos (including from non-NSFW children) eligible for cover selection for both privilege levels (parent NSFW context applies to entire subtree) |
| S-003-17 | User with shared access (AccessPermission) views album: sees `auto_cover_id_least_privilege` (not owner, not admin) |
| S-003-18 | Non-owner user views album with private photos: user sees `auto_cover_id_least_privilege` (may be NULL if no public photos, different from owner's view) |
| S-003-19 | Album with custom photo sorting (e.g., `title ASC`) and multiple starred photos: cover selection uses two-level ordering: `is_starred DESC` first (starred photos prioritized), then album's `photo_sorting` criterion among starred photos. If all photos un-starred, uses `photo_sorting` only. |
| S-003-20 | Album sorting changed after cover computed: cover remains stale until next recomputation event (eventual consistency). Changing `photo_sorting` does NOT automatically trigger recomputation. |

## Test Strategy
- **Unit:** Test `RecomputeAlbumStatsJob` logic in isolation (mocked album, verify SQL queries)
- **Feature:** Test each scenario S-003-01 through S-003-20 with real database
  - Create album hierarchy, perform mutations, assert computed columns correct
  - Verify propagation reaches root
- **Security:** Test dual-cover privilege separation (S-003-13 through S-003-18)
  - Admin vs. owner vs. restricted user sees correct cover
  - Private photo never leaks to least-privilege cover
  - NSFW handling correct for NSFW/non-NSFW albums
- **Integration:** Test with existing album list API endpoints
  - Before migration: response uses virtual columns
  - After migration: response uses physical columns
  - Assert identical JSON output for admin user
  - Assert correct cover selection for non-admin users
- **Performance:** Benchmark album list query before/after (expect 50%+ reduction in query time for large albums)
- **Regression:** Full test suite must pass (ensure no breakage in album/photo operations)
- **Data Migration:** Backfill test on staging clone (100k+ albums), verify correctness via sampling

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-003-01 | Album computed fields: `max_taken_at`, `min_taken_at` (datetime, nullable) | Album model, migrations, AlbumBuilder |
| DO-003-02 | Album computed fields: `num_children`, `num_photos` (integer, default 0) | Album model, migrations, AlbumBuilder |
| DO-003-03 | Album computed field: `auto_cover_id_max_privilege` (string, nullable, FK to photos.id) – cover visible to admin/owner | Album model, HasAlbumThumb, cover selection |
| DO-003-04 | Album computed field: `auto_cover_id_least_privilege` (string, nullable, FK to photos.id) – cover visible to all users | Album model, HasAlbumThumb, cover selection |

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
3. ADR-0003 already created: [ADR-0003-album-computed-fields-precomputation.md](../../6-decisions/ADR-0003-album-computed-fields-precomputation.md) covering:
   - Decision to pre-compute vs. continue runtime calculation
   - Trade-offs: increased write complexity for improved read performance
   - Propagation strategy (jobs vs. immediate updates)
   - Dual-cover strategy for multi-user security (Q-003-09 resolution)
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
    name: Album.auto_cover_id_max_privilege
    type: string
    nullable: true
    foreign_key: photos.id
    description: Automatically selected cover photo ID (admin/owner view) when cover_id is null
  - id: DO-003-06
    name: Album.auto_cover_id_least_privilege
    type: string
    nullable: true
    foreign_key: photos.id
    description: Automatically selected cover photo ID (most restrictive view) when cover_id is null

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

**Current behavior (lines 86-96, 193-211, 226-229):**
- If `cover_id` set: use explicit cover
- Else: query for best photo (recursive descendants)
  - Order: `is_starred DESC`, `taken_at DESC`, `id ASC`
  - Apply searchability/accessibility filters based on authenticated user (lines 203-210, 226-229)
  - LIMIT 1

**Migration requirement (Q-003-09 Resolution - Option D):**

Dual automatic cover IDs must be computed and stored:

1. **`auto_cover_id_max_privilege`**: Best photo query **without** access control filters (admin view)
   - Selects from all photos in album tree, regardless of visibility/permissions
   - **Ordering**: Two-level criterion:
     1. **Primary**: `is_starred DESC` (starred photos always ranked first)
     2. **Secondary**: Album's `photo_sorting` criterion (`sorting_col` and `sorting_order`) from `base_albums` table as tie-breaker among starred photos, or for all photos if none starred. Call `$album->getEffectivePhotoSorting()` to get criterion (falls back to default if null). Apply sorting to query using `SortingDecorator` or equivalent.
   - **NSFW context**: Check if album or any parent album has `is_nsfw=true`. If yes, allow NSFW photos; otherwise exclude NSFW photos from selection.
   - Used when user is admin OR user owns the album

2. **`auto_cover_id_least_privilege`**: Best photo query **with** all restrictive filters applied
   - Uses `PhotoQueryPolicy::appendSearchabilityConditions` and `AlbumQueryPolicy::appendAccessibilityConditions`
   - Selects only photos visible to ALL users (public photos)
   - **Ordering**: Same two-level criterion as max-privilege query (`is_starred DESC`, then album's `photo_sorting`)
   - **NSFW context**: Same rule - check if album/parent is NSFW. If yes, allow NSFW photos; otherwise exclude NSFW photos.
   - Used for non-admin, non-owner users

**NSFW Context Rule:**
- Lychee has `base_albums.is_nsfw` flag (stored in BaseAlbumImpl). If an album is NSFW, all photos in it are considered sensitive.
- When computing cover for album A: check if ANY parent (including A itself) has `is_nsfw=true` using nested set query:
  ```sql
  SELECT COUNT(*) FROM base_albums parent
  WHERE parent.is_nsfw = 1
    AND parent._lft <= A._lft
    AND parent._rgt >= A._rgt
  ```
  If count > 0, album is in NSFW context.
- **In NSFW context**: NSFW photos from descendants are acceptable for cover selection (sensitive content expected in this album tree).
- **Outside NSFW context**: NSFW photos from descendants are excluded from cover selection. Note: Photos in NSFW child album B of non-NSFW album A are excluded (NSFW boundary respected - photos "belong" to B's NSFW context, not A's).

**Display logic:**
```
IF cover_id IS NOT NULL:
    RETURN cover_id
ELSE IF user.may_administrate OR user_owns_album_or_ancestor:
    RETURN auto_cover_id_max_privilege
ELSE:
    RETURN auto_cover_id_least_privilege
```

Where `user_owns_album_or_ancestor` is determined by:
```sql
SELECT COUNT(*) FROM base_albums
WHERE owner_id = :user_id
  AND _lft <= :album_lft
  AND _rgt >= :album_rgt
```
(count > 0 means user owns this album or any ancestor)

This ensures no private photo leakage, respects NSFW boundaries, and respects album ownership hierarchy while maintaining performance benefits of pre-computation.

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
ALTER TABLE albums ADD COLUMN auto_cover_id_max_privilege CHAR(24) NULL;
ALTER TABLE albums ADD COLUMN auto_cover_id_least_privilege CHAR(24) NULL;
ALTER TABLE albums ADD FOREIGN KEY (auto_cover_id_max_privilege) REFERENCES photos(id) ON DELETE SET NULL;
ALTER TABLE albums ADD FOREIGN KEY (auto_cover_id_least_privilege) REFERENCES photos(id) ON DELETE SET NULL;
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
ALTER TABLE albums DROP FOREIGN KEY albums_auto_cover_id_max_privilege_foreign;
ALTER TABLE albums DROP FOREIGN KEY albums_auto_cover_id_least_privilege_foreign;
ALTER TABLE albums DROP COLUMN auto_cover_id_least_privilege;
ALTER TABLE albums DROP COLUMN auto_cover_id_max_privilege;
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
