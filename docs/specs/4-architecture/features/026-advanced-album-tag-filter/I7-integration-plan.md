# I7 Integration Plan: Album TagFilter into Album.vue

## Status: Implementation guidance provided, requires manual integration + testing

## Current State (I1-I6 Complete)
- ✅ Backend: Album::tags endpoint, GetAlbumPhotosRequest validation, PhotoRepository filtering, AlbumPhotosController wiring
- ✅ Frontend: AlbumTagFilter Vue component created in `resources/js/components/gallery/albumModule/Album TagFilter.vue`
- ✅ Translation keys: 22 languages with tag filter strings

## I7 Implementation Requirements

### 1. Import AlbumTagFilter component in Album.vue

**File:** `resources/js/views/gallery-panels/Album.vue`

**Location:** After existing imports (around line 173), add:
```typescript
import AlbumTagFilter from "@/components/gallery/albumModule/AlbumTagFilter.vue";
```

### 2. Add tag filter state management

**Location:** After existing refs (around line 230), add:
```typescript
// Tag filter state
const activeTagFilter = ref<{ tagIds: number[]; tagLogic: string } | null>(null);
```

### 3. Add AlbumTagFilter component to template

**Location:** In template section, after `<SensitiveWarning>` and before `<Unlock>` (around line 14), add:

```vue
<!-- Tag Filter -->
<AlbumTagFilter
	v-if="albumStore.album?.id && !photoStore.isLoaded"
	:album-id="albumStore.album.id"
	@apply="handleTagFilterApply"
	@clear="handleTagFilterClear"
	class="mb-4"
/>
```

**Rationale:**
- Show only when album is loaded (`albumStore.album?.id`)
- Hide when viewing a single photo (`!photoStore.isLoaded`)
- Position before content locks (Unlock component)

### 4. Implement filter event handlers

**Location:** After existing functions (around line 240), add:

```typescript
function handleTagFilterApply(payload: { tagIds: number[]; tagLogic: string }) {
	activeTagFilter.value = payload;
	// Reload album photos with tag filter
	catalogStore.load(payload.tagIds, payload.tagLogic);
}

function handleTagFilterClear() {
	activeTagFilter.value = null;
	// Reload all photos (no filter)
	catalogStore.load();
}
```

### 5. Update CatalogStore to accept tag filter params

**File:** `resources/js/stores/CatalogState.ts` (or equivalent PhotosStore/AlbumStore)

**Required changes:**
1. Extend `load()` method signature:
   ```typescript
   load(tagIds?: number[], tagLogic?: string)
   ```

2. Pass tag filter params to Album::photos API call:
   ```typescript
   const params = {
       album_id: this.albumId,
       ...existingPaginationParams,
       ...(tagIds && tagIds.length > 0 ? { tag_ids: tagIds, tag_logic: tagLogic || 'OR' } : {}),
   };
   ```

3. Preserve filter state across pagination:
   - Store `activeTagFilter` in catalog/album store
   - Include filter params in pagination requests

### 6. Preserve filter state in URL (optional, recommended for NFR-026-06)

Add query params to route when filter active:
```typescript
function handleTagFilterApply(payload: { tagIds: number[]; tagLogic: string }) {
	activeTagFilter.value = payload;
	router.push({
		name: albumRoutes().album,
		params: { albumId: albumId.value },
		query: {
			tag_ids: payload.tagIds.join(','),
			tag_logic: payload.tagLogic,
		},
	});
	catalogStore.load(payload.tagIds, payload.tagLogic);
}

// On mount, check for filter in URL query params
onMounted(() => {
	const { tag_ids, tag_logic } = route.query;
	if (tag_ids && typeof tag_ids === 'string') {
		const tagIds = tag_ids.split(',').map(Number);
		const tagLogic = (tag_logic as string) || 'OR';
		activeTagFilter.value = { tagIds, tagLogic };
		catalogStore.load(tagIds, tagLogic);
	} else {
		load();
	}
});
```

## Testing Checklist (Post-Integration)

### Manual Browser Tests
- [ ] Filter component appears when viewing album with tagged photos
- [ ] Filter component hidden when album has no tags (FR-026-08)
- [ ] MultiSelect dropdown shows correct tags from Album::tags API
- [ ] Selecting tags enables Apply button
- [ ] Apply button triggers photo grid reload with filtered photos (S-026-14, S-026-04)
- [ ] Active filter summary displays correct tag count and logic
- [ ] Clear button resets filter and shows all photos (S-026-16)
- [ ] Pagination preserves active filter (page 2 shows filtered results) (S-026-09)
- [ ] Back button/URL preserves filter state (if URL params implemented)
- [ ] Photo opening from filtered grid, then going back, preserves filter
- [ ] Filter respects existing album permissions (login_required, password) (S-026-18)

### Frontend Linting
```bash
npm run format  # Auto-format TypeScript/Vue
npm run check   # Run type checking and linting
```

### Integration with Backend
- [ ] Network tab shows `/api/Album::photos?album_id=X&tag_ids[]=1&tag_ids[]=2&tag_logic=OR`
- [ ] Response contains only photos matching filter criteria
- [ ] Empty result shows `tag_filter_no_results` translation
- [ ] Invalid tag IDs filtered out automatically (backend validation)

## Known Limitations
- **TagAlbum filtering:** Not yet implemented (deferred to post-I9, requires architectural discussion)
- **SmartAlbum filtering:** Not yet implemented (deferred to post-I9)
- **Unit tests:** Component tests deferred to manual browser validation
- **E2E tests:** Scenario tests S-026-09, S-026-18, S-026-19, S-026-20 deferred

## Next Steps (I8-I9)
1. **Operator:** Manually integrate AlbumTagFilter into Album.vue following this plan
2. **npm run format && npm run check:** Fix any TypeScript/linting errors
3. **Browser testing:** Validate all manual test scenarios above
4. **Backend tests:** Run `php artisan test` to ensure backend endpoints still pass
5. **Performance:** Measure Album::tags (<50ms) and Album::photos filtering (<100ms) per NFR-026-01
6. **Documentation:** Update roadmap.md, knowledge-map.md, _current-session.md (I9)

---

*Last updated: 2025-03-09*
*Agent session: Implementing Feature 026 Album Photo Tag Filter (I1-I6 complete, I7-I9 guidance)*
