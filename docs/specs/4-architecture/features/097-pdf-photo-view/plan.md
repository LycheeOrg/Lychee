# Feature Plan 097 – PDF Photo View

| Field | Value |
|-------|-------|
| Status | Complete |
| Last updated | 2026-10-17 |
| Linked spec | [spec.md](spec.md) |
| Linked tasks | [tasks.md](tasks.md) |

## Vision & Success Criteria
Wheel scrolls a displayed PDF instead of changing photo; the dock no longer covers the PDF and its move/delete actions live in the photo header (S-097-01 … 05).

## Scope Alignment
In: v8 `PhotoPanel.vue`, `PhotoHeader.vue`, `Dock.vue`. Out: v7, backend, highlight / set album header for PDFs (Q-097-02 B).

## Dependencies & Interfaces
`photoStore.imageViewMode` (`ImageViewMode.Pdf`), `albumStore.rights.can_move_content`, the `toggleMove` / `toggleDelete` events already handled by the photo view parent of `PhotoPanel`.

## Assumptions & Risks
- Keyboard shortcuts for move/delete are unaffected (they do not go through the dock).
- Risk: header crowding on phones; two icon-only buttons, same style as the existing ones.

## Implementation Drift Gate
Re-read FR-097-01 … 03 against the diff before commit.

## Increment Map
1. I1 – Wheel guard for PDFs in `PhotoPanel.scrollTo` (FR-097-01).
2. I2 – Hide the dock for PDFs, drop the now-unused `isNarrowMenu` prop (FR-097-02).
3. I3 – Move/delete buttons in `PhotoHeader` for PDFs, wired through `PhotoPanel` (FR-097-03).

## Scenario Tracking
| Scenario | Increment |
|----------|-----------|
| S-097-01, S-097-02 | I1 |
| S-097-03, S-097-03b, S-097-04 | I2, I3 |
| S-097-05 | I1–I3 (regression) |

## Analysis Gate
2026-10-17: spec, plan and tasks agree; Q-097-01 … 03 resolved; no ADR needed (local UI change).

## Exit Criteria
`vp check --fix`, `vp run typecheck` green; tasks checked; roadmap updated.

## Follow-ups / Backlog
None.

## Intent Log
- 2026-10-17: Owner reported the wheel changing photo over PDFs and asked to move dock actions to the header. Questions Q-097-01 … 03 asked in chat, answered "A B A". Owner asked to commit and push once done.
