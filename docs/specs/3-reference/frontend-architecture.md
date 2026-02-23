# Frontend Architecture

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

## Vue 3 Architecture

Lychee uses Vue 3 with the **Composition API** exclusively, following modern Vue.js best practices while integrating seamlessly with Laravel as the backend API.

### Core Technologies

- **Vue 3.5.18** with Composition API (no Options API)
- **TypeScript** for type safety and better developer experience
- **Pinia** for state management with persistence
- **Vue Router 4** for client-side routing
- **PrimeVue 4** as the primary UI component library
- **Vite** for build tooling and development server

### Script Setup Pattern

All Vue components in Lychee use the `<script setup>` syntax for cleaner, more concise code:

```vue
<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useLycheeStateStore } from '@/stores/LycheeState'

// Props with TypeScript
const props = defineProps<{
  albumId: string
  photoId?: string
}>()

// Reactive state
const isLoading = ref(false)
const selectedPhotos = ref<Photo[]>([])

// Computed properties
const hasSelection = computed(() => selectedPhotos.value.length > 0)

// Store usage
const lycheeStore = useLycheeStateStore()
const router = useRouter()
</script>
```

### TypeScript Integration

Lychee emphasizes type safety throughout the Vue components:

```typescript
// Strongly typed props
interface AlbumPanelProps {
  albumId: string
  photos: App.Http.Resources.Models.PhotoResource[]
  config: App.Http.Resources.GalleryConfigs.AlbumConfig
}

const props = defineProps<AlbumPanelProps>()

// Typed reactive references
const user = ref<App.Http.Resources.Models.UserResource | undefined>()
const albums = ref<App.Http.Resources.Models.AlbumResource[]>([])
```

## Component Architecture

### Base Components

Located in `components/`, these are reusable UI elements:
- Form inputs and controls
- Modals and dialogs
- Loading states
- Icons and visual elements

### Page Components

Located in `views/`, these represent full pages:
- **Gallery Views**: Album, Albums, Favourites, Search, Map, Frame, Flow
- **Admin Views**: Settings, Users, Permissions, Maintenance, Diagnostics
- **System Views**: Statistics, Jobs, Profile

## State Management with Pinia

State is managed through dedicated stores:

- **`Auth.ts`** - User authentication and session management
- **`LycheeState.ts`** - Global application state and configuration
- **`LeftMenuState.ts`** - Left navigation menu state
- **`ModalsState.ts`** - Modal dialog state management
- **`FlowState.ts`** - Photo flow/timeline state
- **`FavouriteState.ts`** - Favourites items

### Store Pattern

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

### Store Pattern Example

```typescript
// stores/Auth.ts
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as App.Http.Resources.Models.UserResource | null,
    oauthData: undefined as OauthProvider[] | undefined,
  }),
  
  actions: {
    async getUser(): Promise<App.Http.Resources.Models.UserResource> {
      if (this.user === null) {
        await AuthService.user().then((response) => {
          this.user = response.data
        })
      }
      return this.user as App.Http.Resources.Models.UserResource
    },
    
    setUser(user: App.Http.Resources.Models.UserResource | null) {
      this.user = user
    },
  },
})
```

### Store Usage in Components

```vue
<script setup lang="ts">
import { useAuthStore } from '@/stores/Auth'
import { storeToRefs } from 'pinia'

const auth = useAuthStore()
const { user } = storeToRefs(auth) // Reactive references

// Use store actions
await auth.getUser()
</script>
```

## Composables for Reusable Logic

Composables encapsulate reusable functionality:

### Album Management
- Album creation, editing, deletion
- Album navigation and tree operations
- Permission handling

### Photo Operations
- Photo upload, editing, metadata management
- Photo selection and batch operations
- Preview and slideshow functionality

### UI Interactions
- Context menus and right-click actions
- Modal state management
- Drag and drop operations

### Example: Album Refresher Composable

```typescript
export function useAlbumRefresher(
  albumId: Ref<string>, 
  photoId: Ref<string | undefined>, 
  auth: AuthStore, 
  isLoginOpen: Ref<boolean>
) {
  const isLoading = ref(false)
  const album = ref<AlbumResource | undefined>()
  const photos = ref<PhotoResource[]>([])
  
  function loadAlbum(): Promise<void> {
    isLoading.value = true
    return AlbumService.get(albumId.value).then((data) => {
      album.value = data.data.resource
      photos.value = data.data.photos
      isLoading.value = false
    })
  }
  
  return {
    isLoading,
    album,
    photos,
    loadAlbum,
  }
}
```

### Usage in Components

```vue
<script setup lang="ts">
const props = defineProps<{ albumId: string }>()
const albumId = ref(props.albumId)

const { isLoading, album, photos, loadAlbum } = useAlbumRefresher(
  albumId, 
  photoId, 
  auth, 
  isLoginOpen
)

// Load on mount
onMounted(() => loadAlbum())
</script>
```

### Example Composable Pattern

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

## Service Layer

Services in `services/` handle API communication:

- **`album-service.ts`** - Album CRUD operations
- **`photo-service.ts`** - Photo management and upload
- **`settings-service.ts`** - Application configuration
- **`user-service.ts`** - User management and authentication

### Service Pattern

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

## Routing Architecture

Vue Router handles client-side navigation with:

### Route Structure
- **Gallery Routes**: `/gallery/*` - Photo and album browsing
- **Admin Routes**: `/settings`, `/users`, `/maintenance` - Administration
- **Utility Routes**: `/search`, `/map`, `/frame` - Special views

### Route-Based Components

Components handle route parameters reactively:

```vue
<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// Reactive route params
const albumId = computed(() => route.params.albumId as string)
const photoId = computed(() => route.params.photoId as string | undefined)

// Navigation
function goBack() {
  router.push({ name: 'gallery' })
}

function openPhoto(photo: Photo) {
  router.push({ 
    name: 'album', 
    params: { albumId: albumId.value, photoId: photo.id } 
  })
}
</script>
```

### Dynamic Imports

Routes use lazy loading for better performance:
```typescript
const Settings = () => import('@/views/Settings.vue')
```

## Event Handling

### Component Events

Lychee uses `defineEmits` for component communication:

```vue
<script setup lang="ts">
interface PhotoThumbEvents {
  clicked: [index: number, event: MouseEvent]
  selected: [photo: Photo]
}

const emit = defineEmits<PhotoThumbEvents>()

function handleClick(event: MouseEvent) {
  emit('clicked', props.index, event)
}
</script>
```

### Keyboard Shortcuts

Global keyboard handling using VueUse:

```typescript
import { onKeyStroke } from '@vueuse/core'
import { shouldIgnoreKeystroke } from '@/utils/keybindings-utils'

// Global shortcuts
onKeyStroke('f', () => !shouldIgnoreKeystroke() && toggleFullscreen())
onKeyStroke('ArrowLeft', () => !shouldIgnoreKeystroke() && previousPhoto())
onKeyStroke('Escape', () => !shouldIgnoreKeystroke() && goBack())
```

## PrimeVue Integration

Lychee leverages PrimeVue components throughout:

```vue
<template>
  <Button @click="handleSubmit" :loading="isSubmitting">
    Submit
  </Button>
  
  <Dialog v-model:visible="isDialogOpen" :modal="true">
    <template #header>
      <h3>Photo Details</h3>
    </template>
    <!-- Dialog content -->
  </Dialog>
  
  <Toast />
</template>

<script setup lang="ts">
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'

const toast = useToast()

function showSuccess() {
  toast.add({ 
    severity: 'success', 
    summary: 'Success', 
    detail: 'Photo uploaded successfully' 
  })
}
</script>
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
└── fonts.css             # Fonts styles
```

## Internationalization (i18n)

Multi-language support through:

- **Laravel Vue i18n** integration
- Translation files in `lang/` directory
- Dynamic language switching
- Pluralization and parameter substitution

### Usage

```typescript
// In components
$t('gallery.album.create')

// In setup script
import { trans } from "laravel-vue-i18n";

trans('gallery.album.create')
```

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

## Lychee-Specific Conventions

### 1. No Async/Await Pattern

Lychee deliberately avoids `async/await` in favor of `.then()` chains:

```typescript
// ✅ Preferred in Lychee
AlbumService.get(albumId.value)
  .then((response) => {
    album.value = response.data
  })
  .catch((error) => {
    console.error(error)
  })

// ❌ Avoided in Lychee
// const response = await AlbumService.get(albumId.value)
```

### 2. Function Declarations Over Arrow Functions

Lychee prefers traditional function declarations:

```typescript
// ✅ Preferred
function loadPhotos() {
  // Implementation
}

// ❌ Avoided
const loadPhotos = () => {
  // Implementation
}
```

### 3. Reactive State Patterns

Common patterns for managing reactive state:

```typescript
// Single items with computed fallbacks
const selectedPhoto = ref<Photo | undefined>()
const hasPhoto = computed(() => selectedPhoto.value !== undefined)

// Arrays with computed filters
const photos = ref<Photo[]>([])
const favoritePhotos = computed(() => 
  photos.value.filter(photo => photo.is_highlighted)
)

// Complex state with multiple refs
const isLoading = ref(false)
const error = ref<string | null>(null)
const data = ref<ApiResponse | undefined>()
```

## Performance Considerations

### Lazy Loading
- Components use `import()` for code splitting
- Images loaded progressively with intersection observer
- Virtual scrolling for large photo sets

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

### Reactive Performance

- Prefer `computed()` over watchers when possible
- Implement proper cleanup in `onUnmounted()`

```typescript
export function usePhotoSelection() {
  const selectedPhotos = ref<Photo[]>([])
  
  function selectPhoto(photo: Photo) {
    selectedPhotos.value.push(photo)
  }
  
  function clearSelection() {
    selectedPhotos.value = []
  }
  
  return {
    selectedPhotos,
    selectPhoto,
    clearSelection
  }
}
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

## Best Practices Summary

1. **Always use `<script setup lang="ts">` with TypeScript**
2. **Prefer `.then()` over `async/await`**
3. **Use traditional function declarations**
4. **Leverage composables for reusable logic**
5. **Implement proper TypeScript typing**
6. **Use Pinia for complex state management**
7. **Follow PrimeVue component patterns**
8. **Implement proper cleanup and memory management**

## Related Documentation

For more detailed information about specific aspects of the frontend:

- [Frontend Gallery Views](frontend-gallery.md) - Gallery interface, viewing modes, and component architecture
- [Frontend Layout System](frontend-layouts.md) - Photo layout algorithms and responsive design patterns
- [Coding Conventions](coding-conventions.md) - Coding standards including Vue3/TypeScript conventions

---

*Last updated: December 22, 2025*
