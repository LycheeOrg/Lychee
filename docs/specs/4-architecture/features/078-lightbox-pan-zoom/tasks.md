# Feature 078 Tasks – Lightbox Pan & Zoom

_Status: Implemented (manual check pending)_  
_Last updated: 2026-10-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Configs and contract

- [x] T-078-01 – Failing `PanZoomConfigTest` (FR-078-01, FR-078-20, S-078-01).  
  _Intent:_ `tests/Feature_v3/PanZoom/PanZoomConfigTest.php` (extends `BaseApiWithDataTest`): `GET /api/v2/Gallery::Init` returns `photo_click_action = overlay`, `is_photo_minimap_enabled = true`, `is_photo_minimap_enabled_mobile = true`, `photo_minimap_idle_opacity = 25`, `photo_minimap_idle_opacity_mobile = 25`, `photo_minimap_fade_delay = 2`; after updating the configs, the new values are returned. Confirm red.  
  _Verification commands:_  
  - `php artisan test --filter=PanZoomConfigTest` (expected red)

- [x] T-078-02 – Enum, migration, `InitConfig` (FR-078-01, FR-078-20, S-078-01).  
  _Intent:_ `App\Enum\PhotoClickAction` (`OVERLAY = 'overlay'`, `ZOOM = 'zoom'`); `BaseConfigMigration` adding the six configs to category `gestures` (orders after the two existing gesture configs); `InitConfig` typed properties (`PhotoClickAction`, `bool`, `int`) read via `getValueAsEnum`/`getValueAsBool`/`getValueAsInt`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test --filter=PanZoomConfigTest` (green)  
  - `make phpstan`

- [x] T-078-03 – Lang, TypeScript types, `LycheeState` (FR-078-01, FR-078-20).  
  _Intent:_ description + details entries for the six keys in every `lang/<locale>/all_settings.php` (English text in non-English locales); the six keys added to `SettingsController::V8_CONFIGS` so they are hidden when v8 is disabled (tests `testSettingsHiddenWithoutV8`/`testSettingsVisibleWithV8`); `php artisan lang:json`; `php artisan typescript:transform`; `LycheeState` fields with defaults and init mapping.  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`  
  - `npm run format`  
  - `npm run check`

### I2 – Pure helpers

- [x] T-078-04 – Scale and pan math in `v8/utils/panZoom.ts` (FR-078-03, FR-078-09, NFR-078-02).  
  _Intent:_ `PanZoomState`; `maxScale(sourceWidth, fitWidth)` = `max(4, 4 × native)`; `clampScale`; `zoomAround(state, point, scale)`; `clampPan` with rubber-band resistance and centring on axes smaller than the viewport; `pickZoomSource(photo)` (original when URL exposed and not `precomputed.is_raw`, else `medium2x`, else `medium`).  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-078-05 – Classification and minimap mapping in `panZoom.ts` (FR-078-05, FR-078-07, FR-078-18, NFR-078-02).  
  _Intent:_ `classifyGesture` (tap vs drag, 5 px), `isDoubleTap` (250 ms, 30 px), `classifyHitTarget(point, imageRect, overlayRect, overlayType, imageviewRect)` → `picture | overlay | none-zone | letterbox`; `photoToMinimap` / `minimapToPan`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

### I3 – Transformed layer, pan, pinch, swipe

- [x] T-078-06 – Transform layer in `PhotoBox.vue` (FR-078-02, FR-078-13, S-078-14).  
  _Intent:_ wrap the `Medium`/`Original` `<img>` and the face/NSFW overlays in one layer driven by `translate3d(x,y,0) scale(s)`, `transform-origin: 0 0`; zoomable computed (view mode `Medium`/`Original`, slideshow inactive).  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-078-07 – `usePanZoom` composable: pan and pinch (FR-078-08, FR-078-09, NFR-078-01, NFR-078-04, S-078-06, S-078-07, S-078-08).  
  _Intent:_ Pointer Events state machine, rAF-batched transform writes; pinch around the midpoint; single-pointer drag pans when `s > 1` with rubber band and spring-back (instant under reduced motion); `grabbing` cursor during a pan.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-078-08 – Swipe, touch-action, click suppression (FR-078-07, FR-078-15, NFR-078-05, S-078-09, S-078-23, S-078-24).  
  _Intent:_ keep `useSwipe` for navigation (touch events survive native pinch on non-zoomable photos), gated by `isSwipeBlocked()` so only one-finger gestures that started at fit navigate; 50 px threshold, RTL mapping unchanged; `touch-action: none` on `#imageview` for zoomable photos only, `isPageZoomed()` guard kept for non-zoomable ones; suppress the `click` after a drag or multi-pointer gesture (capture phase, covers face boxes); `draggable="false"` and `user-select: none` on the zoom layer.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-078-09 – Zoom reset (FR-078-14, S-078-15).  
  _Intent:_ state scoped to the `PhotoBox` instance; slideshow start resets to fit; resize/details drawer recomputes fit and `max`.  
  _Verification commands:_  
  - `npm run check`

### I4 – Click modes and hit targets

- [x] T-078-10 – `overlay` mode taps (FR-078-04, FR-078-07, S-078-02, S-078-03).  
  _Intent:_ single tap rotates after the 250 ms double-tap window; double-tap on the picture toggles 2× around the pointer ⇄ fit; drags never count as taps.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-078-11 – `zoom` mode hit targets and cursors (FR-078-02, FR-078-05, FR-078-06, S-078-04, S-078-05, S-078-13, S-078-22).  
  _Intent:_ picture → 2× at the click point / reset; overlay, letterbox and `none` hit zone (bottom 96 px × start 50 %) → `rotateOverlay`; no overlay targets when `is_exif_disabled`; `zoom-in`/`zoom-out` cursors over the picture only; non-zoomable photos rotate on any click in both modes, without delay.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

### I5 – Wheel and keyboard

- [x] T-078-12 – Wheel (FR-078-10, S-078-10).  
  _Intent:_ `wheel` on `#imageview`: `ctrlKey` → zoom; plain wheel → zoom unless `s = 1` and `is_scroll_to_navigate_photos_enabled` (behaviour while zoomed per Q-078-08); `stopPropagation()` whenever consumed so `PhotoPanel`'s window listener does not navigate.  
  _Verification commands:_  
  - `npm run check`

- [x] T-078-13 – Keyboard and rating suppression (FR-078-11, FR-078-21, S-078-16, S-078-25).  
  _Intent:_ `PhotoState.is_zoomed` and `zoom_controls` (`toggle`, `zoomIn`, `zoomOut`, `reset`), published by `PhotoBox` while zoomable and cleared on unmount; `definePanelShortcuts` adds `z` (toggle 2× ⇄ fit), `+`/`=` (×1.5), `-` (÷1.5) for zoomable photos and, while zoomed, overrides `escape` and `0` with reset and drops `1`–`5`; `PhotoRatingOverlay` hides itself and `PhotoPanel` hides the `Dock` while zoomed.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

### I6 – Zoom source upgrade

- [x] T-078-14 – Load the sharper source on zoom (FR-078-03, FR-078-12, S-078-07, S-078-11, S-078-12).  
  _Intent:_ when `s × fit width` > `naturalWidth` of the displayed image and `pickZoomSource()` is wider, load it; `decode()` then swap `src` and drop `srcset` without moving the image; never request a `null` URL or a RAW photo's original; keep current image on error.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

### I7 – Minimap

- [x] T-078-15 – `ZoomMinimap.vue` render and interaction (FR-078-17, FR-078-18, S-078-17, S-078-18, S-078-19).  
  _Intent:_ top-start (`top-7 ltr:left-7 rtl:right-7`), 160 px / 112 px below `sm`, `small`/`small2x` image, visible-area rectangle clipped to the minimap, `N.N×` label; pointer down inside the rectangle grabs it, elsewhere centres then drags (clamped); events stopped from reaching `#imageview`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-078-16 – Minimap fade, device class, enable switches (FR-078-16, FR-078-19, FR-078-20, S-078-20, S-078-21).  
  _Intent:_ `isTouchDevice()` picks the per-class enable flag and idle opacity; opaque on show and on any pan/zoom/minimap interaction; fade (300 ms) after `photo_minimap_fade_delay` s; mouse hover keeps opaque (any device class); idle opacity 0 → `pointer-events-none` while faded.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

### I8 – Docs, verification, quality gate

- [ ] T-078-17 – Manual verification, docs, quality gate (all S-078-02..25).  
  _Intent:_ run S-078-02..25 on desktop (mouse + trackpad, LTR + RTL) and a touch device, record results below; knowledge map entry for `usePanZoom`, `panZoom.ts`, `ZoomMinimap.vue`; roadmap status; Implementation Drift Gate report in plan.md.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `npm run format`  
  - `npm run check`  
  - `php artisan test --filter=PanZoomConfigTest`  
  - `php artisan test --filter=LangTest`  
  - `make phpstan`

## Notes / TODOs
- No frontend unit-test runner exists in the repository; frontend branches are covered by T-078-17's manual checklist (plan Follow-ups).
- 2026-10-03: S-078-02..12, S-078-15..22, S-078-25 verified with Playwright against a scratch instance (desktop LTR + RTL, emulated touch phone, reduced motion); results in plan.md › Drift Gate Report. Still open for T-078-17: real devices, videos (S-078-13, S-078-24), faces (S-078-14, S-078-23), Flow/Moderation unchanged.
