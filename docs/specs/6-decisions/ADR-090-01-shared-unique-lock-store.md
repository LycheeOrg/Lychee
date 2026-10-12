# ADR-090-01: Unique Job Locks in the Database, Shared Cache for Worker Containers

- **Status:** Accepted
- **Date:** 2026-10-12
- **Related features/specs:** Feature 090 (docs/specs/4-architecture/features/090-recompute-dispatch-dedup/spec.md), Feature 002 (docs/specs/4-architecture/features/002-worker-mode/spec.md)
- **Related open questions:** Q-090-06, Q-090-07

## Context

Feature 090 makes the seven recompute jobs `ShouldBeUniqueUntilProcessing`. Laravel takes the unique lock in the dispatching process and releases it in the process that starts the job, both through the default cache store unless the job names another one (`uniqueVia()`).

The LycheeOrg Docker setup runs the web server and the queue worker in two containers. With the `file` cache, each container has its own `storage/framework/cache` (not a shared volume). A lock kept there is taken in the web container and released in the worker's cache, where it does not exist: every later dispatch for the same album or photo is dropped until the lock expires (one hour), even after the recompute ran. The same split cache keeps the web container's caches stale when a job run by the worker evicts them. `CACHE_STORE=database` needs the `cache`/`cache_locks` tables.

Laravel's `DatabaseLock` takes a lock by inserting its row and, when the key exists, catching the unique-constraint violation and updating the row. PostgreSQL aborts the open transaction on that violation, so the update fails (`SQLSTATE[25P02]`) and so does the rest of the transaction. Recompute jobs are dispatched inside transactions (`AlbumSaved` in `BulkAlbumController::patch`), and a dispatch while a copy waits is exactly the case where the key exists.

## Decision

1. Migration `2026_10_12_000000_create_cache_tables` creates Laravel's `cache` and `cache_locks` tables.
2. The seven recompute jobs use `App\Jobs\Traits\HasSharedUniqueLockTrait`: `uniqueVia()` returns the `database` cache store when `queue.default` is not `sync`, the default store otherwise. Web and worker always share the database, so the lock taken on dispatch is the one the worker releases.
3. The `database` cache driver is `App\Services\Cache\TransactionSafeDatabaseStore`, registered in `AppServiceProvider` with Laravel's configuration keys. Its `TransactionSafeDatabaseLock` inserts with `insertOrIgnore` and falls back to Laravel's owner/expiry update: no statement of a lock acquisition fails, so locks can be taken inside a transaction on every supported database. This covers every lock of the `database` store, not only the recompute jobs.
4. `CACHE_STORE` names the cache store everywhere: `config/cache.php` and `config/data.php` read `env('CACHE_STORE', env('CACHE_DRIVER', 'file'))`, the compose file passes `CACHE_STORE: "${CACHE_STORE:-${CACHE_DRIVER:-file}}"`, and `.env.example`, docs, Diagnostics, `phpunit*.xml` and the CI env files name `CACHE_STORE`. A deployment whose `.env` sets only `CACHE_DRIVER` keeps its store.
5. A separate worker container needs a cache store shared with the web container: the compose file sets `CACHE_STORE`, `deploy-worker-mode.md` and `.env.example` recommend `database` or `redis`, and Diagnostics (`WorkerCacheCheck`) warn inside a LycheeOrg image when a non-`sync` queue runs on a `file`, `array` or `apc` cache.

## Consequences

### Positive
- Deduplication works across containers whatever the cache driver.
- `CACHE_STORE=database` is usable.
- Docker administrators are told when their cache is split between containers.
- A lock taken inside a transaction leaves it usable on PostgreSQL; with `CACHE_STORE=database` this also holds for the album-tree lock, `ChunkSequenceRule` and the other unique jobs.

### Negative
- On asynchronous queues each dispatched recompute job costs one insert (and one delete when a worker starts it) in `cache_locks`.
- One more schema change.
- Lychee overrides Laravel's `database` cache driver: an upgrade of Laravel's `DatabaseStore`/`DatabaseLock` constructors or acquisition logic must be mirrored in `TransactionSafeDatabaseStore`/`TransactionSafeDatabaseLock` and `AppServiceProvider::registerDatabaseCacheStore()`.
- The default compose file (database queue, worker, `file` cache) shows the Diagnostics warning until `CACHE_STORE` is changed.

## Alternatives Considered

- **Shared cache only (no database locks):** the default Docker setup keeps dropping updates until reconfigured.
- **Shorter lock lifetime:** bounds the loss without removing it and shrinks the deduplication window.
- **Database locks on every queue connection:** two extra queries per dispatch on `sync`, where the lock is taken and released in the same process.
- **Separate `lock_connection` (second database connection):** the lock row commits independently of the dispatching transaction, while the `jobs` row of the `database` queue rolls back with it: a rolled-back transaction leaves a lock without a job and drops every dispatch for that key for one hour. In tests the lock rows also escape `DatabaseTransactions`.
- **`CACHE_DRIVER` as the only variable (`config/data.php` aligned on it):** keeps a name Laravel 11+ no longer uses.
- **Savepoint around the insert:** same result as `insertOrIgnore`, with two more statements per acquisition inside a transaction.

## Security / Privacy Impact

- None: lock rows hold a key derived from the job class and target id, and a random owner token.

## Operational Impact

- New tables `cache` and `cache_locks`; expired lock rows are pruned by Laravel's lock lottery.
- The admin "Flush cache" action does not clear `cache_locks`; a lost lock expires after one hour.
- Worker deployments set `CACHE_STORE=database` or `CACHE_STORE=redis` for both containers. `CACHE_DRIVER` is read when `CACHE_STORE` is unset.

## Links

- Related spec sections: `docs/specs/4-architecture/features/090-recompute-dispatch-dedup/spec.md#functional-requirements` (FR-090-11 … FR-090-15, NFR-090-02)
- Related how-to: `docs/specs/2-how-to/deploy-worker-mode.md`
