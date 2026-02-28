# Feature 020 Tasks – Raw Upload Support

_Status: Complete_
_Last updated: 2026-02-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections and, when required, ADRs reflect the clarified behaviour.

---

## I1 – SizeVariantType Enum Renumbering + Migration

- [x] T-020-01 – Write failing test: `SizeVariantType::RAW->value === 0` and `ORIGINAL->value === 1` (FR-020-01, S-020-13).
  _Intent:_ Red test proving the enum values are not yet updated.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`
  _Notes:_ Test extends `AbstractTestCase` (Unit).

- [x] T-020-02 – Update `SizeVariantType` enum: `RAW = 0`, shift all others by +1 (`ORIGINAL = 1` … `PLACEHOLDER = 8`) (FR-020-01).
  _Intent:_ Enum definition change. Update `name()` and `localization()` match arms.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ PHPStan will flag every exhaustive match that is now incomplete. Fix all call sites before proceeding.

- [x] T-020-03 – Fix all `SizeVariantType` exhaustive match arms across codebase (FR-020-01).
  _Intent:_ Add `RAW` case to every `match` / `switch` on `SizeVariantType`. Key files: `SizeVariants` model, `SizeVariantDimensionHelpers`, `SizeVariantDefaultFactory`, `DownloadVariantType`, `BaseArchive`, diagnostics checks, etc.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  _Notes:_ `SizeVariantDimensionHelpers::isEnabledByConfiguration()` → RAW returns `false`. `SizeVariantDefaultFactory::createSizeVariantCond()` → reject RAW (like ORIGINAL).

- [x] T-020-04 – Create two-phase migration for `type` column shift (FR-020-02, NFR-020-01, NFR-020-05).
  _Intent:_ Phase 1: `UPDATE size_variants SET type = type + 10 WHERE type >= 0`. Phase 2: `UPDATE size_variants SET type = type - 9 WHERE type >= 10`. Cross-DB safe (SQLite, MySQL, PostgreSQL). Respects unique constraint on `(photo_id, type)`. Rollback: reverse two-phase (`+9`, then `-10`).
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  _Notes:_ Test on SQLite (test suite). Document MySQL/PostgreSQL behavior.

- [x] T-020-05 – Update `SizeVariants` model: add `$raw` property, update `add()`, `getSizeVariant()`, `toCollection()`, `deleteAll()`, add `getRaw()` (FR-020-11, S-020-17).
  _Intent:_ Model CRUD for RAW variant. `deleteAll()` must also delete RAW file from storage.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`

- [x] T-020-06 – Update `SizeVariantFactory` (test factory) with new integer values (FR-020-15).
  _Intent:_ Test factories must produce correct `type` values for all size variants.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --no-coverage`

- [x] T-020-07 – Green: all existing tests pass with new enum values (FR-020-01, S-020-13).
  _Intent:_ Verify the T-020-01 test now passes and all pre-existing tests remain green.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I1b – Existing Raw-Format File Migration

- [x] T-020-08 – Write failing test: ORIGINAL with `short_path` ending `.nef` → after migration becomes `type = RAW (0)` (FR-020-16, S-020-23).
  _Intent:_ Red test for reclassification migration.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-09 – Write failing test: ORIGINAL with `short_path` ending `.pdf` → stays `type = ORIGINAL (1)` after migration (FR-020-16, S-020-22).
  _Intent:_ Red test confirming PDF exclusion.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-10 – Create reclassification migration: read `raw_formats` config, match extension from `short_path`, set `type = 0` for matching rows (excluding `.pdf`) (FR-020-16).
  _Intent:_ Data migration. Rollback: `type = 0` → `type = 1` for affected rows.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  _Notes:_ No JOIN to `photos` needed — `short_path` preserves original extension.

- [x] T-020-11 – Green: reclassification tests pass (FR-020-16, S-020-23).
  _Intent:_ Both T-020-08 and T-020-09 tests pass.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I2 – Update DownloadVariantType + Related Enums

- [x] T-020-12 – Add `DownloadVariantType::RAW = 'RAW'` case and update `getSizeVariantType()` mapping (FR-020-06, DO-020-02).
  _Intent:_ Enum extension for download.
  _Verification commands:_
  - `make phpstan`

- [x] T-020-13 – Verify `PurchasableSizeVariantType` has no RAW case (FR-020-10, S-020-12).
  _Intent:_ Confirm shop exclusion — no code change needed, just verification.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ FULL ≠ ORIGINAL in `PurchasableSizeVariantType`.

- [x] T-020-14 – Update `BaseArchive::extractFileInfo()` to handle RAW variant (FR-020-06, S-020-16).
  _Intent:_ Archive/ZIP logic handles RAW download variant.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I3 – Config Option + Migration

- [x] T-020-15 – Write test: `raw_download_enabled` config default is `false` (FR-020-05, CFG-020-01).
  _Intent:_ Red test for config key existence and default.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-16 – Create migration inserting `raw_download_enabled` config row (boolean, default `0`, category 'Image Processing') (FR-020-05, CFG-020-01).
  _Intent:_ Config infrastructure for RAW download gating.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`

- [x] T-020-17 – Add English translation strings for `raw_download_enabled` setting + placeholders for 21 languages (CFG-020-01, UI-020-03).
  _Intent:_ Settings UI label and description. Only modify PHP arrays in `lang/<locale>/*.php`.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`
  _Notes:_ Never modify `lang/php_*.json` files.

---

## I4 – RAW Conversion Pipeline + HEIC Unification

- [x] T-020-18 – Define `ConvertibleRawFormat` enum/constant list with all extensions from FR-020-09 (FR-020-09).
  _Intent:_ Central definition of extensions that trigger RAW+convert pipeline (includes `.heic`, `.heif`).
  _Verification commands:_
  - `make phpstan`

- [x] T-020-19 – Write failing tests for RAW upload pipeline (FR-020-03, FR-020-04, S-020-01, S-020-02, S-020-03, S-020-04, S-020-05, S-020-14, S-020-21, S-020-22, S-020-24).
  _Intent:_ Red tests covering: (a) HEIC → RAW + ORIGINAL, (b) NEF → RAW + ORIGINAL (mocked), (c) PSD → RAW + ORIGINAL (mocked), (d) JPEG → no RAW, (e) video → no RAW, (f) Imagick failure → RAW-only fallback, (g) `raw_formats` non-convertible → RAW variant, (h) PDF → ORIGINAL, (i) HEIC + no Imagick → RAW-only. Use existing HEIC fixtures: `classic-car.heic`, `sewing-threads.heic`.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`
  _Notes:_ Tests extend `BaseApiWithDataTest` (Feature_v2). May need to source/generate minimal NEF/PSD test fixtures (FX-020-01, FX-020-02).

- [x] T-020-20 – Create `RawToJpeg` converter class (FR-020-04, FR-020-09).
  _Intent:_ Unified Imagick-based converter replacing `HeifToJpeg`. Handles all convertible RAW formats. Quality: 92. Does NOT delete source file.
  _Verification commands:_
  - `make phpstan`

- [x] T-020-21 – Create `DetectAndStoreRaw` Init pipe replacing `ConvertUnsupportedMedia` (FR-020-03, FR-020-04).
  _Intent:_ If extension matches convertible RAW list: (1) copy original to RAW storage, (2) convert via `RawToJpeg`, (3) on success replace `source_file` in DTO with JPEG, (4) on failure graceful fallback (keep as RAW-only, log warning). PDF exception: stays as ORIGINAL. DTO must carry a flag so downstream pipes (`AssertSupportedMedia`, `CreateOriginalSizeVariant`) know about RAW-only uploads.
  _Verification commands:_
  - `make phpstan`

- [x] T-020-22 – Create `CreateRawSizeVariant` Standalone pipe (FR-020-03).
  _Intent:_ Creates RAW `size_variant` DB row if raw path is set in DTO. Placed after `CreateOriginalSizeVariant` in pipeline.
  _Verification commands:_
  - `make phpstan`

- [x] T-020-23 – Update `Create.php` pipe chain: replace `ConvertUnsupportedMedia` with `DetectAndStoreRaw`, add `CreateRawSizeVariant` after `CreateOriginalSizeVariant` (FR-020-03, FR-020-04).
  _Intent:_ Wire new pipes into upload pipeline.
  _Verification commands:_
  - `make phpstan`

- [x] T-020-24 – Remove `HeifToJpeg`, `ConvertUnsupportedMedia`, `PhotoConverterFactory`, `ConvertableImageType` (FR-020-04).
  _Intent:_ Delete superseded classes. Verify no remaining references. HEIC/HEIF detection folded into `ConvertibleRawFormat`.
  _Verification commands:_
  - `make phpstan`
  - `grep -r 'HeifToJpeg\|ConvertUnsupportedMedia\|PhotoConverterFactory\|ConvertableImageType' app/ --include='*.php'`
  _Notes:_ If any remaining call sites reference these classes, refactor them in this task.

- [x] T-020-25 – Green: all RAW upload tests pass (FR-020-03, FR-020-04, S-020-01 through S-020-05, S-020-14, S-020-21, S-020-22, S-020-24).
  _Intent:_ T-020-19 tests and all pre-existing tests pass.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I6 – Watermark Exclusion

- [x] T-020-26 – Write failing test: `Watermarker::do()` on RAW size variant returns early without watermarking (FR-020-07, S-020-11, S-020-15).
  _Intent:_ Red test for watermark skip.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-27 – Add RAW to early-return guard in `Watermarker::do()` alongside PLACEHOLDER skip (FR-020-07).
  _Intent:_ Single-line change in `Watermarker::do()`. `ApplyWatermark` pipe needs no changes.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I7 – API Response Changes

- [x] T-020-28 – Write failing API tests: `has_raw: true` when RAW row exists, `has_raw: false` otherwise (FR-020-08, S-020-09, S-020-10).
  _Intent:_ Red tests for `PhotoResource` change and RAW exclusion from `SizeVariantsResouce`.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`
  _Notes:_ Tests extend `BaseApiWithDataTest` (Feature_v2).

- [x] T-020-29 – Add `public bool $has_raw` to `PhotoResource`, derived from `$photo->size_variants->getRaw() !== null` (FR-020-08, API-020-02).
  _Intent:_ DB row existence check, NOT `Photo::isRaw()` (which is MIME-based). `PreComputedPhotoData::is_raw` stays unchanged. `SizeVariantsResouce` stays unchanged.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`

- [x] T-020-30 – Regenerate TypeScript types (FR-020-08).
  _Intent:_ Run `php artisan typescript:transform` to update generated TS types with `has_raw`.
  _Verification commands:_
  - `php artisan typescript:transform`
  - `npm run check`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I8 – Download Gating

- [x] T-020-31 – Write failing test: RAW download with config disabled → 403 (FR-020-06, S-020-07).
  _Intent:_ Red test for download gating.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-32 – Write failing test: RAW download with config enabled → 200 with file (FR-020-06, S-020-06).
  _Intent:_ Red test for successful RAW download.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-33 – Write failing test: RAW download when photo has no RAW → 404 (S-020-08).
  _Intent:_ Red test for missing-variant case.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-34 – Write failing test: ZIP download includes RAW when enabled (S-020-16).
  _Intent:_ Red test for bulk download behavior.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-35 – Update download controller/request validation to check `raw_download_enabled` when variant is RAW (FR-020-06, API-020-01).
  _Intent:_ Gate logic in download endpoint.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I9 – Diagnostics & Statistics Updates

- [x] T-020-36 – Audit diagnostics checks for hardcoded type values: `PlaceholderExistsCheck`, `SmallMediumExistsCheck`, `WatermarkerEnabledCheck` (FR-020-14, S-020-19).
  _Intent:_ Replace any hardcoded `->value` comparisons with enum references. Ensure ORIGINAL=1 (not 0) everywhere.
  _Verification commands:_
  - `grep -rn 'type.*=.*0\|type.*=.*1\|->value' app/Actions/Diagnostics/ --include='*.php'`
  - `make phpstan`

- [x] T-020-37 – Audit `Spaces.php` and `RSS/Generate.php` for type value references (FR-020-14, S-020-20).
  _Intent:_ Update any hardcoded type integers. Add RAW to storage statistics.
  _Verification commands:_
  - `make phpstan`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `vendor/bin/php-cs-fixer fix --dry-run`

---

## I10 – Frontend: Download RAW Button

- [x] T-020-38 – Update TypeScript types if not already current from I7 (FR-020-08, FR-020-12).
  _Intent:_ Ensure `has_raw` and `RAW` variant type exist in TS types.
  _Verification commands:_
  - `npm run check`

- [x] T-020-39 – Add conditional "Download RAW" button in photo detail view download section (FR-020-12, UI-020-01, UI-020-02, S-020-18).
  _Intent:_ Button visible only when `photo.has_raw === true` AND `raw_download_enabled === true`. Wire to download service with `variant=RAW`.
  _Verification commands:_
  - `npm run check`

- [x] T-020-40 – Add English translation strings for "Download RAW" button + placeholders for 21 languages (FR-020-12, UI-020-01).
  _Intent:_ Only modify PHP arrays in `lang/<locale>/*.php`. Never modify `lang/php_*.json` files.
  _Verification commands:_
  - `npm run format`
  - `npm run check`

- [x] T-020-41 – Verify settings UI shows `raw_download_enabled` toggle (UI-020-03).
  _Intent:_ Config system should auto-render the toggle. Verify it appears in Image Processing section.
  _Verification commands:_
  - `npm run format`
  - `npm run check`

---

## I11 – Integration Tests & Cleanup

- [x] T-020-42 – End-to-end test: upload RAW → verify RAW + ORIGINAL + thumbnails → download RAW (all scenarios).
  _Intent:_ Full pipeline integration test.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-43 – End-to-end test: upload HEIC → verify RAW (HEIC) + ORIGINAL (JPEG) → download RAW (S-020-02).
  _Intent:_ HEIC-specific integration test using existing fixtures.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --filter=<TestClass>`

- [x] T-020-44 – Verify standard JPEG/PNG upload is completely unaffected (S-020-04).
  _Intent:_ Regression check — no behavioral change for normal uploads.
  _Verification commands:_
  - `XDEBUG_MODE=off php artisan test --no-coverage`

- [x] T-020-45 – Run full quality gate (all file types modified).
  _Intent:_ Final quality gate before feature completion.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `XDEBUG_MODE=off php artisan test --no-coverage`
  - `make phpstan`

- [x] T-020-46 – Update knowledge map with RAW size variant type and conversion pipeline.
  _Intent:_ Add entries for new modules/classes in `docs/specs/4-architecture/knowledge-map.md`.
  _Verification commands:_
  - Visual review of knowledge-map.md.

- [x] T-020-47 – Update image-processing reference doc.
  _Intent:_ Document RAW pipeline in `docs/specs/3-reference/image-processing.md` (or create if missing).
  _Verification commands:_
  - Visual review.

---

## Notes / TODOs

- **Test fixtures:** Need to source or generate minimal NEF and PSD sample files for `tests/Samples/` (FX-020-01, FX-020-02). Existing HEIC fixtures are available (FX-020-03).
- **DTO design note (I4):** The `DetectAndStoreRaw` pipe must set a flag on the DTO so downstream pipes (`AssertSupportedMedia`, `CreateOriginalSizeVariant`) know when a RAW-only upload is in progress (Imagick failure fallback path). Design the DTO field in T-020-21.
- **Traceability gaps from final review (carry-forward):**
  - S-020-05 (video unchanged) is assigned to I4 in scenario table but not in I4's refs — covered implicitly by T-020-19 test case (e).
  - S-020-16 (ZIP download with RAW) is assigned to I8 — now explicitly covered by T-020-34.
  - S-020-17 (delete photo deletes RAW) is assigned to I1 — covered by T-020-05 (`deleteAll()` update).
  - `PhotoConverterFactory` removal: listed in plan Dependencies but missing from spec "Removed classes" — covered by T-020-24.
- **Follow-ups (post-feature backlog from plan.md):**
  - RAW format support diagnostics check (list which formats Imagick can handle).
  - Maintenance command for batch-converting old raw-format files.
  - Config for JPEG quality when converting RAW (currently hardcoded at 92).
