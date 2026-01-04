# Feature 005 - Final Architecture Summary

**Date:** 2026-01-04
**Status:** Specification Complete

## Architecture Overview

### Settings Flow (Critical Understanding)

```
┌─────────────────────────────────────────────────────────────┐
│                         ADMIN                               │
│  ┌────────────────────────────────────────────────────┐    │
│  │ Configs UI → Database (configs table)              │    │
│  │ Sets default: album_layout = 'grid' or 'list'     │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (InitConfig.php)                 │
│  ┌────────────────────────────────────────────────────┐    │
│  │ public string $album_layout;                       │    │
│  │ $this->album_layout =                              │    │
│  │   request()->configs()->getValueAsString(          │    │
│  │     'album_layout'                                 │    │
│  │   );                                               │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                            ↓
              GET /api/Gallery (includes InitConfig)
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                  FRONTEND (LycheeState)                     │
│  ┌────────────────────────────────────────────────────┐    │
│  │ album_view_mode: 'grid' | 'list'                   │    │
│  │ Initialized from InitConfig.album_layout           │    │
│  │ Updated on toggle (NO server persistence)          │    │
│  └────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                            ↓
                    User toggles view
                            ↓
              LycheeState.album_view_mode updated
                            ↓
              AlbumThumbPanel.vue reactively renders
                  AlbumListView OR AlbumThumbPanelList
                            ↓
                      User reloads page
                            ↓
              State resets to admin default (no persistence)
```

## Key Architecture Decisions

### 1. No User Persistence
- **User toggles are session-only** - they do NOT persist across page reloads
- **No localStorage** - preference is purely in reactive state
- **No API endpoint** for user preference updates
- **Page reload = reset** to admin-configured default

### 2. Admin-Configured Default
- **Single source of truth:** `configs` table in database
- **Admin access:** Existing Configs UI (no new UI needed)
- **Default applies to:** All users on page load
- **Exposed via:** InitConfig.php → GET /api/Gallery

### 3. Client-Side Toggle
- **Reactive state:** LycheeState.album_view_mode
- **Immediate update:** No API latency
- **No optimistic UI:** State change is synchronous
- **Toggle locations:** AlbumHero.vue (album detail) + AlbumsHeader.vue (albums page)

## Implementation Checklist

### Backend

- [ ] **Database migration:** Create `database/migrations/2026_01_04_000000_add_album_layout_config.php`
  - Extend `BaseConfigMigration` (not standard Migration)
  - Define config in `getConfigs()` array:
    - `key` = `'album_layout'`
    - `value` = `'grid'` (default)
    - `type_range` = `'grid|list'` (creates dropdown in admin UI)
    - `cat` = `'Gallery'` (settings category)
  - See [IMPLEMENTATION-SNIPPETS.md](IMPLEMENTATION-SNIPPETS.md#1-database-migration) for full code

- [ ] **InitConfig.php:** Add property and initialization
  ```php
  public string $album_layout;

  // In constructor:
  $this->album_layout = request()->configs()->getValueAsString('album_layout');
  ```

- [ ] **Admin Configs UI:** Config will appear automatically in Gallery settings section with dropdown (grid|list)

### Frontend - State Management

- [ ] **LycheeState.ts** - Add state property (line ~47, near other album settings):
  ```typescript
  // album stuff
  album_view_mode: "grid" as "grid" | "list",  // Add this line
  ```

- [ ] **LycheeState.ts** - Initialize from InitConfig in `load()` action (line ~190, after other assignments):
  ```typescript
  this.album_view_mode = data.album_layout;  // Add this line
  ```

- [ ] **Toggle helper** - Can be added to AlbumHero.vue or AlbumsHeader.vue directly:
  ```typescript
  const lycheeStore = useLycheeStateStore();

  function toggleAlbumView() {
    lycheeStore.album_view_mode = lycheeStore.album_view_mode === 'grid' ? 'list' : 'grid';
  }
  ```

  **Note:** This follows the same pattern as `are_nsfw_visible` - it's a reactive state property that can be toggled directly, with the default value coming from InitConfig.

### Frontend - Components

- [ ] **AlbumListView.vue** (new component)
  - Parallel to AlbumThumbPanelList.vue
  - Renders albums as horizontal rows
  - Supports RTL layout with Tailwind classes
  - Passes through selection props

- [ ] **AlbumListItem.vue** (new component)
  - Parallel to AlbumThumb.vue
  - 64px thumbnail (48px on mobile)
  - Title + counts on same line (wide screens ≥md)
  - Hide counts if value === 0
  - Selectable with Ctrl/Cmd/Shift modifiers
  - Compatible with SelectDrag overlay

- [ ] **AlbumThumbPanel.vue** (modify)
  - Conditional rendering based on `LycheeState.album_view_mode`
  - `<AlbumListView v-if="viewMode === 'list'" />`
  - `<AlbumThumbPanelList v-else />`

- [ ] **AlbumHero.vue** (modify)
  - Add two icon buttons: `pi-th-large` (grid) and `pi-list` (list)
  - Place in existing icon row (line 33)
  - Active state visual indicator
  - aria-label and aria-pressed attributes

- [ ] **AlbumsHeader.vue** (modify)
  - Add same toggle buttons to header menu
  - Follow existing button pattern in `menu` computed property
  - Sync with AlbumHero toggle state

### Styling Details

**Wide Screens (≥md):**
```vue
<div class="flex flex-row items-center gap-4 ltr:flex-row rtl:flex-row-reverse">
  <img class="w-16 h-16" /> <!-- thumbnail -->
  <div class="flex-1 flex-row gap-2">
    <span class="text-base font-medium">Album Title</span>
    <span v-if="num_photos > 0" class="text-sm text-muted">
      📷 {{ num_photos }} {{ num_photos === 1 ? 'photo' : 'photos' }}
    </span>
    <span v-if="num_children > 0" class="text-sm text-muted">
      📁 {{ num_children }} {{ num_children === 1 ? 'album' : 'albums' }}
    </span>
  </div>
</div>
```

**Narrow Screens (<md):**
```vue
<div class="flex flex-col gap-2">
  <span class="text-base font-medium">Album Title</span>
  <div class="flex gap-2">
    <!-- Counts below title -->
  </div>
</div>
```

**Selection State:**
```vue
:class="{
  'bg-primary-100 dark:bg-primary-900 ring-2 ring-primary-500': isSelected
}"
```

## Testing Strategy

### Backend Tests
- [ ] Migration runs successfully
- [ ] Config enum validation ('grid'|'list')
- [ ] Invalid values default to 'grid'
- [ ] InitConfig includes album_layout property

### Frontend Tests
- [ ] AlbumListView renders correctly
- [ ] AlbumListItem hides zero counts
- [ ] Wide screen: Title and counts on same line
- [ ] Narrow screen: Counts stack below title
- [ ] Toggle updates LycheeState.album_view_mode
- [ ] AlbumThumbPanel switches between views reactively
- [ ] Page reload resets to admin default
- [ ] RTL mode: Thumbnails right-aligned
- [ ] Selection works in list view (Ctrl/Cmd/Shift)
- [ ] Drag-select overlay works in list view
- [ ] Pluralization correct (1 photo vs 2 photos)

## Non-Goals (Explicitly Out of Scope)

- ❌ User preference persistence (localStorage, cookies, or database)
- ❌ New API endpoints for user settings
- ❌ Per-user preference tracking
- ❌ Per-album view preferences
- ❌ Remember last toggle state across sessions

## Files to Modify

### Backend
1. `database/migrations/YYYY_MM_DD_add_album_layout_config.php` (new)
2. `app/Http/Resources/GalleryConfigs/InitConfig.php` (modify)
3. Config model (modify, if enum validation needed)

### Frontend
4. `resources/js/stores/LycheeState.ts` (modify)
5. `resources/js/components/gallery/albumModule/AlbumListView.vue` (new)
6. `resources/js/components/gallery/albumModule/AlbumListItem.vue` (new)
7. `resources/js/components/gallery/albumModule/AlbumThumbPanel.vue` (modify)
8. `resources/js/components/gallery/albumModule/AlbumHero.vue` (modify)
9. `resources/js/components/headers/AlbumsHeader.vue` (modify)

## Admin Configuration

Admin sets default via existing Configs UI:
- **Setting name:** `album_layout`
- **Type:** String enum
- **Valid values:** `'grid'`, `'list'`
- **Default:** `'grid'`
- **Description:** "Default album view mode (grid or list). Users can toggle views client-side, but preference does not persist across page reloads."

---

**Summary:** This feature provides a list view toggle with admin-configurable defaults. User toggles are client-side only and reset on page reload. No new API endpoints or user persistence mechanisms are required.
