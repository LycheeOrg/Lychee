# Open Questions – Feature 034

Open questions for [Feature 034](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-034-01: TagAlbum Rows in Bulk Edit List~~ ✅ RESOLVED

**Feature:** 034 – Bulk Album Edit  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-12  
**Resolved:** 2026-04-14

**Resolution:** **Option A** — Show only regular `Album` records (no TagAlbums). The list query joins only the `albums` table. A note on the page explains TagAlbums are excluded.

**Spec Impact:** FR-034-01 clarified; plan and tasks updated to confirm only `albums` table is queried.

---

### ~~Q-034-02: Depth Indicator Computation Strategy~~ ✅ RESOLVED

**Feature:** 034 – Bulk Album Edit  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-12  
**Resolved:** 2026-04-14

**Resolution:** **Option B** — Compute depth client-side, **linearly**, by scanning `_lft` values in descending order. The server returns `_lft` in each `BulkAlbumResource` row (already included). The frontend performs a single O(n) pass over the sorted-by-`_lft` result set: maintain a stack of ancestor `_rgt` values; pop the stack whenever the current row's `_lft` exceeds the stack top's `_rgt`; depth = stack length. This avoids an extra server-side `withDepth()` join and keeps computation in the client where the full page of records is already available.

**Spec Impact:** `BulkAlbumResource` includes `_lft` and `_rgt` (not `depth`). FR-034-14 updated to specify client-side linear depth computation. T-034-03 and T-034-17 updated accordingly.

---

### ~~Q-034-03: Confirmation for Bulk Delete~~ ✅ RESOLVED

**Feature:** 034 – Bulk Album Edit  
**Priority:** High  
**Status:** Resolved  
**Opened:** 2026-04-12  
**Resolved:** 2026-04-14

**Resolution:** **Option B** — Bulk delete shows a minimal confirmation modal: a dialog displaying the count of selected albums and requiring the admin to click a second **"Confirm Delete"** button. No text input required. This is consistent with the spirit of the no-confirmation rule (field edits apply immediately) while protecting against accidental mass-delete.

**Spec Impact:** FR-034-10 updated to require confirmation modal for delete. UI-034-05 (delete confirmation dialog state) added. T-034-20 updated.

---

### ~~Q-034-04: Scope of "Select All Matching"~~ ✅ RESOLVED

**Feature:** 034 – Bulk Album Edit  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-12  
**Resolved:** 2026-04-14

**Resolution:** **Option A** — Return all albums in the gallery regardless of owner. Admin page; admin has authority over all albums.

**Spec Impact:** FR-034-12 confirmed: no owner filter applied on `GET /BulkAlbumEdit::ids`.
