# ADR-0006: Dual-tree, feature-flag-gated cutover strategy for the Nuxt UI migration

- **Status:** Accepted
- **Date:** 2026-07-02
- **Related features/specs:** Feature 049 (docs/specs/4-architecture/features/049-nuxt-ui-migration/spec.md)
- **Related open questions:** Q-049-04
- **Relationship to ADR-0005:** Amends ADR-0005's *Decision* item 1 and its "coexistence" framing (Consequences/Alternatives). ADR-0005's resolutions for Q-049-01 (full-scope sizing), Q-049-02 (icon parity via `@iconify-json/prime`), and Q-049-03 (drop ripple) are unchanged and still govern this feature.

## Context

ADR-0005 committed Feature 049 to an **in-place, file-by-file** migration: every PrimeVue-coupled file under `resources/js/` is edited in place to use Nuxt UI, with PrimeVue and Nuxt UI coexisting only transitionally, in the *same* running app instance, until a final increment (FR-049-18) removes PrimeVue entirely.

After ADR-0005 was recorded, the user asked whether the two UI libraries could instead run as two **complete, independent frontends** ("one PrimeVue (v7), one Nuxt UI (v8)") rather than a single app mid-migration, and — on further clarification — that this should be a **feature toggle**, with both variants serving the **same URL paths** (no `/v8`-prefixed routes, no separate subdomain).

This is a materially different implementation mechanism from ADR-0005's, with different risk/effort tradeoffs, so it is recorded as its own ADR rather than silently edited into ADR-0005.

The codebase already has a working precedent for a per-request server-side toggle of this shape: `app/Assets/Features.php`'s `Features::active('feature_name')` (backed by `config/features.php`) is already used in `resources/views/vueapp.blade.php` to conditionally branch behavior (`legacy_v4_redirect`). It is a global, environment/config-driven flag — not a per-user preference.

## Decision

Build the Nuxt UI implementation as a **parallel file tree**, served from a **second Vite entry/bundle**, selected **per HTTP request** by a Laravel feature flag, with **both bundles registering identical route paths**. Concretely:

1. **New feature flag:** `nuxt_ui` in `config/features.php`, checked via `Features::active('nuxt_ui')` — same mechanism as the existing `legacy_v4_redirect` flag. Global (per environment/instance), not per-user.
2. **Second Vite entry:** `resources/js/app-v8.ts` (+ `resources/sass/app-v8.css`), added to the existing `laravel-vite-plugin` `input` array in `vite.config.ts` alongside `app.ts`. This does **not** require a separate Vite config file (that pattern is reserved for the embed widget's library build, which has different `build.lib` output requirements) — it's just a second entry in the same config.
3. **Blade-level branch, not a client-side one:** `resources/views/vueapp.blade.php` chooses which compiled bundle to send:
   ```blade
   @if(Features::active('nuxt_ui'))
       @vite(['resources/js/app-v8.ts', 'resources/sass/app-v8.css'])
   @else
       @vite(['resources/js/app.ts', 'resources/sass/app.css'])
   @endif
   ```
   Both mount to the same `<div id="app">`. Exactly one of the two component libraries is ever loaded into a given page — they are never both active in the same document, which sidesteps ADR-0005's noted coexistence risk (double CSS reset/focus-ring/`cssLayer` ordering conflicts) entirely, rather than merely mitigating it.
4. **Same paths, two trees:** route **path/name definitions** are factored into a new, component-free manifest, `resources/js/router/paths.ts` (`{ name, path, meta }` only — no `component:` references). The existing `resources/js/router/routes.ts` (v7) and a new `resources/js/v8/router/routes.ts` (v8) both consume `paths.ts` and attach `component:` imports from their own tree (`resources/js/views/**` vs `resources/js/v8/views/**`). This guarantees both UIs are reachable at identical URLs — deep links, bookmarks, and any external integration hitting a specific Lychee path work the same regardless of which UI is currently active.
5. **New parallel tree, `resources/js/v8/`:** mirrors every PrimeVue-coupled top-level directory 1:1 — `v8/views/**`, `v8/components/**`, `v8/menus/LeftMenu.vue`, `v8/composables/useAppToast.ts` + `useConfirmDialog.ts` (these only make sense where Nuxt UI's `useToast()`/`<UModal>` are mounted, i.e. only in the v8 bundle), `v8/components/modals/ConfirmModalHost.vue`, `v8/components/icons/PiMiniIcon.vue`. **Everything not PrimeVue-coupled is shared, unduplicated**, and imported by both bundles unchanged: `stores/**` (Pinia), `services/**`, most of `composables/**`, `types/**`, `utils/**`, `config/axios-config.ts`, i18n loading, and `composables/contextMenus/contextMenu.ts` (menu-item construction is pure data, not UI).
6. **v7 stays untouched** for the entire build-out. No file under `resources/js/views/**`, `components/**`, `menus/**` is edited by this migration until the Cutover phase — they continue to receive ordinary maintenance (bug fixes, new features) as always, on their own merits, completely independent of Feature 049's progress.
7. **Cutover is all-or-nothing per environment:** because the flag switches the *entire* bundle, not a single route, `nuxt_ui` can only be safely enabled somewhere real users hit once **every** route in `paths.ts` resolves to a working `v8/views/**` component (a route-parity coverage gate). Before that point, the flag can still be exercised in local/dev/staging environments to test whatever portion of `v8/` exists so far — that partial-coverage testing is a development convenience, not a claim of partial production readiness.
8. **Final removal** happens after cutover is confirmed stable: delete `resources/js/views/**`/`components/**`/`menus/**` (v7), delete `app.ts`, delete `resources/js/style/preset.ts`, remove the `nuxt_ui` flag and the blade branch (down to a single unconditional `@vite([...])`), and remove `primevue`/`@primeuix/themes`/`tailwindcss-primeui`/`primeicons` from `package.json` — this is the same hard dependency-removal gate ADR-0005 already established (FR-049-18/NFR-049-07), just reached via cutover rather than via a converging single-tree edit history.

## Consequences

### Positive

- **Zero production risk during the entire build-out.** The default-served bundle (`app.ts`, v7) is never touched until cutover, so a multi-session, possibly-long-running migration can never leave production users looking at a half-migrated app — a real risk ADR-0005 accepted implicitly (continuous increments landing in the one shared app).
- **Instant, zero-deploy rollback.** If `nuxt_ui` is enabled and a critical issue surfaces, flipping the config flag back is immediate — no revert-and-redeploy of a partially-migrated single tree.
- **No single-page library coexistence.** PrimeVue and Nuxt UI are never both mounted in the same document, eliminating (not just mitigating) the double-CSS-reset/ripple/focus-ring risk ADR-0005 flagged for its transitional coexistence period.
- **A real dogfood/beta period becomes possible.** `v8` can be fully exercised end-to-end (every route, both light/dark mode) in a staging environment or by internal users before it's ever exposed broadly, rather than users perpetually seeing a part-PrimeVue/part-Nuxt-UI app as increments land.
- **Same-path guarantee** (via `router/paths.ts`) means no bookmarks, deep links, or third-party integrations pointing at specific Lychee URLs are affected by which UI is currently active.

### Negative

- **Larger total duplicated surface for the build-out window.** This is not a smaller migration than ADR-0005's — it's the same ~235-file PrimeVue-coupled surface, rebuilt fresh in `v8/` rather than edited in place. Nothing is "reused" from v7's `.vue` files beyond using them as a visual/behavioral reference.
- **No incremental production value until full route-parity.** Under ADR-0005's plan, every completed increment immediately improved the one real running app. Under this ADR, progress is invisible to production until the coverage gate is met and the flag is flipped — all value is realized at once, at cutover.
- **New architectural surface not present in ADR-0005's plan:** the `router/paths.ts` shared-manifest indirection, two app shells (`views/App.vue` vs `v8/views/App.vue`), two `PiMiniIcon.vue` wrappers, two icon wiring paths. Small individually, but it's surface ADR-0005's in-place approach didn't need.
- **Both bundles are built and type-checked together for the whole window.** `npm run build` and `npm run check` cover both `app.ts` and `app-v8.ts` from the day the flag lands until final removal, so CI cost (build time, type-check time) is higher throughout than ADR-0005's single-tree plan, not just during a short transitional period.
- **All-or-nothing cutover per environment.** Unlike ADR-0005's directory-by-directory grep-verified sweeps, there is no meaningful concept of "50% cut over" in one environment — a route with no `v8/` implementation yet is simply broken if a user lands on it with the flag on. This pushes all integration risk to the single cutover moment (mitigated by the coverage gate and staging dogfood, but not eliminated).

## Alternatives Considered

- **A (chosen) — Dual tree + feature flag + same-path, whole-app cutover.** Described above.
- **B — Path-prefix split (e.g. `/v8/*` routes, or a separate subdomain).** Raised and rejected in the same conversation: the user explicitly wants both UIs reachable under the *same* paths, so bookmarks/deep links/external integrations are unaffected by which UI is active, and so switching the flag doesn't relocate the app to a different URL structure.
- **C — ADR-0005's original mechanism (in-place, file-by-file, single-bundle transitional coexistence).** Superseded as the default strategy per the user's explicit request for two parallel, toggleable UIs. Retained as the rationale for why route paths, Pinia stores, services, and most composables are *not* being duplicated here — ADR-0005's "coexistence is a real, unavoidable-for-strangler-fig-migrations cost" reasoning is exactly why this ADR minimizes what's duplicated to the UI layer only, rather than duplicating the whole frontend.

## Security / Privacy Impact

None beyond ADR-0005's assessment. The feature flag is a global, admin/environment-controlled config value (same class as `dark_mode_enabled`, `legacy_v4_redirect`), not user-settable, so it introduces no new per-user data exposure or access-control surface.

## Operational Impact

- `config/features.php` gains one new boolean entry (`nuxt_ui`), following the existing pattern for that file.
- Deployments must build both `app.ts` and `app-v8.ts` outputs (`npm run build` already covers both once `app-v8.ts` is added to the Vite input array) — no new build *step*, but a larger build output until v7 is removed.
- Enabling `nuxt_ui` in a given environment is an operational action (a config/env change) gated behind the route-parity coverage check (new FR-049-23) — this should be treated with the same care as any other environment-flip that changes what all users of that environment see, not as a routine per-increment deploy.
- No new telemetry, logging, or observability surface (consistent with ADR-0005 and Feature 049's spec).

## Addendum (2026-07-03): v7 physically relocated to `resources/js/v7/`

Once route-parity coverage (FR-049-23) reached 46/46, the v7 tree was moved — `resources/js/views/**` → `resources/js/v7/views/**`, `resources/js/components/**` → `resources/js/v7/components/**`, `resources/js/menus/**` → `resources/js/v7/menus/**`, `resources/js/router/routes.ts` → `resources/js/v7/router/routes.ts`, `resources/js/style/preset.ts` → `resources/js/v7/style/preset.ts` — so that Item 8's "Final removal" step becomes a single `rm -rf resources/js/v7` plus the `app.ts`/flag/dependency cleanup, instead of enumerating three top-level directories by name. `resources/js/app.ts` (the v7 entry point) and `resources/js/router/paths.ts` (the shared manifest) stay at the top level, matching `app-v8.ts`'s position and `v8/`'s existing pattern of importing shared infra (`stores/`, `services/`, `composables/`, `utils/`, `config/`) unprefixed. This was a pure `git mv` + import-path rewrite (no behavioral change, no v7 logic edited); see `docs/specs/4-architecture/features/049-nuxt-ui-migration/tasks.md` for verification. All other file-path references in this ADR and in `spec.md`/`plan.md` describing `resources/js/{views,components,menus}/**` refer to this now-relocated `resources/js/v7/{views,components,menus}/**`.

## Links

- Related spec sections: `docs/specs/4-architecture/features/049-nuxt-ui-migration/spec.md` (Overview, Goals, Non-Goals, FR-049-01/03/04/05/18, new FR-049-22/23/24, NFR-049-07)
- Related plan: `docs/specs/4-architecture/features/049-nuxt-ui-migration/plan.md` (Increment Map — new I0 scaffolding increment, revised I1-I43 target paths, revised cutover increment)
- Related open questions: Q-049-04 (docs/specs/4-architecture/open-questions.md)
- Related ADRs: ADR-0005 (amended by this ADR — sizing/icon/ripple decisions unchanged, implementation-mechanism decision superseded)
