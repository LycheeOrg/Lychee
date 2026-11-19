# Feature 105 Tasks – Album Edit Select Feedback

_Status: Done_  
_Last updated: 2026-11-01_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-105-01 – `AlbumSortingColumnTest`, red (F-105-01, S-105-01, S-105-02, S-105-03).  
  _Intent:_ PATCH `max_taken_at`/`DESC`, `min_taken_at`/`ASC`, `null`/`null`; assert response `album_sorting` and the `albums` row.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumSortingColumnTest` (S-105-01/02 fail)

- [x] T-105-02 – Parse the album sort column as `ColumnSortingAlbumType` (F-105-01, S-105-01, S-105-02).  
  _Intent:_ `UpdateAlbumRequest::processValidatedValues()`.  
  _Verification commands:_  
  - `php artisan test --filter=AlbumSortingColumnTest`  
  - `php artisan test --filter=AlbumUpdateTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-105-03 – `albumSelectOptions.test.ts`, red (F-105-02, F-105-03, F-105-06, S-105-04, S-105-05, S-105-06, S-105-07, S-105-09).  
  _Intent:_ `withDefaultOption`, `optionFor` (null → Default, unknown → undefined), `timelineSelectOptions` (Default first, `default` relabelled), `sortingOrderFor` (Default column → Default order, set column + Default order → Ascending, else unchanged).  
  _Verification commands:_  
  - `vp test` (new file fails: module missing)

- [x] T-105-04 – `resources/js/v8/utils/albumSelectOptions.ts` (F-105-02, F-105-03, F-105-06).  
  _Verification commands:_  
  - `vp test`

- [x] T-105-05 – Wire `AlbumProperties.vue` (F-105-02, F-105-03, F-105-04, F-105-05, F-105-06, N-105-02).  
  _Intent:_ drawer-local lists, load through `optionFor`, column setters through `sortingOrderFor`, orders disabled on a Default column, header/cover `placeholder`.  
  _Verification commands:_  
  - `vp run typecheck`  
  - `vp check` (baseline warnings only), `vp fmt` on touched files

- [x] T-105-06 – Lang keys `default`, `automatic`, `timeline_enabled` in 23 locales (F-105-07).  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`  
  - `php scripts/check_translations.php`

- [x] T-105-07 – Frontend gate (F-105-02 … 07).  
  _Verification commands:_  
  - `vp test`  
  - `vp check`  
  - `vp run typecheck`

- [x] T-105-08 – Browser check on the scratch instance (S-105-04 … 08).  
  _Intent:_ `vp build`, Playwright sweep before and after a reload, order coupling, timeline `default`.  
  _Verification commands:_  
  - `node verify.mjs` (scratchpad `pw/`)  
  _Notes:_ S-105-04 … 08 pass; S-105-09 shows the label, the API answers 422 without SE (N-105-05).

- [x] T-105-09 – Docs: knowledge map, roadmap, drift gate in plan.

- [x] T-105-10 – `optionThumbProps` cases, red (F-105-08, S-105-11).  
  _Intent:_ loaded thumb URL → direct, not pending; no thumb + resolved tile URLs → `list` URL; no thumb + pending store entry → pending; no thumb + `null` (no source) → `/Asset`.  
  _Verification commands:_  
  - `vp test`

- [x] T-105-11 – `optionThumbProps` + `Thumb` in the header/cover entries, tile URLs asked on open (F-105-08).  
  _Verification commands:_  
  - `vp test`  
  - `vp check`  
  - `vp run typecheck`

- [x] T-105-12 – Browser check S-105-10 (F-105-08, S-105-10).  
  _Verification commands:_  
  - `vp build`, `node header.mjs` (scratchpad `pw/`)
  _Notes:_ header and cover entries load their thumbnail (scratch direct upload URLs 404, so `Thumb` fell back to `/Asset`).

- [x] T-105-13 – Vitest red: `sortingOrderValueFor` cases and `buyMeActions.test.ts` (F-105-09, F-105-10, S-105-13, S-105-14, S-105-15).  
  _Intent:_ Default column → `null`; column without order → `ASC`; column with order → kept. `toggleBuyMe()` on a detail-pending photo calls `loadPhotoDetails([id])` and offers the `medium` price merged by it.  
  _Verification commands:_  
  - `vp test` (new cases fail)

- [x] T-105-14 – `sortingOrderValueFor`; `toggleBuyMe()` loads details of a detail-pending photo (F-105-09, F-105-10).  
  _Verification commands:_  
  - `vp test`

- [x] T-105-15 – `AlbumPanel.vue` `setAsHeader` loads details of a detail-pending photo before reading its URL (F-105-09, S-105-12).  
  _Verification commands:_  
  - `vp run typecheck`

- [x] T-105-16 – `BulkAlbumEdit.vue` sort cells: "Default" entry and label, column patched with its order (F-105-10, S-105-14).  
  _Verification commands:_  
  - `vp run typecheck`

- [x] T-105-17 – `BulkEditFieldsDialog.vue`: "Default" entries, drawer timeline lists, orders start at Ascending, column sent with its order (F-105-10, S-105-15, S-105-16).  
  _Verification commands:_  
  - `vp run typecheck`

- [x] T-105-18 – Frontend gate (F-105-09, F-105-10).  
  _Verification commands:_  
  - `vp test`  
  - `vp check`  
  - `vp run typecheck`

- [x] T-105-19 – Browser check S-105-12, S-105-14 … 16.  
  _Verification commands:_  
  - `vp build`, Playwright scripts (scratchpad `pw/`)
  _Notes:_ `setheader.mjs`: one `Photos/details` request, then `Album::header`, hero shows the photo's `medium` URL. `bulk.mjs`: PATCH bodies `title`/`ASC`, `null`/`null`, order-only `DESC`; lists as S-105-16.

- [x] T-105-20 – Docs: drift gate rows, knowledge map, roadmap.

## Notes / TODOs
- `vp check --fix` rewrites files outside the change set (`EmbedCodeDialog.vue`); use `vp check` + `vp fmt <file>`.
