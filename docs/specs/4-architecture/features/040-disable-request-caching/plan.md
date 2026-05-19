# Feature Plan 040 – Disable Request Caching

_Linked specification:_ `docs/specs/4-architecture/features/040-disable-request-caching/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-05-18

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Redis-backed request caching is disabled by default on all installations. Operators who wish to use it must explicitly opt in by setting `ENABLE_REQUEST_CACHING=true` in `.env`, which makes the caching settings visible in the admin UI. Success is measured by:

- All existing installations have `cache_enabled = 0` after migration.
- `GET /api/v2/Settings` omits the `Mod Cache` category when `ENABLE_REQUEST_CACHING` is not set.
- PHPStan 0 errors, php-cs-fixer clean, all tests pass.

## Scope Alignment

- **In scope:**
  - Database migration to force `cache_enabled = '0'` (FR-040-01).
  - New `enable-request-caching` key in `config/features.php` tied to `ENABLE_REQUEST_CACHING` env variable (FR-040-02).
  - `SettingsController::getAll` filter to hide `Mod Cache` configs when flag is off (FR-040-03).
  - `.env.example` update (FR-040-04).
  - Feature test covering S-040-03 and S-040-05.
  - Quality gates (NFR-040-01 to NFR-040-04).
  - Confirmation that `General.vue` and `InitConfig` require **no changes** (S-040-07 / S-040-08 — handled automatically by the API-level filter).

- **Out of scope:**
  - Removing or refactoring the caching middleware.
  - Runtime UI toggle for enabling caching.
  - Redis configuration changes.
  - Changes to `InitConfig`, `LycheeStateStore`, or `General.vue` — not required because the existing `v-if="cache_enabled !== undefined"` guard in `General.vue` already hides the toggle when the config is absent from the API response.

## Dependencies & Interfaces

- `database/migrations/` — migration naming convention (`YYYY_MM_DD_HHMMSS_<name>.php`).
- `App\Models\Extensions\BaseConfigMigration` — not used here; this migration uses a plain `Migration` with a direct `DB::table` update.
- `config/features.php` — existing feature-flag file; pattern already in use for `hide-lychee-SE`, `webshop`, `webhook`, etc.
- `App\Http\Controllers\Admin\SettingsController::getAll` — query builder chain that filters configs; pattern already used for `hide-lychee-SE` and `not_on_docker`.
- `tests/Feature_v2/` — existing PHPUnit feature test suite using `BaseApiWithDataTest`.

## Assumptions & Risks

- **Assumptions:**
  - The `configs` table and the `cache_enabled` key are always present when the new migration runs (introduced in `2024_12_28_190150_caching_config.php`).
  - The `Mod Cache` category string is stable and used only for caching-related configs.
- **Risks / Mitigations:**
  - If `cache_enabled` key is missing (e.g., very old or incomplete installation), the migration's `DB::table` update will silently affect 0 rows — which is safe.
  - Tests that currently rely on `cache_enabled = 1` being the stored default could fail; check existing test suite for such assumptions before committing.

## Implementation Drift Gate

After each increment, run:

```bash
vendor/bin/php-cs-fixer fix
php artisan test
make phpstan
```

Record results in this plan's Scenario Tracking table.

## Increment Map

### I1 – Database Migration (≤30 min)

- _Goal:_ Force `cache_enabled = '0'` for all rows in `configs` (FR-040-01, S-040-01, S-040-02).
- _Preconditions:_ None.
- _Steps:_
  1. Write the test first: assert `configs` row `cache_enabled` equals `'0'` after the test suite runs (covered implicitly by the full migration run in tests).
  2. Create `database/migrations/2026_05_18_000001_disable_request_caching.php` extending `Migration`.
  3. `up()`: `DB::table('configs')->where('key', 'cache_enabled')->update(['value' => '0']);`
  4. `down()`: no-op (value is not restored — migration is one-directional per FR-040-01 failure path).
  5. Verify test suite passes, PHPStan clean.
- _Commands:_ `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`
- _Exit:_ Migration file present and parseable; `php artisan migrate` completes without error; test suite passes.

### I2 – Feature Flag in `config/features.php` (≤20 min)

- _Goal:_ Expose `enable-request-caching` feature flag (FR-040-02, S-040-03, S-040-04, S-040-05).
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add a new entry in `config/features.php`:
     ```php
     'enable-request-caching' => (bool) env('ENABLE_REQUEST_CACHING', false),
     ```
  2. Add `ENABLE_REQUEST_CACHING=false` with a descriptive comment to `.env.example` (FR-040-04).
  3. Run `php artisan config:clear` locally and verify `config('features.enable-request-caching')` defaults to `false`.
- _Commands:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`
- _Exit:_ `config/features.php` updated; `.env.example` updated; PHPStan 0 errors.

### I3 – Settings Controller Filter (≤30 min)

- _Goal:_ Hide `Mod Cache` configs from admin settings when feature flag is off (FR-040-03, S-040-03, S-040-04, S-040-05, S-040-07, S-040-08).
- _Preconditions:_ I2 complete.
- _Steps:_
  1. In `SettingsController::getAll`, add the filter to the `configs` query chain (after the existing `not_on_docker` filter):
     ```php
     ->when(config('features.enable-request-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))
     ```
  2. **No changes to `General.vue` or `InitConfig` are required.** `General.vue` already guards the cache toggle with `v-if="cache_enabled !== undefined"`. The `load()` function populates `cache_enabled` via `configurations.find(config => config.key === 'cache_enabled')`. When the API omits the `Mod Cache` category, `cache_enabled.value` is `undefined` and the toggle is automatically hidden. Adding an explicit `is_request_caching_enabled` property to `InitConfig` and `LycheeStateStore` would be redundant.
  3. Run full test suite to verify no regressions.
- _Commands:_ `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`
- _Exit:_ Controller updated; tests pass; `General.vue` toggle hidden (S-040-07) confirmed indirectly by REST test.

### I4 – Feature Tests (≤40 min)

- _Goal:_ Provide explicit test coverage for S-040-03 and S-040-05.
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Locate or create a `Feature_v2` test class for the Settings endpoint (extending `BaseApiWithDataTest`).
  2. Add test method for S-040-03: with default config (flag off), assert `GET /api/v2/Settings` response does not contain a category named `Mod Cache`.
  3. Add test method for S-040-05: with `config(['features.enable-request-caching' => true])`, assert `GET /api/v2/Settings` response contains a category named `Mod Cache` with the expected config keys.
  4. Run test suite, confirm new tests are green.
- _Commands:_ `php artisan test --filter=Settings`, `make phpstan`, `vendor/bin/php-cs-fixer fix`
- _Exit:_ New tests pass; no other tests broken.

### I5 – Quality Gates & Documentation (≤20 min)

- _Goal:_ Ensure full pipeline passes and roadmap/session docs are updated.
- _Preconditions:_ I4 complete.
- _Steps:_
  1. Run complete quality gate: `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`.
  2. Update `roadmap.md`: move Feature 040 from Active to Completed.
  3. Update `_current-session.md` with session summary for Feature 040.
- _Commands:_ `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`
- _Exit:_ All gates green; docs updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-040-01 | I1 / T-040-01 | Covered by migration `up()` and test suite re-run. |
| S-040-02 | I1 / T-040-01 | Idempotent `UPDATE` with no-op when already `'0'`. |
| S-040-03 | I3, I4 / T-040-04, T-040-05 | Controller filter; explicit feature test. |
| S-040-04 | I3, I4 / T-040-04, T-040-05 | Same filter path as S-040-03. |
| S-040-05 | I3, I4 / T-040-04, T-040-06 | Controller filter absent when flag is `true`; explicit feature test. |
| S-040-06 | I1 / T-040-01 | `down()` is a no-op; migrate:rollback safe. |
| S-040-07 | I3 / T-040-04 | `General.vue` hides toggle automatically — `v-if="cache_enabled !== undefined"` is `false` when config absent from API response. No changes to `General.vue` or `InitConfig` required. |
| S-040-08 | I3 / T-040-04 | `General.vue` shows toggle when config present in API response. |

## Analysis Gate

_To be completed before coding begins._

- [ ] All four FRs are unambiguous and traceable to tasks.
- [ ] All six scenarios map to at least one increment/task.
- [ ] No open questions logged in `open-questions.md` for Feature 040.
- [ ] Estimated total effort ≤ 140 min (fits within session).

## Exit Criteria

- [ ] Migration `2026_05_18_000001_disable_request_caching.php` created and applied.
- [ ] `config/features.php` contains `enable-request-caching` key.
- [ ] `.env.example` documents `ENABLE_REQUEST_CACHING=false`.
- [ ] `SettingsController::getAll` filters `Mod Cache` configs when flag is off.
- [ ] Feature tests for S-040-03 and S-040-05 pass.
- [ ] `vendor/bin/php-cs-fixer fix` exits 0.
- [ ] `php artisan test` exits 0.
- [ ] `make phpstan` exits 0.
- [ ] `roadmap.md` updated.

## Follow-ups / Backlog

- Consider adding a diagnostic warning when `ENABLE_REQUEST_CACHING=true` but Redis is not configured as the cache driver.
- If the caching feature is later removed entirely, the `Mod Cache` config category and all three config rows can be dropped via a follow-up migration.

---

*Last updated: 2026-05-18*
