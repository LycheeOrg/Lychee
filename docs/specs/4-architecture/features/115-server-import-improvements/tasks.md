# Feature 115 Tasks – Server Import Improvements

_Status: Draft_
_Last updated: 2026-11-15_

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
- [ ] T-115-03 – Migration test, red (FR-115-01, FR-115-14, S-115-38).
  _Intent:_ `tests/Unit/Database/ServerPathMigrationTest.php` loads the migration class and runs its owner grant on fixture users (owner, other admin, user; `owner_id = 0`).
  _Verification commands:_
  - `php artisan test --filter=ServerPathMigrationTest` (red)
- [ ] T-115-04 – Column, owner grant, model (FR-115-01, FR-115-14).
  _Intent:_ `database/migrations/2026_11_15_000001_add_server_path_to_users.php` (nullable string 4096, grant `/` to `owner_id` user; `down()` drops), `User` `@property ?string $server_path`.
  _Verification commands:_
  - `php artisan test --filter=ServerPathMigrationTest`
  - `make phpstan`

### I3 – Config category and settings
- [ ] T-115-05 – Settings test, red (FR-115-15, S-115-40).
  _Intent:_ `tests/Feature_v2/Settings/ImportFromServerSettingsTest.php`: the category `import` exists with its description and holds the fourteen keys (nine server, five URL) with the spec defaults; `skip_duplicates` stays in `Image Processing`.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerSettingsTest` (red)
- [ ] T-115-06 – Category migration and translations (FR-115-09, FR-115-12, FR-115-13, FR-115-15).
  _Intent:_ `database/migrations/2026_11_15_000002_create_import_category.php` (category `import` with its description and a free order between 6 and 10, three configs, six server keys moved from Image Processing and five `import_via_url_*` keys moved from Basics, with new orders; `down()` restores category and order: URL keys back to `config` orders 20–24). `lang/*/all_settings.php` (23 locales): category name and description, description and details for the three keys.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerSettingsTest`
  - `php artisan test --filter=ConfigTranslationKeysTest`
  - `php artisan test --filter=LangTest`

### I4 – User management API
- [ ] T-115-07 – User management tests, red (FR-115-02, S-115-19).
  _Intent:_ cases in `tests/Feature_v2/UserManagement/CreateUserTest.php`, `EditUserTest.php`, `ListUserTest.php`: relative path, `..` segment, missing directory → 422; trailing slash normalised; empty → `null`; absent on update keeps the value; list returns the field.
  _Verification commands:_
  - `php artisan test --filter=CreateUserTest` (red)
  - `php artisan test --filter=EditUserTest` (red)
- [ ] T-115-08 – `ServerPathRule`, requests, actions, resource (FR-115-02).
  _Intent:_ `app/Rules/ServerPathRule.php`, `RequestAttribute::SERVER_PATH_ATTRIBUTE`, `AddUserRequest`, `SetUserSettingsRequest`, `Actions\User\Create::do(..., ?string $server_path = null)`, `Actions\User\Save::do(...)`, `UserManagementController` (select list, pass-through), `UserManagementResource`.
  _Verification commands:_
  - `php artisan test --filter=CreateUserTest`
  - `php artisan test --filter=EditUserTest`
  - `php artisan test --filter=ListUserTest`
  - `make phpstan`

### I5 – First admin gets `/`
- [ ] T-115-09 – First-admin tests, red (FR-115-14, S-115-39).
  _Intent:_ cases in `tests/Install/AdminSetupTest.php` and `tests/Feature_v2/User/AdminSetupTest.php` (admin has `/`); new `tests/Unit/Console/Commands/CreateUserTest.php` (first user `/`, second user `null`).
  _Verification commands:_
  - `php artisan test --filter=AdminSetupTest` (red)
- [ ] T-115-10 – Pass `/` on the three first-admin paths (FR-115-14).
  _Intent:_ `SetUpAdminController::create`, `ProfileController::store`, `CreateUser::handle` (only when the table was empty).
  _Verification commands:_
  - `php artisan test --filter=AdminSetupTest`
  - `php artisan test --filter=CreateUserTest`

### I6 – Policy, options, browse
- [ ] T-115-11 – Options and browse tests, red (FR-115-03, FR-115-04, FR-115-05, NFR-115-01, S-115-01 … 08, S-115-20, S-115-21, S-115-22).
  _Intent:_ `ImportFromServerOptionsTest`, `ImportFromServerBrowseTest`: admin fixture gets `/` in `setUp`; new restricted user fixture (FX-115-01); identical 403 bodies for existing and missing outside paths; inside/outside links in browse.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerOptionsTest` (red)
  - `php artisan test --filter=ImportFromServerBrowseTest` (red)
- [ ] T-115-12 – Policy, options resource, browse containment (FR-115-03, FR-115-04, FR-115-05).
  _Intent:_ `AlbumPolicy::canImportFromServer`; `ImportFromServerOptionsResource` (`directory`, `is_restricted`, `is_root_folder_album`, `import_via_symlink`; 422 when the root is gone, TE-115-02); `ImportFromServerBrowseRequest` runs the guard (403, TE-115-01); controller hides `..` at the root and filters outside links.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerOptionsTest`
  - `php artisan test --filter=ImportFromServerBrowseTest`
  - `make phpstan`

### I7 – Import request and owner
- [ ] T-115-13 – Import request tests, red (FR-115-06, FR-115-07, S-115-09, S-115-10, S-115-11, S-115-28).
  _Intent:_ `ImportFromServerTest`: restricted import inside, mixed inside/outside, album without upload right, `import_via_symlink = true` refused, owner of created albums and photos.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerTest` (red)
- [ ] T-115-14 – Request guard, owner, upload right (FR-115-06, FR-115-07).
  _Intent:_ `ImportFromServerRequest` (guard per directory, `CAN_UPLOAD` on the album, symlink refusal when restricted); `ImportFromServerController::get_exec` (owner `Auth::id()`, import root to `Exec`).
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerTest`
  - `make phpstan`

### I8 – Trust level and job re-check
- [ ] T-115-15 – Trust level and job tests, red (FR-115-08, FR-115-18, S-115-12, S-115-13, S-115-27).
  _Intent:_ `tests/Unit/Jobs/ImportImageJobTest.php` (payload carries trust level and root; swapped file → `FAILURE`, no photo, source kept); `SyncTest` (`check` owner, admin owner); `ImportFromServerTest` (`check` user via web).
  _Verification commands:_
  - `php artisan test --filter=ImportImageJobTest` (red)
  - `php artisan test --filter=SyncTest` (red)
- [ ] T-115-16 – Owner rule in `Exec`, `ImportDTO`, `ImportImageJob` (FR-115-08, FR-115-18, NFR-115-04).
  _Intent:_ `Exec` loads the owner once, derives the level (admin → `trusted`); `ImportDTO` and `ImportImageJob` take `upload_trust_level` and `?string $import_root`; job re-checks with `ImportPathGuard` (TE-115-05); `doFiles` passes both. No `UserUploadTrustLevel::TRUSTED` left in either class.
  _Verification commands:_
  - `php artisan test --filter=ImportImageJobTest`
  - `php artisan test --filter=SyncTest`
  - `php artisan test --filter=ImportFromServerTest`
  - `make phpstan`

### I9 – Links and loop guard
- [ ] T-115-17 – Link tests, red (FR-115-11, S-115-21 … 26).
  _Intent:_ fixtures with inside link, outside directory link, outside file link, loop, two links to one folder; restricted web import and explicit CLI path.
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerTest` (red)
  - `php artisan test --filter=SyncTest` (red)
- [ ] T-115-18 – `BuildTree` containment and visited set (FR-115-11, NFR-115-07).
  _Intent:_ `BuildTree` reads `ImportDTO::$import_root`, one `realpath()` per entry, `symlink_outside` / `already_visited` report events (TE-115-03/04).
  _Verification commands:_
  - `php artisan test --filter=ImportFromServerTest`
  - `php artisan test --filter=SyncTest`
  - `make phpstan`

### I10 – Folder content without its album
- [ ] T-115-19 – Root folder tests, red (FR-115-09, FR-115-10, S-115-14, S-115-15, S-115-32, S-115-33).
  _Intent:_ `SyncTest`: setting on (today), off into album `T`, off into gallery root (Unsorted), re-sync with both delete-missing flags in both targets.
  _Verification commands:_
  - `php artisan test --filter=SyncTest` (red)
- [ ] T-115-20 – Root node mapped to the target (FR-115-09, FR-115-10).
  _Intent:_ `ImportDTO::$root_folder_as_album`; `CreateNonExistingAlbums` sets the root node's album to the target; `ImportPhotos` sends root images there or to Unsorted; delete-missing pipes skip a node with no album (already) and never select photos with no album.
  _Verification commands:_
  - `php artisan test --filter=SyncTest`
  - `php artisan test --filter=ImportFromServerTest`

### I11 – Owner scope
- [ ] T-115-21 – Owner scope tests, red (FR-115-12, S-115-29, S-115-30, S-115-31).
  _Intent:_ `SyncTest`: same-title album of another owner, delete-missing in a shared album, setting off.
  _Verification commands:_
  - `php artisan test --filter=SyncTest` (red)
- [ ] T-115-22 – Owner condition in the three pipes (FR-115-12).
  _Intent:_ `ImportDTO::$scope_to_owner`; `owner_id` condition in `CreateNonExistingAlbums::findOrCreateAlbum`, `DeleteMissingAlbums::findMissingAlbums`, `DeleteMissingPhotos::findMissingPhotos`.
  _Verification commands:_
  - `php artisan test --filter=SyncTest`
  - `make phpstan`

### I12 – `lychee:sync` with no path
- [ ] T-115-23 – No-path sync tests, red (FR-115-13, S-115-16, S-115-17, S-115-18, S-115-34 … 37).
  _Intent:_ `SyncTest`: config off, two users, missing path, `/` user skipped, explicit `--owner_id`, `--album_id` refused, user without upload rights.
  _Verification commands:_
  - `php artisan test --filter=SyncTest` (red)
- [ ] T-115-24 – Optional paths, runtime owner default, per-user loop (FR-115-13).
  _Intent:_ `Sync` signature `{paths?*}`, `--owner_id` without default; helpers for argument mode and user eligibility returning enums; one `Exec` per user with its import root; summary lines; exit codes.
  _Verification commands:_
  - `php artisan test --filter=SyncTest`
  - `make phpstan`

### I13 – Types and v8 UI
- [ ] T-115-25 – Generated types, service, UI translations (FR-115-16, FR-115-17).
  _Intent:_ `php artisan typescript:transform`; `user-management-service.ts` `server_path`; `lang/*/users.php` (field label, hint) and `lang/*/import_from_server.php` (root-folder hint) in 23 locales.
  _Verification commands:_
  - `php artisan test --filter=LangTest`
  - `vp run typecheck`
- [ ] T-115-26 – v8 user form and import dialog (FR-115-16, FR-115-17, S-115-41).
  _Intent:_ `resources/js/v8/components/forms/users/CreateEditUser.vue` field + hint + 422 message; `resources/js/v8/components/modals/ImportFromServer.vue` hides the symlink toggle when `is_restricted`, sends `false`, shows the hint when `is_root_folder_album` is `false`.
  _Verification commands:_
  - `vp check --fix`
  - `vp run typecheck`
  _Notes:_ manual check S-115-41 (v8) by the operator.

### I14 – v7 UI
- [ ] T-115-27 – v7 user form (FR-115-16).
  _Intent:_ `resources/js/v7/components/forms/users/CreateEditUser.vue` field + hint + 422 message.
  _Verification commands:_
  - `vp check --fix`
  - `vp run typecheck`
- [ ] T-115-28 – v7 import dialog (FR-115-17, S-115-41).
  _Intent:_ `resources/js/v7/components/modals/ImportFromServer.vue`, same behaviour as T-115-26.
  _Verification commands:_
  - `vp check --fix`
  - `vp run typecheck`
  _Notes:_ manual check S-115-41 (v7) by the operator.

### I15 – Docs
- [ ] T-115-29 – How-to (Documentation Deliverables).
  _Intent:_ `docs/specs/2-how-to/import-from-server.md`: per-user folders, `/` vs restricted, links, settings of the new category, cron line for `lychee:sync` with no path.
- [ ] T-115-30 – Knowledge map and roadmap.
  _Intent:_ `knowledge-map.md` (import access per user, `ImportPathGuard`, owner scope, `lychee:sync [paths*]`), roadmap row #115 progress.

### I16 – Gates
- [ ] T-115-31 – Quality gate.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `vp check --fix`
  - `vp run typecheck`
  - `php artisan test --filter=<each class above>` (one at a time)
  - `make phpstan`
- [ ] T-115-32 – Drift gate report and commit preparation.
  _Intent:_ FR/NFR → class/test table in plan.md, divergences logged, `./scripts/codex-commit-review.sh` and copy/paste-ready `git commit` for the operator.

## Notes / TODOs
- Test fixtures create symbolic links in temp directories; compare real paths (macOS `/private/var`).
- Never run two test commands at once (shared SQLite database).
