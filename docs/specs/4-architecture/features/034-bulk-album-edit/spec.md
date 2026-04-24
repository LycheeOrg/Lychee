# Feature 034 – Bulk Album Edit

| Field | Value |
|-------|-------|
| Status | Draft – Resolutions incorporated |
| Last updated | 2026-04-14 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/034-bulk-album-edit/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/034-bulk-album-edit/tasks.md` |
| Roadmap entry | #034 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Administrators currently have no way to update common metadata (license, visibility, layout, timeline, sorting, copyright, ownership) across many albums at once. This feature adds a dedicated admin-only **Bulk Album Edit** page where albums are listed in nested-set (`_lft`) order, can be filtered by name via a `LIKE` search, selected individually or all-at-once, and updated in a single batch operation. Ownership transfer also cascades to all descendants and demotes a transferred sub-album to a root level (as the existing `Transfer` action does). The page uses infinite scrolling **or** numbered pagination (configurable via the UI); no DataTable component is used. A persistent warning banner informs the admin that changes are final with no confirmation step (except for the destructive delete operation which shows a minimal confirmation dialog).

Editing is supported in two complementary modes:

1. **Inline editing** — clicking a cell in the album row enters edit mode for that single field; saving triggers a single-album PATCH immediately.
2. **Bulk modal** — selecting multiple albums and clicking "Edit Fields" opens a modal where only explicitly-checked fields are applied to all selected albums in one request.

Public visibility properties (`is_public`, `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload`) are editable both inline (as toggles in each row) and via the bulk modal.

**Affected modules:** `core` (Album/BaseAlbumImpl/AccessPermission models, `Transfer`/`SetProtectionPolicy` actions), `application` (new `BulkEditAlbumsAction`, new Request classes), `REST` (new `Admin\BulkAlbumController`), `UI` (new `BulkAlbumEdit.vue` page, route `/bulk-album-edit`, left-menu entry).

## Goals

1. Admin can list all `Album` records (TagAlbums excluded — Q-034-01 → Option A) ordered by `_lft ASC` with optional name filter (case-insensitive `LIKE`).
2. Admin can select individual albums or all visible albums (across all pages / loaded rows).
3. Admin can set any combination of the following fields for selected albums via the **bulk modal**:
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
   - `is_public` (boolean — creates/deletes public `AccessPermission`)
   - `is_link_required` (boolean — updates public `AccessPermission`)
   - `grants_full_photo_access` (boolean — updates public `AccessPermission`)
   - `grants_download` (boolean — updates public `AccessPermission`)
   - `grants_upload` (boolean, SE-only — updates public `AccessPermission`)
4. Admin can **inline-edit** individual cell values directly in the table row. Changing a value triggers an immediate single-album PATCH (same endpoint, single ID). No explicit save step beyond leaving the field.
5. Ownership transfer (`owner_id`) is a dedicated action: selects albums, opens Set Owner modal, cascades to all descendant albums and photos, and demotes the album to root (reusing `Transfer::do()` logic).
6. The page supports both **infinite scroll** and **numbered pagination** with a per-page selector (25 / 50 / 100).
7. A warning banner at the top of the page informs the admin that changes are applied immediately without any confirmation dialog (except delete).
8. Bulk action buttons (Delete, Set Owner, Edit Fields…) appear above the album list, not in column headers.
9. Admin can delete selected albums via the page; a minimal confirmation dialog (click "Confirm Delete") is shown before deletion — per Q-034-03 → Option B.
10. Depth indicator for each album row is computed client-side in a single O(n) linear pass over the `_lft`/`_rgt`-sorted result — per Q-034-02 → Option B.

## Non-Goals

- Bulk editing of `TagAlbum` metadata (tag albums have a separate schema — deferred; per Q-034-01 → Option A TagAlbums are not listed).
- Bulk editing of per-user or per-group access permissions (only the public permission slot is edited — the same slot managed by the existing Sharing page for single albums).
- Confirmation dialogs for field edits or ownership transfer (warning banner is sufficient; delete retains a minimal confirmation per Q-034-03 → Option B).
- Editing album cover or header photo from this page.
- `title` and `slug` bulk editing (mass-rename risk; individual editor only).
- Pagination configuration persistence across sessions (page size resets on navigate).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-034-01 | A new admin-only page `/bulk-album-edit` lists all regular `Album` records (only `albums` table; TagAlbums excluded — Q-034-01 → A) joined with `base_albums`, ordered by `_lft ASC`. Each row shows: depth indicator, title, owner username, license, is_nsfw flag, is_public indicator, is_link_required indicator, grants_full_photo_access, grants_download, grants_upload, created_at date. | Page renders list of albums in nested-set order. | Admin-only gate (403 for non-admin). Unauthenticated users receive 401/redirect. | Empty state shown when no albums exist. | No telemetry. | Problem statement |
| FR-034-02 | The list supports a name filter: a text input sends a `LIKE %search%` query (case-insensitive) to the backend. Filtering resets the current page to 1 and clears the selection. | Matching albums returned in `_lft ASC` order. | Empty string / null means no filter. Filter does not break pagination. | No matches → empty state with "No albums match your search." | No telemetry. | Problem statement |
| FR-034-03 | The list supports both infinite scrolling and numbered pagination. A page-size selector (options: 25, 50, 100; default 50) controls results per page. Pagination mode (infinite vs numbered) is toggled by a control on the page. | Correct page/cursor-based results returned. Infinite scroll appends next page when sentinel is visible. Numbered pagination shows page number buttons. | Page and per_page parameters validated (page ≥ 1, per_page ∈ {25, 50, 100}). | Server-side pagination errors surface as toast notifications. | No telemetry. | Problem statement |
| FR-034-04 | Each album row has a checkbox. A "select all on current page" checkbox exists in the header row. A separate "select all matching" button selects all album IDs matching the current filter (regardless of current page). The scope covers all albums in the gallery regardless of owner (Q-034-04 → A). | Selected IDs are tracked in frontend state. Select-all-matching fetches all matching IDs from a dedicated endpoint `GET /api/v2/BulkAlbumEdit::ids`. | "Select all matching" capped at 1 000 IDs server-side for safety; a warning toast is shown if cap is reached. | Stale selection cleared when filter changes. | No telemetry. | Problem statement |
| FR-034-05 | A warning banner is displayed permanently at the top of the page: "⚠ Any modification made on this page is final. There is no confirmation step." | Banner visible on page load and remains throughout session. | Cannot be dismissed. | n/a | No telemetry. | Problem statement |
| FR-034-06 | Bulk action buttons are placed above the album list (not in table column headers): **Delete**, **Set Owner**, **Edit Fields**. Buttons are disabled when no albums are selected. | Buttons enabled when selection is non-empty. | n/a | n/a | No telemetry. | Problem statement |
| FR-034-07 | **Edit Fields** (modal) opens when one or more albums are selected. Every field is optional; only fields whose checkbox/toggle is explicitly enabled by the admin are included in the PATCH request. Fields available in the modal: description, copyright, license, photo_layout, photo_sorting (col+order), album_sorting (col+order), album_thumb_aspect_ratio, album_timeline, photo_timeline, is_nsfw, is_public, is_link_required, grants_full_photo_access, grants_download, grants_upload (SE-only — hidden if not SE). | Only enabled fields are sent in request body. Disabled fields are omitted. | Each included field validated against its existing enum/type rules. grants_upload only accepted when SE verified. | 422 returned with per-field errors surfaced in the form. | No telemetry. | Problem statement |
| FR-034-08 | A new REST endpoint `PATCH /api/v2/BulkAlbumEdit` accepts an array of album IDs (`album_ids`, min:1) and a partial set of fields. Admin-only. Fields present in the request are applied to all specified albums; absent fields are left unchanged. Metadata fields (`base_albums`/`albums` columns) use chunked SQL updates; visibility fields (`is_public`, `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload`) are applied per-album via `SetProtectionPolicy::do()` within the same transaction. All operations wrapped in a single DB transaction. Supports both bulk (many IDs) and inline editing (single ID) from the same endpoint. | All specified albums updated. 204 No Content returned. | album_ids: required, array, min:1. Each ID must be a valid random ID for an existing `albums` record. At least one optional field required. Each present field validated per its own rules. | 422 for validation failure. 403 for non-admin. Transaction rolls back on any error. | No telemetry. | Problem statement |
| FR-034-09 | A new REST endpoint `POST /api/v2/BulkAlbumEdit::setOwner` accepts `album_ids` array and `owner_id` (integer). Admin-only. For each album, calls the existing `Transfer::do()` logic: updates `owner_id` on `base_albums`, removes the previous permission grant for the new owner, calls `makeRoot()` on the `albums` record and `fixOwnershipOfChildren()` to cascade to descendants. Albums are processed sequentially to preserve tree integrity (NFR-034-03). | Ownership transferred and descendants updated. Tree integrity maintained. 204 returned. | album_ids: required, array, min:1. owner_id: required, must be an existing user ID. | 422 for validation failure. 403 for non-admin. Transaction rolls back on any error. | No telemetry. | Problem statement |
| FR-034-10 | A new REST endpoint `DELETE /api/v2/BulkAlbumEdit` accepts `album_ids` array. Admin-only. Delegates to the existing `Delete::do()` action. On the frontend, clicking the Delete button shows a minimal confirmation dialog ("You are about to delete N albums. This action cannot be undone. [Confirm Delete] [Cancel]") before the request is sent — per Q-034-03 → Option B. | Selected albums (and their descendants) deleted. 204 returned. | album_ids: required, array, min:1. Each must be an existing albums ID. | 422 for validation failure. 403 for non-admin. | No telemetry. | Q-034-03 resolution |
| FR-034-11 | A new REST endpoint `GET /api/v2/BulkAlbumEdit` returns a paginated list of albums ordered by `_lft ASC`. Supports query parameters: `search` (string, optional), `page` (int ≥ 1), `per_page` (int ∈ {25, 50, 100}). Response includes: `data[]` (array of `BulkAlbumResource`), `meta.current_page`, `meta.last_page`, `meta.total`. Admin-only. | Correct paginated list returned. | Validated query params. | 422 for invalid params. 403 for non-admin. | No telemetry. | Problem statement |
| FR-034-12 | A new REST endpoint `GET /api/v2/BulkAlbumEdit::ids` returns all album IDs matching the current `search` filter (no pagination), capped at 1 000. Returns IDs for all albums in the gallery regardless of owner (Q-034-04 → A). Admin-only. | Array of album ID strings returned. Cap warning field `capped: true` included when total > 1 000. | search param optional. | 403 for non-admin. | No telemetry. | FR-034-04, Q-034-04 |
| FR-034-13 | The page has a left-menu entry labelled "Bulk Album Edit" visible only to admin users, consistent with other admin page entries. | Entry appears in left menu for admins. | Hidden for non-admin users. | n/a | No telemetry. | Problem statement |
| FR-034-14 | Each album row in the list displays a depth indicator (indentation + tree-prefix characters: `└─`, `├─`, `│`) computed **client-side** in a single O(n) linear pass over the `_lft`/`_rgt`-sorted result set — per Q-034-02 → Option B. Algorithm: maintain a stack of ancestor `_rgt` values; pop the stack while current row's `_lft` > stack-top's `_rgt`; depth = stack length before push. The server includes `_lft` and `_rgt` in each `BulkAlbumResource` row (no server-side `withDepth()` call needed). | Correct visual nesting shown for all rows in one pass. | n/a | n/a | No telemetry. | Q-034-02 resolution |
| FR-034-15 | Album visibility/protection properties (`is_public`, `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload`) are displayed as icon-toggles or chips in the album list row and are editable both inline (row toggle → immediate PATCH) and via the bulk modal (FR-034-07). `grants_upload` is hidden when SE is not active. When `is_public` is set to false, all other public-permission fields are effectively hidden/disabled in the UI (public permissions record is deleted). | Inline toggle changes immediately reflected; PATCH sent with single album ID + changed field. Bulk modal update applies to all selected albums. | `grants_upload: true` rejected with 422 when not SE. | 422 surfaced as toast for inline edits. | No telemetry. | Problem statement ("visible, require link, downloadable, guest upload, can access originals") |
| FR-034-16 | Each metadata cell in the album row is directly editable inline: clicking a cell (title, description, copyright, license, photo_layout, photo_sorting, album_sorting, album_thumb_aspect_ratio, album_timeline, photo_timeline, is_nsfw) activates an inline editor for that cell. Confirming the change (Enter / blur / select) immediately sends a PATCH to `PATCH /api/v2/BulkAlbumEdit` with the single album ID and the changed field. | Edited value persisted; row refreshed from response or optimistic update. | Same validation as bulk PATCH. | 422 error shown as inline error state on the cell; original value restored. | No telemetry. | Problem statement ("directly edit in the table") |

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
┌────────────────────────────────────────────────────────────────────────────────────────────────┐
│  ☰  Bulk Album Edit                                                                             │
├────────────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                                  │
│  ⚠  Any modification made on this page is final. There is no confirmation step.                 │
│                                                                                                  │
│  ┌────────────────────────────┐  Per page: [50 ▼]  [∞ scroll / pages]                          │
│  │ 🔍 Filter by name…        │                                                                  │
│  └────────────────────────────┘                                                                  │
│                                                                                                  │
│  [ Delete ]  [ Set Owner… ]  [ Edit Fields… ]     (disabled when 0 selected)                    │
│                                                                                                  │
│  ┌───┬──────────────────────────────┬──────────┬───────┬─────┬──────┬──────┬──────┬──────┬──────┐│
│  │ ☑ │ Title                        │ Owner    │License│NSFW │Pub. │Link? │Dwnl. │Orig. │Upld. ││
│  ├───┼──────────────────────────────┼──────────┼───────┼─────┼──────┼──────┼──────┼──────┼──────┤│
│  │ □ │ Album A          [click→edit]│ admin    │ none▼ │ ○  │  ●  │  ○  │  ●  │  ●  │  ○  ││
│  │ □ │   └─ Sub-album A1            │ admin    │ CC-BY▼│ ○  │  ○  │  ─  │  ─  │  ─  │  ─  ││
│  │ ☑ │      └─ Sub-sub-album A1a    │ admin    │ none▼ │ ●  │  ●  │  ○  │  ○  │  ○  │  ○  ││
│  │ □ │ Album B                      │ user1    │ none▼ │ ○  │  ○  │  ─  │  ─  │  ─  │  ─  ││
│  └───┴──────────────────────────────┴──────────┴───────┴─────┴──────┴──────┴──────┴──────┴──────┘│
│                                                                                                  │
│  [Select all on this page]  [Select all 247 matching]  2 selected                               │
│                                                                                                  │
│  ◀ 1  2  3 … 10 ▶                                                                               │
└────────────────────────────────────────────────────────────────────────────────────────────────┘
```

**Legend:** Pub. = is_public, Link? = is_link_required, Dwnl. = grants_download, Orig. = grants_full_photo_access, Upld. = grants_upload (hidden if not SE). ● = true, ○ = false, ─ = disabled (album not public). Toggles are clickable inline (FR-034-15). License/other metadata cells are click-to-edit dropdowns/inputs (FR-034-16).

### 2. Edit Fields Modal (Bulk)

```
┌─────────────────────────────────────────────────────────────────────┐
│  Edit Fields for 2 albums                                     [×]    │
├─────────────────────────────────────────────────────────────────────┤
│  ⚠ Only checked fields will be updated.                              │
│                                                                      │
│  ─── Metadata ──────────────────────────────────────────────────    │
│  [✓] Description  [________________________________]                 │
│  [✓] Copyright    [________________________________]                 │
│  [ ] License      [none                           ▼]                 │
│  [ ] Photo layout [default                        ▼]                 │
│  [ ] Photo sort   [taken_at          ▼] [asc      ▼]                 │
│  [ ] Album sort   [title             ▼] [asc      ▼]                 │
│  [ ] Aspect ratio [default                        ▼]                 │
│  [ ] Album timeline [default                      ▼]                 │
│  [ ] Photo timeline [default                      ▼]                 │
│  [ ] Is NSFW      [toggle off]                                       │
│                                                                      │
│  ─── Visibility ────────────────────────────────────────────────    │
│  [ ] Public            [toggle off]                                  │
│  [ ] Require link      [toggle off]  (only if Public)                │
│  [ ] Allow download    [toggle off]  (only if Public)                │
│  [ ] Allow originals   [toggle off]  (only if Public)                │
│  [ ] Guest upload ⭐    [toggle off]  (only if Public, SE required)   │
│                                                                      │
├─────────────────────────────────────────────────────────────────────┤
│  [ Cancel ]                                        [ Apply ]         │
└─────────────────────────────────────────────────────────────────────┘
```

### 3. Set Owner Modal

```
┌─────────────────────────────────────────────────────────────────────┐
│  Transfer Ownership for 2 albums                              [×]    │
├─────────────────────────────────────────────────────────────────────┤
│  ⚠ All selected albums will be moved to the root level               │
│     and their descendants will also be transferred.                  │
│                                                                      │
│  New Owner: [Select user…                             ▼]             │
│                                                                      │
├─────────────────────────────────────────────────────────────────────┤
│  [ Cancel ]                                    [ Transfer ]          │
└─────────────────────────────────────────────────────────────────────┘
```

### 4. Delete Confirmation Dialog

```
┌─────────────────────────────────────────────────────────────────────┐
│  Delete Albums                                                [×]    │
├─────────────────────────────────────────────────────────────────────┤
│  You are about to permanently delete 2 albums and all their          │
│  sub-albums and photos. This action cannot be undone.                │
│                                                                      │
├─────────────────────────────────────────────────────────────────────┤
│  [ Cancel ]                                  [ Confirm Delete ]      │
└─────────────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-034-01 | Admin loads page — album list displayed in `_lft ASC` order with depth indicators (client-side O(n) computation) |
| S-034-02 | Admin types a search string — list filtered via `LIKE %search%`, page reset to 1, selection cleared |
| S-034-03 | Admin switches between infinite-scroll and numbered-pagination modes |
| S-034-04 | Admin selects albums on current page using header checkbox |
| S-034-05 | Admin clicks "Select all matching" — up to 1 000 IDs loaded from all albums regardless of owner; warning toast if capped |
| S-034-06 | Admin opens "Edit Fields", checks Description and Copyright, clicks Apply — only those two fields updated on selected albums |
| S-034-07 | Admin opens "Edit Fields", checks License, selects CC-BY-4.0, clicks Apply — license updated on all selected albums |
| S-034-08 | Admin opens "Set Owner", selects a user, clicks Transfer — each selected album moved to root, descendants transferred, tree recomputed |
| S-034-09 | Admin clicks "Delete", sees confirmation dialog, clicks "Confirm Delete" — selected albums and their descendants deleted |
| S-034-10 | Non-admin user attempts to access `/bulk-album-edit` — 403 / redirect to home |
| S-034-11 | Admin submits PATCH with 0 fields enabled — 422 validation error returned |
| S-034-12 | Admin submits PATCH with invalid enum value — 422 validation error surfaced in form |
| S-034-13 | Admin submits Set Owner with non-existent user_id — 422 validation error |
| S-034-14 | Ownership transfer on sub-album: album demoted to root, siblings' `_lft`/`_rgt` recomputed correctly |
| S-034-15 | Admin submits Delete on 500 albums — completes successfully, tree integrity maintained |
| S-034-16 | Admin clicks on a License cell in the table — inline dropdown opens; admin selects "CC-BY-4.0"; dropdown closes; PATCH sent immediately; row updates |
| S-034-17 | Admin toggles `is_public` for an album row to ON — PATCH sent; row shows public flags enabled; `is_link_required`/`grants_*` toggles become active |
| S-034-18 | Admin toggles `is_public` for an album row to OFF — PATCH sent; album's `access_permissions` record deleted; `is_link_required`/`grants_*` toggles disabled/greyed |
| S-034-19 | Admin opens "Edit Fields" modal, checks "Public" toggle ON, checks "Guest upload" — if not SE: validation error; if SE: update applies |
| S-034-20 | Admin clicks "Delete", then "Cancel" in confirmation dialog — nothing deleted, dialog closed |

## Test Strategy

- **Application:** Unit tests for `BulkEditAlbumsAction` (field merging, chunked update logic, visibility field delegation to `SetProtectionPolicy`).
- **REST:** Feature tests in `tests/Feature_v2/BulkAlbumEdit/` covering:
  - `IndexTest` — list, pagination, search filter, `_lft` ordering, `_lft`/`_rgt` fields present in response.
  - `IdsTest` — "select all matching" endpoint, cap at 1 000, all albums returned regardless of owner.
  - `PatchTest` — partial field update, admin-only gate, transaction rollback, 422 scenarios, visibility field updates (is_public, is_link_required, grants_*), SE gate on grants_upload.
  - `SetOwnerTest` — ownership transfer, descendants cascade, tree integrity, admin-only gate.
  - `DeleteTest` — bulk delete, admin-only gate, cascade.
- **UI (JS):** Vue component tests for `BulkAlbumEdit.vue`:
  - Page-size selector, mode toggle.
  - Filter debounce clears selection.
  - "Select all on page" / "Select all matching" interactions.
  - Depth indicator computed correctly from `_lft`/`_rgt` (client-side O(n) algorithm unit test).
  - Edit Fields modal: disabled fields excluded from payload; visibility section renders correctly.
  - Inline editing: cell click enters edit mode, confirm triggers PATCH, error restores value.
  - Inline visibility toggle sends PATCH, updates row state, disables dependent toggles when is_public=false.
  - Set Owner modal: user picker renders, submit calls service.
  - Delete confirmation dialog: Cancel → no request sent; Confirm → DELETE sent.
- **Docs/Contracts:** OpenAPI annotations on all five endpoints.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-034-01 | `BulkAlbumResource` — Spatie Data: `id`, `title`, `owner_id`, `owner_name`, `license`, `photo_layout`, `album_thumb_aspect_ratio`, `album_timeline`, `photo_timeline`, `photo_sorting`, `album_sorting`, `copyright`, `description`, `is_nsfw`, `_lft`, `_rgt`, `is_public`, `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload` (no `depth` — computed client-side per Q-034-02) | application, REST |
| DO-034-02 | `BulkEditAlbumsRequest` — `album_ids[]`, plus optional nullable fields: `description`, `copyright`, `license`, `photo_layout`, `photo_sorting_col`, `photo_sorting_order`, `album_sorting_col`, `album_sorting_order`, `album_thumb_aspect_ratio`, `album_timeline`, `photo_timeline`, `is_nsfw`, `is_public`, `is_link_required`, `grants_full_photo_access`, `grants_download`, `grants_upload`. At least one optional field must be present. | application |
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
| UI-034-03 | Edit Fields modal open | Per-field enable/disable toggles; visibility section shown; only checked fields sent on Apply |
| UI-034-04 | Set Owner modal open | User dropdown populated from existing users list |
| UI-034-05 | Delete confirmation dialog open | Count shown; Cancel closes without action; Confirm Delete sends DELETE request |
| UI-034-06 | PATCH/DELETE/Transfer in progress | Buttons disabled; loading spinner on Apply/Transfer/Confirm button |
| UI-034-07 | Operation success | Success toast; album list refreshed; selection cleared |
| UI-034-08 | Operation error | Error toast with server message; selection preserved |
| UI-034-09 | Infinite scroll active | Next page appended automatically when scroll sentinel visible |
| UI-034-10 | Numbered pagination active | Page buttons rendered; clicking page loads new data; selection cleared |
| UI-034-11 | "Select all matching" capped | Warning toast: "Only first 1 000 albums selected." |
| UI-034-12 | Inline cell editing active | Cell transitions to editable state (dropdown/input); Escape restores original; Enter/blur confirms |
| UI-034-13 | Inline edit confirmed | PATCH sent; cell shows new value; loading micro-state on cell |
| UI-034-14 | Inline edit error | 422 error; cell shows error indicator; original value restored |
| UI-034-15 | is_public toggled OFF | Row's Link?/Dwnl./Orig./Upld. cells greyed out/disabled |
| UI-034-16 | is_public toggled ON | Row's Link?/Dwnl./Orig./Upld. cells become active toggles |

## Telemetry & Observability

No new telemetry events are required for this feature. Standard Laravel exception logging applies for transaction failures. All endpoints emit standard HTTP access logs.

## Documentation Deliverables

- Update `docs/specs/4-architecture/roadmap.md` to add Feature 034 to the Active Features table.
- Update `docs/specs/4-architecture/knowledge-map.md` to reference the new `BulkAlbumController` and `BulkAlbumEdit.vue`.
- Add OpenAPI docblocks to all five new endpoints.

## Fixtures & Sample Data

- Reuse existing `BaseApiWithDataTest` fixtures (albums, users).
- Add a seeder or factory helper that creates a 3-level nested album tree for tree-integrity tests.

## Clarification Resolutions

All four open questions have been resolved (2026-04-14):

| Q-ID | Resolution |
|------|-----------|
| Q-034-01 | **Option A** — Only `Album` records listed; TagAlbums excluded. |
| Q-034-02 | **Option B** — Depth computed client-side in O(n) linear pass using `_lft`/`_rgt` stack algorithm. Server includes `_lft` and `_rgt` in each resource row; no `withDepth()` needed. |
| Q-034-03 | **Option B** — Delete shows a minimal confirmation dialog (count + "Confirm Delete" button). All other operations remain no-confirmation. |
| Q-034-04 | **Option A** — `GET ::ids` returns all albums regardless of owner. |

See [open-questions.md](../../open-questions.md) for full resolution history.

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
      - name: _lft
        type: integer
      - name: _rgt
        type: integer
      # depth is NOT included — computed client-side per Q-034-02 → B
      - name: is_public
        type: boolean
      - name: is_link_required
        type: boolean
      - name: grants_full_photo_access
        type: boolean
      - name: grants_download
        type: boolean
      - name: grants_upload
        type: boolean
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
      - name: is_public
        type: boolean
        constraints: "optional"
      - name: is_link_required
        type: boolean
        constraints: "optional"
      - name: grants_full_photo_access
        type: boolean
        constraints: "optional"
      - name: grants_download
        type: boolean
        constraints: "optional"
      - name: grants_upload
        type: boolean
        constraints: "optional, requires SE (BooleanRequireSupportRule)"
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
      - (optional fields per DO-034-02 — metadata + visibility)
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
    description: Edit Fields modal open (metadata + visibility sections)
  - id: UI-034-04
    description: Set Owner modal open
  - id: UI-034-05
    description: Delete confirmation dialog open
  - id: UI-034-06
    description: Operation in progress
  - id: UI-034-07
    description: Operation success
  - id: UI-034-08
    description: Operation error
  - id: UI-034-09
    description: Infinite scroll appending
  - id: UI-034-10
    description: Numbered pagination navigation
  - id: UI-034-11
    description: Select-all-matching capped at 1 000
  - id: UI-034-12
    description: Inline cell editing active
  - id: UI-034-13
    description: Inline edit confirmed (PATCH in flight)
  - id: UI-034-14
    description: Inline edit error (cell reverted)
  - id: UI-034-15
    description: is_public=false — dependent toggles disabled
  - id: UI-034-16
    description: is_public=true — dependent toggles active
```

## Appendix

### Why no DataTable?

The problem statement explicitly forbids the PrimeVue `DataTable` component. The Kalnoy nested-set depth indicator and `_lft` ordering require custom row rendering that benefits from explicit control over row structure. A custom table (plain `<table>` or `<div>`-based layout with PrimeVue `Checkbox`, `Button`, `Paginator`) is the correct approach.

### Ownership Transfer — Tree Safety

The existing `Transfer::do()` action calls `Album::makeRoot()` which uses the Kalnoy Nestedset library to safely move the node to root and fix all `_lft`/`_rgt` values of affected siblings/ancestors. Processing multiple ownership transfers in a single SQL `UPDATE` on `albums.owner_id` would skip `makeRoot()` and produce corrupt tree state. Therefore each album is processed individually (FR-034-09 / NFR-034-03).

### Editable Fields Rationale

The problem statement asks for: "Photo/Album Order, Photo/Album Thumbs Aspect Ratio, Photo/Album Timeline Mode, Licence and Copyright, visible, require link, downloadable, guest upload, can access originals" — plus "most of the metadata of base_albums (including ownership)". The following fields are therefore in scope:

| Field | Table / Mechanism | Editable inline? | Editable via modal? |
|-------|--------------------|------------------|---------------------|
| `description` | base_albums | ✓ | ✓ |
| `copyright` | base_albums | ✓ | ✓ |
| `photo_layout` | base_albums | ✓ (dropdown) | ✓ |
| `sorting_col` / `sorting_order` | base_albums | ✓ | ✓ |
| `is_nsfw` | base_albums | ✓ (toggle) | ✓ |
| `owner_id` | base_albums (via Transfer action) | ✗ (use Set Owner button) | ✗ |
| `license` | albums | ✓ (dropdown) | ✓ |
| `album_thumb_aspect_ratio` | albums | ✓ (dropdown) | ✓ |
| `album_timeline` | albums | ✓ (dropdown) | ✓ |
| `album_sorting_col` / `album_sorting_order` | albums | ✓ | ✓ |
| `photo_timeline` | base_albums | ✓ (dropdown) | ✓ |
| `is_public` | access_permissions (SetProtectionPolicy) | ✓ (toggle) | ✓ |
| `is_link_required` | access_permissions (SetProtectionPolicy) | ✓ (toggle, only if public) | ✓ |
| `grants_full_photo_access` | access_permissions (SetProtectionPolicy) | ✓ (toggle, only if public) | ✓ |
| `grants_download` | access_permissions (SetProtectionPolicy) | ✓ (toggle, only if public) | ✓ |
| `grants_upload` | access_permissions (SetProtectionPolicy, SE-only) | ✓ (toggle, SE + public) | ✓ (hidden if not SE) |

`title` and `slug` are intentionally excluded from bulk edit (mass-rename risk; individual editor only). `is_pinned` is also excluded.

### Depth Computation Algorithm (Q-034-02 → Option B)

Given an array of album rows sorted by `_lft ASC`, compute depth in a single O(n) pass:

```
stack = []       // stack of {_rgt} values
for each row in sorted rows:
  while stack is not empty and row._lft > stack.top._rgt:
    stack.pop()
  depth = stack.length
  row.computedDepth = depth
  stack.push({ _rgt: row._rgt })
```

This algorithm is O(n) in time and O(tree depth) in space.

---

*Last updated: 2026-04-14*
