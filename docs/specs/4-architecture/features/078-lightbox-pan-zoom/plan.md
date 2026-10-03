# Feature Plan 078 – Lightbox Pan & Zoom

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented (manual check pending)  
_Last updated:_ 2026-10-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Viewers can inspect details of a photo in the v8 lightbox on any device: pinch, double-tap, wheel, keyboard, or (when the admin enables `photo_click_action = zoom`) a single click on the picture. Zoom goes past native resolution, up to `max(4, 4 × native)`, and only ever draws on size variants the viewer may access. Existing click/swipe/wheel behaviour at fit is unchanged. A minimap shows where the viewer is while zoomed and fades when idle, configurable per device class.

Success signals:
- S-078-01 green in `tests/Feature_v3`.
- S-078-02..25 verified manually on desktop (mouse + trackpad) and on a touch device.
- `npm run check`, eslint, `make phpstan`, `php-cs-fixer` clean; `package.json` unchanged (NFR-078-03).

## Scope Alignment
- **In scope:** six configs + `PhotoClickAction` enum + `InitConfig`/`LycheeState` wiring (FR-078-01, FR-078-20); pure gesture helpers (NFR-078-02); `usePanZoom` composable replacing `useSwipe` in `PhotoBox.vue` (FR-078-02..15); click modes and hit targets (FR-078-04..06); keyboard zoom and rating suppression while zoomed (FR-078-11, FR-078-21); source upgrade (FR-078-12); `ZoomMinimap.vue` (FR-078-16..20).
- **Out of scope:** v7, Flow lightbox, videos/PDF/RAW/live photos/slideshow zoom, edge-drag navigation, frontend unit-test runner, any npm dependency (spec Non-Goals).

## Dependencies & Interfaces
- `App\Models\Extensions\BaseConfigMigration`, `Configs::type_range` validation (`0|1`, `positive`, `int:0:100`, `a|b`).
- `App\Http\Resources\GalleryConfigs\InitConfig` → `php artisan typescript:transform` → `resources/js/lychee.d.ts`.
- `resources/js/stores/LycheeState.ts`.
- `resources/js/v8/components/settings/ConfigGroup.vue` renders the new configs generically (`|` → `SliderField`, `int:` → bounded `NumberField`, `0|1` → `BoolField`, `positive` → `NumberField`): no settings UI code.
- `lang/<locale>/all_settings.php` (all locales, `LangTest`) → `php artisan lang:json`.
- v8 lightbox: `PhotoPanel.vue` (window `wheel` listener), `PhotoBox.vue` (`useSwipe`, image elements, face/NSFW overlays, `#imageview` click), `Overlay.vue` (`#image_overlay`, `pointer-events-none`), `composables/album/photoActions.ts` (`rotateOverlay`), `utils/keybindings-utils.ts` (`isTouchDevice`, `shouldIgnoreKeystroke`), `stores/PhotoState.ts` (`imageViewMode`, `srcSetMedium`, new zoom controller DO-078-05), `v8/composables/usePanelShortcuts.ts` (`definePanelShortcuts`, shared by every gallery view), `PhotoRatingOverlay.vue`.

## Assumptions & Risks
- **Assumptions:** the photo store exposes `small`/`small2x`/`medium`/`medium2x`/`original` dimensions for zoomable photos (original dimensions are sent even when its URL is downgraded to `null`).
- **Risk – wheel double handling:** `PhotoPanel.vue` listens to `wheel` on `window`. _Mitigation:_ `PhotoBox` handles `wheel` on `#imageview` and calls `stopPropagation()` whenever it consumes the event for zoom (FR-078-10), so the window listener only sees navigation wheels.
- **Risk – keyboard bindings are per view:** each gallery view declares its own map (`0`–`5` ratings and `escape` in `Album.vue`, `escape` in eight more views). _Mitigation:_ zoom keys are merged in `definePanelShortcuts`, which all views already call: while zoomed it overrides `escape`/`0` and drops `1`–`5`, so one listener handles each key press and `escape` never both unzooms and leaves the photo.
- **Risk – overlay rotation from `PhotoBox`:** `PhotoBox` already emits `rotateOverlay`; the overlay itself is rendered by `PhotoPanel`. _Mitigation:_ hit-target classification (NFR-078-02) runs in `PhotoBox` against the overlay's bounding rect read on pointer-down, with `#image_overlay` set to `pointer-events-none` kept and the click handled on `#imageview` — no new emit path.
- **Risk – srcset vs upgrade:** the medium view uses `srcset`/`sizes`. _Mitigation:_ on upgrade the composable drives a dedicated `src` and drops `srcset` once the new source is decoded (`HTMLImageElement.decode()`).
- **Risk – face boxes and native drag:** face boxes are `pointer-events-auto` with `@click.stop`, and a mouse drag on an `<img>` starts native drag-and-drop. _Mitigation:_ the click after a drag is suppressed in the capture phase; the zoom layer gets `draggable="false"` and `user-select: none` (FR-078-07).
- **Risk – RAW originals:** for `precomputed.is_raw` photos the original is the RAW/PDF file. _Mitigation:_ `pickZoomSource()` skips it (FR-078-03).
- **Risk – no automated frontend tests:** gesture logic is verified manually. _Mitigation:_ pure, typed helpers in `panZoom.ts`; explicit manual checklist T-078-17.

## Implementation Drift Gate
Run after T-078-17 with the latest quality gate green. Record in this plan: FR → file/test mapping, manual verification results per scenario (device/browser used), divergences logged as new `open-questions.md` entries. Commands to rerun: `php artisan test --filter=PanZoomConfigTest`, `php artisan test --filter=LangTest`, `make phpstan`, `npm run check`.

### Drift Gate Report – 2026-10-03 (pre-manual check)

**Verification evidence**
- `php artisan test --filter=PanZoomConfigTest`: 7 passed (defaults, updated values, three validation rejections, settings hidden without v8 / visible with v8). `LangTest` green. `make phpstan` clean. `php-cs-fixer` clean. `npm run check` (vue-tsc) and eslint clean on all changed files.
- `panZoom.ts` helpers: assertion script bundled with the repository's esbuild and run in Node (clamp, rubber band, zoom-around-point, tap/double-tap, hit targets LTR/RTL, minimap round trip, zoom source incl. RAW). Kept outside the repository (no frontend test runner, NFR-078-03).
- Real app, scratch instance (SQLite, storage and uploads in the agent scratchpad, PHP built-in server, `npm run build`), driven with Playwright + system Chromium. Scripts kept outside the repository.
  - Desktop, `overlay` mode (28 checks): wheel at fit navigates; ctrl+wheel and wheel-while-zoomed zoom; original loaded on zoom; `escape`/`0` reset without leaving the photo; `z`, `+`, `-`; single click rotates after the double-tap window; double-click toggles 2× without rotating; `grab`/`grabbing`; drag pans without rotating; minimap fades to 25 %, opaque on hover, click centres; arrows navigate and reset zoom.
  - Desktop, `zoom` mode (21 checks): `zoom-in`/`zoom-out` cursors, default cursor over the overlay; click on picture toggles 2× without rotating; click on overlay rotates (also while zoomed); letterbox click rotates; `none` zone restores the overlay while zoomed; rating actions and dock hidden while zoomed, back at fit; rating key inactive while zoomed, active at fit; `escape` at fit still leaves the photo.
  - Touch, 390×844 emulated phone (17 checks): pinch zooms without navigating; one-finger pan while zoomed without navigating or rotating; mobile minimap setting and 40 % mobile idle opacity; pan wakes the minimap; minimap tap; double-tap toggles; tap rotates; swipe at fit navigates; next photo opens at fit.
  - RTL + reduced motion (5 checks): minimap top-right; `none` zone bottom-right; zoom jumps without animation.

**FR → implementation**
- FR-078-01/20: `App\Enum\PhotoClickAction`, migration `2026_10_03_000001_add_pan_zoom_configs`, `InitConfig`, `SettingsController::V8_CONFIGS`, `LycheeState`, `lang/*/all_settings.php` — `PanZoomConfigTest`.
- FR-078-02..15: `PhotoBox.vue`, `usePanZoom.ts`, `panZoom.ts` — browser checks above.
- FR-078-11/21: `usePanelShortcuts.ts`, `PhotoState.ts`, `PhotoPanel.vue` (dock), `PhotoRatingOverlay.vue`.
- FR-078-16..19: `ZoomMinimap.vue`, `PhotoBox.vue`.

**Low-impact divergences, folded into the spec**
- Swipe navigation stays on `useSwipe` (touch events survive native pinch on non-zoomable photos), gated by `isSwipeBlocked()` (FR-078-07).
- `PhotoBox` is shared with the Flow lightbox and the Moderation preview: zoom is opt-in through `isZoomEnabled`, set by `PhotoPanel` only (FR-078-02).
- A container resize resets zoom instead of re-clamping, because the upgraded image is locked to its fitted size (FR-078-03, FR-078-12).
- The source-upgrade threshold includes `devicePixelRatio` (FR-078-12); ctrl+wheel steps are capped at 25 px of delta (FR-078-10).
- `PhotoState` exposes `is_zoomed` plus `zoom_controls` instead of one controller object (DO-078-05).
- Owner directives during implementation: dock hidden while zoomed (FR-078-21), settings hidden without v8 (FR-078-01), no "v8 lightbox" wording in the settings texts.

**Outstanding (T-078-17)**
- Real touch device (iOS Safari, Android Chrome); NFR-078-01 performance trace.
- Videos (S-078-13, S-078-24), face boxes (S-078-14, S-078-23) and the unchanged Flow lightbox / Moderation preview: no samples in the scratch instance.

**Lessons**
- PHP's built-in server runs with `variables_order=GPCS`, so `LARAVEL_STORAGE_PATH` does not reach the app; start it with `php -d variables_order=EGPCS -S …`, otherwise the scratch app writes sessions, cache and logs into the repository's `storage/`.

## Increment Map

1. **I1 – Configs and contract** (FR-078-01, FR-078-20, S-078-01)
   - _Steps:_ write `tests/Feature_v3/PanZoom/PanZoomConfigTest.php` (defaults, updated values), see it fail; add `App\Enum\PhotoClickAction`; migration adding the six configs to `gestures`; `InitConfig` properties; `lang/<locale>/all_settings.php` entries for all locales + `php artisan lang:json`; `php artisan typescript:transform`; `LycheeState` fields + init mapping.
   - _Commands:_ `vendor/bin/php-cs-fixer fix`, `php artisan test --filter=PanZoomConfigTest`, `php artisan test --filter=LangTest`, `make phpstan`, `npm run check`.
   - _Exit:_ tests green, types regenerated.
2. **I2 – Pure helpers** (NFR-078-02, FR-078-03, FR-078-07, FR-078-09)
   - _Steps:_ `resources/js/v8/utils/panZoom.ts`: `PanZoomState`, `maxScale`, `clampScale`, `zoomAround`, `clampPan` (+ rubber band), `classifyGesture` (tap / drag), `classifyHitTarget` (picture / overlay / none-zone / letterbox), `photoToMinimap` / `minimapToPan`, `pickZoomSource`.
   - _Commands:_ `npm run format`, `npm run check`.
   - _Exit:_ typed, side-effect-free module.
3. **I3 – Transformed layer, pan, pinch, swipe** (FR-078-02, FR-078-08, FR-078-09, FR-078-13, FR-078-14, FR-078-15, NFR-078-01, NFR-078-04)
   - _Steps:_ wrap the image + face/NSFW overlays in a transform layer; `usePanZoom` composable on Pointer Events (rAF-batched); pinch, pan, rubber band + spring-back; `useSwipe` kept for navigation, gated by `isSwipeBlocked()` (swipe only for one-finger gestures that started at fit, 50 px, RTL mapping kept); `touch-action: none` on zoomable photos only; click suppression after drags; reset on photo change.
   - _Commands:_ `npm run format`, `npm run check`.
   - _Exit:_ pinch/pan/swipe work on touch; face boxes aligned.
4. **I4 – Click modes and hit targets** (FR-078-04, FR-078-05, FR-078-06, FR-078-07)
   - _Steps:_ overlay mode: tap with 250 ms double-tap window, double-tap toggles 2×; zoom mode: hit-target classification, picture click toggles 2×, everything else rotates, `none` hit zone; cursors; non-zoomable photos always rotate.
   - _Commands:_ `npm run format`, `npm run check`.
5. **I5 – Wheel and keyboard** (FR-078-10, FR-078-11, FR-078-21)
   - _Steps:_ `wheel` on `#imageview` (ctrl+wheel always zoom; plain wheel zooms unless at fit with scroll-to-navigate on; `stopPropagation()` when consumed); zoom controller in `PhotoState`; `z`/`+`/`=`/`-` and zoomed-only `escape`/`0` in `definePanelShortcuts`, rating keys dropped while zoomed; `PhotoRatingOverlay` and `Dock` hidden while zoomed.
   - _Commands:_ `npm run format`, `npm run check`.
6. **I6 – Zoom source upgrade** (FR-078-12, FR-078-03)
   - _Steps:_ threshold check after each zoom; load original (URL exposed) or `medium2x`; `decode()` then swap `src`, drop `srcset`; never request a `null` URL; recompute `max` from the source.
   - _Commands:_ `npm run format`, `npm run check`.
7. **I7 – Minimap** (FR-078-16, FR-078-17, FR-078-18, FR-078-19, FR-078-20)
   - _Steps:_ `ZoomMinimap.vue` (top-start, 160/112 px, rectangle, `N.N×` label, `small`/`small2x` image); tap/drag → pan (events stopped); device class via `isTouchDevice()`; enable switches; fade timer, idle opacities, hover-keeps-opaque, opacity-0 → `pointer-events-none`.
   - _Commands:_ `npm run format`, `npm run check`.
8. **I8 – Docs, manual verification, quality gate**
   - _Steps:_ knowledge map entry; manual run of S-078-02..25; full quality gate; drift gate report; roadmap status.
   - _Commands:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test --filter=PanZoomConfigTest`, `php artisan test --filter=LangTest`, `make phpstan`.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-078-01 | I1 / T-078-01, T-078-02, T-078-03 | `PanZoomConfigTest` |
| S-078-02, S-078-03 | I4 / T-078-10 | manual |
| S-078-04, S-078-05, S-078-22 | I4 / T-078-11 | manual |
| S-078-06, S-078-08 | I3 / T-078-07 | manual |
| S-078-09, S-078-23, S-078-24 | I3 / T-078-08 | manual |
| S-078-07 | I3 / T-078-07, I6 / T-078-14 | manual |
| S-078-10 | I5 / T-078-12 | manual |
| S-078-11, S-078-12 | I6 / T-078-14 | manual; check network tab for S-078-12 |
| S-078-13 | I4 / T-078-11 | manual |
| S-078-14 | I3 / T-078-06 | manual |
| S-078-15 | I3 / T-078-09 | manual |
| S-078-16, S-078-25 | I5 / T-078-13 | manual; check `escape`/`0` in Album and one other view |
| S-078-17, S-078-18, S-078-19 | I7 / T-078-15 | manual |
| S-078-20, S-078-21 | I7 / T-078-16 | manual |

## Analysis Gate
Completed 2026-10-03, re-run after the review pass and Q-078-07/08 (agent self-review; owner acknowledgement pending).

1. **Specification completeness** — ✅ objectives, 21 FRs, 5 NFRs populated; review pass 2026-10-03 tidied low-impact gaps in place (RAW zoom source, wheel scope, click suppression after drags, touch-action scope, minimap grab, hit-zone size); Q-078-01..06 folded into FR-078-03/04/05/06/09/12/16/19/20; ASCII mock-up present.
2. **Open questions review** — ✅ no `Open` entries (Q-078-07/08 resolved and folded into FR-078-10/11/21). No ADR required: every decision is local to the v8 lightbox and the `gestures` config category (no cross-module boundary, security or telemetry strategy change); the access rule for the original reuses the existing `SizeVariantsResouce` downgrade.
3. **Plan alignment** — ✅ plan links spec and tasks; success criteria mirror spec Goals and NFRs.
4. **Tasks coverage** — ✅ FR mapping: FR-01 T-02/03; FR-02 T-06/11; FR-03 T-04/14; FR-04 T-10; FR-05 T-05/11; FR-06 T-07/11; FR-07 T-05/08/10; FR-08 T-07; FR-09 T-04/07; FR-10 T-12; FR-11 T-13; FR-12 T-14; FR-13 T-06; FR-14 T-09; FR-15 T-08; FR-16 T-16; FR-17 T-15; FR-18 T-05/15; FR-19 T-16; FR-20 T-02/03/16; FR-21 T-13. Backend test (T-078-01) is staged before implementation. Frontend branches are covered by the manual checklist (T-078-17) because no frontend test runner exists (adding one needs dependency approval).
5. **Working-agreement compliance** — ✅ spec-first; no dependency change; branching pushed into pure helpers (`panZoom.ts`); no fallbacks/shims; v8-only scope confirmed by the owner.
6. **Tooling readiness** — ✅ commands listed per increment.

## Exit Criteria
- All tasks `[x]`; `PanZoomConfigTest` and `LangTest` green; `make phpstan`, `php-cs-fixer`, `npm run format`, `npm run check` clean.
- `php artisan typescript:transform` rerun after `InitConfig` change; `php artisan lang:json` rerun after lang edits.
- Manual verification of S-078-02..25 recorded in tasks.md.
- Knowledge map and roadmap updated; drift gate report added here.

## Follow-ups / Backlog
- Frontend unit tests for `panZoom.ts` once a test runner is approved.
- Possible later: pan & zoom in the Flow lightbox and live photos.
