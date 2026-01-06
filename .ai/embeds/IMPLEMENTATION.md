# Embeddable Photo Album Widget - Implementation Plan

## Document Information
- **Version**: 1.0
- **Date**: 2025-10-28
- **Status**: Ready for Implementation

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Backend Implementation](#backend-implementation)
3. [Frontend Widget Implementation](#frontend-widget-implementation)
4. [Layout Algorithms](#layout-algorithms)
5. [Embed Code Generator](#embed-code-generator)
6. [Build and Distribution](#build-and-distribution)
7. [Testing Strategy](#testing-strategy)
8. [Implementation Timeline](#implementation-timeline)

---

## Architecture Overview

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                      External Website                       │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  <div id="lychee-embed">                               │ │
│  │    ┌──────────────────────────────────────────────┐    │ │
│  │    │      LycheeEmbed Widget (JavaScript)         │    │ │
│  │    │  ┌────────────────────────────────────────┐  │    │ │
│  │    │  │  Layout Manager                        │  │    │ │
│  │    │  │  (Square/Justified/Masonry/Grid/Film)  │  │    │ │
│  │    │  └────────────────────────────────────────┘  │    │ │
│  │    │  ┌────────────────────────────────────────┐  │    │ │
│  │    │  │  Lightbox Component                    │  │    │ │
│  │    │  └────────────────────────────────────────┘  │    │ │
│  │    └──────────────────────────────────────────────┘    │ │
│  │  </div>                                                │ │
│  └────────────────────────────────────────────────────────┘ │
│                          │ │                                │
│                          │ │ API Request (CORS)             │
│                          ▼ ▼                                │
└─────────────────────────────────────────────────────────────┘
                           │ │
                           │ │
┌─────────────────────────────────────────────────────────────┐
│                   Lychee Installation                       │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  /api/v2/Embed/{albumId}  (EmbedController)            │ │
│  │      │                                                 │ │
│  │      ├─→ Validate Album Access (AccessPermission)      │ │
│  │      ├─→ Fetch Album + Photos                          │ │
│  │      ├─→ Generate Size Variant URLs (Signed)           │ │
│  │      └─→ Return JSON Response                          │ │
│  └────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────┐ │
│  │  Embed Code Generator (Vue Component)                  │ │
│  │      │                                                 │ │
│  │      ├─→ Configuration Form                            │ │
│  │      ├─→ Live Preview                                  │ │
│  │      └─→ Generate HTML/JS Snippet                      │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack

**Backend**:
- Laravel 12.x (existing)
- PHP 8.4+
- Existing Lychee models and services

**Widget**:
- TypeScript 5.x
- Vanilla JavaScript (no frameworks)
- justified-layout v4.1.0 (for justified layout)
- Vite 7.x (build tool)

**Styling**:
- CSS3 with custom properties (variables)
- No preprocessor (vanilla CSS)
- Namespaced classes

---

## Backend Implementation

### Phase 1.1: Create Embed API Endpoint

**File**: `app/Http/Controllers/Gallery/EmbedController.php` (NEW)

```php
<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Extensions\BaseAlbum;
use App\Http\Resources\Embed\EmbedAlbumResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EmbedController extends Controller
{
    /**
     * Get album data for embedding on external sites.
     *
     * @param string $albumId
     * @return JsonResponse
     * @throws AccessDeniedHttpException
     */
    public function getAlbum(string $albumId): JsonResponse
    {
        $album = $this->findAlbum($albumId);

        // Verify album is publicly accessible
        if (!$this->isPubliclyAccessible($album)) {
            throw new AccessDeniedHttpException(
                'Album must be publicly accessible for embedding'
            );
        }

        return response()->json(
            new EmbedAlbumResource($album)
        );
    }

    /**
     * Find album by ID.
     */
    private function findAlbum(string $albumId): BaseAlbum
    {
        $album = Album::query()
            ->with(['photos', 'photos.size_variants'])
            ->findOrFail($albumId);

        return $album;
    }

    /**
     * Check if album is publicly accessible.
     */
    private function isPubliclyAccessible(BaseAlbum $album): bool
    {
        // Check if album has public access permission
        $policy = $album->getProtectionPolicy();

        // Must be public and not require password
        return $policy->is_public
            && !$policy->is_password_required
            && !$policy->is_link_required; // Optional: may allow link-required
    }
}
```

**File**: `app/Http/Resources/Embed/EmbedAlbumResource.php` (NEW)

```php
<?php

namespace App\Http\Resources\Embed;

use Illuminate\Http\Resources\Json\JsonResource;

class EmbedAlbumResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'album' => [
                'id' => $this->id,
                'title' => $this->title,
                'description' => $this->description,
                'photo_count' => $this->photos->count(),
                'copyright' => $this->copyright,
                'license' => $this->license,
            ],
            'photos' => EmbedPhotoResource::collection($this->photos),
        ];
    }
}
```

**File**: `app/Http/Resources/Embed/EmbedPhotoResource.php` (NEW)

```php
<?php

namespace App\Http\Resources\Embed;

use Illuminate\Http\Resources\Json\JsonResource;

class EmbedPhotoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'size_variants' => [
                'placeholder' => $this->getSizeVariantData('placeholder'),
                'thumb' => $this->getSizeVariantData('thumb'),
                'thumb2x' => $this->getSizeVariantData('thumb2x'),
                'small' => $this->getSizeVariantData('small'),
                'small2x' => $this->getSizeVariantData('small2x'),
                'medium' => $this->getSizeVariantData('medium'),
                'medium2x' => $this->getSizeVariantData('medium2x'),
                'original' => [
                    'width' => $this->size_variants->original?->width ?? 0,
                    'height' => $this->size_variants->original?->height ?? 0,
                ],
            ],
            'exif' => [
                'make' => $this->make,
                'model' => $this->model,
                'lens' => $this->lens,
                'iso' => $this->iso,
                'aperture' => $this->aperture,
                'shutter' => $this->shutter,
                'focal' => $this->focal,
                'taken_at' => $this->taken_at?->toIso8601String(),
            ],
        ];
    }

    private function getSizeVariantData(string $variant): ?array
    {
        $sizeVariant = $this->size_variants->$variant;

        if (!$sizeVariant) {
            return null;
        }

        return [
            'url' => $sizeVariant->url,
            'width' => $sizeVariant->width,
            'height' => $sizeVariant->height,
        ];
    }
}
```

### Phase 1.2: Configure CORS

**File**: `config/cors.php`

```php
return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'api/v2/Embed/*', // Add embed endpoints
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // For public embeds

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400, // 24 hours

    'supports_credentials' => false, // No credentials for embeds
];
```

**OR create dedicated middleware** (if more control needed):

**File**: `app/Http/Middleware/EmbedCors.php` (NEW)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EmbedCors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
```

### Phase 1.3: Extend Photo URL Expiration

**File**: `app/Models/SizeVariant.php` (MODIFY)

Add method to generate embed URLs with longer expiration:

```php
public function getEmbedUrl(): string
{
    if (!$this->shouldUseSignedUrl()) {
        return $this->getPublicUrl();
    }

    // Generate signed URL with longer expiration for embeds
    return URL::temporarySignedRoute(
        'image.show',
        now()->addHours(24), // 24 hours instead of default
        ['path' => $this->short_path]
    );
}
```

**Consideration**: May need to detect embed context or add separate embed-specific resource classes.

### Phase 1.4: Add Routes

**File**: `routes/api_v2.php` (MODIFY)

**IMPORTANT: Route Order Matters**

The stream route MUST be defined BEFORE the album route. Laravel matches routes in order, and `/Embed/{albumId}` would match `/Embed/stream` if it comes first, treating "stream" as an album ID.

```php
use App\Http\Controllers\Gallery\EmbedController;

// Existing routes...

// Embed endpoints
// CRITICAL: More specific routes must come before generic routes with parameters
Route::match(['GET', 'OPTIONS'], '/Embed/stream', [EmbedController::class, 'getPublicStream'])
    ->withoutMiddleware('api')
    ->middleware([
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache_control:900', // 15 minutes cache
        'throttle:30,1', // 30 requests per minute per IP
    ])
    ->name('embed.stream');

Route::match(['GET', 'OPTIONS'], '/Embed/{albumId}', [EmbedController::class, 'getAlbum'])
    ->withoutMiddleware('api')
    ->middleware([
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache_control:300', // 5 minutes cache
        'throttle:120,1', // 120 requests per minute per IP
    ])
    ->name('embed.album');
```

After modifying routes, clear the route cache:
```bash
php artisan route:clear
```

---

## Frontend Widget Implementation

### Project Structure

```
resources/js/embed/
├── index.ts                    # Main entry point
├── LycheeEmbed.ts             # Main widget class
├── config.ts                  # Configuration and validation
├── types.ts                   # TypeScript interfaces
├── api/
│   └── client.ts              # API client for fetching data
├── layouts/
│   ├── LayoutManager.ts       # Layout factory
│   ├── BaseLayout.ts          # Abstract base class
│   ├── SquareLayout.ts        # Square layout implementation
│   ├── JustifiedLayout.ts     # Justified layout implementation
│   ├── MasonryLayout.ts       # Masonry layout implementation
│   ├── GridLayout.ts          # Grid layout implementation
│   └── FilmstripLayout.ts     # Filmstrip layout implementation
├── components/
│   ├── Lightbox.ts            # Lightbox viewer
│   ├── AlbumHeader.ts         # Album metadata display
│   └── ImageLoader.ts         # Progressive image loading
├── utils/
│   ├── responsive.ts          # Column calculation utilities
│   ├── touchHandler.ts        # Touch/swipe support
│   ├── errorHandler.ts        # Error handling
│   └── dom.ts                 # DOM manipulation helpers
└── styles/
    ├── embed.css              # Main styles
    ├── lightbox.css           # Lightbox styles
    └── themes.css             # Theme variants
```

### Phase 2.1: Build Configuration

**File**: `vite.embed.config.ts` (NEW)

```typescript
import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    lib: {
      entry: resolve(__dirname, 'resources/js/embed/index.ts'),
      name: 'LycheeEmbed',
      formats: ['umd', 'es'],
      fileName: (format) => `lychee-embed.${format}.js`,
    },
    outDir: 'public/embed',
    minify: 'terser',
    sourcemap: true,
    rollupOptions: {
      external: [],
      output: {
        globals: {},
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'style.css') {
            return 'lychee-embed.css';
          }
          return assetInfo.name || '';
        },
      },
    },
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
      },
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js/embed'),
    },
  },
});
```

**File**: `package.json` (MODIFY)

Add scripts:

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "build:embed": "vite build --config vite.embed.config.ts",
    "watch:embed": "vite build --config vite.embed.config.ts --watch",
    "dev:embed": "vite --config vite.embed.config.ts"
  },
  "devDependencies": {
    "@types/justified-layout": "^4.1.1",
    "justified-layout": "^4.1.0"
  }
}
```

### Phase 2.2: Core Widget Types

**File**: `resources/js/embed/types.ts` (NEW)

```typescript
export type LayoutType = 'square' | 'justified' | 'masonry' | 'grid' | 'filmstrip';
export type ThemeType = 'light' | 'dark' | 'auto';

export interface LycheeEmbedConfig {
  // Required
  containerId: string;
  albumId: string;
  baseUrl: string;

  // Layout
  layout?: LayoutType;
  squareSize?: number;
  masonryColumnWidth?: number;
  gridColumnWidth?: number;
  justifiedRowHeight?: number;
  filmstripThumbnailHeight?: number;
  layoutGap?: number;

  // Display
  theme?: ThemeType;
  showAlbumInfo?: boolean;
  showCaptions?: boolean;
  showExif?: boolean;

  // Sort
  sortOrder?: 'asc' | 'desc'; // 'desc' = newest first (default), 'asc' = oldest first

  // Callbacks
  onLoad?: () => void;
  onError?: (error: Error) => void;
}

export interface Album {
  id: string;
  title: string;
  description: string;
  photo_count: number;
  copyright?: string;
  license?: string;
}

export interface SizeVariant {
  url: string;
  width: number;
  height: number;
}

export interface SizeVariants {
  placeholder?: SizeVariant;
  thumb?: SizeVariant;
  thumb2x?: SizeVariant;
  small?: SizeVariant;
  small2x?: SizeVariant;
  medium?: SizeVariant;
  medium2x?: SizeVariant;
  original?: { width: number; height: number };
}

export interface Exif {
  make?: string;
  model?: string;
  lens?: string;
  iso?: string;
  aperture?: string;
  shutter?: string;
  focal?: string;
  taken_at?: string;
}

export interface Photo {
  id: string;
  title?: string;
  description?: string;
  size_variants: SizeVariants;
  exif?: Exif;
}

export interface AlbumData {
  album: Album;
  photos: Photo[];
}
```

### Phase 2.3: Main Widget Class

**File**: `resources/js/embed/LycheeEmbed.ts` (NEW)

```typescript
import { LycheeEmbedConfig, AlbumData } from './types';
import { fetchAlbumData } from './api/client';
import { LayoutManager } from './layouts/LayoutManager';
import { Lightbox } from './components/Lightbox';
import { AlbumHeader } from './components/AlbumHeader';
import { validateConfig, getDefaultConfig } from './config';
import { onResize } from './utils/responsive';

export class LycheeEmbed {
  private config: Required<LycheeEmbedConfig>;
  private container: HTMLElement;
  private albumData?: AlbumData;
  private layoutManager?: LayoutManager;
  private lightbox?: Lightbox;

  constructor(config: LycheeEmbedConfig) {
    this.config = { ...getDefaultConfig(), ...config };
    validateConfig(this.config);

    const container = document.getElementById(this.config.containerId);
    if (!container) {
      throw new Error(`Container element '${this.config.containerId}' not found`);
    }
    this.container = container;

    this.init();
  }

  private async init(): Promise<void> {
    try {
      // Add CSS class for styling
      this.container.classList.add('lychee-embed-container');
      this.container.dataset.theme = this.config.theme;

      // Show loading state
      this.showLoading();

      // Fetch album data
      this.albumData = await fetchAlbumData(
        this.config.baseUrl,
        this.config.albumId
      );

      // Render
      this.render();

      // Setup resize handling
      window.addEventListener('resize', onResize(() => this.handleResize()));

      // Call success callback
      if (this.config.onLoad) {
        this.config.onLoad();
      }
    } catch (error) {
      this.showError(error as Error);
      if (this.config.onError) {
        this.config.onError(error as Error);
      }
    }
  }

  private render(): void {
    if (!this.albumData) return;

    // Clear container
    this.container.innerHTML = '';

    // Render album header
    if (this.config.showAlbumInfo) {
      const header = new AlbumHeader(this.albumData.album);
      this.container.appendChild(header.render());
    }

    // Create photo container
    const photoContainer = document.createElement('div');
    photoContainer.className = 'lychee-embed-photos';
    this.container.appendChild(photoContainer);

    // Render layout
    this.layoutManager = new LayoutManager(
      photoContainer,
      this.albumData.photos,
      this.config
    );
    this.layoutManager.render();

    // Setup lightbox
    this.lightbox = new Lightbox(
      this.albumData.photos,
      this.config
    );

    // Attach click handlers
    this.attachPhotoClickHandlers(photoContainer);
  }

  private attachPhotoClickHandlers(container: HTMLElement): void {
    const photos = container.querySelectorAll('.lychee-embed-photo');
    photos.forEach((photo, index) => {
      photo.addEventListener('click', () => {
        if (this.lightbox) {
          this.lightbox.open(index);
        }
      });
    });
  }

  private handleResize(): void {
    if (this.layoutManager) {
      this.layoutManager.reflow();
    }
  }

  private showLoading(): void {
    this.container.innerHTML = `
      <div class="lychee-embed-loading">
        <div class="lychee-embed-spinner"></div>
        <p>Loading gallery...</p>
      </div>
    `;
  }

  private showError(error: Error): void {
    this.container.innerHTML = `
      <div class="lychee-embed-error">
        <p class="error-message">Failed to load gallery</p>
        <p class="error-details">${error.message}</p>
      </div>
    `;
  }

  // Public API
  public destroy(): void {
    window.removeEventListener('resize', this.handleResize);
    if (this.lightbox) {
      this.lightbox.destroy();
    }
    this.container.innerHTML = '';
  }
}
```

**File**: `resources/js/embed/index.ts` (NEW)

```typescript
import { LycheeEmbed } from './LycheeEmbed';
import './styles/embed.css';
import './styles/lightbox.css';
import './styles/themes.css';

// Export as global for UMD build
if (typeof window !== 'undefined') {
  (window as any).LycheeEmbed = LycheeEmbed;
}

// Export for ES modules
export { LycheeEmbed };
export * from './types';
```

---

## Layout Algorithms

### Responsive Column Calculation

**File**: `resources/js/embed/utils/responsive.ts` (NEW)

```typescript
export interface ColumnCalculation {
  columns: number;
  itemWidth: number;
}

/**
 * Calculate number of columns and item width based on container width.
 * Uses Lychee's algorithm for distributing remaining space.
 */
export function calculateColumns(
  containerWidth: number,
  targetWidth: number,
  gap: number
): ColumnCalculation {
  // Calculate how many items fit
  const columns = Math.floor((containerWidth + gap) / targetWidth);

  // Calculate remaining space
  const remainingSpace =
    containerWidth - columns * targetWidth - (columns - 1) * gap;

  // Distribute remaining space evenly
  const spread = Math.ceil(remainingSpace / columns);
  const itemWidth = targetWidth + spread;

  return { columns, itemWidth };
}

/**
 * Get container width accounting for padding and scrollbar.
 */
export function getContainerWidth(container: HTMLElement): number {
  return container.clientWidth;
}

/**
 * Create debounced function for resize handling.
 */
export function onResize(callback: () => void, delay = 150): () => void {
  let timeoutId: number | undefined;
  return () => {
    if (timeoutId !== undefined) {
      clearTimeout(timeoutId);
    }
    timeoutId = window.setTimeout(callback, delay);
  };
}

/**
 * Extract aspect ratios from photos.
 */
export function getAspectRatios(photos: Photo[]): number[] {
  return photos.map((photo) => {
    const width = photo.size_variants.original?.width ?? 1;
    const height = photo.size_variants.original?.height ?? 1;
    return height > 0 ? width / height : 1;
  });
}
```

### Square Layout

**File**: `resources/js/embed/layouts/SquareLayout.ts` (NEW)

```typescript
import { BaseLayout } from './BaseLayout';
import { Photo } from '../types';
import { calculateColumns } from '../utils/responsive';

export class SquareLayout extends BaseLayout {
  render(): void {
    const width = this.getContainerWidth();
    const { columns, itemWidth } = calculateColumns(
      width,
      this.config.squareSize!,
      this.config.layoutGap!
    );

    // Initialize column tracking
    const columnData = Array.from({ length: columns }, (_, i) => ({
      left: i * (itemWidth + this.config.layoutGap!),
      height: 0,
    }));

    this.photos.forEach((photo, index) => {
      const columnIndex = index % columns;
      const column = columnData[columnIndex];

      // Synchronize row heights at start of each row
      if (index % columns === 0 && index > 0) {
        const maxHeight = Math.max(...columnData.map((c) => c.height));
        columnData.forEach((c) => (c.height = maxHeight));
      }

      const element = this.createPhotoElement(
        photo,
        itemWidth,
        itemWidth, // Square!
        column.left,
        column.height
      );

      this.container.appendChild(element);

      // Update column height
      column.height += itemWidth + this.config.layoutGap!;
    });

    // Set container height
    const maxHeight = Math.max(...columnData.map((c) => c.height));
    this.container.style.height = `${maxHeight}px`;
  }
}
```

### Justified Layout

**File**: `resources/js/embed/layouts/JustifiedLayout.ts` (NEW)

```typescript
import { BaseLayout } from './BaseLayout';
import { Photo } from '../types';
import { getAspectRatios } from '../utils/responsive';
import justifiedLayout from 'justified-layout';

export class JustifiedLayout extends BaseLayout {
  render(): void {
    const width = this.getContainerWidth();
    const aspectRatios = getAspectRatios(this.photos);

    // Use justified-layout library (same as Lychee)
    const geometry = justifiedLayout(aspectRatios, {
      containerWidth: width,
      containerPadding: 0,
      targetRowHeight: this.config.justifiedRowHeight!,
      boxSpacing: 0, // We'll handle spacing ourselves if needed
    });

    this.photos.forEach((photo, index) => {
      const box = geometry.boxes[index];

      const element = this.createPhotoElement(
        photo,
        box.width,
        box.height,
        box.left,
        box.top
      );

      this.container.appendChild(element);
    });

    // Set container height
    this.container.style.height = `${geometry.containerHeight}px`;
  }
}
```

### Masonry Layout

**File**: `resources/js/embed/layouts/MasonryLayout.ts` (NEW)

```typescript
import { BaseLayout } from './BaseLayout';
import { Photo } from '../types';
import { calculateColumns, getAspectRatios } from '../utils/responsive';

export class MasonryLayout extends BaseLayout {
  render(): void {
    const width = this.getContainerWidth();
    const { columns, itemWidth } = calculateColumns(
      width,
      this.config.masonryColumnWidth!,
      this.config.layoutGap!
    );

    const aspectRatios = getAspectRatios(this.photos);

    // Initialize columns
    const columnData = Array.from({ length: columns }, (_, i) => ({
      left: i * (itemWidth + this.config.layoutGap!),
      height: 0,
    }));

    this.photos.forEach((photo, index) => {
      // Find shortest column (Pinterest-style)
      const shortestColumnIndex = this.findShortestColumn(columnData);
      const column = columnData[shortestColumnIndex];

      // Calculate height maintaining aspect ratio
      const height = Math.floor(itemWidth / aspectRatios[index]);

      const element = this.createPhotoElement(
        photo,
        itemWidth,
        height,
        column.left,
        column.height
      );

      this.container.appendChild(element);

      // Update column height
      column.height += height + this.config.layoutGap!;
    });

    // Set container height
    const maxHeight = Math.max(...columnData.map((c) => c.height));
    this.container.style.height = `${maxHeight}px`;
  }

  private findShortestColumn(columns: Array<{ height: number }>): number {
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
}
```

### Grid Layout

**File**: `resources/js/embed/layouts/GridLayout.ts` (NEW)

```typescript
import { BaseLayout } from './BaseLayout';
import { Photo } from '../types';
import { calculateColumns, getAspectRatios } from '../utils/responsive';

export class GridLayout extends BaseLayout {
  render(): void {
    const width = this.getContainerWidth();
    const { columns, itemWidth } = calculateColumns(
      width,
      this.config.gridColumnWidth!,
      this.config.layoutGap!
    );

    const aspectRatios = getAspectRatios(this.photos);

    // Initialize columns
    const columnData = Array.from({ length: columns }, (_, i) => ({
      left: i * (itemWidth + this.config.layoutGap!),
      height: 0,
    }));

    this.photos.forEach((photo, index) => {
      const columnIndex = index % columns;
      const column = columnData[columnIndex];

      // Synchronize row heights at start of each row
      if (index % columns === 0 && index > 0) {
        const maxHeight = Math.max(...columnData.map((c) => c.height));
        columnData.forEach((c) => (c.height = maxHeight));
      }

      // Calculate height maintaining aspect ratio
      const height = Math.floor(itemWidth / aspectRatios[index]);

      const element = this.createPhotoElement(
        photo,
        itemWidth,
        height,
        column.left,
        column.height
      );

      this.container.appendChild(element);

      // Update column height
      column.height += height + this.config.layoutGap!;
    });

    // Set container height
    const maxHeight = Math.max(...columnData.map((c) => c.height));
    this.container.style.height = `${maxHeight}px`;
  }
}
```

### Filmstrip Layout

**File**: `resources/js/embed/layouts/FilmstripLayout.ts` (NEW)

```typescript
import { BaseLayout } from './BaseLayout';
import { Photo } from '../types';

export class FilmstripLayout extends BaseLayout {
  private currentPhotoIndex = 0;
  private mainViewer!: HTMLElement;
  private thumbnailStrip!: HTMLElement;

  render(): void {
    // Create layout structure
    this.container.innerHTML = '';
    this.container.classList.add('lychee-embed-filmstrip');

    // Main viewer (70-80% height)
    this.mainViewer = this.createMainViewer();
    this.container.appendChild(this.mainViewer);

    // Thumbnail strip (20-30% height)
    this.thumbnailStrip = this.createThumbnailStrip();
    this.container.appendChild(this.thumbnailStrip);

    // Show first photo
    this.showPhoto(0);
  }

  private createMainViewer(): HTMLElement {
    const viewer = document.createElement('div');
    viewer.className = 'lychee-embed-filmstrip-main';

    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'lychee-embed-filmstrip-nav prev';
    prevBtn.innerHTML = '‹';
    prevBtn.addEventListener('click', () => this.previous());
    viewer.appendChild(prevBtn);

    // Photo container
    const photoContainer = document.createElement('div');
    photoContainer.className = 'lychee-embed-filmstrip-photo';
    viewer.appendChild(photoContainer);

    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'lychee-embed-filmstrip-nav next';
    nextBtn.innerHTML = '›';
    nextBtn.addEventListener('click', () => this.next());
    viewer.appendChild(nextBtn);

    return viewer;
  }

  private createThumbnailStrip(): HTMLElement {
    const strip = document.createElement('div');
    strip.className = 'lychee-embed-filmstrip-thumbnails';

    // Scroll left button
    const scrollLeftBtn = document.createElement('button');
    scrollLeftBtn.className = 'lychee-embed-filmstrip-scroll-btn left';
    scrollLeftBtn.innerHTML = '‹';
    scrollLeftBtn.addEventListener('click', () => this.scrollThumbnails(-1));
    strip.appendChild(scrollLeftBtn);

    // Thumbnails container
    const thumbsContainer = document.createElement('div');
    thumbsContainer.className = 'lychee-embed-filmstrip-thumbs-container';

    this.photos.forEach((photo, index) => {
      const thumb = this.createThumbnail(photo, index);
      thumbsContainer.appendChild(thumb);
    });

    strip.appendChild(thumbsContainer);

    // Scroll right button
    const scrollRightBtn = document.createElement('button');
    scrollRightBtn.className = 'lychee-embed-filmstrip-scroll-btn right';
    scrollRightBtn.innerHTML = '›';
    scrollRightBtn.addEventListener('click', () => this.scrollThumbnails(1));
    strip.appendChild(scrollRightBtn);

    return strip;
  }

  private createThumbnail(photo: Photo, index: number): HTMLElement {
    const thumb = document.createElement('div');
    thumb.className = 'lychee-embed-filmstrip-thumb';
    thumb.dataset.index = String(index);

    const img = document.createElement('img');
    img.src = photo.size_variants.thumb?.url || '';
    if (photo.size_variants.thumb2x) {
      img.srcset = `${photo.size_variants.thumb?.url} 1x, ${photo.size_variants.thumb2x.url} 2x`;
    }
    img.alt = photo.title || '';
    img.loading = 'lazy';

    thumb.appendChild(img);
    thumb.addEventListener('click', () => this.showPhoto(index));

    return thumb;
  }

  private showPhoto(index: number): void {
    this.currentPhotoIndex = index;
    const photo = this.photos[index];

    // Update main viewer
    const photoContainer = this.mainViewer.querySelector(
      '.lychee-embed-filmstrip-photo'
    ) as HTMLElement;

    photoContainer.innerHTML = '';

    const img = document.createElement('img');
    img.src = photo.size_variants.medium?.url || photo.size_variants.small?.url || '';
    if (photo.size_variants.medium2x) {
      img.srcset = `${photo.size_variants.medium?.url} 1x, ${photo.size_variants.medium2x.url} 2x`;
    }
    img.alt = photo.title || '';

    photoContainer.appendChild(img);

    // Update thumbnail highlighting
    this.updateThumbnailHighlight(index);

    // Scroll thumbnail strip to show active thumbnail
    this.scrollToActiveThumbnail(index);
  }

  private updateThumbnailHighlight(index: number): void {
    const thumbs = this.thumbnailStrip.querySelectorAll('.lychee-embed-filmstrip-thumb');
    thumbs.forEach((thumb, i) => {
      if (i === index) {
        thumb.classList.add('active');
      } else {
        thumb.classList.remove('active');
      }
    });
  }

  private scrollToActiveThumbnail(index: number): void {
    const thumbsContainer = this.thumbnailStrip.querySelector(
      '.lychee-embed-filmstrip-thumbs-container'
    ) as HTMLElement;

    const thumb = thumbsContainer.children[index] as HTMLElement;
    if (thumb) {
      thumb.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'center',
      });
    }
  }

  private scrollThumbnails(direction: number): void {
    const thumbsContainer = this.thumbnailStrip.querySelector(
      '.lychee-embed-filmstrip-thumbs-container'
    ) as HTMLElement;

    const scrollAmount = 200 * direction;
    thumbsContainer.scrollBy({
      left: scrollAmount,
      behavior: 'smooth',
    });
  }

  private next(): void {
    const nextIndex = (this.currentPhotoIndex + 1) % this.photos.length;
    this.showPhoto(nextIndex);
  }

  private previous(): void {
    const prevIndex =
      (this.currentPhotoIndex - 1 + this.photos.length) % this.photos.length;
    this.showPhoto(prevIndex);
  }
}
```

---

## Embed Code Generator

### Backend: Generate Embed Snippet

**File**: `app/Http/Controllers/Gallery/AlbumController.php` (MODIFY)

Add method:

```php
/**
 * Generate embed code for an album.
 */
public function generateEmbedCode(string $albumId): JsonResponse
{
    $album = Album::findOrFail($albumId);

    // Verify album is public
    $policy = $album->getProtectionPolicy();
    if (!$policy->is_public || $policy->is_password_required) {
        return response()->json([
            'error' => 'Album must be public to generate embed code'
        ], 403);
    }

    $baseUrl = config('app.url');
    $embedCode = $this->buildEmbedSnippet($albumId, $baseUrl);

    return response()->json([
        'code' => $embedCode,
        'album_id' => $albumId,
    ]);
}

private function buildEmbedSnippet(
    string $albumId,
    string $baseUrl,
    array $options = []
): string {
    $containerId = "lychee-embed-{$albumId}";
    $layout = $options['layout'] ?? 'justified';
    $theme = $options['theme'] ?? 'auto';

    return <<<HTML
<!-- Lychee Photo Gallery Embed -->
<div id="{$containerId}"></div>
<link rel="stylesheet" href="{$baseUrl}/embed/lychee-embed.css">
<script src="{$baseUrl}/embed/lychee-embed.umd.js"></script>
<script>
new LycheeEmbed({
  containerId: '{$containerId}',
  albumId: '{$albumId}',
  baseUrl: '{$baseUrl}',
  layout: '{$layout}',
  theme: '{$theme}',
  showAlbumInfo: true,
  showCaptions: true,
  showExif: true
});
</script>
HTML;
}
```

Add route:

```php
// In routes/api_v2.php
Route::get('/Album/{albumId}/embed-code', [AlbumController::class, 'generateEmbedCode'])
    ->name('album.embed-code');
```

### Frontend: Embed Code Modal

**File**: `resources/js/components/modals/EmbedCodeModal.vue` (NEW)

```vue
<template>
  <Modal :title="$t('gallery.embed.title')" @close="emit('close')" size="xl">
    <div class="embed-generator">
      <!-- Configuration Panel -->
      <div class="config-panel">
        <h3>{{ $t('gallery.embed.configuration') }}</h3>

        <!-- Layout Selection -->
        <div class="config-section">
          <label>{{ $t('gallery.embed.layout') }}</label>
          <RadioGroup v-model="config.layout">
            <RadioOption value="square">
              <Icon name="grid" />
              {{ $t('gallery.layout.squares') }}
            </RadioOption>
            <RadioOption value="justified">
              <Icon name="align-justify" />
              {{ $t('gallery.layout.justified') }}
            </RadioOption>
            <RadioOption value="masonry">
              <Icon name="masonry" />
              {{ $t('gallery.layout.masonry') }}
            </RadioOption>
            <RadioOption value="grid">
              <Icon name="grid-2" />
              {{ $t('gallery.layout.grid') }}
            </RadioOption>
            <RadioOption value="filmstrip">
              <Icon name="film" />
              {{ $t('gallery.layout.filmstrip') }}
            </RadioOption>
          </RadioGroup>
        </div>

        <!-- Dynamic Size Controls -->
        <div v-if="config.layout === 'square'" class="config-section">
          <label>
            {{ $t('gallery.embed.square_size') }}
            <span class="value">{{ config.squareSize }}px</span>
          </label>
          <Slider v-model="config.squareSize" :min="100" :max="400" :step="10" />
        </div>

        <div v-if="config.layout === 'masonry'" class="config-section">
          <label>
            {{ $t('gallery.embed.column_width') }}
            <span class="value">{{ config.masonryColumnWidth }}px</span>
          </label>
          <Slider v-model="config.masonryColumnWidth" :min="200" :max="500" :step="10" />
        </div>

        <div v-if="config.layout === 'grid'" class="config-section">
          <label>
            {{ $t('gallery.embed.column_width') }}
            <span class="value">{{ config.gridColumnWidth }}px</span>
          </label>
          <Slider v-model="config.gridColumnWidth" :min="150" :max="400" :step="10" />
        </div>

        <div v-if="config.layout === 'justified'" class="config-section">
          <label>
            {{ $t('gallery.embed.row_height') }}
            <span class="value">{{ config.justifiedRowHeight }}px</span>
          </label>
          <Slider v-model="config.justifiedRowHeight" :min="200" :max="500" :step="10" />
        </div>

        <div v-if="config.layout === 'filmstrip'" class="config-section">
          <label>
            {{ $t('gallery.embed.thumbnail_height') }}
            <span class="value">{{ config.filmstripThumbnailHeight }}px</span>
          </label>
          <Slider v-model="config.filmstripThumbnailHeight" :min="60" :max="150" :step="10" />
        </div>

        <!-- Gap Size -->
        <div class="config-section">
          <label>
            {{ $t('gallery.embed.gap') }}
            <span class="value">{{ config.layoutGap }}px</span>
          </label>
          <Slider v-model="config.layoutGap" :min="0" :max="30" :step="2" />
        </div>

        <!-- Theme Selection -->
        <div class="config-section">
          <label>{{ $t('gallery.embed.theme') }}</label>
          <RadioGroup v-model="config.theme">
            <RadioOption value="light">{{ $t('theme.light') }}</RadioOption>
            <RadioOption value="dark">{{ $t('theme.dark') }}</RadioOption>
            <RadioOption value="auto">{{ $t('theme.auto') }}</RadioOption>
          </RadioGroup>
        </div>

        <!-- Display Options -->
        <div class="config-section">
          <label>{{ $t('gallery.embed.display_options') }}</label>
          <Checkbox v-model="config.showAlbumInfo">
            {{ $t('gallery.embed.show_album_info') }}
          </Checkbox>
          <Checkbox v-model="config.showCaptions">
            {{ $t('gallery.embed.show_captions') }}
          </Checkbox>
          <Checkbox v-model="config.showExif">
            {{ $t('gallery.embed.show_exif') }}
          </Checkbox>
        </div>
      </div>

      <!-- Preview Panel -->
      <div class="preview-panel">
        <h3>{{ $t('gallery.embed.preview') }}</h3>
        <div class="preview-container">
          <iframe
            :srcdoc="previewHtml"
            class="embed-preview"
            @load="onPreviewLoad"
          ></iframe>
        </div>
      </div>

      <!-- Code Panel -->
      <div class="code-panel">
        <h3>{{ $t('gallery.embed.embed_code') }}</h3>
        <div class="code-container">
          <pre><code ref="codeElement">{{ generatedCode }}</code></pre>
          <Button @click="copyToClipboard" icon="copy" variant="primary">
            {{ copied ? $t('gallery.embed.copied') : $t('gallery.embed.copy') }}
          </Button>
        </div>
        <div class="instructions">
          <p>{{ $t('gallery.embed.instructions') }}</p>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Modal from '@/components/modals/Modal.vue';
import RadioGroup from '@/components/forms/RadioGroup.vue';
import RadioOption from '@/components/forms/RadioOption.vue';
import Checkbox from '@/components/forms/Checkbox.vue';
import Slider from '@/components/forms/Slider.vue';
import Button from '@/components/Button.vue';
import Icon from '@/components/Icon.vue';

interface Props {
  albumId: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  close: [];
}>();

const config = ref({
  layout: 'justified',
  squareSize: 200,
  masonryColumnWidth: 300,
  gridColumnWidth: 250,
  justifiedRowHeight: 320,
  filmstripThumbnailHeight: 100,
  layoutGap: 12,
  theme: 'auto',
  showAlbumInfo: true,
  showCaptions: true,
  showExif: true,
});

const copied = ref(false);
const codeElement = ref<HTMLElement>();

const baseUrl = window.location.origin;

const generatedCode = computed(() => {
  const containerId = `lychee-embed-${props.albumId}`;
  const configJson = JSON.stringify(
    {
      containerId,
      albumId: props.albumId,
      baseUrl,
      layout: config.value.layout,
      ...(config.value.layout === 'square' && { squareSize: config.value.squareSize }),
      ...(config.value.layout === 'masonry' && { masonryColumnWidth: config.value.masonryColumnWidth }),
      ...(config.value.layout === 'grid' && { gridColumnWidth: config.value.gridColumnWidth }),
      ...(config.value.layout === 'justified' && { justifiedRowHeight: config.value.justifiedRowHeight }),
      ...(config.value.layout === 'filmstrip' && { filmstripThumbnailHeight: config.value.filmstripThumbnailHeight }),
      layoutGap: config.value.layoutGap,
      theme: config.value.theme,
      showAlbumInfo: config.value.showAlbumInfo,
      showCaptions: config.value.showCaptions,
      showExif: config.value.showExif,
    },
    null,
    2
  );

  return `<!-- Lychee Photo Gallery Embed -->
<div id="${containerId}"></div>
<link rel="stylesheet" href="${baseUrl}/embed/lychee-embed.css">
<script src="${baseUrl}/embed/lychee-embed.umd.js"><\/script>
<script>
new LycheeEmbed(${configJson});
<\/script>`;
});

const previewHtml = computed(() => {
  return `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { margin: 0; padding: 16px; background: #f3f4f6; }
  </style>
</head>
<body>
  ${generatedCode.value}
</body>
</html>
  `;
});

async function copyToClipboard() {
  try {
    await navigator.clipboard.writeText(generatedCode.value);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    console.error('Failed to copy:', err);
  }
}

function onPreviewLoad() {
  // Preview iframe loaded
}
</script>

<style scoped>
.embed-generator {
  display: grid;
  grid-template-columns: 300px 1fr;
  grid-template-rows: 1fr auto;
  gap: 24px;
  min-height: 600px;
}

.config-panel {
  grid-row: 1 / 3;
}

.preview-panel {
  grid-column: 2;
  grid-row: 1;
}

.code-panel {
  grid-column: 2;
  grid-row: 2;
}

.config-section {
  margin-bottom: 24px;
}

.config-section label {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-weight: 500;
}

.config-section .value {
  color: var(--color-primary);
}

.preview-container {
  border: 1px solid var(--color-border);
  border-radius: 8px;
  overflow: hidden;
  background: white;
}

.embed-preview {
  width: 100%;
  height: 400px;
  border: none;
}

.code-container {
  position: relative;
}

.code-container pre {
  background: #1e293b;
  color: #e2e8f0;
  padding: 16px;
  border-radius: 8px;
  overflow-x: auto;
  max-height: 300px;
}

.code-container button {
  position: absolute;
  top: 16px;
  right: 16px;
}

.instructions {
  margin-top: 16px;
  padding: 12px;
  background: var(--color-info-bg);
  border-radius: 8px;
  color: var(--color-info-text);
}
</style>
```

### Integration in Album View

**File**: `resources/js/views/gallery-panels/Album.vue` (MODIFY)

Add embed button to toolbar:

```vue
<template>
  <div class="album-view">
    <!-- Toolbar -->
    <div class="album-toolbar">
      <!-- Existing buttons... -->

      <Button
        v-if="albumStore.album?.policy.is_public"
        @click="showEmbedModal = true"
        icon="code"
        :tooltip="$t('gallery.embed.button')"
      >
        {{ $t('gallery.embed.button') }}
      </Button>
    </div>

    <!-- Album content... -->

    <!-- Modals -->
    <EmbedCodeModal
      v-if="showEmbedModal"
      :album-id="albumStore.album.id"
      @close="showEmbedModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import EmbedCodeModal from '@/components/modals/EmbedCodeModal.vue';
// ... other imports

const showEmbedModal = ref(false);
// ... rest of component
</script>
```

---

## Build and Distribution

### Build Scripts

**Commands**:
```bash
# Development
npm run dev:embed

# Watch mode
npm run watch:embed

# Production build
npm run build:embed
```

### Output Structure

```
public/embed/
├── lychee-embed.umd.js        # UMD build (~80KB gzipped)
├── lychee-embed.es.js         # ES module build
├── lychee-embed.css           # Styles (~15KB gzipped)
├── lychee-embed.umd.js.map    # Source map
└── examples/
    ├── basic.html
    ├── all-layouts.html
    ├── dark-theme.html
    └── README.md
```

### Serving Assets

**File**: `routes/web_v2.php` (MODIFY)

```php
// Serve embed assets with cache headers
Route::get('/embed/{file}', function (string $file) {
    $path = public_path('embed/' . $file);

    if (!file_exists($path) || !preg_match('/\.(js|css|map)$/', $file)) {
        abort(404);
    }

    $mimeTypes = [
        'js' => 'application/javascript',
        'css' => 'text/css',
        'map' => 'application/json',
    ];

    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('file', '.*\.(js|css|map)$');
```

---

## Testing Strategy

### Unit Tests

**Directory**: `tests/js/embed/`

**Test files**:
- `responsive.test.ts` - Column calculations
- `SquareLayout.test.ts`
- `JustifiedLayout.test.ts`
- `MasonryLayout.test.ts`
- `GridLayout.test.ts`
- `FilmstripLayout.test.ts`
- `Lightbox.test.ts`
- `ImageLoader.test.ts`

**Framework**: Vitest

```typescript
// Example: responsive.test.ts
import { describe, it, expect } from 'vitest';
import { calculateColumns } from '../utils/responsive';

describe('calculateColumns', () => {
  it('calculates correct number of columns', () => {
    const { columns, itemWidth } = calculateColumns(1000, 200, 12);
    expect(columns).toBe(5);
    expect(itemWidth).toBeGreaterThan(200);
  });

  it('distributes remaining space evenly', () => {
    const { columns, itemWidth } = calculateColumns(1000, 200, 12);
    const totalWidth = columns * itemWidth + (columns - 1) * 12;
    expect(totalWidth).toBeLessThanOrEqual(1000);
  });
});
```

### Integration Tests

**Directory**: `tests/Feature/Embed/`

```php
<?php

namespace Tests\Feature\Embed;

use Tests\TestCase;
use App\Models\Album;
use App\Models\Photo;

class EmbedControllerTest extends TestCase
{
    public function test_returns_public_album_data(): void
    {
        $album = Album::factory()
            ->public()
            ->has(Photo::factory()->count(5))
            ->create();

        $response = $this->getJson("/api/v2/Embed/{$album->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'album' => ['id', 'title', 'photo_count'],
            'photos' => [
                ['id', 'size_variants', 'exif'],
            ],
        ]);
    }

    public function test_denies_access_to_private_album(): void
    {
        $album = Album::factory()
            ->private()
            ->create();

        $response = $this->getJson("/api/v2/Embed/{$album->id}");

        $response->assertForbidden();
    }

    public function test_includes_cors_headers(): void
    {
        $album = Album::factory()->public()->create();

        $response = $this->getJson("/api/v2/Embed/{$album->id}");

        $response->assertHeader('Access-Control-Allow-Origin', '*');
    }
}
```

### Manual Testing Checklist

- [ ] All 5 layout types render correctly
- [ ] Responsive column calculation works at various widths
- [ ] Lightbox opens and navigates properly
- [ ] Keyboard navigation works (arrows, ESC, space)
- [ ] Touch/swipe gestures work on mobile
- [ ] Theme switching works (light/dark/auto)
- [ ] Album metadata displays correctly
- [ ] EXIF data displays when available
- [ ] Lazy loading triggers as user scrolls
- [ ] Error states display appropriately
- [ ] Embed code generator produces valid code
- [ ] Copy to clipboard works
- [ ] Live preview updates with config changes
- [ ] Works on external websites (test domain)
- [ ] No CSS conflicts with parent page
- [ ] Performance is acceptable (< 2s load)

---

## Implementation Timeline

### Week 1: Backend Foundation (Days 1-5)
- Day 1-2: Create EmbedController and resources
- Day 2-3: Configure CORS and routes
- Day 3-4: Extend URL expiration logic
- Day 4-5: Testing and refinement

### Week 2: Widget Core & Layouts (Days 6-10)
- Day 6: Project setup, build config, types
- Day 7: Main widget class, API client
- Day 8-9: Implement Square, Masonry, Grid layouts
- Day 9-10: Implement Justified layout with library integration

### Week 3: Filmstrip & Lightbox (Days 11-15)
- Day 11-12: Implement Filmstrip layout
- Day 13-14: Build Lightbox component
- Day 14-15: Image loading strategy, lazy loading

### Week 4: UI Integration (Days 16-20)
- Day 16-17: Album Header component
- Day 17-18: Embed Code Generator modal
- Day 18-19: Album view integration
- Day 19-20: Styling and themes

### Week 5: Polish & Testing (Days 21-25)
- Day 21: Accessibility features (ARIA, keyboard nav)
- Day 22: Error handling and edge cases
- Day 23: Cross-browser testing
- Day 24: Performance optimization
- Day 25: Documentation and examples

---

## File Checklist

### Backend Files
- [ ] `app/Http/Controllers/Gallery/EmbedController.php`
- [ ] `app/Http/Resources/Embed/EmbedAlbumResource.php`
- [ ] `app/Http/Resources/Embed/EmbedPhotoResource.php`
- [ ] `app/Http/Middleware/EmbedCors.php` (optional)
- [ ] `routes/api_v2.php` (modified)
- [ ] `config/cors.php` (modified)

### Frontend Widget Files
- [ ] `vite.embed.config.ts`
- [ ] `resources/js/embed/index.ts`
- [ ] `resources/js/embed/LycheeEmbed.ts`
- [ ] `resources/js/embed/config.ts`
- [ ] `resources/js/embed/types.ts`
- [ ] `resources/js/embed/api/client.ts`
- [ ] `resources/js/embed/layouts/*.ts` (6 files)
- [ ] `resources/js/embed/components/*.ts` (3 files)
- [ ] `resources/js/embed/utils/*.ts` (4 files)
- [ ] `resources/js/embed/styles/*.css` (3 files)

### Frontend Lychee UI Files
- [ ] `resources/js/components/modals/EmbedCodeModal.vue`
- [ ] `resources/js/views/gallery-panels/Album.vue` (modified)

### Test Files
- [ ] `tests/js/embed/*.test.ts` (8 files)
- [ ] `tests/Feature/Embed/*.php` (3 files)

### Documentation Files
- [ ] `public/embed/README.md`
- [ ] `public/embed/examples/*.html` (5 files)

---

## Success Criteria

- [ ] Widget loads and displays all layout types correctly
- [ ] Responsive column calculation matches Lychee's algorithm
- [ ] All interactive features work (lightbox, navigation, etc.)
- [ ] Accessibility standards met (WCAG 2.1 Level AA)
- [ ] Performance targets met (< 100KB bundle, < 2s load)
- [ ] Cross-browser compatibility verified
- [ ] No conflicts with external sites
- [ ] Comprehensive documentation completed
- [ ] All tests passing
- [ ] Code reviewed and approved
