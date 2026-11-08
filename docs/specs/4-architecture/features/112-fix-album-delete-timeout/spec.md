# Feature 112 – Fix Album Delete Timeout

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-11-08 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #112 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Deleting a large album (≈4000 photos) or a large photo selection times out. The DB work is fast (0.3 s of SQL for 4000 photos × 7 size variants). The time goes to two places:
- File deletion: one `FileDeleterJob` per (disk, kind) carries every path (28 000 for 4000 photos), writes one log line per file, and calls S3 twice per file. On the default `sync` queue it runs inside the HTTP request.
- Multi-photo delete with the webhook feature on: one `PhotoWillBeDeleted` per photo, each fanning out to 15 `RecomputeAlbumUserThumbsJob`s plus per-photo queries (504 s for 4000 photos on `sync`).

Affected layers: `App\Jobs\FileDeleterJob`, the three producers of file-deletion jobs (`Actions\Album\Delete`, `DTO\Delete\PhotosToBeDeletedDTO`, `Actions\SizeVariant\Delete`), `Actions\Photo\Delete`, `Listeners\RecomputeAlbumUserThumbsOnPhotoChange`, `Listeners\ManagedCacheAlbumListingInvalidator`, `EventServiceProvider`. No REST contract, schema or frontend change. Decisions: Q-112-01 A and Q-112-02 A (with the thumb refresh computed once per delete request), see [open-questions.md](open-questions.md).

## Goals
- File deletion costs O(files) with a small constant, in bounded jobs, and stays out of the HTTP response time on the `sync` queue.
- Deleting N photos does a constant number of thumb-refresh resolutions and dispatches each `RecomputeAlbumUserThumbsJob` at most once, whatever N is.
- `PhotoWillBeDeleted` is fired only when a `photo.delete` webhook can consume it.

## Non-Goals
- Making the album/photo DB deletion itself a queued job (Q-112-01 Option B).
- Sending `photo.delete` webhooks for photos removed together with their album (album delete never fired `PhotoWillBeDeleted`; unchanged).
- Changing the `photo.delete` webhook payload or the `RecomputeAlbumUserThumbsJob` semantics.
- Changing the other jobs returned by `PhotosToBeDeletedDTO::executeDelete()` (`RecomputePersonStatsJob`, `DeleteFaceEmbeddingsJob`).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-112-01 | `FileDeleterJob` logs one summary line per job instead of one line per file. | `jobs` channel gets one `debug` line naming the disk and the file count. | Empty list: no file I/O, one "No files to delete" line (as today). | Errors still collected; the first one is rethrown as `MediaFileOperationException` after the loop (as today). | `jobs` channel: `Deleted {n} files on {disk}.` | Q-112-01 A |
| FR-112-02 | On non-local disks (S3), `FileDeleterJob` deletes each file without an `exists()` pre-check. | One `delete()` call per file. A missing key is a no-op success. | — | `delete()` returning `false` records a `FileDeletionException` for that path; a thrown exception is recorded; the first is rethrown after the loop. | — | Q-112-01 A |
| FR-112-03 | File-deletion jobs carry at most 500 paths each. Empty lists produce no job. | A producer with P paths for one disk dispatches ⌈P/500⌉ `FileDeleterJob`s, built by `FileDeleterJob::batches(StorageDiskType, array): list<FileDeleterJob>`. | P = 0 → no job. | — | — | Q-112-01 A |
| FR-112-04 | When the queue connection is `sync`, file-deletion jobs run after the HTTP response is sent. Other connections queue them normally. | `sync`: each `FileDeleterJob` is dispatched with `afterResponse()`; the request returns once the DB transaction has committed. Async: `dispatch()` as today. | Applies to all three producers (album, photo, size-variant delete). | A failure after the response is logged by the job, not reported to the caller. | — | Q-112-01 A |
| FR-112-05 | `RecomputeAlbumUserThumbsOnPhotoChange` and `ManagedCacheAlbumListingInvalidator` no longer listen to `PhotoWillBeDeleted`; their `handlePhotoWillBeDeleted` methods are removed. | Only `WebhookListener` consumes `PhotoWillBeDeleted`. | — | — | — | Q-112-02 A |
| FR-112-06 | `Photo\Delete` fires `PhotoWillBeDeleted` only when the webhook feature is active and at least one enabled webhook subscribes to `photo.delete`. | One query for such webhooks; none → no event, no size-variant URL build. Some → one event per hard-deleted photo with today's payload. | — | — | — | Q-112-02 A |
| FR-112-07 | `Photo\Delete` refreshes cached album thumbs once per delete request: it resolves the affected thumb albums once over all hard-deleted photos before the DB delete and dispatches one `RecomputeAlbumUserThumbsJob` per affected album after the DB delete. Independent of the webhook feature. | Affected set = every `SmartAlbumType`, tag albums sharing a tag with the photos, person albums sharing a non-dismissed person, and tag/person albums currently caching one of the photos as thumb. Each (kind, album) dispatched once. Applies to `do()` and `forceDeletePhoto()`. | No hard-deleted photo → no resolution and no job. | — | — | Q-112-02 A (owner: thumb jobs computed once) |
| FR-112-08 | The affected-album resolution of FR-112-07 is the same code the listener uses for its other photo events. | `RecomputeAlbumUserThumbsOnPhotoChange` and `Photo\Delete` share one resolver (`App\Actions\Album\AlbumUserThumbTargets::resolve(array $photo_ids)` returning the (kind, album_id) list, and `dispatch(list)`). | — | — | — | Q-112-02 A |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-112-01 | Deleting 4000 selected photos with the webhook feature on and no `photo.delete` webhook does not grow `RecomputeAlbumUserThumbsJob` dispatches or listener queries with N. | 504 s measured on `sync`. | Test: deleting several photos dispatches each thumb job (kind, id) exactly once and no `PhotoWillBeDeleted`. | Bus/Event fakes. | Q-112-02 |
| NFR-112-02 | No `FileDeleterJob` payload exceeds 500 paths. | Worker `--timeout`, payload size. | Unit test on `FileDeleterJob::batches()`, feature test on album delete. | — | Q-112-01 |
| NFR-112-03 | DB records are still removed before any file. | Today's ordering; a failed file delete never leaves dangling DB rows. | Existing delete tests stay green. | — | Q-112-01 A |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-112-01 | `FileDeleterJob` on 3 local files: files gone, one summary log line. |
| S-112-02 | `FileDeleterJob::batches()` with 1201 paths → 3 jobs (500, 500, 201); with 0 paths → none. |
| S-112-03 | Album delete on `sync` queue: `FileDeleterJob`s are dispatched after response; DB rows already gone when the request returns. |
| S-112-04 | Album delete on an async queue: `FileDeleterJob`s queued normally, none after response. |
| S-112-05 | Multi-photo delete, webhook feature on, no `photo.delete` webhook: no `PhotoWillBeDeleted` fired. |
| S-112-06 | Multi-photo delete, webhook feature on, enabled `photo.delete` webhook: one `PhotoWillBeDeleted` per hard-deleted photo. |
| S-112-07 | Multi-photo delete (webhook feature on or off): each affected (kind, album) `RecomputeAlbumUserThumbsJob` dispatched exactly once, after the photos are gone. |
| S-112-08 | Delete where every photo stays in another album (soft delete only): no thumb resolution, no thumb job. |
| S-112-09 | `PhotoWillBeDeleted` reaches only `WebhookListener`. |

## Test Strategy
- **Unit:** `FileDeleterJobTest` gains summary-log and `batches()` cases (S-112-01/02). `ManagedCacheAlbumListingInvalidatorTest` loses its `PhotoWillBeDeleted` row. New `AlbumUserThumbTargetsTest` covers smart/tag/person/backward resolution and dedup. Event wiring test asserts S-112-09.
- **Feature_v2:** new `PhotoDeleteFanOutTest` (S-112-05..08) over `DELETE /api/v2/Photo` with `Bus::fake`/`Event::fake`. `AlbumDeleteTrackTest` and a new album-delete dispatch test cover S-112-03/04.
- Failing tests are written before each implementation step (see tasks).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-112-01 | `FileDeleterJob::batches(StorageDiskType $disk, array $paths): list<FileDeleterJob>`; `FileDeleterJob::BATCH_SIZE = 500`. | jobs |
| DO-112-02 | `App\Actions\Album\AlbumUserThumbTargets`: `resolve(array $photo_ids): list<array{0:string,1:string}>` (kind, album_id), `dispatch(array $targets): void`. | actions, listeners |
| DO-112-03 | `App\Actions\Shared\DispatchFileDeletion::dispatch(iterable $jobs): void`: `afterResponse()` on the `sync` connection, plain `dispatch()` otherwise. | actions, DTO |

### API Routes / Services
No route or resource change. Behaviour of `DELETE /api/v2/Album`, `DELETE /api/v2/Photo` changes only in timing (FR-112-04) and side effects (FR-112-05..07).

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-112-01 | `jobs` log `Deleted {n} files on {disk}.` | count and disk name only, no paths. |

## Telemetry & Observability
Per-file `Log::debug` lines are removed (FR-112-01). Exceptions keep their current reporting.

## Documentation Deliverables
- Roadmap row 112.
- Knowledge map: `AlbumUserThumbTargets`, file-deletion dispatch helper, `PhotoWillBeDeleted` consumed by `WebhookListener` only.

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
    name: DispatchFileDeletion
routes: []
telemetry_events:
  - id: TE-112-01
    event: jobs.file_deleter.summary
```
