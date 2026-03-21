# Feature Plan 029 – Camera Capture

_Linked specification:_ `docs/specs/4-architecture/features/029-camera-capture/spec.md`
_Status:_ Implemented
_Last updated:_ 2026-03-18

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Key Implementation Decisions (from Resolved Questions)

**Q-029-01 (resolved):** Photos captured at the root albums view are uploaded to Unsorted (null album ID). This mirrors the existing upload pipeline's behaviour when no album is in context — no album picker dialog is shown.

**NFR-029-01 (no open question needed):** The `MediaDevices.getUserMedia` guard was added directly to `startCamera()`. A secure-context failure is surfaced as a human-readable `errorMessage` string; no JS exception escapes.

## Architecture Overview

This feature is **frontend-only**. No backend or API changes are required: the captured JPEG is fed directly into the existing Pinia-based upload queue (`list_upload_files`) and opened via `UploadPanel`, identical to a manually selected file.

```
CameraCapture.vue
    ├── navigator.mediaDevices.getUserMedia()   (browser MediaDevices API)
    ├── <video> element  →  <canvas>.toBlob()  →  File object
    └── list_upload_files.push({ file, status: "waiting" })
            └── UploadPanel  (existing component — unchanged)
```

## Implementation Increments

### I1 – Store & Toggle Wiring
- Add `is_camera_capture_visible: false` to `ModalsState.ts` state.
- Add `toggleCameraCapture()` to `galleryModals.ts` composable.
- **Refs:** FR-029-01

### I2 – "Take Photo" Menu Entry (Album View)
- Add `toggleCameraCapture` to `Callbacks` type in `contextMenuAlbumAdd.ts`.
- Add "Take Photo" menu item (`pi pi-camera`, label `gallery.menus.take_photo`) between "Upload Photo" and the first divider.
- Wire into `AlbumHeader.vue`.
- **Refs:** FR-029-01, S-029-01

### I3 – "Take Photo" Menu Entry (Root Albums View)
- Apply identical changes to `contextMenuAlbumsAdd.ts` and `AlbumsHeader.vue`.
- **Refs:** FR-029-01, S-029-03

### I4 – CameraCapture.vue Component
- New file: `resources/js/components/modals/CameraCapture.vue`.
- PrimeVue `Dialog` (`dismissable-mask`, `border-none`).
- States: loading spinner → live video → still preview → error message.
- `startCamera()` using `.then()/.catch()` (no `async/await`; NFR-029-04).
- `capture()` uses `canvas.toBlob()` with a `function` callback.
- `upload()` constructs `File` named `photo_<ISO timestamp>.jpg` and pushes to `list_upload_files`.
- `watch(is_camera_capture_visible)` drives both `startCamera()` and `stopCamera()` — no `@hide` handler needed (prevents double-stop).
- **Refs:** FR-029-02 through FR-029-07, NFR-029-01, NFR-029-02, NFR-029-04

### I5 – Mount CameraCapture in Gallery Views
- Add `<CameraCapture v-if="…can_upload…" />` to `Album.vue` and `Albums.vue`.
- **Refs:** FR-029-01, S-029-02, S-029-04

### I6 – Translations
- `lang/en/gallery.php`: add `'take_photo' => 'Take Photo'` to menus section.
- `lang/en/dialogs.php`: add `'camera'` section with `title`, `capture`, `retake`, `upload` keys.
- **Refs:** FR-029-01, FR-029-02, FR-029-03, FR-029-04, FR-029-05

### I7 – Server Permissions-Policy Header
- `config/secure-headers.php`: set `camera.self = true` so the server emits `Permissions-Policy: camera=(self)`.
- **Refs:** NFR-029-03

### I8 – Mobile Layout
- `CameraCapture.vue`: `max-h-[60vh]` on `<video>` and `<img>`; `max-h-screen overflow-y-auto` on outer container.
- `UploadPanel.vue`: outer container `w-screen max-w-md max-h-screen flex flex-col overflow-hidden`; content area `flex-1 overflow-y-auto`; button row `flex-shrink-0`; `ScrollPanel` width `w-full`.
- **Refs:** NFR-029-02

## Quality Gates

```bash
npm run format    # Prettier
npm run check     # vue-tsc
```

No PHP changes → PHP quality gates not required for this feature.

## Dependencies

- Existing `UploadPanel.vue` and `list_upload_files` store — no changes needed to the upload pipeline itself.
- PrimeVue `Dialog`, `Button` — already available.
- `Permissions-Policy` header — requires `config/secure-headers.php` change (I7).

---

*Last updated: 2026-03-18*
