<template>
	<LoadingProgress v-model:loading="isLoading" />
	<LoginModal v-if="user?.id === null" @logged-in="refresh" />
	<WebauthnModal v-if="user?.id === null" @logged-in="refresh" />

	<TagPanel
		v-if="tag && layoutConfig"
		:tag="tag.name"
		:photos="photos"
		:user="user"
		:photo-layout="photoLayout"
		:layout-config="layoutConfig"
		:is-photo-open="false"
		@refresh="refresh"
		@go-back="goBack"
	/>

	<!-- Photo panel -->
	<PhotoPanel
		v-if="photo"
		:photo="photo"
		:photos="photos"
		:is-map-visible="false"
		:transition="transition"
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
		<PhotoEdit v-if="photo?.rights.can_edit" v-model:visible="is_photo_edit_open" :photo="photo" />
		<MoveDialog v-model:visible="is_move_visible" :photo="photo" @moved="refresh" />
		<DeleteDialog v-model:visible="is_delete_visible" :photo="photo" @deleted="refresh" />
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
import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
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
import { useHasNextPreviousPhoto } from "@/composables/photo/hasNextPreviousPhoto";
import { usePhotoRefresher } from "@/composables/photo/hasRefresher";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { useSelection } from "@/composables/selections/selections";
import { useTagRefresher } from "@/composables/tags/tagRefresher";
import { useGetLayoutConfig } from "@/layouts/PhotoLayout";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
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
const nullId = ref(null);

// unused? Hard to say...
const videoElement = ref<HTMLVideoElement | null>(null);

// flag to open login modal if necessary
const auth = useAuthStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();

lycheeStore.init();
const { are_nsfw_visible, slideshow_timeout, is_slideshow_enabled } = storeToRefs(lycheeStore);
const { is_photo_edit_open, are_details_open, is_login_open, is_slideshow_active } = storeToRefs(togglableStore);

const { setScroll } = useScrollable(togglableStore, tagStringId);

const { is_delete_visible, toggleDelete, is_move_visible, toggleMove, is_rename_visible, is_tag_visible, is_copy_visible } =
	useGalleryModals(togglableStore);

// Set up Album ID reference. This one is updated at each page change.
const { isLoading, user, tag, photo, transition, photos, photoLayout, refresh, setTransition } = useTagRefresher(tagId, photoId, auth, is_login_open);

const { refreshPhoto } = usePhotoRefresher(photo, photos, photoId);
// const { getParentId } = usePhotoRoute(router);

const { toggleStar, rotatePhotoCCW, rotatePhotoCW, setAlbumHeader, rotateOverlay } = usePhotoActions(photo, nullId, toast, lycheeStore);

const { getNext, getPrevious } = getNextPreviousPhoto(router, photo);
const { slideshow, next, previous, stop } = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);
const { hasNext, hasPrevious } = useHasNextPreviousPhoto(photo);

const { layoutConfig, loadLayoutConfig } = useGetLayoutConfig();

const { selectedPhoto, selectedPhotosIds, selectEverything, unselect, hasSelection } = useSelection({ photos }, togglableStore);

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

		router.push({ name: albumRoutes().album, params: { tagId: tagId.value } });
		return;
	}

	router.push({ name: albumRoutes().home });
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
}

// Album operations
onKeyStroke("h", () => !shouldIgnoreKeystroke() && photo.value === undefined && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photo.value === undefined && togglableStore.toggleFullScreen());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photo.value === undefined && unselect());
onKeyStroke("a", (e) => {
	if (!shouldIgnoreKeystroke() && photo.value === undefined && e.getModifierState(getModKey()) && !e.shiftKey && !e.altKey) {
		e.preventDefault();
		selectEverything();
	}
});

// Photo operations (note that the arrow keys are flipped for RTL languages)
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photo.value !== undefined && isLTR() && hasPrevious() && previous(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photo.value !== undefined && isLTR() && hasNext() && next(true));
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photo.value !== undefined && !isLTR() && hasNext() && next(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photo.value !== undefined && !isLTR() && hasPrevious() && previous(true));
onKeyStroke("o", () => !shouldIgnoreKeystroke() && photo.value !== undefined && rotateOverlay());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photo.value !== undefined && is_slideshow_enabled.value && slideshow());
onKeyStroke("i", () => !shouldIgnoreKeystroke() && photo.value !== undefined && toggleDetails());
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photo.value !== undefined && togglableStore.toggleFullScreen());
onKeyStroke("Escape", () => !shouldIgnoreKeystroke() && photo.value !== undefined && is_slideshow_active.value && stop());

// Privileges Photos view operations
onKeyStroke("m", () => !shouldIgnoreKeystroke() && photo.value === undefined && hasSelection() && toggleMove());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && photo.value === undefined && hasSelection() && toggleDelete());

// Priviledged Photo operations
onKeyStroke("m", () => !shouldIgnoreKeystroke() && photo.value !== undefined && photo.value.rights.can_edit && toggleMove());
onKeyStroke("e", () => !shouldIgnoreKeystroke() && photo.value !== undefined && photo.value.rights.can_edit && toggleEdit());
onKeyStroke("s", () => !shouldIgnoreKeystroke() && photo.value !== undefined && photo.value.rights.can_edit && toggleStar());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && photo.value !== undefined && toggleDelete());

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

watch(
	() => route.params.photoId,
	(newPhotoId, _) => {
		unselect();

		setTransition(newPhotoId as string | undefined);

		photoId.value = newPhotoId as string;
		// debouncedPhotoMetrics();

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
