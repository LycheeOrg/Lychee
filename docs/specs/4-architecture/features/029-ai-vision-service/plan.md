# Feature Plan 029 – Facial Recognition

_Linked specification:_ `docs/specs/4-architecture/features/029-ai-vision-service/spec.md`
_Status:_ Draft
_Last updated:_ 2026-03-18

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs have been updated.

## Vision & Success Criteria

Enable Lychee users to browse their photo library by the people who appear in them. A Python-based facial recognition service (separate container) detects faces; Lychee stores the results, provides management UI, and respects privacy preferences.

**Success signals:**
- Person and Face models created with correct relationships and migrations.
- API endpoints for CRUD operations on Person/Face, scan triggering, and result ingestion pass feature tests.
- People page renders persons with photo counts; photo detail shows face overlays.
- Non-searchable persons are invisible to unauthorized users (verified by tests).
- Lychee functions normally when the Python service is unavailable (NFR-029-03).

## Scope Alignment

- **In scope:**
  - Database migrations for `persons` and `faces` tables.
  - Eloquent models (Person, Face) with relationships.
  - API endpoints (People CRUD, Face assignment, scan trigger, result ingestion).
  - Frontend: People page, Person detail page, face overlays on photo detail, face assignment modal, privacy toggle.
  - Artisan commands for bulk scanning.
  - Inter-service contract definition (JSON schema for scan request/result).
  - Feature and unit tests for all backend functionality.
  - **Python facial recognition service**: face detection, embedding generation, clustering, similarity matching, REST API with callback support.
  - **Docker image**: Dockerfile for the Python service, docker-compose integration, deployment documentation.

- **Out of scope:**
  - Training custom face recognition models (use pre-trained models like InsightFace/dlib/face_recognition).
  - Cluster review/confirmation UI (Lychee consumes cluster suggestions; dedicated review workflow is a follow-up).

## Dependencies & Interfaces

- **Photo model** — Face belongs to Photo; cascade delete required.
- **User model** — Person optionally linked to User (1-1).
- **AccessPermission / PhotoQueryPolicy** — Person photo listing must respect existing album access control.
- **Laravel Queue** — Scan requests dispatched as jobs for async processing.
- **Python service** — External dependency; communicates via REST + webhook callbacks (Q-029-01 resolved). Contract defined in spec appendix.
- **PrimeVue** — Frontend components for People page, modals, overlays.

## Assumptions & Risks

- **Assumptions:**
  - The Python service will conform to the JSON contract defined in the spec appendix.
  - Photos are accessible to the Python service via shared Docker volume (Q-029-07 resolved).
  - Face detection results include bounding boxes as relative coordinates (0.0–1.0).

- **Risks / Mitigations:**
  - *Python service API changes:* Mitigated by versioned contract schema (NFR-029-05).
  - *Large libraries overwhelm scan queue:* Mitigated by rate limiting bulk scans and progress tracking.
  - *Privacy leaks through non-searchable persons:* Mitigated by query scopes tested at unit and feature level (NFR-029-04).

## Implementation Drift Gate

After each increment, verify:

**Python service (I1–I3):**
1. `uv run pytest --cov=app` — all tests pass, ≥80% coverage.
2. `uv run ruff format --check` — zero formatting violations.
3. `uv run ruff check` — zero lint violations.
4. `uv run ty check` — zero type errors.
5. `docker build .` — image builds successfully (I3+).

**Lychee PHP backend (I4–I12):**
1. All new tests pass (`php artisan test --filter=Face` / `--filter=Person`).
2. PHPStan reports 0 errors (`make phpstan`).
3. php-cs-fixer is clean (`vendor/bin/php-cs-fixer fix`).

**Frontend (I13–I18):**
4. Frontend builds cleanly (`npm run check`).

**All increments:**
5. Spec scenario IDs traceable to at least one test.

## Increment Map

> **Implementation order: Python service first (I1–I3), then PHP/Lychee backend (I4–I12), then frontend (I13–I18), then docs (I19).**

### Phase 1: Python Facial Recognition Service

### I1 – Python Service: Project Setup & Face Detection (≈90 min)

- _Goal:_ Create the Python facial recognition service project with face detection and crop generation.
- _Preconditions:_ Inter-service contract finalized (spec appendix).
- _Steps:_
  1. Create project structure: `ai-vision-service/` with `pyproject.toml` (uv), `app/`, `tests/`, `Dockerfile`. Configure ruff and ty in `pyproject.toml`.
  2. Integrate InsightFace (ONNX Runtime backend) with `buffalo_l` model pack. Typed wrapper around InsightFace API.
  3. Create Pydantic models (`app/api/schemas.py`): `DetectRequest`, `FaceResult`, `DetectCallbackPayload`, `AppSettings` (BaseSettings).
  4. Implement face detection (`app/detection/detector.py`): accept photo filesystem path, return bounding boxes (0.0–1.0 relative) + confidence scores. Full type annotations.
  5. Implement embedding generation (`app/embeddings/`): extract face embeddings, store in SQLite+sqlite-vec (default) or PostgreSQL+pgvector. Abstract `EmbeddingStore` protocol with typed implementations.
  6. Implement face crop generation (`app/detection/cropper.py`): 150x150px JPEG crop per face, returned as base64. Uses Pillow.
  7. Unit tests (pytest + pytest-asyncio): detection, embedding storage CRUD, crop generation.
  8. Quality gate: `uv run ruff format --check`, `uv run ruff check`, `uv run ty check`, `uv run pytest`.
- _Commands:_ `uv sync`, `uv run pytest`, `uv run ruff check`, `uv run ty check`
- _Exit:_ Face detection works on test images; embeddings generated and stored; crops generated; all quality gates pass.

### I2 – Python Service: Clustering, Matching & Callback (≈90 min)

- _Goal:_ Implement clustering, selfie-match, callback flow, and REST API.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Implement FastAPI REST API (`app/main.py`, `app/api/routes.py`): `POST /detect`, `POST /match`, `GET /health`. Pydantic models for all request/response schemas. Full type annotations.
  2. Implement clustering (`app/clustering/clusterer.py`): scikit-learn DBSCAN over stored embeddings. Configurable distance threshold. Typed interface.
  3. Implement `POST /match` endpoint (`app/matching/matcher.py`): accepts image file (multipart), detects face, compares embedding against stored embeddings via similarity search, returns top-N `MatchResult` objects with confidence scores.
  4. Implement scan callback flow: receive `DetectRequest` → detect faces → generate embeddings + crops → store embeddings → POST `DetectCallbackPayload` back to Lychee callback URL via httpx.
  5. API key authentication dependency (`app/api/dependencies.py`) for incoming requests from Lychee.
  6. Integration tests (pytest-asyncio + httpx `AsyncClient`): full endpoint flows, clustering, matching, callback with mocked Lychee endpoint.
- _Commands:_ `uv run pytest`, `uv run ruff check`, `uv run ty check`
- _Exit:_ All endpoints work; clustering groups similar faces; match returns correct results; callback delivers results to mock Lychee endpoint; all quality gates pass.

### I3 – Python Service: Docker Image & Deployment (≈60 min)

- _Goal:_ Dockerize the Python service, prepare deployment config, and set up CI/CD.
- _Preconditions:_ I2 complete.
- _Steps:_
  1. Finalize Dockerfile: multi-stage build (builder with `uv sync --frozen --no-dev`, runtime with slim Python base), GPU support optional.
  2. docker-compose integration: add face-recognition service to Lychee's docker-compose with shared photos volume and internal network.
  3. Environment variable configuration via Pydantic `AppSettings` (`VISION_FACE_`-prefixed): `VISION_FACE_LYCHEE_API_URL`, `VISION_FACE_LYCHEE_API_KEY`, `VISION_FACE_API_KEY`, `VISION_FACE_MODEL_NAME`, `VISION_FACE_DETECTION_THRESHOLD` (bounding box filter), `VISION_FACE_MATCH_THRESHOLD` (similarity search cutoff), `VISION_FACE_RESCAN_IOU_THRESHOLD` (IoU on re-scan), `VISION_FACE_MAX_FACES_PER_PHOTO` (default 10), `VISION_FACE_THREAD_POOL_SIZE`, `VISION_FACE_STORAGE_BACKEND`, `VISION_FACE_STORAGE_PATH`, `VISION_FACE_PHOTOS_PATH`, `VISION_FACE_WORKERS`, `VISION_FACE_LOG_LEVEL`.
  4. Startup: FastAPI lifespan handler loads `buffalo_l` model (baked into image at build time; no download on first run — Q-029-32 resolved). Workers count exposed via CMD shell form to honour `VISION_FACE_WORKERS` env var.
  5. Create `.github/workflows/python_ai_vision.yml`: lint (ruff), typecheck (ty check), test (pytest --cov, Python 3.13+3.14 matrix), docker-build. Uses `astral-sh/setup-uv@v5`. Follows existing Lychee CI patterns (harden-runner, pinned actions, concurrency groups).
  6. Smoke test: docker-compose up → health check passes → detect endpoint responds.
- _Commands:_ `docker build .`, `docker-compose up`
- _Exit:_ Docker image builds and runs; health check passes; CI workflow passes; detect endpoint responds in containerized environment.

### Phase 2: Lychee Backend (PHP/Laravel)

### I4 – Database Migrations (≈45 min)

- _Goal:_ Create `persons` and `faces` database tables with crop_token field.
- _Preconditions:_ Spec approved; I1–I3 complete (Python service contract validated).
- _Steps:_
  1. Create migration for `persons` table: `id` (string PK), `name` (varchar 255), `user_id` (nullable unsigned int, unique, FK→users ON DELETE SET NULL), `is_searchable` (boolean default true), timestamps.
  2. Create migration for `faces` table: `id` (string PK), `photo_id` (string, FK→photos ON DELETE CASCADE), `person_id` (nullable string, FK→persons ON DELETE SET NULL), `x` / `y` / `width` / `height` (float, 0.0–1.0), `confidence` (float), `crop_token` (nullable string — random high-entropy token; file stored at `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`, served nginx-direct; Q-029-34), `is_dismissed` (boolean, default false), timestamps. Indexes on `photo_id`, `person_id`.
  3. Create migration for `face_suggestions` table: `face_id` (string, FK→faces CASCADE), `suggested_face_id` (string, FK→faces CASCADE), `confidence` (float); unique constraint on `(face_id, suggested_face_id)`. *(DO-029-05, Q-029-33)*
  4. Add `face_scan_status` nullable `VARCHAR(16)` column to `photos` table. *(DO-029-06, Q-029-38)*
  5. Add `persons.user_id` index.
  6. Add config entries migration (`cat = 'AI Vision'`, `level = 1` / SE): `ai_vision_enabled` (0|1, default 0), `ai_vision_face_enabled` (0|1, default 0), `ai_vision_face_permission_mode` (string, default `restricted`), `ai_vision_face_selfie_confidence_threshold` (float, default 0.8), `ai_vision_face_person_is_searchable_default` (0|1, default 1), `ai_vision_face_allow_user_claim` (0|1, default 1), `ai_vision_face_scan_batch_size` (integer, default 200). Infrastructure keys (`AI_VISION_FACE_URL`, `AI_VISION_FACE_API_KEY`) stored in `.env` / `config/features.php` only — not in the `configs` table.
- _Commands:_ `php artisan test`
- _Exit:_ Migrations run on test SQLite DB; `php artisan test` passes.

### I5 – Eloquent Models & Relationships (≈60 min)

- _Goal:_ Create Person and Face models with all relationships.
- _Preconditions:_ I4 complete.
- _Steps:_
  1. Write unit tests for Person relationships: `user()` (belongsTo User), `faces()` (hasMany Face), `photos()` (hasManyThrough or custom relation via Face).
  2. Write unit tests for Face relationships: `photo()` (belongsTo Photo), `person()` (belongsTo Person).
  3. Write unit test for Photo→faces relationship (hasMany Face). Test cascade: Photo delete → Face cascade delete. Test Person delete → Face.person_id set to null.
  4. Implement Person model with relationships, `scopeSearchable()` query scope, fillable fields.
  5. Implement Face model with relationships, bounding box accessors, `crop_token`, `is_dismissed` (boolean), fillable fields; `ScanStatus` PHP Enum cast on `face_scan_status` (photos model, Q-029-38).
  6. Implement FaceSuggestion model: `face_id`, `suggested_face_id`, `confidence`; `face()` / `suggestedFace()` belongsTo. *(DO-029-05)*
  7. Add `faces()` relationship (hasMany Face) and `faceSuggestions()` through Face to Photo model.
  8. Add `person()` relationship to User model (hasOne Person).
- _Commands:_ `php artisan test --filter=Person`, `php artisan test --filter=Face`
- _Exit:_ All relationship tests green; PHPStan clean.

### I6 – Spatie Data Resources (≈30 min)

- _Goal:_ Create PersonResource and FaceResource for API responses.
- _Preconditions:_ I5 complete.
- _Steps:_
  1. Create PersonResource (Spatie Data): `id`, `name`, `user_id`, `is_searchable`, `face_count`, `photo_count`, `representative_crop_url`.
  2. Create FaceResource (Spatie Data) per DO-029-04 *(Q-029-46)*: `id` (Face ID), `photo_id`, `person_id` (nullable), `x` / `y` / `width` / `height` (float 0.0–1.0), `confidence`, `is_dismissed`, `crop_url` (computed: `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`, null if no crop). Embedded `suggestions[]` array — each item: `suggested_face_id`, `crop_url` (suggested face's crop or null), `person_name` (nullable, LEFT JOIN), `confidence`. Suggestions always included — pre-computed, no N+1 risk.
  3. Include FaceResource array in existing PhotoResource (eager-loaded faces with suggestions), plus `hidden_face_count` (integer, count of suppressed non-searchable faces for unauthorized viewers — Q-029-10).
- _Commands:_ `make phpstan`
- _Exit:_ Resources compile; PHPStan clean.

### I7 – Person CRUD Endpoints (≈90 min)

- _Goal:_ Implement REST API for Person management with configurable permission mode.
- _Preconditions:_ I6 complete.
- _Steps:_
  1. Write feature tests for: list persons (paginated, filtered by is_searchable), get person, create person, update person, delete person (verify face.person_id nullified). Test both `open` and `restricted` permission modes.
  2. Create PeopleController with index, show, store, update, destroy actions.
  3. Create form requests: StorePerson, UpdatePerson with validation rules.
  4. Implement permission mode middleware/gate: check `ai_vision_face_permission_mode` config to determine authorization rules.
  5. Register routes in api_v2.php.
  6. Verify non-searchable filtering: test that non-admin, non-linked users don't see non-searchable persons. Verify hidden_face_count in photo detail response.
- _Commands:_ `php artisan test --filter=People`, `make phpstan`
- _Exit:_ All CRUD tests green; both permission modes tested; non-searchable filtering + hidden_face_count verified.

### I8 – Person Claim, Admin Override & Merge Endpoints (≈90 min)

- _Goal:_ Implement Person-User claim (1-1) with admin override, selfie-upload claim, and Person merge.
- _Preconditions:_ I7 complete; I2 complete (Python /match endpoint available).
- _Steps:_
  1. Write feature tests: claim person (success), claim already-claimed person (409), admin force-claim (overrides existing), unclaim, merge two persons (faces reassigned, source deleted). Test both permission modes.
  2. Write feature tests for selfie-upload claim: upload selfie → Python service /match returns match → Person linked to User; no face in selfie (422); no match found (404); matched Person already claimed (409). Verify selfie image discarded after match.
  3. Implement ClaimPersonController with `claim` action (user self-claim) and `adminClaim` action (admin override).
  4. Implement SelfieClaimController: accepts multipart image upload, forwards to Python service `POST /match`, processes response, links Person to User, deletes temp selfie.
  5. Implement MergePersonAction: reassign all faces from source to target, delete source.
  6. Register routes.
- _Commands:_ `php artisan test --filter=Person`, `make phpstan`
- _Exit:_ Claim 1-1 enforced; admin override works; selfie claim matches and links; selfie discarded; merge correctly reassigns faces.

### I9 – Face Assignment, Dismiss & Cleanup Endpoints (≈75 min)

- _Goal:_ Face-to-person assignment, false-positive dismissal, and admin cleanup.
- _Preconditions:_ I7 complete.
- _Steps:_
  1. Write feature tests: assign face to existing person, assign face to new person (create Person inline), reassign face. Test both permission modes. *(API-029-09)*
  2. Write feature tests: dismiss face (`is_dismissed = true`), undismiss face, non-owner gets 403; admin hard-delete all dismissed faces and their crop files. *(API-029-14, API-029-16)*
  3. Implement FaceController with: `assign` action, `toggleDismissed` action (PATCH `is_dismissed`; auth: photo owner or admin), `destroyDismissed` action (DELETE all `is_dismissed = true` faces + crop file cleanup; admin-only).
  4. Create form requests: `AssignFaceRequest`, `ToggleDismissedRequest`.
  5. Register routes: `POST /api/v2/Face/{id}/assign`, `PATCH /api/v2/Face/{id}`, `DELETE /api/v2/Face/dismissed`.
  6. Emit telemetry: `face.dismissed`, `face.undismissed` (TE-029-10/11), `face.bulk_deleted` with `deleted_count` (TE-029-12).
- _Commands:_ `php artisan test --filter=Face`, `make phpstan`
- _Exit:_ Assignment, dismiss toggle, and admin bulk-delete all tested and green; crop files deleted on bulk delete.

### I10 – Scan Trigger & Result Ingestion Endpoints (≈90 min)

- _Goal:_ API endpoints for requesting scans and receiving results from the Python service. Includes auto-on-upload trigger and crop storage.
- _Preconditions:_ I5 complete; I3 complete (Python service Dockerized).
- _Steps:_
  1. Write feature tests: trigger scan for photo (202 response), trigger scan for album, receive scan results (Face records created with crop_token), re-scan replaces old faces (old crops deleted), service unavailable (503), auto-scan on upload when enabled. Test both permission modes for scan trigger.
  2. Implement FaceDetectionController with `scan` and `results` actions.
  3. Create DispatchFaceScanJob (queued) — sends HTTP request to Python service `POST /detect` with `photo_path` (filesystem path; no `callback_url` in body — Python reads callback URL from env, Q-029-28). API-029-10 body `photo_ids[]` or `album_id`; dispatch in chunks of `ai_vision_face_scan_batch_size` (default 200, Q-029-45). Sets `face_scan_status = pending` on dispatch.
  4. Create ProcessFaceDetectionResults action — validates X-API-Key, decodes base64 crops and stores at `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg` (Q-029-34), creates Face records with `crop_token`, stores FaceSuggestion rows from `suggestions[]` (Q-029-33). IoU-match old faces on re-scan to preserve `person_id` (Q-029-14/35). Error callback sets `face_scan_status = failed` (Q-029-17).
  5. Register routes (scan trigger: per permission mode; results: service-to-service with API key).
  6. Hook into photo upload pipeline: listener on PhotoSaved event dispatches DispatchFaceScanJob when `ai_vision_face_enabled = 1`.
- _Commands:_ `php artisan test --filter=FaceDetection`, `make phpstan`
- _Exit:_ Scan trigger dispatches job with photo_path; result ingestion creates Face records with crops; auto-on-upload works; service-down returns 503.

### I11 – Bulk Scan Commands & Maintenance Endpoints (≈75 min)

- _Goal:_ CLI commands for admin bulk face scanning and stuck-pending recovery; Maintenance page endpoints.
- _Preconditions:_ I10 complete.
- _Steps:_
  1. Write feature tests for `lychee:scan-faces` command (CLI-029-01/02): scans unscanned photos (`face_scan_status IS NULL`), respects `--album` filter (non-recursive), skips already-scanned photos. *(FR-029-09)*
  2. Implement `lychee:scan-faces` and `lychee:scan-faces --album={id}` commands.
  3. Write feature tests for `lychee:rescan-failed-faces` (CLI-029-03): re-enqueues `failed` photos; with `--stuck-pending --older-than=N` additionally resets `pending` records older than N minutes to `null`. *(Q-029-48)*
  4. Implement `lychee:rescan-failed-faces` command with `--stuck-pending` and `--older-than` options.
  5. Write feature tests and implement `GET /api/v2/Maintenance::resetStuckFaces` (check: returns count of stuck-pending photos older than threshold) and `POST /api/v2/Maintenance::resetStuckFaces` (do: reset them to `null`; body: `older_than_minutes` default 60). Admin-only. Follows existing check/do Maintenance pattern. *(API-029-17/17b, Q-029-48)*
  6. Register Maintenance routes in `api_v2.php`.
- _Commands:_ `php artisan test --filter=ScanFaces`, `php artisan test --filter=Maintenance`, `make phpstan`
- _Exit:_ All CLI commands enqueue correct photos; stuck-pending flag works; Maintenance endpoints return count and reset correctly.

### I12 – Person Photos Endpoint (≈30 min)

- _Goal:_ Paginated endpoint listing all photos containing a given Person.
- _Preconditions:_ I7 complete.
- _Steps:_
  1. Write feature test: get photos for person (paginated), respects album access control.
  2. Implement PersonPhotosController with paginated query through Face→Photo join.
  3. Register route.
- _Commands:_ `php artisan test --filter=PersonPhotos`, `make phpstan`
- _Exit:_ Paginated photos returned; access control respected.

### Phase 3: Frontend (Vue3/TypeScript)

### I13 – Frontend: People Page (≈90 min)

- _Goal:_ New Vue3 page displaying all persons as a grid with server-side crop thumbnails.
- _Preconditions:_ I7 complete (API available).
- _Steps:_
  1. Create People.vue view component.
  2. Create PeopleService.ts for API calls (list persons, get person, etc.).
  3. Create PersonCard.vue component (server-side face crop thumbnail from crop_url, name, photo count).
  4. Add /people route to Vue Router.
  5. Add "People" link to navigation/sidebar.
  6. Handle empty state (no persons detected yet).
  7. Handle service unavailable state (toast notification).
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ People page renders with crop thumbnails; navigation works; empty/error states handled.

### I14 – Frontend: Person Detail Page (≈60 min)

- _Goal:_ Person detail view with photo grid.
- _Preconditions:_ I12, I13 complete.
- _Steps:_
  1. Create PersonDetail.vue view component.
  2. Display person info (name, photo count, linked user, searchability status).
  3. Paginated photo grid (reuse existing photo grid components).
  4. Action buttons: Edit name, Toggle searchable, Merge, Delete.
  5. Add /people/:id route.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Person detail page renders with paginated photos and actions.

### I15 – Frontend: Face Overlays on Photo Detail (≈60 min)

- _Goal:_ Display face bounding boxes on photo detail view with privacy-aware behavior.
- _Preconditions:_ I6 complete (FaceResource + hidden_face_count in photo response).
- _Steps:_
  1. Create FaceOverlay.vue component — renders positioned rectangles from bounding box data.
  2. Integrate into photo detail view (composable for face overlay logic).
  3. Display person name label on each overlay (or "Unknown" for unassigned).
  4. Non-searchable faces: overlays hidden entirely; display "{N} face(s) hidden for privacy" message when `hidden_face_count > 0`.
  5. Click unassigned face → open assignment modal.
  6. Handle responsive sizing (overlays scale with image).
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Face rectangles render correctly; privacy message displayed; labels visible.

### I16 – Frontend: Face Assignment Modal (≈60 min)

- _Goal:_ Modal for assigning unassigned faces to persons.
- _Preconditions:_ I9, I15 complete.
- _Steps:_
  1. Create FaceAssignmentModal.vue component.
  2. Face crop preview (from crop_url), confidence display.
  3. Dropdown to select existing person (with search/filter).
  4. Option to create new person inline.
  5. Call assign API on confirm.
  6. Refresh face overlays after assignment.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Assignment modal works end-to-end; face overlay updates after assignment.

### I17 – Frontend: Scan Trigger UI (≈45 min)

- _Goal:_ UI controls for triggering face detection scans.
- _Preconditions:_ I10 complete.
- _Steps:_
  1. Add "Scan for faces" button to photo context menu.
  2. Add "Scan album for faces" button to album context menu.
  3. Add "Bulk scan" button to admin settings/maintenance page.
  4. Progress indicator during scanning.
  5. Handle service unavailable gracefully.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Scan triggers work from photo, album, and admin contexts.

### I18 – Frontend: Selfie Upload Claim (≈45 min)

- _Goal:_ UI for users to upload a selfie and claim their Person via face matching.
- _Preconditions:_ I8 complete (selfie claim API available).
- _Steps:_
  1. Create SelfieClaimModal.vue component.
  2. File upload area (drag & drop or click) for selfie image.
  3. Send to API-029-13, display matching Person result with confidence and crop thumbnail.
  4. Confirm claim button to link Person to User.
  5. Handle error states: no face detected, no match, already claimed.
  6. Add "Find me in photos" button to user profile page.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Selfie upload → match display → claim confirmation works end-to-end.

### Phase 4: Documentation

### I19 – Documentation & Quality Gate (≈45 min)

- _Goal:_ Final documentation updates and full quality gate pass.
- _Preconditions:_ All previous increments (I1–I18) complete.
- _Steps:_
  1. Update knowledge-map.md with Person, Face models, Python service integration, shared volume architecture.
  2. Update database-schema.md with `persons` and `faces` tables (including crop_token on faces, face_suggestions, face_scan_status on photos).
  3. Create `docs/specs/2-how-to/configure-facial-recognition.md` — Docker setup, shared volume, env vars, permission modes, health check, troubleshooting.
  4. Run full quality gate: `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`.
  5. Update roadmap status to Complete.
- _Commands:_ Full quality gate commands.
- _Exit:_ All gates green; documentation current.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-029-01 | I10 | Face creation from Python results (with crops) |
| S-029-02 | I9 | Face assignment to existing person |
| S-029-03 | I9 | New person creation from face |
| S-029-04 | I8 | Person claim (1-1 link) |
| S-029-05 | I7 | Non-searchable filtering |
| S-029-06 | I11 | Admin bulk scan |
| S-029-07 | I10 | Single photo scan trigger |
| S-029-08 | I10 | Album scan trigger |
| S-029-09 | I10 | Service unavailable handling |
| S-029-10 | I15 | Face overlays on photo (hidden for non-searchable) |
| S-029-11 | I13 | People grid page (with crop thumbnails) |
| S-029-12 | I14 | Person detail with photos |
| S-029-13 | I8 | Person merge |
| S-029-14 | I10 | Re-scan replaces old faces |
| S-029-15 | I7 | Non-searchable privacy enforcement + hidden_face_count |
| S-029-16 | I8 | Claim conflict (409) |
| S-029-17 | I5 | Photo delete cascades to faces |
| S-029-18 | I7 | Person delete nullifies face.person_id |
| S-029-19 | I8 | Admin force-link override |
| S-029-20 | I8 | Selfie-upload claim match (selfie discarded) |
| S-029-21 | I8 | Selfie no face detected (422) |
| S-029-22 | I8 | Selfie no matching Person (404) |
| S-029-23 | I10 | Auto-scan on upload |
| S-029-24 | I9 | Face dismissed (is_dismissed toggle) |
| S-029-25 | I9 | Admin hard-deletes all dismissed faces + crop files |
| S-029-26 | I11 | Admin resets stuck-pending photos via Maintenance endpoint |

## Analysis Gate

_To be completed after spec, plan, and tasks align and before implementation begins._

## Exit Criteria

- [ ] All 19 increments (I1–I19) complete with passing tests.
- [ ] PHPStan 0 errors.
- [ ] php-cs-fixer clean.
- [ ] npm run check / npm run format clean.
- [ ] Knowledge map updated.
- [ ] Database schema docs updated.
- [ ] How-to guide for facial recognition configuration published.
- [ ] All spec scenarios covered by at least one test.
- [ ] Non-searchable privacy verified by dedicated tests.
- [ ] Lychee fully functional when Python service is unavailable.

## Follow-ups / Backlog

- Auto-clustering UI — Python service clusters via `POST /cluster` (DBSCAN offline batch); dedicated cluster-review/confirm workflow is a future enhancement beyond manual assignment. *(Q-029-30 resolved)*
- Face recognition accuracy tuning and confidence threshold configuration (admin UI for `VISION_FACE_DETECTION_THRESHOLD` / `VISION_FACE_MATCH_THRESHOLD`).
- Notifications when a user is tagged in a new photo.
- Performance optimisation for large Person/Face datasets (materialized views, caching face counts).
- GPU acceleration for the Python service (optional CUDA/ROCm support in Dockerfile).
- Cluster review UI — surface DBSCAN group results for bulk confirmation by admin.
