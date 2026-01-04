# Feature Plan 005 – Album List View Toggle

_Linked specification:_ `docs/specs/4-architecture/features/005-album-list-view/spec.md`
_Status:_ Draft
_Last updated:_ 2026-01-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:**
Users with many albums or albums with long names can now switch to a list view that prioritizes information density and scannability. Full album names are displayed without truncation, and metadata (photo count, sub-album count) is visible at a glance without requiring hover or navigation.

**Success Signals:**
- Toggle control is discoverable and functional in AlbumHero.vue icon row
- List view displays all required information (thumbnail, full name, counts) in horizontal rows
- View preference persists across page reloads via localStorage
- No performance degradation when rendering 100+ albums in list view
- Responsive layout adapts gracefully on mobile devices

**Quality Bars:**
- Code follows Vue 3 Composition API and TypeScript conventions (NFR-005-04)
- Toggle control is keyboard-accessible with proper aria-labels (NFR-005-03)
- View mode loads synchronously from localStorage without blocking album data fetch (NFR-005-01)
- List view rendering completes within 300ms for 100 albums (NFR-005-02)

## Scope Alignment

**In scope:**
- New AlbumListView.vue component for rendering albums in horizontal list rows
- New AlbumListItem.vue component for individual list row rendering
- Modifications to AlbumHero.vue to add grid/list toggle buttons
- Modifications to AlbumThumbPanel.vue to conditionally render grid or list view
- LycheeState.ts modifications to add album_view_mode state with localStorage persistence
- Responsive design for mobile breakpoints (smaller thumbnails, compact layout)
- Keyboard accessibility for toggle controls
- Visual regression tests and component unit tests

**Out of scope:**
- Backend API changes or database schema modifications
- Per-album view preferences (global preference only)
- Sorting or filtering capabilities specific to list view
- Customizable column layout or field selection
- Photo-level list view (feature is album-only)
- Multi-device sync of view preference (localStorage only, not synced to user settings)
- Advanced list features (drag-and-drop reordering, column resizing, etc.)

## Dependencies & Interfaces

**Frontend Dependencies:**
- Vue 3 (Composition API)
- TypeScript
- Tailwind CSS for styling
- PrimeVue for icons and accessibility utilities
- LycheeState.ts store (state management)
- AlbumState.ts store (album data)
- Existing Album model types (AlbumResource)

**Components:**
- AlbumHero.vue (existing - will be modified)
- AlbumThumbPanel.vue (existing - will be modified)
- AlbumThumbPanelList.vue (existing - for comparison/reference)
- AlbumThumb.vue (existing - for comparison/reference)

**Interfaces:**
- Album data structure from AlbumState.ts (id, title, thumb, num_photos, num_children, badges)
- Router navigation (existing)

**Testing Infrastructure:**
- Vitest (component tests)
- Vue Test Utils
- Visual regression testing setup (if available)

## Assumptions & Risks

**Assumptions:**
- Album data structure includes `num_photos` and `num_children` fields (confirmed via exploration)
- PrimeVue icons (`pi-th`, `pi-list`) are available for toggle buttons
- localStorage is available in all supported browsers (graceful degradation if unavailable)
- Existing album rendering infrastructure supports custom layouts

**Risks / Mitigations:**

| Risk | Impact | Mitigation |
|------|--------|-----------|
| LocalStorage unavailable in private browsing mode | Medium - view preference won't persist | Default to grid view, feature still functional |
| Performance degradation with 1000+ albums | Medium - slow rendering | Test with large datasets, consider virtualization if needed (defer to follow-up) |
| Mobile layout complexity | Low - UI crowding on small screens | Use responsive Tailwind breakpoints, test on actual devices |
| Toggle button placement conflicts with existing icons | Low - UI crowding | Verify visual spacing, consider icon-only on mobile |
| TypeScript type mismatches in new components | Low - compile errors | Follow existing patterns from AlbumThumb.vue and AlbumThumbPanelList.vue |

## Implementation Drift Gate

**Drift Detection Strategy:**
- Before each increment, verify the specification FR/NFR requirements still match the planned work
- After each increment, confirm deliverables align with success criteria
- Record any deviations or clarifications in this plan's appendix

**Evidence Collection:**
- Component tests pass (`npm run check`)
- Visual screenshots of grid vs list views (desktop + mobile)
- Performance measurements (rendering time for 100 albums)
- Accessibility audit results (keyboard navigation, aria-labels)

**Commands to Rerun:**
- `npm run format` - Frontend formatting
- `npm run check` - Frontend tests and type checking
- `npm run dev` - Local development server for manual testing

## Increment Map

### I1 – LycheeState Store Modifications (View Mode State)

**Goal:** Add album view mode state to LycheeState.ts with localStorage persistence

**Preconditions:** None (foundational increment)

**Steps:**
1. Read existing LycheeState.ts to understand structure
2. Add `album_view_mode: "grid" | "list"` property (default: "grid")
3. Add computed getter `albumViewMode`
4. Add action `setAlbumViewMode(mode: "grid" | "list")` that:
   - Updates state
   - Writes to localStorage key `album_view_mode`
5. Add initialization logic in store setup to read from localStorage on mount
6. Write unit test for localStorage read/write behavior

**Commands:**
- `npm run check` (verify TypeScript types and tests)

**Exit:** LycheeState has album_view_mode state, localStorage persistence works, tests pass

**Implements:** FR-005-04, NFR-005-01, S-005-03, S-005-04

---

### I2 – AlbumListItem Component (Individual Row)

**Goal:** Create reusable component for single album list row

**Preconditions:** I1 complete (state management ready)

**Steps:**
1. Create `resources/js/components/gallery/albumModule/AlbumListItem.vue`
2. Define props interface (album: AlbumResource, aspectRatio for thumb)
3. Implement template structure:
   - Router-link wrapper for navigation (FR-005-02)
   - 64px square thumbnail (left) - use existing AlbumThumbImage component
   - Album title (full, untruncated, text-wrap allowed)
   - Photo count display (icon + text or text only)
   - Sub-album count display (icon + text or text only)
   - Badge display (NSFW, password, etc.) - reuse existing badge logic
4. Apply Tailwind styling:
   - Flex row layout: `flex items-center gap-4`
   - Hover state: `hover:bg-gray-100 dark:hover:bg-gray-800`
   - Border separator: `border-b border-gray-200 dark:border-gray-700`
5. Add responsive mobile styles:
   - Smaller thumbnail on mobile: `md:w-16 md:h-16 w-12 h-12`
   - Compact count layout
6. Write component unit test with sample album data

**Commands:**
- `npm run check` (tests + types)
- `npm run format` (code formatting)

**Exit:** AlbumListItem renders correctly with all required information, responsive, tests pass

**Implements:** FR-005-01, FR-005-02, FR-005-05, FR-005-06, S-005-05, S-005-06, S-005-07, S-005-08, S-005-09

---

### I3 – AlbumListView Component (List Container)

**Goal:** Create container component that renders albums as list using AlbumListItem

**Preconditions:** I2 complete (AlbumListItem ready)

**Steps:**
1. Create `resources/js/components/gallery/albumModule/AlbumListView.vue`
2. Define props interface (albums: AlbumResource[], aspectRatio: AspectRatioCSSType)
3. Implement template:
   - Wrapper div with appropriate classes
   - v-for loop over albums array
   - Render AlbumListItem for each album
   - Handle empty state (no albums)
4. Apply styling for list container:
   - Flex column layout: `flex flex-col w-full`
   - Spacing between rows handled by AlbumListItem borders
5. Handle click events (delegate to AlbumListItem router-link)
6. Handle context menu events (if needed - match grid behavior)
7. Write component test with multiple albums

**Commands:**
- `npm run check`
- `npm run format`

**Exit:** AlbumListView renders array of albums as list, navigation works, tests pass

**Implements:** FR-005-01, S-005-05, S-005-06

---

### I4 – AlbumThumbPanel Modifications (Conditional Rendering)

**Goal:** Update AlbumThumbPanel.vue to conditionally render grid or list based on view mode

**Preconditions:** I1, I3 complete (state management + list view ready)

**Steps:**
1. Read existing AlbumThumbPanel.vue to understand structure
2. Import AlbumListView component
3. Import LycheeState store to access albumViewMode
4. Add computed property to read current view mode from store
5. Update template to conditionally render:
   - AlbumThumbPanelList (existing) when mode === "grid"
   - AlbumListView (new) when mode === "list"
6. Ensure both views receive same props (albums, aspectRatio, etc.)
7. Manually test toggle behavior (switch between views)

**Commands:**
- `npm run check`
- `npm run format`
- `npm run dev` (manual testing)

**Exit:** AlbumThumbPanel correctly switches between grid and list views, no regression

**Implements:** S-005-01, S-005-02, S-005-04

---

### I5 – AlbumHero Toggle Buttons (UI Controls)

**Goal:** Add grid/list toggle buttons to AlbumHero.vue icon row

**Preconditions:** I1, I4 complete (state management + conditional rendering ready)

**Steps:**
1. Read existing AlbumHero.vue to understand icon row structure (line 33)
2. Import LycheeState store
3. Add computed property to read current view mode
4. Add two new `<a>` elements in the flex-row-reverse container:
   - Grid icon button (`pi-th` or similar)
   - List icon button (`pi-list` or similar)
5. Apply existing icon styling pattern:
   - Base: `shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color`
   - Active state: Different color or styling when selected
6. Add click handlers that call `lycheeStore.setAlbumViewMode('grid' | 'list')`
7. Add aria-labels for accessibility:
   - Grid: `aria-label="Switch to grid view"`
   - List: `aria-label="Switch to list view"`
8. Add aria-pressed attribute based on active state
9. Add tooltips (v-tooltip) similar to other icons
10. Test keyboard navigation (Tab to focus, Enter to activate)

**Commands:**
- `npm run check`
- `npm run format`
- `npm run dev` (manual testing)

**Exit:** Toggle buttons visible, clickable, toggle view mode, keyboard accessible, aria-labels present

**Implements:** FR-005-03, NFR-005-03, S-005-01, S-005-02, UI-005-03

---

### I6 – Responsive Mobile Layout Testing

**Goal:** Verify and refine mobile responsive layout for list view

**Preconditions:** I2, I3, I4, I5 complete (all components implemented)

**Steps:**
1. Test on various mobile viewport sizes:
   - 320px (very narrow)
   - 375px (iPhone SE)
   - 768px (tablet)
2. Verify thumbnail sizes adjust (48px on mobile)
3. Verify album names wrap appropriately
4. Verify counts display compactly (may stack or inline)
5. Verify toggle buttons are usable on mobile
6. Make CSS adjustments if needed (use md: breakpoints)
7. Take screenshots for documentation

**Commands:**
- `npm run dev` (test in browser DevTools responsive mode)

**Exit:** List view renders correctly on all mobile breakpoints, no layout overflow

**Implements:** FR-005-06, S-005-09, UI-005-05

---

### I7 – Component Unit Tests

**Goal:** Add comprehensive unit tests for new components

**Preconditions:** I2, I3 complete (components implemented)

**Steps:**
1. Create test file for AlbumListItem:
   - Test rendering with sample album data
   - Test click navigation behavior
   - Test badge display
   - Test long album name wrapping
   - Test 0 photos / 0 sub-albums edge cases
2. Create test file for AlbumListView:
   - Test rendering multiple albums
   - Test empty state
   - Test props passing to AlbumListItem
3. Create test file for LycheeState view mode:
   - Test default value (grid)
   - Test setAlbumViewMode updates state
   - Test localStorage read/write (mock localStorage)
4. Create fixture file `albums-list-view.json` with sample data

**Commands:**
- `npm run check` (run all tests)

**Exit:** All unit tests pass, coverage for new components

**Implements:** Test strategy from spec, S-005-07, S-005-08

---

### I8 – Integration Testing & Visual Regression

**Goal:** Test end-to-end toggle behavior and capture visual baselines

**Preconditions:** I5, I6 complete (full feature implemented)

**Steps:**
1. Manual integration testing:
   - Load album page in grid view
   - Click list toggle → verify switch
   - Click grid toggle → verify switch back
   - Reload page → verify preference persisted
   - Test in private browsing mode → verify defaults to grid
2. Visual regression testing (if tooling available):
   - Capture screenshot of grid view (desktop)
   - Capture screenshot of list view (desktop)
   - Capture screenshot of list view (mobile)
   - Store as baseline images
3. Accessibility testing:
   - Keyboard navigation through toggle buttons
   - Screen reader testing (if available)
   - Verify aria-labels and aria-pressed

**Commands:**
- `npm run dev` (manual testing)
- Visual regression tool commands (if available)

**Exit:** Toggle behavior works end-to-end, visual baselines captured, accessibility verified

**Implements:** S-005-01, S-005-02, S-005-03, S-005-04, S-005-10, NFR-005-03

---

### I9 – Documentation Updates

**Goal:** Update knowledge map and spec documentation

**Preconditions:** I8 complete (feature fully implemented and tested)

**Steps:**
1. Update [docs/specs/4-architecture/knowledge-map.md](docs/specs/4-architecture/knowledge-map.md):
   - Add AlbumListView.vue component entry
   - Add AlbumListItem.vue component entry
   - Note AlbumHero.vue modifications
   - Note LycheeState.ts modifications
2. Update spec.md status to "Implemented"
3. Archive resolved open questions (already done)
4. Create PR description with:
   - Feature summary
   - Screenshots (grid vs list views)
   - Testing notes

**Commands:**
- None (documentation updates)

**Exit:** Knowledge map updated, documentation current

**Implements:** Documentation deliverables from spec

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-005-01 | I5 | User clicks list toggle → view switches, localStorage updated |
| S-005-02 | I5 | User clicks grid toggle → view switches, localStorage updated |
| S-005-03 | I1 | User loads page, no localStorage → defaults to grid |
| S-005-04 | I1, I4 | User loads page with localStorage "list" → list view displayed |
| S-005-05 | I2, I3 | User clicks list row → navigates to album detail |
| S-005-06 | I2, I7 | Long album name (50+ chars) → full name displayed with wrapping |
| S-005-07 | I2, I7 | Album with 0 photos → shows "0 photos" |
| S-005-08 | I2, I7 | Album with badges → badges visible in row |
| S-005-09 | I6 | Mobile toggle → responsive layout with smaller thumbnails |
| S-005-10 | I8 | Private browsing → toggle works, resets to grid on reload |

## Analysis Gate

**Status:** Not yet executed (spec just created)

**Checklist to complete before implementation:**
- [ ] Review spec.md for completeness (all FR/NFR defined)
- [ ] Verify all open questions resolved (Q-005-01, Q-005-02, Q-005-03 - ✅ DONE)
- [ ] Confirm existing components can be extended without breaking changes
- [ ] Verify TypeScript type definitions are sufficient
- [ ] Check for potential conflicts with other active features (Feature 004)
- [ ] Review mobile responsive requirements against existing breakpoint strategy

**Findings:** (To be filled after analysis gate execution)

## Exit Criteria

Before declaring Feature 005 complete, the following must pass:

- [ ] All increments (I1-I9) completed successfully
- [ ] `npm run format` passes (frontend code formatting)
- [ ] `npm run check` passes (frontend tests and TypeScript type checking)
- [ ] Manual testing confirms:
  - [ ] Toggle buttons visible and functional in AlbumHero.vue
  - [ ] Grid view displays albums in card layout (existing behavior)
  - [ ] List view displays albums in horizontal rows with all required info
  - [ ] View preference persists across page reloads
  - [ ] Keyboard navigation works (Tab to toggle, Enter to activate)
  - [ ] Mobile responsive layout works on narrow screens
- [ ] Visual regression baselines captured (if tooling available)
- [ ] Accessibility audit passes (aria-labels, keyboard navigation)
- [ ] Documentation updated (knowledge map, spec status)
- [ ] No performance regression (rendering 100 albums < 300ms)

## Follow-ups / Backlog

**Potential enhancements (defer to future features):**
- Virtualization for 1000+ albums to improve performance
- Sortable columns in list view (click column header to sort)
- Customizable column layout (show/hide fields)
- Backend persistence of view preference (sync across devices)
- Per-album view preference (remember different views for different albums)
- Drag-and-drop album reordering in list view
- Bulk selection checkboxes in list view
- Column resizing in list view
- Export list view data to CSV

**Monitoring & Metrics:**
- Track localStorage usage/failures (if telemetry added in future)
- Monitor view mode distribution (how many users prefer list vs grid)
- Performance metrics for large album counts

**Known Limitations:**
- View preference not synced across devices (localStorage only)
- Private browsing mode loses preference on reload (acceptable trade-off)
- No column customization in initial version (fixed layout)

---

_Last updated: 2026-01-03_
