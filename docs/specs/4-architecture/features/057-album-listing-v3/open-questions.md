# Open Questions – Feature 057

Open questions for [Feature 057](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-057-05~~ | 057 – Album Listing v3 | High | Discovered while scoping Feature 058: the move-target picker consumer needs a per-album thumbnail, which the original minimal shape (id/title/_lft/_rgt) didn't carry | Resolved (add `cover_ids` to the base/default response — resolved server-side via the same 3-column priority `HasAlbumThumb::getCoverTypeForAlbum()` already uses, zero extra joins since all 3 columns live on `albums`; frontend resolves actual thumbnail bytes via the separate Feature 056 v3 Asset endpoint) | 2026-08-22 | 2026-08-22 |
| ~~Q-057-01~~ | 057 – Album Listing v3 | High | Rights-curation filter for the default (root) listing mode — `applyVisibilityFilter()` vs. `applyReachabilityFilter()` (password-lock aware) | Resolved (A — visibility only) | 2026-08-22 | 2026-08-22 |
| ~~Q-057-02~~ | 057 – Album Listing v3 | High | Query-parameter shape for the fixTree/bulk-edit variants — two independent booleans vs. one `mode` enum | Resolved (A — two independent booleans, `with_parent_id` + `for_bulk_edit`) | 2026-08-22 | 2026-08-22 |
| ~~Q-057-03~~ | 057 – Album Listing v3 | Medium | Field set returned when the bulk-edit flag is set — minimal owner/visibility subset vs. full parity with today's `BulkAlbumResource` | Resolved (B — full `BulkAlbumResource` field parity) | 2026-08-22 | 2026-08-22 |
| ~~Q-057-04~~ | 057 – Album Listing v3 | High | Pagination — always return the complete curated list (unpaginated) vs. optional `page`/`per_page` for the bulk-edit mode | Resolved (A — never paginate, always the full list) | 2026-08-22 | 2026-08-22 |

## Question Details

_No entries._
