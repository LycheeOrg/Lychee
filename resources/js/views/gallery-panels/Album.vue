<template>
	<LoadingProgress v-model:loading="isLoading" />

	<!-- Modals Upload, login, Create -->
	<UploadPanel v-if="album?.rights.can_upload" @refresh="refresh" key="upload_modal" />
	<LoginModal v-if="user?.id === null" @logged-in="refresh" />
	<WebauthnModal v-if="user?.id === null" @logged-in="refresh" />
	<AlbumCreateDialog v-if="album?.rights.can_upload && config?.is_model_album" v-model:parent-id="album.id" key="create_album_modal" />

	<!-- Warnings & Locks -->
	<SensitiveWarning v-if="config?.is_nsfw_warning_visible" :album-id="albumid" />
	<Unlock :albumid="albumid" :visible="isPasswordProtected" @reload="refresh" @fail="is_login_open = true" />

	<!-- Album panel -->
	<AlbumPanel
		v-if="layoutConfig"
		:model-album="modelAlbum"
		:album="album"
		:config="config"
		:user="user"
		:layoutConfig="layoutConfig"
		:key="album?.id ?? 'not-found'"
		@refresh="refresh"
		@toggle-slide-show="toggleSlideShow"
		@toggle-details="scrollToTop"
	/>

	<!-- Photo panel here (soon) -->

	<!-- Dialogs -->
	<PhotoTagDialog
		v-model:visible="is_tag_visible"
		:parent-id="albumid"
		:photo="selectedPhoto"
		:photo-ids="selectedPhotosIds"
		@tagged="
			() => {
				unselect();
				refresh();
			}
		"
	/>
	<PhotoCopyDialog
		v-model:visible="is_copy_visible"
		:parent-id="albumid"
		:photo="selectedPhoto"
		:photo-ids="selectedPhotosIds"
		@copied="
			() => {
				unselect();
				refresh();
			}
		"
	/>
	<MoveDialog
		v-model:visible="is_move_visible"
		:parent-id="albumid"
		:photo="selectedPhoto"
		:photo-ids="selectedPhotosIds"
		:album="selectedAlbum"
		:album-ids="selectedAlbumsIds"
		@moved="
			() => {
				unselect();
				refresh();
			}
		"
	/>
	<DeleteDialog
		v-model:visible="is_delete_visible"
		:parent-id="albumid"
		:photo="selectedPhoto"
		:photo-ids="selectedPhotosIds"
		:album="selectedAlbum"
		:album-ids="selectedAlbumsIds"
		@deleted="
			() => {
				unselect();
				refresh();
			}
		"
	/>

	<!-- Dialogs for albums -->
	<RenameDialog
		v-model:visible="is_rename_visible"
		:parent-id="undefined"
		:album="selectedAlbum"
		:photo="selectedPhoto"
		@renamed="
			() => {
				unselect();
				refresh();
			}
		"
	/>
	<AlbumMergeDialog
		v-model:visible="is_merge_album_visible"
		:parent-id="albumid"
		:album="selectedAlbum"
		:album-ids="selectedAlbumsIds"
		@merged="
			() => {
				unselect();
				refresh();
			}
		"
	/>
</template>
<script setup lang="ts">
import { useAuthStore } from "@/stores/Auth";
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke } from "@vueuse/core";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { useSelection } from "@/composables/selections/selections";
import { useAlbumRefresher } from "@/composables/album/albumRefresher";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import SensitiveWarning from "@/components/gallery/albumModule/SensitiveWarning.vue";
import Unlock from "@/components/forms/album/Unlock.vue";
import LoginModal from "@/components/modals/LoginModal.vue";
import { useMouseEvents } from "@/composables/album/uploadEvents";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import UploadPanel from "@/components/modals/UploadPanel.vue";
import AlbumCreateDialog from "@/components/forms/album/AlbumCreateDialog.vue";
import { useScrollable } from "@/composables/album/scrollable";
import { useGetLayoutConfig } from "@/layouts/PhotoLayout";
import WebauthnModal from "@/components/modals/WebauthnModal.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import AlbumPanel from "@/components/gallery/albumModule/AlbumPanel.vue";

const route = useRoute();
const router = useRouter();

const props = defineProps<{
	albumid: string;
	photoid?: string;
}>();

const albumid = ref(props.albumid);
// flag to open login modal if necessary
const auth = useAuthStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
togglableStore.resetSearch();

const { are_nsfw_visible, nsfw_consented } = storeToRefs(lycheeStore);
const { is_login_open, is_slideshow_active, is_upload_visible, list_upload_files, is_create_album_visible, are_details_open } =
	storeToRefs(togglableStore);

const { scrollToTop, setScroll } = useScrollable(togglableStore, albumid);

function toggleSlideShow() {
	if (album.value === undefined || album.value.photos.length === 0) {
		return;
	}

	is_slideshow_active.value = true;
	router.push({ name: "photo", params: { albumid: album.value.id, photoid: album.value.photos[0].id } });
}

const { layoutConfig, loadLayoutConfig } = useGetLayoutConfig();

// Set up Album ID reference. This one is updated at each page change.
const { isPasswordProtected, isLoading, user, modelAlbum, album, rights, photos, config, refresh } = useAlbumRefresher(
	albumid,
	auth,
	is_login_open,
	nsfw_consented,
);

const children = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(() => modelAlbum.value?.albums ?? []);

const {
	is_delete_visible,
	toggleDelete,
	is_merge_album_visible,
	is_move_visible,
	toggleMove,
	is_rename_visible,
	toggleRename,
	is_tag_visible,
	toggleTag,
	is_copy_visible,
	toggleCopy,
} = useGalleryModals(togglableStore);

const { selectedPhoto, selectedAlbum, selectedPhotosIds, selectedAlbumsIds, selectEverything, unselect, hasSelection } = useSelection(
	photos,
	children,
	togglableStore,
);

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
	toggleDownload: () => {
		PhotoService.download(selectedPhotosIds.value);
	},
};

function toggleDetails() {
	are_details_open.value = !are_details_open.value;
	if (are_details_open.value) {
		scrollToTop();
	}
}

function openSearch() {
	if (album.value === undefined) {
		return;
	}
	router.push({ name: "search-with-album", params: { albumid: album.value?.id } });
}

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && togglableStore.toggleFullScreen());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && unselect());
onKeyStroke("n", () => !shouldIgnoreKeystroke() && (is_create_album_visible.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && (is_upload_visible.value = true));
onKeyStroke("i", () => !shouldIgnoreKeystroke() && toggleDetails());
onKeyStroke("l", () => !shouldIgnoreKeystroke() && user.value?.id === null && (is_login_open.value = true));
onKeyStroke("/", () => !shouldIgnoreKeystroke() && config.value?.is_search_accessible && openSearch());

// Privileged actions
onKeyStroke("m", () => !shouldIgnoreKeystroke() && album.value?.rights.can_move && hasSelection() && toggleMove());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && album.value?.rights.can_delete && hasSelection() && toggleDelete());

onKeyStroke([getModKey(), "a"], () => !shouldIgnoreKeystroke() && selectEverything());

const { onPaste, dragEnd, dropUpload } = useMouseEvents(rights, is_upload_visible, list_upload_files);

onMounted(() => {
	// Reset the slideshow.
	is_slideshow_active.value = false;

	window.addEventListener("paste", onPaste);
	window.addEventListener("dragover", dragEnd);
	window.addEventListener("drop", dropUpload);
});

onMounted(async () => {
	const results = await Promise.allSettled([loadLayoutConfig(), refresh()]);

	results.forEach((result, index) => {
		if (result.status === "rejected") {
			console.warn(`Promise ${index} reject with reason: ${result.reason}`);
		}
	});

	if (results.every((result) => result.status === "fulfilled")) {
		setScroll();
	}
});

onUnmounted(() => {
	unselect();
	window.removeEventListener("paste", onPaste);
	window.removeEventListener("dragover", dragEnd);
	window.removeEventListener("drop", dropUpload);
});

watch(
	() => route.params.albumid,
	(newId, _oldId) => {
		unselect();

		albumid.value = newId as string;
		refresh().then(setScroll);
	},
);
</script>
