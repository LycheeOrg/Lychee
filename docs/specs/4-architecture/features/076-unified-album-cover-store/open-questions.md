# Open Questions – Feature 076

Open questions for [Feature 076](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-076-01~~ | 076 | High | How `album_user_thumbs` tells precomputed regular-album rows apart from disposable tag/person/smart cache rows, so the ADR-0010 purges and `User::delete()` do not drop regular-album covers (A: `is_precomputed` column, B: scope each purge by a `NOT EXISTS albums` subquery, C: no discriminator, purge both and re-dispatch `RecomputeAlbumStatsJob`) | Resolved (Option A, spec FR-076-01/10, I4, ADR-076-01) | 2026-09-30 | 2026-09-30 |
| ~~Q-076-02~~ | 076 | Medium | Whether a change of an album's access permissions recomputes its automatic covers, now that the least-privilege row is keyed on the single shared user (A: listener on `AccessPermissionChanged` dispatching `RecomputeAlbumStatsJob` with parent propagation, B: same without propagation, C: no recompute) | Resolved (Option A, spec FR-076-13, I5, NG7, ADR-076-01) | 2026-09-30 | 2026-09-30 |

## Question Details

### ~~Q-076-01~~ – Discriminating precomputed rows from cache rows

**Resolution:** Option A. See spec FR-076-01, FR-076-10, invariant I4 and [ADR-076-01](../../../6-decisions/ADR-076-01-unified-album-cover-store.md).

**Context.** Feature 076 moves the regular-album automatic covers from `albums.auto_cover_id_*` into `album_user_thumbs` (spec FR-076-01..03). Today every row of that table is a lazily seeded, disposable cache entry for a tag, person or smart album (ADR-0010). Code writing to the table relies on that:

- `PurgeAlbumUserThumbs::forUsers()` deletes every row of a user when their group membership changes. It would delete an owner's `user_id = owner_id` row for **every** album they own.
- `PurgeAlbumUserThumbs::forBaseAlbums()` deletes every row whose `photo_id`/`photo_id_2`/`photo_id_3` belongs to an album that lost a permission. It would delete regular-album rows whose cover came from that album.
- `User::delete()` deletes every row of the user. It would delete owner rows that must instead be re-keyed to the new owner (FR-076-09).

A regular-album row is not rebuilt lazily: only `RecomputeAlbumStatsJob` writes it. A deleted row leaves the album without an automatic cover until the next photo or album event on that subtree.

`RecomputeAlbumUserThumbsJob` is only dispatched with tag, person or smart album ids, so it never sees regular-album rows and needs no change.

**Option A (recommended): an `is_precomputed` boolean column.**
`album_user_thumbs.is_precomputed` (`boolean`, default `false`). `RecomputeAlbumStatsJob` and the data migration write `true`. `forBaseAlbums()`, `forUsers()` and the cache-row part of `User::delete()` add `where('is_precomputed', false)`.
- Pros: explicit and self-describing in SQL and in the model. The purge queries stay one flat `DELETE` with no subquery. A future purge path fails closed if the author copies an existing purge.
- Cons: one more column. Every new write path must remember to set the flag, but only `RecomputeAlbumStatsJob` and the migration write precomputed rows.

**Option B: scope each purge by a `NOT EXISTS (albums)` subquery.**
No schema change. A row is precomputed exactly when its `album_id` is in `albums`. Each purge gets `whereNotExists(fn ($q) => $q->from('albums')->whereColumn('albums.id', 'album_user_thumbs.album_id'))`.
- Pros: no extra column. The distinction cannot drift from the data.
- Cons: implicit: nothing on the row says which kind it is. Every purge carries a correlated subquery. A purge path added without the subquery silently deletes regular-album covers.

**Option C: no discriminator, purge regular rows too and re-dispatch `RecomputeAlbumStatsJob`.**
Purges delete any matching row, then dispatch `RecomputeAlbumStatsJob` for every affected regular album.
- Pros: also refreshes a least-privilege regular cover whose photo just became private, which today stays stale until the next album or photo event.
- Cons: covers vanish from listings until the job runs. `forUsers()` on an owner triggers a recompute of every album they own on each group-membership change, which is expensive on large instances. Widens this refactor into a behaviour change.

### ~~Q-076-02~~ – Recomputing covers when access permissions change

**Resolution:** Option A, as a listener on the explicitly dispatched `AccessPermissionChanged` domain event (no Eloquent model events). See spec FR-076-13, invariant I5, NG7 and [ADR-076-01](../../../6-decisions/ADR-076-01-unified-album-cover-store.md).

**Context.** `RecomputeAlbumStatsJob` runs on photo and album events (`RecomputeAlbumStatsOnPhotoChange`, `RecomputeAlbumStatsOnAlbumChange`) but not on sharing changes. `SharingController::create()`, `edit()` and `delete()` dispatch `AccessPermissionChanged(album_id)`, whose only listeners invalidate the listing and search caches. `SharingController::propagate()` (`Propagate::update()` / `overwrite()`) dispatches only `AlbumListingCacheFlushRequested`.

With columns on `albums`, a stale least-privilege cover still resolves for every non-owner viewer. With the viewer-keyed rows of FR-076-02, the row's key depends on the permission set:

- The album is shared with user X only. Its least-privilege row is keyed on X.
- The album is then shared with user Y as well. No job runs, so the row stays keyed on X.
- Y's listing finds neither a Y row nor a `NULL` row, so Y gets no automatic cover until the next photo or album event on that subtree.

The reverse (a second permission removed) leaves a `NULL` row computed as the public view. X still sees the public cover, which is safe but not X's best one.

Ancestors are affected too. An ancestor's least-privilege cover is chosen from the photos searchable in its subtree, and those depend on the descendants' permissions. That staleness exists today and is independent of this feature.

**Option A (recommended): a listener on `AccessPermissionChanged` dispatches `RecomputeAlbumStatsJob($album_id)` with its default parent propagation.**
New `RecomputeAlbumStatsOnAccessPermissionChange` listener, registered next to the two cache invalidators. `SharingController::propagate()` dispatches `AccessPermissionChanged` for every descendant whose permissions `Propagate` rewrote.
- Pros: the least-privilege row is re-keyed as soon as the permission set changes. Ancestors' least-privilege covers follow the new searchability, the same as for photo events. Reuses the job's existing debounce.
- Cons: one job chain per sharing change, up to the root. Propagating over a large subtree dispatches one event per descendant, so it enqueues many debounced jobs whose chains overlap towards the root.

**Option B: same listener, `propagate_to_parent: false`.**
- Pros: the smallest fix for the key problem. One job per changed album.
- Cons: ancestors keep today's staleness.

**Option C: no recompute on sharing changes.**
- Pros: no new listener, no extra jobs.
- Cons: newly shared users see no automatic cover until an unrelated event, which is a regression compared with today.
