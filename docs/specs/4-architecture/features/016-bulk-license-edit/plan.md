# Feature Plan 016 – Bulk License Edit

_Linked specification:_ `docs/specs/4-architecture/features/016-bulk-license-edit/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-02-26

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Enable users to efficiently update the license field for multiple photos simultaneously through a consistent, easy-to-use interface. Success is measured by:
- Functional bulk license endpoint with proper authorization
- UI dialog matching existing bulk operation patterns (tags, move)
- All tests passing (authorization, validation, batch update)
- Performance acceptable for typical batch sizes (up to 100 photos in <5s)
- Code compliant with Lychee PHP and Vue3 conventions

## Scope Alignment

**In scope:**
- Backend request/controller for bulk license updates
- Frontend dialog component for license selection
- Integration with gallery selection context menu
- Authorization checks (PhotoPolicy::CAN_EDIT)
- Validation for photo IDs and license enum values
- Transaction-wrapped batch update
- Success/error feedback via toasts
- Feature tests for all scenarios

**Out of scope:**
- License history or audit trail
- Custom license types (use existing LicenseType enum)
- Per-photo license within bulk dialog
- Album-level default licenses
- Handling >1000 photos in single batch
- License compatibility validation

## Dependencies & Interfaces

**Backend:**
- `App\Enum\LicenseType` - Existing enum with all license values
- `App\Policies\PhotoPolicy` - Existing policy with CAN_EDIT gate
- `App\Models\Photo` - Photo model with license field
- `App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait` - Authorization trait
- Laravel's batch update helper

**Frontend:**
- PrimeVue Dialog, Dropdown, Button components
- `PhotoService` - Extended with license() method
- `AlbumService.clearCache()` - Cache invalidation
- Gallery selection state/context menu

**Testing:**
- `BaseApiWithDataTest` - Test base class with fixtures
- In-memory SQLite database
- Existing photo fixtures (photo1, photo2, album1)

## Assumptions & Risks

**Assumptions:**
- Photo owners can always edit their photos' licenses
- Users with album edit permission can edit photo licenses
- All 31 LicenseType enum values are valid choices
- No UI limit on batch size; backend processes in chunks of 100

**Risks & Mitigations:**
- **Risk:** Large batch updates (>1000 photos) may timeout or consume excessive memory
  - **Mitigation:** Process in chunks of 100 photos per iteration; use batch() helper for efficient queries
- **Risk:** UI refresh may not reflect changes immediately
  - **Mitigation:** Clear album cache and emit event for parent to refresh

## Implementation Drift Gate

Before marking complete:
1. Run full quality gate: `php artisan test`, `make phpstan`, `npm run check`
2. Verify all 7 scenarios from Branch & Scenario Matrix pass
3. Confirm UI matches mock-ups (dialog structure, dropdown, toasts)
4. Test with 1, 10, and 100 photos for performance
5. Run frontend component tests

Evidence recorded in tasks.md verification notes for each increment.

## Increment Map

### I1 – Backend Request Class (FR-016-03, FR-016-04)

**Goal:** Create SetPhotosLicenseRequest to validate and authorize bulk license updates.

**Preconditions:** 
- Existing Photo model with license field
- PhotoPolicy with CAN_EDIT gate
- AuthorizeCanEditPhotosTrait available

**Steps:**
1. Create `app/Http/Requests/Photo/SetPhotosLicenseRequest.php`
2. Implement HasPhotos, HasLicense interfaces
3. Use HasPhotosTrait, HasLicenseTrait, AuthorizeCanEditPhotosTrait
4. Define validation rules:
   - `photo_ids`: required, array, min:1
   - `photo_ids.*`: required, valid RandomIDRule
   - `license`: required, Enum(LicenseType)
5. Implement processValidatedValues() to load photos and license

**Commands:**
- `make phpstan` - Verify no type errors
- Tests added in I2

**Exit:** Request class created, passes PHPStan, ready for controller integration.

---

### I2 – Backend Request Tests (NFR-016-04, S-016-03, S-016-04)

**Goal:** Write feature tests for SetPhotosLicenseRequest validation and authorization.

**Preconditions:** I1 complete.

**Steps:**
1. Create `tests/Feature_v2/Photo/PhotoBulkLicenseTest.php` extending BaseApiWithDataTest
2. Test unauthorized access (no auth): returns 401
3. Test forbidden access (userNoUpload on photo1): returns 403
4. Test validation failures:
   - Missing photo_ids: 422
   - Empty photo_ids array: 422
   - Invalid photo ID: 404
   - Missing license: 422
   - Invalid license value: 422
5. Tests expected to fail (endpoint doesn't exist yet)

**Commands:**
- `php artisan test --filter=PhotoBulkLicenseTest` - Run tests (expected failures)

**Exit:** Test file created with 7+ test methods, all currently red (endpoint missing).

---

### I3 – Backend Controller Method (FR-016-03, FR-016-06, S-016-01, S-016-02)

**Goal:** Implement PhotoController::license() to handle bulk license updates with chunked processing.

**Preconditions:** I1, I2 complete.

**Steps:**
1. Add method in `app/Http/Controllers/Gallery/PhotoController.php`:
   ```php
   public function license(SetPhotosLicenseRequest $request): void
   ```
2. Extract photos and license from request
3. Wrap update in DB::transaction():
   - Process photos in chunks of 100 using Photo::query()->whereIn(...)->chunkById(100, ...)
   - Use Laravel batch() helper for each chunk
   - Update license field for all photo IDs
4. Commit transaction

**Commands:**
- `make phpstan` - Verify no type errors
- `php artisan test --filter=PhotoBulkLicenseTest` - Tests should start passing

**Exit:** Controller method implemented with chunked processing, basic tests passing, transaction ensures atomicity.

---

### I4 – Backend Route Registration (API-016-01)

**Goal:** Register route for Photo::license endpoint.

**Preconditions:** I3 complete.

**Steps:**
1. Add route in `routes/api.php` (or appropriate route file):
   ```php
   Route::patch('Photo::license', [PhotoController::class, 'license'])
       ->middleware('auth:api');
   ```
2. Verify route exists: `php artisan route:list | grep "Photo::license"`

**Commands:**
- `php artisan route:list | grep Photo` - Confirm route registered
- `php artisan test --filter=PhotoBulkLicenseTest` - All backend tests should pass

**Exit:** Route registered, all backend tests green.

---

### I5 – Frontend Service Method (API-016-01)

**Goal:** Add license() method to PhotoService for API calls.

**Preconditions:** I4 complete.

**Steps:**
1. Open `resources/js/services/photo-service.ts`
2. Add method:
   ```typescript
   license(photo_ids: string[], license: App.Enum.LicenseType): Promise<AxiosResponse> {
       return axios.patch(`${Constants.getApiUrl()}Photo::license`, { photo_ids, license });
   }
   ```
3. Run `npm run check` to verify TypeScript compilation

**Commands:**
- `npm run check` - Verify TS compiles
- `npm run format` - Apply formatting

**Exit:** Service method added, TypeScript compiles, formatted.

---

### I6 – Frontend Dialog Component (FR-016-01, FR-016-02, UI-016-01, S-016-07)

**Goal:** Create PhotoLicenseDialog.vue component for bulk license editing.

**Preconditions:** I5 complete.

**Steps:**
1. Create `resources/js/components/forms/photo/PhotoLicenseDialog.vue`
2. Define props: parentId, photo?, photoIds?
3. Define emits: licensed
4. Add template with:
   - Dialog from PrimeVue
   - Dropdown with license options (from LicenseType.localized())
   - Question text (single photo vs multiple)
   - Cancel and "Set License" buttons
5. Implement script:
   - Computed question text
   - Selected license ref
   - close() function
   - execute() function calling PhotoService.license()
   - Success toast and emit 'licensed' event
6. Match PhotoTagDialog.vue structure/style

**Commands:**
- `npm run check` - Verify TS compiles
- `npm run format` - Apply formatting

**Exit:** Dialog component created, structurally complete, ready for integration.

---

### I7 – UI Integration (FR-016-01, FR-016-05)

**Goal:** Add "Set License" action to gallery selection context menu.

**Preconditions:** I6 complete.

**Steps:**
1. Locate gallery selection menu component (likely in `resources/js/components/`)
2. Add PhotoLicenseDialog component import and registration
3. Add "Set License" menu item alongside "Set Tags"
4. Add v-model binding for dialog visibility
5. Pass selected photoIds and parentId as props
6. Handle @licensed event to refresh view
7. Ensure action only visible when photos selected

**Commands:**
- `npm run check` - Verify TS compiles
- Manual testing in browser

**Exit:** "Set License" action appears in selection menu, clicking opens dialog.

---

### I8 – License Dropdown Population (FR-016-02)

**Goal:** Populate license dropdown with all LicenseType options.

**Preconditions:** I6 complete.

**Steps:**
1. In PhotoLicenseDialog.vue, create computed/ref for license options
2. Fetch options from backend or hardcode matching LicenseType.localized()
3. Format for PrimeVue Dropdown (label/value pairs)
4. Verify all 31 license types appear in dropdown
5. Set default value to "None"

**Commands:**
- `npm run check` - Verify TS compiles
- Manual testing: open dialog, verify dropdown has all licenses

**Exit:** Dropdown shows all 31 license types, correctly formatted.

---

### I9 – Success Feedback (FR-016-05, S-016-01, S-016-02)

**Goal:** Display success toast and refresh view after license update.

**Preconditions:** I7, I8 complete.

**Steps:**
1. In PhotoLicenseDialog execute() method, on success:
   - Show toast: "License updated for X photos" (use sprintf for count)
   - Call AlbumService.clearCache(parentId)
   - Emit 'licensed' event
   - Close dialog
2. Add error handling for failed requests (show error toast)

**Commands:**
- `npm run check` - Verify TS compiles
- Manual testing: update license, verify toast appears and view refreshes

**Exit:** Success toast shows correct message, view refreshes with updated license.

---

### I10 – Translation Strings

**Goal:** Add translation strings for dialog and toasts.

**Preconditions:** I6 complete.

**Steps:**
1. Open `lang/en/dialogs.php`
2. Add section for photo_license:
   ```php
   'photo_license' => [
       'question' => 'Set license for photo',
       'question_multiple' => 'Set license for %d photos',
       'set_license' => 'Set License',
       'updated' => 'License updated',
       'select_license' => 'Select the license to apply:',
       'replace_warning' => 'This will replace the existing license for all selected photos.',
   ],
   ```
3. Update other 21 language files with same structure (English text as placeholder)
4. Run `php artisan lang:js` if applicable

**Commands:**
- `grep -r "photo_license" lang/` - Verify strings exist
- Manual testing: verify dialog displays translated strings

**Exit:** Translation strings added for English, placeholders for other languages.

---

### I11 – End-to-End Testing and Performance Validation (NFR-016-01, S-016-06)

**Goal:** Validate full feature flow and performance with various photo counts.

**Preconditions:** All previous increments complete.

**Steps:**
1. Test with 1 photo:
   - Select single photo
   - Open "Set License" dialog
   - Choose license, submit
   - Verify license updated, toast shown
2. Test with 10 photos:
   - Select 10 photos
   - Set license to "CC-BY-4.0"
   - Verify all 10 photos updated
3. Test with 100 photos (if available):
   - Measure update time (should be <5s)
4. Test unauthorized user:
   - Login as userNoUpload
   - Verify cannot set license on photos they don't own
5. Test edge cases:
   - Cancel dialog (no changes)
   - Invalid license value (via API direct call)
   - Network error handling

**Commands:**
- Manual browser testing
- `php artisan test` - All tests passing
- Performance measurement via browser dev tools

**Exit:** All scenarios verified, performance acceptable, edge cases handled.

---

### I12 – Documentation Updates

**Goal:** Document the new bulk license feature.

**Preconditions:** Feature functionally complete.

**Steps:**
1. Update photo management user documentation
2. Add API endpoint documentation for Photo::license
3. Update changelog/release notes

**Commands:**
- Review generated docs for accuracy
- `grep -r "bulk license" docs/` - Verify mentions exist

**Exit:** Documentation complete and accurate.

---

### I13 – Final Quality Gate (NFR-016-02, NFR-016-03)

**Goal:** Run full quality gate and verify all standards met.

**Preconditions:** All previous increments complete.

**Steps:**
1. Backend quality checks:
   - `vendor/bin/php-cs-fixer fix` - Apply PHP formatting
   - `php artisan test` - All tests pass
   - `make phpstan` - PHPStan level 6, no errors
2. Frontend quality checks:
   - `npm run format` - Apply formatting
   - `npm run check` - All checks pass
3. Code review checklist:
   - License headers present
   - No `empty()` usage
   - Strict comparisons (===)
   - snake_case variables
   - PSR-4 compliance
4. Verify all test scenarios from spec pass

**Commands:**
- `vendor/bin/php-cs-fixer fix` 
- `php artisan test`
- `make phpstan`
- `npm run format`
- `npm run check`

**Exit:** All quality gates pass, code ready for commit.

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-016-01 | I3, I9, I11 | Single photo license update |
| S-016-02 | I3, I9, I11 | Multiple photos license update |
| S-016-03 | I2, I11 | Authorization checks (403 Forbidden) |
| S-016-04 | I2, I11 | Validation errors (422) |
| S-016-05 | I3 | Transaction rollback handled by Laravel |
| S-016-06 | I11 | Performance testing with 100 photos |
| S-016-07 | I6, I11 | Cancel dialog functionality |

## Analysis Gate

**Status:** Not yet executed.

**Planned execution:** After spec review, before I1.

**Checklist:**
- [ ] All requirements in spec are testable
- [ ] UI mock-ups reviewed and approved
- [ ] Dependencies verified available
- [ ] Risk mitigations documented
- [ ] Test strategy covers all scenarios

## Exit Criteria

- [ ] All 13 increments complete
- [ ] All backend tests passing (unauthorized, forbidden, validation, batch update)
- [ ] Frontend dialog renders correctly with all license options
- [ ] Bulk license update works for 1, 10, 100 photos
- [ ] Performance acceptable (<5s for 100 photos)
- [ ] Success/error toasts display correctly
- [ ] Translation strings added
- [ ] Documentation updated
- [ ] PHPStan level 6 passes
- [ ] php-cs-fixer applied
- [ ] npm run check passes
- [ ] Code follows all Lychee conventions

## Follow-ups / Backlog

- Consider adding license to bulk rename operations
- Potential future enhancement: Album-level default license settings
- Potential future enhancement: License compatibility warnings (e.g., changing from restrictive to permissive)
- Monitor performance with chunked processing (100 photos per chunk) for very large batches (>1000 photos) in production

---

*Last updated: 2026-02-26*
