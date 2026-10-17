# Feature 097 Tasks – Open Album in New Tab

_Status: Complete_  
_Last updated: 2026-10-17_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

- [x] T-097-01 – Vitest wiring and failing tests (F-097-01, F-097-02, F-097-03, S-097-01, S-097-02, S-097-03, S-097-04, S-097-07).  
  _Intent:_ `test` block in `vite.config.ts`; `openAlbumInNewTab.test.ts`, v8 and v7 `contextMenu.test.ts`; confirmed red before code.  
  _Verification commands:_  
  - `vp test`

- [x] T-097-02 – Shared helper `resources/js/composables/album/openAlbumInNewTab.ts` (F-097-02, S-097-03, S-097-04).  
  _Intent:_ `albumHref(router, id)`, `openInNewTab(href)`; license header; v7 style.  
  _Verification commands:_  
  - `vp test resources/js/composables/album`  
  - `vp check --fix`  
  - `vp run typecheck`

- [x] T-097-03 – v8 `albumMenu()` first entry (F-097-01, F-097-03, S-097-01, S-097-02, S-097-07).  
  _Intent:_ open entry, conditional divider, actions; `useRouter()` in the composable.  
  _Verification commands:_  
  - `vp test resources/js/v8/composables/contextMenus`  
  - `vp check --fix`  
  - `vp run typecheck`

- [x] T-097-04 – v7 `albumMenu()` first entry (F-097-01, F-097-03, S-097-09).  
  _Intent:_ same shape with `pi pi-external-link`.  
  _Verification commands:_  
  - `vp test resources/js/composables/contextMenus`  
  - `vp check --fix`  
  - `vp run typecheck`

- [x] T-097-05 – `gallery.menus.open_in_new_tab` in 23 locales (N-097-02 of the NFR table: NFR-097-02).  
  _Intent:_ translated entry after `'merge_all'` in every `lang/<locale>/gallery.php`.  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`  
  - `php scripts/check_translations.php`  

- [x] T-097-06 – Browser check on a scratch instance (F-097-04, F-097-05, S-097-01, S-097-03, S-097-05, S-097-06, S-097-07, S-097-08, S-097-09, S-097-10).  
  _Intent:_ Playwright + Chromium against `php artisan serve` on a scratchpad SQLite database, v8 then v7: menu order (owner, guest, inside an album), entry → new page, middle click on grid tile and list row → new page, left click, two-album menu. Includes the v8 root `UContextMenu` guard on `selectableAlbums` (FR-097-06), found when the guest got no menu.  
  _Verification commands:_ scratchpad `097/pw/check.mjs` (results in the plan's drift gate): v8 12/12 + list 3/3, v7 flag off 12/12, v7 flag on 8/8 owner + list 3/3.

- [x] T-097-07 – Docs and drift gate.  
  _Intent:_ roadmap row, knowledge map, `_current-session.md`, spec status, drift gate report.  
  _Verification commands:_ `vp check`, `vp run typecheck`, `vp test`, `php artisan test --filter=LangTest`.

## Notes / TODOs

- `lang/` is outside the php-cs-fixer finder: running the fixer with `lang` as an explicit path rewrites 850 files (indentation), so the lang gate is `LangTest` and `scripts/check_translations.php` only.
- `vp check --fix` applies pre-existing lint autofixes outside this feature (`EmbedCodeDialog.vue` ×2, where dropping the `<\/script>` escape breaks the SFC, and `useSpotlightRemoteSearch.ts`); those were reverted, `vp check` reports 0 errors and 12 pre-existing warnings.
- Q-097-01 (both trees) and Q-097-02 (Vitest) resolved Option A by the owner on 2026-10-17; see [open-questions.md](open-questions.md).
