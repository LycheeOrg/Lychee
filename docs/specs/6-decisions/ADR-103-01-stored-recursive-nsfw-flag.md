# ADR-103-01: Stored recursive NSFW flag on albums

- **Status:** Accepted
- **Date:** 2026-10-28
- **Related features/specs:** Feature 103 (docs/specs/4-architecture/features/103-album-load-speedup/spec.md)
- **Related open questions:** Q-103-04

## Context

An album is sensitive when it or one of its ancestors has `base_albums.is_nsfw`. Every sensitivity check computes this with `AlbumQueryPolicy::appendRecursiveSensitiveAlbumsCondition()`: a correlated `NOT EXISTS` over the album's ancestors in the nested set, evaluated once per row of the outer query. Photo queries run it once per `photo_album` row. On the Timeline's whole-library `Photos/buckets` count (99,000 photos, SQLite scratch instance) it is most of the ≈392 ms the request takes.

A photo inherits sensitivity only through the album it is in, and a photo can be in several albums. The check runs per `photo_album` row, on the same row as the searchability check, so a photo in an NSFW album and in a non-NSFW album stays visible through the latter.

Some checks are bound to an origin album (photos below an album, cover selection, frame sub-albums, Flow with a base album): NSFW albums at or above the origin do not count there.

## Decision

1. `albums.is_recursive_nsfw` (boolean, not null, default false) stores whether the album or one of its ancestors has `is_nsfw`. It lives on `albums`, the nested-set table; tag and person albums have no ancestry.
2. It is derived data written explicitly, never by Eloquent events or mutators. One action, `App\Actions\Album\SyncRecursiveNsfw::do()`, reads the nested-set ranges of the NSFW albums and, in two `UPDATE` statements, clears the flag outside them and sets it inside them, writing only the albums whose flag changes. Every value is decided from `base_albums.is_nsfw` along the tree, never copied from another album's stored flag, so each write also repairs the flags it touches. Writers: album creation (the parent's ancestry), every change of `is_nsfw` (protection policy, bulk edit, NSFW album job) and of parent (move, merge, transfer), tree repairs (`lychee:fix-tree`, maintenance full-tree update), and the migration that adds and backfills the column.
3. Checks without an origin read the column on the album row they already join: the `PhotoQueryPolicy` sensitivity condition with `origin = null`, the RSS album list, Flow without a base album, and the `is_recursive_nsfw` value of Flow items and of the NSFW album job, which replaces the computed select `AlbumBuilder::addVirtualIsRecursiveNSFW()`.
4. Checks with an origin keep the correlated condition.
5. The flag is not stored on photos or on `photo_album` rows.

## Consequences

### Positive
- Whole-library photo queries (Timeline, search, smart albums, tag and person albums, map, landing page, RSS, embeds) test one column per row instead of running a subquery per row.
- A sync is one read of the NSFW albums and two `UPDATE` statements over the albums table; only the albums whose flag changes are written, and each sync repairs any stale flag in the library.

### Negative
- Derived state that every writer of `is_nsfw` or of the tree must keep in sync. A missed writer shows or hides NSFW content wrongly until the next sync.
- Two forms of the sensitivity rule coexist (stored for no origin, correlated with an origin).
- Tests that write `is_nsfw` or the tree directly must call the sync action.

## Alternatives Considered

- **Uncorrelated list of hidden albums for `origin = null` photo queries (Q-103-04 Option A):** no migration, ≈170 ms for the same count. Rejected by the owner in favour of the cheaper read for every no-origin caller.
- **Keep the correlated condition (Option B):** no risk, ≈392 ms on every first Timeline load after a library change.
- **Flag on photos:** saves no join (photo queries join `photo_album` and `albums` for searchability anyway). "In any NSFW album" hides photos visible today through a non-NSFW album. "Only in NSFW albums" shows a photo that is in a public NSFW album and in a private non-NSFW album. Every upload, copy, move and unlink becomes a writer, and a toggle rewrites every photo of the subtree.
- **Flag on `photo_album` rows:** correct per row, but a toggle or move rewrites every photo link of the subtree and every link change is a writer.

## Security / Privacy Impact

- NSFW visibility for no-origin queries depends on the stored flag being current. The writers are listed in FR-103-11 and each is covered by `AlbumRecursiveNsfwTest`.
- Visibility and access rules (searchability, permissions, passwords) are unchanged.

## Operational Impact

- The migration backfills the flag in a few statements (one per outermost NSFW album).
- `lychee:fix-tree` and the maintenance full-tree update recompute every flag.
- Faster Timeline `buckets`, search and map queries on large libraries.

## Links

- Related spec sections: `docs/specs/4-architecture/features/103-album-load-speedup/spec.md#functional-requirements` (FR-103-11, FR-103-12), DB-103-01
- Related ADRs: —
- Related issues / PRs: —
