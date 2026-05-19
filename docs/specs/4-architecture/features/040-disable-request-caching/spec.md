# Feature 040 – Disable Request Caching

| Field | Value |
|-------|-------|
| Status | Planning |
| Last updated | 2026-05-18 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/040-disable-request-caching/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/040-disable-request-caching/tasks.md` |
| Roadmap entry | #040 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The Redis-backed request caching feature (`cache_enabled`) has been identified as something that should be **off by default and hidden from the settings UI** unless an operator explicitly opts in via an environment variable. This feature (1) introduces a database migration that forces `cache_enabled = 0` regardless of its current value, and (2) adds a `ENABLE_REQUEST_CACHING` environment variable to `config/features.php` (default `false`) that controls whether the three caching-related config rows (`cache_enabled`, `cache_ttl`, `cache_event_logging`) are visible in the admin settings UI. When `ENABLE_REQUEST_CACHING=false` the settings panel never exposes these options to the admin, preventing accidental re-activation.

## Goals

1. Ensure all existing installations have `cache_enabled` forced to `0` after running migrations.
2. Provide a clear operator-level opt-in (`ENABLE_REQUEST_CACHING=true` in `.env`) that makes the caching settings visible in the admin UI.
3. Keep the actual caching middleware (`ResponseCache`, `AlbumRouteCacheRefresher`) intact — the behaviour is gated by `cache_enabled`; this feature simply ensures it is off unless deliberately enabled by the operator.
4. Update `.env.example` to document the new environment variable.
5. Ensure the quality gate (PHPStan, php-cs-fixer, tests) passes with no regressions.

## Non-Goals

- Removing or refactoring the caching middleware or route-cache infrastructure.
- Providing a UI toggle to enable/disable request caching at runtime.
- Changing the default cache driver or Redis configuration.
- Adding a new property to `InitConfig` / `LycheeStateStore` to propagate the feature flag to the frontend (see Appendix — this is not required because the existing `v-if` guard in `General.vue` already handles hiding).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-040-01 | A database migration sets the `cache_enabled` config value to `'0'` unconditionally. | After running `php artisan migrate`, `DB::table('configs')->where('key','cache_enabled')->value('value')` returns `'0'`. | Migration rollback (`down`) reverts nothing (the value is not restored; the migration is one-directional). | If the `configs` table or key does not exist the migration should exit gracefully without error. | None. | Problem statement: "set the value to 0 no matter the current value". |
| FR-040-02 | `config/features.php` exposes a new key `enable-request-caching` sourced from `env('ENABLE_REQUEST_CACHING', false)`. | `config('features.enable-request-caching')` returns `false` unless `.env` contains `ENABLE_REQUEST_CACHING=true`. | Unit test or feature test reads the resolved config value. | N/A — env defaults are always available. | None. | Problem statement: "link it to an env variable in .env ENABLE_REQUEST_CACHING (default to false)". |
| FR-040-03 | The admin settings controller (`SettingsController::getAll`) filters out all configs with `cat = 'Mod Cache'` when `config('features.enable-request-caching')` is `false`. | When `ENABLE_REQUEST_CACHING` is unset or `false`, the API response for `GET /api/v2/Settings` contains no configs whose `cat` is `'Mod Cache'`. | Feature test asserts the category is absent in the response body. | If the feature flag is `true`, all three cache config rows (`cache_enabled`, `cache_ttl`, `cache_event_logging`) appear normally. | None. | Problem statement: "disable the config visibility in the setting". |
| FR-040-04 | `.env.example` is updated to document `ENABLE_REQUEST_CACHING=false` with a descriptive comment. | `.env.example` contains the key and a comment explaining the flag. | Code review / doc review. | N/A. | None. | Coding convention: keep `.env.example` in sync with new env variables. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-040-01 | Migration must be idempotent: running it twice must not throw an error. | Deployment safety. | Test environment running `php artisan migrate` twice without failure. | Laravel migration system. | Standard practice. |
| NFR-040-02 | PHPStan level 6 must report 0 errors after changes. | Code quality gate. | `make phpstan` exits 0. | `phpstan.neon` baseline. | Coding conventions. |
| NFR-040-03 | `php-cs-fixer` must report 0 violations after changes. | Code style gate. | `vendor/bin/php-cs-fixer fix --dry-run` exits 0. | `.php-cs-fixer.php`. | Coding conventions. |
| NFR-040-04 | All existing tests must continue to pass. | Regression safety. | `php artisan test` exits 0. | SQLite test database. | Coding conventions. |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-040-01 | **Migration – value forced off.** An installation where `cache_enabled = '1'` runs `php artisan migrate`. After migration `cache_enabled = '0'`. |
| S-040-02 | **Migration – already off.** An installation where `cache_enabled = '0'` runs `php artisan migrate`. Migration completes without error; value remains `'0'`. |
| S-040-03 | **Feature flag default off – settings hidden.** `ENABLE_REQUEST_CACHING` not set (default). `GET /api/v2/Settings` response contains no `Mod Cache` category. |
| S-040-04 | **Feature flag explicitly false – settings hidden.** `.env` has `ENABLE_REQUEST_CACHING=false`. Same outcome as S-040-03. |
| S-040-05 | **Feature flag true – settings visible.** `.env` has `ENABLE_REQUEST_CACHING=true`. `GET /api/v2/Settings` response contains the `Mod Cache` category with `cache_enabled`, `cache_ttl`, `cache_event_logging` rows. |
| S-040-06 | **Migration rollback.** Running `php artisan migrate:rollback` on the new migration completes without error (no value restoration). |
| S-040-07 | **Vue toggle hidden when flag off.** `ENABLE_REQUEST_CACHING` is unset or `false`. In `resources/js/components/settings/General.vue`, the `BoolField` for `cache_enabled` is not rendered because `cache_enabled.value` is `undefined` (config not in API response). No changes to `General.vue` or `InitConfig` are required — the existing `v-if="cache_enabled !== undefined"` guard handles this automatically. |
| S-040-08 | **Vue toggle visible when flag on.** `ENABLE_REQUEST_CACHING=true`. The `GET /api/v2/Settings` response includes `cache_enabled`; `load()` finds it; `v-if="cache_enabled !== undefined"` is `true`; the toggle renders. |

## Test Strategy

- **Migration:** Covered implicitly by the full test suite (SQLite re-runs all migrations). No additional migration-specific test needed beyond confirming the test suite passes.
- **Feature flag (config):** Verified by the existing settings feature tests (`tests/Feature_v2/` covering `GET /api/v2/Settings`) when run with default env (flag off) and with the flag forced on via `config(['features.enable-request-caching' => true])`.
- **REST (settings list):** Add or extend a `Feature_v2` test that asserts:
  - With flag `false`: response body does not include any config with `cat = 'Mod Cache'`.
  - With flag `true`: response body includes at least one config with `cat = 'Mod Cache'`.
- **Vue (`General.vue`):** No code changes and no additional tests required. The existing `v-if="cache_enabled !== undefined"` guard in the `<BoolField>` element already hides the toggle when `cache_enabled` is absent from the API response. The `load()` function in `General.vue` populates `cache_enabled` via `configurations.find(config => config.key === 'cache_enabled')`; when the config is not in the API payload the reactive ref stays `undefined` and the toggle is not rendered. This behaviour is indirectly verified by the REST tests above.
- **`InitConfig` / `LycheeStateStore`:** No changes required. The frontend does not need an explicit feature-flag property because the conditional rendering is already driven by whether the config exists in the API payload.
- **Core / CLI / Docs:** No changes required.

## Interface & Contract Catalogue

### Domain Objects

_None introduced._

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-040-01 | REST GET /api/v2/Settings | Returns all visible config categories and their rows. | Affected by FR-040-03: the `Mod Cache` category is omitted when `ENABLE_REQUEST_CACHING=false`. |

### CLI Commands / Flags

_None introduced._

### Telemetry Events

_None introduced._

### Fixtures & Sample Data

_None introduced._

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-040-01 | `Mod Cache` category absent from settings panel | `ENABLE_REQUEST_CACHING` is unset or `false`. Admin opens Settings; no caching section visible. Mechanism: backend API filter → `cache_enabled` absent from response → `v-if="cache_enabled !== undefined"` is `false` in `General.vue`. |
| UI-040-02 | `Mod Cache` category present in settings panel | `ENABLE_REQUEST_CACHING=true`. Admin opens Settings; caching section with three rows visible. Mechanism: filter inactive → `cache_enabled` config included in response → `v-if="cache_enabled !== undefined"` is `true`. |

## Telemetry & Observability

No new telemetry events. The existing `cache_event_logging` config row remains unchanged.

## Documentation Deliverables

- Update `.env.example` to include `ENABLE_REQUEST_CACHING=false` with a comment.
- Update `roadmap.md` (Active Features table → Completed once done).
- Update `_current-session.md`.

## Fixtures & Sample Data

None.

## Spec DSL

```yaml
routes:
  - id: API-040-01
    method: GET
    path: /api/v2/Settings
ui_states:
  - id: UI-040-01
    description: Mod Cache category absent (flag off)
  - id: UI-040-02
    description: Mod Cache category visible (flag on)
```

## Appendix

### Affected files (anticipated)

| File | Change |
|------|--------|
| `database/migrations/2026_05_18_000001_disable_request_caching.php` | New migration: set `cache_enabled = '0'`. |
| `config/features.php` | Add `enable-request-caching` entry sourced from `ENABLE_REQUEST_CACHING`. |
| `app/Http/Controllers/Admin/SettingsController.php` | Add `->when(config('features.enable-request-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))` filter. |
| `.env.example` | Add `ENABLE_REQUEST_CACHING=false` with comment. |
| `tests/Feature_v2/Settings/...` | Add/extend test for S-040-03 and S-040-05. |

### `General.vue` and `InitConfig` — no changes required

`resources/js/components/settings/General.vue` already guards the cache toggle with:

```html
<BoolField
    v-if="cache_enabled !== undefined"
    :label="$t('settings.system.cache_enabled')"
    :config="cache_enabled"
    ...
/>
```

The reactive ref is populated in `load()`:

```ts
cache_enabled.value = configurations.find((config) => config.key === "cache_enabled");
```

When `SettingsController::getAll` filters out the `Mod Cache` category (FR-040-03), `cache_enabled` is absent from the API response. `configurations.find(...)` returns `undefined`, so `cache_enabled.value` remains `undefined`, and the `v-if` guard keeps the toggle hidden. This end-to-end chain means:

1. **No changes to `General.vue`** — the `v-if` guard is already in place.
2. **No changes to `InitConfig`** — the frontend does not need a dedicated `is_request_caching_enabled` property. Propagating the flag via `InitConfig` → `LycheeStateStore` would introduce an unnecessary data path when the API-level filter already provides the correct signal.

This decision is captured as S-040-07 and S-040-08 in the Branch & Scenario Matrix.

---

*Last updated: 2026-05-18*
