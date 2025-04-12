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
		:is-photo-open="photo !== undefined"
		@refresh="refresh"
		@toggle-slide-show="toggleSlideShow"
		@toggle-edit="toggleEdit"
		@open-search="openSearch"
		@go-back="goBack"
	/>

	<!-- Photo panel -->
	<PhotoPanel
		v-if="photo"
		:album-id="albumid"
		:photo="photo"
		:photos="photos"
		:is-map-visible="config?.is_map_accessible ?? false"
		@toggle-slide-show="slideshow"
		@rotate-overlay="rotateOverlay"
		@rotate-photo-c-w="rotatePhotoCW"
		@rotate-photo-c-c-w="rotatePhotoCCW"
		@set-album-header="setAlbumHeader"
		@toggle-star="toggleStar"
		@toggle-move="toggleMove"
		@toggle-delete="toggleDelete"
		@updated="refreshPhoto"
		@go-back="goBack"
		@next="() => next(true)"
		@previous="() => previous(true)"
	/>

	<!-- Dialogs -->
	<template v-if="photo">
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
		<PhotoEdit v-if="photo?.rights.can_edit" :photo="photo" v-model:visible="is_photo_edit_open" />
		<MoveDialog :photo="photo" v-model:visible="is_move_visible" :parent-id="props.albumid" @moved="refresh" />
		<DeleteDialog :photo="photo" v-model:visible="is_delete_visible" :parent-id="props.albumid" @deleted="refresh" />
	</template>
	<template v-else>
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
</template>
<script setup lang="ts">
import { useAuthStore } from "@/stores/Auth";
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke, useDebounceFn } from "@vueuse/core";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { useSelection } from "@/composables/selections/selections";
import { useAlbumRefresher } from "@/composables/album/albumRefresher";
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
import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import PhotoPanel from "@/components/gallery/photoModule/PhotoPanel.vue";
import { usePhotoActions } from "@/composables/album/photoActions";
import { useToast } from "primevue/usetoast";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { useHasNextPreviousPhoto } from "@/composables/photo/hasNextPreviousPhoto";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { usePhotoRefresher } from "@/composables/photo/hasRefresher";
import MetricsService from "@/services/metrics-service";

const route = useRoute();
const router = useRouter();
const toast = useToast();

const props = defineProps<{
	albumid: string;
	photoid?: string;
}>();

const albumId = ref(props.albumid);
const photoId = ref(props.photoid);

// unused? Hard to say...
const videoElement = ref<HTMLVideoElement | null>(null);

// flag to open login modal if necessary
const auth = useAuthStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();

lycheeStore.init();

const { are_nsfw_visible, slideshow_timeout } = storeToRefs(lycheeStore);
const {
	is_photo_edit_open,
	are_details_open,
	is_login_open,
	is_slideshow_active,
	is_upload_visible,
	list_upload_files,
	is_create_album_visible,
	is_album_edit_open,
} = storeToRefs(togglableStore);

const { scrollToTop, setScroll } = useScrollable(togglableStore, albumId);

const { is_delete_visible, toggleDelete, is_merge_album_visible, is_move_visible, toggleMove, is_rename_visible, is_tag_visible, is_copy_visible } =
	useGalleryModals(togglableStore);

// Set up Album ID reference. This one is updated at each page change.
const { isPasswordProtected, isLoading, user, modelAlbum, album, photo, rights, photos, config, refresh } = useAlbumRefresher(
	albumId,
	photoId,
	auth,
	is_login_open,
);
const { refreshPhoto } = usePhotoRefresher(photo, photos, photoId);

const children = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(() => modelAlbum.value?.albums ?? []);

const { toggleStar, rotatePhotoCCW, rotatePhotoCW, setAlbumHeader, rotateOverlay } = usePhotoActions(photo, albumId, toast, lycheeStore);

const { getNext, getPrevious } = getNextPreviousPhoto(router, albumId, photo);
const { slideshow, next, previous, stop } = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);
const { hasNext, hasPrevious } = useHasNextPreviousPhoto(photo);

function toggleSlideShow() {
	if (album.value === undefined || album.value.photos.length === 0) {
		return;
	}

	slideshow();
	router.push({ name: "photo", params: { albumid: album.value.id, photoid: album.value.photos[0].id } });
}

const { layoutConfig, loadLayoutConfig } = useGetLayoutConfig();

const { selectedPhoto, selectedAlbum, selectedPhotosIds, selectedAlbumsIds, selectEverything, unselect, hasSelection } = useSelection(
	photos,
	children,
	togglableStore,
);

function goBack() {
	if (is_slideshow_active.value) {
		stop();
	}

	if (is_photo_edit_open.value === true) {
		is_photo_edit_open.value = false;
		return;
	}

	if (photoId.value !== undefined) {
		photoId.value = undefined;
		photo.value = undefined;
		router.push({ name: "album", params: { albumid: albumId.value } });
		return;
	}

	is_album_edit_open.value = false;
	if (modelAlbum.value !== undefined && modelAlbum.value.parent_id !== null) {
		router.push({ name: "album", params: { albumid: modelAlbum.value.parent_id } });
	} else {
		router.push({ name: "gallery" });
	}
}

function toggleDetails() {
	is_photo_edit_open.value = false;
	are_details_open.value = !are_details_open.value;
}

function toggleEdit() {
	if (photo.value !== undefined) {
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
	if (album.value === undefined) {
		return;
	}
	router.push({ name: "search-with-album", params: { albumid: album.value?.id } });
}

// Album operations
onKeyStroke("h", () => !shouldIgnoreKeystroke() && photo.value === undefined && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photo.value === undefined && togglableStore.toggleFullScreen());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photo.value === undefined && unselect());
onKeyStroke("n", () => !shouldIgnoreKeystroke() && photo.value === undefined && (is_create_album_visible.value = true));
onKeyStroke("u", () => !shouldIgnoreKeystroke() && photo.value === undefined && (is_upload_visible.value = true));
onKeyStroke("i", () => !shouldIgnoreKeystroke() && photo.value === undefined && toggleEdit());
onKeyStroke("l", () => !shouldIgnoreKeystroke() && photo.value === undefined && user.value?.id === null && (is_login_open.value = true));
onKeyStroke("/", () => !shouldIgnoreKeystroke() && photo.value === undefined && config.value?.is_search_accessible && openSearch());
onKeyStroke([getModKey(), "a"], () => !shouldIgnoreKeystroke() && photo.value === undefined && selectEverything());

// Photo operations
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photo.value !== undefined && hasPrevious() && previous(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photo.value !== undefined && hasNext() && next(true));
onKeyStroke("o", () => !shouldIgnoreKeystroke() && photo.value !== undefined && rotateOverlay());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photo.value !== undefined && slideshow());
onKeyStroke("i", () => !shouldIgnoreKeystroke() && photo.value !== undefined && toggleDetails());
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photo.value !== undefined && togglableStore.toggleFullScreen());
onKeyStroke("Escape", () => !shouldIgnoreKeystroke() && photo.value !== undefined && is_slideshow_active.value && stop());

// Privileged Album actions
onKeyStroke("m", () => !shouldIgnoreKeystroke() && photo.value === undefined && album.value?.rights.can_move && hasSelection() && toggleMove());
onKeyStroke(
	["Delete", "Backspace"],
	() => !shouldIgnoreKeystroke() && photo.value === undefined && album.value?.rights.can_delete && hasSelection() && toggleDelete(),
);

// Priviledged Photo operations
onKeyStroke("m", () => !shouldIgnoreKeystroke() && photo.value !== undefined && photo.value.rights.can_edit && toggleMove());
onKeyStroke("e", () => !shouldIgnoreKeystroke() && photo.value !== undefined && photo.value.rights.can_edit && toggleEdit());
onKeyStroke("s", () => !shouldIgnoreKeystroke() && photo.value !== undefined && photo.value.rights.can_edit && toggleStar());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && photo.value !== undefined && album.value?.rights.can_delete && toggleDelete());

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

	if (are_details_open.value) {
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

const debouncedPhotoMetrics = useDebounceFn(() => {
	if (photoId.value !== undefined) {
		MetricsService.photo(photoId.value);
		return;
	}
}, 100);

watch(
	() => [route.params.albumid, route.params.photoid],
	([newAlbumId, newPhotoId], _) => {
		unselect();

		albumId.value = newAlbumId as string;
		photoId.value = newPhotoId as string;
		debouncedPhotoMetrics();

		if (photoId.value !== undefined) {
			togglableStore.rememberScrollThumb(photoId.value);
		}

		refresh().then(() => {
			if (photoId.value === undefined) {
				setScroll();
			}
		});
	},
);
</script>
