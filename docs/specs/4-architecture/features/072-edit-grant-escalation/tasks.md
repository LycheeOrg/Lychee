# Feature 072 Tasks – Move Grant & Edit-Grant Escalation Fix

_Status: Draft_  
_Last updated: 2026-09-25_

> Stage tests before implementation. Mark `[x]` immediately after each passes. Run test classes **sequentially** (shared SQLite); always scope with `--filter=`.

## Checklist

- [x] T-072-00 – Pre-flight: confirm the admin `before()` hook covers `AlbumPolicy`/`PhotoPolicy` (A2); list every `grants_delete` touchpoint (R1).  
  _Verification:_ review only.

### I1 – Persistence
- [x] T-072-01 – Migration adding `grants_move` + backfill `= grants_edit`; `APC::GRANTS_MOVE`; model cast/fillable; factory `grants_move()`; `withGrantFullPermissionsToUser()`/`ofPublic()`/`ofPublicHidden()`; `computed_access_permissions` + `EffectiveAccessPermission`; `BaseApiWithDataTest` full-grant perms gain `grants_move()` (F-072-01, F-072-02, F-072-06).  
  _Verification:_ `php artisan test --filter=AlbumQueryPolicyTest`, `--filter=MultiGroupPermissionMergeTest`, `make phpstan`

### I2 – Policies
- [x] T-072-02 – Failing tests: `AlbumPolicy::CAN_MOVE` (owner ± `may_upload`, user grant, group grant, public, edit-only) and `PhotoPolicy::CAN_MOVE` (owner, reduction over albums) (F-072-03, F-072-04).  
  _Verification:_ `php artisan test --filter=MovePolicyTest`
- [x] T-072-03 – Implement both abilities.  
  _Verification:_ `php artisan test --filter=MovePolicyTest`, `make phpstan`

### I3 – `Album::move`
- [x] T-072-04 – Failing tests: S-072-08 (advisory), S-072-09, S-072-10, admin (S-072-16) (F-072-13, F-072-14, F-072-17, F-072-18).  
  _Verification:_ `php artisan test --filter=AlbumMoveGrantTest`
- [x] T-072-05 – `MoveAlbumsRequest` dedicated authorization + `sourceNeedsTransfer()` helper.  
  _Verification:_ `--filter=AlbumMoveGrantTest`, `--filter=AlbumMoveTest`, `make phpstan`

### I4 – `Album::merge`
- [x] T-072-06 – Failing tests: S-072-11 (advisory), S-072-12, S-072-13, S-072-14, admin (F-072-15, F-072-16).  
  _Verification:_ `php artisan test --filter=AlbumMergeGrantTest`
- [x] T-072-07 – `MergeAlbumsRequest` dedicated authorization (move + delete + guard).  
  _Verification:_ `--filter=AlbumMergeGrantTest`, `--filter=AlbumMergeTest`, `--filter=MergeAlbumRequestTest`, `make phpstan`

### I5 – `Photo::copy` / `Photo::move`
- [x] T-072-08 – Failing tests: S-072-01/02 (advisory, `secure_image_link_enabled`), S-072-03..07, S-072-15, S-072-18 (action side), S-072-22..24, admin (F-072-10, F-072-11, F-072-12).  
  _Verification:_ `php artisan test --filter=PhotoMoveGrantTest`
- [x] T-072-09 – `CopyPhotosRequest`/`MovePhotosRequest` dedicated authorization + `photoNeedsFullAccess()` helper.  
  _Verification:_ `--filter=PhotoMoveGrantTest`, `--filter=PhotoCopyTest`, `--filter=PhotoMoveTest`, `--filter=MoveOrDuplicateTest`, `make phpstan`

### I6 – Sharing API
- [x] T-072-10 – Failing tests: S-072-20 (create without → false, edit without → unchanged, propagate copies, 422 on non-boolean); response carries `grants_move` (F-072-05).  
  _Verification:_ `php artisan test --filter=GrantsMoveSharingTest`
- [x] T-072-11 – Add `grants_move` to every sharing touchpoint from T-072-00 (requests, `Share`, `Propagate`, `SharingController`, `AccessPermissionResource`, v3 `AlbumAccessPermissionResource`, `AlbumAccessPermissionListController`, `DiagnosticsController`).  
  _Verification:_ `--filter=GrantsMoveSharingTest`, `--filter=SharingTest`, `--filter=AlbumSharingTest`, `make phpstan`

### I7 – Rights resources
- [x] T-072-12 – Failing tests: S-072-21 on v2 `AlbumRightsResource` and v3 `/rights` (F-072-20).  
  _Verification:_ `php artisan test --filter=MoveGrantRightsTest`
- [x] T-072-13 – v2 `can_move`/`can_merge`; v3 `grants_move[]` replacing `can_move_children` (`GrantsAlbumRights`, `QueryRightsForAlbum`, `AlbumRootController`, matching/search rights).  
  _Verification:_ `--filter=MoveGrantRightsTest` and existing v3 rights test classes, `make phpstan`

### I8 – Editability filter + v2 target list
- [x] T-072-14 – Failing tests: S-072-19 parity; S-072-17 on `getTargetListAlbums` (F-072-30, F-072-31, F-072-32).  
  _Verification:_ `php artisan test --filter=EditableConditionParityTest`, `--filter=TargetListAlbumsTest`
- [x] T-072-15 – `AlbumQueryPolicy::appendEditableCondition()`; apply in `ListAlbums`.  
  _Verification:_ as above, `make phpstan`

### I9 – v3 `can_edits`
- [x] T-072-16 – Failing tests: `can_edits` present and correct (S-072-17), query count unchanged (NFR-072-04) (F-072-33).  
  _Verification:_ `php artisan test --filter=AlbumListV3Test`
- [x] T-072-17 – Add `can_edits` to `AlbumListController` query + `AlbumListResource`.  
  _Verification:_ `--filter=AlbumListV3Test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`

### I9b – Protection policy & delete (Q-072-10, Q-072-11)
- [x] T-072-23 – Tests: `ProtectionPolicyOwnershipTest` (edit-only and fully granted collaborators 403, owner/admin OK, smart album admin-only); `DeleteGrantTest` (delete on parent deletes sub-album, not the album itself; photo delete on containing album) (F-072-40..42).  
  _Verification:_ `php artisan test --filter=ProtectionPolicyOwnershipTest`, `--filter=DeleteGrantTest`
- [x] T-072-24 – `AlbumPolicy::canChangeProtectionPolicy()`; `canDeleteById()` on parents + `canDeleteContentById()` for photos; v8 drawer Visibility gated on ownership.  
  _Verification:_ `--filter=AlbumDeleteTest`, `--filter=AlbumUpdateTest`, `--filter=SetAlbumProtectionPolicyRequestTest`, `--filter=PhotoDeleteTest`, `make phpstan`, `npm run check`

### I9c – Aggregate authorization (Q-072-12)
- [x] T-072-25 – Tests: `AggregateAuthorizationTest` — parity of every `…ById` check with its per-model policy over the whole fixture; grant-query count equal for 1 vs N items (album move, photo copy) (NFR-072-07).  
  _Verification:_ `php artisan test --filter=AggregateAuthorizationTest`
- [x] T-072-26 – `AlbumPolicy` shared helpers (`hasGrantOnParentsById`, `hasGrantOnAllById`, `countGrantedById`), `canMoveAlbumsById`, `canMoveContentById`; `PhotoPolicy::canMoveById`, `canAccessFullAndDownloadById`; requests switched; photo eager loads (`albums`, `size_variants`) dropped; legacy traits deleted.  
  _Verification:_ full sweep of the feature and neighbouring suites, `make phpstan`, `php-cs-fixer`

### I10 – v8 sharing UI
- [x] T-072-18 – Regenerate TS types; Move checkbox in `ShareLine.vue`, `BulkSharingModal.vue`, `AlbumCreateShareDialog.vue`, `Sharing.vue` header; translation keys in all `lang/<locale>/*.php`, then `php artisan lang:json` (F-072-22).  
  _Verification:_ `npm run format`, `npm run check`, `php artisan test --filter=LangTest`

### I11 – v8 rights, menus, picker
- [x] T-072-19 – `adaptAlbumChildTile`/`adaptCategoryTile` (`can_move`, `can_merge`); context menus (album Move → `can_move`, Merge → `can_merge`, photo Move/Copy → `can_move`); `AlbumListState` keeps `can_edits`; `SearchTargetAlbum.vue` v3 path filters on it (F-072-21, F-072-30, F-072-33, F-072-34, N-072-08).  
  _Verification:_ `npm run format`, `npm run check`
- [ ] T-072-20 – **Manual browser check** (flag on and off): share checkboxes round-trip; Move/Merge/Copy menu visibility for edit-only vs move vs move+delete; picker hides read-only albums; cross-owner copy shows the 403 toast (S-072-18).  
  _Verification:_ manual; do not tick without a real browser run.

### I12 – Docs & gates
- [x] T-072-21 – knowledge-map, `docs/specs/3-reference/api-design.md`, roadmap, `_current-session.md`.
- [x] T-072-22 – Quality gate: `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `make phpstan`, every `--filter=` above sequentially; drift gate report in plan.md.

## Notes / TODOs
- `vuln.md` at the repo root is untracked and embargoed; do not stage it.
- No migration tests (the backfill is exercised only through the consuming code).

## Deviations from the plan

- **D-072-A — Q-072-09 (content semantics), decided mid-implementation.** The move grant on an album covers its content, not the album itself. Added `AlbumPolicy::CAN_MOVE_ALBUM` (grant on the parent, like delete); `Album::move` and the album-move picker use it. v2 rights split into `can_move` (album itself) / `can_move_content` / `can_merge`; v3 keeps `can_move_children` (now from the parent's move grant) and adds per-child `grants_move[]`.
- **D-072-B — FR-072-18 was wrong about shared traits.** `AuthorizeCanEditPhotosAlbumTrait`/`AuthorizeCanEditAlbumAlbumsTrait` were used only by the four requests; new dedicated traits replace them and the old two are now unused (deletion pending owner approval).
- **D-072-C — target-list authorization.** `TargetListAlbumRequest` required `CAN_EDIT_ID` on the sources, which would hide the picker from move-only collaborators; now `CAN_MOVE_ALBUM` per source.
- **D-072-D — `can_move` on smart albums.** Restricting `can_move_content` to regular albums would have hidden photo Move/Copy in Unsorted/Recent; smart albums follow `may_upload`, as the backend does. `can_merge` stays regular-album only.
- **D-072-E — FR-072-34 kept today's dialog behaviour** (close on confirm, global 403 toast) instead of keeping dialogs open.
- **D-072-F — assertion changes outside fixtures (NFR-072-05 findings):** `MergeAlbumRequestTest` mocked Gate count 4 → 6 (merge checks edit + move + delete, `authorize()` runs twice in that test); `AlbumMoveTest::testMoveAlbumUnauthorizedForbidden` now removes `grants_move` from `perm1` first, because a move grant on the parent legitimately allows moving the child; `AlbumListV3Test` key-set gains `can_edits`; `AlbumChildrenRightsV3Test` expects `can_move_children=false` when only delete is granted on the parent.
- **D-072-G — findings during documentation, then fixed:** Q-072-10 (edit-only collaborators could widen public access through `Album::updateProtectionPolicy`) → owner-only; Q-072-11 (`DELETE /Album` checked the grant on the album itself) → parent. `AlbumDeleteTest` adjusted: deleting a shared root album with delete on that album is now 403 (its sub-album is deleted instead), and the forbidden case removes `grants_delete` from `perm1` first; `SetAlbumProtectionPolicyRequestTest` mock expects the new ability.
- **D-072-H — documentation:** `docs/specs/1-concepts/permissions.md` gained "What Each Grant Allows" with examples (owner request), and two inaccurate lines were corrected (grants are not inherited by child albums; `grants_edit` no longer covers moving).
- **D-072-I — Q-072-12 aggregate authorization.** Rule unchanged: deleting a photo needs delete on every album containing it (as owner, or through `grants_delete`); one album without delete refuses the request. Only the counting changed: the old check required "owns all" or "granted on all", so a photo in one owned and one delete-granted album was refused although both albums allow delete; it is now accepted. Pinned by `DeleteGrantTest` (both mixed cases). The grant-query count test covers the album grants (the old per-album move lookup filtered on `grants_move`); the old photo path lazy-loaded permission rows without a grant filter, so the photo cost test pins the new behaviour rather than proving the old one grew. The full-access + download aggregate follows `PhotoPolicy` (user, group, public rows and album ownership), not `ResolvesPhotoGrants`, whose `computed_access_permissions` drops group rows when a user row exists and ignores album ownership.
