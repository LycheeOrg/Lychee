<template>
	<LoadingProgress v-model:loading="timelineStore.isLoading" />
	<LoginModal v-if="userStore.isGuest" @logged-in="onLoggedIn" />
	<WebauthnModal v-if="userStore.isGuest" @logged-in="onLoggedIn" />
	<CameraCapture v-if="timelineStore.rootRights?.can_upload" key="camera_capture_modal" />

	<UContextMenu :items="menuSections" :disabled="photosStore.photos.length === 0" class="contents">
		<div v-if="timelineStore.rootConfig && timelineStore.rootRights" class="h-svh overflow-y-auto" id="scrollArea">
			<Collapse :when="!is_full_screen">
				<TimelineHeader v-if="userStore.isLoaded" />
			</Collapse>
			<div v-if="timelineStore.minPage > 1" class="flex justify-center pt-2">
				<UButton
					variant="ghost"
					icon="prime:angle-double-up"
					color="neutral"
					@click="timelineStore.loadLess"
					:label="$t('gallery.timeline.load_previous')"
					v-if="!timelineStore.isLoading"
				/>
				<Spinner class="text-2xl" v-if="timelineStore.isLoading && !isTouchDevice()" />
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
				class="pt-4"
				:intersection-action="loadDate"
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

			<div class="sentinel" v-intersection-observer="onIntersectionObserver" v-if="timelineStore.maxPage < timelineStore.lastPage"></div>
			<Spinner class="flex justify-center text-2xl" v-if="timelineStore.isLoading && !isTouchDevice()" />
			<TimelineDates :dates="timelineStore.dates" v-if="!photoStore.isLoaded" @load="goToDate" />

			<!-- Dialogs -->
			<template v-if="photoStore.isLoaded">
				<PhotoTagDialog
					v-model:open="is_tag_visible"
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
					v-model:open="is_copy_visible"
					:photo="photoStore.photo"
					@copy="
						() => {
							unselect();
							refresh();
						}
					"
				/>
				<MoveDialog :photo="photoStore.photo" v-model:open="is_move_visible" @moved="refresh" />
				<DeleteDialog :photo="photoStore.photo" v-model:open="is_delete_visible" @deleted="refresh" />
			</template>
			<template v-else>
				<!-- Dialogs -->
				<PhotoTagDialog
					v-model:open="is_tag_visible"
					:parent-id="undefined"
					:photo="selectedPhoto"
					:photo-ids="selectedPhotosIds"
					@tagged="refresh"
				/>
				<PhotoLicenseDialog
					v-model:open="is_license_visible"
					:parent-id="undefined"
					:photo="selectedPhoto"
					:photo-ids="selectedPhotosIds"
					@licensed="refresh"
				/>
				<PhotoCopyDialog v-model:open="is_copy_visible" :photo="selectedPhoto" :photo-ids="selectedPhotosIds" @copy="refresh" />
				<MoveDialog
					v-model:open="is_move_visible"
					:photo="selectedPhoto"
					:photo-ids="selectedPhotosIds"
					:album="undefined"
					:album-ids="[]"
					@moved="refresh"
				/>
				<DeleteDialog
					v-model:open="is_delete_visible"
					:photo="selectedPhoto"
					:photo-ids="selectedPhotosIds"
					:album="undefined"
					:album-ids="[]"
					@deleted="refresh"
				/>
				<RenameDialog v-model:open="is_rename_visible" :album="undefined" :photo="selectedPhoto" @updated="refresh" />
			</template>
			<DownloadAlbum v-model:open="is_download_photo_visible" :photo-ids="downloadPhotoIds" />
			<GalleryFooter v-once />
		</div>
	</UContextMenu>
</template>
<script setup lang="ts">
import { computed, nextTick } from "vue";
import { useUserStore } from "@/stores/UserState";
import { ref } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { isTouchDevice, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { useSelection } from "@/composables/selections/selections";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useAdvisoryModal } from "@/composables/modals/useAdvisoryModal";
import { Collapse } from "vue-collapsed";
import { useRoute, useRouter } from "vue-router";
import GalleryFooter from "@/v8/components/footers/GalleryFooter.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import PhotoThumbPanel from "@/v8/components/gallery/albumModule/PhotoThumbPanel.vue";
import TimelineHeader from "@/v8/components/headers/TimelineHeader.vue";
import CameraCapture from "@/v8/components/modals/CameraCapture.vue";
import { onMounted } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
import PhotoPanel from "@/v8/components/gallery/photoModule/PhotoPanel.vue";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import LoginModal from "@/v8/components/modals/LoginModal.vue";
import WebauthnModal from "@/v8/components/modals/WebauthnModal.vue";
import { watch } from "vue";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { usePhotoActions } from "@/composables/album/photoActions";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { useAppToast } from "@/v8/composables/useAppToast";
import TimelineDates from "@/v8/components/gallery/timelineModule/TimelineDates.vue";
import PhotoTagDialog from "@/v8/components/forms/photo/PhotoTagDialog.vue";
import PhotoLicenseDialog from "@/v8/components/forms/photo/PhotoLicenseDialog.vue";
import PhotoCopyDialog from "@/v8/components/forms/photo/PhotoCopyDialog.vue";
import MoveDialog from "@/v8/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/v8/components/forms/gallery-dialogs/DeleteDialog.vue";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import ModerationService from "@/services/moderation-service";
import { EmptyAlbumCallbacks } from "@/utils/Helpers";
import { useMouseEvents } from "@/v8/composables/album/uploadEvents";
import RenameDialog from "@/v8/components/forms/gallery-dialogs/RenameDialog.vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { onUnmounted } from "vue";
import { vIntersectionObserver } from "@vueuse/components";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useTimelineStore } from "@/stores/TimelineState";
import DownloadAlbum from "@/v8/components/modals/DownloadAlbum.vue";
import { trans } from "laravel-vue-i18n";
import type { ContextMenuItem } from "@nuxt/ui";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	date?: string;
	photoId?: string;
}>();

const toast = useAppToast();
const route = useRoute();
const router = useRouter();
const { advisoryCheck } = useAdvisoryModal();
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
const {
	is_full_screen,
	is_login_open,
	is_upload_visible,
	list_upload_files,
	upload_config,
	is_slideshow_active,
	is_photo_edit_open,
	are_details_open,
} = storeToRefs(togglableStore);

const {
	selectedPhoto,
	selectedPhotos,
	selectedPhotosIds,
	photoSelect: selectPhoto,
	unselect,
} = useSelection(photosStore, albumsStore, togglableStore);

const { photoRoute } = usePhotoRoute(router);

const is_download_photo_visible = ref(false);
const downloadPhotoIds = ref<string[]>([]);

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

async function onLoggedIn() {
	await refresh();
	advisoryCheck();
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
	toggleDownload: () => {
		downloadPhotoIds.value = [...selectedPhotosIds.value];
		is_download_photo_visible.value = true;
	},
	toggleApplyRenamer: () => {},
	toggleScanFaces: () => {},
	toggleApprove: () => {
		ModerationService.approve(selectedPhotosIds.value).then(() => {
			selectedPhotosIds.value.forEach((photoId) => {
				const photo = photosStore.photos.find((p) => p.id === photoId);
				if (photo) {
					photo.is_validated = true;
				}
			});
		});
	},
};

const { Menu } = useContextMenu(
	{
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIds: selectedPhotosIds,
	},
	photoCallbacks,
	EmptyAlbumCallbacks,
);

// See AlbumPanel.vue for why the composable's imperative photoMenuOpen is bypassed in favor of
// a declarative UContextMenu wrapping the gallery view, and why `:disabled` above must not
// depend on `Menu.length` (that races against the selection side-effect this handler
// performs on the very same contextmenu event).
function contextMenuPhotoOpen(photoId: string, _e: MouseEvent): void {
	if (!selectedPhotosIds.value.includes(photoId)) {
		selectedPhotosIds.value = [photoId];
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

const can_upload = computed(() => timelineStore.rootRights?.can_upload === true && photoStore.isLoaded === false);
const timeline_parent_id = ref<string | null>(null);
const timeline_existing_albums = ref<{ id: string; title: string }[]>([]);
const { onPaste, dragEnd, dropUpload } = useMouseEvents(
	can_upload,
	is_upload_visible,
	list_upload_files,
	timeline_parent_id,
	timeline_existing_albums,
	upload_config,
);

togglableStore.loadUploadConfig();
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

defineShortcuts({
	l: () => !photoStore.isLoaded && !userStore.isLoggedIn && (is_login_open.value = true),
	"/": () => !photoStore.isLoaded && timelineStore.rootConfig?.is_search_accessible && openSearch(),
	f: () => togglableStore.toggleFullScreen(),
	u: () => !photoStore.isLoaded && (is_upload_visible.value = true),

	arrowleft: () => photoStore.isLoaded && (isLTR() ? photoStore.hasPrevious && previous(true) : photoStore.hasNext && next(true)),
	arrowright: () => photoStore.isLoaded && (isLTR() ? photoStore.hasNext && next(true) : photoStore.hasPrevious && previous(true)),
	o: () => photoStore.isLoaded && rotateOverlay(),
	" ": () => photoStore.isLoaded && slideshow(),
	i: () => photoStore.isLoaded && toggleDetails(),

	delete: () => photoStore.isLoaded && toggleDelete(),
	backspace: () => photoStore.isLoaded && toggleDelete(),

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
