# Feature 005 Tasks – Album List View Toggle

_Status: Draft_
_Last updated: 2026-01-04_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`N-`), and scenario IDs (`S-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I1 – Backend Config Migration

- [ ] T-005-01 – Create migration file for album_layout config (FR-005-04, S-005-03).
  _Intent:_ Create `database/migrations/2026_01_04_000000_add_album_layout_config.php` using BaseConfigMigration pattern.
  _Verification commands:_
  - `php artisan migrate:status` (migration appears in list)
  - File exists and follows BaseConfigMigration structure
  _Notes:_ Use `type_range: 'grid|list'` to auto-create dropdown in admin UI. Reference IMPLEMENTATION-SNIPPETS.md for exact code.

- [ ] T-005-02 – Configure migration parameters (FR-005-04).
  _Intent:_ Set config parameters: key='album_layout', value='grid', cat='Gallery', type_range='grid|list', description, details, is_expert=false, order=50.
  _Verification commands:_
  - Code matches pattern in IMPLEMENTATION-SNIPPETS.md
  _Notes:_ Ensure `is_expert: false` so all admins can access it, not just expert mode.

- [ ] T-005-03 – Run migration and verify config entry (FR-005-04).
  _Intent:_ Execute migration, verify new config row in configs table via admin UI or database query.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan db:seed --class=TestDataSeeder` (if needed)
  - Check admin settings page: Gallery section should show "Default album view layout" dropdown with grid/list options
  _Notes:_ Verify dropdown UI auto-created from type_range.

---

### Increment I2 – InitConfig.php Modification

- [ ] T-005-04 – Add album_layout property to InitConfig.php (FR-005-04, S-005-04).
  _Intent:_ Add `public string $album_layout;` property to `app/Http/Resources/GalleryConfigs/InitConfig.php` (line ~48, after album decoration settings).
  _Verification commands:_
  - `./vendor/bin/phpstan analyze` (static analysis passes)
  - `php artisan test` (no regressions)
  _Notes:_ Follow existing pattern for other config properties.

- [ ] T-005-05 – Initialize album_layout in InitConfig constructor (FR-005-04, S-005-04).
  _Intent:_ Add `$this->album_layout = request()->configs()->getValueAsString('album_layout');` in constructor (line ~154, after is_photo_thumb_tags_enabled).
  _Verification commands:_
  - `./vendor/bin/phpstan analyze`
  - `php artisan test`
  _Notes:_ Ensure initialization order matches other config properties.

- [ ] T-005-06 – Verify TypeScript type generation (FR-005-04).
  _Intent:_ Run TypeScript transformer to generate types from InitConfig.php, verify `album_layout: "grid" | "list"` appears in generated types.
  _Verification commands:_
  - `php artisan typescript:transform` (or equivalent command)
  - Check generated TypeScript types include album_layout property
  _Notes:_ Spatie TypeScriptTransformer should auto-generate from PHP class.

- [ ] T-005-07 – Test InitConfig API endpoint (FR-005-04, S-005-04).
  _Intent:_ Verify `GET /api/Gallery::Init` returns album_layout in response JSON.
  _Verification commands:_
  - `php artisan serve` → `curl http://localhost/api/Gallery::Init` → verify album_layout appears
  - Or use Postman/browser DevTools Network tab
  _Notes:_ Default value should be 'grid'.

---

### Increment I3 – LycheeState Store Modifications

- [ ] T-005-08 – Add album_view_mode state property to LycheeState.ts (FR-005-04, NFR-005-01, S-005-03, S-005-04).
  _Intent:_ Add `album_view_mode: "grid" as "grid" | "list",` to state object (line ~46, after album_decoration_orientation).
  _Verification commands:_
  - `npm run check` (TypeScript compilation)
  _Notes:_ Property should be reactive, accessible directly (no getter/action needed).

- [ ] T-005-09 – Initialize album_view_mode from InitConfig data (FR-005-04, S-005-04).
  _Intent:_ Add `this.album_view_mode = data.album_layout;` in load() action (line ~167, after is_photo_thumb_tags_enabled).
  _Verification commands:_
  - `npm run check`
  - Manual test: Start app, inspect LycheeState in Vue DevTools → album_view_mode should match backend config default
  _Notes:_ No localStorage, no actions—just direct initialization from InitConfig.

- [ ] T-005-10 – Test state initialization (S-005-04, S-005-15).
  _Intent:_ Verify album_view_mode initializes to 'grid' by default, updates if admin changes backend config.
  _Verification commands:_
  - `npm run dev` → Browser DevTools → Vue DevTools → Pinia store → LycheeState → album_view_mode should be 'grid'
  - Change backend config to 'list', reload page → should be 'list'
  _Notes:_ Test page reload resets to backend default (no persistence).

---

### Increment I4 – AlbumListItem Component

- [ ] T-005-11 – Create AlbumListItem.vue component skeleton (FR-005-01, FR-005-02).
  _Intent:_ Create new file `resources/js/components/gallery/albumModule/AlbumListItem.vue` with basic Vue 3 Composition API structure, props interface (album: AlbumResource, isSelected: boolean).
  _Verification commands:_
  - `npm run check` (TypeScript compilation)
  _Notes:_ Import AlbumResource type. Reference IMPLEMENTATION-SNIPPETS.md for exact code.

- [ ] T-005-12 – Implement AlbumListItem template structure (FR-005-01, FR-005-02, FR-005-09, S-005-05, S-005-16, S-005-17).
  _Intent:_ Add template: thumbnail (router-link), title + inline counts container (responsive: md:flex-row for wide screens, flex-col for narrow), conditional rendering for zero counts (v-if="num_photos > 0", v-if="num_children > 0").
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (visual inspection)
  _Notes:_ Use RTL-aware Tailwind classes: `ltr:flex-row rtl:flex-row-reverse`, `ltr:text-left rtl:text-right`.

- [ ] T-005-13 – Add zero-count hiding logic (FR-005-09, S-005-16, S-005-17).
  _Intent:_ Only render photo count if `album.num_photos > 0`, only render sub-album count if `album.num_children > 0`.
  _Verification commands:_
  - `npm run dev` → Test with album with 0 photos → count should not appear
  - Test with album with 0 sub-albums → count should not appear
  _Notes:_ Use `v-if` conditions on count span elements.

- [ ] T-005-14 – Add responsive inline layout (FR-005-09, S-005-18, S-005-19).
  _Intent:_ Title + counts container uses `md:flex-row md:items-center md:gap-2` for wide screens (inline), `flex-col` for narrow screens (stacked).
  _Verification commands:_
  - `npm run dev` → Resize browser to wide (≥768px) → title and counts on same line
  - Resize to narrow (<768px) → counts stack below title
  _Notes:_ Test at 320px, 768px, 1024px viewports.

- [ ] T-005-15 – Add styling to AlbumListItem (FR-005-01, FR-005-06, FR-005-07).
  _Intent:_ Apply Tailwind CSS: hover state, border separator, responsive thumbnail sizes (64px desktop, 64px mobile), selection state styling.
  _Verification commands:_
  - `npm run format`
  - `npm run dev` (visual inspection)
  _Notes:_ Classes: `hover:bg-gray-100 dark:hover:bg-gray-800`, `border-b border-gray-200 dark:border-gray-700`, `w-16 h-16` for thumbnail, `bg-primary-100 dark:bg-primary-900 ring-2 ring-primary-500` when isSelected=true.

- [ ] T-005-16 – Add badge display to AlbumListItem (FR-005-05, S-005-08).
  _Intent:_ Display policy badges (NSFW, password, public) if album.policy exists.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (test with albums that have badges)
  _Notes:_ Reuse badge logic pattern from AlbumThumb.vue or create shared badge component.

- [ ] T-005-17 – Add click and context menu event emission (FR-005-07, S-005-12).
  _Intent:_ Emit 'clicked' event with MouseEvent + album on @click, emit 'contexted' event on @contextmenu.prevent.
  _Verification commands:_
  - `npm run check`
  - Manual test: Click album → should navigate normally, Ctrl+Click → should select
  _Notes:_ Events bubble to parent for selection handling.

- [ ] T-005-18 – Write unit tests for AlbumListItem (S-005-05, S-005-06, S-005-07, S-005-08, S-005-16, S-005-17).
  _Intent:_ Test rendering with sample album data, long album names, 0 photos/sub-albums (counts hidden), badge display, selection styling, responsive layout.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Create test fixture with edge cases. Verify zero counts don't render.

---

### Increment I5 – AlbumListView Component

- [ ] T-005-19 – Create AlbumListView.vue component skeleton (FR-005-01).
  _Intent:_ Create new file `resources/js/components/gallery/albumModule/AlbumListView.vue` with props interface (albums: AlbumResource[], selectedIds: string[]).
  _Verification commands:_
  - `npm run check`
  _Notes:_ Import AlbumListItem component. Reference IMPLEMENTATION-SNIPPETS.md.

- [ ] T-005-20 – Implement AlbumListView template with v-for loop (FR-005-01, S-005-05).
  _Intent:_ Render AlbumListItem for each album in albums array, pass selectedIds prop, emit album-clicked and album-contexted events.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (test with sample albums)
  _Notes:_ Use flex column layout: `flex flex-col gap-0`.

- [ ] T-005-21 – Add event forwarding to AlbumListView (FR-005-07, FR-005-08, S-005-12, S-005-13).
  _Intent:_ Forward 'clicked' and 'contexted' events from AlbumListItem to parent, enabling selection and drag-select.
  _Verification commands:_
  - `npm run check`
  - Manual test: Verify selection works with Ctrl/Cmd/Shift modifiers
  _Notes:_ Parent (AlbumThumbPanel) handles selection logic.

- [ ] T-005-22 – Write unit tests for AlbumListView (S-005-06, S-005-13).
  _Intent:_ Test rendering multiple albums, empty state, props passing to AlbumListItem, event emission.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use fixture data, verify correct number of AlbumListItem components rendered.

---

### Increment I6 – AlbumThumbPanel Modifications

- [ ] T-005-23 – Import AlbumListView and LycheeState in AlbumThumbPanel.vue (S-005-01, S-005-02).
  _Intent:_ Add necessary imports to conditionally render grid or list view.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Read existing AlbumThumbPanel.vue to understand structure before modifying.

- [ ] T-005-24 – Add LycheeState access in AlbumThumbPanel.vue (S-005-04).
  _Intent:_ Add `const lycheeStore = useLycheeStateStore();` in script setup.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Access album_view_mode via `lycheeStore.album_view_mode`.

- [ ] T-005-25 – Update AlbumThumbPanel template to conditionally render grid or list (S-005-01, S-005-02, S-005-14).
  _Intent:_ Use v-if to render AlbumListView when `lycheeStore.album_view_mode === 'list'`, AlbumThumbPanelList when mode === 'grid' (v-else).
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (manually toggle view mode via AlbumHero buttons, verify switch)
  _Notes:_ Ensure both components receive same props (albums array, selectedIds, event handlers).

- [ ] T-005-26 – Verify selection state persists across view switches (FR-005-07, S-005-14).
  _Intent:_ Select albums in grid view, switch to list view → selection persists. Select in list, switch to grid → selection persists.
  _Verification commands:_
  - `npm run dev` (manual testing)
  _Notes:_ Selection state managed by AlbumThumbPanel parent, not affected by view mode.

---

### Increment I7 – AlbumHero Toggle Buttons

- [ ] T-005-27 – Add imports to AlbumHero.vue (FR-005-03, S-005-01, S-005-02).
  _Intent:_ Import `useLycheeStateStore` from `@/stores/LycheeState`.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Follow existing import pattern in AlbumHero.vue.

- [ ] T-005-28 – Add setup logic to AlbumHero.vue (FR-005-03).
  _Intent:_ Add `const lycheeStore = useLycheeStateStore();` and `toggleAlbumView(mode: "grid" | "list")` function that sets `lycheeStore.album_view_mode = mode;`.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Reference IMPLEMENTATION-SNIPPETS.md for exact code.

- [ ] T-005-29 – Add grid and list toggle button elements to AlbumHero.vue (FR-005-03, S-005-01, S-005-02).
  _Intent:_ Add two `<Button>` elements in the icon row (line ~33) with PrimeVue icons `pi pi-th-large` (grid) and `pi pi-list` (list).
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (visual inspection - buttons appear in icon row)
  _Notes:_ Use PrimeVue Button component with `text`, `class="border-none"`, severity based on active state.

- [ ] T-005-30 – Add click handlers to toggle buttons in AlbumHero.vue (FR-005-03, S-005-01, S-005-02).
  _Intent:_ Implement @click handlers that call `toggleAlbumView('grid')` and `toggleAlbumView('list')`.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (click buttons, verify view switches instantly)
  _Notes:_ Verify reactivity works immediately (no page reload needed).

- [ ] T-005-31 – Add active state styling to toggle buttons in AlbumHero.vue (FR-005-03, UI-005-01, UI-005-02).
  _Intent:_ Apply severity="primary" when button is active (current view mode), severity="secondary" otherwise.
  _Verification commands:_
  - `npm run dev` (visual inspection - active button highlighted)
  _Notes:_ Use ternary in severity prop: `:severity="lycheeStore.album_view_mode === 'grid' ? 'primary' : 'secondary'"`.

- [ ] T-005-32 – Add aria-labels and aria-pressed to toggle buttons (NFR-005-03, UI-005-03).
  _Intent:_ Add accessibility attributes: `:aria-label="$t('view.grid')"` / `:aria-label="$t('view.list')"`, `:aria-pressed` based on active state.
  _Verification commands:_
  - `npm run check`
  - Manual accessibility audit (keyboard navigation, screen reader)
  _Notes:_ Ensure buttons are keyboard-navigable (Tab to focus, Enter to activate).

---

### Increment I8 – AlbumsHeader Toggle Buttons

- [ ] T-005-33 – Add imports to AlbumsHeader.vue (FR-005-03, S-005-01, S-005-02).
  _Intent:_ Import `useLycheeStateStore` from `@/stores/LycheeState`.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Check existing imports in AlbumsHeader.vue.

- [ ] T-005-34 – Add setup logic to AlbumsHeader.vue (FR-005-03).
  _Intent:_ Add `const lycheeStore = useLycheeStateStore();`, `toggleToGrid()` function, `toggleToList()` function.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Reference IMPLEMENTATION-SNIPPETS.md for exact code (line ~150).

- [ ] T-005-35 – Add grid and list toggle items to menu computed property (FR-005-03, S-005-01, S-005-02).
  _Intent:_ Add two menu items to `menu` computed property (line ~243, before search button): icon="pi pi-th-large", type="fn", callback=toggleToGrid, severity based on active state.
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (verify buttons appear in AlbumsHeader menu)
  _Notes:_ Follow existing menu item pattern, use `if: true` to always show, `key: "view_grid"` and `key: "view_list"`.

- [ ] T-005-36 – Test AlbumsHeader toggle buttons (FR-005-03, S-005-01, S-005-02).
  _Intent:_ Click grid/list toggles in AlbumsHeader → all album panels switch views.
  _Verification commands:_
  - `npm run dev` (manual testing on Albums page)
  _Notes:_ Verify toggle state persists across navigation within session (not across page reloads).

---

### Increment I9 – RTL Layout Testing

- [ ] T-005-37 – Enable RTL mode for testing (FR-005-01, S-005-11).
  _Intent:_ Configure application to RTL mode (change `dir="rtl"` in HTML or use app settings).
  _Verification commands:_
  - Manual inspection in browser
  _Notes:_ Check if Lychee has RTL mode setting in config or needs manual HTML attribute.

- [ ] T-005-38 – Test list view RTL alignment (FR-005-01, S-005-11).
  _Intent:_ Verify list rows are right-aligned in RTL mode: thumbnails on right side, text flows right-to-left.
  _Verification commands:_
  - `npm run dev` (visual inspection in RTL mode)
  _Notes:_ Verify `ltr:flex-row rtl:flex-row-reverse` and `ltr:text-left rtl:text-right` classes work correctly.

- [ ] T-005-39 – Test toggle buttons in RTL mode (FR-005-03).
  _Intent:_ Verify toggle buttons remain usable and correctly positioned in RTL layout.
  _Verification commands:_
  - `npm run dev` (RTL mode testing)
  _Notes:_ Capture screenshots for documentation.

---

### Increment I10 – Selection & Drag-Select Testing

- [ ] T-005-40 – Test single album selection in list view (FR-005-07, S-005-12).
  _Intent:_ Click album with Ctrl (Windows/Linux) or Cmd (macOS) in list view → album selected (highlighted).
  _Verification commands:_
  - `npm run dev` (manual testing with Ctrl+Click and Cmd+Click)
  _Notes:_ Verify selection styling appears (`bg-primary-100 ring-2 ring-primary-500`).

- [ ] T-005-41 – Test range selection in list view (FR-005-07, S-005-12).
  _Intent:_ Click first album, Shift+Click third album → all albums in range selected.
  _Verification commands:_
  - `npm run dev` (manual testing with Shift+Click)
  _Notes:_ Test both forwards and backwards range selection.

- [ ] T-005-42 – Test drag-select in list view (FR-005-08, S-005-13).
  _Intent:_ Click and drag over multiple albums → SelectDrag overlay appears, albums within overlay are selected.
  _Verification commands:_
  - `npm run dev` (manual testing with mouse drag)
  _Notes:_ Verify SelectDrag component overlay positions correctly over list rows.

- [ ] T-005-43 – Test selection state persists across view switches (FR-005-07, S-005-14).
  _Intent:_ Select albums in grid view → switch to list → selection persists. Select albums in list → switch to grid → selection persists.
  _Verification commands:_
  - `npm run dev` (manual testing)
  _Notes:_ Verify selected album IDs remain in selectedAlbumsIds array regardless of view mode.

---

### Increment I11 – Responsive Mobile Layout Testing

- [ ] T-005-44 – Test list view on 320px viewport (FR-005-06, S-005-09, S-005-18).
  _Intent:_ Verify layout doesn't overflow, thumbnails display at 64px, album names wrap, counts stack below title.
  _Verification commands:_
  - `npm run dev` (browser DevTools responsive mode, set to 320px width)
  _Notes:_ Make CSS adjustments if needed.

- [ ] T-005-45 – Test list view on 375px and 768px viewports (FR-005-06, S-005-09, S-005-19).
  _Intent:_ Verify responsive behavior at common mobile breakpoints, counts inline at ≥768px, stacked at <768px.
  _Verification commands:_
  - `npm run dev` (test multiple viewport sizes)
  _Notes:_ Capture screenshots for documentation.

- [ ] T-005-46 – Test toggle buttons on mobile viewports (FR-005-03, S-005-09).
  _Intent:_ Verify toggle buttons remain usable and don't crowd header on mobile.
  _Verification commands:_
  - `npm run dev` (mobile testing)
  _Notes:_ Test both AlbumHero and AlbumsHeader toggles on narrow screens.

---

### Increment I12 – Zero Count Hiding Testing

- [ ] T-005-47 – Test album with 0 photos in list view (FR-005-09, S-005-16).
  _Intent:_ Display album with num_photos=0 in list view → photo count should not appear.
  _Verification commands:_
  - `npm run dev` (test with album data where num_photos=0)
  _Notes:_ Create test album or modify fixture data.

- [ ] T-005-48 – Test album with 0 sub-albums in list view (FR-005-09, S-005-17).
  _Intent:_ Display album with num_children=0 in list view → sub-album count should not appear.
  _Verification commands:_
  - `npm run dev` (test with album data where num_children=0)
  _Notes:_ Verify v-if conditions work correctly.

- [ ] T-005-49 – Test album with both 0 photos and 0 sub-albums (FR-005-09, S-005-16, S-005-17).
  _Intent:_ Display empty album → no counts appear, layout remains clean.
  _Verification commands:_
  - `npm run dev` (visual inspection)
  _Notes:_ Verify gap/spacing doesn't break when counts are hidden.

---

### Increment I13 – Page Reload Behavior Testing

- [ ] T-005-50 – Test default view mode on first load (FR-005-04, S-005-04).
  _Intent:_ Fresh user (no session) loads app → view mode defaults to admin-configured value (grid).
  _Verification commands:_
  - `npm run dev` → Clear session/cookies → Reload page → Verify grid view
  _Notes:_ Check InitConfig returns default 'grid' from backend.

- [ ] T-005-51 – Test session-only preference (no persistence) (FR-005-04, S-005-15).
  _Intent:_ Toggle to list view → reload page → resets to admin default (grid).
  _Verification commands:_
  - `npm run dev` → Toggle to list → Reload page → Verify resets to grid
  _Notes:_ Verify NO localStorage entry created for album_view_mode.

- [ ] T-005-52 – Test admin config change propagation (FR-005-04, S-005-20).
  _Intent:_ Admin changes default from grid to list → users reload page → new default is list.
  _Verification commands:_
  - Change backend config to 'list' → `npm run dev` → Reload page → Verify list view
  _Notes:_ Test InitConfig endpoint returns updated default.

---

### Increment I14 – Component Unit Tests

- [ ] T-005-53 – Create fixture file albums-list-view.json (FX-005-01).
  _Intent:_ Create `resources/js/components/gallery/albumModule/__tests__/fixtures/albums-list-view.json` with sample album data (long names, 0 counts, badges, high counts).
  _Verification commands:_
  - File exists and is valid JSON
  _Notes:_ Include edge cases: long titles, zero counts, no thumbnails, multiple badges.

- [ ] T-005-54 – Write unit tests for AlbumListItem component (S-005-05, S-005-06, S-005-07, S-005-08, S-005-16, S-005-17).
  _Intent:_ Test rendering with various album data scenarios, navigation behavior, badge display, zero-count hiding, selection styling.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use Vue Test Utils, verify zero counts don't render, verify selection class appears when isSelected=true.

- [ ] T-005-55 – Write unit tests for AlbumListView component (S-005-06, S-005-13).
  _Intent:_ Test rendering multiple albums, empty state, props passing, event emission.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Verify correct number of AlbumListItem components rendered, verify events bubble correctly.

- [ ] T-005-56 – Write integration tests for view mode toggle (S-005-01, S-005-02, S-005-03, S-005-04, S-005-15).
  _Intent:_ Test end-to-end toggle behavior, state initialization from InitConfig, default value, page reload resets to default.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Mock InitConfig API response, test state updates reactively.

---

### Increment I15 – Backend Tests

- [ ] T-005-57 – Write test for album_layout config default value (FR-005-04).
  _Intent:_ Test config defaults to 'grid' after migration runs.
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Query configs table, assert album_layout='grid'.

- [ ] T-005-58 – Write test for album_layout enum validation (FR-005-04).
  _Intent:_ Test config rejects invalid values (not 'grid' or 'list').
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Attempt to set invalid value, expect validation error.

- [ ] T-005-59 – Write test for InitConfig includes album_layout (FR-005-04).
  _Intent:_ Test GET /api/Gallery::Init response includes album_layout property.
  _Verification commands:_
  - `php artisan test` (API test)
  _Notes:_ Assert JSON response has 'album_layout' key with 'grid' or 'list' value.

---

### Increment I16 – Integration Testing & Visual Regression

- [ ] T-005-60 – Manual integration testing: toggle between views (S-005-01, S-005-02).
  _Intent:_ Load album page, click list toggle, verify switch, click grid toggle, verify switch back.
  _Verification commands:_
  - `npm run dev` (manual testing)
  _Notes:_ Test with real album data, various album counts.

- [ ] T-005-61 – Manual integration testing: selection across views (S-005-14).
  _Intent:_ Select albums in grid, switch to list (selection persists), switch back (selection persists).
  _Verification commands:_
  - `npm run dev` (manual testing)
  _Notes:_ Test with multiple selected albums.

- [ ] T-005-62 – Keyboard accessibility testing (NFR-005-03, UI-005-03).
  _Intent:_ Tab to toggle buttons, verify focus outline, press Enter to activate, verify view switches.
  _Verification commands:_
  - Manual keyboard navigation testing
  _Notes:_ Test with screen reader if available, verify aria-labels announced.

- [ ] T-005-63 – Visual regression baseline capture (optional, if tooling available).
  _Intent:_ Capture screenshots of grid view (desktop), list view (desktop), list view (mobile), RTL mode as baseline images.
  _Verification commands:_
  - Visual regression tool commands
  _Notes:_ Store baselines for future regression testing.

---

### Increment I17 – Performance Testing

- [ ] T-005-64 – Performance testing with 100 albums (NFR-005-02).
  _Intent:_ Load album with 100+ albums, measure rendering time, verify < 300ms for list view.
  _Verification commands:_
  - Browser DevTools Performance tab
  _Notes:_ Compare grid vs list rendering performance, verify no significant regression.

- [ ] T-005-65 – Memory profiling (NFR-005-02).
  _Intent:_ Check memory usage in list view vs grid view with 100+ albums.
  _Verification commands:_
  - Browser DevTools Memory tab
  _Notes:_ Verify no memory leaks when toggling between views repeatedly.

---

### Increment I18 – Documentation Updates

- [ ] T-005-66 – Update knowledge-map.md with new components.
  _Intent:_ Add entries for AlbumListView.vue, AlbumListItem.vue, note modifications to AlbumHero.vue, AlbumsHeader.vue, LycheeState.ts, InitConfig.php.
  _Verification commands:_
  - File updated and readable
  _Notes:_ Follow existing knowledge map format.

- [ ] T-005-67 – Update spec.md status to Implemented.
  _Intent:_ Change status field in spec.md from "Draft" to "Implemented", update last updated date.
  _Verification commands:_
  - File updated
  _Notes:_ Update after all other tasks complete.

- [ ] T-005-68 – Update roadmap.md feature status.
  _Intent:_ Change Feature 005 status from "Planning" to "In Progress" when implementation starts, "Complete" when done.
  _Verification commands:_
  - File updated
  _Notes:_ Update incrementally as progress is made.

- [ ] T-005-69 – Create PR description with screenshots.
  _Intent:_ Document feature summary, add screenshots showing grid vs list views (LTR and RTL), zero-count hiding, mobile responsive layout, testing notes.
  _Verification commands:_
  - PR description complete
  _Notes:_ Include before/after screenshots (desktop, mobile, RTL mode).

- [ ] T-005-70 – Add admin documentation for album_layout config.
  _Intent:_ Document new "Default album view layout" setting in admin guide: location (Gallery settings), options (grid/list), behavior (sets default for all users, users can toggle per-session).
  _Verification commands:_
  - Documentation file updated
  _Notes:_ Clarify user toggles are session-only, reset on page reload.

---

## Notes / TODOs

**Environment setup:**
- Ensure Node.js and npm are up to date
- Ensure PHP and Composer are up to date
- Run `npm install` and `composer install` before starting

**Testing strategy:**
- Backend: PHPUnit tests for migration, config, InitConfig endpoint
- Frontend: Vue Test Utils for component unit tests
- Integration: Manual testing for user flows, selection, drag-select
- Visual regression: Optional (if tooling exists)

**Deferred items (out of scope for Feature 005):**
- Virtualization for 1000+ albums (performance optimization)
- User persistence of view preference across sessions (localStorage or backend)
- Sortable columns in list view
- Customizable column layout
- Per-album view preferences

**Common commands:**
- `npm run format` - Format frontend code (Prettier)
- `npm run check` - Run frontend tests and TypeScript type checking
- `npm run dev` - Start local development server
- `php artisan test` - Run backend PHPUnit tests
- `php artisan migrate` - Run database migrations
- `./vendor/bin/phpstan analyze` - Run static analysis

**Architecture patterns used:**
- **BaseConfigMigration**: Auto-creates admin UI dropdown from `type_range: 'grid|list'`
- **InitConfig**: Exposes backend config to frontend on app load
- **LycheeState (Pinia)**: Reactive state without persistence, initialized from InitConfig
- **Session-only preference**: User toggles update state but reset on page reload (no localStorage)
- **RTL support**: Tailwind directional classes (`ltr:`, `rtl:`)
- **Zero-count hiding**: Conditional rendering with `v-if="count > 0"`
- **Responsive inline layout**: `md:flex-row` (inline on wide screens) vs `flex-col` (stacked on narrow)

**Potential blockers:**
- If PrimeVue icons (pi-th-large, pi-list) are not available, choose alternative icons
- If TypeScript transformer doesn't auto-generate types, manually add to TypeScript definitions
- If performance with 100+ albums is poor, may need virtualization (defer to follow-up)

---

_Last updated: 2026-01-04_
