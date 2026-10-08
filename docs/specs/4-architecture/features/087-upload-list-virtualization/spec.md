# Feature 087 – Upload List Virtualization

| Field | Value |
|-------|-------|
| Status | Planning |
| Last updated | 2026-10-09 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #087 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
The v8 upload modal (`v8/components/modals/UploadPanel.vue`) mounts one `v8/components/forms/upload/UploadingLine.vue` per queued file. A folder drop of several thousand files mounts several thousand rows, each with its progress bar, miniature box (Feature 077) and `IntersectionObserver`. The list is virtualized with `useVirtualizer` from `@tanstack/vue-virtual` (already a dependency, used by `AlbumNavTree.vue`), so only the rows near the visible part of the list box are mounted. Frontend only, v8 only; no backend, API or config change.

Each row owns its upload: `UploadingLine.vue` runs the chunk loop, tracks progress and emits `upload:completed`, which `UploadPanel.vue` uses to record the result and start the next file. Vue drops events emitted by an unmounted component, and a row mounted with status `uploading` starts its file from the first chunk. A row whose status is `uploading` therefore stays mounted wherever the list is scrolled (owner directive), and the modal cannot be dismissed while uploads run, since the dialog unmounts its content when hidden (FR-087-07).

## Goals
- G1: the number of mounted rows is bounded by the list box height, the overscan and the number of running uploads, whatever the queue length.
- G2: uploads behave as they do without virtualization: same concurrency (`upload_processing_limit`), every completion recorded, counters, refresh and auto-close unchanged.
- G3: the list looks and scrolls as it does without virtualization: same row layout, wrapping warning and error messages, scrolling to the row that just started, miniatures shown again without a second decode.

## Non-Goals
- v7 (`v7/components/modals/UploadPanel.vue`, `v7/components/forms/upload/UploadingLine.vue`) is unchanged.
- The upload engine stays in `UploadingLine.vue`; it does not move to a store or composable.
- Upload order, concurrency, chunking, retries and error handling are unchanged.
- The empty-queue state (file picker, drop zone, watermark switch) is unchanged.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-087-01 | The upload list renders through `useVirtualizer` bound to the list's own scroll box (`h-72`). Rows are absolutely positioned inside a sizer whose height is the virtualizer's total size. Row heights are measured (`measureElement`), with a single-line row as the estimate, because warning and error messages wrap. The 4 px gap between rows and the 16 px padding above the first and below the last row come from the virtualizer's `gap`, `paddingStart` and `paddingEnd`. Rows are keyed by `Uploadable.uid`. | Only the rows within the visible area plus the overscan are mounted (FR-087-02 adds running uploads). The scrollbar reflects the whole queue. | — | — | none | G1, G3 |
| FR-087-02 | Every row whose status is `uploading` is mounted, inside or outside the visible range. A range extractor adds the indices of these rows to the virtualizer's default range, so they render through the same keyed `v-for` as visible rows and keep their component instance when they leave or enter the visible range. A row is never unmounted while its status is `uploading`. | Scrolling away from a running upload leaves its row in the DOM; its completion is recorded and the next file starts. | — | — | none | Owner directive, G2 |
| FR-087-03 | A row whose status becomes `uploading` while it is outside the visible range mounts with that status and starts its upload on mount. | Files far below the visible area start and complete without being scrolled into view. | — | — | none | G2 |
| FR-087-04 | A row mounted after its upload ended renders from the queue entry: status and message from `list_upload_files`, progress bar full for `done`, `warning` and `error`. | Scrolling back to a failed file shows its message and a full error-coloured bar. | — | — | none | G3 |
| FR-087-05 | When files start uploading, the list box scrolls smoothly to centre the last started row with the virtualizer's `scrollToIndex`. | The running upload stays in view while the queue progresses, including rows that were not mounted when they started. | — | — | none | G3 |
| FR-087-06 | A built miniature is kept by `Uploadable.uid` in `v8/utils/uploadThumbnail.ts` until the upload list is cleared (close, cancel, album change), when every kept object URL is revoked. A row mounting with a kept miniature shows it without decoding again; otherwise it follows FR-077-03. A row unmounting drops its queued decode job; a decode that ends after the list was cleared revokes its own URL. | Scrolling back shows the miniatures at once. | — | Decode failure → `lucide:image` placeholder (FR-077-02); the next mount tries again. | none | Q-087-01, amends FR-077-03, FR-077-07 |
| FR-087-07 | While some queued files have not ended (`showCancel`), the modal cannot be dismissed: Escape and click outside leave it open (`dismissible` false) and the header close button is hidden (`close` false). Cancel stays available. Once every file has ended, all three close the modal again. | Uploads run to the end with the modal open; every completion is recorded. | — | — | none | Q-087-02 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-087-01 | Mounted rows ≤ visible rows + 2 × overscan + `upload_processing_limit`, for any queue length. | G1 | Playwright: queue 2000 files, count rows in the list box at the top, middle and bottom of the list and while uploads run. | FR-087-01, FR-087-02 | — |
| NFR-087-02 | No new dependency. | Dependency policy | `package.json` unchanged. | `@tanstack/vue-virtual` | AGENTS.md |
| NFR-087-03 | Rows span the full width of the sizer without physical left/right offsets, so the Feature 077 RTL mirroring is unchanged. | Feature 077 NFR-077-03 | Manual check with an RTL locale. | — | — |
| NFR-087-04 | Works offline. | No runtime dependency on an external host. | No network access added. | — | Owner directive |

## UI / Interaction Mock-ups

The modal looks the same; only the mounted set changes. 2000 files queued, two running (`upload_processing_limit` = 2), list scrolled to the top:

```
+--------------------------- Upload ----------------------------+
|                      Uploaded 812 / 2000                      |
| [========================>                                  ] |
| ┌ list box (h-72, scrolls) ─────────────────────────────────┐ |
| │ ┌────┐ IMG_0001.jpg                            Finished   │▲|
| │ └────┘ [==================================================]│█|  mounted:
| │ ┌────┐ IMG_0002.jpg                            Finished   ││|  visible rows
| │ └────┘ [==================================================]││|  + overscan
| │   …                                                       ││|
| └───────────────────────────────────────────────────────────┘▼|
|                                                               |
|   rows 813–814  Uploading   mounted outside the visible range |
|                             (FR-087-02)                       |
|   rows 815–2000 Waiting     not mounted                       |
|                                                               |
| [            Cancel            ]                              |
+---------------------------------------------------------------+
```

While uploads run the header has no close button and Escape or a click outside does nothing (FR-087-07); once every file has ended:

```
+--------------------------- Upload ---------------------- [x] -+
|                          Completed                            |
| …                                                             |
| [            Close             ]                              |
+---------------------------------------------------------------+
```

Scrolled back to a failed row (FR-087-04), wrapping message, full error bar:

```
| │ ┌────┐ IMG_0420.jpg                                       │ |
| │ └────┘ The file is a duplicate of IMG_0419.jpg.   Error   │ |
| │        [##################################################]│ |
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-087-01 | Queue 2000 files → the list box holds a bounded number of rows (NFR-087-01); the scrollbar spans the whole queue; scrolling to the bottom shows the last files. |
| S-087-02 | Uploads running, scroll to the far end of the list → the running rows stay mounted, complete, and the next files start; the counter reaches 2000 / 2000 and the album refreshes once. |
| S-087-03 | `upload_processing_limit` > 1 → every running row stays mounted (FR-087-02). |
| S-087-04 | A row ends in `error` or `warning`, is scrolled out and back → it shows its message and a full coloured bar (FR-087-04). |
| S-087-05 | The list follows the running upload: each start centres the last started row (FR-087-05). |
| S-087-06 | Queue 3 files → the list looks as without virtualization (padding, gaps, row layout). |
| S-087-07 | Folder drop queuing files one by one → rows appear as files arrive, uploads start as before. |
| S-087-08 | RTL locale → rows and miniatures mirrored as in Feature 077. |
| S-087-09 | Rows with built miniatures scrolled out and back → miniatures shown at once, no new decode; close the modal → every kept object URL revoked (FR-087-06). |
| S-087-10 | Uploads running → Escape and click outside leave the modal open, no header close button; after the last file ends → Escape closes it (FR-087-07). |

## Test Strategy
- **Models / Actions / REST / Artisan:** none, no backend change.
- **Frontend (v8):** the project has no frontend unit-test runner. Verification is `npm run check` (vue-tsc), `npm run format`, eslint on the touched files, and a Playwright script against a scratch instance covering S-087-01 … S-087-07 and S-087-09 … S-087-10 (row counts in the list box, completion of rows outside the visible range, counters, scroll position). S-087-08 is a manual check.

## Interface & Contract Catalogue

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-087-01 | Visible row mounted | Row within the visible range plus overscan (FR-087-01). |
| UI-087-02 | Running row mounted outside the visible range | Status `uploading` (FR-087-02). |
| UI-087-03 | Row not mounted | Outside the visible range and not `uploading`; its state lives in `list_upload_files`, its miniature in `uploadThumbnail.ts` (FR-087-04, FR-087-06). |
| UI-087-04 | Modal locked | Some files not ended: no header close button, Escape and click outside ignored (FR-087-07). |

## Telemetry & Observability
None.

## Documentation Deliverables
- Roadmap entry for Feature 087.
- Knowledge map: the v8 upload list is virtualized, running rows stay mounted because they own their upload, miniatures are kept per queued file.
- Feature 077 spec: FR-077-03 and FR-077-07 point to FR-087-06.

## Spec DSL

```
ui_states:
  - id: UI-087-01
    description: Row within the visible range plus overscan, mounted
  - id: UI-087-02
    description: Uploading row outside the visible range, kept mounted
  - id: UI-087-03
    description: Row outside the range and not uploading, not mounted
  - id: UI-087-04
    description: Modal not dismissible while uploads run
```
