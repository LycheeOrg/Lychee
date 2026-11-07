# Feature 110 Tasks – Gallery Size Configuration

_Status: Done_
_Last updated: 2026-11-07_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes.

## Checklist
- [x] T-110-01 – `AlbumThumbMinWidthConfigTest` red (FR-110-01, FR-110-02, S-110-01, S-110-02).
  _Intent:_ `tests/Feature_v3/Gallery/AlbumThumbMinWidthConfigTest.php`: default 175 in `Gallery::Init`, updated value, `0`/`abc` rejected, hidden without `features.v8`, listed with it. Confirm red.
  _Verification commands:_ `php artisan test --filter=AlbumThumbMinWidthConfigTest`
  _Result 2026-11-07:_ red, 3 failed (default, updated value, visible with v8); the 2 rejection tests and "hidden without v8" passed vacuously (unknown key) before the key existed.

- [x] T-110-02 – Setting, `V8_CONFIGS`, `InitConfig`, lang, store, types (FR-110-01, FR-110-02, NFR-110-05).
  _Intent:_ migration `2026_11_07_000001_add_album_thumb_configs.php` (`BaseConfigMigration`, `Gallery`, `positive`, 175, order after `number_albums_per_row_mobile`); `SettingsController::V8_CONFIGS`; `InitConfig::$album_thumb_min_width`; `all_settings.php` description ×23; `LycheeState.album_thumb_min_width` (175 default, loaded from Init); `lychee.d.ts` regenerated.
  _Verification commands:_ `php artisan test --filter=AlbumThumbMinWidthConfigTest`, `php artisan test --filter=PanZoomConfigTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`, `vp run typecheck`
  _Result 2026-11-07:_ AlbumThumbMinWidthConfigTest 6/6, PanZoomConfigTest 7/7, php-cs-fixer 0 fixes, phpstan no errors; `lychee.d.ts` one-line diff; migration applied on the dev DB (`php artisan migrate`, only pending one; row id 562 as specified). 23 locales translated (French file orders `details` before `documentation`; checked by section, not by position).

- [x] T-110-03 – Minimum-width tile rule (FR-110-01, FR-110-03, S-110-01, S-110-03, S-110-13, S-110-14).
  _Intent:_ `albumTileWidth.test.ts` red (rule, exact fit, stretch bound, album-count independence, below `sm`, Appendix A rows); `computeAlbumTileGeometry` takes `minWidth`; `TILE_WIDTH_FORMULA` removed; `useAlbumTileWidth` reads the store.
  _Verification commands:_ `vp test resources/js/v8/composables/album/albumTileWidth.test.ts`, `vp run typecheck`
  _Result 2026-11-07:_ red 13/14 → green 14/14; typecheck green. `computeAlbumTileGeometry(containerWidth, breakpoint, perRowMobile, minWidth)` (viewport width no longer an input); `computeAlbumContainerWidth` extracted for the preview.

- [x] T-110-04 – Cover variant by density (FR-110-12, S-110-15).
  _Intent:_ guard test red (no `type` on `AlbumThumbVirtual.vue` cover `Thumb`s); drop the 3 `type="small"`.
  _Verification commands:_ `vp test resources/js/v8/components/gallery/albumModule/Virtualized/AlbumThumbVirtual.test.ts`, `vp run typecheck`
  _Result 2026-11-07:_ source guard red (3 pinned tags) → green 2/2.

- [x] T-110-05 – Pure helpers (DO-110-02, DO-110-03, FR-110-05, FR-110-07, FR-110-09, NFR-110-01, NFR-110-03, S-110-13, S-110-14).
  _Intent:_ tests red, then `v8/utils/gallerySizes.ts`, `v8/utils/galleryPreviewPlaceholders.ts`, `v8/utils/galleryPreviewGeometry.ts`.
  _Verification commands:_ `vp test resources/js/v8/utils/gallerySizes.test.ts resources/js/v8/utils/galleryPreviewPlaceholders.test.ts resources/js/v8/utils/galleryPreviewGeometry.test.ts`
  _Result 2026-11-07:_ red (modules missing) → green 23/23 (one test expectation fixed: 1440 px is `xl`, not `2xl`). WASM primitives mocked in the geometry test (they do not run under jsdom); the test checks the arguments and equality with `computeBucketLayout`. Photo container width mirrors `PhotoGridVirtual.vue` (scrollbar counted twice): to confirm in the browser check (T-110-14).

- [x] T-110-06 – Draft composable (FR-110-10, FR-110-11, S-110-09 … S-110-11).
  _Intent:_ `useGallerySizesDraft.test.ts` red (mocked `SettingsService`), then `v8/composables/gallerySizes/useGallerySizesDraft.ts`.
  _Verification commands:_ `vp test resources/js/v8/composables/gallerySizes/useGallerySizesDraft.test.ts`
  _Result 2026-11-07:_ red (module missing) → green 7/7. Save also updates the open album's config when it has no own aspect ratio (FR-110-11 amended). `LangTest`, `ConfigTranslationKeysTest` green after the `all_settings.php` change.

- [x] T-110-07 – Controls component (FR-110-05, FR-110-08, NFR-110-06).
  _Intent:_ `v8/components/gallerySizes/GallerySizesControls.vue`: 8 controls with UI-110-05 ranges, layout switch, `layout-focus` emit; `showPlaceholders`/`showPresets` slots or props for page vs drawer.
  _Verification commands:_ `vp run typecheck`

- [x] T-110-08 – Preview component (FR-110-06, FR-110-07, FR-110-09, NFR-110-02, NFR-110-06).
  _Intent:_ `v8/components/gallerySizes/GallerySizesPreview.vue`: placeholders through `galleryPreviewGeometry`, presets, scale, text summary.
  _Verification commands:_ `vp run typecheck`

- [x] T-110-09 – Page, routes, tile (FR-110-04, API-110-03, S-110-04 … S-110-08, S-110-12).
  _Intent:_ `router/paths.ts`, `routes/web_v2.php`, `v8/router/routes.ts`, `v8/views/admin/GallerySizes.vue`, `useAdminTiles.ts` tile.
  _Verification commands:_ `vp run typecheck`, `vendor/bin/php-cs-fixer fix`

- [x] T-110-10 – Settings link and page strings (FR-110-04, NFR-110-05).
  _Intent:_ link at the top of Settings › Gallery; `lang/*/gallery_sizes.php` ×23.
  _Verification commands:_ `vp run typecheck`, `vendor/bin/php-cs-fixer fix`, `php artisan test --filter=LangTest`
  _Result 2026-11-07 (T-110-07 … 10):_ typecheck green, php-cs-fixer 0 fixes, LangTest 2/2; 23 locales generated with identical keys and placeholders (scratchpad `lang_gallery_sizes.py`, parity checked by a PHP one-liner). Layout names reuse each locale's existing `gallery.layout.*` terms. Rendering not yet checked in a browser (T-110-14).

- [x] T-110-11 – Live override and consumers (DO-110-04, FR-110-14, NFR-110-01, S-110-17, S-110-20).
  _Intent:_ `gallerySizesLive.test.ts` red; `useGallerySizesLiveStore`; `useAlbumTileWidth` reads it; `useAlbumThumbAspectRatio()` and `usePhotoLayoutConfig()` used by the album tiles, grids, `PhotoGridVirtual.vue` and `dragAndSelect.ts`.
  _Verification commands:_ `vp test resources/js/v8/composables/gallerySizes/gallerySizesLive.test.ts`, `vp test resources/js/v8`, `vp run typecheck`

- [x] T-110-12 – Live drawer (FR-110-14, FR-110-15, S-110-16 … S-110-21).
  _Intent:_ `is_gallery_sizes_live_open` in `ModalsState`; `v8/components/drawers/GallerySizesLive.vue` (non-modal USlideover, controls + draft + override, restore layout on close, close when leaving gallery routes); mounted in `v8/views/App.vue`.
  _Verification commands:_ `vp run typecheck`

- [x] T-110-13 – Spotlight action (FR-110-13, S-110-12, S-110-16).
  _Intent:_ gating test red (admin + gallery routes only), then the action in `useSpotlightGalleryActions.ts`.
  _Verification commands:_ `vp test resources/js/v8/composables/gallerySizes`, `vp run typecheck`
  _Result 2026-11-07 (T-110-11 … 13):_ `gallerySizesLive.test.ts` red → green (18 incl. the pure gate `canOfferLiveGallerySizes`, 11 cases red first); draft test +1 (`resetToSession`, found while writing the drawer: the composable is created at app start, so opening must re-read the session values and apply the override only after the settings load, or tiles could jump). `PhotoGridVirtual.vue` and `dragAndSelect.ts` now share `photoTargetAndGap`. Vitest v8 + stores + composables 27 files / 188 tests before the drawer, typecheck green after it. Drawer behaviour not yet checked in a browser (T-110-14).

- [x] T-110-14 – Quality gate and browser checks (NFR-110-02, NFR-110-06, NFR-110-07, S-110-01 … S-110-21).
  _Intent:_ full gate; scratch instance: page, presets, Save/Revert, drawer live on a large album, drag selection, 2× cover requests, timing, keyboard.
  _Verification commands:_ `vendor/bin/php-cs-fixer fix`, `vp check --fix <changed files>`, `vp run typecheck`, `php artisan test --filter=AlbumThumbMinWidthConfigTest`, `vp test resources/js/v8 resources/js/stores`, `make phpstan`

- [x] T-110-15 – Docs and drift gate.
  _Intent:_ knowledge map, roadmap, drift gate in plan, commit command for the owner.
  _Result 2026-11-07 (T-110-14, 15):_ gate and browser evidence in plan "Implementation Drift Gate" (scratch instance on port 8138, scratchpad `pw/check-110.cjs`, `pw/check-110b.cjs`, `pw/covers.cjs`, screenshots `shots/`). Browser checks found the Settings link missing on the Gallery category tab (only the "all" view had it): fixed with `GallerySizesSettingsLink.vue` in both. Knowledge map section added; roadmap Testing.

- [x] T-110-16 – `AlbumThumbGapConfigTest` red (FR-110-16, S-110-22).
  _Intent:_ `tests/Feature_v3/Gallery/AlbumThumbGapConfigTest.php`: default 16 in `Gallery::Init`, 0 and 30 accepted, `-1`/`abc` rejected, hidden without `features.v8`, listed with it.
  _Verification commands:_ `php artisan test --filter=AlbumThumbGapConfigTest`

- [x] T-110-17 – Gap setting, `InitConfig`, lang, store, types (FR-110-16, NFR-110-05).
  _Intent:_ migration `2026_11_07_000001_add_album_thumb_configs.php`; `V8_CONFIGS`; `InitConfig::$album_thumb_gap`; `all_settings.php` ×23; `LycheeState.album_thumb_gap` (16); `lychee.d.ts`.
  _Verification commands:_ `php artisan test --filter=AlbumThumbGapConfigTest`, `php artisan test --filter=LangTest`, `php artisan test --filter=ConfigTranslationKeysTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`

- [x] T-110-18 – Gap rule in the album geometry (FR-110-16, S-110-22, S-110-23).
  _Intent:_ `albumTileWidth.test.ts` gap cases red; `albumGapForBreakpoint()` replaces `GAP_PX`; `computeAlbumTileGeometry(containerWidth, breakpoint, settings)`; `useEffectiveAlbumTileSettings` gains `gap`; `AlbumThumbPanel.vue` flex gaps from `useAlbumTileWidth().gap`.
  _Verification commands:_ `vp test resources/js/v8/composables/album/albumTileWidth.test.ts`, `vp run typecheck`

- [x] T-110-19 – Gap on the page and in the drawer (FR-110-05, FR-110-11, FR-110-14, S-110-24).
  _Intent:_ tests red (sizes parse 0, draft applies gap, live override gap, preview geometry gap) → `GallerySizes.album_thumb_gap`, controls row (Albums), draft session values and store update.
  _Verification commands:_ `vp test resources/js/v8`, `vp run typecheck`
  _Result 2026-11-07 (T-110-16 … 19):_ `AlbumThumbGapConfigTest` red 4/7 (3 vacuous before the key existed) → 7/7; `AlbumThumbMinWidthConfigTest` 6, `LangTest` 2, `ConfigTranslationKeysTest` 8; migration applied on the dev DB (only pending one); `lychee.d.ts` +1 line; php-cs-fixer 0 fixes, phpstan no errors. `albumTileWidth.test.ts` red 7 → 19 green (`computeAlbumTileGeometry` now takes a settings object `{ minWidth, perRowMobile, gap }`); helper/draft/live/geometry tests red 8 → green; `AlbumThumbPanel.vue` gaps from `useAlbumTileWidth().gap`. Typecheck caught one test still on the old 4-argument call (passing by default fallback): fixed. Vitest `resources/js` 32 files / 238 tests, typecheck green, scoped `vp check` clean.

- [x] T-110-20 – Gate, browser check, docs (NFR-110-07, S-110-22 … 24).
  _Intent:_ full gate; scratch instance: default gaps 4/8/16, 30 and 0 at 1440, live drawer gap with the smart-album panel; knowledge map, roadmap, drift gate addendum, commit command.
  _Result 2026-11-07:_ scratch DB migrated, `vp run build`; `pw/check-110c.cjs` and `pw/gridgap.cjs` (results in plan drift gate addendum). Scratch server stopped.

- [x] T-110-21 – Owner follow-ups: merged migration, no "v8 only", plain drawer, muted rows, sections by page, double-click reset (FR-110-05, FR-110-08, FR-110-14, S-110-25, S-110-26).
  _Intent:_ `2026_11_07_000001_add_album_thumb_configs.php` replaces the two migrations; descriptions without ", v8 only" (23 locales); `GallerySizesControls` `plain`, `showAlbums`/`showPhotos`, `reset` event, muted inactive rows; `liveGallerySections`/`useLiveGallerySections`; `useGallerySizesDraft.resetKey`; `reset_hint`, `live_empty` ×23.
  _Verification commands:_ `vp test resources/js`, `vp run typecheck`, `vp check --fix <files>`, `php artisan test --filter=LangTest`, `php artisan test --filter=AlbumThumbGapConfigTest`, `php artisan test --filter=AlbumThumbMinWidthConfigTest`, `php artisan test --filter=ConfigTranslationKeysTest`
  _Result 2026-11-07:_ red first (`liveGallerySections` 6, `GallerySizesControls.test.ts` 8 after a ResizeObserver stub for jsdom, `resetKey` 1) → green; Vitest 33 files / 255 tests, typecheck, lint, server tests green. Browser (`pw/check-110d.cjs`): double-click 300 → 175 and marker cleared, masonry reset shows Masonry; drawer Albums only on root, Photos only in "Big" and on the timeline; live double-click 448 → 183 px.

- [x] T-110-22 – Remove the Settings › Gallery link (FR-110-04, S-110-12).
  _Intent:_ owner request after commit `3937890c6`: delete `GallerySizesSettingsLink.vue`, drop it from `Settings.vue` and `AllSettings.vue`, remove `settings_link` and `settings_link_details` from the 23 `gallery_sizes.php`.
  _Verification commands:_ `vp check --fix` (2 files), `vp run typecheck`, `vp test resources/js`, `php artisan test --filter=LangTest`, `vp run build`
  _Result 2026-11-07:_ all green (Vitest 33 files / 255 tests, LangTest 2); `public/build` no longer contains the strings. The git-ignored `lang/php_*.json` still hold them: the i18n plugin restores those files' previous content after each build; `vp dev` regenerates them.

## Requirement coverage

| Requirement | Tasks |
|-------------|-------|
| FR-110-01 | T-110-01, T-110-02, T-110-03 |
| FR-110-02 | T-110-01, T-110-02 |
| FR-110-03 | T-110-03 |
| FR-110-04 | T-110-09, T-110-10 |
| FR-110-05 | T-110-05, T-110-07 |
| FR-110-06 | T-110-08 |
| FR-110-07 | T-110-05, T-110-08 |
| FR-110-08 | T-110-07 |
| FR-110-09 | T-110-05, T-110-08 |
| FR-110-10, FR-110-11 | T-110-06 |
| FR-110-12 | T-110-04 |
| FR-110-13 | T-110-13 |
| FR-110-14 | T-110-11, T-110-12 |
| FR-110-15 | T-110-12 |
| FR-110-16 | T-110-16 … 20 |
| owner follow-ups | T-110-21, T-110-22 |
| NFR-110-01 | T-110-05, T-110-11 |
| NFR-110-02, 06, 07 | T-110-14 |
| NFR-110-03 | T-110-05 |
| NFR-110-04 | all (no dependency) |
| NFR-110-05 | T-110-02, T-110-10 |
| NFR-110-08 | T-110-04 |

## Notes / TODOs
- Run `vp check --fix` on changed files only (repo-wide run rewrites `EmbedCodeDialog.vue`).
- Never run two test commands at once (shared SQLite).
