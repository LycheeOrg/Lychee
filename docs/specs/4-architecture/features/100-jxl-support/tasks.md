# Feature 100 Tasks – JPEG XL Support

_Status: Draft_  
_Last updated: 2026-10-18_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist
- [ ] T-100-01 – Unit tests: JXL extension/MIME maps and magic-byte detection (FR-100-01, FR-100-02, S-100-05, S-100-06).  
  _Intent:_ `tests/Unit/Services/Image/FileExtensionServiceJxlTest.php` with synthetic codestream (`FF 0A`) and container headers written to temp files; `.jxl` with JPEG bytes rejected. Red before T-100-02.  
  _Verification commands:_  
  - `php artisan test --filter=FileExtensionServiceJxlTest`

- [ ] T-100-02 – `FileExtensionService`: `.jxl`/`image/jxl` constants and `isJxl()` magic-byte helper used by `isSupportedImage()` for `.jxl` (FR-100-01, FR-100-02).  
  _Verification commands:_  
  - `php artisan test --filter=FileExtensionService`  
  - `make phpstan`

- [ ] T-100-03 – Unit tests: `size_variant_format` accepts `jxl` (migration `type_range`), naming strategy returns `.jxl` for every generated variant with `jxl` and `.jxl` small/medium + `.jpeg` thumbs for a JXL original under `original` (FR-100-03, FR-100-04, FR-100-09, S-100-02, S-100-03, S-100-12). Red before T-100-04.  
  _Verification commands:_  
  - `php artisan test --filter="SizeVariantFormat|JxlNaming"`

- [ ] T-100-04 – `SizeVariantFormat::JXL`, reversible migration extending `type_range` (down: `jxl` → `original`), `generatedExtension()` arm, `php artisan typescript:transform` (FR-100-03, FR-100-04, NFR-100-02).  
  _Verification commands:_  
  - `php artisan test --filter="SizeVariantFormat|JxlNaming|ConfigsTest"`  
  - `make phpstan`  
  - `vp run typecheck`

- [ ] T-100-05 – Tests: GD refuses `.jxl` targets; Imagick load/save of `.jxl` throws `MediaFileOperationException('JPEG XL requires Imagick with JXL support')` when the probe reports no support (FR-100-06, S-100-07, S-100-08). Red before T-100-06.  
  _Verification commands:_  
  - `php artisan test --filter="GdHandlerJxlTest|ImagickHandlerJxlTest"`

- [ ] T-100-06 – `supportsJxl()` probe (single helper), guards in `ImagickHandler::load()`/`save()`, JXL quality/lossless via `resolveQuality()`/`isLossless()`, `GdHandler::save()` refusal for `.jxl` (FR-100-05, FR-100-06, NFR-100-03).  
  _Verification commands:_  
  - `php artisan test --filter="GdHandlerJxlTest|ImagickHandlerJxlTest|PhotosAddHandler"`  
  - `make phpstan`

- [ ] T-100-07 – Tests: `ImagickJxlCheckTest` (probe mocked): info line when unsupported, error when unsupported and `size_variant_format = jxl`, nothing when supported (FR-100-07, S-100-09..11). Red before T-100-08.  
  _Verification commands:_  
  - `php artisan test --filter=ImagickJxlCheckTest`

- [ ] T-100-08 – `ImagickJxlCheck` registered after `ImagickPdfCheck` in `App\Actions\Diagnostics\Errors` (FR-100-07).  
  _Verification commands:_  
  - `php artisan test --filter="ImagickJxlCheckTest|Diagnostics"`  
  - `make phpstan`

- [ ] T-100-09 – Docker: spike (trixie/backports ImageMagick with JXL?), else pinned source build of ImageMagick 7.1.x against `libjxl-dev` in a builder stage; `imagick` linked against it; policy path in `Dockerfile` and `ImagickPdfCheck::IMAGICK_POLICY_LOCATIONS`; JXL smoke step in `.github/workflows/docker_build.yml`; record `magick -list format` diff and image size in plan.md (FR-100-11, NFR-100-01, NFR-100-05, S-100-13; ADR-100-01).  
  _Verification commands:_  
  - `docker build -t lychee-jxl .`  
  - `docker run --rm lychee-jxl php -r "exit(count(Imagick::queryFormats('JXL')) === 1 ? 0 : 1);"`  
  - `php artisan test --filter=ImagickPdfCheck`

- [ ] T-100-10 – Fixture FX-100-01 `tests/Samples/jxl.jxl` generated inside the T-100-09 image (`magick night.jpg -resize 800x -quality 80 jxl.jxl`), size < 100 kB.  
  _Verification commands:_  
  - `file --mime-type tests/Samples/jxl.jxl` → `image/jxl`

- [ ] T-100-11 – Image-processing cases in `tests/ImageProcessing/Image/Handlers/BaseImageHandler.php` (skipped without JXL delegate): S-100-01..04 asserting extensions and JXL/WebP/JPEG signatures, width/height populated (FR-100-01, 03, 04, 05, 08, 09).  
  _Verification commands:_  
  - `php artisan test --filter=PhotosAddHandler` (locally: JXL cases skip)  
  - same command inside the T-100-09 image: JXL cases run and pass

- [ ] T-100-12 – Translations and docs: `size_variant_format` details with the JXL warning in every `lang/*/all_settings.php`; `docs/specs/3-reference/image-processing.md` JXL section (incl. FR-100-10 browser caveat); knowledge map; roadmap (G4, Documentation Deliverables).  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`

- [ ] T-100-13 – Quality gate and drift gate: `vendor/bin/php-cs-fixer fix`, `vp check --fix`, `vp run typecheck`, scoped tests of every touched class, `make phpstan`; drift gate report in plan.md; prepare the commit.

## Notes / TODOs
- JXL encode/decode tests cannot run on the local box (Debian 13 ImageMagick without JXL) nor on GitHub's Ubuntu runners; they run inside the new Docker image.
