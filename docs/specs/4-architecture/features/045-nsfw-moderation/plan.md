# Feature Plan 045 – NSFW Detection & Moderation

_Linked specification:_ `docs/specs/4-architecture/features/045-nsfw-moderation/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-06-22

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md).

## Vision & Success Criteria

Enable automated NSFW content detection for Lychee photo uploads using the Lychee-NSFW-Classification sidecar service. Success: photos scanned asynchronously on upload, configurable actions applied per trust-tier × finding-tier matrix, all detections audit-logged, admin can bulk-scan and review NSFW-flagged content. A new `trust_but_verify` user trust tier bridges the gap between `monitor` and `trusted`.

**Measurable signals:**
- NSFW scan dispatched within 1s of photo upload completion (async job).
- Callback results processed and actions applied within 500ms.
- Zero upload latency increase (NFR-045-01).
- All detection tiers logged with full metadata.
- Block actions (photo deletion) complete within 1s of callback.

## Scope Alignment

- **In scope:**
  - Backend: enums (including `NsfwStatus`, `NsfwDetectionLabel`), migration, model, service, job, pipe, controller, request classes, routes.
  - New `TRUST_BUT_VERIFY` case on `UserUploadTrustLevel` enum.
  - Config: 12 new config keys via migration (9 original + 3 hide-on-scan), 2 new `.env` variables.
  - Trust-tier × finding-tier action matrix implementation.
  - Approval-time album sensitivity marking with recursive NSFW check.
  - Admin Settings UI: NSFW settings section with trust-tier matrix summary.
  - Admin Maintenance UI: Bulk NSFW Scan card.
  - Admin Moderation page: hide blocked findings by default, add toggle.
  - Feature tests for callback endpoint, action logic, and approval-time album marking.
  - Unit tests for enums, service, pipe.

- **Out of scope:**
  - NSFW classifier Python service (separate repo).
  - Frontend photo detail view changes (no NSFW badge/overlay).
  - Per-album or per-user preset overrides (Q-045-07).
  - Docker/deployment documentation (deferred how-to guide).

## Dependencies & Interfaces

| Dependency | Type | Notes |
|------------|------|-------|
| Lychee-NSFW-Classification service | External | Must be running and accessible. Shared Docker volume for photo access. |
| Feature 033 – Upload Trust Level | Internal | `upload_trust_level` column on `users` table. `UserUploadTrustLevel` enum — extended with `TRUST_BUT_VERIFY`. Note: the uploader's trust level (from the DTO) may differ from the photo owner's — when User A uploads to User B's album, `photo.owner_id = B` but the trust level is A's. |
| `is_validated` column on `photos` | Internal | Used by moderation to hide photos from public views. Combined with `nsfw_status` for full moderation context. |
| `is_nsfw` column on `base_albums` | Internal | Used to mark albums as sensitive. |
| `is_recursive_nsfw` virtual column | Internal | AlbumBuilder query — checks if any ancestor album is NSFW. Used to avoid redundant marking. |
| Face detection architecture (Feature 030) | Internal | Pattern reference: callback endpoint, API key auth, job dispatch. |
| `config/features.php` | Internal | New env vars for NSFW service URL and API key. |
| `SetUploadValidated` pipe | Internal | Must be updated to handle `TRUST_BUT_VERIFY` (validated = true, like `MONITOR`/`TRUSTED`). |
| `ModerationController` | Internal | Must be updated for approval-time album marking and `nsfw_status` display. |
| `useAdminTiles.ts` | Internal | Admin dashboard tile for NSFW Config page. |

## Assumptions & Risks

- **Assumptions:**
  - The NSFW classifier service follows the same shared-volume architecture as the face detection service.
  - The callback payload format is stable as described in the spec appendix.
  - The trust-tier × finding-tier matrix provides sufficient granularity for all admin use cases.
  - The `upload_trust_level` snapshot on photos (Q-045-01 → B) captures trust level at upload time for callback processing.
  - Hard-deleting photos on block action is the desired default; admins who want to review block-tier content can switch to `moderate`.

- **Risks / Mitigations:**
  - NSFW service URL/key misconfigured → dispatch job validates config before HTTP call; logs clear error.
  - High volume of detections on bulk scan → batch dispatch with configurable chunk size (same as face detection).
  - Race between NSFW callback and user viewing photo → async by design; photo visible until action applied. Mitigated by optional hide-on-scan configs (FR-045-19) that hide photos during scan at the cost of photos staying hidden if the classifier crashes.
  - Hard-delete on block is irreversible → config defaults to `block` but admin can switch to `moderate` before enabling.
  - Hide-on-scan classifier crash → photos remain in moderation queue indefinitely → setting is opt-in (default off) with UI warning. Admins who enable it accept the trade-off.

## Implementation Drift Gate

After I4 (all backend increments complete), run:
1. `make phpstan` — 0 errors.
2. `php artisan test --filter=Nsfw` — all tests green.
3. `vendor/bin/php-cs-fixer fix --dry-run` — 0 changes needed.
4. Cross-check: every FR/scenario in spec has at least one test reference in tasks.md.

## Increment Map

### I1 – Enums, Trust Level & Migration (≤90 min)

- _Goal:_ Create all enums, add `TRUST_BUT_VERIFY` to `UserUploadTrustLevel`, create database migrations.
- _Preconditions:_ None.
- _Steps:_
  1. Add `TRUST_BUT_VERIFY = 'trust_but_verify'` case to `UserUploadTrustLevel` enum.
  2. Update `SetUploadValidated` pipe: `trust_but_verify` → `is_validated = true` (same as `monitor`/`trusted`).
  3. Create `NsfwPreset` enum (`app/Enum/NsfwPreset.php`).
  4. Create `NsfwStatus` enum (`app/Enum/NsfwStatus.php`) — `PENDING`, `FAILED`, `REVIEW`, `VISIBLE`. Single enum replacing both scan status and visibility tracking.
  5. Create `NsfwBlockFindingAction` enum (`app/Enum/NsfwBlockFindingAction.php`) — `BLOCK`, `MODERATE`.
  6. Create `NsfwSensitiveAlbumAction` enum (`app/Enum/NsfwSensitiveAlbumAction.php`) — `MARK_ALBUM`, `NOTHING`.
  7. Create `NsfwSensitiveNoAlbumAction` enum (`app/Enum/NsfwSensitiveNoAlbumAction.php`) — `SKIP`, `MODERATE`.
  8. Create `NsfwDetectionLabel` enum (`app/Enum/NsfwDetectionLabel.php`) — 18 classifier labels: `FEMALE_GENITALIA_COVERED`, `FACE_FEMALE`, `BUTTOCKS_EXPOSED`, `FEMALE_BREAST_EXPOSED`, `FEMALE_GENITALIA_EXPOSED`, `MALE_BREAST_EXPOSED`, `ANUS_EXPOSED`, `FEET_EXPOSED`, `BELLY_COVERED`, `FEET_COVERED`, `ARMPITS_COVERED`, `ARMPITS_EXPOSED`, `FACE_MALE`, `BELLY_EXPOSED`, `MALE_GENITALIA_EXPOSED`, `ANUS_COVERED`, `FEMALE_BREAST_COVERED`, `BUTTOCKS_COVERED`.
  9. Create migration: add `nsfw_status` and `upload_trust_level` to `photos` table.
  10. Create migration: `nsfw_detections` table.
  11. Create config migration: 12 new config keys (9 original + 3 hide-on-scan: `nsfw_monitor_hide_on_scan`, `nsfw_trust_but_verify_hide_on_scan`, `nsfw_trust_hide_on_scan`).
  12. Update TypeScript type `UserUploadTrustLevel` to include `trust_but_verify`.
- _Commands:_ `make phpstan`
- _Exit:_ Enums compile (including `NsfwStatus` and `NsfwDetectionLabel`), migration runs on test DB, `SetUploadValidated` handles new tier, PHPStan 0.

### I2 – Config & Service Layer (≤90 min)

- _Goal:_ Add `.env` config entries and create services.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add `nsfw-url` and `nsfw-api-key` to `config/features.php` under `ai-vision-service`.
  2. Create `NsfwDetectionService` (`app/Services/Image/NsfwDetectionService.php`) — HTTP client wrapper for `POST /api/nsfw/detect`. Queries use `nsfw_status` column for dispatch eligibility.
  3. Create `NsfwActionService` (`app/Services/Image/NsfwActionService.php`) — implements the trust-tier × finding-tier matrix. Reads `upload_trust_level` from photo, applies configured actions. Sets `nsfw_status` to `review` or `visible` based on matrix outcome. For block findings: reads per-trust-level config (`nsfw_check_block_action`, `nsfw_monitor_block_action`, `nsfw_trust_but_verify_block_action`, or `nsfw_trust_block_action`); block action delegates to the force-delete method on `Actions\Photo\Delete` (Q-045-14 → A). For album marking on non-moderated tiers: iterates all of the photo's associated albums (Q-045-13 → A), checks `is_recursive_nsfw` per album before marking. Album marking is triggered by a `ApplyNsfwAlbumSensitivityJob` dispatched on auto-approval or admin approval. **Hide-on-scan restore (FR-045-19):** after applying all actions, if no block/review findings triggered moderation and the photo's `upload_trust_level` is not `check`, unconditionally sets `is_validated = true` (no-op when hide-on-scan was off; restores visibility when it was on).
  4. Create `NsfwConfigController` (`app/Http/Controllers/AiVision/NsfwConfigController.php`) — single `show()` method that proxies `GET /api/nsfw/config` from the external NSFW classification service. Auth: admin session (within `feature:ai-vision` + `feature:v8` middleware group). Reads `nsfw-url` and `nsfw-api-key` from `config/features.php`. Returns upstream JSON as-is on success; 503 with error on misconfiguration or connectivity failure.
  5. Register route `GET /NsfwDetection/config` in `routes/api_v2.php` within the existing AI Vision middleware group.
  6. Write unit tests for `NsfwDetectionService` (preset omission, request building).
  7. Write unit tests for `NsfwActionService` (all matrix combinations from S-045-04 to S-045-19).
- _Commands:_ `php artisan test --filter=NsfwDetection`, `php artisan test --filter=NsfwConfig`, `make phpstan`
- _Exit:_ Services instantiable, config proxy endpoint returns upstream JSON, unit tests pass, PHPStan 0.

### I3 – Model & Jobs (≤90 min)

- _Goal:_ Create `NsfwDetection` model, `DispatchNsfwScanJob`, and `ApplyNsfwAlbumSensitivityJob`.
- _Preconditions:_ I1, I2 complete.
- _Steps:_
  1. Create `NsfwDetection` model (`app/Models/NsfwDetection.php`) with fillable fields matching `nsfw_detections` table.
  2. Create `DispatchNsfwScanJob` (`app/Jobs/DispatchNsfwScanJob.php`) — dispatches HTTP POST to NSFW service, sets `nsfw_status = pending`.
  3. Create `ApplyNsfwAlbumSensitivityJob` (`app/Jobs/ApplyNsfwAlbumSensitivityJob.php`) — iterates **all** of the photo's associated albums (`$photo->albums` via `photo_album` pivot, Q-045-13 → A), and for each album checks `is_recursive_nsfw`, sets `album.is_nsfw = true` if appropriate. Handles no-album fallback (`$photo->albums->isEmpty()`) via `nsfw_sensitive_no_album_action` (`skip` = log warning; `moderate` = set `nsfw_status = review`, `is_validated = false`). Dispatched from two places: (a) `NsfwActionService` at callback time for auto-approved tiers, (b) `ModerationController::approve()` for admin-approved `check` photos.
  4. Add a `forceDeletePhoto(string $photo_id)` method to `Actions\Photo\Delete` (Q-045-14 → A) — removes all `photo_album` pivot entries, cleans up purchasables, fires `PhotoWillBeDeleted`/`PhotoDeleted` events, delegates to `PhotosToBeDeletedDTO` for size variant file cleanup and record deletion. Bypasses the album-context requirement of `Delete::do()`.
  5. Update `PhotosToBeDeletedDTO::forceDelete()` to explicitly delete `nsfw_detections` rows alongside existing `size_variants`, `statistics`, `palettes`, and `photo_album` cleanup (Q-045-19).
  6. Write unit tests for job dispatch logic (config check, preset handling, retry).
  7. Write unit tests for `ApplyNsfwAlbumSensitivityJob` (recursive NSFW check, no-album fallback, multi-album marking).
- _Commands:_ `php artisan test --filter=NsfwScan`, `php artisan test --filter=ApplyNsfwAlbum`, `make phpstan`
- _Exit:_ Model and jobs compile, tests pass.

### I4 – Upload Pipeline Pipe (≤45 min)

- _Goal:_ Create `AutoScanNsfwOnUpload` standalone pipe.
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Create `AutoScanNsfwOnUpload` pipe — **before `$next()`** (Q-045-18 → B): snapshots `upload_trust_level` on the in-memory photo model and, if hide-on-scan applies, sets `is_validated = false`. These values are persisted by the pipeline — no extra `save()` needed and no visibility gap. **After `$next()`**: checks global + NSFW-specific toggles, dispatches scan. `check`/`monitor`/`trust_but_verify` always scan; `trusted` only if `nsfw_scan_trusted_users = true`. **Hide-on-scan (FR-045-19):** if scan will be dispatched, check per-trust-level hide-on-scan config (`nsfw_monitor_hide_on_scan`, `nsfw_trust_but_verify_hide_on_scan`, `nsfw_trust_hide_on_scan`); if true, `is_validated = false` was already set before `$next()`. `check` users are already hidden via `SetUploadValidated` so this is skipped for them.
  2. Register pipe in the `Create` action's standalone pipe chain (after `AutoScanFacesOnUpload`).
  3. Write unit tests for all trust level branches including `trust_but_verify` and hide-on-scan on/off.
- _Commands:_ `php artisan test --filter=AutoScanNsfw`, `make phpstan`
- _Exit:_ Pipe registered and tested.

### I5 – Callback Controller, Approval Logic & Routes (≤120 min)

- _Goal:_ Create callback endpoint, approval-time album marking, and routes.
- _Preconditions:_ I2, I3 complete.
- _Steps:_
  1. Create `NsfwDetectionResultsRequest` — validates X-API-Key, payload structure.
  2. Create `NsfwDetectionController` with `results()` and `bulkScan()` methods.
  3. Create `BulkNsfwScanRequest`.
  4. Register routes in `routes/api_v2.php`.
  5. Add `'/api/v2/NsfwDetection/results'` to `VerifyCsrfToken::$except` (Q-045-15 — matching the face detection callback pattern).
  6. Update `ModerationController::approve()` — **hybrid two-pass approach (Q-045-16 → B)**: first, keep the existing bulk `Photo::whereIn('id', $chunk)->update(['is_validated' => true])`. Then, separately query for photos among the approved IDs that had `nsfw_status = review`: update their `nsfw_status` to `visible`, and for those with sensitive detections (`is_sensitive = true`) where `nsfw_sensitive_album_action = mark_album`, dispatch `ApplyNsfwAlbumSensitivityJob`.
  7. Write feature tests for all callback scenarios (S-045-04 to S-045-30).
  8. Write feature tests for approval-time album marking (S-045-34, S-045-35).
- _Commands:_ `php artisan test --filter=NsfwDetection`, `make phpstan`
- _Exit:_ Endpoint responds correctly to all test scenarios. Approval triggers album marking.

### I6 – Frontend Settings, Maintenance & Moderation (≤90 min)

- _Goal:_ Add NSFW settings to admin Settings page, bulk scan card to Maintenance, and filtering to Moderation.
- _Preconditions:_ I5 complete (config keys exist).
- _Steps:_
  1. Add NSFW Detection section to Settings view (toggles, dropdowns, 3 hide-on-scan toggles with warning note, trust-tier matrix summary per mock-up UI-045-01).
  2. Add `MaintenanceBulkScanNsfw` component to Maintenance page (UI-045-02).
  3. Create `nsfw-detection-service.ts` frontend service.
  4. Update Moderation page: add `nsfw_status` column/badge (UI-045-03). No blocked filter needed — block actions hard-delete photos.
  5. Update `ModerationResource` to include `nsfw_status`.
  6. Update `ModerationController::list()` — no blocked filtering needed (deleted photos have no rows).
  7. Create `nsfw-config-service.ts` — frontend service with `getConfig()` method calling `GET /NsfwDetection/config`.
  8. Create `NsfwConfig.vue` admin page (`resources/js/views/admin/NsfwConfig.vue`) — fetches upstream config via `nsfw-config-service.ts`, displays: (a) service runtime config as a key-value table (excluding JSON-encoded fields `block`/`review`/`sensitive`), (b) each preset as a card showing name, description, and the three label-set tiers (block/review/sensitive) with their labels rendered as tag chips. Loading spinner while fetching; error panel when service unreachable. Refresh button to re-fetch. Per mockup UI-045-04.
  9. Register frontend route `/admin/nsfw-config` → `NsfwConfig.vue` in `resources/js/router/routes.ts`.
  10. Add admin dashboard tile for NSFW Config in `useAdminTiles.ts` — visible when `is_ai_vision_enabled` and `can_edit`. Icon: `pi pi-eye`, group: `extensions`.
  11. Add translation keys for NSFW settings labels and presets overview page (English first, others as follow-up).
  12. Update TypeScript type for `UserUploadTrustLevel` if not done in I1.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Settings section renders with matrix, moderation page shows NSFW status badge, presets overview page loads and displays upstream config.

### I7 – Quality Gates & Documentation (≤30 min)

- _Goal:_ Final quality checks, knowledge map update, roadmap update.
- _Preconditions:_ I1–I6 complete.
- _Steps:_
  1. Run full quality gate: `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`.
  2. Update `docs/specs/4-architecture/knowledge-map.md`.
  3. Update `docs/specs/4-architecture/roadmap.md`.
  4. Update `.env.example` with new env vars.
- _Commands:_ All quality gate commands.
- _Exit:_ All gates green, documentation current.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-045-01 | I4 / T-045-13 | Upload pipe: check user → scan dispatched |
| S-045-02 | I4 / T-045-13 | Upload pipe: trusted + scan_trusted=true → scan |
| S-045-03 | I4 / T-045-13 | Upload pipe: trusted + scan_trusted=false → skip |
| S-045-04 | I5 / T-045-18 | Callback: check + block + check_block_action=block → delete |
| S-045-05 | I5 / T-045-18 | Callback: check + block + check_block_action=moderate → review |
| S-045-06 | I5 / T-045-18 | Callback: check + review → moderate |
| S-045-07 | I5 / T-045-18 | Callback: monitor + block + monitor_block_action=block → delete |
| S-045-08 | I5 / T-045-18 | Callback: monitor + block + monitor_block_action=moderate → review |
| S-045-09 | I5 / T-045-18 | Callback: monitor + review → moderate |
| S-045-10 | I5 / T-045-18 | Callback: trust_but_verify + block + moderate → review |
| S-045-10b | I5 / T-045-18 | Callback: trust_but_verify + block + block → hard-delete |
| S-045-11 | I5 / T-045-18 | Callback: trust_but_verify + review → approve |
| S-045-12 | I5 / T-045-18 | Callback: trusted + block + approve → no action |
| S-045-12b | I5 / T-045-18 | Callback: trusted + block + moderate → review |
| S-045-12c | I5 / T-045-18 | Callback: trusted + block + block → hard-delete |
| S-045-13 | I5 / T-045-18 | Callback: trusted + review → approve |
| S-045-14 | I5 / T-045-18 | Callback: check + sensitive → moderate; album marked at approval |
| S-045-15 | I5 / T-045-18 | Callback: monitor + sensitive + mark_album → immediate album mark |
| S-045-16 | I5 / T-045-18 | Callback: monitor + sensitive + nothing → no action |
| S-045-17 | I5 / T-045-18 | Callback: trust_but_verify + sensitive + mark_album → immediate album mark |
| S-045-18 | I5 / T-045-18 | Callback: trusted + sensitive + mark_album → immediate album mark |
| S-045-19 | I5 / T-045-18 | Album already recursive NSFW → skip marking |
| S-045-20 | I5 / T-045-18 | Callback: error status → failed |
| S-045-21 | I5 / T-045-18 | Callback: invalid API key → 403 |
| S-045-22 | I4 / T-045-13 | Upload pipe: nsfw_enabled=false → skip |
| S-045-23 | I4 / T-045-13 | Upload pipe: ai_vision_enabled=false → skip |
| S-045-24 | I5 / T-045-18 | Bulk scan (default) |
| S-045-25 | I5 / T-045-18 | Bulk scan with force=true |
| S-045-26 | I5 / T-045-18 | Sensitive + no album + skip → warning |
| S-045-27 | I5 / T-045-18 | Sensitive + no album + moderate → review |
| S-045-28 | I3 / T-045-12 | Preset default → omit field |
| S-045-29 | I3 / T-045-12 | Preset strict → include field |
| S-045-30 | I5 / T-045-18 | Detection logging with tier dedup |
| S-045-31 | I4 / T-045-13 | trust_but_verify user → is_validated=true, scan dispatched |
| ~~S-045-32~~ | — | Removed — no blocked rows to hide |
| ~~S-045-33~~ | — | Removed — no blocked toggle needed |
| S-045-34 | I5 / T-045-19 | Approval: sensitive + mark_album + not recursive → album marked |
| S-045-35 | I5 / T-045-19 | Approval: sensitive + album recursive NSFW → skip |
| S-045-36 | I2 / T-045-10b | Config proxy: admin fetches → upstream JSON returned |
| S-045-37 | I2 / T-045-10b | Config proxy: URL not configured → 503 |
| S-045-38 | I2 / T-045-10b | Config proxy: service unreachable → 503 |
| S-045-39 | I4 / T-045-15, I5 / T-045-21 | Hide-on-scan: monitor + config on + clean callback → is_validated restored |
| S-045-40 | I4 / T-045-15, I5 / T-045-21 | Hide-on-scan: trust_but_verify + config on + clean callback → is_validated restored |
| S-045-41 | I4 / T-045-15, I5 / T-045-21 | Hide-on-scan: trusted + config on + clean callback → is_validated restored |
| S-045-42 | I5 / T-045-21 | Hide-on-scan: monitor + config on + block/review findings → is_validated stays false |
| S-045-43 | I4 / T-045-15 | Hide-on-scan: monitor + config off → is_validated unchanged (true) |

## Analysis Gate

_Not yet completed. Run after I1–I5 are implemented._

## Exit Criteria

- [ ] All enums compile and are used in config/request validation (`NsfwStatus`, `NsfwDetectionLabel`, `NsfwPreset`, action enums).
- [ ] `UserUploadTrustLevel` has 4 cases including `TRUST_BUT_VERIFY`.
- [ ] `SetUploadValidated` handles `trust_but_verify` as validated.
- [ ] Migration creates `nsfw_detections` table (with `NsfwDetectionLabel`-typed `label`), adds `nsfw_status` and `upload_trust_level` to `photos`.
- [ ] 12 config keys inserted by migration (9 original + 3 hide-on-scan).
- [ ] Trust-tier × finding-tier matrix fully implemented in `NsfwActionService`.
- [ ] Block action hard-deletes photos via `Delete::forceDeletePhoto()` (Q-045-14 → A), removing from all albums (no `blocked` status row).
- [ ] Album marking iterates all associated albums via `photo_album` pivot (Q-045-13 → A), respects recursive NSFW check per album.
- [ ] Album marking for `check` users deferred to approval time.
- [ ] `nsfw_detections` FK uses `cascadeOnDelete`; `PhotosToBeDeletedDTO::forceDelete()` explicitly cleans up `nsfw_detections` (Q-045-19).
- [ ] `POST /api/v2/NsfwDetection/results` endpoint handles all scenarios; CSRF-exempted in `VerifyCsrfToken` (Q-045-15).
- [ ] `POST /api/v2/NsfwDetection/bulk-scan` endpoint gated to admin.
- [ ] Upload pipe dispatches scan with correct trust level gating (4 tiers).
- [ ] Hide-on-scan configs (`nsfw_monitor_hide_on_scan`, `nsfw_trust_but_verify_hide_on_scan`, `nsfw_trust_hide_on_scan`) hide photos at dispatch and restore `is_validated = true` on clean callback.
- [ ] PHPStan 0 errors, php-cs-fixer 0 changes, all tests green.
- [ ] Knowledge map and roadmap updated.
- [ ] Admin Settings UI renders NSFW section with trust-tier matrix summary.
- [ ] Admin Maintenance UI renders bulk scan card.
- [ ] `GET /api/v2/NsfwDetection/config` proxies upstream config and returns presets JSON.
- [ ] NSFW Presets overview page renders service config and preset cards at `/admin/nsfw-config`.

## Follow-ups / Backlog

1. **NSFW detection detail view** — Show detection labels/bboxes on photo detail for admin.
2. **Per-album preset override** — Allow album-level preset configuration (Q-045-07).
3. **How-to guide** — `docs/specs/2-how-to/configure-nsfw-detection.md`.
4. **Retry mechanism** — Artisan command to retry all `nsfw_status = failed` photos.
5. **NSFW stats on admin dashboard** — Count of blocked/moderated/sensitive photos.
6. **Soft-delete option for block action** — Alternative to hard-delete for admins who want recoverability.
