# Feature Plan 088 – Frame Overlays

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Complete  
_Last updated:_ 2026-10-13

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A Frame running on a wall tablet or a TV can show the time, the date and what the photo is (title, albums, EXIF), styled and placed per device. Success: every FR-088 scenario covered by a feature test or a browser check; the default settings leave the Frame unchanged; no data the viewer could not already see (NFR-088-03); quality gate green.

## Scope Alignment
- **In scope:** 18 settings and two enums (FR-088-13, DO-088-05/06); `FrameData` with `clock`, `info`, `photo` (FR-088-10); album line with browsability filter (FR-088-06); sub-album mode and the album Frame button (FR-088-16/17); v8 Frame overlays with URL overrides (FR-088-01 … FR-088-05, FR-088-07 … FR-088-12, FR-088-15); translations of the setting texts.
- **Out of scope:** the v7 Frame view; text colour or font settings; a settings panel in the Frame; other photo data (spec Non-Goals).

## Dependencies & Interfaces
- `App\Http\Controllers\Gallery\FrameController`, `App\Http\Requests\Frame\FrameRequest`, `App\Http\Resources\Frame\FrameData`.
- `App\Http\Resources\GalleryConfigs\AlbumConfig` (Frame button rule).
- `Album::all_photos()` (`HasManyPhotosRecursively`), `AlbumQueryPolicy::applyBrowsabilityFilter()`.
- `Configs::sanity()` validation of `0|1`, enum strings, `color`, `int:0:100`; `ConfigGroup.vue` renders them.
- v8 `Frame.vue`, `GoBack.vue`, `useSlideshowFunction` (shutter), `laravel-vue-i18n` `getActiveLanguage()`.
- `php artisan typescript:transform` (regenerates `resources/js/lychee.d.ts`), `php artisan lang:json`.

## Assumptions & Risks
- **Assumptions:** the `BaseApiWithDataTest` fixtures provide `album1` with `subAlbum1`, photos in each, and a public `album4`; Chromium for Playwright is available on the machine (used by Features 086 and 087).
- **Risks / Mitigations:**
  - `HasManyPhotosRecursively::addConstraints()` applies the album's photo sorting, so `inRandomOrder()` alone would always pick the same photo. Mitigation: `reorder()` before `inRandomOrder()`; a test draws from a tree where only a sub-album has photos.
  - `Configs::set()` is not rolled back by the test transaction. Mitigation: every test class restores the configs it changes in `tearDown()`.
  - A `//` comment between a Spatie Data class's `{` and its constructor docblock makes `typescript:transform` emit `Array<any>`. Mitigation: no such comments; check the generated types.
  - `HasManyChildPhotos` filters by viewer visibility, so photo fixtures must be visible to the acting user.

## Implementation Drift Gate
Run after T-088-14: map every FR/NFR to code and tests in a table appended below, rerun the quality gate (php-cs-fixer, prettier, `npm run check`, every test class listed in tasks, `make phpstan`), and record the Playwright evidence.

### Drift Gate Report – 2026-10-13

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-088-01, NFR-088-05 | `FrameClockBox.vue` (`tick()` re-armed for the next minute boundary, `hourCycle`), `frameSlideshow.ts` (clears only its own timeouts) | Playwright: `14:32` → `14:33` → `14:34` on the mocked clock, still `14:39` after a photo refresh; timer cleared in `onUnmounted`. |
| FR-088-02 | `FrameClockBox.vue::locale()`, `Intl.DateTimeFormat` | Playwright (`en-US`): `Tuesday, October 13, 2026`, time above date. |
| FR-088-03, FR-088-08 | `FrameBoxPosition`, migration `type_range`; `FrameOverlays.vue` (`CORNER_CLASSES`, center slot) | `FrameOverlaySettingsTest` (S-088-06); Playwright: center ±2 px, corners measured. |
| FR-088-04, S-088-20 | `frameOverlay.ts::boxBackground()`, `:style` opacity | Playwright: `rgba(255, 0, 0, 0.25)` / `0.6`, `rgba(0, 255, 0, 0)` / `0.3`. |
| FR-088-05, FR-088-07, FR-088-10 | `FramePhotoData`, `FrameController::get` | `FrameOverlayTest` (S-088-07 … 09), `FramePhotoDataTest` (S-088-09, 10); Playwright: five info lines, missing values left out. |
| FR-088-06, NFR-088-06 | `ListBrowsableAlbumTitles`, `FrameRequest::offersAlbumLine()` | `FrameOverlayTest` (S-088-11 … 13). |
| FR-088-09 | `frameOverlay.ts::infoLines()`, `FrameInfoBox.vue` `v-if` | Playwright: no info box for an all-null photo and for `photo = null`. |
| FR-088-11 | `FrameOverlays.vue` (clock `z-30`, info `z-15`, shutter `z-20`) | Playwright, curtain down: hit test on the clock box returns the box, on the info box the shutter. |
| FR-088-12, S-088-19 | `boxesByCorner()`, `ltr:pt-16` / `rtl:pt-16` | Playwright: both boxes in the top-left column, clock first, below the Back button (57 px → 64 px); Arabic install: Back button top right, top-right column below it, top-left column at the top. |
| FR-088-13, FR-088-14 | migration `2026_10_13_000000_add_frame_overlay_configs`, `lang/*/all_settings.php` | `FrameOverlaySettingsTest`, `LangTest`, `FrameOverlayTest` (S-088-01); Playwright: default values render no box. |
| FR-088-15, S-088-17, S-088-18 | `frameOverlay.ts::resolveClock()`, `resolveInfo()`, `parse*` helpers | Playwright: URL override shown on that page only; `middle`, `center`, `150`, `-5`, `red`, `yes` ignored, admin position, colour and opacities kept. |
| FR-088-16, FR-088-17 | `FrameController::loadPhoto()`, `AlbumConfig` | `FrameSubAlbumsTest` (S-088-14, 15), `FrameTest`, `AlbumConfig` filter (20 tests). |
| NFR-088-01, NFR-088-02 | — | No external host; `composer.json`, `package.json` unchanged. |
| NFR-088-03 | `FramePhotoData`, `ListBrowsableAlbumTitles` | `FrameOverlayTest` (S-088-08, 09, 12). |
| NFR-088-04 | text shadow on both boxes | Playwright screenshots, background opacity 0, on a near-white and a near-black photo. |

Verification commands: `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `npx eslint resources/js/v8/components/frame resources/js/v8/utils/frameOverlay.ts resources/js/v8/views/gallery-panels/Frame.vue resources/js/v8/composables/frame`, `php artisan test --filter=` `FrameOverlaySettingsTest` (14), `FrameOverlayTest` (7), `FramePhotoDataTest` (4), `FrameSubAlbumsTest` (4), `FrameTest` (4), `LangTest` (2), `AlbumConfig` (20), all green; `make phpstan` no errors; `npx vite build`, `node <scratchpad>/frame-overlays.mjs <scratchpad>/shots` 35/35 checks green.

Scratch instance: SQLite database in the session scratchpad, migrated with `DB_DATABASE` set to it, `CACHE_DRIVER=array`, `NUXT_UI_ENABLED=true`, admin row inserted directly, PHP built-in server with `variables_order=EGPCS`. Playwright with the system Chromium mocks `GET /api/v2/Frame`, so each scenario sets the admin values and photo data, and installs a fake clock at 14:32:50 Europe/Paris. The RTL case switches the instance's `lang` to `ar`: the `ltr:` variant keys on `:lang()`, so setting `dir` alone in the page is not faithful.

Low-level adjustment: the `0 1px 3px` text shadow left white text faint on a near-white photo at background opacity 0 (NFR-088-04). Both boxes now use a doubled 2 px black halo with 8 px and 24 px glows. No divergence from the spec.

## Increment Map
1. **I1 – Settings and enums** (T-088-01 … T-088-03)
   - _Goal:_ the 18 settings exist, validate, and have texts in every locale.
   - _Steps:_ test S-088-06 (save rejects `center` for the info box, an unknown clock position, an opacity of 150); enums `FrameBoxPosition`, `FrameTimeFormat`; migration `2026_10_13_000000_add_frame_overlay_configs` (BaseConfigMigration, `required_keys = mod_frame_enabled`, orders 3–20); `all_settings.php` texts in 23 locales; `php artisan lang:json`.
   - _Commands:_ `php artisan test --filter=FrameOverlaySettingsTest`, `php artisan test --filter=LangTest`, `make phpstan`.
2. **I2 – Frame response: settings and photo information** (T-088-04 … T-088-06)
   - _Goal:_ `GET /Frame` returns `clock`, `info`, `photo` (FR-088-05, FR-088-07, FR-088-10).
   - _Steps:_ tests S-088-01, S-088-02, S-088-07 … S-088-10 in `FrameOverlayTest`, unit `FramePhotoDataTest`; Data classes `FrameClockData`, `FrameInfoData`, `FramePhotoData` (straight-line builders reading configs once); `FrameController::get` composes them.
   - _Commands:_ `php artisan test --filter=FrameOverlayTest`, `--filter=FramePhotoDataTest`, `--filter=FrameTest`, `make phpstan`.
3. **I3 – Album line** (T-088-07, T-088-08)
   - _Goal:_ `photo.albums` per FR-088-06.
   - _Steps:_ tests S-088-11 … S-088-13; action `App\Actions\Frame\ListBrowsableAlbumTitles` (one query: photo's album ids, browsability filter, `title_base`/`title_index` order); `FrameRequest` exposes whether the frame offers the line.
   - _Commands:_ `php artisan test --filter=FrameOverlayTest`, `make phpstan`.
4. **I4 – Sub-album mode** (T-088-09, T-088-10)
   - _Goal:_ FR-088-16, FR-088-17.
   - _Steps:_ tests S-088-14, S-088-15 in `FrameSubAlbumsTest`; `FrameController::loadPhoto()` uses `all_photos()->reorder()->inRandomOrder()` for regular albums in sub-album mode; `AlbumConfig` checks `all_photos()->exists()` in that mode.
   - _Commands:_ `php artisan test --filter=FrameSubAlbumsTest`, `--filter=FrameTest`, `--filter=AlbumConfig`, `make phpstan`.
5. **I5 – v8 overlays** (T-088-11, T-088-12)
   - _Goal:_ FR-088-01 … FR-088-04, FR-088-08, FR-088-09, FR-088-11, FR-088-12, FR-088-15 in the v8 Frame.
   - _Steps:_ `php artisan typescript:transform`; pure helpers in `resources/js/v8/utils/frameOverlay.ts` (URL parsing per value type, merge over admin values, hex to rgba, corner grouping); `FrameClockBox.vue` (minute-aligned timer, `Intl.DateTimeFormat` with `hourCycle`), `FrameInfoBox.vue`, `FrameOverlays.vue` (corner columns, Back button offset, clock above the shutter, info under it); `Frame.vue` keeps the latest `clock`, `info`, `photo`.
   - _Commands:_ `npm run format`, `npm run check`, `npx eslint` on touched files.
6. **I6 – Browser check and documentation** (T-088-13, T-088-14)
   - _Goal:_ S-088-03 … S-088-05, S-088-16 … S-088-20 verified in Chromium; knowledge map, roadmap, drift gate.
   - _Steps:_ scratch instance (session scratchpad SQLite, `CACHE_STORE=array`, PHP server from `public/`, built assets), Playwright script with a mocked clock; drift gate report.
   - _Commands:_ `npm run build`, Playwright script, full quality gate.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-088-01 | I2 / T-088-04 | `FrameOverlayTest` |
| S-088-02 | I2 / T-088-04 | `FrameOverlayTest` |
| S-088-03 | I6 / T-088-13 | Playwright, mocked clock |
| S-088-04 | I6 / T-088-13 | Playwright |
| S-088-05 | I6 / T-088-13 | Playwright |
| S-088-06 | I1 / T-088-01 | `FrameOverlaySettingsTest` |
| S-088-07 | I2 / T-088-04 | `FrameOverlayTest` |
| S-088-08 | I2 / T-088-04 | `FrameOverlayTest` |
| S-088-09 | I2 / T-088-04, T-088-05 | `FrameOverlayTest`, `FramePhotoDataTest` |
| S-088-10 | I2 / T-088-05 | `FramePhotoDataTest` |
| S-088-11 | I3 / T-088-07 | `FrameOverlayTest` |
| S-088-12 | I3 / T-088-07 | `FrameOverlayTest` |
| S-088-13 | I3 / T-088-07 | `FrameOverlayTest` |
| S-088-14 | I4 / T-088-09 | `FrameSubAlbumsTest` |
| S-088-15 | I4 / T-088-09 | `FrameSubAlbumsTest` |
| S-088-16 | I6 / T-088-13 | Playwright |
| S-088-17 | I6 / T-088-13 | Playwright |
| S-088-18 | I6 / T-088-13 | Playwright |
| S-088-19 | I6 / T-088-13 | Playwright |
| S-088-20 | I6 / T-088-13 | Playwright |

## Analysis Gate
Completed 2026-10-13 (agent review).

1. Specification completeness: pass. FR-088-01 … 17 and NFR-088-01 … 06 populated; Q-088-01 … 05 folded into FR-088-06, 12, 13, 15, 16, 17 and the Overview; ASCII mock-ups for corner, stacked and center layouts.
2. Open questions: pass. No `Open` entry; ADR-088-01 records the settings source, URL overrides and privacy boundary.
3. Plan alignment: pass. Plan links spec and tasks; dependencies match the spec's affected layers.
4. Tasks coverage: pass. Every FR maps to a task (FR-088-01 … 04, 08, 09, 11, 12, 15 → T-088-11 … 13; FR-088-05, 07, 10 → T-088-04 … 06; FR-088-06 → T-088-07, 08; FR-088-13, 14 → T-088-01 … 03; FR-088-16, 17 → T-088-09, 10). Tests precede code in each increment; validation branches (S-088-06, S-088-18) and privacy branches (S-088-08, 09, 12) are queued.
5. Working agreements: pass. No new dependency; URL parsing, merging and photo-information building are small pure helpers; no optional request field (ADR-088-01); v8 only.
6. Tooling: pass. Commands listed per increment.

## Exit Criteria
- All tasks `[x]`; quality gate green (php-cs-fixer, `npm run format`, `npm run check`, every listed test class, `make phpstan`).
- `php artisan typescript:transform` and `php artisan lang:json` rerun after their sources changed.
- Roadmap, knowledge map, `_current-session.md` updated; drift gate report below.

## Follow-ups / Backlog
- `FrameController::get` calls `GET /Frame` twice on start (once for the timeout, once for the first photo), as before this feature.
