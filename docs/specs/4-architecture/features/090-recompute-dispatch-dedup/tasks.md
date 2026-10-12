# Feature 090 Tasks – Recompute Dispatch Deduplication

_Status: In progress_  
_Last updated: 2026-10-12_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-090-01 – Dedup tests, red (FR-090-01, FR-090-02, FR-090-03, FR-090-04, FR-090-06, S-090-01 … S-090-08).  
  _Intent:_ `tests/Precomputing/Dispatch/RecomputeJobDispatchDedupTest.php`.  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeJobDispatchDedupTest` (red on missing uniqueness only)

- [x] T-090-02 – `RecomputeAlbumStatsJob`, `RecomputeAlbumSizeJob` unique, trait removed (FR-090-01, FR-090-03, FR-090-04, FR-090-05).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeJobDispatchDedupTest` (stats and size cases green)

- [x] T-090-03 – `RecomputeAlbumUserThumbsJob` unique; delete `DebouncesLatestJobTrait`, `DebouncesLatestJobTraitTest`, `testSupersededJobLogsWithItsOwnContext` (FR-090-01, FR-090-03, FR-090-05).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeJobDispatchDedupTest`  
  - `php artisan test --filter=RecomputeAlbumUserThumbsJobTest`

- [x] T-090-04 – Four bucket jobs unique (FR-090-01, FR-090-03, FR-090-04).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeJobDispatchDedupTest` (green)

- [x] T-090-05 – Low-queue tests, red (FR-090-08, S-090-09).  
  _Intent:_ extend `RecomputeJobDispatchDedupTest`: each recompute job pushed on `low`; the upload path pushes the recompute jobs on `low`.  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeJobDispatchDedupTest` (red on the queue name only)

- [x] T-090-06 – `App\Constants\JobQueue` + `#[Queue(JobQueue::LOW)]` on the seven jobs (FR-090-08).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeJobDispatchDedupTest` (green)

- [x] T-090-07 – `RecomputeQueueCheck` diagnostic, test first (FR-090-10, S-090-10 … S-090-13).  
  _Intent:_ `tests/Unit/Actions/Diagnostics/RecomputeQueueCheckTest.php` red, then the check, registered in `Errors`.  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeQueueCheckTest`

- [x] T-090-08 – Docker entrypoint default `QUEUE_NAMES=default,low`, `deploy-worker-mode.md`, `.env.example` (FR-090-09).  
  _Verification commands:_  
  - `bash -n docker/scripts/entrypoint.sh`

- [x] T-090-09 – Regression classes green.  
  _Verification commands (one at a time):_ `EventListenersTest`, `EventPropagationIntegrationTest`, `RecomputeAlbumStatsJobTest`, `RecomputeAlbumSizeJobTest`, `AlbumSizeEventListenerTest`, `AlbumSizePropagationTest`, `FulfillPreComputeTest`, `RecomputeAlbumStatsCommandTest`, `PhotoAddTest`, `ImportFromServerTest`, `RecomputeAlbumUserThumbsJobTest`, `RecomputePhotoBucketsJobTest`, `RecomputeAlbumPhotoBucketsJobTest`, `RecomputeChildAlbumBucketsJobTest`, `RecomputeRootAlbumBucketsJobTest`, `PhotoSortingBucketDispatchTest`, `AlbumSortingBucketDispatchTest`, `UpdateSettingsTest`, `DeepNestingPropagationTest`, `CoverSelectionNsfwTest`, `DiagnosticsTest` classes.

- [x] T-090-10 – Quality gate.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-090-11 – Docs: knowledge map Jobs section, roadmap row, session snapshot.

- [x] T-090-12 – Diagnostic checks `QUEUE_NAMES` inside LycheeOrg images, test first (FR-090-10, S-090-12, S-090-14, S-090-15).  
  _Intent:_ `RecomputeQueueCheckTest` cases red, then `config('queue.worker_queues')` and the warning branch; `deploy-worker-mode.md` tells to set `QUEUE_NAMES` in the shared environment.  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeQueueCheckTest`  
  - `vendor/bin/php-cs-fixer fix`, `make phpstan`

- [x] T-090-13 – Lock-store tests, red (FR-090-11, S-090-16 … S-090-18).  
  _Intent:_ `RecomputeJobDispatchDedupTest`: `uniqueVia()` per job on `database` and `sync`; end-to-end test counts `cache_locks` rows.  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeJobDispatchDedupTest` (red)

- [x] T-090-14 – Migration `create_cache_tables` + `HasSharedUniqueLockTrait` on the seven jobs (FR-090-11, NFR-090-02).  
  _Verification commands:_  
  - `php artisan test --filter=RecomputeJobDispatchDedupTest` (green)

- [x] T-090-15 – `WorkerCacheCheck`, test first (FR-090-13, S-090-19 … S-090-21).  
  _Verification commands:_  
  - `php artisan test --filter=WorkerCacheCheckTest`

- [x] T-090-16 – Compose `CACHE_DRIVER`, `deploy-worker-mode.md` worker cache section, `.env.example` (FR-090-12).

- [ ] T-090-17 – Regression, quality gate, ADR-090-01, knowledge map, roadmap, drift gate.  
  _Verification commands:_  
  - regression classes of T-090-09 (one command at a time)  
  - `vendor/bin/php-cs-fixer fix`, `make phpstan`

## Notes / TODOs
- Never run two test commands at once (shared SQLite test database).
