# ADR-075-01: Side covers as fixed extra columns on the cover rows

- **Status:** Accepted
- **Date:** 2026-09-30
- **Related features/specs:** Feature 075 (docs/specs/4-architecture/features/075-album-hover-covers/spec.md, FR-075-01, FR-075-08, FR-075-10, FR-075-11), Feature 076 (docs/specs/4-architecture/features/076-unified-album-cover-store/spec.md)
- **Related open questions:** Q-075-01, Q-075-02

## Context

Album tiles fan out three stacked images on hover, all showing the one cover id the listings provide. Two more photo ids per album are needed. Automatic covers live in one table, `album_user_thumbs`, one row per `(album_id, viewer key)`:

- Regular albums: precomputed rows (`is_precomputed = true`, ADR-076-01), the max-privilege row keyed on the owner and the least-privilege row keyed on `NULL` or the single shared user. They are written by `RecomputeAlbumStatsJob` from one ordered query, and read by the v3 struct-of-arrays listings through one indexed join (`JoinAutoCover`), with no per-album query (ADR-0009).
- Tag, person and smart albums: cache rows, materialised lazily on first view, refreshed by `RecomputeAlbumUserThumbsJob`, and purged on every access revocation (ADR-0010). `GetPhotoAssetRequest` treats a row as proof that the photo represents the album, with no per-request permission re-check.

Both kinds of row sit on hot paths: the listings are the largest responses in the app, the asset endpoint is hit once per rendered thumbnail. Any design that turns "one row per cover" into "one row per rank" multiplies the joined rows and undoes the gains of Features 057, 061 and 062.

## Decision

Every cover row carries ranks 1–3 as **fixed nullable columns**, never as rank rows:

- `album_user_thumbs`: `photo_id` (rank 1, FK `photos.id` ON DELETE CASCADE, so an emptied primary removes the row), `photo_id_2`, `photo_id_3` (`char(24)`, FK `photos.id` ON DELETE SET NULL).
- `RecomputeAlbumStatsJob` takes `limit(3)` on the query that already picks the cover, per privilege level. `CachesAlbumUserThumb` and `RecomputeAlbumUserThumbsJob` write all three from one ordered query (`Thumb::createManyFromQueryable()`).

Consumers stay row-shaped: listings read the three ranks from the same joined or cached row, the unique key `(album_id, user_id_unique_key)` and the `userThumbRow()` HasOne relation are unchanged. The asset-endpoint cover exception matches any of the three columns. The write-side purge of ADR-0010 matches any of the three cache columns and deletes the whole row.

The side ids are never computed inside a listing request. A row without side columns yields `null` sides and the tile falls back to the cover.

## Consequences

### Positive
- Zero additional read-path queries or joins for side covers; one batched cache read for the category listings, which the smart listing already does.
- One write-path query change (`first()` → `limit(3)`), no new job.
- FK integrity: a deleted photo nulls a side column instead of leaving a dangling id that the asset exception would still accept.
- The dual-privilege guarantee of ADR-0003 and the write-side purge invariant of ADR-0010 extend mechanically to the side columns.

### Negative
- Fixed at two side covers. A future "N covers" needs another migration. The tile has exactly two back layers, so this is not a practical limit.
- Two more nullable columns and FKs on the cover table.
- Every future revocation path must still remember `PurgeAlbumUserThumbs` (ADR-0010), matching all three columns.

## Alternatives Considered

- **A — Chosen.** Fixed extra columns on every cover row.
- **B — Normalized `rank` rows.** Any N; but every listing join multiplies by three and needs a regroup, the cache unique key and relation change shape, and the purge/exception paths handle multi-row matches. Rejected on read-path cost.
- **C — JSON id list per row.** No FK, dangling ids after photo deletion, not checkable in SQL. Rejected on integrity.

## Security / Privacy Impact

- Least-privilege side covers are selected by the same `PhotoQueryPolicy::applySearchabilityFilter()` as the least-privilege cover, so they cannot contain a photo invisible to their viewers. Cache side covers come from the same viewer-filtered `photos()` relation as the cached cover.
- The asset-endpoint exception accepts any rank of any precomputed row of a regular album for any viewer who can access it (ids are unguessable 24-char random strings), and any rank of the viewer's own cache row for tag/person/smart albums.
- Password-locked albums: sides are hidden unless `show_cover_of_locked_albums` is on; the manual-cover exception (`show_selected_cover_on_locked_albums`) never reveals sides.

## Operational Impact

- Existing covers carry their side ranks once their album is next recomputed; `php artisan lychee:recompute-album-stats` fills every album at once. Cache rows fill on the next `RecomputeAlbumUserThumbsJob` run or the next lazy materialisation.
- No monitoring change.

## Links

- Spec: `docs/specs/4-architecture/features/075-album-hover-covers/spec.md#functional-requirements`
- Related ADRs: ADR-0003, ADR-0009, ADR-0010, ADR-076-01
- Source: https://github.com/LycheeOrg/Lychee/discussions/4742
