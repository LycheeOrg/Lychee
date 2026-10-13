# Feature 088 Tasks – Frame Overlays

_Status: In progress_  
_Last updated: 2026-10-13_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-088-01 – Failing test: settings validation (F-088-03, F-088-08, F-088-13, S-088-06).  
  _Intent:_ `tests/Feature_v3/Frame/FrameOverlaySettingsTest.php`: saving `mod_frame_info_position = center`, `mod_frame_clock_position = middle`, `mod_frame_clock_opacity = 150` or `mod_frame_info_background_color = red` through `POST /api/v2/Settings::setConfigs` is rejected; valid values are saved.  
  _Verification commands:_  
  - `php artisan test --filter=FrameOverlaySettingsTest` (red before T-088-02)

- [x] T-088-02 – Settings migration and enums (F-088-13, F-088-14).  
  _Intent:_ `app/Enum/FrameBoxPosition.php`, `app/Enum/FrameTimeFormat.php` (`#[TypeScript]`); migration `2026_10_13_000000_add_frame_overlay_configs.php` with the 18 settings of DO-088-05 (`Mod Frame`, `level = 0`, `required_keys = mod_frame_enabled`, orders 3–20).  
  _Verification commands:_  
  - `php artisan test --filter=FrameOverlaySettingsTest`  
  - `make phpstan`

- [x] T-088-03 – Setting texts in every locale (F-088-13).  
  _Intent:_ `documentation` and `details` entries for the 18 keys in `lang/*/all_settings.php` (English text in every locale), `php artisan lang:json`.  
  _Verification commands:_  
  - `php artisan test --filter=LangTest`

- [x] T-088-04 – Failing tests: Frame response (F-088-05, F-088-07, F-088-10, S-088-01, S-088-02, S-088-07, S-088-08, S-088-09).  
  _Intent:_ `tests/Feature_v3/Frame/FrameOverlayTest.php`: default `clock`/`info` values; changed admin values; title, EXIF and `taken_at` of a photo with EXIF; guest with `file_name_hidden`; `display_exif_data` off.  
  _Verification commands:_  
  - `php artisan test --filter=FrameOverlayTest` (red before T-088-06)

- [x] T-088-05 – Failing unit test: photo information builder (F-088-07, S-088-09, S-088-10).  
  _Intent:_ `tests/Unit/Http/Resources/Frame/FramePhotoDataTest.php`: missing lens and ISO give `null`; EXIF switch off keeps `taken_at`; empty title gives `null`.  
  _Verification commands:_  
  - `php artisan test --filter=FramePhotoDataTest` (red before T-088-06)

- [x] T-088-06 – Frame response implementation (F-088-05, F-088-07, F-088-10).  
  _Intent:_ `FrameClockData`, `FrameInfoData`, `FramePhotoData` under `app/Http/Resources/Frame/`; `FrameData` gains `clock`, `info`, `photo`; `FrameController::get` builds them.  
  _Verification commands:_  
  - `php artisan test --filter=FrameOverlayTest`  
  - `php artisan test --filter=FramePhotoDataTest`  
  - `php artisan test --filter=FrameTest`  
  - `make phpstan`

- [x] T-088-07 – Failing tests: album line (F-088-06, S-088-11, S-088-12, S-088-13).  
  _Intent:_ in `FrameOverlayTest`: fully random frame lists browsable albums sorted by title; a hidden (link-only) public album is not listed for a guest; an album frame without sub-album mode gives `albums = null`.  
  _Verification commands:_  
  - `php artisan test --filter=FrameOverlayTest` (red before T-088-08)

- [x] T-088-08 – Album line implementation (F-088-06).  
  _Intent:_ `app/Actions/Frame/ListBrowsableAlbumTitles.php`; `FrameRequest::offersAlbumLine()`; `FramePhotoData` takes the titles or `null`.  
  _Verification commands:_  
  - `php artisan test --filter=FrameOverlayTest`  
  - `make phpstan`

- [x] T-088-09 – Failing tests: sub-album mode (F-088-16, F-088-17, S-088-14, S-088-15).  
  _Intent:_ `tests/Feature_v3/Frame/FrameSubAlbumsTest.php`: album whose only photos are in a sub-album: `GET /Frame` answers a photo and lists its album when the mode is on, `PhotoCollectionEmptyException` when off; `Album::head` `config.is_mod_frame_enabled` true when on, false when off; the photo varies across draws (random order kept).  
  _Verification commands:_  
  - `php artisan test --filter=FrameSubAlbumsTest` (red before T-088-10)

- [x] T-088-10 – Sub-album mode implementation (F-088-16, F-088-17).  
  _Intent:_ `FrameController::loadPhoto()` with `all_photos()->reorder()->inRandomOrder()` for regular albums in sub-album mode; `AlbumConfig` Frame button rule.  
  _Verification commands:_  
  - `php artisan test --filter=FrameSubAlbumsTest`  
  - `php artisan test --filter=FrameTest`  
  - `php artisan test --filter=FrameOverlayTest`  
  - `make phpstan`

- [x] T-088-11 – TypeScript types and overlay helpers (F-088-04, F-088-15).  
  _Intent:_ `php artisan typescript:transform`; `resources/js/v8/utils/frameOverlay.ts`: parse each URL value type, merge over admin values, hex + opacity to `rgba()`, group boxes per corner.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-088-12 – v8 overlay components (F-088-01 … F-088-04, F-088-08, F-088-09, F-088-11, F-088-12).  
  _Intent:_ `resources/js/v8/components/frame/FrameClockBox.vue`, `FrameInfoBox.vue`, `FrameOverlays.vue`; `Frame.vue` wiring.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `npx eslint resources/js/v8/components/frame resources/js/v8/utils/frameOverlay.ts resources/js/v8/views/gallery-panels/Frame.vue`

- [ ] T-088-13 – Browser check (S-088-03, S-088-04, S-088-05, S-088-16 … S-088-20).  
  _Intent:_ scratch instance and Playwright script with a mocked clock; light and dark photos; RTL Back button corner.  
  _Verification commands:_  
  - Playwright script in the session scratchpad (results recorded in the plan's drift gate report)

- [ ] T-088-14 – Documentation and drift gate.  
  _Intent:_ knowledge map, roadmap row, `_current-session.md`, drift gate report in the plan, full quality gate.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, every test class above, `make phpstan`

## Notes / TODOs
- Restore every `Configs::set()` value in `tearDown()`: the test transaction does not roll the config cache back.
