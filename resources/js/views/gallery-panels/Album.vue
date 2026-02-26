<template>
	<LoadingProgress v-model:loading="isLoading" />

	<!-- Modals Upload, login, Create -->
	<UploadPanel v-if="albumStore.rights?.can_upload" key="upload_modal" @refresh="refresh" />
	<LoginModal v-if="!userStore.isLoggedIn" @logged-in="refresh" />
	<WebauthnModal v-if="!userStore.isLoggedIn" @logged-in="refresh" />
	<AlbumCreateDialog v-if="albumStore.rights?.can_upload && albumStore.config?.is_model_album" key="create_album_modal" />
	<ImportFromLink v-if="albumStore.rights?.can_upload" v-model:visible="is_import_from_link_open" @refresh="refresh" />
	<ImportFromServer v-if="albumStore.rights?.can_import_from_server" v-model:visible="is_import_from_server_open" @refresh="refresh" />
	<DropBox v-if="albumStore.rights?.can_upload" v-model:visible="is_import_from_dropbox_open" @refresh="refresh" />

	<!-- Warnings & Locks -->
	<SensitiveWarning v-if="albumStore.config?.is_nsfw_warning_visible" />
	<Unlock :visible="albumStore.isPasswordProtected" @reload="refresh" @fail="is_login_open = true" />

	<!-- Album panel -->
	<AlbumPanel
		v-if="layoutStore.config !== undefined && albumStore.album !== undefined"
		:key="albumStore.albumId ?? 'not-found'"
		:is-photo-open="photoStore.isLoaded"
		@refresh="refresh"
		@toggle-slide-show="toggleSlideShow"
		@toggle-edit="toggleEdit"
		@open-search="openSearch"
		@go-back="goBack"
	/>

	<!-- Photo panel -->
	<PhotoPanel
		v-if="photoStore.isLoaded"
		:is-map-visible="albumStore.config?.is_map_accessible ?? false"
		@toggle-slide-show="slideshow"
		@rotate-overlay="rotateOverlay"
		@rotate-photo-c-w="rotatePhotoCW"
		@rotate-photo-c-c-w="rotatePhotoCCW"
		@set-album-header="setAlbumHeader"
		@toggle-star="toggleHighlight"
		@toggle-move="toggleMove"
		@toggle-delete="toggleDelete"
		@updated="refresh()"
		@go-back="goBack"
		@next="() => next(true)"
		@previous="() => previous(true)"
	/>
	<!-- Dialogs -->
	<template v-if="photoStore.isLoaded">
		<PhotoEdit v-if="albumStore.rights?.can_edit" v-model:visible="is_photo_edit_open" />
		<MoveDialog v-model:visible="is_move_visible" @moved="refresh" />
		<DeleteDialog v-model:visible="is_delete_visible" @deleted="refresh(true)" />
	</template>
	<template v-else>
		<PhotoTagDialog
			v-model:visible="is_tag_visible"
			:parent-id="albumId"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@tagged="
				() => {
					unselect();
					refresh();
				}
			"
		/>
		<PhotoLicenseDialog
			v-model:visible="is_license_visible"
			:parent-id="albumId"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@licensed="
				() => {
					unselect();
					refresh();
				}
			"
		/>
		<PhotoCopyDialog
			v-model:visible="is_copy_visible"
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
</template>
<script setup lang="ts">
import { useUserStore } from "@/stores/UserState";
import { ref, watch, onMounted, onUnmounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke, useDebounceFn } from "@vueuse/core";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { useSelection } from "@/composables/selections/selections";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoLicenseDialog from "@/components/forms/photo/PhotoLicenseDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import SensitiveWarning from "@/components/gallery/albumModule/SensitiveWarning.vue";
import Unlock from "@/components/forms/album/Unlock.vue";
import LoginModal from "@/components/modals/LoginModal.vue";
import { useMouseEvents } from "@/composables/album/uploadEvents";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import UploadPanel from "@/components/modals/UploadPanel.vue";
import AlbumCreateDialog from "@/components/forms/album/AlbumCreateDialog.vue";
import { useScrollable } from "@/composables/album/scrollable";
import WebauthnModal from "@/components/modals/WebauthnModal.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import AlbumPanel from "@/components/gallery/albumModule/AlbumPanel.vue";
import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import PhotoPanel from "@/components/gallery/photoModule/PhotoPanel.vue";
import { usePhotoActions } from "@/composables/album/photoActions";
import { useToast } from "primevue/usetoast";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import MetricsService from "@/services/metrics-service";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useAlbumRoute } from "@/composables/photo/albumRoute";
import { useLtRorRtL } from "@/utils/Helpers";
import ImportFromLink from "@/components/modals/ImportFromLink.vue";
import DropBox from "@/components/modals/DropBox.vue";
import ImportFromServer from "@/components/modals/ImportFromServer.vue";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useCatalogStore } from "@/stores/CatalogState";
import { useRating } from "@/composables/photo/useRating";

const { isLTR } = useLtRorRtL();

const route = useRoute();
const router = useRouter();
const toast = useToast();

const { albumRoutes } = useAlbumRoute(router);

const props = defineProps<{
	albumId: string;
	photoId?: string;
}>();

// unused? Hard to say...
const videoElement = ref<HTMLVideoElement | null>(null);

// flag to open login modal if necessary
const userStore = useUserStore();
const albumStore = useAlbumStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const orderManagement = useOrderManagementStore();
const photoStore = usePhotoStore();
const albumsStore = useAlbumsStore();
const photosStore = usePhotosStore();
const layoutStore = useLayoutStore();
const catalogStore = useCatalogStore();

async function load() {
	await Promise.allSettled([layoutStore.load(), lycheeStore.load(), userStore.load(), albumStore.load()]);
	catalogStore.albumId = albumId.value;
	catalogStore.load();
	orderManagement.load();
	photoStore.photoId = photoId.value;
	photoStore.load();
}

async function refresh(isDelete: boolean = false) {
	await Promise.allSettled([layoutStore.load(), lycheeStore.load(), userStore.refresh(), albumStore.refresh()]);
	catalogStore.albumId = albumId.value;
	catalogStore.load();
	orderManagement.load();
	if (isDelete) {
		// If we have deleted a photo, we need to reset the photo store
		router.push({ name: albumRoutes().album, params: { albumId: albumId.value } });
		return;
	}
	photoStore.photoId = photoId.value;
	photoStore.load();
}

// eslint-disable-next-line vue/no-dupe-keys
const { albumId, isLoading, current_page } = storeToRefs(albumStore);
// eslint-disable-next-line vue/no-dupe-keys
const { photoId } = storeToRefs(photoStore);

const { are_nsfw_visible, slideshow_timeout, is_slideshow_enabled } = storeToRefs(lycheeStore);
const {
	is_photo_edit_open,
	are_details_open,
	is_login_open,
	is_slideshow_active,
	is_upload_visible,
	list_upload_files,
	is_create_album_visible,
	is_album_edit_open,
	is_import_from_link_open,
} = storeToRefs(togglableStore);

const { scrollToTop, setScroll } = useScrollable(togglableStore, albumId);

const {
	is_delete_visible,
	toggleDelete,
	is_merge_album_visible,
	is_move_visible,
	toggleMove,
	is_rename_visible,
	is_tag_visible,
	is_license_visible,
	is_copy_visible,
	is_import_from_dropbox_open,
	is_import_from_server_open,
} = useGalleryModals(togglableStore);

const { getParentId } = usePhotoRoute(router);

const { toggleHighlight, rotatePhotoCCW, rotatePhotoCW, setAlbumHeader, rotateOverlay } = usePhotoActions(photoStore, albumId, toast, lycheeStore);

const { getNext, getPrevious } = getNextPreviousPhoto(router, photoStore);
const { slideshow, next, previous, stop } = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);

function toggleSlideShow() {
	if (albumStore.album === undefined || photosStore.photos.length === 0) {
		return;
	}

	slideshow();
	router.push({ name: albumRoutes().album, params: { albumId: albumStore.album.id, photoId: photosStore.photos[0].id } });
}

const { selectedPhoto, selectedAlbum, selectedPhotosIds, selectedAlbumsIds, selectEverything, unselect, hasSelection } = useSelection(
	photosStore,
	albumsStore,
	togglableStore,
);

const { handleRatingClick } = useRating(photoStore, toast, userStore);

function goBack() {
	if (is_slideshow_active.value) {
		stop();
	}

	if (is_photo_edit_open.value === true) {
		is_photo_edit_open.value = false;
		return;
	}

	if (photoId.value !== undefined) {
		photoStore.reset();

		router.push({ name: albumRoutes().album, params: { albumId: albumId.value } });
		return;
	}

	is_album_edit_open.value = false;
	if (albumStore.modelAlbum !== undefined && albumStore.modelAlbum.parent_id !== null) {
		router.push({ name: albumRoutes().album, params: { albumId: albumStore.modelAlbum.parent_id } });
	} else {
		router.push({ name: albumRoutes().home });
	}
}

function toggleDetails() {
	is_photo_edit_open.value = false;
	are_details_open.value = !are_details_open.value;
}

function toggleEdit() {
	if (photoStore.isLoaded) {
		are_details_open.value = false;
		is_photo_edit_open.value = !is_photo_edit_open.value;
		return;
	}

	is_album_edit_open.value = !is_album_edit_open.value;
	if (is_album_edit_open.value) {
		scrollToTop();
	}
}

function openSearch() {
	if (albumStore.album === undefined) {
		return;
	}
	router.push({ name: "search", params: { albumId: albumStore.album.id } });
}

// Album operations
onKeyStroke("h", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && togglableStore.toggleFullScreen());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && unselect());
onKeyStroke("n", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && (is_create_album_visible.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && (is_upload_visible.value = true));
onKeyStroke("i", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && toggleEdit());
onKeyStroke("l", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && !userStore.isLoggedIn && (is_login_open.value = true));
onKeyStroke("/", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && albumStore.config?.is_search_accessible && openSearch());
onKeyStroke("a", (e) => {
	if (!shouldIgnoreKeystroke() && !photoStore.isLoaded && e.getModifierState(getModKey()) && !e.shiftKey && !e.altKey) {
		e.preventDefault();
		selectEverything();
	}
});

// Photo operations (note that the arrow keys are flipped for RTL languages)
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && isLTR() && photoStore.hasPrevious && previous(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && isLTR() && photoStore.hasNext && next(true));
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && !isLTR() && photoStore.hasNext && next(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && !isLTR() && photoStore.hasPrevious && previous(true));
onKeyStroke("o", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && rotateOverlay());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && is_slideshow_enabled.value && slideshow());
onKeyStroke("i", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && toggleDetails());
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && togglableStore.toggleFullScreen());
onKeyStroke("Escape", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && is_slideshow_active.value && stop());

// Privileged Album actions
onKeyStroke("m", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && albumStore.rights?.can_move && hasSelection() && toggleMove());
onKeyStroke(
	["Delete", "Backspace"],
	() => !shouldIgnoreKeystroke() && !photoStore.isLoaded && albumStore.rights?.can_delete && hasSelection() && toggleDelete(),
);

// Priviledged Photo operations
onKeyStroke("m", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && albumStore.rights?.can_edit && toggleMove());
onKeyStroke("e", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && albumStore.rights?.can_edit && toggleEdit());
onKeyStroke("s", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && albumStore.rights?.can_edit && toggleHighlight());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && photoStore.isLoaded && albumStore.rights?.can_delete && toggleDelete());
onKeyStroke("0", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && handleRatingClick(photoStore.photo!.id, 0));
onKeyStroke("1", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && handleRatingClick(photoStore.photo!.id, 1));
onKeyStroke("2", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && handleRatingClick(photoStore.photo!.id, 2));
onKeyStroke("3", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && handleRatingClick(photoStore.photo!.id, 3));
onKeyStroke("4", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && handleRatingClick(photoStore.photo!.id, 4));
onKeyStroke("5", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && handleRatingClick(photoStore.photo!.id, 5));

// on key stroke escape:
// 1. lose focus
// 2. close modals
// 3. go back
onKeyStroke("Escape", () => {
	// 1. lose focus
	if (shouldIgnoreKeystroke() && document.activeElement instanceof HTMLElement) {
		document.activeElement.blur();
		return;
	}

	if (are_details_open.value && lycheeStore.is_photo_details_always_open === false) {
		are_details_open.value = false;
		return;
	}

	if (is_move_visible.value) {
		is_move_visible.value = false;
		return;
	}

	if (is_delete_visible.value) {
		is_delete_visible.value = false;
		return;
	}

	if (is_rename_visible.value) {
		is_rename_visible.value = false;
		return;
	}

	if (is_tag_visible.value) {
		is_tag_visible.value = false;
		return;
	}

	if (is_copy_visible.value) {
		is_copy_visible.value = false;
		return;
	}

	if (is_move_visible.value) {
		is_move_visible.value = false;
		return;
	}

	goBack();
});

// We prevent the drag mechanism when a photo is loaded.
const can_upload = computed(() => (albumStore.rights?.can_upload ?? false) && photoStore.isLoaded === false);
const { onPaste, dragEnd, dropUpload } = useMouseEvents(can_upload, is_upload_visible, list_upload_files);

onMounted(() => {
	// Reset the slideshow.
	is_slideshow_active.value = false;

	window.addEventListener("paste", onPaste);
	window.addEventListener("dragover", dragEnd);
	window.addEventListener("drop", dropUpload);
});

onMounted(async () => {
	albumId.value = props.albumId;
	photoId.value = props.photoId;

	await load();
	setScroll();

	// if #upload is in the URL, open the upload modal
	if (window.location.hash === "#upload") {
		is_upload_visible.value = true;
	}
});

onUnmounted(() => {
	unselect();
	window.removeEventListener("paste", onPaste);
	window.removeEventListener("dragover", dragEnd);
	window.removeEventListener("drop", dropUpload);
});

const debouncedPhotoMetrics = useDebounceFn(() => {
	if (photoId.value !== undefined) {
		MetricsService.photo(photoId.value, getParentId());
		return;
	}
}, 100);

watch(
	() => [route.params.albumId, route.params.photoId],
	([newAlbumId, newPhotoId], _) => {
		unselect();

		photoStore.setTransition(newPhotoId as string | undefined);

		if (newAlbumId !== albumStore.albumId) {
			current_page.value = 1; // Reset the page if we change album
		}
		albumId.value = newAlbumId as string;
		photoId.value = newPhotoId as string;
		debouncedPhotoMetrics();

		if (photoId.value !== undefined) {
			togglableStore.rememberScrollThumb(photoId.value);
		}

		load().then(() => {
			if (photoId.value === undefined) {
				setScroll();
			}
		});
	},
);
</script>
