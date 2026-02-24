# Feature 015 Tasks – Upload Watermark Toggle

_Status: Draft_  
_Last updated: 2026-02-24_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I0 – Backend: Add watermark_optout_disabled config

- [x] T-015-01 – Create migration for `watermark_optout_disabled` config (FR-015-07, S-015-08).  
  _Intent:_ Add config entry to `configs` table with default value 0 (false).  
  _Verification commands:_  
  - `php artisan migrate`  
  - `php artisan test --filter=Config`  
  _Notes:_ Category: Mod Watermarker. Type string: 0 (bool). Description: "Disable watermark opt-out during upload".

- [x] T-015-02 – Verify config value can be read and updated.  
  _Intent:_ Ensure Configs::getValueAsBool() works for new config.  
  _Verification commands:_  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ Default false = opt-out available.

### Increment I1 – Backend: Extend UploadConfig with watermarker status

- [x] T-015-03 – Add feature test for UploadConfig watermarker status (FR-015-04, FR-015-08, S-015-03, S-015-04, S-015-08).  
  _Intent:_ Write failing test that verifies `is_watermarker_enabled` and `can_watermark_optout` are returned in UploadConfig response.  
  _Verification commands:_  
  - `php artisan test --filter=UploadConfig`  
  _Notes:_ Test should verify: true when all conditions met, false when any condition missing, `can_watermark_optout` respects `watermark_optout_disabled`.

- [x] T-015-04 – Add `is_watermarker_enabled` and `can_watermark_optout` properties to UploadConfig.php (FR-015-04, FR-015-08).  
  _Intent:_ Implement computed properties checking: `watermark_enabled` config, `watermark_photo_id` set, Imagick available, and `watermark_optout_disabled`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadConfig`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ `can_watermark_optout` = `is_watermarker_enabled` AND NOT `watermark_optout_disabled`.

### Increment I2 – Backend: Add watermark flag to upload request

- [x] T-015-05 – Add feature test for upload with apply_watermark parameter (FR-015-02, FR-015-06, S-015-05).  
  _Intent:_ Write tests for upload with apply_watermark true/false/missing.  
  _Verification commands:_  
  - `php artisan test --filter=UploadPhotoRequest`  
  _Notes:_ Missing parameter should not cause validation error (optional field).

- [x] T-015-06 – Add apply_watermark validation to UploadPhotoRequest.php (FR-015-06).  
  _Intent:_ Add validation rule and accessor method for apply_watermark parameter.  
  _Verification commands:_  
  - `php artisan test --filter=UploadPhotoRequest`  
  - `make phpstan`  
  _Notes:_ Rule: `'apply_watermark' => 'sometimes|boolean'`

### Increment I3 – Backend: Pass watermark flag to ProcessImageJob

- [x] T-015-07 – Add unit test for ProcessImageJob with watermark flag (FR-015-05).  
  _Intent:_ Write test verifying ProcessImageJob stores and uses apply_watermark parameter.  
  _Verification commands:_  
  - `php artisan test --filter=ProcessImageJob`  
  _Notes:_ Test constructor, serialization, and handle method.

- [x] T-015-08 – Extend ProcessImageJob to accept apply_watermark parameter (FR-015-05).  
  _Intent:_ Add nullable bool parameter to constructor, store in property, pass to pipeline.  
  _Verification commands:_  
  - `php artisan test --filter=ProcessImageJob`  
  - `make phpstan`  
  _Notes:_ Parameter should be serialized for queue processing.

- [x] T-015-09 – Update PhotoController to pass watermark flag to job (FR-015-02, FR-015-05, FR-015-09).  
  _Intent:_ Get apply_watermark from request, enforce `watermark_optout_disabled` restriction, and pass to ProcessImageJob::dispatch.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoController`  
  - `make phpstan`  
  _Notes:_ If `watermark_optout_disabled` is true, ignore request value and pass `null` to force global setting. Also update process() private method signature.

- [ ] T-015-09b – Add test for server-side enforcement of opt-out restriction (FR-015-09, S-015-08).  
  _Intent:_ Write test that uploads with `apply_watermark=false` when `watermark_optout_disabled=true` and verifies photo is still watermarked.  
  _Verification commands:_  
  - `php artisan test --filter=Upload`  
  _Notes:_ This prevents bypassing admin restriction via direct API calls. Will implement after I4 when watermarking logic is complete.

### Increment I4 –Backend: ApplyWatermark pipe respects flag

- [x] T-015-10 – Add unit test for ApplyWatermark pipe with flag (FR-015-05, S-015-01, S-015-02).  
  _Intent:_ Write tests for ApplyWatermark behavior with flag true/false/null.  
  _Verification commands:_  
  - `php artisan test --filter=ApplyWatermark`  
  _Notes:_ false = skip, true or null = apply if globally enabled.

- [x] T-015-11 – Modify ApplyWatermark pipe to check flag (FR-015-05).  
  _Intent:_ Add flag check in handle() method before applying watermark.  
  _Verification commands:_  
  - `php artisan test --filter=ApplyWatermark`  
  - `php artisan test --filter=ProcessImageJob`  
  - `make phpstan`  
  _Notes:_ Passed flag through InitDTO and StandaloneDTO to ApplyWatermark pipe.

- [x] T-015-11b – Register watermark_optout_disabled in ConfigIntegrity (FR-015-07).  
  _Intent:_ Add config key to SE_FIELDS array to pass integrity checks.  
  _Verification commands:_  
  - `php artisan test tests/Unit/Middleware/ConfigIntegrityTest.php`  
  _Notes:_ Required because watermark configs are Supporter Edition features (level=1).

- [ ] T-015-12 – Integration test: upload without watermark (S-015-02).  
  _Intent:_ End-to-end test uploading photo with apply_watermark=false, verify not watermarked.  
  _Verification commands:_  
  - `php artisan test --filter=Upload`  
  _Notes:_ Requires watermarking to be globally enabled in test setup. Deferred to I9 integration testing.

### Increment I5 – Frontend: Extend TypeScript types

- [x] T-015-13 – Regenerate TypeScript types (FR-015-04, FR-015-08).  
  _Intent:_ Run type generation to include `is_watermarker_enabled` and `can_watermark_optout` in UploadConfig type.  
  _Verification commands:_  
  - `php artisan typescript:transform`  
  - `npm run check`  
  _Notes:_ Verified lychee.d.ts updated with new properties at lines 422-423.

### Increment I6 – Frontend: Add toggle to UploadPanel

- [ ] T-015-14 – Add watermark toggle state to UploadPanel (FR-015-01, FR-015-03).  
  _Intent:_ Add `applyWatermark` ref, default true, manage toggle state.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ State persists for upload session, reset on modal close.

- [ ] T-015-15 – Add toggle UI component to UploadPanel (FR-015-01, S-015-07).  
  _Intent:_ Add PrimeVue InputSwitch with label, conditional visibility, disabled state.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ Show only when `setup.value?.can_watermark_optout`. Disable during uploads.

### Increment I7 – Frontend: Pass watermark flag in upload service

- [ ] T-015-16 – Extend UploadData type and upload-service.ts (FR-015-02).  
  _Intent:_ Add apply_watermark field to UploadData type and FormData.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Append to FormData: `formData.append("apply_watermark", ...)`.

- [ ] T-015-17 – Update UploadingLine.vue to accept watermark prop (FR-015-02, S-015-06).  
  _Intent:_ Add prop for watermark flag, pass to upload service.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Prop passed from UploadPanel, used in process() method.

- [ ] T-015-18 – Update UploadPanel.vue to pass watermark flag to UploadingLine (FR-015-02).  
  _Intent:_ Bind applyWatermark state to UploadingLine component prop.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ Pass current toggle state to each UploadingLine.

### Increment I8 – Translations

- [ ] T-015-19 – Add English translation for watermark toggle and admin setting (FR-015-01, FR-015-07).  
  _Intent:_ Add translation keys to lang/en/gallery.php and lang/en/all_settings.php.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Keys: `dialogs.upload.apply_watermark`, `watermark_optout_disabled`.

- [ ] T-015-20 – Add translations for all supported languages (FR-015-01, FR-015-07).  
  _Intent:_ Add translation strings to all 21 language files for both keys.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Languages: ar, cz, de, el, en, es, fa, fr, hu, it, ja, nl, no, pl, pt, ru, sk, sv, vi, zh_CN, zh_TW.

### Increment I8b – Admin UI: Add watermark_optout_disabled setting

- [ ] T-015-21 – Add watermark_optout_disabled toggle to Mod Watermarker settings (FR-015-07, S-015-08).  
  _Intent:_ Add toggle in admin settings UI for controlling watermark opt-out availability.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ Label: "Disable watermark opt-out". When enabled, users cannot skip watermarking.

### Increment I9 – Integration and documentation

- [ ] T-015-22 – Manual end-to-end testing (S-015-01, S-015-02, S-015-03, S-015-08).  
  _Intent:_ Manually verify all scenarios work correctly.  
  _Verification commands:_  
  - Test upload with toggle ON: watermark applied  
  - Test upload with toggle OFF: no watermark  
  - Test with watermarking disabled: no toggle visible  
  - Test with `watermark_optout_disabled` enabled: no toggle visible  
  - Test server-side enforcement: with `watermark_optout_disabled=true`, use API to upload with `apply_watermark=false`, verify photo is still watermarked  
  _Notes:_ Document results in this task. Server-side enforcement prevents bypassing admin restriction via direct API calls.

- [ ] T-015-23 – Run full quality gate.  
  _Intent:_ Run all checks to ensure nothing broken.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `npm run format`  
  - `php artisan test`  
  - `npm run check`  
  - `make phpstan`  
  _Notes:_ All must pass before feature complete.

- [ ] T-015-24 – Update roadmap and documentation.  
  _Intent:_ Mark feature complete in roadmap, update any relevant documentation.  
  _Verification commands:_  
  - N/A (documentation task)  
  _Notes:_ Move to Completed Features, update last updated dates.

## Notes / TODOs

- **Pipeline configuration:** Need to determine how to pass `apply_watermark` flag to ApplyWatermark pipe. Options:
  1. Add to StandaloneDTO
  2. Add to pipeline context/configuration
  3. Use Laravel's service container binding
  
- **Chunk uploads:** The `apply_watermark` flag only needs to be sent with the final chunk (when processing starts). Earlier chunks can omit it. Verify this during I7.

- **Test database:** The existing watermarking tests should provide a good reference for test setup.

- **Admin setting location:** The `watermark_optout_disabled` setting should be placed in the Mod Watermarker section of admin settings, with other watermark-related settings.

- **Config migration:** Use existing config migration patterns (see watermark_enabled migration) for adding the new config entry.

---

*Last updated: 2026-02-24*
