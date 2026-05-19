# Feature 040 Tasks – Disable Request Caching

_Status: Draft_  
_Last updated: 2026-05-18_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-040-`) inside the same parentheses immediately after the task title.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections and, when required, ADRs reflect the clarified behaviour.

## Checklist

### I1 – Database Migration

- [x] T-040-01 – Create migration to force `cache_enabled = '0'` (FR-040-01, S-040-01, S-040-02, S-040-06).  
  _Intent:_ Create `database/migrations/2026_05_18_000001_disable_request_caching.php` extending `Illuminate\Database\Migrations\Migration`. The `up()` method updates `configs` where `key = 'cache_enabled'` to `value = '0'`. The `down()` method is intentionally a no-op (migration is one-directional — the old value is not restored).  
  _Verification commands:_  
  - `php artisan test` — full suite must pass (migration is applied to SQLite test DB on every run).  
  - `make phpstan` — 0 errors.  
  - `vendor/bin/php-cs-fixer fix` — 0 violations (run with `--dry-run` to check; fix without `--dry-run` to apply).  
  _Notes:_ Use `DB::table('configs')->where('key', 'cache_enabled')->update(['value' => '0'])` in `up()`. No `BaseConfigMigration` inheritance needed since this is an update, not an insert. License header required (see coding conventions).

### I2 – Feature Flag

- [x] T-040-02 – Add `enable-request-caching` feature flag to `config/features.php` (FR-040-02, S-040-03, S-040-04, S-040-05).  
  _Intent:_ Append a new entry to `config/features.php` following the existing pattern:
  ```php
  'enable-request-caching' => (bool) env('ENABLE_REQUEST_CACHING', false),
  ```
  Include a block comment following the style of neighbouring entries, explaining that setting `ENABLE_REQUEST_CACHING=true` makes the caching-related settings visible in the admin UI.  
  _Verification commands:_  
  - `make phpstan` — 0 errors.  
  - `vendor/bin/php-cs-fixer fix` — 0 violations.  
  _Notes:_ Default is `false`; no `.env` change required for the safe default.

- [x] T-040-03 – Update `.env.example` to document `ENABLE_REQUEST_CACHING` (FR-040-04).  
  _Intent:_ Add `ENABLE_REQUEST_CACHING=false` with a descriptive comment to `.env.example`, near the other feature-flag entries (e.g., near `WEBHOOK_ENABLED`, `WEBSHOP_ENABLED`, or `HIDE_LYCHEE_SE_CONFIG`).  
  _Verification commands:_  
  - Manual review: confirm key and comment are present.  
  _Notes:_ No functional impact; documentation only.

### I3 – Settings Controller Filter

- [x] T-040-04 – Filter `Mod Cache` configs out of settings response when `ENABLE_REQUEST_CACHING` is off (FR-040-03, S-040-03, S-040-04, S-040-05, S-040-07, S-040-08).  
  _Intent:_ In `app/Http/Controllers/Admin/SettingsController::getAll`, add a `->when(...)` filter to the `configs` eager-load query chain. Specifically, after the existing `->when($docker_info->isDocker(), ...)` clause, add:
  ```php
  ->when(config('features.enable-request-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))
  ```
  This mirrors the existing `hide-lychee-SE` pattern (`->when(config('features.hide-lychee-SE', false) === true, fn ($q) => $q->where('cat', '!=', 'lychee SE'))`).

  **`General.vue` and `InitConfig` — no code changes required.** `resources/js/components/settings/General.vue` already guards the cache toggle with `v-if="cache_enabled !== undefined"`. The `load()` function populates `cache_enabled` via `configurations.find(config => config.key === 'cache_enabled')`. When this task's filter removes `Mod Cache` from the API response, `cache_enabled.value` is `undefined` and the toggle is not rendered — automatically satisfying S-040-07. No new `InitConfig` property or `LycheeStateStore` field is needed.  
  _Verification commands:_  
  - `php artisan test` — full suite must pass.  
  - `make phpstan` — 0 errors.  
  - `vendor/bin/php-cs-fixer fix` — 0 violations.  
  _Notes:_ This is a one-line change to the existing query chain. The Vue hiding behaviour (S-040-07) is confirmed indirectly by the REST-level tests in T-040-05 / T-040-06.

### I4 – Feature Tests

- [x] T-040-05 – Write feature test: `Mod Cache` category absent when flag is off (S-040-03, S-040-04).  
  _Intent:_ In `tests/Feature_v2/` (extending `BaseApiWithDataTest`), add a test method that:
  1. Ensures `config('features.enable-request-caching')` is `false` (default).
  2. Calls `GET /api/v2/Settings` as admin.
  3. Asserts the response JSON does not contain any item with a `title` or `key` of `Mod Cache` (or that the entire category is absent).  
  _Verification commands:_  
  - `php artisan test --filter=<TestClassName>` — new test must pass green.  
  - `make phpstan` — 0 errors.  
  - `vendor/bin/php-cs-fixer fix` — 0 violations.  
  _Notes:_ Locate the existing settings test file in `tests/Feature_v2/` to find the correct base class and endpoint path.

- [x] T-040-06 – Write feature test: `Mod Cache` category present when flag is on (S-040-05).  
  _Intent:_ Add a companion test method in the same test class that:
  1. Forces `config(['features.enable-request-caching' => true])`.
  2. Calls `GET /api/v2/Settings` as admin.
  3. Asserts the response JSON contains a category with at least one config whose `key` is `cache_enabled`.  
  _Verification commands:_  
  - `php artisan test --filter=<TestClassName>` — new test must pass green.  
  - `make phpstan` — 0 errors.  
  - `vendor/bin/php-cs-fixer fix` — 0 violations.  
  _Notes:_ Use `$this->withConfig(['features.enable-request-caching' => true])` or `config([...])` override pattern already used elsewhere in the test suite.

### I5 – Quality Gates & Documentation

- [x] T-040-07 – Run full quality gate (NFR-040-01 to NFR-040-04).  
  _Intent:_ Execute the complete quality gate sequence and confirm all checks pass.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ All three must exit 0 before proceeding.

- [ ] T-040-08 – Update `roadmap.md`: move Feature 040 from Active to Completed.  
  _Intent:_ Add Feature 040 row to the Completed table with today's date and a one-line summary. Remove it from Active Features. Update the "Last updated" footer.  
  _Verification commands:_ Manual review.  
  _Notes:_ Follow the pattern of completed rows (e.g., Feature 037).

- [ ] T-040-09 – Update `_current-session.md` with Feature 040 summary.  
  _Intent:_ Replace the Feature 037 session context with a Feature 040 summary covering what was implemented and confirming all tasks complete.  
  _Verification commands:_ Manual review.  
  _Notes:_ Keep the session doc as the single live snapshot per the session conventions.

## Notes / TODOs

- If `GET /api/v2/Settings` route or response shape differs from assumed, update T-040-05/T-040-06 assertions accordingly.
- `Mod Cache` is the exact category name used in the migration `2024_12_28_190150_caching_config.php` and is stable.
- The `down()` no-op in T-040-01 is intentional per FR-040-01 failure path: once the caching is disabled, we do not restore the previous value on rollback.
- **`General.vue` requires no changes.** The toggle `<BoolField v-if="cache_enabled !== undefined" ...>` is already guarded. When the SettingsController filter (T-040-04) removes `Mod Cache` from the API response, `cache_enabled.value` stays `undefined` and the toggle is hidden automatically. This was confirmed by code inspection (see spec Appendix).
- **`InitConfig` and `LycheeStateStore` require no changes.** No new `is_request_caching_enabled` property is needed — the API-level filter is the sole signal.
