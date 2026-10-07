# Open Questions – Feature 046

Open questions for [Feature 046](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-046-01~~ | 046 – Tag Album Cover | High | Should `cover_id` move from `albums` to `base_albums`, or be added only to `tag_albums`? | Resolved (B – add to `tag_albums` only) | 2026-06-28 | 2026-06-28 |
| ~~Q-046-02~~ | 046 – Tag Album Cover | Medium | Front-end guard: replace `is_model_album` with `has_cover_support` flag, or widen check to include tag albums? | Resolved (B – check `is_model_album \|\| tagAlbum` in context menu) | 2026-06-28 | 2026-06-28 |
| ~~Q-046-03~~ | 046 – Tag Album Cover | Medium | Should `cover()` relationship and eager-loading live on `BaseAlbumImpl` or remain per-model? | Resolved (N/A – per-model with eager-load on TagAlbum) | 2026-06-28 | 2026-06-28 |

## Question Details

### ~~Q-046-01~~ · Move cover_id to base_albums vs add to tag_albums only ✅ RESOLVED

**Status:** Resolved — **Option B** (add to `tag_albums` only)
**Feature:** F-046 – Tag Album Custom Cover
**Priority:** High
**Opened:** 2026-06-28
**Resolved:** 2026-06-28

**Resolution:** Option B chosen. `cover_id` is added to `tag_albums` only; `albums.cover_id` remains untouched. This avoids touching `HasAlbumThumb` which is tightly coupled to `Album` with its precomputed cover fields (`auto_cover_id_max_privilege`, `auto_cover_id_least_privilege`). Each model manages its own `cover()` relationship independently. Encoded in FR-046-01, FR-046-02.

---

### ~~Q-046-02~~ · Front-end guard mechanism for cover support ✅ RESOLVED

**Status:** Resolved — **Option B** (check `is_model_album || tagAlbum` in context menu)
**Feature:** F-046 – Tag Album Custom Cover
**Priority:** Medium
**Opened:** 2026-06-28
**Resolved:** 2026-06-28

**Resolution:** Option B chosen. No new `AlbumConfig` flag. The context menu guard block at `contextMenu.ts:126–148` is split: "Set as cover" uses `is_model_album || albumStore.tagAlbum !== undefined`; "Set as header" keeps the `is_model_album`-only guard. Encoded in FR-046-07, UI-046-03.

---

### ~~Q-046-03~~ · cover() relationship location and eager-loading strategy ✅ RESOLVED

**Status:** Resolved — N/A (per-model with eager-load on TagAlbum)
**Feature:** F-046 – Tag Album Custom Cover
**Priority:** Medium
**Opened:** 2026-06-28
**Resolved:** 2026-06-28

**Resolution:** Question no longer applies since `cover_id` stays per-model (Q-046-01 → B). `TagAlbum` defines its own `cover()` HasOne relationship and eager-loads it via `$with`. `Album` is unchanged. Encoded in FR-046-02, NFR-046-04.
