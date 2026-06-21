# Feature 045 – NSFW Detection & Moderation

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-06-21 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/045-nsfw-moderation/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/045-nsfw-moderation/tasks.md` |
| Roadmap entry | #045 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

Add automated NSFW content detection to Lychee via integration with the external [Lychee-NSFW-Classification](https://github.com/LycheeOrg/Lychee-NSFW-Classification) Python service. Following the same REST + webhook architecture as face detection (Feature 030), Lychee dispatches NSFW scan requests after photo upload and receives classification results via a callback endpoint. Affected modules: backend (controllers, jobs, pipes, models, config, enums), admin settings UI, moderation admin page. The feature leverages the existing `is_nsfw` album flag and `is_validated` photo flag, extends the user trust level system (Feature 033), and introduces configurable actions (block, flag for moderation, mark sensitive) based on detection severity tiers. A new `nsfw_visibility` enum column on `photos` (values: `visible`, `blocked`, `review`) tracks NSFW-specific state independently from `is_validated`; both columns are displayed in the Moderation admin panel. The uploader's trust level is snapshotted on the photo at upload time (`upload_trust_level` column) so the callback can resolve trust-level-aware actions without relying on the user's current trust level (Q-045-01 → Option B).

## Goals

1. Integrate with the Lychee-NSFW-Classification service to automatically scan uploaded photos for NSFW content.
2. Provide a configurable preset system (6 presets) controlling detection sensitivity at the installation level.
3. Allow admins to configure per-tier actions: block, moderate, or mark sensitive based on detection results.
4. Integrate with the existing user trust level system to determine when NSFW scanning is applied.
5. Log all NSFW detections for audit and admin review.
6. Extend the existing Moderation admin page to surface NSFW-flagged photos alongside manually-moderated uploads.

## Non-Goals

- Real-time/synchronous NSFW scanning during upload (scanning is async via job queue).
- Any UI changes to the photo upload flow (no blocking modal or progress indicator for NSFW scanning).
- Video NSFW detection (photos only, matching face detection scope).
- Custom per-album or per-user preset overrides (global preset only; see Q-045-07).
- Training or fine-tuning the NSFW classification model.
- Frontend NSFW detection result display on photo detail view (deferred).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|---------------------|--------|
| FR-045-01 | On photo upload, if AI Vision is globally enabled (`ai_vision_enabled = 1`) AND NSFW scanning is enabled (`ai_vision_nsfw_enabled = 1`) and the user's trust level requires scanning, dispatch a `DispatchNsfwScanJob` to POST the photo to the NSFW classification service. This mirrors the face detection pattern which checks `ai_vision_enabled` then `ai_vision_face_enabled`. | Job dispatches `POST /api/nsfw/detect` with `photo_id`, `photo_path`, and optionally `preset` (omitted when global preset is `default`). Photo `nsfw_scan_status` set to `pending`. | `photo_id` must reference a valid photo; `photo_path` must be a resolvable path on the shared volume. | Service unreachable or returns error → `nsfw_scan_status = failed`, log error. Retry with backoff (3 attempts). | `nsfw.scan.dispatched`, `nsfw.scan.failed` | Owner directive |
| FR-045-02 | The callback endpoint `POST /api/v2/NsfwDetection/results` accepts classification results from the NSFW service, authenticated via `X-API-Key` header against `AI_VISION_NSFW_API_KEY`. | Payload validated, detections logged to `nsfw_detections` table, action flags (`should_block`, `should_review`, `is_sensitive`) processed. `nsfw_scan_status = completed`. | API key must match config value; `photo_id` must exist; `status` must be `success` or `error`. | Invalid API key → 403. Unknown `photo_id` → log warning, 200. Malformed payload → 422. | `nsfw.results.received`, `nsfw.results.error` | Owner directive |
| FR-045-03 | Global config `nsfw_preset` determines which preset is sent to the classifier. Stored as a config key with enum values: `default`, `strict`, `moderation`, `nude_female`, `permissive`, `social_media`. When set to `default`, the `preset` field is omitted from the request to the classifier. | Preset value read from configs at dispatch time and included/excluded accordingly. | Config value must be one of the 6 valid enum values. | Invalid preset value → fall back to `default` behaviour (omit field). | — | Owner directive |
| FR-045-04 | Configurable block action: global config `nsfw_action_block` determines response to `should_block = true`. Options: `block` (set `nsfw_visibility = blocked` and `is_validated = false`) or `nothing` (ignore). | When action is `block`: photo `nsfw_visibility = blocked`, `is_validated = false`. Photo hidden from public views until admin approves. | — | — | `nsfw.action.block` | Owner directive, Q-045-02 |
| FR-045-05 | Configurable moderation action: global config `nsfw_action_moderation` determines response to `should_review = true`. Options: `block` (set `nsfw_visibility = blocked`, `is_validated = false`), `moderate` (set `nsfw_visibility = review`, `is_validated = false`), `nothing` (ignore). | When `moderate`: `nsfw_visibility = review`, `is_validated = false`. When `block`: `nsfw_visibility = blocked`, `is_validated = false`. | — | — | `nsfw.action.moderate` | Owner directive, Q-045-02 |
| FR-045-06 | Configurable sensitive action: global config `nsfw_action_sensitive` determines response to `is_sensitive = true`. Options: `moderate` (set `nsfw_visibility = review`, `is_validated = false`), `sensitive` (set containing album's `is_nsfw = true`), `nothing` (ignore). | When `sensitive`: look up photo's direct album and set `is_nsfw = true`. When `moderate`: `nsfw_visibility = review`, `is_validated = false`. | If photo has no album (unsorted) and action is `sensitive`, behaviour is controlled by `nsfw_sensitive_no_album_action` config (Q-045-03): `skip` logs warning and does nothing; `moderate` falls back to `nsfw_visibility = review`, `is_validated = false`. | — | `nsfw.action.sensitive` | Owner directive, Q-045-02, Q-045-03 |
| FR-045-07 | User trust level integration: users with `upload_trust_level = check` or `monitor` always have NSFW scanning applied on upload. Users with `upload_trust_level = trusted` consult global config `nsfw_scan_trusted_users` (boolean) to determine if scanning is applied. The uploader's trust level is snapshotted on the photo at upload time via a new `upload_trust_level` column on `photos` (Q-045-01 → Option B). | `check`/`monitor`: always scan. `trusted` + `nsfw_scan_trusted_users = true`: scan. `trusted` + `nsfw_scan_trusted_users = false`: skip. | — | — | — | Owner directive, Q-045-01 |
| FR-045-08 | Global config `nsfw_auto_approve_trusted` (boolean): when true and user's snapshotted `upload_trust_level` is `trusted`, moderation-tier findings (`should_review = true`) are auto-dismissed (no action taken). Block-tier findings still apply. | `should_review = true` + `nsfw_auto_approve_trusted = true` + photo `upload_trust_level = trusted` → skip moderation action. `should_block = true` → always apply block action regardless. | — | — | `nsfw.moderation.auto_approved` | Owner directive, Q-045-01 |
| FR-045-09 | Actionable detection results are logged to a `nsfw_detections` table with: `id`, `photo_id`, `label`, `confidence`, `bbox_x`, `bbox_y`, `bbox_width`, `bbox_height`, `area_pixels`, `area_ratio`, `is_block` (bool), `is_review` (bool), `is_sensitive` (bool), `created_at`. Only detections from `block_detected`, `review_detected`, and `sensitive_detected` arrays are stored — `all_detected` is not persisted (Q-045-06). A single detection can appear in multiple arrays simultaneously; all three boolean columns reflect which tiers it belongs to. Dedup by label+bbox: one row per unique detection, booleans merged. | Each detection across `block_detected`, `review_detected`, `sensitive_detected` is matched by label+bbox. One row created per unique detection with `is_block`, `is_review`, `is_sensitive` set according to which arrays it appears in. | — | — | — | Owner directive, Q-045-06 |
| FR-045-10 | A new `NsfwPreset` enum with 6 cases: `DEFAULT`, `STRICT`, `MODERATION`, `NUDE_FEMALE`, `PERMISSIVE`, `SOCIAL_MEDIA`. Used for config validation and request building. | — | — | — | — | Owner directive |
| FR-045-11 | Admin can trigger a bulk NSFW scan via `POST /api/v2/NsfwDetection/bulk-scan`. By default scans photos with `nsfw_scan_status IS NULL` or `failed`. Optional `force` boolean parameter re-scans `completed` photos as well (Q-045-09 → B). | Default: `NULL` + `failed` photos dispatched. With `force = true`: all photos including `completed` are re-dispatched. | Admin-only endpoint (existing `AdminMiddleware`). `force` is optional boolean, defaults to `false`. | — | `nsfw.bulk_scan.dispatched` | Owner directive, Q-045-09 |
| FR-045-12 | New column `nsfw_scan_status` on `photos` table (nullable enum: `pending`, `completed`, `failed`). Mirrors `face_scan_status` pattern. | Set to `pending` on dispatch, `completed` on success, `failed` on error. | — | — | — | Owner directive |
| FR-045-13 | New enum column `nsfw_visibility` on `photos` table (nullable string, default `null`). Values: `visible` (scan completed, no action), `blocked` (block action applied), `review` (moderation action applied). Combined with `is_validated` to provide full moderation context. Both `nsfw_visibility` and `is_validated` are displayed in the Moderation admin panel. When `nsfw_visibility` is set to `blocked` or `review`, `is_validated` is also set to `false` per the configured action. Cleared by admin approval (set to `visible`, `is_validated = true`). | Set by `NsfwActionService` based on detection results and configured actions. | — | — | — | Owner directive, Q-045-02 |
| FR-045-14 | New column `upload_trust_level` on `photos` table (nullable string). Snapshots the uploading user's `upload_trust_level` at upload time. Used by the NSFW callback to resolve trust-level-aware actions (auto-approve). Populated by the `AutoScanNsfwOnUpload` pipe (or a preceding pipe) during photo creation. | Value is one of `check`, `monitor`, `trusted`, or null (for photos uploaded before this feature). | — | — | — | Owner directive, Q-045-01 |
| FR-045-15 | Global config `nsfw_sensitive_no_album_action` determines fallback behaviour when `nsfw_action_sensitive = sensitive` fires but the photo has no album (`album_id IS NULL`). Options: `skip` (log warning, do nothing) or `moderate` (set `nsfw_visibility = review`, `is_validated = false`). Default: `skip`. | When `skip`: warning logged, photo left as-is. When `moderate`: `nsfw_visibility = review`, `is_validated = false`. | Config value must be `skip` or `moderate`. | — | `nsfw.action.sensitive.no_album_fallback` | Owner directive, Q-045-03 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-045-01 | NSFW scan must be fully asynchronous; upload latency must not increase. | UX parity with face detection. | Upload response time unchanged (<200ms delta). | Queue worker infrastructure. | Owner directive |
| NFR-045-02 | API key for NSFW service is stored in `.env` only (not in `configs` table), matching face detection pattern. | Security: prevent key exposure via admin settings UI. | Key not visible in `GET /Settings`. | `config/features.php` | Owner directive |
| NFR-045-03 | NSFW detection logging must not expose detection details to non-admin users. | Privacy / content sensitivity. | `nsfw_detections` table only accessible via admin endpoints. | — | Owner directive |
| NFR-045-04 | Feature requires both `ai_vision_enabled = 1` (global AI Vision toggle, shared with face detection) AND `ai_vision_nsfw_enabled = 1` (NSFW-specific toggle). Either being `0` prevents scans from being dispatched. Callback endpoint still accepts results regardless (the classifier may complete in-flight scans). Mirrors the `ai_vision_enabled` + `ai_vision_face_enabled` pattern. | Operator flexibility + consistency with face detection. | `ai_vision_enabled = 0` OR `ai_vision_nsfw_enabled = 0` → no scans dispatched. | — | Owner directive |
| NFR-045-05 | SE (Supporter Edition) gating: NSFW endpoints require basic SE license (not pro tier). Uses `support` middleware, not `support:pro` (Q-045-12). Routes are also gated by `feature:ai-vision` and `feature:v8` middleware, consistent with all other AI Vision endpoints. | SE feature + v8 feature flag. | Non-SE instances return 403; v8-disabled instances return 403. | SE licence check middleware (`support`), `feature:ai-vision`, `feature:v8`. | Owner directive, Q-045-12 |

## UI / Interaction Mock-ups

### Admin Settings — NSFW Detection Section

```
┌─────────────────────────────────────────────────────────────┐
│  NSFW Detection                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Enable NSFW Classification [Toggle ON/OFF]                  │
│                                                             │
│  Detection Preset          [▼ default          ]            │
│                             ├ default                       │
│                             ├ strict                        │
│                             ├ moderation                    │
│                             ├ nude_female                   │
│                             ├ permissive                    │
│                             └ social_media                  │
│                                                             │
│  ── Actions ──────────────────────────────────────────────  │
│                                                             │
│  On Block Detection        [▼ Block            ]            │
│                             ├ Block (hide until approved)   │
│                             └ Do nothing                    │
│                                                             │
│  On Moderation Detection   [▼ Flag for moderation ]         │
│                             ├ Block (hide until approved)   │
│                             ├ Flag for moderation           │
│                             └ Do nothing                    │
│                                                             │
│  On Sensitive Detection    [▼ Mark album sensitive ]        │
│                             ├ Flag for moderation           │
│                             ├ Mark album as sensitive       │
│                             └ Do nothing                    │
│                                                             │
│  Sensitive (no album)      [▼ Skip (log warning)   ]        │
│                             ├ Skip (log warning)            │
│                             └ Fall back to moderation       │
│                                                             │
│  ── Trust Level Integration ──────────────────────────────  │
│                                                             │
│  Scan trusted users        [Toggle ON/OFF]                  │
│  Auto-approve moderation   [Toggle ON/OFF]                  │
│  for trusted users                                          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Admin Maintenance — Bulk NSFW Scan Card

```
┌─────────────────────────────────────────────────────────────┐
│  Bulk NSFW Scan                                             │
│                                                             │
│  Scan all unscanned photos for NSFW content using the       │
│  configured preset.                                         │
│                                                             │
│                              [ Scan All Unscanned ]         │
└─────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-045-01 | Photo uploaded by `check` user → NSFW scan dispatched, `nsfw_scan_status = pending` |
| S-045-02 | Photo uploaded by `trusted` user + `nsfw_scan_trusted_users = true` → scan dispatched |
| S-045-03 | Photo uploaded by `trusted` user + `nsfw_scan_trusted_users = false` → scan NOT dispatched |
| S-045-04 | NSFW service returns `should_block = true` + `nsfw_action_block = block` → photo `nsfw_visibility = blocked`, `is_validated = false` |
| S-045-05 | NSFW service returns `should_block = true` + `nsfw_action_block = nothing` → no action taken |
| S-045-06 | NSFW service returns `should_review = true` + `nsfw_action_moderation = moderate` → `nsfw_visibility = review`, `is_validated = false` |
| S-045-07 | NSFW service returns `should_review = true` + `nsfw_action_moderation = block` → `nsfw_visibility = blocked`, `is_validated = false` |
| S-045-08 | NSFW service returns `should_review = true` + `nsfw_action_moderation = nothing` → no action |
| S-045-09 | NSFW service returns `is_sensitive = true` + `nsfw_action_sensitive = sensitive` → album `is_nsfw = true` |
| S-045-10 | NSFW service returns `is_sensitive = true` + `nsfw_action_sensitive = moderate` → `nsfw_visibility = review`, `is_validated = false` |
| S-045-11 | NSFW service returns `is_sensitive = true` + `nsfw_action_sensitive = nothing` → no action |
| S-045-12 | NSFW service returns error → `nsfw_scan_status = failed`, detections not logged |
| S-045-13 | Invalid API key on callback → 403 response |
| S-045-14 | `ai_vision_nsfw_enabled = false` → no scan dispatched on upload |
| S-045-24 | `ai_vision_enabled = false` (global AI Vision off) → no NSFW scan dispatched even if `ai_vision_nsfw_enabled = true` |
| S-045-15 | `should_review = true` + `nsfw_auto_approve_trusted = true` + user is `trusted` → moderation action skipped |
| S-045-16 | `should_block = true` + `nsfw_auto_approve_trusted = true` + user is `trusted` → block action STILL applied |
| S-045-17 | Bulk scan dispatched (default) → photos with `nsfw_scan_status IS NULL` or `failed` queued |
| S-045-22 | Bulk scan dispatched with `force = true` → all photos including `completed` re-queued |
| S-045-18 | Photo has no album (unsorted) + `nsfw_action_sensitive = sensitive` + `nsfw_sensitive_no_album_action = skip` → warning logged, no album change, no photo change |
| S-045-23 | Photo has no album (unsorted) + `nsfw_action_sensitive = sensitive` + `nsfw_sensitive_no_album_action = moderate` → `nsfw_visibility = review`, `is_validated = false` |
| S-045-19 | Preset is `default` → `preset` field omitted from request to classifier |
| S-045-20 | Preset is `strict` → `preset: "strict"` included in request |
| S-045-21 | Detection results logged: only `block_detected`, `review_detected`, `sensitive_detected` items are stored; `all_detected` is not persisted. A detection in multiple arrays gets one row with multiple booleans set |

## Test Strategy

- **Core:** Unit tests for `NsfwPreset` enum, `NsfwBlockAction`/`NsfwModerationAction`/`NsfwSensitiveAction` enums, detection tier deduplication logic.
- **Application:** Unit tests for `NsfwDetectionService` (request building, preset omission), `NsfwActionService` (action application per config), `AutoScanNsfwOnUpload` pipe (trust level gating).
- **REST:** Feature tests for `POST /NsfwDetection/results` (valid payload, invalid key, error status, each action combination), `POST /NsfwDetection/bulk-scan` (admin gate).
- **CLI:** None (no CLI commands in v1).
- **UI:** Manual verification of settings toggles and Maintenance bulk scan card.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-045-01 | `NsfwDetection` model — stores per-detection label, confidence, bbox, area, tier for a photo | application, REST |
| DO-045-02 | `NsfwPreset` enum — `DEFAULT`, `STRICT`, `MODERATION`, `NUDE_FEMALE`, `PERMISSIVE`, `SOCIAL_MEDIA` | core, application |
| DO-045-03 | `NsfwBlockAction` enum — `BLOCK`, `NOTHING` | core, application |
| DO-045-04 | `NsfwModerationAction` enum — `BLOCK`, `MODERATE`, `NOTHING` | core, application |
| DO-045-05 | `NsfwSensitiveAction` enum — `MODERATE`, `SENSITIVE`, `NOTHING` | core, application |
| DO-045-06 | `NsfwScanStatus` enum — `PENDING`, `COMPLETED`, `FAILED` | core, application |
| ~~DO-045-07~~ | ~~`NsfwDetectionTier` enum~~ — removed. Replaced by three boolean columns (`is_block`, `is_review`, `is_sensitive`) on `nsfw_detections` since a detection can belong to multiple tiers simultaneously. | — |
| DO-045-08 | `NsfwVisibility` enum — `VISIBLE`, `BLOCKED`, `REVIEW` | core, application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-045-01 | REST POST /api/v2/NsfwDetection/results | Callback from NSFW classifier service | Auth: X-API-Key header. No user session. |
| API-045-02 | REST POST /api/v2/NsfwDetection/bulk-scan | Admin: enqueue all unscanned photos for NSFW scan | Auth: admin middleware |
| API-045-03 | HTTP POST /api/nsfw/detect (outbound) | Request sent to NSFW classifier service | Sent by `DispatchNsfwScanJob` |

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-045-01 | nsfw.scan.dispatched | `photo_id`, `preset` |
| TE-045-02 | nsfw.results.received | `photo_id`, `should_block`, `should_review`, `is_sensitive`, `detection_count` |
| TE-045-03 | nsfw.action.block | `photo_id`, `action` |
| TE-045-04 | nsfw.action.moderate | `photo_id`, `action` |
| TE-045-05 | nsfw.action.sensitive | `photo_id`, `album_id`, `action` |
| TE-045-06 | nsfw.bulk_scan.dispatched | `photo_count` |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-045-01 | NSFW settings section in admin Settings page | Admin navigates to Settings → NSFW Detection section visible |
| UI-045-02 | Bulk NSFW Scan card in Maintenance page | Admin navigates to Maintenance → card visible when `ai_vision_nsfw_enabled = true` |

## Telemetry & Observability

All NSFW events are logged via Laravel's `Log` facade at `info` level. Detection details (labels, confidence scores) are logged but not included in user-facing responses. The `nsfw_detections` table provides a persistent audit trail accessible only by admin.

## Documentation Deliverables

- Update `docs/specs/4-architecture/knowledge-map.md` with NSFW detection modules.
- Update `docs/specs/4-architecture/roadmap.md` with Feature 045 entry.
- Create `docs/specs/2-how-to/configure-nsfw-detection.md` (deferred to follow-up).

## Spec DSL

```yaml
domain_objects:
  - id: DO-045-01
    name: NsfwDetection
    fields:
      - name: photo_id
        type: string
        constraints: "FK to photos.id"
      - name: label
        type: string
        constraints: "e.g. FEMALE_GENITALIA_EXPOSED"
      - name: confidence
        type: float
        constraints: "0.0–1.0"
      - name: bbox_x
        type: integer
      - name: bbox_y
        type: integer
      - name: bbox_width
        type: integer
      - name: bbox_height
        type: integer
      - name: area_pixels
        type: integer
      - name: area_ratio
        type: float
      - name: is_block
        type: boolean
        constraints: "true if detection is in block_detected"
      - name: is_review
        type: boolean
        constraints: "true if detection is in review_detected"
      - name: is_sensitive
        type: boolean
        constraints: "true if detection is in sensitive_detected"
  - id: DO-045-02
    name: NsfwPreset
    values: [DEFAULT, STRICT, MODERATION, NUDE_FEMALE, PERMISSIVE, SOCIAL_MEDIA]
  - id: DO-045-03
    name: NsfwBlockAction
    values: [BLOCK, NOTHING]
  - id: DO-045-04
    name: NsfwModerationAction
    values: [BLOCK, MODERATE, NOTHING]
  - id: DO-045-05
    name: NsfwSensitiveAction
    values: [MODERATE, SENSITIVE, NOTHING]
  - id: DO-045-06
    name: NsfwScanStatus
    values: [PENDING, COMPLETED, FAILED]
  - id: DO-045-08
    name: NsfwVisibility
    values: [VISIBLE, BLOCKED, REVIEW]
routes:
  - id: API-045-01
    method: POST
    path: /api/v2/NsfwDetection/results
  - id: API-045-02
    method: POST
    path: /api/v2/NsfwDetection/bulk-scan
  - id: API-045-03
    method: POST
    path: /api/nsfw/detect
    direction: outbound
telemetry_events:
  - id: TE-045-01
    event: nsfw.scan.dispatched
  - id: TE-045-02
    event: nsfw.results.received
  - id: TE-045-03
    event: nsfw.action.block
  - id: TE-045-04
    event: nsfw.action.moderate
  - id: TE-045-05
    event: nsfw.action.sensitive
  - id: TE-045-06
    event: nsfw.bulk_scan.dispatched
ui_states:
  - id: UI-045-01
    description: NSFW settings section in admin Settings
  - id: UI-045-02
    description: Bulk NSFW Scan card in Maintenance
```

## Appendix

### Example Callback Payload (from NSFW classifier → Lychee)

```json
{
  "photo_id": "42",
  "status": "success",
  "should_block": true,
  "should_review": false,
  "is_sensitive": true,
  "all_detected": [
    {
      "label": "FEMALE_GENITALIA_EXPOSED",
      "confidence": 0.91,
      "bbox": {"x": 120, "y": 200, "width": 300, "height": 280},
      "area_pixels": 84000,
      "area_ratio": 0.175
    },
    {
      "label": "FEMALE_BREAST_COVERED",
      "confidence": 0.74,
      "bbox": {"x": 50, "y": 80, "width": 150, "height": 140},
      "area_pixels": 21000,
      "area_ratio": 0.044
    }
  ],
  "block_detected": [
    {
      "label": "FEMALE_GENITALIA_EXPOSED",
      "confidence": 0.91,
      "bbox": {"x": 120, "y": 200, "width": 300, "height": 280},
      "area_pixels": 84000,
      "area_ratio": 0.175
    }
  ],
  "review_detected": [],
  "sensitive_detected": [
    {
      "label": "FEMALE_BREAST_COVERED",
      "confidence": 0.74,
      "bbox": {"x": 50, "y": 80, "width": 150, "height": 140},
      "area_pixels": 21000,
      "area_ratio": 0.044
    }
  ]
}
```

### Example Outbound Request (Lychee → NSFW classifier)

```json
{
  "photo_id": "42",
  "photo_path": "2024/01/photo.jpg",
  "preset": "strict"
}
```

When preset is `default`, the request body is:

```json
{
  "photo_id": "42",
  "photo_path": "2024/01/photo.jpg"
}
```

### Detection Storage

Only detections from `block_detected`, `review_detected`, and `sensitive_detected` are persisted. Items in `all_detected` that do not appear in any of the three action arrays are not stored (Q-045-06).

A single detection can appear in multiple arrays simultaneously (e.g., in both `block_detected` and `sensitive_detected`). Instead of picking the highest tier, all three memberships are stored as boolean columns on a single row:
- `is_block` — detection appears in `block_detected`
- `is_review` — detection appears in `review_detected`
- `is_sensitive` — detection appears in `sensitive_detected`

Detections are deduplicated by label + bbox coordinates: if the same label+bbox appears in two arrays, one row is created with both booleans set to `true`.
