# Feature 112 – Fix Album Delete Timeout

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-11-15 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #112 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Deleting a large album (≈4000 photos) or a large photo selection times out. The DB work is fast (0.3 s of SQL for 4000 photos × 7 size variants). The time goes to two places:
- File deletion: one `FileDeleterJob` per (disk, kind) carries every path (28 000 for 4000 photos), writes one log line per file, and calls S3 twice per file. On the default `sync` queue it runs inside the HTTP request.
- Multi-photo delete with the webhook feature on: one `PhotoWillBeDeleted` per photo, each fanning out to 15 `RecomputeAlbumUserThumbsJob`s plus per-photo queries (504 s for 4000 photos on `sync`).

The 2026-11-15 review found two existing defects on lines this feature rewrites, and one more place where a large request breaks the bind bound:
- `order_items.size_variant_id` is NULL for every item delivered by URL (print, pixel, FULL, admin `download_link`), for album items and for unfulfilled items. The exclusion list binds that NULL, and `NOT IN (…, NULL)` matches nothing. Photo delete then leaves files on disk, and size-variant delete removes files but keeps their rows. The "keep sold variants" update also sets `photo_id = NULL` on every sold variant in the instance, not only on those of the deleted photos.
- The delete permission checks (`PhotoPolicy`, `AlbumPolicy`) bind every requested id before the action runs.

Affected layers: `App\Jobs\FileDeleterJob`, the three producers of file-deletion jobs (`Actions\Album\Delete`, `DTO\Delete\PhotosToBeDeletedDTO`, `Actions\SizeVariant\Delete`), `DTO\Delete\AlbumsToBeDeletedDTO`, `Actions\Photo\Delete`, `Actions\Shop\PurchasableService`, `Policies\PhotoPolicy`, `Policies\AlbumPolicy`, `Listeners\RecomputeAlbumUserThumbsOnPhotoChange`, `Listeners\ManagedCacheAlbumListingInvalidator`, `EventServiceProvider`. No REST contract, schema or frontend change.

Decisions: Q-112-01 A, Q-112-02 A (with the thumb refresh computed once per delete request), Q-112-03 A (no query binds more than 1000 parameters), Q-112-04 A (fix both `order_items` defects here), Q-112-05 A (the delete permission checks are bounded too), Q-112-06 A (every job a delete action dispatches after its commit runs after the response on `sync`). See [open-questions.md](open-questions.md).

## Goals
- File deletion costs O(files) with a small constant, in bounded jobs.
- On the `sync` queue, no post-delete job (file deletion, person stats, face embeddings, thumb refresh) runs before the HTTP response is sent.
- Deleting N photos does a constant number of thumb-refresh resolutions and dispatches each `RecomputeAlbumUserThumbsJob` at most once, whatever N is.
- `PhotoWillBeDeleted` is fired only when a `photo.delete` webhook can consume it.
- No query of a delete request, permission checks included, binds more than 1000 parameters (MySQL fails above that), whatever the selection, subtree or shop size.
- Sold size variants are kept whatever else `order_items` holds, and a delete only detaches the sold variants of the photos it deletes.

## Non-Goals
- Making the album/photo DB deletion itself a queued job (Q-112-01 Option B).
- Sending `photo.delete` webhooks for photos removed together with their album (album delete never fired `PhotoWillBeDeleted`; unchanged).
- Changing the `photo.delete` webhook payload or the `RecomputeAlbumUserThumbsJob` semantics.
- Bounding `whereIn` lists outside the album/photo/size-variant delete flow, its permission checks (FR-112-11) and the two listeners named in FR-112-09. The move-by-id checks share the `AlbumPolicy` grant helpers, so they get the bound too, with unchanged results.
- Changing the payload or semantics of the other jobs returned by `PhotosToBeDeletedDTO::executeDelete()` (`RecomputePersonStatsJob`, `DeleteFaceEmbeddingsJob`). Only their dispatch moves after the response on `sync` (FR-112-04).
- Changing how event listeners in the delete flow dispatch their jobs (album stats, album size, caches).
- Protecting files referenced by a free-text `order_items.download_link` (for example a pasted Lychee size-variant URL). The DB does not track them, so deleting the photo breaks such a link, as today.
- Any other shop behaviour (fulfilment, how `size_variant_id` is assigned).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-112-01 | `FileDeleterJob` logs one summary line per job instead of one line per file. | `jobs` channel gets one `debug` line naming the disk and the number of paths processed (missing files and failures included). | Empty list: no file I/O, one "No files to delete" line (as today; only reachable by direct construction once FR-112-03 holds). | Errors still collected; the first one is rethrown as `MediaFileOperationException` after the loop (as today). | `jobs` channel: `Processed {n} paths on {disk}.` | Q-112-01 A |
| FR-112-02 | On non-local disks (S3), `FileDeleterJob` deletes each file without an `exists()` pre-check. | One `delete()` call per file. A missing key is a no-op success. | — | `delete()` returning `false` records a `FileDeletionException` for that path; a thrown exception is recorded; the first is rethrown after the loop. The S3 branch loses its `@codeCoverageIgnore` (covered by S-112-19). | — | Q-112-01 A |
| FR-112-03 | File-deletion jobs carry at most 500 paths each. Empty lists produce no job. | A producer with P paths for one disk dispatches ⌈P/500⌉ `FileDeleterJob`s, built by `FileDeleterJob::batches(StorageDiskType, array): list<FileDeleterJob>`. Track files of `Album\Delete` included. | P = 0 → no job. | — | — | Q-112-01 A |
| FR-112-04 | When the queue connection is `sync` (`config('queue.default') === 'sync'`, as elsewhere in the code base), every job a delete action dispatches after its DB commit runs after the HTTP response is sent: the `FileDeleterJob`s (track files included), the `RecomputePersonStatsJob` and `DeleteFaceEmbeddingsJob` returned by `executeDelete()`, and the `RecomputeAlbumUserThumbsJob`s of FR-112-07. Other connections queue them normally. All go through one helper (DO-112-03). | `sync`: each job is dispatched with `afterResponse()`; the request returns once the DB transaction has committed. Async: `dispatch()` as today. In console (`lychee:sync` import deleting missing photos/albums) the deferred jobs run when the command exits. | Applies to `Album\Delete::do()`, `Photo\Delete::do()` and `forceDeletePhoto()`, `SizeVariant\Delete::do()`. Jobs dispatched by event listeners are unchanged. On `sync`, person counts and thumbs are refreshed a moment after the response (accepted, Q-112-06 A). | A failure after the response is reported through the exception handler (Laravel runs each after-response job inside `rescue(report: true)`); the remaining jobs still run; the caller is not told. | — | Q-112-01 A, Q-112-06 A |
| FR-112-05 | `RecomputeAlbumUserThumbsOnPhotoChange` and `ManagedCacheAlbumListingInvalidator` no longer listen to `PhotoWillBeDeleted`; their `handlePhotoWillBeDeleted` methods are removed. | Only `WebhookListener` consumes `PhotoWillBeDeleted`. | — | — | — | Q-112-02 A |
| FR-112-06 | `Photo\Delete` fires `PhotoWillBeDeleted` only when the webhook feature is active and at least one enabled webhook subscribes to `photo.delete`. | One query for such webhooks (`Webhook::enabled()->forEvent(PhotoWebhookEvent::DELETE)`, the filter `WebhookListener` uses); none → no event, no size-variant URL build. Some → one event per hard-deleted photo with today's payload. | — | — | — | Q-112-02 A |
| FR-112-07 | `Photo\Delete` refreshes cached album thumbs once per delete request: it resolves the affected thumb albums once over all hard-deleted photos before the DB delete and dispatches one `RecomputeAlbumUserThumbsJob` per affected album after the DB delete. Independent of the webhook feature. On `sync` these jobs run after the response (FR-112-04). | Affected set = every `SmartAlbumType`, tag albums sharing a tag with the photos, person albums sharing a non-dismissed person, and tag/person albums currently caching one of the photos as thumb. Each (kind, album) dispatched once. Applies to `do()` and `forceDeletePhoto()`. Album delete does no thumb refresh (FK cascade on `album_user_thumbs.photo_id` plus lazy recompute on read, as today). | No hard-deleted photo → no resolution and no job. | — | — | Q-112-02 A (owner: thumb jobs computed once) |
| FR-112-08 | The affected-album resolution of FR-112-07 is the same code the listener uses for its other photo events. | `RecomputeAlbumUserThumbsOnPhotoChange` and `Photo\Delete` share one resolver (`App\Actions\Album\AlbumUserThumbTargets::resolve(array $photo_ids)` returning the (kind, album_id) list). The listener dispatches the targets normally (`dispatch(list)`); `Photo\Delete` turns them into jobs (`jobs(list)`) and hands them to the after-response helper. The resolved set is the same as the listener's today. | — | — | — | Q-112-02 A |
| FR-112-09 | No query in the delete flow binds more than 1000 parameters. Id lists are de-duplicated, then split into chunks of 500 (`App\Constants\QueryLimits::ID_CHUNK_SIZE`). A query that takes two lists binds at most 500 of each. | Sites: `Photo\Delete::do()` (3 queries) and `dispatchWillBeDeletedEvents()`, `PurchasableService::deleteMulitplePhotoPurchasables()`, `Album\Delete::do()` (person/tag album lookups), `findAllPhotosToDelete()`, `findAllAlbumsToDelete()`, `getGaps()`, `deleteTagAlbums()`, `deletePersonAlbums()`, `AlbumsToBeDeletedDTO` (its local 1000 chunk becomes `ID_CHUNK_SIZE`), `PhotosToBeDeletedDTO::forceDelete()` (album/tag-album re-reads), `AlbumUserThumbTargets::resolve()`, `ManagedCacheAlbumListingInvalidator::evictPersonTagsForPhotos()`, plus FR-112-10 and FR-112-11. Results are merged across chunks and de-duplicated, except in `findAllPhotosToDelete()`: there the per-photo count of links into the deleted albums is **summed** across album chunks (keyed by photo id, no sorted merge) and compared with the photo's total link count. `AlbumUserThumbTargets::resolve()` binds only photo-id chunks: tag and person albums are reached through subqueries (`photos_tags` → `tag_albums_tags`, `faces` → `person_album_persons`), and the backward lookup classifies the cached albums with one query per chunk instead of one `find()` per album. | Empty list → no query. Album ids are de-duplicated before chunking, so a photo link is never counted twice. | — | — | Q-112-03 A (owner: >1000 parameters fails on MySQL); review 2026-11-15 |
| FR-112-10 | Sold size variants are excluded with NULL-safe SQL, and a delete only detaches the sold variants of the photos it deletes. | `PhotosToBeDeletedDTO` (path collection) and `SizeVariant\Delete::do()` (path collection and row delete) skip a size variant when `EXISTS (SELECT 1 FROM order_items oi WHERE oi.size_variant_id = sv.id)`. No `order_items` id list is loaded or bound. In `forceDelete()`, for each force-deleted chunk and before that chunk's `size_variants` delete, the variants with `photo_id IN (chunk)` that match the same `EXISTS` get `photo_id = NULL`. Sold variants of other photos are untouched. A sold variant keeps its row and its file. | `order_items` rows with NULL `size_variant_id` (print, pixel, FULL, `download_link`, album or unfulfilled items) protect nothing and do not change the result. An item with both a `size_variant_id` and a `download_link` still protects its variant. | — | — | Q-112-04 A |
| FR-112-11 | The delete permission checks bind at most 1000 parameters and return the same result as today for any input. | `PhotoPolicy::canDeleteById()`: the owner count is summed across photo-id chunks and compared with the de-duplicated id count; the "photo without album" check stops at the first chunk that finds one; parent album ids are merged and de-duplicated, then passed to `AlbumPolicy::canDeleteContentById()`. `AlbumPolicy::hasGrantOnParentsById()`, `hasGrantOnAllById()` and `countGrantedById()` chunk their album ids, sum counts across chunks and compare with the de-duplicated id count. `countGrantedById()` also binds the user's group ids (bounded by group membership, not by the request). | Owner of every id → allowed. One foreign id without grant, in any chunk → denied. Empty list → as today (`may_upload`). | Denied → 403, as today. | — | Q-112-05 A |

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
| S-112-12 | Delete more than 1000 album ids (regular and tag albums) in one request over HTTP: succeeds, no query binds more than 1000 parameters. |
| S-112-13 | Photo delete with an `order_items` row delivered by URL present (NULL `size_variant_id`, `download_link` set): the deleted photo's size-variant rows are gone and its files are queued for deletion. |
| S-112-14 | Photo delete while a photo that is not deleted has a sold size variant: that variant keeps its `photo_id`. The deleted photo's sold variant keeps its row (with `photo_id` NULL) and its file is not queued. |
| S-112-15 | `SizeVariant\Delete` with an `order_items` row delivered by URL present: unsold variants lose rows and files; a sold variant keeps both. |
| S-112-16 | Delete permission checks with 1200 ids: owner of all → allowed; one foreign id without grant in the last chunk → denied; non-owner with the delete grant on every parent album → allowed. |
| S-112-17 | `AlbumUserThumbTargets::resolve()` and `evictPersonTagsForPhotos()` with 1200 photo ids: same targets and evicted persons as unchunked, no query binds more than 1000 parameters. |
| S-112-18 | Photo delete on `sync` (photos with faces, smart thumbs cached): file, person-stats, face-embedding and thumb jobs are all dispatched after response, none inline. On an async queue all are queued normally. |
| S-112-19 | `FileDeleterJob` on the S3 disk (mocked, non-local adapter): one `delete()` per path and no `exists()` call; a `delete()` returning `false` → every path is still tried, then `MediaFileOperationException`. |

## Test Strategy
- **Unit:**
  - `FileDeleterJobTest` gains summary-log, `batches()` and mocked-S3 cases (S-112-01/02/19).
  - `ManagedCacheAlbumListingInvalidatorTest` loses its `PhotoWillBeDeleted` row and gains a 1200-id `PhotoSaved` case (S-112-17).
  - New `AlbumUserThumbTargetsTest` covers smart/tag/person/backward resolution, dedup, empty input and a 1200-id case (S-112-17).
  - `tests/Unit/Actions/Photo/DeleteTest.php` gains S-112-13/14; `tests/Unit/Actions/SizeVariant/DeleteTest.php` gains S-112-15.
- **Feature_v2:**
  - New `DeleteBindLimitTest` asserts via `DB::listen` that no query binds more than 1000 parameters (S-112-10..12).
  - New `DeletePolicyBindLimitTest` (S-112-16), over `DELETE /api/v2/Photo` and `DELETE /api/v2/Album`.
  - New `PhotoDeleteFanOutTest` (S-112-05..09) over `DELETE /api/v2/Photo` with `Bus::fake`/`Event::fake`. S-112-09 asserts the listeners registered for `PhotoWillBeDeleted`. S-112-07's "after the photos are gone" is checked without the Bus fake: a `Queue::before` hook records whether the photos still exist when each `RecomputeAlbumUserThumbsJob` starts. The HTTP test helper calls the kernel's `terminate()`, so the after-response jobs run inside the request call.
  - `AlbumDeleteTrackTest` and a new `DeleteAfterResponseTest` cover S-112-03/04 and S-112-18 (`Bus::assertDispatchedAfterResponse` on `sync`, `Bus::assertNotDispatchedAfterResponse` on `database`).
  - Unit tests that call a delete action directly on `sync` see deferred jobs only through `Bus::fake` (no kernel terminate); existing ones assert neither files nor these jobs.
- Failing tests are written before each implementation step (see tasks).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-112-01 | `FileDeleterJob::batches(StorageDiskType $disk, array $paths): list<FileDeleterJob>`; `FileDeleterJob::BATCH_SIZE = 500`. | jobs |
| DO-112-02 | `App\Actions\Album\AlbumUserThumbTargets`: `resolve(array $photo_ids): list<array{0:string,1:string}>` (kind, album_id), `jobs(array $targets): list<RecomputeAlbumUserThumbsJob>`, `dispatch(array $targets): void`. | actions, listeners |
| DO-112-03 | `App\Actions\Shared\DispatchAfterDelete::dispatch(iterable<ShouldQueue> $jobs): void`: `afterResponse()` on the `sync` connection, plain `dispatch()` otherwise. Used by `Album\Delete`, `Photo\Delete`, `SizeVariant\Delete` for every post-commit job (FR-112-04). | actions |
| DO-112-04 | `App\Constants\QueryLimits::ID_CHUNK_SIZE = 500`, shared by every chunked `whereIn` of FR-112-09..11 (replaces the local `CHUNK_SIZE` constants of the two delete DTOs, whose comments citing the 65 535 limit are updated). | constants, DTO, actions, listeners, policies |

### API Routes / Services
No route or resource change. Behaviour of `DELETE /api/v2/Album` and `DELETE /api/v2/Photo` changes in timing (FR-112-04), side effects (FR-112-05..07), and which size-variant rows and files are removed when `order_items` holds NULL ids or sold variants of other photos (FR-112-10). Requests with more than 1000 ids no longer fail on MySQL (FR-112-09, FR-112-11).

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-112-01 | `jobs` log `Processed {n} paths on {disk}.` | count and disk name only, no paths. |

## Telemetry & Observability
Per-file `Log::debug` lines are removed (FR-112-01). Exceptions keep their current reporting.

## Documentation Deliverables
- Roadmap row 112.
- Knowledge map: `AlbumUserThumbTargets`, `DispatchAfterDelete` (post-delete jobs after the response on `sync`), `QueryLimits::ID_CHUNK_SIZE`, `PhotoWillBeDeleted` consumed by `WebhookListener` only, sold-variant exclusion through `NOT EXISTS` on `order_items`.

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
routes: []
telemetry_events:
  - id: TE-112-01
    event: jobs.file_deleter.summary
```
