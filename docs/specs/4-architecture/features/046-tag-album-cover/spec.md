# Feature 046 – Tag Album Custom Cover

| Field | Value |
|-------|-------|
| Status | Draft |
| Feature ID | F-046 |
| Author(s) | ildyria |
| Created | 2026-06-28 |
| Last updated | 2026-06-28 |
| Related features | F-003 (Album Decorations) |
| ADRs | — |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

Tag albums currently have no mechanism for setting a custom cover photo; their thumbnail is dynamically computed from the first matching photo. Regular `Album` models support an explicit `cover_id` FK on the `albums` table. This feature adds a `cover_id` column to the `tag_albums` table so that tag albums can also have a user-selected cover photo. The `albums.cover_id` column remains untouched. The front-end context menu, API route, request validation, and resource serialisation are updated to support both album types.

## Resolved Clarifications

- **Q-046-01 (B):** `cover_id` is added to `tag_albums` only. `albums.cover_id` stays where it is. This avoids touching `HasAlbumThumb` which is tightly coupled to the `Album` model with its precomputed cover fields (`auto_cover_id_max_privilege`, `auto_cover_id_least_privilege`).
- **Q-046-02 (B):** Front-end guard uses `is_model_album || albumStore.tagAlbum !== undefined` (or equivalent check against the store's `tagAlbum` state). No new `AlbumConfig` flag needed.
- **Q-046-03 (resolved — not applicable):** Since `cover_id` stays per-model, each model defines its own `cover()` relationship. `TagAlbum` eager-loads its cover photo.

## Goals

- G1: Allow tag album owners to set a custom cover photo via the same "Set as cover" interaction available on regular albums.
- G2: Add `cover_id` to the `tag_albums` table with its own FK to `photos.id`.
- G3: Maintain full backward compatibility for existing albums — `albums.cover_id` is not modified.
- G4: Expose the tag album `cover_id` through the existing API endpoint (`POST /api/v2/Album::cover`) and serialise it in `HeadTagAlbumResource` and `EditableBaseAlbumResource`.

## Non-Goals

- NG1: Automatic cover computation for tag albums (precomputed `auto_cover_id_*` columns). Tag albums resolve photos dynamically via tag matching; precomputed covers require a fundamentally different approach and are out of scope.
- NG2: Header photo support for tag albums. Headers are album-specific (hero banners with colour palettes, title position, etc.).
- NG3: Smart album cover support. Smart albums have a different architecture and are out of scope.
- NG4: Moving `cover_id` to `base_albums`. Decided against (Q-046-01) to avoid `HasAlbumThumb` coupling issues.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|-------------------|--------|
| FR-046-01 | Add nullable `cover_id` char(24) column to `tag_albums` table with FK to `photos.id` (SET NULL on delete). | Column created, FK enforced. | Migration runs cleanly. | Migration fails → rollback. | — | G2 |
| FR-046-02 | `TagAlbum` model gains `cover_id` attribute and `cover()` HasOne relationship, eager-loaded. | `$tagAlbum->cover_id` and `$tagAlbum->cover` are accessible. | `cover_id` must reference an existing photo or be null. | — | — | G2 |
| FR-046-03 | `SetAsCoverRequest` accepts both `Album` and `TagAlbum` (remove the `instanceof Album` guard). | Request validates successfully for both album types. | Album ID must resolve to a `BaseAlbum` (not a smart album). Photo ID must exist. | Returns 422 if album is a smart album or IDs are invalid. | — | G1 |
| FR-046-04 | `AlbumController::cover()` works for both `Album` and `TagAlbum`. | Cover is toggled (set or cleared) on either album type. | — | — | — | G1 |
| FR-046-05 | `HeadTagAlbumResource` includes `cover_id` in its serialised output. | Front-end receives `cover_id` for tag albums. | — | — | — | G4 |
| FR-046-06 | `EditableBaseAlbumResource` populates `cover_id` for both album types (remove the `instanceof Album` guard for cover). | Editable resource always returns the cover_id. | — | — | — | G4 |
| FR-046-07 | Front-end context menu shows "Set as cover" for photos inside tag albums (separate from "Set as header" which remains album-only). | The `is_model_album` guard is split: "Set as cover" shown for model albums and tag albums; "Set as header" shown only for model albums. | User must have `can_edit` permission. | Menu item hidden if no edit permission. | — | G1 |
| FR-046-08 | Front-end "Set as cover" callback sends the correct API request for tag albums. | `photo-service.setAsCover()` is called with the tag album ID. | — | — | — | G1 |
| FR-046-09 | Tag album thumb resolution respects explicit `cover_id` over dynamic first-photo. | If `cover_id` is set, `getThumbAttribute()` returns that photo's thumb via the eager-loaded `cover` relationship. If null, falls back to existing dynamic behaviour. | — | — | — | G1 |
| FR-046-10 | `PhotosToBeDeletedDTO::forceDelete()` nullifies `tag_albums.cover_id` when the referenced photo is deleted. | `tag_albums.cover_id` set to null for any tag album referencing a deleted photo, matching the existing `albums.cover_id` nullification pattern at `PhotosToBeDeletedDTO:103`. | — | Orphaned `cover_id` reference if omitted (FK SET NULL handles DB-level, but the application-level bulk-delete path bypasses FK checks on some DB drivers). | — | G2 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|-------------|--------|
| NFR-046-01 | Migration must be reversible. | Data safety | `migrate:rollback` succeeds. | — | G3 |
| NFR-046-02 | `albums.cover_id` and `HasAlbumThumb` must not be modified. | Stability | Existing Album cover tests pass unchanged. | — | G3 |
| NFR-046-03 | Existing tests for album cover (`AlbumSetCoverTest`) continue to pass. | Regression prevention | `php artisan test --filter=AlbumSetCoverTest` green. | — | G3 |
| NFR-046-04 | Tag album cover photo is eager-loaded to avoid N+1 queries on album listing. | Performance | `TagAlbum::$with` includes `cover`. | — | G1 |

## UI / Interaction Mock-ups

### Photo context menu inside a tag album (current vs. new)

```
CURRENT (tag album):                    NEW (tag album):
+---------------------------+           +---------------------------+
| ★ Highlight               |           | ★ Highlight               |
|                           |           | 🖼 Set as cover            |  ← NEW
| 🏷 Tag                    |           | 🏷 Tag                    |
| 📄 License                |           | 📄 License                |
| ✏️ Rename                  |           | ✏️ Rename                  |
| 📋 Copy to…               |           | 📋 Copy to…               |
| 📁 Move                   |           | 📁 Move                   |
| 🗑 Delete                 |           | 🗑 Delete                 |
| ⬇ Download               |           | ⬇ Download               |
+---------------------------+           +---------------------------+
```

Note: "Set as header" is NOT shown for tag albums (NG2). The existing guard block in `contextMenu.ts` (lines 126–148) must be split so that "Set as cover" has a broader guard (model album OR tag album) while "Set as header" keeps the `is_model_album`-only guard.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-046-01 | Set cover on a tag album → `cover_id` persisted in `tag_albums`, thumb displays selected photo. |
| S-046-02 | Toggle cover off (same photo) on a tag album → `cover_id` set to null, thumb reverts to dynamic. |
| S-046-03 | Set cover on a regular album → existing behaviour unchanged (`albums.cover_id` used). |
| S-046-04 | Delete the cover photo via application delete action → `PhotosToBeDeletedDTO::forceDelete()` nullifies `tag_albums.cover_id` (matching existing `albums.cover_id` nullification at line 103). |
| S-046-05 | Context menu on tag album photo shows "Set as cover" when user has edit permission. |
| S-046-06 | Context menu on tag album photo hides "Set as cover" when user lacks edit permission. |
| S-046-07 | Context menu on tag album photo does NOT show "Set as header". |
| S-046-08 | API returns `cover_id` in `HeadTagAlbumResource` response. |

## Test Strategy

- **Unit:** Test `TagAlbum` model has `cover()` relationship. Test `TagAlbum::getThumbAttribute()` respects `cover_id`.
- **Feature:** Create `TagAlbumSetCoverTest` for S-046-01, S-046-02, S-046-04. Verify S-046-03 passes via existing `AlbumSetCoverTest`.
- **UI:** Manual verification of context menu visibility (S-046-05, S-046-06, S-046-07) and cover display.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-046-01 | `tag_albums.cover_id` — nullable char(24) FK to `photos.id` (SET NULL on delete) | Migration |
| DO-046-02 | `TagAlbum::cover()` — HasOne relationship to Photo, eager-loaded | Model |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-046-01 | POST /api/v2/Album::cover | Set/toggle cover — now accepts both Album and TagAlbum | Existing route, widened scope |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-046-01 | "Set as cover" visible in tag album photo context menu | User has `can_edit` on tag album |
| UI-046-02 | "Set as cover" hidden in tag album photo context menu | User lacks `can_edit` on tag album |
| UI-046-03 | "Set as header" NOT visible in tag album photo context menu | Tag album — header is album-only |

## Telemetry & Observability

No new telemetry events. The existing cover-setting flow does not emit events.

## Documentation Deliverables

- Update knowledge map with `TagAlbum.cover_id` relationship.

## Rollout & Migration

1. Laravel migration adds `cover_id` to `tag_albums` with FK to `photos.id` (SET NULL on delete).
2. Down method drops the column.
3. No feature flag needed — the change is additive.

## Open Questions

All resolved — see "Resolved Clarifications" section above.

| Question ID | Short description | Resolution |
|-------------|-------------------|------------|
| ~~Q-046-01~~ | `cover_id` location | B — add to `tag_albums` only |
| ~~Q-046-02~~ | Front-end guard mechanism | B — check `is_model_album \|\| tagAlbum` in context menu |
| ~~Q-046-03~~ | `cover()` relationship location | N/A — per-model, eager-load on TagAlbum |

## Spec DSL

```yaml
domain_objects:
  - id: DO-046-01
    name: tag_albums.cover_id
    fields:
      - name: cover_id
        type: "char(24), nullable"
        constraints: "FK photos.id, SET NULL on delete"
  - id: DO-046-02
    name: TagAlbum::cover
    type: HasOne<Photo>
    eager_loaded: true
routes:
  - id: API-046-01
    method: POST
    path: /api/v2/Album::cover
    changes: "Accept Album and TagAlbum (was Album-only)"
ui_states:
  - id: UI-046-01
    description: "Set as cover visible in tag album context menu"
  - id: UI-046-02
    description: "Set as cover hidden without edit permission"
  - id: UI-046-03
    description: "Set as header NOT visible for tag albums"
```
