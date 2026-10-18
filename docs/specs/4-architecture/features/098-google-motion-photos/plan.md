# Feature Plan 098 – Google Motion Photos

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented  
_Last updated:_ 2026-10-17

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A photo taken on a current Android phone shows its motion in Lychee: in the lightbox (already works once the video is stored) and in the v8 grid on hover. Success signals:
- A Pixel `.MP.jpg` uploads as a live photo on exiftool, Imagick and Native installs (S-098-01, 03, 09).
- Existing libraries gain their videos with `lychee:extract_motion_photos` (S-098-11).
- Grid pages make no video request until hover intent (NFR-098-01).
- Quality gate green: `php-cs-fixer`, `make phpstan`, scoped `php artisan test`, `vp check --fix`, `vp run typecheck`, `vp test`.

## Scope Alignment
- **In scope:** `App\Metadata\MotionPhoto` reader (FR-098-01); `Extractor` wiring (FR-098-01/02); backfill command (FR-098-04); v8 badge and hover playback for every live photo (FR-098-03); fixtures; docs.
- **Out of scope:** Spec Non-Goals (Samsung legacy trailer, HEIC motion photos, presentation timestamp, HEVC transcoding, lightbox player change, v7 grid, touch inline playback).

## Dependencies & Interfaces
- `App\Metadata\Extractor::createFromFile` (call site of the locator), `PanoramaDetector` (style precedent: final class, pure helpers, readonly record).
- Unchanged consumers: `ExtractGoogleMotionPictures`, `GoogleMotionPictureHandler`, `PlaceGoogleMotionVideo`.
- `Detect360` command (cursor/limit/timeout pattern for CLI-098-01).
- v8 `PhotoThumb.vue`, `Virtualized/PhotoThumbVirtual.vue`, `ThumbBadge.vue`; grid stores' details loaders (Q-098-07).
- Tooling: exiftool and FFmpeg for the image-processing tests (they `assertHasExifToolOrSkip` / `assertHasFFMpegOrSkip`).
- ADR-098-01.

## Assumptions & Risks
- **Assumptions:**
  - Pixel files carry the video's `Container:Item` in the standard XMP packet (extended XMP is not read).
  - The owner supplies one Pixel `.MP.jpg` cleared for publication (FX-098-03, Q-098-06).
- **Risks / Mitigations:**
  - _Local toolchain (resolved 2026-10-17):_ installed at the owner's request: Homebrew `php@8.4` 8.4.26 (CI version) + PECL `imagick` 3.8.1, `exiftool` 13.55, `ffmpeg` 9.0.2, `imagemagick` 7.1.2, `ghostscript` (added during T-098-09: the Imagick PDF tests fail with `FailedToExecuteCommand 'gs'` without it); Composer 2.10.3 phar (SHA-256 checked) in `/opt/homebrew/bin`; `composer install` and `npm ci` from the lockfiles (no dependency change). Frontend commands run as `npx vp …`. Baseline: `php artisan test --filter=GoogleMotion` 10/10 passed (not skipped), `npx vp test` 13/13, `npx vp run typecheck` green.
  - _Some files lack the directory item_ (issue #1283 dump): covered by the `ftyp` scan (S-098-04).
  - _False-positive `ftyp` in JPEG entropy data:_ scan only when `MotionPhoto="1"`, require size 8…256 and a whitelisted brand; FFmpeg's open then rejects garbage (existing broken-video path).
  - _HEVC Apple clips in the grid on Firefox:_ fall back to the still (S-098-18), accepted in Q-098-03.
  - _v3 grid tiles have no URL at hover:_ Q-098-07 resolved (Option A): each view provides its store's deduped details loader; the first hover of a photo costs one details request (FR-098-05).

## Implementation Drift Gate
After T-098-15, during T-098-16, compare every FR/NFR/S-ID against the tests named in tasks.md, re-run all verification commands listed there, and record the report (traceability table, divergences, lessons) under "Drift Gate Report" below.

## Increment Map

1. **I1 – Fixtures** (FX-098-01, 02, 04)
   - _Goal:_ Deterministic Motion Photo 1.0 and Ultra HDR sample files.
   - _Steps:_ Build from `tests/Samples/google_motion_photo.jpg`: cut its MP4 trailer (legacy offset 4 495 341), rewrite the XMP APP1 with a `Container:Directory` (Primary / MotionPhoto, and Primary / GainMap / MotionPhoto using a small JPEG as gain map), drop `MicroVideoOffset`. Keep the builder as `tests/Samples/build_motion_photo_fixtures.php` (pure PHP, no dependency); register constants in `TestConstants`.
   - _Commands:_ `php tests/Samples/build_motion_photo_fixtures.php`; `exiftool -a -G1 -XMP-GContainer:all <file>` to eyeball.
   - _Exit:_ both files committed, each < 5 MB, exiftool lists the expected items.
2. **I2 – `JpegXmpReader`** (FR-098-01, S-098-10, NFR-098-02)
   - _Steps:_ failing `tests/Unit/Metadata/JpegXmpReaderTest` (no SOI, truncated segment, segment length > file, XMP present, XMP absent, stops at SOS) → implement.
   - _Commands:_ `php artisan test --filter=JpegXmpReaderTest`, `make phpstan`.
3. **I3 – `MotionPhotoXmp`** (FR-098-01, S-098-01, 03, 04, 09, 10)
   - _Steps:_ failing `MotionPhotoXmpTest` (v1 directory, Ultra HDR directory, `MotionPhoto=1` without item, legacy offset, attribute vs element syntax for `Item:*`, malformed XML, unrelated XMP) → implement with `xml_parser_create_ns`.
   - _Commands:_ `php artisan test --filter=MotionPhotoXmpTest`, `make phpstan`.
4. **I4 – `MotionPhotoLocator`** (FR-098-01, S-098-04, 05, 08, NFR-098-02)
   - _Steps:_ failing `MotionPhotoLocatorTest` on bytes generated in the test (valid length; length → non-`ftyp`; scan hit; scan miss; no motion XMP + trailing `ftyp` → 0; 20 MB memory check) → implement `ftyp` matcher + chunked scan + composition.
   - _Commands:_ `php artisan test --filter=MotionPhotoLocatorTest`, `make phpstan`.
5. **I5 – `Extractor` wiring + upload tests** (FR-098-01/02, S-098-01 … 07, 09)
   - _Steps:_ failing `BaseImageHandler` tests `testGoogleMotionPhotoV1Upload`, `testGoogleMotionPhotoUltraHdrUpload`, `testGoogleMotionPhotoNoFfmpegUpload`; then call the locator from `Extractor::createFromFile` for `image/jpeg` when `micro_video_offset === 0`; update the field doc comment (DO-098-01).
   - _Commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest`, `--filter=PhotosAddHandlerGdTest` (whichever `BaseImageHandler` subclasses exist), `--filter=PhotoAddTest`, `--filter=DownloadTest`, `make phpstan`.
6. **I6 – Backfill command** (FR-098-04, S-098-11 … 14)
   - _Steps:_ failing `tests/ImageProcessing/Commands/ExtractMotionPhotosTest` → `app/Console/Commands/ImageProcessing/ExtractMotionPhotos.php`, extracting the "video path next to original" computation of `PlaceGoogleMotionVideo` into a small shared helper used by both.
   - _Commands:_ `php artisan test --filter=ExtractMotionPhotosTest`, `--filter=PhotosAddHandlerImagickTest`, `make phpstan`.
7. **I7 – Motion badge + lang key** (FR-098-03, UI-098-01, NFR-098-04)
   - _Steps:_ `gallery.motion_photo` in the 23 `lang/*/gallery.php`; `ThumbBadge` in `PhotoThumb.vue` and `PhotoThumbVirtual.vue`.
   - _Commands:_ `php artisan test --filter=LangTest`, `php scripts/check_translations.php` (if present), `vp check --fix`, `vp run typecheck`.
8. **I8 – `useMotionHover` composable, URL loader, hover overlay** (FR-098-03, FR-098-05, S-098-15 … 20, NFR-098-01)
   - _Steps:_ failing Vitest `useMotionHover.test.ts` (250 ms intent, cancel on leave, hover/pointer media gate, reduced motion, error reset, loader called once only when the URL is missing, late URL, leave before URL, loader rejection) → composable in `resources/js/v8/composables/photo/useMotionHover.ts`; `motionUrlLoader.ts` injection key and `provide` in the album panel, `Timeline.vue`, `Search.vue`, `Flow.vue`; overlay `<video muted loop playsinline>` in both thumbs.
   - _Commands:_ `vp test`, `vp check --fix`, `vp run typecheck`; manual check on Chrome, Firefox, Safari, one phone, in album, timeline, search and flow views.
9. **I9 – Real-sample test** (FX-098-03, S-098-01, S-098-03)
   - _Steps:_ `testGoogleMotionPhotoPixelUpload` on `google_motion_photo_pixel.jpg` (placed 2026-10-17).
   - _Commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest`.
10. **I10 – Docs + drift gate**
    - _Steps:_ knowledge map, `1-concepts/photos.md`, `request-lifecycle-photo-upload.md`, roadmap; drift gate report.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-098-01 | I3, I5, I9 / T-098-04, T-098-08, T-098-15 | `MotionPhotoXmpTest`, `testGoogleMotionPhotoV1Upload`, `testGoogleMotionPhotoPixelUpload` |
| S-098-02 | I5 / T-098-08 | existing `testGoogleMotionPhotoUpload` |
| S-098-03 | I3, I5 / T-098-04, T-098-08 | `testGoogleMotionPhotoUltraHdrUpload` |
| S-098-04 | I3, I4 / T-098-04, T-098-06 | `MotionPhotoLocatorTest` scan hit |
| S-098-05 | I4 / T-098-06 | length → non-`ftyp` |
| S-098-06 | I5 / T-098-08 | existing `testBrokenGoogleMotionPhotoUpload` |
| S-098-07 | I5 / T-098-08 | `testGoogleMotionPhotoNoFfmpegUpload` |
| S-098-08 | I4 / T-098-06 | no motion XMP + trailing `ftyp` |
| S-098-09 | I3, I5 / T-098-04, T-098-08 | legacy via XMP on Imagick reader |
| S-098-10 | I2, I3 / T-098-02, T-098-04 | malformed input |
| S-098-11 … 14 | I6 / T-098-10 | `ExtractMotionPhotosTest` |
| S-098-15 … 18 | I8 / T-098-13, T-098-14 | `useMotionHover.test.ts` + manual |
| S-098-19, 20 | I8 / T-098-13, T-098-17 | `useMotionHover.test.ts` + manual per view |

## Analysis Gate

Run 2026-10-17 by the agent (owner review pending).

1. Specification completeness — ✅ objectives, FR, NFR populated; Q-098-01 … 06 folded into FR/NFR/Non-Goals; ASCII mock-up present.
2. Open questions review — ✅ Q-098-01 … 07 resolved (Q-098-07 Option A, owner 2026-10-17, folded into FR-098-03/05, S-098-19/20). ADR-098-01 written for Q-098-02; Q-098-07 stays inside existing contracts, no ADR.
3. Plan alignment — ✅ plan links spec and tasks; success criteria match the spec.
4. Tasks coverage — ✅ every FR maps to tasks (FR-098-01: T-098-01…09, T-098-15; FR-098-02: T-098-08/09; FR-098-03: T-098-12…14; FR-098-04: T-098-10/11; FR-098-05: T-098-13, T-098-17); tests precede code in every increment; failure branches listed (S-098-05, 06, 07, 10, 13, 14, 18).
5. Working-agreement compliance — ✅ no dependency change; locator split into pure helpers (NFR-098-05); ADR-098-01 reviewed. No other ADR references Feature 098.
6. Tooling readiness — ✅ commands documented per increment; local toolchain installed and baseline green 2026-10-17 (see Risks).

**Outcome:** cleared for I1–I10 (owner's sample placed 2026-10-17). Re-run 2026-10-17 after Q-098-07 and the toolchain install: no new findings.

## Exit Criteria
- All tasks `[x]`; quality gate green (PHP + frontend); `vp test` green.
- `lychee.d.ts` unchanged (no resource change, Q-098-07 Option A).
- Roadmap, knowledge map, concept and lifecycle docs updated; drift gate report below.
- Open questions all resolved.

## Follow-ups / Backlog
- Samsung legacy `MotionPhoto_Data` trailer.
- HEIC primary motion photos.
- Start the hover clip at `MotionPhotoPresentationTimestampUs`.
- v7 grid playback, if requested.

## Intent Log
- 2026-10-17: owner asked for a plan for Google motion photos (#753, #1283) with grid hover or play button. Agent surveyed the live photo pipeline (Explore agent + direct reads of `GoogleMotionPictureHandler`, `ExtractGoogleMotionPictures`, `Extractor`, php-exif 1.4.0 mapper via `gh api`), parsed the existing sample's XMP with a Python one-off (legacy format confirmed), drafted spec + Q-098-01 … 06. Owner chose Option A for all six. While planning FR-098-03 the agent found v3 tiles lack `live_photo_url` until tier-3 details load → Q-098-07; owner chose Option A (details on hover) → FR-098-05, T-098-17. Owner supplied a Pixel 11 Pro motion photo; agent stripped GPS, C2PA and the HDR+ maker note with exiftool (owner asked for GPS removal so they cannot be identified; C2PA/maker note removed as device fingerprints), verified trailer bytes identical and inspected the still and video frames for identifying content (none), placed it as FX-098-03 at full resolution; findings (Primary item without `Item:Length`, HEVC video) folded into the spec Appendix. Grep of v8 thumbnail hosts: `PhotoThumbPanel`, `PhotoThumbPanelVirtual`, `TimelineDatesV3`, `ResultPanel`, views `Timeline`, `Search`, `Flow`; Ken Burns hover is CSS-only, no conflict.

## Drift Gate Report

Run 2026-10-17 by the agent after T-098-01 … T-098-17 (owner acknowledgement pending).

**Preconditions:** 17/17 tasks `[x]`. Quality gate green on the final tree: `vendor/bin/php-cs-fixer fix --dry-run` (0 of 2994 files), `make phpstan` (no errors, 3156 files), `npx vp check` (0 errors; 18 pre-existing warnings, none in feature files), `npx vp run typecheck`, `npx vp test` (31/31), `php scripts/check_translations.php` (gallery.php complete).

**Traceability**

| Requirement | Code | Tests (latest run, all green) |
|-------------|------|-------------------------------|
| FR-098-01 | `app/Metadata/MotionPhoto/{JpegXmpReader,JpegHeader,MotionPhotoXmp,MotionPhotoXmpHandler,FtypBox,MotionPhotoLocator}.php`, `Extractor::createFromFile()` | `JpegXmpReaderTest` 13, `MotionPhotoXmpTest` 18, `MotionPhotoLocatorTest` 18 (incl. Pixel, v1, Ultra HDR, legacy samples, 20 MB memory check) |
| FR-098-02 | unchanged pipes; `PlaceGoogleMotionVideo` uses `GoogleMotionPictureHandler::videoPathNextTo()` | `BaseImageHandler::testGoogleMotionPhoto{V1,UltraHdr,Pixel,WithoutFFMpeg}Upload`, `testLegacyGoogleMotionPhotoWithoutExifToolUpload`, existing GMP tests, in GD/Imagick/RawUploadImagick/Watermarker (34/40/37/39); `PhotoAddTest` 10, `DownloadTest` 15 |
| FR-098-03 | `ThumbBadge.vue` (`label`), `PhotoThumb.vue`, `PhotoThumbVirtual.vue`, `useMotionHover.ts`, 23 × `lang/*/gallery.php` | `useMotionHover.test.ts` 18; `LangTest` 2; browser check below |
| FR-098-04 | `app/Console/Commands/ImageProcessing/ExtractMotionPhotos.php` | `ExtractMotionPhotosTest` 8 |
| FR-098-05 | `motionUrlLoader.ts`; `provide` in `AlbumPanel.vue`, `Timeline.vue`, `Search.vue` | `useMotionHover.test.ts` (loader cases); browser check below |
| NFR-098-01 | `<video>` rendered only after intent | browser: 0 `.mov` requests on load and after a 120 ms sweep |
| NFR-098-02 | `JpegXmpReader` stops at SOS, `FtypBox::scan` 64 KiB chunks | `MotionPhotoLocatorTest::testScanUsesConstantMemory` |
| NFR-098-03 | no `composer.json`/`package.json` change | `git diff` |
| NFR-098-04 | `canPlayMotionOnHover()`, muted video, badge `aria-label` | Vitest gate cases; browser reduced-motion context |
| NFR-098-05 | small pure helpers | PHPStan, review |

**Browser check (scratch instance, 2026-10-17):** separate SQLite file and `public/uploads-motion/` via env overrides, `NUXT_UI_ENABLED` + `STRUCT_OF_ARRAY_ENABLED`, samples imported with `lychee:sync` (all four Google files and the Apple pair became live photos). Google Chrome via Playwright `channel: "chrome"`: 5 badges, none on the plain photo; first hover on the Pixel tile sent exactly one `GET /api/v3/Albums/{id}/Photos/details?photo_ids[]=…` and one `.mov` request, the HEVC clip played muted (`readyState` 4, time advancing, opacity 1); leave removed the element; second hover sent no details request; reduced motion showed badges only; a `.mov` routed to non-video bytes was dropped with the still visible, no toast, no console error; the lightbox shows LivePhotosKit's LIVE control for the Pixel photo. Not done: Firefox, Safari, a touch device (owner).

**Divergences:** all low impact, fixed in the spec: `JpegXmpReader` returns `JpegHeader` (SVC-098-01); FR-098-04 output strings, dry run without FFmpeg, missing original counted as failed, remote originals downloaded (branch not testable with `Storage::fake`, `@codeCoverageIgnore` like `SizeVariantDefaultFactory`); FR-098-05 providers are Album/Timeline/Search (Flow renders no `PhotoThumb`; tag/favourites carry the URL); badge sits after the 360° badge; badge label via new `ThumbBadge` `label` prop; fade-in uses `isPlaying` from the composable. Over-delivery: legacy motion photos now work without exiftool (S-098-09), covered by tests. No high/medium divergence; no new open question.

**Lessons**
- `npx vp check --fix` fixes the whole tree: it rewrote five unrelated files and its "useless escape" fix broke both `EmbedCodeDialog.vue` (`<\/script>` inside a template literal of a `.vue` `<script>` block must stay escaped). Reverted; use `npx vp check` (no `--fix`) or review `git status` after a fix run. Worth an Oxlint rule override or an AGENTS.md note (owner decision).
- The Imagick PDF tests need Ghostscript locally (`FailedToExecuteCommand 'gs'`).
- Playwright's bundled Chromium lacks proprietary codecs (and the cached build lagged the installed Playwright); `channel: "chrome"` gives real H.264/HEVC playback.
