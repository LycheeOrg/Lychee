# Feature 049 Tasks – Migration to Nuxt UI

_Status: Draft_
_Last updated: 2026-07-02_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.
>
> This feature migrates 235 files off PrimeVue. Each task below groups a directory/component-family slice rather than a single file — see the file lists in `spec.md`'s Appendix for exact scope per component. Every task's verification includes a `grep` sweep to confirm the targeted PrimeVue import is gone from the targeted directory (or, on the final task in a phase, repo-wide).

## Checklist

### Phase 0 — Foundation

- [ ] T-049-01 – Install Nuxt UI in standalone Vue mode (FR-049-01, S-049-01).
  _Intent:_ Add `@nuxt/ui`, register its Vite + Vue plugins, wrap `App.vue` root in `<UApp>`, import Nuxt UI CSS, add generated type declarations to `tsconfig.app.json`.
  _Verification commands:_
  - `npm install`
  - `npm run dev` (manual boot check — no console errors, no visual change)
  - `npm run check`
  _Notes:_ Confirm dependency addition with the user before running `npm install` if not already implicitly approved via Q-049-01/02 resolution.

- [ ] T-049-02 – Theme parity: primary color, light/dark surface scales (FR-049-02, FR-049-20, S-049-02).
  _Intent:_ Configure Nuxt UI's theme tokens to match `preset.ts` (`sky` primary, `slate` light surface, `zinc` dark surface); confirm dark-mode `.dark` class selector compatibility.
  _Verification commands:_
  - `npm run dev` (toggle dark mode in Settings, compare a throwaway `<UButton>` against `<Button>`)
  _Notes:_ `preset.ts` stays in the repo as the reference source of truth until T-049-41.

- [ ] T-049-03 – Icon collection setup: `@iconify-json/prime` + `PiMiniIcon.vue` chokepoint (FR-049-15 setup, S-049-08).
  _Intent:_ Add `@iconify-json/prime` dev dependency; repoint `PiMiniIcon.vue` to `<UIcon name="i-prime-...">`.
  _Verification commands:_
  - `npm install`
  - `npm run dev` (spot-check `LeftMenu.vue` nav icons)
  - `npm run check`

### Phase 1 — New shared composables

- [ ] T-049-04 – Build `useAppToast()` composable (FR-049-04 infra, DO-049-01).
  _Intent:_ Wrap Nuxt UI's `useToast()` with the app's existing call shape (`severity/summary/detail/life`).
  _Verification commands:_
  - `npm run dev` (temporary call site, verify visual match to NFR-049-01)
  - `npm run check`

- [ ] T-049-05 – Build `useConfirmDialog()` + `ConfirmModalHost.vue` (FR-049-05 infra, DO-049-02).
  _Intent:_ Promise-based `confirm({...}): Promise<boolean>` backed by a singleton `<UModal>`.
  _Verification commands:_
  - `npm run dev` (temporary call site, verify accept/reject resolution)
  - `npm run check`

### Phase 2 — App shell

- [ ] T-049-06 – Migrate `views/App.vue` shell (FR-049-03, S-049-03, S-049-04).
  _Intent:_ Replace `<Toast/>`/`<ConfirmDialog/>` with Nuxt UI's toast host (via `<UApp>`) and `<ConfirmModalHost/>`.
  _Verification commands:_
  - `npm run dev` (trigger I4/I5 temporary call sites through the real shell)
  - `npm run check`
  - `grep -c "primevue/toast\|primevue/confirmdialog" resources/js/views/App.vue` → expect `0`

- [ ] T-049-07a – Migrate `menus/LeftMenu.vue` structure: Drawer → USlideover, Menu → UNavigationMenu (FR-049-12, S-049-06).
  _Intent:_ Rebuild drawer container and nav-item list.
  _Verification commands:_
  - `npm run dev` (open/close drawer, navigate every top-level nav item)

- [ ] T-049-07b – Migrate `menus/LeftMenu.vue` badges, logout button, pt/dt/ripple cleanup (FR-049-12, FR-049-06, FR-049-16, FR-049-17, S-049-06, S-049-09).
  _Intent:_ `OverlayBadge`→`UBadge`/`UChip`, `Button`→`UButton`, remove `:pt:`/`:dt=`/`v-ripple`.
  _Verification commands:_
  - `npm run dev` (badges/counts render correctly, no ripple on click)
  - `npm run check`
  - `grep -c "primevue" resources/js/menus/LeftMenu.vue` → expect `0`

### Phase 3 — Toast/Confirm call-site migration

- [ ] T-049-08 – Migrate `useToast()` call sites: composables (FR-049-04).
  _Intent:_ 8 `.ts` files under `composables/album/`, `composables/checkout/`, `composables/photo/`.
  _Verification commands:_
  - `npm run check`
  - `grep -rl "primevue/usetoast" resources/js/composables` → expect empty

- [ ] T-049-09 – Migrate `useToast()` call sites: `components/maintenance/` (FR-049-04).
  _Verification commands:_
  - `npm run dev` (trigger a maintenance action)
  - `grep -rl "primevue/usetoast" resources/js/components/maintenance` → expect empty

- [ ] T-049-10 – Migrate `useToast()` call sites: `components/gallery/`, `components/forms/` (FR-049-04).
  _Verification commands:_
  - `npm run dev`
  - `grep -rl "primevue/usetoast" resources/js/components/gallery resources/js/components/forms` → expect empty

- [ ] T-049-11 – Migrate `useToast()` call sites: `views/admin/`, `views/webshop/`, remaining sweep to zero (FR-049-04).
  _Verification commands:_
  - `npm run dev`
  - `npm run check`
  - `grep -rl "primevue/usetoast" resources/js` → expect empty
  _Notes:_ Remove `ToastService` plugin registration from `app.ts` once this sweep is clean; keep `primevue/config` (`PrimeVue` plugin itself) registered until T-049-41 since other components still depend on it.

- [ ] T-049-12 – Migrate `useConfirm()` call sites (FR-049-05, S-049-04).
  _Intent:_ `views/RenamerRules.vue`, `views/admin/ContactMessages.vue`, `views/admin/UserGroups.vue`.
  _Verification commands:_
  - `npm run dev` (exercise all 3 confirm flows)
  - `grep -rl "primevue/useconfirm\|primevue/confirmdialog" resources/js` → expect empty
  _Notes:_ Remove `ConfirmationService` plugin registration from `app.ts`.

### Phase 4 — Button migration (154 files)

- [ ] T-049-13 – Buttons: `components/maintenance/` (FR-049-06).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/button" resources/js/components/maintenance` → expect empty

- [ ] T-049-14 – Buttons: `components/gallery/` (FR-049-06).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/button" resources/js/components/gallery` → expect empty

- [ ] T-049-15 – Buttons: `components/forms/` (FR-049-06).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/button" resources/js/components/forms` → expect empty

- [ ] T-049-16 – Buttons: `components/headers/`, `components/drawers/`, `components/modals/`, `components/renamer/`, `components/faceRecog/` (FR-049-06).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/button" resources/js/components/headers resources/js/components/drawers resources/js/components/modals resources/js/components/renamer resources/js/components/faceRecog` → expect empty

- [ ] T-049-17 – Buttons: `views/admin/`, `views/webshop/` (FR-049-06).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/button" resources/js/views/admin resources/js/views/webshop` → expect empty

- [ ] T-049-18 – Buttons: remaining views, `components/statistics/`, `menus/`, sweep to zero (FR-049-06).
  _Verification commands:_
  - `npm run dev`
  - `npm run check`
  - `grep -rl "primevue/button" resources/js` → expect empty

### Phase 5 — Dialog migration (55 files)

- [ ] T-049-19 – Dialogs: `components/forms/album/` — reference implementation (FR-049-07, S-049-05).
  _Intent:_ Establish the `v-model:visible`→`v-model:open` pattern against `ModalsState` store flags.
  _Verification commands:_
  - `npm run dev` (exercise ≥3 album dialogs end to end: open, edit, save, close)
  - `grep -rl "primevue/dialog" resources/js/components/forms/album` → expect empty
  _Notes:_ Document the store-binding pattern here for reuse in subsequent Dialog tasks: `<UModal v-model:open="modalsState.is_x_visible">`.

- [ ] T-049-20 – Dialogs: `components/forms/sharing/`, `components/forms/users/`, `components/forms/settings/` (FR-049-07).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/dialog" resources/js/components/forms/sharing resources/js/components/forms/users resources/js/components/forms/settings` → expect empty

- [ ] T-049-21 – Dialogs: `components/renamer/`, `components/faceRecog/`, `components/modals/` (FR-049-07).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/dialog" resources/js/components/renamer resources/js/components/faceRecog resources/js/components/modals` → expect empty

- [ ] T-049-22 – Dialogs: `views/admin/`, `views/webshop/`, remaining sweep to zero (FR-049-07).
  _Verification commands:_
  - `npm run dev`
  - `npm run check`
  - `grep -rl "primevue/dialog" resources/js` → expect empty

### Phase 6 — Toolbar migration (42 files, no direct equivalent)

- [ ] T-049-23 – Toolbar: `components/headers/` — reference implementation (FR-049-08).
  _Intent:_ Migrate `AlbumHeader.vue`, `SearchHeader.vue`, `AlbumsHeader.vue`, `TimelineHeader.vue` to a composed flex-header pattern.
  _Verification commands:_
  - `npm run dev` (visual comparison of header layout, light + dark)
  - `grep -rl "primevue/toolbar" resources/js/components/headers` → expect empty
  _Notes:_ Document the flex-header pattern here for reuse: `<div class="flex items-center justify-between">` with left/center/right groups replacing `#start`/`#center`/`#end` slots.

- [ ] T-049-24 – Toolbar: remaining views/panels, sweep to zero (FR-049-08).
  _Verification commands:_
  - `npm run dev`
  - `npm run check`
  - `grep -rl "primevue/toolbar" resources/js` → expect empty

### Phase 7 — Loading/progress primitives

- [ ] T-049-25 – ProgressSpinner, ProgressBar, MeterGroup (FR-049-09).
  _Intent:_ Build custom `Spinner.vue`; migrate 49 combined usages, concentrated in `components/maintenance/` and `components/statistics/`.
  _Verification commands:_
  - `npm run dev` (trigger a long-running maintenance action, view storage statistics meter)
  - `grep -rl "primevue/progressspinner\|primevue/progressbar\|primevue/metergroup" resources/js` → expect empty

### Phase 8 — Layout/content primitives

- [ ] T-049-26a – Card, Panel: `components/maintenance/`, `components/diagnostics/` (FR-049-10).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/card\|primevue/panel" resources/js/components/maintenance resources/js/components/diagnostics` → expect empty

- [ ] T-049-26b – Card, Panel, Fieldset, Divider: remaining sweep to zero (FR-049-10).
  _Verification commands:_
  - `npm run dev`
  - `npm run check`
  - `grep -rl "primevue/card\|primevue/panel\|primevue/fieldset\|primevue/divider" resources/js` → expect empty

### Phase 9 — Form primitives

- [ ] T-049-27 – Rework `components/forms/basic/` wrapper components (FR-049-11).
  _Intent:_ Swap internals of 8 wrappers (`InputText`, `Textarea`, `Password`, `Fieldset`, `InputCurrency`, `InputPassword`, `TagsInput`, `PersonsInput`) — external contract unchanged.
  _Verification commands:_
  - `npm run dev` (Album Properties form or similarly form-heavy view)
  - `npm run check`

- [ ] T-049-28 – Select, FloatLabel, Checkbox, ToggleSwitch: `components/forms/album/`, `components/forms/sharing/` (FR-049-11).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/select\|primevue/floatlabel\|primevue/checkbox\|primevue/toggleswitch" resources/js/components/forms/album resources/js/components/forms/sharing` → expect empty

- [ ] T-049-29 – Select, FloatLabel, Checkbox, ToggleSwitch: `components/forms/settings/`, `components/forms/users/`, `views/admin/`, `views/webshop/` (FR-049-11).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/select\|primevue/floatlabel\|primevue/checkbox\|primevue/toggleswitch" resources/js/components/forms/settings resources/js/components/forms/users resources/js/views/admin resources/js/views/webshop` → expect empty

- [ ] T-049-30 – AutoComplete, SelectButton, InputNumber, MultiSelect, Listbox, RadioButton, DatePicker, InputGroup family: sweep to zero (FR-049-11).
  _Verification commands:_
  - `npm run dev`
  - `npm run check`
  - `grep -rl "primevue/select\|primevue/floatlabel\|primevue/checkbox\|primevue/toggleswitch\|primevue/autocomplete\|primevue/selectbutton\|primevue/inputnumber\|primevue/multiselect\|primevue/listbox\|primevue/radiobutton\|primevue/datepicker\|primevue/inputgroup\|primevue/inputgroupaddon\|primevue/iconfield\|primevue/inputicon\|primevue/inputtext\|primevue/textarea\|primevue/password\|primevue/inputswitch" resources/js` → expect empty

### Phase 10 — Navigation/menu (remaining)

- [ ] T-049-31 – ContextMenu: gallery right-click menus (FR-049-12).
  _Intent:_ 9 files including `TagPanel.vue`, `ResultPanel.vue`, `AlbumPanel.vue`.
  _Verification commands:_
  - `npm run dev` (right-click a photo/album/tag, verify every context-menu action)
  - `grep -rl "primevue/contextmenu" resources/js` → expect empty

- [ ] T-049-32 – Remaining Menu, Paginator, OverlayBadge usages (FR-049-12, FR-049-14).
  _Intent:_ `views/admin/Settings.vue`, `components/settings/AllSettings.vue` (Menu); pagination components (Paginator); remaining OverlayBadge outside `LeftMenu.vue`.
  _Verification commands:_
  - `npm run dev`
  - `npm run check`
  - `grep -rl "primevue/menu\|primevue/paginator\|primevue/overlaybadge" resources/js` → expect empty

### Phase 11 — DataTable migration (10 files, structural rewrite)

- [ ] T-049-33 – DataTable: `components/statistics/AlbumsTable.vue`, `components/drawers/StatTable.vue`, `components/modals/KeybindingsHelp.vue` (FR-049-13, S-049-07).
  _Verification commands:_
  - `npm run dev` (verify sort/pagination/data rendering per table)
  - `npm run check`

- [ ] T-049-34 – DataTable: `views/admin/ContactMessages.vue`, `Purchasables.vue`, `Webhooks.vue`, `NsfwConfig.vue` (FR-049-13, S-049-07).
  _Verification commands:_
  - `npm run dev` (verify row actions — edit/delete/toggle — trigger correct handlers per table)
  - `npm run check`

- [ ] T-049-35 – DataTable: `views/admin/shop/PrintPixelSizesAdmin.vue`, `views/webshop/PurchasablesList.vue`, `OrderList.vue`, sweep to zero (FR-049-13, S-049-07).
  _Verification commands:_
  - `npm run dev`
  - `npm run check`
  - `grep -rl "primevue/datatable\|primevue/column" resources/js` → expect empty

### Phase 12 — Miscellaneous components

- [ ] T-049-36 – Tag → UBadge, Message → UAlert (FR-049-14).
  _Verification commands:_ `npm run dev`; `grep -rl "primevue/tag\|primevue/message" resources/js` → expect empty

- [ ] T-049-37 – ScrollTop, VirtualScroller (FR-049-14).
  _Intent:_ Custom scroll-to-top component; `VirtualScroller` (2 gallery-panel files) → `@tanstack/vue-virtual` or custom, performance-sensitive.
  _Verification commands:_
  - `npm run dev` (scroll performance check on a large album/gallery view)
  - `grep -rl "primevue/scrolltop\|primevue/virtualscroller" resources/js` → expect empty
  _Notes:_ If `@tanstack/vue-virtual` is added, confirm dependency approval with the user first (AGENTS.md rule).

- [ ] T-049-38 – Timeline, Tabs family, Stepper family, Inplace, SpeedDial, ScrollPanel: sweep to zero (FR-049-14).
  _Verification commands:_
  - `npm run dev` (checkout flow for Stepper, Timeline view, Tabs in NsfwConfig/Albums)
  - `npm run check`
  - `grep -rl "primevue/timeline\|primevue/tabs\|primevue/tablist\|primevue/tab\|primevue/tabpanels\|primevue/tabpanel\|primevue/stepper\|primevue/steppanels\|primevue/steppanel\|primevue/steplist\|primevue/step\|primevue/inplace\|primevue/speeddial\|primevue/scrollpanel" resources/js` → expect empty

### Phase 13 — Pass-through cleanup

- [ ] T-049-39 – Verify and clean up remaining `pt`/`dt` overrides (FR-049-16).
  _Intent:_ Verification sweep — most `:pt:`/`:pt=`/`:dt=` usages are removed incidentally by their host component's migration task above.
  _Verification commands:_
  - `grep -rl ":pt:\|:pt=\|:dt=" resources/js` → expect empty

### Phase 14 — Directive cleanup

- [ ] T-049-40 – Remove ripple, focustrap; migrate tooltip to `<UTooltip>` (FR-049-17, S-049-09, S-049-12).
  _Verification commands:_
  - `npm run dev` (no ripple on any button click; Tab into a migrated modal traps focus correctly; tooltips still show on hover)
  - `npm run check`
  - `grep -rl "v-ripple\|v-focustrap\|v-tooltip\|primevue/ripple\|primevue/focustrap\|primevue/tooltip" resources/js` → expect empty

### Phase 15 — Dependency removal & documentation

- [ ] T-049-41 – Remove PrimeVue dependencies (FR-049-18, S-049-10, S-049-11).
  _Intent:_ Hard completion gate — remove `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons` from `package.json`; remove the `tailwindcss-primeui` Tailwind plugin line; delete `resources/js/style/preset.ts`.
  _Verification commands:_
  - `grep -rl "primevue\|@primeuix" resources/js` → expect empty (blocking precondition)
  - `npm install`
  - `npm run build`
  - `npm run build:embed` (compare `public/embed/lychee-embed.js`/`.css` size against pre-migration baseline)
  - `npm run check`

- [ ] T-049-42 – Update coding-conventions.md and knowledge-map.md (FR-049-19).
  _Intent:_ Replace PrimeVue references with Nuxt UI; document `useAppToast()`/`useConfirmDialog()` as new seams.
  _Verification commands:_ Manual review of both diffs against spec.md's FR list.

- [ ] T-049-43 – Full quality gate and final manual smoke test (S-049-01..12).
  _Intent:_ Final sign-off across every major view/flow in both light and dark mode.
  _Verification commands:_
  - `npm run format`
  - `npm run check`
  - `npm run build`
  - `npm run build:embed`
  _Notes:_ Smoke-test coverage: Home, Album, Photo detail, Search, Upload, Settings, Admin Dashboard + all admin sub-pages, Webshop checkout, Sharing, Face recognition, People. Move the roadmap entry to Completed once this task is checked off.

## Notes / TODOs

- **Drift-gate tracking:** after each phase boundary, run `grep -rl "from \"primevue/" resources/js | wc -l` and record the count here to catch missed files or new PrimeVue imports introduced by unrelated concurrent work (see plan.md's Implementation Drift Gate). Baseline at feature start: **235 files**.
  - After Phase 2 (T-049-07b): expected ~233 (shell files only touched)
  - After Phase 3 (T-049-12): expected count drops by ~119+3 unique files touching `usetoast`/`useconfirm` (many overlap with files also using other components, so this is a lower bound, not an exact file-count delta)
  - After Phase 4 (T-049-18): `primevue/button` grep must be empty
  - After Phase 5 (T-049-22): `primevue/dialog` grep must be empty
  - After Phase 15 (T-049-41): overall grep must be empty (hard gate)
- **Dependency approvals needed before implementation starts:** `@nuxt/ui` (T-049-01), `@iconify-json/prime` (T-049-03), optionally `@tanstack/vue-virtual` (T-049-37) — confirm with the user per AGENTS.md's dependency-approval rule before running `npm install` for each.
- No frontend automated test suite exists (confirmed 2026-07-02) — every task's verification relies on `npm run check` (type-checking only) plus manual browser verification. This is a known limitation (NFR-049-03), not a gap introduced by this feature.
- If a task's directory turns out to exceed ~90 minutes of actual migration effort once started (e.g. a directory has more structurally-different `pt`/`dt` overrides than anticipated), split it into `<task-id>a`/`<task-id>b` sub-tasks rather than letting it run long, consistent with AGENTS.md's increment-sizing guardrail.

---

*Last updated: 2026-07-02*
