# Feature 114 Tasks – Fix Skipped Job Status

_Status: Complete_  
_Last updated: 2026-11-15_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-114-01 – Failing job tests for async and sync skips (FR-114-01, FR-114-02, FR-114-03, S-114-01, S-114-02, S-114-03, S-114-04, S-114-05).  
  _Intent:_ `tests/ImageProcessing/Jobs/SkippedDuplicateJobTest.php`: async `ImportImageJob` skip and resync, async `ProcessImageJob` skip (fake queue job: no exception, not failed, not released, history `SKIPPED`, `info` line); sync upload `409` + `SKIPPED`; sync `ImportImageJob` rethrows + `SKIPPED`.  
  _Verification commands:_  
  - `php artisan test --filter=SkippedDuplicateJobTest` (red before T-114-03)

- [x] T-114-02 – Failing unit tests for `failed()`, enum name and dashboard count (FR-114-03, FR-114-05, S-114-06, S-114-07).  
  _Intent:_ `ImportImageJobTest`: `failed(PhotoSkippedException)` → `SKIPPED`, `failed(RuntimeException)` → `FAILURE`. `CoverageTest`: `JobStatus::SKIPPED->name() === 'skipped'`. `AdminStatsServiceTest`: a `SKIPPED` row is not in `failed_jobs_24h`.  
  _Verification commands:_  
  - `php artisan test --filter=ImportImageJobTest`  
  - `php artisan test --filter=CoverageTest`  
  - `php artisan test --filter=AdminStatsServiceTest`

- [x] T-114-03 – Backend skip handling (FR-114-01, FR-114-02, FR-114-03, FR-114-05, N-114-01).  
  _Intent:_ `JobStatus::SKIPPED = 4`; `App\Jobs\Traits\HandlesSkippedPhotoTrait::skipped()`; `catch (PhotoSkippedException)` in `ImportImageJob::handle()` / `ProcessImageJob::handle()` (return type `?Photo`); `HasFailedTrait::failed()` skip branch; `JobHistoryResource` literal; `php artisan typescript:transform`.  
  _Verification commands:_  
  - `php artisan test --filter=SkippedDuplicateJobTest`, `ImportImageJobTest`, `ProcessImageJobTest`, `CoverageTest`, `AdminStatsServiceTest`, `UploadWithMetadataTest`, `PhotoAddTest`, `JobsTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-114-04 – Jobs page and translations (FR-114-04, NFR-114-03, S-114-08).  
  _Intent:_ v7 and v8 `Jobs.vue`: `skipped` label, muted text colour, meter segment. `'skipped'` in every `lang/*/jobs.php`.  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`  
  - `vp check --fix resources/js/v7/views/admin/Jobs.vue resources/js/v8/views/admin/Jobs.vue`  
  - `vp run typecheck`

- [x] T-114-05 – Docs and drift gate.  
  _Intent:_ knowledge map entry, roadmap row, drift gate report in the plan.  
  _Verification commands:_ —

## Notes / TODOs
- Scope `vp check --fix` to changed files (repo-wide autofix breaks `EmbedCodeDialog.vue`).
