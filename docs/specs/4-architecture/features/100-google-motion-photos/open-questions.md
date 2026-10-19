# Open Questions – Feature 100

Open questions for [Feature 100](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-100-01~~ | 100 – Google Motion Photos | High | How does the album grid let the viewer play the motion? (A hover-to-play + badge, B badge as play button, C hover only) | Resolved (Option A — hover-to-play on hover-capable pointers + motion badge everywhere; owner, 2026-10-17; spec FR-100-03, NFR-100-01, NFR-100-04, UI-100-01/02) | 2026-10-17 | 2026-10-17 |
| ~~Q-100-02~~ | 100 – Google Motion Photos | High | How is the embedded video located in Motion Photo 1.0 files? (A pure-PHP XMP `Container:Directory` read + `ftyp` scan when the item is absent, B extend php-exif to map the tags, C `ftyp` scan only) | Resolved (Option A — pure-PHP `MotionPhotoLocator` called from `Extractor`; owner, 2026-10-17; spec FR-100-01, NFR-100-02, NFR-100-03; ADR-100-01) | 2026-10-17 | 2026-10-17 |
| ~~Q-100-03~~ | 100 – Google Motion Photos | Medium | Does grid playback apply to Apple Live Photos too? (A all live photos, B Google only) | Resolved (Option A — every live photo; owner, 2026-10-17; spec FR-100-03) | 2026-10-17 | 2026-10-17 |
| ~~Q-100-04~~ | 100 – Google Motion Photos | Medium | How are already-imported new-format motion photos backfilled? (A artisan command, B re-upload only) | Resolved (Option A — `lychee:extract_motion_photos`; owner, 2026-10-17; spec FR-100-04, CLI-100-01) | 2026-10-17 | 2026-10-17 |
| ~~Q-100-05~~ | 100 – Google Motion Photos | Medium | Which frontends get grid playback? (A v8 only, B v7 and v8) | Resolved (Option A — v8 only; owner, 2026-10-17; spec FR-100-03, Non-Goals) | 2026-10-17 | 2026-10-17 |
| ~~Q-100-06~~ | 100 – Google Motion Photos | Medium | Which test fixture covers the new format? (A synthetic fixture + one real Pixel sample from the owner, B synthetic only, C issue #1283 images) | Resolved (Option A — synthetic fixtures + one owner-supplied Pixel `.MP.jpg`; owner, 2026-10-17; spec FX-100-01 … 03) | 2026-10-17 | 2026-10-17 |
| ~~Q-100-07~~ | 100 – Google Motion Photos | Medium | Where does a v3 grid tile get its video URL for hover playback? (A fetch tier-3 details on hover intent, B add `live_photo_urls[]` to the tier-2 resources, C hover playback only where the URL is already loaded) | Resolved (Option A — the owning view provides its details loader, called on `mouseenter`; owner, 2026-10-17; spec FR-100-03, FR-100-05, S-100-19, S-100-20) | 2026-10-17 | 2026-10-17 |
| ~~Q-100-08~~ | 100 – Google Motion Photos | High | Where does motion photo detection live: Lychee or `lychee-org/php-exif`? (A php-exif, B keep it in Lychee) | Resolved (Option A — move it to php-exif, as maintainer ildyria asked on PR #4900; owner, 2026-10-18; ADR-100-02, spec FR-100-01) | 2026-10-18 | 2026-10-18 |
| ~~Q-100-09~~ | 100 – Google Motion Photos | Medium | Transcode HEVC motion clips to H.264 so every browser plays them? (A Google clips at extraction, B every live photo video incl. Apple, C keep HEVC + add an H.264 fallback file, D no transcode) | Resolved (Option A — transcode Google clips at extraction, `--reencode` for stored ones; owner, 2026-10-19; spec FR-100-02, FR-100-04, S-100-21 … 23, Non-Goals) | 2026-10-19 | 2026-10-19 |

## Question Details

### ~~Q-100-01~~ – Grid interaction for motion photos

**Context:** Today the lightbox already plays live photos through LivePhotosKit (`v8/.../PhotoBox.vue:109-133`), with its own LIVE badge. Grid thumbnails (`PhotoThumb.vue`, `PhotoThumbVirtual.vue`) carry only an unstyled `livephoto` class: no badge, no hover playback. The owner asked for "show the video when hovered over, or a play button". Touch devices have no hover, and a tap on a thumbnail opens the lightbox.

- **Option A (chosen):** hover-to-play on pointer devices plus an always-visible motion badge.
  - Pointer with hover (`matchMedia('(hover: hover)')`): after ~250 ms of hover a muted, looping `<video preload="none">` fades in over the thumbnail; it stops and unloads on leave.
  - Every device: a small motion badge in the thumbnail badge row tells the viewer the photo moves; on touch the motion plays in the lightbox.
  - ✅ Matches Google Photos' own behaviour; zero clicks on desktop.
  - ✅ Badge makes motion discoverable on touch without hijacking the tap.
  - ❌ Hover playback fetches the video on demand (1–4 MB per clip).
- **Option B:** the badge is a play button; clicking it plays inline without opening the lightbox.
  - ✅ Works the same on touch and pointer; no accidental downloads.
  - ❌ Small tap target competing with selection and lightbox open.
  - ❌ One extra click per photo on desktop.
- **Option C:** hover-to-play only, no badge.
  - ✅ Smallest UI change.
  - ❌ Invisible on touch devices; nothing tells the viewer which photos move.

### ~~Q-100-02~~ – Locating the embedded video

**Context:** Android 11+ writes the Motion Photo 1.0 format: XMP `GCamera:MotionPhoto=1` plus a `Container:Directory` sequence of `Container:Item`s (`Item:Mime`, `Item:Semantic`, `Item:Length`, `Item:Padding`). The video is the item with `Item:Semantic="MotionPhoto"` and sits at the end of the file, so its start is `filesize − Item:Length`. Ultra HDR photos add a `GainMap` item between the primary JPEG and the video. Lychee today reads only `XMP-GCamera:MicroVideoOffset` through php-exif 1.4.0 (`Mapper/Exiftool.php:72`), which the Imagick and Native readers do not provide. Issue #1283's exiftool dump lists only the `Primary` directory item, so some files may lack the video's directory entry.

- **Option A (chosen):** pure-PHP reader `App\Metadata\MotionPhotoLocator`.
  - Reads the XMP packet from the JPEG's APP1 segments (no external tool), returns the video's byte length from the `MotionPhoto` item, or from `MicroVideoOffset` for the legacy format.
  - When `GCamera:MotionPhoto=1` but no `MotionPhoto` item exists, scans for the MP4 `ftyp` box after the primary image's EOI marker.
  - ✅ Works on every install (exiftool, Imagick or Native reader); no dependency change.
  - ✅ One place handles legacy, current and Ultra HDR layouts.
  - ❌ We maintain a small XMP/JPEG segment parser.
- **Option B:** extend `lychee-org/php-exif` to map `XMP-GCamera:MotionPhoto` and `XMP-GContainer:DirectoryItem*`, then bump the dependency.
  - ✅ Keeps all metadata reading in the library.
  - ❌ Requires exiftool as the reader; Imagick/Native installs stay unsupported.
  - ❌ Two-repository release and a dependency bump that needs owner approval.
- **Option C:** scan for the `ftyp` box after the JPEG EOI only (the approach of ViRb3/google-camera-motion-photo-splitter).
  - ✅ Simplest; ignores metadata entirely.
  - ❌ Heuristic; false positives possible on any JPEG with trailing data.
  - ❌ Does not use the declared length, so padding/gain-map layouts rely on luck.

### ~~Q-100-03~~ – Apple Live Photos in the grid

**Context:** Google motion videos are stored exactly like Apple Live Photo videos (`photos.live_photo_short_path`), and the frontend only knows `precomputed.is_livephoto` / `is_live_photos[]`. Apple `.mov` files are often HEVC, which Firefox and some Chromium builds cannot decode.

- **Option A (chosen):** grid playback applies to every live photo (Apple and Google).
  - ✅ No new API field or column; consistent UX.
  - ❌ HEVC clips fail silently on browsers without HEVC; the still stays visible.
- **Option B:** Google motion photos only.
  - ✅ Only H.264 clips, so playback works everywhere.
  - ❌ Needs a new `live_photo_kind` (or similar) column, migration and API field.

### ~~Q-100-04~~ – Backfilling existing photos

**Context:** New-format motion photos uploaded before this feature are stored as plain photos, but the original size variant keeps the full file including the video trailer, so the video can still be extracted. Feature 082 used `lychee:detect_360` for the same situation.

- **Option A (chosen):** `php artisan lychee:extract_motion_photos` over JPEG photos with `live_photo_short_path IS NULL`, with `--dry-run` and an album filter; reads originals from local or S3 storage.
  - ✅ Existing libraries gain motion without re-upload.
  - ❌ Must read every candidate original once (I/O on large libraries).
- **Option B:** no backfill; owners re-upload.
  - ✅ No new command.
  - ❌ Re-upload loses ratings, titles, album placement and tags.

### ~~Q-100-05~~ – Frontend scope

**Context:** Both `resources/js/v7` and `resources/js/v8` render thumbnails; Feature 077 shipped v8 only, Feature 097 shipped both.

- **Option A (chosen):** v8 only (`PhotoThumb.vue`, `PhotoThumbVirtual.vue`).
  - ✅ Half the UI work and test surface; v8 is where new work lands.
  - ❌ v7 users get extraction (lightbox playback) but no grid playback.
- **Option B:** v7 and v8.
  - ✅ Parity across trees.
  - ❌ Duplicated component and CSS work.

### ~~Q-100-06~~ – Test fixture

**Context:** `tests/Samples/google_motion_photo.jpg` is the legacy `MicroVideoOffset` format (checked 2026-10-17). No Motion Photo 1.0 sample exists in the repository, and fixtures must be redistributable.

- **Option A (chosen):** a small synthetic fixture (existing sample JPEG + a short H.264 MP4 + a hand-written `Container:Directory` XMP) for unit and upload tests, plus one real Pixel `.MP.jpg` from the owner, downscaled, for an end-to-end test.
  - ✅ Deterministic layout tests (including a `GainMap` item) and proof against a real camera file.
  - ❌ Owner must supply a photo they are happy to publish.
- **Option B:** synthetic fixture only.
  - ✅ No owner action needed.
  - ❌ Nothing proves the parser against real camera output.
- **Option C:** the sample images attached to issue #1283.
  - ✅ Real Pixel files.
  - ❌ Licence unclear; GitHub may have stripped the video trailer.

### ~~Q-100-07~~ – Video URL for grid tiles

**Context:** Found while planning FR-100-03 (2026-10-17). v8 grids built from the v3 struct-of-arrays endpoints (Feature 064/065) only know `is_live_photos[i]` at tile time; `live_photo_url` arrives with the tier-3 `details` call (`adaptPhotoTile.ts` `mergePhotoDetail()`), which today runs only when a photo is selected or opened. So on hover the tile has no URL to play. Every grid store already has a deduped details loader: `AlbumState.loadPhotoDetails`, `TimelineState.loadPhotoDetailsV3`, `SearchState.loadPhotoDetailsV3`, `FlowState.loadPhotoDetailsV3`. The URL's storage disk comes from the original size variant, which the ratios query does not join (NFR-064-03 caps it at 3 fixed `size_variants` joins).

- **Option A (chosen):** on `mouseenter` of a live photo tile, ask the owning store's details loader for that id (injected into `PhotoThumb` / `PhotoThumbVirtual` by the panel); play once `live_photo_url` is set and the 250 ms hover delay has passed.
  - ✅ No API or query change; respects the Feature 064 tier split and NFR-064-03.
  - ✅ The details fetch is deduped per id and also fills the other tier-3 fields the lightbox needs anyway.
  - ❌ One small request per newly hovered live photo; a slow network delays the first play past 250 ms.
- **Option B:** add a nullable `live_photo_urls[]` to `PhotoRatioResource` and `SearchPhotoResource`.
  - ✅ URL known at render time; no extra request.
  - ❌ API contract change (`lychee.d.ts`), and a 4th `size_variants` join for the disk, amending NFR-064-03.
- **Option C:** hover playback only on tiles whose URL is already loaded (v2 payloads, or after the photo was selected/opened).
  - ✅ No change beyond the thumbnail.
  - ❌ On v3 grids hover does nothing on first visit, which looks broken.

### ~~Q-100-08~~ – Library or application

**Context:** On PR #4900 maintainer ildyria asked why the support was not added to `lychee-org/php-exif` instead (comment 6251741379). ADR-100-01 had kept the XMP reader in Lychee because php-exif's exiftool mapping flattens `Container:Directory`. That argument is about the mapping, not about where the code lives: the same file-level reader can sit in php-exif.

- **Option A (chosen):** move the reader into php-exif (`PHPExif\MotionPhoto\*`), run it from `Reader::read()` for every adapter, and report the result as the existing `MicroVideoOffset`.
  - ✅ What the maintainers asked for; every php-exif user gets motion photos; Lychee's `Extractor` needs no special case.
  - ❌ Two-repository change: php-exif release, then a dependency bump in Lychee.
- **Option B:** keep ADR-100-01.
  - ✅ No cross-repository coordination.
  - ❌ Against the maintainers' request; metadata reading split between the library and the app.

### ~~Q-100-09~~ – Transcode HEVC clips

**Context:** Maintainer ildyria asked on PR #4900 (comment 6264700405): "Should we transcode them with ffmpeg then?", about the Non-Goal "Transcoding HEVC clips for browsers that cannot decode them". `GoogleMotionPictureHandler` re-muxes with `MOVFormat` (codec `copy`), so Pixel clips are stored as HEVC and browsers without HEVC decoding (Firefox on many systems, Chromium without hardware support) keep the still, in the grid and in the LivePhotosKit lightbox. Modern iPhones also record Live Photo video in HEVC by default, so Apple pairs have the same gap today (the repository's `train.mov` is an older H.264 one). Measured on the Pixel sample (2.37 s, 1440×1080, HEVC 3.69 MB, rotation −90°): libx264 `veryfast` CRF 23 → 1.15 MB in 0.17 s on an Apple M5 Pro (CRF 28 → 0.42 MB); the rotation is applied to the frames. Both Docker images install Debian trixie's `ffmpeg`, which includes libx264.

- **Option A (chosen):** transcode Google motion clips to H.264 during extraction (upload and `lychee:extract_motion_photos`) when the stream is not already H.264: libx264, `yuv420p`, CRF 23, `+faststart`, still `.mov` for LivePhotosKit; add `--reencode` to the command for clips already stored as HEVC.
  - ✅ Plays in every browser, grid and lightbox; files about a third of the size; rotation baked in.
  - ✅ FFmpeg already runs at that step; no schema change.
  - ❌ Lossy second encode; a few hundred ms to ~2 s CPU per upload on small servers.
  - ❌ Apple HEVC Live Photos keep the gap (follow-up).
- **Option B:** same, for every live photo video, including Apple pairs (`VideoPartner` pipes) and a backfill for them.
  - ✅ Fixes the gap for Apple Live Photos too.
  - ❌ Touches the Apple pairing path and existing libraries; larger scope than this PR.
- **Option C:** keep the original HEVC and store an extra H.264 file used as a `<source>` fallback.
  - ✅ No quality loss where HEVC plays.
  - ❌ New column or size variant, migration, and double storage.
- **Option D:** no transcode; document the limitation.
  - ✅ No change.
  - ❌ Firefox users on systems without HEVC see no motion for Pixel (and recent iPhone) clips.
