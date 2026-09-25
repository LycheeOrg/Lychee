# Feature 072 Tasks – Move Grant & Edit-Grant Escalation Fix

_Status: Draft_  
_Last updated: 2026-09-25_

> Stage tests before implementation. Mark `[x]` immediately after each passes. Run test classes **sequentially** (shared SQLite); always scope with `--filter=`.

## Checklist

- [ ] T-072-00 – Pre-flight: confirm the admin `before()` hook covers `AlbumPolicy`/`PhotoPolicy` (A2); list every `grants_delete` touchpoint (R1).  
  _Verification:_ review only.

### I1 – Persistence
- [ ] T-072-01 – Migration adding `grants_move` + backfill `= grants_edit`; `APC::GRANTS_MOVE`; model cast/fillable; factory `grants_move()`; `withGrantFullPermissionsToUser()`/`ofPublic()`/`ofPublicHidden()`; `computed_access_permissions` + `EffectiveAccessPermission`; `BaseApiWithDataTest` full-grant perms gain `grants_move()` (F-072-01, F-072-02, F-072-06).  
  _Verification:_ `php artisan test --filter=AlbumQueryPolicyTest`, `--filter=MultiGroupPermissionMergeTest`, `make phpstan`

### I2 – Policies
- [ ] T-072-02 – Failing tests: `AlbumPolicy::CAN_MOVE` (owner ± `may_upload`, user grant, group grant, public, edit-only) and `PhotoPolicy::CAN_MOVE` (owner, reduction over albums) (F-072-03, F-072-04).  
  _Verification:_ `php artisan test --filter=MovePolicyTest`
- [ ] T-072-03 – Implement both abilities.  
  _Verification:_ `php artisan test --filter=MovePolicyTest`, `make phpstan`

### I3 – `Album::move`
- [ ] T-072-04 – Failing tests: S-072-08 (advisory), S-072-09, S-072-10, admin (S-072-16) (F-072-13, F-072-14, F-072-17, F-072-18).  
  _Verification:_ `php artisan test --filter=AlbumMoveGrantTest`
- [ ] T-072-05 – `MoveAlbumsRequest` dedicated authorization + `sourceNeedsTransfer()` helper.  
  _Verification:_ `--filter=AlbumMoveGrantTest`, `--filter=AlbumMoveTest`, `make phpstan`

### I4 – `Album::merge`
- [ ] T-072-06 – Failing tests: S-072-11 (advisory), S-072-12, S-072-13, S-072-14, admin (F-072-15, F-072-16).  
  _Verification:_ `php artisan test --filter=AlbumMergeGrantTest`
- [ ] T-072-07 – `MergeAlbumsRequest` dedicated authorization (move + delete + guard).  
  _Verification:_ `--filter=AlbumMergeGrantTest`, `--filter=AlbumMergeTest`, `--filter=MergeAlbumRequestTest`, `make phpstan`

### I5 – `Photo::copy` / `Photo::move`
- [ ] T-072-08 – Failing tests: S-072-01/02 (advisory, `secure_image_link_enabled`), S-072-03..07, S-072-15, S-072-18 (action side), S-072-22..24, admin (F-072-10, F-072-11, F-072-12).  
  _Verification:_ `php artisan test --filter=PhotoMoveGrantTest`
- [ ] T-072-09 – `CopyPhotosRequest`/`MovePhotosRequest` dedicated authorization + `photoNeedsFullAccess()` helper.  
  _Verification:_ `--filter=PhotoMoveGrantTest`, `--filter=PhotoCopyTest`, `--filter=PhotoMoveTest`, `--filter=MoveOrDuplicateTest`, `make phpstan`

### I6 – Sharing API
- [ ] T-072-10 – Failing tests: S-072-20 (create without → false, edit without → unchanged, propagate copies, 422 on non-boolean); response carries `grants_move` (F-072-05).  
  _Verification:_ `php artisan test --filter=GrantsMoveSharingTest`
- [ ] T-072-11 – Add `grants_move` to every sharing touchpoint from T-072-00 (requests, `Share`, `Propagate`, `SharingController`, `AccessPermissionResource`, v3 `AlbumAccessPermissionResource`, `AlbumAccessPermissionListController`, `DiagnosticsController`).  
  _Verification:_ `--filter=GrantsMoveSharingTest`, `--filter=SharingTest`, `--filter=AlbumSharingTest`, `make phpstan`

### I7 – Rights resources
- [ ] T-072-12 – Failing tests: S-072-21 on v2 `AlbumRightsResource` and v3 `/rights` (F-072-20).  
  _Verification:_ `php artisan test --filter=MoveGrantRightsTest`
- [ ] T-072-13 – v2 `can_move`/`can_merge`; v3 `grants_move[]` replacing `can_move_children` (`GrantsAlbumRights`, `QueryRightsForAlbum`, `AlbumRootController`, matching/search rights).  
  _Verification:_ `--filter=MoveGrantRightsTest` and existing v3 rights test classes, `make phpstan`

### I8 – Editability filter + v2 target list
- [ ] T-072-14 – Failing tests: S-072-19 parity; S-072-17 on `getTargetListAlbums` (F-072-30, F-072-31, F-072-32).  
  _Verification:_ `php artisan test --filter=EditableConditionParityTest`, `--filter=TargetListAlbumsTest`
- [ ] T-072-15 – `AlbumQueryPolicy::appendEditableCondition()`; apply in `ListAlbums`.  
  _Verification:_ as above, `make phpstan`

### I9 – v3 `can_edits`
- [ ] T-072-16 – Failing tests: `can_edits` present and correct (S-072-17), query count unchanged (NFR-072-04) (F-072-33).  
  _Verification:_ `php artisan test --filter=AlbumListV3Test`
- [ ] T-072-17 – Add `can_edits` to `AlbumListController` query + `AlbumListResource`.  
  _Verification:_ `--filter=AlbumListV3Test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`

### I10 – v8 sharing UI
- [ ] T-072-18 – Regenerate TS types; Move checkbox in `ShareLine.vue`, `BulkSharingModal.vue`, `AlbumCreateShareDialog.vue`, `Sharing.vue` header; translation keys in all `lang/<locale>/*.php`, then `php artisan lang:json` (F-072-22).  
  _Verification:_ `npm run format`, `npm run check`, `php artisan test --filter=LangTest`

### I11 – v8 rights, menus, picker
- [ ] T-072-19 – `adaptAlbumChildTile`/`adaptCategoryTile` (`can_move`, `can_merge`); context menus (album Move → `can_move`, Merge → `can_merge`, photo Move/Copy → `can_move`); `AlbumListState` keeps `can_edits`; `SearchTargetAlbum.vue` v3 path filters on it (F-072-21, F-072-30, F-072-33, F-072-34, N-072-08).  
  _Verification:_ `npm run format`, `npm run check`
- [ ] T-072-20 – **Manual browser check** (flag on and off): share checkboxes round-trip; Move/Merge/Copy menu visibility for edit-only vs move vs move+delete; picker hides read-only albums; cross-owner copy shows the 403 toast (S-072-18).  
  _Verification:_ manual; do not tick without a real browser run.

### I12 – Docs & gates
- [ ] T-072-21 – knowledge-map, `docs/specs/3-reference/api-design.md`, roadmap, `_current-session.md`.
- [ ] T-072-22 – Quality gate: `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `make phpstan`, every `--filter=` above sequentially; drift gate report in plan.md.

## Notes / TODOs
- `vuln.md` at the repo root is untracked and embargoed; do not stage it.
- No migration tests (the backfill is exercised only through the consuming code).
