# Feature 016 – Bulk License Edit

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-02-26 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/016-bulk-license-edit/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/016-bulk-license-edit/tasks.md` |
| Roadmap entry | #016 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature enables users to edit the license property of multiple photos simultaneously through a bulk edit dialog. When multiple photos are selected in the gallery, users can access a "Set License" action that opens a dialog allowing them to choose a license type from the standard Lychee license options (None, Reserved, CC0, various Creative Commons variants). The selected license is then applied to all selected photos in a single operation.

**Affected modules:** Application (SetPhotosLicenseRequest, PhotoController), REST API (Photo::license endpoint), UI (PhotoLicenseDialog component, gallery selection context menu).

## Goals

- Allow users to set the license for multiple photos at once
- Provide a consistent UI pattern matching existing bulk operations (tags, move, delete)
- Support all existing Lychee license types defined in LicenseType enum
- Require edit permissions on all selected photos before allowing the operation
- Display clear feedback on success/failure
- Maintain data integrity with database transactions

## Non-Goals

- Per-photo license configuration within the bulk dialog (all selected photos receive the same license)
- Creating new custom license types (use existing LicenseType enum values)
- Retroactive license history or audit trail
- Album-level default license inheritance
- Batch processing for extremely large photo sets (>1000 photos)
- License validation based on photo content or existing license restrictions

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-016-01 | Bulk license edit dialog accessible from gallery selection | When 1+ photos selected, context menu shows "Set License" action. Clicking opens PhotoLicenseDialog modal. | Action visible only when photos selected. Single photo or multiple photos both supported. | If no photos selected, action not shown. If user lacks edit permission on any photo, authorization fails. | No telemetry (UI interaction). | User requirement |
| FR-016-02 | Dialog displays dropdown with all license types | PhotoLicenseDialog shows dropdown populated with all LicenseType enum values and their localized names. Default selection is "None". | Dropdown built from LicenseType.localized() values. All 31 license options available. | N/A | No telemetry (UI state). | User requirement |
| FR-016-03 | Backend validates and updates multiple photos | Photo::license endpoint accepts `photo_ids` array and `license` value. Validates user has edit permissions on all photos. Updates license field for all valid photos in single transaction. Processes photos in chunks of 100 for memory efficiency. | SetPhotosLicenseRequest validates: photo_ids (required, array, min:1, valid random IDs), license (required, valid enum value). Backend chunks processing at 100 photos per batch. | Returns 422 if validation fails. Returns 403 if user lacks permission on any photo. | No telemetry (DB update). | Implementation requirement |
| FR-016-04 | Only owner or users with edit permission can bulk edit license | Authorization policy checks PhotoPolicy::CAN_EDIT for each photo in the request. | AuthorizeCanEditPhotosTrait used in request class. | 403 Forbidden if user cannot edit any photo in the set. | No telemetry (authorization). | Security requirement |
| FR-016-05 | Success feedback and cache invalidation | On successful update, show success toast with count of updated photos. Clear parent album cache to refresh view. | Toast displays localized message: "License updated for X photos". AlbumService.clearCache() called for parent album. | N/A | No telemetry (UI feedback). | User experience requirement |
| FR-016-06 | Database transaction ensures atomicity | All photo license updates wrapped in DB transaction. Either all succeed or all roll back. | Use DB::transaction() to wrap update operations. Backend processes in chunks of 100 for efficiency. | If any update fails mid-operation, entire batch rolls back. | Standard Laravel exception logging. | Data integrity requirement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-016-01 | Bulk operation completes in reasonable time | User experience for batch operations | Updates should complete in <5s for up to 100 photos. Use chunked processing (100 photos per chunk) with batch update query to manage memory. No UI limit on batch size. | Laravel batch() helper, chunked processing, database performance | Performance standard |
| NFR-016-02 | Code follows Lychee PHP conventions | Maintainability and code quality | License headers, snake_case variables, strict comparison (===), PSR-4, no `empty()`, `in_array(..., true)`. | php-cs-fixer, phpstan level 6 | [docs/specs/3-reference/coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-016-03 | Frontend follows Vue3/TypeScript conventions | Maintainability and code quality | Template-first component structure, Composition API, regular function declarations, `.then()` instead of async/await, axios calls in services directory. | Prettier, frontend tests | [docs/specs/3-reference/coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-016-04 | Test coverage for bulk license scenarios | Ensure correctness and prevent regression | Feature tests for: authorization (authorized/unauthorized/forbidden), single photo, multiple photos, invalid license value, permission checks. | BaseApiWithDataTest, in-memory SQLite | Testing standard |
| NFR-016-05 | Backward compatibility with single photo edit | API stability | Existing single photo edit via EditPhotoRequest remains unchanged and functional. | EditPhotoRequest, single photo edit tests | API contract |

## UI / Interaction Mock-ups

### 1. Gallery with Multiple Photos Selected

```
┌────────────────────────────────────────────────────────────────┐
│  Album: My Photos                              🔍 ⚙️ ⋮          │
├────────────────────────────────────────────────────────────────┤
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │  ✓       │ │  ✓       │ │          │ │          │          │
│  │          │ │          │ │          │ │          │          │
│  │ Photo 1  │ │ Photo 2  │ │ Photo 3  │ │ Photo 4  │          │
│  │          │ │          │ │          │ │          │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│                                                                 │
│  ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓  │
│  ┃  2 photos selected                                      ┃  │
│  ┃  ────────────────────────────────────────────────────   ┃  │
│  ┃  ★ Highlight    🏷️ Set Tags    ⚖️ Set License         ┃  │  ← NEW action
│  ┃  📁 Move        🗑️ Delete                              ┃  │
│  ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛  │
└────────────────────────────────────────────────────────────────┘
```

### 2. Bulk License Edit Dialog (Single Photo)

```
┌─────────────────────────────────────────────────────┐
│            Set license for photo                    │
│                                                     │
│  Select the license to apply:                      │
│                                                     │
│  ┌───────────────────────────────────────────┐     │
│  │  None                              ▼      │     │
│  └───────────────────────────────────────────┘     │
│    │                                               │
│    ├─ None                                         │
│    ├─ All Rights Reserved                          │
│    ├─ CC0 - Public Domain                          │
│    ├─ CC Attribution 1.0                           │
│    ├─ CC Attribution 2.0                           │
│    ├─ ...                                          │
│    └─ CC Attribution-NonCommercial-ShareAlike 4.0  │
│                                                     │
├─────────────────────────────────────────────────────┤
│  [ Cancel ]                      [ Set License ]   │
└─────────────────────────────────────────────────────┘
```

### 3. Bulk License Edit Dialog (Multiple Photos)

```
┌─────────────────────────────────────────────────────┐
│          Set license for 15 photos                  │
│                                                     │
│  Select the license to apply to all selected        │
│  photos:                                            │
│                                                     │
│  ┌───────────────────────────────────────────┐     │
│  │  CC Attribution 4.0                ▼      │     │
│  └───────────────────────────────────────────┘     │
│                                                     │
│  ⚠️  This will replace the existing license for    │
│     all selected photos.                           │
│                                                     │
├─────────────────────────────────────────────────────┤
│  [ Cancel ]                      [ Set License ]   │
└─────────────────────────────────────────────────────┘
```

### 4. Success Toast

```
┌─────────────────────────────────────────┐
│  ✓ Success                              │
│  License updated for 15 photos.         │
└─────────────────────────────────────────┘
```

**States:**

| State | Description |
|-------|-------------|
| Dialog closed | Default state, no selection |
| Dialog open (single photo) | Shows "Set license for photo" |
| Dialog open (multiple photos) | Shows "Set license for X photos" with count |
| License selected | User has chosen a license from dropdown |
| Submitting | Request in progress, buttons disabled |
| Success | Toast notification, dialog closes, view refreshes |
| Error | Toast notification with error message |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-016-01 | User selects single photo and sets license: Photo license updated, success toast shown |
| S-016-02 | User selects multiple photos and sets license: All photos updated with same license, success toast shows count |
| S-016-03 | User lacks edit permission: Action disabled or returns 403 Forbidden |
| S-016-04 | User selects invalid license value: Returns 422 validation error |
| S-016-05 | Database error during update: Transaction rolls back, error toast shown |
| S-016-06 | User selects 100+ photos: Bulk update completes efficiently using chunked processing (100 photos per chunk) |
| S-016-07 | User cancels dialog: No changes made, dialog closes |

## Test Strategy

- **Core/Application:** Unit tests for SetPhotosLicenseRequest validation and authorization
- **REST:** Feature tests for Photo::license endpoint:
  - Authorized user updating single photo license
  - Authorized user updating multiple photos license
  - Unauthorized/forbidden scenarios
  - Invalid photo IDs
  - Invalid license values
  - Transaction rollback on partial failure
- **UI (JS):** Component tests for PhotoLicenseDialog:
  - Renders with single photo
  - Renders with multiple photos
  - Dropdown populated with license options
  - Submit triggers service call
  - Cancel closes dialog

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-016-01 | SetPhotosLicenseRequest: Validates `photo_ids` array and `license` enum value | Application (Request validation) |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-016-01 | PATCH /Photo::license | Bulk update license for multiple photos | New endpoint |

### UI Components

| ID | Component | Description |
|----|-----------|-------------|
| UI-016-01 | PhotoLicenseDialog.vue | Modal dialog for bulk license editing |

### API Request/Response

**Request:**
```json
{
  "photo_ids": ["abc123...", "def456..."],
  "license": "CC-BY-4.0"
}
```

**Response:** 204 No Content (success) or standard error responses

## Telemetry & Observability

No telemetry events required for this feature. Standard Laravel logging applies for errors during processing.

## Documentation Deliverables

- Update photo management documentation to mention bulk license editing
- Document Photo::license endpoint in API reference
- Update user guide with bulk license operation instructions

## Fixtures & Sample Data

Use existing test fixtures from BaseApiWithDataTest (photo1, photo2, album1, userMayUpload1, userNoUpload).

## Spec DSL

```yaml
domain_objects:
  - id: DO-016-01
    name: SetPhotosLicenseRequest
    fields:
      - name: photo_ids
        type: array<string>
        constraints: "required, min:1, valid random IDs"
      - name: license
        type: LicenseType
        constraints: "required, valid enum value"
routes:
  - id: API-016-01
    method: PATCH
    path: /Photo::license
    request_body:
      - photo_ids: array<string>
      - license: string
    responses:
      - 204: Success (No Content)
      - 403: Forbidden (lack permissions)
      - 422: Validation error
ui_components:
  - id: UI-016-01
    name: PhotoLicenseDialog
    props:
      - parentId: string | undefined
      - photo: PhotoResource | undefined
      - photoIds: string[] | undefined
    emits:
      - licensed: void
```

## Appendix

### Similar Bulk Operations for Reference

This feature follows the established pattern for bulk operations in Lychee:
- **SetPhotosTagsRequest** (`app/Http/Requests/Photo/SetPhotosTagsRequest.php`)
- **PhotoTagDialog.vue** (`resources/js/components/forms/photo/PhotoTagDialog.vue`)
- **PhotoController::tags()** method for backend processing

### License Type Enum Values

All 31 license values from `App\Enum\LicenseType`:
- NONE, RESERVED
- CC0
- CC-BY (1.0, 2.0, 2.5, 3.0, 4.0)
- CC-BY-ND (1.0, 2.0, 2.5, 3.0, 4.0)
- CC-BY-SA (1.0, 2.0, 2.5, 3.0, 4.0)
- CC-BY-NC (1.0, 2.0, 2.5, 3.0, 4.0)
- CC-BY-NC-ND (1.0, 2.0, 2.5, 3.0, 4.0)
- CC-BY-NC-SA (1.0, 2.0, 2.5, 3.0, 4.0)

---

*Last updated: 2026-02-26*
