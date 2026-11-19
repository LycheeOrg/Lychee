# Feature Plan 100 – Google Motion Photos

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented  
_Last updated:_ 2026-10-17

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A photo taken on a current Android phone shows its motion in Lychee: in the lightbox (already works once the video is stored) and in the v8 grid on hover. Success signals:
- A Pixel `.MP.jpg` uploads as a live photo on exiftool, Imagick and Native installs (S-100-01, 03, 09).
- Existing libraries gain their videos with `lychee:extract_motion_photos` (S-100-11).
- Grid pages make no video request until hover intent (NFR-100-01).
- Quality gate green: `php-cs-fixer`, `make phpstan`, scoped `php artisan test`, `npx vp check` (no `--fix`), `vp run typecheck`, `vp test`.

## Scope Alignment
- **In scope:** `App\Metadata\MotionPhoto` reader (FR-100-01); `Extractor` wiring (FR-100-01/02); backfill command (FR-100-04); v8 badge and hover playback for every live photo (FR-100-03); main-lightbox play/stop dock button with a looping `<video>` overlay, v7 and v8 (FR-100-06, I14); fixtures; docs.
- **Out of scope:** Spec Non-Goals (Samsung legacy trailer, HEIC motion photos, presentation timestamp, HEVC transcoding, replacing LivePhotosKit (it still renders the lightbox still and keeps playing in Flow/Moderation, which have no dock), v7 grid, touch inline playback).

## Dependencies & Interfaces
- `App\Metadata\Extractor::createFromFile` (call site of the locator), `PanoramaDetector` (style precedent: final class, pure helpers, readonly record).
- Unchanged consumers: `ExtractGoogleMotionPictures`, `GoogleMotionPictureHandler`, `PlaceGoogleMotionVideo`.
- `Detect360` command (cursor/limit/timeout pattern for CLI-100-01).
- v8 `PhotoThumb.vue`, `Virtualized/PhotoThumbVirtual.vue`, `ThumbBadge.vue`; grid stores' details loaders (Q-100-07).
- Tooling: exiftool and FFmpeg for the image-processing tests (they `assertHasExifToolOrSkip` / `assertHasFFMpegOrSkip`).
- ADR-100-01.

- **Dependency bump (owner approval, 2026-10-19):** `lychee-org/php-exif` `^1.4.0` → `^1.5.0` (lock 1.5.0, commit `05f9945`). Rationale: 1.5.0 ships the motion photo reader moved out of Lychee (LycheeOrg/php-exif#83, ADR-100-01) and runs it from `Reader::read()` for every adapter. Only `lychee-org/php-exif` changed in `composer.lock`; its source URL kept on HTTPS.

## Assumptions & Risks
- **Assumptions:**
  - Pixel files carry the video's `Container:Item` in the standard XMP packet (extended XMP is not read).
  - The owner supplies one Pixel `.MP.jpg` cleared for publication (FX-100-03, Q-100-06).
- **Risks / Mitigations:**
  - _Local toolchain (resolved 2026-10-17):_ installed at the owner's request: Homebrew `php@8.4` 8.4.26 (CI version) + PECL `imagick` 3.8.1, `exiftool` 13.55, `ffmpeg` 9.0.2, `imagemagick` 7.1.2, `ghostscript` (added during T-100-09: the Imagick PDF tests fail with `FailedToExecuteCommand 'gs'` without it); Composer 2.10.3 phar (SHA-256 checked) in `/opt/homebrew/bin`; `composer install` and `npm ci` from the lockfiles (no dependency change). Frontend commands run as `npx vp …`. Baseline: `php artisan test --filter=GoogleMotion` 10/10 passed (not skipped), `npx vp test` 13/13, `npx vp run typecheck` green.
  - _Some files lack the directory item_ (issue #1283 dump): covered by the `ftyp` scan (S-100-04).
  - _False-positive `ftyp` in JPEG entropy data:_ scan only when `MotionPhoto="1"`, require size 8…256 and a whitelisted brand; FFmpeg's open then rejects garbage (existing broken-video path).
  - _HEVC Apple clips in the grid on Firefox:_ fall back to the still (S-100-18), accepted in Q-100-03.
  - _v3 grid tiles have no URL at hover:_ Q-100-07 resolved (Option A): each view provides its store's deduped details loader; the first hover of a photo costs one details request (FR-100-05).

## Implementation Drift Gate
After T-100-15, during T-100-16, compare every FR/NFR/S-ID against the tests named in tasks.md, re-run all verification commands listed there, and record the report (traceability table, divergences, lessons) under "Drift Gate Report" below.

## Increment Map

1. **I1 – Fixtures** (FX-100-01, 02, 04)
   - _Goal:_ Deterministic Motion Photo 1.0 and Ultra HDR sample files.
   - _Steps:_ Build from `tests/Samples/google_motion_photo.jpg`: cut its MP4 trailer (legacy offset 4 495 341), rewrite the XMP APP1 with a `Container:Directory` (Primary / MotionPhoto, and Primary / GainMap / MotionPhoto using a small JPEG as gain map), drop `MicroVideoOffset`. Keep the builder as `tests/Samples/build_motion_photo_fixtures.php` (pure PHP, no dependency); register constants in `TestConstants`.
   - _Commands:_ `php tests/Samples/build_motion_photo_fixtures.php`; `exiftool -a -G1 -XMP-GContainer:all <file>` to eyeball.
   - _Exit:_ both files committed, each < 5 MB, exiftool lists the expected items.
2. **I2 – `JpegXmpReader`** (FR-100-01, S-100-10, NFR-100-02)
   - _Steps:_ failing `tests/Unit/Metadata/JpegXmpReaderTest` (no SOI, truncated segment, segment length > file, XMP present, XMP absent, stops at SOS) → implement.
   - _Commands:_ `php artisan test --filter=JpegXmpReaderTest`, `make phpstan`.
3. **I3 – `MotionPhotoXmp`** (FR-100-01, S-100-01, 03, 04, 09, 10)
   - _Steps:_ failing `MotionPhotoXmpTest` (v1 directory, Ultra HDR directory, `MotionPhoto=1` without item, legacy offset, attribute vs element syntax for `Item:*`, malformed XML, unrelated XMP) → implement with `xml_parser_create_ns`.
   - _Commands:_ `php artisan test --filter=MotionPhotoXmpTest`, `make phpstan`.
4. **I4 – `MotionPhotoLocator`** (FR-100-01, S-100-04, 05, 08, NFR-100-02)
   - _Steps:_ failing `MotionPhotoLocatorTest` on bytes generated in the test (valid length; length → non-`ftyp`; scan hit; scan miss; no motion XMP + trailing `ftyp` → 0; 20 MB memory check) → implement `ftyp` matcher + chunked scan + composition.
   - _Commands:_ `php artisan test --filter=MotionPhotoLocatorTest`, `make phpstan`.
5. **I5 – `Extractor` wiring + upload tests** (FR-100-01/02, S-100-01 … 07, 09)
   - _Steps:_ failing `BaseImageHandler` tests `testGoogleMotionPhotoV1Upload`, `testGoogleMotionPhotoUltraHdrUpload`, `testGoogleMotionPhotoNoFfmpegUpload`; then call the locator from `Extractor::createFromFile` for `image/jpeg` when `micro_video_offset === 0`; update the field doc comment (DO-100-01).
   - _Commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest`, `--filter=PhotosAddHandlerGdTest` (whichever `BaseImageHandler` subclasses exist), `--filter=PhotoAddTest`, `--filter=DownloadTest`, `make phpstan`.
6. **I6 – Backfill command** (FR-100-04, S-100-11 … 14)
   - _Steps:_ failing `tests/ImageProcessing/Commands/ExtractMotionPhotosTest` → `app/Console/Commands/ImageProcessing/ExtractMotionPhotos.php`, extracting the "video path next to original" computation of `PlaceGoogleMotionVideo` into a small shared helper used by both.
   - _Commands:_ `php artisan test --filter=ExtractMotionPhotosTest`, `--filter=PhotosAddHandlerImagickTest`, `make phpstan`.
7. **I7 – Motion badge + lang key** (FR-100-03, UI-100-01, NFR-100-04)
   - _Steps:_ `gallery.motion_photo` in the 23 `lang/*/gallery.php`; `ThumbBadge` in `PhotoThumb.vue` and `PhotoThumbVirtual.vue`.
   - _Commands:_ `php artisan test --filter=LangTest`, `php scripts/check_translations.php` (if present), `npx vp check` (no `--fix`), `vp run typecheck`.
8. **I8 – `useMotionHover` composable, URL loader, hover overlay** (FR-100-03, FR-100-05, S-100-15 … 20, NFR-100-01)
   - _Steps:_ failing Vitest `useMotionHover.test.ts` (250 ms intent, cancel on leave, hover/pointer media gate, reduced motion, error reset, loader called once only when the URL is missing, late URL, leave before URL, loader rejection) → composable in `resources/js/v8/composables/photo/useMotionHover.ts`; `motionUrlLoader.ts` injection key and `provide` in `AlbumPanel.vue`, `Timeline.vue` and `Search.vue` (not `Flow.vue`, which renders no `PhotoThumb`); overlay `<video muted loop playsinline>` in both thumbs.
   - _Commands:_ `vp test`, `npx vp check` (no `--fix`), `vp run typecheck`; manual check on Chrome, Firefox, Safari, one phone, in album, timeline and search views.
9. **I9 – Real-sample test** (FX-100-03, S-100-01, S-100-03)
   - _Steps:_ `testGoogleMotionPhotoPixelUpload` on `google_motion_photo_pixel.jpg` (placed 2026-10-17).
   - _Commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest`.
10. **I10 – Docs + drift gate**
    - _Steps:_ knowledge map, `1-concepts/photos.md`, `request-lifecycle-photo-upload.md`, roadmap; drift gate report.
11. **I14 – Lightbox live button in the dock, v8 then v7** (FR-100-06, S-100-25, S-100-26, UI-100-04)
    - _Steps:_ failing Vitest `useLivePhotoToggle.test.ts` (registration, toggle, `stop()`, release, slide-transition ownership) → shared composable `resources/js/composables/photo/useLivePhotoToggle.ts`, which registers and releases `LivePhotoControls` in `PhotoState`; `PhotoBox` calls it, augments LivePhotosKit with `showsNativeControls: false` in the main lightbox and shows the looping `<video>` overlay while playing; `Dock` live button; `PhotoPanel` shows the dock to non-editors when controls are registered. (The first iteration drove LivePhotosKit `play()`/`stop()`; replaced in T-100-26.)
    - _Commands:_ `npx vp test useLivePhotoToggle`, `npx vp check` (no `--fix`), `npx vp run typecheck`; manual check in the lightbox on an Apple and a Google live photo.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-100-01 | I3, I5, I9 / T-100-04, T-100-08, T-100-15 | `MotionPhotoXmpTest`, `testGoogleMotionPhotoV1Upload`, `testGoogleMotionPhotoPixelUpload` |
| S-100-02 | I5 / T-100-08 | existing `testGoogleMotionPhotoUpload` |
| S-100-03 | I3, I5 / T-100-04, T-100-08 | `testGoogleMotionPhotoUltraHdrUpload` |
| S-100-04 | I3, I4 / T-100-04, T-100-06 | `MotionPhotoLocatorTest` scan hit |
| S-100-05 | I4 / T-100-06 | length → non-`ftyp` |
| S-100-06 | I5 / T-100-08 | existing `testBrokenGoogleMotionPhotoUpload` |
| S-100-07 | I5 / T-100-08 | `testGoogleMotionPhotoNoFfmpegUpload` |
| S-100-08 | I4 / T-100-06 | no motion XMP + trailing `ftyp` |
| S-100-09 | I3, I5 / T-100-04, T-100-08 | legacy via XMP on Imagick reader |
| S-100-10 | I2, I3 / T-100-02, T-100-04 | malformed input |
| S-100-11 … 14 | I6 / T-100-10 | `ExtractMotionPhotosTest` |
| S-100-15 … 18 | I8 / T-100-13, T-100-14 | `useMotionHover.test.ts` + manual |
| S-100-19, 20 | I8 / T-100-13, T-100-17 | `useMotionHover.test.ts` + manual per view |
| S-100-25, 26 | I14 / T-100-23 … T-100-29 | `useLivePhotoToggle.test.ts` + manual |

## Analysis Gate

Run 2026-10-17 by the agent (owner review pending).

1. Specification completeness — ✅ objectives, FR, NFR populated; Q-100-01 … 06 folded into FR/NFR/Non-Goals; ASCII mock-up present.
2. Open questions review — ✅ Q-100-01 … 07 resolved (Q-100-07 Option A, owner 2026-10-17, folded into FR-100-03/05, S-100-19/20). An ADR was written for Q-100-02 (in-house reader; dropped after Q-100-08, see Review Follow-up); Q-100-07 stays inside existing contracts, no ADR.
3. Plan alignment — ✅ plan links spec and tasks; success criteria match the spec.
4. Tasks coverage — ✅ every FR maps to tasks (FR-100-01: T-100-01…09, T-100-15; FR-100-02: T-100-08/09; FR-100-03: T-100-12…14; FR-100-04: T-100-10/11; FR-100-05: T-100-13, T-100-17); tests precede code in every increment; failure branches listed (S-100-05, 06, 07, 10, 13, 14, 18).
5. Working-agreement compliance — ✅ no dependency change; locator split into pure helpers (NFR-100-05); the Q-100-02 ADR reviewed. No other ADR references Feature 100.
6. Tooling readiness — ✅ commands documented per increment; local toolchain installed and baseline green 2026-10-17 (see Risks).

**Outcome:** cleared for I1–I10 (owner's sample placed 2026-10-17). Re-run 2026-10-17 after Q-100-07 and the toolchain install: no new findings.

## Exit Criteria
- All tasks `[x]`; quality gate green (PHP + frontend); `vp test` green.
- `lychee.d.ts` unchanged (no resource change, Q-100-07 Option A).
- Roadmap, knowledge map, concept and lifecycle docs updated; drift gate report below.
- Open questions all resolved.

## Follow-ups / Backlog
- Samsung legacy `MotionPhoto_Data` trailer.
- HEIC primary motion photos.
- Start the hover clip at `MotionPhotoPresentationTimestampUs`.
- v7 grid playback, if requested.

## Intent Log
- 2026-10-31: owner asked to address CodeRabbit's second review of PR #4933: four doc-sync findings (plan scope, ownership in FR-100-06, UI-100-04 wording, knowledge-map contradiction) and translated `aria-label`s for every icon-only dock button, which needed new rotate keys in all 23 locales (T-100-29).
- 2026-10-31: owner tested the dock button in Firefox and Chrome (working) and asked to address the PR #4933 review. CodeRabbit asked to keep T-100-24/25 open until the manual checks passed (now recorded as passed) and to make the live control keyboard-operable: `DockButton` became a `<button type="button">` in v7 and v8, the live button got `aria-label`/`aria-pressed` (T-100-28).
- 2026-10-31: owner reported the motion did not loop until the button was turned off, and that Firefox highlighted the button without playing. Agent probed LivePhotosKit in Chromium with Playwright (Firefox would not start in the sandbox; running it outside was declined), found the gap-after-`ended` and the stall on non-Apple clips, and switched the dock path to a looping `<video>` overlay, the technique the grid hover already uses in Firefox (FR-100-06, T-100-26).
- 2026-10-31: owner asked for the same change in v7. Agent mirrored it in the v7 `PhotoBox`/`Dock`/`PhotoPanel` (new `is-live-dock-enabled` prop, as v7 `PhotoBox` has no zoom prop; PrimeIcons `pi-bullseye`, the closest glyph to the Live Photo rings) and moved `useLivePhotoToggle` to `resources/js/composables/photo/`, as v7 never imports from `@/v8`.
- 2026-10-31: owner asked to move the lightbox "LIVE" button into the dock next to highlight/move/delete, styled like the other dock buttons, click to play and click again to stop and show the still. Agent read `PhotoBox`, `Dock`, `DockButton`, `PhotoPanel`, the LivePhotosKit typings (`showsNativeControls`, `play`/`stop`, `ended` event); chose to keep playing (restart on `ended`) until the second click so the button state always matches what is shown, to show the dock to viewers without edit rights for live photos, and to keep the native controls in Flow/Moderation where there is no dock (FR-100-06).
- 2026-10-17: owner asked for a plan for Google motion photos (#753, #1283) with grid hover or play button. Agent surveyed the live photo pipeline (Explore agent + direct reads of `GoogleMotionPictureHandler`, `ExtractGoogleMotionPictures`, `Extractor`, php-exif 1.4.0 mapper via `gh api`), parsed the existing sample's XMP with a Python one-off (legacy format confirmed), drafted spec + Q-100-01 … 06. Owner chose Option A for all six. While planning FR-100-03 the agent found v3 tiles lack `live_photo_url` until tier-3 details load → Q-100-07; owner chose Option A (details on hover) → FR-100-05, T-100-17. Owner supplied a Pixel 11 Pro motion photo; agent stripped GPS, C2PA and the HDR+ maker note with exiftool (owner asked for GPS removal so they cannot be identified; C2PA/maker note removed as device fingerprints), verified trailer bytes identical and inspected the still and video frames for identifying content (none), placed it as FX-100-03 at full resolution; findings (Primary item without `Item:Length`, HEVC video) folded into the spec Appendix. Grep of v8 thumbnail hosts: `PhotoThumbPanel`, `PhotoThumbPanelVirtual`, `TimelineDatesV3`, `ResultPanel`, views `Timeline`, `Search`, `Flow`; Ken Burns hover is CSS-only, no conflict.

## Review Follow-up (PR #4900, 2026-10-18)
- CodeRabbit: the feature number collided with upstream's Feature 098 (Tag Albums Category, merged meanwhile; 099 is PDF Photo View). Renumbered to the next free ID, 100: folder `features/100-google-motion-photos/`, `ADR-100-01`, every `*-098-*` identifier, and the code and test doc comments. Tag Albums' own 098 references (roadmap row, knowledge map line) left untouched.
- CodeRabbit: repeated hover sent duplicate details requests while the first was in flight. `useMotionHover` now keeps the pending request per photo id until it settles (FR-100-05); Vitest cases added for reuse and for retry after a failure (20/20).
- CodeRabbit (second review, on 2cdecef): (a) the fallback scan started at SOS, so compressed image data looking like an MP4 header could be taken for the video; it now starts after the primary image's EOI (`JpegImageEnd`, SVC-100-04), with tests for a look-alike header inside the image data, an EOI across a chunk boundary and a missing EOI (locator 21/21); (b) the plan and tasks still prescribed `vp check --fix`, now `npx vp check`; (c) T-100-17 and I8 still listed `Flow.vue` as a loader provider, removed.
- Maintainer ildyria (comment 6251741379): asked why the support was not added to php-exif. Owner chose to move it (Q-100-08, ADR-100-01). T-100-18 done on `mlapaglia/php-exif` `feat/google-motion-photos`; T-100-19 (php-exif PR, release, Lychee bump) pending.
- 2026-10-19: php-exif #83 merged and released as 1.5.0; upstream `master` merged (CONTRIBUTING.md only); php-exif bumped to `^1.5.0` with owner approval. Verified: php-exif alone reports 10705 / 10705 / 3725212 / 4495341 bytes for the v1, Ultra HDR, Pixel and legacy samples with the Native and Imagick readers, `false` for a plain JPEG; upload suites, `ExtractMotionPhotosTest`, `PhotoAddTest`, `DownloadTest`, `Detect360Test` and `make phpstan` green. Lychee's own reader was then removed with owner approval of the exact paths (T-100-19): `app/Metadata/MotionPhoto/` and `tests/Unit/Metadata/MotionPhoto/` deleted, `Extractor` reads php-exif only, the command calls `PHPExif\MotionPhoto\MotionPhotoLocator`; the four upload suites, `ExtractMotionPhotosTest`, `PhotoAddTest`, `DownloadTest`, `Detect360Test`, the other `Extractor` users and `make phpstan` green.
- Maintainer ildyria (comment 6264700405) asked whether to transcode HEVC clips. Owner chose Q-100-09 Option A (2026-10-19): `GoogleMotionPictureHandler::saveVideoStream()` copies H.264 and transcodes other codecs with `H264MOVFormat` (libx264 CRF 23 `veryfast`, `yuv420p`, `+faststart`, rotation applied); `lychee:extract_motion_photos --reencode` re-extracts stored Google clips from their originals (Apple pairs excluded). Measured on the Pixel sample: HEVC 3.69 MB → H.264 1.15 MB, 0.17 s on an M5 Pro. Tests: Pixel upload stores upright H.264 1080×1440, v1 stays copied H.264 (`ProbesStoredVideo` trait), three `--reencode` command tests; the four upload suites, `ExtractMotionPhotosTest` (11), `PhotoAddTest`, `DownloadTest` and `make phpstan` green.
- CodeRabbit (discussion r4313351119): a full batch with a failed photo printed a cursor that skips it. The command now prints each failure (`Failed: <id> (<title>): <reason>`) and, after the cursor, asks to run the same batch again first; `testCursorAfterAFailureAsksToRetryTheBatch` added (12/12).
- CodeRabbit (discussion r4314927585): `--reencode` wrote the new clip straight over the stored one, so an interrupted write could leave a truncated clip in use. The command now writes `<target>.part` and moves it over the target (a rename on local disks); `testReencodeKeepsTheStoredClipWhenTheWriteFails` blocks the staging path and checks the stored clip is unchanged (13/13). On the orphan-file thread (r4313351124) ildyria rejected CodeRabbit's transaction-wrapping patch and CodeRabbit withdrew it; no change.
- Maintainer ildyria (review on `docs/specs/6-decisions/README.md`): the superseded in-house reader ADR should not stay. Removed it and renumbered the php-exif ADR from ADR-100-02 to ADR-100-01 (`ADR-100-01-motion-photo-reader-in-php-exif.md`), now self-contained: its context and alternatives carry what the removed ADR recorded (exiftool flattening, Native/Imagick gaps, `ftyp`-only scan). References updated in the ADR index, spec, plan, tasks, open questions, roadmap, knowledge map.
- Maintainer ildyria (discussion r4317257862, "is this comment necessary?"): the doc comment on `Extractor::$micro_video_offset` was left over from the in-house locator call that T-100-19 removed. Dropped it, so `Extractor.php` matches upstream again (DO-100-01 updated). On the same review ildyria checked the hover wiring for `Flow.vue` (it does not use `PhotoThumb`, no change) and accepted the `MOTION_URL_LOADER` provide (Q-100-07), no change.
- CodeRabbit docstring-coverage warning (31.75 % of touched functions): not a repository rule; the new PHP classes and public methods carry doc comments, the uncovered ones are tests and Vue setup functions. No change.

## Drift Gate Report

Run 2026-10-17 by the agent after T-100-01 … T-100-17 (owner acknowledgement pending).

**Preconditions:** 17/17 tasks `[x]`. Quality gate green on the final tree: `vendor/bin/php-cs-fixer fix --dry-run` (0 of 2994 files), `make phpstan` (no errors, 3156 files), `npx vp check` (0 errors; 18 pre-existing warnings, none in feature files), `npx vp run typecheck`, `npx vp test` (31/31), `php scripts/check_translations.php` (gallery.php complete).

**Traceability**

| Requirement | Code | Tests (latest run, all green) |
|-------------|------|-------------------------------|
| FR-100-01 | `app/Metadata/MotionPhoto/{JpegXmpReader,JpegHeader,MotionPhotoXmp,MotionPhotoXmpHandler,FtypBox,MotionPhotoLocator}.php`, `Extractor::createFromFile()` | `JpegXmpReaderTest` 13, `MotionPhotoXmpTest` 18, `MotionPhotoLocatorTest` 18 (incl. Pixel, v1, Ultra HDR, legacy samples, 20 MB memory check) |
| FR-100-02 | unchanged pipes; `PlaceGoogleMotionVideo` uses `GoogleMotionPictureHandler::videoPathNextTo()` | `BaseImageHandler::testGoogleMotionPhoto{V1,UltraHdr,Pixel,WithoutFFMpeg}Upload`, `testLegacyGoogleMotionPhotoWithoutExifToolUpload`, existing GMP tests, in GD/Imagick/RawUploadImagick/Watermarker (34/40/37/39); `PhotoAddTest` 10, `DownloadTest` 15 |
| FR-100-03 | `ThumbBadge.vue` (`label`), `PhotoThumb.vue`, `PhotoThumbVirtual.vue`, `useMotionHover.ts`, 23 × `lang/*/gallery.php` | `useMotionHover.test.ts` 18; `LangTest` 2; browser check below |
| FR-100-04 | `app/Console/Commands/ImageProcessing/ExtractMotionPhotos.php` | `ExtractMotionPhotosTest` 8 |
| FR-100-05 | `motionUrlLoader.ts`; `provide` in `AlbumPanel.vue`, `Timeline.vue`, `Search.vue` | `useMotionHover.test.ts` (loader cases); browser check below |
| NFR-100-01 | `<video>` rendered only after intent | browser: 0 `.mov` requests on load and after a 120 ms sweep |
| NFR-100-02 | `JpegXmpReader` stops at SOS, `FtypBox::scan` 64 KiB chunks | `MotionPhotoLocatorTest::testScanUsesConstantMemory` |
| NFR-100-03 | no `composer.json`/`package.json` change | `git diff` |
| NFR-100-04 | `canPlayMotionOnHover()`, muted video, badge `aria-label` | Vitest gate cases; browser reduced-motion context |
| NFR-100-05 | small pure helpers | PHPStan, review |

**Browser check (scratch instance, 2026-10-17):** separate SQLite file and `public/uploads-motion/` via env overrides, `NUXT_UI_ENABLED` + `STRUCT_OF_ARRAY_ENABLED`, samples imported with `lychee:sync` (all four Google files and the Apple pair became live photos). Google Chrome via Playwright `channel: "chrome"`: 5 badges, none on the plain photo; first hover on the Pixel tile sent exactly one `GET /api/v3/Albums/{id}/Photos/details?photo_ids[]=…` and one `.mov` request, the HEVC clip played muted (`readyState` 4, time advancing, opacity 1); leave removed the element; second hover sent no details request; reduced motion showed badges only; a `.mov` routed to non-video bytes was dropped with the still visible, no toast, no console error; the lightbox shows LivePhotosKit's LIVE control for the Pixel photo. Not done: Firefox, Safari, a touch device (owner).

**Divergences:** all low impact, fixed in the spec: `JpegXmpReader` returns `JpegHeader` (SVC-100-01); FR-100-04 output strings, dry run without FFmpeg, missing original counted as failed, remote originals downloaded (branch not testable with `Storage::fake`, `@codeCoverageIgnore` like `SizeVariantDefaultFactory`); FR-100-05 providers are Album/Timeline/Search (Flow renders no `PhotoThumb`; tag/favourites carry the URL); badge sits after the 360° badge; badge label via new `ThumbBadge` `label` prop; fade-in uses `isPlaying` from the composable. Over-delivery: legacy motion photos now work without exiftool (S-100-09), covered by tests. No high/medium divergence; no new open question.

**Lessons**
- `npx vp check --fix` fixes the whole tree: it rewrote five unrelated files and its "useless escape" fix broke both `EmbedCodeDialog.vue` (`<\/script>` inside a template literal of a `.vue` `<script>` block must stay escaped). Reverted; use `npx vp check` (no `--fix`) or review `git status` after a fix run. Worth an Oxlint rule override or an AGENTS.md note (owner decision).
- The Imagick PDF tests need Ghostscript locally (`FailedToExecuteCommand 'gs'`).
- Playwright's bundled Chromium lacks proprietary codecs (and the cached build lagged the installed Playwright); `channel: "chrome"` gives real H.264/HEVC playback.
