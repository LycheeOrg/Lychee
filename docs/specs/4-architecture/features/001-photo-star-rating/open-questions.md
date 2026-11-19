# Open Questions – Feature 001

Open questions for [Feature 001](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q001-01: Full-size Photo Overlay Positioning~~ ✅ RESOLVED

**Decision:** Option A - Bottom-center
**Rationale:** Centered position is more discoverable and doesn't compete with Dock buttons. Symmetrical with metadata overlay below.
**Updated in spec:** FR-001-10, UI mockup section 2, implementation plan I9c/I9d

---

### ~~Q001-02: Auto-hide Timer Duration~~ ✅ RESOLVED

**Decision:** Option A - 3 seconds
**Rationale:** Standard UX pattern, balanced duration (not too fast, not too slow).
**Updated in spec:** FR-001-10, UI mockup section 2, implementation plan I9c

---

### ~~Q001-03: Rating Removal Button Placement~~ ✅ RESOLVED

**Decision:** Option A - Inline [0] button
**Rationale:** Consistent button pattern, simple implementation, shown as "×" or "Remove" for clarity.
**Updated in spec:** FR-001-09, UI mockup section 1, implementation plan I9a

---

### ~~Q001-04: Overlay Visibility on Mobile Devices~~ ✅ RESOLVED

**Decision:** Option A - Details drawer only on mobile
**Rationale:** Follows existing Lychee pattern (overlays are desktop-only), simple and consistent experience.
**Updated in spec:** FR-001-09, FR-001-10, UI mockup sections 1-2, implementation plan I9a/I9c

---

### ~~Q001-05: Authorization Model for Rating~~ ✅ RESOLVED

**Decision:** Option B - Read access (anyone who can view can rate)
**Rationale:** Follows standard rating system patterns. Rating is a lightweight engagement action similar to favoriting, not a privileged edit operation. Makes ratings more accessible and useful.
**Updated in spec:** FR-001-01, NFR-001-04

---

### ~~Q001-06: Rating Removal HTTP Status Code~~ ✅ RESOLVED

**Decision:** 200 OK (idempotent behavior)
**Rationale:** Removing a non-existent rating is a no-op and should return success (200 OK) rather than 404 error. This makes the endpoint idempotent and simpler to use.
**Updated in spec:** FR-001-02

---

### ~~Q001-07: Statistics Record Creation Strategy~~ ✅ RESOLVED

**Decision:** Option A - firstOrCreate in transaction
**Rationale:** Atomic operation with no race conditions, Laravel handles duplicate creation attempts automatically, simple implementation.
**Updated in spec:** Implementation plan I5

---

### ~~Q001-08: Transaction Rollback Error Handling~~ ✅ RESOLVED

**Decision:** Option B - 409 Conflict for transaction errors
**Rationale:** More semantic HTTP status, indicates temporary issue that suggests retry, clearer to frontend.
**Updated in spec:** Implementation plan I5, I10

---

### ~~Q001-09: N+1 Query Performance for user_rating~~ ✅ RESOLVED

**Decision:** Option A - Eager load with closure in controller
**Rationale:** Standard Laravel pattern, single additional query for all photos, no global scope side effects.
**Updated in spec:** Implementation plan I6

---

### ~~Q001-10: Concurrent Update Debouncing (Rapid Clicks)~~ ✅ RESOLVED

**Decision:** Option A - Disable stars during API call
**Rationale:** Simple implementation, prevents concurrent requests, clear visual feedback with loading state.
**Updated in spec:** Implementation plan I8, I9a, I9c

---

### ~~Q001-11: Metrics Disabled Behavior (Can Still Rate?)~~ ✅ RESOLVED

**Decision:** Option C - Admin setting controls independently
**Rationale:** Granular control allows enabling rating without showing aggregates, future-proof configuration.
**Updated in spec:** New config setting needed (separate `ratings_enabled` from `metrics_enabled`)

---

### ~~Q001-12: Rating Display When Metrics Disabled~~ ✅ RESOLVED

**Decision:** Option B - Hide all rating data when metrics disabled
**Rationale:** Fully consistent with metrics disabled setting, simplest implementation, respects admin preference.
**Updated in spec:** UI components conditional rendering

---

### ~~Q001-13: Half-Star Display for Fractional Averages~~ ✅ RESOLVED

**Decision:** Option B - Half-star display using PrimeVue icons
**Rationale:** PrimeVue provides pi-star, pi-star-fill, pi-star-half, pi-star-half-fill icons. More precise visual representation, common rating pattern.
**Updated in spec:** UI mockups, component implementation uses PrimeVue star icons

---

### ~~Q001-14: Overlay Persistence on Active Interaction~~ ✅ RESOLVED

**Decision:** Option A - Persist while loading, then restart auto-hide timer
**Rationale:** User sees confirmation (success toast + updated rating), natural interaction flow.
**Updated in spec:** Implementation plan I9c, PhotoRatingOverlay behavior

---

### ~~Q001-15: Rating Tooltip/Label Clarity~~ ✅ RESOLVED

**Decision:** Option C - No labels/tooltips (stars are self-evident)
**Rationale:** Cleanest UI, stars are universal rating symbol, keeps overlays compact.
**Updated in spec:** UI components (no tooltip implementation needed)

---

### ~~Q001-16: Accessibility (Keyboard Navigation, ARIA)~~ ✅ RESOLVED

**Decision:** Option C - Defer to post-MVP
**Rationale:** Ship faster with basic implementation, gather user feedback first, can enhance accessibility later.
**Updated in spec:** Out of scope (deferred enhancement)

---

### ~~Q001-17: Optimistic UI Updates vs Server Confirmation~~ ✅ RESOLVED

**Decision:** Option A - Wait for server confirmation
**Rationale:** Always shows accurate server state, clear error handling, no phantom updates.
**Updated in spec:** Implementation plan I8, I9a, I9c (loading state pattern)

---

### ~~Q001-18: Rating Count Threshold for Display~~ ✅ RESOLVED

**Decision:** Option A - Always show rating, regardless of count
**Rationale:** Transparent, simpler logic, users can judge significance from count displayed.
**Updated in spec:** UI components (no threshold logic needed)

---

### ~~Q001-19: Telemetry Event Granularity~~ ✅ RESOLVED

**Decision:** No telemetry events / analytics
**Rationale:** Feature does not include telemetry or analytics tracking.
**Updated in spec:** Remove telemetry events from FR-001-01, FR-001-02, FR-001-03

---

### ~~Q001-20: Rating Analytics/Trending Features~~ ✅ RESOLVED

**Decision:** Option B - Implement minimally for current scope
**Rationale:** Follows YAGNI principle, simpler initial implementation, faster to ship.
**Updated in spec:** Out of scope (no future analytics preparation)

---

### ~~Q001-21: Album Aggregate Rating Display~~ ✅ RESOLVED

**Decision:** Option A - Defer to future feature
**Rationale:** Keeps current feature focused, can design properly later with user feedback on photo ratings.
**Updated in spec:** Out of scope, potential future Feature 00X

---

### ~~Q001-22: Rating Export in Photo Backup~~ ✅ RESOLVED

**Decision:** Option C - No export (ratings are ephemeral/server-side only)
**Rationale:** Simpler export logic, smaller export files.
**Updated in spec:** Out of scope (no export functionality)

---

### ~~Q001-23: Rating Notification to Photo Owner~~ ✅ RESOLVED

**Decision:** Option A - Defer to future feature (notifications system)
**Rationale:** Keeps feature scope focused, requires notifications infrastructure that may not exist yet.
**Updated in spec:** Out of scope (deferred to future notifications feature)

---

### ~~Q001-24: Statistics Recalculation Artisan Command~~ ✅ RESOLVED

**Decision:** Option B - No command, rely on transaction integrity
**Rationale:** Trust atomic transactions to maintain consistency, simpler implementation.
**Updated in spec:** Out of scope (no artisan command)

---

### ~~Q001-25: Migration Strategy for Existing Installations~~ ✅ RESOLVED

**Decision:** Option A - Migration adds columns with defaults, no backfill
**Rationale:** Clean state (accurate: no ratings yet), fast migration, no assumptions about historical data.
**Updated in spec:** Implementation plan I1 (migrations with default values)
