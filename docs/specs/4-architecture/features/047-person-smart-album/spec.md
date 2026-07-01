# Feature 047 – Person Smart Album

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-06-28 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/047-person-smart-album/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/047-person-smart-album/tasks.md` |
| Roadmap entry | #047 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Introduces a **Person Album** — a user-created album that dynamically collects all photos containing one or more identified persons from the facial recognition system. The architecture mirrors the existing **TagAlbum** pattern: a persistent `BaseAlbum` subclass (`PersonAlbum`) linked to persons via a many-to-many junction table, with AND/OR match semantics. Photos are resolved at query time by joining through the `faces` table (face → person, face → photo).

**Affected modules:** Models (`PersonAlbum`), Relations (`HasManyPhotosByPerson`), Actions (`CreatePersonAlbum`), Controllers (`AlbumController`), Requests, Resources, Routes, Migrations, Frontend (dialog, album-service, stores, TypeScript types, translations).

**Availability gates:** The Person Album feature is only available when **both** `config('features.v8') === true` **and** the database config `ai_vision_face_enabled` is truthy. If either is disabled, the creation UI is hidden, existing Person Albums are excluded from listings, and API endpoints return 403.

## Goals

1. Allow users to create a Person Album by selecting one or more `Person` records and choosing AND or OR match semantics.
2. Display all photos containing faces linked to the selected persons, respecting existing photo access control (searchability, sensitivity, NSFW).
3. Show Person Albums alongside Tag Albums on the root albums page (new `person_albums` section in `RootAlbumResource`).
4. Support editing: rename, change persons list, toggle AND/OR, change sorting/layout/timeline, update protection policy.
5. Support deletion via the existing `Album::delete()` pathway.
6. Gate the feature behind `features.v8` + `ai_vision_face_enabled`.

## Non-Goals

- Automatically creating Person Albums when a person is identified (user must explicitly create them).
- Merging Person Albums with Tag Albums or regular Albums.
- Facial recognition itself — this feature consumes existing `Person`/`Face` data.
- Any changes to the Person/Face detection pipeline.
- Nested/hierarchical Person Albums (always at root level, like Tag Albums).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|-------------------|--------|
| FR-047-01 | User can create a Person Album with a title, one or more persons, and an AND/OR toggle. | Album created, ID returned, appears in root listing under `person_albums`. | Title required (max 100 chars), persons array required (min 1), is_and boolean required. Invalid input → HTTP 422. | DB error → HTTP 500. | — | TagAlbum pattern |
| FR-047-02 | Person Album dynamically resolves photos by joining `faces` → `persons` and applying AND/OR logic. | AND: photos must have faces linked to ALL selected persons. OR: photos with faces linked to ANY selected person. | — | No matching photos → empty album (valid state). | — | TagAlbum `HasManyPhotosByTag` |
| FR-047-03 | Photo access control is applied: searchability filter, sensitivity filter, NSFW filter. | Only photos the current user may see appear in the album. | — | — | — | `HasManyPhotosByTag` pattern |
| FR-047-04 | Person Albums appear in the root album listing within the Smart Albums section (merged into the `smartAlbums` getter alongside tag albums, per Q-047-04). | Root API returns `person_albums` collection. Frontend concatenates them into the `smartAlbums` getter. | — | Feature disabled → collection empty. | — | `RootAlbumResource`, Q-047-04 |
| FR-047-05 | Person Album supports editing: title, description, copyright, persons list, is_and, sorting, layout, timeline, is_pinned, slug. | PATCH endpoint updates album; returns `EditableBaseAlbumResource`. | Same validation as create for title/persons/is_and. | Validation error → HTTP 422. | — | `UpdateTagAlbumRequest` |
| FR-047-06 | Person Album supports deletion via the album delete flow. `Delete::do()` gains a `deletePersonAlbums()` method mirroring `deleteTagAlbums()` — cleans up purchasables, live_metrics, access_permissions, statistics, person_albums, base_albums (Q-047-02). | DELETE removes album record and all related rows. | Album ID required. | Not found → 404. | — | `Delete` action, Q-047-02 |
| FR-047-07 | Person Album supports protection policy (public/private/password) via existing `SetProtectionPolicy`. | Same behaviour as TagAlbum protection. | — | — | — | Existing infrastructure |
| FR-047-08 | Person Album creation and listing are gated behind `features.v8 === true` AND `ai_vision_face_enabled === true`. | When both active: full functionality. | When either is false: POST/PATCH → 403, root listing excludes person_albums. | Frontend hides creation UI when feature is disabled. | — | Owner directive |
| FR-047-09 | Person Album photo resolution applies the same visibility override logic as TagAlbum (`PA_override_visibility` config). | When config is true, uses `applySensitivityFilter`; when false, uses `applySearchabilityFilter`. | — | — | — | `HasManyPhotosByTag` pattern |
| FR-047-10 | Person Album has a thumbnail computed the same way as TagAlbum (via `Thumb::createFromQueryable`). | Thumbnail is the first photo according to the album's effective sorting. | — | No photos → null thumb. | — | `TagAlbum::getThumbAttribute()` |
| FR-047-11 | Person Albums are excluded from Bulk Album Edit (same as Tag Albums per Q-034-01). | Bulk edit query joins only `albums` table. | — | — | — | FR-034-01 |
| FR-047-12 | `HeadPersonAlbumResource` includes the list of person names and IDs for the selected persons, plus `is_person_album: true` discriminator. `show_persons` is filtered by the current user's visibility (Q-047-05): non-searchable persons invisible to the current user are omitted from the response. | Resource returns `show_persons` array of `{id, name}` objects, visibility-filtered. | — | — | — | `HeadTagAlbumResource` pattern, Q-047-05 |
| FR-047-13 | When a Person is deleted and that was the last person in a PersonAlbum, the orphaned album is automatically cleaned up via `CleanupOrphanedPersonAlbumsJob`. Job is triggered in the `PeopleController::destroy()` code path and registered as an event listener for other deletion paths (merge, CLI) (Q-047-06). | Orphaned PersonAlbums are deleted. | — | Job failure logged; orphaned album persists until next trigger. | — | Q-047-06 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-047-01 | Person Album photo query must use subquery-based filtering (same as TagAlbum) to avoid duplicate rows and PostgreSQL ORDER BY issues. | Database compatibility, performance | No DISTINCT on outer query; no ORDER BY errors on PostgreSQL | `HasManyPhotosByTag` pattern | Architecture |
| NFR-047-02 | Migration must be reversible. | Deployment safety | `php artisan migrate:rollback` succeeds cleanly. | — | Convention |
| NFR-047-03 | API endpoints must be gated at the request authorization level, not just UI. | Security | Unauthenticated or unauthorized calls return 401/403. | `AlbumPolicy` | Convention |
| NFR-047-04 | Feature gating must prevent data leakage — when disabled, existing Person Albums must not appear in any API response. | Privacy/security | Root listing and album head endpoint return empty/403 when gates are off. | `features.v8`, `ai_vision_face_enabled` | Owner directive |
| NFR-047-05 | Frontend must hide the "Create Person Album" button when the feature is disabled. | UX consistency | Button absent from DOM when `initConfig.is_person_album_enabled === false`. | Frontend config | Owner directive |
| NFR-047-06 | New config key `PA_override_visibility` (default: false) controls whether Person Albums bypass album-based access control (same semantics as `TA_override_visibility` for TagAlbums). | Feature parity | Config present in configs table after migration. | — | TagAlbum parity |
| NFR-047-07 | New config key `hide_nsfw_in_person_albums` (default: true) controls whether NSFW photos appear in Person Albums. | Feature parity | Config present in configs table after migration. | — | TagAlbum parity |

## UI / Interaction Mock-ups

### Create Person Album Dialog

```
+-----------------------------------------------+
|                                               |
|  Select the persons to include in this album  |
|                                               |
|  Title:                                       |
|  +-------------------------------------------+|
|  | [Album title input]                       ||
|  +-------------------------------------------+|
|                                               |
|  Persons:                                     |
|  +-------------------------------------------+|
|  | [Person autocomplete/multiselect]         ||
|  | +---------+ +---------+ +---------+       ||
|  | | Alice X | | Bob   X | | Eve   X |       ||
|  | +---------+ +---------+ +---------+       ||
|  +-------------------------------------------+|
|                                               |
|  [x] All persons must match (AND)             |
|                                               |
|  +-------------------+---------------------+ |
|  |      Cancel        |      Create         | |
|  +-------------------+---------------------+ |
+-----------------------------------------------+
```

### Root Album Listing (Person Albums merged into Smart Albums, per Q-047-04)

```
+-----------------------------------------------+
| Smart Albums (includes tag + person albums)   |
|  [Unsorted] [Recent] [Highlighted] ...        |
|  [Nature] [Vacation] ...          (tag albums)|
|  [Family (Alice & Bob)] [Friends] (person)    |
+-----------------------------------------------+
| Pinned Albums                                 |
|  [Wedding 2025] ...                           |
+-----------------------------------------------+
| Albums                                        |
|  [Summer Trip] [Birthday] ...                 |
+-----------------------------------------------+
```

### Album Properties Panel (Person Album variant)

```
+-----------------------------------------------+
| Album Properties                              |
|                                               |
|  Title: [Family Photos                     ]  |
|  Description: [                            ]  |
|  Copyright: [                              ]  |
|                                               |
|  Persons:                                     |
|  +-------------------------------------------+|
|  | [Person autocomplete/multiselect]         ||
|  | +---------+ +---------+                   ||
|  | | Alice X | | Bob   X |                   ||
|  | +---------+ +---------+                   ||
|  +-------------------------------------------+|
|                                               |
|  [x] All persons must match (AND)             |
|                                               |
|  Sorting: [Created At v] [DESC v]             |
|  Layout:  [Default       v]                   |
|  Timeline: [Default      v]                   |
|  [x] Pinned                                  |
|                                               |
|  [Save]                                       |
+-----------------------------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-047-01 | Create Person Album with one person (OR mode) → album shows all photos containing that person |
| S-047-02 | Create Person Album with two persons (AND mode) → album shows only photos containing BOTH persons |
| S-047-03 | Create Person Album with two persons (OR mode) → album shows photos containing EITHER person |
| S-047-04 | Create Person Album when `features.v8` is false → 403 |
| S-047-05 | Create Person Album when `ai_vision_face_enabled` is false → 403 |
| S-047-06 | Root listing with `features.v8` enabled and `ai_vision_face_enabled` → person_albums section appears |
| S-047-07 | Root listing with feature disabled → person_albums section absent or empty |
| S-047-08 | Update Person Album: change persons, toggle AND/OR → photo set updates on next load |
| S-047-09 | Delete Person Album → album removed, junction table entries cascade-deleted |
| S-047-10 | Person Album respects photo access control: non-owner cannot see private photos via person album |
| S-047-11 | Person Album with `PA_override_visibility = true` shows all photos with matched persons regardless of album permissions |
| S-047-12 | Person Album with no matching photos → empty album displayed correctly |
| S-047-13 | Person Album thumbnail shows first photo by effective sorting |
| S-047-14 | Person Album appears in album head response with `is_person_album: true` and `show_persons` array |
| S-047-15 | Person Album supports protection policy (password-protected person album) |
| S-047-16 | Frontend hides "Create Person Album" button when feature gates are off |
| S-047-17 | Person deleted → orphaned PersonAlbum (zero persons remaining) is auto-deleted by `CleanupOrphanedPersonAlbumsJob` |
| S-047-18 | `HeadPersonAlbumResource.show_persons` omits non-searchable persons invisible to the current user |
| S-047-19 | Photo pagination via `GET /Album::photos` works correctly for PersonAlbum |

## Test Strategy

- **Core/Unit:** Test `HasManyPhotosByPerson` relation with AND/OR logic, access control filters, edge cases (no persons, no matching photos, dismissed faces excluded).
- **Feature/REST:** Test POST/PATCH/DELETE endpoints with valid/invalid data, authorization, feature gating (v8 off, ai_vision off). Test root listing includes/excludes person albums based on feature gates.
- **UI (manual):** Verify create dialog, edit panel, root listing section, thumbnail display, feature gating hides UI elements.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-047-01 | `PersonAlbum` model — extends `BaseAlbum`, attributes: `id`, `is_and`. Relations: `persons()` (BelongsToMany via `person_albums_persons`), `photos()` (HasManyPhotosByPerson). | Models |
| DO-047-02 | `person_albums` table — `id` (char 24, FK to `base_albums`), `is_and` (boolean). | Database |
| DO-047-03 | `person_albums_persons` junction table — `id` (auto-increment), `person_id` (char 24, FK to `persons`), `album_id` (char 24, FK to `person_albums`), unique on `(person_id, album_id)`. | Database |
| DO-047-04 | `HasManyPhotosByPerson` relation — custom Eloquent relation resolving photos via `faces` table join. | Relations |
| DO-047-05 | `CleanupOrphanedPersonAlbumsJob` — job that finds PersonAlbums with zero persons in the junction table and deletes them (Q-047-06). Triggered in `PeopleController::destroy()` and as event listener. | Jobs |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-047-01 | REST POST `/api/v2/PersonAlbum` | Create a person album | Body: `{title, persons: [id,...], is_and}` |
| API-047-02 | REST PATCH `/api/v2/PersonAlbum` | Update a person album | Body: `{album_id, title, description, persons: [id,...], is_and, ...}` |
| API-047-03 | REST GET `/api/v2/Albums` (root) | Root listing now includes `person_albums` | Extended `RootAlbumResource` |
| API-047-04 | REST GET `/api/v2/Album::head` | Album head for PersonAlbum | Returns `HeadPersonAlbumResource` |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-047-01 | Create Person Album dialog visible | User clicks "Create Person Album" in albums header menu |
| UI-047-02 | Create Person Album dialog: persons multiselect populated | Dialog loads available persons via API |
| UI-047-03 | Album Properties panel: person album variant | User opens properties for a PersonAlbum → shows persons multiselect + AND/OR toggle |
| UI-047-04 | Root listing: person albums in Smart Albums section | Person Albums merged into `smartAlbums` getter alongside tag albums (Q-047-04) |
| UI-047-05 | Feature gates off: UI hidden | Create button absent, person_albums section empty |

## Telemetry & Observability

No new telemetry events. Person Albums reuse existing album statistics (`AlbumStatisticsResource`).

## Documentation Deliverables

- Update roadmap with Feature 047 entry.
- Update knowledge map with `PersonAlbum` model and `HasManyPhotosByPerson` relation.
- No new ADRs expected (follows established TagAlbum pattern).

## Fixtures & Sample Data

No new fixtures required. Tests will create Person and Face records inline.

## Spec DSL

```yaml
domain_objects:
  - id: DO-047-01
    name: PersonAlbum
    fields:
      - name: id
        type: string(24)
        constraints: "FK to base_albums"
      - name: is_and
        type: boolean
        constraints: "default false"
  - id: DO-047-02
    name: person_albums
    type: table
    fields:
      - name: id
        type: char(24)
      - name: is_and
        type: boolean
  - id: DO-047-03
    name: person_albums_persons
    type: junction_table
    fields:
      - name: id
        type: auto_increment
      - name: person_id
        type: char(24)
        constraints: "FK to persons.id, cascadeOnDelete"
      - name: album_id
        type: char(24)
        constraints: "FK to person_albums.id, cascadeOnDelete"
routes:
  - id: API-047-01
    method: POST
    path: /api/v2/PersonAlbum
    auth: "Gate AlbumPolicy::CAN_EDIT on null parent + feature gates"
  - id: API-047-02
    method: PATCH
    path: /api/v2/PersonAlbum
    auth: "Gate AlbumPolicy::CAN_EDIT on album + feature gates"
ui_states:
  - id: UI-047-01
    description: Create Person Album dialog
  - id: UI-047-02
    description: Person multiselect in dialog
  - id: UI-047-03
    description: Album Properties person variant
  - id: UI-047-04
    description: Root listing person_albums section
  - id: UI-047-05
    description: Feature gates off hides UI
config_keys:
  - key: PA_override_visibility
    type: boolean
    default: "0"
    category: smart-albums
    description: "When true, Person Albums bypass album-based access control (show all photos matching persons)"
  - key: hide_nsfw_in_person_albums
    type: boolean
    default: "1"
    category: smart-albums
    description: "When true, NSFW photos are hidden from Person Albums"
```

## Appendix

### Photo Resolution Query (AND mode)

```sql
SELECT photos.id FROM photos
WHERE EXISTS (
    SELECT face.photo_id, COUNT(DISTINCT face.person_id) AS num
    FROM faces AS face
    WHERE face.person_id IN (?, ?, ...)
      AND face.photo_id = photos.id
      AND face.is_dismissed = false
    GROUP BY face.photo_id
    HAVING COUNT(DISTINCT face.person_id) = ?
)
```

### Photo Resolution Query (OR mode)

```sql
SELECT photos.id FROM photos
WHERE EXISTS (
    SELECT face.photo_id
    FROM faces AS face
    WHERE face.person_id IN (?, ?, ...)
      AND face.photo_id = photos.id
      AND face.is_dismissed = false
)
```

### Comparison with TagAlbum

| Aspect | TagAlbum | PersonAlbum |
|--------|----------|-------------|
| Table | `tag_albums` | `person_albums` |
| Junction | `tag_albums_tags` (tag_id, album_id) | `person_albums_persons` (person_id, album_id) |
| Relation | `HasManyPhotosByTag` via `photos_tags` | `HasManyPhotosByPerson` via `faces` |
| AND/OR | `is_and` column | `is_and` column |
| Feature gate | None (always available) | `features.v8` + `ai_vision_face_enabled` |
| Config override | `TA_override_visibility` | `PA_override_visibility` |
| NSFW config | `hide_nsfw_in_tag_albums` | `hide_nsfw_in_person_albums` |
