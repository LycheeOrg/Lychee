# Open Questions – Feature 057

Open questions for [Feature 057](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-057-06~~ | 057 – Album Listing v3 | High | Guest/user listing must only contain albums reachable by clicking — reuse `applyBrowsabilityFilter()` as-is (drops still-locked albums) vs. a new ancestor-only variant (keeps still-locked albums listed) | Resolved (A — visibility + `applyAncestorReachabilityFilter()`, spec FR-057-01, S-057-19..22) | 2026-09-29 | 2026-09-29 |
| ~~Q-057-05~~ | 057 – Album Listing v3 | High | Discovered while scoping Feature 058: the move-target picker consumer needs a per-album thumbnail, which the original minimal shape (id/title/_lft/_rgt) didn't carry | Resolved (add `cover_ids` to the base/default response — resolved server-side via the same 3-column priority `HasAlbumThumb::getCoverTypeForAlbum()` already uses, zero extra joins since all 3 columns live on `albums`; frontend resolves actual thumbnail bytes via the separate Feature 056 v3 Asset endpoint) | 2026-08-22 | 2026-08-22 |
| ~~Q-057-01~~ | 057 – Album Listing v3 | High | Rights-curation filter for the default (root) listing mode — `applyVisibilityFilter()` vs. `applyReachabilityFilter()` (password-lock aware) | Resolved (A — visibility for the album itself; ancestors per Q-057-06) | 2026-08-22 | 2026-09-29 |
| ~~Q-057-02~~ | 057 – Album Listing v3 | High | Query-parameter shape for the fixTree/bulk-edit variants — two independent booleans vs. one `mode` enum | Resolved (A — two independent booleans, `with_parent_id` + `for_bulk_edit`) | 2026-08-22 | 2026-08-22 |
| ~~Q-057-03~~ | 057 – Album Listing v3 | Medium | Field set returned when the bulk-edit flag is set — minimal owner/visibility subset vs. full parity with today's `BulkAlbumResource` | Resolved (B — full `BulkAlbumResource` field parity) | 2026-08-22 | 2026-08-22 |
| ~~Q-057-04~~ | 057 – Album Listing v3 | High | Pagination — always return the complete curated list (unpaginated) vs. optional `page`/`per_page` for the bulk-edit mode | Resolved (A — never paginate, always the full list) | 2026-08-22 | 2026-08-22 |

## Question Details

### Q-057-06 · Browsable-only default listing: which filter?

**Status:** Resolved (Option A, 2026-09-29 — folded into spec FR-057-01, NFR-057-04, S-057-13, S-057-19..22)
**Feature:** F-057 – Album Listing v3
**Preferred option:** Option A – Visibility + ancestor-only reachability

**Question**
The default listing must only contain albums a visitor can reach by clicking from the root (a public album nested under a private, link-only, or not-yet-unlocked album must not be listed). `AlbumQueryPolicy::applyBrowsabilityFilter()` already expresses browsability, but it also requires the album itself to be reachable, which excludes a visible album that is password-protected and not yet unlocked. Should such an album stay in the listing?

#### Option A (recommended) – Visibility + ancestor-only reachability
- **Idea:** Keep `applyVisibilityFilter()` and add `AlbumQueryPolicy::applyAncestorReachabilityFilter()`, which reuses `appendUnreachableAlbumsCondition()` restricted to strict ancestors.
- **Spec impact:** FR-057-01 becomes "visible, and every ancestor reachable"; S-057-13 and the locked-cover scenarios stay valid.
- **Pros:** a locked album remains in the nav tree/search so it can be clicked to reach the unlock form; the #4704 locked-cover gate keeps its purpose; its children appear after unlock.
- **Cons:** one more (small) public method on `AlbumQueryPolicy`.

#### Option B – `applyBrowsabilityFilter()` as-is
- **Idea:** Replace the visibility filter with `applyBrowsabilityFilter($query, $user, $unlocked_album_ids)`.
- **Spec impact:** FR-057-01 becomes "browsable"; S-057-13 inverts (locked album absent until unlocked); the locked-cover gate becomes unreachable for this endpoint and its tests must change.
- **Pros:** reuses the existing method unchanged, no new policy surface.
- **Cons:** a still-locked album vanishes from the nav tree/spotlight, so a guest cannot click it to unlock it from there.
