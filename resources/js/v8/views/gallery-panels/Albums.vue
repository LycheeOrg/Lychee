<template>
	<LoadingProgress v-model:loading="albumsStore.isLoading" />
	<UploadPanel v-if="albumsStore.rootRights?.can_upload" key="upload_modal" @refresh="refresh" />
	<CameraCapture v-if="albumsStore.rootRights?.can_upload" key="camera_capture_modal" />
	<KeybindingsHelp v-if="userStore.isLoggedIn" v-model:open="is_keybindings_help_open" />
	<AlbumCreateDialog v-if="albumsStore.rootRights?.can_upload" key="create_album_modal" />
	<AlbumCreateTagDialog v-if="albumsStore.rootRights?.can_upload" key="create_tag_album_modal" />
	<AlbumCreatePersonDialog v-if="albumsStore.rootRights?.can_upload && lycheeStore.is_person_album_enabled" key="create_person_album_modal" />
	<LoginModal v-if="!userStore.isLoggedIn" @logged-in="onLoggedIn" />
	<WebauthnModal v-if="!userStore.isLoggedIn" @logged-in="onLoggedIn" />
	<SecurityAdvisoriesModal v-if="isAdvisoriesVisible" :visible="isAdvisoriesVisible" :advisories="advisories" @update:visible="advisoryDismiss" />
	<LiveMetrics v-if="userStore.isLoggedIn" />
	<ImportFromLink v-if="albumsStore.rootRights?.can_upload" v-model:open="is_import_from_link_open" @refresh="refresh" />
	<ImportFromServer v-if="albumsStore.rootRights?.can_import_from_server" v-model:open="is_import_from_server_open" @refresh="refresh" />
	<DropBox v-if="albumsStore.rootRights?.can_upload" v-model:open="is_import_from_dropbox_open" @refresh="refresh" />

	<UContextMenu :items="menuSections" :disabled="albumsStore.albums.length === 0" class="contents">
		<div v-if="albumsStore.rootConfig && albumsStore.rootRights" id="galleryView" class="relative w-full h-full select-none" @scroll="onScroll">
			<SelectDrag :with-scroll="false" />
			<Collapse :when="!is_full_screen">
				<AlbumsHeader v-if="userStore.isLoaded" :title="title" @refresh="refresh" @help="is_keybindings_help_open = true" />
			</Collapse>

			<!-- Smart Albums (always visible, above tabs per spec) -->
			<AlbumThumbPanel
				v-if="albumsStore.smartAlbums.length > 0"
				header="gallery.smart_albums"
				:albums="albumsStore.smartAlbums"
				:is-alone="!albumsStore.albums.length"
				:selected-albums="[]"
				:is-timeline="false"
			/>

			<!-- Tabbed view for SEPARATE and SEPARATE_SHARED_ONLY modes (only when shared albums exist) -->
			<template v-if="shouldShowTabs">
				<UTabs v-model="activeTab" :items="tabItems" class="w-full">
					<template #my-albums>
						<template v-if="albumsStore.pinnedAlbums.length > 0">
							<AlbumThumbPanel
								:is-timeline="false"
								header="gallery.pinned_albums"
								:albums="albumsStore.pinnedAlbums"
								:is-alone="!displayAlbums.length"
								:selected-albums="selectedAlbumsIds"
								@clicked="albumSelect"
								@contexted="contextMenuAlbumOpen"
							/>
						</template>
						<template v-if="displayAlbums.length > 0">
							<AlbumThumbPanel
								:is-timeline="albumsStore.rootConfig.is_album_timeline_enabled"
								header="gallery.albums"
								:albums="displayAlbums"
								:is-alone="!albumsStore.pinnedAlbums.length"
								:selected-albums="selectedAlbumsIds"
								@clicked="albumSelect"
								@contexted="contextMenuAlbumOpen"
							/>
						</template>
					</template>
					<template #shared>
						<template v-for="sharedAlbum in displaySharedAlbums" :key="sharedAlbum.header">
							<AlbumThumbPanel
								:header="sharedAlbum.header"
								:albums="sharedAlbum.data"
								:is-alone="displaySharedAlbums.length === 1"
								:selected-albums="selectedAlbumsIds"
								:is-timeline="false"
								@clicked="albumSelect"
								@contexted="contextMenuAlbumOpen"
							/>
						</template>
					</template>
				</UTabs>
			</template>

			<!-- Non-tabbed view for SHOW and HIDE modes (or when no shared albums) -->
			<template v-else>
				<template v-if="albumsStore.pinnedAlbums.length > 0">
					<AlbumThumbPanel
						:is-timeline="false"
						header="gallery.pinned_albums"
						:albums="albumsStore.pinnedAlbums"
						:is-alone="!shouldShowSharedAlbums && !albumsStore.smartAlbums.length && !displayAlbums.length"
						:selected-albums="selectedAlbumsIds"
						@clicked="albumSelect"
						@contexted="contextMenuAlbumOpen"
					/>
				</template>
				<template v-if="displayAlbums.length > 0">
					<AlbumThumbPanel
						:is-timeline="albumsStore.rootConfig.is_album_timeline_enabled"
						header="gallery.albums"
						:albums="displayAlbums"
						:is-alone="!shouldShowSharedAlbums && !albumsStore.smartAlbums.length && !albumsStore.pinnedAlbums.length"
						:selected-albums="selectedAlbumsIds"
						@clicked="albumSelect"
						@contexted="contextMenuAlbumOpen"
					/>
				</template>
				<template v-for="sharedAlbum in displaySharedAlbums" :key="sharedAlbum.header">
					<AlbumThumbPanel
						v-if="shouldShowSharedAlbums"
						:header="sharedAlbum.header"
						:albums="sharedAlbum.data"
						:is-alone="!displayAlbums.length"
						:selected-albums="selectedAlbumsIds"
						:is-timeline="false"
						@clicked="albumSelect"
						@contexted="contextMenuAlbumOpen"
					/>
				</template>
			</template>
			<GalleryFooter v-once />
		</div>
	</UContextMenu>
	<!-- Dialogs for albums -->
	<MoveDialog
		v-model:open="is_move_visible"
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
		v-model:open="is_merge_album_visible"
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
		v-model:open="is_delete_visible"
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
		v-model:open="is_rename_visible"
		:album="selectedAlbum"
		@updated="
			() => {
				unselect();
				refresh();
			}
		"
	/>
	<DownloadAlbum v-model:open="is_download_album_visible" :album-ids="downloadAlbumIds" />
</template>
<script setup lang="ts">
import AlbumThumbPanel from "@/v8/components/gallery/albumModule/AlbumThumbPanel.vue";
import { useUserStore } from "@/stores/UserState";
import { computed, ref, onMounted, onUnmounted } from "vue";
import AlbumsHeader from "@/v8/components/headers/AlbumsHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { onKeyStroke } from "@vueuse/core";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import KeybindingsHelp from "@/v8/components/modals/KeybindingsHelp.vue";
import { useSelection } from "@/composables/selections/selections";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import MoveDialog from "@/v8/components/forms/gallery-dialogs/MoveDialog.vue";
import AlbumMergeDialog from "@/v8/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import DeleteDialog from "@/v8/components/forms/gallery-dialogs/DeleteDialog.vue";
import RenameDialog from "@/v8/components/forms/gallery-dialogs/RenameDialog.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { Collapse } from "vue-collapsed";
import AlbumService from "@/services/album-service";
import { useMouseEvents } from "@/v8/composables/album/uploadEvents";
import GalleryFooter from "@/v8/components/footers/GalleryFooter.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import UploadPanel from "@/v8/components/modals/UploadPanel.vue";
import CameraCapture from "@/v8/components/modals/CameraCapture.vue";
import AlbumCreateDialog from "@/v8/components/forms/album/AlbumCreateDialog.vue";
import AlbumCreateTagDialog from "@/v8/components/forms/album/AlbumCreateTagDialog.vue";
import AlbumCreatePersonDialog from "@/v8/components/forms/album/AlbumCreatePersonDialog.vue";
import { useScrollable } from "@/composables/album/scrollable";
import { EmptyPhotoCallbacks } from "@/utils/Helpers";
import WebauthnModal from "@/v8/components/modals/WebauthnModal.vue";
import LoginModal from "@/v8/components/modals/LoginModal.vue";
import SecurityAdvisoriesModal from "@/v8/components/modals/SecurityAdvisoriesModal.vue";
import { useAdvisoryModal } from "@/composables/modals/useAdvisoryModal";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import LiveMetrics from "@/v8/components/drawers/LiveMetrics.vue";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useRouter } from "vue-router";
import SelectDrag from "@/v8/components/forms/album/SelectDrag.vue";
import ImportFromLink from "@/v8/components/modals/ImportFromLink.vue";
import DropBox from "@/v8/components/modals/DropBox.vue";
import ImportFromServer from "@/v8/components/modals/ImportFromServer.vue";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import DownloadAlbum from "@/v8/components/modals/DownloadAlbum.vue";
import { trans } from "laravel-vue-i18n";
import type { ContextMenuItem, TabsItem } from "@nuxt/ui";

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
const { advisories, isAdvisoriesVisible, advisoryCheck, advisoryDismiss } = useAdvisoryModal();

// Reset!
albumStore.reset();
albumsStore.reset();
photosStore.reset();
photoStore.reset();

async function refresh() {
	await Promise.allSettled([lycheeStore.load(), userStore.refresh()]);
	AlbumService.clearAlbums();
	albumsStore.load(router);
	orderManagementStore.refresh();
}

async function onLoggedIn() {
	await Promise.allSettled([lycheeStore.load(), userStore.refresh()]);
	albumsStore.load(router);
	orderManagementStore.refresh();
	advisoryCheck();
}

const albumId = ref("gallery");

const { onScroll, setScroll } = useScrollable(togglableStore, albumId);
const {
	is_full_screen,
	is_login_open,
	is_upload_visible,
	list_upload_files,
	upload_config,
	is_webauthn_open,
	is_import_from_server_open,
	is_keybindings_help_open,
} = storeToRefs(togglableStore);
const { are_nsfw_visible, title } = storeToRefs(lycheeStore);

const { selectedAlbum, selectedAlbums, selectedAlbumsIds, albumSelect, selectEverything, unselect, hasSelection } = useSelection(
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

const is_download_album_visible = ref(false);
const downloadAlbumIds = ref<string[]>([]);

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: toggleRename,
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMove,
	togglePin: togglePin,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		downloadAlbumIds.value = selectedAlbumsIds.value;
		is_download_album_visible.value = true;
	},
	toggleApplyRenamer: () => {},
	toggleScanFaces: () => {},
};

const { Menu } = useContextMenu(
	{
		selectedAlbum: selectedAlbum,
		selectedAlbums: selectedAlbums,
		selectedAlbumsIds: selectedAlbumsIds,
	},
	EmptyPhotoCallbacks,
	albumCallbacks,
);

// See AlbumPanel.vue for why the composable's imperative albumMenuOpen is bypassed in favor of
// a declarative UContextMenu wrapping the gallery view, and why `:disabled` above must not
// depend on `Menu.length` (that races against the selection side-effect this handler
// performs on the very same contextmenu event).
function contextMenuAlbumOpen(_e: MouseEvent, albumId: string): void {
	if (!selectedAlbumsIds.value.includes(albumId)) {
		selectedAlbumsIds.value = [albumId];
	}
}

function toIconifyName(icon: string): string {
	return "prime:" + icon.replace(/^pi\s+pi-/, "").replace(/^pi-/, "");
}

const menuSections = computed<ContextMenuItem[][]>(() => {
	const sections: ContextMenuItem[][] = [[]];
	for (const entry of Menu.value) {
		if (entry.is_divider) {
			sections.push([]);
			continue;
		}
		sections[sections.length - 1].push({
			label: trans(entry.label ?? ""),
			icon: toIconifyName(entry.icon ?? ""),
			onSelect: entry.callback,
		});
	}
	return sections.filter((s) => s.length > 0);
});

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
const root_parent_id = ref<string | null>(null);
const root_existing_albums = computed(() => albumsStore.albums.map((a) => ({ id: a.id, title: a.title })));

// Shared albums visibility mode handling
const sharedAlbumsVisibilityMode = computed(() => albumsStore.rootConfig?.shared_albums_visibility_mode ?? "show");

// Active tab for tabbed view
const activeTab = ref<string>("my-albums");

const tabItems = computed<TabsItem[]>(() => [
	{ label: trans("gallery.tabs.my_albums"), value: "my-albums", slot: "my-albums" },
	{ label: trans("gallery.tabs.shared_with_me"), value: "shared", slot: "shared" },
]);

// Always display owned albums (no merging)
const displayAlbums = computed(() => {
	return albumsStore.albums;
});

// Determine which shared albums to display based on visibility mode
const displaySharedAlbums = computed(() => {
	if (sharedAlbumsVisibilityMode.value === "hide") {
		// HIDE: don't show shared albums at all
		return [];
	}
	if (sharedAlbumsVisibilityMode.value === "separate_shared_only") {
		// Filter to only show directly shared albums (not public albums owned by others)
		// An album is "directly shared" if it's not public (is_public === false)
		return albumsStore.sharedAlbums
			.map((group) => ({
				...group,
				data: group.data.filter((album) => !album.is_public),
			}))
			.filter((group) => group.data.length > 0);
	}
	// SHOW and SEPARATE: show all shared albums grouped by owner
	return albumsStore.sharedAlbums;
});

// Check if we should show shared albums section (for inline mode)
const shouldShowSharedAlbums = computed(() => {
	return displaySharedAlbums.value.length > 0;
});

// Check if we should show tabs (SEPARATE or SEPARATE_SHARED_ONLY mode with shared albums)
const shouldShowTabs = computed(() => {
	const mode = sharedAlbumsVisibilityMode.value;
	const isSeparateMode = mode === "separate" || mode === "separate_shared_only";
	return isSeparateMode && displaySharedAlbums.value.length > 0;
});

const { onPaste, dragEnd, dropUpload } = useMouseEvents(
	can_upload,
	is_upload_visible,
	list_upload_files,
	root_parent_id,
	root_existing_albums,
	upload_config,
);

onMounted(() => {
	togglableStore.loadUploadConfig();
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
