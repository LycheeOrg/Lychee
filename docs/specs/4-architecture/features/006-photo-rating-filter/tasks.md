# Feature 006 Tasks – Photo Star Rating Filter

_Status: Implemented_
_Last updated: 2026-01-14_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`N-`), and scenario IDs (`S-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I1 – PhotosState Store Modifications

- [x] T-006-01 – Locate and read PhotosState.ts store structure (FR-006-04).
  _Intent:_ Understand existing PhotosState store to plan modifications.
  _Verification commands:_
  - File read and structure understood
  _Notes:_ Verify store exists at expected location, note existing properties and patterns.

- [x] T-006-02 – Add photo_rating_filter state property to PhotosState.ts (FR-006-04, S-006-08).
  _Intent:_ Add `photo_rating_filter: null | 1 | 2 | 3 | 4 | 5` property with default value `null`.
  _Verification commands:_
  - `npm run check` (TypeScript compilation)
  _Notes:_ Property should be reactive, type-safe.

- [x] T-006-03 – Add photoRatingFilter getter to PhotosState.ts (FR-006-04).
  _Intent:_ Implement getter for accessing filter state.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use computed or simple getter pattern matching existing store patterns.

- [x] T-006-04 – Add setPhotoRatingFilter action to PhotosState.ts (FR-006-04, S-006-03, S-006-06).
  _Intent:_ Implement action `setPhotoRatingFilter(rating: null | 1 | 2 | 3 | 4 | 5)` that updates filter state.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Action should be type-safe, accept null to clear filter.

- [x] T-006-05 – Write unit tests for PhotosState filter state (S-006-08, S-006-09).
  _Intent:_ Test default value (null), setPhotoRatingFilter updates state correctly.
  _Verification commands:_
  - `npm run check` (includes unit tests)
  _Notes:_ Test both setting and clearing filter.

---

### Increment I2 – Filtering Logic Computed Property

- [x] T-006-06 – Identify photo list rendering location (FR-006-02, FR-006-05).
  _Intent:_ Find where photo array is rendered (PhotoThumbPanel.vue or parent component).
  _Verification commands:_
  - File located and structure understood
  _Notes:_ May be in PhotoPanel.vue, PhotoThumbPanel.vue, or similar.

- [x] T-006-07 – Add hasRatedPhotos computed property (FR-006-01, S-006-01, S-006-02).
  _Intent:_ Create computed property that checks if any photo has user_rating > 0.
  _Verification commands:_
  - `npm run check`
  _Notes:_ `photos.value.some(p => p.user_rating && p.user_rating > 0)`.

- [x] T-006-08 – Add filteredPhotos computed property (FR-006-02, FR-006-05, NFR-006-01, S-006-03, S-006-04, S-006-05).
  _Intent:_ Create computed property that filters photos by minimum rating threshold.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (manual testing with mock data)
  _Notes:_ Logic: if filter === null OR no rated photos → all photos; else → photos.filter(p => p.user_rating >= filter).

- [x] T-006-09 – Update photo grid rendering to use filteredPhotos (FR-006-02, FR-006-05).
  _Intent:_ Change photo grid component to render filteredPhotos instead of photos array.
  _Verification commands:_
  - `npm run dev` (verify grid updates when filter changes)
  _Notes:_ May need to pass filteredPhotos as prop or use computed directly.

- [x] T-006-10 – Write unit tests for filtering logic (S-006-03, S-006-04, S-006-05, S-006-07).
  _Intent:_ Test filter null → all photos, filter 3 → ≥3 stars, filter 5 → only 5 stars, no rated photos → all photos.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Test unrated photos excluded when filter active.

---

### Increment I3 – Star Filter Control Component Structure

- [x] T-006-11 – Read PhotoThumbPanelControl.vue structure (FR-006-07).
  _Intent:_ Understand existing PhotoThumbPanelControl.vue layout and button structure.
  _Verification commands:_
  - File read and structure understood
  _Notes:_ Note location of layout buttons, spacing, styling patterns.

- [x] T-006-12 – Import PhotosState store in PhotoThumbPanelControl.vue (FR-006-04).
  _Intent:_ Import and setup PhotosState store to access filter state.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use `const photosStore = usePhotosStateStore()` or similar pattern.

- [x] T-006-13 – Add hasRatedPhotos computed property to PhotoThumbPanelControl.vue (FR-006-01, S-006-01, S-006-02).
  _Intent:_ Add computed property to detect if any photo has rating.
  _Verification commands:_
  - `npm run check`
  _Notes:_ May need to access photos array via props or store.

- [x] T-006-14 – Add star filter template structure to PhotoThumbPanelControl.vue (FR-006-01, FR-006-07).
  _Intent:_ Add div with v-if="hasRatedPhotos", render 5 star buttons with v-for.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (visual inspection - stars appear when rated photos exist)
  _Notes:_ Position before layout buttons, use role="group" and aria-label.

- [x] T-006-15 – Add ARIA attributes to star filter group (NFR-006-02).
  _Intent:_ Add role="group", aria-label="Filter by star rating" to star filter container.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Ensure accessibility attributes correct.

---

### Increment I4 – Star Click Interaction (Toggle Behavior)

- [x] T-006-16 – Implement handleStarClick method (FR-006-02, FR-006-03, S-006-03, S-006-06).
  _Intent:_ Add click handler that toggles filter (if current === clicked → clear, else → set).
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (test click behavior)
  _Notes:_ Call photosStore.setPhotoRatingFilter(star) or (null).

- [x] T-006-17 – Implement starIconClass method (FR-006-06).
  _Intent:_ Return filled or empty star icon class based on filter state.
  _Verification commands:_
  - `npm run dev` (visual inspection - correct stars filled)
  _Notes:_ If star <= filter → filled ('pi pi-star-fill'), else → empty ('pi pi-star').

- [x] T-006-18 – Add star icon styling (FR-006-06).
  _Intent:_ Apply Tailwind classes for filled/empty stars (yellow vs gray).
  _Verification commands:_
  - `npm run dev` (visual inspection)
  _Notes:_ Filled: `text-yellow-500`, empty: `text-gray-300 dark:text-gray-600`.

- [x] T-006-19 – Test star click toggle behavior manually (S-006-03, S-006-06, S-006-07).
  _Intent:_ Manually test: click star 3 → filter ≥3, click star 3 again → clear filter.
  _Verification commands:_
  - `npm run dev` (manual testing)
  _Notes:_ Verify photo grid updates correctly, stars fill/unfill as expected.

---

### Increment I5 – Hover and Visual Feedback

- [x] T-006-20 – Add hover styling to star buttons (FR-006-06, UI-006-04).
  _Intent:_ Add hover effects: color preview, scale transform.
  _Verification commands:_
  - `npm run dev` (hover over stars, verify visual feedback)
  _Notes:_ Classes: `hover:text-yellow-400 hover:scale-110 transition-transform duration-150`.

- [x] T-006-21 – Add focus styling for keyboard navigation (NFR-006-02).
  _Intent:_ Add visible focus outline for keyboard users.
  _Verification commands:_
  - `npm run dev` (tab to star filter, verify focus outline visible)
  _Notes:_ Classes: `focus:outline-none focus:ring-2 focus:ring-primary`.

- [x] T-006-22 – Ensure touch targets are ≥44px on mobile (NFR-006-02).
  _Intent:_ Add padding to star buttons to meet minimum touch target size.
  _Verification commands:_
  - `npm run dev` (test in browser DevTools responsive mode)
  _Notes:_ Classes: `p-2` or `p-3`, verify total size ≥44px.

---

### Increment I6 – Keyboard Accessibility

- [x] T-006-23 – Add aria-label and aria-pressed to each star button (NFR-006-02, UI-006-05).
  _Intent:_ Add accessibility attributes: aria-label="Filter by N stars or higher", aria-pressed based on active state.
  _Verification commands:_
  - `npm run check`
  - Manual screen reader testing (if available)
  _Notes:_ aria-pressed="true" when filter === star, else "false".

- [x] T-006-24 – Implement keyboard event handlers (Arrow keys, Enter, Space) (NFR-006-02, UI-006-05).
  _Intent:_ Add @keydown handler for arrow key navigation and Enter/Space activation.
  _Verification commands:_
  - Manual keyboard testing (Tab, Arrow Left/Right, Enter)
  _Notes:_ Arrow Right → focus next star, Arrow Left → focus prev star, Enter/Space → click.

- [x] T-006-25 – Test keyboard navigation flow (NFR-006-02).
  _Intent:_ Test full keyboard workflow: Tab to filter, Arrow keys to select star, Enter to activate, Tab out.
  _Verification commands:_
  - Manual keyboard testing
  _Notes:_ Verify focus moves correctly, Enter activates filter, visual feedback clear.

---

### Increment I7 – Responsive Mobile Layout

- [ ] T-006-26 – Test star filter on 320px viewport (Responsive design).
  _Intent:_ Verify layout doesn't overflow, stars remain clickable/tappable.
  _Verification commands:_
  - `npm run dev` (browser DevTools responsive mode, set to 320px width)
  _Notes:_ Adjust gap/padding if needed.

- [ ] T-006-27 – Test star filter on 375px and 768px viewports (Responsive design).
  _Intent:_ Verify responsive behavior at common mobile breakpoints.
  _Verification commands:_
  - `npm run dev` (test multiple viewport sizes)
  _Notes:_ Capture screenshots for documentation.

- [ ] T-006-28 – Test touch interaction on mobile (Responsive design).
  _Intent:_ Verify stars are tappable, no hover state conflicts with touch.
  _Verification commands:_
  - Test on real mobile device (or browser touch emulation)
  _Notes:_ Ensure touch targets ≥44px, tap feedback clear.

---

### Increment I8 – Component Unit Tests

- [ ] T-006-29 – Create fixture file photos-rating-filter.json (FX-006-01).
  _Intent:_ Create sample photo data with various ratings (1-5, null) for testing.
  _Verification commands:_
  - File exists and is valid JSON
  _Notes:_ Include photos with ratings 1-5, unrated (null), and edge cases.

- [ ] T-006-30 – Write unit tests for hasRatedPhotos computed property (S-006-01, S-006-02).
  _Intent:_ Test: no rated photos → false, ≥1 rated photo → true.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use fixture data for test cases.

- [ ] T-006-31 – Write unit tests for handleStarClick toggle behavior (S-006-03, S-006-06, S-006-07).
  _Intent:_ Test: click star N → filter set, click same star → filter cleared, click different star → filter changes.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Mock PhotosState store, verify setPhotoRatingFilter called correctly.

- [ ] T-006-32 – Write unit tests for filteredPhotos computed property (S-006-03, S-006-04, S-006-05, S-006-07).
  _Intent:_ Test filtering logic: filter null → all, filter 3 → ≥3, filter 5 → only 5, no rated → all.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Test unrated photos excluded when filter active.

- [ ] T-006-33 – Write unit tests for starIconClass method (FR-006-06).
  _Intent:_ Test: filter null → all empty stars, filter 3 → stars 1-3 filled, 4-5 empty.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Verify correct icon classes returned.

---

### Increment I9 – Performance Testing

- [ ] T-006-34 – Create test dataset with 1000 photos (NFR-006-04).
  _Intent:_ Generate or mock 1000 photo objects with various ratings for performance testing.
  _Verification commands:_
  - Test data created
  _Notes:_ Use script or manual creation, ensure mix of ratings.

- [ ] T-006-35 – Measure filtering performance with 1000 photos (NFR-006-04).
  _Intent:_ Use browser DevTools Performance tab to measure filteredPhotos computed property recalculation time.
  _Verification commands:_
  - Performance measurement < 100ms for 1000 photos
  _Notes:_ Test filter changes (null → 3, 3 → 5, 5 → null).

- [ ] T-006-36 – Optimize if performance is poor (NFR-006-04).
  _Intent:_ If filtering takes >100ms, investigate and optimize (check unnecessary re-renders, computed caching).
  _Verification commands:_
  - Performance measurement after optimization
  _Notes:_ May skip if performance is already good.

---

### Increment I10 – Integration Testing & Edge Cases

- [ ] T-006-37 – Manual integration testing: filter visibility (S-006-01, S-006-02).
  _Intent:_ Load album with no rated photos → filter hidden. Load album with ≥1 rated photo → filter visible.
  _Verification commands:_
  - `npm run dev` (manual testing with different albums)
  _Notes:_ Test with real album data.

- [ ] T-006-38 – Manual integration testing: filter behavior (S-006-03, S-006-04, S-006-05, S-006-06, S-006-07).
  _Intent:_ Test click stars, verify filtering works, toggle behavior works, photo grid updates.
  _Verification commands:_
  - `npm run dev` (manual testing)
  _Notes:_ Test all star values (1-5), clear filter, change filter.

- [ ] T-006-39 – Manual integration testing: session persistence (S-006-08, S-006-09).
  _Intent:_ Set filter, navigate within album → filter persists. Reload page → filter resets.
  _Verification commands:_
  - `npm run dev` (manual testing with navigation and reload)
  _Notes:_ Verify filter state in store persists during session, resets on reload.

- [ ] T-006-40 – Test edge case: all photos same rating (S-006-07).
  _Intent:_ Album with all photos rated 3 stars → filter ≥4 shows empty grid (valid behavior).
  _Verification commands:_
  - `npm run dev` (test with mock data)
  _Notes:_ Verify empty state message or empty grid displayed.

- [ ] T-006-41 – Test edge case: user rates photo while filter active (S-006-10).
  _Intent:_ Set filter ≥3, rate a photo to 4 stars → photo appears in filtered list. Change rating to 2 → photo disappears.
  _Verification commands:_
  - `npm run dev` (test with Feature 001 rating UI)
  _Notes:_ Verify reactive updates work correctly.

- [ ] T-006-42 – Test integration with Feature 001 (Photo Star Rating).
  _Intent:_ Verify user_rating field is populated, filtering works with real rating data.
  _Verification commands:_
  - `npm run dev` (test with Feature 001 deployed)
  _Notes:_ Ensure Feature 001 is complete before testing.

---

### Increment I11 – Documentation Updates

- [ ] T-006-43 – Update knowledge-map.md with new modifications.
  _Intent:_ Add entries for PhotoThumbPanelControl.vue modifications (star filter) and PhotosState.ts modifications (filter state).
  _Verification commands:_
  - File updated and readable
  _Notes:_ Follow existing knowledge map format.

- [ ] T-006-44 – Update spec.md status to Implemented.
  _Intent:_ Change status field in spec.md from "Draft" to "Implemented", update last updated date.
  _Verification commands:_
  - File updated
  _Notes:_ Update after all other tasks complete.

- [ ] T-006-45 – Update roadmap.md feature status.
  _Intent:_ Change Feature 006 status from "Planning" to "In Progress" when implementation starts, "Complete" when done.
  _Verification commands:_
  - File updated
  _Notes:_ Update incrementally as progress is made.

- [ ] T-006-46 – Create PR description with screenshots.
  _Intent:_ Document feature summary, add screenshots showing filter empty/active/hover states, testing notes.
  _Verification commands:_
  - PR description complete
  _Notes:_ Include screenshots for various filter states, note dependency on Feature 001.

---

## Notes / TODOs

**Environment setup:**
- Ensure Node.js and npm are up to date
- Run `npm install` to install dependencies before starting
- Verify Feature 001 (Photo Star Rating) is complete and deployed

**Testing strategy:**
- Prefer unit tests for filter logic (fast feedback)
- Manual integration testing for user flows
- Performance testing with 1000+ photos

**Deferred items (out of scope for Feature 006):**
- Exact rating match filtering (show only N-star photos)
- Explicit "Unrated" filter option
- Multi-select rating filter (checkboxes)
- LocalStorage or URL query parameter persistence
- Backend filtering (API query parameters)
- Filter by aggregate rating (average from all users)

**Common commands:**
- `npm run format` - Format frontend code (Prettier)
- `npm run check` - Run frontend tests and TypeScript type checking
- `npm run dev` - Start local development server

**Potential blockers:**
- Feature 001 (Photo Star Rating) not complete → blocking dependency
- PhotosState store structure different than expected → may need refactoring
- PrimeVue star icons not available → choose alternative icons
- Performance issues with 1000+ photos → may need optimization (virtualization)

**Dependencies:**
- **Feature 001 (Photo Star Rating):** Required for user_rating field. Verify completion before starting.

---

_Last updated: 2026-01-14_
