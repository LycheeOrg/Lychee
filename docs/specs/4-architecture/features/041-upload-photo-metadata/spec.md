# Feature 041 – Upload Photo Metadata

| Field | Value |
|-------|-------|
| Status | Planning |
| Last updated | 2026-05-31 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/041-upload-photo-metadata/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/041-upload-photo-metadata/tasks.md` |
| Roadmap entry | Active Features #041 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Currently the photo upload API accepts only the file binary, album target, and a few flags (last-modified timestamp, watermark toggle).  Two gaps affect usability:

1. **No pre-upload metadata.** Clients cannot set the photo's `title` or `description` at upload time; they must issue a second `PATCH /api/Photo` request after the picture is fully processed, which is cumbersome and blocks automation workflows.
2. **No immediate ID feedback.** The `POST /api/Photo` response only confirms chunk progress; it does not return the photo ID.  Callers cannot navigate to the photo or link to it until the background processing job finishes (potentially seconds to minutes later).

This feature adds `title`, `description`, and `expected_id` to the upload API, touching the HTTP request/response contract, `UploadMetaResource`, `ProcessImageJob`, `ImportParam`/`InitDTO`/`StandaloneDTO` DTOs, one new pipe, and `Photo` model ID pre-allocation.

## Goals

1. Allow callers to supply an optional `title` (≤ 100 chars) and `description` (≤ 1 000 chars) on upload; values take precedence over EXIF-extracted metadata.
2. Return a pre-generated `expected_id` (24-char Base64 random string) in the final-chunk response so the caller knows the photo's future ID immediately.
3. The shipped `id` of the saved `Photo` record must equal `expected_id` in all non-duplicate, non-zip upload cases.
4. Preserve all existing upload behaviour (chunked upload, zip extraction, live-photo partner, duplicate detection, watermark toggle, trust level) without regressions.

## Non-Goals

- Setting tags, license, rating, or taken-at at upload time (separate concern).
- Returning a "real" photo ID when a duplicate is detected (`expected_id` is deliberately named to signal that it may not match the actual stored record in the duplicate case).
- Changing the zip extraction flow to emit `expected_id` (zip produces multiple photos).
- From-URL import (`POST /api/Photo/url`) is unaffected.
- Any UI / frontend changes (TypeScript types, Vue components, upload panel).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-041-01 | `POST /api/Photo` accepts optional `title` field on every chunk. | Field stored in meta; applied to `Photo.title` before EXIF hydration on the final chunk. | Rejected with HTTP 422 if `title` exceeds 100 chars (validation fires on every chunk — standard Laravel `FormRequest` behaviour). | Field value silently ignored on intermediate chunks (stored in meta but not applied to the photo model until the final chunk). | — | Problem statement |
| FR-041-02 | `POST /api/Photo` accepts optional `description` field on every chunk. | Field stored in meta; applied to `Photo.description` before EXIF hydration on the final chunk. | Rejected with HTTP 422 if `description` exceeds 1 000 chars (validation fires on every chunk — standard Laravel `FormRequest` behaviour). | Field value silently ignored on intermediate chunks (stored in meta but not applied to the photo model until the final chunk). | — | Problem statement |
| FR-041-03 | User-supplied `title` overrides EXIF-extracted title. | `Photo.title` equals the submitted value after save. | — | — | — | Problem statement |
| FR-041-04 | User-supplied `description` overrides EXIF-extracted description. | `Photo.description` equals the submitted value after save. | — | — | — | Problem statement |
| FR-041-05 | If `title` is not supplied (or is `null`), EXIF title is used as before; file-name fallback applies when EXIF title is absent. | Existing fallback behaviour unchanged. | — | — | — | Backward-compat stance |
| FR-041-06 | `AutoRenamer` pipe must be skipped when the caller explicitly provided a `title`. | Renamer rules are not applied when `title` is non-null. | — | — | — | Problem statement |
| FR-041-07 | Final-chunk response (`chunk_number == total_chunks`) includes a non-null `expected_id` string for single-file (non-zip) uploads. | `expected_id` is a 24-char Base64url string matching the `id` later stored on the `Photo` record. | — | — | — | Problem statement |
| FR-041-08 | The `Photo` record's `id` must equal the `expected_id` value returned in the upload response for non-duplicate, non-zip uploads. | DB row's `id` column matches returned `expected_id`. | — | If a DB collision occurs on insert, the retry mechanism in `HasRandomIDAndLegacyTimeBasedID` generates a new ID—the stored ID may then differ from `expected_id`. This is an extremely rare edge-case and is acceptable. | — | Problem statement |
| FR-041-09 | For duplicate uploads `expected_id` is still present in the response (the pre-generated value) but it will not match the duplicate's stored `id`. User-supplied `title` and `description` are discarded; the duplicate record retains its existing title and description. | Response contains non-null `expected_id`; HTTP 409 is returned with duplicate info; callers can distinguish the duplicate by the 409 status. | — | — | — | Problem statement ("the id loses its meaning") |
| FR-041-10 | For zip uploads (when `ExtractZip` job is dispatched) `expected_id` is `null` in the response. | `expected_id = null`. | — | — | — | Non-Goal above |
| FR-041-11 | `UploadMetaResource` gains three new nullable fields: `expected_id`, `title`, `description`. | TypeScript transformer regenerates types; frontend can access these fields. | — | — | — | Interface contract |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-041-01 | No additional database round-trips on the upload hot path. | Performance | Profile `ProcessImageJob::handle()` — query count must not increase. | `HasRandomIDAndLegacyTimeBasedID` pre-allocation logic | Performance stance |
| NFR-041-02 | PHPStan level-6 clean after all changes. | Code quality | `make phpstan` exits 0. | phpstan.neon | AGENTS.md quality gate |
| NFR-041-03 | php-cs-fixer reports no diff after all changes. | Code style | `vendor/bin/php-cs-fixer fix --dry-run` exits 0. | .php-cs-fixer.php | AGENTS.md quality gate |
| NFR-041-04 | All existing tests remain green. | Regression safety | `php artisan test` exits 0. | phpunit.xml | AGENTS.md quality gate |

## UI / Interaction Mock-ups

_Not applicable — this feature is API-only. No UI changes are in scope._

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-041-01 | Single file upload with explicit `title` and `description` — saved photo has those values; EXIF is not used for title/description. |
| S-041-02 | Single file upload without `title` — EXIF title (or file-name fallback) is used; `AutoRenamer` runs if configured. |
| S-041-03 | Single file upload without `description` — EXIF description (or null) is used. |
| S-041-04 | Final-chunk response includes `expected_id`; subsequent `GET /api/Photo/{expected_id}` returns the photo (after processing completes). |
| S-041-05 | `Photo.id` equals `expected_id` after non-duplicate upload. |
| S-041-06 | Duplicate upload — response contains `expected_id` (pre-generated) + HTTP 409; `expected_id` does not match the duplicate's stored `id`. |
| S-041-07 | Zip upload — `expected_id` is `null` in response. |
| S-041-08 | `title` > 100 chars in request — HTTP 422 returned. |
| S-041-09 | `description` > 1 000 chars in request — HTTP 422 returned. |
| S-041-11 | `AutoRenamer` skipped when caller supplies a non-null `title`. |

## Test Strategy

- **Core (Unit):** Unit test for the new `ApplyUserProvidedMetadata` pipe: verify it assigns `title`/`description` to `photo` when present and is a no-op when absent. Unit test for `Photo::preallocateId()` / `generateKey()` integration.
- **Application (Feature_v2):** Feature tests extending `BaseApiWithDataTest` for S-041-01 through S-041-09 via the HTTP upload endpoint. Tests stage failing assertions first, then implement code to make them green.
- **REST:** OpenAPI contract: verify new fields appear in the response schema (manual check; no automated snapshot exists yet).
- **CLI:** Not applicable (no CLI command affected).
- **Docs/Contracts:** `UploadMetaResource` response contract updated; no TypeScript type changes in scope.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-041-01 | `UploadMetaResource` — gains `expected_id: ?string`, `title: ?string`, `description: ?string` | HTTP Resources |
| DO-041-02 | `ImportParam` — gains `title: ?string`, `description: ?string`, `preallocated_id: ?string` | DTO layer |
| DO-041-03 | `InitDTO` — gains `title: ?string`, `description: ?string`, `preallocated_id: ?string` from `ImportParam` | DTO layer |
| DO-041-04 | `StandaloneDTO` — gains `title: ?string`, `description: ?string`; constructor accepts pre-allocated ID and calls `Photo::preallocateId()` | DTO layer |
| DO-041-05 | `ProcessImageJob` — gains serialisable `expected_id: ?string`, `title: ?string`, `description: ?string` | Jobs layer |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-041-01 | REST POST /api/Photo | Adds optional `title` (string, ≤100) and `description` (string, ≤1000) form fields; final-chunk response includes `expected_id`. | Existing chunked upload flow is preserved. |

### CLI Commands / Flags

_None — no CLI changes._

### Telemetry Events

_No new telemetry events — uploads already log via `JobHistory`._

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-041-01 | `tests/Feature_v2/Photo/UploadWithMetadataTest.php` | Feature test covering S-041-01 through S-041-09, S-041-11. |

### UI States

_Not applicable — this feature is API-only. No UI states are in scope._

## Telemetry & Observability

No new events.  Existing `JobHistory` record for `ProcessImageJob` continues to capture upload context.

## Documentation Deliverables

- `docs/specs/4-architecture/knowledge-map.md` — add `DO-041-01` through `DO-041-05` entries and note the new `ApplyUserProvidedMetadata` pipe.
- `docs/specs/4-architecture/roadmap.md` — add Feature 041 row.
- `docs/specs/_current-session.md` — update session snapshot.

## Fixtures & Sample Data

`tests/Feature_v2/Photo/UploadWithMetadataTest.php` (created during T-041-02).

## Spec DSL

```yaml
domain_objects:
  - id: DO-041-01
    name: UploadMetaResource
    fields:
      - name: expected_id
        type: "string|null"
      - name: title
        type: "string|null"
      - name: description
        type: "string|null"
  - id: DO-041-02
    name: ImportParam
    fields:
      - name: title
        type: "string|null"
      - name: description
        type: "string|null"
      - name: preallocated_id
        type: "string|null"
  - id: DO-041-03
    name: InitDTO
    fields:
      - name: title
        type: "string|null"
      - name: description
        type: "string|null"
      - name: preallocated_id
        type: "string|null"
  - id: DO-041-04
    name: StandaloneDTO
    fields:
      - name: title
        type: "string|null"
      - name: description
        type: "string|null"
  - id: DO-041-05
    name: ProcessImageJob
    fields:
      - name: expected_id
        type: "string|null"
      - name: title
        type: "string|null"
      - name: description
        type: "string|null"
routes:
  - id: API-041-01
    method: POST
    path: /api/Photo
fixtures:
  - id: FX-041-01
    path: tests/Feature_v2/Photo/UploadWithMetadataTest.php
```

## Appendix

### ID Pre-allocation Strategy

Photo IDs are generated in `HasRandomIDAndLegacyTimeBasedID::generateKey()` at `performInsert` time.  To pre-allocate an ID before the `Photo` is saved, a new `preallocateId(string $id): void` method is added to the `Photo` model (bypassing the `setAttribute` guard by writing directly to `$this->attributes`).  `generateKey()` checks for a pre-populated key and uses it instead of generating a fresh one.  In the rare event of a DB uniqueness collision on the pre-allocated ID, the existing retry loop regenerates a random ID — meaning `expected_id` and the stored `id` may diverge.  This is acceptable per FR-041-08.

### Duplicate-case Semantics

The name `expected_id` was chosen deliberately (per the problem statement) because when a duplicate is detected, the pre-allocated ID is discarded and the duplicate's existing `id` is used.  Callers must treat `expected_id` as a hint, not a guarantee, and use the HTTP status code (409 vs 200) to determine whether the returned resource ID is reliable.
