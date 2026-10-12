# Feature Plan 090 – Recompute Dispatch Deduplication

_Linked specification:_ [spec.md](spec.md)  
_Status:_ In progress  
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
- Laravel 13.35 `Illuminate\Cache\DatabaseStore` / `DatabaseLock` (constructor and acquisition mirrored by `TransactionSafeDatabaseStore` / `TransactionSafeDatabaseLock`, FR-090-14).

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
| FR-090-10 | `RecomputeQueueCheck` in `Errors::$pipes`, `config('queue.worker_queues')` | `RecomputeQueueCheckTest` (9) |

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

8. **I8 – PR #4864 review (T-090-12, T-090-13)**
   - _Steps:_ owner directive: inside a LycheeOrg image the diagnostic checks `QUEUE_NAMES` (`config('queue.worker_queues')`) for `low` (tests first); how-to tells to set `QUEUE_NAMES` in the shared `.env`; stale knowledge-map note in the spec fixed. Lock store across containers blocked on Q-090-06.
   - _Commands:_ `php artisan test --filter=RecomputeQueueCheckTest`, `--filter='ErrorsTest|InfoTest'`, php-cs-fixer, `make phpstan`.

9. **I9 – Shared lock store and worker cache (T-090-13 … T-090-17, Q-090-06 A + B)**
   - _Steps:_ red tests on `uniqueVia()` and `cache_locks`; migration with Laravel's `cache`/`cache_locks` stub; `HasSharedUniqueLockTrait` (`database` store when `queue.default` is not `sync`); `WorkerCacheCheck` (test first); compose `CACHE_STORE`; worker-cache docs; ADR-090-01.
   - _Approval:_ the schema change (two new tables) was approved by the owner on 2026-10-12 by choosing Q-090-06 A.
   - _Commands:_ `php artisan test --filter=RecomputeJobDispatchDedupTest`, `--filter=WorkerCacheCheckTest`, regression classes, php-cs-fixer, `make phpstan`.

10. **I10 – Transaction-safe database locks (T-090-18, T-090-19)**
   - _Steps:_ red test dispatching one key twice inside `DB::transaction` on the `database` queue (red on PostgreSQL only); `TransactionSafeDatabaseLock` (`insertOrIgnore`, then Laravel's owner/expiry update) and `TransactionSafeDatabaseStore`; `Cache::extend('database', …)` in `AppServiceProvider` with Laravel's configuration keys; FR-090-14, S-090-22, ADR-090-01 decision 3.
   - _Commands:_ `php artisan test --filter=RecomputeJobDispatchDedupTest` on SQLite and with `DB_CONNECTION=pgsql DB_HOST=127.0.0.1 DB_PORT=<port>` against a throwaway `postgres:16` container (migrated with `php artisan migrate --force` under the same variables), `--filter=WorkerCacheCheckTest`, php-cs-fixer, `make phpstan`.

11. **I11 – `high` queue name (T-090-21, T-090-22, Q-090-08 C)**
   - _Steps:_ red diagnostic tests on the `high,<default>,low` command and the `high,default,low` default; `JobQueue::HIGH` (no job pushed on it); `RecomputeQueueCheck` messages; entrypoint default `QUEUE_NAMES=high,default,low`; `config/queue.php`, `.env.example`, `deploy-worker-mode.md`, Feature 002 spec, knowledge map. The warning still fires only without `low`.
   - _Commands:_ `php artisan test --filter=RecomputeQueueCheckTest`, `--filter=RecomputeJobDispatchDedupTest`, `bash -n docker/scripts/entrypoint.sh`, php-cs-fixer, `make phpstan`.

12. **I12 – `CACHE_STORE` with `CACHE_DRIVER` fallback (T-090-23, T-090-24, Q-090-07 D + fallback)**
   - _Steps:_ red `CacheStoreEnvTest` (both config files, three environments) and `WorkerCacheCheckTest` advice; `config/cache.php` `env('CACHE_STORE', env('CACHE_DRIVER', 'file'))` (`config/data.php` already reads it so); compose `CACHE_STORE: "${CACHE_STORE:-${CACHE_DRIVER:-file}}"`; `.env.example`, `phpunit*.xml`, CI env files, docs, ADR-090-01 decision 4.
   - _Approval:_ the `CACHE_DRIVER` fallback was requested by the owner on 2026-10-12 (Q-090-07), overriding the greenfield stance for this variable.
   - _Commands:_ `php artisan test --filter='CacheStoreEnvTest|WorkerCacheCheckTest'`, `docker compose --env-file /dev/null config`, php-cs-fixer, `make phpstan`.

## Analysis Gate
2026-10-12: spec, plan, tasks agree; Q-090-01 … 05 resolved and folded into FR-090-01 … 10. No UI, no ADR needed (job-local contract, no cross-module boundary). Every FR maps to a task; tests precede code.

## Exit Criteria
- `RecomputeJobDispatchDedupTest` and the T-090-05 classes green.
- `php-cs-fixer`, `make phpstan` clean.
- Knowledge map, roadmap, worker docs updated.

## Follow-ups / Backlog
- The deployment documentation outside this repository (LycheeOrg website, linuxserver.io image) must mention `--queue=high,default,low`.
- Owner check on an instance with a database/redis worker: upload a batch into one album, watch the `jobs` table (`queue = 'low'` holds one row per recompute job and album).
