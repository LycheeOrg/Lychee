# Feature Plan 045 – NSFW Detection & Moderation

_Linked specification:_ `docs/specs/4-architecture/features/045-nsfw-moderation/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-06-21

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md).

## Vision & Success Criteria

Enable automated NSFW content detection for Lychee photo uploads using the Lychee-NSFW-Classification sidecar service. Success: photos scanned asynchronously on upload, configurable actions applied per severity tier, all detections audit-logged, admin can bulk-scan and review NSFW-flagged content.

**Measurable signals:**
- NSFW scan dispatched within 1s of photo upload completion (async job).
- Callback results processed and actions applied within 500ms.
- Zero upload latency increase (NFR-045-01).
- All detection tiers logged with full metadata.

## Scope Alignment

- **In scope:**
  - Backend: enums, migration, model, service, job, pipe, controller, request classes, routes.
  - Config: 7 new config keys via migration, 2 new `.env` variables.
  - Admin Settings UI: NSFW settings section.
  - Admin Maintenance UI: Bulk NSFW Scan card.
  - Feature tests for callback endpoint and action logic.
  - Unit tests for enums, service, pipe.

- **Out of scope:**
  - NSFW classifier Python service (separate repo).
  - Frontend photo detail view changes (no NSFW badge/overlay).
  - Per-album or per-user preset overrides (Q-045-07).
  - Docker/deployment documentation (deferred how-to guide).
  - Moderation admin page enhancements for NSFW-specific filtering (follow-up).

## Dependencies & Interfaces

| Dependency | Type | Notes |
|------------|------|-------|
| Lychee-NSFW-Classification service | External | Must be running and accessible. Shared Docker volume for photo access. |
| Feature 033 – Upload Trust Level | Internal | `upload_trust_level` column on `users` table. `UserUploadTrustLevel` enum. |
| `is_validated` column on `photos` | Internal | Used by moderation to hide photos from public views. |
| `is_nsfw` column on `base_albums` | Internal | Used to mark albums as sensitive. |
| Face detection architecture (Feature 030) | Internal | Pattern reference: callback endpoint, API key auth, job dispatch. |
| `config/features.php` | Internal | New env vars for NSFW service URL and API key. |

## Assumptions & Risks

- **Assumptions:**
  - The NSFW classifier service follows the same shared-volume architecture as the face detection service.
  - The callback payload format is stable as described in the spec appendix.
  - The `nsfw_visibility` enum + `is_validated` combination (Q-045-02) provides sufficient visibility control for the action system.
  - The `upload_trust_level` snapshot on photos (Q-045-01 → B) captures trust level at upload time for callback processing.

- **Risks / Mitigations:**
  - NSFW service URL/key misconfigured → dispatch job validates config before HTTP call; logs clear error.
  - High volume of detections on bulk scan → batch dispatch with configurable chunk size (same as face detection).
  - Race between NSFW callback and user viewing photo → async by design; photo visible until action applied. Acceptable for v1.

## Implementation Drift Gate

After I4 (all backend increments complete), run:
1. `make phpstan` — 0 errors.
2. `php artisan test --filter=Nsfw` — all tests green.
3. `vendor/bin/php-cs-fixer fix --dry-run` — 0 changes needed.
4. Cross-check: every FR/scenario in spec has at least one test reference in tasks.md.

## Increment Map

### I1 – Enums & Migration (≤60 min)

- _Goal:_ Create all enums and database migration.
- _Preconditions:_ None.
- _Steps:_
  1. Create `NsfwPreset` enum (`app/Enum/NsfwPreset.php`).
  2. Create `NsfwScanStatus` enum (`app/Enum/NsfwScanStatus.php`).
  3. ~~`NsfwDetectionTier` enum~~ — removed; tier represented by `is_block`/`is_review`/`is_sensitive` boolean columns on `nsfw_detections`.
  4. Create `NsfwBlockAction` enum (`app/Enum/NsfwBlockAction.php`).
  5. Create `NsfwModerationAction` enum (`app/Enum/NsfwModerationAction.php`).
  6. Create `NsfwSensitiveAction` enum (`app/Enum/NsfwSensitiveAction.php`).
  7. Create `NsfwVisibility` enum (`app/Enum/NsfwVisibility.php`) — `VISIBLE`, `BLOCKED`, `REVIEW` (Q-045-02).
  8. Create migration: add `nsfw_scan_status` (nullable string), `nsfw_visibility` (nullable string), `upload_trust_level` (nullable string) to `photos` table (Q-045-01 → B, Q-045-02).
  9. Create migration: `nsfw_detections` table.
  10. Create config migration: 8 new config keys (includes `nsfw_sensitive_no_album_action`, Q-045-03).
- _Commands:_ `make phpstan`
- _Exit:_ Enums compile, migration runs on test DB, PHPStan 0.

### I2 – Config & Service Layer (≤90 min)

- _Goal:_ Add `.env` config entries and create `NsfwDetectionService`.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Add `nsfw-url` and `nsfw-api-key` to `config/features.php` under `ai-vision-service`.
  2. Create `NsfwDetectionService` (`app/Services/Image/NsfwDetectionService.php`) — HTTP client wrapper for `POST /api/nsfw/detect`.
  3. Create `NsfwActionService` (`app/Services/Image/NsfwActionService.php`) — applies configured actions based on `should_block`, `should_review`, `is_sensitive` flags. Sets `nsfw_visibility` (blocked/review) and `is_validated = false` per action config. Reads `upload_trust_level` from photo for auto-approve logic.
  4. Write unit tests for `NsfwDetectionService` (preset omission, request building).
  5. Write unit tests for `NsfwActionService` (all action combinations from S-045-04 to S-045-11, S-045-15, S-045-16, S-045-18).
- _Commands:_ `php artisan test --filter=NsfwDetection`, `make phpstan`
- _Exit:_ Services instantiable, unit tests pass, PHPStan 0.

### I3 – Model & Job (≤60 min)

- _Goal:_ Create `NsfwDetection` model and `DispatchNsfwScanJob`.
- _Preconditions:_ I1, I2 complete.
- _Steps:_
  1. Create `NsfwDetection` model (`app/Models/NsfwDetection.php`) with fillable fields matching `nsfw_detections` table.
  2. Create `DispatchNsfwScanJob` (`app/Jobs/DispatchNsfwScanJob.php`) — dispatches HTTP POST to NSFW service, sets `nsfw_scan_status = pending`.
  3. Write unit tests for job dispatch logic (config check, preset handling, retry).
- _Commands:_ `php artisan test --filter=NsfwScan`, `make phpstan`
- _Exit:_ Model and job compile, tests pass.

### I4 – Upload Pipeline Pipe (≤45 min)

- _Goal:_ Create `AutoScanNsfwOnUpload` standalone pipe.
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Create `AutoScanNsfwOnUpload` pipe (`app/Actions/Photo/Pipes/Standalone/AutoScanNsfwOnUpload.php`) — snapshots `upload_trust_level` on the photo (Q-045-01 → B), checks `ai_vision_enabled` (global) then `ai_vision_nsfw_enabled` (NSFW-specific, mirrors `ai_vision_face_enabled` pattern), trust level, dispatches `DispatchNsfwScanJob`.
  2. Register pipe in the `Create` action's standalone pipe chain (after `AutoScanFacesOnUpload`).
  3. Write unit tests for trust level gating and snapshot (S-045-01, S-045-02, S-045-03, S-045-14).
- _Commands:_ `php artisan test --filter=AutoScanNsfw`, `make phpstan`
- _Exit:_ Pipe registered and tested.

### I5 – Callback Controller & Request (≤90 min)

- _Goal:_ Create the callback endpoint `POST /api/v2/NsfwDetection/results`.
- _Preconditions:_ I2, I3 complete.
- _Steps:_
  1. Create `NsfwDetectionResultsRequest` (`app/Http/Requests/Nsfw/NsfwDetectionResultsRequest.php`) — validates X-API-Key, payload structure.
  2. Create `NsfwDetectionController` (`app/Http/Controllers/AiVision/NsfwDetectionController.php`) with `results()` and `bulkScan()` methods.
  3. Create `BulkNsfwScanRequest` (`app/Http/Requests/Nsfw/BulkNsfwScanRequest.php`).
  4. Register routes in `routes/api_v2.php`.
  5. Write feature tests: valid callback, invalid API key (403), error status, each action combination, bulk scan admin gate (S-045-04 to S-045-13, S-045-17).
- _Commands:_ `php artisan test --filter=NsfwDetection`, `make phpstan`
- _Exit:_ Endpoint responds correctly to all test scenarios.

### I6 – Frontend Settings & Maintenance (≤60 min)

- _Goal:_ Add NSFW settings to admin Settings page and bulk scan card to Maintenance.
- _Preconditions:_ I5 complete (config keys exist).
- _Steps:_
  1. Add NSFW Detection section to Settings view (toggles, dropdowns per mock-up UI-045-01).
  2. Add `MaintenanceBulkScanNsfw` component to Maintenance page (UI-045-02).
  3. Create `nsfw-detection-service.ts` frontend service.
  4. Add translation keys for NSFW settings labels (22 languages — English first, others as follow-up).
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Settings section renders, bulk scan button dispatches request.

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
| S-045-01 | I4 / T-045-10 | Upload pipe: check user → scan dispatched |
| S-045-02 | I4 / T-045-10 | Upload pipe: trusted + scan_trusted=true → scan |
| S-045-03 | I4 / T-045-10 | Upload pipe: trusted + scan_trusted=false → skip |
| S-045-04 | I5 / T-045-14 | Callback: should_block + action=block |
| S-045-05 | I5 / T-045-14 | Callback: should_block + action=nothing |
| S-045-06 | I5 / T-045-14 | Callback: should_review + action=moderate |
| S-045-07 | I5 / T-045-14 | Callback: should_review + action=block |
| S-045-08 | I5 / T-045-14 | Callback: should_review + action=nothing |
| S-045-09 | I5 / T-045-14 | Callback: is_sensitive + action=sensitive |
| S-045-10 | I5 / T-045-14 | Callback: is_sensitive + action=moderate |
| S-045-11 | I5 / T-045-14 | Callback: is_sensitive + action=nothing |
| S-045-12 | I5 / T-045-14 | Callback: error status → failed |
| S-045-13 | I5 / T-045-14 | Callback: invalid API key → 403 |
| S-045-14 | I4 / T-045-10 | Upload pipe: nsfw_enabled=false → skip |
| S-045-15 | I5 / T-045-14 | Callback: review + auto_approve + trusted → skip |
| S-045-16 | I5 / T-045-14 | Callback: block + auto_approve + trusted → block |
| S-045-17 | I5 / T-045-14 | Bulk scan |
| S-045-18 | I5 / T-045-14 | Sensitive + no album + no_album_action=skip → warning |
| S-045-19 | I3 / T-045-08 | Preset default → omit field |
| S-045-22 | I5 / T-045-14 | Bulk scan with force=true → re-scan completed |
| S-045-23 | I5 / T-045-14 | Sensitive + no album + no_album_action=moderate → review |
| S-045-24 | I4 / T-045-13 | ai_vision_enabled=false → no NSFW scan even if ai_vision_nsfw_enabled=true |
| S-045-20 | I3 / T-045-08 | Preset strict → include field |
| S-045-21 | I5 / T-045-14 | Detection logging with tier dedup |

## Analysis Gate

_Not yet completed. Run after I1–I5 are implemented._

## Exit Criteria

- [ ] All 6 enums compile and are used in config/request validation.
- [ ] Migration creates `nsfw_detections` table, adds `nsfw_scan_status`, `nsfw_visibility`, and `upload_trust_level` to `photos`.
- [ ] 8 config keys inserted by migration.
- [ ] `POST /api/v2/NsfwDetection/results` endpoint handles all 21 scenarios.
- [ ] `POST /api/v2/NsfwDetection/bulk-scan` endpoint gated to admin.
- [ ] Upload pipe dispatches scan with correct trust level gating.
- [ ] PHPStan 0 errors, php-cs-fixer 0 changes, all tests green.
- [ ] Knowledge map and roadmap updated.
- [ ] Admin Settings UI renders NSFW section.
- [ ] Admin Maintenance UI renders bulk scan card.

## Follow-ups / Backlog

1. **Moderation page NSFW filter** — Add a toggle to the Moderation admin page to filter by `nsfw_blocked = true` vs general `is_validated = false`.
2. **NSFW detection detail view** — Show detection labels/bboxes on photo detail for admin.
3. **Per-album preset override** — Allow album-level preset configuration (Q-045-07).
4. **How-to guide** — `docs/specs/2-how-to/configure-nsfw-detection.md`.
5. **Retry mechanism** — Artisan command to retry all `nsfw_scan_status = failed` photos.
6. **NSFW stats on admin dashboard** — Count of blocked/moderated/sensitive photos.
