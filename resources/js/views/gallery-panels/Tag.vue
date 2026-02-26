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
		@toggle-star="toggleHighlight"
		@toggle-move="toggleMove"
		@toggle-delete="toggleDelete"
		@updated="refresh"
		@go-back="goBack"
		@next="() => next(true)"
		@previous="() => previous(true)"
	/>

	<!-- Dialogs -->
	<template v-if="photoStore.isLoaded">
		<!-- <PhotoEdit v-if="photoStore.rights?.can_edit" v-model:visible="is_photo_edit_open" /> -->
		<MoveDialog v-model:visible="is_move_visible" :photo="photoStore.photo" @moved="refresh" />
		<DeleteDialog v-model:visible="is_delete_visible" :photo="photoStore.photo" @deleted="refresh" />
	</template>
	<template v-else>
		<PhotoTagDialog
			v-model:visible="is_tag_visible"
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
			v-model:visible="is_license_visible"
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
			:photo="selectedPhoto"
			@renamed="
				() => {
					unselect();
					refresh();
				}
			"
		/>
	</template>
</template>
<script setup lang="ts">
// import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoLicenseDialog from "@/components/forms/photo/PhotoLicenseDialog.vue";
import PhotoPanel from "@/components/gallery/photoModule/PhotoPanel.vue";
import TagPanel from "@/components/gallery/tagModule/TagPanel.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import LoginModal from "@/components/modals/LoginModal.vue";
import WebauthnModal from "@/components/modals/WebauthnModal.vue";
import { usePhotoActions } from "@/composables/album/photoActions";
import { useScrollable } from "@/composables/album/scrollable";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useAlbumRoute } from "@/composables/photo/albumRoute";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { useSelection } from "@/composables/selections/selections";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotosStore } from "@/stores/PhotosState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useTagStore } from "@/stores/TagState";
import { useUserStore } from "@/stores/UserState";
import { useLtRorRtL } from "@/utils/Helpers";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { onKeyStroke } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { useToast } from "primevue/usetoast";
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

const { isLTR } = useLtRorRtL();

const route = useRoute();
const router = useRouter();
const toast = useToast();
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

	if (photoId.value !== undefined) {
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

// function toggleEdit() {
// 	if (photoStore.isLoaded) {
// 		are_details_open.value = false;
// 		is_photo_edit_open.value = !is_photo_edit_open.value;
// 		return;
// 	}
// }

// Album operations
onKeyStroke("h", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && togglableStore.toggleFullScreen());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && unselect());
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

// Privileges Photos view operations
onKeyStroke("m", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && hasSelection() && toggleMove());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && hasSelection() && toggleDelete());

// Priviledged Photo operations
// onKeyStroke("m", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && photoStore.rights?.can_edit && toggleMove());
// onKeyStroke("e", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && photoStore.rights?.can_edit && toggleEdit());
// onKeyStroke("s", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && photoStore.rights?.can_edit && toggleHighlight());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && photoStore.isLoaded && toggleDelete());

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

async function refresh() {
	await Promise.allSettled([userStore.load(), layoutStore.load(), lycheeStore.load(), tagStore.load()]);

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
		// debouncedPhotoMetrics();

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
