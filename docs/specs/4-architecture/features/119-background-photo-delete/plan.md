# Feature Plan 119 – Background Photo Delete

Linked specification: [spec.md](spec.md). Tasks: [tasks.md](tasks.md). Decision: [ADR-119-01](../../../6-decisions/ADR-119-01-photo-trash-and-background-purge.md). Status: implemented, analysis and drift gates passed 2026-11-28. Updated: 2026-11-28.

## Vision & Success Criteria
A delete request touches `photo_album`, `trashed_photos` and album rows only, whatever the disk. Photos left without an album link are trashed and hidden at once, from their owner and admins too. A purge deletes them in batches of 100, right after the delete with a worker, after the response on `sync` within 10 s, and through a Maintenance card (v8 and v7) or `php artisan lychee:purge-trash` for what is left. Nothing is lost when a purge stops.

## Scope Alignment
- Owner (2026-11-27): Q-112-13 Option A (trash), then A for Q-119-01..05.
- Q-119-06 C (owner 2026-11-28, "we should still be able to delete from unsorted"): deletes from smart albums other than Unsorted, tag albums and person albums are refused (422) and hidden in v8 and v7 (FR-119-09).
- Q-119-07 A (owner 2026-11-28): every selected photo must be in `from_id` (Unsorted: in no album), else 422; delete hidden in Timeline, Tag, Search and Person detail in v8 and v7. Moderation deletes without an album (owner, 2026-11-28): `DELETE /api/v2/Moderation`, every link removed, photos trashed (FR-119-10).
- Owner rule (2026-11-27): `photo_album` is many-to-many, a photo is trashed only when its last link is removed (FR-119-01/02, re-checked by the purge in FR-119-04).
- Non-goals per spec: no restore or retention, no scheduler, no blocking of direct file URLs before the purge, no change to what a purge removes.

## Dependencies & Interfaces
- Feature 112: `PhotosToBeDeletedDTO` (per-chunk force delete, FR-112-14), `DispatchAfterDelete`, `QueryLimits::ID_CHUNK_SIZE`, `AlbumUserThumbTargets`, `PurchasableService`, `tests/Traits/CreatesBulkFixtures.php`.
- `App\Actions\Album\Delete` (`do()`, `findAllPhotosToDelete()` replaced, `AlbumsToBeDeletedDTO` kept), `App\Actions\Photo\Delete` (`do()`, `forceDeletePhoto()`), `Photo::delete()`, import pipes `DeleteMissingPhotos` / `DeleteMissingAlbums`, admin `BulkAlbumController::destroy`, NSFW block action.
- `App\Models\Photo` (`booted()`, `PhotoBuilder`), relations that load a photo from a size variant or a face.
- Raw `photos` reads (FR-119-03): `Map\QueryMapBuckets`, `Insights\ComputeInsights`, `Insights\InsightsRevision`, `Insights\PeriodEdges`, `Photo\DuplicateFinder`, `Statistics\Counts`, `Statistics\Spaces`, `Diagnostics\Pipes\Checks\StatisticsIntegrityCheck`, `Face\GetFacePhotoAssetRequest`. Not affected: `RecomputeAlbumStatsJob` (through `photo_album`, trashed photos have no link), `ManagedCachePhotoListingInvalidator`, `BucketKeyWriter`, the delete code itself.
- Maintenance pattern: `MaintenanceRequest`, `Admin\Maintenance\*` controllers (`check` / `do`), routes in `routes/api_v2.php`, `resources/js/services/maintenance-service.ts`, `resources/js/v8/components/maintenance/MaintenanceRow.vue`, `resources/js/v8/views/admin/Maintenance.vue`, the v7 cards on PrimeVue `Card` (`resources/js/v7/components/maintenance/MaintenanceMissingPalettes.vue`, `resources/js/v7/views/admin/Maintenance.vue`), `lang/*/maintenance.php` (23 locales).
- Config `maintenance_processing_limit` (default 5000).
- Read paths affected by the scope (FR-119-03): `Photo\Pipes\Init\FindDuplicate`, `Photo\Create::checkQuota()` through `Statistics\Spaces::getFullSpacePerUser()`, `SizeVariant::photo`, `Face::photo`, order-item downloads.
- Test bases: `Tests\Feature_v3\Base\BaseApiWithDataTest` (v2 routes through the inherited helpers, as `Feature_v3/Maintenance/RecomputeBucketsTest`), `Tests\AbstractTestCase` for Unit.

## Assumptions & Risks
- The global scope hides trashed photos from code that runs while they wait: jobs already queued for them fail with a missing model, as they do today for a deleted photo. Route model binding answers 404. Relations from a size variant or a face to its photo return `null` for a trashed photo; T-119-03 checks their request-path callers.
- A raw `photos` read missed by the audit shows a trashed photo to its owner or an admin until the purge. The window is short with a worker.
- On `sync` under Octane, the request, the 10 s budget and the last batch share the 30 s limit. A batch of 100 photos keeps a batch short (estimate on the 2026-11-27 server: well under the budget).
- `PurgeTrashJob` is `ShouldBeUniqueUntilProcessing` on the cache store. With a per-container file cache (Feature 116, `WorkerCacheCheck`) the lock is per container. Correctness does not depend on it (FR-119-05).
- HTTP tests on `sync` run the after-response purge in the kernel's `terminate()` (Feature 112), so their "rows gone" assertions hold. Tests that call a delete action directly or fake the bus need an explicit purge (T-119-10/11).
- The purge's budget depends on `app()->runningInConsole()`: FrankenPHP's SAPI is not `cli`, so Octane requests get the 10 s budget and `lychee:sync` gets none. Checked in T-119-07.
- Migration: no test (owner rule). Run on the dev DB with `php artisan migrate --path=…` only (never `migrate:fresh`, `migrate:reset`, `db:wipe`).
- `PhotosToBeDeletedDTO`'s soft-delete part is no longer used by the actions (links are removed by `Trash`). The purge builds it with force-delete ids only. The unused part is removed in T-119-09.

## Implementation Drift Gate
2026-11-28: PASS.
1. Every FR has code and a test: FR-119-01/02 `Trash`, `Album\Delete`, `Photo\Delete` (`TrashActionTest`, `TrashDeleteTest`); FR-119-03 `NotTrashedScope` and the raw reads (`TrashVisibilityTest`); FR-119-04/05/07 `PurgeTrash`, `PurgeTrashJob` (`PurgeTrashServiceTest`); FR-119-05 Maintenance and command (`PurgeTrashMaintenanceTest`, `PurgeTrashCommandTest`); FR-119-06 (`TrashDeleteTest`, `DeleteAfterResponseTest`); FR-119-08 v8 and v7 cards (typecheck, by hand); FR-119-09 `DeletePhotosRequest` and the UI gating (`TrashDeleteTest`, `contextMenu.test.ts`); FR-119-10 `DELETE /api/v2/Moderation` (`ModerationDeleteTest`). NFR-119-01/02 in `TrashDeleteTest` and the Maintenance test, NFR-119-03 in `PurgeTrashServiceTest`.
2. Red first, with two exceptions: `TrashDeleteTest`'s first three cases were written after T-119-09 (they pass; on the old code the album delete removed the photo rows, so the trash count would be 0), and the refused-delete, Maintenance, command and Moderation tests were written while the code was in place (the routes and classes did not exist before). `TrashVisibilityTest` was red first (5 cases for the right reason; 3 needed test fixes: the map configs, stored asset bytes, and the space statistics method instead of the quota one), `TrashActionTest` and `PurgeTrashServiceTest` were red first (class missing). The dismissed-face case of Feature 112 and the purge's face handling were checked by mutation earlier (FR-112-14).
3. Lightweight adjustments, no decision needed:
   - The quota reads album sizes only (`getFullSpacePerUser()`), so S-119-15 tests the space statistics (`getSpacePerSizeVariantTypePerUser()`) and the spec says the quota frees when album sizes are recomputed.
   - The asset resolver (`ResolvesSizeVariantAssetTrait`) needed the filter too: it reads size variants, and an album's automatic cover let a trashed photo through.
   - The Moderation history query skips events of trashed photos (`whereHas('photo')`), whose resource reads `$event->photo->title`.
   - `Album\Delete` flushes the album listing cache only when the deleted albums held photos, as the old DTO did (`AlbumMergeTest`).
   - `PhotosToBeDeletedDTO` lost its soft-delete part and `album_ids`.
   - The multi-photo menu entry now checks the delete right (it checked `can_edit`).
   - Search, Tag, Timeline and Person detail lose their delete shortcuts, and their photo panels hide the button (`canDelete` prop).
4. Existing suites adapted (no new Feature_v2 class): `Unit\Actions\Album\DeleteTest`, `Unit\Actions\Photo\DeleteTest` (explicit purge), `Feature_v2\DeleteAfterResponseTest` (purge job after the response, then the purge dispatches the file, person-stats and face jobs), `Feature_v2\PhotoDeleteFanOutTest` (gone means hidden), `PhotosToBeDeletedDTOTest` (constructor).
5. Verification, one class at a time: TrashVisibilityTest 8, TrashActionTest 7, PurgeTrashServiceTest 9, TrashDeleteTest 5, PurgeTrashMaintenanceTest 3, PurgeTrashCommandTest 2, ModerationDeleteTest 3, PhotosToBeDeletedDTOTest 3, `DeleteTest` filter 44, DeleteAfterResponseTest 4, PhotoDeleteFanOutTest 10, DeleteBindLimitTest 5, AlbumDeleteTrackTest 2, AlbumDeleteTest 3, PhotoDeleteTest 4, DeleteGrantTest 11, DeletePolicyBindLimitTest 8, DeleteAlbumsRequestTest 2, AlbumMergeTest 4, MergeTest 7, OpenApiTest 1, LangTest 2, the Insights (4 classes), Map (2), Moderation history (3), DuplicateFinderTest, StatisticsIntegrityTest, PhotoAssetV3Test 52, AssetMiddlewareV3Test 18, FacePhotoAssetTest 13, PeopleControllerTest 29, FaceDismissTest 12, DestroyDismissedFacesTest, the cache invalidator, cover and size listener suites, SyncTest 51, ImportFromServerTest 17, SharingTest 30, PhotoRotateTest 4, AlbumCategoryV3Test 20, UserGroupTest 9, `Tests\Webshop` 287, PhotoAddTest 10, SmartAlbumsTest 16, WebhookDestroyTest, ModerationHistoryNsfwTest, GhostbusterTest, NsfwActionServiceTest, DeletePurchasedPhotoTest, PhotoUnownedDuplicateTest, UploadStatusJobTest, the MoveGrant suites, PhotoTitleSyncTest, PhotoKeysWriteTest, Detect360Test. `contextMenu.test.ts` 6. php-cs-fixer, `make phpstan`, `vp check --fix` on the 27 changed frontend files (in batches), `vp run typecheck`: clean.
5b. T-119-21 (v7 card, 2026-11-28): `vp check --fix` on `v7/components/maintenance/MaintenancePurgeTrash.vue` and `v7/views/admin/Maintenance.vue`, `vp run typecheck`: clean. No PHP change.
6. Not run locally: the full suites (CI), a manual check of the v7 and v8 screens, the migration on MySQL/PostgreSQL in CI.

## Increment Map
1. **I1 – Trash table and hiding** (FR-119-03, DO-119-01/02, S-119-06, S-119-15/16). Migration, run on the dev DB. Failing `Feature_v3/Photo/TrashVisibilityTest`, then `NotTrashedScope`, the raw reads, the null relations.
2. **I2 – Trash action** (FR-119-01/02, DO-119-03, S-119-02..05). Failing `Unit/Actions/Photo/TrashActionTest`, then `Trash::fromAlbums()`, `fromAlbum()`, `everywhere()`, and `resetCoversAndHeaders()` moved from the DTO.
3. **I3 – Purge** (FR-119-04/07, DO-119-04/05, NFR-119-03, S-119-07..09, S-119-13). Failing `Unit/Actions/Photo/PurgeTrashServiceTest`, then `PurgeTrash`, `PurgeTrashJob` (budgets, self re-dispatch), TE-119-01.
4. **I4 – Delete actions through the trash** (FR-119-01/02/05/06, NFR-119-01/02, S-119-01, S-119-12, S-119-14). Failing `Feature_v3/Photo/TrashDeleteTest`, then `Album\Delete` and `Photo\Delete` on `Trash` with `PurgeTrashJob` through `DispatchAfterDelete`, counting removed, DTO's soft-delete part removed.
5. **I5 – Existing suites** (S-119-14). Run every delete-related suite, adapt the direct-call and bus-faked ones with an explicit purge. Two tasks (unit-level, then feature-level).
6. **I6 – Resume paths** (FR-119-05, API-119-01/02, CLI-119-01, S-119-10/11). Failing `Feature_v3/Maintenance/PurgeTrashMaintenanceTest` and `Unit/Console/Commands/PurgeTrashCommandTest`, then the controller, routes and command.
7. **I7 – v8 Maintenance card** (FR-119-08, UI-119-01..03). Component, service calls, registration, `maintenance.purge-trash` keys in every locale.
7b. **I7b – Refused deletes and Moderation delete** (FR-119-09/10, S-119-17..20). Failing 422 cases in `TrashDeleteTest` and the album-view case in `contextMenu.test.ts`, then the membership rule in `DeletePhotosRequest` and the v8 views, then v7. Failing `ModerationDeleteTest`, then `DELETE /api/v2/Moderation` and both Moderation pages on it.
7c. **I7c – v7 Maintenance card** (FR-119-08, UI-119-01..03). `v7/components/maintenance/MaintenancePurgeTrash.vue` as the v7 `MaintenanceMissingPalettes.vue`, with the count reloaded after a purge, registered in `v7/views/admin/Maintenance.vue`. Service calls and translations from I7.
8. **I8 – Docs and quality gate**: knowledge map, roadmap, release note, drift gate, php-cs-fixer, phpstan, `vp check` on the changed files, typecheck, commit.

## Scenario Tracking
| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-119-01 | I4 / T-119-08/09 | `Feature_v3/Photo/TrashDeleteTest`, query log, bus faked |
| S-119-02..05 | I2 / T-119-04/05 | `Unit/Actions/Photo/TrashActionTest` |
| S-119-06, S-119-15/16 | I1 / T-119-02/03 | `Feature_v3/Photo/TrashVisibilityTest` |
| S-119-07..09, S-119-13 | I3 / T-119-06/07 | `Unit/Actions/Photo/PurgeTrashServiceTest` |
| S-119-10 | I6 / T-119-12/13 | `Feature_v3/Maintenance/PurgeTrashMaintenanceTest` |
| S-119-11 | I6 / T-119-12/13 | `Unit/Console/Commands/PurgeTrashCommandTest` |
| S-119-12 | I4 / T-119-08/09 | `Feature_v3/Photo/TrashDeleteTest`, bus and events faked |
| S-119-14 | I5 / T-119-10/11 | the existing Feature_v2 HTTP delete suites, no new class |

## Analysis Gate
2026-11-27: PASS.
1. Specification completeness: PASS. Q-119-01..05 and the owner's last-link rule are folded into FR-119-01..08, NFR-119-01..03, S-119-01..14, the UI mock-up (v8 card), the catalogue and ADR-119-01.
2. Open questions: PASS. No `Open` row in open-questions.md. ADR-119-01 records the trash table, the global scope and the deferred deletion.
3. Plan alignment: PASS. Vision, scope, dependencies and risks match the spec.
4. Tasks coverage: PASS. Every FR maps to tasks (FR-01/02 → T-04/05/08/09, FR-03 → T-01..03, FR-04/07 → T-06/07, FR-05 → T-07/09/12/13, FR-06 → T-08/09, FR-08 → T-14). NFR-01/02 → T-08, NFR-03 → T-06. Failing tests precede each implementation task. Failure branches covered: failing batch (S-119-08), re-linked photo (S-119-07), non-admin (S-119-10). Each task ≤90 min. T-119-10/11 are split by test area.
5. Working agreements: PASS. Spec first, tests first, no new dependency, no fallback or shim. The migration follows the owner rule. Branching lives in small units (`Trash`, `PurgeTrash`, the scope).
6. Tooling: PASS. Commands below.

2026-11-27 (spec review, owner: "look for ambiguities, clarifications" and "least amount of tests added to Feature v2, use Feature v3 if necessary"): REOPENED. Q-119-06 is open (photo deletes from smart, tag and person albums remove no link). Lightweight corrections applied without a decision:
- Tests: no new Feature_v2 class. HTTP cases in Feature_v3 (v2 routes through the inherited helpers), action, purge and command cases in Unit (spec Test Strategy, tasks).
- FR-119-06 ordering: webhooks and thumb targets are computed from the trashed ids after the trash step (the rows stay until the purge). "Before the links change" contradicted the `NOT EXISTS` rule of FR-119-01/02.
- Quota: `Statistics\Spaces` is in FR-119-03's list, so the upload quota stops counting a trashed photo at once. The Non-Goal claiming the opposite is replaced (S-119-15).
- Budgets: 10 s only on `sync` in an HTTP request; none in console (import); 30 minutes on async, then the job dispatches itself again (FR-119-05, DO-119-04/05).
- `PurgeTrashJob` is `ShouldBeUniqueUntilProcessing`, not `ShouldBeUnique`: with the latter a delete during a queued or running purge could add no job and leave photos waiting.
- FR-119-03 names the consequences of the scope: `FindDuplicate` (S-119-16), null relations, migration `down()`.
- Non-Goals: person stats lag until the purge, files shared by duplicates (pre-existing).

Gate blocked until Q-119-06 is answered.

2026-11-28: Q-119-06 answered C (Unsorted keeps working), encoded in FR-119-02/09, S-119-17/18 and a UI mock-up. Encoding it showed that Timeline, the Tag page and the Person page send `from_id = "unsorted"` and Search the searched album, so the silent no-op stays there: logged Q-119-07. Gate blocked until it is answered.

2026-11-28 (after Q-119-07 A): PASS.
1. Spec completeness: PASS. Q-119-06 C and Q-119-07 A in FR-119-02/09, S-119-17..19, the UI mock-up and the Status/Overview. The review corrections of 2026-11-27 are in FR-119-03/05/06, DO-119-01/04/05, S-119-09/15/16, the Non-Goals and the Test Strategy.
2. Open questions: PASS. No `Open` row. No ADR for Q-119-06/07: a request validation and UI gating, no boundary or persistence change. ADR-119-01 is unchanged except the purge budgets.
3. Plan alignment: PASS. Scope, dependencies, risks and increments match the spec.
4. Tasks coverage: PASS. FR-119-09 → T-119-16..18 (backend and v8, then v7 and Moderation). The rest as in the first gate. Tests precede code. No new Feature_v2 class: Feature_v3 and Unit, plus `contextMenu.test.ts`.
5. Working agreements: PASS. No dependency, no fallback. The membership check is one chunked count per request.
6. Tooling: PASS. Commands below, `vp test` added for `contextMenu.test.ts`.

2026-11-28 (owner: "moderation should be delete without needing to give the album id"): PASS after folding it in. FR-119-10, API-119-03, S-119-20, DO-119-03 (`everywhere()` takes a list). The per-album Moderation requests are dropped from FR-119-09. Tasks T-119-19/20 added, T-119-18 reduced to v7. No ADR: an admin endpoint on the existing Moderation pattern.

## Commands
- One at a time (shared SQLite DB): `php artisan test --filter=TrashVisibilityTest`, `--filter=TrashActionTest`, `--filter=TrashDeleteTest`, `--filter=PurgeTrashServiceTest`, `--filter=PurgeTrashMaintenanceTest`, `--filter=PurgeTrashCommandTest`, `--filter=ModerationDeleteTest`, `--filter=PhotosToBeDeletedDTOTest`, then the Feature 112 suites (`DeleteBindLimitTest`, `DeleteAfterResponseTest`, `PhotoDeleteFanOutTest`, `AlbumDeleteTrackTest`, `DeleteTest`, `GhostbusterTest`, `NsfwActionServiceTest`, `SyncTest`) and the suites listed in T-119-10/11.
- Migration: `php artisan migrate --path=database/migrations/<file>` on the dev DB.
- `vendor/bin/php-cs-fixer fix`, `make phpstan`, `vp check --fix <changed files>`, `vp run typecheck`, `vp test` (`contextMenu.test.ts`), `php artisan test --filter=LangTest`.

## Exit Criteria
All tasks `[x]`, scoped tests green, php-cs-fixer, phpstan, `vp check` and typecheck clean, roadmap and knowledge map updated, release note drafted, commit made.

## Follow-ups / Backlog
- A restore from the trash, if ever wanted (needs the photos' former albums, which `trashed_photos` does not keep).
- A second `DB_LOG_SQL` run on the 2026-11-27 server after deployment (owner).

## Intent & Tool Usage
- 2026-11-27: Owner asked "What about the trash implementation?" after Feature 112 I12. Checked `PhotoQueryPolicy` (photos without a link are only visible to their owner, which corrected Q-112-13's assumption), the Maintenance pattern (`MissingPalettes`, `MaintenanceMissingPalettes.vue`, `maintenance-service.ts`), `maintenance_processing_limit` (5000), the raw `photos` reads (14 files), `Photo\Delete::do()` / `forceDeletePhoto()`, the thumb recompute (`$album->photos()`), the `/api/v3/Asset` route, and the tests touching deletes. Logged Q-119-01..05. Owner added the last-link rule, then answered all five with A. Spec, ADR-119-01, plan and tasks written. Gate: PASS.
- 2026-11-28: Owner asked to "commit and implement and commit and push". Branch `background-photo-delete` from `delete-albums-timeout`. Docs committed first, then T-119-01..20 in order of the increments (migration run on the dev DB with `--path`). A long `Tests\Webshop` run went to the background, and no other test ran until it finished (shared SQLite DB). The v7 `Timeline.vue` import of `ref` was duplicated by an edit script and fixed after the typecheck caught it.
