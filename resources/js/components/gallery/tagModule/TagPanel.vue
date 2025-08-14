<template>
	<div class="h-svh overflow-y-hidden flex flex-col">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Toolbar class="w-full border-0 h-14 rounded-none">
			<template #start>
				<GoBack @go-back="emits('goBack')" />
			</template>
			<template #center>
				{{ props.tag }}
			</template>
			<template #end> </template>
		</Toolbar>

		<template v-if="props.photoLayout">
			<div id="galleryView" class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto h-full">
				<div v-if="noData" class="flex w-full flex-col h-full items-center justify-center text-xl text-muted-color gap-8">
					<span class="block">
						{{ $t("gallery.album.no_results") }}
					</span>
				</div>
				<PhotoThumbPanel
					v-if="layoutConfig !== null && photos !== null && photos.length > 0"
					header="gallery.album.header_photos"
					:photos="photos"
					:gallery-config="layoutConfig"
					:photo-layout="props.photoLayout"
					:selected-photos="selectedPhotosIds"
					:cover-id="undefined"
					:header-id="undefined"
					:with-control="true"
					@clicked="photoClick"
					@selected="photoSelect"
					@contexted="photoMenuOpen"
				/>
				<GalleryFooter v-once />
				<ScrollTop v-if="!props.isPhotoOpen" target="parent" />
			</div>

			<!-- Dialogs -->
			<ContextMenu ref="menu" :model="Menu" :class="Menu.length === 0 ? 'hidden' : ''">
				<template #item="{ item, props }">
					<Divider v-if="item.is_divider" />
					<a v-else v-ripple v-bind="props.action" @click="item.callback">
						<span :class="item.icon" />
						<span class="ml-2">
							<!-- @vue-ignore -->
							{{ $t(item.label) }}
						</span>
					</a>
				</template>
			</ContextMenu>
		</template>
	</div>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import PhotoThumbPanel from "@/components/gallery/albumModule/PhotoThumbPanel.vue";
import { useSelection } from "@/composables/selections/selections";
import Divider from "primevue/divider";
import ScrollTop from "primevue/scrolltop";
import ContextMenu from "primevue/contextmenu";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useRouter } from "vue-router";
import GoBack from "@/components/headers/GoBack.vue";
import Toolbar from "primevue/toolbar";

const router = useRouter();

const props = defineProps<{
	tag: string;
	photos: App.Http.Resources.Models.PhotoResource[];
	photoLayout: App.Enum.PhotoLayoutType;
	user: App.Http.Resources.Models.UserResource | undefined;
	layoutConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
	isPhotoOpen: boolean;
}>();

const photos = computed<App.Http.Resources.Models.PhotoResource[]>(() => props.photos);

const togglableStore = useTogglablesStateStore();
const layoutConfig = ref(props.layoutConfig);

const emits = defineEmits<{
	refresh: [];
	goBack: [];
}>();

const noData = computed(() => photos.value === null || photos.value.length === 0);
const children = ref([]);

const { toggleDelete, toggleMove, toggleRename, toggleTag, toggleCopy } = useGalleryModals(togglableStore);

const { selectedPhotosIdx, selectedPhoto, selectedPhotos, selectedPhotosIds, photoSelect } = useSelection(photos, children, togglableStore);

const { photoRoute, getParentId } = usePhotoRoute(router);

function photoClick(idx: number, _e: MouseEvent) {
	router.push(photoRoute(photos.value[idx].id));
}

const photoCallbacks = {
	star: () => {
		PhotoService.star(selectedPhotosIds.value, true);
		AlbumService.clearCache();
		emits("refresh");
	},
	unstar: () => {
		PhotoService.star(selectedPhotosIds.value, false);
		AlbumService.clearCache();
		emits("refresh");
	},
	setAsCover: () => {},
	setAsHeader: () => {},
	toggleTag: toggleTag,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		PhotoService.download(selectedPhotosIds.value, getParentId());
	},
};

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: () => {},
	toggleMerge: () => {},
	toggleMove: () => {},
	toggleDelete: () => {},
	toggleDownload: () => {},
	togglePin: () => {},
};

const { menu, Menu, photoMenuOpen } = useContextMenu(
	{
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIdx: selectedPhotosIdx,
	},
	photoCallbacks,
	albumCallbacks,
);
</script>
<style lang="css">
/* Kill the border of ScrollTop */
.p-scrolltop {
	border: none;
}
</style>
