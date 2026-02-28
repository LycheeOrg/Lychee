# Feature Plan 020 – Raw Upload Support

_Linked specification:_ `docs/specs/4-architecture/features/020-raw-upload-support/spec.md`
_Status:_ Active
_Last updated:_ 2026-02-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

**User value:** Photographers can upload camera RAW files (NEF, CR2, ARW, DNG, PSD, HEIC, etc.) and have Lychee automatically preserve the untouched original while generating web-displayable versions. RAW files can be downloaded when the admin enables the option.

**Success signals:**
- RAW uploads produce both a RAW size variant (original file) and a converted ORIGINAL (JPEG).
- HEIC/HEIF uploads preserve the original file as RAW instead of discarding it.
- Existing photos continue to work after the `type` column renumbering migration.
- RAW files are never watermarked or displayed in the web interface.
- All existing tests pass with the new enum values.

## Scope Alignment

- **In scope:**
  - `SizeVariantType` enum renumbering (RAW=0, shift others +1)
  - Database migration for `type` column shift
  - RAW preprocessing pipeline (store raw + convert to ORIGINAL)
  - HEIC/HEIF refactoring to use RAW pipeline
  - `DownloadVariantType::RAW` and gated download
  - `raw_download_enabled` config option
  - Watermark exclusion for RAW
  - API response changes (`has_raw` flag, no RAW URL)
  - Frontend "Download RAW" button
  - `SizeVariants` model updates
  - Test coverage for all new paths

- **Out of scope:**
  - RAW editing/processing tools
  - RAW display in gallery
  - RAW in shop/purchasable types
  - Backward conversion of existing HEIC photos
  - Video RAW formats

## Dependencies & Interfaces

- **Imagick PHP extension** — Required for converting camera RAW formats to JPEG. Must have libraw/dcraw delegates compiled (Q-020-02 resolved → Option A: Imagick + delegates, single code path). System requirement: `apt install libraw-dev` or equivalent.
- **`ConvertableImageType` enum** — Currently only handles HEIC/HEIF; will be **removed**. HEIC/HEIF detection folded into the unified RAW format list.
- **`PhotoConverterFactory`** — Currently only dispatches `HeifToJpeg`; will be **removed**. Conversion dispatching handled directly by `DetectAndStoreRaw` pipe.
- **`HeifToJpeg`** — Will be **removed** and replaced by unified `RawToJpeg` converter.
- **`FileExtensionService`** — Currently distinguishes supported images, supported videos, and "accepted raw" (unprocessed). The new RAW pipeline introduces a third category: "convertible raw" that gets both stored and converted. Files in `raw_formats` that are NOT convertible are stored as RAW size variants (not ORIGINAL), except PDF which stays as ORIGINAL (Q-020-04 resolved).
- **Feature 015 (Upload Watermark Toggle)** — Watermark skip logic must also respect RAW type.
- **Feature 004 (Album Size Statistics)** — RAW file sizes must be included in storage statistics.

## Assumptions & Risks

- **Assumptions:**
  - Imagick is available on the target system and can handle common camera RAW formats (NEF, CR2, ARW, DNG at minimum).
  - File sample fixtures can be obtained or generated for testing without copyright issues.
  - The `type` column in `size_variants` has no values outside the current 0–7 range.

- **Risks / Mitigations:**
  - **R1: Imagick delegate support varies** — Not all Imagick installations can convert all RAW formats. Mitigation: Graceful fallback (Q-020-01 resolved → Option C) — if conversion fails, store file as-is as RAW variant with no ORIGINAL, matching legacy "accepted raw" behavior. Warning logged.
  - **R2: Large file sizes** — Camera RAW files are 20–80 MB. Mitigation: Existing quota system handles this; upload pipeline already runs in queued jobs so conversion is inherently async from user perspective (Q-020-03 resolved).
  - **R3: Migration on large databases** — Shifting millions of `type` values could lock the table. Mitigation: Use batched UPDATE or database-specific optimizations.
  - **R4: HEIC refactoring may break existing behavior** — Currently HEIC→JPEG works, changing the flow could introduce regressions. Mitigation: Comprehensive tests for HEIC upload before and after.

## Implementation Drift Gate

After each increment, verify:
1. `make phpstan` — Zero errors
2. `php artisan test` — All tests pass
3. `vendor/bin/php-cs-fixer fix` — Code style clean
4. Check `tasks.md` checkboxes match actual progress

Record drift findings in this plan's Follow-ups section.

## Increment Map

### I1 – SizeVariantType Enum Renumbering + Migration (~90 min)

- _Goal:_ Add `RAW = 0` to `SizeVariantType`, shift all others by +1, create migration to update existing DB rows.
- _Preconditions:_ No pending DB changes; clean test suite.
- _Steps:_
  1. Write failing test: verify `SizeVariantType::RAW->value === 0` and `SizeVariantType::ORIGINAL->value === 1`.
  2. Update `SizeVariantType` enum: `RAW = 0`, `ORIGINAL = 1`, ..., `PLACEHOLDER = 8`.
  3. Update `name()` and `localization()` match arms.
  4. Create migration using two-phase approach (cross-DB safe, respects unique constraint on `(photo_id, type)`): Phase 1: `UPDATE size_variants SET type = type + 10 WHERE type >= 0`; Phase 2: `UPDATE size_variants SET type = type - 9 WHERE type >= 10`. PostgreSQL does not support `ORDER BY` in `UPDATE`, so a direct `type + 1 ORDER BY type DESC` strategy is not portable.
  5. Update `SizeVariants` model: add `$raw` property, update `add()`, `getSizeVariant()`, `toCollection()`, `deleteAll()`, add `getRaw()`.
  6. Update `SizeVariantDimensionHelpers::isEnabledByConfiguration()` to handle RAW (return `false` — RAW is not auto-generated).
  7. Update `SizeVariantDefaultFactory::createSizeVariantCond()` to reject RAW (like it rejects ORIGINAL).
  8. Fix all existing test factories (`SizeVariantFactory`) with new integer values.
  9. Run `make phpstan` and `php artisan test`.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ All tests green, enum values correct, migration runs on SQLite.
- _Refs:_ FR-020-01, FR-020-02, FR-020-11, FR-020-13, FR-020-15, S-020-13, S-020-17, NFR-020-01, NFR-020-05

### I1b – Existing Raw-Format File Migration (~30 min)

- _Goal:_ Reclassify existing files stored as ORIGINAL whose extension matches `raw_formats` (excluding PDF) to RAW type.
- _Preconditions:_ I1 complete (RAW=0 type exists in DB).
- _Steps:_
  1. Write failing test: seed DB with a photo having ORIGINAL size variant with `short_path` ending in `.nef` → after migration, verify `type = RAW (0)`.
  2. Write failing test: seed DB with a photo having ORIGINAL size variant with `short_path` ending in `.pdf` → after migration, verify `type = ORIGINAL (1)` (unchanged).
  3. Create migration that checks the file extension from `size_variants.short_path` (no JOIN to `photos` needed — `short_path` preserves the original file extension). Read `raw_formats` config value at migration time. For rows where `type = ORIGINAL` and extension matches (excluding `.pdf`), set `type = 0` (RAW).
  4. Rollback: reverse the reclassification (`type = 0` back to `type = 1` for those rows).
  5. Run `make phpstan` and `php artisan test`.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ Existing raw-format photos properly reclassified. PDF files remain ORIGINAL.
- _Refs:_ FR-020-16, S-020-23, Q-020-01, Q-020-04

### I2 – Update DownloadVariantType + Related Enums (~45 min)

- _Goal:_ Add `RAW` to `DownloadVariantType`, update mapping. Ensure `PurchasableSizeVariantType` is unchanged.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add `DownloadVariantType::RAW = 'RAW'` case.
  2. Update `getSizeVariantType()` method to map `RAW → SizeVariantType::RAW`.
  3. Verify `PurchasableSizeVariantType` has no RAW case (no change needed).
  4. Update BaseArchive's `extractFileInfo()` to handle RAW variant.
  5. Run tests.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ Download enum updated, archive logic handles RAW, shop unaffected.
- _Refs:_ FR-020-06, FR-020-10, DO-020-02, S-020-06, S-020-07, S-020-08, S-020-12

### I3 – Config Option + Migration (~30 min)

- _Goal:_ Add `raw_download_enabled` config key.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Create migration to insert `raw_download_enabled` config row (boolean, default `0`, category 'Image Processing').
  2. Write test verifying config default is false.
  3. Update settings translations (English + placeholders for 21 languages).
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ Config key accessible via `ConfigManager`, settings UI shows toggle.
- _Refs:_ FR-020-05, CFG-020-01, UI-020-03

### I4 – RAW Conversion Pipeline + HEIC Unification (~90 min)

- _Goal:_ Implement the unified RAW preprocessing pipe that replaces both `ConvertUnsupportedMedia` and `HeifToJpeg`. Handles all convertible formats (camera RAW, PSD, HEIC/HEIF) via a single Imagick-based converter.
- _Preconditions:_ I1, I2 complete.
- _Steps:_
  1. Define `ConvertibleRawFormat` enum (or constant list) with all RAW extensions from FR-020-09 (includes `.heic`, `.heif`).
  2. Create `RawToJpeg` converter class — a unified Imagick-based converter that **replaces `HeifToJpeg`**. Key differences from `HeifToJpeg`: (a) handles all convertible RAW formats, not just HEIC/HEIF; (b) does **NOT** delete the source file (the original is preserved as RAW). Quality: 92 (same as current `HeifToJpeg`).
  3. Create new Init pipe: `DetectAndStoreRaw` — **replaces `ConvertUnsupportedMedia`**. If file extension matches convertible RAW list:
     a. Copy original file to RAW storage location (using naming strategy).
     b. Convert to JPEG using `RawToJpeg` converter.
     c. On success: replace `source_file` in DTO with converted JPEG. Store raw path info in DTO.
     d. On failure: **graceful fallback** (Q-020-01 → Option C) — keep file as-is as RAW variant with no ORIGINAL, log warning. Do NOT throw `CannotConvertMediaFileException`.
  4. Create new Standalone pipe: `CreateRawSizeVariant` — creates the RAW `size_variant` row in DB if raw path is set in DTO.
  5. **PDF exception** (Q-020-04): files matching `raw_formats` that are NOT in the convertible list are stored as RAW size variants — **except PDF**, which stays as ORIGINAL.
  6. Remove `HeifToJpeg`, `ConvertUnsupportedMedia`, `PhotoConverterFactory`, and `ConvertableImageType` (HEIC/HEIF detection folded into RAW format list). If `PhotoConverterFactory` or `ConvertableImageType` are used elsewhere, refactor those call sites.
  7. Update `Create.php` Init pipe chain: replace `ConvertUnsupportedMedia` with `DetectAndStoreRaw`. Update Standalone pipe chain: add `CreateRawSizeVariant` after `CreateOriginalSizeVariant`.
  8. Write failing tests first, then implement. Test cases:
     - Upload HEIC → RAW (HEIC) + ORIGINAL (JPEG) (← replaces I5 functionality)
     - Upload NEF → RAW (NEF) + ORIGINAL (JPEG)
     - Upload PSD → RAW (PSD) + ORIGINAL (JPEG)
     - Upload JPEG → no RAW, normal flow
     - Upload with Imagick failure → graceful fallback (RAW-only)
     - Upload HEIC with Imagick unavailable → stored as RAW-only (no JPEG), S-020-24
  9. Test with existing HEIC fixtures (`tests/Samples/classic-car.heic`, `sewing-threads.heic`) and mocked RAW conversions.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ RAW upload produces RAW variant + converted ORIGINAL. HEIC preserved as RAW. Conversion failure falls back gracefully. `HeifToJpeg` and `ConvertUnsupportedMedia` removed.
- _Refs:_ FR-020-03, FR-020-04, FR-020-09, S-020-01, S-020-02, S-020-03, S-020-04, S-020-05, S-020-14, S-020-21, S-020-22, S-020-24, NFR-020-04

### ~~I5 – HEIC/HEIF Refactoring~~ (merged into I4)

> HEIC/HEIF handling is now part of the unified RAW pipeline in I4. `HeifToJpeg` is fully replaced by `RawToJpeg`, and `ConvertUnsupportedMedia` is replaced by `DetectAndStoreRaw`. No separate increment needed.

### I6 – Watermark Exclusion (~20 min)

- _Goal:_ Ensure RAW variants are never watermarked.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Write failing test: create a RAW size variant, call `Watermarker::do()` on it → verify it returns early (no watermark applied).
  2. Add RAW to the early-return guard in `Watermarker::do()` alongside the existing PLACEHOLDER skip. This also covers `AlbumController::shouldWatermark()` since it delegates to the watermarker. `ApplyWatermark` pipe needs no changes (it calls `Watermarker::do()` which does the filtering).
  3. Run watermark-related tests.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ RAW never watermarked, all other variants watermarked normally.
- _Refs:_ FR-020-07, S-020-11, S-020-15

### I7 – API Response Changes (~45 min)

- _Goal:_ Add `has_raw` flag to `PhotoResource` (top-level property); `SizeVariantsResouce` stays unchanged (no `$raw` field).
- _Preconditions:_ I1, I4 complete.
- _Steps:_
  1. Add `public bool $has_raw` property to `PhotoResource`. Set it from `$photo->size_variants->getRaw() !== null` (DB row existence check, NOT `Photo::isRaw()` which is MIME-based). Note: `PreComputedPhotoData::is_raw` remains unchanged — it indicates the MIME type is neither supported image nor video, a separate concept.
  2. `SizeVariantsResouce` stays as-is — no `$raw` field added. It already fetches each variant by explicit type and does not include RAW.
  3. Regenerate TypeScript types (`php artisan typescript:transform`).
  4. Write API tests verifying `has_raw` presence (true when RAW row exists, false otherwise) and RAW exclusion from `size_variants`.
- _Commands:_ `make phpstan`, `php artisan test`, `php artisan typescript:transform`
- _Exit:_ API returns `has_raw` flag on `PhotoResource`, no RAW URL exposed in `SizeVariantsResouce`.
- _Refs:_ FR-020-08, S-020-09, S-020-10, API-020-02

### I8 – Download Gating (~45 min)

- _Goal:_ Gate RAW download by `raw_download_enabled` config.
- _Preconditions:_ I2, I3 complete.
- _Steps:_
  1. Write failing test: request RAW download with config disabled → 403.
  2. Write failing test: request RAW download with config enabled → 200 with file.
  3. Update download controller/request validation to check `raw_download_enabled` when variant is RAW.
  4. Write test: request RAW download when photo has no RAW → 404.
  5. Run tests.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ RAW download properly gated by config.
- _Refs:_ FR-020-06, S-020-06, S-020-07, S-020-08, S-020-16, API-020-01

### I9 – Diagnostics & Statistics Updates (~30 min)

- _Goal:_ Update diagnostics checks and statistics queries for new type values.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Audit `PlaceholderExistsCheck`, `SmallMediumExistsCheck`, `WatermarkerEnabledCheck` for hardcoded type values.
  2. Audit `Spaces.php` for type value references.
  3. Audit `RSS/Generate.php` for type value references.
  4. Update any hardcoded `->value` comparisons to use enum references.
  5. Add RAW to storage statistics if applicable.
  6. Run diagnostics and statistics tests.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ All diagnostics pass, statistics include RAW sizes.
- _Refs:_ FR-020-14, S-020-19, S-020-20

### I10 – Frontend: Download RAW Button (~60 min)

- _Goal:_ Add "Download RAW" option to photo detail view.
- _Preconditions:_ I7, I8 complete.
- _Steps:_
  1. Update TypeScript types (should be done in I7).
  2. Add conditional "Download RAW" button in photo detail view download section.
  3. Button visible only when `photo.has_raw === true` AND config `raw_download_enabled === true`.
  4. Wire button to download service with `variant=RAW`.
  5. Add English translation strings + placeholders for 21 languages.
  6. Update settings UI to show `raw_download_enabled` toggle (should already appear via config system).
- _Commands:_ `npm run format`, `npm run check`
- _Exit:_ Download RAW button appears conditionally, triggers download.
- _Refs:_ FR-020-12, UI-020-01, UI-020-02, S-020-18

### I11 – Integration Tests & Cleanup (~60 min)

- _Goal:_ End-to-end integration tests, cleanup, quality gates.
- _Preconditions:_ All previous increments complete.
- _Steps:_
  1. Full end-to-end test: upload RAW → verify RAW + ORIGINAL + thumbnails → download RAW.
  2. Full end-to-end test: upload HEIC → verify RAW (HEIC) + ORIGINAL (JPEG) → download RAW.
  3. Verify standard JPEG/PNG upload is completely unaffected.
  4. Run full test suite.
  5. Run `make phpstan`, `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`.
  6. Update knowledge map.
  7. Update image-processing reference doc.
- _Commands:_ Full quality gate
- _Exit:_ All quality gates pass, feature complete.
- _Refs:_ All scenarios

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-020-01 | I4 | NEF upload → RAW + ORIGINAL |
| S-020-02 | I4 | HEIC upload → RAW + ORIGINAL (merged from I5) |
| S-020-03 | I4 | PSD upload → RAW + ORIGINAL |
| S-020-04 | I4 | JPEG upload unchanged |
| S-020-05 | I4 | Video upload unchanged |
| S-020-06 | I8 | RAW download enabled |
| S-020-07 | I8 | RAW download disabled → 403 |
| S-020-08 | I8 | No RAW variant → 404 |
| S-020-09 | I7 | API has_raw: true |
| S-020-10 | I7 | API has_raw: false |
| S-020-11 | I6 | Watermark skips RAW |
| S-020-12 | I2 | Shop excludes RAW |
| S-020-13 | I1 | Migration shifts type values |
| S-020-14 | I4 | No Imagick → graceful fallback |
| S-020-15 | I6 | RAW not watermarked |
| S-020-16 | I8 | ZIP download with RAW |
| S-020-17 | I1 | Delete photo deletes RAW |
| S-020-18 | I10 | Frontend download button |
| S-020-19 | I9 | Diagnostics pass |
| S-020-20 | I9 | Statistics include RAW |
| S-020-21 | I4 | raw_formats non-convertible → RAW variant |
| S-020-22 | I4 | PDF → stays ORIGINAL |
| S-020-23 | I1b | Existing raw files migrated to RAW type |
| S-020-24 | I4 | HEIC + no Imagick → RAW-only (graceful) |

## Analysis Gate

Not yet completed. Will be run after spec, plan, and tasks agree.

## Exit Criteria

- [ ] All 11 increments (I1, I1b, I2–I4, ~~I5 merged~~, I6–I11) complete with passing quality gates
- [ ] `SizeVariantType` enum has RAW=0 and all others shifted by +1
- [ ] Migration tested on SQLite (tests) and documented for MySQL/PostgreSQL
- [ ] Existing DB photos with raw-format extensions migrated from ORIGINAL to RAW type (excluding PDF)
- [ ] PDF files stored as ORIGINAL (not RAW)
- [ ] `HeifToJpeg`, `ConvertUnsupportedMedia`, and `ConvertableImageType` removed; replaced by unified `RawToJpeg` + `DetectAndStoreRaw`
- [ ] RAW upload pipeline works for NEF, CR2, ARW, DNG, PSD, HEIC, HEIF
- [ ] HEIC/HEIF handled by unified RAW pipeline (original preserved, HEIC fallback to RAW-only if no Imagick)
- [ ] Watermark skip enforced in `Watermarker::do()` for RAW type
- [ ] RAW excluded from API `size_variants` response
- [ ] `has_raw` flag on `PhotoResource` derived from DB (`getRaw() !== null`), not MIME-based `Photo::isRaw()`
- [ ] `SizeVariantsResouce` unchanged (no `$raw` field)
- [ ] Download gated by `raw_download_enabled` config
- [ ] RAW not in `PurchasableSizeVariantType`
- [ ] Frontend "Download RAW" button conditional
- [ ] All existing tests pass with new enum values
- [ ] PHPStan, php-cs-fixer, npm check/format all clean
- [ ] Knowledge map and reference docs updated

## Follow-ups / Backlog

- **RAW format support diagnostics** — Add a diagnostics check that lists which RAW formats the installed Imagick can handle.
- **Maintenance command** — Consider a command to batch-convert existing "unprocessed raw" files (from old `raw_formats` config) into proper RAW size variants with ORIGINAL (for users who want thumbnails for old files).
- **RAW preview quality settings** — Config for JPEG quality when converting RAW to ORIGINAL (currently hardcoded at 92 in `HeifToJpeg`).

---

*Last updated: 2026-02-28*
