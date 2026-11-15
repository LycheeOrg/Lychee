# Open Questions – Feature 114

Open questions for [Feature 114](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-114-01~~ | 114 | High | What the `queue:work` output line shows for a photo skipped as a duplicate | Resolved (B, owner 2026-11-15, spec FR-114-01, NFR-114-02, ADR-114-01) | 2026-11-15 | 2026-11-15 |
| ~~Q-114-02~~ | 114 | Medium | Which `JobHistory` status a skipped photo records (admin Jobs page, admin dashboard failure count) | Resolved (A, owner 2026-11-15, spec FR-114-03–05, ADR-114-01) | 2026-11-15 | 2026-11-15 |

## Question Details

### ~~Q-114-01~~ — Worker output line for a skipped duplicate

**Context.** A user importing a server directory with workers watched `docker compose logs -f` and spent an hour on `FAIL` lines that were only duplicate skips. Mechanism:
- `Create::add()` throws `PhotoSkippedException` (or its subclass `PhotoResyncedException`) from `Duplicate\ThrowSkipDuplicate` / `ThrowUntrustedDuplicate`. `ImportImageJob::handle()` and `ProcessImageJob::handle()` do not catch it.
- The Docker worker runs `queue:work --tries=3` (`docker/scripts/entrypoint.sh`). Each skip is attempted three times: `FAIL` (yellow, released after exception) twice, then `FAIL` (red). `HasFailedTrait::failed()` then logs an `error` with the full stack trace, sets `JobHistory` to `FAILURE`, and Laravel adds a `failed_jobs` row.
- Laravel's `WorkCommand::writeOutputForCli()` only knows `RUNNING`, `DONE`, `RELEASED` and `FAIL`; the status match is inline in that protected method.
- On the `sync` queue the exception must keep propagating: the upload answers `409` (front end shows "skipped") and `ExecuteBatch` reports it in the import output.

**Option A (recommended): Lychee `queue:work` prints a yellow `SKIPPED`.** The job catches the skip on an asynchronous queue, finishes normally (no retry, no `failed_jobs` row, no error trace) and flags its queue job as skipped in an in-process `WeakMap` keyed by the queue `Job` instance. A Lychee subclass of `Illuminate\Queue\Console\WorkCommand`, bound in place of the framework one, overrides `writeOutput()`: a flagged job on `success` prints `SKIPPED` (yellow) in the same layout, and `--json` output gets `"status": "skipped"`. Everything else goes to the parent.
- Pros: the Docker log says exactly what happened. No retries, no false errors.
- Cons: about 30 lines copied from Laravel's line layout (dots, run time, verbose fields), to re-check when Laravel changes `WorkCommand`. A container binding override for a framework command.

**Option B: keep Laravel's `queue:work`; a skip ends as `DONE`.** Same catch in the job (no retry, no `failed_jobs` row, no error trace). The worker line reads `DONE` (green); a `Skipped (duplicate): <file>` notice goes to the `jobs` log channel.
- Pros: no framework override, smallest change.
- Cons: the Docker log still does not say "skipped". The notice lands in `storage/logs/jobs-*.log`, which `docker compose logs` does not show.

**Option C: keep the exception, lower its noise only.** No catch; `failed()` logs a skip at `notice` without the trace.
- Pros: tiny change.
- Cons: the worker still prints `FAIL` three times per duplicate and keeps the `failed_jobs` rows. Does not fix the report.

Owner selected B on 2026-11-15. Normative outcome: spec FR-114-01, NFR-114-02, ADR-114-01.

### ~~Q-114-02~~ — `JobHistory` status for a skipped photo

**Context.** Today a skipped photo ends as `JobStatus::FAILURE`: the admin Jobs page (v7 and v8 `Jobs.vue`) counts it under "Failure", and `AdminStatsService` counts it in `failed_jobs_24h`. `JobStatus` has `READY`, `SUCCESS`, `FAILURE`, `STARTED`.

**Option A (recommended): new `JobStatus::SKIPPED = 4` (`'skipped'`).** Both jobs record it, on every queue (including `sync`). The v7 and v8 Jobs pages show it with its own label and colour (status column and meter); `failed_jobs_24h` no longer counts it. Adds `jobs.skipped` to every `lang/*/jobs.php` and widens the generated TS types.
- Pros: the history says what happened; the admin dashboard stops reporting duplicates as failures.
- Cons: touches the enum, `lychee.d.ts`, both `Jobs.vue`, and all language files.

**Option B: record `SUCCESS`.** No front-end change.
- Pros: smallest change.
- Cons: the Jobs page says "Process Image: X added to Y — success" for a photo that was not added.

**Option C: keep `FAILURE`.**
- Pros: no change.
- Cons: the Jobs page and the dashboard keep reporting duplicates as failures, the same confusion as the worker log.

Owner selected A on 2026-11-15. Normative outcome: spec FR-114-03–05, NFR-114-03, ADR-114-01.
