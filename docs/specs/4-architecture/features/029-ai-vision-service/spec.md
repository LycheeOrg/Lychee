# Feature 029 – AI Vision Service

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-03-15 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/029-ai-vision-service/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/029-ai-vision-service/tasks.md` |
| Roadmap entry | #029 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

Add facial recognition capabilities to Lychee via an **external Python service** running in a separate Docker container. The service detects faces in photos, generates embeddings for similarity matching, and clusters faces to identify individuals. Lychee gains two new Eloquent models — **Person** and **Face** — with relationships that tie detected faces to photos and identified people to users. Users can mark their Person record as non-searchable for privacy.

Affected modules: **Models** (new Person, Face), **Migrations** (new tables + pivot), **Controllers** (People management, Face assignment), **API** (REST endpoints for people/faces), **Frontend** (People page, face tagging UI, privacy toggle), **External** (Python face-recognition microservice).

## Goals

1. Detect faces in photos via an external Python-based facial recognition service running in a dedicated Docker container.
2. **Implement the Python facial recognition service** — face detection, embedding generation, clustering, and similarity matching.
3. **Build and publish the Docker image** for the Python service, with Dockerfile and deployment documentation.
4. Introduce `Person` and `Face` Eloquent models with proper relationships.
5. Allow linking a `Person` to a `User` (1-to-1) for self-identification.
6. Allow linking multiple `Face` records to a single `Person` (many-to-1).
7. Support many-to-many relationship between `Person` and `Photo` (a person can appear in many photos; a photo can contain many people) — derived through the `Face` join model.
8. Provide a UI for browsing, naming, merging, and managing detected people.
9. Allow users to toggle their linked Person as non-searchable for privacy.
10. Define the communication contract between Lychee and the Python service so both can be developed independently.

## Non-Goals

- Real-time video face detection.
- Emotion/age/gender detection.
- Face detection on non-image media (videos, documents).
- Training custom face recognition models.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|---------------------|--------|
| FR-029-01 | System shall store Person records with a name, optional User link (1-1), and a searchability flag. | Person created with name; optionally linked to a User; `is_searchable` defaults to `true`. | Name must be non-empty string ≤255 chars; user_id must reference existing User and be unique across Person table (1-1). | Return 422 with validation errors. | `person.created`, `person.updated` | Owner directive |
| FR-029-02 | System shall store Face records linking a detected face region in a Photo to an optional Person, including a server-side crop thumbnail path. *(Resolved Q-029-09: server-side crop)* | Face created with photo_id, bounding box (x, y, width, height as percentages), confidence score, crop_path (150x150px JPEG); person_id nullable (unassigned). | photo_id must exist; bounding box values 0.0–1.0; confidence 0.0–1.0; crop_path nullable (set when scan result includes crop). | Return 422; reject invalid photo_id with 404. | `face.created` | Owner directive, Q-029-09 |
| FR-029-03 | A Person can appear in multiple Photos (many-to-many through Face). The system shall provide an endpoint to list all photos containing a given Person. | GET endpoint returns paginated photos where at least one Face with matching person_id exists. | person_id must exist; pagination params validated. | 404 if Person not found; empty result set if no faces assigned. | — | Owner directive |
| FR-029-04 | A Photo can contain multiple Persons. The system shall return all identified Persons when viewing a Photo's details. For non-searchable Persons, face overlays are hidden entirely for unauthorized viewers; a `hidden_face_count` integer is included instead. *(Resolved Q-029-10: hide overlay + count indicator)* | Photo detail response includes `faces` array (only searchable/authorized faces) + `hidden_face_count` (integer, count of suppressed non-searchable faces). | — | Graceful empty array if no faces detected; hidden_face_count = 0 if none suppressed. | — | Owner directive, Q-029-10 |
| FR-029-05 | Users can link their account to a Person (1-1) via direct claim. Admins can link/unlink any Person-User pair, overriding user claims. Only one Person per User, one User per Person. *(Resolved Q-029-06: self-identification + admin override)* | User claims a Person; `person.user_id` set; old claim (if any) cleared. Admin can force-link/unlink any pair. | user_id unique on persons table; User must exist. Non-admin claim: 409 if Person already claimed by another User. Admin claim: overrides existing link. | 409 if Person already claimed (non-admin); 422 for validation errors. | `person.claimed` | Owner directive, Q-029-06 |
| FR-029-06 | A Person's linked User (or admin) can toggle `is_searchable` on the Person. When `is_searchable` is false, the Person is excluded from search results and People browsing for non-admin users who are not the linked User. *(Resolved Q-029-05: hidden from search + People page for unauthorized users)* | Toggle flips boolean; subsequent search/browse queries filter accordingly. | Only linked User or admin can toggle. | 403 if unauthorized; 404 if Person not found. | `person.searchability_changed` | Owner directive, Q-029-05 |
| FR-029-07 | System shall expose API endpoints for the external Python service to submit face detection results (batch of faces for a given photo) via REST webhook callback. *(Resolved Q-029-01: REST + webhooks)* | POST endpoint accepts photo_id + array of face data (bounding box, confidence, embedding reference); creates Face records. | photo_id must exist; face data array validated per FR-029-02 rules; idempotent (re-scanning same photo replaces old faces). Authenticated via `face_recognition_api_key`. | 404 for invalid photo; 422 for malformed face data; 401 for invalid API key. | `face.batch_created` | Owner directive, Q-029-01 |
| FR-029-08 | System shall expose an API endpoint to request face detection for a photo or album. Lychee sends REST request to Python service with photo filesystem path (shared Docker volume) and callback URL. Authorization governed by `face_recognition_permission_mode` setting. *(Resolved Q-029-01, Q-029-02, Q-029-07: REST + webhooks + shared volume; Q-029-08: configurable permissions)* | POST triggers scan request via HTTP to Python service; returns 202 Accepted with job/task reference. Also auto-triggered on photo upload when `face_recognition_enabled` is true. | photo_id or album_id must exist; user must have permission per `face_recognition_permission_mode` (open: any user; restricted: photo/album owner or admin). | 404/403 for invalid/unauthorized targets; 503 if Python service unavailable. | `face.scan_requested` | Owner directive, Q-029-01, Q-029-02, Q-029-07, Q-029-08 |
| FR-029-09 | Admin can trigger bulk face detection scan for all unscanned photos. *(Resolved Q-029-02: multiple triggers including bulk)* | Admin action enqueues all photos where `face_scan_status` IS NULL; progress trackable via `face_scan_status` column. | Admin-only access. | 503 if service unavailable; partial failure logged per photo (status set to `failed`). | `face.bulk_scan_requested` | Owner directive, Q-029-02 |
| FR-029-10 | Users can manually assign/reassign an unassigned Face to a Person, or create a new Person from a Face. Python service provides cluster suggestions for grouping similar faces. *(Resolved Q-029-03: auto-cluster with manual confirmation)* | Face's person_id updated; new Person created if requested. Cluster suggestions displayed as similarity scores in assignment UI. | Face must exist; target Person (if specified) must exist. | 404/422 for invalid references. | `face.assigned` | Owner directive, Q-029-03 |
| FR-029-11 | Users can merge two Person records (combining all their Face associations). | All Faces of source Person reassigned to target Person; source Person deleted. | Both Persons must exist; user must have edit permission. | 404 if either Person not found; 403 if unauthorized. | `person.merged` | Owner directive |
| FR-029-12 | Users can upload a selfie photo to claim a Person via face matching. Selfie sent to Python service's dedicated `POST /match` endpoint; image discarded immediately after matching. *(Resolved Q-029-06: selfie-upload claim; Q-029-11: discard after match; Q-029-12: dedicated /match endpoint)* | User uploads selfie → Python service `POST /match` returns top-N matches with confidence → if best match above `face_recognition_selfie_confidence_threshold`, Person is linked to User (same 1-1 rules as FR-029-05). Selfie image deleted after response. | Selfie must contain exactly one detectable face; confidence threshold configurable. | 422 if no face detected in selfie; 404 if no matching Person found; 409 if matched Person already claimed by another User. | `person.selfie_claim_requested`, `person.selfie_claim_matched` | Owner directive, Q-029-06, Q-029-11, Q-029-12 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-029-01 | Face detection must not block photo upload. Processing is asynchronous. | UX: upload speed must not degrade. | Upload response time unchanged (< 2s for typical photo); face detection runs in background. | Queue system (Laravel jobs) or external service callback. | Owner directive |
| NFR-029-02 | The Person/Face data model must support libraries with 100k+ photos and 1k+ persons without query degradation. | Scalability. | People listing < 500ms; photo-faces lookup < 200ms with proper indexes. | Database indexes on `faces.photo_id`, `faces.person_id`, `persons.user_id`. | Owner directive |
| NFR-029-03 | Communication with the Python service must be resilient to service unavailability. Lychee must function normally when the face-recognition container is down. | Reliability; optional feature. | All non-face-recognition features unaffected; face endpoints return 503 gracefully. | Health check / circuit breaker pattern. | Owner directive |
| NFR-029-04 | Privacy: non-searchable Person data must never leak through search endpoints, People browsing, or photo detail responses (for unauthorized viewers). | GDPR / user privacy. | Unit + feature tests verify filtering. | Query scopes on Person model. | Owner directive |
| NFR-029-05 | The API contract between Lychee and the Python service must be versioned and documented so both can evolve independently. | Maintainability; separate codebases. | OpenAPI/JSON schema for the inter-service contract. | — | Owner directive |
| NFR-029-06 | Face bounding box coordinates stored as relative values (0.0–1.0 percentages) to remain resolution-independent. | Display correctness across size variants. | Bounding boxes render correctly on thumb, medium, and original size variants. | Frontend rendering logic. | Owner directive |
| NFR-029-07 | Authorization governed by configurable `face_recognition_permission_mode` setting (enum: `open`, `restricted`). Default: `open`. Both modes must be covered by feature tests. *(Resolved Q-029-08)* | Flexibility for single-user and multi-user deployments. | Feature tests pass in both modes; admin settings UI toggles mode. | Config migration, conditional authorization middleware. | Owner directive, Q-029-08 |
| NFR-029-08 | Python service accesses photos via shared Docker volume (filesystem path). Deployment must document volume mount configuration. *(Resolved Q-029-07)* | Performance; no auth complexity for file access. | Python service reads photos from shared path; integration test confirms file access. | Docker volume configuration in docker-compose. | Owner directive, Q-029-07 |

## UI / Interaction Mock-ups

### People Page (new top-level view)

```
+------------------------------------------------------------------+
| Lychee > People                                          [Search] |
+------------------------------------------------------------------+
|                                                                    |
|  +----------+  +----------+  +----------+  +----------+           |
|  |  ┌────┐  |  |  ┌────┐  |  |  ┌────┐  |  |  ┌────┐  |          |
|  |  │face│  |  |  │face│  |  |  │face│  |  |  │ ?  │  |          |
|  |  │crop│  |  |  │crop│  |  |  │crop│  |  |  │    │  |          |
|  |  └────┘  |  |  └────┘  |  |  └────┘  |  |  └────┘  |          |
|  |  Alice   |  |  Bob     |  |  Carol   |  | Unknown  |          |
|  | 142 photos|  | 87 photos|  | 53 photos|  | 12 faces |          |
|  +----------+  +----------+  +----------+  +----------+           |
|                                                                    |
+------------------------------------------------------------------+
```

### Photo Detail — Face Overlay

```
+------------------------------------------------------------------+
| ◄  Photo Title                                    ⋮ Menu          |
+------------------------------------------------------------------+
|                                                                    |
|     ┌──────────────────────────────────────────┐                  |
|     │                                          │                  |
|     │        ┌─ ─ ─ ┐      ┌─ ─ ─ ┐          │                  |
|     │        │ face1 │      │ face2 │          │                  |
|     │        │ Alice │      │  ???  │          │                  |
|     │        └─ ─ ─ ┘      └─ ─ ─ ┘          │                  |
|     │                                          │                  |
|     │                                          │                  |
|     └──────────────────────────────────────────┘                  |
|                                                                    |
|  Faces: [Alice ✓] [Unknown - click to assign]                    |
+------------------------------------------------------------------+
```

### Person Detail Page

```
+------------------------------------------------------------------+
| Lychee > People > Alice                                           |
+------------------------------------------------------------------+
|  ┌────┐                                                           |
|  │face│  Alice                                                    |
|  │crop│  142 photos · Linked to: alice@example.com               |
|  └────┘  [✓ Searchable] [Edit] [Merge] [Delete]                 |
+------------------------------------------------------------------+
|                                                                    |
|  Photos containing Alice:                                         |
|  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐                   |
|  │      │ │      │ │      │ │      │ │      │                    |
|  │ img1 │ │ img2 │ │ img3 │ │ img4 │ │ img5 │                    |
|  │      │ │      │ │      │ │      │ │      │                    |
|  └──────┘ └──────┘ └──────┘ └──────┘ └──────┘                    |
|                                                                    |
|  [Load more (137 remaining)]                                      |
+------------------------------------------------------------------+
```

### Face Assignment Modal

```
+------------------------------------------+
| Assign Face                              |
+------------------------------------------+
|                                          |
|  ┌────┐                                 |
|  │face│  From: photo_title.jpg          |
|  │crop│  Confidence: 98.2%              |
|  └────┘                                 |
|                                          |
|  Assign to:                             |
|  ○ Existing person: [ Alice       ▼ ]  |
|  ○ New person:      [ __________ ]     |
|                                          |
|  Similar faces found:                   |
|  [Alice (94%)] [Bob (12%)]             |
|                                          |
|          [Cancel]  [Assign]             |
+------------------------------------------+
```

### Selfie Upload Claim

```
+------------------------------------------+
| Find Me in Photos                        |
+------------------------------------------+
|                                          |
|  Upload a photo of yourself to find      |
|  your Person record automatically.       |
|                                          |
|  ┌──────────────────────────────────┐   |
|  │                                  │   |
|  │   [ Drop selfie here or click ] │   |
|  │                                  │   |
|  └──────────────────────────────────┘   |
|                                          |
|  Result:                                |
|  ┌────┐                                |
|  │face│  Match: "Alice" (96.3%)        |
|  │crop│  142 photos                    |
|  └────┘                                |
|                                          |
|  [ ] This is me — link to my account    |
|                                          |
|          [Cancel]  [Claim]              |
+------------------------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-029-01 | Face detected in photo → Face record created with bounding box, confidence, no person assigned |
| S-029-02 | User assigns face to existing Person → Face.person_id updated |
| S-029-03 | User creates new Person from unassigned face → Person created, Face linked |
| S-029-04 | User links Person to their User account → person.user_id set (1-1) |
| S-029-05 | User toggles Person as non-searchable → Person excluded from search/browse for others |
| S-029-06 | Admin triggers bulk scan → All unscanned photos queued for face detection |
| S-029-07 | User requests scan for single photo → Scan job dispatched to Python service |
| S-029-08 | User requests scan for album → All photos in album queued for scanning |
| S-029-09 | Python service unavailable → 503 returned; no impact on other Lychee features |
| S-029-10 | Photo detail view → Face overlays displayed with person names |
| S-029-11 | People page → Grid of all known persons with face crop thumbnails and photo counts |
| S-029-12 | Person detail page → Paginated photos containing that person |
| S-029-13 | Merge two persons → Faces reassigned, source person deleted |
| S-029-14 | Re-scan photo that was already scanned → Old face records replaced with new results |
| S-029-15 | Non-searchable person queried by unauthorized user → Person not returned in results |
| S-029-16 | User claims Person already claimed by another user → 409 Conflict |
| S-029-17 | Photo deleted → Associated Face records cascade-deleted |
| S-029-18 | Person deleted → Associated Face records have person_id set to null (faces remain, become unassigned) |
| S-029-19 | Admin force-links Person to User, overriding existing claim → Previous user's claim cleared, new link set |
| S-029-20 | User uploads selfie → Python service matches face → matching Person linked to User |
| S-029-21 | User uploads selfie with no detectable face → 422 error |
| S-029-22 | User uploads selfie but no matching Person found → 404, user informed |
| S-029-23 | Photo uploaded with face_recognition_enabled → auto-scan job dispatched |

## Test Strategy

- **Core (Unit):** Model relationship tests (Person-User 1-1, Person-Face 1-many, Person-Photo many-many through Face), query scope tests for `is_searchable` filtering, bounding box validation.
- **Application (Feature):** API endpoint tests for CRUD Person/Face, face assignment, person merge, privacy toggle, bulk scan trigger, scan result ingestion.
- **REST:** Request validation tests (bounding box ranges, confidence ranges, unique user_id constraint). Response format verification (faces included in photo detail, people listing pagination).
- **CLI:** Artisan command tests for bulk scan trigger (if implemented as command).
- **UI (JS):** People page rendering, face overlay positioning from bounding box data, assignment modal interaction, searchability toggle.
- **Integration:** Mock Python service interaction — verify scan request dispatch and result ingestion flow.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-029-01 | `Person` — id, name, user_id (nullable, unique), is_searchable (boolean, default true), timestamps | Models, API, UI |
| DO-029-02 | `Face` — id, photo_id (FK→photos), person_id (nullable FK→persons), x, y, width, height (float 0.0–1.0), confidence (float 0.0–1.0), crop_path (nullable string, 150x150 JPEG), timestamps | Models, API, UI |
| DO-029-03 | `PersonResource` — Spatie Data resource for Person API responses | Resources |
| DO-029-04 | `FaceResource` — Spatie Data resource for Face API responses (included in PhotoResource) | Resources |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-029-01 | GET /api/v2/People | List all persons (paginated), filtered by is_searchable for non-admin users | Returns PersonResource collection |
| API-029-02 | GET /api/v2/Person/{id} | Get Person detail with face count and photo count | Returns PersonResource |
| API-029-03 | POST /api/v2/Person | Create a new Person | Body: name, user_id? |
| API-029-04 | PATCH /api/v2/Person/{id} | Update Person (name, is_searchable) | |
| API-029-05 | DELETE /api/v2/Person/{id} | Delete Person (nullifies face.person_id) | |
| API-029-06 | POST /api/v2/Person/{id}/merge | Merge source Person into target | Body: target_person_id |
| API-029-07 | POST /api/v2/Person/{id}/claim | Link Person to current User (1-1) | |
| API-029-08 | GET /api/v2/Person/{id}/photos | Paginated photos containing Person | |
| API-029-09 | POST /api/v2/Face/{id}/assign | Assign a Face to a Person (or create new Person) | Body: person_id or new_person_name |
| API-029-10 | POST /api/v2/FaceDetection/scan | Request face detection for photo(s) or album | Body: photo_ids[] or album_id |
| API-029-11 | POST /api/v2/FaceDetection/results | Receive face detection results from Python service (internal/service-to-service) | Body: photo_id, faces[] |
| API-029-12 | POST /api/v2/FaceDetection/bulk-scan | Admin: enqueue all unscanned photos | Admin-only |
| API-029-13 | POST /api/v2/Person/claim-by-selfie | Upload selfie photo, Python service matches against embeddings, link matching Person to current User | Multipart form with image file |

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-029-01 | `php artisan lychee:scan-faces` | Enqueue all unscanned photos for face detection (admin batch operation) |
| CLI-029-02 | `php artisan lychee:scan-faces --album={id}` | Enqueue photos in a specific album for face detection |

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-029-01 | person.created | `person_id`, `has_user_link` |
| TE-029-01b | person.updated | `person_id`, `changed_fields` |
| TE-029-02 | person.claimed | `person_id`, `user_id` |
| TE-029-03 | person.searchability_changed | `person_id`, `is_searchable` |
| TE-029-04 | person.merged | `source_person_id`, `target_person_id`, `faces_moved_count` |
| TE-029-05 | face.batch_created | `photo_id`, `face_count` |
| TE-029-06 | face.assigned | `face_id`, `person_id` |
| TE-029-07 | face.scan_requested | `target_type` (photo/album/bulk), `target_id`, `photo_count` |
| TE-029-08 | person.selfie_claim_requested | `user_id` |
| TE-029-09 | person.selfie_claim_matched | `user_id`, `person_id`, `confidence` |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-029-01 | People grid | Navigate to /people → Grid of persons with face thumbnails and photo counts |
| UI-029-02 | Person detail | Click person card → Person page with paginated photos |
| UI-029-03 | Face overlay on photo | View photo detail → Dashed rectangles over detected faces with name labels |
| UI-029-04 | Face assignment modal | Click unassigned face → Modal with person selector and similarity suggestions |
| UI-029-05 | Scan progress | Trigger scan → Progress indicator (scanning N of M photos) |
| UI-029-06 | Service unavailable | Python service down → Toast notification, face features gracefully disabled |
| UI-029-07 | Selfie upload claim | User profile → "Find me" button → Upload selfie → Matching result displayed → Confirm claim |

## Telemetry & Observability

Events follow existing Lychee patterns. Face detection request/result events enable tracking of:
- Scan throughput (photos processed per minute)
- Assignment rate (faces assigned vs. unassigned over time)
- Service availability (scan requests vs. 503 responses)

No PII in event payloads — person names are not logged; only IDs.

## Documentation Deliverables

1. Update [knowledge-map.md](../../knowledge-map.md) with Person, Face models and Python service integration.
2. Add how-to guide: `docs/specs/2-how-to/configure-facial-recognition.md` (Docker setup, env vars, service configuration).
3. Update [database-schema.md](../../3-reference/database-schema.md) with `persons`, `faces` tables.
4. ADR for inter-service communication pattern (after Q-029-01 resolved).

## Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-029-01 | `tests/fixtures/face-detection-response.json` | Mock Python service response for testing result ingestion |
| FX-029-02 | `tests/fixtures/sample-face-photo.jpg` | Test photo with known face regions for bounding box validation |

## Spec DSL

```yaml
domain_objects:
  - id: DO-029-01
    name: Person
    fields:
      - name: id
        type: string
        constraints: "primary key"
      - name: name
        type: string
        constraints: "max:255, required"
      - name: user_id
        type: integer
        constraints: "nullable, unique, FK→users"
      - name: is_searchable
        type: boolean
        constraints: "default: true"
  - id: DO-029-02
    name: Face
    fields:
      - name: id
        type: string
        constraints: "primary key"
      - name: photo_id
        type: string
        constraints: "required, FK→photos, cascade delete"
      - name: person_id
        type: string
        constraints: "nullable, FK→persons, set null on delete"
      - name: x
        type: float
        constraints: "0.0–1.0"
      - name: y
        type: float
        constraints: "0.0–1.0"
      - name: width
        type: float
        constraints: "0.0–1.0"
      - name: height
        type: float
        constraints: "0.0–1.0"
      - name: confidence
        type: float
        constraints: "0.0–1.0"
      - name: crop_path
        type: string
        constraints: "nullable, 150x150 JPEG stored alongside size variants"
routes:
  - id: API-029-01
    method: GET
    path: /api/v2/People
  - id: API-029-02
    method: GET
    path: /api/v2/Person/{id}
  - id: API-029-03
    method: POST
    path: /api/v2/Person
  - id: API-029-04
    method: PATCH
    path: /api/v2/Person/{id}
  - id: API-029-05
    method: DELETE
    path: /api/v2/Person/{id}
  - id: API-029-06
    method: POST
    path: /api/v2/Person/{id}/merge
  - id: API-029-07
    method: POST
    path: /api/v2/Person/{id}/claim
  - id: API-029-08
    method: GET
    path: /api/v2/Person/{id}/photos
  - id: API-029-09
    method: POST
    path: /api/v2/Face/{id}/assign
  - id: API-029-10
    method: POST
    path: /api/v2/FaceDetection/scan
  - id: API-029-11
    method: POST
    path: /api/v2/FaceDetection/results
  - id: API-029-12
    method: POST
    path: /api/v2/FaceDetection/bulk-scan
  - id: API-029-13
    method: POST
    path: /api/v2/Person/claim-by-selfie
cli_commands:
  - id: CLI-029-01
    command: php artisan lychee:scan-faces
  - id: CLI-029-02
    command: php artisan lychee:scan-faces --album={id}
telemetry_events:
  - id: TE-029-01
    event: person.created
  - id: TE-029-01b
    event: person.updated
  - id: TE-029-02
    event: person.claimed
  - id: TE-029-03
    event: person.searchability_changed
  - id: TE-029-04
    event: person.merged
  - id: TE-029-05
    event: face.batch_created
  - id: TE-029-06
    event: face.assigned
  - id: TE-029-07
    event: face.scan_requested
  - id: TE-029-08
    event: person.selfie_claim_requested
  - id: TE-029-09
    event: person.selfie_claim_matched
ui_states:
  - id: UI-029-01
    description: People grid page
  - id: UI-029-02
    description: Person detail with photo grid
  - id: UI-029-03
    description: Face overlay on photo detail
  - id: UI-029-04
    description: Face assignment modal
  - id: UI-029-05
    description: Scan progress indicator
  - id: UI-029-06
    description: Service unavailable state
  - id: UI-029-07
    description: Selfie upload claim modal
```

## Appendix

### Data Model Relationships

```
┌──────────┐     1-1 (optional)     ┌──────────┐
│   User   │◄───────────────────────│  Person  │
└──────────┘                        └──────────┘
                                         │
                                    1 ──►│◄── many
                                         │
                                    ┌──────────┐
                                    │   Face   │
                                    └──────────┘
                                         │
                                    many │──► 1
                                         │
                                    ┌──────────┐
                                    │  Photo   │
                                    └──────────┘

Person ◄──many──► Photo  (derived through Face: Person has many Faces,
                           each Face belongs to one Photo)
```

### Inter-Service Communication (REST + Webhook Callbacks + Shared Volume)

Communication uses REST API with webhook callbacks. Photo files are accessed via **shared Docker volume** (Q-029-07 resolved). Authentication via shared API key (`face_recognition_api_key`).

**Scan Request (Lychee → Python):** `POST /detect`
```json
{
  "photo_id": "abc123",
  "photo_path": "/data/photos/original/abc123.jpg",
  "callback_url": "https://lychee.example.com/api/v2/FaceDetection/results"
}
```

**Scan Result (Python → Lychee):** `POST /api/v2/FaceDetection/results`
```json
{
  "photo_id": "abc123",
  "faces": [
    {
      "x": 0.25, "y": 0.10, "width": 0.15, "height": 0.20,
      "confidence": 0.982,
      "embedding_id": "emb_001",
      "crop": "<base64-encoded 150x150 JPEG>"
    },
    {
      "x": 0.60, "y": 0.15, "width": 0.12, "height": 0.18,
      "confidence": 0.876,
      "embedding_id": "emb_002",
      "crop": "<base64-encoded 150x150 JPEG>"
    }
  ]
}
```

**Selfie Match (Lychee → Python):** `POST /match` *(Q-029-12 resolved)*
```
// Request: multipart form with "image" file field
// Response:
{
  "matches": [
    { "embedding_id": "emb_001", "person_suggestion": "cluster_42", "confidence": 0.963 },
    { "embedding_id": "emb_002", "person_suggestion": "cluster_17", "confidence": 0.412 }
  ]
}
```

**Health Check:** `GET /health` → `{"status": "ok"}`

### Python Service Technical Specification

#### Technology Stack

| Component | Choice | Rationale |
|-----------|--------|-----------|
| **Language** | Python 3.13+ | Required for InsightFace compatibility and modern type annotation syntax (`type` statements, `X \| Y` unions). |
| **Package manager** | `uv` | Fast dependency resolution and lockfile support. `pyproject.toml` as single config source. |
| **Web framework** | FastAPI | Async-capable, auto-generated OpenAPI docs, native Pydantic integration for request/response validation. |
| **Face detection & recognition** | InsightFace (ONNX Runtime backend) | State-of-the-art accuracy on LFW benchmark (99.8%+); permissive Apache-2.0 license; ONNX Runtime allows CPU-only or GPU-accelerated inference without heavy CUDA build deps. |
| **Face detection model** | `buffalo_l` (default), configurable via `MODEL_NAME` env var | InsightFace model pack including RetinaFace detector + ArcFace recognition. `buffalo_l` = large/high-accuracy; `buffalo_s` = small/faster alternative. |
| **Embedding storage** | SQLite + `sqlite-vec` (default); PostgreSQL + `pgvector` (optional) | SQLite for single-container simplicity; pgvector for production-scale deployments. Configurable via `STORAGE_BACKEND` env var. |
| **Clustering** | scikit-learn DBSCAN (density-based) | No need to pre-specify cluster count; handles noise/outliers naturally. |
| **Image processing** | Pillow (PIL) | Face crop generation (150×150px JPEG). |
| **Data validation** | Pydantic v2 | Request/response schemas, settings management (`BaseSettings`), strict type coercion. |
| **Testing** | pytest + pytest-asyncio + httpx (async test client) | pytest for test runner and fixtures; httpx `AsyncClient` for testing FastAPI endpoints without starting a server. |
| **Formatter & linter** | Ruff | Single tool for formatting (`ruff format`) and linting (`ruff check`). Configured in `pyproject.toml`. |
| **Type checker** | ty (Astral) | All functions must have complete type annotations. Part of the Astral toolchain (uv + ruff + ty). Enforced in CI via `uv run ty check`. |
| **ASGI server** | Uvicorn | Production server for FastAPI; configurable workers via `WORKERS` env var. |

#### Project Structure

```
ai-vision-service/
├── pyproject.toml              # uv project config, dependencies, ruff/ty settings
├── uv.lock                     # Locked dependency versions
├── Dockerfile                  # Multi-stage build
├── README.md
├── app/
│   ├── __init__.py
│   ├── main.py                 # FastAPI app factory, lifespan (model loading)
│   ├── config.py               # Pydantic BaseSettings for env var configuration
│   ├── api/
│   │   ├── __init__.py
│   │   ├── routes.py           # POST /detect, POST /match, GET /health
│   │   ├── dependencies.py     # API key auth dependency, model injection
│   │   └── schemas.py          # Pydantic request/response models
│   ├── detection/
│   │   ├── __init__.py
│   │   ├── detector.py         # InsightFace face detection wrapper
│   │   └── cropper.py          # Face crop generation (150x150 JPEG, base64)
│   ├── embeddings/
│   │   ├── __init__.py
│   │   ├── store.py            # Abstract embedding store protocol
│   │   ├── sqlite_store.py     # SQLite + sqlite-vec implementation
│   │   └── pgvector_store.py   # PostgreSQL + pgvector implementation
│   ├── clustering/
│   │   ├── __init__.py
│   │   └── clusterer.py        # DBSCAN clustering over stored embeddings
│   └── matching/
│       ├── __init__.py
│       └── matcher.py          # Similarity search for selfie matching
└── tests/
    ├── conftest.py             # Shared fixtures (test images, mock stores)
    ├── test_detection.py       # Face detection unit tests
    ├── test_cropper.py         # Crop generation tests
    ├── test_embeddings.py      # Embedding store CRUD + similarity tests
    ├── test_clustering.py      # Clustering correctness tests
    ├── test_matching.py        # Selfie matching tests
    └── test_api.py             # Integration tests (full endpoint flows)
```

#### Pydantic Models (Request/Response Schemas)

```python
# app/api/schemas.py

class DetectRequest(BaseModel):
    photo_id: str
    photo_path: str  # Filesystem path on shared volume
    callback_url: str  # Lychee webhook URL

class FaceResult(BaseModel):
    x: float = Field(ge=0.0, le=1.0)
    y: float = Field(ge=0.0, le=1.0)
    width: float = Field(ge=0.0, le=1.0)
    height: float = Field(ge=0.0, le=1.0)
    confidence: float = Field(ge=0.0, le=1.0)
    embedding_id: str
    crop: str  # Base64-encoded 150x150 JPEG

class DetectCallbackPayload(BaseModel):
    photo_id: str
    faces: list[FaceResult]

class MatchRequest(BaseModel):
    # Multipart file upload — validated in endpoint, not as Pydantic model
    pass

class MatchResult(BaseModel):
    embedding_id: str
    person_suggestion: str | None  # Cluster label if available
    confidence: float = Field(ge=0.0, le=1.0)

class MatchResponse(BaseModel):
    matches: list[MatchResult]

class HealthResponse(BaseModel):
    status: str  # "ok" | "degraded"
    model_loaded: bool
    embedding_count: int

class AppSettings(BaseSettings):
    """Pydantic BaseSettings — all config from env vars."""
    lychee_api_url: str
    lychee_api_key: str
    api_key: str  # Key that Lychee sends to authenticate with this service
    model_name: str = "buffalo_l"
    confidence_threshold: float = 0.5
    storage_backend: str = "sqlite"  # "sqlite" | "pgvector"
    storage_path: str = "/data/embeddings"
    photos_path: str = "/data/photos"
    workers: int = 1
    log_level: str = "info"

    model_config = SettingsConfigDict(env_prefix="FACE_")
```

#### Type Annotation Requirements

All Python code must be fully type-annotated:
- All function signatures (parameters and return types).
- All class attributes and instance variables.
- No `Any` types except where interfacing with untyped third-party libraries (must be isolated behind typed wrappers).
- Use modern syntax: `list[str]` not `List[str]`, `str | None` not `Optional[str]`.
- `ty check` must pass with zero errors in CI.

#### Ruff & ty Configuration

```toml
# In pyproject.toml
[tool.ruff]
target-version = "py313"
line-length = 120

[tool.ruff.lint]
select = [
    "E",    # pycodestyle errors
    "W",    # pycodestyle warnings
    "F",    # pyflakes
    "I",    # isort
    "N",    # pep8-naming
    "UP",   # pyupgrade
    "ANN",  # flake8-annotations
    "B",    # flake8-bugbear
    "A",    # flake8-builtins
    "SIM",  # flake8-simplicity
    "TCH",  # flake8-type-checking
    "RUF",  # ruff-specific
]

[tool.ruff.lint.per-file-ignores]
"tests/**/*.py" = ["ANN"]  # Relax annotation enforcement in tests

[tool.ty]
python-version = "3.13"
```

#### Test Strategy (Python Service)

| Layer | Scope | Tooling | Notes |
|-------|-------|---------|-------|
| **Unit** | Detection, cropping, embedding CRUD, clustering, matching | pytest + fixtures with sample images | Mock InsightFace model for fast tests; one slow integration test with real model. |
| **API integration** | Full endpoint flows (detect → callback, match → response, health) | pytest-asyncio + httpx `AsyncClient` | Test against FastAPI `TestClient`; mock external HTTP calls (callback to Lychee). |
| **Validation** | Pydantic schema enforcement | pytest | Invalid payloads return 422 with structured errors. |
| **Type checking** | Static analysis | ty (Astral) | Run in CI via `uv run ty check`; zero errors required. |
| **Formatting & linting** | Code style | ruff | `ruff format --check` + `ruff check` in CI; zero violations required. |

Minimum test coverage target: **80%** (measured by `pytest-cov`).

#### CI/CD: GitHub Actions Workflow

A new workflow `python_ai_vision.yml` runs when Python files change. It follows the existing Lychee CI patterns (pinned action versions, `step-security/harden-runner`, concurrency groups).

**Trigger conditions:**
```yaml
on:
  push:
    branches: [master]
    paths:
      - "ai-vision-service/**"
      - ".github/workflows/python_ai_vision.yml"
  pull_request:
    paths:
      - "ai-vision-service/**"
      - ".github/workflows/python_ai_vision.yml"
```

**Jobs:**

| Job | Steps | Matrix |
|-----|-------|--------|
| **lint** | `ruff format --check`, `ruff check` | Python 3.13 |
| **typecheck** | `uv run ty check` | Python 3.13 |
| **test** | `pytest --cov=app --cov-report=xml` | Python 3.13, 3.14 |
| **docker-build** | `docker build .` (verify image builds) | — |

All jobs use `uv` for dependency installation:
```yaml
- uses: astral-sh/setup-uv@v5
- run: uv sync --frozen
- run: uv run ruff format --check
- run: uv run ruff check
- run: uv run ty check
- run: uv run pytest --cov=app --cov-report=xml
```

**Quality gate (must all pass before merge):**
1. `ruff format --check` — zero formatting violations.
2. `ruff check` — zero lint violations.
3. `ty check` — zero type errors.
4. `pytest` — all tests pass, ≥80% coverage.
5. `docker build .` — image builds successfully.

#### Docker Configuration

```dockerfile
# Multi-stage build
FROM python:3.13-slim AS builder
COPY --from=ghcr.io/astral-sh/uv:latest /uv /usr/local/bin/uv
WORKDIR /app
COPY pyproject.toml uv.lock ./
RUN uv sync --frozen --no-dev

FROM python:3.13-slim AS runtime
WORKDIR /app
COPY --from=builder /app/.venv /app/.venv
COPY app/ ./app/
ENV PATH="/app/.venv/bin:$PATH"
EXPOSE 8000
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000"]
```

**Environment variables (runtime):**

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `FACE_LYCHEE_API_URL` | Yes | — | Lychee instance base URL for callbacks |
| `FACE_LYCHEE_API_KEY` | Yes | — | API key for authenticating callbacks to Lychee |
| `FACE_API_KEY` | Yes | — | API key that Lychee sends to authenticate with this service |
| `FACE_MODEL_NAME` | No | `buffalo_l` | InsightFace model pack name |
| `FACE_CONFIDENCE_THRESHOLD` | No | `0.5` | Minimum face detection confidence |
| `FACE_STORAGE_BACKEND` | No | `sqlite` | Embedding storage: `sqlite` or `pgvector` |
| `FACE_STORAGE_PATH` | No | `/data/embeddings` | Path for SQLite DB or pgvector connection string |
| `FACE_PHOTOS_PATH` | No | `/data/photos` | Shared volume mount for photo files |
| `FACE_WORKERS` | No | `1` | Number of Uvicorn worker processes |
| `FACE_LOG_LEVEL` | No | `info` | Logging level |
