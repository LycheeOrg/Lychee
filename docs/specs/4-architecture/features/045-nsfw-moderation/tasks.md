# Feature 045 Tasks – NSFW Detection & Moderation

_Status: Draft_  
_Last updated: 2026-06-22_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions.

## Checklist

### I1 – Enums, Trust Level & Migration

- [ ] T-045-01 – Add `TRUST_BUT_VERIFY` to `UserUploadTrustLevel` enum (FR-045-07, DO-045-09).  
  _Intent:_ Add `case TRUST_BUT_VERIFY = 'trust_but_verify'` to `app/Enum/UserUploadTrustLevel.php`. Update the enum docblock to describe the new tier: uploads are immediately validated, NSFW scanning always applied, block findings moderated, review findings auto-approved.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ This is the first task because all subsequent NSFW logic depends on the 4-tier trust model.

- [ ] T-045-02 – Update `SetUploadValidated` pipe for `trust_but_verify` (FR-045-07).  
  _Intent:_ In `app/Actions/Photo/Pipes/Shared/SetUploadValidated.php`, ensure `trust_but_verify` maps to `is_validated = true`. Current logic: `$state->photo->is_validated = $state->upload_trust_level !== UserUploadTrustLevel::CHECK`. This already works since `TRUST_BUT_VERIFY !== CHECK`, but verify and add a test.  
  _Verification commands:_  
  - `php artisan test --filter=SetUploadValidated`  
  - `make phpstan`

- [ ] T-045-03 – Update TypeScript type for `UserUploadTrustLevel`.  
  _Intent:_ In `resources/js/lychee.d.ts`, update the type to include `"trust_but_verify"`: `type UserUploadTrustLevel = "check" | "monitor" | "trust_but_verify" | "trusted"`.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-045-04 – Create `NsfwPreset` enum (FR-045-03, FR-045-10, DO-045-02).  
  _Intent:_ `app/Enum/NsfwPreset.php` with 6 cases: `DEFAULT`, `STRICT`, `MODERATION`, `NUDE_FEMALE`, `PERMISSIVE`, `SOCIAL_MEDIA`. Backed by string values matching lowercase names.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Used in config validation and outbound request building.

- [ ] T-045-05 – Create `NsfwStatus` enum (FR-045-13, DO-045-06).  
  _Intent:_ `app/Enum/NsfwStatus.php` with 4 cases: `PENDING`, `FAILED`, `REVIEW`, `VISIBLE`. Single enum replacing both scan status and visibility tracking. `null` = not yet scanned; `PENDING` = scan dispatched; `FAILED` = scan errored; `REVIEW` = held for moderation; `VISIBLE` = scan completed with no action or admin-approved. No `BLOCKED` value — block actions hard-delete the photo.  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-06 – Create action enums and detection label enum (FR-045-04, FR-045-06, FR-045-09, FR-045-15, DO-045-03, DO-045-04, DO-045-10, DO-045-11).  
  _Intent:_ Four enums in `app/Enum/`:
  - `NsfwBlockFindingAction`: `BLOCK`, `MODERATE`, `APPROVE` — controls what happens on block findings per trust tier. `APPROVE` only valid for `nsfw_trust_block_action` (trusted tier)
  - `NsfwSensitiveAlbumAction`: `MARK_ALBUM`, `NOTHING` — controls whether sensitive findings trigger album NSFW marking
  - `NsfwSensitiveNoAlbumAction`: `SKIP`, `MODERATE` — fallback when sensitive fires on unsorted photo
  - `NsfwDetectionLabel`: 18 cases matching the classifier output labels — `FEMALE_GENITALIA_COVERED`, `FACE_FEMALE`, `BUTTOCKS_EXPOSED`, `FEMALE_BREAST_EXPOSED`, `FEMALE_GENITALIA_EXPOSED`, `MALE_BREAST_EXPOSED`, `ANUS_EXPOSED`, `FEET_EXPOSED`, `BELLY_COVERED`, `FEET_COVERED`, `ARMPITS_COVERED`, `ARMPITS_EXPOSED`, `FACE_MALE`, `BELLY_EXPOSED`, `MALE_GENITALIA_EXPOSED`, `ANUS_COVERED`, `FEMALE_BREAST_COVERED`, `BUTTOCKS_COVERED`. Used as the `label` column type on `nsfw_detections`.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Review findings are not configurable (determined by trust-tier matrix). Sensitive findings have two separate configs: album action and no-album fallback.

- [ ] T-045-07 – Create migration: add `nsfw_status` and `upload_trust_level` to `photos` table (FR-045-13, FR-045-14).  
  _Intent:_ Migration adds:
  - `nsfw_status` (nullable string, after `face_scan_status`) — pending/failed/review/visible. Single column replacing both `nsfw_scan_status` and `nsfw_visibility`.
  - `upload_trust_level` (nullable string, default null) — snapshots the uploading user's trust level at upload time (Q-045-01 → Option B).
  Down migration drops both columns.  
  _Verification commands:_  
  - `php artisan test` (migration runs on test DB)  
  - `make phpstan`

- [ ] T-045-08 – Create migration: `nsfw_detections` table (FR-045-09, DO-045-01).  
  _Intent:_ Table with columns: `id` (bigIncrements), `photo_id` (string FK to photos.id, cascade delete), `label` (string), `confidence` (float), `bbox_x` (integer), `bbox_y` (integer), `bbox_width` (integer), `bbox_height` (integer), `area_pixels` (integer, nullable), `area_ratio` (float, nullable), `is_block` (boolean, default false), `is_review` (boolean, default false), `is_sensitive` (boolean, default false), `created_at` (timestamp). No `updated_at`. A detection appearing in multiple arrays (e.g., both `block_detected` and `sensitive_detected`) gets one row with multiple booleans set to true. Cascade delete ensures detections are removed when a photo is hard-deleted (block action).  
  _Verification commands:_  
  - `php artisan test`  
  - `make phpstan`

- [ ] T-045-09 – Create config migration: 9 NSFW config keys (FR-045-03, FR-045-04, FR-045-06, FR-045-08, FR-045-15, NFR-045-04).  
  _Intent:_ Extends `BaseConfigMigration`. Keys:
  1. `ai_vision_nsfw_enabled` (bool, default `0`, cat `AI Vision`) — NSFW-specific toggle, mirrors `ai_vision_face_enabled`
  2. `nsfw_preset` (string, default `default`, cat `AI Vision`, type_range `default|strict|moderation|nude_female|permissive|social_media`)
  3. `nsfw_check_block_action` (string, default `block`, cat `AI Vision`, type_range `block|moderate`) — action for block findings on `check` users
  4. `nsfw_monitor_block_action` (string, default `moderate`, cat `AI Vision`, type_range `block|moderate`) — action for block findings on `monitor` users
  5. `nsfw_trust_but_verify_block_action` (string, default `moderate`, cat `AI Vision`, type_range `block|moderate`) — action for block findings on `trust_but_verify` users
  6. `nsfw_trust_block_action` (string, default `approve`, cat `AI Vision`, type_range `block|moderate|approve`) — action for block findings on `trusted` users
  7. `nsfw_sensitive_album_action` (string, default `mark_album`, cat `AI Vision`, type_range `mark_album|nothing`) — whether sensitive findings trigger album marking
  8. `nsfw_sensitive_no_album_action` (string, default `skip`, cat `AI Vision`, type_range `skip|moderate`) — fallback when sensitive fires on unsorted photo
  9. `nsfw_scan_trusted_users` (bool, default `0`, cat `AI Vision`)  
  _Verification commands:_  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ Block finding action is configurable per trust level with progressively relaxed defaults: `check` → `block` (strictest), `monitor` → `moderate`, `trust_but_verify` → `moderate`, `trusted` → `approve` (most permissive). The `trusted` tier additionally supports `approve` to skip action entirely.

### I2 – Config & Service Layer

- [ ] T-045-10 – Add NSFW service config to `config/features.php` and `.env.example` (NFR-045-02).  
  _Intent:_ Add `nsfw-url` and `nsfw-api-key` under `ai-vision-service` in `config/features.php`. Add `AI_VISION_NSFW_URL` and `AI_VISION_NSFW_API_KEY` to `.env.example`.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Keys stored in `.env` only, never in `configs` table.

- [ ] T-045-11 – Create `NsfwDetectionService` (FR-045-01, API-045-03, S-045-28, S-045-29).  
  _Intent:_ `app/Services/Image/NsfwDetectionService.php`. Methods:
  - `dispatchPhoto(string $photo_id, ?string $album_id)` — reads photo path, builds payload, sends HTTP POST to NSFW service.
  - `dispatchUnscannedPhotos(?string $album_id)` — queries photos with `nsfw_status IS NULL` or `failed`, batches dispatch.
  - Preset handling: reads `nsfw_preset` from config; if `default`, omit `preset` field from payload.  
  Write unit tests for request building and preset omission.  
  _Verification commands:_  
  - `php artisan test --filter=NsfwDetectionService`  
  - `make phpstan`

- [ ] T-045-12 – Create `NsfwActionService` (FR-045-04, FR-045-05, FR-045-06, FR-045-07, FR-045-08, FR-045-13, FR-045-17).  
  _Intent:_ `app/Services/Image/NsfwActionService.php`. Core method:
  - `applyActions(Photo $photo, bool $should_block, bool $should_review, bool $is_sensitive)` — implements the trust-tier × finding-tier matrix:
    - Reads `$photo->upload_trust_level` (snapshotted at upload).
    - **Block findings:** `check` → read `nsfw_check_block_action`: `block` = hard-delete photo, `moderate` = set review. `monitor` → read `nsfw_monitor_block_action`: same options. `trust_but_verify` → read `nsfw_trust_but_verify_block_action`: `block` or `moderate` (default `moderate`). `trusted` → read `nsfw_trust_block_action`: `block`, `moderate`, or `approve` (default `approve`).
    - **Review findings:** `check`/`monitor` → moderate. `trust_but_verify`/`trusted` → approve.
    - **Sensitive findings:** `check` → moderate photo + record for deferred album action (job dispatched at admin approval). `monitor`/`trust_but_verify`/`trusted` → read `nsfw_sensitive_album_action`: `mark_album` = dispatch `ApplyNsfwAlbumSensitivityJob` immediately. `nothing` = no action.
  - `logDetections(string $photo_id, array $block_detected, array $review_detected, array $sensitive_detected)` — creates `NsfwDetection` rows. Deduplicated by photo_id+label+bbox.
  Write unit tests covering all matrix combinations (S-045-04 to S-045-19, S-045-26, S-045-27).  
  _Verification commands:_  
  - `php artisan test --filter=NsfwActionService`  
  - `make phpstan`

- [ ] T-045-10b – Create `NsfwConfigController` and register route (FR-045-18, API-045-04, API-045-05, S-045-36, S-045-37, S-045-38).  
  _Intent:_ `app/Http/Controllers/AiVision/NsfwConfigController.php`. Single `show()` method:
  - Reads `nsfw-url` and `nsfw-api-key` from `config('features.ai-vision-service')`.
  - If URL not configured → return 503 with error message.
  - Proxies `GET /api/nsfw/config` to the external NSFW classification service, authenticated via `X-API-Key` header.
  - On success: return upstream JSON response as-is (contains `config` and `presets` sections).
  - On upstream error: return 502 with error message.
  - On connectivity failure: return 503 with error message.
  Register route `GET /NsfwDetection/config` in `routes/api_v2.php` inside the existing `['feature:ai-vision', 'feature:v8']` middleware group (admin session required — inherits existing auth middleware).  
  Write feature tests for: successful proxy, URL not configured (503), service unreachable (503).  
  _Verification commands:_  
  - `php artisan test --filter=NsfwConfig`  
  - `make phpstan`  
  _Notes:_ Follows the same `Http::withHeaders(['X-API-Key' => ...])` pattern as `FacialRecognitionService`. Timeout: 10s. Response is proxied as-is — Lychee does not validate or transform the upstream payload.

### I3 – Model & Jobs

- [ ] T-045-13 – Create `NsfwDetection` model (DO-045-01).  
  _Intent:_ `app/Models/NsfwDetection.php`. Fillable: `photo_id`, `label`, `confidence`, `bbox_x`, `bbox_y`, `bbox_width`, `bbox_height`, `area_pixels`, `area_ratio`, `is_block`, `is_review`, `is_sensitive`. BelongsTo `Photo`. No `updated_at` (set `UPDATED_AT = null` constant or use `$timestamps = false` with manual `created_at`).  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-14 – Create `DispatchNsfwScanJob` (FR-045-01, S-045-28, S-045-29).  
  _Intent:_ `app/Jobs/DispatchNsfwScanJob.php`. Accepts `photo_id`. Sets `nsfw_status = pending`, calls `NsfwDetectionService::dispatchPhoto()`. 3 retries with exponential backoff. On final failure sets `nsfw_status = failed`.  
  Write unit tests for job dispatch and failure handling.  
  _Verification commands:_  
  - `php artisan test --filter=DispatchNsfwScan`  
  - `make phpstan`

- [ ] T-045-14b – Create `ApplyNsfwAlbumSensitivityJob` (FR-045-06, FR-045-17, S-045-14 to S-045-19, S-045-26, S-045-27, S-045-34, S-045-35).  
  _Intent:_ `app/Jobs/ApplyNsfwAlbumSensitivityJob.php`. Accepts `photo_id`. Loads the photo and its direct parent album. Checks `is_recursive_nsfw` on album (via `AlbumBuilder::addVirtualIsRecursiveNSFW()`). If album exists and no ancestor is NSFW, sets `album.is_nsfw = true`. If no album (unsorted): reads `nsfw_sensitive_no_album_action` — `skip` logs warning, `moderate` sets `nsfw_status = review`, `is_validated = false`.  
  Dispatched from two places:
  - **Auto-approval path** (callback time): `NsfwActionService::applyActions()` dispatches for `monitor`/`trust_but_verify`/`trusted` users when `nsfw_sensitive_album_action = mark_album`.
  - **Admin approval path**: `ModerationController::approve()` dispatches for `check` users when photo has `is_sensitive` detections and `nsfw_sensitive_album_action = mark_album`.  
  Write unit tests for: album marking, recursive NSFW skip, no-album fallback (skip and moderate).  
  _Verification commands:_  
  - `php artisan test --filter=ApplyNsfwAlbum`  
  - `make phpstan`

### I4 – Upload Pipeline Pipe

- [ ] T-045-15 – Create `AutoScanNsfwOnUpload` pipe (FR-045-01, FR-045-08, FR-045-14, S-045-01 to S-045-03, S-045-22, S-045-23, S-045-31).  
  _Intent:_ `app/Actions/Photo/Pipes/Standalone/AutoScanNsfwOnUpload.php`. Implements `StandalonePipe`. After `$state = $next($state)`:
  1. `$state->photo->isPhoto()` — skip non-photos.
  2. Snapshot the uploader's trust level: `$state->photo->upload_trust_level = $state->upload_trust_level` (from the DTO, which carries the authenticated uploader's trust level — NOT `$state->photo->owner`, which is the album owner and may differ from the uploader).
  3. `ai_vision_enabled` config — skip if global AI Vision is disabled.
  4. `ai_vision_nsfw_enabled` config — skip if NSFW classification is disabled.
  5. Trust level check using the just-snapshotted value: `check`/`monitor`/`trust_but_verify` → always dispatch. `trusted` → dispatch only if `nsfw_scan_trusted_users = true`.
  6. Dispatches `DispatchNsfwScanJob`.
  Register in `Create` action's standalone pipe chain after `AutoScanFacesOnUpload`.  
  Write unit tests for all trust level branches (4 tiers).  
  _Verification commands:_  
  - `php artisan test --filter=AutoScanNsfw`  
  - `make phpstan`

### I5 – Callback Controller, Approval Logic & Routes

- [ ] T-045-16 – Create `NsfwDetectionResultsRequest` (FR-045-02, API-045-01, S-045-21).  
  _Intent:_ `app/Http/Requests/Nsfw/NsfwDetectionResultsRequest.php`. Authorizes via `X-API-Key` header against `config('features.ai-vision-service.nsfw-api-key')`. Validates full callback payload: `photo_id`, `status`, `should_block`, `should_review`, `is_sensitive`, `all_detected.*`, `block_detected.*`, `review_detected.*`, `sensitive_detected.*` including nested `bbox` objects.  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-17 – Create `NsfwDetectionController` (FR-045-02, FR-045-11, API-045-01, API-045-02).  
  _Intent:_ `app/Http/Controllers/AiVision/NsfwDetectionController.php`. Methods:
  - `results(NsfwDetectionResultsRequest $request)` — on error: set `nsfw_status = failed`, log, return. On success: call `NsfwActionService::logDetections()` then `NsfwActionService::applyActions()`. The action service sets `nsfw_status` to `review` or `visible` per the trust-tier matrix. Note: if block action deletes the photo, the row is gone.
  - `bulkScan(BulkNsfwScanRequest $request, NsfwDetectionService $service)` — call `service->dispatchUnscannedPhotos()`, return 202.  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-18 – Create `BulkNsfwScanRequest` (FR-045-11, API-045-02, S-045-24, S-045-25).  
  _Intent:_ `app/Http/Requests/Nsfw/BulkNsfwScanRequest.php`. Admin-only authorization. Parameters: optional `album_id` (string) for scoped scan, optional `force` (boolean, default false) to re-scan completed photos.  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-19 – Update `ModerationController::approve()` to dispatch album sensitivity job (FR-045-17, S-045-34, S-045-35).  
  _Intent:_ In `app/Http/Controllers/Admin/ModerationController.php`, after setting `is_validated = true` and `nsfw_status = visible` on approval:
  - Check if photo has `is_sensitive = true` detections in `nsfw_detections` AND `nsfw_sensitive_album_action = mark_album`.
  - If yes, dispatch `ApplyNsfwAlbumSensitivityJob` for the photo. The job handles the recursive NSFW check, album marking, and no-album fallback asynchronously.
  Write tests for approval with and without sensitive detections.  
  _Verification commands:_  
  - `php artisan test --filter=Moderation`  
  - `make phpstan`

- [ ] T-045-20 – Register routes in `routes/api_v2.php` (API-045-01, API-045-02).  
  _Intent:_ Add routes:
  - `POST /NsfwDetection/results` → `NsfwDetectionController::results()` (public, API-key auth only)
  - `POST /NsfwDetection/bulk-scan` → `NsfwDetectionController::bulkScan()` (admin middleware)  
  Register inside the existing `['feature:ai-vision', 'feature:v8']` middleware group. Add `support` middleware (basic SE gating, not `support:pro`) to the NSFW routes.  
  _Verification commands:_  
  - `php artisan route:list --path=NsfwDetection`  
  - `make phpstan`

- [ ] T-045-21 – Write feature tests for callback endpoint (S-045-04 to S-045-30).  
  _Intent:_ Feature test class `Tests\Feature_v2\Nsfw\NsfwDetectionResultsTest`. Test cases:
  1. `check` + `should_block` + `nsfw_check_block_action = block` → photo hard-deleted (S-045-04).
  2. `check` + `should_block` + `nsfw_check_block_action = moderate` → `nsfw_status = review` (S-045-05).
  3. `check` + `should_review` → `nsfw_status = review` (S-045-06).
  4. `monitor` + `should_block` + `nsfw_monitor_block_action = block` → photo hard-deleted (S-045-07).
  5. `monitor` + `should_block` + `nsfw_monitor_block_action = moderate` → review (S-045-08).
  6. `monitor` + `should_review` → review (S-045-09).
  7. `trust_but_verify` + `should_block` + `nsfw_trust_but_verify_block_action = moderate` → review (S-045-10).
  7b. `trust_but_verify` + `should_block` + `nsfw_trust_but_verify_block_action = block` → hard-deleted (S-045-10b).
  8. `trust_but_verify` + `should_review` → no action (S-045-11).
  9. `trusted` + `should_block` + `nsfw_trust_block_action = approve` → no action (S-045-12).
  9b. `trusted` + `should_block` + `nsfw_trust_block_action = moderate` → review (S-045-12b).
  9c. `trusted` + `should_block` + `nsfw_trust_block_action = block` → hard-deleted (S-045-12c).
  10. `trusted` + `should_review` → no action (S-045-13).
  11. `check` + `is_sensitive` → moderate photo (S-045-14).
  12. `monitor` + `is_sensitive` + `mark_album` → album marked immediately (S-045-15).
  13. `monitor` + `is_sensitive` + `nothing` → no action (S-045-16).
  14. `trust_but_verify` + `is_sensitive` + `mark_album` → album marked (S-045-17).
  15. `trusted` + `is_sensitive` + `mark_album` → album marked (S-045-18).
  16. Album already recursive NSFW + sensitive → album NOT re-marked (S-045-19).
  17. Error status → `nsfw_status = failed` (S-045-20).
  18. Invalid API key → 403 (S-045-21).
  19. Bulk scan (default) → 202, `NULL` + `failed` queued (S-045-24).
  20. Bulk scan (`force = true`) → 202, all re-queued (S-045-25).
  21. Sensitive + no album + `skip` → warning, no change (S-045-26).
  22. Sensitive + no album + `moderate` → review (S-045-27).
  23. Detection logging: only `block_detected`/`review_detected`/`sensitive_detected` stored; dedup by photo_id+label+bbox (S-045-30).  
  _Verification commands:_  
  - `php artisan test --filter=NsfwDetectionResults`  
  - `make phpstan`

### I6 – Frontend Settings, Maintenance & Moderation

- [ ] T-045-22 – Add NSFW settings section to admin Settings view (UI-045-01).  
  _Intent:_ Add NSFW Detection section to the settings page under `AI Vision` category. Controls: enable toggle, preset dropdown, block finding action dropdown, sensitive album action dropdown, sensitive no-album fallback dropdown, scan trusted users toggle. Include a read-only trust-tier × finding matrix summary table. Uses existing settings infrastructure.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`

- [ ] T-045-23 – Add `MaintenanceBulkScanNsfw` component (UI-045-02, FR-045-11).  
  _Intent:_ `resources/js/components/maintenance/MaintenanceBulkScanNsfw.vue`. Card with description and "Scan All Unscanned" button. Calls `POST /NsfwDetection/bulk-scan`. Show only when `ai_vision_nsfw_enabled = true`.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`

- [ ] T-045-24 – Create `nsfw-detection-service.ts` (API-045-02).  
  _Intent:_ `resources/js/services/nsfw-detection-service.ts`. Method `bulkScan(albumId?: string)` calling `POST /NsfwDetection/bulk-scan`.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-045-24b – Create `nsfw-config-service.ts` (API-045-04, FR-045-18).  
  _Intent:_ `resources/js/services/nsfw-config-service.ts`. TypeScript types and service:
  - Type `NsfwLabelSet`: `{ labels: string[], confidence: number | null, area_ratio: number | null, label_thresholds: Record<string, number> }`
  - Type `NsfwPresetConfig`: `{ name: string, description: string, block: NsfwLabelSet, review: NsfwLabelSet, sensitive: NsfwLabelSet }`
  - Type `NsfwConfigResponse`: `{ config: Record<string, string>, presets: Record<string, NsfwPresetConfig> }`
  - Method `getConfig()` calling `GET /NsfwDetection/config`.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-045-24c – Create `NsfwConfig.vue` admin page (FR-045-18, UI-045-04).  
  _Intent:_ `resources/js/views/admin/NsfwConfig.vue`. Dedicated admin page for viewing the NSFW classification service's presets overview:
  - Fetches config via `nsfw-config-service.ts` `getConfig()` on mount.
  - **Service Runtime Config** section: renders `config` object as a key-value table. Excludes JSON-encoded fields (`block`, `review`, `sensitive`) since they are the stringified defaults — the presets section provides the parsed version.
  - **Available Presets** section: renders each preset as a card/panel showing:
    - Preset name as header (highlight if it matches the active `nsfw_preset` config key).
    - Description text.
    - Three sub-sections (Block / Review / Sensitive) each showing labels as tag chips, plus confidence and area_ratio overrides if non-null.
  - Toolbar with title and refresh button.
  - Loading spinner (ProgressSpinner) while fetching.
  - Error panel when service unreachable (show error message from API).
  - Uses PrimeVue components: Panel, Tag, DataTable (for config), ProgressSpinner, Button.
  Register route in `resources/js/router/routes.ts`: `{ name: "nsfw-config", path: "/admin/nsfw-config", component: NsfwConfig }`.  
  Add admin dashboard tile in `resources/js/composables/useAdminTiles.ts`: key `nsfw-config`, group `extensions`, icon `pi pi-eye`, visible when `is_ai_vision_enabled` and `can_edit`.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`  
  - Manual: navigate to `/admin/nsfw-config`, verify page loads and displays presets when service is configured.

- [ ] T-045-25 – Update Moderation page: NSFW status badge (FR-045-13, UI-045-03).  
  _Intent:_ 
  - Update `ModerationResource` to include `nsfw_status` field.
  - In `resources/js/views/admin/Moderation.vue`: display NSFW badge/tag on photos where `nsfw_status` is `review`. Badge text: "NSFW Review". No blocked filter needed — block actions hard-delete photos, so no blocked rows exist.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`  
  - `php artisan test --filter=Moderation`  
  - `make phpstan`

- [ ] T-045-26 – Add translation keys for NSFW settings and presets page (22 languages).  
  _Intent:_ Add English translation keys first; other languages as follow-up. Keys for: section title, enable label, preset label, each preset name, block finding action labels, sensitive album action labels, no-album fallback labels, trust level labels, NSFW badge labels for Moderation view, trust-tier matrix labels. Additionally for the presets overview page: page title, service config section header, presets section header, block/review/sensitive tier labels, refresh button label, loading text, error messages (service not configured, service unreachable).  
  _Verification commands:_  
  - `npm run check`

### I7 – Quality Gates & Documentation

- [ ] T-045-27 – Run full quality gate.  
  _Intent:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`. Fix any issues.  
  _Verification commands:_  
  - All quality gate commands pass.

- [ ] T-045-28 – Update knowledge map and roadmap.  
  _Intent:_ Add NSFW detection modules to `docs/specs/4-architecture/knowledge-map.md`. Add Feature 045 row to `docs/specs/4-architecture/roadmap.md` Active Features table.  
  _Verification commands:_  
  - Manual review.

- [ ] T-045-29 – Update `.env.example` with new env vars.  
  _Intent:_ Add `AI_VISION_NSFW_URL=` and `AI_VISION_NSFW_API_KEY=` to `.env.example` in the AI Vision section.  
  _Verification commands:_  
  - Manual review.

## Notes / TODOs

### Key Changes from v1 Spec

- **New trust tier**: `TRUST_BUT_VERIFY` added to `UserUploadTrustLevel` (4 tiers total).
- **Block action = hard delete**: Block findings delete the photo entirely. No `blocked` status — the photo row is gone.
- **Trust-tier × finding-tier matrix**: Replaces the per-finding-tier config approach. Block findings are configurable per trust tier via four config settings: `nsfw_check_block_action` (default `block`), `nsfw_monitor_block_action` (default `moderate`), `nsfw_trust_but_verify_block_action` (default `moderate`), `nsfw_trust_block_action` (default `approve`; also supports `approve`). Album marking is controlled via `nsfw_sensitive_album_action`.
- **Deferred album marking**: Sensitive findings for `check` users mark the album at approval time, not callback time. This prevents a moderated photo from prematurely flipping the album to NSFW.
- **Recursive NSFW check**: Before marking an album NSFW, check `is_recursive_nsfw`. If any ancestor is already NSFW, skip marking (already effectively NSFW).
- **Moderation page**: No blocked filter needed — block actions hard-delete photos. Moderation page shows `nsfw_status = review` entries.
- **Config keys**: 9 config keys. Block action configurable per trust level: `nsfw_check_block_action` (default `block`), `nsfw_monitor_block_action` (default `moderate`), `nsfw_trust_but_verify_block_action` (default `moderate`), `nsfw_trust_block_action` (default `approve`).

### Resolved Questions
- **Q-045-01 → B:** `upload_trust_level` snapshotted on `photos` at upload time from `$state->upload_trust_level` (the uploader's trust level, not the photo owner's). Now includes `trust_but_verify` value.
- **Q-045-02 → Custom:** Single `nsfw_status` enum (pending/failed/review/visible) + `is_validated` combination. No `blocked` value — block actions hard-delete the photo row.
- **Q-045-03 → Custom:** Configurable via `nsfw_sensitive_no_album_action` (skip/moderate).
- **Q-045-04 → Subsumed** by Q-045-01. Pipe reads `$state->upload_trust_level` from the DTO (the uploader's trust level, pre-resolved at dispatch time). Does NOT use `$state->photo->owner` — the owner may be the album owner, not the uploader.
- **Q-045-05 → A:** Separate URL (`AI_VISION_NSFW_URL`) and API key (`AI_VISION_NSFW_API_KEY`).
- **Q-045-06 → A (modified):** Store individual detections but only from `block_detected`/`review_detected`/`sensitive_detected`. `all_detected` NOT persisted.
- **Q-045-07 → A:** Global preset only in v1.
- **Q-045-08 → A:** Direct album only marked sensitive. Now with recursive NSFW check.
- **Q-045-09 → B:** Bulk scan includes `NULL` + `failed` by default; `force` param re-scans `completed`.
- **Q-045-10 → B:** NSFW badge/tag in Moderation view. Blocked hidden by default.
- **Q-045-11 → Custom:** Config category is `AI Vision`.
- **Q-045-12 → Custom:** Simple SE gating (`support` middleware, not `support:pro`).

### Implementation Notes
- All 12 open questions remain resolved. No new questions introduced.
- Trust-tier matrix is the core decision logic — all action logic flows through `NsfwActionService::applyActions()`.
- Hard-delete on block means `nsfw_detections` rows cascade-delete with the photo. The action is logged via telemetry before deletion.
- `SetUploadValidated` already handles `trust_but_verify` correctly (anything != CHECK is validated), but a test should confirm this.
- The `is_recursive_nsfw` virtual column on `AlbumBuilder` provides the ancestor NSFW check — use `Album::query()->addVirtualIsRecursiveNSFW()->find($albumId)` to load it.
- **NSFW Config proxy** (T-045-10b): Pure pass-through — Lychee does not validate, transform, or cache the upstream response. The controller follows the same `Http::withHeaders(['X-API-Key' => ...])` pattern as `FacialRecognitionService`. The frontend page is read-only (no editing of upstream config from Lychee).
- **Presets overview page** (T-045-24c): The upstream `config` object contains stringified JSON in `block`/`review`/`sensitive` keys (the service's active label-set defaults). These are excluded from the runtime config table since the parsed versions are already displayed in the presets section. The `presets` object always includes `default` plus any named presets (`strict`, `moderation`, `nude_female`, `permissive`, `social_media`).
