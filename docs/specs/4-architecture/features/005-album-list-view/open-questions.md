# Open Questions – Feature 005

Open questions for [Feature 005](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-005-01: List View Layout Structure and Information Display~~ ✅ RESOLVED

**Decision:** Option A - Windows Details View Pattern
**Rationale:** Familiar file manager pattern with horizontal row layout: `[Thumb 64px] [Album Name - Full] [X photos] [Y sub-albums]`. Scannable, information-dense, shows full untruncated album names.
**Updated in spec:** FR-005-01, FR-005-02, UI mockup section

---

### ~~Q-005-02: Toggle Control Placement and Styling~~ ✅ RESOLVED

**Decision:** Custom - AlbumHero.vue icon row (same line as statistics/download toggles)
**Rationale:** User specified placement on the same line as the statistics and download toggle buttons in AlbumHero.vue (line 33, flex-row-reverse container). Follows existing icon pattern with px-3 spacing and hover animations.
**Updated in spec:** FR-005-03, UI implementation section

---

### ~~Q-005-03: View Preference Persistence Strategy~~ ✅ RESOLVED

**Decision:** Option B - LocalStorage/session-only (no backend)
**Rationale:** Simple implementation, no backend changes needed, fast toggle response. User preference stored in browser localStorage per-device.
**Updated in spec:** FR-005-04, NFR-005-01
