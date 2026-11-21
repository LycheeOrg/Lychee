# Feature 116 – Upload Status Query

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-18 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #116 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

With a queue worker, `POST /api/v2/Photo` answers `READY` with a provisional ID (`expected_id`) before the photo is processed. The client cannot learn the outcome: a new photo takes the provisional ID, but a duplicate ends under the existing photo's ID, and a skipped duplicate disappears. Clients poll whole album listings with arbitrary timeouts and upload one file at a time to stay unambiguous ([discussion #4991](https://github.com/LycheeOrg/Lychee/discussions/4991)).

Every single-file upload records its provisional ID and status in the cache for one day, `ProcessImageJob` writes the outcome there when it finishes, and a new endpoint reads the entry back by provisional ID (owner directive, 2026-11-18). Layers: upload controller, `ProcessImageJob` and its traits, duplicate pipes (to carry the duplicate's ID), one new REST v2 route, one resource, one enum, generated TS types.

## Goals

- G1: a client can upload a whole set without waiting, then learn each file's final outcome from its provisional ID.
- G2: the outcome distinguishes the six cases of the discussion: in progress, completed, completed as an existing duplicate, skipped (not added), failed, no information.
- G3: the duplicate's ID is given when the caller may see that photo, and withheld otherwise.
- G4: the same contract on every queue (`sync` and asynchronous).

## Non-Goals

- N1: ZIP uploads (`ExtractZip`): they have no provisional ID and produce several photos.
- N2: `Photo::fromUrl`, server import, `lychee:sync`: no provisional ID.
- N3: persistence beyond the cache (no table, no history); one day is the retention.
- N4: changing the upload response or the `409` on `sync` for skipped duplicates.
- N5: `JobHistory` status of `PhotoRejectedException` on asynchronous queues (still `FAILURE` with retries, Feature 114 scope).
- N6: front-end changes. The v7/v8 upload panels keep showing `READY` for queued files; showing final outcomes there is a follow-up feature (Q-116-04 A).

## Status Vocabulary

| `status` | Meaning | `photo_id` |
|----------|---------|------------|
| `processing` | Last chunk received, job queued, running, or released for a retry. | `null` |
| `completed` | A photo was created (or a live-photo video was joined to its existing still) and is in the target album. | The resulting photo: the provisional ID, or the still's ID for a joined video. |
| `duplicate` | The same file already existed; that photo is now in the target album. | The existing photo, unless deleted since (FR-116-07). |
| `skipped` | Not added to the target album: `skip_duplicates`, metadata resync (`PhotoResyncedException`), a copy waiting for moderation, or `skip_duplicates_not_owned` (`PhotoRejectedException`). | The existing photo, unless deleted since (FR-116-07). |
| `failed` | Processing failed after its last attempt. | `null` |
| `unknown` | No entry: expired, never existed, ZIP upload, or not readable by the caller (FR-116-06). | `null` |

Clients record `photo_id`, never assume it equals the provisional ID. `message` is filled on `skipped` and `failed` only (FR-116-04, FR-116-05).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-116-01 | The last chunk of a non-ZIP upload writes an entry `{status: processing}` under its provisional ID, with the uploader's user ID (`null` for a guest), for one day. | Entry readable as `processing` right after the upload answers. | ZIP uploads write nothing. | A cache write error is logged at `warning` and does not fail the upload (NFR-116-03). | None new. | Owner directive 2026-11-18. |
| FR-116-02 | When `ProcessImageJob` creates a photo, the entry becomes `completed` with the resulting photo ID. | New photo: `photo_id` = provisional ID. Live-photo video joined to an existing still: `photo_id` = the still. | Jobs without a provisional ID write nothing. | As FR-116-01. | None new. | Discussion #4991, case 2. |
| FR-116-03 | When the file is an existing duplicate linked into the target album (`skip_duplicates` off), the entry becomes `duplicate` with the existing photo's ID. | Includes a duplicate already in the target album. | — | As FR-116-01. | None new. | Discussion #4991, case 3. |
| FR-116-04 | When the duplicate is skipped or rejected, the entry becomes `skipped` with the existing photo's ID and `message` = the exception's message ("The photo has been skipped", "…but its metadata has been updated", "…there is already a copy waiting for moderation", "The photo has been rejected"). | `PhotoSkippedException`, `PhotoResyncedException` and `PhotoRejectedException` carry the existing photo's ID to the job. | — | As FR-116-01. | Existing `jobs` channel `info` line unchanged. | Discussion #4991, case 4. |
| FR-116-05 | When the job fails for good (`failed()`), the entry becomes `failed`. `message` is the exception's message when it extends `BaseLycheeException` (quota, unsupported media, …), otherwise the fixed `"Processing failed"`; internal messages stay in the logs. | A release for retry (code 999, attempts left) keeps `processing`. | — | As FR-116-01. | Existing `jobs` channel `error` line unchanged. | Discussion #4991, case 5. |
| FR-116-06 | `GET /api/v2/Photo::uploadStatus?photo_ids[]=…` (1 to 100 provisional IDs) answers a list in request order, one `UploadStatusResource` per ID. An entry with an uploader ID answers only to that authenticated user; a guest entry (`uploader_id` `null`) answers to any caller. Otherwise, or without an entry: `unknown`. | `200` with the list. Repeated IDs answer once per occurrence. | `422` on a malformed ID (not a 24-character random ID), an empty list or more than 100. | — | None new. | Owner directive 2026-11-18, Q-116-01 A, Q-116-02 A. |
| FR-116-07 | `photo_id` is returned as recorded, with no visibility check: the caller uploaded the same file, so the ID reveals nothing the `duplicate`/`skipped` status has not already told them, and every photo route still applies its own policy. Only a photo deleted since answers `null`. | Uploader's own photo, or the existing photo of another user (duplicate): its ID. | — | Photo deleted since: `null`. | — | Discussion #4991, case 4 note; owner directive 2026-11-18 (per-photo `CAN_SEE` too heavy, and the ID is not sensitive). |
| FR-116-08 | Each write resets the one-day expiry. | — | — | — | — | Owner directive 2026-11-18. |
| FR-116-09 | The `sync` queue writes the same entries. | Upload answers `DONE` (or `409`); the entry already holds the outcome. | — | — | — | G4. |
| FR-116-10 | When the entries live in a `database` cache store, writing a `processing` entry deletes expired upload status rows with 1-in-50 odds (key prefix match, `expiration` below now). Other stores expire entries themselves. | The `cache` table holds about one day of upload statuses. | — | A delete error is caught and logged like a write error (NFR-116-03). | None new. | Q-116-05 C. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-116-01 | The query reads the cache only, plus one primary-key lookup on `photos` for all non-null `photo_id`s to drop deleted photos. No model hydration, no relation, no policy check, no job table or photo listing scan: the query count does not depend on the batch size or on who owns the photos. | Polling cost. | Query-count assertions in the feature test (own photos, and photos of other users). | Laravel cache. | G1; owner directive 2026-11-18. |
| NFR-116-02 | The web container and the worker read and write the same entries, including with the default LycheeOrg compose file (`CACHE_STORE=file`, separate worker container). Entries live in the default cache store, except when `queue.default` is not `sync` and the default store's driver keeps its data in one container (`file`, `array`, `apc`): then in the `database` store. The driver list has one definition, shared with `WorkerCacheCheck` (DO-116-05). | Docker worker mode. | Unit test of the store choice over (queue, driver); `WorkerCacheCheckTest` unchanged. | ADR-090-01 (`database` store, `cache` table), `WorkerCacheCheck`. | Q-116-05 C, ADR-116-01. |
| NFR-116-03 | Status recording never changes the upload's or the job's outcome: cache errors are caught and logged. | Status is secondary to processing. | Unit test with a throwing cache store. | — | — |
| NFR-116-04 | An unreadable entry and a missing entry give the same answer. | No existence oracle. | Feature test across two users. | — | Q-116-01 A. |
| NFR-116-05 | Outcome-to-entry mapping is a small pure helper; jobs and controller call it without inline branching. | Straight-line increments. | Unit test on the helper. | — | AGENTS.md. |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-116-01 | Async upload, before the worker runs: `processing`. |
| S-116-02 | Async upload, new photo: `completed`, `photo_id` = provisional ID. |
| S-116-03 | Async upload of a duplicate from another album (`skip_duplicates` off): `duplicate`, `photo_id` = existing photo. |
| S-116-04 | Async upload of a duplicate already in the target album (`skip_duplicates` off): `duplicate`, `photo_id` = existing photo. |
| S-116-05 | Async upload, `skip_duplicates` on: `skipped`, existing `photo_id`, reason. |
| S-116-06 | Metadata resync: `skipped` with the resync reason. Uploads never resync (`ProcessImageJob` builds its `ImportMode` without it), so only the mapping is tested (`UploadStatusEntryTest`). |
| S-116-07 | Duplicate of a copy waiting for moderation: `skipped` with the moderation reason. |
| S-116-08 | `skip_duplicates_not_owned`, duplicate owned by another user: `skipped`, `photo_id` = that user's photo (FR-116-07). |
| S-116-09 | Processing fails for good on a Lychee exception: `failed` with its message; on any other throwable: `failed` with `"Processing failed"`. |
| S-116-10 | Released for retry: still `processing`. |
| S-116-11 | Live-photo video joined to an existing still: `completed`, `photo_id` = the still. |
| S-116-12 | Unknown or expired provisional ID: `unknown`. |
| S-116-13 | Another user, or a guest, queries an authenticated upload's ID: `unknown`. Any caller reads a guest upload's entry. |
| S-116-14 | `sync` queue upload: entry holds the final outcome when the upload answers. |
| S-116-15 | ZIP upload: no entry. |
| S-116-16 | Malformed ID, empty list, 101 IDs: `422`. |
| S-116-18 | Batch of three IDs (one `completed`, one `processing`, one unknown): three entries in request order. |
| S-116-19 | Store choice: `sync` queue → default store; async queue on `file`/`array`/`apc` → `database`; async queue on `redis`/`database` → default store. |
| S-116-20 | `database` store, lottery won: expired upload status rows deleted, live ones and other keys kept. |
| S-116-17 | Cache store throws on write: upload and job succeed. |

## Test Strategy

- **Models / Actions:** unit tests for the outcome mapping helper (NFR-116-05) and the cache repository (TTL, key, throwing store).
- **Jobs:** `ProcessImageJob` run with a non-sync queue job double for S-116-02 … 11, asserting the cache entry.
- **REST API (v2):** new `Feature_v2` test class on `Photo::uploadStatus` for S-116-01, 12, 13, 14, 15, 16 and `photo_id` (FR-116-07); extend `PhotoAddTest`/`UploadWithMetadataTest` only if their fixtures are reused.
- **Frontend (v7/v8):** none (N6); `vp run typecheck` after the TS types change.
- **Docs/Contracts:** `UploadStatusResource` and `UploadStatus` enum in `lychee.d.ts` (`php artisan typescript:transform`).

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-116-01 | `App\Enum\UploadStatus` (string): `processing`, `completed`, `duplicate`, `skipped`, `failed`, `unknown`. | enum, resource, TS |
| DO-116-02 | Cache entry under `upload-status:<provisional id>` in the store of NFR-116-02: `uploader_id` (`int\|null`), `status`, `photo_id` (`string\|null`), `message` (`string\|null`). TTL 86 400 s. | store, jobs, controller |
| DO-116-03 | `UploadStatusResource`: `expected_id` (echo of the provisional ID), `status`, `photo_id`, `message`. | resource, TS |
| DO-116-04 | `PhotoSkippedException` and `PhotoRejectedException` carry the existing photo's ID (`?string`). | exceptions, duplicate pipes |
| DO-116-05 | One list of cache drivers whose data stays in one container (`file`, `array`, `apc`), read by `WorkerCacheCheck` and the upload status store choice. | cache services, diagnostics |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-116-01 | REST GET `/api/v2/Photo::uploadStatus` | Status of 1 to 100 uploads by provisional ID (`photo_ids[]`). | `UploadStatusRequest`, list of `UploadStatusResource`. Open to guests; access filtered per entry (FR-116-06). |

### Telemetry Events

No new events. Existing `jobs` channel lines stay as they are.

## Telemetry & Observability

Cache write failures log one `warning` on the default channel with the provisional ID and the exception message.

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md`: the new route and the status vocabulary.
- `docs/specs/4-architecture/knowledge-map.md`: upload status cache between `ProcessImageJob` and the new endpoint, store choice shared with `WorkerCacheCheck`.
- ADR-116-01: entry access and store choice.
- Roadmap row #116.
- Reply on discussion #4991 once released (owner).

## Spec DSL

```
domain_objects:
  - id: DO-116-01
    name: UploadStatus
    values: [processing, completed, duplicate, skipped, failed, unknown]
  - id: DO-116-02
    name: upload status cache entry
    key: "upload-status:<provisional id>"
    ttl_seconds: 86400
    fields: [uploader_id, status, photo_id, message]
  - id: DO-116-03
    name: UploadStatusResource
    fields: [expected_id, status, photo_id, message]
  - id: DO-116-04
    name: PhotoSkippedException / PhotoRejectedException
    fields: [photo_id]
routes:
  - id: API-116-01
    method: GET
    path: /api/v2/Photo::uploadStatus
    query: {photo_ids: "array of 1..100 RandomID"}
    response: "list of UploadStatusResource, request order"
```
