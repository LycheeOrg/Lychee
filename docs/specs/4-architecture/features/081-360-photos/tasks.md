# Feature 081 Tasks – 360° Photos

_Status: Planned_  
_Last updated: 2026-10-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Detection and storage

- [ ] T-081-01 – Fixtures (FX-081-01, FX-081-02).  
  _Intent:_ `tests/Samples/photosphere.jpg` (small 2:1 JPEG resized from an existing sample, GPano `ProjectionType=equirectangular`, `UsePanoramaViewer=True`) and `tests/Samples/photosphere-partial.jpg` (GPano full 8000×4000, crop 6000×2000 at 1000/1000, file width ≠ 6000 so scaling is exercised); constants in `tests/Constants/TestConstants.php`.  
  _Verification commands:_  
  - `exiftool -G1 -XMP-GPano:all tests/Samples/photosphere*.jpg`

- [ ] T-081-02 – Failing `PanoramaDetectorTest` (FR-081-02, S-081-01, S-081-03, S-081-04, S-081-05).  
  _Intent:_ `tests/Unit/Metadata/PanoramaDetectorTest.php` (`AbstractTestCase`), `Exif` built with setters: full sphere; partial scaled (factor ≠ 1); crop equal to full → full sphere; `UsePanoramaViewer` false; `null` viewer flag; other projection; no projection; partial crop set (3 of 6); out-of-bounds crop; zero width. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=PanoramaDetectorTest` (expected red)

- [ ] T-081-03 – `PanoramaInfo` + `PanoramaDetector` (FR-081-02, S-081-05).  
  _Intent:_ `app/Metadata/PanoramaInfo.php` (readonly record), `app/Metadata/PanoramaDetector.php` (pure, small private helpers: `cropState()` returning absent/valid/malformed, `scale()`); license headers.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=PanoramaDetectorTest` (green)  
  - `make phpstan`

- [ ] T-081-04 – Failing `Photo360UploadTest` (FR-081-03, S-081-01 to S-081-04).  
  _Intent:_ `tests/Feature_v3/Photo/Photo360UploadTest.php` (`BaseApiWithDataTest`): upload FX-081-01 with exiftool (`RequiresExifTool`) and with `has_exiftool` off (Imagick); upload FX-081-02; upload a plain sample → `is_360` false; assert DB columns. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=Photo360UploadTest` (expected red)

- [ ] T-081-05 – Migration, model, `Extractor`, `HydrateMetadata` (FR-081-03, DO-081-01, NFR-081-07).  
  _Intent:_ migration adding `is_360` (nullable boolean) and `pano_full_width`, `pano_full_height`, `pano_crop_left`, `pano_crop_top` (nullable unsigned int) to `photos`, with `down()`; `Photo` docblock + casts; `Extractor` public fields filled from `PanoramaDetector::detect($exif, $metadata->width)`; `HydrateMetadata` copies them when `is_360 === null`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=Photo360UploadTest` (green)  
  - `php artisan test --filter=PanoramaDetectorTest`  
  - `make phpstan`

### I2 – API exposure

- [ ] T-081-06 – Failing `Photo360ResourcesTest` (FR-081-06, S-081-06).  
  _Intent:_ `tests/Feature_v3/Photo/Photo360ResourcesTest.php`: after uploading FX-081-01/02 and a plain photo into one album: v2 photo payload `precomputed.is_360` and `panorama`; `GET /api/v3/Albums/{id}/Photos` `is_360s`; `/details` `panoramas`; v3 search `is_360s`. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=Photo360ResourcesTest` (expected red)

- [ ] T-081-07 – Resources and queries (FR-081-06, DO-081-04).  
  _Intent:_ `PanoramaResource` (`fromPhoto(Photo): ?self`, `fromRow(object): ?self`); `PreComputedPhotoData::$is_360`; `PhotoResource::$panorama`; `QueryPhotoRatios` selects `photos.is_360` → `PhotoRatioResource::$is_360s`; `QueryPhotoDetails` → `PhotoDetailResource::$panoramas`; `QuerySearchPhotos` → `SearchPhotoResource::$is_360s`; `php artisan typescript:transform`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=Photo360ResourcesTest` (green)  
  - `php artisan test --filter=PhotoRatiosV3Test`  
  - `php artisan test --filter=PhotoDetailsV3Test`  
  - `make phpstan`  
  - `npm run check`

### I3 – Manual flag and rotation guard

- [ ] T-081-08 – Failing `Photo360EditTest` and `Photo360RotateTest` (FR-081-04, FR-081-07, S-081-07, S-081-08).  
  _Intent:_ `tests/Feature_v3/Photo/Photo360EditTest.php`: PATCH `/Photo` with `is_360` true then false; without `is_360` (v7 payload) → unchanged; `is_360: "yes"` → 422; `is_360: true` on a video → 422; listing cache entry evicted after a change (ratios endpoint reflects it with `managed_cache_albums_enabled` on). `tests/Feature_v3/Photo/Photo360RotateTest.php`: rotate a 360° photo → 422 and file checksum unchanged; rotate a flat photo → 200. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=Photo360EditTest` (expected red)  
  - `php artisan test --filter=Photo360RotateTest` (expected red)

- [ ] T-081-09 – Edit request, controller, rotate guard (FR-081-04, FR-081-07, API-081-05, API-081-06).  
  _Intent:_ `RequestAttribute::IS_360_ATTRIBUTE = 'is_360'`; `EditPhotoRequest` rule `sometimes|boolean`, `is360(): ?bool`, video + true → validation error; `PhotoController::update` sets `is_360` when given and dispatches `PhotoSaved` when it changed (merged into the existing `dispatchIf`); `PhotoController::rotate` throws a 422 for `is_360 === true`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=Photo360EditTest` (green)  
  - `php artisan test --filter=Photo360RotateTest` (green)  
  - `make phpstan`

### I4 – Detection command

- [ ] T-081-10 – Failing `Detect360Test` (FR-081-05, S-081-09).  
  _Intent:_ `tests/ImageProcessing/Commands/Detect360Test.php` (`BaseApiWithDataTest`): upload FX-081-01, FX-081-02, a plain photo and a video; set `is_360` back to `NULL` for the photos; set one 360° photo to `false` by hand (non-`NULL`, must stay); run `lychee:detect_360` → expected flags and crops, video still `NULL`, output summary; second run prints "No photos require 360° detection."; an original whose file is deleted → failed count, exit code 1, photo still `NULL`. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=Detect360Test` (expected red)

- [ ] T-081-11 – `Detect360` command (FR-081-05, CLI-081-01).  
  _Intent:_ `app/Console/Commands/ImageProcessing/Detect360.php`, signature `lychee:detect_360 {offset=0} {limit=100} {tm=600}`, same structure as `ExifLens`; `PhotoSaved` once with the ids found; `Log::warning` per failure.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=Detect360Test` (green)  
  - `make phpstan`

### I5 – Frontend data, markers, edit, dock

- [ ] T-081-12 – SoA adapter, badge, list label, lang (FR-081-06, FR-081-17, S-081-17).  
  _Intent:_ `adaptPhotoTile.ts` (`precomputed.is_360`, `mergePhotoDetail` → `photo.panorama`); `360°` `ThumbBadge` in `PhotoThumb.vue` and `PhotoThumbVirtual.vue`; `360°` label in `PhotoListItem.vue` and `PhotoListItemVirtual.vue`. Lang keys in every `lang/<locale>/` file (English text outside `en`): `gallery.photo.edit.is_360`, `gallery.photo.actions.show_sphere`, `gallery.photo.actions.show_flat`, `dialogs.keybindings.toggle_sphere`; `php artisan lang:json`.  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint <touched files>`

- [ ] T-081-13 – Edit checkbox and dock (FR-081-04, FR-081-07, S-081-18, UI-081-05).  
  _Intent:_ `photo-service.ts` `PhotoUpdateRequest.is_360?: boolean`; `PhotoEdit.vue` `UCheckbox` before the license row, hidden for videos, sent on save; `Dock.vue` hides the rotate buttons when `precomputed.is_360`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint <touched files>`

### I6 – Sphere renderer

- [ ] T-081-14 – Pure math `v8/utils/sphere.ts` (FR-081-10 to FR-081-14, NFR-081-03).  
  _Intent:_ types `SphereView { yaw, pitch, fov }`, `PanoramaCoverage`; `coverageFrom(panorama, width, height)`; `initialView(coverage)`; `dragView(view, dx, dy, viewportHeight)`; `fovFromZoom` / `zoomFromFov`; `zoomAround(view, s, direction)`; `clampView(view, coverage, aspect)`; `neededTextureWidth(...)`; `isTap(...)` reuse from `panZoom.ts` when it fits. Scratch check script (not committed) asserting each function on known values.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - scratch: `node --experimental-strip-types <scratchpad>/sphere-check.ts`

- [ ] T-081-15 – Shader `v8/utils/sphereShader.ts` (FR-081-12, FR-081-14).  
  _Intent:_ WebGL2 vertex (full-screen triangle) and fragment shader: inverse view rotation, ray → longitude/latitude, coverage mapping with background outside, `textureGrad` with gradients from a continuous longitude.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [ ] T-081-16 – `useSphereViewer.ts` + `SphereView.vue` (FR-081-10, FR-081-11, FR-081-15, FR-081-16, NFR-081-02, NFR-081-04, NFR-081-05).  
  _Intent:_ canvas sizing with `ResizeObserver` (DPR), WebGL2 context + program + texture (`createImageBitmap` resize to `MAX_TEXTURE_SIZE`, mipmaps), draw on demand via `requestAnimationFrame`, Pointer Events drag/pinch/tap, wheel, context lost/restored, dispose; emits `tap` and `unavailable`; exposes `toggle/zoomIn/zoomOut/reset` and `isZoomed`. `SphereView.vue` loaded with `defineAsyncComponent` (lazy chunk).  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint <touched files>`  
  - `npm run build` (separate chunk present)

### I7 – Lightbox integration

- [ ] T-081-17 – `PhotoState`, `PhotoBox`, `PhotoPanel` (FR-081-08, FR-081-10, FR-081-11, FR-081-15, S-081-10, S-081-12, S-081-15).  
  _Intent:_ `ImageViewMode.Sphere`; `is_sphere_flat` and `is_sphere_unavailable` reset on photo change; `PhotoBox` renders `SphereView` for that mode (main lightbox only), registers `zoom_controls`/`is_zoomed`, routes `tap` to `rotateOverlay`, gates `useSwipe`, hides faces/NSFW/minimap; `PhotoPanel` window wheel navigation off in sphere mode; slideshow keeps flat.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint <touched files>`

- [ ] T-081-18 – Flat switch: header, `v`, keyboard help (FR-081-09, S-081-11).  
  _Intent:_ `PhotoHeader.vue` toggle button (hidden when unavailable); `v` in `usePanelShortcuts.ts` next to the zoom keys; `KeybindingsHelp.vue` (v8) entry `dialogs.keybindings.toggle_sphere`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint <touched files>`

- [ ] T-081-19 – Texture upgrade to the original (FR-081-14, S-081-14).  
  _Intent:_ start from `medium2x`/`medium`; on view/size change, compare `neededTextureWidth` with the current texture width; load the original when exposed and not RAW (`pickZoomSource` rule), swap after decode, keep the current texture on failure.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

### I8 – Verification and docs

- [ ] T-081-20 – Playwright scratch run (S-081-10 to S-081-18).  
  _Intent:_ scratch instance with FX-081-01/02 and a flat photo: drag changes the view, swipe/wheel do not navigate, arrows do, tap rotates overlay, `v` and header toggle, zoom keys and limits, ratings hidden while zoomed, partial clamp, original requested only when exposed, WebGL disabled → flat without switch, dispose on navigation (no live contexts), badge and list label, dock without rotate; screenshot at yaw 180° shows no seam.  
  _Verification commands:_  
  - scratch Playwright script (not committed)

- [ ] T-081-21 – Quality gate, knowledge map, roadmap, drift gate.  
  _Intent:_ full quality gate; knowledge-map entries; roadmap status; Implementation Drift Gate in plan.md.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `npm run format`  
  - `npm run check`  
  - `php artisan test --filter=PanoramaDetectorTest`, `Photo360UploadTest`, `Photo360ResourcesTest`, `Photo360EditTest`, `Photo360RotateTest`, `Detect360Test`, `PhotoRatiosV3Test`, `PhotoDetailsV3Test`, `LangTest` (one at a time)  
  - `make phpstan`

- [ ] T-081-22 – Owner real-device check (S-081-10 to S-081-16, NFR-081-04, NFR-081-05, NFR-081-08).  
  _Intent:_ phone and desktop: drag smoothness and performance trace, pinch, partial sphere, reduced motion, RTL, context loss after tab switch.

## Notes / TODOs
- FR coverage: FR-081-01 (T-081-04/05 via both readers), FR-081-02 (T-081-02/03), FR-081-03 (T-081-04/05), FR-081-04 (T-081-08/09/13), FR-081-05 (T-081-10/11), FR-081-06 (T-081-06/07/12), FR-081-07 (T-081-08/09/13), FR-081-08 (T-081-17), FR-081-09 (T-081-18), FR-081-10/11 (T-081-14/16/17), FR-081-12/13 (T-081-14/15), FR-081-14 (T-081-14/15/19), FR-081-15/16 (T-081-16/17), FR-081-17 (T-081-12).
- `composer.json` / `composer.lock` (php-exif `^1.4.0`, NFR-081-06) are already changed in the working tree and go into the first commit.
- Never run two test commands at once (shared SQLite database).
