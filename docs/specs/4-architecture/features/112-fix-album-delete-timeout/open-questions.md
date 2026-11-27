# Open Questions – Feature 112

Open questions for [Feature 112](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-112-01~~ | 112 | High | Deleting a 4000-photo album times out: fix direction, since the DB queries are not the measured bottleneck | Resolved (A, owner 2026-11-08, spec FR-112-01–04) | 2026-11-05 | 2026-11-08 |
| ~~Q-112-02~~ | 112 | High | Deleting 4000 selected photos stalls: per-photo `PhotoWillBeDeleted` fan-out when the webhook feature is on | Resolved (A, thumb jobs computed once per request, owner 2026-11-08, spec FR-112-05–08) | 2026-11-05 | 2026-11-08 |
| ~~Q-112-03~~ | 112 | Medium | Unbounded `whereIn` id lists in the delete flow can exceed the bind-parameter limit | Resolved (A, owner 2026-11-08: a `whereIn` over 1000 parameters fails on MySQL, spec FR-112-09, NFR-112-04) | 2026-11-08 | 2026-11-08 |
| ~~Q-112-04~~ | 112 | High | `order_items` exclusion: a NULL `size_variant_id` empties every `NOT IN`, and the "keep" update detaches sold variants of photos that are not deleted | Resolved (A, owner 2026-11-15, spec FR-112-10, S-112-13..15) | 2026-11-15 | 2026-11-15 |
| ~~Q-112-05~~ | 112 | High | The delete policies bind every request id, before the action runs; FR-112-09 does not list them | Resolved (A, owner 2026-11-15, spec FR-112-11, NFR-112-04, S-112-16) | 2026-11-15 | 2026-11-15 |
| ~~Q-112-06~~ | 112 | Medium | Which post-delete jobs run after the response on `sync` (producers return file, person-stats and face-embedding jobs together; FR-112-07 adds thumb jobs, also with the webhook feature off) | Resolved (A, owner 2026-11-15, spec FR-112-04, FR-112-07, DO-112-03, S-112-18) | 2026-11-15 | 2026-11-15 |
| ~~Q-112-07~~ | 112 | Medium | With a `photo.delete` webhook on `sync`, one inline HTTP call per deleted photo still runs before the DB delete | Resolved (A, owner 2026-11-15, spec FR-112-12, S-112-21) | 2026-11-15 | 2026-11-15 |
| ~~Q-112-08~~ | 112 | High | `SizeVariant\Delete` keeps sold variants attached, so rotating a photo with a sold variant breaks `unique(photo_id, type)` | Resolved (A, owner 2026-11-15, spec FR-112-10, S-112-15, S-112-20) | 2026-11-15 | 2026-11-15 |
| ~~Q-112-09~~ | 112 | Medium | Repair sold variants already detached from photos that still exist by the old instance-wide update | Resolved (A, owner 2026-11-15, spec FR-112-13, DO-112-05, S-112-22) | 2026-11-15 | 2026-11-15 |
| ~~Q-112-10~~ | 112 | Medium | On `sync`, after-response jobs (delete webhooks, file deletions) are lost when the PHP worker is killed after the response; the spec does not say what is guaranteed | Resolved (A, owner 2026-11-22, spec FR-112-04, FR-112-12, Non-Goals, Documentation Deliverables) | 2026-11-22 | 2026-11-22 |
| ~~Q-112-11~~ | 112 | High | A sale committed during a delete can get its file deleted: the sold-variant path query and the detach/delete statements read `order_items` separately | Resolved (C, owner 2026-11-23, spec FR-112-10 failure path, Non-Goals) | 2026-11-23 | 2026-11-23 |
| ~~Q-112-12~~ | 112 | Medium | `Album\Delete::findAllPhotosToDelete()` reads link counts in one statement per album chunk: a concurrent move between chunks can classify a photo that has just entered a surviving album as fully deletable | Resolved (A, owner 2026-11-23, spec FR-112-09 failure path, Non-Goals) | 2026-11-23 | 2026-11-23 |
| ~~Q-112-13~~ | 112 | High | Album delete on an HDD-backed DB hits Octane's 30 s wall-clock limit inside the DB transaction, before any job this feature defers; raising the limit is not a fix | Resolved for this feature (Option C as FR-112-14, owner go-ahead 2026-11-27; the end state, Options A/B, goes to a follow-up feature) | 2026-11-23 | 2026-11-27 |

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


### Q-112-04 – `order_items` exclusion: NULL ids and the instance-wide detach

**Context (2026-11-15 review).** FR-112-09 rewrites the three `order_items` sites as a subquery. Two existing defects sit on those lines:

1. `order_items.size_variant_id` is nullable. Only `OrderCompletedListener::fullfillOrder()` sets it, and only for MEDIUM / MEDIUM2X / ORIGINAL photo items whose variant exists. It stays NULL **permanently** for every item delivered by URL: print and pixel items, FULL items, and any item that already has a `download_link` (`fullfillOrder()` skips it, and `markAsDelivered` writes only `download_link`). It is also NULL for album items and for items not yet fulfilled. `pluck('size_variant_id')` keeps the NULL and Laravel binds it. `sv.id NOT IN (…, NULL)` is never true (checked on SQLite: `NOT IN (1, NULL)` and `NOT IN (SELECT …)` return no row, `NOT EXISTS` returns the expected rows). So once the shop has one such item (one print, FULL or URL-delivered sale is enough, and it never clears):
   - `PhotosToBeDeletedDTO::collectSizeVariantPathsByPhotoID()` returns nothing. The DB rows are deleted but the files stay on disk (silent leak).
   - `SizeVariant\Delete::do()` deletes the files (the `array_diff` path) but `whereNotIn('id', …)` deletes no row. The rows then point at missing files.
   A `whereNotIn('sv.id', fn => select size_variant_id from order_items)` subquery has the same defect.
2. `PhotosToBeDeletedDTO::forceDelete()` line 184 runs `size_variants WHERE id IN (every sold variant) SET photo_id = NULL` with no photo filter. Every photo delete detaches all sold variants of every photo in the instance, including photos that are not deleted (for example, a surviving photo whose original was sold loses its original). Existing tests only check the deleted photo's sold variant.

**Question.** Does Feature 112 fix these while it rewrites the lines?

**Resolution (2026-11-15).** Option A. Encoded in [spec.md](spec.md) FR-112-10, S-112-13..15. No ADR: local bug fix on lines the feature already rewrites.

#### Option A – Fix both in Feature 112 (recommended)

- The exclusion uses `NOT EXISTS (SELECT 1 FROM order_items oi WHERE oi.size_variant_id = sv.id)`. It binds nothing and NULLs are harmless.
- The "keep" update is scoped to the force-deleted chunk: `size_variants WHERE photo_id IN (chunk) AND EXISTS (order_items …) SET photo_id = NULL`, run before the chunk's `size_variants` delete.
- URL-delivered items protect no size variant: the customer downloads from `download_link`, not from Lychee's variant. An item with both a `size_variant_id` and a `download_link` (auto-fulfilled, then overridden by the admin) still protects its variant. A `download_link` that points at a file Lychee serves is not tracked and breaks when the photo is deleted, as today (out of scope).
- Regression tests: one URL-delivered order item (NULL `size_variant_id`, `download_link` set) present → files and rows of the deleted photo are gone; a sold variant of a surviving photo keeps its `photo_id`.

Pros: the rewrite is on these lines anyway. Fixes a silent file leak and a data-corrupting update. No schema change.
Cons: the diff grows by one scoped update and two tests. It also changes behaviour outside the timeout fix (shop data).

#### Option B – NULL-safe subquery in Feature 112, detach bug in its own feature

Feature 112 uses `NOT EXISTS`. Line 184 only stops binding the list (`whereExists` on `order_items`) and keeps its instance-wide scope, and a new feature is opened for it.

Pros: keeps 112 to performance and bounding.
Cons: leaves a known data-corrupting update in place, on a line 112 already edits.

#### Option C – Fix both in a separate feature before 112

Pros: shop fixes ship and are reviewed on their own.
Cons: blocks 112 on another feature. Both features touch the same lines, so they will conflict.

### Q-112-05 – Delete authorization binds every request id

**Context (2026-11-15 review).** `DeletePhotosRequest::authorize()` → `PhotoPolicy::canDeleteById()` runs three queries with `whereIn` over all request `photo_ids` (owner count, unsorted check, parent albums). It then calls `AlbumPolicy::canDeleteContentById()` → `hasGrantOnParentsById()` with every parent album id. `DeleteAlbumsRequest` → `AlbumPolicy::canDeleteById()` does the same with `album_ids`. These run before the action, so with the 1000-parameter bound a 4000-photo selection still fails in authorization. FR-112-09 does not list them, while S-112-10 / NFR-112-04 measure the whole HTTP request and would fail on them.

**Question.** Are the delete policies part of the bound?

**Resolution (2026-11-15).** Option A. Encoded in [spec.md](spec.md) FR-112-11, NFR-112-04, S-112-16. No ADR: permission semantics unchanged, only the query shape.

#### Option A – Add the delete policies to FR-112-09 (recommended)

Chunk `PhotoPolicy::canDeleteById()` and the `AlbumPolicy` delete checks used by the two delete requests. Counts are summed across chunks and compared with the de-duplicated input size. Parent-album lists are merged and de-duplicated before they are chunked.

Pros: makes Goal 4 true end to end over HTTP. S-112-10/11 stay HTTP tests.
Cons: touches two policies, which other features use (move-by-id shares `hasGrantOnParentsById()`).

#### Option B – Keep policies out of scope, test the actions directly

NFR-112-04 is measured on `Photo\Delete::do()` / `Album\Delete::do()` and not over HTTP. Policies get a follow-up feature.

Pros: smaller diff.
Cons: on MySQL a selection of more than 1000 photos still fails, now in the policy. The goal is not met for users.

#### Option C – Cap request arrays at 1000 (`max:1000`)

Pros: bounds policies and request-derived lists with one rule each.
Cons: API contract change (422 above 1000). Rejected as the sole bound in Q-112-03, it would only add to A.

### Q-112-06 – Which post-delete jobs run after the response on `sync`

**Context (2026-11-15 review).** `PhotosToBeDeletedDTO::executeDelete()` returns `FileDeleterJob`s mixed with `RecomputePersonStatsJob` and `DeleteFaceEmbeddingsJob` (which calls the facial-recognition service). DO-112-03 takes `iterable $jobs`, but FR-112-04 says "file-deletion jobs" and the non-goals say the other jobs are not changed. FR-112-07 also adds one `RecomputeAlbumUserThumbsJob` per affected album (at least 15 smart albums) on every photo delete, with the webhook feature on or off. `WEBHOOK_ENABLED` defaults to `false`, so default installs run no thumb job on delete today. On `sync` they would run inline before the response, each one recomputing every cached viewer.

**Question.** Which jobs does the `sync` after-response helper defer?

**Resolution (2026-11-15).** Option A. Helper `DispatchAfterDelete`. Encoded in [spec.md](spec.md) FR-112-04, FR-112-07, FR-112-08, DO-112-02/03, S-112-03/04/18; the non-goal now reads "payload and semantics unchanged, only the dispatch moves". Jobs dispatched by event listeners stay as they are. No ADR: dispatch timing inside one feature.

#### Option A – Every job the delete actions dispatch after the commit (recommended)

One helper (for example `DispatchAfterDelete`) defers file, person-stats, face-embedding and thumb-refresh jobs on `sync`, and dispatches them normally otherwise. Producers pass their whole job list.

Pros: none of this work is on the response path, which is the goal of Q-112-01. The thumb jobs added by FR-112-07 cost default installs no request latency. Call sites stay simple.
Cons: on `sync`, person counts and thumbs are refreshed a moment after the client gets the response (the next read can still see the old count). It changes how the "other jobs" are dispatched, so the non-goal is reworded to "their payload and semantics are unchanged".

#### Option B – File deletion only (as written)

Producers split the list: `FileDeleterJob`s go through the helper, the rest is dispatched inline as today. Thumb jobs are dispatched inline.

Pros: strictly matches FR-112-04 and the non-goals.
Cons: default installs gain 15+ inline thumb jobs per photo delete. The face-embedding HTTP call stays on the response path.

#### Option C – File deletion and thumb jobs, person/face jobs inline

Pros: removes the latency FR-112-07 adds, keeps the other jobs as today.
Cons: the split is arbitrary. The face-embedding call stays on the response path.

### Q-112-07 – `photo.delete` webhooks on `sync`

**Context (2026-11-15, second review).** FR-112-06 keeps one `PhotoWillBeDeleted` per hard-deleted photo when a `photo.delete` webhook is enabled. `WebhookListener::handlePhotoWillBeDeleted()` then dispatches one `WebhookDispatchJob` per (photo, webhook). That job is `ShouldQueue` and makes an HTTP call with a 10 s timeout (`features.webhook_timeout_seconds`). On `sync` it runs inline, before the DB delete, because the event fires before `executeDelete()`. Deleting 4000 photos with one delete webhook therefore makes 4000 sequential HTTP calls inside the request, so it still times out. FR-112-04 leaves listener jobs unchanged, and the plan's success criterion ("a 4000-photo selection returns once the DB transaction commits") does not hold in this case.

**Question.** Should delete webhooks also leave the response path?

**Resolution (2026-11-15).** Option A. Encoded in [spec.md](spec.md) FR-112-12, FR-112-04, DO-112-03, S-112-21; the listener non-goal names this exception. No ADR.

#### Option A – `WebhookListener` hands its delete jobs to `DispatchAfterDelete` (recommended)

`handlePhotoWillBeDeleted()` passes its `WebhookDispatchJob`s to the after-response helper: after the response on `sync`, queued normally otherwise. Payload, per-photo semantics and the "before delete" snapshot stay unchanged.

Pros: a 4000-photo delete with a delete webhook returns once the commit is done, matching Q-112-06 A. One-line change in one listener.
Cons: the "listener jobs unchanged" non-goal gets one exception. As today, a webhook is still sent if the DB delete then fails (the event fires before the delete).

#### Option B – Keep as is, document the limit

Record in the spec and release note: installs with a `photo.delete` webhook should use an async queue for large deletes.

Pros: no change.
Cons: the timeout stays for those installs on `sync`.

#### Option C – Option A plus firing the events after the commit

Build the snapshots before the delete, fire `PhotoWillBeDeleted` after `executeDelete()` succeeds, and defer the webhook jobs.

Pros: no webhook for a delete that failed.
Cons: the event name no longer matches its timing. 4000 snapshots (28 000 URLs) are held in memory across the delete. Larger change.

### Q-112-08 – Sold variants in `SizeVariant\Delete`

**Context (2026-11-15, second review).** `size_variants` has `unique(photo_id, type)` (2021_12_04 refactor, never dropped). FR-112-10 keeps a sold variant's row and file in `SizeVariant\Delete::do()`, with its `photo_id` unchanged. `Rotate` calls `size_variants->deleteAll()` (→ `SizeVariant\Delete`) and then `SizeVariant::create()` for the new original and the other types. If one of the photo's variants is sold, the kept row still holds (photo_id, type), and the insert fails after the old files were already queued for deletion. The same holds for the photo's same-checksum duplicates, which `Rotate` rebuilds the same way. Today it is worse: with any NULL `size_variant_id` in `order_items`, `SizeVariant\Delete` deletes no row at all, so every rotate fails that way. FR-112-10 (`NOT EXISTS`) already fixes the unsold case.

**Question.** What does `SizeVariant\Delete` do with a sold variant?

**Resolution (2026-11-15).** Option A. Encoded in [spec.md](spec.md) FR-112-10 (detach in every delete path), S-112-15, S-112-20. No ADR.

#### Option A – Detach it like photo delete does (recommended)

`SizeVariant\Delete` sets `photo_id = NULL` on sold variants (row and file kept), deletes the unsold ones, and queues only the unsold files. Same rule as FR-112-10's force delete.

Pros: rotate and regeneration work for photos with sales. The customer keeps downloading the version they bought (`order_items.size_variant_id` still points at it). One rule for every delete path.
Cons: after a rotate, the photo shows the new variant while the buyer downloads the old one (expected for a purchase). The detached variant stays in storage until the order item is removed.

#### Option B – Keep it attached (as written) and refuse the operation

`Rotate` (and any regeneration) checks for sold variants first and fails with a 422 ("photo has sold size variants").

Pros: the photo never changes after a sale.
Cons: owners cannot rotate or regenerate sold photos. Needs a new error path in `Rotate`.

#### Option C – Keep it attached, let callers skip the sold types

Callers re-create only the types that are not sold.

Pros: the sold variant stays visible on the photo.
Cons: a rotated photo mixes rotated and unrotated variants. Each caller has to know about sales.

### Q-112-09 – Repair variants already detached by the old update

**Context (2026-11-15, second review).** Before FR-112-10, every photo force-delete ran `size_variants SET photo_id = NULL WHERE id IN (every sold variant)` across the whole instance. On a shop that sold something and later deleted any photo, the sold variants of photos that still exist are detached: those photos lost, for example, their original or medium. They can be found: `sv.photo_id IS NULL`, an `order_items` row with `size_variant_id = sv.id` and `order_items.photo_id IS NOT NULL`. The FK sets `order_items.photo_id` to NULL when a photo is really deleted, so a non-NULL value means the photo still exists. `unique(photo_id, type)` blocks re-linking when the photo has since been given a new variant of that type (for example by "generate missing size variants").

**Question.** Does Feature 112 repair those rows?

**Resolution (2026-11-15).** Option A. Encoded in [spec.md](spec.md) FR-112-13, DO-112-05, S-112-22 (manual check on the dev DB, no test per owner rule). When several detached variants compete for one (photo, type), the highest id is re-linked. No ADR.

#### Option A – Migration that re-links them (recommended)

Set `sv.photo_id = oi.photo_id` for those rows when the photo has no variant of that type yet. Leave the others detached (row and file kept). Idempotent, no schema change. Per the owner's rule, no migration test: run it on the dev DB.

Pros: affected photos get their variants back on upgrade, with no admin action.
Cons: a data migration on shop data. Rows skipped because of the unique index stay detached (their files are kept).

#### Option B – Admin maintenance action

The same re-link as a Maintenance page check/fix with a count.

Pros: visible to the admin and opt-in.
Cons: most admins never run it. Needs v7/v8 UI and translations.

#### Option C – No repair

Only stop the damage (FR-112-10) and mention it in the release note.

Pros: smallest change.
Cons: photos already damaged stay broken.

### Q-112-10 – After-response jobs lost when the worker is killed on `sync`

**Context (2026-11-22, CodeRabbit review on PR #4997).** On `sync`, FR-112-04 and FR-112-12 run the post-commit jobs and the `photo.delete` `WebhookDispatchJob`s after the response. Laravel keeps them as in-memory terminating callbacks (`Dispatcher::dispatchAfterResponse()`) and runs them in registration order. The webhook jobs are registered before `executeDelete()`, so they run first, then the file, person-stats, face-embedding and thumb jobs. Nothing is persisted. If the worker stops before the list is done (FPM `request_terminate_timeout`, OOM kill, restart), the rest is lost, and the caller has already received success. The review's example: 4000 photos with one delete webhook at 20 ms per call need about 80 s, more than a 60 s limit. Lychee's Docker image sets `max_execution_time=3000`, but custom FPM setups can be tighter.

What a lost tail means, by job:
- `WebhookDispatchJob`: the receiver never hears about some deleted photos. Feature 031 already makes delivery best effort: `tries = 1`, and a failed call is logged and dropped (Q-031-04 A). Today on `sync` the same timeout hits before the DB delete. Some webhooks have then been sent for photos that are not deleted.
- `FileDeleterJob`: files stay on disk without DB rows. `lychee:ghostbuster` removes those on the active upload disk. On an S3 install it does not walk the local disk, where track files always live: those are removed by hand.
- `RecomputePersonStatsJob`: person counts stay stale until the next recompute.
- `DeleteFaceEmbeddingsJob`: the facial-recognition service keeps the embeddings of deleted faces.
- `RecomputeAlbumUserThumbsJob`: no effect. The FK cascade drops the thumb rows of deleted photos, and the thumb is recomputed on the next read.

**Question.** What does the spec guarantee for after-response work on `sync` when the worker stops early?

**Resolution (2026-11-22).** Option A. Encoded in [spec.md](spec.md) FR-112-04 and FR-112-12 (failure paths), Non-Goals (no guaranteed delivery on `sync`) and Documentation Deliverables (release note: an async queue stores each job when it is dispatched, so a stopped worker drops none). No webhook doc exists in this repository, so the release note carries the advice. No ADR: it applies Feature 031's existing best-effort contract to one more path. Wording corrected 2026-11-23 (CodeRabbit, PR #4997): an async queue does not deliver every delete webhook either, because a failed call is not retried (`tries = 1`), and `lychee:ghostbuster` only walks the active upload disk.

#### Option A – Best effort on `sync`, stated in the spec (recommended)

- FR-112-04 and FR-112-12 failure path: on `sync`, after-response work is best effort. If the worker stops early, the remaining jobs are lost and not retried. Some delete webhooks are never sent, files stay orphaned until `lychee:ghostbuster` runs (active upload disk only, on an S3 install it does not walk the local disk, where track files always live: those are removed by hand), and person stats and face embeddings stay stale. This matches Feature 031's no-retry delivery contract.
- Release note and webhook docs: installs that do not want a stopped worker to drop delete webhooks use an async queue, where each job is stored when it is dispatched. A failed call is still not retried (`tries = 1`), so delivery stays best effort.

Pros: no new mechanism, and it matches the existing webhook contract. The request no longer times out, which is the goal of this feature. Webhooks run before the other deferred jobs, so they are the first to be sent.
Cons: on `sync`, a killed worker drops the rest of the list without a log line, and the receiver cannot tell.

#### Option B – Durable outbox for delete webhooks

Write one row per (photo, webhook) to a new outbox table inside the delete transaction. Drain it after the response, and drain rows left behind from the scheduler.

Pros: every delete webhook is eventually sent, even after a crash.
Cons: needs a new table, a migration, a drain command and a running scheduler, which many `sync` installs do not have. It makes one event at-least-once while the others stay best effort (against Q-031-04 A). It is large enough to be its own feature.

#### Option C – Keep delete webhooks inline on `sync` (reverts Q-112-07 A)

Webhooks run before the DB delete, as today. Only the file, person, face and thumb jobs move after the response.

Pros: a killed request leaves the photos in place, so the user can retry.
Cons: a 4000-photo delete with a delete webhook still times out on `sync`, which is the problem Q-112-07 fixed. The webhooks already sent announce deletes that did not happen.

### Q-112-11 – A sale committed during a delete loses its file

**Context (2026-11-23, CodeRabbit review on PR #4997, commit `83900d328`).** FR-112-10 keeps sold variants' files. Two paths pick the file paths with a plain read that skips sold variants (`NOT EXISTS` on `order_items`), then detach or delete the rows in later statements that check `order_items` again:
- `SizeVariant\Delete::deleteChunk()` (used by `Rotate`, `SizeVariants::deleteAll()`, `SizeVariant::delete()`): no transaction at all.
- `PhotosToBeDeletedDTO::forceDelete()`: inside `executeDelete()`'s transaction, but on MySQL (`REPEATABLE READ`) the plain `SELECT` reads the transaction snapshot while the `UPDATE … WHERE EXISTS` reads the latest committed rows.

An `order_items` row that starts referencing the variant between the two (order created or fulfilled at that moment) makes the row survive, detached, while its path is already in a `FileDeleterJob`. The buyer then has an order item pointing at a variant whose file is gone. `order_items.size_variant_id` has a foreign key to `size_variants.id` (migration `2025_11_23_111052`). Master had the same gap (path list, then delete). It needs a sale and a delete of the same photo or variant within milliseconds.

**Question.** Should the delete paths guarantee FR-112-10 against a concurrent sale?

**Resolution (2026-11-23).** Option C. Encoded in [spec.md](spec.md) FR-112-10 (failure path) and Non-Goals. No code change, no lock. No ADR: it records a limit that master already has.

#### Option A – Locking read on the candidate variants (recommended)

Run the path query of both paths with `lockForUpdate()`, inside a transaction (`SizeVariant\Delete::do()` gets one, see the review fix that wraps its chunks). A locking read sees the latest committed `order_items`, so the path list and the detach agree. A sale that starts after the lock must check the foreign key on the locked row, so it waits until the delete commits. It then finds the row deleted (the sale fails, which is right for a deleted photo) or detached (the sale succeeds, the file is kept).

Pros: two one-line changes plus the transaction. No new table or job. Works the same on MySQL and PostgreSQL. SQLite has no row locks but a single writer, so a concurrent sale cannot commit in the gap there either.
Cons: a checkout touching those variants waits for the delete transaction (milliseconds per chunk). On PostgreSQL `FOR UPDATE` also locks the joined `photos` rows, which the same transaction deletes or keeps anyway. Not testable on the SQLite test DB: verified by review, recorded in the spec.

#### Option B – Drop the paths of survivors after the delete

Keep the plain read. After the detach and delete, re-read which of the collected variant ids still exist (in chunks of 500) and remove their paths from the jobs. A row our `DELETE` did not remove was sold in between.

Pros: no locks, works on every isolation level.
Cons: one more query per 500 variants (a photo has up to 8, so `forceDelete()` needs the variant ids, not only the photo ids). The ids must be carried from path collection to the jobs. More code than Option A.

#### Option C – Accept and document

State in FR-112-10 that a sale committed while the same variant is being deleted can lose its file.

Pros: no change.
Cons: a paying buyer can lose the file, which FR-112-10 exists to prevent.

### Q-112-12 – Concurrent move during the album-delete classification

**Context (2026-11-23, CodeRabbit review on PR #4997, commit `83900d328`).** `Album\Delete::findAllPhotosToDelete()` reads, for each chunk of 500 deleted album ids, the photo's links into the chunk (summed) and the photo's total links (overwritten). Nothing holds a transaction or a lock across the chunks. Example: photo P is in deleted albums A1 (chunk 1) and A2 (chunk 2). After chunk 1 (1 link, total 2), the owner moves P from A1 to surviving album S. Chunk 2 reads 1 more link (sum 2) and a total of 2 (A2, S). P is classified as only in the deleted albums, force-deleted, and its new link to S is removed.

Not new to this feature:
- Master read the in-album counts and the totals in two statements without a transaction: a move between them gives the same result. Chunking turns 2 statements into 2 per 500 albums, and the case needs the photo in at least two deleted albums in different chunks (a subtree of more than 500 albums).
- The gap between the classification and `executeDelete()` remains: a photo moved after the classification is force-deleted with any chunking, on master too. `Photo\Delete::do()` has the same gap.

**Question.** Should this feature close the race between an album delete and a concurrent move of one of its photos?

**Resolution (2026-11-23).** Option A. Encoded in [spec.md](spec.md) FR-112-09 (failure path) and Non-Goals. No code change. No ADR: it records a limit that master already has. Option B stays available as its own feature.

#### Option A – Accept and document as a known limit (recommended)

Add to FR-112-09's failure path: the classification is not atomic with a concurrent move or copy of the same photo. A photo moved out of the deleted subtree while the delete runs can still be deleted (as on master). No code change.

Pros: no change in this PR. The race needs the same photo to be moved during the delete of its album subtree, both usually done by the same owner.
Cons: the race stays, and its loss (a photo deleted) is not recoverable.

#### Option B – Decide inside the delete transaction (follow-up feature)

Inside `executeDelete()`'s transaction: remove the links of the collected photos to the deleted albums (album chunks), then force-delete only the photos with no remaining `photo_album` row, read with `lockForUpdate()`. The counting goes away. A move that commits after the check fails on the `photo_album.photo_id` foreign key of the deleted photo.

Pros: closes the race, including the classification-to-delete gap. Removes the summed counts.
Cons: reshapes `findAllPhotosToDelete()` and the `PhotosToBeDeletedDTO` contract. `Photo\Delete::do()` should follow for consistency. Large for a review fix: better as its own feature.

#### Option C – One snapshot for the chunk reads

Wrap the chunk loop in a transaction at `REPEATABLE READ` or higher.

Pros: small.
Cons: PostgreSQL defaults to `READ COMMITTED` (one snapshot per statement) and rejects `FOR UPDATE` with `GROUP BY`. The classification-to-delete gap stays. Not portable, does not close the race.

### Q-112-13 – Album delete times out in the DB transaction on a slow disk

**Context (2026-11-23).** New report from a production container running master: `Maximum execution time of 30 seconds exceeded` while `PhotosToBeDeletedDTO::forceDelete()` runs `DELETE FROM size_variants WHERE photo_id IN (…)` (master line 187), inside `executeDelete()`'s transaction, called from `Album\Delete::do()` line 105. No file, person-stats or face-embedding job had been dispatched yet. Same DB engine as the dev setup, but the server's DB is on an HDD (dev: SSD). The shop is not used (`order_items` empty). The container cannot easily be inspected.

**Evidence.**

- The 30 s is not the image's `php.ini` value (`max_execution_time=3000`). It is Octane's `max_execution_time` (`config/octane.php`, `LYCHEE_MAX_EXECUTION_TIME`, default 30), which `vendor/laravel/octane/bin/frankenphp-worker.php` applies with `set_time_limit()`.
- FrankenPHP is a thread-safe PHP build: there the limit counts wall-clock time, so time spent waiting for the DB counts (under PHP-FPM on Linux it does not).
- Octane's `Worker::handle()` sends the response and then calls `terminate()` in the same handler call. On `sync`, the after-response jobs of FR-112-04 therefore most likely run under the same 30 s budget. FR-112-04's failure path names FPM `request_terminate_timeout` but not this limit.
- Q-112-01 measured 0.3 s of SQL for 4000 photos on SQLite/SSD. Photo ids are random strings, so the rows a delete touches sit on index pages spread across the tables: on an HDD most of them are random reads. The DB-phase cost grows with the number of photos, so any limit is reached by a large enough album.
- Also inside the transaction, before the deletes: `AlbumSaved` for albums whose explicit cover/header is deleted. On `sync` each `RecomputeAlbumStatsJob` runs inline and propagates to the root.
- The Docker compose files default to `QUEUE_CONNECTION=database` with a `lychee_worker` container (`queue:work --timeout=3600`, CLI, no PHP time limit). `.env.example` and plain installs default to `sync`. Whether the reporting server runs a worker is unknown.
- Owner (2026-11-23): raising the execution time is not the solution.

**Evidence (2026-11-27, server `warning.log`, `DB_LOG_SQL=true`, queries ≥ 100 ms, master).** About 7000 photos (14 chunks of 500):

- 21:33:53 tree check 0.44 s; 21:33:54–55 photo classification 1.5 s (two `photo_album` queries).
- 21:33:57–21:34:16: `collectSizeVariantPathsByPhotoID()` for disk `images`, 14 queries of 0.6–4.1 s (about 1 s each, about 20 s in all).
- 21:34:16–21:34:22: the same 14 chunks for disk `s3`, about 1 s each. The log stops after 9 of them, near the 30 s limit. The live-photo queries and the deletes had not run yet.
- `select … from size_variants where photo_id = ? and type in (?, ?)` takes 100–126 ms for a single photo. Its `EXPLAIN` on the server (2026-11-27) is fine: `range` on `size_variants_photo_id_type_unique`, `key_len` 101 (`char(24)` utf8mb4 + nullable flag + `int` type: both columns, no charset conversion), 2 rows. So these are cold page reads from the HDD, not a missing index.
- The `s3` pass reads the same photo ids right after the `images` pass and is just as slow, so the pages read by one pass are gone by the next. Cause: the pages of 7000 photos do not fit in the InnoDB buffer pool (128 MB on the server, see below). `photos.id` is a random string, so each photo of a chunk sits on its own page of `photos`, and its size variants on another page of `size_variants`.
- Master and this branch read the same rows in four passes over all the photos (variant paths and live-photo paths, once per disk), then again in the deletes. With a buffer pool smaller than one pass, every pass reads from the disk again.
- The bindings end in `NOT IN (NULL, NULL, 447420)`: `order_items` has 3 rows (the shop is not used), two with a NULL `size_variant_id`. On master every path query therefore returns no row, and the files of deleted photos are never deleted on this instance (Q-112-04, fixed by FR-112-10).
- The server's schemas (`SHOW CREATE TABLE`, 2026-11-27) match the migrations: `photos.id` and `size_variants.photo_id` are both `char(24)` utf8mb4_unicode_ci, so the join compares index to index. The int display widths suggest MariaDB.
- `photos` has 16 secondary indexes and `size_variants` 3. Deleting a chunk of 500 photos changes about 500 × 17 + 3500 × 4 ≈ 22 500 index entries, on pages spread by the random ids. MariaDB has no change buffer since 11.0 (off by default since 10.5.15 / 10.6.7), so each such page that is not in memory is read from the disk during the delete. This cost is in the deletes, not in the read passes.
- Server `innodb_buffer_pool_size`: 128 MB, the default (owner, 2026-11-27). The dev copy (MariaDB 11.8.6, assumed close to the server: 534 029 size variants against the server's `AUTO_INCREMENT` of 619 700) has `photos` at 123 MB (82 811 rows) and `size_variants` at 271 MB. One pass over 7000 random photos touches about half the pages of both tables, about 200 MB, so it does not fit in the buffer pool and every pass reads from the disk again. One chunk of 500 photos fits.
- Not known yet: how long the live-photo passes and the deletes take on the server (not reached before the limit).

**Question.** How should the album and photo deletes stay within the request limit when the DB phase itself is slow?

**Resolution (2026-11-27).** For this feature: Option C, the first step of every option, encoded in [spec.md](spec.md) FR-112-14, NFR-112-05, S-112-24/25, Overview and Non-Goals (owner: "let's refocus on the solution", after the proposal to add Option C to this branch). The end state, Option A (the owner's trash album, recommended) or Option B (deletion flag), is not decided here: it goes to a follow-up feature, now Feature 119 (`features/119-background-photo-delete/`), whose own open questions carry it (Q-119-01 also corrects this entry: photos without an album link are already hidden from other users). No ADR: FR-112-14 restructures one method without changing its contract.

Options reordered on 2026-11-27. First after the server log: the measuring step of the former Option C is done. Then after the owner proposed a deletion flag on `base_albums` (now Option B) and moving the photos into a trash album (now Option A): the per-chunk restructure is Option C, queuing is Option D and the resumable delete is Option E. A variant that moved the albums themselves into a trash album (a misreading of the owner's proposal) was dropped: it rewrites the tree on each delete, needs the moved permissions removed, and leaves photo-selection deletes per photo.

Facts for the flag (2026-11-27): the container runs no scheduler (`entrypoint.sh` has only `web` and `worker` modes, and `routes/console.php` schedules nothing). About 100 files read albums: 72 through the `Album` models (about 40 of them use `AlbumQueryPolicy`) and 28 through raw `DB::table('albums' | 'base_albums')`.

#### Option A – Move the photos into a trash album, delete the albums at once, purge the trash in the background (recommended)

The owner's proposal (2026-11-27). One hidden system album, the trash, holds every photo waiting for deletion.

- Album delete request: classify the photos as today (only in the deleted subtree: to delete; also elsewhere: detach). Each photo to delete gets one link to the trash and loses its links to the deleted albums. The detached photos lose their links to the deleted albums. The album rows are deleted as today (cost per album). The request dispatches the purge and returns.
- Photo delete request: the same move for the selected photos (their links replaced by one trash link), bounded the same way.
- Cost: the request writes only `photo_album` and the album rows. On the dev copy `photo_album` is 42 MB, against about 394 MB for `photos` and `size_variants`, so it mostly stays in the buffer pool. `photos` and `size_variants`, where the time goes on the server, are only touched by the purge. The request still grows with the number of photos, but over the link table only: on the server the classification of 7000 photos took about 1.5 s, and the link move should be of the same order (not measured).
- Albums are really deleted in the request: no flag and no filter on album read paths.
- Hiding: the trash is private, so the access policies already hide its photos from everyone except their owner and admins. Each photo keeps one link (to the trash), so Unsorted (photos without a link) does not show them. For owners and admins, smart albums, search, timeline, map, tag and person albums still list them unless `PhotoQueryPolicy` excludes the trash. Its searchability and visibility filters are shared by those paths (22 files). Direct access to a trashed photo by id is refused in `PhotoPolicy`. Photo reads outside the policy (raw reads in 14 files, plus model queries) need an audit.
- The trash album: created by a migration, owned by the admin, hidden from album listings and the tree, and protected: no upload, move, share, rename or delete, and no stats or size recompute. It is filled by direct `photo_album` writes, so no `AlbumSaved` fires, but the listeners must still skip it.
- Purge: Option C's per-chunk structure over the trash's photos, one transaction per chunk, never photo by photo (one log flush per photo on an HDD, and the per-photo fan-out fixed in Q-112-02). The trash content is the durable to-do list: a killed worker or request loses nothing, and the next run continues.
- Where the purge runs: in the worker on async connections. On `sync` (no worker and no scheduler in the container) after the response, under the Octane limit, plus an artisan command and an admin Maintenance entry ("empty trash") for whatever is left.
- A table of queued photo ids instead of an album would avoid the system album. But the photos would then have no link: Unsorted and every other "no album" path would need a filter, and non-owners would no longer be hidden for free.
- Delivery: Option C first, in this feature. The trash as a new feature, with the schema and data change approved first (escalation policy).

Pros: albums disappear for real at once. The heavy tables leave the request, for album and photo deletes alike. The deletion runs in the background and survives a killed worker. Only the photo side needs a filter, mostly in one shared policy. A base for a later "restore from trash".
Cons: a system album to hide and protect wherever albums are listed or targeted. Owners' and admins' photo listings must exclude the trash (a missed path shows a deleted photo to its owner). The request still grows with the number of photos, on the link table. Owner counts (for example storage used) include trashed photos until they are purged.

#### Option B – Flag the albums on `base_albums`, then purge them in the background

The owner's proposal (2026-11-27): the deletion flag is a column on `base_albums`, and the deletion itself runs in the background. The request authorizes, flags the selected albums and their whole subtree, dispatches the purge and returns. The purge runs Option C's per-chunk structure, one transaction per chunk. It classifies the photos when it runs, so a photo moved out of a flagged album in the meantime survives. Albums are deleted after their photos. The purge is idempotent: a stopped run leaves flagged albums behind, and the next run continues them.

- Column: one nullable timestamp on `base_albums` (for example `deleted_at`) rather than a boolean, so the purge takes the oldest first and a stuck purge shows by its age. Tag and person albums share it. Their delete is already a few row deletes, so they can also stay deleted at once.
- Subtree: one `UPDATE base_albums` joined to `albums` on the `_lft`/`_rgt` range of each selected album. The cost depends on the number of albums, not of photos. Flagging only the selected albums would make every album read look up its ancestors.
- Hiding: `Album` and `TagAlbum` load `base_albums` as an eager-loaded relation (`base_class`), not through a join. A global scope therefore adds `NOT EXISTS (flagged base_albums row with the same id)` to each album query, a primary-key lookup. A join would clash with the policies' own joins on `base_albums`. Laravel's `SoftDeletes` does not fit, since it expects the column on the model's own table. The nestedset soft-delete support is not used either: `Album` overrides `deleteDescendants()` as a no-op and routes `delete()` to `Album\Delete`. Still to filter by hand: the album joins in `AlbumQueryPolicy` and `PhotoQueryPolicy` (query-builder joins), and the 28 raw `albums` / `base_albums` reads (9 read only `base_albums` and filter on the column directly). Uploading into, moving into and sharing a flagged album are refused.
- Photos that are only in flagged albums stay visible to their owner (smart albums, search, timeline, map, tag and person albums) until the purge reaches them. Flagging the photos too would hide them at once, but that `UPDATE` reads every photo page, the same cost as one of today's passes, so the request would grow with the number of photos again.
- Where the purge runs: in the worker on async connections. On `sync` there is no worker and no scheduler, so it runs after the response, under the same Octane limit. What it finished is kept (each chunk commits), and the rest needs a way to resume: an artisan command and an admin Maintenance entry that purge whatever is still flagged.
- Delivery: two steps. Option C first, in this feature, since the purge needs it and it helps every install now. The flag and the purge as a new feature, with the schema change approved first (escalation policy).

Pros: the deletion runs in the background, and the request cost depends on the number of albums only, on every disk and queue connection. Albums disappear at once. A killed worker or request loses no deletion: the flag stays and the purge resumes. No more single transaction over the whole delete.
Cons: the largest change: a migration, a global scope plus the policy joins and raw reads to filter (a missed one shows a deleted album), and writes into flagged albums refused. Photos can stay visible to their owner until the purge reaches them. On `sync` a large purge still needs a manual or later resume.

#### Option C – Read each chunk once

Restructure `PhotosToBeDeletedDTO::forceDelete()` so that each chunk of 500 photos is handled from start to end before the next one: covers and headers reset, one path query for every disk and kind (`storage_disk`, the variant paths, `live_photo_short_path` and a sold flag in the select list, split in PHP), the person and face lookups, the sold-variant detach and the row deletes. A chunk's pages are then read from the disk once and stay in the buffer pool for every statement of that chunk.

Pros: about one disk read per page instead of four passes plus the deletes, whatever the buffer pool size. Helps every install and every queue connection. No REST or schema change. Option A's purge reuses it.
Cons: the deletes still change about 17 index entries per photo and 4 per size variant, on random pages: Option C removes the extra read passes, not that cost. The cost still grows with the number of photos, so a large enough album on the same disk still reaches 30 s. The gain on this server is an estimate until measured again with `DB_LOG_SQL` (the two measured passes took about 1 s per chunk each).

#### Option D – Option C plus queuing the whole delete on async connections

The `DELETE` endpoints validate and authorize, then dispatch one delete job (ids plus the acting user) and return. The job runs `Album\Delete::do()` / `Photo\Delete::do()` in the worker. On `sync` the delete stays inline, with Option C's gain.

Pros: with a worker (the Docker compose default), no request limit applies at all, whatever the album size or disk speed. No schema change.
Cons: on `sync` only Option C's gain. The album or photos stay visible until the worker runs, and a failure is no longer reported to the caller. A worker killed during the job loses that deletion (one transaction, nothing persisted). Lifts the Non-Goal taken in Q-112-01 (Option B there).

#### Option E – Bounded, resumable delete across requests

Each `DELETE` call removes at most a bounded slice of photos (count or time budget) in its own transaction and returns how much is left. The frontend repeats the call, with progress, until the albums themselves are deleted.

Pros: works on every queue connection, `sync` included, without a worker. Each request is bounded whatever the disk. Errors still reach the caller.
Cons: REST contract change (response body, repeated calls), frontend loop and progress UI, and every other caller (admin bulk edit, API clients) must loop. If the user leaves midway, the album stays with fewer photos: consistent, and finished by deleting again.
