# Feature 005 – Album List View Toggle

| Field | Value |
|-------|-------|
| Status | Draft (Refined 2026-01-04) |
| Last updated | 2026-01-04 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/005-album-list-view/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/005-album-list-view/tasks.md` |
| Roadmap entry | #005 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Add a view toggle to the album display that allows users to switch between the current grid/card layout and a new list view. The list view displays albums in a horizontal row format (similar to Windows Explorer details view) with a thumbnail, full untruncated album name, photo count (if > 0), and sub-album count (if > 0). The toggle is available both within album detail view (AlbumHero) and on the Albums page (AlbumsHeader). List items are left-aligned in LTR mode and right-aligned in RTL mode. Albums remain fully selectable in list mode with drag-select support.

**Settings architecture:** Default view mode is configured by admin in the existing Configs interface (database-backed, no new endpoint). The default value is provided to clients via `InitConfig.php`. Users can toggle between views client-side (stored in reactive state), but their preference is **not persisted to the server** - it resets to the admin-configured default on page reload.

## Goals
- Provide an alternative list view for albums that prioritizes information density and scannability over visual thumbnails
- Display full, untruncated album names to improve discoverability for albums with long titles
- Show album metadata (photo count, sub-album count) inline for quick scanning
- Persist user view preference across sessions using localStorage
- Maintain existing grid view functionality with seamless toggle capability

## Non-Goals
- Per-album view preferences (global preference only)
- Sorting or filtering capabilities in list view (use existing sort mechanisms)
- Customizable column layout or field selection
- Photo-level list view (this feature is album-only)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-005-01 | Display albums in list view format when list mode is active | Each album renders as a horizontal row with: 64px square thumbnail, full album name (untruncated, text-wrapped if needed). On wide screens (≥md breakpoint): photo count and sub-album count displayed on same line as title. On narrow screens: counts below title. Counts only shown if > 0 (format: "X photos", "Y albums" or icon + number). Rows are left-aligned in LTR mode, right-aligned in RTL mode | N/A (UI layout) | N/A | None | User requirement Q-005-01 |
| FR-005-02 | List view rows must be clickable and navigate to album detail | Clicking anywhere on the row navigates to the album (same behavior as grid card click) | N/A | N/A | None | Consistency with grid view |
| FR-005-03 | Provide toggle control to switch between grid and list views | Two icon buttons using PrimeVue icons `pi-th-large` (grid) and `pi-list` (list). Toggle appears in AlbumHero.vue (album detail view) and AlbumsHeader.vue (albums page). Active view is visually indicated. Clicking toggles between views and updates LycheeState reactive property (client-side only, not persisted) | N/A | N/A | None | User requirement Q-005-02 |
| FR-005-04 | Backend config for default album layout | New `album_layout` config in `configs` table with values `"grid"` or `"list"` (default: `"grid"`). Exposed via InitConfig.php to clients on page load. Admin configures default via existing Configs interface. User toggles do NOT persist to server | Config value validated as enum ('grid', 'list'), defaults to 'grid' on invalid value | N/A (admin-configured via existing Configs UI) | None | User requirement Q-005-03 |
| FR-005-07 | Albums must be selectable in list view | List view rows support click-to-select (with Ctrl/Cmd/Shift modifiers) identical to grid view. Selected state is visually indicated with background color or outline | N/A | N/A | None | Feature parity with grid view |
| FR-005-08 | Drag-select overlay must work in list view | SelectDrag component works in list view. User can click-and-drag to select multiple albums in a single gesture | N/A | N/A | None | Feature parity with grid view |
| FR-005-09 | Hide zero counts in list view | Photo count only displayed if num_photos > 0. Sub-album count only displayed if num_children > 0. If both are 0, neither count is shown | N/A | N/A | None | UI clarity requirement |
| FR-005-05 | Display album badges in list view | Show existing badges (NSFW, password, public, etc.) in list view rows, positioned adjacent to thumbnail or album name | N/A | N/A | None | Feature parity with grid view |
| FR-005-06 | List view must be responsive on mobile | On mobile breakpoints (below md:), list view adapts: smaller thumbnails (48px), stacked or compact layout for counts, maintain full album name visibility | N/A | N/A | None | Responsive design requirement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-005-01 | View preference must load without blocking album data fetch | User experience - instant view mode application | View mode applied synchronously from Gallery config (loaded on app initialization) before first render, no additional API call | Gallery config initialization | Performance requirement |
| NFR-005-02 | List view rendering must not degrade performance for large album lists | Performance - handle 100+ albums smoothly | Vue reactivity and DOM rendering should complete within 300ms for 100 albums | Vue 3, Tailwind CSS | User experience |
| NFR-005-03 | Toggle control must be accessible via keyboard | Accessibility - keyboard navigation support | Tab to focus toggle buttons, Enter/Space to activate, aria-labels present | PrimeVue accessibility features | WCAG 2.1 AA |
| NFR-005-04 | Component code must follow Vue 3 Composition API and TypeScript conventions | Code quality and maintainability | Follows existing patterns in AlbumThumbPanel.vue and AlbumThumb.vue, TypeScript types for props/emits | Vue 3, TypeScript, existing codebase patterns | [docs/specs/3-reference/coding-conventions.md](docs/specs/3-reference/coding-conventions.md) |

## UI / Interaction Mock-ups

### Grid View (Current - Default) - Album Detail
```
┌─────────────────────────────────────────────────────────────────┐
│ Album: Vacation 2024                                            │
│ ┌─────────┐  [Download] [Share] [Stats] [⊞*] [≡] ...           │
│ └─────────┘                                                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐                       │
│  │ Album│  │ Album│  │ Album│  │ Album│                       │
│  │  #1  │  │  #2  │  │  #3  │  │  #4  │                       │
│  │ Name │  │ Name │  │ Name │  │ Name │                       │
│  └──────┘  └──────┘  └──────┘  └──────┘                       │
│                                                                 │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐                       │
│  │ Album│  │ Album│  │ Album│  │ Album│                       │
│  │  #5  │  │  #6  │  │  #7  │  │  #8  │                       │
│  │ Name │  │ Name │  │ Name │  │ Name │                       │
│  └──────┘  └──────┘  └──────┘  └──────┘                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### List View (New - When Toggle Active) - LTR Mode - Wide Screen (≥md)
```
┌──────────────────────────────────────────────────────────────────────────────────┐
│ Album: Vacation 2024                                                             │
│ ┌─────────┐  [Download] [Share] [Stats] [⊞] [≡*] ...                            │
│ └─────────┘                                                                      │
├──────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│ ┌────┐  Summer Vacation 2024 - California Road Trip    📷 145 photos  📁 3 albums│
│ │ T1 │  ────────────────────────────────────────────────────────────────────     │
│ └────┘                                                                           │
│                                                                                  │
│ ┌────┐  Winter Sports - Skiing and Snowboarding Adventures    📷 87 photos      │
│ │ T2 │  ────────────────────────────────────────────────────────────────────     │
│ └────┘                                                                           │
│                                                                                  │
│ ┌────┐  Family Gathering    📷 23 photos  📁 2 albums                            │
│ │ T3 │  ────────────────────────────────────────────────────────────────────     │
│ └────┘                                                                           │
│                                                                                  │
│ ┌────┐  Work Conference - Tech Summit 2024 with Long Name    📷 56 photos  📁 1 album│
│ │ T4 │  ────────────────────────────────────────────────────────────────────     │
│ └────┘                                                                           │
│                                                                                  │
│ ┌────┐  Empty Album (no photos or sub-albums)                                   │
│ │ T5 │  ────────────────────────────────────────────────────────────────────     │
│ └────┘                                                                           │
│                                                                                  │
└──────────────────────────────────────────────────────────────────────────────────┘

Legend:
  T1-T5 = 64px square thumbnails (left-aligned in LTR)
  [⊞] [≡*] = Grid/List toggle buttons (pi-th-large/pi-list icons, list active)
  Title, photo count, and sub-album count on same line (wide screens)
  📷 = Photo count (only shown if > 0)
  📁 = Sub-album count (only shown if > 0, uses singular "album" for count=1)
  Note: Row 2 has 0 sub-albums → "📁 0 albums" is NOT shown
  Note: Row 5 has 0 photos and 0 sub-albums → no counts shown at all
  Horizontal separator lines between rows
  Rows are selectable (click with Ctrl/Cmd/Shift modifiers)
```

### List View - RTL Mode - Wide Screen
```
┌──────────────────────────────────────────────────────────────────────────────────┐
│                                                            2024 تعطيلات :ملبا │
│                            ... [≡*] [⊞] [Stats] [Share] [Download]  ┌─────────┐ │
│                                                                      └─────────┘ │
├──────────────────────────────────────────────────────────────────────────────────┤
│                                                                                  │
│ albums 3 📁  photos 145 📷    California Road Trip - Summer Vacation 2024  ┌────┐│
│      ────────────────────────────────────────────────────────────────────  │ T1 ││
│                                                                            └────┘│
│                                                                                  │
│       photos 87 📷      Winter Sports - Skiing and Snowboarding Adventures  ┌────┐│
│      ────────────────────────────────────────────────────────────────────  │ T2 ││
│                                                                            └────┘│
│                                                                                  │
│             albums 2 📁  photos 23 📷                    Family Gathering  ┌────┐│
│      ────────────────────────────────────────────────────────────────────  │ T3 ││
│                                                                            └────┘│
│                                                                                  │
└──────────────────────────────────────────────────────────────────────────────────┘

Legend:
  Thumbnails right-aligned in RTL mode (on the right side)
  Text and counts flow right-to-left
  Counts only shown if > 0 (note: Row 2 has 0 sub-albums, not shown)
  Same selection/drag-select behavior as LTR
```

### Albums Page Toggle (AlbumsHeader)
```
┌─────────────────────────────────────────────────────────────────┐
│ Lychee Gallery                              [⊞*] [≡] [↻] [?]   │
├─────────────────────────────────────────────────────────────────┤
│ Smart Albums                                                    │
│  ┌──────┐  ┌──────┐  ┌──────┐                                 │
│  │Recent│  │Public│  │Favs  │                                 │
│  └──────┘  └──────┘  └──────┘                                 │
│                                                                 │
│ Albums                                                          │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐                       │
│  │ 2024 │  │ 2023 │  │ 2022 │  │ 2021 │                       │
│  └──────┘  └──────┘  └──────┘  └──────┘                       │
└─────────────────────────────────────────────────────────────────┘

Note: Toggle controls in AlbumsHeader.vue affect all album panels below
```

### Mobile List View (Responsive)
```
┌────────────────────────────┐
│ Album: Vacation 2024       │
│ [≡] [↓] [📊] [⊞] [☰*] ...  │
├────────────────────────────┤
│                            │
│ ┌──┐ Summer Vacation 2024  │
│ │T1│ California Road Trip  │
│ └──┘ 📷 145  📁 3           │
│ ───────────────────────     │
│                            │
│ ┌──┐ Winter Sports -       │
│ │T2│ Skiing Adventures     │
│ └──┘ 📷 87  📁 0            │
│ ───────────────────────     │
│                            │
│ ┌──┐ Family Gathering      │
│ │T3│ 📷 23  📁 2            │
│ └──┘ ───────────────────    │
│                            │
└────────────────────────────┘

Legend:
  T1-T3 = 48px thumbnails (mobile)
  Text may wrap on narrow screens
  Counts inline/compact format
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-005-01 | User clicks list view toggle in AlbumHero or AlbumsHeader → albums switch from grid cards to horizontal list rows, toggle button shows active state, LycheeState updated (client-side only) |
| S-005-02 | User clicks grid view toggle in AlbumHero or AlbumsHeader → albums switch from list rows back to grid cards, toggle button shows active state, LycheeState updated (client-side only) |
| S-005-03 | User loads album page with album_layout config = "grid" (admin default) → grid view displayed |
| S-005-04 | User loads album page with album_layout config = "list" (admin default) → list view displayed automatically |
| S-005-20 | User toggles to list view, then reloads page → view resets to admin-configured default (preference not persisted) |
| S-005-15 | Album with 0 photos and 5 sub-albums displayed in list view → shows "📁 5 albums" only, no photo count |
| S-005-16 | Album with 10 photos and 0 sub-albums displayed in list view → shows "📷 10 photos" only, no sub-album count |
| S-005-17 | Album with 0 photos and 0 sub-albums displayed in list view → no counts shown, title only |
| S-005-18 | Wide screen (≥md): Album displayed in list view → title, photo count, and sub-album count on same line |
| S-005-19 | Narrow screen (<md): Album displayed in list view → counts below title |
| S-005-05 | User clicks album row in list view → navigates to album detail page (same as grid card behavior) |
| S-005-06 | List view displays album with long name (50+ characters) → full name displayed with text wrapping, no truncation |
| S-005-08 | List view displays album with badges (NSFW, password, etc.) → badges visible in row |
| S-005-09 | User toggles view on mobile device → responsive list layout with smaller thumbnails and compact counts |
| S-005-11 | User in RTL mode switches to list view → thumbnails right-aligned, text flows right-to-left |
| S-005-12 | User clicks album in list view with Ctrl/Cmd → album is selected (added to selection), same as grid behavior |
| S-005-13 | User drag-selects multiple albums in list view → SelectDrag overlay appears, albums within selection are selected |
| S-005-14 | User with albums selected switches from grid to list → selection state persists, selected albums remain highlighted in list view |

## Test Strategy
- **Core:** Unit tests for album_layout config value validation (enum: 'grid'|'list', default: 'grid')
- **Application:** N/A (uses existing Configs interface)
- **REST:** API tests for:
  - GET /api/Gallery (via InitConfig) returns album_layout in config object
  - Verify album_layout defaults to 'grid' if invalid value in database
- **CLI:** N/A (no CLI changes)
- **UI (JS/Selenium):**
  - Unit tests for AlbumListView component
  - Unit tests for AlbumListItem component (zero count hiding logic, inline layout on wide screens)
  - Component tests for toggle button behavior (click → view change → LycheeState updated)
  - Integration test: Reload page after toggling → view resets to admin default
  - Integration tests for view rendering (grid cards vs list rows)
  - Visual regression tests for list view layout on desktop and mobile
  - Accessibility tests for keyboard navigation and aria-labels
- **Docs/Contracts:** N/A (no API contracts)

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-005-01 | Album display data (id, title, thumbnail, num_photos, num_children) - uses existing Album model | UI |

### API Routes / Services
| ID | Route | Method | Description | Request | Response |
|----|-------|--------|-------------|---------|----------|
| API-005-01 | /api/Gallery (InitConfig) | GET | Returns Gallery config including album_layout default setting | None | JSON with `config.album_layout: "grid" \| "list"` (default value from database config) |

**Note:** No new API endpoints required. Album layout default is admin-configured via existing Configs interface and exposed through InitConfig.php.

### CLI Commands / Flags
N/A - No CLI changes required

### Telemetry Events
N/A - No telemetry events (per project scope)

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-005-01 | resources/js/components/gallery/albumModule/__tests__/fixtures/albums-list-view.json | Sample album data for list view component testing |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-005-01 | Grid view active | Default state or user clicks grid toggle. AlbumThumbPanelList renders grid cards. Grid toggle button has active styling. |
| UI-005-02 | List view active | User clicks list toggle. New AlbumListView component renders horizontal rows. List toggle button has active styling. |
| UI-005-03 | Toggle button focused | User tabs to toggle button. Visual focus outline visible for accessibility. |
| UI-005-04 | List row hover | User hovers over list row. Hover state styling applied (similar to grid card hover). |
| UI-005-05 | Mobile list view | Viewport width < md: breakpoint. List rows adapt to compact layout with smaller thumbnails. |

## Telemetry & Observability
No telemetry events are defined for this feature per project scope.

## Documentation Deliverables
- Update [docs/specs/4-architecture/roadmap.md](docs/specs/4-architecture/roadmap.md) with Feature 005 entry
- Update [docs/specs/4-architecture/knowledge-map.md](docs/specs/4-architecture/knowledge-map.md) with new components:
  - AlbumListView.vue (if created)
  - AlbumHero.vue modifications (toggle buttons)
  - LycheeState.ts modifications (view mode state)

## Fixtures & Sample Data
Create fixture file `resources/js/components/gallery/albumModule/__tests__/fixtures/albums-list-view.json` with sample album data including:
- Albums with long names (50+ characters)
- Albums with 0 photos and 0 sub-albums
- Albums with various badge combinations (NSFW, password, public)
- Albums with high photo/sub-album counts (1000+ photos, 50+ sub-albums)

## Spec DSL

```yaml
domain_objects:
  - id: DO-005-01
    name: Album (existing)
    fields:
      - name: id
        type: string
      - name: title
        type: string
      - name: thumb
        type: object (Photo)
      - name: num_photos
        type: integer
      - name: num_children
        type: integer
      - name: badges
        type: array

routes: []

cli_commands: []

telemetry_events: []

fixtures:
  - id: FX-005-01
    path: resources/js/components/gallery/albumModule/__tests__/fixtures/albums-list-view.json
    purpose: Sample album data for list view component testing

ui_states:
  - id: UI-005-01
    description: Grid view active (default)
  - id: UI-005-02
    description: List view active
  - id: UI-005-03
    description: Toggle button focused (keyboard navigation)
  - id: UI-005-04
    description: List row hover state
  - id: UI-005-05
    description: Mobile list view (responsive)

ui_components:
  - id: UC-005-01
    name: AlbumListView.vue (new component)
    location: resources/js/components/gallery/albumModule/AlbumListView.vue
    purpose: Renders albums in horizontal list row format
  - id: UC-005-02
    name: AlbumListItem.vue (new component)
    location: resources/js/components/gallery/albumModule/AlbumListItem.vue
    purpose: Individual list row item component
  - id: UC-005-03
    name: AlbumHero.vue (modified)
    location: resources/js/components/gallery/albumModule/AlbumHero.vue
    modifications: Add grid/list toggle buttons to icon row (line 33)
  - id: UC-005-04
    name: LycheeState.ts (modified)
    location: resources/js/stores/LycheeState.ts
    modifications: Add album_view_mode state property with localStorage persistence
```

## Appendix

### Resolved Open Questions
All open questions (Q-005-01, Q-005-02, Q-005-03) have been resolved and incorporated into the spec:

- **Q-005-01:** List view uses Windows Details View Pattern (horizontal rows, 64px thumbnails, full names, counts on same line for wide screens, hide zero counts)
- **Q-005-02:** Toggle controls placed in AlbumHero.vue icon row (same line as statistics/download buttons) AND AlbumsHeader.vue (albums page header)
- **Q-005-03:** Default view configured by admin in database (via Configs UI), exposed through InitConfig.php. User toggles are client-side only (reactive state), NOT persisted to server

### Refinements (2026-01-04)

**First refinement batch:**
- Use `pi-th-large` and `pi-list` PrimeVue icons for grid/list toggle buttons
- Add toggle to Albums page (AlbumsHeader.vue) in addition to album detail view (AlbumHero.vue)
- Support RTL mode: list rows left-aligned in LTR, right-aligned in RTL
- Ensure albums are selectable in list view (click with Ctrl/Cmd/Shift modifiers)
- Support drag-select overlay (SelectDrag component) in list view

**Second refinement batch:**
- **Storage clarification:** Default value comes from database config (via InitConfig.php). User toggles are client-side only (reactive state), NOT persisted to server. Reloads reset to admin default.
- **UI layout:** On wide screens (≥md), display photo count and sub-album count on the same line as the title
- **Zero count hiding:** Only display counts if value > 0 (don't show "0 photos" or "0 albums")

### Implementation Notes

1. **Backend Changes:**
   - **Database Migration:** Create `database/migrations/2026_01_04_000000_add_album_layout_config.php` extending `BaseConfigMigration`:
     ```php
     return new class() extends BaseConfigMigration {
         public const MOD_GALLERY = 'Gallery';
         public function getConfigs(): array {
             return [['key' => 'album_layout', 'value' => 'grid',
                      'cat' => self::MOD_GALLERY, 'type_range' => 'grid|list',
                      'description' => 'Default album view layout.']];
         }
     };
     ```
   - **InitConfig.php:** Add `public string $album_layout;` property and initialize from config in constructor:
     ```php
     $this->album_layout = request()->configs()->getValueAsString('album_layout');
     ```
   - **Admin Configs UI:** Config appears automatically in Gallery settings as a dropdown (grid|list), no additional UI code needed

2. **Component Architecture:**
   - Create new `AlbumListView.vue` component parallel to `AlbumThumbPanelList.vue`
   - Create new `AlbumListItem.vue` component parallel to `AlbumThumb.vue`
   - Modify `AlbumThumbPanel.vue` to conditionally render grid or list based on view mode
   - Modify `AlbumHero.vue` to add toggle buttons to existing icon row (line 33)
   - Modify `AlbumsHeader.vue` to add toggle buttons (similar to AlbumHero pattern)

3. **State Management:**
   - Initialize `album_view_mode` from `InitConfig.album_layout` on app load (stored in LycheeState)
   - On toggle click, update `LycheeState.album_view_mode` directly (reactive, client-side only)
   - **No API call, no persistence** - user preference is session-only
   - Page reload resets to admin-configured default from InitConfig
   - Default to "grid" if config value is missing or invalid

4. **Styling Considerations:**
   - List rows use Tailwind flexbox: `flex items-center gap-4` with `ltr:flex-row rtl:flex-row-reverse` for RTL support
   - Thumbnails: `w-16 h-16` (64px) on desktop, `w-12 h-12` (48px) on mobile
   - **Wide screens (≥md):** Title and counts on same line using `flex-row` with `gap-2`
   - **Narrow screens (<md):** Counts stacked below title using `flex-col`
   - Album name: `flex-1 text-base font-medium ltr:text-left rtl:text-right` (allows text wrapping, aligned per direction)
   - Counts: `text-sm text-muted-color flex items-center gap-1` with `v-if="num_photos > 0"` and `v-if="num_children > 0"`
   - **Pluralization:** Use singular "album"/"photo" when count = 1, plural "albums"/"photos" when count > 1
   - Hover state: `hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer`
   - Selection state: `bg-primary-100 dark:bg-primary-900 ring-2 ring-primary-500` (matches grid selection style)
   - Row separator: `border-b border-gray-200 dark:border-gray-700`
   - Container alignment: `ltr:justify-start rtl:justify-end` for proper RTL layout

5. **Accessibility:**
   - Toggle buttons must have `aria-label` attributes
   - Active toggle should have `aria-pressed="true"`
   - List rows should have `role="button"` or remain as router-links
   - Ensure keyboard navigation works (Tab to toggle, Enter to activate)

6. **Mobile Responsiveness:**
   - Use Tailwind breakpoints: `md:` for desktop-specific styles
   - Stack counts vertically on very narrow screens (<320px) if needed
   - Ensure full album names remain visible (wrapping allowed)
   - Toggle buttons may need tooltip labels on mobile due to space constraints

### Data Flow

```
App initialization:
  → GET /api/Gallery
    → InitConfig.php loads album_layout from database ('grid' or 'list')
      → Response includes config.album_layout
        → LycheeState.album_view_mode initialized from InitConfig.album_layout
          → AlbumThumbPanel.vue renders based on initial view mode

User clicks toggle button:
  → AlbumHero.vue or AlbumsHeader.vue click handler
    → LycheeState.album_view_mode = newMode ('grid' or 'list')
      → AlbumThumbPanel.vue computed property reacts to state change
        → Conditionally renders AlbumListView or AlbumThumbPanelList
      → No API call, no persistence

User reloads page:
  → State resets to admin-configured default from InitConfig
```

### Database Schema

**Note:** The `album_layout` config is added via `BaseConfigMigration`, which automatically handles the database schema. The migration creates a row in the `configs` table:

```
| key          | value | cat     | type_range  | description              |
|--------------|-------|---------|-------------|--------------------------|
| album_layout | grid  | Gallery | grid|list   | Default album view layout|
```

The `type_range` field (`grid|list`) automatically creates:
1. Admin UI dropdown with two options
2. Runtime validation (enum constraint)
3. Default value (`grid`)

### InitConfig Response Example

```json
{
  "config": {
    "are_nsfw_visible": false,
    "album_layout": "grid",
    ...
  }
}
```

**Note:** The `album_layout` value is the admin-configured default from the database. Users can toggle views client-side, but their preference is not sent back to the server.
