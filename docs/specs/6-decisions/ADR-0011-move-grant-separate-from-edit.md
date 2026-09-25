# ADR-0011: Move grant separate from Edit, and cross-owner guards

- **Status:** Accepted
- **Date:** 2026-09-25
- **Related features/specs:** Feature 072 (docs/specs/4-architecture/features/072-edit-grant-escalation/spec.md)
- **Related open questions:** Q-072-01, Q-072-02, Q-072-03 (superseded), Q-072-04 … Q-072-11

## Context

Owning an album gives full rights over every photo in it: `AlbumPolicy::canAccessFullPhoto()` and `canDownload()` short-circuit on album ownership, and `PhotoPolicy` ORs grants over all albums containing a photo. `Photo::copy`, `Photo::move`, `Album::move` and `Album::merge` only checked `CAN_EDIT`, so an edit-only collaborator could place someone else's photos or albums under their own ownership and obtain originals, downloads, subtree ownership, or delete the source (GHSA-pw32-v9r5-85hc, GHSA-jp9x-63pp-pv4v).

Affected modules: persistence (`access_permissions`), policies, REST request authorization, sharing API, v8 sharing UI and destination picker.

## Decision

1. **One new share grant, `grants_move`,** governs Move, Copy and Merge. It is separate from `grants_edit` and covers an album's **content** (its photos and sub-albums), never the album itself (Q-072-09): moving album X needs the grant on X's parent, exactly like delete. Photo move/copy need it on the source album; merge needs it on the source (content leaves) plus delete on the source's parent. Targets keep requiring `CAN_EDIT`.
2. **Cross-owner guards apply regardless of grants:**
   - placing someone else's photo into an album whose owner differs from the photo's owner requires already holding full-photo access and download on it (moves within the photo owner's albums, and to their unsorted, need only the move grant);
   - moving or merging an album into another owner's album requires owning the source (`CAN_TRANSFER`).
3. **No coupling** between the move grant and full-photo access/download; the guards provide the security.
4. **Backfill:** existing permissions get `grants_move = grants_edit`.
5. **Destination picker:** filtered to albums the user can edit; not source-aware. Guard rejections surface as 403.

6. **Delete follows the same content semantics** (Q-072-11): deleting an album needs `grants_delete` on its parent (root album: owner only); deleting a photo needs it on the album containing it.
7. **Sharing decisions stay with the owner** (Q-072-10): the protection policy (public, link, password, NSFW, public grants) is owner-only, like per-user sharing; smart albums admin-only.

General rule going forward: *an operation that changes ownership or containment must require the rights its effect confers, not only the right to edit.*

## Consequences

### Positive
- Owners can let collaborators reorganise without handing out originals, or edit metadata without reorganising.
- No grant combination escalates into full-photo access, download, ownership or deletion.
- Existing collaborators keep their reorganising workflow within the owner's albums.

### Negative
- A collaborator with the move grant can move a photo from the owner's restricted album into the owner's public, full-access album (accepted, Q-072-01).
- One more column and checkbox per share.
- The picker can still offer a target that a cross-owner guard refuses (403 at submit).
- Backfill preserves reorganising rights that some owners may not have intended; they must untick Move.

## Alternatives Considered

- **Edit implies full-photo access** — widens every existing edit share; does not fix album takeover or merge deletion. Rejected.
- **Move grant auto-enables full-photo access + download** — removes the "reorganise without originals" combination; still cannot allow cross-owner album moves. Rejected.
- **Separate Copy/Merge grants** — the guards make Copy and Move equally safe; Merge already needs delete. Rejected as extra UI for little gain.
- **Source-aware picker (server-computed targets)** — rules in one place and no failing targets, but an extra endpoint and request. Rejected by owner in favour of an editability filter.
- **Anchor photo rights to the photo owner** — breaks album owners' access to collaborator uploads. Rejected.

## Security / Privacy Impact

- Closes two high-severity CWE-863 advisories and two related, unreported vectors (`Photo::move`, merge photo relinking).
- Trust boundary: an album owner's rights over foreign photos in their album can only be obtained through operations that already required equivalent rights on those photos.
- Links created before the fix are not cleaned up (indistinguishable from collaborator uploads).

## Operational Impact

- One migration adding and backfilling a boolean column.
- `/api/v3/Albums` gains a `can_edits` column via a join in the existing query; no extra query, cache key unchanged.

## Links

- Related spec sections: `docs/specs/4-architecture/features/072-edit-grant-escalation/spec.md` (FR-072-01..34, NG6–NG9)
- Related ADRs: ADR-0004 (multi-group permission merge)
- Related advisories: GHSA-pw32-v9r5-85hc, GHSA-jp9x-63pp-pv4v (embargoed)
