# Feature 007 Tasks – Photos and Albums Pagination

_Status: Draft_
_Last updated: 2026-01-07_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Backend: Database & Configuration (I1)

- [ ] T-007-01 – Create migration for pagination config keys (FR-007-05, FR-007-06, FR-007-07, FR-007-08, NFR-007-05).
  _Intent:_ Add 5 config keys to configs table with defaults and validation.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback`
  - `php artisan test --filter=ConfigTest`
  _Notes:_ Migration file: `2026_01_07_add_pagination_config_keys.php`. Config keys: `sub_albums_per_page=30`, `photos_per_page=100`, `search_results_per_page=50`, `photos_pagination_ui_mode=infinite_scroll`, `albums_pagination_ui_mode=infinite_scroll`.

### Backend: Album Head Endpoint (I2)

- [ ] T-007-02 – Create AlbumHeadResource class (FR-007-01, API-007-01).
  _Intent:_ API resource for lightweight album metadata without children/photos arrays.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ File: `app/Http/Resources/Models/AlbumHeadResource.php`. Fields: id, title, description, num_photos, num_children, thumb, rights.

- [ ] T-007-03 – Create AlbumHeadController and route (FR-007-01, API-007-01, S-007-01).
  _Intent:_ Controller method and route for GET `/Album/{id}/head`.
  _Verification commands:_
  - `php artisan test --filter=AlbumHeadEndpointTest`
  - `make phpstan`
  _Notes:_ File: `app/Http/Controllers/Gallery/AlbumHeadController.php`. Route: `routes/api_v2.php`. Middleware: `login_required:album`, `cache_control`.

- [ ] T-007-04 – Write feature test for `/Album/{id}/head` endpoint (FR-007-01).
  _Intent:_ Test success, 404, 403, verify no children/photos in response.
  _Verification commands:_
  - `php artisan test --filter=AlbumHeadEndpointTest`
  _Notes:_ File: `tests/Feature_v2/AlbumHeadEndpointTest.php`.

### Backend: Repository Pagination Methods (I3)

- [ ] T-007-05 – Add getChildrenPaginated method to Album model (NFR-007-01, NFR-007-06).
  _Intent:_ Repository method returning paginated LengthAwarePaginator of child albums.
  _Verification commands:_
  - `php artisan test --filter=AlbumRepositoryTest`
  - `make phpstan`
  _Notes:_ Use SortingDecorator for query building and sorting.

- [ ] T-007-06 – Add getPhotosPaginated method to Album model (NFR-007-01, NFR-007-06).
  _Intent:_ Repository method returning paginated LengthAwarePaginator of photos.
  _Verification commands:_
  - `php artisan test --filter=AlbumRepositoryTest`
  - `make phpstan`
  _Notes:_ Use SortingDecorator for query building and sorting.

- [ ] T-007-07 – Write unit tests for repository pagination methods (NFR-007-01).
  _Intent:_ Test pagination with various page sizes, empty results, beyond available pages, sorting.
  _Verification commands:_
  - `php artisan test --filter=AlbumRepositoryTest`
  _Notes:_ File: `tests/Unit/Models/AlbumRepositoryTest.php`.

### Backend: Paginated Albums Endpoint (I4)

- [ ] T-007-08 – Create PaginatedAlbumsResource class (FR-007-02, API-007-02).
  _Intent:_ API resource with structure `{data: [...], current_page, last_page, per_page, total}`.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ File: `app/Http/Resources/Collections/PaginatedAlbumsResource.php`.

- [ ] T-007-09 – Create GetAlbumChildrenRequest validator (FR-007-02).
  _Intent:_ Validate `page` parameter, default to 1.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ File: `app/Http/Requests/GetAlbumChildrenRequest.php`.

- [ ] T-007-10 – Create AlbumChildrenController and route (FR-007-02, API-007-02, S-007-01, S-007-06).
  _Intent:_ Controller method and route for GET `/Album/{id}/albums?page={n}`.
  _Verification commands:_
  - `php artisan test --filter=AlbumChildrenEndpointTest`
  - `make phpstan`
  _Notes:_ File: `app/Http/Controllers/Gallery/AlbumChildrenController.php`. Route: `routes/api_v2.php`.

- [ ] T-007-11 – Write feature test for `/Album/{id}/albums` endpoint (FR-007-02, S-007-09).
  _Intent:_ Test first page, subsequent pages, beyond available data, metadata accuracy, default page=1.
  _Verification commands:_
  - `php artisan test --filter=AlbumChildrenEndpointTest`
  _Notes:_ File: `tests/Feature_v2/AlbumChildrenEndpointTest.php`.

### Backend: Paginated Photos Endpoint (I5)

- [ ] T-007-12 – Create PaginatedPhotosResource class (FR-007-03, API-007-03).
  _Intent:_ API resource with structure `{data: [...], current_page, last_page, per_page, total}`.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ File: `app/Http/Resources/Collections/PaginatedPhotosResource.php`.

- [ ] T-007-13 – Create GetAlbumPhotosRequest validator (FR-007-03).
  _Intent:_ Validate `page` parameter, default to 1.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ File: `app/Http/Requests/GetAlbumPhotosRequest.php`.

- [ ] T-007-14 – Create AlbumPhotosController and route (FR-007-03, API-007-03, S-007-01, S-007-05, S-007-12).
  _Intent:_ Controller method and route for GET `/Album/{id}/photos?page={n}`.
  _Verification commands:_
  - `php artisan test --filter=AlbumPhotosEndpointTest`
  - `make phpstan`
  _Notes:_ File: `app/Http/Controllers/Gallery/AlbumPhotosController.php`. Route: `routes/api_v2.php`.

- [ ] T-007-15 – Write feature test for `/Album/{id}/photos` endpoint (FR-007-03, NFR-007-01).
  _Intent:_ Test first page, subsequent pages, beyond available data, metadata, default page=1, performance with 500+ photos.
  _Verification commands:_
  - `php artisan test --filter=AlbumPhotosEndpointTest`
  _Notes:_ File: `tests/Feature_v2/AlbumPhotosEndpointTest.php`.

### Backend: Integration & Backward Compatibility (I6)

- [ ] T-007-16 – Write integration test for new pagination endpoints (FR-007-12, S-007-08, NFR-007-04).
  _Intent:_ Test loading album via three endpoints, data consistency, concurrent requests.
  _Verification commands:_
  - `php artisan test --filter=PaginationIntegrationTest`
  _Notes:_ File: `tests/Feature_v2/PaginationIntegrationTest.php`.

- [ ] T-007-17 – Verify backward compatibility of existing `/Album` endpoint (FR-007-12, S-007-08, NFR-007-04).
  _Intent:_ Run existing tests to ensure `/Album` endpoint unchanged.
  _Verification commands:_
  - `php artisan test --filter=AlbumControllerTest`
  - Manual test: verify legacy endpoint returns full data
  _Notes:_ Existing tests must pass without modifications.

- [ ] T-007-18 – Run full backend quality gate (NFR-007-01, NFR-007-02, NFR-007-04).
  _Intent:_ Ensure all backend code passes quality checks.
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  _Notes:_ All checks must pass before proceeding to frontend.

### Frontend: Service Layer (I7)

- [ ] T-007-19 – Add TypeScript interfaces for new API resources (NFR-007-03).
  _Intent:_ Define TypeScript types for AlbumHeadResource, PaginatedAlbumsResource, PaginatedPhotosResource.
  _Verification commands:_
  - `npm run check`
  _Notes:_ File: `resources/js/types/album.ts` (or appropriate types file).

- [ ] T-007-20 – Update album-service.ts with new endpoint methods (NFR-007-03, S-007-01).
  _Intent:_ Add getHead(), getAlbums(), getPhotos() methods with axios cache keys.
  _Verification commands:_
  - `npm run check`
  - `npm test services/album-service.test.ts`
  _Notes:_ File: `resources/js/services/album-service.ts`.

- [ ] T-007-21 – Write unit tests for new service methods.
  _Intent:_ Test service methods with mocked axios.
  _Verification commands:_
  - `npm test services/album-service.test.ts`
  _Notes:_ File: `resources/js/services/album-service.test.ts`.

### Frontend: Pinia Store Updates (I8)

- [ ] T-007-22 – Update AlbumState store with pagination state (NFR-007-03, S-007-01, S-007-02, S-007-03).
  _Intent:_ Add pagination state fields, loadHead/loadAlbums/loadPhotos actions, computed properties.
  _Verification commands:_
  - `npm run check`
  - `npm test stores/AlbumState.test.ts`
  _Notes:_ File: `resources/js/stores/AlbumState.ts`.

- [ ] T-007-23 – Write unit tests for AlbumState pagination logic.
  _Intent:_ Test store actions, state management, computed properties.
  _Verification commands:_
  - `npm test stores/AlbumState.test.ts`
  _Notes:_ File: `resources/js/stores/AlbumState.test.ts`.

### Frontend: Pagination UI Components (I9)

- [ ] T-007-24 – Create PaginationInfiniteScroll.vue component (FR-007-07, FR-007-08, UI-007-04).
  _Intent:_ Infinite scroll component with Intersection Observer.
  _Verification commands:_
  - `npm run check`
  - `npm test components/pagination/PaginationInfiniteScroll.test.ts`
  _Notes:_ File: `resources/js/components/pagination/PaginationInfiniteScroll.vue`.

- [ ] T-007-25 – Create PaginationLoadMore.vue component (FR-007-07, FR-007-08, UI-007-01, UI-007-02).
  _Intent:_ Load more button component with loading/disabled states.
  _Verification commands:_
  - `npm run check`
  - `npm test components/pagination/PaginationLoadMore.test.ts`
  _Notes:_ File: `resources/js/components/pagination/PaginationLoadMore.vue`.

- [ ] T-007-26 – Create PaginationNavigation.vue component (FR-007-07, FR-007-08, UI-007-03).
  _Intent:_ Page navigation component with prev/next and page numbers.
  _Verification commands:_
  - `npm run check`
  - `npm test components/pagination/PaginationNavigation.test.ts`
  _Notes:_ File: `resources/js/components/pagination/PaginationNavigation.vue`.

- [ ] T-007-27 – Create usePagination.ts composable (FR-007-07, FR-007-08, UI-007-05).
  _Intent:_ Shared composable for pagination logic, loading/error states.
  _Verification commands:_
  - `npm run check`
  - `npm test composables/usePagination.test.ts`
  _Notes:_ File: `resources/js/composables/usePagination.ts`.

- [ ] T-007-28 – Write component tests for all pagination components.
  _Intent:_ Test component behavior, events, props.
  _Verification commands:_
  - `npm test components/pagination/*.test.ts`
  _Notes:_ Test files in `resources/js/components/pagination/`.

### Frontend: Integration with Album View (I10)

- [ ] T-007-29 – Update AlbumView.vue to load paginated data (FR-007-09, FR-007-10, S-007-01, S-007-02, S-007-03, S-007-04, S-007-11).
  _Intent:_ Wire up pagination: load head/albums/photos, conditionally render pagination components based on UI mode.
  _Verification commands:_
  - `npm run check`
  - Manual testing with all three UI modes
  _Notes:_ File: `resources/js/views/AlbumView.vue` (or appropriate view component).

- [ ] T-007-30 – Update AlbumThumbPanelList.vue for paginated sub-albums.
  _Intent:_ Support appending vs replacing albums based on UI mode.
  _Verification commands:_
  - `npm run check`
  - Manual testing
  _Notes:_ File: `resources/js/components/gallery/albumModule/AlbumThumbPanelList.vue`.

- [ ] T-007-31 – Update PhotoThumbPanelList.vue for paginated photos.
  _Intent:_ Support appending vs replacing photos based on UI mode.
  _Verification commands:_
  - `npm run check`
  - Manual testing
  _Notes:_ File: `resources/js/components/gallery/albumModule/PhotoThumbPanelList.vue`.

- [ ] T-007-32 – Add loading and error states to album view.
  _Intent:_ Display loading skeleton, error messages, retry buttons.
  _Verification commands:_
  - `npm run check`
  - Manual testing
  _Notes:_ Test with slow network, error conditions.

- [ ] T-007-33 – Manual testing: All three UI modes functional (FR-007-07, FR-007-08, S-007-02, S-007-03, S-007-04).
  _Intent:_ Test infinite scroll, load more button, page navigation modes.
  _Verification commands:_
  - Manual testing with all UI modes
  - Test with albums containing 100+, 500+, 1000+ photos
  _Notes:_ Document any issues or performance concerns.

### Frontend: Admin Configuration UI (I11)

- [ ] T-007-34 – Add pagination config inputs to admin settings (FR-007-05, FR-007-06, FR-007-07, FR-007-08, S-007-10, S-007-11).
  _Intent:_ Add inputs for page sizes and UI mode dropdowns in admin panel.
  _Verification commands:_
  - `npm run check`
  - Manual testing: save configs, verify UI updates
  _Notes:_ Locate admin settings component and add pagination section.

- [ ] T-007-35 – Run full frontend quality gate.
  _Intent:_ Ensure all frontend code passes quality checks.
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  _Notes:_ All checks must pass.

### Backend & Frontend: Smart/Tag Album Migration (I12)

- [ ] T-007-35a – Refactor BaseSmartAlbum to remove inline pagination (FR-007-13, S-007-14, S-007-16).
  _Intent:_ Update BaseSmartAlbum class to work with new `/Album/{id}/photos` endpoint.
  _Verification commands:_
  - `php artisan test --filter=SmartAlbumTest`
  - `make phpstan`
  _Notes:_ File: `app/SmartAlbums/BaseSmartAlbum.php`. Remove `SortingDecorator::paginate()` from `getPhotosAttribute()`, keep sorting only.

- [ ] T-007-35b – Update SmartAlbumResource to remove pagination metadata (FR-007-13).
  _Intent:_ Remove `current_page`, `last_page`, `per_page`, `total` fields from SmartAlbumResource (delegated to new endpoint).
  _Verification commands:_
  - `make phpstan`
  - `php artisan test --filter=SmartAlbumTest`
  _Notes:_ File: `app/Http/Resources/Models/SmartAlbumResource.php`.

- [ ] T-007-35c – Update Tag album classes for new endpoints (FR-007-14, S-007-15).
  _Intent:_ Ensure Tag albums work with `/Album/{id}/head` and `/Album/{id}/photos` endpoints.
  _Verification commands:_
  - `php artisan test --filter=TagAlbumTest`
  - `make phpstan`
  _Notes:_ Update Tag album controllers/resources as needed.

- [ ] T-007-35d – Update frontend to use new endpoints for Smart/Tag albums (FR-007-13, FR-007-14, S-007-16).
  _Intent:_ Frontend detects Smart/Tag albums and calls new endpoints like regular albums.
  _Verification commands:_
  - `npm run check`
  - Manual testing: navigate to Recent, Starred, Tag albums
  _Notes:_ Remove special-case handling in AlbumView.vue or album-service.ts.

- [ ] T-007-35e – Write migration tests for Smart/Tag albums (FR-007-13, FR-007-14).
  _Intent:_ Test Smart albums (Recent, Starred) and Tag albums with new endpoints.
  _Verification commands:_
  - `php artisan test --filter=SmartAlbumPaginationTest`
  - `php artisan test --filter=TagAlbumPaginationTest`
  _Notes:_ New test files verifying pagination works correctly for Smart/Tag albums.

- [ ] T-007-35f – Update existing Smart/Tag album tests for new behavior.
  _Intent:_ Modify existing tests to expect new endpoint usage.
  _Verification commands:_
  - `php artisan test --filter=SmartAlbumTest`
  - `php artisan test --filter=TagAlbumTest`
  _Notes:_ Update test expectations, ensure backward compatibility preserved for legacy `/Album` endpoint.

### Testing & Performance (I13)

- [ ] T-007-36 – Create test fixtures for large albums (NFR-007-01, S-007-13, FX-007-01, FX-007-02).
  _Intent:_ Album with 500+ photos, album with 100+ children.
  _Verification commands:_
  - `php artisan test --filter=PaginationPerformanceTest`
  _Notes:_ File: `tests/Feature_v2/Fixtures/LargeAlbumFixture.php`.

- [ ] T-007-37 – Write performance test for pagination (NFR-007-01).
  _Intent:_ Measure query time for first page of 1000-photo album (target < 500ms p95).
  _Verification commands:_
  - `php artisan test --filter=PaginationPerformanceTest`
  _Notes:_ File: `tests/Feature_v2/PaginationPerformanceTest.php`.

- [ ] T-007-38 – Run full test suite (backend + frontend).
  _Intent:_ Ensure all tests pass.
  _Verification commands:_
  - `php artisan test`
  - `npm run check`
  _Notes:_ Document any failures or warnings.

### Documentation (I14)

- [ ] T-007-39 – Update knowledge-map.md with pagination documentation.
  _Intent:_ Document new endpoints, config keys, pagination flow.
  _Verification commands:_
  - N/A (manual review)
  _Notes:_ File: `docs/specs/4-architecture/knowledge-map.md`.

- [ ] T-007-40 – Create/update API documentation for new endpoints.
  _Intent:_ Document `/Album/{id}/head`, `/Album/{id}/albums`, `/Album/{id}/photos` with examples.
  _Verification commands:_
  - N/A (manual review)
  _Notes:_ Include request/response examples from spec appendix.

- [ ] T-007-41 – Add inline code comments for pagination logic.
  _Intent:_ Document repository methods, frontend composables.
  _Verification commands:_
  - N/A (manual review)
  _Notes:_ Focus on non-obvious logic and edge cases.

- [ ] T-007-42 – Update admin guide with configuration instructions.
  _Intent:_ Document how to configure pagination settings.
  _Verification commands:_
  - N/A (manual review)
  _Notes:_ Include screenshots if possible.

### Final Verification

- [ ] T-007-43 – Run all exit criteria checks.
  _Intent:_ Verify all quality gates pass, manual tests complete, documentation updated.
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  - `npm run check`
  - `npm run format`
  - Manual testing: all three UI modes
  - Manual testing: performance target met
  _Notes:_ See plan exit criteria section for full checklist.

- [ ] T-007-44 – Update roadmap status to "Complete".
  _Intent:_ Mark Feature 007 as complete in roadmap.
  _Verification commands:_
  - N/A (update file)
  _Notes:_ File: `docs/specs/4-architecture/roadmap.md`. Move Feature 007 from Active to Completed table.

## Notes / TODOs

- Consider adding database indexes on commonly sorted columns if performance testing reveals slow queries
- Monitor memory usage with infinite scroll mode to ensure it doesn't grow unbounded
- Future: Apply same pagination pattern to search results (Feature 008?)
- Future: Explore cursor-based pagination for very large albums (10000+ photos)
