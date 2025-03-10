<template>
	<LoadingProgress v-model:loading="isLoading" />
	<LoginModal v-if="user?.id === null" />
	<WebauthnModal v-if="user?.id === null" />

	<div v-if="rootConfig && rootRights" class="h-svh overflow-y-auto">
		<Collapse :when="!is_full_screen">
			<TimelineHeader v-if="user" :user="user" :title="title" :rights="rootRights" :config="rootConfig" :has-hidden="false" />
		</Collapse>
		<div v-if="minPage > 1" class="flex justify-center pt-2">
			<Button
				text
				icon="pi pi-angle-double-up"
				severity="secondary"
				@click="loadLess"
				:label="$t('gallery.timeline.load_previous')"
				v-if="!isLoading"
			/>
			<ProgressSpinner class="" v-if="isLoading && !isTouchDevice()" />
		</div>
		<PhotoThumbPanel
			v-if="layoutConfig !== undefined && photos !== null && photos.length > 0"
			header="gallery.album.header_photos"
			:photos="photos"
			:album="undefined"
			:gallery-config="layoutConfig"
			:photo-layout="layout"
			:selected-photos="selectedPhotosIds"
			@clicked="photoClick"
			@selected="photoSelect"
			:is-timeline="true"
			:with-control="false"
		/>
		<!-- Photo panel -->
		<PhotoPanel
			v-if="photo"
			:photo="photo"
			:photos="photos"
			:album-id="photo?.album_id ?? 'unsorted'"
			:is-map-visible="rootConfig?.is_map_accessible ?? false"
			@toggle-slide-show="slideshow"
			@rotate-overlay="rotateOverlay"
			@rotate-photo-c-w="rotatePhotoCW"
			@rotate-photo-c-c-w="rotatePhotoCCW"
			@set-album-header="setAlbumHeader"
			@toggle-star="toggleStar"
			@toggle-move="toggleMove"
			@toggle-delete="toggleDelete"
			@go-back="goBack"
			@next="() => next(true)"
			@previous="() => previous(true)"
		/>
		<!-- @updated="refreshPhoto" -->

		<div class="sentinel" ref="sentinel" v-if="maxPage < lastPage"></div>
		<ProgressSpinner class="flex justify-center" v-if="isLoading && !isTouchDevice()" />
		<TimelineDates :dates="dates" v-if="photo === undefined" @load="goToDate" />
		<ScrollTop target="parent" :threshold="50" v-if="photo === undefined" />
		<!-- Dialogs -->
		<!-- <PhotoTagDialog
			v-model:visible="is_tag_visible"
			:parent-id="undefined"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@tagged="refresh"
		/>
		<PhotoCopyDialog
			v-model:visible="is_copy_visible"
			:parent-id="undefined"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@copied="refresh"
		/>
		<MoveDialog
			v-model:visible="is_move_visible"
			:parent-id="undefined"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="undefined"
			:album-ids="[]"
			@moved="refresh"
		/> -->
		<!-- <DeleteDialog
			v-model:visible="is_delete_visible"
			:parent-id="undefined"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="undefined"
			:album-ids="[]"
			@deleted="refresh"
		/> -->
		<!-- <RenameDialog v-model:visible="is_rename_visible" :parent-id="undefined" :album="undefined" :photo="selectedPhoto" @renamed="refresh" /> -->

		<!-- <ContextMenu ref="menu" :model="Menu" :class="Menu.length === 0 ? 'hidden' : ''">
			<template #item="{ item, props }">
				<Divider v-if="item.is_divider" />
				<a v-else v-ripple v-bind="props.action" @click="item.callback">
					<span :class="item.icon" />
					<span class="ml-2">
						{{ $t(item.label) }}
					</span>
				</a>
			</template>
		</ContextMenu> -->
		<GalleryFooter v-once />
	</div>
</template>
<script setup lang="ts">
import { useAuthStore } from "@/stores/Auth";
import { ref } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { onKeyStroke } from "@vueuse/core";
import { isTouchDevice, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { useSelection } from "@/composables/selections/selections";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { Collapse } from "vue-collapsed";
import { useRoute, useRouter } from "vue-router";
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import { useGetLayoutConfig } from "@/layouts/PhotoLayout";
import ScrollTop from "primevue/scrolltop";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useTimelineRefresher } from "@/composables/timeline/timelineRefresher";
import PhotoThumbPanel from "@/components/gallery/albumModule/PhotoThumbPanel.vue";
import TimelineHeader from "@/components/headers/TimelineHeader.vue";
import { onMounted } from "vue";
import ProgressSpinner from "primevue/progressspinner";
import PhotoPanel from "@/components/gallery/photoModule/PhotoPanel.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import LoginModal from "@/components/modals/LoginModal.vue";
import WebauthnModal from "@/components/modals/WebauthnModal.vue";
import { watch } from "vue";
import Button from "primevue/button";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useRouteDateUpdater } from "@/composables/timeline/routeDateUpdater";
import { usePhotoActions } from "@/composables/album/photoActions";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { useHasNextPreviousPhoto } from "@/composables/photo/hasNextPreviousPhoto";
import { useToast } from "primevue/usetoast";
import TimelineDates from "@/components/gallery/timelineModule/TimelineDates.vue";

const props = defineProps<{
	date?: string;
	photoId?: string;
}>();

const toast = useToast();
const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const photoId = ref<undefined | string>(props.photoId);
lycheeStore.init();

// unused? Hard to say...
const videoElement = ref<HTMLVideoElement | null>(null);
const albumId = ref(null);

const { are_nsfw_visible, title, slideshow_timeout } = storeToRefs(lycheeStore);
const { is_full_screen, is_login_open, is_upload_visible, list_upload_files, is_slideshow_active, is_photo_edit_open, are_details_open } =
	storeToRefs(togglableStore);

const albums = ref([]); // unused.

const { layoutConfig, loadLayoutConfig } = useGetLayoutConfig();
const {
	user,
	dates,
	loadUser,
	rootConfig,
	rootRights,
	minPage,
	maxPage,
	lastPage,
	photos,
	photo,
	layout,
	isTimelineEnabled,
	loadTimelineConfig,
	initialLoad,
	loadLess,
	loadMore,
	loadDate,
	loadDates,
	loadPhoto,
	isLoading,
} = useTimelineRefresher(photoId, router, auth);

const { selectedPhotosIdx, selectedPhoto, selectedPhotos, selectedPhotosIds, photoSelect, hasSelection, unselect, selectEverything } = useSelection(
	photos,
	albums,
	togglableStore,
);

const { photoRoute } = usePhotoRoute(router);

const sentinel = ref(null);

const { registerSentinel, registerScrollSpy } = useRouteDateUpdater(sentinel, loadMore, loadDate);

function photoClick(idx: number, e: MouseEvent) {
	router.push(photoRoute(photos.value[idx].id));
}

const { toggleStar, rotatePhotoCCW, rotatePhotoCW, setAlbumHeader, rotateOverlay } = usePhotoActions(photo, albumId, toast, lycheeStore);

const { getNext, getPrevious } = getNextPreviousPhoto(router, photo);
const { slideshow, next, previous, stop } = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);
const { hasNext, hasPrevious } = useHasNextPreviousPhoto(photo);

function goToDate(date: string) {
	loadDate(date);
	initialLoad(date, undefined);
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

function goBack() {
	if (is_slideshow_active.value) {
		stop();
	}

	if (is_photo_edit_open.value === true) {
		is_photo_edit_open.value = false;
		return;
	}

	if (photoId.value !== undefined) {
		loadDate(photo.value?.timeline?.timeDate);
		photoId.value = undefined;
		photo.value = undefined;
		return;
	}
}

onMounted(async () => {
	await loadTimelineConfig();

	if (!isTimelineEnabled.value) {
		// Bye.
		router.push({ name: "gallery" });
	}

	await loadLayoutConfig();
	loadUser();
	loadDates();
	await initialLoad(props.date ?? "", props.photoId);
	registerSentinel();
	registerScrollSpy();
});

// Modals for Albums
const {
	is_delete_visible,
	toggleDelete,
	is_move_visible,
	toggleMove,
	is_rename_visible,
	toggleRename,
	is_tag_visible,
	toggleTag,
	is_copy_visible,
	toggleCopy,
} = useGalleryModals(togglableStore);

// const photoCallbacks = {
// 	star: () => {
// 		PhotoService.star(selectedPhotosIds.value, true);
// 		AlbumService.clearCache();
// 		refresh();
// 	},
// 	unstar: () => {
// 		PhotoService.star(selectedPhotosIds.value, false);
// 		AlbumService.clearCache();
// 		refresh();
// 	},
// 	setAsCover: () => {},
// 	setAsHeader: () => {},
// 	toggleTag: toggleTag,
// 	toggleRename: toggleRename,
// 	toggleCopyTo: toggleCopy,
// 	toggleMove: toggleMove,
// 	toggleDelete: toggleDelete,
// 	toggleDownload: () => {},
// };

// const { menu, Menu, photoMenuOpen } = useContextMenu(
// 	{
// 		selectedPhoto: selectedPhoto,
// 		selectedPhotos: selectedPhotos,
// 		selectedPhotosIdx: selectedPhotosIdx,
// 	},
// 	photoCallbacks,
// 	EmptyAlbumCallbacks,
// );

// refresh();

// onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
// onKeyStroke("f", () => !shouldIgnoreKeystroke() && togglableStore.toggleFullScreen());
// onKeyStroke(" ", () => !shouldIgnoreKeystroke() && unselect());
// onKeyStroke("m", () => !shouldIgnoreKeystroke() && rootRights.value?.can_edit && hasSelection() && toggleMove());
// onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && rootRights.value?.can_edit && hasSelection() && toggleDelete());

// onKeyStroke([getModKey(), "a"], () => !shouldIgnoreKeystroke() && selectEverything());

// const { onPaste, dragEnd, dropUpload } = useMouseEvents(rootRights, is_upload_visible, list_upload_files);

// window.addEventListener("paste", onPaste);
// window.addEventListener("dragover", dragEnd);
// window.addEventListener("drop", dropUpload);
// router.afterEach(() => {
// 	window.removeEventListener("paste", onPaste);
// 	window.removeEventListener("dragover", dragEnd);
// 	window.removeEventListener("drop", dropUpload);
// });

onKeyStroke("f", () => !shouldIgnoreKeystroke() && photo.value === undefined && togglableStore.toggleFullScreen());
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photo.value !== undefined && hasPrevious() && previous(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photo.value !== undefined && hasNext() && next(true));
onKeyStroke("o", () => !shouldIgnoreKeystroke() && photo.value !== undefined && rotateOverlay());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photo.value !== undefined && slideshow());
onKeyStroke("i", () => !shouldIgnoreKeystroke() && photo.value !== undefined && toggleDetails());
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photo.value !== undefined && togglableStore.toggleFullScreen());
onKeyStroke("Escape", () => !shouldIgnoreKeystroke() && photo.value !== undefined && is_slideshow_active.value && stop());

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

watch(
	() => [route.params.photoId],
	([newPhotoId], _) => {
		unselect();

		photoId.value = newPhotoId as string;
		loadPhoto();
		if (photoId.value !== undefined) {
			togglableStore.rememberScrollThumb(photoId.value);
		}
	},
);
</script>
<style lang="css">
/* Kill the border of ScrollTop */
.p-scrolltop {
	border: none;
}
</style>
