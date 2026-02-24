# Feature Plan 014 – Photo List View Toggle

_Linked specification:_ `docs/specs/4-architecture/features/014-photo-list-view/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-02-24

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Provide users with an alternative list-based view for browsing photos within albums, displaying key metadata (title, date, type, size, rating) in a scannable row format. This enhances usability for users who prefer list-style navigation over thumbnail grids.

**Success signals:**
- List view renders correctly with all specified metadata
- Users can seamlessly toggle between thumbnail layouts and list view
- Selection, navigation, and context menu work identically to thumbnail view
- List view is responsive across mobile and desktop viewports
- RTL layout support functions correctly

**Quality bars:**
- No perceptible lag when rendering 100+ photos
- Full keyboard accessibility (Tab, Enter, Space)
- Screen reader compatible with proper ARIA labels
- Dark mode support

## Scope Alignment

### In scope

- PhotoListItem component (individual photo row)
- PhotoListView component (list container)
- List toggle button in PhotoThumbPanelControl
- LayoutState store modification (listClass getter)
- Conditional rendering in PhotoThumbPanelList
- Translation strings for list layout label
- Responsive layouts (mobile/desktop)
- RTL support
- Selection state integration
- Context menu integration

### Out of scope

- Database persistence of list preference
- Admin configuration for default view
- Drag-and-drop reordering
- Photo editing from list view
- Virtual scrolling (may be added later if performance requires)

## Dependencies & Interfaces

### Dependencies

| Dependency | Purpose |
|------------|---------|
| LayoutState.ts | Store for layout mode state |
| PhotoThumbPanelControl.vue | Existing layout toggle buttons (will be modified) |
| PhotoThumbPanelList.vue | Photo display container (will be modified) |
| PhotoThumb.vue | Reference for photo data display patterns |
| AlbumListItem.vue | Reference implementation for list row component |
| AlbumListView.vue | Reference implementation for list container |
| PrimeVue | UI component library (pi-list icon) |
| Tailwind CSS | Styling framework |

### Interfaces

| Interface | Type | Notes |
|-----------|------|-------|
| PhotoResource | TypeScript type | Existing type used for photo data |
| PhotoLayoutType | TypeScript type | Needs 'list' addition (front-end only) |
| LayoutStore | Pinia store | Needs listClass getter |

## Assumptions & Risks

### Assumptions

1. Feature 005 (Album List View) patterns are suitable for photo list view
2. Existing selection/context menu composables can be reused
3. No performance issues with list rendering up to 100 photos
4. PhotoResource contains all necessary data (title, date, type, size, rating)

### Risks / Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Performance with large photo sets | High | Implement virtual scrolling in follow-up if needed; initial implementation limited to reasonable sets |
| Inconsistent styling with album list | Medium | Follow AlbumListItem patterns closely; visual review in both views |
| Selection state issues on view switch | Medium | Test thoroughly with existing selection tests; maintain compatibility |
| Timeline mode integration complexity | Low | Explicitly disable list view in timeline mode; document limitation |

## Implementation Drift Gate

After completing implementation, verify:
1. All scenarios from spec (S-014-01 through S-014-22) are covered by tests or manual verification
2. All FRs and NFRs are satisfied
3. No undocumented behavior added
4. TypeScript compilation passes (`npm run check`)
5. All tests pass (`npm run test`)

Record results in the tasks.md file under verification notes.

## Increment Map

### I1 – LayoutState Store Modification

**Goal:** Add list view support to the layout store

**Preconditions:** None

**Steps:**
1. Add `listClass` getter to `resources/js/stores/LayoutState.ts`
   - Follow pattern of existing getters (squareClass, justifiedClass, etc.)
   - Return active styling when `this.layout === 'list'`
2. Verify store compiles and existing layouts still work

**Commands:**
- `npm run check` (TypeScript compilation)

**Exit:** LayoutState has listClass getter, existing functionality unchanged

---

### I2 – PhotoThumbPanelControl List Button

**Goal:** Add list toggle button to the layout control panel

**Preconditions:** I1 complete

**Steps:**
1. Open `resources/js/components/gallery/albumModule/PhotoThumbPanelControl.vue`
2. Add list toggle button after existing grid button:
   ```vue
   <a class="px-1 cursor-pointer group hidden sm:inline-block h-8" 
      :title="$t('gallery.layout.list')" 
      @click="layout = 'list'">
     <i class="pi pi-list" :class="layoutStore.listClass"></i>
   </a>
   ```
3. Import and use listClass from layoutStore
4. Verify button appears and updates layout state on click

**Commands:**
- `npm run check`
- `npm run dev` (manual verification in browser)

**Exit:** List button visible in control panel, clicking sets layout to 'list'

---

### I3 – Translation String

**Goal:** Add translation for list layout option

**Preconditions:** None (can be done in parallel with I1/I2)

**Steps:**
1. Add `'list' => 'List view',` to the `layout` array in `lang/en/gallery.php`
2. Add corresponding entries to other language files (at minimum: en, de, fr)

**Commands:**
- Verify PHP syntax is valid

**Exit:** Translation string available for list layout button tooltip

---

### I4 – PhotoListItem Component

**Goal:** Create the individual photo row component

**Preconditions:** I1 complete (to understand data flow)

**Steps:**
1. Create `resources/js/components/gallery/albumModule/PhotoListItem.vue`
2. Define props interface:
   ```typescript
   defineProps<{
     photo: App.Http.Resources.Models.PhotoResource;
     isSelected: boolean;
     isCoverId: boolean;
     isHeaderId: boolean;
   }>();
   ```
3. Define emits:
   ```typescript
   defineEmits<{
     clicked: [event: MouseEvent, id: string];
     contexted: [event: MouseEvent, id: string];
   }>();
   ```
4. Implement template with:
   - 48px (mobile) / 64px (desktop) thumbnail (use size_variants.thumb or small)
   - Photo title (truncated)
   - Date taken (preformatted.taken_at) or created_at
   - File type badge (precomputed.is_video, is_livephoto, is_raw)
   - File size (preformatted.filesize)
   - Rating stars (if rating exists)
   - Highlighted badge (if is_highlighted)
   - Cover/Header badges (if applicable)
5. Apply Tailwind styling following AlbumListItem.vue patterns:
   - Row: `flex items-center gap-4 px-3 py-0.5 cursor-pointer hover:bg-primary-400/10`
   - Selection: `:class="{ 'bg-primary-100 dark:bg-primary-900/50': isSelected }"`
   - Responsive: `flex-col md:flex-row` for info section
   - RTL: `ltr:flex-row rtl:flex-row-reverse`

**Commands:**
- `npm run check`

**Exit:** PhotoListItem component created, compiles without errors

---

### I5 – PhotoListView Component

**Goal:** Create container component that renders photos as list

**Preconditions:** I4 complete (PhotoListItem ready)

**Steps:**
1. Create `resources/js/components/gallery/albumModule/PhotoListView.vue`
2. Define props interface:
   ```typescript
   defineProps<{
     photos: App.Http.Resources.Models.PhotoResource[];
     selectedPhotos: string[];
   }>();
   ```
3. Define emits:
   ```typescript
   defineEmits<{
     clicked: [id: string, event: MouseEvent];
     selected: [id: string, event: MouseEvent];
     contexted: [id: string, event: MouseEvent];
     toggleBuyMe: [id: string];
   }>();
   ```
4. Implement template:
   - Wrapper div: `flex flex-col w-full`
   - v-for loop over photos array
   - Render PhotoListItem for each photo with `:is-selected="selectedPhotos.includes(photo.id)"`
   - Handle click events (may-select logic from PhotoThumbPanelList)
   - Forward context menu events
5. Import albumStore for cover_id/header_id checks

**Commands:**
- `npm run check`

**Exit:** PhotoListView component renders list of PhotoListItem components

---

### I6 – PhotoThumbPanelList Integration

**Goal:** Modify PhotoThumbPanelList to conditionally render list or thumbnails

**Preconditions:** I5 complete

**Steps:**
1. Open `resources/js/components/gallery/albumModule/PhotoThumbPanelList.vue`
2. Import PhotoListView component
3. Import layoutStore and get layout state
4. Add conditional rendering:
   ```vue
   <PhotoListView
     v-if="layoutStore.layout === 'list'"
     :photos="props.photos"
     :selected-photos="props.selectedPhotos"
     @clicked="emits('clicked', $event.id, $event.event)"
     @selected="emits('selected', $event.id, $event.event)"
     @contexted="emits('contexted', $event.id, $event.event)"
     @toggle-buy-me="emits('toggleBuyMe', $event)"
   />
   <template v-else>
     <!-- existing PhotoThumb loop -->
   </template>
   ```
5. Ensure selection state passes through correctly

**Commands:**
- `npm run check`
- `npm run dev` (manually test toggle between layouts)

**Exit:** PhotoThumbPanelList renders list view when layout is 'list', thumbnails otherwise

---

### I7 – Unit Tests for PhotoListItem

**Goal:** Write unit tests for PhotoListItem component

**Preconditions:** I4 complete

**Steps:**
1. Create `resources/js/components/gallery/albumModule/__tests__/PhotoListItem.spec.ts`
2. Create test fixture at `__tests__/fixtures/photos-list-view.json`
3. Test cases:
   - Renders photo thumbnail correctly
   - Displays title (with truncation when long)
   - Shows correct date (taken_at or created_at)
   - Shows video badge for video type
   - Shows livephoto badge for live photos
   - Shows RAW badge for raw files
   - Shows rating stars when rating exists
   - Shows highlighted badge when is_highlighted true
   - Shows cover badge when isCoverId true
   - Shows header badge when isHeaderId true
   - Emits clicked event on click
   - Emits contexted event on right-click
   - Selected state applies correct styling
   - Hover state applies correct styling
   - RTL mode renders correctly

**Commands:**
- `npm run test -- PhotoListItem`

**Exit:** All PhotoListItem unit tests pass

---

### I8 – Unit Tests for PhotoListView

**Goal:** Write unit tests for PhotoListView component

**Preconditions:** I5 complete, I7 complete

**Steps:**
1. Create `resources/js/components/gallery/albumModule/__tests__/PhotoListView.spec.ts`
2. Test cases:
   - Renders correct number of PhotoListItem components
   - Passes selection state correctly to child components
   - Emits clicked event with correct photo id
   - Emits selected event on Ctrl+click
   - Emits contexted event on right-click
   - Empty array renders empty container

**Commands:**
- `npm run test -- PhotoListView`

**Exit:** All PhotoListView unit tests pass

---

### I9 – Integration Tests

**Goal:** Test full layout toggle flow

**Preconditions:** I6 complete

**Steps:**
1. Create or update integration test file for PhotoThumbPanel flow
2. Test cases:
   - Clicking list toggle switches to list view
   - Clicking thumbnail toggle (square/justified/masonry/grid) switches back
   - Selection persists when switching between layouts
   - Context menu works in list view
   - List view works correctly in timeline mode (each date group shows list)

**Commands:**
- `npm run test -- PhotoThumbPanel`

**Exit:** All integration tests pass

---

### I10 – Accessibility & RTL Review

**Goal:** Ensure accessibility and RTL support

**Preconditions:** I4, I5, I6 complete

**Steps:**
1. Add aria-labels to PhotoListItem rows
2. Ensure keyboard navigation works (Tab to navigate, Enter to open)
3. Test with screen reader (or automated a11y test)
4. Verify RTL layout renders correctly
5. Add any missing ARIA attributes

**Commands:**
- `npm run check`
- Manual testing with RTL mode enabled

**Exit:** Accessible and RTL-compliant

---

### I11 – Documentation Update

**Goal:** Update project documentation

**Preconditions:** All implementation complete

**Steps:**
1. Update `docs/specs/4-architecture/roadmap.md` with Feature 014 entry
2. Update `docs/specs/4-architecture/knowledge-map.md` with new components
3. Ensure code comments are complete

**Commands:**
- Verify documentation is accurate

**Exit:** Documentation reflects new feature

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-014-01 | I2, I6 / T-014-03, T-014-11 | List toggle → list view display |
| S-014-02 | I2, I6 / T-014-03, T-014-11 | Thumbnail toggle → return to thumbnails |
| S-014-03 | I6 / T-014-11 | Default state (thumbnails) |
| S-014-04 | I1 / T-014-01 | Not persisted (by design) |
| S-014-05 | I4, I5 / T-014-05, T-014-07 | Click → navigate |
| S-014-06 | I5 / T-014-07 | Ctrl+click selection |
| S-014-07 | I5 / T-014-07 | Shift+click range selection |
| S-014-08 | I4, I5 / T-014-05, T-014-07 | Context menu |
| S-014-09 | I4 / T-014-05 | Video badge display |
| S-014-10 | I4 / T-014-05 | Livephoto badge display |
| S-014-11 | I4 / T-014-05 | RAW badge display |
| S-014-12 | I4 / T-014-05 | Rating stars display |
| S-014-13 | I4 / T-014-05 | Highlighted badge display |
| S-014-14 | I4 / T-014-05 | Cover badge display |
| S-014-15 | I4 / T-014-05 | Header badge display |
| S-014-16 | I4, I10 / T-014-05, T-014-20 | RTL layout |
| S-014-17 | I6 / T-014-11, T-014-17 | Selection persistence on view switch |
| S-014-18 | I6, I9 / T-014-11, T-014-18 | Timeline mode with list view |
| S-014-19 | I4 / T-014-05 | Mobile compact layout |
| S-014-20 | I4 / T-014-05 | Desktop full layout |
| S-014-21 | I4 / T-014-05 | Long title truncation |
| S-014-22 | I4 / T-014-05 | Hover state |

## Analysis Gate

_To be completed after spec review_

- [ ] Spec reviewed and approved
- [ ] All FR/NFR/scenarios mapped to increments
- [ ] Dependencies available
- [ ] No blocking open questions

## Exit Criteria

- [ ] All increments (I1-I11) complete
- [ ] All scenarios (S-014-01 through S-014-22) verified
- [ ] TypeScript compilation passes (`npm run check`)
- [ ] All unit tests pass (`npm run test`)
- [ ] Manual verification in browser complete
- [ ] Documentation updated
- [ ] No outstanding lint/type errors

## Follow-ups / Backlog

1. **Virtual scrolling** – If performance issues arise with large photo sets (500+), implement virtual scrolling using vue-virtual-scroller
2. **Column configuration** – Allow users to choose which columns to display (title, date, size, type, rating)
3. **Sortable columns** – Click column header to sort by that field
4. **Persistence** – Add optional localStorage persistence for layout preference
