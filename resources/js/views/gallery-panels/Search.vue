<template>
	<LoadingProgress v-model:loading="isSearching" />

	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<SearchHeader :title="title" @goBack="goBack" />
		</Collapse>
		<div
			:class="{
				'relative flex flex-wrap content-start w-full justify-start overflow-y-auto': true,
				'h-svh': is_full_screen,
				'h-[calc(100vh-3.5rem)]': !is_full_screen,
			}"
		>
			<SearchPanel
				:title="title"
				:searchMinimumLengh="searchMinimumLengh"
				:isSearching="isSearching"
				:noData="noData"
				:search="search_term"
				@clear="clear"
				@search="search"
			/>
			<ResultPanel
				v-if="albums !== undefined && photos !== undefined && layoutConfig"
				:albums="albums"
				:photos="photos"
				:total="total"
				:albumHeader="albumHeader"
				:photoHeader="photoHeader"
				:layout="layout"
				:layoutConfig="layoutConfig"
				:album-panel-config="albumPanelConfig"
				:is-photo-timeline-enabled="configForMenu.is_photo_timeline_enabled"
				:photo-callbacks="photoCallbacks"
				:album-callbacks="albumCallbacks"
				:selectors="selectors"
				v-model:first="from"
				v-model:rows="per_page"
				@refresh="refresh"
			/>
		</div>
	</div>

	<!-- Photo panel -->
	<PhotoPanel
		v-if="photo"
		:album-id="albumId"
		:photo="photo"
		:photos="photosForSelection"
		:is-map-visible="config?.is_map_accessible ?? false"
		@toggle-slide-show="slideshow"
		@rotate-overlay="rotateOverlay"
		@rotate-photo-c-w="rotatePhotoCW"
		@rotate-photo-c-c-w="rotatePhotoCCW"
		@set-album-header="setAlbumHeader"
		@toggle-star="toggleStar"
		@toggle-move="toggleMove"
		@toggle-delete="toggleDelete"
		@updated="refresh"
		@go-back="goBack"
		@next="() => next(true)"
		@previous="() => previous(true)"
	/>

	<!-- Dialogs -->
	<template v-if="photo">
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
		<PhotoCopyDialog
			v-model:visible="is_copy_visible"
			:parent-id="albumId"
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
		<MoveDialog :photo="photo" v-model:visible="is_move_visible" :parent-id="props.albumId" @moved="refresh" />
		<DeleteDialog :photo="photo" v-model:visible="is_delete_visible" :parent-id="props.albumId" @deleted="refresh" />
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
		<PhotoCopyDialog
			v-model:visible="is_copy_visible"
			:parent-id="albumId"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@copied="refresh"
		/>
		<MoveDialog
			v-model:visible="is_move_visible"
			:parent-id="albumId"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@moved="refresh"
		/>
		<DeleteDialog
			v-model:visible="is_delete_visible"
			:parent-id="albumId"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@deleted="refresh"
		/>
		<RenameDialog v-model:visible="is_rename_visible" :parent-id="undefined" :album="selectedAlbum" :photo="selectedPhoto" @renamed="refresh" />
		<AlbumMergeDialog
			v-model:visible="is_merge_album_visible"
			:parent-id="albumId"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@merged="refresh"
		/>
	</template>
</template>
<script setup lang="ts">
import { AlbumThumbConfig } from "@/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import { useAlbumRefresher } from "@/composables/album/albumRefresher";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useSelection } from "@/composables/selections/selections";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke, useDebounceFn } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { computed, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useSearch } from "@/composables/search/searchRefresher";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useGetLayoutConfig } from "@/layouts/PhotoLayout";
import SearchPanel from "@/components/gallery/searchModule/SearchPanel.vue";
import ResultPanel from "@/components/gallery/searchModule/ResultPanel.vue";
import { onMounted } from "vue";
import { useScrollable } from "@/composables/album/scrollable";
import { useSearchComputed } from "@/composables/search/searchComputed";
import { ALL } from "@/config/constants";
import PhotoPanel from "@/components/gallery/photoModule/PhotoPanel.vue";
import PhotoEdit from "@/components/drawers/PhotoEdit.vue";
import { usePhotoActions } from "@/composables/album/photoActions";
import { useToast } from "primevue/usetoast";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { usePhotoRefresher } from "@/composables/photo/hasRefresher";
import { Collapse } from "vue-collapsed";
import SearchHeader from "@/components/headers/SearchHeader.vue";
import LoadingProgress from "@/components/loading/LoadingProgress.vue";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { useHasNextPreviousPhoto } from "@/composables/photo/hasNextPreviousPhoto";
import MetricsService from "@/services/metrics-service";

const route = useRoute();
const router = useRouter();
const toast = useToast();

const props = defineProps<{
	albumId?: string;
	photoId?: string;
}>();

const albumId = ref(props.albumId ?? ALL);
const photoId = ref(props.photoId);

// unused? Hard to say...
const videoElement = ref<HTMLVideoElement | null>(null);

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { slideshow_timeout, are_nsfw_visible } = storeToRefs(lycheeStore);

const togglableStore = useTogglablesStateStore();

const search_page = ref(1);
const search_term = ref("");

const { is_login_open, is_slideshow_active, is_photo_edit_open, is_full_screen, are_details_open } = storeToRefs(togglableStore);

const { album, photo, config, loadAlbum } = useAlbumRefresher(albumId, photoId, auth, is_login_open);

const {
	albums,
	photos,
	layout,
	searchMinimumLengh,
	isSearching,
	from,
	per_page,
	total,
	photoHeader,
	albumHeader,
	searchInit,
	search,
	clear,
	refresh,
} = useSearch(albumId, search_term, search_page);

const { refreshPhoto } = usePhotoRefresher(photo, photos, photoId);
const { albumsForSelection, photosForSelection, noData, configForMenu, title } = useSearchComputed(config, album, albums, photos, lycheeStore);

const { layoutConfig, loadLayoutConfig } = useGetLayoutConfig();

const { hasPrevious, hasNext } = useHasNextPreviousPhoto(photo);
const { getNext, getPrevious } = getNextPreviousPhoto(router, photo);
const { slideshow, next, previous, stop } = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);

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
	is_copy_visible,
	toggleCopy,
} = useGalleryModals(togglableStore);

const {
	selectedPhotosIdx,
	selectedAlbumsIdx,
	selectedPhoto,
	selectedAlbum,
	selectedPhotos,
	selectedAlbums,
	selectedPhotosIds,
	selectedAlbumsIds,
	unselect,
} = useSelection(photosForSelection, albumsForSelection, togglableStore);

const { toggleStar, rotatePhotoCCW, rotatePhotoCW, setAlbumHeader, rotateOverlay } = usePhotoActions(photo, albumId, toast, lycheeStore);

const photoCallbacks = {
	star: () => {
		PhotoService.star(selectedPhotosIds.value, true);
		AlbumService.clearCache();
		refresh();
	},
	unstar: () => {
		PhotoService.star(selectedPhotosIds.value, false);
		AlbumService.clearCache();
		refresh();
	},
	setAsCover: () => {
		PhotoService.setAsCover(selectedPhoto.value!.id, albumId.value);
		AlbumService.clearCache(albumId.value);
		refresh();
	},
	setAsHeader: () => {
		PhotoService.setAsHeader(selectedPhoto.value!.id, albumId.value, false);
		AlbumService.clearCache(albumId.value);
		refresh();
	},
	toggleTag: toggleTag,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {},
};

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: toggleRename,
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {},
};

const selectors = {
	config: configForMenu,
	album: albumId.value !== "" ? album : undefined,
	selectedPhoto: selectedPhoto,
	selectedPhotos: selectedPhotos,
	selectedPhotosIdx: selectedPhotosIdx,
	selectedAlbum: selectedAlbum,
	selectedAlbums: selectedAlbums,
	selectedAlbumIdx: selectedAlbumsIdx,
};

const albumPanelConfig = computed<AlbumThumbConfig>(() => ({
	album_thumb_css_aspect_ratio: "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

const { scrollToTop, setScroll } = useScrollable(togglableStore, albumId);

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
		router.push({ name: "search-with-album", params: { albumId: albumId.value } });
		return;
	}

	if (albumId.value !== undefined && albumId.value !== ALL && albumId.value !== "") {
		router.push({ name: "album", params: { albumId: props.albumId } });
	} else {
		router.push({ name: "gallery" });
	}
}

// Album operations
onKeyStroke("h", () => !shouldIgnoreKeystroke() && photo.value === undefined && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photo.value === undefined && togglableStore.toggleFullScreen());

// Photo operations
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photo.value !== undefined && hasPrevious() && previous(true));
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photo.value !== undefined && hasNext() && next(true));
onKeyStroke("o", () => !shouldIgnoreKeystroke() && photo.value !== undefined && rotateOverlay());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photo.value !== undefined && slideshow());
onKeyStroke("i", () => !shouldIgnoreKeystroke() && photo.value !== undefined && (are_details_open.value = !are_details_open.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photo.value !== undefined && togglableStore.toggleFullScreen());
onKeyStroke("Escape", () => !shouldIgnoreKeystroke() && photo.value !== undefined && is_slideshow_active.value && stop());

// Priviledged Photo operations
onKeyStroke("m", () => !shouldIgnoreKeystroke() && photo.value !== undefined && photo.value.rights.can_edit && toggleMove());
onKeyStroke(
	"e",
	() =>
		!shouldIgnoreKeystroke() &&
		photo.value !== undefined &&
		photo.value.rights.can_edit &&
		(is_photo_edit_open.value = !is_photo_edit_open.value),
);
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

onMounted(() => {
	if (photoId.value !== undefined) {
		router.push({ name: "search-with-album", params: { albumId: albumId.value } });
	}

	if (albumId.value !== "" && albumId.value !== ALL) {
		loadAlbum();
	}

	searchInit();
	loadLayoutConfig();
});

const debouncedPhotoMetrics = useDebounceFn(() => {
	if (photoId.value !== undefined) {
		MetricsService.photo(photoId.value);
		return;
	}
}, 100);

watch(
	() => route.params.photoId,
	(newPhotoId, _) => {
		unselect();

		photoId.value = newPhotoId as string;
		debouncedPhotoMetrics();
		if (photoId.value !== undefined) {
			togglableStore.rememberScrollThumb(photoId.value);
			refreshPhoto();
		}

		if (photoId.value === undefined) {
			setScroll();
		}
	},
);
</script>
