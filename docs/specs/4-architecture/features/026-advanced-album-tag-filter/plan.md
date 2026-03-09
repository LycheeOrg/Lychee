# Feature Plan 026 – Album Photo Tag Filter

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Ready for Implementation  
_Last updated:_ 2026-03-09

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Enable users to filter photos within an album view by selecting one or more tags with AND/OR logic. The filter UI should be intuitive, performant, and only visible when an album contains tagged photos.

**Success signals:**
- Users can select tags from a dropdown populated with tags in the current album
- Applying an OR filter shows photos with ANY of the selected tags
- Applying an AND filter shows photos with ALL of the selected tags  
- Filter persists during pagination navigation
- Filter UI is hidden when album has no tagged photos
- Performance: Album::tags endpoint ≤50ms p95, tag filtering query ≤100ms p95
- All tests pass (unit, feature, integration)
- PHPStan level 6, php-cs-fixer clean, npm check passes

## Scope Alignment

**In scope:**
- New `GET /api/Album::tags?album_id={id}` endpoint returning tags in an album
- Extend `GET /api/Album::photos` to accept `tag_ids[]` and `tag_logic` parameters
- Vue3 tag filter component with PrimeVue MultiSelect (with filter/search enabled) and RadioButton
- Tag filtering for ALL album types: regular Albums, TagAlbums, and Smart Albums (Q-026-01 resolved)
- Translation keys for 7 UI strings in 22 languages
- Feature tests for both endpoints and all filtering scenarios
- Component tests for filter UI

**Out of scope:**
- Cross-album filtering (filters within single album only)
- Saving filter presets or creating smart albums from filters
- Other filter dimensions (date, location, rating)
- URL query string representation of active filter (filter state in component only, Q-026-03 resolved)
- User-specific filter preferences/history

## Dependencies & Interfaces

**Backend dependencies:**
- Existing `AlbumPhotosController` and `GetAlbumPhotosRequest`
- Existing `PhotoRepository::getPhotosForAlbumPaginated()` method
- Existing `login_required:album` middleware for access control
- Existing `photos_tags` many-to-many relationship
- Existing database indexes on `photo_album`, `photos_tags` tables

**Frontend dependencies:**
- Existing Album.vue view component
- PrimeVue MultiSelect and RadioButton components
- Existing album store (Pinia)
- Existing photo grid rendering components

**Contracts:**
- `Album::tags` response: `{tags: [{id: int, name: string, description: string|null}]}`
- `Album::photos` extended with optional `tag_ids[]: int[]` and `tag_logic: "AND"|"OR"` parameters

## Assumptions & Risks

**Assumptions:**
- Existing indexes on `photo_album.album_id`, `photos_tags.photo_id`, `photos_tags.tag_id` are sufficient for performance
- Albums typically have ≤20 unique tags (per NFR-026-01); PrimeVue MultiSelect filter handles larger lists (Q-026-02 resolved)
- Filter state does NOT need to persist across browser sessions or be in URL (component state only, Q-026-03 resolved)
- Security: Album-level access check is sufficient; Album::tags returns tags from photos directly attached to album (Q-026-04 resolved)
- Validation: Individual invalid tag IDs ignored; if ALL tag IDs invalid, return 422 error (Q-026-05 resolved)

**Risks & Mitigations:**
- **Risk:** Performance degradation with AND logic for many tags
  - **Mitigation:** Use indexed HAVING COUNT query pattern; performance tests in I4
- **Risk:** N+1 query issues when fetching tags
  - **Mitigation:** Use proper eager loading and DISTINCT in Album::tags query
- **Risk:** UI complexity with large tag lists (100+ tags)
  - **Mitigation:** PrimeVue MultiSelect `filter` prop provides built-in search (Q-026-02 resolved)
- **Risk:** Tag filtering behavior for TagAlbums and Smart Albums
  - **Mitigation:** Apply same filtering logic to all album types; TagAlbums can filter by tags within their existing tag set (Q-026-01 resolved)

## Implementation Drift Gate

**Execution:**
- Run after I4 (core backend complete) and I7 (frontend complete)
- Evidence: git diff against spec requirements (FR-026-01 through FR-026-11, NFR-026-01 through NFR-026-09)
- Record results: Add "Drift Gate" section to end of this plan document
- Commands: `make phpstan`, `vendor/bin/php-cs-fixer fix`, `php artisan test`, `npm run check`

## Increment Map

### I1 – Album::tags Endpoint (Backend)

**Goal:** Create new endpoint to fetch tags available in an album.

**Preconditions:** None (new endpoint)

**Steps:**
1. **Test:** Create `AlbumTagsControllerTest.php` feature test with scenarios:
   - S-026-11: Album with tagged photos returns distinct sorted tags
   - S-026-12: Non-existent album returns 404
   - S-026-13: Private album without access returns 403
   - Album with no tagged photos returns empty array
   - S-026-19: TagAlbum returns tags from photos in that TagAlbum
   - S-026-20: Smart Album returns tags from photos in computed photo set
2. **Implementation:**
   - Create `App\Http\Controllers\Gallery\AlbumTagsController`
   - Create `App\Http\Requests\Album\AlbumTagsRequest` (validates `album_id`, uses `login_required:album` middleware)
   - Implement query: JOIN `photo_album` → JOIN `photos_tags` → JOIN `tags` → SELECT DISTINCT with ORDER BY `tags.name`
   - Add route in `routes/api_v2.php`: `Route::get('/Album::tags', [Gallery\AlbumTagsController::class, 'get'])->middleware(['login_required:album', 'cache_control']);`
3. **Implementation:** Return `{tags: [{id, name, description}]}` JSON response

**Commands:**
```bash
php artisan test --filter=AlbumTagsControllerTest
make phpstan
vendor/bin/php-cs-fixer fix
```

**Exit criteria:**
- All 4 test scenarios pass
- PHPStan clean
- php-cs-fixer clean
- FR-026-01 fully implemented

---

### I2 – Extend GetAlbumPhotosRequest (Backend)

**Goal:** Add optional `tag_ids[]` and `tag_logic` parameters to existing request validator.

**Preconditions:** I1 complete, Album::photos endpoint exists

**Steps:**
1. **Test:** Add test cases to existing `GetAlbumPhotosRequestTest` (if exists, else create):
   - Valid `tag_ids[]` array accepted
   - Valid `tag_logic` enum ("AND", "OR") accepted
   - Empty `tag_ids[]` treated as no filter
   - Invalid `tag_logic` value rejected
2. **Implementation:**
   - Extend `GetAlbumPhotosRequest` with optional validation rules:
     ```php
     'tag_ids' => ['sometimes', 'array'],
     'tag_ids.*' => ['integer'],
     'tag_logic' => ['sometimes', 'string', 'in:AND,OR'],
     ```
   - Add accessor methods: `tagIds(): array`, `tagLogic(): string` (default "OR")
   - Add custom validation: After standard rules, check if ALL provided tag IDs are invalid (don't exist in database). If so, throw validation exception with message "No valid tags found for filtering" (Q-026-05 resolved)
3. **Implementation:** Process validated values in `processValidatedValues()`

**Commands:**
```bash
php artisan test --filter=GetAlbumPhotosRequestTest
make phpstan
vendor/bin/php-cs-fixer fix
```

**Exit criteria:**
- Validation tests pass
- PHPStan clean
- FR-026-02 validation path implemented

---

### I3 – PhotoRepository Tag Filtering Logic (Backend)

**Goal:** Add tag filtering capability to `PhotoRepository::getPhotosForAlbumPaginated()`.

**Preconditions:** I2 complete

**Steps:**
1. **Test:** Create `PhotoRepositoryTest.php` unit tests (or extend existing):
   - S-026-03: OR logic with 2 tags returns photos with T1 OR T2
   - S-026-04: AND logic with 2 tags returns photos with T1 AND T2
   - S-026-05: Single tag filter (logic irrelevant)
   - S-026-06: AND logic with 3 tags returns intersection
   - S-026-07: No matching photos returns empty paginator
   - S-026-15: Invalid tag IDs silently ignored
2. **Implementation:**
   - Extend `getPhotosForAlbumPaginated()` signature: add `?array $tag_ids = null, string $tag_logic = 'OR'`
   - **OR logic:** Add `->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tag_ids))`
   - **AND logic:** Add joins + groupBy + havingRaw:
     ```php
     ->join('photos_tags as pt', 'photos.id', '=', 'pt.photo_id')
     ->whereIn('pt.tag_id', $tag_ids)
     ->groupBy('photos.id')
     ->havingRaw('COUNT(DISTINCT pt.tag_id) = ?', [count($tag_ids)])
     ```
   - Handle empty `$tag_ids` = no filtering (existing behavior)
   - Invalid tag IDs: whereIn() will naturally exclude them (no error)

**Commands:**
```bash
php artisan test --filter=PhotoRepositoryTest
make phpstan
vendor/bin/php-cs-fixer fix
```

**Exit criteria:**
- All 6+ test scenarios pass
- Query performance measured (manual test with 1000 photos, 10 tags): ≤100ms
- PHPStan clean
- FR-026-03, FR-026-04, NFR-026-01 implemented

---

### I4 – Wire Album::photos with Tag Filtering (Backend)

**Goal:** Connect request parameters to repository filtering logic in AlbumPhotosController.

**Preconditions:** I3 complete

**Steps:**
1. **Test:** Create `AlbumPhotosFilterTest.php` feature tests:
   - S-026-14: GET `/Album::photos?album_id=A&tag_ids[]=1&tag_ids[]=2&tag_logic=OR` returns filtered photos
   - S-026-16: Backward compatibility - no tag params returns all photos
   - S-026-09: Filter persists across pagination (page 2 with tag filter)
   - S-026-18: User with read-only access can apply filter (respects album access)
   - S-026-19: TagAlbum with additional tag filter (filters within TagAlbum photos)
   - S-026-20: Smart Album with tag filter (filters computed photo set)
2. **Implementation:**
   - In `AlbumPhotosController::get()`, extract tag filter params from request:
     ```php
     $tag_ids = $request->tagIds();
     $tag_logic = $request->tagLogic();
     ```
   - Pass to repository for ALL album types (Albums, TagAlbums, SmartAlbums) - Q-026-01 resolved:
     ```php
     // For all album types:
     $paginator = $this->photo_repository->getPhotosForAlbumPaginated(
         $album->id,
         $album->getEffectivePhotoSorting(),
         $per_page,
         $tag_ids,
         $tag_logic
     );
     ```
   - TagAlbums can filter by tags within their existing tag set (additional filtering layer)
   - SmartAlbums can apply tag filtering to their computed photo set

**Commands:**
```bash
php artisan test --filter=AlbumPhotosFilterTest
make phpstan
vendor/bin/php-cs-fixer fix
```

**Exit criteria:**
- All 4+ feature tests pass
- Backward compatibility verified (S-026-16)
- PHPStan clean
- FR-026-02, FR-026-05, NFR-026-04, NFR-026-05 implemented

---

### I5 – Translation Keys (Backend)

**Goal:** Add English translation keys for filter UI strings.

**Preconditions:** None (can run in parallel with I1-I4)

**Steps:**
1. Add 7 translation keys to `lang/en/gallery.php`:
   ```php
   'tag_filter_label' => 'Filter by tags:',
   'tag_filter_logic_or' => 'OR',
   'tag_filter_logic_and' => 'AND',
   'tag_filter_apply_button' => 'Apply',
   'tag_filter_clear_button' => 'Clear',
   'tag_filter_no_results' => 'No photos found matching your tag filter.',
   'tag_filter_active_summary' => 'Filtered by: :tags (:logic)',
   ```
2. Replicate to remaining 21 language files (use English as placeholder for now)

**Commands:**
```bash
# No automated tests for translations
# Manual verification: check all 22 files updated
```

**Exit criteria:**
- 7 keys added to all 22 language files
- NFR-026-09 implemented

---

### I6 – AlbumTagFilter Component (Frontend)

**Goal:** Create Vue3 component for tag filter UI.

**Preconditions:** I1 complete (Album::tags endpoint available), I5 complete (translations)

**Steps:**
1. **Test:** Create `AlbumTagFilter.spec.ts` component tests:
   - Component fetches tags via Album::tags on mount
   - Component hides itself when tags array empty (FR-026-08)
   - Multi-select dropdown populated with fetched tags
   - Logic toggle switches between OR/AND
   - Apply button emits filter event with selected tag IDs and logic
   - Clear button resets selection and emits clear event
2. **Implementation:**
   - Create `resources/js/components/album/AlbumTagFilter.vue`
   - Composition API setup with `ref` for: `availableTags`, `selectedTagIds`, `tagLogic`, `isLoading`
   - `onMounted()`: fetch tags via `/api/Album::tags?album_id=${props.albumId}`
   - If `availableTags.length === 0`, set `v-if="false"` or return early
   - Template:
     - PrimeVue `MultiSelect` bound to `selectedTagIds`, `:options="availableTags"`, **`:filter="true"`** for search/filter capability (Q-026-02 resolved)
     - PrimeVue `RadioButton` group for OR/AND logic
     - Apply button: `@click="emit('apply', { tagIds: selectedTagIds, logic: tagLogic })"`
     - Clear button: `@click="clearFilter()"`
     - Active filter summary (conditional rendering when filter applied)
   - Use translations: `$t('gallery.tag_filter_label')` etc.

**Commands:**
```bash
npm run test:unit -- AlbumTagFilter.spec.ts
npm run check
npm run format
```

**Exit criteria:**
- All component tests pass
- Component renders correctly in isolation
- FR-026-06, FR-026-07, FR-026-08, FR-026-09, FR-026-11, NFR-026-03, NFR-026-08 implemented

---

### I7 – Integrate Filter into Album.vue (Frontend)

**Goal:** Add AlbumTagFilter component to Album view and wire to photo fetching.

**Preconditions:** I6 complete, I4 complete (backend filtering ready)

**Steps:**
1. **Test:** Create integration test or manual E2E test:
   - Filter component appears in album with tagged photos
   - Applying filter updates photo grid via Album::photos call
   - Pagination preserves filter (S-026-09)
   - Clear button reloads all photos
2. **Implementation:**
   - Import AlbumTagFilter in `Album.vue`
   - Add to template below album header, above photo grid:
     ```vue
     <AlbumTagFilter
       v-if="albumStore.album?.id && !photoStore.isLoaded"
       :album-id="albumStore.album.id"
       @apply="applyTagFilter"
       @clear="clearTagFilter"
     />
     ```
   - Implement `applyTagFilter({ tagIds, logic })`:
     - Store filter state in component data or Pinia store
     - Refetch photos with `tag_ids[]` and `tag_logic` params
   - Implement `clearTagFilter()`:
     - Clear filter state, refetch photos without filter params
   - Ensure pagination calls include filter params from state (FR-026-10)

**Commands:**
```bash
npm run check
npm run format
npm run build
```

**Exit criteria:**
- Filter appears and functions in album view
- Photo grid updates correctly when filter applied
- Pagination preserves filter
- FR-026-07, FR-026-09, FR-026-10 fully implemented

---

### I8 – Integration and Performance Testing

**Goal:** Verify end-to-end functionality and performance benchmarks.

**Preconditions:** I1-I7 complete

**Steps:**
1. **Feature tests:** Run full test suite for scenarios S-026-01 through S-026-18
   - Create album fixtures with varying tag configurations
   - Test all combinations: OR/AND logic, single/multiple tags, no matches, pagination
2. **Performance tests:**
   - Create test album with 1000 photos, 20 unique tags
   - Measure Album::tags endpoint: ≤50ms p95 (NFR-026-02)
   - Measure Album::photos with tag filter: ≤100ms p95 (NFR-026-01)
   - Use `php artisan test --filter=Performance` or manual benchmarking
3. **Security tests:**
   - Verify guest cannot access private album tags (S-026-13)
   - Verify tag filter respects album access policies (S-026-18)
4. **Edge case tests:**
   - Album with 0 tags: filter hidden (S-026-02)
   - All tag IDs invalid: graceful handling (S-026-15)
   - No photos match filter: empty state with message (S-026-07)

**Commands:**
```bash
php artisan test
npm run check
# Manual performance benchmarking (if needed)
```

**Exit criteria:**
- All 18 scenarios pass
- Performance benchmarks met
- Security tests pass
- NFR-026-01, NFR-026-02, NFR-026-04, NFR-026-06 verified

---

### I9 – Quality Gates and Documentation

**Goal:** Final quality checks and documentation updates.

**Preconditions:** I8 complete

**Steps:**
1. **Quality gates:**
   ```bash
   make phpstan          # PHPStan level 6 clean
   vendor/bin/php-cs-fixer fix --dry-run  # Code style clean
   php artisan test      # All tests pass
   npm run check         # Vue/TS linting clean
   npm run format        # Prettier formatting clean
   ```
2. **Documentation:**
   - Update `docs/specs/4-architecture/roadmap.md`: Set Feature 026 status to "Complete"
   - Update `docs/specs/4-architecture/knowledge-map.md`: Add AlbumTagFilter component, Album::tags endpoint
   - Update `docs/specs/_current-session.md`: Record completion
3. **Review:**
   - Review open questions (see Q-026-01 through Q-026-05 below)
   - Ensure all decisions captured in spec or ADR if high-impact

**Commands:**
```bash
make phpstan
vendor/bin/php-cs-fixer fix
php artisan test
npm run check
npm run format
```

**Exit criteria:**
- All quality gates pass
- Documentation updated
- NFR-026-07, NFR-026-08 verified
- Feature ready for commit

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|------------------------------|-------|
| S-026-01 | I6, I7 | Filter UI displayed with tags |
| S-026-02 | I6 | Filter UI hidden when no tags |
| S-026-03 | I3, I4 | OR logic filtering |
| S-026-04 | I3, I4 | AND logic filtering |
| S-026-05 | I3 | Single tag filter |
| S-026-06 | I3 | Multiple tags AND logic |
| S-026-07 | I7 | Empty result handling |
| S-026-08 | I7 | Clear button functionality |
| S-026-09 | I4, I7 | Filter persists during pagination |
| S-026-10 | — | Deferred: filter state not persisted across navigation (component state only) |
| S-026-11 | I1 | Album::tags returns distinct sorted tags |
| S-026-12 | I1 | Album::tags 404 for invalid album |
| S-026-13 | I1 | Album::tags 403 for private album |
| S-026-14 | I4 | Album::photos with tag filters |
| S-026-15 | I2, I3 | Invalid tag IDs: return 422 if all invalid, else silently ignore invalid ones (Q-026-05 resolved) |
| S-026-16 | I4 | Backward compatibility |
| S-026-17 | I8 | Performance test with large album |
| S-026-18 | I8 | Access control respected |
| S-026-19 | I1, I4 | TagAlbum with additional tag filter (Q-026-01 resolved) |
| S-026-20 | I1, I4 | Smart Album with tag filter (Q-026-01 resolved) |

## Analysis Gate

**To be completed after increments I1-I4 (backend) and I6-I7 (frontend) are done.**

Checklist:
- [ ] All FR requirements (FR-026-01 through FR-026-11) implemented
- [ ] All NFR requirements (NFR-026-01 through NFR-026-09) met
- [ ] All 20 scenarios tested and passing
- [ ] Performance benchmarks verified (≤50ms Album::tags, ≤100ms tag filtering)
- [ ] Security requirements verified (album access policies respected)
- [ ] Backward compatibility confirmed (existing Album::photos behavior unchanged)
- [ ] Open questions resolved (Q-026-01 through Q-026-05: all resolved)

## Exit Criteria

- [ ] All increments (I1-I9) complete
- [ ] All tests pass: `php artisan test` (0 failures)
- [ ] PHPStan clean: `make phpstan` (0 errors)
- [ ] Code style clean: `vendor/bin/php-cs-fixer fix` (no violations)
- [ ] Frontend checks pass: `npm run check` (0 errors)
- [ ] Frontend formatted: `npm run format` (no changes)
- [ ] 20 test scenarios (S-026-01 through S-026-20) verified
- [ ] Performance benchmarks met (NFR-026-01, NFR-026-02)
- [ ] Translation keys added to all 22 language files
- [ ] Documentation updated (roadmap, knowledge map, session notes)
- [ ] Open questions resolved or marked as deferred

## Follow-ups / Backlog

**Deferred to future enhancements:**
1. **URL-based filter state:** Add tag filter to URL query string for bookmarking/sharing (Q-026-03: deferred to v2)
2. **Filter analytics:** Add telemetry events for filter usage (currently no events planned)
3. **Saved filter presets:** Allow users to save commonly-used tag filter combinations
4. **Combined filter dimensions:** Add date, location, rating filters that can be combined with tag filter

**Monitoring:**
- Track Album::tags endpoint latency (target ≤50ms p95)
- Track Album::photos with filter latency (target ≤100ms p95)
- Monitor user adoption: how often is tag filter used?

---

## Open Questions Log

All questions resolved. See [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) for resolution details:

- **Q-026-01**: ✅ RESOLVED - Tag filtering applies to ALL album types (regular, TagAlbum, Smart Album)
- **Q-026-02**: ✅ RESOLVED - PrimeVue MultiSelect `filter` prop enabled for tag dropdown search
- **Q-026-03**: ✅ RESOLVED - Component state only (no URL representation in v1)
- **Q-026-04**: ✅ RESOLVED - Album-level access only; Album::tags returns tags from photos attached to album
- **Q-026-05**: ✅ RESOLVED - Return 422 validation error when ALL tag IDs are invalid
