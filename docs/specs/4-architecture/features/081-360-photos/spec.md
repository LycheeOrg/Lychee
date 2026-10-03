# Feature 081 – 360° Photos

| Field | Value |
|-------|-------|
| Status | Implemented (manual check pending) |
| Last updated | 2026-10-03 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #081 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Equirectangular 360° photos (phone photo spheres, 360° cameras) are flagged on upload from their XMP-GPano metadata, stored on `photos` with their partial-panorama crop, exposed through the v2 `PhotoResource` and the v3 Struct-of-Arrays photo tiers, and displayed by the v8 lightbox as an interactive sphere drawn by an in-house TypeScript + WebGL2 renderer ([ADR-081-01](../../../6-decisions/ADR-081-01-in-house-webgl2-sphere-renderer.md)). Owners can set or clear the flag by hand, and an artisan command flags photos uploaded before this feature.

Layers: `lychee-org/php-exif` 1.4.0 (GPano getters on `Exif`), one `photos` migration, metadata extraction (`Extractor`, `HydrateMetadata`, a pure `PanoramaDetector`), `PATCH /Photo`, `POST /Photo::rotate`, one artisan command, resources (`PreComputedPhotoData`, `PhotoResource`, `PhotoRatioResource`, `PhotoDetailResource`, `SearchPhotoResource`), v8 frontend (`PhotoState`, `PhotoBox`, `PhotoHeader`, `PhotoEdit`, `Dock`, thumbs and list rows). The v7 frontend is unchanged; it ignores the new fields and keeps working against the shared v2 endpoints.

## Goals
- G1: 360° photos are recognised on upload without user action, including partial (cropped) photo spheres.
- G2: the v8 lightbox opens a 360° photo as a sphere the viewer looks around in by dragging and zooms by changing the field of view, with a switch to the flat image.
- G3: lightbox navigation keeps working on 360° photos (arrows and buttons), and no gesture both rotates the sphere and changes photo.
- G4: the sphere never loads an image the viewer may not access, and loads the original only when it adds detail.
- G5: owners can correct detection by hand, existing libraries can be scanned, and 360° photos cannot be broken by rotation.
- G6: 360° photos are recognisable in grids and lists.

## Non-Goals
- v7 frontend.
- 360° video (follow-up feature).
- Cubemap, cylindrical, stereo (top/bottom, side-by-side) and fisheye inputs.
- Gyroscope / device-orientation look-around, keyboard look-around, auto-rotation, inertia.
- Face and NSFW boxes on the sphere (they stay in the flat view).
- Detection without exiftool or Imagick (Native reader only): such installs rely on the manual flag.
- Aspect-ratio based detection.
- Hotspots, markers, virtual tours.
- Splitting textures wider than the GPU limit (they are downscaled).
- Changes to thumbnail and size-variant generation.
- Bulk flagging from a selection.
- New npm dependencies; Rust/WASM.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-081-01 | **php-exif contract.** `lychee-org/php-exif` 1.4.0 exposes GPano on `Exif`, filled by `Mapper\Exiftool` (`XMP-GPano:<Tag>`) and `Mapper\ImageMagick` (`GPano:<Tag>`): `getProjectionType(): string\|false` (lower-cased), `getUsePanoramaViewer(): ?bool`, and `int\|false` getters `getFullPanoWidthPixels`, `getFullPanoHeightPixels`, `getCroppedAreaLeftPixels`, `getCroppedAreaTopPixels`, `getCroppedAreaImageWidthPixels`, `getCroppedAreaImageHeightPixels`. The Native and FFprobe readers return none. Because the tags are mapped, the existing sidecar `.xmp` merge in `Extractor::createFromFile()` carries them as well. Lychee never reads GPano from `Exif::getRawData()`. | Exiftool and Imagick readers return identical values for the same file. | — | — | None. | Owner directive 2026-10-03. |
| FR-081-02 | **Detection rule.** A pure `App\Metadata\PanoramaDetector::detect(Exif $exif, int $width): PanoramaInfo` decides. `is_360` is true when `getProjectionType() === 'equirectangular'`, `getUsePanoramaViewer() !== false`, and the crop is absent or valid. **Crop absent:** none of the six crop getters returns a value → full sphere, all four crop fields `null`. **Crop valid:** all six present, widths and heights > 0, left and top ≥ 0, `left + cropWidth ≤ fullWidth`, `top + cropHeight ≤ fullHeight`. A valid crop covering the whole panorama (`left = top = 0`, crop size = full size) is a full sphere. Otherwise the crop is scaled to the file's pixel width (`factor = $width ÷ CroppedAreaImageWidthPixels`, values rounded) and returned as `full_width`, `full_height`, `crop_left`, `crop_top`; the covered size is the photo's own width and height. **Crop malformed** (some but not all six present, a constraint above fails, or the file width is unknown, `$width ≤ 0`): `is_360` false. | A phone photo sphere with a partial crop is flagged with its scaled crop. | `UsePanoramaViewer = false`, another projection, or a malformed crop → flat. | — | None. | Owner directive 2026-10-03. |
| FR-081-03 | **Storage on upload.** `Extractor::createFromFile()` runs `PanoramaDetector` with the `Exif` object it already holds and exposes `is_360` and the four crop values; `HydrateMetadata` copies them when `photos.is_360` is `null` (same rule as `img_direction`). Every upload therefore stores `is_360` as true or false (false for videos and for installs whose reader has no GPano). | Upload of FX-081-01 stores `is_360 = true`, crop `null`. | — | Metadata read failure: existing `Extractor` fallback to the Native reader; `is_360` false. | None. | Owner directive 2026-10-03. |
| FR-081-04 | **Manual flag.** The v8 photo edit dialog (`PhotoEdit.vue`) shows a "360° photo" checkbox for still photos (hidden for videos), initialised from `precomputed.is_360`. `PATCH /Photo` (`EditPhotoRequest`) accepts an optional `is_360` boolean (`sometimes`, so v7 requests without it leave the flag unchanged). When present it sets `photos.is_360`; the crop columns are untouched (a photo flagged by hand without crop data is a full sphere). A changed `is_360` dispatches `PhotoSaved` for the photo, so the cached album and Timeline listings are invalidated. | Owner ticks the box; the lightbox opens the photo as a sphere. | `is_360` not boolean → 422. `is_360 = true` on a video → 422. | — | None. | Owner directive 2026-10-03. |
| FR-081-05 | **Existing photos.** `php artisan lychee:detect_360 {offset=0} {limit=100} {tm=600}` processes photos with `is_360 IS NULL` that are not videos, ordered by `id`, `offset`/`limit` applied to that set. For each, it reads the original (`getFile()->toLocalFile()`, which downloads S3 originals), runs `Extractor::createFromFile()` and stores `is_360` and the crop. Photos set by hand or by upload are never `NULL`, so they are never touched. Photos found to be 360° dispatch one `PhotoSaved` per run. It prints one line per 360° photo found and a summary (`checked`, `found`, `failed`); with no candidate it prints "No photos require 360° detection.". | Rerunning until no candidate is left scans a whole library. | — | Unreadable original: logged, the photo stays `NULL`, counted as failed, the command continues; exit code 1 when any failed. | `Log::warning` per failure (photo id, exception message). | Owner directive 2026-10-03. |
| FR-081-06 | **API exposure.** v2: `PreComputedPhotoData::$is_360` (`photos.is_360 === true`); `PhotoResource::$panorama`: `?PanoramaResource` (`full_width`, `full_height`, `crop_left`, `crop_top`), `null` unless the photo is 360° with a crop. v3: `PhotoRatioResource::$is_360s` and `SearchPhotoResource::$is_360s` (`bool[]`, index-aligned), `PhotoDetailResource::$panoramas` (`(PanoramaResource\|null)[]`, index-aligned, same rule). `adaptPhotoTile.ts` maps `is_360s[i]` to `precomputed.is_360`; `mergePhotoDetail()` maps `panoramas[i]` to `photo.panorama`. | Grid, search and lightbox know the flag without extra requests; the crop arrives with the details tier. | — | — | None. | — |
| FR-081-07 | **Rotation.** In the v8 lightbox the rotate buttons of `Dock.vue` are hidden for a photo with `precomputed.is_360`. `POST /Photo::rotate` on a 360° photo returns 422 (v7 included). | 360° photos cannot be rotated by accident. | Rotate request on a 360° photo → 422, file untouched. | — | None. | Owner directive 2026-10-03. |
| FR-081-08 | **Sphere mode.** `PhotoState` gains `ImageViewMode.Sphere` and the getters `isSphereCapable` (360°, still, not RAW, not a live photo, WebGL2 usable, not failed) and `isSphereView` (capable and not switched to flat); its `imageViewMode` getter is unchanged because v7 shares it. The v8 `PhotoBox` uses `ImageViewMode.Sphere` when the photo has `precomputed.is_360`, is not a video, not RAW, not a live photo, the slideshow is inactive, the lightbox is the main one (`PhotoPanel`; the Flow and Moderation previews stay flat), WebGL2 is usable (FR-081-15), and the flat switch (FR-081-09) is off. The sphere canvas replaces the `<img>` inside `#imageview`; the EXIF overlay, header, `NextPrevious`, rating actions and details drawer stay. Face and NSFW boxes and the Feature 078 minimap are not rendered in sphere mode. During the slideshow 360° photos are shown flat. | A 360° photo opens as a sphere. | — | — | None. | Owner directive 2026-10-03. |
| FR-081-09 | **Flat switch.** While a 360° photo is open in the main lightbox and WebGL2 is usable, `PhotoHeader.vue` shows a toggle button (`lucide:globe` in flat view, label "Show as sphere"; `lucide:rectangle-horizontal` in sphere view, label "Show flat"), and the `v` key (declared in `definePanelShortcuts` next to the Feature 078 zoom keys, active whenever `isSphereCapable`) toggles the same state. Flat view is the regular `Medium`/`Original` mode with Feature 078 zoom, faces and NSFW boxes. The switch resets to sphere when the photo changes. The v8 keyboard help (`KeybindingsHelp.vue`, photo section) lists `v` (`dialogs.keybindings.toggle_sphere`, "Switch 360° photo between sphere and flat"). | One key or click between sphere and flat. | Key ignored per existing `definePanelShortcuts` rules (modal open, text input). | — | None. | Owner directive 2026-10-03. |
| FR-081-10 | **Look around.** In sphere mode a single-pointer drag (touch or mouse) rotates the view with the pointer: one CSS pixel is `fov ÷ viewport height` degrees of yaw (horizontal) or pitch (vertical). Pitch of the view centre is clamped to [−90°, 90°]. No inertia. A pointer-down/up that moved ≤ 5 px with a single pointer is a tap and rotates the EXIF overlay immediately; any other gesture is never a tap (Feature 078 classification). Swipe navigation, vertical swipe-back and the window-level wheel navigation of `PhotoPanel.vue` are inactive; plain arrow keys and the `NextPrevious` buttons navigate. There is no keyboard look-around. | Drag looks around; arrows change photo. | — | — | None. | Owner directive 2026-10-03. |
| FR-081-11 | **Field of view.** Vertical field of view, default 75°, clamped to [30°, 100°]. Zoom factor `s = tan(37.5°) ÷ tan(fov ÷ 2)`. Two-finger pinch, `ctrl`+wheel and plain wheel change it, keeping the direction under the gesture midpoint / cursor fixed. Keyboard through the Feature 078 `PhotoState.zoom_controls` registration: `z` toggles between default and `s = 2`, `+`/`=` multiply `s` by 1.5, `-` divides it by 1.5; while `s > 1` (published as `PhotoState.is_zoomed`) `escape` and `0` reset to default and the rating keys and rating actions are hidden (FR-078-11, FR-078-21 unchanged). | Same zoom keys and gestures as flat photos. | Clamped to [30°, 100°]. | — | None. | Owner directive 2026-10-03. |
| FR-081-12 | **Partial panoramas.** With a crop, the shader maps the image to longitudes `[crop_left, crop_left + width] ÷ full_width × 360°` and latitudes `[crop_top, crop_top + height] ÷ full_height × 180°` (width and height of the photo itself) and draws the lightbox background colour outside. The view centre is clamped so that, on each axis where the covered span is larger than the view, the view stays inside it; on an axis where it is smaller, the view is centred on it. A crop as wide as the full panorama wraps horizontally like a full sphere. | A partial sphere renders undistorted, without empty areas when the coverage allows. | — | — | None. | Owner directive 2026-10-03. |
| FR-081-13 | **Initial view.** On open: field of view 75°, yaw at the horizontal centre of the image (full sphere: the centre column; partial: the centre of the covered area), pitch at the vertical centre of the covered area (0° for a full sphere), then clamped per FR-081-12. | — | — | — | None. | — |
| FR-081-14 | **Texture source.** The sphere first shows `medium2x`, else `medium`; a photo too small to have them starts from the original (exposed and not RAW), else `small2x`, else `small`. A photo synthesised from a Struct-of-Arrays listing has no size variants and no crop until its details tier is merged: the sphere waits for them, and sets its initial view (FR-081-13) once the first texture is uploaded. The needed width is `min(MAX_TEXTURE_SIZE, container width × devicePixelRatio × 360° ÷ horizontal fov × coverage)`, where `coverage` = photo width ÷ `full_width` (1 for a full sphere). When it exceeds the current texture width and the original's URL is exposed and the photo is not RAW, the original is loaded in the background (`createImageBitmap`, resized to `MAX_TEXTURE_SIZE` when wider) and swapped in once decoded; the original is never requested otherwise. Any source wider than `MAX_TEXTURE_SIZE` is downscaled before upload. Mipmaps are generated; the shader samples with explicit gradients so no seam appears at the ±180° wrap. | Sharpest image allowed, fetched only when it adds detail. | — | Load or decode of the original fails: the current texture stays. | None. | Owner directive 2026-10-03. |
| FR-081-15 | **Unusable WebGL.** When WebGL2 context creation, shader compilation or texture upload fails, the photo is shown in flat view and the switch (FR-081-09) is hidden for that photo. On `webglcontextlost` the renderer waits for `webglcontextrestored` and rebuilds program and texture; while lost, nothing is drawn. | — | — | Flat view with Feature 078 zoom. | `console.warn` once with the failure reason. | — |
| FR-081-16 | **Lifecycle.** Navigating to another photo, switching to flat view, starting the slideshow or closing the lightbox disposes of the renderer: listeners removed, texture and program deleted, `WEBGL_lose_context.loseContext()` called. A container size change (window resize, details drawer, device rotation) resizes the canvas and keeps yaw, pitch and field of view. | No GPU memory growth while browsing; no view reset on resize. | — | — | None. | — |
| FR-081-17 | **Markers.** Grid thumbs (`PhotoThumb.vue`, `PhotoThumbVirtual.vue`) show a `360°` `ThumbBadge` in the top-start badge row for every viewer when `precomputed.is_360`; list rows (`PhotoListItem.vue`, `PhotoListItemVirtual.vue`) show a `360°` type label in place of the generic "Photo" label (video, live photo and RAW keep theirs). | 360° photos are recognisable before opening. | — | — | None. | Owner directive 2026-10-03. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-081-01 | Works offline: every runtime asset is bundled; no CDN or remote fetch. | Offline-only requirement. | Network tab shows only Lychee origin requests. | — | Owner directive. |
| NFR-081-02 | No new npm dependency: TypeScript, WebGL2 and Pointer Events only ([ADR-081-01](../../../6-decisions/ADR-081-01-in-house-webgl2-sphere-renderer.md)). The renderer is loaded as a lazy chunk (dynamic `import()`), only when a sphere is shown. | Dependency policy; bundle size for galleries without panoramas. | `package.json` unchanged; `vite build` output shows a separate chunk, not requested on a non-360 album. | — | Owner directive 2026-10-03. |
| NFR-081-03 | Camera and projection math (pointer delta → yaw/pitch, clamps of FR-081-10/11/12, zoom factor ↔ field of view, needed texture width, initial view) lives in pure functions in `resources/js/v8/utils/sphere.ts`; the GLSL lives in `resources/js/v8/utils/sphereShader.ts`; the composable `resources/js/v8/composables/useSphereViewer.ts` only wires canvas, events and WebGL to them. | Straight-line increments, reviewability. | Code review. | — | AGENTS.md. |
| NFR-081-04 | Rendering on demand: one draw per animation frame only when yaw, pitch, field of view, size or texture changed; no continuous render loop. No layout reads inside pointer-move handlers. | Battery, smoothness. | Chrome DevTools performance trace on a mid-range Android: ≥ 55 fps while dragging, no frames drawn at rest. | — | — |
| NFR-081-05 | With `prefers-reduced-motion: reduce`, the `z` zoom toggle jumps instead of animating. | Accessibility. | Manual check with the OS setting. | — | — |
| NFR-081-06 | Dependency change: `lychee-org/php-exif` constraint `^1.4.0` (the release carrying FR-081-01). The same `composer.lock` update refreshes the other locked packages within their existing constraints and adds `entropy/entropy` (required by `tomasvotruba/class-leak` 2.3.0). No new direct dependency. | Dependency policy. | `composer validate` clean; `composer.json` diff limited to the php-exif constraint. | LycheeOrg/php-exif 1.4.0. | Owner approval 2026-10-03. |
| NFR-081-07 | Detection adds no file read on upload: it uses the `Exif` object `Extractor` already builds. | Upload throughput. | Code review. | — | — |
| NFR-081-08 | RTL: the badge sits in the top-start corner; the header button follows the header's existing order; the drag direction is physical (content follows the pointer) in both directions. | RTL parity. | Manual check with an RTL locale. | — | — |

## UI / Interaction Mock-ups

```
 Lightbox, 360° photo, sphere view (ImageViewMode.Sphere)
+--------------------------------------------------------------------------+
| [<-]                          [▭ flat] [⚑] [▶] [⧉] [⤓] [✎] [i]            |
|                                                                          |
| [<]          perspective view of the sphere (WebGL2 canvas)          [>] |
|                  drag = look around   pinch / wheel = zoom               |
|                  z  + / -  = zoom     v = flat     ← → = navigate        |
|                                                                          |
|  Sunset over the bay                                                     |
|  12 Aug 2026                                          ★ ★ ★ ☆ ☆          |
+--------------------------------------------------------------------------+

 Lightbox, same photo, flat view (v or [▭ flat])
+--------------------------------------------------------------------------+
| [<-]                          [◍ sphere] [⚑] [▶] [⧉] [⤓] [✎] [i]          |
|        +----------------------------------------------------------+      |
| [<]    |  equirectangular image (Feature 078 zoom, faces, NSFW)   |  [>] |
|        +----------------------------------------------------------+      |
|  Sunset over the bay                                                     |
+--------------------------------------------------------------------------+

 Photo edit dialog (v8), still photo
+---------------------------------------------+
| Edit Photo: Sunset over the bay             |
|  Title        [Sunset over the bay       ]  |
|  Description  [                          ]  |
|  Tags         [beach ×] [+]                 |
|  Taken date   [x] [2026-08-12 19:42     ]   |
|  [x] 360° photo                             |
|  License      [CC BY 4.0             v]     |
|                                   [ Save ]  |
+---------------------------------------------+

 Album grid                         List view
+----------+  +----------+          +------------------------------------+
|[360°]    |  |          |          | [img] Sunset over the bay   360°   |
|  thumb   |  |  thumb   |          | [img] Harbour               video  |
+----------+  +----------+          +------------------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-081-01 | Upload full-sphere GPano JPEG (exiftool reader) → `is_360` true, crop `null`. |
| S-081-02 | Same upload with the Imagick reader (`has_exiftool` off) → same result. |
| S-081-03 | Upload partial GPano JPEG → `is_360` true, crop scaled to the file width. |
| S-081-04 | Upload JPEG without GPano → `is_360` false. |
| S-081-05 | GPano with `UsePanoramaViewer = false`, another projection, or a malformed crop → `is_360` false. |
| S-081-06 | `PhotoResource.precomputed.is_360` / `panorama`, `PhotoRatioResource.is_360s`, `PhotoDetailResource.panoramas`, `SearchPhotoResource.is_360s` carry the values. |
| S-081-07 | `PATCH /Photo` with `is_360` true/false sets the flag; without `is_360` leaves it unchanged; `is_360` true on a video → 422; listing caches invalidated on change. |
| S-081-08 | `POST /Photo::rotate` on a 360° photo → 422; flat photos rotate as before. |
| S-081-09 | `lychee:detect_360` flags `NULL` 360° photos, sets others to false, skips non-`NULL` photos and videos, reports unreadable originals and exits 1. |
| S-081-10 | Open a 360° photo in the v8 lightbox → sphere; drag rotates, swipe/wheel do not navigate, arrows navigate, tap rotates the overlay. |
| S-081-11 | `v` / header button switch to flat (Feature 078 zoom, faces) and back; the next photo opens as a sphere. |
| S-081-12 | Pinch / wheel / `z` / `+` / `-` / `0` / `escape` change the field of view within [30°, 100°]; ratings hidden while zoomed. |
| S-081-13 | Partial panorama: background outside the crop, view clamped to the covered area. |
| S-081-14 | Original exposed and the screen needs more pixels → original loaded and swapped; original URL `null` → never requested. |
| S-081-15 | WebGL2 unusable → flat view, no switch. Context lost then restored → sphere rebuilt. |
| S-081-16 | Navigate away / close → renderer disposed. |
| S-081-17 | Grid badge and list label shown for 360° photos. |
| S-081-18 | Rotate buttons hidden in the v8 dock for 360° photos. |

## Test Strategy
- **Unit (`tests/Unit`, `AbstractTestCase`):** `PanoramaDetectorTest` for every branch of FR-081-02 (S-081-01/03/04/05), with `Exif` objects built through its setters.
- **Feature_v3 (`BaseApiWithDataTest`):** upload tests for S-081-01 to S-081-04 (exiftool reader via `RequiresExifTool`, Imagick reader with `has_exiftool` off); resource tests for S-081-06 (v2 photo, v3 ratios, details, search); edit tests for S-081-07; rotate test for S-081-08.
- **ImageProcessing commands (`BaseApiWithDataTest`):** `Detect360Test` for S-081-09.
- **Frontend (v8):** no JS unit runner exists and none is added (NFR-081-02). The pure helpers in `sphere.ts` are checked by a scratch Playwright/Node script during implementation; S-081-10 to S-081-18 by a Playwright run against a scratch instance, plus a manual real-device check.
- **Docs/Contracts:** `php artisan typescript:transform` regenerates `resources/js/lychee.d.ts`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-081-01 | `photos.is_360` (nullable boolean, `NULL` = not checked yet), `photos.pano_full_width`, `pano_full_height`, `pano_crop_left`, `pano_crop_top` (nullable unsigned integers, `NULL` = full sphere). | migration, `Photo` model (casts, docblock), `HydrateMetadata` |
| DO-081-02 | `PHPExif\Exif` GPano getters (FR-081-01). | `lychee-org/php-exif` 1.4.0, `Extractor` |
| DO-081-03 | `App\Metadata\PanoramaInfo` (readonly: `is_360`, `full_width`, `full_height`, `crop_left`, `crop_top`) returned by `PanoramaDetector::detect()`. | `app/Metadata/` |
| DO-081-04 | `App\Http\Resources\Models\PanoramaResource` (`full_width`, `full_height`, `crop_left`, `crop_top`). | v2 and v3 resources, TypeScript types |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-081-01 | v2 photo payloads (`PhotoResource`) | `precomputed.is_360`, `panorama` | `PreComputedPhotoData`, `PhotoResource` |
| API-081-02 | REST GET `/api/v3/Albums/{album_id}/Photos` | `is_360s[]` | `PhotoRatioResource`, `QueryPhotoRatios` |
| API-081-03 | REST GET `/api/v3/Albums/{album_id}/Photos/details` (and the search details tier) | `panoramas[]` | `PhotoDetailResource`, `QueryPhotoDetails` |
| API-081-04 | REST GET v3 search photos | `is_360s[]` | `SearchPhotoResource`, `QuerySearchPhotos` |
| API-081-05 | REST PATCH `/api/v2/Photo` | optional `is_360` boolean | `EditPhotoRequest`, `PhotoController::update` |
| API-081-06 | REST POST `/api/v2/Photo::rotate` | 422 for 360° photos | `PhotoController::rotate` |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-081-01 | `php artisan lychee:detect_360 {offset=0} {limit=100} {tm=600}` | FR-081-05. |

### Telemetry Events
None.

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-081-01 | `tests/Samples/photosphere.jpg` | Small 2:1 JPEG with GPano `ProjectionType=equirectangular`, `UsePanoramaViewer=True`, no crop. |
| FX-081-02 | `tests/Samples/photosphere-partial.jpg` | Small JPEG with GPano full 8000×4000, crop 6000×2000 at (1000, 1000). |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-081-01 | Sphere view | 360° photo opened in the main v8 lightbox (FR-081-08). |
| UI-081-02 | Flat view of a 360° photo | `v` / header button (FR-081-09). |
| UI-081-03 | Flat, no switch | WebGL2 unusable (FR-081-15). |
| UI-081-04 | 360° badge / label | Grid and list (FR-081-17). |
| UI-081-05 | 360° checkbox | v8 photo edit dialog (FR-081-04). |

## Telemetry & Observability
No telemetry events. `lychee:detect_360` logs one `Log::warning` per unreadable original (FR-081-05); the renderer logs one `console.warn` when WebGL2 is unusable (FR-081-15).

## Documentation Deliverables
- Roadmap row #081.
- Knowledge map: `PanoramaDetector`, the sphere renderer (`sphere.ts`, `sphereShader.ts`, `useSphereViewer.ts`), the `lychee:detect_360` command, the 360° fields on the photo resources.
- [ADR-081-01](../../../6-decisions/ADR-081-01-in-house-webgl2-sphere-renderer.md).

## Fixtures & Sample Data
- FX-081-01, FX-081-02 (see catalogue), produced with exiftool from an existing sample.

## Spec DSL

```
domain_objects:
  - id: DO-081-01
    name: photos 360 columns
    fields:
      - name: is_360
        type: boolean|null
      - name: pano_full_width
        type: unsigned int|null
      - name: pano_full_height
        type: unsigned int|null
      - name: pano_crop_left
        type: unsigned int|null
      - name: pano_crop_top
        type: unsigned int|null
  - id: DO-081-02
    name: PHPExif\Exif GPano getters
  - id: DO-081-03
    name: App\Metadata\PanoramaInfo
  - id: DO-081-04
    name: App\Http\Resources\Models\PanoramaResource
routes:
  - id: API-081-02
    method: GET
    path: /api/v3/Albums/{album_id}/Photos
  - id: API-081-03
    method: GET
    path: /api/v3/Albums/{album_id}/Photos/details
  - id: API-081-05
    method: PATCH
    path: /api/v2/Photo
  - id: API-081-06
    method: POST
    path: /api/v2/Photo::rotate
cli_commands:
  - id: CLI-081-01
    command: php artisan lychee:detect_360
fixtures:
  - id: FX-081-01
    path: tests/Samples/photosphere.jpg
  - id: FX-081-02
    path: tests/Samples/photosphere-partial.jpg
ui_states:
  - id: UI-081-01
    description: Sphere view
  - id: UI-081-02
    description: Flat view of a 360° photo
  - id: UI-081-03
    description: Flat, no switch (WebGL2 unusable)
  - id: UI-081-04
    description: 360° badge / label
  - id: UI-081-05
    description: 360° checkbox in photo edit dialog
```
