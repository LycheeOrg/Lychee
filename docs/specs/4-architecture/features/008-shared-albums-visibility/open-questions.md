# Open Questions – Feature 008

Open questions for [Feature 008](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-008-01: User Preference Storage Location~~ ✅ RESOLVED

**Decision:** Option A - New column in users table
**Rationale:** Follows existing Lychee pattern (user attributes in users table), simple implementation with single query, no new tables needed.
**Updated in spec:** FR-008-02, COL-008-01, migration strategy

---

### ~~Q-008-02: Smart Albums in Tabbed View~~ ✅ RESOLVED

**Decision:** Option D - Show above tabs (outside tab context)
**Rationale:** Smart albums span all content (photos from both owned and shared albums), so they should be displayed above the tab bar and remain always visible regardless of selected tab.
**Updated in spec:** UI mockups, FR-008-06, FR-008-07

---

### ~~Q-008-03: Tab Visibility When Empty~~ ✅ RESOLVED

**Decision:** Option A - Hide empty tabs
**Rationale:** Cleaner UX - if "Shared with Me" has no albums, don't show tab bar at all (behave like SHOW mode). Simpler for users with no shared albums.
**Updated in spec:** S-008-08, UI-008-02

---
