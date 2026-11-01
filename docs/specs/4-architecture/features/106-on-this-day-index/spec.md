# Feature 106 – On This Day Index

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-01 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #106 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
The On This Day smart album (`OnThisDayAlbum`) selects photos with `MONTH(taken_at) = m AND DAY(taken_at) = d`, or for photos without `taken_at` the same on `created_at` before the current year. No index can serve these predicates, so every On This Day query reads the whole `photos` table and runs the viewer's visibility filter on the candidates: the album listing cover (`GET /api/v3/Albums/smart`), `Album::head` (frame check, header `exists`, header `count` + `OFFSET`), and the photo listing. On a production instance `Album::head?album_id=on_this_day` takes 10 s and `Albums/smart` up to 7 s, on the first request as on every later one. A stored, indexed month-day key on `photos` lets these queries read only the photos of the day.

## Goals
- On This Day queries read the matching photos through an index instead of scanning `photos`.
- Every write of `taken_at` or `created_at` on a photo keeps the key current.
- Existing photos get their key when the migration runs.

## Non-Goals
- Recent (`created_at >= …`, no index on `photos.created_at`) and the other smart albums.
- The random header pick (`HasHeaderUrl`: `count` + `OFFSET`) and the smart-album cover cache: unchanged, they become cheap because their On This Day query is indexed.
- Which photos On This Day shows, beyond the day rule of FR-106-01.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-106-01 | `photos.month_day_key` (unsigned small integer, `month × 100 + day`, nullable) holds the month and day where the photo was taken (the date of `taken_key`: `taken_at` in `taken_at_orig_tz`) when `taken_at` is set, else the month and day of `created_at` as stored. Index `photos_month_day_key_index`. | Taken 2026-03-07 → `307`; taken 2026-11-02 00:30 in Europe/Paris (stored 2026-11-01 23:30 UTC) → `1102`. | – | – | – | Owner choice (index the day, option C), Q-106-01 A |
| FR-106-02 | `OnThisDayAlbum` selects `photos.month_day_key = <today>` and (`photos.taken_at IS NOT NULL` or `photos.created_at < <1 January of the current year>`), today in the application time zone. | Members follow the day rule of FR-106-01. | – | – | – | Owner choice |
| FR-106-03 | The key is assigned explicitly next to every write of `taken_at` or `created_at`: the shared `Save` pipe and `Duplicate\SaveIfDirty` (upload, import, re-upload), `PhotoController::update` (taken date, upload date), `lychee:takedate`. No model event or mutator. | Key follows the edited date. | – | – | – | Owner directive (explicit writes) |
| FR-106-04 | A migration adds the column and index and fills the key of every existing photo, from `taken_key` when it is set and from `created_at` otherwise: two `UPDATE` statements with driver-specific expressions, only on rows whose key is `NULL`. | Every photo has a key after migrating. | – | – | – | Owner choice |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-106-01 | On This Day queries use `photos_month_day_key_index`; `Album::head?album_id=on_this_day` and `Albums/smart` no longer grow with the size of the library. | 10 s / 7 s in production. | SQLite `EXPLAIN QUERY PLAN` names the index; scratch instance with 50,000 photos timed before and after. | – | Owner report |
| NFR-106-02 | The column is only ever read by an equality predicate, never sorted on, so it cannot draw the MySQL planner away from the `(album_id, …)` indices (rationale of `2022_04_06_091900_drop_objectionable_indices`). | Planner stability. | Code review: no `orderBy` on the column. | – | – |
| NFR-106-03 | MySQL/MariaDB, PostgreSQL and SQLite. | Supported databases. | CI matrix. | – | – |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-106-01 | Photo taken on today's month and day in an earlier year → listed. |
| S-106-02 | Photo taken on another day → not listed. |
| S-106-03 | Photo without taken date uploaded on today's month and day in an earlier year → listed; uploaded on today's date this year → not listed. |
| S-106-04 | Photo taken on another day but uploaded on today's month and day → not listed (the taken date wins). |
| S-106-05 | Photo taken shortly after midnight local time on today's date in a zone east of UTC (stored on the previous day in UTC) → listed today, not on the previous day. |
| S-106-06 | Upload or import with and without an EXIF date → key set from the taken date, else the upload date. |
| S-106-07 | Edit the taken date, or reset it to the initial one → key follows; edit the upload date of a photo without taken date → key follows. |
| S-106-08 | `lychee:takedate` rewrites `taken_at` or `created_at` → key follows. |

## Test Strategy
- **Unit:** the key helper (dates in and out of UTC, leap day, no taken date).
- **REST (Feature_v3):** On This Day membership S-106-01 … 05 through the v3 photo listing; S-106-07 through `PATCH Photo`.
- **Actions / commands:** S-106-06 through the upload pipeline, S-106-08 through `lychee:takedate` (existing test classes extended).
- **Migration:** no test of its own; the backfill is checked on the scratch instance.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-106-01 | `photos.month_day_key` (`unsigned smallint`, nullable), index `photos_month_day_key_index` | models, migrations |

## Documentation Deliverables
- Knowledge map: `photos.month_day_key` next to `taken_key`.
- Roadmap row.
