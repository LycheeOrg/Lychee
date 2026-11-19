# Open Questions – Feature 004

Open questions for [Feature 004](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-004-01: Recomputation Trigger Strategy for Size Statistics~~ ✅ RESOLVED

**Decision:** Option B - Separate `RecomputeAlbumSizeJob` triggered independently, using Skip middleware with cache-based job tracking (same pattern as Feature 003's `RecomputeAlbumStatsJob`)
**Rationale:** Decoupled from Feature 003, can optimize independently, reuses proven Skip middleware pattern from [RecomputeAlbumStatsJob.php](app/Jobs/RecomputeAlbumStatsJob.php:76-93) with cache key `album_size_latest_job:{album_id}` and unique job IDs for deduplication.
**Updated in spec:** FR-004-02, JOB-004-01, middleware implementation details

---

### ~~Q-004-02: Migration/Backfill Strategy for Existing Albums~~ ✅ RESOLVED

**Decision:** Option A - Separate artisan command, manual execution, PLUS maintenance UI button for operators
**Rationale:** Operator controls timing during maintenance window, fast migration (schema only), progress monitoring. Admin UI button provides convenient trigger for backfill without CLI access.
**Updated in spec:** FR-004-04, CLI-004-01, maintenance UI addition

---

### ~~Q-004-03: Job Deduplication Approach for Concurrent Updates~~ ✅ RESOLVED

**Decision:** Option D (Custom) - Use Skip middleware with cache-based job tracking (same pattern as Feature 003)
**Rationale:** Reuses proven pattern from [RecomputeAlbumStatsJob.php](app/Jobs/RecomputeAlbumStatsJob.php): Each job gets unique ID, latest job ID stored in cache with key `album_size_latest_job:{album_id}`, `Skip::when()` middleware checks if newer job queued. Simpler than `WithoutOverlapping`, guarantees most recent update eventually processes.
**Updated in spec:** FR-004-02, JOB-004-01
