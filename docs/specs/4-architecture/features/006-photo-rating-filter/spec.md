# Feature 006 – Photo Star Rating Filter

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-01-14 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/006-photo-rating-filter/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/006-photo-rating-filter/tasks.md` |
| Roadmap entry | #006 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Add a star rating filter control to the photo panel that allows users to quickly filter photos in the current album by minimum star rating threshold. The filter displays as 5 hoverable/clickable stars positioned to the left of the photo layout selection buttons. Clicking a star filters photos to show rating ≥ selected star (e.g., click 3rd star → show 3, 4, 5 star photos). Clicking the same star again removes the filter. The filter is frontend-only (no backend changes), uses client-side filtering on the already-loaded photo array, and only appears when at least one photo in the album has a rating. Filter state persists in the Pinia state store during the session.

## Goals
- Provide quick visual filtering of photos by minimum star rating threshold
- Display filter control only when relevant (at least one rated photo exists)
- Support intuitive interaction: click star to set minimum threshold, click again to clear filter
- Maintain filter state in Pinia store (similar to NSFW visibility pattern)
- Implement fully client-side filtering (no API calls)
- Position filter control to the left of existing photo layout selection buttons

## Non-Goals
- Backend API changes or query parameter filtering
- Exact rating matching (filter shows ≥ N stars, not == N stars)
- Filtering for unrated photos explicitly (unrated excluded from filtered results)
- Multiple rating selection (checkboxes, multi-select)
- Persistent filter state across page reloads (localStorage)
- Filter controls for albums (feature is photo-only)
- Range sliders or complex UI controls

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-006-01 | Display star filter control only when at least one photo has a rating | Component computes `hasRatedPhotos` from photo array. If true, render 5-star filter control. If false, hide filter control entirely. | N/A (UI conditional rendering) | N/A | None | User requirement (conditional display) |
| FR-006-02 | Filter photos by minimum star rating threshold (≥ N stars) | User clicks star N (1-5) → filter state updates to N → photo list filtered to show only photos with `user_rating >= N`. Unrated photos (null/0 rating) excluded from results. | N/A (client-side filtering) | N/A | None | User requirement Q-006-01, Q-006-04 |
| FR-006-03 | Toggle filter off by clicking same star | User clicks star N when filter already set to N → filter state resets to null → all photos shown (no filtering applied). | N/A | N/A | None | User requirement Q-006-01 |
| FR-006-04 | Persist filter state in Pinia store during session | Filter state stored in PhotosState store (similar to NSFW visibility). State persists during navigation within album but resets on page reload or closing tab. | N/A | N/A | None | User requirement Q-006-03 |
| FR-006-05 | Apply filtering only when filter is active and rated photos exist | If filter state is null OR no rated photos exist → display all photos unfiltered. If filter state is set AND rated photos exist → apply filter. | N/A | N/A | None | User requirement (conditional filtering) |
| FR-006-06 | Visual feedback: highlight selected star threshold | Selected star and all stars below it should be visually highlighted (filled) to indicate active filter. Empty stars indicate no filter active. | N/A | N/A | None | UX clarity |
| FR-006-07 | Position filter control to the left of photo layout selection | Filter control rendered in PhotoThumbPanelControl.vue, positioned before (to the left of) existing layout buttons (squares, justified, masonry, grid). | N/A | N/A | None | User requirement (placement) |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-006-01 | Filtering must be instant (no API call, client-side only) | Performance - immediate user feedback | Filter applied synchronously via computed property, no observable delay | Vue 3 reactivity, photo data already loaded | User requirement (frontend-only) |
| NFR-006-02 | Filter control must be accessible via keyboard | Accessibility - keyboard navigation support | Tab to focus stars, Enter/Space to select, arrow keys to navigate stars, aria-labels present | PrimeVue accessibility features | WCAG 2.1 AA |
| NFR-006-03 | Component code must follow Vue 3 Composition API and TypeScript conventions | Code quality and maintainability | Follows existing patterns in PhotoThumbPanelControl.vue, TypeScript types for props/state | Vue 3, TypeScript, existing codebase patterns | [docs/specs/3-reference/coding-conventions.md](docs/specs/3-reference/coding-conventions.md) |
| NFR-006-04 | Filtering performance must handle 1000+ photos smoothly | Performance - large album support | Computed property recalculation completes within 100ms for 1000 photos | Vue 3 computed property optimization | User experience |

## UI / Interaction Mock-ups

### PhotoThumbPanelControl with Star Filter (No Filter Active)
```
┌────────────────────────────────────────────────────┐
│ Photos Panel                                       │
├────────────────────────────────────────────────────┤
│                                                    │
│ [☆][☆][☆][☆][☆]  [⊞][≡][⊟][▦]                    │
│  ^Star Filter^    ^Layout buttons^                │
│                                                    │
│  Photo grid below...                               │
└────────────────────────────────────────────────────┘

Legend:
  [☆] = Empty star (no filter active)
  Filter only visible if at least one photo has rating
```

### Star Filter Active (Minimum 3 Stars Selected)
```
┌────────────────────────────────────────────────────┐
│ Photos Panel                                       │
├────────────────────────────────────────────────────┤
│                                                    │
│ [★][★][★][☆][☆]  [⊞][≡][⊟][▦]                    │
│  ^3+ stars^       ^Layout buttons^                │
│                                                    │
│  Showing photos with rating ≥ 3 stars             │
│  (excludes 1-2 star and unrated photos)            │
└────────────────────────────────────────────────────┘

Legend:
  [★] = Filled star (stars 1-3 filled → filter ≥ 3)
  [☆] = Empty star (stars 4-5 not part of threshold)
  Click on star 3 again → clear filter
```

### Star Filter Hover Interaction
```
User hovers over star 4:
[★][★][★][★*][☆]
          ^Hover highlight

User clicks star 4:
[★][★][★][★][☆]  → Filter set to ≥ 4 stars
                    Shows 4 and 5 star photos only

User clicks star 4 again:
[☆][☆][☆][☆][☆]  → Filter cleared
                    Shows all photos
```

### Filter Hidden (No Rated Photos)
```
┌────────────────────────────────────────────────────┐
│ Photos Panel                                       │
├────────────────────────────────────────────────────┤
│                                                    │
│ [⊞][≡][⊟][▦]  (Star filter hidden)                │
│  ^Layout buttons^                                  │
│                                                    │
│  All photos shown (none have ratings)              │
└────────────────────────────────────────────────────┘

Legend:
  Star filter not rendered when no photos have ratings
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-006-01 | Album has no rated photos → star filter hidden, all photos displayed |
| S-006-02 | Album has ≥1 rated photo → star filter visible (5 empty stars) |
| S-006-03 | User clicks star 3 (no filter active) → filter set to ≥3, stars 1-3 filled, photos filtered to show 3+ star ratings |
| S-006-04 | User clicks star 5 → filter set to ≥5, all 5 stars filled, only 5-star photos shown |
| S-006-05 | User clicks star 1 → filter set to ≥1, star 1 filled, all rated photos shown (excludes unrated) |
| S-006-06 | User clicks star 3 when filter already ≥3 → filter cleared, all stars empty, all photos shown |
| S-006-07 | User clicks star 4, then clicks star 2 → filter changes from ≥4 to ≥2, stars 1-2 filled, photos with 2+ stars shown |
| S-006-08 | User navigates within album with filter active → filter state persists |
| S-006-09 | User reloads page → filter state resets to no filter (all photos shown) |
| S-006-10 | User rates a photo while filter active → filtered list updates reactively if photo meets threshold |

## Test Strategy
- **Core:** N/A (no backend changes)
- **Application:** N/A (no backend changes)
- **REST:** N/A (no API changes)
- **CLI:** N/A (no CLI changes)
- **UI (JS/Selenium):**
  - Unit tests for filter computed property logic (≥ threshold filtering)
  - Unit tests for hasRatedPhotos detection
  - Unit tests for toggle behavior (click star → set filter, click again → clear)
  - Component tests for PhotoThumbPanelControl with filter rendering
  - Integration tests for filter state persistence in PhotosState store
  - Visual regression tests for star filter UI (empty, partial filled, all filled)
  - Accessibility tests for keyboard navigation (Tab, Enter, Arrow keys)
  - Performance tests with 1000+ photos
- **Docs/Contracts:** N/A (no API contracts)

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-006-01 | Photo data with user_rating field (existing PhotoResource) | UI |

### API Routes / Services
N/A - No API changes required

### CLI Commands / Flags
N/A - No CLI changes required

### Telemetry Events
N/A - No telemetry events (per project scope)

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-006-01 | resources/js/components/gallery/photoModule/__tests__/fixtures/photos-rating-filter.json | Sample photo data with various ratings (0-5) for filter testing |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-006-01 | No filter active (empty stars) | Default state or user clears filter. All photos displayed (if rated photos exist, filter is visible). |
| UI-006-02 | Filter active (≥N stars filled) | User clicks star N. Stars 1-N filled, stars N+1-5 empty. Photos with rating ≥ N shown. |
| UI-006-03 | Filter hidden (no rated photos) | Album has no photos with ratings. Star filter control not rendered. |
| UI-006-04 | Star hover state | User hovers over star N. Visual highlight on star N (preview state). |
| UI-006-05 | Star focused (keyboard nav) | User tabs to star filter. Visual focus outline on current star. |

## Telemetry & Observability
No telemetry events are defined for this feature per project scope.

## Documentation Deliverables
- Update [docs/specs/4-architecture/roadmap.md](docs/specs/4-architecture/roadmap.md) with Feature 006 entry
- Update [docs/specs/4-architecture/knowledge-map.md](docs/specs/4-architecture/knowledge-map.md) with:
  - PhotoThumbPanelControl.vue modifications (star filter control)
  - PhotosState.ts modifications (filter state property)
  - Filtering logic documentation

## Fixtures & Sample Data
Create fixture file `resources/js/components/gallery/photoModule/__tests__/fixtures/photos-rating-filter.json` with sample photo data including:
- Photos with ratings 1-5 (at least 2 photos per rating level)
- Photos with no rating (user_rating: null or 0)
- Album with no rated photos (all user_rating: null)
- Album with mixed rated/unrated photos

## Spec DSL

```yaml
domain_objects:
  - id: DO-006-01
    name: Photo (existing PhotoResource)
    fields:
      - name: id
        type: string
      - name: user_rating
        type: integer | null
        constraints: "0-5 or null"

routes: []

cli_commands: []

telemetry_events: []

fixtures:
  - id: FX-006-01
    path: resources/js/components/gallery/photoModule/__tests__/fixtures/photos-rating-filter.json
    purpose: Sample photo data for rating filter testing

ui_states:
  - id: UI-006-01
    description: No filter active (empty stars)
  - id: UI-006-02
    description: Filter active (≥N stars filled)
  - id: UI-006-03
    description: Filter hidden (no rated photos)
  - id: UI-006-04
    description: Star hover state
  - id: UI-006-05
    description: Star focused (keyboard navigation)

ui_components:
  - id: UC-006-01
    name: PhotoThumbPanelControl.vue (modified)
    location: resources/js/components/gallery/photoModule/PhotoThumbPanelControl.vue
    modifications: Add star filter control (5 clickable stars) before layout buttons
  - id: UC-006-02
    name: PhotosState.ts (modified)
    location: resources/js/stores/PhotosState.ts
    modifications: Add photo_rating_filter property (null | 1-5) for filter state
```

## Appendix

### Resolved Open Questions
All open questions (Q-006-01, Q-006-02, Q-006-03, Q-006-04) have been resolved and incorporated into the spec:

- **Q-006-01:** Filter UI uses hoverable star list with minimum threshold filtering and toggle-off
- **Q-006-02:** Unrated photos excluded from filtered results (addressed by minimum threshold logic)
- **Q-006-03:** Filter state persisted in Pinia store (like NSFW visibility), not localStorage
- **Q-006-04:** Minimum threshold filtering (≥ N stars) rather than exact match or multi-select

### Implementation Notes

1. **Component Architecture:**
   - Modify existing `PhotoThumbPanelControl.vue` to add star filter control
   - Add computed property `hasRatedPhotos` to check if any photo has user_rating > 0
   - Render star filter conditionally: `v-if="hasRatedPhotos"`
   - Add computed property `filteredPhotos` to PhotosState or parent component

2. **State Management:**
   - Add `photo_rating_filter: null | 1 | 2 | 3 | 4 | 5` to PhotosState store
   - Default value: `null` (no filter active)
   - Action: `setPhotoRatingFilter(rating: null | 1-5)`
   - Getter: `photoRatingFilter`

3. **Filtering Logic:**
   ```typescript
   const filteredPhotos = computed(() => {
     const filter = photosStore.photoRatingFilter;
     const hasRated = photos.value.some(p => p.user_rating > 0);

     // Only apply filter if active AND rated photos exist
     if (filter === null || !hasRated) {
       return photos.value;
     }

     return photos.value.filter(p =>
       p.user_rating !== null &&
       p.user_rating >= filter
     );
   });
   ```

4. **Star Control Component:**
   - 5 clickable star icons (PrimeVue `pi-star` and `pi-star-fill`)
   - Stars 1-N filled when filter = N
   - Click star N: if filter !== N → set filter to N, else → set filter to null
   - Hover effect on stars (preview state)
   - Aria-labels: "Filter by N stars or higher" for each star
   - Keyboard support: Tab to focus, Arrow keys to select star, Enter to activate

5. **Styling Considerations:**
   - Star filter inline with layout buttons: `flex flex-row items-center gap-2`
   - Stars grouped with small gap: `inline-flex gap-1`
   - Star size: match layout button icon size (e.g., `text-lg` or `w-5 h-5`)
   - Filled stars: `text-yellow-500` or `text-primary`
   - Empty stars: `text-gray-300 dark:text-gray-600`
   - Hover: `hover:text-yellow-400 cursor-pointer`
   - Separator between filter and layout buttons: border or margin

6. **Accessibility:**
   - Star filter wrapped in `<div role="group" aria-label="Filter by star rating">`
   - Each star button: `<button aria-label="Filter by N stars or higher" aria-pressed="true|false">`
   - Keyboard navigation: Tab into group, Arrow Left/Right to navigate stars, Enter/Space to select
   - Focus visible outline on keyboard navigation

7. **Mobile Responsiveness:**
   - On narrow screens (<768px), consider icon-only layout (no text labels)
   - Stars may reduce size slightly on mobile
   - Ensure touch targets are ≥44px for accessibility

### Performance Considerations

- Filtering is client-side computed property (reactive)
- Vue automatically caches computed results and only recalculates when dependencies change
- For 1000 photos: Array.filter() with simple comparison should complete in <10ms on modern devices
- No API calls, no loading states needed

### Edge Cases

1. **All photos have same rating:**
   - If all photos rated 3 stars, filter ≥4 shows empty grid (valid behavior)

2. **User rates photo while filter active:**
   - Reactive computed property automatically updates filtered list
   - Photo appears/disappears based on new rating vs threshold

3. **User deletes all rated photos:**
   - `hasRatedPhotos` becomes false → filter control hides
   - Filter state remains in store but not applied (all photos shown)

4. **Filter set to ≥5, user changes photo rating from 5 to 4:**
   - Photo disappears from filtered list (no longer meets threshold)

### Data Flow

```
User clicks star N
  → PhotoThumbPanelControl emits setPhotoRatingFilter(N)
    → PhotosState.photo_rating_filter updated to N
      → filteredPhotos computed property recalculates
        → Photo grid re-renders with filtered list
```

### Integration with Existing Features

- **Feature 001 (Photo Star Rating):** This feature depends on Feature 001's user_rating data
- **Photo layout buttons:** Filter control positioned to the left, both controls coexist
- **NSFW visibility:** Similar state management pattern (PhotosState, session-only persistence)
- **Photo grid rendering:** Existing PhotoThumbPanelList receives filtered array instead of all photos

### Future Enhancements (Out of Scope)

- Exact rating match filter (show only 3-star photos)
- Multi-select filter (checkboxes for 4 AND 5 stars)
- Filter for unrated photos explicitly
- Combined filters (rating + date range + tags)
- Save filter presets
- URL query parameter support for shareable filtered views
- Backend filtering (API query parameter `?min_rating=3`)
