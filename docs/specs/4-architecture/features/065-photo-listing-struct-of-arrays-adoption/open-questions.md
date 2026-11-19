# Open Questions – Feature 065

Open questions for [Feature 065](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-065-05~~ | 065 – Photo Listing Struct-of-Arrays Frontend Adoption | High | Should "is the SoA photo path active for this view" be one centralized flag consulted by the fetch dispatcher, render dispatcher, on-demand `details` fetch, and drag-select — with the `details` fetch explicitly skipped when false — or independently re-derived/tolerated at each call site? | Resolved (Option A — one centralized `isPhotoSoaActive` getter on `AlbumState.ts`; user: "Q-065-5: A") | 2026-09-06 | 2026-09-06 |
| ~~Q-065-06~~ | 065 – Photo Listing Struct-of-Arrays Frontend Adoption | Medium | `AdaptedPhotoTile` (tier 2-derived) lacks `face_count` (hover face-prefetch) and file size (list-mode metadata row) — both read by the v2 tiles this feature forks. Accept as regressions (mirroring NG11's blur-up precedent), or request a Feature 064 amendment adding one or both fields to `ratios`? | Resolved (Option A — both accepted as documented regressions, added to NG11; user: "Q-065-6: A") | 2026-09-06 | 2026-09-06 |

## Question Details

### ~~Q-065-05~~ · Centralized "SoA path active" flag + on-demand `details` fetch gating ✅ RESOLVED

**Status:** Resolved — **Option A**  
**Feature:** 065 – Photo Listing Struct-of-Arrays Frontend Adoption  
**Resolved:** 2026-09-06

**Resolution:** User confirmed Option A ("Q-065-5: A"). `AlbumState.ts` gains one centralized, computed `isPhotoSoaActive` getter (`is_struct_of_array_enabled` AND regular-`Album` parent AND no active tag/person filter), read by every SoA-photo-path consumer — the fetch dispatcher (`loadPhotosAuto()`), the render dispatcher, the on-demand `details` fetch (lightbox open, edit-dialog open), and drag-select — instead of each re-deriving the same condition independently. `loadPhotoDetails()` is now explicitly gated: when `isPhotoSoaActive` is false, the lightbox/dialog reads directly from the already-loaded, already-complete v2 `PhotoResource` object instead of calling `loadPhotoDetails()` at all — eliminating both the 404 risk (Feature 064's endpoints 404 for non-`Album` parents) and the wasted round trip (tag/person-filtered views, where v2 already has full data). Encoded in FR-065-02/09/13/14/16, G1, and a new Appendix Decision Card (Q-065-05) in `spec.md` itself.

---

### ~~Q-065-06~~ · `AdaptedPhotoTile` lacks `face_count` / file-size fields the forked v2 tiles read ✅ RESOLVED

**Status:** Resolved — **Option A**  
**Feature:** 065 – Photo Listing Struct-of-Arrays Frontend Adoption  
**Resolved:** 2026-09-06

**Resolution:** User confirmed Option A ("Q-065-6: A"). Both gaps are accepted, documented regressions on the SoA virtualized path, added to NG11 alongside the existing blur-up-placeholder regression: (1) hover-triggered face-recognition prefetch becomes a no-op, since `face_count` exists only in the bounded `details` tier, not `ratios`, and fetching `details` eagerly per rendered tile would defeat G5's on-demand-only discipline; (2) `PhotoListItemVirtual.vue`'s metadata row omits the file-size chip, for the same reason. No Feature 064 amendment is requested. Encoded in NG11, FR-065-04, FR-065-10.
