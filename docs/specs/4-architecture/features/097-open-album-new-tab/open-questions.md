# Open Questions – Feature 097

Open questions for [Feature 097](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-097-01~~ | 097 – Open album in new tab | Medium | Which frontends get the entry: v8 is behind `NUXT_UI_ENABLED` (off by default), so v7 is the shipped UI (A both v7 and v8 · B v8 only) | Resolved (Option A; owner, 2026-10-17; spec FR-097-01, S-097-09) | 2026-10-17 | 2026-10-17 |
| ~~Q-097-02~~ | 097 – Open album in new tab | Medium | First frontend unit tests of the repository: Vitest ships with Vite+ (`vp test`, jsdom already a devDependency), nothing runs them in CI yet (A keep the tests, `test` block in `vite.config.ts`, CI step as a follow-up · B browser check only, no unit tests) | Resolved (Option A; owner, 2026-10-17; spec NFR-097-03, N-097-05) | 2026-10-17 | 2026-10-17 |

## Question Details

### ~~Q-097-01~~ – v7 and v8, or v8 only

**Context:** The request names no frontend. The album context menu is built by two near-identical composables, `resources/js/composables/contextMenus/contextMenu.ts` (v7, PrimeVue `ContextMenu`, `pi` icons) and `resources/js/v8/composables/contextMenus/contextMenu.ts` (v8, `UContextMenu`, `lucide` icons). Album tiles are `router-link` anchors in both trees, so the middle-click half of the request already holds in both. v8 is served only with `NUXT_UI_ENABLED=true` (`config/features.php`, default `false`), so an installation on the defaults shows v7.

- **Option A (recommended):** Add the entry to both composables; one shared helper builds the URL and opens the tab; one label key in every locale.
  - ✅ Users on the default (v7) installation get the entry.
  - ✅ Ten lines per composable, no new module in v7.
  - ❌ v7 is on its way out; a few more lines to drop with it.
- **Option B:** v8 only.
  - ✅ No v7 change.
  - ❌ The default installation does not get the entry.

**Resolution:** Option A (owner, 2026-10-17). Both trees carry the entry (FR-097-01, S-097-09).

### ~~Q-097-02~~ – Frontend unit tests with `vp test`

**Context:** Earlier frontend features declared a JS unit-test runner a non-goal because it needed a new dependency (Feature 071 NG8, N-094-07). Vite+ (Feature 096) bundles Vitest, reachable as `vp test`, and `jsdom` is already a devDependency, so unit tests now cost no dependency. This feature's URL and menu-order rules are the kind of pure logic a unit test pins down better than a browser run. Nothing runs `vp test` in CI (`js_check.yml` runs `vp check` and `vp run typecheck`).

- **Option A (recommended):** Keep the three `*.test.ts` files and the `test` block in `vite.config.ts` (`include: resources/js/**/*.test.ts`, `environment: jsdom`, so Playwright's `e2e/` specs are not picked up); add a `vp test` step to `js_check.yml` as a follow-up the owner schedules.
  - ✅ Executable spec for S-097-01, 03, 04, 07; `vp test` runs in about a second.
  - ✅ No dependency, no new script.
  - ❌ Until the CI step exists the tests guard only local runs.
- **Option B:** Drop the unit tests and the config block; rely on the browser check.
  - ✅ No tooling change.
  - ❌ The menu order and the route mapping are checked only by hand.

**Resolution:** Option A (owner, 2026-10-17). The tests and the `test` block stay (NFR-097-03); the CI step is a follow-up (N-097-05).
