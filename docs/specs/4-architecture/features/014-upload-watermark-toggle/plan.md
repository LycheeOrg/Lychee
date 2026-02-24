# Feature Plan 014 – Upload Watermark Toggle

_Linked specification:_ `docs/specs/4-architecture/features/014-upload-watermark-toggle/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-02-24

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:** Enable photographers to control watermarking on a per-upload basis, allowing them to upload photos without watermarks when needed (e.g., for personal/internal use) while maintaining watermarks as the default for public-facing content.

**Success Criteria:**
- Toggle appears in upload modal when watermarking is globally enabled
- Toggle defaults to ON, respecting global watermark setting
- Photos uploaded with toggle OFF are not watermarked
- Photos uploaded with toggle ON (or by default) are watermarked
- Existing uploads without the parameter continue to work (backward compatibility)
- No measurable performance impact on upload process

## Scope Alignment

**In scope:**
- Add watermark toggle UI to UploadPanel.vue
- Extend UploadConfig to include `is_watermarker_enabled` flag
- Add `apply_watermark` parameter to upload request/service
- Pass watermark flag through ProcessImageJob
- Modify ApplyWatermark pipe to respect the flag
- Feature tests for all scenarios
- Translations for toggle label

**Out of scope:**
- Per-photo watermark toggle (all photos in session use same setting)
- Album-level watermark configuration
- Watermark appearance settings in upload modal
- Bulk watermark/unwatermark for existing photos (existing feature)

## Dependencies & Interfaces

| Dependency | Type | Impact |
|------------|------|--------|
| UploadConfig.php | Resource | Add `is_watermarker_enabled` computed property |
| UploadPhotoRequest.php | Request | Add optional `apply_watermark` validation rule |
| ProcessImageJob.php | Job | Add `apply_watermark` parameter, pass to pipeline |
| ApplyWatermark.php | Pipe | Check flag before applying watermark |
| UploadPanel.vue | Component | Add toggle UI and state management |
| upload-service.ts | Service | Include `apply_watermark` in FormData |
| UploadingLine.vue | Component | Pass watermark flag to service |

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

### I1 – Backend: Extend UploadConfig with watermarker status (~30 min)

_Goal:_ Add `is_watermarker_enabled` property to UploadConfig resource so frontend knows whether to show toggle.

_Preconditions:_ None

_Steps:_
1. Test first: Add feature test verifying `is_watermarker_enabled` in response
2. Add `is_watermarker_enabled` property to `UploadConfig.php`
3. Compute based on: `watermark_enabled` config AND `watermark_photo_id` set AND Imagick available
4. Run `php artisan test --filter=UploadConfig`

_Commands:_
- `php artisan test --filter=UploadConfig`
- `make phpstan`
- `vendor/bin/php-cs-fixer fix`

_Exit:_ UploadConfig response includes `is_watermarker_enabled` boolean, TypeScript types regenerated.

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

_Goal:_ Thread watermark flag from request through controller to job.

_Preconditions:_ I2 complete

_Steps:_
1. Test first: Unit test for ProcessImageJob with `apply_watermark` parameter
2. Add `?bool $apply_watermark = null` parameter to ProcessImageJob constructor
3. Store in job property, serialize for queue
4. Pass from PhotoController::upload to ProcessImageJob::dispatch
5. Update PhotoController::process method signature
6. Run tests

_Commands:_
- `php artisan test --filter=ProcessImageJob`
- `make phpstan`

_Exit:_ ProcessImageJob receives and stores watermark flag.

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

_Goal:_ Regenerate TypeScript types to include `is_watermarker_enabled`.

_Preconditions:_ I1 complete

_Steps:_
1. Run TypeScript type generation
2. Verify `App.Http.Resources.GalleryConfigs.UploadConfig` includes `is_watermarker_enabled`
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
3. Show toggle only when `setup.value?.is_watermarker_enabled === true`
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

_Goal:_ Add translation strings for watermark toggle.

_Preconditions:_ I6 complete

_Steps:_
1. Add to `lang/en/gallery.php`: `'upload.apply_watermark' => 'Apply watermark'`
2. Add to other language files (21 languages)
3. Verify translation works in UI

_Commands:_
- `npm run check`

_Exit:_ Toggle label translated in all supported languages.

### I9 – Integration testing and documentation (~45 min)

_Goal:_ End-to-end verification and documentation updates.

_Preconditions:_ I1-I8 complete

_Steps:_
1. Manual test: Upload with toggle ON, verify watermark applied
2. Manual test: Upload with toggle OFF, verify no watermark
3. Manual test: Upload with watermarking disabled, verify no toggle
4. Run full test suite
5. Update documentation

_Commands:_
- `php artisan test`
- `npm run check`
- `vendor/bin/php-cs-fixer fix`
- `make phpstan`

_Exit:_ All tests pass, feature complete.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-014-01 | I4 / T-014-04 | Default behavior: watermark applied |
| S-014-02 | I4, I7 / T-014-04, T-014-07 | Toggle OFF: no watermark |
| S-014-03 | I1, I6 / T-014-01, T-014-06 | Toggle hidden when disabled |
| S-014-04 | I1 / T-014-01 | Watermark image not configured |
| S-014-05 | I2, I4 / T-014-02, T-014-04 | Backward compatibility |
| S-014-06 | I6, I7 / T-014-06, T-014-07 | Session state persistence |
| S-014-07 | I6 / T-014-06 | Toggle disabled during upload |

## Analysis Gate

To be completed after I1-I3 pass tests:
- [ ] TypeScript types correctly generated
- [ ] API response includes new field
- [ ] Request validation works as expected
- [ ] Job receives and stores flag correctly

## Exit Criteria

- [ ] UploadConfig includes `is_watermarker_enabled` boolean
- [ ] Upload endpoint accepts `apply_watermark` optional parameter
- [ ] ProcessImageJob passes flag to processing pipeline
- [ ] ApplyWatermark pipe respects flag (false = skip, true/null = apply if enabled)
- [ ] Toggle visible in upload modal when watermarking available
- [ ] Toggle default matches global setting (ON)
- [ ] Toggle disabled during active uploads
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
