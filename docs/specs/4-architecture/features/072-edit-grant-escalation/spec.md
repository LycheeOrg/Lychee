# Feature 072 – Move Grant & Edit-Grant Escalation Fix

| Field | Value |
|-------|-------|
| Status | Implemented (backend + v8 UI); manual browser check pending |
| Last updated | 2026-09-25 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | Feature 072 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [open-questions.md](../../open-questions.md); resolved answers are encoded in the normative sections below.

## Overview

`Photo::copy`, `Photo::move`, `Album::move` and `Album::merge` were authorized with the edit grant only, although their effects go beyond editing: they change which albums contain a photo and, for albums, who owns a subtree or whether it still exists. Since album ownership confers full rights over every photo the album contains (`AlbumPolicy::canAccessFullPhoto()`/`canDownload()` short-circuit on it), the authorization of these operations must match their effect.

This feature does three things:

1. **Separates Move/Copy/Merge from Edit** with one new share grant, `grants_move` (owner direction; ADR-0011), so owners can let collaborators edit titles, tags, etc. without letting them reorganise content — and the reverse.
2. **Guards every operation that changes ownership or containment across owners**, so no grant combination can be escalated into full-photo access, download, ownership or deletion.
3. **Filters the destination picker** to albums the user may actually use as a target. Today it lists every *visible* album, including ones where every attempt fails.

Affected modules: persistence (`access_permissions` column + backfill), policies (new ability, new query condition), REST (4 action requests, sharing requests/resources, album target listings), v8 UI (sharing dialogs, target picker, context menus). The v7 UI keeps working but does not expose the new grant (it sends `grants_move = grants_edit`).

## Goals

1. An edit-only share can no longer be turned into full-photo access, download, ownership, or deletion.
2. Move, Copy and Merge are governed by a grant separate from Edit.
3. The destination picker only lists albums the user can target.
4. Existing collaborators keep their current reorganising workflows within the owner's albums.
5. Regression coverage for every scenario below.

## Non-Goals

- **NG1 — Edit does not imply full-photo access.** Rejected: it silently widens every existing edit share where the owner withheld originals, and does nothing for album ownership takeover or merge deletion.
- **NG2 — Photo ownership is not re-anchored.** Tying photo rights to the photo owner instead of the album owner would break album owners' access to collaborator uploads (uploads are owned by the uploader, not the album owner).
- **NG3 — No clean-up of existing cross-owner links.** Links already created through these endpoints cannot be told apart from legitimate ones (collaborator uploads also produce albums containing other users' photos).
- **NG4 — v7 UI does not expose the new grant.** v7 has no Move checkbox; in v7, move is the same right as edit, so every v7 share create/edit sends `grants_move` equal to `grants_edit` (same rule as the FR-072-02 backfill).
- **NG5 — `Album::transfer`, photo delete, and tag/person albums are unchanged.** Tag and person albums are not containers in `photo_album`, so they do not confer grants through `PhotoPolicy::reduction()`.
- **NG6 — Public/link shares never grant move.** The column is always `false` for public permissions and is not shown in the public-sharing UI.
- **NG7 — The move grant does not imply full-photo access or download** (Q-072-06). The share checkboxes stay independent.
- **NG8 — The picker is not source-aware** (Q-072-08). It filters on the target right only. Targets rejected by the cross-owner guards (FR-072-12, FR-072-14, FR-072-16) still appear and are refused with 403.
- **NG9 — No separate Copy or Merge grant** (Q-072-04). One `grants_move` covers all three; Merge additionally needs delete.

## Functional Requirements

"Non-owner" below means a user who is neither the owner of the item nor an admin. Admins keep bypassing every check (existing `Gate::before`).

### New grant

| ID | Requirement | Success path | Validation path | Failure path | Telemetry | Source |
|----|-------------|--------------|-----------------|--------------|-----------|--------|
| FR-072-01 | `access_permissions` gains one boolean `grants_move` column, default `false`. It governs Move, Copy and Merge. | — | — | — | — | Q-072-04 (A), ADR-0011 |
| FR-072-02 | Migration backfills existing user and group rows with `grants_move = grants_edit`; public rows keep `false` (NG6). | Existing edit collaborators keep reorganising within the owner's albums. | — | — | — | Q-072-07 (A) |
| FR-072-03 | The move grant covers an album's **content** (its photos and sub-albums), never the album itself. `AlbumPolicy::CAN_MOVE` on album P ("content of P may be moved out"): owner with `may_upload`, or a user/group permission on P with `grants_move = true`; smart/root albums follow `may_upload`. | — | — | — | — | ADR-0011, Q-072-09 (A) |
| FR-072-03b | `AlbumPolicy::CAN_MOVE_ALBUM` on album X ("X itself may be moved"): owner of X with `may_upload`, or a user/group permission on X's **parent** with `grants_move = true` (mirrors `canDelete()`). A root album can only be moved by its owner. | — | — | — | — | Q-072-09 (A) |
| FR-072-04 | `PhotoPolicy::CAN_MOVE` on a photo: photo owner with `may_upload`, or `AlbumPolicy::CAN_MOVE` on any album containing it (same reduction as `PhotoPolicy::canEdit()`). | — | — | — | — | ADR-0011 |
| FR-072-05 | `POST /api/v2/Sharing` (single and bulk) and `PATCH /api/v2/Sharing` require a `grants_move` boolean. `AccessPermissionResource` returns it. `PUT /api/v2/Sharing` (propagate to children) copies it like the other grants. | v8 sends its checkbox; v7 sends `grants_edit`. | Required boolean. | 422 when missing or non-boolean. | — | NG4 |
| FR-072-06 | `AccessPermission::withGrantFullPermissionsToUser()` sets `grants_move = true`; `ofPublic()`/`ofPublicHidden()` set it `false`. | — | — | — | — | NG6 |

### Operation authorization

| ID | Requirement | Success path | Validation path | Failure path | Telemetry | Source |
|----|-------------|--------------|-----------------|--------------|-----------|--------|
| FR-072-10 | `Photo::copy` requires `PhotoPolicy::CAN_MOVE` on every photo (replaces `CAN_EDIT`). | — | — | 403; no `photo_album` row written. | — | ADR-0011 |
| FR-072-11 | `Photo::move` requires `AlbumPolicy::CAN_MOVE` on `from_album` and `PhotoPolicy::CAN_MOVE` on every photo (replaces `CAN_EDIT`). | — | — | 403; no link removed or added. | — | ADR-0011 |
| FR-072-12 | **Cross-owner photo guard.** For `Photo::copy` and `Photo::move`, when the destination album's owner differs from the photo's owner, a non-owner of the photo must also hold `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` **and** `PhotoPolicy::CAN_DOWNLOAD` on it. Moving to root (unsorted) never triggers the guard: the photo lands in its owner's unsorted. | Same-owner reorganising needs only the move grant; a user who can already download the photo copies it into another owner's album. | — | 403. | — | Q-072-01 (A) |
| FR-072-13 | `Album::move` requires `AlbumPolicy::CAN_MOVE_ALBUM` on every source album, i.e. the move grant on its parent (replaces `CAN_EDIT`). Moving to root is allowed with `CAN_MOVE` alone; ownership does not change. | — | — | 403; nothing reparented. | — | ADR-0011 |
| FR-072-14 | **Cross-owner album guard.** `Album::move` with a non-null target: every source whose owner differs from the target's owner requires `AlbumPolicy::CAN_TRANSFER` (owner only). Subtree ownership is uniform (`Transfer` re-roots, `Move`/`Merge` call `fixOwnershipOfChildren()`), so checking the source covers its descendants. | Owner may still give their own album away by moving it into someone else's album. | — | 403; no `owner_id` changed. | — | — |
| FR-072-15 | `Album::merge` requires `AlbumPolicy::CAN_MOVE` (its content leaves it: grant on the source) **and** `AlbumPolicy::CAN_DELETE` (it is deleted: grant on its parent) on every source album. | — | — | 403; nothing relinked or deleted. | — | — |
| FR-072-16 | `Album::merge` applies FR-072-14's cross-owner guard to every source. This also closes the photo leak through merge. | Owner may merge their own album into someone else's album. | — | 403. | — | Triage |
| FR-072-17 | The **target** of all four operations keeps requiring `AlbumPolicy::CAN_EDIT` (root target: `may_upload`, unchanged). | — | — | 403. | — | Q-072-05 (A) |
| FR-072-18 | All checks run in each request's `authorize()`, before any mutation. A batch is all-or-nothing. Authorization is **aggregate** (Q-072-12): `AlbumPolicy::canMoveAlbumsById`/`canMoveContentById`/`canDeleteById`/`canDeleteContentById` and `PhotoPolicy::canMoveById`/`canAccessFullAndDownloadById` issue a fixed number of queries whatever the batch size; cross-owner guards read `owner_id` from the already-hydrated models. Dedicated traits: `AuthorizeCanMoveAlbumsTrait`, `AuthorizeCanMergeAlbumsTrait`, `AuthorizeCanMovePhotosTrait`, `GuardsCrossOwnerAlbumsTrait`; the legacy `AuthorizeCanEditPhotosAlbumTrait`/`AuthorizeCanEditAlbumAlbumsTrait` are deleted. | — | — | 403, database unchanged. | — | Q-072-12 (A) |

### Protection policy and delete

| ID | Requirement | Success path | Validation path | Failure path | Telemetry | Source |
|----|-------------|--------------|-----------------|--------------|-----------|--------|
| FR-072-40 | `POST /api/v2/Album::updateProtectionPolicy` requires `AlbumPolicy::CAN_CHANGE_PROTECTION_POLICY`: the album owner (admins via `before()`); built-in smart albums: admins only. Replaces `CAN_EDIT`, which let edit-only collaborators publish the owner's album with full-photo access and download. The v8 album drawer shows the Visibility section only when `rights.can_transfer` (same ownership rule). | Owner/admin changes visibility. | — | 403; nothing changed. | — | Q-072-10 (A) |
| FR-072-41 | `DELETE /api/v2/Album` (`AlbumPolicy::CAN_DELETE_ID`): each album is deletable by its owner (with `may_upload`) or with `grants_delete` on its **parent**; an album without parent (root, tag, person) only by its owner. Batch is all-or-nothing. Now consistent with `AlbumPolicy::canDelete()` and the move grant (content semantics). | — | — | 403. | — | Q-072-11 (A) |
| FR-072-42 | `DELETE /api/v2/Photo`: the user needs delete on **every** album containing each photo — as owner (with `may_upload`) or through `grants_delete` (`AlbumPolicy::canDeleteContentById()`). A single containing album without delete refuses the whole request. | — | — | 403. | — | Q-072-11 |

### Rights exposed to the UI

| ID | Requirement | Success path | Validation path | Failure path | Telemetry | Source |
|----|-------------|--------------|-----------------|--------------|-----------|--------|
| FR-072-20 | v2 `AlbumRightsResource`: `can_move` = `CAN_MOVE_ALBUM` (the album itself may be moved; today `CAN_DELETE`); new `can_move_content` = `CAN_MOVE` (its photos and sub-albums may be moved out; smart albums follow `may_upload`); new `can_merge` = regular album && `CAN_MOVE` && `CAN_DELETE`. v3 `AlbumRightsResource` (`/Albums/{id}/rights`, `/Albums/root/rights`, search rights): `can_move_children` (whole-response) now comes from `grants_move` on the parent instead of copying `can_delete_children`; new per-child `grants_move: bool[]` (the child's own content, for merge). v8's `adaptAlbumChildTile` derives `can_move = (isOwner && mayUpload) \|\| can_move_children`, `can_move_content = (isOwner && mayUpload) \|\| grants_move[i]`, `can_merge = can_move_content && can_delete`. Tag/person category tiles keep all three `false`. | — | — | — | — | ADR-0011, Q-072-09 |
| FR-072-21 | v8 gates: album **Move** (tile menu, album edit drawer, spotlight) on `can_move`; album **Merge** (single and multi-select) on `can_merge`; photo **Move**/**Copy** (context menus, photo dock, `m` key in Album and Search views) on the current album's `can_move_content` (today `can_edit`). | — | — | — | — | ADR-0011, Q-072-09 |
| FR-072-22 | v8 share UI (`ShareLine.vue`, `BulkSharingModal.vue`, `AlbumCreateShareDialog.vue`, `Sharing.vue`) shows a **Move** checkbox between Edit and Delete, independent of the other checkboxes. | — | — | — | — | Q-072-06 (A) |

### Destination picker

| ID | Requirement | Success path | Validation path | Failure path | Telemetry | Source |
|----|-------------|--------------|-----------------|--------------|-----------|--------|
| FR-072-30 | The picker lists only albums on which the user holds the target right (`CAN_EDIT`, FR-072-17), in addition to the existing exclusions (the sources' own subtrees; root offered only when the first source is not already at root). It is not source-aware (NG8). | — | — | — | — | Q-072-08 (C + filter) |
| FR-072-31 | The editability condition is one SQL helper on `AlbumQueryPolicy` (owner with `may_upload`, or user/group/public permission with `grants_edit`), equivalent to `AlbumPolicy::canEdit()` for regular albums. | — | — | — | — | NFR-072-02 |
| FR-072-32 | `GET /api/v2/Album::getTargetListAlbums` applies FR-072-31 to its query (opt-in `editable_only` on `ListAlbums`; `Sharing::albums` is unaffected). Used by the v7 UI and the v8 flag-off path. Its authorization requires `CAN_MOVE_ALBUM` on each source album (was `CAN_EDIT_ID`), so a move-granted collaborator can open the album move picker. | — | — | — | — | — |
| FR-072-33 | `GET /api/v3/Albums` (`AlbumListResource`) gains an always-present `can_edits: bool[]` column computed with FR-072-31 in the same query. `SearchTargetAlbum.vue`'s v3 path keeps only rows with `can_edits[i] = true`. The listing still returns every visible album (breadcrumbs need non-editable ancestors). | — | — | — | — | — |
| FR-072-34 | A 403 from a rejected target keeps today's dialog behaviour: the dialog closes on confirm and the global error handler shows the 403 toast. When the filtered list is empty, the dialog shows the existing "no target" state. | — | — | — | — | NG8 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-072-01 | No share gains data access it did not have (full-photo, download, ownership, deletion). | Security fix must only restrict data access. | Behavioural Change Register below. | — | Owner |
| NFR-072-02 | The picker's editability filter is a single SQL helper shared by v2 and v3; a test asserts it agrees with `AlbumPolicy::canEdit()` row by row. | One source of truth for rights. | Parity test (S-072-19). | `AlbumQueryPolicy`, `AlbumPolicy` | Design |
| NFR-072-03 | Guards are small pure helpers composed in `authorize()` (flat control flow). | AGENTS.md straight-line increments. | Code review. | — | AGENTS.md |
| NFR-072-04 | `can_edits` adds no extra query to `/api/v3/Albums` (join in the existing query). The listing cache is already per user, so its key does not change. | Listing is cached and hot. | Query-count test. | `ManagedCacheService` | — |
| NFR-072-05 | Existing `AlbumMoveTest`, `AlbumMergeTest`, `PhotoMoveTest`, `PhotoCopyTest`, `MoveOrDuplicateTest`, `MergeAlbumRequestTest` stay green once their fixtures grant `grants_move` where they rely on shared (non-owned) albums. Any other assertion change is a finding. | Proves legitimate use is unaffected. | Scoped `--filter=` runs. | — | — |
| NFR-072-07 | Authorization cost of move/copy/merge is independent of batch size: the grant-checking queries for 1 and N items are equal. Each `…ById` check agrees with its per-model policy counterpart (parity tests). | Owner: "We are aiming for speed, hydration is heavy." | `AggregateAuthorizationTest`. | `AlbumPolicy`, `PhotoPolicy` | Q-072-12 |
| NFR-072-06 | Licence headers, `===`, no `empty()`, snake_case, PSR-4; new translation keys in `lang/<locale>/*.php` for all locales, then `php artisan lang:json`; regenerate TS types. | Conventions. | `php-cs-fixer`, `make phpstan`, `npm run check`, `LangTest`. | — | coding-conventions.md |

## Branch & Scenario Matrix

Fixture: **V** (owner) owns album **VA** (with unshared child **VC**, photo **P**) and album **VB**. **A** (collaborator, `may_upload`) owns album **AA**. V shares VA and VB with A. "Edit-only" = `grants_edit=true`, all other grants `false`.

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-072-01 | Edit-only on VA: A copies P into AA → 403. |
| S-072-02 | Same with `secure_image_link_enabled=true` → 403. |
| S-072-03 | Edit-only on VA: A moves P from VA into AA → 403. |
| S-072-04 | Edit-only on VA and VB: A moves P from VA to VB → 403 (no move grant). |
| S-072-05 | Move on VA, edit on VB: A moves P from VA to VB → 204 (same owner). |
| S-072-06 | Move on VA: A copies P into AA → 403 (cross-owner, no full/download). |
| S-072-07 | Move + full + download on VA: A copies P into AA → 204. |
| S-072-08 | Edit-only on VA: A moves VA under AA → 403; VA/VC keep `owner_id = V`; VC details still 403 for A. |
| S-072-09 | Move on VA: A moves VC under AA → 403 (cross-owner needs `CAN_TRANSFER`). |
| S-072-10 | Move on VA: A moves VC to root → 204, owner unchanged. Move on VC itself: 403 (Q-072-09). Move on VA: moving VA itself → 403 (root album, owner only). |
| S-072-11 | Edit-only on VA: A merges VA into AA → 403; VA/VC still exist. |
| S-072-12 | Move + delete on VA: A merges VA into AA → 403 (cross-owner); AA gains no link to P. |
| S-072-13 | Move + delete on VA, edit on VB: A merges VA into VB → 204 (same owner). |
| S-072-14 | A merges own album AB into VB (edit on VB) → 204; AB's content now V's (gift). |
| S-072-15 | Batch: A copies [own photo, P] into AA → 403 for the whole batch. |
| S-072-16 | Admin performs S-072-01, S-072-08, S-072-11 → 204 each. |
| S-072-17 | `getTargetListAlbums` and `/api/v3/Albums` `can_edits` for A: VB (edit) and AA (own) targetable; an album V shares with A read-only is visible but not targetable. |
| S-072-18 | Move on VA, no full/download: AA is listed in the photo copy picker (A can edit AA), and copying P into it returns 403 (NG8). |
| S-072-19 | Parity: for every album in the fixture, the SQL editability condition equals `AlbumPolicy::canEdit()` (owner with/without `may_upload`, user grant, group grant, public grant). |
| S-072-20 | Share create or edit without `grants_move` is rejected (422). |
| S-072-21 | `AlbumRightsResource`: edit-only → `can_move=false`, `can_merge=false`; move only → `can_move=true`, `can_merge=false`; move + delete → both `true`. v3 `/rights` returns the matching `grants_move[i]`. |
| S-072-22 | Residual path (accepted, Q-072-01 A): move on VA, edit on VB where VB is shared publicly with full access; A moves P from VA to VB → 204. |
| S-072-23 | A owns photo Q uploaded into VA (collaborator upload); A copies Q into AA → 204 (A owns the photo; guard does not apply). |
| S-072-24 | Move on VA, no full/download: A moves P from VA to root → 204; P is in V's unsorted. |

## Behavioural Change Register

| Surface | Before | After | Direction |
|---------|--------|-------|-----------|
| `Photo::copy` | edit on photo + destination | move grant on photo; cross-owner guard | **restricts** |
| `Photo::move` | edit on photo, source, destination | move grant on photo + source; cross-owner guard | **restricts** |
| `Album::move` | edit on sources + target | move grant on sources; owner across owners | **restricts** |
| `Album::merge` | edit on sources + target | move + delete on sources; owner across owners | **restricts** |
| `Album::updateProtectionPolicy` | edit on album (smart: upload) | owner (smart: admin) | **restricts** |
| `DELETE /Album` | delete grant on the album itself | delete grant on its parent; root album: owner only | **restricts** deleting a shared root album; **relaxes** deleting a sub-album of an album shared with delete (matches what the UI already offered) |
| Destination picker | every *visible* album | only albums the user can edit | **restricts** (removes targets that always failed) |
| `can_move` (UI) | `CAN_DELETE` | `CAN_MOVE` | a user with delete but no move grant loses Move/Merge; a user with move but no delete gains Move (not Merge) |
| Existing shares | — | `grants_move = grants_edit` | unchanged for same-owner reorganising |

## UI / Interaction Mock-ups

### Share line (v8 `ShareLine.vue`, `BulkSharingModal.vue`, `AlbumCreateShareDialog.vue`, `Sharing.vue`)

A new, independent **Move** checkbox between Edit and Delete. Column header tooltip: "Move, copy and merge".

```
┌───────────────────────────────────────────────────────────────────────────┐
│ User            👁   ⤢Full  ⬇DL   ⬆Up   ✎Edit  ⇄Move  🗑Del               │
│ ───────────────────────────────────────────────────────────────────────── │
│ alice           [✓]   [ ]    [ ]   [ ]    [✓]    [ ]    [ ]      [👤−]    │
│ bob             [✓]   [ ]    [ ]   [ ]    [✓]    [✓]    [ ]      [👤−]    │
│ carol           [✓]   [✓]    [✓]   [✓]    [✓]    [✓]    [✓]      [👤−]    │
└───────────────────────────────────────────────────────────────────────────┘
  alice: edits metadata only · bob: also reorganises, never gets originals
```

### Destination picker (Move / Copy / Merge dialogs)

Only albums the user can edit are offered. A target refused by a cross-owner guard closes the dialog (as today) and the existing error toast appears.

```
┌─ Copy 3 photos ───────────────────────────────┐
│  Search album…  [                          ]  │
│  ─────────────────────────────────────────────│
│  ▸ Holidays / 2025          (editable)        │
│  ▸ Holidays / 2026          (editable)        │
│  ▸ My album                 (own)             │
│    (read-only shared albums are not listed)   │
│  ─────────────────────────────────────────────│
│                         [ Cancel ]  [ Copy ]  │
└───────────────────────────────────────────────┘

Refused target (e.g. copying another user's photos into "My album"
without full-photo + download access):

┌──────────────────────────────────────────────┐
│  ⚠  <existing error toast, 403 message>       │
└──────────────────────────────────────────────┘
```

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-072-01 | `access_permissions.grants_move` column + `APC::GRANTS_MOVE`; `AccessPermission` casts/fillable; factory state `grants_move()`; `computed_access_permissions` sub-query carries it. | persistence |
| DO-072-02 | `AlbumPolicy::CAN_MOVE`, `PhotoPolicy::CAN_MOVE`. | policies |
| DO-072-03 | `AlbumQueryPolicy` editability condition helper (FR-072-31). | policies |

### API Routes / Services

| Route | Change |
|-------|--------|
| `POST /api/v2/Photo::copy` | FR-072-10, FR-072-12 |
| `POST /api/v2/Photo::move` | FR-072-11, FR-072-12 |
| `POST /api/v2/Album::move` | FR-072-13, FR-072-14 |
| `POST /api/v2/Album::merge` | FR-072-15, FR-072-16 |
| `POST`/`PATCH`/`PUT /api/v2/Sharing` | FR-072-05 |
| `GET /api/v2/Album::getTargetListAlbums` | FR-072-32 |
| `GET /api/v3/Albums` | FR-072-33 (`can_edits`) |
| Album rights (v2 `AlbumRightsResource`, v3 rights tiers) | FR-072-20 (`can_move`, `can_merge`) |

### Telemetry Events

None.

## Documentation Deliverables

- roadmap.md entry; knowledge-map note on "album ownership confers photo rights" and the endpoints guarding it.
- [ADR-0011](../../../6-decisions/ADR-0011-move-grant-separate-from-edit.md): move grant separate from edit, and the cross-owner guard rule.
- `docs/specs/3-reference/api-design.md`: `grants_move` on sharing, `can_edits` on `/api/v3/Albums`, `can_merge` on rights.

## Spec DSL

```yaml
domain_objects:
  - {id: DO-072-01, name: access_permissions.grants_move, new: true}
  - {id: DO-072-02, name: CAN_MOVE abilities, new: true}
  - {id: DO-072-03, name: AlbumQueryPolicy editability condition, new: true}
routes:
  - {method: POST, path: /api/v2/Photo::copy, change: authorization}
  - {method: POST, path: /api/v2/Photo::move, change: authorization}
  - {method: POST, path: /api/v2/Album::move, change: authorization}
  - {method: POST, path: /api/v2/Album::merge, change: authorization}
  - {method: POST, path: /api/v2/Sharing, change: required grants_move}
  - {method: PATCH, path: /api/v2/Sharing, change: required grants_move}
  - {method: PUT, path: /api/v2/Sharing, change: propagates grants_move}
  - {method: GET, path: /api/v2/Album::getTargetListAlbums, change: editable-only filter}
  - {method: GET, path: /api/v3/Albums, change: can_edits column}
telemetry_events: []
open_questions: []
```
