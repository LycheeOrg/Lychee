# Feature 090 – Recompute Dispatch Deduplication

| Field | Value |
|-------|-------|
| Status | Testing |
| Last updated | 2026-10-12 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #090 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Event listeners dispatch the precompute jobs (`RecomputeAlbumStatsJob`, `RecomputeAlbumSizeJob`, `RecomputeAlbumUserThumbsJob`) after every photo or album change. An upload of N photos into one album ends each `ProcessImageJob` with a `PhotoSaved` event (`SetParent` pipe), so N copies of each recompute job are pushed; `DebouncesLatestJobTrait` lets only the newest copy run, but every copy sits in the queue until a worker pops it and skips it. On an asynchronous queue (database, redis, …) the queue grows with jobs that do nothing.

This feature deduplicates at dispatch time: a recompute job is pushed only when no copy for the same target is already waiting in the queue. The same rule covers the four bucket recompute jobs (`RecomputePhotoBucketsJob`, `RecomputeAlbumPhotoBucketsJob`, `RecomputeChildAlbumBucketsJob`, `RecomputeRootAlbumBucketsJob`). Two uploads into album A give `ProcessImageJob(1)`, `ProcessImageJob(2)`, one recompute per job class. The recompute jobs also go on a `low` queue, which workers drain after `default`, so they run after the waiting uploads. Backend and worker configuration only: listeners, events, REST API, UI and schema do not change. The default `sync` queue runs every dispatch immediately, as before.

## Goals
- At most one waiting copy per recompute job class and target in the queue.
- A recompute always runs after the last change to its target: no change is left uncomputed.
- The jobs compute the same values as before.

## Non-Goals
- What the jobs compute, and when listeners fire.
- `RecomputePersonStatsJob` (array of person ids, dispatched with `dispatchSync` or inside the photo-delete job list).
- Merging jobs across targets (one job for several albums).
- Behaviour on the `sync` queue.
- Moving any other job to another queue.
- The lock store of `WatermarkerJob` and `EmbedMetadataJob`.
- The deployment documentation outside this repository.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-090-01 | The seven target-keyed recompute jobs (`RecomputeAlbumStatsJob`, `RecomputeAlbumSizeJob`, `RecomputeAlbumUserThumbsJob`, `RecomputePhotoBucketsJob`, `RecomputeAlbumPhotoBucketsJob`, `RecomputeChildAlbumBucketsJob`, `RecomputeRootAlbumBucketsJob`) implement `Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing`. A dispatch made while a copy with the same unique key waits in the queue is not pushed. | First dispatch acquires the unique lock and is pushed; later dispatches for the same key are dropped until a worker starts the waiting copy. | — | Cache store without lock support: Laravel raises on dispatch (same constraint as `WatermarkerJob`/`EmbedMetadataJob`). | Laravel fires `Illuminate\Queue\Events\UniqueJobSkipped` for each dropped dispatch; Lychee logs nothing. | Owner directive 2026-10-12, Q-090-02 A |
| FR-090-02 | The unique lock is released when a worker starts the job, before `handle()` reads the database. A dispatch after that point is pushed. | Change during or after the run → a new copy is queued and covers it. | — | Job fails or is retried: the lock is already released, later dispatches are pushed. | — | Owner directive 2026-10-12 |
| FR-090-03 | Unique keys (`uniqueId()`): `RecomputeAlbumStatsJob` and `RecomputeAlbumSizeJob` → `album_id` + `propagate_to_parent`; `RecomputeAlbumUserThumbsJob` → `album_kind` + `album_id`; `RecomputePhotoBucketsJob` → `photo_id`; `RecomputeAlbumPhotoBucketsJob` → `album_id`; `RecomputeChildAlbumBucketsJob` → `parent_album_id`; `RecomputeRootAlbumBucketsJob` → one constant key. | Same album, both flags → two copies may wait: a waiting maintenance copy (`propagate_to_parent = false`) never swallows an event-driven copy that must update the ancestors. | — | — | — | Q-090-02 A |
| FR-090-04 | Lock lifetime `uniqueFor` = 3600 s. After it expires, the next dispatch is pushed even if a copy still waits. | Backlog longer than one hour: at most one extra copy per hour and key. | — | A waiting copy lost from the queue (cleared queue, evicted redis) blocks that key's dispatches for at most one hour, or until the cache is flushed. | — | Q-090-03 A |
| FR-090-05 | `DebouncesLatestJobTrait` and its tests are deleted; the three jobs run `handle()` without a "newer job" skip. | — | — | — | — | Q-090-04 A. The trait registers its marker in the constructor: a dispatch dropped by the lock would still mark the waiting copy as superseded, and that copy would skip itself. |
| FR-090-06 | Propagation dispatches (`RecomputeAlbumStatsJob`/`RecomputeAlbumSizeJob` dispatching themselves for the parent) follow FR-090-01: N children finishing while the parent's copy waits give one parent recompute. | — | — | — | — | Derived from FR-090-01 |
| FR-090-07 | No delay: the waiting copy is pushed without delay and runs when a worker reaches it. | — | — | — | — | Q-090-01 A |
| FR-090-08 | The seven recompute jobs are pushed on the `low` queue (`#[Queue(JobQueue::LOW)]`, `App\Constants\JobQueue::LOW = 'low'`). Every other job stays on the connection's default queue. | A worker started with `--queue=default,low` runs them once `default` is empty: after every waiting upload/import, so with FR-090-01 a backlog ends with one recompute per target. | — | A worker that does not list `low` never runs them: stats, covers, sizes and buckets stay stale (FR-090-10 informs the admin). Uploads that never stop delay recomputes until `default` drains. | — | Q-090-05 B |
| FR-090-09 | The Docker entrypoint (`docker/scripts/entrypoint.sh`, both LycheeOrg images) defaults `QUEUE_NAMES` to `default,low`; `deploy-worker-mode.md` and `.env.example` document `low`. | Docker worker without `QUEUE_NAMES` drains `default` then `low`. | — | `QUEUE_NAMES` set explicitly without `low`: recomputes never run. | Entrypoint prints the queue names on start. | Q-090-05 B |
| FR-090-10 | Diagnostics check `RecomputeQueueCheck`, only when the default queue connection is not `sync`. Outside a LycheeOrg image (no `docker_target`/`frankenphp_target` marker: bare metal, linuxserver.io, custom images): info line saying recompute jobs use the `low` queue, with the worker command `php artisan queue:work --queue=<default queue of the connection>,low`. Inside a LycheeOrg image: `QUEUE_NAMES` (read through `config('queue.worker_queues')`) is checked; when it is set and its comma-separated names do not include `low`, a warning says recompute jobs never run and gives `QUEUE_NAMES=<value>,low`. | `sync` → no line. LycheeOrg image with `QUEUE_NAMES` unset (entrypoint default `default,low`) or including `low` → no line. | Names are trimmed before comparison. | `QUEUE_NAMES` set only on the worker service is not visible to the web container where Diagnostics run: no line (the how-to tells to set it in the shared environment). | Diagnostics info / warning line. | Owner directive 2026-10-12 (Q-090-05), owner directive 2026-10-12 on the PR #4864 review |
| FR-090-11 | On an asynchronous queue (`queue.default` not `sync`) the unique locks of the seven jobs live in the `database` cache store (`uniqueVia()`, `App\Jobs\Traits\HasSharedUniqueLockTrait`); on `sync` they live in the default cache store. | The web container takes the lock on dispatch and the worker releases it in the same `cache_locks` table, whatever `CACHE_DRIVER` is. | — | — | — | Q-090-06 A, ADR-090-01 |
| FR-090-12 | The Docker compose file sets `CACHE_DRIVER` (the variable `config/cache.php` reads). `deploy-worker-mode.md` and `.env.example` state that a separate worker container needs a cache store shared with the web container (`database` or `redis`). | — | — | — | — | Q-090-06 B, ADR-090-01 |
| FR-090-13 | Diagnostics check `WorkerCacheCheck`: inside a LycheeOrg image, with a queue connection other than `sync` and a default cache store whose driver is `file`, `array` or `apc`, a warning says the cache is not shared with the worker container and recommends `CACHE_DRIVER=database` or `CACHE_DRIVER=redis`. | `sync`, outside a LycheeOrg image (one host, one cache directory), or a `database`/`redis`/`memcached`/`dynamodb` cache → no line. | — | — | Diagnostics warning line. | Q-090-06 B |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-090-01 | Queue size per recompute job class and key is bounded by one waiting copy (within the lock lifetime). | Queue growth on asynchronous queues. | Test: N dispatches for one key under `Queue::fake()` → one pushed. | Laravel `UniqueLock` | Owner directive 2026-10-12 |
| NFR-090-02 | No new dependency. One schema change: Laravel's `cache` and `cache_locks` tables (migration `2026_10_12_000000_create_cache_tables`), used by the lock store of FR-090-11 and by `CACHE_DRIVER=database`. | Offline-only, no new services; locks shared across containers. | Review; `testDatabaseQueueRunsOneRecomputeAfterTheUploads` reads `cache_locks`. | `config/cache.php` `database` store | AGENTS.md (dependencies, escalation policy: owner approved the schema change with Q-090-06 A) |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-090-01 | Two uploads into album A on an asynchronous queue, both `ProcessImageJob`s waiting: one `RecomputeAlbumStatsJob(A)`, one `RecomputeAlbumSizeJob(A)`, one `RecomputeAlbumUserThumbsJob` per smart album type are pushed, behind the second upload. |
| S-090-02 | Same job and key dispatched N times while the first copy waits: one pushed. |
| S-090-03 | Different targets: one copy each. |
| S-090-04 | `RecomputeAlbumStatsJob(A, true)` and `RecomputeAlbumStatsJob(A, false)`: two copies. |
| S-090-05 | A dispatch after the waiting copy started (lock released): pushed. |
| S-090-06 | The lock is free while `handle()` runs (checked from inside the run). |
| S-090-07 | `sync` queue: every dispatch runs immediately; the lock is free afterwards. |
| S-090-08 | Each job declares `uniqueFor = 3600`. |
| S-090-09 | Each recompute job is pushed on the `low` queue, also from the upload path (`PhotoSaved`). |
| S-090-10 | Diagnostics, `sync` connection: no recompute-queue line. |
| S-090-11 | Diagnostics, `database` connection outside a LycheeOrg image: info line with `php artisan queue:work --queue=default,low`. |
| S-090-12 | Diagnostics, `database` connection inside a LycheeOrg image, `QUEUE_NAMES` unset or including `low`: no line. |
| S-090-13 | Diagnostics, `redis` connection with `REDIS_QUEUE=lychee`: the command names `--queue=lychee,low`. |
| S-090-14 | Diagnostics, `database` connection inside a LycheeOrg image, `QUEUE_NAMES=default`: warning with `QUEUE_NAMES=default,low`. |
| S-090-15 | Diagnostics, `sync` connection inside a LycheeOrg image with `QUEUE_NAMES=default`: no line. |
| S-090-16 | Asynchronous queue: each job's `uniqueVia()` is the `database` store. |
| S-090-17 | `sync` queue: each job's `uniqueVia()` is the default store. |
| S-090-18 | Database queue end to end: one `cache_locks` row while the recompute waits, none after the worker started it. |
| S-090-19 | Diagnostics, LycheeOrg image, `database` queue, `file` cache: warning. |
| S-090-20 | Diagnostics, LycheeOrg image, `database` queue, `database` or `redis` cache: no line. |
| S-090-21 | Diagnostics, `sync` queue or outside a LycheeOrg image, `file` cache: no line. |

## Test Strategy
- **Jobs (Precomputing suite, `BasePrecomputingTest`):** new `RecomputeJobDispatchDedupTest` covering S-090-02 … S-090-08 for each of the seven jobs, under `Queue::fake()` (the lock is taken on dispatch and never released, so a second dispatch is visibly dropped) and on the `sync` connection (lock released by processing). Written first, red before the jobs change.
- **Unit:** `DebouncesLatestJobTraitTest` and `RecomputeAlbumUserThumbsJobTest::testSupersededJobLogsWithItsOwnContext` are deleted with the trait (Q-090-04 A).
- **End to end:** `RecomputeJobDispatchDedupTest::testDatabaseQueueRunsOneRecomputeAfterTheUploads` pushes on the real `database` driver (SQLite test database) and drains it with `queue:work --once --queue=default,low` (S-090-01, S-090-05, S-090-09).
- **Regression:** every existing class asserting on these jobs (`EventListenersTest`, `EventPropagationIntegrationTest`, `RecomputeAlbumStatsJobTest`, `RecomputeAlbumSizeJobTest`, `AlbumSizeEventListenerTest`, `AlbumSizePropagationTest`, `FulfillPreComputeTest`, `RecomputeAlbumStatsCommandTest`, `PhotoAddTest`, `RecomputeAlbumUserThumbsJobTest`, `RecomputePhotoBucketsJobTest`, `RecomputeAlbumPhotoBucketsJobTest`, `RecomputeChildAlbumBucketsJobTest`, `RecomputeRootAlbumBucketsJobTest`, `PhotoSortingBucketDispatchTest`, `AlbumSortingBucketDispatchTest`, `UpdateSettingsTest`).
- **Diagnostics (Unit):** `RecomputeQueueCheckTest` covers S-090-10 … S-090-15; the LycheeOrg-image detection is overridden in the test (the marker files live in the image root).
- **Lock store:** `RecomputeJobDispatchDedupTest` covers S-090-16 … S-090-18 (the migration is exercised through the lock rows, not tested on its own).
- **Diagnostics (Unit):** `WorkerCacheCheckTest` covers S-090-19 … S-090-21.
- **REST / CLI / Frontend:** no change.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-090-01 | Unique key per recompute job (FR-090-03). | `app/Jobs/Recompute*Job.php` |
| DO-090-02 | Tables `cache` (`key` primary, `value` mediumText, `expiration` bigint indexed) and `cache_locks` (`key` primary, `owner`, `expiration` bigint indexed), Laravel's stub. | `database/migrations/2026_10_12_000000_create_cache_tables.php`, `config/cache.php` |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-090-01 | `php artisan queue:work --queue=default,low` | Worker drains `default`, then `low` (recompute jobs). |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-090-01 | `Illuminate\Queue\Events\UniqueJobSkipped` (Laravel, not listened to) | job instance |

## Telemetry & Observability
Dropped dispatches are not logged. Diagnostics carry the worker-cache warning line (FR-090-13). The `jobs` log channel keeps the "Recomputing … for album …" line at the start of each run. Diagnostics carry the recompute-queue info line (FR-090-10).

## Documentation Deliverables
- Roadmap row 090.
- `docker/scripts/entrypoint.sh` default `QUEUE_NAMES`, `docs/specs/2-how-to/deploy-worker-mode.md` (set `QUEUE_NAMES` in the shared environment so Diagnostics can check it), `.env.example`, Feature 002 spec (`QUEUE_NAMES` default) (FR-090-09).
- `config/queue.php` `worker_queues` (reads `QUEUE_NAMES`) (FR-090-10).
- `docker-compose.yaml` `CACHE_DRIVER`, worker cache section in `deploy-worker-mode.md`, `.env.example` (FR-090-12).
- ADR-090-01 (lock store and shared cache for workers).
- Knowledge map: Jobs section (uniqueness and `low` queue of the recompute jobs, propagation notes under `RecomputeAlbumStatsJob`), Worker Mode `QUEUE_NAMES` default.

## Fixtures & Sample Data
None.

## Spec DSL

```
domain_objects:
  - id: DO-090-01
    name: RecomputeJobUniqueKey
    fields:
      - name: RecomputeAlbumStatsJob
        type: string
        constraints: "album_id + propagate_to_parent"
      - name: RecomputeAlbumSizeJob
        type: string
        constraints: "album_id + propagate_to_parent"
      - name: RecomputeAlbumUserThumbsJob
        type: string
        constraints: "album_kind + album_id"
      - name: RecomputePhotoBucketsJob
        type: string
        constraints: "photo_id"
      - name: RecomputeAlbumPhotoBucketsJob
        type: string
        constraints: "album_id"
      - name: RecomputeChildAlbumBucketsJob
        type: string
        constraints: "parent_album_id"
      - name: RecomputeRootAlbumBucketsJob
        type: string
        constraints: "constant"
      - name: uniqueFor
        type: integer
        constraints: "3600"
cli_commands:
  - id: CLI-090-01
    command: php artisan queue:work --queue=default,low
telemetry_events:
  - id: TE-090-01
    event: Illuminate\Queue\Events\UniqueJobSkipped
```

## Appendix
- Laravel 13.35 `PendingDispatch::shouldDispatch()` takes the lock for every `ShouldBeUnique` job (also under `Queue::fake()`/`Bus::fake()`); `CallQueuedHandler::dispatchThroughMiddleware()` releases it for `ShouldBeUniqueUntilProcessing` before calling `handle()`.
- `#[DebounceFor]` (Laravel 13) pushes every dispatch with a delay and deletes superseded copies when a worker pops them: one queue entry per dispatch.
- `ShouldBeUnique` (lock until the job finishes) drops dispatches made while the job runs, after it read the database: those changes would stay uncomputed.
- `dispatchSync` releases the lock of a waiting copy with the same key (force release, no owner): at most one extra copy can then be pushed.
- `ApcStore` has no lock support; the admin "Flush cache" action clears the locks of the default store, not the `cache_locks` rows.
- The LycheeOrg compose file mounts `uploads`, `logs` and `tmp` only: `storage/framework/cache` is per container.
