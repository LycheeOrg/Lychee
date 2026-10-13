# Feature Plan 089 – Moderation History and NSFW Review

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Complete  
_Last updated:_ 2026-10-14

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Admins can tell when each photo was held and approved, and review NSFW findings across the library, one row per photo, deleting the wrong ones.
- Every write site of FR-089-02 and FR-089-03 records its event, filtered by the three settings (FR-089-04), with one insert per call (NFR-089-03).
- History and findings listings answer with a constant number of queries per page (NFR-089-02).
- v8 pages UI-089-01 … UI-089-03 work in the browser.

## Scope Alignment
- **In scope:** table, model, enums, settings; recorder service and its three call sites; five REST endpoints; v8 findings page, Moderation History tab, photo details section; lang keys; docs.
- **Out of scope:** v7; filters on the findings page; undo of a dismissal; album un-marking; dedup of re-scan detections.

## Dependencies & Interfaces
- Feature 033: `photos.is_validated`, `SetUploadValidated`, `ModerationController`, v8 `Moderation.vue`.
- Feature 045: `nsfw_detections`, `NsfwActionService`, `NsfwDetectionResource`, `usePhotoNsfwDetectionsStore`, `support:se` on NSFW admin endpoints.
- `PhotoAlbumResource` (album id and title), `PhotoBox` (in-place photo view), `PhotoDetails.vue` drawer.
- `BaseConfigMigration` and `configs.required_keys` ([settings_dependencies.md](../../../3-reference/settings_dependencies.md)).
- Tooling: `php artisan typescript:transform` regenerates `resources/js/lychee.d.ts` after resources change.

## Assumptions & Risks
- **Assumptions:** the worktree needs its own `vendor/` (copied, not symlinked: Composer's autoloader resolves `app/` relative to the real `vendor` path), `node_modules/` and an empty `database/database.sqlite`.
- **Risks / Mitigations:**
  - Upload hold recorded before the photo row exists → the recorder runs in a new last pipe of the standalone pipeline, after `Save` and `AutoScanNsfwOnUpload`.
  - `NsfwActionService` saves the photo in two places (end of `applyActions()`, `handleNoAlbumFallback()`) → record after the final `save()` from the computed outcome, not inside the helpers.
  - Approval must know which photos were hidden → select hidden ids per chunk before the update (one extra query per chunk).
  - Findings ordering on a grouped subquery across SQLite/MySQL/PostgreSQL → order by `MAX(nsfw_detections.id)`, a plain aggregate.

## Implementation Drift Gate
After the last task: map FR-089-01 … 15 and NFR-089-01 … 03 to classes and tests in a table here, rerun every test class listed in the tasks, `make phpstan`, `npm run check`, and record findings.

### Report – 2026-10-14
| Requirement | Implementation | Verification |
|-------------|----------------|--------------|
| FR-089-01 | migration `photo_moderation_events`, `PhotoModerationEvent`, `ModerationEvent`, `ModerationSource`, `PhotosToBeDeletedDTO` | `ModerationHistoryListTest` (photo/admin deletion) |
| FR-089-02 | `Standalone\RecordUploadHold` (both standalone pipelines), `NsfwActionService::recordOutcome()` | `ModerationHistoryUploadTest`, `ModerationHistoryNsfwTest` |
| FR-089-03 | `ModerationController::approvePhotos()`, `NsfwActionService::recordOutcome()` | `ModerationHistoryApproveTest`, `ModerationHistoryNsfwTest` |
| FR-089-04 | `ModerationHistory::shouldRecord()`, config migration | `ModerationHistoryPredicateTest`, settings tests in the three classes above |
| FR-089-05, 06 | `ListModerationHistoryRequest`, `GetModerationPhotoHistoryRequest`, `ModerationEventResource`, `PaginatedModerationHistoryResource`, `history()`, `photoHistory()` | `ModerationHistoryListTest` |
| FR-089-07 | `ModerationHistoryTable.vue`, tabs in `Moderation.vue` | browser check (History tab, thumbnail, phone width) |
| FR-089-08 | `PhotoModerationHistory.vue` in `PhotoDetails.vue` | browser check (admin section, non-admin hidden, no request) |
| FR-089-09 | `NsfwFindingsController::index()`, `ListNsfwFindingsRequest`, `NsfwFindingResource`, `PaginatedNsfwFindingsResource` | `NsfwFindingsListTest` |
| FR-089-10, 11 | `destroy()`, `destroyByPhotos()`, two delete requests | `NsfwFindingsDeleteTest` |
| FR-089-12 | `NsfwFindings.vue`, `nsfw-findings-service.ts`, `usePhotoNsfwDetectionsStore.forget()` | browser check (findings endpoints mocked: SE only) |
| FR-089-13 | `useAdminTiles.ts` tile, `paths.ts`, v8 `routes.ts`, `web_v2.php` | browser check (tile with mocked rights flag) |
| FR-089-14 | unchanged approval marking reads remaining rows | `NsfwFindingsDeleteTest::testApprovalAfterDismissingSensitiveFindingDoesNotMarkAlbums` |
| FR-089-15 | `handleNoAlbumFallback()` returns its outcome | `NsfwActionServiceTest`, `ModerationHistoryNsfwTest` |
| NFR-089-01 | admin-only `authorize()` on the five requests | 401/403 tests in the list, delete and history classes |
| NFR-089-02 | eager loading / three bulk queries per page | query-count tests in `ModerationHistoryListTest`, `NsfwFindingsListTest` |
| NFR-089-03 | one bulk insert per `record()` | insert count in `ModerationHistoryApproveTest` |

Gate run: 19 test classes green (feature classes plus `NsfwActionServiceTest`, `NsfwDetectionResultsTest`, `PhotoNsfwDetectionsTest`, `NsfwConfigControllerTest`, `BulkNsfwScanTest`, `ApplyNsfwAlbumSensitivityJobTest`, `AutoScanNsfwOnUploadTest`, `ModerationTest`, `UploadModerationFlowTest`, `LangTest`, `DeleteUserTest`, `Photo360UploadTest`); `vendor/bin/php-cs-fixer fix`, `make phpstan`, `npm run format`, `npm run check`, eslint on touched files clean; browser check 27/27. Findings: none blocking. The findings and tile checks run against mocked SE endpoints; a real SE instance check is left to the owner.

## Increment Map
1. **I0 – Worktree environment**
   - _Steps:_ copy `vendor/` from the main checkout, symlink `node_modules/`, copy `.env`, create `database/database.sqlite`; run one existing test class (`ModerationTest`) to prove the setup.
   - _Exit:_ the existing class is green.
2. **I1 – Schema, enums, model, settings** (FR-089-01, FR-089-04)
   - _Steps:_ migration `photo_moderation_events`; config migration for the three settings with `required_keys`; enums `ModerationEvent`, `ModerationSource`; model `PhotoModerationEvent`; `PhotosToBeDeletedDTO` deletes the events with the photos; lang `all_settings.php` (all locales); `settings_dependencies.md` row.
   - _Commands:_ `make phpstan`.
3. **I2 – Recorder** (FR-089-04, NFR-089-03)
   - _Steps:_ unit test for the pure predicate (red), then `ModerationHistory::shouldRecord()` and `record()` (one bulk insert, empty input writes nothing).
   - _Commands:_ `php artisan test --filter=ModerationHistoryPredicateTest`.
4. **I3 – Upload holds** (FR-089-02 (1); S-089-01 … 03)
   - _Steps:_ feature tests uploading as `check`, `monitor` with hide-on-scan, `trusted` (red); pipe `Standalone\RecordUploadHold` appended to the standalone pipeline.
   - _Commands:_ `php artisan test --filter=ModerationHistoryUploadTest`, `--filter=AutoScanNsfwOnUploadTest`.
5. **I4 – NSFW callback events** (FR-089-02 (2), FR-089-03 (2); S-089-04 … 08, S-089-12)
   - _Steps:_ tests through `POST NsfwDetection/results` (red); `NsfwActionService::applyActions()` records `held` when moderated, `approved` when a hidden non-`check` photo is restored.
   - _Commands:_ `php artisan test --filter=ModerationHistoryNsfwTest`, `--filter=NsfwActionServiceTest`, `--filter=NsfwDetectionResultsTest`.
6. **I5 – Admin approval events** (FR-089-03 (1); S-089-09 … 11)
   - _Steps:_ tests with query count (red); `approvePhotos()` selects hidden ids, updates, records once per chunk.
   - _Commands:_ `php artisan test --filter=ModerationHistoryApproveTest`, `--filter=ModerationTest`, `--filter=UploadModerationFlowTest`.
7. **I6 – History endpoints** (FR-089-05, FR-089-06; S-089-13 … 16)
   - _Steps:_ tests (red); requests, `ModerationEventResource`, `PaginatedModerationHistoryResource`, controller methods, routes.
   - _Commands:_ `php artisan test --filter=ModerationHistoryListTest`.
8. **I7 – Findings listing** (FR-089-09; S-089-17, S-089-18)
   - _Steps:_ tests with query count (red); `ListNsfwFindingsRequest`, `NsfwFindingResource`, `PaginatedNsfwFindingsResource`, `NsfwFindingsController::index`, route with `support:se`.
   - _Commands:_ `php artisan test --filter=NsfwFindingsListTest`.
9. **I8 – Findings deletion** (FR-089-10, FR-089-11, FR-089-14; S-089-18 … 22)
   - _Steps:_ tests (red); two requests, `destroy`, `destroyByPhotos`, routes.
   - _Commands:_ `php artisan test --filter=NsfwFindingsDeleteTest`.
10. **I9 – Frontend contracts**
    - _Steps:_ `php artisan typescript:transform`; `moderation-service.ts` (`history`, `photoHistory`), `nsfw-findings-service.ts`; `usePhotoNsfwDetectionsStore.forget()`; lang keys in `moderation.php` and `admin-dashboard.php` (all locales).
    - _Commands:_ `npm run check`.
11. **I10 – Findings page** (FR-089-12, FR-089-13; UI-089-01, S-089-23)
    - _Steps:_ `v8/views/admin/NsfwFindings.vue`, `paths.ts`, v8 `routes.ts`, web route, dashboard tile.
    - _Commands:_ `npm run format`, `npm run check`, eslint on touched files.
12. **I11 – Moderation History tab** (FR-089-07; UI-089-02, S-089-24)
    - _Steps:_ tabs on v8 `Moderation.vue`, History table component.
    - _Commands:_ `npm run format`, `npm run check`.
13. **I12 – Photo details section** (FR-089-08; UI-089-03, S-089-25)
    - _Steps:_ admin-only section in v8 `PhotoDetails.vue`.
    - _Commands:_ `npm run format`, `npm run check`.
14. **I13 – Docs, quality gate, browser check**
    - _Steps:_ roadmap, knowledge map, Feature 033/045 pointers; full quality gate; Playwright on a scratch instance for UI-089-01 … 03.

## Scenario Tracking
| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-089-01 … 03 | I3 / T-089-05, T-089-06 | `ModerationHistoryUploadTest` |
| S-089-04 … 08 | I4 / T-089-07, T-089-08 | `ModerationHistoryNsfwTest` |
| S-089-09 … 11 | I5 / T-089-09, T-089-10 | `ModerationHistoryApproveTest` |
| S-089-12 | I4 / T-089-07 | `ModerationHistoryNsfwTest` |
| S-089-13 … 16 | I6 / T-089-11, T-089-12 | `ModerationHistoryListTest` |
| S-089-17, 18 | I7 / T-089-13, T-089-14 | `NsfwFindingsListTest` |
| S-089-18 … 22 | I8 / T-089-15, T-089-16 | `NsfwFindingsDeleteTest` |
| S-089-23 | I10 / T-089-18, T-089-22 | browser check |
| S-089-24 | I11 / T-089-19, T-089-22 | browser check |
| S-089-25 | I12 / T-089-20, T-089-22 | browser check |

## Analysis Gate
2026-10-13, agent review:
1. Specification completeness: goals, FR-089-01 … 14, NFR-089-01 … 03, settings, scenarios populated; Q-089-01 … 05 folded into FRs, Non-Goals and UI; ASCII mock-ups for UI-089-01 … 03. Pass.
2. Open questions: none open. ADR-089-01 (history log) and ADR-089-02 (dismissal deletes) written and linked from the spec and open questions. Pass.
3. Plan alignment: plan links spec and tasks; dependencies and success criteria match FR/NFR wording. Pass.
4. Tasks coverage: FR-089-01 → T-089-02; 02 → T-089-05 … 08; 03 → T-089-07 … 10; 04 → T-089-02 … 04; 05, 06 → T-089-11, 12; 07 → T-089-19; 08 → T-089-20; 09 → T-089-13, 14; 10, 11 → T-089-15, 16; 12 → T-089-18; 13 → T-089-18; 14 → T-089-15; 15 → T-089-07, 08 (added with Q-089-06). Each backend increment has its red test task first; success, validation (422) and failure (401/402/403/404) branches listed. Pass.
5. Working agreements: no new dependency; explicit writes at each site (no model events); branching held in the pure `shouldRecord()` predicate; new tests in Feature_v3 or AssistedVision, none in Feature_v2; v8 only. ADRs referencing Feature 089: ADR-089-01, ADR-089-02 (reviewed). Pass.
6. Tooling: commands listed per increment and task. Pass.

## Exit Criteria
- T-089-01 … T-089-22 `[x]`.
- php-cs-fixer, phpstan, prettier, vue-tsc, eslint clean; every test class in the tasks green.
- `lychee.d.ts` regenerated; roadmap, knowledge map, settings dependencies, Feature 033/045 pointers updated.

## Intent Log
- 2026-10-13, session 2 ("continue working the last feature"): I3 done. `ModerationHistoryUploadTest` written red (2 failed, 2 passed), then `Standalone\RecordUploadHold` appended to `handleStandalone` and to the standalone pipes of `handlePhotoLivePartner` (that path also runs `SetUploadValidated`, so a `check` live photo is held there too; FR-089-02 and DO-089-05 reworded). Duplicates never run `SetUploadValidated` and need no pipe. Commands: `php artisan test --filter=ModerationHistoryUploadTest|AutoScanNsfwOnUploadTest|UploadModerationFlowTest|PhotoAddTest|Photo360UploadTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`. `PhotoAddTest` Apple live photo tests fail identically on the untouched tree.
- 2026-10-13, session 2: I4 done. `ModerationHistoryNsfwTest` (12 tests) written red through `POST NsfwDetection/results`; `NsfwActionService::applyActions()` notes `is_validated` on entry and calls `recordOutcome()` after the final save (`held` when moderated, `approved` when a hidden photo became visible). The no-album fallback test exposed a Feature 045 defect (fallback undone by the clean-result branch), logged as Q-089-06; owner chose A: fixed here as FR-089-15 with two `NsfwActionServiceTest` tests (skip, moderate). Commands: `php artisan test --filter=ModerationHistoryNsfwTest|NsfwActionServiceTest|NsfwDetectionResultsTest|BulkNsfwScanTest|ApplyNsfwAlbumSensitivityJobTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
- 2026-10-13, session 2: I5 done. `ModerationHistoryApproveTest` (4 tests, insert count via `DB::listen`) written red; `approvePhotos()` now selects the hidden ids of the chunk, updates only those and records them with the admin id in one insert. Commands: `php artisan test --filter=ModerationHistoryApproveTest|ModerationTest|UploadModerationFlowTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
- 2026-10-13, session 2: I6 done. `ModerationHistoryListTest` (10 tests) written red; `ListModerationHistoryRequest` (`page`, `per_page` 1–100 required), `GetModerationPhotoHistoryRequest`, `ModerationEventResource` (enum-typed `event`/`source`), `PaginatedModerationHistoryResource`, `ModerationController::history()` (eager `photo.size_variants`, `photo.owner`, `user`) and `photoHistory()` (photo loaded once, set as relation), routes `Moderation::history`, `Moderation::photoHistory`. The query-count test warms the config cache and includes an admin event in both runs so it compares like with like. `lychee.d.ts` regeneration deferred to T-089-17. Commands: `php artisan test --filter=ModerationHistoryListTest|ModerationTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
- 2026-10-14, session 2 (owner: "Implement all increments in one go successively"): I7 done. `NsfwFindingsListTest` (8 tests) red, then `ListNsfwFindingsRequest`, `NsfwFindingResource`, `PaginatedNsfwFindingsResource`, `NsfwFindingsController::index()` (page of `photo_id`, `MAX(id)` grouped on `nsfw_detections`; then photos with `size_variants`, `owner`, `albums`; then detections ordered by confidence), route `GET NsfwDetection/findings` with `support:se`. Album titles sorted in PHP (natural, case-insensitive).
- 2026-10-14, session 2: I8 done. `NsfwFindingsDeleteTest` (5 tests, incl. S-089-22 approval without album marking) red, then `DeleteNsfwFindingsRequest` (`detection_ids` 1–500 integers ≥ 1), `DeleteNsfwFindingsByPhotosRequest` (`photo_ids` 1–500 random ids), `destroy()`, `destroyByPhotos()`, two DELETE routes with `support:se`. Full backend set (14 classes) green, `php-cs-fixer`, `make phpstan` clean.
- 2026-10-14, session 2: I9 done. `php artisan typescript:transform` (38 lines in `lychee.d.ts`, enums typed); `ModerationService.history()`/`photoHistory()`, `nsfw-findings-service.ts`, `usePhotoNsfwDetectionsStore.forget()`; 26 keys in `moderation.php` and `nsfw_findings.title` in `admin-dashboard.php` for all 23 locales (English, as for earlier NSFW keys). `LangTest` green.
- 2026-10-14, session 2: I10–I12 done. `NsfwFindings.vue` (chips coloured by most severe tier, chip dismiss at once, row and bulk dismissal through `useConfirmDialog`), route/path/web route/tile; `ModerationHistoryTable.vue` as the History tab of `Moderation.vue` (`UTabs`, Pending content unchanged); `PhotoModerationHistory.vue` in `PhotoDetails.vue` (admin only, fetched once per photo while open); shared labels in `v8/utils/moderationEvents.ts`. `npm run format`, `npm run check`, eslint clean.
- 2026-10-14, session 2: I13 done. Scratch instance in the session scratchpad (`serve.sh` port 8189, uploads on 8190, `run.sh` restores `db.seeded.sqlite`, `moderation-history.mjs`): carol (check) uploads 3 photos through the real pipeline, admin approves one; 27/27 checks (History tab, thumbnail, details section admin/non-admin, tile, findings page chip/row/bulk dismissal with confirmation, album link, empty state, phone width, no console errors). Thumbnails got `min-w-16` after the phone screenshot showed them squeezed. Docs: knowledge map section, roadmap, Feature 033/045 pointers, drift gate report.

## Follow-ups / Backlog
- `NsfwActionService::logDetections()` appends on re-scan, so a forced re-scan duplicates findings (out of scope).
- Infinite scroll after removing rows (findings page, and the existing Moderation Pending tab): the next page is fetched by page number, so rows that moved up after a removal can be skipped until reload.
- v7 gets a placeholder for `/admin/nsfw-findings` (shared `paths.ts`; v8 only by Q-089-05).
- Owner check on a real SE instance with the NSFW classifier.
