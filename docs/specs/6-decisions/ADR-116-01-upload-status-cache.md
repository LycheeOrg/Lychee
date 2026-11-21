# ADR-116-01: Upload outcomes in a shared cache, readable by the uploader

- **Status:** Accepted
- **Date:** 2026-11-18
- **Related features/specs:** Feature 116 (docs/specs/4-architecture/features/116-upload-status-query/spec.md), Feature 090 (ADR-090-01), Feature 114 (ADR-114-01)
- **Related open questions:** Q-116-01, Q-116-02, Q-116-05

## Context

With a queue worker, `POST /api/v2/Photo` answers before the photo is processed, with a provisional ID. Duplicates end under another photo's ID or are skipped, and the client cannot learn which ([discussion #4991](https://github.com/LycheeOrg/Lychee/discussions/4991)). The owner asked for the outcome to be kept in the cache for one day, written by the job, and read back by provisional ID.

Two constraints shape where and for whom:
- The upload request runs in the web container, the job in the worker container. The default LycheeOrg compose file uses `CACHE_STORE=file`, which each container keeps to itself (ADR-090-01). Job locks already move to the `database` store on asynchronous queues for that reason.
- Laravel's `database` cache store removes an expired row only when the key is read again, and Lychee cannot rely on its scheduler (one weekly command, cron rarely set up).
- A duplicate's ID may point at a photo the caller cannot see.

## Decision

1. **Entry.** `upload-status:<provisional id>` holds `uploader_id`, `status`, `photo_id`, `message`, with a 24-hour TTL reset on each write. `ProcessImageJob` writes `processing` when it is created (still in the upload request), and the outcome in `handle()` and `failed()`. Recording errors are caught and logged; they never change the upload's or the job's outcome.
2. **Store.** The default cache store, except when `queue.default` is not `sync` and the default driver keeps its data in one container (`file`, `array`, `apc`): then the `database` store. That driver list has one definition, also read by `WorkerCacheCheck`.
3. **Cleanup.** On a `database` store, writing a `processing` entry deletes expired upload status rows with 1-in-50 odds.
4. **Access.** An entry with an uploader ID answers only to that authenticated user. A guest upload's entry answers to anyone presenting its provisional ID (144 random bits). Every other case answers `unknown`, the same as a missing entry. `photo_id` is returned only when the caller passes `PhotoPolicy::CAN_SEE` on that photo at query time.
5. **Endpoint.** `GET /api/v2/Photo::uploadStatus?photo_ids[]=…`, 1 to 100 IDs, answered in request order.

## Consequences

### Positive
- The default Docker setup reports outcomes without configuration.
- Redis and database users keep entries in the store they chose.
- One polling request covers a whole batch; no album listing scans.
- No existence oracle; no leak of photos the caller cannot see.

### Negative
- On an asynchronous queue with a `file` cache, each upload costs two or three `cache` table writes.
- The cleanup query depends on the `cache` table layout (`key`, `expiration`) of Laravel's `database` driver.
- A client that changes account between upload and query gets `unknown`.

## Alternatives Considered

- **Default store only (Q-116-05 B):** least code, broken with the default compose file.
- **Database store on every asynchronous queue (Q-116-05 A):** simplest rule, moves Redis users' entries to the database.
- **Anyone holding the ID (Q-116-01 B):** a leaked ID exposes outcomes and messages for a day.
- **Single-ID endpoint (Q-116-02 B):** N requests per polling round.
- **A dedicated table with scheduled pruning:** durable, but a schema change and a scheduler Lychee does not run reliably.

## Security / Privacy Impact

- Failure messages are exposed only for `BaseLycheeException` (user-facing); other throwables answer `"Processing failed"`.
- No new authenticated surface: the endpoint reads only entries bound to the caller (or guest entries by their unguessable ID).

## Operational Impact

- No admin action required. `WorkerCacheCheck` keeps warning about split caches for the other caches.
- Rows in `cache` stay bounded to about one day of uploads.

## Links

- Related spec sections: `docs/specs/4-architecture/features/116-upload-status-query/spec.md` (FR-116-01 … 10, NFR-116-02 … 04)
- Related ADRs: ADR-090-01, ADR-114-01
- Related issues / PRs: LycheeOrg/Lychee discussion #4991
