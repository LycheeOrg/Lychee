# Feature Plan 114 – Fix Skipped Job Status

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Complete (pending operator check)  
_Last updated:_ 2026-11-15

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A directory import or an upload that meets duplicates on an asynchronous queue no longer looks like an error: one attempt per duplicate, `DONE` in the worker output, no `error` log or `failed_jobs` row, and a `skipped` entry on the admin Jobs page. The `sync` queue keeps its `409` / import-report behaviour.

## Scope Alignment
- **In scope:** `JobStatus::SKIPPED`; skip handling in `ImportImageJob` / `ProcessImageJob` and `HasFailedTrait`; `JobHistoryResource` + generated TS types; v7/v8 Jobs page label and colour; `jobs.skipped` in all languages; tests; roadmap / knowledge map.
- **Out of scope:** `queue:work` output override (ADR-114-01), duplicate detection, other job failures.

## Dependencies & Interfaces
- `App\Actions\Photo\Create` and the `Duplicate\*` pipes (unchanged; they keep throwing).
- `Illuminate\Queue\Jobs\SyncJob` (sync detection), `InteractsWithQueue::withFakeQueueInteractions()` (tests).
- `spatie/laravel-typescript-transformer` (`php artisan typescript:transform`).

## Assumptions & Risks
- **Assumptions:** On the `sync` queue the job's `$this->job` is a `SyncJob` (Laravel `SyncQueue::executeJob`); `CallQueuedHandler::failed()` re-hydrates the job, so `failed()` reads the history row saved by `handle()`.
- **Risks / Mitigations:** `typescript:transform` may reformat unrelated parts of `lychee.d.ts` → keep only the two relevant hunks. `vp check --fix` repo-wide breaks `EmbedCodeDialog.vue` → scope it to changed files.

## Implementation Drift Gate
2026-11-15, agent review after T-114-01…04, all listed commands green.

| Requirement | Code | Tests |
|-------------|------|-------|
| FR-114-01 | `HandlesSkippedPhotoTrait::skipped()`, `catch` in `ImportImageJob::handle()` / `ProcessImageJob::handle()` (return `null`) | `SkippedDuplicateJobTest` async ×3 (no exception, not failed, not released, `info` line, no `error`) |
| FR-114-02 | `callerAwaitsOutcome()` (`null` job or `SyncJob`) → rethrow | `SkippedDuplicateJobTest::testSyncUploadSkipStillConflicts`, `testSyncImportSkipStillThrows`; `UploadWithMetadataTest`, `SyncTest`, `ImportFromServerTest` unchanged |
| FR-114-03 | `JobStatus::SKIPPED`; `HasFailedTrait::failed()` skip branch | `SkippedDuplicateJobTest` (all 5), `ImportImageJobTest` `failed()` ×2, `CoverageTest` |
| FR-114-04 | v7/v8 `Jobs.vue` (`skipped` count, meter segment, `textCss`, `translateStatus`); `JobHistoryResource` literal; `lychee.d.ts` | typecheck; manual check pending |
| FR-114-05 | `AdminStatsService` query unchanged (`FAILURE` only) | `AdminStatsServiceTest::testSkippedJobsAreNotCountedAsFailures` |
| NFR-114-01 | async path returns normally | `assertNotFailed()` / `assertNotReleased()` |
| NFR-114-02 | no `WorkCommand` subclass or binding (`grep -rn WorkCommand app` empty) | — |
| NFR-114-03 | `'skipped'` in all 23 `lang/*/jobs.php` | `LangTest` |

Findings: none high/medium. Low: the upload test helper sends `file_name = tests/Samples/night.jpg`, so the skip line carries that path; the new tests pass `file_name: 'night.jpg'`. Lessons: `BaseApiWithDataTest` seeds 9 photos, so select the uploaded photo explicitly; `typescript:transform` output must go through `vp check --fix resources/js/lychee.d.ts` to keep the diff to the changed types.

## Increment Map
1. **I1 – Failing tests**
   - _Steps:_ `SkippedDuplicateJobTest` (S-114-01…05), `ImportImageJobTest` `failed()` cases (S-114-06), `CoverageTest` `skipped` name, `AdminStatsServiceTest` (S-114-07). Run, confirm red.
   - _Commands:_ `php artisan test --filter=SkippedDuplicateJobTest`, `--filter=ImportImageJobTest`, `--filter=CoverageTest`, `--filter=AdminStatsServiceTest`.
2. **I2 – Backend**
   - _Steps:_ `JobStatus::SKIPPED`; `HandlesSkippedPhotoTrait::skipped()` (record, log, rethrow on sync); catch in both jobs; `HasFailedTrait::failed()` branch; `JobHistoryResource` literal; regenerate TS types.
   - _Commands:_ the four test filters above, `UploadWithMetadataTest`, `PhotoAddTest`, `ProcessImageJobTest`, `JobsTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
3. **I3 – Frontend + translations**
   - _Steps:_ v7/v8 `Jobs.vue` label, colour, meter segment; `jobs.skipped` in `lang/*/jobs.php`.
   - _Commands:_ `php artisan test --filter=LangTest`, `vp check --fix <changed files>`, `vp run typecheck`.
4. **I4 – Docs**
   - _Steps:_ knowledge map, roadmap, drift gate.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-114-01 | I1, I2 / T-114-01, T-114-03 | `SkippedDuplicateJobTest` |
| S-114-02 | I1, I2 / T-114-01, T-114-03 | `SkippedDuplicateJobTest` |
| S-114-03 | I1, I2 / T-114-01, T-114-03 | `SkippedDuplicateJobTest` |
| S-114-04 | I1, I2 / T-114-01, T-114-03 | `SkippedDuplicateJobTest`, `UploadWithMetadataTest` |
| S-114-05 | I1, I2 / T-114-01, T-114-03 | `SkippedDuplicateJobTest` |
| S-114-06 | I1, I2 / T-114-02, T-114-03 | `ImportImageJobTest` |
| S-114-07 | I1, I2 / T-114-02, T-114-03 | `AdminStatsServiceTest` |
| S-114-08 | I3 / T-114-04 | typecheck + manual |

## Analysis Gate
2026-11-15, agent review:
1. Spec completeness — pass: FR/NFR filled, Q-114-01/02 folded in, ASCII mock-up present.
2. Open questions — pass: none open; ADR-114-01 written.
3. Plan alignment — pass: links and success criteria match the spec.
4. Tasks coverage — pass: FR-114-01…05 map to T-114-01…04; tests (T-114-01/02) precede code (T-114-03/04); success (async skip), validation (sync rethrow) and failure (other exception) branches listed.
5. Working agreements — pass: no dependency change; skip handling isolated in one small trait method, jobs get a single `catch`.
6. Tooling — pass: commands listed per increment.

## Exit Criteria
- All tasks `[x]`; test filters above green; `php-cs-fixer`, `make phpstan`, `vp check` (changed files), `vp run typecheck` clean.
- `lychee.d.ts` regenerated; roadmap and knowledge map updated; drift gate recorded.

## Intent Log
- 2026-11-15: user report (Docker `queue:work` shows `FAIL` for duplicate skips during a directory import). Root cause traced through `Create::handleDuplicate()`, `ThrowSkipDuplicate`, `HasFailedTrait`, `WorkCommand::writeOutputForCli()`, `SyncQueue::executeJob()`, `docker/scripts/entrypoint.sh`. Owner chose Q-114-01 B, Q-114-02 A.

## Follow-ups / Backlog
- None yet.
