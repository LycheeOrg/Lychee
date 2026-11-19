# Feature Plan 110 – Gallery Size Configuration

_Linked specification:_ [spec.md](spec.md)
_Linked tasks:_ [tasks.md](tasks.md)
_Status:_ Implemented
_Last updated:_ 2026-11-07

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Admins choose album and photo tile sizes with immediate visual feedback: on placeholders at several screen widths (page) or on the real gallery (Spotlight drawer). Success: the 8 settings (DO-110-03) are editable on both surfaces, the preview and drawer geometry equal the gallery's (NFR-110-01), album tiles follow `album_thumb_min_width` (ADR-110-01), covers are sharp on 2× screens (FR-110-12), v7 unchanged (NFR-110-07).

## Scope Alignment
- **In scope:** FR-110-01 … FR-110-15, NFR-110-01 … NFR-110-08.
- **Out of scope:** spec Non-Goals (v7, real data on the page, album gap, per-user sizes, defaults reset, gap 0, `medium` covers, other drawer entry points).

## Dependencies & Interfaces
- Back end: `BaseConfigMigration` (`App\Models\Extensions`), `SettingsController::V8_CONFIGS`, `InitConfig`, `lang/*/all_settings.php`, `routes/web_v2.php`.
- Front end: `albumTileWidth.ts` (`computeAlbumTileGeometry`, `useAlbumTileWidth`), `analyticPhotoLayout.ts` (`computeBucketLayout`), `wasmLayouts.ts` (`initLayouts`), `aspectRatioNumber.ts`, `LayoutState`, `LycheeState`, `AlbumsState`, `AlbumState`, `ModalsState`, `SettingsService` (`getAll`, `setConfigs`), `useAdminTiles.ts`, `router/paths.ts`, `v8/router/routes.ts`, `useSpotlightGalleryActions.ts` / `SpotlightSearch.vue`, `LiveMetrics.vue` (USlideover precedent), `WatermarkPreview.vue` (page precedent), `dragAndSelect.ts`.
- Types: `php artisan typescript:transform` (via `vp run typecheck`'s `pretypecheck` → `scripts/generate-js-types.sh`).

## Assumptions & Risks
- **Assumptions:** WASM layouts initialise in the page as in the gallery (`initLayouts()`); `SettingsService.getAll()` returns `default_album_thumb_aspect_ratio`, `number_albums_per_row_mobile` and the five `photo_layout_*` in the `Gallery` category for admins.
- **Risks / Mitigations:**
  - Album grids use `useElementSize`/virtualizer caches: changing geometry live may desync tanstack-virtual (documented in `albumTileWidth.ts`). Mitigation: grids already re-measure on `itemsPerRow`/`tileWidth` change (`AlbumThumbGridVirtual.vue` watcher); browser check S-110-17 on a large album.
  - Live override must reach drag selection: route all reads through the same composables (DO-110-04) and cover with Vitest.
  - `vp check --fix` repo-wide rewrites `EmbedCodeDialog.vue` (memory): run `vp check --fix` on changed files only.
  - Shared test SQLite DB: never two test commands at once.

## Implementation Drift Gate

Run 2026-11-07 by the agent after T-110-14 (15/15 tasks).

**Preconditions.** All tasks `[x]`. Gate green: php-cs-fixer (0 fixes), `make phpstan` (no errors), `vp check --fix` on the changed files (formatting only, 0 lint findings; `EmbedCodeDialog.vue` untouched), `vp run typecheck`, Vitest `resources/js` 32 files / 230 tests, `AlbumThumbMinWidthConfigTest` 6/6, `PanZoomConfigTest` 7/7, `LangTest` 2/2, `ConfigTranslationKeysTest` 8/8. Migration applied on the dev DB and the scratch DB (owner rule: no migration test).

**Traceability.**

| Requirement | Code | Tests / evidence |
|-------------|------|------------------|
| FR-110-01, 02 | migration `2026_11_07_000001_add_album_thumb_configs.php`, `SettingsController::V8_CONFIGS`, `InitConfig`, `LycheeState`, `albumTileWidth.ts` | `AlbumThumbMinWidthConfigTest`, `albumTileWidth.test.ts`; browser 183 px tiles at 1440 (7 columns) |
| FR-110-03 | `computeAlbumTileGeometry` below `sm` | `albumTileWidth.test.ts`, `galleryPreviewGeometry.test.ts` (Phone) |
| FR-110-04 | `paths.ts`, `routes/web_v2.php`, `v8/router/routes.ts`, `GallerySizes.vue`, `useAdminTiles.ts` | browser: tile, logged-out redirect (Settings link checked, then removed 2026-11-07) |
| FR-110-05 … 09 | `GallerySizesControls.vue`, `GallerySizesPreview.vue`, `gallerySizes.ts`, `galleryPreviewPlaceholders.ts`, `galleryPreviewGeometry.ts` | 3 helper test files (23); browser: stored values, Laptop 7, Wide 13, Phone 3, min 300 → 4, masonry edit selects Masonry, 200 photos, aspect 2:3, 0 requests while editing |
| FR-110-10, 11 | `useGallerySizesDraft.ts` | `useGallerySizesDraft.test.ts` (8); browser: one `setConfigs`, gallery at 263 px without reload, Revert clears the dot |
| FR-110-12 | `AlbumThumbVirtual.vue` | `AlbumThumbVirtual.test.ts`; browser: tile covers `small` at 1×, `small2x` at 2× |
| FR-110-13 | `canOfferLiveGallerySizes`, `useSpotlightGalleryActions.ts` | `gallerySizesLive.test.ts` (11 gate cases); browser: action on `/gallery`, absent on Settings |
| FR-110-14, 15 | `gallerySizesLive.ts`, `GallerySizesLive.vue`, `ModalsState.ts`, `App.vue`, consumers (4 tiles, 2 album grids, `PhotoGridVirtual.vue`, `dragAndSelect.ts`) | `gallerySizesLive.test.ts`; browser: no move on open, 448 px at live 400, stays open on SPA navigation into "Big", masonry 150 re-lays photos, close restores layout and 263 px, leaving to Settings closes and discards |
| NFR-110-01 | `computePreviewGeometry`, `photoTargetAndGap` shared by preview, `PhotoGridVirtual.vue`, `dragAndSelect.ts` | `galleryPreviewGeometry.test.ts` |
| NFR-110-02 | plain blocks, no images | browser, 100 albums + 500 photos, input → Vue render + browser layout (no paint): median 10.2–12.1 ms, worst of 20 changes 22.8–32.0 ms per layout |
| NFR-110-05 | `lang/*/gallery_sizes.php`, `lang/*/all_settings.php` ×23 | `LangTest`, `ConfigTranslationKeysTest`, key/placeholder parity script |
| NFR-110-06 | labels, `aria-pressed`, `aria-hidden` canvas, `role=status` summary | browser: arrow key moves the slider one step (stored 250 → 255) |
| NFR-110-07 | no `resources/js/v7/` change | `git status` |

**Divergences.**
- NFR-110-02 (low impact, documented, no question): the median fits one frame; single changes at the maximum counts reach 23–32 ms (measured end to end, the layout computation alone was not isolated). Only the preview page at 100 albums + 500 photos.
- Spec amended during implementation (lightweight): FR-110-05 mobile control shown only when `Settings` returns it (SE-only setting); FR-110-11 also updates the open album's config; FR-110-14 shortcuts off while the drawer is open and override starts after load; NFR-110-07 adds `ModalsState.ts`. Settings link placed in both the Gallery category tab (`Settings.vue`) and the "all" view, through one component.
- Not browser-checked: the SE-only "Per row on phones" control (scratch instance is not SE; covered by `useGallerySizesDraft.test.ts` editable keys), the non-admin view (S-110-12; tile and Spotlight gates follow existing patterns, the gate is unit-tested), v7 serving `/admin/gallery-sizes` as Placeholder.

**Lessons.** A composable created at app start (the drawer) must re-read session state when it opens; caught while writing the drawer, not by a test. `pkill -f` with a pattern that also appears in the running command line kills the shell running it.

### Drift gate addendum – album gap (I11, 2026-11-07)

20/20 tasks. Gate: `AlbumThumbGapConfigTest` 7, `AlbumThumbMinWidthConfigTest` 6, `LangTest` 2, `ConfigTranslationKeysTest` 8, php-cs-fixer 0 fixes, phpstan no errors, typecheck, Vitest `resources/js` 32 files / 238 tests, scoped `vp check` clean. Migration `2026_11_07_000001_add_album_thumb_configs.php` applied on the dev DB and the scratch DB.

| Requirement | Code | Tests / evidence |
|-------------|------|------------------|
| FR-110-16 | migration, `V8_CONFIGS`, `InitConfig`, `LycheeState`, `albumGapForBreakpoint()` in `albumTileWidth.ts`, `computeAlbumTileGeometry(…, { minWidth, perRowMobile, gap })`, `useEffectiveAlbumTileSettings().gap`, `AlbumThumbPanel.vue` | `AlbumThumbGapConfigTest`, `albumTileWidth.test.ts` (rule, default = 4/8/16, 30 → 6 columns of 204.5 px, 0 → 7 touching); browser: virtual grid row `column-gap` and smart-album panel spacing 4/8/16 (default), 8/15/30 (30), 0 (0) at 390/700/1440 |
| FR-110-05, 11, 14 (gap) | `gallerySizes.ts` (9 keys, 0 accepted), `GallerySizesControls.vue`, `useGallerySizesDraft.ts`, `gallerySizesLive.ts` | `gallerySizes.test.ts`, `useGallerySizesDraft.test.ts`, `gallerySizesLive.test.ts`, `galleryPreviewGeometry.test.ts`; browser: drawer gap 40 moves the grid widths (196 px) and the smart panel (40 px), close returns to 16; preview Laptop 7 → 6 per row at 30 |

Divergences: none new. Found during the increment: `AlbumThumbPanel.vue` (smart, tag, person, pinned albums; tag page) had its own Tailwind gap classes and would have ignored the setting; now it reads the shared geometry. A geometry test still on the old 4-argument call passed only through the default fallback; typecheck caught it.

## Increment Map

1. **I1 – Setting and `InitConfig`** (FR-110-01 server side, FR-110-02)
   - _Steps:_ `AlbumThumbMinWidthConfigTest` (red) → migration `2026_11_07_000001_add_album_thumb_configs.php` → `V8_CONFIGS` → `InitConfig` → `all_settings.php` ×23 → `LycheeState` field → regenerate types.
   - _Commands:_ `php artisan test --filter=AlbumThumbMinWidthConfigTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`, `vp run typecheck`.
2. **I2 – Minimum-width tile rule** (FR-110-01, FR-110-03, S-110-01 … 03, 13, 14)
   - _Steps:_ `albumTileWidth.test.ts` (red) → `computeAlbumTileGeometry(viewportWidth, containerWidth, breakpoint, perRowMobile, minWidth)` → remove `TILE_WIDTH_FORMULA` → `useAlbumTileWidth` reads `album_thumb_min_width`.
   - _Commands:_ `vp test resources/js/v8/composables/album/albumTileWidth.test.ts`, `vp run typecheck`.
3. **I3 – Cover variant by density** (FR-110-12, S-110-15)
   - _Steps:_ guard test (red) → drop `type="small"` on the 3 cover `Thumb`s of `AlbumThumbVirtual.vue`.
4. **I4 – Pure helpers** (DO-110-02, DO-110-03, FR-110-05, FR-110-07, FR-110-09, NFR-110-01, NFR-110-03)
   - _Steps:_ tests (red) → `v8/utils/gallerySizes.ts` (type, ranges, clamp, parse, changed-keys payload) → `v8/utils/galleryPreviewPlaceholders.ts` (seeded PRNG) → `v8/utils/galleryPreviewGeometry.ts` (presets → container width, breakpoint, scale; albums via `computeAlbumTileGeometry`, photos via `computeBucketLayout`).
5. **I5 – Draft composable** (FR-110-10, FR-110-11, S-110-09 … 11)
   - _Steps:_ `useGallerySizesDraft.test.ts` (red, mocked `SettingsService`) → `v8/composables/gallerySizes/useGallerySizesDraft.ts` (load, values, stored, dirty, revert, save, apply to stores).
6. **I6 – Controls and preview components** (FR-110-05 … 09, NFR-110-06)
   - _Steps:_ `GallerySizesControls.vue` (8 controls, layout switch, emits layout focus), `GallerySizesPreview.vue` (placeholders, presets, scale, summary).
7. **I7 – Page, route, tile, link, lang** (FR-110-04, API-110-03, NFR-110-05, S-110-04 … 08, 12, 13)
   - _Steps:_ `paths.ts`, `routes/web_v2.php`, `v8/router/routes.ts`, `v8/views/admin/GallerySizes.vue`, `useAdminTiles.ts` tile, Settings › Gallery link, `lang/*/gallery_sizes.php` ×23 (+ v8 i18n registration if needed).
8. **I8 – Live override and consumers** (DO-110-04, FR-110-14 geometry, NFR-110-01)
   - _Steps:_ `gallerySizesLive.test.ts` (red) → store `useGallerySizesLiveStore` → `useAlbumTileWidth` reads override → `useAlbumThumbAspectRatio()` (replaces the 4 inline fallbacks + 2 grid computeds + `dragAndSelect.ts`) → `usePhotoLayoutConfig()` (used by `PhotoGridVirtual.vue`, `dragAndSelect.ts`).
9. **I9 – Live drawer and Spotlight action** (FR-110-13 … 15, S-110-16 … 21)
   - _Steps:_ Spotlight gating test (red) → `is_gallery_sizes_live_open` in `ModalsState` → `v8/components/drawers/GallerySizesLive.vue` (USlideover, non-modal, reuses controls + draft) mounted in `App.vue` → action in `useSpotlightGalleryActions.ts` → close on leaving gallery routes.
10. **I10 – Verification and docs** (NFR-110-02, NFR-110-06, NFR-110-07)
    - _Steps:_ full quality gate, browser checks on the scratch instance, knowledge map, roadmap, drift gate, commit command.

11. **I11 – Album gap setting** (FR-110-16, S-110-22 … 24, Q-110-05 A)
    - _Steps:_ `AlbumThumbGapConfigTest` (red) → migration `2026_11_07_000001_add_album_thumb_configs.php` (`int`, 16), `V8_CONFIGS`, `InitConfig`, `LycheeState`, `all_settings.php` ×23, types → `albumTileWidth.test.ts` gap cases (red) → `albumGapForBreakpoint()` replaces `GAP_PX`, `computeAlbumTileGeometry(containerWidth, breakpoint, settings)` → `AlbumThumbPanel.vue` flex gaps from `useAlbumTileWidth().gap` → `GallerySizes` gains `album_thumb_gap` (parse allows 0), draft/live/controls/preview tests (red) then code → gate, browser check, docs.
    - _Commands:_ `php artisan test --filter=AlbumThumbGapConfigTest`, `vp test resources/js`, `vp run typecheck`, `vp check --fix <files>`, `make phpstan`, `vendor/bin/php-cs-fixer fix`, `php artisan test --filter=LangTest`, `php artisan test --filter=ConfigTranslationKeysTest`.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-110-01, 02 | I1, I2 / T-110-01 … 03 | Feature_v3 test + Vitest + browser |
| S-110-03 | I2 / T-110-03 | Vitest |
| S-110-04 … 08 | I4, I6, I7 / T-110-05, T-110-07 … 09 | Vitest helpers + browser |
| S-110-09 … 11 | I5 / T-110-06 | Vitest |
| S-110-12 | I7, I9 / T-110-09, T-110-10, T-110-13 | tile / link / action gating |
| S-110-13, 14 | I2, I4 / T-110-03, T-110-05 | Vitest |
| S-110-15 | I3 / T-110-04 | guard test + browser |
| S-110-16 … 21 | I8, I9 / T-110-11 … 13 | Vitest + browser |
| S-110-22 … 24 | I11 / T-110-16 … 20 | Feature_v3 test + Vitest + browser |

## Analysis Gate

Run 2026-11-07 by the agent (spec, plan, tasks, open questions, ADR-110-01, ADR-102-01 reviewed).

1. Specification completeness: ✅ goals, FR-110-01 … 15, NFR-110-01 … 08 populated; Q-110-01 … 04 folded into normative sections; ASCII mock-ups for page (wide, narrow), Spotlight entry and drawer.
2. Open questions: ✅ none open (`grep` listing empty). ADR-110-01 covers the two high-impact answers (Q-110-01, Q-110-04); Q-110-02/03 (medium) live in the spec.
3. Plan alignment: ✅ links to spec and tasks; success criteria match the spec wording.
4. Tasks coverage: ✅ every FR maps to a task (table in tasks.md notes); tests precede code in each task; increments ≤ 90 min (I7 and I9 split into two tasks each).
5. Working agreements: ✅ no dependency added; helpers pure (`gallerySizes.ts`, placeholders, geometry) to keep component control flow flat; ADRs for Feature 102 reviewed (density rule reused, ADR-102-01 unaffected).
6. Tooling: ✅ commands per increment above; `vp check --fix` scoped to changed files.

Findings: none blocking. Lightweight decisions recorded in the spec without a question: drawer routes (`gallery`, `album`, `flow-album`, `timeline`, `search`), close discards, drawer survives navigation between gallery routes (FR-110-13 … 15); Settings › Gallery link (Q-110-02 A text).

## Exit Criteria
- All tasks `[x]`; quality gate green: `vendor/bin/php-cs-fixer fix`, `vp check --fix` (changed files), `vp run typecheck`, `php artisan test --filter=AlbumThumbMinWidthConfigTest` (+ `PanZoomConfigTest`, `DateFormatsInitTest` as related Init tests), Vitest for the new/changed test files, `make phpstan`.
- Browser evidence for S-110-04 … 21 and NFR-110-02.
- Roadmap, knowledge map, ADR index updated; drift gate recorded.

## Follow-ups / Backlog
- `medium` through the v3 asset API (existing row) would sharpen tiles above 360 CSS px on 2× screens.

## Intent Log
- 2026-11-07 (after commit `3937890c6`): owner removed the "Preview the gallery sizes" link from Settings › Gallery: `GallerySizesSettingsLink.vue` deleted, its uses in `Settings.vue` and `AllSettings.vue` removed, `settings_link` / `settings_link_details` removed from the 23 `gallery_sizes.php`. T-110-22.
- 2026-11-07 (later): owner: drawer sections only when the page has those tiles (`liveGallerySections`), double-click on a label resets that setting (`resetKey`), T-110-21.
- 2026-11-07 (later): owner UI notes: drawer sections as plain headings (no fieldsets, `GallerySizesControls` `plain` prop); inactive layout rows muted instead of the active one in primary. Browser screenshot `shots/15-drawer-plain.png`.
- 2026-11-07 (later): owner asked to merge the two config migrations into `2026_11_07_000001_add_album_thumb_configs.php` and to drop ", v8 only" from the setting descriptions (23 locales and the migration). Old migration records renamed/removed in the test and scratch DBs (script in scratchpad `merge_migrations.php`, guarded on the expected state); the dev DB was left untouched because its state differed (gap row and record absent).
- 2026-11-07 (later): owner asked for an album gap setting; Non-Goal withdrawn, Q-110-05 A (one value, scaled ½ / ¼ below 768 / 640 px, default 16), I11.
- 2026-11-07: owner request (configurable v8 album thumb size + preview page for 5 photo settings), mid-turn additions (aspect ratio in preview; reactive random placeholders with configurable counts). Decisions: Q-110-01 A, Q-110-02 A + C (Spotlight only), Q-110-03 A, Q-110-04 A then B. Analysis numbers: scratchpad `tiles.mjs`, `sweep.mjs`.
