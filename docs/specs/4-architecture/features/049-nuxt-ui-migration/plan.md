# Feature Plan 049 – Migration to Nuxt UI

_Linked specification:_ `docs/specs/4-architecture/features/049-nuxt-ui-migration/spec.md`
_Status:_ Draft
_Last updated:_ 2026-07-02

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Replace PrimeVue with Nuxt UI across the entire main app bundle (`resources/js/app.ts` and everything it transitively imports) with zero visual regression (same colors, icons, layout — Q-049-02), zero behavioral regression (same dialog/toast/confirm/navigation interactions), and a clean dependency removal at the end (FR-049-18). Success is measured by:
- `grep -rl "primevue\|@primeuix" resources/js` returning zero files.
- `package.json` containing no `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, or `primeicons` entries.
- `npm run check`, `npm run format`, `npm run build`, and `npm run build:embed` all passing.
- Manual browser verification (light + dark mode) confirming no visual/behavioral regression across every migrated view.
- `docs/specs/3-reference/coding-conventions.md` and `docs/specs/4-architecture/knowledge-map.md` no longer referencing PrimeVue.

## Scope Alignment

- **In scope:** Every file under `resources/js/` reachable from `app.ts` that imports from `primevue/*` or `@primeuix/*` (235 files at spec time), the theme preset (`resources/js/style/preset.ts`), Tailwind PrimeUI utility classes (197 files), pass-through/design-token overrides (36-42 files), icon migration (139 files), directive removal (ripple/focustrap/tooltip), and final dependency removal.
- **Out of scope:** `resources/js/embed/**` (zero PrimeVue coupling, explicitly verified untouched per NFR-049-04/S-049-11), introducing a frontend automated test suite, any visual/design redesign beyond dropping the ripple effect, migrating to the full Nuxt meta-framework.

## Dependencies & Interfaces

- **New npm dependencies:** `@nuxt/ui` (+ its transitive Reka UI / Tailwind Variants deps), `@iconify-json/prime` (dev dependency). Per AGENTS.md, adding dependencies requires explicit user approval — confirmed via the resolution of Q-049-01/02 (user selected the options that require these packages); reconfirm the exact package names/versions with the user immediately before I1 if not already implicitly approved.
- **Removed npm dependencies (at completion, FR-049-18):** `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons`.
- **Existing infrastructure reused unchanged:** Vue Router, Pinia (+ `pinia-plugin-persistedstate`), `vue-i18n`/`laravel-vue-i18n`, Tailwind CSS v4, Vite, `axios`/`axios-cache-interceptor`.
- **Existing architectural seams leveraged:** `resources/js/stores/ModalsState.ts` (dialog-visibility flags — pattern preserved, only the bound prop name changes from `visible` to `open`), `resources/js/components/icons/PiMiniIcon.vue` (icon chokepoint), the 8 wrapper components in `resources/js/components/forms/basic/`.
- **New architectural seams introduced:** `useAppToast()` composable, `useConfirmDialog()` composable + `ConfirmModalHost.vue`.

## Assumptions & Risks

- **Assumptions:**
  - Nuxt UI v3's standalone Vue mode (Vite plugin + Vue plugin, confirmed via `ui.nuxt.com/getting-started/installation/vue`) is stable enough for production use outside the Nuxt framework.
  - `@iconify-json/prime` (confirmed published on npm, v1.2.5) covers all 107 distinct icon names currently used via `pi pi-*`; any gap is handled case-by-case during I3/the icon sweep increments.
  - The project's Tailwind CSS v4 setup (`@tailwindcss/vite`) is compatible with Nuxt UI's own Vite plugin without ordering conflicts — verified in I1.
  - No frontend automated tests exist today (confirmed) and none are added by this feature — verification is manual per increment.
- **Risks / Mitigations:**
  - **Risk:** 235-file scope makes partial completion likely to stall across sessions, leaving the app in a permanently mixed-library state. **Mitigation:** FR-049-18/NFR-049-07 make dependency removal a hard completion gate; the roadmap entry stays "In Progress" (not moved to Completed) until it's satisfied; increments are ordered so each layer (shell → toast/confirm → high-frequency components → niche components → cleanup) leaves the app in a working, visually-correct state at every checkpoint.
  - **Risk:** Nuxt UI's `Toolbar`-equivalent gap (FR-049-08) requires a bespoke pattern across 42 files — inconsistent implementations could drift visually. **Mitigation:** I23 establishes one reference implementation (a small shared pattern/snippet, not a new component abstraction per AGENTS.md's anti-over-abstraction guidance) in `components/headers/`, reused verbatim by subsequent Toolbar-migration increments.
  - **Risk:** `DataTable`→`UTable` (FR-049-13) is a structural rewrite, not a mechanical swap — risk of losing sort/pagination/row-action behavior per table. **Mitigation:** dedicated increments per table (I33-I35), each with an explicit before/after behavior checklist in its exit criteria.
  - **Risk:** Embed bundle accidentally picks up a Nuxt UI import via a shared `@/`-aliased file. **Mitigation:** S-049-11 file-size/hash check after every increment that touches a file the embed might import (composables, utils, types — not `views/`/`components/` which the embed doesn't touch today per the inventory).
  - **Risk:** Losing visual parity on the ~500-line theme preset's per-component overrides (`components` key in `preset.ts`, covering ~35 PrimeVue components) since Nuxt UI's Tailwind-Variants system is structurally different. **Mitigation:** `preset.ts` is kept in the repo (unused, reference-only) until FR-049-18's final removal, so every migration increment can diff its target component's current styling against the preset's override for that component before rewriting.
  - **Risk:** Coexistence period — both `ripple: true` (PrimeVue) and Nuxt UI mounted simultaneously could double-apply some global style/reset CSS. **Mitigation:** I1's exit criteria explicitly checks for visual double-application (e.g. duplicate focus rings, conflicting `cssLayer` ordering) before proceeding to component migration.

## Implementation Drift Gate

Before each phase transition (Foundation → Shell → Toast/Confirm → Button → Dialog → Toolbar → Loading/Layout → Forms → Navigation → DataTable → Misc → Cleanup → Dependency Removal), re-run `grep -rl "from \"primevue/" resources/js | wc -l` and compare against the expected remaining count for that phase boundary (tracked in tasks.md's Notes section). Record the actual count alongside the expected count; investigate any divergence >5 files before continuing (likely indicates a missed file or a new PrimeVue import introduced by unrelated concurrent work). Record results in tasks.md's Notes/TODOs section, not a separate traceability file, given the size of this feature.

## Increment Map

### Phase 0 — Foundation

1. **I1 – Install Nuxt UI in standalone Vue mode**
   - _Goal:_ FR-049-01. App boots with Nuxt UI installed and registered alongside PrimeVue, zero visual change.
   - _Preconditions:_ User-confirmed dependency addition (AGENTS.md dependency-approval rule) for `@nuxt/ui`.
   - _Steps:_ Add `@nuxt/ui` to `package.json`; register `ui()` in `vite.config.ts` (verify plugin ordering against `@tailwindcss/vite` and `laravel-vite-plugin`); register the Nuxt UI Vue plugin in `app.ts`; wrap `views/App.vue`'s root template in `<UApp>`; add `@import "@nuxt/ui";` to `resources/sass/app.css`; add Nuxt UI's generated type declarations to `tsconfig.app.json` and `.gitignore`.
   - _Commands:_ `npm install`, `npm run dev` (manual boot check), `npm run check`.
   - _Exit:_ App boots with no console errors; every existing PrimeVue-rendered view is pixel-unchanged; `npm run check` passes.

2. **I2 – Theme parity (colors, dark mode)**
   - _Goal:_ FR-049-02, FR-049-20. Nuxt UI's theme tokens match `preset.ts`'s primary/surface/dark-mode configuration.
   - _Preconditions:_ I1.
   - _Steps:_ Configure Nuxt UI's theme (CSS `@theme`/`app.config.ts` per its Vue-mode conventions) with primary = `sky` scale, light surface = `slate`, dark surface = `zinc`; confirm Nuxt UI's dark-mode strategy keys off the existing `.dark` class on `<body>` (no change needed to `resources/sass/app.css`'s `@custom-variant dark` line).
   - _Commands:_ `npm run dev` (manual dark-mode toggle check per S-049-02).
   - _Exit:_ Toggling dark mode in Settings flips Nuxt UI's own (not-yet-used) tokens the same way PrimeVue's do; primary color swatch matches when a throwaway `<UButton>` is temporarily rendered next to a `<Button>` for comparison (removed before commit).

3. **I3 – Icon collection setup**
   - _Goal:_ FR-049-15 (setup only — full 139-file sweep happens later, per-phase, as each component migrates). Establish the icon migration seam.
   - _Preconditions:_ I1. User-confirmed dependency addition for `@iconify-json/prime`.
   - _Steps:_ Add `@iconify-json/prime` as a dev dependency; repoint `resources/js/components/icons/PiMiniIcon.vue` to render `<UIcon name="i-prime-...">` instead of `<i class="pi pi-...">`.
   - _Commands:_ `npm install`, `npm run dev` (spot-check icons rendered via `PiMiniIcon.vue`, e.g. `LeftMenu.vue` nav icons).
   - _Exit:_ `PiMiniIcon.vue`-rendered icons are visually identical; `npm run check` passes.

### Phase 1 — New shared composables

4. **I4 – Build `useAppToast()` composable**
   - _Goal:_ FR-049-04 (infrastructure only — 119 call-site migration happens in Phase 3).
   - _Preconditions:_ I1.
   - _Steps:_ Create `resources/js/composables/useAppToast.ts` wrapping Nuxt UI's `useToast()`, exposing `add({ severity, summary, detail, life })` translated to `{ color, title, description, duration }`. Add one throwaway call site to manually verify rendering before wiring into real call sites.
   - _Commands:_ `npm run dev`, `npm run check`.
   - _Exit:_ Composable exists, type-checks, and manually verified to render a toast matching current visual style (per NFR-049-01).

5. **I5 – Build `useConfirmDialog()` composable + `ConfirmModalHost.vue`**
   - _Goal:_ FR-049-05 (infrastructure only — 3 call-site migration happens in I12).
   - _Preconditions:_ I1.
   - _Steps:_ Create `resources/js/components/modals/ConfirmModalHost.vue` (singleton, `<UModal>`-backed) and `resources/js/composables/useConfirmDialog.ts` exposing `confirm({ title, message, acceptLabel?, rejectLabel?, severity? }): Promise<boolean>`.
   - _Commands:_ `npm run dev`, `npm run check`.
   - _Exit:_ Composable + host component exist, type-check, and manually verified (temporary call site) to resolve `true`/`false` correctly on accept/reject.

### Phase 2 — App shell

6. **I6 – Migrate `views/App.vue` shell**
   - _Goal:_ FR-049-03. `<Toast/>`→Nuxt UI toast host (via `<UApp>`, already wrapped in I1), `<ConfirmDialog/>`→`<ConfirmModalHost/>`.
   - _Preconditions:_ I1, I5.
   - _Steps:_ Remove `<Toast/>`/`primevue/toast` import; mount `<ConfirmModalHost/>` in place of `<ConfirmDialog/>`; remove `primevue/confirmdialog` import.
   - _Commands:_ `npm run dev` (S-049-03, S-049-04 smoke check using I4/I5's temporary call sites), `npm run check`.
   - _Exit:_ Toasts and confirms render as global overlays above all routed content; `grep -c "primevue/toast\|primevue/confirmdialog" resources/js/views/App.vue` returns 0.

7. **I7a – Migrate `menus/LeftMenu.vue` structure**
   - _Goal:_ FR-049-12 (structural part). `Drawer`→`USlideover`, `Menu`→`UNavigationMenu`.
   - _Preconditions:_ I2, I3.
   - _Steps:_ Rebuild the drawer container and nav-item list against `<USlideover>`/`<UNavigationMenu>`; recompose `PiMiniIcon`/router-link slot content against `<UNavigationMenu>`'s item slot API.
   - _Commands:_ `npm run dev` (S-049-06: open/close drawer, navigate every top-level nav item).
   - _Exit:_ Drawer opens/closes and routes correctly; icons render via `PiMiniIcon.vue`.

8. **I7b – Migrate `menus/LeftMenu.vue` badges, logout button, styling**
   - _Goal:_ FR-049-12 (remainder), partial FR-049-06/FR-049-16/FR-049-17 (this file's own `Button`/`pt`/`dt`/`v-ripple` usages).
   - _Preconditions:_ I7a, I4.
   - _Steps:_ `OverlayBadge`→`UBadge`/`UChip` for unread/count badges; `Button`→`UButton` for logout; remove `:pt:`/`:dt=` overrides, re-express as `:ui=` or plain Tailwind classes; remove `v-ripple` usage.
   - _Commands:_ `npm run dev` (S-049-06, S-049-09 no-ripple check), `npm run check`.
   - _Exit:_ Badges/counts render correctly; zero `primevue/*` imports remain in `menus/LeftMenu.vue`.

### Phase 3 — Toast/Confirm call-site migration (mechanical)

9. **I8 – Migrate `useToast()` call sites: composables**
   - _Goal:_ FR-049-04. Migrate the 8 `.ts` composables under `composables/album/`, `composables/checkout/`, `composables/photo/` from `primevue/usetoast` to `useAppToast()`.
   - _Preconditions:_ I4.
   - _Commands:_ `npm run check`.
   - _Exit:_ Zero `primevue/usetoast` imports remain in `resources/js/composables/`.

10. **I9 – Migrate `useToast()` call sites: maintenance components**
    - _Goal:_ FR-049-04. Migrate `components/maintenance/Maintenance*.vue` (~14+ files).
    - _Preconditions:_ I4.
    - _Commands:_ `npm run dev` (trigger a few maintenance actions), `npm run check`.
    - _Exit:_ Zero `primevue/usetoast` imports remain in `resources/js/components/maintenance/`.

11. **I10 – Migrate `useToast()` call sites: gallery + forms components**
    - _Goal:_ FR-049-04. Migrate `components/gallery/**`, `components/forms/**` toast call sites.
    - _Preconditions:_ I4.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/usetoast` imports remain in `resources/js/components/gallery/`, `resources/js/components/forms/`.

12. **I11 – Migrate `useToast()` call sites: admin/webshop views + remaining sweep**
    - _Goal:_ FR-049-04. Migrate `views/admin/**`, `views/webshop/**`, and any remaining stragglers to zero.
    - _Preconditions:_ I4.
    - _Commands:_ `npm run dev`, `npm run check`, `grep -rl "primevue/usetoast" resources/js` (must return empty).
    - _Exit:_ Zero `primevue/usetoast` imports repo-wide; `ToastService`/`primevue/config` registration for toast removed from `app.ts` (Config plugin itself stays until I41 since other PrimeVue components still need it).

13. **I12 – Migrate `useConfirm()` call sites**
    - _Goal:_ FR-049-05. Migrate `views/RenamerRules.vue`, `views/admin/ContactMessages.vue`, `views/admin/UserGroups.vue` from `primevue/useconfirm` to `useConfirmDialog()`.
    - _Preconditions:_ I5, I6.
    - _Commands:_ `npm run dev` (S-049-04 on all 3 call sites), `npm run check`.
    - _Exit:_ Zero `primevue/useconfirm` imports remain; `ConfirmationService` registration removed from `app.ts`.

### Phase 4 — Button migration (154 files)

14. **I13 – Buttons: `components/maintenance/`**
    - _Goal:_ FR-049-06 for the maintenance directory (~14-20 files).
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/button` imports in `components/maintenance/`.

15. **I14 – Buttons: `components/gallery/`**
    - _Goal:_ FR-049-06 for album/photo/tag/search gallery modules.
    - _Commands:_ `npm run dev` (photo/album action buttons), `npm run check`.
    - _Exit:_ Zero `primevue/button` imports in `components/gallery/`.

16. **I15 – Buttons: `components/forms/`**
    - _Goal:_ FR-049-06 for album/sharing/settings/users/webshop/basic form components.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/button` imports in `components/forms/`.

17. **I16 – Buttons: `components/headers/`, `components/drawers/`, `components/modals/`, `components/renamer/`, `components/faceRecog/`**
    - _Goal:_ FR-049-06 for remaining component subdirectories.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/button` imports in these directories.

18. **I17 – Buttons: `views/admin/`, `views/webshop/`**
    - _Goal:_ FR-049-06 for admin and webshop views.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/button` imports in `views/admin/`, `views/webshop/`.

19. **I18 – Buttons: remaining views, `components/statistics/`, `menus/` sweep to zero**
    - _Goal:_ FR-049-06 completion. Includes top-level views (`Home.vue`, `Landing.vue`, `Contact.vue`, `Diagnostics.vue`, `Sharing.vue`, `Permissions.vue`, etc.), `views/gallery-panels/`, `views/face-recog/`.
    - _Commands:_ `npm run dev`, `npm run check`, `grep -rl "primevue/button" resources/js` (must return empty).
    - _Exit:_ Zero `primevue/button` imports repo-wide.

### Phase 5 — Dialog migration (55 files)

20. **I19 – Dialogs: `components/forms/album/`**
    - _Goal:_ FR-049-07 for album-related dialogs (`AlbumProperties`, `WatermarkConfirmDialog`, `AlbumCreateShareDialog`, `AlbumCreateTagDialog`, `EmbedCodeDialog`, `ConfirmSharingDialog`, `ApplyRenamerDialog`, etc.). This is the reference implementation for the `v-model:visible`→`v-model:open` rename against `ModalsState`.
    - _Commands:_ `npm run dev` (S-049-05 on ≥3 of these dialogs), `npm run check`.
    - _Exit:_ Zero `primevue/dialog` imports in `components/forms/album/`; the `visible`→`open` store-binding pattern is established and documented in tasks.md Notes for reuse.

21. **I20 – Dialogs: `components/forms/sharing/`, `components/forms/users/`, `components/forms/settings/`**
    - _Goal:_ FR-049-07 for sharing/user/settings dialogs.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/dialog` imports in these directories.

22. **I21 – Dialogs: `components/renamer/`, `components/faceRecog/`, `components/modals/`**
    - _Goal:_ FR-049-07 for renamer rule modal, face-recognition modals (FaceAssignmentModal, SelfieClaimModal), and general modals (UploadPanel, KeybindingsHelp).
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/dialog` imports in these directories.

23. **I22 – Dialogs: `views/admin/`, `views/webshop/`, remaining sweep to zero**
    - _Goal:_ FR-049-07 completion.
    - _Commands:_ `npm run dev`, `npm run check`, `grep -rl "primevue/dialog" resources/js` (must return empty).
    - _Exit:_ Zero `primevue/dialog` imports repo-wide.

### Phase 6 — Toolbar migration (42 files, no direct equivalent)

24. **I23 – Toolbar: `components/headers/` (reference implementation)**
    - _Goal:_ FR-049-08. Migrate `AlbumHeader.vue`, `SearchHeader.vue`, `AlbumsHeader.vue`, `TimelineHeader.vue` to the composed flex-header pattern; this establishes the reusable pattern referenced by I24.
    - _Commands:_ `npm run dev` (visual comparison of header layout), `npm run check`.
    - _Exit:_ Zero `primevue/toolbar` imports in `components/headers/`; the flex-header pattern is noted in tasks.md for reuse.

25. **I24 – Toolbar: remaining views/panels, sweep to zero**
    - _Goal:_ FR-049-08 completion (other panels using Toolbar as page-header chrome).
    - _Commands:_ `npm run dev`, `npm run check`, `grep -rl "primevue/toolbar" resources/js` (must return empty).
    - _Exit:_ Zero `primevue/toolbar` imports repo-wide.

### Phase 7 — Loading/progress primitives

26. **I25 – ProgressSpinner, ProgressBar, MeterGroup (49 files combined)**
    - _Goal:_ FR-049-09. Build a small custom `Spinner.vue`; migrate `ProgressSpinner` (41), `ProgressBar` (6), `MeterGroup` (2) usages, concentrated in `components/maintenance/` and `components/statistics/`.
    - _Commands:_ `npm run dev` (trigger a long-running maintenance action, view storage statistics), `npm run check`.
    - _Exit:_ Zero `primevue/progressspinner`, `primevue/progressbar`, `primevue/metergroup` imports repo-wide.

### Phase 8 — Layout/content primitives

27. **I26a – Card, Panel: `components/maintenance/`, `components/diagnostics/`**
    - _Goal:_ FR-049-10 for the highest-concentration directories.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/card`, `primevue/panel` imports in these directories.

28. **I26b – Card, Panel, Fieldset, Divider: remaining sweep to zero**
    - _Goal:_ FR-049-10 completion (statistics, settings, forms, webshop).
    - _Commands:_ `npm run dev`, `npm run check`, `grep -rl "primevue/card\|primevue/panel\|primevue/fieldset\|primevue/divider" resources/js` (must return empty).
    - _Exit:_ Zero remaining imports for these four components.

### Phase 9 — Form primitives

29. **I27 – Rework the 8 `components/forms/basic/` wrapper components**
    - _Goal:_ FR-049-11 (seam). Swap internals of `InputText.vue`, `Textarea.vue`, `Password.vue`, `Fieldset.vue`, `InputCurrency.vue`, `InputPassword.vue`, `TagsInput.vue`, `PersonsInput.vue` to Nuxt UI equivalents, keeping external prop/emit contracts unchanged.
    - _Commands:_ `npm run dev` (a form-heavy view, e.g. Album Properties), `npm run check`.
    - _Exit:_ All 8 wrappers migrated; consuming components require no changes (contract preserved).

30. **I28 – Select, FloatLabel, Checkbox, ToggleSwitch: `components/forms/album/`, `components/forms/sharing/`**
    - _Goal:_ FR-049-11 for the highest-concentration form directories.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/select`, `primevue/floatlabel`, `primevue/checkbox`, `primevue/toggleswitch` imports in these directories.

31. **I29 – Select, FloatLabel, Checkbox, ToggleSwitch: `components/forms/settings/`, `components/forms/users/`, `views/admin/`, `views/webshop/`**
    - _Goal:_ FR-049-11 continuation.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero remaining imports for these four components in these directories.

32. **I30 – AutoComplete, SelectButton, InputNumber, MultiSelect, Listbox, RadioButton, DatePicker, InputGroup family: sweep to zero**
    - _Goal:_ FR-049-11 completion for all remaining scattered form-primitive usages.
    - _Commands:_ `npm run dev`, `npm run check`, `grep -rl "primevue/select\|primevue/floatlabel\|primevue/checkbox\|primevue/toggleswitch\|primevue/autocomplete\|primevue/selectbutton\|primevue/inputnumber\|primevue/multiselect\|primevue/listbox\|primevue/radiobutton\|primevue/datepicker\|primevue/inputgroup\|primevue/iconfield\|primevue/inputtext\|primevue/textarea\|primevue/password\|primevue/inputswitch" resources/js` (must return empty).
    - _Exit:_ Zero remaining form-primitive PrimeVue imports repo-wide.

### Phase 10 — Navigation/menu (remaining)

33. **I31 – ContextMenu: gallery right-click menus**
    - _Goal:_ FR-049-12 for `components/gallery/tagModule/TagPanel.vue`, `components/gallery/searchModule/ResultPanel.vue`, `components/gallery/albumModule/AlbumPanel.vue`, and the remaining ContextMenu usages (9 files total).
    - _Preconditions:_ `composables/contextMenus/contextMenu.ts` reviewed (menu item construction logic, unaffected by the component swap itself).
    - _Commands:_ `npm run dev` (right-click a photo/album/tag, verify all actions), `npm run check`.
    - _Exit:_ Zero `primevue/contextmenu` imports; every context-menu action verified present.

34. **I32 – Remaining Menu, Paginator, OverlayBadge usages**
    - _Goal:_ FR-049-12/FR-049-14 completion for `views/admin/Settings.vue`, `components/settings/AllSettings.vue` (Menu), pagination components (Paginator), and remaining OverlayBadge usages outside `LeftMenu.vue`.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/menu`, `primevue/paginator`, `primevue/overlaybadge` imports repo-wide.

### Phase 11 — DataTable migration (10 files, structural rewrite)

35. **I33 – DataTable: `components/statistics/AlbumsTable.vue`, `components/drawers/StatTable.vue`, `components/modals/KeybindingsHelp.vue`**
    - _Goal:_ FR-049-13 for the first 3 tables (lower complexity — mostly read-only display tables).
    - _Commands:_ `npm run dev` (S-049-07: verify sort/pagination/data rendering per table), `npm run check`.
    - _Exit:_ These 3 tables migrated to `UTable` with equivalent sort/display behavior.

36. **I34 – DataTable: `views/admin/ContactMessages.vue`, `views/admin/Purchasables.vue`, `views/admin/Webhooks.vue`, `views/admin/NsfwConfig.vue`**
    - _Goal:_ FR-049-13 for admin tables with row actions (edit/delete/toggle).
    - _Commands:_ `npm run dev` (S-049-07: verify row actions trigger correct handlers), `npm run check`.
    - _Exit:_ These 4 tables migrated with equivalent row-action behavior.

37. **I35 – DataTable: `views/admin/shop/PrintPixelSizesAdmin.vue`, `views/webshop/PurchasablesList.vue`, `views/webshop/OrderList.vue`, sweep to zero**
    - _Goal:_ FR-049-13 completion.
    - _Commands:_ `npm run dev`, `npm run check`, `grep -rl "primevue/datatable\|primevue/column" resources/js` (must return empty).
    - _Exit:_ Zero `primevue/datatable`/`primevue/column` imports repo-wide.

### Phase 12 — Miscellaneous components

38. **I36 – Tag, Message**
    - _Goal:_ FR-049-14 for `Tag`(10)→`UBadge`, `Message`(8)→`UAlert`.
    - _Commands:_ `npm run dev`, `npm run check`.
    - _Exit:_ Zero `primevue/tag`, `primevue/message` imports repo-wide.

39. **I37 – ScrollTop, VirtualScroller**
    - _Goal:_ FR-049-14 for `ScrollTop`(7)→custom component, `VirtualScroller`(2, gallery panels — performance-sensitive)→`@tanstack/vue-virtual` or custom.
    - _Preconditions:_ User confirmation if `@tanstack/vue-virtual` is added as a new dependency (AGENTS.md dependency-approval rule) — otherwise implement a minimal custom virtual-scroll fallback.
    - _Commands:_ `npm run dev` (scroll performance check on a large album/gallery view), `npm run check`.
    - _Exit:_ Zero `primevue/scrolltop`, `primevue/virtualscroller` imports; scroll performance on large lists is not visibly degraded.

40. **I38 – Timeline, Tabs family, Stepper family, Inplace, SpeedDial, ScrollPanel: sweep to zero**
    - _Goal:_ FR-049-14 completion for all remaining niche components.
    - _Commands:_ `npm run dev` (checkout flow for Stepper, `views/gallery-panels/Timeline.vue`, `views/admin/NsfwConfig.vue`/`views/gallery-panels/Albums.vue` for Tabs), `npm run check`, `grep -rl "primevue/timeline\|primevue/tabs\|primevue/tablist\|primevue/tab\|primevue/tabpanels\|primevue/tabpanel\|primevue/stepper\|primevue/steppanels\|primevue/steppanel\|primevue/steplist\|primevue/step\|primevue/inplace\|primevue/speeddial\|primevue/scrollpanel" resources/js` (must return empty).
    - _Exit:_ Zero remaining imports for these components repo-wide.

### Phase 13 — Pass-through cleanup

41. **I39 – Verify and clean up remaining `pt`/`dt` overrides**
    - _Goal:_ FR-049-16 completion sweep. Most `:pt:`/`:pt=`/`:dt=` usages are removed incidentally as their host components migrate in earlier phases (each migration increment's exit criteria includes removing that file's overrides); this increment is a verification pass, not a first-touch migration.
    - _Preconditions:_ Phases 4-12 complete.
    - _Commands:_ `grep -rl ":pt:\|:pt=\|:dt=" resources/js` (must return empty, since these are exclusively PrimeVue APIs).
    - _Exit:_ Zero `:pt:`/`:pt=`/`:dt=` occurrences remain.

### Phase 14 — Directive cleanup

42. **I40 – Remove ripple, focustrap, migrate tooltip**
    - _Goal:_ FR-049-17 completion. Remove `ripple: true` config + `Ripple` directive registration + remaining `v-ripple` usages (should already be near-zero after Phase 2/9's file-by-file cleanup); confirm `v-focustrap` fully gone (removed incidentally as each host component migrated to a Reka-UI-backed primitive); migrate `v-tooltip` directive usages to `<UTooltip>` component wrapping.
    - _Preconditions:_ Phases 2-12 complete.
    - _Commands:_ `npm run dev` (S-049-09 no-ripple check, S-049-12 keyboard focus-trap check on a migrated modal, tooltip hover check), `npm run check`, `grep -rl "v-ripple\|v-focustrap\|v-tooltip\|primevue/ripple\|primevue/focustrap\|primevue/tooltip" resources/js` (must return empty).
    - _Exit:_ Zero remaining directive references; keyboard focus-trapping verified on ≥2 migrated modals.

### Phase 15 — Dependency removal & documentation

43. **I41 – Remove PrimeVue dependencies**
    - _Goal:_ FR-049-18. Hard completion gate.
    - _Preconditions:_ All prior increments complete; `grep -rl "primevue\|@primeuix" resources/js` returns zero files.
    - _Steps:_ Remove `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons` from `package.json`; remove `@plugin "tailwindcss-primeui";` from `resources/sass/app.css`; delete `resources/js/style/preset.ts`.
    - _Commands:_ `npm install`, `npm run build`, `npm run build:embed` (S-049-11 embed-untouched check), `npm run check`.
    - _Exit:_ `npm install` produces a lockfile with zero PrimeVue-family packages; both builds succeed; app renders identically.

44. **I42 – Update governing documentation**
    - _Goal:_ FR-049-19.
    - _Preconditions:_ I41.
    - _Steps:_ Update `docs/specs/3-reference/coding-conventions.md`'s `### UI Components` section; update `docs/specs/4-architecture/knowledge-map.md`'s Frontend Dependencies/Components sections.
    - _Exit:_ Both documents reference Nuxt UI, `useAppToast()`, `useConfirmDialog()`; no stale PrimeVue references remain.

45. **I43 – Full quality gate and final sign-off**
    - _Goal:_ Feature completion verification.
    - _Preconditions:_ I41, I42.
    - _Commands:_ `npm run format`, `npm run check`, `npm run build`, `npm run build:embed`.
    - _Steps:_ Full manual smoke test across every major view/flow in both light and dark mode (Home, Album, Photo detail, Search, Upload, Settings, Admin Dashboard, all admin sub-pages, Webshop checkout, Sharing, Face recognition, People).
    - _Exit:_ All commands pass; manual smoke test finds no regressions; roadmap entry moved to Completed.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-049-01 | I1 | Foundation boot, zero visual change |
| S-049-02 | I2 | Dark mode toggle parity |
| S-049-03 | I4, I6 | Toast infra + shell wiring |
| S-049-04 | I5, I6, I12 | Confirm infra + shell wiring + call-site migration |
| S-049-05 | I19 (reference), I20-I22 | Dialog → UModal, store-driven `open` binding |
| S-049-06 | I7a, I7b | LeftMenu drawer + nav |
| S-049-07 | I33-I35 | DataTable → UTable per table |
| S-049-08 | I3 (setup), all component-migration increments (icon sweep incidental to each) | Icon parity spot-check |
| S-049-09 | I8-I18 (button ripple removal incidental), I40 (final verification) | No-ripple check |
| S-049-10 | I41 | Dependency removal gate |
| S-049-11 | I41 (explicit check), spot-checked at any increment touching shared `@/`-aliased files | Embed bundle untouched |
| S-049-12 | I40 | Keyboard focus-trap verification |

## Analysis Gate

Run per [docs/specs/5-operations/analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md).

**Date:** 2026-07-02 · **Reviewer:** AI agent (pre-implementation self-review)

1. **Specification completeness** — ✅ Pass. 21 FRs / 7 NFRs populated in spec.md; Q-049-01/02/03 resolutions folded directly into Overview, Goals, and the relevant FR/NFR `Source` columns; ASCII mock-ups included (app-shell before/after, dialog before/after).
2. **Open questions review** — ✅ Pass. Zero `Open` entries remain for Feature 049 in open-questions.md (all three resolved same-day). ADR-0005 created for the architecturally significant cross-cutting decision (UI-library replacement) and linked from all three Question Details entries and from spec.md's Overview.
3. **Plan alignment** — ✅ Pass. This plan references `spec.md` and `tasks.md` at the paths declared in both documents' headers; Dependencies & Interfaces section matches spec.md's Appendix component-mapping table.
4. **Tasks coverage** — ✅ Pass. Every FR-049-01..21 maps to at least one task in tasks.md (cross-checked by ID during drafting); tasks sequence infrastructure (composables, theme, icons) before consuming call-site migrations; increments are sized to directory/component-family slices to stay within the ≤90-minute guardrail, with explicit sub-increment splitting guidance (I7a/I7b, I26a/I26b) where a single family already exceeds it.
   - ⚠ **Deviation noted:** this feature has no success/validation/failure branch matrix in the traditional sense (NFR-044-style) since it is a mechanical library migration, not new business logic — branch coverage is expressed instead as per-directory `grep`-verified completion sweeps (zero remaining PrimeVue imports) plus manual behavioral spot-checks (S-049-01..12). This substitution is intentional given the feature's nature and is not treated as a gap.
5. **Constitution compliance** — ✅ Pass. No dependency is added without flagging the AGENTS.md approval requirement (see tasks.md Notes); no fallback/shim/backwards-compatibility scaffolding is introduced (PrimeVue/Nuxt UI coexistence during implementation is a transitional engineering reality, not a permanent fallback — FR-049-18/NFR-049-07 make removal a hard gate); ADR-0005 reviewed as part of this analysis (it is the only ADR referencing Feature 049).
6. **Tooling readiness** — ✅ Pass. Every task lists its verification commands (`npm run dev`, `npm run check`, targeted `grep` sweeps, `npm run build`/`build:embed` at completion).

**Outcome:** All checklist items pass or have an explicitly documented, intentional deviation. Implementation of I1 may proceed.

_Implementation Drift Gate: not yet run — deferred until all tasks in tasks.md are marked complete, per the checklist's own sequencing (§"Run this section once all planned tasks are complete")._

## Exit Criteria

- All 45 increments (I1-I43, including I7a/I7b/I26a/I26b sub-increments) marked complete in `tasks.md`.
- `grep -rl "primevue\|@primeuix" resources/js` returns zero files.
- `package.json` has no `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons` entries.
- `npm run format`, `npm run check`, `npm run build`, `npm run build:embed` all pass.
- `docs/specs/3-reference/coding-conventions.md` and `docs/specs/4-architecture/knowledge-map.md` updated (FR-049-19).
- Full manual smoke test (I43) completed with no open regressions.
- Roadmap entry for Feature 049 moved from Active to Completed.

## Follow-ups / Backlog

- Consider a follow-up feature to adopt Nuxt UI's default Lucide icon set as a deliberate visual refresh, now that the library migration (Q-049-02 Option A) has decoupled icon choice from the library swap.
- Consider a follow-up feature to introduce a Vitest + `@vue/test-utils` component test suite now that the app is on a single, more testable UI library (out of scope here per Non-Goals, but the migration removes one obstacle — PrimeVue's heavier test-mounting requirements — to adding one later).
- Re-evaluate whether a custom ripple directive (Q-049-03 Option B) is worth revisiting if user feedback indicates the interaction-feel change is unwelcome.
- Monitor Nuxt UI's release notes for its standalone-Vue-mode maturity (it's a newer usage pattern than full-Nuxt integration) and revisit any workarounds adopted during this migration if upstream gaps close.

---

*Last updated: 2026-07-02*
