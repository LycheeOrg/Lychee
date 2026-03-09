# Feature 026 – Album Photo Tag Filter

| Field | Value |
|-------|-------|
| Status | Ready for Implementation |
| Last updated | 2026-03-09 |
| Owners | LycheeOrg |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #026 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

When viewing an album with tagged photos, users currently see all photos without any ability to filter by tags within that album view. This feature adds tag-based filtering to the album photo view: users can select one or more tags to filter the displayed photos, with AND/OR logic support. The filter UI only appears if the album contains photos with tags, and uses a new `Album::tags` endpoint to discover which tags are available in the album.

Affected modules: **Album Photos Endpoint** (`App\Http\Controllers\Gallery\AlbumPhotosController`), **REST API** (new `Album::tags` endpoint), **UI** (tag filter component in album view), **Photo Query** (`App\Repositories\PhotoRepository`).

## Goals

1. Add a new REST API endpoint (`GET /api/Album::tags?album_id={id}`) that returns tags available in a specific album.
2. Extend the existing `GET /api/Album::photos` endpoint to accept optional tag filter parameters (`tag_ids[]` and `tag_logic`).
3. Build a Vue3 tag filter component in the album view with multi-select dropdown for tags and AND/OR logic toggle.
4. Hide the filter UI when an album contains no tagged photos.
5. Ensure filter results respect existing album access policies and pagination.
6. Maintain performance with indexed queries for tag filtering.

## Non-Goals

- Cross-album filtering (this filters photos within a single album only).
- Saving filter configurations or creating smart albums from filters.
- Adding other filter types (date, location, rating) in this feature.
- URL query string representation of active filter (filter state in component only).
- User-specific filter preferences or history.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-026-01 | Add new endpoint `GET /api/Album::tags?album_id={id}` that returns all unique tags from photos in the specified album (regular Album, TagAlbum, or Smart Album), ordered alphabetically by tag name. Returns tags from photos directly attached to that album; album-level access rights determine visibility. | Endpoint queries photos in album, collects all tags (via many-to-many relation), returns distinct tag objects `[{id, name, description}]`. | Album ID must be valid and user must have read access to the album (respecting existing `login_required:album` middleware). | If album doesn't exist or user lacks access, return 404 or 403. If album has no photos with tags, return empty array `[]`. | — | User requirement for tag selection scoped to album context. Q-026-01, Q-026-04 resolved. |
| FR-026-02 | Extend `GET /api/Album::photos` endpoint to accept optional query parameters: `tag_ids[]` (array of tag IDs) and `tag_logic` (enum: "AND" or "OR", default "OR"). | When tag filter parameters provided, endpoint filters photos to include only those matching the tag criteria before pagination. Returns standard `PaginatedPhotosResource`. | `tag_ids[]` validated as array of integers; `tag_logic` validated as "AND" or "OR" enum value. Empty `tag_ids[]` treated as no filter (returns all photos). | Invalid tag IDs individually ignored; if ALL provided tag IDs are invalid/non-existent, return 422 validation error with message "No valid tags found for filtering". | — | Issue #4037, Q-026-05 resolved. |
| FR-026-03 | When `tag_logic="OR"` and `tag_ids=[T1, T2]`, return photos that have tag T1 OR tag T2. | Photo query uses `whereHas('tags', fn($q) => $q->whereIn('tags.id', [T1, T2]))` or equivalent. | — | Empty result if no photos have any of the specified tags. | — | Issue #4037 |
| FR-026-04 | When `tag_logic="AND"` and `tag_ids=[T1, T2]`, return photos that have BOTH tag T1 AND tag T2. | Photo query joins `photos_tags` multiple times or uses `havingRaw('COUNT(DISTINCT tag_id) = ?', [count($tag_ids)])` with groupBy. | At least 2 tag IDs required for AND logic to make sense; single tag ID treated same as OR. | Empty result if no photos have all specified tags. | — | Issue #4037 |
| FR-026-05 | Tag filtering respects existing album access policies and pagination settings. | Filter applied after album access check but before pagination; standard `photos_per_page` config used. | — | — | — | Security and consistency requirement. |
| FR-026-06 | Frontend tag filter component fetches available tags via `Album::tags` endpoint when album view loads. | Component makes GET request to `/api/Album::tags?album_id={current_album}` and populates tag dropdown with results. | If request fails or returns empty array, component hides itself. | API errors handled gracefully (show error message or hide filter). | — | UX requirement. |
| FR-026-07 | Frontend tag filter UI includes: multi-select dropdown for tags, AND/OR logic toggle (radio buttons or switch), "Apply" button, "Clear" button. | User selects tags, chooses logic, clicks Apply → component sends filtered request to `Album::photos` with `tag_ids[]` and `tag_logic` parameters → photo grid updates with filtered results. | — | — | — | Issue #4037 mockup. |
| FR-026-08 | Filter UI is hidden when album has no tagged photos (when `Album::tags` returns empty array). | Component checks response from `Album::tags`; if `tags.length === 0`, component does not render or collapses/hides itself. | — | — | — | User requirement: "if there are no tags, the filter is hidden". |
| FR-026-09 | "Clear" button resets tag selection and reloads all photos in the album (removes tag filter from `Album::photos` request). | Frontend clears selected tags, sends unfiltered request to `Album::photos` (no `tag_ids[]` or `tag_logic` parameters), updates photo grid with all photos. | — | — | — | UX requirement. |
| FR-026-10 | Filter state persists during pagination (when user navigates to next/previous page, tag filter remains active). | Frontend includes `tag_ids[]` and `tag_logic` parameters in all paginated `Album::photos` requests until filter is cleared. | — | — | — | UX consistency. |
| FR-026-11 | Filter component shows active filter summary (e.g., "Showing photos with tags: Tag1 OR Tag2"). | Component displays human-readable filter description when filter is active; hide when no filter. | — | — | — | UX clarity. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-026-01 | Tag filtering query completes in ≤100ms p95 for albums with up to 1000 photos and up to 20 unique tags. | Performance | Queries use indexed joins on `photos_tags.photo_id`, `photos_tags.tag_id`. Existing indexes sufficient. | Existing database indexes. | — |
| NFR-026-02 | `/api/Album::tags` endpoint executes in ≤50ms p95 for albums with up to 1000 photos. Tag dropdown includes search/filter capability for large tag lists. | Performance & Usability | Query uses single join with DISTINCT, indexed on `photo_album.album_id`, `photos_tags.photo_id`. PrimeVue MultiSelect `filter` prop enabled for dropdown search. | Existing indexes, PrimeVue MultiSelect component. | Q-026-02 resolved. |
| NFR-026-03 | Filter UI loads and renders without blocking main UI thread. | UX responsiveness | Vue3 async component with loading states; API calls non-blocking. | — | — |
| NFR-026-04 | Filter respects existing album access policies (no permission bypass). | Security | Tag filter applied within existing `AlbumPhotosController` flow which already checks album access via middleware. | `login_required:album` middleware | Critical security requirement. |
| NFR-026-05 | Backward compatibility: Existing `Album::photos` behavior unchanged when tag filter parameters not provided. | Stability | Tag filter parameters are optional; omitting them returns all photos as before. | — | — |
| NFR-026-06 | Graceful handling of edge cases: album with no tags, invalid tag IDs, no matching photos. | Resilience | Empty tag list hides UI; invalid tag IDs ignored; empty results show appropriate message. | — | — |
| NFR-026-07 | New code follows PSR-4, strict comparisons, no `empty()`, `in_array()` with `true` third arg, snake_case variables. | Coding conventions | `vendor/bin/php-cs-fixer fix` + `make phpstan` both pass. | — | AGENTS.md |
| NFR-026-08 | Frontend code uses TypeScript in Composition API, PrimeVue components, no `await` in top-level (use `.then()`). | Coding conventions | Vue3 linting passes, no TypeScript errors. | — | AGENTS.md |
| NFR-026-09 | Translation keys added for all new UI strings in 22 languages. | I18n | All strings use `$t('key')` syntax; keys added to `lang/*/gallery.php`. | — | I18n requirement. |

## UI / Interaction Mock-ups

### Album View with Tag Filter Component

```
┌────────────────────────────────────────────────────────────────┐
│ 📁 Creepy Magazine                                    [≡ Menu] │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ 🏷️ Filter by tags:                                             │
│ ┌─────────────────────────────────────────────┐               │
│ │ ☑ Ken Kelly                                ▼│               │
│ │ ☑ Frank Frazetta                            │               │
│ │ ☐ Richard Corben                            │               │
│ │ ☐ Bernie Wrightson                          │               │
│ └─────────────────────────────────────────────┘               │
│ Logic: ( • ) OR    ( ) AND    [Apply] [Clear]                 │
│                                                                │
│ ─────────────────────────────────────────────────────────────  │
│ Showing 24 photos (filtered by: Ken Kelly OR Frank Frazetta)  │
│ ─────────────────────────────────────────────────────────────  │
│                                                                │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐             │
│ │  Photo  │ │  Photo  │ │  Photo  │ │  Photo  │             │
│ │  Thumb  │ │  Thumb  │ │  Thumb  │ │  Thumb  │             │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘             │
│                                                                │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐             │
│ │  Photo  │ │  Photo  │ │  Photo  │ │  Photo  │             │
│ │  Thumb  │ │  Thumb  │ │  Thumb  │ │  Thumb  │             │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘             │
│                                                                │
│                 [ « Previous | 1 | 2 | 3 | Next » ]            │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

**Explanation:**
- **Filter component location:** Positioned below album header, above photo grid.
- **Tags multi-select dropdown:** Shows all tags that exist in photos within this album (fetched from `/api/Album::tags?album_id={album}`).
- **Logic toggle:** Radio buttons for "OR" (default) or "AND". 
  - "OR" = photos with ANY of the selected tags
  - "AND" = photos with ALL selected tags
- **Apply button:** Sends filtered request to `/api/Album::photos?album_id={album}&tag_ids[]={T1}&tag_ids[]={T2}&tag_logic=OR`
- **Clear button:** Resets selection, reloads all photos (unfiltered request).
- **Active filter summary:** Shows human-readable description when filter is active.
- **Component visibility:** If `/api/Album::tags` returns empty array, entire filter component is hidden.

### Album View WITHOUT Tags (Filter Hidden)

```
┌────────────────────────────────────────────────────────────────┐
│ 📁 Vacation Photos 2024                           [≡ Menu]    │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│ Showing 48 photos                                              │
│ ─────────────────────────────────────────────────────────────  │
│                                                                │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐             │
│ │  Photo  │ │  Photo  │ │  Photo  │ │  Photo  │             │
│ │  Thumb  │ │  Thumb  │ │  Thumb  │ │  Thumb  │             │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘             │
│                                                                │
│       (No tag filter shown - album has no tagged photos)       │
│                                                                │
└────────────────────────────────────────────────────────────────┘
```

**Notes:**
- When album has no tags, filter component does not render (requirement FR-026-08).
- Photo grid layout and pagination remain unchanged.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|-------------------------------|
| S-026-01 | User views album with 10 photos, 3 different tags — Filter UI displayed with 3 tags in dropdown, sorted alphabetically. |
| S-026-02 | User views album with no tagged photos — Filter UI hidden (component does not render). |
| S-026-03 | User selects tags [T1, T2] with "OR" logic, clicks Apply — Returns photos with tag T1 OR tag T2; photo grid updates; filter summary shows "Filtered by: T1 OR T2". |
| S-026-04 | User selects tags [T1, T2] with "AND" logic, clicks Apply — Returns photos with BOTH tag T1 AND tag T2; photo grid updates. |
| S-026-05 | User selects single tag [T1] (logic irrelevant), clicks Apply — Returns photos with tag T1. |
| S-026-06 | User applies filter with tags [T1, T2, T3] with "AND" logic — Returns photos with ALL three tags (T1 ∩ T2 ∩ T3). |
| S-026-07 | User applies filter but no photos match criteria — Photo grid empty; shows "No photos found matching your filter criteria" message. |
| S-026-08 | User clicks "Clear" button after applying filter — Filter selection reset; all photos reloaded (unfiltered); filter summary hidden. |
| S-026-09 | User applies filter, navigates to page 2 — Filter persists; page 2 shows filtered photos; pagination includes `tag_ids[]` and `tag_logic` parameters. |
| S-026-10 | User applies filter, then navigates away and returns to album — Filter state not persisted (by default, shows all photos; user must reapply filter). |
| S-026-11 | `/api/Album::tags?album_id=A` called for album A with 5 photos having tags [T1, T2, T1, T3, T2] — Returns distinct tags: `[{id: T1, name: "..."}, {id: T2, name: "..."}, {id: T3, name: "..."}]` sorted alphabetically by name. |
| S-026-12 | `/api/Album::tags?album_id=invalid` for non-existent album — Returns 404 error. |
| S-026-13 | `/api/Album::tags?album_id=A` for private album user doesn't have access to — Returns 403 error (middleware blocks). |
| S-026-14 | `/api/Album::photos?album_id=A&tag_ids[]=T1&tag_ids[]=T2&tag_logic=OR` called — Returns paginated photos with tag T1 OR T2. |
| S-026-15 | `/api/Album::photos?album_id=A&tag_ids[]=999&tag_logic=OR` with single invalid tag ID — Returns 422 validation error \"No valid tags found for filtering\". Multiple tags with some valid: invalid IDs silently ignored, returns photos matching valid tag IDs. |
| S-026-16 | `/api/Album::photos?album_id=A` called without tag filter parameters — Returns all photos (backward compatible, no filter applied). |
| S-026-17 | Album with 1000 photos and 20 unique tags — Tag filter query completes within performance budget (≤100ms); `/api/Album::tags` completes within ≤50ms. |
| S-026-18 | User with read-only access to shared album applies tag filter — Filter works correctly; respects existing album access policies. |
| S-026-19 | TagAlbum with photos filtered by tag "Landscape" — User can apply additional tag filter (e.g., "Mountains") to further filter photos within the TagAlbum that have BOTH Landscape and Mountains tags. |
| S-026-20 | Smart Album (e.g., "Recent Photos") with tagged photos — User can apply tag filter to the Smart Album's computed photo set; returns photos from Smart Album that match tag criteria. |

## Test Strategy

- **Core/Application:** Unit tests for tag filter query logic (OR logic, AND logic, edge cases with invalid IDs). Mock Photo/Tag models.
- **REST:** Feature tests for `GET /api/Album::tags` (valid album, invalid album, private album, album with no tagged photos). Feature tests for `GET /api/Album::photos` with tag filter parameters (tag OR logic, tag AND logic, no filter params for backward compat, invalid tag IDs, pagination with filter).
- **CLI:** Not applicable (no CLI changes).
- **UI (JS/Vue):** Component tests for tag filter component (fetch tags, select tags, toggle logic, apply filter, clear filter, hide when no tags). Integration tests for filter state during pagination.
- **Integration:** End-to-end tests creating test albums/photos/tags with various access permissions, applying filters, verifying correct filtered results, pagination, and access control.
- **Security:** Tests ensuring album access policies are respected (guest vs. authenticated, shared vs. private albums).

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-026-01 | `GetAlbumPhotosRequest` — Extend existing DTO to accept optional `tag_ids: int[]` and `tag_logic: "AND"\|"OR"` parameters. | `App\Http\Requests\Album\GetAlbumPhotosRequest` |
| DO-026-02 | `AlbumTagsRequest` — New DTO for album tags endpoint: `album_id: string`. | `App\Http\Requests\Album\AlbumTagsRequest` |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-026-01 | GET `/api/Album::tags?album_id={id}` | Returns distinct tags from photos in the specified album, ordered alphabetically. Response: `{tags: [{id, name, description}]}`. | Uses existing `login_required:album` middleware for access control. |
| API-026-02 | GET `/api/Album::photos?album_id={id}&tag_ids[]={t1}&tag_ids[]={t2}&tag_logic=OR` | **Extended** existing endpoint to accept optional tag filter parameters. Returns paginated filtered photos. | Backward compatible: omitting tag params returns all photos as before. |

### Database Migrations

Not applicable (no schema changes required; uses existing `photos_tags` many-to-many relationship).

### CLI Commands / Flags

Not applicable (no CLI additions).

### Translation Keys

| ID | Key | Description |
|----|-----|-------------|
| TRANS-026-01 | `gallery.tag_filter_label` | "Filter by tags:" (label for filter component). |
| TRANS-026-02 | `gallery.tag_filter_logic_or` | "OR" (logic toggle option). |
| TRANS-026-03 | `gallery.tag_filter_logic_and` | "AND" (logic toggle option). |
| TRANS-026-04 | `gallery.tag_filter_apply_button` | "Apply" (apply button text). |
| TRANS-026-05 | `gallery.tag_filter_clear_button` | "Clear" (clear button text). |
| TRANS-026-06 | `gallery.tag_filter_no_results` | "No photos found matching your tag filter." (empty state message). |
| TRANS-026-07 | `gallery.tag_filter_active_summary` | "Filtered by: {tags} ({logic})" (filter summary template, e.g., "Filtered by: Ken Kelly, Frank Frazetta (OR)"). |

### Fixtures & Sample Data

Test fixtures should include:
- Albums with various access levels (public, private, shared).
- Photos with various tag combinations within albums.
- Tags with various names (for alphabetical sorting tests).
- Scenarios with photos having multiple tags.
- Albums with no tagged photos.

## Telemetry & Observability

No new telemetry events planned for initial implementation. Standard request logging captures API calls. Optional future enhancements:
- `album.tag_filter.applied` — Event when user applies tag filter (with tag count, logic type).
- `album.tag_filter.no_results` — Event when filter returns empty result set.

## Documentation Deliverables

- Update roadmap (`docs/specs/4-architecture/roadmap.md`) — update Feature 026 status.
- Update knowledge map (`docs/specs/4-architecture/knowledge-map.md`) — document new tag filter component and `Album::tags` endpoint.
- Update `_current-session.md` with planning/implementation progress.
- Optional: Add user documentation explaining how to use tag filters within albums.
- Optional: Update OpenAPI schema for extended `Album::photos` endpoint and new `Album::tags` endpoint.

## Fixtures & Sample Data

See "Fixtures & Sample Data" section under "Interface & Contract Catalogue" above.

## Spec DSL

```yaml
domain_objects:
  - id: DO-026-01
    name: GetAlbumPhotosRequest
    extended: true
    added_fields:
      - name: tag_ids
        type: array<int>
        constraints: "array of valid tag IDs"
        optional: true
      - name: tag_logic
        type: enum
        values: ["AND", "OR"]
        default: "OR"
        optional: true
  - id: DO-026-02
    name: AlbumTagsRequest
    fields:
      - name: album_id
        type: string
        constraints: "valid album ID"

routes:
  - id: API-026-01
    method: GET
    path: /api/Album::tags
    parameters:
      - name: album_id
        type: string
        location: query
    response: "{tags: [{id, name, description}]}"
    middleware: ["login_required:album"]
  - id: API-026-02
    method: GET
    path: /api/Album::photos
    extended: true
    added_parameters:
      - name: tag_ids[]
        type: array<int>
        location: query
        optional: true
      - name: tag_logic
        type: enum
        values: ["AND", "OR"]
        location: query
        optional: true
        default: "OR"
    response: PaginatedPhotosResource
    middleware: ["login_required:album", "cache_control"]

translation_keys:
  - id: TRANS-026-01
    key: gallery.tag_filter_label
    en: "Filter by tags:"
  - id: TRANS-026-02
    key: gallery.tag_filter_logic_or
    en: "OR"
  - id: TRANS-026-03
    key: gallery.tag_filter_logic_and
    en: "AND"
  - id: TRANS-026-04
    key: gallery.tag_filter_apply_button
    en: "Apply"
  - id: TRANS-026-05
    key: gallery.tag_filter_clear_button
    en: "Clear"
  - id: TRANS-026-06
    key: gallery.tag_filter_no_results
    en: "No photos found matching your tag filter."
  - id: TRANS-026-07
    key: gallery.tag_filter_active_summary
    en: "Filtered by: {tags} ({logic})"

ui_components:
  - id: UI-026-01
    name: AlbumTagFilter.vue
    description: "Tag filter component within album view with tag multi-select, logic toggle, apply/clear buttons, conditional rendering"
  - id: UI-026-02
    name: useAlbumTagFilter.ts (or inline in component)
    description: "Composable for tag filter state management (selected tags, logic, active filter status)"

scenarios:
  - S-026-01: Album with tags shows filter UI
  - S-026-02: Album without tags hides filter UI
  - S-026-03: Filter with OR logic
  - S-026-04: Filter with AND logic
  - S-026-05: Single tag filter
  - S-026-06: Multiple tags AND logic
  - S-026-07: No matching photos
  - S-026-08: Clear filter
  - S-026-09: Filter persists during pagination
  - S-026-10: Filter state not persisted across navigation
  - S-026-11: Album::tags returns distinct sorted tags
  - S-026-12: Album::tags 404 for invalid album
  - S-026-13: Album::tags 403 for private album
  - S-026-14: Album::photos with tag filters
  - S-026-15: Invalid tag IDs gracefully handled
  - S-026-16: Backward compatibility without filter params
  - S-026-17: Performance with large albums
  - S-026-18: Access control respected
```

## Appendix

### Example Use Case from Issue #4037

A user viewing their "Creepy Magazine" album wants to see only covers by artist "Ken Kelly":

1. Album loads with all photos displayed
2. Tag filter component appears (album has tagged photos)
3. User clicks tag dropdown, sees available tags: [Ken Kelly, Frank Frazetta, Richard Corben, ...]
4. User selects "Ken Kelly"
5. User clicks "Apply"
6. Photo grid updates to show only photos tagged "Ken Kelly"
7. Filter summary shows: "Filtered by: Ken Kelly"

To extend the filter to include "Frank Frazetta":

1. User opens tag dropdown (already showing Ken Kelly selected)
2. User additionally selects "Frank Frazetta"
3. User ensures logic is set to "OR" (default)
4. User clicks "Apply"
5. Photo grid updates to show photos tagged "Ken Kelly" OR "Frank Frazetta"
6. Filter summary shows: "Filtered by: Ken Kelly, Frank Frazetta (OR)"

### Performance Considerations

**Query optimization strategies:**

For **tag OR logic** with `tag_ids = [T1, T2]`:
```sql
SELECT photos.* FROM photos
INNER JOIN photo_album ON photos.id = photo_album.photo_id
INNER JOIN photos_tags ON photos.id = photos_tags.photo_id
WHERE photo_album.album_id = 'ALBUM_ID'
  AND photos_tags.tag_id IN (T1, T2)
GROUP BY photos.id
```

For **tag AND logic** with `tag_ids = [T1, T2]`:
```sql
SELECT photos.* FROM photos
INNER JOIN photo_album ON photos.id = photo_album.photo_id
INNER JOIN photos_tags ON photos.id = photos_tags.photo_id
WHERE photo_album.album_id = 'ALBUM_ID'
  AND photos_tags.tag_id IN (T1, T2)
GROUP BY photos.id
HAVING COUNT(DISTINCT photos_tags.tag_id) = 2
```

Both queries leverage existing indexes on:
- `photo_album.album_id`
- `photo_album.photo_id`
- `photos_tags.photo_id`
- `photos_tags.tag_id`

Pagination applied after filtering (via LIMIT/OFFSET).

### Implementation Notes

**Backend (PHP/Laravel):**
- New controller method: `AlbumTagsController::get()` for `/api/Album::tags`
- Extend `AlbumPhotosController::get()` to handle optional `tag_ids[]` and `tag_logic` parameters
- Add tag filtering logic to `PhotoRepository::getPhotosForAlbumPaginated()` method
- Validation in `GetAlbumPhotosRequest` for new optional parameters

**Frontend (Vue3/TypeScript):**
- New component: `AlbumTagFilter.vue` in `resources/js/components/album/`
- Integrate component into album view (below header, above photo grid)
- Conditionally render based on tag availability
- Use PrimeVue `MultiSelect` component for tag selection
- Use PrimeVue `RadioButton` or `ToggleButton` for logic selection
- API service methods in `resources/js/services/album-service.ts`:
  - `getAlbumTags(albumId: string): Promise<Tag[]>`
  - Extend `getAlbumPhotos()` to accept optional `tagIds?: number[], tagLogic?: 'AND'|'OR'`

---

*Last updated: 2026-03-09*
