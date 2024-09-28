<template>
	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<AlbumHeader v-if="album && config && user" :album="album" :config="config" :user="user" @refresh="refresh" />
		</Collapse>
		<template v-if="config && album">
			<div
				class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto"
				:class="is_full_screen ? 'h-svh' : 'h-[calc(100vh-3.5rem)]'"
			>
				<AlbumEdit v-if="album.rights.can_edit" :album="album" :config="config" />
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
					:config="albumPanelConfig"
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
					:gallery-config="layout"
					:selected-photos="selectedPhotosIds"
					@clicked="photoClick"
					@contexted="photoMenuOpen"
				/>
			</div>
			<ShareAlbum v-model:visible="isShareAlbumVisible" :title="album.title" :url="route.path" />
			<!-- Dialogs -->
			<PhotoTagDialog
				v-model:visible="isTagVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				@tagged="refresh"
			/>
			<PhotoCopyDialog
				v-model:visible="isCopyVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				@copied="refresh"
			/>
			<MoveDialog
				v-model:visible="isMoveVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@moved="refresh"
			/>
			<DeleteDialog
				v-model:visible="isDeleteVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@deleted="refresh"
			/>

			<!-- Dialogs for albums -->
			<RenameDialog v-model:visible="isRenameVisible" :parent-id="undefined" :album="selectedAlbum" :photo="selectedPhoto" @renamed="refresh" />
			<AlbumMergeDialog
				v-model:visible="isMergeAlbumVisible"
				:parent-id="albumid"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@merged="refresh"
			/>

			<ContextMenu ref="menu" :model="Menu" :class="Menu.length === 0 ? 'hidden' : ''">
				<template #item="{ item, props }">
					<Divider v-if="item.is_divider" />
					<a v-else v-ripple v-bind="props.action" @click="item.callback">
						<span :class="item.icon" />
						<span class="ml-2">{{ $t(item.label) }}</span>
					</a>
				</template>
			</ContextMenu>
		</template>
	</div>
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
import { useSelection } from "@/composables/selections/selections";
import Divider from "primevue/divider";
import ContextMenu from "primevue/contextmenu";
import { useAlbumRefresher } from "@/composables/album/albumRefresher";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import { Collapse } from "vue-collapsed";

const route = useRoute();

const props = defineProps<{
	albumid: string;
}>();

const albumid = ref(props.albumid);

// flag to open login modal if necessary
const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
lycheeStore.resetSearch();

const { are_nsfw_visible, is_full_screen, is_login_open } = storeToRefs(lycheeStore);

// Set up Album ID reference. This one is updated at each page change.
const { user, modelAlbum, album, layout, photos, config, loadLayout, refresh } = useAlbumRefresher(albumid, auth, is_login_open);

watch(
	() => route.params.albumid,
	(newId, _oldId) => {
		albumid.value = newId as string;
		refresh();
	},
);

const children = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(() => modelAlbum.value?.albums ?? []);
const noData = computed(() => children.value.length === 0 && (photos.value === null || photos.value.length === 0));

const {
	isDeleteVisible,
	toggleDelete,
	isMergeAlbumVisible,
	toggleMergeAlbum,
	isMoveVisible,
	toggleMove,
	isRenameVisible,
	toggleRename,
	isShareAlbumVisible,
	toggleShareAlbum,
	isTagVisible,
	toggleTag,
	isCopyVisible,
	toggleCopy,
} = useGalleryModals();

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
	toggleTag: toggleTag,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {},
};

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: toggleRename,
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
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

const albumPanelConfig = computed<AlbumThumbConfig>(() => ({
	album_thumb_css_aspect_ratio: config.value?.album_thumb_css_aspect_ratio ?? "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

loadLayout();

refresh();

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && lycheeStore.toggleFullScreen());
</script>
