# Feature Plan 015 – Upload Watermark Toggle

_Linked specification:_ `docs/specs/4-architecture/features/015-upload-watermark-toggle/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-02-24

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:** Enable photographers to control watermarking on a per-upload basis, allowing them to upload photos without watermarks when needed (e.g., for personal/internal use) while maintaining watermarks as the default for public-facing content.

**Success Criteria:**
- Toggle appears in upload modal when watermarking is globally enabled and opt-out is allowed
- Admin can disable opt-out toggle via `watermark_optout_disabled` setting
- Toggle defaults to ON, respecting global watermark setting
- Photos uploaded with toggle OFF are not watermarked
- Photos uploaded with toggle ON (or by default) are watermarked
- Existing uploads without the parameter continue to work (backward compatibility)
- No measurable performance impact on upload process

## Scope Alignment

**In scope:**
- Add watermark toggle UI to UploadPanel.vue
- Add admin setting `watermark_optout_disabled` to control toggle visibility
- Add admin UI for `watermark_optout_disabled` setting in Mod Watermarker settings
- Extend UploadConfig to include `is_watermarker_enabled` and `can_watermark_optout` flags
- Add `apply_watermark` parameter to upload request/service
- Pass watermark flag through ProcessImageJob
- Modify ApplyWatermark pipe to respect the flag
- Feature tests for all scenarios
- Translations for toggle label and admin setting

**Out of scope:**
- Per-photo watermark toggle (all photos in session use same setting)
- Album-level watermark configuration
- Watermark appearance settings in upload modal
- Bulk watermark/unwatermark for existing photos (existing feature)

## Dependencies & Interfaces

| Dependency | Type | Impact |
|------------|------|--------|
| configs table | Database | Add `watermark_optout_disabled` config entry |
| UploadConfig.php | Resource | Add `is_watermarker_enabled` and `can_watermark_optout` computed properties |
| UploadPhotoRequest.php | Request | Add optional `apply_watermark` validation rule |
| ProcessImageJob.php | Job | Add `apply_watermark` parameter, pass to pipeline |
| ApplyWatermark.php | Pipe | Check flag before applying watermark |
| UploadPanel.vue | Component | Add toggle UI and state management |
| upload-service.ts | Service | Include `apply_watermark` in FormData |
| UploadingLine.vue | Component | Pass watermark flag to service |
| Settings admin UI | Component | Add toggle for `watermark_optout_disabled` |

## Assumptions & Risks

**Assumptions:**
- Watermarker class properly checks configuration and availability
- Existing upload chunking mechanism unchanged
- Toggle state managed at modal level, not per-file
- ProcessImageJob already has mechanism to configure pipeline

**Risks / Mitigations:**
| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Chunked uploads lose watermark flag | Medium | High | Pass flag in every chunk OR store in job context only for final chunk |
| Race condition with parallel uploads | Low | Medium | Toggle disabled during active uploads |
| Backward compatibility break | Low | High | Flag is optional, null = use global setting |

## Implementation Drift Gate

After completing all increments:
1. Run `php artisan test --filter=Upload` to verify upload tests pass
2. Run `php artisan test --filter=Watermark` to verify watermarking tests pass
3. Manually test upload with toggle ON/OFF
4. Verify existing uploads without parameter work unchanged
5. Record test results in tasks.md

## Increment Map

### I0 – Backend: Add watermark_optout_disabled config (~30 min)

_Goal:_ Add configuration setting to control whether users can opt out of watermarking.

_Preconditions:_ None

_Steps:_
1. Create migration to add `watermark_optout_disabled` config entry (default: 0/false)
2. Add to Configs model if needed
3. Add test for config retrieval
4. Run migration and tests

_Commands:_
- `php artisan migrate`
- `php artisan test --filter=Config`
- `make phpstan`
- `vendor/bin/php-cs-fixer fix`

_Exit:_ Config entry exists in database with default value false.

### I1 – Backend: Extend UploadConfig with watermarker status (~30 min)

_Goal:_ Add `is_watermarker_enabled` and `can_watermark_optout` properties to UploadConfig resource so frontend knows whether to show toggle.

_Preconditions:_ I0 complete

_Steps:_
1. Test first: Add feature test verifying both fields in response
2. Add `is_watermarker_enabled` property to `UploadConfig.php`
3. Compute based on: `watermark_enabled` config AND `watermark_photo_id` set AND Imagick available
4. Add `can_watermark_optout` property computed as: `is_watermarker_enabled` AND NOT `watermark_optout_disabled`
5. Run `php artisan test --filter=UploadConfig`

_Commands:_
- `php artisan test --filter=UploadConfig`
- `make phpstan`
- `vendor/bin/php-cs-fixer fix`

_Exit:_ UploadConfig response includes `is_watermarker_enabled` and `can_watermark_optout` booleans, TypeScript types regenerated.

### I2 – Backend: Add watermark flag to upload request (~30 min)

_Goal:_ Accept optional `apply_watermark` parameter in upload request.

_Preconditions:_ I1 complete

_Steps:_
1. Test first: Feature test for upload with `apply_watermark` true/false/missing
2. Add validation rule to `UploadPhotoRequest.php`: `'apply_watermark' => 'sometimes|boolean'`
3. Add accessor method to retrieve the value
4. Update `processValidatedValues` to capture parameter
5. Run tests

_Commands:_
- `php artisan test --filter=UploadPhotoRequest`
- `make phpstan`

_Exit:_ Request accepts and validates `apply_watermark` parameter.

### I3 – Backend: Pass watermark flag to ProcessImageJob (~45 min)

_Goal:_ Thread watermark flag from request through controller to job, with server-side enforcement of opt-out restriction.

_Preconditions:_ I2 complete

_Steps:_
1. Test first: Unit test for ProcessImageJob with `apply_watermark` parameter
2. Add `?bool $apply_watermark = null` parameter to ProcessImageJob constructor
3. Store in job property, serialize for queue
4. In PhotoController::upload, check `watermark_optout_disabled` config:
   - If true: ignore request's `apply_watermark` value, pass `null` to job (forces global setting)
   - If false: pass request's `apply_watermark` value to job
5. Update PhotoController::process method signature
6. Add test for enforcement: upload with `apply_watermark=false` when `watermark_optout_disabled=true` should still watermark
7. Run tests

_Commands:_
- `php artisan test --filter=ProcessImageJob`
- `php artisan test --filter=PhotoController`
- `make phpstan`

_Exit:_ ProcessImageJob receives watermark flag; server enforces opt-out restriction.

### I4 – Backend: ApplyWatermark pipe respects flag (~45 min)

_Goal:_ Modify ApplyWatermark pipe to check flag before applying watermark.

_Preconditions:_ I3 complete

_Steps:_
1. Test first: Unit test for ApplyWatermark with flag true/false/null
2. Receive `apply_watermark` flag in StandaloneDTO or via configuration
3. Modify ApplyWatermark::handle to check flag:
   - `false` → skip watermarking
   - `true` or `null` → apply watermark if globally enabled
4. Update pipeline configuration to pass flag
5. Run integration tests

_Commands:_
- `php artisan test --filter=ApplyWatermark`
- `php artisan test --filter=ProcessImageJob`
- `make phpstan`

_Exit:_ Photos uploaded with flag=false are not watermarked.

### I5 – Frontend: Extend UploadConfig TypeScript types (~15 min)

_Goal:_ Regenerate TypeScript types to include `is_watermarker_enabled` and `can_watermark_optout`.

_Preconditions:_ I1 complete

_Steps:_
1. Run TypeScript type generation
2. Verify `App.Http.Resources.GalleryConfigs.UploadConfig` includes `is_watermarker_enabled` and `can_watermark_optout`
3. No tests (type generation)

_Commands:_
- `php artisan typescript:transform`
- `npm run check`

_Exit:_ TypeScript types updated, build passes.

### I6 – Frontend: Add toggle to UploadPanel (~60 min)

_Goal:_ Add watermark toggle switch to upload modal UI.

_Preconditions:_ I5 complete

_Steps:_
1. Add `applyWatermark` ref in UploadPanel.vue, default to true
2. Add toggle switch using PrimeVue ToggleButton or InputSwitch
3. Show toggle only when `setup.value?.can_watermark_optout === true`
4. Disable toggle when `counts.value.files > 0 && counts.value.completed < counts.value.files`
5. Style toggle consistent with existing modal design
6. Add translation key for label

_Commands:_
- `npm run format`
- `npm run check`

_Exit:_ Toggle visible in upload modal when watermarking enabled.

### I7 – Frontend: Pass watermark flag in upload service (~30 min)

_Goal:_ Include watermark flag in upload request.

_Preconditions:_ I6 complete

_Steps:_
1. Extend UploadData type to include `apply_watermark?: boolean`
2. Update `upload-service.ts` to append `apply_watermark` to FormData
3. Update UploadingLine.vue to receive and pass watermark flag
4. Update UploadPanel.vue to pass `applyWatermark` value to UploadingLine

_Commands:_
- `npm run format`
- `npm run check`

_Exit:_ Upload requests include `apply_watermark` field.

### I8 – Translations (~30 min)

_Goal:_ Add translation strings for watermark toggle and admin setting.

_Preconditions:_ I6 complete

_Steps:_
1. Add to `lang/en/gallery.php`: `'upload.apply_watermark' => 'Apply watermark'`
2. Add to `lang/en/all_settings.php`: `'watermark_optout_disabled'` with description
3. Add to other language files (21 languages)
4. Verify translation works in UI

_Commands:_
- `npm run check`

_Exit:_ Toggle label and admin setting translated in all supported languages.

### I8b – Admin UI: Add watermark_optout_disabled setting (~30 min)

_Goal:_ Add toggle in admin settings to control watermark opt-out availability.

_Preconditions:_ I0, I8 complete

_Steps:_
1. Locate Mod Watermarker settings section in admin UI
2. Add toggle for `watermark_optout_disabled` setting
3. Label: "Disable watermark opt-out" or similar
4. Description: "When enabled, users cannot skip watermarking during upload"
5. Test toggle persists setting to database

_Commands:_
- `npm run format`
- `npm run check`

_Exit:_ Admin can toggle `watermark_optout_disabled` from settings UI.

### I9 – Integration testing and documentation (~45 min)

_Goal:_ End-to-end verification and documentation updates.

_Preconditions:_ I0-I8b complete

_Steps:_
1. Manual test: Upload with toggle ON, verify watermark applied
2. Manual test: Upload with toggle OFF, verify no watermark
3. Manual test: Upload with watermarking disabled, verify no toggle
4. Manual test: Enable `watermark_optout_disabled`, verify toggle hidden
5. Manual test: With `watermark_optout_disabled=true`, send API request with `apply_watermark=false`, verify photo is still watermarked (server-side enforcement)
6. Run full test suite
7. Update documentation

_Commands:_
- `php artisan test`
- `npm run check`
- `vendor/bin/php-cs-fixer fix`
- `make phpstan`

_Exit:_ All tests pass, feature complete.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-015-01 | I4 / T-015-09 | Default behavior: watermark applied |
| S-015-02 | I4, I7 / T-015-09, T-015-15 | Toggle OFF: no watermark |
| S-015-03 | I1, I6 / T-015-03, T-015-13 | Toggle hidden when watermarking disabled |
| S-015-04 | I1 / T-015-03 | Watermark image not configured |
| S-015-05 | I2, I4 / T-015-05, T-015-09 | Backward compatibility |
| S-015-06 | I6, I7 / T-015-13, T-015-15 | Session state persistence |
| S-015-07 | I6 / T-015-13 | Toggle disabled during upload |
| S-015-08 | I0, I1, I3, I8b / T-015-01, T-015-03, T-015-09b, T-015-21 | Admin disabled opt-out + server-side enforcement |

## Analysis Gate

To be completed after I1-I3 pass tests:
- [ ] TypeScript types correctly generated
- [ ] API response includes new field
- [ ] Request validation works as expected
- [ ] Job receives and stores flag correctly

## Exit Criteria

- [ ] Config entry `watermark_optout_disabled` exists with default false
- [ ] UploadConfig includes `is_watermarker_enabled` and `can_watermark_optout` booleans
- [ ] Upload endpoint accepts `apply_watermark` optional parameter
- [ ] Server-side enforcement: `apply_watermark=false` ignored when `watermark_optout_disabled=true`
- [ ] ProcessImageJob passes flag to processing pipeline
- [ ] ApplyWatermark pipe respects flag (false = skip, true/null = apply if enabled)
- [ ] Toggle visible in upload modal when `can_watermark_optout` is true
- [ ] Toggle hidden when admin sets `watermark_optout_disabled` to true
- [ ] Toggle default matches global setting (ON)
- [ ] Toggle disabled during active uploads
- [ ] Admin UI includes `watermark_optout_disabled` toggle
- [ ] Translations added for all 21 languages
- [ ] All feature tests pass
- [ ] PHPStan level 6 passes
- [ ] Frontend build passes
- [ ] Documentation updated

## Follow-ups / Backlog

- Consider per-photo watermark toggle in photo edit modal
- Consider album-level watermark default settings
- Add watermark preview in upload modal

---

*Last updated: 2026-02-24*
