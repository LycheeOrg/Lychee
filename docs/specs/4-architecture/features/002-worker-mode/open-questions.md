# Open Questions – Feature 002

Open questions for [Feature 002](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-002-01: Worker Auto-Restart Queue Priority~~ ✅ RESOLVED

**Decision:** Option A - Support multiple queue workers with priority via QUEUE_NAMES environment variable
**Rationale:** Allows time-sensitive jobs to be prioritized, standard Laravel pattern, operator flexibility.
**Updated in spec:** FR-002-02, DO-002-02, CLI-002-01, Spec DSL, Queue Connection Configuration appendix

---

### ~~Q-002-02: Worker Max-Time Configurability~~ ✅ RESOLVED

**Decision:** Option A - Configurable with sensible default via WORKER_MAX_TIME environment variable
**Rationale:** Operators can tune for their workload, no code changes needed to adjust restart interval.
**Updated in spec:** FR-002-02, DO-002-03, CLI-002-01, Spec DSL, Queue Connection Configuration appendix

---

### ~~Q-002-03: Job Deduplication for Concurrent Mutations~~ ✅ RESOLVED

**Decision:** Option A - Laravel job middleware with deduplication using WithoutOverlapping
**Rationale:** Built-in Laravel feature, prevents wasted work, automatic lock release.
**Updated in spec:** NFR-002-05, Documentation Deliverables

---

### ~~Q-002-04: Worker Healthcheck Failure Behavior~~ ✅ RESOLVED

**Decision:** Option B - Healthcheck tracks restart count, fail after 10 restarts in 5 minutes
**Rationale:** Orchestrator can restart container if worker is fundamentally broken, prevents infinite crash loops.
**Updated in spec:** FR-002-05
