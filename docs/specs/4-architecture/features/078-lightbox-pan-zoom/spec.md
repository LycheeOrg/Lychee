# Feature 078 – Lightbox Pan & Zoom

| Field | Value |
|-------|-------|
| Status | Implemented (manual check pending) |
| Last updated | 2026-10-03 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #078 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
The v8 lightbox (`v8/components/gallery/photoModule/PhotoPanel.vue` + `PhotoBox.vue`) gains pan & zoom on still images. Today a click on `#imageview` rotates the overlay, a horizontal swipe (`useSwipe`) navigates to the next/previous photo, a vertical swipe goes back, and the mouse wheel navigates when `is_scroll_to_navigate_photos_enabled` is on. Mobile users can only zoom through native page pinch (`meta.blade.php`, `maximum-scale=4.0`), which scales the whole page and upscales the displayed medium variant.

Zoom is a mode of the lightbox: at fit scale every existing gesture keeps its meaning; while zoomed, a single-pointer drag pans the image instead of navigating. A new admin config `photo_click_action` decides whether a click rotates the overlay (default) or, when it lands on the picture, toggles a 2× zoom; pinch and wheel zoom further. Zoom draws from the original only when its URL is exposed to the viewer, otherwise from the largest accessible variant. While zoomed, a minimap in the top-start corner shows the zoom level and the visible area, can be tapped or dragged to move around, and fades to a configurable opacity when idle.

Layers: one config migration (6 configs), `InitConfig` + `LycheeState`, v8 frontend. v7 is unchanged.

## Goals
- G1: pinch, double-tap/click, wheel and keyboard zoom on still images in the v8 lightbox, on touch and desktop.
- G2: pan by dragging while zoomed, with navigation swipes suppressed.
- G3: an admin setting that makes a click on the picture toggle zoom, while a click on the overlay still rotates it.
- G4: zoom uses the original when the viewer may access it, and never requests it otherwise.
- G5: face and NSFW boxes stay aligned with the image at every zoom level.
- G6: while zoomed, a minimap shows the zoom level and the visible part of the photo, can be used to move around, and is configurable per device class (touch / non-touch).
- G7: while zoomed, the rating actions, the rating keys and the editor dock are out of the way.

## Non-Goals
- v7 lightbox.
- Zoom on videos, PDFs, the RAW placeholder, live photos, and during the slideshow.
- Zoom in album/thumbnail grids, the Flow lightbox (`flowModule/LigtBox.vue`) or the map.
- Tiled / deep-zoom image pyramids: zoom works on whole size-variant files.
- Navigating to another photo by dragging past the edge of a zoomed image (FR-078-09).
- New npm dependencies (NFR-078-03).
- Changes to the viewport meta tag outside the lightbox.
- Per-user preferences: all settings are admin configs.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-078-01 | New config `photo_click_action` (category `gestures`, `type_range` `overlay\|zoom`, default `overlay`, level 0, not expert), backed by enum `App\Enum\PhotoClickAction`, exposed as `InitConfig::$photo_click_action` and `LycheeState.photo_click_action`. Like every pan & zoom config it is listed in `SettingsController::V8_CONFIGS`, so the settings page hides it when v8 is disabled (`features.v8`). | Admin switches the value in settings; next lightbox load uses it. | Values outside `overlay\|zoom` are rejected by the existing config validation. | — | None. | Owner directive 2026-10-03. |
| FR-078-02 | A photo is **zoomable** when it is shown in the main lightbox (`PhotoPanel` passes `isZoomEnabled` to `PhotoBox`; the Flow lightbox and the Moderation preview reuse `PhotoBox` without it and keep their own click handlers), its view mode is `Medium` or `Original`, and the slideshow is inactive. | Zoom gestures act on zoomable photos. | — | Non-zoomable photo: zoom gestures are ignored and a click anywhere on `#imageview` rotates the overlay whatever `photo_click_action` says. | None. | — |
| FR-078-03 | Zoom scale `s` is relative to the fit size (`s = 1` is the current fit-to-screen rendering), clamped to `[1, max]` with `max = max(4, 4 × native)`, where `native = source width ÷ fit width` and the **zoom source** is the largest accessible, browser-displayable variant: the original when its URL is exposed and the photo is not RAW (`precomputed.is_raw`, whose original is the RAW/PDF file itself), otherwise `medium2x`, otherwise `medium`. Zooming past the source's native resolution is allowed (a 4K image on a 4K screen still needs magnification to inspect details). A change of the container size (window resize, details drawer, device rotation) resets the zoom to fit. | A 50 MP original on a phone zooms deeper than a `medium2x`-only photo. | — | — | None. | Owner directive 2026-10-03 (Q-078-02, Q-078-06 Option B). |
| FR-078-04 | `photo_click_action = overlay`: a single click/tap on `#imageview` rotates the overlay, delayed by the double-tap window (250 ms); a double-click/double-tap on a zoomable picture zooms to 2× centred on the pointer when at fit, or resets to fit when zoomed. On a non-zoomable photo the click rotates immediately (no double-tap window). | Overlay rotation and zoom toggle both reachable by pointer. | A second tap counts as a double-tap only within 250 ms and 30 px of the first. | — | None. | — |
| FR-078-05 | `photo_click_action = zoom`, where the click lands decides what it does: on the **overlay** (the click point lies inside the bounding box of `#image_overlay`, which stays `pointer-events-none`; the click is handled on `#imageview`) it rotates the overlay; on the **picture** (inside the rendered image's bounds) it zooms to 2× centred on the click point when at fit, and resets to fit when zoomed. Every other click on `#imageview` rotates the overlay: the area around a letterboxed picture, and, while the overlay type is `none`, an invisible hit zone at the overlay's position (from the bottom edge of `#imageview` up 96 px, from its start edge across 50 % of its width) that takes precedence over the picture. When `is_exif_disabled` is on, no overlay is rendered: there are no overlay targets and clicks outside the picture do nothing. No double-tap delay. | One click in, one click out; overlay always rotatable on touch, including back from `none`. | — | — | None. | Owner directive 2026-10-03 (Q-078-01, Q-078-05 Option A). |
| FR-078-06 | `photo_click_action = zoom`, mouse pointer over a zoomable picture: magnifier with **+** (`cursor: zoom-in`) at fit, magnifier with **−** (`cursor: zoom-out`) while zoomed; default cursor over the overlay, the letterbox area and the `none` hit zone. During an active pan the cursor is `grabbing` in both modes. | Click outcome visible before clicking. | — | — | None. | Owner directive 2026-10-03. |
| FR-078-07 | A pointer-down/up pair that moved more than 5 px, or any gesture that involved a second pointer, is a drag, never a click or tap: the `click` that follows is suppressed for every element inside `#imageview`, including face boxes (which otherwise open the face assignment). Native image dragging and text selection are disabled on the zoom layer. At `s = 1` a touch drag is a swipe once it moves at least 50 px: navigation stays on `useSwipe` (touch events, unaffected by native pinch on non-zoomable photos), which ignores gestures that started zoomed or used two fingers; the image does not follow the finger. | Panning never toggles zoom, rotates the overlay or opens a face. | — | — | None. | — |
| FR-078-08 | Two-finger pinch and trackpad pinch (`wheel` with `ctrlKey`) zoom continuously around the gesture midpoint / cursor, in both click modes. | Smooth zoom anchored under the fingers. | Clamped to `[1, max]`. | — | None. | — |
| FR-078-09 | While `s > 1`, a single-pointer drag (touch or mouse) pans the image. On an axis where the image is larger than the viewport it cannot leave a gap (rubber-band resistance past the edge, springs back on release); on an axis where it is smaller it stays centred. Swipe navigation (next/previous/go-back) is suppressed; dragging past an edge never navigates. Keyboard arrows and the `NextPrevious` buttons still navigate. In `overlay` mode the mouse cursor is `grab`/`grabbing` over the zoomed picture. | Pan stops at the image edges; no accidental photo change. | — | — | None. | Q-078-03 (Option A). |
| FR-078-10 | Plain wheel over `#imageview`: at `s = 1` with `is_scroll_to_navigate_photos_enabled` on, it navigates (unchanged); otherwise it zooms around the cursor (about 1.22× per 100 px of wheel delta), and it never navigates while `s > 1`. A ctrl+wheel step is capped at 25 px of delta (about 1.28× per mouse notch); trackpad pinch deltas stay below the cap. A trackpad two-finger scroll is a plain wheel and zooms too; trackpad users pan by dragging or with the minimap. Wheel events outside `#imageview` (header, details drawer, dock) keep today's behaviour. | — | — | — | None. | Existing gesture config, Q-078-08 (Option A). |
| FR-078-11 | Keyboard zoom is declared in `definePanelShortcuts` (`v8/composables/usePanelShortcuts.ts`), which every gallery view uses, so it applies in every view. While a zoomable photo is open: `z` zooms to 2× around the viewport centre at fit and resets to fit when zoomed; `+`/`=` zoom in ×1.5 and `-` zoom out ÷1.5 around the viewport centre. While zoomed (`s > 1`): `escape` and `0` reset to fit and replace the view's own binding for that key press; `1`–`5` (rating) are inactive (FR-078-21). At fit, `escape` and `0`–`5` keep the view's meaning. While it shows a zoomable photo, `PhotoBox` publishes `PhotoState.is_zoomed` and `PhotoState.zoom_controls` (`toggle`, `zoomIn`, `zoomOut`, `reset`) and clears them on unmount (only its own registration, since the next photo's box mounts before the previous one leaves); the zoom state itself stays in `PhotoBox` (FR-078-14). Other bindings (`o`, arrows, …) are unchanged; navigating resets zoom. | One keyboard path: a key press never both unzooms and leaves the photo. | Keys ignored when `shouldIgnoreKeystroke()` is true or a modal is open (existing `definePanelShortcuts` behaviour). | — | None. | Owner directive 2026-10-03 (Q-078-07). |
| FR-078-12 | When `s × fit width × devicePixelRatio` exceeds the `naturalWidth` of the image currently displayed (whichever `srcset` candidate the browser picked) and the zoom source of FR-078-03 is wider, the zoom source is loaded in the background and swapped in once decoded; the image keeps its fitted size (explicit width/height), so nothing moves. The original is never requested when its URL is `null` or the photo is RAW. While loading, the current image stays displayed. | Sharpest image the viewer may see. | — | Load fails: the current variant stays, zoom continues. | None. | Owner directive 2026-10-03. |
| FR-078-13 | Face and NSFW detection overlays sit in the same transformed layer as the image, so their boxes follow pan and zoom. The EXIF `Overlay`, `Dock` and `PhotoRatingOverlay` (both hidden while zoomed, FR-078-21), `NextPrevious`, minimap and header stay in screen space. | Boxes stay on the faces at any scale. | — | — | None. | — |
| FR-078-14 | Zoom state belongs to the `PhotoBox` instance (keyed by `photo.id`): changing photo, opening the slideshow or closing the lightbox resets to fit. | — | — | — | None. | — |
| FR-078-15 | On zoomable photos, native page pinch is disabled on `#imageview` (`touch-action: none`) so the lightbox receives pinch gestures. On non-zoomable photos `#imageview` keeps native page pinch and the existing `isPageZoomed()` guard that suppresses swipes while the page is pinch-zoomed. The viewport meta tag is unchanged. | — | — | — | None. | — |
| FR-078-16 | **Device class:** touch when `isTouchDevice()` (`utils/keybindings-utils.ts`) is true, otherwise desktop — the same split `Dock.vue` uses. Every per-device minimap setting reads the value for the current class. | — | — | — | None. | Owner directive 2026-10-03 (Q-078-04). |
| FR-078-17 | While `s > 1` and the minimap is enabled for the device class (FR-078-20), a minimap is shown in the top-start corner of `#imageview` (`top-7`, `ltr:left-7 rtl:right-7`: top-left in LTR, top-right in RTL): the whole photo scaled so its longest side is 160 px (112 px below the `sm` breakpoint), a rectangle marking the part of the photo visible in the viewport (clipped to the minimap during rubber-band overscroll), and the zoom level as a label under it (`2.0×`, one decimal). Hidden at `s = 1` and on non-zoomable photos. Its image is the `small` variant (`srcset` with `small2x`) when it exists, otherwise the variant currently displayed; it never triggers a load of the original. | Minimap appears on zoom, disappears on reset. | — | — | None. | Owner directive 2026-10-03. |
| FR-078-18 | Pointer down inside the rectangle grabs it: dragging moves it with the pointer, without a jump. Pointer down elsewhere on the minimap centres the viewport on the matching point of the photo, and dragging continues from there; the result is clamped like FR-078-09. This works whether the minimap is opaque or faded. Minimap pointer events never reach `#imageview` (no zoom toggle, overlay rotation or swipe). | Jump and drag around a zoomed photo from the minimap. | — | — | None. | Owner directive 2026-10-03. |
| FR-078-19 | **Minimap fade.** The minimap is fully opaque when it appears and whenever the user interacts; after `photo_minimap_fade_delay` seconds without interaction it fades (300 ms transition) to `photo_minimap_idle_opacity` % on desktop or `photo_minimap_idle_opacity_mobile` % on touch. Interactions that make it opaque and restart the delay: any pan or zoom of the photo (all devices), pointer down/drag on the minimap (all devices). Hovering the minimap with a mouse (`pointerType` `mouse`, whatever the device class) keeps it opaque for as long as the pointer is over it; the delay restarts when the pointer leaves. With an idle opacity of 0 the faded minimap ignores pointer events. | Unobtrusive when idle; reappears as soon as the user moves. | — | — | None. | Owner directive 2026-10-03 (Q-078-04). |
| FR-078-20 | **Minimap configs** (category `gestures`, level 0, not expert), exposed through `InitConfig` and `LycheeState`: `is_photo_minimap_enabled` (`0\|1`, default `1`, desktop), `is_photo_minimap_enabled_mobile` (`0\|1`, default `1`, touch), `photo_minimap_idle_opacity` (`int:0:100`, default `25`, desktop), `photo_minimap_idle_opacity_mobile` (`int:0:100`, default `25`, touch), `photo_minimap_fade_delay` (`positive`, seconds, default `2`, both classes). Disabling the minimap for a class hides it entirely; zoom and pan are unaffected. | Admin tunes or disables the minimap per device class. | Out-of-range values rejected by the existing config validation. | — | None. | Owner directive 2026-10-03 (Q-078-04). |
| FR-078-21 | While zoomed (`s > 1`), the rating actions at the bottom of the lightbox (`PhotoRatingOverlay`) and the editor `Dock` at the top are hidden, and the rating keybindings (`0`–`5`) are inactive (`0` resets zoom, FR-078-11). They return as soon as the photo is back at fit. The rating widget in the details drawer is unchanged. | Nothing to rate or edit by accident while inspecting details; the minimap never sits under the full-width dock on narrow screens. | — | — | None. | Owner directive 2026-10-03. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-078-01 | Pan/zoom updates only `transform` on one layer, batched per animation frame; no layout reads inside pointer-move handlers. The minimap rectangle updates in the same frame. | Smoothness on phones. | Chrome DevTools performance trace on a mid-range Android: no layout/recalc during pinch, ≥ 55 fps. | — | — |
| NFR-078-02 | Gesture math (clamp, zoom-around-point, rubber band, tap/drag classification, hit-target classification, photo ↔ minimap mapping) lives in pure functions in `resources/js/v8/utils/panZoom.ts`; the composable only wires events to them. | Straight-line increments, reviewability. | Code review. | — | AGENTS.md. |
| NFR-078-03 | No new npm dependency; Pointer Events only. | Dependency policy, offline requirement. | `package.json` unchanged. | — | AGENTS.md. |
| NFR-078-04 | With `prefers-reduced-motion: reduce`, click/double-tap zoom and rubber-band spring-back jump instead of animating. | Accessibility. | Manual check with the OS setting. | — | — |
| NFR-078-05 | Swipe direction mapping in RTL is unchanged; the minimap mirrors to the top-right corner. | RTL parity. | Manual check with an RTL locale. | — | — |

## UI / Interaction Mock-ups

```
 photo_click_action = zoom                     photo_click_action = zoom
 State: fit (s = 1)                            State: zoomed (s = 2), minimap opaque
+--------------------------------------------+ +--------------------------------------------+
| ← Album title                    ☆ ⋯  ⓘ    | | ← Album title                    ☆ ⋯  ⓘ    |
+--------------------------------------------+ +--------------------------------------------+
|      +--------------------------------+    | | +--------+################################ |
|  ‹   |                                |  › | | | ┌──┐   |######  image at 2×  ########## |
|      |          (+)  cursor           |    | | | └──┘   |######## (−) cursor ########### |
|      |        click → 2× here         |    | | +--------+################################ |
|      |                                |    | |   2.0×  ##  drag = pan, no swipe nav  ### |
|      |                                |    | | ‹  #######  click = reset to fit  ##### › |
|      +--------------------------------+    | |    ###################################### |
| [2026-07-14 · f/2.8 · 1/250 s] ← click     | | [2026-07-14 · f/2.8 · 1/250 s] ← click     |
|        rotates overlay                     | |        rotates overlay                     |
+--------------------------------------------+ +--------------------------------------------+

 Minimap fade (FR-078-19)
   zoom / pan / tap minimap ──▶ opaque 100 % ──(fade_delay s idle)──▶ idle_opacity % (25 %)
   desktop: hover minimap ──▶ opaque while hovered
   touch:   pan or pinch the photo ──▶ opaque again for fade_delay s

 photo_click_action = overlay (default)
   tap anywhere        → rotate overlay (after 250 ms)
   double-tap picture  → 2× at tap point ⇄ reset
   pinch / ctrl+wheel  → continuous zoom, both modes (plain wheel: FR-078-10)
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-078-01 | `InitConfig` exposes `photo_click_action` and the five minimap configs with their stored values; defaults `overlay`, `1`, `1`, `25`, `25`, `2`. The settings page lists the six configs only when v8 is enabled. |
| S-078-02 | Overlay mode, single tap → overlay rotates after 250 ms; zoom unchanged. |
| S-078-03 | Overlay mode, double-tap on the picture at fit → 2× around the tap point; double-tap again → fit; overlay not rotated. |
| S-078-04 | Zoom mode, hover the picture at fit → `zoom-in` cursor; click → 2× at the click point; cursor `zoom-out`; click → fit. |
| S-078-05 | Zoom mode, click on the overlay text (at fit or zoomed) → overlay rotates; zoom unchanged. |
| S-078-06 | Zoom mode, drag while zoomed then release → image panned, zoom kept (FR-078-07). |
| S-078-07 | Pinch on touch / ctrl+wheel on trackpad → continuous zoom between 1 and `max = max(4, 4 × native)`. |
| S-078-08 | Zoomed, one-finger horizontal drag → pan; past the edge → rubber band then spring back; no navigation. |
| S-078-09 | Fit, horizontal swipe → next/previous as today; vertical swipe → back when enabled. |
| S-078-10 | Wheel at fit with scroll-to-navigate on → navigates; with it off → zooms; wheel while zoomed → zooms. |
| S-078-11 | Zoom past the medium's pixel width with an accessible original → original loads and replaces the medium without the image jumping. |
| S-078-12 | Original not accessible (`url === null`) or RAW photo → zoom source is `medium2x` (else `medium`), loaded only when wider than what is displayed; `max` computed from it; no request for the original. |
| S-078-13 | Video / PDF / RAW placeholder / live photo / slideshow → zoom gestures ignored; click rotates the overlay in both modes. |
| S-078-14 | Faces and NSFW boxes stay aligned with the image while zoomed and panned. |
| S-078-15 | Navigate to another photo while zoomed → new photo opens at fit. |
| S-078-16 | At fit: `z` → 2× around the centre; `+`/`=` and `-` step; `0`–`5` set the rating; `escape` does what the view does today. Zoomed: `z`, `escape` or `0` → fit (`escape` does not leave the photo); `1`–`5` do nothing; arrows still navigate. Works in every gallery view. |
| S-078-17 | Zoom in → minimap appears top-left (top-right in RTL), opaque, with the visible-area rectangle and `2.0×`; reset → minimap disappears. |
| S-078-18 | Pan or pinch on the photo → minimap rectangle and zoom label follow. |
| S-078-19 | Tap on the minimap (opaque or faded) → viewport centred there (clamped); drag on it → continuous pan; the picture's click handler, overlay rotation and swipe are not triggered. |
| S-078-20 | Idle for `photo_minimap_fade_delay` s → minimap fades to the device class's idle opacity; desktop hover → opaque until the pointer leaves; touch pan → opaque for another delay. |
| S-078-21 | `is_photo_minimap_enabled` = 0 on desktop (or `_mobile` = 0 on touch) → no minimap while zoomed; zoom and pan unchanged. |
| S-078-22 | Zoom mode, tap on the black area beside a letterboxed picture → overlay rotates. Overlay `none` → tap in the bottom-start hit zone → overlay rotates to `desc`, even when the zoomed picture covers that corner. |
| S-078-23 | Zoomed, drag that starts on a face box → pan; face assignment does not open. Click without drag on a face box → face assignment opens; zoom and overlay unchanged. |
| S-078-24 | Video at fit: native page pinch still works and suppresses swipes while the page is zoomed, as today. |
| S-078-25 | Zoom in → the rating actions at the bottom and the editor dock disappear; reset → they reappear. |

## Test Strategy
- **Models / Actions:** none.
- **REST API (v2/v3):** `tests/Feature_v3/PanZoom/PanZoomConfigTest` asserts `InitConfig` returns the six configs with their defaults and after changing them, that invalid values are rejected, and that `GET /api/v2/Settings` lists them only with `features.v8` on — S-078-01.
- **Artisan commands:** none.
- **Frontend (v8):** the repository has no frontend unit-test runner and adding one needs approval (NFR-078-03), so the pure helpers in `panZoom.ts` are kept small and typed; `npm run check` + eslint must pass. S-078-02..25 are verified manually in a browser on desktop (mouse + trackpad) and on a touch device.
- **Docs/Contracts:** regenerated TypeScript types (`php artisan typescript:transform`) for `InitConfig` and `PhotoClickAction`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-078-01 | `App\Enum\PhotoClickAction`: `OVERLAY = 'overlay'`, `ZOOM = 'zoom'`. | enum, InitConfig |
| DO-078-02 | Config rows in category `gestures`: `photo_click_action`, `is_photo_minimap_enabled`, `is_photo_minimap_enabled_mobile`, `photo_minimap_idle_opacity`, `photo_minimap_idle_opacity_mobile`, `photo_minimap_fade_delay` (FR-078-01, FR-078-20). | migration, settings UI, InitConfig |
| DO-078-03 | Frontend `PanZoomState { scale, x, y }` and helpers in `resources/js/v8/utils/panZoom.ts`; `usePanZoom` composable (`resources/js/v8/composables/usePanZoom.ts`) wiring Pointer Events, wheel and click to them. | v8 frontend |
| DO-078-04 | `v8/components/gallery/photoModule/ZoomMinimap.vue`: props `src`, `srcset`, `aspect`, `visible` (normalised rectangle), `scale`, `activity`, `idleOpacity`, `fadeDelay`; emits `centre` with the normalised photo point to centre on. | v8 frontend |
| DO-078-05 | `PhotoState.is_zoomed` (boolean) and `PhotoState.zoom_controls` (`ZoomControls`: `toggle`, `zoomIn`, `zoomOut`, `reset`, or `undefined`), written by `PhotoBox` only; read by `definePanelShortcuts`, `PhotoPanel` (dock) and `PhotoRatingOverlay`. | v8 frontend, stores |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-078-01 | REST GET /api/v2/Gallery::Init | Gains the six configs of DO-078-02. | `InitConfig` |

### CLI Commands / Flags
None.

### Telemetry Events
None.

### Fixtures & Sample Data
None.

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-078-01 | Fit | Default; existing gestures apply; zoom mode shows `zoom-in` cursor over zoomable pictures. |
| UI-078-02 | Zoomed | `s > 1`; drag pans; swipe nav suppressed; rating actions and dock hidden, rating keys inactive; zoom mode shows `zoom-out` cursor. |
| UI-078-03 | Upgrading source | Zoomed past the displayed variant's resolution; original or `medium2x` loading in the background. |
| UI-078-04 | Not zoomable | Video/PDF/RAW/live photo/slideshow; click rotates the overlay. |
| UI-078-05 | Minimap opaque | Shown on zoom and on any interaction (FR-078-19). |
| UI-078-06 | Minimap faded | Idle for the fade delay; idle opacity of the device class. |

## Telemetry & Observability
None.

## Documentation Deliverables
- Roadmap entry #078.
- Knowledge map: `usePanZoom` composable, `panZoom.ts` helpers and `ZoomMinimap.vue` under the v8 lightbox.
- `lang/<locale>/all_settings.php`: labels and details for the six configs, then `php artisan lang:json`.

## Fixtures & Sample Data
None.

## Spec DSL

```
domain_objects:
  - id: DO-078-01
    name: PhotoClickAction
    values: [overlay, zoom]
  - id: DO-078-02
    name: configs
    category: gestures
    entries:
      - { key: photo_click_action, type_range: "overlay|zoom", default: overlay }
      - { key: is_photo_minimap_enabled, type_range: "0|1", default: 1 }
      - { key: is_photo_minimap_enabled_mobile, type_range: "0|1", default: 1 }
      - { key: photo_minimap_idle_opacity, type_range: "int:0:100", default: 25 }
      - { key: photo_minimap_idle_opacity_mobile, type_range: "int:0:100", default: 25 }
      - { key: photo_minimap_fade_delay, type_range: positive, default: 2 }
  - id: DO-078-03
    name: PanZoomState
    fields:
      - name: scale
        type: number
        constraints: "1..max"
      - name: x
        type: number
      - name: y
        type: number
  - id: DO-078-04
    name: ZoomMinimap
routes:
  - id: API-078-01
    method: GET
    path: /api/v2/Gallery::Init
    adds: [photo_click_action, is_photo_minimap_enabled, is_photo_minimap_enabled_mobile, photo_minimap_idle_opacity, photo_minimap_idle_opacity_mobile, photo_minimap_fade_delay]
ui_states:
  - id: UI-078-01
    description: Fit
  - id: UI-078-02
    description: Zoomed
  - id: UI-078-03
    description: Upgrading source
  - id: UI-078-04
    description: Not zoomable
  - id: UI-078-05
    description: Minimap opaque
  - id: UI-078-06
    description: Minimap faded
```
