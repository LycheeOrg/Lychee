# Open Questions – Feature 011

Open questions for [Feature 011](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-011-01: Config Key Naming for My Best Pictures Count~~ ✅ RESOLVED

**Decision:** Option A - Separate config key `my_best_pictures_count`
**Rationale:** Allows independent configuration. Users might want different counts for overall best pictures vs personal favorites. Clearer semantics with each album having its own setting.
**Updated in spec:** CFG-011-03, DO-011-02 implementation

---

### ~~Q-011-02: Default Sort Order for My Rated Pictures Album~~ ✅ RESOLVED

**Decision:** Option A - Sort by rating DESC, then by created_at DESC
**Rationale:** Shows highest-rated photos first, consistent with "favorites" concept. Most intuitive for users wanting to see their best-rated photos at the top.
**Updated in spec:** FR-011-01, query implementation details
