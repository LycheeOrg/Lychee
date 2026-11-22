# Feature 112 – Fix Album Delete Timeout

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-11-22 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #112 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Deleting a large album (≈4000 photos) or a large photo selection times out. The DB work is fast (0.3 s of SQL for 4000 photos × 7 size variants). The time goes to two places:
- File deletion: one `FileDeleterJob` per (disk, kind) carries every path (28 000 for 4000 photos), writes one log line per file, and calls S3 twice per file. On the default `sync` queue it runs inside the HTTP request.
- Multi-photo delete with the webhook feature on: one `PhotoWillBeDeleted` per photo, each fanning out to 15 `RecomputeAlbumUserThumbsJob`s plus per-photo queries (504 s for 4000 photos on `sync`).

The 2026-11-15 reviews found existing defects on lines this feature rewrites, and gaps around it:
- `order_items.size_variant_id` is NULL for every item delivered by URL (print, pixel, FULL, admin `download_link`), for album items and for unfulfilled items. The exclusion list binds that NULL, and `NOT IN (…, NULL)` matches nothing. Photo delete then leaves files on disk, and size-variant delete removes files but keeps their rows. The "keep sold variants" update also sets `photo_id = NULL` on every sold variant in the instance, not only on those of the deleted photos.
- The delete permission checks (`PhotoPolicy`, `AlbumPolicy`) bind every requested id before the action runs.
- With a `photo.delete` webhook on `sync`, one webhook HTTP call per deleted photo runs inline, before the DB delete.
- `size_variants` keeps `unique(photo_id, type)`. `SizeVariant\Delete` leaves sold variants attached, so `Rotate` (delete all variants, then insert new ones) fails for a photo with a sold variant. With any NULL `size_variant_id` in `order_items` it deletes no row at all, so every rotate fails.
- Photos that still exist have already lost sold variants to the old instance-wide update.

Affected layers: `App\Jobs\FileDeleterJob`, the three producers of file-deletion jobs (`Actions\Album\Delete`, `DTO\Delete\PhotosToBeDeletedDTO`, `Actions\SizeVariant\Delete`), `DTO\Delete\AlbumsToBeDeletedDTO`, `Actions\Photo\Delete`, `Actions\Shop\PurchasableService`, `Policies\PhotoPolicy`, `Policies\AlbumPolicy`, `Listeners\RecomputeAlbumUserThumbsOnPhotoChange`, `Listeners\ManagedCacheAlbumListingInvalidator`, `Listeners\WebhookListener`, `EventServiceProvider`, and one data migration. No REST contract, schema or frontend change.

Decisions: Q-112-01 A, Q-112-02 A (with the thumb refresh computed once per delete request), Q-112-03 A (no query binds more than 1000 parameters), Q-112-04 A (fix both `order_items` defects here), Q-112-05 A (the delete permission checks are bounded too), Q-112-06 A (every job a delete action dispatches after its commit runs after the response on `sync`), Q-112-07 A (delete webhooks after the response too), Q-112-08 A (every delete path detaches sold variants), Q-112-09 A (a migration re-links sold variants detached from photos that still exist), Q-112-10 A (after-response work on `sync`, delete webhooks included, is best effort). See [open-questions.md](open-questions.md).

## Goals
- File deletion costs O(files) with a small constant, in bounded jobs.
- On the `sync` queue, none of the jobs the delete code starts itself (file deletion, person stats, face embeddings, thumb refresh) and none of the `photo.delete` webhook calls run before the HTTP response is sent. Jobs started by other listeners (album stats, album size) still run before it.
- Deleting N photos does a constant number of thumb-refresh resolutions and dispatches each `RecomputeAlbumUserThumbsJob` at most once, whatever N is.
- `PhotoWillBeDeleted` is fired only when a `photo.delete` webhook can consume it.
- No query of a delete request, permission checks included, binds more than 1000 parameters (MySQL fails above that), whatever the selection, subtree or shop size.
- Sold size variants are kept whatever else `order_items` holds. Every delete path detaches them from their photo (so the photo can get a new variant of that type), and only the variants being deleted are touched.
- Photos that lost sold variants to the old instance-wide update get them back on upgrade, where the unique index allows it.

## Non-Goals
- Making the album/photo DB deletion itself a queued job (Q-112-01 Option B).
- Sending `photo.delete` webhooks for photos removed together with their album (album delete never fired `PhotoWillBeDeleted`; unchanged).
- Changing the `photo.delete` webhook payload or the `RecomputeAlbumUserThumbsJob` semantics.
- Bounding `whereIn` lists outside the album/photo/size-variant delete flow, its permission checks (FR-112-11) and the two listeners named in FR-112-09. The move-by-id checks share the `AlbumPolicy` grant helpers, so they get the bound too, with unchanged results.
- Changing the payload or semantics of the other jobs returned by `PhotosToBeDeletedDTO::executeDelete()` (`RecomputePersonStatsJob`, `DeleteFaceEmbeddingsJob`). Only their dispatch moves after the response on `sync` (FR-112-04).
- Changing how other event listeners in the delete flow dispatch their jobs (album stats, album size, caches). `WebhookListener`'s delete webhooks are the one exception (FR-112-12).
- Protecting files referenced by a free-text `order_items.download_link` (for example a pasted Lychee size-variant URL). The DB does not track them, so deleting the photo breaks such a link, as today.
- Any other shop behaviour (fulfilment, how `size_variant_id` is assigned).
- Keeping files shared by same-checksum duplicates (`Rotate` points duplicates at the same files, so deleting one deletes the shared files). Pre-existing; only the `collectSizeVariantPathsByPhotoID()` docblock, which claims those files are skipped, is corrected.
- Re-enabling foreign-key checks when `AlbumsToBeDeletedDTO::executeDelete()` fails (pre-existing).
- Guaranteed delivery of delete webhooks or of the other deferred jobs on `sync` (no outbox, no retry). After-response work there is best effort (Q-112-10 A, FR-112-04, FR-112-12).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-112-01 | `FileDeleterJob` logs one summary line per job instead of one line per file. | `jobs` channel gets one `debug` line naming the disk and the number of paths processed (missing files and failures included). | Empty list: no file I/O, one "No files to delete" line (as today; only reachable by direct construction once FR-112-03 holds). | Errors still collected; the first one is rethrown as `MediaFileOperationException` after the loop (as today). | `jobs` channel: `Processed {n} paths on {disk}.` | Q-112-01 A |
| FR-112-02 | On non-local disks (S3), `FileDeleterJob` deletes each file without an `exists()` pre-check. | One `delete()` call per file. A missing key is a no-op success. | — | `delete()` returning `false` records a `FileDeletionException` for that path; a thrown exception is recorded; the first is rethrown after the loop. The S3 branch loses its `@codeCoverageIgnore` (covered by S-112-19). | — | Q-112-01 A |
| FR-112-03 | File-deletion jobs carry at most 500 paths each. Empty lists produce no job. | The paths of every kind for one disk (variant, watermarked, live-photo and track files) are merged, then batched: a producer with P paths for one disk dispatches ⌈P/500⌉ `FileDeleterJob`s, built by `FileDeleterJob::batches(StorageDiskType, array): list<FileDeleterJob>`. | P = 0 → no job. | — | — | Q-112-01 A |
| FR-112-04 | When the queue connection is `sync` (`config('queue.default') === 'sync'`, as elsewhere in the code base), every job a delete action dispatches after its DB commit runs after the HTTP response is sent: the `FileDeleterJob`s (track files included), the `RecomputePersonStatsJob` and `DeleteFaceEmbeddingsJob` returned by `executeDelete()`, the `RecomputeAlbumUserThumbsJob`s of FR-112-07, and the `WebhookDispatchJob`s of FR-112-12. Other connections queue them normally. All go through one helper (DO-112-03). | `sync`: each job is dispatched with `afterResponse()`; the request returns once the DB transaction has committed. Async: `dispatch()` as today. In console (`lychee:sync` import deleting missing photos/albums) the deferred jobs run when the command exits. | Applies to `Album\Delete::do()`, `Photo\Delete::do()` and `forceDeletePhoto()`, `SizeVariant\Delete::do()`, whatever the entry point (see API Routes / Services). Jobs dispatched by other event listeners are unchanged. On `sync`, person counts and thumbs are refreshed a moment after the response (accepted, Q-112-06 A). | A failure after the response is reported through the exception handler (Laravel runs each after-response job inside `rescue(report: true)`); the remaining jobs still run; the caller is not told. On `sync` this work is best effort. The jobs live only in memory and run in dispatch order. If the worker stops before the list is done (FPM `request_terminate_timeout`, OOM kill, restart), the remaining jobs are lost, with no retry and no log line. Files then stay orphaned until `lychee:ghostbuster` runs, person stats and face embeddings stay stale, and thumbs are recomputed on the next read. Async connections store each job when it is dispatched, so nothing is lost there. | — | Q-112-01 A, Q-112-06 A, Q-112-10 A |
| FR-112-05 | `RecomputeAlbumUserThumbsOnPhotoChange` and `ManagedCacheAlbumListingInvalidator` no longer listen to `PhotoWillBeDeleted`; their `handlePhotoWillBeDeleted` methods are removed. | Only `WebhookListener` consumes `PhotoWillBeDeleted`. | — | — | — | Q-112-02 A |
| FR-112-06 | `Photo\Delete` fires `PhotoWillBeDeleted` only when the webhook feature is active and at least one enabled webhook subscribes to `photo.delete`. | One query for such webhooks (`Webhook::enabled()->forEvent(PhotoWebhookEvent::DELETE)`, the filter `WebhookListener` uses); none → no event, no size-variant URL build. Some → one event per hard-deleted photo with today's payload. | — | — | — | Q-112-02 A |
| FR-112-07 | `Photo\Delete` refreshes cached album thumbs once per delete request: it resolves the affected thumb albums once over all hard-deleted photos before the DB delete and dispatches one `RecomputeAlbumUserThumbsJob` per affected album after the DB delete. Independent of the webhook feature. On `sync` these jobs run after the response (FR-112-04). | Affected set = every `SmartAlbumType`, tag albums sharing a tag with the photos, person albums sharing a non-dismissed person, and tag/person albums currently caching one of the photos as thumb. Each (kind, album) dispatched once. Applies to `do()` and `forceDeletePhoto()`. Album delete does no thumb refresh (FK cascade on `album_user_thumbs.photo_id` plus lazy recompute on read, as today). | No hard-deleted photo → no resolution and no job. | — | — | Q-112-02 A (owner: thumb jobs computed once) |
| FR-112-08 | The affected-album resolution of FR-112-07 is the same code the listener uses for its other photo events. | `RecomputeAlbumUserThumbsOnPhotoChange` and `Photo\Delete` share one resolver (`App\Actions\Album\AlbumUserThumbTargets::resolve(array $photo_ids)` returning the (kind, album_id) list). The listener dispatches the targets normally (`dispatch(list)`); `Photo\Delete` turns them into jobs (`jobs(list)`) and hands them to the after-response helper. The resolved set is the same as the listener's today. | — | — | — | Q-112-02 A |
| FR-112-09 | No query in the delete flow binds more than 1000 parameters. Id lists are de-duplicated, then split into chunks of 500 (`App\Constants\QueryLimits::ID_CHUNK_SIZE`). A query that takes two lists binds at most 500 of each. | Sites: `Photo\Delete::do()` (3 queries) and `dispatchWillBeDeletedEvents()`, `PurchasableService::deleteMulitplePhotoPurchasables()`, `Album\Delete::do()` (person/tag album lookups), `findAllPhotosToDelete()`, `findAllAlbumsToDelete()`, `getGaps()`, `deleteTagAlbums()`, `deletePersonAlbums()`, `AlbumsToBeDeletedDTO` (its local 1000 chunk becomes `ID_CHUNK_SIZE`), `PhotosToBeDeletedDTO::forceDelete()` (album/tag-album re-reads), `AlbumUserThumbTargets::resolve()`, `ManagedCacheAlbumListingInvalidator::evictPersonTagsForPhotos()`, plus FR-112-10 and FR-112-11. Results are merged across chunks and de-duplicated, except in `findAllPhotosToDelete()`: there the per-photo count of links into the deleted albums is **summed** across album chunks (keyed by photo id, no sorted merge) and compared with the photo's total link count. `AlbumUserThumbTargets::resolve()` binds only photo-id chunks: tag and person albums are reached through subqueries (`photos_tags` → `tag_albums_tags`, `faces` → `person_album_persons`), and the backward lookup joins `album_user_thumbs` (filtered on the photo chunk) to `tag_albums` and to `person_albums`, so it binds only the photo chunk and replaces the `find()` per album. | Empty list → no query. Album ids are de-duplicated before chunking, so a photo link is never counted twice. | — | — | Q-112-03 A (owner: >1000 parameters fails on MySQL); review 2026-11-15 |
| FR-112-10 | Sold size variants are excluded with NULL-safe SQL, and every delete path detaches them from their photo; only the variants being deleted are touched. | `PhotosToBeDeletedDTO` (path collection) and `SizeVariant\Delete::do()` (path collection and row delete) treat a size variant as sold when `EXISTS (SELECT 1 FROM order_items oi WHERE oi.size_variant_id = sv.id)`. No `order_items` id list is loaded or bound. Sold variants get `photo_id = NULL` and keep their row and their file; unsold ones lose both. In `forceDelete()` this runs for each force-deleted chunk (`photo_id IN (chunk)`) before that chunk's `size_variants` delete; in `SizeVariant\Delete` for the given variant ids. Sold variants of other photos are untouched. The `collectSizeVariantPathsByPhotoID()` docblock is corrected (it does not skip files shared with duplicates). | `order_items` rows with NULL `size_variant_id` (print, pixel, FULL, `download_link`, album or unfulfilled items) protect nothing and do not change the result. An item with both a `size_variant_id` and a `download_link` still protects its variant. After `Rotate` of a photo with a sold variant, the photo gets a new variant of that type and the order item still points at the detached one. | — | — | Q-112-04 A, Q-112-08 A |
| FR-112-11 | The delete permission checks bind at most 1000 parameters and return the same result as today for any input. | `PhotoPolicy::canDeleteById()`: the owner count is summed across photo-id chunks and compared with the de-duplicated id count; the "photo without album" check stops at the first chunk that finds one; parent album ids are merged and de-duplicated, then passed to `AlbumPolicy::canDeleteContentById()`. `AlbumPolicy::hasGrantOnParentsById()`, `hasGrantOnAllById()` and `countGrantedById()` chunk their album ids, sum counts across chunks and compare with the de-duplicated id count. `countGrantedById()` matches group permissions through a subquery (`user_group_id IN (SELECT user_group_id FROM users_user_groups WHERE user_id = ?)`). It binds the user id only, not the user's group ids as today. Groups are not chunked. Each album chunk counts distinct albums and the chunks do not overlap, so the summed count stays right when one album matches several groups. | Owner of every id → allowed. One foreign id without grant, in any chunk → denied. A user in 600 groups with 1200 album ids → same result as today, at most 1000 parameters per query. Empty list → as today (`may_upload`). | Denied → 403, as today. | — | Q-112-05 A; review 2026-11-22 (group bound) |
| FR-112-12 | `WebhookListener::handlePhotoWillBeDeleted()` hands its `WebhookDispatchJob`s to `DispatchAfterDelete`. | `sync`: one job per (photo, webhook) dispatched after the response; async: queued normally. Payload unchanged; the event still fires before the DB delete with its snapshot. The `photo.add` and `photo.move` handlers are unchanged. | No enabled `photo.delete` webhook → no event, no job (FR-112-06). | A webhook is still sent when the DB delete then fails, as today. A failing call is handled by `WebhookDispatchJob` as today (`tries = 1`). On `sync`, delivery is best effort, like every webhook (Q-031-04 A). If the worker stops after the response, the delete webhooks not yet sent are lost (FR-112-04). They are dispatched before the DB delete, so they run before the other deferred jobs. Installs that need every delete webhook use an async queue (release note). | — | Q-112-07 A, Q-112-10 A |
| FR-112-13 | A data migration re-links sold size variants that the old instance-wide update detached from photos that still exist. | Candidates: `size_variants.photo_id IS NULL` with an `order_items` row where `size_variant_id = sv.id` and `photo_id IS NOT NULL` (the FK clears `order_items.photo_id` when the photo is deleted, so the photo exists). For each (photo, type) without a current variant, the candidate with the highest id gets `photo_id = order_items.photo_id`. Id lists are processed in chunks of 500. | The photo already has a variant of that type (`unique(photo_id, type)`) → left detached, row and file kept. A second run changes nothing. | `down()` does nothing (data repair). | Number of re-linked and skipped rows logged. | Q-112-09 A |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-112-01 | Deleting 4000 selected photos with the webhook feature on and no `photo.delete` webhook does not grow `RecomputeAlbumUserThumbsJob` dispatches or listener queries with N. | 504 s measured on `sync`. | Test: deleting several photos dispatches each thumb job (kind, id) exactly once and no `PhotoWillBeDeleted`. | Bus/Event fakes. | Q-112-02 |
| NFR-112-02 | No `FileDeleterJob` payload exceeds 500 paths. | Worker `--timeout`, payload size. | Unit test on `FileDeleterJob::batches()`, feature test on album delete. | — | Q-112-01 |
| NFR-112-03 | DB records are still removed before any file. | Today's ordering; a failed file delete never leaves dangling DB rows. | Existing delete tests stay green. | — | Q-112-01 A |
| NFR-112-04 | Every query issued by a delete request binds at most 1000 parameters for any input size, permission checks included. | MySQL fails above 1000 parameters (owner). | Feature tests over HTTP (request validation and permission checks included) delete 1200 selected photos (with purchasables, 1200 `order_items` rows present), an album whose subtree holds more than 1000 albums and photos, and more than 1000 album ids in one request. `DB::listen` asserts that no query binds more than 1000 parameters. The two listener paths have their own 1200-id cases. | — | Q-112-03 A, Q-112-05 A |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-112-01 | `FileDeleterJob` on 3 local files: files gone, one summary log line. |
| S-112-02 | `FileDeleterJob::batches()` with 1201 paths → 3 jobs (500, 500, 201); with 0 paths → none. |
| S-112-03 | Album delete on `sync` queue: `FileDeleterJob`s (and `RecomputePersonStatsJob` / `DeleteFaceEmbeddingsJob` when faces are involved) are dispatched after response; DB rows already gone when the request returns. |
| S-112-04 | Album delete on an async queue: the same jobs queued normally, none after response. |
| S-112-05 | Multi-photo delete, webhook feature on, no `photo.delete` webhook: no `PhotoWillBeDeleted` fired. |
| S-112-06 | Multi-photo delete, webhook feature on, enabled `photo.delete` webhook: one `PhotoWillBeDeleted` per hard-deleted photo. |
| S-112-07 | Multi-photo delete (webhook feature on or off): each affected (kind, album) `RecomputeAlbumUserThumbsJob` dispatched exactly once, after the photos are gone. |
| S-112-08 | Delete where every photo stays in another album (soft delete only): no thumb resolution, no thumb job. |
| S-112-09 | `PhotoWillBeDeleted` reaches only `WebhookListener`. |
| S-112-10 | Delete 1200 selected photos over HTTP, with purchasables and 1200 sold size variants in `order_items`: succeeds, no query (permission checks included) binds more than 1000 parameters. |
| S-112-11 | Delete an album whose subtree holds more than 1000 albums and more than 1000 photos, one photo linked to two sub-albums that fall in different chunks: succeeds, that photo is force-deleted (not detached), no query binds more than 1000 parameters. |
| S-112-12 | Delete more than 1000 album ids (regular and tag albums) in one request over HTTP, through `DELETE /api/v2/Album` and the admin `DELETE /api/v2/BulkAlbumEdit`: succeeds, no query binds more than 1000 parameters. |
| S-112-13 | Photo delete with an `order_items` row delivered by URL present (NULL `size_variant_id`, `download_link` set): the deleted photo's size-variant rows are gone and its files are queued for deletion. |
| S-112-14 | Photo delete while a photo that is not deleted has a sold size variant: that variant keeps its `photo_id`. The deleted photo's sold variant keeps its row (with `photo_id` NULL) and its file is not queued. |
| S-112-15 | `SizeVariant\Delete` with an `order_items` row delivered by URL present: unsold variants lose rows and files; a sold variant keeps its row and file and gets `photo_id` NULL. |
| S-112-16 | Delete permission checks with 1200 ids: owner of all → allowed; one foreign id without grant in the last chunk → denied; non-owner with the delete grant on every parent album → allowed; non-owner in 600 groups who holds the delete grant on every parent through one of them → allowed, no query binds more than 1000 parameters. |
| S-112-17 | `AlbumUserThumbTargets::resolve()` and `evictPersonTagsForPhotos()` with 1200 photo ids: same targets and evicted persons as unchunked, no query binds more than 1000 parameters. |
| S-112-18 | Photo delete on `sync` (photos with faces, smart thumbs cached): file, person-stats, face-embedding and thumb jobs are all dispatched after response, none inline. On an async queue all are queued normally. |
| S-112-19 | `FileDeleterJob` on the S3 disk (mocked, non-local adapter): one `delete()` per path and no `exists()` call; a `delete()` returning `false` → every path is still tried, then `MediaFileOperationException`. |
| S-112-20 | Rotate a photo whose original is sold: succeeds; the photo has a new original; the sold variant keeps its row and file with `photo_id` NULL and the order item still points at it. |
| S-112-21 | Photo delete with an enabled `photo.delete` webhook on `sync`: one `WebhookDispatchJob` per (photo, webhook) dispatched after response, payload unchanged; on an async queue queued normally. |
| S-112-22 | Migration on the dev DB (manual, no test per owner rule): a sold variant detached from a surviving photo is re-linked; one whose photo has a newer variant of that type stays detached; a second run of `up()` on the same data re-links nothing and leaves the rows unchanged. `php artisan migrate` skips a migration that is already recorded, so the second run calls `up()` directly, inside a transaction that is rolled back (procedure in T-112-20). |

## Test Strategy
- **Unit:**
  - `FileDeleterJobTest` gains summary-log, `batches()` and mocked-S3 cases (S-112-01/02/19).
  - `ManagedCacheAlbumListingInvalidatorTest` loses its `PhotoWillBeDeleted` row and gains a 1200-id `PhotoSaved` case (S-112-17).
  - New `AlbumUserThumbTargetsTest` covers smart/tag/person/backward resolution, dedup, empty input and a 1200-id case (S-112-17).
  - `tests/Unit/Actions/Photo/DeleteTest.php` gains S-112-13/14; `tests/Unit/Actions/SizeVariant/DeleteTest.php` gains S-112-15.
- **ImageProcessing:** `tests/ImageProcessing/Photo/PhotoRotateTest.php` gains S-112-20.
- **Feature_v2:**
  - New `DeleteBindLimitTest` asserts via `DB::listen` that no query binds more than 1000 parameters (S-112-10..12).
  - New `DeletePolicyBindLimitTest` (S-112-16), over `DELETE /api/v2/Photo` and `DELETE /api/v2/Album`.
  - New `PhotoDeleteFanOutTest` (S-112-05..09, S-112-21) over `DELETE /api/v2/Photo` with `Bus::fake`/`Event::fake`. S-112-09 asserts the listeners registered for `PhotoWillBeDeleted`. S-112-07's "after the photos are gone" is checked without the Bus fake: a `Queue::before` hook records whether the photos still exist when each `RecomputeAlbumUserThumbsJob` starts. The HTTP test helper calls the kernel's `terminate()`, so the after-response jobs run inside the request call.
  - `AlbumDeleteTrackTest` and a new `DeleteAfterResponseTest` cover S-112-03/04 and S-112-18 (`Bus::assertDispatchedAfterResponse` on `sync`, `Bus::assertNotDispatchedAfterResponse` on `database`).
  - Unit tests that call a delete action directly on `sync` see deferred jobs only through `Bus::fake` (no kernel terminate); existing ones assert neither files nor these jobs.
  - Suites that check `album_user_thumbs` after a delete (`SharingTest`, `PeopleControllerTest`, `FaceDismissTest`, `AlbumCategoryV3Test`, `UserGroupTest`) are re-run, since photo delete now runs thumb jobs.
- **Migration:** no test (owner rule). S-112-22 is checked on the dev DB: first `up()` twice on a fixture inside a rolled-back transaction, then the real `php artisan migrate`.
- Failing tests are written before each implementation step (see tasks).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-112-01 | `FileDeleterJob::batches(StorageDiskType $disk, array $paths): list<FileDeleterJob>`; `FileDeleterJob::BATCH_SIZE = 500`. | jobs |
| DO-112-02 | `App\Actions\Album\AlbumUserThumbTargets`: `resolve(array $photo_ids): list<array{0:string,1:string}>` (kind, album_id), `jobs(array $targets): list<RecomputeAlbumUserThumbsJob>`, `dispatch(array $targets): void`. | actions, listeners |
| DO-112-03 | `App\Actions\Shared\DispatchAfterDelete::dispatch(iterable<ShouldQueue> $jobs): void` (new namespace `App\Actions\Shared`): `afterResponse()` on the `sync` connection, plain `dispatch()` otherwise. Used by `Album\Delete`, `Photo\Delete`, `SizeVariant\Delete` for every post-commit job (FR-112-04) and by `WebhookListener` for delete webhooks (FR-112-12). | actions, listeners |
| DO-112-04 | `App\Constants\QueryLimits::ID_CHUNK_SIZE = 500`, shared by every chunked `whereIn` of FR-112-09..11 (replaces the local `CHUNK_SIZE` constants of the two delete DTOs, whose comments citing the 65 535 limit are updated). | constants, DTO, actions, listeners, policies |
| DO-112-05 | Migration `2026_11_15_000001_relink_detached_sold_size_variants.php` (FR-112-13). | migrations |

### API Routes / Services
No route or resource change. Entry points that reach the changed delete code: `DELETE /api/v2/Album`, `DELETE /api/v2/Photo`, `DELETE /api/v2/BulkAlbumEdit` (admin; `DeleteBulkAlbumRequest` checks the admin flag only, no id query), `POST /api/v2/Photo::rotate` (through `SizeVariants::deleteAll()`), `Photo::delete()`, the import pipes `DeleteMissingPhotos` / `DeleteMissingAlbums` (`lychee:sync` and server import), the NSFW block action (`forceDeletePhoto()`), `CleanupOrphanedPersonAlbumsJob`, and `SizeVariant::delete()` (Ghostbuster, placeholder encoder). Their behaviour changes in timing (FR-112-04, FR-112-12), side effects (FR-112-05..07), and how sold variants are kept (FR-112-10). Requests with more than 1000 ids no longer fail on MySQL (FR-112-09, FR-112-11). Rotating a photo with a sold variant now succeeds (FR-112-10).

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-112-01 | `jobs` log `Processed {n} paths on {disk}.` | count and disk name only, no paths. |

## Telemetry & Observability
Per-file `Log::debug` lines are removed (FR-112-01). Exceptions keep their current reporting.

## Documentation Deliverables
- Roadmap row 112.
- Knowledge map: `AlbumUserThumbTargets`, `DispatchAfterDelete` (post-delete jobs after the response on `sync`, best effort), `QueryLimits::ID_CHUNK_SIZE`, `PhotoWillBeDeleted` consumed by `WebhookListener` only (its jobs through `DispatchAfterDelete`), sold variants found through `NOT EXISTS` on `order_items` and detached in every delete path.
- Release note: the migration re-links sold variants detached from photos that still exist (FR-112-13); rotating photos with sales works again; on `sync`, delete webhooks and file cleanup run after the response on a best-effort basis (a worker killed mid-way drops the rest, and `lychee:ghostbuster` removes leftover files), so installs that need every delete webhook should use an async queue (Q-112-10 A).

## Fixtures & Sample Data
None.

## Spec DSL

```
domain_objects:
  - id: DO-112-01
    name: FileDeleterJob::batches
    constants: { BATCH_SIZE: 500 }
  - id: DO-112-02
    name: AlbumUserThumbTargets
  - id: DO-112-03
    name: DispatchAfterDelete
  - id: DO-112-04
    name: QueryLimits
    constants: { ID_CHUNK_SIZE: 500 }
  - id: DO-112-05
    name: relink_detached_sold_size_variants
    kind: migration
routes: []
telemetry_events:
  - id: TE-112-01
    event: jobs.file_deleter.summary
```
