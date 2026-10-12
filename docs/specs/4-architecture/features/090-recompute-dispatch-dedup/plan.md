# Feature Plan 090 – Recompute Dispatch Deduplication

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Testing  
_Last updated:_ 2026-10-12

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Two uploads into one album on an asynchronous queue give `ProcessImageJob(1)`, `ProcessImageJob(2)`, then one copy of each recompute job (S-090-01). Success: N dispatches for one key push one job (NFR-090-01), a dispatch after the worker started the copy is pushed (FR-090-02), existing precompute tests stay green.

## Scope Alignment
- **In scope:** `ShouldBeUniqueUntilProcessing` + `uniqueId()` + `uniqueFor = 3600` on the seven recompute jobs (FR-090-01 … 04); removal of `DebouncesLatestJobTrait` (FR-090-05); tests; knowledge map and roadmap.
- **In scope (Q-090-05 B):** `low` queue for the seven jobs (FR-090-08), Docker/docs worker configuration (FR-090-09), Diagnostics info line (FR-090-10).
- **Out of scope:** what the jobs compute, listeners, events, `RecomputePersonStatsJob`, `sync` behaviour, other jobs' queues.

## Dependencies & Interfaces
- Laravel 13.35 `Illuminate\Bus\UniqueLock`, `PendingDispatch::shouldDispatch()`, `CallQueuedHandler` (lock released before `handle()` for `ShouldBeUniqueUntilProcessing`).
- Default cache store with lock support (`file`, `database`, `redis`, `memcached`, `array` in tests).

## Assumptions & Risks
- **Assumptions:** tests run on the `sync` connection and the `array` cache (`phpunit.xml`); the array store lives for one test.
- **Risks / Mitigations:**
  - `Queue::fake()` keeps the lock taken (nothing processes the job): a test dispatching the same job and key twice now sees one push. Mitigation: run every class asserting on these jobs (T-090-05).
  - A key that omits a constructor argument would drop a dispatch that differs in that argument. Mitigation: keys cover every constructor argument (FR-090-03), tested per job.

## Implementation Drift Gate
Run after T-090-11: map FR-090-01 … 10 and S-090-01 … 13 to tests, rerun the T-090-09 classes, record the result below.

**Report 2026-10-12** (agent):

| Requirement | Code | Tests |
|-------------|------|-------|
| FR-090-01, FR-090-03 | `ShouldBeUniqueUntilProcessing` + `uniqueId()` on the seven `app/Jobs/Recompute*Job.php` | `testRepeatedDispatchPushesOneJob`, `testDifferentTargetsPushOneJobEach`, `testPropagationFlagIsPartOfTheKey`, `testTwoUploadsIntoOneAlbumPushOneRecomputePerJob` |
| FR-090-02 | Laravel `CallQueuedHandler` (lock released before `handle()`) | `testLockIsFreeWhileTheJobRuns`, `testDispatchAfterTheJobRanIsPushed`, `testDatabaseQueueRunsOneRecomputeAfterTheUploads` |
| FR-090-04 | `$uniqueFor = 3600` | `testJobIsUniqueUntilProcessingForOneHour` |
| FR-090-05 | trait and its test deleted | `RecomputeAlbumUserThumbsJobTest` (8) |
| FR-090-06 | propagation through `self::dispatch()` | `testChildrenFinishingTogetherPushOneParentJob` |
| FR-090-07 | no delay | — (absence of delay; `testDatabaseQueueRunsOneRecomputeAfterTheUploads` pops the job right away) |
| FR-090-08 | `#[Queue(JobQueue::LOW)]`, `App\Constants\JobQueue` | `testJobIsPushedOnTheLowQueue`, `testDatabaseQueueRunsOneRecomputeAfterTheUploads` |
| FR-090-09 | `entrypoint.sh`, `deploy-worker-mode.md`, `.env.example`, Feature 002 spec | `bash -n docker/scripts/entrypoint.sh` |
| FR-090-10 | `RecomputeQueueCheck` in `Errors::$pipes` | `RecomputeQueueCheckTest` (4) |

Regression: 182 + 110 + 208 tests across the T-090-09 classes green. One existing assertion changed: `ImportFromServerTest::testImportFromServerSuccess` counted `job_count + 2` pushes; creating the import album already queues its size/stats jobs, so the import's own copies are dropped (FR-090-01) and the test now asserts `job_count` pushes, `job_count - 2` `ImportImageJob`s and both recompute jobs on `low`. `php-cs-fixer` (uncached dry run on touched files) and `make phpstan` clean. No undocumented work. Not verified: a production instance with a running worker (owner).

## Increment Map
1. **I1 – Dedup tests (red)**
   - _Goal:_ executable spec for FR-090-01 … 04, FR-090-06.
   - _Steps:_ `tests/Precomputing/Dispatch/RecomputeJobDispatchDedupTest.php` (`BasePrecomputingTest`): per job, double dispatch under `Queue::fake()` → one push; different key → two; Stats/Size flag true vs false → two; dispatch on `sync` runs and leaves the lock free; lock free inside `handle()` (listener on `AlbumComputedDataUpdated`); `uniqueFor` = 3600.
   - _Commands:_ `php artisan test --filter=RecomputeJobDispatchDedupTest` (red).
   - _Exit:_ failures only on the missing uniqueness.
2. **I2 – Album stats, size and user-thumb jobs**
   - _Steps:_ implement `ShouldBeUniqueUntilProcessing`, `uniqueId()`, `$uniqueFor`; drop the trait, `$jobId` and `forgetLatestJobMarker()` calls; delete the trait, its unit test and `testSupersededJobLogsWithItsOwnContext`.
   - _Commands:_ `php artisan test --filter=RecomputeJobDispatchDedupTest`, `--filter=RecomputeAlbumUserThumbsJobTest`.
3. **I3 – Bucket jobs**
   - _Steps:_ same contract on the four bucket jobs.
   - _Commands:_ `php artisan test --filter=RecomputeJobDispatchDedupTest` (green).
4. **I4 – Low queue (T-090-05, T-090-06)**
   - _Steps:_ red tests asserting `low` for the seven jobs and the upload path; `App\Constants\JobQueue::LOW`; `#[Queue(JobQueue::LOW)]` on each job (Laravel 13 `Dispatcher::pushCommandToQueue()` reads the attribute).
   - _Commands:_ `php artisan test --filter=RecomputeJobDispatchDedupTest`.
5. **I5 – Diagnostic (T-090-07)**
   - _Steps:_ `RecomputeQueueCheckTest` red (sync → nothing; database outside LycheeOrg image → info with command; inside → nothing; redis with custom default queue → command names it), then `App\Actions\Diagnostics\Pipes\Checks\RecomputeQueueCheck` registered in `Errors::$pipes`. LycheeOrg-image detection reuses the `docker_target`/`frankenphp_target` markers of `DockerVersionInfo`, behind a protected method the test overrides.
   - _Commands:_ `php artisan test --filter=RecomputeQueueCheckTest`.
6. **I6 – Worker configuration (T-090-08)**
   - _Steps:_ entrypoint default `QUEUE_NAMES=default,low`; `deploy-worker-mode.md` (default, compose example, bare-metal command); `.env.example` comment next to `QUEUE_CONNECTION`.
7. **I7 – Regression, quality gate, docs (T-090-09 … T-090-11)**
   - _Steps:_ regression classes; `vendor/bin/php-cs-fixer fix`; `make phpstan`; knowledge map, roadmap, session snapshot.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-090-01 | I1 / T-090-01 | listener path: `PhotoSaved` twice under `Queue::fake()` |
| S-090-02 | I1 / T-090-01 | every job |
| S-090-03 | I1 / T-090-01 | every job with an argument |
| S-090-04 | I1 / T-090-01 | Stats, Size |
| S-090-05 | I1 / T-090-01 | `sync` run, then `Queue::fake()` dispatch pushed |
| S-090-06 | I1 / T-090-01 | `AlbumComputedDataUpdated` listener acquires the key |
| S-090-07 | I1 / T-090-01 | `sync` run |
| S-090-08 | I1 / T-090-01 | every job |
| S-090-09 | I4 / T-090-05 | every job + upload path |
| S-090-10 … 13 | I5 / T-090-07 | `RecomputeQueueCheckTest` |

## Analysis Gate
2026-10-12: spec, plan, tasks agree; Q-090-01 … 05 resolved and folded into FR-090-01 … 10. No UI, no ADR needed (job-local contract, no cross-module boundary). Every FR maps to a task; tests precede code.

## Exit Criteria
- `RecomputeJobDispatchDedupTest` and the T-090-05 classes green.
- `php-cs-fixer`, `make phpstan` clean.
- Knowledge map, roadmap, worker docs updated.

## Follow-ups / Backlog
- The deployment documentation outside this repository (LycheeOrg website, linuxserver.io image) must mention `--queue=default,low`.
- Owner check on an instance with a database/redis worker: upload a batch into one album, watch the `jobs` table (`queue = 'low'` holds one row per recompute job and album).
