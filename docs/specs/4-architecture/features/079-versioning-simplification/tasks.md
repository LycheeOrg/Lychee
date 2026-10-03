# Feature 079 Tasks – Versioning Code Simplification

_Status: Testing_  
_Last updated: 2026-10-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Delete dead code

- [x] T-079-01 – Delete `CheckUpdate`, `UpdateStatus`, `CheckUpdateTest` and the `CheckUpdate` binding.  
  _Verification commands:_
  - `make phpstan`
  - `php artisan test --filter=CheckUpdateAvailabilityTest`

- [x] T-079-02 – Delete `HasVersion`, `HasIsRelease`, `VersionControl` and their `implements` clauses.  
  _Verification commands:_
  - `make phpstan`

### I2 – `GitHubVersion` local + compare only (FR-079-01 … FR-079-04, NFR-079-01)

- [x] T-079-03 – Rewrite `tests/Unit/Metadata/GitHubVersionTest.php` (S-079-01 … S-079-06, S-079-08).  
  _Intent:_ helper arranging `.git/HEAD` / ref file contents; compare mock bound for master cases; asserts no remote call for feature branch, detached HEAD and `with_remote: false`.  
  _Verification commands:_
  - `php artisan test --filter=GitHubVersionTest` (red before T-079-04)

- [x] T-079-04 – Rewrite `GitHubVersion`; delete `GitCommits`, `GitTags`, `AbstractGitRemote`, `GitRemote`, `CommitsRequest`, `TagsRequest`, `GitRemoteTest`, `commits.json`, `tags.json`, the two config URLs and the `CommitsRequest` binding (FR-079-01 … FR-079-04, S-079-07).  
  _Verification commands:_
  - `php artisan test --filter='GitHubVersionTest|BranchCheckTest|CheckUpdateAvailabilityTest'`
  - `make phpstan`

### I3 – One git-info formatter (FR-079-05, FR-079-06)

- [x] T-079-05 – `tests/Unit/Actions/Diagnostics/Pipes/Infos/VersionInfoTest.php`: channel + git info for branch, detached, no git data (S-079-06, S-079-09).  
  _Verification commands:_
  - `php artisan test --filter=VersionInfoTest` (red before T-079-06)

- [x] T-079-06 – `LycheeGitInfo` (`info`, `extra`), `VersionInfo::getGitInfo()` / `getChannelName()`, `UpdateController::get()` (FR-079-05, FR-079-06).  
  _Verification commands:_
  - `php artisan test --filter='VersionInfoTest|Feature_v2\\Maintenance\\UpdateTest|Feature_v2\\Diagnostics\\InfoTest'`

### I4 – Gate + docs (NFR-079-02)

- [x] T-079-07 – Quality gate: php-cs-fixer, PHPStan, `typescript:transform` no diff, all related test classes.  
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`
  - `php artisan typescript:transform` (only `App.Enum.UpdateStatus` removed)
  - `php artisan test --filter='GitHubVersionTest|VersionInfoTest|BranchCheckTest|CheckUpdateAvailabilityTest|AdminUpdateStatusControllerTest|Feature_v2\\VersionTest|Feature_v2\\Maintenance\\UpdateTest|Feature_v2\\Diagnostics\\InfoTest'`

- [x] T-079-08 – Knowledge map, Feature 037 FR-037-08 cross-reference, roadmap row.  
  _Verification commands:_ manual review.

### I5 – Remove the online updater (FR-079-07 … FR-079-10)

- [x] T-079-09 – Tests: `tests/Unit/Actions/InstallUpdate/ApplyMigrationTest.php` (pipeline = `ArtisanMigrate` only, ANSI stripped) and `tests/Feature_v2/Maintenance/UpdateTest.php` asserting `GET`/`POST Maintenance::update` → 404 for admins (S-079-10, S-079-11, S-079-12).  
  _Verification commands:_
  - `php artisan test --filter='ApplyMigrationTest|Feature_v2\\Maintenance\\UpdateTest'` (red before T-079-10)

- [x] T-079-10 – Backend removal: routes, `UpdateController` → `MigrateController`, `ApplyUpdate` → `ApplyMigration`, delete `BranchCheck` / `GitPull` / `ComposerCall` (+ tests), `CommandExecutor`, `UpdatableCheck` (+ `Errors` entry), `VersionControlException`, `Maintenance\UpdateRequest`, `UpdateInfo`, `UpdateCheckInfo`, `GitHubVersion::hasPermissions()` (+ tests) (FR-079-07, FR-079-08, FR-079-10).  
  _Verification commands:_
  - `php artisan test --filter='ApplyMigrationTest|Feature_v2\\Maintenance\\UpdateTest|GitHubVersionTest|Feature_v2\\Diagnostics'`
  - `make phpstan`

- [x] T-079-11 – Config-removal migration for `allow_online_git_pull` / `apply_composer_update` (reversible, `@codeCoverageIgnore`, no test) and their labels in `lang/*/all_settings.php` (FR-079-09, S-079-13).  
  _Verification commands:_
  - `make phpstan`

- [x] T-079-12 – Frontend: remove `MaintenanceUpdate.vue` (v7 + v8) and its use in `Maintenance.vue`, `MaintenanceService.updateGet/updateCheck`, `maintenance.update.*` keys in `lang/*/maintenance.php`; regenerate TS types (FR-079-07, S-079-14).  
  _Verification commands:_
  - `php artisan typescript:transform`
  - `npm run format`
  - `npm run check`

- [x] T-079-13 – Quality gate + knowledge map + roadmap (FR-079-07 … FR-079-10).  
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`

## Notes / TODOs
- Pending: manual browser check of the Maintenance page without the Updates card, v7 and v8 (S-079-14).
- Review items 4 and 6 are out of scope (Q-079-01).
