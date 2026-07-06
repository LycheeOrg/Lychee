# Feature Plan 049 – Migration to Nuxt UI

_Linked specification:_ `docs/specs/4-architecture/features/049-nuxt-ui-migration/spec.md`
_Status:_ Draft
_Last updated:_ 2026-07-02

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Build a complete Nuxt UI implementation of the app as a **parallel tree** (`resources/js/v8/**`, served by a second Vite entry `resources/js/app-v8.ts`, selected per request by a `nuxt_ui` feature flag), reachable at the **same URL paths** as the existing PrimeVue app (`resources/js/app.ts`, left untouched), with zero visual regression (same colors, icons, layout — Q-049-02), zero behavioral regression (same dialog/toast/confirm/navigation interactions), a route-parity coverage gate before cutover, and a clean cutover + dependency removal at the end (FR-049-23, FR-049-24, FR-049-18). This mechanism supersedes the originally-planned in-place, file-by-file migration (see ADR-0006, Q-049-04); ADR-0005's other resolutions (full scope, icon parity, drop ripple) are unchanged. Success is measured by:
- Every route in `resources/js/router/paths.ts` resolving to a working `v8/views/**` component (coverage gate, FR-049-23) before cutover.
- `Features::active('nuxt_ui')` flipped on for real traffic with no regression found in a full manual smoke test (cutover, FR-049-24), and a rehearsed instant rollback (flip back to `false`) confirmed to restore v7 exactly.
- After cutover is stable: `grep -rl "primevue\|@primeuix" resources/js` returning zero files; `package.json` containing no `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, or `primeicons` entries; `resources/js/views\|components\|menus/**` (v7), `app.ts`, and the `nuxt_ui` flag deleted.
- `npm run check`, `npm run format`, `npm run build`, and `npm run build:embed` all passing throughout (covering both entry points until removal).
- Manual browser verification (light + dark mode) confirming no visual/behavioral regression across every `v8/` view, both before and after cutover.
- `docs/specs/3-reference/coding-conventions.md` and `docs/specs/4-architecture/knowledge-map.md` no longer referencing PrimeVue.

## Scope Alignment

- **In scope:** Building, under `resources/js/v8/**`, a Nuxt UI equivalent of every file under `resources/js/{views,components,menus}/**` that imports from `primevue/*` or `@primeuix/*` (235 files at spec time, mirrored not edited), a new Nuxt UI theme configuration for visual parity (reference: `resources/js/style/preset.ts`, ~500 lines, kept in the shared repo as read-only reference until FR-049-18), icon migration to the `v8/` tree (139 files' worth), directive removal within `v8/` (ripple/focustrap/tooltip — v7 keeps its originals until removal), the toggle/dual-bundle scaffolding itself (`config/features.php`, `vueapp.blade.php`, `router/paths.ts`, `vite.config.ts`), the route-parity coverage gate, cutover, and final dependency/tree removal.
- **Out of scope:** Editing any file under `resources/js/views/**`, `components/**`, `menus/**` (v7) — they are the reference implementation and are only deleted (not edited) at the end. `resources/js/embed/**` (zero PrimeVue/Nuxt UI coupling from either tree, explicitly verified untouched per NFR-049-04/S-049-13), introducing a frontend automated test suite, any visual/design redesign beyond dropping the ripple effect, migrating to the full Nuxt meta-framework, a path-prefix (`/v8/*`) or subdomain split (Q-049-04 Option B, rejected), per-route/per-user partial cutover in production.

## Dependencies & Interfaces

- **New npm dependencies:** `@nuxt/ui` (+ its transitive Reka UI / Tailwind Variants deps), `@iconify-json/prime` (dev dependency). Per AGENTS.md, adding dependencies requires explicit user approval — confirmed via the resolution of Q-049-01/02 (user selected the options that require these packages); reconfirm the exact package names/versions with the user immediately before I0 if not already implicitly approved.
- **Removed npm dependencies (at completion, FR-049-18):** `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons`.
- **New backend surface (small):** `nuxt_ui` boolean in `config/features.php`, read via `App\Assets\Features::active('nuxt_ui')` in `resources/views/vueapp.blade.php` (same mechanism as the existing `legacy_v4_redirect` flag) — FR-049-22.
- **Existing infrastructure reused unchanged, shared by both bundles:** Vue Router (route *paths*, via the new shared `router/paths.ts`), Pinia (+ `pinia-plugin-persistedstate`) and all existing stores, `vue-i18n`/`laravel-vue-i18n`, Tailwind CSS v4, Vite (`laravel-vite-plugin`'s multi-entry `input` array), `axios`/`axios-cache-interceptor`, and the vast majority of `composables/**`/`services/**`/`utils/**`/`types/**`.
- **Existing architectural seams leveraged:** `resources/js/stores/ModalsState.ts` (dialog-visibility flags — pattern preserved and shared unchanged; v7 binds `visible`, v8 binds `open`, against the same flags), the wrapper-component pattern in `resources/js/components/forms/basic/` (referenced when building v8's twins).
- **New architectural seams introduced:** `resources/js/router/paths.ts` (shared, component-free route manifest — new, DO-049-03); `resources/js/v8/composables/useAppToast.ts` + `useConfirmDialog.ts` + `resources/js/v8/components/modals/ConfirmModalHost.vue` (v8-only, since they depend on Nuxt UI's `useToast()`/`<UApp>`); one `PiMiniIcon.vue` per tree.

## Assumptions & Risks

- **Assumptions:**
  - Nuxt UI v3's standalone Vue mode (Vite plugin + Vue plugin, confirmed via `ui.nuxt.com/getting-started/installation/vue`) is stable enough for production use outside the Nuxt framework.
  - `@iconify-json/prime` (confirmed published on npm, v1.2.5) covers all 107 distinct icon names currently used via `pi pi-*`; any gap is handled case-by-case during I2/the icon sweep increments.
  - The project's Tailwind CSS v4 setup (`@tailwindcss/vite`) is compatible with Nuxt UI's own Vite plugin without ordering conflicts — verified in I0.
  - `laravel-vite-plugin`'s multi-entry `input` array (already proven for `resources/sass/app.css` + `resources/js/app.ts`, and by the separately-configured embed build) extends cleanly to a third/fourth entry (`app-v8.ts`, `app-v8.css`) without cross-bundle asset collisions — verified in I0.
  - No frontend automated tests exist today (confirmed) and none are added by this feature — verification is manual per increment, exercised against the `v8` tree with the flag toggled on in dev/staging.
  - A single global `Features::active('nuxt_ui')` flag (not per-user, not per-route) is sufficient — the user did not ask for per-user opt-in, only for the two UIs to coexist under the same paths behind a toggle.
- **Risks / Mitigations:**
  - **Risk:** Building a full parallel `v8/` tree is a strictly larger total surface than in-place editing (nothing is reused beyond using v7 files as a behavioral reference) — the 235-file scope is duplicated, not converted. **Mitigation:** accepted tradeoff per Q-049-04 (ADR-0006), in exchange for zero production risk during build-out and instant rollback; non-UI-coupled code (stores, services, most composables) stays shared and is not duplicated, keeping the actually-duplicated surface to the UI/template layer only.
  - **Risk:** No incremental production value until the route-parity coverage gate (FR-049-23) is met — unlike an in-place migration where every increment improves the one real app, progress here is invisible in production until cutover. **Mitigation:** the coverage gate plus a staging/dev dogfood period (using the flag) gives partial, pre-cutover validation without exposing real users to an incomplete `v8/` tree; increments are still ordered layer-by-layer (shell → toast/confirm → high-frequency components → niche components → cleanup) so the `v8/` tree itself is coherent at each checkpoint even though it isn't yet user-facing.
  - **Risk:** All-or-nothing cutover per environment — a route with no `v8/` implementation is simply broken if a user lands on it with the flag on. **Mitigation:** FR-049-23's coverage gate is a hard precondition to enabling the flag anywhere real users are served; `router/paths.ts` being the single source of truth for both routers makes "which routes are missing" a mechanical diff, not a manual audit.
  - **Risk:** Nuxt UI's `Toolbar`-equivalent gap (FR-049-08) requires a bespoke pattern across 42 files (mirrored into `v8/`) — inconsistent implementations could drift visually. **Mitigation:** I24 establishes one reference implementation (a small shared pattern/snippet, not a new component abstraction per AGENTS.md's anti-over-abstraction guidance) in `v8/components/headers/`, reused verbatim by subsequent Toolbar-building increments.
  - **Risk:** `DataTable`→`UTable` (FR-049-13) is a structural rewrite, not a mechanical swap — risk of the `v8/` table losing sort/pagination/row-action behavior present in its v7 counterpart. **Mitigation:** dedicated increments per table (I34-I36), each with an explicit before/after behavior checklist in its exit criteria, diffed directly against the still-running v7 table.
  - **Risk:** Embed bundle accidentally picks up a Nuxt UI **or** PrimeVue import via a shared `@/`-aliased file, from either tree. **Mitigation:** S-049-13 file-size/hash check after every increment that touches a file either bundle might import (composables, utils, types — not `views/`/`components/`/`v8/views/`/`v8/components/`, which the embed doesn't touch today per the inventory).
  - **Risk:** Losing visual parity on the ~500-line theme preset's per-component overrides (`components` key in `preset.ts`, covering ~35 PrimeVue components) since Nuxt UI's Tailwind-Variants system is structurally different. **Mitigation:** `preset.ts` is kept in the repo (unused, reference-only) until FR-049-18's final removal, so every `v8/`-tree increment can diff its target component's current styling against the preset's override for that component.
  - **Risk:** Both bundles are built/type-checked together for the entire window (longer than a purely-transitional coexistence period), raising steady-state CI cost (NFR-049-08). **Mitigation:** accepted tradeoff; monitor `npm run build`/`npm run check` wall-clock time and revisit only if it becomes a real bottleneck — no mitigation beyond awareness is planned, since the alternative (in-place migration) was rejected per Q-049-04.
  - **Risk:** Because v7 and v8 are never mounted in the same document, the single-bundle "double ripple/focus-ring" coexistence risk ADR-0005 flagged no longer applies structurally — but a *new* risk is a v8-only Tailwind/theme misconfiguration going unnoticed if dev/staging isn't regularly exercised with the flag on. **Mitigation:** I0's exit criteria requires booting the flag-on app at least once per session that touches `v8/`, not just at the end of a phase.

## Implementation Drift Gate

Before each phase transition (Foundation → Shell → Toast/Confirm → Button → Dialog → Toolbar → Loading/Layout → Forms → Navigation → DataTable → Misc → Cleanup → Dependency Removal), re-run `grep -rl "from \"primevue/" resources/js | wc -l` and compare against the expected remaining count for that phase boundary (tracked in tasks.md's Notes section). Record the actual count alongside the expected count; investigate any divergence >5 files before continuing (likely indicates a missed file or a new PrimeVue import introduced by unrelated concurrent work). Record results in tasks.md's Notes/TODOs section, not a separate traceability file, given the size of this feature.

## Increment Map

> **Reading note (Q-049-04, ADR-0006):** Every increment from Phase 1 onward builds **new files under `resources/js/v8/**`**, using the equivalent path under `resources/js/{views,components,menus}/**` (v7) as the behavioral/visual reference — v7 is never edited. "Migrate X" in an increment's goal means "build v8's version of X, wired into `resources/js/v8/router/routes.ts` via the shared `resources/js/router/paths.ts` manifest." Grep-based exit criteria now check for the **presence** of the new file under `resources/js/v8/...` (and its absence of any `primevue/*` import) rather than the **absence** of PrimeVue from the v7 path — v7 keeps importing PrimeVue, by design, until Phase 15's cutover. Directory names and file counts describe the v7 inventory being mirrored. Phase 0 additionally stands up the toggle/dual-bundle scaffolding itself (new I0), and Phase 15 now ends in a coverage gate + cutover + removal, rather than removal alone.

### Phase 0 — Foundation & toggle scaffolding

0. **I0 – Toggle & dual-bundle scaffolding**
   - _Goal:_ FR-049-22. Stand up the mechanism that lets both UIs be served, per request, at the same paths.
   - _Preconditions:_ None (first increment).
   - _Steps:_ Add `nuxt_ui` (boolean, default `false`) to `config/features.php`; branch `resources/views/vueapp.blade.php`'s `@vite([...])` include on `Features::active('nuxt_ui')` between `app.ts`/`app.css` and a new (initially minimal) `app-v8.ts`/`app-v8.css`; extract `resources/js/router/paths.ts` (`{ name, path, meta }` only, no `component:` refs) out of the existing `resources/js/router/routes.ts`, and update `routes.ts` to consume it; create a minimal `resources/js/v8/router/routes.ts` consuming the same manifest with placeholder/blank components; add both entries to `vite.config.ts`'s `laravel-vite-plugin` `input` array.
   - _Commands:_ `npm run dev` with the flag off (confirm v7 unchanged) and on (confirm a blank v8 shell boots at the same paths), `npm run check`, `php artisan test` (touches `config/features.php`/blade only).
   - _Exit:_ Flag off → app is byte-for-byte the current app; flag on (dev only) → a minimal v8 page loads at every existing route path with no server error; `resources/js/router/routes.ts` and `resources/js/v8/router/routes.ts` have identical path/name lists (both derived from `paths.ts`).

1. **I1 – Install Nuxt UI in standalone Vue mode (v8 entry)**
   - _Goal:_ FR-049-01. The v8 bundle boots with Nuxt UI installed and registered, at the same routes I0 established.
   - _Preconditions:_ I0. User-confirmed dependency addition (AGENTS.md dependency-approval rule) for `@nuxt/ui`.
   - _Steps:_ Add `@nuxt/ui` to `package.json`; register `ui()` in `vite.config.ts` for the `app-v8.ts` entry (verify plugin ordering against `@tailwindcss/vite` and `laravel-vite-plugin`); register the Nuxt UI Vue plugin in `app-v8.ts`; create `resources/js/v8/views/App.vue` wrapping its root in `<UApp>`; add `@import "@nuxt/ui";` to `resources/sass/app-v8.css`; add Nuxt UI's generated type declarations to `tsconfig.app.json` and `.gitignore`. `app.ts`/`resources/sass/app.css` (v7) are not touched.
   - _Commands:_ `npm install`, `npm run dev` (flag on: manual boot check of the v8 shell), `npm run check` (both entries).
   - _Exit:_ v8 shell boots with no console errors; v7 (flag off) is still pixel-unchanged since it wasn't touched; `npm run check` passes for both entries.

2. **I2 – Theme parity (colors, dark mode) — v8**
   - _Goal:_ FR-049-02, FR-049-20. Nuxt UI's theme tokens (in the v8 tree) match `preset.ts`'s primary/surface/dark-mode configuration.
   - _Preconditions:_ I1.
   - _Steps:_ Configure Nuxt UI's theme (CSS `@theme`/`app.config.ts` per its Vue-mode conventions, scoped to `v8/`) with primary = `sky` scale, light surface = `slate`, dark surface = `zinc`; confirm Nuxt UI's dark-mode strategy keys off the existing `.dark` class on `<body>` (shared by both bundles — no change needed to `resources/sass/app.css`'s `@custom-variant dark` line, which both `app.css` and `app-v8.css` can share).
   - _Commands:_ `npm run dev` with the flag on (manual dark-mode toggle check per S-049-02, against the v8 shell).
   - _Exit:_ Toggling dark mode flips the v8 shell's tokens the same way v7's PrimeVue tokens flip; primary color swatch matches when a throwaway `<UButton>` (v8) is compared side-by-side with `<Button>` (v7, in a separate tab) — comparison artifact removed before commit.

3. **I3 – Icon collection setup — v8**
   - _Goal:_ FR-049-15 (setup only — full 139-file mirror happens later, per-phase, as each v8 component is built). Establish the icon seam in the v8 tree.
   - _Preconditions:_ I1. User-confirmed dependency addition for `@iconify-json/prime`.
   - _Steps:_ Add `@iconify-json/prime` as a dev dependency (shared — it's just an Iconify collection, consumed only by v8 code); create `resources/js/v8/components/icons/PiMiniIcon.vue` rendering `<UIcon name="i-prime-...">`, mirroring the props/API of v7's `resources/js/components/icons/PiMiniIcon.vue` (which keeps rendering `<i class="pi pi-...">`, untouched).
   - _Commands:_ `npm install`, `npm run dev` (flag on: spot-check icons rendered via `v8/components/icons/PiMiniIcon.vue`, e.g. once `v8/menus/LeftMenu.vue` exists in I7a).
   - _Exit:_ `v8/components/icons/PiMiniIcon.vue`-rendered icons are visually identical to v7's; `npm run check` passes.

### Phase 1 — New shared composables (v8-only)

4. **I4 – Build `useAppToast()` composable**
   - _Goal:_ FR-049-04 (infrastructure only — call-site build-out happens in Phase 3, entirely within `v8/`).
   - _Preconditions:_ I1.
   - _Steps:_ Create `resources/js/v8/composables/useAppToast.ts` wrapping Nuxt UI's `useToast()`, exposing `add({ severity, summary, detail, life })` translated to `{ color, title, description, duration }`. Add one throwaway call site (flag on) to manually verify rendering before wiring into real v8 call sites.
   - _Commands:_ `npm run dev` (flag on), `npm run check`.
   - _Exit:_ Composable exists, type-checks, and manually verified to render a toast matching v7's current visual style (per NFR-049-01).

5. **I5 – Build `useConfirmDialog()` composable + `ConfirmModalHost.vue`**
   - _Goal:_ FR-049-05 (infrastructure only — 3 v8 call sites built in I12).
   - _Preconditions:_ I1.
   - _Steps:_ Create `resources/js/v8/components/modals/ConfirmModalHost.vue` (singleton, `<UModal>`-backed) and `resources/js/v8/composables/useConfirmDialog.ts` exposing `confirm({ title, message, acceptLabel?, rejectLabel?, severity? }): Promise<boolean>`.
   - _Commands:_ `npm run dev` (flag on), `npm run check`.
   - _Exit:_ Composable + host component exist, type-check, and manually verified (temporary call site) to resolve `true`/`false` correctly on accept/reject.

### Phase 2 — App shell (v8)

6. **I6 – Build `v8/views/App.vue` shell**
   - _Goal:_ FR-049-03. Nuxt UI's implicit toast host (via `<UApp>`, already wrapped in I1) covers `<Toast/>`'s role; `<ConfirmModalHost/>` covers `<ConfirmDialog/>`'s role.
   - _Preconditions:_ I1, I5.
   - _Steps:_ In `resources/js/v8/views/App.vue`, mount `<ConfirmModalHost/>`; wire routed content the same way v7's `App.vue` does, sourced from `v8/router/routes.ts`.
   - _Commands:_ `npm run dev` (flag on — S-049-03, S-049-04 smoke check using I4/I5's temporary call sites), `npm run check`.
   - _Exit:_ Toasts and confirms render as global overlays above all routed content in the v8 shell; `grep -c "primevue" resources/js/v8/views/App.vue` returns 0 (structural — v8 files never import PrimeVue).

7. **I7a – Build `v8/menus/LeftMenu.vue` structure**
   - _Goal:_ FR-049-12 (structural part). `Drawer`→`USlideover`, `Menu`→`UNavigationMenu`.
   - _Preconditions:_ I2, I3.
   - _Steps:_ Build the drawer container and nav-item list against `<USlideover>`/`<UNavigationMenu>`, using v7's `menus/LeftMenu.vue` as the reference for nav items/order/routes; compose `v8/components/icons/PiMiniIcon.vue`/router-link slot content against `<UNavigationMenu>`'s item slot API.
   - _Commands:_ `npm run dev` (flag on — S-049-06: open/close drawer, navigate every top-level nav item, confirm each routes to the same path v7's does).
   - _Exit:_ Drawer opens/closes and routes correctly; icons render via `v8/components/icons/PiMiniIcon.vue`.

8. **I7b – Build `v8/menus/LeftMenu.vue` badges, logout button, styling**
   - _Goal:_ FR-049-12 (remainder), partial FR-049-06 (this file's own `Button`). `pt`/`dt`/`v-ripple` translation happens at authoring time, not as removal (see Phase 13/14 note).
   - _Preconditions:_ I7a, I4.
   - _Steps:_ `OverlayBadge`→`UBadge`/`UChip` for unread/count badges; `Button`→`UButton` for logout; translate any spacing/color customization v7 expresses via `:pt:`/`:dt=` into Nuxt UI's `:ui=` prop or plain Tailwind classes on the v8 component (no ripple is ever added, since v8 is authored fresh with Nuxt UI's own interaction style).
   - _Commands:_ `npm run dev` (flag on — S-049-06, S-049-09 no-ripple check by construction), `npm run check`.
   - _Exit:_ Badges/counts render correctly and match v7's spacing/color; `grep -c "primevue" resources/js/v8/menus/LeftMenu.vue` returns 0.

### Phase 3 — Toast/Confirm build-out (v8, mirrors v7 call sites)

9. **I8 – Toast usage: composables**
   - _Goal:_ FR-049-04. Build v8 twins of the 8 `.ts` composables under `composables/album/`, `composables/checkout/`, `composables/photo/` that call `primevue/usetoast` in v7 — using `useAppToast()`. (These composables are otherwise non-UI-coupled; only their toast call needs a v8-specific variant, or they can stay shared if the call is isolated behind a thin seam — evaluate per file.)
   - _Preconditions:_ I4.
   - _Commands:_ `npm run check`.
   - _Exit:_ Every v8-tree consumer of these composables' toast behavior uses `useAppToast()`, verified against v7's equivalent messages/severities.

10. **I9 – Toast usage: `v8/components/maintenance/`**
    - _Goal:_ FR-049-04. Build v8 twins of `components/maintenance/Maintenance*.vue` (~14+ files) that call `useToast()` in v7.
    - _Preconditions:_ I4.
    - _Commands:_ `npm run dev` (flag on — trigger a few maintenance actions), `npm run check`.
    - _Exit:_ v8 maintenance toasts match v7's messages/severities.

11. **I10 – Toast usage: `v8/components/gallery/`, `v8/components/forms/`**
    - _Goal:_ FR-049-04. Build v8 twins of `components/gallery/**`, `components/forms/**` toast call sites.
    - _Preconditions:_ I4.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 gallery/forms toasts match v7's.

12. **I11 – Toast usage: `v8/views/admin/`, `v8/views/webshop/`, remaining sweep**
    - _Goal:_ FR-049-04. Build v8 twins for `views/admin/**`, `views/webshop/**`, and any remaining stragglers.
    - _Preconditions:_ I4.
    - _Commands:_ `npm run dev` (flag on), `npm run check`, `grep -rl "primevue/usetoast" resources/js/v8` (must return empty — structural).
    - _Exit:_ Every v8 file needing a toast uses `useAppToast()`.

13. **I12 – `useConfirmDialog()` call sites**
    - _Goal:_ FR-049-05. Build v8 twins of `views/RenamerRules.vue`, `views/admin/ContactMessages.vue`, `views/admin/UserGroups.vue`, calling `useConfirmDialog()` instead of v7's `primevue/useconfirm`.
    - _Preconditions:_ I5, I6.
    - _Commands:_ `npm run dev` (flag on — S-049-04 on all 3 call sites), `npm run check`.
    - _Exit:_ All 3 v8 views resolve `true`/`false` correctly on accept/reject, matching v7's `accept`/`reject` callback behavior.

### Phase 4 — Button build-out (154 files mirrored)

14. **I13 – Buttons: `v8/components/maintenance/`**
    - _Goal:_ FR-049-06 for the maintenance directory (~14-20 files).
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 maintenance buttons match v7's affordances (color/variant/icon/loading).

15. **I14 – Buttons: `v8/components/gallery/`**
    - _Goal:_ FR-049-06 for album/photo/tag/search gallery modules.
    - _Commands:_ `npm run dev` (flag on — photo/album action buttons), `npm run check`.
    - _Exit:_ v8 gallery buttons match v7's.

16. **I15 – Buttons: `v8/components/forms/`**
    - _Goal:_ FR-049-06 for album/sharing/settings/users/webshop/basic form components.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 form buttons match v7's.

17. **I16 – Buttons: `v8/components/headers/`, `v8/components/drawers/`, `v8/components/modals/`, `v8/components/renamer/`, `v8/components/faceRecog/`**
    - _Goal:_ FR-049-06 for remaining component subdirectories.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 buttons in these directories match v7's.

18. **I17 – Buttons: `v8/views/admin/`, `v8/views/webshop/`**
    - _Goal:_ FR-049-06 for admin and webshop views.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 buttons in these views match v7's.

19. **I18 – Buttons: remaining views, `v8/components/statistics/`, `v8/menus/` sweep to zero**
    - _Goal:_ FR-049-06 completion. Includes top-level views (`Home.vue`, `Landing.vue`, `Contact.vue`, `Diagnostics.vue`, `Sharing.vue`, `Permissions.vue`, etc.), `views/gallery-panels/`, `views/face-recog/`.
    - _Commands:_ `npm run dev` (flag on), `npm run check`, `grep -rl "primevue/button" resources/js/v8` (must return empty — structural).
    - _Exit:_ Every v7 file using `Button` has a `v8/` twin using `UButton`.

### Phase 5 — Dialog build-out (55 files mirrored)

20. **I19 – Dialogs: `v8/components/forms/album/` (reference implementation)**
    - _Goal:_ FR-049-07 for album-related dialogs (`AlbumProperties`, `WatermarkConfirmDialog`, `AlbumCreateShareDialog`, `AlbumCreateTagDialog`, `EmbedCodeDialog`, `ConfirmSharingDialog`, `ApplyRenamerDialog`, etc.). Establishes the `v-model:visible`(v7)/`v-model:open`(v8) dual-binding-against-the-same-store pattern, reused verbatim by subsequent Dialog increments.
    - _Commands:_ `npm run dev` (flag on — S-049-05 on ≥3 of these dialogs, confirmed to bind to the same `ModalsState` flags v7 uses), `npm run check`.
    - _Exit:_ v8 twins built for this directory; the store-binding pattern is documented in tasks.md Notes for reuse.

21. **I20 – Dialogs: `v8/components/forms/sharing/`, `v8/components/forms/users/`, `v8/components/forms/settings/`**
    - _Goal:_ FR-049-07 for sharing/user/settings dialogs.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 twins built for these directories.

22. **I21 – Dialogs: `v8/components/renamer/`, `v8/components/faceRecog/`, `v8/components/modals/`**
    - _Goal:_ FR-049-07 for renamer rule modal, face-recognition modals (FaceAssignmentModal, SelfieClaimModal), and general modals (UploadPanel, KeybindingsHelp).
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 twins built for these directories.

23. **I22 – Dialogs: `v8/views/admin/`, `v8/views/webshop/`, remaining sweep**
    - _Goal:_ FR-049-07 completion.
    - _Commands:_ `npm run dev` (flag on), `npm run check`, `grep -rl "primevue/dialog" resources/js/v8` (must return empty — structural).
    - _Exit:_ Every v7 file using `Dialog` has a `v8/` twin using `UModal`.

### Phase 6 — Toolbar build-out (42 files, no direct equivalent, mirrored)

24. **I23 – Toolbar: `v8/components/headers/` (reference implementation)**
    - _Goal:_ FR-049-08. Build v8 twins of `AlbumHeader.vue`, `SearchHeader.vue`, `AlbumsHeader.vue`, `TimelineHeader.vue` using a composed flex-header pattern; this establishes the reusable pattern referenced by I24.
    - _Commands:_ `npm run dev` (flag on — visual comparison of header layout against v7), `npm run check`.
    - _Exit:_ v8 twins built for this directory; the flex-header pattern is noted in tasks.md for reuse.

25. **I24 – Toolbar: remaining views/panels, sweep**
    - _Goal:_ FR-049-08 completion (other panels using Toolbar as page-header chrome in v7).
    - _Commands:_ `npm run dev` (flag on), `npm run check`, `grep -rl "primevue/toolbar" resources/js/v8` (must return empty — structural).
    - _Exit:_ Every v7 file using `Toolbar` has a `v8/` twin using the flex-header pattern.

### Phase 7 — Loading/progress primitives (v8)

26. **I25 – ProgressSpinner, ProgressBar, MeterGroup (49 files combined, mirrored)**
    - _Goal:_ FR-049-09. Build a small custom `v8/components/Spinner.vue`; build v8 twins for `ProgressSpinner` (41), `ProgressBar` (6), `MeterGroup` (2) usages, concentrated in `components/maintenance/` and `components/statistics/`.
    - _Commands:_ `npm run dev` (flag on — trigger a long-running maintenance action, view storage statistics), `npm run check`.
    - _Exit:_ v8 twins built repo-wide for these three.

### Phase 8 — Layout/content primitives (v8)

27. **I26a – Card, Panel: `v8/components/maintenance/`, `v8/components/diagnostics/`**
    - _Goal:_ FR-049-10 for the highest-concentration directories.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 twins built for these directories.

28. **I26b – Card, Panel, Fieldset, Divider: remaining sweep**
    - _Goal:_ FR-049-10 completion (statistics, settings, forms, webshop).
    - _Commands:_ `npm run dev` (flag on), `npm run check`, `grep -rl "primevue/card\|primevue/panel\|primevue/fieldset\|primevue/divider" resources/js/v8` (must return empty — structural).
    - _Exit:_ v8 twins built repo-wide for these four.

### Phase 9 — Form primitives (v8)

29. **I27 – Build the 8 `v8/components/forms/basic/` wrapper components**
    - _Goal:_ FR-049-11 (seam). Build v8 twins of `InputText.vue`, `Textarea.vue`, `Password.vue`, `Fieldset.vue`, `InputCurrency.vue`, `InputPassword.vue`, `TagsInput.vue`, `PersonsInput.vue` against Nuxt UI equivalents, keeping the **same external prop/emit contract** v7's wrappers expose, so v8 consumers built in later increments need no per-caller adaptation.
    - _Commands:_ `npm run dev` (flag on — a form-heavy view, e.g. Album Properties), `npm run check`.
    - _Exit:_ All 8 v8 wrappers built with matching contracts.

30. **I28 – Select, FloatLabel, Checkbox, ToggleSwitch: `v8/components/forms/album/`, `v8/components/forms/sharing/`**
    - _Goal:_ FR-049-11 for the highest-concentration form directories.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 twins built for these directories.

31. **I29 – Select, FloatLabel, Checkbox, ToggleSwitch: `v8/components/forms/settings/`, `v8/components/forms/users/`, `v8/views/admin/`, `v8/views/webshop/`**
    - _Goal:_ FR-049-11 continuation.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 twins built for these directories.

32. **I30 – AutoComplete, SelectButton, InputNumber, MultiSelect, Listbox, RadioButton, DatePicker, InputGroup family: sweep**
    - _Goal:_ FR-049-11 completion for all remaining scattered form-primitive usages.
    - _Commands:_ `npm run dev` (flag on), `npm run check`, `grep -rl "primevue/select\|primevue/floatlabel\|primevue/checkbox\|primevue/toggleswitch\|primevue/autocomplete\|primevue/selectbutton\|primevue/inputnumber\|primevue/multiselect\|primevue/listbox\|primevue/radiobutton\|primevue/datepicker\|primevue/inputgroup\|primevue/iconfield\|primevue/inputtext\|primevue/textarea\|primevue/password\|primevue/inputswitch" resources/js/v8` (must return empty — structural).
    - _Exit:_ v8 twins built for all remaining scattered form-primitive usages.

### Phase 10 — Navigation/menu build-out (remaining)

33. **I31 – ContextMenu: gallery right-click menus**
    - _Goal:_ FR-049-12 for `components/gallery/tagModule/TagPanel.vue`, `components/gallery/searchModule/ResultPanel.vue`, `components/gallery/albumModule/AlbumPanel.vue`, and the remaining ContextMenu usages (9 files total).
    - _Preconditions:_ `composables/contextMenus/contextMenu.ts` reviewed (menu item construction logic — shared, not PrimeVue-coupled, unaffected by the component swap).
    - _Commands:_ `npm run dev` (flag on — right-click a photo/album/tag, verify all actions), `npm run check`.
    - _Exit:_ v8 twins built; every context-menu action verified present.

34. **I32 – Remaining Menu, Paginator, OverlayBadge usages**
    - _Goal:_ FR-049-12/FR-049-14 completion for `views/admin/Settings.vue`, `components/settings/AllSettings.vue` (Menu), pagination components (Paginator), and remaining OverlayBadge usages outside `LeftMenu.vue`.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 twins built repo-wide for these.

### Phase 11 — DataTable build-out (10 files, structural rewrite, mirrored)

35. **I33 – DataTable: `v8/components/statistics/AlbumsTable.vue`, `v8/components/drawers/StatTable.vue`, `v8/components/modals/KeybindingsHelp.vue`**
    - _Goal:_ FR-049-13 for the first 3 tables (lower complexity — mostly read-only display tables).
    - _Commands:_ `npm run dev` (flag on — S-049-07: verify sort/pagination/data rendering matches v7 per table), `npm run check`.
    - _Exit:_ These 3 tables built in v8 with equivalent sort/display behavior.

36. **I34 – DataTable: `v8/views/admin/ContactMessages.vue`, `Purchasables.vue`, `Webhooks.vue`, `NsfwConfig.vue`**
    - _Goal:_ FR-049-13 for admin tables with row actions (edit/delete/toggle).
    - _Commands:_ `npm run dev` (flag on — S-049-07: verify row actions trigger correct handlers per table), `npm run check`.
    - _Exit:_ These 4 tables built in v8 with equivalent row-action behavior.

37. **I35 – DataTable: `v8/views/admin/shop/PrintPixelSizesAdmin.vue`, `v8/views/webshop/PurchasablesList.vue`, `OrderList.vue`, sweep**
    - _Goal:_ FR-049-13 completion.
    - _Commands:_ `npm run dev` (flag on), `npm run check`, `grep -rl "primevue/datatable\|primevue/column" resources/js/v8` (must return empty — structural).
    - _Exit:_ Every v7 `DataTable`/`Column` usage has a `v8/` twin using `UTable`.

### Phase 12 — Miscellaneous components (v8)

38. **I36 – Tag, Message**
    - _Goal:_ FR-049-14 for `Tag`(10)→`UBadge`, `Message`(8)→`UAlert`.
    - _Commands:_ `npm run dev` (flag on), `npm run check`.
    - _Exit:_ v8 twins built repo-wide for these two.

39. **I37 – ScrollTop, VirtualScroller**
    - _Goal:_ FR-049-14 for `ScrollTop`(7)→custom component, `VirtualScroller`(2, gallery panels — performance-sensitive)→`@tanstack/vue-virtual` or custom.
    - _Preconditions:_ User confirmation if `@tanstack/vue-virtual` is added as a new dependency (AGENTS.md dependency-approval rule) — otherwise implement a minimal custom virtual-scroll fallback.
    - _Commands:_ `npm run dev` (flag on — scroll performance check on a large album/gallery view), `npm run check`.
    - _Exit:_ v8 twins built; scroll performance on large lists is not visibly degraded vs. v7.

40. **I38 – Timeline, Tabs family, Stepper family, Inplace, SpeedDial, ScrollPanel: sweep**
    - _Goal:_ FR-049-14 completion for all remaining niche components.
    - _Commands:_ `npm run dev` (flag on — checkout flow for Stepper, `Timeline.vue`, Tabs in NsfwConfig/Albums), `npm run check`, `grep -rl "primevue/timeline\|primevue/tabs\|primevue/tablist\|primevue/tab\|primevue/tabpanels\|primevue/tabpanel\|primevue/stepper\|primevue/steppanels\|primevue/steppanel\|primevue/steplist\|primevue/step\|primevue/inplace\|primevue/speeddial\|primevue/scrollpanel" resources/js/v8` (must return empty — structural).
    - _Exit:_ v8 twins built repo-wide for all remaining niche components.

### Phase 13 — Pass-through verification (structural, not a cleanup)

41. **I39 – Verify no `pt`/`dt` syntax leaked into the v8 tree**
    - _Goal:_ FR-049-16 verification. Because v8 files are authored fresh against Nuxt UI (which has no `pt`/`dt` APIs), there is nothing to remove — each earlier component-family increment was already responsible for translating any v7 `:pt:`/`:dt=` customization it referenced into v8's `:ui=`/Tailwind-class equivalent at authoring time. This increment is a structural verification pass, not a first-touch migration.
    - _Preconditions:_ Phases 4-12 complete.
    - _Commands:_ `grep -rl ":pt:\|:pt=\|:dt=" resources/js/v8` (must return empty — these are exclusively PrimeVue APIs, so a hit here indicates a copy-paste error from an earlier increment).
    - _Exit:_ Zero `:pt:`/`:pt=`/`:dt=` occurrences in `resources/js/v8`.

### Phase 14 — Directive verification (structural, not a cleanup)

42. **I40 – Verify no ripple/focustrap directive usage in v8; confirm tooltip parity**
    - _Goal:_ FR-049-17 verification. `app-v8.ts` never registers `ripple`/`focustrap` directives (Nuxt UI has no equivalent, and Reka UI traps focus internally) and no v8 file uses `v-ripple`/`v-focustrap` — structural, not a removal step. Confirm every v7 `v-tooltip` site has a v8 twin using `<UTooltip>` as a wrapping component (Nuxt UI has no directive-based tooltip).
    - _Preconditions:_ Phases 2-12 complete.
    - _Commands:_ `npm run dev` (flag on — S-049-09 no-ripple check, S-049-14 keyboard focus-trap check on a v8 modal, tooltip hover check), `npm run check`, `grep -rl "v-ripple\|v-focustrap\|v-tooltip\|primevue" resources/js/v8` (must return empty — structural).
    - _Exit:_ Zero directive references in `resources/js/v8`; keyboard focus-trapping verified on ≥2 v8 modals; tooltip parity confirmed on ≥3 sites.

### Phase 15 — Coverage gate, cutover, dependency removal & documentation

43. **I41 – Route-parity coverage gate**
    - _Goal:_ FR-049-23. Confirm the `v8/` tree is complete enough to serve real traffic.
    - _Preconditions:_ Phases 0-14 complete.
    - _Steps:_ Cross-check every `{ name, path }` entry in `resources/js/router/paths.ts` against `resources/js/v8/router/routes.ts`; produce a coverage checklist (one row per route) and verify each manually (flag on, both light/dark mode).
    - _Commands:_ `npm run dev` (flag on, full route walk), `npm run check`.
    - _Exit:_ Every route in `paths.ts` has a working `v8/views/**` component; zero placeholder/missing entries in `v8/router/routes.ts`.

44. **I42 – Cutover**
    - _Goal:_ FR-049-24.
    - _Preconditions:_ I41.
    - _Steps:_ Enable `nuxt_ui` for real traffic (config/env change, no application redeploy needed beyond the already-shipped code); run the full manual smoke test (same scope as I45) against the now-live v8 bundle; rehearse rollback by flipping the flag back to `false` and confirming v7 is restored exactly.
    - _Commands:_ `npm run build`, `npm run build:embed`, manual smoke test.
    - _Exit:_ v8 serves real traffic with no regression found; rollback rehearsal confirms instant recovery to v7 if needed. Flag is left in place for a deliberate observation window before I43 proceeds.

45. **I43 – Remove PrimeVue dependencies, v7 tree, and the flag**
    - _Goal:_ FR-049-18. Hard completion gate.
    - _Preconditions:_ I42 stable for the observation window; `grep -rl "primevue\|@primeuix" resources/js` returns hits only under `resources/js/views\|components\|menus` (v7) and nowhere under `resources/js/v8`.
    - _Steps:_ Delete `resources/js/views/**`, `resources/js/components/**`, `resources/js/menus/**` (v7), `resources/js/app.ts`, `resources/js/style/preset.ts`; remove the `nuxt_ui` flag from `config/features.php` and collapse `vueapp.blade.php`'s `@if`/`@else` to a single unconditional `@vite([...])` pointing at `app-v8.ts`; remove `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons` from `package.json`; remove `@plugin "tailwindcss-primeui";` from the app CSS.
    - _Commands:_ `npm install`, `npm run build`, `npm run build:embed` (S-049-13 embed-untouched check), `npm run check`.
    - _Exit:_ `npm install` produces a lockfile with zero PrimeVue-family packages; `grep -rl "primevue\|@primeuix" resources/js` returns zero files; a single entry point remains; app renders identically to the cut-over state.

46. **I44 – Update governing documentation**
    - _Goal:_ FR-049-19.
    - _Preconditions:_ I43.
    - _Steps:_ Update `docs/specs/3-reference/coding-conventions.md`'s `### UI Components` section; update `docs/specs/4-architecture/knowledge-map.md`'s Frontend Dependencies/Components sections; note the former `v8/` tree's structure is now simply the canonical `resources/js/` structure.
    - _Exit:_ Both documents reference Nuxt UI, `useAppToast()`, `useConfirmDialog()`; no stale PrimeVue or `v8/`-prefix references remain.

47. **I45 – Full quality gate and final sign-off**
    - _Goal:_ Feature completion verification.
    - _Preconditions:_ I43, I44.
    - _Commands:_ `npm run format`, `npm run check`, `npm run build`, `npm run build:embed`.
    - _Steps:_ Full manual smoke test across every major view/flow in both light and dark mode (Home, Album, Photo detail, Search, Upload, Settings, Admin Dashboard, all admin sub-pages, Webshop checkout, Sharing, Face recognition, People).
    - _Exit:_ All commands pass; manual smoke test finds no regressions; roadmap entry moved to Completed.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-049-01 | I0, I1 | Toggle scaffolding + v8 boot, zero visual change to v7 |
| S-049-02 | I2 | Dark mode toggle parity (per active bundle) |
| S-049-03 | I4, I6 | Toast infra + v8 shell wiring |
| S-049-04 | I5, I6, I12 | Confirm infra + v8 shell wiring + call-site build-out |
| S-049-05 | I19 (reference), I20-I22 | Dialog → UModal, store-driven `open` binding, v8 tree |
| S-049-06 | I7a, I7b | LeftMenu drawer + nav, v8 tree |
| S-049-07 | I33-I35 | DataTable → UTable per table, v8 tree |
| S-049-08 | I3 (setup), all component-build increments (icon mirroring incidental to each) | Icon parity spot-check |
| S-049-09 | I8-I18 (v8 authored ripple-free by construction), I40 (final verification) | No-ripple check |
| S-049-10 | I41 | Route-parity coverage gate |
| S-049-11 | I42 | Cutover + rollback rehearsal |
| S-049-12 | I43 | Dependency/v7-tree/flag removal gate |
| S-049-13 | I43 (explicit check), spot-checked at any increment touching shared `@/`-aliased files | Embed bundle untouched by either tree |
| S-049-14 | I40 | Keyboard focus-trap verification, v8 tree |

## Analysis Gate

Run per [docs/specs/5-operations/analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md).

**Date:** 2026-07-02 · **Reviewer:** AI agent (pre-implementation self-review, re-run after ADR-0006/Q-049-04)

1. **Specification completeness** — ✅ Pass. 24 FRs (21 original + FR-049-22/23/24) / 9 NFRs (7 original + NFR-049-08/09) populated in spec.md; Q-049-01/02/03/04 resolutions folded directly into Overview, Goals, Non-Goals, and the relevant FR/NFR `Source` columns; ASCII mock-ups updated to show the request-routing branch (flag → bundle) alongside the original app-shell/dialog before-after diagrams.
2. **Open questions review** — ✅ Pass. Zero `Open` entries remain for Feature 049 in open-questions.md (all four resolved same-day as raised). ADR-0005 (original library-replacement decision) and ADR-0006 (dual-tree/toggle mechanism, amending ADR-0005's implementation-mechanism item) both created and cross-linked from their respective Question Details entries and from spec.md's Overview.
3. **Plan alignment** — ✅ Pass. This plan references `spec.md` and `tasks.md` at the paths declared in both documents' headers; Dependencies & Interfaces section matches spec.md's Appendix component-mapping table and the new `router/paths.ts`/`v8/` tree/`nuxt_ui` flag surface. `tasks.md` was regenerated 2026-07-03 against this plan's 48-increment (I0-I45) Increment Map (T-049-00 through T-049-45, including 07a/07b and 26a/26b sub-tasks) — no remaining sync gap.
4. **Tasks coverage** — ✅ Pass. Every FR-049-01..24 maps to at least one task in `tasks.md` (cross-checked by ID during regeneration); tasks sequence infrastructure (toggle scaffolding, composables, theme, icons) before consuming call-site build-out; task IDs and phase groupings mirror the Increment Map 1:1.
   - ⚠ **Deviation noted (carried over):** this feature has no success/validation/failure branch matrix in the traditional sense (NFR-044-style) since it is a mechanical library migration, not new business logic — branch coverage is expressed instead as per-directory presence/absence `grep` checks (v8 tree gains the component; v7 tree is untouched) plus manual behavioral spot-checks (S-049-01..14). This substitution is intentional given the feature's nature and is not treated as a gap.
5. **Constitution compliance** — ✅ Pass. No dependency is added without flagging the AGENTS.md approval requirement (see tasks.md Notes); no fallback/shim/backwards-compatibility scaffolding is introduced beyond the explicitly time-boxed `nuxt_ui` flag itself, which FR-049-18 makes a hard removal gate (not a permanent feature flag); ADR-0005 and ADR-0006 both reviewed as part of this analysis.
6. **Tooling readiness** — ✅ Pass. Every increment/task lists its verification commands (`npm run dev` with the flag toggled appropriately, `npm run check` for both entries, targeted `grep` sweeps scoped to `resources/js/v8`, `npm run build`/`build:embed` at completion).

**Outcome:** All checklist items pass. Implementation of I0/T-049-00 may proceed.

_Implementation Drift Gate: not yet run — deferred until all increments in this plan are marked complete in `tasks.md`, per the checklist's own sequencing (§"Run this section once all planned tasks are complete")._

## Exit Criteria

- All 48 increments (I0-I45, including I7a/I7b/I26a/I26b sub-increments) marked complete in `tasks.md` (T-049-00 through T-049-45).
- `resources/js/router/paths.ts` fully covered by `resources/js/v8/router/routes.ts` (route-parity coverage gate, I41/FR-049-23).
- `nuxt_ui` flipped on for real traffic with no regression found, and a rollback rehearsal confirmed (I42/FR-049-24).
- `grep -rl "primevue\|@primeuix" resources/js` returns zero files; `resources/js/views\|components\|menus/**` (v7), `resources/js/app.ts`, `resources/js/style/preset.ts`, and the `nuxt_ui` flag are deleted (I43/FR-049-18).
- `package.json` has no `primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons` entries.
- `npm run format`, `npm run check`, `npm run build`, `npm run build:embed` all pass, with a single entry point again.
- `docs/specs/3-reference/coding-conventions.md` and `docs/specs/4-architecture/knowledge-map.md` updated (FR-049-19, I44).
- Full manual smoke test (I45) completed with no open regressions.
- Roadmap entry for Feature 049 moved from Active to Completed.

## Follow-ups / Backlog

- Consider a follow-up feature to adopt Nuxt UI's default Lucide icon set as a deliberate visual refresh, now that the library migration (Q-049-02 Option A) has decoupled icon choice from the library swap.
- Consider a follow-up feature to introduce a Vitest + `@vue/test-utils` component test suite now that the app is on a single, more testable UI library (out of scope here per Non-Goals, but the migration removes one obstacle — PrimeVue's heavier test-mounting requirements — to adding one later).
- Re-evaluate whether a custom ripple directive (Q-049-03 Option B) is worth revisiting if user feedback indicates the interaction-feel change is unwelcome.
- Monitor Nuxt UI's release notes for its standalone-Vue-mode maturity (it's a newer usage pattern than full-Nuxt integration) and revisit any workarounds adopted during this migration if upstream gaps close.
- After cutover (I42) and before final removal (I43), consider whether the observation window should be a fixed calendar duration or tied to a specific usage/error signal — not yet specified, since this feature has no telemetry to key an automated criterion off of.

---

*Last updated: 2026-07-02*
