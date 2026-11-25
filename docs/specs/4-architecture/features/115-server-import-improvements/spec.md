# Feature 115 – Server Import Improvements

| Field | Value |
|-------|-------|
| Status | Implemented (PR #4989, merged 2026-11-22). Q-115-01 … 13 resolved, manual checks S-115-41/42 pending |
| Last updated | 2026-11-22 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #115 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Import from server (`POST/GET /api/v2/Import`, `GET /api/v2/Import::browse`, `lychee:sync`) is today reserved to the user named by the `owner_id` config, browses the whole file system, follows every symbolic link, always creates the selected folder as an album, reuses and deletes albums and photos whoever owns them, and stamps every imported photo `trusted` (`ImportDTO`, `ImportImageJob` hardcode `UserUploadTrustLevel::TRUSTED`). This feature opens import to other users through a per-user `server_path` that both grants access and confines it (ADR-115-01), contains symbolic links inside that path, keeps imports away from other users' content, applies the owner's upload trust level, adds a setting to import a folder's content without the folder itself becoming an album, lets `lychee:sync` with no path sync every user's own folder, and gathers the import settings in their own category.

Affected layers: `users` table and `User` model, `Actions\User\Create`/`Save` and the three first-admin paths, user management API (v2) and forms (v7, v8), `AlbumPolicy::canImportFromServer`, `ImportFromServer*` requests/controller/resources, `Actions\Import\Exec` and its pipes, `ImportDTO`, `ImportImageJob`, `lychee:sync`, configs and config categories, v7/v8 import dialog, translations (23 locales).

## Goals
- The server owner (the `owner_id` user) grants import from server per user with `server_path`; disabled (`null`) by default, `/` for the existing owner and for the first admin of a new install. Other admins cannot change it.
- A user with a restricted `server_path` browses and imports only inside it; nothing outside it is listed, disclosed or imported, through symbolic links included. Two accepted risks remain (ADR-115-01): a link import whose source the user later replaces (Q-115-10), and a file swapped after the job's check (Q-115-08).
- `server_path = /` keeps today's unrestricted browsing, starting at Lychee's working directory.
- Imports never merge into or delete another user's albums and photos (setting, on by default).
- Imported photos carry the trust level of the user who owns them, on the web and on the CLI.
- A setting imports the content of the selected folder straight into the target instead of into an album named after the folder.
- `lychee:sync` with no path syncs every user's `server_path` as that user, behind a setting disabled by default.
- All import settings, from the server and from a URL, live in one new "Import" settings category.

## Non-Goals
- Letting users set their own `server_path` (server owner only, FR-115-19).
- `lychee:create_user` / `lychee:update_user` options for `server_path` (the first user still gets `/`, FR-115-14).
- A trust level picker in the dialog or a `--trust_level` option (Q-115-04 B/C, rejected).
- A per-user target album for the no-path sync (the target is the gallery root, FR-115-13).
- Changing `DISABLE_IMPORT_FROM_SERVER`: it still turns the feature off for everyone, admins included.
- Restricting the paths an operator passes to `lychee:sync` (shell access is already full access).
- Moving `skip_duplicates`: uploads and URL import read it too, so it stays in Image Processing.
- Import from URL behaviour and regular uploads (the URL-import settings only move category, FR-115-15).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-115-01 | New nullable column `users.server_path` (string, max 4096, default `null`). `null` = import from server disabled for that user. A value starting with `/` = enabled. `/` alone = full access; any other value = restricted to that directory and its descendants (the **import root**). | Column present after migration; `User::$server_path` typed `?string`. | — | — | None. | Owner request |
| FR-115-02 | The server owner sets `server_path` when creating or editing a user (FR-115-19) (`POST /api/v2/UserManagement`, `PATCH /api/v2/UserManagement`). `UserManagementResource` exposes it. Stored normalised: trailing `/` removed (except for `/`), repeated `/` collapsed. `server_path` is always considered sent: absent, `null` or empty string → `null`, on create and on update. | Value persisted and returned. | Must start with `/`; no `.` segment; no NUL byte; none of the sequences `..`, `%2e`, `%2f`, `\` (case-insensitive, as `SecurePathController`); must be an existing directory readable by PHP at save time. Otherwise 422 on `server_path`. | — | None. | Owner request |
| FR-115-03 | **Amended by FR-118-14 (ADR-118-01): `server_path` alone grants import, `may_upload`/`may_administrate` no longer count.** `AlbumPolicy::canImportFromServer(User)` = `features.disable-import-from-server` is false **and** `server_path !== null` **and** (`may_administrate` or `may_upload`). The `owner_id` check is removed and admins do not skip it (`AlbumPolicy::before()` leaves this ability to the rule). `can_import_from_server` in album rights follows. | User with a path and upload rights sees "Import from server" in v7/v8 menus. | — | Policy false → 403 on all three `Import` routes, entry hidden. | None. | Owner request |
| FR-115-04 | `GET /api/v2/Import` returns `directory` = the user's import root, or `base_path()` when `server_path` is `/`; new field `is_root_folder_album` (config `import_root_folder_as_album`). No restriction flag: the dialog needs none, restriction is enforced by the requests (`is_restricted` removed 2026-11-21). `import_via_symlink` follows the config for every user (Q-115-10). | Dialog opens on that directory with the right toggles. | — | Restricted import root missing or unreadable → 422 "Import directory is not available", dialog shows the message. | TE-115-02. | Owner request, Q-115-01 A |
| FR-115-05 | `GET /api/v2/Import::browse` for a restricted user: the requested directory is first normalised lexically (`.`/`..` resolved without touching the disk) and must equal the import root or start with `<import root>/`; then its `realpath()` must pass the same test against `realpath(<import root>)`. Only then is `is_dir()` evaluated. `..` is not listed when the directory is the import root. Child entries that are symbolic links are never listed, and a requested directory that is itself a link (however spelled: `link/`, `link/.`) is refused with 422 "invalid directory" after containment, for every user (Q-115-11). Full-access users: otherwise unchanged. | Child directories of an allowed directory are listed. | Lexically outside, or real path outside → 403 with one generic message, before any `is_dir()`/`realpath()` on the outside path. Inside but missing → 422 "invalid directory" as today. | Unreadable directory → empty list as today. | TE-115-01. | Owner request, NFR-115-01, Q-115-01 A |
| FR-115-06 | `POST /api/v2/Import`: every entry of `directories` passes FR-115-05's containment test before any file-system access. Restricted users may import via symbolic link (Q-115-10). A requested directory that is itself a symbolic link → 422, for every user (Q-115-11). | Import runs. | Any entry outside → 403, nothing imported. | — | TE-115-01. | Owner request, Q-115-01 A |
| FR-115-07 | **Target check amended by FR-118-15: `CAN_IMPORT_INTO` (owner or `grants_upload`) replaces `CAN_UPLOAD`.** Web imports are owned by the requesting user: `Exec::$intended_owner_id` = `Auth::id()` (today `owner_id` config). With `album_id`, the user needs `AlbumPolicy::CAN_UPLOAD` on that album. No other album right is checked: delete-missing (`delete_missing_photos`, `delete_missing_albums`) needs no delete right, because the import-from-server right covers the deletions of the import itself (Q-115-12). FR-115-12 still limits what is deleted. | Albums and photos created with the user as owner; quota checked by `Photo\Create` as for uploads. | No upload right on the target album → 403. | Quota exceeded → per-file failure in the job history, as for uploads. | None. | Owner request, Q-115-12 |
| FR-115-08 | Trust level of imported photos follows the owner, with the rule `ProcessImageJob` uses for uploads: owner `may_administrate` → `trusted`, else `owner->upload_trust_level`. One rule for the web import (owner = requester), `lychee:sync` (owner = `--owner_id` or its default), the per-user sync (FR-115-13) and zip extraction (`ExtractZip` runs through `Exec`, owner = uploader). Resolved once per `Exec` run and passed to `ImportDTO` and `ImportImageJob`; no hardcoded `TRUSTED` remains. No picker. | Photo `upload_trust_level`/`is_validated` match the owner's level; NSFW auto-scan (Feature 045) applies per that level. | — | Owner deleted between dispatch and job → job fails as today. | None. | Owner request, Q-115-04 A, FR-033-03 |
| FR-115-09 | New config `import_root_folder_as_album` (bool, default `1` = today). When `0`, the selected folder is not created as an album; the target (selected album, or gallery root) takes its place: sub-folders become albums directly under the target, the folder's own images go into the target album, or into Unsorted (no album) when the target is the gallery root. Same for `lychee:sync` directory paths. | `/import/Example/{a.jpg, Trip/b.jpg}` into gallery root → album `Trip` at root holding `b.jpg`, `a.jpg` in Unsorted; no album `Example`. | — | — | None. | Owner request, Q-115-05 A |
| FR-115-10 | Delete-missing with FR-115-09 at `0`: a target album is treated as the folder, so `delete_missing_photos` removes its photos not on disk and `delete_missing_albums` its sub-albums not on disk (within FR-115-12). At the gallery root neither applies to the root level, only below it. Whatever the settings, delete-missing never deletes a photo that has no album (Unsorted). | Re-sync after removing `a.jpg` and `Trip/` from disk, target album `T` → both removed from `T`. | — | — | Existing import report events. | Q-115-05 A, owner directive "do not delete unsorted photos" |
| FR-115-11 | Symbolic links in the import tree (`BuildTree`) are never followed, wherever they point, for every user (web import, per-user and explicit `lychee:sync`): an entry for which `is_link()` is true is skipped with a warning. Without followed links there are no loops, so no visited set. A directory or file named explicitly that is itself a link is refused: browse and web import 422 (FR-115-05/06), `lychee:sync` path "Path is a symbolic link" and exit 1 (`ImportPathGuard::isLink()`). A folder's `.lycheeignore` that is a link is not read either: it is skipped with the same warning, and the folder is imported without its local ignore list. Links higher up in the path (server path, install directory) are resolved as before. | Regular files and folders imported. | — | Any link (file or folder, inside or outside the root, looping) → skipped, rest imported. | Report warning `symlink_skipped` (entry path). | Owner directive 2026-11-21, Q-115-11, ADR-115-01 |
| FR-115-12 | New config `import_scope_to_owner` (bool, default `1`). On: `CreateNonExistingAlbums` reuses only albums owned by the owner a new album would get. Below a parent album that is the parent's owner, because `Album\Create` gives a child its parent's owner. At the gallery root it is the intended owner. `DeleteMissingAlbums` and `DeleteMissingPhotos` only delete albums and photos owned by the intended owner. A deleted album takes its whole subtree, other owners' sub-albums included: content of other users inside the importer's album does not belong there (owner, PR #4989). a file whose duplicate photo belongs to another user is skipped and reported, whatever `skip_duplicates_not_owned` says: `ImportMode` carries the scope (server imports only, web and CLI; uploads, zip and URL imports keep `false`) and `ThrowUnownedDuplicate` rejects that duplicate before any metadata resync (`HydrateMetadata`, `SaveIfDirty`) and before `SetParent`, so the other user's photo is neither modified nor added to the importer's album. Off: today's behaviour (any owner; duplicates follow `skip_duplicates_not_owned`). Applies to web and CLI, admins included. | Alice imports `Holidays` at the root where Bob has `Holidays` → new album owned by Alice; Alice's delete-missing in a shared album leaves Bob's photos; Alice imports a file identical to one of Bob's photos → skipped, Bob's photo unchanged. | — | — | Rejected duplicate (`PhotoRejectedException`) handled as a skip, as for `skip_duplicates_not_owned`: job history `SKIPPED`, one `info` line, no failed job on an asynchronous queue, rethrown on the `sync` queue (Q-115-13, ADR-114-01, FR-114-01/03). | Q-115-03 A + owner directive "config option, on by default", Q-115-09 A |
| FR-115-13 | `lychee:sync` `paths` becomes optional. New config `sync_users_server_paths` (bool, default `0`). With no path and the config on, the command syncs, in id order, every user whose `server_path` is set and not `/` and who may import (FR-115-03), as that user (owner, FR-115-08 trust level, FR-115-11 import root), into the gallery root, with the command's import options, `import_via_symlink` included (Q-115-10). Users with `/` are listed as skipped. `--owner_id` given explicitly limits the run to that user; its default moves from the signature to runtime (first admin, as today, when paths are given). `--album_id` with no path → error. | Each eligible user's folder synced; one summary line per user. | No path and config off → error "No path given. Pass paths, or enable sync_users_server_paths.", exit 1. `--album_id` with no path → error, exit 1. `--owner_id` of a user not eligible → error, exit 1. | A user whose path is missing or empty → warning, next user; exit 1 if any user failed. | One `line()` per user (id, username, path, job count or reason skipped). | Owner request, Q-115-06 A |
| FR-115-14 | Upgrade and first admin: the migration adding `server_path` sets it to `/` on the user named by `owner_id` (no-op when none), every other user `null`. The first admin of a new install gets `/` on every creation path: v7 installer (`SetUpAdminController::create`), v8 admin setup (`ProfileController::store`), `lychee:create_user` when no user exists. Through `Actions\User\Create::do(..., ?string $server_path = null)`. | Existing owner keeps import; new install's admin can import at once. | — | — | None. | Q-115-02 A + owner directive "also when creating the first admin" |
| FR-115-15 | New config category `import` ("Import"), ordered between Image Processing and Search, with the description "Settings for importing photos from a folder on the server and from a URL." It holds, in this order: the server-import keys `import_root_folder_as_album`, `import_scope_to_owner`, `sync_users_server_paths` and the moved `delete_imported`, `import_via_symlink`, `skip_duplicates_early`, `sync_delete_missing_photos`, `sync_delete_missing_albums`, `sync_dry_run`; then the URL-import keys moved from Admin: `import_via_url_forbidden_localhost`, `import_via_url_forbidden_local_ip`, `import_via_url_require_https`, `import_via_url_forbidden_ports`, `import_via_url_block_redirect`. The migration's `down()` puts the moved keys back in their former category (Image Processing, or Admin for the URL keys) with their old order. | Settings page shows the category, its description and fourteen settings. | — | — | None. | Owner suggestion (Q-115-03 answer) |
| FR-115-16 | v7 and v8 user create/edit forms gain a "Server import path" text field (empty = disabled), with a hint. The field is shown only when `user_management.can_edit_server_path` is true (FR-115-19). The form always sends `server_path`: the field value for the owner, `null` for other admins (ignored by the server, so a stale stored path cannot fail their save with 422). The users list shows nothing new. | Owner types `/srv/import/alice`, saves, user can import from there. Another admin sees no field and saves without touching the path. | 422 message shown under the field. | — | None. | Owner request, Q-115-07 |
| FR-115-17 | v7 and v8 import dialog: the "import via symbolic link" toggle is shown to every user (Q-115-10). When `is_root_folder_album` is `false`, a hint under the target says the folder's content goes straight into the target and that delete-missing then applies to the target album. | — | — | — | None. | Q-115-01 A, Q-115-05 A |
| FR-115-18 | `ImportImageJob` carries the import root (real path, nullable). When it runs with a root, it checks the file's `realpath()` again: outside → the job fails (history `FAILURE`), nothing is imported and the source is not deleted. The check narrows the window but does not close it: a swap after it is accepted (NFR-115-06, Q-115-08 D). | File unchanged since the scan → imported. | — | File replaced by a link outside between scan and job → failure, log warning. | `Log::warning` (owner id, file path). | Q-115-01 A, NFR-115-06 |
| FR-115-19 | Only the server owner (`may_administrate` and `id` = config `owner_id`) may set `server_path`. New ability `UserPolicy::CAN_EDIT_SERVER_PATH`, which admins do not skip (`UserPolicy::before()` answers it with the owner check, as `AlbumPolicy` does for FR-115-03). `AddUserRequest` and `SetUserSettingsRequest` validate `server_path` for every admin (FR-115-02) and read it in `processValidatedValues()` only when the ability passes: for anyone else the value, whatever it is (`null`, empty string and absent included), is ignored, so a new user gets `null` and an edited user keeps the stored path (`SetUserSettingsRequest::serverPath()` returns it, `Save` always writes `server_path`). `UserManagementRightsResource` gains `can_edit_server_path`. Reading `server_path` in `GET /api/v2/UserManagement` is unchanged. | Owner creates or edits a user with `server_path` → saved as in FR-115-02. Another admin creates or edits a user, with or without the key → saved, new user at `null`, edited user's path unchanged. | Invalid `server_path` → 422 for any admin (FR-115-02). | — | None. | Owner directive 2026-11-16 and 2026-11-17, Q-115-07 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-115-01 | A restricted user learns nothing about the file system outside the import root: outside paths are refused before any disk access, with the same response whether they exist or not. | Today's browse checks `is_dir()` first, so a restricted user could probe arbitrary paths. | Feature test: an existing and a missing outside path give identical 403 bodies. | FR-115-05, FR-115-06 | Owner request ("avoid files inspection") |
| NFR-115-02 | Containment compares real paths with a trailing separator (`/import/alice` never admits `/import/alice2`). | Prefix bypass. | Unit test on the helper. | FR-115-05, FR-115-11, FR-115-18 | Security |
| NFR-115-03 | Path checks are one pure helper (`ImportPathGuard`) returning an enum (`INSIDE`, `OUTSIDE`, `MISSING`), shared by browse, import, tree builder, job and sync. | AGENTS.md straight-line increments. | Unit tests cover each value. | FR-115-05, FR-115-06, FR-115-11, FR-115-18 | AGENTS.md |
| NFR-115-04 | No extra query per file: owner, trust level, import root and settings are resolved once per run; the job carries the import root in its payload. | Large imports. | Query count in `ImportFromServerTest` unchanged per file. | FR-115-08, FR-115-18 | Performance |
| NFR-115-05 | Full-access users (`/`) see today's browse and import behaviour, apart from FR-115-07/08 (owner, trust level), FR-115-05/11 (links neither listed nor followed) and FR-115-12's owner scope. | No regression for single-user installs. | Existing `ImportFromServerBrowseTest` / `ImportFromServerTest` stay green after fixture update. | FR-115-04 | Owner request |
| NFR-115-06 | A restricted import checks each file against the import root at scan time and again right before the job reads it (FR-115-18), so a file swapped between the scan and the job is not read. The window left after that check (the reads, and the `delete_imported` deletion) is an accepted risk (Q-115-08 D). A user who controls their folder can win it to have a file outside the root read, or deleted when it has an image or video extension and PHP can write to its folder. | Users who control their folder (network share) can swap files. | S-115-27. | FR-115-11, FR-115-18 | Q-115-01 A, Q-115-08 D, ADR-115-01 |
| NFR-115-07 | No `realpath()` per tree entry: links are detected with `is_link()`, one `realpath()` per directory (reserved folder check). | Large trees. | Code review of `BuildTree`. | FR-115-11 | Q-115-11 |

## UI / Interaction Mock-ups

User create/edit (v8, v7 equivalent), as the server owner (left) and as another admin (right, FR-115-19):
```
+------------------------------------------------+   +------------------------------------------------+
| Edit user                                       |   | Edit user                                       |
| Username      [ alice                        ]  |   | Username      [ alice                        ]  |
| Password      [ ••••••                       ]  |   | Password      [ ••••••                       ]  |
| [x] Upload rights                               |   | [x] Upload rights                               |
| [x] Can edit own settings                       |   | [x] Can edit own settings                       |
| Upload trust level  [ Check               v ]   |   | Upload trust level  [ Check               v ]   |
| Server import path  [ /srv/import/alice      ]  |   |                                                 |
|   Empty: disabled. "/": whole server.           |   |                                                 |
|   Otherwise: only this folder and below.        |   |                                                 |
|                         [ Cancel ] [ Save ]     |   |                         [ Cancel ] [ Save ]     |
+------------------------------------------------+   +------------------------------------------------+
```

Import dialog, restricted user at the import root (no `..`) and one level down; `import_root_folder_as_album = 0` shows the hint:
```
+-----------------------------------------+   +-----------------------------------------+
| Import from server                       |   | Import from server                       |
| Selected directory                       |   | Selected directory                       |
| /srv/import/alice                        |   | /srv/import/alice/2026                   |
|  > 2025                                  |   |  > ..                                    |
|  > 2026                                  |   |  > Holidays                              |
|  > Family                                |   |  > Birthday                              |
| (i) The folder's content goes straight   |   | (i) The folder's content goes straight   |
|     into the target album.               |   |     into the target album.               |
| [ ] Delete imported files                |   | [ ] Delete imported files                |
| [ ] Import via symbolic link             |   | [ ] Import via symbolic link             |
| [x] Skip duplicates                      |   | [x] Skip duplicates                      |
| [ ] Delete missing photos / albums       |   | [ ] Delete missing photos / albums       |
|                  [ Cancel ] [ Import ]   |   |                  [ Cancel ] [ Import ]   |
+-----------------------------------------+   +-----------------------------------------+
```

Settings, new category:
```
Import
  Settings for importing photos from a folder on the server and from a URL.
  Import the selected folder as an album ............. [x]
  Only reuse and delete the importer's own content ... [x]
  lychee:sync with no path syncs every user's folder . [ ]
  Delete originals after import ...................... [ ]
  Use symbolic links instead of copies ............... [ ]
  Skip duplicates early (sync) ....................... [x]
  Sync: delete missing photos ........................ [ ]
  Sync: delete missing albums ........................ [ ]
  Sync: dry run ...................................... [x]
  URL: forbid localhost .............................. [x]
  URL: forbid local IP addresses ..................... [x]
  URL: require https ................................. [x]
  URL: only ports 80 and 443 ......................... [x]
  URL: do not follow redirections .................... [x]
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-115-01 | User with `server_path = null` → `Import` routes 403, `can_import_from_server` false. |
| S-115-02 | User with a path but neither `may_upload` nor `may_administrate` → 403. |
| S-115-03 | `DISABLE_IMPORT_FROM_SERVER=true`, admin with `/` → 403. |
| S-115-04 | Full-access user: options `directory` = `base_path()`; browse `/` lists root, `..` shown below `/`. |
| S-115-05 | Restricted user: options `directory` = import root, `import_via_symlink` = config; browse import root → children, no `..`; browse a child → `..` listed. |
| S-115-06 | Restricted user browses `/etc` (exists) and `/does/not/exist` → identical 403. |
| S-115-07 | Restricted user browses `<root>/../bob` → 403 (lexical check). |
| S-115-08 | Restricted `/srv/import/alice`, browse `/srv/import/alice2` → 403. |
| S-115-09 | Restricted user imports a directory inside the root → photos and albums owned by that user. |
| S-115-10 | Restricted user imports two directories, one outside → 403, nothing imported. |
| S-115-11 | Import into an album without upload right → 403. |
| S-115-12 | `lychee:sync --owner_id=<check user>` → photos `upload_trust_level = check`, `is_validated = false`. Web import by a `check` user → same. |
| S-115-13 | `lychee:sync --owner_id=<admin with check level>` → `trusted`, `is_validated = true`. |
| S-115-14 | `import_root_folder_as_album = 1` → today's tree (album named after the selected folder). |
| S-115-15 | `import_root_folder_as_album = 0`, target album `T` → sub-folders under `T`, root images in `T`, no album named after the folder. |
| S-115-16 | `lychee:sync` with no path, `sync_users_server_paths = 0` → error, exit 1, nothing imported. |
| S-115-17 | `lychee:sync` with no path, config on, two users with paths → each folder imported as its user into the gallery root. |
| S-115-18 | Same, one user's path missing → warning, other user still synced, exit 1. |
| S-115-19 | User management: save `server_path` `srv/x`, `/srv/../etc`, missing directory → 422; `/srv/import/alice/` → stored `/srv/import/alice`; empty → `null`. |
| S-115-20 | Restricted import root deleted after save → options 422 "Import directory is not available". |
| S-115-21 | Link inside the root (folder or file, any user) → not listed by browse, skipped in import with `symlink_skipped`. Named explicitly (browse or import, any user, `link/` and `link/.` included) → 422. |
| S-115-22 | Link to an outside directory → not listed by browse, skipped in import with `symlink_skipped`; requested explicitly by a restricted user → 403. |
| S-115-23 | Link to an outside image file → skipped with `symlink_skipped`, other files imported. |
| S-115-24 | Loop `a/loop → a` (any user) → `loop` skipped as a link, `a` imported once, no `loop` album. |
| S-115-25 | Two links to the same folder → both skipped, the folder imported once through its real path. |
| S-115-26 | Explicit `lychee:sync` path containing links → skipped too; a path that is itself a link (folder or file) → "Path is a symbolic link", exit 1, nothing queued. |
| S-115-27 | Job with an import root; file replaced by a link outside before the job runs → job `FAILURE`, no photo, source not deleted. |
| S-115-28 | Restricted user posts `import_via_symlink = true` → import queued with symbolic links (Q-115-10); `lychee:sync` with no path and `--import_via_symlink=1` → every user's jobs link. |
| S-115-29 | `import_scope_to_owner = 1`: album with the same title under the same parent owned by another user → new album for the importer. |
| S-115-30 | `import_scope_to_owner = 1`: delete-missing in a shared target leaves the other owner's photos and sub-albums. |
| S-115-31 | `import_scope_to_owner = 0`: today's reuse and deletion across owners. |
| S-115-44 | `import_scope_to_owner = 1`, target album owned by another user than the importer: the same folder imported twice → the child albums created the first time (owned by the target's owner) are reused, no duplicate albums. |
| S-115-45 | A folder whose `.lycheeignore` is a symbolic link → the link is not read (`symlink_skipped`), the folder's images are imported without its patterns. |
| S-115-43 | `import_scope_to_owner = 1`, `skip_duplicates_not_owned = 0`: importing a file whose duplicate is another user's photo → rejected (with and without `resync_metadata`), job history `SKIPPED` (Q-115-13), the other user's photo keeps its metadata and albums. Scope off → today's behaviour (duplicate added to the target album). |
| S-115-32 | `import_root_folder_as_album = 0`, gallery root target: root images → Unsorted; re-sync with both delete-missing flags after removing files and folders from disk → Unsorted photos and root-level albums untouched, deeper albums cleaned. |
| S-115-33 | `import_root_folder_as_album = 0`, target album `T`, both delete-missing flags → `T`'s own photos and sub-albums not on disk deleted (owner scope applies). |
| S-115-34 | Sync with no path: a `/` user is listed as skipped, not imported. |
| S-115-35 | Sync with no path, explicit `--owner_id` → only that user synced. |
| S-115-36 | Sync with no path and `--album_id` → error, exit 1. |
| S-115-37 | Sync with no path: user with a path but no upload rights → skipped. Explicit `--owner_id` of that user → error, exit 1. |
| S-115-38 | Migration: `owner_id` user gets `/`, another admin and a regular user `null`. |
| S-115-39 | First admin via v7 installer, v8 admin setup and `lychee:create_user` on an empty database → `/`; second user via `lychee:create_user` → `null`. |
| S-115-40 | Settings list: category `import` has its description and holds the fourteen keys (nine server, five URL); `skip_duplicates` stays in Image Processing. |
| S-115-41 | Dialog (v7, v8), restricted user: symlink toggle shown; `import_root_folder_as_album = 0` → hint shown. |
| S-115-42 | Admin who is not the server owner: create or edit with a valid `server_path` (a path, `/`, empty string or `null`) → saved, value ignored (new user at `null`, edited user's path unchanged); same request without the key → same result; an invalid `server_path` (missing directory, traversal sequence) → 422 on `server_path`, nothing created, edited user's path unchanged; `user_management.can_edit_server_path` false (owner: true). User form (v7, v8) hides the field for that admin. |

## Test Strategy
- **Unit:** `ImportPathGuardTest` (NFR-115-02/03): inside, equal to root, outside, prefix trap, lexical `..`, missing, real path outside through a link, no root. `ImportImageJobTest` (S-115-27, trust level in the payload).
- **Import jobs:** `SkippedDuplicateJobTest` (S-115-43).
- **REST API (v2):** `ImportFromServerOptionsTest`, `ImportFromServerBrowseTest`, `ImportFromServerTest` (S-115-01 … 11, 20 … 23, 28); `Feature_v2/UserManagement/ServerPathTest` (S-115-19, S-115-42); `Feature_v2/User/AdminSetupTest`, `Install/AdminTest` (S-115-39). Written first, seen red.
- **Artisan commands:** `SyncTest` (S-115-12 … 18, 24 … 26, 29 … 37, 44, 45), new `CreateUserCommandTest` (S-115-39). Written first, seen red.
- **Migrations / configs:** test on the migrated SQLite database for S-115-38 (owner gets `/`) and S-115-40 (category membership).
- **Frontend (v7/v8):** no automated suite for these forms; `vp check`, `vp run typecheck`, manual check of S-115-41 and the user form (S-115-42 as owner and as another admin).
- **Docs/Contracts:** `UserManagementResource` and `ImportFromServerOptionsResource` regenerated with `php artisan typescript:transform`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-115-01 | `users.server_path` `?string`, normalised, FR-115-01/02 | migration, `User`, user management |
| DO-115-02 | Config `import_root_folder_as_album` bool, default `1`, category `import` | configs, `Exec`, pipes |
| DO-115-03 | Config `sync_users_server_paths` bool, default `0`, category `import` | configs, `lychee:sync` |
| DO-115-04 | `App\Actions\Import\ImportPathGuard` and enum `ImportPathStatus` (`INSIDE`, `OUTSIDE`, `MISSING`) | requests, controller, `BuildTree`, `ImportImageJob`, `lychee:sync` |
| DO-115-05 | Config `import_scope_to_owner` bool, default `1`, category `import`; carried by `ImportDTO` and, for duplicates, by `ImportMode` (FR-115-12) | configs, pipes, `ImportMode`, `ThrowUnownedDuplicate` |
| DO-115-06 | Config category `import` with its description, the six moved server keys and the five moved URL keys (FR-115-15) | configs |
| DO-115-07 | `ImportFromServerOptionsResource` gains `is_root_folder_album` | REST, v7/v8 |
| DO-115-08 | `ImportDTO` / `ImportImageJob` gain `upload_trust_level` and `?string import_root` | import pipeline |
| DO-115-09 | `ServerPathRule` validation rule (FR-115-02) | user management requests |
| DO-115-10 | `UserPolicy::CAN_EDIT_SERVER_PATH` (owner only, no admin bypass) and `UserManagementRightsResource::$can_edit_server_path` (FR-115-19) | user management requests, v7/v8 user form |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-115-01 | REST GET /api/v2/Import | `directory` from `server_path`, `is_root_folder_album` | `ImportFromServerOptionsResource` |
| API-115-02 | REST GET /api/v2/Import::browse | containment for restricted users, links never listed | `ImportFromServerBrowseRequest` |
| API-115-03 | REST POST /api/v2/Import | containment, owner = requester, upload right on target | `ImportFromServerRequest` |
| API-115-04 | REST POST /api/v2/UserManagement, PATCH /api/v2/UserManagement | `server_path` field, read from the server owner only (ignored otherwise, FR-115-19) | `AddUserRequest`, `SetUserSettingsRequest`, `UserManagementResource` |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-115-01 | `php artisan lychee:sync [paths*]` | `paths` optional; trust level from owner; `--owner_id` default resolved at runtime; no-path per-user sync behind `sync_users_server_paths` |
| CLI-115-02 | `php artisan lychee:create_user` | first user gets `server_path = /` |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-115-01 | log notice "import path denied" | user id, requested path |
| TE-115-02 | log warning "import root unavailable" | user id, `server_path` |
| TE-115-03 | import report warning `symlink_skipped` | entry path |
| TE-115-04 | removed (Q-115-11: no visited set) | — |
| TE-115-05 | log warning "import file left the import root" | owner id, file path |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-115-01 | temporary directories built in test `setUp` (alice root, sibling `alice2`, outside dir, inside/outside/loop links, copies of `tests/Samples` images) | containment and link scenarios; no committed files |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-115-01 | Import dialog at restricted import root | No `..` entry |
| UI-115-02 | User form `server_path` error | 422 message under the field |
| UI-115-03 | Import dialog with `import_root_folder_as_album = 0` | Hint under the target |
| UI-115-04 | User form opened by an admin who is not the server owner | No server import path field |

## Telemetry & Observability
Laravel log lines (TE-115-01/02/05) and import report events (TE-115-03/04); no new telemetry channel.

## Documentation Deliverables
- Roadmap entry #115.
- ADR-115-01 (import access model: per-user import root, link containment, owner scope, upgrade grant).
- Knowledge map: import access per user (`users.server_path`), `ImportPathGuard`, owner scope, new config category.
- New how-to `docs/specs/2-how-to/import-from-server.md` (per-user folders, settings, `sync_users_server_paths` cron line).

## Fixtures & Sample Data
Temporary directories only (FX-115-01).

## Spec DSL

```
domain_objects:
  - id: DO-115-01
    name: users.server_path
    type: "?string"
    constraints: "null | '/' | absolute normalised existing directory"
  - id: DO-115-02
    name: import_root_folder_as_album
    type: bool
    default: 1
  - id: DO-115-03
    name: sync_users_server_paths
    type: bool
    default: 0
  - id: DO-115-04
    name: ImportPathGuard
    result: [INSIDE, OUTSIDE, MISSING]
  - id: DO-115-05
    name: import_scope_to_owner
    type: bool
    default: 1
  - id: DO-115-06
    name: config_category.import
    keys: [import_root_folder_as_album, import_scope_to_owner, sync_users_server_paths, delete_imported, import_via_symlink, skip_duplicates_early, sync_delete_missing_photos, sync_delete_missing_albums, sync_dry_run, import_via_url_forbidden_localhost, import_via_url_forbidden_local_ip, import_via_url_require_https, import_via_url_forbidden_ports, import_via_url_block_redirect]
  - id: DO-115-07
    name: ImportFromServerOptionsResource
    fields: [is_root_folder_album]
  - id: DO-115-08
    name: ImportDTO/ImportImageJob
    fields: [upload_trust_level, import_root]
  - id: DO-115-09
    name: ServerPathRule
  - id: DO-115-10
    name: UserPolicy::CAN_EDIT_SERVER_PATH
    fields: [can_edit_server_path]
routes:
  - id: API-115-01
    method: GET
    path: /api/v2/Import
  - id: API-115-02
    method: GET
    path: /api/v2/Import::browse
  - id: API-115-03
    method: POST
    path: /api/v2/Import
  - id: API-115-04
    method: POST|PATCH
    path: /api/v2/UserManagement
cli_commands:
  - id: CLI-115-01
    command: php artisan lychee:sync [paths*]
  - id: CLI-115-02
    command: php artisan lychee:create_user
telemetry_events:
  - id: TE-115-01
    event: import.path_denied
  - id: TE-115-02
    event: import.root_unavailable
  - id: TE-115-03
    event: import.symlink_skipped
  - id: TE-115-04
    event: removed
  - id: TE-115-05
    event: import.file_left_root
fixtures:
  - id: FX-115-01
    path: tests (temporary directories)
ui_states:
  - id: UI-115-01
    description: Import dialog at restricted root, no ".."
  - id: UI-115-02
    description: server_path validation error
  - id: UI-115-03
    description: root-folder-as-album hint
  - id: UI-115-04
    description: user form without server_path for non-owner admins
```

## Appendix
Code read on 2026-11-15:
- `AlbumPolicy::canImportFromServer` — `owner_id` user only, plus the env kill switch.
- `ImportFromServerController::browse` — `is_dir()` on the raw request path, then lists it; `..` hidden only at `/`.
- `ImportFromServerController::get_exec` — owner = `owner_id` config, not the requester.
- `ImportDTO` and `ImportImageJob` — `UserUploadTrustLevel::TRUSTED` hardcoded.
- `BuildTree` — `glob()` + `is_dir()`, so symbolic links to directories and files are followed with no containment and no loop guard.
- `CreateNonExistingAlbums` — reuses any album with the same title under the same parent, whoever owns it.
- `DeleteMissingAlbums` / `DeleteMissingPhotos` — delete every child album / photo of the matched album not found on disk, whoever owns it; both skip a node with no album, so Unsorted is never touched today.
- First admin is created in `SetUpAdminController::create` (v7 installer), `ProfileController::store` (v8 setup, Feature 051) and `lychee:create_user` on an empty `users` table.
- Import settings today sit in `Image Processing`; `skip_duplicates` is also read by `ProcessImageJob` and `FromUrl`.
