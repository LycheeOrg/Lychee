<template>
	<AlbumHeader
		v-if="album && config && user"
		:album="album"
		:config="config"
		:user="user"
		v-model:are-details-open="areDetailsOpen"
		@refresh="refresh"
	/>
	<template v-if="config && album">
		<div class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto h-[calc(100vh-66px)]">
			<AlbumEdit v-model="areDetailsOpen" v-if="album.rights.can_edit" :album="album" :config="config" />
			<div v-if="noData" class="flex w-full h-full items-center justify-center text-xl text-muted-color">
				<span class="block">
					{{ "Nothing to see here" }}
				</span>
			</div>
			<AlbumHero v-if="!noData" :album="album" @open-sharing-modal="toggleShareAlbum" />
			<AlbumThumbPanel
				v-if="children !== null && children.length > 0"
				header="lychee.ALBUMS"
				:album="modelAlbum"
				:albums="children"
				:config="config"
				:is-alone="!photos?.length"
				:are-nsfw-visible="are_nsfw_visible"
				@clicked="albumClick"
				@contexted="albumMenuOpen"
				:idx-shift="0"
				:selected-albums="selectedAlbumsIds"
			/>
			<PhotoThumbPanel
				v-if="layout !== null && photos !== null && photos.length > 0"
				header="lychee.PHOTOS"
				:photos="photos"
				:album="album"
				:config="config"
				:gallery-config="layout"
				:selected-photos="selectedPhotosIds"
				@clicked="photoClick"
				@contexted="photoMenuOpen"
			/>
		</div>
		<ShareAlbum v-model:visible="isShareAlbumVisible" :title="album.title" :url="route.path" />
		<!-- Dialogs for photos -->
		<DialogPhotoMove
			v-model:visible="isMovePhotoVisible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album-id="albumid"
			@moved="refresh"
		/>
		<DialogPhotoDelete
			v-model:visible="isDeletePhotoVisible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album-id="albumid"
			@deleted="refresh"
		/>

		<!-- Dialogs for albums -->
		<AlbumMoveDialog
			v-model:visible="isMoveAlbumVisible"
			:parent-id="albumid"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@moved="refresh"
		/>
		<AlbumMergeDialog
			v-model:visible="isMergeAlbumVisible"
			:parent-id="albumid"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@merged="refresh"
		/>
		<AlbumDeleteDialog
			v-model:visible="isDeleteAlbumVisible"
			:parent-id="albumid"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@deleted="refresh"
		/>

		<ContextMenu ref="menu" :model="Menu">
			<template #item="{ item, props }">
				<Divider v-if="item.is_divider" />
				<a v-else v-ripple v-bind="props.action" @click="item.callback">
					<span :class="item.icon" />
					<span class="ml-2">{{ $t(item.label) }}</span>
				</a>
			</template>
		</ContextMenu>
	</template>
</template>
<script setup lang="ts">
import { useAuthStore } from "@/stores/Auth";
import { computed, ref, watch } from "vue";
import { useRoute } from "vue-router";
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import PhotoThumbPanel from "@/components/gallery/PhotoThumbPanel.vue";
import ShareAlbum from "@/components/modals/ShareAlbum.vue";
import AlbumHero from "@/components/gallery/AlbumHero.vue";
import AlbumEdit from "@/components/drawers/AlbumEdit.vue";
import AlbumHeader from "@/components/headers/AlbumHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import DialogPhotoMove from "@/components/forms/photo/DialogPhotoMove.vue";
import DialogPhotoDelete from "@/components/forms/photo/DialogPhotoDelete.vue";
import { useMovePhotoOpen } from "@/composables/modalsTriggers/movePhotoOpen";
import { useDeletePhotoOpen } from "@/composables/modalsTriggers/deletePhotoOpen";
import { useSelection } from "@/composables/selections/selections";
// import PhotoService from "@/services/photo-service";
import { useShareAlbumOpen } from "@/composables/modalsTriggers/shareAlbumOpen";
import Divider from "primevue/divider";
import ContextMenu from "primevue/contextmenu";
import { useAlbumRefresher } from "@/composables/album/albumRefresher";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { useDeleteAlbumOpen } from "@/composables/modalsTriggers/deleteAlbumOpen";
import AlbumDeleteDialog from "@/components/forms/album/AlbumDeleteDialog.vue";
import { useMoveAlbumOpen } from "@/composables/modalsTriggers/moveAlbumOpen";
import AlbumMoveDialog from "@/components/forms/album/AlbumMoveDialog.vue";
import AlbumMergeDialog from "@/components/forms/album/AlbumMergeDialog.vue";
import { useMergeAlbumOpen } from "@/composables/modalsTriggers/mergeAlbumOpen";

const route = useRoute();

const props = defineProps<{
	albumid: string;
}>();

const albumid = ref(props.albumid);

// Sharing stuff
const { isShareAlbumVisible, toggleShareAlbum } = useShareAlbumOpen();

// binding between hero and header. We use a boolean instead of events to avoid de-sync
const areDetailsOpen = ref(false);
// flag to open login modal if necessary
const isLoginOpen = ref(false);

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

// Set up Album ID reference. This one is updated at each page change.
const { user, modelAlbum, tagAlbum, smartAlbum, album, layout, photos, config, loadUser, loadAlbum, loadLayout, refresh } = useAlbumRefresher(
	albumid,
	auth,
	isLoginOpen,
);

watch(
	() => route.params.albumid,
	(newId, _oldId) => {
		albumid.value = newId as string;
		refresh();
	},
);

const children = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(() => modelAlbum.value?.albums ?? []);
const noData = computed(() => children.value.length === 0 && (photos.value === null || photos.value.length === 0));

// Modals for Photos
const { isMovePhotoVisible, toggleMovePhoto } = useMovePhotoOpen();
const { isDeletePhotoVisible, toggleDeletePhoto } = useDeletePhotoOpen();

// Modals for Albums
const { isMoveAlbumVisible, toggleMoveAlbum } = useMoveAlbumOpen();
const { isMergeAlbumVisible, toggleMergeAlbum } = useMergeAlbumOpen();
const { isDeleteAlbumVisible, toggleDeleteAlbum } = useDeleteAlbumOpen();

const {
	selectedPhotosIdx,
	selectedAlbumsIdx,
	selectedPhoto,
	selectedAlbum,
	selectedPhotos,
	selectedAlbums,
	selectedPhotosIds,
	selectedAlbumsIds,
	photoClick,
	albumClick,
} = useSelection(config, album, photos, children);

const photoCallbacks = {
	star: () => {
		PhotoService.star(selectedPhotosIds.value, true);
		AlbumService.clearCache(albumid.value);
		refresh();
	},
	unstar: () => {
		PhotoService.star(selectedPhotosIds.value, false);
		AlbumService.clearCache(albumid.value);
		refresh();
	},
	setAsCover: () => {
		PhotoService.setAsCover(selectedPhoto.value!.id, albumid.value);
		AlbumService.clearCache(albumid.value);
		refresh();
	},
	setAsHeader: () => {
		PhotoService.setAsHeader(selectedPhoto.value!.id, albumid.value, false);
		AlbumService.clearCache(albumid.value);
		refresh();
	},
	toggleTag: () => {},
	toggleRename: () => {},
	toggleCopyTo: () => {},
	toggleMove: toggleMovePhoto,
	toggleDelete: toggleDeletePhoto,
	toggleDownload: () => {},
};

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: () => {},
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMoveAlbum,
	toggleDelete: toggleDeleteAlbum,
	toggleDownload: () => {},
};

const { menu, Menu, photoMenuOpen, albumMenuOpen } = useContextMenu(
	{
		config: config,
		album: album,
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIdx: selectedPhotosIdx,
		selectedAlbum: selectedAlbum,
		selectedAlbums: selectedAlbums,
		selectedAlbumIdx: selectedAlbumsIdx,
	},
	photoCallbacks,
	albumCallbacks,
);

// const { photomenu, PhotoMenu } = useContextMenuPhoto(
// 	{
// 		getAlbumConfig,
// 		getAlbum,
// 		getSelectedPhotos,
// 	},
// 	{
// 		star: () => PhotoService.star(getSelectedPhotosIds(), true),
// 		unstar: () => PhotoService.star(getSelectedPhotosIds(), false),
// 		setAsCover: () => PhotoService.setAsCover(getSelectedPhotos()[0].id, album.value?.id as string),
// 		setAsHeader: () => PhotoService.setAsHeader(getSelectedPhotos()[0].id, album.value?.id as string, true),
// 		toggleTag: () => {},
// 		toggleRename: () => {},
// 		toggleCopyTo: () => {},
// 		toggleMove: toggleMovePhoto,
// 		toggleDelete: toggleDeletePhoto,
// 		toggleDownload: () => {},
// 	},
// );

loadLayout();

refresh();

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
</script>
