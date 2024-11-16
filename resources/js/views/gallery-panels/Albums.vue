<template>
	<KeybindingsHelp v-model:visible="isKeybindingsHelpOpen" v-if="user?.id" />
	<div v-if="rootConfig && rootRights" @click="unselect" class="h-svh overflow-y-auto">
		<Collapse :when="!is_full_screen">
			<AlbumsHeader
				v-if="user"
				:user="user"
				:title="title"
				:rights="rootRights"
				@refresh="refresh"
				@help="isKeybindingsHelpOpen = true"
				:config="rootConfig"
			/>
		</Collapse>
		<AlbumThumbPanel
			v-if="smartAlbums.length > 0"
			header="lychee.SMART_ALBUMS"
			:album="undefined"
			:albums="smartAlbums"
			:user="user"
			:config="albumPanelConfig"
			:is-alone="!albums.length"
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
			:is-alone="!sharedAlbums.length && !smartAlbums.length"
			:idx-shift="0"
			:selected-albums="selectedAlbumsIds"
			@clicked="albumClick"
			@contexted="albumMenuOpen"
		/>
		<template v-for="sharedAlbum in sharedAlbums">
			<AlbumThumbPanel
				v-if="sharedAlbums.length > 0"
				:header="sharedAlbum.header"
				:album="undefined"
				:albums="sharedAlbum.data"
				:user="user"
				:config="albumPanelConfig"
				:is-alone="!albums.length"
				:idx-shift="sharedAlbum.iter"
				:selected-albums="selectedAlbumsIds"
				@clicked="albumClick"
				@contexted="albumMenuOpen"
			/>
		</template>
		<GalleryFooter v-once />
	</div>
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
	<!-- Dialogs for albums -->
	<MoveDialog
		v-model:visible="isMoveVisible"
		:parent-id="undefined"
		:album="selectedAlbum"
		:album-ids="selectedAlbumsIds"
		@moved="
			() => {
				unselect();
				refresh();
			}
		"
	/>
	<AlbumMergeDialog
		v-model:visible="isMergeAlbumVisible"
		:parent-id="undefined"
		:album="selectedAlbum"
		:album-ids="selectedAlbumsIds"
		@merged="
			() => {
				unselect();
				refresh();
			}
		"
	/>
	<DeleteDialog
		v-model:visible="isDeleteVisible"
		:parent-id="undefined"
		:album="selectedAlbum"
		:album-ids="selectedAlbumsIds"
		@deleted="
			() => {
				unselect();
				refresh();
			}
		"
	/>
	<RenameDialog
		v-if="selectedAlbum"
		v-model:visible="isRenameVisible"
		:parent-id="undefined"
		:album="selectedAlbum"
		@renamed="
			() => {
				unselect();
				refresh();
			}
		"
	/>
</template>
<script setup lang="ts">
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import { useAuthStore } from "@/stores/Auth";
import { computed, ref } from "vue";
import AlbumsHeader from "@/components/headers/AlbumsHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { onKeyStroke } from "@vueuse/core";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
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
import Divider from "primevue/divider";
import { Collapse } from "vue-collapsed";
import AlbumService from "@/services/album-service";
import { useRouter } from "vue-router";
import { useMouseEvents } from "@/composables/album/uploadEvents";
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";

const auth = useAuthStore();
const router = useRouter();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();

lycheeStore.init();
togglableStore.resetSearch();

const { is_full_screen, is_login_open, is_upload_visible, list_upload_files } = storeToRefs(togglableStore);
const { are_nsfw_visible, title } = storeToRefs(lycheeStore);

const photos = ref([]); // unused.

const { user, isKeybindingsHelpOpen, smartAlbums, albums, sharedAlbums, rootConfig, rootRights, selectableAlbums, refresh } = useAlbumsRefresher(
	auth,
	lycheeStore,
	is_login_open,
);

const { selectedAlbum, selectedAlbumsIdx, selectedAlbums, selectedAlbumsIds, albumClick, selectEverything, unselect, hasSelection } = useSelection(
	photos,
	selectableAlbums,
);

// Modals for Albums
const { isDeleteVisible, toggleDelete, isMergeAlbumVisible, toggleMergeAlbum, isMoveVisible, toggleMove, isRenameVisible, toggleRename } =
	useGalleryModals(is_upload_visible);

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
	toggleDownload: () => {
		AlbumService.download(selectedAlbumsIds.value);
	},
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
onKeyStroke("f", () => !shouldIgnoreKeystroke() && togglableStore.toggleFullScreen());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && unselect());
onKeyStroke("m", () => !shouldIgnoreKeystroke() && rootRights.value?.can_edit && hasSelection() && toggleMove());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && rootRights.value?.can_edit && hasSelection() && toggleDelete());

onKeyStroke([getModKey(), "a"], () => !shouldIgnoreKeystroke() && selectEverything());

const { onPaste, dragEnd, dropUpload } = useMouseEvents(rootRights, is_upload_visible, list_upload_files);

window.addEventListener("paste", onPaste);
window.addEventListener("dragover", dragEnd);
window.addEventListener("drop", dropUpload);
router.afterEach(() => {
	window.removeEventListener("paste", onPaste);
	window.removeEventListener("dragover", dragEnd);
	window.removeEventListener("drop", dropUpload);
});
</script>
