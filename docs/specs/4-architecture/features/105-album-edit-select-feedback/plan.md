# Feature Plan 105 – Album Edit Select Feedback

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented  
_Last updated:_ 2026-11-01

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Opening the v8 album edit drawer shows a value in every select and its dropdown checks the applicable entry (S-105-04, S-105-08); album sort by taken date is kept (S-105-01/02). Success: Playwright sweep on the scratch instance reports a non-empty trigger and exactly one checked entry for each of the eleven option selects, before and after a reload.

## Scope Alignment
- **In scope:** FR-105-01 … 10: `UpdateAlbumRequest` fix, v8 `AlbumProperties.vue` null mapping, order coupling, header/cover placeholder, drawer-local timeline lists, three lang keys.
- **Out of scope:** N-105-01 … 04 (v7, bulk edit, restyling, header photo outside the loaded page).

## Dependencies & Interfaces
- `app/Http/Requests/Album/UpdateAlbumRequest.php`, `app/Enum/ColumnSortingAlbumType.php`.
- `resources/js/v8/components/forms/album/AlbumProperties.vue`, `resources/js/config/constants.ts` (read only), Nuxt UI `USelectMenu` (`placeholder`, `disabled`).
- `lang/<locale>/gallery.php` (23 locales).

## Assumptions & Risks
- **Assumptions:** Reka UI compares option objects by content (`ohash` `isEqual`), so a model value built from a drawer-local list matches the list entry; checked on the scratch instance.
- **Risks / Mitigations:** `USelectMenu` typing may reject a model wider than its items → type the item lists as `SelectOption<T | null>[]`; `vp run typecheck` gate.

## Implementation Drift Gate
2026-11-01, agent. All tasks done; `AlbumSortingColumnTest` 3/3, `AlbumUpdateTest` 18/18, `LangTest` 2/2, `check_translations.php` complete, `vp test` 128/128, `vp check` 0 errors (11 baseline warnings), `vp run typecheck`, `make phpstan`, php-cs-fixer clean.

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-105-01 | `UpdateAlbumRequest::processValidatedValues()` | `AlbumSortingColumnTest` (S-105-01 … 03); browser: "Latest Take Date" stored as `max_taken_at`. |
| FR-105-02 | `withDefaultOption`, `optionFor`, `AlbumProperties.vue` lists and `load()` | `albumSelectOptions.test.ts`; browser S-105-04. |
| FR-105-03 | `sortingOrderFor`, `photoSortingColumnModel`/`albumSortingColumnModel`, order `:disabled` | `albumSelectOptions.test.ts`; browser S-105-05/06 (one PATCH 200 each, row `title`/`ASC`, then `null`/`null`). |
| FR-105-04 | header/cover `placeholder` | browser S-105-04 (placeholder style). |
| FR-105-05 | same component paths for tag/person albums | code path shared; not browser-checked (no tag album in scratch data). |
| FR-105-06 | `timelineSelectOptions`, full-list lookup in `load()` | `albumSelectOptions.test.ts`; browser S-105-07 (`default` kept, album timeline stays `null`). |
| FR-105-07 | `lang/<locale>/gallery.php` ×23 | `LangTest`, `check_translations.php`. |
| FR-105-08 | `optionThumbProps`, `Thumb` in the header/cover `#item-leading` slots, `requestThumbUrls()` on open | `albumSelectOptions.test.ts` (S-105-11); browser S-105-10 (struct-of-arrays album, every entry loads its thumbnail). |
| FR-105-09 | `toggleBuyMe()` and `AlbumPanel.vue` `setAsHeader` gated on `isDetailPending()` | `buyMeActions.test.ts` (S-105-13); browser S-105-12 (one details request, hero on the photo's `medium` URL). The buy dialog itself is not browser-checked (no webshop catalogue in the scratch data). |
| FR-105-10 | `sortingOrderValueFor`; `BulkAlbumEdit.vue` `onInlineSortingColumnChange`, `sortingColumnLabel`; `BulkEditFieldsDialog.vue` lists, `isOrderLocked`/`sentOrder` | `albumSelectOptions.test.ts`; browser S-105-14 … 16 (PATCH bodies recorded in tasks). |

Findings: S-105-09 sends an SE granularity unchanged and the API refuses it without SE (422); this predates the feature, spec states it (N-105-05), follow-up below. No other divergence.

## Increment Map

1. **I1 – Album sort column parsing (FR-105-01)**
   - _Steps:_ `AlbumSortingColumnTest` (S-105-01 … 03) red, switch `ColumnSortingPhotoType` → `ColumnSortingAlbumType`, green.
   - _Commands:_ `php artisan test --filter=AlbumSortingColumnTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Exit:_ test green, phpstan clean.
2. **I2 – Option helpers (FR-105-02, 03, 06, NFR-105-02)**
   - _Steps:_ `albumSelectOptions.test.ts` red, `albumSelectOptions.ts` (`DEFAULT_OPTION`, `withDefaultOption`, `optionFor`, `timelineSelectOptions`, `sortingOrderFor`, `isDefaultOption`), green.
   - _Commands:_ `vp test`.
3. **I3 – Drawer wiring and labels (FR-105-02 … 07)**
   - _Steps:_ `AlbumProperties.vue` lists, load mapping, column setters, disabled orders, header/cover placeholders, timeline lists; three keys in 23 locales.
   - _Commands:_ `vp check`, `vp fmt <files>`, `vp run typecheck`, `vp test`, `php artisan test --filter=LangTest`, `php scripts/check_translations.php`.
4. **I4 – Browser check and docs**
   - _Steps:_ `vp build`, Playwright sweep (S-105-04 … 09) on the scratch instance; knowledge map, roadmap, drift gate.
5. **I5 – Header and cover thumbnails (FR-105-08)**
   - _Steps:_ `optionThumbProps` cases in `albumSelectOptions.test.ts` red, helper green; `Thumb` in both `#item-leading` slots, tile URLs asked on open; `vp build`, Playwright S-105-10.
   - _Commands:_ `vp test`, `vp check`, `vp run typecheck`.
6. **I6 – Struct-of-arrays actions (FR-105-09)**
   - _Steps:_ `buyMeActions.test.ts` red (S-105-13); `toggleBuyMe()` and `setAsHeader` load details when `isDetailPending()`; Playwright S-105-12.
   - _Commands:_ `vp test`, `vp run typecheck`.
7. **I7 – Bulk album edit selects (FR-105-10)**
   - _Steps:_ `sortingOrderValueFor` cases red, helper green; `BulkAlbumEdit.vue` sort cells; `BulkEditFieldsDialog.vue` lists, order coupling, submit rule; Playwright S-105-14 … 16 on `/bulk-album-edit`.
   - _Commands:_ `vp test`, `vp check`, `vp run typecheck`.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-105-01 | I1 / T-105-01, T-105-02 | `AlbumSortingColumnTest` |
| S-105-02 | I1 / T-105-01, T-105-02 | `AlbumSortingColumnTest` |
| S-105-03 | I1 / T-105-01 | `AlbumSortingColumnTest` |
| S-105-04 | I2, I3, I4 / T-105-03 … 08 | Vitest + Playwright |
| S-105-05 | I2, I4 / T-105-03, T-105-08 | `sortingOrderFor` + Playwright |
| S-105-06 | I2, I4 / T-105-03, T-105-08 | `sortingOrderFor` + Playwright |
| S-105-07 | I2, I4 / T-105-03, T-105-08 | `timelineSelectOptions` + Playwright |
| S-105-08 | I4 / T-105-08 | Playwright |
| S-105-09 | I2 / T-105-03 | `optionFor` on the full list |
| S-105-10 | I5 / T-105-12 | Playwright |
| S-105-11 | I5 / T-105-10 | `optionThumbProps` |
| S-105-12 | I6 / T-105-15, T-105-19 | Playwright |
| S-105-13 | I6 / T-105-13, T-105-14 | `buyMeActions.test.ts` |
| S-105-14 | I7 / T-105-13, T-105-16, T-105-19 | `sortingOrderValueFor` + Playwright |
| S-105-15 | I7 / T-105-13, T-105-17, T-105-19 | `sortingOrderValueFor` + Playwright |
| S-105-16 | I7 / T-105-17, T-105-19 | Playwright |

## Analysis Gate
2026-11-01, agent:
1. Specification completeness: pass (FR/NFR filled, Q-105-01/02 folded into FR-105-02 … 07, ASCII mock-ups present).
2. Open questions: pass (no open entry; no ADR: drawer-local UI decision).
3. Plan alignment: pass (links, scope = spec FR list).
4. Tasks coverage: pass (each FR has a task; tests precede code in I1, I2).
5. Working agreements: pass (mapping and coupling in pure helpers; no dependency; ADRs referencing 105: none).
6. Tooling: pass (commands listed per increment).

## Exit Criteria
- `AlbumSortingColumnTest`, `LangTest` green; `vp test`, `vp check` (baseline warnings only), `vp run typecheck`, `make phpstan`, php-cs-fixer clean.
- Playwright sweep S-105-04 … 08 recorded in tasks.
- Roadmap and knowledge map updated.

## Follow-ups / Backlog
- `gallery.timeline.default`/`disabled` are lowercase in `en` ("default", "disabled") and shared with v7 and bulk edit.
- N-105-04: header photo outside the loaded page shows "Automatic" and an autosave clears it (same cause the cover fixed with `{ id: value }`).
- N-105-05: an album storing an SE timeline granularity cannot be saved from the drawer on an instance without SE (422 on every autosave).
