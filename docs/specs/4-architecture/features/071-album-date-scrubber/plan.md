# Feature 071 Plan – Album Date Scrubber

_Status: Implemented — manual browser verification pending (T-071-19)_
_Last updated: 2026-09-24_
_Linked spec:_ [spec.md](spec.md) · _Tasks:_ [tasks.md](tasks.md) · _ADR:_ [ADR-0011](../../../6-decisions/ADR-0011-date-scrubber-ticks-derived-client-side.md)

## Vision & Success Criteria
Album views on the v8 SoA path show the Timeline's scrubber rail when the album is single-kind and date-ordered, with day precision, no extra request, and `/timeline` unchanged. Success = S-071-01…12 pass (automated backend branches, manual browser check for UI), and the quality gate is green.

## Scope Alignment
- In scope: FR-071-01…11, NFR-071-01…05.
- Out of scope: NG1–NG8, in particular bucket storage (ADR-0011) and bulk edit.

## Dependencies & Interfaces
- Reuses `TimelineDatesV3.vue` (Feature 066 T-067), `phpDateFormat.ts` (Feature 063), and the v3 tiers of Features 061/064.
- Backend touch points:
  - `database/migrations` (one `BaseConfigMigration` for the config, one schema migration for the column).
  - `BaseAlbumImpl` / `BaseAlbum` (property, cast, default).
  - `AlbumConfig`.
  - `UpdateAlbumRequest` / `UpdateTagAlbumRequest` / `UpdatePersonAlbumRequest` (+ `RequestAttribute`, a new `HasDateScrubber` contract and trait, following `HasTimelinePhoto`).
  - `AlbumController` (three `photo_timeline` write sites: L142/L212/L246).
  - `EditableBaseAlbumResource`.
- Frontend touch points:
  - New `resources/js/v8/utils/dateScrubber.ts` (pure helpers).
  - `TimelineDatesV3.vue` (one additive prop).
  - `PhotoGridVirtual.vue`, `AlbumThumbGridVirtual.vue`, and their panel wrappers `PhotoThumbPanelVirtual.vue` / `AlbumThumbPanelVirtual.vue` (event and ref forwarding).
  - `AlbumPanel.vue` (rail mount), `AlbumHeader.vue` (toggle), `AlbumProperties.vue` (override select).
  - Generated `lychee.d.ts` (`make gen_typescript_types`).
- No new dependency.

## Assumptions & Risks
- **R1 — date format in `taken_ats`/`created_ats`.** They are the raw DB values (`QueryPhotoRatios` L359–360), so the first 10 characters give the same day the server's `truncateRawDate()` uses. Albums' `created_ats`/`min_taken_ats`/`max_taken_ats` must be confirmed to be raw too; if one is ISO with an offset, derive the day from the same string prefix anyway, to stay in step with the server bucket.
- **R2 — masonry.** Tile tops are not monotonic across columns. An entry's `top` = the minimum `box.top` of its run. Its `height` = the next entry's top − its top, clamped ≥ 0, with the last entry ending at `totalHeight`. `bucketAt()`'s binary search needs non-decreasing tops, so the derivation enforces `top_i = max(top_i, top_{i-1})`.
- **R3 — rail range excludes the hero.** Position 0 on the rail is the grid's top (FR-071-10), which matches how `scrollToPixelOffset()` already adds `scrollMargin`.
- **R4 — no JS test runner (NG8).** Helper branch tables are listed in the tasks and verified by type-checking plus the manual scenarios. Manual browser verification may be pending if no dev environment is available (as on 063/065/066).
- **Config level.** `album_date_scrubber_enabled` is level 0 (not SE-gated); the owner did not ask for gating.

## Implementation Drift Gate
Before each commit, re-read FR-071-xx for the increment. Any divergence goes back into the spec first.

## Increment Map
| Increment | Scope | Tasks | Est. |
|-----------|-------|-------|------|
| I1 | Backend: config, column, model, `AlbumConfig` eligibility (tests first) | T-071-01…04 | 90 min |
| I2 | Backend: per-album override write path for 3 album types + edit resource (tests first) | T-071-05…07 | 75 min |
| I3 | Lang keys, TS types, backend quality gate | T-071-08…09 | 45 min |
| I4 | Frontend pure helpers + `TimelineDatesV3` prop | T-071-10…11 | 75 min |
| I5 | Grids: album-mode layout, scroll and scroll-to emission for photos and albums | T-071-12…13 | 90 min |
| I6 | `AlbumPanel` rail mount + viewer toggle | T-071-14…15 | 75 min |
| I7 | Album properties override select; frontend gate | T-071-16…17 | 60 min |
| I8 | Docs: knowledge map, roadmap, session, manual verification | T-071-18…19 | 30 min |

## Scenario Tracking
| Scenario | Covered by |
|----------|-----------|
| S-071-01/02/06/07/10 | `AlbumConfigDateScrubberTest` (T-071-01) + manual |
| S-071-03/04/05/12 | `resolveDateScrubberSource` / derivation branch tables (T-071-10) + manual |
| S-071-08/09/11 | Manual (T-071-19) |
| FR-071-02 round-trip | `UpdateAlbumDateScrubberTest` (T-071-05) |

## Analysis Gate
Run 2026-09-24 against [analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md): **PASS**.
1. Spec completeness — ✅ FR-071-01…11 / NFR-071-01…05 populated; Q-071-01…05 folded into the normative sections; ASCII mock-ups present (album view, lens, properties drawer).
2. Open questions — ✅ no `Open` Q-071 rows; Q-071-05 → ADR-0011, linked from the spec and the open-questions row.
3. Plan alignment — ✅ links correct; success criteria match S-071-01…12.
4. Tasks coverage — ✅ FR-01→T02 · FR-02→T02/03/05–07/16 · FR-03/04/05→T01/04 · FR-06→T10/14 · FR-07→T15 · FR-08→T10/12/13 · FR-09→T11 · FR-10/11→T14. Backend tests precede code (T01→T04, T05→T06/07). The frontend branch table is manual, per NG8 (accepted limitation, no runner).
5. Constitution — ✅ no new dependency; eligibility split into one-decision helpers (T-071-04, T-071-10); ADR-0009 (v3 response shape) reviewed — no v3 response changes; ADR-0011 applies.
6. Tooling — ✅ commands recorded per task.
Follow-up: none.

## Exit Criteria
All tasks `[x]`. Backend: `php-cs-fixer`, filtered `php artisan test` for the new test classes and the existing `AlbumConfig`/album-update tests, and `make phpstan` all green. Frontend: `npm run format` and `npm run check` green. Roadmap updated.

## Follow-ups / Backlog
- A persisted "always smallest granularity" bucketing refactor, as its own feature (ADR-0011).
- Possibly the override in bulk album edit (NG7), if requested.

## Intent Log
- 2026-09-24: Owner kickoff ("feature 71 … date effect on album views"). The spec draft plus Q-071-01…05 were logged before any code. Answers: 1A, 2A, 3B, 4A (+ title `DATE_PREFIX`), 5A. ADR-0011 records Q-071-05. Findings that shaped the design: `ratios` already carries per-photo dates; `TimelineDatesV3.vue`'s tick logic accepts `YYYY-MM-DD` ids, so it can be fed synthesized day entries unchanged; the stored `num_children`/`num_photos` counts include items the viewer can't see, so single-kind eligibility is decided client-side from bucket-tier counts.
- 2026-09-24 (implementation): I1–I8 executed in order, tests first. Two corrections came up during implementation and were folded back into the spec before continuing:
  1. **Update-request field made optional.** It was first `present` (matching `photo_timeline`), but v7 shares those endpoints and never sends it, so v7 album editing would have got a 422. Sending `null` from v7 would have silently wiped an override set in v8. Switched to Feature 068's `published_at` pattern (omitted = unchanged); FR-071-02 updated, and the 46 existing test payloads reverted to untouched.
  2. **Visible counts come from tier-2 tile counts**, not the sum of bucket `counts`, which is empty when a tier is `bucketable: false`. Smart albums (no children listing) count 0 albums. FR-071-06 updated.
  Other findings: the day-label format is `timeline_photo_date_format_day` (`j M Y`), not the quick-access format (`j M`, no year). The album list view shares the grid's row model, so it got the rail too, through a shared composable. The pure helpers were executed, not only type-checked, via the already-installed `esbuild` + node (throwaway script kept in the session scratchpad).

## Reflection
- Coverage: backend branches are fully covered (31 new tests across eligibility, the enable layering and the write path). The frontend relies on type-checking, a one-off executed branch table, and pending manual verification (no JS runner, NG8).
- Only touch outside v8: `AlbumConfig` fallback literals in v7 `Search.vue` (compile-only), forced by the shared generated `lychee.d.ts`.
- `npm run check` reports two errors in untouched `app.ts`/`app-v8.ts` (i18n plugin typing). They were present before this feature started.
- Follow-up candidates: a JS unit-test runner (needs approval); the persisted day-granularity bucketing refactor (ADR-0011); the override in bulk album edit (NG7).
