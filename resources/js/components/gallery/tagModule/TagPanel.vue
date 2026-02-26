<template>
	<div class="h-svh overflow-y-hidden flex flex-col">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Toolbar class="w-full border-0 h-14 rounded-none" v-if="tagStore.tag">
			<template #start>
				<GoBack @go-back="emits('goBack')" />
			</template>
			<template #center>
				{{ tagStore.tag.name }}
			</template>
			<template #end> </template>
		</Toolbar>

		<div id="galleryView" class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto h-full">
			<div
				v-if="photosStore.photos.length === 0"
				class="flex w-full flex-col h-full items-center justify-center text-xl text-muted-color gap-8"
			>
				<span class="block">
					{{ $t("gallery.album.no_results") }}
				</span>
			</div>
			<PhotoThumbPanel
				v-else
				header="gallery.album.header_photos"
				:photos="photosStore.photos"
				:selected-photos="selectedPhotosIds"
				:with-control="true"
				@clicked="photoClick"
				@selected="selectPhoto"
				@contexted="contextMenuPhotoOpen"
			/>
			<GalleryFooter v-once />
			<ScrollTop v-if="!photoStore.isLoaded" target="parent" />
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
	</div>
</template>
<script setup lang="ts">
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
import { usePhotosStore } from "@/stores/PhotosState";
import { useTagStore } from "@/stores/TagState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { usePhotoStore } from "@/stores/PhotoState";

const router = useRouter();
const togglableStore = useTogglablesStateStore();
const photosStore = usePhotosStore();
const tagStore = useTagStore();
const albumsStore = useAlbumsStore();
const photoStore = usePhotoStore();

const emits = defineEmits<{
	refresh: [];
	goBack: [];
}>();

const { toggleDelete, toggleMove, toggleRename, toggleTag, toggleLicense, toggleCopy, toggleApplyRenamer } = useGalleryModals(togglableStore);

const { selectedPhoto, selectedPhotos, selectedPhotosIds, photoSelect: selectPhoto } = useSelection(photosStore, albumsStore, togglableStore);

const { photoRoute, getParentId } = usePhotoRoute(router);

function photoClick(photoId: string, _e: MouseEvent) {
	router.push(photoRoute(photoId));
}

const photoCallbacks = {
	star: () => {
		PhotoService.highlight(selectedPhotosIds.value, true);
		AlbumService.clearCache();
		emits("refresh");
	},
	unstar: () => {
		PhotoService.highlight(selectedPhotosIds.value, false);
		AlbumService.clearCache();
		emits("refresh");
	},
	setAsCover: () => {},
	setAsHeader: () => {},
	toggleTag: toggleTag,
	toggleLicense: toggleLicense,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		PhotoService.download(selectedPhotosIds.value, getParentId());
	},
	toggleApplyRenamer: toggleApplyRenamer,
};

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: () => {},
	toggleMerge: () => {},
	toggleMove: () => {},
	toggleDelete: () => {},
	toggleDownload: () => {},
	togglePin: () => {},
	toggleApplyRenamer: () => {},
};

const {
	menu,
	Menu,
	photoMenuOpen: contextMenuPhotoOpen,
} = useContextMenu(
	{
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIds: selectedPhotosIds,
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
