# Feature 033 Tasks – Upload Trust Level

_Status: Draft_  
_Last updated: 2026-04-11_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`N-`), and scenario IDs (`S-033-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Enum, Migrations & Model Updates

- [x] T-033-01 – Create `UserUploadTrustLevel` string-backed enum (FR-033-01, DO-033-01).  
  _Intent:_ Create `app/Enum/UserUploadTrustLevel.php` with three cases: `CHECK = 'check'`, `MONITOR = 'monitor'`, `TRUSTED = 'trusted'`.  
  _Files:_ `app/Enum/UserUploadTrustLevel.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ Follow the pattern of `UserSharedAlbumsVisibility` enum (string-backed, in `App\Enum` namespace).

- [x] T-033-02 – Create migration: add `upload_trust_level` to `users` table (FR-033-01, DO-033-02, NFR-033-02, NFR-033-06).  
  _Intent:_ Add `string('upload_trust_level', 20)->default('trusted')->after('quota_kb')` column to `users` table.  
  _Files:_ `database/migrations/2026_04_09_000001_add_upload_trust_level_to_users.php`  
  _Verification commands:_  
  - `php artisan migrate`  
  - `php artisan migrate:rollback --step=1 && php artisan migrate`  
  _Notes:_ Default `trusted` ensures backward compatibility (NFR-033-02). String column allows enum extensibility (NFR-033-06).

- [x] T-033-03 – Create migration: add `is_upload_validated` to `photos` table (FR-033-02, DO-033-03, NFR-033-01, NFR-033-02).  
  _Intent:_ Add `boolean('is_upload_validated')->default(true)->after('is_highlighted')` with index to `photos` table.  
  _Files:_ `database/migrations/2026_04_09_000002_add_is_upload_validated_to_photos.php`  
  _Verification commands:_  
  - `php artisan migrate`  
  - `php artisan migrate:rollback --step=1 && php artisan migrate`  
  _Notes:_ Default `true` ensures all existing photos remain visible (NFR-033-02). Index supports efficient query filtering (NFR-033-01).

- [x] T-033-04 – Create config migration: `default_user_trust_level` and `guest_upload_trust_level` (FR-033-09, DO-033-04).  
  _Intent:_ Extend `BaseConfigMigration` to insert two config rows: `default_user_trust_level` (value `trusted`, cat `Admin`, type_range `check|monitor|trusted`) and `guest_upload_trust_level` (value `check`, same type_range).  
  _Files:_ `database/migrations/2026_04_09_000003_add_upload_trust_level_configs.php`  
  _Verification commands:_  
  - `php artisan migrate`  
  - `php artisan tinker --execute="echo DB::table('configs')->where('key','default_user_trust_level')->value('value');"`  
  _Notes:_ Follow pattern of `2026_03_12_000000_add_search_colour_distance_config.php`.

- [x] T-033-05 – Update `User` model with `upload_trust_level` property and cast (FR-033-01, DO-033-02).  
  _Intent:_ Add `use App\Enum\UserUploadTrustLevel` import, add `@property UserUploadTrustLevel $upload_trust_level` PHPDoc, add `'upload_trust_level' => UserUploadTrustLevel::class` to `$casts` array.  
  _Files:_ `app/Models/User.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Follow the pattern of `shared_albums_visibility` cast on User model.

- [x] T-033-06 – Update `Photo` model with `is_upload_validated` property and cast (FR-033-02, DO-033-03).  
  _Intent:_ Add `@property bool $is_upload_validated` PHPDoc, add `'is_upload_validated' => 'boolean'` to `$casts` array.  
  _Files:_ `app/Models/Photo.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Follow the pattern of `is_highlighted` boolean cast on Photo model.

- [x] T-033-06b – Update `UserFactory` with `upload_trust_level` default (FX-033-01).  
  _Intent:_ Add `'upload_trust_level' => UserUploadTrustLevel::TRUSTED->value` to factory `definition()` method. Add `use App\Enum\UserUploadTrustLevel` import.  
  _Files:_ `database/factories/UserFactory.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Follow the pattern of `shared_albums_visibility` default in factory.

### I2 – Photo Creation Pipeline Integration

- [x] T-033-07 – Create `SetUploadValidated` pipe for photo creation pipeline (FR-033-03, S-033-05, S-033-06; Q-033-01 → A, Q-033-03 → A).  
  _Intent:_ Create `app/Actions/Photo/Pipes/Shared/SetUploadValidated.php`. The pipe resolves the intended owner from `$state->intended_owner_id` and sets `$state->photo->is_upload_validated` as follows: (1) if owner exists and `may_administrate === true`, always set to `true` (admin short-circuit, Q-033-03 → A); (2) if owner is `null` (guest upload), read `guest_upload_trust_level` config; (3) otherwise read `owner->upload_trust_level`. Trust level `CHECK` → `false`; `TRUSTED` or `MONITOR` → `true` (Q-033-01 → A: `monitor` behaves as `trusted`).  
  _Files:_ `app/Actions/Photo/Pipes/Shared/SetUploadValidated.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Look at `SetOwnership.php` and `SetHighlighted.php` for pipe patterns. The pipe must handle the case where `intended_owner_id` is 0 or null (guest upload). Admin short-circuit takes precedence over trust level.

- [x] T-033-08 – Handle guest upload trust level in `SetUploadValidated` pipe (FR-033-04, S-033-07).  
  _Intent:_ When `intended_owner_id` resolves to no user (guest/anonymous upload), read `guest_upload_trust_level` config from `ConfigManager` and use that to determine `is_upload_validated`. For `MONITOR`, treat as `trusted` (Q-033-01 → A).  
  _Files:_ `app/Actions/Photo/Pipes/Shared/SetUploadValidated.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Use `app(ConfigManager::class)->getValueAsEnum('guest_upload_trust_level', UserUploadTrustLevel::class)`. Admin short-circuit does not apply to guest uploads.

- [x] T-033-09 – Register `SetUploadValidated` in photo creation pipeline (FR-033-03).  
  _Intent:_ Add `SetUploadValidated::class` to the shared pipe chain in `Create.php`, after `SetOwnership` and before `Save`. Ensure it runs for both new photo creation and duplicate handling flows.  
  _Files:_ `app/Actions/Photo/Create.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `php artisan test --filter=PhotoAddTest`  
  _Notes:_ Review both `$init_pipes` (or equivalent) and the finalize pipeline to determine the correct insertion point.

### I3 – PhotoQueryPolicy Visibility Filter

- [x] T-033-10 – Update `applyVisibilityFilter` to exclude unvalidated photos (FR-033-05, S-033-08, S-033-09, S-033-10).  
  _Intent:_ In `PhotoQueryPolicy::applyVisibilityFilter()`, for non-admin users, add a condition that requires `photos.is_upload_validated = true` OR `photos.owner_id = $user_id`. This means unvalidated photos are only visible to their owner or to admins. The admin early-return already bypasses the filter.  
  _Files:_ `app/Policies/PhotoQueryPolicy.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `php artisan test --filter=PhotoQueryPolicy`  
  _Notes:_ The owner condition already exists in the visibility sub-query (`orWhere('photos.owner_id', '=', $user_id)`). The unvalidated filter must wrap or augment the existing logic so that album accessibility alone is not sufficient for unvalidated photos — the viewer must also be the owner.

- [x] T-033-11 – Update `applySearchabilityFilter` to exclude unvalidated photos (FR-033-05, S-033-19).  
  _Intent:_ Apply the same `is_upload_validated` filter logic in `PhotoQueryPolicy::applySearchabilityFilter()` so search results also hide unvalidated photos from non-owners/non-admins.  
  _Files:_ `app/Policies/PhotoQueryPolicy.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Consistent filtering across visibility and searchability.

- [x] T-033-12 – Write feature tests for visibility filtering with trust levels (S-033-08, S-033-09, S-033-10, S-033-19, S-033-20).  
  _Intent:_ Create `tests/Feature_v2/TrustLevel/PhotoVisibilityTest.php`. Tests:  
  - Upload photo as `check`-level user → not visible to anonymous user.  
  - Upload photo as `check`-level user → visible to photo owner.  
  - Upload photo as `check`-level user → visible to admin.  
  - Upload photo as `trusted`-level user → visible to anonymous user (if album is public).  
  - Admin approves photo → now visible to anonymous user.  
  - Search results exclude unvalidated photos for non-owner.  
  _Files:_ `tests/Feature_v2/TrustLevel/PhotoVisibilityTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=PhotoVisibilityTest`  

### I4 – User Management API Updates

- [x] T-033-13 – Add `UPLOAD_TRUST_LEVEL_ATTRIBUTE` to `RequestAttribute` constants (DO-033-02).  
  _Intent:_ Add `public const UPLOAD_TRUST_LEVEL_ATTRIBUTE = 'upload_trust_level';` to `RequestAttribute` class.  
  _Files:_ `app/Contracts/Http/Requests/RequestAttribute.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-14 – Update `AddUserRequest` to accept `upload_trust_level` (FR-033-08, S-033-01, S-033-02).  
  _Intent:_ Add optional `upload_trust_level` validation rule using `Illuminate\Validation\Rules\Enum(UserUploadTrustLevel::class)`. In `processValidatedValues`, read value or default to `ConfigManager::getValueAsEnum('default_user_trust_level', UserUploadTrustLevel::class)`. Add `uploadTrustLevel(): UserUploadTrustLevel` accessor.  
  _Files:_ `app/Http/Requests/UserManagement/AddUserRequest.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Default trust level comes from config, not hardcoded.

- [x] T-033-15 – Update `SetUserSettingsRequest` to accept `upload_trust_level` (FR-033-08, S-033-03, S-033-04).  
  _Intent:_ Add optional `upload_trust_level` validation rule. In `processValidatedValues`, read value if provided, else keep existing value from user model. Add `uploadTrustLevel(): ?UserUploadTrustLevel` accessor (nullable — null means "keep existing").  
  _Files:_ `app/Http/Requests/UserManagement/SetUserSettingsRequest.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ On update, if not provided, existing trust level is preserved (S-033-04).

- [x] T-033-16 – Update `Create` user action for trust level (FR-033-08).  
  _Intent:_ Add `?UserUploadTrustLevel $upload_trust_level = null` parameter to `Create::do()`. If provided, set `$user->upload_trust_level = $upload_trust_level`. If null, use `ConfigManager::getValueAsEnum('default_user_trust_level', UserUploadTrustLevel::class)`.  
  _Files:_ `app/Actions/User/Create.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-17 – Update `Save` user action for trust level (FR-033-08).  
  _Intent:_ Add `?UserUploadTrustLevel $upload_trust_level = null` parameter to `Save::do()`. If provided, set `$user->upload_trust_level = $upload_trust_level`.  
  _Files:_ `app/Actions/User/Save.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-18 – Update `UserManagementController` to pass trust level (FR-033-08, FR-033-12, S-033-21).  
  _Intent:_ Update `create()` and `save()` to pass `$request->uploadTrustLevel()` to respective action methods. Update `list()` to include `upload_trust_level` in the select columns.  
  _Files:_ `app/Http/Controllers/Admin/UserManagementController.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-19 – Update `UserManagementResource` with trust level (FR-033-12, S-033-21).  
  _Intent:_ Add `public string $upload_trust_level` property. In constructor, set `$this->upload_trust_level = $user->upload_trust_level->value`.  
  _Files:_ `app/Http/Resources/Models/UserManagementResource.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-19b – Update `PhotoResource` with `is_upload_validated` (FR-033-13, S-033-22).  
  _Intent:_ Add `public bool $is_upload_validated` property. In constructor, set from photo model.  
  _Files:_ `app/Http/Resources/Models/PhotoResource.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-19c – Write feature tests for user management trust level CRUD (S-033-01, S-033-02, S-033-03, S-033-04, S-033-21).  
  _Intent:_ Create `tests/Feature_v2/TrustLevel/UserTrustLevelTest.php`. Tests:  
  - Admin creates user with `upload_trust_level = check` → persisted with `check`.  
  - Admin creates user without trust level → defaults to config value.  
  - Admin updates user trust level → updated.  
  - Admin updates user without trust level field → existing value preserved.  
  - List users → trust level visible in response.  
  - Non-admin cannot create/update users (existing behaviour, verify not broken).  
  _Files:_ `tests/Feature_v2/TrustLevel/UserTrustLevelTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=UserTrustLevelTest`  

### I5 – Moderation API Endpoints

- [x] T-033-20 – Create `ModerationResource` (Spatie Data) (API-033-01).  
  _Intent:_ Create `app/Http/Resources/Models/ModerationResource.php` extending Spatie `Data`. Fields: `photo_id` (string), `title` (string), `thumb_url` (string|null), `owner_username` (string), `album_title` (string|null), `created_at` (string). Constructor takes Photo model with owner and albums relations loaded.  
  _Files:_ `app/Http/Resources/Models/ModerationResource.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-21 – Create `ListModerationRequest` and `ApproveModerationRequest` (API-033-01, API-033-02).  
  _Intent:_ `ListModerationRequest`: admin-only authorization (gate check). `ApproveModerationRequest`: admin-only authorization, validates `photo_ids` (required array, each valid `RandomIDRule`).  
  _Files:_ `app/Http/Requests/Moderation/ListModerationRequest.php`, `app/Http/Requests/Moderation/ApproveModerationRequest.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-22 – Create `ModerationController` (FR-033-10, FR-033-11, API-033-01, API-033-02).  
  _Intent:_ Create `app/Http/Controllers/Admin/ModerationController.php` with:  
  - `list(ListModerationRequest)`: Query `Photo::where('is_upload_validated', false)->with(['owner', 'albums'])->orderBy('created_at', 'desc')`, paginate (default 30, max 100). Transform to `ModerationResource` collection.  
  - `approve(ApproveModerationRequest)`: `Photo::whereIn('id', $photo_ids)->update(['is_upload_validated' => true])`. Return 204.  
  _Files:_ `app/Http/Controllers/Admin/ModerationController.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-033-23 – Register moderation routes (API-033-01, API-033-02).  
  _Intent:_ Add to `routes/api_v2.php`: `Route::get('/Moderation', [ModerationController::class, 'list'])` and `Route::post('/Moderation::approve', [ModerationController::class, 'approve'])`.  
  _Files:_ `routes/api_v2.php`  
  _Verification commands:_  
  - `php artisan route:list --name=Moderation`  
  - `make phpstan`  

- [x] T-033-24 – Write feature tests for moderation API (S-033-11, S-033-12, S-033-13, S-033-14, S-033-17, S-033-18).  
  _Intent:_ Create `tests/Feature_v2/TrustLevel/ModerationTest.php`. Tests:  
  - Admin lists unvalidated photos → 200 with correct data.  
  - Non-admin lists → 403.  
  - Unauthenticated lists → 401.  
  - Empty list → 200 with empty data.  
  - Admin approves photos → 204, photos now validated.  
  - Admin approves with invalid IDs → 422.  
  - Non-admin approves → 403.  
  - Bulk approve 200+ photos → processes correctly.  
  - Pagination works (page, per_page params).  
  _Files:_ `tests/Feature_v2/TrustLevel/ModerationTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=ModerationTest`  

### I6 – Frontend: User Management Trust Level

- [x] T-033-25 – Update TypeScript types for trust level (DO-033-01, S-033-21).  
  _Intent:_ Add `upload_trust_level: string` to `UserManagementResource` type in `lychee.d.ts`. Add `App.Enum.UserUploadTrustLevel` type union.  
  _Files:_ `resources/js/lychee.d.ts`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-033-26 – Update `user-management-service.ts` for trust level (API-033-03, API-033-04).  
  _Intent:_ Add `upload_trust_level?: string` to `UserManagementCreateRequest` type.  
  _Files:_ `resources/js/services/user-management-service.ts`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-033-27 – Update `CreateEditUser.vue` with trust level dropdown (UI-033-01, UI-033-09).  
  _Intent:_ Add PrimeVue Select component for trust level with options `check`, `monitor`, `trusted`. Default to `trusted` on create. Bind to `upload_trust_level` ref. Pass in create and edit API calls. Watch prop for trust level on edit.  
  _Files:_ `resources/js/components/forms/users/CreateEditUser.vue`  
  _Verification commands:_  
  - `npm run check`  
  - Manual testing: create user with trust level, edit user trust level  

- [x] T-033-28 – Update `ListUser.vue` with trust level indicator (UI-033-02).  
  _Intent:_ Display shield icon (`pi pi-shield`) with colour coding: green for `trusted`, yellow for `monitor`, red for `check`. Add tooltip with trust level name.  
  _Files:_ `resources/js/components/forms/users/ListUser.vue`  
  _Verification commands:_  
  - `npm run check`  
  - Manual testing: verify icons in user list  

- [x] T-033-28b – Update `Users.vue` legend with trust level (UI-033-02).  
  _Intent:_ Add trust level icon and description to the legend card.  
  _Files:_ `resources/js/views/Users.vue`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-033-28c – Add English language strings for trust level (UI-033-01, UI-033-02).  
  _Intent:_ Add trust level labels and descriptions to `lang/en/users.php`.  
  _Files:_ `lang/en/users.php`  
  _Verification commands:_  
  - `npm run check`  

### I7 – Frontend: Moderation Panel

- [x] T-033-29 – Create `moderation-service.ts` (API-033-01, API-033-02).  
  _Intent:_ Create `resources/js/services/moderation-service.ts` with `list(page?)` → `GET /Moderation` and `approve(photo_ids)` → `POST /Moderation::approve`.  
  _Files:_ `resources/js/services/moderation-service.ts`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-033-30 – Update TypeScript types for moderation (DO-033-03, API-033-01).  
  _Intent:_ Add `ModerationResource` type to `lychee.d.ts`. Add `is_upload_validated: boolean` to `PhotoResource` type.  
  _Files:_ `resources/js/lychee.d.ts`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-033-31 – Create `Moderation.vue` view (UI-033-03, UI-033-05, UI-033-06, UI-033-07, UI-033-08).  
  _Intent:_ Create `resources/js/views/Moderation.vue` with:  
  - Toolbar with "Moderation" title and "Approve Selected" button (disabled when no selection).  
  - PrimeVue DataTable with checkbox selection column, columns: thumbnail, title, owner, album, upload date.  
  - Empty state message when no unvalidated photos.  
  - Pagination (lazy loading or front-end pagination).  
  - Approve button calls `moderation-service.approve()`, shows toast, reloads.  
  _Files:_ `resources/js/views/Moderation.vue`  
  _Verification commands:_  
  - `npm run check`  
  - Manual testing: navigate to moderation panel, verify rendering  

- [x] T-033-32 – Add moderation route (UI-033-04).  
  _Intent:_ Add `{ path: '/moderation', component: () => import('@/views/Moderation.vue') }` to `routes.ts`.  
  _Files:_ `resources/js/router/routes.ts`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-033-33 – Add moderation entry to left menu (UI-033-04).  
  _Intent:_ Add moderation link to admin section of left menu, visible only to admins. Use shield or moderation icon.  
  _Files:_ `resources/js/composables/contextMenus/leftMenu.ts`  
  _Verification commands:_  
  - `npm run check`  
  - Manual testing: verify menu link appears for admin users  

- [x] T-033-34 – Create English language file for moderation (UI-033-03).  
  _Intent:_ Create `lang/en/moderation.php` with strings: title, description, approve button, empty state, success toast, column headers.  
  _Files:_ `lang/en/moderation.php`  
  _Verification commands:_  
  - `npm run check`  

### I9 – Queued-Job Guest-Upload Trust Fix

These tasks address FR-033-14. `ProcessImageJob` loses the HTTP session when it runs in a queue worker, so `Auth::user()` is no longer available. The job currently resolves `intended_owner_id` to the album owner as an ownership fallback for guest uploads. Without an explicit flag, `SetUploadValidated` cannot distinguish a guest upload from a direct owner upload and silently skips the `guest_upload_trust_level` config branch.

- [ ] T-033-38 – Add `is_guest_upload` flag to `ProcessImageJob` (FR-033-14, S-033-24, DO-033-05).  
  _Intent:_ Add `public bool $is_guest_upload;` to `ProcessImageJob`. In the constructor, **before** the `$this->user_id = $user_id ?? $album?->owner_id` assignment, capture `$this->is_guest_upload = ($user_id === null);`. This preserves the original uploader context as a serialisable primitive.  
  _Files:_ `app/Jobs/ProcessImageJob.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  _Notes:_ `is_guest_upload` must be declared as a `public` property so Laravel's `SerializesModels` can serialise it to the queue payload. It is intentionally separate from `user_id` (which still holds the album owner for photo ownership purposes).

- [ ] T-033-39 – Thread `is_guest_upload` from `ProcessImageJob::handle()` to `Create::add()` (FR-033-14).  
  _Intent:_ Add an `is_guest_upload: bool = false` parameter to `Create::add()`. In `ProcessImageJob::handle()`, pass `is_guest_upload: $this->is_guest_upload`. Thread the flag through into the pipeline state DTO (whichever DTO is used by `SetUploadValidated`) as a `bool $is_guest_upload` field (default `false`).  
  _Files:_ `app/Actions/Photo/Create.php`, `app/DTO/PhotoCreate/StandaloneDTO.php`, `app/DTO/PhotoCreate/DuplicateDTO.php`, `app/Jobs/ProcessImageJob.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ All other callers of `Create::add()` that do not pass the flag default to `false` (authenticated user upload or import). No existing callers need changes beyond the default. Verify no other caller breaks by running `php artisan test`.

- [ ] T-033-40 – Update `SetUploadValidated` to consume `is_guest_upload` flag (FR-033-14, S-033-24).  
  _Intent:_ In `SetUploadValidated::resolveIsValidated()`, check `$state->is_guest_upload` (or receive as parameter) **before** the `intended_owner_id === 0` check. When `is_guest_upload === true`, execute the same guest-upload branch: read `guest_upload_trust_level` from `ConfigManager`. Admin short-circuit does not apply when `is_guest_upload` is `true` (the intended owner is the album owner, not the uploader, so their admin status is irrelevant to the trust decision).  
  _Files:_ `app/Actions/Photo/Pipes/Shared/SetUploadValidated.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `php artisan test --filter=SetUploadValidated`  
  _Notes:_ The guard order becomes: (1) `is_guest_upload === true` → guest branch; (2) `intended_owner_id === 0` → guest branch (direct dispatch edge case); (3) admin short-circuit; (4) owner trust level.

- [ ] T-033-41 – Write feature test for queued guest upload trust enforcement (S-033-24, FR-033-14).  
  _Intent:_ Extend `tests/Feature_v2/TrustLevel/UploadModerationFlowTest.php` (or create a dedicated `QueuedGuestUploadTrustTest.php`). Test steps:  
  1. Set `guest_upload_trust_level` config to `check`.  
  2. Create an album with `grants_upload = true` owned by a `trusted`-level user.  
  3. Simulate a `ProcessImageJob` dispatched as if by a guest (construct with `Auth::user()` null → `is_guest_upload = true`).  
  4. Run `handle()` synchronously.  
  5. Assert the resulting photo has `is_upload_validated = false`.  
  6. Repeat with `guest_upload_trust_level = trusted` → assert `is_upload_validated = true`.  
  _Files:_ `tests/Feature_v2/TrustLevel/QueuedGuestUploadTrustTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=QueuedGuestUploadTrust`  
  _Notes:_ Use `Queue::fake()` so no real worker is needed. Construct the job without Auth::user and call handle() directly to assert pipeline behaviour.

### I8 – Integration Tests & Final Verification

- [ ] T-033-35 – Write end-to-end integration test: upload → moderation → public (S-033-05, S-033-08, S-033-12).  
  _Intent:_ Create `tests/Feature_v2/TrustLevel/UploadModerationFlowTest.php`. Full flow:  
  1. Create user with `check` trust level.  
  2. Upload photo as that user → verify `is_upload_validated = false`.  
  3. List album photos as anonymous user → photo not in response.  
  4. List album photos as photo owner → photo in response.  
  5. Admin calls moderation list → photo present.  
  6. Admin approves photo.  
  7. List album photos as anonymous user → photo now in response.  
  _Files:_ `tests/Feature_v2/TrustLevel/UploadModerationFlowTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=UploadModerationFlowTest`  

- [ ] T-033-36 – Run full test suite and verify no regressions.  
  _Intent:_ Run complete test suite to ensure no existing tests are broken by the changes.  
  _Verification commands:_  
  - `php artisan test`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  - `npm run check`  

- [ ] T-033-37 – Update knowledge map with trust level module (Documentation).  
  _Intent:_ Add entries for Upload Trust Level enum, moderation controller, and config entries to `docs/specs/4-architecture/knowledge-map.md`.  
  _Files:_ `docs/specs/4-architecture/knowledge-map.md`  
  _Verification commands:_  
  - Manual review  

## Notes / TODOs

- The `monitor` trust level is reserved and behaves identically to `trusted` in this iteration (Q-033-01 → A). A follow-up task should implement the monitoring queue (soft-audit: uploads are public but flagged for periodic admin review).
- Admin uploads always set `is_upload_validated = true` regardless of the admin's trust level setting (Q-033-03 → A). The `SetUploadValidated` pipe checks `may_administrate` first and short-circuits. This short-circuit does NOT apply when `is_guest_upload = true` (FR-033-14): the intended owner is the album owner, not the anonymous uploader.
- **Queued-job gap (FR-033-14):** When `ProcessImageJob` runs in a queue worker, `Auth::user()` is null. Ownership falls back to `album->owner_id`. Without the `is_guest_upload` flag added in I9, `SetUploadValidated` would apply the album owner's trust level to guest uploads, completely bypassing `guest_upload_trust_level` config. Tasks T-033-38 to T-033-41 address this.
- Trust level changes do not retroactively affect existing photos (Q-033-02 → A). Only future uploads are affected. Document this clearly in the admin guide.
- The `lychee:create_user` CLI command does not currently accept a `--upload-trust-level` flag. This is deferred to a follow-up task.
- The moderation panel does not currently support a "reject" action. Admins can use existing photo delete functionality. A dedicated rejection workflow is a follow-up.
- Consider adding a badge/count of pending moderation items to the admin left menu entry as a future enhancement.
