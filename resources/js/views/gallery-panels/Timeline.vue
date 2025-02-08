<template>
	<KeybindingsHelp v-model:visible="isKeybindingsHelpOpen" v-if="user?.id" />
	<!-- <div v-if="rootConfig && rootRights" @click="unselect" class="h-svh overflow-y-auto"> -->
	<div v-if="rootConfig && rootRights" class="h-svh overflow-y-auto">
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
		<PhotoThumbPanel
			v-if="layoutConfig !== null && photos !== null && photos.length > 0"
			header="lychee.PHOTOS"
			:photos="photos"
			:album="undefined"
			:gallery-config="layoutConfig"
			:photo-layout="layout"
			:selected-photos="selectedPhotosIds"
			@clicked="photoClick"
			@contexted="photoMenuOpen"
			:is-timeline="true"
		/>
		<div class="sentinel" ref="sentinel"></div>
		<ScrollTop target="parent" :threshold="50" />
		<!-- Dialogs -->
		<PhotoTagDialog
			v-model:visible="isTagVisible"
			:parent-id="undefined"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@tagged="refresh"
		/>
		<PhotoCopyDialog
			v-model:visible="isCopyVisible"
			:parent-id="undefined"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@copied="refresh"
		/>
		<MoveDialog
			v-model:visible="isMoveVisible"
			:parent-id="undefined"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="undefined"
			:album-ids="[]"
			@moved="refresh"
		/>
		<DeleteDialog
			v-model:visible="isDeleteVisible"
			:parent-id="undefined"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="undefined"
			:album-ids="[]"
			@deleted="refresh"
		/>
		<RenameDialog v-model:visible="isRenameVisible" :parent-id="undefined" :album="undefined" :photo="selectedPhoto" @renamed="refresh" />

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
		<GalleryFooter v-once />
	</div>
</template>
<script setup lang="ts">
import { useAuthStore } from "@/stores/Auth";
import { computed, ref } from "vue";
import AlbumsHeader from "@/components/headers/AlbumsHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { onKeyStroke, useIntersectionObserver } from "@vueuse/core";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import KeybindingsHelp from "@/components/modals/KeybindingsHelp.vue";
import { useSelection } from "@/composables/selections/selections";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import ContextMenu from "primevue/contextmenu";
import { useAlbumsRefresher } from "@/composables/album/albumsRefresher";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import Divider from "primevue/divider";
import { Collapse } from "vue-collapsed";
import AlbumService from "@/services/album-service";
import { useRouter } from "vue-router";
import { useMouseEvents } from "@/composables/album/uploadEvents";
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import PhotoService from "@/services/photo-service";
import { useGetLayoutConfig } from "@/layouts/PhotoLayout";
import PhotoThumbPanel from "@/components/gallery/PhotoThumbPanel.vue";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import ScrollTop from "primevue/scrolltop";
import TimelineService from "@/services/timeline-service";
import { EmptyAlbumCallbacks } from "@/utils/Helpers";

const auth = useAuthStore();
const router = useRouter();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
lycheeStore.resetSearch();

const { are_nsfw_visible, is_full_screen, is_login_open, title, is_upload_visible, list_upload_files } = storeToRefs(lycheeStore);

const photos = ref([] as App.Http.Resources.Models.PhotoResource[]); // unused.
const albums = ref([]); // unused.

const { layout, layoutConfig, loadLayoutConfig, loadLayoutTimeline } = useGetLayoutConfig();
const { user, isKeybindingsHelpOpen, rootConfig, rootRights, refresh } = useAlbumsRefresher(auth, lycheeStore, is_login_open);

const { selectedPhotosIdx, selectedPhoto, selectedPhotos, selectedPhotosIds, photoClick, hasSelection, unselect, selectEverything } = useSelection(
	photos,
	albums,
);

loadLayoutConfig();
loadLayoutTimeline();

const page = ref(0);
const lastPage = ref(0);

const sentinel = ref(null);

const { stop } = useIntersectionObserver(sentinel, ([{ isIntersecting }]) => {
	if (isIntersecting) {
		loadMore();
	}
});

function loadMore() {
	if (page.value > lastPage.value) {
		return;
	}
	page.value += 1;
	TimelineService.timeline(page.value).then((response) => {
		console.log(response.data.last_page);
		console.log(page.value);
		photos.value.push(...response.data.photos);
		lastPage.value = response.data.last_page;
	});
}

// Modals for Albums
const {
	isDeleteVisible,
	toggleDelete,
	isMoveVisible,
	toggleMove,
	isRenameVisible,
	toggleRename,
	isTagVisible,
	toggleTag,
	isCopyVisible,
	toggleCopy,
} = useGalleryModals(is_upload_visible);

const photoCallbacks = {
	star: () => {
		PhotoService.star(selectedPhotosIds.value, true);
		AlbumService.clearCache();
		refresh();
	},
	unstar: () => {
		PhotoService.star(selectedPhotosIds.value, false);
		AlbumService.clearCache();
		refresh();
	},
	setAsCover: () => {},
	setAsHeader: () => {},
	toggleTag: toggleTag,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {},
};

const { menu, Menu, photoMenuOpen } = useContextMenu(
	{
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIdx: selectedPhotosIdx,
	},
	photoCallbacks,
	EmptyAlbumCallbacks,
);

refresh();

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && lycheeStore.toggleFullScreen());
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
<style lang="css">
/* Kill the border of ScrollTop */
.p-scrolltop {
	border: none;
}
</style>
