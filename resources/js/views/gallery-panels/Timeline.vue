<template>
	<LoadingProgress v-model:loading="timelineStore.isLoading" />
	<LoginModal v-if="userStore.isGuest" @logged-in="refresh" />
	<WebauthnModal v-if="userStore.isGuest" @logged-in="refresh" />

	<div v-if="timelineStore.rootConfig && timelineStore.rootRights" class="h-svh overflow-y-auto" id="scrollArea">
		<Collapse :when="!is_full_screen">
			<TimelineHeader v-if="userStore.isLoaded" />
		</Collapse>
		<div v-if="timelineStore.minPage > 1" class="flex justify-center pt-2">
			<Button
				text
				icon="pi pi-angle-double-up"
				severity="secondary"
				@click="timelineStore.loadLess"
				:label="$t('gallery.timeline.load_previous')"
				v-if="!timelineStore.isLoading"
			/>
			<ProgressSpinner class="" v-if="timelineStore.isLoading && !isTouchDevice()" />
		</div>
		<PhotoThumbPanel
			v-if="layoutStore.config !== undefined && photosStore.photos.length > 0"
			header="gallery.album.header_photos"
			:photos="photosStore.photos"
			:photos-timeline="photosStore.photosTimeline"
			:selected-photos="selectedPhotosIds"
			@clicked="photoClick"
			@selected="selectPhoto"
			@contexted="contextMenuPhotoOpen"
			:is-timeline="true"
			:with-control="false"
			:pt:header:class="'hidden'"
			class="pt-4"
			:intersection-action="loadDate"
			:catalog="undefined"
		/>
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
			@go-back="goBack"
			@next="() => next(true)"
			@previous="() => previous(true)"
		/>
		<!-- @updated="refreshPhoto" -->

		<div class="sentinel" v-intersection-observer="onIntersectionObserver" v-if="timelineStore.maxPage < timelineStore.lastPage"></div>
		<ProgressSpinner class="flex justify-center" v-if="timelineStore.isLoading && !isTouchDevice()" />
		<TimelineDates :dates="timelineStore.dates" v-if="!photoStore.isLoaded" @load="goToDate" />
		<ScrollTop target="parent" :threshold="50" v-if="!photoStore.isLoaded" />

		<!-- Dialogs -->
		<template v-if="photoStore.isLoaded">
			<PhotoTagDialog
				v-model:visible="is_tag_visible"
				:photo="photoStore.photo"
				:parent-id="undefined"
				@tagged="
					() => {
						unselect();
						refresh();
					}
				"
			/>
			<PhotoCopyDialog
				v-model:visible="is_copy_visible"
				:photo="photoStore.photo"
				@copied="
					() => {
						unselect();
						refresh();
					}
				"
			/>
			<!-- <PhotoEdit v-if="albumStore.rights?.can_edit" v-model:is-edit-open="is_photo_edit_open" /> -->
			<MoveDialog :photo="photoStore.photo" v-model:visible="is_move_visible" :parent-id="'unsorted'" @moved="refresh" />
			<DeleteDialog :photo="photoStore.photo" v-model:visible="is_delete_visible" :parent-id="'unsorted'" @deleted="refresh" />
		</template>
		<template v-else>
			<!-- Dialogs -->
			<PhotoTagDialog
				v-model:visible="is_tag_visible"
				:parent-id="undefined"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				@tagged="refresh"
			/>
			<PhotoLicenseDialog
				v-model:visible="is_license_visible"
				:parent-id="undefined"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				@licensed="refresh"
			/>
			<PhotoCopyDialog v-model:visible="is_copy_visible" :photo="selectedPhoto" :photo-ids="selectedPhotosIds" @copied="refresh" />
			<MoveDialog
				v-model:visible="is_move_visible"
				:parent-id="undefined"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				:album="undefined"
				:album-ids="[]"
				@moved="refresh"
			/>
			<DeleteDialog
				v-model:visible="is_delete_visible"
				:parent-id="undefined"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				:album="undefined"
				:album-ids="[]"
				@deleted="refresh"
			/>
			<RenameDialog v-model:visible="is_rename_visible" :parent-id="undefined" :album="undefined" :photo="selectedPhoto" @renamed="refresh" />
		</template>
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
		<GalleryFooter v-once />
	</div>
</template>
<script setup lang="ts">
import { computed, nextTick } from "vue";
import { useUserStore } from "@/stores/UserState";
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
import ScrollTop from "primevue/scrolltop";
import { useTogglablesStateStore } from "@/stores/ModalsState";
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
import { usePhotoActions } from "@/composables/album/photoActions";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { useToast } from "primevue/usetoast";
import TimelineDates from "@/components/gallery/timelineModule/TimelineDates.vue";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoLicenseDialog from "@/components/forms/photo/PhotoLicenseDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
// import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { EmptyAlbumCallbacks } from "@/utils/Helpers";
import Divider from "primevue/divider";
import ContextMenu from "primevue/contextmenu";
import { useMouseEvents } from "@/composables/album/uploadEvents";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { onUnmounted } from "vue";
import { vIntersectionObserver } from "@vueuse/components";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useTimelineStore } from "@/stores/TimelineState";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	date?: string;
	photoId?: string;
}>();

const toast = useToast();
const route = useRoute();
const router = useRouter();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const photoStore = usePhotoStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();
const layoutStore = useLayoutStore();
const userStore = useUserStore();
const timelineStore = useTimelineStore();

// eslint-disable-next-line vue/no-dupe-keys
const { photoId } = storeToRefs(photoStore);

lycheeStore.load();

// unused? Hard to say...
const videoElement = ref<HTMLVideoElement | null>(null);

const { slideshow_timeout } = storeToRefs(lycheeStore);
const { is_full_screen, is_login_open, is_upload_visible, list_upload_files, is_slideshow_active, is_photo_edit_open, are_details_open } =
	storeToRefs(togglableStore);

const {
	selectedPhoto,
	selectedPhotos,
	selectedPhotosIds,
	photoSelect: selectPhoto,
	unselect,
} = useSelection(photosStore, albumsStore, togglableStore);

const { photoRoute } = usePhotoRoute(router);

function onIntersectionObserver([entry]: IntersectionObserverEntry[]) {
	if (entry.isIntersecting) {
		timelineStore.loadMore();
	}
}

function photoClick(photoId: string, _e: MouseEvent) {
	router.push(photoRoute(photoId));
}

const albumId = ref(undefined);
const { toggleHighlight, rotatePhotoCCW, rotatePhotoCW, setAlbumHeader, rotateOverlay } = usePhotoActions(photoStore, albumId, toast, lycheeStore);

const { getNext, getPrevious } = getNextPreviousPhoto(router, photoStore);
const { slideshow, next, previous, stop } = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);

function loadDate(date: string | null = null): void {
	if (date === null && router.currentRoute.value.params.date === undefined) {
		if (photosStore.photos.length === 0 || !photosStore.photos[0].timeline?.time_date) {
			console.warn("No timeline data available to set initial date");
			return;
		}

		// We push the first date of the timeline, this ensures that the timeline is always loaded with a date
		router.push({ name: "timeline", params: { date: photosStore.photos[0].timeline?.time_date } });
	}

	if (date) {
		router.push({ name: "timeline", params: { date } });
	}
}

function scrollToDate(date: string) {
	document.querySelector(`[data-date="${date}"]`)?.scrollIntoView({ behavior: "smooth", block: "start" });
}
function goToDate(date: string) {
	loadDate(date);
	timelineStore.initialLoad(date, undefined)?.then(() => scrollToDate(date));
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

function goBack() {
	if (is_slideshow_active.value) {
		stop();
	}

	if (is_photo_edit_open.value === true) {
		is_photo_edit_open.value = false;
		return;
	}

	if (photoStore.photo !== undefined) {
		loadDate(photoStore.photo?.timeline?.time_date);
		photoStore.reset();
		return;
	}
}

async function refresh() {
	await timelineStore.load();
	if (timelineStore.isTimelineEnabled !== true) {
		// Bye.
		router.push({ name: "gallery" });
	}
	await Promise.allSettled([layoutStore.load(), userStore.load(), timelineStore.loadDates()]);
	await timelineStore.initialLoad(props.date ?? "", props.photoId);
	await nextTick();
	scrollToDate(props.date ?? "");
}

onMounted(async () => {
	await refresh();
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
	is_license_visible,
	toggleLicense,
	is_copy_visible,
	toggleCopy,
} = useGalleryModals(togglableStore);

const photoCallbacks = {
	star: () => {
		PhotoService.highlight(selectedPhotosIds.value, true);
		AlbumService.clearCache();
		refresh();
	},
	unstar: () => {
		PhotoService.highlight(selectedPhotosIds.value, false);
		AlbumService.clearCache();
		refresh();
	},
	setAsCover: () => {},
	setAsHeader: () => {},
	toggleTag: toggleTag,
	toggleLicense: toggleLicense,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {},
	toggleApplyRenamer: () => {},
};

const {
	menu,
	Menu,
	photoMenuOpen: contextMenuPhotoOpen,
} = useContextMenu(
	{
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIds: selectedPhotosIds,
	},
	photoCallbacks,
	EmptyAlbumCallbacks,
);

const can_upload = computed(() => timelineStore.rootRights?.can_upload === true && photoStore.isLoaded === false);
const { onPaste, dragEnd, dropUpload } = useMouseEvents(can_upload, is_upload_visible, list_upload_files);

window.addEventListener("paste", onPaste);
window.addEventListener("dragover", dragEnd);
window.addEventListener("drop", dropUpload);

function cleanupEventListeners() {
	window.removeEventListener("paste", onPaste);
	window.removeEventListener("dragover", dragEnd);
	window.removeEventListener("drop", dropUpload);
}
router.afterEach(cleanupEventListeners);
onUnmounted(cleanupEventListeners);

function openSearch() {
	router.push({ name: "search" });
}

onKeyStroke("l", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && !userStore.isLoggedIn && (is_login_open.value = true));
onKeyStroke("/", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && timelineStore.rootConfig?.is_search_accessible && openSearch());
onKeyStroke("f", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && togglableStore.toggleFullScreen());
onKeyStroke("u", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && (is_upload_visible.value = true));

onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && isLTR() && photoStore.hasPrevious && previous(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && isLTR() && photoStore.hasNext && next(true));
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && !isLTR() && photoStore.hasNext && next(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && !isLTR() && photoStore.hasPrevious && previous(true));
onKeyStroke("o", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && rotateOverlay());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && slideshow());
onKeyStroke("i", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && toggleDetails());
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && togglableStore.toggleFullScreen());
onKeyStroke("Escape", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && is_slideshow_active.value && stop());

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
	if (is_slideshow_active.value) {
		return;
	}

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
	() => route.params.photoId,
	(newPhotoId, _) => {
		unselect();

		photoStore.setTransition(newPhotoId as string | undefined);

		photoStore.photoId = newPhotoId as string;
		photoStore.load();

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
