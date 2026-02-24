# Feature 015 – Upload Watermark Toggle

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-02-24 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/015-upload-watermark-toggle/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/015-upload-watermark-toggle/tasks.md` |
| Roadmap entry | #015 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature adds a toggle switch to the upload modal that allows users to enable or disable watermarking for photos being uploaded. When the global watermarking setting (`watermark_enabled`) is enabled, the toggle appears in the upload modal, defaulting to "on" (watermark enabled). Users can toggle it off to upload photos without watermarks. The toggle is only visible when global watermarking is enabled and properly configured (valid watermark image ID set).

**Admin control:** A new configuration setting `watermark_optout_disabled` (default: `false`) allows administrators to hide the toggle, forcing all uploads to be watermarked when watermarking is enabled. When set to `true`, the toggle is not displayed and all photos are watermarked according to global settings.

**Affected modules:** Application (UploadConfig resource, PhotoController, ProcessImageJob, Settings), REST API (Gallery::getUploadLimits response), UI (UploadPanel component, upload service, Settings panel).

## Goals

- Allow users to opt-out of watermarking on a per-upload-session basis
- Display watermark toggle only when watermarking is globally enabled and properly configured
- Provide admin control to disable the opt-out toggle (force watermarking)
- Default toggle state matches the global watermark setting (on by default)
- Pass watermark preference through the upload pipeline to the processing job
- Maintain backward compatibility—existing upload behavior unchanged when toggle not interacted with

## Non-Goals

- Per-photo watermark toggle (applies to all photos in current upload session)
- Album-level watermark settings
- Modifying watermark appearance settings at upload time (position, size, opacity)
- Retroactive watermarking changes for already-uploaded photos (use existing watermark button)
- Changing the global watermark configuration from the upload modal

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-015-01 | Upload modal displays watermark toggle when watermarking is enabled and opt-out allowed | UploadPanel shows toggle switch when `UploadConfig.can_watermark_optout` is true. Toggle defaults to ON. | Toggle only visible if `can_watermark_optout` is true in UploadConfig response. | If watermarking disabled, not properly configured, or opt-out disabled by admin, toggle not rendered. | No telemetry (UI state). | User requirement |
| FR-015-02 | Toggle state controls watermark application during upload | When toggle is ON, `apply_watermark: true` sent with upload request. When OFF, `apply_watermark: false` sent. Backend respects this flag during photo processing. | Boolean flag passed through FormData in upload request. Validated as optional boolean in UploadPhotoRequest. | If flag missing, default to global `watermark_enabled` setting (backward compatibility). | No telemetry (upload processing). | User requirement |
| FR-015-03 | Toggle persists across multiple files in same upload session | Once toggled, state applies to all subsequent uploads until modal closed or state changed. Multiple files uploaded in parallel respect the toggle state set at upload initiation. | Toggle state stored in Vue ref, read at upload time for each file. | State reset when modal closed and reopened. | No telemetry (UI state). | User requirement |
| FR-015-04 | Backend UploadConfig includes watermarker status | `Gallery::getUploadLimits` endpoint returns `is_watermarker_enabled: boolean` indicating if watermarking is available. True only when: `watermark_enabled` config is true AND `watermark_photo_id` is set AND Imagick is available. | UploadConfig resource computes this by checking all three conditions. | Returns false if any condition not met. | No telemetry (config read). | User requirement |
| FR-015-05 | ProcessImageJob respects watermark flag | ProcessImageJob receives `apply_watermark` parameter. When false, skips ApplyWatermark pipe in processing pipeline. When true or null (default), applies watermark if globally enabled. | Flag stored in job and passed to pipeline configuration. | If flag is null/missing, fall back to global setting. | No telemetry (job processing). | User requirement |
| FR-015-06 | UploadPhotoRequest validates watermark flag | Request accepts optional `apply_watermark` boolean parameter. Validates as boolean when present. | Rule: `'apply_watermark' => 'sometimes|boolean'`. | Invalid value returns 422 validation error. | No telemetry (validation). | Implementation detail |
| FR-015-07 | Admin setting `watermark_optout_disabled` controls toggle visibility | Config key `watermark_optout_disabled` (boolean, default: false). When true, toggle hidden and uploads always watermarked per global setting. | Setting stored in `configs` table, exposed in admin settings UI. | N/A | No telemetry (config). | User requirement |
| FR-015-08 | UploadConfig includes `can_watermark_optout` flag | `Gallery::getUploadLimits` endpoint returns `can_watermark_optout: boolean`. True only when: `watermark_enabled` is true AND `watermark_photo_id` is set AND Imagick available AND `watermark_optout_disabled` is false. | UploadConfig resource computes this by checking all four conditions. | Returns false if any condition not met. | No telemetry (config read). | User requirement |
| FR-015-09 | Server-side enforcement of opt-out restriction | When `watermark_optout_disabled` is true, backend MUST ignore `apply_watermark=false` from request and treat it as `null` (use global setting). This prevents bypassing the admin restriction via direct API calls. | PhotoController checks `watermark_optout_disabled` config before passing flag to job. If disabled, force `apply_watermark` to `null`. | N/A | No telemetry (security enforcement). | Security requirement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-015-01 | Toggle must not affect upload performance | User experience during bulk uploads | No measurable latency added by toggle presence or state transmission. Single boolean in request payload. | Existing upload pipeline | Performance standard |
| NFR-015-02 | Backward compatibility with existing clients | API stability | Uploads without `apply_watermark` parameter work as before (use global setting). Field is optional. | UploadPhotoRequest validation | API contract |
| NFR-015-03 | Code follows Lychee PHP conventions | Maintainability and code quality | License headers, snake_case variables, strict comparison (===), PSR-4, no `empty()`, `in_array(..., true)`. | php-cs-fixer, phpstan level 6 | [docs/specs/3-reference/coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-015-04 | Frontend follows Vue3/TypeScript conventions | Maintainability and code quality | Template-first component structure, Composition API, regular function declarations, `.then()` instead of async/await, axios calls in services directory. | Prettier, frontend tests | [docs/specs/3-reference/coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-015-05 | Test coverage for watermark toggle paths | Ensure correctness and prevent regression | Feature tests for upload with/without flag, toggle visibility conditions, backward compatibility. | BaseApiWithDataTest, in-memory SQLite | Testing standard |

## UI / Interaction Mock-ups

### 1. Upload Modal with Watermark Toggle (Empty State)

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │                                               │  │
│  │           Select your files                  │  │
│  │                                               │  │
│  │      Drag and drop files here or click       │  │
│  │                                               │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │  🖼️ Apply watermark            [====ON====]  │  │  ← NEW: Toggle switch
│  └───────────────────────────────────────────────┘  │    (only when watermarking enabled)
│                                                     │
│  ┌─────────────────────────────────────────────────┐│
│  │                    Close                       ││
│  └─────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────┘
```

### 2. Upload Modal with Watermark Toggle (Uploading)

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│             Uploaded 2 / 5                          │
│  ████████████████░░░░░░░░░░░░░░░░░░  40%           │
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │  IMG_001.jpg               ████████  Done ✓   │  │
│  │  IMG_002.jpg               ████████  Done ✓   │  │
│  │  IMG_003.jpg               ████░░░░  45%      │  │
│  │  IMG_004.jpg                        Waiting   │  │
│  │  IMG_005.jpg                        Waiting   │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │  🖼️ Apply watermark            [====ON====]  │  │  ← Toggle disabled during upload
│  └───────────────────────────────────────────────┘  │    (state already sent for queued files)
│                                                     │
│  ┌────────────────────┐┌────────────────────────────┐
│  │       Cancel       ││          Close            │
│  └────────────────────┘└────────────────────────────┘
└─────────────────────────────────────────────────────┘
```

### 3. Upload Modal without Watermark Toggle (Watermarking Disabled)

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │                                               │  │
│  │           Select your files                  │  │
│  │                                               │  │
│  │      Drag and drop files here or click       │  │
│  │                                               │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│                                                     │  ← No toggle shown
│                                                     │
│  ┌─────────────────────────────────────────────────┐│
│  │                    Close                       ││
│  └─────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────┘
```

**States:**

| State | Description |
|-------|-------------|
| Toggle ON (default) | Watermark will be applied to uploaded photos |
| Toggle OFF | Photos uploaded without watermark |
| Toggle Hidden (disabled) | Watermarking not available (disabled globally or not configured) |
| Toggle Hidden (opt-out disabled) | Admin has disabled opt-out via `watermark_optout_disabled` setting |
| Toggle Disabled | Upload in progress, toggle cannot be changed for queued files |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-015-01 | Watermarking enabled + toggle ON: Photos are watermarked during processing |
| S-015-02 | Watermarking enabled + toggle OFF: Photos uploaded without watermark |
| S-015-03 | Watermarking disabled globally: Toggle not visible, photos not watermarked |
| S-015-04 | Watermark image not configured: Toggle not visible, photos not watermarked |
| S-015-05 | Backward compatibility: Upload without `apply_watermark` param uses global setting |
| S-015-06 | Toggle state persists: Multiple files in session respect toggle state |
| S-015-07 | Toggle disabled during upload: Cannot change state while files are uploading |
| S-015-08 | Admin disabled opt-out: Toggle not visible when `watermark_optout_disabled` is true, all photos watermarked even if `apply_watermark=false` sent via API |

## Test Strategy

- **Core/Application:** Feature tests for UploadPhotoRequest validation with/without `apply_watermark` parameter. Tests for ProcessImageJob with flag true/false/null.
- **REST:** API tests for upload endpoint accepting and respecting the watermark flag. Tests for UploadConfig response including `is_watermarker_enabled`.
- **UI (JS):** Component tests for toggle visibility based on config. Tests for toggle state management and persistence within session.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-015-01 | UploadPhotoRequest: Add optional `apply_watermark` boolean parameter | Application (Request validation) |
| DO-015-02 | ProcessImageJob: Add `apply_watermark` nullable boolean parameter | Application (Job processing) |
| DO-015-03 | Config: Add `watermark_optout_disabled` boolean setting (default: false) | Application (Settings) |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-015-01 | GET /Gallery::getUploadLimits | Returns UploadConfig including `is_watermarker_enabled` and `can_watermark_optout` | Existing endpoint, extended response |
| API-015-02 | POST /Photo | Upload endpoint accepts `apply_watermark` form field | Existing endpoint, new optional parameter |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-015-01 | Toggle visible ON | `can_watermark_optout` true, no uploads in progress, default state |
| UI-015-02 | Toggle visible OFF | User clicked toggle to disable watermarking |
| UI-015-03 | Toggle hidden | `can_watermark_optout` false in UploadConfig (watermarking unavailable or opt-out disabled) |
| UI-015-04 | Toggle disabled | Upload in progress (files in uploading/waiting state) |

## Telemetry & Observability

No telemetry events required for this feature. Standard Laravel logging applies for errors during processing.

## Documentation Deliverables

- Update upload modal documentation to mention watermark toggle
- Update watermarking documentation to explain per-upload toggle behavior
- Document `watermark_optout_disabled` admin setting in settings reference

## Fixtures & Sample Data

No additional fixtures required. Uses existing watermark test setup.

## Spec DSL

```yaml
config:
  - id: CFG-015-01
    key: watermark_optout_disabled
    type: boolean
    default: false
    category: Mod Watermarker
    description: "When true, users cannot opt out of watermarking during upload"
domain_objects:
  - id: DO-015-01
    name: UploadPhotoRequest
    fields:
      - name: apply_watermark
        type: boolean
        constraints: "optional, defaults to null"
  - id: DO-015-02
    name: ProcessImageJob
    fields:
      - name: apply_watermark
        type: boolean
        constraints: "nullable, null = use global setting"
  - id: DO-015-03
    name: UploadConfig
    fields:
      - name: is_watermarker_enabled
        type: boolean
        constraints: "computed: watermark_enabled AND watermark_photo_id AND imagick"
      - name: can_watermark_optout
        type: boolean
        constraints: "computed: is_watermarker_enabled AND NOT watermark_optout_disabled"
routes:
  - id: API-015-01
    method: GET
    path: /Gallery::getUploadLimits
    response_changes:
      - field: is_watermarker_enabled
        type: boolean
      - field: can_watermark_optout
        type: boolean
  - id: API-015-02
    method: POST
    path: /Photo
    request_changes:
      - field: apply_watermark
        type: boolean
        required: false
ui_states:
  - id: UI-015-01
    description: Watermark toggle visible and ON (default)
  - id: UI-015-02
    description: Watermark toggle visible and OFF
  - id: UI-015-03
    description: Watermark toggle hidden (watermarking not available or opt-out disabled)
  - id: UI-015-04
    description: Watermark toggle disabled (upload in progress)
```

## Appendix

### Current Watermarking Flow

1. User uploads photo via UploadPanel.vue
2. Photo chunks sent to `/Photo` endpoint (PhotoController::upload)
3. After final chunk, ProcessImageJob dispatched
4. ProcessImageJob executes pipeline including ApplyWatermark pipe
5. ApplyWatermark checks `watermark_enabled` config and applies watermark to all size variants

### Proposed Watermarking Flow

1. User uploads photo via UploadPanel.vue
2. **If `can_watermark_optout` is true, user can toggle watermark switch (default: ON)**
3. **If `watermark_optout_disabled` is true, toggle hidden and watermark always applied**
4. Photo chunks sent to `/Photo` endpoint with `apply_watermark` param
5. After final chunk, ProcessImageJob dispatched **with `apply_watermark` flag**
6. ProcessImageJob executes pipeline
7. **ApplyWatermark pipe checks `apply_watermark` flag; if false, skips watermarking**

### Admin Configuration Flow

1. Admin navigates to Settings > Mod Watermarker
2. Admin toggles "Disable watermark opt-out" setting
3. Setting saved to `configs` table as `watermark_optout_disabled`
4. When enabled (true): Users cannot opt out, all uploads are watermarked when watermarking is enabled
5. When disabled (false, default): Users see toggle and can choose to skip watermarking

### Related Components

- `resources/js/components/modals/UploadPanel.vue` - Upload modal UI
- `resources/js/components/forms/upload/UploadingLine.vue` - Individual file upload
- `resources/js/services/upload-service.ts` - Upload HTTP service
- `app/Http/Resources/GalleryConfigs/UploadConfig.php` - Upload configuration
- `app/Http/Requests/Photo/UploadPhotoRequest.php` - Upload request validation
- `app/Http/Controllers/Gallery/PhotoController.php` - Upload controller
- `app/Jobs/ProcessImageJob.php` - Photo processing job
- `app/Actions/Photo/Pipes/Standalone/ApplyWatermark.php` - Watermark application pipe

---

*Last updated: 2026-02-24*
