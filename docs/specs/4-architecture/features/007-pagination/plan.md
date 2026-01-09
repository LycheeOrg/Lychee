# Feature Plan 007 – Photos and Albums Pagination

_Linked specification:_ `docs/specs/4-architecture/features/007-pagination/spec.md`
_Status:_ Draft
_Last updated:_ 2026-01-07

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**Vision:** Enable Lychee to efficiently handle albums with hundreds or thousands of photos by implementing pagination on both frontend and backend, improving performance, reducing memory usage, and providing a smooth user experience through configurable UI modes.

**Success Criteria:**
- Albums with 1000+ photos load first page in < 500ms (p95)
- Existing `/Album` endpoint remains unchanged (backward compatibility validated by passing tests)
- New endpoints (`/Album/{id}/head`, `/Album/{id}/albums`, `/Album/{id}/photos`) functional with pagination
- Frontend supports three UI modes: infinite scroll (default), load more button, page navigation
- Admin can configure page sizes and UI modes via config table
- Zero breaking changes to existing API clients

## Scope Alignment

**In scope:**
- New REST API endpoints: `/Album/{id}/head`, `/Album/{id}/albums`, `/Album/{id}/photos`
- Pagination config keys: `sub_albums_per_page`, `photos_per_page`, `search_results_per_page`
- UI mode config keys: `photos_pagination_ui_mode`, `albums_pagination_ui_mode`
- Frontend pagination state management in Pinia stores
- Frontend UI components for three pagination modes
- Repository methods for paginated queries
- Migration for new config keys with default values
- API resources: `HeadAlbumResource`, `PaginatedPhotosResource`, `PaginatedAlbumsResource`
- Feature tests for new endpoints

**Out of scope:**
- Modifications to existing `/Album` endpoint
- Smart albums pagination (already implemented)
- Search results pagination (separate feature)
- Timeline pagination changes
- Cursor-based pagination
- Extracting data fetching into separate service classes
- Album sorting changes

## Dependencies & Interfaces

**Backend:**
- Laravel pagination (`LengthAwarePaginator`)
- Existing `SortingDecorator` for query building
- Repository pattern (`Album`, `Photo` models)
- Config table for settings storage
- Existing `AlbumResource`, `PhotoResource` structures

**Frontend:**
- Pinia stores: `AlbumState`, `PhotosState`
- Vue 3 components: Album grid, photo grid
- `album-service.ts` for API calls
- Axios for HTTP requests with caching

**Interfaces:**
- Must maintain compatibility with existing  `AlbumResource` structure
- Must follow existing API versioning (`/api/v2`)
- Must align with existing auth/permissions middleware patterns

## Assumptions & Risks

**Assumptions:**
- SortingDecorator pagination logic is stable and tested
- Config table schema supports string enum values for UI modes
- Existing tests for `/Album` endpoint comprehensive enough to validate backward compatibility
- Frontend can handle three different pagination UI modes without major refactoring

**Risks / Mitigations:**
- **Risk:** Code duplication in new endpoints may become maintenance burden
  - **Mitigation:** Accept duplication per user directive (Q-007-05), use repository pattern methods to share query logic
- **Risk:** Infinite scroll mode may accumulate too much memory with many pages
  - **Mitigation:** Document recommended limits, consider adding max-pages-loaded threshold in future
- **Risk:** Pagination may not perform well with complex album queries
  - **Mitigation:** Use existing SortingDecorator patterns, add database indexes if needed, performance test with 1000+ photo albums
- **Risk:** Frontend state management complexity with multiple UI modes
  - **Mitigation:** Use composition API and reusable composables for pagination logic

## Implementation Drift Gate

**Gate execution:**
1. Run `make phpstan` - must pass with zero errors
2. Run `php artisan test` - all existing tests must pass, new tests must achieve >80% coverage
3. Run `npm run check` - frontend tests and type checking must pass
4. Manual testing: Load album with 1000+ photos, verify first page loads < 500ms
5. Manual testing: Test all three UI modes (infinite scroll, load more, page navigation)
6. API contract validation: Verify response structures match spec examples
7. Backward compatibility: Verify legacy `/Album` endpoint unchanged via existing tests

**Evidence recording:**
- Test output logged in feature tasks checklist
- Performance measurements recorded in plan follow-ups
- API response examples captured in spec appendix

## Increment Map

### I1 – Database Migration & Config Keys
_Goal:_ Add pagination configuration keys to config table with validation and defaults.

_Preconditions:_ Config table exists and supports string/enum types.

_Steps:_
1. Create migration: `2026_01_07_add_pagination_config_keys`
2. Add config keys:
   - `sub_albums_per_page` (integer, default 30, range 1-1000)
   - `photos_per_page` (integer, default 100, range 1-1000)
   - `search_results_per_page` (integer, default 50, range 1-1000)
   - `photos_pagination_ui_mode` (enum: infinite_scroll, load_more_button, page_navigation, default: infinite_scroll)
   - `albums_pagination_ui_mode` (enum: same as above, default: infinite_scroll)
3. Add validation rules for config keys in relevant validators
4. Write migration up() and down() methods

_Commands:_
- `php artisan migrate`
- `php artisan migrate:rollback`
- `php artisan test --filter=ConfigTest`

_Exit:_ Migration runs successfully, config keys exist with default values, rollback works.

_Maps to:_ FR-007-05, FR-007-06, FR-007-07, FR-007-08, NFR-007-05

---

### I2 – HeadAlbumResource & `/Album/{id}/head` Endpoint
_Goal:_ Create lightweight endpoint that returns album metadata without children/photos.

_Preconditions:_ I1 complete.

_Steps:_
1. Create `HeadAlbumResource` API resource class
   - Fields: id, title, description, num_photos, num_children, thumb, rights
   - No `albums` or `photos` arrays
2. Create `AlbumHeadController@get` method
   - Load album with only necessary relations (thumb, rights)
   - Return `HeadAlbumResource`
3. Add route: `GET /Album/{id}/head` in `routes/api_v2.php`
4. Add middleware: `login_required:album`, `cache_control`
5. Write feature test: `AlbumHeadEndpointTest`
   - Test successful retrieval
   - Test 404 for non-existent album
   - Test 403 for unauthorized access
   - Verify no children/photos in response

_Commands:_
- `php artisan test --filter=AlbumHeadEndpointTest`
- `make phpstan`

_Exit:_ `/Album/{id}/head` returns lightweight album metadata, tests pass.

_Maps to:_ FR-007-01, API-007-01, S-007-01

---

### I3 – Repository Methods for Paginated Queries
_Goal:_ Add repository methods to fetch paginated children and photos using SortingDecorator.

_Preconditions:_ I1 complete.

_Steps:_
1. Add method to Album model/repository: `getChildrenPaginated($perPage, $page)`
   - Use `SortingDecorator` with album's children query
   - Apply default album sorting from config
   - Return `LengthAwarePaginator`
2. Add method to Album model/repository: `getPhotosPaginated($perPage, $page)`
   - Use `SortingDecorator` with album's photos query
   - Apply default photo sorting from config
   - Return `LengthAwarePaginator`
3. Write unit tests for repository methods
   - Test pagination with various page sizes
   - Test pagination with empty results
   - Test pagination beyond available pages
   - Test sorting is applied correctly

_Commands:_
- `php artisan test --filter=AlbumRepositoryTest`
- `make phpstan`

_Exit:_ Repository methods return correct paginated results with metadata.

_Maps to:_ NFR-007-01, NFR-007-06

---

### I4 – PaginatedAlbumsResource & `/Album/{id}/albums` Endpoint
_Goal:_ Create endpoint for fetching paginated sub-albums.

_Preconditions:_ I3 complete.

_Steps:_
1. Create `PaginatedAlbumsResource` API resource class
   - Structure: `{data: [...], current_page, last_page, per_page, total}`
2. Create `AlbumChildrenController@get` method
   - Load album
   - Get `sub_albums_per_page` from config
   - Get `page` from request, default to 1
   - Call `$album->getChildrenPaginated($perPage, $page)`
   - Return `PaginatedAlbumsResource`
3. Add route: `GET /Album/{id}/albums?page={n}` in `routes/api_v2.php`
4. Add middleware: `login_required:album`, `cache_control`
5. Create request validator: `GetAlbumChildrenRequest`
   - Validate `page` is positive integer
   - Default page to 1
6. Write feature test: `AlbumChildrenEndpointTest`
   - Test first page retrieval
   - Test subsequent pages
   - Test page beyond available data (empty results)
   - Test pagination metadata accuracy
   - Test default page=1 behavior

_Commands:_
- `php artisan test --filter=AlbumChildrenEndpointTest`
- `make phpstan`

_Exit:_ `/Album/{id}/albums` returns paginated sub-albums, tests pass.

_Maps to:_ FR-007-02, API-007-02, S-007-01, S-007-06

---

### I5 – PaginatedPhotosResource & `/Album/{id}/photos` Endpoint
_Goal:_ Create endpoint for fetching paginated photos.

_Preconditions:_ I3 complete.

_Steps:_
1. Create `PaginatedPhotosResource` API resource class
   - Structure: `{data: [...], current_page, last_page, per_page, total}`
2. Create `AlbumPhotosController@get` method
   - Load album
   - Get `photos_per_page` from config
   - Get `page` from request, default to 1
   - Call `$album->getPhotosPaginated($perPage, $page)`
   - Return `PaginatedPhotosResource`
3. Add route: `GET /Album/{id}/photos?page={n}` in `routes/api_v2.php`
4. Add middleware: `login_required:album`, `cache_control`
5. Create request validator: `GetAlbumPhotosRequest`
   - Validate `page` is positive integer
   - Default page to 1
6. Write feature test: `AlbumPhotosEndpointTest`
   - Test first page retrieval
   - Test subsequent pages
   - Test page beyond available data
   - Test pagination metadata accuracy
   - Test default page=1 behavior
   - Test with album containing 500+ photos (performance test)

_Commands:_
- `php artisan test --filter=AlbumPhotosEndpointTest`
- `make phpstan`

_Exit:_ `/Album/{id}/photos` returns paginated photos, tests pass, performance acceptable.

_Maps to:_ FR-007-03, API-007-03, S-007-01, S-007-05, S-007-12, NFR-007-01

---

### I6 – Backend Integration Test & Backward Compatibility Validation
_Goal:_ Ensure new endpoints work together and existing `/Album` endpoint unchanged.

_Preconditions:_ I2, I4, I5 complete.

_Steps:_
1. Write integration test: `PaginationIntegrationTest`
   - Test loading album via three endpoints (`/head`, `/albums`, `/photos`)
   - Verify data consistency across endpoints
   - Test concurrent requests to same album
2. Run existing `/Album` endpoint tests to verify backward compatibility
3. Manually test legacy endpoint returns full data (no pagination)
4. Document API response examples in spec appendix

_Commands:_
- `php artisan test --filter=PaginationIntegrationTest`
- `php artisan test --filter=AlbumControllerTest` (existing tests)
- `make phpstan`

_Exit:_ All backend tests pass, backward compatibility confirmed.

_Maps to:_ FR-007-12, S-007-08, NFR-007-04

---

### I7 – Frontend Service Layer Updates
_Goal:_ Update album-service.ts with new endpoint methods.

_Preconditions:_ I2, I4, I5 complete (backend endpoints functional).

_Steps:_
1. Add to `album-service.ts`:
   - `getHead(albumId)` → GET `/Album/{id}/head`
   - `getAlbums(albumId, page)` → GET `/Album/{id}/albums?page={page}`
   - `getPhotos(albumId, page)` → GET `/Album/{id}/photos?page={page}`
2. Update axios cache keys to include page numbers
3. Add TypeScript interfaces for new response structures:
   - `HeadAlbumResource`
   - `PaginatedAlbumsResource`
   - `PaginatedPhotosResource`
4. Write unit tests for service methods (mock axios)

_Commands:_
- `npm run check`
- `npm test services/album-service.test.ts`

_Exit:_ Service methods functional, type-safe, tests pass.

_Maps to:_ NFR-007-03, S-007-01

---

### I8 – Pinia Store Updates for Pagination State
_Goal:_ Update AlbumState and PhotosState stores to manage pagination.

_Preconditions:_ I7 complete.

_Steps:_
1. Update `AlbumState` store:
   - Add state: `albums_current_page`, `albums_last_page`, `albums_per_page`, `albums_total`
   - Add state: `photos_current_page`, `photos_last_page`, `photos_per_page`, `photos_total`
   - Add state: `albums_ui_mode`, `photos_ui_mode` (from config)
   - Add action: `loadHead(albumId)` → call service.getHead()
   - Update action: `loadAlbums(albumId, page)` → call service.getAlbums(), append or replace data based on UI mode
   - Update action: `loadPhotos(albumId, page)` → call service.getPhotos(), append or replace data based on UI mode
   - Add computed: `hasMoreAlbums`, `hasMorePhotos`
2. Write unit tests for store actions and state management

_Commands:_
- `npm run check`
- `npm test stores/AlbumState.test.ts`

_Exit:_ Stores manage pagination state correctly, tests pass.

_Maps to:_ NFR-007-03, S-007-01, S-007-02, S-007-03

---

### I9 – Frontend Pagination UI Components
_Goal:_ Create reusable pagination UI components for three modes.

_Preconditions:_ I8 complete.

_Steps:_
1. Create `PaginationInfiniteScroll.vue` component
   - Use Intersection Observer to detect scroll to bottom
   - Emit `loadMore` event when threshold reached
   - Show loading skeleton during fetch
2. Create `PaginationLoadMore.vue` component
   - Render "Load More (N remaining)" button
   - Disable button during loading
   - Hide button when last page reached
3. Create `PaginationNavigation.vue` component
   - Render prev/next buttons and page numbers
   - Highlight current page
   - Emit `pageChange` event
4. Create composable: `usePagination.ts`
   - Shared logic for all three modes
   - Handle loading states, error states
5. Write component tests

_Commands:_
- `npm run check`
- `npm test components/pagination/*.test.ts`

_Exit:_ Pagination components functional, tests pass.

_Maps to:_ FR-007-07, FR-007-08, UI-007-01 through UI-007-05

---

### I10 – Integrate Pagination Components into Album View
_Goal:_ Wire up pagination components to album/photo grids.

_Preconditions:_ I9 complete.

_Steps:_
1. Update `AlbumView.vue`:
   - Load config for UI modes on mount
   - Call `albumState.loadHead()`, `albumState.loadAlbums(id, 1)`, `albumState.loadPhotos(id, 1)` on album open
   - Conditionally render pagination component based on `albums_ui_mode` config
   - Conditionally render pagination component based on `photos_ui_mode` config
   - Handle pagination events (loadMore, pageChange)
2. Update `AlbumThumbPanelList.vue` (sub-albums grid):
   - Support appending vs replacing data based on UI mode
3. Update `PhotoThumbPanelList.vue` (photos grid):
   - Support appending vs replacing data based on UI mode
4. Add loading states and error handling
5. Manual testing: Test all three UI modes

_Commands:_
- `npm run check`
- `npm run dev` (manual testing)

_Exit:_ Album view loads paginated data, all three UI modes functional.

_Maps to:_ S-007-01, S-007-02, S-007-03, S-007-04, S-007-11

---

### I11 – Admin UI for Pagination Configuration
_Goal:_ Add admin UI to configure pagination settings.

_Preconditions:_ I1 complete (config keys exist).

_Steps:_
1. Locate admin settings page/component
2. Add input fields for page size configs:
   - `sub_albums_per_page` (number input, 1-1000)
   - `photos_per_page` (number input, 1-1000)
   - `search_results_per_page` (number input, 1-1000)
3. Add dropdown selects for UI mode configs:
   - `photos_pagination_ui_mode` (infinite_scroll, load_more_button, page_navigation)
   - `albums_pagination_ui_mode` (same options)
4. Wire up save functionality to update configs
5. Add validation and error messages
6. Manual testing: Change configs, verify UI updates

_Commands:_
- `npm run check`
- Manual testing in admin panel

_Exit:_ Admin can configure pagination settings via UI.

_Maps to:_ FR-007-05, FR-007-06, FR-007-07, FR-007-08, S-007-10, S-007-11

---

### I12 – Migrate Smart Albums and Tag Albums to New Endpoints
_Goal:_ Refactor Smart albums and Tag albums to use new `/Album/{id}/photos` endpoint.

_Preconditions:_ I5 complete (photos endpoint functional), I10 complete (frontend integration done).

_Steps:_
1. **Backend refactoring:**
   - Update `BaseSmartAlbum` class to remove inline pagination from `getPhotosAttribute()`
   - Ensure Smart albums work with `/Album/{id}/head` and `/Album/{id}/photos` endpoints
   - Update `SmartAlbumResource` to remove pagination metadata fields (delegate to new endpoint)
   - Update Tag album controllers/resources similarly
2. **Remove old pagination logic:**
   - Remove `SortingDecorator::paginate()` calls from Smart album photo loading
   - Keep sorting logic but delegate pagination to endpoint
3. **Frontend updates:**
   - Update frontend to detect Smart albums and Tag albums
   - Ensure they call `/Album/{id}/head` and `/Album/{id}/photos` like regular albums
   - Remove special-case handling for Smart album inline pagination
4. **Write migration tests:**
   - Test Smart album "Recent" with new endpoints
   - Test Smart album "Starred" with new endpoints
   - Test Tag albums with new endpoints
   - Verify pagination works correctly
   - Verify backward compatibility: existing `/Album` endpoint still works for Smart albums
5. **Update existing tests:**
   - Modify Smart album tests to expect new endpoint usage
   - Ensure existing functionality preserved

_Commands:_
- `php artisan test --filter=SmartAlbumTest`
- `php artisan test --filter=TagAlbumTest`
- `npm run check`
- `make phpstan`

_Exit:_ Smart albums and Tag albums use new paginated endpoints, all tests pass.

_Maps to:_ FR-007-13, FR-007-14, S-007-14, S-007-15, S-007-16

---

### I13 – Test Fixtures & Performance Testing
_Goal:_ Create large test albums and validate performance.

_Preconditions:_ All increments I1-I12 complete.

_Steps:_
1. Create test fixture: `tests/Feature_v2/Fixtures/LargeAlbumFixture.php`
   - Album with 500+ photos
   - Album with 100+ child albums
2. Write performance test: `PaginationPerformanceTest`
   - Load first page of album with 1000 photos
   - Measure query execution time (target < 500ms p95)
   - Test memory usage doesn't grow excessively with multiple pages
3. Run full test suite
4. Run phpstan with strictest settings
5. Run frontend tests and type checking

_Commands:_
- `php artisan test`
- `make phpstan`
- `npm run check`
- `php artisan test --filter=PaginationPerformanceTest`

_Exit:_ All tests pass, performance acceptable, fixtures available.

_Maps to:_ NFR-007-01, S-007-13, FX-007-01, FX-007-02

---

### I14 – Documentation & Knowledge Map Updates
_Goal:_ Document new endpoints, config keys, and pagination patterns.

_Preconditions:_ All implementation increments complete.

_Steps:_
1. Update `docs/specs/4-architecture/knowledge-map.md`:
   - Add entry for pagination flow
   - Document new API endpoints
   - Document config keys and defaults
2. Create API documentation (if separate docs exist):
   - Document `/Album/{id}/head`
   - Document `/Album/{id}/albums`
   - Document `/Album/{id}/photos`
   - Include request/response examples
3. Add inline code comments:
   - Repository pagination methods
   - Frontend pagination composables
4. Update admin guide (if exists) with configuration instructions

_Commands:_
- N/A (documentation review)

_Exit:_ Documentation complete and accurate.

_Maps to:_ Documentation Deliverables section in spec

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-007-01 | I2, I4, I5, I7, I8, I10 | Album open with 3 API calls (head, albums, photos) |
| S-007-02 | I9, I10 | Load more button click appends photos |
| S-007-03 | I9, I10 | Page navigation replaces photos |
| S-007-04 | I9, I10 | Infinite scroll auto-fetches on scroll |
| S-007-05 | I5 | Album with < page size shows all, no pagination UI |
| S-007-06 | I4 | Album with 150 sub-albums paginated |
| S-007-07 | I6 | Legacy `/Album` endpoint backward compat validated |
| S-007-08 | I6 | Legacy `/Album` endpoint backward compat validated |
| S-007-09 | I4, I5 | New endpoints default page=1 |
| S-007-10 | I11 | Admin configures page sizes |
| S-007-11 | I11 | Admin configures UI modes |
| S-007-12 | I5 | Page beyond data returns empty with correct metadata |
| S-007-13 | I6, I13 | Concurrent users test |
| S-007-14 | I12 | Smart album uses new paginated endpoint |
| S-007-15 | I12 | Tag album uses new paginated endpoint |
| S-007-16 | I12 | Smart album frontend integration with new endpoints |

## Analysis Gate

**Completion criteria:**
- All open questions resolved (Q-007-01 through Q-007-06) ✅
- Spec reviewed and approved by user ✅
- Plan increments map to all functional requirements
- Test strategy covers all scenarios
- Dependencies identified and available

**Status:** ✅ PASSED (2026-01-07)

## Exit Criteria

- [ ] All increments I1-I14 completed and verified
- [ ] All backend tests pass: `php artisan test`
- [ ] PHPStan passes: `make phpstan`
- [ ] PHP-CS-Fixer passes: `vendor/bin/php-cs-fixer fix`
- [ ] All frontend tests pass: `npm run check`
- [ ] Frontend formatting passes: `npm run format`
- [ ] Manual testing: All three UI modes functional
- [ ] Manual testing: Performance target met (< 500ms first page load)
- [ ] Backward compatibility: Existing `/Album` endpoint tests pass
- [ ] Documentation updated
- [ ] Knowledge map updated
- [ ] Roadmap updated to "Complete" status
- [ ] Tasks checklist 100% complete

## Follow-ups / Backlog

- **Search results pagination:** Apply same pagination pattern to search endpoints (Feature 008?)
- **Monitoring:** Add metrics for pagination query performance (p95, p99 latency)
- **Optimization:** Consider database indexes on frequently sorted columns if performance degrades
- **Infinite scroll limits:** Consider max pages loaded threshold to prevent memory issues
- **Cursor pagination exploration:** Evaluate cursor-based pagination for very large albums (10000+ photos)
- **Album cover optimization:** Paginated albums may need cover selection optimization
