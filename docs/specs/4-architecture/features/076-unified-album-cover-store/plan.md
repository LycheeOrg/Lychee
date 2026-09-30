# Feature Plan 076 – Unified Album Cover Store

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented 2026-09-30  
_Last updated:_ 2026-09-30

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Regular-album automatic covers live in `album_user_thumbs` next to the tag/person/smart cache rows (ADR-076-01), and `albums` loses its six `auto_cover_id_*` columns. Success criteria:
- Every v2/v3 response returns the same cover ids as before for owner, admin, single-share user, other user and guest (S-076-06..09).
- Listing query counts are unchanged (NFR-076-01).
- The v2 album load uses no more queries than before (NFR-076-02).
- The job adds at most two writes per album (NFR-076-03).
- The asset endpoint runs at most one query for a regular album (NFR-076-04).
- Non-owner, non-admin viewers never get an owner-row photo (NFR-076-05).
- `grep -rn "auto_cover_id_\(max\|least\)\|privilege_cover" app` is empty at the end.

## Scope Alignment
- **In scope:** FR-076-01..13: one migration, `AlbumUserThumb`/`Album` model changes, `RecomputeAlbumStatsJob` row writes, `JoinAutoCover`, `AutoCoverRows`, the four regular-album listing producers, `SideCoverIds`, `HasAlbumThumb` and the `with`/`without` lists, landing page and `Meta`, `GetPhotoAssetRequest`, `FulfillPreCompute`, `PurgeAlbumUserThumbs`, `Transfer`, `User::delete()`, `Actions\Album\Delete`, the `AccessPermissionChanged` listener and `propagate()` dispatches.
- **Out of scope:** NG1–NG7 in the spec (API/frontend, cache-row lifecycle, cover selection query, `cover_id`/`header_id`, table rename, purging precomputed rows, Eloquent model events).

## Dependencies & Interfaces
- ADR-0003 (precomputed covers), ADR-0009 (SoA listings), ADR-0010 (cache purge invariant), ADR-075-01 (rank triples), ADR-076-01 (this feature).
- Feature 053 managed caches: the existing `AccessPermissionChanged` invalidators stay registered unchanged.
- `DebouncesLatestJobTrait` on `RecomputeAlbumStatsJob` (NFR-076-07).
- Test bases: `AbstractTestCase` (Unit, Precomputing), `BaseApiWithDataTest` (Feature_v3). No new Feature_v2 tests. `Queue::fake()` wherever a test asserts rows after a photo deletion or a sharing change (the `PhotoDeleted` listener recomputes synchronously in tests).

## Assumptions & Risks
- **Assumptions:** tests run on SQLite, and the migration applies automatically. `access_permissions` rows are unique per `(base_album_id, user_id)`.
- **Risks / Mitigations:**
  - Between I3 and I5, readers of the old columns see stale data. Increments run in order within one commit, and each increment's own test classes end green. The columns are dropped only in I9, after `grep` confirms no reader remains.
  - The migration must derive the single-share user portably. Use a grouped `access_permissions` query in PHP (`count = 1 AND user_id IS NOT NULL AND user_id <> owner_id`), chunked, with no driver-specific SQL.
  - Writing rows against a unique key that includes the generated `user_id_unique_key`: MySQL/MariaDB reject values for generated columns, so the job deletes the album's precomputed rows and inserts the new ones (two statements, FR-076-02).
  - A per-descendant `AccessPermissionChanged` on large propagate calls also runs the two cache invalidators once per descendant (a few tag forgets and one `SELECT` each). Accepted under Q-076-02 A.
  - The query-count baselines in the existing tests move for the job (NFR-076-03) and for the v2 album load (NFR-076-02). Update them to the new number with the reason in the test, and never loosen them to `>=`.

## Implementation Drift Gate
After all tasks are `[x]`:
- rerun `vendor/bin/php-cs-fixer fix --dry-run`, `make phpstan` and every test class named in tasks.md, one at a time;
- confirm `grep -rnE "auto_cover_id_(max|least)|privilege_cover" app database/factories` is empty;
- walk FR-076-01..13 against the diff;
- record the traceability table and lessons in `## Drift Gate Record` below.

## Increment Map

1. **I1 – Pure picker**
   - _Goal:_ `AutoCoverRows::forViewer()` / `publicRow()` (FR-076-07 rule, DO-076-04).
   - _Preconditions:_ none.
   - _Steps:_ failing `tests/Unit/Actions/AutoCoverRowsTest` (admin → owner row; owner → own row; single-share user → own row; other user → `NULL` row; guest → `NULL` row; no rows → `null`; `publicRow`), then a straight-line implementation.
   - _Commands:_ `php artisan test --filter=AutoCoverRowsTest`, `make phpstan`.
   - _Exit:_ green.
2. **I2 – Schema, model and purge scoping**
   - _Goal:_ FR-076-01, FR-076-03 steps 1–2, FR-076-10 purge half.
   - _Preconditions:_ none.
   - _Steps:_ failing assertions in `PurgeSideCoversV3Test` (S-076-11, S-076-12), seeding a precomputed row by hand. Migration `2026_10_01_000001_move_auto_covers_to_album_user_thumbs.php` adds `is_precomputed` and copies the triples (column drop comes in I9). `AlbumUserThumb` gets the fillable, cast and docblock. `PurgeAlbumUserThumbs` gets `where('is_precomputed', false)`.
   - _Commands:_ `php artisan test --filter=PurgeSideCoversV3Test`, `make phpstan`.
   - _Exit:_ green.
3. **I3 – Job writes rows**
   - _Goal:_ FR-076-02, NFR-076-03, S-076-01..04, S-076-17.
   - _Preconditions:_ I2.
   - _Steps:_ rewrite the assertions in `RecomputeAlbumStatsJobTest`, `DeepNestingPropagationTest`, `CoverSelectionNsfwTest`, `Console/AlbumCoverSecurityTest`, `Console/AlbumMutationScenariosTest` and `Console/ExplicitCoverTest` to read rows (add a small test helper `precomputedRow(album_id, ?user_id)` to the Precomputing base if one fits). Implement a key resolution helper that returns `[?user_id, triple]` for the least row. Write the rows: delete the album's precomputed rows, insert at most two, in one transaction with the album save. Stop assigning the columns.
   - _Commands:_ `php artisan test --filter=RecomputeAlbumStatsJobTest`, `--filter=DeepNestingPropagationTest`, `--filter=CoverSelectionNsfwTest`, `--filter=AlbumCoverSecurityTest`, `--filter=AlbumMutationScenariosTest`, `--filter=ExplicitCoverTest`, `make phpstan`.
   - _Exit:_ green, query-count baseline updated per NFR-076-03.
4. **I4 – v3 listings via `JoinAutoCover`**
   - _Goal:_ FR-076-04, FR-076-05, FR-076-06, NFR-076-01, NFR-076-05, S-076-06, S-076-07.
   - _Preconditions:_ I3.
   - _Steps:_ failing viewer-matrix cases in `AlbumSideCoversV3Test` and `AlbumListV3Test` (owner, admin, single-share, other user, guest), `SideCoverIdsTest` without privilege cases. Implement `JoinAutoCover::apply()`, switch `BuildAlbumDataResource`, `AlbumRootController`, `AlbumPinnedController`, `AlbumListController` (selects, `TRow` types, `rawCoverId()`), `BuildsAlbumCategoryResource` and `SideCoverIds::forAlbumRow()`.
   - _Commands:_ `php artisan test --filter=AlbumSideCoversV3Test`, `--filter=AlbumListV3Test`, `--filter=AlbumRootV3Test`, `--filter=AlbumCategoryV3Test`, `--filter=QuerySearchAlbumsTest`, `--filter=SideCoverIdsTest`, `make phpstan`.
   - _Exit:_ green, query counts unchanged.
5. **I5 – v2 model path, landing page, Meta**
   - _Goal:_ FR-076-07, FR-076-08, NFR-076-02, S-076-08, S-076-09.
   - _Preconditions:_ I1, I3.
   - _Steps:_ failing thumb assertions for owner, admin and guest on a v2 album fetch (extend the existing Feature_v3 test that covers `ThumbAlbumResource`, or `CoverDisplayPermissionTest`), and a failing `MetaTest` case. Implement `Album::autoCoverRows()` with its viewer constraint. Switch `$with`, `HasAlbumThumb`, `Flow`, `Notify`, `BulkAlbumController`, the three request `without` lists, `LandingPageResource`, `LandingFeaturedContentResource` and `Meta`. Remove the `min_privilege_cover` / `max_privilege_cover` relations.
   - _Commands:_ `php artisan test --filter=CoverDisplayPermissionTest`, `--filter=MetaTest`, `--filter=LandingPage`, `--filter=CoverageTest`, `make phpstan`.
   - _Exit:_ green.
6. **I6 – Asset exception and FulfillPreCompute**
   - _Goal:_ FR-076-11, FR-076-12, NFR-076-04, S-076-13..15.
   - _Preconditions:_ I3.
   - _Steps:_ failing cases in `PhotoAssetV3Test` (rank 1–3 of owner and `NULL` rows, non-member 403, query count) and `FulfillPreComputeTest` (album with photos and no row). Implement the single combined `EXISTS` query and the `whereNotExists` precompute check.
   - _Commands:_ `php artisan test --filter=PhotoAssetV3Test`, `--filter=FulfillPreComputeTest`, `make phpstan`.
   - _Exit:_ green.
7. **I7 – Ownership and deletion lifecycle**
   - _Goal:_ FR-076-09, FR-076-10 (delete half), S-076-10, S-076-16.
   - _Preconditions:_ I3.
   - _Steps:_ failing `tests/Feature_v3/Album/AutoCoverRowsV3Test`: transfer with a sub-album and a prior single share for the new owner, user deletion moving albums, album deletion. Add a re-key helper (`AlbumUserThumb::rekeyOwnerRows(album_ids, old_owner_ids, new_owner_id)`, straight-line: delete the new owner's rows, then update) called from `Transfer::do()` and `User::delete()`. Delete precomputed rows in `Actions\Album\Delete`.
   - _Commands:_ `php artisan test --filter=AutoCoverRowsV3Test`, `--filter=TransferTest` (if present), `--filter=UserManagementTest` (if present), `make phpstan`.
   - _Exit:_ green.
8. **I8 – Recompute on permission change**
   - _Goal:_ FR-076-13, NFR-076-07, S-076-18, S-076-19.
   - _Preconditions:_ I3.
   - _Steps:_ failing `tests/Unit/Listeners/RecomputeAlbumStatsOnAccessPermissionChangeTest` and `tests/Feature_v3/Sharing/SharingPropagateRecomputeV3Test` (`Queue::fake()`). Add the listener and register it with `Event::listen`. Make `Propagate::update()` / `overwrite()` return the touched descendant ids, and have `SharingController::propagate()` dispatch the event per id. S-076-18 end to end, without the fake: after the second share, the row is keyed `NULL`.
   - _Commands:_ `php artisan test --filter=RecomputeAlbumStatsOnAccessPermissionChangeTest`, `--filter=SharingPropagateRecomputeV3Test`, `--filter=SharingV3Test` (if present), `make phpstan`.
   - _Exit:_ green.
9. **I9 – Drop the columns**
   - _Goal:_ FR-076-03 step 3 and `down()`, G1, DO-076-02.
   - _Preconditions:_ I4–I8.
   - _Steps:_ `grep` shows no reader. Complete the migration (drop six FKs and columns; `down()` re-adds them, copies back, deletes precomputed rows, drops `is_precomputed`). Remove the attributes, casts, defaults and docblock lines from `Album`. Rerun every test class from I2–I8 one at a time.
   - _Commands:_ `grep -rnE "auto_cover_id_(max|least)|privilege_cover" app database/factories`, all the class filters above, `make phpstan`.
   - _Exit:_ grep empty, all green.
10. **I10 – Docs and quality gate**
    - _Goal:_ Documentation Deliverables.
    - _Steps:_ update the knowledge map (computed fields, `AlbumUserThumb`, `SideCoverIds`, `PurgeAlbumUserThumbs`, the `AccessPermissionChanged` listeners, `JoinAutoCover`, `AutoCoverRows`). Rewrite ADR-0003, ADR-0010 and ADR-075-01 in place to describe the single store. Update roadmap row 076. Run `vendor/bin/php-cs-fixer fix`, `make phpstan`, drift gate, commit hand-off.
    - _Commands:_ as listed.
    - _Exit:_ drift gate recorded, commit command handed over.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-076-01..04 | I3 / T-076-05, T-076-06 | `RecomputeAlbumStatsJobTest`, `AlbumCoverSecurityTest` |
| S-076-05 | I2, I9 / T-076-03, T-076-20 | Through consumers (no migration tests) |
| S-076-06 | I4 / T-076-08, T-076-09 | Viewer matrix in `AlbumListV3Test` |
| S-076-07 | I4 / T-076-08, T-076-09 | `AlbumSideCoversV3Test`, `SideCoverIdsTest` |
| S-076-08, 09 | I5 / T-076-10, T-076-11 | `CoverDisplayPermissionTest`, `MetaTest` |
| S-076-10, 16 | I7 / T-076-14, T-076-15 | `AutoCoverRowsV3Test` |
| S-076-11, 12 | I2 / T-076-03, T-076-04 | `PurgeSideCoversV3Test` |
| S-076-13, 14 | I6 / T-076-12, T-076-13 | `PhotoAssetV3Test` |
| S-076-15 | I6 / T-076-12, T-076-13 | `FulfillPreComputeTest` |
| S-076-17 | I3 / T-076-05, T-076-06 | `RecomputeAlbumStatsJobTest` |
| S-076-18, 19 | I8 / T-076-16..18 | `SharingPropagateRecomputeV3Test` |

## Analysis Gate
Run 2026-09-30 (agent, owner ildyria) against spec/plan/tasks as of this date.

1. **Specification completeness** — PASS.
   - Goals G1–G5, Row Model with invariants I1–I5, FR-076-01..13 and NFR-076-01..07 are populated.
   - The Q-076-01 and Q-076-02 answers are folded into FR-076-01/10/13, I4, I5, NG6 and NG7.
   - No UI impact (NG1), so no mock-up is needed.
2. **Open questions review** — PASS. No `Open` rows remain. ADR-076-01 covers both decisions and the store move.
3. **Plan alignment** — PASS. The plan links spec.md and tasks.md, and its success criteria quote NFR-076-01..05 and G1.
4. **Tasks coverage** — PASS. Mapping from FR to tasks:

   | Requirement | Tasks |
   |-------------|-------|
   | FR-076-01 | T-03 |
   | FR-076-02 | T-05, T-06 |
   | FR-076-03 | T-03, T-20 |
   | FR-076-04, 05, 06 | T-08, T-09 |
   | FR-076-07, 08 | T-01, T-02, T-10, T-11 |
   | FR-076-09 | T-14, T-15 |
   | FR-076-10 | T-03, T-04, T-14, T-15 |
   | FR-076-11, 12 | T-12, T-13 |
   | FR-076-13 | T-16..T-18 |

   Every increment stages failing tests before its implementation task.
5. **Working-agreement compliance** — PASS.
   - No new dependency, and no Eloquent hooks (NG7).
   - Branching lives in the pure `AutoCoverRows` helper and the three-shape `JoinAutoCover`. Producers gain one call each.
   - ADR-0003, ADR-0009, ADR-0010 and ADR-075-01 were reviewed.
   - No compatibility shim: the columns are dropped outright, and `down()` is a plain reversal.
6. **Tooling readiness** — PASS. Commands are listed per increment and task, and the drift-gate commands are listed above.

Findings, none blocking:
- Some test class names in I7 and I8 are marked "if present". Confirm them with `ls tests` at the start of each increment and record the ones used in tasks.md.

## Drift Gate Record
Run 2026-09-30 (agent, owner ildyria) after T-076-01..21.

- **Commands:** `vendor/bin/php-cs-fixer fix` (0 files changed on the final run), `make phpstan` (no errors), 48 test classes run one at a time, all green. `grep -rnE "auto_cover_id_(max|least)|privilege_cover" app database/factories` is empty.
- **Traceability:**

  | Requirement | Code | Tests |
  |-------------|------|-------|
  | FR-076-01 | migration `2026_10_01_000001_…`, `AlbumUserThumb` | `PurgeSideCoversV3Test` |
  | FR-076-02 | `RecomputeAlbumStatsJob::writeCoverRows()` | `RecomputeAlbumStatsJobTest`, Precomputing cover classes |
  | FR-076-03 | migration `up()`/`down()` | through consumers; scratch round trip (T-076-20 note) |
  | FR-076-04..06 | `JoinAutoCover`, listing producers, `AlbumListController`, `SideCoverIds` | `AlbumSideCoversV3Test`, `AlbumListV3Test`, `SideCoverIdsTest`, `AlbumRootV3Test`, `AlbumCategoryV3Test`, `QuerySearchAlbumsTest` |
  | FR-076-07, 08 | `Album::autoCoverRows()`, `AutoCoverRows`, `HasAlbumThumb`, landing resources, `Meta`, `with`/`without` lists | `AutoCoverRowsTest`, `CoverageTest`, `CoverDisplayPermissionTest`, `MetaTest`, landing and Flow classes |
  | FR-076-09 | `AlbumUserThumb::rekeyOwnerRows()`, `Transfer`, `User::delete()` | `AutoCoverRowsV3Test`, `AlbumTransferTest`, `DeleteUserTest` |
  | FR-076-10 | `PurgeAlbumUserThumbs`, `AlbumsToBeDeletedDTO` | `PurgeSideCoversV3Test`, `SharingTest`, `AutoCoverRowsV3Test` |
  | FR-076-11 | `GetPhotoAssetRequest::isMemberOrPrecomputedCover()` | `PhotoAssetV3Test` |
  | FR-076-12 | `FulfillPreCompute` | `FulfillPreComputeTest` |
  | FR-076-13 | `RecomputeAlbumStatsOnAccessPermissionChange`, `SharingController::propagate()`, `Propagate` | `RecomputeAlbumStatsOnAccessPermissionChangeTest`, `SharingPropagateRecomputeV3Test` |

- **Query counts:** v3 children listing 21 → 20, `Album` model load 24 → 23, `RecomputeAlbumStatsJob` 35 → 36 (−1 album load, +2 row writes). Asset check for a regular album: one existence query.
- **Adjusted existing tests:** `AlbumListV3Test` (four FR-057-09 cover tests seed rows), `SharingTest` (purge assertions count cache rows), `CoverageTest` (`HasAlbumThumb` reflection tests), `MetaTest` (`autoCoverRows` mocks), `PurgeSideCoversV3Test` (S-075-14 on precomputed rows).
- **Lessons:** the migration test DB only reruns pending migrations, so editing an applied migration needs the documented SQLite reset. SQLite migrations run outside a transaction (only the PostgreSQL and SQL Server grammars declare schema transactions), so Laravel's table-rebuild `foreign_keys = off` pragma is effective; a data round trip cannot be run inside `DatabaseTransactions` for the same reason.
- **Pending:** none for the backend. No UI change (NG1).

## Exit Criteria
- All tasks `[x]`, and `php-cs-fixer`, `make phpstan` and every listed test class green.
- The `grep` for old column and relation names is empty.
- Drift gate recorded below. Roadmap row 076 → Implemented. Knowledge map and ADR-0003/0010/075-01 updated.

## Follow-ups / Backlog
- None yet.
