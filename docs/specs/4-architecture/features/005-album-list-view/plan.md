# Feature Plan 005 – Album List View Toggle

_Linked specification:_ `docs/specs/4-architecture/features/005-album-list-view/spec.md`
_Status:_ Ready for Implementation
_Last updated:_ 2026-01-04

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:**
Users with many albums or albums with long names can now switch to a list view that prioritizes information density and scannability. Full album names are displayed without truncation, and metadata (photo count > 0, sub-album count > 0) is visible at a glance on the same line (wide screens). Admin configures the default view; users can toggle client-side but preference does not persist across reloads.

**Success Signals:**
- Toggle controls are discoverable and functional in AlbumHero.vue and AlbumsHeader.vue
- List view displays all required information (thumbnail, full name, counts if > 0) in horizontal rows
- Counts displayed inline with title on wide screens (≥md), stacked on narrow screens
- Zero counts are hidden (no "0 photos" or "0 sub-albums")
- Default view mode is admin-configurable via Configs UI
- No performance degradation when rendering 100+ albums in list view
- RTL mode properly aligns thumbnails and text
- Albums are selectable in list view (Ctrl/Cmd/Shift modifiers)
- Drag-select overlay works in list view

**Quality Bars:**
- Code follows Vue 3 Composition API and TypeScript conventions (NFR-005-04)
- Toggle control is keyboard-accessible with proper aria-labels (NFR-005-03)
- View mode loads synchronously from InitConfig without blocking album data fetch (NFR-005-01)
- List view rendering completes within 300ms for 100 albums (NFR-005-02)

## Scope Alignment

**In scope:**
- Backend: `BaseConfigMigration` for `album_layout` config (`grid|list`)
- Backend: InitConfig.php modification to expose `album_layout`
- Frontend: LycheeState.ts modification to add `album_view_mode` state (initialized from InitConfig)
- Frontend: AlbumListView.vue component for rendering albums in horizontal list rows
- Frontend: AlbumListItem.vue component for individual list row rendering
- Frontend: AlbumHero.vue modifications to add grid/list toggle buttons (`pi-th-large`, `pi-list`)
- Frontend: AlbumsHeader.vue modifications to add grid/list toggle buttons
- Frontend: AlbumThumbPanel.vue modifications to conditionally render grid or list view
- Wide screen inline layout (title + counts on same line)
- Zero count hiding (only show if > 0)
- RTL support (right-aligned thumbnails, right-to-left text flow)
- Selection support (Ctrl/Cmd/Shift modifiers)
- Drag-select overlay compatibility
- Responsive design for mobile breakpoints (smaller thumbnails, stacked counts)
- Keyboard accessibility for toggle controls
- Component unit tests

**Out of scope:**
- User preference persistence (reloads reset to admin default)
- localStorage usage (removed from original plan)
- Backend API endpoints for user settings
- Per-album view preferences (global preference only)
- Sorting or filtering capabilities specific to list view
- Customizable column layout or field selection
- Photo-level list view (feature is album-only)

## Dependencies & Interfaces

**Backend Dependencies:**
- Laravel migration system (`BaseConfigMigration`)
- Existing Configs table and management UI
- InitConfig.php resource

**Frontend Dependencies:**
- Vue 3 (Composition API)
- TypeScript
- Tailwind CSS for styling (including RTL support with `ltr:` and `rtl:` prefixes)
- PrimeVue for icons (`pi-th-large`, `pi-list`) and accessibility utilities
- LycheeState.ts store (state management)
- AlbumsState.ts store (album data)
- Existing Album model types (AlbumResource)

**Components:**
- AlbumHero.vue (existing - will be modified)
- AlbumsHeader.vue (existing - will be modified)
- AlbumThumbPanel.vue (existing - will be modified)
- AlbumThumbPanelList.vue (existing - for reference)
- AlbumThumb.vue (existing - for reference)
- SelectDrag.vue (existing - must work with list view)

**Interfaces:**
- Album data structure from AlbumsState.ts (id, title, thumb, num_photos, num_children, policy/badges)
- Router navigation (existing)
- InitConfig type from TypeScript transformer

**Testing Infrastructure:**
- PHPUnit (backend config tests)
- Vitest (component tests)
- Vue Test Utils
- Visual regression testing setup (if available)

## Assumptions & Risks

**Assumptions:**
- Album data structure includes `num_photos` and `num_children` fields ✅ confirmed
- PrimeVue icons `pi-th-large` and `pi-list` are available ✅ confirmed
- BaseConfigMigration pattern automatically creates admin UI ✅ confirmed via example migration
- TypeScript types auto-generated from InitConfig.php ✅ confirmed via Spatie transformer
- Existing album rendering infrastructure supports custom layouts ✅ confirmed
- RTL support available via Tailwind `ltr:` and `rtl:` prefixes ✅ confirmed

**Risks / Mitigations:**

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Performance degradation with 1000+ albums | Medium | Test with large datasets, measure rendering time |
| Mobile layout complexity | Low | Use responsive Tailwind breakpoints, test on actual devices |
| Toggle button placement conflicts | Low | Follow existing AlbumHero/AlbumsHeader icon patterns |
| TypeScript type mismatches | Low | Follow existing patterns, verify types with `npm run check` |
| Selection behavior breaks in list view | Medium | Ensure AlbumListItem emits proper click events, test with modifiers |
| SelectDrag overlay positioning | Medium | Verify overlay calculates bounding rects correctly for list rows |

## Implementation Drift Gate

**Drift Detection Strategy:**
- Before each increment, verify the specification FR/NFR requirements still match the planned work
- After each increment, confirm deliverables align with success criteria
- Record any deviations or clarifications in this plan's appendix

**Evidence Collection:**
- Backend: `php artisan test` passes
- Frontend: `npm run check` passes
- Visual screenshots of grid vs list views (desktop + mobile + RTL)
- Performance measurements (rendering time for 100 albums)
- Accessibility audit results (keyboard navigation, aria-labels)

**Commands to Rerun:**
- Backend: `php artisan test`, `make phpstan`
- Frontend: `npm run format`, `npm run check`
- Development: `npm run dev` (local development server for manual testing)

## Increment Map

### I1 – Backend Config Migration (Admin Default Setting)

**Goal:** Create database migration for `album_layout` config using BaseConfigMigration

**Preconditions:** None (foundational increment)

**Steps:**
1. Create `database/migrations/2026_01_04_000000_add_album_layout_config.php`
2. Extend `BaseConfigMigration` (not standard Migration)
3. Define config in `getConfigs()` array:
   - `key` = `'album_layout'`
   - `value` = `'grid'` (default)
   - `cat` = `'Gallery'`
   - `type_range` = `'grid|list'` (creates dropdown in admin UI)
   - `description` = `'Default album view layout.'`
   - `details` = Explanation of grid vs list with note about no user persistence
   - `is_expert` = `false`
   - `order` = `50`
4. Run migration: `php artisan migrate`
5. Verify config appears in admin Configs UI under Gallery section
6. Test dropdown functionality (select grid/list)

**Commands:**
- `php artisan migrate`
- `php artisan migrate:rollback` (to test down migration)
- Manual: Check admin UI → Settings → Gallery → album_layout

**Exit:** Migration runs successfully, config appears in admin UI with dropdown, default is 'grid'

**Implements:** FR-005-04 (Backend config for default album layout)

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#1-database-migration)

---

### I2 – InitConfig.php Modification (Expose Default to Frontend)

**Goal:** Add `album_layout` property to InitConfig.php so frontend receives default value

**Preconditions:** I1 complete (config migration ran)

**Steps:**
1. Open `app/Http/Resources/GalleryConfigs/InitConfig.php`
2. Add property (line ~48, after album decoration settings):
   ```php
   // Album view mode
   public string $album_layout;
   ```
3. Add initialization in constructor (line ~154, after `is_photo_thumb_tags_enabled`):
   ```php
   $this->album_layout = request()->configs()->getValueAsString('album_layout');
   ```
4. Save file
5. Verify TypeScript type is auto-generated:
   - Check that `App.Http.Resources.GalleryConfigs.InitConfig` includes `album_layout: string`
   - If not, trigger TypeScript transformer rebuild

**Commands:**
- Manual: Edit InitConfig.php
- Verify: Check GET `/api/Gallery::Init` response includes `album_layout`

**Exit:** InitConfig.php includes `album_layout` property, API returns it in response

**Implements:** FR-005-04 (Backend config exposed to frontend)

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#2-initconfigphp)

---

### I3 – LycheeState Store Modifications (View Mode State)

**Goal:** Add album view mode state to LycheeState.ts, initialized from InitConfig

**Preconditions:** I2 complete (InitConfig exposes album_layout)

**Steps:**
1. Open `resources/js/stores/LycheeState.ts`
2. Add state property (line ~47, after album decoration settings):
   ```typescript
   // album stuff
   album_view_mode: "grid" as "grid" | "list",
   ```
3. Add initialization in `load()` action (line ~167, after `is_photo_thumb_tags_enabled`):
   ```typescript
   this.album_view_mode = data.album_layout;
   ```
4. Save file
5. Run `npm run check` to verify TypeScript types
6. No toggle function needed - components will update state directly

**Commands:**
- `npm run check` (verify TypeScript types)
- `npm run format` (code formatting)

**Exit:** LycheeState has `album_view_mode` state, initialized from InitConfig on app load

**Implements:** FR-005-03 (Client-side state management), NFR-005-01 (Load without blocking)

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#3-lycheestatets)

---

### I4 – AlbumListItem Component (Individual Row)

**Goal:** Create reusable component for single album list row with zero-hiding and inline layout

**Preconditions:** I3 complete (state management ready)

**Steps:**
1. Create `resources/js/components/gallery/albumModule/AlbumListItem.vue`
2. Define props interface:
   ```typescript
   defineProps<{
     album: App.Http.Resources.Models.AlbumResource;
     isSelected: boolean;
   }>();
   ```
3. Define emits:
   ```typescript
   defineEmits<{
     clicked: [event: MouseEvent, album: AlbumResource];
     contexted: [event: MouseEvent, album: AlbumResource];
   }>();
   ```
4. Implement template structure:
   - Root div with flex layout, click/contextmenu handlers
   - Router-link thumbnail (64px, 48px on mobile)
   - Content div with title + counts
   - Wide screens (≥md): Title and counts on same line (flexbox row)
   - Narrow screens (<md): Counts stacked below title (flexbox col)
   - Photo count: `v-if="album.num_photos > 0"` with pluralization
   - Sub-album count: `v-if="album.num_children > 0"` with pluralization
   - Badges (if album.policy exists)
5. Apply Tailwind styling:
   - Row: `flex items-center gap-4 p-3 border-b ltr:flex-row rtl:flex-row-reverse`
   - Hover: `hover:bg-gray-100 dark:hover:bg-gray-800`
   - Selection: `:class="{ 'bg-primary-100 dark:bg-primary-900 ring-2 ring-primary-500': isSelected }"`
   - Thumbnail: `shrink-0` (use canonical class as per linter)
   - Content: `flex-1 min-w-0 flex flex-col md:flex-row md:items-center md:gap-2 ltr:text-left rtl:text-right`
6. Handle click events with modifier keys (Ctrl/Cmd/Shift for selection)
7. Write component unit test

**Commands:**
- `npm run check` (tests + types)
- `npm run format` (code formatting)

**Exit:** AlbumListItem renders correctly with inline counts (wide), zero-hiding, RTL support, selection styling, tests pass

**Implements:** FR-005-01 (List format), FR-005-02 (Clickable), FR-005-05 (Badges), FR-005-06 (Responsive), FR-005-07 (Selectable), FR-005-09 (Hide zero counts), S-005-15-19

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#9-albumlistitemvue---new-component)

---

### I5 – AlbumListView Component (List Container)

**Goal:** Create container component that renders albums as list using AlbumListItem

**Preconditions:** I4 complete (AlbumListItem ready)

**Steps:**
1. Create `resources/js/components/gallery/albumModule/AlbumListView.vue`
2. Define props interface:
   ```typescript
   defineProps<{
     albums: App.Http.Resources.Models.AlbumResource[];
     selectedIds: string[];
   }>();
   ```
3. Define emits:
   ```typescript
   defineEmits<{
     "album-clicked": [event: MouseEvent, album: AlbumResource];
     "album-contexted": [event: MouseEvent, album: AlbumResource];
   }>();
   ```
4. Implement template:
   - Wrapper div: `flex flex-col gap-0`
   - v-for loop over albums array
   - Render AlbumListItem for each album with `:is-selected="selectedIds.includes(album.id)"`
   - Emit click/context events from AlbumListItem
5. Handle empty state (no albums) - can be minimal/inherit from parent
6. Write component test with multiple albums

**Commands:**
- `npm run check`
- `npm run format`

**Exit:** AlbumListView renders array of albums as list, emits events, tests pass

**Implements:** FR-005-01 (List format), FR-005-08 (Drag-select compatible via event emission)

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#8-albumlistviewvue---new-component)

---

### I6 – AlbumThumbPanel Modifications (Conditional Rendering)

**Goal:** Update AlbumThumbPanel.vue to conditionally render grid or list based on view mode

**Preconditions:** I3, I5 complete (state management + list view ready)

**Steps:**
1. Read existing AlbumThumbPanel.vue to understand structure
2. Import AlbumListView component
3. Import LycheeState store:
   ```typescript
   import { useLycheeStateStore } from "@/stores/LycheeState";
   const lycheeStore = useLycheeStateStore();
   ```
4. Update template to conditionally render:
   ```vue
   <AlbumListView
     v-if="lycheeStore.album_view_mode === 'list'"
     :albums="albums"
     :selected-ids="selectedAlbumsIds"
     @album-clicked="handleAlbumClick"
     @album-contexted="handleAlbumContext"
   />
   <AlbumThumbPanelList
     v-else
     :albums="albums"
     :selected-ids="selectedAlbumsIds"
     @album-clicked="handleAlbumClick"
     @album-contexted="handleAlbumContext"
   />
   ```
5. Ensure both views receive same props and emit same events
6. Manually test toggle behavior (switch between views)
7. Verify SelectDrag component still works (test drag-select in both views)

**Commands:**
- `npm run check`
- `npm run format`
- `npm run dev` (manual testing)

**Exit:** AlbumThumbPanel correctly switches between grid and list views, no regression, drag-select works

**Implements:** S-005-01, S-005-02, S-005-04, FR-005-08 (Drag-select)

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#7-albumthumbpanelvue---conditional-rendering)

---

### I7 – AlbumHero Toggle Buttons (UI Controls - Album Detail)

**Goal:** Add grid/list toggle buttons to AlbumHero.vue icon row using `pi-th-large` and `pi-list`

**Preconditions:** I3, I6 complete (state management + conditional rendering ready)

**Steps:**
1. Read existing AlbumHero.vue to understand icon row structure (line ~33)
2. Import LycheeState store:
   ```typescript
   import { useLycheeStateStore } from "@/stores/LycheeState";
   const lycheeStore = useLycheeStateStore();
   ```
3. Add toggle function:
   ```typescript
   function toggleAlbumView(mode: "grid" | "list") {
     lycheeStore.album_view_mode = mode;
   }
   ```
4. Add two PrimeVue Button components in the icon row:
   - Grid button: `icon="pi pi-th-large"`
   - List button: `icon="pi pi-list"`
5. Apply styling:
   - `severity`: `primary` when active, `secondary` when inactive
   - `text` attribute for flat buttons
   - `class="border-none"`
6. Add aria attributes:
   - `aria-label`: "Grid view" / "List view"
   - `aria-pressed`: `true` when active, `false` when inactive
7. Add click handlers: `@click="toggleAlbumView('grid')"` / `@click="toggleAlbumView('list')"`
8. Test keyboard navigation (Tab to focus, Enter to activate)

**Commands:**
- `npm run check`
- `npm run format`
- `npm run dev` (manual testing)

**Exit:** Toggle buttons visible in AlbumHero, clickable, toggle view mode, keyboard accessible, aria-labels present

**Implements:** FR-005-03 (Toggle controls), NFR-005-03 (Keyboard accessible), S-005-01, S-005-02

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#5-albumherovue---toggle-buttons)

---

### I8 – AlbumsHeader Toggle Buttons (UI Controls - Albums Page)

**Goal:** Add grid/list toggle buttons to AlbumsHeader.vue menu

**Preconditions:** I7 complete (pattern established in AlbumHero)

**Steps:**
1. Open `resources/js/components/headers/AlbumsHeader.vue`
2. Import LycheeState store:
   ```typescript
   import { useLycheeStateStore } from "@/stores/LycheeState";
   const lycheeStore = useLycheeStateStore();
   ```
3. Add toggle functions:
   ```typescript
   function toggleToGrid() {
     lycheeStore.album_view_mode = "grid";
   }
   function toggleToList() {
     lycheeStore.album_view_mode = "list";
   }
   ```
4. Add two menu items to `menu` computed property (before search button):
   ```typescript
   {
     icon: "pi pi-th-large",
     type: "fn" as const,
     callback: toggleToGrid,
     severity: lycheeStore.album_view_mode === "grid" ? "primary" : "secondary",
     if: true,
     key: "view_grid",
   },
   {
     icon: "pi pi-list",
     type: "fn" as const,
     callback: toggleToList,
     severity: lycheeStore.album_view_mode === "list" ? "primary" : "secondary",
     if: true,
     key: "view_list",
   },
   ```
5. Test that menu items appear in both desktop menu and mobile SpeedDial
6. Verify toggle state syncs between AlbumHero and AlbumsHeader

**Commands:**
- `npm run check`
- `npm run format`
- `npm run dev` (manual testing)

**Exit:** Toggle buttons visible in AlbumsHeader, clickable, sync with AlbumHero toggles

**Implements:** FR-005-03 (Toggle in AlbumsHeader), S-005-01, S-005-02

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#6-albumsheadervue---toggle-buttons)

---

### I9 – RTL Layout Testing

**Goal:** Verify RTL mode properly aligns thumbnails and text

**Preconditions:** I4, I5, I6 complete (list view components implemented)

**Steps:**
1. Set browser/OS to RTL language (Arabic, Hebrew)
2. Load album page in list view
3. Verify thumbnails appear on right side
4. Verify text flows right-to-left
5. Verify counts appear right-to-left (sub-albums, then photos, then title)
6. Verify selection styling works in RTL
7. Take screenshots for documentation
8. If issues found, adjust Tailwind classes:
   - Ensure `ltr:flex-row rtl:flex-row-reverse` is applied
   - Ensure `ltr:text-left rtl:text-right` is applied

**Commands:**
- `npm run dev` (test in browser with RTL language setting)

**Exit:** List view renders correctly in RTL mode, thumbnails right-aligned, text flows right-to-left

**Implements:** FR-005-01 (RTL support), S-005-11

---

### I10 – Selection & Drag-Select Testing

**Goal:** Verify albums are selectable in list view and drag-select works

**Preconditions:** I4, I5, I6 complete (list view components with event emission)

**Steps:**
1. Load album page in list view
2. Click album without modifiers → navigates to album detail (not selected)
3. Ctrl+Click (Windows/Linux) or Cmd+Click (macOS) album → album selected, stays on page
4. Shift+Click album → range selection works
5. Verify selected albums have proper styling (blue background, ring)
6. Test drag-select:
   - Click and drag across multiple albums
   - Verify SelectDrag overlay appears
   - Verify albums within overlay are selected
7. Switch from list to grid view → verify selection persists
8. Switch back to list → verify selection persists

**Commands:**
- `npm run dev` (manual testing)

**Exit:** Selection works identically in list view as grid view, drag-select works, selection persists across view changes

**Implements:** FR-005-07 (Selectable), FR-005-08 (Drag-select), S-005-12, S-005-13, S-005-14

---

### I11 – Responsive Mobile Layout Testing

**Goal:** Verify and refine mobile responsive layout for list view

**Preconditions:** I4, I5, I6, I7, I8 complete (all components implemented)

**Steps:**
1. Test on various mobile viewport sizes:
   - 320px (very narrow)
   - 375px (iPhone SE)
   - 768px (tablet)
2. Verify thumbnail sizes adjust (48px on mobile)
3. Verify album names wrap appropriately
4. Verify counts stack below title on narrow screens (<md)
5. Verify counts inline with title on wide screens (≥md)
6. Verify toggle buttons are usable on mobile
7. Make CSS adjustments if needed (use `md:` breakpoints)
8. Take screenshots for documentation

**Commands:**
- `npm run dev` (test in browser DevTools responsive mode)

**Exit:** List view renders correctly on all mobile breakpoints, counts stack on narrow screens, inline on wide screens, no layout overflow

**Implements:** FR-005-06 (Responsive), S-005-09, S-005-18, S-005-19

---

### I12 – Zero Count Hiding Testing

**Goal:** Verify zero counts are hidden (no "0 photos" or "0 sub-albums")

**Preconditions:** I4 complete (AlbumListItem implemented with v-if)

**Steps:**
1. Create test albums:
   - Album with 10 photos, 3 sub-albums → both counts shown
   - Album with 10 photos, 0 sub-albums → only photo count shown
   - Album with 0 photos, 3 sub-albums → only sub-album count shown
   - Album with 0 photos, 0 sub-albums → no counts shown
2. Load album page in list view
3. Verify each album displays counts correctly per above rules
4. Verify pluralization works (1 photo vs 2 photos, 1 album vs 2 albums)

**Commands:**
- `npm run dev` (manual testing with test data)

**Exit:** Zero counts are hidden, only non-zero counts displayed, pluralization correct

**Implements:** FR-005-09 (Hide zero counts), S-005-15, S-005-16, S-005-17

---

### I13 – Page Reload Behavior Testing

**Goal:** Verify page reload resets view mode to admin default

**Preconditions:** I1, I2, I3, I6 complete (config, InitConfig, state, conditional rendering)

**Steps:**
1. Set admin default to "grid" via Configs UI
2. Load album page → verify grid view displayed
3. Toggle to list view
4. Reload page (F5) → verify view resets to grid
5. Set admin default to "list" via Configs UI
6. Load album page → verify list view displayed
7. Toggle to grid view
8. Reload page → verify view resets to list

**Commands:**
- `npm run dev` (manual testing)
- Manual: Admin UI → Settings → Gallery → album_layout

**Exit:** Page reload always resets to admin-configured default, no user persistence

**Implements:** FR-005-04 (Default from admin config), S-005-03, S-005-04, S-005-20

---

### I14 – Component Unit Tests

**Goal:** Add comprehensive unit tests for new components

**Preconditions:** I4, I5 complete (components implemented)

**Steps:**
1. Create test file for AlbumListItem:
   - Test rendering with sample album data
   - Test click event emission (clicked, contexted)
   - Test selection styling (isSelected prop)
   - Test long album name wrapping
   - Test zero count hiding (num_photos = 0, num_children = 0)
   - Test non-zero count display
   - Test pluralization (1 photo vs 2 photos)
   - Test badge display (if album.policy exists)
2. Create test file for AlbumListView:
   - Test rendering multiple albums
   - Test empty state
   - Test props passing to AlbumListItem
   - Test event emission from child to parent
3. Create test file for LycheeState view mode:
   - Test default value (initialized from InitConfig)
   - Test direct state mutation (no action needed)
4. Create fixture file `albums-list-view.json` with sample data

**Commands:**
- `npm run check` (run all tests)

**Exit:** All unit tests pass, coverage for new components

**Implements:** Test strategy from spec

---

### I15 – Backend Tests

**Goal:** Add backend tests for album_layout config

**Preconditions:** I1 complete (migration ran)

**Steps:**
1. Create test file for config validation:
   - Test config exists with key `'album_layout'`
   - Test default value is `'grid'`
   - Test valid values (`'grid'`, `'list'`)
   - Test type_range creates dropdown options
2. Create test file for InitConfig:
   - Test InitConfig includes `album_layout` property
   - Test InitConfig.album_layout matches config value

**Commands:**
- `php artisan test`

**Exit:** Backend tests pass, config validation works

**Implements:** Test strategy from spec (Core, Application, REST)

**Code Reference:** See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#testing)

---

### I16 – Integration Testing & Visual Regression

**Goal:** Test end-to-end toggle behavior and capture visual baselines

**Preconditions:** I7, I8, I11 complete (full feature implemented)

**Steps:**
1. Manual integration testing:
   - Load album page in default view (admin-configured)
   - Click list toggle → verify switch
   - Click grid toggle → verify switch back
   - Reload page → verify resets to admin default
   - Test on mobile → verify responsive layout
   - Test in RTL mode → verify right-to-left layout
2. Visual regression testing (if tooling available):
   - Capture screenshot of grid view (desktop)
   - Capture screenshot of list view (desktop, LTR)
   - Capture screenshot of list view (desktop, RTL)
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

**Implements:** S-005-01, S-005-02, S-005-03, S-005-04, S-005-20, NFR-005-03

---

### I17 – Performance Testing

**Goal:** Verify list view rendering meets performance requirements

**Preconditions:** I6 complete (conditional rendering ready)

**Steps:**
1. Create test album with 100 albums
2. Measure rendering time in grid view (baseline)
3. Toggle to list view
4. Measure rendering time in list view
5. Verify list view rendering < 300ms (NFR-005-02)
6. If performance issues:
   - Profile with browser DevTools
   - Identify bottlenecks
   - Optimize (e.g., v-memo if needed)
7. Document performance measurements

**Commands:**
- `npm run dev` (manual testing with browser DevTools Performance tab)

**Exit:** List view rendering completes within 300ms for 100 albums

**Implements:** NFR-005-02 (Performance)

---

### I18 – Documentation Updates

**Goal:** Update knowledge map and spec documentation

**Preconditions:** I17 complete (feature fully implemented and tested)

**Steps:**
1. Update [docs/specs/4-architecture/knowledge-map.md](../../knowledge-map.md):
   - Add AlbumListView.vue component entry
   - Add AlbumListItem.vue component entry
   - Note AlbumHero.vue modifications (toggle buttons)
   - Note AlbumsHeader.vue modifications (toggle buttons)
   - Note AlbumThumbPanel.vue modifications (conditional rendering)
   - Note LycheeState.ts modifications (album_view_mode state)
   - Note InitConfig.php modifications (album_layout property)
2. Update spec.md status to "Implemented"
3. Update roadmap.md status to "Completed"
4. Create PR description with:
   - Feature summary
   - Screenshots (grid vs list views, desktop/mobile/RTL)
   - Testing notes
   - Performance measurements

**Commands:**
- None (documentation updates)

**Exit:** Knowledge map updated, documentation current, ready for PR

**Implements:** Documentation deliverables from spec

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-005-01 | I7, I8 | User clicks list toggle → view switches (client-side state update) |
| S-005-02 | I7, I8 | User clicks grid toggle → view switches (client-side state update) |
| S-005-03 | I13 | User loads page, admin default = grid → grid displayed |
| S-005-04 | I13 | User loads page, admin default = list → list displayed |
| S-005-05 | I4, I5 | User clicks list row → navigates to album detail |
| S-005-06 | I4, I14 | Long album name (50+ chars) → full name displayed with wrapping |
| S-005-08 | I4, I14 | Album with badges → badges visible in row |
| S-005-09 | I11 | Mobile toggle → responsive layout with smaller thumbnails |
| S-005-11 | I9 | RTL mode list view → thumbnails right-aligned, text right-to-left |
| S-005-12 | I10 | Ctrl/Cmd+Click in list view → album selected |
| S-005-13 | I10 | Drag-select in list view → SelectDrag overlay works |
| S-005-14 | I10 | Selection persists when switching views |
| S-005-15 | I12 | Album with 0 photos, 5 sub-albums → only sub-album count shown |
| S-005-16 | I12 | Album with 10 photos, 0 sub-albums → only photo count shown |
| S-005-17 | I12 | Album with 0 photos, 0 sub-albums → no counts shown |
| S-005-18 | I11 | Wide screen (≥md) → title and counts on same line |
| S-005-19 | I11 | Narrow screen (<md) → counts stacked below title |
| S-005-20 | I13 | User toggles view, reloads page → resets to admin default |

## Analysis Gate

**Status:** ✅ Executed 2026-01-04

**Checklist:**
- [x] Review spec.md for completeness (all FR/NFR defined)
- [x] Verify all open questions resolved (Q-005-01, Q-005-02, Q-005-03)
- [x] Confirm existing components can be extended without breaking changes
- [x] Verify TypeScript type definitions are sufficient (auto-generated from InitConfig)
- [x] Check for potential conflicts with other active features (no conflicts)
- [x] Review mobile responsive requirements against existing breakpoint strategy (standard Tailwind `md:` breakpoints)
- [x] Verify BaseConfigMigration pattern matches existing migrations (confirmed via example)
- [x] Verify InitConfig pattern matches existing properties (confirmed via are_nsfw_visible)
- [x] Verify LycheeState pattern matches existing state properties (confirmed)

**Findings:**
- ✅ Architecture simplified: No localStorage, no new API endpoints, follows existing patterns exactly
- ✅ BaseConfigMigration automatically creates admin UI dropdown
- ✅ TypeScript types auto-generated from InitConfig via Spatie transformer
- ✅ Selection and drag-select should work via proper event emission from AlbumListItem
- ✅ RTL support available via Tailwind `ltr:` and `rtl:` prefixes

## Exit Criteria

Before declaring Feature 005 complete, the following must pass:

- [ ] All increments (I1-I18) completed successfully
- [ ] **Backend:**
  - [ ] `php artisan test` passes
  - [ ] `make phpstan` passes (PHPStan level 6 minimum)
  - [ ] Migration runs and rolls back cleanly
  - [ ] Config appears in admin UI with dropdown (grid|list)
- [ ] **Frontend:**
  - [ ] `npm run format` passes (frontend code formatting)
  - [ ] `npm run check` passes (frontend tests and TypeScript type checking)
- [ ] **Manual testing confirms:**
  - [ ] Toggle buttons visible and functional in AlbumHero.vue and AlbumsHeader.vue
  - [ ] Grid view displays albums in card layout (existing behavior unchanged)
  - [ ] List view displays albums in horizontal rows with all required info
  - [ ] Counts inline with title on wide screens (≥md), stacked on narrow (<md)
  - [ ] Zero counts hidden (no "0 photos" or "0 sub-albums")
  - [ ] View mode resets to admin default on page reload (no persistence)
  - [ ] Keyboard navigation works (Tab to toggle, Enter to activate)
  - [ ] Mobile responsive layout works on narrow screens
  - [ ] RTL mode properly aligns thumbnails and text
  - [ ] Albums selectable in list view (Ctrl/Cmd/Shift modifiers)
  - [ ] Drag-select overlay works in list view
- [ ] **Quality gates:**
  - [ ] Visual regression baselines captured (grid, list LTR, list RTL, mobile)
  - [ ] Accessibility audit passes (aria-labels, keyboard navigation)
  - [ ] Performance verified (100 albums < 300ms rendering)
- [ ] **Documentation:**
  - [ ] Knowledge map updated
  - [ ] Spec status = "Implemented"
  - [ ] Roadmap status = "Completed"

## Follow-ups / Backlog

**Potential enhancements (defer to future features):**
- User preference persistence (localStorage or backend API)
- Virtualization for 1000+ albums to improve performance
- Sortable columns in list view (click column header to sort)
- Customizable column layout (show/hide fields)
- Per-album view preference (remember different views for different albums)
- Bulk selection checkboxes in list view
- Column resizing in list view

**Monitoring & Metrics:**
- Track admin default configuration (how many instances use list vs grid)
- Monitor performance metrics for large album counts
- Accessibility feedback from users

**Known Limitations:**
- View preference not persisted for users (resets to admin default on reload)
- No column customization in initial version (fixed layout)
- No virtualization (performance may degrade with 1000+ albums)

---

_Last updated: 2026-01-04_
_Ready for implementation_
