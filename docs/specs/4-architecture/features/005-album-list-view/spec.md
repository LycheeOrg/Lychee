# Feature 005 – Album List View Toggle

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-01-03 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/005-album-list-view/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/005-album-list-view/tasks.md` |
| Roadmap entry | #005 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Add a view toggle to the album display that allows users to switch between the current grid/card layout and a new list view. The list view displays albums in a horizontal row format (similar to Windows Explorer details view) with a thumbnail on the left, full untruncated album name, photo count, and sub-album count. This feature affects the UI layer only, requires no backend changes, and stores user preference in browser localStorage.

## Goals
- Provide an alternative list view for albums that prioritizes information density and scannability over visual thumbnails
- Display full, untruncated album names to improve discoverability for albums with long titles
- Show album metadata (photo count, sub-album count) inline for quick scanning
- Persist user view preference across sessions using localStorage
- Maintain existing grid view functionality with seamless toggle capability

## Non-Goals
- Backend API changes or database schema modifications
- Per-album view preferences (global preference only)
- Sorting or filtering capabilities in list view (use existing sort mechanisms)
- Customizable column layout or field selection
- Photo-level list view (this feature is album-only)
- Multi-device sync of view preference (localStorage only, not user settings)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-005-01 | Display albums in list view format when list mode is active | Each album renders as a horizontal row with: 64px square thumbnail (left), full album name (untruncated, text-wrapped if needed), photo count (format: "X photos" or icon + number), sub-album count (format: "Y albums" or icon + number) | N/A (UI layout) | N/A | None | User requirement Q-005-01 |
| FR-005-02 | List view rows must be clickable and navigate to album detail | Clicking anywhere on the row navigates to the album (same behavior as grid card click) | N/A | N/A | None | Consistency with grid view |
| FR-005-03 | Provide toggle control to switch between grid and list views | Two icon buttons in AlbumHero.vue icon row (line 33): grid icon and list icon. Active view is visually indicated. Clicking toggles between views and updates localStorage | N/A | N/A | None | User requirement Q-005-02 |
| FR-005-04 | Persist view preference across sessions | On toggle, save preference to localStorage key `album_view_mode` with value `"grid"` or `"list"`. On page load, read from localStorage and apply saved view mode (default: grid) | N/A | If localStorage unavailable (private browsing), default to grid view without error | None | User requirement Q-005-03 |
| FR-005-05 | Display album badges in list view | Show existing badges (NSFW, password, public, etc.) in list view rows, positioned adjacent to thumbnail or album name | N/A | N/A | None | Feature parity with grid view |
| FR-005-06 | List view must be responsive on mobile | On mobile breakpoints (below md:), list view adapts: smaller thumbnails (48px), stacked or compact layout for counts, maintain full album name visibility | N/A | N/A | None | Responsive design requirement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-005-01 | View preference must load without blocking album data fetch | User experience - instant view mode application | View mode applied synchronously from localStorage before first render, no API call | localStorage API | Performance requirement |
| NFR-005-02 | List view rendering must not degrade performance for large album lists | Performance - handle 100+ albums smoothly | Vue reactivity and DOM rendering should complete within 300ms for 100 albums | Vue 3, Tailwind CSS | User experience |
| NFR-005-03 | Toggle control must be accessible via keyboard | Accessibility - keyboard navigation support | Tab to focus toggle buttons, Enter/Space to activate, aria-labels present | PrimeVue accessibility features | WCAG 2.1 AA |
| NFR-005-04 | Component code must follow Vue 3 Composition API and TypeScript conventions | Code quality and maintainability | Follows existing patterns in AlbumThumbPanel.vue and AlbumThumb.vue, TypeScript types for props/emits | Vue 3, TypeScript, existing codebase patterns | [docs/specs/3-reference/coding-conventions.md](docs/specs/3-reference/coding-conventions.md) |

## UI / Interaction Mock-ups

### Grid View (Current - Default)
```
┌─────────────────────────────────────────────────────────────────┐
│ Album: Vacation 2024                                            │
│ ┌─────────┐  [Download] [Share] [Stats] [Grid*] [List] ...     │
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

### List View (New - When Toggle Active)
```
┌─────────────────────────────────────────────────────────────────┐
│ Album: Vacation 2024                                            │
│ ┌─────────┐  [Download] [Share] [Stats] [Grid] [List*] ...     │
│ └─────────┘                                                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ ┌────┐  Summer Vacation 2024 - California Road Trip            │
│ │ T1 │  📷 145 photos    📁 3 sub-albums                        │
│ └────┘  ────────────────────────────────────────────────────    │
│                                                                 │
│ ┌────┐  Winter Sports - Skiing and Snowboarding Adventures     │
│ │ T2 │  📷 87 photos     📁 0 sub-albums                        │
│ └────┘  ────────────────────────────────────────────────────    │
│                                                                 │
│ ┌────┐  Family Gathering                                       │
│ │ T3 │  📷 23 photos     📁 2 sub-albums                        │
│ └────┘  ────────────────────────────────────────────────────    │
│                                                                 │
│ ┌────┐  Work Conference - Tech Summit 2024 with Long Name...   │
│ │ T4 │  📷 56 photos     📁 1 sub-album                         │
│ └────┘  ────────────────────────────────────────────────────    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘

Legend:
  T1-T4 = 64px square thumbnails
  [Grid] [List*] = Toggle buttons (asterisk indicates active)
  📷 = Photo count icon (or text: "145 photos")
  📁 = Sub-album count icon (or text: "3 albums")
  Full album names displayed without truncation
  Horizontal separator lines between rows
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
| S-005-01 | User clicks list view toggle in AlbumHero → albums switch from grid cards to horizontal list rows, toggle button shows active state, localStorage updated |
| S-005-02 | User clicks grid view toggle in AlbumHero → albums switch from list rows back to grid cards, toggle button shows active state, localStorage updated |
| S-005-03 | User loads album page with no localStorage preference → default grid view displayed |
| S-005-04 | User loads album page with localStorage preference "list" → list view displayed automatically |
| S-005-05 | User clicks album row in list view → navigates to album detail page (same as grid card behavior) |
| S-005-06 | List view displays album with long name (50+ characters) → full name displayed with text wrapping, no truncation |
| S-005-07 | List view displays album with 0 photos → shows "0 photos" (or equivalent empty state) |
| S-005-08 | List view displays album with badges (NSFW, password, etc.) → badges visible in row |
| S-005-09 | User toggles view on mobile device → responsive list layout with smaller thumbnails and compact counts |
| S-005-10 | User with localStorage unavailable (private mode) toggles view → toggle works, but resets to grid on reload |

## Test Strategy
- **Core:** N/A (no backend changes)
- **Application:** N/A (no backend changes)
- **REST:** N/A (no API changes)
- **CLI:** N/A (no CLI changes)
- **UI (JS/Selenium):**
  - Unit tests for AlbumListView component (if created as separate component)
  - Unit tests for localStorage read/write in LycheeState store
  - Component tests for toggle button behavior (click → view change → localStorage update)
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
N/A - No API changes required

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

- **Q-005-01:** List view uses Windows Details View Pattern (horizontal rows, 64px thumbnails, full names, counts)
- **Q-005-02:** Toggle controls placed in AlbumHero.vue icon row (same line as statistics/download buttons)
- **Q-005-03:** View preference stored in localStorage only (no backend, session-scoped to device/browser)

### Implementation Notes

1. **Component Architecture:**
   - Create new `AlbumListView.vue` component parallel to `AlbumThumbPanelList.vue`
   - Create new `AlbumListItem.vue` component parallel to `AlbumThumb.vue`
   - Modify `AlbumThumbPanel.vue` to conditionally render grid or list based on view mode
   - Modify `AlbumHero.vue` to add toggle buttons to existing icon row

2. **State Management:**
   - Add `album_view_mode: "grid" | "list"` to LycheeState store
   - Implement localStorage read on app mount
   - Implement localStorage write on toggle click
   - Default to "grid" if localStorage not available or no preference saved

3. **Styling Considerations:**
   - List rows should use Tailwind flexbox: `flex flex-row items-center gap-4`
   - Thumbnails: `w-16 h-16` (64px) on desktop, `w-12 h-12` (48px) on mobile
   - Album name: `flex-1 text-base font-medium` (allows text wrapping)
   - Counts: `text-sm text-muted-color flex items-center gap-1`
   - Hover state: `hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer`
   - Row separator: `border-b border-gray-200 dark:border-gray-700`

4. **Accessibility:**
   - Toggle buttons must have `aria-label` attributes
   - Active toggle should have `aria-pressed="true"`
   - List rows should have `role="button"` or remain as router-links
   - Ensure keyboard navigation works (Tab to toggle, Enter to activate)

5. **Mobile Responsiveness:**
   - Use Tailwind breakpoints: `md:` for desktop-specific styles
   - Stack counts vertically on very narrow screens (<320px) if needed
   - Ensure full album names remain visible (wrapping allowed)
   - Toggle buttons may need tooltip labels on mobile due to space constraints

### Data Flow

```
User clicks toggle button
  → AlbumHero.vue emits viewModeChanged event
    → LycheeState.album_view_mode updated
      → localStorage.setItem('album_view_mode', newMode)
        → AlbumThumbPanel.vue computed property reacts
          → Conditionally renders AlbumListView or AlbumThumbPanelList
```

### LocalStorage Schema

```typescript
// Key: 'album_view_mode'
// Value: 'grid' | 'list'
// Example:
localStorage.setItem('album_view_mode', 'list');
const viewMode = localStorage.getItem('album_view_mode') || 'grid';
```
