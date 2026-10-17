# Open Questions – Feature 099

Open questions for [Feature 099](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-099-01~~ | 099 | Medium | Wheel over a PDF: A) never navigate photos while a PDF is shown, B) navigate only when the wheel is outside the PDF frame, C) navigate only once the PDF hits its first/last page | Resolved (A, 2026-10-17, FR-099-01) | 2026-10-17 | 2026-10-17 |
| ~~Q-099-02~~ | 099 | Medium | Which dock actions move to the header for PDFs: A) every dock action that applies to a PDF (highlight, set album header, move, delete), B) move and delete only, C) move and delete in the header, the rest in an overflow menu | Resolved (B, 2026-10-17, FR-099-03) | 2026-10-17 | 2026-10-17 |
| ~~Q-099-03~~ | 099 | Low | Frontend scope: A) v8 only, B) v8 and v7 | Resolved (A, 2026-10-17, Non-Goals) | 2026-10-17 | 2026-10-17 |

## Question Details

### ~~Q-099-01~~ · Wheel behaviour while a PDF is displayed

**Question:** `PhotoPanel.vue` listens to `wheel` on `window` and turns any wheel event into next/previous when "scroll to navigate" is on, so scrolling a PDF changes photo instead of turning pages. What should the wheel do while a PDF is shown?

**Answer:** A (2026-10-17), folded into FR-099-01.

- **A (recommended) – Never navigate while a PDF is shown.** Same rule as the 360° sphere (FR-082-10). The wheel only scrolls the PDF; arrows, keyboard and swipe still navigate. Simple, predictable, one guard in `scrollTo`.
- **B – Navigate only when the wheel is outside the PDF frame** (header strip, margins). Keeps wheel navigation, but the strip is thin, and browsers differ in which wheel events leave the embedded viewer, so it is hard to predict and to test.
- **C – Navigate once the PDF reaches its first/last page.** Natural "scroll through", but the embedded browser viewer exposes no page/scroll position to the page, so it cannot be implemented with `<embed>`.

### ~~Q-099-02~~ · Header actions replacing the dock for PDFs

**Question:** For PDFs, the dock is hidden and its actions move to the header. Which actions?

Dock actions today: highlight, set album header, watermark, scan faces, rotate CCW/CW, move, delete. Watermark, scan faces and rotate do not apply to a PDF.

**Answer:** B (2026-10-17), folded into FR-099-03 and Non-Goals.

- **A (recommended) – Every action that applies to a PDF:** highlight, set album header (not in the timeline), move (when `can_move_content`), delete. No capability lost; same rights checks as the dock.
- **B – Move and delete only.** Smallest header, but PDF owners can no longer highlight a PDF or set it as album header from the photo view.
- **C – Move and delete as buttons, highlight and set album header in a `…` overflow menu.** Compact, but adds a new menu pattern to the photo header.

### ~~Q-099-03~~ · v7 frontend

**Question:** The same wheel listener and dock exist in `resources/js/v7/components/gallery/photoModule/`. Should v7 change too?

**Answer:** A (2026-10-17), folded into Overview and Non-Goals.

- **A (recommended) – v8 only.** v8 is the current frontend; v7 stays as is.
- **B – v8 and v7.** Same behaviour in both, but twice the change and the testing.
