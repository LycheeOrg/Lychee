# Open Questions – Feature 006

Open questions for [Feature 006](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-006-01: Filter UI Control Design and Interaction Pattern~~ ✅ RESOLVED

**Decision:** Option D - Hover star list with minimum threshold filtering and toggle-off
**Rationale:** User specified custom interaction: Display 5 hoverable stars. Empty stars = no filtering. Click on star N = show photos with rating ≥ N (minimum threshold). Click same star again = remove filtering. Combines visual clarity of inline stars with flexible threshold filtering.
**Updated in spec:** FR-006-01, FR-006-02, FR-006-03, UI mockup section

---

### ~~Q-006-02: Filter Behavior for Unrated Photos~~ ✅ RESOLVED

**Decision:** Addressed by Q-006-01 decision
**Rationale:** Minimum threshold filtering (≥ N stars) inherently excludes unrated photos (which have no rating value). Empty stars (no filter) shows all photos including unrated.
**Updated in spec:** FR-006-02, filtering logic section

---

### ~~Q-006-03: Filter State Persistence Strategy~~ ✅ RESOLVED

**Decision:** Custom - State store persistence (like NSFW visibility)
**Rationale:** User specified to keep selection in state store, similar to existing NSFW visibility pattern. State persists during session but managed by Pinia store, not localStorage (follows existing Lychee patterns for view state).
**Updated in spec:** FR-006-04, NFR-006-01

---

### ~~Q-006-04: Multi-Rating Filter Support (AND vs OR)~~ ✅ RESOLVED

**Decision:** Option C - Range filter (minimum threshold) as explained in Q-006-01
**Rationale:** User clarified in Q-006-01 that clicking star N shows photos with rating ≥ N (3+ stars shows 3, 4, 5 star photos). Simple single-selection UI with flexible filtering capability.
**Updated in spec:** FR-006-01, FR-006-02, filtering algorithm section
