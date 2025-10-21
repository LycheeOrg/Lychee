<template>
	<LoadingProgress v-model:loading="albumsStore.isLoading" />
	<UploadPanel v-if="albumsStore.rootRights?.can_upload" key="upload_modal" @refresh="refresh" />
	<KeybindingsHelp v-if="userStore.isLoggedIn" v-model:visible="is_keybindings_help_open" />
	<AlbumCreateDialog v-if="albumsStore.rootRights?.can_upload" key="create_album_modal" />
	<AlbumCreateTagDialog v-if="albumsStore.rootRights?.can_upload" key="create_tag_album_modal" />
	<LoginModal v-if="!userStore.isLoggedIn" @logged-in="refresh" />
	<WebauthnModal v-if="!userStore.isLoggedIn" @logged-in="refresh" />
	<LiveMetrics v-if="userStore.isLoggedIn" />
	<ImportFromLink v-if="albumsStore.rootRights?.can_upload" v-model:visible="is_import_from_link_open" @refresh="refresh" />
	<ImportFromServer v-if="albumsStore.rootRights?.can_import_from_server" v-model:visible="is_import_from_server_open" @refresh="refresh" />
	<DropBox v-if="albumsStore.rootRights?.can_upload" v-model:visible="is_import_from_dropbox_open" @refresh="refresh" />

	<div v-if="albumsStore.rootConfig && albumsStore.rootRights" id="galleryView" class="relative w-full h-full select-none" @scroll="onScroll">
		<SelectDrag :with-scroll="false" />
		<Collapse :when="!is_full_screen">
			<AlbumsHeader v-if="userStore.isLoaded" :title="title" @refresh="refresh" @help="is_keybindings_help_open = true" />
		</Collapse>

		<AlbumThumbPanel
			v-if="albumsStore.smartAlbums.length > 0"
			header="gallery.smart_albums"
			:album="undefined"
			:albums="albumsStore.smartAlbums"
			:is-alone="!albumsStore.albums.length"
			:idx-shift="-1"
			:selected-albums="[]"
			:is-timeline="false"
		/>
		<template v-if="albumsStore.pinnedAlbums.length > 0">
			<AlbumThumbPanel
				:is-timeline="false"
				header="gallery.pinned_albums"
				:album="null"
				:albums="albumsStore.pinnedAlbums"
				:is-alone="!albumsStore.sharedAlbums.length && !albumsStore.smartAlbums.length && !albumsStore.albums.length"
				:idx-shift="0"
				:selected-albums="selectedAlbumsIds"
				@clicked="albumClick"
				@contexted="albumMenuOpen"
			/>
		</template>
		<template v-if="albumsStore.albums.length > 0">
			<AlbumThumbPanel
				:is-timeline="albumsStore.rootConfig.is_album_timeline_enabled"
				header="gallery.albums"
				:album="null"
				:albums="albumsStore.albums"
				:is-alone="!albumsStore.sharedAlbums.length && !albumsStore.smartAlbums.length && !albumsStore.pinnedAlbums.length"
				:idx-shift="albumsStore.pinnedAlbums.length"
				:selected-albums="selectedAlbumsIds"
				@clicked="albumClick"
				@contexted="albumMenuOpen"
			/>
		</template>
		<template v-for="sharedAlbum in albumsStore.sharedAlbums" :key="sharedAlbum.header">
			<AlbumThumbPanel
				v-if="albumsStore.sharedAlbums.length > 0"
				:header="sharedAlbum.header"
				:album="undefined"
				:albums="sharedAlbum.data"
				:is-alone="!albumsStore.albums.length"
				:idx-shift="sharedAlbum.iter"
				:selected-albums="selectedAlbumsIds"
				:is-timeline="false"
				@clicked="albumClick"
				@contexted="albumMenuOpen"
			/>
		</template>
		<GalleryFooter v-once />
		<ScrollTop target="parent" />
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
	<!-- Dialogs for albums -->
	<MoveDialog
		v-model:visible="is_move_visible"
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
		v-model:visible="is_merge_album_visible"
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
		v-model:visible="is_delete_visible"
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
		v-model:visible="is_rename_visible"
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
import AlbumThumbPanel from "@/components/gallery/albumModule/AlbumThumbPanel.vue";
import { useUserStore } from "@/stores/UserState";
import { computed, ref, onMounted, onUnmounted } from "vue";
import AlbumsHeader from "@/components/headers/AlbumsHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { onKeyStroke } from "@vueuse/core";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import KeybindingsHelp from "@/components/modals/KeybindingsHelp.vue";
import { useSelection } from "@/composables/selections/selections";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import ContextMenu from "primevue/contextmenu";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import Divider from "primevue/divider";
import ScrollTop from "primevue/scrolltop";
import { Collapse } from "vue-collapsed";
import AlbumService from "@/services/album-service";
import { useMouseEvents } from "@/composables/album/uploadEvents";
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import UploadPanel from "@/components/modals/UploadPanel.vue";
import AlbumCreateDialog from "@/components/forms/album/AlbumCreateDialog.vue";
import AlbumCreateTagDialog from "@/components/forms/album/AlbumCreateTagDialog.vue";
import { useScrollable } from "@/composables/album/scrollable";
import { EmptyPhotoCallbacks } from "@/utils/Helpers";
import WebauthnModal from "@/components/modals/WebauthnModal.vue";
import LoginModal from "@/components/modals/LoginModal.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import LiveMetrics from "@/components/drawers/LiveMetrics.vue";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useRouter } from "vue-router";
import SelectDrag from "@/components/forms/album/SelectDrag.vue";
import ImportFromLink from "@/components/modals/ImportFromLink.vue";
import DropBox from "@/components/modals/DropBox.vue";
import ImportFromServer from "@/components/modals/ImportFromServer.vue";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useOrderManagementStore } from "@/stores/OrderManagement";

const userStore = useUserStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const leftMenuStore = useLeftMenuStateStore();
const albumsStore = useAlbumsStore();
const albumStore = useAlbumStore();
const photosStore = usePhotosStore();
const photoStore = usePhotoStore();
const router = useRouter();
const orderManagementStore = useOrderManagementStore();

// Reset!
albumStore.reset();
albumsStore.reset();
photosStore.reset();
photoStore.reset();

async function refresh() {
	await Promise.allSettled([lycheeStore.load(), userStore.refresh()]);
	albumsStore.load(router);
	orderManagementStore.refresh();
}

const albumId = ref("gallery");

const { onScroll, setScroll } = useScrollable(togglableStore, albumId);
const {
	is_full_screen,
	is_login_open,
	is_upload_visible,
	list_upload_files,
	is_webauthn_open,
	is_import_from_server_open,
	is_keybindings_help_open,
} = storeToRefs(togglableStore);
const { are_nsfw_visible, title } = storeToRefs(lycheeStore);

const { selectedAlbum, selectedAlbumsIdx, selectedAlbums, selectedAlbumsIds, albumClick, selectEverything, unselect, hasSelection } = useSelection(
	photosStore,
	albumsStore,
	togglableStore,
);

// Modals for Albums
const {
	is_delete_visible,
	toggleDelete,
	is_merge_album_visible,
	toggleMergeAlbum,
	is_move_visible,
	toggleMove,
	is_rename_visible,
	toggleRename,
	is_import_from_link_open,
	is_import_from_dropbox_open,
} = useGalleryModals(togglableStore);

function togglePin() {
	if (!selectedAlbum.value) return;

	AlbumService.setPinned(selectedAlbum.value.id, !selectedAlbum.value.is_pinned).then(() => {
		AlbumService.clearAlbums();
		refresh();
		unselect();
	});
}

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: toggleRename,
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMove,
	togglePin: togglePin,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		AlbumService.download(selectedAlbumsIds.value);
	},
};

const { menu, Menu, albumMenuOpen } = useContextMenu(
	{
		selectedAlbum: selectedAlbum,
		selectedAlbums: selectedAlbums,
		selectedAlbumIdx: selectedAlbumsIdx,
	},
	EmptyPhotoCallbacks,
	albumCallbacks,
);

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && togglableStore.toggleFullScreen());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && unselect());
onKeyStroke("m", () => !shouldIgnoreKeystroke() && albumsStore.rootRights?.can_edit && hasSelection() && toggleMove());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && albumsStore.rootRights?.can_edit && hasSelection() && toggleDelete());

onKeyStroke("a", (e) => {
	if (!shouldIgnoreKeystroke() && e.getModifierState(getModKey()) && !e.shiftKey && !e.altKey) {
		e.preventDefault();
		selectEverything();
	}
});
onKeyStroke("l", () => !shouldIgnoreKeystroke() && !userStore.isLoggedIn && (is_login_open.value = true));
onKeyStroke("k", () => !shouldIgnoreKeystroke() && !userStore.isLoggedIn && (is_webauthn_open.value = true));

const can_upload = computed(() => albumsStore.rootRights?.can_upload === true);

const { onPaste, dragEnd, dropUpload } = useMouseEvents(can_upload, is_upload_visible, list_upload_files);

onMounted(() => {
	window.addEventListener("paste", onPaste);
	window.addEventListener("dragover", dragEnd);
	window.addEventListener("drop", dropUpload);
	leftMenuStore.left_menu_open = false;
});

onMounted(async () => {
	await refresh();
	setScroll();
});

onUnmounted(() => {
	window.removeEventListener("paste", onPaste);
	window.removeEventListener("dragover", dragEnd);
	window.removeEventListener("drop", dropUpload);
});
</script>
