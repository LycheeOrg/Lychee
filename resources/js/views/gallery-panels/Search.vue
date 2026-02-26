<template>
	<LoadingProgress v-model:loading="searchStore.isSearching" />

	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<SearchHeader :title="title" @go-back="goBack" />
		</Collapse>
		<div
			:class="{
				'relative flex flex-wrap content-start w-full justify-start overflow-y-auto': true,
				'h-svh': is_full_screen,
				'h-[calc(100vh-3.5rem)]': !is_full_screen,
			}"
		>
			<SearchPanel :title="title" :no-data="noData" :search="searchStore.searchTerm" @clear="searchStore.clear" @search="searchStore.search" />
			<ResultPanel
				v-model:first="searchStore.from"
				:total="searchStore.total"
				v-model:rows="searchStore.perPage"
				:album-panel-config="albumPanelConfig"
				:is-photo-timeline-enabled="configForMenu.is_photo_timeline_enabled"
				:photo-callbacks="photoCallbacks"
				:album-callbacks="albumCallbacks"
				:selectors="selectors"
				@refresh="refresh"
			/>
		</div>
	</div>

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
	<template v-if="photoStore.photo !== undefined">
		<PhotoTagDialog
			v-model:visible="is_tag_visible"
			:parent-id="albumId"
			:photo="photoStore.photo"
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
			:photo="photoStore.photo"
			@licensed="
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
		<PhotoEdit v-if="albumStore.rights?.can_edit" v-model:visible="is_photo_edit_open" />
		<MoveDialog v-model:visible="is_move_visible" @moved="refresh" />
		<DeleteDialog v-model:visible="is_delete_visible" @deleted="refresh" />
	</template>
	<template v-else-if="!noData">
		<!-- Dialogs -->
		<PhotoTagDialog
			v-model:visible="is_tag_visible"
			:parent-id="albumId"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@tagged="refresh"
		/>
		<PhotoLicenseDialog
			v-model:visible="is_license_visible"
			:parent-id="albumId"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@licensed="refresh"
		/>
		<PhotoCopyDialog v-model:visible="is_copy_visible" :photo="selectedPhoto" :photo-ids="selectedPhotosIds" @copied="refresh" />
		<MoveDialog
			v-model:visible="is_move_visible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@moved="refresh"
		/>
		<DeleteDialog
			v-model:visible="is_delete_visible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@deleted="refresh"
		/>
		<RenameDialog v-model:visible="is_rename_visible" :album="selectedAlbum" :photo="selectedPhoto" @renamed="refresh" />
		<AlbumMergeDialog v-model:visible="is_merge_album_visible" :album="selectedAlbum" :album-ids="selectedAlbumsIds" @merged="refresh" />
	</template>
</template>
<script setup lang="ts">
import { AlbumThumbConfig } from "@/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useSelection } from "@/composables/selections/selections";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke, useDebounceFn } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { computed, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import SearchPanel from "@/components/gallery/searchModule/SearchPanel.vue";
import ResultPanel from "@/components/gallery/searchModule/ResultPanel.vue";
import { onMounted } from "vue";
import { useScrollable } from "@/composables/album/scrollable";
import { ALL } from "@/config/constants";
import PhotoPanel from "@/components/gallery/photoModule/PhotoPanel.vue";
import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import { usePhotoActions } from "@/composables/album/photoActions";
import { useToast } from "primevue/usetoast";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { Collapse } from "vue-collapsed";
import SearchHeader from "@/components/headers/SearchHeader.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import MetricsService from "@/services/metrics-service";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useLtRorRtL } from "@/utils/Helpers";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useUserStore } from "@/stores/UserState";
import { useSearchStore } from "@/stores/SearchState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLayoutStore } from "@/stores/LayoutState";
import { trans } from "laravel-vue-i18n";

const { isLTR } = useLtRorRtL();

const route = useRoute();
const router = useRouter();
const toast = useToast();

const props = defineProps<{
	albumId?: string;
	photoId?: string;
}>();

// const albumId = ref(props.albumId ?? ALL);

const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();
const photoStore = usePhotoStore();
const photosStore = usePhotosStore();
const userStore = useUserStore();
const searchStore = useSearchStore();
const layoutStore = useLayoutStore();

// eslint-disable-next-line vue/no-dupe-keys
const { albumId } = storeToRefs(albumStore);
// eslint-disable-next-line vue/no-dupe-keys
const { photoId } = storeToRefs(photoStore);

// unused? Hard to say...
const videoElement = ref<HTMLVideoElement | null>(null);

const lycheeStore = useLycheeStateStore();
const { slideshow_timeout, are_nsfw_visible, is_slideshow_enabled } = storeToRefs(lycheeStore);
const togglableStore = useTogglablesStateStore();

async function load() {
	await Promise.allSettled([layoutStore.load(), lycheeStore.load(), userStore.load(), albumStore.load(), searchStore.load()]);
	photoStore.photoId = photoId.value;
	photoStore.load();
}

const { is_slideshow_active, is_photo_edit_open, is_full_screen, are_details_open } = storeToRefs(togglableStore);

const { getParentId } = usePhotoRoute(router);
const title = computed<string>(() => {
	if (albumStore.album === undefined) {
		return trans(lycheeStore.title);
	}
	return albumStore.album.title;
});

const noData = computed<boolean>(() => albumsStore.albums.length === 0 && photosStore.photos.length === 0);

const configForMenu = computed<App.Http.Resources.GalleryConfigs.AlbumConfig>(() => {
	if (albumStore.config !== undefined) {
		return albumStore.config;
	}
	return {
		is_base_album: false,
		is_model_album: false,
		is_password_protected: false,
		is_map_accessible: false,
		is_mod_frame_enabled: false,
		is_search_accessible: false,
		is_nsfw_warning_visible: false,
		album_thumb_css_aspect_ratio: "aspect-square",
		photo_layout: "justified",
		is_album_timeline_enabled: false,
		is_photo_timeline_enabled: false,
	};
});

async function refresh() {
	await Promise.allSettled([layoutStore.load(), lycheeStore.load(), userStore.refresh(), albumStore.refresh(), searchStore.refresh()]);
	photoStore.photoId = photoId.value;
	photoStore.load();
}

const { getNext, getPrevious } = getNextPreviousPhoto(router, photoStore);
const { slideshow, next, previous, stop } = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);

function toggleDetails() {
	is_photo_edit_open.value = false;
	are_details_open.value = !are_details_open.value;
}

const {
	is_delete_visible,
	toggleDelete,
	is_merge_album_visible,
	toggleMergeAlbum,
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
	toggleApplyRenamer,
} = useGalleryModals(togglableStore);

const { selectedPhoto, selectedAlbum, selectedPhotos, selectedAlbums, selectedPhotosIds, selectedAlbumsIds, unselect } = useSelection(
	photosStore,
	albumsStore,
	togglableStore,
);

const { toggleHighlight, rotatePhotoCCW, rotatePhotoCW, setAlbumHeader, rotateOverlay } = usePhotoActions(photoStore, albumId, toast, lycheeStore);

const photoCallbacks = {
	star: () => {
		PhotoService.highlight(selectedPhotosIds.value, true);
		AlbumService.clearCache();
		// refresh();
	},
	unstar: () => {
		PhotoService.highlight(selectedPhotosIds.value, false);
		AlbumService.clearCache();
		// refresh();
	},
	setAsCover: () => {
		if (albumId.value === undefined) {
			return;
		}
		PhotoService.setAsCover(selectedPhoto.value!.id, albumId.value);
		// Update the album's cover_id immediately to reflect the change (toggle behavior)
		if (albumStore.modelAlbum !== undefined) {
			albumStore.modelAlbum.cover_id = albumStore.modelAlbum.cover_id === selectedPhoto.value!.id ? null : selectedPhoto.value!.id;
		}
		AlbumService.clearCache(albumId.value);
		// refresh();
	},
	setAsHeader: () => {
		if (albumId.value === undefined) {
			return;
		}
		PhotoService.setAsHeader(selectedPhoto.value!.id, albumId.value, false);
		// Update the album's header_id immediately to reflect the change (toggle behavior)
		const isToggleOff = albumStore.modelAlbum?.header_id === selectedPhoto.value!.id;
		if (albumStore.modelAlbum !== undefined) {
			albumStore.modelAlbum.header_id = isToggleOff ? null : selectedPhoto.value!.id;
		}
		// Update the header image URL in the album's preFormattedData
		if (albumStore.album?.preFormattedData) {
			if (isToggleOff) {
				albumStore.album.preFormattedData.url = null;
			} else {
				// Use medium or small variant for the header image
				const headerUrl = selectedPhoto.value!.size_variants.medium?.url ?? selectedPhoto.value!.size_variants.small?.url ?? null;
				albumStore.album.preFormattedData.url = headerUrl;
			}
		}
		AlbumService.clearCache(albumId.value);
		// refresh();
	},
	toggleTag: toggleTag,
	toggleLicense: toggleLicense,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {},
	toggleApplyRenamer: toggleApplyRenamer,
};

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: toggleRename,
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {},
	togglePin: () => {},
	toggleApplyRenamer: toggleApplyRenamer,
};

const album = computed(() => albumStore.album);
const selectors = {
	config: configForMenu,
	album: album,
	selectedPhoto: selectedPhoto,
	selectedPhotos: selectedPhotos,
	selectedPhotosIds: selectedPhotosIds,
	selectedAlbum: selectedAlbum,
	selectedAlbums: selectedAlbums,
	selectedAlbumsIds: selectedAlbumsIds,
};

const albumPanelConfig = computed<AlbumThumbConfig>(() => ({
	album_thumb_css_aspect_ratio: "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

const { setScroll } = useScrollable(togglableStore, albumId);

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

		router.push({ name: "search", params: { albumId: albumId.value } });
		return;
	}

	if (albumId.value !== undefined && albumId.value !== ALL && albumId.value !== "") {
		router.push({ name: "album", params: { albumId: props.albumId } });
	} else {
		router.push({ name: "gallery" });
	}
}

// Album operations
onKeyStroke("h", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && !photoStore.isLoaded && togglableStore.toggleFullScreen());

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

// Priviledged Photo operations
onKeyStroke("m", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && albumStore.rights?.can_edit && toggleMove());
onKeyStroke(
	"e",
	() => !shouldIgnoreKeystroke() && photoStore.isLoaded && albumStore.rights?.can_edit && (is_photo_edit_open.value = !is_photo_edit_open.value),
);
onKeyStroke("s", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && albumStore.rights?.can_edit && toggleHighlight());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && photoStore.isLoaded && albumStore.album?.rights.can_delete && toggleDelete());

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
	albumId.value = props.albumId ?? ALL;
	photoId.value = props.photoId;

	await load();
	setScroll();

	// if (photoId.value !== undefined) {
	// 	router.push({ name: "search", params: { albumId: albumId.value } });
	// }

	// if (albumId.value !== "" && albumId.value !== ALL) {
	// 	loadAlbum();
	// }

	// searchInit();
	// loadLayoutConfig();
});

const debouncedPhotoMetrics = useDebounceFn(() => {
	if (photoId.value !== undefined) {
		MetricsService.photo(photoId.value, getParentId());
		return;
	}
}, 100);

watch(
	() => route.params.photoId,
	(newPhotoId, _) => {
		unselect();

		photoStore.setTransition(newPhotoId as string | undefined);

		photoId.value = newPhotoId as string;
		debouncedPhotoMetrics();
		if (photoId.value !== undefined) {
			togglableStore.rememberScrollThumb(photoId.value);
			photoStore.load();
		}

		if (photoId.value === undefined) {
			setScroll();
		}
	},
);
</script>
