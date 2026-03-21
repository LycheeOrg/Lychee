# Feature 029 – Camera Capture

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-03-18 |
| Owners | mitpjones |
| Linked plan | `docs/specs/4-architecture/features/029-camera-capture/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/029-camera-capture/tasks.md` |
| Roadmap entry | #029 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

Users want to take a photo using their device camera directly from within Lychee and have it uploaded to the current album (or to Unsorted when at the root gallery view). This removes the need to use a separate camera app and then upload the file manually. The feature affects the gallery UI (album view and root albums view) and the existing upload pipeline.

## Goals

- Add a "Take Photo" option to the "+" add menu in both the album view and the root albums view.
- Open a modal that streams the device camera, allows capture, shows a preview, and on confirmation feeds the JPEG into the existing `UploadPanel` upload pipeline.
- Photos captured inside an album upload to that album; photos captured at the root view upload to Unsorted (null album ID).
- Show a clear error when the browser environment does not support camera access (non-secure context).

## Non-Goals

- Video recording.
- Selecting from existing device photos (that is covered by the existing file upload).
- Camera access on browsers that do not support `MediaDevices.getUserMedia`.
- Album picker flow after capture at root level (deferred; see Q-029-01 resolution).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|---------|
| FR-029-01 | "Take Photo" menu item appears in the "+" add menu when the user has `can_upload` rights, in both album and root views. | Menu item is visible and labelled correctly. | Only shown when `can_upload === true`. | Hidden when rights absent. | — | User request |
| FR-029-02 | Tapping "Take Photo" opens the camera capture modal and starts the rear-facing camera stream. | Live video preview visible; capture button enabled once stream is ready. | `facingMode: "environment"` requested; spinner shown while stream initialises. | Error message shown if `getUserMedia` fails or is denied. | — | User request |
| FR-029-03 | Tapping "Capture" freezes the current frame as a JPEG preview. | Still image shown; "Retake" and "Upload" buttons appear. | Canvas draws at native video resolution. | If canvas/blob creation fails silently, nothing is pushed. | — | User request |
| FR-029-04 | Tapping "Upload" adds the JPEG to the upload queue and opens `UploadPanel`. | File named `photo_<ISO timestamp>.jpg` pushed to `list_upload_files`; `UploadPanel` opens. | Album ID taken from current route param; null at root (Unsorted). | — | — | User request; Q-029-01 resolved |
| FR-029-05 | Tapping "Retake" discards the preview, stops the stream, and restarts the camera. | Live preview resumes. | — | Camera re-request may be denied by browser. | — | User request |
| FR-029-06 | Closing the modal stops the camera stream and resets all state. | Stream tracks stopped; video/blob/error state cleared. | Watch on `is_camera_capture_visible` drives cleanup. | — | — | User request |
| FR-029-07 | When `MediaDevices.getUserMedia` is unavailable (non-secure context), a human-readable error is shown immediately. | Error message instructs user to use HTTPS or localhost. | Checked before `getUserMedia` call via `navigator.mediaDevices?.getUserMedia`. | — | — | NFR-029-01 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-029-01 | Camera access requires a secure context (HTTPS or `localhost`). The feature must degrade gracefully — showing an error — rather than throwing a JS exception. | Browser security model (`MediaDevices` only available in secure contexts). | Manual test on HTTP non-localhost origin shows error message, not JS exception. | `navigator.mediaDevices?.getUserMedia` guard in `CameraCapture.vue`. | MDN Secure Contexts |
| NFR-029-02 | The capture modal must be usable on small portrait and landscape mobile screens. | Primary use case is phone camera. | Buttons remain visible and reachable without scrolling on a 375×667 viewport in both orientations. | `max-h-[60vh]` on video/image; `max-h-screen overflow-y-auto` on container. | User report |
| NFR-029-03 | The server `Permissions-Policy` header must allow camera access from the same origin. | FrankenPHP/Laravel secure-headers middleware blocks camera by default. | `camera=(self)` present in response headers on the gallery page. | `config/secure-headers.php` `camera.self = true`. | Browser Permissions Policy spec |
| NFR-029-04 | Frontend code must follow project conventions: `.then()` over `async/await`, `function` declarations over arrow functions. | Coding conventions in `docs/specs/3-reference/coding-conventions.md`. | `npm run check` passes; no `async` keyword in `CameraCapture.vue`. | — | Coding conventions doc |

## UI / Interaction Mock-ups

### "+" Add Menu (album view)

```
┌─────────────────────────┐
│  ↑ Upload Photo          │
│  📷 Take Photo           │  ← new
│ ─────────────────────── │
│  🔗 Import from Link     │
│  📦 Import from Dropbox  │
│  🖥  Import from Server  │
│ ─────────────────────── │
│  📁 New Album            │
│  🧭 Upload track         │
└─────────────────────────┘
```

### Camera Capture Modal — live view

```
┌───────────────────────────┐
│      Take a Photo         │
│ ┌───────────────────────┐ │
│ │                       │ │
│ │   [ live camera feed ]│ │
│ │                       │ │
│ └───────────────────────┘ │
│       [ Capture 📷 ]      │
└───────────────────────────┘
```

### Camera Capture Modal — after capture

```
┌───────────────────────────┐
│      Take a Photo         │
│ ┌───────────────────────┐ │
│ │                       │ │
│ │   [ still preview ]   │ │
│ │                       │ │
│ └───────────────────────┘ │
│  [ Retake 🔄 ] [ Upload ↑]│
└───────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|-------------------------------|
| S-029-01 | User opens album, taps "+", selects "Take Photo" — modal opens, camera starts. |
| S-029-02 | User captures photo in album — JPEG uploaded to that album. |
| S-029-03 | User opens root albums view, taps "+", selects "Take Photo" — modal opens, camera starts. |
| S-029-04 | User captures photo at root — JPEG uploaded to Unsorted. |
| S-029-05 | User taps "Retake" — stream restarts, preview discarded. |
| S-029-06 | User dismisses modal — stream stopped, state reset. |
| S-029-07 | User on HTTP non-localhost — error message shown, no JS exception. |
| S-029-08 | User denies camera permission — browser error message shown in modal. |

## Test Strategy

- **UI (JS):** Component tests for `CameraCapture.vue` covering: modal visibility toggle, error display on missing `getUserMedia`, post-capture state (blob/dataUrl set, stream stopped), upload action pushes correct File to store. `npm run check`.
- **Core / REST / CLI:** No backend changes; existing upload pipeline tests cover the upload path.

## Interface & Contract Catalogue

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-029-01 | Idle (modal closed) | `is_camera_capture_visible === false`; stream null. |
| UI-029-02 | Loading camera | Modal open; `cameraReady === false`; spinner shown. |
| UI-029-03 | Live preview | `cameraReady === true`; Capture button enabled. |
| UI-029-04 | Preview captured | `capturedBlob` set; Retake + Upload buttons shown. |
| UI-029-05 | Error | `errorMessage` non-empty; no video shown. |

## Telemetry & Observability

No telemetry events introduced. Camera permission errors are surfaced in the UI only.

## Documentation Deliverables

- `docs/specs/4-architecture/features/029-camera-capture/spec.md` (this file)
- `docs/specs/4-architecture/features/029-camera-capture/plan.md`
- `docs/specs/4-architecture/features/029-camera-capture/tasks.md`
- `docs/specs/5-decisions/ADR-0001.md` — Docker Compose security hardening removal

## Spec DSL

```
ui_states:
  - id: UI-029-01
    description: Modal closed, stream null
  - id: UI-029-02
    description: Loading camera, spinner shown
  - id: UI-029-03
    description: Live camera preview, capture enabled
  - id: UI-029-04
    description: Still preview shown, retake/upload available
  - id: UI-029-05
    description: Error state, message shown
```

---

*Last updated: 2026-03-18*
