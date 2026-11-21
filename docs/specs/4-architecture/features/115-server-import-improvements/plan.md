# Feature Plan 115 – Server Import Improvements

_Linked specification:_ [spec.md](spec.md)
_Linked tasks:_ [tasks.md](tasks.md)
_Status:_ Implemented (analysis and drift gates passed)
_Last updated:_ 2026-11-16

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Any user an admin chooses can import from their own server folder, and nothing else: no listing, probing, importing or serving outside it, links included (ADR-115-01). Imports respect ownership (owner scope, trust level). Two settings cover the requested workflows: folder content without the folder album, and a one-line cron sync for every user.

Success signals:
- S-115-01 … 41 covered by green tests, except S-115-41 (manual, v7/v8 dialog).
- `make phpstan`, `php-cs-fixer`, `vp check`, `vp run typecheck` clean.
- `ConfigTranslationKeysTest` and `LangTest` green (every new key present in the 23 locales).

## Scope Alignment
- **In scope:** FR-115-01 … 18, NFR-115-01 … 07, ADR-115-01, how-to, knowledge map.
- **Out of scope:** spec Non-Goals (user self-service path, `create_user`/`update_user` path options, trust level picker, per-user sync target album, URL import, regular uploads).

## Dependencies & Interfaces
- Feature 024 (`Exec::doFiles`, file paths in `lychee:sync`), Feature 033 (trust level rule, `ProcessImageJob`), Feature 045 (NSFW auto-scan per trust level), Feature 051 (v8 admin setup).
- `BaseConfigMigration` / `config_categories`, `ConfigTranslationKeysTest`, `LangTest`.
- `php artisan typescript:transform` for `UserManagementResource`, `ImportFromServerOptionsResource`.
- No new library.

## Assumptions & Risks
- **Assumptions:**
  - The test runner can create symbolic links in a temporary directory (Linux CI, local Linux).
  - The test SQLite database starts with no user, so the migration's owner grant is tested by calling it on fixture users (T-115-04).
- **Risks / Mitigations:**
  - _Existing import tests assume the `owner_id` admin with no column._ Mitigation: test base gives the admin `/` in `setUp` (T-115-11).
  - _`realpath()` on macOS temp dirs (`/var` → `/private/var`)._ Mitigation: fixtures always compare real paths; guard tests build roots through `realpath()`.
  - _Config category order collision._ Mitigation: pick a free order between 6 (Image Processing) and 10 (Search) after reading `config_categories` in the migration test.
  - _Changing `lychee:sync` `--owner_id` default breaks scripts relying on `--help` text only._ Accepted (Q-115-06 A cons).
  - _Translations in 23 locales slow down each increment._ Mitigation: dedicated tasks per key set; English first, then the 22 others in the same task.

## Implementation Drift Gate
Run once T-115-01 … 32 are `[x]`: map each FR/NFR to the classes and tests that cover it (table appended below), rerun the scoped test classes listed in the tasks plus the quality gate, record divergences in open-questions.md. Report goes in this file under "Drift Gate Report".

## Increment Map

1. **I1 – Path guard** (FR-115-05/06/11/18, NFR-115-02/03)
   - _Goal:_ `App\Actions\Import\ImportPathGuard` + enum `App\Enum\ImportPathStatus` (`INSIDE`, `OUTSIDE`, `MISSING`). Pure: `normalise(string): string` (lexical, no disk), `check(string $path, ?string $root): ImportPathStatus` (lexical test, then real-path test with trailing separator; `null` root → `INSIDE` if it exists, else `MISSING`).
   - _Steps:_ `tests/Unit/Actions/Import/ImportPathGuardTest.php` first (inside, root itself, outside, `alice2` trap, `..`, missing, link inside, link outside, null root), seen red; then the helper.
   - _Commands:_ `php artisan test --filter=ImportPathGuardTest`, `make phpstan`.
   - _Exit:_ all guard cases green.
2. **I2 – Schema and owner grant** (FR-115-01, FR-115-14 upgrade half)
   - _Goal:_ migration `add_server_path_to_users` (column, owner gets `/`), `User` `@property ?string $server_path`.
   - _Steps:_ test (calls the migration's grant on fixture users: owner → `/`, other admin and user → `null`, `owner_id = 0` → no-op) red; migration; model.
   - _Commands:_ `php artisan test --filter=ServerPathMigrationTest`.
   - _Exit:_ S-115-38 green.
3. **I3 – Config category and settings** (FR-115-09/12/13/15 config rows)
   - _Goal:_ migration `create_import_category`: category `import` with its description, configs `import_root_folder_as_album` (1), `import_scope_to_owner` (1), `sync_users_server_paths` (0), six server keys moved from Image Processing and five `import_via_url_*` keys moved from Admin; `down()` restores both. `all_settings.php` entries (category name/description, 3 × description/details) in 23 locales.
   - _Steps:_ settings test for S-115-40 red; migration; translations.
   - _Commands:_ `php artisan test --filter=ImportSettingsTest`, `--filter=ConfigTranslationKeysTest`, `--filter=LangTest`.
   - _Exit:_ S-115-40 green, translation tests green.
4. **I4 – User management API** (FR-115-02)
   - _Goal:_ `App\Rules\ServerPathRule` (normalises, validates FR-115-02), `server_path` in `AddUserRequest` / `SetUserSettingsRequest`, `Actions\User\Create::do(..., ?string $server_path = null)`, `Save::do(...)`, controller list select, `UserManagementResource`.
   - _Steps:_ `CreateUserTest`, `EditUserTest`, `ListUserTest` cases for S-115-19 red; implementation.
   - _Commands:_ `php artisan test --filter=CreateUserTest`, `--filter=EditUserTest`, `--filter=ListUserTest`, `make phpstan`.
   - _Exit:_ S-115-19 green.
5. **I5 – First admin gets `/`** (FR-115-14 first-admin half)
   - _Goal:_ `SetUpAdminController::create`, `ProfileController::store`, `lychee:create_user` (empty table) pass `server_path: '/'`.
   - _Steps:_ cases in `tests/Install/AdminSetupTest.php`, `tests/Feature_v2/User/AdminSetupTest.php`, new `tests/Unit/Console/Commands/CreateUserTest.php` red; implementation.
   - _Commands:_ `php artisan test --filter=AdminSetupTest`, `--filter=CreateUserTest`.
   - _Exit:_ S-115-39 green.
6. **I6 – Policy, options, browse** (FR-115-03/04/05, NFR-115-01)
   - _Goal:_ `canImportFromServer` new rule; `ImportFromServerOptionsResource` (`directory`, `is_restricted`, `is_root_folder_album`, `import_via_symlink` false when restricted (dropped by Q-115-10), 422 when the root is gone); browse request runs the guard before the controller touches the disk; links filtered; no `..` at the root; TE-115-01/02.
   - _Steps:_ `ImportFromServerOptionsTest`, `ImportFromServerBrowseTest` cases S-115-01 … 08, 20, 21/22 (browse half) red; existing cases updated (admin gets `/`); implementation.
   - _Commands:_ `php artisan test --filter=ImportFromServerOptionsTest`, `--filter=ImportFromServerBrowseTest`, `make phpstan`.
   - _Exit:_ listed scenarios green.
7. **I7 – Import request and owner** (FR-115-06/07)
   - _Goal:_ guard on every directory, `import_via_symlink` refused when restricted (dropped by Q-115-10), owner = `Auth::id()`, `CAN_UPLOAD` on `album_id`, `Exec` receives the import root.
   - _Steps:_ `ImportFromServerTest` cases S-115-09/10/11/28 red; implementation.
   - _Commands:_ `php artisan test --filter=ImportFromServerTest`, `make phpstan`.
   - _Exit:_ listed scenarios green.
8. **I8 – Trust level and job re-check** (FR-115-08/18, NFR-115-04/06)
   - _Goal:_ `Exec` resolves the owner once and the trust level with the `ProcessImageJob` rule; `ImportDTO` and `ImportImageJob` carry `upload_trust_level` and `?string import_root`; job re-checks with the guard; `doFiles` same.
   - _Steps:_ `ImportImageJobTest` (S-115-27, payload), `SyncTest` (S-115-12/13), `ImportFromServerTest` (S-115-12 web half) red; implementation.
   - _Commands:_ `php artisan test --filter=ImportImageJobTest`, `--filter=SyncTest`, `--filter=ImportFromServerTest`.
   - _Exit:_ listed scenarios green; no `UserUploadTrustLevel::TRUSTED` left in `ImportDTO`/`ImportImageJob`.
9. **I9 – Links and loop guard in `BuildTree`** (FR-115-11, NFR-115-07)
   - _Goal:_ `BuildTree` gets the import root from `ImportDTO`, resolves each entry once, skips outside links (`symlink_outside`), keeps a visited set (`already_visited`).
   - _Steps:_ cases S-115-21 … 26 (import half) in `ImportFromServerTest` / `SyncTest` red; implementation.
   - _Commands:_ `php artisan test --filter=ImportFromServerTest`, `--filter=SyncTest`.
   - _Exit:_ listed scenarios green.
10. **I10 – Folder content without its album** (FR-115-09/10)
    - _Goal:_ `ImportDTO::$root_folder_as_album` from config; `CreateNonExistingAlbums` maps the root node to the target; `ImportPhotos` sends root images to the target or Unsorted; delete-missing pipes skip the root node at the gallery root and never touch photos with no album.
    - _Steps:_ `SyncTest` cases S-115-14/15/32/33 red; implementation.
    - _Commands:_ `php artisan test --filter=SyncTest`, `--filter=ImportFromServerTest`.
    - _Exit:_ listed scenarios green.
11. **I11 – Owner scope** (FR-115-12)
    - _Goal:_ `ImportDTO::$scope_to_owner` from config; owner condition in `CreateNonExistingAlbums::findOrCreateAlbum`, `DeleteMissingAlbums::findMissingAlbums`, `DeleteMissingPhotos::findMissingPhotos`.
    - _Steps:_ `SyncTest` cases S-115-29/30/31 red; implementation.
    - _Commands:_ `php artisan test --filter=SyncTest`.
    - _Exit:_ listed scenarios green.
12. **I12 – `lychee:sync` with no path** (FR-115-13)
    - _Goal:_ `{paths?*}`, `--owner_id` without signature default (first admin resolved at runtime with paths), per-user loop with eligibility, import root, summary lines, exit codes.
    - _Steps:_ `SyncTest` cases S-115-16/17/18/34 … 37 red; implementation (eligibility and argument checks as small helpers returning enums).
    - _Commands:_ `php artisan test --filter=SyncTest`, `make phpstan`.
    - _Exit:_ listed scenarios green.
13. **I13 – Types and v8 UI** (FR-115-16/17)
    - _Goal:_ `php artisan typescript:transform`; `user-management-service.ts` field; v8 `CreateEditUser.vue` field + hint; v8 `ImportFromServer.vue` hides the symlink toggle when restricted (dropped by Q-115-10), shows the hint; `users.php` and `import_from_server.php` keys in 23 locales.
    - _Commands:_ `vp check --fix`, `vp run typecheck`, `php artisan test --filter=LangTest`.
    - _Exit:_ checks clean; manual S-115-41 (v8) noted.
14. **I14 – v7 UI** (FR-115-16/17)
    - _Goal:_ same as I13 for v7 `CreateEditUser.vue` and `ImportFromServer.vue` (keys from I13 reused).
    - _Commands:_ `vp check --fix`, `vp run typecheck`.
    - _Exit:_ checks clean; manual S-115-41 (v7) noted.
15. **I15 – Docs** 
    - _Goal:_ `docs/specs/2-how-to/import-from-server.md`, knowledge map, roadmap, ADR links.
    - _Exit:_ docs merged with spec wording.
16. **I16 – Quality gate and drift gate**
    - _Goal:_ `vendor/bin/php-cs-fixer fix`, `vp check --fix`, `vp run typecheck`, every test class above, `make phpstan`; drift gate report; operator commit command.
17. **I17 – `server_path` for the server owner only** (FR-115-02/16/19, Q-115-07)
    - _Goal:_ `UserPolicy::CAN_EDIT_SERVER_PATH` (owner only, `before()` skips the admin bypass for it); `AddUserRequest`/`SetUserSettingsRequest::authorize` check it when the `server_path` key is present; `UserManagementRightsResource::$can_edit_server_path`; `php artisan typescript:transform`; v7/v8 `CreateEditUser.vue` show the field and send the key only when the right is true.
    - _Steps:_ `ServerPathTest` S-115-42 cases red (non-owner admin create/edit with a path, `/`, `''`, `null` → 403; without the key → saved; rights flag) → policy, requests, resource → green; then the forms.
    - _Commands:_ `php artisan test --filter=ServerPathTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`, `vp check --fix` (scoped to the two forms), `vp run typecheck`.
    - _Exit:_ S-115-42 green; manual check of the form as owner and as another admin noted.
18. **I18 – Other users' duplicates in a scoped import** (FR-115-12, S-115-43, Q-115-09)
    - _Goal:_ `ImportMode` gains the owner scope (default `false`); `ImportFromServerController` and `lychee:sync` (both `ImportMode` constructions) set it from `import_scope_to_owner`; `DuplicateDTO` carries it; `ThrowUnownedDuplicate` rejects another user's duplicate when `skip_duplicates_not_owned` or the scope is on. Uploads, zip and URL imports unchanged.
    - _Steps:_ `SkippedDuplicateJobTest` S-115-43 cases red (scope on, `skip_duplicates_not_owned` off, Bob's photo as duplicate: with `skip_duplicates` on and `resync_metadata` → skipped, Bob's photo metadata unchanged; with `skip_duplicates` off → skipped, not added to the target album; scope off → added as today) → `ImportMode`, `DuplicateDTO`, pipe, two callers → green.
    - _Commands:_ `php artisan test --filter=SkippedDuplicateJobTest`, `php artisan test --filter=SyncTest`, `php artisan test --filter=ImportFromServerTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
    - _Exit:_ S-115-43 green, existing duplicate tests unchanged.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-115-01 … 03 | I6 / T-115-11, T-115-12 | `ImportFromServerOptionsTest` |
| S-115-04 … 08 | I6 / T-115-11, T-115-12 | `ImportFromServerBrowseTest` |
| S-115-09 … 11 | I7 / T-115-13, T-115-14 | `ImportFromServerTest` |
| S-115-12, 13 | I8 / T-115-15, T-115-16 | `SyncTest`, `ImportFromServerTest` |
| S-115-14, 15 | I10 / T-115-19, T-115-20 | `SyncTest` |
| S-115-16 … 18 | I12 / T-115-23, T-115-24 | `SyncTest` |
| S-115-19 | I4 / T-115-07, T-115-08 | `CreateUserTest`, `EditUserTest`, `ListUserTest` |
| S-115-20 | I6 / T-115-11, T-115-12 | `ImportFromServerOptionsTest` |
| S-115-21, 22 | I6 (browse), I9 (import) / T-115-11, T-115-17 | browse + import |
| S-115-23 … 26 | I9 / T-115-17, T-115-18 | `ImportFromServerTest`, `SyncTest` |
| S-115-27 | I8 / T-115-15, T-115-16 | `ImportImageJobTest` |
| S-115-28 | I7 / T-115-13, T-115-14 | `ImportFromServerTest` |
| S-115-29 … 31 | I11 / T-115-21, T-115-22 | `SyncTest` |
| S-115-32, 33 | I10 / T-115-19, T-115-20 | `SyncTest` |
| S-115-34 … 37 | I12 / T-115-23, T-115-24 | `SyncTest` |
| S-115-38 | I2 / T-115-03, T-115-04 | `ServerPathMigrationTest` |
| S-115-39 | I5 / T-115-09, T-115-10 | `AdminSetupTest` (×2), `Unit/Console/Commands/CreateUserTest` |
| S-115-40 | I3 / T-115-05, T-115-06 | `ImportSettingsTest` |
| S-115-41 | I13, I14 / T-115-26, T-115-28 | manual |
| S-115-42 | I17 / T-115-33 … T-115-35 | `ServerPathTest`; form part manual |
| S-115-43 | I18 / T-115-38, T-115-39 | `SkippedDuplicateJobTest` |

## Analysis Gate

Run 2026-11-15 by the agent, against spec, plan, tasks and open-questions as written that day.

1. Specification completeness
   - [x] Objectives, FR-115-01 … 18 and NFR-115-01 … 07 populated.
   - [x] Q-115-01 … 06 answers folded into FR/NFR (each row cites its question).
   - [x] ASCII mock-ups: user form, import dialog, settings category.
2. Open questions review
   - [x] No `Open` entry left in open-questions.md.
   - [x] ADR-115-01 covers the access and security decisions (Q-115-01/02/03), linked from spec and open-questions.
3. Plan alignment
   - [x] Plan links spec and tasks; dependencies and success criteria use the spec wording.
4. Tasks coverage
   - [x] Every FR maps to a task: FR-01 T-04; FR-02 T-08; FR-03/04/05 T-12; FR-06/07 T-14; FR-08 T-16; FR-09/10 T-20; FR-11 T-18; FR-12 T-22; FR-13 T-24; FR-14 T-04, T-10; FR-15 T-06; FR-16/17 T-26, T-28; FR-18 T-16. Helper T-02.
   - [x] Each implementation task follows a red-test task; increments sized ≤90 minutes (translation tasks split from code where the key count is large).
   - [x] Success, validation and failure branches listed per scenario (S-115-01 … 41).
5. Working-agreement compliance
   - [x] Spec-first, clarification gate, test-first respected; no dependency added.
   - [x] Validation and normalisation delegated to `ImportPathGuard`, `ServerPathRule` and small sync helpers returning enums.
   - [x] ADRs reviewed: ADR-115-01 (new); no older ADR lists Feature 024/033 import decisions that conflict.
6. Tooling readiness
   - [x] Commands listed per increment and task.
   - [x] Results recorded here.

Findings: none blocking. Note for I6: today's `ImportFromServerBrowseTest` relies on the admin browsing with no column; T-115-11 updates its `setUp`.

## Drift Gate Report

Every FR/NFR maps to code and tests; no divergence from the spec.

| Requirement | Code | Tests |
|-------------|------|-------|
| FR-115-01 | migration `2026_11_15_000001_add_server_path_to_users`, `User::$server_path` (default attribute `null`) | `ServerPathMigrationTest` |
| FR-115-02 | `ServerPathRule`, `AddUserRequest`, `SetUserSettingsRequest`, `Actions\User\Create`/`Save`, `UserManagementController`, `UserManagementResource` | `Feature_v2/UserManagement/ServerPathTest` |
| FR-115-03 | `AlbumPolicy::before()` + `canImportFromServer` | `ImportFromServerOptionsTest` |
| FR-115-04 | `ImportFromServerController::options`, `ImportFromServerOptionsResource` | `ImportFromServerOptionsTest` |
| FR-115-05, NFR-115-01 | `HasImportRootTrait`, `ImportFromServerBrowseRequest::authorize`, controller `isTop`/`isListable` | `ImportFromServerBrowseTest` |
| FR-115-06, FR-115-07 | `ImportFromServerRequest::authorize`/`rules`, `ImportFromServerController::get_exec` | `ImportFromServerTest` |
| FR-115-08, NFR-115-04 | `Exec::uploadTrustLevel`, `ImportDTO`, `ImportImageJob` | `SyncTest`, `ImportFromServerTest`, `ImportImageJobTest` |
| FR-115-09, FR-115-10 | `CreateNonExistingAlbums::processRootAsTarget`, `DeleteMissingPhotos::processNode` | `SyncTest` |
| FR-115-11, NFR-115-07 | `BuildTree::followedRealPath`, visited set | `ImportFromServerTest`, `SyncTest` |
| FR-115-12 | owner condition in `CreateNonExistingAlbums`, `DeleteMissingAlbums`, `DeleteMissingPhotos` | `SyncTest` |
| FR-115-13 | `Sync::syncUsers` and helpers, `SyncUserEligibility` | `SyncTest` |
| FR-115-14 | migration grant, `SetUpAdminController`, `ProfileController::store`, `CreateUser` command | `ServerPathMigrationTest`, `AdminSetupTest`, `Install\AdminTest`, `CreateUserCommandTest` |
| FR-115-15 | migration `2026_11_15_000002_create_import_category`, `lang/*/all_settings.php` | `ImportSettingsTest`, `ConfigTranslationKeysTest`, `LangTest` |
| FR-115-16, FR-115-17 | v7/v8 `CreateEditUser.vue`, `ImportFromServer.vue`, `user-management-service.ts`, `lang/*/users.php`, `lang/*/import_from_server.php` | `LangTest`, typecheck; S-115-41 manual (operator) |
| FR-115-19 | `UserPolicy::canEditServerPath` + `before()`, `AddUserRequest::processValidatedValues`, `SetUserSettingsRequest::processValidatedValues`, `UserManagementRightsResource`, v7/v8 `CreateEditUser.vue` | `ServerPathTest` |
| FR-115-18, NFR-115-06 | `ImportImageJob::assertStillInsideImportRoot`, `ImportFileOutsideRootException` | `ImportImageJobTest` |
| NFR-115-02, NFR-115-03 | `ImportPathGuard`, `ImportPathStatus` | `ImportPathGuardTest` |
| NFR-115-05 | admin fixtures get `/`; earlier tests unchanged | `ImportFromServerBrowseTest`, `ImportFromServerTest`, `SyncTest` |

## Exit Criteria
- All tasks `[x]`; quality gate green (php-cs-fixer, `vp check`, typecheck, scoped tests, `make phpstan`).
- `php artisan typescript:transform` rerun, generated types committed.
- Roadmap, knowledge map, how-to, ADR index updated.
- Drift gate report below, manual S-115-41 recorded by the operator.

## Follow-ups / Backlog
- `lychee:create_user` / `lychee:update_user` `--server_path` option (non-goal here).
- Per-user target album for the no-path sync (non-goal here).

## Intent Log
- 2026-11-15: owner request (trust level, root folder option, per-user `server_path`, no-path sync, link handling question). Code survey of `ImportFromServerController`, requests, `Exec` and pipes, `ImportDTO`, `ImportImageJob`, `Sync`, `AlbumPolicy`, user management, first-admin paths, config categories. Q-115-01 … 06 logged and answered by the owner the same day (all A; Q-115-02 + first admin, Q-115-03 as config + new settings category, Q-115-05 + never delete Unsorted). Spec, ADR-115-01, plan and tasks written; analysis gate passed.
- Implementation: increments I1 … I14 test-first, one commit each (scoped `php artisan test --filter=…`, `phpstan` on touched files, `php-cs-fixer`, `vp check`, `vp run typecheck`). Notes: `vp check --fix` rewrites the two unrelated `EmbedCodeDialog.vue` files (breaks the `<\/script>` escape), restore them; a red symlink-import test left a dangling link in `public/uploads` that broke `RequiresEmptyPhotos`, so refusal tests now use `Queue::fake()`; `User::query()->value()` and `Album::query()->value()` trip strict eager loading in tests, use `DB::table`; the album factory's `children_of` hands every child to the parent's owner.
- 2026-11-16: owner directive "Only the owner of the server is allowed to modify the path limitations of users. Admins are not allowed to modify this." Logged and resolved as Q-115-07 (A: 403 for any other admin sending `server_path`, whatever its value, field hidden for them). Spec FR-115-02/16 amended, FR-115-19, S-115-42, DO-115-10, UI-115-04 added, ADR-115-01 amended, increment I17 planned. Earlier the same day: the merge from master brought `ImportImageJobTest` and `SkippedDuplicateJobTest` cases without the trust level argument, fixed in `18f027a8f`.
- 2026-11-17: owner directives. (1) `ServerPathRule` also refuses `..`, `%2e`, `%2f`, `\` like `SecurePathController` (`449ea0399`). (2) Q-115-07 moved from A to B: requests no longer answer 403 when a non-owner admin sends `server_path`, they validate it and ignore it in `processValidatedValues()`. An `exclude` rule for non-owners (no validation, so no 422 telling whether a directory exists) was proposed and turned down as too complex. Spec FR-115-19, S-115-42, API-115-04 amended, `ServerPathTest` updated first (red), T-115-36. (3) `server_path` is always considered sent (absent = `null` for the owner): `has_server_path`/`update_server_path` removed, non-owners keep the stored value through the request, forms always send the key (`null` for non-owners so a stale hidden path cannot 422). The owner's on-disk `UserPolicy::before()` rewrite crashed guests (`$user->id` on null, `CreateUserTest`/`EditUserTest` guest cases), made null-safe. FR-115-02/16/19 amended, T-115-37.
- 2026-11-18: PR #4989 CodeRabbit review triaged (8 open threads + 1 nitpick). Two behaviour questions logged: Q-115-08 (file swapped between the job's root check and the read, NFR-115-06; open) and Q-115-09 (another user's duplicate photo in a scoped import). Q-115-09 answered A by the owner: FR-115-12, S-115-43, DO-115-05, ADR-115-01 amended, increment I18 planned. The owner earlier confirmed on the PR that delete-missing removes a missing album's whole subtree, other owners' sub-albums included (FR-115-12 wording still to align). Remaining review fixes (path normalised in the job, skipped tree entries kept for delete-missing, album reuse under a target filtered by the parent's owner, delete right for delete-missing, `SyncTest` assertions, non-owner 422 test) not yet started; the legacy job payload comment is declined (greenfield stance).
- 2026-11-19: owner directive "we want restricted users to also have import via symlink", logged and resolved as Q-115-10. `ImportFromServerRequest` drops the `declined` rule, `ImportFromServerOptionsResource` sends the config value, `lychee:sync` per-user runs keep `--import_via_symlink`, v7/v8 dialogs show the toggle. Tests flipped first (`ImportFromServerOptionsTest::testRestrictedOptions`, `ImportFromServerTest::testRestrictedSymlinkImport`, new `SyncTest::testNoPathKeepsSymlinkOption`), seen red. ADR-115-01 point 5 amended with the accepted risk, Q-115-08 annotated (its option A assumed copies only). `ImportFromServerBrowseTest` has 2 local-only failures unrelated to the change (stray empty `tests/Frontend` directory, `APP_DEBUG=true` traces in 403 bodies). T-115-40.
- 2026-11-21: owner directive "import from server should NOT follow links even if within the path. We just skip all links.", logged and resolved as Q-115-11. `BuildTree` skips every `is_link()` entry with warning `symlink_skipped` (no per-entry `realpath()`, no import root, no visited set), browse lists no link. Applies to every user, `lychee:sync` explicit paths included. Explicitly requested directories keep the FR-115-05/06 containment (a link given explicitly is resolved). `ImportDTO::import_root` kept for the job re-check (FR-115-18). Tests flipped first (`ImportFromServerBrowseTest::testFullAccessSeesNoLinks`, `testRestrictedAtRoot`, `ImportFromServerTest::testRestrictedImportSkipsAllLinks` with an inside file link, `SyncTest::testExplicitPathSkipsLinks`), seen red. T-115-41.
- 2026-11-21 (follow-up): owner answer "Refuse." to the named-link case. New `ImportPathGuard::isLink()` (lexical normalisation then `is_link()`, so `link/` and `link/.` are caught) used by browse and web import next to `is_dir()` (422, containment still first so outside links stay 403) and by `Sync::validatePaths()` before `realpath()` (exit 1). Links higher up a named path stay resolved (deploy symlinks, server path). Tests first: `ImportPathGuardTest::testIsLink`, `ImportFromServerBrowseTest::testNamedLinkRefused`, `ImportFromServerTest::testImportOfNamedLinkRefused`, `SyncTest::testExplicitLinkPathRefused`, seen red. T-115-42.
- 2026-11-21: `ImportFromServerOptionsResource::$is_restricted` removed on owner request: no reader left after Q-115-10 (backend derives restriction from `importRoot()`). `ImportFromServerOptionsTest` asserts the field is absent (red first), `php artisan typescript:transform` then `vp check --fix resources/js/lychee.d.ts` (the generator reformats the whole file at another line width, the formatter brings it back to a one-line diff). FR-115-04/17 (FR-115-17 still described the hidden toggle, missed in the Q-115-10 update), S-115-04/05, DO-115-07, API-115-01 updated. T-115-43.
