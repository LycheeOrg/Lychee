# Current Session

_Last updated: 2026-03-03_

## Active Features

**Feature 024 – CLI Sync File-List Support**
- Status: Planning (spec, plan, tasks complete)
- Priority: P2
- License: Open
- Started: 2026-03-03
- Dependencies: None

## Session Summary

Feature 024 (CLI Sync File-List Support) specification, plan, and tasks created per GitHub issue #1231. The `lychee:sync` artisan command currently rejects individual file paths with "Given path is not a directory". This feature extends the command to accept both directories and individual file paths in a single invocation.

### Feature 024: CLI Sync File-List Support

**Problem:**
Users want to run commands like:
```bash
php artisan lychee:sync $(find /storage/NAS/photos/ -type f -mtime -1) \
  --import_via_symlink=1 --skip_duplicates=1
```
Currently this fails because the `lychee:sync` command only accepts directory paths, not individual file paths.

**Key Design Decisions:**
- Accept both file and directory paths in the existing `{dir*}` (renamed to `{paths*}`) positional argument.
- File paths bypass the `BuildTree` pipe and are queued directly via `ImportImageJob`.
- Mixed invocations (some dirs, some files) are supported; each path is processed independently.
- `skip_duplicates` applies to file-list mode; `delete_missing_*` flags are inactive for file paths (with a notice).
- No new external dependencies; no HTTP API changes.

**Implementation Phases:**
- I1 (Tests first): Write failing tests for S-024-02 through S-024-08 (~60 min)
- I2 (DTO extension): Add `file_list` to `ImportDTO` (~30 min)
- I3 (Command layer): Classify paths in `Sync::validatePaths()` (~60 min)
- I4 (Import layer): Add `Exec::doFiles()` for direct file import (~60 min)
- I5 (Duplicate detection & flag guard): skip_duplicates + delete_missing notice (~45 min)
- I6 (Quality gates): php-cs-fixer, phpstan, docs (~30 min)

**Total: 6 increments (~5.25 hours)**

**Deliverables:**
1. [spec.md](docs/specs/4-architecture/features/024-sync-file-list/spec.md)
2. [plan.md](docs/specs/4-architecture/features/024-sync-file-list/plan.md)
3. [tasks.md](docs/specs/4-architecture/features/024-sync-file-list/tasks.md)

## Next Steps

1. Run analysis gate checklist.
2. Begin implementation with I1: write failing feature tests.

## Open Questions

None - requirements are clear from issue #1231 and existing codebase analysis.

## References

**Feature 024:**
- Feature spec: [024-sync-file-list/spec.md](docs/specs/4-architecture/features/024-sync-file-list/spec.md)
- Implementation plan: [024-sync-file-list/plan.md](docs/specs/4-architecture/features/024-sync-file-list/plan.md)
- Task checklist: [024-sync-file-list/tasks.md](docs/specs/4-architecture/features/024-sync-file-list/tasks.md)
- GitHub issue: https://github.com/LycheeOrg/Lychee/issues/1231

**Common:**
- Roadmap: [roadmap.md](docs/specs/4-architecture/roadmap.md)
- Open questions: [open-questions.md](docs/specs/4-architecture/open-questions.md)

---

**Session Context for Handoff:**

Feature 024 (CLI Sync File-List Support) fully planned with 6 increments (~5.25 hours total). This is an open-licensed feature:
1. `lychee:sync` positional argument accepts files and directories.
2. File paths bypass `BuildTree` pipe; queued directly via `ImportImageJob`.
3. Mixed invocations (dirs + files) supported in a single call.
4. `skip_duplicates` applies to file-list mode; `delete_missing_*` inactive for file paths.
5. No new dependencies; no HTTP API changes.

Ready to begin I1: write failing feature tests.
