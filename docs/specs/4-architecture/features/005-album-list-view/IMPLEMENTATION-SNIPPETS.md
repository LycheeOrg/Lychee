# Feature 005 - Implementation Code Snippets

Quick reference for exact code changes needed.

## Backend Changes

### 1. Database Migration

Create: `database/migrations/2026_01_04_000000_add_album_layout_config.php`

```php
<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public const MOD_GALLERY = 'Gallery';

	/**
	 * @return array<int,array{key:string,value:string,is_secret:bool,cat:string,type_range:string,description:string,details?:string,order?:int,not_on_docker?:bool,is_expert?:bool,level?:int}>
	 */
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'album_layout',
				'value' => 'grid',
				'cat' => self::MOD_GALLERY,
				'type_range' => 'grid|list',
				'description' => 'Default album view layout.',
				'details' => 'Choose between grid (thumbnail cards) or list (detailed rows) view for albums. Users can toggle between views client-side, but preference does not persist across page reloads.',
				'is_secret' => false,
				'is_expert' => false,
				'order' => 50,
				'not_on_docker' => false,
				'level' => 0,
			],
		];
	}
};
```

**Notes:**
- Uses `BaseConfigMigration` pattern (automatic up/down migrations)
- `type_range` = `'grid|list'` creates a dropdown in admin UI
- `cat` = `'Gallery'` places it in Gallery settings section
- `is_expert` = `false` makes it visible to all admins (not just expert mode)
- `order` = `50` determines position in settings list

### 2. InitConfig.php

File: `app/Http/Resources/GalleryConfigs/InitConfig.php`

**Add property (line ~48, after album decoration settings):**

```php
// Album view mode
public string $album_layout;
```

**Add initialization in constructor (line ~154, after photo_thumb_tags_enabled):**

```php
$this->album_layout = request()->configs()->getValueAsString('album_layout');
```

**Full context (line ~154):**

```php
$this->photo_thumb_info = request()->configs()->getValueAsEnum('photo_thumb_info', PhotoThumbInfoType::class);
$this->is_photo_thumb_tags_enabled = request()->configs()->getValueAsBool('photo_thumb_tags_enabled');
$this->album_layout = request()->configs()->getValueAsString('album_layout');  // ADD THIS LINE
```

## Frontend Changes

### 3. LycheeState.ts

File: `resources/js/stores/LycheeState.ts`

**Add state property (line ~47, after album decoration settings):**

```typescript
// album stuff
display_thumb_album_overlay: "always" as App.Enum.VisibilityType,
display_thumb_photo_overlay: "always" as App.Enum.VisibilityType,
album_subtitle_type: "OLDSTYLE" as App.Enum.ThumbAlbumSubtitleType,
album_decoration: "LAYERS" as App.Enum.AlbumDecorationType,
album_decoration_orientation: "ROW" as App.Enum.AlbumDecorationOrientation,
album_view_mode: "grid" as "grid" | "list",  // ADD THIS LINE
number_albums_per_row_mobile: 3 as 1 | 2 | 3,
```

**Add initialization in load() action (line ~167, after is_photo_thumb_tags_enabled):**

```typescript
this.number_albums_per_row_mobile = data.number_albums_per_row_mobile;
this.photo_thumb_info = data.photo_thumb_info;
this.is_photo_thumb_tags_enabled = data.is_photo_thumb_tags_enabled;
this.album_view_mode = data.album_layout;  // ADD THIS LINE
```

### 4. TypeScript Type Definition

The TypeScript type for InitConfig should be auto-generated from the PHP class via `Spatie\TypeScriptTransformer`. Verify it includes:

```typescript
namespace App.Http.Resources.GalleryConfigs {
  export type InitConfig = {
    // ... existing properties
    album_layout: "grid" | "list";  // Should appear automatically
  };
}
```

### 5. AlbumHero.vue - Toggle Buttons

File: `resources/js/components/gallery/albumModule/AlbumHero.vue`

**Add imports:**

```typescript
import { useLycheeStateStore } from "@/stores/LycheeState";
```

**Add to setup:**

```typescript
const lycheeStore = useLycheeStateStore();

function toggleAlbumView(mode: "grid" | "list") {
  lycheeStore.album_view_mode = mode;
}
```

**Add to template (line ~33, in the icon row):**

```vue
<!-- Existing icons -->
<Button
  icon="pi pi-th-large"
  class="border-none"
  :severity="lycheeStore.album_view_mode === 'grid' ? 'primary' : 'secondary'"
  text
  :aria-label="$t('view.grid')"
  :aria-pressed="lycheeStore.album_view_mode === 'grid'"
  @click="toggleAlbumView('grid')"
/>
<Button
  icon="pi pi-list"
  class="border-none"
  :severity="lycheeStore.album_view_mode === 'list' ? 'primary' : 'secondary'"
  text
  :aria-label="$t('view.list')"
  :aria-pressed="lycheeStore.album_view_mode === 'list'"
  @click="toggleAlbumView('list')"
/>
```

### 6. AlbumsHeader.vue - Toggle Buttons

File: `resources/js/components/headers/AlbumsHeader.vue`

**Add to imports:**

```typescript
import { useLycheeStateStore } from "@/stores/LycheeState";
```

**Add to setup (near line 150):**

```typescript
const lycheeStore = useLycheeStateStore();

function toggleToGrid() {
  lycheeStore.album_view_mode = "grid";
}

function toggleToList() {
  lycheeStore.album_view_mode = "list";
}
```

**Add to menu computed property (line ~243, before search button):**

```typescript
const menu = computed(() =>
  [
    // ... existing items
    {
      icon: "pi pi-th-large",
      type: "fn" as const,
      callback: toggleToGrid,
      severity: lycheeStore.album_view_mode === "grid" ? "primary" : "secondary",
      if: true,
      key: "view_grid",
    },
    {
      icon: "pi pi-list",
      type: "fn" as const,
      callback: toggleToList,
      severity: lycheeStore.album_view_mode === "list" ? "primary" : "secondary",
      if: true,
      key: "view_list",
    },
    {
      icon: "pi pi-search",
      type: "fn",
      callback: openSearch,
      if: albumsStore.rootConfig?.is_search_accessible,
      key: "search",
    },
    // ... rest of items
  ].filter((item) => item.if),
) as ComputedRef<MenuRight[]>;
```

### 7. AlbumThumbPanel.vue - Conditional Rendering

File: `resources/js/components/gallery/albumModule/AlbumThumbPanel.vue`

**Add import:**

```typescript
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumListView from "./AlbumListView.vue";  // NEW COMPONENT
```

**Add to setup:**

```typescript
const lycheeStore = useLycheeStateStore();
```

**Modify template to conditionally render:**

```vue
<template>
  <div>
    <!-- Existing header/controls -->

    <!-- List view -->
    <AlbumListView
      v-if="lycheeStore.album_view_mode === 'list'"
      :albums="albums"
      :selected-ids="selectedAlbumsIds"
      @album-clicked="handleAlbumClick"
      @album-contexted="handleAlbumContext"
    />

    <!-- Grid view (existing) -->
    <AlbumThumbPanelList
      v-else
      :albums="albums"
      :selected-ids="selectedAlbumsIds"
      @album-clicked="handleAlbumClick"
      @album-contexted="handleAlbumContext"
    />
  </div>
</template>
```

### 8. AlbumListView.vue - New Component

File: `resources/js/components/gallery/albumModule/AlbumListView.vue`

```vue
<template>
  <div class="flex flex-col gap-0">
    <AlbumListItem
      v-for="album in albums"
      :key="album.id"
      :album="album"
      :is-selected="selectedIds.includes(album.id)"
      @clicked="$emit('album-clicked', $event)"
      @contexted="$emit('album-contexted', $event)"
    />
  </div>
</template>

<script setup lang="ts">
import AlbumListItem from "./AlbumListItem.vue";

defineProps<{
  albums: App.Http.Resources.Models.AlbumResource[];
  selectedIds: string[];
}>();

defineEmits<{
  "album-clicked": [event: MouseEvent, album: App.Http.Resources.Models.AlbumResource];
  "album-contexted": [event: MouseEvent, album: App.Http.Resources.Models.AlbumResource];
}>();
</script>
```

### 9. AlbumListItem.vue - New Component

File: `resources/js/components/gallery/albumModule/AlbumListItem.vue`

```vue
<template>
  <div
    class="flex items-center gap-4 p-3 border-b border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 ltr:flex-row rtl:flex-row-reverse"
    :class="{
      'bg-primary-100 dark:bg-primary-900 ring-2 ring-primary-500': isSelected,
    }"
    @click="$emit('clicked', $event, album)"
    @contextmenu.prevent="$emit('contexted', $event, album)"
  >
    <!-- Thumbnail -->
    <router-link
      :to="{ name: 'album', params: { albumId: album.id } }"
      class="flex-shrink-0"
    >
      <img
        :src="album.thumb?.thumb?.url || '/img/no_images.svg'"
        :alt="album.title"
        class="w-16 h-16 md:w-16 md:h-16 object-cover rounded"
      />
    </router-link>

    <!-- Content (title + counts) -->
    <div class="flex-1 min-w-0 flex flex-col md:flex-row md:items-center md:gap-2 ltr:text-left rtl:text-right">
      <!-- Title -->
      <span class="text-base font-medium truncate md:truncate-none">
        {{ album.title }}
      </span>

      <!-- Counts (inline on wide screens, stacked on narrow) -->
      <div class="flex gap-2 text-sm text-gray-600 dark:text-gray-400">
        <!-- Photo count (only if > 0) -->
        <span v-if="album.num_photos > 0" class="flex items-center gap-1">
          <i class="pi pi-image" />
          {{ album.num_photos }} {{ album.num_photos === 1 ? "photo" : "photos" }}
        </span>

        <!-- Sub-album count (only if > 0) -->
        <span v-if="album.num_children > 0" class="flex items-center gap-1">
          <i class="pi pi-folder" />
          {{ album.num_children }} {{ album.num_children === 1 ? "album" : "albums" }}
        </span>
      </div>
    </div>

    <!-- Badges (if any) -->
    <div v-if="album.policy" class="flex gap-1">
      <!-- Copy badge logic from AlbumThumb.vue -->
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  album: App.Http.Resources.Models.AlbumResource;
  isSelected: boolean;
}>();

defineEmits<{
  clicked: [event: MouseEvent, album: App.Http.Resources.Models.AlbumResource];
  contexted: [event: MouseEvent, album: App.Http.Resources.Models.AlbumResource];
}>();
</script>
```

## Testing

### Backend Test Example

```php
public function testAlbumLayoutDefaultsToGrid()
{
    $config = Config::first();
    $this->assertEquals('grid', $config->album_layout);
}

public function testAlbumLayoutEnumValidation()
{
    $config = Config::first();
    $config->album_layout = 'invalid';

    $this->expectException(\Exception::class);
    $config->save();
}
```

### Frontend Test Example

```typescript
describe('Album View Toggle', () => {
  it('initializes from InitConfig', async () => {
    const store = useLycheeStateStore();
    await store.load();
    expect(store.album_view_mode).toBe('grid');
  });

  it('toggles between grid and list', () => {
    const store = useLycheeStateStore();
    store.album_view_mode = 'list';
    expect(store.album_view_mode).toBe('list');

    store.album_view_mode = 'grid';
    expect(store.album_view_mode).toBe('grid');
  });
});
```

---

**Summary:** All code snippets follow existing patterns in the codebase (especially `are_nsfw_visible` pattern). No new API endpoints, no localStorage, just reactive state initialized from InitConfig.
