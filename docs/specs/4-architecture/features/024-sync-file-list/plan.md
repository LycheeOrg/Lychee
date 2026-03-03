# Feature Plan 024 – CLI Sync File-List Support

_Linked specification:_ `docs/specs/4-architecture/features/024-sync-file-list/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-03-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Users can pipe `find` (or any shell tool) output directly to `lychee:sync` and import only the matching files without getting "Given path is not a directory". The command accepts individual file paths alongside directory paths, performs duplicate detection, and respects all existing import flags. No regression in current directory-sync behaviour.

**Success signals:**
- `php artisan lychee:sync $(find /path -mtime -1) --skip_duplicates=1` completes without errors.
- Existing directory-sync tests remain green.
- PHPStan and php-cs-fixer both pass at zero findings.

## Scope Alignment

- **In scope:**
  - Extend the `lychee:sync` command argument handling to accept file paths.
  - Add a `file_list` field (or equivalent) to `ImportDTO` to carry individual file paths that bypass `BuildTree`.
  - Add a new pipe or pre-processing step that directly creates `ImportImageJob` entries for file paths.
  - Update argument description in `$signature` / help text.
  - Unit and feature tests covering all eight scenarios in the Branch & Scenario Matrix.
  - Knowledge-map update.
- **Out of scope:**
  - Server-side filtering (mtime, glob, size) parameters on the CLI (user pre-filters with shell tools).
  - HTTP import API changes.
  - Album creation from filename hierarchy for file-mode imports.

## Dependencies & Interfaces

| Dependency | Notes |
|-----------|-------|
| `App\Console\Commands\Sync` | Entry point to extend |
| `App\Actions\Import\Exec` | Orchestrator; needs awareness of file vs directory path |
| `App\Actions\Import\Pipes\BuildTree` | Currently throws on non-directory paths; file paths must bypass this pipe |
| `App\DTO\ImportDTO` | Needs a `file_list` property for direct-file mode |
| `App\DTO\FolderNode` | Used by BuildTree; unchanged for file mode |
| `App\Jobs\ImportImageJob` | Existing job to enqueue per file |
| `App\Services\Image\FileExtensionService` | Used to validate file extensions |
| `tests/Feature/Commands/` | Existing test location for CLI command tests |

## Assumptions & Risks

- **Assumptions:**
  - `FileExtensionService::isSupportedOrAcceptedFileExtension()` correctly identifies importable extensions.
  - The existing `ImportImageJob` pipeline is sufficient for direct-file import.
  - `skip_duplicates` logic can operate at the album level without a `FolderNode` context.
- **Risks / Mitigations:**
  - *Risk:* `skip_duplicates_early` short-circuit in `ImportPhotos` pipe assumes a `FolderNode` with an album. *Mitigation:* File-mode duplicate detection will be handled in the pre-processing step before jobs are enqueued, bypassing the pipe-level filtering.
  - *Risk:* `delete_missing_photos`/`delete_missing_albums` flags could cause unintended deletions if applied to file-list mode. *Mitigation:* These flags are silently skipped for individual-file paths per FR-024-07.

## Implementation Drift Gate

Run after all tasks complete and the latest build is green:
1. Confirm every FR-024-XX maps to a test and a code change.
2. Run `php artisan test --filter=SyncCommand` — all pass.
3. Run `make phpstan` — zero errors.
4. Run `vendor/bin/php-cs-fixer fix --dry-run` — zero diff.
5. Record results in the "Analysis Gate" section below.

## Increment Map

### I1 – Tests first: Write failing feature tests for file-list scenarios (≤60 min)
- _Goal:_ Establish failing test coverage for all new scenarios before touching implementation code.
- _Preconditions:_ Existing `Sync` command tests exist and pass.
- _Steps:_
  1. Locate (or create) `tests/Feature/Commands/SyncCommandTest.php`.
  2. Add test methods for S-024-02 (single file), S-024-03 (unsupported ext), S-024-04 (missing path), S-024-05 (mixed), S-024-07 (delete_missing with files only).
  3. Run tests — confirm new tests fail with "Given path is not a directory" or equivalent.
- _Commands:_ `php artisan test --filter=SyncCommand`
- _Exit:_ New test methods exist and fail as expected; existing tests still pass.

### I2 – DTO extension: Add `file_list` to `ImportDTO` (≤30 min)
- _Goal:_ Provide a typed container for individual file paths in the import pipeline.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Open `app/DTO/ImportDTO.php`; add `public array $file_list = []` property.
  2. Run PHPStan — zero new errors.
- _Commands:_ `make phpstan`
- _Exit:_ `ImportDTO` has `file_list` property; PHPStan clean.

### I3 – Command layer: Classify paths in `Sync::validateDirectories()` (≤60 min)
- _Goal:_ Instead of aborting on non-directory paths, split supplied paths into `$directories` and `$files` lists.
- _Preconditions:_ I2 complete.
- _Steps:_
  1. Rename argument in `$signature` from `{dir*}` to `{paths* : Files or directories to sync}` and update `validateDirectories()` method (rename to `validatePaths()` or similar).
  2. For each path: `realpath()` then `is_dir()` → add to directories list; `is_file()` → add to files list; neither → error + return null.
  3. Return both lists (e.g., `['directories' => [...], 'files' => [...]]` or two separate arrays).
  4. Update `handle()` to pass files alongside directories to `executeImport()`.
- _Commands:_ `php artisan test --filter=SyncCommand`, `make phpstan`
- _Exit:_ S-024-04 test passes; existing directory tests still pass.

### I4 – Import layer: Add file-list processing to `Exec` (≤60 min)
- _Goal:_ Allow `Exec::do()` to handle a list of individual files, bypassing `BuildTree`.
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Add method `Exec::doFiles(array $file_paths, ?Album $parent_album): array` (or extend `do()` to detect file vs directory mode via `ImportDTO::file_list`).
  2. For each file: validate extension via `FileExtensionService`, apply `skip_duplicates` logic if needed, create `ImportImageJob`, push to job bus.
  3. Dispatch `RecomputeAlbumSizeJob` and `RecomputeAlbumStatsJob` after processing the file list (if parent album is set).
  4. Wire `executeImport()` in `Sync` to call the new method for any file paths.
- _Commands:_ `php artisan test --filter=SyncCommand`, `make phpstan`
- _Exit:_ S-024-02, S-024-03, S-024-05, S-024-08 tests pass.

### I5 – Duplicate detection & flag guard (≤45 min)
- _Goal:_ Ensure `skip_duplicates` and `delete_missing_*` behave correctly for file-list mode per FR-024-05 and FR-024-07.
- _Preconditions:_ I4 complete.
- _Steps:_
  1. In `doFiles()`: when `skip_duplicates=1`, query the target album for existing photos with matching titles (reuse `filterExistingPhotos` logic or extract a shared helper).
  2. In `Sync::executeImport()`: when all paths are individual files and `delete_missing_photos` or `delete_missing_albums` is set, print notice per FR-024-07.
- _Commands:_ `php artisan test --filter=SyncCommand`
- _Exit:_ S-024-06, S-024-07 tests pass.

### I6 – Quality gates and documentation (≤30 min)
- _Goal:_ Ensure all quality gates pass and documentation is updated.
- _Preconditions:_ I5 complete, all tests green.
- _Steps:_
  1. Run `vendor/bin/php-cs-fixer fix`.
  2. Run `make phpstan` — zero errors.
  3. Update `docs/specs/4-architecture/knowledge-map.md` to note file-list support in CLI section.
  4. Update roadmap to mark feature 024 status.
- _Commands:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`, `php artisan test --filter=SyncCommand`
- _Exit:_ All quality gates green; docs updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-024-01 | I1 / T-024-01 | Covered by existing tests + regression guard in I1 |
| S-024-02 | I1 (test), I4 (impl) / T-024-02, T-024-08 | Core new behaviour |
| S-024-03 | I1 (test), I4 (impl) / T-024-03, T-024-08 | Unsupported extension warning |
| S-024-04 | I1 (test), I3 (impl) / T-024-04, T-024-06 | Missing path error |
| S-024-05 | I1 (test), I3+I4 (impl) / T-024-05, T-024-06, T-024-08 | Mixed mode |
| S-024-06 | I1 (test), I5 (impl) / T-024-05, T-024-09 | Skip duplicates |
| S-024-07 | I1 (test), I5 (impl) / T-024-05, T-024-10 | Flag guard |
| S-024-08 | I1 (test), I4 (impl) / T-024-02, T-024-08 | Shell expansion multiple files |

## Analysis Gate

_Not yet completed — run after spec, plan, and tasks are agreed._

## Exit Criteria

- [ ] All eight S-024-XX scenarios covered by passing tests.
- [ ] PHPStan: 0 new errors.
- [ ] php-cs-fixer: 0 changes.
- [ ] `php artisan test` full suite passes.
- [ ] `--help` output reflects updated argument description.
- [ ] Knowledge map updated.
- [ ] Roadmap entry for 024 is set to Complete.

## Follow-ups / Backlog

- Consider adding a `--files-from` option (reads a newline-separated list of paths from a file or stdin) as a follow-up enhancement — this would handle very large file lists that exceed shell argument length limits.
- Consider adding `--mtime` / `--since` filter parameters to the command itself (out of scope for this feature per Non-Goals).
