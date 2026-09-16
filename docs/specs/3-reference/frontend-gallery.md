# Frontend Gallery Views

This document provides detailed information about Lychee's gallery frontend, including the different viewing modes and the comprehensive component architecture that powers the photo browsing experience.

## Gallery Viewing Modes

Lychee provides multiple specialized viewing modes, each optimized for different use cases and user experiences. All modes share common functionality while providing unique features.

### 1. Albums View (`Albums.vue`)

**Purpose**: Root gallery view showing all albums, smart albums, and collections.

**Key Features:**
- **Smart Albums**: Recent, Highlighted, Shared, Tagged albums displayed at the top
- **Pinned Albums**: User-pinned albums for quick access
- **Shared Albums**: Albums shared with the current user
- **Regular Albums**: All other user albums in hierarchical view
- **Album Management**: Create, edit, delete, merge, move operations
- **Bulk Operations**: Multi-select with context menus
- **Upload Support**: Drag & drop file upload to root or specific albums

**Navigation:** Accessed via `/gallery` route (root view)

**Technical Details:**
```typescript
// Key composables used
const { albums, smartAlbums, pinnedAlbums, sharedAlbums } = useAlbumsRefresher()
const { selectedAlbums, albumClick, unselect } = useSelection()
const { menu, albumMenuOpen } = useContextMenu()
```

**User Interactions:**
- Click album → Navigate to Album view
- Right-click → Context menu with album operations
- Keyboard shortcuts: `h` (toggle Sensitive albums), `f` (fullscreen), `a` (select all)
- File drag & drop for uploads

---

### 2. Album View (`Album.vue`)

**Purpose**: Individual album view showing photos and sub-albums within a specific album.

**Key Features:**
- **Photo Grid**: Justified or square layout with photo thumbnails
- **Sub-albums**: Nested album navigation
- **Photo Operations**: Star, rotate, tag, copy, move, delete
- **Album Information**: Title, description, statistics
- **Password Protection**: Unlock protected albums
- **Timeline View**: Chronological photo organization
- **Photo Preview**: Click photo → Full-screen photo view
- **Batch Operations**: Multi-select photos for bulk actions

**Navigation:** `/gallery/{albumId}` and `/gallery/{albumId}/{photoId}`

**Technical Details:**
```typescript
// Core album management
const { album, photos, children, rights } = useAlbumRefresher(albumId, photoId)
const { selectedPhotos, photoSelect } = useSelection()
const { slideshow, next, previous } = useSlideshowFunction()
```

**Layout Modes:**
- **Grid View**: Square or justified photo thumbnails
- **Timeline View**: Photos organized by date
- **Photo View**: Full-screen photo display with overlay information

---

### 3. Favourites View (`Favourites.vue`)

**Purpose**: Dedicated view for favourite photos across all albums.

**Key Features:**
- **Aggregated Favourites**: All favourite photos in one place
- **Cross-Album Access**: Photos from different albums shown together
- **Quick Navigation**: Click photo → Navigate to original album context
- **Persistent State**: Favourites stored in Pinia store with persistence
- **Minimal Interface**: Streamlined view focused on browsing favourites

**Navigation:** `/gallery/favourites`

**Technical Details:**
```typescript
const favourites = useFavouriteStore()
const photos = computed(() => favourites.photos ?? [])
// Simplified navigation - no complex album operations
```

**User Experience:**
- Clean, distraction-free interface
- Maintains album context when clicking photos
- Perfect for revisiting preferred images

---

### 4. Flow View (`Flow.vue`)

**Purpose**: Instagram-style infinite scroll feed showing photos from recent albums.

**Key Features:**
- **Infinite Scroll**: Lazy-loaded content as user scrolls
- **Card-Based Layout**: Albums displayed as cards with hero images
- **Lightbox Integration**: Full-screen photo viewer with navigation
- **Touch Optimized**: Gesture support for mobile devices
- **Performance Optimized**: Intersection observer for efficient loading
- **Sensitive Handling**: Blur/consent system for sensitive content

**Navigation:** `/flow`

**Technical Details:**
```typescript
const { albums, loadMore } = useFlowService()
const { selectedPhoto, setSelection } = useState()
// Intersection observer for infinite scroll
const { stop } = useIntersectionObserver(sentinel, loadMore)
```

**User Experience:**
- Modern social media-style browsing
- Optimized for discovery and casual browsing
- Seamless photo-to-photo navigation

---

### 5. Frame View (`Frame.vue`)

**Purpose**: Fullscreen slideshow mode designed for digital photo frames or presentation displays.

**Key Features:**
- **Automatic Slideshow**: Timed photo rotation
- **Fullscreen Mode**: Immersive display without UI elements
- **Background Blur**: Artistic presentation with blurred backgrounds
- **Configurable Timing**: Server-controlled refresh intervals
- **Random Selection**: Photos selected randomly from specified album
- **Minimal Controls**: Simple back navigation

**Navigation:** `/frame` or `/frame/{albumId}`

**Technical Details:**
```typescript
// Auto-fullscreen on mount
document.documentElement.requestFullscreen()

// Automatic photo rotation
const { slideshow } = useSlideshowFunction(refreshTimeout)
AlbumService.frame(albumId).then(response => {
  imgSrc.value = response.data.src
  refreshTimeout.value = response.data.timeout
})
```

**Use Cases:**
- Digital photo frames
- Kiosk displays
- Presentation modes
- Ambient display systems

---

### 6. Map View (`Map.vue`)

**Purpose**: Geographic view showing photos plotted on an interactive map based on GPS coordinates.

**Key Features:**
- **Interactive Map**: Leaflet.js-powered mapping with tile layers
- **Photo Markers**: Clickable markers showing photo thumbnails
- **Clustering**: Grouped markers for dense photo areas
- **GPS Track Display**: GPX track overlay when available
- **Popup Previews**: Photo preview popups on marker click
- **Dynamic Bounds**: Auto-zoom to fit all photos
- **Provider Support**: Configurable map tile providers

**Navigation:** `/map` or `/map/{albumId}`

**Technical Details:**
```typescript
// Leaflet integration
import L from 'leaflet'
import 'leaflet.markercluster'
import '@lychee-org/leaflet.photo'

// Photo clustering and positioning
photoLayer.value = L.photo.cluster().on('click', showPopup)
map.value.fitBounds(calculatePhotoBounds())
```

**Geographic Features:**
- Marker clustering for performance
- Custom photo thumbnails as markers
- GPS track visualization
- Responsive zoom and pan

**Feature 067 (v3, SoA) path**: behind the same `is_struct_of_array_enabled` flag other v3 tiers use, `Map.vue` dispatches between the v2 behavior above (unchanged, `fetchData()`/`addContentsToMap()`) and a viewport-driven path sourced from `resources/js/stores/MapState.ts`. Leaflet's `moveend`/`zoomend` events call a debounced `requestViewport(bounds, zoom)`, which dedupes an identical/in-flight snapped-viewport request and fetches `GET /api/v3/Map/buckets` + `/Map/Photos` in parallel (`resources/js/services/map-v3-service.ts`). A bucket whose count exceeds the fixed leaf threshold (`20`) renders as a plain count-badge marker (click zooms in, never fetches members); a leaf-tier photo reuses the existing `clusterFunc()`/`.leaflet-marker-photo` template, with its marker image assigned asynchronously once `ThumbAssetService.acquire()`'s object URL resolves (no backend-supplied thumbnail URL in this tier, Q-067-12) — the same async-assignment pattern `Thumb.vue` uses. GPX tracks are fetched once per album (`MapState.ts.loadTracks()`), decoupled from pan/zoom. See `docs/specs/4-architecture/features/067-map-geo-bucketing/`.

---

### 7. Search View (`Search.vue`)

**Purpose**: Comprehensive search interface for finding photos and albums across the entire gallery.

**Key Features:**
- **Universal Search**: Search across photos, albums, tags, and metadata
- **Real-time Results**: Debounced search with instant feedback
- **Advanced Filtering**: Filter by album, date, tags, or metadata
- **Pagination**: Efficient handling of large result sets
- **Photo Integration**: Seamless transition to photo viewer
- **Search History**: Recent searches and suggestions

**Navigation:** `/search` with query parameters

**Technical Details:**
```typescript
const { search_term, search_results } = useSearch()
const debouncedSearch = useDebounceFn(performSearch, 300)

// Real-time search with debouncing
watch(search_term, debouncedSearch)
```

**Search Capabilities:**
- Text search across titles, descriptions, tags
- Metadata search (EXIF, camera settings)
- Date range filtering (soon)
- Album-scoped searches

---

## Gallery Component Architecture

The gallery components are organized into specialized modules, each handling specific aspects of the photo browsing experience.

### Album Module (`albumModule/`)

The album module handles all album-related UI components and interactions.

#### Core Album Components

**`AlbumPanel.vue`**
- Main container for album content display
- Integrates photo grids, sub-albums, and album information
- Handles layout switching and responsive design
- Manages scroll position and pagination

**`AlbumHero.vue`**
- Album header section with title, description, and statistics
- Cover photo display and album metadata
- Action buttons for album operations (edit, share, etc.)
- Breadcrumb navigation for nested albums

**`AlbumStatistics.vue`**
- Displays album metrics: photo count, size, date ranges
- Creation and modification timestamps
- Storage usage and file type breakdown
- Performance metrics when available

#### Thumbnail Panels

**`AlbumThumbPanel.vue`**
- Album thumbnail container
- Handles layout modes (grid, justified, timeline)
- Manages selection state and bulk operations
- Responsive grid sizing and spacing

**`AlbumThumbPanelList.vue`**
- The actual implementation of the display of albums thumbs.

**`PhotoThumbPanel.vue`**
- Photo thumbnail container
- Justified layout engine integration
- Thumbnail generation and caching

**`PhotoThumbPanelControl.vue`**
- Control bar for photo thumbnail panels
- Layout switcher (grid/justified/timeline)

**`PhotoThumbPanelList.vue`**
- The actual implementation of the display of albums thumbs.

**`SensitiveWarning.vue`**
- Sensitive content warning overlay
- Blur effects and content filtering
- Privacy and safety controls

#### Thumbnail Components (`thumbs/`)

**`AlbumThumb.vue`**
- Individual album thumbnail display
- Cover photo with overlay information
- Hover effects and interaction states
- Configurable aspect ratios and decorations

**`AlbumThumbDecorations.vue`**
- Decorative elements for album thumbnails
- Photo count badges and status indicators
- Lock icons for protected albums
- Star indicators for pinned albums

**`AlbumThumbImage.vue`**
- Album cover image component
- Lazy loading and progressive enhancement
- Responsive image sizing
- Fallback handling for missing covers

**`AlbumThumbOverlay.vue`**
- Text overlay for album thumbnails
- Title, description, and metadata display
- Responsive typography and truncation
- Accessibility improvements

**`PhotoThumb.vue`**
- Individual photo thumbnail component
- Square and justified layout support
- Selection states and visual feedback
- Context menu integration

**`ThumbBadge.vue`**
- Badge component for thumbnails
- Status indicators (highlighted, edited, etc.)
- Customizable colors and icons
- Positioned overlays

**`ThumbFavourite.vue`**
- Star/favourite indicator for thumbnails
- Interactive toggle functionality
- Animation states and feedback
- Persistent state management

---

### Flow Module (`flowModule/`)

The flow module implements the Instagram-style infinite scroll interface.

**`AlbumCard.vue`**
- Card-style album display for flow view
- Hero image with overlay information
- Touch-optimized interactions
- Card-based layout system

**`Blur.vue`**
- NSFW content blur component
- Animated blur effects
- User consent integration
- Progressive reveal system

**`CarouselImages.vue`**
- Image carousel within album cards
- Touch gesture navigation
- Thumbnail strip navigation
- Smooth transitions

**`HeaderImage.vue`**
- Header/hero images for album cards
- Responsive image sizing
- Lazy loading with intersection observer
- Fallback image handling

**`LigtBox.vue`**
- Full-screen photo viewer for flow mode
- Swipe navigation between photos
- Zoom and pan functionality
- Metadata overlay integration

**`TopImages.vue`**
- Featured/top image selection
- Algorithm-based photo selection
- Quality scoring and ranking
- Dynamic content curation

---

### Photo Module (`photoModule/`)

The photo module handles individual photo display and interaction.

**`PhotoPanel.vue`**
- Main photo display container
- Full-screen photo viewer
- Rotation controls
- Metadata and EXIF display

**`PhotoBox.vue`**
- Photo container with aspect ratio management
- Loading states and progressive enhancement
- Error handling and fallbacks
- Responsive sizing

**`Overlay.vue`**
- Information overlay for photos
- EXIF data, camera settings, location
- Toggleable display modes
- Responsive layout adaptation

**`Dock.vue`**
- Photo action toolbar
- Navigation, editing, and sharing controls
- Contextual button display
- Mobile-optimized layout

**`DockButton.vue`**
- Individual action buttons for photo dock
- Icon and label combinations
- Disabled states and permissions
- Tooltip integration

**`NextPrevious.vue`**
- Photo navigation controls for previous/next photos
- Keyboard shortcut integration
- RTL language support
- Smooth transitions

**`ColourSquare.vue`**
- Color display component
- Image color analysis

**`LinksInclude.vue`**
- External link integration
- Share button functionality
- Copy link functionality

**`MapInclude.vue`**
- Embedded map for geotagged photos
- GPS coordinate display
- Interactive map integration
- Location context information

---

### Search Module (`searchModule/`)

The search module provides comprehensive search functionality.

**`SearchPanel.vue`**
- Main search interface
- Search input with autocomplete
- Filter controls and options
- Recent searches and suggestions

**`ResultPanel.vue`**
- Search results display
- Mixed content (photos/albums) results
- Pagination and infinite scroll
- Result highlighting and relevance

---

### Timeline Module (`timelineModule/`)

The timeline module implements the global Timeline's date-scrub rail (`Timeline.vue`'s own right-hand navigation), separate from the album module's per-album grid.

**`TimelineDatesV3.vue`**
- The v3 (Struct-of-Arrays) date rail — mounted only when `isTimelineSoaActive` is true; `TimelineDates.vue` (below) is the untouched v2 fallback, a deliberate fork rather than shared code.
- A narrow, sticky flex sibling of the photo grid, not an overlay — `Timeline.vue` wraps both in one `<div class="flex">`, the same pattern `AlbumNavPanel.vue` uses alongside `AlbumPanel.vue`'s grid.
- **At rest**, shows only year labels and month ticks:
  - A bold year number marks the first (most recent) bucket of that year.
  - A small dot marks a month with at least one photo; a short dash marks a "quarter" month specifically (January/April/July/October), drawn larger so the rail reads like a ruler with minor (month) and major (quarter) ticks. Months with no photos get no tick at all — the rail reflects actual data density, not a fixed calendar grid.
  - **Spacing between ticks is proportional to real scroll distance, not calendar time.** Every tick's position is `(bucket.top / totalHeight) * railHeight`, using the same pixel layout `PhotoGridVirtual.vue` computes for the grid itself — photo count, row-packing under the active layout mode (justified/masonry/square/grid/list), photo aspect ratios, and the browser's container width all feed into it. A year with more photos (or whose photos pack into more rows) takes up proportionally more of the rail; a sparse year compresses to a sliver.
  - A month tick that would land within 16px of a year label is dropped rather than repositioned, so every rendered tick's position stays exactly true to the real layout (an earlier attempt that pushed colliding ticks down instead caused the push to cascade across dozens of ticks per year, eventually dragging later years off the bottom of the rail entirely).
- **Hovering** shows a fisheye lens that magnifies nearby dates into readable labels near the cursor (with a collision-cull pass so labels never overlap), plus a readout pill with the exact date and photo count. The lens is anchored to the rail's own right edge (not floating beside it), so it fully covers — rather than merely sitting next to — the resting-state ticks while active.
- A playhead line continuously tracks the real scroll position.
- **Drag-to-scrub**: pressing and dragging on the rail scrubs the grid continuously by real pixel offset (rAF-throttled); releasing (or a plain click) finalizes to a route-pushed, deep-linkable bucket via the existing `@load`/`goToBucket` navigation.
- Ported from a Claude Design mockup (`Timeline Scrubber.html`) with the mockup's own hardcoded dark palette/fonts replaced by the app's semantic theme tokens (`text-primary`, `bg-toned`, etc.) so it stays correct in both light and dark mode.

**`TimelineDates.vue`**
- The v2 (legacy) date sidebar — an always-expanded, `position: fixed` list of every date that overlaps the rightmost photo column. Untouched fork, still used when the SoA flag is off.

**Data flow** (`PhotoGridVirtual.vue` → `Timeline.vue` → `TimelineDatesV3.vue`):
```typescript
// PhotoGridVirtual.vue emits, timeline-only:
activeBucketChanged   // bucket id currently scrolled into view
timelineLayoutChanged // { buckets: {bucketId,top,height}[], totalHeight } — every bucket's real (or placeholder-estimated) pixel geometry
scrollOffsetChanged   // continuous content-relative scroll position, for the playhead

// exposed for the rail's drag-scrub, without a route push:
scrollToPixelOffset(px: number)
```

---

## Component Interaction Patterns

### State Management Flow

```typescript
// Typical component interaction pattern
User Action → Component Event → Composable Function → Service Call → Store Update → Reactive UI Update
```

### Navigation Patterns

1. **Album Navigation**: Albums → Album → Photo → Back to Album
2. **Search Navigation**: Search → Results → Photo → Back to Search  
3. **Flow Navigation**: Flow → Album Card → Lightbox → Next Photo
4. **Map Navigation**: Map → Photo Marker → Photo View

### Selection Management

All gallery components use the unified selection system:

```typescript
const { selectedPhotos, selectedAlbums, hasSelection } = useSelection()
// Consistent selection behavior across all views
```

### Context Menus

Right-click context menus provide consistent operations:
- **Photo Context**: Star, Rotate, Tag, Copy, Move, Delete
- **Album Context**: Rename, Move, Merge, Pin, Delete, Download

### Keyboard Navigation

All gallery modes support comprehensive keyboard shortcuts:
- **Navigation**: Arrow keys, Space, Enter
- **Operations**: Letters (h, f, i, l, etc.)
- **Universal**: Escape (back), F (fullscreen)

## Performance Considerations

### Lazy Loading
- Components use `import()` for code splitting
- Images loaded progressively with intersection observer
- Virtual scrolling for large photo sets — `PhotoGridVirtual.vue` (`albumModule/Virtualized/`) windows rendering via `@tanstack/vue-virtual`, analytic (non-DOM-measured) WASM layout, behind the `is_struct_of_array_enabled` flag. Feature 065 wired the per-album photo grid this way; Feature 066 extended the same component (`source: "album"|"timeline"` prop) to the global Timeline view (`Timeline.vue`), adding incremental, bucket-windowed data fetching (`TimelineState.ts`'s v3 fields/actions) and layout-mode-correct placeholder sizing for not-yet-loaded date buckets, since a whole library — unlike one album — can't be fetched at once. See `docs/specs/4-architecture/features/066-timeline-struct-of-arrays/` and `docs/specs/4-architecture/features/065-photo-listing-struct-of-arrays-adoption/`.

### Caching Strategy
- Thumbnail caching at multiple resolutions
- API response caching with axios-cache-interceptor
- State persistence with Pinia

### Memory Management
- Component cleanup in `onUnmounted` hooks
- Event listener removal

### Mobile Optimization
- Touch gesture support with TinyGesture
- Responsive image sizing
- Mobile-first component design
- Performance-conscious animations

---

## Related Documentation

- [Frontend Architecture](frontend-architecture.md) - Overall frontend architecture, Vue3 patterns, and state management
- [Frontend Layout System](frontend-layouts.md) - Photo layout algorithms and responsive design patterns
- [Coding Conventions](coding-conventions.md) - Coding standards including Vue3/TypeScript conventions

---

*Last updated: September 13, 2026*
