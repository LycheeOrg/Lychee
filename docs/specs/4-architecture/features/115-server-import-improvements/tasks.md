# Feature 115 Tasks – Server Import Improvements

_Status: Implemented_
_Last updated: 2026-11-16_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Path guard
- [x] T-115-01 – Guard tests, red (FR-115-05, FR-115-11, FR-115-18, NFR-115-02, NFR-115-03).
  _Intent:_ `tests/Unit/Actions/Import/ImportPathGuardTest.php`: inside, root itself, outside, `alice2` prefix trap, lexical `..`, missing inside, link inside, link outside, `null` root. Roots built with `realpath()` of a temp dir.
  _Verification commands:_
  - `php artisan test --filter=ImportPathGuardTest` (red)
- [x] T-115-02 – `ImportPathGuard` and `ImportPathStatus` (FR-115-05, NFR-115-03).
  _Intent:_ `app/Actions/Import/ImportPathGuard.php` (`normalise()`, `check()`), `app/Enum/ImportPathStatus.php`. Pure, no logging.
  _Verification commands:_
  - `php artisan test --filter=ImportPathGuardTest`
  - `make phpstan`

### I2 – Schema and owner grant
- [x] T-115-03 – Migration test, red (FR-115-01, FR-115-14, S-115-38).
  _Intent:_ `tests/Unit/Database/ServerPathMigrationTest.php` loads the migration class and runs its owner grant on fixture users (owner, other admin, user; `owner_id = 0`).
  _Verification commands:_
  - `php artisan test --filter=ServerPathMigrationTest` (red)
- [x] T-115-04 – Column, owner grant, model (FR-115-01, FR-115-14).
  _Intent:_ `database/migrations/2026_11_15_000001_add_server_path_to_users.php` (nullable string 4096, grant `/` to `owner_id` user; `down()` drops), `User` `@property ?string $server_path`.
  _Verification commands:_
  - `php artisan test --filter=ServerPathMigrationTest`
  - `make phpstan`

### I3 – Config category and settings
- [x] T-115-05 – Settings test, red (FR-115-15, S-115-40).
  _Intent:_ `tests/Feature_v2/Settings/ImportSettingsTest.php`: the category `import` exists with its description and holds the fourteen keys (nine server, five URL) with the spec defaults; `skip_duplicates` stays in `Image Processing`.
  _Verification commands:_
  - `php artisan test --filter=ImportSettingsTest` (red)
- [x] T-115-06 – Category migration and translations (FR-115-09, FR-115-12, FR-115-13, FR-115-15).
  _Intent:_ `database/migrations/2026_11_15_000002_create_import_category.php` (category `import` with its description and a free order between 6 and 10, three configs, six server keys moved from Image Processing and five `import_via_url_*` keys moved from Admin, with new orders; `down()` restores category and order: URL keys back to `Admin` orders 20–24). `lang/*/all_settings.php` (23 locales): category name and description, description and details for the three keys.
  _Verification commands:_
  - `php artisan test --filter=ImportSettingsTest`
  - `php artisan test --filter=ConfigTranslationKeysTest`
  - `php artisan test --filter=LangTest`

### I4 – User management API
- [x] T-115-07 – User management tests, red (FR-115-02, S-115-19).
  _Intent:_ `tests/Feature_v2/UserManagement/ServerPathTest.php` (create, edit, list): relative path, `..` segment, missing directory → 422; trailing slash normalised; empty → `null`; absent on update keeps the value (absent = `null` for the owner since T-115-37); list returns the field.
  _Verification commands:_
  - `php artisan test --filter='Feature_v2\\UserManagement\\ServerPathTest'` (red)
- [x] T-115-08 – `ServerPathRule`, requests, actions, resource (FR-115-02).
  _Intent:_ `app/Rules/ServerPathRule.php`, `RequestAttribute::SERVER_PATH_ATTRIBUTE`, `AddUserRequest`, `SetUserSettingsRequest`, `Actions\User\Create::do(..., ?string $server_path = null)`, `Actions\User\Save::do(...)`, `UserManagementController` (select list, pass-through), `UserManagementResource`.
  _Verification commands:_
  - `php artisan test --filter=CreateUserTest`
  - `php artisan test --filter=EditUserTest`
  - `php artisan test --filter=ListUserTest`
  - `make phpstan`

### I5 – First admin gets `/`
- [x] T-115-09 – First-admin tests, red (FR-115-14, S-115-39).
  _Intent:_ cases in `tests/Feature_v2/User/AdminSetupTest.php` (admin has `/`); `tests/Install/AdminTest.php` (v7 installer) and new `tests/Unit/Console/Commands/CreateUserCommandTest.php` (first user `/`, second user `null`).
  _Verification commands:_
  - `php artisan test --filter=AdminSetupTest` (red)
- [x] T-115-10 – Pass `/` on the three first-admin paths (FR-115-14).
  _Intent:_ `SetUpAdminController::create`, `ProfileController::store`, `CreateUser::handle` (only when the table was empty).
  _Verification commands:_
  - `php artisan test --filter=AdminSetupTest`
  - `php artisan test --filter='Install\\AdminTest'`
  - `php artisan test --filter=CreateUserCommandTest`

### I6 – Policy, options, browse
- [x] T-115-11 – Options and browse tests, red (FR-115-03, FR-115-04, FR-115-05, NFR-115-01, S-115-01 … 08, S-115-20, S-115-21, S-115-22).
  _Intent:_ `ImportFromServerOptionsTest`, `ImportFromServerBrowseTest`: admin fixture gets `/` in `setUp`; restricted user and temp tree from `tests/Traits/CreatesImportTree.php` (FX-115-01); identical 403 bodies for existing and missing outside paths; inside/outside links in browse.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerOptionsTest` (red)
  - `php artisan test --filter=ImportFromServerBrowseTest` (red)
- [x] T-115-12 – Policy, options resource, browse containment (FR-115-03, FR-115-04, FR-115-05).
  _Intent:_ `AlbumPolicy::canImportFromServer`; `ImportFromServerOptionsResource` (`directory`, `is_restricted`, `is_root_folder_album`, `import_via_symlink`; 422 when the root is gone, TE-115-02); `ImportFromServerBrowseRequest` runs the guard (403, TE-115-01); controller hides `..` at the root and filters outside links.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerOptionsTest`
  - `php artisan test --filter=ImportFromServerBrowseTest`
  - `make phpstan`

### I7 – Import request and owner
- [x] T-115-13 – Import request tests, red (FR-115-06, FR-115-07, S-115-09, S-115-10, S-115-11, S-115-28).
  _Intent:_ `ImportFromServerTest`: restricted import inside, mixed inside/outside, album without upload right, `import_via_symlink = true` refused, owner of created albums and photos.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerTest` (red)
- [x] T-115-14 – Request guard, owner, upload right (FR-115-06, FR-115-07).
  _Intent:_ `ImportFromServerRequest` (guard per directory, `CAN_UPLOAD` on the album, symlink refusal when restricted); `ImportFromServerController::get_exec` (owner `Auth::id()`, import root to `Exec`).
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerTest`
  - `make phpstan`

### I8 – Trust level and job re-check
- [x] T-115-15 – Trust level and job tests, red (FR-115-08, FR-115-18, S-115-12, S-115-13, S-115-27).
  _Intent:_ `tests/Unit/Jobs/ImportImageJobTest.php` (payload carries trust level and root; swapped file → `FAILURE`, no photo, source kept); `SyncTest` (`check` owner, admin owner); `ImportFromServerTest` (`check` user via web).
  _Verification commands:_
  - `php artisan test --filter=ImportImageJobTest` (red)
  - `php artisan test --filter=SyncTest` (red)
- [x] T-115-16 – Owner rule in `Exec`, `ImportDTO`, `ImportImageJob` (FR-115-08, FR-115-18, NFR-115-04).
  _Intent:_ `Exec` loads the owner once, derives the level (admin → `trusted`); `ImportDTO` and `ImportImageJob` take `upload_trust_level` and `?string $import_root`; job re-checks with `ImportPathGuard` (TE-115-05); `doFiles` passes both. No `UserUploadTrustLevel::TRUSTED` left in either class.
  _Verification commands:_
  - `php artisan test --filter=ImportImageJobTest`
  - `php artisan test --filter=SyncTest`
  - `php artisan test --filter=ImportFromServerTest`
  - `make phpstan`

### I9 – Links and loop guard
- [x] T-115-17 – Link tests, red (FR-115-11, S-115-21 … 26).
  _Intent:_ fixtures with inside link, outside directory link, outside file link, loop, two links to one folder; restricted web import and explicit CLI path.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerTest` (red)
  - `php artisan test --filter=SyncTest` (red)
- [x] T-115-18 – `BuildTree` containment and visited set (FR-115-11, NFR-115-07).
  _Intent:_ `BuildTree` reads `ImportDTO::$import_root`, one `realpath()` per entry, `symlink_outside` / `already_visited` report events (TE-115-03/04).
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerTest`
  - `php artisan test --filter=SyncTest`
  - `make phpstan`

### I10 – Folder content without its album
- [x] T-115-19 – Root folder tests, red (FR-115-09, FR-115-10, S-115-14, S-115-15, S-115-32, S-115-33).
  _Intent:_ `SyncTest`: setting on (today), off into album `T`, off into gallery root (Unsorted), re-sync with both delete-missing flags in both targets.
  _Verification commands:_
  - `php artisan test --filter=SyncTest` (red)
- [x] T-115-20 – Root node mapped to the target (FR-115-09, FR-115-10).
  _Intent:_ `ImportDTO::$root_folder_as_album`; `CreateNonExistingAlbums` sets the root node's album to the target; `ImportPhotos` sends root images there or to Unsorted; delete-missing pipes skip a node with no album (already) and never select photos with no album.
  _Verification commands:_
  - `php artisan test --filter=SyncTest`
  - `php artisan test --filter=ImportFromServerTest`

### I11 – Owner scope
- [x] T-115-21 – Owner scope tests, red (FR-115-12, S-115-29, S-115-30, S-115-31).
  _Intent:_ `SyncTest`: same-title album of another owner, delete-missing in a shared album, setting off.
  _Verification commands:_
  - `php artisan test --filter=SyncTest` (red)
- [x] T-115-22 – Owner condition in the three pipes (FR-115-12).
  _Intent:_ `ImportDTO::$scope_to_owner`; `owner_id` condition in `CreateNonExistingAlbums::findOrCreateAlbum`, `DeleteMissingAlbums::findMissingAlbums`, `DeleteMissingPhotos::findMissingPhotos`.
  _Verification commands:_
  - `php artisan test --filter=SyncTest`
  - `make phpstan`

### I12 – `lychee:sync` with no path
- [x] T-115-23 – No-path sync tests, red (FR-115-13, S-115-16, S-115-17, S-115-18, S-115-34 … 37).
  _Intent:_ `SyncTest`: config off, two users, missing path, `/` user skipped, explicit `--owner_id`, `--album_id` refused, user without upload rights.
  _Verification commands:_
  - `php artisan test --filter=SyncTest` (red)
- [x] T-115-24 – Optional paths, runtime owner default, per-user loop (FR-115-13).
  _Intent:_ `Sync` signature `{paths?*}`, `--owner_id` without default; helpers for argument mode and user eligibility returning enums; one `Exec` per user with its import root; summary lines; exit codes.
  _Verification commands:_
  - `php artisan test --filter=SyncTest`
  - `make phpstan`

### I13 – Types and v8 UI
- [x] T-115-25 – Generated types, service, UI translations (FR-115-16, FR-115-17).
  _Intent:_ `php artisan typescript:transform`; `user-management-service.ts` `server_path`; `lang/*/users.php` (field label, hint) and `lang/*/import_from_server.php` (root-folder hint) in 23 locales.
  _Verification commands:_
  - `php artisan test --filter=LangTest`
  - `vp run typecheck`
- [x] T-115-26 – v8 user form and import dialog (FR-115-16, FR-115-17, S-115-41).
  _Intent:_ `resources/js/v8/components/forms/users/CreateEditUser.vue` field + hint + 422 message; `resources/js/v8/components/modals/ImportFromServer.vue` hides the symlink toggle when `is_restricted`, sends `false`, shows the hint when `is_root_folder_album` is `false`.
  _Verification commands:_
  - `vp check --fix`
  - `vp run typecheck`
  _Notes:_ manual check S-115-41 (v8) by the operator.

### I14 – v7 UI
- [x] T-115-27 – v7 user form (FR-115-16).
  _Intent:_ `resources/js/v7/components/forms/users/CreateEditUser.vue` field + hint + 422 message.
  _Verification commands:_
  - `vp check --fix`
  - `vp run typecheck`
- [x] T-115-28 – v7 import dialog (FR-115-17, S-115-41).
  _Intent:_ `resources/js/v7/components/modals/ImportFromServer.vue`, same behaviour as T-115-26.
  _Verification commands:_
  - `vp check --fix`
  - `vp run typecheck`
  _Notes:_ manual check S-115-41 (v7) by the operator.

### I15 – Docs
- [x] T-115-29 – How-to (Documentation Deliverables).
  _Intent:_ `docs/specs/2-how-to/import-from-server.md`: per-user folders, `/` vs restricted, links, settings of the new category, cron line for `lychee:sync` with no path.
- [x] T-115-30 – Knowledge map and roadmap.
  _Intent:_ `knowledge-map.md` (import access per user, `ImportPathGuard`, owner scope, `lychee:sync [paths*]`), roadmap row #115 progress.

### I16 – Gates
- [x] T-115-31 – Quality gate.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `vp check --fix`
  - `vp run typecheck`
  - `php artisan test --filter=<each class above>` (one at a time)
  - `make phpstan`
- [x] T-115-32 – Drift gate report and commit preparation.
  _Intent:_ FR/NFR → class/test table in plan.md, divergences logged, `./scripts/codex-commit-review.sh` and copy/paste-ready `git commit` for the operator.

### I17 – `server_path` for the server owner only (Q-115-07)
- [x] T-115-33 – Tests for S-115-42 (FR-115-19), red first.
  _Intent:_ `tests/Feature_v2/UserManagement/ServerPathTest.php`: a second admin who is not `owner_id` creates and edits with `server_path` = a path, `/`, `''`, `null` → 403, nothing saved; same requests without the key → saved, path unchanged; `GET /api/v2/Auth::rights` gives `can_edit_server_path` false for them, true for the owner.
  _Verification commands:_
  - `php artisan test --filter=ServerPathTest`
- [x] T-115-34 – Policy, requests and rights (FR-115-19).
  _Intent:_ `UserPolicy::CAN_EDIT_SERVER_PATH` + `canEditServerPath()` + `before()` override; `AddUserRequest::authorize()` and `SetUserSettingsRequest::authorize()` check it when the key is present; `UserManagementRightsResource::$can_edit_server_path`; `php artisan typescript:transform`.
  _Verification commands:_
  - `php artisan test --filter=ServerPathTest`
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`
- [x] T-115-35 – v7/v8 user forms (FR-115-16, UI-115-04).
  _Intent:_ `resources/js/v7/components/forms/users/CreateEditUser.vue` and `resources/js/v8/components/forms/users/CreateEditUser.vue`: field shown and `server_path` sent only when `rights.user_management.can_edit_server_path`.
  _Verification commands:_
  - `vp check --fix` on the two files
  - `vp run typecheck`
  _Notes:_ manual check of the form as owner and as another admin by the operator.
- [x] T-115-36 – Non-owner `server_path` ignored instead of 403 (Q-115-07 → B, FR-115-19).
  _Intent:_ `ServerPathTest` non-owner create/edit cases expect 201/204 with the value ignored (red first), then `AddUserRequest` and `SetUserSettingsRequest` drop the `authorize()` check and gate the value in `processValidatedValues()`.
  _Verification commands:_
  - `php artisan test --filter=ServerPathTest`
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`
- [x] T-115-37 – `server_path` always considered sent (FR-115-02/16/19).
  _Intent:_ `ServerPathTest::testEditSetsAndClears` (absent = `null` for the owner, red first). `SetUserSettingsRequest` drops `has_server_path`/`hasServerPath()` and resolves the stored value for non-owners. `Save` drops `update_server_path` and always writes. v7/v8 forms always send `server_path` (`null` for non-owners), `UserManagementCreateRequest.server_path` required. `UserPolicy::before()` owner check made null-safe for guests.
  _Verification commands:_
  - `php artisan test --filter='Tests\\Feature_v2\\UserManagement'`
  - `vp check --fix` on the two forms and the service
  - `vp run typecheck`
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`

### I18 – Other users' duplicates in a scoped import (Q-115-09)
- [ ] T-115-38 – Tests for S-115-43 (FR-115-12), red first.
  _Intent:_ `tests/ImageProcessing/Jobs/SkippedDuplicateJobTest.php`: `import_scope_to_owner = 1`, `skip_duplicates_not_owned = 0`, a photo owned by another user with the same checksum. `skip_duplicates` + `resync_metadata` → job skipped, other user's photo metadata unchanged; `skip_duplicates` off → skipped, other user's photo not in the target album; scope off → added to the target album as today.
  _Verification commands:_
  - `php artisan test --filter=SkippedDuplicateJobTest` (red)
- [ ] T-115-39 – Owner scope in duplicate handling (FR-115-12, DO-115-05).
  _Intent:_ `ImportMode` gains the scope flag (default `false`); `ImportFromServerController` and `Sync` (both `new ImportMode(...)`) set it from `import_scope_to_owner`; `DuplicateDTO::ofInit()` copies it; `ThrowUnownedDuplicate` rejects when `skip_duplicates_not_owned` or the flag is on.
  _Verification commands:_
  - `php artisan test --filter=SkippedDuplicateJobTest`
  - `php artisan test --filter=SyncTest`
  - `php artisan test --filter=ImportFromServerTest`
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`

### I19 – Symbolic link import for restricted users (Q-115-10)
- [x] T-115-40 – Restricted users may import via symbolic link (FR-115-04/06/13, S-115-05/28/41).
  _Intent:_ tests flipped first (`ImportFromServerOptionsTest::testRestrictedOptions`, `ImportFromServerTest::testRestrictedSymlinkImport`, new `SyncTest::testNoPathKeepsSymlinkOption`). `ImportFromServerRequest` without `declined`, `ImportFromServerOptionsResource` sends the config value, `Sync` passes the command's `ImportMode` to every user, v7/v8 `ImportFromServer.vue` show the toggle. ADR-115-01 point 5, how-to, ADR index updated.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerOptionsTest`
  - `php artisan test --filter=ImportFromServerTest`
  - `php artisan test --filter=SyncTest`
  - `vp check --fix` on the two dialogs
  - `vp run typecheck`
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`

### I20 – Links never followed (Q-115-11)
- [x] T-115-41 – Browse and `BuildTree` skip every symbolic link (FR-115-05/11, NFR-115-05/07, S-115-21 … 26, TE-115-03/04).
  _Intent:_ tests flipped first (`ImportFromServerBrowseTest::testFullAccessSeesNoLinks`, `testRestrictedAtRoot`, `ImportFromServerTest::testRestrictedImportSkipsAllLinks`, `SyncTest::testExplicitPathSkipsLinks`). `BuildTree` drops `followedRealPath()`, `$import_root`, `$visited`, skips `is_link()` entries with `symlink_skipped`. `ImportFromServerController::browse` drops `isListable()`, lists no link. ADR-115-01 point 3, how-to, knowledge map, ADR index updated.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerBrowseTest` (2 local-only failures: stray `tests/Frontend`, `APP_DEBUG=true`)
  - `php artisan test --filter=ImportFromServerTest`
  - `php artisan test --filter=SyncTest`
  - `php artisan test --filter=ImportPathGuardTest`
  - `php artisan test --filter=ImportImageJobTest`
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`

- [x] T-115-42 – Refuse a named path that is itself a link (FR-115-05/06/11, S-115-21/26).
  _Intent:_ tests first (`ImportPathGuardTest::testIsLink`, `ImportFromServerBrowseTest::testNamedLinkRefused`, `ImportFromServerTest::testImportOfNamedLinkRefused`, `SyncTest::testExplicitLinkPathRefused`). `ImportPathGuard::isLink()`, checked in `ImportFromServerController::browse()` and `__invoke()` next to `is_dir()`, and in `Sync::validatePaths()` before `realpath()`.
  _Verification commands:_
  - `php artisan test --filter=ImportPathGuardTest`
  - `php artisan test --filter=ImportFromServerBrowseTest` (2 local-only failures: stray `tests/Frontend`, `APP_DEBUG=true`)
  - `php artisan test --filter=ImportFromServerTest`
  - `php artisan test --filter=SyncTest`
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`

## Notes / TODOs
- Test fixtures create symbolic links in temp directories; compare real paths (macOS `/private/var`).
- Never run two test commands at once (shared SQLite database).
