# Feature Plan 079 – Live Metrics Struct-of-Arrays

_Linked specification:_ [spec.md](spec.md)
_Status:_ Testing
_Last updated:_ 2026-10-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criria

The live-metrics drawer loads from one grouped, capped SQL query instead of a hydrated Eloquent collection with five eager loads and one signed URL per row. Success: S-079-01..16 green; one SELECT on `live_metrics` per request (NFR-079-01); `MetricsGetTest` and `ConfigIntegrityTest` still green; `npm run check` and `make phpstan` clean.

## Scope Alignment

- **In scope:** `GET /api/v3/Metrics`; settings `live_metrics_result_limit` and `live_metrics_cleanup`; index on `live_metrics.created_at`; retention filter and cleanup setting on the v2 GET; v8 drawer adoption with a truncation line.
- **Out of scope:** v2 response shape, the metrics write path, v7, the SE-preview branch of the drawer (spec NG1–NG4).

## Dependencies & Interfaces

- `App\Policies\MetricsPolicy::CAN_SEE_LIVE`, `App\Actions\Metrics\CleanupMetrics`, `App\Actions\Metrics\GetMetrics` (v2).
- Per-driver date-format pattern of `App\Actions\Photo\StructOfArrays\QueryPhotoBuckets::dateTruncationExpression()`.
- v3 Asset endpoint and `resources/js/v8/components/thumbs/Thumb.vue` for thumbnails.
- `App\Models\Extensions\BaseConfigMigration` for settings; `ConfigIntegrity::SE_FIELDS`.

## Assumptions & Risks

- **Assumptions:** `app()->terminating()` callbacks run after `Response::send()` (which calls `fastcgi_finish_request()` under PHP-FPM) and are run by the test client's `Kernel::terminate()`.
- **Risks / Mitigations:**
  - `GROUP BY` on an expression not in the SELECT list: valid on all three drivers; the joined columns are only added in an outer query over the grouped subquery, so `ONLY_FULL_GROUP_BY` (MySQL) and PostgreSQL's strict grouping are satisfied.
  - `typescript:transform` may not resolve the enum element type from a `@param MetricsAction[]` docblock; check the generated `lychee.d.ts` and fall back to `string[]` typed by the consumer if needed.

## Implementation Drift Gate

After the last task: compare FR-079-01..09 and NFR-079-01..06 against code and tests, record results below, rerun the commands in the Exit Criteria.

### Report (2026-10-03)

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-079-01 | `routes/api_v3.php`, `LiveMetricsListController`, `LiveMetricsListResource` | `testEmpty`, `testAdminSeesAllExceptPhotoVisitsAndTagAlbums` |
| FR-079-02 | `GetLiveMetricsListRequest`, controller `live_metrics_enabled` check | `testFlagOffReturns403`, `testNonAdminForbiddenWhenAccessIsAdmin`, `testLiveMetricsDisabledReturns403`, `testGuestReturns401` |
| FR-079-03 | `QueryLiveMetrics::do()` / `groupedEvents()` | `testAdminSeesAllExceptPhotoVisitsAndTagAlbums`, `testNonAdminSeesOwnAlbumsAndOwnPhotos` |
| FR-079-04 | `QueryLiveMetrics::toResource()` | `testTitleIsNotEscaped` |
| FR-079-05 | `QueryLiveMetrics::toResource()` | `testThumbPhotoIds` |
| FR-079-06 | `QueryLiveMetrics::minuteExpression()` | `testEventsAreGroupedPerMinute` (SQLite; MySQL/PostgreSQL via CI) |
| FR-079-07 | `QueryLiveMetrics::do()` (`limit + 1`) | `testResultIsCapped` |
| FR-079-08 | `CleanupMetrics::apply()` / `threshold()`, `GetMetrics`, `MetricsController::get()` | `testExpiredEventsAreHiddenAndCleanedUpPerMode`, `testV2HidesExpiredEventsAndHonoursCleanupMode` (3 modes each) |
| FR-079-09 | `metrics-service.ts` `getV3()`, `LiveMetrics.vue` `prettifyV3()` | `npm run check`; manual check pending (T-079-11) |
| NFR-079-01 | single `toBase`-style query builder | `testListingRunsOneSelectOnLiveMetrics` |
| NFR-079-02 | `date('c', strtotime())`, `CleanupMetrics::threshold()` | `testEventsAreGroupedPerMinute` |
| NFR-079-03 | resource has no `visitor_id` | `testEmpty` (`assertExactJson`) |
| NFR-079-05 | `2026_10_03_000002_add_live_metrics_created_at_index.php` | migrations applied in every test run |
| NFR-079-06 | `ConfigIntegrity::SE_FIELDS`, 23 `all_settings.php`, 23 `statistics.php` | `ConfigIntegrityTest`, `ConfigIntegrityMiddlewareTest` |

Commands run green: `php artisan test --filter=` `LiveMetricsListTest` (18), `MetricsGetTest` (4), `EventsFiredTest` (4), `ConfigIntegrityTest` (2), `ConfigIntegrityMiddlewareTest` (2); `vendor/bin/php-cs-fixer fix`; `make phpstan`; `npm run format`; `npm run check`; `php artisan typescript:transform`; `php artisan lang:json`.

Spec corrections made while implementing: FR-079-02 (a disabled feed is 403, as v2, and guests are 401); there are 23 locales, not 22.

Open: T-079-11 manual drawer check; MySQL/MariaDB and PostgreSQL run in CI only.

## Increment Map

1. **I1 – Schema and settings** (NFR-079-05, NFR-079-06, DO-079-02, DO-079-03)
   - _Steps:_ migration adding the `created_at` index; config migration for `live_metrics_result_limit` and `live_metrics_cleanup`; enum `LiveMetricsCleanup`; both keys in `ConfigIntegrity::SE_FIELDS`; both keys in the 23 `lang/<locale>/all_settings.php` (description + details); `php artisan lang:json`.
   - _Commands:_ `php artisan test --filter=ConfigIntegrityTest`, `make phpstan`.
   - _Exit:_ migrations apply in the test run; ConfigIntegrityTest green.
2. **I2 – Failing feature tests** (S-079-01..16)
   - _Steps:_ `tests/Feature_v3/Metrics/LiveMetricsListTest.php`; confirm red (route missing).
   - _Commands:_ `php artisan test --filter=LiveMetricsListTest`.
3. **I3 – Backend** (FR-079-01..08, NFR-079-01..04)
   - _Steps:_ `LiveMetricsListResource`, `GetLiveMetricsListRequest`, `QueryLiveMetrics`, `CleanupMetrics::apply()` (mode dispatch) + `CleanupMetrics::threshold()`, `LiveMetricsListController`, route in `routes/api_v3.php`; v2 `MetricsController::get()` uses `apply()`, `GetMetrics` filters on the threshold; `php artisan typescript:transform`.
   - _Commands:_ `php artisan test --filter=LiveMetricsListTest`, `php artisan test --filter=MetricsGetTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
4. **I4 – v8 drawer** (FR-079-09, UI-079-01)
   - _Steps:_ `MetricsService.getV3()`; `LiveMetrics.vue` v3 loader, count-summing merge, `<Thumb>` for v3 rows, truncation line; `statistics.metrics.truncated` in 23 `lang/<locale>/statistics.php`; `php artisan lang:json`.
   - _Commands:_ `npm run format`, `npm run check`.
5. **I5 – Docs and gates**
   - _Steps:_ roadmap row, knowledge map, drift gate report, `_current-session.md`.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-079-01..10 | I2–I3 / T-079-04, T-079-05..08 | `LiveMetricsListTest` |
| S-079-11 | I2–I3 / T-079-04, T-079-06 | grouping |
| S-079-12 | I2–I3 / T-079-04, T-079-06 | cap |
| S-079-13, S-079-14 | I2–I3 / T-079-04, T-079-07 | v2 and v3 |
| S-079-15, S-079-16 | I2–I3 / T-079-04, T-079-06 | format, query count |

## Analysis Gate

2026-10-03, agent review.
1. Specification completeness — pass: FR/NFR populated; Q-079-01..05 folded into FR-079-06..09 and NFR-079-05; ASCII mock-up present.
2. Open questions — pass: none open; no ADR needed (applies ADR-0009, ADR-069-01, which were reviewed).
3. Plan alignment — pass.
4. Tasks coverage — pass: every FR maps to a task; tests (T-079-04) precede implementation (T-079-05..09).
5. Working agreements — pass: persistence change (index) and new settings approved in Q-079-03/04/05; no new dependency; cleanup dispatch is a small helper with flat branches.
6. Tooling — pass: commands listed per increment.

## Exit Criteria

- `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `make phpstan` clean.
- `php artisan test --filter=LiveMetricsListTest`, `--filter=MetricsGetTest`, `--filter=ConfigIntegrityTest` green.
- `php artisan typescript:transform` rerun; `php artisan lang:json` rerun.
- Roadmap and knowledge map updated.

## Follow-ups / Backlog

- Manual drawer check in a browser with `is_struct_of_array_enabled` on and off.
