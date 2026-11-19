<template>
	<UContextMenu :items="menuSections" :disabled="!hasResults" class="contents">
		<!-- Struct-of-Arrays results (Feature 069). No pagination: the photo
		     tier is whole-scope, virtualized, and bounded by the
		     `search_result_limit` cap rather than by a page size. -->
		<div class="contents">
			<SearchAlbumGridV3
				:header="albumHeaderV3"
				:selected-albums="selectedAlbumsIds"
				@clicked="albumSelect"
				@selected="albumSelect"
				@contexted="contextMenuAlbumOpen"
			/>
			<template v-if="photosStore.photos.length > 0">
				<h2 class="w-full px-3 py-2 font-semibold text-toned text-lg">{{ photoHeader }}</h2>
				<UAlert
					v-if="searchStore.isTruncatedV3"
					class="mx-3 mb-2"
					color="warning"
					variant="subtle"
					icon="lucide:triangle-alert"
					:description="truncationHint"
				/>
				<PhotoGridVirtual
					source="search"
					:selected-photos="selectedPhotosIds"
					@clicked="photoClick"
					@selected="selectPhoto"
					@contexted="contextMenuPhotoOpen"
				/>
			</template>
		</div>
	</UContextMenu>
</template>
<script setup lang="ts">
import { useContextMenu, type PhotoCallbacks, type AlbumCallbacks, type Selectors } from "@/v8/composables/contextMenus/contextMenu";
import { useSelection } from "@/composables/selections/selections";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import PhotoGridVirtual from "@/v8/components/gallery/albumModule/Virtualized/PhotoGridVirtual.vue";
import SearchAlbumGridV3 from "@/v8/components/gallery/searchModule/SearchAlbumGridV3.vue";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { sprintf } from "@/utils/sprintf";
import { trans } from "laravel-vue-i18n";
import { computed } from "vue";
import { useSearchStore } from "@/stores/SearchState";
import type { ContextMenuItem } from "@nuxt/ui";

const togglableStore = useTogglablesStateStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();
const searchStore = useSearchStore();
const router = useRouter();

const props = defineProps<{
	// for menu
	photoCallbacks: PhotoCallbacks;
	albumCallbacks: AlbumCallbacks;
	selectors: Selectors;
}>();

const photoHeader = computed(() => {
	return sprintf(trans("gallery.search.photos"), searchStore.total);
});

const albumHeaderV3 = computed(() => {
	return sprintf(trans("gallery.search.albums"), searchStore.albumTilesV3.length);
});

const truncationHint = computed(() => {
	return sprintf(trans("gallery.search.results_truncated"), searchStore.total);
});

const { photoRoute } = usePhotoRoute(router);

function photoClick(photoId: string, _e: MouseEvent) {
	router.push(photoRoute(photoId));
}

// The album hits never reach `albumsStore` (FR-069-20), so both
// the selection pool and the "is there anything to act on" gate above have to
// read them from the search store instead.
const searchAlbumsV3 = computed<App.Http.Resources.Models.ThumbAlbumResource[] | undefined>(() => searchStore.albumTilesV3);

const hasResults = computed<boolean>(() => searchStore.albumTilesV3.length > 0 || photosStore.photos.length > 0);

const {
	selectedPhotosIds,
	selectedAlbumsIds,
	photoSelect: selectPhoto,
	albumSelect,
} = useSelection(photosStore, albumsStore, togglableStore, searchAlbumsV3);

const { Menu } = useContextMenu(props.selectors, props.photoCallbacks, props.albumCallbacks);

// See AlbumPanel.vue for why the composable's imperative photoMenuOpen/albumMenuOpen are
// bypassed in favor of a declarative UContextMenu wrapping the gallery view, and why
// `:disabled` above must not depend on `Menu.length` (that races against the selection
// side-effect this handler performs on the very same contextmenu event).
function contextMenuPhotoOpen(photoId: string, _e: MouseEvent): void {
	selectedAlbumsIds.value = [];
	if (!selectedPhotosIds.value.includes(photoId)) {
		selectedPhotosIds.value = [photoId];
	}
}

function contextMenuAlbumOpen(_e: MouseEvent, albumId: string): void {
	selectedPhotosIds.value = [];
	if (!selectedAlbumsIds.value.includes(albumId)) {
		selectedAlbumsIds.value = [albumId];
	}
}

const menuSections = computed<ContextMenuItem[][]>(() => {
	const sections: ContextMenuItem[][] = [[]];
	for (const entry of Menu.value) {
		if (entry.is_divider) {
			sections.push([]);
			continue;
		}
		sections[sections.length - 1].push({
			label: trans(entry.label ?? ""),
			icon: entry.icon,
			onSelect: entry.callback,
		});
	}
	return sections.filter((s) => s.length > 0);
});
</script>
