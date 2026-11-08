# Open Questions – Feature 112

Open questions for [Feature 112](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-112-01~~ | 112 | High | Deleting a 4000-photo album times out: fix direction, since the DB queries are not the measured bottleneck | Resolved (A, owner 2026-11-08, spec FR-112-01–04) | 2026-11-05 | 2026-11-08 |
| ~~Q-112-02~~ | 112 | High | Deleting 4000 selected photos stalls: per-photo `PhotoWillBeDeleted` fan-out when the webhook feature is on | Resolved (A, thumb jobs computed once per request, owner 2026-11-08, spec FR-112-05–08) | 2026-11-05 | 2026-11-08 |
| ~~Q-112-03~~ | 112 | Medium | Unbounded `whereIn` id lists in the delete flow can exceed the bind-parameter limit | Resolved (A, owner 2026-11-08: a `whereIn` over 1000 parameters fails on MySQL, spec FR-112-09, NFR-112-04) | 2026-11-08 | 2026-11-08 |

## Question Details

### Q-112-01 – Fix direction for the album-delete timeout

**Context.** Report: deleting an album holding ~4000 photos "times out and dies". No log of the failure exists in this checkout (`storage/logs`, `failed_jobs` hold nothing delete-related), so the root cause was measured rather than observed.

**Evidence (2026-11-05).**

- End-to-end `App\Actions\Album\Delete::do()` on the SQLite test DB, 4000 photos × 7 size variants, `QUEUE_CONNECTION=sync`: **2.9 s wall, 172 queries, 0.3 s in SQL**. `FileDeleterJob` alone took **2.3 s** (28 000 paths, files absent, so this is almost entirely the per-file `Log::debug` line).
- `FileDeleterJob` on 28 000 *real* files on local SSD: 2.45 s with logging, 1.21 s without (87 → 43 µs/file).
- Read-only timing on the dev MariaDB 11.8 (67 790 photos, 433 122 size variants) for the largest album (1532 photos): every SELECT of the delete flow runs in < 160 ms. `EXPLAIN` of the `IN (subquery)` occurrence query uses `ref` on indexes. Every FK pointing at `photos`, `size_variants`, `albums`, `base_albums` has a leading index, and there are no triggers. Write-side `EXPLAIN DELETE` was not run (read-only session).
- Cost that does scale with photo count is file deletion, done in **one** `FileDeleterJob` per (disk, kind) carrying all paths (28 000 for 4000 photos):
  - `sync` queue (Lychee's default, see `.env.example`): runs inside the HTTP request after the DB commit, so the request hits PHP / FPM / proxy timeouts on slow storage (NAS, network mounts).
  - S3: `exists()` + `delete()` per file = 2 HTTP round trips × 28 000 ≈ 56 000 requests. That can't finish in any request or default worker timeout (60 s).
  - Async queues: a single job with 28 000 paths can exceed the worker `--timeout`, and the worker gets killed.
- Re-measured over HTTP (`DELETE /api/v2/Album`, request validation and policy included), 4000 photos × 7 size variants, `sync` queue, webhook feature **on**: **2.8 s wall, 194 queries, 0.3 s in SQL, +16 MB peak memory**. The album path never dispatches `PhotoWillBeDeleted`, so the fan-out in Q-112-02 does not apply here. The request runs no hydration and no per-ID policy loop (authorization is two aggregate queries).

**Question.** Which direction should the fix take?

**Resolution (2026-11-08).** Option A. Encoded in [spec.md](spec.md) FR-112-01–04.

#### Option A – Make file deletion cheap and bounded (recommended)

- Drop the per-file `Log::debug` in `FileDeleterJob` (one summary line per job).
- S3: skip the `exists()` pre-check (deleting a missing key is a no-op), halving round trips.
- Split file lists into fixed-size batches (for example 500 paths per `FileDeleterJob`) so each queued job and its payload stay bounded.
- With the `sync` queue, run the file-deletion jobs after the HTTP response is sent (`afterResponse()`), so the request returns once the DB transaction commits.

Pros: targets the measured hotspot. Works in every queue mode. No schema or API change. DB records are still removed first, as today.
Cons: with `sync` the PHP worker still does the file I/O after the response (FPM `request_terminate_timeout` still applies). Does not help if the real failure is a DB query on another instance.

#### Option B – Option A plus making the whole album delete a queued job

The HTTP call returns immediately and the DB deletion runs in a job.

Pros: no request timeout at all on async queues.
Cons: with `sync` it is still inline. The UI must cope with the album disappearing later. Errors are no longer reported to the caller. Larger change.

#### Option C – Gather the actual failure first

Get the real error (log line, HTTP status, or worker message) from the instance where it died before changing code.

Pros: rules out a DB-side cause that does not reproduce here (different engine, lock contention with a worker, very large gallery).
Cons: delays the fix. Option A is beneficial regardless.

### Q-112-02 – Per-photo event fan-out on multi-photo delete

**Context.** Selecting ~4000 photos and deleting them stalls Lychee. `App\Actions\Photo\Delete::dispatchWillBeDeletedEvents()` fires one `PhotoWillBeDeleted` per hard-deleted photo whenever `Features::active('webhook')` (`WEBHOOK_ENABLED=true`, on in the dev `.env`). It does this before the delete and whether or not any webhook subscribes to `photo.delete`. It also builds a download URL for every size variant (28 000 for 4000 photos). Three listeners react to each event:

- `RecomputeAlbumUserThumbsOnPhotoChange::handlePhotoWillBeDeleted`: dispatches one `RecomputeAlbumUserThumbsJob` per `SmartAlbumType` case (15), plus 3 lookup queries, **per photo**.
- `ManagedCacheAlbumListingInvalidator::handlePhotoWillBeDeleted`: one `faces` query plus a tag flush per photo. This is redundant with the coarse `AlbumListingCacheFlushRequested` fired after the delete.
- `WebhookListener::handlePhotoWillBeDeleted`: one `webhooks` query per photo.

**Evidence (2026-11-05)**, `DELETE /api/v2/Photo` on the SQLite test DB, photos × 7 size variants, smart-album thumbs cached for 2 users:

| Scenario | Wall | Queries | SQL time | Jobs |
|---|---|---|---|---|
| 4000 photos, webhook off, `sync` | 3.3 s | 213 | 0.35 s | 8 run |
| 4000 photos, webhook on, `sync` | **504 s** | 368 322 | 203 s | **60 000** `RecomputeAlbumUserThumbsJob` run (4000 × 15) |
| 4000 photos, webhook on, `database` | **30 s** | 141 399 | 2.8 s | 15 queued (the unique lock drops the rest) |
| 1000 photos, webhook on, `database` | 7.6 s | 35 376 | 0.7 s | breakdown: 30 000 `cache_locks` insert/update (unique-job lock attempts), 2002 `faces`, 1000 each `photos_tags` / `album_user_thumbs` / `webhooks` |

No memory issue (peak +27 MB) and no model hydration. The cost is linear in photo count with a large constant (~7.5 ms/photo on async queues, ~126 ms/photo on `sync`). The thumb refresh on delete is also redundant: `album_user_thumbs.photo_id` is `ON DELETE CASCADE`, and `CachesAlbumUserThumb::getCachedOrLiveThumb()` recomputes on a miss. On `sync` the jobs even run *before* the photos are gone, so they can re-pick a photo that is about to be deleted. With the webhook feature off, none of these listeners run on delete, and thumbs and listings still come out right.

Side note: album deletion never fires `PhotoWillBeDeleted`, so `photo.delete` webhooks are not sent for photos removed with their album. This is kept out of scope unless requested.

**Question.** How should the fan-out be removed?

**Resolution (2026-11-08).** Option A, with the owner's amendment that the `RecomputeAlbumUserThumbsJob`s are still dispatched, computed once per delete request: the affected albums are resolved once before the DB delete and each job is dispatched once after it. Encoded in [spec.md](spec.md) FR-112-05–08.

#### Option A – Keep `PhotoWillBeDeleted` for webhooks only, batch the rest (recommended)

- Unsubscribe `RecomputeAlbumUserThumbsOnPhotoChange` and `ManagedCacheAlbumListingInvalidator` from `PhotoWillBeDeleted`. The FK cascade, the lazy thumb recompute and the post-delete `AlbumListingCacheFlushRequested` already cover them.
- In `dispatchWillBeDeletedEvents()`, query once for enabled `photo.delete` webhooks and return early when there are none. This skips the 4000 events and 28 000 URL builds in the common case.
- When delete webhooks do exist, behaviour is unchanged: one event and one payload per photo, as today.

Pros: removes the measured hotspot entirely. No API or payload change for webhook consumers. Small diff in three files. Thumbs stay correct through existing mechanisms.
Cons: person-album "matching albums" cache eviction now relies only on the coarse flush (already the case when the webhook feature is off).

#### Option B – Introduce a batched `PhotosWillBeDeleted(photo_ids[])` event

Fire one event carrying all IDs. Listeners use their existing batched `refreshForPhotoIds()` / `evictPersonTagsForPhotos()`, giving 15 jobs and a handful of queries per request. `WebhookListener` expands it into per-photo payloads.

Pros: keeps the listeners' behaviour, only batched. Scales to any N.
Cons: new event class and changed listener wiring. Still does redundant thumb work before the photos are gone.

#### Option C – Deduplicate inside the listeners

Remember already-dispatched album IDs within the request in the thumb listener (static set).

Pros: smallest change.
Cons: still 4000 events, URL builds and per-photo queries (~20 s on `database` per the breakdown). Request-scoped static state is fragile under Octane / queue workers.

### Q-112-03 – Unbounded `whereIn` id lists in the delete flow

**Context.** `PhotosToBeDeletedDTO` and `AlbumsToBeDeletedDTO` chunk their id lists (500 / 1000), but several queries around them bind the whole list. Limits: SQLite 32 766 bind parameters, MySQL/MariaDB and PostgreSQL 65 535. `DeletePhotosRequest` and `DeleteAlbumsRequest` have no `max` on their id arrays.

| Site | List bound | Grows with |
|------|------------|------------|
| `Photo\Delete::do()` (3 queries on `photos`/`photo_album`) | request `photo_ids` | selection size |
| `PurchasableService::deleteMulitplePhotoPurchasables()` | request `photo_ids` | selection size |
| `Photo\Delete::dispatchWillBeDeletedEvents()` first query (later chunked) | hard-deleted ids | selection size |
| `Album\Delete::findAllPhotosToDelete()` (2 queries) | recursive album ids | subtree size |
| `Album\Delete::findAllAlbumsToDelete()`, `getGaps()`, tag/person album deletes | request `album_ids` | selection size |
| `PhotosToBeDeletedDTO::forceDelete()` `Album`/`TagAlbum` re-reads | albums whose cover/header was a deleted photo | photos × covers |
| `PhotosToBeDeletedDTO::forceDelete()` and `SizeVariant\Delete` `order_items` exclusion (`whereIn`/`whereNotIn`, added to every 500-chunk) | every `order_items.size_variant_id` in the instance | shop orders, not the request |
| `RecomputeAlbumUserThumbsOnPhotoChange::refreshForPhotoIds()` (becomes `AlbumUserThumbTargets`) and `ManagedCacheAlbumListingInvalidator::evictPersonTagsForPhotos()` | event photo ids | bulk edit size |

With the 1000-parameter bound on MySQL, a 4000-photo selection already fails in `Photo\Delete::do()` and `PurchasableService`, and the `order_items` exclusion fails every delete once the shop has sold more than ≈500 size variants.

**Question.** How far should Feature 112 go?

**Resolution (2026-11-08).** Option A. Owner: a `whereIn` with more than 1000 parameters fails on MySQL, so the bound is 1000 per query, not the 32 766 / 65 535 placeholder limits. Every listed site binds at most 500 ids per query. Encoded in [spec.md](spec.md) FR-112-09 and NFR-112-04.

#### Option A – Chunk every listed site inside Feature 112 (recommended)

Use a shared chunk size (500) for request/derived id lists. Replace the `order_items` id list by a `NOT EXISTS` / subquery on `order_items`, so it binds nothing. `AlbumUserThumbTargets` chunks from the start.

Pros: removes the whole class of failure in the code this feature already touches. No API change.
Cons: more files in the diff (adds `PurchasableService`, `Album\Delete` queries).

#### Option B – Chunk only the code Feature 112 writes, plus the `order_items` exclusion

Pros: smaller diff. Fixes the one site that grows independently of the request.
Cons: very large selections can still fail in `Photo\Delete` and `PurchasableService`.

#### Option C – Cap the request arrays (`max:N` on `photo_ids` / `album_ids`)

Pros: one-line bound at the edge.
Cons: changes the API contract (422 above N), does not bound derived lists (subtree, `order_items`, events).

