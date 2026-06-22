# Feature 045 – NSFW Detection & Moderation

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-06-22 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/045-nsfw-moderation/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/045-nsfw-moderation/tasks.md` |
| Roadmap entry | #045 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

Add automated NSFW content detection to Lychee via integration with the external [Lychee-NSFW-Classification](https://github.com/LycheeOrg/Lychee-NSFW-Classification) Python service. Following the same REST + webhook architecture as face detection (Feature 030), Lychee dispatches NSFW scan requests after photo upload and receives classification results via a callback endpoint. Affected modules: backend (controllers, jobs, pipes, models, config, enums), admin settings UI, moderation admin page. The feature leverages the existing `is_nsfw` album flag and `is_validated` photo flag, extends the user trust level system (Feature 033) with a new `trust_but_verify` tier, and introduces configurable actions (block, flag for moderation, mark sensitive) based on detection severity tiers. A new `nsfw_visibility` enum column on `photos` (values: `visible`, `blocked`, `review`) tracks NSFW-specific state independently from `is_validated`; both columns are displayed in the Moderation admin panel. The uploader's trust level is snapshotted on the photo at upload time (`upload_trust_level` column) so the callback can resolve trust-level-aware actions without relying on the user's current trust level (Q-045-01 → Option B).

### Trust-Tier × Finding-Tier Action Matrix

The core decision engine maps each combination of user trust tier and detection finding tier to a concrete action. Three config settings modify the matrix:

- **`nsfw_check_block_action`**: controls what happens when a `check` user triggers a block finding. Options: `block` (default) or `moderate`.
- **`nsfw_monitor_block_action`**: controls what happens when a `monitor` user triggers a block finding. Options: `block` or `moderate` (default).
- **`nsfw_sensitive_album_action`**: controls whether sensitive findings trigger the album NSFW marking. Options: `mark_album` (default) or `nothing`. Applies at photo approval time, not at callback time.

| Trust Tier \ Finding | Block | Review | Sensitive |
|---------------------|-------|--------|-----------|
| **Check** | Block or Moderate _(configurable via `nsfw_check_block_action`)_ | Moderate | Moderate + album action at approval _(configurable)_ |
| **Monitor** | Block or Moderate _(configurable via `nsfw_monitor_block_action`)_ | Moderate | Nothing or album action at approval _(configurable via `nsfw_sensitive_album_action`)_ |
| **Trust but verify** | Moderate | Approve | Nothing or album action at approval _(configurable via `nsfw_sensitive_album_action`)_ |
| **Trusted** | Approve | Approve | Nothing or album action at approval _(configurable via `nsfw_sensitive_album_action`)_ |

**Block action** = delete the photo entirely (hard delete, not soft delete).
**Moderate** = set `nsfw_visibility = review`, `is_validated = false`. Photo held for admin review.
**Approve** = no action on the photo; scan result logged but photo remains visible.
**Album action at approval** = when an admin approves a moderated photo that has `is_sensitive = true` detections, set the parent album's `is_nsfw = true` — but only if none of the album's ancestors are already marked as NSFW (checked via `is_recursive_nsfw`). This is deferred to approval time so that a moderated photo doesn't prematurely flip the album to NSFW before the admin confirms the finding.

## Goals

1. Integrate with the Lychee-NSFW-Classification service to automatically scan uploaded photos for NSFW content.
2. Provide a configurable preset system (6 presets) controlling detection sensitivity at the installation level.
3. Allow admins to configure per-tier actions via the trust-tier × finding-tier matrix.
4. Introduce a new `trust_but_verify` trust level for users whose uploads should be scanned but auto-approved for non-block findings.
5. Integrate with the existing user trust level system to determine when NSFW scanning is applied.
6. Log all NSFW detections for audit and admin review.
7. Extend the existing Moderation admin page to surface NSFW-flagged photos, with `is_blocked` findings hidden by default.

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
| FR-045-01 | On photo upload, if AI Vision is globally enabled (`ai_vision_enabled = 1`) AND NSFW scanning is enabled (`ai_vision_nsfw_enabled = 1`) and the user's trust level requires scanning, dispatch a `DispatchNsfwScanJob` to POST the photo to the NSFW classification service. Scanning is applied for all four trust levels (`check`, `monitor`, `trust_but_verify`, `trusted`); the `nsfw_scan_trusted_users` config controls whether `trusted` users are scanned. | Job dispatches `POST /api/nsfw/detect` with `photo_id`, `photo_path`, and optionally `preset` (omitted when global preset is `default`). Photo `nsfw_scan_status` set to `pending`. | `photo_id` must reference a valid photo; `photo_path` must be a resolvable path on the shared volume. | Service unreachable or returns error → `nsfw_scan_status = failed`, log error. Retry with backoff (3 attempts). | `nsfw.scan.dispatched`, `nsfw.scan.failed` | Owner directive |
| FR-045-02 | The callback endpoint `POST /api/v2/NsfwDetection/results` accepts classification results from the NSFW service, authenticated via `X-API-Key` header against `AI_VISION_NSFW_API_KEY`. | Payload validated, detections logged to `nsfw_detections` table, actions applied per trust-tier × finding-tier matrix. `nsfw_scan_status = completed`. | API key must match config value; `photo_id` must exist; `status` must be `success` or `error`. | Invalid API key → 403. Unknown `photo_id` → log warning, 200. Malformed payload → 422. | `nsfw.results.received`, `nsfw.results.error` | Owner directive |
| FR-045-03 | Global config `nsfw_preset` determines which preset is sent to the classifier. Stored as a config key with enum values: `default`, `strict`, `moderation`, `nude_female`, `permissive`, `social_media`. When set to `default`, the `preset` field is omitted from the request to the classifier. | Preset value read from configs at dispatch time and included/excluded accordingly. | Config value must be one of the 6 valid enum values. | Invalid preset value → fall back to `default` behaviour (omit field). | — | Owner directive |
| FR-045-04 | **Block finding action**: When the callback returns `should_block = true`, the action depends on the photo's `upload_trust_level` per the trust-tier × finding-tier matrix. For `check`: action is configurable via `nsfw_check_block_action` — either `block` (delete the photo entirely, default) or `moderate` (set `nsfw_visibility = review`, `is_validated = false`). For `monitor`: action is configurable via `nsfw_monitor_block_action` — either `block` (delete) or `moderate` (default). For `trust_but_verify`: always moderate. For `trusted`: always approve (no action). | Block: photo is hard-deleted. Moderate: `nsfw_visibility = review`, `is_validated = false`. Approve: photo unchanged, scan logged. | — | — | `nsfw.action.block` | Owner directive |
| FR-045-05 | **Review finding action**: When the callback returns `should_review = true`, the action depends on the photo's `upload_trust_level`. For `check`/`monitor`: always moderate (`nsfw_visibility = review`, `is_validated = false`). For `trust_but_verify`/`trusted`: always approve (no action). | Moderate: `nsfw_visibility = review`, `is_validated = false`. Approve: photo unchanged, scan logged. | — | — | `nsfw.action.moderate` | Owner directive |
| FR-045-06 | **Sensitive finding action**: When the callback returns `is_sensitive = true`, the action depends on the photo's `upload_trust_level`. For `check`: moderate the photo (`nsfw_visibility = review`, `is_validated = false`) and mark the detection with `is_sensitive = true` so the album action can be applied at approval time. For `monitor`/`trust_but_verify`/`trusted`: the `nsfw_sensitive_album_action` config determines whether album marking is performed — `mark_album` or `nothing`. Album marking is always executed via a dispatched `ApplyNsfwAlbumSensitivityJob`. For non-moderated tiers (`monitor`/`trust_but_verify`/`trusted`), this job is dispatched **immediately** at callback time (auto-approval path). For `check` users, the job is dispatched at **admin approval time**. The job checks the photo's direct parent album, verifies none of its ancestors are already NSFW (`is_recursive_nsfw`), and sets `album.is_nsfw = true` if appropriate. If the photo has no album (unsorted), behaviour is controlled by `nsfw_sensitive_no_album_action` config: `skip` logs warning and does nothing; `moderate` falls back to `nsfw_visibility = review`, `is_validated = false`. | See trust-tier matrix above. Album marking via async job respects recursive NSFW check. | — | — | `nsfw.action.sensitive` | Owner directive |
| FR-045-07 | **New trust tier — `trust_but_verify`**: A new `UserUploadTrustLevel` enum case `TRUST_BUT_VERIFY`. Uploads from these users are immediately validated (`is_validated = true`) like `trusted` users. NSFW scanning is always applied. Block findings are moderated (not deleted). Review findings are auto-approved. Sensitive findings follow the configurable album action. | Upload: `is_validated = true`. NSFW scan always dispatched. Callback applies matrix actions. | — | — | — | Owner directive |
| FR-045-08 | User trust level integration: users with `upload_trust_level = check` or `monitor` always have NSFW scanning applied on upload. Users with `upload_trust_level = trust_but_verify` always have NSFW scanning applied. Users with `upload_trust_level = trusted` consult global config `nsfw_scan_trusted_users` (boolean) to determine if scanning is applied. The uploader's trust level is snapshotted on the photo at upload time via a new `upload_trust_level` column on `photos` (Q-045-01 → Option B). | `check`/`monitor`/`trust_but_verify`: always scan. `trusted` + `nsfw_scan_trusted_users = true`: scan. `trusted` + `nsfw_scan_trusted_users = false`: skip. | — | — | — | Owner directive, Q-045-01 |
| FR-045-09 | Actionable detection results are logged to a `nsfw_detections` table with: `id`, `photo_id`, `label`, `confidence`, `bbox_x`, `bbox_y`, `bbox_width`, `bbox_height`, `area_pixels`, `area_ratio`, `is_block` (bool), `is_review` (bool), `is_sensitive` (bool), `created_at`. Only detections from `block_detected`, `review_detected`, and `sensitive_detected` arrays are stored — `all_detected` is not persisted (Q-045-06). A single detection can appear in multiple arrays simultaneously; all three boolean columns reflect which tiers it belongs to. Dedup by label+bbox: one row per unique detection, booleans merged. | Each detection across `block_detected`, `review_detected`, `sensitive_detected` is matched by label+bbox. One row created per unique detection with `is_block`, `is_review`, `is_sensitive` set according to which arrays it appears in. | — | — | — | Owner directive, Q-045-06 |
| FR-045-10 | A new `NsfwPreset` enum with 6 cases: `DEFAULT`, `STRICT`, `MODERATION`, `NUDE_FEMALE`, `PERMISSIVE`, `SOCIAL_MEDIA`. Used for config validation and request building. | — | — | — | — | Owner directive |
| FR-045-11 | Admin can trigger a bulk NSFW scan via `POST /api/v2/NsfwDetection/bulk-scan`. By default scans photos with `nsfw_scan_status IS NULL` or `failed`. Optional `force` boolean parameter re-scans `completed` photos as well (Q-045-09 → B). | Default: `NULL` + `failed` photos dispatched. With `force = true`: all photos including `completed` are re-dispatched. | Admin-only endpoint (existing `AdminMiddleware`). `force` is optional boolean, defaults to `false`. | — | `nsfw.bulk_scan.dispatched` | Owner directive, Q-045-09 |
| FR-045-12 | New column `nsfw_scan_status` on `photos` table (nullable enum: `pending`, `completed`, `failed`). Mirrors `face_scan_status` pattern. | Set to `pending` on dispatch, `completed` on success, `failed` on error. | — | — | — | Owner directive |
| FR-045-13 | New enum column `nsfw_visibility` on `photos` table (nullable string, default `null`). Values: `visible` (scan completed, no action), `blocked` (photo was deleted — historical record only), `review` (moderation action applied). Combined with `is_validated` to provide full moderation context. Both `nsfw_visibility` and `is_validated` are displayed in the Moderation admin panel. When `nsfw_visibility` is set to `review`, `is_validated` is also set to `false` per the configured action. Cleared by admin approval (set to `visible`, `is_validated = true`). On approval, if the photo has sensitive detections and `nsfw_sensitive_album_action = mark_album`, the album action is applied (see FR-045-06). | Set by `NsfwActionService` based on detection results and trust-tier matrix. | — | — | — | Owner directive, Q-045-02 |
| FR-045-14 | New column `upload_trust_level` on `photos` table (nullable string). Snapshots the uploading user's `upload_trust_level` at upload time. Used by the NSFW callback to resolve trust-level-aware actions. Populated by the `AutoScanNsfwOnUpload` pipe (or a preceding pipe) during photo creation. | Value is one of `check`, `monitor`, `trust_but_verify`, `trusted`, or null (for photos uploaded before this feature). | — | — | — | Owner directive, Q-045-01 |
| FR-045-15 | Global config `nsfw_sensitive_no_album_action` determines fallback behaviour when album marking fires but the photo has no album (`album_id IS NULL`). Options: `skip` (log warning, do nothing) or `moderate` (set `nsfw_visibility = review`, `is_validated = false`). Default: `skip`. | When `skip`: warning logged, photo left as-is. When `moderate`: `nsfw_visibility = review`, `is_validated = false`. | Config value must be `skip` or `moderate`. | — | `nsfw.action.sensitive.no_album_fallback` | Owner directive, Q-045-03 |
| FR-045-16 | **Moderation page — hide blocked findings by default**: The Moderation admin page hides photos whose `nsfw_visibility = blocked` (i.e. that were deleted due to block-tier findings) by default. A toggle/filter allows the admin to show them if desired. This prevents the moderation queue from being cluttered with already-actioned block findings. | Default view: `nsfw_visibility != blocked` (or `nsfw_visibility IS NULL`). Toggle reveals all. | — | — | — | Owner directive |
| FR-045-17 | **`ApplyNsfwAlbumSensitivityJob`**: A queued job that applies the album NSFW marking for a photo with sensitive detections. Dispatched in two contexts: (1) **auto-approval path** — at callback time for `monitor`/`trust_but_verify`/`trusted` users when `nsfw_sensitive_album_action = mark_album`; (2) **admin approval path** — when an admin approves a moderated `check`-user photo that has `is_sensitive = true` detections and `nsfw_sensitive_album_action = mark_album`. The job loads the photo's direct parent album, checks `is_recursive_nsfw` (if any ancestor is already NSFW, skip), and sets `album.is_nsfw = true` if appropriate. If the photo has no album, the `nsfw_sensitive_no_album_action` config applies. | Job dispatched → album checked → marked if conditions met. | Album must exist; recursive NSFW check must pass. | No album → `nsfw_sensitive_no_album_action` applies. | `nsfw.action.sensitive.album_marked` | Owner directive |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-045-01 | NSFW scan must be fully asynchronous; upload latency must not increase. | UX parity with face detection. | Upload response time unchanged (<200ms delta). | Queue worker infrastructure. | Owner directive |
| NFR-045-02 | API key for NSFW service is stored in `.env` only (not in `configs` table), matching face detection pattern. | Security: prevent key exposure via admin settings UI. | Key not visible in `GET /Settings`. | `config/features.php` | Owner directive |
| NFR-045-03 | NSFW detection logging must not expose detection details to non-admin users. | Privacy / content sensitivity. | `nsfw_detections` table only accessible via admin endpoints. | — | Owner directive |
| NFR-045-04 | Feature requires both `ai_vision_enabled = 1` (global AI Vision toggle, shared with face detection) AND `ai_vision_nsfw_enabled = 1` (NSFW-specific toggle). Either being `0` prevents scans from being dispatched. Callback endpoint still accepts results regardless (the classifier may complete in-flight scans). Mirrors the `ai_vision_enabled` + `ai_vision_face_enabled` pattern. | Operator flexibility + consistency with face detection. | `ai_vision_enabled = 0` OR `ai_vision_nsfw_enabled = 0` → no NSFW scan dispatched even if `ai_vision_nsfw_enabled = true`. | — | Owner directive |
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
│  Block finding: Check      [▼ Block (delete photo) ]        │
│                             ├ Block (delete photo)          │
│                             └ Moderate (hold for review)    │
│                                                             │
│  Block finding: Monitor    [▼ Moderate (hold for review) ]  │
│                             ├ Block (delete photo)          │
│                             └ Moderate (hold for review)    │
│                                                             │
│  Sensitive: album action   [▼ Mark album as sensitive ]     │
│                             ├ Mark album as sensitive        │
│                             └ Do nothing                    │
│                                                             │
│  Sensitive (no album)      [▼ Skip (log warning)   ]        │
│                             ├ Skip (log warning)            │
│                             └ Fall back to moderation       │
│                                                             │
│  ── Trust Level Integration ──────────────────────────────  │
│                                                             │
│  Scan trusted users        [Toggle ON/OFF]                  │
│                                                             │
│  ── Trust-Tier × Finding Matrix (read-only summary) ─────  │
│                                                             │
│  ┌──────────────────┬─────────┬─────────┬───────────┐      │
│  │ Trust Level      │ Block   │ Review  │ Sensitive │      │
│  ├──────────────────┼─────────┼─────────┼───────────┤      │
│  │ Check            │ Blk/Mod │Moderate │Mod + Album│      │
│  │ Monitor          │ Blk/Mod │Moderate │ Album/None│      │
│  │ Trust but verify │Moderate │ Approve │ Album/None│      │
│  │ Trusted          │ Approve │ Approve │ Album/None│      │
│  └──────────────────┴─────────┴─────────┴───────────┘      │
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

### Admin Moderation — NSFW Filtering

```
┌─────────────────────────────────────────────────────────────┐
│  Moderation                                                 │
├─────────────────────────────────────────────────────────────┤
│  [☑ Hide blocked (deleted)] [☐ Show blocked]                │
│                                                             │
│  ┌──────┬────────────┬───────┬─────────┬──────────┬──────┐ │
│  │  ☐   │ Thumbnail  │ Title │ Owner   │ NSFW     │ Act. │ │
│  ├──────┼────────────┼───────┼─────────┼──────────┼──────┤ │
│  │  ☐   │ [img]      │ A.jpg │ alice   │ Review   │ ✓ ✗  │ │
│  │  ☐   │ [img]      │ B.jpg │ bob     │ —        │ ✓ ✗  │ │
│  │  ☐   │ [img]      │ C.jpg │ alice   │ Blocked  │ ✓ ✗  │ │ ← hidden by default
│  └──────┴────────────┴───────┴─────────┴──────────┴──────┘ │
└─────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-045-01 | Photo uploaded by `check` user → NSFW scan dispatched, `nsfw_scan_status = pending` |
| S-045-02 | Photo uploaded by `trusted` user + `nsfw_scan_trusted_users = true` → scan dispatched |
| S-045-03 | Photo uploaded by `trusted` user + `nsfw_scan_trusted_users = false` → scan NOT dispatched |
| S-045-04 | `check` user + `should_block = true` + `nsfw_check_block_action = block` → photo hard-deleted |
| S-045-05 | `check` user + `should_block = true` + `nsfw_check_block_action = moderate` → `nsfw_visibility = review`, `is_validated = false` |
| S-045-06 | `check` user + `should_review = true` → `nsfw_visibility = review`, `is_validated = false` |
| S-045-07 | `monitor` user + `should_block = true` + `nsfw_monitor_block_action = block` → photo hard-deleted |
| S-045-08 | `monitor` user + `should_block = true` + `nsfw_monitor_block_action = moderate` → `nsfw_visibility = review`, `is_validated = false` |
| S-045-09 | `monitor` user + `should_review = true` → `nsfw_visibility = review`, `is_validated = false` |
| S-045-10 | `trust_but_verify` user + `should_block = true` → `nsfw_visibility = review`, `is_validated = false` (always moderate) |
| S-045-11 | `trust_but_verify` user + `should_review = true` → no action (auto-approved) |
| S-045-12 | `trusted` user + `should_block = true` → no action (auto-approved) |
| S-045-13 | `trusted` user + `should_review = true` → no action (auto-approved) |
| S-045-14 | `check` user + `is_sensitive = true` → moderate photo; on admin approval with `nsfw_sensitive_album_action = mark_album`: set album `is_nsfw = true` (if not recursive NSFW) |
| S-045-15 | `monitor` user + `is_sensitive = true` + `nsfw_sensitive_album_action = mark_album` → set album `is_nsfw = true` immediately (if not recursive NSFW) |
| S-045-16 | `monitor` user + `is_sensitive = true` + `nsfw_sensitive_album_action = nothing` → no action |
| S-045-17 | `trust_but_verify` user + `is_sensitive = true` + `nsfw_sensitive_album_action = mark_album` → set album `is_nsfw = true` immediately (if not recursive NSFW) |
| S-045-18 | `trusted` user + `is_sensitive = true` + `nsfw_sensitive_album_action = mark_album` → set album `is_nsfw = true` immediately (if not recursive NSFW) |
| S-045-19 | Album already has ancestor with `is_nsfw = true` (recursive NSFW) + sensitive finding → album NOT re-marked |
| S-045-20 | NSFW service returns error → `nsfw_scan_status = failed`, detections not logged |
| S-045-21 | Invalid API key on callback → 403 response |
| S-045-22 | `ai_vision_nsfw_enabled = false` → no scan dispatched on upload |
| S-045-23 | `ai_vision_enabled = false` (global AI Vision off) → no NSFW scan dispatched even if `ai_vision_nsfw_enabled = true` |
| S-045-24 | Bulk scan dispatched (default) → photos with `nsfw_scan_status IS NULL` or `failed` queued |
| S-045-25 | Bulk scan dispatched with `force = true` → all photos including `completed` re-queued |
| S-045-26 | Photo has no album (unsorted) + sensitive action fires + `nsfw_sensitive_no_album_action = skip` → warning logged, no album change, no photo change |
| S-045-27 | Photo has no album (unsorted) + sensitive action fires + `nsfw_sensitive_no_album_action = moderate` → `nsfw_visibility = review`, `is_validated = false` |
| S-045-28 | Preset is `default` → `preset` field omitted from request to classifier |
| S-045-29 | Preset is `strict` → `preset: "strict"` included in request |
| S-045-30 | Detection results logged: only `block_detected`, `review_detected`, `sensitive_detected` items are stored; `all_detected` is not persisted. A detection in multiple arrays gets one row with multiple booleans set |
| S-045-31 | Photo uploaded by `trust_but_verify` user → `is_validated = true`, NSFW scan dispatched |
| S-045-32 | Moderation page: default view hides photos with `nsfw_visibility = blocked` |
| S-045-33 | Moderation page: admin enables "show blocked" toggle → blocked photos visible |
| S-045-34 | Admin approves photo with sensitive detections + `nsfw_sensitive_album_action = mark_album` + album not recursive NSFW → album marked NSFW |
| S-045-35 | Admin approves photo with sensitive detections + album already recursive NSFW → album NOT re-marked |

## Test Strategy

- **Core:** Unit tests for `NsfwPreset` enum, `NsfwBlockFindingAction`/`NsfwSensitiveAlbumAction` enums, detection tier deduplication logic.
- **Application:** Unit tests for `NsfwDetectionService` (request building, preset omission), `NsfwActionService` (action application per trust-tier × finding-tier matrix), `AutoScanNsfwOnUpload` pipe (trust level gating including `trust_but_verify`).
- **REST:** Feature tests for `POST /NsfwDetection/results` (valid payload, invalid key, error status, each trust-tier × finding combination), `POST /NsfwDetection/bulk-scan` (admin gate).
- **Integration:** Tests for approval-time album marking (recursive NSFW check, no-album fallback).
- **CLI:** None (no CLI commands in v1).
- **UI:** Manual verification of settings toggles, Maintenance bulk scan card, and Moderation page filtering.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-045-01 | `NsfwDetection` model — stores per-detection label, confidence, bbox, area, tier for a photo | application, REST |
| DO-045-02 | `NsfwPreset` enum — `DEFAULT`, `STRICT`, `MODERATION`, `NUDE_FEMALE`, `PERMISSIVE`, `SOCIAL_MEDIA` | core, application |
| DO-045-03 | `NsfwBlockFindingAction` enum — `BLOCK`, `MODERATE` | core, application |
| DO-045-04 | `NsfwSensitiveAlbumAction` enum — `MARK_ALBUM`, `NOTHING` | core, application |
| DO-045-05 | ~~`NsfwModerationAction` enum~~ — removed. Review findings are no longer configurable; the matrix determines action by trust tier. | — |
| DO-045-06 | `NsfwScanStatus` enum — `PENDING`, `COMPLETED`, `FAILED` | core, application |
| ~~DO-045-07~~ | ~~`NsfwDetectionTier` enum~~ — removed. Replaced by three boolean columns (`is_block`, `is_review`, `is_sensitive`) on `nsfw_detections` since a detection can belong to multiple tiers simultaneously. | — |
| DO-045-08 | `NsfwVisibility` enum — `VISIBLE`, `BLOCKED`, `REVIEW` | core, application |
| DO-045-09 | `UserUploadTrustLevel` enum — updated with new `TRUST_BUT_VERIFY` case | core, application |
| DO-045-10 | `NsfwSensitiveNoAlbumAction` enum — `SKIP`, `MODERATE` | core, application |

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
| TE-045-03 | nsfw.action.block | `photo_id`, `action` (deleted/moderated/approved), `upload_trust_level` |
| TE-045-04 | nsfw.action.moderate | `photo_id`, `action` (moderated/approved), `upload_trust_level` |
| TE-045-05 | nsfw.action.sensitive | `photo_id`, `album_id`, `action` (mark_album/nothing/moderate), `upload_trust_level` |
| TE-045-06 | nsfw.bulk_scan.dispatched | `photo_count` |
| TE-045-07 | nsfw.action.sensitive.album_marked | `photo_id`, `album_id` — emitted when album is marked NSFW at approval time |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-045-01 | NSFW settings section in admin Settings page | Admin navigates to Settings → NSFW Detection section visible with trust-tier matrix summary |
| UI-045-02 | Bulk NSFW Scan card in Maintenance page | Admin navigates to Maintenance → card visible when `ai_vision_nsfw_enabled = true` |
| UI-045-03 | Moderation page with NSFW filtering | Default view hides `nsfw_visibility = blocked`; toggle to show all |

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
    name: NsfwBlockFindingAction
    values: [BLOCK, MODERATE]
  - id: DO-045-04
    name: NsfwSensitiveAlbumAction
    values: [MARK_ALBUM, NOTHING]
  - id: DO-045-06
    name: NsfwScanStatus
    values: [PENDING, COMPLETED, FAILED]
  - id: DO-045-08
    name: NsfwVisibility
    values: [VISIBLE, BLOCKED, REVIEW]
  - id: DO-045-09
    name: UserUploadTrustLevel
    values: [CHECK, MONITOR, TRUST_BUT_VERIFY, TRUSTED]
  - id: DO-045-10
    name: NsfwSensitiveNoAlbumAction
    values: [SKIP, MODERATE]
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
  - id: TE-045-07
    event: nsfw.action.sensitive.album_marked
ui_states:
  - id: UI-045-01
    description: NSFW settings section in admin Settings with trust-tier matrix
  - id: UI-045-02
    description: Bulk NSFW Scan card in Maintenance
  - id: UI-045-03
    description: Moderation page with blocked-findings filter
config_keys:
  - key: ai_vision_nsfw_enabled
    type: bool
    default: "0"
    category: AI Vision
  - key: nsfw_preset
    type: string
    default: "default"
    category: AI Vision
    type_range: "default|strict|moderation|nude_female|permissive|social_media"
  - key: nsfw_check_block_action
    type: string
    default: "block"
    category: AI Vision
    type_range: "block|moderate"
  - key: nsfw_monitor_block_action
    type: string
    default: "moderate"
    category: AI Vision
    type_range: "block|moderate"
  - key: nsfw_sensitive_album_action
    type: string
    default: "mark_album"
    category: AI Vision
    type_range: "mark_album|nothing"
  - key: nsfw_sensitive_no_album_action
    type: string
    default: "skip"
    category: AI Vision
    type_range: "skip|moderate"
  - key: nsfw_scan_trusted_users
    type: bool
    default: "0"
    category: AI Vision
```

## Appendix

### Trust-Tier × Finding-Tier Action Matrix (detailed)

This matrix is the core decision engine for the NSFW action system. It replaces the previous per-finding-tier config approach with a unified trust-aware model.

#### Block Findings (`should_block = true`)

| Trust Level | Action | Configurable? |
|------------|--------|---------------|
| Check | Block (delete) or Moderate | Yes — `nsfw_check_block_action` (default: `block`) |
| Monitor | Block (delete) or Moderate | Yes — `nsfw_monitor_block_action` (default: `moderate`) |
| Trust but verify | Moderate | No — always moderate |
| Trusted | Approve (no action) | No — always approve |

**Block = hard-delete the photo.** The photo row, files, and thumbnails are permanently removed. The `nsfw_detections` rows are cascade-deleted with the photo. Each trust tier has its own config: `check` defaults to `block` (strictest), `monitor` defaults to `moderate` (these users have some trust).

#### Review Findings (`should_review = true`)

| Trust Level | Action | Configurable? |
|------------|--------|---------------|
| Check | Moderate | No — always moderate |
| Monitor | Moderate | No — always moderate |
| Trust but verify | Approve (no action) | No — always approve |
| Trusted | Approve (no action) | No — always approve |

#### Sensitive Findings (`is_sensitive = true`)

| Trust Level | Action on photo | Action on album | Configurable? |
|------------|----------------|-----------------|---------------|
| Check | Moderate | Mark album NSFW at approval time | Album action: `nsfw_sensitive_album_action` |
| Monitor | No action | Mark album NSFW immediately (or nothing) | Album action: `nsfw_sensitive_album_action` |
| Trust but verify | No action | Mark album NSFW immediately (or nothing) | Album action: `nsfw_sensitive_album_action` |
| Trusted | No action | Mark album NSFW immediately (or nothing) | Album action: `nsfw_sensitive_album_action` |

**Album marking rules:**
1. Only the photo's direct parent album is marked (`album_id` FK on photo).
2. Before marking, check `is_recursive_nsfw` on the album. If any ancestor is already NSFW, skip marking (the album is already effectively NSFW).
3. If the photo has no album (unsorted), the `nsfw_sensitive_no_album_action` config controls fallback: `skip` (log and do nothing) or `moderate` (hold for review).
4. Album marking is always executed via `ApplyNsfwAlbumSensitivityJob` (queued job), not inline in the controller or callback handler.
5. For `check` users, the job is dispatched at **admin approval time** — the photo is moderated first, and only when the admin approves does the job run. This prevents a false positive from flipping an album to NSFW without human review.
6. For `monitor`/`trust_but_verify`/`trusted` users, the job is dispatched **immediately** at callback time (auto-approval path — the photo is not held for moderation on sensitive findings alone).

### `trust_but_verify` Trust Level

This new tier fills the gap between `monitor` and `trusted`:

| Property | Check | Monitor | Trust but verify | Trusted |
|---------|-------|---------|-----------------|---------|
| `is_validated` on upload | `false` | `true` | `true` | `true` |
| NSFW scan dispatched | Always | Always | Always | Configurable |
| Block finding | Block/Moderate | Block/Moderate | Moderate | Approve |
| Review finding | Moderate | Moderate | Approve | Approve |
| Sensitive finding | Moderate + deferred album | Album action | Album action | Album action |

Use case: users who are generally trusted but whose uploads should still be screened for the most severe (block-tier) content. Review-tier findings are considered acceptable for these users, but block-tier content is held for admin review rather than deleted.

### Config Keys Summary

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `ai_vision_nsfw_enabled` | bool | `0` | Enable/disable NSFW scanning |
| `nsfw_preset` | string | `default` | Detection preset sent to classifier |
| `nsfw_check_block_action` | string | `block` | Action for block findings on `check` users: `block` (delete) or `moderate` |
| `nsfw_monitor_block_action` | string | `moderate` | Action for block findings on `monitor` users: `block` (delete) or `moderate` |
| `nsfw_sensitive_album_action` | string | `mark_album` | Action for sensitive findings: `mark_album` or `nothing` |
| `nsfw_sensitive_no_album_action` | string | `skip` | Fallback when sensitive fires on unsorted photo: `skip` or `moderate` |
| `nsfw_scan_trusted_users` | bool | `0` | Whether to scan trusted users' uploads |

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
