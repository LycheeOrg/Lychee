# Feature 024 Tasks – CLI Sync File-List Support

_Status: Complete_  
_Last updated: 2026-03-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`N-`), and scenario IDs (`S-024-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Tests first: Failing feature tests

- [x] T-024-01 – Verify existing directory-sync tests still pass (regression guard) (S-024-01).  
  _Intent:_ Establish a green baseline before any changes.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommand`  
  _Notes:_ If no `SyncCommandTest` exists, create the test file with at least one existing-directory smoke test.

- [x] T-024-02 – Write failing test: single valid file path imports successfully (FR-024-01, FR-024-02, S-024-02).  
  _Intent:_ Test that a single supported-extension file path is accepted and an import job is created.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommandTest::test_sync_single_file`  
  _Notes:_ Test should currently fail with "Given path is not a directory" or exit code 1.

- [x] T-024-03 – Write failing test: unsupported extension is warned and skipped (FR-024-02, S-024-03).  
  _Intent:_ A file with an extension not in `FileExtensionService` produces a warning, exits 0.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommandTest::test_sync_file_unsupported_extension`

- [x] T-024-04 – Write failing test: non-existent path produces error and exits 1 (FR-024-01, S-024-04).  
  _Intent:_ Passing a path that does not exist on disk returns exit code 1 and prints an error.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommandTest::test_sync_nonexistent_path`

- [x] T-024-05 – Write failing test: mixed directory and file paths processed together (FR-024-04, S-024-05).  
  _Intent:_ One directory arg + one file arg in the same invocation; both are processed.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommandTest::test_sync_mixed_paths`

- [x] T-024-06 – Write failing test: multiple file paths (shell expansion simulation) (FR-024-01, S-024-08).  
  _Intent:_ Three file paths supplied; all three import jobs are created.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommandTest::test_sync_multiple_files`

### I2 – DTO extension

- [x] T-024-07 – Add `file_list` property to `ImportDTO` (DO-024-01).  
  _Intent:_ `ImportDTO` gains a `public array $file_list = []` property for direct-file import mode.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Minimal change; no new class needed.

### I3 – Command layer: classify paths

- [x] T-024-08 – Refactor `Sync::validateDirectories()` to `validatePaths()` returning separate file/directory lists (FR-024-01, FR-024-06, S-024-04).  
  _Intent:_ Method classifies each path via `is_dir()` / `is_file()` after `realpath()`; neither → error + null return.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommand`  
  - `make phpstan`  
  _Notes:_ Update `$signature` argument name and description string per FR-024-06 (CLI-024-01).

### I4 – Import layer: file-list processing in `Exec`

- [x] T-024-09 – Add `Exec::doFiles()` method (or extend `do()`) for direct file import (FR-024-02, S-024-02, S-024-03, S-024-08).  
  _Intent:_ New method validates each file extension, creates an `ImportImageJob` per file, dispatches recompute jobs if a parent album is set.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommand`  
  - `make phpstan`  
  _Notes:_ Reuse `FileExtensionService`. Keep logic flat — no nested if/else trees.

- [x] T-024-10 – Wire `Sync::executeImport()` to call `doFiles()` for file-list paths (FR-024-04, S-024-05).  
  _Intent:_ `executeImport()` iterates directories (existing `exec->do()`) then files (`exec->doFiles()`).  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommand`

### I5 – Duplicate detection and flag guard

- [x] T-024-11 – Implement `skip_duplicates` guard in `doFiles()` (FR-024-05, S-024-06).  
  _Intent:_ When `skip_duplicates=1`, query the target album for photos with matching titles; skip and warn for matches.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommandTest::test_sync_file_skip_duplicate`  
  _Notes:_ Extract shared duplicate-check helper from `ImportPhotos::filterExistingPhotos()` if needed.

- [x] T-024-12 – Print notice when `delete_missing_*` flags are set but all paths are files (FR-024-07, S-024-07).  
  _Intent:_ `Sync::executeImport()` detects "files only + delete_missing flag" combination and prints a notice; no deletions occur.  
  _Verification commands:_  
  - `php artisan test --filter=SyncCommandTest::test_sync_file_delete_missing_notice`

### I6 – Quality gates and documentation

- [x] T-024-13 – Run `vendor/bin/php-cs-fixer fix` and resolve any style issues (NFR-024-03).  
  _Intent:_ All PHP files conform to the project style.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [x] T-024-14 – Run `make phpstan` — zero new errors (NFR-024-03).  
  _Intent:_ Static analysis passes at the project's configured level.  
  _Verification commands:_  
  - `make phpstan`

- [x] T-024-15 – Run full test suite and confirm no regressions (NFR-024-01).  
  _Intent:_ `php artisan test` passes entirely.  
  _Verification commands:_  
  - `php artisan test`

- [x] T-024-16 – Update knowledge map to note file-list support in CLI section.  
  _Intent:_ `docs/specs/4-architecture/knowledge-map.md` references `lychee:sync` accepting both files and directories.  
  _Verification commands:_ Manual review.

- [x] T-024-17 – Update roadmap to set feature 024 status to Complete.  
  _Intent:_ `docs/specs/4-architecture/roadmap.md` Active → Completed row for feature 024.  
  _Verification commands:_ Manual review.

## Notes / TODOs

- T-024-01: If a dedicated `SyncCommandTest.php` does not yet exist under `tests/Feature/Commands/`, create it as part of this task. Use the `artisan()` test helper and `DatabaseTransactions` trait consistent with other command tests.
- T-024-11: The `skip_duplicates_early` config key and the per-node filtering in `ImportPhotos` are directory-mode concerns. File-list mode should implement its own lightweight title-based check rather than reusing the pipe internals, to avoid coupling.
- Follow-up idea (out of scope): `--files-from=-` option to read file paths from stdin, enabling very large file lists beyond shell argument limits.
