# Feature 115 – Server Import Improvements

| Field | Value |
|-------|-------|
| Status | Draft (Q-115-01 … 06 resolved 2026-11-15, plan and tasks written) |
| Last updated | 2026-11-15 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #115 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Import from server (`POST/GET /api/v2/Import`, `GET /api/v2/Import::browse`, `lychee:sync`) is today reserved to the user named by the `owner_id` config, browses the whole file system, follows every symbolic link, always creates the selected folder as an album, reuses and deletes albums and photos whoever owns them, and stamps every imported photo `trusted` (`ImportDTO`, `ImportImageJob` hardcode `UserUploadTrustLevel::TRUSTED`). This feature opens import to other users through a per-user `server_path` that both grants access and confines it (ADR-115-01), contains symbolic links inside that path, keeps imports away from other users' content, applies the owner's upload trust level, adds a setting to import a folder's content without the folder itself becoming an album, lets `lychee:sync` with no path sync every user's own folder, and gathers the import settings in their own category.

Affected layers: `users` table and `User` model, `Actions\User\Create`/`Save` and the three first-admin paths, user management API (v2) and forms (v7, v8), `AlbumPolicy::canImportFromServer`, `ImportFromServer*` requests/controller/resources, `Actions\Import\Exec` and its pipes, `ImportDTO`, `ImportImageJob`, `lychee:sync`, configs and config categories, v7/v8 import dialog, translations (23 locales).

## Goals
- An admin grants import from server per user with `server_path`; disabled (`null`) by default, `/` for the existing owner and for the first admin of a new install.
- A user with a restricted `server_path` browses and imports only inside it; nothing outside it is listed, disclosed or imported, through symbolic links included.
- `server_path = /` keeps today's unrestricted browsing, starting at Lychee's working directory.
- Imports never merge into or delete another user's albums and photos (setting, on by default).
- Imported photos carry the trust level of the user who owns them, on the web and on the CLI.
- A setting imports the content of the selected folder straight into the target instead of into an album named after the folder.
- `lychee:sync` with no path syncs every user's `server_path` as that user, behind a setting disabled by default.
- Import settings live in a new "Import from server" settings category.

## Non-Goals
- Letting users set their own `server_path` (admin only).
- `lychee:create_user` / `lychee:update_user` options for `server_path` (the first user still gets `/`, FR-115-14).
- A trust level picker in the dialog or a `--trust_level` option (Q-115-04 B/C, rejected).
- A per-user target album for the no-path sync (the target is the gallery root, FR-115-13).
- Changing `DISABLE_IMPORT_FROM_SERVER`: it still turns the feature off for everyone, admins included.
- Restricting the paths an operator passes to `lychee:sync` (shell access is already full access).
- Moving `skip_duplicates`: uploads and URL import read it too, so it stays in Image Processing.
- Import from URL and regular uploads.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-115-01 | New nullable column `users.server_path` (string, max 4096, default `null`). `null` = import from server disabled for that user. A value starting with `/` = enabled. `/` alone = full access; any other value = restricted to that directory and its descendants (the **import root**). | Column present after migration; `User::$server_path` typed `?string`. | — | — | None. | Owner request |
| FR-115-02 | Admins set `server_path` when creating or editing a user (`POST /api/v2/UserManagement`, `PATCH /api/v2/UserManagement`). `UserManagementResource` exposes it. Stored normalised: trailing `/` removed (except for `/`), repeated `/` collapsed. Empty string or absent on create → `null`; absent on update → unchanged; `null` or empty string on update → `null`. | Value persisted and returned. | Must start with `/`; no `.` or `..` segment; no NUL byte; must be an existing directory readable by PHP at save time. Otherwise 422 on `server_path`. | — | None. | Owner request |
| FR-115-03 | `AlbumPolicy::canImportFromServer(User)` = `features.disable-import-from-server` is false **and** `server_path !== null` **and** (`may_administrate` or `may_upload`). The `owner_id` check is removed. `can_import_from_server` in album rights follows. | User with a path and upload rights sees "Import from server" in v7/v8 menus. | — | Policy false → 403 on all three `Import` routes, entry hidden. | None. | Owner request |
| FR-115-04 | `GET /api/v2/Import` returns `directory` = the user's import root, or `base_path()` when `server_path` is `/`; new fields `is_restricted` (`server_path !== '/'`) and `is_root_folder_album` (config `import_root_folder_as_album`). For a restricted user `import_via_symlink` is `false`. | Dialog opens on that directory with the right toggles. | — | Restricted import root missing or unreadable → 422 "Import directory is not available", dialog shows the message. | TE-115-02. | Owner request, Q-115-01 A |
| FR-115-05 | `GET /api/v2/Import::browse` for a restricted user: the requested directory is first normalised lexically (`.`/`..` resolved without touching the disk) and must equal the import root or start with `<import root>/`; then its `realpath()` must pass the same test against `realpath(<import root>)`. Only then is `is_dir()` evaluated. `..` is not listed when the directory is the import root. A child entry that is a symbolic link is listed only when its real path is inside the import root. Full-access users: unchanged. | Child directories of an allowed directory are listed. | Lexically outside, or real path outside → 403 with one generic message, before any `is_dir()`/`realpath()` on the outside path. Inside but missing → 422 "invalid directory" as today. | Unreadable directory → empty list as today. | TE-115-01. | Owner request, NFR-115-01, Q-115-01 A |
| FR-115-06 | `POST /api/v2/Import`: every entry of `directories` passes FR-115-05's containment test before any file-system access. A restricted user sending `import_via_symlink = true` gets 422. | Import runs. | Any entry outside → 403, nothing imported. `import_via_symlink` refused → 422 on that field. | — | TE-115-01. | Owner request, Q-115-01 A |
| FR-115-07 | Web imports are owned by the requesting user: `Exec::$intended_owner_id` = `Auth::id()` (today `owner_id` config). With `album_id`, the user needs `AlbumPolicy::CAN_UPLOAD` on that album. | Albums and photos created with the user as owner; quota checked by `Photo\Create` as for uploads. | No upload right on the target album → 403. | Quota exceeded → per-file failure in the job history, as for uploads. | None. | Owner request |
| FR-115-08 | Trust level of imported photos follows the owner, with the rule `ProcessImageJob` uses for uploads: owner `may_administrate` → `trusted`, else `owner->upload_trust_level`. One rule for the web import (owner = requester), `lychee:sync` (owner = `--owner_id` or its default) and the per-user sync (FR-115-13). Resolved once per `Exec` run and passed to `ImportDTO` and `ImportImageJob`; no hardcoded `TRUSTED` remains. No picker. | Photo `upload_trust_level`/`is_validated` match the owner's level; NSFW auto-scan (Feature 045) applies per that level. | — | Owner deleted between dispatch and job → job fails as today. | None. | Owner request, Q-115-04 A, FR-033-03 |
| FR-115-09 | New config `import_root_folder_as_album` (bool, default `1` = today). When `0`, the selected folder is not created as an album; the target (selected album, or gallery root) takes its place: sub-folders become albums directly under the target, the folder's own images go into the target album, or into Unsorted (no album) when the target is the gallery root. Same for `lychee:sync` directory paths. | `/import/Example/{a.jpg, Trip/b.jpg}` into gallery root → album `Trip` at root holding `b.jpg`, `a.jpg` in Unsorted; no album `Example`. | — | — | None. | Owner request, Q-115-05 A |
| FR-115-10 | Delete-missing with FR-115-09 at `0`: a target album is treated as the folder, so `delete_missing_photos` removes its photos not on disk and `delete_missing_albums` its sub-albums not on disk (within FR-115-12). At the gallery root neither applies to the root level, only below it. Whatever the settings, delete-missing never deletes a photo that has no album (Unsorted). | Re-sync after removing `a.jpg` and `Trip/` from disk, target album `T` → both removed from `T`. | — | — | Existing import report events. | Q-115-05 A, owner directive "do not delete unsorted photos" |
| FR-115-11 | Symbolic links in the import tree (`BuildTree`): every entry is resolved with `realpath()`; a linked directory or file is followed only when its real path is inside the import root. The import root is the user's real `server_path` for a restricted web user and for the per-user sync (FR-115-13), and none (everything inside) for `/` users and explicit `lychee:sync` paths. Entries outside are skipped with a warning. During one run, a real directory already visited is skipped (link loops, two links to one folder), for every user. | Link inside the root imported; loop `a/loop → a` imported once with no nested albums. | — | Link outside → skipped, rest imported. | Report warning `symlink_outside` (entry path), notice `already_visited` (entry path). | Q-115-01 A, ADR-115-01 |
| FR-115-12 | New config `import_scope_to_owner` (bool, default `1`). On: `CreateNonExistingAlbums` reuses only albums owned by the intended owner; `DeleteMissingAlbums` and `DeleteMissingPhotos` only delete albums and photos owned by the intended owner. Off: today's behaviour (any owner). Applies to web and CLI, admins included. | Alice imports `Holidays` at the root where Bob has `Holidays` → new album owned by Alice; Alice's delete-missing in a shared album leaves Bob's photos. | — | — | None. | Q-115-03 A + owner directive "config option, on by default" |
| FR-115-13 | `lychee:sync` `paths` becomes optional. New config `sync_users_server_paths` (bool, default `0`). With no path and the config on, the command syncs, in id order, every user whose `server_path` is set and not `/` and who may import (FR-115-03), as that user (owner, FR-115-08 trust level, FR-115-11 import root), into the gallery root, with the command's import options. Users with `/` are listed as skipped. `--owner_id` given explicitly limits the run to that user; its default moves from the signature to runtime (first admin, as today, when paths are given). `--album_id` with no path → error. | Each eligible user's folder synced; one summary line per user. | No path and config off → error "No path given. Pass paths, or enable sync_users_server_paths.", exit 1. `--album_id` with no path → error, exit 1. `--owner_id` of a user not eligible → error, exit 1. | A user whose path is missing or empty → warning, next user; exit 1 if any user failed. | One `line()` per user (id, username, path, job count or reason skipped). | Owner request, Q-115-06 A |
| FR-115-14 | Upgrade and first admin: the migration adding `server_path` sets it to `/` on the user named by `owner_id` (no-op when none), every other user `null`. The first admin of a new install gets `/` on every creation path: v7 installer (`SetUpAdminController::create`), v8 admin setup (`ProfileController::store`), `lychee:create_user` when no user exists. Through `Actions\User\Create::do(..., ?string $server_path = null)`. | Existing owner keeps import; new install's admin can import at once. | — | — | None. | Q-115-02 A + owner directive "also when creating the first admin" |
| FR-115-15 | New config category `import_from_server` ("Import from server"), ordered between Image Processing and Search. It holds `import_root_folder_as_album`, `import_scope_to_owner`, `sync_users_server_paths` and the moved keys `delete_imported`, `import_via_symlink`, `skip_duplicates_early`, `sync_delete_missing_photos`, `sync_delete_missing_albums`, `sync_dry_run`. The migration's `down()` puts the moved keys back in Image Processing with their old order. | Settings page shows the category with nine settings. | — | — | None. | Owner suggestion (Q-115-03 answer) |
| FR-115-16 | v7 and v8 user create/edit forms gain a "Server import path" text field (empty = disabled), with a hint. The users list shows nothing new. | Admin types `/srv/import/alice`, saves, user can import from there. | 422 message shown under the field. | — | None. | Owner request |
| FR-115-17 | v7 and v8 import dialog: when `is_restricted`, the "import via symbolic link" toggle is hidden and sent as `false`. When `is_root_folder_album` is `false`, a hint under the target says the folder's content goes straight into the target and that delete-missing then applies to the target album. | — | — | — | None. | Q-115-01 A, Q-115-05 A |
| FR-115-18 | `ImportImageJob` carries the import root (real path, nullable). When it runs with a root, it checks the file's `realpath()` again: outside → the job fails (history `FAILURE`), nothing is imported and the source is not deleted. | File unchanged since the scan → imported. | — | File replaced by a link outside between scan and job → failure, log warning. | `Log::warning` (owner id, file path). | Q-115-01 A, NFR-115-06 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-115-01 | A restricted user learns nothing about the file system outside the import root: outside paths are refused before any disk access, with the same response whether they exist or not. | Today's browse checks `is_dir()` first, so a restricted user could probe arbitrary paths. | Feature test: an existing and a missing outside path give identical 403 bodies. | FR-115-05, FR-115-06 | Owner request ("avoid files inspection") |
| NFR-115-02 | Containment compares real paths with a trailing separator (`/import/alice` never admits `/import/alice2`). | Prefix bypass. | Unit test on the helper. | FR-115-05, FR-115-11, FR-115-18 | Security |
| NFR-115-03 | Path checks are one pure helper (`ImportPathGuard`) returning an enum (`INSIDE`, `OUTSIDE`, `MISSING`), shared by browse, import, tree builder, job and sync. | AGENTS.md straight-line increments. | Unit tests cover each value. | FR-115-05, FR-115-06, FR-115-11, FR-115-18 | AGENTS.md |
| NFR-115-04 | No extra query per file: owner, trust level, import root and settings are resolved once per run; the job carries the import root in its payload. | Large imports. | Query count in `ImportFromServerTest` unchanged per file. | FR-115-08, FR-115-18 | Performance |
| NFR-115-05 | Full-access users (`/`) see today's browse and import behaviour, apart from FR-115-07/08 (owner, trust level), FR-115-11's loop guard and FR-115-12's owner scope. | No regression for single-user installs. | Existing `ImportFromServerBrowseTest` / `ImportFromServerTest` stay green after fixture update. | FR-115-04 | Owner request |
| NFR-115-06 | No file outside a restricted import root is read by an import, even when the file system changes between the scan and the job. | Users who control their folder (network share) can swap files. | S-115-27. | FR-115-11, FR-115-18 | Q-115-01 A, ADR-115-01 |
| NFR-115-07 | One `realpath()` per tree entry at most; the visited set holds one string per directory. | Large trees. | Code review of `BuildTree`. | FR-115-11 | Q-115-01 A cons |

## UI / Interaction Mock-ups

User create/edit (v8, v7 equivalent):
```
+------------------------------------------------+
| Edit user                                       |
| Username      [ alice                        ]  |
| Password      [ ••••••                       ]  |
| [x] Upload rights                               |
| [x] Can edit own settings                       |
| Upload trust level  [ Check               v ]   |
| Server import path  [ /srv/import/alice      ]  |
|   Empty: disabled. "/": whole server.           |
|   Otherwise: only this folder and below.        |
|                         [ Cancel ] [ Save ]     |
+------------------------------------------------+
```

Import dialog, restricted user at the import root (no `..`, no symlink toggle) and one level down; `import_root_folder_as_album = 0` shows the hint:
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
| [x] Skip duplicates                      |   | [x] Skip duplicates                      |
| [ ] Delete imported files                |   | [ ] Delete imported files                |
| [ ] Delete missing photos / albums       |   | [ ] Delete missing photos / albums       |
|                  [ Cancel ] [ Import ]   |   |                  [ Cancel ] [ Import ]   |
+-----------------------------------------+   +-----------------------------------------+
```

Settings, new category:
```
Import from server
  Import the selected folder as an album ............. [x]
  Only reuse and delete the importer's own content ... [x]
  lychee:sync with no path syncs every user's folder . [ ]
  Delete originals after import ...................... [ ]
  Use symbolic links instead of copies ............... [ ]
  Skip duplicates early (sync) ....................... [ ]
  Sync: delete missing photos ........................ [ ]
  Sync: delete missing albums ........................ [ ]
  Sync: dry run ...................................... [x]
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-115-01 | User with `server_path = null` → `Import` routes 403, `can_import_from_server` false. |
| S-115-02 | User with a path but neither `may_upload` nor `may_administrate` → 403. |
| S-115-03 | `DISABLE_IMPORT_FROM_SERVER=true`, admin with `/` → 403. |
| S-115-04 | Full-access user: options `directory` = `base_path()`, `is_restricted` false; browse `/` lists root, `..` shown below `/`. |
| S-115-05 | Restricted user: options `directory` = import root, `is_restricted` true, `import_via_symlink` false; browse import root → children, no `..`; browse a child → `..` listed. |
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
| S-115-21 | Restricted user, link inside the root to a folder inside the root → listed by browse, imported. |
| S-115-22 | Restricted user, link to an outside directory → not listed by browse, skipped in import with `symlink_outside`. |
| S-115-23 | Restricted user, link to an outside image file → skipped with `symlink_outside`, other files imported. |
| S-115-24 | Loop `a/loop → a` (any user) → `a` imported once, no `loop` album chain, `already_visited` reported. |
| S-115-25 | Two links to the same folder → its photos imported once. |
| S-115-26 | Explicit `lychee:sync` path containing a link to an outside folder → followed. |
| S-115-27 | Job with an import root; file replaced by a link outside before the job runs → job `FAILURE`, no photo, source not deleted. |
| S-115-28 | Restricted user posts `import_via_symlink = true` → 422. |
| S-115-29 | `import_scope_to_owner = 1`: album with the same title under the same parent owned by another user → new album for the importer. |
| S-115-30 | `import_scope_to_owner = 1`: delete-missing in a shared target leaves the other owner's photos and sub-albums. |
| S-115-31 | `import_scope_to_owner = 0`: today's reuse and deletion across owners. |
| S-115-32 | `import_root_folder_as_album = 0`, gallery root target: root images → Unsorted; re-sync with both delete-missing flags after removing files and folders from disk → Unsorted photos and root-level albums untouched, deeper albums cleaned. |
| S-115-33 | `import_root_folder_as_album = 0`, target album `T`, both delete-missing flags → `T`'s own photos and sub-albums not on disk deleted (owner scope applies). |
| S-115-34 | Sync with no path: a `/` user is listed as skipped, not imported. |
| S-115-35 | Sync with no path, explicit `--owner_id` → only that user synced. |
| S-115-36 | Sync with no path and `--album_id` → error, exit 1. |
| S-115-37 | Sync with no path: user with a path but no upload rights → skipped. Explicit `--owner_id` of that user → error, exit 1. |
| S-115-38 | Migration: `owner_id` user gets `/`, another admin and a regular user `null`. |
| S-115-39 | First admin via v7 installer, v8 admin setup and `lychee:create_user` on an empty database → `/`; second user via `lychee:create_user` → `null`. |
| S-115-40 | Settings list: category `import_from_server` holds the nine keys; `skip_duplicates` stays in Image Processing. |
| S-115-41 | Dialog (v7, v8), restricted user: symlink toggle hidden; `import_root_folder_as_album = 0` → hint shown. |

## Test Strategy
- **Unit:** `ImportPathGuardTest` (NFR-115-02/03): inside, equal to root, outside, prefix trap, lexical `..`, missing, real path outside through a link, no root. `ImportImageJobTest` (S-115-27, trust level in the payload).
- **REST API (v2):** `ImportFromServerOptionsTest`, `ImportFromServerBrowseTest`, `ImportFromServerTest` (S-115-01 … 11, 20 … 23, 28); `CreateUserTest`, `EditUserTest`, `ListUserTest` (S-115-19); `Feature_v2/User/AdminSetupTest`, `Install/AdminSetupTest` (S-115-39). Written first, seen red.
- **Artisan commands:** `SyncTest` (S-115-12 … 18, 24 … 26, 29 … 37), new `CreateUserCommandTest` (S-115-39). Written first, seen red.
- **Migrations / configs:** test on the migrated SQLite database for S-115-38 (owner gets `/`) and S-115-40 (category membership).
- **Frontend (v7/v8):** no automated suite for these forms; `vp check`, `vp run typecheck`, manual check of S-115-41 and the user form.
- **Docs/Contracts:** `UserManagementResource` and `ImportFromServerOptionsResource` regenerated with `php artisan typescript:transform`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-115-01 | `users.server_path` `?string`, normalised, FR-115-01/02 | migration, `User`, user management |
| DO-115-02 | Config `import_root_folder_as_album` bool, default `1`, category `import_from_server` | configs, `Exec`, pipes |
| DO-115-03 | Config `sync_users_server_paths` bool, default `0`, category `import_from_server` | configs, `lychee:sync` |
| DO-115-04 | `App\Actions\Import\ImportPathGuard` and enum `ImportPathStatus` (`INSIDE`, `OUTSIDE`, `MISSING`) | requests, controller, `BuildTree`, `ImportImageJob`, `lychee:sync` |
| DO-115-05 | Config `import_scope_to_owner` bool, default `1`, category `import_from_server` | configs, pipes |
| DO-115-06 | Config category `import_from_server` and the six moved keys (FR-115-15) | configs |
| DO-115-07 | `ImportFromServerOptionsResource` gains `is_restricted`, `is_root_folder_album` | REST, v7/v8 |
| DO-115-08 | `ImportDTO` / `ImportImageJob` gain `upload_trust_level` and `?string import_root` | import pipeline |
| DO-115-09 | `ServerPathRule` validation rule (FR-115-02) | user management requests |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-115-01 | REST GET /api/v2/Import | `directory` from `server_path`, `is_restricted`, `is_root_folder_album` | `ImportFromServerOptionsResource` |
| API-115-02 | REST GET /api/v2/Import::browse | containment for restricted users, links filtered | `ImportFromServerBrowseRequest` |
| API-115-03 | REST POST /api/v2/Import | containment, owner = requester, upload right on target, no symlink import when restricted | `ImportFromServerRequest` |
| API-115-04 | REST POST /api/v2/UserManagement, PATCH /api/v2/UserManagement | `server_path` field | `AddUserRequest`, `SetUserSettingsRequest`, `UserManagementResource` |

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
| TE-115-03 | import report warning `symlink_outside` | entry path |
| TE-115-04 | import report notice `already_visited` | entry path |
| TE-115-05 | log warning "import file left the import root" | owner id, file path |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-115-01 | temporary directories built in test `setUp` (alice root, sibling `alice2`, outside dir, inside/outside/loop links, copies of `tests/Samples` images) | containment and link scenarios; no committed files |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-115-01 | Import dialog at restricted import root | No `..` entry, no symlink toggle |
| UI-115-02 | User form `server_path` error | 422 message under the field |
| UI-115-03 | Import dialog with `import_root_folder_as_album = 0` | Hint under the target |

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
    name: config_category.import_from_server
    keys: [import_root_folder_as_album, import_scope_to_owner, sync_users_server_paths, delete_imported, import_via_symlink, skip_duplicates_early, sync_delete_missing_photos, sync_delete_missing_albums, sync_dry_run]
  - id: DO-115-07
    name: ImportFromServerOptionsResource
    fields: [is_restricted, is_root_folder_album]
  - id: DO-115-08
    name: ImportDTO/ImportImageJob
    fields: [upload_trust_level, import_root]
  - id: DO-115-09
    name: ServerPathRule
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
    event: import.symlink_outside
  - id: TE-115-04
    event: import.already_visited
  - id: TE-115-05
    event: import.file_left_root
fixtures:
  - id: FX-115-01
    path: tests (temporary directories)
ui_states:
  - id: UI-115-01
    description: Import dialog at restricted root, no "..", no symlink toggle
  - id: UI-115-02
    description: server_path validation error
  - id: UI-115-03
    description: root-folder-as-album hint
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
