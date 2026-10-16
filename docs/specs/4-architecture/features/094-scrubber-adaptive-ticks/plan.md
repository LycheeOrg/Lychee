# Feature Plan 094 – Scrubber Adaptive Ticks

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented — drift gate 2026-10-16  
_Last updated:_ 2026-10-16

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

The scrubber rail's resting labels tell the viewer where they are at the scale of their data: years for a long library, months for one year, days for one month, hours for one day on `/timeline`.

- S-094-01 … S-094-19 and every Example Catalogue row pass in the browser on `/timeline` and on album rails.
- `npm run check` and `npm run format` clean; no API or `lychee.d.ts` change (NFR-094-02).
- Hourly `/timeline` lens and pill show date and hour in a lens wide enough for it (FR-094-08, FR-094-09); otherwise lens, pill, playhead and scrubbing unchanged (FR-094-07).

## Scope Alignment

- **In scope:** resting-state labels, highlight and dots of `TimelineDatesV3.vue`, through a new pure helper module; hourly lens and pill labels on `/timeline` (FR-094-08).
- **Out of scope:** configurable label formats (N-094-01, follow-up), lens and pill labels other than hourly `/timeline`, hour entries on albums (N-094-03), v7, backend, rail eligibility, `AlbumPanel.vue` (the only caller change is `Timeline.vue`, FR-094-08).

## Dependencies & Interfaces

- Feature 066 T-067 (`TimelineDatesV3.vue`), Feature 071 (album rail, FR-071-08/12, ADR-071-01), Feature 092 (bucket id = key prefix, FR-092-05; capture-local keys, FR-092-01/18).
- `resources/js/v8/utils/phpDateFormat.ts` (`M` short month).
- `run-lychee` skill for the browser check.

## Assumptions & Risks

- **Assumptions:** `/timeline` loads the full `buckets` tier up front (`timelineStore.bucketsV3`); album entries carry the day key as `bucketId` with an optional `#n` suffix (`deriveDayScrubEntries`).
- **Risks / Mitigations:**
  - Dense rails (day dots over a year, hour dots over a month): 4 px dot cull (FR-094-06).
  - Uneven distribution clusters labels: 16 px label cull (FR-094-04).

## Implementation Drift Gate

After I4: compare each FR-094-* against the code and S-094-* screenshots; record a table (FR → file:line → evidence) under "Drift Gate Report" below; rerun `npm run check`.

## Increment Map

1. **I1 – Tick helpers** (FR-094-01 … 04, 06; S-094-13, 16, 17)
   - _Steps:_ seed script reproducing the spec's Example Catalogue first (T-094-01); then `railTicks.ts`: `entryKeyOf(bucketId)`, `precisionOf(key)`, `spanUnit(min, max, e)`, `formatTickLabel(key, unit)`, `buildRailTicks({ keys, tops, separatorTops, toRailPx })` → `{ labels, dots, unit }` with both culls. License header per coding conventions.
   - _Commands:_ `npm run format`, `npm run check`.
   - _Exit:_ helpers typed and pure; branch tables below hold.
2. **I2 – Rail rendering** (FR-094-04 … 07; S-094-01 … 15)
   - _Steps:_ `TimelineDatesV3.vue`: `ticks` from `buildRailTicks()`, `"label" | "dot"` kinds, highlight by L-prefix of the scroll/hover entry key.
   - _Commands:_ `npm run format`, `npm run check`.
   - _Exit:_ `/timeline` and album rails show the seeded scenarios.
3. **I3 – Hourly lens and readout labels** (FR-094-08, FR-094-09; S-094-19, EX-094-L01 … L08)
   - _Steps:_ `hourlyLensLabels(buckets, dayFormat)` in `railTicks.ts` (returns the bucket tier with labels replaced when ids have precision 4, unchanged otherwise); `Timeline.vue` applies it to `timelineStore.bucketsV3` with `lycheeStore.date_scrubber_label_format` before passing it to the rail; `isWideLens(bucketIds)` in `railTicks.ts`, used by `TimelineDatesV3.vue` for the lens width (`w-60` / `w-50`) and the pill offset (`63` / `53`).
   - _Commands:_ `npm run format`, `npm run check`.
   - _Exit:_ EX-094-L01 … L08 on the scratch instance (L03 … L06 unchanged labels).
4. **I4 – Browser check and docs** (S-094-18; documentation deliverables)
   - _Steps:_ screenshot S-094-01 … 19 (LTR/RTL, light/dark); amend Feature 071 spec (NG2, FR-071-12 note), close the Feature 092 backlog item, knowledge map, roadmap; drift gate.
   - _Commands:_ `npm run check`; `php artisan typescript:transform` (expect no diff).
   - _Exit:_ all scenarios recorded; docs updated.

### Branch tables

`entryKeyOf(bucketId)`:

| Input | Result |
|-------|--------|
| `2026-10-14#2` | `2026-10-14` |
| `2026-10-14-16` | `2026-10-14-16` |
| `unknown`, `abc`, `2026-1` | `null` |

`spanUnit(min, max, e)` — keys compared by prefix:

| Condition | Result |
|-----------|--------|
| years differ | 1 |
| months differ (same year) | 2 |
| days differ (same month) | 3 |
| hours differ (same day) | 4 |
| equal | e |

Label unit: `L = spanUnit(...)` (Q-094-02 → B).

Dot level: `/timeline` → `L + 1` when `L + 1 ≤ E`, else none; albums → `separatorTops` (Q-094-04 → A).

`formatTickLabel(key, unit)`: 1 → `Y`; 2 → `M`; 3 → `d/m`; 4 → `H:00`.

`hourlyLensLabels(buckets, dayFormat)`: 

| Condition | Dated labels |
|-----------|--------------|
| precision 4, dated ids on several days | `phpDateFormat(dayFormat, day) + " " + H:00` |
| precision 4, every dated id on one day | `H:00` |
| precision 1–3 | unchanged (input returned) |

`unknown` keeps its label.

`isWideLens(bucketIds)`: precision 4 and dated ids on more than one day → `true`; otherwise `false` (FR-094-09).

Album header dots: the header directly above an entry that starts an L-unit (first entry at or below it, no other header in between) → no dot; any other header, including hour headers inside a day run → dot (FR-094-06).

Culls (rail order): label within 16 px of the previous kept label → dropped (first kept); dot within 16 px of any kept label → dropped; dot within 4 px of the previous kept dot → dropped.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-094-01 … 07 | I1, I2 / T-094-02, T-094-03 | `/timeline`, `timeline_photos_granularity` varied |
| S-094-08 … 12 | I2 / T-094-03 | album rails, header granularity varied |
| S-094-13 … 17 | I1, I2 / T-094-02, T-094-03 | edge cases |
| S-094-18 | I4 / T-094-05 | interaction regression |
| S-094-19 | I3 / T-094-04 | hourly lens and pill |

## Analysis Gate

Re-run 2026-10-16, agent review, after FR-094-08/09, the consistency review and Q-094-07:

1. Specification completeness — pass: FR-094-01 … 09, NFR-094-01 … 03; Q-094-01 … 07 folded into N-094-03 and FR-094-02/03/06/09; mock-ups for the rail, the album rail, the lens at day and at hour (wide and hour-only).
2. Open questions review — pass: no open entries; no ADR needed (display rules only; ADR-071-01 holds).
3. Plan alignment — pass: increments cite FR-094-01 … 09; success and exit criteria cite S-094-01 … 19 and the Example Catalogue.
4. Tasks coverage — pass: FR-094-01 … 04, 06 → T-094-02; FR-094-04 … 07 → T-094-03; FR-094-08/09 → T-094-04; seed with expected results (T-094-01) before code; increments ≤ 90 minutes.
5. Working agreements — pass: spec-first, no dependency, decisions in pure helpers (NFR-094-03); ADR-071-01 and ADR-092-01 reviewed.
6. Tooling readiness — pass: unchanged (frontend commands only).

First run, 2026-10-16, agent review (superseded):

1. Specification completeness — pass: goals, FR-094-01 … 07, NFR-094-01 … 03 populated; Q-094-01 … 06 folded into N-094-03 and FR-094-02/03/06; ASCII mock-ups included.
2. Open questions review — pass: no open entries; no ADR needed (frontend-only display rule; ADR-071-01 still holds, album entries stay client-derived days).
3. Plan alignment — pass: plan links spec and tasks; success criteria use the spec's IDs.
4. Tasks coverage — pass: FR-094-01 … 04, 06 → T-094-02; FR-094-04 … 07 → T-094-03; scenarios seeded with expected labels (T-094-01) before code; increments ≤ 90 minutes.
5. Working agreements — pass: spec-first, no dependency, logic in pure helpers (NFR-094-03); ADR-071-01 and ADR-092-01 reviewed.
6. Tooling readiness — pass: commands per increment above. No PHP change, so `php-cs-fixer`, `phpstan` and PHP tests are not in this feature's gate.

## Drift Gate Report

2026-10-16, agent review after T-094-01 … 06 (`npm run format`, `npm run check` green; no PHP change).

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-094-01 | `railTicks.ts` `entryKeyOf()`, `keyedEntries()` | Node check B07 (`#1` suffix); undated photos never reach a rail (B06) |
| FR-094-02 | `labelUnitOf()`, `spanUnit()` | Node T01 … T14, B01 … B03, ascending order; browser uy, um, ud, uh, udense |
| FR-094-03 | `formatTickLabel()`, `DAY_TICK_FORMAT`, `HOUR_TICK_FORMAT` | Browser: `2026`, `Oct`, `28/10`, `18:00` |
| FR-094-04 | `cullLabels()` | Node B08 (no two labels < 16 px) |
| FR-094-05 | `TimelineDatesV3.vue` `highlightUnitKey` | Browser: hovered/scrolled unit in `text-primary` |
| FR-094-06 | `unitDotTops()`, `separatorDotTops()`, `cullDots()` | Node T03/T06/T10 (2 month dots), A01/A02/A03/A16, B04; browser A02, A16, AD |
| FR-094-07 | `TimelineDatesV3.vue` interaction code untouched | Browser: tap-to-scrub scrolls and pushes the route; no page errors |
| FR-094-08 | `hourlyLensLabels()`, `Timeline.vue` `railBuckets` | Browser: ud pill `21 Oct 2026 10:00`, uh pill `15:00`, headers keep server labels |
| FR-094-09 | `isWideLens()`, lens `w-60`/`w-50`, pill `63`/`53` | Browser udense LTR/RTL: full labels in the 240 px lens |
| NFR-094-01 … 03 | single passes, no sort; pure helpers | Code review |

Divergences: the album header-dot rule was corrected during implementation (spec FR-094-06 updated); single-header albums show no rail (Q-094-08 → B, catalogue updated). Observed, out of scope: the pill and the lens focal item can name neighbouring buckets (pill = span under the cursor, focal = nearest label), and RTL bidi moves a leading day number to the end of lens/pill labels — both pre-existing.

## Exit Criteria

- All tasks `[x]`; `npm run format`, `npm run check` clean; `lychee.d.ts` unchanged.
- S-094-01 … 19 recorded; roadmap, knowledge map, Feature 071/092 docs updated; `_current-session.md` refreshed.

## Follow-ups / Backlog

- Configurable rail label formats (`d/m`, `H:00`) in `InitConfig` (N-094-01).
- Album photo headers accept `hour` granularity (`timelinePhotoGranularityOptions`, FR-092-05) while album rails stay daily (Q-094-01); revisit if the owner wants `hour` removed from album settings.

## Intent Log

- 2026-10-16: owner asked to base rail ticks on the chosen granularity and the data span (hour granularity: days → `DD/MM`, months → `Oct.`, years → `YYYY`), spec and plan only, no implementation, and to check for inconsistencies. Read: Feature 071 spec and ADR-071-01, Feature 092 spec and plan backlog, `TimelineDatesV3.vue`, `dateScrubber.ts`, `PhotoGridVirtual.vue`, `AlbumPanel.vue`, `Timeline.vue`, `TimelinePhotoGranularity`, `constants.ts`. Inconsistencies logged as Q-094-01 … 06.
- 2026-10-16: owner answered Q-094-01 (albums have no hourly entries → B), Q-094-02 B, Q-094-03 … 06 A. Spec rewritten: L = span of the rail entries; the `granularity` prop and album wiring dropped; analysis gate run.
- 2026-10-16: owner asked for every combination of examples in the spec → Example Catalogue (EX-094-T01 … T14 `/timeline`, A01 … A18 albums, B01 … B10 boundaries).
- 2026-10-16: owner asked that the lens and readout show the time in hourly mode → FR-094-08 (`/timeline` only; albums have no hourly entries), format `date_scrubber_label_format` + `H:00`, EX-094-L01 … L06.
- 2026-10-16: owner asked that, with every photo on the same day, the hourly lens show the hour only → FR-094-08 extended to the lens and the pill, EX-094-L07/L08.
- 2026-10-16: owner asked for a consistency review → fixes: month-name locale (FR-094-03), undated photos never reach a rail (FR-094-01, S-094-17, EX-094-B06), S-094-07 vs EX-094-B01 dots, EX-094-B05 at hour (new B11), album header dots dropped by unit start instead of distance (FR-094-06, NFR-094-01), S-094-09/11 made precise, plan scope vs `Timeline.vue`, scenario ranges; Q-094-07 opened (lens width for hourly labels).
- 2026-10-16: owner chose Q-094-07 A → FR-094-09 (240 px lens and pill offset 63 for dated hour labels), lens-width column in EX-094-L01 … L08, `isWideLens()`; analysis gate re-run.
- 2026-10-16: implementation T-094-01 … 04; the browser check found single-header albums show no rail (Feature 071 eligibility) → Q-094-08, owner chose B: catalogue rows A05, A09, A10, A13 … A15, A17 → no rail; album mock-up and S-094-08 updated; the header-dot rule corrected to "header directly above a unit start" (hour headers inside a day run).
