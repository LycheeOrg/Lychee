# Feature 016 Tasks – Bulk License Edit

_Status: Draft_  
_Last updated: 2026-02-26_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Backend Implementation

- [x] T-016-01 – Create SetPhotosLicenseRequest class (FR-016-03, FR-016-04, S-016-03).  
  _Intent:_ Implement request validation and authorization for bulk license updates.  
  _Files:_ `app/Http/Requests/Photo/SetPhotosLicenseRequest.php`  
  _Verification commands:_  
  - `make phpstan` - No type errors  
  _Notes:_ Use HasPhotosTrait, HasLicenseTrait, AuthorizeCanEditPhotosTrait patterns from SetPhotosTagsRequest. ✓ Completed - PHPStan passes.

- [x] T-016-02 – Write feature tests for SetPhotosLicenseRequest (NFR-016-04, S-016-03, S-016-04).  
  _Intent:_ Create comprehensive test coverage for validation and authorization scenarios.  
  _Files:_ `tests/Feature_v2/Photo/PhotoBulkLicenseTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBulkLicenseTest` - Tests created (will fail until controller implemented)  
  _Notes:_ Test: unauthorized (401), forbidden (403), validation errors (422), invalid IDs (404). Use BaseApiWithDataTest fixtures. ✓ Completed - 7 tests created, all passing.

- [x] T-016-03 – Implement PhotoController::license() method (FR-016-03, FR-016-06, S-016-01, S-016-02).  
  _Intent:_ Handle bulk license updates with transaction-wrapped chunked processing.  
  _Files:_ `app/Http/Controllers/Gallery/PhotoController.php`  
  _Verification commands:_  
  - `make phpstan` - No type errors  
  - `php artisan test --filter=PhotoBulkLicenseTest` - Tests should start passing  
  _Notes:_ Wrap in DB::transaction(). Process in chunks of 100 photos using chunkById(100, ...). Use Laravel batch() helper for each chunk. Pattern similar to RenamerController. ✓ Completed - Implemented with chunked processing.

- [x] T-016-04 – Register Photo::license route (API-016-01).  
  _Intent:_ Expose bulk license endpoint via REST API.  
  _Files:_ `routes/api_v2.php`  
  _Verification commands:_  
  - `php artisan route:list | grep "Photo::license"` - Route exists  
  - `php artisan test --filter=PhotoBulkLicenseTest` - All backend tests pass  
  _Notes:_ PATCH method, auth:api middleware. ✓ Completed - Route registered.

- [x] T-016-05 – Run backend quality gates (NFR-016-02).  
  _Intent:_ Ensure PHP code follows Lychee conventions and passes static analysis.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix` - Apply formatting  
  - `php artisan test` - All tests pass  
  - `make phpstan` - PHPStan level 6, no errors  
  _Notes:_ Verify license headers, snake_case, strict comparison, no empty(). ✓ Completed - All quality gates pass.

### Frontend Implementation

- [x] T-016-06 – Add PhotoService.license() method (API-016-01).  
  _Intent:_ Create service method for API communication.  
  _Files:_ `resources/js/services/photo-service.ts`  
  _Verification commands:_  
  - `npm run check` - TypeScript compiles  
  - `npm run format` - Formatting applied  
  _Notes:_ Method signature: `license(photo_ids: string[], license: App.Enum.LicenseType): Promise<AxiosResponse>`. ✓ Completed.

- [x] T-016-07 – Create PhotoLicenseDialog component structure (FR-016-01, UI-016-01).  
  _Intent:_ Build dialog component scaffold with props, emits, and template.  
  _Files:_ `resources/js/components/forms/photo/PhotoLicenseDialog.vue`  
  _Verification commands:_  
  - `npm run check` - TypeScript compiles  
  - `npm run format` - Formatting applied  
  _Notes:_ Props: parentId, photo?, photoIds?. Emit: licensed. Use PrimeVue Dialog, Dropdown, Button. Match PhotoTagDialog structure. ✓ Completed.

- [x] T-016-08 – Implement license dropdown with all options (FR-016-02).  
  _Intent:_ Populate dropdown with all 31 LicenseType enum values.  
  _Files:_ `resources/js/components/forms/photo/PhotoLicenseDialog.vue`  
  _Verification commands:_  
  - `npm run check` - TypeScript compiles  
  - Manual testing: Open dialog, verify all license types appear  
  _Notes:_ Default to "None", format for PrimeVue (label/value pairs), match LicenseType.localized(). ✓ Completed - Uses licenseOptions from constants.

- [x] T-016-09 – Implement dialog logic (submit, cancel, validation) (S-016-01, S-016-02, S-016-07).  
  _Intent:_ Connect dialog UI to PhotoService, handle success/error states.  
  _Files:_ `resources/js/components/forms/photo/PhotoLicenseDialog.vue`  
  _Verification commands:_  
  - `npm run check` - TypeScript compiles  
  - Manual testing: Submit updates licenses, cancel closes dialog  
  _Notes:_ execute() calls PhotoService.license(), close() resets state. ✓ Completed.

- [x] T-016-10 – Add success/error feedback (FR-016-05).  
  _Intent:_ Display toasts and clear cache on success.  
  _Files:_ `resources/js/components/forms/photo/PhotoLicenseDialog.vue`  
  _Verification commands:_  
  - Manual testing: Verify toast appears with correct count, view refreshes  
  _Notes:_ Toast: "License updated for X photos". Call AlbumService.clearCache(parentId). ✓ Completed.

- [x] T-016-11 – Integrate dialog into gallery selection menu (FR-016-01).  
  _Intent:_ Add "Set License" action to photo selection context menu.  
  _Files:_ Gallery selection menu component (TBD during implementation)  
  _Verification commands:_  
  - `npm run check` - TypeScript compiles  
  - Manual testing: Select photos, verify "Set License" action appears  
  _Notes:_ Import PhotoLicenseDialog, add menu item, bind v-model, pass props, handle @licensed event. ✓ Completed - Integrated in Album, Tag, Timeline, Search views + context menus.

- [x] T-016-12 – Add translation strings for English (I10).  
  _Intent:_ Add dialog and toast translations.  
  _Files:_ `lang/en/dialogs.php`  
  _Verification commands:_  
  - `grep "photo_license" lang/en/dialogs.php` - Strings exist  
  - Manual testing: Verify dialog displays correct English text  
  _Notes:_ Add: question, question_multiple, set_license, updated, select_license, replace_warning. ✓ Completed.

- [x] T-016-13 – Add translation string placeholders for other languages.  
  _Intent:_ Add English placeholders to other 21 language files.  
  _Files:_ `lang/*/dialogs.php` (22 languages total)  
  _Verification commands:_  
  - `grep -r "photo_license" lang/` - Strings exist in all language files  
  _Notes:_ Copy English strings as placeholders (to be translated later by community). ✓ Completed - 22 languages updated.

- [x] T-016-14 – Run frontend quality gates (NFR-016-03).  
  _Intent:_ Ensure Vue/TypeScript code follows Lychee conventions.  
  _Verification commands:_  
  - `npm run format` - Apply formatting  
  - `npm run check` - All checks pass  
  _Notes:_ Verify: template-first, composition API, regular functions (not arrow), .then() not async/await. ✓ Completed.

### Testing & Validation

- [ ] T-016-15 – Test single photo license update (S-016-01).  
  _Intent:_ Verify bulk license works for single photo.  
  _Verification commands:_  
  - Manual testing: Select 1 photo, set license, verify update and toast  
  _Notes:_ Test with various license types (None, Reserved, CC-BY-4.0).

- [ ] T-016-16 – Test multiple photos license update (S-016-02).  
  _Intent:_ Verify bulk license updates multiple photos correctly.  
  _Verification commands:_  
  - Manual testing: Select 10 photos, set license, verify all updated  
  _Notes:_ Check all 10 photos show new license in edit dialog.

- [ ] T-016-17 – Test authorization scenarios (S-016-03).  
  _Intent:_ Verify unauthorized/forbidden access properly blocked.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBulkLicenseTest` - Authorization tests pass  
  - Manual testing: Login as userNoUpload, verify cannot set license on others' photos  
  _Notes:_ Test: no auth (401), no permission (403).

- [ ] T-016-18 – Test validation errors (S-016-04).  
  _Intent:_ Verify invalid inputs return proper errors.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoBulkLicenseTest` - Validation tests pass  
  _Notes:_ Test: missing photo_ids, invalid photo IDs, missing license, invalid license value.

- [ ] T-016-19 – Test performance with 100+ photos (NFR-016-01, S-016-06).  
  _Intent:_ Verify bulk update completes in acceptable time with chunked processing.  
  _Verification commands:_  
  - Manual testing with timer: Select 100 photos, set license, measure time  
  - Optional: Test with 500+ photos to verify chunking works efficiently  
  _Notes:_ Should complete in <5 seconds for 100 photos. Chunked processing (100 per chunk) prevents memory issues with larger batches.

- [ ] T-016-20 – Test edge cases (S-016-05, S-016-07).  
  _Intent:_ Verify error handling and edge case behaviors.  
  _Verification commands:_  
  - Manual testing: Cancel dialog (no changes), test with network error simulation  
  _Notes:_ Transaction rollback tested via unit tests. Cancel should close dialog without API call.

### Documentation & Completion

- [ ] T-016-21 – Update photo management documentation.  
  _Intent:_ Document bulk license feature in user guides.  
  _Files:_ Relevant documentation files in `docs/`  
  _Verification commands:_  
  - `grep -r "bulk license" docs/` - Feature documented  
  _Notes:_ Explain how to select multiple photos and set license.

- [ ] T-016-22 – Update API documentation.  
  _Intent:_ Document Photo::license endpoint.  
  _Files:_ API reference documentation  
  _Verification commands:_  
  - Review API docs for Photo::license endpoint  
  _Notes:_ Include request/response examples, parameter descriptions.

- [ ] T-016-23 – Run final full quality gate (NFR-016-02, NFR-016-03, NFR-016-04).  
  _Intent:_ Verify entire feature meets all quality standards.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix` - PHP formatting applied  
  - `php artisan test` - All tests pass (including new bulk license tests)  
  - `make phpstan` - PHPStan level 6, no errors  
  - `npm run format` - Frontend formatting applied  
  - `npm run check` - All frontend checks pass  
  _Notes:_ All scenarios from spec must pass before marking complete.

- [ ] T-016-24 – Update roadmap and mark feature complete.  
  _Intent:_ Record feature completion in roadmap.  
  _Files:_ `docs/specs/4-architecture/roadmap.md`  
  _Verification commands:_  
  - Review roadmap entry for Feature 016  
  _Notes:_ Move from Active to Completed, add completion date and summary.

## Notes / TODOs

- None yet. Add notes here as issues arise during implementation.

## Progress Summary

- **Total tasks:** 24
- **Completed:** 0
- **In progress:** 0
- **Blocked:** 0

---

*Last updated: 2026-02-26*
