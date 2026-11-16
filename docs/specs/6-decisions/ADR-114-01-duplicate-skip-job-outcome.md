# ADR-114-01: Duplicate skips end queued jobs as a handled outcome

- **Status:** Accepted
- **Date:** 2026-11-15
- **Related features/specs:** Feature 114 (docs/specs/4-architecture/features/114-fix-skipped-job-status/spec.md)
- **Related open questions:** Q-114-01, Q-114-02

## Context

`Create::add()` signals a duplicate skip by throwing `PhotoSkippedException` (or `PhotoResyncedException`). `ImportImageJob` and `ProcessImageJob` let it escape `handle()`. On an asynchronous queue the worker treats it as a failure: with `--tries=3` (Docker entrypoint) every duplicate runs three times, prints `FAIL` three times, ends in `failed()` (an `error` log entry with the full trace), adds a `failed_jobs` row and records `JobStatus::FAILURE`. Users reading `docker compose logs -f` take duplicates for errors.

On the `sync` queue the same exception is the caller's signal: the upload answers `409` (front end shows "skipped") and the server import report lists the skip.

Laravel's `queue:work` output only knows `RUNNING`, `DONE`, `RELEASED` and `FAIL`, decided inline in the protected `WorkCommand::writeOutputForCli()`.

## Decision

- On an asynchronous queue (a queue job that is not a `SyncJob`), both jobs catch `PhotoSkippedException`, record the skip and return normally. The worker deletes the job after one attempt and prints Laravel's `DONE`.
- On the `sync` queue, or without a queue job, both jobs record the skip and rethrow, so the caller contract stays as it is.
- A skip records the new `JobStatus::SKIPPED` (`4`, `skipped`) in `JobHistory` on every queue; `HasFailedTrait::failed()` saves `SKIPPED` for a `PhotoSkippedException` (the `sync` rethrow path) and logs nothing.
- The skip is logged once at `info` on the `jobs` channel, without trace.
- Lychee does not override or extend Laravel's `queue:work` command.

## Consequences

### Positive
- Duplicates cost one attempt instead of `--tries`; no false `error` entries or `failed_jobs` rows.
- The admin Jobs page and the dashboard failure count report what happened.
- No dependency on Laravel's console internals.

### Negative
- The worker line reads `DONE`, not `SKIPPED`; the skip reason is only in `storage/logs/jobs-*.log` and on the Jobs page.
- The jobs need to know whether they run on the `sync` queue (`SyncJob` check).
- New enum value: `JobHistoryResource`, generated TS types, both Jobs pages and every `lang/*/jobs.php` change.

## Alternatives Considered

- **Lychee `WorkCommand` subclass printing `SKIPPED`** (Q-114-01 A): exact wording in Docker logs, but about 30 lines copied from Laravel and a framework command binding to maintain.
- **Keep the exception, drop the trace** (Q-114-01 C): smallest change; still three `FAIL` lines and `failed_jobs` rows per duplicate.
- **Record `SUCCESS`** (Q-114-02 B): no front-end change, but claims the photo was added.
- **Keep `FAILURE`** (Q-114-02 C): no change, same confusion on the Jobs page.

## Security / Privacy Impact

None. The `info` line carries the file base name, as the existing `JobHistory::job` text already does.

## Operational Impact

- Fewer worker attempts and no `failed_jobs` growth on re-imports of known directories.
- Operators looking for skips read the Jobs page or `storage/logs/jobs-*.log`.

## Links

- Related spec sections: `docs/specs/4-architecture/features/114-fix-skipped-job-status/spec.md#functional-requirements`
- Related ADRs: —
