# Feature Plan 082 – 360° Photos

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented (manual check pending)  
_Last updated:_ 2026-10-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
360° photos are flagged on upload (full and partial spheres), can be corrected by hand or detected later by command, and open in the v8 lightbox as a sphere with the same navigation, zoom keys and overlay behaviour as flat photos.

Success signals:
- Uploading FX-082-01/02 with the exiftool and the Imagick reader stores the same flag and crop (S-082-01 to S-082-03).
- The v2 and v3 photo payloads carry `is_360` / `panorama` (S-082-06).
- In the browser, a 360° photo is a draggable sphere; arrows navigate; `v` switches to flat; no swipe or wheel navigation happens while in the sphere (S-082-10 to S-082-12).
- No new npm dependency; the renderer is a lazy chunk (NFR-082-02).

## Scope Alignment
- **In scope:** FR-082-01 to FR-082-17, NFR-082-01 to NFR-082-08.
- **Out of scope:** everything listed under the spec's Non-Goals (v7, 360° video, other projections, gyroscope and keyboard look-around, boxes on the sphere, Native-reader detection, texture splitting, bulk flagging).

## Dependencies & Interfaces
- `lychee-org/php-exif` 1.4.0 GPano getters (FR-082-01), already installed (`composer.json` `^1.4.0`, lock refreshed, not committed yet).
- Feature 078: `usePanZoom` tap/drag classification, `PhotoState.zoom_controls` / `is_zoomed`, `definePanelShortcuts` zoom keys, `pickZoomSource` access rule.
- Feature 064/065 SoA tiers: `QueryPhotoRatios`, `QueryPhotoDetails`, `adaptPhotoTile.ts`.
- `ManagedCachePhotoListingInvalidator` via `PhotoSaved`.
- exiftool (for fixtures and the exiftool-reader tests, `RequiresExifTool` trait) and Imagick (Imagick-reader tests).
- Playwright (already a dev dependency) for the scratch browser check.

## Assumptions & Risks
- **Assumptions:** CI images have Imagick; exiftool tests skip through `RequiresExifTool` where it is missing. `Extractor::$width` is the pixel width of the file the GPano crop refers to.
- **Risks / Mitigations:**
  - ImageMagick 6 may not expose XMP as `GPano:*` properties (verified only on 7.1.1). Mitigation: the Imagick-reader upload test fails loudly in CI if so; the exiftool path is unaffected.
  - Mobile GPUs with `MAX_TEXTURE_SIZE` 4096 make large spheres soft. Accepted (texture splitting is a non-goal).
  - Seam at ±180° with mipmaps. Mitigation: explicit `textureGrad` with gradients from a continuous longitude (FR-082-14), checked in the Playwright screenshot at yaw 180°.
  - `PhotoBox.vue` is already large (589 lines). Mitigation: the sphere lives in its own component `SphereView.vue`, `PhotoBox` only switches on `ImageViewMode.Sphere`.
  - No JS unit runner: pure helpers checked by a scratch script, not committed (spec Test Strategy).

## Implementation Drift Gate
After the last task: map every FR/NFR to code and tests in a table under this section, rerun the quality gate (`vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, every test class listed in tasks, `make phpstan`), and record findings and lessons here.

### Drift Gate Report – 2026-10-03 (pre-manual check)

**Verification evidence**
- Test classes, run one at a time, all green: `PanoramaDetectorTest` (13), `Photo360UploadTest` (5, exiftool and Imagick readers), `Photo360ResourcesTest` (5), `Photo360EditTest` (6), `Photo360RotateTest` (1), `Detect360Test` (4), and the regression classes `PhotoRatiosV3Test` (28), `PhotoDetailsV3Test` (17), `QuerySearchPhotosTest` (10), `QuerySearchPhotoDetailsTest` (9), `SearchV3ParityTest` (6), `PhotoEditTest` (5), `PhotoRotateTest` (3), `LangTest` (2).
- `vendor/bin/php-cs-fixer fix`, `make phpstan`, `npm run format`, `npm run check`, eslint on every touched frontend file: clean. `vite build` emits `SphereView-*.js` as its own chunk (11.6 kB, 4.9 kB gzip).
- `sphere.ts`: assertion script run with Node on a copy of the module (coverage, clamps, drag, zoom around a point, needed texture width, rotation matrix orthonormality, start source). Kept outside the repository.
- Real app, scratch instance (SQLite, storage and uploads in the agent scratchpad, PHP built-in server with `variables_order=EGPCS`, `V8_ENABLED` and `STRUCT_OF_ARRAY_ENABLED` on), photos imported with `lychee:sync` (detection verified on import: full, partial with crop 800×400 / 100×100, flat), driven with Playwright + system Chromium (SwiftShader WebGL). 34 checks, all passing:
  - S-082-10: sphere canvas instead of `<img>`, lazy chunk requested, drag changes the view without navigating or rotating the overlay, tap rotates the overlay, wheel zooms without navigating, arrow key navigates.
  - S-082-11: `v` shows the flat image with the header button "Show as sphere", the button returns to the sphere; no toggle on a flat photo.
  - S-082-12: `z` zooms and `z` again returns to the identical frame; `escape` while zoomed keeps the photo open.
  - S-082-13: partial panorama fills the view at 75°, background above and below the crop once zoomed out.
  - S-082-14: large sphere (4000×2000) starts from `medium2x` and fetches the original at 1280 px (needs about 4300 px).
  - S-082-15: Chromium with WebGL disabled shows the flat image without the toggle.
  - S-082-16: no canvas left after navigating to a flat photo.
  - S-082-17: `360°` badge on every 360° grid thumb; list rows show `360°` in place of "Photo".
  - S-082-18: no rotate buttons in the dock for a 360° photo, present for a flat one.
  - S-082-07 (UI): checkbox in the edit dialog flags a flat photo, the open photo switches to the sphere immediately, persists after reload, unflagging returns to flat.
  - FR-082-14 seam: a synthetic panorama that wraps continuously shows no line at yaw 180°.

**FR → implementation**
- FR-082-01: `lychee-org/php-exif` 1.4.0 (`composer.json` `^1.4.0`).
- FR-082-02: `App\Metadata\PanoramaDetector`, `PanoramaInfo` — `PanoramaDetectorTest`.
- FR-082-03: `Extractor`, `HydrateMetadata`, migration `2026_10_03_000005_add_360_columns_to_photos`, `Photo` casts — `Photo360UploadTest`.
- FR-082-04: `EditPhotoRequest` (`is_360`, `is360()`), `PhotoController::update`, `PhotoEdit.vue`, `photo-service.ts` — `Photo360EditTest`, browser check.
- FR-082-05: `App\Console\Commands\ImageProcessing\Detect360` — `Detect360Test`.
- FR-082-06: `PanoramaResource`, `PreComputedPhotoData`, `PhotoResource`, `PhotoRatioResource`/`QueryPhotoRatios`, `PhotoDetailResource`/`QueryPhotoDetails`, `SearchPhotoResource`/`QuerySearchPhotos`, `adaptPhotoTile.ts` — `Photo360ResourcesTest`.
- FR-082-07: `PhotoController::rotate` (`MediaFileUnsupportedException`), `Dock.vue` — `Photo360RotateTest`, browser check.
- FR-082-08 to FR-082-16: `PhotoState.ts`, `PhotoBox.vue`, `PhotoPanel.vue`, `PhotoHeader.vue`, `usePanelShortcuts.ts`, `KeybindingsHelp.vue`, `SphereView.vue`, `useSphereViewer.ts`, `sphere.ts`, `sphereShader.ts`, `webgl.ts` — browser checks.
- FR-082-17: `ThumbBadge.vue` (`text` prop), `PhotoThumb.vue`, `PhotoThumbVirtual.vue`, `PhotoListItem.vue`, `PhotoListItemVirtual.vue` — browser check.

**Low-impact divergences, folded into the spec**
- `PhotoState.imageViewMode` is shared with v7, so it stays flat; `isSphereCapable`/`isSphereView` getters drive the v8 `PhotoBox` (FR-082-08).
- On the Struct-of-Arrays path the photo has no size variants or crop until its details are merged; the sphere waits for its first source and sets its initial view after the first upload (FR-082-14, FR-082-13).
- Photos too small for medium variants start from the original, else the small variants (FR-082-14).
- Drag speed is `fov ÷ viewport height` degrees per pixel rather than an exact point-under-pointer projection (FR-082-10).
- A lost WebGL context draws nothing until restored (FR-082-15).
- The list label replaces the generic "Photo" label (FR-082-17).
- `wheelDelta()` moved from `usePanZoom.ts` to `utils/panZoom.ts` so both composables share it.

**Outstanding (T-082-22)**
- Real touch device (iOS Safari, Android Chrome): pinch, one-finger drag without swipe navigation, NFR-082-04 performance trace, context loss after a tab switch.
- Reduced motion (NFR-082-05) and RTL (NFR-082-08).
- Images served from S3 with a different origin need CORS for WebGL; without it the first texture fails and the photo falls back to flat (FR-082-15). Not exercised (no S3 in the scratch instance).

**Lessons**
- The login route allows 10 attempts per hour; browser scripts against a scratch instance should log in once and reuse the saved `storageState`.
- A migration renamed after a test run leaves the old name in `database/database.sqlite`'s `migrations` table; update that row instead of wiping the test database.

## Increment Map

1. **I1 – Detection and storage** (FR-082-01 to FR-082-03)
   - _Preconditions:_ php-exif 1.4.0 installed.
   - _Steps:_ fixtures FX-082-01/02; failing `PanoramaDetectorTest`; `PanoramaInfo` + `PanoramaDetector`; failing `Photo360UploadTest`; migration, `Photo` casts/docblock, `Extractor` fields, `HydrateMetadata`.
   - _Commands:_ `php artisan test --filter=PanoramaDetectorTest`, `php artisan test --filter=Photo360UploadTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Exit:_ both test classes green.
2. **I2 – API exposure** (FR-082-06)
   - _Steps:_ failing `Photo360ResourcesTest`; `PanoramaResource`; `PreComputedPhotoData`, `PhotoResource`; `QueryPhotoRatios` + `PhotoRatioResource`; `QueryPhotoDetails` + `PhotoDetailResource`; `QuerySearchPhotos` + `SearchPhotoResource`; `php artisan typescript:transform`.
   - _Commands:_ `php artisan test --filter=Photo360ResourcesTest`, `php artisan test --filter=PhotoRatiosV3Test`, `php artisan test --filter=PhotoDetailsV3Test`, `make phpstan`, `npm run check`.
   - _Exit:_ green; `lychee.d.ts` regenerated.
3. **I3 – Manual flag and rotation guard** (FR-082-04, FR-082-07 backend)
   - _Steps:_ failing `Photo360EditTest`, `Photo360RotateTest`; `RequestAttribute::IS_360_ATTRIBUTE`, `EditPhotoRequest` rule `sometimes|boolean` + video check, `PhotoController::update` (set flag, `PhotoSaved` on change), `PhotoController::rotate` 422.
   - _Commands:_ `php artisan test --filter=Photo360EditTest`, `php artisan test --filter=Photo360RotateTest`, `make phpstan`.
4. **I4 – Detection command** (FR-082-05)
   - _Steps:_ failing `Detect360Test`; `App\Console\Commands\ImageProcessing\Detect360`.
   - _Commands:_ `php artisan test --filter=Detect360Test`, `make phpstan`.
5. **I5 – Frontend data, markers, edit, dock** (FR-082-04 UI, FR-082-06 frontend, FR-082-07 UI, FR-082-17)
   - _Steps:_ `adaptPhotoTile.ts` / `mergePhotoDetail()`; `ThumbBadge` in `PhotoThumb.vue` + `PhotoThumbVirtual.vue`; label in `PhotoListItem.vue` + `PhotoListItemVirtual.vue`; `PhotoEdit.vue` checkbox + `photo-service.ts` `PhotoUpdateRequest.is_360`; `Dock.vue` rotate hidden; lang keys in every `lang/<locale>/*.php` (English text in other locales) + `php artisan lang:json`.
   - _Commands:_ `npm run format`, `npm run check`, `npx eslint <touched files>`, `php artisan test --filter=LangTest`.
6. **I6 – Sphere renderer** (FR-082-10 to FR-082-16, NFR-082-02 to NFR-082-05)
   - _Steps:_ `v8/utils/sphere.ts` pure helpers (scratch check script); `v8/utils/sphereShader.ts`; `v8/composables/useSphereViewer.ts` (WebGL2 context, program, texture upload with `createImageBitmap` resize and mipmaps, on-demand draw, Pointer Events drag/pinch/tap, wheel, context loss/restore, dispose); `v8/components/gallery/photoModule/SphereView.vue` loaded through `defineAsyncComponent`.
   - _Commands:_ `npm run format`, `npm run check`, scratch Node/Playwright check.
7. **I7 – Lightbox integration** (FR-082-08, FR-082-09, FR-082-11 keys, FR-082-14)
   - _Steps:_ `PhotoState`: `ImageViewMode.Sphere`, `is_sphere_flat` (reset on photo change), `is_sphere_unavailable`; `PhotoBox.vue`: branch to `SphereView`, gate swipe, tap → overlay, register `zoom_controls`, hide faces/NSFW/minimap; `PhotoPanel.vue`: window wheel navigation inactive in sphere mode; `PhotoHeader.vue` toggle; `usePanelShortcuts.ts` `v`; `KeybindingsHelp.vue` entry; texture upgrade to the original.
   - _Commands:_ `npm run format`, `npm run check`, `npx eslint <touched files>`.
8. **I8 – Verification and docs**
   - _Steps:_ Playwright scratch run for S-082-10 to S-082-18; full quality gate; knowledge map, roadmap, drift gate; owner real-device check.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-082-01 | I1 / T-082-01, T-082-02, T-082-04, T-082-05 | `PanoramaDetectorTest`, `Photo360UploadTest` |
| S-082-02 | I1 / T-082-04, T-082-05 | `Photo360UploadTest` (Imagick reader) |
| S-082-03 | I1 / T-082-02 to T-082-05 | partial fixture |
| S-082-04 | I1 / T-082-02 to T-082-05 | plain sample |
| S-082-05 | I1 / T-082-02, T-082-03 | `PanoramaDetectorTest` |
| S-082-06 | I2 / T-082-06, T-082-07 | `Photo360ResourcesTest` |
| S-082-07 | I3 / T-082-08, T-082-09 | `Photo360EditTest` |
| S-082-08 | I3 / T-082-08, T-082-09 | `Photo360RotateTest` |
| S-082-09 | I4 / T-082-10, T-082-11 | `Detect360Test` |
| S-082-10 | I6, I7 / T-082-15 to T-082-18, T-082-20 | Playwright |
| S-082-11 | I7 / T-082-17, T-082-18, T-082-20 | Playwright |
| S-082-12 | I6, I7 / T-082-14, T-082-17, T-082-20 | Playwright |
| S-082-13 | I6 / T-082-14, T-082-15, T-082-20 | Playwright with FX-082-02 |
| S-082-14 | I7 / T-082-19, T-082-20 | Playwright network log |
| S-082-15 | I6, I7 / T-082-16, T-082-17, T-082-20 | Playwright with WebGL disabled |
| S-082-16 | I6 / T-082-16, T-082-20 | Playwright |
| S-082-17 | I5 / T-082-12, T-082-20 | Playwright |
| S-082-18 | I5 / T-082-13, T-082-20 | Playwright |

## Analysis Gate
Completed 2026-10-03 (agent self-review; owner acknowledged by asking to implement).

1. Specification completeness — pass: goals, FR-082-01 to 17, NFR-082-01 to 08, ASCII mock-ups present; all twelve answers folded into normative sections.
2. Open questions — pass: no open entry in [open-questions.md](open-questions.md); ADR-082-01 records the renderer choice (Q-082-01).
3. Plan alignment — pass: plan links spec and tasks; dependencies (php-exif 1.4.0, Feature 078 contracts) match the spec.
4. Tasks coverage — pass: every FR maps to at least one task (see tasks notes); each increment starts with failing tests where a PHP test harness exists; frontend checks are scripted Playwright runs because no JS unit runner exists (accepted in spec Test Strategy, NFR-082-02).
5. Working-agreement compliance — pass: spec-first, no new dependency beyond the approved php-exif upgrade, detection isolated in a pure helper returning a result record, sphere math isolated in `sphere.ts`; ADR-082-01 and the Feature 078 spec reviewed.
6. Tooling readiness — pass: commands listed per increment and per task.

## Exit Criteria
- All tasks `[x]`; quality gate green (`vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, every listed test class, `make phpstan`).
- `php artisan typescript:transform` and `php artisan lang:json` rerun.
- Knowledge map and roadmap updated; drift gate recorded above.
- Owner real-device check done (T-082-22).

## Follow-ups / Backlog
- 360° video (spherical video metadata via ffprobe, video texture).
- Texture splitting for panoramas wider than `MAX_TEXTURE_SIZE`.
- Face/NSFW boxes projected on the sphere.
- Gyroscope look-around.
