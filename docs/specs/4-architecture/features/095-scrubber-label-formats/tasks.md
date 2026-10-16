# Feature 095 Tasks – Scrubber Label Formats

_Status: Complete_  
_Last updated: 2026-10-16_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

- [x] T-095-01 – Scrubber format configs (F-095-01, S-095-04, S-095-06).  
  _Intent:_ failing tests first (defaults, levels, SE membership, empty value rejected); migration with the four keys (orders 23–26); `ConfigIntegrity::SE_FIELDS`; translated lang entries in every locale.  
  _Verification commands:_  
  - `php artisan test --filter=DateFormatsInitTest`  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

- [x] T-095-02 – Default migration: hour header and album day formats (F-095-07, S-095-08).  
  _Intent:_ failing migration test first; migration `g:i` → `H:00` and `j M` → `j M Y`, custom values kept, rollback, `cache:clear`.  
  _Verification commands:_  
  - `php artisan test --filter=<migration test class>`  
  - `make phpstan`

- [x] T-095-03 – `InitConfig` fields (F-095-02, S-095-09).  
  _Intent:_ test first; five fields; `php artisan typescript:transform`; `LycheeState`.  
  _Verification commands:_  
  - `php artisan test --filter=DateFormatsInitTest`  
  - `make phpstan`  
  - `npm run check`

- [x] T-095-04 – Formats in the rail, hourly lens and sub-album lens (F-095-03, F-095-04, F-095-06, S-095-01, S-095-03, S-095-07).  
  _Intent:_ `TickFormats` parameter, constants removed; callers pass `LycheeState` values; sub-album lens uses `date_scrubber_album_label_format`; Feature 094 Node check rerun with defaults.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`

- [x] T-095-05 – Clip resting labels to the rail (F-095-05, S-095-05).  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - browser LTR/RTL with a long day format

- [x] T-095-06 – Browser check, docs and drift gate (S-095-01 … 07).  
  _Intent:_ scratch instance (settings page order, S-095-10); Feature 092/094 docs, knowledge map, roadmap; drift gate report.  
  _Verification commands:_ `run-lychee`; `npm run check`; `make phpstan`.

## Notes / TODOs

- 2026-10-16: verified with `php artisan test` (ScrubberLabelFormatsSettingsTest 7, TimelineDateFormatDefaultsMigrationTest 3, DateFormatsInitTest 3, ConfigTranslationKeysTest 8, GetAllSettingsTest 5), `make phpstan`, `php-cs-fixer`, `npm run check`; Node helper check 29/29 plus custom formats; browser on a fresh scratch instance (seed094.php + seed095.php): settings page free / SE preview, `j.n.`, `G\h`, clipped `l j F Y`, sub-album pill `21 Oct 2026` / `21.10.2026`, hour headers `18:00`.

- Run test classes one at a time (shared SQLite test DB).
