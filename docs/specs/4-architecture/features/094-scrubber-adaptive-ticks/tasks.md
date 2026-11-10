# Feature 094 Tasks – Scrubber Adaptive Ticks

_Status: Complete_  
_Last updated: 2026-10-16_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

- [x] T-094-01 – Scenario seed and expected labels (S-094-01 … 17, S-094-19).  
  _Intent:_ executable spec in lieu of a JS runner: seed script in the scratchpad giving one `/timeline` dataset and one album per scenario; each dataset reproduces a row of the spec's Example Catalogue (EX-094-T01 … T14, A01 … A18, L01 … L08, B01 … B10), whose labels and dots are the expected results.  
  _Verification commands:_ seed runs on the `run-lychee` scratch instance; current rail screenshotted as the "before" state.

- [x] T-094-02 – `railTicks.ts` helpers (F-094-01, F-094-02, F-094-03, F-094-04, F-094-06, S-094-13, S-094-16, S-094-17).  
  _Intent:_ `entryKeyOf`, `precisionOf`, `spanUnit`, `formatTickLabel`, `buildRailTicks` with label and dot culls, per the plan's branch tables.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-094-03 – `TimelineDatesV3.vue` renders the new ticks (F-094-04, F-094-05, F-094-06, F-094-07, S-094-01 … 15).  
  _Intent:_ `ticks` from `buildRailTicks()`, `"label" | "dot"` kinds, highlight by L-prefix; no prop change.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - browser: seeded `/timeline` and album scenarios match the expected labels

- [x] T-094-04 – Hourly lens and readout labels, wide lens (F-094-08, F-094-09, N-094-02, S-094-19).  
  _Intent:_ `hourlyLensLabels()` in `railTicks.ts`; `Timeline.vue` passes the relabelled bucket tier to the rail; `isWideLens()` drives the lens width and pill offset in `TimelineDatesV3.vue`; expected results EX-094-L01 … L08 (hour only when every photo is on one day).  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - browser: `/timeline` at hour (several days, one day) and at day granularity

- [x] T-094-05 – Browser check S-094-18 and full scenario screenshots (LTR/RTL, light/dark), recorded in the plan.  
  _Verification commands:_ `run-lychee` scratch instance.

- [x] T-094-06 – Docs and drift gate.  
  _Intent:_ Feature 071 spec (NG2, FR-071-12 note), Feature 092 backlog item closed, knowledge map, roadmap, drift gate report.  
  _Verification commands:_ `php artisan typescript:transform` (no diff in `resources/js/lychee.d.ts`).

- [x] T-094-07 – Album hour runs (F-094-10, S-094-10).  
  _Intent:_ `dateScrubber.ts` tile key at hour precision; `PhotoGridVirtual.vue` picks hour runs when the photo header ids are hour-precise and the field is `taken_at` / `created_at`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - Node check of the key helper (UTC, zone, missing date)

- [x] T-094-08 – Album hourly lens and pill (F-094-08, F-094-09, F-094-10, EX-094-L09, EX-094-L10).  
  _Intent:_ `AlbumPanel.vue` passes the rail buckets through `hourlyLensLabels()`.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-094-09 – Browser check of hourly albums (S-094-10, EX-094-A16, L09, L10) and docs (spec, plan drift note, roadmap).  
  _Verification commands:_ `run-lychee` scratch instance.

- [x] T-094-10 – No lens on hour-only rails (FR-094-11).  
  _Intent:_ `railTicks.ts` `hasLens()` pure helper; `TimelineDatesV3.vue` keeps the lens closed when it returns false, so the focal line and the pill beside the rail show.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `run-lychee` browser check: album with hour headers on one day (no lens), several days (wide lens)

## Notes / TODOs

- 2026-10-16 (FR-094-11): browser — `/timeline` as `uh` (D-H, one day): hover shows the focal line and pill `15:00  2 photos`, no lens. As `udense` (hours over months): wide lens unchanged.

- 2026-10-16 (Q-094-09): browser — A16 (hour headers, one day): rail `18:00` … `09:00`, pill `15:00  2 photos`; AD (hour headers, months): wide lens with date and hour, pill `18 Jun 2026 04:00  1 photo`. A11 (one photo at 10:00 on four days) shows no rail: its four hour buckets are merged into one `10:00` boundary by `computeBucketBoundaries()` — Q-095-08.

- 2026-10-16: seed `scratchpad/seed094.php` (users uy/um/ud/uh/udense for `/timeline`, admin albums A02/A05/A11/A16/AD), helpers checked in Node against EX-094-T01 … T14, A01/A02/A03/A05/A16, B01 … B04, B07, B08 and L01/L07 (`scratchpad/railcheck/check.ts`, stubbed `phpDateFormat`). Browser: T10 … T13, dense, A02, A11, A16, AD, wide lens LTR/RTL, tap-to-scrub. A05 shows no rail: Q-094-08 (→ B, no rail on single-header albums). Light mode via `dark_mode_enabled = 0`: labels, dots, wide lens and pill follow the theme tokens.

- No PHP change: `php-cs-fixer`, `phpstan` and `php artisan test` are not part of this feature's gate unless that changes.
