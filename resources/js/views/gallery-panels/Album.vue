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
			/>
			<!-- @clicked="maySelect"
			@contexted="menuOpen" -->
		</div>
		<ShareAlbum v-model:visible="isShareAlbumVisible" :title="album.title" :url="route.path" />
		<DialogPhotoMove v-model:visible="isMovePhotoVisible" :photo="selectedPhoto" :photo-ids="selectedPhotosIds" />
		<DialogPhotoDelete v-model:visible="isDeletePhotoVisible" :photo="selectedPhoto" :photo-ids="selectedPhotosIds" />
		<!-- <ContextMenu ref="menu" :model="Menu">
			<template #item="{ item, props }">
				<Divider v-if="item.is_divider" />
				<a v-else v-ripple v-bind="props.action" @click="item.callback">
					<span :class="item.icon" />
					<span class="ml-2">{{ $t(item.label) }}</span>
				</a>
			</template>
		</ContextMenu> -->
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
import { useContextMenuPhoto } from "@/composables/contextMenus/contextMenuPhoto";
import PhotoService from "@/services/photo-service";
import { useShareAlbumOpen } from "@/composables/modalsTriggers/shareAlbumOpen";
import Divider from "primevue/divider";
import ContextMenu from "primevue/contextmenu";
import { useAlbumRefresher } from "@/composables/album/albumRefresher";

const route = useRoute();

const props = defineProps<{
	albumid: string;
}>();

const albumid = ref(props.albumid);

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

const { isMovePhotoVisible, toggleMovePhoto } = useMovePhotoOpen();
const { isDeletePhotoVisible, toggleDeletePhoto } = useDeletePhotoOpen();
const { isShareAlbumVisible, toggleShareAlbum } = useShareAlbumOpen();

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

// const { getAlbum, getAlbumConfig, selectedPhotos, isPhotoSelected, getSelectedPhotos, getSelectedPhotosIds, addToPhotoSelection, maySelect } =
// 	usePhotosSelection({
// 		config
// 	});

// const photo = computed(() => (getSelectedPhotos().length === 1 ? getSelectedPhotos()[0] : undefined));

// function menuOpen(idx: number, e: MouseEvent): void {
// 	if (!isPhotoSelected(idx)) {
// 		selectedPhotos.value = [];
// 		addToPhotoSelection(idx);
// 	}
// 	photomenu.value.show(e);
// }

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
// loadUser();

refresh();

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
</script>
