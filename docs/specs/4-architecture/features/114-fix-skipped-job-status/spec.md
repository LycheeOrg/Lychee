# Feature 114 – Fix Skipped Job Status

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-15 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #114 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
With an asynchronous queue, a photo skipped as a duplicate (`PhotoSkippedException`, `PhotoResyncedException`) makes `ImportImageJob` / `ProcessImageJob` throw. The Docker worker (`queue:work --tries=3`) prints `FAIL` three times per duplicate, `HasFailedTrait::failed()` logs an `error` with the full trace, Laravel stores a `failed_jobs` row, and `JobHistory` records `FAILURE`. A skip is the expected result of `skip_duplicates`, not an error: a user reading `docker compose logs -f` during a directory import took an hour to find that out. Layers: queue jobs, `JobHistory`, admin Jobs page (v7/v8), admin dashboard stats.

## Goals
- A duplicate skip on an asynchronous queue ends the job normally: one attempt, no `failed_jobs` row, no `error` log entry, no stack trace; the worker line reads `DONE`.
- `JobHistory` records a new `skipped` status for it, on every queue, shown as such on the admin Jobs page and not counted as a failure on the admin dashboard.
- The `sync` queue keeps today's caller-visible behaviour: the upload answers `409` and the import report lists the skip.

## Non-Goals
- Overriding or extending Laravel's `queue:work` command or its output (ADR-114-01).
- Changing duplicate detection or the `skip_duplicates` / `shall_resync_metadata` rules.
- Changing how other job failures are reported or retried.
- Changing the upload front end's handling of `409`.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-114-01 | On an asynchronous queue (the job runs with a queue job that is not a `SyncJob`), `ImportImageJob` and `ProcessImageJob` treat `PhotoSkippedException` and its subclasses as a terminal, non-failing outcome. | `handle()` records the skip (FR-114-03) and returns `null`: Laravel deletes the job after one attempt, `failed()` is not called, no `failed_jobs` row; `queue:work` prints `DONE`. | — | Any other exception keeps today's path (retries, `failed()`, `FAILURE`, `error` log). | One `info` line on the `jobs` channel (see Telemetry). | User report 2026-11-15; Q-114-01 B, ADR-114-01 |
| FR-114-02 | On the `sync` queue, or when `handle()` runs without a queue job, the skip still reaches the caller. | `handle()` records the skip (FR-114-03) then rethrows: upload answers `409`; `ExecuteBatch` reports the skip in the import output. | — | — | Same `info` line; no `error` log entry, no trace. | Existing upload/import contract |
| FR-114-03 | A skipped job records `JobStatus::SKIPPED` (`4`, name `skipped`) in its `JobHistory`, on every queue. | `handle()` saves `SKIPPED` before returning or rethrowing; `HasFailedTrait::failed()` given a `PhotoSkippedException` saves `SKIPPED` and logs nothing. | — | `failed()` given any other exception keeps `FAILURE` + `error` log. | — | Q-114-02 A |
| FR-114-04 | The admin Jobs page (v7 and v8) shows `skipped` jobs with their own label and colour. | Status column shows "Skipped" in the muted colour; the status meter gets a "Skipped — n" segment. | — | — | — | Q-114-02 A |
| FR-114-05 | The admin dashboard does not count skipped jobs as failures. | `AdminStatsService::failed_jobs_24h` counts `FAILURE` only (unchanged query), so `SKIPPED` rows are excluded. | — | — | — | Q-114-02 A |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-114-01 | A duplicate costs one job attempt, not `--tries`. | Large re-imports of mostly-known directories. | Test with a fake queue job asserts no exception, not failed, not released. | `docker/scripts/entrypoint.sh` (`--tries=3`) | User report 2026-11-15 |
| NFR-114-02 | No change to Laravel framework classes or command bindings. | Upgrade safety. | Drift gate: no subclass or binding of `WorkCommand`. | — | Q-114-01 B, ADR-114-01 |
| NFR-114-03 | Every `lang/*/jobs.php` carries `skipped`. | Translation parity. | `LangTest`. | — | Coding conventions |

## UI / Interaction Mock-ups

Admin Jobs page (v7 and v8), status meter and list after a re-import with duplicates:

```
┌──────────────────────────────────────────────────────────────────────┐
│ Jobs                                                                 │
│ ████████████████████████░░░░░░░░░░░░░░░░██                           │
│ ● Success — 12   ● Skipped — 8   ● Failure — 1                       │
│                  (muted)          (red)                              │
├──────────────────────────────────────────────────────────────────────┤
│ 2026-11-15 10:02  Skipped   admin  Process Image: IMG_0001.jpg ...   │
│ 2026-11-15 10:02  Success   admin  Process Image: IMG_0002.jpg ...   │
│ 2026-11-15 10:01  Failure   admin  Process Image: broken.jpg ...     │
└──────────────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-114-01 | Async `ImportImageJob`, duplicate with `skip_duplicates=true`: no exception, job not failed nor released, history `SKIPPED`, `info` skip line. |
| S-114-02 | Async `ImportImageJob`, duplicate with resync metadata (`PhotoResyncedException`): same as S-114-01. |
| S-114-03 | Async `ProcessImageJob` (web upload), duplicate: same as S-114-01. |
| S-114-04 | `sync` upload of a duplicate: `409` as today, history `SKIPPED`. |
| S-114-05 | `sync` `ImportImageJob` of a duplicate: `PhotoSkippedException` reaches the caller, history `SKIPPED`. |
| S-114-06 | `failed()` with another exception: history `FAILURE` (unchanged). `failed()` with `PhotoSkippedException`: history `SKIPPED`. |
| S-114-07 | Admin dashboard: a `SKIPPED` history row in the last 24 h is not counted in `failed_jobs_24h`. |
| S-114-08 | Jobs page shows `skipped` with its label and colour (v7, v8). |

## Test Strategy
- **Jobs (ImageProcessing suite):** new `tests/ImageProcessing/Jobs/SkippedDuplicateJobTest.php` runs the real jobs with `withFakeQueueInteractions()` for the async path (S-114-01…03) and `dispatchSync` / upload for the sync path (S-114-04, S-114-05).
- **Unit:** `ImportImageJobTest` covers `failed()` branches (S-114-06); `CoverageTest` covers `JobStatus::SKIPPED->name()`; `AdminStatsServiceTest` covers S-114-07.
- **REST API (v2):** `UploadWithMetadataTest` keeps asserting `409` for a sync duplicate.
- **Frontend (v7/v8):** status label/colour mapping covered by typecheck and manual check (S-114-08); no existing Jobs page unit test.
- **Docs/Contracts:** `JobHistoryResource` literal type and `JobStatus` regenerate into `resources/js/lychee.d.ts`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-114-01 | `JobStatus::SKIPPED = 4`, `name()` → `skipped`. | `App\Enum\JobStatus`, `JobHistory` |
| DO-114-02 | `JobHistoryResource::$status` literal type `'ready'\|'success'\|'failure'\|'started'\|'skipped'`. | `App\Http\Resources\Models\JobHistoryResource`, `lychee.d.ts` |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-114-01 | Skipped job row | `status === "skipped"`: label `jobs.skipped`, muted text colour, muted meter segment. |

## Telemetry & Observability
- Skip: one `info` line on the `jobs` channel, `Skipped: <file base name> (<exception message>)`, no trace. The message tells the reason apart (`The photo has been skipped`, `…but its metadata has been updated`, `…there is already a copy waiting for moderation`).
- `queue:work` prints Laravel's `DONE` for a skipped job (ADR-114-01).
- Other failures: unchanged.

## Documentation Deliverables
- Roadmap row 114.
- Knowledge map: duplicate-skip job outcome next to the `ImportImageJob` / `ProcessImageJob` entry.
- ADR-114-01.

## Fixtures & Sample Data
- Reuse `tests/Samples/night.jpg` (`TestConstants::SAMPLE_FILE_NIGHT_IMAGE`); no new fixture.
