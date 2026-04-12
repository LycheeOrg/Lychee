# Feature 034 – Bulk Album Edit

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-04-12 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/034-bulk-album-edit/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/034-bulk-album-edit/tasks.md` |
| Roadmap entry | #034 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Administrators currently have no way to update common metadata (license, visibility, layout, timeline, sorting, copyright, ownership) across many albums at once. This feature adds a dedicated admin-only **Bulk Album Edit** page where albums are listed in nested-set (`_lft`) order, can be filtered by name via a `LIKE` search, selected individually or all-at-once, and updated in a single batch operation. Ownership transfer also cascades to all descendants and demotes a transferred sub-album to a root level (as the existing `Transfer` action does). The page uses infinite scrolling **or** numbered pagination (configurable via the UI); no DataTable component is used. A persistent warning banner informs the admin that changes are final with no confirmation step.

**Affected modules:** `core` (Album/BaseAlbumImpl models, `Transfer` action), `application` (new `BulkEditAlbumsRequest`, new `BulkEditAlbumsAction`), `REST` (new `Admin\BulkAlbumController`), `UI` (new `BulkAlbumEdit.vue` page, route `/bulk-album-edit`, left-menu entry).

## Goals

1. Admin can list all albums ordered by `_lft ASC` with optional name filter (case-insensitive `LIKE`).
2. Admin can select individual albums or all visible albums (across all pages / loaded rows).
3. Admin can set any combination of the following fields for selected albums in one request:
   - `title` (optional — only if explicitly supplied)
   - `description`
   - `copyright`
   - `license` (`LicenseType` enum)
   - `photo_layout` (`PhotoLayoutType` enum)
   - `photo_sorting` (column + order)
   - `album_sorting` (column + order)
   - `album_thumb_aspect_ratio` (`AspectRatioType` enum)
   - `album_timeline` (`TimelineAlbumGranularity` enum)
   - `photo_timeline` (`TimelinePhotoGranularity` enum)
   - `is_nsfw` (boolean)
   - `owner_id` (integer — admin-only; cascades to descendants + demotes to root)
4. Ownership transfer cascades to all descendant albums and photos, and the album is demoted to root (reusing `Transfer::do()` logic).
5. The page supports both **infinite scroll** and **numbered pagination** with a per-page selector (25 / 50 / 100).
6. A warning banner at the top of the page informs the admin that changes are applied immediately without any confirmation dialog.
7. Bulk action buttons (Delete, Set Owner, Set Fields…) appear above the album list, not in column headers.
8. Admin can delete selected albums via the page (with the same semantics as the existing `DeleteAlbumsRequest`).

## Non-Goals

- Bulk editing of `TagAlbum` metadata (tag albums have a separate schema — deferred).
- Bulk editing of album access/protection policies — use the existing Sharing page.
- Editing individual album fields inline in the table — all edits go through the batch form.
- Pagination configuration persistence across sessions (page size resets on navigate).
- Non-admin users accessing this page.
- Confirmation dialogs for any bulk operation (per problem statement: no confirmation).
- Editing album cover or header photo from this page.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-034-01 | A new admin-only page `/bulk-album-edit` lists all regular albums (`albums` table joined with `base_albums`) ordered by `_lft ASC`. Each row shows: depth indicator, title, owner username, license, is_nsfw flag, created_at date. | Page renders list of albums in nested-set order. | Admin-only gate (403 for non-admin). Unauthenticated users receive 401/redirect. | Empty state shown when no albums exist. | No telemetry. | Problem statement |
| FR-034-02 | The list supports a name filter: a text input sends a `LIKE %search%` query (case-insensitive) to the backend. Filtering resets the current page to 1. | Matching albums returned in `_lft ASC` order. | Empty string / null means no filter. Filter does not break pagination. | No matches → empty state with "No albums match your search." | No telemetry. | Problem statement |
| FR-034-03 | The list supports both infinite scrolling and numbered pagination. A page-size selector (options: 25, 50, 100; default 50) controls results per page. Pagination mode (infinite vs numbered) is toggled by a control on the page. | Correct page/cursor-based results returned. Infinite scroll appends next page when sentinel is visible. Numbered pagination shows page number buttons. | Page and per_page parameters validated (page ≥ 1, per_page ∈ {25, 50, 100}). | Server-side pagination errors surface as toast notifications. | No telemetry. | Problem statement + problem statement ("infinite scrolling or paginated with page numbers") |
| FR-034-04 | Each album row has a checkbox. A "select all on current page" checkbox exists in the header row. A separate "select all matching" button selects all album IDs matching the current filter (regardless of current page). | Selected IDs are tracked in frontend state. Select-all-matching fetches all matching IDs from a dedicated endpoint `GET /api/v2/BulkAlbumEdit::ids`. | "Select all matching" capped at 1 000 IDs server-side for safety; a warning toast is shown if cap is reached. | Stale selection cleared when filter changes. | No telemetry. | Problem statement |
| FR-034-05 | A warning banner is displayed permanently at the top of the page: "⚠ Any modification made on this page is final. There is no confirmation step." | Banner visible on page load and remains throughout session. | Cannot be dismissed. | n/a | No telemetry. | Problem statement |
| FR-034-06 | Bulk action buttons are placed above the album list (not in table column headers): **Delete**, **Set Owner**, **Edit Fields**. Buttons are disabled when no albums are selected. | Buttons enabled when selection is non-empty. | n/a | n/a | No telemetry. | Problem statement |
| FR-034-07 | **Edit Fields** opens a side-panel or modal form. Every field is optional; only fields whose checkbox/toggle is explicitly enabled by the admin are included in the PATCH request. Fields available: description, copyright, license, photo_layout, photo_sorting (col+order), album_sorting (col+order), album_thumb_aspect_ratio, album_timeline, photo_timeline, is_nsfw. | Only enabled fields are sent in request body. Disabled fields are omitted entirely (null-safe partial update). | Each included field validated against its existing enum/type rules. | 422 returned with per-field errors surfaced in the form. | No telemetry. | Problem statement |
| FR-034-08 | A new REST endpoint `PATCH /api/v2/BulkAlbumEdit` accepts an array of album IDs (`album_ids`) and a partial set of fields (any combination from FR-034-07). Only admin users may call this endpoint. Fields present in the request are applied to all specified albums; absent fields are left unchanged. Updates are wrapped in a single DB transaction. | All specified albums updated. 204 No Content returned. | album_ids: required, array, min:1. Each ID must be a valid random ID for an existing `albums` record. Each present field validated per its own rules. | 422 for validation failure. 403 for non-admin. Transaction rolls back on any error. | No telemetry. | Problem statement |
| FR-034-09 | A new REST endpoint `POST /api/v2/BulkAlbumEdit::setOwner` accepts `album_ids` array and `owner_id` (integer). Admin-only. For each album, calls the existing `Transfer::do()` logic: updates `owner_id` on `base_albums`, removes the previous permission grant for the new owner, calls `makeRoot()` on the `albums` record and `fixOwnershipOfChildren()` to cascade to descendants. If the album is already a root album, `makeRoot()` is still called (no-op for tree position). | Ownership transferred and descendants updated. Tree integrity maintained. 204 returned. | album_ids: required, array, min:1. owner_id: required, must be an existing user ID. | 422 for validation failure. 403 for non-admin. Transaction rolls back on any error. | No telemetry. | Problem statement |
| FR-034-10 | A new REST endpoint `DELETE /api/v2/BulkAlbumEdit` accepts `album_ids` array. Admin-only. Delegates to the existing `Delete::do()` action. | Selected albums (and their descendants) deleted. 204 returned. | album_ids: required, array, min:1. Each must be an existing albums ID. | 422 for validation failure. 403 for non-admin. | No telemetry. | Problem statement |
| FR-034-11 | A new REST endpoint `GET /api/v2/BulkAlbumEdit` returns a paginated list of albums ordered by `_lft ASC`. Supports query parameters: `search` (string, optional), `page` (int ≥ 1), `per_page` (int ∈ {25, 50, 100}). Response includes: `data[]` (array of `BulkAlbumResource`), `meta.current_page`, `meta.last_page`, `meta.total`. Admin-only. | Correct paginated list returned. | Validated query params. | 422 for invalid params. 403 for non-admin. | No telemetry. | Problem statement |
| FR-034-12 | A new REST endpoint `GET /api/v2/BulkAlbumEdit::ids` returns all album IDs matching the current `search` filter (no pagination), capped at 1 000. Admin-only. | Array of album ID strings returned. Cap warning field `capped: true` included when total > 1 000. | search param optional. | 403 for non-admin. | No telemetry. | FR-034-04 |
| FR-034-13 | The page has a left-menu entry labelled "Bulk Album Edit" visible only to admin users, consistent with other admin page entries. | Entry appears in left menu for admins. | Hidden for non-admin users. | n/a | No telemetry. | Problem statement |
| FR-034-14 | Each album row in the list displays a depth indicator (indentation or tree-prefix characters: `└─`, `├─`, `│`) computed from the album's depth in the nested-set tree. | Correct visual nesting shown. Tree prefix computed client-side or server-side using `depth` field from `withDepth()`. | n/a | n/a | No telemetry. | Problem statement ("Albums should be ordered by _lft increasing") |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-034-01 | The album list query must complete in < 500 ms for galleries with up to 10 000 albums. The query joins `albums` and `base_albums` with optional `LIKE` filter, ordered by `_lft`. Indexed on `_lft` (existing Nestedset index). | Performance. | Query explain plan shows index use on `_lft`. Manual benchmark on seed data set of 10 000 albums. | Existing `_lft` index from Kalnoy Nestedset. | Implementation requirement |
| NFR-034-02 | The `PATCH /api/v2/BulkAlbumEdit` endpoint must handle up to 500 album IDs per request using chunked updates (100 per chunk) to avoid memory spikes. | Scalability. | Chunked via `BaseAlbumImpl::query()->whereIn()->chunk(100, ...)`. | Eloquent chunked update. | Implementation requirement |
| NFR-034-03 | Ownership transfer (`POST /api/v2/BulkAlbumEdit::setOwner`) must process albums sequentially (one at a time), not in bulk, because `Transfer::do()` triggers `makeRoot()` which modifies the nested-set tree. Processing in bulk would corrupt `_lft`/`_rgt` values. | Data integrity. | Each album is transferred individually inside the DB transaction. | `Transfer::do()`, `makeRoot()`, `fixOwnershipOfChildren()`. | Implementation requirement |
| NFR-034-04 | The UI must not use PrimeVue DataTable. The album list is rendered using a custom table/list component with checkboxes. | Problem statement constraint. | Code review — no `<DataTable>` import in `BulkAlbumEdit.vue`. | PrimeVue (Checkbox, Button, Select, Paginator, etc. allowed). | Problem statement |
| NFR-034-05 | All PHP code follows Lychee coding conventions: license header, snake_case variables, strict comparison (`===`), PSR-4, no `empty()`, `in_array(..., true)`. | Maintainability. | `php-cs-fixer`, PHPStan level 6. | php-cs-fixer, phpstan. | Coding conventions |
| NFR-034-06 | All TypeScript/Vue code follows Lychee frontend conventions: Composition API, regular function declarations, `.then()` not `async/await`, PrimeVue components, `<script setup lang="ts">`. | Maintainability. | `npm run check`, `npm run format`. | vue-tsc, prettier. | Coding conventions |
| NFR-034-07 | The warning banner (FR-034-05) must be visually prominent (e.g. yellow/orange `Message` severity="warn") and must not be dismissible. | UX safety. | Visual review. Banner present after page interaction. | PrimeVue `Message` component. | Problem statement |

## UI / Interaction Mock-ups

### 1. Page Layout

```
┌──────────────────────────────────────────────────────────────────────────┐
│  ☰  Bulk Album Edit                                                       │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ⚠  Any modification made on this page is final. There is no              │
│     confirmation step.                                                     │
│                                                                            │
│  ┌────────────────────────────────┐  Per page: [50 ▼]  [∞ scroll / pages]│
│  │ 🔍 Filter by name…            │                                        │
│  └────────────────────────────────┘                                        │
│                                                                            │
│  [ Delete ]  [ Set Owner… ]  [ Edit Fields… ]   (disabled when 0 selected)│
│                                                                            │
│  ┌───┬──────────────────────────────┬──────────┬───────┬────────┬────────┐│
│  │ ☑ │ Title                        │ Owner    │License│ NSFW  │Created ││
│  ├───┼──────────────────────────────┼──────────┼───────┼────────┼────────┤│
│  │ □ │ Album A                      │ admin    │ none  │ no    │2024-01 ││
│  │ □ │   └─ Sub-album A1            │ admin    │ CC-BY │ no    │2024-01 ││
│  │ ☑ │      └─ Sub-sub-album A1a    │ admin    │ none  │ yes   │2024-02 ││
│  │ □ │ Album B                      │ user1    │ none  │ no    │2024-03 ││
│  └───┴──────────────────────────────┴──────────┴───────┴────────┴────────┘│
│                                                                            │
│  [Select all on this page]  [Select all 247 matching]  2 selected          │
│                                                                            │
│  ◀ 1  2  3 … 10 ▶                                                         │
└──────────────────────────────────────────────────────────────────────────┘
```

### 2. Edit Fields Panel/Modal

```
┌─────────────────────────────────────────────────────────────┐
│  Edit Fields for 2 albums                             [×]    │
├─────────────────────────────────────────────────────────────┤
│  ⚠ Only checked fields will be updated.                      │
│                                                              │
│  [✓] Description  [________________________________]         │
│  [✓] Copyright    [________________________________]         │
│  [ ] License      [none                           ▼]         │
│  [ ] Photo layout [default                        ▼]         │
│  [ ] Photo sort   [taken_at          ▼] [asc      ▼]         │
│  [ ] Album sort   [title             ▼] [asc      ▼]         │
│  [ ] Aspect ratio [default                        ▼]         │
│  [ ] Album timeline [default                      ▼]         │
│  [ ] Photo timeline [default                      ▼]         │
│  [ ] Is NSFW      [toggle off]                               │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│  [ Cancel ]                              [ Apply ]           │
└─────────────────────────────────────────────────────────────┘
```

### 3. Set Owner Modal

```
┌─────────────────────────────────────────────────────────────┐
│  Transfer Ownership for 2 albums                      [×]    │
├─────────────────────────────────────────────────────────────┤
│  ⚠ All selected albums will be moved to the root level       │
│     and their descendants will also be transferred.          │
│                                                              │
│  New Owner: [Select user…                         ▼]         │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│  [ Cancel ]                              [ Transfer ]        │
└─────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-034-01 | Admin loads page — album list displayed in `_lft ASC` order with depth indicators |
| S-034-02 | Admin types a search string — list filtered via `LIKE %search%`, page reset to 1 |
| S-034-03 | Admin switches between infinite-scroll and numbered-pagination modes |
| S-034-04 | Admin selects albums on current page using header checkbox |
| S-034-05 | Admin clicks "Select all matching" — up to 1 000 IDs loaded; warning toast if capped |
| S-034-06 | Admin opens "Edit Fields", checks Description and Copyright, clicks Apply — only those two fields updated on selected albums |
| S-034-07 | Admin opens "Edit Fields", checks License, selects CC-BY-4.0, clicks Apply — license updated on all selected albums |
| S-034-08 | Admin opens "Set Owner", selects a user, clicks Transfer — each selected album moved to root, descendants transferred, tree recomputed |
| S-034-09 | Admin clicks "Delete" — selected albums and their descendants deleted |
| S-034-10 | Non-admin user attempts to access `/bulk-album-edit` — 403 / redirect to home |
| S-034-11 | Admin submits PATCH with 0 fields enabled — 422 validation error returned |
| S-034-12 | Admin submits PATCH with invalid enum value — 422 validation error surfaced in form |
| S-034-13 | Admin submits Set Owner with non-existent user_id — 422 validation error |
| S-034-14 | Ownership transfer on sub-album: album demoted to root, siblings' `_lft`/`_rgt` recomputed correctly |
| S-034-15 | Admin submits Delete on 500 albums — completes successfully, tree integrity maintained |

## Test Strategy

- **Application:** Unit tests for `BulkEditAlbumsAction` (field merging, chunked update logic).
- **REST:** Feature tests in `tests/Feature_v2/BulkAlbumEdit/` covering:
  - `IndexTest` — list, pagination, search filter, `_lft` ordering.
  - `IdsTest` — "select all matching" endpoint, cap at 1 000.
  - `PatchTest` — partial field update, admin-only gate, transaction rollback, 422 scenarios.
  - `SetOwnerTest` — ownership transfer, descendants cascade, tree integrity, admin-only gate.
  - `DeleteTest` — bulk delete, admin-only gate, cascade.
- **UI (JS):** Vue component tests for `BulkAlbumEdit.vue`:
  - Page-size selector, mode toggle.
  - Filter debounce clears selection.
  - "Select all on page" / "Select all matching" interactions.
  - Edit Fields panel: disabled fields excluded from payload.
  - Set Owner modal: user picker renders, submit calls service.
- **Docs/Contracts:** OpenAPI annotations on all four endpoints.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-034-01 | `BulkAlbumResource` — Spatie Data: `id`, `title`, `owner_id`, `owner_name`, `license`, `photo_layout`, `album_thumb_aspect_ratio`, `album_timeline`, `photo_timeline`, `photo_sorting`, `album_sorting`, `copyright`, `description`, `is_nsfw`, `depth`, `_lft` | application, REST |
| DO-034-02 | `BulkEditAlbumsRequest` — `album_ids[]`, plus optional nullable fields: `description`, `copyright`, `license`, `photo_layout`, `photo_sorting_col`, `photo_sorting_order`, `album_sorting_col`, `album_sorting_order`, `album_thumb_aspect_ratio`, `album_timeline`, `photo_timeline`, `is_nsfw`. At least one optional field must be present. | application |
| DO-034-03 | `SetOwnerBulkRequest` — `album_ids[]` (required), `owner_id` (required, integer) | application |
| DO-034-04 | `DeleteBulkAlbumsRequest` — `album_ids[]` (required, min:1) | application |
| DO-034-05 | `BulkAlbumIdsResource` — `ids[]` (array of strings), `capped` (bool) | application, REST |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-034-01 | GET /api/v2/BulkAlbumEdit | Paginated list of albums | Admin-only; query params: `search`, `page`, `per_page` |
| API-034-02 | GET /api/v2/BulkAlbumEdit::ids | All matching album IDs (capped 1 000) | Admin-only; query param: `search` |
| API-034-03 | PATCH /api/v2/BulkAlbumEdit | Bulk partial field update | Admin-only; body: `album_ids[]` + optional fields |
| API-034-04 | POST /api/v2/BulkAlbumEdit::setOwner | Bulk ownership transfer | Admin-only; body: `album_ids[]`, `owner_id` |
| API-034-05 | DELETE /api/v2/BulkAlbumEdit | Bulk album delete | Admin-only; body: `album_ids[]` |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-034-01 | Page loaded, no selection | Action buttons disabled; warning banner visible |
| UI-034-02 | Albums selected | Action buttons enabled; selection count shown |
| UI-034-03 | Edit Fields panel open | Per-field enable/disable toggles; only checked fields sent on Apply |
| UI-034-04 | Set Owner modal open | User dropdown populated from existing users list |
| UI-034-05 | PATCH in progress | Buttons disabled; loading spinner on Apply button |
| UI-034-06 | PATCH success | Success toast; album list refreshed; selection cleared |
| UI-034-07 | PATCH error | Error toast with server message; selection preserved |
| UI-034-08 | Infinite scroll active | Next page appended automatically when scroll sentinel visible |
| UI-034-09 | Numbered pagination active | Page buttons rendered; clicking page loads new data; selection cleared |
| UI-034-10 | "Select all matching" capped | Warning toast: "Only first 1 000 albums selected." |

## Telemetry & Observability

No new telemetry events are required for this feature. Standard Laravel exception logging applies for transaction failures. All endpoints emit standard HTTP access logs.

## Documentation Deliverables

- Update `docs/specs/4-architecture/roadmap.md` to add Feature 034 to the Active Features table.
- Update `docs/specs/4-architecture/knowledge-map.md` to reference the new `BulkAlbumController` and `BulkAlbumEdit.vue`.
- Add OpenAPI docblocks to all five new endpoints.

## Fixtures & Sample Data

- Reuse existing `BaseApiWithDataTest` fixtures (albums, users).
- Add a seeder or factory helper that creates a 3-level nested album tree for tree-integrity tests.

## Open Questions (logged separately)

See [open-questions.md](../../open-questions.md) for Q-034-01 through Q-034-04.

## Spec DSL

```yaml
domain_objects:
  - id: DO-034-01
    name: BulkAlbumResource
    fields:
      - name: id
        type: string
      - name: title
        type: string
      - name: owner_id
        type: integer
      - name: owner_name
        type: string
      - name: license
        type: LicenseType|null
      - name: photo_layout
        type: PhotoLayoutType|null
      - name: album_thumb_aspect_ratio
        type: AspectRatioType|null
      - name: album_timeline
        type: TimelineAlbumGranularity|null
      - name: photo_timeline
        type: TimelinePhotoGranularity|null
      - name: photo_sorting
        type: PhotoSortingCriterion|null
      - name: album_sorting
        type: AlbumSortingCriterion|null
      - name: copyright
        type: string|null
      - name: description
        type: string|null
      - name: is_nsfw
        type: boolean
      - name: depth
        type: integer
      - name: _lft
        type: integer
  - id: DO-034-02
    name: BulkEditAlbumsRequest
    fields:
      - name: album_ids
        type: array<string>
        constraints: "required, min:1, valid random IDs for existing albums"
      - name: description
        type: string|null
        constraints: "optional, nullable, max:1024"
      - name: copyright
        type: string|null
        constraints: "optional, nullable"
      - name: license
        type: LicenseType
        constraints: "optional, valid enum"
      - name: photo_layout
        type: PhotoLayoutType
        constraints: "optional, valid enum"
      - name: photo_sorting_col
        type: ColumnSortingPhotoType
        constraints: "optional, nullable, valid enum"
      - name: photo_sorting_order
        type: OrderSortingType
        constraints: "required_with:photo_sorting_col, nullable, valid enum"
      - name: album_sorting_col
        type: ColumnSortingAlbumType
        constraints: "optional, nullable, valid enum"
      - name: album_sorting_order
        type: OrderSortingType
        constraints: "required_with:album_sorting_col, nullable, valid enum"
      - name: album_thumb_aspect_ratio
        type: AspectRatioType
        constraints: "optional, nullable, valid enum"
      - name: album_timeline
        type: TimelineAlbumGranularity
        constraints: "optional, nullable, valid enum"
      - name: photo_timeline
        type: TimelinePhotoGranularity
        constraints: "optional, nullable, valid enum"
      - name: is_nsfw
        type: boolean
        constraints: "optional"
  - id: DO-034-03
    name: SetOwnerBulkRequest
    fields:
      - name: album_ids
        type: array<string>
        constraints: "required, min:1"
      - name: owner_id
        type: integer
        constraints: "required, must be existing user ID"
  - id: DO-034-04
    name: DeleteBulkAlbumsRequest
    fields:
      - name: album_ids
        type: array<string>
        constraints: "required, min:1"
  - id: DO-034-05
    name: BulkAlbumIdsResource
    fields:
      - name: ids
        type: array<string>
      - name: capped
        type: boolean

routes:
  - id: API-034-01
    method: GET
    path: /api/v2/BulkAlbumEdit
    auth: admin
    query_params:
      - search: string|null
      - page: integer (≥1, default 1)
      - per_page: integer (25|50|100, default 50)
    responses:
      - 200: Paginated BulkAlbumResource list
      - 403: Non-admin
  - id: API-034-02
    method: GET
    path: /api/v2/BulkAlbumEdit::ids
    auth: admin
    query_params:
      - search: string|null
    responses:
      - 200: BulkAlbumIdsResource
      - 403: Non-admin
  - id: API-034-03
    method: PATCH
    path: /api/v2/BulkAlbumEdit
    auth: admin
    request_body:
      - album_ids: array<string>
      - (optional fields per DO-034-02)
    responses:
      - 204: Success
      - 403: Non-admin
      - 422: Validation error
  - id: API-034-04
    method: POST
    path: /api/v2/BulkAlbumEdit::setOwner
    auth: admin
    request_body:
      - album_ids: array<string>
      - owner_id: integer
    responses:
      - 204: Success
      - 403: Non-admin
      - 422: Validation error
  - id: API-034-05
    method: DELETE
    path: /api/v2/BulkAlbumEdit
    auth: admin
    request_body:
      - album_ids: array<string>
    responses:
      - 204: Success
      - 403: Non-admin
      - 422: Validation error

ui_states:
  - id: UI-034-01
    description: Page loaded, no selection
  - id: UI-034-02
    description: Albums selected (action buttons enabled)
  - id: UI-034-03
    description: Edit Fields panel open
  - id: UI-034-04
    description: Set Owner modal open
  - id: UI-034-05
    description: PATCH/DELETE/Transfer in progress
  - id: UI-034-06
    description: Operation success
  - id: UI-034-07
    description: Operation error
  - id: UI-034-08
    description: Infinite scroll appending
  - id: UI-034-09
    description: Numbered pagination navigation
  - id: UI-034-10
    description: Select-all-matching capped at 1 000
```

## Appendix

### Why no DataTable?

The problem statement explicitly forbids the PrimeVue `DataTable` component. The Kalnoy nested-set depth indicator and `_lft` ordering require custom row rendering that benefits from explicit control over row structure. A custom table (plain `<table>` or `<div>`-based layout with PrimeVue `Checkbox`, `Button`, `Paginator`) is the correct approach.

### Ownership Transfer — Tree Safety

The existing `Transfer::do()` action calls `Album::makeRoot()` which uses the Kalnoy Nestedset library to safely move the node to root and fix all `_lft`/`_rgt` values of affected siblings/ancestors. Processing multiple ownership transfers in a single SQL `UPDATE` on `albums.owner_id` would skip `makeRoot()` and produce corrupt tree state. Therefore each album is processed individually (FR-034-09 / NFR-034-03).

### Editable Fields Rationale

The problem statement asks for: "Photo/Album Order, Photo/Album Thumbs Aspect Ratio, Photo/Album Timeline Mode, Licence and Copyright" — plus "most of the metadata of base_albums (including ownership)". The following fields from `base_albums` and `albums` are therefore in scope:

| Field | Table |
|-------|-------|
| `description` | base_albums |
| `copyright` | base_albums |
| `photo_layout` | base_albums |
| `sorting_col` / `sorting_order` | base_albums |
| `is_nsfw` | base_albums |
| `owner_id` | base_albums (via Transfer action) |
| `license` | albums |
| `album_thumb_aspect_ratio` | albums |
| `album_timeline` | albums |
| `album_sorting_col` / `album_sorting_order` | albums |
| `photo_timeline` | base_albums (via photo_timeline column) |

`title` and `slug` are intentionally excluded from the batch form to prevent accidental mass-rename of albums; they remain editable only via the individual album editor. `is_pinned` is also excluded from bulk edit (unlikely to need bulk update; individual toggle is sufficient).

---

*Last updated: 2026-04-12*
