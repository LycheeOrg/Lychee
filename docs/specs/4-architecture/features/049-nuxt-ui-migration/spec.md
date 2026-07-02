# Feature 049 – Migration to Nuxt UI

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-07-02 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/049-nuxt-ui-migration/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/049-nuxt-ui-migration/tasks.md` |
| Roadmap entry | #049 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Lychee's frontend (`resources/js/`, Vue 3 + TypeScript + Vite + Tailwind CSS v4) currently builds its entire UI on **PrimeVue** v4 (`primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons`). This feature replaces PrimeVue with **Nuxt UI** (`@nuxt/ui`), used in its standalone Vue mode (no Nuxt meta-framework required — Nuxt UI v3 ships a Vite plugin and Vue plugin for non-Nuxt apps, built on Reka UI primitives + Tailwind CSS v4, which the app already uses).

A codebase inventory (2026-07-02) found PrimeVue imported in **235 of 286** `.vue`/`.ts` files under `resources/js/` (~82%). The cost is not evenly distributed:
- **197 files** use `tailwindcss-primeui`-generated Tailwind utility classes (`bg-primary-500`, `text-surface-700`, `text-muted-color`, etc.) — the single largest, most diffuse migration cost.
- A **~500-line custom theme preset** (`resources/js/style/preset.ts`, passed to `definePreset(Aura, ...)`) defines the app's entire visual identity (primary color scale, light/dark surface scales, per-component style overrides for ~35 PrimeVue components) and has no Nuxt UI equivalent — it must be re-authored as Nuxt UI's Tailwind-Variants-based `app.config.ts`/CSS theme layer.
- **36–42 files** use PrimeVue v4's pass-through styling APIs (`:pt:<slot>:class=`, `:pt=`, `:dt=`) which have no mechanical translation to Nuxt UI's `:ui=` slot-override system.
- High-frequency components: Button (154 files), `useToast` (119 call sites, no wrapper composable exists today), Dialog (55), Toolbar (42, no direct Nuxt UI equivalent), ProgressSpinner (41), Card (36), Panel (35), Select (27), FloatLabel (26), ScrollPanel (23), Checkbox (23), ToggleSwitch (17).
- Icons: `primeicons` (`pi pi-*`) appears **562 times across 139 files**. Font Awesome (also a dependency) is effectively unused (2 files) and is not part of this migration's scope.
- **10 files** (all in admin/statistics/webshop views) use PrimeVue's `DataTable`/`Column`, whose slot-based column API is structurally different from Nuxt UI's `UTable` (TanStack-Table column-array API) and requires per-file rewrite, not a prop swap.
- The embeddable widget bundle (`resources/js/embed/`, built via `vite.embed.config.ts` into `public/embed/lychee-embed.js`) has **zero PrimeVue coupling** and is explicitly out of scope.
- There is **no frontend automated test suite** (`resources/js/**/*.test.ts` — zero files exist); `npm run check` is `vue-tsc` type-checking only. Verification is manual/browser-based per the project's standard practice for frontend changes.

Three high-impact design questions were resolved before planning and recorded in **ADR-0005** (`docs/specs/6-decisions/ADR-0005-nuxt-ui-migration.md`) (see also [open-questions.md](../../open-questions.md) Q-049-01..03):
- **Q-049-01** (feature sizing): this feature covers the **entire** PrimeVue removal, planned as one feature with many grouped increments, tracked to full completion.
- **Q-049-02** (icons): icon **visual parity** is preserved via the Iconify `prime` collection (`@iconify-json/prime`, confirmed published on npm, mirrors PrimeIcons 1:1) — no icon redesign in this feature.
- **Q-049-03** (ripple): the PrimeVue ripple click effect is **dropped entirely**; Reka UI's built-in focus-trapping replaces `v-focustrap`.

**Affected modules:** Frontend only (`resources/js/`, `resources/sass/app.css`, `package.json`, `vite.config.ts`). No backend (PHP), REST, or CLI changes. `docs/specs/3-reference/coding-conventions.md` (UI Components section) and `docs/specs/4-architecture/knowledge-map.md` (Frontend Dependencies section) are updated as documentation deliverables once the migration completes.

## Goals

- Replace every PrimeVue component, directive, service, and theme construct in the main app bundle (everything reachable from `resources/js/app.ts`) with a Nuxt UI equivalent or a small custom composable/component built on Nuxt UI primitives, matching the existing coding convention of "build custom components on top of [the UI library's] primitives."
- Preserve the current visual identity: same primary color (sky-based), same light/dark surface scales, same icon set (via Iconify `prime`), same layout and information hierarchy across every view. This is a **library migration**, not a redesign.
- Preserve the existing server-config-driven dark mode toggle (`document.body.classList` + `dark_mode_enabled` config) unchanged in behavior.
- Preserve the existing Pinia-store-driven modal visibility pattern (`resources/js/stores/ModalsState.ts`) — dialogs keep using `v-model` bindings against store flags, just renamed to whatever prop Nuxt UI's `<UModal>` expects (`open` instead of `visible`).
- Introduce a `useAppToast()` wrapper composable to centralize the 119 currently-direct `useToast()` call sites, and a new `useConfirm()`-equivalent composable (backed by `<UModal>`) to replace PrimeVue's `ConfirmationService`/`useConfirm()`/`<ConfirmDialog>` (no Nuxt UI built-in equivalent exists), since these are new architectural seams this migration introduces.
- Remove `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, and `primeicons` from `package.json` once no file imports from them.
- Keep `resources/js/embed/**` and its build output (`public/embed/lychee-embed.js`, `public/embed/lychee-embed.css`) untouched — verify no Nuxt UI code is pulled into that bundle.
- Keep `npm run check` (`vue-tsc --noEmit`) green throughout every increment, including increments where PrimeVue and Nuxt UI coexist.
- Update `docs/specs/3-reference/coding-conventions.md` and `docs/specs/4-architecture/knowledge-map.md` to reference Nuxt UI instead of PrimeVue once the migration completes.

## Non-Goals

- Any visual/design-language redesign beyond what dropping the ripple effect (Q-049-03) necessarily changes. Icon shapes, color palette, spacing, and component layout stay the same (Q-049-02).
- Migrating `resources/js/embed/**` to Nuxt UI or any UI library — it remains dependency-free.
- Introducing a frontend automated test suite (unit/component tests) as part of this migration. Verification is manual/browser-based, consistent with current project practice. (A follow-up feature could introduce Vitest coverage, but that is out of scope here.)
- Migrating the full app to the Nuxt meta-framework (SSR, file-based routing, Nuxt modules). Nuxt UI is used purely as a component library inside the existing Vite + Vue Router SPA.
- Rebuilding `resources/js/style/preset.ts`'s color math from scratch — the goal is visual parity with the existing preset, not a new palette.
- Adding automated visual-regression tooling (e.g. Percy/Chromatic) to verify pixel parity. Manual browser comparison is the verification method (see NFR-049-06).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-049-01 | Install Nuxt UI in standalone Vue mode alongside PrimeVue | `@nuxt/ui` is added to `package.json`; `vite.config.ts` registers the `ui()` Vite plugin; `resources/js/app.ts` registers the Nuxt UI Vue plugin (`app.use(ui)`); `resources/js/views/App.vue`'s root template is wrapped in `<UApp>` (required for Toast/Tooltip/programmatic overlays); `resources/sass/app.css` imports `@import "@nuxt/ui";` alongside the existing `@import "tailwindcss";`. | App boots with both libraries active; no console errors; existing PrimeVue-rendered views are visually unchanged. | `npm run check` (vue-tsc) passes with both libraries' types present; `tsconfig.app.json` includes Nuxt UI's generated `auto-imports.d.ts`/`components.d.ts`. | If the Vite plugin conflicts with `laravel-vite-plugin`/`@tailwindcss/vite` ordering, build fails — resolved before any component migration proceeds. | No telemetry (frontend-only). | Q-049-01 |
| FR-049-02 | Recreate theme tokens for visual parity | A Nuxt UI theme configuration (CSS `@theme`/`app.config.ts` per Nuxt UI's Vue-mode conventions) defines: primary color = the same `sky` Tailwind scale currently in `preset.ts`'s `semantic.primary`; light-mode surface scale = `slate` (matches `colorScheme.light.surface`); dark-mode surface scale = `zinc` (matches `colorScheme.dark.surface`); the same focus-ring, border-radius, and form-field padding tokens where Nuxt UI exposes equivalent knobs. | Buttons, form fields, and surfaces render with the same colors pre/post migration for a sampled set of views (Home, Album, Settings, Admin Dashboard). | Side-by-side manual comparison against `resources/js/style/preset.ts` values (kept as the source of truth for the target palette until fully removed in FR-049-18). | Where Nuxt UI has no equivalent token (e.g. PrimeVue's per-component `formField.paddingX`), the closest Tailwind Variants override on the affected Nuxt UI component is used instead. | No telemetry. | Q-049-02, NFR-049-01 |
| FR-049-03 | Migrate the app shell (`views/App.vue`) | `<Toast />` (PrimeVue) is replaced by Nuxt UI's toast host (provided implicitly by `<UApp>`, no separate host component needed); `<ConfirmDialog />` is replaced by the new custom confirm composable's host component (FR-049-05); `<LeftMenu />` and `<EmbedCodeDialog />` remain mounted the same way, migrated internally per FR-049-08/FR-049-07. | App shell renders identically; toasts and confirms still appear as global overlays above all routed content. | Manual verification: trigger a toast and a confirm dialog from any view, confirm they render above the `<main>` content. | N/A | No telemetry. | Inventory §9 |
| FR-049-04 | Migrate Toast usage via a new `useAppToast()` composable | A new composable `resources/js/composables/useAppToast.ts` wraps Nuxt UI's `useToast()`, exposing the same call shape the app already uses (`{ severity/color, summary/title, detail/description, life/duration }`, mapped to Nuxt UI's `toast.add({...})` fields). All 119 current `useToast()` call sites (in `.vue` components and composables under `composables/album/`, `composables/checkout/`, `composables/photo/`) are migrated to import and call `useAppToast()` instead of `primevue/usetoast`. | Every toast-producing action (upload errors, save confirmations, etc.) shows a Nuxt UI toast with the correct severity color and message, in every migrated call site. | Grep-verified: zero remaining `from "primevue/usetoast"` imports after this increment. | N/A | No telemetry. | Inventory §5 |
| FR-049-05 | Replace `ConfirmationService`/`useConfirm()`/`<ConfirmDialog>` with a custom confirm composable | A new composable `resources/js/composables/useConfirmDialog.ts` (backed by a new singleton component, e.g. `resources/js/components/modals/ConfirmModalHost.vue`, using `<UModal>`) exposes a Promise-based `confirm({ title, message, ... }): Promise<boolean>` API. The singleton host is mounted once in `views/App.vue`. All 3 existing `useConfirm()` call sites (`views/RenamerRules.vue`, `views/admin/ContactMessages.vue`, `views/admin/UserGroups.vue`) are migrated to the new composable. | Calling `confirm({...})` shows a modal; resolving `true`/`false` on confirm/cancel matches the current `accept`/`reject` callback behavior. | Grep-verified: zero remaining `from "primevue/useconfirm"` or `from "primevue/confirmdialog"` imports after this increment. | N/A | No telemetry. | Inventory §2, §5 |
| FR-049-06 | Migrate all `Button` usages to `UButton` | All 154 files importing `primevue/button` are updated to Nuxt UI's `<UButton>`, mapping `severity`→`color`, `outlined`/`text`/`link` variant props → Nuxt UI's `variant`, `icon`/`iconPos` → Nuxt UI's `icon`/`trailing` conventions, `loading` prop preserved. | Every button renders with the same color/variant/icon/loading affordances as before. | Manual spot-check across a representative sample (toolbar buttons, form submit buttons, icon-only buttons, danger/destructive buttons). | N/A | No telemetry. | Inventory §1 |
| FR-049-07 | Migrate all `Dialog` usages to `UModal` | All 55 files importing `primevue/dialog` are updated to Nuxt UI's `<UModal>`, renaming `v-model:visible` bindings to `v-model:open` against the same `ModalsState` Pinia store flags (no store restructuring required — confirmed store already uses a boolean-flag-per-dialog pattern compatible with `v-model:open`). Header/footer/content slots are remapped to `<UModal>`'s `#header`/`#body`/`#footer` slots. | Every dialog opens/closes via the same store flag, renders the same header/content/footer, and the same primary/secondary action buttons trigger the same handlers as before. | Manual verification per dialog: open via its trigger, confirm content renders, confirm close (X, backdrop click, Escape, cancel button) all still dismiss it. | N/A | No telemetry. | Inventory §5, §9 |
| FR-049-08 | Migrate `Toolbar` usages to a composed header pattern | All 42 files importing `primevue/toolbar` are updated to a plain flex container (`<div class="flex items-center justify-between ...">`) with `#start`/`#center`/`#end` PrimeVue slot content redistributed into left/center/right flex groups, since Nuxt UI has no direct `Toolbar` component. | Page/panel headers retain the same left-aligned title/breadcrumb, centered content (if any), and right-aligned action buttons layout. | Manual visual comparison of each migrated header against its pre-migration screenshot/behavior. | N/A | No telemetry. | Inventory §1, §9 |
| FR-049-09 | Migrate loading/progress primitives | `ProgressSpinner` (41 files) → Nuxt UI's loading affordance (e.g. a small custom `Spinner.vue` using Nuxt UI's icon system with a spin animation, since Nuxt UI has no standalone spinner component — `UButton`'s built-in `loading` covers in-button cases) or `<UProgress>` where a determinate bar fits; `ProgressBar` (6 files) and `MeterGroup` (2 files, storage-size meters) → `<UProgress>` with appropriate `color`/value mapping. | Loading states and progress meters render visually equivalent to before. | Manual spot-check on long-running actions (uploads, bulk operations, maintenance tasks) and the storage statistics meter. | N/A | No telemetry. | Inventory §1 |
| FR-049-10 | Migrate layout/content primitives | `Card` (36), `Panel` (35), `Fieldset` (1), `Divider` (9) are migrated to Nuxt UI's `<UCard>`/`<UPageCard>` (or a plain styled `<div>` where Nuxt UI's card padding/shadow defaults don't fit) and `<USeparator>` respectively, preserving header/content/footer slot structure. | Panels/cards/fieldsets retain the same visual grouping, border, and spacing as before. | Manual spot-check across settings, statistics, and maintenance panels. | N/A | No telemetry. | Inventory §1 |
| FR-049-11 | Migrate form input primitives via existing wrapper components | The 8 existing thin wrapper components in `resources/js/components/forms/basic/` (`InputText.vue`, `Textarea.vue`, `Password.vue`, `Fieldset.vue`, plus `InputCurrency.vue`, `InputPassword.vue`, `TagsInput.vue`, `PersonsInput.vue`) have their internals swapped to Nuxt UI's `<UInput>`, `<UTextarea>`, `<UInputPassword>` equivalents (or nearest match), keeping their external prop/emit contract unchanged so consuming components need no changes beyond re-importing if paths change. Direct (non-wrapped) usages of `Select` (27), `Checkbox` (23), `ToggleSwitch`/`InputSwitch` (18), `FloatLabel` (26), `AutoComplete` (5), `SelectButton` (4), `InputNumber` (7), `MultiSelect` (1), `Listbox` (1), `RadioButton` (1), `DatePicker` (2), `InputGroup`/`InputGroupAddon`/`IconField`/`InputIcon` (2 each) are migrated to their nearest Nuxt UI equivalents (`USelect`/`USelectMenu`, `UCheckbox`, `USwitch`, `UFormField` (label/float-label replacement), `UInputNumber`, `URadioGroup`, calendar/date-picker composed from Reka UI's date primitives, `UButtonGroup`/`UInput` slots for icon-field composition). | Every form field renders with the same label placement, validation-error styling hook points, and interaction behavior as before. | Manual verification of the most form-heavy views (Album Properties, User create/edit, Webshop checkout, Settings). | N/A | No telemetry. | Inventory §1 |
| FR-049-12 | Migrate navigation/menu components, including `LeftMenu.vue` | `resources/js/menus/LeftMenu.vue` (the primary nav shell: `Drawer` + `Menu` + `OverlayBadge` + `Button` + `v-ripple` + `:pt:`/`:dt=` overrides) is rebuilt using Nuxt UI's `<USlideover>` (replaces `Drawer`) and `<UNavigationMenu>` (vertical mode, replaces `Menu`), with `PiMiniIcon`/`SETag`/badge-count slots recomposed against `<UNavigationMenu>`'s slot API. `ContextMenu` (9 files, right-click menus in gallery panels) migrates to Nuxt UI's `<UContextMenu>`/`<UDropdownMenu>`. Other `Menu` usages (`views/admin/Settings.vue`, `components/settings/AllSettings.vue`) migrate similarly. | The left navigation drawer opens/closes, routes to the correct views, shows the same icons/badges/counts, and the same right-click context menus appear in gallery views with the same actions. | Manual click-through: open/close drawer, navigate every top-level nav item, right-click a photo/album/tag to confirm the context menu still has all actions. | N/A | No telemetry. | Inventory §9 |
| FR-049-13 | Migrate `DataTable`/`Column` to `UTable` | The 10 files using `primevue/datatable`+`primevue/column` (`components/statistics/AlbumsTable.vue`, `components/modals/KeybindingsHelp.vue`, `components/drawers/StatTable.vue`, `views/admin/ContactMessages.vue`, `views/admin/shop/PrintPixelSizesAdmin.vue`, `views/admin/Purchasables.vue`, `views/webshop/PurchasablesList.vue`, `views/webshop/OrderList.vue`, `views/admin/Webhooks.vue`, `views/admin/NsfwConfig.vue`) are individually rewritten against Nuxt UI's `<UTable>` (TanStack-Table-based column-definition API), preserving sorting, pagination, row-action, and cell-formatting behavior each table currently has. | Each migrated table renders the same columns/rows, supports the same sort/pagination/row-action interactions as before. | Manual verification per table: sort a sortable column, paginate if applicable, trigger a row action, confirm data matches the pre-migration table. | N/A | No telemetry. | Inventory §1, §9 |
| FR-049-14 | Migrate remaining miscellaneous components | `Tag` (10, e.g. SE badge), `Message` (8, inline validation/info banners) → `<UBadge>`/`<UAlert>`; `ScrollTop` (7) → a small custom scroll-to-top button (no Nuxt UI equivalent, trivial to hand-roll); `VirtualScroller` (2, large gallery lists) → Nuxt UI has no built-in virtual scroller; evaluate `@tanstack/vue-virtual` (already a Nuxt UI/Reka UI ecosystem dependency) or keep a minimal custom implementation; `Timeline` (2) → custom composition (no direct Nuxt UI equivalent); `Tabs` family (`Tabs`/`TabList`/`Tab`/`TabPanels`/`TabPanel`, 2 files) → Nuxt UI's `<UTabs>`; `Stepper` family (5 modules, checkout flow) → Nuxt UI has no stepper component; compose from `<UButton>`/`<UProgress>` step indicators; `Inplace` (1) → custom click-to-edit composition; `Paginator` (3) → Nuxt UI's `<UPagination>`; `SpeedDial` (2) → composed `<UButton>` group. | Each migrated component preserves its current behavior (badge display, alert visibility, scroll-to-top action, virtualized list performance, timeline rendering, tab switching, checkout stepper progression, inline-edit toggling, pagination, floating action menu). | Manual verification per component family in its actual usage context. | Where no Nuxt UI equivalent exists (`VirtualScroller`, `Timeline`, `Stepper`, `ScrollTop`, `Inplace`, `SpeedDial`), a small custom component is built and documented inline (one-line comment only where genuinely non-obvious, per project comment policy) rather than left as a PrimeVue island. | No telemetry. | Inventory §1 |
| FR-049-15 | Migrate icons to Iconify `prime` collection | `@iconify-json/prime` is added as a dev dependency. `resources/js/components/icons/PiMiniIcon.vue` (the existing chokepoint wrapper) is repointed to render Nuxt UI's `<UIcon name="i-prime-<name>">` instead of `<i class="pi pi-<name>">`. All other direct `pi pi-*` class-string usages (in templates and in menu/data-config objects across ~139 files) are updated to the `i-prime-<name>` Iconify naming convention consumed by `<UIcon>`/component `icon=` props. | Every icon renders visually identical to its PrimeIcons predecessor (same glyph, same collection) across all 139 files. | Automated grep check: zero remaining `pi pi-` class-string occurrences after this increment; spot-check a representative sample (nav icons, button icons, status icons) visually. | N/A | No telemetry. | Q-049-02 |
| FR-049-16 | Remove PrimeVue pass-through (`pt`/`dt`) overrides | The 36 files using `:pt:<slot>:class=` shorthand, 6 files using object-form `:pt=`, and 6 files using `:dt=` design-token overrides are individually rewritten. Where the override exists to fix spacing/color for that specific instance, it is re-expressed as Nuxt UI's `:ui="{ <slot>: '<tailwind classes>' }"` slot-override prop (Nuxt UI's Tailwind-Variants-based equivalent) or as plain Tailwind classes on the component if Nuxt UI's default styling already matches. | Every previously-overridden component instance retains its custom spacing/color/layout post-migration. | Manual visual comparison per overridden instance (these are concentrated in `menus/LeftMenu.vue`, `components/statistics/TotalCard.vue`, `components/headers/*.vue`, `components/forms/basic/Fieldset.vue`, `components/forms/users/CreateEditUser.vue`). | N/A | No telemetry. | Inventory §3 |
| FR-049-17 | Remove ripple effect and `v-focustrap` directive | `app.ts`'s `ripple: true` PrimeVue config, the `app.directive("ripple", Ripple)` registration, and every `v-ripple` template usage are deleted with no replacement (Q-049-03 Option A). `app.directive("focustrap", FocusTrap)` and every `v-focustrap` usage are removed as each host component migrates to a Reka-UI-backed Nuxt UI primitive (`<UModal>`, `<USlideover>`, etc.) that traps focus internally. `v-tooltip` (`primevue/tooltip`) is migrated to Nuxt UI's `<UTooltip>` component (Nuxt UI does not ship a directive-based tooltip; call sites using the directive form are converted to wrapping the target element in `<UTooltip text="...">`). | No ripple visual effect remains anywhere in the app; focus still moves correctly into/out of modals and drawers (verified via keyboard Tab navigation); tooltips still appear on hover/focus with the same text. | Grep-verified: zero remaining `v-ripple`, `v-focustrap`, `v-tooltip`, `primevue/ripple`, `primevue/focustrap`, `primevue/tooltip` references after this increment. | N/A | No telemetry. | Q-049-03 |
| FR-049-18 | Remove PrimeVue dependencies | Once every file above has migrated (zero remaining `from "primevue/*"` or `from "@primeuix/*"` imports, confirmed via repo-wide grep), `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, and `primeicons` are removed from `package.json` `dependencies`; the `@plugin "tailwindcss-primeui";` line is removed from `resources/sass/app.css`; `resources/js/style/preset.ts` is deleted. | `npm install` produces a lockfile with zero PrimeVue-family packages; `npm run build` succeeds; app boots and renders identically to the pre-removal state. | `grep -rl "primevue\|@primeuix" resources/js` returns zero files before this task is marked complete. | If any residual import is found, this task is blocked until that file is migrated (no partial removal). | No telemetry. | FR-049-01..17 completion gate |
| FR-049-19 | Update governing documentation | `docs/specs/3-reference/coding-conventions.md`'s "UI Components" section (`### UI Components` under `## Vue3/TypeScript Conventions`) is updated to say "Use Nuxt UI for UI components" (replacing the PrimeVue reference). `docs/specs/4-architecture/knowledge-map.md`'s "Frontend Dependencies" and "Components" sections are updated to reference Nuxt UI instead of PrimeVue. | Both documents accurately describe the post-migration stack with no stale PrimeVue references. | Manual review of both files' diffs against this spec's FR list. | N/A | No telemetry. | AGENTS.md documentation-sync requirement |
| FR-049-20 | Preserve dark mode toggle behavior | The existing server-config-driven dark mode mechanism (`document.body.classList.add/remove("dark")` in `components/settings/General.vue` and `views/admin/Settings.vue`, driven by the `dark_mode_enabled` config and set server-side in `resources/views/vueapp.blade.php`) is preserved unchanged. Nuxt UI's dark-mode styling is configured to key off the same `.dark` class on `<body>` (Tailwind's `@custom-variant dark (&:where(.dark, .dark *));` in `resources/sass/app.css` already targets this and needs no change). | Toggling dark mode in Settings still flips every migrated component's appearance immediately, with no page reload required. | Manual toggle test after each major increment (foundation, then periodically through component migration) to catch any Nuxt UI component that doesn't respect the shared `.dark` selector. | N/A | No telemetry. | Inventory §8 |
| FR-049-21 | Keep the embed bundle untouched | `resources/js/embed/**` continues to import nothing from PrimeVue, Nuxt UI, `resources/js/app.ts`, `resources/js/style/`, or any migrated `views/`/`components/` file that itself now depends on Nuxt UI. `vite.embed.config.ts`'s build output (`public/embed/lychee-embed.js`, `public/embed/lychee-embed.css`) is verified to have no new UI-library code inlined after the migration. | `npm run build:embed` output file size stays within the same order of magnitude as before migration (no accidental Nuxt UI inlining). | Compare `public/embed/lychee-embed.js` file size/hash before and after each increment that touches shared (`@/`-aliased) files the embed might import. | If the embed bundle grows unexpectedly, identify and remove the accidental shared-file dependency before proceeding. | No telemetry. | Inventory §7, NFR-049-04 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-049-01 | Visual parity (colors, icons, layout) | UX continuity — this is a library migration, not a redesign (Q-049-02) | Manual side-by-side comparison of each migrated view against its pre-migration rendering; primary color, surface scales, and icon glyphs must match. | `resources/js/style/preset.ts` (kept as reference until FR-049-18), `@iconify-json/prime` | Q-049-02 |
| NFR-049-02 | `npm run check` stays green throughout | Prevents type-checking regressions from blocking other in-flight work during a long-running migration | `vue-tsc --noEmit -p tsconfig.json --composite false --skipLibCheck` passes after every increment, not just at the end | Nuxt UI's generated `auto-imports.d.ts`/`components.d.ts` registered in `tsconfig.app.json` | AGENTS.md quality gate |
| NFR-049-03 | No automated frontend regression suite exists — verification is manual | No `resources/js/**/*.test.ts` files exist in the repo today; introducing one is out of scope (see Non-Goals) | Each increment's exit criteria include an explicit manual browser verification step (dev server, representative views, both light and dark mode) per AGENTS.md's "start the dev server and use the feature in a browser" rule for UI changes | `npm run dev` | Inventory §6 |
| NFR-049-04 | Embed bundle stays dependency-free and near-constant size | The embeddable widget is distributed to third-party sites; bundle bloat directly affects host-page load time | `public/embed/lychee-embed.js` size compared before/after each increment touching shared files; zero PrimeVue/Nuxt UI imports found via grep in `resources/js/embed/` | `vite.embed.config.ts` | Inventory §7 |
| NFR-049-05 | Accessibility parity or improvement for interactive primitives | Reka UI (Nuxt UI's underlying primitive library) ships WAI-ARIA-compliant, keyboard-accessible modal/menu/dropdown primitives out of the box | Manual keyboard-navigation spot-check (Tab/Escape/Arrow keys) on migrated modals, drawers, and menus | Reka UI (transitive Nuxt UI dependency) | Inventory §5, §9 |
| NFR-049-06 | Increments stay ≤90 minutes despite overall feature size | AGENTS.md planning guardrail; a 235-file migration must be decomposed into many small, independently-shippable increments rather than attempted as one large change | plan.md's Increment Map groups work by component family/layer, each sized to fit the guardrail (finer-grained sub-increments — e.g. I7a/I7b — where a single component family still exceeds it) | plan.md Increment Map | AGENTS.md |
| NFR-049-07 | PrimeVue/Nuxt UI coexistence is transitional only | Avoids a permanently mixed-UI-library codebase (per Q-049-01 Option A's rejection of open-ended coexistence) | FR-049-18 is a hard completion gate — the feature is not considered done, and the roadmap entry is not moved to Completed, until zero PrimeVue imports remain and the dependencies are removed from `package.json` | All FR-049-01..17 | Q-049-01 |

## UI / Interaction Mock-ups

The migration preserves layout and information hierarchy; only the underlying component implementation changes. The app shell (left navigation + toolbar header + content) looks the same before and after — annotations below show which PrimeVue construct maps to which Nuxt UI construct at each point in the shell.

```
BEFORE (PrimeVue)                              AFTER (Nuxt UI)
┌───────────────────────────────────┐          ┌───────────────────────────────────┐
│ <Drawer> (LeftMenu)                │          │ <USlideover> (LeftMenu)            │
│  ┌─────────────────────────────┐   │          │  ┌─────────────────────────────┐   │
│  │ ☰  Lychee                    │   │          │  │ ☰  Lychee                    │   │
│  │                               │   │          │  │                               │   │
│  │ <Menu> (nav items)            │   │          │  │ <UNavigationMenu> (nav items) │   │
│  │  📁 Albums                    │   │   ⇄      │  │  📁 Albums                    │   │
│  │  ⭐ Favourites                 │   │          │  │  ⭐ Favourites                 │   │
│  │  🔔 Notifications <OverlayBadge>3│  │          │  │  🔔 Notifications  <UBadge>3  │   │
│  │                               │   │          │  │                               │   │
│  │ <Button> Logout                │   │          │  │ <UButton> Logout               │   │
│  └─────────────────────────────┘   │          │  └─────────────────────────────┘   │
├───────────────────────────────────┤          ├───────────────────────────────────┤
│ <Toolbar> (AlbumHeader)            │          │ flex header (AlbumHeader)          │
│  #start: title   #end: [Btn][Btn]  │   ⇄      │  <div class="flex justify-between">│
│                                     │          │   title      <UButton><UButton>    │
├───────────────────────────────────┤          ├───────────────────────────────────┤
│ <main><router-view/></main>        │   =      │ <main><router-view/></main>        │
│   (photo grid — unaffected layout) │          │   (photo grid — unaffected layout) │
└───────────────────────────────────┘          └───────────────────────────────────┘
  Global singletons in App.vue:                   Global singletons in App.vue:
  <Toast/> <ConfirmDialog/>              ⇄        <UApp> toast host + ConfirmModalHost
```

```
Dialog example (e.g. Album Properties) — same store-driven visibility, different component:

BEFORE                                          AFTER
<Dialog v-model:visible="                       <UModal v-model:open="
  modalsState.is_rename_visible">                  modalsState.is_rename_visible">
  #header  Rename album                           #header  Rename album
  #default <InputText v-model="title"/>           #body    <UInput v-model="title"/>
  #footer  <Button @click="save"/>                #footer  <UButton @click="save"/>
</Dialog>                                        </UModal>
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-049-01 | Foundation increment lands (FR-049-01/02) → app boots with Nuxt UI installed alongside PrimeVue, zero visual change on any existing view, `npm run check` passes |
| S-049-02 | Dark mode toggle (Settings → General) → all migrated components (both PrimeVue and Nuxt UI, during coexistence) flip appearance immediately without reload |
| S-049-03 | An action that previously showed a PrimeVue toast (e.g. failed upload) now shows a Nuxt UI toast via `useAppToast()`, same severity color and message |
| S-049-04 | A destructive action requiring confirmation (e.g. deleting a webhook in `views/admin/Webhooks.vue`) shows the new `useConfirmDialog()` modal; confirming proceeds, cancelling aborts, matching prior `useConfirm()` behavior |
| S-049-05 | A representative `Dialog`-based form (e.g. Album Properties rename) migrated to `<UModal>` opens via its existing trigger, saves via the same store/service call, and closes on save/cancel/Escape/backdrop-click |
| S-049-06 | `LeftMenu.vue` migrated to `<USlideover>` + `<UNavigationMenu>` — drawer opens/closes, every nav item routes correctly, unread/count badges still render |
| S-049-07 | A `DataTable`-based admin view (e.g. `views/admin/Webhooks.vue`) migrated to `<UTable>` retains sorting, pagination (where present), and row-action buttons |
| S-049-08 | A sampled set of ≥15 icons (nav, button, status) renders identically (same glyph) after the Iconify `prime` migration (FR-049-15) |
| S-049-09 | Post-migration, clicking any `<UButton>` shows no ripple animation (explicit non-behavior check per Q-049-03) |
| S-049-10 | Final increment (FR-049-18) — `npm run build` succeeds with `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons` fully removed from `package.json`, and `grep -rl "primevue\|@primeuix" resources/js` returns zero files |
| S-049-11 | `npm run build:embed` output (`public/embed/lychee-embed.js`/`.css`) is unaffected — no new UI-library code inlined, file size stays in the same order of magnitude, at every increment boundary that touches `@/`-aliased shared files |
| S-049-12 | Keyboard navigation (Tab into a migrated `<UModal>`, Escape to close) traps and restores focus correctly, verifying Reka UI's built-in focus-trap replaces the removed `v-focustrap` directive without regression |

## Test Strategy

- **UI (manual/browser):** Since no frontend automated test suite exists (NFR-049-03) and introducing one is out of scope, every increment's exit criteria requires starting `npm run dev` and manually exercising the migrated views/components in both light and dark mode, per AGENTS.md's frontend verification rule. Increments that touch shared shell components (`App.vue`, `LeftMenu.vue`) require a broader smoke pass across multiple views since regressions there are global.
- **Type safety:** `npm run check` (`vue-tsc --noEmit`) must pass after every increment (NFR-049-02) — this is the only automated gate available for this feature and catches prop/type mismatches from the library swap even without behavioral test coverage.
- **Formatting:** `npm run format` (Prettier) after every increment per the standard frontend quality gate.
- **Build integrity:** `npm run build` (both `vite build` and `vite build --config vite.embed.config.ts`) must succeed at the end of every increment; the embed sub-build is specifically checked against NFR-049-04 (S-049-11).
- **No backend/PHP test impact:** this feature makes no backend changes; `php artisan test`/`make phpstan` are not run for increments scoped to `resources/js/**` only, per AGENTS.md's "only run checks for file types that were modified" rule.

## Interface & Contract Catalogue

Frontend-only feature — no backend domain objects, API routes, CLI commands, telemetry events, or fixtures are introduced or changed. The catalogue below lists the frontend-internal contracts this feature creates or changes.

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-049-01 | `useAppToast()` composable — wraps Nuxt UI's `useToast()`; exposes `add({ severity, summary, detail, life })` matching the app's existing PrimeVue-era call shape, translated internally to Nuxt UI's `toast.add({ color, title, description, duration })` | `resources/js/composables/useAppToast.ts` (new) |
| DO-049-02 | `useConfirmDialog()` composable — Promise-based `confirm({ title, message, acceptLabel?, rejectLabel?, severity? }): Promise<boolean>`, backed by a singleton `<UModal>` host | `resources/js/composables/useConfirmDialog.ts` (new), `resources/js/components/modals/ConfirmModalHost.vue` (new) |

### API Routes / Services

None — no backend/REST changes.

### CLI Commands / Flags

None.

### Telemetry Events

None — no telemetry changes; this feature has no backend or logging component.

### Fixtures & Sample Data

None.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-049-01 | Foundation installed, PrimeVue/Nuxt UI coexisting | Nuxt UI plugin registered; no visible change to any existing PrimeVue-rendered view |
| UI-049-02 | Toast shown via `useAppToast()` | Any migrated action that previously called `primevue/usetoast`'s `useToast()` now renders via Nuxt UI's toast host with matching severity color |
| UI-049-03 | Confirm modal shown via `useConfirmDialog()` | A migrated destructive action shows the new modal; resolves `true`/`false` on confirm/cancel |
| UI-049-04 | Migration-complete state | `grep -rl "primevue\|@primeuix" resources/js` returns zero files; `package.json` has no PrimeVue-family dependencies; every view renders via Nuxt UI only |

## Telemetry & Observability

Not applicable — this is a frontend UI-library migration with no telemetry, logging, or observability surface. No new events, fields, or redaction rules are introduced.

## Documentation Deliverables

- `docs/specs/3-reference/coding-conventions.md` — update the `### UI Components` subsection (under `## Vue3/TypeScript Conventions`) to reference Nuxt UI instead of PrimeVue (FR-049-19).
- `docs/specs/4-architecture/knowledge-map.md` — update the `### Frontend (Vue3/TypeScript)` and `### Frontend Dependencies` sections to reference Nuxt UI, `useAppToast()`, and `useConfirmDialog()` as new architectural seams (FR-049-19).
- `docs/specs/4-architecture/roadmap.md` — add Feature 049 to Active Features on spec creation; move to Completed once FR-049-18's removal gate is satisfied.

## Fixtures & Sample Data

None — frontend-only, no backend fixtures affected.

## Spec DSL

```yaml
domain_objects:
  - id: DO-049-01
    name: useAppToast
    fields:
      - name: severity
        type: "'success' | 'info' | 'warn' | 'error'"
        constraints: "mapped internally to Nuxt UI's toast color"
      - name: summary
        type: string
      - name: detail
        type: string
        constraints: optional
      - name: life
        type: number
        constraints: "milliseconds, optional, default matches current app-wide default"
  - id: DO-049-02
    name: useConfirmDialog
    fields:
      - name: title
        type: string
      - name: message
        type: string
      - name: acceptLabel
        type: string
        constraints: optional
      - name: rejectLabel
        type: string
        constraints: optional
      - name: severity
        type: "'danger' | 'warning' | 'info'"
        constraints: optional

ui_states:
  - id: UI-049-01
    description: Foundation installed, PrimeVue/Nuxt UI coexisting, no visual change
  - id: UI-049-02
    description: Toast shown via useAppToast()
  - id: UI-049-03
    description: Confirm modal shown via useConfirmDialog()
  - id: UI-049-04
    description: Migration-complete state — zero PrimeVue imports/dependencies remain
```

## Appendix

### PrimeVue Usage Inventory (2026-07-02 snapshot)

Full component-by-component, file-count inventory backing the requirements above (component name — file count — representative paths):

| Component | Files | Notes |
|---|---|---|
| Button | 154 | → `UButton` (FR-049-06) |
| useToast | 119 (no wrapper) | → `useAppToast()` (FR-049-04) |
| Dialog | 55 | → `UModal` (FR-049-07) |
| Toolbar | 42 | → composed flex header, no direct equivalent (FR-049-08) |
| ProgressSpinner | 41 | → custom spinner / `UButton loading` (FR-049-09) |
| Card | 36 | → `UCard` (FR-049-10) |
| Panel | 35 | → `UCard`/plain div (FR-049-10) |
| Select | 27 | → `USelect`/`USelectMenu` (FR-049-11) |
| FloatLabel | 26 | → `UFormField` (FR-049-11) |
| ScrollPanel | 23 | → native overflow + Tailwind (FR-049-10/14) |
| Checkbox | 23 | → `UCheckbox` (FR-049-11) |
| ToggleSwitch / InputSwitch | 18 | → `USwitch` (FR-049-11) |
| Tag | 10 | → `UBadge` (FR-049-14) |
| DataTable / Column | 10 each | → `UTable` (FR-049-13) |
| ToastService (plugin) | 10 (setup) | removed with FR-049-18 |
| Divider | 9 | → `USeparator` (FR-049-10) |
| ContextMenu | 9 | → `UContextMenu`/`UDropdownMenu` (FR-049-12) |
| Message | 8 | → `UAlert` (FR-049-14) |
| ScrollTop | 7 | → custom component (FR-049-14) |
| InputNumber | 7 | → `UInputNumber` (FR-049-11) |
| ProgressBar | 6 | → `UProgress` (FR-049-09) |
| AutoComplete | 5 | → `USelectMenu` (searchable) (FR-049-11) |
| PassThrough (`:pt=`) | 6 (object form) + 36 (shorthand) | → `:ui=` (FR-049-16) |
| SelectButton | 4 | → `UButtonGroup` (FR-049-14) |
| InputText | 4 direct (+ wrapper) | → `UInput` (FR-049-11) |
| useConfirm | 3 | → `useConfirmDialog()` (FR-049-05) |
| ConfirmDialog | 3 | → `ConfirmModalHost.vue` (FR-049-05) |
| Menu | 3 | → `UNavigationMenu`/`UDropdownMenu` (FR-049-12) |
| Drawer | 3 | → `USlideover` (FR-049-12) |
| Paginator | 3 | → `UPagination` (FR-049-14) |
| OverlayBadge | 3 | → `UChip`/`UBadge` (FR-049-14) |
| VirtualScroller | 2 | → `@tanstack/vue-virtual` or custom (FR-049-14) |
| Timeline | 2 | → custom composition (FR-049-14) |
| Tabs family | 2 | → `UTabs` (FR-049-14) |
| Textarea | 2 direct (+ wrapper) | → `UTextarea` (FR-049-11) |
| DatePicker | 2 | → Reka UI date primitives (FR-049-11) |
| SpeedDial | 2 | → composed `UButton` group (FR-049-14) |
| MeterGroup | 2 | → `UProgress` (FR-049-09) |
| InputGroup / InputGroupAddon / IconField / InputIcon | 2 each | → `UInput` slots (FR-049-11) |
| MultiSelect | 1 | → `USelectMenu` (multiple) (FR-049-11) |
| Listbox | 1 | → `USelectMenu` (FR-049-11) |
| Password | 1 (+ wrapper) | → `UInput type=password` (FR-049-11) |
| RadioButton | 1 | → `URadioGroup` (FR-049-11) |
| Stepper family | 1 each | → composed `UButton`/`UProgress` (FR-049-14) |
| Fieldset | 1 (+ wrapper) | → `UCard`/`fieldset` (FR-049-10) |
| Tooltip (directive) | global | → `UTooltip` component (FR-049-17) |
| Ripple (directive) | global | removed, no replacement (FR-049-17) |
| FocusTrap (directive) | global | removed, replaced by Reka UI internal trapping (FR-049-17) |
| Config (plugin) | 1 (setup) | removed with FR-049-18 |
| ConfirmationService (plugin) | 1 (setup) | removed with FR-049-05/18 |

### Icons

- `primeicons` (`pi pi-*`): 562 occurrences across 139 files → migrate to `@iconify-json/prime` via `<UIcon name="i-prime-...">` (FR-049-15).
- `@fortawesome/fontawesome-free`: effectively unused (2 files: `components/forms/auth/LoginForm.vue`, `services/oauth-service.ts`) — not part of this migration's scope; left as-is.
- Existing chokepoint: `resources/js/components/icons/PiMiniIcon.vue` — repointed to `<UIcon>` first, reducing (not eliminating) the remaining direct `pi pi-*` string surface.

### Styling coupling

- `tailwindcss-primeui` generates utility classes (`bg-primary-500`, `text-surface-700`, `text-muted-color`, etc.) used in 197 files — remapped to Nuxt UI's own CSS-variable-driven semantic color utilities as each file migrates (tracked per-increment in plan.md, not exhaustively enumerated here).
- `resources/js/style/preset.ts` (~500 lines) — re-authored as Nuxt UI's theme configuration (FR-049-02); deleted once no longer referenced (FR-049-18).
- `.p-*` selector / `--p-*` CSS-variable usage in component `<style>` blocks: 36 / 39 files respectively — removed as each host component migrates, since these targeted PrimeVue's internal class/variable names directly.

### Related Components

- `resources/js/app.ts` — plugin registration (PrimeVue → Nuxt UI)
- `resources/js/views/App.vue` — app shell singleton
- `resources/js/menus/LeftMenu.vue` — primary navigation shell (highest-complexity single-file migration)
- `resources/js/stores/ModalsState.ts` — dialog-visibility store, pattern preserved (`visible` → `open` prop rename only)
- `resources/js/components/icons/PiMiniIcon.vue` — icon chokepoint
- `resources/js/components/forms/basic/*.vue` — form-primitive wrapper seam (8 files)
- `resources/js/embed/**` — explicitly out of scope, verified untouched (FR-049-21)
- `docs/specs/3-reference/coding-conventions.md`, `docs/specs/4-architecture/knowledge-map.md` — documentation deliverables (FR-049-19)

---

*Last updated: 2026-07-02*
