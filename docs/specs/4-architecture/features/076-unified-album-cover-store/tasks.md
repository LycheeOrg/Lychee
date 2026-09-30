# Feature 076 Tasks – Unified Album Cover Store

_Status: Implemented 2026-09-30_  
_Last updated: 2026-09-30_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Pure picker
- [x] T-076-01 – Failing unit tests for `AutoCoverRows` (F-076-07).  
  _Intent:_ `tests/Unit/Actions/AutoCoverRowsTest` (extends `AbstractTestCase`):
  - admin, not owner → owner row;
  - owner → own row;
  - single-share user → own row;
  - other user → `NULL` row;
  - guest → `NULL` row;
  - empty collection → `null`;
  - `publicRow()` with and without a `NULL` row.

  _Verification commands:_ `php artisan test --filter=AutoCoverRowsTest`
- [x] T-076-02 – Implement `App\Actions\Album\AutoCoverRows` (F-076-07).  
  _Intent:_ Two pure static methods, straight-line, license header, strict comparisons.  
  _Verification commands:_ `php artisan test --filter=AutoCoverRowsTest`, `make phpstan`

### I2 – Schema, model and purge scoping
- [x] T-076-03 – Migration part 1, model, and failing purge tests (F-076-01, F-076-03, S-076-11, S-076-12).  
  _Intent:_
  - `database/migrations/2026_10_01_000001_move_auto_covers_to_album_user_thumbs.php` adds `is_precomputed` (bool, default false).
  - It copies the max triples to owner rows and the least triples to rows keyed per the Row Model (single shared user derived in PHP, chunked). The drop comes in T-076-20.
  - `AlbumUserThumb` gets `$fillable`, the bool cast and a docblock line.
  - Extend `PurgeSideCoversV3Test`: after `forUsers(owner)` and after revoking a sub-album permission, the parent's hand-seeded precomputed rows survive and the cache rows are gone.

  Red until T-076-04.  
  _Verification commands:_ `php artisan test --filter=PurgeSideCoversV3Test`
- [x] T-076-04 – Scope `PurgeAlbumUserThumbs` to cache rows (F-076-10).  
  _Intent:_ `where('is_precomputed', false)` in `forBaseAlbums()` and `forUsers()`; docblock register line.  
  _Verification commands:_ `php artisan test --filter=PurgeSideCoversV3Test`, `make phpstan`

### I3 – Job writes rows
- [x] T-076-05 – Rewrite cover assertions to read rows (F-076-02, S-076-01..04, S-076-17, NFR-076-03, NFR-076-05).  
  _Intent:_ Switch `RecomputeAlbumStatsJobTest`, `DeepNestingPropagationTest`, `CoverSelectionNsfwTest`, `Console/AlbumCoverSecurityTest`, `Console/AlbumMutationScenariosTest` and `Console/ExplicitCoverTest` from `auto_cover_id_*` to precomputed rows (shared helper). Add the cases:
  - single share → X row, no `NULL` row;
  - single share on the owner → no least row;
  - no permission → owner row only;
  - two permissions → `NULL` row;
  - the rank-1 photo deleted → row recomputed (`Queue::fake()` where needed).

  Set the new query-count baseline with a comment.  
  _Verification commands:_ each class above via `php artisan test --filter=<ClassName>`
- [x] T-076-06 – Implement row writes in `RecomputeAlbumStatsJob` (F-076-02).  
  _Intent:_
  - `computeLeastPrivilegeCovers()` returns `[?int $key, triple]`.
  - One transaction with the album save: delete the album's precomputed rows, insert at most two (`writeCoverRows()`).
  - The columns are no longer assigned, and the debug line prints the key.

  _Notes:_ delete + insert instead of an upsert, since the unique key contains the generated `user_id_unique_key`. `JOB_QUERY_BASELINE` 35 → 37.

  _Verification commands:_ the T-076-05 classes, `php artisan test --filter=RecomputeAlbumStatsCommandTest`, `make phpstan`

### I4 – v3 listings via `JoinAutoCover`
- [x] T-076-07 – Implement `JoinAutoCover` (F-076-04).  
  _Intent:_ `App\Actions\Album\StructOfArrays\JoinAutoCover::apply(Builder, ?User)`. Three join shapes chosen by a `match (true)` in PHP. The aliases `auto_cover_id`, `_2`, `_3` come from one row (in the user shape the public join is conditioned on `own.id IS NULL`, then `COALESCE`). All joins filter on `is_precomputed = true`.  
  _Verification commands:_ `make phpstan` (covered by T-076-08)
- [x] T-076-08 – Failing viewer-matrix tests (F-076-05, F-076-06, S-076-06, S-076-07, NFR-076-01).  
  _Intent:_ In `AlbumListV3Test` and `AlbumSideCoversV3Test`: one parent with a child album holding public and private photos, viewed as owner, admin, single-share user, other shared user and guest, across children, root, pinned and search. Assert `cover_ids` / `cover_ids_2` / `cover_ids_3` match the Row Model, with query counts unchanged. `SideCoverIdsTest` drops the privilege cases and uses the new signature.  
  _Verification commands:_ `php artisan test --filter=AlbumListV3Test`, `--filter=AlbumSideCoversV3Test`, `--filter=SideCoverIdsTest`
- [x] T-076-09 – Switch the listing producers (F-076-05, F-076-06).  
  _Intent:_
  - `BuildAlbumDataResource`, `AlbumRootController`, `AlbumPinnedController` and `AlbumListController` call `JoinAutoCover::apply()` and have their `TRow` types updated.
  - `rawCoverId()` becomes `cover_id ?? auto_cover_id`.
  - `SideCoverIds::forAlbumRow()` loses `$user`, and the `BuildsAlbumCategoryResource` call sites are updated.

  _Notes:_ `AlbumListController::resolveCoverId()` / `rawCoverId()` and `toCategoryResource()` lose their `$user` parameter too. `AlbumListV3Test`'s four FR-057-09 cover tests now seed precomputed rows instead of writing the old columns.

  _Verification commands:_ `php artisan test --filter=AlbumListV3Test`, `--filter=AlbumSideCoversV3Test`, `--filter=SideCoverIdsTest`, `--filter=AlbumRootV3Test`, `--filter=AlbumCategoryV3Test`, `--filter=QuerySearchAlbumsTest`, `make phpstan`

### I5 – v2 model path, landing page, Meta
- [x] T-076-10 – Failing v2/landing/meta tests (F-076-07, F-076-08, S-076-08, S-076-09, NFR-076-02).  
  _Intent:_
  - `CoverDisplayPermissionTest`: `thumb` of a v2 album fetch for owner, admin and guest, plus a query-count assertion on the album load.
  - `MetaTest`: public album → `NULL`-row cover; single-share album → no automatic cover.

  _Verification commands:_ `php artisan test --filter=CoverDisplayPermissionTest`, `--filter=MetaTest`
- [x] T-076-11 – Implement `Album::autoCoverRows()` and switch the consumers (F-076-07, F-076-08).  
  _Intent:_
  - A `HasMany` with the viewer constraint.
  - `$with` changes to `autoCoverRows`, `autoCoverRows.photo.size_variants`.
  - `HasAlbumThumb` picks the row via `AutoCoverRows::forViewer()`.
  - `Flow`, `Notify`, `BulkAlbumController`, `GetAlbumChildrenRequest`, `GetAlbumPhotosRequest` and `GetAlbumPersonsRequest` get updated `with`/`without` lists.
  - `LandingPageResource`, `LandingFeaturedContentResource` and `Meta` use `publicRow()`.
  - The two `*_privilege_cover` relations are removed.

  _Notes:_ `CoverageTest`'s reflection tests of `HasAlbumThumb` now target `selectCoverIdForAlbum()` with `autoCoverRows` set as a relation. `CoverDisplayPermissionTest::testModelThumbFollowsTheRowModel` pins the model load at 23 queries (24 before). Also green: `FlowV3Test`, `FlowTest`, `AlbumChildrenDataV3Test`, `AlbumChildrenEndpointTest`, `AlbumPhotosEndpointTest`, `BulkAlbum*`, `LandingPageContentTest`, `LandingFeaturedItems*Test`.

  _Verification commands:_ `php artisan test --filter=CoverDisplayPermissionTest`, `--filter=MetaTest`, `--filter=CoverageTest`, `--filter=LandingPage`, `make phpstan`

### I6 – Asset exception and FulfillPreCompute
- [x] T-076-12 – Failing asset and precompute tests (F-076-11, F-076-12, S-076-13..15, NFR-076-04).  
  _Intent:_
  - `PhotoAssetV3Test`: ranks 1–3 of the owner row and the `NULL` row served for a viewer with access, a non-member 403, and at most one query for a regular album.
  - `FulfillPreComputeTest`: an album with photos but no precomputed row is counted.

  _Verification commands:_ `php artisan test --filter=PhotoAssetV3Test`, `--filter=FulfillPreComputeTest`
- [x] T-076-13 – Implement the asset exception and precompute check (F-076-11, F-076-12).  
  _Intent:_
  - `isPhotoOfAlbum()` for `Album`: `cover_id` in memory, then one `SELECT EXISTS(photo_album …) OR EXISTS(album_user_thumbs … is_precomputed …)`.
  - `FulfillPreCompute::getAlbumsNeedingComputation()` uses `orWhereNotExists` on precomputed rows.

  _Verification commands:_ `php artisan test --filter=PhotoAssetV3Test`, `--filter=FulfillPreComputeTest`, `make phpstan`

### I7 – Ownership and deletion lifecycle
- [x] T-076-14 – Failing lifecycle tests (F-076-09, F-076-10, S-076-10, S-076-16).  
  _Intent:_ New `tests/Feature_v3/Album/AutoCoverRowsV3Test`:
  - transfer of an album with a sub-album to Y, where Y had a single-share row: Y's rows are removed and the owner rows re-keyed to Y;
  - user deletion by an admin: the owner rows are re-keyed to the admin, and the deleted user's single-share rows are gone;
  - album deletion: the precomputed rows are gone.

  _Verification commands:_ `php artisan test --filter=AutoCoverRowsV3Test`
- [x] T-076-15 – Implement re-keying and deletion (F-076-09, F-076-10).  
  _Intent:_
  - `AlbumUserThumb::rekeyOwnerRows(array $album_ids, int $new_owner_id)` deletes the new owner's precomputed rows for those albums, then updates the remaining owner rows to the new owner. It's called from `Transfer::do()` (album + descendants) and `User::delete()` (albums moved), before the existing row cleanup.
  - `Actions\Album\Delete` deletes the precomputed rows of the deleted albums.

  _Verification commands:_ `php artisan test --filter=AutoCoverRowsV3Test`, `--filter=AlbumTransferTest`, `--filter=DeleteUserTest`, `--filter=AlbumDeleteTest`, `--filter=AlbumDeleteTrackTest`, `make phpstan`

  _Notes:_ the row deletion sits in `AlbumsToBeDeletedDTO::executeDelete()`, next to the other per-album dependents. The deletion test attaches the cover photo to a second album, since otherwise the `photo_id` cascade alone removes the row.

### I8 – Recompute on permission change
- [x] T-076-16 – Failing listener and propagate tests (F-076-13, S-076-18, S-076-19, NFR-076-07).  
  _Intent:_
  - `tests/Unit/Listeners/RecomputeAlbumStatsOnAccessPermissionChangeTest`: `Queue::fake()`, one `RecomputeAlbumStatsJob` for the event's album.
  - `tests/Feature_v3/Sharing/SharingPropagateRecomputeV3Test`: propagate (update and overwrite) dispatches one event per descendant, plus the end-to-end S-076-18 (second share → `NULL` row; revoke → X row again).

  _Verification commands:_ `php artisan test --filter=RecomputeAlbumStatsOnAccessPermissionChangeTest`, `--filter=SharingPropagateRecomputeV3Test`
- [x] T-076-17 – Implement and register the listener (F-076-13).  
  _Intent:_ `App\Listeners\RecomputeAlbumStatsOnAccessPermissionChange::handle()` dispatches the job. Register it with `Event::listen(AccessPermissionChanged::class, …)` in `EventServiceProvider`. No model events (N-076-07).  
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumStatsOnAccessPermissionChangeTest`, `make phpstan`
- [x] T-076-18 – Propagate dispatches per descendant (F-076-13).  
  _Intent:_ `Propagate::update()` / `overwrite()` return the touched descendant ids, and `SharingController::propagate()` dispatches `AccessPermissionChanged` for each.  
  _Verification commands:_ `php artisan test --filter=SharingPropagateRecomputeV3Test`, `--filter=SharingTest`, `--filter=AlbumSharingTest`, `--filter=GrantsMoveSharingTest`, `--filter=AlbumAccessPermissionListTest`, `--filter=UserGroupMembershipTest`, `--filter=UserGroupTest`, `make phpstan`

  _Notes:_ `SharingTest`'s four ADR-0010 purge assertions now count cache rows only (`is_precomputed = false`): the recompute triggered by the permission change legitimately writes precomputed rows holding the same photos. Row-key assertions use `assertEqualsCanonicalizing`, since `ORDER BY user_id` places `NULL` differently on PostgreSQL.

### I9 – Drop the columns
- [x] T-076-19 – Confirm no reader remains (G1).  
  _Intent:_ `grep -rnE "auto_cover_id_(max|least)|privilege_cover" app database/factories` is empty apart from `Album` model declarations.  
  _Verification commands:_ the grep
- [x] T-076-20 – Complete the migration and clean the `Album` model (F-076-03, S-076-05).  
  _Intent:_
  - The migration drops the six FKs and columns.
  - `down()` re-adds them, copies the rows back, deletes the precomputed rows and drops `is_precomputed`.
  - Remove the attributes, casts, `$attributes` defaults and docblock lines from `Album`.
  - Rerun every class from T-076-03..18 one at a time.

  _Verification commands:_ the grep (now fully empty), each class listed in I2–I8, `make phpstan`

  _Notes:_ the SQLite test database was reset (`rm` + `touch database/database.sqlite`, per AGENTS.md) so the completed migration applied from scratch. The data copy was verified by a throwaway script (scratchpad, not committed) that ran `down()` then `up()` on a separate scratch SQLite file seeded with a public, a single-share and a self-shared album: identical rows and keys after the round trip. `AlbumSideCoversV3Test::CHILDREN_QUERY_BASELINE` 21 → 20 (lighter parent `Album` load).

### I10 – Docs and quality gate
- [x] T-076-21 – Knowledge map and ADR rewrites (docs).  
  _Intent:_
  - Knowledge map: `albums` computed-field list, `AlbumUserThumb`, `SideCoverIds`, `PurgeAlbumUserThumbs`, the `AccessPermissionChanged` listeners line, new `JoinAutoCover` and `AutoCoverRows` entries.
  - Rewrite ADR-0003, ADR-0010 and ADR-075-01 in place for the single store, with no history wording.

  _Verification commands:_ read-through
- [x] T-076-22 – Quality gate, drift gate, roadmap, commit hand-off.  
  _Intent:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`, the drift gate recorded in plan.md, roadmap row 076 → Implemented, `./scripts/codex-commit-review.sh`, and a copy-paste `git commit` command for the operator.  
  _Verification commands:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`

## Notes / TODOs
- Never run two test commands at once (shared SQLite database).
