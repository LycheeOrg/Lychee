# Feature 080 Tasks – Versioning Code Simplification

_Status: Testing_  
_Last updated: 2026-10-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Delete dead code

- [x] T-080-01 – Delete `CheckUpdate`, `UpdateStatus`, `CheckUpdateTest` and the `CheckUpdate` binding.  
  _Verification commands:_
  - `make phpstan`
  - `php artisan test --filter=CheckUpdateAvailabilityTest`

- [x] T-080-02 – Delete `HasVersion`, `HasIsRelease`, `VersionControl` and their `implements` clauses.  
  _Verification commands:_
  - `make phpstan`

### I2 – `GitHubVersion` local + compare only (FR-080-01 … FR-080-04, NFR-080-01)

- [x] T-080-03 – Rewrite `tests/Unit/Metadata/GitHubVersionTest.php` (S-080-01 … S-080-06, S-080-08).  
  _Intent:_ helper arranging `.git/HEAD` / ref file contents; compare mock bound for master cases; asserts no remote call for feature branch, detached HEAD and `with_remote: false`.  
  _Verification commands:_
  - `php artisan test --filter=GitHubVersionTest` (red before T-080-04)

- [x] T-080-04 – Rewrite `GitHubVersion`; delete `GitCommits`, `GitTags`, `AbstractGitRemote`, `GitRemote`, `CommitsRequest`, `TagsRequest`, `GitRemoteTest`, `commits.json`, `tags.json`, the two config URLs and the `CommitsRequest` binding (FR-080-01 … FR-080-04, S-080-07).  
  _Verification commands:_
  - `php artisan test --filter='GitHubVersionTest|BranchCheckTest|CheckUpdateAvailabilityTest'`
  - `make phpstan`

### I3 – One git-info formatter (FR-080-05, FR-080-06)

- [x] T-080-05 – `tests/Unit/Actions/Diagnostics/Pipes/Infos/VersionInfoTest.php`: channel + git info for branch, detached, no git data (S-080-06, S-080-09).  
  _Verification commands:_
  - `php artisan test --filter=VersionInfoTest` (red before T-080-06)

- [x] T-080-06 – `LycheeGitInfo` (`info`, `extra`), `VersionInfo::getGitInfo()` / `getChannelName()`, `UpdateController::get()` (FR-080-05, FR-080-06).  
  _Verification commands:_
  - `php artisan test --filter='VersionInfoTest|Feature_v2\\Maintenance\\UpdateTest|Feature_v2\\Diagnostics\\InfoTest'`

### I4 – Gate + docs (NFR-080-02)

- [x] T-080-07 – Quality gate: php-cs-fixer, PHPStan, `typescript:transform` no diff, all related test classes.  
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`
  - `php artisan typescript:transform` (only `App.Enum.UpdateStatus` removed)
  - `php artisan test --filter='GitHubVersionTest|VersionInfoTest|BranchCheckTest|CheckUpdateAvailabilityTest|AdminUpdateStatusControllerTest|Feature_v2\\VersionTest|Feature_v2\\Maintenance\\UpdateTest|Feature_v2\\Diagnostics\\InfoTest'`

- [x] T-080-08 – Knowledge map, Feature 037 FR-037-08 cross-reference, roadmap row.  
  _Verification commands:_ manual review.

### I5 – Remove the online updater (FR-080-07 … FR-080-10)

- [x] T-080-09 – Tests: `tests/Unit/Actions/InstallUpdate/ApplyMigrationTest.php` (pipeline = `ArtisanMigrate` only, ANSI stripped) and `tests/Feature_v2/Maintenance/UpdateTest.php` asserting `GET`/`POST Maintenance::update` → 404 for admins (S-080-10, S-080-11, S-080-12).  
  _Verification commands:_
  - `php artisan test --filter='ApplyMigrationTest|Feature_v2\\Maintenance\\UpdateTest'` (red before T-080-10)

- [x] T-080-10 – Backend removal: routes, `UpdateController` → `MigrateController`, `ApplyUpdate` → `ApplyMigration`, delete `BranchCheck` / `GitPull` / `ComposerCall` (+ tests), `CommandExecutor`, `UpdatableCheck` (+ `Errors` entry), `VersionControlException`, `Maintenance\UpdateRequest`, `UpdateInfo`, `UpdateCheckInfo`, `GitHubVersion::hasPermissions()` (+ tests) (FR-080-07, FR-080-08, FR-080-10).  
  _Verification commands:_
  - `php artisan test --filter='ApplyMigrationTest|Feature_v2\\Maintenance\\UpdateTest|GitHubVersionTest|Feature_v2\\Diagnostics'`
  - `make phpstan`

- [x] T-080-11 – Config-removal migration for `allow_online_git_pull` / `apply_composer_update` (reversible, `@codeCoverageIgnore`, no test) and their labels in `lang/*/all_settings.php` (FR-080-09, S-080-13).  
  _Verification commands:_
  - `make phpstan`

- [x] T-080-12 – Frontend: remove `MaintenanceUpdate.vue` (v7 + v8) and its use in `Maintenance.vue`, `MaintenanceService.updateGet/updateCheck`, `maintenance.update.*` keys in `lang/*/maintenance.php`; regenerate TS types (FR-080-07, S-080-14).  
  _Verification commands:_
  - `php artisan typescript:transform`
  - `npm run format`
  - `npm run check`

- [x] T-080-13 – Quality gate + knowledge map + roadmap (FR-080-07 … FR-080-10).  
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`

## Notes / TODOs
- Pending: manual browser check of the Maintenance page without the Updates card, v7 and v8 (S-080-14).
- Review items 4 and 6 are out of scope (Q-080-01).
