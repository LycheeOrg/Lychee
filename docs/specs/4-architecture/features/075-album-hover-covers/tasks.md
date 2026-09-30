# Feature 075 Tasks – Album Hover Side Covers

_Status: Implemented 2026-09-30 — T-075-22 (manual browser check) and T-075-24 (commit hand-off) open_  
_Last updated: 2026-09-30_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Schema and setting
- [x] T-075-01 – Failing test: setting change flushes the album-listing cache (F-075-04, S-075-18).  
  _Intent:_ Create `tests/Feature_v3/Album/AlbumSideCoversV3Test` (extends `BaseApiWithDataTest`) with `testSettingChangeFlushesListingCache` using `Event::fake([AlbumListingCacheFlushRequested::class])` around a settings update of `album_hover_side_covers_enabled`. Red until T-075-03.  
  _Verification commands:_ `php artisan test --filter=AlbumSideCoversV3Test`
- [x] T-075-02 – Migrations: four `albums` side columns, two `album_user_thumbs` side columns (F-075-01, F-075-08).  
  _Intent:_ `2026_09_30_000001_add_side_covers_to_albums.php` (char(24) nullable ×4, FK `photos.id` nullOnDelete, `down()` drops FKs then columns) and `2026_09_30_000002_add_side_photos_to_album_user_thumbs.php` (`photo_id_2`, `photo_id_3`, FK nullOnDelete). Add the attributes to the `Album` / `AlbumUserThumb` docblocks, `$fillable`, and `$attributes` defaults where the existing pair has them.  
  _Verification commands:_ `php artisan test --filter=AlbumSideCoversV3Test` (migrations apply), `make phpstan`
- [x] T-075-03 – Config migration and settings lists (F-075-04, S-075-18).  
  _Intent:_ `2026_09_30_000003_add_album_hover_side_covers_config.php` via `BaseConfigMigration` (Gallery, bool, default `1`, level 0). Add the key to `SettingsController::V8_CONFIGS` and `ALBUM_LISTING_COARSE_FLUSH_CONFIGS`. T-075-01 goes green.  
  _Verification commands:_ `php artisan test --filter=AlbumSideCoversV3Test`, `make phpstan`

### I2 – Recompute job ranks 1–3
- [x] T-075-04 – Failing tests: job stores ranks 1–3 per privilege, no extra query (F-075-02, S-075-01, S-075-02, NFR-075-02).  
  _Intent:_ Extend `tests/Precomputing/CoverSelection/RecomputeAlbumStatsJobTest`: ≥3 photos → six columns filled in effective order with highlighted first; one photo → ranks 2–3 `NULL`; empty → all `NULL`; query-log count unchanged versus the pre-feature baseline recorded in the test.  
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumStatsJobTest`
- [x] T-075-05 – Failing test: least-privilege sides contain public photos only (NFR-075-03, S-075-03).  
  _Intent:_ Extend `tests/Precomputing/CoverSelection/Console/AlbumCoverSecurityTest` with a mixed public/private subtree asserting the least-privilege triple never contains the private id while the max-privilege triple may.  
  _Verification commands:_ `php artisan test --filter=AlbumCoverSecurityTest`
- [x] T-075-06 – Implement `getPhotoIdsForUser()` with `limit(3)` and assign six columns (F-075-02, F-075-03).  
  _Intent:_ Rename the private method, return `array<int,string>`, assign ranks 1–3 to the max and least columns (missing ranks → `null`), extend the debug log. `lychee:recompute-album-stats` needs no change.  
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumStatsJobTest`, `php artisan test --filter=AlbumCoverSecurityTest`, `php artisan test --filter=RecomputeAlbumStatsCommandTest`, `make phpstan`

### I3 – SideCoverIds helper
- [x] T-075-07 – Failing unit tests for `SideCoverIds` (F-075-05, F-075-09, S-075-05, S-075-06, S-075-07).  
  _Intent:_ `tests/Unit/Actions/SideCoverIdsTest` (extends `AbstractTestCase`): disabled → nulls; null primary → nulls; admin/owner picks max triple, other picks least; nulls dropped; primary dropped at rank 1, 2 and 3; locked album with neither / `show_cover_of_locked_albums` / only `show_selected_cover_on_locked_albums`; `fromCacheRow` with row, without row, disabled.  
  _Verification commands:_ `php artisan test --filter=SideCoverIdsTest`
- [x] T-075-08 – Implement `App\Actions\Album\StructOfArrays\SideCoverIds` (F-075-05, F-075-09).  
  _Intent:_ Two static pure methods, straight-line: pick triple → filter → slice → gate. License header, strict comparisons, `in_array(..., true)`.  
  _Verification commands:_ `php artisan test --filter=SideCoverIdsTest`, `make phpstan`

### I4 – Regular-album listings
- [x] T-075-09 – Failing v3 listing tests (F-075-06, F-075-07, S-075-04, S-075-05, S-075-06, S-075-07, S-075-16, NFR-075-01).  
  _Intent:_ Extend `AlbumSideCoversV3Test`: owner vs guest arrays on `/Albums/{id}/children` and `/Albums/root`; setting off → all-null arrays; manual cover cases; locked album cases; `/Albums/pinned` and `/Search/albums` carry the arrays with matching lengths; query count on children/root unchanged from baseline.  
  _Verification commands:_ `php artisan test --filter=AlbumSideCoversV3Test`
- [x] T-075-10 – Add the arrays to `AlbumDataResource` / `AlbumCategoryResource` and fill them in the regular-album producers (F-075-06, F-075-07).  
  _Intent:_ Select the four columns in `BuildAlbumDataResource`, `AlbumRootController`, `AlbumPinnedController`; extend the `TRow` phpstan types; call `SideCoverIds::forAlbumRow()` next to `resolveCoverId()`; smart/tag/person controllers pass all-null arrays until I6.  
  _Verification commands:_ `php artisan test --filter=AlbumSideCoversV3Test`, `php artisan test --filter=AlbumRootV3Test`, `php artisan test --filter=AlbumCategoryV3Test`, `php artisan test --filter=QuerySearchAlbumsTest`, `make phpstan`

### I5 – Asset authorization, album half
- [x] T-075-11 – Failing tests: side photo in a descendant is served, non-member still 403 (F-075-10, S-075-08, S-075-09).  
  _Intent:_ Extend `tests/Feature_v3/Photo/PhotoAssetV3Test`.  
  _Verification commands:_ `php artisan test --filter=PhotoAssetV3Test`
- [x] T-075-12 – Widen `isPhotoOfAlbum()` to the seven album cover columns (F-075-10).  
  _Verification commands:_ `php artisan test --filter=PhotoAssetV3Test`, `make phpstan`

### I6 – Cache store: three ids per viewer row
- [x] T-075-13 – Failing tests: cache job rewrites three ids, deletes the row when empty (F-075-08, S-075-15).  
  _Intent:_ Extend `tests/Unit/Jobs/RecomputeAlbumUserThumbsJobTest`.  
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumUserThumbsJobTest`
- [x] T-075-14 – Failing tests: lazy seeding writes three columns for tag, person and smart albums (F-075-08, S-075-10).  
  _Intent:_ Extend `tests/Unit/Models/TagAlbumTest` and `PersonAlbumTest`; smart-album seeding asserted in `AlbumCategoryV3Test` (T-075-16).  
  _Verification commands:_ `php artisan test --filter=TagAlbumTest`, `php artisan test --filter=PersonAlbumTest`
- [x] T-075-15 – Implement `Thumb::createManyFromQueryable()`, three-column seeding in `CachesAlbumUserThumb`, three-column refresh in `RecomputeAlbumUserThumbsJob` (F-075-08).  
  _Intent:_ One ordered query with `limit`, `withOnly` size variants as today; closure contract of `getCachedOrLiveThumb()` becomes "return the ordered collection"; `updateOrCreate` writes `photo_id`, `photo_id_2`, `photo_id_3`.  
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumUserThumbsJobTest`, `php artisan test --filter=TagAlbumTest`, `php artisan test --filter=PersonAlbumTest`, `make phpstan`
- [x] T-075-16 – Failing then green: smart/tag/person listings return cached primary and sides (F-075-09, S-075-10, S-075-11, S-075-12, NFR-075-01).  
  _Intent:_ Extend `tests/Feature_v3/Album/AlbumCategoryV3Test` (update the person `null`-cover assertion to "null before a row exists, cached id after"); implement the batched cache read in `AlbumSmartController`, `AlbumTagController`, `AlbumPersonController` and `SideCoverIds::fromCacheRow()` wiring, tag rows through the locked gate.  
  _Verification commands:_ `php artisan test --filter=AlbumCategoryV3Test`, `make phpstan`
- [x] T-075-17 – Asset authorization, cache half (F-075-10, S-075-10).  
  _Intent:_ `PhotoAssetV3Test`: rank-2 cached smart cover is served; `isComputedAlbumThumb()` matches three columns.  
  _Verification commands:_ `php artisan test --filter=PhotoAssetV3Test`, `make phpstan`
- [x] T-075-18 – Purge and FK behaviour for side columns (F-075-11, F-075-01, S-075-13, S-075-14).  
  _Intent:_ New `tests/Feature_v3/Sharing/PurgeSideCoversV3Test`: revoking public access on the source album deletes a row whose only match is `photo_id_2`; deleting a photo nulls the `albums` side column and the cache side column while the row survives. Implement the three-column match in `PurgeAlbumUserThumbs::forBaseAlbums()` and update its docblock register.  
  _Verification commands:_ `php artisan test --filter=PurgeSideCoversV3Test`, `php artisan test --filter=SharingTest`, `make phpstan`

### I7 – Frontend
- [x] T-075-19 – Regenerate TypeScript types (F-075-13).  
  _Verification commands:_ `php artisan typescript:transform`, `npm run check`
- [x] T-075-20 – `AdaptedAlbumTile` + adapters (F-075-12).  
  _Intent:_ Add `cover_id_2`, `cover_id_3` to the type; fill in `adaptAlbumChildTile.ts`, `adaptCategoryTile.ts` and the root store's tile assembly.  
  _Verification commands:_ `npm run format`, `npm run check`
- [x] T-075-21 – `AlbumThumbVirtual.vue` back layers use the side ids with cover fallback (F-075-12, N-075-06).  
  _Verification commands:_ `npm run format`, `npm run check`
- [ ] T-075-22 – Manual browser check of the fan (S-075-17, UI-075-01, UI-075-02).  
  _Intent:_ Album grid, root grid (regular, pinned, smart, tag, person tiles), search results; setting on and off. Record the result or "pending, no environment" in the plan.  
  _Verification commands:_ manual

### I8 – Docs and drift gate
- [x] T-075-23 – Knowledge map, roadmap, ADR status, drift gate record.  
  _Intent:_ Entries per spec Documentation Deliverables; run the Implementation Drift Gate and the NFR-075-05 empty-diff check; roadmap → Implemented.  
  _Verification commands:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, every test class above, `make phpstan`
- [ ] T-075-24 – Prepare the Conventional Commit for the operator (AGENTS.md commit protocol).  
  _Intent:_ Stage the paths, run `./scripts/codex-commit-review.sh`, hand over the `git commit` command with `Spec impact:` line, no semicolons, `timeout_ms >= 300000`.  
  _Verification commands:_ `git status --short`

## Notes / TODOs
- T-075-22 not performed this session: no browser environment. Type-check and the listing/asset tests cover everything but the visual fan itself.
- Never run two test commands concurrently (shared SQLite database).
- No new Feature_v2 tests; `SharingTest` is only rerun for the existing purge coverage.
