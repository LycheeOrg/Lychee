# Open Questions – Feature 026

Open questions for [Feature 026](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-026-01: TagAlbum and Smart Album Support Scope~~ ✅ RESOLVED

**Question:** Should TagAlbums and Smart Albums support tag filtering in the future, or is "only regular Albums" a permanent architectural decision?

**Resolution:** Tag filtering applies to **all album types** (regular Albums, TagAlbums, and Smart Albums) in v1.

**Rationale:** User specified "This is for all albums: regular, tags, smart." The feature should provide consistent filtering UX across all album types.

**Spec Impact:** Remove "Filtering TagAlbums or Smart Albums" from Non-Goals; update FR-026-01 to clarify support for all album types; add test scenarios for TagAlbum and SmartAlbum filtering.

**Resolved:** 2026-03-09

---

### ~~Q-026-02: Large Tag List UX Strategy (100+ Tags)~~ ✅ RESOLVED

**Question:** How should the tag filter UI handle albums with 100+ unique tags (beyond the spec's "up to 20 unique tags" performance target)?

**Resolution:** **Option B** - Add search/filter to tag dropdown in v1 (enable PrimeVue MultiSelect `filter` prop).

**Rationale:** PrimeVue MultiSelect has built-in filter capability; minimal implementation effort for better UX.

**Spec Impact:** Update NFR-026-02 (Usability) to note that tag dropdown includes search/filter for large tag lists.

**Resolved:** 2026-03-09

---

### ~~Q-026-03: URL-based Filter State Representation~~ ✅ RESOLVED

**Question:** Should the active tag filter be represented in the URL query string (e.g., `/gallery/album-id?tag_ids=1,2&tag_logic=OR`) to enable bookmarking and sharing, or should it remain in component state only?

**Resolution:** **Option A** - Component state only; no URL representation in v1.

**Rationale:** Simpler implementation for v1. Filter state stored in component `ref()` without Vue Router query param synchronization. Users cannot bookmark/share filtered views (accepted limitation).

**Spec Impact:** Non-Goals already documents this; no change needed.

**Resolved:** 2026-03-09

---

### ~~Q-026-04: Album::tags Security Filtering Approach~~ ✅ RESOLVED

**Question:** For the `Album::tags` endpoint, should it apply per-photo security filters when fetching tags (e.g., only include tags from public photos when viewing as guest), or rely solely on album-level access check?

**Resolution:** **Album-level access only** (Option A). Album::tags returns tags from photos directly attached to that album. Album-level access rights determine which photos are accessible, and thus which tags should be returned.

**Rationale:** User clarified: "Album::tags should return the list of tags which are associated to the photos directly attached to that album. The access rights on the album_id determine directly what photos are accessible, thus which tags should be returned."

**Spec Impact:** Clarify FR-026-01 to explicitly state album-level access model; no per-photo filtering required.

**Resolved:** 2026-03-09

---

### ~~Q-026-05: Behavior When All Tag IDs Are Invalid~~ ✅ RESOLVED

**Question:** When a user provides tag IDs via `tag_ids[]` parameter and ALL of them are invalid (don't exist in database), should the endpoint return all photos (treating invalid IDs as "no filter") or an empty result?

**Resolution:** **Option C** - Return validation error (422 Unprocessable Entity) when all tag IDs are invalid.

**Rationale:** Clear feedback to client that the request was invalid. Individual invalid IDs are still silently ignored, but if the entire filter set is invalid, return error.

**Spec Impact:** Update FR-026-02 to clarify: "Invalid tag IDs individually ignored; if ALL provided tag IDs are invalid, return 422 validation error."

**Resolved:** 2026-03-09
