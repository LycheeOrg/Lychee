# Vue 3 in Lychee

This document provides an introduction to how Vue 3 is used in Lychee, covering the specific patterns, conventions, and architectural decisions that make up the frontend framework.

## Vue 3 Architecture in Lychee

Lychee uses Vue 3 with the **Composition API** exclusively, following modern Vue.js best practices while integrating seamlessly with Laravel as the backend API.

### Core Technologies

- **Vue 3.5.18** with Composition API (no Options API)
- **TypeScript** for type safety and better developer experience
- **Pinia** for state management with persistence
- **Vue Router 4** for client-side routing
- **PrimeVue 4** as the primary UI component library
- **Vite** for build tooling and development server

## Component Architecture

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

## State Management with Pinia

### Store Pattern

Lychee uses Pinia stores for centralized state management:

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

Lychee extensively uses composables to encapsulate and reuse reactive logic:

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

## Specific Lychee Conventions

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
  photos.value.filter(photo => photo.is_starred)
)

// Complex state with multiple refs
const isLoading = ref(false)
const error = ref<string | null>(null)
const data = ref<ApiResponse | undefined>()
```

## Router Integration

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

### Component Usage

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

## Performance Considerations

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

## Best Practices Summary

1. **Always use `<script setup lang="ts">` with TypeScript**
2. **Prefer `.then()` over `async/await`**
3. **Use traditional function declarations**
4. **Leverage composables for reusable logic**
5. **Implement proper TypeScript typing**
6. **Use Pinia for complex state management**
7. **Follow PrimeVue component patterns**
8. **Implement proper cleanup and memory management**

This architecture provides Lychee with a maintainable, type-safe, and performant Vue 3 application that integrates seamlessly with the Laravel backend while following modern frontend development practices.

---

*Last updated: August 14, 2025*
