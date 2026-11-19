<template>
	<LoadingProgress v-model:loading="searchStore.isSearching" />

	<div class="min-h-svh">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<SearchHeader
				:title="title"
				:scoped-album-title="scopedAlbumTitle"
				:show-breadcrumb="showBreadcrumb"
				:breadcrumb-items="breadcrumbItems"
				@go-back="goBack"
				@clear-scope="clearScope"
			/>
		</Collapse>
		<!-- No `overflow-y-auto` / fixed height: results render through
		     `PhotoGridVirtual`, whose `useWindowVirtualizer` tracks the real
		     window scroll position — a nested scroll container desyncs its
		     visibility calculation entirely. Exactly the pitfall `Timeline.vue`
		     and `Flow.vue` both document. -->
		<div class="relative flex flex-wrap content-start w-full justify-start">
			<SearchPanel :no-data="noData" @clear="onClear" @search="onSearch" @clear-scope="clearScope" />
			<div ref="resultsMarkerRef" aria-hidden="true" class="w-full h-0" data-search-results></div>
			<ResultPanel v-if="!isInitialSearchLoading" :photo-callbacks="photoCallbacks" :album-callbacks="albumCallbacks" :selectors="selectors" />
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
		@toggle-highlight="toggleHighlight"
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
			v-model:open="is_tag_visible"
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
			v-model:open="is_license_visible"
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
			v-model:open="is_copy_visible"
			:photo="photoStore.photo"
			@copied="
				() => {
					unselect();
					refresh();
				}
			"
		/>
		<PhotoEdit v-if="albumStore.rights?.can_edit" v-model:is-edit-open="is_photo_edit_open" />
		<MoveDialog v-model:open="is_move_visible" @moved="refresh" />
		<DeleteDialog v-model:open="is_delete_visible" @deleted="refresh" />
	</template>
	<template v-else-if="!noData">
		<!-- Dialogs -->
		<PhotoTagDialog v-model:open="is_tag_visible" :parent-id="albumId" :photo="selectedPhoto" :photo-ids="selectedPhotosIds" @tagged="refresh" />
		<PhotoLicenseDialog
			v-model:open="is_license_visible"
			:parent-id="albumId"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			@licensed="refresh"
		/>
		<PhotoCopyDialog v-model:open="is_copy_visible" :photo="selectedPhoto" :photo-ids="selectedPhotosIds" @copied="refresh" />
		<MoveDialog
			v-model:open="is_move_visible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@moved="refresh"
		/>
		<DeleteDialog
			v-model:open="is_delete_visible"
			:photo="selectedPhoto"
			:photo-ids="selectedPhotosIds"
			:album="selectedAlbum"
			:album-ids="selectedAlbumsIds"
			@deleted="refresh"
		/>
		<RenameDialog v-model:open="is_rename_visible" :album="selectedAlbum" :photo="selectedPhoto" @renamed="refresh" />
		<AlbumMergeDialog v-model:open="is_merge_album_visible" :album="selectedAlbum" :album-ids="selectedAlbumsIds" @merged="refresh" />
		<DownloadAlbum v-model:open="is_download_photo_visible" :photo-ids="downloadPhotoIds" :from-id="downloadFromId" />
		<DownloadAlbum v-model:open="is_download_album_visible" :album-ids="downloadAlbumIds" />
	</template>
</template>
<script setup lang="ts">
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useSelection } from "@/composables/selections/selections";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useDebounceFn } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { computed, nextTick, provide, ref, watch } from "vue";
import { MOTION_URL_LOADER } from "@/v8/composables/photo/motionUrlLoader";
import { useRoute, useRouter } from "vue-router";
import AlbumMergeDialog from "@/v8/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import RenameDialog from "@/v8/components/forms/gallery-dialogs/RenameDialog.vue";
import PhotoTagDialog from "@/v8/components/forms/photo/PhotoTagDialog.vue";
import PhotoCopyDialog from "@/v8/components/forms/photo/PhotoCopyDialog.vue";
import MoveDialog from "@/v8/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/v8/components/forms/gallery-dialogs/DeleteDialog.vue";
import PhotoLicenseDialog from "@/v8/components/forms/photo/PhotoLicenseDialog.vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import SearchPanel from "@/v8/components/gallery/searchModule/SearchPanel.vue";
import ResultPanel from "@/v8/components/gallery/searchModule/ResultPanel.vue";
import { onMounted } from "vue";
import { useScrollable } from "@/composables/album/scrollable";
import { ALL } from "@/config/constants";
import PhotoPanel from "@/v8/components/gallery/photoModule/PhotoPanel.vue";
import PhotoEdit from "@/v8/components/drawers/PhotoEdit.vue";
import { usePhotoActions } from "@/composables/album/photoActions";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useSlideshowFunction } from "@/composables/photo/slideshow";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { Collapse } from "vue-collapsed";
import SearchHeader from "@/v8/components/headers/SearchHeader.vue";
import LoadingProgress from "@/v8/components/loading/LoadingProgress.vue";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import MetricsService from "@/services/metrics-service";
import ModerationService from "@/services/moderation-service";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useLtRorRtL } from "@/utils/Helpers";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotoNsfwDetectionsStore } from "@/stores/PhotoNsfwDetectionsState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useUserStore } from "@/stores/UserState";
import { useSearchStore } from "@/stores/SearchState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLayoutStore } from "@/stores/LayoutState";
import { trans } from "laravel-vue-i18n";
import DownloadAlbum from "@/v8/components/modals/DownloadAlbum.vue";
import { definePanelShortcuts } from "@/v8/composables/usePanelShortcuts";

const { isLTR } = useLtRorRtL();

const route = useRoute();
const router = useRouter();
const toast = useAppToast();

const props = defineProps<{
	albumId?: string;
	photoId?: string;
}>();

const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();
const photoStore = usePhotoStore();
const nsfwDetectionsStore = usePhotoNsfwDetectionsStore();
const photosStore = usePhotosStore();
const userStore = useUserStore();
const searchStore = useSearchStore();
// Feature 100: thumbnails fetch their live photo video URL on hover.
provide(MOTION_URL_LOADER, (ids) => searchStore.loadPhotoDetailsV3(ids).then(() => undefined));
const layoutStore = useLayoutStore();

// Pre-fill the store from a bookmarked/shared URL before children (e.g. SearchBox)
// mount, since SearchBox only reads searchStore.searchTerm once, on its own mount.
const hasInitialQuery = typeof route.query.q === "string" && route.query.q.length > 0;
if (hasInitialQuery) {
	searchStore.searchTerm = route.query.q as string;
}

// True while a bookmarked/shared URL's search is still in flight. Keeps ResultPanel hidden
// so the scoped album's own browsing data -- an unrelated side effect of the albumStore.load()
// call in load() below (needed just for the header/title) -- never flashes on screen before
// the real search results replace it.
const isInitialSearchLoading = ref(hasInitialQuery);

const resultsMarkerRef = ref<HTMLElement | null>(null);

function onSearch(terms: string, updateQuery = true): Promise<void> {
	const promise = searchStore.searchV3(terms).then(() => {
		if (searchStore.total > 0) {
			nextTick(() => {
				resultsMarkerRef.value?.scrollIntoView({ behavior: "smooth" });
			});
		}
	});
	if (updateQuery && route.query.q !== terms) {
		router.replace({ query: { ...route.query, q: terms } });
	}
	return promise;
}

function onClear() {
	searchStore.clear();
	searchStore.searchTerm = undefined;
	if (route.query.q !== undefined) {
		const query = { ...route.query };
		delete query.q;
		router.replace({ query });
	}
}

// oxlint-disable-next-line vue/no-dupe-keys
const { albumId } = storeToRefs(albumStore);
// oxlint-disable-next-line vue/no-dupe-keys
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

const { is_slideshow_active, is_photo_edit_open, is_full_screen, are_details_open, is_download_album_visible, is_download_photo_visible } =
	storeToRefs(togglableStore);

const { getParentId } = usePhotoRoute(router);

const downloadPhotoIds = ref<string[]>([]);
const downloadFromId = ref<string | null>(null);
const downloadAlbumIds = ref<string[]>([]);

const title = computed<string>(() => trans("gallery.search.title"));

const isAlbumScoped = computed<boolean>(() => albumId.value !== undefined && albumId.value !== ALL && albumId.value !== "");

const scopedAlbumTitle = computed<string | undefined>(() => (isAlbumScoped.value ? albumStore.album?.title : undefined));

const showBreadcrumb = computed<boolean>(() => isAlbumScoped.value && (albumStore.config?.is_breadcrumb_enabled ?? false));

const breadcrumbItems = computed<App.Http.Resources.Models.BreadcrumbItemResource[]>(() => albumStore.modelAlbum?.breadcrumb ?? []);

function clearScope() {
	if (!isAlbumScoped.value) {
		return;
	}
	albumId.value = ALL;
	albumStore.reset();
	// Preserve q (and any other query params) in this same navigation: onSearch() only
	// updates the query when it differs from route.query.q, but route.query wouldn't
	// reflect a preceding q-less replace() until that navigation resolves, so a separate
	// replace() here would race with onSearch()'s check and could drop q.
	router.replace({ name: "search", params: { albumId: undefined, photoId: photoId.value }, query: route.query });
	if (searchStore.searchTerm !== undefined) {
		onSearch(searchStore.searchTerm, false);
	}
}

// Mirrors `ResultPanel.vue`: the album hits live in the search store only
// (FR-069-20), and this instance feeds the context menu's own `selectors`, so it
// has to resolve against the same pool the panel does.
const searchAlbumsV3 = computed<App.Http.Resources.Models.ThumbAlbumResource[] | undefined>(() => searchStore.albumTilesV3);

const noData = computed<boolean>(() => searchStore.albumTilesV3.length === 0 && photosStore.photos.length === 0);

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
		is_breadcrumb_enabled: false,
		album_thumb_css_aspect_ratio: "aspect-square",
		photo_layout: "justified",
		is_album_timeline_enabled: false,
		is_photo_timeline_enabled: false,
		is_date_scrubber_enabled: false,
		photo_date_scrubber_field: null,
		album_date_scrubber_field: null,
	};
});

async function refresh() {
	// `albumStore.refresh()` is not just a header refresh: for a scoped search it
	// goes through `AlbumState.load()`, which resets and repopulates the shared
	// photos store. Run to completion *before* the search starts, or its photos
	// can land after the search has already published its own results and
	// silently replace them — the same ordering `onMounted()` below already
	// relies on when it awaits `onSearch()`.
	await Promise.allSettled([layoutStore.load(), lycheeStore.load(), userStore.refresh(), albumStore.refresh()]);
	if (searchStore.searchTerm !== undefined) {
		await searchStore.searchV3(searchStore.searchTerm);
	}
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
	searchAlbumsV3,
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
		if (
			albumStore.album !== undefined &&
			"editable" in albumStore.album &&
			albumStore.album.editable !== undefined &&
			albumStore.album.editable !== null
		) {
			albumStore.album.editable.cover_id = albumStore.modelAlbum?.cover_id ?? null;
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
		if (
			albumStore.album !== undefined &&
			"editable" in albumStore.album &&
			albumStore.album.editable !== undefined &&
			albumStore.album.editable !== null
		) {
			albumStore.album.editable.header_id = isToggleOff ? null : selectedPhoto.value!.id;
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
	toggleDownload: () => {
		downloadPhotoIds.value = [...selectedPhotosIds.value];
		downloadFromId.value = getParentId() ?? null;
		is_download_photo_visible.value = true;
	},
	toggleApplyRenamer: toggleApplyRenamer,
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

const albumCallbacks = {
	setAsCover: () => {},
	toggleRename: toggleRename,
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		downloadAlbumIds.value = selectedAlbumsIds.value;
		is_download_album_visible.value = true;
	},
	togglePin: () => {},
	toggleApplyRenamer: toggleApplyRenamer,
	toggleScanFaces: () => {},
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

		router.push({
			name: "search",
			params: { albumId: albumId.value },
			query: searchStore.searchTerm ? { q: searchStore.searchTerm } : {},
		});
		return;
	}

	if (albumId.value !== undefined && albumId.value !== ALL && albumId.value !== "") {
		router.push({ name: "album", params: { albumId: props.albumId } });
	} else {
		router.push({ name: "gallery" });
	}
}

definePanelShortcuts({
	// Album operations
	h: () => {
		if (photoStore.isLoaded && lycheeStore.is_nsfw_classifier_enabled) {
			lycheeStore.cycleNsfwOverlayMode(nsfwDetectionsStore.get(photoStore.photo?.id ?? "").detections);
		} else {
			are_nsfw_visible.value = !are_nsfw_visible.value;
		}
	},
	f: () => togglableStore.toggleFullScreen(),

	// Photo operations (note that the arrow keys are flipped for RTL languages)
	arrowleft: () => photoStore.isLoaded && (isLTR() ? photoStore.hasPrevious && previous(true) : photoStore.hasNext && next(true)),
	arrowright: () => photoStore.isLoaded && (isLTR() ? photoStore.hasNext && next(true) : photoStore.hasPrevious && previous(true)),
	o: () => photoStore.isLoaded && rotateOverlay(),
	" ": () => photoStore.isLoaded && is_slideshow_enabled.value && slideshow(),
	i: () => photoStore.isLoaded && toggleDetails(),

	// Priviledged Photo operations
	m: () => photoStore.isLoaded && albumStore.rights?.can_move_content && toggleMove(),
	e: () => photoStore.isLoaded && albumStore.rights?.can_edit && (is_photo_edit_open.value = !is_photo_edit_open.value),
	s: () => photoStore.isLoaded && (albumsStore.rootRights?.can_highlight || albumStore.rights?.can_edit) && toggleHighlight(),
	delete: () => photoStore.isLoaded && albumStore.album?.rights.can_delete && toggleDelete(),
	backspace: () => photoStore.isLoaded && albumStore.album?.rights.can_delete && toggleDelete(),

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

onMounted(async () => {
	albumId.value = props.albumId ?? ALL;
	photoId.value = props.photoId;

	await load();

	// AlbumsState/PhotosState are shared with the browsing views (Gallery/Album/Timeline).
	// When scoped to an album, the albumStore.load() call above (needed just to get the
	// album's title/config for the header) has the side effect of populating albumsStore
	// with that album's own sub-albums, exactly like the Album browsing view does. On a
	// fresh search entry (no search performed yet) that leftover browsing data must not be
	// shown as if it were search results. Must run *after* load(), not before, since load()
	// is what (re)populates it.
	if (hasInitialQuery) {
		// Bookmarked/shared URL: run the search that the query string encodes.
		// Awaited (unlike onSearch's usual fire-and-forget use) so photoStore.load() below
		// sees the actual search results -- not the scoped album's own browsing data that
		// load() incidentally populated -- and can open the requested photo straight away.
		// isInitialSearchLoading keeps ResultPanel hidden for that same reason until then.
		await onSearch(searchStore.searchTerm as string, true);
		isInitialSearchLoading.value = false;
		photoStore.photoId = photoId.value;
		photoStore.load();
	} else if (searchStore.searchTerm === undefined) {
		searchStore.clear();
	}

	setScroll();
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
