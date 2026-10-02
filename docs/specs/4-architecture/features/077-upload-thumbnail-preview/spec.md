# Feature 077 – Upload Thumbnail Preview

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-10-02 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #077 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
The v8 upload modal (`v8/components/modals/UploadPanel.vue`) lists queued files as text rows (`v8/components/forms/upload/UploadingLine.vue`): file name, status, progress bar. Each row gains a miniature of the file, built in the browser from the local `File` before and during upload, on the inline-start side of the row (left in LTR, right in RTL). Frontend only, v8 only; no backend, API or config change. Addresses LycheeOrg/Lychee#2084.

Every upload entry point (file picker, modal drop, window drop, folder drop, paste, camera capture) pushes a `File` into `ModalsState.list_upload_files`, which `UploadPanel.vue` renders through `UploadingLine.vue`. The miniature is built in `UploadingLine.vue`, so all entry points get it without touching them.

## Goals
- G1: every row in the v8 upload list shows a miniature (or a type placeholder) of its file.
- G2: the miniature sits on the inline-start side of the row: left in LTR, right in RTL.
- G3: queuing hundreds of large photos does not freeze the modal or exhaust browser memory.

## Non-Goals
- v7 (`v7/components/modals/UploadPanel.vue`, `v7/components/forms/upload/UploadingLine.vue`) is unchanged.
- Server-generated thumbnails: the miniature never comes from the server, not even after the upload completes.
- Upload logic (chunking, concurrency, retries, error handling) is unchanged.
- Video poster frames: videos show an icon (FR-077-02).
- Existing physical-direction classes in `UploadingLine.vue` (`mr-1`, `pr-1`, `text-right`) unrelated to the miniature are unchanged.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-077-01 | Each `UploadingLine.vue` row shows a 40×40 px square miniature of its `File`, built in the browser. | `image/*` file → decoded with `createImageBitmap`, centre-cropped and drawn on an 80×80 px canvas (2× the box, for HiDPI), encoded with `canvas.toBlob("image/webp")` (browsers without WebP encoding return PNG), shown through a blob object URL. The decoded bitmap is closed as soon as the canvas is drawn. EXIF orientation is applied (`imageOrientation: "from-image"`). | — | Decoding throws (e.g. HEIC outside Safari) → placeholder `lucide:image` (FR-077-02). | none | Issue #2084, Q-077-01 |
| FR-077-02 | Files without a miniature show a bundled Lucide icon in the same 40×40 px box, chosen by MIME family. | `video/*` → `lucide:video`, never decoded. `image/*` that failed to decode → `lucide:image`. Any other or empty MIME type (e.g. `raw_formats` extensions, `application/octet-stream`) → `lucide:file`, never decoded. | — | — | none | Q-077-02 |
| FR-077-03 | Miniatures are built lazily: a row's generation starts when it comes within 200 px of the visible part of the list (`IntersectionObserver`). At most 2 decodes run at once across all rows (shared queue). Until its miniature is ready, a row shows its FR-077-02 icon (`lucide:image` for `image/*`). A built miniature is kept until the row unmounts. | Rows never scrolled near the visible area are never decoded. | — | — | none | Q-077-01, NFR-077-01 |
| FR-077-04 | The miniature is on the inline-start side of the row, spanning the name line and the progress bar: left in LTR, right in RTL. | Row reads `[miniature] name … status` in LTR, mirrored in RTL. | — | — | none | Owner directive, Q-077-03 |
| FR-077-05 | The upload list box in `UploadPanel.vue` grows from `h-48` (192 px) to `h-72` (288 px). | About as many rows stay visible as before the miniature was added. | — | — | none | Q-077-03 |
| FR-077-06 | The miniature does not change with upload status (waiting, uploading, done, warning, error). | — | — | — | none | — |
| FR-077-07 | On row unmount (modal close, cancel, list cleared) the row's object URL is revoked and its queued job is dropped. A job already running when the row unmounts revokes its own URL on completion. | — | — | — | none | NFR-077-01 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-077-01 | Memory stays bounded when hundreds of large photos are queued: one stored miniature is ≤ 80×80 px, and at most 2 full-size bitmaps exist at any time. | G3 | Manual check: queue ≥ 200 photos of ≥ 20 MP, modal stays responsive. | FR-077-01, FR-077-03 | Q-077-01 |
| NFR-077-02 | Works offline. | No runtime dependency on an external host. | Icons come from the bundled Lucide collection (`v8/icons.ts`). | — | Owner directive |
| NFR-077-03 | The RTL layout relies on `dir` set on `<html>` (`vueapp.blade.php`), which the teleported modal inherits: a flex row mirrors itself, and any spacing on the miniature uses logical properties or `ltr:`/`rtl:` variants. | G2 | Manual check with an RTL locale. | — | — |

## UI / Interaction Mock-ups

Current row (unchanged in v7):
```
+--------------------------------------------------------------+
| Album / IMG_0042.jpg                        37%   Uploading  |
| [==========>                                               ] |
+--------------------------------------------------------------+
```

v8 row, LTR (40×40 px miniature, `object-cover`):
```
+--------------------------------------------------------------+
| ┌────┐  Album / IMG_0042.jpg                   37%  Uploading |
| │img │  [=========>                                        ]  |
| └────┘                                                       |
+--------------------------------------------------------------+
```

v8 row, RTL (mirrored):
```
+--------------------------------------------------------------+
| Uploading  37%                   IMG_0042.jpg / Album  ┌────┐ |
|  [                                        <=========]  │img │ |
|                                                        └────┘ |
+--------------------------------------------------------------+
```

Placeholders (FR-077-02), also shown while a miniature is pending (FR-077-03):
```
| ┌────┐  clip.mp4                                      Waiting |
| │ ▶  │  [                                                  ]  |   lucide:video
| └────┘                                                       |
| ┌────┐  IMG_0043.heic                                 Waiting |
| │ ▣  │  [                                                  ]  |   lucide:image
| └────┘                                                       |
| ┌────┐  DSC_0001.nef                                  Waiting |
| │ ≡  │  [                                                  ]  |   lucide:file
| └────┘                                                       |
```

Modal list box: `h-48` → `h-72` (FR-077-05).

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-077-01 | Pick JPEG files → each row shows the photo's miniature, correctly oriented. |
| S-077-02 | Queue a HEIC file in a non-Safari browser → `lucide:image` placeholder. |
| S-077-03 | Queue a video → `lucide:video` placeholder, no decode; queue a `raw_formats` file → `lucide:file`. |
| S-077-04 | RTL locale → miniature on the right of the row. |
| S-077-05 | Queue ≥ 200 large photos → modal stays responsive (NFR-077-01). |
| S-077-06 | Close or cancel the modal → miniature resources released (FR-077-05). |
| S-077-07 | Folder drop, paste and camera capture → rows show miniatures like the file picker. |

## Test Strategy
- **Models / Actions / REST / Artisan:** none, no backend change.
- **Frontend (v8):** the project has no frontend unit-test runner. Verification is `npm run check` (vue-tsc), `npm run format`, and a manual browser check of S-077-01..07.

## Interface & Contract Catalogue

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-077-01 | Miniature shown | `image/*` file decoded (FR-077-01). |
| UI-077-02 | Placeholder shown | Pending miniature, video, other MIME type, or decoding failure (FR-077-02, FR-077-03). |

## Telemetry & Observability
None.

## Documentation Deliverables
- Roadmap entry for Feature 077.
- Knowledge map: note that `v8/components/forms/upload/UploadingLine.vue` builds client-side miniatures.

## Spec DSL

```
ui_states:
  - id: UI-077-01
    description: Miniature built from the local File
  - id: UI-077-02
    description: Type placeholder for files without a miniature
```
