# Feature 119 – Background Photo Delete

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-27 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #119 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
On a slow disk, deleting a large album still exceeds the request time limit after Feature 112. On the 2026-11-27 production server (MariaDB on an HDD, 128 MB buffer pool, Octane's 30 s `LYCHEE_MAX_EXECUTION_TIME`), the cost is in `photos` and `size_variants` (about 394 MB on the dev copy): every deleted photo is read from the disk and changes about 17 index entries in `photos` and 4 per size variant. FR-112-14 reads each chunk once, but the request still grows with the number of photos (Feature 112, Q-112-13).

This feature takes the photo rows out of the request (Q-112-13 Option A, owner's proposal; [ADR-119-01](../../../6-decisions/ADR-119-01-photo-trash-and-background-purge.md)). A delete removes the album links it targets, puts the photos left without any link into a trash table, and deletes the albums at once. A purge then deletes the trashed photos in small batches in the background, with `PhotosToBeDeletedDTO`'s per-chunk structure (FR-112-14). Trashed photos are hidden from everyone, their owner and admins included, from the response on. On `sync` installs, where nothing runs in the background, an admin Maintenance task (v8 and v7) and an artisan command finish what the after-response purge could not.

Affected layers: a migration (`trashed_photos`), the `Photo` model (global scope), `App\Actions\Album\Delete`, `App\Actions\Photo\Delete`, a new trash action, a new purge service, job and artisan command, `App\DTO\Delete\PhotosToBeDeletedDTO`, the raw `photos` reads listed in FR-119-03, an admin Maintenance controller and two routes, the v8 and v7 Maintenance pages and the `maintenance` translations. REST contracts of the delete endpoints are unchanged (no content).

Decisions: Q-119-01 A (trash table), Q-119-02 A (every album and photo delete path), Q-119-03 A (purge job after each delete, plus a Maintenance task in v8 and v7 and an artisan command), Q-119-04 A (no retention), Q-119-05 A (visible side effects when trashed, row-dependent ones when purged). Owner rule (2026-11-27): `photo_album` is many-to-many, so a photo is trashed only once its last link is removed. Q-119-06 C (owner, 2026-11-28): deletes from smart albums other than Unsorted, tag albums and person albums are refused and hidden; deleting from Unsorted keeps working. Q-119-07 A (owner, 2026-11-28): every selected photo must be in `from_id` (or, for Unsorted, in no album), and delete is hidden in Timeline, the Tag page, the Person page and Search. See [open-questions.md](open-questions.md).

## Goals
- The delete request's cost no longer depends on the `photos` and `size_variants` rows of the photos it deletes: it reads and writes `photo_album`, `trashed_photos` and the rows of the deleted albums only.
- A photo enters the trash only when its last album link is removed. The purge deletes only trashed photos that still have no link.
- Deleted albums are gone when the request returns.
- Trashed photos are hidden from every photo listing and from direct access when the request returns, for their owner and admins too.
- The purge deletes trashed photos in batches, each in its own transaction. A stopped purge loses nothing: the next run continues from the trash.
- On `sync` without a worker, an admin can see and finish pending deletions (Maintenance, v8 and v7) or run `php artisan lychee:purge-trash`.

## Non-Goals
- Restoring photos from the trash, and any retention period (Q-119-04 A).
- A scheduler in the Docker image.
- Changing what a purge removes: files, sold size variants (FR-112-10), faces, person stats and the bind limits of Feature 112 behave as today, only later.
- Changing tag, person or smart album deletion (no photo rows involved).
- Blocking direct file URLs of trashed photos (public storage served by the web server): they stay reachable until the purge deletes the files, as they do today until the file jobs run.
- Person stats and face counts (People page) are recomputed by the purge (FR-119-06), so they count the faces of trashed photos until then.
- Files shared by same-checksum duplicates (pre-existing, Feature 112 Non-Goals): deleting one photo of such a pair still deletes the shared files.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-119-01 | Album delete removes the links of the photos to the deleted albums, trashes those of these photos that have no link left, and deletes the albums in the request. | In one transaction: collect the photo ids linked to the deleted albums (`photo_album` by album chunk), delete those links, then insert into `trashed_photos` the collected photos for which `NOT EXISTS` a `photo_album` row (photo chunks, ignoring photos already trashed). A photo also in a surviving album keeps that link and is not trashed. A photo in several deleted albums is trashed once. The album rows are deleted as today (`AlbumsToBeDeletedDTO`). This replaces the link counting of `findAllPhotosToDelete()`. | Permission checks and bind limits as today (FR-112-09/11). | A failure rolls back the links, the trash entries and the album rows. | — | Q-112-13 A, Q-119-01 A, owner rule |
| FR-119-02 | Every photo delete path goes through the trash, with the same rule. | `Photo\Delete::do($photo_ids, $from_id)` (`DELETE /api/v2/Photo`, import `DeleteMissingPhotos`): delete the photos' links to `$from_id`, then trash the selected photos that have no link left (photos without any album included). The request has already checked that every selected photo is in `$from_id`, or, for `unsorted`, in no album (FR-119-09), so each selected photo either loses a link or is trashed: no photo is silently kept. `Photo\Delete::forceDeletePhoto()` (NSFW block action) and the Moderation delete (FR-119-10): delete every link of the photo, then trash it. Album paths (`DELETE /api/v2/Album`, admin `DELETE /api/v2/BulkAlbumEdit`, import `DeleteMissingAlbums`) use FR-119-01. `Photo::delete()` follows the action it calls. | As today. | As FR-119-01. | — | Q-119-02 A, owner rule |
| FR-119-03 | Trashed photos are hidden from everyone, their owner and admins included. | A global scope on `Photo` (`NotTrashedScope`: `NOT EXISTS` a `trashed_photos` row for `photos.id`) applies to every Eloquent photo query: albums, smart albums (Unsorted included), tag and person albums, search, timeline, frame, RSS, photo routes by id and `/api/v3/Asset/{album_id}/{photo_id}/…` (a trashed photo answers 404, like a missing one). Raw `photos` reads that list or serve photos apply the same condition: `Map\QueryMapBuckets`, `Insights\ComputeInsights`, `Insights\InsightsRevision`, `Insights\PeriodEdges`, `Photo\DuplicateFinder`, `Statistics\Counts`, `Statistics\Spaces`, `Diagnostics\…\StatisticsIntegrityCheck`, `Face\GetFacePhotoAssetRequest`. The trash action and the purge read without the scope. Consequences: the space statistics stop counting a photo when it is trashed. The upload quota (`Photo\Create::checkQuota()` through `Statistics\Spaces::getFullSpacePerUser()`) reads album sizes only, so it frees the space when the album sizes are recomputed after the delete (`PhotoDeleted` / `AlbumDeleted`). `FindDuplicate` (Eloquent) does not see trashed photos, so a new upload of the same file is stored as a new photo. Relations that load the photo of a size variant, a face or an order item return `null` for a trashed photo, and their request-path callers answer as for a missing photo. | — | — | — | Q-119-01 A, Q-112-13 A |
| FR-119-04 | A purge deletes trashed photos in batches of `PurgeTrash::BATCH_SIZE` (100), oldest `trashed_at` first, each batch in its own transaction. | Per batch: a trashed photo that has a `photo_album` row again (a link added after it was trashed) leaves the trash and is not deleted. The others go through `PhotosToBeDeletedDTO::executeDelete()` (FR-112-14: paths, faces, sold-variant detach, row deletes). Their `trashed_photos` rows go with the photos (foreign key `ON DELETE CASCADE`). After the batch commits, its file, person-stats and face-embedding jobs are dispatched with `dispatch()`: inline on `sync`, inside the purge's time budget, queued otherwise. | Empty trash → no query beyond the batch read. | A failing batch rolls back that batch only. Its photos stay in the trash for the next run, the error is reported, and the purge stops. | One `jobs` log line per run (TE-119-01). | Q-119-03 A |
| FR-119-05 | The purge runs after every delete that trashed photos, and can be resumed from the Maintenance page and the command line. | Every delete that trashed at least one photo dispatches `PurgeTrashJob` through `DispatchAfterDelete`: queued on async connections, after the response on `sync`. On `sync` in an HTTP request it starts no new batch after `PurgeTrash::SYNC_TIME_BUDGET` (10 s), so it ends within Octane's limit. In console on `sync` (import deletes, run when the command ends) it purges until the trash is empty. On async it purges until the trash is empty, but starts no new batch after `PurgeTrash::ASYNC_TIME_BUDGET` (30 minutes, under the worker's `--timeout=3600`) and then dispatches itself again if photos are left. A small delete is thus purged right after the response or by the worker, as files are today. Whatever is left is purged by the next delete's job, by the Maintenance task `GET`/`POST /api/v2/Maintenance::purgeTrash` (count of trashed photos; purges until the trash is empty, `maintenance_processing_limit` photos are done, or 10 s have passed), or by `php artisan lychee:purge-trash` (until the trash is empty, no time budget). | The Maintenance routes need the admin right (`MaintenanceRequest`). | Two purges at the same time are harmless: a photo deleted by one is gone for the other, and file deletion ignores missing files. `PurgeTrashJob` is `ShouldBeUniqueUntilProcessing`: a delete while a purge waits in the queue adds no second job, and a delete while a purge runs queues one, which picks up what the running one missed. Best effort. | TE-119-01 | Q-119-03 A |
| FR-119-06 | Side effects that users see run when the photos are trashed. Those that need the rows gone run when they are purged. | In the request: purchasables of the photos deleted (`PurchasableService`), as today. The trash step (FR-119-01/02) returns the trashed ids. The photo rows, tags and faces stay until the purge, so `PhotoWillBeDeleted` for the trashed photos (only with an enabled `photo.delete` webhook, FR-112-06/12, payload as today) and the thumb targets (FR-112-07) are computed from these ids after the trash step, not from a classification made before. In the trash transaction: covers and headers that point to trashed photos reset, with `AlbumSaved` / `TagAlbumSaved` (moved from the purge). After it commits: `AlbumListingCacheFlushRequested`; `PhotoDeleted` / `AlbumDeleted` as today; the thumb jobs through `DispatchAfterDelete` (their photo queries skip trashed photos through the scope). In the purge: file deletion, `RecomputePersonStatsJob`, `DeleteFaceEmbeddingsJob`. | — | A delete webhook is sent although the rows and files exist until the purge (accepted, Q-119-05 A). | — | Q-119-05 A |
| FR-119-07 | Trashed photos are purged as soon as possible. Nothing is restored. | No retention setting, no restore route. | — | — | — | Q-119-04 A |
| FR-119-08 | The admin Maintenance pages (v8 and v7) show pending deletions while the trash is not empty. | A card "Pending deletions" with the count and a "Purge" button, shown only when the count is above 0 (as the other Maintenance cards). The button calls `POST Maintenance::purgeTrash`, then reloads the count. Each version has its own `components/maintenance/MaintenancePurgeTrash.vue` (v8 on `MaintenanceRow`, v7 on a PrimeVue `Card` as `MaintenanceMissingPalettes.vue`), on the same service calls and `maintenance.purge-trash` keys. | — | Error → error toast with the server message (as the other cards). | — | Q-119-03 A |
| FR-119-09 | A photo delete names an album that holds every selected photo, or Unsorted for photos without an album. Other deletes are refused, and the UIs do not offer delete where no such album exists. | `DeletePhotosRequest` checks, in photo chunks, that every selected photo has a `photo_album` row for `from_id`, or, when `from_id` is `unsorted`, has no `photo_album` row at all. Unsorted keeps working (owner, 2026-11-28). Smart albums other than Unsorted, tag albums and person albums have no linked photo, so a delete from them is refused (Q-119-06 C). UIs, v8 and v7: photo delete stays in the album view of a regular album and of Unsorted. It is hidden (context menu, photo panel, `delete` / `backspace` shortcuts) in the album view of another smart album, a tag album or a person album, and in `Timeline.vue`, `Tag.vue`, `Search.vue` and `face-recog/PersonDetail.vue`. Both `admin/Moderation.vue` pages use the Moderation delete of FR-119-10 instead. | A selected photo not in `from_id` (or, for Unsorted, in an album) → 422 with a validation message on `photo_ids` naming those photos (at most 10, then a count). Nothing is trashed. | — | — | Q-119-06 C, Q-119-07 A |
| FR-119-10 | Moderation deletes photos without naming an album. | `DELETE /api/v2/Moderation` with `photo_ids` (1–500, as `Moderation::approve`): in one transaction per chunk, every `photo_album` row of the photos is deleted and the photos are trashed (`Trash::everywhere()`), with the side effects of FR-119-06 (as `forceDeletePhoto()`), then `PurgeTrashJob` through `DispatchAfterDelete`. v8 and v7 `admin/Moderation.vue` call it for "delete" and "delete selected" (new `ModerationService.delete(photo_ids)`). | Admin only (`may_administrate`), else 403. Empty or more than 500 ids → 422. | As FR-119-01. | — | Owner 2026-11-28 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-119-01 | A delete request issues no statement on `size_variants`, and no write on `photos`, for the photos it trashes. | Server log 2026-11-27: the cost is in these tables. | Feature test with a query log over an album delete of more than one chunk, with the bus faked (S-119-01). | `trashed_photos` | Q-112-13 |
| NFR-119-02 | No query of a delete request, of the purge, of the Maintenance task or of the command binds more than 1000 parameters. | Feature 112, Q-112-03 A. | `DB::listen` in the tests (`CreatesBulkFixtures`). | `QueryLimits` | Q-112-03 |
| NFR-119-03 | On `sync`, a purge started after the response or from Maintenance starts no batch after 10 s. | Octane's 30 s limit covers after-response work (FR-112-04). | Unit test with a time budget of 0 s: one batch, then stop (S-119-09). | `PurgeTrash::SYNC_TIME_BUDGET` | Q-119-03 A |

## UI / Interaction Mock-ups
v8 admin Maintenance page, new card (same layout as the other Maintenance cards), shown only while photos are waiting:

```
┌ Maintenance ─────────────────────────────────────────────────────┐
│ ┌──────────────────────────────┐ ┌──────────────────────────────┐ │
│ │ Pending deletions            │ │ Missing Palettes             │ │
│ │ 1234 photos are hidden and   │ │ Found 12 missing palettes.   │ │
│ │ waiting to be deleted.       │ │                              │ │
│ │                    [ Purge ] │ │           [ Create missing ] │ │
│ └──────────────────────────────┘ └──────────────────────────────┘ │
└───────────────────────────────────────────────────────────────────┘

While purging:                 After the click:
┌──────────────────────────────┐   count reloaded; card hidden at 0,
│ Pending deletions            │   success toast. Error: error toast
│   ◌ (loading icon)           │   with the server message.
└──────────────────────────────┘
```

v7 admin Maintenance page, same card in the v7 grid (PrimeVue `Card`, centred title, full-width button, as `MaintenanceMissingPalettes.vue`), shown only while photos are waiting:

```
┌──────────────────────────┐ ┌──────────────────────────┐
│    Pending deletions     │ │     Missing Palettes     │
│                          │ │                          │
│  1234 photos are hidden  │ │  Found 12 missing        │
│  and waiting to be       │ │  palettes.               │
│  deleted.                │ │                          │
│ ┌──────────────────────┐ │ │ ┌──────────────────────┐ │
│ │        Purge         │ │ │ │    Create missing    │ │
│ └──────────────────────┘ │ │ └──────────────────────┘ │
└──────────────────────────┘ └──────────────────────────┘
While purging: a spinner replaces the description and the button.
After the click: as in v8 (toast, count reloaded, card hidden at 0).
```

Photo context menu in a smart album other than Unsorted, a tag album, a person album, and in Timeline, Tag, Search and Person detail (FR-119-09), v8 and v7:

```
Before                          After
┌────────────────────┐          ┌────────────────────┐
│ ★ Highlight        │          │ ★ Highlight        │
│ ✎ Rename           │          │ ✎ Rename           │
│ 📁 Move            │          │ 📁 Move            │
│ 🗑 Delete          │   ──▶    │ ⇩ Download         │
│ ⇩ Download         │          └────────────────────┘
└────────────────────┘          (Delete shown in Unsorted
                                 and in regular albums)
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-119-01 | Album delete of a subtree holding 502 photos (two chunks), webhook feature off, bus faked: the albums and their `photo_album` rows are gone, the 502 photos are in `trashed_photos`, no statement touches `size_variants` and none writes `photos`, no query binds more than 1000 parameters. `PurgeTrashJob` is dispatched after the response on `sync` and queued on `database`. |
| S-119-02 | Album delete where a photo is also in a surviving album: its link to the surviving album stays, it is not trashed and is still listed there. |
| S-119-03 | Album delete where a photo is in two deleted sub-albums that fall in different album chunks: it is trashed once, then purged. |
| S-119-04 | Photo delete from album X: a photo also in Y loses its link to X only and is not trashed; a photo only in X is trashed; a photo without any album (deleted from Unsorted) is trashed. |
| S-119-05 | `forceDeletePhoto()` of a photo in two albums: both links removed, photo trashed. |
| S-119-06 | A trashed photo is hidden from its owner and from an admin: Unsorted, Recent, search, timeline, map, a tag album with its tag, a person album with its person, `GET` of the photo by id and `/api/v3/Asset/…` (404), the face photo asset (404), statistics counts, the duplicate finder. |
| S-119-07 | Purge of a trashed photo that has a `photo_album` row again: it leaves the trash and its rows stay. |
| S-119-08 | Purge of two batches whose second batch throws: the first batch is purged, the second batch's photos are still in the trash, the error is reported, the next run purges them. |
| S-119-09 | Purge with a time budget of 0 s: one batch purged, the rest left; without a budget the trash is emptied. `PurgeTrashJob` on async whose budget runs out with photos left dispatches itself again; in console on `sync` it empties the trash. |
| S-119-10 | `GET Maintenance::purgeTrash` returns the number of trashed photos; `POST` purges them and the count drops; a non-admin gets 403. |
| S-119-11 | `php artisan lychee:purge-trash` empties the trash and prints the purged and left counts. |
| S-119-12 | Side-effect timing on `sync`, bus faked, then the purge run by hand: `PhotoWillBeDeleted` (enabled `photo.delete` webhook), cover reset with `AlbumSaved`, thumb jobs and the cache flush happen with the delete; no `FileDeleterJob`, `RecomputePersonStatsJob` or `DeleteFaceEmbeddingsJob` is dispatched until the purge runs, which dispatches them. |
| S-119-13 | Purge of a photo with a sold size variant: the variant keeps its row and file with `photo_id` NULL (FR-112-10). |
| S-119-14 | HTTP delete on `sync` without faking the bus: the request's terminate runs the purge, so the photos' rows are gone after the call (existing delete tests keep passing). |
| S-119-15 | A trashed photo no longer counts in its owner's space statistics (`getSpacePerSizeVariantTypePerUser()`, which adds the photos without an album). The upload quota (`getFullSpacePerUser()`) reads album sizes only, so it drops when the album's size is recomputed after the delete. |
| S-119-16 | Upload of a file identical to a trashed photo: a new photo is created (the trashed one is not found as a duplicate). |
| S-119-17 | `DELETE /api/v2/Photo` with `from_id` `recent`, a tag album id or a person album id → 422, nothing trashed; with `unsorted` and a photo without album → trashed. |
| S-119-18 | `DELETE /api/v2/Photo` with `from_id` album X and one selected photo that is not in X (for example found in a sub-album by a search inside X), or with `unsorted` and one selected photo that is in an album → 422 naming that photo, nothing trashed, no link removed. |
| S-119-19 | v8: photo delete hidden in the album view of a smart album other than Unsorted, a tag album and a person album, and in Timeline, Tag, Search and Person detail; `delete` / `backspace` do nothing there; Unsorted and regular albums keep it (`contextMenu.test.ts` for the album-view rule). |
| S-119-20 | `DELETE /api/v2/Moderation` as admin with a photo in two albums, a photo in one album and a photo without album: all three lose every link and are trashed, `PurgeTrashJob` dispatched; as a non-admin → 403, nothing trashed; more than 500 ids → 422. |

## Test Strategy
- **No new Feature_v2 class (owner, 2026-11-27).** S-119-14 is covered by the existing Feature_v2 delete suites, which keep their HTTP assertions because on `sync` the purge runs in the request's terminate. Existing suites that call a delete action directly or fake the bus and then assert missing rows are adapted to run the purge explicitly (list in the plan).
- **Feature_v3** (`Tests\Feature_v3\Base\BaseApiWithDataTest`; v2 routes through the inherited helpers, as `Feature_v3/Maintenance/RecomputeBucketsTest` does): `Feature_v3/Photo/TrashDeleteTest` (S-119-01, S-119-12, S-119-17, S-119-18, through `DELETE /api/v2/Album` and `DELETE /api/v2/Photo`), `Feature_v3/Photo/TrashVisibilityTest` (S-119-06, S-119-15, S-119-16: v3 `Albums/{album_id}/Photos` for smart, tag and person albums, `Search/Photos`, `Map/Photos`, `Asset`, `Faces/{face_id}/photo`, `Insights`; the v2 helpers for photo by id, statistics, duplicate finder and upload), `Feature_v3/Maintenance/PurgeTrashMaintenanceTest` (S-119-10), `Feature_v3/Moderation/ModerationDeleteTest` (S-119-20).
- **Unit:** `tests/Unit/Actions/Photo/TrashActionTest.php` (S-119-02..05), `tests/Unit/Actions/Photo/PurgeTrashServiceTest.php` (S-119-07..09, S-119-13); `PhotosToBeDeletedDTOTest` unchanged.
- **Console:** `tests/Unit/Console/Commands/PurgeTrashCommandTest.php` (S-119-11).
- **Migration:** no test (owner rule). It is run on the dev DB.
- **Frontend (v8, v7):** `contextMenu.test.ts` (vitest) gains S-119-19 for the album-view rule; the other views and v7 are checked by `vp run typecheck` and by hand. `vp check --fix` on the changed files only (memory: a repo-wide autofix breaks `EmbedCodeDialog.vue`), `vp run typecheck`.
- Failing tests are written before each implementation step (see tasks).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-119-01 | Table `trashed_photos`: `photo_id` `char(24)` primary key, foreign key to `photos.id` `ON DELETE CASCADE`; `trashed_at` datetime, indexed. Migration only, no model. `down()` drops the table: photos still in the trash then show again to their owner as photos without an album. | migrations |
| DO-119-02 | `App\Models\Scopes\NotTrashedScope`, registered in `Photo::booted()`. Removed with `Photo::withoutGlobalScope(NotTrashedScope::class)` by the trash action and the purge. | models |
| DO-119-03 | `App\Actions\Photo\Trash`: `fromAlbums(string[] $album_ids): string[]`, `fromAlbum(string[] $photo_ids, string $from_id): string[]`, `everywhere(string[] $photo_ids): string[]` (used by `forceDeletePhoto()` and the Moderation delete). Each removes the links, trashes the photos left without a link, and returns the ids it trashed. Covers and headers are reset by `resetCoversAndHeaders(string[] $photo_ids)`, moved here from `PhotosToBeDeletedDTO`. | actions |
| DO-119-04 | `App\Actions\Photo\PurgeTrash::run(?int $limit, ?float $time_budget): PurgeTrashResult` (`purged`, `restored`, `left`); constants `BATCH_SIZE = 100`, `SYNC_TIME_BUDGET = 10`, `ASYNC_TIME_BUDGET = 1800` (seconds). | actions |
| DO-119-05 | `App\Jobs\PurgeTrashJob` (`ShouldQueue`, `ShouldBeUniqueUntilProcessing`): budget `SYNC_TIME_BUDGET` on `sync` in an HTTP request, none in console on `sync`, `ASYNC_TIME_BUDGET` on async (then dispatches itself again if photos are left). | jobs |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-119-01 | REST GET /api/v2/Maintenance::purgeTrash | Number of trashed photos. | `Admin\Maintenance\PurgeTrash::check`, `MaintenanceRequest` |
| API-119-02 | REST POST /api/v2/Maintenance::purgeTrash | Purges until empty, `maintenance_processing_limit` photos, or 10 s. | `Admin\Maintenance\PurgeTrash::do` |
| API-119-03 | REST DELETE /api/v2/Moderation | Body `photo_ids` (1–500). Removes every album link of each photo and trashes it. No content. | `Admin\ModerationController::delete`, `DeleteModerationRequest` (admin only, as `ApproveModerationRequest`) |

The delete endpoints (`DELETE /api/v2/Album`, `DELETE /api/v2/Photo`, `DELETE /api/v2/BulkAlbumEdit`) keep their request and response.

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-119-01 | `php artisan lychee:purge-trash` | Purges the trash until it is empty and prints the purged, restored and left counts. |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-119-01 | `jobs` log `Purged {n} photos from the trash, {r} restored, {m} left.` | counts only, no ids. |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-119-01 | Card hidden | Count 0 or not loaded. |
| UI-119-02 | Card shown | Count above 0: description with the count, "Purge" button. |
| UI-119-03 | Purging | Button pressed: loading icon, then success or error toast and the count reloaded. |

## Telemetry & Observability
TE-119-01 per purge run (job, Maintenance, command). Failures are reported through the exception handler with the batch's error.

## Documentation Deliverables
- Roadmap row 119.
- ADR-119-01 and its index row.
- Knowledge map: the trash (`trashed_photos`, `NotTrashedScope`, `Trash`), the purge (`PurgeTrash`, `PurgeTrashJob`, Maintenance, command), the delete flow now ending at the trash.
- Release note: deleted photos disappear at once and their files and rows are removed in the background. On installs without a queue worker, Maintenance shows "Pending deletions" when a purge could not finish, and `php artisan lychee:purge-trash` empties the trash.

## Fixtures & Sample Data
`tests/Traits/CreatesBulkFixtures.php` (Feature 112). No new sample file.

## Spec DSL

```
domain_objects:
  - id: DO-119-01
    name: trashed_photos
    kind: table
    fields:
      - { name: photo_id, type: char(24), constraints: "primary, fk photos.id on delete cascade" }
      - { name: trashed_at, type: datetime, constraints: "indexed" }
  - id: DO-119-02
    name: NotTrashedScope
  - id: DO-119-03
    name: Trash
  - id: DO-119-04
    name: PurgeTrash
    constants: { BATCH_SIZE: 100, SYNC_TIME_BUDGET: 10, ASYNC_TIME_BUDGET: 1800 }
  - id: DO-119-05
    name: PurgeTrashJob
routes:
  - id: API-119-01
    method: GET
    path: /api/v2/Maintenance::purgeTrash
  - id: API-119-02
    method: POST
    path: /api/v2/Maintenance::purgeTrash
  - id: API-119-03
    method: DELETE
    path: /api/v2/Moderation
cli_commands:
  - id: CLI-119-01
    command: php artisan lychee:purge-trash
telemetry_events:
  - id: TE-119-01
    event: jobs.purge_trash.summary
ui_states:
  - id: UI-119-01
    description: Pending deletions card hidden
  - id: UI-119-02
    description: Pending deletions card with count
  - id: UI-119-03
    description: Purging
```
