# Feature 015 Tasks – Upload Watermark Toggle

_Status: Draft_  
_Last updated: 2026-02-24_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I1 – Backend: Extend UploadConfig with watermarker status

- [ ] T-014-01 – Add feature test for UploadConfig watermarker status (FR-014-04, S-014-03, S-014-04).  
  _Intent:_ Write failing test that verifies `is_watermarker_enabled` is returned in UploadConfig response.  
  _Verification commands:_  
  - `php artisan test --filter=UploadConfig`  
  _Notes:_ Test should verify: true when all conditions met, false when any condition missing.

- [ ] T-014-02 – Add `is_watermarker_enabled` property to UploadConfig.php (FR-014-04).  
  _Intent:_ Implement computed property checking: `watermark_enabled` config, `watermark_photo_id` set, Imagick available.  
  _Verification commands:_  
  - `php artisan test --filter=UploadConfig`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ Use existing Watermarker::can_watermark() logic as reference.

### Increment I2 – Backend: Add watermark flag to upload request

- [ ] T-014-03 – Add feature test for upload with apply_watermark parameter (FR-014-02, FR-014-06, S-014-05).  
  _Intent:_ Write tests for upload with apply_watermark true/false/missing.  
  _Verification commands:_  
  - `php artisan test --filter=UploadPhotoRequest`  
  _Notes:_ Missing parameter should not cause validation error (optional field).

- [ ] T-014-04 – Add apply_watermark validation to UploadPhotoRequest.php (FR-014-06).  
  _Intent:_ Add validation rule and accessor method for apply_watermark parameter.  
  _Verification commands:_  
  - `php artisan test --filter=UploadPhotoRequest`  
  - `make phpstan`  
  _Notes:_ Rule: `'apply_watermark' => 'sometimes|boolean'`

### Increment I3 – Backend: Pass watermark flag to ProcessImageJob

- [ ] T-014-05 – Add unit test for ProcessImageJob with watermark flag (FR-014-05).  
  _Intent:_ Write test verifying ProcessImageJob stores and uses apply_watermark parameter.  
  _Verification commands:_  
  - `php artisan test --filter=ProcessImageJob`  
  _Notes:_ Test constructor, serialization, and handle method.

- [ ] T-014-06 – Extend ProcessImageJob to accept apply_watermark parameter (FR-014-05).  
  _Intent:_ Add nullable bool parameter to constructor, store in property, pass to pipeline.  
  _Verification commands:_  
  - `php artisan test --filter=ProcessImageJob`  
  - `make phpstan`  
  _Notes:_ Parameter should be serialized for queue processing.

- [ ] T-014-07 – Update PhotoController to pass watermark flag to job (FR-014-02, FR-014-05).  
  _Intent:_ Get apply_watermark from request and pass to ProcessImageJob::dispatch.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoController`  
  - `make phpstan`  
  _Notes:_ Also update process() private method signature.

### Increment I4 – Backend: ApplyWatermark pipe respects flag

- [ ] T-014-08 – Add unit test for ApplyWatermark pipe with flag (FR-014-05, S-014-01, S-014-02).  
  _Intent:_ Write tests for ApplyWatermark behavior with flag true/false/null.  
  _Verification commands:_  
  - `php artisan test --filter=ApplyWatermark`  
  _Notes:_ false = skip, true or null = apply if globally enabled.

- [ ] T-014-09 – Modify ApplyWatermark pipe to check flag (FR-014-05).  
  _Intent:_ Add flag check in handle() method before applying watermark.  
  _Verification commands:_  
  - `php artisan test --filter=ApplyWatermark`  
  - `php artisan test --filter=ProcessImageJob`  
  - `make phpstan`  
  _Notes:_ Need to pass flag through StandaloneDTO or pipeline configuration.

- [ ] T-014-10 – Integration test: upload without watermark (S-014-02).  
  _Intent:_ End-to-end test uploading photo with apply_watermark=false, verify not watermarked.  
  _Verification commands:_  
  - `php artisan test --filter=Upload`  
  _Notes:_ Requires watermarking to be globally enabled in test setup.

### Increment I5 – Frontend: Extend TypeScript types

- [ ] T-014-11 – Regenerate TypeScript types (FR-014-04).  
  _Intent:_ Run type generation to include `is_watermarker_enabled` in UploadConfig type.  
  _Verification commands:_  
  - `php artisan typescript:transform`  
  - `npm run check`  
  _Notes:_ Verify lychee.d.ts updated with new property.

### Increment I6 – Frontend: Add toggle to UploadPanel

- [ ] T-014-12 – Add watermark toggle state to UploadPanel (FR-014-01, FR-014-03).  
  _Intent:_ Add `applyWatermark` ref, default true, manage toggle state.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ State persists for upload session, reset on modal close.

- [ ] T-014-13 – Add toggle UI component to UploadPanel (FR-014-01, S-014-07).  
  _Intent:_ Add PrimeVue InputSwitch with label, conditional visibility, disabled state.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ Show only when `setup.value?.is_watermarker_enabled`. Disable during uploads.

### Increment I7 – Frontend: Pass watermark flag in upload service

- [ ] T-014-14 – Extend UploadData type and upload-service.ts (FR-014-02).  
  _Intent:_ Add apply_watermark field to UploadData type and FormData.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Append to FormData: `formData.append("apply_watermark", ...)`.

- [ ] T-014-15 – Update UploadingLine.vue to accept watermark prop (FR-014-02, S-014-06).  
  _Intent:_ Add prop for watermark flag, pass to upload service.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Prop passed from UploadPanel, used in process() method.

- [ ] T-014-16 – Update UploadPanel.vue to pass watermark flag to UploadingLine (FR-014-02).  
  _Intent:_ Bind applyWatermark state to UploadingLine component prop.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ Pass current toggle state to each UploadingLine.

### Increment I8 – Translations

- [ ] T-014-17 – Add English translation for watermark toggle (FR-014-01).  
  _Intent:_ Add translation key to lang/en/gallery.php.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Key: `dialogs.upload.apply_watermark` or similar.

- [ ] T-014-18 – Add translations for all supported languages (FR-014-01).  
  _Intent:_ Add translation strings to all 21 language files.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Languages: ar, cz, de, el, en, es, fa, fr, hu, it, ja, nl, no, pl, pt, ru, sk, sv, vi, zh_CN, zh_TW.

### Increment I9 – Integration and documentation

- [ ] T-014-19 – Manual end-to-end testing (S-014-01, S-014-02, S-014-03).  
  _Intent:_ Manually verify all scenarios work correctly.  
  _Verification commands:_  
  - Test upload with toggle ON: watermark applied  
  - Test upload with toggle OFF: no watermark  
  - Test with watermarking disabled: no toggle visible  
  _Notes:_ Document results in this task.

- [ ] T-014-20 – Run full quality gate.  
  _Intent:_ Run all checks to ensure nothing broken.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `npm run format`  
  - `php artisan test`  
  - `npm run check`  
  - `make phpstan`  
  _Notes:_ All must pass before feature complete.

- [ ] T-014-21 – Update roadmap and documentation.  
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

---

*Last updated: 2026-02-24*
