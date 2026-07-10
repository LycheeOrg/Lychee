<template>
	<LoadingProgress v-model:loading="tagStore.isLoading" />
	<LoginModal v-if="userStore.isGuest" @logged-in="refresh" />
	<WebauthnModal v-if="userStore.isGuest" @logged-in="refresh" />

	<TagPanel v-if="tagStore.tag !== undefined" @refresh="refresh" @go-back="goBack" />

	<!-- Photo panel -->
	<PhotoPanel
		v-if="photoStore.isLoaded"
		:is-map-visible="false"
		@toggle-slide-show="slideshow"
		@rotate-overlay="rotateOverlay"
		@rotate-photo-c-w="rotatePhotoCW"
		@rotate-photo-c-c-w="rotatePhotoCCW"
		@set-album-header="setAlbumHeader"
		@toggle-highlight="toggleHighlight"
		@toggle-move="toggleMove"
		@toggle-delete="toggleDelete"
		@updated="refresh"
		@go-back="goBack"
		@next="() => next(true)"
		@previous="() => previous(true)"
	/>

	<!-- Dialogs -->
	<template v-if="photoStore.isLoaded">
		<MoveDialog v-model:open="is_move_visible" :photo="photoStore.photo" @moved="refresh" />
		<DeleteDialog v-model:open="is_delete_visible" :photo="photoStore.photo" @deleted="refresh" />
	</template>
	<template v-else>
		<PhotoTagDialog
			v-model:open="is_tag_visible"
			:parent-id="undefined"
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
			v-model:open="is_license_visible"
			:parent-id="undefined"
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
			v-model:open="is_copy_visible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@copy="
				() => {
					unselect();
					refresh();
				}
			"
		/>
		<MoveDialog
			v-model:open="is_move_visible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@moved="
				() => {
					unselect();
					refresh();
				}
			"
		/>
		<DeleteDialog
			v-model:open="is_delete_visible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@deleted="
				() => {
					unselect();
					refresh();
				}
			"
		/>

		<!-- Dialogs for albums -->
		<RenameDialog
			v-model:open="is_rename_visible"
			:photo="selectedPhoto"
			@updated="
				() => {
					unselect();
					refresh();
				}
			"
		/>
	</template>
</template>
<script setup lang="ts">
import DeleteDialog from "@/v8/components/forms/gallery-dialogs/DeleteDialog.vue";
import MoveDialog from "@/v8/components/forms/gallery-dialogs/MoveDialog.vue";
import RenameDialog from "@/v8/components/forms/gallery-dialogs/RenameDialog.vue";
import PhotoCopyDialog from "@/v8/components/forms/photo/PhotoCopyDialog.vue";
import PhotoTagDialog from "@/v8/components/forms/photo/PhotoTagDialog.vue";
import PhotoLicenseDialog from "@/v8/components/forms/photo/PhotoLicenseDialog.vue";
import PhotoPanel from "@/v8/components/gallery/photoModule/PhotoPanel.vue";
import TagPanel from "@/v8/components/gallery/tagModule/TagPanel.vue";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import LoginModal from "@/v8/components/modals/LoginModal.vue";
import WebauthnModal from "@/v8/components/modals/WebauthnModal.vue";
import { usePhotoActions } from "@/composables/album/photoActions";
import { useScrollable } from "@/composables/album/scrollable";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useAdvisoryModal } from "@/composables/modals/useAdvisoryModal";
import { useAlbumRoute } from "@/composables/photo/albumRoute";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { useSelection } from "@/composables/selections/selections";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotosStore } from "@/stores/PhotosState";
import { usePhotoNsfwDetectionsStore } from "@/stores/PhotoNsfwDetectionsState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useTagStore } from "@/stores/TagState";
import { useUserStore } from "@/stores/UserState";
import { useLtRorRtL } from "@/utils/Helpers";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

const { isLTR } = useLtRorRtL();

const route = useRoute();
const router = useRouter();
const toast = useAppToast();
const { advisoryCheck } = useAdvisoryModal();
const { albumRoutes } = useAlbumRoute(router);

const props = defineProps<{
	tagId: string;
	photoId?: string;
}>();

const tagId = ref(props.tagId);
const tagStringId = computed(() => `tag-${tagId.value}`);
const photoId = ref(props.photoId);
const nullId = ref(undefined);

// unused? Hard to say...
const videoElement = ref<HTMLVideoElement | null>(null);

// flag to open login modal if necessary
const userStore = useUserStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();
const nsfwDetectionsStore = usePhotoNsfwDetectionsStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();
const layoutStore = useLayoutStore();
const tagStore = useTagStore();

const { are_nsfw_visible, slideshow_timeout, is_slideshow_enabled } = storeToRefs(lycheeStore);
const { is_photo_edit_open, are_details_open, is_slideshow_active } = storeToRefs(togglableStore);

const { setScroll } = useScrollable(togglableStore, tagStringId);

const { is_delete_visible, toggleDelete, is_move_visible, toggleMove, is_rename_visible, is_tag_visible, is_license_visible, is_copy_visible } =
	useGalleryModals(togglableStore);

const { toggleHighlight, rotatePhotoCCW, rotatePhotoCW, setAlbumHeader, rotateOverlay } = usePhotoActions(photoStore, nullId, toast, lycheeStore);

const { getNext, getPrevious } = getNextPreviousPhoto(router, photoStore);
const { slideshow, next, previous, stop } = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);

const { selectedPhoto, selectedPhotosIds, selectEverything, unselect, hasSelection } = useSelection(photosStore, albumsStore, togglableStore);

function goBack() {
	if (is_slideshow_active.value) {
		stop();
	}

	if (is_photo_edit_open.value === true) {
		is_photo_edit_open.value = false;
		return;
	}

	if (photoStore.photoId !== undefined) {
		photoStore.reset();
		router.push({ name: albumRoutes().album, params: { tagId: tagId.value } });
		return;
	}

	router.push({ name: albumRoutes().home });
}

function toggleDetails() {
	is_photo_edit_open.value = false;
	are_details_open.value = !are_details_open.value;
}

defineShortcuts({
	// Album operations
	h: () => {
		if (photoStore.isLoaded && lycheeStore.is_nsfw_classifier_enabled) {
			lycheeStore.cycleNsfwOverlayMode(nsfwDetectionsStore.get(photoStore.photo?.id ?? "").detections);
		} else {
			are_nsfw_visible.value = !are_nsfw_visible.value;
		}
	},
	f: () => togglableStore.toggleFullScreen(),
	" ": () => (photoStore.isLoaded ? is_slideshow_enabled.value && slideshow() : unselect()),
	meta_a: () => !photoStore.isLoaded && selectEverything(),

	// Photo operations (note that the arrow keys are flipped for RTL languages)
	arrowleft: () => photoStore.isLoaded && (isLTR() ? photoStore.hasPrevious && previous(true) : photoStore.hasNext && next(true)),
	arrowright: () => photoStore.isLoaded && (isLTR() ? photoStore.hasNext && next(true) : photoStore.hasPrevious && previous(true)),
	o: () => photoStore.isLoaded && rotateOverlay(),
	i: () => photoStore.isLoaded && toggleDetails(),

	// Privileges Photos view operations
	m: () => !photoStore.isLoaded && hasSelection() && toggleMove(),
	delete: () => (photoStore.isLoaded ? toggleDelete() : hasSelection() && toggleDelete()),
	backspace: () => (photoStore.isLoaded ? toggleDelete() : hasSelection() && toggleDelete()),

	// on key stroke escape:
	// 1. stop an active slideshow
	// 2. lose focus
	// 3. close modals
	// 4. go back
	escape: {
		usingInput: true,
		handler: () => {
			if (photoStore.isLoaded && is_slideshow_active.value) {
				stop();
			}

			// 2. lose focus
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

			goBack();
		},
	},
});

async function refresh() {
	await Promise.allSettled([userStore.load(), layoutStore.load(), lycheeStore.load(), tagStore.load()]);
	advisoryCheck();
	photoStore.load();
}

onMounted(async () => {
	photoStore.photoId = props.photoId;
	tagStore.tagId = props.tagId;

	await refresh();

	setScroll();
});

watch(
	() => route.params.photoId,
	(newPhotoId, _) => {
		unselect();

		photoStore.setTransition(newPhotoId as string | undefined);

		photoStore.photoId = newPhotoId as string;

		if (photoStore.photoId !== undefined) {
			togglableStore.rememberScrollThumb(photoStore.photoId);
		}

		refresh().then(() => {
			if (photoId.value === undefined) {
				setScroll();
			}
		});
	},
);
</script>
