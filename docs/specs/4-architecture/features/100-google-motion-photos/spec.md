# Feature 100 – Google Motion Photos

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-10-17 |
| Owners | mlapaglia |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #100 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Lychee extracts the video from Google Motion Photos only in the legacy format (Android ≤ 10, XMP `GCamera:MicroVideoOffset`), and only when exiftool is the metadata reader (php-exif maps the tag for exiftool alone). Android 11+ writes the Motion Photo 1.0 format (`GCamera:MotionPhoto` + `Container:Directory`), which Lychee imports as a plain still (LycheeOrg/Lychee#753, #1283). This feature reads both formats with an in-house XMP reader, stores the video through the existing live photo path (`photos.live_photo_short_path`; the lightbox already plays it through LivePhotosKit), backfills existing photos with an artisan command, and adds motion playback to v8 grid thumbnails for every live photo.

Affected layers: metadata extraction (`app/Metadata`), one artisan command, v8 grid thumbnails and the views that host them. No migration. No REST contract change: v3 grid tiles fetch their video URL through the existing tier-3 details endpoint on hover (Q-100-07).

## Goals
- Motion Photo 1.0 JPEGs (Pixel, Ultra HDR included) import as live photos, the video placed next to the original as `.mov`.
- Legacy `MicroVideoOffset` files keep working, now on every metadata reader (exiftool, Imagick, Native).
- On v8 grids, live photos (Apple and Google) carry a motion badge and play their motion on hover.
- Already-imported motion photos gain their video through `lychee:extract_motion_photos`.

## Non-Goals
- Samsung's legacy `MotionPhoto_Data` / SEF trailer format.
- HEIC/AVIF motion photos (primary image other than JPEG).
- Honouring `MotionPhotoPresentationTimestampUs`.
- Transcoding Apple Live Photo videos (separate `.mov` partners); only clips embedded in Google Motion Photos are transcoded (Q-100-09).
- Changing the lightbox player (LivePhotosKit stays).
- v7 grid playback (Q-100-05).
- Inline playback on touch devices (Q-100-01).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-100-01 | Locate the embedded video of a JPEG. | `Extractor::createFromFile` takes the php-exif (`^1.5.0`) `MicroVideoOffset`. php-exif's `Reader::read()` fills it for every reader when the adapter found none, by calling `PHPExif\MotionPhoto\MotionPhotoLocator::locate()` (ADR-100-02), which reads the XMP packet from the JPEG APP1 segments and returns the video length in bytes from the end of the file: (1) `Item:Length` of the `Container:Item` with `Item:Semantic="MotionPhoto"`; (2) else `GCamera:MicroVideoOffset`; (3) else, when `GCamera:MotionPhoto="1"`, the distance from the first plausible MP4 `ftyp` box after the primary image's end-of-image marker (`JpegImageEnd`, the first `FF D9` after SOS) to the end of the file; no end-of-image marker → 0. The result goes into `Extractor::$micro_video_offset`. | A declared length (1 or 2) is accepted only when `0 < length < filesize` and the bytes at `filesize − length + 4` read `ftyp`; otherwise step 3 applies when `MotionPhoto="1"`, else 0. A plausible `ftyp` box has a big-endian size in 8…256 and a major brand from the Appendix list. Non-JPEG, no XMP, malformed XMP → 0. | Any read/parse error → 0 and `Log::warning`; the photo imports as a still. Never throws out of `Extractor`. | Existing span `photo.extract_google_motion_pictures` unchanged. | #753, #1283, Q-100-02, ADR-100-01 |
| FR-100-02 | Extract and place the video at upload. | `ExtractGoogleMotionPictures` passes the length to `GoogleMotionPictureHandler::load`; `saveVideoStream()` copies an H.264 stream into `.mov` (`MOVFormat`, `-map 0:0`) and transcodes any other codec (HEVC from current Pixels) to H.264 (`H264MOVFormat`: libx264, CRF 23, `veryfast`, `yuv420p`, `+faststart`, `-map 0:0`, rotation applied to the frames), so every browser plays it (Q-100-09); `PlaceGoogleMotionVideo` writes it next to the original and sets `live_photo_short_path`. Ultra HDR files (`GainMap` item) extract the video, not the gain map. | Requires `has_ffmpeg` (unchanged). | FFmpeg disabled/missing or unreadable stream → still imported, no video, error reported via `Handler::reportSafely` (unchanged). | — | Existing pipeline |
| FR-100-03 | Grid motion playback (v8, every live photo). | `PhotoThumb.vue` and `PhotoThumbVirtual.vue`: a motion badge (`ThumbBadge` with `lucide:smartphone` on `bg-purple-700`, the icon and colour of the list-view "Live" label, FR-014-06; its new `label` prop sets `title` and `aria-label` to `gallery.motion_photo`) in the badge row whenever `precomputed.is_livephoto`. On devices matching `(hover: hover) and (pointer: fine)`, after 250 ms of continuous hover a muted, looping, `playsinline` `<video>` fades in over the thumbnail (`object-cover`, same box) and plays `live_photo_url`; on `mouseleave` it pauses, its `src` is removed and the still shows again. When `live_photo_url` is still `null` at `mouseenter`, the URL is fetched per FR-100-05; playback starts once both the 250 ms have passed and the URL is set, and never if the pointer left first. The overlay covers the still, so the Ken Burns hover zoom (when enabled) keeps running unseen underneath. | Only tiles with `is_livephoto === true`; nothing when `prefers-reduced-motion: reduce` (badge only); nothing on touch-only devices (badge only, tap opens the lightbox as today). | Video `error` event → overlay removed, still stays, no toast, no retry during that hover. | None (frontend only). | Owner request, Q-100-01, Q-100-03, Q-100-05 |
| FR-100-04 | Backfill already-imported photos. | `php artisan lychee:extract_motion_photos {limit=100} {tm=600} {--after=} {--album=} {--dry-run} {--reencode}` walks photos with `type = 'image/jpeg'` and `live_photo_short_path IS NULL`, ordered by id with an `--after` cursor (as `lychee:detect_360`). For each it uses the original in place when hosted locally, or a downloaded temporary copy otherwise (as `SizeVariantDefaultFactory`), runs `PHPExif\MotionPhoto\MotionPhotoLocator::locate()` on its path, and when the length > 0 extracts via `GoogleMotionPictureHandler` and writes `GoogleMotionPictureHandler::videoPathNextTo(<original path>)` on the original's disk (the helper the upload pipe `PlaceGoogleMotionVideo` now shares), then sets `live_photo_short_path` and dispatches `PhotoSaved` for the extracted ids. Output: `Motion photo: <id> (<title>)` per hit, then `Checked N photos: X motion photos extracted, Y failed.` (`found (dry run)` with `--dry-run`), and `More photos may remain: continue with --after=<last id>` when `N = limit`; nothing to scan → `No photos require motion photo extraction.` | `--reencode` switches the candidates to Google motion photos that already have a video (`live_photo_short_path` set, `live_photo_content_id` null) and re-extracts each clip from the original into its current path, transcoding as in FR-100-02; the summary says `re-extracted`. `--dry-run` writes nothing and does not need FFmpeg. `--album` restricts to that album's direct photos; unknown id → `Album <id> not found.`, exit 1. FFmpeg disabled without `--dry-run` → `FFmpeg is required to extract motion photo videos (or use --dry-run).`, exit 1, before the scan. | Missing original or any per-photo error → `Log::warning` and `Failed: <id> (<title>): <reason>` on the console, counted as failed, loop continues; exit 1 when any failed. When the batch is full and some photos failed, the cursor line is followed by `N photos failed in this batch and would be skipped by that cursor: run it again with --after=<same value> (or without --after) to retry them first.` (PR #4900 review). Plain JPEGs stay candidates, so a re-run checks them again (no "checked" marker; the cursor bounds each run). | Command output only. | Q-100-04 |
| FR-100-05 | Video URL for v3 grid tiles. | Each v8 view that renders photo thumbnails provides a `MotionUrlLoader` (`(photoIds: string[]) => Promise<void>`, Vue `provide`/`inject` key in `resources/js/v8/composables/photo/motionUrlLoader.ts`) wrapping its store's existing deduped details loader: `AlbumPanel.vue` → `AlbumState.loadPhotoDetails`, `Timeline.vue` → `TimelineState.loadPhotoDetailsV3`, `Search.vue` → `SearchState.loadPhotoDetailsV3`. `Flow.vue` renders its own cards, not these thumbnails, and the tag and favourites views use v2 `PhotoResource` payloads that already carry `live_photo_url`, so they provide none. The thumbnail calls it with its own id on `mouseenter` when `is_livephoto` and `live_photo_url === null`, on hover-capable devices only; the result lands in `live_photo_url` through `mergePhotoDetail()`. | No call when the URL is already set, the tile is not a live photo, or the device/motion gates of FR-100-03 fail. A view that provides no loader plays only tiles whose URL is already loaded. | Loader rejects or leaves the URL `null` → no playback for that hover, no toast; the next hover tries again (the store's dedupe makes a resolved id free). A request still in flight is reused when the pointer leaves and re-enters, because the stores mark an id resolved only once its response arrives (PR #4900 review). | None. | Q-100-07 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-100-01 | No video bytes are requested until hover intent (250 ms). | Grids show hundreds of tiles. | `<video>` is not in the DOM before intent; browser network panel shows no `.mov` on page load or quick mouse sweeps. | v8 thumbnails | Q-100-01 |
| NFR-100-02 | `MotionPhotoLocator` uses constant memory: header segments are read one by one (stop at SOS), the end-of-image search and the `ftyp` scan read 64 KiB chunks with an overlap, the file is never loaded whole. | 8–20 MB uploads under the PHP memory limit. | Unit test on a synthetic 20 MB file with `memory_get_peak_usage` delta < 2 MB. | — | Q-100-02 |
| NFR-100-03 | No new composer/npm dependency and no new PHP extension: XMP is parsed with `ext-xml` (`xml_parser_create_ns`), already required. The existing `lychee-org/php-exif` is bumped to `^1.5.0`, which carries the reader (ADR-100-02, owner approval 2026-10-19). | AGENTS.md dependency rule. | `composer.json` / `package.json` unchanged. | — | AGENTS.md, ADR-100-01 |
| NFR-100-04 | Hover playback is muted, has no controls, respects `prefers-reduced-motion`, and the badge has an accessible label (`gallery.motion_photo`). | Accessibility. | Manual check with reduced motion; `LangTest` for the new key. | lang files (23 locales) | WCAG 2.3.3 |
| NFR-100-05 | The locator is a set of small pure helpers (segment reader, XMP parser returning a record, `ftyp` matcher) composed in one straight-line method. | AGENTS.md straight-line increments. | Code review; PHPStan level of `phpstan.neon`. | — | AGENTS.md |

## UI / Interaction Mock-ups

```
 Idle (all devices)               Hover ≥250 ms (fine pointer, no reduced motion)
┌────────────────────────┐        ┌────────────────────────┐
│ (◎)                  ♡ │        │ (◎)                  ♡ │
│                        │        │  ▶ muted looping video │
│      still image       │        │    fades in over the   │
│                        │        │    still (object-cover)│
│ Title                  │        │ Title                  │
└────────────────────────┘        └────────────────────────┘
 (◎) = motion badge in the existing badge row, right after the 360° badge
       (purple background, tooltip and aria-label "Motion photo")
 Leave → video paused, src removed, still visible again.
 Touch / reduced motion → badge only; tap opens the lightbox (LivePhotosKit plays it).
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-100-01 | Upload Motion Photo 1.0 JPEG with a `MotionPhoto` directory item → live photo, `.mov` next to the original. |
| S-100-02 | Upload legacy `MicroVideoOffset` JPEG with exiftool → unchanged behaviour. |
| S-100-03 | Upload Ultra HDR motion photo (`Primary`, `GainMap`, `MotionPhoto` items) → video extracted, gain map ignored. |
| S-100-04 | `MotionPhoto="1"` but no `MotionPhoto` directory item → video found by the `ftyp` scan. |
| S-100-05 | Declared length points at non-`ftyp` bytes → `ftyp` scan when `MotionPhoto="1"`, else not a motion photo. |
| S-100-06 | Motion photo with a corrupt video trailer → still imported, no video, error logged. |
| S-100-07 | Motion photo with FFmpeg disabled → still imported, no video. |
| S-100-08 | Plain JPEG with unrelated trailing bytes and no motion XMP → not a motion photo (no scan). |
| S-100-09 | Legacy `MicroVideoOffset` JPEG with the Imagick reader → now extracted (locator reads the XMP). |
| S-100-10 | Malformed XMP / truncated JPEG → 0, warning logged, still imported. |
| S-100-11 | `lychee:extract_motion_photos` over a library with new-format photos → videos attached; second run reports 0 extracted. |
| S-100-12 | `--dry-run` → candidates listed, no file written, no row changed. |
| S-100-13 | `--album=<id>` → only that album's photos; unknown id → exit 1. |
| S-100-14 | Backfill with FFmpeg disabled → exit 1 before scanning. |
| S-100-21 | Upload a motion photo whose clip is HEVC → stored `.mov` is H.264, upright (rotation applied). |
| S-100-22 | Upload a motion photo whose clip is already H.264 → stream copied, not re-encoded. |
| S-100-23 | `lychee:extract_motion_photos --reencode` on a motion photo stored with an HEVC clip → clip re-extracted from the original and stored as H.264 at the same path; `--dry-run` lists it and changes nothing; Apple pairs (`live_photo_content_id` set) are never candidates. |
| S-100-15 | Grid, fine pointer: hover a live photo ≥250 ms → muted loop plays; leave → stops and unloads. |
| S-100-16 | Grid: hover < 250 ms → no video element, no request. |
| S-100-17 | Grid: touch device or `prefers-reduced-motion` → badge only. |
| S-100-18 | Grid: video fails to load (e.g. HEVC in Firefox) → still stays, no toast. |
| S-100-19 | Grid on a v3 payload, URL not loaded: `mouseenter` triggers one details request for that id; playback starts after 250 ms or when the URL arrives, whichever is later; a second hover sends no request, including one that starts before the first request has answered. |
| S-100-20 | Details request fails or the pointer leaves before the URL arrives → no playback, no toast, no stray `<video>`. |

## Test Strategy
- **Unit:** in php-exif since 1.5.0 (`tests/PHPExif/MotionPhoto/`: `JpegXmpReaderTest`, `MotionPhotoXmpTest`, `MotionPhotoLocatorTest`, `ReaderMotionPhotoTest`), over synthetic byte layouts and the v1/Ultra HDR samples (S-100-01, 03, 04, 05, 08, 09, 10; NFR-100-02). Lychee covers the integration through the upload and command tests below.
- **Image processing (`BaseImageHandler`):** `testGoogleMotionPhotoV1Upload`, `testGoogleMotionPhotoUltraHdrUpload`, `testGoogleMotionPhotoPixelUpload` (owner sample), with FFmpeg disabled variant (S-100-01, 03, 06, 07); existing `testGoogleMotionPhotoUpload` / `testBrokenGoogleMotionPhotoUpload` stay green (S-100-02, 06).
- **Artisan (`tests/ImageProcessing/Commands/ExtractMotionPhotosTest`):** S-100-11 … 14, plus missing original and cursor. The remote-original download branch is not covered: `Storage::fake('s3')` is a local adapter, so `isLocalFile()` is always true in tests (same gap as `SizeVariantDefaultFactory`; marked `@codeCoverageIgnore`).
- **Frontend (v8):** Vitest for the `useMotionHover` composable (timer, intent, reduced-motion and hover-capability gates, error reset, loader called once and only when the URL is missing, late URL, pointer leaving before the URL, loader rejection) (S-100-15 … 20); manual browser check on desktop Chrome, Firefox, Safari and a phone, in album, timeline, search and flow views.
- **Docs/Contracts:** `lychee.d.ts` unchanged.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-100-01 | `Extractor::$micro_video_offset` keeps its name and meaning, "video length in bytes from the end of the file", now filled by php-exif for both formats and every reader. Doc comment updated. | `app/Metadata/Extractor.php` |
| DO-100-02 | `MotionPhotoXmp` readonly record: `is_motion_photo: bool`, `item_length: int`, `legacy_offset: int`. | `app/Metadata/MotionPhoto/` |

### Services
Provided by `lychee-org/php-exif` 1.5.0 (LycheeOrg/php-exif#83, ADR-100-02); Lychee calls only SVC-100-03, from `lychee:extract_motion_photos`.

| ID | Class | Description |
|----|-------|-------------|
| SVC-100-01 | `PHPExif\MotionPhoto\JpegXmpReader::read(resource $stream, int $file_size): ?JpegHeader` | Walks JPEG markers from SOI to SOS (fill bytes allowed), returns `JpegHeader(?string $xmp, int $image_data_offset)`: the first standard XMP packet (`http://ns.adobe.com/xap/1.0/\0` APP1, namespace stripped) and the offset of the SOS marker, where the `ftyp` scan of FR-100-01 starts. Null when the stream is not a JPEG, a marker is truncated, or a segment length is < 2 or runs past `$file_size`. |
| SVC-100-02 | `PHPExif\MotionPhoto\MotionPhotoXmp::fromXml(string $xmp): self` | Namespace-aware parse with `ext-xml`; unknown or malformed → all-zero record. |
| SVC-100-03 | `PHPExif\MotionPhoto\MotionPhotoLocator::locate(string $path): int` | Composes SVC-100-01/02, the `ftyp` check and the scan (FR-100-01). |
| SVC-100-04 | `PHPExif\MotionPhoto\JpegImageEnd::find(resource $stream, int $from, int $file_size): ?int` | Offset just after the first `FF D9` at or after the SOS marker (byte stuffing keeps it unique inside entropy-coded data); the scan of FR-100-01 starts there, so image data cannot match. Null when the image never ends (PR #4900 review). |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-100-01 | `php artisan lychee:extract_motion_photos {limit=100} {tm=600} {--after=} {--album=} {--dry-run} {--reencode}` | FR-100-04. |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-100-01 | `tests/Samples/google_motion_photo_v1.jpg` | Synthetic Motion Photo 1.0: existing sample JPEG + short H.264 MP4 + `Container:Directory` XMP. |
| FX-100-02 | `tests/Samples/google_motion_photo_ultrahdr.jpg` | Synthetic: Primary + GainMap + MotionPhoto items. |
| FX-100-03 | `tests/Samples/google_motion_photo_pixel.jpg` | Real Pixel 11 Pro Ultra HDR motion photo supplied by the owner (2026-10-17), 4.85 MB, 3064×4080 still + 1753-byte gain map + 2.37 s HEVC 1440×1080 video. Kept at full resolution so the camera layout stays byte-faithful (smaller than the existing 8.2 MB legacy sample). Privacy-stripped with exiftool 13.55: `-GPS:all= -XMP:GPS*= -JUMBF:all= -XMP-GCamera:HDRPlusMakerNote=` (GPS, C2PA manifest, HDR+ debug maker note / extended XMP); gain map + video bytes verified identical to the original. Make, model and capture time remain. |
| FX-100-04 | `tests/Samples/build_motion_photo_fixtures.php` (or a documented one-off script in the plan) | Rebuilds FX-100-01/02 deterministically. |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-100-01 | Motion badge | `precomputed.is_livephoto` → badge in the thumbnail badge row. |
| UI-100-02 | Hover playback | FR-100-03 success path. |
| UI-100-03 | Playback failed | Video error → back to UI-100-01. |

## Telemetry & Observability
No new events. Extraction keeps the `photo.extract_google_motion_pictures` span. Locator failures log `Log::warning` with the file basename (no path); FFmpeg failures go through `Handler::reportSafely` as today.

## Documentation Deliverables
- Roadmap row 100.
- Knowledge map: php-exif `Reader::read()` → `Extractor` → standalone pipeline; the command.
- `docs/specs/1-concepts/photos.md` and `docs/specs/4-architecture/request-lifecycle-photo-upload.md`: Motion Photo 1.0 support and the backfill command.
- ADR-100-01 (in-house XMP reader for motion photos).

## Spec DSL

```
domain_objects:
  - id: DO-100-02
    name: MotionPhotoXmp
    fields:
      - { name: is_motion_photo, type: bool }
      - { name: item_length, type: int, constraints: ">= 0" }
      - { name: legacy_offset, type: int, constraints: ">= 0" }
cli_commands:
  - id: CLI-100-01
    command: php artisan lychee:extract_motion_photos
fixtures:
  - { id: FX-100-01, path: tests/Samples/google_motion_photo_v1.jpg }
  - { id: FX-100-02, path: tests/Samples/google_motion_photo_ultrahdr.jpg }
  - { id: FX-100-03, path: tests/Samples/google_motion_photo_pixel.jpg }
ui_states:
  - { id: UI-100-01, description: Motion badge on live photo thumbnails }
  - { id: UI-100-02, description: Muted looping hover playback }
  - { id: UI-100-03, description: Playback failure falls back to the still }
```

## Appendix – Motion Photo 1.0 layout

```
<JPEG primary: SOI … APP1 XMP … EOI> [Item:Padding] [GainMap JPEG] [MP4 video]
                                                                    ^ filesize − Item:Length(MotionPhoto)
XMP namespaces:
  GCamera   = http://ns.google.com/photos/1.0/camera/         MotionPhoto, MotionPhotoVersion, MotionPhotoPresentationTimestampUs,
                                                              MicroVideo, MicroVideoOffset (legacy)
  Container = http://ns.google.com/photos/1.0/container/      Directory → rdf:Seq of Container:Item
  Item      = http://ns.google.com/photos/1.0/container/item/ Mime, Semantic (Primary|GainMap|MotionPhoto), Length, Padding
MP4 `ftyp` box: [uint32 size][ 'ftyp' ][major brand ×4] …
Accepted major brands: isom, iso2, iso4, iso5, iso6, mp41, mp42, avc1, qt␣␣

Observed on a Pixel 11 Pro (FX-100-03), in the standard XMP packet (not the extended one):
  <Container:Item Item:Mime="image/jpeg" Item:Semantic="Primary"/>                    ← no Item:Length
  <Container:Item Item:Length="1753" Item:Mime="image/jpeg" Item:Semantic="GainMap"/>
  <Container:Item Item:Mime="video/mp4" Item:Semantic="MotionPhoto" Item:Length="3725212" Item:Padding="0"/>
Attributes appear in any order; an absent attribute reads as "not set". exiftool's flattened output
pads the Primary length as 0, one more reason not to rely on php-exif here (ADR-100-01).
The video is HEVC (`hvc1`, ftyp brand `isom`), so FR-100-03's playback-failure path applies to
Pixel clips on browsers without HEVC, not only to Apple clips.
```
