# Open Questions – Feature 118

Open questions for [Feature 118](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-118-01~~ | 118 | High | What grants sharing with users once `may_upload` no longer does: (A) ownership alone, (B) a new `may_share` flag | Resolved (B, owner 2026-11-24, spec FR-118-01 … 04, FR-118-17 … 21) | 2026-11-24 | 2026-11-24 |
| ~~Q-118-02~~ | 118 | High | Whether owners without upload rights may edit and organise their own albums and photos: (A) no, (B) metadata only, (C) everything | Resolved (D, owner 2026-11-24: a separate right, spec FR-118-08 … 12, FR-118-17 … 21) | 2026-11-24 | 2026-11-24 |
| ~~Q-118-03~~ | 118 | High | Owners upload into their own albums without `may_upload` today: (A) require `may_upload`, (B) keep | Resolved (A, owner 2026-11-24, spec FR-118-13) | 2026-11-24 | 2026-11-24 |
| ~~Q-118-04~~ | 118 | Medium | Import from server without upload rights: (A) `server_path` alone grants it, (B) keep FR-115-03 | Resolved (A, owner 2026-11-24, spec FR-118-14, FR-118-15) | 2026-11-24 | 2026-11-24 |
| ~~Q-118-05~~ | 118 | Medium | Smart albums that need upload rights (Unsorted, Recent, On this day …): (A) unchanged, out of scope, (B) shown to users who own a photo | Resolved (B, owner 2026-11-24, spec FR-118-16, NFR-118-03) | 2026-11-24 | 2026-11-24 |
| ~~Q-118-06~~ | 118 | Medium | Transfer and the user picker under `may_share`: (A) transfer needs `may_share`, picker = `may_share`, (B) transfer stays owner-only, picker = `may_share` or owns an album | Resolved (A, owner 2026-11-25, spec FR-118-04, ADR-118-01) | 2026-11-24 | 2026-11-25 |
| ~~Q-118-07~~ | 118 | High | Handing out grants one does not hold (`grants_upload` without `may_upload`, `grants_edit`/`move`/`delete` without `may_edit`): (A) refused, (B) recipient must hold the right, (C) allowed | Resolved (A, owner 2026-11-25, spec FR-118-07, ADR-118-01) | 2026-11-24 | 2026-11-25 |
| ~~Q-118-08~~ | 118 | Medium | Single-photo edits by the owner need no flag today, batch edits need `may_upload`: (A) both need `may_edit`, (B) owner always edits own photos' metadata, (C) unchanged | Resolved (A, owner 2026-11-25, spec FR-118-10, ADR-118-01) | 2026-11-24 | 2026-11-25 |

## Question Details

### ~~Q-118-01~~ — What grants sharing with users

**Context.** Sharing an album with users or groups (`AlbumPolicy::canShareWithUsers()`, `canShareById()`) needs ownership **and** `may_upload`. The pickers the share dialog and the Sharing page use need `may_upload` too (`UserPolicy::canList()`, `UserGroupPolicy::canList()`, which also admits group admins). Public visibility (`canChangeProtectionPolicy()`) and transfer (`canTransfer()`) already need ownership only. In the setup of [discussion #4982](https://github.com/LycheeOrg/Lychee/discussions/4982) the admin imports each user's NAS folder as that user, and the users must share their albums without being able to upload.

**Option A (recommended): ownership alone.** Drop `may_upload` from `canShareWithUsers()` and `canShareById()`. The user and group pickers admit `may_upload` or the owner of at least one album (group admins as today). No schema change.
- Pros: one rule for every sharing decision (public, users, groups, transfer). No migration, form, CLI or translation change. The admin still decides who owns what.
- Cons: no way to have a user who uploads but may not share. A user whose upload right was revoked keeps sharing the albums they own (they can already make them public today).

**Option B: a new per-user `may_share` flag.** New column `users.may_share`, set from `may_upload` by the migration. It gates sharing with users, sharing by ID, the pickers and public visibility. Set by admins in the v7/v8 user forms, `lychee:create_user`/`lychee:update_user`, and for new accounts (registration, OAuth, LDAP) by a new `grant_new_user_share_rights` setting.
- Pros: explicit control both ways (share without upload, upload without share). Nothing changes on upgrade.
- Cons: migration, both user forms and the users list, `UserManagementResource`, two requests, two actions, two commands, three account-creation paths, a setting, 23 languages. Putting public visibility under the flag takes it away from owners without upload rights who have it today.

Owner selected B on 2026-11-24. The flag is backfilled from `may_upload` and gates sharing with users, sharing by ID, the pickers and public visibility. `lychee:update_user` takes no rights options today, so only `lychee:create_user` gains `--may-share` (adjusted locally). Transfer and the picker for it: Q-118-06. Normative outcome: spec FR-118-01 … 04, FR-118-17 … 21.

### ~~Q-118-02~~ — Editing and organising own content without upload rights

**Context.** Every owner change to content needs `may_upload`: album `canEdit`, `canMove`, `canMoveAlbum`, `canDelete` and their batch forms, `PhotoPolicy` move/edit/delete by ID, `AlbumQueryPolicy::appendEditableCondition()`, `AlbumListController::canEdit()`, `TagPolicy::canEdit()`, renamer rules. Album creation needs `CAN_EDIT` on the parent. In the reported setup the NAS folders are the source of truth and Lychee mirrors them through `lychee:sync`.

**Option A (recommended): keep them tied to `may_upload`.** `may_upload` means "may change content". Ownership decides who sees it.
- Pros: smallest change, no behaviour change for editing. Renames, moves and deletes inside Lychee would drift from the NAS folders, and a later sync would bring the content back or delete it.
- Cons: an owner without upload rights cannot rename an album, set its cover, or gather selected photos in a new album to share them. They share albums as imported.

**Option B: metadata follows ownership, structure stays with `may_upload`.** Title, description, cover, sorting, header, license and photo title/description/tags/rating follow ownership; creating, moving, merging and deleting stay with `may_upload`.
- Pros: owners can tidy what they share without changing the structure.
- Cons: `CAN_EDIT` splits into two abilities and every request using it has to be sorted into one of them. Large test surface.

**Option C: ownership decides every owner action.** `may_upload` only gates adding content (upload, URL import, albums at the root).
- Pros: the simplest model.
- Cons: every owner without upload rights gains move and delete on upgrade. In the reported setup, deletes in Lychee would remove the links to the NAS files.

Owner selected D on 2026-11-24: "create another rights for that". A new per-user flag `may_edit` (name chosen locally, see spec) covers every gate listed in the context above, backfilled from `may_upload`, with a `grant_new_user_edit_rights` setting and a `--may-edit` option like Q-118-01 B. Single-photo edits: Q-118-08. Normative outcome: spec FR-118-08 … 12, FR-118-17 … 21.

### ~~Q-118-03~~ — Owners upload into their own albums without `may_upload`

**Context.** `AlbumPolicy::canUpload()` on a regular album is "owner, or `grants_upload` on a user, group or public permission". It does not check `may_upload`, so an owner without upload rights sees the upload button in their own albums and can upload, import from a URL or use the camera there. Only the root and smart albums check `may_upload`. The reported setup needs those users not to upload.

**Option A (recommended): owners need `may_upload`.** `canUpload()` on a regular album becomes `(owner and may_upload)` or `grants_upload` on a user, group or public permission, like `canEdit()`. Import from server keeps its own target rule (Q-118-04).
- Pros: `may_upload` means what it says. Without it the reported setup cannot stop users from uploading into the albums imported for them.
- Cons: owners without upload rights who upload into their own albums today lose that. This is an unadvertised path and probably rare.

**Option B: keep it.**
- Pros: no change.
- Cons: the reported setup does not work: users still upload into their albums.

Owner selected A on 2026-11-24. Normative outcome: spec FR-118-13.

### ~~Q-118-04~~ — Import from server without upload rights

**Context.** FR-115-03 makes `canImportFromServer()` need `server_path` **and** (`may_administrate` or `may_upload`). FR-115-07 then needs `CAN_UPLOAD` on the target album. `lychee:sync` without a path skips users who may not import (`SyncUserEligibility::NOT_ALLOWED`), so today the reported setup's users are skipped. The request asks for these users to sync their own folders from the web UI.

**Option A (recommended): `server_path` alone grants import.** Drop `may_upload` from `canImportFromServer()`. The target album must be the requester's or carry `grants_upload` (today's `canUpload()` rule, kept for import whatever Q-118-03 decides). Importing into the root stays allowed: the albums created are the requester's. `lychee:sync` without a path then covers these users. FR-115-03 is amended.
- Pros: matches the request. The server owner already grants `server_path` per user, and only they can.
- Cons: amends a rule Feature 115 just shipped. Import becomes the one way a user without upload rights adds photos, which is the intent.

**Option B: keep FR-115-03.** Only users with upload rights import. The admin keeps running `lychee:sync <path> --owner_id=<id>` per user.
- Pros: Feature 115 unchanged.
- Cons: these users cannot sync from the web UI, and `sync_users_server_paths` skips them.

Owner selected A on 2026-11-24. FR-115-03 and FR-115-07 amended. Normative outcome: spec FR-118-14, FR-118-15.

### ~~Q-118-05~~ — Smart albums that need upload rights

**Context.** `AlbumPolicy::canSee()` shows Unsorted, Highlighted, Recent, On this day, Unrated and Untagged (`SmartAlbumType::require_upload_rights()`) only to users with `may_upload`. A user whose photos are imported for them owns photos but sees none of these.

**Option A (recommended): unchanged, out of scope.** Sharing is the subject of this feature. A backlog item records the question.
- Pros: the feature stays small. No extra query when listing smart albums.
- Cons: these users keep a reduced gallery (no Recent, no On this day).

**Option B: shown to users who own at least one photo.** `canSee()` admits `may_upload` or ownership of a photo.
- Pros: users whose photos are imported get the full gallery.
- Cons: one `exists()` query per request listing smart albums. Users who see only shared albums get nothing new.

Owner selected B on 2026-11-24. Normative outcome: spec FR-118-16, NFR-118-03.

### ~~Q-118-06~~ — Transfer and the user picker

**Context.** Q-118-01 B puts sharing with users, the pickers and public visibility under `may_share`. Transfer (`AlbumPolicy::canTransfer()`) needs ownership only, and its dialog uses the same user picker (`GET /api/v2/Users`, `UserPolicy::canList()`). The v8 drawer shows the Visibility section on `can_transfer`, because both rules were "owner". In practice no user without `may_upload` can transfer today: the drawer needs `can_edit` and the picker needs `may_upload`.

**Option A (recommended): transfer needs `may_share` too.** `canTransfer()` = owner and `may_share`. The picker admits `may_share` (groups: or group admin).
- Pros: one rule for every action that gives others access, and handing an album over is the strongest of them. `can_transfer` still matches visibility, so the drawer needs no new right. The picker stays a flag check, with no query.
- Cons: a user with `may_upload` but not `may_share` cannot hand an album to another user. Only an admin can.

**Option B: transfer stays owner-only.** The picker admits `may_share` or the owner of at least one album.
- Pros: every owner can still transfer.
- Cons: users without `may_share` can list every username. One `exists()` query per picker request. The drawer needs a new `can_change_visibility` right, separate from `can_transfer`.

Owner selected A on 2026-11-25. With the owner directive that `may_upload` implies `may_share` (ADR-118-01), the con of A no longer applies: every uploader may share. Normative outcome: spec FR-118-04, ADR-118-01.

### ~~Q-118-07~~ — Handing out grants one does not hold

**Context.** The share dialog (`AddSharingRequest`, `EditSharingRequest`, bulk sharing) lets the owner give `grants_upload`, `grants_edit`, `grants_move` and `grants_delete`. The visibility form (`SetAlbumProtectionPolicyRequest`) lets them give `grants_upload` to everyone. A grant works whatever the recipient's own rights: that is how family members without `may_upload` upload into an album shared with them. With the rights split, a user with `may_share` but no `may_upload` could give `grants_upload` on their album to another user, or to the public. Two such users could give each other upload rights, and the reported setup would be bypassed.

**Option A (recommended): only grants the sharer holds.** `grants_upload` needs the sharer's `may_upload`. `grants_edit`, `grants_move` and `grants_delete` need their `may_edit`. A request setting a grant the sharer lacks → 422 on that field. Admins skip the check. The v7/v8 checkboxes for those grants are hidden for that sharer. Existing grants are left as they are.
- Pros: an admin's choice cannot be worked around. Recipients keep today's behaviour, so collaborators without upload rights still upload into albums shared with upload. Nothing changes on upgrade: every current sharer has `may_upload`, so both new flags.
- Cons: checks in four requests and per-checkbox UI in v7 and v8. Revoking a right later does not remove the grants already given; propagating sharing (`PropagateSharingRequest`) copies them as they are.

**Option B: the recipient must hold the matching right.** `grants_upload` works only for a recipient with `may_upload`, and so on.
- Pros: one check in the policies.
- Cons: breaks the common setup where users without upload rights upload into an album shared with them. A public `grants_upload` would mean nothing to guests.

**Option C: no restriction.** Grants stay independent of user rights.
- Pros: no work.
- Cons: the reported setup can be bypassed as described above.

Owner selected A on 2026-11-25. The v7/v8 checkboxes for grants the sharer lacks are disabled rather than hidden, so the grant columns stay aligned (adjusted locally). Normative outcome: spec FR-118-07, ADR-118-01.

### ~~Q-118-08~~ — Single-photo edits by the owner

**Context.** `PhotoPolicy::canEdit()` lets a photo's owner edit it with no flag. It is used for edit, rename, tags, license, set as cover and the other single-photo requests (`AuthorizeCanEditPhotoTrait`). The batch forms (`canEditById()`, `canMoveById()`, `canDeleteById()`) need `may_upload`. Users without upload rights who uploaded into someone else's album through `grants_upload` own those photos, and today edit them one at a time.

**Option A (recommended): both need `may_edit`.** `canEdit()` on a photo = (owner and `may_edit`), or an edit right on a containing album, like `canEditById()`.
- Pros: `may_edit` means the same everywhere. An admin who withholds it gets no edits at all.
- Cons: users without upload rights stop editing the photos they uploaded into albums shared with them, unless that album also grants edit. The migration cannot give them `may_edit` without also letting them create albums.

**Option B: the owner always edits their photos' metadata.** Drop the flag from the owner branch of `canEditById()` too. `may_edit` keeps albums, moving, deleting, tags and renamer rules.
- Pros: collaborators keep editing their own uploads, now in batch too. Titles and descriptions do not touch the NAS folders.
- Cons: `may_edit` no longer covers photo metadata. Rotation and other edits that rewrite files would also be open to owners without the flag.

**Option C: unchanged.** Single edits need no flag, batch edits need `may_edit`.
- Pros: no behaviour change.
- Cons: the inconsistency stays and becomes visible: the same edit works on one photo and is refused on two.

Owner selected A on 2026-11-25. Normative outcome: spec FR-118-10, ADR-118-01.
