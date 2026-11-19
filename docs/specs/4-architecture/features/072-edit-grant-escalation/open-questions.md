# Open Questions – Feature 072

Open questions for [Feature 072](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-072-13~~ | 072 – Edit-Grant Escalation | High | Pre-release details in commits already pushed to a public branch: how are they removed? | Resolved (Option C — removed from the tree only; the PR is squash-merged and the branch deleted; owner, 2026-09-26). | 2026-09-26 | 2026-09-26 |
| ~~Q-072-01~~ | 072 – Edit-Grant Escalation | High | `Photo::copy`/`Photo::move` by a non-owner: when must the user already hold full-photo access + download on the photo? Only when the destination belongs to someone other than the photo's owner, or always? | Resolved (Option A — guard applies only when destination owner ≠ photo owner; owner, 2026-09-25: "Q-72-1: A"). Spec FR-072-12, S-072-22..24; ADR-0011. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-02~~ | 072 – Edit-Grant Escalation | High | ~~`Album::move` within one owner's albums: keep `CAN_EDIT`, or also require `CAN_DELETE`?~~ | Superseded (owner, 2026-09-25: "separate Move/Copy/Merge from Edit") — sources now require the new move grant (FR-072-13); neither edit nor delete. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-03~~ | 072 – Edit-Grant Escalation | Medium | ~~`Photo::move` source: keep `CAN_EDIT`, or require `CAN_DELETE`?~~ | Superseded (same owner direction) — `from_album` now requires the new move grant (FR-072-11). | 2026-09-25 | 2026-09-25 |
| ~~Q-072-04~~ | 072 – Edit-Grant Escalation | High | Shape of the new grant: one `grants_move` covering Move/Copy/Merge, or separate flags? | Resolved (Option A — one `grants_move` for Move/Copy/Merge; Merge also needs delete; owner, 2026-09-25). Spec FR-072-01, NG9; ADR-0011. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-05~~ | 072 – Edit-Grant Escalation | High | Which right is required on the **target** album of Move/Copy/Merge? | Resolved (Option A — target keeps `CAN_EDIT`; owner, 2026-09-25). Spec FR-072-17; ADR-0011. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-06~~ | 072 – Edit-Grant Escalation | High | Must enabling the move grant also enable full-photo access and download on the share? | Resolved (Option A — no coupling; owner, 2026-09-25). Spec NG7, FR-072-22; ADR-0011. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-07~~ | 072 – Edit-Grant Escalation | High | Backfill of `grants_move` for existing shares. | Resolved (Option A — backfill `grants_move = grants_edit`; owner, 2026-09-25). Spec FR-072-02; ADR-0011. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-08~~ | 072 – Edit-Grant Escalation | High | How does the destination picker learn the valid targets for a given selection? | Resolved (owner, 2026-09-25: "C however the destination album needs to be filtered") — no source-aware endpoint; picker filtered to albums the user can edit (target right), cross-owner rejections surface as 403. Spec FR-072-30..34, NG8; ADR-0011. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-09~~ | 072 – Edit-Grant Escalation | High | Meaning of `grants_move` on album P: "content of P (sub-albums, photos) may be moved out of P" — not "P itself may be moved". Album::move currently checks the grant on the moved album itself; should check its parent. | Resolved (Option A — content semantics; owner, 2026-09-25: "yes that is the intent"). Spec FR-072-03/03b/13/20/21; ADR-0011; docs/specs/1-concepts/permissions.md. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-10~~ | 072 – Edit-Grant Escalation | High | Should the album protection policy (`SetAlbumProtectionPolicyRequest`, today `CAN_EDIT`) require ownership, like per-user sharing? | Resolved (Option A — ownership required; owner, 2026-09-25: "Yes ownership required."). Spec FR-072-40; ADR-0011. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-11~~ | 072 – Edit-Grant Escalation | Medium | Delete is inconsistent: `DELETE /Album` checks `grants_delete` on the album itself (`canDeleteById`), while the UI's `can_delete` and merge check it on the parent (`canDelete`). Which is intended? | Resolved (owner, 2026-09-25: album delete checks grants_delete on the parent; photo delete on the containing album). Spec FR-072-41/42; ADR-0011. | 2026-09-25 | 2026-09-25 |
| ~~Q-072-12~~ | 072 – Edit-Grant Escalation | High | Move/copy/merge authorization runs per hydrated album/photo (`Gate::check` loops, per-album permission loads, per-album parent-grant queries): query count grows with batch size. Rewrite as aggregate `…ById` SQL checks? | Resolved (Option A — aggregate `…ById` checks; owner, 2026-09-25). Spec FR-072-18, NFR-072-07. | 2026-09-25 | 2026-09-25 |

## Question Details

### ~~Q-072-13~~ · Removing pre-release details from public history ✅ RESOLVED

**Status:** Resolved (Option C; owner, 2026-09-26: the PR is squash-merged and the branch deleted at merge). Details removed from the tree; history left as is.

**Context.** Feature 072's early commits were pushed to a public branch with an open PR. They contain advisory identifiers and reproduction details that must stay private until release. Deleting them in a new commit is not enough: the old commits stay reachable by SHA and through the PR's refs.

**Option A (recommended) — move the work to the advisory's private fork.** Sanitize the tree, squash the branch into the advisory's temporary private fork, close the public PR, delete the public branch, and ask GitHub Support to purge the PR and the exposed commits.
- ✅ Full details stay private until release; the fix is published together with the advisory.
- ❌ Needs GitHub Support for the purge; review moves to the private fork.

**Option B — rewrite the public branch.** Sanitize, rewrite the branch as clean commit(s), force-push, close the PR and open a new one; ask GitHub Support to purge the old PR and commits.
- ✅ Review stays public and on the usual flow.
- ❌ The fix stays public before release (it still hints at the issue); force-push to a shared branch.

**Option C — sanitize going forward only.** Remove the details in a new commit and release quickly.
- ✅ No history rewrite.
- ❌ The exposed commits stay public: the finding is not addressed.

---

### ~~Q-072-01~~ · When does a non-owner need full access + download to copy/move a photo? ✅ RESOLVED

**Status:** Resolved (Option A; owner, 2026-09-25: "Q-72-1: A"). Spec FR-072-12; residual path accepted as S-072-22; ADR-0011.  
**Feature:** F-072 – Fix Edit-Grant Escalation via Copy, Move and Merge  
**Priority:** High

**Context**  
Owning an album gives full-photo access and download on every photo in it, so placing a photo into an album of another owner must require those rights on the photo. The fix requires `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` and `CAN_DOWNLOAD` on the photo; the question is when.

**Option A (recommended) — only when the destination's owner differs from the photo's owner**
- ✅ Copying into your own album needs rights you already have.
- ✅ Move-granted collaborators can still reorganise photos between the owner's own albums without full access.
- ❌ Residual path: a collaborator with the move grant on two of the owner's albums can move a photo from a restricted album into a public, full-access album of the same owner. That is the owner's own sharing setup, but it may surprise them.

**Option B — always, for any non-owner**
- ✅ Simplest rule; no residual path.
- ❌ The move grant becomes useless without full access + download; "reorganise without originals" is impossible.

**Option C — forbid cross-owner copy/move entirely for non-owners**
- ✅ Simple, no policy lookups beyond ownership.
- ❌ Blocks a legitimate case: a user who can already download a photo copying it into their own album.

---

### ~~Q-072-04~~ · One move grant, or several? ✅ RESOLVED

**Status:** Resolved (Option A — one `grants_move` for Move/Copy/Merge; Merge also needs delete; owner, 2026-09-25). Spec FR-072-01, NG9; ADR-0011.  
**Feature:** F-072  
**Priority:** High

**Context**  
Owner direction: separate Move/Copy/Merge from Edit for more granularity. The share line already has six checkboxes (read, full, download, upload, edit, delete).

**Option A (recommended) — one `grants_move` covering Move, Copy and Merge**
- ✅ One column, one checkbox, one policy ability; easy to explain ("may reorganise").
- ✅ Merge still also needs delete (it deletes the source), so the destructive part keeps its own gate.
- ❌ Cannot allow Copy while forbidding Move.

**Option B — `grants_move` (Move, Merge) + `grants_copy` (Copy)**
- ✅ Copy never removes anything from the owner's albums, so it can be granted more freely.
- ❌ Two columns and checkboxes; after the cross-owner guard, Copy and Move carry the same leak risk, so the split buys little security.

**Option C — three flags (Move, Copy, Merge)**
- ✅ Maximum granularity.
- ❌ Merge already requires delete; a third flag mostly duplicates "move + delete". Eight checkboxes per share line.

---

### ~~Q-072-05~~ · Which right is required on the target album? ✅ RESOLVED

**Status:** Resolved (Option A — target keeps `CAN_EDIT`; owner, 2026-09-25). Spec FR-072-17; ADR-0011.  
**Feature:** F-072  
**Priority:** High

**Context**  
Today the target of all four operations needs `CAN_EDIT`. Adding photos to an album is otherwise governed by `CAN_UPLOAD` (`UploadPhotoRequest`); adding a sub-album by `CAN_EDIT` (`AddAlbumRequest`).

**Option A (recommended) — keep `CAN_EDIT` on the target**
- ✅ No change to who can be a target; smallest behavioural change.
- ✅ Matches how sub-albums are created today.
- ❌ A user with upload but no edit on an album can upload into it but not move photos into it.

**Option B — `CAN_UPLOAD` for photo targets, `CAN_EDIT` for album targets**
- ✅ Consistent with the existing meaning of "may add photos".
- ❌ Changes who can be a photo target in both directions (upload-only gains, edit-only-without-upload loses).

**Option C — the move grant on the target too**
- ✅ One grant governs both ends.
- ❌ Owners must grant move on every album that should receive content; confusing with upload.

---

### ~~Q-072-06~~ · Must the move grant imply full-photo access and download? ✅ RESOLVED

**Status:** Resolved (Option A — no coupling; owner, 2026-09-25). Spec NG7, FR-072-22; ADR-0011.  
**Feature:** F-072  
**Priority:** High

**Context**  
Your initial idea was to force full-photo access when Move/Merge is enabled. With the cross-owner guards (FR-072-12, FR-072-14, FR-072-16), content can only leave the owner's albums if the user already has full access + download (photos) or owns it (albums), so the coupling is no longer needed for security.

**Option A (recommended) — no coupling**
- ✅ Owners can let a collaborator reorganise without handing out originals, which is exactly the granularity this feature adds.
- ✅ Security holds through the guards, not through share configuration.
- ❌ Collaborators with move but no full access cannot copy the owner's photos into their own albums.

**Option B — ticking Move forces Full + Download (UI and server validation)**
- ✅ A move-granted collaborator can always copy into their own albums.
- ❌ Removes the "reorganise without originals" combination; couples checkboxes, which is confusing.
- ❌ Still does not allow cross-owner **album** moves (ownership transfer stays owner-only).

---

### ~~Q-072-07~~ · Backfill `grants_move` for existing shares? ✅ RESOLVED

**Status:** Resolved (Option A — backfill `grants_move = grants_edit`; owner, 2026-09-25). Spec FR-072-02; ADR-0011.  
**Feature:** F-072  
**Priority:** High

**Option A (recommended) — `grants_move = grants_edit` for existing rows**
- ✅ Collaborators keep reorganising the owner's albums as today; only the cross-owner escalations disappear.
- ✅ No surprise for owners who relied on edit collaborators curating.
- ❌ Owners who never meant to allow reorganising must untick Move afterwards.

**Option B — `false` for all existing rows**
- ✅ Strictest: every move right must be granted explicitly.
- ❌ Every existing edit collaborator silently loses Move/Copy/Merge on upgrade.

**Option C — `grants_move = grants_edit AND grants_delete`**
- ✅ Matches what the UI already showed as `can_move` (delete-gated), so visible behaviour is preserved for UI users.
- ❌ `can_move` was delete-on-parent, not delete-on-this-share, so the mapping is only approximate.

---

### ~~Q-072-08~~ · How does the picker learn the valid targets? ✅ RESOLVED

**Status:** Resolved (owner, 2026-09-25: "C however the destination album needs to be filtered") — no source-aware endpoint; picker filtered to albums the user can edit (target right), cross-owner rejections surface as 403. Spec FR-072-30..34, NG8; ADR-0011.  
**Feature:** F-072  
**Priority:** High

**Context**  
v2 `GET Album::getTargetListAlbums` takes `album_ids` and applies only a reachability filter (it already lists albums the user cannot edit). The v8 flag-on path builds the list client-side from `GET /api/v3/Albums` (visibility-filtered, no per-album rights) via `AlbumListState.getExcludedTargetIds()`. Valid targets now depend on the sources' owners and on per-photo full/download rights.

**Option A (recommended) — server-side, source-aware target list**
- v2 `getTargetListAlbums` gains `photo_ids[]` (+ optional `from_album_id`) and filters with the same policy code as the action requests.
- New v3 `GET /api/v3/Albums/targets?album_ids[]=…|photo_ids[]=…` returns `{ ids: string[], can_target_root: bool }`; `SearchTargetAlbum.vue` intersects `albumListStore.rows` with `ids`, keeping breadcrumbs/covers from the store.
- ✅ Rules live in one place (NFR-072-02); list/authorize parity is testable (S-072-19).
- ❌ One extra request when a dialog opens.

**Option B — per-row flags on `/api/v3/Albums` + client-side rules**
- Add `owner_ids`/`can_edits` to the listing and re-implement the cross-owner rules in TypeScript.
- ✅ No extra request.
- ❌ Duplicates authorization in TS; the photo rule needs per-photo full/download grants that the listing cannot carry. Drift risk.

**Option C — keep the picker unfiltered; rely on 403**
- ✅ No UI change.
- ❌ Users keep picking targets that always fail; rejected by owner ("this will require a UI change").

---

### ~~Q-072-10~~ · Should the protection policy require ownership? ✅ RESOLVED

**Status:** Resolved (owner, 2026-09-25). Spec FR-072-40.  
**Feature:** F-072  
**Priority:** High

**Context**  
Found while documenting permissions. `SetAlbumProtectionPolicyRequest::authorize()` only checks `AlbumPolicy::CAN_EDIT`, yet the action sets the album's public permission row: `is_public`, `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload`, password. Changing it is a sharing decision, which elsewhere is reserved to the owner.

**Option A (recommended) — protection policy requires ownership**
- Use `AlbumPolicy::CAN_SHARE_WITH_USERS`-style ownership (owner or admin), like per-user sharing already is.
- ✅ Sharing decisions stay with the owner, consistently for public and per-user shares.
- ❌ Edit collaborators can no longer toggle NSFW or change the password.

**Option B — edit may change NSFW/password, but not widen public grants**
- Owner required whenever a grant or visibility would become more permissive.
- ✅ Keeps harmless edits available.
- ❌ More branching; the password and link-required flags still affect access.

**Option C — keep as is, document it**
- ❌ Leaves a known escalation open.

---

### ~~Q-072-12~~ · Aggregate authorization for move/copy/merge ✅ RESOLVED

**Status:** Resolved (Option A; owner, 2026-09-25). Spec FR-072-18, NFR-072-07.  
**Feature:** F-072  
**Priority:** High (owner: "We are aiming for speed, hydration is heavy.")

**Context**  
The four request classes authorize by looping `Gate::check()` over hydrated models: `current_user_permissions()` loads each album's permission rows, `canMoveAlbum()` issues one query per album, `PhotoPolicy::canMove()`/`canAccessFullPhoto()`/`canDownload()` walk each photo's albums. The replaced traits had the same shape. The actions themselves (`Move`, `Merge`, `MoveOrDuplicate`) still need the models, so hydration of sources/target stays; the authorization overhead is what can go.

**Option A (recommended) — aggregate `…ById` checks, constant query count**
- `AlbumPolicy`: one private helper "grant X on the parent of every non-owned album" (shared by `canDeleteById` and a new `canMoveAlbumsById`), one helper "grant X on every album" (shared by `canDeleteContentById` and a new `canMoveContentById`).
- `PhotoPolicy::canMoveById()`: owner shortcut + one `EXISTS` query "every non-owned photo has a containing album granting move" (copy); `Photo::move` checks `canMoveContentById([from_album])`.
- Cross-owner guards read `owner_id` from rows the checks already fetch; photo full-access + download via the existing batched `ResolvesPhotoGrants` (one grouped query).
- Query-count tests: authorization cost independent of batch size.
- Delete the two unused legacy traits.
- ✅ Fixed, small number of queries per request; mirrors the existing `…ById` pattern.
- ❌ Policy logic duplicated between model-based (`canMove`, UI rights) and `…ById` variants; parity tests needed.

**Option B — keep per-model checks, only eager-load permissions**
- ✅ Smaller change.
- ❌ Still grows with batch size (parent-grant and per-photo grant lookups).

---

### ~~Q-072-11~~ · Is `grants_delete` about the album's content or the album itself? ✅ RESOLVED

**Status:** Resolved (owner, 2026-09-25). Spec FR-072-41/42.  
**Feature:** F-072  
**Priority:** Medium

**Context**  
`AlbumPolicy::canDelete()` (UI `can_delete`, merge) checks `grants_delete` on the album's **parent**: content semantics, like the move grant (Q-072-09). `AlbumPolicy::canDeleteById()` (used by `DELETE /api/v2/Album`) checks it on the **album itself**. So `grants_delete` on album P lets a collaborator delete P through the API, while the UI only offers deleting P's sub-albums. Photo delete checks the grant on the containing album (content semantics).

**Option A (recommended) — content semantics everywhere**
- `canDeleteById()` checks the grant on each album's parent (root album: owner only), like `canDelete()` and the move grant.
- ✅ One meaning for delete and move; the UI and API agree.
- ❌ Collaborators with delete on P lose the ability to delete P itself through the API.

**Option B — keep both**
- ❌ The UI and API disagree about the same grant.

---

### ~~Q-072-09~~ · Does `grants_move` on an album cover its content, or the album itself? ✅ RESOLVED

**Status:** Resolved (Option A; owner, 2026-09-25). Spec FR-072-03/03b/13/20/21; ADR-0011.  
**Feature:** F-072  
**Priority:** High

**Context**  
Owner, 2026-09-25: "`can_move` on an album means its sub-albums and photos can be moved from this album. It does NOT mean that this album can be moved." Photo move/copy and merge already follow this; `Album::move`, the album-move picker authorization and the UI "move this album" gates currently check the grant on the moved album itself.

**Option A (recommended, owner's stated intent) — container semantics everywhere**
- `Album::move` of X requires `grants_move` on X's parent (root album: owner only), mirroring how `grants_delete` on the parent governs deleting X.
- Album rights expose two flags: "can move this album" (grant on its parent) and "can move content out of this album" (grant on the album).
- ✅ One consistent meaning, same shape as delete.
- ❌ Rework of Album::move authorization, target-list authorization, v2/v3 rights and their tests.

**Option B — keep the mixed meaning**
- ❌ The same grant means different things for photos and albums.

---

### ~~Q-072-02~~ · Should every `Album::move` require delete on the source? ⤴ SUPERSEDED

**Superseded** by owner direction, 2026-09-25: "I still want to separate Move/Copy/Merge from Edit." Album sources require the new move grant (spec FR-072-13). Text kept below for history.

**Status:** Open  
**Feature:** F-072 – Fix Edit-Grant Escalation via Copy, Move and Merge  
**Priority:** High

**Context**  
Cross-owner moves will require `CAN_TRANSFER` (FR-072-03) regardless. This question is about moves that do not change ownership (same owner, or to root). Moving an album takes it out of its parent, which is currently never checked. The UI already hides Move and Merge unless `can_move`, which `AlbumRightsResource` computes as `CAN_DELETE`; the server only checks `CAN_EDIT`.

**Option A (recommended) — require `CAN_DELETE` on each source**
- ✅ Server matches what the UI already enforces; no visible change for UI users.
- ✅ Removing an album from its parent needs the same right as deleting it from there.
- ✅ Existing `testMoveAlbumAuthorizedUser` holds `grants_delete` on the parent and stays green.
- ❌ Direct API clients with edit-only shares lose same-owner moves.

**Option B — keep `CAN_EDIT` for same-owner moves**
- ✅ No change beyond the cross-owner rule.
- ❌ Server and UI keep disagreeing; an edit-only collaborator can still detach the owner's album from its parent.

---

### ~~Q-072-03~~ · Should `Photo::move` require delete on the source album? ⤴ SUPERSEDED

**Superseded** by the same owner direction. `from_album` requires the new move grant (spec FR-072-11). Text kept below for history.

**Status:** Open  
**Feature:** F-072 – Fix Edit-Grant Escalation via Copy, Move and Merge  
**Priority:** Medium

**Context**  
`Photo::move` removes the photo from `from_album`. Once Q-072-01 applies, a user without full access + download can no longer move the photo into another owner's album, so a move either lands in another album of the photo's owner or in the owner's unsorted (move to root). Nothing is lost or leaked either way.

**Option A (recommended) — keep `CAN_EDIT` on the source**
- ✅ Nothing leaves the owner's space after Q-072-01; this is reorganising, not deleting.
- ✅ No change for collaborators who curate shared albums today.
- ❌ An edit-only collaborator can empty a shared album into the owner's unsorted.

**Option B — require `CAN_DELETE` on `from_album`**
- ✅ Removing a photo from an album needs the same right as deleting it from there.
- ❌ Photo move becomes unavailable to edit-only collaborators; the UI gates it on `can_edit` today, so the menu would offer an action that then fails unless the UI is changed too.
