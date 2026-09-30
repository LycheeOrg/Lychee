# ADR-076-01: One cover store for all album kinds, keyed by viewer class

- **Status:** Accepted
- **Date:** 2026-09-30
- **Related features/specs:** Feature 076 (docs/specs/4-architecture/features/076-unified-album-cover-store/spec.md, FR-076-01..13, Row Model, I1–I5)
- **Related open questions:** Q-076-01, Q-076-02

## Context

Automatically selected album covers live in two stores:

- Regular albums: six columns on `albums`, one rank-1..3 triple per privilege level (`auto_cover_id_{max,least}_privilege[_2,_3]`), written by `RecomputeAlbumStatsJob` (ADR-0003, ADR-075-01).
- Tag, person and smart albums: one row per `(album_id, viewer)` in `album_user_thumbs` with `photo_id`, `photo_id_2`, `photo_id_3`. Rows are seeded lazily, refreshed by `RecomputeAlbumUserThumbsJob` and purged on revocation (ADR-0010).

Both stores answer the same question, "which photos represent this album for this viewer", with two schemas, two read rules and two asset-authorization paths. The regular-album privilege levels map onto viewer keys:

- the max-privilege cover is what the owner (and any admin) sees;
- the least-privilege cover is what everyone else sees. It is computed as the public view, or as the single shared user when the album has exactly one user permission.

The v3 listings are the hottest read path (ADR-0009). Whatever store is chosen, a listing must stay one query, with no per-album lookup.

## Decision

1. **Regular-album covers are rows in `album_user_thumbs`.** Each album has at most two rows:
   - the max-privilege row, keyed on `albums.owner_id`;
   - the least-privilege row, keyed on the single shared user, or on `NULL` otherwise.

   No least-privilege row exists without any permission, or when the single shared user is the owner. `albums.cover_id` stays on `albums`, and the six `auto_cover_id_*` columns are dropped. A migration copies the existing triples into rows.
2. **Rows are tagged `is_precomputed`** (`boolean`, default `false`). `RecomputeAlbumStatsJob` is the only runtime writer of `true` rows. The ADR-0010 purges (`PurgeAlbumUserThumbs::forBaseAlbums()` / `forUsers()`) and the cache path of `User::delete()` filter on `is_precomputed = false`.
3. **Read rule.** Admin → the owner row. Logged-in user → their own row, else the `NULL` row. Guest → the `NULL` row. For regular albums the only non-`NULL` keys are the owner and the single shared user, so a row keyed on a non-admin viewer is always the right one for them.
4. **Listings choose the join shape in PHP** (`JoinAutoCover::apply()`), with no `CASE` on viewer identity:
   - admin: one `LEFT JOIN` on `user_id = base_albums.owner_id`;
   - guest: one `LEFT JOIN` on `user_id_unique_key = 0`;
   - logged-in user: two `LEFT JOIN`s (their own row and the `NULL` row), taking all three ranks from one row.

   Every join hits the existing `(album_id, user_id_unique_key)` unique index. The v2 model path eager-loads the viewer's candidate rows (`Album::autoCoverRows()`) and picks one with a pure helper.
5. **The least-privilege key follows the permission set.** A listener on the explicitly dispatched `AccessPermissionChanged` domain event runs `RecomputeAlbumStatsJob` with parent propagation. `SharingController::propagate()` dispatches that event for every touched descendant. No Eloquent model events are used.
6. **Ownership changes re-key the owner row** (`Transfer`, `User::delete()`) in the same code path that changes `owner_id`.

## Consequences

### Positive
- `albums` loses six columns and six foreign keys. One store and one read rule serve every album kind.
- Listings keep their query count; the v2 model path uses one query fewer (row, photo, size variants instead of two relations with two size-variant loads each).
- The least-privilege cover of an album and of its ancestors is refreshed on sharing changes, which the column model never did.
- Side covers resolve through the same triple shape for every album kind (`SideCoverIds` loses its privilege branch).

### Negative
- Precomputed and cache rows share a table with different lifecycles. Every new purge path must filter on `is_precomputed`.
- The asset endpoint runs one query for a regular album's non-manual cover, where it used to compare in memory against loaded columns.
- Ownership changes must re-key rows explicitly.
- Sharing changes enqueue recompute jobs up to the root, and a propagate call enqueues one chain per descendant, collapsed by the job's existing debounce.

## Alternatives Considered

- **Q-076-01 A — Chosen.** `is_precomputed` column.
- **Q-076-01 B.** Scope each purge by `NOT EXISTS (albums)`. No column, but the distinction is implicit, and a purge path that forgets the subquery silently deletes covers.
- **Q-076-01 C.** No discriminator; purge both kinds and re-dispatch the job. Covers vanish until the job runs, and a group-membership change recomputes every album the user owns.
- **Q-076-02 A — Chosen.** Recompute on `AccessPermissionChanged` with parent propagation.
- **Q-076-02 B.** Same without propagation. Ancestors keep stale least-privilege covers.
- **Q-076-02 C.** No recompute. Newly shared users see no automatic cover.
- **Keep the columns.** No lifecycle mixing, but two stores, two read rules and six more columns on the widest table.

## Security / Privacy Impact

- A viewer who is neither admin nor owner is never joined to the owner row. The dual-privilege guarantee of ADR-0003 is unchanged.
- The single-share row is computed as that user and only readable by them (or through the owner/admin shortcut, which already sees everything).
- The asset-endpoint exception accepts any precomputed row of the album for any viewer who can access it. This is the same set of ids the seven-column check accepted.
- The ADR-0010 write-side purge invariant still covers cache rows. Precomputed rows are kept current by the job, which now also runs on permission changes.

## Operational Impact

- The upgrade migration copies existing covers into rows, so no recompute is needed after deploying.
- Extra queue load on sharing changes, proportional to subtree depth (and to subtree size for propagate).
- No monitoring change.

## Links

- Spec: `docs/specs/4-architecture/features/076-unified-album-cover-store/spec.md`
- Related ADRs: ADR-0003, ADR-0009, ADR-0010, ADR-075-01
