# Open Questions – Feature 007

Open questions for [Feature 007](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-007-01: Pagination Strategy (Offset vs Cursor) and Page Size Configuration~~ ✅ RESOLVED

**Decision:** Option A - Offset-based pagination with config table page size
**Rationale:** Simple Laravel pagination pattern with standard LIMIT/OFFSET, easy navigation to specific pages, admin-configurable page sizes via config table. Performance acceptable for expected album sizes.
**Updated in spec:** FR-007-01 through FR-007-06, NFR-007-01, NFR-007-05, DO-007-01

---

### ~~Q-007-02: API Endpoint Design (New Endpoints vs Modify Existing)~~ ✅ RESOLVED

**Decision:** Option B - New paginated endpoints (`/Album/{id}/head`, `/Album/{id}/albums`, `/Album/{id}/photos`)
**Rationale:** Clear separation of concerns, existing `/Album` endpoint unchanged for backward compatibility (avoiding test changes), consistent response structure per endpoint. Code duplication acceptable to minimize refactoring risk.
**Updated in spec:** FR-007-01, FR-007-02, FR-007-03, FR-007-12, NFR-007-04, NFR-007-06, API-007-01 through API-007-05

---

### ~~Q-007-03: Frontend Loading Strategy (Load-More vs Page Navigation)~~ ✅ RESOLVED

**Decision:** Configurable with infinite scroll as default
**Rationale:** User specified configurable UI modes: "infinite_scroll" (default), "load_more_button", "page_navigation". Infinite scroll provides smoothest UX for photo galleries. First page always loaded automatically, subsequent pages on demand based on UI mode.
**Updated in spec:** FR-007-07, FR-007-08, FR-007-09, FR-007-10, DO-007-02, UI mockups

---

### ~~Q-007-04: Config Key Naming and Default Values~~ ✅ RESOLVED

**Decision:** Option C - Multiple granular configs
**Rationale:** User specified: `albums_per_page` (default 30), `photos_per_page` (default 100), Flexible tuning for different resource types with appropriate defaults based on typical usage patterns.
**Updated in spec:** FR-007-06, NFR-007-05, DO-007-01

---

### ~~Q-007-05: Refactoring Scope (Extract Album/Photo Fetching Logic)~~ ✅ RESOLVED

**Decision:** Option B - Repository pattern methods, code duplication acceptable
**Rationale:** User directive to avoid extensive refactoring, prioritize backward compatibility and minimal test changes. New endpoints can duplicate logic from existing implementation. Repository pattern methods for data access without extracting to separate service classes.
**Updated in spec:** NFR-007-06, Goals section, Non-Goals section

---

### ~~Q-007-06: Backward Compatibility Strategy for Existing Clients~~ ✅ RESOLVED

**Decision:** New endpoints default page=1, existing `/Album` endpoint unchanged
**Rationale:** User specified creating new endpoints only. Legacy `/Album?album_id=X` endpoint remains unchanged returning full data. New endpoints (`/Album/{id}/albums`, `/Album/{id}/photos`) default to page 1 if `?page=` parameter absent (not "return all").
**Updated in spec:** FR-007-11, FR-007-12, API-007-02, API-007-03, API-007-04
