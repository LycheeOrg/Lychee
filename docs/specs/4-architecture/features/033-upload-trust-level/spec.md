# Feature 033 – Upload Trust Level

| Field | Value |
|-------|-------|
| Status | Draft (questions Q-033-01 – Q-033-03 resolved) |
| Last updated | 2026-04-09 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/033-upload-trust-level/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/033-upload-trust-level/tasks.md` |
| Roadmap entry | #1633 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

When Lychee is used as a community gallery (multiple users, public-facing albums, guest uploads via `grants_upload`), administrators need a way to prevent inappropriate or unwanted content from being displayed publicly before they have reviewed it. This feature introduces a per-user **upload trust level** that controls whether newly uploaded photos are immediately visible to the public or require explicit admin approval first.

Affected modules: `core` (User model, Photo model, enums), `application` (photo creation pipeline, query policies, moderation actions), `REST` (user management API, moderation API), `CLI` (user creation command), `UI` (user management form, admin moderation panel).

## Goals

1. Administrators can assign one of three trust levels (`check`, `monitor`, `trusted`) to each user, controlling whether that user's uploads require validation.
2. Photos uploaded by users with trust level `check` are persisted but hidden from public display until an administrator marks them as validated.
3. Photo owners always see their own photos regardless of validation status.
4. Administrators always see all photos regardless of validation status, with a visual indicator for unvalidated photos.
5. A dedicated admin moderation panel lists all unvalidated photos and supports bulk-approve operations.
6. Two global configuration settings control the default trust level for newly created users and the trust level applied to guest (anonymous) uploads.

## Non-Goals

- Implementing the `monitor` trust level's distinct soft-audit behaviour in this iteration — the enum value exists and uploads are immediately validated (like `trusted`), but the future monitoring queue (periodic admin review of `monitor` users' uploads) is deferred (resolved: Q-033-01 → A).
- Per-album trust level overrides — trust level is per-user only.
- Automated content moderation (AI-based image scanning, NSFW detection) — this is out of scope.
- Rejection workflow (deleting or quarantining photos that fail review) — the admin can use existing delete functionality.
- Notification system for users when their photos are approved or rejected.
- Trust level changes triggering retroactive validation/invalidation of existing photos — only future uploads are affected by trust level changes (resolved: Q-033-02 → A).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-033-01 | A new string column `upload_trust_level` is added to the `users` table with allowed values `check`, `monitor`, `trusted` (default: `trusted`). The column is cast to a `UserUploadTrustLevel` backed enum on the User model. | Column present after migration; User model returns enum instances. | Value must be one of the three allowed enum cases. Migration is reversible. | Invalid values rejected by enum cast. | No telemetry. | Problem statement |
| FR-033-02 | A new boolean column `is_upload_validated` is added to the `photos` table (default: `true`, indexed). The Photo model casts it to boolean. | Column present after migration; existing photos have `is_upload_validated = true`. | Default ensures backward compatibility — all pre-existing photos remain visible. | Migration must be reversible. | No telemetry. | Problem statement |
| FR-033-03 | When a photo is created (via upload, import, or URL import), the `is_upload_validated` flag is determined as follows: (1) if the intended owner is an admin (`may_administrate = true`), always set to `true` regardless of trust level (resolved: Q-033-03 → A); (2) if the intended owner's `upload_trust_level` is `check`, set to `false`; (3) for `trusted` or `monitor`, set to `true` — `monitor` behaves as `trusted` in this iteration (resolved: Q-033-01 → A). | Photo persisted with correct `is_upload_validated` flag based on owner's admin status and trust level. | Owner must be resolved before the flag is set. For album uploads where the owner is the album owner, the album owner's trust level is used. Admin check takes precedence over trust level. | If user cannot be resolved, default to `true` (fail-open for backward compatibility). | No telemetry (DB write). | Problem statement; Q-033-01 → A; Q-033-03 → A |
| FR-033-04 | For guest (anonymous) uploads to albums with `grants_upload = true`, the effective trust level is read from the `guest_upload_trust_level` configuration setting (default: `check`). | Guest-uploaded photos have `is_upload_validated = false` when config is `check`. | Config must be loaded before photo creation. | If config is missing, default to `check`. | No telemetry. | Problem statement |
| FR-033-05 | Photos with `is_upload_validated = false` are excluded from public visibility queries. Specifically, `PhotoQueryPolicy::applyVisibilityFilter` must add a condition that hides unvalidated photos from non-owner, non-admin users. | Non-admin, non-owner users do not see unvalidated photos in album views, search results, or smart albums. | Query produces correct SQL for all three user types (admin, owner, public). | No user sees unvalidated photos they should not see. | No telemetry (query filter). | Problem statement |
| FR-033-06 | Photo owners always see their own photos, including those with `is_upload_validated = false`. | Owner browsing their own albums or unsorted photos sees all their uploads. | Owner visibility query returns both validated and unvalidated photos. | — | No telemetry. | Problem statement |
| FR-033-07 | Administrators always see all photos regardless of `is_upload_validated`. The admin moderation panel specifically lists only unvalidated photos for review. | Admin sees all photos in normal gallery browsing. Moderation endpoint returns only unvalidated photos. | Admin query does not apply the validation filter. | — | No telemetry. | Problem statement |
| FR-033-08 | Admin can set the `upload_trust_level` for a user via the user management CRUD API (both create and update). The field is optional on create (defaults to the `default_user_trust_level` config value) and optional on update (keeps existing value if not provided). | Trust level persisted on user record; reflected in user management list. | Value must be a valid `UserUploadTrustLevel` enum case. | Return 422 if invalid value is provided. | No telemetry. | Problem statement |
| FR-033-09 | Two new configuration settings are added: `default_user_trust_level` (default: `trusted`, enum `check\|monitor\|trusted`) and `guest_upload_trust_level` (default: `check`, same enum). Both are editable via the admin settings panel. | Configs stored in `configs` table; accessible via `ConfigManager`. | Values validated against enum range. | Invalid values rejected. | No telemetry. | Problem statement |
| FR-033-10 | A new admin-only REST endpoint `GET /api/v2/Moderation` returns a paginated list of photos where `is_upload_validated = false`, ordered by `created_at DESC`. Each entry includes the photo resource data plus the owner's username. | Admin receives list of unvalidated photos. | Only admin users may call this endpoint (403 for non-admins). Empty list returned when no unvalidated photos exist. | Return 403 for non-admin users. Return 401 for unauthenticated requests. | No telemetry. | Problem statement |
| FR-033-11 | A new admin-only REST endpoint `POST /api/v2/Moderation::approve` accepts an array of photo IDs and sets `is_upload_validated = true` for all specified photos. | Photos marked as validated; become publicly visible. | Photo IDs must be valid, existing photo IDs. | Return 422 for invalid input. Return 403 for non-admin users. | No telemetry (DB update). | Problem statement |
| FR-033-12 | The `UserManagementResource` includes the user's `upload_trust_level` field so the admin UI can display and edit it. | Trust level visible in user management list and edit form. | Value serialised as the enum string value. | — | No telemetry. | Problem statement |
| FR-033-13 | The `PhotoResource` includes the `is_upload_validated` boolean field so the frontend can display a visual indicator for unvalidated photos (visible only to the owner and admins). | Field present in photo API response. | Boolean value correctly reflects DB state. | — | No telemetry. | Problem statement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-033-01 | The visibility filter for `is_upload_validated` must not degrade query performance for the common case (all photos validated). The indexed boolean column ensures efficient filtering. | Performance — photo listing queries must remain fast. | Benchmark: photo listing latency unchanged (< 5% overhead) when all photos are validated. Index on `is_upload_validated` column. | Database index migration. | Implementation requirement |
| NFR-033-02 | Backward compatibility: all existing photos default to `is_upload_validated = true` after migration, and all existing users default to `upload_trust_level = trusted`. No behaviour change for existing installations until the admin explicitly configures trust levels. | Backward compatibility — existing galleries must not break on upgrade. | After migration: all photos visible, all users trusted. | Migration defaults. | Implementation requirement |
| NFR-033-03 | The moderation endpoint must support pagination and return at most 100 photos per page to prevent excessive memory usage for large backlogs. | Scalability — installations with many pending photos must not OOM. | Endpoint respects `page` and `per_page` parameters (default 30, max 100). | Pagination middleware/logic. | Implementation requirement |
| NFR-033-04 | The bulk-approve endpoint must process up to 500 photo IDs per request efficiently using chunked updates (100 per batch). | Scalability — bulk approve must handle large selections without timeout. | Batch update completes in < 5 seconds for 500 photos. | Eloquent chunked update. | Implementation requirement |
| NFR-033-05 | Admin moderation panel UI must be consistent with existing admin pages (PrimeVue DataTable, toolbar, left-menu navigation). | UX consistency. | Visual review against Users and Webhooks admin pages. | PrimeVue, Vue 3. | Problem statement |
| NFR-033-06 | The trust level enum must be extensible — adding new levels in the future (e.g., activating `monitor` behaviour) should require only application-layer changes, not schema migrations. | Extensibility — `monitor` level is reserved for future use. | String-backed enum; no DB schema change needed to add new behaviour. | String column type. | Problem statement |

## UI / Interaction Mock-ups

### User Management — Trust Level in Create/Edit Dialog

```
┌────────────────────────────────────────────────────┐
│ Create User                                  [×]    │
├────────────────────────────────────────────────────┤
│ Username *       [____________________________]     │
│ Password *       [____________________________]     │
│                                                     │
│ [✓] User can upload content.                        │
│ [✓] User can modify their profile.                  │
│ [ ] User has admin rights. [SE]                     │
│                                                     │
│ Upload trust     [trusted              ▼]           │
│                  (check | monitor | trusted)         │
│                                                     │
│ [ ] User has quota limit. [SE]                      │
│ [  0  ] quota in kB (0 for default)                 │
│                                                     │
│ Admin note       [____________________________]     │
├────────────────────────────────────────────────────┤
│                          [Cancel]  [Create]         │
└────────────────────────────────────────────────────┘
```

### User Management — Trust Level in User List

```
┌──────────────────────────────────────────────────────────────────────┐
│  Username           │ ⬆ │ 🔓 │ 🛡  │                │              │
│─────────────────────┼───┼────┼─────┼────────────────┼──────────────│
│  alice              │ ✓ │ ✓  │ 🟢  │  [Edit]        │  [Delete]    │
│  bob                │ ✓ │ ✓  │ 🔴  │  [Edit]        │  [Delete]    │
│  carol              │ ✓ │ ✓  │ 🟡  │  [Edit]        │  [Delete]    │
└──────────────────────────────────────────────────────────────────────┘

Legend:
  ⬆  = Upload rights        🔓 = Edit rights
  🛡  = Trust level: 🟢 trusted  🟡 monitor  🔴 check
```

### Admin Moderation Panel

```
┌──────────────────────────────────────────────────────────────────────────┐
│ Admin › Moderation                                      [Approve Selected] │
├──────────────────────────────────────────────────────────────────────────┤
│ [☐] │ Thumbnail │ Title             │ Owner    │ Album       │ Uploaded  │
│─────┼───────────┼───────────────────┼──────────┼─────────────┼──────────│
│ [☐] │ [img]     │ IMG_20240101.jpg  │ bob      │ Vacation    │ 2h ago   │
│ [☐] │ [img]     │ sunset.png        │ bob      │ Unsorted    │ 3h ago   │
│ [☐] │ [img]     │ photo_001.jpg     │ guest    │ Public Pool │ 1d ago   │
│─────┼───────────┼───────────────────┼──────────┼─────────────┼──────────│
│                                                   Page 1 of 3  [< >]    │
└──────────────────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-033-01 | Admin creates a user with `upload_trust_level = check` → user record persisted with trust level `check`. |
| S-033-02 | Admin creates a user without specifying `upload_trust_level` → defaults to `default_user_trust_level` config (default: `trusted`). |
| S-033-03 | Admin updates a user's trust level from `trusted` to `check` → user record updated. Future uploads require validation. |
| S-033-04 | Admin updates a user but does not send `upload_trust_level` → existing value preserved. |
| S-033-05 | User with trust level `check` uploads a photo → photo created with `is_upload_validated = false`. |
| S-033-06 | User with trust level `trusted` uploads a photo → photo created with `is_upload_validated = true`. |
| S-033-07 | Guest uploads a photo to a public album (with `grants_upload = true`) and `guest_upload_trust_level` config is `check` → photo created with `is_upload_validated = false`. |
| S-033-08 | Non-admin, non-owner user requests album photos → unvalidated photos excluded from response. |
| S-033-09 | Photo owner requests their own photos → all photos returned including unvalidated ones. |
| S-033-10 | Admin requests album photos → all photos returned including unvalidated ones. |
| S-033-11 | Admin accesses moderation panel (`GET /api/v2/Moderation`) → paginated list of unvalidated photos returned. |
| S-033-12 | Admin approves a set of photo IDs (`POST /api/v2/Moderation::approve`) → photos set to `is_upload_validated = true`. |
| S-033-13 | Non-admin user accesses moderation endpoint → 403 Forbidden. |
| S-033-14 | Admin approves photos with some invalid IDs → 422 with validation error. |
| S-033-15 | Admin sets `default_user_trust_level` config to `check` → new users created without explicit trust level default to `check`. |
| S-033-16 | Admin sets `guest_upload_trust_level` config to `trusted` → guest uploads are immediately validated. |
| S-033-17 | Moderation panel with no unvalidated photos → empty state message displayed. |
| S-033-18 | Bulk approve of 200+ photos → processed in chunks, all marked as validated. |
| S-033-19 | Search results exclude unvalidated photos for non-admin/non-owner users. |
| S-033-20 | Smart albums (recent, unsorted, highlighted) exclude unvalidated photos for non-admin/non-owner users. |
| S-033-21 | `UserManagementResource` includes `upload_trust_level` field in API response. |
| S-033-22 | `PhotoResource` includes `is_upload_validated` boolean field. |
| S-033-23 | CLI `lychee:create_user` does not set trust level explicitly → defaults to `default_user_trust_level` config value. |

## Test Strategy

- **Core (Unit):** `UserUploadTrustLevel` enum construction and value validation. Unit test for trust-level resolution logic (user trust level vs guest config).
- **Application (Feature):** Feature tests for photo upload with different trust levels — verify `is_upload_validated` is correctly set. Feature tests for `PhotoQueryPolicy` visibility filtering — verify unvalidated photos are hidden from public, visible to owner and admin. Feature tests for moderation endpoints (list, approve) including auth enforcement and pagination.
- **REST (Feature):** User management CRUD tests verifying trust level field is accepted, validated, persisted, and returned. Moderation API tests: list unvalidated, bulk approve, auth.
- **CLI:** Test `lychee:create_user` with and without trust level flag (future enhancement).
- **UI (Vue/Vitest):** Unit tests for trust level dropdown in CreateEditUser dialog. Unit tests for moderation panel rendering and bulk selection.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-033-01 | `UserUploadTrustLevel` string-backed enum: `check`, `monitor`, `trusted`. Cast on `User.upload_trust_level` column. | core |
| DO-033-02 | `User.upload_trust_level` column: string, default `trusted`, cast to `UserUploadTrustLevel` enum. | core |
| DO-033-03 | `Photo.is_upload_validated` column: boolean, default `true`, indexed. Indicates whether the photo has been approved for public display. | core |
| DO-033-04 | Two config entries: `default_user_trust_level` (default `trusted`) and `guest_upload_trust_level` (default `check`), both with type range `check\|monitor\|trusted`. | core |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-033-01 | `GET /api/v2/Moderation` | List unvalidated photos (admin only). Paginated, ordered by `created_at DESC`. | Returns `ModerationResource[]`. |
| API-033-02 | `POST /api/v2/Moderation::approve` | Bulk approve photos by IDs (admin only). Accepts `photo_ids` array. | Returns 204 No Content on success. |
| API-033-03 | `PATCH /api/v2/UserManagement` (existing) | Updated to accept optional `upload_trust_level` field. | Backward compatible. |
| API-033-04 | `POST /api/v2/UserManagement` (existing) | Updated to accept optional `upload_trust_level` field (defaults to config). | Backward compatible. |

### UI Components

| ID | Component | Description |
|----|-----------|-------------|
| UI-033-01 | Trust level Select in `CreateEditUser.vue` | Dropdown for selecting user trust level (check / monitor / trusted). |
| UI-033-02 | Trust level indicator in `ListUser.vue` | Shield icon with colour coding indicating trust level in user list. |
| UI-033-03 | `Moderation.vue` view | Admin panel showing DataTable of unvalidated photos with thumbnail, title, owner, album, upload date. Checkbox selection and bulk-approve button. |
| UI-033-04 | Left menu entry for Moderation | Navigation link to `/moderation` visible only to admins. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-033-05 | Moderation panel — empty | No unvalidated photos → "No photos pending review" message. |
| UI-033-06 | Moderation panel — populated | Unvalidated photos exist → DataTable with selection checkboxes and Approve button. |
| UI-033-07 | Moderation panel — selection | Admin selects one or more photos → Approve button becomes active. |
| UI-033-08 | Moderation panel — approve success | Admin clicks Approve → selected photos removed from list, success toast displayed. |
| UI-033-09 | Trust level dropdown — default | When creating a new user, dropdown defaults to the `default_user_trust_level` config value. |

## Telemetry & Observability

No dedicated telemetry events are introduced in this iteration. Standard Laravel logging applies:
- Photo creation logs include the `is_upload_validated` status.
- Moderation approve actions are logged at INFO level with the approving admin's user ID and the list of approved photo IDs.

## Documentation Deliverables

- `docs/specs/4-architecture/knowledge-map.md` — add Upload Trust Level and Moderation entries.
- Update admin guide with trust level configuration and moderation workflow documentation.
- Inline PHPDoc on `UserUploadTrustLevel` enum, `is_upload_validated` column, and moderation endpoints.

## Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-033-01 | `database/factories/UserFactory.php` | Updated with `upload_trust_level` default. |
| FX-033-02 | Test fixtures in feature tests | Photos created by `check`-level users for moderation endpoint tests. |

## Spec DSL

```yaml
domain_objects:
  - id: DO-033-01
    name: UserUploadTrustLevel
    type: enum (string-backed)
    values:
      - check
      - monitor
      - trusted
  - id: DO-033-02
    name: User.upload_trust_level
    type: string column
    constraints: "default: trusted, cast: UserUploadTrustLevel"
  - id: DO-033-03
    name: Photo.is_upload_validated
    type: boolean column
    constraints: "default: true, indexed"
  - id: DO-033-04
    name: Config entries
    entries:
      - key: default_user_trust_level
        value: "trusted"
        type_range: "check|monitor|trusted"
      - key: guest_upload_trust_level
        value: "check"
        type_range: "check|monitor|trusted"

routes:
  - id: API-033-01
    method: GET
    path: /api/v2/Moderation
    auth: admin
    response: ModerationResource[] (paginated)
  - id: API-033-02
    method: POST
    path: /api/v2/Moderation::approve
    auth: admin
    request_body:
      - photo_ids: array<string>
    response: 204 No Content

ui_components:
  - id: UI-033-01
    name: Trust level Select (CreateEditUser)
  - id: UI-033-02
    name: Trust level indicator (ListUser)
  - id: UI-033-03
    name: Moderation.vue (admin panel)
  - id: UI-033-04
    name: Left menu entry

ui_states:
  - id: UI-033-05
    description: Moderation panel empty state
  - id: UI-033-06
    description: Moderation panel populated state
  - id: UI-033-07
    description: Moderation panel selection state
  - id: UI-033-08
    description: Moderation panel approve success
  - id: UI-033-09
    description: Trust level dropdown default
```

## Appendix

### Trust Level Decision Matrix

| User type | Trust level | `is_upload_validated` on new photo | Photo visible to public? |
|-----------|-------------|-----------------------------------|--------------------------|
| Registered user | `trusted` | `true` | Yes (subject to album permissions) |
| Registered user | `monitor` | `true` (same as trusted in this iteration; future: soft-audit queue per Q-033-01 → A) | Yes |
| Registered user | `check` | `false` | No (until admin approves) |
| Guest (anonymous) | per config | per `guest_upload_trust_level` config | Depends on config |
| Admin | always `true` (Q-033-03 → A) | `true` (admin short-circuit, trust level ignored) | Yes |

### Visibility Filter Truth Table

| Viewer | Photo `is_upload_validated` | Photo visible? |
|--------|---------------------------|----------------|
| Admin | `true` | Yes |
| Admin | `false` | Yes |
| Owner | `true` | Yes |
| Owner | `false` | Yes |
| Other authenticated user | `true` | Yes (if album accessible) |
| Other authenticated user | `false` | No |
| Anonymous user | `true` | Yes (if album public) |
| Anonymous user | `false` | No |
