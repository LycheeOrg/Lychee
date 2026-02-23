# ADR-0003: Album Computed Fields Pre-computation Strategy

- **Status:** Accepted
- **Date:** 2025-12-28
- **Last updated:** 2025-12-28
- **Related features/specs:** Feature 003 (docs/specs/4-architecture/features/003-album-computed-fields/spec.md)
- **Related open questions:** Q-003-02, Q-003-03, Q-003-05, Q-003-08, Q-003-09 (resolved)

## Context

Lychee's album listing performance is constrained by expensive runtime computations in [AlbumBuilder.php](app/Models/Builders/AlbumBuilder.php:109-174). Every album fetch executes 4 subqueries (min_taken_at, max_taken_at, num_children, num_photos) with nested set joins. For a list of 50 albums, this generates 200 database subqueries, creating significant load.

Feature 003 addresses this by pre-computing these values into physical database columns and maintaining them via event-driven updates. However, this introduces architectural decisions around:

1. **Backfill execution strategy** during migration (manual vs. automatic)
2. **Job deduplication** for concurrent album mutations (bulk uploads)
3. **Propagation failure handling** when jobs fail mid-tree
4. **Migration rollback strategy** for multi-phase deployments
5. **Multi-user cover selection strategy** for pre-computed automatic cover IDs

These decisions affect multiple modules: application layer (Laravel models, events), domain layer (album aggregates), persistence layer (migrations, database schema), CLI (artisan commands), and security layer (photo visibility policies).

## Decision

We adopt an **event-driven pre-computation architecture** with the following strategies:

### 1. Manual Backfill Execution (Q-003-02)

Migration adds columns only; operator manually runs `php artisan lychee:recompute-album-stats` (bulk mode, without album_id) during maintenance window. Code includes dual-read fallback (uses virtual columns if computed columns are NULL) until backfill completes.

**Rationale:** Operator controls timing (low-traffic period), migration completes quickly (<1 minute vs. 10 minutes for 100k albums), safer rollback (no data loss), aligns with dual-read pattern. All Lychee commands use `lychee:` namespace convention.

### 2. Job Deduplication via WithoutOverlapping Middleware (Q-003-03)

`RecomputeAlbumStatsJob` uses Laravel's `WithoutOverlapping` middleware keyed by `album_id`. Concurrent jobs for the same album queue up; only one executes at a time per album.

**Rationale:** Built-in Laravel feature (consistent with Feature 002 Worker Mode), prevents wasted work, automatic lock release, simple implementation. Jobs queue up (50 bulk uploads → 49 wait), but eventual consistency (30-second window per NFR-003-04) allows this delay.

### 3. Stop Propagation on Permanent Failure (Q-003-05)

If a recomputation job fails after 3 retries, do NOT dispatch parent job. Log error with `album_id`. Operator uses manual recovery command `php artisan lychee:recompute-album-stats {album_id}`.

**Rationale:** Prevents cascading errors (if child fails, parent likely fails too due to shared root cause). Clear failure boundary enables root-cause investigation. Requires monitoring/alerting to detect failures.

### 4. Full Rollback with down() Migration (Q-003-08)

Migration `down()` method drops all 6 columns (max_taken_at, min_taken_at, num_children, num_photos, auto_cover_id_max_privilege, auto_cover_id_least_privilege) and both foreign key constraints. Safe during Phase 1-2 (before backfill). If backfill already ran, rollback discards computed data (acceptable, regenerable). **CRITICAL:** Do NOT rollback after Phase 4 cleanup (virtual column code removed).

**Rationale:** Clean schema restoration, simple one-command rollback (`php artisan migrate:rollback`). Trade-off: data loss if backfill ran, but computed values can be regenerated. Safer than forward-only approach for early-stage deployment issues.

### 5. Dual Automatic Cover IDs with Privilege-Based Selection (Q-003-09)

Store two automatic cover IDs per album: `auto_cover_id_max_privilege` (admin/owner view, no access filters) and `auto_cover_id_least_privilege` (public view, all restrictive filters applied). Display logic selects at query time based on user permissions: admin/owner sees max-privilege cover, other users see least-privilege cover.

**Rationale:** Balances performance (pre-computation, no subqueries) with security (no private photo leakage). Simple schema (2 columns vs. per-user table or runtime filtering complexity). Guaranteed safe: least-privilege cover NEVER exposes photos invisible to restricted users. Good UX: admin/owner always sees best possible cover (may include private photos), other users see safe public cover (may be NULL if no public photos exist). At query time, selecting the appropriate cover is a simple conditional column read (no database queries).

**Trade-offs:** Double storage for cover IDs (2 columns instead of 1), recomputation job must run cover selection twice (once with filters, once without). However, this overhead is minimal compared to runtime subquery cost (eliminated) and complexity of alternatives (per-user table, runtime filtering with fallback).

## Consequences

### Positive

- **Performance:** 50%+ reduction in album list query time (eliminates 200 subqueries → 50 simple column reads)
- **Operational simplicity:** Manual backfill provides operator control, dual-read fallback enables zero-downtime deployment
- **Maintainability:** Built-in Laravel patterns (`WithoutOverlapping`, migration `down()`) reduce custom code
- **Debuggability:** Propagation stops on failure with clear error logs; manual recovery command available
- **Consistency:** Eventual consistency within 30 seconds meets user expectations (NFR-003-04)

### Negative

- **Write complexity:** Every photo/album mutation triggers job dispatch and propagation (increased write-path overhead)
- **Operational burden:** Propagation failures require monitoring/alerting and manual intervention via recovery command
- **Data loss risk:** Rollback after backfill discards computed data (mitigated: values regenerable)
- **Queue depth:** Bulk operations (50-photo upload) dispatch 50 jobs even with deduplication (49 wait, only 1 executes per album)
- **Manual step risk:** Operator may forget to run backfill command (mitigated: dual-read fallback maintains functionality)

## Alternatives Considered

### Q-003-02: Backfill Execution

- **Option B: Automatic backfill in migration** – Zero intervention, but may timeout on 100k+ albums, blocks deployment, rollback complexity
- **Option C: Automatic background job after migration** – Fast migration, but hard to track completion, race conditions if code deploys before backfill finishes

### Q-003-03: Job Deduplication

- **Option B: Debounced job dispatch with rate limiting** – Reduces job count, but complex implementation (cache management, race conditions), may miss updates
- **Option C: Batch processing with cron** – Minimal job count, but not event-driven (polling pattern), can't meet 30-second target reliably

### Q-003-05: Propagation Failure

- **Option B: Continue propagation despite failure** – Maximizes freshness (self-healing), but may propagate incorrect data, harder to debug
- **Option C: Exponential backoff with delayed retry** – Eventually consistent, but may retry indefinitely on permanent errors, hard to detect stuck jobs

### Q-003-08: Rollback Strategy

- **Option A: Forward-only with revert commits** – Safe (no data loss), but leaves schema cruft (unused columns), requires revert commit (not instant)
- **Option C: Blue-green deployment with feature flag** – Instant rollback, no data loss, but code complexity (dual implementation), increases scope significantly

### Q-003-09: Multi-User Cover Selection

- **Option A: Keep cover dynamic (do NOT pre-compute)** – Preserves security model, but still requires 1 subquery per album (partial performance gain only)
- **Option B: Store admin-perspective cover with runtime filtering** – Fast for most users, but complex dual-path implementation (fallback on restricted users), may show "no cover" when photos exist
- **Option C: Per-user computed cover (user_album_covers table)** – Perfect per-user experience, but storage explosion (N users × M albums), defeats performance goal, recomputation complexity

## Security / Privacy Impact

- **No direct security impact:** Computed fields contain same data as virtual columns (album stats, cover photo IDs). No new PII introduced.
- **Integrity protection:** Foreign key constraints (`auto_cover_id_max_privilege` and `auto_cover_id_least_privilege` REFERENCES photos.id ON DELETE SET NULL) prevent dangling references.
- **Cover selection visibility (Q-003-09):** Dual-cover approach GUARANTEES no private photo leakage:
  - `auto_cover_id_least_privilege`: Computed WITH all restrictive filters (`PhotoQueryPolicy::appendSearchabilityConditions`, `AlbumQueryPolicy::appendAccessibilityConditions`). Shown to non-admin, non-owner users. NEVER contains photos invisible to public.
  - `auto_cover_id_max_privilege`: Computed WITHOUT filters (admin view). Shown ONLY to admin/owner. May contain private photos.
  - Display logic enforces separation at query time (simple conditional, no database queries).
- **Album ownership checking:** Display logic must correctly identify album owners (user who created album or any parent album owner in tree) to determine whether to show max-privilege cover.

## Operational Impact

### Monitoring & Alerting

- **Job failures:** Alert on propagation failures (`Propagation stopped at album {album_id} due to failure`)
- **Queue depth:** Monitor job queue lag to ensure 30-second consistency window (NFR-003-04)
- **Backfill progress:** Track `Backfilled {count}/{total} albums` logs during manual backfill execution

### Maintenance

- **Manual backfill:** Operator must run `php artisan lychee:recompute-album-stats` (without album_id) after migration during maintenance window
- **Manual recovery:** Operator must run `php artisan lychee:recompute-album-stats {album_id}` for propagation failures
- **Rollback window:** Safe rollback only during Phase 1-2 (before Phase 4 cleanup removes virtual column code)

### Performance & Resource Usage

- **Read-path improvement:** 50%+ reduction in album list query time (measured via NFR-003-01: <5 seconds for 1000 photos)
- **Write-path overhead:** Job dispatch on every photo/album mutation. Queue worker resource usage increases proportionally to mutation rate.
- **Database indexes required:** `_lft`, `_rgt`, `taken_at`, `is_highlighted` (already exist for virtual columns)

### Runbooks

- **Deployment runbook:** Phase 1 (migration), Phase 2 (code deploy with dual-read), Phase 3 (manual backfill), Phase 4 (cleanup)
- **Rollback runbook:** `php artisan migrate:rollback` (safe Phase 1-2 only, NOT after Phase 4)
- **Recovery runbook:** Monitor logs for `Propagation stopped`, run `lychee:recompute-album-stats {album_id}`, verify ancestors updated

## Links

- Related spec sections: [docs/specs/4-architecture/features/003-album-computed-fields/spec.md#functional-requirements](docs/specs/4-architecture/features/003-album-computed-fields/spec.md#functional-requirements) (FR-003-01, FR-003-02, FR-003-04, FR-003-06, FR-003-07), [NFR-003-05](docs/specs/4-architecture/features/003-album-computed-fields/spec.md#non-functional-requirements), [Migration Strategy appendix](docs/specs/4-architecture/features/003-album-computed-fields/spec.md#migration-strategy), [Cover Selection Logic appendix](docs/specs/4-architecture/features/003-album-computed-fields/spec.md#cover-selection-logic)
- Related open questions (resolved): Q-003-02, Q-003-03, Q-003-05, Q-003-08, Q-003-09 in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md)
- AlbumBuilder virtual column logic: [app/Models/Builders/AlbumBuilder.php:109-174](app/Models/Builders/AlbumBuilder.php#L109-L174)
- HasAlbumThumb cover selection logic: [app/Relations/HasAlbumThumb.php:193-211, 226-229](app/Relations/HasAlbumThumb.php#L193-L211)
- Feature 002 Worker Mode (WithoutOverlapping precedent): [docs/specs/4-architecture/features/002-worker-mode/spec.md](docs/specs/4-architecture/features/002-worker-mode/spec.md)
