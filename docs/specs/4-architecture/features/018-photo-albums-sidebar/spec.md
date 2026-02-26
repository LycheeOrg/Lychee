# Feature 018 – Photo Albums Sidebar

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-02-26 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/018-photo-albums-sidebar/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/018-photo-albums-sidebar/tasks.md` |
| Roadmap entry | #018 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

When a user opens the detail sidebar for a photo, the sidebar currently shows metadata (title, dates, description, tags, EXIF, location, license, statistics, rating). This feature adds a new section listing all albums that contain the photo. The data is fetched on-demand (lazy-loaded) when the sidebar opens — it is not included in the standard `PhotoResource`. The backend endpoint verifies that the requesting user has access to the photo and filters the album list to only those the user is permitted to see.

**Affected modules:** Application (new Request class, PhotoController endpoint), REST API (new `GET Photo::albums` route), UI (`PhotoDetails.vue` drawer component, `PhotoService`), Models (Photo, Album), Policies (PhotoPolicy, AlbumPolicy).

## Goals

- Show all albums containing a photo in the details sidebar, filtered to the user's access rights
- Lazy-load the album list only when the sidebar is opened (not bundled with the photo resource)
- Verify photo access before returning any data
- Filter returned albums against the user's accessible albums via `AlbumPolicy::canAccess`
- Provide album title and ID so users can navigate to the album
- Keep the endpoint lightweight and fast

## Non-Goals

- Adding or removing album associations from the sidebar (this is read-only)
- Showing albums in the photo edit drawer (`PhotoEdit.vue`)
- Including smart albums (Highlighted, Recent, etc.) — only real `Album` records from the `photo_album` pivot
- Prefetching album lists on photo load or gallery view
- Deep album tree / hierarchy display (only flat list of album titles)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-018-01 | New endpoint returns albums for a photo | `GET /api/v2/Photo/{photo_id}/albums` returns a JSON array of accessible albums (`id`, `title`) for the given photo. | `photo_id` must be a valid, existing photo ID (random string, max 24 chars). | 404 if photo not found. | No telemetry. | User requirement |
| FR-018-02 | Photo access is verified before returning data | Endpoint checks `PhotoPolicy::CAN_SEE` for the requesting user. Guests and authenticated users are both supported. | Policy gate evaluated before any album query. | 403 Forbidden if the user cannot see the photo. 401 if authentication is required but not provided. | No telemetry. | Security requirement |
| FR-018-03 | Album list filtered by user access | Only albums for which `AlbumPolicy::canAccess(user, album)` returns true are included in the response. Albums the user cannot access are silently omitted. | Iterate albums from the `photo_album` pivot and check access for each. | If user has access to zero albums, return an empty array (not an error). | No telemetry. | Security requirement |
| FR-018-04 | Response contains album ID and title | Each album in the response includes at minimum `id` (string) and `title` (string). | Fields sourced from `Album` model. | N/A | No telemetry. | User requirement |
| FR-018-05 | Sidebar section displays album list | The `PhotoDetails.vue` drawer shows a new "Albums" section listing album titles. Section appears after tags and before EXIF data. | Section visible whenever the sidebar is open. If albums list is empty, show "No albums" or equivalent. If loading, show a spinner/skeleton. | If fetch fails, show a brief error message in the section. | No telemetry. | User requirement |
| FR-018-06 | Album list fetched lazily on sidebar open | The frontend requests the album list only when `areDetailsOpen` transitions to `true`. The request is not repeated while the sidebar remains open for the same photo. | Watch `areDetailsOpen` and `photoStore.photo.id` to trigger fetch. Cache response per photo ID during the session. | If request fails, section shows error state without blocking other sidebar content. | No telemetry. | Performance requirement |
| FR-018-07 | Album titles are clickable links that navigate to the album | Each album title in the sidebar is a clickable link. Clicking navigates the user to the album gallery view (`/gallery/{albumId}`). The sidebar closes and the photo store resets before navigation. | Uses `router.push({ name: "album", params: { albumId } })`. Sidebar state (`are_details_open`) set to `false` before navigation. | N/A | No telemetry. | User requirement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-018-01 | Endpoint responds within 200ms for photos with ≤50 albums | User experience | Measure response time in feature tests. Eager-load album titles. | Database indexing on `photo_album` pivot table (already indexed). | Performance standard |
| NFR-018-02 | PHP code follows Lychee conventions | Maintainability | License headers, snake_case variables, strict comparison (`===`), PSR-4, no `empty()`, `in_array(..., true)`. | php-cs-fixer, phpstan level 6 | [coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-018-03 | Frontend follows Vue3/TypeScript conventions | Maintainability | Template-first, Composition API, regular function declarations, `.then()` not async/await, axios in services directory. | Prettier, ESLint | [coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-018-04 | Test coverage for endpoint scenarios | Correctness | Feature tests for: valid photo with accessible/inaccessible albums, photo not found, forbidden photo, unauthenticated user, empty album list. | BaseApiWithDataTest, in-memory SQLite | Testing standard |
| NFR-018-05 | No N+1 queries | Performance | Eager-load albums in single query then filter in PHP. Verify with query count assertion or manual inspection. | Eloquent eager loading | Performance standard |

## UI / Interaction Mock-ups

### 1. Photo Details Sidebar — Albums Section

```
┌──────────────────────────────────────────────┐
│  ✕  Photo Details                            │
├──────────────────────────────────────────────┤
│                                              │
│  Title: Sunset at the Beach                  │
│  Resolution: 4032 × 3024                     │
│  Filesize: 5.2 MB                            │
│                                              │
│  ── Dates ──────────────────────────────     │
│  Uploaded: 2026-01-15                        │
│  Captured: 2025-12-28                        │
│                                              │
│  ── Description ────────────────────────     │
│  A beautiful sunset captured from the pier.  │
│                                              │
│  ── Tags ───────────────────────────────     │
│  [ sunset ] [ beach ] [ nature ]             │
│                                              │
│  ── Albums ─────────────────────────────     │  ← NEW SECTION
│  • Vacation 2025              ←── clickable  │
│  • Best of Nature             ←── clickable  │
│  • Shared Favourites          ←── clickable  │
│                                              │
│  ── EXIF ───────────────────────────────     │
│  Camera: Canon EOS R5                        │
│  Lens: RF 24-70mm f/2.8L                     │
│  ...                                         │
│                                              │
└──────────────────────────────────────────────┘
```

### 2. Albums Section — Loading State

```
│  ── Albums ─────────────────────────────     │
│  ◌ Loading...                                │
```

### 3. Albums Section — Empty State

```
│  ── Albums ─────────────────────────────     │
│  This photo is not in any album.             │
```

### 4. Albums Section — Error State

```
│  ── Albums ─────────────────────────────     │
│  ⚠ Could not load albums.                   │
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-018-01 | Authenticated user requests albums for own photo in 3 albums: Returns all 3 albums with id/title |
| S-018-02 | Authenticated user requests albums for photo shared with them: Returns only albums user can access |
| S-018-03 | Authenticated user requests albums where some albums are inaccessible: Inaccessible albums silently filtered out |
| S-018-04 | Guest requests albums for photo in public album: Returns public albums only |
| S-018-05 | Guest requests albums for private photo: Returns 403 Forbidden |
| S-018-06 | User requests albums for non-existent photo: Returns 404 Not Found |
| S-018-07 | User requests albums for photo not in any album: Returns empty array `[]` |
| S-018-08 | Sidebar opens and fetches album list: Albums section populates after loading state |
| S-018-09 | Sidebar stays open, same photo: No duplicate request fired |
| S-018-10 | Sidebar reopens for a different photo: New request fetches albums for the new photo |
| S-018-11 | User clicks album title in sidebar: Sidebar closes, navigates to `/gallery/{albumId}` |

## Test Strategy

- **Application:** Feature tests for the `GET Photo/{photo_id}/albums` endpoint:
  - Authenticated owner sees all their albums (S-018-01)
  - Authenticated user sees only accessible albums (S-018-02, S-018-03)
  - Guest sees public albums (S-018-04)
  - Guest denied for private photo (S-018-05)
  - Non-existent photo returns 404 (S-018-06)
  - Photo with no albums returns empty array (S-018-07)
- **REST:** Validate JSON structure (`id`, `title` fields present)
- **UI (JS):** Manual verification:
  - Sidebar shows albums section with correct data (S-018-08)
  - No duplicate requests on same photo (S-018-09)
  - Switching photos triggers new fetch (S-018-10)
  - Clicking album title navigates to album (S-018-11)

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-018-01 | GetPhotoAlbumsRequest: Validates `photo_id` path parameter and authorizes access via `PhotoPolicy::CAN_SEE` | Application (Request validation) |
| DO-018-02 | PhotoAlbumEntry: Lightweight Spatie Data resource with `id` (string) and `title` (string) | Application (Response resource) |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-018-01 | GET /api/v2/Photo/{photo_id}/albums | Returns list of accessible albums for the given photo | New endpoint. Response: `PhotoAlbumEntry[]` |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-018-01 | Albums loading | Sidebar opens → spinner shown in Albums section |
| UI-018-02 | Albums loaded (non-empty) | Fetch completes → list of album titles displayed |
| UI-018-03 | Albums loaded (empty) | Fetch completes with empty array → "This photo is not in any album." |
| UI-018-04 | Albums error | Fetch fails → "Could not load albums." message |
| UI-018-05 | Album click navigation | User clicks album title → sidebar closes, router navigates to `/gallery/{albumId}` |

## Telemetry & Observability

No custom telemetry events. Standard Laravel request logging applies.

## Documentation Deliverables

- Update knowledge map to document the new endpoint and its relationship to PhotoController / PhotoDetails sidebar.
- No ADR required (straightforward read-only endpoint).

## Spec DSL

```yaml
domain_objects:
  - id: DO-018-01
    name: GetPhotoAlbumsRequest
    fields:
      - name: photo_id
        type: string
        constraints: "required, exists in photos table"
  - id: DO-018-02
    name: PhotoAlbumEntry
    fields:
      - name: id
        type: string
      - name: title
        type: string
routes:
  - id: API-018-01
    method: GET
    path: /api/v2/Photo/{photo_id}/albums
    response: PhotoAlbumEntry[]
ui_states:
  - id: UI-018-01
    description: Albums section loading spinner
  - id: UI-018-02
    description: Albums section with album list
  - id: UI-018-03
    description: Albums section empty state
  - id: UI-018-04
    description: Albums section error state
  - id: UI-018-05
    description: Album click navigates to album view
```

## Appendix

### Response Example

```json
[
  { "id": "abc123def456", "title": "Vacation 2025" },
  { "id": "xyz789ghi012", "title": "Best of Nature" }
]
```

### Empty Response

```json
[]
```
