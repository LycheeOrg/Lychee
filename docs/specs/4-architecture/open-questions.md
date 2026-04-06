# Open Questions

Track unresolved high- and medium-impact questions here. Remove each row as soon as it is resolved, ensuring the answer is captured first in the governing spec's normative sections and, for high-impact clarifications, in an ADR.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| *(no active questions for feature 030)* | | | | | | |

## Question Details

### ~~Q-032-01: Advisory URL Field Missing from DTO/Resource~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** High  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option B** — Construct GitHub URL from GHSA ID. GitHub advisory URLs follow the pattern `https://github.com/advisories/{ghsa_id}`. The frontend computes the URL from the existing `ghsa_id` field. No DTO/Resource changes needed.

**Spec Impact:** Updated FR-032-06 to clarify that advisory links are computed client-side from `ghsa_id`. Updated UI-032-02 (modal) and UI-032-04 (diagnostic panel) to note clickable links. Updated tasks T-032-13, T-032-14 to include link rendering logic.

**Resolved:** 2026-04-06

---

### ~~Q-032-02: "Go to Diagnostics" Button Dismissal Behavior~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** High  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — "Go to Diagnostics" also sets dismissal flag. Clicking "Go to Diagnostics" navigates to the diagnostics page **and** sets `sessionStorage.advisory_dismissed = '1'`. Modal does not re-appear in the same session. Consistent with "once per session" intent.

**Spec Impact:** Updated FR-032-07 to clarify both buttons set the dismissal flag. Updated UI-032-03 (modal dismissed state). Updated tasks T-032-13, T-032-14.

**Resolved:** 2026-04-06

---

### ~~Q-032-03: Advisory with Null/Missing `vulnerable_version_range`~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — Treat null/empty range as "matches all versions". If `vulnerable_version_range` is null or empty, consider the advisory applicable to all Lychee versions and include it in the diagnostic/modal output. Conservative approach ensures critical advisories aren't missed.

**Spec Impact:** Added clause to FR-032-03: "If `vulnerable_version_range` is null or an empty string, the version range check passes (advisory matches all versions)." Updated tasks T-032-03 (VersionRangeChecker tests), T-032-07 (SecurityAdvisoriesService tests).

**Resolved:** 2026-04-06

---

### ~~Q-032-04: Multiple Vulnerability Ranges Per Advisory~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — Deduplicate by advisory ID; show once. If one or more `vulnerabilities[].vulnerable_version_range` entries match the running version, include the advisory **once** in the results. Clean UX; no duplicate entries.

**Spec Impact:** Added clause to FR-032-03: "A single advisory with multiple matching `vulnerabilities[]` entries is included once (deduplicated by `ghsa_id`)." Updated tasks T-032-07 (SecurityAdvisoriesService deduplication logic), T-032-08 (SecurityAdvisoriesCheck single-entry behavior).

**Resolved:** 2026-04-06

---

### ~~Q-032-05: CVE/GHSA Display Format When `cve_id` is Null~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — "GHSA-xxxx-xxxx-xxxx  CVSS {score}". Use the same format as CVE, but with the GHSA ID when `cve_id` is null. Consistent format; GHSA is GitHub's canonical ID.

**Spec Impact:** Updated FR-032-04 diagnostic error message format: "Security vulnerability: {cve_id ?? ghsa_id} (CVSS {score})". Updated UI-032-02, UI-032-04 mock-ups to show GHSA format example. Updated tasks T-032-08, T-032-13.

**Resolved:** 2026-04-06

---

### ~~Q-032-06: Diagnostic Panel Advisory Ordering~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Custom Option D** — Sort by CVSS score descending (highest severity first), then by CVE ID descending (higher = more recent). Sort matched advisories by `cvss_score DESC NULLS LAST, cve_id DESC NULLS LAST` before adding diagnostic entries and returning API results.

**Spec Impact:** Updated FR-032-04 to specify sorting: "Matching advisories are sorted by `cvss_score DESC NULLS LAST, cve_id DESC NULLS LAST` before being added to the diagnostic pipeline." Updated tasks T-032-07 (SecurityAdvisoriesService sorting), T-032-08 (SecurityAdvisoriesCheck ordered output).

**Resolved:** 2026-04-06

---

### ~~Q-032-07: CVSS Score Display Precision~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — Always format to 1 decimal place; show "(no CVSS score)" when null. Use `number_format($score, 1)` in PHP / `.toFixed(1)` in TypeScript. Consistent visual format.

**Spec Impact:** Updated FR-032-04 diagnostic error format and UI-032-02/UI-032-04 to specify 1-decimal formatting. Added note: "When `cvss_score` is null, display '(no CVSS score)' instead of the score." Updated tasks T-032-08, T-032-13.

**Resolved:** 2026-04-06

---

### ~~Q-032-08: Cache Invalidation — No Admin Force-Refresh Path~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option C** — No force-refresh; cache TTL is acceptable. Accept the 1-day delay as tolerable. Operators who need faster updates can reduce `ADVISORIES_CACHE_TTL_DAYS` to hours (e.g., `0.042` for 1 hour). Force-refresh feature tracked as follow-up in plan backlog (CLI command or API endpoint).

**Spec Impact:** No spec changes required. Documented as follow-up in plan.md backlog.

**Resolved:** 2026-04-06

---

### ~~Q-032-09: Modal Trigger Timing — "On Each Admin Login" Ambiguous~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — After successful `POST /login` response only. The advisory check fires immediately after the login API call returns a successful response with `is_admin = true`. Does not fire on page refresh or navigation if the user is already logged in. **Important:** Check user access rights (`is_admin`) before calling the advisory endpoint to avoid a 403 response.

**Spec Impact:** Updated FR-032-06 to clarify: "The frontend checks `GET /api/v2/Security/Advisories` immediately after a successful login response (POST /login) when `is_admin` is true. The check does not fire on page refresh or navigation for already-authenticated users." Updated tasks T-032-14 to emphasize the admin rights check before the API call.

**Resolved:** 2026-04-06

---

### ~~Q-032-10: sessionStorage Multi-Tab Behavior~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — Keep `sessionStorage` (per-tab dismissal). Each tab tracks dismissal independently. Opening a new tab shows the modal again (if advisories are present). Matches spec as written; ensures advisory is seen across different browser contexts. Note: This behavior is acceptable since the modal is only triggered after a login request, not on every page load.

**Spec Impact:** No spec changes required (already uses `sessionStorage` in FR-032-07). Added clarifying note to FR-032-07: "Dismissal is scoped per browser tab (sessionStorage semantics); opening a new tab triggers the modal again on login in that tab."

**Resolved:** 2026-04-06

---

### ~~Q-031-08: `size_variants` Encoding in Query-String Payload Format~~ ✅ RESOLVED

**Feature:** 031 – Configurable Webhooks
**Priority:** High
**Status:** Resolved
**Opened:** 2026-03-25

**Context:** `payload_format = query_string` delivers all payload fields as URL query parameters. Simple scalar fields (`photo_id`, `album_id`, `title`) serialize trivially. However, `size_variants` is an array of objects (`[{type, url}]`), which has no single canonical query-string encoding.

**Resolution:** The URL of each size variant is **base64-encoded** (standard base64, not URL-safe) and delivered as a flat named query parameter using the pattern `size_variant_{type}=<base64(url)>`. For example: `size_variant_original=aHR0cHM6Ly9leGFtcGxlLmNvbS91cGxvYWRzL29yaWdpbmFsL3Bob3RvLmpwZw==&size_variant_medium=aHR0cHM6Ly9leGFtcGxlLmNvbS91cGxvYWRzL21lZGl1bS9waG90by5qcGc=`. Base64 encoding avoids any URL-encoding ambiguity for complex S3/CDN URLs.

**Spec Impact:** Updated FR-031-09, S-031-15, `WebhookPayloadBuilder`, and `WebhookDispatchJob`. Spec DSL updated.

**Resolved:** 2026-03-25

---

### ~~Q-031-01: HTTPS Enforcement for Webhook URLs~~ ✅ RESOLVED

**Resolution:** **Option A** — Allow both HTTP and HTTPS. Plain HTTP URLs are accepted at the server; the admin UI displays a security warning ("Plain HTTP transmits your secret key in cleartext.") when a non-HTTPS URL is entered. No backend enforcement.

**Spec Impact:** Updated FR-031-01 (validation path), NFR-031-06, UI-031-08, S-031-21. HTTP URL warning added to modal mock-up.

**Resolved:** 2026-03-25

---

### ~~Q-031-02: Payload Delivery for GET and DELETE Methods~~ ✅ RESOLVED

**Resolution:** **New option** — Add a `payload_format` field to the `Webhook` model. Admins choose per-webhook whether to deliver the payload as a **JSON body** (`json`) or **URL query parameters** (`query_string`). This choice is independent of HTTP method. If the admin selects `payload_format = json` with `method = GET`, Lychee sends the JSON body regardless (explicit operator choice; documented in admin guide). Note: `size_variants` encoding in query-string mode is tracked separately in Q-031-08.

**Spec Impact:** Added `payload_format` field to DO-031-01 (Webhook model), FR-031-01, FR-031-09, S-031-15, S-031-20, `WebhookPayloadFormat` enum, migration, mock-up, WebhookDispatchJob, Spec DSL.

**Resolved:** 2026-03-25

---

### ~~Q-031-03: Hard Delete vs. Soft Delete for Webhook Records~~ ✅ RESOLVED

**Resolution:** **Option A** — Hard delete only. No `deleted_at` column. The `enabled` flag provides sufficient protection.

**Spec Impact:** Updated NFR-031-02, FR-031-04, DO-031-01 (no `deleted_at`), migration (no `deleted_at` column), `WebhookController.destroy()`.

**Resolved:** 2026-03-25

---

### ~~Q-031-04: Automatic Retry Policy for Failed Dispatches~~ ✅ RESOLVED

**Resolution:** **Option A** — No automatic retry. Log failure at ERROR level and discard. `WebhookDispatchJob.$tries = 1`.

**Spec Impact:** Updated NFR-031-04, DO-031-04, `WebhookDispatchJob`.

**Resolved:** 2026-03-25

---

### ~~Q-031-05: Distinguishing `photo.add` from `photo.move` via `PhotoSaved`~~ ✅ RESOLVED

**Resolution:** **Option C** — Add new dedicated events `PhotoAdded` and `PhotoMoved`, fired from the relevant action classes. `PhotoAdded` fired from `app/Actions/Photo/Pipes/Shared/SetParent.php` for new photo records. `PhotoMoved` fired from `app/Actions/Photo/MoveOrDuplicate.php` when source and destination albums differ. Existing `PhotoSaved` remains unchanged and continues to serve existing listeners.

**Spec Impact:** Added `PhotoAdded`, `PhotoMoved` to DO-031-03, Spec DSL `domain_events`, Appendix event table. Updated FR-031-06, FR-031-07, plan Dependencies, Scope, I1 steps, I3 steps. New tasks T-031-02, T-031-14, T-031-15.

**Resolved:** 2026-03-25

---

### ~~Q-031-06: Capturing Photo Data Before Hard Deletion~~ ✅ RESOLVED

**Resolution:** **Option D** — Create a new `PhotoWillBeDeleted` event that carries the full photo snapshot (`photo_id`, `album_id`, `title`, `size_variants[]`). This event is fired from `app/Actions/Photo/Delete.php` **before** `executeDelete()`, per photo scheduled for deletion. No Eloquent hooks or model observers. Existing `PhotoDeleted` event remains unchanged.

**Spec Impact:** Added `PhotoWillBeDeleted` to DO-031-03, Spec DSL `domain_events`, Appendix event table. Updated FR-031-08, plan Dependencies, I1 steps, I3 steps. New tasks T-031-02, T-031-16.

**Resolved:** 2026-03-25

---

### ~~Q-031-07: Secret Exposure in API Response~~ ✅ RESOLVED

**Resolution:** **Option A** — Exclude raw `secret` from all API responses. Return `has_secret` (boolean) computed as `secret !== null`. Admins must set a new secret if they lose it.

**Spec Impact:** Updated DO-031-01, FR-031-02, `WebhookResource`, S-031-22, Spec DSL.

**Resolved:** 2026-03-25

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

### ~~Q-030-45: `photo_ids[]` Batch in API-030-10 Has No Maximum~~ ✅ RESOLVED

**Resolution:** **Option B** — accept any count, dispatch in configurable chunks. The job dispatcher slices the photo ID list into chunks of size `ai_vision_face_scan_batch_size` (Lychee `configs` table, default `200`). No hard caller limit; queue load controlled by chunk size + queue concurrency.

**Spec Impact:** Add `ai_vision_face_scan_batch_size` to the Lychee `configs` table (integer, default `200`). Update API-030-10 notes to describe chunked dispatch.

**Resolved:** 2026-03-18

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

**Resolution:** Deferred for now. The current four-level permission mode semantic is correct. However, the policy also needs refinement with regard to album/photo edit rights (which is not currently applied). For now, focus on the UI/UX interaction. Policy refinement will be revisited in a future iteration. Captured as a note in NFR-030-07.

**Context:** The `AiVisionPolicy` currently checks `ai_vision_face_permission_mode` globally but does not cross-check whether the user has edit rights on the specific album or photo. For example, in `privacy-preserving` mode, "photo/album owner + admin" should mean the owner of the specific album/photo, but the current policy may not check actual album ownership.

**Impact:** Deferred — no immediate spec changes beyond noting the gap.

**Resolved:** 2026-04-04 (deferred for future iteration)

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

**Resolution:** **Option B** — Defer to next iteration. The current four-level permission mode semantic (public/private/privacy-preserving/restricted) provides a reasonable baseline. Policy refinement for per-resource album/photo ownership is deferred. This is the same conclusion as Q-030-63. Captured as a note in NFR-030-07 policy refinement note.

**Context:** The current `AiVisionPolicy` checks the global `ai_vision_face_permission_mode` but does not cross-reference the user's actual edit rights on the specific album or photo. In `privacy-preserving` and `restricted` modes, "photo/album owner" should mean the owner of that specific resource, but the current implementation may check ownership globally.

**Impact:** High — could allow users to assign/dismiss faces on photos they don't own. Affects all face operations gated on "photo/album owner + admin".

**Option A — Add album/photo ownership checks to policy methods**
- For operations like face assignment, dismiss, and scan trigger: check that the authenticated user owns (or has edit rights on) the photo or its containing album.
- Use existing `PhotoPolicy` and `AlbumPolicy` gates alongside `AiVisionPolicy`.

**Option B (Chosen) — Defer to next iteration (current approach)**
- Accept the gap for now. The four-level mode provides a reasonable baseline. Refine later.

**Affects:** AiVisionPolicy, FaceController, FaceDetectionController, all face-related request classes.

**Resolved:** 2026-04-04 (deferred for future iteration)

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


### ~~Q-030-32: InsightFace Model Acquisition — Baked Into Docker Image vs. Runtime Download~~ ✅ RESOLVED

**Resolution:** **Option A** — bake `buffalo_l` model weights into the Docker image at build time via a `RUN` step in the builder stage. The multi-stage Dockerfile copies the downloaded model folder from builder to runtime. Image is significantly larger (~1GB+) but starts instantly and works in airgapped environments. Model updates require an image rebuild (acceptable given model stability).

**Spec Impact:** Update Dockerfile spec: add `RUN uv run python -c "..."` model download step in builder stage; add `COPY --from=builder /root/.insightface /root/.insightface` in runtime stage. Note model size and rebuild requirement in Docker configuration section.

**Resolved:** 2026-03-17

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

### Q-030-14: Re-scan Destroys Manual Face Assignments

**Question:** FR-030-07 says re-scanning a photo replaces old Face records (idempotent). But if a user manually assigned Face → Person, re-scan deletes those records and creates new unassigned ones. All manual assignment work is lost silently. Is this acceptable?

**Impact:** Affects I10 (scan result ingestion). Could cause significant user frustration with no recourse.

**Options:**
- **(A)** Preserve assignments: match new faces to old faces by bounding box IoU overlap (≥ threshold), carry over `person_id` from old → new face. Delete truly gone faces.
- **(B)** Soft-delete old faces — mark as `superseded` but keep records. Let user review changes.
- **(C)** Block re-scan on photos with any assigned faces unless user explicitly confirms (force flag).
- **(D)** Accept data loss — document it as expected behavior. User must re-assign after re-scan.

**Affects:** FR-030-07, S-030-14, I10, ProcessFaceDetectionResults action.

---

### Q-030-15: Two API Keys but Lychee Config Only Defines One

**Question:** The inter-service contract requires two authentication directions:
1. **Lychee → Python** (scan requests): Python validates incoming requests via `FACE_API_KEY`.
2. **Python → Lychee** (callbacks): Lychee validates incoming results via... what?

The Lychee config migration only defines `face_recognition_api_key` (singular). Which direction does it authenticate? What HTTP header format is used (`Authorization: Bearer <key>`, `X-API-Key: <key>`, etc.)?

**Impact:** Blocks I3 (Python API key auth), I4 (Lychee config migration), I10 (result ingestion auth).

**Options:**
- **(A)** Single shared symmetric key — same key used in both directions. Simpler but less secure (compromise exposes both directions). Header: `X-API-Key: <key>`.
- **(B)** Two separate keys — Lychee config gets `face_recognition_api_key` (Lychee sends to Python) + `face_recognition_callback_key` (Python sends to Lychee). Header: `X-API-Key: <key>`.

**Affects:** FR-030-07, FR-030-08, I3, I4, I10, inter-service contract, Pydantic `AppSettings`.

---

### Q-030-16: Missing Face Deletion Endpoint for False Positives

**Question:** There is no API to delete a Face record. If the detector produces a false positive (e.g., a face detected in tree bark, a painting, etc.), the user has no way to remove it. This is a basic UX requirement for any face detection system.

**Impact:** Affects I9 (FaceController), frontend face overlay UX.

**Options:**
- **(A)** Add `DELETE /api/v2/Face/{id}` — hard-delete Face record + crop file. Authorization: photo owner or admin. Add to API catalogue as API-030-14.
- **(B)** Add `is_dismissed` boolean to Face model — dismissed faces hidden from UI but record retained for re-scan deduplication. Toggle via `PATCH /api/v2/Face/{id}`.
- **(C)** Both — dismiss by default, hard-delete as admin action.

**Affects:** FR-030-02, I9, I15 (face overlay UI needs a "dismiss" or "delete" action), migrations (if option B).

---

### Q-030-17: Error Callback Shape Undefined

**Question:** If the Python service fails to process a photo (corrupt file, unsupported format, OOM, model error), what does it POST back to Lychee? The inter-service contract only defines the success payload (`DetectCallbackPayload`). Without an error callback, `face_scan_status` will remain `pending` indefinitely for failed photos.

**Impact:** Blocks I2 (Python callback flow), I10 (result ingestion — needs to handle errors), I11 (bulk scan progress tracking).

**Options:**
- **(A)** Define error callback payload: `{"photo_id": "abc", "status": "error", "error_code": "corrupt_file", "message": "..."}`. Lychee sets `face_scan_status = failed`. Add `ErrorCallbackPayload` Pydantic model.
- **(B)** Python doesn't callback on failure; Lychee has a configurable timeout (e.g., `face_recognition_scan_timeout` = 5 min) that marks stale `pending` → `failed` via scheduled job.
- **(C)** Both — Python best-effort error callback + Lychee timeout as safety net.

**Affects:** Inter-service contract, `face_scan_status` transitions, I2, I10, I11, Pydantic schemas.

---

### Q-030-18: Spec DSL Type Mismatch — Face.person_id

**Question:** In the Spec DSL (line ~338), DO-030-02 declares `person_id` with `type: integer` but the actual FK target (Person PK) is `string`. The constraints say `"FK→persons (string)"` contradicting the type field. This is a copy-paste error that could generate wrong migrations if the DSL is used as a generation source.

**Impact:** Low runtime risk (DSL is documentary), but misleading if used for code generation.

**Options:**
- **(A)** Fix: change `type: integer` → `type: string` on `person_id` in DO-030-02.

**Affects:** Spec DSL only.

---

### Q-030-19: Naming Inconsistency — FACE_* Prefix vs ai-vision-service

**Question:** The service directory is `ai-vision-service` (chosen for future extensibility: tagging, scene detection, etc.), but all environment variables use `FACE_*` prefix and all Lychee config keys use `face_recognition_*`. Should these be renamed for consistency and extensibility?

**Impact:** Naming decision that becomes harder to change after v1 ships.

**Options:**
- **(A)** Keep `FACE_*` / `face_recognition_*` — scope is facial recognition for now; rename later if/when new capabilities added.
- **(B)** Rename to `VISION_*` / `ai_vision_*` — future-proof now. More churn in spec but cleaner long-term.
- **(C)** Hybrid: service-level config uses `VISION_*` (generic), Lychee-side config stays `face_recognition_*` (feature-specific).

**Affects:** Pydantic `AppSettings` (env_prefix), Lychee config migration, docker-compose, all documentation.

---

### Q-030-20: Permission Mode Scope per Operation Is Ambiguous

**Question:** The `face_recognition_permission_mode` setting (`open` / `restricted`) is defined but the spec doesn't enumerate which operations each mode governs. Specifically:

- **open**: Any authenticated user can do what exactly? CRUD persons? Assign faces? Trigger scans? View all persons?
- **restricted**: Only photo/album owner or admin — but for which operations? Can a non-owner VIEW persons in restricted mode? Can they see face overlays on photos they have album access to?

**Impact:** Affects I7, I8, I9, I10 — every controller needs to know what to gate.

**Options:**
- **(A)** Define a per-operation matrix:
  | Operation | open | restricted |
  |-----------|------|-----------|
  | View People page | all users | all users |
  | View face overlays | album access | album access |
  | Create/edit Person | all users | admin only |
  | Assign face | all users | photo owner + admin |
  | Trigger scan | all users | photo/album owner + admin |
  | Claim person | all users | all users |
  | Merge persons | all users | admin only |
- **(B)** Simpler: `open` = all authenticated users for everything; `restricted` = admin-only for all write operations, read follows album access.

**Affects:** NFR-030-07, I7, I8, I9, I10, form request authorization.

---

### Q-030-21: Missing Person Unclaim Endpoint

**Question:** FR-030-05 describes claim behavior and test intents reference "unclaim", but there's no API route for unclaiming a Person (removing the User link). How does a user or admin remove a Person-User link?

**Impact:** Affects I8 (claim controller).

**Options:**
- **(A)** Add `DELETE /api/v2/Person/{id}/claim` — removes `person.user_id`. Linked user or admin only. Add as API-030-15.
- **(B)** Use existing `PATCH /api/v2/Person/{id}` with `user_id: null` — no new route needed, but less semantic.

**Affects:** FR-030-05, API catalogue, I8.

---

### Q-030-22: Merge Direction Ambiguity on API-030-06

**Question:** `POST /api/v2/Person/{id}/merge` with body `{target_person_id}`. Which person is destroyed?

- Reading 1: `{id}` is the **source** (destroyed), faces moved to `target_person_id` (kept).
- Reading 2: `{id}` is the **target** (kept), body's `source_person_id` provides the one destroyed.

REST convention: the URL resource (`{id}`) is typically the one acted upon and preserved. The current spec text says "merge source into target" with `{id}` and body `target_person_id`, which implies `{id}` = source (destroyed). This contradicts the convention.

**Impact:** Affects I8 (merge implementation), frontend merge UI.

**Options:**
- **(A)** `{id}` = target (kept). Rename body param to `source_person_id`. Follows REST convention.
- **(B)** `{id}` = source (destroyed). Keep body as `target_person_id`. Document explicitly.

**Affects:** API-030-06, FR-030-11, I8, I14 (frontend merge action).

---

### Q-030-23: face_scan_status State Machine Transitions Undefined

**Question:** The `face_scan_status` enum (`null` / `pending` / `completed` / `failed`) is added to the photos table but its state transitions are not documented:

1. What sets `pending`? (DispatchFaceScanJob dispatch? Or the HTTP request to Python?)
2. What sets `completed`? (The callback handler in ProcessFaceDetectionResults?)
3. What sets `failed`? (Error callback? Timeout? Exception in job?)
4. Can `failed` → `pending` (retry)? Can `completed` → `pending` (re-scan)?
5. If a photo is `pending` and user triggers another scan, what happens? Ignore? Reset?

**Impact:** Affects I10, I11, bulk scan progress reporting.

**Options:**
- **(A)** Define explicit state machine:
  - `null` → `pending` (scan requested)
  - `pending` → `completed` (success callback received)
  - `pending` → `failed` (error callback or timeout)
  - `failed` → `pending` (retry allowed)
  - `completed` → `pending` (re-scan allowed)
  - `pending` → `pending` (duplicate request ignored — no-op)

**Affects:** I10, I11, DispatchFaceScanJob, ProcessFaceDetectionResults.

---

### Q-030-24: Similar Faces in Assignment Modal — Data Source Unspecified

**Question:** UI-030-04 (Face Assignment Modal) shows "Similar faces found: [Alice (94%)] [Bob (12%)]". This implies a similarity query — given an unassigned face, find the most similar existing persons. But there's no Lychee API endpoint that provides this data. Where does it come from?

**Impact:** Affects I16 (frontend assignment modal), possibly I2 (Python service), possibly new API endpoint.

**Options:**
- **(A)** Pre-computed during scan: Python includes `cluster_suggestion` or `similar_embedding_ids` in the callback. Lychee stores these on the Face record or a separate suggestions table.
- **(B)** On-demand query: when user opens assignment modal, frontend calls a new endpoint (e.g., `GET /api/v2/Face/{id}/suggestions`) which queries Python service for similar embeddings → resolves to Persons.
- **(C)** Frontend-only heuristic: no similarity data. Drop the "Similar faces found" from the modal. User picks from a Person dropdown only.

**Affects:** UI-030-04, possibly new API endpoint, I2 (if pre-computed), I16.

---

### Q-030-25: Crop Storage Path Pattern Undefined

**Question:** Face crops (150×150 JPEG) are described as "stored alongside size variants" but the actual filesystem path pattern is not specified. This matters for:
- Generating crop URLs for frontend display.
- Cleanup on Face deletion or re-scan.
- Serving via Lychee's existing media serving pipeline.

**Impact:** Affects I10 (ProcessFaceDetectionResults — where to write), I6 (FaceResource crop_url), I16 (frontend crop display).

**Options:**
- **(A)** Store under photo's size variant directory: `{photo_variant_dir}/faces/{face_id}.jpg`. Served via same media controller.
- **(B)** Dedicated faces directory: `uploads/faces/{face_id}.jpg`. Separate serving route.
- **(C)** Store in `storage/app/faces/{face_id}.jpg` — Laravel storage disk, served via signed URL or controller.

**Affects:** FR-030-02, I10, I6, Face model `crop_url` accessor, frontend.

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

### ~~Q-029-01: Destination album for camera capture from root view~~ ✅ RESOLVED

**Question:** When the user takes a photo from the root albums view (not inside any album), where should the captured photo be stored?

**Resolution:** Upload with no album ID — photo lands in the "Unsorted" smart album, consistent with existing upload behaviour at root level.

**Resolved:** 2026-03-18

---

### ~~Q-026-01: TagAlbum and Smart Album Support Scope~~ ✅ RESOLVED

**Question:** Should TagAlbums and Smart Albums support tag filtering in the future, or is "only regular Albums" a permanent architectural decision?

**Resolution:** Tag filtering applies to **all album types** (regular Albums, TagAlbums, and Smart Albums) in v1.

**Rationale:** User specified "This is for all albums: regular, tags, smart." The feature should provide consistent filtering UX across all album types.

**Spec Impact:** Remove "Filtering TagAlbums or Smart Albums" from Non-Goals; update FR-026-01 to clarify support for all album types; add test scenarios for TagAlbum and SmartAlbum filtering.

**Resolved:** 2026-03-09

---

### ~~Q-026-02: Large Tag List UX Strategy (100+ Tags)~~ ✅ RESOLVED

**Question:** How should the tag filter UI handle albums with 100+ unique tags (beyond the spec's "up to 20 unique tags" performance target)?

**Resolution:** **Option B** - Add search/filter to tag dropdown in v1 (enable PrimeVue MultiSelect `filter` prop).

**Rationale:** PrimeVue MultiSelect has built-in filter capability; minimal implementation effort for better UX.

**Spec Impact:** Update NFR-026-02 (Usability) to note that tag dropdown includes search/filter for large tag lists.

**Resolved:** 2026-03-09

---

### ~~Q-026-03: URL-based Filter State Representation~~ ✅ RESOLVED

**Question:** Should the active tag filter be represented in the URL query string (e.g., `/gallery/album-id?tag_ids=1,2&tag_logic=OR`) to enable bookmarking and sharing, or should it remain in component state only?

**Resolution:** **Option A** - Component state only; no URL representation in v1.

**Rationale:** Simpler implementation for v1. Filter state stored in component `ref()` without Vue Router query param synchronization. Users cannot bookmark/share filtered views (accepted limitation).

**Spec Impact:** Non-Goals already documents this; no change needed.

**Resolved:** 2026-03-09

---

### ~~Q-026-04: Album::tags Security Filtering Approach~~ ✅ RESOLVED

**Question:** For the `Album::tags` endpoint, should it apply per-photo security filters when fetching tags (e.g., only include tags from public photos when viewing as guest), or rely solely on album-level access check?

**Resolution:** **Album-level access only** (Option A). Album::tags returns tags from photos directly attached to that album. Album-level access rights determine which photos are accessible, and thus which tags should be returned.

**Rationale:** User clarified: "Album::tags should return the list of tags which are associated to the photos directly attached to that album. The access rights on the album_id determine directly what photos are accessible, thus which tags should be returned."

**Spec Impact:** Clarify FR-026-01 to explicitly state album-level access model; no per-photo filtering required.

**Resolved:** 2026-03-09

---

### ~~Q-026-05: Behavior When All Tag IDs Are Invalid~~ ✅ RESOLVED

**Question:** When a user provides tag IDs via `tag_ids[]` parameter and ALL of them are invalid (don't exist in database), should the endpoint return all photos (treating invalid IDs as "no filter") or an empty result?

**Resolution:** **Option C** - Return validation error (422 Unprocessable Entity) when all tag IDs are invalid.

**Rationale:** Clear feedback to client that the request was invalid. Individual invalid IDs are still silently ignored, but if the entire filter set is invalid, return error.

**Spec Impact:** Update FR-026-02 to clarify: "Invalid tag IDs individually ignored; if ALL provided tag IDs are invalid, return 422 validation error."

**Resolved:** 2026-03-09
### ~~Q-027-04: Named-Colour Name→Hex Mapping Mechanism~~ ✅ RESOLVED

**Decision:** Option A — Hardcode a PHP `ColourNameMap` class (e.g. `app/Actions/Search/ColourNameMap.php`) containing a `const` array mapping lowercase CSS colour names to `#rrggbb` hex strings, covering the 16 basic CSS Level 1 colours. `ColourStrategy` consults this map when the token value does not start with `#`. Unknown names throw `InvalidTokenException` → HTTP 400. No schema migration required.  
**Rationale:** No DB dependency; stateless; testable in isolation; fast. The `colours` table has no `name` column and `Colour::fromHex()` only accepts hex strings, so a hardcoded PHP map is the only viable no-migration path.  
**Updated in spec:** FR-027-09 (named-colour resolution description updated), T-027-03 and T-027-22 notes updated.

---

### ~~Q-027-05: Invalid SQL Syntax in Colour-Similarity EXISTS Subquery~~ ✅ RESOLVED

**Decision:** Option A — Replace the invalid `JOIN … ON c.id IN (…)` with an explicit OR expansion in the `ON` clause:

```sql
EXISTS (
  SELECT 1 FROM palette p
  JOIN colours c ON (c.id = p.colour_1 OR c.id = p.colour_2 OR c.id = p.colour_3 OR c.id = p.colour_4 OR c.id = p.colour_5)
  WHERE p.photo_id = photos.id
    AND ABS(c.R - :R) + ABS(c.G - :G) + ABS(c.B - :B) <= :dist
)
```

**Rationale:** Standard SQL valid across SQLite, MySQL, and PostgreSQL. Within an `EXISTS` the five-OR join is harmless — multiple matching `colours` rows per palette row are irrelevant since `EXISTS` short-circuits on the first match.  
**Updated in spec:** FR-027-09, NFR-027-04 (both SQL snippets corrected); plan.md I7; tasks.md T-027-22.

---

### ~~Q-027-01: Colour Distance Metric and Named-Colour Lookup~~ ✅ RESOLVED

**Decision:** `palette.colour_N` values are foreign keys to `colours.id` (the packed 0xRRGGBB integer); the `colours` table already has separate `R`, `G`, `B` integer columns. Use a JOIN `palette → colours ON colours.id IN (p.colour_1, …, p.colour_5)` and compute Manhattan distance directly on `colours.R/G/B`. No schema migration required. Named colours resolved via `Colour::fromHex()` / `colours` table lookup.  
**Rationale:** The separate R/G/B columns are already present in the DB; no bit-shift needed, fully portable across SQLite/MySQL/PostgreSQL.  
**Updated in spec:** FR-027-09 (colour query mechanism), NFR-027-04 (SQL portability note updated).

---

### ~~Q-027-02: Rating Filter — Own Rating vs Average Rating~~ ✅ RESOLVED

**Decision:** Option C — Support both sub-modifier forms: `rating:avg:>=4` (filters by `photos.rating_avg`) and `rating:own:>=4` (filters by the requesting user's own rating via JOIN on `photo_ratings WHERE user_id = Auth::id()`). Unauthenticated users may only use `rating:avg:`.  
**Rationale:** Maximum flexibility; users with personal rating habits benefit from `own:` while gallery visitors can still filter by average.  
**Updated in spec:** FR-027-14 (rating sub-modifiers), grammar reference updated, scenarios S-027-21/S-027-22 added.

---

### ~~Q-027-03: Album Search Modifier Support — This Feature or Follow-up?~~ ✅ RESOLVED

**Decision:** Option B — Include album modifier support (`title:`, `description:`, `date:`) in Feature 027. `AlbumSearch` is wired to the same `SearchTokenParser`; a new `AlbumSearchTokenStrategy` interface mirrors `PhotoSearchTokenStrategy`.  
**Rationale:** Consistent user experience in a single release; the token infrastructure from the photo search is directly reusable.  
**Updated in spec:** FR-027-15 (album modifiers), Non-Goals updated (album modifiers removed), scenarios S-027-23/S-027-24 added.

---

### ~~Q-020-01: RAW Conversion Failure Behavior~~ ✅ RESOLVED

**Decision:** Option C — Fall back to existing `raw_formats` behavior (store unprocessed, no conversion)
**Rationale:** Graceful degradation preserves the uploaded file. If Imagick cannot convert the RAW file, it is stored as-is using the existing accepted-raw path (the raw file becomes the ORIGINAL with no thumbnails). Additionally, a data migration will move existing files that are currently stored as ORIGINAL but match raw format extensions to the new RAW size variant type.
**Updated in spec:** FR-020-03 (failure path), FR-020-16 (migration of existing raw-format files from ORIGINAL to RAW type)

---

### ~~Q-020-02: RAW Conversion Tooling & Imagick Delegate Requirements~~ ✅ RESOLVED

**Decision:** Option A — Require Imagick with libraw/dcraw delegates; document system requirements
**Rationale:** Single code path through Imagick. Existing `HeifToJpeg` already uses Imagick. System requirement: `apt install libraw-dev` (or equivalent) for camera RAW delegate support. If a specific format is unsupported by the installed Imagick delegates, the fallback from Q-020-01 applies (file stored as-is).
**Updated in spec:** NFR-020-04 (Imagick requirement), FR-020-09 (conversion tooling)

---

### ~~Q-020-03: Async Conversion for Large RAW Files~~ ✅ RESOLVED

**Decision:** Option A — Synchronous conversion (already async via job pipeline)
**Rationale:** Lychee already processes uploads through queued jobs, so conversion is inherently asynchronous from the user's perspective. No additional async infrastructure is needed. The conversion runs within the existing job pipeline.
**Updated in spec:** NFR-020-02 (clarified: conversion happens in existing job pipeline)

---

### ~~Q-020-04: Interaction with Existing `raw_formats` Config~~ ✅ RESOLVED

**Decision:** Option A — Keep both systems separate, with refinement
**Rationale:** The `raw_formats` config continues to define accepted extra formats. However, files matching `raw_formats` are now stored as **RAW size variants** (not ORIGINAL) — unless they are PDF, which remains stored as ORIGINAL (since PDF can be rendered/displayed). The new convertible-RAW pipeline (camera RAW + HEIC/HEIF) is a separate hardcoded list that triggers conversion. If an extension is in both lists, the new RAW pipeline takes precedence.
**Updated in spec:** FR-020-03, FR-020-04, FR-020-09, FR-020-16 (unprocessed raw_formats files stored as RAW type, PDF exception)

---

### ~~Q-019-01: Hierarchical vs Flat Slugs~~ ✅ RESOLVED

**Decision:** Option A — Flat globally-unique slugs
**Rationale:** Simpler implementation with a single `slug` column and unique index on `base_albums`. No dependency on parent album structure — renaming/moving a parent doesn't invalidate child slugs. Easier to reason about uniqueness and collisions.
**Updated in spec:** FR-019-01 (slug on `base_albums`), FR-019-03 (global uniqueness), Non-Goals (hierarchical paths explicitly excluded)

---

### ~~Q-019-02: Top-Level Route Support~~ ✅ RESOLVED

**Decision:** Option A — Gallery-prefixed only (`/gallery/{slug}`)
**Rationale:** No collision risk with existing routes (`/settings`, `/profile`, `/login`, etc.). No changes to web route definitions — slug resolution happens inside the existing `{albumId}` parameter. Simpler, safer, ships faster.
**Updated in spec:** FR-019-05 (resolution within existing route), FR-019-10 (Vue Router `/gallery/{slug}`), Non-Goals (top-level routes excluded)

---

### ~~Q-019-03: Tag Album Slug Support~~ ✅ RESOLVED

**Decision:** Option A — Both Album and TagAlbum (via shared `base_albums` table)
**Rationale:** The `slug` column lives on `base_albums`, which is shared by both Album and TagAlbum. Consistent behaviour — any album-like entity can have a friendly URL. No special-casing needed in the factory or validation.
**Updated in spec:** FR-019-01 (column on `base_albums`), FR-019-03 (uniqueness across Album + TagAlbum), S-019-14 (tag album scenario)

---

### ~~Q-017-01: Context Menu Scope Behaviour for Photos vs Albums~~ ✅ RESOLVED

**Decision:** Option A — Scope radio hidden for photos, shown for albums
**Rationale:** Most intuitive UX. Photos have no descendants so scope is meaningless — hide it. Albums support "Current level" (rename only selected album titles) and "All descendants" (selected albums + sub-albums recursively). Backend receives `album_ids[]` + `scope` for the album path; `photo_ids[]` only (no scope) for the photo path.
**Updated in spec:** FR-017-07 (scope hidden for photos), FR-017-08 (scope shown for albums), FR-017-09 (contract split by target type)

---

### ~~Q-017-02: No Renamer Rules Configured Edge Case~~ ✅ RESOLVED

**Decision:** Option A — Show the empty preview with an enhanced message
**Rationale:** Simplest approach with no extra API calls. The empty-state message is enhanced: "No titles would change. If you haven't configured renamer rules yet, visit Settings → Renamer Rules." Minimal code change, no additional data dependencies.
**Updated in spec:** FR-017-05 (enhanced empty-state message), UI-017-05

---

### ~~Q-011-02: Default Sort Order for My Rated Pictures Album~~ ✅ RESOLVED

**Decision:** Option A - Sort by rating DESC, then by created_at DESC
**Rationale:** Shows highest-rated photos first, consistent with "favorites" concept. Most intuitive for users wanting to see their best-rated photos at the top.
**Updated in spec:** FR-011-01, query implementation details

---

### ~~Q-011-01: Config Key Naming for My Best Pictures Count~~ ✅ RESOLVED

**Decision:** Option A - Separate config key `my_best_pictures_count`
**Rationale:** Allows independent configuration. Users might want different counts for overall best pictures vs personal favorites. Clearer semantics with each album having its own setting.
**Updated in spec:** CFG-011-03, DO-011-02 implementation

---

### ~~Q-010-12: TLS/StartTLS Configuration~~ ✅ RESOLVED

**Decision:** Option A - Single `LDAP_USE_TLS` flag, protocol determined by port
**Rationale:** Simpler configuration with fewer env vars. Protocol auto-detected: port 636 = LDAPS, port 389 = StartTLS. Documentation in .env.example clarifies both scenarios.
**Updated in spec:** ENV-010-13, I10 documentation deliverables

---

### ~~Q-010-11: Authentication Flow Sequence~~ ✅ RESOLVED

**Decision:** Option A - Search-first pattern (username → search → DN → bind → groups)
**Rationale:** Flexible approach supporting diverse LDAP schemas. Flow: 1) User submits username+password, 2) Search LDAP using `LDAP_USER_FILTER`, 3) Get userDn from search result, 4) Bind with userDn+password, 5) Query groups using userDn, 6) Retrieve user attributes.
**Updated in spec:** FR-010-01, I2 LdapService `authenticate()` method, I4 `getUserGroups()` signature

---

### ~~Q-010-10: Testing Strategy~~ ✅ RESOLVED

**Decision:** Option A - LdapRecord testing utilities for unit tests, skip Docker integration tests
**Rationale:** Fast unit tests using LdapRecord's `DirectoryEmulator` or test helpers. Mock LDAP responses at service boundary. Docker integration tests deferred to future enhancement.
**Updated in spec:** I2-I7 test implementation, no Docker CI configuration needed

---

### ~~Q-010-09: Connection Pooling Implementation~~ ✅ RESOLVED

**Decision:** Option A - Configure LdapRecord's built-in connection management
**Rationale:** Leverage existing, tested library features. Configure timeouts and connection caching via LdapRecord config. No custom pooling code needed.
**Updated in spec:** I2 implementation approach, NFR-010-04

---

### ~~Q-010-08: LdapConfiguration DTO Purpose~~ ✅ RESOLVED

**Decision:** Option A - LdapConfiguration validates/transforms .env values
**Rationale:** Clean validation layer providing type-safe value object. Single source of truth: .env → LdapConfiguration::fromEnv() validates → values passed to LdapRecord config. Prevents invalid config, provides testability.
**Updated in spec:** I1 LdapConfiguration DTO implementation, validation strategy

---

### ~~Q-010-07: LdapRecord Integration Strategy~~ ✅ RESOLVED

**Decision:** Option A - Service layer wrapping LdapRecord
**Rationale:** Better separation of concerns and testability. `LdapService` acts as facade/adapter over LdapRecord's Connection and query builder. Business logic abstracted from LDAP library details. Easier to test (mock LdapService interface) and swap libraries if needed.
**Updated in spec:** I2-I5 architecture, LdapService design as wrapper pattern

---

### ~~Q-010-07: LdapRecord Integration Strategy~~ (ARCHIVED - moved above)

**Question:** How should `App\Services\Auth\LdapService` integrate with LdapRecord?

- **Option A (Recommended):** Service layer wrapping LdapRecord
  - Create `LdapService` as a facade/adapter over LdapRecord's `Connection`, `Model`, and query builder
  - Business logic lives in `LdapService`, LDAP library details abstracted
  - Easier testing (mock LdapService interface)
  - Easier to swap LDAP libraries in future if needed
  
- **Option B:** Direct LdapRecord usage throughout codebase
  - AuthController and Actions call LdapRecord directly
  - Less abstraction, fewer layers
  - Tighter coupling to LdapRecord API
  - Testing requires mocking LdapRecord classes

**Pros/Cons:**
- **A:** Better separation of concerns, testability; adds abstraction layer
- **B:** Simpler, fewer files; harder to test, tight coupling

**Impact:** HIGH - affects architecture, testing strategy, and implementation complexity across all increments (I2-I5)

---

### Q-010-08: LdapConfiguration DTO Purpose

**Question:** What is the relationship between `App\DTO\LdapConfiguration` and LdapRecord's `config/ldap.php`?

- **Option A (Recommended):** LdapConfiguration validates/transforms .env values
  - `LdapConfiguration` is a validated value object created from .env variables
  - Values are passed to LdapRecord's config at runtime
  - Single source of truth: .env → LdapConfiguration → LdapRecord config
  - Validation happens in DTO constructor
  
- **Option B:** LdapConfiguration duplicates LdapRecord config
  - Separate parallel configuration system
  - Risk of config drift between two systems
  - More complex synchronization

**Pros/Cons:**
- **A:** Clean validation layer, no duplication; .env values must be transformed
- **B:** More flexible; potential sync issues, redundant config

**Impact:** MEDIUM - affects I1 configuration setup and validation strategy

---

### Q-010-09: Connection Pooling Implementation

**Question:** What does "implement connection pooling logic" mean given LdapRecord already manages connections?

- **Option A (Recommended):** Configure LdapRecord's built-in connection management
  - Use LdapRecord's connection caching and reuse features
  - Configure timeouts via LdapRecord config
  - No custom pooling code needed
  
- **Option B:** Build custom connection pool
  - Implement connection reuse, timeout, retry logic manually
  - More control over pool behavior
  - Significant additional complexity

**Pros/Cons:**
- **A:** Leverage existing, tested library feature; less code
- **B:** Full control; reinventing the wheel, higher maintenance

**Impact:** MEDIUM - affects I2 implementation complexity and testing

---

### Q-010-10: Testing Strategy for LDAP Operations

**Question:** How should LDAP server responses be mocked for deterministic testing?

- **Option A (Recommended):** LdapRecord's testing utilities for unit tests + optional Docker for integration
  - Use LdapRecord's `DirectoryEmulator` or test helpers for unit tests
  - Mock LDAP responses at service boundary
  - Optional: `rroemhild/test-openldap` Docker image for integration tests
  
- **Option B:** Docker LDAP server for all tests
  - Realistic LDAP server in test environment
  - Slower test execution
  - More complex CI setup
  
- **Option C:** PHP mock/stub classes only
  - Fastest execution
  - May not catch library integration issues
  - No LdapRecord-specific testing utilities

**Pros/Cons:**
- **A:** Fast unit tests + realistic integration tests; best of both worlds
- **B:** Most realistic; slowest, most complex
- **C:** Simplest, fastest; least realistic

**Impact:** MEDIUM - affects I2-I7 test implementation and CI configuration

---

### Q-010-11: Authentication Flow Sequence

**Question:** What is the complete flow from username to group membership, including how userDn is obtained?

Need to clarify the sequence:
1. User submits username + password
2. How do we get the userDn? 
   - **Option A:** Search for user first (`LDAP_USER_FILTER`) → get DN → bind with DN + password
   - **Option B:** Construct DN from username (e.g., `uid={username},ou=people,dc=example,dc=com`) → bind directly
3. After successful bind, query groups using userDn
4. Retrieve user attributes
5. Map groups to roles

**Recommended:** Option A (search-first pattern) for flexibility with diverse LDAP schemas

**Impact:** HIGH - affects I2-I4 implementation, especially `bind()` and `getUserGroups()` method signatures

---

### Q-010-12: TLS/StartTLS Configuration Clarity

**Question:** Does `LDAP_USE_TLS=true` cover both LDAPS (port 636) and StartTLS (port 389), or do we need separate configuration?

- **Option A (Recommended):** Single `LDAP_USE_TLS` flag, protocol determined by port
  - `LDAP_USE_TLS=true` + `LDAP_PORT=636` → LDAPS (SSL/TLS from start)
  - `LDAP_USE_TLS=true` + `LDAP_PORT=389` → StartTLS (upgrade connection)
  - `LDAP_USE_TLS=false` → plaintext (dev only)
  - Document both scenarios in .env.example
  
- **Option B:** Separate flags for LDAPS and StartTLS
  - `LDAP_USE_LDAPS=true` for port 636
  - `LDAP_USE_STARTTLS=true` for port 389
  - More explicit configuration
  - More environment variables

**Pros/Cons:**
- **A:** Simpler configuration, fewer env vars; requires clear documentation
- **B:** More explicit; more complex, more env vars

**Impact:** MEDIUM - affects I1 configuration, I2 TLS implementation, and documentation

---

### ~~Q-010-06: Configuration Method~~ ✅ RESOLVED

**Decision:** Option A - Environment variables only
**Rationale:** LDAP is an expert/power-user setting; .env configuration is appropriate and avoids database complexity.
**Updated in spec:** All configuration options use .env variables, NFR-010-01

---

### ~~Q-010-05: Password Storage~~ ✅ RESOLVED

**Decision:** Option A - Don't store LDAP passwords
**Rationale:** Most secure approach; authenticate only against LDAP server without password duplication.
**Updated in spec:** FR-010-01, authentication flow, security model

---

### ~~Q-010-04: User Attribute Mapping~~ ✅ RESOLVED

**Decision:** Option C - Defaults with optional override via .env
**Rationale:** Provides sensible defaults (uid→username, mail→email, displayName→display_name) with .env configuration for LDAP schemas that differ.
**Updated in spec:** FR-010-02, attribute mapping configuration

---

### ~~Q-010-03: LDAP Group Mapping~~ ✅ RESOLVED

**Decision:** Option B - Map LDAP groups to Lychee roles (admin/user)
**Rationale:** Allows admin role assignment via LDAP groups; provides automatic role sync without complex user group management.
**Updated in spec:** FR-010-03, role mapping configuration

---

### ~~Q-010-02: User Provisioning~~ ✅ RESOLVED

**Decision:** Option C - User provisioning configurable via .env
**Rationale:** Flexibility for different deployment scenarios; allows auto-create or pre-existing-only mode via configuration.
**Updated in spec:** FR-010-04, user provisioning behavior

---

### ~~Q-010-01: LDAP Authentication Method~~ ✅ RESOLVED

**Decision:** Option C - Both basic auth and LDAP independently configurable via .env
**Rationale:** Maximum flexibility; allows deployments to use LDAP-only, basic-only, or both. LDAP enablement controlled by .env variables.
**Updated in spec:** FR-010-05, authentication method selection

---

### ~~Q-009-06: NULLS LAST Cross-Database Strategy~~ ✅ RESOLVED

**Decision:** Simple indexed ORDER BY with COALESCE pattern for fastest performance
**Rationale:** User specified "fastest ordering possible with indexing." Using `COALESCE(rating_avg, -1) DESC` allows the query to use the index on `rating_avg` efficiently across all databases. Since ratings are always positive (1-5), -1 as sentinel value is safe and pushes NULLs to the end.
**Updated in spec:** FR-009-02, sorting strategy, SortingDecorator implementation

---

### ~~Q-009-01: Average Rating Storage Strategy~~ ✅ RESOLVED

**Decision:** Option B - Add denormalized rating_avg column to photos table
**Rationale:** Fast indexed sorting with simple ORDER BY. Application logic will keep it in sync when ratings are updated (same transaction as rating_sum/rating_count updates).
**Updated in spec:** FR-009-01, DO-009-01, migration strategy

---

### ~~Q-009-02: Rating Smart Album Threshold Logic~~ ✅ RESOLVED

**Decision:** Option C - Hybrid (threshold for 3★+, exact for 1★-2★)
**Rationale:** Matches user's explicit statement that "3_stars album will contain all photos rated 3 stars or above." Low ratings (1★, 2★) use exact buckets so photos only appear in one album; high ratings (3★+) use threshold for cumulative view.
**Updated in spec:** FR-009-03 through FR-009-08, smart album filtering logic

---

### ~~Q-009-03: Best Pictures Cutoff Behavior~~ ✅ RESOLVED

**Decision:** Option B - Top N by rating, include ties
**Rationale:** Fair behavior that doesn't arbitrarily exclude photos with the same rating as the Nth photo. May show more than N photos if ties exist, but ensures no photo is unfairly excluded.
**Updated in spec:** FR-009-09, Best Pictures smart album logic

---

### ~~Q-009-04: Smart Album Sorting Default~~ ✅ RESOLVED

**Decision:** Custom - Rating smart albums and Best Pictures sorted by rating DESC
**Rationale:** Shows highest-rated photos first, which is the natural expectation for rating-based albums.
**Updated in spec:** FR-009-10, NFR-009-03

---

### ~~Q-008-01: User Preference Storage Location~~ ✅ RESOLVED

**Decision:** Option A - New column in users table
**Rationale:** Follows existing Lychee pattern (user attributes in users table), simple implementation with single query, no new tables needed.
**Updated in spec:** FR-008-02, COL-008-01, migration strategy

---

### ~~Q-008-02: Smart Albums in Tabbed View~~ ✅ RESOLVED

**Decision:** Option D - Show above tabs (outside tab context)
**Rationale:** Smart albums span all content (photos from both owned and shared albums), so they should be displayed above the tab bar and remain always visible regardless of selected tab.
**Updated in spec:** UI mockups, FR-008-06, FR-008-07

---

### ~~Q-008-03: Tab Visibility When Empty~~ ✅ RESOLVED

**Decision:** Option A - Hide empty tabs
**Rationale:** Cleaner UX - if "Shared with Me" has no albums, don't show tab bar at all (behave like SHOW mode). Simpler for users with no shared albums.
**Updated in spec:** S-008-08, UI-008-02

---

---

### ~~Q-007-01: Pagination Strategy (Offset vs Cursor) and Page Size Configuration~~ ✅ RESOLVED

**Decision:** Option A - Offset-based pagination with config table page size
**Rationale:** Simple Laravel pagination pattern with standard LIMIT/OFFSET, easy navigation to specific pages, admin-configurable page sizes via config table. Performance acceptable for expected album sizes.
**Updated in spec:** FR-007-01 through FR-007-06, NFR-007-01, NFR-007-05, DO-007-01

---

### ~~Q-007-02: API Endpoint Design (New Endpoints vs Modify Existing)~~ ✅ RESOLVED

**Decision:** Option B - New paginated endpoints (`/Album/{id}/head`, `/Album/{id}/albums`, `/Album/{id}/photos`)
**Rationale:** Clear separation of concerns, existing `/Album` endpoint unchanged for backward compatibility (avoiding test changes), consistent response structure per endpoint. Code duplication acceptable to minimize refactoring risk.
**Updated in spec:** FR-007-01, FR-007-02, FR-007-03, FR-007-12, NFR-007-04, NFR-007-06, API-007-01 through API-007-05

---

### ~~Q-007-03: Frontend Loading Strategy (Load-More vs Page Navigation)~~ ✅ RESOLVED

**Decision:** Configurable with infinite scroll as default
**Rationale:** User specified configurable UI modes: "infinite_scroll" (default), "load_more_button", "page_navigation". Infinite scroll provides smoothest UX for photo galleries. First page always loaded automatically, subsequent pages on demand based on UI mode.
**Updated in spec:** FR-007-07, FR-007-08, FR-007-09, FR-007-10, DO-007-02, UI mockups

---

### ~~Q-007-04: Config Key Naming and Default Values~~ ✅ RESOLVED

**Decision:** Option C - Multiple granular configs
**Rationale:** User specified: `albums_per_page` (default 30), `photos_per_page` (default 100), Flexible tuning for different resource types with appropriate defaults based on typical usage patterns.
**Updated in spec:** FR-007-06, NFR-007-05, DO-007-01

---

### ~~Q-007-05: Refactoring Scope (Extract Album/Photo Fetching Logic)~~ ✅ RESOLVED

**Decision:** Option B - Repository pattern methods, code duplication acceptable
**Rationale:** User directive to avoid extensive refactoring, prioritize backward compatibility and minimal test changes. New endpoints can duplicate logic from existing implementation. Repository pattern methods for data access without extracting to separate service classes.
**Updated in spec:** NFR-007-06, Goals section, Non-Goals section

---

### ~~Q-007-06: Backward Compatibility Strategy for Existing Clients~~ ✅ RESOLVED

**Decision:** New endpoints default page=1, existing `/Album` endpoint unchanged
**Rationale:** User specified creating new endpoints only. Legacy `/Album?album_id=X` endpoint remains unchanged returning full data. New endpoints (`/Album/{id}/albums`, `/Album/{id}/photos`) default to page 1 if `?page=` parameter absent (not "return all").
**Updated in spec:** FR-007-11, FR-007-12, API-007-02, API-007-03, API-007-04

---

### ~~Q-006-01: Filter UI Control Design and Interaction Pattern~~ ✅ RESOLVED

**Decision:** Option D - Hover star list with minimum threshold filtering and toggle-off
**Rationale:** User specified custom interaction: Display 5 hoverable stars. Empty stars = no filtering. Click on star N = show photos with rating ≥ N (minimum threshold). Click same star again = remove filtering. Combines visual clarity of inline stars with flexible threshold filtering.
**Updated in spec:** FR-006-01, FR-006-02, FR-006-03, UI mockup section

---

### ~~Q-006-02: Filter Behavior for Unrated Photos~~ ✅ RESOLVED

**Decision:** Addressed by Q-006-01 decision
**Rationale:** Minimum threshold filtering (≥ N stars) inherently excludes unrated photos (which have no rating value). Empty stars (no filter) shows all photos including unrated.
**Updated in spec:** FR-006-02, filtering logic section

---

### ~~Q-006-03: Filter State Persistence Strategy~~ ✅ RESOLVED

**Decision:** Custom - State store persistence (like NSFW visibility)
**Rationale:** User specified to keep selection in state store, similar to existing NSFW visibility pattern. State persists during session but managed by Pinia store, not localStorage (follows existing Lychee patterns for view state).
**Updated in spec:** FR-006-04, NFR-006-01

---

### ~~Q-006-04: Multi-Rating Filter Support (AND vs OR)~~ ✅ RESOLVED

**Decision:** Option C - Range filter (minimum threshold) as explained in Q-006-01
**Rationale:** User clarified in Q-006-01 that clicking star N shows photos with rating ≥ N (3+ stars shows 3, 4, 5 star photos). Simple single-selection UI with flexible filtering capability.
**Updated in spec:** FR-006-01, FR-006-02, filtering algorithm section

---

### ~~Q-005-01: List View Layout Structure and Information Display~~ ✅ RESOLVED

**Decision:** Option A - Windows Details View Pattern
**Rationale:** Familiar file manager pattern with horizontal row layout: `[Thumb 64px] [Album Name - Full] [X photos] [Y sub-albums]`. Scannable, information-dense, shows full untruncated album names.
**Updated in spec:** FR-005-01, FR-005-02, UI mockup section

---

### ~~Q-005-02: Toggle Control Placement and Styling~~ ✅ RESOLVED

**Decision:** Custom - AlbumHero.vue icon row (same line as statistics/download toggles)
**Rationale:** User specified placement on the same line as the statistics and download toggle buttons in AlbumHero.vue (line 33, flex-row-reverse container). Follows existing icon pattern with px-3 spacing and hover animations.
**Updated in spec:** FR-005-03, UI implementation section

---

### ~~Q-005-03: View Preference Persistence Strategy~~ ✅ RESOLVED

**Decision:** Option B - LocalStorage/session-only (no backend)
**Rationale:** Simple implementation, no backend changes needed, fast toggle response. User preference stored in browser localStorage per-device.
**Updated in spec:** FR-005-04, NFR-005-01

---

### ~~Q-003-09: Multi-user Cover Selection Strategy for computed_cover_id~~ ✅ RESOLVED

**Decision:** Option D - Store dual cover IDs with privilege-based selection (`auto_cover_id_max_privilege` and `auto_cover_id_least_privilege`)
**Rationale:** Balances performance (pre-computation) with security (no photo leakage). Two cover IDs stored per album: one for admin/owner view (max privilege), one for public view (least privilege). Display logic selects appropriate cover based on user permissions at query time (simple column read, no subquery). Simple schema (2 columns vs. per-user table), guaranteed safe (least-privilege cover never leaks private photos), good UX (admin/owner sees best possible cover).
**Updated in spec:** FR-003-01, FR-003-02, FR-003-04, FR-003-07, NFR-003-05, DO-003-03, DO-003-04, Migration Strategy, Cover Selection Logic appendix
**ADR:** ADR-0003-album-computed-fields-precomputation.md (to be updated with Q-003-09 resolution)

---

### ~~Q-003-01: Recomputation Job Queue Priority~~ ✅ RESOLVED

**Decision:** Option A - Use default queue, rely on worker scaling
**Rationale:** Simpler configuration, standard Laravel pattern, natural backpressure signaling. Operators scale worker count to meet 30-second consistency target.
**Updated in spec:** FR-003-02, JOB-003-01

---

### ~~Q-003-02: Backfill Execution Strategy During Migration~~ ✅ RESOLVED

**Decision:** Option A - Manual trigger after migration (with `lychee:` prefix requirement)
**Rationale:** Operator controls timing during maintenance window, migration completes quickly, aligns with dual-read fallback pattern. All Lychee commands use `lychee:` namespace.
**Updated in spec:** FR-003-06, CLI-003-01, Migration Strategy appendix
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-03: Concurrent Album Mutation Deduplication~~ ✅ RESOLVED

**Decision:** Option A - Laravel WithoutOverlapping middleware
**Rationale:** Built-in Laravel feature (same as Feature 002 Q-002-03), prevents wasted work, automatic lock release, simple implementation.
**Updated in spec:** FR-003-02, JOB-003-01
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-04: Cover Selection Race Condition Handling~~ ✅ RESOLVED

**Decision:** Option A - Foreign key ON DELETE SET NULL (already in spec)
**Rationale:** Database handles automatically, simple, eventual consistency. Photo deletion events trigger recomputation for parent albums.
**Updated in spec:** FR-003-02 (added photo deletion event trigger), Migration Strategy appendix (FK constraint confirmed)

---

### ~~Q-003-05: Propagation Chain Failure Handling~~ ✅ RESOLVED

**Decision:** Option A - Stop propagation, log error, manual recovery
**Rationale:** Prevents cascading errors, clear failure boundary, operator can investigate root cause before retrying via `lychee:recompute-album-stats`.
**Updated in spec:** FR-003-02, CLI-003-02
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-06: Soft-Deleted Photo Exclusion from Computations~~ ✅ RESOLVED

**Decision:** N/A - Lychee does not use soft deletes
**Rationale:** Per user clarification, Lychee does not implement soft delete pattern for photos. Hard deletes only.
**Updated in spec:** FR-003-02 (removed soft-delete references)

---

### ~~Q-003-07: NULL taken_at Handling in Min/Max Calculations~~ ✅ RESOLVED

**Decision:** Option A - Ignore NULL taken_at, use SQL MIN/MAX directly
**Rationale:** Mirrors existing AlbumBuilder.php behavior (lines 111, 125). SQL MIN/MAX ignores NULLs by default. Semantically correct (taken_at unknown = exclude from range).
**Updated in spec:** FR-003-02 validation path

---

### ~~Q-003-08: Migration Rollback Strategy for Multi-Phase Deployment~~ ✅ RESOLVED

**Decision:** Option B - Full rollback with down() migration
**Rationale:** Clean schema restoration, simple one-command rollback. Trade-off: data loss if backfill ran, but values can be regenerated. Critical constraint: do NOT rollback after Phase 4 cleanup.
**Updated in spec:** FR-003-06, Migration Strategy appendix (new Rollback Strategy section)
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-002-01: Worker Auto-Restart Queue Priority~~ ✅ RESOLVED

**Decision:** Option A - Support multiple queue workers with priority via QUEUE_NAMES environment variable
**Rationale:** Allows time-sensitive jobs to be prioritized, standard Laravel pattern, operator flexibility.
**Updated in spec:** FR-002-02, DO-002-02, CLI-002-01, Spec DSL, Queue Connection Configuration appendix

---

### ~~Q-002-02: Worker Max-Time Configurability~~ ✅ RESOLVED

**Decision:** Option A - Configurable with sensible default via WORKER_MAX_TIME environment variable
**Rationale:** Operators can tune for their workload, no code changes needed to adjust restart interval.
**Updated in spec:** FR-002-02, DO-002-03, CLI-002-01, Spec DSL, Queue Connection Configuration appendix

---

### ~~Q-002-03: Job Deduplication for Concurrent Mutations~~ ✅ RESOLVED

**Decision:** Option A - Laravel job middleware with deduplication using WithoutOverlapping
**Rationale:** Built-in Laravel feature, prevents wasted work, automatic lock release.
**Updated in spec:** NFR-002-05, Documentation Deliverables

---

### ~~Q-002-04: Worker Healthcheck Failure Behavior~~ ✅ RESOLVED

**Decision:** Option B - Healthcheck tracks restart count, fail after 10 restarts in 5 minutes
**Rationale:** Orchestrator can restart container if worker is fundamentally broken, prevents infinite crash loops.
**Updated in spec:** FR-002-05

---

### ~~Q001-07: Statistics Record Creation Strategy~~ ✅ RESOLVED

**Decision:** Option A - firstOrCreate in transaction
**Rationale:** Atomic operation with no race conditions, Laravel handles duplicate creation attempts automatically, simple implementation.
**Updated in spec:** Implementation plan I5

---

### ~~Q001-08: Transaction Rollback Error Handling~~ ✅ RESOLVED

**Decision:** Option B - 409 Conflict for transaction errors
**Rationale:** More semantic HTTP status, indicates temporary issue that suggests retry, clearer to frontend.
**Updated in spec:** Implementation plan I5, I10

---

### ~~Q001-09: N+1 Query Performance for user_rating~~ ✅ RESOLVED

**Decision:** Option A - Eager load with closure in controller
**Rationale:** Standard Laravel pattern, single additional query for all photos, no global scope side effects.
**Updated in spec:** Implementation plan I6

---

### ~~Q001-10: Concurrent Update Debouncing (Rapid Clicks)~~ ✅ RESOLVED

**Decision:** Option A - Disable stars during API call
**Rationale:** Simple implementation, prevents concurrent requests, clear visual feedback with loading state.
**Updated in spec:** Implementation plan I8, I9a, I9c

---

### ~~Q001-11: Metrics Disabled Behavior (Can Still Rate?)~~ ✅ RESOLVED

**Decision:** Option C - Admin setting controls independently
**Rationale:** Granular control allows enabling rating without showing aggregates, future-proof configuration.
**Updated in spec:** New config setting needed (separate `ratings_enabled` from `metrics_enabled`)

---

### ~~Q001-12: Rating Display When Metrics Disabled~~ ✅ RESOLVED

**Decision:** Option B - Hide all rating data when metrics disabled
**Rationale:** Fully consistent with metrics disabled setting, simplest implementation, respects admin preference.
**Updated in spec:** UI components conditional rendering

---

### ~~Q001-13: Half-Star Display for Fractional Averages~~ ✅ RESOLVED

**Decision:** Option B - Half-star display using PrimeVue icons
**Rationale:** PrimeVue provides pi-star, pi-star-fill, pi-star-half, pi-star-half-fill icons. More precise visual representation, common rating pattern.
**Updated in spec:** UI mockups, component implementation uses PrimeVue star icons

---

### ~~Q001-14: Overlay Persistence on Active Interaction~~ ✅ RESOLVED

**Decision:** Option A - Persist while loading, then restart auto-hide timer
**Rationale:** User sees confirmation (success toast + updated rating), natural interaction flow.
**Updated in spec:** Implementation plan I9c, PhotoRatingOverlay behavior

---

### ~~Q001-15: Rating Tooltip/Label Clarity~~ ✅ RESOLVED

**Decision:** Option C - No labels/tooltips (stars are self-evident)
**Rationale:** Cleanest UI, stars are universal rating symbol, keeps overlays compact.
**Updated in spec:** UI components (no tooltip implementation needed)

---

### ~~Q001-16: Accessibility (Keyboard Navigation, ARIA)~~ ✅ RESOLVED

**Decision:** Option C - Defer to post-MVP
**Rationale:** Ship faster with basic implementation, gather user feedback first, can enhance accessibility later.
**Updated in spec:** Out of scope (deferred enhancement)

---

### ~~Q001-17: Optimistic UI Updates vs Server Confirmation~~ ✅ RESOLVED

**Decision:** Option A - Wait for server confirmation
**Rationale:** Always shows accurate server state, clear error handling, no phantom updates.
**Updated in spec:** Implementation plan I8, I9a, I9c (loading state pattern)

---

### ~~Q001-18: Rating Count Threshold for Display~~ ✅ RESOLVED

**Decision:** Option A - Always show rating, regardless of count
**Rationale:** Transparent, simpler logic, users can judge significance from count displayed.
**Updated in spec:** UI components (no threshold logic needed)

---

### ~~Q001-19: Telemetry Event Granularity~~ ✅ RESOLVED

**Decision:** No telemetry events / analytics
**Rationale:** Feature does not include telemetry or analytics tracking.
**Updated in spec:** Remove telemetry events from FR-001-01, FR-001-02, FR-001-03

---

### ~~Q001-20: Rating Analytics/Trending Features~~ ✅ RESOLVED

**Decision:** Option B - Implement minimally for current scope
**Rationale:** Follows YAGNI principle, simpler initial implementation, faster to ship.
**Updated in spec:** Out of scope (no future analytics preparation)

---

### ~~Q001-21: Album Aggregate Rating Display~~ ✅ RESOLVED

**Decision:** Option A - Defer to future feature
**Rationale:** Keeps current feature focused, can design properly later with user feedback on photo ratings.
**Updated in spec:** Out of scope, potential future Feature 00X

---

### ~~Q001-22: Rating Export in Photo Backup~~ ✅ RESOLVED

**Decision:** Option C - No export (ratings are ephemeral/server-side only)
**Rationale:** Simpler export logic, smaller export files.
**Updated in spec:** Out of scope (no export functionality)

---

### ~~Q001-23: Rating Notification to Photo Owner~~ ✅ RESOLVED

**Decision:** Option A - Defer to future feature (notifications system)
**Rationale:** Keeps feature scope focused, requires notifications infrastructure that may not exist yet.
**Updated in spec:** Out of scope (deferred to future notifications feature)

---

### ~~Q001-24: Statistics Recalculation Artisan Command~~ ✅ RESOLVED

**Decision:** Option B - No command, rely on transaction integrity
**Rationale:** Trust atomic transactions to maintain consistency, simpler implementation.
**Updated in spec:** Out of scope (no artisan command)

---

### ~~Q001-25: Migration Strategy for Existing Installations~~ ✅ RESOLVED

**Decision:** Option A - Migration adds columns with defaults, no backfill
**Rationale:** Clean state (accurate: no ratings yet), fast migration, no assumptions about historical data.
**Updated in spec:** Implementation plan I1 (migrations with default values)

---

### ~~Q001-05: Authorization Model for Rating~~ ✅ RESOLVED

**Decision:** Option B - Read access (anyone who can view can rate)
**Rationale:** Follows standard rating system patterns. Rating is a lightweight engagement action similar to favoriting, not a privileged edit operation. Makes ratings more accessible and useful.
**Updated in spec:** FR-001-01, NFR-001-04

---

### ~~Q001-06: Rating Removal HTTP Status Code~~ ✅ RESOLVED

**Decision:** 200 OK (idempotent behavior)
**Rationale:** Removing a non-existent rating is a no-op and should return success (200 OK) rather than 404 error. This makes the endpoint idempotent and simpler to use.
**Updated in spec:** FR-001-02

---

### ~~Q001-01: Full-size Photo Overlay Positioning~~ ✅ RESOLVED

**Decision:** Option A - Bottom-center
**Rationale:** Centered position is more discoverable and doesn't compete with Dock buttons. Symmetrical with metadata overlay below.
**Updated in spec:** FR-001-10, UI mockup section 2, implementation plan I9c/I9d

---

### ~~Q001-02: Auto-hide Timer Duration~~ ✅ RESOLVED

**Decision:** Option A - 3 seconds
**Rationale:** Standard UX pattern, balanced duration (not too fast, not too slow).
**Updated in spec:** FR-001-10, UI mockup section 2, implementation plan I9c

---

### ~~Q001-03: Rating Removal Button Placement~~ ✅ RESOLVED

**Decision:** Option A - Inline [0] button
**Rationale:** Consistent button pattern, simple implementation, shown as "×" or "Remove" for clarity.
**Updated in spec:** FR-001-09, UI mockup section 1, implementation plan I9a

---

### ~~Q001-04: Overlay Visibility on Mobile Devices~~ ✅ RESOLVED

**Decision:** Option A - Details drawer only on mobile
**Rationale:** Follows existing Lychee pattern (overlays are desktop-only), simple and consistent experience.
**Updated in spec:** FR-001-09, FR-001-10, UI mockup sections 1-2, implementation plan I9a/I9c

---

### ~~Q001-01: Full-size Photo Overlay Positioning~~ (ARCHIVED)

**Context:** When hovering over the lower area of a full-size photo, the rating overlay can be positioned in different locations. The spec currently presents two options.

**Question:** Which positioning approach should we use for the full-size photo rating overlay?

**Options (ordered by preference):**

**Option A: Bottom-center (Recommended)**
- **Position:** Horizontally centered, positioned above the metadata overlay (title/EXIF)
- **Layout:** `★★★★☆ 4.2 (15) Your rating: ★★★★☆ [0][1][2][3][4][5]`
- **Pros:**
  - Centered position is intuitive and balanced
  - Doesn't compete with Dock buttons for space
  - More visible and discoverable
  - Symmetrical with metadata overlay below it
- **Cons:**
  - May obstruct central portion of photo
  - Wider horizontal space required

**Option B: Bottom-right (near Dock buttons)**
- **Position:** Bottom-right corner, adjacent to existing Dock action buttons
- **Layout:** Compact vertical or horizontal near Dock
- **Pros:**
  - Groups with other photo actions (Dock buttons)
  - Consistent with action button placement pattern
  - Less obstruction of photo center
- **Cons:**
  - May crowd the Dock button area
  - Less discoverable (user might not look at corner)
  - Asymmetrical with metadata overlay (which is bottom-left)

**Impact:** Medium - affects UX discoverability and visual balance, but either option is functional.

---

### Q001-02: Auto-hide Timer Duration

**Context:** The full-size photo rating overlay auto-hides after a period of inactivity to avoid obstructing the photo view.

**Question:** What duration should the auto-hide timer be set to?

**Options (ordered by preference):**

**Option A: 3 seconds (Recommended)**
- **Duration:** Overlay fades out after 3 seconds of no mouse movement
- **Pros:**
  - Short enough to not be annoying
  - Long enough for user to read and interact
  - Common UX pattern for transient overlays
- **Cons:**
  - May feel rushed for slower users
  - Might hide before user finishes reading

**Option B: 5 seconds**
- **Duration:** Overlay fades out after 5 seconds of no mouse movement
- **Pros:**
  - More time for users to read and decide
  - Less pressure to act quickly
- **Cons:**
  - Longer obstruction of photo view
  - May feel sluggish

**Option C: Configurable (with 3s default)**
- **Duration:** User setting for auto-hide duration (1-10 seconds)
- **Pros:**
  - User preference accommodated
  - Accessible for users with different needs
- **Cons:**
  - Added complexity (settings UI, store management)
  - Deferred to post-MVP

**Option D: No auto-hide (manual dismiss only)**
- **Duration:** Overlay persists until user moves mouse away from lower area
- **Pros:**
  - No time pressure
  - User controls when it disappears
- **Cons:**
  - Overlay may linger and obstruct photo
  - Less elegant UX

**Impact:** Medium - affects user experience and perception of polish, but any reasonable duration works.

---

### Q001-03: Rating Removal Button Placement

**Context:** Users can remove their rating by selecting "0". The UI design needs to clarify how this is presented.

**Question:** How should the "remove rating" (0) option be presented in the UI?

**Options (ordered by preference):**

**Option A: Inline button [0] before stars (Recommended)**
- **Layout:** `[0] [1] [2] [3] [4] [5]` with 0 shown as "×" or "Remove"
- **Pros:**
  - Consistent with the button pattern
  - Clear that 0 is a special action (remove)
  - Simple implementation (same component pattern)
- **Cons:**
  - May be confused with a rating of zero
  - Takes up space in compact overlays

**Option B: Separate "Clear rating" button**
- **Layout:** `[1] [2] [3] [4] [5] [Clear ×]`
- **Pros:**
  - Visually distinct from rating action
  - Clearer intent (remove vs rate)
  - Reduces accidental removal
- **Cons:**
  - Additional UI element
  - Less compact for overlays

**Option C: Right-click or long-press to remove**
- **Interaction:** Click star to rate, right-click/long-press to remove
- **Pros:**
  - No additional UI needed
  - Clean visual design
- **Cons:**
  - Not discoverable (hidden interaction)
  - Accessibility concerns
  - Mobile long-press may be awkward

**Impact:** Low - all options are functional, mainly affects visual design and user discovery.

---

### Q001-04: Overlay Visibility on Mobile Devices

**Context:** The current spec hides rating overlays on mobile (below md: breakpoint) because hover interactions don't work well on touch devices. Users can still rate via the details drawer.

**Question:** Should we provide any rating interaction on mobile beyond the details drawer?

**Options (ordered by preference):**

**Option A: Details drawer only on mobile (Recommended)**
- **Behavior:** No overlays on mobile, rating only via PhotoDetails drawer
- **Pros:**
  - Simple, consistent experience
  - No awkward touch interaction patterns needed
  - Cleaner thumbnail grid (no overlay clutter)
  - Follows existing Lychee mobile pattern (overlays are desktop-only)
- **Cons:**
  - Requires opening details drawer to rate
  - Less convenient for quick ratings

**Option B: Tap-to-show overlay on thumbnails**
- **Behavior:** Single tap shows overlay (without opening photo), tap star to rate, tap outside to dismiss
- **Pros:**
  - Quick access to rating on mobile
  - No need to open details drawer
- **Cons:**
  - Conflicts with tap-to-open-photo gesture
  - Requires double-tap or long-press (poor UX)
  - Added complexity in touch event handling

**Option C: Always-visible compact rating on thumbnails (mobile)**
- **Behavior:** Small rating display (stars or number) always visible on thumbnails on mobile
- **Pros:**
  - Ratings always visible at a glance
  - Tap star to rate directly
- **Cons:**
  - Clutters thumbnail grid
  - Inconsistent with desktop (hover-only)
  - May obscure thumbnail image

**Impact:** Medium - affects mobile user experience, but details drawer provides full fallback.

---

### Q001-07: Statistics Record Creation Strategy

**Context:** When a user rates a photo for the first time, the `photo_statistics` record may not exist yet. The implementation must handle this gracefully.

**Question:** How should we ensure the statistics record exists when creating the first rating?

**Options (ordered by preference):**

**Option A: firstOrCreate in transaction (Recommended)**
- **Approach:** Use `PhotoStatistics::firstOrCreate(['photo_id' => $photo_id], [...defaults])` within the transaction
- **Pros:**
  - Atomic operation, no race condition
  - Laravel handles duplicate creation attempts
  - Simple implementation
- **Cons:**
  - May create statistics record even if rating fails validation
  - Extra query overhead

**Option B: Check existence before rating**
- **Approach:** Check if statistics exists, create if missing before rating transaction
- **Pros:**
  - Explicit control flow
  - Clear error handling
- **Cons:**
  - Two separate operations (not atomic)
  - Race condition if two users rate simultaneously
  - More complex code

**Option C: Database trigger**
- **Approach:** Create database trigger to auto-create statistics record on photo insert
- **Pros:**
  - Guarantees statistics always exists
  - No application logic needed
- **Cons:**
  - Adds database complexity
  - Migration complexity for existing photos
  - Not Lychee's pattern (application-level logic preferred)

**Impact:** High - affects data integrity and implementation complexity

---

### Q001-08: Transaction Rollback Error Handling

**Context:** When a database transaction fails (e.g., deadlock, constraint violation), the spec doesn't clarify what error should be returned to the user.

**Question:** How should we handle transaction failures in the rating endpoint?

**Options (ordered by preference):**

**Option A: 500 Internal Server Error with generic message (Recommended)**
- **Response:** HTTP 500, `{"message": "Unable to save rating. Please try again."}`
- **Pros:**
  - Doesn't expose database implementation details
  - Standard error handling pattern
  - User-friendly message
- **Cons:**
  - Less specific for debugging
  - May retry without fixing underlying issue

**Option B: 409 Conflict for transaction errors**
- **Response:** HTTP 409, `{"message": "Rating conflict. Please refresh and try again."}`
- **Pros:**
  - More semantic (conflict suggests retry)
  - Indicates temporary issue
- **Cons:**
  - 409 typically used for optimistic locking conflicts
  - May confuse frontend logic

**Option C: Log error, retry transaction automatically**
- **Approach:** Catch deadlock exceptions, retry transaction 2-3 times before failing
- **Pros:**
  - Transparent to user
  - Handles temporary deadlocks gracefully
- **Cons:**
  - Added complexity
  - May mask underlying database issues
  - Increased latency

**Impact:** High - affects error handling strategy and user experience

---

### Q001-09: N+1 Query Performance for user_rating

**Context:** PhotoResource includes `user_rating` field by querying `$this->ratings()->where('user_id', auth()->id())->value('rating')`. When loading many photos (album grid), this creates N+1 query problem.

**Question:** How should we optimize user_rating loading for photo collections?

**Options (ordered by preference):**

**Option A: Eager load with closure in controller (Recommended)**
- **Implementation:**
  ```php
  $photos->load(['ratings' => fn($q) => $q->where('user_id', auth()->id())]);
  ```
- **Pros:**
  - Single additional query for all photos
  - Standard Laravel pattern
  - No PhotoResource changes needed
- **Cons:**
  - Must remember to eager load in every controller method
  - Easy to forget and create N+1

**Option B: Global scope on Photo model**
- **Implementation:** Add global scope to always eager load current user's rating
- **Pros:**
  - Automatic, no controller changes needed
  - Consistent across all queries
- **Cons:**
  - Always loads ratings even when not needed
  - Performance overhead for unauthenticated users
  - Global scopes can have unexpected side effects

**Option C: Separate endpoint for ratings**
- **Implementation:** Load photos without ratings, fetch ratings separately via `/api/photos/{ids}/ratings`
- **Pros:**
  - Decoupled data loading
  - Can defer ratings until needed
- **Cons:**
  - Two API calls required
  - More complex frontend logic
  - Increased latency

**Impact:** High - affects performance for album views with many photos

---

### Q001-10: Concurrent Update Debouncing (Rapid Clicks)

**Context:** If a user rapidly clicks different star values, multiple concurrent API requests may be sent. This could cause race conditions or display inconsistencies.

**Question:** Should we debounce or throttle rapid rating changes in the UI?

**Options (ordered by preference):**

**Option A: Disable stars during API call (Recommended)**
- **Behavior:** Set `loading = true`, disable all star buttons until API returns
- **Pros:**
  - Simple implementation
  - Prevents concurrent requests
  - Clear visual feedback (loading state)
- **Cons:**
  - User must wait for each rating to complete
  - Slower if user wants to correct mistake

**Option B: Debounce rating submissions (300ms)**
- **Behavior:** Wait 300ms after last click before sending API request, cancel pending requests
- **Pros:**
  - Allows user to change mind quickly
  - Reduces API calls for rapid clicks
- **Cons:**
  - Delayed feedback
  - More complex implementation (cancel logic)
  - May feel sluggish

**Option C: Queue requests, send last value only**
- **Behavior:** Queue rating changes, send only most recent value when previous request completes
- **Pros:**
  - Always saves final user choice
  - No wasted API calls
- **Cons:**
  - Complex state management
  - User may see intermediate states that don't persist

**Impact:** High - affects UX responsiveness and data consistency

---

### Q001-11: Metrics Disabled Behavior (Can Still Rate?)

**Context:** The spec says rating data is hidden when `metrics_enabled` config is false, but doesn't clarify if users can still submit ratings when metrics are disabled.

**Question:** When metrics are disabled, should users still be able to rate photos?

**Options (ordered by preference):**

**Option A: Yes, rating functionality always available (Recommended)**
- **Behavior:** Users can rate, but aggregates/counts are hidden in UI. Data is still stored.
- **Pros:**
  - Consistent user experience
  - Data collection continues even if display is disabled
  - Easy to re-enable metrics later with existing data
- **Cons:**
  - May confuse users (why can I rate if I can't see ratings?)
  - Data stored but not shown

**Option B: No, disable rating when metrics disabled**
- **Behavior:** Hide all rating UI and disable `/Photo::rate` endpoint when metrics disabled
- **Pros:**
  - Consistent (if metrics off, ratings off)
  - Respects privacy/metrics setting fully
- **Cons:**
  - Loss of data collection
  - Hard to re-enable later (no historical data)
  - Inconsistent with favorites (favorites work when metrics disabled)

**Option C: Admin setting controls independently**
- **Behavior:** Separate `ratings_enabled` config independent of `metrics_enabled`
- **Pros:**
  - Granular control
  - Can enable rating without showing aggregates
- **Cons:**
  - More configuration complexity
  - May confuse admins

**Impact:** High - affects feature scope and user experience

---

### Q001-12: Rating Display When Metrics Disabled

**Context:** FR-001-04 says rating data is shown "when metrics are enabled," but spec doesn't clarify if user's own rating is shown when metrics are disabled.

**Question:** When metrics are disabled, should the UI show the user's own rating (even if aggregates are hidden)?

**Options (ordered by preference):**

**Option A: Show user's own rating regardless of metrics setting (Recommended)**
- **Behavior:** User sees their own rating stars highlighted, but no aggregate average/count
- **Pros:**
  - User feedback on their own action
  - Doesn't expose community metrics (privacy preserved)
  - Consistent with user-centric data (my data vs community data)
- **Cons:**
  - Slightly inconsistent with "metrics disabled" (rating is a metric)

**Option B: Hide all rating data when metrics disabled**
- **Behavior:** No rating display at all, including user's own
- **Pros:**
  - Fully consistent with metrics disabled
  - Simplest implementation
- **Cons:**
  - Poor UX (user can't see what they rated)
  - Feels broken ("I clicked 4 stars, where did it go?")

**Impact:** Medium - affects UX when metrics are disabled

---

### Q001-13: Half-Star Display for Fractional Averages

**Context:** Spec stores rating_avg as decimal(3,2), allowing fractional values like 4.33. UI mockups show full/empty stars only (no half-stars).

**Question:** Should we display half-stars for fractional average ratings?

**Options (ordered by preference):**

**Option A: Full stars only, round to nearest integer (Recommended)**
- **Display:** 4.33 avg → ★★★★☆ (4 stars), show "4.33" as text next to stars
- **Pros:**
  - Simpler UI implementation
  - Clear visual (full or empty)
  - Numeric value still shows precision
- **Cons:**
  - Visual representation less precise

**Option B: Half-star display for .25-.74 range**
- **Display:** 4.33 avg → ★★★★⯨ (4.5 stars visually), show "4.33" as text
- **Pros:**
  - More precise visual representation
  - Common rating pattern (Amazon, IMDb)
- **Cons:**
  - More complex implementation (half-star icon, rounding logic)
  - May not match user's mental model (users rate 1-5, not 1-10)

**Option C: Gradient fill for precise fractional display**
- **Display:** 4.33 avg → ★★★★⯨ (4th star 33% filled)
- **Pros:**
  - Exact visual representation
  - Visually interesting
- **Cons:**
  - Complex implementation (SVG/CSS gradients)
  - May be hard to read at small sizes
  - Uncommon pattern (users may not understand)

**Impact:** Medium - affects UI polish and clarity

---

### Q001-14: Overlay Persistence on Active Interaction

**Context:** PhotoRatingOverlay (full photo) auto-hides after 3 seconds of inactivity. Spec says "persists if mouse over overlay itself," but doesn't clarify behavior when user is actively clicking/interacting.

**Question:** Should the overlay stay visible while the user is actively interacting with the rating stars, even if they briefly move the mouse outside the overlay?

**Options (ordered by preference):**

**Option A: Persist while loading, then restart auto-hide timer (Recommended)**
- **Behavior:** After user clicks a star, overlay stays visible during API call (loading state), then restarts 3s auto-hide timer on success
- **Pros:**
  - User sees confirmation (success toast + updated rating)
  - Natural flow (interact → see result → overlay fades)
- **Cons:**
  - May stay visible longer than expected

**Option B: Auto-hide immediately after successful rating**
- **Behavior:** After rating succeeds, overlay fades out immediately (no 3s delay)
- **Pros:**
  - Faster cleanup after action
  - User sees toast notification for confirmation
- **Cons:**
  - Abrupt (overlay disappears right after click)
  - User may not see updated average

**Option C: Persist until mouse leaves lower area entirely**
- **Behavior:** Overlay stays visible as long as mouse is in lower 20-30% zone, regardless of timer
- **Pros:**
  - User has full control
  - Overlay available for multiple rating changes
- **Cons:**
  - May linger too long
  - Obstructs photo view longer

**Impact:** Medium - affects UX polish and expected behavior

---

### Q001-15: Rating Tooltip/Label Clarity (What Are Stars?)

**Context:** UI mockups don't show tooltips or ARIA labels explaining what the star rating means (1 = lowest, 5 = highest).

**Question:** Should we add tooltips/labels to explain the star rating scale?

**Options (ordered by preference):**

**Option A: Hover tooltips on star buttons (Recommended)**
- **Implementation:** Each star button shows tooltip: "1 star", "2 stars", ... "5 stars"
- **Pros:**
  - Self-explanatory on hover
  - Accessible (screen reader friendly with aria-label)
  - Doesn't clutter UI
- **Cons:**
  - Requires tooltip implementation
  - May be obvious to most users

**Option B: Label text: "Rate 1-5 stars"**
- **Implementation:** Static text label above star buttons
- **Pros:**
  - Always visible, no hover needed
  - Clear scale indication
- **Cons:**
  - Takes up space in compact overlays
  - May be redundant (stars are intuitive)

**Option C: No labels/tooltips (stars are self-evident)**
- **Implementation:** No additional labels, star icons only
- **Pros:**
  - Cleanest UI
  - Stars are universal rating symbol
- **Cons:**
  - Accessibility concerns (screen reader users)
  - New users may not understand scale

**Impact:** Medium - affects accessibility and UX clarity

---

### Q001-16: Accessibility (Keyboard Navigation, ARIA)

**Context:** Spec doesn't specify keyboard navigation or ARIA attributes for rating components.

**Question:** What accessibility features should be implemented for the rating UI?

**Options (ordered by preference):**

**Option A: Full WCAG 2.1 AA compliance (Recommended)**
- **Implementation:**
  - Keyboard navigation: Tab to focus rating, Arrow keys to select star, Enter/Space to rate
  - ARIA attributes: `role="radiogroup"`, `aria-label="Rate this photo"`, `aria-checked` on selected star
  - Focus indicators: Visible outline on focused star
  - Screen reader announcements: "4 stars selected, 15 total votes, average 4.2"
- **Pros:**
  - Fully accessible to all users
  - Meets legal/compliance requirements
  - Better UX for keyboard users
- **Cons:**
  - More implementation effort
  - Testing complexity

**Option B: Basic accessibility (tab focus, ARIA labels only)**
- **Implementation:** Tab to rating widget, click to rate, basic aria-labels
- **Pros:**
  - Simpler implementation
  - Covers most accessibility needs
- **Cons:**
  - Not fully keyboard navigable
  - May not meet WCAG AA

**Option C: Defer to post-MVP**
- **Decision:** Launch with basic implementation, enhance accessibility later
- **Pros:**
  - Faster to ship
  - Can gather user feedback first
- **Cons:**
  - Excludes users with disabilities
  - Harder to retrofit later
  - Potential compliance issues

**Impact:** Medium - affects accessibility and inclusivity

---

### Q001-17: Optimistic UI Updates vs Server Confirmation

**Context:** Spec doesn't clarify whether UI should update optimistically (immediately on click) or wait for server confirmation.

**Question:** Should the rating UI update optimistically or wait for API response?

**Options (ordered by preference):**

**Option A: Wait for server confirmation (Recommended)**
- **Behavior:** Show loading state on click, update UI only after API success
- **Pros:**
  - Always shows accurate server state
  - Clear error handling (revert on failure)
  - No phantom updates
- **Cons:**
  - Slower perceived responsiveness
  - Requires loading state UI

**Option B: Optimistic update, revert on error**
- **Behavior:** Update UI immediately on click, show error and revert if API fails
- **Pros:**
  - Instant feedback, feels faster
  - Better perceived performance
- **Cons:**
  - Complex state management (revert logic)
  - User may see incorrect state briefly
  - Confusing if network is slow and revert happens seconds later

**Option C: Hybrid (optimistic for user rating, wait for aggregate)**
- **Behavior:** Update user's star selection immediately, but wait for server to update average/count
- **Pros:**
  - Fast feedback for user action
  - Accurate aggregate display
- **Cons:**
  - Split state management
  - May show inconsistent state (user rating updated, aggregate unchanged)

**Impact:** Medium - affects perceived performance and UX

---

### Q001-18: Rating Count Threshold for Display

**Context:** Spec doesn't specify if ratings should be hidden when count is very low (e.g., 1-2 ratings may not be statistically meaningful).

**Question:** Should we hide average rating display until a minimum number of ratings exist?

**Options (ordered by preference):**

**Option A: Always show rating, regardless of count (Recommended)**
- **Display:** Show "★★★★★ 5.0 (1)" even for single rating
- **Pros:**
  - Transparent, shows all data
  - Simpler logic (no threshold)
  - Users can judge significance from count
- **Cons:**
  - Single ratings may be misleading (not representative)
  - May encourage rating manipulation

**Option B: Hide average until N >= 3 ratings**
- **Display:** Show "(3 ratings)" text only until 3+ ratings, then show average
- **Pros:**
  - More statistically meaningful average
  - Reduces impact of single outlier ratings
- **Cons:**
  - Hides data from users
  - Arbitrary threshold (why 3?)
  - Users may be confused why they can't see average after rating

**Option C: Show with disclaimer for low counts**
- **Display:** "★★★★★ 5.0 (1 rating)" with styling/tooltip: "Based on limited ratings"
- **Pros:**
  - Shows data with context
  - Users can make informed judgment
- **Cons:**
  - More UI complexity
  - May clutter compact overlays

**Impact:** Medium - affects data presentation and perceived trustworthiness

---

### Q001-19: Telemetry Event Granularity

**Context:** Spec defines three telemetry events (photo.rated, photo.rating_updated, photo.rating_removed). These events overlap (updating is also rating).

**Question:** Should we emit separate events for create vs update, or combine into one event?

**Options (ordered by preference):**

**Option A: Three separate events (as spec defines) (Recommended)**
- **Events:** `photo.rated` (new), `photo.rating_updated` (change), `photo.rating_removed` (delete)
- **Pros:**
  - Granular analytics (can track rating changes separately from new ratings)
  - Easier to query specific actions
- **Cons:**
  - More event types to maintain
  - Logic to determine which event to emit

**Option B: Single event with action field**
- **Event:** `photo.rating_changed` with field `action: "created"|"updated"|"removed"`
- **Pros:**
  - Simpler event schema
  - Single event handler
- **Cons:**
  - Less semantic
  - Requires filtering by action field in analytics

**Option C: Two events (rated/removed only)**
- **Events:** `photo.rated` (create or update), `photo.rating_removed`
- **Pros:**
  - Simpler (updates are just "rated again")
  - Matches user mental model (user doesn't distinguish create vs update)
- **Cons:**
  - Can't track rating changes separately from new ratings

**Impact:** Low - affects telemetry analytics, doesn't affect user experience

---

### Q001-20: Rating Analytics/Trending Features

**Context:** Spec explicitly excludes "advanced rating analytics or trends" from scope, but this may be a desirable future feature.

**Question:** Should we design the schema and telemetry to support future analytics features (trending photos, rating distributions)?

**Options (ordered by preference):**

**Option A: Yes, design for extensibility (Recommended)**
- **Approach:** Include timestamps, consider adding indexes for common queries (ORDER BY rating_avg), design telemetry for time-series analysis
- **Pros:**
  - Easier to add features later
  - Better query performance from day 1
  - Minimal overhead now
- **Cons:**
  - May add complexity that's never used
  - YAGNI (You Aren't Gonna Need It) principle violation

**Option B: No, implement minimally for current scope**
- **Approach:** Bare minimum schema/indexes for current requirements, add analytics support later if needed
- **Pros:**
  - Simpler initial implementation
  - Follows YAGNI principle
  - Faster to ship
- **Cons:**
  - May require schema changes later
  - Migration complexity for existing data

**Impact:** Low - affects future extensibility, not current functionality

---

### Q001-21: Album Aggregate Rating Display

**Context:** Spec excludes "album-level aggregate ratings" from scope, but users may expect to see album ratings in album grid view.

**Question:** Should we display aggregate album ratings (average of all photo ratings in album)?

**Options (ordered by preference):**

**Option A: Defer to future feature (Recommended)**
- **Decision:** Not in scope for Feature 001, track as separate future feature (Feature 00X)
- **Pros:**
  - Keeps current feature focused
  - Can design properly later with user feedback on photo ratings
- **Cons:**
  - Users may expect this feature
  - More work to add later

**Option B: Add to current feature scope**
- **Implementation:** Calculate album average from photo ratings, display in album grid
- **Pros:**
  - Complete feature (photos + albums)
  - More useful to users
- **Cons:**
  - Increases scope significantly
  - More complex queries (aggregate of aggregates)
  - Unclear UX (what does album rating mean? average of photos? weighted by photo quality?)

**Impact:** Low - out of current scope, but may be user expectation

---

### Q001-22: Rating Export in Photo Backup

**Context:** Lychee supports photo export/backup functionality. Spec doesn't clarify if rating data should be included in exports.

**Question:** Should photo export/backup include rating data (user's own rating and/or aggregates)?

**Options (ordered by preference):**

**Option A: Include in export (CSV/JSON format) (Recommended)**
- **Export fields:** photo_id, user's rating, average rating, rating count
- **Pros:**
  - Complete data portability
  - Users can back up their ratings
  - Useful for data analysis outside Lychee
- **Cons:**
  - Larger export files
  - Privacy concerns if export is shared (includes others' aggregate data)

**Option B: Export user's ratings only (not aggregates)**
- **Export fields:** photo_id, user's rating
- **Pros:**
  - User data portability
  - No privacy concerns (only user's own data)
- **Cons:**
  - Incomplete export (aggregates lost)

**Option C: No export (ratings are ephemeral/server-side only)**
- **Decision:** Ratings not included in photo exports
- **Pros:**
  - Simpler export logic
  - Smaller export files
- **Cons:**
  - Data loss risk if server fails
  - No migration path to other platforms

**Impact:** Low - affects data portability, not core functionality

---

### Q001-23: Rating Notification to Photo Owner

**Context:** When other users rate a photo, the photo owner may want to be notified (similar to comment notifications).

**Question:** Should photo owners receive notifications when their photos are rated?

**Options (ordered by preference):**

**Option A: Defer to future feature (notifications system) (Recommended)**
- **Decision:** Not in scope for Feature 001, add when notifications framework is implemented
- **Pros:**
  - Keeps feature scope focused
  - Requires notifications infrastructure (may not exist yet)
  - Can be added non-intrusively later
- **Cons:**
  - Photo owners won't know when photos are rated
  - Lower engagement

**Option B: Simple email notification**
- **Implementation:** Send email to photo owner when photo is rated (with throttling: max 1 email per photo per day)
- **Pros:**
  - Engagement boost
  - Photo owners stay informed
- **Cons:**
  - Email fatigue (could get many emails)
  - Requires email configuration
  - Increases scope

**Option C: In-app notification only (no email)**
- **Implementation:** Show notification bell/count in Lychee UI when photos are rated
- **Pros:**
  - Less intrusive than email
  - Real-time feedback when user is active
- **Cons:**
  - Requires notification UI infrastructure
  - User may miss notifications if not logged in

**Impact:** Low - nice-to-have feature, not core rating functionality

---

### Q001-24: Statistics Recalculation Artisan Command

**Context:** Implementation notes mention "artisan command to recalculate all statistics from photo_ratings table for data integrity audits."

**Question:** Should we implement an artisan command to recalculate rating statistics, and if so, when should it be used?

**Options (ordered by preference):**

**Option A: Yes, implement `php artisan photos:recalculate-ratings` command (Recommended)**
- **Usage:** Run manually after data migration, database corruption, or as periodic audit
- **Behavior:** Iterate all photos, sum ratings from photo_ratings table, update photo_statistics
- **Pros:**
  - Data integrity safety net
  - Useful for debugging/auditing
  - Can fix inconsistencies from bugs or manual DB edits
- **Cons:**
  - Extra code to maintain
  - May be slow on large databases
  - Risk of overwriting correct data if command is buggy

**Option B: No command, rely on transaction integrity**
- **Decision:** Trust atomic transactions to maintain consistency, no recalculation needed
- **Pros:**
  - Simpler (less code)
  - Transactions should guarantee consistency
- **Cons:**
  - No recovery if bug causes inconsistency
  - No way to audit/verify correctness

**Option C: Automated periodic recalculation (cron job)**
- **Implementation:** Run recalculation command daily/weekly via scheduler
- **Pros:**
  - Automatic data integrity maintenance
  - Catches and fixes issues proactively
- **Cons:**
  - Resource intensive (extra DB load)
  - May mask underlying bugs instead of fixing them
  - Overkill if transactions are working correctly

**Impact:** Low - data integrity safety feature, not core functionality

---

### Q001-25: Migration Strategy for Existing Installations

**Context:** When existing Lychee installations upgrade to this feature, they'll have photos but no rating data. Migration behavior isn't specified.

**Question:** How should the migration handle existing photos with no rating data?

**Options (ordered by preference):**

**Option A: Migration adds columns with defaults, no backfill (Recommended)**
- **Behavior:** Migration adds rating_sum/rating_count columns with default 0, existing photos have no ratings
- **Pros:**
  - Clean state (accurate: no ratings yet)
  - Fast migration (no data processing)
  - No assumptions about historical data
- **Cons:**
  - Existing photos start with no ratings (expected behavior)

**Option B: Backfill with random/seeded ratings (dev/test only)**
- **Behavior:** For development, optionally seed some random ratings for testing
- **Pros:**
  - Easier to test rating display with real-looking data
- **Cons:**
  - Fake data, not suitable for production
  - Could confuse users if accidentally run in production

**Option C: Import from external source (if available)**
- **Behavior:** If migrating from another system with ratings, provide import script
- **Pros:**
  - Preserves historical rating data
- **Cons:**
  - Complex, requires external data source
  - Not applicable to most installations
  - Out of scope for Feature 001

**Impact:** Low - affects upgrade experience, but default behavior (no ratings) is expected

---

### ~~Q-004-01: Recomputation Trigger Strategy for Size Statistics~~ ✅ RESOLVED

**Decision:** Option B - Separate `RecomputeAlbumSizeJob` triggered independently, using Skip middleware with cache-based job tracking (same pattern as Feature 003's `RecomputeAlbumStatsJob`)
**Rationale:** Decoupled from Feature 003, can optimize independently, reuses proven Skip middleware pattern from [RecomputeAlbumStatsJob.php](app/Jobs/RecomputeAlbumStatsJob.php:76-93) with cache key `album_size_latest_job:{album_id}` and unique job IDs for deduplication.
**Updated in spec:** FR-004-02, JOB-004-01, middleware implementation details

---

### ~~Q-004-02: Migration/Backfill Strategy for Existing Albums~~ ✅ RESOLVED

**Decision:** Option A - Separate artisan command, manual execution, PLUS maintenance UI button for operators
**Rationale:** Operator controls timing during maintenance window, fast migration (schema only), progress monitoring. Admin UI button provides convenient trigger for backfill without CLI access.
**Updated in spec:** FR-004-04, CLI-004-01, maintenance UI addition

---

### ~~Q-004-03: Job Deduplication Approach for Concurrent Updates~~ ✅ RESOLVED

**Decision:** Option D (Custom) - Use Skip middleware with cache-based job tracking (same pattern as Feature 003)
**Rationale:** Reuses proven pattern from [RecomputeAlbumStatsJob.php](app/Jobs/RecomputeAlbumStatsJob.php): Each job gets unique ID, latest job ID stored in cache with key `album_size_latest_job:{album_id}`, `Skip::when()` middleware checks if newer job queued. Simpler than `WithoutOverlapping`, guarantees most recent update eventually processes.
**Updated in spec:** FR-004-02, JOB-004-01

---

## How to Use This Document

1. **Log new questions:** Add a row to the Active Questions table with a unique ID (format: `Q###-##`), feature reference, priority (High/Medium), and brief summary.
2. **Add details:** Create a corresponding section under Question Details with:
   - Full question context
   - Options (A, B, C...) ordered by preference
   - Pros/cons for each option
   - Impact analysis
3. **Present to user:** Once logged, present the question inline in chat referencing the question ID.
4. **Resolve and remove:** When answered, update the relevant spec sections (and create ADR if high-impact), then delete both the table row and Question Details entry.

---

*Last updated: 2026-03-15*

### ~~Q-023-01: Remember-me Cookie Duration and Admin Configurability~~ ✅ RESOLVED

**Decision:** Option C — Use a shorter default (4 weeks) with env override
**Rationale:** A 4-week (40320 minutes) default is more security-conscious than Laravel's ~5-year default while still being practical for home/personal instances. The duration is configurable via `REMEMBER_LIFETIME` env variable, loaded by `config/auth.php` in the lychee guard config (`'remember' => (int) env('REMEMBER_LIFETIME', 40320)`). The existing `SessionOrTokenGuard::createGuard()` already reads this key via `setRememberDuration()`. No admin UI control — env/config only.
**Updated in spec:** Non-Goals (clarified no admin UI for duration), NFR-023-01 (cookie duration = 4 weeks default)

---

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
