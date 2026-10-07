# Open Questions – Feature 019

Open questions for [Feature 019](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-019-01: Hierarchical vs Flat Slugs~~ ✅ RESOLVED

**Decision:** Option A — Flat globally-unique slugs
**Rationale:** Simpler implementation with a single `slug` column and unique index on `base_albums`. No dependency on parent album structure — renaming/moving a parent doesn't invalidate child slugs. Easier to reason about uniqueness and collisions.
**Updated in spec:** FR-019-01 (slug on `base_albums`), FR-019-03 (global uniqueness), Non-Goals (hierarchical paths explicitly excluded)

---

### ~~Q-019-02: Top-Level Route Support~~ ✅ RESOLVED

**Decision:** Option A — Gallery-prefixed only (`/gallery/{slug}`)
**Rationale:** No collision risk with existing routes (`/settings`, `/profile`, `/login`, etc.). No changes to web route definitions — slug resolution happens inside the existing `{albumId}` parameter. Simpler, safer, ships faster.
**Updated in spec:** FR-019-05 (resolution within existing route), FR-019-10 (Vue Router `/gallery/{slug}`), Non-Goals (top-level routes excluded)

---

### ~~Q-019-03: Tag Album Slug Support~~ ✅ RESOLVED

**Decision:** Option A — Both Album and TagAlbum (via shared `base_albums` table)
**Rationale:** The `slug` column lives on `base_albums`, which is shared by both Album and TagAlbum. Consistent behaviour — any album-like entity can have a friendly URL. No special-casing needed in the factory or validation.
**Updated in spec:** FR-019-01 (column on `base_albums`), FR-019-03 (uniqueness across Album + TagAlbum), S-019-14 (tag album scenario)
