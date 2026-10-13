# Feature 089 Tasks – Moderation History and NSFW Review

_Status: In progress_  
_Last updated: 2026-10-13_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-089-01 – Worktree environment (I0).  
  _Intent:_ `vendor/` copied, `node_modules/` symlinked, `.env` copied, empty `database/database.sqlite`.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationTest`

- [x] T-089-02 – Schema, enums, model, settings (FR-089-01, FR-089-04).  
  _Intent:_ migration `photo_moderation_events`; config migration (three settings, `required_keys`); `ModerationEvent`, `ModerationSource`, `PhotoModerationEvent`; `PhotosToBeDeletedDTO` deletes events; `all_settings.php` keys in every locale; `settings_dependencies.md` row.  
  _Verification commands:_  
  - `make phpstan`

- [x] T-089-03 – Predicate tests (FR-089-04, S-089-10 … 12).  
  _Intent:_ `tests/Unit/Services/ModerationHistoryPredicateTest.php`: every event × source against each setting combination; red.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryPredicateTest`

- [x] T-089-04 – Recorder service (FR-089-04, NFR-089-03).  
  _Intent:_ `App\Services\ModerationHistory::shouldRecord()` (pure) and `record()` (one bulk insert, nothing for an empty list or an excluded event).  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryPredicateTest`  
  - `make phpstan`

- [x] T-089-05 – Upload hold tests (FR-089-02, S-089-01 … 03, S-089-10).  
  _Intent:_ `tests/Feature_v3/Moderation/ModerationHistoryUploadTest.php` uploading as `check`, `monitor` + hide-on-scan, `trusted`, and with `moderation_history_log_system = 0`; red.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryUploadTest`

- [x] T-089-06 – Upload hold pipe (FR-089-02).  
  _Intent:_ `Standalone\RecordUploadHold` appended to both standalone pipelines in `Create` (`handleStandalone`, `handlePhotoLivePartner`).  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryUploadTest`  
  - `php artisan test --filter=AutoScanNsfwOnUploadTest`  
  - `php artisan test --filter=UploadModerationFlowTest`

- [ ] T-089-07 – NSFW callback tests (FR-089-02, FR-089-03, S-089-04 … 08, S-089-11, S-089-12).  
  _Intent:_ `tests/AssistedVision/NsfwClassification/ModerationHistoryNsfwTest.php` through `POST NsfwDetection/results`; red.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryNsfwTest`

- [ ] T-089-08 – NSFW callback recording (FR-089-02, FR-089-03).  
  _Intent:_ `NsfwActionService::applyActions()` records `held` / `nsfw_scan` when moderated (no-album fallback included) and `approved` / `nsfw_scan` when a hidden non-`check` photo is restored.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryNsfwTest`  
  - `php artisan test --filter=NsfwActionServiceTest`  
  - `php artisan test --filter=NsfwDetectionResultsTest`

- [ ] T-089-09 – Approval tests (FR-089-03, NFR-089-03, S-089-09 … 11).  
  _Intent:_ `tests/Feature_v3/Moderation/ModerationHistoryApproveTest.php`: hidden and visible ids mixed, admin id stored, query count, settings off; red.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryApproveTest`

- [ ] T-089-10 – Approval recording (FR-089-03).  
  _Intent:_ `ModerationController::approvePhotos()` selects hidden ids, updates them, records once per chunk.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryApproveTest`  
  - `php artisan test --filter=ModerationTest`  
  - `php artisan test --filter=UploadModerationFlowTest`

- [ ] T-089-11 – History endpoint tests (FR-089-05, FR-089-06, NFR-089-01, NFR-089-02, S-089-13 … 16).  
  _Intent:_ `tests/Feature_v3/Moderation/ModerationHistoryListTest.php`: shape, order, paging, 401/403/404/422, photo filter, photo deletion, admin deletion, query count; red.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryListTest`

- [ ] T-089-12 – History endpoints (FR-089-05, FR-089-06).  
  _Intent:_ `ListModerationHistoryRequest`, `GetModerationPhotoHistoryRequest`, `ModerationEventResource`, `PaginatedModerationHistoryResource`, `ModerationController::history()` / `photoHistory()`, routes.  
  _Verification commands:_  
  - `php artisan test --filter=ModerationHistoryListTest`  
  - `make phpstan`

- [ ] T-089-13 – Findings listing tests (FR-089-09, NFR-089-01, NFR-089-02, S-089-17, S-089-18).  
  _Intent:_ `tests/AssistedVision/NsfwClassification/NsfwFindingsListTest.php`: one entry per photo, all detections and albums, order, paging, 401/403/402/422, query count; red.  
  _Verification commands:_  
  - `php artisan test --filter=NsfwFindingsListTest`

- [ ] T-089-14 – Findings listing (FR-089-09).  
  _Intent:_ `ListNsfwFindingsRequest`, `NsfwFindingResource`, `PaginatedNsfwFindingsResource`, `NsfwFindingsController::index()`, route (`support:se`).  
  _Verification commands:_  
  - `php artisan test --filter=NsfwFindingsListTest`  
  - `make phpstan`

- [ ] T-089-15 – Findings deletion tests (FR-089-10, FR-089-11, FR-089-14, S-089-18 … 22).  
  _Intent:_ `tests/AssistedVision/NsfwClassification/NsfwFindingsDeleteTest.php`: by id, by photo, photo state and history unchanged, validation, 401/403/402, approval without album marking; red.  
  _Verification commands:_  
  - `php artisan test --filter=NsfwFindingsDeleteTest`

- [ ] T-089-16 – Findings deletion (FR-089-10, FR-089-11).  
  _Intent:_ `DeleteNsfwFindingsRequest`, `DeleteNsfwFindingsByPhotosRequest`, `destroy()`, `destroyByPhotos()`, routes.  
  _Verification commands:_  
  - `php artisan test --filter=NsfwFindingsDeleteTest`  
  - `make phpstan`

- [ ] T-089-17 – Frontend contracts.  
  _Intent:_ `php artisan typescript:transform`; `moderation-service.ts`, `nsfw-findings-service.ts`; `usePhotoNsfwDetectionsStore.forget()`; lang keys (`moderation.php`, `admin-dashboard.php`) in every locale.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-089-18 – Findings page (FR-089-12, FR-089-13, UI-089-01).  
  _Intent:_ `v8/views/admin/NsfwFindings.vue`, `paths.ts`, v8 `routes.ts`, `web_v2.php`, dashboard tile.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [ ] T-089-19 – Moderation History tab (FR-089-07, UI-089-02).  
  _Intent:_ tabs on v8 `Moderation.vue`, History table.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [ ] T-089-20 – Photo details section (FR-089-08, UI-089-03).  
  _Intent:_ admin-only section in v8 `PhotoDetails.vue`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [ ] T-089-21 – Docs and quality gate.  
  _Intent:_ roadmap, knowledge map, Feature 033/045 pointers, drift gate report; php-cs-fixer, phpstan, prettier, vue-tsc, eslint; every test class above.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`  
  - `npm run check`

- [ ] T-089-22 – Browser check (S-089-23 … 25).  
  _Intent:_ Playwright on a scratch instance: findings page (chip, row, bulk dismissal, links, empty state), Moderation History tab, photo details section for admin and non-admin.  
  _Verification commands:_ Playwright script in the session scratchpad.

## Notes / TODOs
- 2026-10-13: `PhotoAddTest::testAppleLivePhoto1/2` fail with and without the T-089-06 change (live photo content id null, environment); not a regression of this feature.
- Tests for the NSFW parts live in the `AssistedVision` suite next to the Feature 045 tests; the other new tests go to `Feature_v3`.
