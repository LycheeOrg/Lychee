# Feature 118 – Share Rights Decoupling

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-24 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #118 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
`users.may_upload` gates far more than uploading: sharing albums with users and groups, listing users and groups, editing, moving and deleting one's own content, tags, renamer rules, import from server and six smart albums. [Discussion #4982](https://github.com/LycheeOrg/Lychee/discussions/4982) describes an instance where each user's photos live on a NAS and are imported into Lychee as symbolic links, as that user. The users must decide whom they share their albums with, but must not upload through Lychee, which would break the NAS folder structure.

This feature splits the user's rights into three flags: `may_upload` (add content), `may_share` (give others access: users, groups, public, transfer) and `may_edit` (change existing content: edit, organise, delete). `may_upload` implies the other two: the rule is enforced when users are written, so every policy reads one flag only (owner directive 2026-11-25, ADR-118-01). Import from server depends on `server_path` alone. Affected layers: migration and `User`, `AlbumPolicy`, `PhotoPolicy`, `UserPolicy`, `UserGroupPolicy`, `TagPolicy`, `AlbumQueryPolicy`, rights resources, user management (requests, actions, resource, CLI, OAuth, LDAP, registration), import, v7/v8 user forms, album settings drawer and left menu.

## Goals
- An album owner with `may_share` shares their albums with users and groups, and changes their visibility, without `may_upload` (Q-118-01 B).
- Editing and organising one's own content has its own right, `may_edit` (Q-118-02 D).
- `may_upload` means adding content only, owned albums included (Q-118-03 A).
- A user with a `server_path` imports from it without `may_upload` (Q-118-04 A).
- Users who own photos see the smart albums that need upload rights today (Q-118-05 B).
- Nothing changes on upgrade for users with `may_upload` (NFR-118-01).

## Non-Goals
- Mapping folders under the import mount to users: Feature 115 (`server_path`) covers it.
- A setting that forces import via symbolic link for non-admins: `import_via_symlink` already applies to every user (Q-115-10).
- A file browser for uploading into one's NAS folder (third wish in the discussion).
- Changes to the meaning of the album grants (`grants_edit`, `grants_move`, `grants_delete`, `grants_upload`, Feature 072) for their recipients.
- Rights options on `lychee:update_user`, which takes none today.

## Functional Requirements

### Share right (Q-118-01 B)

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-118-01 | New column `users.may_share` (boolean, not null, default `false`), cast `bool` on `User`. The migration sets it to `may_upload` for every user. `down()` drops it. Admins skip it (`BasePolicy::before()`). | After migration, every user's `may_share` equals their `may_upload`. | — | — | None. | Q-118-01 B |
| FR-118-02 | `AlbumPolicy::canShareWithUsers()` = `may_share` and owner (with no album: `may_share` and owns at least one album, as today). `canShareById()` = `may_share` and owns every album. These gate the `/api/v2/Sharing*` routes and `AlbumAccessPermissionListRequest`. | Owner with `may_share`, without `may_upload`, lists, adds, edits, deletes and propagates shares. | — | Rule false → 403. | None. | Q-118-01 B |
| FR-118-03 | `AlbumPolicy::canChangeProtectionPolicy()` = owner and `may_share` (public visibility, link, password, NSFW flag, public grants). | Owner with `may_share` changes visibility. | — | Owner without `may_share` → 403. | None. | Q-118-01 B |
| FR-118-04 | User and group pickers: `UserPolicy::canList()` = `may_share`; `UserGroupPolicy::canList()` = `may_share` or group admin. Transfer: `AlbumPolicy::canTransfer()` = owner and `may_share`. | Share and transfer dialogs list users and groups for a user with `may_share`. | — | Rule false → 403. | None. | Q-118-01 B, Q-118-06 A |
| FR-118-05 | `RootAlbumRightsResource` gains `can_share_with_users` (= `CAN_SHARE_WITH_USERS` with no album, which is `may_share` alone: no album query, so the root config stays query-free as Feature 062 requires). The v7 and v8 left menu "Sharing" entry follows it instead of `root_album.can_upload`. The Sharing page (`Sharing::all`) lists the user's own shares, possibly none. | User with `may_share` sees "Sharing"; without albums the list is empty. | — | — | None. | Q-118-01 B |
| FR-118-06 | The v7 and v8 album settings drawer, mounted today only when `rights.can_edit`, opens when at least one section is available: properties (`can_edit`), visibility (`can_transfer`, which equals FR-118-03's rule after FR-118-04), move (`can_move`), share (`can_share_with_users`), transfer (`can_transfer`), delete (`can_delete`), purchasable (`can_make_purchasable`). Each section keeps its own right. The header's settings button follows the same rule (`AlbumState.hasAlbumSettings`, shared by v7 and v8). v8 lays out the drawer whenever at least one section exists (it used to need two, and showed nothing for a single non-visibility section). v7 shows the "About" tab only when properties or visibility is allowed, and opens on the first tab available. | Owner with `may_share` only: drawer shows Visibility and Share. | — | No section available → no button, no drawer. | None. | Q-118-01 B, Q-118-02 D |
| FR-118-07 | A sharer hands out only the grants they hold: `grants_upload` needs their `may_upload`; `grants_edit`, `grants_move` and `grants_delete` need their `may_edit`. Checked by a `HeldGrantRule` on those fields in `AddSharingRequest` (single and bulk), `EditSharingRequest` and, for the public `grants_upload`, `SetAlbumProtectionPolicyRequest`. Admins skip it. `PropagateSharingRequest` copies existing grants unchanged. Grants already given stay when a right is revoked later. v7/v8 grant checkboxes (create share, share line, bulk sharing, visibility) for a grant the sharer lacks are disabled, following `root_album.can_upload` (upload) and `root_album.can_edit` (edit, move, delete). | Sharer with `may_share` only grants full photo and download. | A lacked grant set to `true` → 422 on that field, nothing saved. `false` always accepted. | — | None. | Q-118-07 A |

### Edit right (Q-118-02 D)

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-118-08 | New column `users.may_edit` (boolean, not null, default `false`), cast `bool` on `User`. The migration sets it to `may_upload` for every user. `down()` drops it. Admins skip it. Name chosen locally: it reads "may edit own content", next to `may_edit_own_settings`. | After migration, every user's `may_edit` equals their `may_upload`. | — | — | None. | Q-118-02 D |
| FR-118-09 | `AlbumPolicy`: `may_edit` replaces `may_upload` in the owner branch of `canEdit()`, `canMove()`, `canMoveAlbum()`, `canDelete()`, in their root and smart-album branches, and in `canEditById()`, `hasGrantOnParentsById()`, `hasGrantOnAllById()` (empty list and owned albums). Album and tag-album creation follow, through `CAN_EDIT` on the parent or the root. | Owner with `may_edit`, without `may_upload`: rename, cover, sort, create sub-album, move, merge, delete. | — | Owner without `may_edit` → 403 unless a grant applies. | None. | Q-118-02 D |
| FR-118-10 | `PhotoPolicy`: `may_edit` replaces `may_upload` in the owner branch of `canMove()`, `canMoveById()`, `canEditById()`, `canDeleteById()`. The owner branch of single-photo `canEdit()` (edit, rename, tags, license, set as cover, other `AuthorizeCanEditPhotoTrait` requests) becomes owner and `may_edit`. | Owner with `may_edit` edits, moves and deletes their photos, one or many. | — | Owner without `may_edit` → 403 unless an album grant applies. | None. | Q-118-02 D, Q-118-08 A |
| FR-118-11 | `AlbumQueryPolicy::appendEditableCondition()` and `AlbumListController::canEdit()` read `may_edit`, keeping parity with `AlbumPolicy::canEdit()` (FR-072-31). | Listings mark the same albums editable as the policy. | — | — | None. | Q-118-02 D, FR-072-31 |
| FR-118-12 | `TagPolicy::canEdit()` = `may_edit`. Renamer rule requests (list, create, update, delete) = `may_administrate` or `may_edit`, since renaming changes existing content (decided locally). | User with `may_edit` edits, merges, deletes tags and manages renamer rules. | — | Rule false → 403. | None. | Q-118-02 D |

### Upload, import, smart albums (Q-118-03 A, Q-118-04 A, Q-118-05 B)

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-118-13 | `AlbumPolicy::canUpload()` on a regular, tag or person album = (owner and `may_upload`) or `grants_upload` on a user, group or public permission. Root and smart albums: `may_upload`, unchanged. Every upload path follows (file upload, URL import, camera, `can_upload` in album rights). | Owner with `may_upload` uploads into their album. Collaborator without `may_upload` uploads into an album shared with `grants_upload`. | — | Owner without `may_upload` → 403, upload button hidden. | None. | Q-118-03 A |
| FR-118-14 | `AlbumPolicy::canImportFromServer()` = `features.disable-import-from-server` is false and `server_path !== null`. `may_upload` and `may_administrate` no longer count (amends FR-115-03). `SyncUserEligibility::NOT_ALLOWED` then means "feature disabled" only, so `lychee:sync` with no path covers users without `may_upload`. | User with `server_path`, without `may_upload`, sees "Import from server" and imports. | — | No `server_path` → 403, entry hidden. | None. | Q-118-04 A |
| FR-118-15 | Import target (amends FR-115-07): new ability `AlbumPolicy::CAN_IMPORT_INTO` = owner of the target album or `grants_upload` on it (the `canUpload()` rule before FR-118-13). Importing into the root stays allowed. `ImportFromServerRequest` checks it instead of `CAN_UPLOAD`. | User without `may_upload` imports into their own album or the root. | — | Target neither owned nor granted → 403. | None. | Q-118-04 A |
| FR-118-16 | `AlbumPolicy::canSee()`: the smart albums with `require_upload_rights()` (Unsorted, Highlighted, Recent, On this day, Unrated, Untagged) are visible to users with `may_upload` or who own at least one photo. Others unchanged. | User without `may_upload` owning imported photos sees Recent and On this day. | — | User owning no photo, without `may_upload` → hidden, 403 on access. | None. | Q-118-05 B |

### Accounts and user management

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-118-17 | `POST` and `PATCH /api/v2/UserManagement` take `may_share` and `may_edit` (required booleans, like `may_upload`). `Actions\User\Create::do()` and `Save::do()` take them as trailing parameters (default `false` for `Create`, so positional callers keep working) and store them per FR-118-22. `GET /api/v2/UserManagement` and `UserManagementResource` return them (flag or `may_administrate`, as for `may_upload`). | Admin creates and edits users with each allowed combination. | Missing or non-boolean → 422. | — | None. | Q-118-01 B, Q-118-02 D |
| FR-118-18 | v7 and v8 user create/edit forms gain "Share rights" and "Edit rights" checkboxes after upload rights, with hints (translation keys `users.share_rights`, `users.content_rights` and their `create_edit.` forms: `edit_rights` already names the own-settings right). Ticking upload ticks both and disables them; unticking upload enables them again with their values kept. The v8 users table and the v7 users list show both. | See mock-up. | — | — | None. | Q-118-01 B, Q-118-02 D, FR-118-22 |
| FR-118-19 | New settings `grant_new_user_share_rights` and `grant_new_user_edit_rights` (`0|1`), category `Users Management` like `grant_new_user_upload_rights`. The migration copies that setting's current value into both. Registration (`ProfileController::register()`) and OAuth (`Actions\Oauth\Oauth`) read them. With `grant_new_user_upload_rights` on, the new user gets all three flags whatever the two new settings say (FR-118-22). | Self-registered or OAuth user gets the flags from the settings. | — | — | None. | Q-118-01 B, Q-118-02 D |
| FR-118-20 | LDAP provisioning (`ProvisionLdapUser`) sets `may_share` and `may_edit` to `true`, as it does `may_upload`. | New LDAP user has all three flags. | — | — | None. | Q-118-01 B, Q-118-02 D |
| FR-118-21 | `lychee:create_user` gains `--may-share` and `--may-edit`. `--may-administrate` implies upload, hence both (FR-118-22); so does `--may-upload`. The first admin (v7 installer, v8 admin setup) is created with `may_upload`, hence all three flags. | `lychee:create_user bob pw --may-share` → share only. | — | — | None. | Q-118-01 B, Q-118-02 D |
| FR-118-22 | `may_upload` implies `may_share` and `may_edit`. Enforced where users are written: `Create` and `Save` store `may_share || may_upload` and `may_edit || may_upload`; `ProvisionLdapUser` sets all three; the migration backfill (FR-118-01, FR-118-08) satisfies it; the `UserFactory::may_upload()` state sets all three. Policies read one flag each and never combine `may_upload` with the others. | Admin saves a user with upload and share unticked by the API → stored with all three true. | — | — | None. | Owner directive 2026-11-25, ADR-118-01 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-118-01 | After upgrade, a user with `may_upload` may do exactly what they did before. Users without `may_upload` change only where this spec says so: owners lose public visibility (FR-118-03), transfer (FR-118-04), upload into their own albums (FR-118-13) and single-photo edits of their own photos (FR-118-10); they gain import with a `server_path` (FR-118-14) and smart albums when they own photos (FR-118-16). | No regression for existing installs. | Existing tests keep their expectations for users with `may_upload`. Expectations for `userNoUpload` change only in the cases listed here, each noted in the task that changes it. | `SharingTest`, policy tests, `ImportFromServerTest`, `SyncTest` | Q-118-01 B, Q-118-06 A, Q-118-08 A |
| NFR-118-02 | Each policy rule stays one flat expression or delegates to a small private helper. No new branch is inlined into `hasGrantOnParentsById()` or `hasGrantOnAllById()`. | AGENTS.md straight-line increments. | Review. | `AlbumPolicy`, `PhotoPolicy` | AGENTS.md |
| NFR-118-03 | The photo-ownership check of FR-118-16 runs at most once per request (memoised), whatever the number of smart albums listed. | Smart albums are checked one by one in `Top::get()` and `AlbumSmartController`. | Query count in the feature test. | `AlbumPolicy::canSee()` | Q-118-05 B |
| NFR-118-04 | Every new label and setting description exists in all 23 languages. | `LangTest`. | `php artisan test --filter=LangTest`. | `lang/*/users.php`, `lang/*/all_settings.php` | AGENTS.md |
| NFR-118-05 | Both migrations (columns, settings) are reversible. | Rollback safety. | `down()` drops the columns and settings. | migrations | AGENTS.md |

## UI / Interaction Mock-ups

User create/edit form (v8; v7 shows the same checkboxes in its layout), FR-118-18:

```
┌─ Edit user ───────────────────────────────────────────┐
│  Username      [ alice                        ]       │
│  Password      [ ••••••••                     ]       │
│                                                       │
│  [ ] Upload rights   User can upload content.         │
│  [x] Share rights    User can share their albums.     │
│  [ ] Edit rights     User can edit, move and delete   │
│                      their own content.               │
│                                                       │
│  (upload ticked → share and edit ticked, disabled:    │
│   [x] Upload  [x̶] Share  [x̶] Edit)                    │
│  [x] User can edit their own settings                 │
│  …                                                    │
│                              [ Cancel ]  [ Save ]     │
└───────────────────────────────────────────────────────┘
```

v8 users table, FR-118-18:

```
┌──────────┬────────┬───────┬──────┬──────────┬─────┐
│ Username │ Upload │ Share │ Edit │ Settings │  …  │
├──────────┼────────┼───────┼──────┼──────────┼─────┤
│ alice    │   ✕    │   ✓   │  ✕   │    ✓     │     │
│ bob      │   ✓    │   ✓   │  ✓   │    ✓     │     │
└──────────┴────────┴───────┴──────┴──────────┴─────┘
```

Album settings drawer (v8) for an owner with `may_share` only (FR-118-06):

```
┌─ Album settings ─────────────────────────────────────────┐
│  Visibility                                              │
│   [x] Public   [ ] Hidden   [ ] Password   …             │
│                                                          │
│  Share                                                   │
│   ┌ user / group ──┬ full  dl   upload edit move del ┬───┐│
│   │ bob            │ [x]   [x]   [-]   [-]  [-]  [-] │[✕]││
│   └────────────────┴─────────────────────────────────┴───┘│
│   [-] = disabled: the sharer lacks upload / edit (FR-118-07)
│                                                          │
│  ▸ Expert mode: Transfer                                 │
│                                                          │
│  (no Properties, Move, Delete: need edit rights)         │
└──────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-118-01 | Migration: users with `may_upload` get `may_share` and `may_edit` true, others false; the two settings copy `grant_new_user_upload_rights`. Rollback removes them. |
| S-118-02 | Owner with `may_share`, without `may_upload`/`may_edit`: share, list, edit, delete, propagate shares → 2xx; visibility change → 201. A user with `may_share` and no album: root `can_share_with_users` true, `Sharing::all` empty. |
| S-118-03 | Owner without `may_share` (with `may_upload`): `/Sharing*` and visibility → 403; root `can_share_with_users` false. |
| S-118-04 | User with `may_share` shares an album they do not own → 403. |
| S-118-05 | User with `may_share` lists users and groups → 200; without it (not group admin) → 403. |
| S-118-06 | Owner with `may_edit`, without `may_upload`: rename, create sub-album, move, merge, delete albums; move and delete own photos by ID; edit tags; renamer rules → 2xx. |
| S-118-07 | Owner without `may_edit` (with `may_upload`): the same requests → 403, unless an album grant applies; listings mark their albums not editable (parity FR-118-11). |
| S-118-08 | Owner without `may_upload`: upload, URL import into own album → 403, `can_upload` false. Same user with `grants_upload` on another user's album → upload allowed. |
| S-118-09 | User with `server_path`, no `may_upload`, no admin: import options, browse, import into own album and into the root → 2xx; into another user's album without `grants_upload` → 403. |
| S-118-10 | `lychee:sync` with no path and `sync_users_server_paths` on syncs a user without `may_upload`. |
| S-118-11 | User without `may_upload` owning a photo sees Unsorted, Recent, On this day …; owning none → hidden. One ownership query per request. |
| S-118-12 | User management: create and edit with each flag; resource returns them; admin row shows all true. |
| S-118-13 | Registration and OAuth apply the three settings; LDAP gives all three; `lychee:create_user --may-share --may-edit` and `--may-administrate` behave as FR-118-21. |
| S-118-14 | v7/v8: owner with `may_share` only opens the settings drawer, sees Visibility and Share, and "Sharing" in the left menu; user forms and lists show both new flags. |
| S-118-15 | User with all three flags: every existing scenario unchanged (NFR-118-01). |
| S-118-16 | Owner with `may_share` transfers their album → 200; owner without it → 403. |
| S-118-17 | Sharer with `may_share` only: add or edit a share with `grants_upload` (or edit, move, delete) `true` → 422 on that field, nothing saved; all four `false` → 2xx. Public `grants_upload` `true` in the visibility form → 422. Admin: any grant accepted. Propagation copies grants unchanged. |
| S-118-18 | Sharer with `may_share` and `may_edit`, no `may_upload`: edit, move, delete grants accepted, upload refused. |
| S-118-19 | Owner without `may_edit`: edit, rename, tag, license, set as cover on their own photo → 403; with `may_edit` → 2xx. |
| S-118-20 | `Create`/`Save` with `may_upload` true and `may_share`/`may_edit` false → all three stored true; with `may_upload` false the given values are stored. |
| S-118-21 | v7/v8 user form: ticking upload ticks and disables share and edit; unticking enables them. Grant checkboxes disabled for a sharer lacking the right. |

## Test Strategy
- **Migration:** a feature test for the backfill and the settings copy (S-118-01).
- **Policies:** unit-level matrix per gate in the appendix, for users with each flag alone and none (S-118-02 … 11).
- **REST API (v2):** `SharingTest`, album and photo edit, move and delete tests, upload tests, `ImportFromServerTest`, `ImportFromServerBrowseTest`, smart album tests, user management tests extended with users without `may_upload` (S-118-02 … 13).
- **Artisan:** `SyncTest` (S-118-10), `CreateUserTest` or equivalent (S-118-13).
- **Frontend (v7/v8):** manual check of S-118-14; `vp check --fix`, `vp run typecheck`.
- **Docs/Contracts:** regenerated `lychee.d.ts` for the new resource fields.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-118-01 | `users.may_share` bool, default `false`, backfilled from `may_upload` (FR-118-01) | migration, `User`, policies, user management |
| DO-118-02 | `users.may_edit` bool, default `false`, backfilled from `may_upload` (FR-118-08) | migration, `User`, policies, user management |
| DO-118-03 | Configs `grant_new_user_share_rights`, `grant_new_user_edit_rights` `0|1` (FR-118-19) | configs, registration, OAuth |
| DO-118-04 | `RootAlbumRightsResource::$can_share_with_users` (FR-118-05) | rights resources, v7/v8 left menu |
| DO-118-05 | `UserManagementResource::$may_share`, `$may_edit` (FR-118-17) | user management, v7/v8 user forms |
| DO-118-06 | `AlbumPolicy::CAN_IMPORT_INTO` (FR-118-15) | import requests |
| DO-118-07 | `App\Rules\HeldGrantRule(bool $held)`: fails when the value is `true` and `$held` is false (FR-118-07) | sharing and protection policy requests |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-118-01 | REST `/api/v2/Sharing*` | `may_share` and owner (FR-118-02) | `ListSharingRequest`, `AddSharingRequest`, `EditSharingRequest`, `DeleteSharingRequest`, `PropagateSharingRequest`, `ListAllSharingRequest`, `AlbumAccessPermissionListRequest` |
| API-118-02 | REST `POST /api/v2/Album::updateProtectionPolicy` | `may_share` and owner (FR-118-03) | `SetAlbumProtectionPolicyRequest` |
| API-118-03 | REST `GET /api/v2/Users`, `GET /api/v2/UserGroups` | `may_share` (FR-118-04) | `ListUsersRequest`, `ListUserGroupRequest` |
| API-118-04 | REST `POST`/`PATCH`/`GET /api/v2/UserManagement` | `may_share`, `may_edit` fields (FR-118-17) | `AddUserRequest`, `SetUserSettingsRequest`, `UserManagementResource` |
| API-118-05 | REST `/api/v2/Import*` | `server_path` alone; target `CAN_IMPORT_INTO` (FR-118-14, FR-118-15) | `ImportFromServer*Request` |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-118-01 | `php artisan lychee:create_user` | `--may-share`, `--may-edit`; `--may-administrate` implies both (FR-118-21) |
| CLI-118-02 | `php artisan lychee:sync` | No-path mode covers users without `may_upload` (FR-118-14) |

### Telemetry Events
None.

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-118-01 | Settings drawer without properties | Owner without `can_edit` opens album settings; only allowed sections render (FR-118-06). |
| UI-118-02 | User form with three rights | Admin ticks upload, share and edit independently (FR-118-18). |

## Telemetry & Observability
None.

## Documentation Deliverables
- Knowledge map: the three user rights and what each gates; `CAN_IMPORT_INTO`.
- Spec 115: FR-115-03 and FR-115-07 amended, pointing to FR-118-14 and FR-118-15.
- [ADR-118-01](../../../6-decisions/ADR-118-01-split-user-rights.md): the three-flag rights model.

## Fixtures & Sample Data
None: the feature tests build their users and albums.

## Spec DSL

```
domain_objects:
  - id: DO-118-01
    name: users.may_share
    type: bool
    default: false
    backfill: may_upload
  - id: DO-118-02
    name: users.may_edit
    type: bool
    default: false
    backfill: may_upload
  - id: DO-118-03
    name: configs.grant_new_user_share_rights, configs.grant_new_user_edit_rights
    type: "0|1"
    backfill: grant_new_user_upload_rights
  - id: DO-118-04
    name: RootAlbumRightsResource.can_share_with_users
    type: bool
  - id: DO-118-05
    name: UserManagementResource.may_share, UserManagementResource.may_edit
    type: bool
  - id: DO-118-06
    name: AlbumPolicy::CAN_IMPORT_INTO
routes:
  - id: API-118-01
    path: /api/v2/Sharing*
  - id: API-118-02
    path: /api/v2/Album::updateProtectionPolicy
  - id: API-118-03
    path: /api/v2/Users, /api/v2/UserGroups
  - id: API-118-04
    path: /api/v2/UserManagement
  - id: API-118-05
    path: /api/v2/Import*
cli_commands:
  - id: CLI-118-01
    command: php artisan lychee:create_user --may-share --may-edit
  - id: CLI-118-02
    command: php artisan lychee:sync
ui_states:
  - id: UI-118-01
    description: Album settings drawer opened without can_edit
  - id: UI-118-02
    description: User form with upload, share and edit rights
```

## Appendix – `may_upload` gates, before and after

| Area | Gate | Before | After | Source |
|------|------|--------|-------|--------|
| Share with users | `AlbumPolicy::canShareWithUsers()`, `canShareById()` | `may_upload` and owner | `may_share` and owner | FR-118-02 |
| Public visibility | `canChangeProtectionPolicy()` | owner | `may_share` and owner | FR-118-03 |
| User / group pickers | `UserPolicy::canList()`, `UserGroupPolicy::canList()` | `may_upload` (groups: or group admin) | `may_share` (groups: or group admin) | FR-118-04 |
| Transfer | `canTransfer()` | owner | `may_share` and owner | FR-118-04 |
| Grants handed out | sharing and visibility requests | any | only grants the sharer holds | FR-118-07 |
| Sharing page entry | v7/v8 left menu | `root_album.can_upload` | `root_album.can_share_with_users` | FR-118-05 |
| Settings drawer | v7/v8 `AlbumPanel` | `rights.can_edit` | any section available | FR-118-06 |
| Edit, move, delete own albums | `AlbumPolicy` `canEdit`, `canMove`, `canMoveAlbum`, `canDelete`, `canEditById`, `hasGrantOnParentsById`, `hasGrantOnAllById` | `may_upload` and owner | `may_edit` and owner | FR-118-09 |
| Root and smart albums | `canEdit`, `canMove`, `canDelete` with `null` or a smart album | `may_upload` | `may_edit` | FR-118-09 |
| Own photos, batch | `PhotoPolicy` `canMove`, `canMoveById`, `canEditById`, `canDeleteById` | `may_upload` and owner | `may_edit` and owner | FR-118-10 |
| Own photos, single edit | `PhotoPolicy::canEdit()` | owner | `may_edit` and owner | FR-118-10 |
| Editable listings | `AlbumQueryPolicy::appendEditableCondition()`, `AlbumListController::canEdit()` | `may_upload` and owner | `may_edit` and owner | FR-118-11 |
| Tags, renamer rules | `TagPolicy::canEdit()`, renamer requests | `may_upload` (renamer: or admin) | `may_edit` (renamer: or admin) | FR-118-12 |
| Upload into own album | `AlbumPolicy::canUpload()` | owner | `may_upload` and owner | FR-118-13 |
| Upload at the root | `canUpload(null)` | `may_upload` | unchanged | — |
| Import from server | `canImportFromServer()`, `SyncUserEligibility` | `server_path` and (`may_upload` or admin) | `server_path` | FR-118-14 |
| Import target | `ImportFromServerRequest` | `CAN_UPLOAD` | `CAN_IMPORT_INTO` (owner or `grants_upload`) | FR-118-15 |
| Smart albums | `canSee()` | `may_upload` | `may_upload` or owns a photo | FR-118-16 |
| New accounts | registration, OAuth, `lychee:create_user`, LDAP | upload setting, `--may-upload`, LDAP `true` | plus share and edit settings, options, LDAP `true` | FR-118-19 … 21 |
