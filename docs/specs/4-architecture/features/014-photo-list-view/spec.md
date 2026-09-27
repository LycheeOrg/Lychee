# Feature 014 – Photo List View Toggle

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-02-24 |
| Owners | TBD |
| Linked plan | `docs/specs/4-architecture/features/014-photo-list-view/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/014-photo-list-view/tasks.md` |
| Roadmap entry | TBD |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Add a list view option to the photo display that allows users to switch between the current thumbnail layouts (square, justified, masonry, grid) and a new list view. The list view displays photos in horizontal rows (similar to the existing Album List View in Feature 005) with a small thumbnail, title, date taken, file type indicator, and optional metadata. The toggle is integrated into the existing PhotoThumbPanelControl layout buttons.

**Settings architecture:** This is a purely front-end feature with no database persistence. The list view mode is stored in reactive state (LayoutState store) and is not persisted across page reloads. Users can toggle between thumbnail layouts and list view client-side only. The view resets to the default thumbnail layout on page reload.

## Goals

1. Provide an alternative list-based view for browsing photos within albums
2. Display key photo metadata (title, date, type, size) in a scannable row format
3. Integrate seamlessly with existing layout toggle controls in PhotoThumbPanelControl
4. Maintain full photo selection functionality (click, Ctrl+click, drag-select) in list mode
5. Support responsive layouts for mobile and desktop viewports
6. Support RTL (right-to-left) text direction

## Non-Goals

1. **No database persistence** – List view preference is not stored server-side
2. **No admin configuration** – Unlike album list view, no admin-configurable default
3. **No photo editing from list** – List view is display-only; editing requires opening the photo
4. **No drag-and-drop reordering** – List is read-only for ordering purposes

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-014-01 | Display photos in list view format when list mode is active | Each photo renders as a horizontal row with: 48px (mobile) or 64px (desktop) square thumbnail, photo title (truncated with ellipsis if needed), date taken or created, file type badge (photo/video/raw/livephoto), file size. Rows are left-aligned in LTR mode, right-aligned in RTL mode | N/A (UI layout) | N/A | None | User requirement |
| FR-014-02 | Add list view toggle button to PhotoThumbPanelControl | New button with list icon (`pi-list`) appears after grid button. Clicking toggles to list view. Button shows active state when list mode is selected | Button must integrate with existing layout buttons | N/A | None | User requirement |
| FR-014-03 | Persist layout selection in LayoutState store | When user selects list view, `layoutStore.layout` updates to `'list'`. Value resets to previous thumbnail layout on page reload | Value must be valid PhotoLayoutType or 'list' | N/A | None | User requirement |
| FR-014-04 | Support photo selection in list view | Click selects/navigates to photo. Ctrl/Cmd+click adds to selection. Shift+click range-selects. Selected rows have visual highlight (same as thumbnail selection) | Selection state must persist when switching between layouts | N/A | None | Feature parity with thumbnail view |
| FR-014-05 | Support context menu in list view | Right-click on photo row opens the same context menu as thumbnail view | Context menu must function identically to thumbnail mode | N/A | None | Feature parity with thumbnail view |
| FR-014-06 | Display video/livephoto/raw indicators | Badge or icon indicates media type: video icon for videos, livephoto badge for live photos, RAW text for raw files | Must use precomputed data from PhotoResource.precomputed | N/A | None | User requirement |
| FR-014-07 | Show rating stars if photo has rating | If photo.rating is not null and user has appropriate permissions, display star rating in row | Rating display follows existing rating_album_view_mode setting | N/A | None | Feature parity |
| FR-014-08 | Show highlighted badge if photo is highlighted | If photo.is_highlighted is true and user can view highlights, show flag badge | Badge follows existing highlight display logic | N/A | None | Feature parity |
| FR-014-09 | Support list view in timeline mode | When timeline is active and list mode selected, each date group displays photos as list rows. Timeline headers remain visible above each group | List view renders within each timeline group | N/A | None | User requirement |
| FR-014-10 | Maintain cover/header indicators | If a photo is the album cover or header, show appropriate badge (same as thumbnail view) | Must check albumStore.modelAlbum.cover_id and header_id | N/A | None | Feature parity |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-014-01 | List view must render 100 photos without perceptible lag | Performance | First meaningful paint < 200ms for 100 items | Vue virtual list not required for initial implementation | User experience |
| NFR-014-02 | List view must be fully keyboard accessible | Accessibility | Tab navigation works, Enter opens photo, Space toggles selection | PrimeVue accessibility patterns | WCAG compliance |
| NFR-014-03 | List view must support screen readers | Accessibility | Proper ARIA labels on rows, selection state announced | Standard HTML semantics | WCAG compliance |
| NFR-014-04 | List view must adapt to viewport width | Responsiveness | Mobile: compact layout (smaller thumbnails, stacked info). Desktop: full info on single line | Tailwind breakpoints (md) | User experience |
| NFR-014-05 | Layout toggle must be discoverable | Usability | List button visible alongside existing layout buttons, with tooltip | Existing PhotoThumbPanelControl pattern | User experience |
| NFR-014-06 | Dark mode support | Theming | List rows must respect dark/light theme | Existing Tailwind dark: classes | Visual consistency |

## UI / Interaction Mock-ups

### PhotoThumbPanelControl with List Button (Desktop)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ Photos                                                                          │
│ ┌──────────────────────────────────────────────────────────────────────────────┐│
│ │ [⭐ filter] [rating stars] │ [□] [⊞] [▦] [⊞⊞] [≡]                           ││
│ │                            │ sqr just mas  grid list                        ││
│ └──────────────────────────────────────────────────────────────────────────────┘│
│                                                                                 │
│ ... photos displayed below ...                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

Legend:
  [□]   = Square thumbnails (existing)
  [⊞]   = Justified (existing)
  [▦]   = Masonry (existing)
  [⊞⊞]  = Grid (existing)
  [≡]   = List (NEW) - pi-list icon
```

### List View (LTR Mode - Wide Screen ≥md)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│ Photos                                                          [□][⊞][▦][⊞⊞][≡*]│
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│ ┌────┐  Summer Beach Trip 2024            📷  Jul 15, 2024   2.4 MB  ⭐⭐⭐⭐    │
│ │ T1 │  ────────────────────────────────────────────────────────────────────    │
│ └────┘                                                                          │
│                                                                                 │
│ ┌────┐  Family Dinner Video               🎬  Jul 14, 2024   45.2 MB   🚩       │
│ │ T2 │  ────────────────────────────────────────────────────────────────────    │
│ └────┘  (video badge)                                                           │
│                                                                                 │
│ ┌────┐  Mountain Panorama                 📷  Jul 13, 2024   8.1 MB             │
│ │ T3 │  ────────────────────────────────────────────────────────────────────    │
│ └────┘                                                                          │
│                                                                                 │
│ ┌────┐  Live Photo Sunset                 📱  Jul 12, 2024   12.3 MB            │
│ │ T4 │  (livephoto badge)                                                       │
│ └────┘  ────────────────────────────────────────────────────────────────────    │
│                                                                                 │
│ ┌────┐  RAW_IMG_4521.NEF                  RAW Jul 11, 2024   25.6 MB            │
│ │ T5 │  ────────────────────────────────────────────────────────────────────    │
│ └────┘                                                                          │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘

Legend:
  T1-T5 = 64px square thumbnails (left-aligned in LTR)
  [≡*]  = List toggle button active
  📷    = Photo type indicator
  🎬    = Video type indicator
  📱    = Live Photo indicator
  RAW   = RAW file badge
  🚩    = Highlighted badge
  ⭐⭐⭐⭐ = Rating stars (if present)
  Title, type, date, size on same line (wide screens)
```

### List View (LTR Mode - Narrow Screen <md / Mobile)

```
┌────────────────────────────────┐
│ Photos              [⊞⊞] [≡*] │
├────────────────────────────────┤
│                                │
│ ┌──┐  Summer Beach Trip 2024  │
│ │T1│  📷 Jul 15, 2024  2.4 MB │
│ └──┘  ⭐⭐⭐⭐                   │
│ ─────────────────────────────  │
│                                │
│ ┌──┐  Family Dinner Video     │
│ │T2│  🎬 Jul 14, 2024  45.2 MB│
│ └──┘  🚩                       │
│ ─────────────────────────────  │
│                                │
│ ┌──┐  Mountain Panorama       │
│ │T3│  📷 Jul 13, 2024  8.1 MB │
│ └──┘                           │
│ ─────────────────────────────  │
│                                │
└────────────────────────────────┘

Legend:
  T1-T3 = 48px thumbnails (mobile)
  Info stacked below title
  Only grid/list toggles visible (squares/justified/masonry hidden on mobile)
```

### List View (RTL Mode)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                                                          Photos │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│    ⭐⭐⭐⭐   MB 2.4   2024 ,Jul 15  📷            Summer Beach Trip 2024  ┌────┐ │
│    ────────────────────────────────────────────────────────────────────  │ T1 │ │
│                                                                          └────┘ │
│                                                                                 │
│       🚩   MB 45.2  2024 ,Jul 14  🎬               Family Dinner Video  ┌────┐ │
│    ────────────────────────────────────────────────────────────────────  │ T2 │ │
│                                                       (video badge)      └────┘ │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘

Legend:
  Thumbnails right-aligned in RTL mode
  Text flows right-to-left
```

### Selected Photo Row State

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                                                                 │
│ ╔════╗  Summer Beach Trip 2024            📷  Jul 15, 2024   2.4 MB  ⭐⭐⭐⭐    │
│ ║ T1 ║  ═══════════════════════════════════════════════════════════════════    │
│ ╚════╝  [SELECTED - highlighted background + primary border]                    │
│                                                                                 │
│ ┌────┐  Family Dinner Video               🎬  Jul 14, 2024   45.2 MB           │
│ │ T2 │  ────────────────────────────────────────────────────────────────────    │
│ └────┘  [normal state]                                                          │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘

Legend:
  Selected row has:
  - bg-primary-100 dark:bg-primary-900/50 background
  - ring-2 ring-primary-500 border
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-014-01 | User clicks list view toggle in PhotoThumbPanelControl → photos switch from thumbnail grid to horizontal list rows, toggle button shows active state, LayoutState updated (client-side only) |
| S-014-02 | User clicks any thumbnail layout toggle (square/justified/masonry/grid) when in list view → photos switch from list rows back to selected thumbnail layout, list toggle deactivated |
| S-014-03 | User loads album page → default thumbnail layout displayed (list toggle not active by default) |
| S-014-04 | User toggles to list view, then reloads page → view resets to default thumbnail layout (list preference not persisted) |
| S-014-05 | User clicks photo row in list view → navigates to photo detail page (same as thumbnail click behavior) |
| S-014-06 | User Ctrl/Cmd+clicks photo row in list view → photo is added/removed from selection (toggle behavior) |
| S-014-07 | User Shift+clicks photo row in list view → range selection from last selected photo to clicked photo |
| S-014-08 | User right-clicks photo row in list view → context menu appears with same options as thumbnail view |
| S-014-09 | List view displays video file → shows video badge/icon (🎬) and duration if available |
| S-014-10 | List view displays live photo → shows livephoto badge/icon (📱) |
| S-014-11 | List view displays RAW file → shows RAW text badge |
| S-014-12 | List view displays photo with rating → shows star rating (1-5 stars) in row |
| S-014-13 | List view displays highlighted photo → shows flag badge (🚩) |
| S-014-14 | List view displays album cover photo → shows cover badge |
| S-014-15 | List view displays album header photo → shows header badge |
| S-014-16 | User in RTL mode switches to list view → thumbnails right-aligned, text flows right-to-left |
| S-014-17 | User with photos selected switches from thumbnail to list view → selection state persists, selected photos remain highlighted in list view |
| S-014-18 | User in timeline mode clicks list toggle → each date group displays photos as list rows, timeline headers and structure preserved |
| S-014-19 | Mobile viewport (<md) → compact list layout with smaller thumbnails, metadata stacked |
| S-014-20 | Desktop viewport (≥md) → full list layout with larger thumbnails, all metadata on single line |
| S-014-21 | Photo with long title displayed in list view → title truncated with ellipsis |
| S-014-22 | User hovers over photo row → hover state styling applied (light background) |

## Test Strategy

- **Core:** N/A (no backend changes)
- **Application:** N/A (no backend changes)
- **REST:** N/A (no API changes)
- **CLI:** N/A (no CLI changes)
- **UI (JS/Vitest):**
  - Unit tests for PhotoListItem component (renders all metadata correctly, emits events)
  - Unit tests for PhotoListView component (renders list of PhotoListItem, handles selection)
  - Unit tests for LayoutState store (listClass getter, layout value management)
  - Integration tests for PhotoThumbPanelControl (list button click triggers layout change)
  - Integration tests for PhotoThumbPanel / PhotoThumbPanelList (conditional rendering based on layout)
  - Visual tests for list row states (normal, hover, selected)
  - Responsive tests for mobile/desktop breakpoints
  - RTL tests for right-to-left layout
  - Accessibility tests for keyboard navigation and ARIA labels
- **Docs/Contracts:** N/A (no API contract changes)

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-014-01 | PhotoResource (existing) - uses id, title, type, size_variants, precomputed, preformatted, is_highlighted, rating | UI |
| DO-014-02 | PhotoLayoutType (existing + extension) - adds 'list' option to existing enum values | UI (TypeScript only, not PHP enum) |

### API Routes / Services

N/A - No new API endpoints required. This is a pure front-end feature.

### CLI Commands / Flags

N/A - No CLI changes required.

### Telemetry Events

N/A - No telemetry events (per project scope).

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-014-01 | resources/js/components/gallery/albumModule/__tests__/fixtures/photos-list-view.json | Sample photo data for list view component testing |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-014-01 | Thumbnail view active | Default state or user clicks any thumbnail layout toggle. PhotoThumbPanelList renders thumbnails. List toggle button inactive. |
| UI-014-02 | List view active | User clicks list toggle. PhotoListView component renders horizontal rows. List toggle button has active styling. |
| UI-014-03 | List toggle button focused | User tabs to list toggle button. Visual focus outline visible for accessibility. |
| UI-014-04 | List row hover | User hovers over list row. Hover state styling applied (bg-primary-400/10). |
| UI-014-05 | List row selected | User clicks/selects photo row. Selection styling applied (bg-primary-100, ring-2 ring-primary-500). |
| UI-014-06 | Mobile list view | Viewport width < md breakpoint. List rows adapt to compact layout with smaller thumbnails, stacked metadata. |
| UI-014-07 | Timeline list mode | Timeline is active with list view selected. Each date group shows photos as list rows under the timeline header. |

## Telemetry & Observability

N/A - No telemetry required for this feature per project scope.

## Documentation Deliverables

1. Update roadmap ([docs/specs/4-architecture/roadmap.md](docs/specs/4-architecture/roadmap.md)) with Feature 014 entry
2. Update knowledge map ([docs/specs/4-architecture/knowledge-map.md](docs/specs/4-architecture/knowledge-map.md)) with new component relationships
3. Component documentation in code (JSDoc/TypeScript comments)

## Fixtures & Sample Data

Create test fixture at `resources/js/components/gallery/albumModule/__tests__/fixtures/photos-list-view.json` containing:
- Photos with various titles (short, long, special characters)
- Photos with different types (image, video, raw, livephoto)
- Photos with/without ratings
- Photos with/without highlights
- Photos with various dates and file sizes

## Spec DSL

```yaml
domain_objects:
  - id: DO-014-01
    name: PhotoResource (existing)
    fields:
      - name: id
        type: string
      - name: title
        type: string
      - name: type
        type: string
      - name: size_variants
        type: object (SizeVariantsResource)
      - name: precomputed
        type: object (PreComputedPhotoData)
      - name: preformatted
        type: object (PreformattedPhotoData)
      - name: is_highlighted
        type: boolean
      - name: rating
        type: object (PhotoRatingResource) | null
      - name: taken_at
        type: string | null
      - name: created_at
        type: string

routes: []

cli_commands: []

telemetry_events: []

fixtures:
  - id: FX-014-01
    path: resources/js/components/gallery/albumModule/__tests__/fixtures/photos-list-view.json
    purpose: Sample photo data for list view component testing

ui_states:
  - id: UI-014-01
    description: Thumbnail view active (default)
  - id: UI-014-02
    description: List view active
  - id: UI-014-03
    description: List toggle button focused
  - id: UI-014-04
    description: List row hover
  - id: UI-014-05
    description: List row selected
  - id: UI-014-06
    description: Mobile list view (compact)
  - id: UI-014-07
    description: Timeline mode with list view (each date group shows list rows)

components:
  - id: COMP-014-01
    name: PhotoListItem.vue
    path: resources/js/components/gallery/albumModule/PhotoListItem.vue
    purpose: Individual photo row in list view
  - id: COMP-014-02
    name: PhotoListView.vue
    path: resources/js/components/gallery/albumModule/PhotoListView.vue
    purpose: Container component rendering list of PhotoListItem
```

## Appendix

### Related Features

- **Feature 005 - Album List View Toggle**: This feature follows the same pattern established for album list view. The PhotoListItem component is modeled after AlbumListItem, and PhotoListView after AlbumListView.

### Component Hierarchy

```
PhotoThumbPanel.vue
├── PhotoThumbPanelControl.vue  (adds list toggle button)
│   └── MiniIcon.vue (existing icons + new list icon)
├── PhotoThumbPanelList.vue (modified to conditionally render)
│   ├── PhotoThumb.vue (existing - for thumbnail layouts)
│   └── PhotoListView.vue (NEW - for list layout)
│       └── PhotoListItem.vue (NEW - individual row)
└── Timeline (existing - list view supported within each date group)
```

### Implementation Notes

1. The list layout bypasses the existing layout algorithms (useLayouts) since it uses simple CSS flexbox/grid rather than absolute positioning
2. Selection state is managed by existing stores (PhotosState) and should work identically in list view
3. The PhotoLayoutType TypeScript type needs to include 'list' as a valid option (front-end only, not PHP enum)
4. List toggle button should use PrimeVue icons (pi-list) for consistency with album list view
