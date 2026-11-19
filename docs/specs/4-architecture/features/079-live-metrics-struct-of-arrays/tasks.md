# Feature 079 Tasks – Live Metrics Struct-of-Arrays

_Status: Testing_
_Last updated: 2026-10-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist
- [x] T-079-01 – Index on `live_metrics.created_at` (NFR-079-05).
  _Intent:_ new migration.
  _Verification commands:_ `php artisan test --filter=MetricsGetTest`

- [x] T-079-02 – Settings `live_metrics_result_limit`, `live_metrics_cleanup` and enum `LiveMetricsCleanup` (FR-079-07, FR-079-08, DO-079-02, DO-079-03, NFR-079-06).
  _Intent:_ config migration, enum, `ConfigIntegrity::SE_FIELDS`, 23 `all_settings.php`, `php artisan lang:json`.
  _Verification commands:_ `php artisan test --filter=ConfigIntegrityTest`

- [x] T-079-03 – Roadmap row (status In progress).

- [x] T-079-04 – Failing tests `tests/Feature_v3/Metrics/LiveMetricsListTest.php` (S-079-01..16).
  _Verification commands:_ `php artisan test --filter=LiveMetricsListTest` (red: route missing)

- [x] T-079-05 – `LiveMetricsListResource`, `GetLiveMetricsListRequest`, route `GET /api/v3/Metrics` (FR-079-01, FR-079-02, S-079-06..08, S-079-10).

- [x] T-079-06 – `QueryLiveMetrics`: grouped subquery, joins, owner filter, cap, native date formatting (FR-079-03..07, NFR-079-01..04, S-079-01..05, S-079-09, S-079-11, S-079-12, S-079-15, S-079-16).
  _Verification commands:_ `php artisan test --filter=LiveMetricsListTest`

- [x] T-079-07 – `CleanupMetrics::apply()` / `threshold()`, used by v2 and v3; v2 `GetMetrics` retention filter (FR-079-08, S-079-13, S-079-14).
  _Verification commands:_ `php artisan test --filter=LiveMetricsListTest`, `php artisan test --filter=MetricsGetTest`

- [x] T-079-08 – `php artisan typescript:transform`; `vendor/bin/php-cs-fixer fix`; `make phpstan`.

- [x] T-079-09 – v8 drawer adoption: `MetricsService.getV3()`, `LiveMetrics.vue`, `statistics.metrics.truncated` in 23 locales, `php artisan lang:json` (FR-079-09, UI-079-01).
  _Verification commands:_ `npm run format`, `npm run check`

- [x] T-079-10 – Knowledge map, drift gate report in plan, roadmap status.

- [ ] T-079-11 – Manual drawer check in a browser (flag on/off, truncation line).

## Notes / TODOs
- Verified on SQLite only; MySQL/MariaDB and PostgreSQL (GROUP BY on the minute expression) are covered by CI.
