# Feature 106 Tasks – On This Day Index

_Status: Complete_  
_Last updated: 2026-11-01_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-106-01 – Failing `MonthDayKeyTest` (FR-106-01).  
  _Intent:_ Unit tests for the key: taken date in its own zone (Paris after midnight), no taken date → upload date, 29 February, 31 December.  
  _Verification commands:_  
  - `php artisan test --filter=MonthDayKeyTest` (red)

- [x] T-106-02 – `App\Services\MonthDayKey` (FR-106-01).  
  _Intent:_ `of(?DateTimeInterface $taken_at, DateTimeInterface $created_at): int`.  
  _Verification commands:_  
  - `php artisan test --filter=MonthDayKeyTest`  
  - `make phpstan`

- [x] T-106-03 – Migration, factory, model docblock (FR-106-01, FR-106-04).  
  _Intent:_ `photos.month_day_key` + `photos_month_day_key_index`, backfill from `taken_key` / `created_at` (two `UPDATE`s per driver); `PhotoFactory` `afterMaking` assigns the key; `@property` on `Photo`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoKeysWriteTest`  
  - `make phpstan`

- [x] T-106-04 – Failing `OnThisDayAlbumTest` (FR-106-02, S-106-01, S-106-02, S-106-03, S-106-04, S-106-05).  
  _Intent:_ Feature_v3, time frozen; S-106-05 fails on the current UTC-day query.  
  _Verification commands:_  
  - `php artisan test --filter=OnThisDayAlbumTest` (red on S-106-05)

- [x] T-106-05 – `OnThisDayAlbum` on `month_day_key` (FR-106-02, NFR-106-02).  
  _Intent:_ `month_day_key = today` and (`taken_at IS NOT NULL` or `created_at < 1 January`).  
  _Verification commands:_  
  - `php artisan test --filter=OnThisDayAlbumTest`  
  - `php artisan test --filter=AlbumHeadEndpointTest`  
  - `php artisan test --filter=AlbumCategoryV3Test`

- [x] T-106-06 – Failing write-site cases in `PhotoKeysWriteTest` (FR-106-03, S-106-06, S-106-07, S-106-08).  
  _Intent:_ upload, edit taken date, reset to the initial taken date, edit upload date without taken date, `lychee:takedate`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoKeysWriteTest` (red)

- [x] T-106-07 – Assign the key at the four write sites (FR-106-03).  
  _Intent:_ `Shared\Save`, `Duplicate\SaveIfDirty`, `PhotoController::update`, `Takedate`.  
  _Verification commands:_  
  - `php artisan test --filter=PhotoKeysWriteTest`  
  - `php artisan test --filter=TakeDateTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-106-08 – Knowledge map, roadmap, scratch verification (NFR-106-01).  
  _Intent:_ knowledge map entry next to `taken_key`; migrate a copy of the 50,000-photo scratch database, `EXPLAIN QUERY PLAN` of the On This Day query, `Album::head` and `Albums/smart` timings before and after; drift gate in the plan.  
  _Verification commands:_  
  - scratchpad `cachecheck/head.php on_this_day`, `cachecheck/smart.php`

## Notes / TODOs
- No migration test (backfill checked on the scratch instance).
