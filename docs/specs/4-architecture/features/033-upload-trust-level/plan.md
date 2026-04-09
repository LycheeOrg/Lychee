# Feature Plan 033 – Upload Trust Level

_Linked specification:_ `docs/specs/4-architecture/features/033-upload-trust-level/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-04-09

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Allow Lychee administrators to control whether a user's uploads are immediately public or require explicit approval. Administrators can review and bulk-approve pending uploads through a dedicated moderation panel. No existing behaviour changes for installations that do not configure trust levels.

**Success signals:**
- `UserUploadTrustLevel` enum created and cast on `User` model.
- `is_upload_validated` boolean on `Photo` model, set during photo creation pipeline.
- `PhotoQueryPolicy` correctly hides unvalidated photos from public queries while preserving owner and admin visibility.
- User management API accepts and returns `upload_trust_level`.
- Two new config entries (`default_user_trust_level`, `guest_upload_trust_level`) stored in `configs` table.
- Admin moderation endpoints (`GET /Moderation`, `POST /Moderation::approve`) pass feature tests.
- Admin Vue moderation panel renders, supports selection and bulk-approve.
- `php artisan test` — full suite green.
- `make phpstan` — 0 errors.
- `npm run check` — 0 TypeScript/Vue errors.

## Scope Alignment

- **In scope:**
  - `UserUploadTrustLevel` string-backed enum (`check`, `monitor`, `trusted`).
  - Migration: `upload_trust_level` column on `users` table (default `trusted`).
  - Migration: `is_upload_validated` boolean on `photos` table (default `true`, indexed).
  - Migration: two config entries in `configs` table.
  - `User` model cast, factory update, resource update.
  - `Photo` model cast, resource update.
  - Photo creation pipeline: determine `is_upload_validated` from user trust level or guest config.
  - `PhotoQueryPolicy::applyVisibilityFilter` updated to exclude unvalidated photos for non-owner, non-admin users.
  - `PhotoQueryPolicy::applySearchabilityFilter` updated similarly.
  - User management request classes (`AddUserRequest`, `SetUserSettingsRequest`) updated for trust level.
  - `Create` and `Save` user actions updated for trust level.
  - `UserManagementController::list` updated to select trust level.
  - New `ModerationController` with list and approve endpoints.
  - New `ModerationResource` (Spatie Data).
  - Frontend: trust level dropdown in `CreateEditUser.vue`, indicator in `ListUser.vue`.
  - Frontend: new `Moderation.vue` view with DataTable, route, left-menu entry.
  - Frontend: `moderation-service.ts`, type definitions.
  - English language strings for trust level and moderation.
  - Feature tests for trust-level CRUD, photo validation filtering, moderation API.

- **Out of scope:**
  - `monitor` trust level distinct behaviour — reserved for future.
  - Per-album trust-level overrides.
  - Automated content moderation / NSFW detection.
  - Rejection workflow (admin can use existing delete).
  - User notification on approval.
  - Retroactive trust-level changes affecting existing photos.
  - CLI `lychee:create_user` trust-level flag (follow-up).

## Dependencies & Interfaces

- **`User` model** (`app/Models/User.php`) — new `upload_trust_level` property and enum cast.
- **`Photo` model** (`app/Models/Photo.php`) — new `is_upload_validated` boolean property and cast.
- **`ImportParam` DTO** (`app/DTO/ImportParam.php`) — may need new `is_upload_validated` parameter, or determination can happen in a pipe.
- **Photo creation pipeline** (`app/Actions/Photo/Create.php`) — trust level resolution pipe added to the shared pipe chain.
- **`PhotoQueryPolicy`** (`app/Policies/PhotoQueryPolicy.php`) — visibility and searchability filters updated.
- **`ConfigManager`** (`app/Repositories/ConfigManager.php`) — reads `default_user_trust_level` and `guest_upload_trust_level`.
- **User management requests** (`app/Http/Requests/UserManagement/`) — updated for trust level validation.
- **User actions** (`app/Actions/User/Create.php`, `app/Actions/User/Save.php`) — updated for trust level parameter.
- **`UserManagementResource`** (`app/Http/Resources/Models/UserManagementResource.php`) — includes trust level.
- **`PhotoResource`** (`app/Http/Resources/Models/PhotoResource.php`) — includes `is_upload_validated`.
- **PrimeVue / Vue 3** — admin UI components.
- **Spatie Laravel Data** — `ModerationResource` extends `Data`.

## Assumptions & Risks

- **Assumptions:**
  - The photo creation pipeline supports adding new pipes without disrupting existing behaviour.
  - The `PhotoQueryPolicy` visibility sub-query can be extended with an additional AND condition on `is_upload_validated`.
  - Queue workers are not required — trust level resolution is synchronous during photo creation.
  - The existing `ImportParam` DTO can be extended with an additional boolean or the determination can be made in a new pipe that queries the user model.

- **Risks / Mitigations:**
  - *Performance impact of adding a WHERE condition to every photo query:* Mitigation: `is_upload_validated` is indexed; for the common case (all photos validated), the index lookup is trivially fast.
  - *Guest upload trust level resolution may require loading config in a hot path:* Mitigation: `ConfigManager` caches config values in memory; no additional DB queries.
  - *Moderation backlog could grow large if admin doesn't review:* Mitigation: pagination on moderation endpoint (NFR-033-03); UI shows total count.
  - *Changing trust level for a user does not retroactively update existing photos:* This is intentional and confirmed (Q-033-02 → A). Mitigation: document this behaviour clearly in the admin guide.

## Implementation Drift Gate

After each increment, verify:

1. `php artisan test --filter=TrustLevel` — all trust-level-related tests pass.
2. `php artisan test --filter=Moderation` — all moderation tests pass.
3. `make phpstan` — 0 PHPStan errors.
4. `vendor/bin/php-cs-fixer fix --dry-run` — 0 formatting violations.
5. `npm run check` (after frontend increments) — 0 TypeScript/Vue errors.

## Increment Map

### I1 – Enum, Migrations & Model Updates (≈60 min)

- _Goal:_ Create `UserUploadTrustLevel` enum, database migrations for users, photos, and configs tables. Update `User` and `Photo` models with new properties and casts.
- _Preconditions:_ None.
- _Steps:_
  1. Create `app/Enum/UserUploadTrustLevel.php` string-backed enum: `CHECK = 'check'`, `MONITOR = 'monitor'`, `TRUSTED = 'trusted'`.
  2. Create migration `add_upload_trust_level_to_users`: add `upload_trust_level` string(20) column to `users` table, default `trusted`, after `quota_kb`.
  3. Create migration `add_is_upload_validated_to_photos`: add `is_upload_validated` boolean to `photos` table, default `true`, indexed, after `is_highlighted`.
  4. Create config migration extending `BaseConfigMigration`: add `default_user_trust_level` (default `trusted`, cat `Admin`, type_range `check|monitor|trusted`) and `guest_upload_trust_level` (default `check`, same type_range).
  5. Update `User` model: add `upload_trust_level` to `$casts` array as `UserUploadTrustLevel::class`. Add `@property` PHPDoc.
  6. Update `Photo` model: add `is_upload_validated` to `$casts` array as `boolean`. Add `@property` PHPDoc.
  7. Update `UserFactory`: add `upload_trust_level` default value.
  8. Run `php artisan migrate` and verify schema.
- _Commands:_ `php artisan migrate`, `make phpstan`
- _Exit:_ All models compile, migrations applied, PHPStan clean.

### I2 – Photo Creation Pipeline Integration (≈60 min)

- _Goal:_ Set `is_upload_validated` on new photos based on the uploader's trust level or guest config.
- _Preconditions:_ I1 complete. Q-033-01, Q-033-03 resolved.
- _Steps:_
  1. Create `app/Actions/Photo/Pipes/Shared/SetUploadValidated.php` — a new pipe in the shared photo creation pipeline. Logic:
     - Resolve the intended owner from `$state->intended_owner_id`.
     - If owner exists and `owner->may_administrate === true`: set `is_upload_validated = true` (admin short-circuit, Q-033-03 → A).
     - If owner is `null` (guest upload): read `guest_upload_trust_level` config via `ConfigManager`.
     - If owner exists (non-admin): read `owner->upload_trust_level`.
     - If trust level is `CHECK`: set `$state->photo->is_upload_validated = false`.
     - For `TRUSTED` or `MONITOR`: set `$state->photo->is_upload_validated = true` (Q-033-01 → A: `monitor` behaves as `trusted`).
  2. Register the pipe in `Create.php` shared pipe list (after `SetOwnership`, before `Save`).
  3. Ensure the pipe also runs in the duplicate detection pipeline if applicable.
  4. Write unit test: mock admin user with `check` trust level → photo gets `is_upload_validated = true` (admin override).
  5. Write unit test: mock non-admin user with `check` trust level → photo gets `is_upload_validated = false`.
  6. Write unit test: mock non-admin user with `trusted` trust level → photo gets `is_upload_validated = true`.
  7. Write unit test: mock non-admin user with `monitor` trust level → photo gets `is_upload_validated = true`.
  8. Write unit test: guest upload with `guest_upload_trust_level = check` → photo gets `is_upload_validated = false`.
- _Commands:_ `php artisan test --filter=SetUploadValidated`, `make phpstan`
- _Exit:_ Photos created with correct `is_upload_validated` based on trust level.

### I3 – PhotoQueryPolicy Visibility Filter (≈45 min)

- _Goal:_ Hide unvalidated photos from public queries while preserving owner and admin visibility.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Update `PhotoQueryPolicy::applyVisibilityFilter()`: after the existing visibility sub-query, add an additional condition — for non-admin, non-owner contexts, also require `photos.is_upload_validated = true` OR `photos.owner_id = $user_id`.
  2. Update `PhotoQueryPolicy::applySearchabilityFilter()`: apply the same `is_upload_validated` condition.
  3. Write feature test: user with `check` trust level uploads photo → photo visible to owner, visible to admin, NOT visible to other users or anonymous visitors.
  4. Write feature test: user with `trusted` trust level uploads photo → photo visible to everyone (per album permissions).
  5. Write feature test: admin approves photo (sets `is_upload_validated = true`) → photo becomes visible to all.
- _Commands:_ `php artisan test --filter=PhotoQueryPolicy`, `make phpstan`
- _Exit:_ Visibility filter correctly discriminates validated/unvalidated photos.

### I4 – User Management API Updates (≈45 min)

- _Goal:_ Allow admins to set and view `upload_trust_level` via user management CRUD.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add `UPLOAD_TRUST_LEVEL_ATTRIBUTE` constant to `RequestAttribute`.
  2. Update `AddUserRequest`: add optional `upload_trust_level` validation rule (enum validation via `Illuminate\Validation\Rules\Enum`). In `processValidatedValues`, read value or default to `ConfigManager::getValueAsEnum('default_user_trust_level', UserUploadTrustLevel::class)`.
  3. Update `SetUserSettingsRequest`: add optional `upload_trust_level` validation rule. In `processValidatedValues`, read value (keeping existing if not provided).
  4. Add `uploadTrustLevel()` accessor methods on both request classes.
  5. Update `Create` user action: accept `?UserUploadTrustLevel $upload_trust_level` parameter, set on user model.
  6. Update `Save` user action: accept `?UserUploadTrustLevel $upload_trust_level` parameter, set on user model.
  7. Update `UserManagementController::create` and `::save`: pass trust level from request to action.
  8. Update `UserManagementController::list`: add `upload_trust_level` to the select columns.
  9. Update `UserManagementResource`: add `public string $upload_trust_level` field, populate from user model.
  10. Write feature tests: create user with explicit trust level, create user without trust level (verify default), update user trust level, list users shows trust level.
- _Commands:_ `php artisan test --filter=UserManagement`, `make phpstan`
- _Exit:_ Trust level round-trips correctly through create, update, and list API.

### I5 – Moderation API Endpoints (≈60 min)

- _Goal:_ Create admin-only moderation endpoints for listing and approving unvalidated photos.
- _Preconditions:_ I1 and I2 complete.
- _Steps:_
  1. Create `ModerationResource` (Spatie Data): `photo_id`, `title`, `thumb_url`, `owner_username`, `album_title`, `created_at`.
  2. Create `ListModerationRequest` — admin-only authorization (via `UserPolicy::CAN_ADMINISTRATE` or similar gate), supports pagination parameters.
  3. Create `ApproveModerationRequest` — admin-only authorization, validates `photo_ids` array (required, array, min:1, each valid RandomIDRule).
  4. Create `ModerationController`:
     - `list(ListModerationRequest)`: query `Photo::where('is_upload_validated', false)->orderBy('created_at', 'desc')`, paginate, eager-load owner and albums, transform to `ModerationResource`.
     - `approve(ApproveModerationRequest)`: update `Photo::whereIn('id', $photo_ids)->update(['is_upload_validated' => true])` in chunks of 100.
  5. Register routes in `routes/api_v2.php`: `GET /Moderation` → `ModerationController::list`, `POST /Moderation::approve` → `ModerationController::approve`.
  6. Write feature tests: list unvalidated photos (admin → 200, non-admin → 403, empty → 200 with empty data), approve photos (admin → 204, non-admin → 403, invalid IDs → 422).
- _Commands:_ `php artisan test --filter=Moderation`, `make phpstan`
- _Exit:_ Moderation API fully functional and tested.

### I6 – Frontend: User Management Trust Level (≈45 min)

- _Goal:_ Add trust level selection to user create/edit dialog and indicator to user list.
- _Preconditions:_ I4 complete.
- _Steps:_
  1. Update `lychee.d.ts`: add `upload_trust_level: string` to `UserManagementResource` type. Add `App.Enum.UserUploadTrustLevel` type.
  2. Update `user-management-service.ts`: add `upload_trust_level` to `UserManagementCreateRequest` type.
  3. Update `CreateEditUser.vue`:
     - Add Select/Dropdown component for trust level (options: `check`, `monitor`, `trusted`).
     - Bind to `upload_trust_level` ref, default to `trusted`.
     - Pass trust level in create and edit API calls.
     - Watch prop for trust level on edit.
  4. Update `ListUser.vue`:
     - Display trust level indicator (shield icon with colour: green=trusted, yellow=monitor, red=check).
     - Add tooltip explaining the trust level.
  5. Update `Users.vue`:
     - Add trust level icon to column header legend.
  6. Add English language strings in `lang/en/users.php`: trust level labels and descriptions.
- _Commands:_ `npm run check`, manual visual testing.
- _Exit:_ Trust level visible and editable in user management UI.

### I7 – Frontend: Moderation Panel (≈60 min)

- _Goal:_ Create the admin moderation panel for reviewing and approving unvalidated photos.
- _Preconditions:_ I5 complete.
- _Steps:_
  1. Create `resources/js/services/moderation-service.ts`: `list()` (GET /Moderation), `approve(photo_ids)` (POST /Moderation::approve).
  2. Update `lychee.d.ts`: add `ModerationResource` type.
  3. Create `resources/js/views/Moderation.vue`:
     - Toolbar with title "Moderation" and "Approve Selected" button.
     - PrimeVue DataTable with checkbox selection, columns: thumbnail, title, owner, album, upload date.
     - Empty state message when no unvalidated photos.
     - Pagination.
     - Approve button calls `moderation-service.approve()`, shows success toast, reloads list.
  4. Add route in `resources/js/router/routes.ts`: `/moderation` → `Moderation.vue`.
  5. Update `leftMenu.ts`: add moderation link visible to admins.
  6. Add English language strings in `lang/en/moderation.php`.
  7. Update `PhotoResource` in `lychee.d.ts` with `is_upload_validated` field.
- _Commands:_ `npm run check`, manual visual testing.
- _Exit:_ Moderation panel renders, loads data, and supports bulk-approve.

### I8 – Integration Tests & Final Verification (≈60 min)

- _Goal:_ Comprehensive end-to-end testing and cleanup.
- _Preconditions:_ I1–I7 complete.
- _Steps:_
  1. Write integration test: full upload-to-moderation-to-public flow.
     - Create user with `check` trust level.
     - Upload photo as that user → photo has `is_upload_validated = false`.
     - Verify photo hidden from public listing.
     - Admin approves photo via moderation endpoint.
     - Verify photo now visible in public listing.
  2. Run full test suite: `php artisan test`.
  3. Run PHPStan: `make phpstan`.
  4. Run CS fixer: `vendor/bin/php-cs-fixer fix --dry-run`.
  5. Run frontend checks: `npm run check`.
  6. Update `docs/specs/4-architecture/knowledge-map.md` with trust level module entry.
  7. Verify all scenarios in Branch & Scenario Matrix are covered by tests.
- _Commands:_ `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix --dry-run`, `npm run check`
- _Exit:_ Full suite green, all linting clean, documentation updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-033-01 | I4 / T-033-14 | User creation with explicit trust level |
| S-033-02 | I4 / T-033-15 | User creation with default trust level |
| S-033-03 | I4 / T-033-16 | User update trust level |
| S-033-04 | I4 / T-033-17 | User update without trust level preserves existing |
| S-033-05 | I2 / T-033-07 | Check-level user upload → unvalidated |
| S-033-06 | I2 / T-033-08 | Trusted-level user upload → validated |
| S-033-07 | I2 / T-033-09 | Guest upload → config-based validation |
| S-033-08 | I3 / T-033-10 | Public visibility excludes unvalidated |
| S-033-09 | I3 / T-033-11 | Owner visibility includes unvalidated |
| S-033-10 | I3 / T-033-12 | Admin visibility includes unvalidated |
| S-033-11 | I5 / T-033-20 | Moderation list endpoint |
| S-033-12 | I5 / T-033-22 | Moderation approve endpoint |
| S-033-13 | I5 / T-033-21 | Moderation auth enforcement |
| S-033-14 | I5 / T-033-23 | Approve with invalid IDs |
| S-033-15 | I4 / T-033-15, I8 | Config default trust level effect |
| S-033-16 | I2 / T-033-09, I8 | Guest trust level config effect |
| S-033-17 | I7 / T-033-29 | Moderation empty state |
| S-033-18 | I5 / T-033-24, I8 | Bulk approve 200+ photos |
| S-033-19 | I3 / T-033-13 | Search excludes unvalidated |
| S-033-20 | I3 / T-033-13 | Smart albums exclude unvalidated |
| S-033-21 | I4 / T-033-18 | UserManagementResource includes trust level |
| S-033-22 | I7 / T-033-27, I2 | PhotoResource includes is_upload_validated |
| S-033-23 | I8 / T-033-32 | CLI user creation defaults |

## Analysis Gate

_Not yet completed._ To be filled after plan review and before implementation begins.

## Exit Criteria

- [ ] `UserUploadTrustLevel` enum exists and is cast on `User` model.
- [ ] `is_upload_validated` column on `photos` table, indexed, default `true`.
- [ ] Two config entries created and accessible via `ConfigManager`.
- [ ] Photo creation pipeline sets `is_upload_validated` based on user trust level or guest config.
- [ ] `PhotoQueryPolicy` hides unvalidated photos from non-owner, non-admin users.
- [ ] User management API (create/update/list) handles `upload_trust_level`.
- [ ] Moderation API (list/approve) works and is admin-gated.
- [ ] Frontend user management shows trust level.
- [ ] Frontend moderation panel functional with bulk-approve.
- [ ] All existing tests pass (no regressions).
- [ ] PHPStan and CS fixer clean.
- [ ] TypeScript compilation clean.
- [ ] Knowledge map updated.

## Follow-ups / Backlog

- **Monitor trust level — soft-audit queue (Q-033-01 → A):** Implement the monitoring queue for `monitor`-level users. Photos are immediately public but flagged for periodic admin review in a separate queue. This requires a new "monitoring" tab or filter in the moderation panel.
- **CLI trust level flag:** Add `--upload-trust-level` option to `lychee:create_user` Artisan command.
- **Notification system:** Notify users when their photos are approved (or rejected).
- **Moderation metrics:** Track approval rate, average time-to-approve, rejection rate.
- **Moderation rejection:** Add a reject action that deletes or hides photos with optional feedback to the uploader.
- **Per-album trust level overrides:** Allow admins to set a trust level per album (e.g., public community albums always require check).
- **Moderation pending count badge:** Show count of pending moderation items in the admin left menu entry.
