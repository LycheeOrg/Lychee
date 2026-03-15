# Feature 029 Tasks – Facial Recognition

_Status: Draft_
_Last updated: 2026-03-15_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### Phase 1: Python Facial Recognition Service

### I1 – Python Service: Project Setup & Face Detection

- [ ] T-029-01 – Create Python service project structure with uv, ruff, ty.
  _Intent:_ Create `ai-vision-service/` directory with: `pyproject.toml` (uv project config, ruff settings, ty config), `app/` (main application with `__init__.py`), `app/detection/`, `app/embeddings/`, `app/api/`, `app/clustering/`, `app/matching/`, `tests/`, `Dockerfile`, `README.md`. Configure ruff lint rules (E, W, F, I, N, UP, ANN, B, A, SIM, TCH, RUF) and ty in `pyproject.toml`. Create Pydantic `AppSettings` (BaseSettings) in `app/config.py` with all FACE_-prefixed env vars. Create Pydantic request/response schemas in `app/api/schemas.py`: `DetectRequest`, `FaceResult`, `DetectCallbackPayload`, `MatchResult`, `MatchResponse`, `HealthResponse`. All code fully type-annotated.
  _Verification commands:_
  - `uv sync`
  - `uv run ruff format --check`
  - `uv run ruff check`
  - `uv run ty check`

- [ ] T-029-02 – Implement face detection and crop generation with InsightFace.
  _Intent:_ `app/detection/detector.py`: typed wrapper around InsightFace (ONNX Runtime backend, `buffalo_l` model). Accept photo filesystem path (shared Docker volume — Q-029-07 resolved), return list of `FaceResult` with bounding box coordinates as 0.0–1.0 relative values and confidence scores. `app/detection/cropper.py`: generate 150x150px JPEG face crop per detected face using Pillow, returned as base64 string (Q-029-09 resolved: server-side crop). Full type annotations; no `Any` types.
  _Verification commands:_
  - `uv run pytest tests/test_detection.py tests/test_cropper.py`
  - `uv run ty check`

- [ ] T-029-03 – Implement embedding generation and storage layer.
  _Intent:_ `app/embeddings/store.py`: abstract `EmbeddingStore` protocol (typed). `app/embeddings/sqlite_store.py`: SQLite+sqlite-vec implementation. `app/embeddings/pgvector_store.py`: PostgreSQL+pgvector implementation. CRUD operations for embeddings. Vector similarity search for matching. Configurable via `FACE_STORAGE_BACKEND` env var. Pydantic validation on all inputs.
  _Verification commands:_
  - `uv run pytest tests/test_embeddings.py`
  - `uv run ty check`

### I2 – Python Service: Clustering, Matching & Callback

- [ ] T-029-04 – Implement face clustering with scikit-learn DBSCAN.
  _Intent:_ `app/clustering/clusterer.py`: cluster similar face embeddings using scikit-learn DBSCAN. Configurable distance threshold (eps). Returns cluster labels for each embedding. Typed interface. No need to pre-specify cluster count (Q-029-03 resolved: auto-cluster with manual confirmation).
  _Verification commands:_
  - `uv run pytest tests/test_clustering.py`
  - `uv run ty check`

- [ ] T-029-05 – Implement similarity matching.
  _Intent:_ `app/matching/matcher.py`: `POST /match` endpoint logic (Q-029-12 resolved: dedicated endpoint). Accepts image file (multipart via FastAPI `UploadFile`), detects face, compares embedding against stored embeddings via `EmbeddingStore.similarity_search()`, returns list of `MatchResult` with confidence scores. Selfie image discarded after match — no temp file persisted (Q-029-11 resolved). Full type annotations.
  _Verification commands:_
  - `uv run pytest tests/test_matching.py`
  - `uv run ty check`

- [ ] T-029-06 – Implement FastAPI REST API, scan callback flow, and API key auth.
  _Intent:_ `app/main.py`: FastAPI app factory with lifespan handler (model loading on startup). `app/api/routes.py`: `POST /detect`, `POST /match`, `GET /health` — all using Pydantic request/response models. `app/api/dependencies.py`: API key auth as FastAPI dependency (validates `FACE_API_KEY` header). Scan callback flow: receive `DetectRequest` → detect faces → generate embeddings + base64 crops → store embeddings → POST `DetectCallbackPayload` back to Lychee via httpx. `HealthResponse` includes model_loaded status and embedding_count.
  _Verification commands:_
  - `uv run pytest tests/test_api.py`
  - `uv run ruff format --check`
  - `uv run ruff check`
  - `uv run ty check`

### I3 – Python Service: Docker Image, Deployment & CI/CD

- [ ] T-029-07 – Create Dockerfile and docker-compose integration.
  _Intent:_ Multi-stage Dockerfile: builder stage uses `uv sync --frozen --no-dev`, runtime stage uses `python:3.13-slim`. Minimal image size. GPU support optional. Model download on first run via FastAPI lifespan handler. All env vars FACE_-prefixed (see Pydantic `AppSettings`). Add service to Lychee's docker-compose example with shared photos volume and internal network.
  _Verification commands:_
  - `docker build -t lychee-ai-vision .`
  - `docker-compose up -d`

- [ ] T-029-08 – Create GitHub Actions CI/CD workflow for Python service.
  _Intent:_ `.github/workflows/python_ai_vision.yml`: triggers on push/PR when `ai-vision-service/**` changes. Jobs: lint (`uv run ruff format --check`, `uv run ruff check`), typecheck (`uv run ty check`), test (`uv run pytest --cov=app --cov-report=xml`, Python 3.13+3.14 matrix), docker-build (`docker build .`). Uses `astral-sh/setup-uv@v5`. Follow existing Lychee CI patterns: pinned action versions, `step-security/harden-runner`, concurrency groups.
  _Verification commands:_
  - Push branch and verify workflow runs green

- [ ] T-029-09 – End-to-end smoke test in Docker.
  _Intent:_ docker-compose up → health check passes → detect endpoint responds → callback delivers results to mock Lychee endpoint. Verify shared volume photo access works.
  _Verification commands:_
  - Integration test suite

### Phase 2: Lychee Backend (PHP/Laravel)

### I4 – Database Migrations

- [ ] T-029-10 – Create `persons` table migration (FR-029-01, S-029-01).
  _Intent:_ Migration with columns: id (string PK), name (varchar 255), user_id (nullable unsigned int, unique, FK→users ON DELETE SET NULL), is_searchable (boolean default true), timestamps. Index on user_id.
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Use string PK consistent with Photo/Album models.

- [ ] T-029-11 – Create `faces` table migration (FR-029-02, S-029-01).
  _Intent:_ Migration with columns: id (string PK), photo_id (string, FK→photos ON DELETE CASCADE), person_id (nullable string, FK→persons ON DELETE SET NULL), x (float), y (float), width (float), height (float), confidence (float), crop_path (nullable string — Q-029-09 resolved: server-side crop stored as file), timestamps. Indexes on photo_id and person_id.
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Bounding box values are relative (0.0–1.0) per NFR-029-06.

- [ ] T-029-12 – Add config entries migration (FR-029-07).
  _Intent:_ Config entries: `face_recognition_service_url`, `face_recognition_enabled`, `face_recognition_api_key`, `face_recognition_selfie_confidence_threshold` (float, default 0.8), `face_recognition_permission_mode` (enum: open/restricted, default: open — Q-029-08 resolved).
  _Verification commands:_
  - `php artisan test`

### I5 – Eloquent Models & Relationships

- [ ] T-029-13 – Write unit tests for Person model relationships (FR-029-01, FR-029-03, S-029-17).
  _Intent:_ Test Person→User (belongsTo), Person→Faces (hasMany), Person→Photos (derived through Face). Test cascade: Photo delete → Face cascade delete. Test Person delete → Face.person_id set to null.
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `make phpstan`

- [ ] T-029-14 – Write unit tests for Face model relationships (FR-029-02, FR-029-04).
  _Intent:_ Test Face→Photo (belongsTo), Face→Person (belongsTo nullable). Test bounding box validation (0.0–1.0 range). Test crop_path accessor.
  _Verification commands:_
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

- [ ] T-029-15 – Implement Person model (FR-029-01, FR-029-03).
  _Intent:_ Eloquent model with: `user()` belongsTo, `faces()` hasMany, `photos()` custom relation via Face→Photo, `scopeSearchable()` query scope for is_searchable filtering. Fillable: name, user_id, is_searchable.
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `make phpstan`

- [ ] T-029-16 – Implement Face model (FR-029-02).
  _Intent:_ Eloquent model with: `photo()` belongsTo, `person()` belongsTo (nullable). Fillable: photo_id, person_id, x, y, width, height, confidence, crop_path. Casts for float fields.
  _Verification commands:_
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

- [ ] T-029-17 – Add `faces()` relationship to Photo model and `person()` to User model (FR-029-04, FR-029-05).
  _Intent:_ Photo hasMany Face. User hasOne Person. Lazy-loaded by default.
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

### I6 – Spatie Data Resources

- [ ] T-029-18 – Create PersonResource and FaceResource (DO-029-03, DO-029-04).
  _Intent:_ PersonResource: id, name, user_id, is_searchable, face_count (int), photo_count (int), representative_crop_url. FaceResource: id, person_id, person_name (nullable), x, y, width, height, confidence, crop_url. Include FaceResource array in PhotoResource (lazy-loaded via `faces` relation), plus `hidden_face_count` (integer — Q-029-10 resolved: count of suppressed non-searchable faces for unauthorized viewers).
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Follow existing Spatie Data patterns in app/Http/Resources/.

### I7 – Person CRUD Endpoints

- [ ] T-029-19 – Write feature tests for Person CRUD and non-searchable filtering (FR-029-01, FR-029-06, S-029-05, S-029-15, S-029-18).
  _Intent:_ Tests for: list persons (paginated), get person, create person, update person (name, is_searchable), delete person (face.person_id nullified). Non-searchable person hidden from non-admin non-linked users. Admin sees all. Test both `open` and `restricted` permission modes (Q-029-08 resolved). Verify hidden_face_count in photo detail response.
  _Verification commands:_
  - `php artisan test --filter=PeopleControllerTest`
  - `make phpstan`

- [ ] T-029-20 – Implement PeopleController with CRUD actions (API-029-01 through API-029-05).
  _Intent:_ index (paginated, searchable scope), show, store, update, destroy. Form requests: StorePersonRequest (name required, user_id optional unique), UpdatePersonRequest (name, is_searchable). Permission mode middleware/gate: check `face_recognition_permission_mode` config. Routes in api_v2.php.
  _Verification commands:_
  - `php artisan test --filter=PeopleControllerTest`
  - `make phpstan`

### I8 – Person Claim, Admin Override, Merge & Selfie Claim

- [ ] T-029-21 – Write feature tests for Person claim, admin override, and merge (FR-029-05, FR-029-11, S-029-04, S-029-13, S-029-16, S-029-19).
  _Intent:_ Tests: claim person (success, sets user_id), claim already-claimed (409), admin force-claim (overrides existing link), unclaim. Merge: faces reassigned from source to target, source deleted, face count updated. Test both permission modes.
  _Verification commands:_
  - `php artisan test --filter=PersonClaimTest`
  - `php artisan test --filter=PersonMergeTest`
  - `make phpstan`

- [ ] T-029-22 – Implement claim (user + admin override) and merge actions (API-029-06, API-029-07).
  _Intent:_ ClaimPerson action: set person.user_id to Auth::id(), enforce uniqueness for non-admin. Admin claim: override existing link (clear previous user's claim, set new). MergePerson action: reassign Face records, delete source Person. Register routes.
  _Verification commands:_
  - `php artisan test --filter=PersonClaimTest`
  - `php artisan test --filter=PersonMergeTest`
  - `make phpstan`

- [ ] T-029-23 – Write feature tests for selfie-upload claim (FR-029-12, S-029-20, S-029-21, S-029-22).
  _Intent:_ Tests: upload selfie → Python service returns match → Person linked (success); selfie with no face detected (422); no matching Person (404); matched Person already claimed by another user (409). Verify selfie image discarded after match (Q-029-11 resolved).
  _Verification commands:_
  - `php artisan test --filter=SelfieClaimTest`
  - `make phpstan`

- [ ] T-029-24 – Implement SelfieClaimController (API-029-13).
  _Intent:_ POST /Person/claim-by-selfie: accepts multipart image upload, sends to Python service `POST /match` (Q-029-12 resolved: dedicated endpoint), receives matching person_id + confidence, validates confidence ≥ `face_recognition_selfie_confidence_threshold`, links Person to User (same 1-1 rules), deletes temp selfie. Register route.
  _Verification commands:_
  - `php artisan test --filter=SelfieClaimTest`
  - `make phpstan`

### I9 – Face Assignment Endpoint

- [ ] T-029-25 – Write feature tests for face assignment (FR-029-10, S-029-02, S-029-03).
  _Intent:_ Tests: assign face to existing person, assign face creating new person, reassign face to different person. Test both permission modes.
  _Verification commands:_
  - `php artisan test --filter=FaceAssignmentTest`
  - `make phpstan`

- [ ] T-029-26 – Implement FaceController assign action (API-029-09).
  _Intent:_ POST /Face/{id}/assign: accepts person_id OR new_person_name. If new_person_name, create Person first. Update face.person_id. Register route.
  _Verification commands:_
  - `php artisan test --filter=FaceAssignmentTest`
  - `make phpstan`

### I10 – Scan Trigger & Result Ingestion Endpoints

- [ ] T-029-27 – Write feature tests for scan trigger and result ingestion (FR-029-07, FR-029-08, S-029-01, S-029-07, S-029-08, S-029-14, S-029-23).
  _Intent:_ Tests: trigger scan for photo (202), trigger scan for album, receive results (Face records created with crop_path), re-scan replaces old faces (old crops deleted), invalid photo_id (404), auto-scan on upload when enabled. Test both permission modes for scan trigger.
  _Verification commands:_
  - `php artisan test --filter=FaceDetectionTest`
  - `make phpstan`

- [ ] T-029-28 – Write feature test for service unavailability (FR-029-08, NFR-029-03, S-029-09).
  _Intent:_ Test: scan trigger when Python service is unreachable returns 503; all other Lychee endpoints continue to work.
  _Verification commands:_
  - `php artisan test --filter=FaceDetectionServiceUnavailableTest`
  - `make phpstan`

- [ ] T-029-29 – Implement FaceDetectionController, DispatchFaceScanJob, ProcessFaceDetectionResults, and auto-on-upload hook (API-029-10, API-029-11, API-029-12, S-029-23).
  _Intent:_ `scan` action: validate target (photo_ids or album_id), dispatch DispatchFaceScanJob per photo, return 202. Job sends HTTP request to Python service `POST /detect` with `photo_path` (filesystem path via shared volume — Q-029-07 resolved) and `callback_url`. `results` action: validate service API key, decode base64 crops and store as files, create Face records with crop_path, delete old faces/crops for re-scans. Auto-on-upload: listener on PhotoSaved event dispatches DispatchFaceScanJob when `face_recognition_enabled` is true.
  _Verification commands:_
  - `php artisan test --filter=FaceDetection`
  - `make phpstan`

### I11 – Bulk Scan Artisan Command

- [ ] T-029-30 – Write feature tests for lychee:scan-faces command (FR-029-09, S-029-06, CLI-029-01, CLI-029-02).
  _Intent:_ Tests: command enqueues unscanned photos, --album filter works, already-scanned photos skipped.
  _Verification commands:_
  - `php artisan test --filter=ScanFacesCommandTest`
  - `make phpstan`

- [ ] T-029-31 – Implement lychee:scan-faces command and face_scan_status migration.
  _Intent:_ Add `face_scan_status` enum column (null/pending/completed/failed) to photos table via migration. Artisan command queries photos where face_scan_status IS NULL, dispatches DispatchFaceScanJob for each, sets status to pending.
  _Verification commands:_
  - `php artisan test --filter=ScanFacesCommandTest`
  - `make phpstan`

### I12 – Person Photos Endpoint

- [ ] T-029-32 – Write feature test for Person photos listing (FR-029-03, S-029-12, API-029-08).
  _Intent:_ Tests: get paginated photos for person, respects album access control (user without album access doesn't see photo), empty result for person with no faces.
  _Verification commands:_
  - `php artisan test --filter=PersonPhotosTest`
  - `make phpstan`

- [ ] T-029-33 – Implement PersonPhotosController (API-029-08).
  _Intent:_ GET /Person/{id}/photos: paginated photos through Face join, apply PhotoQueryPolicy for access control. Register route.
  _Verification commands:_
  - `php artisan test --filter=PersonPhotosTest`
  - `make phpstan`

### Phase 3: Frontend (Vue3/TypeScript)

### I13 – Frontend: People Page

- [ ] T-029-34 – Create People.vue, PeopleService.ts, and PersonCard.vue (UI-029-01).
  _Intent:_ People page at /people route. Grid of PersonCard components (server-side face crop thumbnail from crop_url, name, photo count). PeopleService: getPeople(), getPerson(), etc. Empty state when no persons exist. Service unavailable state (toast notification). Navigation link in sidebar.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I14 – Frontend: Person Detail Page

- [ ] T-029-35 – Create PersonDetail.vue (UI-029-02).
  _Intent:_ Person detail at /people/:id. Person info header (name, counts, linked user, searchability badge). Paginated photo grid (reuse existing layout components). Action buttons: Edit, Toggle searchable, Merge, Delete. Route registration.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I15 – Frontend: Face Overlays on Photo Detail

- [ ] T-029-36 – Create FaceOverlay.vue and integrate into photo detail (UI-029-03).
  _Intent:_ Positioned div overlays on photo using bounding box percentages (x, y, width, height as CSS left/top/width/height %). Name label per overlay. "Unknown" for unassigned faces. Non-searchable faces: overlays hidden entirely; display "{N} face(s) hidden for privacy" message when `hidden_face_count > 0` (Q-029-10 resolved). Click unassigned → open assignment modal. Responsive scaling with image container.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I16 – Frontend: Face Assignment Modal

- [ ] T-029-37 – Create FaceAssignmentModal.vue (UI-029-04).
  _Intent:_ Modal triggered by clicking unassigned face overlay. Face crop preview (from crop_url), confidence display. PrimeVue Dropdown to select existing person (with filter). Text input for new person name. Calls FaceService.assign() on confirm. Refreshes face overlays after success.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I17 – Frontend: Scan Trigger UI

- [ ] T-029-38 – Add scan trigger buttons to photo/album context menus and admin page (UI-029-05, UI-029-06).
  _Intent:_ "Scan for faces" in photo context menu (calls FaceDetectionService.scan). "Scan album" in album context menu. "Bulk scan all photos" in admin Maintenance page. Progress toast during scanning. Graceful handling when service unavailable.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I18 – Frontend: Selfie Upload Claim

- [ ] T-029-39 – Create SelfieClaimModal.vue and integrate into user profile (UI-029-07, S-029-20, S-029-21, S-029-22).
  _Intent:_ Modal with file upload area (drag & drop or click) for selfie image. Sends to API-029-13. Displays matching Person result (face crop, name, confidence score). Confirm button links Person to User. Error states: no face detected, no match found, already claimed. "Find me in photos" button on user profile page triggers modal.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### Phase 4: Documentation

### I19 – Documentation & Quality Gate

- [ ] T-029-40 – Update knowledge-map.md with Person/Face models and service integration.
  _Intent:_ Add Person, Face to Domain Layer models. Add Python face-recognition service to Dependencies. Add inter-service communication to Architectural Patterns. Add shared Docker volume architecture.
  _Verification commands:_
  - Review documentation for accuracy.

- [ ] T-029-41 – Update database-schema.md with persons and faces tables.
  _Intent:_ Add table definitions (including crop_path on faces), relationships, indexes, and constraints.
  _Verification commands:_
  - Review documentation for accuracy.

- [ ] T-029-42 – Create configure-facial-recognition.md how-to guide.
  _Intent:_ Docker setup instructions, shared volume configuration, environment variables, permission modes (open/restricted), service health check, troubleshooting.
  _Verification commands:_
  - Review documentation for accuracy.

- [ ] T-029-43 – Run full quality gate and update roadmap.
  _Intent:_ Run all quality gates across all three codebases. All green. Update roadmap status to Complete.
  _Verification commands:_
  - Python: `cd ai-vision-service && uv run ruff format --check && uv run ruff check && uv run ty check && uv run pytest --cov=app`
  - PHP: `vendor/bin/php-cs-fixer fix && php artisan test && make phpstan`
  - Frontend: `npm run format && npm run check`

## Notes / TODOs

**Open questions (2026-03-15) — 6 high, 7 medium:**
- **Q-029-13 (High):** Embedding ID → Person mapping gap in selfie match flow. Blocks I8.
- **Q-029-14 (High):** Re-scan destroys manual face assignments. Blocks I10.
- **Q-029-15 (High):** Two API keys but Lychee config only defines one; header format unspecified. Blocks I3, I4, I10.
- **Q-029-16 (High):** Missing Face deletion endpoint for false positives. Affects I9.
- **Q-029-17 (High):** Error callback shape undefined (Python → Lychee on failure). Blocks I2, I10.
- **Q-029-18 (High):** Spec DSL type mismatch — Face.person_id declared as integer, should be string.
- **Q-029-19 (Medium):** Naming inconsistency — FACE_* env prefix vs ai-vision-service name.
- **Q-029-20 (Medium):** Permission mode scope per operation is ambiguous. Affects I7, I8, I9, I10.
- **Q-029-21 (Medium):** Missing Person unclaim endpoint. Affects I8.
- **Q-029-22 (Medium):** Merge direction ambiguity on API-029-06. Affects I8.
- **Q-029-23 (Medium):** face_scan_status state machine transitions undefined. Affects I10, I11.
- **Q-029-24 (Medium):** Similar faces in assignment modal — data source unspecified. Affects I16.
- **Q-029-25 (Medium):** Crop storage path pattern undefined. Affects I10, I6.

**Blocking summary:** I1–I3 can start. I4+ partially blocked on Q-029-15. I8 blocked on Q-029-13. I10 blocked on Q-029-14, Q-029-15, Q-029-17.

**Previously resolved (Q-029-01 through Q-029-12):**
- **Q-029-01:** REST + webhook callbacks (Option A). Scan callback in I2/I10.
- **Q-029-02:** Multiple triggers — upload + manual + bulk (Option A). Auto-on-upload in I10; manual in I10; bulk CLI in I11.
- **Q-029-03:** Auto-cluster with manual confirmation (Option A). Python service clusters in I2; Lychee supports manual assignment in I9.
- **Q-029-04:** Embeddings in Python service (Option A). Lychee stores only Face metadata; embeddings in Python DB (I1/I2).
- **Q-029-05:** Non-searchable = hidden from search + People page for unauthorized users (Option A). Enforced in I7.
- **Q-029-06:** Self-identification + admin override + selfie-upload claim (Option A extended). Admin force-link in I8; selfie claim in I8 (backend) and I18 (frontend).
- **Q-029-07:** Shared Docker volume (Option A). Python service reads photos via `PHOTOS_PATH` mount. Configured in I3.
- **Q-029-08:** Configurable permission mode — open/restricted (Option C). Admin setting in I4; enforced in I7, I8, I9, I10.
- **Q-029-09:** Server-side face crop (Option B). Python service generates 150x150 JPEG crops as base64 in I1; stored as files in I10.
- **Q-029-10:** Hide overlays entirely + "{N} face(s) hidden for privacy" message (Option B). Implemented in I15 (frontend) with hidden_face_count from I6.
- **Q-029-11:** Discard selfie after match (Option A). Enforced in I8 (backend) and I18 (frontend).
- **Q-029-12:** Dedicated `POST /match` endpoint (Option A). Implemented in I2 (Python) and I8 (Lychee consumer).
