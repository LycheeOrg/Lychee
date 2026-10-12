# ADR-090-01: Unique Job Locks in the Database, Shared Cache for Worker Containers

- **Status:** Accepted
- **Date:** 2026-10-12
- **Related features/specs:** Feature 090 (docs/specs/4-architecture/features/090-recompute-dispatch-dedup/spec.md), Feature 002 (docs/specs/4-architecture/features/002-worker-mode/spec.md)
- **Related open questions:** Q-090-06

## Context

Feature 090 makes the seven recompute jobs `ShouldBeUniqueUntilProcessing`. Laravel takes the unique lock in the dispatching process and releases it in the process that starts the job, both through the default cache store unless the job names another one (`uniqueVia()`).

The LycheeOrg Docker setup runs the web server and the queue worker in two containers. With the `file` cache, each container has its own `storage/framework/cache` (not a shared volume). A lock kept there is taken in the web container and released in the worker's cache, where it does not exist: every later dispatch for the same album or photo is dropped until the lock expires (one hour), even after the recompute ran. The same split cache keeps the web container's caches stale when a job run by the worker evicts them. `CACHE_DRIVER=database` needs the `cache`/`cache_locks` tables.

## Decision

1. Migration `2026_10_12_000000_create_cache_tables` creates Laravel's `cache` and `cache_locks` tables.
2. The seven recompute jobs use `App\Jobs\Traits\HasSharedUniqueLockTrait`: `uniqueVia()` returns the `database` cache store when `queue.default` is not `sync`, the default store otherwise. Web and worker always share the database, so the lock taken on dispatch is the one the worker releases.
3. A separate worker container needs a cache store shared with the web container: the compose file sets `CACHE_DRIVER`, `deploy-worker-mode.md` and `.env.example` recommend `database` or `redis`, and Diagnostics (`WorkerCacheCheck`) warn inside a LycheeOrg image when a non-`sync` queue runs on a `file`, `array` or `apc` cache.

## Consequences

### Positive
- Deduplication works across containers whatever the cache driver.
- `CACHE_DRIVER=database` is usable.
- Docker administrators are told when their cache is split between containers.

### Negative
- On asynchronous queues each dispatched recompute job costs one insert (and one delete when a worker starts it) in `cache_locks`.
- One more schema change.
- The default compose file (database queue, worker, `file` cache) shows the Diagnostics warning until `CACHE_DRIVER` is changed.

## Alternatives Considered

- **Shared cache only (no database locks):** the default Docker setup keeps dropping updates until reconfigured.
- **Shorter lock lifetime:** bounds the loss without removing it and shrinks the deduplication window.
- **Database locks on every queue connection:** two extra queries per dispatch on `sync`, where the lock is taken and released in the same process.

## Security / Privacy Impact

- None: lock rows hold a key derived from the job class and target id, and a random owner token.

## Operational Impact

- New tables `cache` and `cache_locks`; expired lock rows are pruned by Laravel's lock lottery.
- The admin "Flush cache" action does not clear `cache_locks`; a lost lock expires after one hour.
- Worker deployments set `CACHE_DRIVER=database` or `CACHE_DRIVER=redis` for both containers.

## Links

- Related spec sections: `docs/specs/4-architecture/features/090-recompute-dispatch-dedup/spec.md#functional-requirements` (FR-090-11 … FR-090-13, NFR-090-02)
- Related how-to: `docs/specs/2-how-to/deploy-worker-mode.md`
