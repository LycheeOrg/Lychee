# Feature 045 Tasks – NSFW Detection & Moderation

_Status: Draft_  
_Last updated: 2026-06-21_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions.

## Checklist

### I1 – Enums & Migration

- [ ] T-045-01 – Create `NsfwPreset` enum (FR-045-03, FR-045-10, DO-045-02).  
  _Intent:_ `app/Enum/NsfwPreset.php` with 6 cases: `DEFAULT`, `STRICT`, `MODERATION`, `NUDE_FEMALE`, `PERMISSIVE`, `SOCIAL_MEDIA`. Backed by string values matching lowercase names.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Used in config validation and outbound request building.

- [ ] T-045-02 – Create `NsfwScanStatus` enum (FR-045-12, DO-045-06).  
  _Intent:_ `app/Enum/NsfwScanStatus.php` with 3 cases: `PENDING`, `COMPLETED`, `FAILED`. Mirrors `FaceScanStatus`.  
  _Verification commands:_  
  - `make phpstan`

- [ ] ~~T-045-03~~ – ~~Create `NsfwDetectionTier` enum~~ — removed. Tier is now represented by three boolean columns (`is_block`, `is_review`, `is_sensitive`) on `nsfw_detections` since a detection can belong to multiple tiers simultaneously. No enum needed.

- [ ] T-045-04 – Create action enums and visibility enum (FR-045-04, FR-045-05, FR-045-06, FR-045-13, DO-045-03, DO-045-04, DO-045-05, DO-045-08).  
  _Intent:_ Four enums in `app/Enum/`:
  - `NsfwBlockAction`: `BLOCK`, `NOTHING`
  - `NsfwModerationAction`: `BLOCK`, `MODERATE`, `NOTHING`
  - `NsfwSensitiveAction`: `MODERATE`, `SENSITIVE`, `NOTHING`
  - `NsfwVisibility`: `VISIBLE`, `BLOCKED`, `REVIEW` — tracks the NSFW-specific state on a photo (Q-045-02 resolution)  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-05 – Create migration: add `nsfw_scan_status`, `nsfw_visibility`, and `upload_trust_level` to `photos` table (FR-045-12, FR-045-13, FR-045-14).  
  _Intent:_ Migration adds:
  - `nsfw_scan_status` (nullable string, after `face_scan_status`) — pending/completed/failed.
  - `nsfw_visibility` (nullable string, default null) — visible/blocked/review. Combined with `is_validated` in Moderation panel (Q-045-02).
  - `upload_trust_level` (nullable string, default null) — snapshots the uploading user's trust level at upload time (Q-045-01 → Option B).
  Down migration drops all three columns.  
  _Verification commands:_  
  - `php artisan test` (migration runs on test DB)  
  - `make phpstan`

- [ ] T-045-06 – Create migration: `nsfw_detections` table (FR-045-09, DO-045-01).  
  _Intent:_ Table with columns: `id` (bigIncrements), `photo_id` (string FK to photos.id, cascade delete), `label` (string), `confidence` (float), `bbox_x` (integer), `bbox_y` (integer), `bbox_width` (integer), `bbox_height` (integer), `area_pixels` (integer, nullable), `area_ratio` (float, nullable), `is_block` (boolean, default false), `is_review` (boolean, default false), `is_sensitive` (boolean, default false), `created_at` (timestamp). No `updated_at`. A detection appearing in multiple arrays (e.g., both `block_detected` and `sensitive_detected`) gets one row with multiple booleans set to true.  
  _Verification commands:_  
  - `php artisan test`  
  - `make phpstan`

- [ ] T-045-07 – Create config migration: 8 NSFW config keys (FR-045-03, FR-045-04, FR-045-05, FR-045-06, FR-045-07, FR-045-08, FR-045-15, NFR-045-04).  
  _Intent:_ Extends `BaseConfigMigration`. Keys:
  1. `ai_vision_nsfw_enabled` (bool, default `0`, cat `AI Vision`) — NSFW-specific toggle, mirrors `ai_vision_face_enabled`
  2. `nsfw_preset` (string, default `default`, cat `AI Vision`, type_range `default|strict|moderation|nude_female|permissive|social_media`)
  3. `nsfw_action_block` (string, default `block`, cat `AI Vision`, type_range `block|nothing`)
  4. `nsfw_action_moderation` (string, default `moderate`, cat `AI Vision`, type_range `block|moderate|nothing`)
  5. `nsfw_action_sensitive` (string, default `sensitive`, cat `AI Vision`, type_range `moderate|sensitive|nothing`)
  6. `nsfw_sensitive_no_album_action` (string, default `skip`, cat `AI Vision`, type_range `skip|moderate`) — fallback when sensitive action fires but photo has no album (Q-045-03)
  7. `nsfw_scan_trusted_users` (bool, default `0`, cat `AI Vision`)
  8. `nsfw_auto_approve_trusted` (bool, default `0`, cat `AI Vision`)  
  _Verification commands:_  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ Category `AI Vision` groups NSFW detection settings with other AI-powered features (Q-045-11).

### I2 – Config & Service Layer

- [ ] T-045-08 – Add NSFW service config to `config/features.php` and `.env.example` (NFR-045-02).  
  _Intent:_ Add `nsfw-url` and `nsfw-api-key` under `ai-vision-service` in `config/features.php`. Add `AI_VISION_NSFW_URL` and `AI_VISION_NSFW_API_KEY` to `.env.example`.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Keys stored in `.env` only, never in `configs` table.

- [ ] T-045-09 – Create `NsfwDetectionService` (FR-045-01, API-045-03, S-045-19, S-045-20).  
  _Intent:_ `app/Services/Image/NsfwDetectionService.php`. Methods:
  - `dispatchPhoto(string $photo_id, ?string $album_id)` — reads photo path, builds payload, sends HTTP POST to NSFW service.
  - `dispatchUnscannedPhotos(?string $album_id)` — queries photos with `nsfw_scan_status IS NULL`, batches dispatch.
  - Preset handling: reads `nsfw_preset` from config; if `default`, omit `preset` field from payload.  
  Write unit tests for request building and preset omission.  
  _Verification commands:_  
  - `php artisan test --filter=NsfwDetectionService`  
  - `make phpstan`

- [ ] T-045-10 – Create `NsfwActionService` (FR-045-04, FR-045-05, FR-045-06, FR-045-07, FR-045-08, FR-045-13, FR-045-14, FR-045-15).  
  _Intent:_ `app/Services/Image/NsfwActionService.php`. Method:
  - `applyActions(Photo $photo, bool $should_block, bool $should_review, bool $is_sensitive)` — reads action configs, applies the configured action for each tier. Sets `nsfw_visibility` to `blocked` or `review` as appropriate, and sets `is_validated = false` when `nsfw_visibility` is `blocked` or `review`. For trust-level auto-approve: reads `$photo->upload_trust_level` (snapshotted at upload, Q-045-01 → B). If trusted + auto-approve enabled, moderation findings are skipped; block findings always apply. For sensitive action on unsorted photos: reads `nsfw_sensitive_no_album_action` config — `skip` logs warning and does nothing; `moderate` falls back to `nsfw_visibility = review`, `is_validated = false` (Q-045-03).
  - `logDetections(string $photo_id, array $block_detected, array $review_detected, array $sensitive_detected)` — creates `NsfwDetection` rows from `block_detected`, `review_detected`, `sensitive_detected` only. `all_detected` is NOT persisted (Q-045-06). Detections are deduplicated by label+bbox; a detection appearing in multiple arrays gets one row with `is_block`, `is_review`, `is_sensitive` booleans set accordingly.
  Write unit tests covering all action combinations (S-045-04 to S-045-11, S-045-15, S-045-16, S-045-18).  
  _Verification commands:_  
  - `php artisan test --filter=NsfwActionService`  
  - `make phpstan`

### I3 – Model & Job

- [ ] T-045-11 – Create `NsfwDetection` model (DO-045-01).  
  _Intent:_ `app/Models/NsfwDetection.php`. Fillable: `photo_id`, `label`, `confidence`, `bbox_x`, `bbox_y`, `bbox_width`, `bbox_height`, `area_pixels`, `area_ratio`, `tier`. BelongsTo `Photo`. No `updated_at` (set `UPDATED_AT = null` constant or use `$timestamps = false` with manual `created_at`).  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-12 – Create `DispatchNsfwScanJob` (FR-045-01, S-045-19, S-045-20).  
  _Intent:_ `app/Jobs/DispatchNsfwScanJob.php`. Accepts `photo_id`. Sets `nsfw_scan_status = pending`, calls `NsfwDetectionService::dispatchPhoto()`. 3 retries with exponential backoff. On final failure sets `nsfw_scan_status = failed`.  
  Write unit tests for job dispatch and failure handling.  
  _Verification commands:_  
  - `php artisan test --filter=DispatchNsfwScan`  
  - `make phpstan`

### I4 – Upload Pipeline Pipe

- [ ] T-045-13 – Create `AutoScanNsfwOnUpload` pipe (FR-045-01, FR-045-07, FR-045-14, S-045-01, S-045-02, S-045-03, S-045-14, S-045-24).  
  _Intent:_ `app/Actions/Photo/Pipes/Standalone/AutoScanNsfwOnUpload.php`. Implements `StandalonePipe`. After `$state = $next($state)`:
  1. `$state->photo->isPhoto()` — skip non-photos.
  2. Snapshot the uploading user's trust level: `$state->photo->upload_trust_level = $state->photo->owner->upload_trust_level` and save (Q-045-01 → Option B). This persists the trust level at upload time for later use by the callback.
  3. `ai_vision_enabled` config — skip if global AI Vision is disabled (mirrors `AutoScanFacesOnUpload`).
  4. `ai_vision_nsfw_enabled` config — skip if NSFW classification is disabled.
  5. Trust level check using the just-snapshotted value: `check`/`monitor` → always dispatch. `trusted` → dispatch only if `nsfw_scan_trusted_users = true`.
  6. Dispatches `DispatchNsfwScanJob`.
  Register in `Create` action's standalone pipe chain after `AutoScanFacesOnUpload`.  
  Write unit tests for all trust level branches.  
  _Verification commands:_  
  - `php artisan test --filter=AutoScanNsfw`  
  - `make phpstan`  
  _Notes:_ Q-045-04 resolved by Q-045-01 → B: the pipe loads `$state->photo->owner` to snapshot trust level. The extra query is acceptable per-upload.

### I5 – Callback Controller & Routes

- [ ] T-045-14 – Create `NsfwDetectionResultsRequest` (FR-045-02, API-045-01, S-045-13).  
  _Intent:_ `app/Http/Requests/Nsfw/NsfwDetectionResultsRequest.php`. Authorizes via `X-API-Key` header against `config('features.ai-vision-service.nsfw-api-key')`. Validates full callback payload: `photo_id`, `status`, `should_block`, `should_review`, `is_sensitive`, `all_detected.*`, `block_detected.*`, `review_detected.*`, `sensitive_detected.*` including nested `bbox` objects.  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-15 – Create `NsfwDetectionController` (FR-045-02, FR-045-11, API-045-01, API-045-02).  
  _Intent:_ `app/Http/Controllers/AiVision/NsfwDetectionController.php`. Methods:
  - `results(NsfwDetectionResultsRequest $request)` — on error: set `nsfw_scan_status = failed`, log, return. On success: call `NsfwActionService::logDetections()` then `NsfwActionService::applyActions()`, set `nsfw_scan_status = completed`.
  - `bulkScan(BulkNsfwScanRequest $request, NsfwDetectionService $service)` — call `service->dispatchUnscannedPhotos()`, return 202.  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-16 – Create `BulkNsfwScanRequest` (FR-045-11, API-045-02, S-045-17, S-045-22).  
  _Intent:_ `app/Http/Requests/Nsfw/BulkNsfwScanRequest.php`. Admin-only authorization. Parameters: optional `album_id` (string) for scoped scan, optional `force` (boolean, default false) to re-scan completed photos (Q-045-09 → B).  
  _Verification commands:_  
  - `make phpstan`

- [ ] T-045-17 – Register routes in `routes/api_v2.php` (API-045-01, API-045-02).  
  _Intent:_ Add routes:
  - `POST /NsfwDetection/results` → `NsfwDetectionController::results()` (public, API-key auth only)
  - `POST /NsfwDetection/bulk-scan` → `NsfwDetectionController::bulkScan()` (admin middleware)  
  Register inside the existing `['feature:ai-vision', 'feature:v8']` middleware group in `routes/api_v2.php` (same group as face detection routes). Add `support` middleware (basic SE gating, not `support:pro`, Q-045-12) to the NSFW routes within that group.  
  _Verification commands:_  
  - `php artisan route:list --path=NsfwDetection`  
  - `make phpstan`

- [ ] T-045-18 – Write feature tests for callback endpoint (S-045-04 to S-045-13, S-045-15, S-045-16, S-045-17, S-045-18, S-045-21, S-045-22, S-045-23).  
  _Intent:_ Feature test class `Tests\Feature_v2\Nsfw\NsfwDetectionResultsTest`. Test cases:
  1. Valid callback with `should_block = true` + block action → photo `nsfw_visibility = blocked`, `is_validated = false`.
  2. Valid callback with `should_block = true` + nothing action → no change.
  3. Valid callback with `should_review = true` + moderate action → `nsfw_visibility = review`, `is_validated = false`.
  4. Valid callback with `should_review = true` + block action → `nsfw_visibility = blocked`, `is_validated = false`.
  5. Valid callback with `should_review = true` + nothing action → no change.
  6. Valid callback with `is_sensitive = true` + sensitive action → album `is_nsfw = true`.
  7. Valid callback with `is_sensitive = true` + moderate action → `nsfw_visibility = review`, `is_validated = false`.
  8. Valid callback with `is_sensitive = true` + nothing action → no change.
  9. Error status → `nsfw_scan_status = failed`.
  10. Invalid API key → 403.
  11. Auto-approve: `should_review + upload_trust_level = trusted + auto_approve` → skip (uses snapshotted trust level).
  12. Auto-approve: `should_block + upload_trust_level = trusted + auto_approve` → still block.
  13. Bulk scan (default) → 202, `NULL` + `failed` photos queued, `completed` skipped.
  14. Bulk scan (`force = true`) → 202, all photos including `completed` re-queued (S-045-22).
  15. Sensitive + no album + `nsfw_sensitive_no_album_action = skip` → warning logged, no change (S-045-18).
  19. Sensitive + no album + `nsfw_sensitive_no_album_action = moderate` → `nsfw_visibility = review`, `is_validated = false` (S-045-23).
  16. Detections logged: only `block_detected`/`review_detected`/`sensitive_detected` stored; `all_detected` NOT persisted. A detection in multiple arrays gets one row with multiple booleans true.
  17. Verify both `nsfw_visibility` and `is_validated` are set in combination per action config.
  18. NSFW badge/tag visible in Moderation view for photos with `nsfw_visibility = blocked` or `review` (Q-045-10 → B).  
  _Verification commands:_  
  - `php artisan test --filter=NsfwDetectionResults`  
  - `make phpstan`

### I6 – Frontend Settings & Maintenance

- [ ] T-045-19 – Add NSFW settings section to admin Settings view (UI-045-01).  
  _Intent:_ Add NSFW Detection section to the settings page under `AI Vision` category (Q-045-11). Controls: enable toggle, preset dropdown, 3 action dropdowns, 2 trust-level toggles. Uses existing settings infrastructure.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`

- [ ] T-045-20 – Add `MaintenanceBulkScanNsfw` component (UI-045-02, FR-045-11).  
  _Intent:_ `resources/js/components/maintenance/MaintenanceBulkScanNsfw.vue`. Card with description and "Scan All Unscanned" button. Calls `POST /NsfwDetection/bulk-scan`. Show only when `ai_vision_nsfw_enabled = true`.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`

- [ ] T-045-21 – Create `nsfw-detection-service.ts` (API-045-02).  
  _Intent:_ `resources/js/services/nsfw-detection-service.ts`. Method `bulkScan(albumId?: string)` calling `POST /NsfwDetection/bulk-scan`.  
  _Verification commands:_  
  - `npm run check`

- [ ] T-045-22 – Add NSFW badge/tag to Moderation admin view (FR-045-13, Q-045-10 → B).  
  _Intent:_ In `resources/js/views/admin/Moderation.vue`, display an NSFW badge/tag on photos where `nsfw_visibility` is `blocked` or `review`. The badge text should indicate the NSFW state (e.g., "NSFW Blocked", "NSFW Review"). Requires the `ModerationController::list()` response to include `nsfw_visibility` for each photo — update the resource/query accordingly. Both `nsfw_visibility` and `is_validated` should be visible to the admin so they understand why a photo is held.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`  
  - `php artisan test --filter=Moderation`  
  - `make phpstan`

- [ ] T-045-23 – Add translation keys for NSFW settings (22 languages).  
  _Intent:_ Add English translation keys first; other languages as follow-up. Keys for: section title, enable label, preset label, each preset name, action labels, trust level labels, NSFW badge labels for Moderation view.  
  _Verification commands:_  
  - `npm run check`

### I7 – Quality Gates & Documentation

- [ ] T-045-24 – Run full quality gate.  
  _Intent:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`. Fix any issues.  
  _Verification commands:_  
  - All quality gate commands pass.

- [ ] T-045-25 – Update knowledge map and roadmap.  
  _Intent:_ Add NSFW detection modules to `docs/specs/4-architecture/knowledge-map.md`. Add Feature 045 row to `docs/specs/4-architecture/roadmap.md` Active Features table.  
  _Verification commands:_  
  - Manual review.

- [ ] T-045-26 – Update `.env.example` with new env vars.  
  _Intent:_ Add `AI_VISION_NSFW_URL=` and `AI_VISION_NSFW_API_KEY=` to `.env.example` in the AI Vision section.  
  _Verification commands:_  
  - Manual review.

## Notes / TODOs

### Resolved Questions
- **Q-045-01 → B:** `upload_trust_level` snapshotted on `photos` at upload time. T-045-05, T-045-13 updated.
- **Q-045-02 → Custom:** `nsfw_visibility` enum (visible/blocked/review) + `is_validated` combination. Both shown in Moderation panel. T-045-04, T-045-05, T-045-10, T-045-18 updated.
- **Q-045-04 → Subsumed** by Q-045-01. Pipe loads `$state->photo->owner` to snapshot trust level.
- **Q-045-05 → A:** Separate URL (`AI_VISION_NSFW_URL`) and API key (`AI_VISION_NSFW_API_KEY`).
- **Q-045-06 → A (modified):** Store individual detections but only from `block_detected`/`review_detected`/`sensitive_detected`. `all_detected` NOT persisted. `NsfwDetectionTier` has 3 cases (no `DETECTED`). T-045-03, T-045-06, T-045-10 updated.
- **Q-045-07 → A:** Global preset only in v1. Per-album/user override deferred.
- **Q-045-08 → A:** Direct album only marked sensitive.
- **Q-045-09 → B:** Bulk scan includes `NULL` + `failed` by default; `force` param re-scans `completed`. T-045-16, T-045-18 updated.
- **Q-045-10 → B:** NSFW badge/tag added to Moderation view. New T-045-22.
- **Q-045-11 → Custom:** Config category is `AI Vision` (not `mod-nsfw`). T-045-07, T-045-19 updated.
- **Q-045-12 → Custom:** Simple SE gating (`support` middleware, not `support:pro`). T-045-17 updated.

- **Q-045-03 → Custom:** Configurable via `nsfw_sensitive_no_album_action` (skip/moderate). 8th config key. T-045-07, T-045-10, T-045-18 updated. New S-045-23.

### Implementation Notes
- All 12 open questions now resolved. No blockers remain — all increments can proceed.
- Detection deduplication in T-045-10: match by label+bbox across arrays; one row per unique detection with `is_block`/`is_review`/`is_sensitive` booleans merged. `NsfwDetectionTier` enum removed — booleans replace it.
- Config migration now has 8 keys (was 7).
