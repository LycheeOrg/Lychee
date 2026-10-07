# Open Questions – Feature 058

Open questions for [Feature 058](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-058-01~~ | 058 – Album Listing v3 Adoption | High | Scope & approach for migrating the 3 v2 consumers, given real UX mismatches (move-picker loses thumbnail/breadcrumb/self-exclusion; bulk-edit loses server pagination+search) — migrate all 3 with client-side compensation vs. a narrower scope | Resolved (all 3, client-side compensation, thumbnails preserved via a new `<Thumb>` component) | 2026-08-22 | 2026-08-22 |
| ~~Q-058-02~~ | 058 – Album Listing v3 Adoption | Medium | Feature-flag granularity in `config/features.php` — one combined flag vs. one independent flag per consumer | Resolved (A — one combined flag) | 2026-08-22 | 2026-08-22 |
| ~~Q-058-03~~ | 058 – Album Listing v3 Adoption | Medium | Flag naming/scope — `ALBUM_LISTING_V3_ENABLED` (feature-specific) vs. a general SoA toggle | Resolved (`STRUCT_OF_ARRAY_ENABLED`/`struct-of-array`/`is_struct_of_array_enabled` — reused later by a future Photos SoA endpoint) | 2026-08-22 | 2026-08-22 |
| ~~Q-058-04~~ | 058 – Album Listing v3 Adoption | High | Where the shared album list lives and how its tree is computed — bare composable vs. Pinia store; `_lft`/`_rgt` reconstruction vs. admin-gated `parent_ids` | Resolved (Pinia store `AlbumListState.ts`; tree from `_lft`/`_rgt` only, since `parent_ids` is admin-gated and unusable for non-admin move/merge dialogs) | 2026-08-22 | 2026-08-22 |
| ~~Q-058-05~~ | 058 – Album Listing v3 Adoption | High | Cyclic-dependency prevention for Album Move and Album Merge (multi-album) — single-root vs. multi-root exclusion | Resolved (one pure `getExcludedTargetIds(rootIds)` function, self ∪ descendants via `_lft`/`_rgt`, covering N roots uniformly) | 2026-08-22 | 2026-08-22 |
| ~~Q-058-06~~ | 058 – Album Listing v3 Adoption | High | Breadcrumb display and the "move to root" option — drop as dead scope (today's v8 picker renders neither) vs. build both as real UI additions | Resolved (build both — breadcrumb full-path text mirrors `ListAlbums::do()`, truncation uses CSS not `shorten()`'s algorithm; root option derived from `_lft`/`_rgt` containment, no `parent_ids` needed) | 2026-08-22 | 2026-08-22 |
| ~~Q-058-07~~ | 058 – Album Listing v3 Adoption | Medium | Breadth of the shared store's mutation-invalidation net — move/merge/login/logout only vs. widen to all regular-Album mutations | Resolved (widen to delete/unlock/visibility-change/Fix-Tree-save/server-import, plus WebAuthn login and registration's auto-login; explicitly excludes pin-toggle and Tag/Person-Album operations, confirmed irrelevant to `GET /api/v3/Albums`'s tracked fields) | 2026-08-22 | 2026-08-22 |

## Question Details

_No entries._
