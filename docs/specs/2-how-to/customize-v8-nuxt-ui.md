# How to design and customize the v8 (Nuxt UI) frontend

Lychee is mid-migration from PrimeVue (v7, `resources/js/v7/`) to Nuxt UI (v8, `resources/js/v8/`), run as two parallel trees toggled by the `nuxt_ui` feature flag — see [ADR-0005](../6-decisions/ADR-0005-nuxt-ui-migration.md) and [ADR-0006](../6-decisions/ADR-0006-nuxt-ui-dual-tree-toggle.md) for why. This guide covers the parts specific to the v8/Nuxt UI tree: theming, per-component style overrides, icons, toasts/dialogs, and how to see your changes. Everything not UI-library-specific (Pinia stores, services, most composables, coding conventions like `.then()` over `async/await`) is shared with v7 and already documented in [frontend-architecture.md](../3-reference/frontend-architecture.md) and [coding-conventions.md](../3-reference/coding-conventions.md) — that content still applies to `v8/` unchanged.

## Seeing your changes

1. Set `NUXT_UI_ENABLED=true` in `.env` (backs the `nuxt_ui` key in `config/features.php`) so `resources/views/vueapp.blade.php` serves `app-v8.ts`/`app-v8.css` instead of the v7 bundle.
2. `npm run dev` and open the app — every route resolves to a `v8/views/**` component while the flag is on (route parity is a hard gate; see ADR-0006 item 7).
3. There is no automated frontend test suite (see the ADR-0005 "Operational Impact" section) — verification is manual/browser-based. Always check both light and dark mode (toggle via Settings → General → dark mode, or add/remove the `.dark` class on `<body>` directly in devtools for a quick check).
4. Before considering a change done: `npm run check` (vue-tsc), `npm run lint`, `npm run check-formatting`, and `npm run build` — all four run in CI ([.github/workflows/js_check.yml](../../../.github/workflows/js_check.yml)) and cover both `app.ts` and `app-v8.ts`.

## How the color theme is wired

Nuxt UI expects to run inside a real Nuxt app, where its `colors.js` runtime plugin reads `appConfig.ui.colors` and injects the resolved `--ui-color-*`/`--ui-*` CSS custom properties via `useHead()`. None of that exists in a plain Vue SPA, so v8 reproduces it by hand in two places that must stay in sync:

- **[vite.config.ts](../../../vite.config.ts)** — the `ui()` Vite plugin's `ui` option sets the *named* palette (`{ colors: { primary: "sky", neutral: "slate" } }`) and any other Nuxt UI `appConfig.ui` overrides. This is build-time: it's spread into a virtual `#build/app.config` module (via `defu`) that every Nuxt UI component reads through `useAppConfig()` to resolve its own `tv()` theme (colors, and any per-component slot/variant overrides — see below).
- **[resources/js/v8/theme.ts](../../../resources/js/v8/theme.ts)** — `applyV8Theme()`, called once before `app.mount()` in `app-v8.ts`, materializes the actual `--ui-color-<name>-<shade>` CSS custom properties into a `<style>` tag at runtime, since there's no `useHead()`/Nuxt plugin to do it for us. `neutral` gets an extra light/dark *family* split (slate in light mode, zinc in dark mode) to match v7's Aura preset — that part is bespoke, Nuxt UI's own mechanism only varies shade, not family, between light/dark.

**If you add or rename a named color** (`primary`, `secondary`, `success`, `info`, `warning`, `error`), update both `NAMED_COLORS` in `theme.ts` *and* the `colors` object passed to `ui()` in `vite.config.ts` — they're independent hand-maintained copies of the same intent, nothing enforces they match.

Dark mode itself is a plain `.dark` class toggled on `document.body` (see `resources/js/v8/components/settings/General.vue` and `views/admin/Settings.vue`) — no `useColorMode()`/`nuxt-color-mode` composable is wired up, since that's also a Nuxt-runtime feature this SPA doesn't have.

## Overriding a component's default styling

Nuxt UI components resolve their classes via `tailwind-variants` (`tv()`), merged in this order for a given instance:

1. The component's own base theme (bundled with `@nuxt/ui`).
2. `appConfig.ui.<component>` — global overrides, set via `vite.config.ts`'s `ui({ ui: { ... } })` (same mechanism as `colors` above). Use this when a change should apply to **every** instance of a component app-wide.
3. The component's own `:ui="{ ... }"` prop, passed per-instance in a template. Use this for a one-off tweak to a single usage.
4. The `class` prop, merged into the `root`/`base` slot last.

Conflicting Tailwind utility classes (e.g. `ring` vs `ring-0`) are deduplicated by `tailwind-merge`, with later-merged classes winning — so a global override can still be widened or narrowed per-instance with a `:ui`/`class` prop if needed.

**Worked example:** removing the border from every `<UCard>` (used by the Maintenance page's ~22 panels, `BulkAlbumEdit`, diagnostics pages, etc.). `UCard`'s default (`outline`) variant applies `ring ring-default` to its `root` slot. Rather than editing 20+ files, the global override lives in `vite.config.ts`:

```ts
ui({
  ui: {
    colors: { primary: "sky", neutral: "slate" },
    card: { variants: { variant: { outline: { root: "ring-0" } } } },
  },
}),
```

The shape of the override object (`variants.variant.<name>.<slot>`) mirrors the component's own theme definition — to find it for another component, look at `node_modules/@nuxt/ui/dist/shared/*.mjs` for `const <component> = (options) => ({ slots: {...}, variants: {...} })`, or check the component's own `.vue` source (e.g. `node_modules/@nuxt/ui/dist/runtime/components/Card.vue`) for the exact `tv({ extend: tv(theme), ...appConfig.ui?.card })` call — `appConfig.ui.<component>` is spread directly as `tv()` config, so anything valid in a `tv()` call (slots, variants, compoundVariants, defaultVariants) is valid there.

For a one-off override instead, pass `:ui` directly on the instance — several maintenance panels already do this for other slots, e.g.:

```vue
<UCard class="min-h-40 relative" :ui="{ body: 'h-full flex flex-col justify-between gap-4' }">
```

## Icons

v7 used PrimeIcons (`pi pi-*` classes); v8 uses Nuxt UI's `<UIcon>` backed by Iconify, with the `prime` Iconify collection (`@iconify-json/prime`) providing a 1:1 icon-name mapping so no icons had to be redesigned as part of the migration (see ADR-0005, Q-049-02).

- `resources/js/v8/icons.ts` registers the `prime` collection offline at startup (`registerIconCollections()`, called once in `app-v8.ts`) so icon lookups never hit the public Iconify API at runtime.
- `primeIconToIconifyName("pi pi-home")` → `"prime:home"` converts a v7-style class string to the Iconify name `<UIcon>` expects — use this when porting a v7 component that stored icon names as PrimeIcons classes (e.g. from a store or API response) rather than hand-writing the `prime:` prefix everywhere.
- `resources/js/v8/components/icons/PiMiniIcon.vue` wraps this for the common "small icon that might be a Prime icon or a custom SVG" case — pass it whatever icon string the data already has and it picks `<UIcon name="prime:...">` vs a custom `<MiniIcon>` automatically.
- For a genuinely new icon not in the `prime` collection, use any other Iconify collection name directly with `<UIcon name="collection:icon-name">` (see the `@iconify-json/prime` devDependency in `package.json` for the pattern of adding another `@iconify-json/*` package if needed).

## Toasts and confirm dialogs

Nuxt UI has no direct equivalent to PrimeVue's imperative `ConfirmationService`, and its `useToast()` has a different call shape than v7's `severity`/`summary`/`detail`/`life` convention. Two composables in `resources/js/v8/composables/` bridge this:

- **`useAppToast()`** wraps Nuxt UI's `useToast()` so call sites keep the same `{ severity, summary, detail, life }` shape v7 used — call it instead of importing `useToast` from `@nuxt/ui` directly, so any future toast-styling change only needs updating one place.
- **`useConfirmDialog()`** returns a Promise-based `confirm(options)`, backed by a singleton `<UModal>` mounted once as `ConfirmModalHost.vue` in `v8/views/App.vue` — replaces v7's `useConfirm()`/`<ConfirmDialog>`. Call `confirm({ title, message, severity })` and `await`/`.then()` the boolean result; don't mount another `ConfirmModalHost` elsewhere, there's only ever one instance.

## Adding a new v8 component or page

- Mirror the v7 directory it corresponds to under `v8/` (e.g. a new gallery component goes in `resources/js/v8/components/gallery/...`, matching `resources/js/v7/components/gallery/...`'s layout) — this 1:1 mirroring is deliberate (ADR-0006 item 5) so the two trees stay easy to compare during the migration.
- No PrimeVue imports in anything under `v8/` — that's the whole point of the dual-tree split. If you need a component PrimeVue has but Nuxt UI doesn't (e.g. `DataTable`'s column API, `VirtualScroller`, `Stepper`), check ADR-0005's "Consequences → Negative" list first; these need bespoke composition, not a prop-for-prop swap.
- New routes are added to the *shared* `resources/js/router/paths.ts` manifest (path/name/meta only, no `component:`), then wired to a `v8/views/**` component in `resources/js/v8/router/routes.ts` — this keeps v7 and v8 reachable at identical URLs regardless of which bundle is currently served.
- Use `<script setup lang="ts">`, Composition API, and the project's usual conventions (`.then()` over `async/await`, function declarations over arrow functions) — see [coding-conventions.md](../3-reference/coding-conventions.md), unchanged for v8.

## Gotchas

- Two `<style>`/config sources for colors (`vite.config.ts` and `theme.ts`) must be kept in sync manually — there's no single source of truth today (see above).
- `appConfig.ui.<component>` overrides in `vite.config.ts` are **global** — before adding one, check how many existing usages of that component rely on the default look; a targeted `:ui`/`class` prop on the specific instance(s) may be safer than a blanket theme change.
- The embeddable widget bundle (`resources/js/embed/`) is explicitly out of scope for all of this — it must stay dependency-free of both PrimeVue and Nuxt UI (ADR-0005 item 5).
- `optimizeDeps.exclude` in `vite.config.ts` currently excludes `@primeuix/themes`/`primevue` from the dev-server dependency scan — a side effect of the two Vite entries (`app.ts` + `app-v8.ts`) sharing one dependency scan; see the comment above that option if a similar resolution error shows up for a new dependency.

## Related

- [ADR-0005: Replace PrimeVue with Nuxt UI](../6-decisions/ADR-0005-nuxt-ui-migration.md)
- [ADR-0006: Dual-tree, feature-flag-gated cutover strategy](../6-decisions/ADR-0006-nuxt-ui-dual-tree-toggle.md)
- [Feature 049 spec](../4-architecture/features/049-nuxt-ui-migration/spec.md) / [plan](../4-architecture/features/049-nuxt-ui-migration/plan.md)
- [Frontend Architecture](../3-reference/frontend-architecture.md) (currently documents the v7/PrimeVue tree; shared-layer content — stores, services, composables, conventions — applies equally to v8)
