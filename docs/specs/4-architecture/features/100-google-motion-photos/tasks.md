# Feature 100 Tasks – Google Motion Photos

_Status: Complete_  
_Last updated: 2026-10-31_

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


### I11 – Move the reader to php-exif (ADR-100-01, Q-100-08)
- [x] T-100-18 – Port `App\Metadata\MotionPhoto` to php-exif `PHPExif\MotionPhoto`, run it from `Reader::read()`, tests and usage doc (F-100-01, S-100-01 … 10).  
  _Intent:_ branch `feat/google-motion-photos` on `mlapaglia/php-exif`, from `upstream/master`.  
  _Verification commands (php-exif):_  
  - `vendor/bin/phpunit -c phpunit.xml.dist --no-coverage` (464 tests; only the pre-existing `ExifTest::testAdapterConsistency` GPS failure)  
  - `vendor/bin/phpstan analyze` (level 7)  
  - `composer check-code-style`, `composer validate-files`  
  _Notes:_ exiftool tests only pass in the full run: `ExiftoolProcOpenTest` overrides `proc_open` and enables the real one as a side effect.  
- [x] T-100-19 – Open the php-exif PR; after release, bump `lychee-org/php-exif` in Lychee (owner approval), delete `app/Metadata/MotionPhoto` and its unit tests, drop the locator call in `Extractor`, point `ExtractMotionPhotos` at `PHPExif\MotionPhoto\MotionPhotoLocator` (F-100-01, F-100-04).  
  _Verification commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest`, `--filter=ExtractMotionPhotosTest`, `make phpstan`.  
  _Notes:_ php-exif PR opened 2026-10-18: LycheeOrg/php-exif#83, merged and released as 1.5.0 on 2026-10-19. Done 2026-10-19: upstream merged, `lychee-org/php-exif` bumped to `^1.5.0` (owner approval), suites and PHPStan green. Done 2026-10-19 (owner approved the paths): deleted `app/Metadata/MotionPhoto/` (7 files) and `tests/Unit/Metadata/MotionPhoto/` (3 files, now in php-exif), dropped the locator call in `Extractor`, `ExtractMotionPhotos` uses `PHPExif\MotionPhoto\MotionPhotoLocator`; spec, knowledge map and lifecycle doc updated. Suites and PHPStan green.  


### I12 – Transcode HEVC clips (Q-100-09)
- [x] T-100-20 – Failing tests: stored clip is H.264 after uploading the Pixel (HEVC) sample and the v1 (H.264) sample; `--reencode` turns a stored HEVC clip into H.264, `--dry-run` with it changes nothing, Apple pairs are not candidates (F-100-02, F-100-04, S-100-21, S-100-22, S-100-23).  
  _Verification commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest`, `--filter=ExtractMotionPhotosTest` (red)  
- [x] T-100-21 – `H264MOVFormat`, codec-based format choice in `GoogleMotionPictureHandler::saveVideoStream()`, `--reencode` in `lychee:extract_motion_photos` (F-100-02, F-100-04).  
  _Verification commands:_  
  - all four `BaseImageHandler` subclasses, `ExtractMotionPhotosTest`, `PhotoAddTest`, `DownloadTest`  
  - `make phpstan`, `vendor/bin/php-cs-fixer fix`  

### I13 – Review follow-up: staged clip write
- [x] T-100-22 – Failing test `testReencodeKeepsTheStoredClipWhenTheWriteFails` (a directory blocks the staging path), then stage the clip at `<target>.part` and move it over the target in `ExtractMotionPhotos::extract()` (F-100-04, S-100-24).  
  _Verification commands:_ `php artisan test --filter=ExtractMotionPhotosTest` (red, then 13/13), `vendor/bin/php-cs-fixer fix`, `make phpstan`.  

### I14 – Lightbox live button in the dock (FR-100-06)
- [x] T-100-23 – Failing Vitest `useLivePhotoToggle.test.ts`, then `resources/js/composables/photo/useLivePhotoToggle.ts` (shared by v7 and v8) (F-100-06, S-100-25).  
  _Verification commands:_ `npx vp test useLivePhotoToggle` (red, then green)  
- [x] T-100-24 – `PhotoState.live_photo_controls` / `is_live_playing`, `PhotoBox` without native controls in the main lightbox, `Dock` live button, `PhotoPanel` dock gate for viewers (F-100-06, S-100-25, S-100-26, UI-100-04).  
  _Verification commands:_ `npx vp check` (no `--fix`), `npx vp run typecheck`, manual lightbox check.  
  _Notes:_ 2026-10-31: `npx vp test useLivePhotoToggle` 5/5, `npx vp check` on the six files clean, `npx vp run typecheck` green. Manual lightbox check passed: owner tested in Firefox and Chrome on 2026-10-31.  
- [x] T-100-25 – Same change in v7: `PhotoBox` `is-live-dock-enabled` prop, `Dock` live button (`pi-bullseye`) and `can-edit` gate, `PhotoPanel` dock gate for viewers; composable moved to `resources/js/composables/photo/` (F-100-06, S-100-25, S-100-26, UI-100-04).  
  _Verification commands:_ `npx vp test useLivePhotoToggle`, `npx vp check` (no `--fix`), `npx vp run typecheck`, manual v7 lightbox check.  
  _Notes:_ 2026-10-31: `npx vp test useLivePhotoToggle` 5/5, `npx vp check` on the seven touched files clean, `npx vp run typecheck` green. Manual check passed: owner tested in Firefox and Chrome on 2026-10-31.  
- [x] T-100-26 – Owner feedback: the motion must loop until the button is turned off, and nothing played in Firefox. Rewrote `useLivePhotoToggle` (failing tests first: registration, toggle, `stop()`, release, slide-transition ownership) to own the store registration; both `PhotoBox` versions show a looping `<video>` over the still instead of driving LivePhotosKit (F-100-06, S-100-25, S-100-26).  
  _Verification commands:_ `npx vp test useLivePhotoToggle` (4 red, then 5/5), `npx vp test` (108), `npx vp check` on the nine touched files, `npx vp run typecheck`.  
  _Notes:_ Diagnosis with a scratch Playwright page over `train.jpg`/`train.mov` and a Google clip extracted from `google_motion_photo.jpg`: in Chromium LivePhotosKit `play()` returns `false` right after `ended` and only restarts on the next `canplay` (~350 ms on the still); on the Google clip it threw `Offset is outside the bounds of the DataView` and stayed at `currentTime` 0.004 while `isPlaying` was true. A plain looping `<video>` wrapped continuously on both clips. Firefox could not be launched in the agent sandbox; owner tested in Firefox and Chrome on 2026-10-31: working.  
- [x] T-100-27 – Owner feedback: the `<video>` was hidden under `.lpk-live-photo-renderer` (its layers carry z-index 1–4 and `#livephoto` formed no stacking context). Added `isolate` to the live photo divs in both `PhotoBox` versions (F-100-06, S-100-25).  
  _Verification commands:_ scratch Playwright page in Chromium: `elementFromPoint` at the centre is `CANVAS.lpk-photo` without `isolate`, `VIDEO` with it; `npx vp check` on both `PhotoBox` files, `npx vp run typecheck`.  
- [x] T-100-28 – PR #4933 review: `DockButton` (v7 and v8) renders a `<button type="button">` instead of an `<a>` without `href`, so every dock button is focusable and works with Enter/Space; the live button carries `aria-label` (`gallery.motion_photo`) and `aria-pressed` (`is_live_playing`) (F-100-06, NFR-100-04).  
  _Verification commands:_ `npx vp check` on both `Dock.vue` and `DockButton.vue`, `npx vp run typecheck`.  
  _Notes:_ Tailwind preflight (imported by `app.css` and `app-v8.css`) resets the button chrome, so the dock looks unchanged.  
- [x] T-100-29 – PR #4933 second review: every icon-only dock button (v7 and v8) gets a translated `aria-label` (highlight/unhighlight, album header, watermark, face scan, rotate, move, delete); new `gallery.photo.actions.rotate_ccw`/`rotate_cw` keys in the 23 locales; the hard-coded "Watermark" tooltip uses `gallery.album.hero.watermark`. Docs aligned: plan scope, composable ownership in FR-100-06 and I14, UI-100-04, knowledge-map lightbox summary (F-100-06, NFR-100-04).  
  _Verification commands:_ `php artisan test --filter=LangTest`, `npx vp check` on both `Dock.vue`, `npx vp run typecheck`.  

## Notes / TODOs
- 2026-10-17: local toolchain installed (PHP 8.4 + imagick, Composer, exiftool, FFmpeg, `vendor/`, `node_modules/`); `vp` is not global, run it as `npx vp …` (e.g. `npx vp check` (never `--fix`: it rewrites unrelated files), `npx vp run typecheck`, `npx vp test`).
- Image-processing tests skip silently without exiftool/FFmpeg (`assertHasExifToolOrSkip`, `assertHasFFMpegOrSkip`): a "green" run without them proves nothing for T-100-08/09/11/15.
