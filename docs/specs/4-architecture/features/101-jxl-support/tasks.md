# Feature 101 Tasks – JPEG XL Support

_Status: Complete_  
_Last updated: 2026-10-18_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist
- [x] T-101-01 – Unit tests: JXL extension/MIME maps and magic-byte detection (FR-101-01, FR-101-02, S-101-05, S-101-06).  
  _Intent:_ `tests/Unit/Services/Image/FileExtensionServiceJxlTest.php` with synthetic codestream (`FF 0A`) and container headers written to temp files; `.jxl` with JPEG bytes rejected. Red before T-101-02.  
  _Verification commands:_  
  - `php artisan test --filter=FileExtensionServiceJxlTest`

- [x] T-101-02 – `FileExtensionService`: `.jxl`/`image/jxl` constants and `isJxl()` magic-byte helper used by `isSupportedImage()` for `.jxl` (FR-101-01, FR-101-02).  
  _Verification commands:_  
  - `php artisan test --filter=FileExtensionService`  
  - `make phpstan`

- [x] T-101-03 – Unit tests: `size_variant_format` accepts `jxl` (migration `type_range`), naming strategy returns `.jxl` for every generated variant with `jxl` and `.jxl` small/medium + `.jpeg` thumbs for a JXL original under `original` (FR-101-03, FR-101-04, FR-101-09, S-101-02, S-101-03, S-101-12). Red before T-101-04.  
  _Verification commands:_  
  - `php artisan test --filter="SizeVariantFormat|JxlNaming"`

- [x] T-101-04 – `SizeVariantFormat::JXL`, reversible migration extending `type_range` (down: `jxl` → `original`), `generatedExtension()` arm, `php artisan typescript:transform` (FR-101-03, FR-101-04, NFR-101-02).  
  _Verification commands:_  
  - `php artisan test --filter="SizeVariantFormat|JxlNaming|ConfigsTest"`  
  - `make phpstan`  
  - `vp run typecheck`

- [x] T-101-05 – Tests: GD refuses `.jxl` targets; Imagick load/save of `.jxl` throws `MediaFileOperationException('JPEG XL requires Imagick with JXL support')` when the probe reports no support (FR-101-06, S-101-07, S-101-08). Red before T-101-06.  
  _Verification commands:_  
  - `php artisan test --filter="GdHandlerJxlTest|ImagickHandlerJxlTest"`

- [x] T-101-06 – `supportsJxl()` probe (single helper), guards in `ImagickHandler::load()`/`save()`, JXL quality/lossless via `resolveQuality()`/`isLossless()`, `GdHandler::save()` refusal for `.jxl` (FR-101-05, FR-101-06, NFR-101-03).  
  _Verification commands:_  
  - `php artisan test --filter="GdHandlerJxlTest|ImagickHandlerJxlTest|PhotosAddHandler"`  
  - `make phpstan`

- [x] T-101-07 – Tests: `ImagickJxlCheckTest` (probe mocked): info line when unsupported, error when unsupported and `size_variant_format = jxl`, nothing when supported (FR-101-07, S-101-09..11). Red before T-101-08.  
  _Verification commands:_  
  - `php artisan test --filter=ImagickJxlCheckTest`

- [x] T-101-08 – `ImagickJxlCheck` registered after `ImagickPdfCheck` in `App\Actions\Diagnostics\Errors` (FR-101-07).  
  _Verification commands:_  
  - `php artisan test --filter="ImagickJxlCheckTest|Diagnostics"`  
  - `make phpstan`

- [x] T-101-09 – Docker: pinned source build of ImageMagick 7.1.x and imagick against `libjxl-dev` in an `imagemagick` stage (`/opt/imagemagick`, Debian `policy.xml`); production stage copies it, installs runtime libraries, enables imagick; policy path in `Dockerfile` and `ImagickPdfCheck::IMAGICK_POLICY_LOCATIONS`; build-time JXL round-trip check; record format list and image size in plan.md (FR-101-11, NFR-101-01, NFR-101-05, S-101-13; ADR-101-01).  
  _Verification commands:_  
  - `docker build -t lychee-jxl .`  
  - `docker run --rm lychee-jxl php -r "exit(count(Imagick::queryFormats('JXL')) === 1 ? 0 : 1);"`  
  - `php artisan test --filter=ImagickPdfCheck`

- [x] T-101-10 – Fixture FX-101-01 `tests/Samples/jxl.jxl` generated inside the T-101-09 image (`magick night.jpg -resize 800x -quality 80 jxl.jxl`), size < 100 kB.  
  _Verification commands:_  
  - `file --mime-type tests/Samples/jxl.jxl` → `image/jxl`

- [x] T-101-11 – Image-processing cases in `tests/ImageProcessing/Image/Handlers/BaseImageHandler.php` (skipped without JXL delegate): S-101-01..04 asserting extensions and JXL/WebP/JPEG signatures, width/height populated (FR-101-01, 03, 04, 05, 08, 09).  
  _Verification commands:_  
  - `php artisan test --filter=PhotosAddHandler` (locally: JXL cases skip)  
  - same command inside the T-101-09 image: JXL cases run and pass

- [x] T-101-12 – Translations and docs: `size_variant_format` details with the JXL warning in every `lang/*/all_settings.php`; `docs/specs/3-reference/image-processing.md` JXL section (incl. FR-101-10 browser caveat); knowledge map; roadmap (G4, Documentation Deliverables).  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`

- [x] T-101-13 – Quality gate and drift gate: `vendor/bin/php-cs-fixer fix`, `vp check --fix`, `vp run typecheck`, scoped tests of every touched class, `make phpstan`; drift gate report in plan.md; prepare the commit.

## Notes / TODOs
- JXL encode/decode tests cannot run on the local box (Debian 13 ImageMagick without JXL) nor on GitHub's Ubuntu runners; they run inside the new Docker image.
