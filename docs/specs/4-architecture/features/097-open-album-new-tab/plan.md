# Feature Plan 097 – Open Album in New Tab

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented — drift gate 2026-10-17  
_Last updated:_ 2026-10-17

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

A viewer who wants to keep the listing open gets the album in a new tab from the context menu's first entry or from a middle click.

- S-097-01 … 10 pass: `vp test` for the unit scenarios, Playwright on a scratch instance for the browser ones, in v7 and v8.
- `vp check --fix`, `vp run typecheck`, `vp test`, `php artisan test --filter=LangTest` green; no dependency, API or `lychee.d.ts` change (NFR-097-01).

## Scope Alignment

- **In scope:** first entry of the single-album context menu in both composables (FR-097-01 … 03), the shared helper (NFR-097-03), the label in 23 locales (NFR-097-02), the middle-click verification (FR-097-04), Vitest wiring (Q-097-02), the v8 root listing's menu guard on `selectableAlbums` (FR-097-06).
- **Out of scope:** modifier clicks (N-097-01), multi-album "open all" (N-097-02), photos (N-097-03), smart albums (N-097-04), the CI step for `vp test` (N-097-05).

## Dependencies & Interfaces

- `resources/js/composables/photo/albumRoute.ts` (`useAlbumRoute().albumRoutes().album`, the route name the tiles link to).
- `resources/js/composables/contextMenus/contextMenu.ts` (v7) and `resources/js/v8/composables/contextMenus/contextMenu.ts` (v8): `albumMenu()`; both are called from `<script setup>` so `useRouter()` is available inside them.
- v8 panels turn `Menu` into `UContextMenu` sections at dividers and drop empty sections; v7 panels render `is_divider` entries as `<Divider>`, hence the conditional divider of FR-097-01.
- `lang/<locale>/gallery.php` `menus` arrays; `tests/Unit/LangTest.php`.
- Vite+ `vp test` (Vitest 5, `jsdom`), `vite.config.ts` `baseConfig.test`.

## Assumptions & Risks

- **Assumptions:** none open. Q-097-01 → A (both trees) and Q-097-02 → A (unit tests) resolved by the owner on 2026-10-17.
- **Risks / Mitigations:**
  - A store imported by the composables touches the network at import time under jsdom → mock `useGlobalRights` and `vue-router` in the tests; stores only get created, never fetched.
  - A trailing divider for a viewer without rights (v7 renders it) → divider gated on an accessible later entry (FR-097-01).
  - `window.open` with a feature string opens a popup window in some browsers → `noopener` alone is the one feature that keeps a tab (FR-097-02).

## Implementation Drift Gate

After I4: table FR-097-* → file → evidence (unit test or browser scenario) under "Drift Gate Report" below; rerun `vp check`, `vp run typecheck`, `vp test`, `php artisan test --filter=LangTest`.

## Increment Map

1. **I1 – Spec, plan, tasks, open questions** (this document set)
   - _Exit:_ analysis gate below.
2. **I2 – Vitest wiring and failing tests** (NFR-097-03; S-097-01, 02, 03, 04, 07)
   - _Steps:_ `test` block in `vite.config.ts` (`include: resources/js/**/*.test.ts`, `environment: jsdom`); `resources/js/composables/album/openAlbumInNewTab.test.ts` (route mapping, `window.open` arguments); `resources/js/v8/composables/contextMenus/contextMenu.test.ts` and `resources/js/composables/contextMenus/contextMenu.test.ts` (first entry, divider, guest, multi-selection, callback). Confirm they fail (missing module, missing entry).
   - _Commands:_ `vp test`.
3. **I3 – Helper, composables, labels** (FR-097-01 … 03, NFR-097-02)
   - _Steps:_ `openAlbumInNewTab.ts` (`albumHref`, `openInNewTab`, license header, v7 style: function declarations); both `albumMenu()` return `[open, divider(hasAccessible(actions)), ...actions]`; `'open_in_new_tab'` after `'merge_all'` in 23 `gallery.php` files.
   - _Commands:_ `vp test`, `vp check --fix`, `vp run typecheck`, `php artisan test --filter=LangTest`.
   - _Exit:_ all green.
4. **I4 – Browser check and docs** (FR-097-04, S-097-05 … 10)
   - _Steps:_ scratch instance (SQLite in the scratchpad, `php artisan serve`, env overrides, never the development database) with one admin, a few albums and one public album; Playwright with Chromium: right-click menu order (owner, guest, inside an album), entry → new page URL, middle click on grid tile and list row → new page URL, left click, two-album menu; `NUXT_UI_ENABLED` true then false. Roadmap, knowledge map, session file, drift gate.
   - _Commands:_ scratchpad script; `vp build` for fresh assets.

### Branch tables

`albumHref(router, id)` → `albumRoutes().album`:

| Current route name | Route resolved |
|--------------------|----------------|
| `gallery`, `album`, `tags`, `search`, … | `album` |
| `flow`, `flow-album` | `flow-album` |
| `tag` with `photoId` | `tag` |
| `tag` without `photoId` | `album` |

`albumMenu()`:

| Selection | Menu |
|-----------|------|
| one album, some action accessible | open, divider, actions |
| one album, no action accessible | open |
| two or more albums | actions only (unchanged) |

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-097-01, 02, 07 | I2, I3 / T-097-01, 03, 04 | composable unit tests |
| S-097-03, 04 | I2, I3 / T-097-01, 02 | helper unit test + browser |
| S-097-05, 06, 08, 09, 10 | I4 / T-097-06 | browser |
| NFR-097-02 | I3 / T-097-05 | `LangTest` |
| FR-097-06 | I4 / T-097-06 | v8 `Albums.vue` guard, found by the guest browser check |

## Analysis Gate

2026-10-17, agent review:

1. Specification completeness — pass: goals, FR-097-01 … 05, NFR-097-01 … 04, mock-ups for the menu (owner, guest) and the middle click.
2. Open questions review — pass: Q-097-01 → A (both trees) and Q-097-02 → A (Vitest kept) resolved by the owner on 2026-10-17; neither is architectural (no ADR).
3. Plan alignment — pass: increments cite the FR/NFR/S IDs; dependencies name the two composables, the route helper and `vp test`.
4. Tasks coverage — pass: FR-097-01 → T-097-03/04, FR-097-02 → T-097-02, FR-097-03 → T-097-01/03/04, FR-097-04/05 → T-097-06, NFR-097-02 → T-097-05; tests (T-097-01) before code; every task under 90 minutes.
5. Working agreements — pass: spec-first, no dependency (NFR-097-01), logic in a pure helper, divider rule as a tiny predicate; no ADR references this feature.
6. Tooling readiness — pass: `vp test`, `vp check --fix`, `vp run typecheck`, `php artisan test --filter=LangTest`; no PHP code change, so `php-cs-fixer` and `phpstan` are not in this feature's gate (lang files are PHP arrays: `php-cs-fixer` run on them anyway).

## Drift Gate Report

2026-10-17, agent review after T-097-01 … 07. Gate: `vp check` (0 errors, 12 pre-existing warnings outside this feature), `vp run typecheck`, `vp test` (3 files, 13 tests), `php artisan test --filter=LangTest` (2), `php scripts/check_translations.php` all green. No PHP code change; `package.json`, `composer.json`, `resources/js/lychee.d.ts` unchanged (NFR-097-01).

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-097-01 | v8 and v7 `contextMenu.ts` `albumMenu()` → `[open, divider(hasAccessibleItem(actions)), …actions]` | unit S-097-01/02 in both trees; browser v8 and v7 S-097-01 (owner, root), S-097-10 (inside an album: open, divider, `Set Album Cover`, …), S-097-02 (guest without download grant: entry alone; with the grant: entry, divider, `Download`) |
| FR-097-02 | `composables/album/openAlbumInNewTab.ts` `albumHref()`, `openInNewTab()` | unit S-097-03/04 (route mapping, `window.open` arguments); browser S-097-03 and S-097-02b in both trees (new page on `/gallery/<id>`, current tab still on the listing) |
| FR-097-03 | `albumsMenu()` unchanged | unit and browser S-097-07 in both trees (`Merge Selected`, `Move Selected`, `Delete Selected`, `Download Selected`) |
| FR-097-04 | no code: tiles are `router-link` anchors; `dragAndSelect.ts` `canStart` requires button 0 | browser S-097-05 (grid tile, owner and guest, both trees) and S-097-06 (list view: both row links, both trees) open a new page on the album route with the current tab unchanged |
| FR-097-05 | unchanged | browser S-097-08 in both trees (Ctrl+click stays on `/gallery`, plain click navigates) |
| FR-097-06 | v8 `views/gallery-panels/Albums.vue` `:disabled="albumsStore.selectableAlbums.length === 0"` | browser v8 S-097-02: no menu for the guest before the change, menu with the entry after |
| NFR-097-02 | 23 `lang/<locale>/gallery.php` | `LangTest`, `check_translations.php` |
| NFR-097-03 | helper + `openAlbumInNewTab.test.ts`, v8 and v7 `contextMenu.test.ts` | `vp test` 13/13 (red before the code: 6 failed, 2 passed) |
| NFR-097-04 | `lucide:external-link` (bundled Lucide, already used by `Sharing.vue`), `pi pi-external-link` (PrimeIcons) | code review |

Browser runs (Playwright, system Chromium, scratch instance on port 8123, SQLite in the scratchpad, `php artisan serve`, env overrides for DB, cache, session, queue, uploads and the frontend flags; admin + three imported folders, one public root album):

| Tree | Flags | Main phase | List phase |
|------|-------|-----------|------------|
| v8 | `NUXT_UI_ENABLED=true`, `STRUCT_OF_ARRAY_ENABLED=true` | 12/12 | 3/3 |
| v7 | `NUXT_UI_ENABLED=false`, `STRUCT_OF_ARRAY_ENABLED=true` | 8/8 owner (guest not reachable, see below) | 3/3 |
| v7 | `NUXT_UI_ENABLED=false`, `STRUCT_OF_ARRAY_ENABLED=false` | 12/12 | — |

Divergences: FR-097-06 added during the browser check (spec, plan, tasks updated). The v8 guest scenario first showed `Open in a new tab`, divider, `Download` because the seeded permission granted downloads; the spec's S-097-02 now names both cases.

Observed, out of scope: with `STRUCT_OF_ARRAY_ENABLED=true` the v7 root listing requests `root/buckets` and `root/rights` for the shared scope as a guest but not `root/children`, so a guest sees the sign-in prompt and no public album; with the flag off (the v7 default) the v2 listing shows it. Pre-existing, Feature 062 territory.

Lessons: Nuxt UI renders context-menu sections as `role="group"` containers with a CSS divider (no separator element); PrimeVue's context menu root is `.p-contextmenu` and a speed dial on the same page also carries `role="menu"`. `vp check --fix` applies pre-existing autofixes outside the change set (and the `no-useless-escape` fix on `<\/script>` inside a template literal breaks `EmbedCodeDialog.vue`), so the gate ran `vp check` without `--fix` after reverting those files. `lang/` is outside the php-cs-fixer finder: never pass it as an explicit path. Playwright's bundled Chromium build did not match the installed one: `executablePath: "/usr/bin/chromium"`. A `pkill -f` pattern that appears in the calling command line kills the caller.

## Exit Criteria

- T-097-01 … 07 checked; gate commands green; roadmap and knowledge map updated; drift gate report below.

## Follow-ups / Backlog

- `vp test` step in `.github/workflows/js_check.yml` (N-097-05, owner).
