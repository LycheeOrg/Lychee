# Feature 100 Tasks – Google Motion Photos

_Status: Complete_  
_Last updated: 2026-10-17_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Fixtures
- [x] T-100-01 – Build synthetic Motion Photo 1.0 and Ultra HDR fixtures (F-100-01, S-100-01, S-100-03).  
  _Intent:_ `tests/Samples/build_motion_photo_fixtures.php` derives `google_motion_photo_v1.jpg` and `google_motion_photo_ultrahdr.jpg` from `google_motion_photo.jpg` (cut the legacy trailer, rewrite XMP with `Container:Directory`, drop `MicroVideoOffset`); constants in `tests/Constants/TestConstants.php`.  
  _Verification commands:_  
  - `php tests/Samples/build_motion_photo_fixtures.php`  
  - `exiftool -a -G1 -XMP-GCamera:all -XMP-GContainer:all tests/Samples/google_motion_photo_v1.jpg tests/Samples/google_motion_photo_ultrahdr.jpg`  
  _Notes:_ FX-100-01, 02, 04. Each file < 5 MB.

### I2 – JPEG XMP segment reader
- [x] T-100-02 – Failing `JpegXmpReaderTest` (F-100-01, S-100-10).  
  _Intent:_ cases: not a JPEG, truncated marker, segment length beyond EOF, XMP APP1 present, absent, non-XMP APP1 before XMP, stop at SOS.  
  _Verification commands:_ `php artisan test --filter=JpegXmpReaderTest` (red)  
- [x] T-100-03 – Implement `App\Metadata\MotionPhoto\JpegXmpReader` (F-100-01, S-100-10).  
  _Verification commands:_  
  - `php artisan test --filter=JpegXmpReaderTest`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ license header; bounds every read by the file size (ADR-100-01 security).

### I3 – XMP parser
- [x] T-100-04 – Failing `MotionPhotoXmpTest` (F-100-01, S-100-01, S-100-03, S-100-04, S-100-09, S-100-10).  
  _Intent:_ v1 directory, Ultra HDR directory (use the exact Pixel XMP from the spec Appendix: `Primary` without `Item:Length`, attributes in varying order), `MotionPhoto=1` without video item, legacy `MicroVideoOffset`, `Item:*` as attributes and as child elements, malformed XML, unrelated XMP.  
  _Verification commands:_ `php artisan test --filter=MotionPhotoXmpTest` (red)  
- [x] T-100-05 – Implement `App\Metadata\MotionPhoto\MotionPhotoXmp` readonly record + `fromXml()` (F-100-01).  
  _Verification commands:_  
  - `php artisan test --filter=MotionPhotoXmpTest`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ `xml_parser_create_ns` (ext-xml); no new extension (NFR-100-03).

### I4 – Locator
- [x] T-100-06 – Failing `MotionPhotoLocatorTest` (F-100-01, S-100-04, S-100-05, S-100-08).  
  _Intent:_ bytes generated in the test: valid declared length; declared length → non-`ftyp` with and without `MotionPhoto=1`; scan hit; scan miss; plain JPEG with trailing `ftyp` and no motion XMP → 0; 20 MB file peak memory delta < 2 MB (NFR-100-02).  
  _Verification commands:_ `php artisan test --filter=MotionPhotoLocatorTest` (red)  
- [x] T-100-07 – Implement `App\Metadata\MotionPhoto\MotionPhotoLocator` (F-100-01, NFR-100-02, NFR-100-05).  
  _Verification commands:_  
  - `php artisan test --filter=MotionPhotoLocatorTest`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  

### I5 – Extractor wiring
- [x] T-100-08 – Failing upload tests in `BaseImageHandler` (F-100-01, F-100-02, S-100-01, S-100-03, S-100-07, S-100-09).  
  _Intent:_ `testGoogleMotionPhotoV1Upload`, `testGoogleMotionPhotoUltraHdrUpload`, `testGoogleMotionPhotoNoFfmpegUpload`; legacy sample on the Imagick reader extracts its video.  
  _Verification commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest` (red for the new cases)  
- [x] T-100-09 – Call the locator from `Extractor::createFromFile` (F-100-01, F-100-02, S-100-02, S-100-06).  
  _Intent:_ `image/jpeg` and `micro_video_offset === 0` → `MotionPhotoLocator::locate()`; doc comment of `$micro_video_offset` (DO-100-01).  
  _Verification commands:_  
  - `php artisan test --filter=PhotosAddHandlerImagickTest`  
  - `php artisan test --filter=PhotoAddTest`  
  - `php artisan test --filter=DownloadTest`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ run every `BaseImageHandler` subclass; existing GMP tests must stay green.

### I6 – Backfill command
- [x] T-100-10 – Failing `ExtractMotionPhotosTest` (F-100-04, S-100-11, S-100-12, S-100-13, S-100-14).  
  _Intent:_ extract + idempotent second run, `--dry-run`, `--album` valid/unknown, FFmpeg disabled → exit 1, per-photo failure counted.  
  _Verification commands:_ `php artisan test --filter=ExtractMotionPhotosTest` (red)  
- [x] T-100-11 – Implement `lychee:extract_motion_photos` + shared "video path next to original" helper used by `PlaceGoogleMotionVideo` (F-100-04).  
  _Verification commands:_  
  - `php artisan test --filter=ExtractMotionPhotosTest`  
  - `php artisan test --filter=PhotosAddHandlerImagickTest`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  

### I7 – Motion badge
- [x] T-100-12 – `gallery.motion_photo` lang key (23 locales) + badge in v8 `PhotoThumb.vue` and `PhotoThumbVirtual.vue` (F-100-03, S-100-17, UI-100-01, NFR-100-04).  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`  
  - `npx vp check` (non-mutating; format only the changed files with `npx vp fmt <file>`)  
  - `vp run typecheck`  

### I8 – Hover playback
- [x] T-100-13 – Failing Vitest `useMotionHover.test.ts` (F-100-03, F-100-05, S-100-15, S-100-16, S-100-17, S-100-18, S-100-19, S-100-20).  
  _Intent:_ 250 ms intent timer, cancel on leave, `(hover: hover) and (pointer: fine)` gate, reduced-motion gate, error reset; loader called once and only when the URL is missing; playback at max(250 ms, URL arrival); leave before URL → nothing; loader rejection → nothing; no loader injected → URL-present tiles only.  
  _Verification commands:_ `vp test` (red)  
- [x] T-100-17 – `motionUrlLoader.ts` injection key + `provide` in the album panel (`AlbumState.loadPhotoDetails`), `Timeline.vue` and `Search.vue` (`loadPhotoDetailsV3`); `Flow.vue` renders no `PhotoThumb`, so it provides none (F-100-05, S-100-19).  
  _Verification commands:_  
  - `npx vp check` (non-mutating; format only the changed files with `npx vp fmt <file>`)  
  - `vp run typecheck`  
  _Notes:_ numbered after T-100-16 to keep IDs stable; do it before T-100-14.  
- [x] T-100-14 – Implement `resources/js/v8/composables/photo/useMotionHover.ts` and the overlay `<video muted loop playsinline>` in `PhotoThumb.vue` and `PhotoThumbVirtual.vue` (F-100-03, F-100-05, S-100-15 … 20, NFR-100-01).  
  _Verification commands:_  
  - `vp test`  
  - `npx vp check` (non-mutating; format only the changed files with `npx vp fmt <file>`)  
  - `vp run typecheck`  
  - Manual: Chrome, Firefox, Safari desktop + one phone, in album, timeline, search and flow views; network panel shows no `.mov` before hover and one details request on the first hover of a photo.  

### I9 – Real sample
- [x] T-100-15 – `testGoogleMotionPhotoPixelUpload` with `google_motion_photo_pixel.jpg` (F-100-01, S-100-01, S-100-03).  
  _Intent:_ upload → live photo with `.mov`; register `SAMPLE_FILE_GMP_PIXEL_IMAGE` in `TestConstants`.  
  _Verification commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest`  
  _Notes:_ FX-100-03 placed 2026-10-17 (privacy-stripped, full resolution, see spec Fixtures). Sample is an Ultra HDR file, so it also covers S-100-03 with real camera output.

### I10 – Docs and drift gate
- [x] T-100-16 – Knowledge map, `1-concepts/photos.md`, `request-lifecycle-photo-upload.md`, roadmap; Implementation Drift Gate report in plan.md.  
  _Verification commands:_ re-run every command above.

## Notes / TODOs
- 2026-10-17: local toolchain installed (PHP 8.4 + imagick, Composer, exiftool, FFmpeg, `vendor/`, `node_modules/`); `vp` is not global, run it as `npx vp …` (e.g. `npx vp check` (never `--fix`: it rewrites unrelated files), `npx vp run typecheck`, `npx vp test`).
- Image-processing tests skip silently without exiftool/FFmpeg (`assertHasExifToolOrSkip`, `assertHasFFMpegOrSkip`): a "green" run without them proves nothing for T-100-08/09/11/15.
