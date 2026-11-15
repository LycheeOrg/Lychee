# Feature Plan 115 – Server Import Improvements

_Linked specification:_ [spec.md](spec.md)
_Linked tasks:_ [tasks.md](tasks.md)
_Status:_ Draft (analysis gate passed 2026-11-15, implementation not started)
_Last updated:_ 2026-11-15

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
   - _Goal:_ migration `create_import_from_server_category`: category `import_from_server`, configs `import_root_folder_as_album` (1), `import_scope_to_owner` (1), `sync_users_server_paths` (0), six keys moved; `down()` restores. `all_settings.php` entries (category name/description, 3 × description/details) in 23 locales.
   - _Steps:_ settings test for S-115-40 red; migration; translations.
   - _Commands:_ `php artisan test --filter=ImportFromServerSettingsTest`, `--filter=ConfigTranslationKeysTest`, `--filter=LangTest`.
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
   - _Goal:_ `canImportFromServer` new rule; `ImportFromServerOptionsResource` (`directory`, `is_restricted`, `is_root_folder_album`, `import_via_symlink` false when restricted, 422 when the root is gone); browse request runs the guard before the controller touches the disk; links filtered; no `..` at the root; TE-115-01/02.
   - _Steps:_ `ImportFromServerOptionsTest`, `ImportFromServerBrowseTest` cases S-115-01 … 08, 20, 21/22 (browse half) red; existing cases updated (admin gets `/`); implementation.
   - _Commands:_ `php artisan test --filter=ImportFromServerOptionsTest`, `--filter=ImportFromServerBrowseTest`, `make phpstan`.
   - _Exit:_ listed scenarios green.
7. **I7 – Import request and owner** (FR-115-06/07)
   - _Goal:_ guard on every directory, `import_via_symlink` refused when restricted, owner = `Auth::id()`, `CAN_UPLOAD` on `album_id`, `Exec` receives the import root.
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
    - _Goal:_ `php artisan typescript:transform`; `user-management-service.ts` field; v8 `CreateEditUser.vue` field + hint; v8 `ImportFromServer.vue` hides the symlink toggle when restricted, shows the hint; `users.php` and `import_from_server.php` keys in 23 locales.
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
| S-115-40 | I3 / T-115-05, T-115-06 | `ImportFromServerSettingsTest` |
| S-115-41 | I13, I14 / T-115-26, T-115-28 | manual |

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
