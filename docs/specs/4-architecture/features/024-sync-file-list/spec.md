# Feature 024 – CLI Sync File-List Support

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-03-03 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/024-sync-file-list/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/024-sync-file-list/tasks.md` |
| Roadmap entry | #024 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The `lychee:sync` artisan command currently only accepts directories as input paths. When a user passes individual file paths (e.g., piped from `find ... -mtime -1`), the command fails with "Given path is not a directory". This feature extends `lychee:sync` to accept individual file paths in addition to directories, enabling users to import a selective subset of files — such as recently-modified photos — without restructuring their filesystem or writing wrapper scripts.

Affected modules: **CLI** (`App\Console\Commands\Sync`), **Import pipeline** (`App\Actions\Import`, `App\Actions\Import\Pipes\BuildTree`), **DTO** (`App\DTO\ImportDTO`, `App\DTO\FolderNode`).

## Goals

1. Allow `lychee:sync` to accept individual file paths (in addition to directories) as positional arguments.
2. When a file path is supplied, import the file directly into the target album (or root) without recursive directory traversal.
3. Mixed arguments (some directories, some files) work correctly in a single invocation.
4. No change to existing directory-sync behaviour.
5. Provide clear error messages when a supplied path is neither a valid file nor a valid directory.

## Non-Goals

- Adding server-side search/filter parameters (mtime, size, glob) directly to the CLI — the user is expected to pre-filter with shell tools like `find` and pass the resulting file list.
- Changing the HTTP import API.
- Changing album-creation logic for file-mode imports (files are placed directly in the parent album; no sub-album is created from the filename).
- Supporting remote/URL paths in file-list mode.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-024-01 | `lychee:sync` positional argument `dir*` accepts both directory paths and individual file paths. | Each supplied path is resolved and classified as file or directory before processing begins. | Paths are validated with `is_file()` / `is_dir()` after `realpath()`. | A path that is neither a valid file nor a valid directory produces an error line and aborts (exit code 1). | — | Issue #1231 |
| FR-024-02 | When a path resolves to a regular file with a supported image/media extension, it is imported directly into the target album (or root) using the existing `ImportImageJob` pipeline. | File is added to the job bus; album-stats recompute jobs are dispatched. | `FileExtensionService::isSupportedOrAcceptedFileExtension()` gates acceptance. | Unsupported file extension produces a warning line and the file is skipped; remaining paths continue. | — | Issue #1231 |
| FR-024-03 | When a path resolves to a directory, the existing recursive tree-based import continues unchanged. | BuildTree pipe traverses the directory tree as before. | N/A | `InvalidDirectoryException` or `ReservedDirectoryException` propagate as today. | — | Existing behaviour |
| FR-024-04 | A single invocation may mix directory paths and file paths. | Each path is processed in the order it is supplied; directories use tree mode, files use direct-import mode. | — | Mixed-mode errors (per-path) do not abort the entire run; the command reports errors per path and continues with remaining paths. | — | Issue #1231 (comment) |
| FR-024-05 | Files supplied in file-list mode are subject to `skip_duplicates` logic (same as directory mode). | Duplicate detection runs against the target album before a job is created. | `skip_duplicates` option controls whether duplicate detection fires. | If a duplicate is detected and `skip_duplicates=1`, a warning is emitted and the file is skipped. | — | Consistency with existing behaviour |
| FR-024-06 | The argument placeholder in `$signature` and the `--help` output reflect that both files and directories are accepted. | Help text reads: `{paths* : Files or directories to sync}` (or equivalent). | `php artisan lychee:sync --help` shows the updated description. | — | — | UX clarity |
| FR-024-07 | `delete_missing_photos` and `delete_missing_albums` options only apply to directory-mode paths; they are silently ignored for individual file paths. | No photos/albums are unexpectedly deleted when only file paths are supplied. | If all paths are individual files and `delete_missing_*` flags are set, the command logs a notice explaining the flags are inactive in file-list mode. | — | — | Safety |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-024-01 | No regression in existing directory-sync behaviour. | Stability | All existing `Sync` command tests pass. | `App\Console\Commands\Sync`, `App\Actions\Import\Pipes\BuildTree` | Existing test suite |
| NFR-024-02 | File-list mode has no worse throughput than adding each file to a single-file directory. | Performance | Import job count equals number of supported files supplied. | `ImportImageJob` pipeline | — |
| NFR-024-03 | New code follows PSR-4, strict comparisons, no `empty()`, `in_array()` with `true` third arg, snake_case variables. | Coding conventions | `vendor/bin/php-cs-fixer fix` + `make phpstan` both pass. | — | AGENTS.md |
| NFR-024-04 | No new external dependencies. | Dependency control | `composer.json` unchanged. | — | AGENTS.md |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|-------------------------------|
| S-024-01 | Single directory path — tree sync proceeds exactly as before; no regression. |
| S-024-02 | Single file path with supported extension — file is imported into target album (or root). |
| S-024-03 | Single file path with unsupported extension — warning emitted, file skipped, exit code 0. |
| S-024-04 | Single file path that does not exist — error emitted, exit code 1. |
| S-024-05 | Mix of directory and file paths — directory traversed, file imported directly; both processed. |
| S-024-06 | File path supplied with `skip_duplicates=1` when the same file already exists — duplicate warning, file skipped. |
| S-024-07 | `delete_missing_photos=1` used with only file paths — notice printed explaining the flag is inactive; no deletions. |
| S-024-08 | Multiple file paths passed via shell expansion (`$(find ... -mtime -1)`) — all files processed. |

## Test Strategy

- **CLI:** Feature tests for `lychee:sync` command using `artisan()` test helper; cover scenarios S-024-01 through S-024-08.
- **Core/Application:** Unit tests for any new helper methods that classify paths.
- **REST:** Not applicable (no HTTP interface changes).
- **UI (JS/Selenium):** Not applicable (CLI-only change).
- **Docs/Contracts:** Update `--help` description string; no OpenAPI changes.

## Interface & Contract Catalogue

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-024-01 | `php artisan lychee:sync {paths* : Files or directories to sync} [options...]` | Accepts one or more paths; each path is independently classified as file or directory and processed accordingly. |

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-024-01 | `ImportDTO` may receive a `file_list` property alongside (or instead of) the root `path`, holding individual file paths to import directly without tree traversal. | `App\DTO\ImportDTO`, `App\Actions\Import\Exec` |

## Telemetry & Observability

No new telemetry events. Existing `ImportEventReport` notice/warning/debug lines cover file-level import reporting.

## Documentation Deliverables

- Update roadmap (`docs/specs/4-architecture/roadmap.md`) — add Feature 024.
- Update knowledge map (`docs/specs/4-architecture/knowledge-map.md`) — note that `lychee:sync` accepts file paths.
- Update `_current-session.md`.

## Fixtures & Sample Data

No new fixture files required. Existing test helpers (e.g., `tests/Feature/Commands/`) and PHPUnit test doubles are sufficient.

## Spec DSL

```yaml
cli_commands:
  - id: CLI-024-01
    command: "php artisan lychee:sync {paths*} [--album_id=] [--owner_id=] ..."
    description: "Accepts files and/or directories; files import directly, directories traverse tree."
domain_objects:
  - id: DO-024-01
    name: ImportDTO
    fields:
      - name: file_list
        type: "string[]"
        constraints: "Optional; non-empty means direct file import mode"
```

## Appendix

### Related Issue

GitHub issue #1231: [Command line sync/import file list or search parameters](https://github.com/LycheeOrg/Lychee/issues/1231)

Reported use-case:
```bash
# User wants to sync only recently-modified files
php artisan lychee:sync $(find /storage/NAS/photos/ -type f -mtime -1) \
  --import_via_symlink=1 --skip_duplicates=1
# Currently fails: "Given path is not a directory"
```

### Current Architecture Note

`BuildTree::normalizePath()` calls `is_dir()` and throws `InvalidDirectoryException` if the path is not a directory. The fix requires path classification before the `BuildTree` pipe so that file paths bypass the pipe entirely and are queued directly via `ImportImageJob`.
