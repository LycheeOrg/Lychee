# Open Questions – Feature 090

Open questions for [Feature 090](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-090-01~~ | 090 | High | Coalescing window: A no delay (dedupe only), B dedupe + fixed delay, C Laravel `#[DebounceFor]` | Resolved (A, spec FR-090-07) | 2026-10-12 | 2026-10-12 |
| ~~Q-090-02~~ | 090 | Medium | Scope: A all seven target-keyed recompute jobs, B only the three debounced ones | Resolved (A, spec FR-090-01, FR-090-03) | 2026-10-12 | 2026-10-12 |
| ~~Q-090-03~~ | 090 | Medium | Lock lifetime `uniqueFor`: A 1 hour, B 1 day, C no expiry | Resolved (A, spec FR-090-04) | 2026-10-12 | 2026-10-12 |
| ~~Q-090-04~~ | 090 | Medium | `DebouncesLatestJobTrait`: A delete trait and its tests, B keep the file unused | Resolved (A, spec FR-090-05) | 2026-10-12 | 2026-10-12 |
| ~~Q-090-05~~ | 090 | Medium | Queue priority for recompute jobs: A opt-in low-priority queue via env, B always on `low` queue, C no priority | Resolved (B + diagnostic, spec FR-090-08 … 10) | 2026-10-12 | 2026-10-12 |
| ~~Q-090-06~~ | 090 | High | Unique locks across the web and worker containers: A database lock store when the queue is not `sync` (new `cache`/`cache_locks` migration), B require a shared cache store, C shorter `uniqueFor` | Resolved (A + B, spec FR-090-11 … 13, ADR-090-01) | 2026-10-12 | 2026-10-12 |
| ~~Q-090-07~~ | 090 | Medium | Legacy `CACHE_STORE` vs `CACHE_DRIVER` (CodeRabbit, PR #4864): A `config/data.php` reads `CACHE_DRIVER` only, B no change, C compose falls back to `CACHE_STORE`, D rename to `CACHE_STORE` everywhere | Resolved (D + `CACHE_DRIVER` fallback, spec FR-090-15, ADR-090-01) | 2026-10-12 | 2026-10-12 |
| ~~Q-090-08~~ | 090 | High | `high,default,low` queues: A web uploads (`ProcessImageJob`, `ExtractZip`) always on `high`, B opt-in `UPLOAD_QUEUE`, C `high` name only, no job | Resolved (C, spec FR-090-08 … 10) | 2026-10-12 | 2026-10-12 |

## Question Details

### Q-090-01 · Coalescing window

**Status:** Resolved 2026-10-12, option A.

**Question:** With dispatch-time deduplication, the waiting recompute runs as soon as it reaches the head of the queue. Should dispatches also be delayed so uploads that arrive over time (worker idle between them) collapse into one recompute?

- **A (recommended) – No delay.** `ShouldBeUniqueUntilProcessing` only. Pros: standard Laravel, no tuning constant, no added latency; any backlog collapses (U1, U2, R). Cons: when the worker keeps up with uploads arriving one by one (queue empty between them), each upload still gets its own recompute; the queue does not grow, but the work repeats.
- **B – Dedupe + fixed delay (e.g. 10 s).** Pros: uploads trickling in collapse into one recompute per window. Cons: every stats/cover/size update lags by the delay even on an idle queue; propagation adds the delay per tree level unless parent dispatches skip it; one more constant to tune; `sync` ignores it.
- **C – Laravel `#[DebounceFor]`.** Pros: built-in trailing run. Cons: pushes one delayed entry per dispatch and deletes superseded ones when popped: the queue still grows with every dispatch.

### Q-090-02 · Jobs in scope

**Status:** Resolved 2026-10-12, option A.

**Question:** Which recompute jobs become unique?

- **A (recommended) – All seven target-keyed recompute jobs:** `RecomputeAlbumStatsJob`, `RecomputeAlbumSizeJob`, `RecomputeAlbumUserThumbsJob`, plus `RecomputePhotoBucketsJob` (`photo_id`), `RecomputeAlbumPhotoBucketsJob` (`album_id`), `RecomputeChildAlbumBucketsJob` (`parent_album_id`), `RecomputeRootAlbumBucketsJob` (constant). Pros: one rule for every recompute job; repeated edits (rating clicks, sort toggles) collapse too; each job reads the database when it runs. Cons: four more classes and their dispatch tests to check.
- **B – Only the three debounced jobs.** Pros: smallest change. Cons: the bucket jobs keep one queue entry per dispatch; two patterns side by side.

`RecomputePersonStatsJob` stays out in both options (array payload, `dispatchSync`).

### Q-090-03 · Lock lifetime

**Status:** Resolved 2026-10-12, option A.

**Question:** How long does the unique lock of a waiting copy live (`uniqueFor`)?

- **A (recommended) – 1 hour** (as `WatermarkerJob`). Pros: a waiting copy lost from the queue blocks its target for at most one hour; in a backlog longer than one hour, at most one extra copy per hour and target. Cons: very long imports push a few extra copies.
- **B – 1 day.** Pros: fewer extra copies in very long backlogs. Cons: a lost copy blocks its target's recompute for up to a day (admin "Flush cache" clears it).
- **C – No expiry** (Laravel default `0`). Pros: never more than one copy. Cons: a lost copy blocks its target until the cache is flushed.

### Q-090-04 · Remove `DebouncesLatestJobTrait`

**Status:** Resolved 2026-10-12, option A.

**Question:** The trait cannot stay on the unique jobs: it registers its "latest job" marker in the constructor, so a dispatch dropped by the lock would mark the waiting copy as superseded, and that copy would skip itself (recompute lost). May the unused files be deleted?

- **A (recommended) – Delete** `app/Jobs/Traits/DebouncesLatestJobTrait.php`, `tests/Unit/Jobs/Traits/DebouncesLatestJobTraitTest.php`, and the method `testSupersededJobLogsWithItsOwnContext` in `tests/Unit/Jobs/RecomputeAlbumUserThumbsJobTest.php`. Pros: no dead code. Cons: none.
- **B – Keep the trait file unused.** Pros: available for a future job. Cons: dead code whose pattern conflicts with unique jobs.

### Q-090-05 · Queue priority for recompute jobs

**Status:** Resolved 2026-10-12, option B, plus a Diagnostics info line when the queue is not `sync` and Lychee does not run in a LycheeOrg image.

**Question:** Laravel has no per-job priority; priority comes from named queues that a worker drains in order (`queue:work --queue=default,low` runs `low` only when `default` is empty). Putting the recompute jobs on a lower queue would make them run after every waiting upload, so a long upload gives one recompute per album at the end instead of one per pass through the backlog. The Docker worker runs `--queue="$QUEUE_NAMES"` with `QUEUE_NAMES=default`, and a bare-metal `php artisan queue:work` drains only the connection's default queue: a job on any other queue never runs there. Should the recompute jobs get a lower priority?

- **A (recommended) – Opt-in low-priority queue.** New env `RECOMPUTE_QUEUE` (unset → the connection's default queue, as today). The seven recompute jobs are pushed on it. `.env.example` and `deploy-worker-mode.md` document `RECOMPUTE_QUEUE=low` together with `QUEUE_NAMES=default,low`. Pros: no existing worker stops recomputing; Docker users opt in with two variables. Cons: setting `RECOMPUTE_QUEUE` without adding it to the worker's queue list leaves stats stale; uploads that never stop delay recomputes until the default queue drains.
- **B – Always on a `low` queue.** Docker default becomes `QUEUE_NAMES=default,low`; bare-metal workers must add `--queue=default,low`. Pros: every Docker install gets it without configuration. Cons: bare-metal workers and compose files that pass `QUEUE_NAMES=default` (the compose example in `deploy-worker-mode.md` does) silently stop recomputing.
- **C – No priority.** Dedupe only. Pros: nothing to configure, no risk. Cons: with uploads arriving during a backlog, a recompute runs once per pass through the backlog instead of once at the end.

### Q-090-06 · Unique locks across the web and worker containers

**Status:** Resolved 2026-10-12, options A and B together (the owner's choice of A approves the migration).

**Question:** Laravel keeps a unique-job lock in the default cache store. The web container takes the lock on dispatch; the worker releases it when it starts the job, in its own cache store. In the LycheeOrg Docker setup both containers use the `file` cache (`config/cache.php` reads `CACHE_DRIVER`, the compose file sets `CACHE_STORE`) and `storage/framework/cache` is not a shared volume: the worker's release never reaches the web container's lock, so every later dispatch for that album or photo is dropped until the lock expires (3600 s), even after the recompute ran. Bare metal and single-container images share one cache directory and are not affected. `WatermarkerJob`/`EmbedMetadataJob` (`ShouldBeUnique`) have the same problem today. Where should the locks live?

- **A (recommended) – Database lock store when the queue is not `sync`.** New migration with Laravel's `cache` and `cache_locks` tables; the seven jobs' `uniqueVia()` returns `Cache::store('database')` when `queue.default` is not `sync`, the default store otherwise. Pros: web and worker always share the database, whatever the cache driver and queue driver; no deployment change; `CACHE_DRIVER=database` becomes usable (its tables are missing today). Cons: a schema change; one insert and one delete per dispatched recompute job on asynchronous queues.
- **B – Require a shared cache store in worker mode.** Fix the compose variable (`CACHE_STORE` → `CACHE_DRIVER`), document `redis`/`database` cache for workers, Diagnostics warning when a LycheeOrg image runs a non-`sync` queue on the `file` cache. Pros: no schema change. Cons: the default Docker setup keeps dropping updates for up to an hour until reconfigured; `database` cache still needs the missing tables.
- **C – Shorter `uniqueFor` (e.g. 60 s).** Pros: one-line change. Cons: does not fix it, every album still drops updates for up to the lock lifetime; the dedupe window shrinks with it.

### Q-090-07 · Legacy `CACHE_STORE` next to `CACHE_DRIVER`

**Status:** Resolved 2026-10-12, option D with a `CACHE_DRIVER` fallback (owner directive): `CACHE_STORE` everywhere, `CACHE_DRIVER` read when `CACHE_STORE` is unset.

**Question:** The compose file now sets `CACHE_DRIVER` (read by `config/cache.php`) instead of `CACHE_STORE`. `config/data.php` (laravel-data structure cache) reads `CACHE_STORE` first, then `CACHE_DRIVER`. A deployment whose `.env` sets only `CACHE_STORE` gets the structure cache in that store and Laravel's default cache on `file`. The compose services load `./.env` through `env_file`, so `CACHE_STORE` still reaches the container: this split is the same as before the PR. Nothing in Lychee needs two variables: `config/data.php` is laravel-data's published default, written for Laravel 11+ where the variable is called `CACHE_STORE`, while Lychee's `config/cache.php`, `.env.example`, docs and Diagnostics use `CACHE_DRIVER`. The structure cache holds class metadata derived from the code, so moving it to another store only costs a rebuild. Should the split be resolved?

- **A (recommended) – `config/data.php` reads `CACHE_DRIVER` only.** Pros: one variable drives every cache; an admin setting `CACHE_STORE` is no longer misled into thinking the cache moved (the compose file itself set `CACHE_STORE` before this PR). Cons: deployments that set `CACHE_STORE` move their structure cache to `CACHE_DRIVER`'s store (rebuilt on first use); edits a published vendor config.
- **B – No change.** Reply on the PR that the split predates it and is harmless. Pros: no behaviour change. Cons: two variable names for two caches stay.
- **C – CodeRabbit's compose fallback** (`CACHE_DRIVER: "${CACHE_DRIVER:-${CACHE_STORE:-file}}"` plus the same for `CACHE_STORE`). Pros: a legacy `CACHE_STORE=redis` also moves Laravel's default cache. Cons: a compatibility shim for a legacy variable, against the greenfield stance in AGENTS.md; compose-only, bare metal unchanged.
- **D – Rename to `CACHE_STORE` everywhere** (Laravel 11+ name). Pros: matches current Laravel. Cons: every deployment's `CACHE_DRIVER` setting stops working; docs, compose, `.env.example` and Diagnostics change.

### Q-090-08 · `high,default,low` queues

**Status:** Resolved 2026-10-12, option C.

**Question:** The owner asked for `high,default,low` support. Only the seven recompute jobs set a queue (`low`); every other job uses the connection's default queue, so a worker on `high,default,low` already runs everything and `high` stays empty. Which jobs go on `high`, and what do the Docker default and Diagnostics expect?

- **A (recommended) – Web uploads always on `high`.** `ProcessImageJob` and `ExtractZip` (dispatched by `PhotoController` for an upload) declare `#[Queue(JobQueue::HIGH)]`. `ImportImageJob` (server import) and the follow-up jobs (colours, geodecoding, S3, watermark, metadata, AI scans, webhooks, file deletion) stay on `default`; the recompute jobs stay on `low`. Docker default `QUEUE_NAMES=high,default,low`. `RecomputeQueueCheck` warns inside a LycheeOrg image when `QUEUE_NAMES` misses `high` or `low`, and gives `php artisan queue:work --queue=high,<default>,low` elsewhere. Pros: an upload's thumbnails appear before background work drains, and a web upload jumps ahead of a running server import; same "always + Diagnostics" model as `low` (Q-090-05 B). Cons: a worker that does not list `high` (bare-metal `queue:work` without `--queue`, `QUEUE_NAMES=default,low`) never processes uploads: photos stay unprocessed, worse than stale statistics; only the Diagnostics line tells the admin.
- **B – Opt-in `UPLOAD_QUEUE`.** New env (unset → connection's default queue, as today); `ProcessImageJob`/`ExtractZip` are pushed on it. Docs pair `UPLOAD_QUEUE=high` with `QUEUE_NAMES=high,default,low`; Diagnostics warn when `UPLOAD_QUEUE` is set and missing from `QUEUE_NAMES`. Pros: no existing worker stops processing uploads. Cons: two variables to keep consistent; differs from the `low` model.
- **C – `high` accepted, no job on it.** `JobQueue::HIGH` constant, Docker default `high,default,low`, docs reserve `high`. Pros: no behaviour change, no risk. Cons: a queue name that does nothing.
