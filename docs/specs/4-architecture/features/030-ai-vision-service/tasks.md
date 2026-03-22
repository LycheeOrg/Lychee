# Feature 030 Tasks – Facial Recognition

_Status: Draft_
_Last updated: 2026-03-21_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### Phase 1: Python Facial Recognition Service

### I1 – Python Service: Project Setup & Face Detection

- [ ] T-030-01 – Create Python service project structure with uv, ruff, ty.
  _Intent:_ Create `ai-vision-service/` directory with: `pyproject.toml` (uv project config, ruff settings, ty config), `app/` (main application with `__init__.py`), `app/detection/`, `app/embeddings/`, `app/api/`, `app/clustering/`, `app/matching/`, `tests/`, `Dockerfile`, `README.md`. Configure ruff lint rules (E, W, F, I, N, UP, ANN, B, A, SIM, TCH, RUF) and ty in `pyproject.toml`. Create Pydantic `AppSettings` (BaseSettings) in `app/config.py` with all `VISION_FACE_`-prefixed env vars. Create Pydantic request/response schemas in `app/api/schemas.py`: `DetectRequest`, `FaceResult`, `DetectCallbackPayload`, `MatchResult`, `MatchResponse`, `HealthResponse`. All code fully type-annotated.
  _Verification commands:_
  - `uv sync`
  - `uv run ruff format --check`
  - `uv run ruff check`
  - `uv run ty check`

- [ ] T-030-02 – Implement face detection and crop generation with InsightFace.
  _Intent:_ `app/detection/detector.py`: typed wrapper around InsightFace (ONNX Runtime backend, `buffalo_l` model). Accept photo filesystem path (shared Docker volume — Q-030-07 resolved), return list of `FaceResult` with bounding box coordinates as 0.0–1.0 relative values and confidence scores. `app/detection/cropper.py`: generate 150x150px JPEG face crop per detected face using Pillow, returned as base64 string (Q-030-09 resolved: server-side crop). Full type annotations; no `Any` types.
  _Verification commands:_
  - `uv run pytest tests/test_detection.py tests/test_cropper.py`
  - `uv run ty check`

- [ ] T-030-03 – Implement embedding generation and storage layer.
  _Intent:_ `app/embeddings/store.py`: abstract `EmbeddingStore` protocol (typed). `app/embeddings/sqlite_store.py`: SQLite+sqlite-vec implementation. `app/embeddings/pgvector_store.py`: PostgreSQL+pgvector implementation. CRUD operations for embeddings. Vector similarity search for matching. Configurable via `VISION_FACE_STORAGE_BACKEND` env var. Pydantic validation on all inputs.
  _Verification commands:_
  - `uv run pytest tests/test_embeddings.py`
  - `uv run ty check`

### I2 – Python Service: Clustering, Matching & Callback

- [ ] T-030-04 – Implement face clustering with scikit-learn DBSCAN.
  _Intent:_ `app/clustering/clusterer.py`: cluster similar face embeddings using scikit-learn DBSCAN. Configurable distance threshold (eps). Returns cluster labels for each embedding. Typed interface. No need to pre-specify cluster count (Q-030-03 resolved: auto-cluster with manual confirmation).
  _Verification commands:_
  - `uv run pytest tests/test_clustering.py`
  - `uv run ty check`

- [ ] T-030-05 – Implement similarity matching.
  _Intent:_ `app/matching/matcher.py`: `POST /match` endpoint logic (Q-030-12 resolved: dedicated endpoint). Accepts image file (multipart via FastAPI `UploadFile`), detects face, compares embedding against stored embeddings via `EmbeddingStore.similarity_search()`, returns list of `MatchResult` with confidence scores. Selfie image discarded after match — no temp file persisted (Q-030-11 resolved). Full type annotations.
  _Verification commands:_
  - `uv run pytest tests/test_matching.py`
  - `uv run ty check`

- [ ] T-030-06 – Implement FastAPI REST API, scan callback flow, and API key auth.
  _Intent:_ `app/main.py`: FastAPI app factory with lifespan handler (model loading on startup). `app/api/routes.py`: `POST /detect`, `POST /match`, `GET /health` — all using Pydantic request/response models. `app/api/dependencies.py`: API key auth as FastAPI dependency (validates `X-API-Key` header against `VISION_FACE_API_KEY`). Scan callback flow: receive `DetectRequest` → detect faces → generate embeddings + base64 crops → store embeddings → POST `DetectCallbackPayload` back to Lychee via httpx. `HealthResponse` includes model_loaded status and embedding_count.
  _Verification commands:_
  - `uv run pytest tests/test_api.py`
  - `uv run ruff format --check`
  - `uv run ruff check`
  - `uv run ty check`

### I3 – Python Service: Docker Image, Deployment & CI/CD

- [ ] T-030-07 – Create Dockerfile and docker-compose integration.
  _Intent:_ Multi-stage Dockerfile: builder stage uses `uv sync --frozen --no-dev`, runtime stage uses `python:3.13-slim`. Minimal image size. GPU support optional. Model (`buffalo_l`) baked into image at build time — lifespan handler loads it on startup, no runtime download (Q-030-32 resolved). Workers count via CMD shell form to honour `VISION_FACE_WORKERS`. All env vars `VISION_FACE_`-prefixed (see Pydantic `AppSettings`). Add service to Lychee's docker-compose example with shared photos volume and internal network.
  _Verification commands:_
  - `docker build -t lychee-ai-vision .`
  - `docker-compose up -d`

- [ ] T-030-08 – Create GitHub Actions CI/CD workflow for Python service.
  _Intent:_ `.github/workflows/python_ai_vision.yml`: triggers on push/PR when `ai-vision-service/**` changes. Jobs: lint (`uv run ruff format --check`, `uv run ruff check`), typecheck (`uv run ty check`), test (`uv run pytest --cov=app --cov-report=xml`, Python 3.13+3.14 matrix), docker-build (`docker build .`). Uses `astral-sh/setup-uv@v5`. Follow existing Lychee CI patterns: pinned action versions, `step-security/harden-runner`, concurrency groups.
  _Verification commands:_
  - Push branch and verify workflow runs green

- [ ] T-030-09 – End-to-end smoke test in Docker.
  _Intent:_ docker-compose up → health check passes → detect endpoint responds → callback delivers results to mock Lychee endpoint. Verify shared volume photo access works.
  _Verification commands:_
  - Integration test suite

### Phase 2: Lychee Backend (PHP/Laravel)

### I4 – Database Migrations

- [ ] T-030-10 – Create `persons` table migration (FR-030-01, S-030-01).
  _Intent:_ Migration with columns: id (string PK), name (varchar 255), user_id (nullable unsigned int, unique, FK→users ON DELETE SET NULL), is_searchable (boolean default true), timestamps. Index on user_id.
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Use string PK consistent with Photo/Album models.

- [ ] T-030-11 – Create `faces` table migration and `face_suggestions` table migration (FR-030-02, DO-030-02, DO-030-05, Q-030-33/34/38).
  _Intent:_ `faces` migration: `id` (string PK), `photo_id` (FK→photos CASCADE), `person_id` (nullable FK→persons SET NULL), `x`/`y`/`width`/`height` (float, 0.0–1.0), `confidence` (float), `crop_token` (nullable string — random high-entropy token; file served nginx-direct from `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`, Q-030-34), `is_dismissed` (boolean default false), timestamps. Indexes on `photo_id`, `person_id`. `face_suggestions` migration: `face_id` (FK→faces CASCADE), `suggested_face_id` (FK→faces CASCADE), `confidence` (float 0.0–1.0); unique on `(face_id, suggested_face_id)`. Separate migration: add nullable `face_scan_status VARCHAR(16)` column to `photos` table (Q-030-38).
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Bounding box values are relative (0.0–1.0) per NFR-030-06. `crop_path` is NOT a column — `crop_url` is a computed accessor derived from `crop_token`.

- [ ] T-030-12 – Add AI Vision config entries migration (FR-030-07, FR-030-08, NFR-030-09).
  _Intent:_ Config entries in `configs` table (`cat = 'AI Vision'`, `level = 1` / Supporter Edition): `ai_vision_enabled` (0|1, default 0), `ai_vision_face_enabled` (0|1, default 0), `ai_vision_face_permission_mode` (string, default `restricted`), `ai_vision_face_selfie_confidence_threshold` (float, default 0.8), `ai_vision_face_person_is_searchable_default` (0|1, default 1), `ai_vision_face_allow_user_claim` (0|1, default 1), `ai_vision_face_scan_batch_size` (integer, default 200). Infrastructure secrets (`AI_VISION_FACE_URL`, `AI_VISION_FACE_API_KEY`) added to `config/features.php` — NOT in the `configs` table.
  _Verification commands:_
  - `php artisan test`

### I5 – Eloquent Models & Relationships

- [ ] T-030-13 – Write unit tests for Person model relationships (FR-030-01, FR-030-03, S-030-17).
  _Intent:_ Test Person→User (belongsTo), Person→Faces (hasMany), Person→Photos (derived through Face). Test cascade: Photo delete → Face cascade delete. Test Person delete → Face.person_id set to null.
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `make phpstan`

- [ ] T-030-14 – Write unit tests for Face model relationships (FR-030-02, FR-030-04).
  _Intent:_ Test Face→Photo (belongsTo), Face→Person (belongsTo nullable). Test bounding box validation (0.0–1.0 range). Test `crop_url` accessor (computed from `crop_token`).
  _Verification commands:_
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

- [ ] T-030-15 – Implement Person model (FR-030-01, FR-030-03).
  _Intent:_ Eloquent model with: `user()` belongsTo, `faces()` hasMany, `photos()` custom relation via Face→Photo, `scopeSearchable()` query scope for is_searchable filtering. Fillable: name, user_id, is_searchable.
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `make phpstan`

- [ ] T-030-16 – Implement Face model (FR-030-02).
  _Intent:_ Eloquent model with: `photo()` belongsTo, `person()` belongsTo (nullable). `crop_url` computed accessor (from `crop_token`). Fillable: photo_id, person_id, x, y, width, height, confidence, crop_token, is_dismissed. Casts for float fields.
  _Verification commands:_
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

- [ ] T-030-17 – Add `faces()`, `faceSuggestions()` relationships to Photo model; `person()` to User model; `ScanStatus` Enum cast (FR-030-04, FR-030-05, Q-030-38).
  _Intent:_ Photo hasMany Face; User hasOne Person. Add `ScanStatus` PHP Backed Enum (values: `pending`, `completed`, `failed`) and cast `face_scan_status` via it on the Photo model. FaceSuggestion Eloquent model: `face()` / `suggestedFace()` belongsTo Face; `confidence` float; fillable `[face_id, suggested_face_id, confidence]`. *(DO-030-05, DO-030-06)*
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

### I6 – Spatie Data Resources

- [ ] T-030-18 – Create PersonResource and FaceResource (DO-030-03, DO-030-04, Q-030-46).
  _Intent:_ PersonResource: `id`, `name`, `user_id`, `is_searchable`, `face_count` (int), `photo_count` (int), `representative_crop_url`. FaceResource per DO-030-04: `id` (Face ID), `photo_id`, `person_id` (nullable), `x`/`y`/`width`/`height` (float 0.0–1.0), `confidence`, `is_dismissed`, `crop_url` (computed from `crop_token`: `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`; null if no crop). Embedded `suggestions[]` array — each item: `suggested_face_id`, `crop_url` (suggested face's own crop or null), `person_name` (nullable, LEFT JOIN), `confidence`. Suggestions always included (pre-computed from `face_suggestions` table, no N+1 risk). Include FaceResource array in PhotoResource with `hidden_face_count` (int, count of suppressed non-searchable faces — Q-030-10).
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Follow existing Spatie Data patterns in app/Http/Resources/.

### I7 – Person CRUD Endpoints

- [ ] T-030-19 – Write feature tests for Person CRUD and non-searchable filtering (FR-030-01, FR-030-06, S-030-05, S-030-15, S-030-18).
  _Intent:_ Tests for: list persons (paginated), get person, create person, update person (name, is_searchable), delete person (face.person_id nullified). Non-searchable person hidden from non-admin non-linked users. Admin sees all. Test both `open` and `restricted` permission modes (Q-030-08 resolved). Verify hidden_face_count in photo detail response.
  _Verification commands:_
  - `php artisan test --filter=PeopleControllerTest`
  - `make phpstan`

- [ ] T-030-20 – Implement PeopleController with CRUD actions (API-030-01 through API-030-05).
  _Intent:_ index (paginated, searchable scope), show, store, update, destroy. Form requests: StorePersonRequest (name required, user_id optional unique), UpdatePersonRequest (name, is_searchable). Permission mode middleware/gate: check `ai_vision_face_permission_mode` config. Routes in api_v2.php.
  _Verification commands:_
  - `php artisan test --filter=PeopleControllerTest`
  - `make phpstan`

### I8 – Person Claim, Admin Override, Merge & Selfie Claim

- [ ] T-030-21 – Write feature tests for Person claim, admin override, and merge (FR-030-05, FR-030-11, S-030-04, S-030-13, S-030-16, S-030-19).
  _Intent:_ Tests: claim person (success, sets user_id), claim already-claimed (409), admin force-claim (overrides existing link), unclaim. Merge: faces reassigned from source to target, source deleted, face count updated. Test both permission modes.
  _Verification commands:_
  - `php artisan test --filter=PersonClaimTest`
  - `php artisan test --filter=PersonMergeTest`
  - `make phpstan`

- [ ] T-030-22 – Implement claim (user + admin override) and merge actions (API-030-06, API-030-07).
  _Intent:_ ClaimPerson action: set person.user_id to Auth::id(), enforce uniqueness for non-admin. Admin claim: override existing link (clear previous user's claim, set new). MergePerson action: reassign Face records, delete source Person. Register routes.
  _Verification commands:_
  - `php artisan test --filter=PersonClaimTest`
  - `php artisan test --filter=PersonMergeTest`
  - `make phpstan`

- [ ] T-030-23 – Write feature tests for selfie-upload claim (FR-030-12, S-030-20, S-030-21, S-030-22).
  _Intent:_ Tests: upload selfie → Python service returns match → Person linked (success); selfie with no face detected (422); no matching Person (404); matched Person already claimed by another user (409). Verify selfie image discarded after match (Q-030-11 resolved).
  _Verification commands:_
  - `php artisan test --filter=SelfieClaimTest`
  - `make phpstan`

- [ ] T-030-24 – Implement SelfieClaimController (API-030-13).
  _Intent:_ POST /Person/claim-by-selfie: accepts multipart image upload, sends to Python service `POST /match` (Q-030-12 resolved: dedicated endpoint), receives matching person_id + confidence, validates confidence ≥ `ai_vision_face_selfie_confidence_threshold`, links Person to User (same 1-1 rules), deletes temp selfie. Register route.
  _Verification commands:_
  - `php artisan test --filter=SelfieClaimTest`
  - `make phpstan`

### I9 – Face Assignment, Dismiss & Cleanup Endpoints

- [ ] T-030-25 – Write feature tests for face assignment (FR-030-10, S-030-02, S-030-03).
  _Intent:_ Tests: assign face to existing person, assign face creating new person, reassign face to different person. Test all four `ai_vision_face_permission_mode` values.
  _Verification commands:_
  - `php artisan test --filter=FaceAssignmentTest`
  - `make phpstan`

- [ ] T-030-26 – Write feature tests for face dismiss/undismiss and admin bulk delete (API-030-14, API-030-16, S-030-24, S-030-25, Q-030-47).
  _Intent:_ Tests: `PATCH /api/v2/Face/{id}` toggles `is_dismissed`; photo owner can dismiss, non-owner gets 403; admin can always dismiss. `DELETE /api/v2/Face/dismissed` hard-deletes all `is_dismissed = true` faces, removes crop files, returns count. Emit `face.dismissed` / `face.undismissed` / `face.bulk_deleted` telemetry events (TE-030-10/11/12).
  _Verification commands:_
  - `php artisan test --filter=FaceDismissTest`
  - `make phpstan`

- [ ] T-030-26b – Implement FaceController: `assign`, `toggleDismissed`, and `destroyDismissed` actions (API-030-09, API-030-14, API-030-16).
  _Intent:_ `POST /Face/{id}/assign`: accepts `person_id` OR `new_person_name`; creates Person if needed; updates `face.person_id`. `PATCH /Face/{id}`: flips `is_dismissed`; auth: photo owner or admin; emits `face.dismissed` or `face.undismissed`. `DELETE /Face/dismissed`: admin-only; loops `is_dismissed = true` faces, deletes crop files from `uploads/faces/`, deletes Face records, emits `face.bulk_deleted` with count. Create form requests: `AssignFaceRequest`, `ToggleDismissedRequest`. Register routes.
  _Verification commands:_
  - `php artisan test --filter=FaceAssignmentTest`
  - `php artisan test --filter=FaceDismissTest`
  - `make phpstan`

### I10 – Scan Trigger & Result Ingestion Endpoints

- [ ] T-030-27 – Write feature tests for scan trigger and result ingestion (FR-030-07, FR-030-08, S-030-01, S-030-07, S-030-08, S-030-14, S-030-23).
  _Intent:_ Tests: trigger scan for photo (202), trigger scan for album, receive results (Face records created with crop_token), re-scan replaces old faces (old crops deleted), invalid photo_id (404), auto-scan on upload when enabled. Test both permission modes for scan trigger.
  _Verification commands:_
  - `php artisan test --filter=FaceDetectionTest`
  - `make phpstan`

- [ ] T-030-28 – Write feature test for service unavailability (FR-030-08, NFR-030-03, S-030-09).
  _Intent:_ Test: scan trigger when Python service is unreachable returns 503; all other Lychee endpoints continue to work.
  _Verification commands:_
  - `php artisan test --filter=FaceDetectionServiceUnavailableTest`
  - `make phpstan`

- [ ] T-030-29 – Implement FaceDetectionController, DispatchFaceScanJob, ProcessFaceDetectionResults, and auto-on-upload hook (API-030-10, API-030-11, API-030-12, S-030-23, Q-030-28/33/34/35/45).
  _Intent:_ `scan` action: validate target (`photo_ids[]` or `album_id`), set `face_scan_status = pending`, dispatch DispatchFaceScanJob in chunks of `ai_vision_face_scan_batch_size` (default 200, Q-030-45), return 202. Job sends HTTP `POST /detect` with `photo_path` (filesystem via shared volume) — **no `callback_url` in body** (Python reads callback URL from `VISION_FACE_LYCHEE_API_URL` env, Q-030-28). `results` action: validate X-API-Key; on success — decode base64 crops, store at `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`, create Face records with `crop_token`, create/replace FaceSuggestion rows from `suggestions[]` (Q-030-33), IoU-match old faces on re-scan to preserve `person_id` (Q-030-14; threshold from `VISION_FACE_RESCAN_IOU_THRESHOLD`, Q-030-35), set `face_scan_status = completed`; on error — set `face_scan_status = failed` (Q-030-17). `bulk-scan` action: enqueue photos where `face_scan_status IS NULL` (Q-030-40/41). Auto-on-upload: listener on PhotoSaved event dispatches job when `ai_vision_face_enabled = 1`.
  _Verification commands:_
  - `php artisan test --filter=FaceDetection`
  - `make phpstan`

### I11 – Bulk Scan Commands & Maintenance Endpoints

- [ ] T-030-30 – Write feature tests for `lychee:scan-faces` command (FR-030-09, S-030-06, CLI-030-01, CLI-030-02).
  _Intent:_ Tests: command enqueues photos where `face_scan_status IS NULL` (not failed/completed), `--album` filter works (non-recursive — only direct photos in album, Q-030-41), already-scanned photos skipped.
  _Verification commands:_
  - `php artisan test --filter=ScanFacesCommandTest`
  - `make phpstan`

- [ ] T-030-31 – Implement `lychee:scan-faces` and `lychee:scan-faces --album={id}` commands (CLI-030-01, CLI-030-02).
  _Intent:_ Query photos where `face_scan_status IS NULL`, dispatch DispatchFaceScanJob. `--album={id}` limits to direct photos in that album (non-recursive). Progress output per batch.
  _Verification commands:_
  - `php artisan test --filter=ScanFacesCommandTest`
  - `make phpstan`

- [ ] T-030-31b – Implement `lychee:rescan-failed-faces [--stuck-pending] [--older-than=N]` command (CLI-030-03, Q-030-40/48).
  _Intent:_ Default: re-enqueue all photos where `face_scan_status = 'failed'`. With `--stuck-pending`: additionally reset photos with `face_scan_status = 'pending'` and `updated_at < now() - N minutes` (default `--older-than=60`) back to `null`, making them eligible for a fresh scan. *(Q-030-48)*
  _Verification commands:_
  - `php artisan test --filter=RescanFailedFacesCommandTest`
  - `make phpstan`

- [ ] T-030-31c – Write feature tests and implement Maintenance::resetStuckFaces endpoints (API-030-17, API-030-17b, Q-030-48, S-030-26).
  _Intent:_ `GET /api/v2/Maintenance::resetStuckFaces`: admin-only check — returns `{count: N}` for photos stuck in `pending` longer than `older_than_minutes` (default 60). `POST /api/v2/Maintenance::resetStuckFaces`: admin-only do — resets those records to `null` and returns `{reset_count: N}`. Body: optional `older_than_minutes` (integer). Follows existing `Maintenance::cleaning` / `Maintenance::jobs` check/do pattern. Register in `api_v2.php`.
  _Verification commands:_
  - `php artisan test --filter=MaintenanceResetStuckFacesTest`
  - `make phpstan`

### I12 – Person Photos Endpoint

- [ ] T-030-32 – Write feature test for Person photos listing (FR-030-03, S-030-12, API-030-08).
  _Intent:_ Tests: get paginated photos for person, respects album access control (user without album access doesn't see photo), empty result for person with no faces.
  _Verification commands:_
  - `php artisan test --filter=PersonPhotosTest`
  - `make phpstan`

- [ ] T-030-33 – Implement PersonPhotosController (API-030-08).
  _Intent:_ GET /Person/{id}/photos: paginated photos through Face join, apply PhotoQueryPolicy for access control. Register route.
  _Verification commands:_
  - `php artisan test --filter=PersonPhotosTest`
  - `make phpstan`

### Phase 3: Frontend (Vue3/TypeScript)

### I13 – Frontend: People Page

- [ ] T-030-34 – Create People.vue, PeopleService.ts, and PersonCard.vue (UI-030-01).
  _Intent:_ People page at /people route. Grid of PersonCard components (server-side face crop thumbnail from crop_url, name, photo count). PeopleService: getPeople(), getPerson(), etc. Empty state when no persons exist. Service unavailable state (toast notification). Navigation link in sidebar.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I14 – Frontend: Person Detail Page

- [ ] T-030-35 – Create PersonDetail.vue (UI-030-02).
  _Intent:_ Person detail at /people/:id. Person info header (name, counts, linked user, searchability badge). Paginated photo grid (reuse existing layout components). Action buttons: Edit, Toggle searchable, Merge, Delete. Route registration.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I15 – Frontend: Face Overlays on Photo Detail

- [ ] T-030-36 – Create FaceOverlay.vue and integrate into photo detail (UI-030-03).
  _Intent:_ Positioned div overlays on photo using bounding box percentages (x, y, width, height as CSS left/top/width/height %). Name label per overlay. "Unknown" for unassigned faces. Non-searchable faces: overlays hidden entirely; display "{N} face(s) hidden for privacy" message when `hidden_face_count > 0` (Q-030-10 resolved). Click unassigned → open assignment modal. Responsive scaling with image container.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I16 – Frontend: Face Assignment Modal

- [ ] T-030-37 – Create FaceAssignmentModal.vue (UI-030-04).
  _Intent:_ Modal triggered by clicking unassigned face overlay. Face crop preview (from crop_url), confidence display. PrimeVue Dropdown to select existing person (with filter). Text input for new person name. Calls FaceService.assign() on confirm. Refreshes face overlays after success.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I17 – Frontend: Scan Trigger UI

- [ ] T-030-38 – Add scan trigger buttons to photo/album context menus and admin page (UI-030-05, UI-030-06).
  _Intent:_ "Scan for faces" in photo context menu (calls FaceDetectionService.scan). "Scan album" in album context menu. "Bulk scan all photos" in admin Maintenance page. Progress toast during scanning. Graceful handling when service unavailable.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I18 – Frontend: Selfie Upload Claim

- [ ] T-030-39 – Create SelfieClaimModal.vue and integrate into user profile (UI-030-07, S-030-20, S-030-21, S-030-22).
  _Intent:_ Modal with file upload area (drag & drop or click) for selfie image. Sends to API-030-13. Displays matching Person result (face crop, name, confidence score). Confirm button links Person to User. Error states: no face detected, no match found, already claimed. "Find me in photos" button on user profile page triggers modal.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### Phase 4: Documentation

### I19 – Documentation & Quality Gate

- [ ] T-030-40 – Update knowledge-map.md with Person/Face models and service integration.
  _Intent:_ Add Person, Face to Domain Layer models. Add Python face-recognition service to Dependencies. Add inter-service communication to Architectural Patterns. Add shared Docker volume architecture.
  _Verification commands:_
  - Review documentation for accuracy.

- [ ] T-030-41 – Update database-schema.md with persons and faces tables.
  _Intent:_ Add table definitions (including `crop_token` on faces, `face_suggestions` table, `face_scan_status` on photos), relationships, indexes, and constraints.
  _Verification commands:_
  - Review documentation for accuracy.

- [ ] T-030-42 – Create configure-facial-recognition.md how-to guide.
  _Intent:_ Docker setup instructions, shared volume configuration, environment variables, permission modes (open/restricted), service health check, troubleshooting.
  _Verification commands:_
  - Review documentation for accuracy.

- [ ] T-030-43 – Run full quality gate and update roadmap.
  _Intent:_ Run all quality gates across all three codebases. All green. Update roadmap status to Complete.
  _Verification commands:_
  - Python: `cd ai-vision-service && uv run ruff format --check && uv run ruff check && uv run ty check && uv run pytest --cov=app`
  - PHP: `vendor/bin/php-cs-fixer fix && php artisan test && make phpstan`
  - Frontend: `npm run format && npm run check`

## Notes / TODOs

**All Q-030-01 through Q-030-48 have been resolved.** All decisions are encoded in spec.md normative sections. No blocking questions remain.

**Previously blocking items — now resolved:**
- Q-030-13: `lychee_face_id` returned by `/match`; used in selfie claim flow. *(resolved, I8)*
- Q-030-14: Re-scan IoU-matches old faces to preserve `person_id`; configurable via `VISION_FACE_RESCAN_IOU_THRESHOLD`. *(resolved, I10)*
- Q-030-15: Single shared symmetric API key, both directions via `X-API-Key` header. *(resolved, I3/I10)*
- Q-030-16: `is_dismissed` boolean on Face; dismiss via `PATCH /Face/{id}`, hard-delete via `DELETE /Face/dismissed`. *(resolved, I9)*
- Q-030-17: Error callback payload defined (`ErrorCallbackPayload`); sets `face_scan_status = failed`. *(resolved, I10)*
- Q-030-18: Face.person_id type is `string` (consistent with string PKs). *(resolved, no code impact)*
- Q-030-19: `VISION_FACE_*` env prefix; `ai_vision_face_*` / `ai_vision_*` config keys. *(resolved, I3/I4)*
- Q-030-20: Four-mode permission matrix defined. *(resolved, I7)*
- Q-030-21: `DELETE /Person/{id}/claim` unclaim endpoint. *(resolved, I8)*
- Q-030-22: `{id}` = target Person (kept); `source_person_id` in body. *(resolved, I8)*
- Q-030-23: State machine documented; `face_scan_status VARCHAR(16)` + ScanStatus enum cast. *(resolved, I4/I10)*
- Q-030-24: Suggestions pre-computed via NN cosine similarity search (Python side); stored in `face_suggestions` table; embedded in FaceResource. *(resolved, I2/I10/I6)*
- Q-030-25: crop stored at `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`; served nginx-direct. *(resolved, I10)*
- Q-030-26: ThreadPoolExecutor concurrency model in Python. *(resolved, I2)*
- Q-030-27: Fire-and-forget callback; stuck-pending recovery via CLI-030-03 `--stuck-pending` and Maintenance endpoint. *(resolved, I11)*
- Q-030-28: `callback_url` removed from DetectRequest body; Python reads from env. *(resolved, I2/I10)*
- Q-030-29–48: All resolved; see spec.md and open-questions.md.
