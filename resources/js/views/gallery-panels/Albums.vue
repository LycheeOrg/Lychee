<template>
	<KeybindingsHelp v-model:visible="isKeybindingsHelpOpen" v-if="user?.id" />
	<div v-if="rootConfig && rootRights" @click="unselect">
		<AlbumsHeader
			v-model:is-login-open="isLoginOpen"
			v-if="user"
			:user="user"
			title="lychee.ALBUMS"
			:rights="rootRights"
			@refresh="refresh"
			@help="isKeybindingsHelpOpen = true"
			:config="rootConfig"
		/>
		<AlbumThumbPanel
			v-if="smartAlbums.length > 0"
			header="lychee.SMART_ALBUMS"
			:album="undefined"
			:albums="smartAlbums"
			:user="user"
			:config="albumPanelConfig"
			:is-alone="!albums.length"
			:are-nsfw-visible="false"
			:idx-shift="-1"
			:selected-albums="[]"
		/>
		<AlbumThumbPanel
			v-if="albums.length > 0"
			header="lychee.ALBUMS"
			:album="null"
			:albums="albums"
			:user="user"
			:config="albumPanelConfig"
			:is-alone="!sharedAlbums.length"
			:are-nsfw-visible="are_nsfw_visible"
			:idx-shift="0"
			:selected-albums="selectedAlbumsIds"
			@clicked="albumClick"
			@contexted="albumMenuOpen"
		/>
		<AlbumThumbPanel
			v-if="sharedAlbums.length > 0"
			header="lychee.SHARED_ALBUMS"
			:album="undefined"
			:albums="sharedAlbums"
			:user="user"
			:config="albumPanelConfig"
			:is-alone="!albums.length"
			:are-nsfw-visible="are_nsfw_visible"
			:idx-shift="albums.length"
			:selected-albums="selectedAlbumsIds"
			@clicked="albumClick"
			@contexted="albumMenuOpen"
		/>
	</div>
	<ContextMenu ref="menu" :model="Menu">
		<template #item="{ item, props }">
			<Divider v-if="item.is_divider" />
			<a v-else v-ripple v-bind="props.action" @click="item.callback">
				<span :class="item.icon" />
				<span class="ml-2">{{ $t(item.label) }}</span>
			</a>
		</template>
	</ContextMenu>
	<!-- Dialogs for albums -->
	<MoveDialog v-model:visible="isMoveVisible" :parent-id="undefined" :album="selectedAlbum" :album-ids="selectedAlbumsIds" @moved="refresh" />
	<AlbumMergeDialog
		v-model:visible="isMergeAlbumVisible"
		:parent-id="undefined"
		:album="selectedAlbum"
		:album-ids="selectedAlbumsIds"
		@merged="refresh"
	/>
	<DeleteDialog v-model:visible="isDeleteVisible" :parent-id="undefined" :album="selectedAlbum" :album-ids="selectedAlbumsIds" @deleted="refresh" />
	<RenameDialog v-if="selectedAlbum" v-model:visible="isRenameVisible" :parent-id="undefined" :album="selectedAlbum" @renamed="refresh" />
</template>
<script setup lang="ts">
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import { useAuthStore } from "@/stores/Auth";
import { computed, ref } from "vue";
import AlbumsHeader from "@/components/headers/AlbumsHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import KeybindingsHelp from "@/components/modals/KeybindingsHelp.vue";
import { useSelection } from "@/composables/selections/selections";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import ContextMenu from "primevue/contextmenu";
import { useAlbumsRefresher } from "@/composables/album/albumsRefresher";
import { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";

const isLoginOpen = ref(false);

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

const config = ref(null); // unused for now.
const photos = ref([]); // unused.

const { user, isKeybindingsHelpOpen, smartAlbums, albums, sharedAlbums, rootConfig, rootRights, selectableAlbums, refresh } = useAlbumsRefresher(
	auth,
	lycheeStore,
	isLoginOpen,
);

const { selectedAlbum, selectedAlbumsIdx, selectedAlbums, selectedAlbumsIds, albumClick } = useSelection(config, undefined, photos, selectableAlbums);

// Modals for Albums
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
} = useGalleryModals();

const unselect = () => {
	selectedAlbumsIdx.value = [];
};

// Unused.
const photoCallbacks = {
	star: () => {},
	unstar: () => {},
	setAsCover: () => {},
	setAsHeader: () => {},
	toggleTag: () => {},
	toggleRename: () => {},
	toggleCopyTo: () => {},
	toggleMove: () => {},
	toggleDelete: () => {},
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

const { menu, Menu, albumMenuOpen } = useContextMenu(
	{
		config: null,
		album: null,
		selectedPhoto: undefined,
		selectedPhotos: undefined,
		selectedPhotosIdx: undefined,
		selectedAlbum: selectedAlbum,
		selectedAlbums: selectedAlbums,
		selectedAlbumIdx: selectedAlbumsIdx,
	},
	photoCallbacks,
	albumCallbacks,
);

const albumPanelConfig = computed<AlbumThumbConfig>(() => ({
	album_thumb_css_aspect_ratio: rootConfig.value?.album_thumb_css_aspect_ratio ?? "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

refresh();

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
</script>
