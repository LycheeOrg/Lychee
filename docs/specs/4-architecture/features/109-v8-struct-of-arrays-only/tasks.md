# Feature 109 Tasks – v8 Struct-of-Arrays Only

_Status: Complete (owner commits)_
_Last updated: 2026-11-07_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-109-01 – Rewrite the SoA gating tests against `features.v8` (F-109-03, S-109-04, S-109-05).
  _Intent:_ in the 33 test files other than `StructOfArrayInitTest`, drop `features.struct-of-array => true` lines (setUp/tearDown/inline) and turn every `=> false` into `features.v8 => false`, same assertions. Confirm red on a sample (success cases 403 while the requests still read the SoA flag).
  _Verification commands:_
  - `php artisan test --filter=AlbumRootV3Test` (red expected)
  _Notes:_ empty `setUp()`/`tearDown()` left behind are removed.

- [x] T-109-02 – Gate on `features.v8`, remove the SoA flag and the init field (F-109-03, F-109-05, S-109-03, S-109-08).
  _Intent:_ 19 FormRequests + `ModulesRightsResource::canQuickNameFaces()` read `config('features.v8') === true`; `struct-of-array` leaves `config/features.php`; `InitConfig::$is_struct_of_array_enabled` removed; `LycheeState.load()` no longer writes it; `app-v8.ts` sets it `true` after `app.use(pinia)`; `php artisan typescript:transform`.
  _Verification commands:_
  - `php artisan test --filter=<Class>` for each of the 33 classes, one at a time
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`
  - `vp run typecheck`

- [x] T-109-03 – Remove `nuxt_ui` (F-109-04, S-109-06, S-109-07).
  _Intent:_ tests first: `AdminSetupTest`, `ToAdminSetterTest` toggle `features.v8`; `MetaTest` covers the SVG icon on `features.v8` (red). Then `vueapp.blade.php`, `components/meta.blade.php`, `ToAdminSetter` read `Features::active('v8')`; `nuxt_ui` leaves `config/features.php`; `v8` docblock, `app-v8.ts` header and `docs/specs/2-how-to/customize-v8-nuxt-ui.md` updated.
  _Verification commands:_
  - `php artisan test --filter=AdminSetupTest`
  - `php artisan test --filter=ToAdminSetterTest`
  - `php artisan test --filter=MetaTest`
  - `php artisan test --filter=UpTest`
  - `vendor/bin/php-cs-fixer fix`, `make phpstan`

- [x] T-109-04 – Guard test for v8 SoA-only sources (F-109-02).
  _Intent:_ `resources/js/v8/soaOnly.test.ts` fails on any v8 read of `is_struct_of_array_enabled`, `isTimelineSoaActive`, `isSearchSoaActive`, `isMapSoaActive`, `isFlowSoaActive` and names the files. Red until T-109-07.
  _Verification commands:_
  - `vp test run resources/js/v8/soaOnly.test.ts` (red expected)

- [x] T-109-05 – Albums, album panel and small readers on v3 only (F-109-01, F-109-02, F-109-06, S-109-01, S-109-02).
  _Intent:_ `Albums.vue`, `AlbumPanel.vue` (flag reads; `isPhotoSoaActive` stays), `albumTileWidth.ts`, `SearchTargetAlbum.vue`, `LiveMetrics.vue`, v8 `Maintenance.vue`.
  _Verification commands:_
  - `vp run typecheck`
  - `vp check <changed files>`
  - `vp test run resources/js/v8/soaOnly.test.ts` (fewer offenders)

- [x] T-109-06 – Timeline and Flow on v3 only (F-109-01, F-109-02, S-109-01).
  _Intent:_ `Timeline.vue` (v2 dates/pages path), `Flow.vue` (v2 `load()` path).
  _Verification commands:_ as T-109-05.

- [x] T-109-07 – Search and Map on v3 only (F-109-01, F-109-02, S-109-01).
  _Intent:_ `Search.vue`, `ResultPanel.vue`, `Map.vue`. Guard test green.
  _Verification commands:_ as T-109-05; guard test green.

- [x] T-109-08 – List v8 files orphaned by T-109-05 … 07 (F-109-02, Q-109-04).
  _Intent:_ for every v8 component/composable/util imported only from removed branches, search its basename across `resources/js`; record the list below for owner approval.
  _Verification commands:_
  - `grep -rn <basename> resources/js` per candidate

- [x] T-109-09 – Delete approved paths (F-109-02, Q-109-04).
  _Intent:_ after T-109-14/15 and owner approval: `tests/Feature_v2/StructOfArrayInitTest.php` and the T-109-08 list.
  _Verification commands:_
  - `vp run typecheck`
  - `vp run build`

- [x] T-109-10 – Knowledge map (F-109-02, F-109-03).
  _Intent:_ SoA entries describe v8 as v3-only and the gate as `features.v8`.

- [x] T-109-11 – Roadmap, backlog, release note (N-109-03).
  _Intent:_ roadmap row #109 progress; backlog rows for the N-109-03 listings; release-note text for the flag change in plan.md (ADR-109-01 Operational Impact; release notes live outside this repo).

- [x] T-109-13 – Flow card header tests (F-109-07, S-109-09, S-109-10, S-109-11).
  _Intent:_ `AlbumCardV3.test.ts`: fit (blurred copy + contained photo), cover (one cropped photo), "show more" (no fixed header height). Red against today's card.
  _Verification commands:_
  - `vp test run resources/js/v8/components/gallery/flowModule/AlbumCardV3.test.ts` (red expected)

- [x] T-109-14 – Flow card header parity (F-109-07, S-109-09, S-109-10, S-109-11).
  _Intent:_ `AlbumCardV3.vue` renders `fit` and the "show more" natural height like v2 `HeaderImage.vue`; header variant per Q-109-06.
  _Status:_ done; header `small2x` per Q-109-06 A (test asserted the size first, red, then green).
  _Verification commands:_
  - `vp test run resources/js/v8/components/gallery/flowModule/AlbumCardV3.test.ts`
  - `vp run typecheck`, `vp check <files>`

- [x] T-109-15 – Map fits its content on open (F-109-08, S-109-12, S-109-13).
  _Intent:_ tests first for `resources/js/v8/utils/mapContentBounds.ts` (padding, single point, empty, response extraction); then the helper and `Map.vue` (album points, first world fetch, tracks fallback, fit once).
  _Verification commands:_
  - `vp test run resources/js/v8/utils/mapContentBounds.test.ts`
  - `vp run typecheck`, `vp check <files>`

- [x] T-109-16 – Flow top-images grid tests (F-109-09, S-109-14, S-109-15, S-109-16).
  _Intent:_ `AlbumCardV3.test.ts`: header off with 8 photos (5 thumbs, "+N", wrapped rows, no carousel), with 3 photos (one full-height row, no "+N"), click emits the photo id, skeleton while loading. Red against today's card.
  _Verification commands:_
  - `vp test run resources/js/v8/components/gallery/flowModule/AlbumCardV3.test.ts` (red expected)

- [x] T-109-17 – Flow top-images grid (F-109-09, S-109-14, S-109-15, S-109-16).
  _Intent:_ `AlbumCardV3.vue` grid as v2 `TopImages.vue`; skeleton not gated on the image header.
  _Verification commands:_
  - `vp test run resources/js/v8/components/gallery/flowModule/AlbumCardV3.test.ts`
  - `vp run typecheck`, `vp check <files>`
  - browser check (scratch instance, `flow_image_header_enabled = 0`)

- [x] T-109-12 – Browser checks and drift gate (S-109-01, S-109-02, S-109-03).
  _Intent:_ scratch instance with only `V8_ENABLED=true` (v3 requests for root, album, timeline, search, map, flow; filtered album on v2) and with `V8_ENABLED=false` (v7, v2, no Recompute buckets); drift gate in plan.md.
  _Status:_ browser checks done 2026-11-07 (plan "Browser Evidence"; S-109-02 and S-109-11 not reachable in the browser, see there); drift gate recorded in plan.md.

## Notes / TODOs
- 2026-11-07, T-109-02 verification blocked by the environment: `storage/logs/daily-2026-11-07.log` was created at 10:30 by the web server as `www-data:www-data` 664; the test process (user `biv`, not in `www-data`) cannot append, so every response that logs (each 403 logs "Insufficient privileges", validation 422s too) becomes a 500 `NoWriteAccessOnLogsExceptions`. Loop over the 33 classes: 25 with failures; `PhotoUrlsV3Test` 9/9 and `MapListingV3Test` 9/9 failures are this log error. Owner made the file writable (`chown biv:www-data`); rerun of the 33 classes one at a time: all green (461 tests). Done meanwhile: phpstan, php-cs-fixer, typecheck, guard test, Vitest stores + v8 (13 files, 105 tests).
- `vp check --fix` repo-wide rewrites `EmbedCodeDialog.vue`; scope `vp check` to the changed files.
- Orphan list (T-109-08, 2026-11-07; found by searching each removed import's basename across `resources/js`, then their own imports): no importer left outside v7 and the list itself.
  - `resources/js/v8/components/gallery/flowModule/AlbumCard.vue` (v2 Flow card)
  - `resources/js/v8/components/gallery/flowModule/HeaderImage.vue` (only `AlbumCard.vue`)
  - `resources/js/v8/components/gallery/flowModule/TopImages.vue` (only `AlbumCard.vue`)
  - `resources/js/v8/components/gallery/flowModule/CarouselImages.vue` (only `AlbumCard.vue`)
  - `resources/js/v8/components/gallery/timelineModule/TimelineDates.vue` (v2 timeline date list)
  - Kept: `Blur.vue` (`AlbumCardV3.vue`), `AlbumThumbPanel.vue`, `PhotoThumbPanel.vue`, `Pagination.vue` (smart/pinned panels, filtered album).
  - Plus `tests/Feature_v2/StructOfArrayInitTest.php` (Q-109-04).
