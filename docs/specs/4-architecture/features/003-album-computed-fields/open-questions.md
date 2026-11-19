# Open Questions – Feature 003

Open questions for [Feature 003](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-003-01: Recomputation Job Queue Priority~~ ✅ RESOLVED

**Decision:** Option A - Use default queue, rely on worker scaling
**Rationale:** Simpler configuration, standard Laravel pattern, natural backpressure signaling. Operators scale worker count to meet 30-second consistency target.
**Updated in spec:** FR-003-02, JOB-003-01

---

### ~~Q-003-02: Backfill Execution Strategy During Migration~~ ✅ RESOLVED

**Decision:** Option A - Manual trigger after migration (with `lychee:` prefix requirement)
**Rationale:** Operator controls timing during maintenance window, migration completes quickly, aligns with dual-read fallback pattern. All Lychee commands use `lychee:` namespace.
**Updated in spec:** FR-003-06, CLI-003-01, Migration Strategy appendix
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-03: Concurrent Album Mutation Deduplication~~ ✅ RESOLVED

**Decision:** Option A - Laravel WithoutOverlapping middleware
**Rationale:** Built-in Laravel feature (same as Feature 002 Q-002-03), prevents wasted work, automatic lock release, simple implementation.
**Updated in spec:** FR-003-02, JOB-003-01
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-04: Cover Selection Race Condition Handling~~ ✅ RESOLVED

**Decision:** Option A - Foreign key ON DELETE SET NULL (already in spec)
**Rationale:** Database handles automatically, simple, eventual consistency. Photo deletion events trigger recomputation for parent albums.
**Updated in spec:** FR-003-02 (added photo deletion event trigger), Migration Strategy appendix (FK constraint confirmed)

---

### ~~Q-003-05: Propagation Chain Failure Handling~~ ✅ RESOLVED

**Decision:** Option A - Stop propagation, log error, manual recovery
**Rationale:** Prevents cascading errors, clear failure boundary, operator can investigate root cause before retrying via `lychee:recompute-album-stats`.
**Updated in spec:** FR-003-02, CLI-003-02
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-06: Soft-Deleted Photo Exclusion from Computations~~ ✅ RESOLVED

**Decision:** N/A - Lychee does not use soft deletes
**Rationale:** Per user clarification, Lychee does not implement soft delete pattern for photos. Hard deletes only.
**Updated in spec:** FR-003-02 (removed soft-delete references)

---

### ~~Q-003-07: NULL taken_at Handling in Min/Max Calculations~~ ✅ RESOLVED

**Decision:** Option A - Ignore NULL taken_at, use SQL MIN/MAX directly
**Rationale:** Mirrors existing AlbumBuilder.php behavior (lines 111, 125). SQL MIN/MAX ignores NULLs by default. Semantically correct (taken_at unknown = exclude from range).
**Updated in spec:** FR-003-02 validation path

---

### ~~Q-003-08: Migration Rollback Strategy for Multi-Phase Deployment~~ ✅ RESOLVED

**Decision:** Option B - Full rollback with down() migration
**Rationale:** Clean schema restoration, simple one-command rollback. Trade-off: data loss if backfill ran, but values can be regenerated. Critical constraint: do NOT rollback after Phase 4 cleanup.
**Updated in spec:** FR-003-06, Migration Strategy appendix (new Rollback Strategy section)
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-09: Multi-user Cover Selection Strategy for computed_cover_id~~ ✅ RESOLVED

**Decision:** Option D - Store dual cover IDs with privilege-based selection (`auto_cover_id_max_privilege` and `auto_cover_id_least_privilege`)
**Rationale:** Balances performance (pre-computation) with security (no photo leakage). Two cover IDs stored per album: one for admin/owner view (max privilege), one for public view (least privilege). Display logic selects appropriate cover based on user permissions at query time (simple column read, no subquery). Simple schema (2 columns vs. per-user table), guaranteed safe (least-privilege cover never leaks private photos), good UX (admin/owner sees best possible cover).
**Updated in spec:** FR-003-01, FR-003-02, FR-003-04, FR-003-07, NFR-003-05, DO-003-03, DO-003-04, Migration Strategy, Cover Selection Logic appendix
**ADR:** ADR-0003-album-computed-fields-precomputation.md (to be updated with Q-003-09 resolution)
