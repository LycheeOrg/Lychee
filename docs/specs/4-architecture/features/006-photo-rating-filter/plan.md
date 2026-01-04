# Feature Plan 006 – Photo Star Rating Filter

_Linked specification:_ `docs/specs/4-architecture/features/006-photo-rating-filter/spec.md`
_Status:_ Draft
_Last updated:_ 2026-01-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:**
Users can quickly filter photos in an album by minimum star rating threshold using an intuitive visual interface (5 clickable stars). This makes it easy to find highly-rated photos or explore photos by rating quality without complex UI. The filter only appears when relevant (at least one rated photo exists), keeping the interface clean.

**Success Signals:**
- Star filter control visible when album has ≥1 rated photo, hidden otherwise
- Clicking star N filters photos to show rating ≥ N (minimum threshold)
- Clicking same star again clears filter (toggle behavior)
- Filter state persists in Pinia store during session
- Filtering is instant (client-side, no API calls)
- Responsive keyboard navigation works (Tab, Arrow keys, Enter)

**Quality Bars:**
- Code follows Vue 3 Composition API and TypeScript conventions (NFR-006-03)
- Keyboard accessible with proper aria-labels (NFR-006-02)
- Filtering performance handles 1000+ photos smoothly (<100ms) (NFR-006-04)
- No API calls, fully client-side filtering (NFR-006-01)

## Scope Alignment

**In scope:**
- Star filter control in PhotoThumbPanelControl.vue (5 clickable stars)
- Conditional rendering (show only when rated photos exist)
- Minimum threshold filtering logic (≥ N stars)
- Toggle behavior (click same star to clear filter)
- Filter state in PhotosState store (session-only persistence)
- Client-side filtering computed property
- Keyboard accessibility (Tab, Arrow keys, Enter)
- Visual feedback (filled/empty stars, hover states)
- Responsive design for mobile

**Out of scope:**
- Backend API filtering or query parameters
- Exact rating match filtering (show only N-star photos)
- Filtering for unrated photos explicitly
- Multiple rating selection (checkboxes, multi-select)
- LocalStorage or URL query parameter persistence
- Filter controls for albums (feature is photo-only)
- Range sliders or complex UI controls
- Filtering by aggregate rating (average rating from all users)

## Dependencies & Interfaces

**Frontend Dependencies:**
- Vue 3 (Composition API)
- TypeScript
- Tailwind CSS for styling
- PrimeVue for star icons (`pi-star`, `pi-star-fill`)
- PhotosState.ts store (state management)
- Existing Photo model types (PhotoResource with user_rating field)

**Feature Dependencies:**
- **Feature 001 (Photo Star Rating):** This feature depends on Feature 001 being implemented. Photos must have `user_rating` field populated.

**Components:**
- PhotoThumbPanelControl.vue (existing - will be modified)
- PhotosState.ts (existing - will be modified)
- PhotoThumbPanel.vue or parent component (may need modification for filtered list)

**Interfaces:**
- Photo data structure from PhotosState.ts (id, user_rating: null | 0-5)

**Testing Infrastructure:**
- Vitest (component tests)
- Vue Test Utils
- Performance testing utilities (for 1000+ photo filtering)

## Assumptions & Risks

**Assumptions:**
- Feature 001 (Photo Star Rating) is complete and user_rating field is available
- PhotosState store exists and can be extended with filter state
- Photo grid components accept filtered photo array via props or computed property
- PrimeVue star icons (`pi-star`, `pi-star-fill`) are available
- User ratings are stored as integers 1-5 (or null for unrated)

**Risks / Mitigations:**

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Feature 001 not complete | High - blocking dependency | Verify Feature 001 status before starting. If incomplete, defer Feature 006 or implement mock data for testing |
| Performance issues with 1000+ photos | Medium - slow filtering | Use Vue computed properties (cached), test with large datasets, consider virtualization if needed (defer to follow-up) |
| Star icon visual clarity | Low - UX concern | Use standard PrimeVue icons, test with users, adjust colors/size if needed |
| Mobile touch targets too small | Low - accessibility | Ensure stars are ≥44px touch targets, test on real devices |
| Filter state conflicts with other features | Low - state management | Use unique state property name, follow existing patterns (e.g., NSFW visibility) |

## Implementation Drift Gate

**Drift Detection Strategy:**
- Before each increment, verify the specification FR/NFR requirements still match the planned work
- After each increment, confirm deliverables align with success criteria
- Record any deviations or clarifications in this plan's appendix

**Evidence Collection:**
- Component tests pass (`npm run check`)
- Visual screenshots of star filter (empty, partial filled, hover states)
- Performance measurements (filtering time for 1000 photos)
- Accessibility audit results (keyboard navigation, aria-labels)

**Commands to Rerun:**
- `npm run format` - Frontend formatting
- `npm run check` - Frontend tests and TypeScript type checking
- `npm run dev` - Local development server for manual testing

## Increment Map

### I1 – PhotosState Store Modifications (Filter State)

**Goal:** Add photo rating filter state to PhotosState.ts

**Preconditions:** PhotosState.ts store exists (verify location)

**Steps:**
1. Read existing PhotosState.ts to understand structure
2. Add `photo_rating_filter: null | 1 | 2 | 3 | 4 | 5` property (default: null)
3. Add computed getter `photoRatingFilter`
4. Add action `setPhotoRatingFilter(rating: null | 1 | 2 | 3 | 4 | 5)` that updates state
5. Write unit test for filter state behavior

**Commands:**
- `npm run check` (verify TypeScript types and tests)

**Exit:** PhotosState has photo_rating_filter state, getter, and action. Tests pass.

**Implements:** FR-006-04, S-006-08

---

### I2 – Filtering Logic Computed Property

**Goal:** Create computed property that filters photos by minimum rating threshold

**Preconditions:** I1 complete (filter state ready), Feature 001 complete (user_rating field exists)

**Steps:**
1. Identify where photo list is rendered (likely PhotoThumbPanel.vue or parent)
2. Add computed property `filteredPhotos`:
   ```typescript
   const filteredPhotos = computed(() => {
     const filter = photosStore.photoRatingFilter;
     const hasRated = photos.value.some(p => p.user_rating && p.user_rating > 0);

     if (filter === null || !hasRated) {
       return photos.value;
     }

     return photos.value.filter(p =>
       p.user_rating !== null &&
       p.user_rating >= filter
     );
   });
   ```
3. Update photo grid rendering to use `filteredPhotos` instead of `photos`
4. Write unit tests for filtering logic:
   - Test filter === null → all photos
   - Test filter === 3 → only photos with rating ≥ 3
   - Test filter === 5 → only 5-star photos
   - Test no rated photos → all photos shown
   - Test unrated photos excluded when filter active

**Commands:**
- `npm run check` (tests + types)
- `npm run dev` (manual testing with mock photo data)

**Exit:** Filtered photo list works correctly based on filter state, tests pass

**Implements:** FR-006-02, FR-006-05, NFR-006-01, NFR-006-04, S-006-03, S-006-04, S-006-05, S-006-07

---

### I3 – Star Filter Control Component Structure

**Goal:** Add star filter UI control to PhotoThumbPanelControl.vue

**Preconditions:** I1, I2 complete (filter state and logic ready)

**Steps:**
1. Read existing PhotoThumbPanelControl.vue to understand layout
2. Add computed property `hasRatedPhotos`:
   ```typescript
   const hasRatedPhotos = computed(() =>
     photos.value.some(p => p.user_rating && p.user_rating > 0)
   );
   ```
3. Add template section for star filter (before layout buttons):
   ```vue
   <div
     v-if="hasRatedPhotos"
     role="group"
     aria-label="Filter by star rating"
     class="inline-flex gap-1 pr-3 border-r border-gray-300 dark:border-gray-600"
   >
     <button
       v-for="star in 5"
       :key="star"
       :aria-label="`Filter by ${star} stars or higher`"
       :aria-pressed="photoRatingFilter === star"
       class="text-lg cursor-pointer hover:scale-110"
       @click="handleStarClick(star)"
     >
       <i :class="starIconClass(star)" />
     </button>
   </div>
   ```
4. Add methods:
   - `handleStarClick(star: number)`: toggle logic
   - `starIconClass(star: number)`: filled vs empty icon
5. Import PhotosState store to access filter state

**Commands:**
- `npm run check`
- `npm run dev` (visual inspection)

**Exit:** Star filter control renders when hasRatedPhotos === true, hidden otherwise

**Implements:** FR-006-01, FR-006-07, S-006-01, S-006-02

---

### I4 – Star Click Interaction (Toggle Behavior)

**Goal:** Implement click handling for star filter with toggle logic

**Preconditions:** I3 complete (star UI rendered)

**Steps:**
1. Implement `handleStarClick(star: number)` method:
   ```typescript
   const handleStarClick = (star: number) => {
     const current = photosStore.photoRatingFilter;
     if (current === star) {
       // Click same star → clear filter
       photosStore.setPhotoRatingFilter(null);
     } else {
       // Click different star → set filter
       photosStore.setPhotoRatingFilter(star);
     }
   };
   ```
2. Implement `starIconClass(star: number)` method:
   ```typescript
   const starIconClass = (star: number) => {
     const filter = photosStore.photoRatingFilter;
     const filled = filter !== null && star <= filter;
     return filled
       ? 'pi pi-star-fill text-yellow-500'
       : 'pi pi-star text-gray-300 dark:text-gray-600';
   };
   ```
3. Test click behavior manually:
   - Click star 3 → stars 1-3 filled, photos filtered
   - Click star 3 again → all stars empty, filter cleared
   - Click star 5 → all stars filled, only 5-star photos shown

**Commands:**
- `npm run check`
- `npm run dev` (manual testing)

**Exit:** Star clicks toggle filter correctly, visual feedback works

**Implements:** FR-006-02, FR-006-03, FR-006-06, S-006-03, S-006-06, S-006-07

---

### I5 – Hover and Visual Feedback

**Goal:** Add hover states and visual polish to star filter

**Preconditions:** I4 complete (click behavior works)

**Steps:**
1. Add hover styling to star buttons:
   - Hover effect: `hover:text-yellow-400` for empty stars
   - Hover effect: `hover:scale-110 transition-transform duration-150`
2. Add focus styling for keyboard navigation:
   - Focus outline: `focus:outline-none focus:ring-2 focus:ring-primary`
3. Test hover states:
   - Hover over empty star → color preview
   - Hover over filled star → maintain filled color
4. Ensure touch targets are ≥44px on mobile:
   - Add padding if needed: `p-2` or `p-3`

**Commands:**
- `npm run dev` (visual inspection, hover testing)

**Exit:** Hover states work correctly, visual feedback is clear

**Implements:** FR-006-06, UI-006-04

---

### I6 – Keyboard Accessibility

**Goal:** Add keyboard navigation and ARIA attributes for accessibility

**Preconditions:** I5 complete (visual feedback works)

**Steps:**
1. Verify ARIA attributes are correct:
   - Group: `role="group" aria-label="Filter by star rating"`
   - Buttons: `aria-label="Filter by N stars or higher"` and `aria-pressed="true|false"`
2. Add keyboard event handlers:
   - Arrow Left/Right to navigate between stars
   - Enter/Space to activate star (same as click)
   - Tab to focus into/out of star group
3. Implement keyboard navigation logic:
   ```typescript
   const handleKeyDown = (event: KeyboardEvent, star: number) => {
     if (event.key === 'ArrowRight' && star < 5) {
       // Focus next star
       focusStar(star + 1);
     } else if (event.key === 'ArrowLeft' && star > 1) {
       // Focus previous star
       focusStar(star - 1);
     } else if (event.key === 'Enter' || event.key === ' ') {
       event.preventDefault();
       handleStarClick(star);
     }
   };
   ```
4. Test keyboard navigation:
   - Tab to star filter
   - Arrow keys to navigate
   - Enter to select
   - Tab out to layout buttons

**Commands:**
- `npm run check`
- Manual keyboard testing

**Exit:** Keyboard navigation works, ARIA attributes correct, screen reader friendly

**Implements:** NFR-006-02, UI-006-05

---

### I7 – Responsive Mobile Layout

**Goal:** Ensure star filter works on mobile devices

**Preconditions:** I6 complete (full desktop functionality)

**Steps:**
1. Test on mobile viewport sizes:
   - 320px (very narrow)
   - 375px (iPhone SE)
   - 768px (tablet)
2. Verify touch targets are ≥44px:
   - Add padding if needed: `p-2` on mobile (`md:p-1` on desktop)
3. Adjust spacing for mobile:
   - Star gap: `gap-1` on mobile, `gap-2` on desktop
   - Border separator may need adjustment
4. Test touch interaction:
   - Tap star to filter
   - Ensure no hover state interferes with touch
5. Consider icon-only on very narrow screens (optional)

**Commands:**
- `npm run dev` (test in browser DevTools responsive mode)

**Exit:** Star filter works on all mobile breakpoints, touch targets adequate

**Implements:** Responsive design requirements

---

### I8 – Component Unit Tests

**Goal:** Add comprehensive unit tests for star filter functionality

**Preconditions:** I4 complete (core functionality works)

**Steps:**
1. Create test file for PhotoThumbPanelControl (if not exists)
2. Write tests for `hasRatedPhotos` computed property:
   - No rated photos → false
   - At least one rated photo → true
3. Write tests for star click behavior:
   - Click star N → filter set to N
   - Click star N when filter === N → filter cleared
4. Write tests for filtering logic (from I2):
   - Filter null → all photos
   - Filter 3 → only ≥3 star photos
   - Filter 5 → only 5-star photos
   - No rated photos → no filtering applied
5. Write tests for starIconClass:
   - Filter null → all empty stars
   - Filter 3 → stars 1-3 filled, 4-5 empty
6. Create fixture file `photos-rating-filter.json` with sample data

**Commands:**
- `npm run check` (run all tests)

**Exit:** All unit tests pass, coverage for filter functionality

**Implements:** Test strategy from spec, S-006-01 through S-006-10

---

### I9 – Performance Testing

**Goal:** Verify filtering performance with 1000+ photos

**Preconditions:** I2 complete (filtering logic implemented)

**Steps:**
1. Create test dataset with 1000 photos (various ratings)
2. Measure filtering performance:
   - Use browser DevTools Performance tab
   - Measure computed property recalculation time
   - Target: <100ms for 1000 photos (NFR-006-04)
3. Test scenarios:
   - Filter from null to 3 (large change)
   - Filter from 3 to 4 (small change)
   - Clear filter (back to all photos)
4. If performance is poor:
   - Check for unnecessary re-renders
   - Verify computed property is cached correctly
   - Consider optimization (memoization, virtualization)

**Commands:**
- `npm run dev` (manual performance testing)
- Browser DevTools Performance profiling

**Exit:** Filtering completes within 100ms for 1000 photos

**Implements:** NFR-006-04

---

### I10 – Integration Testing & Edge Cases

**Goal:** Test end-to-end filter behavior and edge cases

**Preconditions:** I8 complete (unit tests pass)

**Steps:**
1. Manual integration testing:
   - Load album with mixed rated/unrated photos
   - Verify filter appears
   - Click stars, verify filtering works
   - Navigate away and back, verify filter state persists in session
   - Reload page, verify filter resets to null
2. Test edge cases:
   - Album with no rated photos → filter hidden
   - Album with all same rating (e.g., all 3 stars) → filter ≥4 shows empty grid
   - User rates photo while filter active → list updates reactively
   - User changes photo rating → filtered list updates
3. Test with Feature 001 integration:
   - Verify user_rating field is populated correctly
   - Test rating a photo, then filtering by that rating

**Commands:**
- `npm run dev` (manual testing)

**Exit:** All integration tests pass, edge cases handled correctly

**Implements:** S-006-08, S-006-09, S-006-10

---

### I11 – Documentation Updates

**Goal:** Update knowledge map and spec documentation

**Preconditions:** I10 complete (feature fully implemented and tested)

**Steps:**
1. Update [docs/specs/4-architecture/knowledge-map.md](docs/specs/4-architecture/knowledge-map.md):
   - Note PhotoThumbPanelControl.vue modifications (star filter)
   - Note PhotosState.ts modifications (filter state)
   - Document filtering logic location
2. Update spec.md status to "Implemented"
3. Update roadmap.md feature status to "Complete"
4. Create PR description with:
   - Feature summary
   - Screenshots (filter empty, filter active, hover states)
   - Testing notes
   - Dependency note (Feature 001 required)

**Commands:**
- None (documentation updates)

**Exit:** Knowledge map updated, documentation current

**Implements:** Documentation deliverables from spec

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-006-01 | I3 | No rated photos → filter hidden |
| S-006-02 | I3 | ≥1 rated photo → filter visible |
| S-006-03 | I2, I4 | Click star 3 → filter ≥3, photos filtered |
| S-006-04 | I2, I4 | Click star 5 → filter ≥5, only 5-star photos |
| S-006-05 | I2 | Click star 1 → filter ≥1, all rated photos (excludes unrated) |
| S-006-06 | I4 | Click star when already selected → filter cleared |
| S-006-07 | I4 | Click star 4, then star 2 → filter changes to ≥2 |
| S-006-08 | I1, I10 | Navigate within album → filter state persists |
| S-006-09 | I10 | Reload page → filter resets |
| S-006-10 | I10 | Rate photo while filter active → list updates reactively |

## Analysis Gate

**Status:** Not yet executed (spec just created)

**Checklist to complete before implementation:**
- [ ] Verify Feature 001 (Photo Star Rating) is complete and deployed
- [ ] Confirm PhotosState store exists and can be extended
- [ ] Check that PhotoResource includes user_rating field
- [ ] Verify PrimeVue star icons are available (`pi-star`, `pi-star-fill`)
- [ ] Review existing PhotoThumbPanelControl.vue structure
- [ ] Confirm no conflicts with other active features (Feature 005)

**Findings:** (To be filled after analysis gate execution)

## Exit Criteria

Before declaring Feature 006 complete, the following must pass:

- [ ] All increments (I1-I11) completed successfully
- [ ] `npm run format` passes (frontend code formatting)
- [ ] `npm run check` passes (frontend tests and TypeScript type checking)
- [ ] Manual testing confirms:
  - [ ] Star filter hidden when no rated photos
  - [ ] Star filter visible when ≥1 rated photo exists
  - [ ] Click star N → photos filtered to rating ≥ N
  - [ ] Click same star → filter cleared
  - [ ] Hover states work correctly
  - [ ] Keyboard navigation works (Tab, Arrow keys, Enter)
  - [ ] Mobile responsive layout works
  - [ ] Filter state persists during session
  - [ ] Filter resets on page reload
- [ ] Performance test passes (1000 photos filtered in <100ms)
- [ ] Accessibility audit passes (aria-labels, keyboard navigation)
- [ ] Integration with Feature 001 works (user_rating field populated)
- [ ] Documentation updated (knowledge map, spec status)

## Follow-ups / Backlog

**Potential enhancements (defer to future features):**
- Exact rating match filter (show only 3-star photos, not ≥3)
- Explicit "Unrated" filter option (show only unrated photos)
- Multi-select rating filter (checkboxes for 4 AND 5 stars)
- Combined filters (rating + date range + tags + NSFW)
- Save filter presets (named filters)
- URL query parameter support (shareable filtered views)
- Backend filtering (API query parameter `?min_rating=3`)
- Filter by aggregate rating (average from all users, not just current user)
- Filter animation transitions (smooth photo grid updates)

**Monitoring & Metrics:**
- Track filter usage (how often users use filter, which ratings are most filtered)
- Monitor performance with large albums (1000+ photos)
- Collect user feedback on filter UX

**Known Limitations:**
- Filter state not synced across devices (session-only, Pinia store)
- Page reload clears filter (acceptable per requirements)
- Cannot filter for exact rating (only minimum threshold)
- Cannot filter explicitly for unrated photos (they're excluded from filtered results)
- Depends on Feature 001 (blocking dependency)

---

_Last updated: 2026-01-03_
