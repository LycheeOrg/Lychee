# Feature 118 Tasks – Share Rights Decoupling

_Status: Implemented (manual browser checks pending)_  
_Last updated: 2026-11-25_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Schema, settings, invariant
- [x] T-118-01 – Failing tests for the migration and the invariant (FR-118-01, FR-118-08, FR-118-19, FR-118-22, S-118-01, S-118-20).  
  _Intent:_ `tests/Unit/Database/UserRightsMigrationTest.php`: columns exist; `backfill()` sets both flags to `may_upload`; settings migration's `copyUploadSetting()` copies `grant_new_user_upload_rights` (`0` and `1`). `tests/Unit/Actions/User/UserRightsInvariantTest.php`: `Create` and `Save` with `may_upload` true store all three true; with `may_upload` false they store the given share/edit values.  
  _Verification commands:_  
  - `php artisan test --filter=UserRightsMigrationTest` (red)  
  - `php artisan test --filter=UserRightsInvariantTest` (red)

- [x] T-118-02 – Columns, backfill, model, factory (FR-118-01, FR-118-08, FR-118-22, NFR-118-05).  
  _Intent:_ `database/migrations/2026_11_25_000001_add_share_and_edit_rights_to_users.php` (two booleans after `may_upload`, default `false`, public `backfill()`, `down()` drops). `User` `@property bool $may_share`, `$may_edit`, casts. `UserFactory::may_upload()` sets all three; new `may_share()`, `may_edit()` states.  
  _Verification commands:_  
  - `php artisan test --filter=UserRightsMigrationTest`  
  - `make phpstan`

- [x] T-118-03 – Settings migration, `Create`/`Save` invariant, LDAP (FR-118-17 actions, FR-118-19, FR-118-20, FR-118-22).  
  _Intent:_ `database/migrations/2026_11_25_000002_add_grant_new_user_share_edit_rights.php` (category `Users Management`, value copied, public `copyUploadSetting()`, `down()` deletes). `Create::do(..., bool $may_share = false, bool $may_edit = false)`, `Save::do(..., bool $may_share = false, bool $may_edit = false)`, stored as `|| $may_upload`. `ProvisionLdapUser` sets both `true`.  
  _Verification commands:_  
  - `php artisan test --filter=UserRightsMigrationTest`  
  - `php artisan test --filter=UserRightsInvariantTest`  
  - `php artisan test --filter=LdapAuthTest`  
  - `make phpstan`

### I2 – Share right
- [x] T-118-04 – Failing tests for the share right (FR-118-02 … 05, S-118-02 … 05, S-118-16).  
  _Intent:_ `tests/Feature_v2/Album/ShareRightTest.php` on `BaseApiWithDataTest`: a user with `may_share` only owning an album shares, lists, edits, deletes, propagates; changes visibility; transfers; lists users and groups; root rights `can_share_with_users` true. `userMayUpload1` with `may_share` set false directly in the DB → 403 on each and right false. Sharing an album not owned → 403.  
  _Verification commands:_  
  - `php artisan test --filter=ShareRightTest` (red)

- [x] T-118-05 – Share policies and root right (FR-118-02 … 05).  
  _Intent:_ `AlbumPolicy::canShareWithUsers()`, `canShareById()`, `canChangeProtectionPolicy()`, `canTransfer()`; `UserPolicy::canList()`; `UserGroupPolicy::canList()`; `RootAlbumRightsResource::$can_share_with_users`. Update `userNoUpload` expectations in existing tests where it changes visibility or transfers its own album (NFR-118-01).  
  _Verification commands:_  
  - `php artisan test --filter=ShareRightTest`  
  - `php artisan test --filter=SharingTest`  
  - `php artisan test --filter=UsersTest`  
  - `php artisan test --filter=UserGroupTest`  
  - `php artisan test --filter=AlbumTransferTest` (if present)  
  - `make phpstan`

### I3 – Held grants
- [x] T-118-06 – Failing tests for held grants (FR-118-07, S-118-17, S-118-18).  
  _Intent:_ `tests/Feature_v2/Album/HeldGrantTest.php`: sharer with `may_share` only → 422 on `grants_upload`, `grants_edit`, `grants_move`, `grants_delete` (add, bulk add, edit); all `false` → 201/200; with `may_edit` too → edit/move/delete accepted, upload refused; public `grants_upload` in `Album::updateProtectionPolicy` → 422; admin accepted; propagation copies grants. `tests/Unit/Rules/HeldGrantRuleTest.php`.  
  _Verification commands:_  
  - `php artisan test --filter=HeldGrantTest` (red)  
  - `php artisan test --filter=HeldGrantRuleTest` (red)

- [x] T-118-07 – `HeldGrantRule` and request rules (FR-118-07).  
  _Intent:_ `app/Rules/HeldGrantRule.php`; rules in `AddSharingRequest`, `EditSharingRequest`, `SetAlbumProtectionPolicyRequest`, from `may_upload`/`may_edit` or `may_administrate` of the authenticated user.  
  _Verification commands:_  
  - `php artisan test --filter=HeldGrantRuleTest`  
  - `php artisan test --filter=HeldGrantTest`  
  - `make phpstan`

- [x] T-118-08 – Sharing regression (NFR-118-01, S-118-15).  
  _Intent:_ rerun the existing sharing and protection-policy suites; fix fixtures only.  
  _Verification commands:_  
  - `php artisan test --filter=SharingTest`  
  - `php artisan test --filter=AlbumProtectionPolicyTest` (or the class covering `updateProtectionPolicy`)  
  - `php artisan test --filter=BulkSharing` (if present)

### I4 – Edit right
- [x] T-118-09 – Failing tests for the edit right (FR-118-09 … 12, S-118-06, S-118-07, S-118-19).  
  _Intent:_ `tests/Feature_v2/Album/EditRightTest.php`: user with `may_edit` only, owning an album and photo: rename, set cover, create sub-album, create root and tag album, move, merge, delete album; edit, rename, tag, move, delete own photo (single and batch); edit/merge/delete tags; create/list renamer rule → 2xx. `userMayUpload1` with `may_edit` false in the DB → 403 on each; album rights `can_edit` false; editable listings agree (FR-118-11).  
  _Verification commands:_  
  - `php artisan test --filter=EditRightTest` (red)

- [x] T-118-10 – `AlbumPolicy`, `AlbumQueryPolicy`, `AlbumListController` (FR-118-09, FR-118-11).  
  _Intent:_ swap `may_upload` for `may_edit` in the gates listed in FR-118-09 and FR-118-11; no new branch (NFR-118-02).  
  _Verification commands:_  
  - `php artisan test --filter=EditRightTest`  
  - `php artisan test --filter=EditableConditionParityTest`  
  - `php artisan test --filter=AggregateAuthorizationTest`  
  - `php artisan test --filter=MovePolicyTest`  
  - `php artisan test --filter=DeleteGrantTest`  
  - `make phpstan`

- [x] T-118-11 – `PhotoPolicy` (FR-118-10).  
  _Intent:_ owner branches of `canEdit()`, `canMove()`, `canMoveById()`, `canEditById()`, `canDeleteById()` read `may_edit`. Update `userNoUpload` expectations where it edits its own photo (NFR-118-01).  
  _Verification commands:_  
  - `php artisan test --filter=EditRightTest`  
  - `php artisan test --filter=PhotoBulkLicenseTest`  
  - `php artisan test --filter=GetPhotoAlbumsTest`  
  - `php artisan test --filter=PhotoEditTest` (or the classes covering `Photo::edit`, `Photo::rename`, `Photo::tags`)  
  - `make phpstan`

- [x] T-118-12 – `TagPolicy`, renamer requests, album regression (FR-118-12, S-118-15).  
  _Intent:_ `TagPolicy::canEdit()` = `may_edit`; renamer rule requests `may_administrate || may_edit`. Rerun album, tag and renamer suites using `userNoUpload`.  
  _Verification commands:_  
  - `php artisan test --filter=EditRightTest`  
  - `php artisan test --filter='EditTagsTest|MergeTagsTest|DeleteTagsTest|ListTagsTest'`  
  - `php artisan test --filter='RenamerRules|RenamerTest|RenamerPreviewTest|RenameTest'`  
  - `php artisan test --filter='AlbumRenameTest|AlbumSetCoverTest|TagAlbumSetCoverTest|AlbumSetHeaderTest|AlbumSetPinnedTest|AlbumMergeTest'`  
  - `make phpstan`

### I5 – Upload and import
- [x] T-118-13 – Failing tests for upload and import (FR-118-13 … 15, S-118-08 … 10).  
  _Intent:_ `tests/Feature_v2/Photo/UploadRightTest.php`: owner without `may_upload` → upload and URL import into own album 403, album rights `can_upload` false; same user with `grants_upload` on another album → allowed; owner with `may_upload` → allowed. Import tests: user with `server_path`, no `may_upload`: options, browse, import into own album and root → 2xx; into a foreign album without `grants_upload` → 403. `SyncTest`: no-path sync includes that user.  
  _Verification commands:_  
  - `php artisan test --filter=UploadRightTest` (red)  
  - `php artisan test --filter=ImportFromServerTest` (red cases)  
  - `php artisan test --filter=SyncTest` (red case)

- [x] T-118-14 – `canUpload()`, `canImportFromServer()`, `CAN_IMPORT_INTO`, `SyncUserEligibility` (FR-118-13 … 15).  
  _Intent:_ as spec; `ImportFromServerRequest` checks `CAN_IMPORT_INTO`; `SyncUserEligibility::NOT_ALLOWED` docblock.  
  _Verification commands:_  
  - `php artisan test --filter=UploadRightTest`  
  - `php artisan test --filter='ImportFromServerTest|ImportFromServerBrowseTest|ImportFromServerOptionsTest|SyncTest'`  
  - `php artisan test --filter='PhotoAddTest|UploadWithMetadataTest'`  
  - `make phpstan`

### I6 – Smart albums
- [x] T-118-15 – Failing tests for smart album visibility (FR-118-16, NFR-118-03, S-118-11).  
  _Intent:_ `tests/Feature_v2/SmartAlbums/SmartAlbumOwnerVisibilityTest.php`: user without `may_upload` owning a photo sees Unsorted, Recent, On this day … in the root listing and can open them; owning none → hidden, 403; one ownership query per request.  
  _Verification commands:_  
  - `php artisan test --filter=SmartAlbumOwnerVisibilityTest` (red)

- [x] T-118-16 – `OwnedPhotoCheck` and `canSee()` (FR-118-16, NFR-118-03).  
  _Intent:_ `app/Services/OwnedPhotoCheck.php` (scoped binding, memo per user id); `AlbumPolicy::canSee()`. Update smart-album expectations for `userNoUpload` (NFR-118-01).  
  _Verification commands:_  
  - `php artisan test --filter=SmartAlbumOwnerVisibilityTest`  
  - `php artisan test --filter=SmartAlbums`  
  - `php artisan test --filter=SmartAlbumTypeTest`  
  - `make phpstan`

### I7 – User management API, accounts, CLI
- [x] T-118-17 – Failing tests for user management and accounts (FR-118-17, FR-118-19, FR-118-21, S-118-12, S-118-13).  
  _Intent:_ user management create/edit with `may_share`, `may_edit` (missing → 422; stored per invariant; listed); registration and OAuth read the two settings; `CreateUserCommandTest` `--may-share`, `--may-edit`, `--may-upload`, `--may-administrate`.  
  _Verification commands:_  
  - `php artisan test --filter=UserManagement` (red cases)  
  - `php artisan test --filter=CreateUserCommandTest` (red cases)

- [x] T-118-18 – User management API (FR-118-17).  
  _Intent:_ `RequestAttribute::MAY_SHARE_ATTRIBUTE`, `MAY_EDIT_ATTRIBUTE`; `AddUserRequest`, `SetUserSettingsRequest` rules and getters; `UserManagementController` (select and pass); `UserManagementResource`.  
  _Verification commands:_  
  - `php artisan test --filter=UserManagement`  
  - `make phpstan`

- [x] T-118-19 – Registration, OAuth, CLI (FR-118-19, FR-118-21).  
  _Intent:_ `ProfileController::register()`, `Oauth`, `CreateUser` options.  
  _Verification commands:_  
  - `php artisan test --filter=CreateUserCommandTest`  
  - `php artisan test --filter='Oauth|RegistrationTest|AdminSetupTest'`  
  - `make phpstan`

- [x] T-118-20 – Setting descriptions in 23 languages (FR-118-19, NFR-118-04).  
  _Intent:_ `lang/*/all_settings.php` description and details for both keys.  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`  
  - `php artisan test --filter=ConfigTranslationKeysTest`

### I8 – Frontend
- [x] T-118-21 – Types and v8 (FR-118-05, 06, 07, 18, S-118-14, S-118-21).  
  _Intent:_ `php artisan typescript:transform`; `user-management-service.ts`; v8 `CreateEditUser.vue` (upload locks share/edit), `Users.vue` columns; v8 left menu "Sharing" on `root_album.can_share_with_users`; `AlbumPanel.vue`/`AlbumEdit.vue` and `AlbumHeader.vue` open when any section is available; `ShareLine.vue`, `BulkSharingModal.vue`, create-share form and `AlbumVisibility.vue` disable lacked grants.  
  _Verification commands:_  
  - `vp check --fix`  
  - `vp run typecheck`

- [x] T-118-22 – v7 (FR-118-05, 06, 07, 18, S-118-14, S-118-21).  
  _Intent:_ v7 equivalents: `CreateEditUser.vue`, `ListUser.vue`, left menu, `AlbumPanel.vue`/drawer, `AlbumHeader.vue`, `CreateSharing.vue`, `ShareLine.vue`, `BulkSharingModal.vue`, visibility form.  
  _Verification commands:_  
  - `vp check --fix`  
  - `vp run typecheck`

- [x] T-118-23 – User form labels in 23 languages (FR-118-18, NFR-118-04).  
  _Intent:_ `lang/*/users.php` share and edit labels and hints.  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`

### I9 – Docs and drift gate
- [x] T-118-24 – Knowledge map, roadmap, drift gate (all FR).  
  _Intent:_ knowledge map entry for the three rights and `CAN_IMPORT_INTO`; roadmap row; drift gate report in plan.md; full `vendor/bin/php-cs-fixer fix`, `make phpstan`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

### I10 – Grants already given (Q-118-09)
- [x] T-118-25 – Failing tests for resent grants (FR-118-07, S-118-22).  
  _Intent:_ `HeldGrantRuleTest`: `true` accepted when already granted, refused otherwise. `HeldGrantTest`: sharer who lost `may_upload`/`may_edit` edits another grant on a share carrying the lacked grant → 200, grant kept; unticks it → 200; switches on another lacked grant → 422; non-owner → same 422 whatever the stored grant; album with public `grants_upload`, owner without `may_upload`, changes NSFW → 201.  
  _Verification commands:_  
  - `php artisan test --filter=HeldGrantRuleTest` (red)  
  - `php artisan test --filter=HeldGrantTest` (red)

- [x] T-118-26 – Rule and requests read the stored grant (FR-118-07, DO-118-07).  
  _Intent:_ `HeldGrantRule(bool $held, bool $already_granted = false)`; `EditSharingRequest` loads the permission once in `rules()` and reuses it; `SetAlbumProtectionPolicyRequest` checks the owned album's public upload grant only when the sharer lacks `may_upload`.  
  _Verification commands:_  
  - `php artisan test --filter=HeldGrantRuleTest`  
  - `php artisan test --filter=HeldGrantTest`  
  - `php artisan test --filter=SharingTest`  
  - `make phpstan`

- [x] T-118-27 – Share line reloads on a failed save (S-118-22).  
  _Intent:_ v7 and v8 `ShareLine.vue` keep the last saved permission and restore it when `SharingService.edit` fails.  
  _Verification commands:_  
  - `vp check --fix <changed paths>`  
  - `vp run typecheck`

## Notes / TODOs
- Tooling: `vp check --fix` over the whole tree rewrites `<\/script>` in both `EmbedCodeDialog.vue` files and breaks them (pre-existing autofix hazard). Run it with the changed paths only.
- Pre-existing, unrelated: `AlbumRootV3Test::testSortingColumnChangeRecomputesOwnScopeBuckets…` and `testRecomputeJobInvalidatesRootCacheRewarmed…` fail with or without Feature 118 changes (bucket `2023` instead of `2024`, checked 2026-11-25 by stashing the feature's code). Same for `TimelineAlbumTest::testBucketsGroupsByYear…` and `PhotoBucketsV3Test::testCacheInvalidatedOnAlbumPhotoSortingChange` (checked the same way). Likely local timezone.
- Manual checks S-118-14 and S-118-21 need a browser: owner with `may_share` only, owner with `may_edit` only, admin user form.
