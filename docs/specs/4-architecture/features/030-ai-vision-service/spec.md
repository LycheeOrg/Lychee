# Feature 030 – AI Vision Service

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-04-04 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/030-ai-vision-service/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/030-ai-vision-service/tasks.md` |
| Roadmap entry | #030 |

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
| FR-030-01 | System shall store Person records with a name, optional User link (1-1), and a searchability flag. | Person created with name; optionally linked to a User; `is_searchable` defaults to the value of `ai_vision_face_person_is_searchable_default` config (default `true`). | Name must be non-empty string ≤255 chars; user_id must reference existing User and be unique across Person table (1-1). | Return 422 with validation errors. | `person.created`, `person.updated` | Owner directive |
| FR-030-02 | System shall store Face records linking a detected face region in a Photo to an optional Person, including a server-side crop thumbnail path and a dismissal flag. The Python service shall filter detections by both confidence and sharpness before sending results to Lychee. *(Resolved Q-030-09: server-side crop; Q-030-16: is_dismissed; Q-030-25: crop storage path; Q-030-34: nginx-direct hash path)* | Face created with photo_id, bounding box (x, y, width, height as percentages), confidence score, `crop_token` (random high-entropy token, stored on Face model); crop file stored at `uploads/faces/{token[0:2]}/{token[2:4]}/{token}.jpg` and served directly by nginx (path is unguessable, no app-level auth required); person_id nullable (unassigned); `is_dismissed` defaults to `false`. Python service filters detections by two thresholds before callback: (a) `VISION_FACE_DETECTION_THRESHOLD` (confidence; default 0.5) — faces below excluded; (b) `VISION_FACE_BLUR_THRESHOLD` (Laplacian variance sharpness score; default `100.0`) — faces whose crop has a Laplacian variance below this value are excluded as too blurry to be usable for recognition. | photo_id must exist; bounding box values 0.0–1.0; confidence 0.0–1.0. | Return 422; reject invalid photo_id with 404. | `face.created` | Owner directive, Q-030-09, Q-030-16, Q-030-25 |
| FR-030-03 | A Person can appear in multiple Photos (many-to-many through Face). The system shall provide an endpoint to list all photos containing a given Person. | GET endpoint returns paginated photos where at least one Face with matching person_id exists. | person_id must exist; pagination params validated. | 404 if Person not found; empty result set if no faces assigned. | — | Owner directive |
| FR-030-04 | A Photo can contain multiple Persons. The system shall return all identified Persons when viewing a Photo's details. For non-searchable Persons, face overlays are hidden entirely for unauthorized viewers; a `hidden_face_count` integer is included instead. *(Resolved Q-030-10: hide overlay + count indicator)* | Photo detail response includes `faces` array (only searchable/authorized faces) + `hidden_face_count` (integer, count of suppressed non-searchable faces). | — | Graceful empty array if no faces detected; hidden_face_count = 0 if none suppressed. | — | Owner directive, Q-030-10 |
| FR-030-05 | Users can link their account to a Person (1-1) via direct claim, and unlink via unclaim. Admins can link/unlink any Person-User pair, overriding user claims. Only one Person per User, one User per Person. *(Resolved Q-030-06: self-identification + admin override; Q-030-21: unclaim endpoint)* | User claims a Person; `person.user_id` set; old claim (if any) cleared. Admin can force-link/unlink any pair. User unclaims via `DELETE /api/v2/Person/{id}/claim`; sets `person.user_id = null`. | user_id unique on persons table; User must exist. Non-admin claim: 409 if Person already claimed by another User; 403 if `ai_vision_face_allow_user_claim` is `false`. Admin claim: overrides existing link; bypasses `ai_vision_face_allow_user_claim`. Unclaim: only linked User or admin. | 409 if Person already claimed (non-admin); 403 if `ai_vision_face_allow_user_claim` is `false` (non-admin); 403 if unclaim caller is not linked User or admin; 422 for validation errors. | `person.claimed`, `person.unclaimed` | Owner directive, Q-030-06, Q-030-21 |
| FR-030-06 | A Person's linked User (or admin) can toggle `is_searchable` on the Person. When `is_searchable` is false, the Person is excluded from search results and People browsing for non-admin users who are not the linked User. *(Resolved Q-030-05: hidden from search + People page for unauthorized users)* | Toggle flips boolean; subsequent search/browse queries filter accordingly. | Only linked User or admin can toggle. | 403 if unauthorized; 404 if Person not found. | `person.searchability_changed` | Owner directive, Q-030-05 |
| FR-030-07 | System shall expose API endpoints for the external Python service to submit face detection results (success or error) via REST webhook callback. *(Resolved Q-030-01: REST + webhooks; Q-030-14: re-scan IoU preservation + force flag; Q-030-15: single shared API key, separation of concerns; Q-030-17: error callback; Q-030-23: state machine)* | Success POST: accepts photo_id + faces array; creates/updates Face records (IoU matching preserves `person_id` from old faces); returns `embedding_id → lychee_face_id` mapping in 200 response. Error POST: accepts photo_id + error details; sets `face_scan_status = failed`. Re-scan with assigned faces requires `force: true`. `face_scan_status` set: `pending` on dispatch, `completed` on success callback, `failed` on error callback. | photo_id must exist; face data array validated per FR-030-02; endpoint authenticated **exclusively** via `X-API-Key` header (no user/admin session); `force` boolean optional (default false, required when photo has assigned faces on re-scan). | 404 for invalid photo; 422 for malformed face data; 401 for invalid API key; 409 if photo has assigned faces and `force` is false. | `face.batch_created` | Owner directive, Q-030-01, Q-030-14, Q-030-15, Q-030-17, Q-030-23 |
| FR-030-08 | System shall expose an API endpoint to request face detection for a photo or album. Lychee sends REST request to Python service with photo filesystem path (shared Docker volume) and callback URL. Authorization governed by `ai_vision_face_permission_mode` setting (4-value enum). *(Resolved Q-030-01, Q-030-02, Q-030-07: REST + webhooks + shared volume; Q-030-08: configurable permissions; Q-030-19: ai_vision_* naming; Q-030-20: four-mode permission enum)* | POST triggers scan request via HTTP to Python service; sets `face_scan_status = pending` on dispatch; returns 202 Accepted. Also auto-triggered on photo upload when `ai_vision_face_enabled` is true. | photo_id or album_id must exist; user must have Trigger scan permission per `ai_vision_face_permission_mode` (see NFR-030-07 matrix). | 404/403 for invalid/unauthorized targets; 503 if Python service unavailable. | `face.scan_requested` | Owner directive, Q-030-01, Q-030-02, Q-030-07, Q-030-08, Q-030-19, Q-030-20 |
| FR-030-09 | Admin can trigger bulk face detection scan for all unscanned photos. *(Resolved Q-030-02: multiple triggers including bulk; Q-030-19: ai_vision_* naming)* | Admin action enqueues all photos where `face_scan_status` IS NULL; progress trackable via `face_scan_status` column. | Admin-only access. | 503 if service unavailable; partial failure logged per photo (status set to `failed`). | `face.scan_requested` (`target_type: "bulk"`) | Owner directive, Q-030-02, Q-030-19 |
| FR-030-10 | Users can manually assign/reassign an unassigned Face to a Person, or create a new Person from a Face. Python service provides cluster suggestions for grouping similar faces. *(Resolved Q-030-03: auto-cluster with manual confirmation)* | Face's person_id updated; new Person created if requested. Cluster suggestions displayed as similarity scores in assignment UI. | Face must exist; target Person (if specified) must exist. | 404/422 for invalid references. | `face.assigned` | Owner directive, Q-030-03 |
| FR-030-11 | Users can merge two Person records (combining all their Face associations). *(Resolved Q-030-22: merge direction — URL {id} = target kept; body source_person_id = source destroyed)* | All Faces of source Person reassigned to target Person (`{id}`); source Person deleted. | Both Persons must exist; user must have edit permission per `ai_vision_face_permission_mode`; body must supply `source_person_id`. | 404 if either Person not found; 403 if unauthorized. | `person.merged` | Owner directive, Q-030-22 |
| FR-030-12 | Users can upload a selfie photo to claim a Person via face matching. Selfie sent to Python service's dedicated `POST /match` endpoint; image discarded immediately after matching. *(Resolved Q-030-06: selfie-upload claim; Q-030-11: discard after match; Q-030-12: dedicated /match endpoint; Q-030-13: lychee_face_id returned by /match)* | User uploads selfie → Python service `POST /match` returns top-N matches with `lychee_face_id` + confidence → if best match above `ai_vision_face_selfie_confidence_threshold`, Lychee resolves `lychee_face_id → Face → person_id` and links Person to User (same 1-1 rules as FR-030-05). Selfie image deleted after response. | Selfie must contain exactly one detectable face; confidence threshold configurable. | 422 if no face detected in selfie; 404 if no matching Person found; 409 if matched Person already claimed by another User. | `person.selfie_claim_requested`, `person.selfie_claim_matched` | Owner directive, Q-030-06, Q-030-11, Q-030-12, Q-030-13 |
| FR-030-14 | When Face records are hard-deleted from Lychee (either via admin bulk-dismissed-delete or via cascade when a Photo is deleted), the corresponding embeddings shall also be removed from the Python service's embedding store to prevent stale data from polluting future clustering and suggestion results. | After hard-deleting Face records, Lychee calls Python `DELETE /embeddings` with `{face_ids: [str]}`. Python removes those embeddings from `EmbeddingStore`. The call is best-effort (fire-and-forget via queued job): Lychee proceeds with the deletion regardless of whether the Python service responds. Failures are logged as warnings. Dispatch is triggered at **two explicit call-sites** (no Face model observer): (1) `destroyDismissed` action — collect dismissed face IDs before `Face::where('is_dismissed', true)->delete()`, then dispatch `DeleteFaceEmbeddingsJob`; (2) `PhotoObserver::deleting` — collect `$photo->faces()->pluck('id')` before cascade delete, then dispatch batch job. *(Q-030-52)* | face_ids must be a non-empty list of strings. | If the Python service is unavailable, log warning and continue — do not fail or roll back the Lychee-side deletion. | `face.embeddings_deleted` (with `count`) | Owner directive, Q-030-52 |
| FR-030-15 | System shall provide a Cluster Review page where authorized users can browse face clusters produced by DBSCAN (faces grouped by visual similarity, not yet assigned to a Person) and bulk-name or dismiss an entire cluster in one action. | `GET /api/v2/FaceDetection/clusters` returns a paginated list of clusters; each cluster contains a `cluster_id` (integer, equal to `faces.cluster_label`, stable between clustering runs), a list of `FaceResource` items (crop_url, confidence, photo_id), and a `size` count. The page renders face-crop grids grouped by cluster. User can: (a) type a name and click “Create Person & assign all” — creates a new Person and bulk-assigns every face in the cluster to it via `POST /api/v2/FaceDetection/clusters/{cluster_id}/assign`; (b) click “Dismiss cluster” — marks every face `is_dismissed = true` via `POST /api/v2/FaceDetection/clusters/{cluster_id}/dismiss`. | Cluster review page respects `ai_vision_face_permission_mode` (same visibility rules as People page). `cluster_id` must be a valid integer `cluster_label` value with at least one qualifying face. | 404 if `cluster_id` not found; 403 if unauthorized. | `face.cluster_assigned` (with `cluster_id`, `person_id`, `face_count`), `face.cluster_dismissed` (with `cluster_id`, `face_count`) | Owner directive, Q-030-49 |
| FR-030-13 | Admin can trigger offline DBSCAN face clustering across all stored embeddings to generate cross-photo face suggestion pairs and persist cluster labels on Face records. The Python service exposes `POST /cluster`; Lychee exposes `POST /FaceDetection/cluster-results` to ingest the results. *(Q-030-49)* | Admin calls `POST /api/v2/Maintenance::runFaceClustering` (admin-only) → Lychee calls Python service `POST /cluster` (X-API-Key auth) → Python reads all stored embeddings from `EmbeddingStore`, runs DBSCAN (eps from `VISION_FACE_CLUSTER_EPS` env var, default configurable), generates: (a) `{face_id: str, cluster_label: int}[]` where label = DBSCAN integer label (noise faces omitted); (b) `(face_id, suggested_face_id, confidence)` pairs for every intra-cluster pair using cosine similarity → POSTs both to `POST /api/v2/FaceDetection/cluster-results` (X-API-Key auth) → Lychee: bulk-updates `faces.cluster_label` (all previously clustered faces first reset to NULL, then new labels written); bulk-upserts `face_suggestions` rows (unique on `(face_id, suggested_face_id)`); returns `{faces_labeled: N, suggestions_updated: M}`. Maintenance trigger returns 202 Accepted; clustering runs asynchronously on the Python side. | Python endpoint: X-API-Key required. PHP cluster-results endpoint: X-API-Key required; body: `{labels: [{face_id: str, cluster_label: int}], suggestions: [{face_id: str, suggested_face_id: str, confidence: float}]}`. Both arrays optional (empty array = no-op for that field). Maintenance trigger: admin-only session auth. | 503 if Python service unavailable; 401 for invalid API key; no-op if no embeddings stored (returns `{faces_labeled: 0, suggestions_updated: 0}`). | `face.clustering_triggered`, `face.cluster_labels_written` (with `faces_labeled`), `face.cluster_suggestions_ingested` (with `suggestions_updated`) | Owner directive, Q-030-49 |
| FR-030-16 | System shall support dismissing a face via: (a) a "Dismiss" button in the FaceAssignmentModal; (b) a CTRL+click shortcut on face overlays (desktop only) — when the CTRL key is held, overlay rectangles switch to red dashed borders, and clicking a rectangle directly dismisses the face. On touch devices, CTRL+click is not available; dismiss is only accessible via the modal button. *(Q-030-54, Q-030-70)* | (a) FaceAssignmentModal includes a "Dismiss" button that calls `PATCH /Face/{id}` to set `is_dismissed = true`. (b) FaceOverlay.vue monitors `keydown`/`keyup` for CTRL key on non-touch devices (checked via `isTouchDevice()` from `keybindings-utils.ts`); when CTRL is held, all face rectangles render with red dashed CSS borders; clicking a rectangle in this state calls `PATCH /Face/{id}` directly without opening the modal. After dismiss, the overlay is removed from view. | Modal dismiss: face must exist, user must have dismiss permission per `ai_vision_face_permission_mode`. CTRL+click: same authorization as modal dismiss; only available on non-touch devices. | 403 if unauthorized; 404 if face not found. | `face.dismissed` | Owner directive, Q-030-54, Q-030-70 |
| FR-030-17 | In the Cluster Review UI, users can select individual faces within a cluster and "uncluster" them — setting `cluster_label = NULL` on selected faces, removing them from the cluster without dismissing them. Supports batch selection. *(Q-030-56)* | New API: `POST /api/v2/FaceDetection/clusters/{cluster_id}/uncluster` with body `{face_ids: [str]}`. Sets `faces.cluster_label = NULL` for the specified face IDs (only if they belong to the given cluster_id and are qualifying — `person_id IS NULL AND is_dismissed = false`). Returns `{unclustered_count: int}`. | face_ids must be non-empty; all face_ids must belong to the specified cluster_id. | 404 if cluster_id not found; 422 if face_ids empty or invalid; 403 if unauthorized. | `face.unclustered` (with `cluster_id`, `face_count`) | Owner directive, Q-030-56 |
| FR-030-18 | Users can remove a face from a person (unassign), making it unassigned again (not dismissed). Distinct from dismissal — unassign returns the face to the unassigned pool. *(Q-030-57)* | `POST /Face/{id}/assign` accepts `person_id: null` to unassign the face. Sets `face.person_id = NULL`. The face becomes available for future cluster runs and manual assignment. | Face must exist; user must have assign permission. | 404 if face not found; 403 if unauthorized. | `face.unassigned` (with `face_id`, `previous_person_id`) | Owner directive, Q-030-57 |
| FR-030-19 | In person detail and cluster review views, users can select multiple faces (batch selection mode), then choose: (a) unassign all selected (`person_id = NULL`), (b) assign all to an existing person, (c) assign all to a new person. *(Q-030-58)* | New API: `POST /api/v2/Face/batch` with body `{face_ids: [str], action: "unassign"\|"assign", person_id?: str, new_person_name?: str}`. For "unassign": sets `person_id = NULL` on all specified faces. For "assign": sets `person_id` to the given person (or creates a new Person from `new_person_name`). Returns `{affected_count: int, person_id?: str}`. | face_ids must be non-empty; action must be "unassign" or "assign"; for "assign", either `person_id` or `new_person_name` required. | 422 if validation fails; 403 if unauthorized for any face; 404 if person_id not found. | `face.batch_updated` (with `action`, `face_count`, `person_id`) | Owner directive, Q-030-58 |
| FR-030-20 | When listing persons in the face assignment modal dropdown, each entry shall display a small circular face crop miniature (the representative crop from `PersonResource.representative_crop_url`) next to the person name, to disambiguate people with the same name. *(Q-030-59)* | FaceAssignmentModal dropdown uses PrimeVue Dropdown with custom `option` template: 24px circular `<img>` (representative_crop_url) + person name + face count. Placeholder icon when no representative crop exists. | PersonResource already includes `representative_crop_url`. | — | — | Owner directive, Q-030-59 |
| FR-030-21 | When the photo details panel (sidebar) is open and the photo has detected faces, display circular face crop thumbnails with person name underneath. Click opens FaceAssignmentModal; CTRL+click dismisses the face (desktop only). Map the `P` key to toggle face overlay visibility. *(Q-030-60, Q-030-61, Q-030-65, Q-030-70, Q-030-71)* | New "People in this photo" section in PhotoDetails.vue. Horizontal scrollable flex row of circular face crops (48px diameter) with name label below each. Overflow: `overflow-x: auto`; when faces exceed visible width they are reachable by scrolling. Click → FaceAssignmentModal. CTRL+click (desktop only) → dismiss (same API as FR-030-16). `P` key confirmed free — `F` maps to fullscreen — toggles overlay visibility (stored in component state; default from `ai_vision_face_overlay_default_visibility` config). | Photo must have faces; face overlay feature must be enabled (`ai_vision_face_overlay_enabled = 1`). | — | — | Owner directive, Q-030-60, Q-030-61, Q-030-65, Q-030-70, Q-030-71 |
| FR-030-22 | New endpoint `GET /api/v2/Album/{id}/people` returns the list of persons found in the given album (via `photo_albums` pivot join). Response uses `PaginatedPersonsResource`. *(Q-030-62)* | Query: `SELECT DISTINCT persons.* FROM persons JOIN faces ON faces.person_id = persons.id JOIN photos ON faces.photo_id = photos.id JOIN photo_albums ON photo_albums.photo_id = photos.id WHERE photo_albums.album_id = ? AND faces.is_dismissed = false`. Respects `ai_vision_face_permission_mode` and `is_searchable` filtering. Paginated. | Album must exist; user must have access to the album. | 404 if album not found; 403 if unauthorized. | — | Owner directive, Q-030-62 |
| FR-030-23 | Maintenance page shall include a block for destroying all dismissed faces. The block only appears when dismissed faces exist (count > 0). *(Q-030-55)* | `GET /Maintenance::destroyDismissedFaces` (check): returns count of dismissed faces. `POST /Maintenance::destroyDismissedFaces` (do): calls existing `DELETE /Face/dismissed` logic. Maintenance card hidden when count is 0. | Admin-only. | — | `face.bulk_deleted` | Owner directive, Q-030-55 |
| FR-030-24 | Maintenance page shall include a single combined block for resetting photos with stuck-pending (`face_scan_status = 'pending'` older than 720 minutes) OR failed (`face_scan_status = 'failed'`) scan status, so they can be re-scanned. The block only appears when the combined count is > 0. *(Q-030-55, Q-030-73)* | `GET /Maintenance::resetFaceScanStatus` (check): returns combined count of stuck-pending (>720 min) + failed photos. `POST /Maintenance::resetFaceScanStatus` (do): sets `face_scan_status = NULL` on all stuck-pending (>720 min) and all failed photos in a single update. Block hidden when combined count is 0. The existing `Maintenance::resetStuckFaces` endpoint remains available for CLI use but is superseded in the UI by this combined block. | Admin-only. | — | `face.failed_scans_reset` (with `reset_count`) | Owner directive, Q-030-55, Q-030-73 |
| FR-030-25 | Person merge shall be accessible from the UI via a "Merge into..." button on the PersonDetail page. A modal allows searching/selecting the target person. *(Q-030-58)* | PersonDetail page includes a "Merge" action button. Clicking opens `MergePersonModal.vue` with a person search dropdown (same PrimeVue Dropdown + custom template with miniature as FR-030-20). User selects target, confirms → calls `POST /Person/{id}/merge`. Source person's page redirects to target person after merge. | Both persons must exist; user must have merge permission. | 403 if unauthorized; 404 if either person not found. | `person.merged` | Owner directive, Q-030-58 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-030-01 | Face detection must not block photo upload. Processing is asynchronous. | UX: upload speed must not degrade. | Upload response time unchanged (< 2s for typical photo); face detection runs in background. | Queue system (Laravel jobs) or external service callback. | Owner directive |
| NFR-030-02 | The Person/Face data model must support libraries with 100k+ photos and 1k+ persons without query degradation. | Scalability. | People listing < 500ms; photo-faces lookup < 200ms with proper indexes. | Database indexes on `faces.photo_id`, `faces.person_id`, `persons.user_id`. | Owner directive |
| NFR-030-03 | Communication with the Python service must be resilient to service unavailability. Lychee must function normally when the face-recognition container is down. | Reliability; optional feature. | All non-face-recognition features unaffected; face endpoints return 503 gracefully. | Health check / circuit breaker pattern. | Owner directive |
| NFR-030-04 | Privacy: non-searchable Person data must never leak through search endpoints, People browsing, or photo detail responses (for unauthorized viewers). | GDPR / user privacy. | Unit + feature tests verify filtering. | Query scopes on Person model. | Owner directive |
| NFR-030-05 | The API contract between Lychee and the Python service must be versioned and documented so both can evolve independently. | Maintainability; separate codebases. | OpenAPI/JSON schema for the inter-service contract. | — | Owner directive |
| NFR-030-06 | Face bounding box coordinates stored as relative values (0.0–1.0 percentages) to remain resolution-independent. | Display correctness across size variants. | Bounding boxes render correctly on thumb, medium, and original size variants. | Frontend rendering logic. | Owner directive |
| NFR-030-07 | Authorization governed by configurable `ai_vision_face_permission_mode` setting (enum: `public`, `private`, `privacy-preserving`, `restricted`). Default: `restricted`. All four modes must be covered by feature tests. *(Resolved Q-030-08; Q-030-19: ai_vision_* naming; Q-030-20: four-mode permission matrix)* | Flexibility for public, private, and multi-user deployments. | Feature tests pass in all four modes; admin settings UI toggles mode. | Config migration, conditional authorization middleware. | Owner directive, Q-030-08, Q-030-19, Q-030-20 |

**Permission matrix per mode:**

| Operation          | public       | private      | privacy-preserving        | restricted                |
|--------------------|--------------|--------------|---------------------------|---------------------------|
| View People page   | guest        | logged users | photo/album owner + admin | admin only                |
| View face overlays | album access | logged users | photo/album owner + admin | photo/album owner + admin |
| Create/edit Person | logged users | logged users | photo/album owner + admin | admin only                |
| Assign face        | logged users | logged users | photo/album owner + admin | admin only                |
| Trigger scan       | logged users | logged users | photo/album owner + admin | photo/album owner + admin |
| Claim person       | logged users | logged users | logged users              | logged users              |
| Merge persons      | logged users | logged users | photo/album owner + admin | admin only                |
| Dismiss face       | photo owner + admin | photo owner + admin | photo owner + admin | photo owner + admin |
| Batch face ops     | logged users | logged users | photo/album owner + admin | admin only                |
| View album people  | album access | logged users | photo/album owner + admin | photo/album owner + admin |

| NFR-030-08 | Python service accesses photos via shared Docker volume (filesystem path). Deployment must document volume mount configuration. *(Resolved Q-030-07)* | Performance; no auth complexity for file access. | Python service reads photos from shared path; integration test confirms file access. | Docker volume configuration in docker-compose. | Owner directive, Q-030-07 |
| NFR-030-09 | AI Vision (facial recognition and People management) is a **Supporter Edition (SE)** feature. All AI Vision config keys are stored with `level = 1`; the admin settings UI hides them on non-SE instances. Face detection and People management endpoints return 403 on non-SE instances. | Licensing; feature differentiation. | Non-SE instance: settings page does not expose the AI Vision category; scan/people endpoints return 403. | License-level check middleware (existing SE gate); config `level` column. | Owner directive |
| NFR-030-10 | All `ai_vision_face_*` functionality is implicitly gated on `ai_vision_enabled = 1`. Any code path that checks `ai_vision_face_enabled` must first confirm `ai_vision_enabled = 1`. When `ai_vision_enabled = 0`, all AI Vision endpoints (face detection, People management, cluster review, selfie claim) return 503 and all AI Vision UI elements are hidden, regardless of the value of `ai_vision_face_enabled`. *(Q-030-51)* | Correctness; global kill-switch. | Feature tests confirm: `ai_vision_enabled=0` + `ai_vision_face_enabled=1` → all face endpoints inactive. | Compound gate check in FaceDetection and People controllers; `ai_vision_enabled` evaluated before `ai_vision_face_enabled` in every guard. | Owner directive, Q-030-51 |
| NFR-030-11 | Face overlay display is governed by two global config settings: `ai_vision_face_overlay_enabled` (0\|1, default 1) — master toggle for face overlay rendering; when 0, no face overlays or face circles are shown anywhere. `ai_vision_face_overlay_default_visibility` (string: `visible`\|`hidden`, default `visible`) — sets whether overlays are shown or hidden by default when a photo is viewed; user can toggle with `P` key (`P` confirmed unbound — `F` maps to fullscreen). Both settings are global (configs table), not per-user. *(Q-030-61, Q-030-65)* | UX flexibility; some users find overlays distracting. | Overlay disabled: feature test confirms no face DOM elements rendered. Toggle: pressing `P` flips overlay visibility. | Config migration, FaceOverlay.vue, PhotoDetails.vue. | Owner directive, Q-030-61, Q-030-65 |

> **Policy refinement note *(Q-030-63)*:** The current four-level permission mode semantic (public/private/privacy-preserving/restricted) is correct. However, the policy also needs refinement with regard to album/photo edit rights — currently, "photo/album owner + admin" checks global ownership rather than per-resource ownership. This gap is acknowledged and will be revisited in a future iteration. For now, the focus is on UI/UX interaction.

> **Cross-user reassignment *(Q-030-42)*:** The "Assign face" row governs reassignment of faces regardless of who previously assigned them. In `public`/`private` modes any user meeting the mode's write threshold may reassign; in `privacy-preserving`/`restricted` only the photo owner or admin may do so.

### Lychee Configuration — AI Vision Category

All keys below belong to the `AI Vision` config category (`cat = 'AI Vision'`) and use `level = 1` (Supporter Edition). They are grouped in the admin settings UI under an **AI Vision** section that is only visible on SE instances.

> **Infrastructure keys** (`ai_vision_face_url`, `ai_vision_face_api_key`) are **not** stored in the `configs` table. They are infrastructure / secret values bound to environment variables and read via `config/features.php`. This avoids exposing the service URL or the shared API key through the admin settings UI or any config API endpoint.
>
> Using a service-specific name (e.g. `face` rather than a generic `ai_vision`) allows future services (e.g. NudeNet content moderation) to add their own `ai_vision_nudenet_url` / `ai_vision_nudenet_api_key` pairs under the same pattern without collision.

#### `config/features.php` — AI Vision infrastructure keys

Read via `config('features.ai-vision.face-url')` and `config('features.ai-vision.face-api-key')`. Never visible in the admin UI or included in config API responses.

| PHP key | `.env` variable | Default | Description |
|---------|----------------|---------|-------------|
| `features.ai-vision.face-url` | `AI_VISION_FACE_URL` | `""` | Base URL of the Python face-recognition service (e.g. `http://ai-vision:8000`). Must not have a trailing slash. |
| `features.ai-vision.face-api-key` | `AI_VISION_FACE_API_KEY` | `""` | Shared API key for both directions: sent as `X-API-Key` in Lychee→Python scan requests; expected as `X-API-Key` in Python→Lychee callbacks. Must match `VISION_FACE_API_KEY` on the Python side. |

#### `configs` table — AI Vision admin-configurable keys

| Key | Type / Range | Default | Description |
|-----|-------------|---------|-------------|
| `ai_vision_enabled` | `0\|1` | `0` | Master AI Vision toggle. When `0`, all AI Vision endpoints and UI elements are inactive regardless of sub-feature toggles. |
| `ai_vision_face_enabled` | `0\|1` | `0` | Enable facial recognition specifically. Requires `ai_vision_enabled = 1`. When `0`, face detection endpoints, People pages, and auto-scan on upload are inactive. |
| `ai_vision_face_permission_mode` | enum string | `restricted` | Access control mode for all People/Face operations. Values: `public`, `private`, `privacy-preserving`, `restricted` (see NFR-030-07 permission matrix). |
| `ai_vision_face_selfie_confidence_threshold` | float (0.0–1.0) | `0.8` | Minimum match confidence score for selfie-based Person auto-claim (FR-030-12). |
| `ai_vision_face_person_is_searchable_default` | `0\|1` | `1` | Default `is_searchable` value assigned to each newly created Person record (FR-030-01). |
| `ai_vision_face_allow_user_claim` | `0\|1` | `1` | When `1`, regular (non-admin) users may claim an assigned Person to link it to their account. Admins can always claim/unclaim regardless of this setting (FR-030-05). |
| `ai_vision_face_scan_batch_size` | integer | `200` | Number of photo IDs dispatched per job chunk when bulk-scanning. Controls queue saturation — lower values reduce burst load at the cost of more job records. *(Q-030-45)* |
| `ai_vision_face_overlay_enabled` | `0\|1` | `1` | Master toggle for face overlay rendering. When `0`, no face overlays or face circles are shown anywhere in the UI. *(Q-030-61, NFR-030-11)* |
| `ai_vision_face_overlay_default_visibility` | enum string | `visible` | Default visibility state for face overlays when viewing a photo. Values: `visible`, `hidden`. User can toggle with `P` key during photo viewing. *(Q-030-61, NFR-030-11)* |

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
|  ○ Existing person:                     |
|    [ (○) Alice  142ph  ▼ ]             |
|    *(miniature + name + count)*         |
|  ○ New person:      [ __________ ]     |
|                                          |
|  Similar faces found:                   |
|  [Alice (94%)] [Bob (12%)]             |
|                                          |
|    [Dismiss]   [Cancel]  [Assign]       |
+------------------------------------------+
```

*Note: The dropdown for existing persons shows a circular miniature (representative_crop_url) next to the name and face count to disambiguate same-name persons (FR-030-20). The "Dismiss" button calls `PATCH /Face/{id}` to mark `is_dismissed = true` (FR-030-16).*

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

### Cluster Review Page

```
+------------------------------------------------------------------+
| Lychee > People > Clusters                         [Run Cluster] |
+------------------------------------------------------------------+
|  Showing 24 clusters of unassigned faces                         |
+------------------------------------------------------------------+
|                                                                  |
|  Cluster #1  (12 faces)                                         |
|  +------+ +------+ +------+ +------+ +------+  [+7 more]        |
|  |      | |      | |      | |      | |      |                   |
|  | crop | | crop | | crop | | crop | | crop |                   |
|  |      | |      | |      | |      | |      |                   |
|  +------+ +------+ +------+ +------+ +------+                   |
|  [ _______________ ]  [Create Person & Assign All]  [Dismiss]   |
|                                                                  |
|  Cluster #2  (7 faces)                                          |
|  +------+ +------+ +------+ +------+ +------+  [+2 more]        |
|  |      | |      | |      | |      | |      |                   |
|  | crop | | crop | | crop | | crop | | crop |                   |
|  |      | |      | |      | |      | |      |                   |
|  +------+ +------+ +------+ +------+ +------+                   |
|  [ _______________ ]  [Create Person & Assign All]  [Dismiss]   |
|                                                                  |
|  Cluster #3  (3 faces)     · · ·                                |
|                                                                  |
|  [Load more clusters]                                           |
+------------------------------------------------------------------+
```

*Note: "Run Cluster" button triggers `POST /api/v2/Maintenance::runFaceClustering` then refreshes the page. Clusters with faces already assigned to a Person are not shown.*

### Face Overlay — CTRL+Click Dismiss Mode *(FR-030-16)*

```
+------------------------------------------------------------------+
| ◄  Photo Title                                    ⋮ Menu          |
+------------------------------------------------------------------+
|                                                                    |
|     ┌──────────────────────────────────────────┐                  |
|     │                                          │                  |
|     │        ┌╌╌╌╌╌╌╌┐      ┌╌╌╌╌╌╌╌┐        │                  |
|     │        ╎ face1  ╎      ╎ face2  ╎        │                  |
|     │        ╎ RED    ╎      ╎ RED    ╎        │                  |
|     │        └╌╌╌╌╌╌╌┘      └╌╌╌╌╌╌╌┘        │                  |
|     │                                          │                  |
|     └──────────────────────────────────────────┘                  |
|                                                                    |
|  [CTRL held — click face to dismiss]                              |
+------------------------------------------------------------------+
```

*Note: When CTRL is held (desktop only — touch devices dismiss via modal button only), all face overlay rectangles switch to red dashed borders. Clicking directly dismisses the face without opening the modal. *(Q-030-70)***

### Photo Detail Panel — Face Circles *(FR-030-21)*

```
+------------------------------------------------------------------+
| Photo Details Sidebar                                             |
+------------------------------------------------------------------+
|  ┌────┐                                                           |
|  │    │  sunset_beach.jpg                                        |
|  │img │  4032×3024 · 8.2 MB                                      |
|  └────┘                                                           |
|  ...                                                              |
|                                                                    |
|  People in this photo:                                            |
|  ┌──┐  ┌──┐  ┌──┐                                               |
|  │○○│  │○○│  │○○│                                                |
|  │○○│  │○○│  │○○│                                                |
|  └──┘  └──┘  └──┘                                                |
|  Alice  Bob   ???                                                 |
|                                                                    |
|  [P] Toggle face overlay                                          |
|  ...                                                              |
+------------------------------------------------------------------+
```

*Note: Circular face crop thumbnails (48px) with person name underneath. Horizontal scrollable row (`overflow-x: auto`). Click opens FaceAssignmentModal. CTRL+click (desktop only) dismisses. "???" for unassigned faces. *(Q-030-70, Q-030-71)***

### Cluster Review — Batch Select & Uncluster *(FR-030-17, FR-030-19)*

```
+------------------------------------------------------------------+
| Lychee > People > Clusters                         [Run Cluster] |
+------------------------------------------------------------------+
|  Cluster #1  (12 faces)                          [Select Mode ✓] |
|  +------+ +------+ +------+ +------+ +------+  [+7 more]        |
|  |  ☑   | |  ☐   | |  ☑   | |  ☐   | |  ☐   |                  |
|  | crop | | crop | | crop | | crop | | crop |                   |
|  +------+ +------+ +------+ +------+ +------+                   |
|  ┌──────────────────────────────────────────────────────────┐    |
|  │ 2 selected: [Uncluster] [Reassign to...] [Unassign]    │    |
|  └──────────────────────────────────────────────────────────┘    |
|  [ _______________ ]  [Create Person & Assign All]  [Dismiss]   |
+------------------------------------------------------------------+
```

*Note: When "Select Mode" is active, checkbox overlays appear. Selected faces can be unclustered (removed from cluster), reassigned to another person, or unassigned.*

### Person Detail — Batch Face Operations *(FR-030-19)*

```
+------------------------------------------------------------------+
| Lychee > People > Alice                          [Select Mode ✓] |
+------------------------------------------------------------------+
|  ┌────┐  Alice · 142 photos                                      |
|  │crop│  [Edit] [Merge into...] [Delete]                         |
|  └────┘                                                           |
+------------------------------------------------------------------+
|  Faces assigned to Alice:                                         |
|  +------+ +------+ +------+ +------+ +------+                   |
|  |  ☑   | |  ☐   | |  ☑   | |  ☐   | |  ☐   |                  |
|  | crop | | crop | | crop | | crop | | crop |                   |
|  +------+ +------+ +------+ +------+ +------+                   |
|  ┌──────────────────────────────────────────────────────────┐    |
|  │ 2 selected: [Unassign] [Reassign to...] [New Person]   │    |
|  └──────────────────────────────────────────────────────────┘    |
+------------------------------------------------------------------+
```

*Note: From a person's detail page, users can select faces and unassign them (return to unassigned pool), reassign to another person, or create a new person from the selection.*

### Merge Person Modal *(FR-030-25)*

```
+------------------------------------------+
| Merge Person                             |
+------------------------------------------+
|                                          |
|  Merge "Bob" into:                       |
|                                          |
|  [ (○) Alice  142ph  ▼ ]               |
|  *(search dropdown with miniature)*      |
|                                          |
|  This will:                             |
|  • Move all 87 faces from Bob to Alice  |
|  • Delete Bob's person record           |
|  • This action cannot be undone         |
|                                          |
|          [Cancel]  [Merge]              |
+------------------------------------------+
```

### Maintenance — Face Blocks *(FR-030-23, FR-030-24)*

```
+------------------------------------------------------------------+
| Maintenance                                                       |
+------------------------------------------------------------------+
|  ... existing blocks ...                                          |
|                                                                    |
|  ┌─────────────────────┐  ┌─────────────────────┐                |
|  │ Destroy Dismissed   │  │ Reset Face Scan     │                |
|  │ Faces               │  │ Status              │                |
|  │ 23 dismissed faces  │  │ 5 stuck + 3 failed  │                |
|  │ [Destroy All]       │  │ [Reset All]         │                |
|  └─────────────────────┘  └─────────────────────┘                |
|                                                                    |
+------------------------------------------------------------------+
```

*Note: Each face maintenance block is conditional — hidden when its count is 0. The "Reset Face Scan Status" block combines stuck-pending (>720 min) and failed scans into one action. The existing `Maintenance::resetStuckFaces` API is retained for CLI use but has no dedicated UI card.*

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-030-01 | Face detected in photo → Face record created with bounding box, confidence, no person assigned |
| S-030-02 | User assigns face to existing Person → Face.person_id updated |
| S-030-03 | User creates new Person from unassigned face → Person created, Face linked |
| S-030-04 | User links Person to their User account → person.user_id set (1-1) |
| S-030-05 | User toggles Person as non-searchable → Person excluded from search/browse for others |
| S-030-06 | Admin triggers bulk scan → All unscanned photos queued for face detection |
| S-030-07 | User requests scan for single photo → Scan job dispatched to Python service |
| S-030-08 | User requests scan for album → All photos in album queued for scanning |
| S-030-09 | Python service unavailable → 503 returned; no impact on other Lychee features |
| S-030-10 | Photo detail view → Face overlays displayed with person names |
| S-030-11 | People page → Grid of all known persons with face crop thumbnails and photo counts |
| S-030-12 | Person detail page → Paginated photos containing that person |
| S-030-13 | Merge two persons → Faces reassigned, source person deleted |
| S-030-14 | Re-scan photo that was already scanned → Old face records replaced with new results |
| S-030-15 | Non-searchable person queried by unauthorized user → Person not returned in results |
| S-030-16 | User claims Person already claimed by another user → 409 Conflict |
| S-030-17 | Photo deleted → Associated Face records cascade-deleted |
| S-030-18 | Person deleted → Associated Face records have person_id set to null (faces remain, become unassigned) |
| S-030-19 | Admin force-links Person to User, overriding existing claim → Previous user's claim cleared, new link set |
| S-030-20 | User uploads selfie → Python service matches face → matching Person linked to User |
| S-030-21 | User uploads selfie with no detectable face → 422 error |
| S-030-22 | User uploads selfie but no matching Person found → 404, user informed |
| S-030-23 | Photo uploaded with `ai_vision_face_enabled` → auto-scan job dispatched |
| S-030-27 | Admin triggers face clustering → Python runs DBSCAN across all stored embeddings → suggestion pairs bulk-upserted into `face_suggestions` → FaceAssignmentModal shows updated suggestions |
| S-030-28 | Admin hard-deletes all dismissed faces → Lychee deletes Face records → Python `DELETE /embeddings` called with their IDs → embeddings removed from store |
| S-030-29 | Photo deleted → Face records cascade-deleted → Python `DELETE /embeddings` called → stale embeddings removed |
| S-030-30 | Face detected in photo with sharpness below `VISION_FACE_BLUR_THRESHOLD` → face excluded from detection callback; not stored in Lychee or embedding store |
| S-030-31 | Admin opens Cluster Review page → clusters of visually similar unassigned faces displayed → admin names a cluster → new Person created and all faces in cluster assigned |
| S-030-32 | Admin dismisses an entire cluster from Cluster Review page → all faces in cluster marked `is_dismissed = true` |
| S-030-33 | User clicks "Dismiss" button in FaceAssignmentModal → face is dismissed (`is_dismissed = true`), modal closes, overlay removed |
| S-030-34 | User holds CTRL key → face overlay rectangles turn red/dashed → clicking a rectangle directly dismisses the face without opening modal |
| S-030-35 | User selects faces in cluster, clicks "Uncluster" → selected faces have `cluster_label` set to NULL, removed from cluster view |
| S-030-36 | User removes a face from a person (unassign) → face's `person_id` set to NULL, face returns to unassigned pool |
| S-030-37 | User selects multiple faces in person detail, clicks "Reassign to..." → all selected faces assigned to the chosen person |
| S-030-38 | Photo detail panel open with detected faces → circular face crops shown with names under; clicking opens FaceAssignmentModal |
| S-030-39 | CTRL+click on face circle in detail panel → face is dismissed directly |
| S-030-40 | User opens album → requests `GET /Album/{id}/people` → list of persons appearing in album photos is returned |
| S-030-41 | User presses `P` while viewing photo → face overlays toggle between visible and hidden |
| S-030-42 | Admin opens maintenance page with dismissed faces → "Destroy Dismissed Faces" block shown; clicks "Destroy All" → all dismissed faces hard-deleted |
| S-030-43 | Admin opens maintenance page with failed face scans → "Reset Failed Scans" block shown; clicks "Reset All" → failed photos reset to null |
| S-030-44 | `ai_vision_face_overlay_enabled` set to `0` → no face overlays or face circles rendered anywhere in the UI |
| S-030-45 | Person merge from UI: user opens PersonDetail → clicks "Merge into..." → selects target person with miniature → confirms → source person merged into target |
| S-030-46 | Face assignment dropdown shows persons with circular miniature + name + face count; two persons named "John" visually distinguishable |

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
| DO-030-01 | `Person` — id, name, user_id (nullable, unique), is_searchable (boolean, default true), representative_face_id (nullable FK→faces ON DELETE SET NULL — explicit override for the representative crop; if NULL, PersonResource falls back to highest-confidence non-dismissed face), timestamps | Models, API, UI |
| DO-030-02 | `Face` — id, photo_id (FK→photos), person_id (nullable FK→persons), x, y, width, height (float 0.0–1.0), confidence (float 0.0–1.0), is_dismissed (boolean, default false), crop_token (random high-entropy token, nullable; file stored at `uploads/faces/{token[0:2]}/{token[2:4]}/{token}.jpg` and served directly by nginx — path is unguessable, no app-level auth required), cluster_label (nullable INT; DBSCAN cluster assignment written by `POST /FaceDetection/cluster-results`; -1 = DBSCAN noise/outlier stored as NULL; NULL = not yet clustered), timestamps | Models, API, UI |
| DO-030-03 | `PersonResource` — Spatie Data resource for Person API responses. Fields: `id`, `name`, `user_id` (nullable), `is_searchable` (boolean), `face_count` (int), `photo_count` (int), `representative_face_id` (nullable string — echoes `persons.representative_face_id`), `representative_crop_url` (nullable string — computed: if `representative_face_id` is set and the referenced Face has a `crop_token`, use that face's crop URL; otherwise `SELECT crop_token FROM faces WHERE person_id = ? AND is_dismissed = false AND crop_token IS NOT NULL ORDER BY confidence DESC LIMIT 1`; null if no qualifying face exists). PATCH /Person/{id} accepts optional `representative_face_id` (string\|null) to explicitly set or clear the override. *(Q-030-50)* | Resources |
| DO-030-04 | `FaceResource` — Spatie Data resource for Face API responses (included in PhotoResource). Fields exposed: `id` (Face ID), `photo_id`, `person_id` (nullable), `x`, `y`, `width`, `height` (float 0.0–1.0), `confidence` (float 0.0–1.0), `is_dismissed` (boolean), `crop_url` (computed: `uploads/faces/{token[0:2]}/{token[2:4]}/{token}.jpg`; null if no crop). Embedded `suggestions[]` array — each item: `suggested_face_id`, `crop_url` (suggested face's crop or null), `person_name` (nullable, resolved via LEFT JOIN on persons), `confidence` (float 0.0–1.0). Suggestions always embedded (not lazy-loaded) since they are pre-computed and stored — no N+1 risk. *(Q-030-46)* | Resources |
| DO-030-05 | `FaceSuggestion` — face_id (FK→faces), suggested_face_id (FK→faces), confidence (float 0.0–1.0); pre-computed similar-face suggestions stored from Python scan callback. Both FKs point to `faces`; the assignment modal JOINs at read time to resolve `suggested_face_id → person_id` (supports unassigned suggestions). Unique constraint on `(face_id, suggested_face_id)`. *(Q-030-33)* | Models, API, UI |
| DO-030-06 | `photos` table addendum — adds nullable `face_scan_status VARCHAR(16)` column; PHP `ScanStatus` Enum cast. Values: `null` (never scanned), `pending`, `completed`, `failed`. Type chosen for MySQL/PostgreSQL/SQLite portability. *(Q-030-38)* | Models, Migrations |
| DO-030-07 | `faces` table addendum — adds nullable `cluster_label INT` column (DBSCAN output label; NULL = not yet clustered or noise). Composite index `(cluster_label, person_id, is_dismissed)` on `faces` enables O(index-scan) `GROUP BY cluster_label` pagination for API-030-18. *(Q-030-49)* | Models, Migrations |
| DO-030-08 | `persons` table addendum — adds `representative_face_id` nullable FK→`faces` ON DELETE SET NULL. Separate migration required (cannot be included in the original `persons` migration because `faces` does not yet exist at that point — circular-FK dependency). *(Q-030-50)* | Models, Migrations |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-030-01 | GET /api/v2/People | List all persons (paginated), filtered by is_searchable for non-admin users; always appends a synthetic `{id: null, name: "Unknown", face_count: N}` entry (omitted when N = 0) where N = unassigned faces count *(Q-030-37)* | Returns PersonResource collection + synthetic Unknown entry |
| API-030-02 | GET /api/v2/Person/{id} | Get Person detail with face count and photo count | Returns PersonResource |
| API-030-03 | POST /api/v2/Person | Create a new Person | Body: name, user_id? |
| API-030-04 | PATCH /api/v2/Person/{id} | Update Person (name, is_searchable) | |
| API-030-05 | DELETE /api/v2/Person/{id} | Delete Person (nullifies face.person_id) | |
| API-030-06 | POST /api/v2/Person/{id}/merge | Merge source Person into target (`{id}` kept) | Body: `source_person_id` |
| API-030-07 | POST /api/v2/Person/{id}/claim | Link Person to current User (1-1) | |
| API-030-08 | GET /api/v2/Person/{id}/photos | Paginated photos containing Person | |
| API-030-09 | POST /api/v2/Face/{id}/assign | Assign a Face to a Person (or create new Person) | Body: person_id or new_person_name |
| API-030-10 | POST /api/v2/FaceDetection/scan | Request face detection for photo(s) or album | Body: photo_ids[] or album_id, optional `force` boolean; dispatched in chunks of `ai_vision_face_scan_batch_size` (default 200) *(Q-030-45)* |
| API-030-11 | POST /api/v2/FaceDetection/results | Receive face detection results (success or error) from Python service (service-to-service only; authenticated via `X-API-Key`, no user session) | Body: success payload or error payload |
| API-030-12 | POST /api/v2/FaceDetection/bulk-scan | Admin: enqueue all unscanned photos (face_scan_status IS NULL); scope: full library (no album_id) or direct photos in specified album (non-recursive) *(Q-030-41)* | Admin-only |
| API-030-13 | POST /api/v2/Person/claim-by-selfie | Upload selfie photo, Python service matches against embeddings, link matching Person to current User | Multipart form with image file; **throttle: 5 requests/minute per user** *(Q-030-44)* |
| API-030-14 | PATCH /api/v2/Face/{id} | Toggle `is_dismissed` flag (dismiss/undismiss a false-positive face) | Auth: photo owner or admin |
| API-030-15 | DELETE /api/v2/Person/{id}/claim | Remove the User link from a Person (unclaim) | Auth: linked User or admin |
| API-030-16 | DELETE /api/v2/Face/dismissed | Admin: hard-delete all dismissed faces (is_dismissed = true), their crop files, and their embeddings (FR-030-14: calls Python `DELETE /embeddings` asynchronously) | Admin-only *(Q-030-43)* |
| API-030-17 | GET /api/v2/Maintenance::resetStuckFaces | Admin: check — returns count of photos stuck in `pending` longer than `older_than_minutes` (default 60). Follows existing check/do Maintenance pattern. | Admin-only *(Q-030-48)* |
| API-030-17b | POST /api/v2/Maintenance::resetStuckFaces | Admin: do — reset all `face_scan_status = 'pending'` records older than threshold back to `null`. Body: optional `older_than_minutes` (integer, default 60). | Admin-only *(Q-030-48)* |
| API-030-18 | GET /api/v2/FaceDetection/clusters | List face clusters using `SELECT cluster_label, COUNT(*) as size FROM faces WHERE cluster_label IS NOT NULL AND person_id IS NULL AND is_dismissed = false GROUP BY cluster_label ORDER BY cluster_label LIMIT/OFFSET`. Paginated; each cluster: `{cluster_id: int, size: int, faces: FaceResource[]}` (faces loaded via separate WHERE cluster_label = ? query, limited to first N for preview). Composite index `(cluster_label, person_id, is_dismissed)` ensures O(index-scan). Respects `ai_vision_face_permission_mode` visibility rules. | Auth per permission mode |
| API-030-19 | POST /api/v2/FaceDetection/clusters/{cluster_id}/assign | Bulk-assign all qualifying faces (`cluster_label = cluster_id AND person_id IS NULL AND is_dismissed = false`) to a Person (existing `person_id` or new `new_person_name`). Creates Person if `new_person_name` provided. `cluster_id` is the integer `cluster_label` value. | Body: `person_id` or `new_person_name` |
| API-030-20 | POST /api/v2/FaceDetection/clusters/{cluster_id}/dismiss | Bulk-dismiss all qualifying faces (`cluster_label = cluster_id AND person_id IS NULL AND is_dismissed = false`) by setting `is_dismissed = true`. `cluster_id` is the integer `cluster_label` value. | Auth: photo owner or admin |
| API-030-21 | GET /api/v2/Maintenance::destroyDismissedFaces | Admin: check — returns count of dismissed faces (`is_dismissed = true`). Follows existing check/do Maintenance pattern. Hidden when count is 0. *(Q-030-55, FR-030-23)* | Admin-only |
| API-030-21b | POST /api/v2/Maintenance::destroyDismissedFaces | Admin: do — hard-delete all dismissed faces, their crop files, and their embeddings (same logic as API-030-16). Returns `{deleted_count: N}`. | Admin-only |
| API-030-22 | GET /api/v2/Maintenance::resetFaceScanStatus | Admin: check — returns combined count of stuck-pending photos (older than 720 min) + photos with `face_scan_status = 'failed'`. Hidden when count is 0. *(Q-030-55, Q-030-73, FR-030-24)* | Admin-only |
| API-030-22b | POST /api/v2/Maintenance::resetFaceScanStatus | Admin: do — sets `face_scan_status = NULL` on all stuck-pending (>720 min) AND all failed photos in one operation. Returns `{reset_count: N}`. Supersedes the UI role of `Maintenance::resetStuckFaces` (which remains for CLI use only). *(Q-030-73)* | Admin-only |
| API-030-23 | POST /api/v2/FaceDetection/clusters/{cluster_id}/uncluster | Remove selected faces from a cluster by setting `cluster_label = NULL`. Body: `{face_ids: [str]}`. Only affects qualifying faces in the given cluster (`cluster_label = cluster_id AND person_id IS NULL AND is_dismissed = false`). Returns `{unclustered_count: int}`. *(FR-030-17, Q-030-56)* | Auth per permission mode |
| API-030-24 | POST /api/v2/Face/batch | Batch face operations. Body: `{face_ids: [str], action: "unassign"\|"assign", person_id?: str, new_person_name?: str}`. "unassign" sets `person_id = NULL`; "assign" sets `person_id` (or creates new Person). Returns `{affected_count: int, person_id?: str}`. *(FR-030-19, Q-030-58)* | Auth per permission mode; user must have assign permission for all faces |
| API-030-25 | GET /api/v2/Album/{id}/people | List distinct persons appearing in an album's photos. Joins `photo_albums → photos → faces → persons`. Returns `PaginatedPersonsResource`. Respects `ai_vision_face_permission_mode` and `is_searchable` filtering. *(FR-030-22, Q-030-62)* | Auth: album access required |

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-030-01 | `php artisan lychee:scan-faces` | Enqueue all unscanned photos for face detection (admin batch operation) |
| CLI-030-02 | `php artisan lychee:scan-faces --album={id}` | Enqueue photos directly in a specific album for face detection (non-recursive) |
| CLI-030-03 | `php artisan lychee:rescan-failed-faces [--stuck-pending] [--older-than=N]` | Maintenance: re-enqueue all photos where `face_scan_status = 'failed'` *(Q-030-40)*. With `--stuck-pending`: additionally reset `face_scan_status = 'pending'` records older than N minutes (default 60) back to `null`, making them eligible for a fresh scan. *(Q-030-48)* |

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-030-01 | person.created | `person_id`, `has_user_link` |
| TE-030-01b | person.updated | `person_id`, `changed_fields` |
| TE-030-02 | person.claimed | `person_id`, `user_id` |
| TE-030-02b | person.unclaimed | `person_id`, `user_id` |
| TE-030-03 | person.searchability_changed | `person_id`, `is_searchable` |
| TE-030-04 | person.merged | `source_person_id`, `target_person_id`, `faces_moved_count` |
| TE-030-05 | face.batch_created | `photo_id`, `face_count` |
| TE-030-06 | face.assigned | `face_id`, `person_id` |
| TE-030-07 | face.scan_requested | `target_type` (photo/album/bulk), `target_id`, `photo_count` |
| TE-030-08 | person.selfie_claim_requested | `user_id` |
| TE-030-09 | person.selfie_claim_matched | `user_id`, `person_id`, `confidence` |
| TE-030-10 | face.dismissed | `face_id`, `photo_id` *(Q-030-47)* |
| TE-030-11 | face.undismissed | `face_id`, `photo_id` *(Q-030-47)* |
| TE-030-12 | face.bulk_deleted | `deleted_count` (count of dismissed faces hard-deleted by API-030-16) *(Q-030-47)* |
| TE-030-13 | face.unclustered | `cluster_id`, `face_count` (faces removed from cluster via uncluster action) *(Q-030-56)* |
| TE-030-14 | face.unassigned | `face_id`, `previous_person_id` (face removed from person, returned to unassigned pool) *(Q-030-57)* |
| TE-030-15 | face.batch_updated | `action` (unassign\|assign), `face_count`, `person_id` (nullable) *(Q-030-58)* |
| TE-030-16 | face.failed_scans_reset | `reset_count` (number of failed-status photos reset to null) *(Q-030-55)* |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-030-01 | People grid | Navigate to /people → Grid of persons with face thumbnails and photo counts |
| UI-030-02 | Person detail | Click person card → Person page with paginated photos |
| UI-030-03 | Face overlay on photo | View photo detail → Dashed rectangles over detected faces with name labels |
| UI-030-04 | Face assignment modal | Click unassigned face → Modal with person selector and similarity suggestions |
| UI-030-05 | Scan progress | Trigger scan → Progress indicator (scanning N of M photos) |
| UI-030-06 | Service unavailable | Python service down → Toast notification, face features gracefully disabled |
| UI-030-07 | Selfie upload claim | User profile → "Find me" button → Upload selfie → Matching result displayed → Confirm claim |
| UI-030-08 | CTRL+click dismiss mode | Hold CTRL (desktop only) → face overlays turn red/dashed → click to dismiss directly *(FR-030-16, Q-030-70)* |
| UI-030-09 | Person miniature in dropdown | Face assignment dropdown shows 24px circular miniature + name + face count per person; type-ahead filter *(FR-030-20, Q-030-69)* |
| UI-030-10 | Face circles in detail panel | Photo detail sidebar → "People in this photo" → horizontal scrollable row of 48px circular crops with name labels *(FR-030-21, Q-030-71)* |
| UI-030-11 | Face overlay visibility toggle | Press `P` (confirmed free; `F` = fullscreen) → face overlays toggle between visible and hidden *(NFR-030-11, Q-030-65)* |
| UI-030-12 | Batch face selection mode | Person detail or cluster view → "Select" button → checkbox overlays → action bar *(FR-030-19)* |
| UI-030-13 | Merge person modal | PersonDetail → "Merge into..." → modal with person search dropdown → confirm merge *(FR-030-25)* |
| UI-030-14 | Maintenance dismiss cleanup | Maintenance page → "Destroy Dismissed Faces" card (visible only when count > 0) *(FR-030-23)* |
| UI-030-15 | Maintenance reset face scan status | Maintenance page → "Reset Face Scan Status" card — combined reset for stuck-pending (>720 min) AND failed scans (visible only when combined count > 0) *(FR-030-24, Q-030-73)* |

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
4. ADR for inter-service communication pattern (after Q-030-01 resolved).

## Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-030-01 | `tests/fixtures/face-detection-response.json` | Mock Python service response for testing result ingestion |
| FX-030-02 | `tests/fixtures/sample-face-photo.jpg` | Test photo with known face regions for bounding box validation |

## Spec DSL

```yaml
domain_objects:
  - id: DO-030-01
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
  - id: DO-030-02
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
      - name: is_dismissed
        type: boolean
        constraints: "default: false"
      - name: crop_token
        type: string
        constraints: "nullable, random high-entropy token; file stored at uploads/faces/{token[0:2]}/{token[2:4]}/{token}.jpg; served directly by nginx — path is unguessable (Q-030-34)"
  - id: DO-030-05
    name: FaceSuggestion
    fields:
      - name: face_id
        type: string
        constraints: "required, FK→faces, cascade delete"
      - name: suggested_face_id
        type: string
        constraints: "required, FK→faces, cascade delete; unique with face_id (Q-030-33)"
      - name: confidence
        type: float
        constraints: "0.0–1.0"
  - id: DO-030-06
    name: photos table addendum
    note: "Existing core table; this feature adds one nullable column (Q-030-38)"
    fields:
      - name: face_scan_status
        type: string
        constraints: "nullable, VARCHAR(16); PHP cast: ScanStatus enum; values: null, pending, completed, failed"
routes:
  - id: API-030-01
    method: GET
    path: /api/v2/People
  - id: API-030-02
    method: GET
    path: /api/v2/Person/{id}
  - id: API-030-03
    method: POST
    path: /api/v2/Person
  - id: API-030-04
    method: PATCH
    path: /api/v2/Person/{id}
  - id: API-030-05
    method: DELETE
    path: /api/v2/Person/{id}
  - id: API-030-06
    method: POST
    path: /api/v2/Person/{id}/merge
    notes: body param source_person_id; {id} = target kept
  - id: API-030-07
    method: POST
    path: /api/v2/Person/{id}/claim
  - id: API-030-08
    method: GET
    path: /api/v2/Person/{id}/photos
  - id: API-030-09
    method: POST
    path: /api/v2/Face/{id}/assign
  - id: API-030-10
    method: POST
    path: /api/v2/FaceDetection/scan
  - id: API-030-11
    method: POST
    path: /api/v2/FaceDetection/results
    notes: service-to-service only; X-API-Key auth; no user session
  - id: API-030-12
    method: POST
    path: /api/v2/FaceDetection/bulk-scan
  - id: API-030-13
    method: POST
    path: /api/v2/Person/claim-by-selfie
  - id: API-030-14
    method: PATCH
    path: /api/v2/Face/{id}
    notes: toggle is_dismissed
  - id: API-030-15
    method: DELETE
    path: /api/v2/Person/{id}/claim
    notes: unclaim person
  - id: API-030-16
    method: DELETE
    path: /api/v2/Face/dismissed
    notes: admin-only; hard-delete all is_dismissed faces + crop files (Q-030-43)
  - id: API-030-17
    method: GET
    path: /api/v2/Maintenance::resetStuckFaces
    notes: admin-only; check — count of photos stuck in pending older than threshold (Q-030-48)
  - id: API-030-17b
    method: POST
    path: /api/v2/Maintenance::resetStuckFaces
    notes: admin-only; do — reset stuck pending photos to null; body: older_than_minutes (default 60) (Q-030-48)
  - id: API-030-21
    method: GET
    path: /api/v2/Maintenance::destroyDismissedFaces
    notes: admin-only; check — count of dismissed faces (Q-030-55)
  - id: API-030-21b
    method: POST
    path: /api/v2/Maintenance::destroyDismissedFaces
    notes: admin-only; do — hard-delete all dismissed faces + crops + embeddings (Q-030-55)
  - id: API-030-22
    method: GET
    path: /api/v2/Maintenance::resetFaceScanStatus
    notes: admin-only; check — combined count of stuck-pending (>720 min) + failed face scans (Q-030-55, Q-030-73)
  - id: API-030-22b
    method: POST
    path: /api/v2/Maintenance::resetFaceScanStatus
    notes: admin-only; do — reset both stuck-pending AND failed scans to null in one operation (Q-030-73)
  - id: API-030-23
    method: POST
    path: /api/v2/FaceDetection/clusters/{cluster_id}/uncluster
    notes: remove selected faces from cluster (set cluster_label = NULL) (Q-030-56)
  - id: API-030-24
    method: POST
    path: /api/v2/Face/batch
    notes: batch face operations — unassign or assign multiple faces (Q-030-58)
  - id: API-030-25
    method: GET
    path: /api/v2/Album/{id}/people
    notes: list distinct persons in album photos (Q-030-62)
cli_commands:
  - id: CLI-030-01
    command: php artisan lychee:scan-faces
  - id: CLI-030-02
    command: php artisan lychee:scan-faces --album={id}
  - id: CLI-030-03
    command: "php artisan lychee:rescan-failed-faces [--stuck-pending] [--older-than=N]"
    note: "Re-enqueue failed photos (Q-030-40). --stuck-pending also resets pending records older than N minutes (default 60) to null (Q-030-48)"
telemetry_events:
  - id: TE-030-01
    event: person.created
  - id: TE-030-01b
    event: person.updated
  - id: TE-030-02
    event: person.claimed
  - id: TE-030-02b
    event: person.unclaimed
  - id: TE-030-03
    event: person.searchability_changed
  - id: TE-030-04
    event: person.merged
  - id: TE-030-05
    event: face.batch_created
  - id: TE-030-06
    event: face.assigned
  - id: TE-030-07
    event: face.scan_requested
  - id: TE-030-08
    event: person.selfie_claim_requested
  - id: TE-030-09
    event: person.selfie_claim_matched
  - id: TE-030-10
    event: face.dismissed
  - id: TE-030-11
    event: face.undismissed
  - id: TE-030-12
    event: face.bulk_deleted
  - id: TE-030-13
    event: face.unclustered
  - id: TE-030-14
    event: face.unassigned
  - id: TE-030-15
    event: face.batch_updated
  - id: TE-030-16
    event: face.failed_scans_reset
ui_states:
  - id: UI-030-01
    description: People grid page
  - id: UI-030-02
    description: Person detail with photo grid
  - id: UI-030-03
    description: Face overlay on photo detail
  - id: UI-030-04
    description: Face assignment modal
  - id: UI-030-05
    description: Scan progress indicator
  - id: UI-030-06
    description: Service unavailable state
  - id: UI-030-07
    description: Selfie upload claim modal
  - id: UI-030-08
    description: CTRL+click dismiss mode on face overlays
  - id: UI-030-09
    description: Person miniature in assignment dropdown
  - id: UI-030-10
    description: Face circles in photo detail panel
  - id: UI-030-11
    description: Face overlay visibility toggle (P key)
  - id: UI-030-12
    description: Batch face selection mode
  - id: UI-030-13
    description: Merge person modal
  - id: UI-030-14
    description: Maintenance dismiss cleanup card
  - id: UI-030-15
    description: Maintenance reset failed scans card
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

Communication uses REST API with webhook callbacks. Photo files are accessed via **shared Docker volume** (Q-030-07 resolved). Authentication via a single shared symmetric API key stored in `.env` as `AI_VISION_FACE_API_KEY` and read via `config('features.ai-vision.face-api-key')` — **never** from the `configs` table. Header: `X-API-Key: <key>`. *(Q-030-15 resolved: single key, both directions; Q-030-19 resolved: ai_vision_* naming)*

> **Separation of concerns:** The `POST /api/v2/FaceDetection/results` callback endpoint is authenticated **exclusively** via the `X-API-Key` header. It is not accessible via user session or admin session — even an authenticated admin cannot call this endpoint through the normal auth middleware.

**Scan Request (Lychee → Python):** `POST /detect`
```json
{
  "photo_id": "abc123",
  "photo_path": "/data/photos/original/abc123.jpg"
}
```
*(Q-030-28 resolved: `callback_url` removed from request body — Python reads `VISION_FACE_LYCHEE_API_URL` from env. `photo_path` is validated to reside within `VISION_FACE_PHOTOS_PATH` to prevent path traversal.)*

**Scan Result (Python → Lychee):** `POST /api/v2/FaceDetection/results` *(success)*
```json
{
  "photo_id": "abc123",
  "status": "success",
  "faces": [
    {
      "x": 0.25, "y": 0.10, "width": 0.15, "height": 0.20,
      "confidence": 0.982,
      "embedding_id": "emb_001",
      "crop": "<base64-encoded 150x150 JPEG>",
      "suggestions": [
        { "lychee_face_id": "face_prev_001", "confidence": 0.944 },
        { "lychee_face_id": "face_prev_002", "confidence": 0.871 }
      ]
    },
    {
      "x": 0.60, "y": 0.15, "width": 0.12, "height": 0.18,
      "confidence": 0.876,
      "embedding_id": "emb_002",
      "crop": "<base64-encoded 150x150 JPEG>",
      "suggestions": []
    }
  ]
}
```

Lychee responds with a **200** body containing the `embedding_id → lychee_face_id` mapping so Python can update its embedding store:
```json
{
  "faces": [
    { "embedding_id": "emb_001", "lychee_face_id": "face_abc123" },
    { "embedding_id": "emb_002", "lychee_face_id": "face_def456" }
  ]
}
```
*(Q-030-13 resolved: lychee_face_id stored in Python's embedding DB for selfie match resolution)*

**Scan Error Callback (Python → Lychee):** `POST /api/v2/FaceDetection/results` *(error)*
```json
{
  "photo_id": "abc123",
  "status": "error",
  "error_code": "corrupt_file",
  "message": "Unable to decode image: unexpected EOF"
}
```
Lychee sets `face_scan_status = failed` upon receiving an error payload. *(Q-030-17 resolved)*

**Selfie Match (Lychee → Python):** `POST /match` *(Q-030-12, Q-030-13 resolved)*
```
// Request: multipart form with "image" file field
// Response:
{
  "matches": [
    { "lychee_face_id": "face_abc123", "confidence": 0.963 },
    { "lychee_face_id": "face_ghi789", "confidence": 0.412 }
  ]
}
```
Lychee resolves `lychee_face_id → Face → person_id` to identify the matching Person.

**Health Check:** `GET /health` → `{"status": "ok"}`

**Embedding Deletion (Lychee → Python):** `DELETE /embeddings` *(FR-030-14)*
```json
{
  "face_ids": ["face_abc123", "face_def456"]
}
```
Response (200):
```json
{ "deleted_count": 2 }
```
Lychee dispatches this call as a fire-and-forget queued job after hard-deleting Face records (dismissed bulk-delete or Photo cascade). The Python service removes the listed embeddings from `EmbeddingStore`; IDs not found are silently ignored. Endpoint authenticated via `X-API-Key`.

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
| **Clustering** | scikit-learn DBSCAN (density-based) | Offline batch operation grouping unassigned faces for the People browse UI. **Not used for per-scan suggestions** — those use NN cosine similarity search via `sqlite-vec`/`pgvector`. Triggered manually via `POST /cluster`. *(Q-030-30 resolved)* |
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
│   │   ├── routes.py           # POST /detect, POST /match, POST /cluster (offline DBSCAN batch), GET /health
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
│   │   └── clusterer.py        # DBSCAN offline batch clustering — groups unassigned faces; NOT invoked per scan; triggered via POST /cluster *(Q-030-30)*
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

#### Concurrency Model *(Q-030-26, Q-030-27 resolved)*

InsightFace inference is synchronous and CPU-bound. The `POST /detect` handler offloads detection to a `ThreadPoolExecutor` via `asyncio.run_in_executor`, keeping the FastAPI event loop responsive while detection runs on a background thread. Pool size is configurable via `VISION_FACE_THREAD_POOL_SIZE` (default `1`).

**Structured logging checkpoints** (required at each stage):

| Event | Level | Fields |
|-------|-------|--------|
| Scan job received | `INFO` | `photo_id` |
| Detection started | `INFO` | `photo_id` |
| Detection finished | `INFO` | `photo_id`, `face_count`, `elapsed_ms` |
| Callback dispatched | `INFO` | `photo_id`, `status` |
| Callback failed | `ERROR` | `photo_id`, `callback_url`, `error` |

**Callback policy *(Q-030-27 resolved)*:** Python makes one callback attempt (fire-and-forget). If it fails, the failure is logged at `ERROR` level and the job is discarded. The photo's `face_scan_status` remains `pending` until an operator resets it manually. No retry logic; no outbox table.

#### Python Service HTTP Endpoints

All endpoints require `X-API-Key: <VISION_FACE_API_KEY>` header on every request except `GET /health` (unauthenticated).

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| `POST` | `/detect` | Required | Accept a scan job; validate `photo_path` against `VISION_FACE_PHOTOS_PATH`; run detection asynchronously via `ThreadPoolExecutor`; POST results back to `VISION_FACE_LYCHEE_API_URL/api/v2/FaceDetection/results`. Body: `DetectRequest`. |
| `POST` | `/match` | Required | Accept a multipart selfie image; detect + embed the face; cosine-search against stored embeddings; return top-N matches above `VISION_FACE_MATCH_THRESHOLD`. Body: multipart file. Response: `MatchResponse`. |
| `POST` | `/cluster` | Required | Run DBSCAN offline batch clustering on all unassigned embeddings; return cluster group assignments. Body: none. Expensive — operators call manually, not per scan. |
| `GET` | `/health` | None | Return service health status, model-loaded flag, and embedding count. Response: `HealthResponse`. |

#### Pydantic Models (Request/Response Schemas)

```python
# app/api/schemas.py

class DetectRequest(BaseModel):
    photo_id: str
    photo_path: str  # Filesystem path on shared volume; validated within VISION_FACE_PHOTOS_PATH (Q-030-28: path traversal protection)
    # callback_url is not in the request body — Python reads VISION_FACE_LYCHEE_API_URL from env (Q-030-28)

class SuggestionResult(BaseModel):
    lychee_face_id: str  # Q-030-29/33: Python returns the lychee_face_id of the suggested (similar) Face — stored as suggested_face_id in face_suggestions; Lychee JOINs to resolve person at read time
    confidence: float = Field(ge=0.0, le=1.0)

class FaceResult(BaseModel):
    x: float = Field(ge=0.0, le=1.0)
    y: float = Field(ge=0.0, le=1.0)
    width: float = Field(ge=0.0, le=1.0)
    height: float = Field(ge=0.0, le=1.0)
    confidence: float = Field(ge=0.0, le=1.0)
    embedding_id: str
    crop: str  # Base64-encoded 150×150 JPEG; Lychee stores it at uploads/faces/{token[0:2]}/{token[2:4]}/{token}.jpg (crop_token = random high-entropy token). Only the top max_faces_per_photo faces (by confidence) are included per callback (Q-030-39).
    suggestions: list[SuggestionResult] = []  # Pre-computed similar faces (Q-030-24)

class DetectCallbackPayload(BaseModel):
    photo_id: str
    status: str  # "success"
    faces: list[FaceResult]

class ErrorCallbackPayload(BaseModel):  # Q-030-17
    photo_id: str
    status: str  # "error"
    error_code: str  # e.g. "corrupt_file", "no_faces", "oom"
    message: str

class FaceMapping(BaseModel):  # Q-030-13: returned in 200 response to callback
    embedding_id: str  # Echoed back from FaceResult.embedding_id. NOT stored on Lychee's Face model — transient exchange value only; Python uses the returned lychee_face_id to update its embedding store.
    lychee_face_id: str

class DetectCallbackResponse(BaseModel):
    faces: list[FaceMapping]

class MatchRequest(BaseModel):
    # Multipart file upload — validated in endpoint, not as Pydantic model
    pass

class MatchResult(BaseModel):
    lychee_face_id: str  # Q-030-13: replaces embedding_id; resolved by Lychee to person_id
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
    lychee_api_key: str  # Key Python sends to Lychee callbacks (X-API-Key header)
    api_key: str  # Key Lychee sends to authenticate with this service (X-API-Key header)
    model_name: str = "buffalo_l"
    detection_threshold: float = 0.5  # Q-030-31: bounding box filter — faces below threshold excluded from callback
    match_threshold: float = 0.5  # Q-030-31: similarity search cutoff for suggestions and selfie matching
    rescan_iou_threshold: float = 0.5  # Q-030-35: IoU threshold for bounding-box matching on re-scan (preserves person_id)
    max_faces_per_photo: int = 10  # Q-030-39: top-N faces included in callback payload (by confidence); rest dropped
    thread_pool_size: int = 1  # Q-030-26: ThreadPoolExecutor pool size for asyncio.run_in_executor
    storage_backend: str = "sqlite"  # "sqlite" | "pgvector"
    # SQLite storage (used when storage_backend = "sqlite")
    storage_path: str = "/data/embeddings"  # Path to the SQLite DB file
    # PostgreSQL connection params (used when storage_backend = "pgvector")
    # Mapped from VISION_FACE_PG_HOST, VISION_FACE_PG_PORT, etc.
    pg_host: str = "localhost"
    pg_port: int = 5432
    pg_database: str = "ai_vision"
    pg_user: str = "ai_vision"
    pg_password: str = ""
    photos_path: str = "/data/photos"
    workers: int = 1
    log_level: str = "info"

    model_config = SettingsConfigDict(env_prefix="VISION_FACE_")  # Prefixed with service scope for future VISION_NUDENET_ etc.
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
# Bake buffalo_l model weights into the image at build time (~300 MB download). (Q-030-32 resolved: Option A)
# The resulting image is ~1 GB larger but starts instantly and works in airgapped environments.
# Model updates require an image rebuild.
RUN uv run python -c \
    "from insightface.app import FaceAnalysis; \
     app = FaceAnalysis(name='buffalo_l', root='/root/.insightface', providers=['CPUExecutionProvider']); \
     app.prepare(ctx_id=-1); \
     print('buffalo_l model downloaded.')"

FROM python:3.13-slim AS runtime
WORKDIR /app
COPY --from=builder /app/.venv /app/.venv
COPY --from=builder /root/.insightface /root/.insightface
COPY app/ ./app/
ENV PATH="/app/.venv/bin:$PATH"
EXPOSE 8000
CMD ["sh", "-c", "uvicorn app.main:app --host 0.0.0.0 --port 8000 --workers ${VISION_FACE_WORKERS:-1}"]
```

**Environment variables (runtime):** *(Prefixed `VISION_FACE_` to scope vars to this service; future services use e.g. `VISION_NUDENET_*`)*

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `VISION_FACE_LYCHEE_API_URL` | Yes | — | Lychee instance base URL for callbacks |
| `VISION_FACE_API_KEY` | Yes | — | Shared API key used in both directions: validates inbound `X-API-Key` from Lychee scan requests; also sent as `X-API-Key` on callbacks to Lychee. Must match `AI_VISION_FACE_API_KEY` on the Lychee side. |
| `VISION_FACE_MODEL_NAME` | No | `buffalo_l` | InsightFace model pack name |
| `VISION_FACE_DETECTION_THRESHOLD` | No | `0.5` | Bounding box confidence filter — faces below threshold excluded from callback *(Q-030-31)* |
| `VISION_FACE_BLUR_THRESHOLD` | No | `100.0` | Laplacian variance sharpness filter — faces whose crop Laplacian variance falls below this value are excluded as too blurry for reliable recognition (FR-030-02). Set to `0.0` to disable blur filtering. |
| `VISION_FACE_MATCH_THRESHOLD` | No | `0.5` | Similarity score cutoff for suggestions and selfie match results *(Q-030-31)* |
| `VISION_FACE_CLUSTER_EPS` | No | `0.6` | DBSCAN epsilon (maximum cosine distance between embeddings to be considered the same cluster) used by `POST /cluster` (FR-030-13). Lower values → tighter clusters. |
| `VISION_FACE_RESCAN_IOU_THRESHOLD` | No | `0.5` | IoU threshold for bounding-box matching on re-scan (preserves `person_id`) *(Q-030-35)* |
| `VISION_FACE_MAX_FACES_PER_PHOTO` | No | `10` | Maximum faces included in the callback payload (top-N by confidence; rest dropped) *(Q-030-39)* |
| `VISION_FACE_THREAD_POOL_SIZE` | No | `1` | CPU-bound inference thread pool size (`asyncio.run_in_executor`) *(Q-030-26)* |
| `VISION_FACE_STORAGE_BACKEND` | No | `sqlite` | Embedding storage: `sqlite` or `pgvector` |
| `VISION_FACE_STORAGE_PATH` | No | `/data/embeddings` | SQLite DB file path (only when `VISION_FACE_STORAGE_BACKEND=sqlite`) |
| `VISION_FACE_PG_HOST` | No* | `localhost` | PostgreSQL host (only when `VISION_FACE_STORAGE_BACKEND=pgvector`; *required in that case) |
| `VISION_FACE_PG_PORT` | No | `5432` | PostgreSQL port |
| `VISION_FACE_PG_DATABASE` | No* | `ai_vision` | PostgreSQL database name (*required when pgvector) |
| `VISION_FACE_PG_USER` | No* | `ai_vision` | PostgreSQL username (*required when pgvector) |
| `VISION_FACE_PG_PASSWORD` | No* | `` | PostgreSQL password (*required when pgvector) |
| `VISION_FACE_PHOTOS_PATH` | No | `/data/photos` | Shared volume mount for photo files |
| `VISION_FACE_WORKERS` | No | `1` | Number of Uvicorn worker processes |
| `VISION_FACE_LOG_LEVEL` | No | `info` | Logging level |

### face_scan_status State Machine *(Q-030-23 resolved)*

**Column:** `face_scan_status` on the `photos` table. Type: `VARCHAR(16)`, nullable (null = never scanned), cast in the Photo model via `ScanStatus` PHP Enum. *(Q-030-38: VARCHAR chosen for MySQL/PostgreSQL/SQLite portability.)*

```
         ┌──────────────────────┐
         │                      │
         ▼                      │  duplicate scan request
   ┌──────────┐                 │  (reset, not ignored)
   │   null   │─────dispatch────►  ┌─────────┐
   └──────────┘                    │ pending │◄──────┐
                                   └─────────┘       │
                                    │       │         │
                     success        │       │ error   │ retry /
                     callback       │       │ callback│ re-scan
                                    ▼       ▼         │
                             ┌──────────┐ ┌────────┐  │
                             │completed │ │ failed │──┘
                             └──────────┘ └────────┘
                                    │
                                    └──────re-scan──────►  pending
```

**Transition rules:**
1. `null → pending`: set when the scan job is enqueued (on dispatch), before the HTTP request to Python is sent.
2. `pending → completed`: set when Lychee receives a **success** callback payload from Python.
3. `pending → failed`: set when Lychee receives an **error** callback payload from Python. No timeout mechanism — Lychee never waits synchronously (async model).
4. `failed → pending`: retry allowed.
5. `completed → pending`: re-scan allowed.
6. `pending → pending` (duplicate request): **reset** (set to pending); the earlier pending may represent a silent timeout.
