# Open Questions – Feature 081

Open questions for [Feature 081](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-081-01~~ | 081 – 360° photos | High | Which rendering engine draws the sphere in the lightbox? | Resolved (Option A — in-house TypeScript + WebGL2 renderer; owner, 2026-10-03; spec NFR-081-02, NFR-081-03; ADR-081-01) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-02~~ | 081 – 360° photos | High | How is a photo detected as 360° on upload? | Resolved (Option A — php-exif getters from the exiftool and Imagick readers; Native-only installs flag by hand; owner, 2026-10-03; spec FR-081-01, FR-081-02, FR-081-03) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-03~~ | 081 – 360° photos | Medium | Full spheres only, or also partial (GPano-cropped) panoramas? | Resolved (Option A — full and partial spheres, four crop columns; owner, 2026-10-03; spec FR-081-02, FR-081-12, DO-081-01) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-04~~ | 081 – 360° photos | Medium | Can the owner set or clear the 360° flag by hand? | Resolved (Option A — checkbox in the v8 photo edit dialog; owner, 2026-10-03; spec FR-081-04) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-05~~ | 081 – 360° photos | High | What does the lightbox show first for a 360° photo? | Resolved (Option A — sphere first, `v` / header button to flat; owner, 2026-10-03; spec FR-081-08, FR-081-09) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-06~~ | 081 – 360° photos | Medium | Which image feeds the sphere texture? | Resolved (Option A — `medium2x` first, original only when it adds detail; owner, 2026-10-03; spec FR-081-14) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-07~~ | 081 – 360° photos | Medium | Which viewer interactions ship in this feature? | Resolved (Option B — drag and field-of-view zoom only; no letter-key look-around because keyboard layouts differ (AZERTY, …), no gyroscope; owner, 2026-10-03; spec FR-081-10, FR-081-11) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-08~~ | 081 – 360° photos | Medium | How are existing photos flagged? | Resolved (Option A — `lychee:detect_360` on photos never checked (`is_360 IS NULL`); owner, 2026-10-03; spec FR-081-05) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-09~~ | 081 – 360° photos | Medium | Does 360° video belong to this feature? | Resolved (Option A — photos only; owner, 2026-10-03; spec Non-Goals) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-10~~ | 081 – 360° photos | Low | How are 360° photos marked in grids and lists? | Resolved (Option A — grid badge and list label; owner, 2026-10-03; spec FR-081-17) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-11~~ | 081 – 360° photos | Medium | Are face and NSFW boxes shown in sphere mode? | Resolved (Option A — boxes in flat view only; owner, 2026-10-03; spec FR-081-08) | 2026-10-03 | 2026-10-03 |
| ~~Q-081-12~~ | 081 – 360° photos | Medium | What happens to rotate on a 360° photo? | Resolved (Option A — rotate hidden, `Photo::rotate` → 422; owner, 2026-10-03; spec FR-081-07) | 2026-10-03 | 2026-10-03 |

## Question Details

### ~~Q-081-01~~ – Rendering engine

**Context:** No panorama library is installed. NFR-078-03 kept Feature 078 dependency-free, and Lychee must work offline (any library is bundled by Vite, so all options satisfy that). Another famous photo app uses `@photo-sphere-viewer/core` 5.14 with the markers, resolution and settings plugins on the web, loaded as a lazy chunk; its pending mobile implementation writes its own sphere renderer instead, because the available Flutter libraries had outdated dependencies. Photo Sphere Viewer reads GPano crop data from the image file's XMP, which Lychee's generated variants do not carry, so it would need `panoData` passed explicitly. An equirectangular sphere is a single full-screen quad whose fragment shader maps each screen ray to longitude/latitude and samples the texture.

Where the work happens: per pixel, in the fragment shader on the GPU (GLSL for WebGL, WGSL for WebGPU, whatever language drives it). Per frame, the CPU only builds a rotation from yaw/pitch and the field of view, a few dozen float operations, and sets two uniforms. Decoding and downscaling the source image is done by the browser (`createImageBitmap` with `resizeWidth`, off the main thread), faster than any WASM decoder. The remaining CPU code is event handling (Pointer Events, wheel, keyboard), which must be JavaScript.

- **Option A (chosen):** in-house TypeScript renderer on WebGL2 in `resources/js/v8/` (pure camera/projection math in `utils/`, a composable owning the canvas, one GLSL fragment shader).
  - ✅ No new dependency, about 400 lines we control; same stance as Feature 078.
  - ✅ Shares the gesture model of `usePanZoom` (Pointer Events, rAF batching).
  - ❌ We maintain the shader, context-loss handling and texture-size downscaling ourselves.
- **Option B:** `@photo-sphere-viewer/core` (MIT) + `three` peer dependency.
  - ✅ Mature: gyroscope, cubemaps, partial panoramas, markers plugins.
  - ❌ Two new dependencies, about 600 KB minified (`three` dominates), loaded as a lazy chunk.
  - ❌ Its own gesture and keyboard handling must be reconciled with the lightbox.
- **Option C:** `pannellum` (MIT, raw WebGL, about 50 KB gzipped).
  - ✅ Small, no `three`.
  - ❌ Global-script design, slow release cadence, no ESM typings; awkward in Vue/TypeScript.
- **Option D:** in-house renderer in Rust (`wgpu`, or raw WebGL bindings through `web-sys`) compiled to WASM, like `@lychee-org/layouts`.
  - ✅ Rust tooling and tests for the camera math.
  - ❌ No speed gain: the shader runs on the GPU in any case, and the per-frame CPU work is negligible.
  - ❌ `wgpu` adds about 1–2 MB of WASM; event handling and canvas setup still need JavaScript glue.
  - ❌ The WASM build and embedding pipeline (manual byte-inlining for the UMD bundle) for code that TypeScript covers in a few hundred lines.

**Resolution:** Option A, recorded in [spec.md](spec.md) NFR-081-02, NFR-081-03 and [ADR-081-01](../../../6-decisions/ADR-081-01-in-house-webgl2-sphere-renderer.md).

### ~~Q-081-02~~ – Detection on upload

**Context:** `lychee-org/php-exif` 1.4.0 exposes GPano through `Exif` getters filled by the exiftool and Imagick mappers (spec FR-081-01); both readers return identical normalised values, the Native reader none. Raw tags per reader, verified on a JPEG tagged with GPano (exiftool 13.25, ImageMagick 7.1.1-43):
- exiftool reader (`exiftool -n -j -a -G1`): keys `XMP-GPano:ProjectionType` (`"equirectangular"`), `XMP-GPano:UsePanoramaViewer` (`true`), `XMP-GPano:FullPanoWidthPixels` (`8000`, int), … typed values.
- Imagick reader (`getImageProperties("*")`): keys `GPano:ProjectionType`, `GPano:UsePanoramaViewer` (`"True"`), `GPano:FullPanoWidthPixels` (`"8000"`), … string values.
- Native reader (`exif_read_data`): no GPano. It is used only when neither exiftool nor Imagick is available, or as the fallback when the chosen reader throws.

The sidecar `.xmp` merge in `Extractor::createFromFile()` merges mapped data only, so GPano from a sidecar needs the sidecar reader's raw data too. Another famous photo app always has exiftool and stores `ProjectionType` uppercased in a generic projection-type column; it treats `EQUIRECTANGULAR`, or any file with the Insta360 `.insp` extension, as a panorama. It trusts the tag: a PanoramaStudio flat panorama tagged `equirectangular` opens as a sphere, which that project treats as a tagging problem.

- **Option A (chosen):** `Extractor` uses the php-exif GPano getters (FR-081-01) of the exiftool and Imagick readers, on the main file and the sidecar. `getProjectionType() === 'equirectangular'` → 360°, unless `getUsePanoramaViewer()` is `false`. Native-only installs get no detection; the owner flags by hand (Q-081-04).
  - ✅ One code path; covers every install with exiftool or Imagick.
  - ❌ Native-only installs never auto-detect.
- **Option B:** Option A, plus an XMP packet scan in the php-exif Native adapter so it fills the same getters.
  - ✅ Detection on every install.
  - ❌ An XMP parser in php-exif for a rare configuration.
- **Option C:** Option A, plus flag any image with an exact 2:1 aspect ratio and no GPano tags.
  - ✅ Catches cameras/editors that strip XMP.
  - ❌ False positives: 2:1 crops and stitched flat panoramas would open as spheres.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-01, FR-081-02, FR-081-03.

### ~~Q-081-03~~ – Partial panoramas

**Context:** Phone "photo spheres" are often partial: GPano `CroppedAreaImageWidthPixels/HeightPixels`, `CroppedAreaLeftPixels/TopPixels` and `FullPanoWidthPixels/HeightPixels` place the image inside a larger virtual sphere. Without them, a partial image stretched over a full sphere is distorted.

- **Option A (chosen):** support both; store the crop as nullable integer columns (`pano_full_width`, `pano_full_height`, `pano_crop_left`, `pano_crop_top`); `NULL` means full sphere. The renderer shows black outside the crop and limits the pitch/yaw to the covered area.
  - ✅ Phone spheres render undistorted.
  - ❌ Four more columns, more renderer math.
- **Option B:** full 360×180 spheres only; a single boolean column `is_360`; partial GPano images are not flagged.
  - ✅ Simplest data model.
  - ❌ Many phone spheres are excluded or distorted when flagged by hand.
- **Option C:** Option A plus cylindrical projection (`ProjectionType=cylindrical`).
  - ✅ Covers more stitching apps.
  - ❌ Second projection in the shader; rare in practice.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-02, FR-081-12, DO-081-01.

### ~~Q-081-04~~ – Manual override

**Context:** Detection misses images whose XMP was stripped, and installs with neither exiftool nor Imagick if Q-081-02 = A. `PhotoEdit.vue` already has a `UCheckbox` (taken-date block); the PATCH `/Photo` request is `EditPhotoRequest`.

- **Option A (chosen):** a "360° photo" checkbox in the v8 photo edit dialog (single photo), sent on the existing PATCH `/Photo`. The manual value wins over detection and survives re-detection (Q-081-08).
  - ✅ One field, existing endpoint.
  - ❌ Flagging many photos means one dialog each.
- **Option B:** Option A plus a bulk "Mark as 360° / Unmark" action in the selection context menu (new PATCH endpoint, like `Photo::license`).
  - ✅ Fast for large imports without XMP.
  - ❌ New endpoint, request, menu entries.
- **Option C:** no manual override; detection only.
  - ✅ No UI or API change.
  - ❌ No way to fix a missed or wrong detection.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-04.

### ~~Q-081-05~~ – Default lightbox presentation

**Context:** In a sphere, a drag rotates the view, which conflicts with swipe navigation (`useSwipe` in `PhotoBox.vue`), the window-level wheel navigation (`PhotoPanel.vue`) and Feature 078 pan/zoom. Arrow keys and the `NextPrevious` buttons still navigate either way. Another famous photo app opens panoramas directly in the sphere on the web, with no swipe navigation and no flat toggle; a user request for a flat toggle went unanswered. Its pending mobile implementation does the opposite: a flat photo with a 360° button.

- **Option A (chosen):** open 360° photos directly in the sphere; drag looks around, pinch/wheel change the field of view, swipe and wheel navigation are off; a header button and the `v` key switch to the flat image (with Feature 078 zoom) and back.
  - ✅ The photo is seen as intended, without an extra step.
  - ❌ On touch, the user must use the arrows to move to the next photo while in the sphere.
- **Option B:** open the flat image (current behaviour, swipe and zoom intact); a header button and the `v` key enter the sphere.
  - ✅ Navigation unchanged everywhere.
  - ❌ The 360° view is hidden behind a button; the flat equirectangular image looks distorted.
- **Option C:** admin config `photo_360_default_view` (`sphere|flat`, default `sphere`), with the toggle in both cases.
  - ✅ Admins choose.
  - ❌ One more setting and two code paths to test.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-08, FR-081-09.

### ~~Q-081-06~~ – Texture source

**Context:** For a 2:1 image, `medium` is 1920×960 and `medium2x` 3840×1920 (if enabled). A sphere spreads that width over 360°, so a sharp view needs a texture about `viewport width × devicePixelRatio × 360 / FOV` pixels wide: 7680 px for a 1920 px screen at 90°, so `medium2x` is already soft at the default field of view on most screens. GPU `MAX_TEXTURE_SIZE` is often 4096 on phones and 8192–16384 on desktops. Feature 078 exposes the original only when its URL is non-null and the photo is not RAW, and loads it only when the zoom needs more pixels (FR-078-12). Another famous photo app shows its preview (1440 px by default) first and swaps in the original once zoomed past 75 % of its zoom range, or immediately when the user preference "always load original" is on.

- **Option A (chosen):** show `medium2x` (else `medium`) immediately; when the needed width (formula above, capped at `MAX_TEXTURE_SIZE`) exceeds the current texture's width and the original is accessible and not RAW, load the original in the background and swap it in once decoded. Images wider than `MAX_TEXTURE_SIZE` are downscaled in a canvas before upload.
  - ✅ Same rule as FR-078-12: the original is fetched only when it adds detail, which in practice is on first view for most screens.
  - ❌ Large downloads on phones whenever the original is accessible.
- **Option B:** show `medium2x` first and always swap in the accessible original, whatever the field of view.
  - ✅ Simplest rule.
  - ❌ Downloads the original even on screens where it adds nothing.
- **Option C:** `medium2x` (else `medium`) only.
  - ✅ Predictable bandwidth.
  - ❌ Soft on every screen wider than about 1000 device pixels.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-14.

### ~~Q-081-07~~ – Viewer interactions

**Context:** Another famous photo app offers, on the web, drag, wheel and pinch zoom (field of view 15°–90°) and a `z` key that toggles between two zoom levels; no gyroscope, keyboard look or auto-rotate.

- **Option A:** drag to look around (with inertia), pinch/wheel/`+`/`-` to change the field of view (30°–100°), `Shift`+arrows or `w`/`a`/`s`/`d` to look around by keyboard (plain arrows keep navigating), and a gyroscope toggle in the header on touch devices (iOS permission requested on first use).
  - ✅ Full desktop and phone experience; arrows keep their meaning.
  - ❌ Gyroscope needs HTTPS and an iOS permission prompt.
- **Option B (chosen):** drag and field-of-view zoom only.
  - ✅ Smallest scope.
  - ❌ No keyboard look; no "move the phone to look around".
- **Option C:** Option A plus slow auto-rotation when idle, behind an admin config.
  - ✅ Showcase effect for slideshows and kiosks.
  - ❌ More settings; motion-sensitivity concerns (`prefers-reduced-motion`).

**Resolution:** Option B, recorded in [spec.md](spec.md) FR-081-10, FR-081-11. The owner rejected letter keys for looking around because they depend on the keyboard layout (AZERTY and others).

### ~~Q-081-08~~ – Flagging existing photos

**Context:** Detection runs on upload only. `lychee:exif_lens` re-reads EXIF but only for photos missing EXIF; size-variant and EXIF commands take `offset`/`limit`/`tm` arguments.

- **Option A (chosen):** new artisan command `lychee:detect_360 {offset} {limit} {tm}` that runs the Q-081-02 detection on originals of photos not yet flagged and never touches manually set flags.
  - ✅ Explicit, resumable, safe to rerun.
  - ❌ One more command; reading S3 originals means downloading them.
- **Option B:** extend `lychee:exif_lens` to also detect 360° photos.
  - ✅ No new command.
  - ❌ That command filters on missing EXIF, so it would skip most existing photos without a rework.
- **Option C:** none; existing photos are flagged by hand (Q-081-04).
  - ✅ No backend work.
  - ❌ Large existing libraries stay flat.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-05.

### ~~Q-081-09~~ – 360° video

**Context:** Spherical video carries Google Spatial Media metadata (`ffprobe` side data `spherical`). Rendering needs a `<video>` texture updated per frame, plus playback controls over the canvas. Another famous photo app plays 360° video in the sphere on the web through Photo Sphere Viewer's video adapter and video plugin (with its own navbar); its mobile app shows 360° video flat.

- **Option A (chosen):** photos only; 360° video is a follow-up feature.
  - ✅ Keeps this feature small; the renderer is built so a video texture can be added later.
- **Option B:** photos and videos.
  - ✅ Complete 360° story at once.
  - ❌ Larger scope: ffprobe mapping, video texture, controls over the canvas.

**Resolution:** Option A, recorded in [spec.md](spec.md) Non-Goals.

### ~~Q-081-10~~ – Marker in grids and lists

**Context:** Grid thumbs show a top-start `ThumbBadge` row (`PhotoThumb.vue` and `PhotoThumbVirtual.vue`); list rows show a purple "livephoto" label (`PhotoListItem.vue` and its virtual twin). Square thumbs of a 2:1 sphere are a centre crop, which looks like a normal photo. Another famous photo app has no 360° marker on web thumbnails; its pending mobile implementation adds a 360° badge.

- **Option A (chosen):** a `360°` `ThumbBadge` on grid thumbs and a `360°` label in list rows.
  - ✅ Visible before opening.
  - ❌ Four components to keep in sync.
- **Option B:** list label only (like live photos).
  - ✅ Minimal.
  - ❌ Invisible in the default grid layouts.
- **Option C:** no marker.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-17.

### ~~Q-081-11~~ – Face and NSFW boxes in sphere mode

**Context:** In the flat lightbox, `PhotoBox` draws face boxes (clickable, open the face assignment) and NSFW detection outlines (informational, no blur) over the image. Box coordinates are in image pixels. Another famous photo app projects face and OCR boxes onto the sphere as markers and pans to a face hovered in the details panel.

- **Option A (chosen):** boxes are drawn only in the flat view (Q-081-05 toggle); the sphere shows none.
  - ✅ No projection code; face assignment still reachable from the flat view and the details drawer.
  - ❌ Faces are not visible while looking around.
- **Option B:** project each box onto the sphere every frame (quadrilateral of the four projected corners in an SVG layer), clickable as in the flat view.
  - ✅ Same information in both views.
  - ❌ Projection per frame, boxes crossing the seam or behind the camera need clipping.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-08.

### ~~Q-081-12~~ – Rotation of 360° photos

**Context:** `POST /Photo::rotate` rotates the original by 90° steps. A rotated equirectangular image is no longer a valid sphere. Another famous photo app rejects edits of panoramas.

- **Option A (chosen):** the rotate actions are hidden for 360° photos in v8, and `Photo::rotate` rejects them with 422.
  - ✅ A 360° photo cannot be broken by accident.
  - ❌ A wrongly flagged photo must be unflagged first (Q-081-04) before rotating.
- **Option B:** rotation stays allowed and clears the 360° flag.
  - ✅ No blocked action.
  - ❌ Silent loss of the 360° view.
- **Option C:** unchanged; rotation allowed, flag kept.
  - ❌ The sphere shows a broken image.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-081-07.
