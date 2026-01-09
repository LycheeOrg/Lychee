# Feature 007 – Photos and Albums Pagination

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-01-07 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/007-pagination/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/007-pagination/tasks.md` |
| Roadmap entry | #007 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Lychee currently loads all sub-albums and all photos for a given album in a single API request. For albums containing hundreds or thousands of photos, this creates performance problems (slow database queries, large API payloads, memory pressure on both backend and frontend). This feature implements pagination for both album children and photos, allowing data to be loaded incrementally in configurable page sizes.

Affected modules: application (controllers/services), REST API (v2 endpoints), database (query optimization), UI (Vue components, Pinia stores).

## Goals

- Create new REST API endpoints for paginated data retrieval (separate from existing `/Album` endpoint)
- Implement `/Album/{id}/albums` endpoint for paginated sub-albums
- Implement `/Album/{id}/photos` endpoint for paginated photos
- Implement `/Album/{id}/head` endpoint for album metadata without children/photos
- Provide configurable page size settings via config table (separate for albums and photos)
- Provide configurable UI loading strategy per resource type (infinite scroll, load more button, page navigation)
- Always load first page on initial album load, subsequent pages on demand
- Improve performance for large album collections
- Provide clear pagination metadata in API responses (current_page, total, etc.)
- Migrate Smart albums and Tag albums to use new paginated endpoints
- Ensure consistent pagination behavior across all album types

## Non-Goals

- Modifying existing `/Album` endpoint (maintaining backward compatibility, avoiding test changes)
- Search results pagination (separate feature)
- Cursor-based pagination (using offset-based pagination)
- Album sorting changes (use existing SortingDecorator)
- Extracting album/photo fetching into separate service classes (use repository pattern methods)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-007-01 | Fetch album metadata without children/photos | GET `/Album/{id}/head` returns album metadata (title, description, counts, thumb, rights) without loading children or photos | Validate album_id exists and user has access | Return 404 if album not found, 403 if no access | None | Owner directive Q-007-02 |
| FR-007-02 | Fetch paginated album children | GET `/Album/{id}/albums?page=2` returns page 2 of child albums with pagination metadata | Validate `page` parameter is positive integer, default to 1 if missing | Return 422 if page parameter is invalid (negative, non-numeric) | None | Owner directive Q-007-02 |
| FR-007-03 | Fetch paginated album photos | GET `/Album/{id}/photos?page=2` returns page 2 of photos with pagination metadata | Validate `page` parameter is positive integer, default to 1 if missing | Return 422 if page parameter is invalid | None | Owner directive Q-007-02 |
| FR-007-04 | Return pagination metadata in API responses | Include `current_page`, `last_page`, `per_page`, `total` in paginated responses | Verify metadata is accurate (current_page matches request, total matches query count) | N/A | None | Standard REST pagination pattern |
| FR-007-05 | Support configurable page size via config table | Admin can configure page size settings (see FR-007-06 for config keys) | Validate page size values are positive integers (1-1000) | Reject invalid config values via validation rules | None | Owner directive Q-007-04 |
| FR-007-06 | Multiple granular pagination config keys | Admin can configure: `sub_albums_per_page` (default 30), `photos_per_page` (default 100), `search_results_per_page` (default 50) | Each config key validated independently | Reject invalid values, fall back to defaults | None | Owner directive Q-007-04 Option C |
| FR-007-07 | Configurable UI loading strategy for photos | Admin can configure `photos_pagination_ui_mode` with options: "infinite_scroll" (default), "load_more_button", "page_navigation" | Validate mode is one of allowed values | Fall back to "infinite_scroll" default | None | Owner directive Q-007-03 |
| FR-007-08 | Configurable UI loading strategy for albums | Admin can configure `albums_pagination_ui_mode` with options: "infinite_scroll" (default), "load_more_button", "page_navigation" | Validate mode is one of allowed values | Fall back to "infinite_scroll" default | None | Owner directive Q-007-03 |
| FR-007-09 | First page always loaded on album open | When album loads, automatically fetch `/Album/{id}/head`, `/Album/{id}/albums?page=1`, `/Album/{id}/photos?page=1` | N/A | N/A | None | Owner directive Q-007-03 |
| FR-007-10 | Subsequent pages loaded on demand | User triggers next page load via UI interaction (scroll, button click, page number) | N/A | N/A | None | Owner directive Q-007-03 |
| FR-007-11 | Page parameter defaults to 1 if absent | GET `/Album/{id}/photos` without `?page=` parameter returns first page (not all photos) | N/A | N/A | None | Owner directive Q-007-06 |
| FR-007-12 | Existing `/Album` endpoint unchanged | GET `/Album?album_id={id}` continues to return full album data (no pagination) for backward compatibility | N/A | N/A | None | Owner directive Q-007-02 |
| FR-007-13 | Migrate Smart albums to new endpoints | Smart albums (Recent, Starred, etc.) use new `/Album/{id}/photos` endpoint instead of inline pagination in SmartAlbumResource | Verify Smart albums return paginated data via new endpoint | N/A | None | Owner directive - consistency |
| FR-007-14 | Migrate Tag albums to new endpoints | Tag albums use new `/Album/{id}/photos` endpoint for paginated photos | Verify Tag albums return paginated data via new endpoint | N/A | None | Owner directive - consistency |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-007-01 | Pagination queries must use efficient SQL (LIMIT/OFFSET) | Performance for large datasets | Query execution time < 500ms for typical page load (p95) | SortingDecorator, repository pattern methods | Performance requirement Q-007-01 |
| NFR-007-02 | Pagination metadata calculation must be efficient | Avoid expensive COUNT(*) queries on every request | Consider caching total counts for large albums | Laravel LengthAwarePaginator | Performance requirement |
| NFR-007-03 | Frontend state management must support pagination per resource type | Clean separation, support multiple UI modes | Pagination state stored in Pinia stores (AlbumState, PhotosState) with UI mode support | Pinia stores, album-service.ts | Architectural consistency Q-007-03 |
| NFR-007-04 | New endpoints must not break existing API clients | Backward compatibility | Existing `/Album` endpoint unchanged, tests continue to pass | Legacy endpoint preserved | API stability Q-007-02 |
| NFR-007-05 | Config settings must be validated with sensible defaults | Data integrity, prevent misconfiguration | Defaults: sub_albums_per_page=30, photos_per_page=100, search_results_per_page=50, UI modes with validated enums | Configs table, validation rules | Operational safety Q-007-04 |
| NFR-007-06 | Code duplication acceptable for new endpoints | Avoid refactoring complexity, minimize test changes | New controllers/methods can duplicate logic from existing `/Album` implementation | Repository pattern methods | Owner directive Q-007-05 |

## UI / Interaction Mock-ups

### Album View with Configurable Pagination UI

**Mode 1: Infinite Scroll (default for both photos and albums)**

```
┌─────────────────────────────────────────────────────────────┐
│ Photos (showing 1-300 of 450)                                │
│ ┌────┐ ┌────┐ ┌────┐ ┌────┐                                 │
│ │    │ │    │ │    │ │    │  ... scroll down ...           │
│ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │                                 │
│ └────┘ └────┘ └────┘ └────┘                                 │
│ ... (300 photos already loaded)                              │
│ ┌────┐ ┌────┐ ┌────┐ ┌────┐                                 │
│ │ ░░ │ │ ░░ │ │ ░░ │ │ ░░ │  (auto-loading page 4...)      │
│ └────┘ └────┘ └────┘ └────┘                                 │
└─────────────────────────────────────────────────────────────┘
```

**Mode 2: "Load More" Button**

```
┌─────────────────────────────────────────────────────────────┐
│ Album: Vacation 2025                                  [≡][↓] │
│ 450 photos · 12 sub-albums                                   │
├─────────────────────────────────────────────────────────────┤
│ Sub-albums (showing 1-30 of 12) - Page Navigation           │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐                │
│ │ Beach  │ │ Hiking │ │ Food   │ │ City   │  ...            │
│ │ 45 ⚫   │ │ 32 ⚫   │ │ 28 ⚫   │ │ 67 ⚫   │                │
│ └────────┘ └────────┘ └────────┘ └────────┘                │
│                                            [◄ Prev] [1] [Next ►] │
│                                                               │
│ Photos (showing 1-100 of 450) - Load More                    │
│ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐   │
│ │    │ │    │ │    │ │    │ │    │ │    │ │    │ │    │   │
│ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │   │
│ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘   │
│ ... (more photos in grid layout)                             │
│                                                               │
│              [Load More Photos (350 remaining)]              │
└─────────────────────────────────────────────────────────────┘
```

**Mode 3: Page Navigation**

```
┌─────────────────────────────────────────────────────────────┐
│ Photos (showing 101-200 of 450)                              │
│ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐ ┌────┐   │
│ │    │ │    │ │    │ │    │ │    │ │    │ │    │ │    │   │
│ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │ │ 📷 │   │
│ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘ └────┘   │
│                                                               │
│           [◄ Prev] [1] [2] [3] [4] [5] ... [Next ►]         │
└─────────────────────────────────────────────────────────────┘
```

### Loading States

```
┌─────────────────────────────────────────────────────────────┐
│ Photos (loading page 2...)                                   │
│ ┌────┐ ┌────┐ ┌────┐ ┌────┐                                 │
│ │ ░░ │ │ ░░ │ │ ░░ │ │ ░░ │  (skeleton loading state)      │
│ └────┘ └────┘ └────┘ └────┘                                 │
│                                                               │
│ [Loading...] (disabled button during fetch)                  │
└─────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-007-01 | User opens album with 500 photos → Frontend makes 3 requests: `/Album/{id}/head`, `/Album/{id}/albums?page=1`, `/Album/{id}/photos?page=1` → First page loaded |
| S-007-02 | User clicks "Load More Photos" button (mode: load_more_button) → Frontend fetches `/Album/{id}/photos?page=2` → Photos appended to grid |
| S-007-03 | User clicks page "2" button (mode: page_navigation) → Frontend fetches `/Album/{id}/photos?page=2` → Photos replaced in grid |
| S-007-04 | User scrolls to bottom (mode: infinite_scroll) → Frontend auto-fetches next page → Photos appended seamlessly |
| S-007-05 | User loads album with 20 photos (less than page size) → First page shows all 20, pagination UI hidden (last_page = 1) |
| S-007-06 | User loads album with 150 sub-albums → First 30 loaded, page navigation shown |
| S-007-07 | User navigates to smart album (Recent) → Uses existing SmartAlbum pagination (unchanged) |
| S-007-08 | API client uses legacy `/Album?album_id=X` → Returns full album data (no pagination, backward compat) |
| S-007-09 | API client uses new `/Album/{id}/photos` without page param → Returns first page (page defaults to 1) |
| S-007-10 | Admin configures custom page sizes (sub_albums_per_page=20, photos_per_page=50) → New endpoints use custom values |
| S-007-11 | Admin configures UI mode (photos: infinite_scroll, albums: page_navigation) → Frontend renders appropriate UI controls |
| S-007-12 | User requests page beyond available data (page=999) → Returns empty items array with correct metadata (current_page=999, total unchanged) |
| S-007-13 | Concurrent users load same album → Each receives correct paginated data without conflicts |
| S-007-14 | User navigates to Smart album (Recent) → Uses new `/Album/{id}/photos` endpoint with pagination |
| S-007-15 | User navigates to Tag album → Uses new `/Album/{id}/photos` endpoint with pagination |
| S-007-16 | Frontend loads Smart album → Makes request to `/Album/{id}/head` and `/Album/{id}/photos?page=1` (no albums children) |

## Test Strategy

- **Core:** Unit tests for pagination logic in SortingDecorator, query builders
- **Application:** Feature tests for controllers returning paginated responses with correct metadata
- **REST:** API tests verifying pagination parameter handling, metadata accuracy, backward compatibility
- **UI (JS):** Component tests for AlbumState/PhotosState pagination state management, "Load More" button behavior
- **Performance:** Load tests with large albums (1000+ photos) verifying query performance < 500ms

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-007-01 | PaginationConfig: sub_albums_per_page (int, default 30), photos_per_page (int, default 100), search_results_per_page (int, default 50) | application, REST |
| DO-007-02 | PaginationUIModeConfig: photos_pagination_ui_mode (enum: infinite_scroll, load_more_button, page_navigation), albums_pagination_ui_mode (enum, same options) | application, REST, UI |
| DO-007-03 | PaginationMetadata: current_page (int), last_page (int), per_page (int), total (int) | application, REST, UI |
| DO-007-04 | HeadAlbumResource: Album metadata without children/photos arrays (id, title, description, counts, thumb, rights) | application, REST |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-007-01 | GET /Album/{id}/head | Fetch album metadata without children/photos | Lightweight endpoint, returns HeadAlbumResource |
| API-007-02 | GET /Album/{id}/albums?page={n} | Fetch paginated sub-albums | Page defaults to 1 if absent, returns PaginatedAlbumsResource |
| API-007-03 | GET /Album/{id}/photos?page={n} | Fetch paginated photos | Page defaults to 1 if absent, returns PaginatedPhotosResource |
| API-007-04 | GET /Album?album_id={id} (unchanged) | Fetch full album data (legacy) | Backward compatibility, no pagination, returns AlbumResource |
| API-007-05 | GET /Albums (unchanged) | Fetch root albums | No changes, returns all root albums |

### CLI Commands / Flags

None required for this feature.

### Telemetry Events

None required for this feature.

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-007-01 | tests/Feature_v2/fixtures/large-album.php | Album with 500+ photos for pagination testing |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-007-01 | Pagination loading state (load_more_button mode) | User clicks "Load More" → Loading spinner shown, button disabled |
| UI-007-02 | Pagination complete state (load_more_button mode) | Last page loaded → "Load More" button hidden |
| UI-007-03 | Pagination navigation state (page_navigation mode) | Page number buttons shown, current page highlighted, prev/next enabled/disabled |
| UI-007-04 | Infinite scroll loading state | User scrolls to bottom → Skeleton items shown, next page auto-fetches |
| UI-007-05 | Pagination error state | API request fails → Error message shown, "Retry" button available |

## Telemetry & Observability

No telemetry events required for this feature. Standard application logging for errors.

## Documentation Deliverables

- Update [knowledge-map.md](../../knowledge-map.md) with new API endpoints and pagination flow
- Document pagination configuration in admin guide: config keys for page sizes and UI modes
- Create/update API documentation for new endpoints: `/Album/{id}/head`, `/Album/{id}/albums`, `/Album/{id}/photos`
- Add inline comments in repository methods explaining pagination behavior
- Document UI mode configuration options and behavior differences

## Fixtures & Sample Data

- Create test album with 500+ photos for pagination load testing
- Create test album with 100+ child albums for child pagination testing

## Spec DSL

```yaml
domain_objects:
  - id: DO-007-01
    name: PaginationConfig
    fields:
      - name: sub_albums_per_page
        type: integer
        constraints: "1-1000"
        default: 30
      - name: photos_per_page
        type: integer
        constraints: "1-1000"
        default: 100
      - name: search_results_per_page
        type: integer
        constraints: "1-1000"
        default: 50
  - id: DO-007-02
    name: PaginationUIModeConfig
    fields:
      - name: photos_pagination_ui_mode
        type: enum
        values: ["infinite_scroll", "load_more_button", "page_navigation"]
        default: "infinite_scroll"
      - name: albums_pagination_ui_mode
        type: enum
        values: ["infinite_scroll", "load_more_button", "page_navigation"]
        default: "infinite_scroll"
  - id: DO-007-03
    name: PaginationMetadata
    fields:
      - name: current_page
        type: integer
      - name: last_page
        type: integer
      - name: per_page
        type: integer
      - name: total
        type: integer
  - id: DO-007-04
    name: HeadAlbumResource
    description: Album metadata without children/photos arrays
    fields:
      - name: id
        type: string
      - name: title
        type: string
      - name: description
        type: string
      - name: num_photos
        type: integer
      - name: num_children
        type: integer
      - name: thumb
        type: ThumbResource
      - name: rights
        type: AlbumRightsResource

routes:
  - id: API-007-01
    method: GET
    path: /Album/{id}/head
    parameters:
      - id: string (required, path parameter)
    response: HeadAlbumResource
  - id: API-007-02
    method: GET
    path: /Album/{id}/albums
    parameters:
      - id: string (required, path parameter)
      - page: integer (optional, default: 1)
    response: PaginatedAlbumsResource
  - id: API-007-03
    method: GET
    path: /Album/{id}/photos
    parameters:
      - id: string (required, path parameter)
      - page: integer (optional, default: 1)
    response: PaginatedPhotosResource
  - id: API-007-04
    method: GET
    path: /Album (unchanged)
    parameters:
      - album_id: string (required, query parameter)
    response: AlbumResource (full data, no pagination)

fixtures:
  - id: FX-007-01
    path: tests/Feature_v2/fixtures/large-album.php
    description: Album with 500+ photos for pagination testing
  - id: FX-007-02
    path: tests/Feature_v2/fixtures/large-album-children.php
    description: Album with 100+ child albums for pagination testing

ui_states:
  - id: UI-007-01
    description: Pagination loading state (load_more_button mode)
  - id: UI-007-02
    description: Pagination complete state (load_more_button mode)
  - id: UI-007-03
    description: Pagination navigation state (page_navigation mode)
  - id: UI-007-04
    description: Infinite scroll loading state (infinite_scroll mode - default)
  - id: UI-007-05
    description: Pagination error state (all modes)
```

## Appendix

### Existing Pagination Implementation Reference

Smart albums already implement pagination using the `SortingDecorator::paginate()` method:

**File:** [app/SmartAlbums/BaseSmartAlbum.php:138-149](app/SmartAlbums/BaseSmartAlbum.php#L138-L149)

```php
protected function getPhotosAttribute(): LengthAwarePaginator
{
    if ($this->photos === null) {
        $sorting = PhotoSortingCriterion::createDefault();

        $photos = (new SortingDecorator($this->photos()))
            ->orderPhotosBy($sorting->column, $sorting->order)
            ->paginate($this->config_manager->getValueAsInt('photos_pagination_limit'));
        $this->photos = $photos;
    }
    return $this->photos;
}
```

**File:** [app/Models/Extensions/SortingDecorator.php:203-247](app/Models/Extensions/SortingDecorator.php#L203-L247)

The `SortingDecorator::paginate()` method handles hybrid SQL + PHP sorting efficiently:
- Applies SQL sorting for database-level columns
- Falls back to PHP sorting for complex columns (TITLE, DESCRIPTION)
- Returns Laravel `LengthAwarePaginator` with metadata

### API Response Structure Examples

**NEW: HeadAlbumResource (GET /Album/{id}/head):**
```json
{
  "id": "abc123",
  "title": "Vacation 2025",
  "description": "Summer vacation photos",
  "num_photos": 450,
  "num_children": 12,
  "thumb": {
    "id": "photo123",
    "url": "https://...",
    "type": "photo"
  },
  "rights": {
    "can_edit": true,
    "can_download": true
  }
}
```

**NEW: PaginatedPhotosResource (GET /Album/{id}/photos?page=1):**
```json
{
  "data": [
    {
      "id": "photo1",
      "title": "Beach sunset",
      "thumb": {...},
      ...
    },
    ...
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 100,
  "total": 450
}
```

**NEW: PaginatedAlbumsResource (GET /Album/{id}/albums?page=1):**
```json
{
  "data": [
    {
      "id": "album1",
      "title": "Beach",
      "num_photos": 45,
      "thumb": {...},
      ...
    },
    ...
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 30,
  "total": 12
}
```

**UNCHANGED: SmartAlbumResource (existing pagination):**
```json
{
  "id": "starred",
  "title": "Starred",
  "photos": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 100,
  "total": 450
}
```

### User Decisions Summary

All open questions (Q-007-01 through Q-007-06) have been resolved:

- **Q-007-01:** Offset-based pagination with config table storage (Option A)
- **Q-007-02:** Create new separate endpoints (Option B): `/Album/{id}/head`, `/Album/{id}/albums`, `/Album/{id}/photos`
- **Q-007-03:** Configurable UI modes with infinite scroll as default for both photos and albums
- **Q-007-04:** Multiple granular config keys (Option C): `sub_albums_per_page=30`, `photos_per_page=100`, `search_results_per_page=50`
- **Q-007-05:** Use repository pattern methods (Option B), code duplication acceptable
- **Q-007-06:** New endpoints default page=1, existing `/Album` endpoint unchanged for backward compatibility
