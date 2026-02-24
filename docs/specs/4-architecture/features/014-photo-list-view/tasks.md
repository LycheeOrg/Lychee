# Feature 014 Tasks – Photo List View Toggle

_Status: Draft_  
_Last updated: 2026-02-24_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-functional IDs (`NFR-`), and scenario IDs (`S-014-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I1 – LayoutState Store Modification

- [ ] T-014-01 – Add `listClass` getter to LayoutState store (FR-014-03, S-014-01, S-014-02).
  _Intent:_ Add getter that returns active/inactive styling based on `this.layout === 'list'`. Follow existing pattern from squareClass, justifiedClass, etc.
  _File:_ `resources/js/stores/LayoutState.ts`
  _Verification commands:_
  - `npm run check` (TypeScript compilation)
  _Notes:_ Getter should return `BASE + (this.layout === "list" ? "stroke-primary-400" : "stroke-neutral-400")` matching existing pattern.

---

### Increment I2 – PhotoThumbPanelControl List Button

- [ ] T-014-02 – Import listClass from layoutStore in PhotoThumbPanelControl (FR-014-02).
  _Intent:_ Ensure layoutStore is available and listClass getter accessible.
  _File:_ `resources/js/components/gallery/albumModule/PhotoThumbPanelControl.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ layoutStore already imported; verify listClass is usable.

- [ ] T-014-03 – Add list toggle button to PhotoThumbPanelControl template (FR-014-02, S-014-01, S-014-02).
  _Intent:_ Add `<a>` element with pi-list icon after existing grid button. On click, set `layout = 'list'`.
  _File:_ `resources/js/components/gallery/albumModule/PhotoThumbPanelControl.vue`
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (manually verify button appears and sets layout)
  _Notes:_ Use PrimeVue icon class `pi pi-list`. Apply listClass for active state styling.

---

### Increment I3 – Translation String

- [ ] T-014-04 – Add gallery.layout.list translation to English locale (NFR-014-05).
  _Intent:_ Add `'list' => 'List view',` entry to the `layout` array.
  _File:_ `lang/en/gallery.php`
  _Verification commands:_
  - `php -l lang/en/gallery.php` (syntax check)
  _Notes:_ Add after `'filmstrip'` entry in the `layout` array. JSON files are auto-generated.

---

### Increment I4 – PhotoListItem Component

- [ ] T-014-05 – Create PhotoListItem.vue component (FR-014-01, FR-014-06, FR-014-07, FR-014-08, FR-014-10, S-014-05, S-014-08, S-014-09, S-014-10, S-014-11, S-014-12, S-014-13, S-014-14, S-014-15, S-014-19, S-014-20, S-014-21, S-014-22).
  _Intent:_ Create individual photo row component for list view. Display thumbnail (48px mobile / 64px desktop), title, date, type badge, file size, rating, and highlight/cover/header badges.
  _File:_ `resources/js/components/gallery/albumModule/PhotoListItem.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ Model after AlbumListItem.vue. Use props: photo, isSelected, isCoverId, isHeaderId. Emit clicked and contexted events.

- [ ] T-014-06 – Create ListBadge icons for photo type indicators (FR-014-06).
  _Intent:_ Ensure video (🎬), livephoto (📱), and RAW badges render correctly in PhotoListItem.
  _File:_ `resources/js/components/gallery/albumModule/PhotoListItem.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use PrimeVue icons: pi-video for video, custom for livephoto, text badge for RAW. Reference precomputed.is_video, is_livephoto, is_raw.

---

### Increment I5 – PhotoListView Component

- [ ] T-014-07 – Create PhotoListView.vue container component (FR-014-04, FR-014-05, S-014-06, S-014-07).
  _Intent:_ Create container that renders list of PhotoListItem components. Handle click, Ctrl+click (selection), Shift+click (range), right-click (context menu) events.
  _File:_ `resources/js/components/gallery/albumModule/PhotoListView.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ Props: photos, selectedPhotos. Emits: clicked, selected, contexted, toggleBuyMe. Use maySelect logic from PhotoThumbPanelList for click handling.

- [ ] T-014-08 – Integrate albumStore for cover/header ID checks in PhotoListView (FR-014-10).
  _Intent:_ Import useAlbumStore and pass isCoverId/isHeaderId props to each PhotoListItem.
  _File:_ `resources/js/components/gallery/albumModule/PhotoListView.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ Check `albumStore.modelAlbum?.cover_id === photo.id` and `albumStore.modelAlbum?.header_id === photo.id`.

---

### Increment I6 – PhotoThumbPanelList Integration

- [ ] T-014-09 – Import PhotoListView in PhotoThumbPanelList (FR-014-01).
  _Intent:_ Add import statement for PhotoListView component.
  _File:_ `resources/js/components/gallery/albumModule/PhotoThumbPanelList.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ Add to existing imports near PhotoThumb.

- [ ] T-014-10 – Import layoutStore in PhotoThumbPanelList (FR-014-03).
  _Intent:_ Import useLayoutStore to access current layout mode.
  _File:_ `resources/js/components/gallery/albumModule/PhotoThumbPanelList.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ Add `const layoutStore = useLayoutStore();` to script setup.

- [ ] T-014-11 – Add conditional rendering for list vs thumbnail layouts (FR-014-01, FR-014-09, S-014-01, S-014-02, S-014-03, S-014-17, S-014-18).
  _Intent:_ Use v-if to render PhotoListView when `layoutStore.layout === 'list'`, otherwise render existing PhotoThumb loop. Works in both regular and timeline mode.
  _File:_ `resources/js/components/gallery/albumModule/PhotoThumbPanelList.vue`
  _Verification commands:_
  - `npm run check`
  - `npm run dev` (manually toggle between layouts, verify photos display correctly in both regular and timeline views)
  _Notes:_ Ensure all event handlers pass through correctly to both views. Selection state must persist on switch. Timeline date headers remain visible above list rows.

---

### Increment I7 – Unit Tests for PhotoListItem

- [ ] T-014-12 – Create test fixture for photo list view tests (FX-014-01).
  _Intent:_ Create JSON fixture with variety of photo data for testing.
  _File:_ `resources/js/components/gallery/albumModule/__tests__/fixtures/photos-list-view.json`
  _Verification commands:_
  - Verify JSON is valid
  _Notes:_ Include photos with: various titles, video/livephoto/raw types, ratings, highlights, different dates/sizes.

- [ ] T-014-14 – Create PhotoListItem.spec.ts test file (NFR-014-02, NFR-014-03).
  _Intent:_ Write unit tests for PhotoListItem component.
  _File:_ `resources/js/components/gallery/albumModule/__tests__/PhotoListItem.spec.ts`
  _Verification commands:_
  - `npm run test -- PhotoListItem`
  _Notes:_ Test cases: renders thumbnail, displays title (truncation), shows date, type badges (video/livephoto/raw), rating stars, highlight badge, cover badge, header badge, click/context events, selection styling, hover styling.

- [ ] T-014-15 – Add RTL rendering test for PhotoListItem (S-014-16).
  _Intent:_ Test that PhotoListItem renders correctly in RTL mode.
  _File:_ `resources/js/components/gallery/albumModule/__tests__/PhotoListItem.spec.ts`
  _Verification commands:_
  - `npm run test -- PhotoListItem`
  _Notes:_ Mock RTL mode and verify flex-row-reverse class applied.

---

### Increment I8 – Unit Tests for PhotoListView

- [ ] T-014-16 – Create PhotoListView.spec.ts test file (FR-014-04).
  _Intent:_ Write unit tests for PhotoListView container component.
  _File:_ `resources/js/components/gallery/albumModule/__tests__/PhotoListView.spec.ts`
  _Verification commands:_
  - `npm run test -- PhotoListView`
  _Notes:_ Test cases: renders correct number of PhotoListItem, passes selection state, emits clicked/selected/contexted events, empty array handling.

- [ ] T-014-17 – Add selection persistence test (S-014-17).
  _Intent:_ Test that selection state is correctly passed to PhotoListItem children.
  _File:_ `resources/js/components/gallery/albumModule/__tests__/PhotoListView.spec.ts`
  _Verification commands:_
  - `npm run test -- PhotoListView`
  _Notes:_ Verify selectedPhotos prop correctly highlights the right rows.

---

### Increment I9 – Integration Tests

- [ ] T-014-18 – Create or update PhotoThumbPanelList integration test.
  _Intent:_ Test the full layout toggle flow including list view.
  _File:_ `resources/js/components/gallery/albumModule/__tests__/PhotoThumbPanelList.spec.ts` (or integration test file)
  _Verification commands:_
  - `npm run test -- PhotoThumbPanel`
  _Notes:_ Test: list toggle switches to list view, thumbnail toggle switches back, selection persists.

- [ ] T-014-19 – Test context menu in list view.
  _Intent:_ Verify right-click on photo row triggers context menu.
  _File:_ Integration test file
  _Verification commands:_
  - `npm run test`
  _Notes:_ Ensure contexted event bubbles up correctly from PhotoListItem → PhotoListView → PhotoThumbPanelList.

---

### Increment I10 – Accessibility & RTL Review

- [ ] T-014-20 – Add ARIA labels to PhotoListItem rows (NFR-014-02, NFR-014-03, S-014-16).
  _Intent:_ Add appropriate aria-label to photo rows for screen readers.
  _File:_ `resources/js/components/gallery/albumModule/PhotoListItem.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ Example: `aria-label="Photo: ${photo.title}, ${photo.preformatted.taken_at}"`. Add role="row" or keep as link.

- [ ] T-014-21 – Ensure keyboard navigation works (NFR-014-02).
  _Intent:_ Verify Tab navigates between rows, Enter opens photo, Space toggles selection (if applicable).
  _File:_ `resources/js/components/gallery/albumModule/PhotoListItem.vue`
  _Verification commands:_
  - Manual testing with keyboard
  _Notes:_ Ensure focusable elements have visible focus state.

- [ ] T-014-22 – Manual RTL verification.
  _Intent:_ Manually test RTL layout in browser.
  _Verification commands:_
  - `npm run dev` (set RTL mode in browser/settings)
  _Notes:_ Verify: thumbnail right-aligned, text flows RTL, hover/selection styling correct.

---

### Increment I11 – Documentation Update

- [ ] T-014-23 – Update roadmap with Feature 014 entry.
  _Intent:_ Add Feature 014 to the project roadmap.
  _File:_ `docs/specs/4-architecture/roadmap.md`
  _Verification commands:_
  - Review file for accuracy
  _Notes:_ Include status, description, and links to spec/plan/tasks.

- [ ] T-014-24 – Update knowledge map with new components.
  _Intent:_ Add PhotoListItem and PhotoListView to the knowledge map.
  _File:_ `docs/specs/4-architecture/knowledge-map.md`
  _Verification commands:_
  - Review file for accuracy
  _Notes:_ Document relationships: PhotoThumbPanelList → PhotoListView → PhotoListItem.

- [ ] T-014-25 – Add JSDoc comments to new components.
  _Intent:_ Ensure PhotoListItem.vue and PhotoListView.vue have complete documentation.
  _Files:_ `resources/js/components/gallery/albumModule/PhotoListItem.vue`, `resources/js/components/gallery/albumModule/PhotoListView.vue`
  _Verification commands:_
  - `npm run check`
  _Notes:_ Document props, emits, and component purpose.

---

## Verification Summary

After all tasks complete:

- [ ] `npm run check` passes (TypeScript compilation)
- [ ] `npm run format` applies no changes (code formatted)
- [ ] `npm run test` passes (all tests)
- [ ] Manual verification in browser:
  - [ ] List toggle appears in PhotoThumbPanelControl
  - [ ] Clicking list toggle displays photos in list format
  - [ ] Photos show: thumbnail, title, date, type, size, rating, badges
  - [ ] Click on row navigates to photo
  - [ ] Ctrl+click adds to selection
  - [ ] Right-click opens context menu
  - [ ] Selection persists when switching layouts
  - [ ] List view works in timeline mode (each date group shows list)
  - [ ] Mobile responsive layout works
  - [ ] RTL layout works
  - [ ] Dark mode works

---

## Notes / TODOs

- The PhotoLayoutType TypeScript type may need updating if it's defined as a strict union (check lychee.d.ts for App.Enum.PhotoLayoutType)
- Consider whether the list button icon should use MiniIcon (custom SVG) or PrimeVue icon (pi-list) – recommend pi-list for consistency with album list view
- Virtual scrolling is out of scope for initial implementation; monitor performance with larger photo sets
- If isBuyable prop is needed in PhotoListItem, add toggle-buy-me handling

---

_Last updated: 2026-02-24_
