# Feature 097 – PDF Photo View

| Field | Value |
|-------|-------|
| Status | Ready |
| Last updated | 2026-10-17 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #097 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
When a PDF is shown in the photo view (`ImageViewMode.Pdf`), the mouse wheel changes photo instead of scrolling the PDF's pages: `PhotoPanel.vue` listens to `wheel` on `window` and maps every event to next/previous when "scroll to navigate" is enabled. The dock also sits on top of the PDF and hides part of its first page. This feature makes the wheel scroll the PDF and moves the dock actions into the photo header for PDFs. Frontend only: v8 (Q-097-03 A); v7 is unchanged. No backend or API change.

## Goals
- Scrolling over a displayed PDF scrolls its pages and never changes photo (Q-097-01 A).
- The dock is not shown for PDFs; move and delete are in the photo header instead (Q-097-02 B).

## Non-Goals
- Replacing the browser's embedded PDF viewer (pdf.js or similar).
- Changing wheel, dock or header behaviour for images, videos, RAW placeholders or 360° photos.
- The v7 frontend (Q-097-03 A).
- Highlight and set-album-header from the PDF photo view: both dock actions are dropped for PDFs (Q-097-02 B). The header's existing highlight button for users without album edit rights is unchanged.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-097-01 | The wheel never navigates photos while a PDF is displayed, like the 360° sphere (FR-082-10) (Q-097-01 A). | Wheel scrolls the PDF; arrows, keyboard and swipe still navigate. | Applies only when `photoStore.imageViewMode === ImageViewMode.Pdf`. | n/a | none | Owner report 2026-10-17 |
| FR-097-02 | The dock is hidden while a PDF is displayed. | No dock over the PDF. | Applies only to `ImageViewMode.Pdf`. | n/a | none | Owner directive 2026-10-17 |
| FR-097-03 | The photo header shows move (when `albumStore.rights.can_move_content`) and delete for a PDF, with the dock's rights checks (Q-097-02 B). | Buttons emit `toggleMove` / `toggleDelete`, the same events as the dock buttons. | Shown only when the dock would have been shown (`canEditCurrentPhoto`, not editing, not in a slideshow). | n/a | none | Owner directive 2026-10-17 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-097-01 | No regression for non-PDF media. | Scope guard | Manual check: image, video, 360° photo keep their wheel and dock behaviour. | `PhotoPanel.vue`, `PhotoHeader.vue` | AGENTS.md |
| NFR-097-02 | Frontend gates pass. | Quality | `vp check --fix`, `vp run typecheck` | Vite+ (Feature 096) | AGENTS.md |

## UI / Interaction Mock-ups

Before (PDF):
```
+----------------------------------------------------------------+
| [<]                               [app] [dl] [edit] [info] ... |
|                 +----------------------------+                 |
|                 | [flag][img][folder][trash] |   <- dock       |
|  +----------------------------------------------------------+  |
|  | PDF page 1 (hidden under the dock)                       |  |
|  |                                                          |  |
|  |        wheel ->  next / previous photo  (bug)            |  |
|  +----------------------------------------------------------+  |
+----------------------------------------------------------------+
```

After (PDF):
```
+----------------------------------------------------------------+
| [<]                  [app] [dl] [edit] [info] [folder] [trash] |
|  +----------------------------------------------------------+  |
|  | PDF page 1                                               |  |
|  |                                                          |  |
|  |        wheel ->  scrolls the PDF pages                   |  |
|  +----------------------------------------------------------+  |
+----------------------------------------------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-097-01 | PDF shown, scroll-to-navigate on, wheel over the PDF: pages scroll, photo stays. |
| S-097-02 | PDF shown, arrow key / next button: navigates as before. |
| S-097-03 | PDF shown, user can edit and move: no dock; header shows move and delete. |
| S-097-03b | PDF shown, user can edit but not move: header shows delete only. |
| S-097-04 | PDF shown, user cannot edit: no dock, no extra header actions. |
| S-097-05 | Image shown: wheel navigation and dock unchanged. |

## Test Strategy
- **Frontend (v8):** no frontend unit test harness covers `PhotoPanel`/`PhotoHeader`; verified with `vp run typecheck`, `vp check --fix` and a manual run of S-097-01 … 05 on a scratch instance (run-lychee skill).
- **Backend:** none.

## Interface & Contract Catalogue

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-097-01 | PDF view, editor | Dock hidden, move and delete in header. |
| UI-097-02 | PDF view, viewer | Dock hidden, header unchanged. |

## Telemetry & Observability
None.

## Documentation Deliverables
Roadmap row 097.

## Fixtures & Sample Data
None.

## Spec DSL
```
ui_states:
  - id: UI-097-01
    description: PDF view, editor — dock hidden, move and delete in header
  - id: UI-097-02
    description: PDF view, viewer — dock hidden, header unchanged
```
