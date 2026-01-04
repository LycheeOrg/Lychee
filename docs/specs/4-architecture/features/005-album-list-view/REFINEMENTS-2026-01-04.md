# Feature 005 Refinements Summary

**Date:** 2026-01-04
**Status:** Specification and Plan Updated

## User Requirements

The user requested the following refinements to Feature 005 (Album List View Toggle):

1. **Icon Selection:** Use `pi-th-large` (grid view) and `pi-list` (list view) for the toggle buttons
2. **Albums Page Toggle:** Add view toggle to the Albums page (AlbumsHeader.vue) in addition to album detail view (AlbumHero.vue)
3. **RTL Support:** List rows should be left-aligned in LTR mode and right-aligned in RTL mode
4. **Selection Support:** Albums must be selectable in list view just like in tile/grid mode (with Ctrl/Cmd/Shift modifiers)
5. **Drag-Select Support:** The drag&select overlay (SelectDrag component) must work in list view

## Changes Applied to Specification

### spec.md Updates

1. **Overview Section:**
   - Added mention of AlbumsHeader toggle placement
   - Added RTL alignment requirement
   - Added selection and drag-select support

2. **Functional Requirements (FR):**
   - **FR-005-01:** Updated to specify left-aligned (LTR) / right-aligned (RTL) row layout
   - **FR-005-03:** Updated to specify `pi-th-large` and `pi-list` icons, toggle in both AlbumHero and AlbumsHeader
   - **FR-005-07 (NEW):** Albums must be selectable in list view with identical behavior to grid view
   - **FR-005-08 (NEW):** Drag-select overlay must work in list view

3. **UI Mockups:**
   - Added RTL mode mockup showing right-aligned thumbnails and right-to-left text flow
   - Added AlbumsHeader toggle mockup
   - Updated icon representation in mockups ([⊞] for grid, [≡] for list)
   - Added notes about selection and drag-select behavior

4. **Scenario Matrix:**
   - **S-005-01, S-005-02:** Updated to mention AlbumsHeader in addition to AlbumHero
   - **S-005-11 (NEW):** User in RTL mode switches to list view → thumbnails right-aligned
   - **S-005-12 (NEW):** User clicks album with Ctrl/Cmd in list view → album selected
   - **S-005-13 (NEW):** User drag-selects multiple albums in list view → SelectDrag overlay works
   - **S-005-14 (NEW):** Selection state persists when switching between grid and list views

5. **Implementation Notes:**
   - Added "Refinements (2026-01-04)" section documenting user requests
   - Updated component architecture to include AlbumsHeader.vue modifications
   - Updated styling considerations with RTL support classes:
     - `ltr:flex-row rtl:flex-row-reverse` for row direction
     - `ltr:text-left rtl:text-right` for text alignment
     - `ltr:justify-start rtl:justify-end` for container alignment
   - Added selection state styling: `bg-primary-100 dark:bg-primary-900 ring-2 ring-primary-500`

## Implementation Impact

### New Components to Modify

1. **AlbumsHeader.vue** (NEW in this refinement)
   - Location: [resources/js/components/headers/AlbumsHeader.vue](../../../../../../resources/js/components/headers/AlbumsHeader.vue)
   - Changes: Add grid/list toggle buttons to the `menu` computed property (similar to existing items)
   - Pattern: Follow existing button pattern in template #end section

### Updated Component Requirements

1. **AlbumListItem.vue**
   - Must support click-to-select behavior (propagate click events with modifier keys)
   - Must apply selection state styling when album is in selectedAlbumsIds array
   - Must support RTL layout with `ltr:` and `rtl:` Tailwind classes

2. **AlbumListView.vue**
   - Must work with SelectDrag component overlay
   - Must pass selection props to AlbumListItem components
   - Must emit selection events (clicked, contexted)

3. **AlbumThumbPanel.vue**
   - Already handles selection state, should work with list view without changes
   - Verify SelectDrag component overlay positions correctly over list rows

### PrimeVue Icon Verification

Icons to use:
- **Grid view:** `pi pi-th-large`
- **List view:** `pi pi-list`

These are standard PrimeVue icons available in the icon library.

## Testing Additions

New test scenarios required:

1. **RTL Mode Tests:**
   - Verify list rows are right-aligned in RTL mode
   - Verify thumbnails appear on the right side
   - Verify text flows right-to-left

2. **Selection Tests:**
   - Click album in list view → navigate (no modifiers)
   - Ctrl+Click album → select/deselect album
   - Shift+Click album → range select
   - Cmd+Click album (macOS) → select/deselect album

3. **Drag-Select Tests:**
   - Click and drag in list view → SelectDrag overlay appears
   - Drag over multiple albums → albums within overlay are selected
   - Release drag → selection state persists

4. **AlbumsHeader Toggle Tests:**
   - Click grid toggle in AlbumsHeader → all album panels switch to grid
   - Click list toggle in AlbumsHeader → all album panels switch to list
   - Toggle state persists to localStorage

5. **Cross-View Tests:**
   - Select albums in grid view → switch to list → selection persists
   - Select albums in list view → switch to grid → selection persists

## Files Modified

### Specification Files
- ✅ [docs/specs/4-architecture/features/005-album-list-view/spec.md](spec.md)
- ⏳ [docs/specs/4-architecture/features/005-album-list-view/plan.md](plan.md) - Needs update
- ⏳ [docs/specs/4-architecture/features/005-album-list-view/tasks.md](tasks.md) - Needs update

### Implementation Files (To Be Modified)
- ⏳ resources/js/stores/LycheeState.ts
- ⏳ resources/js/components/gallery/albumModule/AlbumListView.vue (NEW)
- ⏳ resources/js/components/gallery/albumModule/AlbumListItem.vue (NEW)
- ⏳ resources/js/components/gallery/albumModule/AlbumThumbPanel.vue
- ⏳ resources/js/components/gallery/albumModule/AlbumHero.vue
- ⏳ resources/js/components/headers/AlbumsHeader.vue (NEW in refinement)

## Next Steps

1. Update plan.md with refined increments
2. Update tasks.md with new tasks for:
   - AlbumsHeader toggle implementation
   - RTL layout support
   - Selection behavior in list view
   - Drag-select support
3. Begin implementation starting with LycheeState store modifications

## Notes

- The core architecture remains the same (localStorage preference, conditional rendering)
- RTL support is achieved purely through Tailwind CSS classes (no logic changes)
- Selection behavior already exists in AlbumThumbPanel.vue, needs to be passed through to list components
- SelectDrag component should work automatically if AlbumListItem emits proper click events with bounding rectangles

---

*Documented by: AI Agent*
*Reviewed by: Pending user approval*
