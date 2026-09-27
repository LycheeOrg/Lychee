# Open Questions – Feature 030

Open questions for [Feature 030](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-030-01: Communication Protocol Between Python Face-Recognition Service and Lychee~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** High
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A** — REST API with webhook callbacks. Lychee sends scan requests to the Python service's REST API; the Python service calls back to Lychee's `/api/v2/FaceDetection/results` endpoint when results are ready.

**Rationale:** Simplest architecture, stateless, easy to debug, works with existing HTTP infrastructure. No additional broker dependencies.

**Spec Impact:** FR-030-07, FR-030-08 confirmed with REST+callback pattern. Inter-service contract in spec appendix is authoritative.

**Resolved:** 2026-03-15

---

### ~~Q-030-02: Face Detection Trigger Mechanism~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** High
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A** — Multiple triggers: automatic on upload (via queue job), manual scan (photo/album), and admin bulk-scan command.

**Rationale:** Covers all use cases. New photos auto-processed; existing libraries backfilled via bulk scan; manual scan for on-demand needs.

**Spec Impact:** FR-030-08 (manual scan), FR-030-09 (bulk scan) confirmed. Auto-on-upload trigger added to plan as I7 sub-task.

**Resolved:** 2026-03-15

---

### ~~Q-030-03: Face Clustering and Assignment Workflow~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** High
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A** — Auto-cluster with manual confirmation. Python service clusters face embeddings and suggests groupings. Users review, name clusters (creating Person records), and can merge/split. Unknown faces grouped as "Unknown" until assigned.

**Rationale:** Best balance of automation and user control. Leverages ML capability while keeping human in the loop.

**Spec Impact:** Clustering result ingestion added to inter-service contract. UI for cluster review added to frontend increments.

**Resolved:** 2026-03-15

---

### ~~Q-030-04: Face Embedding Storage Location~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A** — Python service owns embeddings in its own storage. Lychee's `faces` table stores only bounding box, confidence, person_id, photo_id. No raw embedding data in Lychee DB.

**Rationale:** Keeps Lychee DB lean; vector similarity search belongs in the Python service; clean separation of concerns.

**Spec Impact:** DO-030-02 (Face) confirmed without embedding column. NFR-030-05 (versioned contract) covers embedding_id reference.

**Resolved:** 2026-03-15

---

### ~~Q-030-05: "Non-Searchable" Person Semantics~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A** — Non-searchable Person hidden from search results AND People browsing page for all users except the Person's linked User and admins. Faces still detected and stored internally.

**Rationale:** Privacy-respecting; person can opt out of being discoverable; data remains available for the linked user and administrators.

**Spec Impact:** FR-030-06 updated with full visibility rules. NFR-030-04 confirmed. S-030-05, S-030-15 test scenarios confirmed.

**Resolved:** 2026-03-15

---

### ~~Q-030-06: Person-User Tie Purpose and Semantics~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A (extended)** — Self-identification ("this Person is me") with two additions:
1. **Admin override:** Admins can link/unlink any Person-User pair, overriding user claims.
2. **Selfie-upload claim:** Users can upload a photo of themselves; the Python service matches the selfie against existing face embeddings to find and assign the matching Person record.

**Rationale:** Self-identification enables privacy self-service and "find photos of me". Admin override provides governance. Selfie-upload leverages the face recognition service for convenient self-assignment without manual browsing.

**Spec Impact:** FR-030-05 updated with admin override. New FR-030-12 added for selfie-upload claim flow. New API endpoint (API-030-13) and UI state (UI-030-07) added. Plan increment I5 extended with selfie-upload sub-tasks.

**Resolved:** 2026-03-15

---

### ~~Q-030-07: How Does the Python Service Access Photo Files?~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** High
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A** — Shared Docker volume. Both containers mount the same storage volume. The scan request includes a `photo_path` (filesystem path) instead of a URL. Python service reads directly from disk.

**Rationale:** Fastest access; no auth complexity; works with private photos; no network overhead. Deployment requires both containers to share the photos volume.

**Spec Impact:** Inter-service contract updated: `photo_url` replaced with `photo_path` in scan request. Deployment docs must specify shared volume configuration. NFR added for S3/remote storage documentation (FUSE mount or alternative).

**Resolved:** 2026-03-15

---

### ~~Q-030-08: Permission Model for People/Face Operations~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** High
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option C** — Configurable via admin setting (`face_recognition_permission_mode`). Two modes:
- **"open"** (default): Any authenticated user can perform all CRUD/assign/merge operations. Only bulk scan restricted to admin.
- **"restricted"**: Photo-owner-centric with admin escalation:
  - Create Person: Any authenticated user.
  - Update/Delete Person: Linked User, creator, or admin.
  - Assign Face: Photo owner or admin.
  - Trigger scan: Photo/album owner or admin.
  - Bulk scan: Admin only.
  - Merge Persons: Admin only.
  - Claim Person: Any authenticated user.

**Rationale:** Accommodates both single-user/family instances (open mode) and multi-user deployments (restricted mode). Default is "open" since most Lychee instances are single-user.

**Spec Impact:** New config entry `face_recognition_permission_mode` (enum: open, restricted). FR-030-05/08/10/11 updated with conditional authorization. New NFR for permission mode testing (both modes covered by feature tests).

**Resolved:** 2026-03-15

---

### ~~Q-030-09: Face Crop Thumbnail Generation~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** High
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option B** — Server-side crop stored as a new asset. The Python service generates a cropped face thumbnail (150x150px) during face detection and includes it in the scan result callback. The crop is stored alongside size variants. The Face record includes a `crop_path` field.

**Rationale:** Crisp thumbnails optimized for People page grid; fast rendering from small pre-generated files; Python service already has the image loaded during detection so the crop is essentially free.

**Spec Impact:** DO-030-02 (Face) gains `crop_path` field. Inter-service contract updated: scan result includes `crop` (base64 JPEG) per face. New migration adds `crop_path` to faces table. I16 Python service includes crop generation.

**Resolved:** 2026-03-15

---

### ~~Q-030-10: Non-Searchable Person Face Overlay Behavior~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option B (extended)** — Hide the overlay entirely for non-searchable persons, but include a summary indicator: "N faces detected but hidden for privacy reasons" displayed below the photo or in the faces info bar. The count does not reveal which specific persons are hidden.

**Rationale:** Maximum privacy — no hint about which specific face was identified. The summary count maintains transparency about face detection having occurred without leaking person-specific data.

**Spec Impact:** FR-030-04 updated: photo detail response excludes Face records for non-searchable persons (for unauthorized viewers), but includes `hidden_face_count` (integer). Frontend displays "{N} face(s) hidden for privacy" when count > 0. NFR-030-04 test cases updated.

**Resolved:** 2026-03-15

---

### ~~Q-030-11: Selfie Image Lifecycle~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A** — Discard immediately after match. The selfie is held in memory/temp storage only during the matching request. Once the Python service returns its result, the image is deleted. No permanent record.

**Rationale:** Privacy-friendly; no unnecessary data retention; simpler storage. Users can re-upload if they want to retry.

**Spec Impact:** FR-030-12 confirmed: selfie is transient. No storage schema changes needed for selfie retention. Implementation uses temp file or in-memory buffer.

**Resolved:** 2026-03-15

---

### ~~Q-030-12: Selfie Match Inter-Service Contract~~ ✅ RESOLVED

**Feature:** 030 – Facial Recognition
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-03-15

**Resolution:** **Option A** — Dedicated match endpoint on Python service. `POST /match` accepts an image file (multipart) and returns top-N matching embedding references with confidence scores.

Contract:
```json
// Request: POST /match (multipart form with "image" file field)
// Response:
{
  "matches": [
    { "embedding_id": "emb_001", "person_suggestion": "cluster_42", "confidence": 0.963 },
    { "embedding_id": "emb_002", "person_suggestion": "cluster_17", "confidence": 0.412 }
  ]
}
```

Lychee maps `embedding_id` back to Face records (which have person_id) to identify the matching Person. The `person_suggestion` field is advisory (from clustering) and may be null.

**Rationale:** Clean separation; Python service owns matching logic; single round trip; Lychee just consumes results.

**Spec Impact:** Inter-service contract appendix updated with `/match` endpoint. I17 Python service implements the endpoint. I5 Lychee SelfieClaimController consumes it.

**Resolved:** 2026-03-15

---

### ~~Q-030-13: Embedding ID → Person Mapping Gap in Selfie Match Flow~~ ✅ RESOLVED

**Resolution:** **Option A** — Store `lychee_face_id` in Python's embedding DB. When Lychee ingests a scan callback it creates Face records and returns the `embedding_id → lychee_face_id` mapping in the HTTP 200 response body. Python persists each mapping. The `/match` endpoint returns `lychee_face_id` (not `embedding_id`); Lychee resolves `lychee_face_id → Face → person_id`.

**Spec Impact:** Update `DetectCallbackPayload` response body to include `{"faces": [{"embedding_id": "...", "lychee_face_id": "..."}]}`. Update `MatchResult` Pydantic model: replace `embedding_id` with `lychee_face_id`. Update FR-030-12, API-030-13, I2, I8, inter-service contract.

**Resolved:** 2026-03-17

---

### ~~Q-030-14: Re-scan Destroys Manual Face Assignments~~ ✅ RESOLVED

**Resolution:** **Options A + C** — On re-scan, new faces are matched to existing faces by bounding box IoU (≥ threshold); matched old face's `person_id` is carried over to the new face record; truly gone faces are deleted. Additionally, if a photo has any faces with a `person_id` assigned, re-scan is blocked unless the request includes `force: true`. Without `force: true` a 409 Conflict is returned listing the number of assigned faces at risk.

**Spec Impact:** Update FR-030-07 (re-scan idempotency now caveated with IoU preservation + force flag). Update S-030-14. Update `ProcessFaceDetectionResults` action description. Update API-030-10 to document optional `force` parameter.

**Resolved:** 2026-03-17

---

### ~~Q-030-15: Two API Keys but Lychee Config Only Defines One~~ ✅ RESOLVED

**Resolution:** **Option A** — Single shared symmetric key for both directions. Header: `X-API-Key: <key>`. The key is defined in `.env` as `AI_VISION_API_KEY` (after Q-030-19 renaming). **Critical separation of concerns:** the AI vision callback endpoints (`POST /api/v2/FaceDetection/results`) are authenticated **exclusively** via the API key header — no user session, no admin session. Even authenticated admins cannot reach these endpoints through the normal auth middleware. Lychee-to-Python requests likewise send `X-API-Key` with the same shared key.

**Spec Impact:** Update config migration to single key `ai_vision_api_key`. Add note that FaceDetection/results middleware skips session auth. Update NFR-030-07, I3, I4, I10, inter-service contract, AppSettings.

**Resolved:** 2026-03-17

---

### ~~Q-030-16: Missing Face Deletion Endpoint for False Positives~~ ✅ RESOLVED

**Resolution:** **Option C (dismiss-first)** — Users dismiss false positives via `PATCH /api/v2/Face/{id}` (toggles `is_dismissed`). Dismissed faces are hidden from face overlays and assignment UI. Admin can hard-delete all dismissed faces in bulk from the Maintenance page (a new maintenance action); this permanently removes the Face records + crop files.

**Spec Impact:** Add `is_dismissed` boolean (default `false`) to DO-030-02 and Face migration. Add API-030-14 (`PATCH /api/v2/Face/{id}` dismiss toggle). Add admin maintenance action for bulk hard-delete of dismissed faces. Update UI-030-03 (face overlay hides dismissed faces).

**Resolved:** 2026-03-17

---

### ~~Q-030-17: Error Callback Shape Undefined~~ ✅ RESOLVED

**Resolution:** **Option A** — Python posts an error callback payload to the same `callback_url`: `{"photo_id": "abc", "status": "error", "error_code": "corrupt_file", "message": "..."}`. Lychee sets `face_scan_status = failed`. Python defines `ErrorCallbackPayload` Pydantic model. No timeout mechanism; status transitions only occur via explicit callbacks.

**Spec Impact:** Add `ErrorCallbackPayload` Pydantic model. Update FR-030-07 (result endpoint handles both success and error payloads). Update `face_scan_status` state machine in spec. Update I2, I10.

**Resolved:** 2026-03-17

---

### ~~Q-030-18: Spec DSL Type Mismatch — Face.person_id~~ ✅ RESOLVED

**Resolution:** **Option A** — Fix `person_id` field in DO-030-02 DSL from `type: integer` to `type: string`.

**Spec Impact:** Update DO-030-02 Spec DSL `person_id` type field. Low impact.

**Resolved:** 2026-03-17

---

### ~~Q-030-19: Naming Inconsistency — FACE_* Prefix vs ai-vision-service~~ ✅ RESOLVED

**Resolution:** **Option B** — Rename for future-proofing. Python env vars use `VISION_*` prefix; Lychee config keys use `ai_vision_*` prefix. All documentation, docker-compose, and AppSettings updated accordingly.

**Spec Impact:** Rename `FACE_*` → `VISION_*` throughout Python service config and docker-compose. Rename `face_recognition_*` → `ai_vision_*` for all Lychee config keys. Update AppSettings `env_prefix`. Update all env variable tables in spec and docs.

**Resolved:** 2026-03-17

---

### ~~Q-030-20: Permission Mode Scope per Operation Is Ambiguous~~ ✅ RESOLVED

**Resolution:** **Option C** — Four-mode enum (`public`, `private`, `privacy-preserving`, `restricted`) with a per-operation matrix:

| Operation          | public       | private      | privacy-preserving        | restricted                |
|--------------------|--------------|--------------|---------------------------|---------------------------|
| View People page   | guest        | logged users | photo/album owner + admin | admin only                |
| View face overlays | album access | logged users | photo/album owner + admin | photo/album owner + admin |
| Create/edit Person | logged users | logged users | photo/album owner + admin | admin only                |
| Assign face        | logged users | logged users | photo/album owner + admin | admin only                |
| Trigger scan       | logged users | logged users | photo/album owner + admin | photo/album owner + admin |
| Claim person       | logged users | logged users | logged users              | all users                 |
| Merge persons      | logged users | logged users | photo/album owner + admin | admin only                |

**Spec Impact:** Update `ai_vision_permission_mode` to a 4-value enum. Update NFR-030-07 with full matrix. Update FR-030-08 authorization description. Update all controller authorization references (I7, I8, I9, I10).

**Resolved:** 2026-03-17

---

### ~~Q-030-21: Missing Person Unclaim Endpoint~~ ✅ RESOLVED

**Resolution:** **Option A** — Add `DELETE /api/v2/Person/{id}/claim` as API-030-15. Removes `person.user_id` (sets to null). Linked user or admin only.

**Spec Impact:** Add API-030-15 to API catalogue and Spec DSL routes. Update FR-030-05 to reference unclaim. Update I8.

**Resolved:** 2026-03-17

---

### ~~Q-030-22: Merge Direction Ambiguity on API-030-06~~ ✅ RESOLVED

**Resolution:** **Option A** — `{id}` = target (kept). Body parameter renamed to `source_person_id`. Follows REST convention: the URL resource is the one preserved.

**Spec Impact:** Update API-030-06 body param from `target_person_id` to `source_person_id`. Update FR-030-11. Update I8 and I14 (frontend merge action).

**Resolved:** 2026-03-17

---

### ~~Q-030-23: face_scan_status State Machine Transitions Undefined~~ ✅ RESOLVED

**Resolution:** **Option A** — Explicit state machine:
1. `null → pending`: set on **dispatch** (when the scan job is enqueued, before the HTTP request to Python is sent).
2. `pending → completed`: set when Lychee receives a **success** callback from the Python service.
3. `pending → failed`: set when Lychee receives an **error** callback from Python. No timeout mechanism (async model; Lychee never waits for a response).
4. Retry/re-scan: `failed → pending` (retry) and `completed → pending` (re-scan) are both **allowed**.
5. Duplicate pending: **reset** to `pending` (do not ignore); the earlier `pending` could be a silent timeout.

**Spec Impact:** Document state machine in FR-030-07/NFR section. Update I10, I11, DispatchFaceScanJob, ProcessFaceDetectionResults.

**Resolved:** 2026-03-17

---

### ~~Q-030-24: Similar Faces in Assignment Modal — Data Source Unspecified~~ ✅ RESOLVED

**Resolution:** **Option A, stored in a dedicated suggestions table** — Python includes a `suggestions` array per face in the `DetectCallbackPayload`. Lychee persists these in a `face_suggestions` table (`face_id`, `person_id`, `confidence`). The assignment modal reads from this table. New domain object `FaceSuggestion` added.

**Spec Impact:** Add `FaceSuggestion` domain object (DO-030-05). Add `face_suggestions` table to migrations. Update `FaceResult` Pydantic model to include `suggestions: list[SuggestionResult]`. Update UI-030-04. Update I2, I10, I16.

**Resolved:** 2026-03-17

---

### ~~Q-030-25: Crop Storage Path Pattern Undefined~~ ✅ RESOLVED

**Resolution:** **Option B** — Crops stored at `uploads/faces/{face_id}.jpg` in a dedicated `faces/` subdirectory under the main uploads directory. Served via a separate media controller route (not the standard photo size-variant pipeline).

**Spec Impact:** Update DO-030-02 `crop_path` description. Update `crop_url` accessor. Add a new route for serving face crops. Update I6, I10, I16.

**Resolved:** 2026-03-17

---

### ~~Q-030-26: Python Concurrency Model — CPU-Bound Face Detection Blocks Event Loop~~ ✅ RESOLVED

**Resolution:** **Option A** — inference runs in a `ThreadPoolExecutor` via `asyncio.run_in_executor`, keeping the FastAPI event loop responsive while CPU-bound detection executes on a background thread. Pool size is configurable via `VISION_FACE_THREAD_POOL_SIZE` env var (default `1`). The service must emit structured log entries at three checkpoints: job received (`INFO`), detection started (`INFO`), and detection finished (`INFO` with face count and elapsed milliseconds). Callback failures are logged at `ERROR` level.

**Spec Impact:** Add `thread_pool_size: int = 1` to `AppSettings`. Add `VISION_FACE_THREAD_POOL_SIZE` to env var table. Add "Concurrency Model" subsection to Python Service Technical Specification documenting the `run_in_executor` pattern and the structured logging checkpoints table.

**Resolved:** 2026-03-17

---

### ~~Q-030-27: Callback Retry Policy — Stuck-Pending Risk When Python→Lychee POST Fails~~ ✅ RESOLVED

**Resolution:** **Option B** — fire-and-forget. Python makes one callback attempt. If the request fails (network error, 5xx), the failure is logged at `ERROR` level and discarded. The photo's `face_scan_status` remains `pending` indefinitely; operators must reset stuck records manually. No retry logic in the Python service; no outbox table.

**Spec Impact:** Document fire-and-forget policy in the "Concurrency Model" subsection. Add `ERROR` log entry for callback failure in the structured logging table. Note in state machine documentation that `pending` can become permanently stuck on callback failure; add an operator note.

**Resolved:** 2026-03-17

---

### ~~Q-030-28: Security — `photo_path` Path Traversal and `callback_url` SSRF~~ ✅ RESOLVED

**Resolution:** **Option A, extended** — validate `photo_path` resolves within `VISION_FACE_PHOTOS_PATH` (resolve symlinks, reject traversals with 422). `callback_url` is **removed from the `DetectRequest` body entirely** — Python reads the callback endpoint from `VISION_FACE_LYCHEE_API_URL` env var. Since the callback URL is operator-supplied via env and not present in the request payload, the SSRF vector is eliminated structurally rather than via allowlist validation.

**Spec Impact:** Remove `callback_url` field from `DetectRequest` Pydantic model. Remove `callback_url` from Scan Request JSON example. Add path-traversal validation note to `DetectRequest.photo_path` field comment. Update inter-service contract description and the scan request JSON example.

**Resolved:** 2026-03-17

---

### ~~Q-030-29: Suggestion Items — `embedding_id` vs. `lychee_face_id` in Callback Suggestions~~ ✅ RESOLVED

**Resolution:** **Option A** — Python sends `lychee_face_id` in suggestion items (it already stores them from prior callback 200 responses). Rename `SuggestionResult.embedding_id` → `lychee_face_id`. Lychee stores `(face_id, suggested_face_id, confidence)` in `face_suggestions` using `lychee_face_id` directly — no cross-callback resolution needed.

**Spec Impact:** Rename `SuggestionResult.embedding_id` → `lychee_face_id` in Pydantic schemas. Update suggestion examples in the callback JSON. Update `FaceResult.suggestions` comment. Update `face_suggestions` table schema note (`DO-030-05`).

**Resolved:** 2026-03-17

---

### ~~Q-030-30: Clustering Trigger — When Does DBSCAN Run and How Does It Feed Suggestions?~~ ✅ RESOLVED

**Resolution:** **Option A** — per-scan suggestions use **nearest-neighbour cosine similarity search** against stored embeddings via `sqlite-vec`/`pgvector` (fast, inline with the detection job). DBSCAN is a **separate offline batch operation** grouping unassigned faces for the People browse UI; triggered manually via `POST /cluster` and never invoked per scan request.

**Spec Impact:** Update `clustering/clusterer.py` description in project structure (offline batch, not per-scan). Update DBSCAN tech stack table entry. Add `POST /cluster` to routes list. Clarify `SuggestionResult` data source as NN cosine similarity search.

**Resolved:** 2026-03-17

---

### ~~Q-030-31: `VISION_CONFIDENCE_THRESHOLD` — Detection Filter vs. Matching Threshold~~ ✅ RESOLVED

**Resolution:** **Option B** — two separate thresholds. Rename `VISION_CONFIDENCE_THRESHOLD` → `VISION_FACE_DETECTION_THRESHOLD` (bounding box filter: faces below threshold excluded from callback payloads) and add `VISION_FACE_MATCH_THRESHOLD` (similarity search cutoff: suggestions and selfie match results below threshold excluded). Independent configuration allows operators to tune detection sensitivity and identity matching independently.

**Spec Impact:** Remove `VISION_CONFIDENCE_THRESHOLD` from env var table. Add `VISION_FACE_DETECTION_THRESHOLD` (default `0.5`) and `VISION_FACE_MATCH_THRESHOLD` (default `0.5`). Rename `AppSettings.confidence_threshold` → `detection_threshold` + add `match_threshold`. Update `app/detection/detector.py` and `app/matching/matcher.py` references.

**Resolved:** 2026-03-17

---

### ~~Q-030-32: InsightFace Model Acquisition — Baked Into Docker Image vs. Runtime Download~~ ✅ RESOLVED

**Resolution:** **Option A** — bake `buffalo_l` model weights into the Docker image at build time via a `RUN` step in the builder stage. The multi-stage Dockerfile copies the downloaded model folder from builder to runtime. Image is significantly larger (~1GB+) but starts instantly and works in airgapped environments. Model updates require an image rebuild (acceptable given model stability).

**Spec Impact:** Update Dockerfile spec: add `RUN uv run python -c "..."` model download step in builder stage; add `COPY --from=builder /root/.insightface /root/.insightface` in runtime stage. Note model size and rebuild requirement in Docker configuration section.

**Resolved:** 2026-03-17

---

### ~~Q-030-33: `face_suggestions` Schema Wrong — Face-to-Face, Not Face-to-Person~~ ✅ RESOLVED

**Resolution:** **Option A** — schema changed to `(face_id FK→faces, suggested_face_id FK→faces, confidence)`. Both FKs point to `faces`. Python sends `lychee_face_id` (a Face ID) as the suggestion target — there is no concept of Persons in the Python service, and suggestions may reference unassigned faces (where `person_id IS NULL`). The assignment modal resolves `suggested_face_id → faces → persons` via LEFT JOIN at read time. A unique constraint on `(face_id, suggested_face_id)` prevents duplicate suggestion rows.

**Spec Impact:** Updated DO-030-05 (domain object table and DSL). Updated `SuggestionResult` Pydantic model comment. `face_suggestions` migration will use `suggested_face_id` (FK→faces) instead of `person_id` (FK→persons).

**Resolved:** 2026-03-18

**Resolution: Option A adopted.**  nullable INT column on . Spec updated: DO-030-02, DO-030-07, FR-030-13, FR-030-15, API-030-18/19/20.

**Resolved:** 2026-03-23

---

### ~~Q-030-34: Crop Serving Route Undefined~~ ✅ RESOLVED

**Resolution:** **Option B** — crops served directly by nginx with no application-level auth. The crop token stored in the Face model is a random high-entropy identifier (not a sequential ID), so enumeration of `uploads/faces/` is not feasible. Path structure mirrors Lychee's existing size-variant pattern: `uploads/faces/{token[0:2]}/{token[2:4]}/{token}.jpg` (e.g. `uploads/faces/aa/bb/aabbccddeeff0011223344.jpg`). `FaceResource.crop_url` returns this path directly; no dedicated controller route needed. API-030-16 slot is therefore free for the dismissed-face bulk delete (Q-030-43).

**Spec Impact:** Update DO-030-02 and DSL `crop_token` constraint to reflect the two-level hash path and nginx-direct serving.

**Resolved:** 2026-03-18

---

### ~~Q-030-35: IoU Threshold for Re-scan Face Matching Not Defined~~ ✅ RESOLVED

**Resolution:** **Option B** — add `VISION_FACE_RESCAN_IOU_THRESHOLD` env var (default `0.5`) mapped to `AppSettings.rescan_iou_threshold`. Allows operators to tune matching sensitivity for re-scans without rebuilding the image.

**Spec Impact:** Add `rescan_iou_threshold: float = 0.5` to `AppSettings`. Add `VISION_FACE_RESCAN_IOU_THRESHOLD` row to the env var table. Update FR-030-07 resolved note to reference the configurable threshold.

**Resolved:** 2026-03-18

---

### ~~Q-030-36: "Claim Person" in Restricted Mode Listed as "All Users" — Contradictory~~ ✅ RESOLVED

**Resolution:** Fixed in permission matrix — `Claim person` now reads `logged users` for all four modes. "All users" (including unauthenticated guests) would make no sense since claiming requires a User record to link.

**Spec Impact:** Spec line 78 updated. No further changes needed.

**Resolved:** 2026-03-18

---

### ~~Q-030-37: "Unknown" Group in People Page Not Designed~~ ✅ RESOLVED

**Resolution:** **Option A** — virtual aggregate. `GET /api/v2/People` always appends a synthetic `{id: null, name: "Unknown", face_count: N}` entry where `N = COUNT(faces WHERE person_id IS NULL)`. No DB record required. Clicking the tile navigates to `GET /api/v2/Face?unassigned=true`. The entry is omitted when `N = 0`.

**Spec Impact:** Update API-030-01 notes. Add `GET /api/v2/Face?unassigned=true` filter note. Update UI-030-01 description.

**Resolved:** 2026-03-18

---

### ~~Q-030-38: `face_scan_status` Column Type and DSL Entry Missing~~ ✅ RESOLVED

**Resolution:** **Option A** — `VARCHAR(16)`, nullable, with a PHP-side `ScanStatus` Enum cast. Portable across MySQL, PostgreSQL, and SQLite. Consistent with Lychee's existing enum-as-string column pattern.

**Spec Impact:** Add `face_scan_status` field to the `photos` table addendum in the Spec DSL (`type: string (VARCHAR 16)`, nullable, `cast: ScanStatus`). Document the cast in the state machine section.

**Resolved:** 2026-03-18

---

### ~~Q-030-39: Crop Inline Base64 Payload Size Limit Undefined~~ ✅ RESOLVED

**Resolution:** **Option A** — cap at N faces per callback, default `N = 10` (configurable via `VISION_FACE_MAX_FACES_PER_PHOTO`). Python keeps the top-N faces by confidence and drops the rest from the callback payload. Operators may raise the limit but must accept the corresponding body size increase.

**Spec Impact:** Add `VISION_FACE_MAX_FACES_PER_PHOTO` env var (default `10`) and `max_faces_per_photo: int = 10` to `AppSettings`. Update `FaceResult` / `DetectCallbackPayload` comments to note the cap.

**Resolved:** 2026-03-18

---

### ~~Q-030-40: Bulk Scan Scope — `IS NULL` Only or Include `failed`?~~ ✅ RESOLVED

**Resolution:** **Option A** — bulk scan targets `IS NULL` only. A separate **Maintenance page action** ("Re-scan failed photos") handles `face_scan_status = 'failed'` recovery, keeping bulk scan fast and predictable.

**Spec Impact:** FR-030-09 stays as IS NULL. Add CLI-030-03 `php artisan lychee:rescan-failed-faces` and a corresponding admin Maintenance page action.

**Resolved:** 2026-03-18

---

### ~~Q-030-41: Album Scan Depth — Recursive Through Sub-Albums?~~ ✅ RESOLVED

**Resolution:** **Option C** — user-selectable scope. Bulk scan UI offers two options: (1) **Library scan** — all unscanned photos across the entire library; (2) **Album scan** — all unscanned photos directly in the selected album (non-recursive). Sub-album scans are triggered explicitly. Matches existing CLI-030-01 / CLI-030-02 pattern.

**Spec Impact:** Update FR-030-09 to describe both scope options. Update API-030-12 notes to clarify non-recursive album scope.

**Resolved:** 2026-03-18

---

### ~~Q-030-42: Face Reassignment Authorization Across Users~~ ✅ RESOLVED

**Resolution:** **Option C** — mode-governed. In `public` and `private` modes, any user who passes the "Assign face" permission check (NFR-030-07 matrix) may reassign any face. In `privacy-preserving` and `restricted` modes, only the photo owner or admin may reassign. No `assigned_by_user_id` field needed.

**Spec Impact:** Add a clarifying note to the permission matrix that the "Assign face" row governs cross-user reassignment as well. Add comment to FR-030-04/FR-030-10.

**Resolved:** 2026-03-18

---

### ~~Q-030-43: Admin Bulk Hard-Delete of Dismissed Faces Missing from API Catalogue~~ ✅ RESOLVED

**Resolution:** **Option A** — add `DELETE /api/v2/Face/dismissed` as **API-030-16**. Admin-only; hard-deletes all `is_dismissed = true` Face records and their crop files.

**Spec Impact:** Add API-030-16 to API catalogue table and DSL routes.

**Resolved:** 2026-03-18

---

### ~~Q-030-44: Selfie Upload Has No Rate Limiting~~ ✅ RESOLVED

**Resolution:** Rate limiting applied at the **Lychee PHP layer** via Laravel's built-in throttle middleware on API-030-13 (`POST /api/v2/Person/claim-by-selfie`). No changes to the Python service needed.

**Spec Impact:** Add `throttle:5,1` (5 requests/minute per user) to the API-030-13 route definition note. Document in deployment guide.

**Resolved:** 2026-03-18

---

### ~~Q-030-46: `FaceResource` (DO-030-04) Field Specification Missing~~ ✅ RESOLVED

**Resolution:** **Option A** — suggestions are embedded in FaceResource. Fields exposed: `id` (Face ID), `photo_id`, `person_id` (nullable), `x`/`y`/`width`/`height` (float 0.0–1.0 bounding box), `confidence`, `is_dismissed`, `crop_url` (computed nginx-direct path from crop_token). Embedded `suggestions[]` array — each item: `suggested_face_id`, `crop_url` (suggested face's own crop or null), `person_name` (nullable, LEFT JOIN on persons), `confidence`. Suggestions are always included (pre-computed, stored in `face_suggestions`) — no N+1 risk.

**Spec Impact:** Expanded DO-030-04 in narrative domain objects table.

**Resolved:** 2026-03-18

---

### ~~Q-030-47: Missing Telemetry Events for Face Dismiss/Undismiss and Bulk Delete~~ ✅ RESOLVED

**Resolution:** **Option A** — three new events added: `TE-030-10` → `face.dismissed` (`face_id`, `photo_id`), `TE-030-11` → `face.undismissed` (`face_id`, `photo_id`), `TE-030-12` → `face.bulk_deleted` (`deleted_count`).

**Spec Impact:** Added TE-030-10, TE-030-11, TE-030-12 to telemetry events table and DSL.

**Resolved:** 2026-03-18

---

### ~~Q-030-48: No CLI/UI Path for Photos Stuck in `pending` Indefinitely~~ ✅ RESOLVED

**Resolution:** **Options B + C** combined — (B) `CLI-030-03` extended with optional `--stuck-pending [--older-than=N]` flag to reset pending records older than N minutes (default 60) back to `null`. (C) Admin Maintenance page action via **`GET /api/v2/Maintenance::resetStuckFaces`** (check: count of stuck records) + **`POST /api/v2/Maintenance::resetStuckFaces`** (do: reset them). Follows the existing check/do Maintenance route pattern. Endpoint added as API-030-17 / API-030-17b.

**Spec Impact:** Extended CLI-030-03 description. Added API-030-17 and API-030-17b to API catalogue and DSL routes.

**Resolved:** 2026-03-18

---

### ~~Q-030-49: Cluster Storage Model — Resolved~~ ✅ RESOLVED — How Should the Backend Know About Existing Clusters?

**Context:** FR-030-15 and API-030-18/19/20 specify a Cluster Review page. The current spec says `cluster_id` is "derived from the suggestion graph" (connected components of `face_suggestions`). This approach has three fatal flaws: (1) O(V+E) graph traversal per `GET /clusters` request violates NFR-030-02; (2) SHA1-of-sorted-face-IDs IDs are unstable — they change when any face in the cluster is dismissed or assigned, breaking `POST .../clusters/{id}/assign`; (3) pagination over connected components requires materialising all clusters first.

**Option A (Recommended) — `cluster_label` nullable INT column on `faces`**
- DBSCAN already produces integer labels (0, 1, 2... for clusters; -1 = noise). Persist them directly.
- `POST /cluster-results` payload carries `{face_id, cluster_label}[]` alongside suggestion pairs; PHP bulk-updates `faces.cluster_label`.
- `GET /clusters` = standard `GROUP BY cluster_label` SQL with `LIMIT/OFFSET`; composite index on `(cluster_label, person_id, is_dismissed)`.
- `cluster_id` in the API = `cluster_label` integer (stable between clustering runs).
- Assign/dismiss = `WHERE cluster_label = ?`.
- Stale for faces added after last clustering run (they have `cluster_label = NULL` and don't appear until re-cluster). Acceptable — Cluster Review is explicitly post-clustering UX.
- **One nullable column. No new model. Trivial pagination.**

**Option B — Separate `face_clusters` table + FK on `faces`**
- `face_clusters`: id (ULID), run_at, size (cached).
- `faces.cluster_id` FK → `face_clusters.id`.
- Pros: opaque ULID IDs, can record run timestamp. Cons: extra table + model, more complex ingestion, size cache goes stale on dismiss/assign. Not worth the complexity over A.

**Option C — Keep as-is (on-the-fly BFS/DFS over `face_suggestions`)**
- Always reflects current suggestion relationships (no staleness).
- Fatal: O(V+E) per page load, unstable IDs, pagination infeasible. Violates NFR-030-02. **Not viable at scale.**

**Required spec changes if Option A adopted:**
- `DO-030-02`: add `cluster_label` (nullable INT) to Face
- `DO-030-06` / migration: add `cluster_label INT NULL` column + composite index `(cluster_label, person_id, is_dismissed)` on `faces`
- `FR-030-13` (`POST /cluster-results`): body gains `{face_id: str, cluster_label: int | null}[]` alongside suggestion pairs
- `FR-030-15` / API-030-18/19/20: `cluster_id` = `cluster_label` integer; remove "opaque stable identifier derived from the suggestion graph" language; add note that noisy faces (`cluster_label = NULL`) excluded from Cluster Review

---

### ~~Q-030-50: `PersonResource.representative_crop_url` — Selection Rule Unspecified~~ ✅ RESOLVED

**Resolution:** **Options A + C** combined. Default logic uses highest-confidence non-dismissed face (`ORDER BY confidence DESC LIMIT 1`). A `representative_face_id` nullable FK→`faces` ON DELETE SET NULL is also added to the `persons` table (DO-030-08, T-030-53), allowing admins/users to override the representative via `PATCH /Person/{id}`. `PersonResource` uses the FK if set (and the referenced Face has a `crop_token`); otherwise falls back to the highest-confidence SELECT. Captured in DO-030-01, DO-030-03, DO-030-08, T-030-10 (note), T-030-18, T-030-53.

**Context:** DO-030-03 (`PersonResource`) lists `representative_crop_url` as a field. T-030-18 mentions it. The spec has no rule for *which* Face crop is chosen as representative. `PersonCard.vue` uses it as the person's avatar on the People page.

**Impact:** If implementors pick different strategies independently, the result will differ from what product design expects. Affects I6 (FaceResource), I13 (People page thumbnails).

**Option A (Recommended) — highest-confidence face crop**
- `SELECT crop_token FROM faces WHERE person_id = ? AND is_dismissed = false AND crop_token IS NOT NULL ORDER BY confidence DESC LIMIT 1`
- Deterministic, stable once detection quality is good, no additional sort column needed.

**Option B — most-recently added face crop**
- `ORDER BY created_at DESC LIMIT 1`
- Reflects the latest photo that person appeared in — may be more "current" but less relevant.

**Option C — null until user explicitly sets a representative face**
- Add a `representative_face_id` nullable FK on `persons` table, set via a new PATCH sub-action.
- Fully explicit but requires extra migration and UI affordance.

**Affects:** DO-030-03, T-030-18, PersonResource, PersonCard.vue.

---

### ~~Q-030-51: `ai_vision_enabled` / `ai_vision_face_enabled` Gating Hierarchy~~ ✅ RESOLVED

**Resolution:** **Option A** — compound gate. All `ai_vision_face_*` functionality is implicitly gated on `ai_vision_enabled = 1`. Any code path that gates on `ai_vision_face_enabled` must first confirm `ai_vision_enabled = 1`. If `ai_vision_enabled = 0`, all AI Vision endpoints return 503 / UI hides all AI Vision elements, regardless of `ai_vision_face_enabled`. Captured in NFR-030-10 and config table note for `ai_vision_face_enabled`.

**Context:** T-030-12 adds two separate config flags: `ai_vision_enabled` (global feature kill-switch for the whole AI Vision system) and `ai_vision_face_enabled` (specifically enables face detection). T-030-29 fires auto-scan when `ai_vision_face_enabled = 1`, but no task or spec explicitly states that `ai_vision_face_enabled` must ALSO check `ai_vision_enabled` first. An implementor could check only one flag or check both.

**Impact:** If `ai_vision_enabled = 0` but `ai_vision_face_enabled = 1`, undefined behaviour. Affects I8, I9, I10, I17 (every place that gates on the config).

**Option A (Recommended) — `ai_vision_face_enabled` implies `ai_vision_enabled`; guard with both**
- Any code path that checks `ai_vision_face_enabled` must first confirm `ai_vision_enabled`. Documented as a compound gate in NFR or as a middleware.
- Spec adds: "All `ai_vision_face_*` functionality is implicitly gated on `ai_vision_enabled = 1`."

**Option B — single flag; remove `ai_vision_enabled`**
- Since only face detection exists now, `ai_vision_face_enabled` is the only effective toggle. `ai_vision_enabled` is removed or deferred to when a second AI Vision feature ships.
- Simpler, but loses the global kill-switch if other AI features follow.

**Option C — independent flags; document the combination table**
- `ai_vision_enabled` controls API availability (503 when off). `ai_vision_face_enabled` controls auto-on-upload and People page visibility. Both can vary independently.

**Affects:** FR-030-08, NFR-030-03, T-030-12, T-030-29, T-030-38, FaceDetectionController, FaceDetectionService.ts.

---

### ~~Q-030-52: Embedding Deletion Dispatch Hook — Observer vs. Photo Pipeline~~ ✅ RESOLVED

**Resolution:** **Option B** — no Face model observer. Two explicit call-sites: (1) `destroyDismissed` action — collect dismissed face IDs before `Face::where('is_dismissed', true)->delete()`, dispatch `DeleteFaceEmbeddingsJob`; (2) `PhotoObserver::deleting` — collect `$photo->faces()->pluck('id')` before cascade, dispatch batch job. Captured in FR-030-14 and T-030-49.

**Context:** T-030-49 (FR-030-14) specifies dispatching `DeleteFaceEmbeddingsJob` when Face records are hard-deleted. The task says "Face model observer `deleting` event, **or** by hooking into the Photo delete pipeline." These are architecturally different:

- **Observer (`deleting`)**: fires per-row; requires N individual event firings for a batch delete; works for both cascade-from-Photo and admin bulk-delete paths uniformly.
- **Photo pipeline hook**: collects all `face_ids` before the cascade delete, dispatches one batch job; avoids N observer firings but only covers Photo→Face cascade. The admin `destroyDismissed` path still needs its own dispatch.

**Impact:** The observer approach fires for every delete path automatically but causes N jobs for a batch Photo cascade. The pipeline approach is more efficient but duplicates dispatch logic. Affects I21 (PHP `DeleteFaceEmbeddingsJob`), T-030-49.

**Option A (Recommended) — Observer on `deleting`, but coalesce with batch dispatch**
- Register a `Face` model observer. On `deleting`, collect IDs into a static `$pendingDeletion` buffer. A `deleted` static hook (or `booted` teardown) dispatches one job for the full batch at the end of the request lifecycle. Handles all delete paths without duplicating logic.

**Option B — No observer; explicit dispatch at each call-site**
- `destroyDismissed`: collects IDs before delete, dispatches one job explicitly.
- Photo delete: `PhotoObserver::deleting` collects `$photo->faces()->pluck('id')` before cascade, dispatches job.
- Simpler per-path but requires remembering to add dispatch at every future Face-delete call-site.

**Affects:** FR-030-14, T-030-49, I21, Face model observer, PhotoObserver, `destroyDismissed` action.

---

### ~~Q-030-54: Dismiss Face — Button Placement and CTRL+Click Shortcut~~ ✅ RESOLVED

**Resolution:** **Option A** — Add a "Dismiss" button in the FaceAssignmentModal. Additionally, when the user holds CTRL, face overlay rectangles switch to red dashed borders; clicking a rectangle in this state directly dismisses the face without opening the modal. Captured in FR-030-16, UI-030-08, S-030-33/34.

**Context:** The spec provides `PATCH /Face/{id}` to toggle `is_dismissed`, but the UI only allows toggling via API. Users need a convenient visual way to dismiss false-positive faces during browsing.

**Impact:** Affects I15 (FaceOverlay.vue), I16 (FaceAssignmentModal.vue), new UI interactions.

**Option A (Recommended) — Dismiss button in modal + CTRL+click overlay shortcut**
- FaceAssignmentModal gets a "Dismiss" button alongside "Assign".
- FaceOverlay.vue listens for CTRL key state; when held, overlays turn red/dashed; click triggers dismiss API.
- Clear visual feedback on CTRL state change.

**Option B — Dismiss only via modal button**
- Simpler, but requires two clicks (open modal + click dismiss) for every false positive.

**Resolved:** 2026-04-04

---

### ~~Q-030-55: Maintenance Block — Destroy Dismissed Faces + Reset Stuck/Failed Scans~~ ✅ RESOLVED

**Resolution:** **Option A** — Add a maintenance block for destroying all dismissed faces (calls `DELETE /Face/dismissed`). The block should only appear when there are dismissed faces to destroy (conditional rendering via the check endpoint). Additionally, add maintenance blocks to reset photos with face scan status "stuck" (pending too long) and "failed" so they can be re-scanned. Captured in API-030-21, API-030-22, API-030-23.

**Context:** The `DELETE /Face/dismissed` endpoint exists (API-030-16) but has no maintenance UI block. Users need a convenient way to clean up dismissed faces. Similarly, photos stuck in "pending" or "failed" need to be resettable from the UI.

**Impact:** Affects Maintenance.vue, new maintenance controller endpoints.

**Option A (Recommended) — Three conditional maintenance blocks: dismiss cleanup + reset stuck + reset failed**
- `MaintenanceDestroyDismissedFaces.vue`: check returns count of dismissed faces; do calls `DELETE /Face/dismissed`; hidden when count is 0.
- `MaintenanceResetStuckFaces.vue`: (already exists) check returns count of stuck-pending; do resets them.
- `MaintenanceResetFailedFaces.vue`: check returns count of failed scans; do resets `face_scan_status` to null.

**Option B — Single combined maintenance block**
- One block with multiple actions. Less granular but simpler UI.

**Resolved:** 2026-04-04

---

### ~~Q-030-56: Uncluster Faces from a Cluster — Batch Selection + Uncluster Action~~ ✅ RESOLVED

**Resolution:** **Option A** with batch selection — In the Cluster Review UI, users can select individual faces within a cluster (checkbox/multi-select), then choose to "uncluster" them. Unclustering sets `cluster_label = NULL` on the selected faces, removing them from the cluster without dismissing them. This allows fine-grained curation of clusters before bulk-assigning. Captured in FR-030-17, API-030-24, S-030-35.

**Context:** DBSCAN may group unrelated faces in the same cluster. Users need to remove incorrect faces from a cluster before bulk-assigning the rest to a Person.

**Impact:** Affects I22 (FaceClusters.vue), new API endpoint.

**Option A (Recommended) — Select faces in cluster, then uncluster selected**
- Multi-select UI in cluster card (checkbox on each face crop).
- "Uncluster selected" button sets `cluster_label = NULL` on selected face IDs.
- New API: `POST /FaceDetection/clusters/{cluster_id}/uncluster` with body `{face_ids: []}`.

**Option B — Drag faces out of cluster**
- Drag-and-drop UX. More intuitive but harder to implement and inaccessible.

**Resolved:** 2026-04-04

---

### ~~Q-030-57: Remove Face from Person — Face Becomes Unassigned~~ ✅ RESOLVED

**Resolution:** **Option A** — Users can remove a face from a person, which sets `face.person_id = NULL`. The face becomes unassigned (not dismissed). This is distinct from dismissing: dismiss marks the face as a false positive; unassign returns it to the pool of unassigned faces. Captured in FR-030-18, API-030-25, S-030-36.

**Context:** After assigning faces to persons, users may discover incorrect assignments. They need to unlink a face from a person without dismissing it entirely.

**Impact:** Affects PersonDetail.vue, FaceOverlay.vue, new/updated API endpoint.

**Option A (Recommended) — Set `person_id = NULL` via existing assign endpoint**
- `POST /Face/{id}/assign` with `person_id: null` (or a dedicated `unassign` action).
- The face returns to the unassigned pool and may appear in future cluster runs.

**Resolved:** 2026-04-04

---

### ~~Q-030-58: Batch Face Operations — Select Multiple Faces, Unassign/Assign/Create Person~~ ✅ RESOLVED

**Resolution:** **Option A** — For a person or cluster view, users can select a set of faces (multi-select with checkboxes), then choose from: (a) unassign all selected (set `person_id = NULL`), (b) assign all selected to another existing person, (c) assign all selected to a new person. This applies in both Person Detail and Cluster Review contexts. Captured in FR-030-19, API-030-26, S-030-37.

**Context:** One-by-one face operations are tedious for large datasets. Batch operations dramatically improve UX for face curation.

**Impact:** Affects PersonDetail.vue, FaceClusters.vue, batch API endpoint.

**Option A (Recommended) — Batch action bar with select mode**
- Toggle "select mode" in person/cluster views.
- Checkbox overlay on each face crop.
- Action bar appears: "Unassign (N)", "Reassign to...", "Assign to new person".
- `POST /Face/batch` with `{face_ids: [], action: "unassign"|"assign", person_id?: string, new_person_name?: string}`.

**Resolved:** 2026-04-04

---

### ~~Q-030-59: Person Miniature in Face Assignment Dropdown~~ ✅ RESOLVED

**Resolution:** **Option A** — When listing persons in the face assignment modal dropdown, each entry shows a small circular face crop miniature (the representative crop) next to the person name. This helps differentiate people with the same name. Captured in FR-030-20, UI-030-09.

**Context:** Multiple persons can share the same name (e.g., two people named "John"). Without a visual differentiator, users cannot distinguish them in the dropdown.

**Impact:** Affects I16 (FaceAssignmentModal.vue), PersonResource (already includes `representative_crop_url`).

**Option A (Recommended) — Circular miniature + name in dropdown**
- PrimeVue Dropdown with custom `option` template slot.
- Each option: 24px circular `<img>` (representative_crop_url) + person name + face count.
- Fallback placeholder icon when no representative crop exists.

**Resolved:** 2026-04-04

---

### ~~Q-030-60: Face Circles in Photo Detail Panel~~ ✅ RESOLVED

**Resolution:** **Option A** — When the photo details panel (sidebar) is open and the photo has detected faces, display them as circular face crop thumbnails with the person name underneath. Clicking a face circle opens the FaceAssignmentModal. CTRL+clicking a face circle dismisses it (same pattern as CTRL+click on overlay). Captured in FR-030-21, UI-030-10, S-030-38/39.

**Context:** Face overlays on the main photo image may be hard to interact with (small faces, precise clicking). The detail panel provides a more accessible interface for face management.

**Impact:** Affects PhotoDetails.vue, new sub-component, FaceAssignmentModal integration.

**Option A (Recommended) — Circular crops in detail panel with click/CTRL+click**
- New section "People in this photo" in PhotoDetails.vue.
- Row of circular face crops (48px diameter) with name label below.
- Click → open FaceAssignmentModal for that face.
- CTRL+click → dismiss face directly.
- "Unknown" label for unassigned faces.

**Resolved:** 2026-04-04

---

### ~~Q-030-61: Face Overlay Global Config Settings~~ ✅ RESOLVED

**Resolution:** **Option A** with modification — Two **global** config settings (both in `configs` table, not per-user): (1) `ai_vision_face_overlay_enabled` (0|1, default 1): master toggle that enables/disables the face overlay feature entirely. When 0, no face overlays are rendered anywhere. (2) `ai_vision_face_overlay_default_visibility` (enum: `visible`|`hidden`, default `visible`): sets whether face overlays are shown or hidden by default when viewing a photo. Users can toggle visibility with the `P` key. Per-user configuration deferred to a future enhancement. Captured in NFR-030-11, config table entries.

**Context:** Some users may find face overlays distracting. A global toggle and default visibility setting provide admin control over the face overlay UX.

**Impact:** Affects config migration, FaceOverlay.vue, PhotoDetails.vue, keybinding.

**Resolved:** 2026-04-04

---

### ~~Q-030-62: Album People Endpoint~~ ✅ RESOLVED

**Resolution:** **Option A** with modification — New endpoint `GET /api/v2/Album/{id}/people` returns the list of people found in a given album. The response uses the same `PaginatedPersonsResource` pattern as the People listing (consistent with `CollectionPhotoResource` style responses, not `ResourceName::collect()`). Photos are linked to albums via the `photo_albums` pivot table (not a direct `album_id` on photos). The query joins `photo_albums → photos → faces → persons` to collect distinct persons. Captured in FR-030-22, API-030-27, S-030-40.

**Context:** When browsing an album, users want to see which people appear in it. This enables a "People in this album" section in the album detail view.

**Impact:** New API endpoint, possible album detail UI enhancement.

**Option A (Recommended) — Distinct persons via photo_albums join**
- `SELECT DISTINCT persons.* FROM persons JOIN faces ON faces.person_id = persons.id JOIN photos ON faces.photo_id = photos.id JOIN photo_albums ON photo_albums.photo_id = photos.id WHERE photo_albums.album_id = ?`
- Returns `PaginatedPersonsResource`.
- Respects `ai_vision_face_permission_mode` visibility and `is_searchable` filtering.

**Resolved:** 2026-04-04

---

### ~~Q-030-63: Policy Refinement — Album/Photo Rights vs Face-Level Policy~~ ✅ RESOLVED

**Resolution:** ~~Deferred for now~~ **→ Fully resolved 2026-04-11 by I39.** `PhotoPolicy` gains four per-photo face gate constants (`CAN_VIEW_FACE_OVERLAYS`, `CAN_DISMISS_FACE`, `CAN_ASSIGN_FACE_ON_PHOTO`, `CAN_TRIGGER_SCAN_ON_PHOTO`) and `AlbumPolicy` gains four per-album face gate constants (`CAN_VIEW_ALBUM_PEOPLE`, `CAN_TRIGGER_SCAN_ON_ALBUM`, `CAN_ASSIGN_FACE_IN_ALBUM`, `CAN_BATCH_FACE_OPS`), each evaluating ownership against the concrete photo/album model. `PhotoRightsResource` and `AlbumRightsResource` surface the resulting booleans to the frontend. All face-related `Request` authorizers are updated to use these per-resource gates (FR-030-43 through FR-030-47).

**Context:** The `AiVisionPolicy` currently checks `ai_vision_face_permission_mode` globally but does not cross-check whether the user has edit rights on the specific album or photo. For example, in `privacy-preserving` mode, "photo/album owner + admin" should mean the owner of the specific album/photo, but the current policy may not check actual album ownership.

**Impact:** Resolved — see NFR-030-07 policy refinement note, FR-030-43 through FR-030-47, DO-030-12, DO-030-13, S-030-61 through S-030-65, I39.

**Resolved:** 2026-04-04 (initially deferred); **re-resolved 2026-04-11 (I39 implements full fix)**

---

### ~~Q-030-65: Face Overlay Toggle Key Binding Conflict — Does P Already Have a Mapping?~~ ✅ RESOLVED

**Resolution:** **Option A** — Use `P`. Confirmed that `P` has no existing binding. `F` is mapped to fullscreen (`togglableStore.toggleFullScreen()` in `Album.vue`). `P` is free and is used for toggling face overlay visibility. Captured in NFR-030-11, FR-030-21, I24.

**Context:** FR-030-21 specifies mapping the `P` key to toggle face overlay visibility. The existing Lychee photo viewer may already use `P` for another action (e.g., play slideshow, or some other shortcut). If there is a conflict, we need to choose a different key.

**Impact:** If `P` conflicts with an existing binding, the implementation would override existing behaviour. Affects I23 (keybinding setup), FaceOverlay.vue.

**Option A (Recommended) — Use `P` if available; otherwise use `F` or another unbound key**
- Check existing key bindings in the photo viewer. If `P` is free, use it. If not, fall back to `F` (for "Faces").

**Option B — Always use `F` for "Faces" regardless**
- Avoids any conflict risk, but `P` is more intuitive for "People".

**Affects:** FR-030-21, FaceOverlay.vue, keybinding system.

**Resolved:** 2026-04-04

---

### ~~Q-030-66: Album People Endpoint — Recursive vs Direct Photos Only~~ ✅ RESOLVED

**Resolution:** **Option A** — Direct photos only (non-recursive). Consistent with existing bulk scan behaviour (Q-030-41). Sub-album people can be viewed by navigating to each sub-album. Captured in FR-030-22, API-030-25 (renamed from API-030-27).

**Context:** FR-030-22 adds `GET /Album/{id}/people`. Should it include people from sub-album photos (recursive) or only direct photos in the album (joined via `photo_albums` where `album_id = ?`)?

**Impact:** Recursive requires either a CTE or pre-computing the album tree. Direct is simpler and consistent with how bulk scan works (non-recursive per Q-030-41). Affects API-030-25.

**Option A (Recommended) — Direct photos only (non-recursive)**
- Consistent with bulk scan behaviour. Sub-album people can be viewed by navigating to each sub-album.
- Query is simpler and faster.

**Option B — Recursive through sub-albums**
- More comprehensive but potentially expensive for deep album trees. May require album path pre-computation.

**Affects:** FR-030-22, API-030-25, AlbumPeopleController.

**Resolved:** 2026-04-04

---

### ~~Q-030-67: Batch Face Selection UX — Checkbox Overlay or Selection Mode Toggle?~~ ✅ RESOLVED

**Resolution:** **Option A** — Selection mode toggle. A "Select" button toggles selection mode; checkboxes appear on face crops only when active. Action bar slides in at the bottom. Captured in FR-030-19, UI-030-12.

**Context:** FR-030-19 specifies batch face selection in person/cluster views. Should selection be always-on (checkboxes always visible) or require entering a "select mode" first (like file managers)?

**Impact:** Affects the visual density and usability of the face grid in PersonDetail.vue and FaceClusters.vue.

**Option A (Recommended) — Selection mode toggle**
- A "Select" button toggles selection mode. When active, checkbox overlays appear on each face crop. Action bar slides in at the bottom.
- Cleaner default view; explicit mode transition.

**Option B — Always-visible checkboxes**
- No mode switch needed; faster for power users. But clutters the UI.

**Affects:** FR-030-19, PersonDetail.vue, FaceClusters.vue.

**Resolved:** 2026-04-04

---

### ~~Q-030-68: Person Merge UI — Location and Target Selection~~ ✅ RESOLVED

**Resolution:** **Option A** — "Merge into..." button on PersonDetail page, opens modal with person search dropdown. Captured in FR-030-25, UI-030-13, MergePersonModal.vue.

**Context:** FR-030-11 allows merging two Person records. The backend supports `POST /Person/{id}/merge` with `source_person_id` in body. Where should the merge UI live? How should the user select the target person?

**Impact:** Affects PersonDetail.vue, possible new MergePersonModal.

**Option A (Recommended) — "Merge into..." button on PersonDetail page, opens modal with person search dropdown**
- PersonDetail page has a "Merge" button. Clicking opens a modal with a person search dropdown (same component as assignment modal). User selects target person; confirms merge.

**Option B — Drag-and-drop between person cards on People page**
- More visual but hard to discover and inaccessible.

**Affects:** PersonDetail.vue, new MergePersonModal.vue.

**Resolved:** 2026-04-04

---

### ~~Q-030-69: Person Miniature Size and Layout for Same-Name Persons~~ ✅ RESOLVED

**Resolution:** **Option A** — Compact layout: 24px circle + name + face count, with type-ahead filter already built into PrimeVue Select/Dropdown. Captured in FR-030-20, UI-030-09.

**Context:** FR-030-20 adds circular miniatures in the face assignment dropdown. If there are many persons with the same name, the dropdown may become long and hard to navigate.

**Impact:** Minor UX concern. Affects FaceAssignmentModal.vue dropdown template.

**Option A (Recommended) — Compact layout: 24px circle + name + face count, with type-ahead filter**
- The PrimeVue Dropdown already supports filtering. The miniature helps differentiate same-name entries visually. No special layout needed beyond the custom option template.

**Option B — Group same-name persons with sub-labels (e.g., "John (142 photos)" vs "John (3 photos)")**
- Adds face count as disambiguator alongside the miniature.

**Affects:** FR-030-20, FaceAssignmentModal.vue.

**Resolved:** 2026-04-04

---

### ~~Q-030-70: CTRL+Click Dismiss on Touch Devices~~ ✅ RESOLVED

**Resolution:** **Option B** — No touch shortcut. Dismiss only via the modal button. On touch devices (detected via `isTouchDevice()` from `keybindings-utils.ts`), the CTRL+click behaviour is not implemented. Touch users open the modal and click the "Dismiss" button. Captured in FR-030-16 (updated), UI-030-08 (desktop-only note).

**Context:** FR-030-16 uses CTRL+click as a shortcut for face dismissal on overlays and in the detail panel. Touch devices (tablets, phones) don't have a CTRL key.

**Impact:** Touch users would have no shortcut for face dismissal and must use the modal button instead.

**Option A — Long-press on touch devices triggers dismiss**
- Long-press (500ms+) on a face overlay or face circle opens a context menu with "Dismiss" option.
- Alternatively, long-press directly dismisses (with undo toast).

**Option B (Chosen) — No touch shortcut; dismiss only via modal**
- Simplest approach. Touch users open the modal and click the dismiss button.

**Affects:** FR-030-16, FaceOverlay.vue, PhotoDetails.vue face circles.

**Resolved:** 2026-04-04

---

### ~~Q-030-71: Face Circles in Photo Detail Panel — Layout When Panel Is Narrow~~ ✅ RESOLVED

**Resolution:** **Option A** — Horizontal scrollable row with overflow indicator. Flex row with `overflow-x: auto`. When faces exceed visible width, a "+N more" badge is shown; the row is scrollable to reveal all faces. Captured in FR-030-21, UI-030-10.

**Context:** FR-030-21 adds circular face crops to the PhotoDetails sidebar. The sidebar is fixed at `w-95` (380px). If a photo has many faces (10+), the circles may overflow.

**Impact:** Layout overflow or truncation for photos with many detected faces.

**Option A (Recommended) — Horizontal scrollable row with overflow indicator**
- Flex row with `overflow-x: auto`. Shows "+N more" indicator when faces overflow.
- Clicking "+N more" expands to a grid view.

**Option B — Wrapping grid layout**
- Faces wrap to multiple rows. May push other detail sections down significantly.

**Affects:** FR-030-21, PhotoDetails.vue face section.

**Resolved:** 2026-04-04

---

### ~~Q-030-72: Policy Refinement — Album/Photo Edit Rights~~ ✅ RESOLVED

**Resolution:** ~~Option B — Defer~~ **→ Fully resolved 2026-04-11 by I39** (same resolution as Q-030-63). `PhotoPolicy` and `AlbumPolicy` now provide per-resource face gate methods that check the concrete photo/album owner. All face-related request authorizers are wired to these new gates. See FR-030-43 through FR-030-47 and I39 in `plan.md`.

**Context:** The current `AiVisionPolicy` checks the global `ai_vision_face_permission_mode` but does not cross-reference the user's actual edit rights on the specific album or photo. In `privacy-preserving` and `restricted` modes, "photo/album owner" should mean the owner of that specific resource, but the current implementation may check ownership globally.

**Impact:** High — could allow users to assign/dismiss faces on photos they don't own. Affects all face operations gated on "photo/album owner + admin".

**Option A (Chosen) — Add album/photo ownership checks to policy methods**
- `PhotoPolicy` gains `CAN_VIEW_FACE_OVERLAYS`, `CAN_DISMISS_FACE`, `CAN_ASSIGN_FACE_ON_PHOTO`, `CAN_TRIGGER_SCAN_ON_PHOTO` methods taking `(?User, Photo)`.
- `AlbumPolicy` gains `CAN_VIEW_ALBUM_PEOPLE`, `CAN_TRIGGER_SCAN_ON_ALBUM`, `CAN_ASSIGN_FACE_IN_ALBUM`, `CAN_BATCH_FACE_OPS` methods taking `(?User, AbstractAlbum|null)`.
- All face request authorizers updated to use these per-resource gates.

**Affects:** AiVisionPolicy, PhotoPolicy, AlbumPolicy, PhotoRightsResource, AlbumRightsResource, all face-related request classes.

**Resolved:** 2026-04-04 (initially deferred); **re-resolved 2026-04-11 (I39 implements full fix)**

---

### ~~Q-030-73: Reset Face Scan Status Maintenance Blocks — Separate or Combined?~~ ✅ RESOLVED

**Resolution:** **Option A with grouping** — Group stuck-pending and failed resets into a **single** combined maintenance block, distinct from the "Destroy Dismissed Faces" block. The final UI has exactly two face maintenance action blocks: (1) "Destroy Dismissed Faces" and (2) "Reset Face Scan Status" (handles both stuck-pending and failed). The existing `Maintenance::resetStuckFaces` backend endpoint remains available for CLI use but no longer has a dedicated UI card. Captured in FR-030-24 (updated), API-030-22/22b (renamed to `resetFaceScanStatus`), UI-030-15.

**Context:** Q-030-55 resolution requires maintenance blocks for: (a) destroying dismissed faces, (b) resetting stuck-pending scans, (c) resetting failed scans. Should these be three separate maintenance cards or combined into fewer?

**Impact:** Affects Maintenance.vue layout and number of maintenance controllers.

**Option A with grouping (Chosen) — Two conditional blocks: dismiss cleanup + combined reset stuck/failed**
- Block 1: `MaintenanceDestroyDismissedFaces.vue` — destroys dismissed faces (count > 0 to show).
- Block 2: `MaintenanceResetFaceScanStatus.vue` — combined reset of stuck-pending (>720 min) AND failed scans.
  - check: `count_stuck + count_failed`; hidden when 0
  - do: resets both `PENDING` (older than 720 min) and `FAILED` photos to `null`

**Option B — Three separate conditional blocks**
- Each block independently checks its count and hides when zero. Clear, granular control.

**Affects:** FR-030-24, Maintenance.vue, API-030-22/22b, new `ResetFaceScanStatus.php` controller.

**Resolved:** 2026-04-04

---

### ~~Q-030-74: PersonDetail Lightbox Navigation Strategy~~ ✅ RESOLVED

**Feature:** 030 – AI Vision Service  
**Priority:** High  
**Status:** Resolved  
**Opened:** 2026-04-07  
**Affects:** T-030-85 (I35), FR-030-39

**Resolution:** **Option A (server-side)** — `GET /Person/{id}/photos` computes and includes `next_photo_id` and `previous_photo_id` on each `PhotoResource`, ordered sequentially by collection position (access-filtered). First photo: `previous_photo_id = null`; last photo: `next_photo_id = null`. `PhotoPanel.vue` then uses these person-relative IDs natively for navigation within the person's collection. No client-side monkey-patching required.

**Spec Impact:** Updated FR-030-03 success path to note that each `PhotoResource` includes `next_photo_id`/`previous_photo_id` relative to the person's collection. Updated FR-030-39 success path. Updated S-030-55 scenario. Updated plan.md I12 steps and exit criteria. Updated T-030-32 (test for next/previous IDs), T-030-33 (implementation), T-030-85 (lightbox navigation note).

**Resolved:** 2026-04-07

---

### ~~Q-030-75: FaceCluster Detail View — Dialog vs. Sub-Route~~ ✅ RESOLVED

**Feature:** 030 – AI Vision Service  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-07  
**Affects:** T-030-78 (I32), FR-030-29

**Resolution:** **Option A** — PrimeVue `<Dialog>`. Clicking a cluster card opens a Dialog that fetches all faces via `GET /FaceDetection/clusters/{cluster_id}/faces`. URL does not change. No routing changes needed.

**Spec Impact:** Updated FR-030-29 requirement and success path to specify PrimeVue Dialog. Updated plan.md I32 step 3. Updated T-030-78 intent (Dialog only, sub-route option removed).

**Resolved:** 2026-04-07

---

### ~~Q-030-76: People.vue Context Menu "Assign to User" Action~~ ✅ RESOLVED

**Feature:** 030 – AI Vision Service  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-07  
**Affects:** T-030-79 (I33), FR-030-32, FR-030-05

**Resolution:** **Option A** — User-picker dialog. "Assign to user" (admin-only) opens a PrimeVue `<Dialog>` with an autocomplete Dropdown listing user accounts (name + email). On confirm, calls `PATCH /Person/{id}` with `{ user_id: selectedUserId }`. Requires extending `UpdatePersonRequest` to accept nullable `user_id` with an admin-only validation gate.

**Spec Impact:** Updated FR-030-32 success path to describe the user-picker dialog and `PATCH /Person/{id}` with `user_id`. Updated plan.md I33 step 1. Updated T-030-79 intent (user-picker dialog, UpdatePersonRequest extension noted).

**Resolved:** 2026-04-07

---

### ~~Q-030-77: Admin/Feature Short-Circuit Mechanism in PhotoPolicy and AlbumPolicy Face Gates~~ ✅ RESOLVED

**Feature:** 030 – AI Vision Service
**Priority:** High
**Status:** Resolved
**Opened:** 2026-04-11
**Affects:** T-030-100 (PhotoPolicy), T-030-101 (AlbumPolicy), FR-030-43, FR-030-44

**Resolution:** FR-030-43/44 wording about `AiVisionPolicy::before()` is incorrect and is removed. The new `PhotoPolicy` and `AlbumPolicy` face gate methods rely on those policies' own `before()` hooks for the admin short-circuit. Side-effect: admins will bypass the gate even when AI Vision is disabled — **accepted risk**. No inline duplication or shared trait needed.

**Resolved:** 2026-04-11

---

### ~~Q-030-78: "Album Access" Verification in Public/Private Mode for Face Gates~~ ✅ RESOLVED

**Feature:** 030 – AI Vision Service
**Priority:** High
**Status:** Resolved
**Opened:** 2026-04-11
**Affects:** T-030-100 (PhotoPolicy), T-030-101 (AlbumPolicy), FR-030-43, FR-030-44

**Resolution:** Non-issue. There is no circular dependency — `AlbumPolicy::canViewAlbumPeople()` can call `$this->canAccess($user, $album)` directly on the same policy instance without going through `Gate::check()`. No proxy or workaround needed.

**Resolved:** 2026-04-11

---

### ~~Q-030-79: BatchFaceRequest Album Context and ScanPhotosRequest Photo-Path Authorization~~ ✅ RESOLVED

**Feature:** 030 – AI Vision Service
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-04-11
**Affects:** T-030-103, FR-030-47

**Resolution:** Option A for both cases — check ownership of the concrete **photo** when no album is available. `BatchFaceRequest`: when `album_id` is null (no album context), check `Gate::check(PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO, $face->photo)` for each resolved face. `ScanPhotosRequest` photo-only path: check `Gate::check(PhotoPolicy::CAN_TRIGGER_SCAN_ON_PHOTO, $photo)` for each photo. FR-030-47 (c) and (d) updated accordingly.

**Resolved:** 2026-04-11
