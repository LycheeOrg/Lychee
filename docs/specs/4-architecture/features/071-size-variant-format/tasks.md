# Feature 071 Tasks – Configurable Size-Variant Format and Quality

_Status: Complete_  
_Last updated: 2026-09-26_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist
- [x] T-071-01 – Unit tests: enum mapping and config validation (F-071-01, F-071-06, S-071-07, S-071-08).  
  _Intent:_ `tests/Unit/Enum/SizeVariantFormatTest.php`, `tests/Unit/Models/SizeVariantFormatConfigSanityTest.php`.  
  _Verification commands:_  
  - `php artisan test --filter=SizeVariantFormat`

- [x] T-071-02 – `SizeVariantFormat` enum + migrations `add_size_variant_format_config` and `bound_compression_quality` (F-071-01, F-071-06, F-071-10).  
  _Verification commands:_  
  - `php artisan test --filter=SizeVariantFormat`  
  - `make phpstan`

- [x] T-071-03 – Image-processing tests for extension selection (S-071-01, S-071-02, S-071-05).  
  _Intent:_ new cases in `tests/ImageProcessing/Image/Handlers/BaseImageHandler.php` (run under GD and Imagick).

- [x] T-071-04 – `BaseSizeVariantNamingStrategy::generateExtension()` honours `size_variant_format` (F-071-02..05).  
  _Verification commands:_  
  - `php artisan test --filter=PhotosAddHandler`

- [x] T-071-05 – Image-processing tests for encoded content (S-071-03, S-071-04, S-071-06).

- [x] T-071-06 – `BaseImageHandler::resolveQuality()`/`isLossless()`; WebP routing + lossless in `GdHandler::save()`; lossless in `ImagickHandler::save()` (F-071-07, F-071-08, F-071-09).  
  _Verification commands:_  
  - `php artisan test --filter=PhotosAddHandler`  
  - `make phpstan`

- [x] T-071-07 – Docs: roadmap, knowledge map, `docs/specs/3-reference/image-processing.md`.

- [x] T-071-08 – Quality gate (CI): `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`; complete the drift gate in plan.md.

## Notes / TODOs
- The Imagick variants of the `BaseImageHandler` cases (`PhotosAddHandlerImagickTest`) are skipped when `ext-imagick` is not installed and are covered by CI on the pull request. The GD variants and all unit tests were run locally.
- T-071-08 local results: `php-cs-fixer` clean, PHPStan `[OK] No errors`, Unit and ImageProcessing suites without regressions against the 7.9.0 baseline, `tests/Feature_v2/Settings` and `Install` green, `migrate:rollback --step=2` followed by `migrate` round-trips both migrations.
