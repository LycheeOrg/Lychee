# Open Questions – Feature 029

Open questions for [Feature 029](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-029-01: Destination album for camera capture from root view~~ ✅ RESOLVED

**Question:** When the user takes a photo from the root albums view (not inside any album), where should the captured photo be stored?

**Resolution:** Upload with no album ID — photo lands in the "Unsorted" smart album, consistent with existing upload behaviour at root level.

**Resolved:** 2026-03-18
