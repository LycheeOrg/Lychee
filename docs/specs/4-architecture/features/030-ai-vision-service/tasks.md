# Feature 030 Tasks – Facial Recognition

_Status: Draft_
_Last updated: 2026-04-04_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### Phase 1: Python Facial Recognition Service

### I1 – Python Service: Project Setup & Face Detection

- [x] T-030-01 – Create Python service project structure with uv, ruff, ty.
  _Intent:_ Create `ai-vision-service/` directory with: `pyproject.toml` (uv project config, ruff settings, ty config), `app/` (main application with `__init__.py`), `app/detection/`, `app/embeddings/`, `app/api/`, `app/clustering/`, `app/matching/`, `tests/`, `Dockerfile`, `README.md`. Configure ruff lint rules (E, W, F, I, N, UP, ANN, B, A, SIM, TCH, RUF) and ty in `pyproject.toml`. Create Pydantic `AppSettings` (BaseSettings) in `app/config.py` with all `VISION_FACE_`-prefixed env vars. Create Pydantic request/response schemas in `app/api/schemas.py`: `DetectRequest`, `FaceResult`, `DetectCallbackPayload`, `MatchResult`, `MatchResponse`, `HealthResponse`. All code fully type-annotated.
  _Verification commands:_
  - `uv sync`
  - `uv run ruff format --check`
  - `uv run ruff check`
  - `uv run ty check`

- [x] T-030-02 – Implement face detection and crop generation with InsightFace.
  _Intent:_ `app/detection/detector.py`: typed wrapper around InsightFace (ONNX Runtime backend, `buffalo_l` model). Accept photo filesystem path (shared Docker volume — Q-030-07 resolved), return list of `FaceResult` with bounding box coordinates as 0.0–1.0 relative values and confidence scores. `app/detection/cropper.py`: generate 150x150px JPEG face crop per detected face using Pillow, returned as base64 string (Q-030-09 resolved: server-side crop). Full type annotations; no `Any` types.
  _Verification commands:_
  - `uv run pytest tests/test_detection.py tests/test_cropper.py`
  - `uv run ty check`

- [x] T-030-03 – Implement embedding generation and storage layer.
  _Intent:_ `app/embeddings/store.py`: abstract `EmbeddingStore` protocol (typed). `app/embeddings/sqlite_store.py`: SQLite+sqlite-vec implementation. `app/embeddings/pgvector_store.py`: PostgreSQL+pgvector implementation. CRUD operations for embeddings. Vector similarity search for matching. Configurable via `VISION_FACE_STORAGE_BACKEND` env var. Pydantic validation on all inputs.
  _Verification commands:_
  - `uv run pytest tests/test_embeddings.py`
  - `uv run ty check`

### I2 – Python Service: Clustering, Matching & Callback

- [x] T-030-04 – Implement face clustering with scikit-learn DBSCAN.
  _Intent:_ `app/clustering/clusterer.py`: cluster similar face embeddings using scikit-learn DBSCAN. Configurable distance threshold (eps). Returns cluster labels for each embedding. Typed interface. No need to pre-specify cluster count (Q-030-03 resolved: auto-cluster with manual confirmation).
  _Verification commands:_
  - `uv run pytest tests/test_clustering.py`
  - `uv run ty check`

- [x] T-030-05 – Implement similarity matching.
  _Intent:_ `app/matching/matcher.py`: `POST /match` endpoint logic (Q-030-12 resolved: dedicated endpoint). Accepts image file (multipart via FastAPI `UploadFile`), detects face, compares embedding against stored embeddings via `EmbeddingStore.similarity_search()`, returns list of `MatchResult` with confidence scores. Selfie image discarded after match — no temp file persisted (Q-030-11 resolved). Full type annotations.
  _Verification commands:_
  - `uv run pytest tests/test_matching.py`
  - `uv run ty check`

- [x] T-030-06 – Implement FastAPI REST API, scan callback flow, and API key auth.
  _Intent:_ `app/main.py`: FastAPI app factory with lifespan handler (model loading on startup). `app/api/routes.py`: `POST /detect`, `POST /match`, `GET /health` — all using Pydantic request/response models. `app/api/dependencies.py`: API key auth as FastAPI dependency (validates `X-API-Key` header against `VISION_FACE_API_KEY`). Scan callback flow: receive `DetectRequest` → detect faces → generate embeddings + base64 crops → store embeddings → POST `DetectCallbackPayload` back to Lychee via httpx. `HealthResponse` includes model_loaded status and embedding_count.
  _Verification commands:_
  - `uv run pytest tests/test_api.py`
  - `uv run ruff format --check`
  - `uv run ruff check`
  - `uv run ty check`

### I3 – Python Service: Docker Image, Deployment & CI/CD

- [x] T-030-07 – Create Dockerfile and docker-compose integration.
  _Intent:_ Multi-stage Dockerfile: builder stage uses `uv sync --frozen --no-dev`, runtime stage uses `python:3.13-slim`. Minimal image size. GPU support optional. Model (`buffalo_l`) baked into image at build time — lifespan handler loads it on startup, no runtime download (Q-030-32 resolved). Workers count via CMD shell form to honour `VISION_FACE_WORKERS`. All env vars `VISION_FACE_`-prefixed (see Pydantic `AppSettings`). Add service to Lychee's docker-compose example with shared photos volume and internal network.
  _Verification commands:_
  - `docker build -t lychee-ai-vision .`
  - `docker-compose up -d`

- [x] T-030-08 – Create GitHub Actions CI/CD workflow for Python service.
  _Intent:_ `.github/workflows/python_ai_vision.yml`: triggers on push/PR when `ai-vision-service/**` changes. Jobs: lint (`uv run ruff format --check`, `uv run ruff check`), typecheck (`uv run ty check`), test (`uv run pytest --cov=app --cov-report=xml`, Python 3.13+3.14 matrix), docker-build (`docker build .`). Uses `astral-sh/setup-uv@v5`. Follow existing Lychee CI patterns: pinned action versions, `step-security/harden-runner`, concurrency groups.
  _Verification commands:_
  - Push branch and verify workflow runs green

- [x] T-030-09 – End-to-end smoke test in Docker.
  _Intent:_ docker-compose up → health check passes → detect endpoint responds → callback delivers results to mock Lychee endpoint. Verify shared volume photo access works.
  _Verification commands:_
  - Integration test suite

### Phase 2: Lychee Backend (PHP/Laravel)

### I4 – Database Migrations

- [x] T-030-10 – Create `persons` table migration (FR-030-01, S-030-01).
  _Intent:_ Migration with columns: id (string PK), name (varchar 255), user_id (nullable unsigned int, unique, FK→users ON DELETE SET NULL), is_searchable (boolean default true), timestamps. Index on user_id. **Does not include `representative_face_id`** — that FK references the `faces` table which does not yet exist; it is added in a separate addendum migration (T-030-53, DO-030-08) after `faces` is created, to avoid a circular FK dependency.
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Use string PK consistent with Photo/Album models.

- [x] T-030-11 – Create `faces` table migration and `face_suggestions` table migration (FR-030-02, DO-030-02, DO-030-05, Q-030-33/34/38).
  _Intent:_ `faces` migration: `id` (string PK), `photo_id` (FK→photos CASCADE), `person_id` (nullable FK→persons SET NULL), `x`/`y`/`width`/`height` (float, 0.0–1.0), `confidence` (float), `crop_token` (nullable string — random high-entropy token; file served nginx-direct from `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`, Q-030-34), `is_dismissed` (boolean default false), timestamps. Indexes on `photo_id`, `person_id`. `face_suggestions` migration: `face_id` (FK→faces CASCADE), `suggested_face_id` (FK→faces CASCADE), `confidence` (float 0.0–1.0); unique on `(face_id, suggested_face_id)`. Separate migration: add nullable `face_scan_status VARCHAR(16)` column to `photos` table (Q-030-38).
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Bounding box values are relative (0.0–1.0) per NFR-030-06. `crop_path` is NOT a column — `crop_url` is a computed accessor derived from `crop_token`.

- [x] T-030-12 – Add AI Vision config entries migration (FR-030-07, FR-030-08, NFR-030-09).
  _Intent:_ Config entries in `configs` table (`cat = 'AI Vision'`, `level = 1` / Supporter Edition): `ai_vision_enabled` (0|1, default 0), `ai_vision_face_enabled` (0|1, default 0), `ai_vision_face_permission_mode` (string, default `restricted`), `ai_vision_face_selfie_confidence_threshold` (float, default 0.8), `ai_vision_face_person_is_searchable_default` (0|1, default 1), `ai_vision_face_allow_user_claim` (0|1, default 1), `ai_vision_face_scan_batch_size` (integer, default 200). Infrastructure secrets (`AI_VISION_FACE_URL`, `AI_VISION_FACE_API_KEY`) added to `config/features.php` — NOT in the `configs` table.
  _Verification commands:_
  - `php artisan test`

- [x] T-030-53 – Add `representative_face_id` column migration to `persons` table (DO-030-08, Q-030-50).
  _Intent:_ New migration (runs **after** the `faces` table migration from T-030-11): `ALTER TABLE persons ADD COLUMN representative_face_id VARCHAR(?) NULL`. Add FK constraint: `representative_face_id` → `faces.id` ON DELETE SET NULL. This resolves the circular-FK dependency (persons→faces and faces→persons both require the other table to exist first). Run tests to confirm migration applies cleanly on SQLite.
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`

### I5 – Eloquent Models & Relationships

- [x] T-030-13 – Write unit tests for Person model relationships (FR-030-01, FR-030-03, S-030-17).
  _Intent:_ Test Person→User (belongsTo), Person→Faces (hasMany), Person→Photos (derived through Face). Test cascade: Photo delete → Face cascade delete. Test Person delete → Face.person_id set to null.
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `make phpstan`

- [x] T-030-14 – Write unit tests for Face model relationships (FR-030-02, FR-030-04).
  _Intent:_ Test Face→Photo (belongsTo), Face→Person (belongsTo nullable). Test bounding box validation (0.0–1.0 range). Test `crop_url` accessor (computed from `crop_token`).
  _Verification commands:_
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

- [x] T-030-15 – Implement Person model (FR-030-01, FR-030-03).
  _Intent:_ Eloquent model with: `user()` belongsTo, `faces()` hasMany, `photos()` custom relation via Face→Photo, `scopeSearchable()` query scope for is_searchable filtering. Fillable: name, user_id, is_searchable.
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `make phpstan`

- [x] T-030-16 – Implement Face model (FR-030-02).
  _Intent:_ Eloquent model with: `photo()` belongsTo, `person()` belongsTo (nullable). `crop_url` computed accessor (from `crop_token`). Fillable: photo_id, person_id, x, y, width, height, confidence, crop_token, is_dismissed. Casts for float fields.
  _Verification commands:_
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

- [x] T-030-17 – Add `faces()`, `faceSuggestions()` relationships to Photo model; `person()` to User model; `ScanStatus` Enum cast (FR-030-04, FR-030-05, Q-030-38).
  _Intent:_ Photo hasMany Face; User hasOne Person. Add `ScanStatus` PHP Backed Enum (values: `pending`, `completed`, `failed`) and cast `face_scan_status` via it on the Photo model. FaceSuggestion Eloquent model: `face()` / `suggestedFace()` belongsTo Face; `confidence` float; fillable `[face_id, suggested_face_id, confidence]`. *(DO-030-05, DO-030-06)*
  _Verification commands:_
  - `php artisan test --filter=PersonModelTest`
  - `php artisan test --filter=FaceModelTest`
  - `make phpstan`

### I6 – Spatie Data Resources

- [x] T-030-18 – Create PersonResource and FaceResource (DO-030-03, DO-030-04, Q-030-46).
  _Intent:_ PersonResource: `id`, `name`, `user_id`, `is_searchable`, `face_count` (int), `photo_count` (int), `representative_face_id` (nullable string), `representative_crop_url` (nullable string — computed: if `representative_face_id` is set and the referenced Face has a `crop_token`, use that face's crop URL; otherwise `SELECT crop_token FROM faces WHERE person_id = ? AND is_dismissed = false AND crop_token IS NOT NULL ORDER BY confidence DESC LIMIT 1`; null if no qualifying face). FaceResource per DO-030-04: `id` (Face ID), `photo_id`, `person_id` (nullable), `x`/`y`/`width`/`height` (float 0.0–1.0), `confidence`, `is_dismissed`, `crop_url` (computed from `crop_token`: `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`; null if no crop). Embedded `suggestions[]` array — each item: `suggested_face_id`, `crop_url` (suggested face's own crop or null), `person_name` (nullable, LEFT JOIN), `confidence`. Suggestions always included (pre-computed from `face_suggestions` table, no N+1 risk). Include FaceResource array in PhotoResource with `hidden_face_count` (int, count of suppressed non-searchable faces — Q-030-10).
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Follow existing Spatie Data patterns in app/Http/Resources/.

### I7 – Person CRUD Endpoints

- [x] T-030-19 – Write feature tests for Person CRUD and non-searchable filtering (FR-030-01, FR-030-06, S-030-05, S-030-15, S-030-18).
  _Intent:_ Tests for: list persons (paginated), get person, create person, update person (name, is_searchable), delete person (face.person_id nullified). Non-searchable person hidden from non-admin non-linked users. Admin sees all. Test both `open` and `restricted` permission modes (Q-030-08 resolved). Verify hidden_face_count in photo detail response.
  _Verification commands:_
  - `php artisan test --filter=PeopleControllerTest`
  - `make phpstan`

- [x] T-030-20 – Implement PeopleController with CRUD actions (API-030-01 through API-030-05).
  _Intent:_ index (paginated, searchable scope), show, store, update, destroy. Form requests: StorePersonRequest (name required, user_id optional unique), UpdatePersonRequest (name, is_searchable). Permission mode middleware/gate: check `ai_vision_face_permission_mode` config. Routes in api_v2.php.
  _Verification commands:_
  - `php artisan test --filter=PeopleControllerTest`
  - `make phpstan`

### I8 – Person Claim, Admin Override, Merge & Selfie Claim

- [x] T-030-21 – Write feature tests for Person claim, admin override, and merge (FR-030-05, FR-030-11, S-030-04, S-030-13, S-030-16, S-030-19).
  _Intent:_ Tests: claim person (success, sets user_id), claim already-claimed (409), admin force-claim (overrides existing link), unclaim. Merge: faces reassigned from source to target, source deleted, face count updated. Test both permission modes.
  _Verification commands:_
  - `php artisan test --filter=PersonClaimTest`
  - `php artisan test --filter=PersonMergeTest`
  - `make phpstan`

- [x] T-030-22 – Implement claim (user + admin override), unclaim, and merge actions (API-030-06, API-030-07, API-030-15).
  _Intent:_ ClaimPerson action: set person.user_id to Auth::id(), enforce uniqueness for non-admin. Admin claim: override existing link (clear previous user's claim, set new). UnclaimPerson action: `DELETE /api/v2/Person/{id}/claim` — sets `person.user_id = null`; only linked User or admin can unclaim (FR-030-05). MergePerson action: reassign Face records, delete source Person. Register all three routes.
  _Verification commands:_
  - `php artisan test --filter=PersonClaimTest`
  - `php artisan test --filter=PersonMergeTest`
  - `make phpstan`

- [x] T-030-23 – Write feature tests for selfie-upload claim (FR-030-12, S-030-20, S-030-21, S-030-22).
  _Intent:_ Tests: upload selfie → Python service returns match → Person linked (success); selfie with no face detected (422); no matching Person (404); matched Person already claimed by another user (409). Verify selfie image discarded after match (Q-030-11 resolved).
  _Verification commands:_
  - `php artisan test --filter=SelfieClaimTest`
  - `make phpstan`

- [x] T-030-24 – Implement SelfieClaimController (API-030-13).
  _Intent:_ POST /Person/claim-by-selfie: accepts multipart image upload, sends to Python service `POST /match` (Q-030-12 resolved: dedicated endpoint), receives matching person_id + confidence, validates confidence ≥ `ai_vision_face_selfie_confidence_threshold`, links Person to User (same 1-1 rules), deletes temp selfie. Apply Laravel `throttle:5,1` middleware to this route (5 requests/minute per user — Q-030-44). Register route.
  _Verification commands:_
  - `php artisan test --filter=SelfieClaimTest`
  - `make phpstan`

### I9 – Face Assignment, Dismiss & Cleanup Endpoints

- [x] T-030-25 – Write feature tests for face assignment (FR-030-10, S-030-02, S-030-03).
  _Intent:_ Tests: assign face to existing person, assign face creating new person, reassign face to different person. Test all four `ai_vision_face_permission_mode` values.
  _Verification commands:_
  - `php artisan test --filter=FaceAssignmentTest`
  - `make phpstan`

- [x] T-030-26 – Write feature tests for face dismiss/undismiss and admin bulk delete (API-030-14, API-030-16, S-030-24, S-030-25, Q-030-47).
  _Intent:_ Tests: `PATCH /api/v2/Face/{id}` toggles `is_dismissed`; photo owner can dismiss, non-owner gets 403; admin can always dismiss. `DELETE /api/v2/Face/dismissed` hard-deletes all `is_dismissed = true` faces, removes crop files, returns count. Emit `face.dismissed` / `face.undismissed` / `face.bulk_deleted` telemetry events (TE-030-10/11/12).
  _Verification commands:_
  - `php artisan test --filter=FaceDismissTest`
  - `make phpstan`

- [x] T-030-26b – Implement FaceController: `assign`, `toggleDismissed`, and `destroyDismissed` actions (API-030-09, API-030-14, API-030-16).
  _Intent:_ `POST /Face/{id}/assign`: accepts `person_id` OR `new_person_name`; creates Person if needed; updates `face.person_id`. `PATCH /Face/{id}`: flips `is_dismissed`; auth: photo owner or admin; emits `face.dismissed` or `face.undismissed`. `DELETE /Face/dismissed`: admin-only; loops `is_dismissed = true` faces, deletes crop files from `uploads/faces/`, deletes Face records, emits `face.bulk_deleted` with count. Create form requests: `AssignFaceRequest`, `ToggleDismissedRequest`. Register routes.
  _Verification commands:_
  - `php artisan test --filter=FaceAssignmentTest`
  - `php artisan test --filter=FaceDismissTest`
  - `make phpstan`

### I10 – Scan Trigger & Result Ingestion Endpoints

- [x] T-030-27 – Write feature tests for scan trigger and result ingestion (FR-030-07, FR-030-08, S-030-01, S-030-07, S-030-08, S-030-14, S-030-23).
  _Intent:_ Tests: trigger scan for photo (202), trigger scan for album, receive results (Face records created with crop_token), re-scan replaces old faces (old crops deleted), invalid photo_id (404), auto-scan on upload when enabled. Test both permission modes for scan trigger.
  _Verification commands:_
  - `php artisan test --filter=FaceDetectionTest`
  - `make phpstan`

- [x] T-030-28 – Write feature test for service unavailability (FR-030-08, NFR-030-03, S-030-09).
  _Intent:_ Test: scan trigger when Python service is unreachable returns 503; all other Lychee endpoints continue to work.
  _Verification commands:_
  - `php artisan test --filter=FaceDetectionServiceUnavailableTest`
  - `make phpstan`

- [x] T-030-29 – Implement FaceDetectionController, DispatchFaceScanJob, ProcessFaceDetectionResults, and auto-on-upload hook (API-030-10, API-030-11, API-030-12, S-030-23, Q-030-28/33/34/35/45).
  _Intent:_ `scan` action: validate target (`photo_ids[]` or `album_id`), set `face_scan_status = pending`, dispatch DispatchFaceScanJob in chunks of `ai_vision_face_scan_batch_size` (default 200, Q-030-45), return 202. Job sends HTTP `POST /detect` with `photo_path` (filesystem via shared volume) — **no `callback_url` in body** (Python reads callback URL from `VISION_FACE_LYCHEE_API_URL` env, Q-030-28). `results` action: validate X-API-Key; on success — decode base64 crops, store at `uploads/faces/{tok[0:2]}/{tok[2:4]}/{tok}.jpg`, create Face records with `crop_token`, create/replace FaceSuggestion rows from `suggestions[]` (Q-030-33), IoU-match old faces on re-scan to preserve `person_id` (Q-030-14; threshold from `VISION_FACE_RESCAN_IOU_THRESHOLD`, Q-030-35), set `face_scan_status = completed`; on error — set `face_scan_status = failed` (Q-030-17). `bulk-scan` action: enqueue photos where `face_scan_status IS NULL` (Q-030-40/41). Auto-on-upload: listener on PhotoSaved event dispatches job when `ai_vision_face_enabled = 1`.
  _Verification commands:_
  - `php artisan test --filter=FaceDetection`
  - `make phpstan`

### I11 – Bulk Scan Commands & Maintenance Endpoints

- [x] T-030-30 – Write feature tests for `lychee:scan-faces` command (FR-030-09, S-030-06, CLI-030-01, CLI-030-02).
  _Intent:_ Tests: command enqueues photos where `face_scan_status IS NULL` (not failed/completed), `--album` filter works (non-recursive — only direct photos in album, Q-030-41), already-scanned photos skipped.
  _Verification commands:_
  - `php artisan test --filter=ScanFacesCommandTest`
  - `make phpstan`

- [x] T-030-31 – Implement `lychee:scan-faces` and `lychee:scan-faces --album={id}` commands (CLI-030-01, CLI-030-02).
  _Intent:_ Query photos where `face_scan_status IS NULL`, dispatch DispatchFaceScanJob. `--album={id}` limits to direct photos in that album (non-recursive). Progress output per batch.
  _Verification commands:_
  - `php artisan test --filter=ScanFacesCommandTest`
  - `make phpstan`

- [x] T-030-31b – Implement `lychee:rescan-failed-faces [--stuck-pending] [--older-than=N]` command (CLI-030-03, Q-030-40/48).
  _Intent:_ Default: re-enqueue all photos where `face_scan_status = 'failed'`. With `--stuck-pending`: additionally reset photos with `face_scan_status = 'pending'` and `updated_at < now() - N minutes` (default `--older-than=60`) back to `null`, making them eligible for a fresh scan. *(Q-030-48)*
  _Verification commands:_
  - `php artisan test --filter=RescanFailedFacesCommandTest`
  - `make phpstan`

- [x] T-030-31c – Write feature tests and implement Maintenance::resetStuckFaces endpoints (API-030-17, API-030-17b, Q-030-48, S-030-26).
  _Intent:_ `GET /api/v2/Maintenance::resetStuckFaces`: admin-only check — returns `{count: N}` for photos stuck in `pending` longer than `older_than_minutes` (default 60). `POST /api/v2/Maintenance::resetStuckFaces`: admin-only do — resets those records to `null` and returns `{reset_count: N}`. Body: optional `older_than_minutes` (integer). Follows existing `Maintenance::cleaning` / `Maintenance::jobs` check/do pattern. Register in `api_v2.php`.
  _Verification commands:_
  - `php artisan test --filter=MaintenanceResetStuckFacesTest`
  - `make phpstan`

### I12 – Person Photos Endpoint

- [x] T-030-32 – Write feature test for Person photos listing (FR-030-03, S-030-12, API-030-08).
  _Intent:_ Tests: get paginated photos for person, respects album access control (user without album access doesn't see photo), empty result for person with no faces.
  _Verification commands:_
  - `php artisan test --filter=PersonPhotosTest`
  - `make phpstan`

- [x] T-030-33 – Implement PersonPhotosController (API-030-08).
  _Intent:_ GET /Person/{id}/photos: paginated photos through Face join, apply PhotoQueryPolicy for access control. Register route.
  _Verification commands:_
  - `php artisan test --filter=PersonPhotosTest`
  - `make phpstan`

### Phase 3: Frontend (Vue3/TypeScript)

### I13 – Frontend: People Page

- [x] T-030-34 – Create People.vue, PeopleService.ts, and PersonCard.vue (UI-030-01).
  _Intent:_ People page at /people route. Grid of PersonCard components (server-side face crop thumbnail from crop_url, name, photo count). PeopleService: getPeople(), getPerson(), etc. Empty state when no persons exist. Service unavailable state (toast notification). Navigation link in sidebar.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I14 – Frontend: Person Detail Page

- [x] T-030-35 – Create PersonDetail.vue (UI-030-02).
  _Intent:_ Person detail at /people/:id. Person info header (name, counts, linked user, searchability badge). Paginated photo grid (reuse existing layout components). Action buttons: Edit, Toggle searchable, Merge, Delete. Route registration.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I15 – Frontend: Face Overlays on Photo Detail

- [x] T-030-36 – Create FaceOverlay.vue and integrate into photo detail (UI-030-03).
  _Intent:_ Positioned div overlays on photo using bounding box percentages (x, y, width, height as CSS left/top/width/height %). Name label per overlay. "Unknown" for unassigned faces. Non-searchable faces: overlays hidden entirely; display "{N} face(s) hidden for privacy" message when `hidden_face_count > 0` (Q-030-10 resolved). Click unassigned → open assignment modal. Responsive scaling with image container.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I16 – Frontend: Face Assignment Modal

- [x] T-030-37 – Create FaceAssignmentModal.vue (UI-030-04).
  _Intent:_ Modal triggered by clicking unassigned face overlay. Face crop preview (from crop_url), confidence display. PrimeVue Dropdown to select existing person (with filter). Text input for new person name. Calls FaceService.assign() on confirm. Refreshes face overlays after success.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I17 – Frontend: Scan Trigger UI

- [x] T-030-38 – Add scan trigger buttons to photo/album context menus and admin page (UI-030-05, UI-030-06).
  _Intent:_ "Scan for faces" in photo context menu (calls FaceDetectionService.scan). "Scan album" in album context menu. "Bulk scan all photos" in admin Maintenance page. Progress toast during scanning. Graceful handling when service unavailable.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I18 – Frontend: Selfie Upload Claim

- [x] T-030-39 – Create SelfieClaimModal.vue and integrate into user profile (UI-030-07, S-030-20, S-030-21, S-030-22).
  _Intent:_ Modal with file upload area (drag & drop or click) for selfie image. Sends to API-030-13. Displays matching Person result (face crop, name, confidence score). Confirm button links Person to User. Error states: no face detected, no match found, already claimed. "Find me in photos" button on user profile page triggers modal.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### Phase 4: Documentation

### I19 – Documentation & Quality Gate

- [x] T-030-40 – Update knowledge-map.md with Person/Face models and service integration.
  _Intent:_ Add Person, Face to Domain Layer models. Add Python face-recognition service to Dependencies. Add inter-service communication to Architectural Patterns. Add shared Docker volume architecture.
  _Verification commands:_
  - Review documentation for accuracy.

- [x] T-030-41 – Update database-schema.md with persons and faces tables.
  _Intent:_ Add table definitions (including `crop_token` on faces, `face_suggestions` table, `face_scan_status` on photos), relationships, indexes, and constraints.
  _Verification commands:_
  - Review documentation for accuracy.

- [x] T-030-42 – Create configure-facial-recognition.md how-to guide.
  _Intent:_ Docker setup instructions, shared volume configuration, environment variables, permission modes (open/restricted), service health check, troubleshooting.
  _Verification commands:_
  - Review documentation for accuracy.

### I20 – Clustering Endpoint: Python `POST /cluster` + PHP Ingestion & Trigger

- [x] T-030-43 – Add `cluster_label` column migration to `faces` table (DO-030-07, Q-030-49).
  _Intent:_ New migration: `ALTER TABLE faces ADD COLUMN cluster_label INT NULL`. Add composite index `(cluster_label, person_id, is_dismissed)` on `faces` to support O(index-scan) `GROUP BY cluster_label` paging in API-030-18. This migration is a prerequisite for T-030-44 (Python cluster ingestion) and T-030-50 (cluster-review API). Run tests to confirm migration applies cleanly on SQLite.
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`

- [x] T-030-44 – Implement Python `POST /cluster` endpoint and wire `FaceClusterer` (FR-030-13, S-030-27, Q-030-49).
  _Intent:_ Add `ClusterResponse` Pydantic schema (`{clusters: int, faces_labeled: int, suggestions_generated: int}`) to `app/api/schemas.py`. Add `VISION_FACE_CLUSTER_EPS` (float, default `0.6`) to `AppSettings`. Extend `app/clustering/clusterer.py` with `run_cluster_and_notify(store, lychee_url, api_key)`: read all embeddings, run DBSCAN, produce (a) `labels` list — `[{face_id: str, cluster_label: int}]` for every non-noise face (noise faces skipped); (b) `suggestions` list — `(face_id, suggested_face_id, confidence)` pairs for every intra-cluster pair (cosine similarity); POST `{labels: [...], suggestions: [...]}` to `{lychee_url}/api/v2/FaceDetection/cluster-results` with `X-API-Key`. Add `POST /cluster` route to `app/api/routes.py` (X-API-Key auth dependency) that calls `run_cluster_and_notify()` and returns `ClusterResponse`. Add unit + integration tests in `tests/test_clustering.py` (mock httpx POST to Lychee).
  _Verification commands:_
  - `cd ai-vision-service && uv run pytest tests/test_clustering.py`
  - `uv run ty check`
  - `uv run ruff check`

- [x] T-030-45 – Write feature tests and implement PHP `POST /FaceDetection/cluster-results` ingestion endpoint (FR-030-13, Q-030-49).
  _Intent:_ New action `clusterResults` on `FaceDetectionController`: auth via X-API-Key header; validate body `{labels: [{face_id: str, cluster_label: int}], suggestions: [{face_id: str, suggested_face_id: str, confidence: float}]}` (both arrays optional — empty = no-op for that field). Processing: (1) if `labels` non-empty — first reset all `faces.cluster_label` to NULL (full re-cluster run), then bulk `UPDATE faces SET cluster_label = ? WHERE id = ?` for each label entry; (2) if `suggestions` non-empty — bulk-upsert `face_suggestions` rows on `(face_id, suggested_face_id)` updating `confidence`. Return `{faces_labeled: N, suggestions_updated: M}`. Feature tests: labels bulk-update `faces.cluster_label` correctly; suggestions upserted; both arrays in same request; invalid API key (401); malformed body (422); unknown face_id (422). Register route in `api_v2.php`.
  _Verification commands:_
  - `php artisan test --filter=FaceClusterResultsTest`
  - `make phpstan`

- [x] T-030-46 – Write feature tests and implement PHP `POST /Maintenance::runFaceClustering` admin trigger (FR-030-13).
  _Intent:_ Admin-only Maintenance endpoint following the existing check/do pattern. `POST /api/v2/Maintenance::runFaceClustering`: calls Python service `POST /cluster` via HTTP with `X-API-Key`; returns 202 Accepted on success; returns 503 if Python service is unreachable (NFR-030-03). Feature tests: admin triggers clustering (202), non-admin gets 403, service unavailable (503). Register route in `api_v2.php`.
  _Verification commands:_
  - `php artisan test --filter=MaintenanceFaceClusteringTest`
  - `make phpstan`

### I21 – Embedding Sync on Deletion + Blur Threshold Filtering

- [x] T-030-47 – Python: `VISION_FACE_BLUR_THRESHOLD` filter in detector (FR-030-02, S-030-30).
  _Intent:_ Add `VISION_FACE_BLUR_THRESHOLD` (float, default `100.0`) to `AppSettings` in `app/config.py`. In `app/detection/detector.py`, after detecting each face and cropping the bounding-box region, compute its Laplacian variance (`cv2.Laplacian(crop_region, cv2.CV_64F).var()`); discard any face whose variance is below the threshold before adding it to the results list. A value of `0.0` disables the filter (all faces pass). Update `tests/test_detection.py`: verify a synthetic blurry patch (Gaussian blur, variance << threshold) is excluded; verify a sharp patch is retained.
  _Verification commands:_
  - `cd ai-vision-service && uv run pytest tests/test_detection.py`
  - `uv run ty check`
  - `uv run ruff check`

- [x] T-030-48 – Python: `DELETE /embeddings` endpoint (FR-030-14, S-030-28, S-030-29).
  _Intent:_ Add `delete_many(face_ids: list[str]) -> int` to the `EmbeddingStore` protocol in `app/embeddings/store.py` and implement it in both `SQLiteStore` and `PgVectorStore` (ignores unknown IDs silently; returns count of rows actually deleted). Add `DELETE /embeddings` route in `app/api/routes.py` (X-API-Key auth): accepts `{face_ids: list[str]}`, calls `store.delete_many()`, returns `{deleted_count: int}`. Add tests in `tests/test_api.py`: success (returns count), invalid API key (401), empty list (400), IDs not in store (returns `{deleted_count: 0}`).
  _Verification commands:_
  - `cd ai-vision-service && uv run pytest tests/test_api.py tests/test_embeddings.py`
  - `uv run ty check`
  - `uv run ruff check`

- [x] T-030-49 – PHP: dispatch embedding deletion after Face hard-deletes (FR-030-14, S-030-28, S-030-29).
  _Intent:_ Create `DeleteFaceEmbeddingsJob` (implements `ShouldQueue`): accepts `array<string> $faceIds`, calls Python `DELETE /embeddings` via HTTP with `X-API-Key`; catches all exceptions, logs a warning (`Log::warning`), and returns without re-throwing (never fails the queue worker or rolls back the Lychee deletion). Dispatch this job from **two explicit call-sites** (no Face model observer — Q-030-52/Option B): (1) in `destroyDismissed` action — collect IDs of dismissed faces before `Face::where('is_dismissed', true)->delete()`, then dispatch job; (2) in `PhotoObserver::deleting` — collect `$photo->faces()->pluck('id')` before cascade delete, then dispatch batch job for those IDs. Write feature tests: `DELETE /Face/dismissed` → job dispatched with correct IDs; Photo delete → job dispatched for cascaded faces; Python service unavailable → Lychee deletion succeeds, warning logged.
  _Verification commands:_
  - `php artisan test --filter=FaceEmbeddingSyncTest`
  - `make phpstan`

### I22 – Cluster Review UI: Browse & Bulk-Name/Dismiss Clusters

- [x] T-030-50 – Write feature tests and implement PHP cluster-review API endpoints (FR-030-15, API-030-18, API-030-19, API-030-20, S-030-31, S-030-32, Q-030-49).
  _Intent:_ `GET /api/v2/FaceDetection/clusters`: `SELECT cluster_label, COUNT(*) as size FROM faces WHERE cluster_label IS NOT NULL AND person_id IS NULL AND is_dismissed = false GROUP BY cluster_label ORDER BY cluster_label LIMIT ? OFFSET ?` (uses composite index DO-030-07); for each cluster, load preview faces via `WHERE cluster_label = ? AND person_id IS NULL AND is_dismissed = false`; return `{cluster_id: int, size: int, faces: FaceResource[]}`. Respects `ai_vision_face_permission_mode`. `POST /api/v2/FaceDetection/clusters/{cluster_id}/assign`: validate `cluster_id` is a valid integer `cluster_label` with qualifying faces; create Person if `new_person_name` supplied (or validate existing `person_id`); bulk `UPDATE faces SET person_id = ? WHERE cluster_label = ? AND person_id IS NULL AND is_dismissed = false`; emit `face.cluster_assigned`; return `{person_id, assigned_count}`. `POST /api/v2/FaceDetection/clusters/{cluster_id}/dismiss`: bulk `UPDATE faces SET is_dismissed = true WHERE cluster_label = ? AND person_id IS NULL AND is_dismissed = false`; emit `face.cluster_dismissed`; return `{dismissed_count}`. Feature tests: list clusters (only qualifying faces; already-assigned or dismissed excluded), 404 for unknown cluster_id, assign cluster (new person + faces linked; existing person used if person_id supplied), dismiss cluster (all qualifying faces marked is_dismissed). Test permission mode enforcement (public/restricted at minimum). Register routes in `api_v2.php`.
  _Verification commands:_
  - `php artisan test --filter=FaceClusterReviewTest`
  - `make phpstan`

- [x] T-030-51 – Create FaceClusterService.ts, FaceClusters.vue page, and wire into navigation (FR-030-15, UI-030-08, S-030-31, S-030-32).
  _Intent:_ Create `services/FaceClusterService.ts` with typed functions: `getClusters(page)`, `assignCluster(clusterId, payload)`, `dismissCluster(clusterId)`, `runClustering()` — all using `${Constants.getApiUrl()}` base URL. New Vue3 view `FaceClusters.vue` at `/people/clusters`. Fetches `GET /FaceDetection/clusters` (paginated, via FaceClusterService). Renders a vertical list of cluster cards; each card shows: first 5 face-crop `<img>` thumbnails (from `crop_url`), "+N more" badge when `size > 5`, size badge, a name `InputText`, a "Create Person & Assign All" `Button` (calls `assignCluster` with `new_person_name`; or if person selected from dropdown, uses `person_id`), and a "Dismiss" `Button` (calls `dismissCluster`). After either action, remove the cluster card from the list without full-page reload. "Run Cluster" `Button` in page header calls `runClustering()` then re-fetches clusters. Empty state illustration when no clusters exist. Add `/people/clusters` route to Vue Router and a "Clusters" navigation link under People in the sidebar (visible only when `ai_vision_face_enabled` is true).
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### Phase 5: Face UX Enhancements

### I23 – Face Dismiss UX: Modal Button + CTRL+Click Overlay

- [ ] T-030-54 – Add "Dismiss" button to FaceAssignmentModal.vue (FR-030-16, S-030-33).
  _Intent:_ Add a "Dismiss" button to FaceAssignmentModal.vue (alongside existing "Cancel" and "Assign" buttons). Clicking "Dismiss" calls `PATCH /Face/{id}` to set `is_dismissed = true`, closes the modal, and refreshes the face overlay on the photo.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [ ] T-030-55 – Implement CTRL+click dismiss shortcut on FaceOverlay.vue (FR-030-16, S-030-34, UI-030-08).
  _Intent:_ In FaceOverlay.vue, listen for CTRL `keydown`/`keyup` events on `window`. When CTRL is held: (a) switch all face rectangle CSS to red dashed borders (`border: 2px dashed red`); (b) change cursor to indicate dismiss action. When a rectangle is clicked in CTRL state: call `PATCH /Face/{id}` directly (no modal), remove the overlay element on success, show success toast. When CTRL is released, revert to normal overlay styles.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I24 – Face Overlay Config Settings & P-Key Toggle

- [ ] T-030-56 – Add config migration for face overlay settings (NFR-030-11, S-030-44).
  _Intent:_ Add two new config entries to the AI Vision category: `ai_vision_face_overlay_enabled` (0|1, default 1, level 1/SE) — master toggle for face overlay rendering; `ai_vision_face_overlay_default_visibility` (string: `visible`|`hidden`, default `visible`, level 1/SE) — default visibility when viewing photos.
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`

- [ ] T-030-57 – Implement face overlay config gating and P-key toggle in FaceOverlay.vue (NFR-030-11, FR-030-21, S-030-41, UI-030-11).
  _Intent:_ Gate FaceOverlay rendering on `ai_vision_face_overlay_enabled` config (if 0, render nothing). Initialize overlay visibility from `ai_vision_face_overlay_default_visibility` config. Register `P` key handler on the photo view (e.g., in the photo composable or FaceOverlay.vue itself) to toggle overlay visibility state. Ensure no conflict with existing key bindings.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I25 – Face Circles in Photo Detail Panel

- [ ] T-030-58 – Add "People in this photo" section to PhotoDetails.vue (FR-030-21, S-030-38, S-030-39, UI-030-10).
  _Intent:_ Add a new section titled "People in this photo" in `PhotoDetails.vue` (photo detail sidebar). Render a horizontal flex row of circular face crop `<img>` elements (48px diameter, `border-radius: 50%`, `object-fit: cover`) sourced from `FaceResource.crop_url`. Below each circle, show person name (`person_name` from FaceResource or "???" for unassigned). Section hidden when: no faces detected, `ai_vision_face_overlay_enabled = 0`, or `ai_vision_enabled = 0`. Click on a face circle → emit event to open FaceAssignmentModal for that face. CTRL+click on a face circle → call `PATCH /Face/{id}` to dismiss (same pattern as I23). Handle overflow: flex row with `overflow-x: auto`.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I26 – Batch Face Operations: API + Frontend

- [ ] T-030-59 – Implement `POST /api/v2/Face/batch` endpoint (FR-030-19, API-030-24, S-030-37).
  _Intent:_ New endpoint in FaceController. Body: `{face_ids: string[], action: "unassign"|"assign", person_id?: string, new_person_name?: string}`. For "unassign": bulk `UPDATE faces SET person_id = NULL WHERE id IN (...)`. For "assign": if `person_id` provided, validate it exists; if `new_person_name` provided, create new Person. Then bulk `UPDATE faces SET person_id = ? WHERE id IN (...)`. Auth: check assign permission for every face. Return `{affected_count: int, person_id?: string}`. Emit `face.batch_updated` telemetry. Create `BatchFaceRequest` form request.
  _Verification commands:_
  - `php artisan test --filter=FaceBatchTest`
  - `make phpstan`

- [ ] T-030-60 – Implement `POST /api/v2/FaceDetection/clusters/{cluster_id}/uncluster` endpoint (FR-030-17, API-030-23, S-030-35).
  _Intent:_ New endpoint in FaceClusterController. Body: `{face_ids: string[]}`. Sets `cluster_label = NULL` for faces matching: `id IN (face_ids) AND cluster_label = cluster_id AND person_id IS NULL AND is_dismissed = false`. Returns `{unclustered_count: int}`. Emit `face.unclustered` telemetry. Create `UnclusterFacesRequest` form request. Register route.
  _Verification commands:_
  - `php artisan test --filter=FaceUnclusterTest`
  - `make phpstan`

- [ ] T-030-61 – Write feature tests for batch face operations and uncluster (FR-030-17, FR-030-19).
  _Intent:_ Tests: batch unassign (person_id set to NULL on selected faces), batch assign to existing person, batch assign creating new person, uncluster faces from cluster (cluster_label set to NULL), auth checks (unauthorized user → 403), invalid face_ids (422), empty face_ids (422).
  _Verification commands:_
  - `php artisan test --filter=FaceBatch`
  - `php artisan test --filter=FaceUncluster`
  - `make phpstan`

- [ ] T-030-62 – Implement batch selection mode in PersonDetail.vue and FaceClusters.vue (FR-030-19, UI-030-12).
  _Intent:_ Add "Select" toggle button to PersonDetail.vue (face grid section) and FaceClusters.vue (each cluster card). When active: checkbox overlay appears on each face crop thumbnail. Selecting faces shows an action bar at the bottom: "Unassign (N)" (PersonDetail only), "Reassign to..." (opens person search dropdown), "Assign to new person" (text input), "Uncluster" (FaceClusters only). Each action calls the corresponding API endpoint. After action, deselect all and refresh the view. Create `FaceBatchService.ts` with typed functions: `batchUpdate(faceIds, action, personId?, newPersonName?)`, `unclusterFaces(clusterId, faceIds)`.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I27 – Maintenance Blocks: Dismiss Cleanup + Reset Failed/Stuck Scans

- [ ] T-030-63 – Implement `DestroyDismissedFaces` maintenance controller (FR-030-23, API-030-21/21b, S-030-42).
  _Intent:_ New maintenance controller class following the existing check/do pattern. `check(MaintenanceRequest)`: returns count of `Face::where('is_dismissed', true)->count()`. Returns 0 if AI Vision is not enabled. `do(MaintenanceRequest)`: reuse `destroyDismissed` logic — collect dismissed face IDs, delete crop files, delete Face records, dispatch `DeleteFaceEmbeddingsJob`, return `{deleted_count}`. Register `GET`/`POST` routes as `Maintenance::destroyDismissedFaces` in `api_v2.php`.
  _Verification commands:_
  - `php artisan test --filter=MaintenanceDestroyDismissedFacesTest`
  - `make phpstan`

- [ ] T-030-64 – Implement `ResetFailedFaceScans` maintenance controller (FR-030-24, API-030-22/22b, S-030-43).
  _Intent:_ New maintenance controller class following check/do pattern. `check(MaintenanceRequest)`: returns count of `Photo::where('face_scan_status', FaceScanStatus::FAILED)->count()`. Returns 0 if AI Vision is not enabled. `do(MaintenanceRequest)`: `Photo::where('face_scan_status', FaceScanStatus::FAILED)->update(['face_scan_status' => null])`, return `{reset_count}`. Emit `face.failed_scans_reset` telemetry. Register routes.
  _Verification commands:_
  - `php artisan test --filter=MaintenanceResetFailedFaceScansTest`
  - `make phpstan`

- [ ] T-030-65 – Create maintenance Vue components for dismiss cleanup and failed scan reset (UI-030-14, UI-030-15).
  _Intent:_ Create `MaintenanceDestroyDismissedFaces.vue`: follows existing `MaintenanceBulkScanFaces.vue` pattern. On mount, calls `GET /Maintenance::destroyDismissedFaces` check endpoint. If count is 0, component renders nothing (v-if). If count > 0, shows card with count and "Destroy All" button. Button calls `POST /Maintenance::destroyDismissedFaces`, refreshes count on success. Create `MaintenanceResetFailedFaceScans.vue` with same pattern for failed scans. Add both components to `Maintenance.vue` template alongside existing face maintenance blocks.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I28 – Merge Person UI + Person Miniature in Dropdown

- [ ] T-030-66 – Create MergePersonModal.vue (FR-030-25, S-030-45, UI-030-13).
  _Intent:_ New modal component `MergePersonModal.vue`. Props: `sourcePerson` (PersonResource). Content: header "Merge {source.name} into:", PrimeVue Dropdown with person search (same custom option template as T-030-67 — miniature + name + count), filter by typing. Warning text explaining merge consequences (face count moved, source deleted, irreversible). Cancel/Merge buttons. On confirm, call `POST /Person/{source.id}/merge` with `{source_person_id: source.id}` (note: URL `{id}` = target, body = source). After success, navigate to target person page. Add "Merge into..." button to PersonDetail.vue actions, gated on merge permission.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [ ] T-030-67 – Add person miniature to FaceAssignmentModal dropdown (FR-030-20, S-030-46, UI-030-09).
  _Intent:_ Update the existing person Dropdown in FaceAssignmentModal.vue to use a custom `option` template (PrimeVue `#option` slot). Each option renders: 24px circular `<img>` (`border-radius: 50%`, `object-fit: cover`) from `person.representative_crop_url`, person name, face count in muted text. Fallback: placeholder person icon (PrimeVue `pi pi-user` or similar) when `representative_crop_url` is null. Reuse this template pattern in MergePersonModal.vue.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

### I29 – Album People Endpoint

- [ ] T-030-68 – Implement `GET /api/v2/Album/{id}/people` endpoint (FR-030-22, API-030-25, S-030-40).
  _Intent:_ New `AlbumPeopleController` with `index()` action. Query joins `photo_albums → photos → faces → persons` to collect distinct persons in the album (non-recursive, direct photos only). Apply `ai_vision_face_permission_mode` visibility rules and `is_searchable` filtering. Return `PaginatedPersonsResource` (consistent with People listing). Create `AlbumPeopleRequest` form request: validate album_id exists, user has album access (use existing album access policy). Register route in `api_v2.php`.
  _Verification commands:_
  - `php artisan test --filter=AlbumPeopleTest`
  - `make phpstan`

- [ ] T-030-69 – Write feature tests for album people endpoint (FR-030-22, S-030-40).
  _Intent:_ Tests: album with persons returns correct distinct list, album with no faces returns empty, non-searchable person filtered out for non-admin, user without album access gets 403, album not found returns 404, pagination works correctly. Test with photos linked via `photo_albums` pivot table.
  _Verification commands:_
  - `php artisan test --filter=AlbumPeopleTest`
  - `make phpstan`

### I30 – Unassign Face from Person

- [ ] T-030-70 – Update face assign endpoint to support unassign (FR-030-18, S-030-36).
  _Intent:_ Update `POST /Face/{id}/assign` in FaceController to accept `person_id: null` (or omitted `person_id` with neither `person_id` nor `new_person_name` present, treated as unassign). Sets `face.person_id = NULL`. Emit `face.unassigned` telemetry with `previous_person_id`. Update `AssignFaceRequest` validation to allow nullable `person_id`. Write feature test: assign a face, then unassign it; verify face is in unassigned state.
  _Verification commands:_
  - `php artisan test --filter=FaceAssignment`
  - `make phpstan`

- [ ] T-030-71 – Add "Remove from person" UI in PersonDetail.vue (FR-030-18).
  _Intent:_ In PersonDetail.vue face grid (non-batch mode), add a small "×" remove button (or right-click context menu) on each face crop. Clicking calls `POST /Face/{id}/assign` with `person_id: null`. After success, remove the face from the grid and update the face count.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

## Notes / TODOs

**Q-030-01 through Q-030-53 have been resolved.** All decisions are encoded in spec.md normative sections.

**Q-030-54 through Q-030-64 resolved** (2026-04-04): dismiss UX, maintenance blocks, uncluster, unassign, batch ops, person miniatures, face circles in detail panel, overlay config, album people, merge UI, policy refinement (deferred).

**Active questions (Q-030-65 through Q-030-73):** P-key binding conflict, album people recursive scope, batch selection UX, merge UI location, person miniature layout, CTRL+click on touch, face circle overflow, policy album/photo rights, maintenance block granularity. See open-questions.md.
