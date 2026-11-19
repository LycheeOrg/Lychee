# ADR-0010: Purge cached album covers on revocation rather than re-checking permissions per asset request

- **Status:** Accepted
- **Date:** 2026-09-22
- **Related features/specs:** Feature 056 (docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md, FR-056-08/FR-056-09), Feature 062 (docs/specs/4-architecture/features/062-root-album-listing-struct-of-arrays/spec.md, FR-062-16)
- **Related open questions:** none open (Q-063-15 resolved earlier, for the cover exception itself)

## Context

`album_user_thumbs` caches the computed cover of a tag/person/smart album per viewer, keyed by
`(album_id, user_id)` where `user_id` is `Auth::id()` — i.e. by whoever materialised the row, never by
the permission that granted them access.

`GetPhotoAssetRequest::isComputedAlbumThumb()` (FR-056-08) accepts such a row as proof that the photo
legitimately represents the album, short-circuiting the permission-filtered `$album->photos()` check.
That exception exists to tolerate staleness of the album's *membership* condition — a photo that fell
out of the tag/person/smart query before the next `RecomputeAlbumUserThumbsJob` run. It does not
distinguish that from staleness of the *permissions* on the album the photo physically lives in, so a
row which outlives the access that produced it keeps serving the photo's bytes.

A security retest (2026-09-22, following commit 8e84d7667) reproduced exactly that: the source album
was shared publicly, an authenticated viewer materialised the tag album's cover, the owner revoked the
public permission, and `GET /api/v3/Asset/{tagAlbumId}/{photoId}/small` kept returning the private
thumbnail. `SharingController::delete()` purged only rows with `user_id IS NULL` for a public
permission, while the viewer's row carried their own `user_id`.

Two further revocation paths have the same shape and were never purged at all: leaving a user group,
and deleting a user group — neither deletes an `access_permissions` row, so no permission-keyed purge
is reachable from them. `Propagate::overwrite()` is a third: it wipes the descendants' own permissions
before re-inserting the ancestor's.

`GET /api/v3/Asset/...` is served once per rendered thumbnail and is the hottest authenticated endpoint
in the application, which constrains what may be added to its authorization path.

## Decision

Keep the asset endpoint's cache exception free of any per-request permission re-check, and make the
invariant a write-side obligation instead: **every path that revokes a viewer's access purges the
cached covers that access may have produced.**

The purge is centralised in `App\Actions\Sharing\PurgeAlbumUserThumbs`:

- `forBaseAlbums(ids)` — drops **every** viewer's row pointing at a photo of those albums. Deliberately
  not narrowed by the revoked permission's user/group, since rows are keyed by materialiser, not by
  grant. Called from `SharingController::delete()` and `Propagate::overwrite()`.
- `forUsers(ids)` — drops all rows of those viewers, used where the viewer's side of the relation
  changed and the lost album set is not readily available. Called from
  `PurgeAlbumUserThumbsOnMembershipChange` (on `UserGroupMembershipChanged`) and
  `UserGroupsController::delete()`.

Purging is intentionally coarse. A missing row costs one lazy recomputation on the viewer's next read,
through the permission-filtered live query in `CachesAlbumUserThumb`; a surviving row is an access leak.

Paths already covered need no call and are listed in the action's docblock so the register stays in one
place: photo deletion (FK cascade on `photo_id`), user deletion (`User::delete()`), album deletion
(`Actions\Album\Delete`), and photo membership changes (`RecomputeAlbumUserThumbsOnPhotoChange`).

## Consequences

### Positive
- The hot path is unchanged: no additional query on the per-thumbnail authorization path.
- FR-056-08's staleness tolerance is preserved exactly as specified.
- Revocation semantics are immediate and visible in one auditable place.

### Negative
- Correctness now depends on every *future* revocation path remembering to purge. A path added without
  a `PurgeAlbumUserThumbs` call silently re-opens the hole, and nothing in the request path will catch
  it.
- Coarse purges evict unaffected viewers' covers, which are then recomputed lazily.

## Alternatives Considered

- **A — Re-check visibility in `isComputedAlbumThumb()`** (`PhotoQueryPolicy::applyVisibilityFilter()`
  on the photo). Pro: closes the whole class regardless of revocation path, defence in depth. Con: adds
  a query to an endpoint hit once per rendered thumbnail. **Rejected on cost** (owner decision,
  2026-09-22).
- **B — Chosen.** Exhaustive write-side purge, no read-side cost. Con: one missed call site re-opens it.
- **C — Stop treating the cache row as membership proof and drop the exception.** Pro: no invariant to
  maintain. Con: a cached cover that has fallen out of its album's live condition would 403 until the
  next `RecomputeAlbumUserThumbsJob` run — the exact breakage FR-056-08 was introduced to prevent.

## Security / Privacy Impact

- Closes a private-thumbnail disclosure reachable after revoking a public share, a group membership, a
  group, or a descendant's own permissions.
- Moves a trust boundary from the read path to the write path: the asset endpoint now trusts
  `album_user_thumbs` unconditionally, so the table's freshness *is* the access control for that branch.
  Reviewers adding a revocation path must treat a missing purge as a security defect, not a stale-cache
  nuisance.
- No secrets, logging, or redaction impact.

## Operational Impact

- No new configuration, job, or runbook step. Purges are synchronous single-statement deletes on an
  indexed table.
- Slight, bounded increase in lazy thumb recomputation after sharing and group changes.

## Links

- Related spec sections: `docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md` (FR-056-08, FR-056-09, S-056-18/19)
- Related ADRs: ADR-0008 (v3 asset endpoint signing and authorization)
- Related code: `app/Actions/Sharing/PurgeAlbumUserThumbs.php`, `app/Listeners/PurgeAlbumUserThumbsOnMembershipChange.php`, `app/Http/Requests/Photo/GetPhotoAssetRequest.php`
