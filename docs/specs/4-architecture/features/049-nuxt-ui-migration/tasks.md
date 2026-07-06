# Feature 049 Tasks – Migration to Nuxt UI

_Status: Draft_
_Last updated: 2026-07-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.
>
> **Regenerated 2026-07-03 against ADR-0006/Q-049-04.** Every task below builds **new files under `resources/js/v8/**`**, using the equivalent path under `resources/js/v7/{views,components,menus}/**` (v7) purely as the behavioral/visual reference — v7 is **never edited** by this feature until the Phase 15 removal task. "Migrate X" means "build v8's version of X, wired into `resources/js/v8/router/routes.ts` via the shared `resources/js/router/paths.ts` manifest." Grep-based verification now checks for the **presence** of the new file under `resources/js/v8/...` (and its absence of any `primevue/*` import — structurally guaranteed, since v8 files are authored fresh) rather than the **absence** of PrimeVue from the v7 path. Directory names and file counts describe the v7 inventory being mirrored, not files edited in place. See `docs/specs/6-decisions/ADR-0006-nuxt-ui-dual-tree-toggle.md` for the full rationale.
>
> **Update 2026-07-03 (post route-parity):** with 46/46 routes covered, `resources/js/{views,components,menus}/**` and `resources/js/router/routes.ts`/`resources/js/style/preset.ts` were physically moved to `resources/js/v7/{views,components,menus}/**`, `resources/js/v7/router/routes.ts`, `resources/js/v7/style/preset.ts` (pure `git mv` + import-path rewrite, no logic changed — see ADR-0006's addendum). `resources/js/app.ts` and `resources/js/router/paths.ts` stay at the top level. Every occurrence of the old bare paths below (and in `spec.md`/`plan.md`) refers to this new `v7/`-prefixed location. T-049-43's "delete `resources/js/views/**`/`components/**`/`menus/**`" step is now simply `rm -rf resources/js/v7`.
>
> This feature builds a Nuxt UI twin of the 235 files that import PrimeVue in v7. Each task below groups a directory/component-family slice rather than a single file — see the file lists in `spec.md`'s Appendix for exact scope per component. Every task's verification includes a `grep` sweep confirming the targeted v8 file exists and is clean (or, on the final task in a phase, that the whole `resources/js/v8/` tree is clean for that component).

## Checklist

### Phase 0 — Foundation & toggle scaffolding

- [x] T-049-00 – Toggle & dual-bundle scaffolding (FR-049-22, S-049-01).
  _Intent:_ Add `nuxt_ui` flag to `config/features.php`; branch `vueapp.blade.php`'s `@vite([...])` on `Features::active('nuxt_ui')` between `app.ts`/`app.css` (v7) and a new `app-v8.ts`/`app-v8.css` (v8); extract `resources/js/router/paths.ts` (`{name, path, meta}` only) out of the existing `router/routes.ts`; create a minimal `resources/js/v8/router/routes.ts` consuming the same manifest with placeholder components; register both entries in `vite.config.ts`'s `laravel-vite-plugin` `input` array.
  _Verification commands:_
  - `npm run dev` (flag off — confirm v7 unchanged; flag on — confirm a blank v8 shell boots at every existing route path)
  - `npm run check`
  - `php artisan test` (touches `config/features.php`/blade only)
  _Notes:_ This is the only task in the feature that touches PHP/Blade. `resources/js/router/routes.ts` (v7) and `resources/js/v8/router/routes.ts` (v8) must have identical path/name lists — both derived from `paths.ts`.

- [x] T-049-01 – Install Nuxt UI in standalone Vue mode (v8 entry) (FR-049-01, S-049-01).
  _Intent:_ Add `@nuxt/ui`, register its Vite + Vue plugins on the `app-v8.ts` entry only, create `resources/js/v8/views/App.vue` wrapped in `<UApp>`, import Nuxt UI CSS in `app-v8.css`, add generated type declarations to `tsconfig.app.json`. `app.ts`/`app.css` (v7) are not touched.
  _Verification commands:_
  - `npm install`
  - `npm run dev` (flag on — manual boot check of the v8 shell, no console errors; flag off — confirm v7 still pixel-unchanged)
  - `npm run check` (both entries)
  _Notes:_ Confirm dependency addition with the user before running `npm install` if not already implicitly approved via Q-049-01/02 resolution.

- [x] T-049-02 – Theme parity: primary color, light/dark surface scales — v8 (FR-049-02, FR-049-20, S-049-02).
  _Intent:_ Configure Nuxt UI's theme tokens (scoped to `v8/`) to match `preset.ts` (`sky` primary, `slate` light surface, `zinc` dark surface); confirm dark-mode `.dark` class selector compatibility (shared by both bundles).
  _Verification commands:_
  - `npm run dev` (flag on — toggle dark mode, compare a throwaway `<UButton>` (v8) against `<Button>` (v7, separate tab))
  _Notes:_ `preset.ts` stays in the repo as the reference source of truth until T-049-43.

- [x] T-049-03 – Icon collection setup: `@iconify-json/prime` + `v8/components/icons/PiMiniIcon.vue` (FR-049-15 setup, S-049-08).
  _Intent:_ Add `@iconify-json/prime` dev dependency (shared); create `resources/js/v8/components/icons/PiMiniIcon.vue` rendering `<UIcon name="i-prime-...">`, mirroring v7's `resources/js/components/icons/PiMiniIcon.vue` API (untouched, still renders `<i class="pi pi-...">`).
  _Verification commands:_
  - `npm install`
  - `npm run dev` (flag on — spot-check icons once `v8/menus/LeftMenu.vue` exists, T-049-07a)
  - `npm run check`

### Phase 1 — New shared composables (v8-only)

- [x] T-049-04 – Build `useAppToast()` composable (FR-049-04 infra, DO-049-01).
  _Intent:_ `resources/js/v8/composables/useAppToast.ts` wrapping Nuxt UI's `useToast()` with the app's existing call shape (`severity/summary/detail/life`).
  _Verification commands:_
  - `npm run dev` (flag on — temporary call site, verify visual match to NFR-049-01)
  - `npm run check`

- [x] T-049-05 – Build `useConfirmDialog()` + `ConfirmModalHost.vue` (FR-049-05 infra, DO-049-02).
  _Intent:_ `resources/js/v8/composables/useConfirmDialog.ts` + `resources/js/v8/components/modals/ConfirmModalHost.vue` — Promise-based `confirm({...}): Promise<boolean>` backed by a singleton `<UModal>`.
  _Verification commands:_
  - `npm run dev` (flag on — temporary call site, verify accept/reject resolution)
  - `npm run check`

### Phase 2 — App shell (v8)

- [x] T-049-06 – Build `v8/views/App.vue` shell (FR-049-03, S-049-03, S-049-04).
  _Intent:_ Mount `<ConfirmModalHost/>`; Nuxt UI's implicit toast host (via `<UApp>`) covers `<Toast/>`'s role; wire routed content from `v8/router/routes.ts`.
  _Verification commands:_
  - `npm run dev` (flag on — trigger T-049-04/05's temporary call sites through the real v8 shell)
  - `npm run check`
  - `grep -c "primevue" resources/js/v8/views/App.vue` → expect `0`

- [x] T-049-07a – Build `v8/menus/LeftMenu.vue` structure: `USlideover` + `UNavigationMenu` (FR-049-12, S-049-06).
  _Intent:_ Build the drawer container and nav-item list, using v7's `menus/LeftMenu.vue` as the reference for nav items/order/routes.
  _Verification commands:_
  - `npm run dev` (flag on — open/close drawer, navigate every top-level nav item, confirm same paths as v7)

- [x] T-049-07b – Build `v8/menus/LeftMenu.vue` badges, logout button, pt/dt/ripple parity (FR-049-12, FR-049-06, S-049-06, S-049-09).
  _Intent:_ `OverlayBadge`→`UBadge`/`UChip`, `Button`→`UButton`; translate any v7 `:pt:`/`:dt=` spacing/color customization into `:ui=`/Tailwind classes on the v8 file (no ripple ever added — v8 is authored fresh).
  _Verification commands:_
  - `npm run dev` (flag on — badges/counts render correctly, no ripple by construction)
  - `npm run check`
  - `grep -c "primevue" resources/js/v8/menus/LeftMenu.vue` → expect `0`

### Phase 3 — Toast/Confirm build-out (v8, mirrors v7 call sites)

- [x] T-049-08 – Toast usage: composables (FR-049-04).
  _Intent:_ Build v8 twins (or a shared thin seam) for the 8 `.ts` files under `composables/album/`, `composables/checkout/`, `composables/photo/` that call `primevue/usetoast` in v7, using `useAppToast()`.
  _Verification commands:_
  - `npm run check`

- [x] T-049-09 – Toast usage: `v8/components/maintenance/` (FR-049-04).
  _Verification commands:_
  - `npm run dev` (flag on — trigger a maintenance action)

- [x] T-049-10 – Toast usage: `v8/components/gallery/`, `v8/components/forms/` (FR-049-04).
  _Verification commands:_
  - `npm run dev` (flag on)

- [x] T-049-11 – Toast usage: `v8/views/admin/`, `v8/views/webshop/`, remaining sweep (FR-049-04).
  _Verification commands:_
  - `npm run dev` (flag on)
  - `npm run check`
  - `grep -rl "primevue/usetoast" resources/js/v8` → expect empty
  _Notes:_ Structural check — v8 never imports `primevue/usetoast` by construction.

- [x] T-049-12 – `useConfirmDialog()` call sites (FR-049-05, S-049-04).
  _Intent:_ Build v8 twins of `views/RenamerRules.vue`, `views/admin/ContactMessages.vue`, `views/admin/UserGroups.vue`.
  _Verification commands:_
  - `npm run dev` (flag on — exercise all 3 confirm flows)
  - `grep -rl "primevue/useconfirm\|primevue/confirmdialog" resources/js/v8` → expect empty

### Phase 4 — Button build-out (154 files mirrored)

- [x] T-049-13 – Buttons: `v8/components/maintenance/` (FR-049-06).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-14 – Buttons: `v8/components/gallery/` (FR-049-06).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-15 – Buttons: `v8/components/forms/` (FR-049-06).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-16 – Buttons: `v8/components/headers/`, `v8/components/drawers/`, `v8/components/modals/`, `v8/components/renamer/`, `v8/components/faceRecog/` (FR-049-06).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-17 – Buttons: `v8/views/admin/`, `v8/views/webshop/` (FR-049-06).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-18 – Buttons: remaining views, `v8/components/statistics/`, `v8/menus/`, sweep (FR-049-06).
  _Verification commands:_
  - `npm run dev` (flag on)
  - `npm run check`
  - `grep -rl "primevue/button" resources/js/v8` → expect empty

### Phase 5 — Dialog build-out (55 files mirrored)

- [x] T-049-19 – Dialogs: `v8/components/forms/album/` — reference implementation (FR-049-07, S-049-05).
  _Intent:_ Establish the `v-model:open` (v8) binding against the same `ModalsState` flags `v-model:visible` (v7) uses.
  _Verification commands:_
  - `npm run dev` (flag on — exercise ≥3 album dialogs end to end: open, edit, save, close)
  _Notes:_ Document the store-binding pattern here for reuse: `<UModal v-model:open="modalsState.is_x_visible">` (same flag v7 binds via `visible`).

- [x] T-049-20 – Dialogs: `v8/components/forms/sharing/`, `v8/components/forms/users/`, `v8/components/forms/settings/` (FR-049-07).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-21 – Dialogs: `v8/components/renamer/`, `v8/components/faceRecog/`, `v8/components/modals/` (FR-049-07).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-22 – Dialogs: `v8/views/admin/`, `v8/views/webshop/`, remaining sweep (FR-049-07).
  _Verification commands:_
  - `npm run dev` (flag on)
  - `npm run check`
  - `grep -rl "primevue/dialog" resources/js/v8` → expect empty

### Phase 6 — Toolbar build-out (42 files, no direct equivalent, mirrored)

- [x] T-049-23 – Toolbar: `v8/components/headers/` — reference implementation (FR-049-08).
  _Intent:_ Build v8 twins of `AlbumHeader.vue`, `SearchHeader.vue`, `AlbumsHeader.vue`, `TimelineHeader.vue` using a composed flex-header pattern.
  _Verification commands:_
  - `npm run dev` (flag on — visual comparison of header layout against v7, light + dark)
  _Notes:_ Document the flex-header pattern here for reuse: `<div class="flex items-center justify-between">` with left/center/right groups replacing `#start`/`#center`/`#end` slots.

- [x] T-049-24 – Toolbar: remaining views/panels, sweep (FR-049-08).
  _Verification commands:_
  - `npm run dev` (flag on)
  - `npm run check`
  - `grep -rl "primevue/toolbar" resources/js/v8` → expect empty

### Phase 7 — Loading/progress primitives (v8)

- [x] T-049-25 – ProgressSpinner, ProgressBar, MeterGroup (FR-049-09).
  _Intent:_ Build custom `v8/components/Spinner.vue`; build v8 twins for 49 combined usages, concentrated in `components/maintenance/` and `components/statistics/`.
  _Verification commands:_
  - `npm run dev` (flag on — trigger a long-running maintenance action, view storage statistics meter)
  - `grep -rl "primevue/progressspinner\|primevue/progressbar\|primevue/metergroup" resources/js/v8` → expect empty

### Phase 8 — Layout/content primitives (v8)

- [x] T-049-26a – Card, Panel: `v8/components/maintenance/`, `v8/components/diagnostics/` (FR-049-10).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-26b – Card, Panel, Fieldset, Divider: remaining sweep (FR-049-10).
  _Verification commands:_
  - `npm run dev` (flag on)
  - `npm run check`
  - `grep -rl "primevue/card\|primevue/panel\|primevue/fieldset\|primevue/divider" resources/js/v8` → expect empty

### Phase 9 — Form primitives (v8)

- [x] T-049-27 – Build the 8 `v8/components/forms/basic/` wrapper components (FR-049-11).
  _Intent:_ Build v8 twins of `InputText`, `Textarea`, `Password`, `Fieldset`, `InputCurrency`, `InputPassword`, `TagsInput`, `PersonsInput` — keeping the same external prop/emit contract v7's wrappers expose.
  _Verification commands:_
  - `npm run dev` (flag on — Album Properties form or similarly form-heavy view)
  - `npm run check`

- [x] T-049-28 – Select, FloatLabel, Checkbox, ToggleSwitch: `v8/components/forms/album/`, `v8/components/forms/sharing/` (FR-049-11).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-29 – Select, FloatLabel, Checkbox, ToggleSwitch: `v8/components/forms/settings/`, `v8/components/forms/users/`, `v8/views/admin/`, `v8/views/webshop/` (FR-049-11).
  _Verification commands:_ `npm run dev` (flag on)

- [x] T-049-30 – AutoComplete, SelectButton, InputNumber, MultiSelect, Listbox, RadioButton, DatePicker, InputGroup family: sweep (FR-049-11).
  _Verification commands:_
  - `npm run dev` (flag on)
  - `npm run check`
  - `grep -rl "primevue/select\|primevue/floatlabel\|primevue/checkbox\|primevue/toggleswitch\|primevue/autocomplete\|primevue/selectbutton\|primevue/inputnumber\|primevue/multiselect\|primevue/listbox\|primevue/radiobutton\|primevue/datepicker\|primevue/inputgroup\|primevue/inputgroupaddon\|primevue/iconfield\|primevue/inputicon\|primevue/inputtext\|primevue/textarea\|primevue/password\|primevue/inputswitch" resources/js/v8` → expect empty

### Phase 10 — Navigation/menu build-out (remaining)

- [x] T-049-31 – ContextMenu: gallery right-click menus (FR-049-12).
  _Intent:_ 9 files including `TagPanel.vue`, `ResultPanel.vue`, `AlbumPanel.vue`.
  _Verification commands:_
  - `npm run dev` (flag on — right-click a photo/album/tag, verify every context-menu action)
  - `grep -rl "primevue/contextmenu" resources/js/v8` → expect empty
  _Notes:_ `composables/contextMenus/contextMenu.ts` (menu-item construction) is shared, not PrimeVue-coupled — unaffected by this task.

- [x] T-049-32 – Remaining Menu, Paginator, OverlayBadge usages (FR-049-12, FR-049-14).
  _Intent:_ `views/admin/Settings.vue`, `components/settings/AllSettings.vue` (Menu); pagination components (Paginator); remaining OverlayBadge outside `LeftMenu.vue`.
  _Verification commands:_
  - `npm run dev` (flag on)
  - `npm run check`
  - `grep -rl "primevue/menu\|primevue/paginator\|primevue/overlaybadge" resources/js/v8` → expect empty

### Phase 11 — DataTable build-out (10 files, structural rewrite, mirrored)

- [x] T-049-33 – DataTable: `v8/components/statistics/AlbumsTable.vue`, `v8/components/drawers/StatTable.vue`, `v8/components/modals/KeybindingsHelp.vue` (FR-049-13, S-049-07).
  _Verification commands:_
  - `npm run dev` (flag on — verify sort/pagination/data rendering matches v7 per table)
  - `npm run check`

- [x] T-049-34 – DataTable: `v8/views/admin/ContactMessages.vue`, `Purchasables.vue`, `Webhooks.vue`, `NsfwConfig.vue` (FR-049-13, S-049-07).
  _Verification commands:_
  - `npm run dev` (flag on — verify row actions — edit/delete/toggle — trigger correct handlers per table)
  - `npm run check`

- [x] T-049-35 – DataTable: `v8/views/admin/shop/PrintPixelSizesAdmin.vue`, `v8/views/webshop/PurchasablesList.vue`, `OrderList.vue`, sweep (FR-049-13, S-049-07).
  _Verification commands:_
  - `npm run dev` (flag on)
  - `npm run check`
  - `grep -rl "primevue/datatable\|primevue/column" resources/js/v8` → expect empty

### Phase 12 — Miscellaneous components (v8)

- [x] T-049-36 – Tag → UBadge, Message → UAlert (FR-049-14).
  _Verification commands:_ `npm run dev` (flag on); `grep -rl "primevue/tag\|primevue/message" resources/js/v8` → expect empty

- [x] T-049-37 – ScrollTop, VirtualScroller (FR-049-14).
  _Intent:_ Custom scroll-to-top component; `VirtualScroller` (2 gallery-panel files) → `@tanstack/vue-virtual` or custom, performance-sensitive.
  _Verification commands:_
  - `npm run dev` (flag on — scroll performance check on a large album/gallery view)
  - `grep -rl "primevue/scrolltop\|primevue/virtualscroller" resources/js/v8` → expect empty
  _Notes:_ If `@tanstack/vue-virtual` is added, confirm dependency approval with the user first (AGENTS.md rule). **Status 2026-07-03:** custom `v8/components/ScrollTop.vue` built and wired into all consumers. `VirtualScroller` → `<UScrollArea :virtualize="...">` (Nuxt UI's own component, backed by `@tanstack/vue-virtual` already bundled as `@nuxt/ui`'s own transitive dependency — no new top-level dependency added, confirmed with the user). `views/FixTree.vue` and `views/DuplicatesFinder.vue` (+ `FixTreeLine.vue`, `mini/LeftWarn.vue`, `mini/RightWarn.vue`, `DuplicateLine.vue`) built on this pattern.

- [x] T-049-38 – Timeline, Tabs family, Stepper family, Inplace, SpeedDial, ScrollPanel: sweep (FR-049-14).
  _Verification commands:_
  - `npm run dev` (flag on — checkout flow for Stepper, Timeline view, Tabs in NsfwConfig/Albums)
  - `npm run check`
  - `grep -rl "primevue/timeline\|primevue/tabs\|primevue/tablist\|primevue/tab\|primevue/tabpanels\|primevue/tabpanel\|primevue/stepper\|primevue/steppanels\|primevue/steppanel\|primevue/steplist\|primevue/step\|primevue/inplace\|primevue/speeddial\|primevue/scrollpanel" resources/js/v8` → expect empty

### Phase 13 — Pass-through verification (structural, not a cleanup)

- [x] T-049-39 – Verify no `pt`/`dt` syntax leaked into the v8 tree (FR-049-16).
  _Intent:_ Structural verification pass — v8 files are authored fresh against Nuxt UI (no `pt`/`dt` APIs exist there), so a hit here means an earlier task copy-pasted v7 syntax by mistake, not a first-touch migration.
  _Verification commands:_
  - `grep -rl ":pt:\|:pt=\|:dt=" resources/js/v8` → expect empty

### Phase 14 — Directive verification (structural, not a cleanup)

- [x] T-049-40 – Verify no ripple/focustrap directive usage in v8; confirm `<UTooltip>` parity (FR-049-17, S-049-09, S-049-14).
  _Intent:_ `app-v8.ts` never registers `ripple`/`focustrap` (Nuxt UI has no equivalent; Reka UI traps focus internally) — structural, not a removal step. Confirm every v7 `v-tooltip` site has a v8 twin using `<UTooltip>` as a wrapping component.
  _Verification commands:_
  - `npm run dev` (flag on — no ripple on any v8 button click; Tab into a v8 modal traps focus correctly; tooltips still show on hover)
  - `npm run check`
  - `grep -rl "v-ripple\|v-focustrap\|v-tooltip\|primevue" resources/js/v8` → expect empty

### Phase 15 — Coverage gate, cutover, dependency removal & documentation

- [ ] T-049-41 – Route-parity coverage gate (FR-049-23, S-049-10).
  _Intent:_ Cross-check every `{name, path}` entry in `resources/js/router/paths.ts` against `resources/js/v8/router/routes.ts`; produce and check off a coverage checklist (one row per route), each verified manually with the flag on, in both light and dark mode.
  _Verification commands:_
  - `npm run dev` (flag on, full route walk)
  - `npm run check`
  _Notes:_ Hard precondition to T-049-42 — no route may fall back to a v7 component or render blank. **Status 2026-07-03:** 46/46 routes in `paths.ts` now have a real `v8/views/**` component wired in `v8/router/routes.ts` (verified by script diff) and structurally smoke-tested headlessly (build + render, only expected backend-404 console noise). Manual `npm run dev` light/dark walk of all 46 routes with a live backend has NOT been performed — that pass, plus the checklist artifact itself, is still open before this task can be checked off.

- [ ] T-049-42 – Cutover (FR-049-24, S-049-11).
  _Intent:_ Enable `nuxt_ui` for real traffic (config/env change only, no app redeploy needed beyond already-shipped code); run the full manual smoke test (same scope as T-049-45) against the now-live v8 bundle; rehearse rollback by flipping the flag back to `false` and confirming v7 is restored exactly.
  _Verification commands:_
  - `npm run build`
  - `npm run build:embed`
  - Manual smoke test (flag on, real traffic)
  - Rollback rehearsal (flag back to `false`, confirm v7 exact restoration)
  _Notes:_ Leave the flag in place for a deliberate observation window before T-049-43 proceeds — no fixed duration specified (see plan.md Follow-ups); use judgment or the environment's normal release-observation practice.

- [ ] T-049-43 – Remove PrimeVue dependencies, v7 tree, and the flag (FR-049-18, S-049-12).
  _Intent:_ Hard completion gate — delete `resources/js/views/**`, `components/**`, `menus/**` (v7), `resources/js/app.ts`, `resources/js/style/preset.ts`; remove the `nuxt_ui` flag from `config/features.php` and collapse `vueapp.blade.php`'s `@if`/`@else` to a single unconditional `@vite([...])` pointing at `app-v8.ts`; remove `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons` from `package.json`; remove the `tailwindcss-primeui` Tailwind plugin line.
  _Verification commands:_
  - `grep -rl "primevue\|@primeuix" resources/js` → expect empty (blocking precondition, checked before starting this task)
  - `npm install`
  - `npm run build`
  - `npm run build:embed` (compare `public/embed/lychee-embed.js`/`.css` size against pre-migration baseline)
  - `npm run check`
  _Notes:_ Blocked until T-049-42's cutover has been stable for the observation window — no partial removal while the flag still exists as an active rollback path.

- [ ] T-049-44 – Update coding-conventions.md and knowledge-map.md (FR-049-19).
  _Intent:_ Replace PrimeVue references with Nuxt UI; document `useAppToast()`/`useConfirmDialog()` as new seams; note the former `v8/` tree's structure is now simply the canonical `resources/js/` structure.
  _Verification commands:_ Manual review of both diffs against spec.md's FR list.

- [ ] T-049-45 – Full quality gate and final manual smoke test (S-049-01..14).
  _Intent:_ Final sign-off across every major view/flow in both light and dark mode.
  _Verification commands:_
  - `npm run format`
  - `npm run check`
  - `npm run build`
  - `npm run build:embed`
  _Notes:_ Smoke-test coverage: Home, Album, Photo detail, Search, Upload, Settings, Admin Dashboard + all admin sub-pages, Webshop checkout, Sharing, Face recognition, People. Move the roadmap entry to Completed once this task is checked off.

## Notes / TODOs

- **Reading the checklist:** every task builds inside `resources/js/v8/**`; `resources/js/{views,components,menus}/**` (v7) is the reference implementation and is not edited by any task except T-049-43. Grep commands scoped to `resources/js/v8` check for presence-and-cleanliness of the new file, not absence from v7 (v7 keeps importing PrimeVue by design until T-049-43).
- **Drift-gate tracking:** after each phase boundary, run `grep -rl "from \"primevue/" resources/js/v8 | wc -l` and record the count here — for this feature the meaningful drift signal is **unexpected PrimeVue imports appearing inside `resources/js/v8`** (should always be zero), not a shrinking count in `resources/js/views` (which stays constant until T-049-43 by design). See plan.md's Implementation Drift Gate.
  - After Phase 2 (T-049-07b): `resources/js/v8` should contain a working shell + LeftMenu, zero primevue imports.
  - After Phase 4 (T-049-18): `grep -rl "primevue/button" resources/js/v8` must be empty.
  - After Phase 5 (T-049-22): `grep -rl "primevue/dialog" resources/js/v8` must be empty.
  - After Phase 15 (T-049-41): every route in `router/paths.ts` has a working `v8/views/**` component (coverage gate) — this is the one drift check that matters for the whole v7 surface, since it's the cutover precondition.
  - After Phase 15 (T-049-43): `grep -rl "primevue\|@primeuix" resources/js` (repo-wide, no `/v8` scope) must be empty — the only point in the feature where v7 itself is checked.
- **Dependency approvals needed before implementation starts:** `@nuxt/ui` (T-049-01), `@iconify-json/prime` (T-049-03), optionally `@tanstack/vue-virtual` (T-049-37) — confirm with the user per AGENTS.md's dependency-approval rule before running `npm install` for each.
- No frontend automated test suite exists (confirmed 2026-07-02) — every task's verification relies on `npm run check` (type-checking only, covering both `app.ts` and `app-v8.ts` entries) plus manual browser verification against the `v8` tree with the flag toggled on. This is a known limitation (NFR-049-03), not a gap introduced by this feature.
- If a task's directory turns out to exceed ~90 minutes of actual build-out effort once started, split it into `<task-id>a`/`<task-id>b` sub-tasks rather than letting it run long, consistent with AGENTS.md's increment-sizing guardrail (precedent: T-049-07a/07b, T-049-26a/26b).
- **Cutover observation window (T-049-42 → T-049-43):** no fixed duration is specified in plan.md (flagged as an open Follow-up) — use judgment or the environment's normal release-observation practice before proceeding to removal.

---

*Last updated: 2026-07-03*
