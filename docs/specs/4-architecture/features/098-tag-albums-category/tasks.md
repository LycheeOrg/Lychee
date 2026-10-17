# Feature 098 Tasks – Tag Albums Category

_Status: Complete_  
_Last updated: 2026-10-17_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist
- [x] T-098-01 – Failing test for the root config flags (FR-098-01, FR-098-02, S-098-01 … 04).  
  _Intent:_ `tests/Feature_v2/Album/RootConfigSeparateCategoriesTest.php`: both flags `false` by default, each `true` when its config is `1`.  
  _Verification commands:_ `php artisan test --filter=RootConfigSeparateCategoriesTest` (expected red).

- [x] T-098-02 – Migration, `RootConfig` fields, `all_settings.php` strings (FR-098-01, FR-098-02, NFR-098-02).  
  _Verification commands:_  
  - `php artisan test --filter=RootConfigSeparateCategoriesTest`  
  - `php artisan test --filter=AlbumRootConfigV3Test`  
  - `php artisan test --filter=LangTest`  
  - `vendor/bin/php-cs-fixer fix`, `make phpstan`

- [x] T-098-03 – Regenerate TypeScript types; `AlbumsState` getters (FR-098-03 … 05, NFR-098-01).  
  _Verification commands:_ `php artisan typescript:transform`, `vp run typecheck`

- [x] T-098-04 – v7 and v8 root panels, `is-alone`, `gallery.php` strings (FR-098-04 … 07).  
  _Verification commands:_ `vp check --fix`, `vp run typecheck`, `php artisan test --filter=LangTest`

- [x] T-098-05 – Browser check on a scratch instance (S-098-02 … 06, v7 and v8); knowledge map and roadmap.  
  _Verification commands:_ run-lychee scratch instance.

- [x] T-098-06 – PR #4895 review: virtual panels keep their header while Featured or shared albums are visible (FR-098-07).  
  _Intent:_ `isVirtualPanelAlone` computed in v7 and v8 `Albums.vue` (one non-empty virtual panel, no regular, pinned or shared albums) replaces the per-panel `!albums.length && virtualPanelCount === 1`, for the Smart, Tag and Person panels.  
  _Verification commands:_ `vp check --fix` (scoped), `vp run typecheck`, scratch browser check (built-in smart albums disabled, `deduplicate_pinned_albums` on, Family pinned: headers `Smart Albums | Featured Albums` in v8 and v7).

## Notes / TODOs
- `vp check --fix` run unscoped rewrites `<\/script>` in `EmbedCodeDialog.vue` (v7, v8) into `</script>`, which breaks the SFC; the run was reverted and checks were scoped to the touched files. Pre-existing, not part of this feature.
