<template>
	<AlbumThumbPanel
		v-if="albumsStore.albums.length > 0"
		:header="albumHeader"
		:albums="albumsStore.albums"
		:is-alone="false"
		:idx-shift="0"
		:selected-albums="selectedAlbumsIds"
		:is-timeline="false"
		@clicked="albumClick"
		@contexted="albumMenuOpen"
	/>
	<div v-if="photosStore.photos.length > 0" class="flex justify-center w-full">
		<Paginator v-model:first="first" :total-records="searchStore.total" :rows="rows" :always-show="false" @update:first="emits('refresh')" />
	</div>
	<PhotoThumbPanel
		v-if="photosStore.photos.length > 0"
		:header="photoHeader"
		:photos="photosStore.photos"
		:photos-timeline="undefined"
		:selected-photos="selectedPhotosIds"
		:is-timeline="false"
		:with-control="true"
		@clicked="photoClick"
		@selected="photoSelect"
		@contexted="photoMenuOpen"
	/>
	<div v-if="photosStore.photos.length > 0" class="flex justify-center w-full">
		<Paginator v-model:first="first" :total-records="searchStore.total" :rows="rows" :always-show="false" @update:first="emits('refresh')" />
	</div>

	<ContextMenu ref="menu" :model="Menu" :class="Menu.length === 0 ? 'hidden' : ''">
		<template #item="{ item, props }">
			<Divider v-if="item.is_divider" />
			<a v-else v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ltr:ml-2 rtl:mr-2">
					<!-- @vue-ignore -->
					{{ $t(item.label) }}
				</span>
			</a>
		</template>
	</ContextMenu>
</template>
<script setup lang="ts">
import { useContextMenu, type PhotoCallbacks, type AlbumCallbacks, type Selectors } from "@/composables/contextMenus/contextMenu";
import { useSelection } from "@/composables/selections/selections";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import PhotoThumbPanel from "@/components/gallery/albumModule/PhotoThumbPanel.vue";
import AlbumThumbPanel from "@/components/gallery/albumModule/AlbumThumbPanel.vue";
import { AlbumThumbConfig } from "@/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import ContextMenu from "primevue/contextmenu";
import Divider from "primevue/divider";
import Paginator from "primevue/paginator";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";
import { computed } from "vue";
import { useSearchStore } from "@/stores/SearchState";

const togglableStore = useTogglablesStateStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();
const searchStore = useSearchStore();
const router = useRouter();

const props = defineProps<{
	// config
	albumPanelConfig: AlbumThumbConfig;
	isPhotoTimelineEnabled: boolean;
	// for menu
	photoCallbacks: PhotoCallbacks;
	albumCallbacks: AlbumCallbacks;
	selectors: Selectors;
}>();

const photoHeader = computed(() => {
	return sprintf(trans("gallery.search.photos"), searchStore.total);
});

const albumHeader = computed(() => {
	if (albumsStore.albums.length === 0) {
		return "";
	}
	return sprintf(trans("gallery.search.albums"), albumsStore.albums.length);
});

const first = defineModel<number | undefined>("first");
const rows = defineModel<number | undefined>("rows");

const emits = defineEmits<{
	refresh: [];
}>();

const { photoRoute } = usePhotoRoute(router);

function photoClick(idx: number, _e: MouseEvent) {
	router.push(photoRoute(photosStore.photos[idx].id));
}

const { selectedPhotosIds, selectedAlbumsIds, photoSelect, albumClick } = useSelection(photosStore, albumsStore, togglableStore);

const { menu, Menu, photoMenuOpen, albumMenuOpen } = useContextMenu(props.selectors, props.photoCallbacks, props.albumCallbacks);
</script>
