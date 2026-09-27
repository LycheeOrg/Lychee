# Open Questions – Feature 009

Open questions for [Feature 009](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-009-01: Average Rating Storage Strategy~~ ✅ RESOLVED

**Decision:** Option B - Add denormalized rating_avg column to photos table
**Rationale:** Fast indexed sorting with simple ORDER BY. Application logic will keep it in sync when ratings are updated (same transaction as rating_sum/rating_count updates).
**Updated in spec:** FR-009-01, DO-009-01, migration strategy

---

### ~~Q-009-02: Rating Smart Album Threshold Logic~~ ✅ RESOLVED

**Decision:** Option C - Hybrid (threshold for 3★+, exact for 1★-2★)
**Rationale:** Matches user's explicit statement that "3_stars album will contain all photos rated 3 stars or above." Low ratings (1★, 2★) use exact buckets so photos only appear in one album; high ratings (3★+) use threshold for cumulative view.
**Updated in spec:** FR-009-03 through FR-009-08, smart album filtering logic

---

### ~~Q-009-03: Best Pictures Cutoff Behavior~~ ✅ RESOLVED

**Decision:** Option B - Top N by rating, include ties
**Rationale:** Fair behavior that doesn't arbitrarily exclude photos with the same rating as the Nth photo. May show more than N photos if ties exist, but ensures no photo is unfairly excluded.
**Updated in spec:** FR-009-09, Best Pictures smart album logic

---

### ~~Q-009-04: Smart Album Sorting Default~~ ✅ RESOLVED

**Decision:** Custom - Rating smart albums and Best Pictures sorted by rating DESC
**Rationale:** Shows highest-rated photos first, which is the natural expectation for rating-based albums.
**Updated in spec:** FR-009-10, NFR-009-03

---

### ~~Q-009-06: NULLS LAST Cross-Database Strategy~~ ✅ RESOLVED

**Decision:** Simple indexed ORDER BY with COALESCE pattern for fastest performance
**Rationale:** User specified "fastest ordering possible with indexing." Using `COALESCE(rating_avg, -1) DESC` allows the query to use the index on `rating_avg` efficiently across all databases. Since ratings are always positive (1-5), -1 as sentinel value is safe and pushes NULLs to the end.
**Updated in spec:** FR-009-02, sorting strategy, SortingDecorator implementation
