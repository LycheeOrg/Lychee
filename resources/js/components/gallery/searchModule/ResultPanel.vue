<template>
	<AlbumThumbPanel
		v-if="props.albums.length > 0"
		:header="props.albumHeader"
		:album="null"
		:albums="props.albums"
		:config="albumPanelConfig"
		:is-alone="false"
		:are-nsfw-visible="are_nsfw_visible"
		:idx-shift="0"
		:selected-albums="selectedAlbumsIds"
		:is-timeline="false"
		@clicked="albumClick"
		@contexted="albumMenuOpen"
	/>
	<div v-if="photos.length > 0" class="flex justify-center w-full">
		<Paginator v-model:first="first" :total-records="total" :rows="rows" :always-show="false" @update:first="emits('refresh')" />
	</div>
	<PhotoThumbPanel
		v-if="photos.length > 0"
		:photo-layout="props.layout"
		:header="props.photoHeader"
		:photos="props.photos"
		:photos-timeline="undefined"
		:cover-id="undefined"
		:header-id="undefined"
		:gallery-config="props.layoutConfig"
		:selected-photos="selectedPhotosIds"
		:is-timeline="false"
		:with-control="true"
		@clicked="photoClick"
		@selected="photoSelect"
		@contexted="photoMenuOpen"
	/>
	<div v-if="photos.length > 0" class="flex justify-center w-full">
		<Paginator v-model:first="first" :total-records="total" :rows="rows" :always-show="false" @update:first="emits('refresh')" />
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
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { ref } from "vue";
import PhotoThumbPanel from "@/components/gallery/albumModule/PhotoThumbPanel.vue";
import AlbumThumbPanel from "@/components/gallery/albumModule/AlbumThumbPanel.vue";
import { AlbumThumbConfig } from "@/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import ContextMenu from "primevue/contextmenu";
import Divider from "primevue/divider";
import Paginator from "primevue/paginator";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);
const togglableStore = useTogglablesStateStore();
const router = useRouter();

const props = defineProps<{
	// results
	albums: App.Http.Resources.Models.ThumbAlbumResource[];
	photos: App.Http.Resources.Models.PhotoResource[];
	total: number;
	// headers
	albumHeader: string;
	photoHeader: string;
	// config
	layout: App.Enum.PhotoLayoutType;
	layoutConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
	albumPanelConfig: AlbumThumbConfig;
	isPhotoTimelineEnabled: boolean;
	// for menu
	photoCallbacks: PhotoCallbacks;
	albumCallbacks: AlbumCallbacks;
	selectors: Selectors;
}>();

const first = defineModel<number | undefined>("first");
const rows = defineModel<number | undefined>("rows");

const emits = defineEmits<{
	refresh: [];
}>();

const { photoRoute } = usePhotoRoute(router);

function photoClick(idx: number, _e: MouseEvent) {
	router.push(photoRoute(photos.value[idx].id));
}

const photos = ref(props.photos);
const children = ref(props.albums);

const { selectedPhotosIds, selectedAlbumsIds, photoSelect, albumClick } = useSelection(photos, children, togglableStore);

const { menu, Menu, photoMenuOpen, albumMenuOpen } = useContextMenu(props.selectors, props.photoCallbacks, props.albumCallbacks);
</script>
