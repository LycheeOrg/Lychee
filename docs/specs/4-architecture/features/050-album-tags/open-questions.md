# Open Questions – Feature 050

Open questions for [Feature 050](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-050-01~~ | 050 – Album Tags | Medium | `/tag/{id}` detail page — layout for showing tagged albums alongside tagged photos | Resolved (A – separate Albums section above Photos grid) | 2026-07-12 | 2026-07-12 |
| ~~Q-050-02~~ | 050 – Album Tags | Medium | `/tags` global list & counts — should tags used only by albums (zero photos) be listed, and how are counts split? | Resolved (A – show album-only tags, split photo/album counts) | 2026-07-12 | 2026-07-12 |
| ~~Q-050-03~~ | 050 – Album Tags | Medium | Should an album's tags be visible to viewers (non-editors, incl. guests on public albums) on the album's own page, or only to editors via the properties panel? | Resolved (A – editor-only, no public read-only display) | 2026-07-12 | 2026-07-12 |

## Question Details

### ~~Q-050-01~~ · `/tag/{id}` detail page — layout for showing tagged albums alongside tagged photos ✅ RESOLVED

**Status:** Resolved — **Option A** (separate "Albums" section above the existing photos grid, shown only when non-empty)
**Feature:** 050 – Album Tags
**Priority:** Medium
**Opened:** 2026-07-12
**Resolved:** 2026-07-12

**Resolution:** `TagPanel.vue` gains an album-tile grid (reusing the existing album-grid component from `Albums.vue`/`Search.vue`) rendered above the `PhotoThumbPanel`, visible only when the tag has ≥1 accessible album. `TagWithPhotosResource`/`GetTagWithPhotos` and `TagState.ts` are extended to carry an `albums` collection alongside `photos`.

**Spec impact:** Captured in FR-050 behaviour/UI sections and the ASCII mock-up below.

---

### ~~Q-050-02~~ · `/tags` global list & counts — should album-only tags be listed, and how are counts split? ✅ RESOLVED

**Status:** Resolved — **Option A** (show album-only tags; split counts into photo-count + album-count)
**Feature:** 050 – Album Tags
**Priority:** Medium
**Opened:** 2026-07-12
**Resolved:** 2026-07-12

**Resolution:** `ListTags`'s visibility `HAVING` clause is extended to OR-in album usage (`COUNT(photos_tags.photo_id) > 0 OR COUNT(DISTINCT albums_tags.album_id) > 0`, scoped the same way for non-admins — own photos OR own albums). `TagResource` gains `num_photos` and `num_albums` fields (replacing the single `num`); `TagsManagement.vue` displays both counts.

**Spec impact:** Captured in FR-050 requirements and DO-050 catalogue below.

---

### ~~Q-050-03~~ · Should an album's tags be visible to viewers (non-editors), or only to editors? ✅ RESOLVED

**Status:** Resolved — **Option A** (editor-only; no public read-only display in this feature)
**Feature:** 050 – Album Tags
**Priority:** Medium
**Opened:** 2026-07-12
**Resolved:** 2026-07-12

**Resolution:** `EditableBaseAlbumResource.tags` (editor-gated, per FR-050-03) remains the only surface where an album's own tags are shown/edited. `HeadAlbumResource` gains no new public `tags` field. Viewers can still discover tagged albums via `/tag/{id}` (FR-050-05) and `Search` (FR-050-06/07); they just won't see the tag list rendered inline on the album page itself unless they can edit it. A public read-only display is deferred as a possible fast-follow.

**Spec impact:** Confirms FR-050-03/NFR scope as drafted — no spec change needed beyond this record.

---

**Context (historical):** The current draft (FR-050-03) only populates `EditableBaseAlbumResource.tags`, which `HeadAlbumResource` only sends when `rights->can_edit` is true (`app/Http/Resources/Models/HeadAlbumResource.php:98`). That means someone merely viewing a shared/public album (no edit rights, including guests) would never see which tags it carries on the album page itself — tags would only be discoverable indirectly, via `/tags`, `/tag/{id}`, or Search. Whether that is the intended experience is genuinely ambiguous: the feature could be "tags are private organisational metadata for the owner/editor" or "tags are visible album metadata, like title/description, just not editable by everyone."

**Options (ordered by preference):**
- **Option A (recommended).** Keep tags editor-only for now (no change beyond FR-050-03 as drafted) — matches how `TagAlbum`'s own `tags` field already behaves (editor-only visibility today) and keeps this increment's surface area minimal; add a read-only display as a fast follow-up if wanted later. *Pros:* smallest diff, no new privacy surface to reason about (e.g., whether tags should be hidden on `is_nsfw`/protected albums). *Cons:* a viewer can't tell why an album showed up in a tag search unless they already have edit rights.
- **Option B.** Also add `tags: string[]` to `HeadAlbumResource` directly (visible to any viewer with read access to the album, same audience as title/description), rendered as a small read-only chip row near the album title. *Pros:* consistent with tags acting as visible, title-like metadata; explains why an album appears under a given tag when browsing to it directly. *Cons:* slightly larger surface (new field on the always-sent `HeadAlbumResource`, one more UI element to design/translate now rather than later).
