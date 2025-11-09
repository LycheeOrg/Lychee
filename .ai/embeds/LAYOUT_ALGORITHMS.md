# Lychee Layout Algorithms - Reference Guide

## Overview

This document provides detailed mathematical and algorithmic specifications for all 5 gallery layout types in Lychee. These algorithms must be replicated exactly in the embeddable widget to ensure visual consistency with Lychee's native galleries.

## Common Concepts

### Aspect Ratio Calculation

```typescript
function getAspectRatio(photo: Photo): number {
  const width = photo.size_variants.original?.width ?? 1;
  const height = photo.size_variants.original?.height ?? 1;
  return height > 0 ? width / height : 1;
}
```

### Container Width

```typescript
function getContainerWidth(container: HTMLElement): number {
  return container.clientWidth;
}
```

### Positioning Method

All layouts use **absolute positioning** with JavaScript-calculated dimensions:

```typescript
element.style.position = 'absolute';
element.style.top = `${top}px`;
element.style.left = `${left}px`; // or 'right' for RTL
element.style.width = `${width}px`;
element.style.height = `${height}px`;
```

---

## 1. Square Layout

### Description
- All photos displayed as perfect squares
- Regular grid pattern with synchronized row heights
- Photos cropped to fit squares

### Configuration
- `squareSize`: Target size for squares (default: 200px)
- `layoutGap`: Gap between squares (default: 12px)

### Algorithm

#### Step 1: Calculate Columns

```typescript
function calculateColumns(
  containerWidth: number,
  squareSize: number,
  gap: number
): { columns: number; finalSquareSize: number } {
  // How many squares fit?
  const columns = Math.floor((containerWidth + gap) / squareSize);

  // Remaining space after fitting squares + gaps
  const remainingSpace =
    containerWidth - (columns * squareSize) - ((columns - 1) * gap);

  // Distribute remaining space evenly across all squares
  const spread = Math.ceil(remainingSpace / columns);

  // Final square size after distributing extra space
  const finalSquareSize = squareSize + spread;

  return { columns, finalSquareSize };
}
```

#### Step 2: Initialize Column Tracking

```typescript
interface ColumnData {
  left: number;  // Left position of this column
  height: number; // Current height of this column
}

const columnData: ColumnData[] = Array.from(
  { length: columns },
  (_, i) => ({
    left: i * (finalSquareSize + gap),
    height: 0
  })
);
```

#### Step 3: Position Photos

```typescript
photos.forEach((photo, index) => {
  const columnIndex = index % columns;
  const column = columnData[columnIndex];

  // Synchronize row heights at start of each new row
  if (index % columns === 0 && index > 0) {
    const maxHeight = Math.max(...columnData.map(c => c.height));
    columnData.forEach(c => c.height = maxHeight);
  }

  // Position photo as square
  positionPhoto(photo, {
    width: finalSquareSize,
    height: finalSquareSize, // Square!
    left: column.left,
    top: column.height
  });

  // Update column height
  column.height += finalSquareSize + gap;
});
```

#### Step 4: Set Container Height

```typescript
const maxHeight = Math.max(...columnData.map(c => c.height));
container.style.height = `${maxHeight}px`;
```

### Example Calculation

**Given**:
- Container width: 1000px
- Target square size: 200px
- Gap: 12px

**Calculation**:
```
columns = floor((1000 + 12) / 200) = floor(1012 / 200) = 5

remainingSpace = 1000 - (5 × 200) - (4 × 12)
               = 1000 - 1000 - 48
               = -48 (negative means we need more space)

Wait, let me recalculate:
Actually the formula ensures we DON'T exceed the width:

columns = floor((1000 + 12) / 200) = 5

Total used = (5 × 200) + (4 × 12) = 1000 + 48 = 1048 (too much!)

So we need to recalculate:
columns = floor(1000 / (200 + 12)) = floor(1000 / 212) = 4

Now:
remainingSpace = 1000 - (4 × 200) - (3 × 12)
               = 1000 - 800 - 36
               = 164

spread = ceil(164 / 4) = ceil(41) = 41

finalSquareSize = 200 + 41 = 241px
```

---

## 2. Justified Layout (Flickr-style)

### Description
- Photos arranged in rows with consistent height
- Aspect ratios preserved
- Photos scaled to fit perfectly within rows
- Uses Flickr's `justified-layout` library

### Configuration
- `justifiedRowHeight`: Target height for rows (default: 320px)

### Algorithm

Uses external library `justified-layout` v4.1.0:

```typescript
import justifiedLayout from 'justified-layout';

function renderJustifiedLayout(
  photos: Photo[],
  containerWidth: number,
  targetRowHeight: number
) {
  // Extract aspect ratios
  const aspectRatios = photos.map(photo => getAspectRatio(photo));

  // Calculate layout geometry
  const geometry = justifiedLayout(aspectRatios, {
    containerWidth: containerWidth,
    containerPadding: 0,
    targetRowHeight: targetRowHeight,
    boxSpacing: 0, // Handle spacing separately if needed
  });

  // Position photos according to geometry
  photos.forEach((photo, index) => {
    const box = geometry.boxes[index];

    positionPhoto(photo, {
      width: box.width,
      height: box.height,
      left: box.left,
      top: box.top
    });
  });

  // Set container height
  container.style.height = `${geometry.containerHeight}px`;
}
```

### How justified-layout Works

The library:
1. Groups photos into rows based on aspect ratios
2. Scales photos in each row to exact row height
3. Adjusts last row to fill remaining space
4. Returns box geometry for each photo

**Note**: This is the same library Lychee uses, ensuring perfect consistency.

---

## 3. Masonry Layout (Pinterest-style)

### Description
- Photos arranged in columns with variable heights
- Aspect ratios preserved
- Items placed in shortest available column
- Organic, flowing appearance

### Configuration
- `masonryColumnWidth`: Target column width (default: 300px)
- `layoutGap`: Gap between items (default: 12px)

### Algorithm

#### Step 1: Calculate Columns (Same as Square)

```typescript
const { columns, finalWidth } = calculateColumns(
  containerWidth,
  masonryColumnWidth,
  gap
);
```

#### Step 2: Initialize Columns

```typescript
const columnData: ColumnData[] = Array.from(
  { length: columns },
  (_, i) => ({
    left: i * (finalWidth + gap),
    height: 0
  })
);
```

#### Step 3: Position Photos (Shortest Column First)

```typescript
photos.forEach((photo, index) => {
  // Find shortest column (Pinterest algorithm)
  const shortestColumnIndex = findShortestColumn(columnData);
  const column = columnData[shortestColumnIndex];

  // Calculate height maintaining aspect ratio
  const aspectRatio = getAspectRatio(photo);
  const height = Math.floor(finalWidth / aspectRatio);

  // Position photo
  positionPhoto(photo, {
    width: finalWidth,
    height: height,
    left: column.left,
    top: column.height
  });

  // Update column height
  column.height += height + gap;
});

function findShortestColumn(columns: ColumnData[]): number {
  let shortestIndex = 0;
  let shortestHeight = columns[0].height;

  for (let i = 1; i < columns.length; i++) {
    if (columns[i].height < shortestHeight) {
      shortestHeight = columns[i].height;
      shortestIndex = i;
    }
  }

  return shortestIndex;
}
```

#### Step 4: Set Container Height

```typescript
const maxHeight = Math.max(...columnData.map(c => c.height));
container.style.height = `${maxHeight}px`;
```

### Example

**Given**:
- 3 columns
- Column width: 300px
- Photos with aspect ratios: [1.5, 0.75, 1.33, 2.0, 1.0]

**Placement**:
```
Photo 0 (ratio 1.5): height = 300/1.5 = 200px → Column 0 (shortest: 0)
Photo 1 (ratio 0.75): height = 300/0.75 = 400px → Column 1 (shortest: 0)
Photo 2 (ratio 1.33): height = 300/1.33 = 225px → Column 2 (shortest: 0)
Photo 3 (ratio 2.0): height = 300/2.0 = 150px → Column 0 (shortest: 200)
Photo 4 (ratio 1.0): height = 300/1.0 = 300px → Column 2 (shortest: 225)

Final column heights: [350, 400, 525]
```

---

## 4. Grid Layout

### Description
- Regular grid with fixed column widths
- Aspect ratios preserved within columns
- Row heights synchronized across columns
- Balanced between uniformity and aspect ratios

### Configuration
- `gridColumnWidth`: Target column width (default: 250px)
- `layoutGap`: Gap between items (default: 12px)

### Algorithm

#### Step 1-2: Calculate Columns and Initialize (Same as Masonry)

```typescript
const { columns, finalWidth } = calculateColumns(
  containerWidth,
  gridColumnWidth,
  gap
);

const columnData: ColumnData[] = Array.from(
  { length: columns },
  (_, i) => ({
    left: i * (finalWidth + gap),
    height: 0
  })
);
```

#### Step 3: Position Photos (Round-Robin with Row Sync)

```typescript
photos.forEach((photo, index) => {
  const columnIndex = index % columns; // Round-robin
  const column = columnData[columnIndex];

  // Synchronize row heights at start of each new row
  if (index % columns === 0 && index > 0) {
    const maxHeight = Math.max(...columnData.map(c => c.height));
    columnData.forEach(c => c.height = maxHeight);
  }

  // Calculate height maintaining aspect ratio
  const aspectRatio = getAspectRatio(photo);
  const height = Math.floor(finalWidth / aspectRatio);

  // Position photo
  positionPhoto(photo, {
    width: finalWidth,
    height: height,
    left: column.left,
    top: column.height
  });

  // Update column height
  column.height += height + gap;
});
```

#### Step 4: Set Container Height (Same as Others)

```typescript
const maxHeight = Math.max(...columnData.map(c => c.height));
container.style.height = `${maxHeight}px`;
```

### Key Difference from Masonry

**Masonry**: Places each photo in shortest column (organic flow)
**Grid**: Places photos round-robin (column 0, 1, 2, 0, 1, 2...) and syncs row heights

This creates a more structured, grid-like appearance while still preserving aspect ratios.

---

## 5. Filmstrip Layout

### Description
- Single large photo display (70-80% of container height)
- Horizontal thumbnail strip below (20-30% of container height)
- Active thumbnail highlighted
- Navigation controls

### Configuration
- `filmstripThumbnailHeight`: Height of thumbnail strip (default: 100px)

### Layout Structure

```
┌─────────────────────────────────────┐
│                                     │
│                                     │
│         Main Photo Viewer           │  70-80%
│         (Large Photo)               │  height
│                                     │
│                                     │
├─────────────────────────────────────┤
│ [◀] [thumb][thumb][thumb][thumb][▶] │  20-30%
└─────────────────────────────────────┘  height
```

### Algorithm

#### Step 1: Create Layout Structure

```typescript
function renderFilmstrip(
  container: HTMLElement,
  photos: Photo[],
  thumbnailHeight: number
) {
  // Calculate heights
  const containerHeight = container.clientHeight;
  const mainHeight = containerHeight - thumbnailHeight - gap;

  // Create main viewer
  const mainViewer = createMainViewer(mainHeight);
  container.appendChild(mainViewer);

  // Create thumbnail strip
  const thumbnailStrip = createThumbnailStrip(thumbnailHeight, photos);
  container.appendChild(thumbnailStrip);

  // Show first photo
  showPhoto(0);
}
```

#### Step 2: Main Viewer

```typescript
function createMainViewer(height: number): HTMLElement {
  const viewer = document.createElement('div');
  viewer.style.height = `${height}px`;
  viewer.style.position = 'relative';

  // Previous button (left)
  const prevBtn = createButton('‹', () => showPrevious());
  viewer.appendChild(prevBtn);

  // Photo container (center)
  const photoContainer = document.createElement('div');
  photoContainer.className = 'filmstrip-photo';
  viewer.appendChild(photoContainer);

  // Next button (right)
  const nextBtn = createButton('›', () => showNext());
  viewer.appendChild(nextBtn);

  return viewer;
}
```

#### Step 3: Thumbnail Strip

```typescript
function createThumbnailStrip(
  height: number,
  photos: Photo[]
): HTMLElement {
  const strip = document.createElement('div');
  strip.style.height = `${height}px`;
  strip.style.overflowX = 'auto';
  strip.style.display = 'flex';
  strip.style.gap = '8px';

  // Scroll left button
  const scrollLeftBtn = createButton('‹', () => scrollThumbnails(-200));
  strip.appendChild(scrollLeftBtn);

  // Thumbnails container
  const thumbsContainer = document.createElement('div');
  thumbsContainer.style.display = 'flex';
  thumbsContainer.style.gap = '8px';

  photos.forEach((photo, index) => {
    const thumb = createThumbnail(photo, index, height - 16);
    thumbsContainer.appendChild(thumb);
  });

  strip.appendChild(thumbsContainer);

  // Scroll right button
  const scrollRightBtn = createButton('›', () => scrollThumbnails(200));
  strip.appendChild(scrollRightBtn);

  return strip;
}

function createThumbnail(
  photo: Photo,
  index: number,
  height: number
): HTMLElement {
  const thumb = document.createElement('div');
  thumb.style.height = `${height}px`;
  thumb.style.cursor = 'pointer';
  thumb.dataset.index = String(index);

  const img = document.createElement('img');
  img.src = photo.size_variants.thumb?.url || '';
  img.style.height = '100%';
  img.style.width = 'auto';

  thumb.appendChild(img);
  thumb.addEventListener('click', () => showPhoto(index));

  return thumb;
}
```

#### Step 4: Photo Display Logic

```typescript
let currentPhotoIndex = 0;

function showPhoto(index: number): void {
  currentPhotoIndex = index;
  const photo = photos[index];

  // Update main viewer
  const photoContainer = document.querySelector('.filmstrip-photo');
  photoContainer.innerHTML = '';

  const img = document.createElement('img');
  img.src = photo.size_variants.medium?.url || photo.size_variants.small?.url;
  img.style.maxWidth = '100%';
  img.style.maxHeight = '100%';
  img.style.objectFit = 'contain';

  photoContainer.appendChild(img);

  // Highlight active thumbnail
  updateThumbnailHighlight(index);

  // Scroll thumbnail strip to show active thumbnail
  scrollToActiveThumbnail(index);
}

function updateThumbnailHighlight(index: number): void {
  const thumbs = document.querySelectorAll('[data-index]');
  thumbs.forEach((thumb, i) => {
    if (i === index) {
      thumb.classList.add('active');
    } else {
      thumb.classList.remove('active');
    }
  });
}

function scrollToActiveThumbnail(index: number): void {
  const thumb = document.querySelector(`[data-index="${index}"]`);
  if (thumb) {
    thumb.scrollIntoView({
      behavior: 'smooth',
      block: 'nearest',
      inline: 'center'
    });
  }
}
```

#### Step 5: Navigation

```typescript
function showNext(): void {
  const nextIndex = (currentPhotoIndex + 1) % photos.length;
  showPhoto(nextIndex);
}

function showPrevious(): void {
  const prevIndex = (currentPhotoIndex - 1 + photos.length) % photos.length;
  showPhoto(prevIndex);
}

// Keyboard support
document.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowRight') showNext();
  if (e.key === 'ArrowLeft') showPrevious();
});
```

---

## Performance Optimizations

### 1. Debounced Resize

```typescript
function createDebouncedResize(
  callback: () => void,
  delay: number = 150
): () => void {
  let timeoutId: number | undefined;

  return () => {
    if (timeoutId !== undefined) {
      clearTimeout(timeoutId);
    }
    timeoutId = window.setTimeout(callback, delay);
  };
}

// Usage
const debouncedReflow = createDebouncedResize(() => {
  layoutManager.reflow();
}, 150);

window.addEventListener('resize', debouncedReflow);
```

### 2. Lazy Image Loading

```typescript
function setupLazyLoading(images: HTMLImageElement[]): void {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target as HTMLImageElement;
          loadImage(img);
          observer.unobserve(img);
        }
      });
    },
    {
      rootMargin: '50px' // Start loading 50px before visible
    }
  );

  images.forEach((img) => observer.observe(img));
}

function loadImage(img: HTMLImageElement): void {
  const src = img.dataset.src;
  const srcset = img.dataset.srcset;

  if (src) img.src = src;
  if (srcset) img.srcset = srcset;
}
```

### 3. Progressive Image Loading

```typescript
function createPhotoElement(photo: Photo): HTMLElement {
  const container = document.createElement('div');

  // 1. Show placeholder immediately (blurred, tiny file)
  const placeholder = document.createElement('img');
  placeholder.src = photo.size_variants.placeholder?.url || '';
  placeholder.className = 'blur-md';
  container.appendChild(placeholder);

  // 2. Load actual thumbnail
  const img = document.createElement('img');
  img.dataset.src = photo.size_variants.small?.url || '';
  img.dataset.srcset = photo.size_variants.small2x
    ? `${photo.size_variants.small?.url} 1x, ${photo.size_variants.small2x.url} 2x`
    : '';
  img.loading = 'lazy';

  img.onload = () => {
    placeholder.remove(); // Remove placeholder once loaded
  };

  container.appendChild(img);

  return container;
}
```

---

## Testing Layout Algorithms

### Unit Test Example

```typescript
import { describe, it, expect } from 'vitest';
import { calculateColumns } from './responsive';

describe('Column Calculation', () => {
  it('calculates correct columns for standard width', () => {
    const { columns, finalWidth } = calculateColumns(1000, 200, 12);

    // Should fit 4 columns with gaps
    expect(columns).toBe(4);

    // Total width should not exceed container
    const totalWidth = columns * finalWidth + (columns - 1) * 12;
    expect(totalWidth).toBeLessThanOrEqual(1000);

    // Should distribute remaining space
    expect(finalWidth).toBeGreaterThan(200);
  });

  it('handles narrow containers', () => {
    const { columns, finalWidth } = calculateColumns(300, 200, 12);

    // Should fit 1 column
    expect(columns).toBe(1);

    // Width should use full container
    expect(finalWidth).toBe(300);
  });

  it('handles containers exactly fitting columns', () => {
    // 4 columns: 4*200 + 3*12 = 836px
    const { columns, finalWidth } = calculateColumns(836, 200, 12);

    expect(columns).toBe(4);
    expect(finalWidth).toBe(200);
  });
});
```

### Visual Testing

Create test pages with known photo sets:

```html
<!DOCTYPE html>
<html>
<head>
  <title>Layout Test: Square</title>
  <link rel="stylesheet" href="/embed/lychee-embed.css">
</head>
<body>
  <h1>Square Layout Test</h1>
  <p>Container: 1000px wide</p>

  <div id="test-container" style="width: 1000px; border: 1px solid red;">
    <div id="lychee-embed"></div>
  </div>

  <script src="/embed/lychee-embed.umd.js"></script>
  <script>
    new LycheeEmbed({
      containerId: 'lychee-embed',
      albumId: 'test-album-123',
      baseUrl: 'http://localhost:8000',
      layout: 'square',
      squareSize: 200,
      layoutGap: 12
    });
  </script>
</body>
</html>
```

---

## Summary

| Layout | Positioning | Aspect Ratios | Column Selection | Row Sync |
|--------|-------------|---------------|------------------|----------|
| Square | Round-robin | No (cropped) | Sequential | Yes |
| Justified | Library | Yes (preserved) | Row-based | Yes (by design) |
| Masonry | Shortest column | Yes (preserved) | Dynamic | No |
| Grid | Round-robin | Yes (preserved) | Sequential | Yes |
| Filmstrip | N/A (single photo) | Yes (preserved) | N/A | N/A |

All layouts except Filmstrip use the same column calculation formula for responsive width distribution.

---

**Document Status**: ✅ Complete
**Last Updated**: 2025-10-28
**Version**: 1.0
