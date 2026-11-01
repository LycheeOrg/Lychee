# Feature Plan 106 – On This Day Index

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented  
_Last updated:_ 2026-11-01

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
On This Day reads only the photos of the day through `photos_month_day_key_index` (FR-106-01, FR-106-02, NFR-106-01). Every date write keeps the key current (FR-106-03), the migration fills it for existing photos (FR-106-04). Success: S-106-01 … 08 green, the SQLite plan names the index, and the scratch instance (50,000 photos) shows `Album::head?album_id=on_this_day` and the On This Day cover no longer scanning `photos`.

## Scope Alignment
- **In scope:** `App\Services\MonthDayKey`, migration (column, index, backfill), `OnThisDayAlbum`, the four write sites, `PhotoFactory`, `Photo` docblock, knowledge map.
- **Out of scope:** spec Non-Goals (Recent, random header pick, smart-album cover cache).

## Dependencies & Interfaces
- `photos.taken_key` (Feature 092): the backfill reads the local day where the photo was taken from it; `BucketKeys::takenKey()` formats the same cast `taken_at` the new helper reads.
- `Shared\Save`, `Duplicate\SaveIfDirty`, `PhotoController::update`, `lychee:takedate`: the places that already assign `taken_key`.
- `OptimizeTables::dropIndexIfExists()` for the index, as in `2026_10_12_000001_add_bucket_keys_to_photos`.

## Assumptions & Risks
- **Assumptions:** `taken_key` is set for every photo with a `taken_at` (assigned at every write since Feature 092, backfilled by its migration). The cast `taken_at` is in `taken_at_orig_tz`; `created_at` and `Carbon::today()` are in the application time zone.
- **Risks / Mitigations:** a single-column index misleading the MySQL planner (2022 migration) — the column is only compared for equality, never sorted on (NFR-106-02). A new photo has no `created_at` before `save()` — the `Save` pipe passes `now()` for a photo without one, the time Eloquent stamps it with.

## Implementation Drift Gate
2026-11-01, agent, after T-106-08 (all tasks checked, gate green).

| Requirement | Code | Tests / evidence |
|-------------|------|------------------|
| FR-106-01 | `App\Services\MonthDayKey`, `photos.month_day_key` + `photos_month_day_key_index` | `MonthDayKeyTest` 4/4 (red first) |
| FR-106-02 | `OnThisDayAlbum` smart condition | `OnThisDayAlbumTest` 5/5 (S-106-05 red first); `AlbumCategoryV3Test` 20/20, `AlbumHeadEndpointTest` 15/15 |
| FR-106-03 | `Shared\Save`, `Duplicate\SaveIfDirty`, `PhotoController::update()`, `Takedate`, `PhotoFactory` | `PhotoKeysWriteTest` 12/12 (6 new cases red first), `TakeDateTest` 2/2, `ImportFromServerTest` 6/6 |
| FR-106-04 | `2026_11_01_000001_add_month_day_key_to_photos` | Scratch copy, 50,000 photos (2,000 without taken date, 2,000 taken in Europe/Paris at 23:30 UTC): migration 84 ms, 0 rows without key, 0 keys differing from `MonthDayKey`, the 2,000 Paris photos on their local day |
| NFR-106-01 | – | SQLite plan: `SEARCH photos USING INDEX photos_month_day_key_index (month_day_key=?)`. Same data, old vs new condition: header `count` 20.8 → 3.8 ms, `OFFSET` 12.7 → 2.9 ms, `exists` 2.3 → 1.4 ms; `Album::head?album_id=on_this_day` 37–47 → 17–21 ms (SQL 22–35 → 6 ms); On This Day queries of `Albums/smart` 0.2–1 ms |
| NFR-106-02 | no `orderBy` on the column | code review |
| NFR-106-03 | per-driver backfill expressions (SQLite, PostgreSQL, MySQL/MariaDB) | SQLite run locally; PostgreSQL and MySQL/MariaDB through CI |

Commands rerun: `php artisan test --filter=` `MonthDayKeyTest`, `OnThisDayAlbumTest`, `AlbumCategoryV3Test`, `AlbumHeadEndpointTest`, `OverridePermissionsTest`, `CoverageTest`, `SmartAlbumTypeTest`, `RecomputeAlbumUserThumbsJobTest`, `AlbumFactoryTest`, `SetSmartProtectionPolicyTest`, `PhotoKeysWriteTest`, `TakeDateTest`, `ImportFromServerTest`, `Photo360UploadTest`, `Photo360EditTest`, `PhotoTitleSyncTest`, `PhotoSortingBucketDispatchTest`, `TimelineAlbumTest` (all green); `php-cs-fixer` on the 13 touched files (0 fixed); `make phpstan` (no errors). Scratch scripts: `onthisday/prepare.php`, `verify.php`, `ab.php`; `cachecheck/head.php`, `smart.php`.

Findings: `AlbumCategoryV3Test` fixtures moved `taken_at` with raw SQL and now set `taken_key` and `month_day_key` with it. The remaining cold cost of `Albums/smart` on the scratch data is Untagged (128 ms), Recent (120 ms) and Unsorted (53 ms), each scanning `photos` (Non-Goals, follow-up below).

## Increment Map
1. **I1 – Key helper** (T-106-01, T-106-02)
   - _Steps:_ failing `MonthDayKeyTest`, then `App\Services\MonthDayKey`.
   - _Commands:_ `php artisan test --filter=MonthDayKeyTest`, `make phpstan`.
   - _Exit:_ green, phpstan clean.
2. **I2 – Schema** (T-106-03)
   - _Steps:_ migration (column, index, two backfill `UPDATE`s per driver), `PhotoFactory` `afterMaking`, `Photo` docblock.
   - _Commands:_ `php artisan test --filter=PhotoKeysWriteTest` (migrates the test database), `make phpstan`.
   - _Exit:_ test database migrated, existing tests green.
3. **I3 – On This Day query** (T-106-04, T-106-05)
   - _Steps:_ failing `OnThisDayAlbumTest` (S-106-05 fails on the current UTC-day query), then `OnThisDayAlbum` on `month_day_key`.
   - _Commands:_ `php artisan test --filter=OnThisDayAlbumTest`, `php artisan test --filter=AlbumHeadEndpointTest`, `php artisan test --filter=AlbumCategoryV3Test`.
   - _Exit:_ green.
4. **I4 – Write sites** (T-106-06, T-106-07)
   - _Steps:_ failing `PhotoKeysWriteTest` cases (upload, edit taken date, reset, edit upload date, takedate), then the four write sites.
   - _Commands:_ `php artisan test --filter=PhotoKeysWriteTest`, `php artisan test --filter=TakeDateTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Exit:_ green.
5. **I5 – Verification and docs** (T-106-08)
   - _Steps:_ knowledge map, roadmap; scratch instance: migrate a copy, `EXPLAIN QUERY PLAN`, timings before/after.
   - _Exit:_ index used, timings recorded in the drift gate.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-106-01 … 05 | I3 / T-106-04, T-106-05 | `OnThisDayAlbumTest` (time frozen) |
| S-106-06 | I4 / T-106-06, T-106-07 | `PhotoKeysWriteTest` upload |
| S-106-07 | I4 / T-106-06, T-106-07 | `PhotoKeysWriteTest` edit |
| S-106-08 | I4 / T-106-06, T-106-07 | `PhotoKeysWriteTest` takedate |

## Analysis Gate
2026-11-01, agent:
- Specification completeness: pass — FR-106-01 … 04, NFR-106-01 … 03, S-106-01 … 08; no UI change (no mock-up).
- Open questions: pass — Q-106-01 resolved A; no ADR (one derived column, same pattern as `taken_key`).
- Plan alignment: pass — success criteria match the Goals.
- Tasks coverage: pass — every FR has a task, tests staged before code (T-106-01, 04, 06).
- Working agreements: pass — explicit writes, no model events; migration approved by the owner (option C); no dependency; no migration test (backfill checked on the scratch instance).
- Tooling: pass — commands per increment.

## Follow-ups / Backlog
- Untagged, Recent and Unsorted still scan `photos` for their cover and header (`Albums/smart` cold, `Album::head`); empty smart albums recompute their cover on every request (`CachesAlbumUserThumb` caches only a photo).

## Exit Criteria
- T-106-01 … 08 checked; listed test classes green; `php-cs-fixer`, `make phpstan` clean.
- Knowledge map and roadmap updated, drift gate recorded.
