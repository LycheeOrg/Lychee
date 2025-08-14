# Lychee Frontend Documentation

This document provides comprehensive information about Lychee's frontend architecture, built with Vue.js 3, TypeScript, and PrimeVue.

## Overview

Lychee's frontend is a modern Single Page Application (SPA) built with:

- **Vue.js 3** with Composition API and TypeScript
- **PrimeVue** as the primary UI component library
- **Tailwind CSS** for styling with custom PrimeUI integration
- **Vue Router** for client-side routing
- **Pinia** for state management
- **Vite** as the build tool and development server
- **i18n** for internationalization

## Project Structure

```
resources/js/
├── app.ts                     # Main application entry point
├── components/                
│   ├── diagnostics/           # System diagnostic components
│   ├── drawers/               # Side panel/drawer components
│   ├── footers/               # Footer components
│   ├── forms/                 # Form components and inputs
│   ├── gallery/               # Photo/album gallery components
│   ├── headers/               # Header and navigation components
│   ├── icons/                 # Custom icon components
│   ├── loading/               # Loading state components
│   ├── maintenance/           # System maintenance components
│   ├── modals/                # Modal dialog components
│   ├── settings/              # Settings page components
│   └── statistics/            # Statistics display components
│
├── composables/               
│   ├── album/                 # Album-related composables
│   ├── contextMenus/          # Context menu logic
│   ├── modalsTriggers/        # Modal state management
│   ├── photo/                 # Photo-related composables
│   ├── preview/               # Photo preview functionality
│   ├── search/                # Search functionality
│   └── selections/            # Selection state management
│
├── config/                    # Configuration files
├── layouts/                   # Photo layout helpers (justified, masonry, etc.)
├── menus/                     # Left menu structure definitions
├── router/                    # Vue Router configuration
├── services/                  # API service layer
├── stores/                    # Pinia state stores
├── style/                     # Style configurations for PrimeVue
├── utils/                     # Utility functions
├── vendor/                    # Third-party integrations
└── views/                     # Page-level Vue components
    ├── gallery-panels/        # Gallery-specific views
    └── *.vue                  # Application pages
```

## Key Features

### 1. Component Architecture

Lychee follows a modular component architecture:

#### Base Components
Located in `components/`, these are reusable UI elements:
- Form inputs and controls
- Modals and dialogs
- Loading states
- Icons and visual elements

#### Page Components
Located in `views/`, these represent full pages:
- **Gallery Views**: Album, Albums, Favourites, Search, Map, Frame, Flow
- **Admin Views**: Settings, Users, Permissions, Maintenance, Diagnostics
- **System Views**: Statistics, Jobs, Profile

### 2. State Management with Pinia

State is managed through dedicated stores:

- **`Auth.ts`** - User authentication and session management
- **`LycheeState.ts`** - Global application state and configuration
- **`LeftMenuState.ts`** - Left navigation menu state
- **`ModalsState.ts`** - Modal dialog state management
- **`FlowState.ts`** - Photo flow/timeline state
- **`FavouriteState.ts`** - Favourites items

#### Store Pattern Example
```typescript
import { defineStore } from 'pinia'

export const useFavouriteStore = defineStore("favourite-store", {
	state: () => ({
		photos: undefined as App.Http.Resources.Models.PhotoResource[] | undefined,
	}),
	getters: {
		getPhotoIds(): string[] {
			return this.photos?.map((p) => p.id) ?? [];
		},
	},
	actions: {
		addPhoto(photo: App.Http.Resources.Models.PhotoResource) {
			if (!this.photos) {
				this.photos = [];
			}
			this.photos.push(photo);
		},
	},
	persist: true,
});
```

### 3. Composables for Reusable Logic

Vue composables encapsulate reusable functionality:

#### Album Management
- Album creation, editing, deletion
- Album navigation and tree operations
- Permission handling

#### Photo Operations
- Photo upload, editing, metadata management
- Photo selection and batch operations
- Preview and slideshow functionality

#### UI Interactions
- Context menus and right-click actions
- Modal state management
- Drag and drop operations

#### Example Composable Structure
```typescript
// Very simplied!
export function usePhotoSelection() {
  const selectedPhotos = ref<Photo[]>([])
  
  function selectPhoto(photo: Photo) {
    // Selection logic
  }
  
  function clearSelection() {
    selectedPhotos.value = []
  }
  
  return {
    selectedPhotos: readonly(selectedPhotos),
    selectPhoto,
    clearSelection
  }
}
```

### 4. Service Layer

Services in `services/` handle API communication:

- **`album-service.ts`** - Album CRUD operations
- **`photo-service.ts`** - Photo management and upload
- **`settings-service.ts`** - Application configuration
- **`user-service.ts`** - User management and authentication

#### Service Pattern
```typescript
export class AlbumService {
  create(data: CreateAlbumData): Promise<AxiosResponse<Album>> {
    return axios.post('/api/albums', data)
  }
  
  getAlbum(id: string): Promise<AxiosResponse<Album>> {
    return axios.get(`/api/albums/${id}`)
  }
}
```

### 5. Routing Architecture

Vue Router handles client-side navigation with:

#### Route Structure
- **Gallery Routes**: `/gallery/*` - Photo and album browsing
- **Admin Routes**: `/settings`, `/users`, `/maintenance` - Administration
- **Utility Routes**: `/search`, `/map`, `/frame` - Special views

#### Dynamic Imports
Routes use lazy loading for better performance:
```typescript
const Settings = () => import('@/views/Settings.vue')
```

### 6. Internationalization (i18n)

Multi-language support through:

- **Laravel Vue i18n** integration
- Translation files in `lang/` directory
- Dynamic language switching
- Pluralization and parameter substitution

#### Usage
```typescript
// In components
$t('gallery.album.create')

// In setup script
import { trans } from "laravel-vue-i18n";

trans('gallery.album.create')
```

## Styling and Theming

### Tailwind CSS Integration
- Utility-first CSS approach
- Custom PrimeUI integration via `tailwindcss-primeui`
- Responsive design utilities
- Dark mode support via the `dark:` prefix

### PrimeVue Theme Customization
- Custom Aura theme preset in `style/preset`
- Consistent color palette
- Component-specific styling overrides

### SCSS Architecture
```
resources/sass/
├── app.css               # Main stylesheet entry
└── fonts.css             # fonts styles
```

## Development Workflow

### Development Server
```bash
# Start development server with hot reload
npm run dev

# TypeScript type checking
npm run check

# Linting and formatting
npm run lint
npm run format
```

### Build Process
```bash
# Production build
npm run build
```

### Code Quality
- **ESLint** configuration for Vue 3 + TypeScript
- **Prettier** for consistent code formatting
- **TypeScript** strict mode for type safety
- **Vue Component Analyzer** for composition API analysis

## API Integration

### Axios Configuration
- Base URL configuration
- Request/response interceptors
- Caching layer with `axios-cache-interceptor`
- Error handling and retry logic

### Data Flow
1. **User Action** → Component method
2. **Component** → Composable function
3. **Composable** → Service call
4. **Service** → API request
5. **Response** → Store update
6. **Store** → Component reactivity

## Key Libraries and Integrations

### Map Integration
- **Leaflet.js** for interactive maps
- **Leaflet.markercluster** for photo clustering
- **Leaflet GPX** for GPS track display

### User Interface
- **TinyGesture** for touch gesture handling
- **Mousetrap** for keyboard shortcuts
- **Vue Collapsed** for collapsible content
- **ScrollSpy** for navigation highlighting

### Utilities
- **QR Code** generation for sharing
- **sprintf-js** for string formatting
- **VueUse** for composition utilities

## Testing and Quality Assurance

### Type Safety
- Full TypeScript coverage
- Strict type checking enabled
- Custom type definitions for API responses

### Development Tools
- Vue DevTools integration
- Hot module replacement (HMR)
- Source map support for debugging

### Code Standards
- Consistent naming conventions
- Component composition patterns
- Error boundary implementation
- Accessibility considerations

## Related Documentation

For more detailed information about specific aspects of the frontend:

- **[Vue 3 Guide](Vue3.md)** - Vue 3 Composition API patterns, TypeScript integration, and Lychee-specific conventions
- **[Gallery Frontend Documentation](Gallery.md)** - Detailed gallery interface and viewing modes, including Albums, Flow, Map, Search, and Frame views
- **[Layout System Documentation](Layouts.md)** - Photo layout algorithms and responsive design patterns for Square, Justified, Masonry, and Grid layouts

---

*Last updated: August 14, 2025*
