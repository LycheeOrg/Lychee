# Feature 119 Tasks – Background Photo Delete

_Status: Implemented (drift gate passed 2026-11-28)_  
_Last updated: 2026-11-28_

## Checklist

### I1 – Trash table and hiding
- [x] T-119-01 – Migration creating `trashed_photos` (DO-119-01: `photo_id` `char(24)` primary, foreign key to `photos.id` `ON DELETE CASCADE`, `trashed_at` indexed). No test (owner rule). Run on the dev DB.  
  _Verification:_ `php artisan migrate --path=database/migrations/<file>` on the dev DB, then `SHOW CREATE TABLE trashed_photos`; `make phpstan`.  
  _Result 2026-11-28:_ `2026_11_28_000001_create_trashed_photos_table` DONE on the dev DB (MariaDB): `photo_id char(24)` primary, `trashed_at` indexed, FK to `photos` `ON DELETE CASCADE`.

- [x] T-119-02 – Failing `tests/Feature_v3/Photo/TrashVisibilityTest.php` (`Feature_v3\Base\BaseApiWithDataTest`): a photo with a `trashed_photos` row is missing for its owner and for an admin from v3 `Albums/{album_id}/Photos` (Unsorted, Recent, a tag album, a person album), `Search/Photos`, `Map/Photos`, `Insights`, `Asset/…` and `Faces/{face_id}/photo/…` (404), and through the v2 helpers from photo by id (404), statistics counts and the duplicate finder; S-119-15 (owner's space without it); S-119-16 (same file uploaded again creates a new photo) (FR-119-03, S-119-06).  
  _Verification:_ `php artisan test --filter=TrashVisibilityTest` (red).

- [x] T-119-03 – `App\Models\Scopes\NotTrashedScope` registered in `Photo::booted()` (DO-119-02); the `NOT EXISTS` condition in the raw reads of FR-119-03 (`Statistics\Spaces` included, so the quota frees at once); check the request-path callers of `SizeVariant::photo`, `Face::photo` and the order-item downloads for a `null` photo (FR-119-03).  
  _Verification:_ `php artisan test --filter=TrashVisibilityTest`, then the suites of the touched reads one at a time (map, insights, statistics, duplicate finder, face asset).

### I2 – Trash action
- [x] T-119-04 – Failing `tests/Unit/Actions/Photo/TrashActionTest.php`: S-119-02 (photo in a surviving album keeps its link, not trashed), S-119-03 (photo in two deleted sub-albums in different chunks, trashed once), S-119-04 (delete from X: also in Y, only in X, unsorted), S-119-05 (`everywhere()` removes every link) (FR-119-01/02).  
  _Verification:_ `php artisan test --filter=TrashActionTest` (red).

- [x] T-119-05 – `App\Actions\Photo\Trash` (DO-119-03): `fromAlbums()`, `fromAlbum()`, `everywhere()` in one transaction each (links removed, `INSERT … WHERE NOT EXISTS photo_album`, already trashed photos skipped, chunks of `ID_CHUNK_SIZE`); `resetCoversAndHeaders()` moved from `PhotosToBeDeletedDTO` (FR-119-01/02, FR-119-06).  
  _Verification:_ `php artisan test --filter=TrashActionTest`.

### I3 – Purge
- [x] T-119-06 – Failing `tests/Unit/Actions/Photo/PurgeTrashServiceTest.php`: S-119-07 (re-linked photo leaves the trash), S-119-08 (second batch throws: first purged, second still trashed, error reported), S-119-09 (budget 0 s: one batch; none: all; job on async with photos left after its budget dispatches itself again; in console on `sync` no budget), S-119-13 (sold variant kept), batches of 100 oldest first (FR-119-04/05, NFR-119-03).  
  _Verification:_ `php artisan test --filter=PurgeTrashServiceTest` (red).

- [x] T-119-07 – `App\Actions\Photo\PurgeTrash` and `PurgeTrashResult` (DO-119-04), `App\Jobs\PurgeTrashJob` (`ShouldBeUniqueUntilProcessing`; 10 s on `sync` in an HTTP request, none in console, 30 minutes on async then self re-dispatch; check `runningInConsole()` under FrankenPHP, DO-119-05), log line TE-119-01; jobs of each batch dispatched with `dispatch()` after its commit (FR-119-04/05/07).  
  _Verification:_ `php artisan test --filter=PurgeTrashServiceTest`, `--filter=PhotosToBeDeletedDTOTest`.

### I4 – Delete actions through the trash
- [x] T-119-08 – Failing `tests/Feature_v3/Photo/TrashDeleteTest.php` (`DELETE /api/v2/Album` and `/api/v2/Photo` through the inherited helpers): S-119-01 (502 photos, query log: no `size_variants` statement, no `photos` write, ≤1000 bindings, `PurgeTrashJob` after response on `sync` and queued on `database`), S-119-12 (side effects with the delete, row-dependent jobs only from the purge), S-119-14 (no fake: rows gone after the call) (FR-119-01/02/05/06, NFR-119-01/02).  
  _Verification:_ `php artisan test --filter=TrashDeleteTest` (new cases red).

- [x] T-119-09 – `Album\Delete::do()` on `Trash::fromAlbums()` (counting in `findAllPhotosToDelete()` removed), `Photo\Delete::do()` on `Trash::fromAlbum()`, `forceDeletePhoto()` on `Trash::everywhere()`; side effects per FR-119-06 (webhooks and thumb targets from the trashed ids, after the trash step); deletes from smart, tag and person albums per Q-119-06; `PurgeTrashJob` through `DispatchAfterDelete` when photos were trashed; `PhotosToBeDeletedDTO`'s soft-delete part removed (FR-119-01/02/05/06).  
  _Verification:_ `php artisan test --filter=TrashDeleteTest`, `--filter=DeleteBindLimitTest`, `--filter=DeleteAfterResponseTest`, `--filter=PhotoDeleteFanOutTest`, `--filter=AlbumDeleteTrackTest`.

### I5 – Existing suites
- [x] T-119-10 – Unit-level suites that call delete actions directly or fake the bus: run each, add an explicit purge where they assert missing rows or deferred jobs (`Tests\Unit\Actions\Album\DeleteTest`, `Tests\Unit\Actions\Photo\DeleteTest`, `MoveOrDuplicateTest`, the `ManagedCache*InvalidatorTest`s, `Precomputing\CoverSelection\EventListenersTest`, `EventPropagationIntegrationTest`, `AlbumSizeEventListenerTest`) (S-119-14).  
  _Verification:_ each class with `php artisan test --filter=<Class>`.

- [x] T-119-11 – Feature-level suites: `AlbumDeleteTest`, `DeleteGrantTest`, `BulkAlbumEdit\DeleteTest`, `WebhookDestroyTest`, `ModerationHistoryListTest`, `ImageProcessing\Photo\PhotoDeleteTest`, `SyncTest`, `GhostbusterTest`, `NsfwActionServiceTest`, `ModerationHistoryNsfwTest`, `DeletePurchasedPhotoTest`, `PhotoUnownedDuplicateTest`, `UploadStatusJobTest`, the MoveGrant suites, `PhotoTitleSyncTest`, `PhotoKeysWriteTest`, `Detect360Test` (S-119-14).  
  _Verification:_ each class with `php artisan test --filter=<Class>`.

### I6 – Resume paths
- [x] T-119-12 – Failing `tests/Feature_v3/Maintenance/PurgeTrashMaintenanceTest.php` (count, purge, non-admin 403, ≤1000 bindings) and `tests/Unit/Console/Commands/PurgeTrashCommandTest.php` (S-119-10, S-119-11).  
  _Verification:_ `php artisan test --filter=PurgeTrashMaintenanceTest`, `--filter=PurgeTrashCommandTest` (red).

- [x] T-119-13 – `Admin\Maintenance\PurgeTrash` (`check`, `do`: until empty, `maintenance_processing_limit` or 10 s), routes `GET`/`POST /api/v2/Maintenance::purgeTrash`, `lychee:purge-trash` command (API-119-01/02, CLI-119-01, FR-119-05).  
  _Verification:_ `php artisan test --filter=PurgeTrashMaintenanceTest`, `--filter=PurgeTrashCommandTest`, `--filter=OpenApiTest`.

### I7 – v8 Maintenance card
- [x] T-119-14 – `MaintenancePurgeTrash.vue` (UI-119-01..03, as `MaintenanceMissingPalettes.vue`), `purgeTrashCheck()` / `purgeTrashDo()` in `maintenance-service.ts`, registration in `v8/views/admin/Maintenance.vue`, `maintenance.purge-trash` (`title`, `description` with `%d`, `button`) in every `lang/*/maintenance.php` (FR-119-08).  
  _Verification:_ `vp check --fix` on the changed files, `vp run typecheck`, `php artisan test --filter=LangTest`.

### I7b – Refused deletes
- [x] T-119-16 – Failing S-119-17/18 in `tests/Feature_v3/Photo/TrashDeleteTest.php` (`from_id` `recent`, a tag album, a person album → 422; a photo not in X with `from_id` X → 422 naming it; `unsorted` with a photo in an album → 422; `unsorted` with a photo without album → trashed; nothing trashed or unlinked on 422) and S-119-19 in `resources/js/v8/composables/contextMenus/contextMenu.test.ts` (smart album other than Unsorted: no delete entry) (FR-119-09).  
  _Verification:_ `php artisan test --filter=TrashDeleteTest`, `vp test` on `contextMenu.test.ts` (red).

- [x] T-119-17 – `DeletePhotosRequest`: membership check in photo chunks (linked to `from_id`, or no link for `unsorted`), 422 naming at most 10 photos then a count; v8: delete hidden in `contextMenu.ts` and the `Album.vue` shortcuts for smart (non-Unsorted), tag and person album views, and in `Timeline.vue`, `Tag.vue`, `Search.vue`, `face-recog/PersonDetail.vue` (FR-119-09).  
  _Verification:_ `php artisan test --filter=TrashDeleteTest`, `--filter=DeletePhotosRequestTest` if present, `vp test` on `contextMenu.test.ts`, `vp check --fix` on the changed files, `vp run typecheck`.

- [x] T-119-18 – v7: the same hiding in `Album.vue`, `Timeline.vue`, `Tag.vue`, `Search.vue`, `face-recog/PersonDetail.vue` (FR-119-09, S-119-19).  
  _Verification:_ `vp check --fix` on the changed files, `vp run typecheck`.

- [x] T-119-19 – Failing `tests/Feature_v3/Moderation/ModerationDeleteTest.php`: S-119-20 (admin: photo in two albums, in one album, without album → all trashed with every link removed, `PurgeTrashJob` dispatched; non-admin 403; more than 500 ids 422) (FR-119-10).  
  _Verification:_ `php artisan test --filter=ModerationDeleteTest` (red).

- [x] T-119-20 – `DeleteModerationRequest` (admin only, `photo_ids` 1–500), `ModerationController::delete` on `Trash::everywhere()` with the FR-119-06 side effects and `PurgeTrashJob`, route `DELETE /api/v2/Moderation`; `ModerationService.delete(photo_ids)` and both `admin/Moderation.vue` pages on it (FR-119-10, API-119-03).  
  _Verification:_ `php artisan test --filter=ModerationDeleteTest`, `--filter=ModerationHistoryListTest`, `--filter=OpenApiTest`, `vp check --fix` on the changed files, `vp run typecheck`.

### I8 – Docs and quality gate
- [x] T-119-15 – Knowledge map, roadmap, release note in the plan, drift gate; `vendor/bin/php-cs-fixer fix`, `make phpstan`; commit command for the owner.  
  _Verification:_ re-read spec, plan, tasks, ADR-119-01 against the code; all commands clean.

## Notes / TODOs
Run test classes one at a time (shared SQLite DB). Scope `vp check --fix` to the changed files. No new Feature_v2 test class (owner, 2026-11-27): HTTP cases go to Feature_v3, the rest to Unit.
