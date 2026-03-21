# Feature 029 – Camera Capture – Implementation Tasks

_Linked plan:_ [plan.md](plan.md)
_Status:_ Feature Complete ✅
_Last updated:_ 2026-03-18

## Task Overview

All increments from the implementation plan. This feature is frontend-only; no backend tasks exist.

**Total estimated effort:** ~3 hours

## Task Status Legend

- ⏳ **Not Started** - Task not yet begun
- 🔄 **In Progress** - Currently being worked on
- ✅ **Complete** - All exit criteria met
- ⚠️ **Blocked** - Waiting on dependency or clarification

---

## I1 – Store & Toggle Wiring ✅

**Status:** Complete

**Deliverables:**
- [x] `resources/js/stores/ModalsState.ts`: add `is_camera_capture_visible: false` to state
- [x] `resources/js/composables/modalsTriggers/galleryModals.ts`: add `is_camera_capture_visible` to `storeToRefs` destructure and export `toggleCameraCapture()`

**Exit Criteria:**
- ✅ `is_camera_capture_visible` reactive ref available in any component that uses the store

---

## I2 – "Take Photo" Menu Entry (Album View) ✅

**Status:** Complete

**Deliverables:**
- [x] `resources/js/composables/contextMenus/contextMenuAlbumAdd.ts`: add `toggleCameraCapture` to `Callbacks` type; add "Take Photo" menu item (`pi pi-camera`, label `gallery.menus.take_photo`) between Upload and first divider
- [x] `resources/js/components/headers/AlbumHeader.vue`: destructure `toggleCameraCapture` from `useGalleryModals`; add to `useContextMenuAlbumAdd` callbacks

**Exit Criteria:**
- ✅ "Take Photo" item visible in album view `+` menu when `can_upload === true`
- ✅ Item hidden when `can_upload === false`

---

## I3 – "Take Photo" Menu Entry (Root Albums View) ✅

**Status:** Complete

**Deliverables:**
- [x] `resources/js/composables/contextMenus/contextMenuAlbumsAdd.ts`: identical changes to I2 (root view counterpart)
- [x] `resources/js/components/headers/AlbumsHeader.vue`: identical wiring to I2

**Exit Criteria:**
- ✅ "Take Photo" item visible in root albums view `+` menu when `can_upload === true`

---

## I4 – CameraCapture.vue Component ✅

**Status:** Complete

**Deliverables:**
- [x] New file: `resources/js/components/modals/CameraCapture.vue`
  - [x] PrimeVue `Dialog` with `dismissable-mask` and `border-none`
  - [x] Spinner overlay while camera initialises (`cameraReady === false`)
  - [x] Live `<video>` element with `autoplay playsinline`
  - [x] Hidden `<canvas>` for frame capture
  - [x] Still preview `<img>` after capture
  - [x] Capture button (disabled until `cameraReady`)
  - [x] Retake + Upload buttons after capture
  - [x] Error message display for missing `getUserMedia` / permission denial
  - [x] `startCamera()` using `.then()/.catch()` — no `async/await` (NFR-029-04)
  - [x] `capture()` using `canvas.toBlob()` with `function` callback
  - [x] `upload()` names file `photo_<ISO timestamp>.jpg`, pushes to `list_upload_files`, opens `UploadPanel`, closes modal
  - [x] `watch(is_camera_capture_visible)` drives `startCamera()` / `stopCamera()` — no `@hide` handler (prevents double-stop)
  - [x] Secure-context guard: check `navigator.mediaDevices?.getUserMedia` before calling; show human-readable error if absent (FR-029-07, NFR-029-01)
  - [x] Mobile layout: `max-h-[60vh]` on `<video>`/`<img>`, `max-h-screen overflow-y-auto` on outer container (NFR-029-02)

**Exit Criteria:**
- ✅ Modal opens and camera stream starts on `is_camera_capture_visible = true`
- ✅ Capture produces a still JPEG preview
- ✅ Upload pushes `File` with correct name to queue and opens `UploadPanel`
- ✅ Closing modal stops stream and resets all state
- ✅ Error shown on non-secure context (not a JS exception)
- ✅ No `async` keyword in component

---

## I5 – Mount CameraCapture in Gallery Views ✅

**Status:** Complete

**Deliverables:**
- [x] `resources/js/views/gallery-panels/Album.vue`: add `<CameraCapture v-if="albumStore.rights?.can_upload" key="camera_capture_modal" />` and corresponding import
- [x] `resources/js/views/gallery-panels/Albums.vue`: add `<CameraCapture v-if="albumsStore.rootRights?.can_upload" key="camera_capture_modal" />` and corresponding import

**Exit Criteria:**
- ✅ Modal mounted in both album and root albums view
- ✅ Gated on `can_upload` right

---

## I6 – Translations ✅

**Status:** Complete

**Deliverables:**
- [x] `lang/en/gallery.php`: add `'take_photo' => 'Take Photo'` to menus section
- [x] `lang/en/dialogs.php`: add `'camera' => ['title' => 'Take a Photo', 'capture' => 'Capture', 'retake' => 'Retake', 'upload' => 'Upload']`

**Exit Criteria:**
- ✅ No raw translation keys visible in UI

---

## I7 – Server Permissions-Policy Header ✅

**Status:** Complete

**Deliverables:**
- [x] `config/secure-headers.php`: set `'self' => true` in the `camera` policy block

**Exit Criteria:**
- ✅ Response headers contain `Permissions-Policy: camera=(self)` on gallery pages
- ✅ No "Permissions policy violation: camera is not allowed" browser console error

---

## I8 – Mobile Layout ✅

**Status:** Complete

**Deliverables:**
- [x] `CameraCapture.vue`: `max-h-[60vh]` on `<video>` and `<img>`; `max-h-screen overflow-y-auto` on outer container
- [x] `UploadPanel.vue`: outer container `w-screen max-w-md max-h-screen flex flex-col overflow-hidden`; content area `flex-1 overflow-y-auto`; button row `flex-shrink-0`; `ScrollPanel` width changed from `w-96` to `w-full`

**Exit Criteria:**
- ✅ Capture button visible without scrolling on 375×667 portrait and landscape (NFR-029-02)
- ✅ UploadPanel "Close" button visible on small portrait screens

---

## Quality Gate ✅

**Deliverables:**
- [x] `npm run format` passes (Prettier)
- [x] `npm run check` passes (vue-tsc)

**Note:** These gates must be run inside the Docker container or a local Node environment where `node_modules` is installed. They are not runnable directly on the host if `node_modules` is absent.

```bash
npm run format
npm run check
```

---

## Scenario Coverage

| Scenario | Covered by |
|----------|-----------|
| S-029-01 Album view → Take Photo → camera starts | I2, I4, I5 |
| S-029-02 Capture in album → uploaded to that album | I4, I5 |
| S-029-03 Root view → Take Photo → camera starts | I3, I4, I5 |
| S-029-04 Capture at root → uploaded to Unsorted | I4, I5 (null album ID) |
| S-029-05 Retake → stream restarts | I4 |
| S-029-06 Dismiss modal → stream stopped | I4 (watch handler) |
| S-029-07 HTTP non-localhost → error shown | I4 (secure-context guard) |
| S-029-08 Camera permission denied → error shown | I4 (.catch handler) |

---

*Last updated: 2026-03-18*
