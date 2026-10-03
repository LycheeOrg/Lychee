# Feature 079 – Live Metrics Struct-of-Arrays

| Field | Value |
|-------|-------|
| Status | Testing |
| Last updated | 2026-10-03 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | 079 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

`GET /api/v2/Metrics` feeds the v8 live-metrics drawer. It hydrates every `live_metrics` row as an Eloquent model, eager-loads five relations (`photo`, `photo.size_variants`, `album`, `album_impl`, `album.thumb`), builds a thumbnail URL for each row and deletes expired rows before reading. `GET /api/v3/Metrics` returns the same feed as a Struct-of-Arrays (ADR-0009), pre-grouped per minute in SQL, capped, from a single `toBase()` query: no hydration, no size-variant join, no URL generation. Thumbnails are photo IDs fetched through the v3 Asset endpoint. When `is_struct_of_array_enabled` is on, the v8 drawer reads it. Speed is the primary goal.

Affected layers: REST API v2 (cleanup setting, retention filter) and v3 (new route), `app/Actions/Metrics`, configs and migrations, v8 drawer, locale files.

## Goals

- G1: `GET /api/v3/Metrics` returns the live-metrics feed as parallel arrays from one SELECT.
- G2: The same visible events and the same authorisation as v2: owner/admin scoping, no photo `visit` events, no tag albums.
- G3: The response size is bounded by an admin setting (ADR-069-01, strategy 3).
- G4: Deleting expired rows no longer has to block the read.
- G5: The v8 drawer uses the v3 endpoint when `is_struct_of_array_enabled` is on, with no visible change except a truncation line.

## Non-Goals

- NG1: No change to the v2 response shape or to the metrics write path (`Metrics::photo`, `Metrics::favourite`, events, listener). v2 changes only in FR-079-08 (cleanup setting and retention filter).
- NG2: No change to which events are recorded or to `live_metrics_max_time`.
- NG3: No thumbnail URL in the response: clients resolve thumbs through `GET /api/v3/Asset/{album_id}/{photo_id}/thumb` (Feature 056, ADR-0008), as the v3 album grid does.
- NG4: v7 has no live-metrics drawer; no v7 change. The SE-preview branch of the drawer (random data built from `AlbumService.getAll()`) is unchanged.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-079-01 | `GET /api/v3/Metrics` returns a `LiveMetricsListResource` (DO-079-01). | 200 with parallel arrays ordered by `created_ats` DESC. | No request parameters. | — | None. | Owner request 2026-10-03; ADR-0009. |
| FR-079-02 | Authorisation matches v2, plus the v3 SoA flag. | Allowed when `features.struct-of-array` is `true` and `MetricsPolicy::CAN_SEE_LIVE` passes. | — | 401 for guests. 403 when the flag is off, the policy denies, or `live_metrics_enabled` is `false` (as v2). `support:se` middleware (as v2). | None. | v2 `MetricsController::get()`; v3 FormRequest convention. |
| FR-079-03 | Row selection matches v2 `GetMetrics`. | Non-admins see events on albums they own (`base_albums.owner_id`) or photos they own (`photos.owner_id`); admins see all. Photo `visit` events are excluded. Events whose `album_id` is not a regular album (tag album, `NULL`) are excluded by an inner join on `albums`. Events older than `live_metrics_max_time` days are excluded (FR-079-08). | — | — | None. | v2 `GetMetrics::get()`. |
| FR-079-04 | `titles[i]` is the photo title for photo events (empty string for an untitled photo), else the album title, unescaped. | Raw title string. The choice follows `photo_id`, never the title's nullability: a photo owner must not receive the title of an album they do not own. | — | — | None. | The v8 drawer escapes through `textContent` (`titlize()`). |
| FR-079-05 | `thumb_photo_ids[i]` is the photo shown as the event thumbnail. | Photo events: `photo_id`. Album events: `albums.cover_id ?? albums.auto_cover_id_max_privilege`; album rows are only visible to the album owner or an admin (FR-079-03), for whom max-privilege is the right cover. | — | `null` when the album has no cover. | None. | NG3; `AlbumListController::rawCoverId()`. |
| FR-079-06 | Events are grouped in SQL by `(action, album_id, photo_id, created_at truncated to the minute)`. | One output row per group: `counts[i]` is the number of events in it; `created_ats[i]` is the latest event in it. The minute expression is per driver: SQLite `strftime('%Y-%m-%d %H:%M', …)`, MySQL/MariaDB `DATE_FORMAT(…, '%Y-%m-%d %H:%i')`, PostgreSQL `to_char(…, 'YYYY-MM-DD HH24:MI')`. | — | Unsupported driver → `LycheeInvalidArgumentException` (same rule as `QueryPhotoBuckets`). | None. | Q-079-02 (Option B). |
| FR-079-07 | Capped with truncation (ADR-069-01, strategy 3). | The query fetches `cap + 1` groups, newest first; the response holds the first `cap` and `is_truncated = true` when a group was dropped. `cap` is the setting `live_metrics_result_limit` (default `1000`, positive integer, category `Mod Pro`, level 1). | — | — | None. | Q-079-03 (Option A). |
| FR-079-08 | Expired-row cleanup is an admin setting, `live_metrics_cleanup` = `deferred` \| `sync` \| `disabled` (default `deferred`, category `Mod Pro`, level 1), applied by both the v2 and the v3 GET after authorisation. | `sync`: `CleanupMetrics::do()` runs before the read (current v2 behaviour). `deferred`: it is registered with `app()->terminating()`, so it runs after the response is sent. `disabled`: no delete. Both reads filter `live_metrics.created_at > now − live_metrics_max_time days`, so expired rows are never returned whatever the mode. | Invalid value rejected by the config type range. | — | None. | Q-079-04 (owner option). |
| FR-079-09 | The v8 drawer adopts the endpoint. | When `is_struct_of_array_enabled` is on, `LiveMetrics.vue` calls `MetricsService.getV3()`. Groups are merged into the existing relative-time labels by the existing key `(action, ago, photo_id ?? album_id)`, adding `counts[i]` instead of 1. The thumbnail is `<Thumb :album-id="album_ids[i]" :photo-id="thumb_photo_ids[i]" type="thumb">`. When `is_truncated`, a line `statistics.metrics.truncated` is shown under the list. With the flag off, the drawer keeps calling v2. | — | Request error: logged to the console (as v2). | None. | Q-079-01 (Option A). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-079-01 | One SELECT for the listing; no Eloquent hydration, no eager loads, no URL signing. | Speed. | Feature test counts `select` statements on `live_metrics` with `DB::listen()`. | `toBase()`. | Owner ("speed is of essence"). |
| NFR-079-02 | No Carbon in the v3 request path: `created_ats[]` built with `date('c', strtotime($raw))` from the raw DB string, same output as v2's `toIso8601String()`; the retention threshold built with `date()`/`strtotime()`. | Per-row cost. | Feature test asserts the ISO 8601 format. | — | Owner preference (no Carbon on new server paths). |
| NFR-079-03 | No `visitor_id` in the response. | Privacy, payload size. | Resource has no such field. | — | The drawer never reads it. |
| NFR-079-04 | Works on SQLite, MySQL/MariaDB and PostgreSQL. | Supported drivers. | CI matrix. | FR-079-06 driver expressions. | Project baseline. |
| NFR-079-05 | Index on `live_metrics.created_at`. | The retention filter and the cleanup become range scans. | Migration present; tests run on the migrated schema. | New migration. | Q-079-05 (Option A). |
| NFR-079-06 | The two new settings are in `ConfigIntegrity::SE_FIELDS` and have entries in all 23 `lang/<locale>/all_settings.php` files (English text in non-English locales, as for recent settings); the new drawer string is in all 23 `lang/<locale>/statistics.php`; `lang/*.json` regenerated with `php artisan lang:json`. | Consistency. | `ConfigIntegrityTest`; `npm run check`. | — | Project conventions. |

## UI / Interaction Mock-ups

The drawer is unchanged except for the truncation line, which shows only when `is_truncated`:

```
┌─ Live metrics ──────────────── ✕ ┐
│ 3 visitors downloaded "Sunset" ▢ │
│ 2 hours ago                      │
│ A visitor viewed "Holidays"    ▢ │
│ a day ago                        │
│ ...                              │
│ ──────────────────────────────── │
│ Older activity is not shown.     │  ← only when is_truncated
└──────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-079-01 | Admin: sees album and photo events of every owner, newest first. |
| S-079-02 | Non-admin owner: sees only events on their own albums or photos. |
| S-079-03 | Photo `visit` events are absent; album `visit` events are present. |
| S-079-04 | An event on a tag album is absent. |
| S-079-05 | Photo event: `thumb_photo_ids[i] === photo_ids[i]`. Album event: `cover_id`, else max-privilege auto cover, else `null`. |
| S-079-06 | `features.struct-of-array` off → 403. |
| S-079-07 | `live_metrics_access = admin`, non-admin user → 403. |
| S-079-08 | `live_metrics_enabled = false` → 403; guest (with `live_metrics_access = logged-in users`) → 401. |
| S-079-09 | A title containing `<b>&` is returned unescaped. |
| S-079-17 | An untitled photo owned by the viewer, in an album they do not own → `titles[i] === ''`, never the album title. |
| S-079-10 | Empty table → every array empty, `is_truncated = false`. |
| S-079-11 | Three `download` events on one photo within one minute → one row with `counts = 3`; the same event one minute later → a separate row. |
| S-079-12 | `live_metrics_result_limit = 2` with three groups → two newest groups, `is_truncated = true`; with exactly two groups → `is_truncated = false`. |
| S-079-13 | An event older than `live_metrics_max_time` days is never returned, by v2 or v3, in every cleanup mode. |
| S-079-14 | `live_metrics_cleanup = sync` and `deferred` → the expired row is deleted once the request completes; `disabled` → it is still in the table. |
| S-079-15 | `created_ats[i]` is ISO 8601 with offset. |
| S-079-16 | The listing runs exactly one SELECT on `live_metrics`. |

## Test Strategy

- **REST API (v3):** `tests/Feature_v3/Metrics/LiveMetricsListTest.php` (base `Tests\Feature_v3\Base\BaseApiWithDataTest`) covering S-079-01..16. Events are inserted with `DB::table('live_metrics')->insert()` at fixed `created_at` values so grouping is deterministic.
- **REST API (v2):** `tests/Feature_v2/Metrics/MetricsGetTest.php` stays green; S-079-13/14 for v2 are covered by one extra case in the new v3 test class calling the v2 route through the inherited `getJson()` (no new Feature_v2 test class).
- **Unit:** `tests/Unit/Middleware/ConfigIntegrityTest.php` stays green with the two new SE settings.
- **Frontend (v8):** `npm run check`, `npm run format`; manual drawer check.
- **Docs/Contracts:** `php artisan typescript:transform` for `App.Http.Resources.V3.LiveMetricsListResource` and `App.Enum.LiveMetricsCleanup`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-079-01 | `App\Http\Resources\V3\LiveMetricsListResource`: `created_ats: string[]`, `actions: MetricsAction[]`, `album_ids: string[]`, `photo_ids: (string\|null)[]`, `titles: string[]`, `thumb_photo_ids: (string\|null)[]`, `counts: int[]`, `is_truncated: bool`. | REST v3, resources |
| DO-079-02 | `App\Enum\LiveMetricsCleanup`: `deferred`, `sync`, `disabled`. | enums, configs |
| DO-079-03 | Settings `live_metrics_result_limit` (positive int, default `1000`) and `live_metrics_cleanup` (`deferred\|sync\|disabled`, default `deferred`), category `Mod Pro`, level 1. | configs, migrations |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-079-01 | REST GET `/api/v3/Metrics` | Live-metrics feed, SoA, grouped per minute, capped. | `App\Http\Controllers\Gallery\LiveMetricsListController::index`, `App\Http\Requests\Metrics\GetLiveMetricsListRequest`, action `App\Actions\Metrics\QueryLiveMetrics`. Middleware `support:se`. |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-079-01 | Truncated feed | `is_truncated = true` → line "Older activity is not shown." under the list. |

## Telemetry & Observability

None.

## Documentation Deliverables

- Roadmap row for Feature 079.
- Knowledge map: `QueryLiveMetrics`, `LiveMetricsListResource`, `LiveMetricsCleanup`.
- ADR: none (applies ADR-0009 and ADR-069-01).

## Spec DSL

```
domain_objects:
  - id: DO-079-01
    name: LiveMetricsListResource
    fields:
      - { name: created_ats, type: "string[]" }
      - { name: actions, type: "MetricsAction[]" }
      - { name: album_ids, type: "string[]" }
      - { name: photo_ids, type: "(string|null)[]" }
      - { name: titles, type: "string[]" }
      - { name: thumb_photo_ids, type: "(string|null)[]" }
      - { name: counts, type: "int[]" }
      - { name: is_truncated, type: bool }
  - id: DO-079-02
    name: LiveMetricsCleanup
    values: [deferred, sync, disabled]
  - id: DO-079-03
    name: settings
    keys:
      - { key: live_metrics_result_limit, type: positive, default: 1000 }
      - { key: live_metrics_cleanup, type: "deferred|sync|disabled", default: deferred }
routes:
  - id: API-079-01
    method: GET
    path: /api/v3/Metrics
ui_states:
  - id: UI-079-01
    description: Truncation line under the drawer list
```
