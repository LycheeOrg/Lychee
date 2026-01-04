# Feature 005 Tasks – Album List View Toggle

_Status: Draft_
_Last updated: 2026-01-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`N-`), and scenario IDs (`S-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I1 – LycheeState Store Modifications

- [ ] T-005-01 – Add album_view_mode state property to LycheeState.ts (FR-005-04, NFR-005-01, S-005-03, S-005-04).
  _Intent:_ Add `album_view_mode: "grid" | "list"` property with default value "grid" to LycheeState.ts store.
  _Verification commands:_
  - `npm run check` (TypeScript compilation)
  _Notes:_ Property should be reactive, accessible via computed getter.

- [ ] T-005-02 – Add setAlbumViewMode action to LycheeState.ts (FR-005-04, S-005-01, S-005-02).
  _Intent:_ Implement action `setAlbumViewMode(mode: "grid" | "list")` that updates state and writes to localStorage.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use localStorage key `album_view_mode`, handle localStorage unavailability gracefully.

- [ ] T-005-03 – Add localStorage initialization logic to LycheeState.ts (FR-005-04, NFR-005-01, S-005-04).
  _Intent:_ On store setup/mount, read from localStorage and initialize album_view_mode state.
  _Verification commands:_
  - `npm run check`
  - Manual test: Set localStorage, reload page, verify state initialized correctly
  _Notes:_ Default to "grid" if localStorage unavailable or key not found.

- [ ] T-005-04 – Write unit tests for LycheeState view mode state and localStorage persistence (S-005-03, S-005-04, S-005-10).
  _Intent:_ Test default value, setAlbumViewMode updates, localStorage read/write behavior (mock localStorage).
  _Verification commands:_
  - `npm run check` (includes unit tests)
  _Notes:_ Mock localStorage API for tests, test both success and unavailable scenarios.

---

### Increment I2 – AlbumListItem Component

- [ ] T-005-05 – Create AlbumListItem.vue component skeleton (FR-005-01, FR-005-02).
  _Intent:_ Create new file `resources/js/components/gallery/albumModule/AlbumListItem.vue` with basic Vue 3 Composition API structure, props interface (album: AlbumResource).
  _Verification commands:_
  - `npm run check` (TypeScript compilation)
  _Notes:_ Import AlbumResource type from existing types.

- [ ] T-005-06 – Implement AlbumListItem template structure (FR-005-01, FR-005-02, S-005-05).
  _Intent:_ Add router-link wrapper, thumbnail slot (use AlbumThumbImage component), album title, photo count, sub-album count sections.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (visual inspection)
  _Notes:_ Use Tailwind classes for layout: `flex items-center gap-4`.

- [ ] T-005-07 – Add styling to AlbumListItem (FR-005-01, FR-005-06).
  _Intent:_ Apply Tailwind CSS: hover state, border separator, responsive thumbnail sizes (64px desktop, 48px mobile).
  _Verification commands:_
  - `npm run format`
  - `npm run dev` (visual inspection)
  _Notes:_ Classes: `hover:bg-gray-100 dark:hover:bg-gray-800`, `border-b border-gray-200 dark:border-gray-700`, `md:w-16 md:h-16 w-12 h-12` for thumbnail.

- [ ] T-005-08 – Add badge display to AlbumListItem (FR-005-05, S-005-08).
  _Intent:_ Reuse existing badge logic from AlbumThumb.vue to display NSFW, password, public badges in list row.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (test with albums that have badges)
  _Notes:_ Position badges adjacent to thumbnail or album name.

- [ ] T-005-09 – Write unit tests for AlbumListItem (S-005-06, S-005-07, S-005-08).
  _Intent:_ Test rendering with sample album data, long album names, 0 photos/sub-albums, badge display.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Create test fixture with edge cases (long names, 0 counts, multiple badges).

---

### Increment I3 – AlbumListView Component

- [ ] T-005-10 – Create AlbumListView.vue component skeleton (FR-005-01).
  _Intent:_ Create new file `resources/js/components/gallery/albumModule/AlbumListView.vue` with props interface (albums: AlbumResource[], aspectRatio: AspectRatioCSSType).
  _Verification commands:_
  - `npm run check`
  _Notes:_ Import AlbumListItem component.

- [ ] T-005-11 – Implement AlbumListView template with v-for loop (FR-005-01, S-005-05).
  _Intent:_ Render AlbumListItem for each album in albums array, handle empty state.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (test with sample albums)
  _Notes:_ Use flex column layout: `flex flex-col w-full`.

- [ ] T-005-12 – Write unit tests for AlbumListView (S-005-06).
  _Intent:_ Test rendering multiple albums, empty state, props passing to AlbumListItem.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use fixture data from FX-005-01.

---

### Increment I4 – AlbumThumbPanel Modifications

- [ ] T-005-13 – Import AlbumListView and LycheeState in AlbumThumbPanel.vue (S-005-01, S-005-02).
  _Intent:_ Add necessary imports to conditionally render grid or list view.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Read existing AlbumThumbPanel.vue to understand structure before modifying.

- [ ] T-005-14 – Add computed property for view mode in AlbumThumbPanel.vue (S-005-04).
  _Intent:_ Read album_view_mode from LycheeState store via computed property.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use `const lycheeStore = useLycheeStateStore()`.

- [ ] T-005-15 – Update AlbumThumbPanel template to conditionally render grid or list (S-005-01, S-005-02).
  _Intent:_ Use v-if to render AlbumThumbPanelList when mode === "grid", AlbumListView when mode === "list".
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (manually toggle view mode in localStorage, verify switch)
  _Notes:_ Ensure both components receive same props (albums array, etc.).

---

### Increment I5 – AlbumHero Toggle Buttons

- [ ] T-005-16 – Add grid and list toggle button elements to AlbumHero.vue (FR-005-03, S-005-01, S-005-02).
  _Intent:_ Add two `<a>` elements in the flex-row-reverse icon container (line 33) with grid/list icons (PrimeVue pi-th, pi-list).
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (visual inspection - buttons appear in icon row)
  _Notes:_ Follow existing icon pattern: `shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color`.

- [ ] T-005-17 – Add click handlers to toggle buttons in AlbumHero.vue (FR-005-03, S-005-01, S-005-02).
  _Intent:_ Implement @click handlers that call `lycheeStore.setAlbumViewMode('grid' | 'list')`.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (click buttons, verify view switches, localStorage updated)
  _Notes:_ Import LycheeState store at top of component.

- [ ] T-005-18 – Add active state styling to toggle buttons in AlbumHero.vue (FR-005-03, UI-005-01, UI-005-02).
  _Intent:_ Apply different styling when button is active (current view mode), e.g., different color or text-primary-emphasis.
  _Verification commands:_
  - `npm run dev` (visual inspection - active button highlighted)
  _Notes:_ Use computed property or v-bind:class based on current view mode.

- [ ] T-005-19 – Add aria-labels and aria-pressed to toggle buttons (NFR-005-03, UI-005-03).
  _Intent:_ Add accessibility attributes: aria-label="Switch to grid view" / "Switch to list view", aria-pressed based on active state.
  _Verification commands:_
  - `npm run check`
  - Manual accessibility audit (keyboard navigation, screen reader)
  _Notes:_ Ensure buttons are keyboard-navigable (Tab to focus, Enter to activate).

- [ ] T-005-20 – Add tooltips to toggle buttons in AlbumHero.vue (FR-005-03).
  _Intent:_ Add v-tooltip.bottom directives similar to other icons in AlbumHero.vue.
  _Verification commands:_
  - `npm run dev` (hover over buttons, verify tooltips appear)
  _Notes:_ Tooltip text: "Grid view" / "List view".

---

### Increment I6 – Responsive Mobile Layout Testing

- [ ] T-005-21 – Test list view on 320px viewport (FR-005-06, S-005-09).
  _Intent:_ Verify layout doesn't overflow, thumbnails scale to 48px, album names wrap, counts display compactly.
  _Verification commands:_
  - `npm run dev` (browser DevTools responsive mode, set to 320px width)
  _Notes:_ Make CSS adjustments if needed, use Tailwind md: breakpoints.

- [ ] T-005-22 – Test list view on 375px and 768px viewports (FR-005-06, S-005-09, UI-005-05).
  _Intent:_ Verify responsive behavior at common mobile breakpoints.
  _Verification commands:_
  - `npm run dev` (test multiple viewport sizes)
  _Notes:_ Capture screenshots for documentation.

- [ ] T-005-23 – Test toggle buttons on mobile viewports (FR-005-03, S-005-09).
  _Intent:_ Verify toggle buttons remain usable and don't crowd header on mobile.
  _Verification commands:_
  - `npm run dev` (mobile testing)
  _Notes:_ Consider icon-only display on very narrow screens if needed.

---

### Increment I7 – Component Unit Tests

- [ ] T-005-24 – Create fixture file albums-list-view.json (FX-005-01).
  _Intent:_ Create `resources/js/components/gallery/albumModule/__tests__/fixtures/albums-list-view.json` with sample album data (long names, 0 counts, badges, high counts).
  _Verification commands:_
  - File exists and is valid JSON
  _Notes:_ Include edge cases mentioned in spec.

- [ ] T-005-25 – Write unit tests for AlbumListItem component (S-005-05, S-005-06, S-005-07, S-005-08).
  _Intent:_ Test rendering with various album data scenarios, navigation behavior, badge display.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use Vue Test Utils, test props passing and rendering output.

- [ ] T-005-26 – Write unit tests for AlbumListView component (S-005-06).
  _Intent:_ Test rendering multiple albums, empty state, props passing.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Verify correct number of AlbumListItem components rendered.

- [ ] T-005-27 – Write integration tests for view mode toggle (S-005-01, S-005-02, S-005-03, S-005-04).
  _Intent:_ Test end-to-end toggle behavior, localStorage persistence, default value.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Mock localStorage for tests, test both available and unavailable scenarios.

---

### Increment I8 – Integration Testing & Visual Regression

- [ ] T-005-28 – Manual integration testing: toggle between views (S-005-01, S-005-02).
  _Intent:_ Load album page, click list toggle, verify switch, click grid toggle, verify switch back.
  _Verification commands:_
  - `npm run dev` (manual testing)
  _Notes:_ Test with real album data, various album counts.

- [ ] T-005-29 – Manual integration testing: localStorage persistence (S-005-04, S-005-10).
  _Intent:_ Set view to list, reload page, verify still in list view. Test private browsing mode (defaults to grid on reload).
  _Verification commands:_
  - `npm run dev` (manual testing with page reloads)
  _Notes:_ Clear localStorage between tests to verify default behavior.

- [ ] T-005-30 – Keyboard accessibility testing (NFR-005-03, UI-005-03).
  _Intent:_ Tab to toggle buttons, verify focus outline, press Enter to activate, verify view switches.
  _Verification commands:_
  - Manual keyboard navigation testing
  _Notes:_ Test with screen reader if available, verify aria-labels announced.

- [ ] T-005-31 – Visual regression baseline capture (optional, if tooling available).
  _Intent:_ Capture screenshots of grid view (desktop), list view (desktop), list view (mobile) as baseline images.
  _Verification commands:_
  - Visual regression tool commands
  _Notes:_ Store baselines for future regression testing.

- [ ] T-005-32 – Performance testing with 100 albums (NFR-005-02).
  _Intent:_ Load album with 100+ albums, measure rendering time, verify < 300ms for list view.
  _Verification commands:_
  - Browser DevTools Performance tab
  _Notes:_ Compare grid vs list rendering performance.

---

### Increment I9 – Documentation Updates

- [ ] T-005-33 – Update knowledge-map.md with new components.
  _Intent:_ Add entries for AlbumListView.vue, AlbumListItem.vue, note modifications to AlbumHero.vue and LycheeState.ts.
  _Verification commands:_
  - File updated and readable
  _Notes:_ Follow existing knowledge map format.

- [ ] T-005-34 – Update spec.md status to Implemented.
  _Intent:_ Change status field in spec.md from "Draft" to "Implemented", update last updated date.
  _Verification commands:_
  - File updated
  _Notes:_ Update after all other tasks complete.

- [ ] T-005-35 – Update roadmap.md feature status.
  _Intent:_ Change Feature 005 status from "Planning" to "In Progress" when implementation starts, "Complete" when done.
  _Verification commands:_
  - File updated
  _Notes:_ Update incrementally as progress is made.

- [ ] T-005-36 – Create PR description with screenshots.
  _Intent:_ Document feature summary, add screenshots showing grid vs list views, testing notes.
  _Verification commands:_
  - PR description complete
  _Notes:_ Include before/after screenshots (desktop and mobile).

---

## Notes / TODOs

**Environment setup:**
- Ensure Node.js and npm are up to date
- Run `npm install` to install dependencies before starting

**Testing strategy:**
- Prefer unit tests for components (fast feedback)
- Manual integration testing for user flows
- Visual regression testing optional (if tooling exists)

**Deferred items (out of scope for Feature 005):**
- Virtualization for 1000+ albums (performance optimization)
- Backend persistence of view preference (multi-device sync)
- Sortable columns in list view
- Customizable column layout
- Per-album view preferences

**Common commands:**
- `npm run format` - Format frontend code (Prettier)
- `npm run check` - Run frontend tests and TypeScript type checking
- `npm run dev` - Start local development server

**Potential blockers:**
- If PrimeVue icons (pi-th, pi-list) are not available, choose alternative icons
- If localStorage is restricted (CSP, privacy settings), feature will default to grid view (acceptable)
- If performance with 100+ albums is poor, may need virtualization (defer to follow-up)

---

_Last updated: 2026-01-03_
