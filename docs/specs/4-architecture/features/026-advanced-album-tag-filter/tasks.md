# Feature Tasks 026 – Album Photo Tag Filter

_Linked plan:_ [plan.md](plan.md)  
_Linked spec:_ [spec.md](spec.md)  
_Status:_ In Progress (I1-I2 complete, starting I3)  
_Last updated:_ 2026-03-09

> Guardrail: Each task should complete in ≤90 minutes. Mark tasks `[x]` immediately after completion and commit. Tests come before implementation. Reference scenario IDs (S-026-XX) and requirement IDs (FR-026-XX, NFR-026-XX) from the spec.

---

## I1 – Album::tags Endpoint (Backend)

### Task 1.1: Write AlbumTagsControllerTest (S-026-11, S-026-12, S-026-13, S-026-19, S-026-20)
- [x] Create `tests/Feature_v2/AlbumTagsControllerTest.php`
- [x] Extend `BaseApiWithDataTest` base class
- [x] Test scenario S-026-11: Album with tagged photos returns distinct sorted tags
- [x] Test scenario S-026-12: Non-existent album returns 404
- [x] Test scenario S-026-13: Private album without access returns 403
- [x] Test scenario: Album with no tagged photos returns empty array `[]`
- [x] Test scenario S-026-19: TagAlbum returns tags from photos in that TagAlbum
- [x] Test scenario S-026-20: Smart Album returns tags from photos in computed photo set
- [x] Verify tests fail (endpoint doesn't exist yet)

**Duration:** 60 min  
**Dependencies:** None  
**Verification:** `php artisan test --filter=AlbumTagsControllerTest` (expect failures)

---

### Task 1.2: Create AlbumTagsRequest validator
- [x] Create `app/Http/Requests/Album/AlbumTagsRequest.php`
- [x] Add license header and single blank line after opening PHP tag
- [x] Extend `BaseApiRequest`
- [x] Add validation rules: `album_id` required string
- [x] Add `authorize()` method (return true, middleware handles auth)
- [x] Add accessor method: `albumId(): string`
- [x] Apply PSR-4 conventions, snake_case variables, strict comparison

**Duration:** 20 min  
**Dependencies:** Task 1.1 complete  
**Verification:** `make phpstan` (0 errors)

---

### Task 1.3: Create AlbumTagsController
- [x] Create `app/Http/Controllers/Gallery/AlbumTagsController.php`
- [x] Add license header and single blank line after opening PHP tag
- [x] Add `get(AlbumTagsRequest $request)` method
- [x] Fetch album by ID (handle Album, TagAlbum, SmartAlbum types)
- [x] Query: SELECT DISTINCT tags via photo_album → photos_tags → tags relationships
- [x] Order tags by `tags.name` ASC (alphabetically)
- [x] Return JSON: `{tags: [{id, name, description}]}`
- [x] Handle edge case: album with no tagged photos returns `{tags: []}`

**Duration:** 45 min  
**Dependencies:** Task 1.2 complete  
**Verification:** `php artisan test --filter=AlbumTagsControllerTest` (tests pass)

---

### Task 1.4: Add Album::tags route
- [x] Open `routes/api_v2.php`
- [x] Add route: `Route::get('/Album::tags', [Gallery\AlbumTagsController::class, 'get'])->middleware(['login_required:album', 'cache_control']);`
- [x] Verify route registered: `php artisan route:list | grep Album::tags`

**Duration:** 10 min  
**Dependencies:** Task 1.3 complete  
**Verification:** `php artisan route:list | grep "Album::tags"`

---

### Task 1.5: Run quality checks for I1
- [x] Run `make phpstan` (0 errors)
- [x] Run `vendor/bin/php-cs-fixer fix app/Http/Controllers/Gallery/AlbumTagsController.php`
- [x] Run `vendor/bin/php-cs-fixer fix app/Http/Requests/Album/AlbumTagsRequest.php`
- [x] Run `php artisan test --filter=AlbumTagsControllerTest` (all pass)
- [x] Verify FR-026-01 fully implemented

**Duration:** 15 min  
**Dependencies:** Tasks 1.1-1.4 complete  
**Verification:** All commands pass with 0 errors

---

## I2 – Extend GetAlbumPhotosRequest (Backend)

### Task 2.1: Write GetAlbumPhotosRequest validation tests
- [x] Create or extend `tests/Unit/Requests/Album/GetAlbumPhotosRequestTest.php`
- [x] Extend `AbstractTestCase` base class
- [x] Test: Valid `tag_ids[]` array is accepted
- [x] Test: Valid `tag_logic` enum ("AND", "OR") is accepted
- [x] Test: Empty `tag_ids[]` is treated as no filter
- [x] Test: Invalid `tag_logic` value is rejected
- [x] Test: All tag IDs invalid returns validation error 422 (Q-026-05)
- [x] Verify tests fail (validation rules don't exist yet)

**Duration:** 45 min  
**Dependencies:** I1 complete  
**Verification:** `php artisan test --filter=GetAlbumPhotosRequestTest` (expect failures)

---

### Task 2.2: Extend GetAlbumPhotosRequest with tag filter validation
- [x] Open `app/Http/Requests/Album/GetAlbumPhotosRequest.php`
- [x] Add validation rules:
  ```php
  'tag_ids' => ['sometimes', 'array'],
  'tag_ids.*' => ['integer'],
  'tag_logic' => ['sometimes', 'string', 'in:AND,OR'],
  ```
- [x] Add accessor methods: `tagIds(): array` (return empty array if not set), `tagLogic(): string` (default "OR")
- [x] Add custom validation method `withValidator()` to check if ALL tag IDs are invalid
- [x] If all tag IDs invalid, throw ValidationException with message "No valid tags found for filtering"
- [x] Individual invalid tag IDs are silently ignored (array_filter valid IDs)

**Duration:** 60 min  
**Dependencies:** Task 2.1 complete  
**Verification:** `php artisan test --filter=GetAlbumPhotosRequestTest` (all pass)

---

### Task 2.3: Process validated tag filter values
- [x] In `GetAlbumPhotosRequest`, add logic to `processValidatedValues()` or accessor methods
- [x] Filter out invalid tag IDs from `tag_ids[]` array
- [x] Store validated values in class properties or return via accessors
- [x] Ensure strict comparison (`===`) used throughout

**Duration:** 20 min  
**Dependencies:** Task 2.2 complete  
**Verification:** `make phpstan` (0 errors)

---

### Task 2.4: Run quality checks for I2
- [x] Run `make phpstan` (0 errors)
- [x] Run `vendor/bin/php-cs-fixer fix app/Http/Requests/Album/GetAlbumPhotosRequest.php`
- [x] Run `php artisan test --filter=GetAlbumPhotosRequestTest` (all pass)
- [x] Verify FR-026-02 validation path implemented

**Duration:** 10 min  
**Dependencies:** Tasks 2.1-2.3 complete  
**Verification:** All commands pass with 0 errors

---

## I3 – PhotoRepository Tag Filtering Logic (Backend)

### Task 3.1: Write PhotoRepository tag filter unit tests
- [ ] Create or extend `tests/Unit/Repositories/PhotoRepositoryTest.php`
- [ ] Extend `AbstractTestCase` base class
- [ ] Mock Photo and Tag models, set up test data
- [ ] Test S-026-03: OR logic with 2 tags returns photos with T1 OR T2
- [ ] Test S-026-04: AND logic with 2 tags returns photos with T1 AND T2
- [ ] Test S-026-05: Single tag filter (logic irrelevant)
- [ ] Test S-026-06: AND logic with 3 tags returns intersection (T1 ∩ T2 ∩ T3)
- [ ] Test S-026-07: No matching photos returns empty paginator
- [ ] Test S-026-15: Invalid tag IDs silently ignored (graceful handling)
- [ ] Verify tests fail (repository method doesn't support filtering yet)

**Duration:** 75 min  
**Dependencies:** I2 complete  
**Verification:** `php artisan test --filter=PhotoRepositoryTest` (expect failures)

---

### Task 3.2: Extend PhotoRepository with tag filtering for OR logic
- [ ] Open `app/Repositories/PhotoRepository.php`
- [ ] Extend `getPhotosForAlbumPaginated()` signature: add `?array $tag_ids = null, string $tag_logic = 'OR'` parameters
- [ ] Handle empty `$tag_ids`: skip tag filtering (existing behavior)
- [ ] Implement OR logic: `->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tag_ids))`
- [ ] Ensure query uses existing indexes on `photos_tags` table
- [ ] Apply strict comparison (`===`), no `empty()` usage

**Duration:** 30 min  
**Dependencies:** Task 3.1 complete  
**Verification:** Run OR logic tests only

---

### Task 3.3: Extend PhotoRepository with tag filtering for AND logic
- [ ] In `getPhotosForAlbumPaginated()`, add conditional for `$tag_logic === 'AND'`
- [ ] Implement AND logic query:
  ```php
  ->join('photos_tags as pt', 'photos.id', '=', 'pt.photo_id')
  ->whereIn('pt.tag_id', $tag_ids)
  ->groupBy('photos.id')
  ->havingRaw('COUNT(DISTINCT pt.tag_id) = ?', [count($tag_ids)])
  ```
- [ ] Handle single tag ID: treat same as OR logic
- [ ] Ensure no duplicate photos returned (use DISTINCT or groupBy correctly)

**Duration:** 45 min  
**Dependencies:** Task 3.2 complete  
**Verification:** `php artisan test --filter=PhotoRepositoryTest` (all pass)

---

### Task 3.4: Performance test tag filtering queries
- [ ] Create test album with 1000 photos and 10 unique tags
- [ ] Manually benchmark OR query: measure execution time (target ≤100ms)
- [ ] Manually benchmark AND query: measure execution time (target ≤100ms)
- [ ] Verify existing indexes on `photos_tags.photo_id`, `photos_tags.tag_id` are used
- [ ] Document performance results in task notes

**Duration:** 30 min  
**Dependencies:** Task 3.3 complete  
**Verification:** Performance ≤100ms p95 (NFR-026-01)

---

### Task 3.5: Run quality checks for I3
- [ ] Run `make phpstan` (0 errors)
- [ ] Run `vendor/bin/php-cs-fixer fix app/Repositories/PhotoRepository.php`
- [ ] Run `php artisan test --filter=PhotoRepositoryTest` (all pass)
- [ ] Verify FR-026-03, FR-026-04, NFR-026-01 implemented

**Duration:** 15 min  
**Dependencies:** Tasks 3.1-3.4 complete  
**Verification:** All commands pass with 0 errors

---

## I4 – Wire Album::photos with Tag Filtering (Backend)

### Task 4.1: Write AlbumPhotosFilterTest feature tests
- [ ] Create `tests/Feature_v2/AlbumPhotosFilterTest.php`
- [ ] Extend `BaseApiWithDataTest` base class
- [ ] Test S-026-14: GET `/Album::photos?album_id=A&tag_ids[]=1&tag_ids[]=2&tag_logic=OR` returns filtered photos
- [ ] Test S-026-16: Backward compatibility - no tag params returns all photos
- [ ] Test S-026-09: Filter persists across pagination (page 2 with tag filter)
- [ ] Test S-026-18: User with read-only access can apply filter (respects album access)
- [ ] Test S-026-19: TagAlbum with additional tag filter (filters within TagAlbum)
- [ ] Test S-026-20: Smart Album with tag filter (filters computed photo set)
- [ ] Verify tests fail (controller doesn't pass params to repository yet)

**Duration:** 75 min  
**Dependencies:** I3 complete  
**Verification:** `php artisan test --filter=AlbumPhotosFilterTest` (expect failures)

---

### Task 4.2: Wire tag filter params in AlbumPhotosController
- [ ] Open `app/Http/Controllers/Gallery/AlbumPhotosController.php`
- [ ] In `get()` method, extract tag filter params from request:
  ```php
  $tag_ids = $request->tagIds();
  $tag_logic = $request->tagLogic();
  ```
- [ ] Pass `$tag_ids` and `$tag_logic` to `PhotoRepository::getPhotosForAlbumPaginated()` for ALL album types (Album, TagAlbum, SmartAlbum)
- [ ] Ensure call site updates for all album type branches
- [ ] Apply PSR-4 conventions, snake_case variables

**Duration:** 30 min  
**Dependencies:** Task 4.1 complete  
**Verification:** `php artisan test --filter=AlbumPhotosFilterTest` (tests pass)

---

### Task 4.3: Verify backward compatibility
- [ ] Test: Call `/Album::photos?album_id=X` without tag parameters
- [ ] Verify: Returns all photos (existing behavior unchanged)
- [ ] Test: Pagination without tag filter works as before
- [ ] Test: Sorting without tag filter works as before

**Duration:** 20 min  
**Dependencies:** Task 4.2 complete  
**Verification:** Test S-026-16 passes

---

### Task 4.4: Run quality checks for I4
- [ ] Run `make phpstan` (0 errors)
- [ ] Run `vendor/bin/php-cs-fixer fix app/Http/Controllers/Gallery/AlbumPhotosController.php`
- [ ] Run `php artisan test --filter=AlbumPhotosFilterTest` (all pass)
- [ ] Verify FR-026-02, FR-026-05, NFR-026-04, NFR-026-05 implemented

**Duration:** 15 min  
**Dependencies:** Tasks 4.1-4.3 complete  
**Verification:** All commands pass with 0 errors

---

## I5 – Translation Keys (Backend)

### Task 5.1: Add English translation keys
- [ ] Open `lang/en/gallery.php`
- [ ] Add 7 new translation keys:
  ```php
  'tag_filter_label' => 'Filter by tags:',
  'tag_filter_logic_or' => 'OR',
  'tag_filter_logic_and' => 'AND',
  'tag_filter_apply_button' => 'Apply',
  'tag_filter_clear_button' => 'Clear',
  'tag_filter_no_results' => 'No photos found matching your tag filter.',
  'tag_filter_active_summary' => 'Filtered by: :tags (:logic)',
  ```
- [ ] Maintain alphabetical or logical ordering within file

**Duration:** 15 min  
**Dependencies:** None (can run in parallel with I1-I4)  
**Verification:** Manual review of `lang/en/gallery.php`

---

### Task 5.2: Replicate translation keys to all 21 other languages
- [ ] Copy 7 keys from `lang/en/gallery.php` to:
  - `lang/ar/gallery.php`
  - `lang/bg/gallery.php`
  - `lang/cz/gallery.php`
  - `lang/de/gallery.php`
  - `lang/el/gallery.php`
  - `lang/es/gallery.php`
  - `lang/fa/gallery.php`
  - `lang/fr/gallery.php`
  - `lang/hu/gallery.php`
  - `lang/it/gallery.php`
  - `lang/ja/gallery.php`
  - `lang/nl/gallery.php`
  - `lang/no/gallery.php`
  - `lang/pl/gallery.php`
  - `lang/pt/gallery.php`
  - `lang/ru/gallery.php`
  - `lang/sk/gallery.php`
  - `lang/sv/gallery.php`
  - `lang/vi/gallery.php`
  - `lang/zh_CN/gallery.php`
  - `lang/zh_TW/gallery.php`
- [ ] Use English text as placeholder for now (translation to be done later)

**Duration:** 30 min  
**Dependencies:** Task 5.1 complete  
**Verification:** Manual check that all 22 files contain the 7 keys

---

### Task 5.3: Verify translation keys
- [ ] Run application, check no missing translation key errors
- [ ] Verify NFR-026-09 implemented (all strings use translation keys)

**Duration:** 10 min  
**Dependencies:** Task 5.2 complete  
**Verification:** Manual testing or `php artisan test` (no translation errors)

---

## I6 – AlbumTagFilter Component (Frontend)

### Task 6.1: Write AlbumTagFilter component tests
- [ ] Create `resources/js/components/album/AlbumTagFilter.spec.ts`
- [ ] Mock Album::tags API endpoint
- [ ] Test: Component fetches tags via Album::tags on mount
- [ ] Test: Component hides itself when tags array empty (FR-026-08)
- [ ] Test: Multi-select dropdown populated with fetched tags
- [ ] Test: Logic toggle switches between OR/AND
- [ ] Test: Apply button emits 'apply' event with `{ tagIds, logic }`
- [ ] Test: Clear button resets selection and emits 'clear' event
- [ ] Test: Active filter summary displays when filter applied
- [ ] Verify tests fail (component doesn't exist yet)

**Duration:** 60 min  
**Dependencies:** I1 complete (Album::tags endpoint available), I5 complete (translations)  
**Verification:** `npm run test:unit -- AlbumTagFilter.spec.ts` (expect failures)

---

### Task 6.2: Create AlbumTagFilter Vue component scaffold
- [ ] Create `resources/js/components/album/AlbumTagFilter.vue`
- [ ] Set up Composition API with TypeScript: `<script setup lang="ts">`
- [ ] Define props: `albumId: string` (required)
- [ ] Define emits: `apply`, `clear`
- [ ] Create refs: `availableTags`, `selectedTagIds`, `tagLogic`, `isLoading`, `isVisible`
- [ ] Template structure: filter label, multi-select, logic toggle, Apply/Clear buttons, filter summary
- [ ] Add basic styling (use existing Lychee/PrimeVue styles)

**Duration:** 30 min  
**Dependencies:** Task 6.1 complete  
**Verification:** Component renders without errors (visual check)

---

### Task 6.3: Implement tag fetching logic
- [ ] In `onMounted()` hook, fetch tags via `/api/Album::tags?album_id=${props.albumId}`
- [ ] Use axios service from `services/` directory with `${Constants.getApiUrl()}` base URL
- [ ] Handle response: populate `availableTags` with tag objects `[{id, name, description}]`
- [ ] If `availableTags.length === 0`, set `isVisible = false` (hide component)
- [ ] Handle API errors: set `isVisible = false`, log error
- [ ] Use `.then()` instead of `await` (Vue3 convention per AGENTS.md)

**Duration:** 30 min  
**Dependencies:** Task 6.2 complete  
**Verification:** Component fetches tags and displays/hides correctly

---

### Task 6.4: Implement PrimeVue MultiSelect with filter
- [ ] Add PrimeVue `MultiSelect` component to template
- [ ] Bind to `selectedTagIds` ref
- [ ] Set `:options="availableTags"`, `optionLabel="name"`, `optionValue="id"`
- [ ] Enable filter: `:filter="true"` (Q-026-02 resolved)
- [ ] Add placeholder: use `$t('gallery.tag_filter_label')` translation
- [ ] Style according to Lychee design system

**Duration:** 20 min  
**Dependencies:** Task 6.3 complete  
**Verification:** Dropdown displays tags with search functionality

---

### Task 6.5: Implement logic toggle (OR/AND)
- [ ] Add PrimeVue `RadioButton` group for OR/AND logic
- [ ] Bind to `tagLogic` ref (default "OR")
- [ ] Two options: "OR" (`$t('gallery.tag_filter_logic_or')`) and "AND" (`$t('gallery.tag_filter_logic_and')`)
- [ ] Style buttons inline or as button group

**Duration:** 15 min  
**Dependencies:** Task 6.4 complete  
**Verification:** Logic toggle switches correctly

---

### Task 6.6: Implement Apply and Clear buttons
- [ ] Add Apply button with `@click="applyFilter()"`
- [ ] In `applyFilter()`: emit `('apply', { tagIds: selectedTagIds.value, logic: tagLogic.value })`
- [ ] Add Clear button with `@click="clearFilter()"`
- [ ] In `clearFilter()`: reset `selectedTagIds = []`, emit `('clear')`
- [ ] Use translation keys for button labels

**Duration:** 20 min  
**Dependencies:** Task 6.5 complete  
**Verification:** Buttons emit correct events

---

### Task 6.7: Implement active filter summary
- [ ] Add conditional rendering: show summary when `selectedTagIds.length > 0`
- [ ] Display: "Filtered by: [tag names] (OR/AND)" using `$t('gallery.tag_filter_active_summary')`
- [ ] Format tag names as comma-separated list
- [ ] Hide summary when filter cleared

**Duration:** 20 min  
**Dependencies:** Task 6.6 complete  
**Verification:** Summary displays correctly when filter applied

---

### Task 6.8: Run component tests and quality checks
- [ ] Run `npm run test:unit -- AlbumTagFilter.spec.ts` (all pass)
- [ ] Run `npm run check` (0 errors)
- [ ] Run `npm run format` (auto-format code)
- [ ] Verify FR-026-06, FR-026-07, FR-026-08, FR-026-09, FR-026-11, NFR-026-03, NFR-026-08 implemented

**Duration:** 15 min  
**Dependencies:** Tasks 6.1-6.7 complete  
**Verification:** All commands pass with 0 errors

---

## I7 – Integrate Filter into Album.vue (Frontend)

### Task 7.1: Write integration tests for Album.vue with filter
- [ ] Create or extend integration test file for Album.vue
- [ ] Test: Filter component appears in album with tagged photos
- [ ] Test: Applying filter updates photo grid via Album::photos call
- [ ] Test: Pagination preserves filter state (S-026-09)
- [ ] Test: Clear button reloads all photos (unfiltered)
- [ ] Test: Filter hidden when album has no tags
- [ ] Verify tests fail (integration not complete yet)

**Duration:** 45 min  
**Dependencies:** I6 complete, I4 complete (backend filtering ready)  
**Verification:** Integration tests run (expect failures)

---

### Task 7.2: Import AlbumTagFilter component in Album.vue
- [ ] Open `resources/js/views/gallery-panels/Album.vue`
- [ ] Import AlbumTagFilter component at top of `<script setup>`
- [ ] Add component to template below album header, above photo grid
- [ ] Add conditional rendering: `v-if="albumStore.album?.id"`
- [ ] Pass `:album-id="albumStore.album.id"` prop

**Duration:** 15 min  
**Dependencies:** Task 7.1 complete  
**Verification:** Component appears in album view (visual check)

---

### Task 7.3: Implement applyTagFilter event handler
- [ ] In Album.vue, create `applyTagFilter({ tagIds, logic })` function
- [ ] Store filter state in component ref or Pinia store: `activeTagFilter = { tagIds, logic }`
- [ ] Call photo fetching service with tag filter params:
  ```ts
  fetchAlbumPhotos(albumId, { tag_ids: tagIds, tag_logic: logic })
  ```
- [ ] Update photo grid with filtered results
- [ ] Ensure loading states handled correctly

**Duration:** 30 min  
**Dependencies:** Task 7.2 complete  
**Verification:** Applying filter updates photo grid

---

### Task 7.4: Implement clearTagFilter event handler
- [ ] In Album.vue, create `clearTagFilter()` function
- [ ] Clear filter state: `activeTagFilter = null`
- [ ] Refetch photos without tag filter params:
  ```ts
  fetchAlbumPhotos(albumId) // no filter params
  ```
- [ ] Update photo grid with all photos
- [ ] Reset UI state (loading indicators, etc.)

**Duration:** 20 min  
**Dependencies:** Task 7.3 complete  
**Verification:** Clear button reloads all photos

---

### Task 7.5: Ensure filter persists during pagination
- [ ] Review pagination logic in Album.vue
- [ ] Ensure pagination calls include `activeTagFilter` params if set
- [ ] When navigating to page 2, include `tag_ids[]` and `tag_logic` in API call
- [ ] Test: Filter state preserved across page navigation (S-026-09)

**Duration:** 30 min  
**Dependencies:** Task 7.4 complete  
**Verification:** Test S-026-09 passes

---

### Task 7.6: Handle empty filter results
- [ ] Add conditional rendering for empty state when filter returns 0 photos
- [ ] Display message: `$t('gallery.tag_filter_no_results')`
- [ ] Style empty state consistently with other empty states in app

**Duration:** 15 min  
**Dependencies:** Task 7.5 complete  
**Verification:** Empty state displays when no photos match filter

---

### Task 7.7: Run integration tests and quality checks
- [ ] Run integration tests (all pass)
- [ ] Run `npm run check` (0 errors)
- [ ] Run `npm run format` (auto-format code)
- [ ] Run `npm run build` (successful build)
- [ ] Manual E2E test: apply filter, paginate, clear filter
- [ ] Verify FR-026-07, FR-026-09, FR-026-10 fully implemented

**Duration:** 30 min  
**Dependencies:** Tasks 7.1-7.6 complete  
**Verification:** All commands pass, manual testing successful

---

## I8 – Integration and Performance Testing

### Task 8.1: Create comprehensive feature test fixtures
- [ ] Create test database fixtures with:
  - Regular Album with 10 photos, 5 unique tags
  - TagAlbum with 8 photos, 3 unique tags
  - Smart Album with 15 photos, 7 unique tags
  - Album with 0 tagged photos
  - Album with 1000 photos, 20 unique tags (performance test)
  - Private album with tagged photos (access control test)
- [ ] Ensure fixtures cover all edge cases

**Duration:** 45 min  
**Dependencies:** I1-I7 complete  
**Verification:** Fixtures created in test database

---

### Task 8.2: Run scenario tests S-026-01 through S-026-10 (Frontend)
- [ ] Test S-026-01: Filter UI displayed with tags
- [ ] Test S-026-02: Filter UI hidden when no tags
- [ ] Test S-026-03: OR logic filtering (2 tags)
- [ ] Test S-026-04: AND logic filtering (2 tags)
- [ ] Test S-026-05: Single tag filter
- [ ] Test S-026-06: AND logic with 3 tags
- [ ] Test S-026-07: Empty result handling
- [ ] Test S-026-08: Clear button functionality
- [ ] Test S-026-09: Filter persists during pagination
- [ ] Test S-026-10: (Deferred) Filter state not persisted across navigation
- [ ] Document results for each scenario

**Duration:** 60 min  
**Dependencies:** Task 8.1 complete  
**Verification:** All frontend scenarios pass

---

### Task 8.3: Run scenario tests S-026-11 through S-026-20 (Backend)
- [ ] Test S-026-11: Album::tags returns distinct sorted tags
- [ ] Test S-026-12: Album::tags 404 for invalid album
- [ ] Test S-026-13: Album::tags 403 for private album
- [ ] Test S-026-14: Album::photos with tag filters (OR logic)
- [ ] Test S-026-15: Invalid tag IDs handling (422 if all invalid)
- [ ] Test S-026-16: Backward compatibility (no filter params)
- [ ] Test S-026-17: Performance with large album (1000 photos, 20 tags)
- [ ] Test S-026-18: Access control respected (read-only user)
- [ ] Test S-026-19: TagAlbum with additional tag filter
- [ ] Test S-026-20: Smart Album with tag filter
- [ ] Document results for each scenario

**Duration:** 75 min  
**Dependencies:** Task 8.1 complete  
**Verification:** All backend scenarios pass

---

### Task 8.4: Performance benchmarking (NFR-026-01, NFR-026-02)
- [ ] Use fixture: Album with 1000 photos, 20 unique tags
- [ ] Benchmark Album::tags endpoint: measure p50, p95, p99 latencies
- [ ] Target: ≤50ms p95 (NFR-026-02)
- [ ] Benchmark Album::photos with tag filter (OR logic): measure p50, p95, p99
- [ ] Target: ≤100ms p95 (NFR-026-01)
- [ ] Benchmark Album::photos with tag filter (AND logic): measure p50, p95, p99
- [ ] Target: ≤100ms p95 (NFR-026-01)
- [ ] Verify database indexes are used (EXPLAIN query plans)
- [ ] Document performance results

**Duration:** 45 min  
**Dependencies:** Task 8.3 complete  
**Verification:** Performance targets met

---

### Task 8.5: Security and access control tests
- [ ] Test: Guest user cannot access private album tags (S-026-13)
- [ ] Test: Tag filter respects album-level access policies (S-026-18)
- [ ] Test: Shared album (read-only) allows tag filtering
- [ ] Test: No permission bypass via tag filter parameters
- [ ] Verify middleware (`login_required:album`) enforced correctly

**Duration:** 30 min  
**Dependencies:** Task 8.4 complete  
**Verification:** All security tests pass

---

### Task 8.6: Edge case and error handling tests
- [ ] Test: Album with 0 tags - filter component hidden (S-026-02)
- [ ] Test: All tag IDs invalid - returns 422 error (S-026-15)
- [ ] Test: No photos match filter - empty state with message (S-026-07)
- [ ] Test: Malformed tag_ids[] parameter - validation error
- [ ] Test: Invalid tag_logic value - validation error
- [ ] Test: Large tag list (100+ tags) - MultiSelect filter works

**Duration:** 45 min  
**Dependencies:** Task 8.5 complete  
**Verification:** All edge cases handled gracefully

---

### Task 8.7: Full test suite run
- [ ] Run `php artisan test` (all tests pass, 0 failures)
- [ ] Run `npm run check` (0 errors)
- [ ] Verify all 20 scenarios (S-026-01 through S-026-20) covered
- [ ] Verify all FR requirements (FR-026-01 through FR-026-11) tested
- [ ] Verify all NFR requirements (NFR-026-01 through NFR-026-09) met
- [ ] Document any test failures or issues

**Duration:** 30 min  
**Dependencies:** Tasks 8.1-8.6 complete  
**Verification:** `php artisan test` (0 failures)

---

## I9 – Quality Gates and Documentation

### Task 9.1: Run PHPStan static analysis
- [ ] Run `make phpstan` (level 6)
- [ ] Fix any errors or warnings
- [ ] Re-run until 0 errors
- [ ] Verify NFR-026-07 (coding conventions) met

**Duration:** 20 min  
**Dependencies:** I8 complete  
**Verification:** `make phpstan` (0 errors)

---

### Task 9.2: Run PHP code style checks and fixes
- [ ] Run `vendor/bin/php-cs-fixer fix --dry-run` (check violations)
- [ ] Run `vendor/bin/php-cs-fixer fix` (apply auto-fixes)
- [ ] Manually fix any remaining style issues
- [ ] Verify: license headers, snake_case, no `empty()`, `in_array()` with third param `true`, strict comparison

**Duration:** 20 min  
**Dependencies:** Task 9.1 complete  
**Verification:** `vendor/bin/php-cs-fixer fix --dry-run` (0 violations)

---

### Task 9.3: Run frontend linting and formatting
- [ ] Run `npm run check` (0 errors)
- [ ] Fix any TypeScript or Vue linting errors
- [ ] Run `npm run format` (apply Prettier formatting)
- [ ] Verify NFR-026-08 (frontend conventions) met: Composition API, TypeScript, no await in top-level, PrimeVue components

**Duration:** 20 min  
**Dependencies:** Task 9.2 complete  
**Verification:** `npm run check` (0 errors), `npm run format` (no changes)

---

### Task 9.4: Update roadmap
- [ ] Open `docs/specs/4-architecture/roadmap.md`
- [ ] Update Feature 026 status from "Ready" to "Complete"
- [ ] Add completion date
- [ ] Add notes: "All 20 scenarios tested, performance targets met"

**Duration:** 10 min  
**Dependencies:** Task 9.3 complete  
**Verification:** Roadmap updated correctly

---

### Task 9.5: Update knowledge map
- [ ] Open `docs/specs/4-architecture/knowledge-map.md`
- [ ] Add entry for `AlbumTagFilter` component (frontend/Vue3)
- [ ] Add entry for `Album::tags` endpoint (backend/REST API)
- [ ] Add entry for tag filtering in `PhotoRepository`
- [ ] Document relationships: AlbumTagFilter → Album::tags, Album.vue → AlbumTagFilter

**Duration:** 20 min  
**Dependencies:** Task 9.4 complete  
**Verification:** Knowledge map includes new components/endpoints

---

### Task 9.6: Update current session notes
- [ ] Open `docs/specs/_current-session.md`
- [ ] Record Feature 026 completion
- [ ] Document key decisions: Q-026-01 through Q-026-05 resolutions
- [ ] Note performance benchmarks achieved
- [ ] List any follow-up items or deferred enhancements

**Duration:** 15 min  
**Dependencies:** Task 9.5 complete  
**Verification:** Session notes complete and accurate

---

### Task 9.7: Final verification and analysis gate
- [ ] Review Analysis Gate checklist in plan.md
- [ ] Verify all FR requirements (FR-026-01 through FR-026-11) implemented ✅
- [ ] Verify all NFR requirements (NFR-026-01 through NFR-026-09) met ✅
- [ ] Verify all 20 scenarios tested and passing ✅
- [ ] Verify performance benchmarks met (≤50ms Album::tags, ≤100ms filtering) ✅
- [ ] Verify security requirements met (album access policies respected) ✅
- [ ] Verify backward compatibility confirmed ✅
- [ ] Verify open questions resolved (Q-026-01 through Q-026-05) ✅
- [ ] Complete "Drift Gate" section in plan.md with results

**Duration:** 30 min  
**Dependencies:** Tasks 9.1-9.6 complete  
**Verification:** All exit criteria met

---

### Task 9.8: Prepare commit
- [ ] Stage all modified files
- [ ] Run `./scripts/codex-commit-review.sh` to generate Conventional Commit message
- [ ] Verify commit message includes `Spec impact:` line (docs and code changed together)
- [ ] Review staged changes one final time
- [ ] Present copy/paste-ready `git commit` command to operator
- [ ] DO NOT execute commit unless explicitly delegated by user

**Duration:** 20 min  
**Dependencies:** Task 9.7 complete  
**Verification:** Commit staged and ready for operator execution

---

## Summary

**Total tasks:** 76  
**Estimated total duration:** ~32 hours (spread across 9 increments)  
**Current status:** Not Started  
**Blocked tasks:** None (all dependencies internal to this feature)

**Critical path:**
I1 → I2 → I3 → I4 → I8 → I9 (backend critical path)  
I5 → I6 → I7 → I8 → I9 (frontend critical path, depends on I1)

**Parallelizable work:**
- I5 (translations) can run parallel to I1-I4
- I1-I4 (backend) and I6-I7 (frontend) can overlap once I1 complete

**Next actions:**
1. Begin Task 1.1 (write AlbumTagsControllerTest)
2. Follow test-first approach throughout
3. Mark tasks complete immediately after verification
4. Commit after each increment (I1-I9) when all tests pass
