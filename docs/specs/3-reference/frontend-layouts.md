# Frontend Layout System

This document explains how Lychee's dynamic photo layout system works, including the four different layout algorithms and their implementation details.

## Overview

Lychee provides a sophisticated layout system that dynamically arranges photo thumbnails in different visual patterns. The system supports four distinct layout modes, each optimized for different use cases and visual preferences:

1. **Square Layout** - Uniform grid with square thumbnails
2. **Justified Layout** - Flickr-style rows with consistent heights
3. **Masonry Layout** - Pinterest-style columns with variable heights
4. **Grid Layout** - Regular grid preserving aspect ratios

## Layout Architecture

### Core Components

The layout system is built around several key files:

- **`PhotoLayout.ts`** - Main layout orchestrator and configuration management
- **`useSquare.ts`** - Square grid layout implementation
- **`useJustify.ts`** - Justified row layout implementation
- **`useMasonry.ts`** - Masonry column layout implementation
- **`useGrid.ts`** - Regular grid layout implementation
- **`getWidth.ts`** - Dynamic width calculation utilities
- **`types.d.ts`** - TypeScript interfaces and type definitions

### Layout Selection

The layout system uses a factory pattern to select and activate the appropriate layout algorithm:

```typescript
function activateLayout() {
  switch (layout.value) {
    case "square": return useSquare(...)
    case "justified": return useJustify(...)
    case "masonry": return useMasonry(...)
    case "grid": return useGrid(...)
  }
}
```

## Layout Algorithms

### 1. Square Layout (`useSquare.ts`)

**Purpose**: Creates a uniform grid where all photos are displayed as perfect squares.

**Key Characteristics:**
- All thumbnails have identical square dimensions
- Photos are cropped to fit square aspect ratio
- Regular grid pattern with consistent spacing
- Optimal for clean, organized appearance

**Algorithm Details:**
```typescript
// Calculate grid dimensions
const perChunk = Math.floor((max_width + grid_gap) / target_width_height)
const grid_width = target_width_height + spread

// Position each item in a regular grid
e.style.width = grid_width + "px"
e.style.height = grid_width + "px"  // Square aspect ratio
e.style.left = column.left + "px"
```

**Configuration Parameters:**
- `photo_layout_square_column_width` - Target width/height for square thumbnails
- `photo_layout_gap` - Spacing between thumbnails

**Use Cases:**
- Instagram-style photo grids
- Portfolio presentations
- Clean, minimalist interfaces
- Equal visual weight for all photos

---

### 2. Justified Layout (`useJustify.ts`)

**Purpose**: Creates Flickr-style rows where photos maintain their aspect ratios while keeping row heights consistent.

**Key Characteristics:**
- Preserves original photo aspect ratios
- Consistent row heights across the grid
- Photos scaled to fit perfectly within rows
- Uses the `justified-layout` library for optimal spacing

**Algorithm Details:**
```typescript
// Calculate aspect ratios for all photos
const ratio: number[] = justifiedItems.map(photo => {
  return height > 0 ? width / height : 1
})

// Use justified-layout library to calculate optimal positioning
const layoutGeometry = createJustifiedLayout(ratio, {
  containerWidth: width,
  containerPadding: 0,
  targetRowHeight: photoDefaultHeight,
})

// Apply calculated dimensions and positions
e.style.width = layoutGeometry.boxes[i].width + "px"
e.style.height = layoutGeometry.boxes[i].height + "px"
```

**Configuration Parameters:**
- `photo_layout_justified_row_height` - Target height for each row (default: 320px)

**Use Cases:**
- Professional photo galleries
- Showcasing photography with varied aspect ratios
- Optimal space utilization
- Maintaining photo composition integrity

---

### 3. Masonry Layout (`useMasonry.ts`)

**Purpose**: Creates a Pinterest-style layout with columns of varying heights, preserving aspect ratios.

**Key Characteristics:**
- Preserves original photo aspect ratios
- Variable column heights create organic flow
- Photos placed in shortest available column
- Optimal for diverse photo dimensions

**Algorithm Details:**
```typescript
// Calculate aspect ratios
const ratio = gridItems.map(photo => width / height)

// Find shortest column for placement
idx = findSmallestIdx(columns)
const column = columns[idx]
const height = grid_width / ratio[i]

// Position photo in shortest column
e.style.height = height + "px"
e.style.top = column.height + "px"
column.height = column.height + height + grid_gap
```

**Column Selection Strategy:**
```typescript
function findSmallestIdx(columns: Column[]) {
  // Find column with minimum height
  return columns.reduce((minIdx, col, i) => 
    col.height < columns[minIdx].height ? i : minIdx
  , 0)
}
```

**Configuration Parameters:**
- `photo_layout_masonry_column_width` - Target width for columns
- `photo_layout_gap` - Spacing between photos

**Use Cases:**
- Pinterest-style browsing
- Mixed media galleries
- Varied photo dimensions
- Organic, flowing layouts

---

### 4. Grid Layout (`useGrid.ts`)

**Purpose**: Creates a regular grid where photos maintain aspect ratios within column constraints.

**Key Characteristics:**
- Fixed column widths with variable heights
- Preserves aspect ratios within columns
- Regular row alignment across columns
- Balanced between uniformity and aspect ratio preservation

**Algorithm Details:**
```typescript
// Calculate photo dimensions preserving aspect ratio
const ratio = gridItems.map(photo => width / height)
const height = Math.floor(grid_width / ratio[i])

// Align photos in rows across columns
if (idx % perChunk === 0) {
  const newTop = Math.max(...columns.map(column => column.height))
  columns.forEach(column => column.height = newTop)
}

e.style.width = grid_width + "px"
e.style.height = height + "px"
```

**Row Synchronization:**
The grid layout ensures photos are aligned in rows by synchronizing column heights at the start of each new row.

**Configuration Parameters:**
- `photo_layout_grid_column_width` - Target width for grid columns
- `photo_layout_gap` - Spacing between photos

**Use Cases:**
- Traditional photo galleries
- Balanced visual presentation
- Consistent column structure
- Professional portfolios

## Dynamic Width Calculation

The `getWidth.ts` utility calculates available container width considering various UI factors:

```typescript
export function getWidth(timelineData: TimelineData, route: RouteLocationNormalizedLoaded): number {
  const baseWidth = window.innerWidth
  const paddingLeftRight = 2 * 18
  let scrollBarWidth = 15
  
  if (isTouchDevice()) {
    scrollBarWidth = 0  // Touch devices hide scrollbars
  }
  
  // Account for timeline border if visible
  let timeLineBorder = 0
  if (timelineData.isTimeline.value && timelineData.isLeftBorderVisible.value) {
    timeLineBorder = 50
  }
  
  return baseWidth - paddingLeftRight - scrollBarWidth - timeLineBorder
}
```

**Width Factors Considered:**
- Window inner width
- Left/right padding (36px total)
- Scrollbar width (15px on desktop, 0px on touch devices)
- Timeline border width (50px when timeline is active)
- Route-specific adjustments

## Layout Configuration Management

### Configuration Loading

Layout configurations are loaded dynamically from the server:

```typescript
export function useGetLayoutConfig() {
  const layoutConfig = ref<App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>()
  
  function loadLayoutConfig(): Promise<void> {
    return AlbumService.getLayout().then((data) => {
      layoutConfig.value = data.data
    })
  }
  
  return { layoutConfig, loadLayoutConfig }
}
```

### Layout Classes

Visual feedback for layout selection is provided through dynamic CSS classes:

```typescript
export function useLayoutClass(layout: Ref<App.Enum.PhotoLayoutType>) {
  const BASE = "my-0 w-5 h-5 mr-0 ml-0 transition-all duration-300 group-hover:scale-150"
  
  const squareClass = computed(() => 
    BASE + (layout.value === "square" ? "stroke-primary-400" : "stroke-neutral-400")
  )
  // Similar for justified, masonry, grid...
}
```

## Timeline Integration

All layouts support timeline mode, which affects:

- **Width Calculation**: Timeline border reduces available width
- **Layout Positioning**: Photos positioned relative to timeline border
- **Visual Indicators**: Timeline-specific UI elements

Timeline data structure:
```typescript
export type TimelineData = {
  isTimeline: Ref<boolean>
  isLeftBorderVisible: Ref<boolean>
}
```

## RTL Language Support

The layout system supports right-to-left languages:

```typescript
const { isLTR } = useLtRorRtL()
const align = isLTR() ? "left" : "right"

// Apply positioning based on text direction
e.style[align] = column.left + "px"
```

## Performance Considerations

### Efficient DOM Manipulation

All layouts use direct DOM manipulation for optimal performance:

```typescript
// Filter to only element nodes (nodeType === 1)
const gridItems = [...el.childNodes].filter(gridItem => gridItem.nodeType === 1)

// Direct style property assignment
e.style.top = column.height + "px"
e.style.width = grid_width + "px"
```

### Column-Based Algorithms

Masonry and square layouts use column-based algorithms for O(n) complexity:

```typescript
// Efficient column tracking
const columns: Column[] = Array.from({ length: perChunk }, (_, idx) => ({
  height: 0, 
  left: (grid_gap + grid_width) * idx 
}))
```

### Memory Management

- Layouts reuse existing DOM elements
- Minimal object allocation during layout calculations
- Efficient array operations for positioning

## Layout Responsiveness

### Dynamic Recalculation

Layouts automatically recalculate when:
- Window resizes
- Layout mode changes
- Timeline visibility toggles
- Container width changes

### Mobile Optimization

- Touch device detection for scrollbar width
- Responsive column counts based on available width
- Optimized touch targets for mobile interaction

## Integration with Gallery Components

### Photo Thumbnail Integration

Layouts work with photo thumbnail components that provide:
- `data-width` and `data-height` attributes for aspect ratio calculation
- Absolute positioning support
- Responsive image loading

### Timeline Component Integration

Timeline layouts coordinate with:
- Timeline border visibility
- Date separator positioning
- Scroll synchronization

## Best Practices

### Layout Selection Guidelines

- **Square**: Use for uniform, clean presentations
- **Justified**: Best for professional photo galleries
- **Masonry**: Ideal for varied content dimensions
- **Grid**: Good balance between structure and flexibility

### Performance Optimization

- Layouts are applied after DOM elements are rendered
- Batch DOM updates for better performance
- Use `requestAnimationFrame` for smooth transitions

### Accessibility Considerations

- Maintain logical tab order regardless of visual layout
- Ensure adequate spacing for touch targets
- Support keyboard navigation patterns

---

## Related Documentation

- [Frontend Architecture](frontend-architecture.md) - Overall frontend architecture, Vue3 patterns, and state management
- [Frontend Gallery Views](frontend-gallery.md) - Gallery interface and viewing modes
- [Coding Conventions](coding-conventions.md) - Coding standards including Vue3/TypeScript conventions

---

*Last updated: December 22, 2025*
