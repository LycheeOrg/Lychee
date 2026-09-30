# ADR-075-01: Side covers as fixed extra columns on both cover stores

- **Status:** Accepted
- **Date:** 2026-09-30
- **Related features/specs:** Feature 075 (docs/specs/4-architecture/features/075-album-hover-covers/spec.md, FR-075-01, FR-075-08, FR-075-10, FR-075-11)
- **Related open questions:** Q-075-01, Q-075-02

## Context

Album tiles fan out three stacked images on hover, all showing the one cover id the listings provide. Two more photo ids per album are needed. Lychee keeps album covers in two stores with different shapes:

- Regular albums: two precomputed columns on `albums` (`auto_cover_id_max_privilege`, `auto_cover_id_least_privilege`, ADR-0003), filled by `RecomputeAlbumStatsJob` from one ordered query and read by the v3 struct-of-arrays listings straight off the row, with no relation load and no per-album query (ADR-0009).
- Tag, person and smart albums: one row per `(album_id, user_id)` in `album_user_thumbs`, materialised lazily on first view, refreshed by `RecomputeAlbumUserThumbsJob`, and purged on every access revocation (ADR-0010). `GetPhotoAssetRequest` treats a row as proof that the photo represents the album, with no per-request permission re-check.

Both stores are on hot paths: the listings are the largest responses in the app, the asset endpoint is hit once per rendered thumbnail. Any design that turns "one id per row" into "one query or join per album" undoes the gains of Features 057, 061 and 062.

## Decision

Both stores grow by **fixed nullable columns**, never by rows:

- `albums`: `auto_cover_id_max_privilege_2`, `_3`, `auto_cover_id_least_privilege_2`, `_3` (`char(24)`, FK `photos.id` ON DELETE SET NULL). `RecomputeAlbumStatsJob` takes `limit(3)` on the query that already picks the cover.
- `album_user_thumbs`: `photo_id_2`, `photo_id_3` (`char(24)`, FK `photos.id` ON DELETE SET NULL; `photo_id` keeps its cascade so an emptied primary still removes the row). `CachesAlbumUserThumb` and `RecomputeAlbumUserThumbsJob` write all three from one ordered query (`Thumb::createManyFromQueryable()`).

Consumers stay row-shaped: listings read the extra columns from the same row, the unique key `(album_id, user_id_unique_key)` and the `userThumbRow()` HasOne relation are unchanged, and the asset-endpoint cover exception widens from "equals the cover column" to "equals any of the cover columns". The write-side purge of ADR-0010 matches any of the three cache columns and deletes the whole row.

The side ids are never computed inside a listing request. A row without side columns yields `null` sides and the tile falls back to the cover.

## Consequences

### Positive
- Zero additional read-path queries or joins for regular albums; one batched cache read for the category listings, which the smart listing already does.
- One write-path query change (`first()` → `limit(3)`), no new job.
- FK integrity: a deleted photo nulls a side column instead of leaving a dangling id that the asset exception would still accept.
- The dual-privilege guarantee of ADR-0003 and the write-side purge invariant of ADR-0010 extend mechanically to the new columns.

### Negative
- Fixed at two side covers. A future "N covers" needs another migration. The tile has exactly two back layers, so this is not a practical limit.
- Six more nullable columns and FKs across the two tables.
- Every future revocation path must still remember `PurgeAlbumUserThumbs` (unchanged from ADR-0010), now for three columns instead of one.

## Alternatives Considered

- **A — Chosen.** Fixed extra columns on both stores.
- **B — Normalized `album_covers` / `rank` rows.** Any N; but every listing needs a join or a second query plus a client-side regroup, the cache unique key and relation change shape, and the purge/exception paths handle multi-row matches. Rejected on read-path cost.
- **C — JSON id list per privilege.** No FK, dangling ids after photo deletion, not checkable in SQL. Rejected on integrity.

## Security / Privacy Impact

- Least-privilege side covers are selected by the same `PhotoQueryPolicy::applySearchabilityFilter()` as the least-privilege cover, so they cannot contain a photo invisible to the public. Cache side covers come from the same viewer-filtered `photos()` relation as the cached cover.
- The asset-endpoint exception already accepts the max-privilege cover id for any viewer who can access the album (ids are unguessable 24-char random strings). Widening it to the side columns keeps that property unchanged in kind.
- Password-locked albums: sides are hidden unless `show_cover_of_locked_albums` is on; the manual-cover exception (`show_selected_cover_on_locked_albums`) never reveals sides.

## Operational Impact

- Upgrade: existing albums have `NULL` sides until their next recompute. Operators run `php artisan lychee:recompute-album-stats` once to backfill (same command as ADR-0003 §1). Cache rows fill on the next `RecomputeAlbumUserThumbsJob` run or the next lazy materialisation.
- No monitoring change.

## Links

- Spec: `docs/specs/4-architecture/features/075-album-hover-covers/spec.md#functional-requirements`
- Related ADRs: ADR-0003, ADR-0009, ADR-0010
- Source: https://github.com/LycheeOrg/Lychee/discussions/4742
