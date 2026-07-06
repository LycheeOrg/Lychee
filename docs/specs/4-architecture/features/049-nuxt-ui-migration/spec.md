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

A fourth, architecturally significant question was raised after ADR-0005 and is recorded in **ADR-0006** (`docs/specs/6-decisions/ADR-0006-nuxt-ui-dual-tree-toggle.md`) (Q-049-04), which **amends ADR-0005's implementation mechanism** (Q-049-01/02/03 above are unaffected):
- **Q-049-04** (coexistence/cutover mechanism): rather than migrating `resources/js/` in place file-by-file with the two libraries transitionally coexisting in one bundle, Nuxt UI is built as a **parallel tree** (`resources/js/v8/**`), served from a **second Vite entry** (`resources/js/app-v8.ts`), selected **per HTTP request** by a new Laravel feature flag (`Features::active('nuxt_ui')`, `config/features.php`) branching `resources/views/vueapp.blade.php`. Both bundles register **identical route paths** (via a new shared, component-free manifest `resources/js/router/paths.ts`), so both UIs are reachable at the same URLs — no `/v8/*` prefix, no subdomain. `resources/js/views/**`/`components/**`/`menus/**` (v7, PrimeVue) are **not edited** by this feature until cutover; they receive only their normal, independent maintenance in the meantime. Cutover (flipping the flag on for real, then deleting v7 + PrimeVue) requires every route to have a working `v8/` implementation first (a coverage gate — see FR-049-23).

**Affected modules:** Frontend only (`resources/js/`, `resources/sass/app.css`, `vite.config.ts`, `package.json`), plus one small backend touch point: `config/features.php` (new `nuxt_ui` flag) and `resources/views/vueapp.blade.php` (branches which compiled bundle to serve). No other backend (PHP), REST, or CLI changes. `docs/specs/3-reference/coding-conventions.md` (UI Components section) and `docs/specs/4-architecture/knowledge-map.md` (Frontend Dependencies section) are updated as documentation deliverables once the migration completes and v7 is removed.

## Goals

- Build a complete Nuxt UI implementation of every PrimeVue-coupled file (everything reachable from `resources/js/app.ts`) as a **parallel tree** under `resources/js/v8/**`, served by a **second Vite entry** (`resources/js/app-v8.ts`), selected per request by a **feature flag** (`Features::active('nuxt_ui')`) — rather than editing `resources/js/views/**`/`components/**`/`menus/**` in place (Q-049-04, ADR-0006). Every PrimeVue component/directive/service/theme construct gets a Nuxt UI equivalent or a small custom composable/component built on Nuxt UI primitives in the `v8/` tree, matching the existing coding convention of "build custom components on top of [the UI library's] primitives."
- Register **identical route paths** in both the v7 (`resources/js/router/routes.ts`) and v8 (`resources/js/v8/router/routes.ts`) routers, factored from one shared, component-free manifest (`resources/js/router/paths.ts`), so both UIs are reachable at the same URLs regardless of which is active.
- Share everything that isn't PrimeVue-coupled between both bundles, unduplicated: Pinia stores, services, most composables, types, utils, i18n, the Axios config. Only the UI/template layer is built twice.
- Preserve the current visual identity in the v8 tree: same primary color (sky-based), same light/dark surface scales, same icon set (via Iconify `prime`), same layout and information hierarchy across every view. This is a **library migration**, not a redesign.
- Preserve the existing server-config-driven dark mode toggle (`document.body.classList` + `dark_mode_enabled` config) unchanged in behavior, in both bundles.
- Preserve the existing Pinia-store-driven modal visibility pattern (`resources/js/stores/ModalsState.ts`, shared unchanged by both bundles) — v8 dialogs use `v-model:open` against the same store flags v7's `v-model:visible` uses.
- Introduce `useAppToast()` and `useConfirmDialog()` (backed by `<UModal>`) as new composables living under `resources/js/v8/composables/` (they depend on Nuxt UI's `useToast()`/`<UApp>`, which only exist in the v8 bundle) to replace PrimeVue's direct `useToast()` call sites and `ConfirmationService`/`useConfirm()`/`<ConfirmDialog>` — v7's existing call sites are untouched, since v7 keeps using PrimeVue's own APIs until it is deleted at cutover.
- Reach a **route-parity coverage gate** — every path in `resources/js/router/paths.ts` resolves to a working `v8/views/**` component — before the `nuxt_ui` flag can be enabled anywhere real users are served.
- After cutover is confirmed stable, delete `resources/js/views/**`/`components/**`/`menus/**` (v7), `resources/js/app.ts`, `resources/js/style/preset.ts`, the `nuxt_ui` flag, and remove `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, and `primeicons` from `package.json`.
- Keep `resources/js/embed/**` and its build output (`public/embed/lychee-embed.js`, `public/embed/lychee-embed.css`) untouched — verify no Nuxt UI (or PrimeVue) code is pulled into that bundle from either app tree.
- Keep `npm run check` (`vue-tsc --noEmit`) green throughout every increment — it must cover both `app.ts` and `app-v8.ts` entry points for the entire build-out window, not just transitionally.
- Update `docs/specs/3-reference/coding-conventions.md` and `docs/specs/4-architecture/knowledge-map.md` to reference Nuxt UI instead of PrimeVue once cutover and v7 removal complete.

## Non-Goals

- Any visual/design-language redesign beyond what dropping the ripple effect (Q-049-03) necessarily changes. Icon shapes, color palette, spacing, and component layout stay the same (Q-049-02).
- Migrating `resources/js/embed/**` to Nuxt UI or any UI library — it remains dependency-free.
- Introducing a frontend automated test suite (unit/component tests) as part of this migration. Verification is manual/browser-based, consistent with current project practice. (A follow-up feature could introduce Vitest coverage, but that is out of scope here.)
- Migrating the full app to the Nuxt meta-framework (SSR, file-based routing, Nuxt modules). Nuxt UI is used purely as a component library inside the existing Vite + Vue Router SPA.
- Rebuilding `resources/js/style/preset.ts`'s color math from scratch — the goal is visual parity with the existing preset, not a new palette.
- Adding automated visual-regression tooling (e.g. Percy/Chromatic) to verify pixel parity. Manual browser comparison is the verification method (see NFR-049-06).
- Editing any file under `resources/js/views/**`, `resources/js/components/**`, or `resources/js/menus/**` (v7) as part of this migration (Q-049-04). They are the reference implementation for the `v8/` rebuild and continue to receive their own independent maintenance; they are only deleted, wholesale, after cutover (FR-049-24).
- Per-route or per-user partial cutover in production. The `nuxt_ui` flag switches the entire app for a given environment — there is no mechanism (or plan to build one) for some routes or some users to see v8 while others see v7 within the same environment. Partial-coverage testing of the `v8/` tree is a development/staging convenience only.
- A path-prefix (`/v8/*`) or subdomain split between the two UIs — explicitly rejected in favor of same-path serving (Q-049-04 Option B).

## Functional Requirements

> **Reading note (Q-049-04, ADR-0006):** FR-049-06 through FR-049-17 below describe component-family conversions (Button→UButton, Dialog→UModal, etc.). Under the original ADR-0005 mechanism these were in-place edits to `resources/js/views/**`/`components/**`/`menus/**`. Under ADR-0006 they instead mean: **build the equivalent file fresh under `resources/js/v8/**`**, using the v7 file at the same relative path as the reference for behavior/props/slots, then wire it into `resources/js/v8/router/routes.ts`. The v7 file itself is not edited and stays the reference implementation until cutover (FR-049-24). File counts and directory names in these FRs describe the v7 inventory being mirrored 1:1, not files being edited in place.

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-049-01 | Install Nuxt UI in standalone Vue mode as a second, independent bundle | `@nuxt/ui` is added to `package.json`; a new entry `resources/js/app-v8.ts` (+ `resources/sass/app-v8.css`) registers the `ui()` Vite plugin, the Nuxt UI Vue plugin, and a new `resources/js/v8/views/App.vue` root wrapped in `<UApp>` (required for Toast/Tooltip/programmatic overlays); both entries are added to `vite.config.ts`'s `laravel-vite-plugin` `input` array. `resources/js/app.ts` (v7) is **not modified**. | App boots identically via `app.ts` (PrimeVue, unchanged) when `Features::active('nuxt_ui')` is false, and boots a minimal Nuxt-UI-only shell via `app-v8.ts` when true; no console errors in either mode; PrimeVue's rendered views are byte-for-byte unchanged since v7 is untouched. | `npm run check` (vue-tsc) passes for both entry points; `tsconfig.app.json` includes Nuxt UI's generated `auto-imports.d.ts`/`components.d.ts` for the v8 entry. | If the Vite plugin conflicts with `laravel-vite-plugin`/`@tailwindcss/vite` ordering, build fails — resolved before any v8-tree component work proceeds. Because the two bundles are never loaded in the same document, no runtime double-application risk exists (unlike a single-bundle coexistence approach). | No telemetry (frontend-only). | Q-049-01, Q-049-04 |
| FR-049-02 | Recreate theme tokens for visual parity | A Nuxt UI theme configuration (CSS `@theme`/`app.config.ts` per Nuxt UI's Vue-mode conventions, scoped to the `v8/` tree) defines: primary color = the same `sky` Tailwind scale currently in `preset.ts`'s `semantic.primary`; light-mode surface scale = `slate` (matches `colorScheme.light.surface`); dark-mode surface scale = `zinc` (matches `colorScheme.dark.surface`); the same focus-ring, border-radius, and form-field padding tokens where Nuxt UI exposes equivalent knobs. | Buttons, form fields, and surfaces in the `v8` bundle render with the same colors the `v7`/PrimeVue bundle renders, for a sampled set of views (Home, Album, Settings, Admin Dashboard), verified with `Features::active('nuxt_ui')` toggled on in a dev/staging environment. | Side-by-side manual comparison against `resources/js/style/preset.ts` values (kept as the source of truth for the target palette in the shared repo until fully removed in FR-049-24). | Where Nuxt UI has no equivalent token (e.g. PrimeVue's per-component `formField.paddingX`), the closest Tailwind Variants override on the affected Nuxt UI component is used instead. | No telemetry. | Q-049-02, NFR-049-01 |
| FR-049-03 | Build the v8 app shell (`resources/js/v8/views/App.vue`) | A new `resources/js/v8/views/App.vue` (not an edit of the existing `resources/js/views/App.vue`) wraps its root in `<UApp>`; Nuxt UI's implicit toast host replaces `<Toast />`; the new `ConfirmModalHost.vue` (FR-049-05) replaces `<ConfirmDialog />`; `<LeftMenu />` and `<EmbedCodeDialog />` are mounted the same way, from their own `v8/menus/`/`v8/components/` twins (FR-049-08/FR-049-07). | v8 app shell renders (behind the `nuxt_ui` flag) with the same structure as v7's; toasts and confirms appear as global overlays above all routed content in both bundles. | Manual verification with the flag on in dev/staging: trigger a toast and a confirm dialog from any view, confirm they render above `<main>`. | N/A | No telemetry. | Inventory §9, Q-049-04 |
| FR-049-04 | Build v8's toast usage via a new `useAppToast()` composable | A new composable `resources/js/v8/composables/useAppToast.ts` (not `resources/js/composables/` — it depends on Nuxt UI's `useToast()`, only present under `<UApp>` in the v8 bundle) wraps Nuxt UI's `useToast()`, exposing the same call shape the app already uses (`{ severity/color, summary/title, detail/description, life/duration }`, mapped to Nuxt UI's `toast.add({...})` fields). Every `.vue`/`.ts` file in the `v8/` tree that needs a toast (mirroring the 119 `primevue/usetoast` call sites in v7) imports and calls `useAppToast()`. v7's existing `primevue/usetoast` call sites are untouched. | Every toast-producing action in the v8 bundle (upload errors, save confirmations, etc.) shows a Nuxt UI toast with the correct severity color and message, matching its v7 counterpart. | Grep-verified: zero `from "primevue/usetoast"` imports anywhere under `resources/js/v8/`. | N/A | No telemetry. | Inventory §5, Q-049-04 |
| FR-049-05 | Build a custom confirm composable for v8 | A new composable `resources/js/v8/composables/useConfirmDialog.ts` (backed by a new singleton component `resources/js/v8/components/modals/ConfirmModalHost.vue`, using `<UModal>`) exposes a Promise-based `confirm({ title, message, ... }): Promise<boolean>` API. The singleton host is mounted once in `resources/js/v8/views/App.vue`. The v8 twins of the 3 existing `useConfirm()` call sites (`v8/views/RenamerRules.vue`, `v8/views/admin/ContactMessages.vue`, `v8/views/admin/UserGroups.vue`) call the new composable; v7's originals keep using `primevue/useconfirm` untouched. | Calling `confirm({...})` in the v8 tree shows a modal; resolving `true`/`false` on confirm/cancel matches v7's current `accept`/`reject` callback behavior. | Grep-verified: zero `from "primevue/useconfirm"` or `from "primevue/confirmdialog"` imports anywhere under `resources/js/v8/`. | N/A | No telemetry. | Inventory §2, §5, Q-049-04 |
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
| FR-049-18 | Remove PrimeVue dependencies (post-cutover) | Once cutover (FR-049-24) is confirmed stable — `nuxt_ui` has been the sole path for the environment and no rollback is anticipated — `resources/js/views/**`, `resources/js/components/**`, `resources/js/menus/**` (v7), `resources/js/app.ts`, `resources/js/style/preset.ts`, the `nuxt_ui` flag (`config/features.php`), and the `@if`/`@else` branch in `resources/views/vueapp.blade.php` are all deleted; `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, and `primeicons` are removed from `package.json` `dependencies`; the `@plugin "tailwindcss-primeui";` line is removed from `resources/sass/app.css`. | `npm install` produces a lockfile with zero PrimeVue-family packages; `npm run build` succeeds with a single entry point again; app boots and renders via the (formerly v8) tree with no flag branch remaining. | `grep -rl "primevue\|@primeuix" resources/js` returns zero files before this task is marked complete; `resources/views/vueapp.blade.php` has a single unconditional `@vite([...])`. | This task is blocked until FR-049-24's cutover has been stable for a deliberate observation period (no partial removal while the flag still exists as an active rollback path). | No telemetry. | FR-049-22..24 completion gate |
| FR-049-19 | Update governing documentation | `docs/specs/3-reference/coding-conventions.md`'s "UI Components" section (`### UI Components` under `## Vue3/TypeScript Conventions`) is updated to say "Use Nuxt UI for UI components" (replacing the PrimeVue reference). `docs/specs/4-architecture/knowledge-map.md`'s "Frontend Dependencies" and "Components" sections are updated to reference Nuxt UI instead of PrimeVue. | Both documents accurately describe the post-migration stack with no stale PrimeVue references. | Manual review of both files' diffs against this spec's FR list. | N/A | No telemetry. | AGENTS.md documentation-sync requirement |
| FR-049-20 | Preserve dark mode toggle behavior | The existing server-config-driven dark mode mechanism (`document.body.classList.add/remove("dark")` in `components/settings/General.vue` and `views/admin/Settings.vue`, driven by the `dark_mode_enabled` config and set server-side in `resources/views/vueapp.blade.php`) is preserved unchanged. Nuxt UI's dark-mode styling is configured to key off the same `.dark` class on `<body>` (Tailwind's `@custom-variant dark (&:where(.dark, .dark *));` in `resources/sass/app.css` already targets this and needs no change). | Toggling dark mode in Settings still flips every migrated component's appearance immediately, with no page reload required. | Manual toggle test after each major increment (foundation, then periodically through component migration) to catch any Nuxt UI component that doesn't respect the shared `.dark` selector. | N/A | No telemetry. | Inventory §8 |
| FR-049-21 | Keep the embed bundle untouched | `resources/js/embed/**` continues to import nothing from PrimeVue, Nuxt UI, `resources/js/app.ts`, `resources/js/app-v8.ts`, `resources/js/v8/**`, `resources/js/style/`, or any `views/`/`components/` file that depends on either UI library. `vite.embed.config.ts`'s build output (`public/embed/lychee-embed.js`, `public/embed/lychee-embed.css`) is verified to have no new UI-library code inlined. | `npm run build:embed` output file size stays within the same order of magnitude as before migration (no accidental PrimeVue or Nuxt UI inlining from either tree). | Compare `public/embed/lychee-embed.js` file size/hash before and after each increment that touches shared (`@/`-aliased) files either bundle might import. | If the embed bundle grows unexpectedly, identify and remove the accidental shared-file dependency before proceeding. | No telemetry. | Inventory §7, NFR-049-04 |
| FR-049-22 | Toggle & dual-bundle scaffolding (Q-049-04, ADR-0006) | A new boolean flag `nuxt_ui` is added to `config/features.php`; `resources/views/vueapp.blade.php` branches its `@vite([...])` include on `Features::active('nuxt_ui')` between `app.ts`/`app.css` (v7) and `app-v8.ts`/`app-v8.css` (v8); a new shared, component-free route manifest `resources/js/router/paths.ts` (`{ name, path, meta }` only) is factored out of the existing `resources/js/router/routes.ts`, which is updated to consume it; a new `resources/js/v8/router/routes.ts` consumes the same manifest and maps each entry to a `v8/views/**` component. | With the flag off, the app is byte-for-byte the current app (v7, PrimeVue). With the flag on (dev/staging), the app boots the v8 shell (FR-049-03) at the same route paths as v7, even before any other view is migrated (unmigrated routes simply have no `v8/views/**` component yet). | `npm run check`, `npm run dev` with the flag toggled both ways; confirm `resources/js/router/routes.ts`'s and `resources/js/v8/router/routes.ts`'s path/name lists are identical (derived from the same `paths.ts`, so this is structural, not just tested). | If `laravel-vite-plugin`'s multi-entry output conflicts with either Vite plugin's asset naming, resolved before any v8-tree component work proceeds. | No telemetry. | Q-049-04 |
| FR-049-23 | Route-parity coverage gate | Before `nuxt_ui` is enabled anywhere real users are served, every `{ name, path }` entry in `resources/js/router/paths.ts` must resolve to a working, manually-verified `v8/views/**` component in `resources/js/v8/router/routes.ts` — no route may fall back to a v7 component or render blank. | A coverage checklist (one row per route in `paths.ts`) is fully checked off, each row verified via `npm run dev` with the flag on, in both light and dark mode. | Automated: a script or manual pass confirms `v8/router/routes.ts` has no missing/placeholder entries for any path present in `paths.ts`. | If any route lacks a `v8/` implementation, cutover (FR-049-24) is blocked for that environment. | No telemetry. | Q-049-04 |
| FR-049-24 | Cutover | Once FR-049-23's coverage gate passes and a dogfood/staging period on the v8 bundle finds no regressions, `nuxt_ui` is enabled for real traffic (flag flip, no redeploy of application code required). The flag is left in place for a deliberate rollback-observation window before FR-049-18's dependency/tree removal proceeds. | Users on the environment see the v8 (Nuxt UI) app at every route, with no broken links, no visual regression vs. the v7 baseline, and no behavioral regression (toasts, confirms, dialogs, navigation). | Full manual smoke test across every major view/flow in both light and dark mode (same scope as the original T-049-43 smoke test), performed against the flagged-on environment. | If a regression is found post-flip, the flag is flipped back to v7 immediately (instant rollback) and the specific `v8/` gap is fixed before re-attempting cutover. | No telemetry. | Q-049-04, FR-049-23 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-049-01 | Visual parity (colors, icons, layout) | UX continuity — this is a library migration, not a redesign (Q-049-02) | Manual side-by-side comparison of each migrated view against its pre-migration rendering; primary color, surface scales, and icon glyphs must match. | `resources/js/style/preset.ts` (kept as reference until FR-049-18), `@iconify-json/prime` | Q-049-02 |
| NFR-049-02 | `npm run check` stays green throughout | Prevents type-checking regressions from blocking other in-flight work during a long-running migration | `vue-tsc --noEmit -p tsconfig.json --composite false --skipLibCheck` passes after every increment for **both** `app.ts` and `app-v8.ts` entry points, not just at the end | Nuxt UI's generated `auto-imports.d.ts`/`components.d.ts` registered in `tsconfig.app.json` | AGENTS.md quality gate |
| NFR-049-03 | No automated frontend regression suite exists — verification is manual | No `resources/js/**/*.test.ts` files exist in the repo today; introducing one is out of scope (see Non-Goals) | Each increment's exit criteria include an explicit manual browser verification step (dev server, representative views, both light and dark mode) per AGENTS.md's "start the dev server and use the feature in a browser" rule for UI changes | `npm run dev` | Inventory §6 |
| NFR-049-04 | Embed bundle stays dependency-free and near-constant size | The embeddable widget is distributed to third-party sites; bundle bloat directly affects host-page load time | `public/embed/lychee-embed.js` size compared before/after each increment touching shared files; zero PrimeVue/Nuxt UI imports found via grep in `resources/js/embed/`, from either the v7 or v8 tree | `vite.embed.config.ts` | Inventory §7 |
| NFR-049-05 | Accessibility parity or improvement for interactive primitives | Reka UI (Nuxt UI's underlying primitive library) ships WAI-ARIA-compliant, keyboard-accessible modal/menu/dropdown primitives out of the box | Manual keyboard-navigation spot-check (Tab/Escape/Arrow keys) on migrated modals, drawers, and menus | Reka UI (transitive Nuxt UI dependency) | Inventory §5, §9 |
| NFR-049-06 | Increments stay ≤90 minutes despite overall feature size | AGENTS.md planning guardrail; a 235-file migration must be decomposed into many small, independently-shippable increments rather than attempted as one large change | plan.md's Increment Map groups work by component family/layer, each sized to fit the guardrail (finer-grained sub-increments — e.g. I7a/I7b — where a single component family still exceeds it) | plan.md Increment Map | AGENTS.md |
| NFR-049-07 | PrimeVue/Nuxt UI coexistence is transitional only, and never in the same document | Avoids a permanently mixed-UI-library codebase (per Q-049-01 Option A's rejection of open-ended coexistence); the dual-tree/toggle mechanism (Q-049-04, ADR-0006) additionally guarantees the two libraries are never loaded in the same page during the transitional period | FR-049-18 is a hard completion gate — the feature is not considered done, and the roadmap entry is not moved to Completed, until zero PrimeVue imports remain, `resources/js/v8/**` has replaced `resources/js/views\|components\|menus/**` at the same paths, and the dependencies are removed from `package.json` | All FR-049-01..05, FR-049-22..24 | Q-049-01, Q-049-04 |
| NFR-049-08 | Both bundles build and type-check together for the full implementation window | `npm run build`/`npm run check` must cover `app.ts` and `app-v8.ts` from FR-049-22 (toggle scaffolding) through FR-049-18 (removal) — a materially longer window than a single-bundle migration's transitional-only build overhead | Both entries present in `vite.config.ts`'s `input` array and `tsconfig.app.json`'s scope for the entire duration; CI build/type-check time is monitored for regressions attributable to carrying two bundles | `vite.config.ts`, `tsconfig.app.json` | Q-049-04, ADR-0006 |
| NFR-049-09 | Cutover is reversible with no redeploy | The `nuxt_ui` flag must remain a safe, instant rollback path from the moment FR-049-24 flips it until FR-049-18 deletes it | Flipping `Features::active('nuxt_ui')` back to `false` in `config/features.php` (config/env change only) restores the exact pre-cutover v7 behavior, verified once immediately after cutover and once before FR-049-18 proceeds | `config/features.php`, `resources/views/vueapp.blade.php` | Q-049-04, ADR-0006 |

## UI / Interaction Mock-ups

The migration preserves layout and information hierarchy; only the underlying component implementation changes. The app shell (left navigation + toolbar header + content) looks the same before and after — annotations below show which PrimeVue construct maps to which Nuxt UI construct at each point in the shell.

Per Q-049-04/ADR-0006, "before" and "after" below are **not** the same running app at different points in time — they are two separate bundles (`app.ts` vs `app-v8.ts`) served at the **same URL paths**, selected per request by `Features::active('nuxt_ui')`:

```
Request for /gallery/123
        │
        ▼
resources/views/vueapp.blade.php
        │
   Features::active('nuxt_ui') ?
     │                    │
    false                true
     │                    │
     ▼                    ▼
@vite(app.ts)        @vite(app-v8.ts)
     │                    │
     ▼                    ▼
resources/js/         resources/js/v8/
  views/router          router/routes.ts  ─┐
  routes.ts  ───────────────────────────────┴─ both built from
                                                resources/js/router/paths.ts
                                                (same {name, path} list)
```

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
Dialog example (e.g. Album Properties) — same store-driven visibility, different component,
built as a new file under resources/js/v8/ rather than an edit to the v7 file shown on the left:

v7 (resources/js/components/forms/album/*.vue)   v8 (resources/js/v8/components/forms/album/*.vue)
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
| S-049-01 | Toggle scaffolding lands (FR-049-22) → with `nuxt_ui` off, app is byte-for-byte the current PrimeVue app; with it on (dev/staging), the v8 shell boots at the same route paths; `npm run check` passes for both entries |
| S-049-02 | Dark mode toggle (Settings → General) → in whichever bundle is active (v7 or v8), all its components flip appearance immediately without reload |
| S-049-03 | In the v8 bundle, an action that shows a PrimeVue toast in v7 (e.g. failed upload) shows a Nuxt UI toast via `useAppToast()`, same severity color and message |
| S-049-04 | In the v8 bundle, a destructive action requiring confirmation (e.g. deleting a webhook) shows the new `useConfirmDialog()` modal; confirming proceeds, cancelling aborts, matching v7's `useConfirm()` behavior |
| S-049-05 | A representative `Dialog`-based form (e.g. Album Properties rename), built fresh in `v8/components/forms/album/`, opens via its existing trigger, saves via the same shared store/service call, and closes on save/cancel/Escape/backdrop-click |
| S-049-06 | `v8/menus/LeftMenu.vue` (`<USlideover>` + `<UNavigationMenu>`) — drawer opens/closes, every nav item routes to the same paths as v7, unread/count badges still render |
| S-049-07 | A `DataTable`-based admin view (e.g. Webhooks), rebuilt in `v8/views/admin/` against `<UTable>`, retains sorting, pagination (where present), and row-action buttons |
| S-049-08 | A sampled set of ≥15 icons (nav, button, status) in the v8 tree renders identically (same glyph) to its v7 counterpart, via the Iconify `prime` collection (FR-049-15) |
| S-049-09 | In the v8 bundle, clicking any `<UButton>` shows no ripple animation (explicit non-behavior check per Q-049-03) |
| S-049-10 | Route-parity coverage gate (FR-049-23) passes — every path in `resources/js/router/paths.ts` resolves to a working `v8/views/**` component |
| S-049-11 | Cutover (FR-049-24) — `nuxt_ui` flipped on for real traffic; full smoke test finds no regression; flag flipped back to `false` and confirmed to instantly restore v7 behavior (rollback rehearsal) |
| S-049-12 | Final removal (FR-049-18) — `npm run build` succeeds with a single entry point, `primevue`/`@primeuix/themes`/`tailwindcss-primeui`/`primeicons` fully removed from `package.json`, `resources/js/views\|components\|menus/**` (v7) and the `nuxt_ui` flag deleted, and `grep -rl "primevue\|@primeuix" resources/js` returns zero files |
| S-049-13 | `npm run build:embed` output (`public/embed/lychee-embed.js`/`.css`) is unaffected by either tree — no new UI-library code inlined, file size stays in the same order of magnitude, at every increment boundary that touches `@/`-aliased shared files |
| S-049-14 | Keyboard navigation (Tab into a `v8` `<UModal>`, Escape to close) traps and restores focus correctly, verifying Reka UI's built-in focus-trap replaces the removed `v-focustrap` directive without regression |

## Test Strategy

- **UI (manual/browser):** Since no frontend automated test suite exists (NFR-049-03) and introducing one is out of scope, every increment's exit criteria requires starting `npm run dev` with `Features::active('nuxt_ui')` toggled on (dev/staging config) and manually exercising the newly-built `v8/` views/components in both light and dark mode, per AGENTS.md's frontend verification rule. Increments that touch the v8 shell components (`v8/views/App.vue`, `v8/menus/LeftMenu.vue`) require a broader smoke pass across multiple v8 views since regressions there are global to the v8 bundle. v7 is not exercised by these increments since it is not modified — its own existing manual-QA practice, unrelated to this feature, is unaffected.
- **Type safety:** `npm run check` (`vue-tsc --noEmit`) must pass after every increment (NFR-049-02) for **both** `app.ts` and `app-v8.ts` entry points — this is the only automated gate available for this feature and catches prop/type mismatches from the library swap even without behavioral test coverage.
- **Formatting:** `npm run format` (Prettier) after every increment per the standard frontend quality gate.
- **Build integrity:** `npm run build` (now covering `app.ts` and `app-v8.ts` together) and `vite build --config vite.embed.config.ts` must succeed at the end of every increment; the embed sub-build is specifically checked against NFR-049-04 (S-049-13).
- **Toggle/rollback:** After cutover (FR-049-24), a rollback rehearsal (flip `nuxt_ui` back to `false`, confirm v7 renders exactly as before) is required before FR-049-18's removal proceeds (S-049-11).
- **No backend/PHP test impact beyond the flag itself:** this feature's only backend touch points are the `nuxt_ui` entry in `config/features.php` and the `@if`/`@else` branch in `vueapp.blade.php` — no PHP business logic changes. `php artisan test`/`make phpstan` are not run for increments scoped to `resources/js/**` only, per AGENTS.md's "only run checks for file types that were modified" rule, but are run once for the increments that touch `config/features.php`/`vueapp.blade.php` (FR-049-22).

## Interface & Contract Catalogue

Almost entirely a frontend feature — no backend domain objects, CLI commands, telemetry events, or fixtures are introduced. One backend config flag is introduced (below). The catalogue below lists the frontend-internal contracts this feature creates or changes.

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-049-01 | `useAppToast()` composable — wraps Nuxt UI's `useToast()`; exposes `add({ severity, summary, detail, life })` matching the app's existing PrimeVue-era call shape, translated internally to Nuxt UI's `toast.add({ color, title, description, duration })` | `resources/js/v8/composables/useAppToast.ts` (new) |
| DO-049-02 | `useConfirmDialog()` composable — Promise-based `confirm({ title, message, acceptLabel?, rejectLabel?, severity? }): Promise<boolean>`, backed by a singleton `<UModal>` host | `resources/js/v8/composables/useConfirmDialog.ts` (new), `resources/js/v8/components/modals/ConfirmModalHost.vue` (new) |
| DO-049-03 | `router/paths.ts` — shared, component-free route manifest (`{ name, path, meta }`); single source of truth for the path/name list both `router/routes.ts` (v7) and `v8/router/routes.ts` (v8) attach components to | `resources/js/router/paths.ts` (new) |

### API Routes / Services

None — no new REST endpoints. One config flag: `nuxt_ui` in `config/features.php`, read via `App\Assets\Features::active('nuxt_ui')`, branching `resources/views/vueapp.blade.php`'s `@vite([...])` include (FR-049-22). Same class of flag as the existing `legacy_v4_redirect`.

### CLI Commands / Flags

None.

### Telemetry Events

None — no telemetry changes; this feature has no backend or logging component.

### Fixtures & Sample Data

None.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-049-01 | Toggle scaffolding installed, dual bundles both booting | `nuxt_ui` flag off → byte-for-byte v7 (PrimeVue); flag on (dev/staging) → v8 shell boots at the same paths; v7 is untouched either way |
| UI-049-02 | Toast shown via `useAppToast()` (v8 only) | Any v8-tree action that has a `primevue/usetoast` counterpart in v7 renders via Nuxt UI's toast host with matching severity color |
| UI-049-03 | Confirm modal shown via `useConfirmDialog()` (v8 only) | A v8-tree destructive action shows the new modal; resolves `true`/`false` on confirm/cancel, matching v7's `useConfirm()` behavior |
| UI-049-04 | Route-parity reached | Every path in `router/paths.ts` resolves to a working `v8/views/**` component; `nuxt_ui` is eligible to be enabled for real traffic (FR-049-23) |
| UI-049-05 | Cut over | `nuxt_ui` enabled for real traffic; v7 still present as an instant rollback path (FR-049-24) |
| UI-049-06 | Migration-complete state | `grep -rl "primevue\|@primeuix" resources/js` returns zero files; `package.json` has no PrimeVue-family dependencies; `resources/js/views\|components\|menus/**` (v7), `app.ts`, and the `nuxt_ui` flag are deleted; every view renders via Nuxt UI only, at a single entry point |

## Telemetry & Observability

Not applicable — this is a frontend UI-library migration with no telemetry, logging, or observability surface. No new events, fields, or redaction rules are introduced.

## Documentation Deliverables

- `docs/specs/3-reference/coding-conventions.md` — update the `### UI Components` subsection (under `## Vue3/TypeScript Conventions`) to reference Nuxt UI instead of PrimeVue (FR-049-19). Deferred until after cutover (FR-049-24) and removal (FR-049-18) — while `v7` is still the default, PrimeVue remains the accurate "current convention" to document.
- `docs/specs/4-architecture/knowledge-map.md` — update the `### Frontend (Vue3/TypeScript)` and `### Frontend Dependencies` sections to reference Nuxt UI, `useAppToast()`, and `useConfirmDialog()` as new architectural seams (FR-049-19), and to describe the (now-removed) `v8/` tree's former structure being promoted to the canonical one.
- `docs/specs/4-architecture/roadmap.md` — add Feature 049 to Active Features on spec creation; move to Completed once FR-049-18's removal gate is satisfied.
- ADR-0006 (`docs/specs/6-decisions/ADR-0006-nuxt-ui-dual-tree-toggle.md`) — already recorded; amends ADR-0005's implementation-mechanism decision.

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
    description: Toggle scaffolding installed — flag off is byte-for-byte v7, flag on (dev/staging) boots v8 shell at the same paths
  - id: UI-049-02
    description: Toast shown via useAppToast() (v8 tree only)
  - id: UI-049-03
    description: Confirm modal shown via useConfirmDialog() (v8 tree only)
  - id: UI-049-04
    description: Route-parity reached — every path in router/paths.ts has a working v8/views component
  - id: UI-049-05
    description: Cut over — nuxt_ui enabled for real traffic, v7 retained as rollback path
  - id: UI-049-06
    description: Migration-complete state — v7 tree and nuxt_ui flag deleted, zero PrimeVue imports/dependencies remain
```

## Appendix

### PrimeVue Usage Inventory (2026-07-02 snapshot)

Full component-by-component, file-count inventory backing the requirements above (component name — file count — representative paths). Per Q-049-04/ADR-0006, these counts describe the `resources/js/{views,components,menus}/**` (v7) surface being **mirrored** into `resources/js/v8/**`, not edited in place — see the reading note above the Functional Requirements table.

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

- `resources/js/app.ts` — v7 entry point, PrimeVue, **untouched** by this feature until FR-049-18
- `resources/js/app-v8.ts` — new v8 entry point, Nuxt UI (FR-049-01, FR-049-22)
- `resources/js/router/paths.ts` — new shared, component-free route manifest consumed by both routers (FR-049-22, DO-049-03)
- `config/features.php`, `resources/views/vueapp.blade.php` — new `nuxt_ui` flag and the bundle-selection branch (FR-049-22)
- `resources/js/views/App.vue` (v7) / `resources/js/v8/views/App.vue` (v8, new) — app shell singleton, built fresh for v8 rather than migrated (FR-049-03)
- `resources/js/menus/LeftMenu.vue` (v7) / `resources/js/v8/menus/LeftMenu.vue` (v8, new) — primary navigation shell (highest-complexity single-file build)
- `resources/js/stores/ModalsState.ts` — dialog-visibility store, **shared unchanged** by both bundles (v7 binds `visible`, v8 binds `open`, against the same flags)
- `resources/js/components/icons/PiMiniIcon.vue` (v7) / `resources/js/v8/components/icons/PiMiniIcon.vue` (v8, new) — icon chokepoint, one per tree
- `resources/js/v8/composables/useAppToast.ts`, `resources/js/v8/composables/useConfirmDialog.ts`, `resources/js/v8/components/modals/ConfirmModalHost.vue` — new v8-only architectural seams (FR-049-04, FR-049-05)
- `resources/js/embed/**` — explicitly out of scope, verified untouched by either tree (FR-049-21)
- `docs/specs/3-reference/coding-conventions.md`, `docs/specs/4-architecture/knowledge-map.md` — documentation deliverables, updated post-cutover (FR-049-19)
- `docs/specs/6-decisions/ADR-0006-nuxt-ui-dual-tree-toggle.md` — governing ADR for the dual-tree/toggle mechanism (Q-049-04)

---

*Last updated: 2026-07-02*
