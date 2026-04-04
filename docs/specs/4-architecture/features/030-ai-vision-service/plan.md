# Feature Plan 030 – Facial Recognition

_Linked specification:_ `docs/specs/4-architecture/features/030-ai-vision-service/spec.md`
_Status:_ Draft
_Last updated:_ 2026-04-04

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs have been updated.

## Vision & Success Criteria

Enable Lychee users to browse their photo library by the people who appear in them. A Python-based facial recognition service (separate container) detects faces; Lychee stores the results, provides management UI, and respects privacy preferences.

**Success signals:**
- Person and Face models created with correct relationships and migrations.
- API endpoints for CRUD operations on Person/Face, scan triggering, and result ingestion pass feature tests.
- People page renders persons with photo counts; photo detail shows face overlays.
- Non-searchable persons are invisible to unauthorized users (verified by tests).
- Lychee functions normally when the Python service is unavailable (NFR-030-03).

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
  - **Face dismiss UX**: Dismiss button in modal + CTRL+click shortcut on overlays *(Q-030-54)*.
  - **Maintenance blocks**: Destroy dismissed faces, reset stuck/failed scans with conditional visibility *(Q-030-55)*.
  - **Batch face operations**: Multi-select faces, unassign/reassign/uncluster *(Q-030-56, Q-030-58)*.
  - **Unassign face from person**: Return face to unassigned pool *(Q-030-57)*.
  - **Person miniature in dropdowns**: Circular crop in assignment/merge dropdowns *(Q-030-59)*.
  - **Face circles in detail panel**: Photo sidebar shows circular face crops with click/CTRL+click interactions *(Q-030-60)*.
  - **Face overlay config**: Global enable/disable toggle + default visibility setting + P-key toggle *(Q-030-61)*.
  - **Album people endpoint**: List persons found in an album *(Q-030-62)*.
  - **Merge person UI**: Modal with person search and miniatures *(Q-030-58)*.

- **Out of scope:**
  - Training custom face recognition models (use pre-trained models like InsightFace/dlib/face_recognition).
  - Per-user face overlay preferences (deferred — currently global config only).
  - Policy refinement for album/photo edit rights cross-check (deferred — Q-030-63).

## Dependencies & Interfaces

- **Photo model** — Face belongs to Photo; cascade delete required.
- **User model** — Person optionally linked to User (1-1).
- **AccessPermission / PhotoQueryPolicy** — Person photo listing must respect existing album access control.
- **Laravel Queue** — Scan requests dispatched as jobs for async processing.
- **Python service** — External dependency; communicates via REST + webhook callbacks (Q-030-01 resolved). Contract defined in spec appendix.
- **PrimeVue** — Frontend components for People page, modals, overlays.

## Assumptions & Risks

- **Assumptions:**
  - The Python service will conform to the JSON contract defined in the spec appendix.
  - Photos are accessible to the Python service via shared Docker volume (Q-030-07 resolved).
  - Face detection results include bounding boxes as relative coordinates (0.0–1.0).

- **Risks / Mitigations:**
  - *Python service API changes:* Mitigated by versioned contract schema (NFR-030-05).
  - *Large libraries overwhelm scan queue:* Mitigated by rate limiting bulk scans and progress tracking.
  - *Privacy leaks through non-searchable persons:* Mitigated by query scopes tested at unit and feature level (NFR-030-04).

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

> **Implementation order: Python service first (I1–I3), then PHP/Lychee backend (I4–I12), then frontend (I13–I18), then docs (I19), then clustering & embedding sync (I20–I22), then face UX enhancements (I23–I30).**

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
  3. Environment variable configuration via Pydantic `AppSettings` (`VISION_FACE_`-prefixed): `VISION_FACE_LYCHEE_API_URL`, `VISION_FACE_API_KEY`, `VISION_FACE_MODEL_NAME`, `VISION_FACE_DETECTION_THRESHOLD` (bounding box filter), `VISION_FACE_MATCH_THRESHOLD` (similarity search cutoff), `VISION_FACE_RESCAN_IOU_THRESHOLD` (IoU on re-scan), `VISION_FACE_MAX_FACES_PER_PHOTO` (default 10), `VISION_FACE_THREAD_POOL_SIZE`, `VISION_FACE_STORAGE_BACKEND`, `VISION_FACE_STORAGE_PATH`, `VISION_FACE_PHOTOS_PATH`, `VISION_FACE_WORKERS`, `VISION_FACE_LOG_LEVEL`.
  4. Startup: FastAPI lifespan handler loads `buffalo_l` model (baked into image at build time; no download on first run — Q-030-32 resolved). Workers count exposed via CMD shell form to honour `VISION_FACE_WORKERS` env var.
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
  2. Create migration for `faces` table: `id` (string PK), `photo_id` (string, FK→photos ON DELETE CASCADE), `person_id` (nullable string, FK→persons ON DELETE SET NULL), `x` / `y` / `width` / `height` (float, 0.0–1.0), `confidence` (float), `crop_token` (nullable string — random high-entropy token; file stored at `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`, served nginx-direct; Q-030-34), `is_dismissed` (boolean, default false), timestamps. Indexes on `photo_id`, `person_id`.
  3. Create migration for `face_suggestions` table: `face_id` (string, FK→faces CASCADE), `suggested_face_id` (string, FK→faces CASCADE), `confidence` (float); unique constraint on `(face_id, suggested_face_id)`. *(DO-030-05, Q-030-33)*
  4. Add `face_scan_status` nullable `VARCHAR(16)` column to `photos` table. *(DO-030-06, Q-030-38)*
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
  5. Implement Face model with relationships, bounding box accessors, `crop_token`, `is_dismissed` (boolean), fillable fields; `ScanStatus` PHP Enum cast on `face_scan_status` (photos model, Q-030-38).
  6. Implement FaceSuggestion model: `face_id`, `suggested_face_id`, `confidence`; `face()` / `suggestedFace()` belongsTo. *(DO-030-05)*
  7. Add `faces()` relationship (hasMany Face) and `faceSuggestions()` through Face to Photo model.
  8. Add `person()` relationship to User model (hasOne Person).
- _Commands:_ `php artisan test --filter=Person`, `php artisan test --filter=Face`
- _Exit:_ All relationship tests green; PHPStan clean.

### I6 – Spatie Data Resources (≈30 min)

- _Goal:_ Create PersonResource and FaceResource for API responses.
- _Preconditions:_ I5 complete.
- _Steps:_
  1. Create PersonResource (Spatie Data): `id`, `name`, `user_id`, `is_searchable`, `face_count`, `photo_count`, `representative_crop_url`.
  2. Create FaceResource (Spatie Data) per DO-030-04 *(Q-030-46)*: `id` (Face ID), `photo_id`, `person_id` (nullable), `x` / `y` / `width` / `height` (float 0.0–1.0), `confidence`, `is_dismissed`, `crop_url` (computed: `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`, null if no crop). Embedded `suggestions[]` array — each item: `suggested_face_id`, `crop_url` (suggested face's crop or null), `person_name` (nullable, LEFT JOIN), `confidence`. Suggestions always included — pre-computed, no N+1 risk.
  3. Include FaceResource array in existing PhotoResource (eager-loaded faces with suggestions), plus `hidden_face_count` (integer, count of suppressed non-searchable faces for unauthorized viewers — Q-030-10).
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
  1. Write feature tests: assign face to existing person, assign face to new person (create Person inline), reassign face. Test both permission modes. *(API-030-09)*
  2. Write feature tests: dismiss face (`is_dismissed = true`), undismiss face, non-owner gets 403; admin hard-delete all dismissed faces and their crop files. *(API-030-14, API-030-16)*
  3. Implement FaceController with: `assign` action, `toggleDismissed` action (PATCH `is_dismissed`; auth: photo owner or admin), `destroyDismissed` action (DELETE all `is_dismissed = true` faces + crop file cleanup; admin-only).
  4. Create form requests: `AssignFaceRequest`, `ToggleDismissedRequest`.
  5. Register routes: `POST /api/v2/Face/{id}/assign`, `PATCH /api/v2/Face/{id}`, `DELETE /api/v2/Face/dismissed`.
  6. Emit telemetry: `face.dismissed`, `face.undismissed` (TE-030-10/11), `face.bulk_deleted` with `deleted_count` (TE-030-12).
- _Commands:_ `php artisan test --filter=Face`, `make phpstan`
- _Exit:_ Assignment, dismiss toggle, and admin bulk-delete all tested and green; crop files deleted on bulk delete.

### I10 – Scan Trigger & Result Ingestion Endpoints (≈90 min)

- _Goal:_ API endpoints for requesting scans and receiving results from the Python service. Includes auto-on-upload trigger and crop storage.
- _Preconditions:_ I5 complete; I3 complete (Python service Dockerized).
- _Steps:_
  1. Write feature tests: trigger scan for photo (202 response), trigger scan for album, receive scan results (Face records created with crop_token), re-scan replaces old faces (old crops deleted), service unavailable (503), auto-scan on upload when enabled. Test both permission modes for scan trigger.
  2. Implement FaceDetectionController with `scan` and `results` actions.
  3. Create DispatchFaceScanJob (queued) — sends HTTP request to Python service `POST /detect` with `photo_path` (filesystem path; no `callback_url` in body — Python reads callback URL from env, Q-030-28). API-030-10 body `photo_ids[]` or `album_id`; dispatch in chunks of `ai_vision_face_scan_batch_size` (default 200, Q-030-45). Sets `face_scan_status = pending` on dispatch.
  4. Create ProcessFaceDetectionResults action — validates X-API-Key, decodes base64 crops and stores at `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg` (Q-030-34), creates Face records with `crop_token`, stores FaceSuggestion rows from `suggestions[]` (Q-030-33). IoU-match old faces on re-scan to preserve `person_id` (Q-030-14/35). Error callback sets `face_scan_status = failed` (Q-030-17).
  5. Register routes (scan trigger: per permission mode; results: service-to-service with API key).
  6. Hook into photo upload pipeline: listener on PhotoSaved event dispatches DispatchFaceScanJob when `ai_vision_face_enabled = 1`.
- _Commands:_ `php artisan test --filter=FaceDetection`, `make phpstan`
- _Exit:_ Scan trigger dispatches job with photo_path; result ingestion creates Face records with crops; auto-on-upload works; service-down returns 503.

### I11 – Bulk Scan Commands & Maintenance Endpoints (≈75 min)

- _Goal:_ CLI commands for admin bulk face scanning and stuck-pending recovery; Maintenance page endpoints.
- _Preconditions:_ I10 complete.
- _Steps:_
  1. Write feature tests for `lychee:scan-faces` command (CLI-030-01/02): scans unscanned photos (`face_scan_status IS NULL`), respects `--album` filter (non-recursive), skips already-scanned photos. *(FR-030-09)*
  2. Implement `lychee:scan-faces` and `lychee:scan-faces --album={id}` commands.
  3. Write feature tests for `lychee:rescan-failed-faces` (CLI-030-03): re-enqueues `failed` photos; with `--stuck-pending --older-than=N` additionally resets `pending` records older than N minutes to `null`. *(Q-030-48)*
  4. Implement `lychee:rescan-failed-faces` command with `--stuck-pending` and `--older-than` options.
  5. Write feature tests and implement `GET /api/v2/Maintenance::resetStuckFaces` (check: returns count of stuck-pending photos older than threshold) and `POST /api/v2/Maintenance::resetStuckFaces` (do: reset them to `null`; body: `older_than_minutes` default 60). Admin-only. Follows existing check/do Maintenance pattern. *(API-030-17/17b, Q-030-48)*
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

### I20 – Clustering Endpoint: Python `POST /cluster` + PHP Ingestion & Trigger (≥90 min)

- _Goal:_ Wire up the existing `FaceClusterer` (DBSCAN) to a FastAPI REST endpoint and complete the round-trip: Python runs clustering, posts suggestion pairs to Lychee, Lychee bulk-upserts `face_suggestions`. Admin can trigger clustering via a Maintenance API action.
- _Preconditions:_ I2 complete (`FaceClusterer` and `EmbeddingStore` implemented); I10 complete (`face_suggestions` table exists, PHP ingestion pipeline in place); I11 complete (Maintenance endpoint pattern established).
- _Steps:_
  1. **Python** — Add `ClusterResponse` Pydantic schema (`{clusters: int, suggestions_generated: int}`) to `app/api/schemas.py`. Add `VISION_FACE_CLUSTER_EPS` env var to `AppSettings` (default `0.6`).
  2. **Python** — Extend `app/clustering/clusterer.py` with `run_cluster_and_notify(store: EmbeddingStore, lychee_url: str, api_key: str) -> ClusterResponse`: reads all embeddings from store, runs DBSCAN, produces (a) a `labels` list — `[{face_id: str, cluster_label: int}]` for every non-noise face; (b) a `suggestions` list — `(face_id, suggested_face_id, confidence)` pairs for every intra-cluster pair (cosine similarity as confidence). POSTs `{labels: [...], suggestions: [...]}` to `{lychee_url}/api/v2/FaceDetection/cluster-results` with `X-API-Key` header. *(Q-030-49)*
  3. **Python** — Add `POST /cluster` route to `app/api/routes.py` (X-API-Key auth); calls `run_cluster_and_notify()`; returns `ClusterResponse`. Add unit + integration tests in `tests/test_clustering.py` (mock httpx POST to Lychee).
  4. **PHP** — Implement `POST /api/v2/FaceDetection/cluster-results` endpoint in `FaceDetectionController` (or a new `FaceClusterResultsController`): auth via X-API-Key, validate body `{suggestions: [{face_id, suggested_face_id, confidence}]}`, bulk-upsert `face_suggestions` rows (upsert on `(face_id, suggested_face_id)`, update `confidence`), return `{updated_count: N}`. Register route.
  5. **PHP** — Add `POST /api/v2/Maintenance::runFaceClustering` Maintenance endpoint (admin-only, follows existing check/do pattern): calls Python service `POST /cluster` via HTTP, returns 202 Accepted. Register route.
  6. Write PHP feature tests for cluster-results ingestion (success, invalid API key, malformed body) and for the Maintenance trigger (success, service unavailable 503).
- _Commands:_ `uv run pytest tests/test_clustering.py`, `php artisan test --filter=FaceCluster`, `make phpstan`
- _Exit:_ `POST /cluster` on Python service triggers DBSCAN and POSTs pairs to Lychee; `POST /FaceDetection/cluster-results` bulk-upserts face_suggestions; Maintenance trigger returns 202; all tests green.

### I21 – Embedding Sync on Deletion + Blur Threshold Filtering (≈60 min)

- _Goal:_ Prevent stale embeddings from corrupting clustering/suggestions after face hard-deletes; discard blurry faces before they ever reach Lychee.
- _Preconditions:_ I2 complete (EmbeddingStore implemented); I9 complete (`destroyDismissed` action exists); Photo model cascade delete exists.
- _Steps:_
  1. **Python** — Add `VISION_FACE_BLUR_THRESHOLD` (float, default `100.0`) to `AppSettings` in `app/config.py`. In `app/detection/detector.py`, after cropping each detected face region, compute its Laplacian variance using OpenCV/NumPy (`cv2.Laplacian(crop, cv2.CV_64F).var()`); exclude faces whose variance is below `VISION_FACE_BLUR_THRESHOLD`. Also add `VISION_FACE_CLUSTER_EPS` to `AppSettings` (default `0.6`) if not already present from I20.
  2. **Python** — Add `DELETE /embeddings` route to `app/api/routes.py` (X-API-Key auth): accepts `{face_ids: [str]}`, calls `EmbeddingStore.delete_many(face_ids)`, returns `{deleted_count: int}`. Add `delete_many()` method to the `EmbeddingStore` protocol and both implementations (`SQLiteStore`, `PgVectorStore`). IDs not found are silently ignored.
  3. **Python** — Add tests: `tests/test_detection.py` — blurry face below threshold not returned; sharp face above threshold returned. `tests/test_api.py` — `DELETE /embeddings` removes embeddings and returns count.
  4. **PHP** — Create `DeleteFaceEmbeddingsJob` (queued): accepts `array<string> $faceIds`, calls Python `DELETE /embeddings` via HTTP with `X-API-Key`; logs warning on failure, never throws. Dispatch this job **after** `destroyDismissed` deletes Face records (FR-030-14, S-030-28).
  5. **PHP** — Add `Face` model observer or hook into `Photo` cascade: after a Photo delete triggers Face cascade deletes, collect deleted face IDs and dispatch `DeleteFaceEmbeddingsJob` (S-030-29).
  6. **PHP** — Write feature tests: `DELETE /Face/dismissed` → embeddings deleted (job dispatched with correct IDs); Photo delete → embeddings deleted; Python unavailable → Lychee deletion still succeeds, warning logged.
- _Commands:_ `uv run pytest tests/test_detection.py tests/test_api.py`, `php artisan test --filter=FaceEmbeddingSync`, `make phpstan`
- _Exit:_ Blurry faces never reach Lychee; dismissed/cascade-deleted Face embeddings are removed from the store; service unavailability does not block Lychee-side deletions.

### I22 – Cluster Review UI: Browse & Bulk-Name/Dismiss Clusters (≥90 min)

- _Goal:_ Give authorized users a dedicated page to review DBSCAN-produced face clusters (visually similar unassigned faces) and resolve them in bulk — either creating a Person and assigning all faces in one action, or dismissing the whole cluster as false-positives.
- _Preconditions:_ I20 complete (`face_suggestions` populated by clustering); I9 complete (dismiss action exists); I7 complete (Person create + face assign available).
- _Steps:_
  1. **PHP** — Add migration for `cluster_label INT NULL` column + composite index `(cluster_label, person_id, is_dismissed)` on `faces` (DO-030-07, Q-030-49). Implement `GET /api/v2/FaceDetection/clusters` (API-030-18): `SELECT cluster_label, COUNT(*) as size FROM faces WHERE cluster_label IS NOT NULL AND person_id IS NULL AND is_dismissed = false GROUP BY cluster_label ORDER BY cluster_label LIMIT/OFFSET`; load preview faces per cluster; return `{cluster_id: int, size: int, faces: FaceResource[]}`. `cluster_id` = integer `cluster_label` value. Respect `ai_vision_face_permission_mode` visibility rules.
  2. **PHP** — Implement `POST /api/v2/FaceDetection/clusters/{cluster_id}/assign` (API-030-19): resolve cluster faces, create Person if `new_person_name` provided, bulk-update `face.person_id` for all faces in cluster, emit `face.cluster_assigned` telemetry. Return `{person_id, assigned_count}`.
  3. **PHP** — Implement `POST /api/v2/FaceDetection/clusters/{cluster_id}/dismiss` (API-030-20): bulk-set `is_dismissed = true` for all faces in cluster, emit `face.cluster_dismissed` telemetry. Return `{dismissed_count}`.
  4. Write feature tests: list clusters (unassigned faces grouped, assigned faces excluded), assign cluster (new person + faces linked), dismiss cluster (all faces dismissed). Test permission mode enforcement.
  5. **Frontend** — Create `FaceClusters.vue` page at `/people/clusters`. Paginated grid of cluster cards: first 5 face-crop thumbnails + "+N more" overflow badge, cluster size badge, name input, “Create Person & Assign All” button, “Dismiss” button. “Run Cluster” button in page header calls `POST /Maintenance::runFaceClustering` then refreshes. Empty state when no clusters exist.
  6. Add route `/people/clusters` to Vue Router; add “Clusters” navigation link under People in sidebar.
- _Commands:_ `php artisan test --filter=FaceClusterReviewTest`, `make phpstan`, `npm run check`
- _Exit:_ Clusters page shows unassigned face groups; bulk-assign creates Person and links all faces; bulk-dismiss marks all is_dismissed; “Run Cluster” triggers fresh clustering; all tests green.

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
  3. Send to API-030-13, display matching Person result with confidence and crop thumbnail.
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

### Phase 5: Face UX Enhancements (Q-030-54 through Q-030-64)

### I23 – Face Dismiss UX: Modal Button + CTRL+Click Overlay (≈60 min)

- _Goal:_ Add dismiss functionality to the FaceAssignmentModal and CTRL+click shortcut on face overlays.
- _Preconditions:_ I15 (FaceOverlay.vue) and I16 (FaceAssignmentModal.vue) complete.
- _Steps:_
  1. **Frontend** — Add "Dismiss" button to FaceAssignmentModal.vue. Clicking calls `PATCH /Face/{id}` to set `is_dismissed = true`, then closes the modal and refreshes overlays. *(FR-030-16)*
  2. **Frontend** — In FaceOverlay.vue, listen for CTRL `keydown`/`keyup` events on `window`. When CTRL is held, switch all face rectangle CSS to red dashed borders (`border: 2px dashed red`). When a rectangle is clicked in CTRL state, call `PATCH /Face/{id}` directly (no modal). After dismiss, remove the overlay element.
  3. Write JS unit test: CTRL state toggles overlay CSS classes; click in CTRL state fires dismiss API call.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Dismiss button works in modal; CTRL+click dismiss works on overlays; visual feedback correct; tests green.

### I24 – Face Overlay Config Settings & P-Key Toggle (≈45 min)

- _Goal:_ Add config settings for face overlay enable/disable and default visibility; map P key to toggle.
- _Preconditions:_ I4 (config migration pattern established), I15 (FaceOverlay.vue exists).
- _Steps:_
  1. **PHP** — Add config migration for `ai_vision_face_overlay_enabled` (0|1, default 1) and `ai_vision_face_overlay_default_visibility` (string: `visible`|`hidden`, default `visible`) to the AI Vision category. *(NFR-030-11)*
  2. **Frontend** — In FaceOverlay.vue, gate overlay rendering on `ai_vision_face_overlay_enabled` config. Initialize overlay visibility from `ai_vision_face_overlay_default_visibility`.
  3. **Frontend** — Register `P` key handler (on photo view) to toggle overlay visibility. Ensure no conflict with existing key bindings (check Q-030-65).
  4. Write PHP migration test; verify config values accessible.
- _Commands:_ `php artisan test`, `npm run check`, `npm run format`
- _Exit:_ Overlay disabled when config is off; default visibility respected; P key toggles; no key binding conflicts.

### I25 – Face Circles in Photo Detail Panel (≈60 min)

- _Goal:_ Display circular face crop thumbnails in the photo details sidebar with click/CTRL+click interactions.
- _Preconditions:_ I15 (face overlays), I16 (FaceAssignmentModal), I23 (CTRL+click dismiss).
- _Steps:_
  1. **Frontend** — Add "People in this photo" section to `PhotoDetails.vue`. Render a horizontal flex row of circular face crop images (48px, `border-radius: 50%`) with person name label below each. Unassigned faces show "???". Hidden when photo has no faces or when `ai_vision_face_overlay_enabled = 0`.
  2. **Frontend** — Click on a face circle → open FaceAssignmentModal for that face.
  3. **Frontend** — CTRL+click on a face circle → dismiss face directly (same pattern as I23).
  4. Handle overflow: horizontal scroll with "+N more" indicator when faces exceed container width.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Face circles render in detail panel; click/CTRL+click interactions work; overflow handled; tests green.

### I26 – Batch Face Operations: API + Frontend (≈90 min)

- _Goal:_ Implement batch face selection, unassign, reassign, uncluster operations in both backend and frontend.
- _Preconditions:_ I9 (face assignment), I22 (cluster review), I14 (person detail).
- _Steps:_
  1. **PHP** — Implement `POST /api/v2/Face/batch` endpoint in FaceController. Body: `{face_ids: [str], action: "unassign"|"assign", person_id?: str, new_person_name?: str}`. Validation: face_ids non-empty, action valid, person_id or new_person_name for assign. Auth: check assign permission for each face. Returns `{affected_count, person_id?}`. *(FR-030-19, API-030-24)*
  2. **PHP** — Implement `POST /api/v2/FaceDetection/clusters/{cluster_id}/uncluster` endpoint. Body: `{face_ids: [str]}`. Sets `cluster_label = NULL` for qualifying faces. Returns `{unclustered_count}`. *(FR-030-17, API-030-23)*
  3. **PHP** — Write feature tests: batch unassign, batch assign to existing person, batch assign to new person, uncluster faces, auth checks.
  4. **Frontend** — Add "Select Mode" toggle button to PersonDetail.vue and FaceClusters.vue. When active, checkbox overlays appear on each face crop.
  5. **Frontend** — Add action bar (slides in at bottom when faces selected): "Unassign (N)", "Reassign to...", "Assign to new person", "Uncluster" (cluster view only). Each calls the corresponding API.
  6. Create `FaceBatchService.ts` with typed functions.
- _Commands:_ `php artisan test --filter=FaceBatch`, `make phpstan`, `npm run check`
- _Exit:_ Batch operations work end-to-end; select mode activates cleanly; action bar renders; all tests green.

### I27 – Maintenance Blocks: Dismiss Cleanup + Reset Failed/Stuck Scans (≈60 min)

- _Goal:_ Add conditional maintenance blocks for face cleanup and scan reset operations.
- _Preconditions:_ I9 (dismiss exists), I11 (stuck reset exists), maintenance pattern established.
- _Steps:_
  1. **PHP** — Implement `DestroyDismissedFaces` maintenance controller with check/do pattern. `check()` returns count of `Face::where('is_dismissed', true)->count()`. `do()` reuses `destroyDismissed` logic from FaceController. *(FR-030-23, API-030-21/21b)*
  2. **PHP** — Implement `ResetFailedFaceScans` maintenance controller with check/do. `check()` returns count of `Photo::where('face_scan_status', FaceScanStatus::FAILED)->count()`. `do()` sets `face_scan_status = NULL` on those photos. *(FR-030-24, API-030-22/22b)*
  3. **PHP** — Register maintenance routes in `api_v2.php`. Write feature tests for both check/do endpoints.
  4. **Frontend** — Create `MaintenanceDestroyDismissedFaces.vue` and `MaintenanceResetFailedFaceScans.vue` maintenance card components. Each calls check endpoint on mount and hides when count is 0. Follow existing `MaintenanceBulkScanFaces.vue` pattern.
  5. **Frontend** — Add new maintenance cards to `Maintenance.vue` template.
- _Commands:_ `php artisan test --filter=Maintenance`, `make phpstan`, `npm run check`
- _Exit:_ Both maintenance blocks appear conditionally; check returns correct counts; do performs cleanup; cards hidden when count is 0.

### I28 – Merge Person UI + Person Miniature in Dropdown (≈60 min)

- _Goal:_ Implement merge person modal and add person miniatures to the face assignment dropdown.
- _Preconditions:_ I8 (merge backend), I16 (FaceAssignmentModal), I14 (PersonDetail).
- _Steps:_
  1. **Frontend** — Create `MergePersonModal.vue`. Triggered by "Merge into..." button on PersonDetail.vue. Shows source person info, PrimeVue Dropdown with person search (custom option template: 24px circular miniature + name + face count), warning text about merge consequences, Cancel/Merge buttons. On confirm, calls `POST /Person/{id}/merge`. *(FR-030-25)*
  2. **Frontend** — Update FaceAssignmentModal.vue dropdown to use custom `option` template with circular miniature (from `representative_crop_url`), person name, and face count. Use PrimeVue Dropdown's `optionLabel` slot. Fallback placeholder icon when no crop exists. *(FR-030-20)*
  3. After merge, redirect from source person page to target person page.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Merge modal opens from PersonDetail; person search works with miniatures; merge executes and redirects; assignment dropdown shows miniatures.

### I29 – Album People Endpoint (≈45 min)

- _Goal:_ New endpoint returning persons found in a given album.
- _Preconditions:_ I7 (People CRUD), photo_albums relationship exists.
- _Steps:_
  1. **PHP** — Implement `AlbumPeopleController` with `index()` action. `GET /api/v2/Album/{id}/people`: query `SELECT DISTINCT persons.* FROM persons JOIN faces ... JOIN photo_albums WHERE photo_albums.album_id = ?` (non-recursive, direct photos only). Return `PaginatedPersonsResource`. Respect `ai_vision_face_permission_mode` and `is_searchable` filtering. *(FR-030-22, API-030-25)*
  2. **PHP** — Create `AlbumPeopleRequest` form request; validate album_id exists, user has album access.
  3. **PHP** — Write feature tests: album with persons returns correct list; empty album returns empty; non-searchable filtering; access control.
  4. **PHP** — Register route in `api_v2.php`.
- _Commands:_ `php artisan test --filter=AlbumPeople`, `make phpstan`
- _Exit:_ Album people endpoint returns correct persons; pagination works; access control verified.

### I30 – Unassign Face from Person (≈30 min)

- _Goal:_ Allow unassigning a face from a person, returning it to the unassigned pool.
- _Preconditions:_ I9 (face assignment API).
- _Steps:_
  1. **PHP** — Update `POST /Face/{id}/assign` to accept `person_id: null` (or empty string) as a valid value that unassigns the face (sets `face.person_id = NULL`). Emit `face.unassigned` telemetry. *(FR-030-18, API-030-25)*
  2. **PHP** — Write feature test: assign face, then unassign (person_id = null); verify face returns to unassigned state.
  3. **Frontend** — In PersonDetail.vue (non-batch mode), add "Remove" button or context menu option on each face crop that calls assign with `person_id: null`.
- _Commands:_ `php artisan test --filter=FaceAssignment`, `make phpstan`, `npm run check`
- _Exit:_ Face unassign works via API; PersonDetail UI allows removal; face returns to unassigned pool.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-030-01 | I10 | Face creation from Python results (with crops) |
| S-030-02 | I9 | Face assignment to existing person |
| S-030-03 | I9 | New person creation from face |
| S-030-04 | I8 | Person claim (1-1 link) |
| S-030-05 | I7 | Non-searchable filtering |
| S-030-06 | I11 | Admin bulk scan |
| S-030-07 | I10 | Single photo scan trigger |
| S-030-08 | I10 | Album scan trigger |
| S-030-09 | I10 | Service unavailable handling |
| S-030-10 | I15 | Face overlays on photo (hidden for non-searchable) |
| S-030-11 | I13 | People grid page (with crop thumbnails) |
| S-030-12 | I14 | Person detail with photos |
| S-030-13 | I8 | Person merge |
| S-030-14 | I10 | Re-scan replaces old faces |
| S-030-15 | I7 | Non-searchable privacy enforcement + hidden_face_count |
| S-030-16 | I8 | Claim conflict (409) |
| S-030-17 | I5 | Photo delete cascades to faces |
| S-030-18 | I7 | Person delete nullifies face.person_id |
| S-030-19 | I8 | Admin force-link override |
| S-030-20 | I8 | Selfie-upload claim match (selfie discarded) |
| S-030-21 | I8 | Selfie no face detected (422) |
| S-030-22 | I8 | Selfie no matching Person (404) |
| S-030-23 | I10 | Auto-scan on upload |
| S-030-24 | I9 | Face dismissed (is_dismissed toggle) |
| S-030-25 | I9 | Admin hard-deletes all dismissed faces + crop files |
| S-030-26 | I11 | Admin resets stuck-pending photos via Maintenance endpoint |
| S-030-27 | I20 | Admin triggers clustering; suggestion pairs ingested into face_suggestions |
| S-030-28 | I21 | Admin hard-deletes dismissed faces; embeddings deleted from Python store |
| S-030-29 | I21 | Photo deleted; Face cascade-deleted; embeddings deleted from Python store |
| S-030-30 | I21 | Blurry face below VISION_FACE_BLUR_THRESHOLD excluded from detection callback |
| S-030-31 | I22 | Cluster Review page: admin names cluster → Person created, all faces assigned |
| S-030-32 | I22 | Cluster Review page: admin dismisses cluster → all faces marked is_dismissed |
| S-030-33 | I23 | Dismiss button in FaceAssignmentModal |
| S-030-34 | I23 | CTRL+click dismiss on face overlays (red dashed borders) |
| S-030-35 | I26 | Uncluster selected faces from a cluster |
| S-030-36 | I30 | Unassign face from person (person_id = NULL) |
| S-030-37 | I26 | Batch select + reassign faces in person detail |
| S-030-38 | I25 | Face circles in photo detail panel (click → modal) |
| S-030-39 | I25 | CTRL+click face circle in detail panel → dismiss |
| S-030-40 | I29 | Album people endpoint returns persons in album |
| S-030-41 | I24 | P key toggles face overlay visibility |
| S-030-42 | I27 | Maintenance destroy dismissed faces block |
| S-030-43 | I27 | Maintenance reset failed face scans block |
| S-030-44 | I24 | Face overlay disabled when config is off |
| S-030-45 | I28 | Person merge from UI with merge modal |
| S-030-46 | I28 | Person miniature in face assignment dropdown |

## Analysis Gate

_To be completed after spec, plan, and tasks align and before implementation begins._

## Exit Criteria

- [ ] All 30 increments (I1–I30) complete with passing tests.
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

- Face recognition accuracy tuning and confidence threshold configuration (admin UI for `VISION_FACE_DETECTION_THRESHOLD` / `VISION_FACE_MATCH_THRESHOLD`).
- Notifications when a user is tagged in a new photo.
- Performance optimisation for large Person/Face datasets (materialized views, caching face counts).
- GPU acceleration for the Python service (optional CUDA/ROCm support in Dockerfile).
- Per-user face overlay visibility preference (currently global config only — Q-030-61).
- Policy refinement: cross-check album/photo edit rights in AiVisionPolicy (Q-030-63, deferred).
