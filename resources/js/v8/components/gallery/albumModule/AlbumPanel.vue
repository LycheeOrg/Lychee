<template>
	<BuyMeDialog />
	<div class="h-svh overflow-y-hidden flex flex-col">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<AlbumHeader
			v-if="albumStore.isLoaded && userStore.isLoaded"
			@refresh="emits('refresh')"
			@toggle-edit="emits('toggleEdit')"
			@open-search="emits('openSearch')"
			@go-back="emits('goBack')"
			@show-selected="albumCallbacks.copyHighlighted()"
			@open-context-menu="openContextMenuFromHeader"
		/>
		<template v-if="albumStore.album && albumStore.config && userStore.isLoaded">
			<UContextMenu :items="menuSections" :disabled="noData" class="contents">
				<div id="galleryView" class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto h-full select-none">
					<SelectDrag :with-scroll="true" />
					<AlbumEdit v-if="albumStore.rights?.can_edit" />
					<div v-if="noData" class="flex w-full flex-col h-full items-center justify-center text-xl text-muted-color gap-8">
						<span class="block">
							{{ $t("gallery.album.no_results") }}
						</span>
						<UButton
							v-if="albumStore.rights?.can_upload && albumStore.modelAlbum !== undefined"
							color="warning"
							class="rounded max-w-xs w-full font-bold justify-center"
							icon="prime:upload"
							@click="toggleUpload"
							>{{ $t("gallery.album.upload") }}</UButton
						>
					</div>
					<AlbumHero
						v-if="!noData"
						@open-sharing-modal="toggleShareAlbum"
						@open-embed-code="toggleEmbedCode"
						@open-statistics="toggleStatistics"
						@toggle-slide-show="emits('toggleSlideShow')"
						@scroll-to-pictures="albumCallbacks.scrollToPaginatorTop"
						@toggle-apply-renamer="toggleApplyRenamer"
						@toggle-watermark-confirm="toggleWatermarkConfirm"
						@toggle-download-album="toggleDownloadAlbumFromHero"
						@toggle-scan-faces="toggleScanFacesFromHero"
					/>
					<template v-if="is_se_enabled && userStore.isLoggedIn">
						<AlbumStatistics
							v-if="photosStore.photos.length > 0"
							:key="`statistics_${albumStore.album?.id}`"
							v-model:open="areStatisticsOpen"
						/>
					</template>
					<div v-if="albumStore.isLoading" class="flex w-full items-center justify-center">
						<Spinner class="text-4xl text-primary-400" />
					</div>
					<template v-else>
						<AlbumThumbPanel
							v-if="albumsStore.albums.length > 0"
							header="gallery.album.header_albums"
							:albums="albumsStore.albums"
							:config="albumPanelConfig"
							:is-alone="photosStore.photos.length === 0"
							:selected-albums="selectedAlbumsIds"
							:is-timeline="albumStore.config.is_album_timeline_enabled"
							@clicked="albumSelect"
							@selected="albumSelect"
							@contexted="contextMenuAlbumOpen"
						/>
						<!-- Pagination for albums -->
						<Pagination
							v-if="albumsStore.albums.length > 0 && albumStore.hasAlbumsPagination"
							:mode="lycheeStore.albums_pagination_mode"
							:loading="albumStore.albums_loading"
							:has-more="albumStore.hasMoreAlbums"
							:current-page="albumStore.albums_current_page"
							:last-page="albumStore.albums_last_page"
							:per-page="albumStore.albums_per_page"
							:total="albumStore.albums_total"
							:remaining="albumStore.albumsRemainingCount"
							resource-type="albums"
							@load-more="albumStore.loadMoreAlbums()"
							@go-to-page="goToAlbumsPage"
						/>
						<!-- Tag Filter -->
						<PhotoThumbPanel
							v-if="layoutStore.config && photosStore.photos.length > 0"
							header="gallery.album.header_photos"
							:photos="photosStore.filteredPhotos"
							:photos-timeline="photosStore.filteredPhotosTimeline"
							:selected-photos="selectedPhotosIds"
							:is-timeline="albumStore.config.is_photo_timeline_enabled"
							:with-control="true"
							@clicked="photoClick"
							@selected="photoSelect"
							@contexted="contextMenuPhotoOpen"
							@toggle-buy-me="toggleBuyMe"
							ref="photoPanel"
						/>
						<!-- Pagination for photos -->
						<Pagination
							v-if="photosStore.photos.length > 0 && albumStore.hasPhotosPagination"
							:mode="lycheeStore.photos_pagination_mode"
							:loading="albumStore.photos_loading"
							:has-more="albumStore.hasMorePhotos"
							:current-page="albumStore.photos_current_page"
							:last-page="albumStore.photos_last_page"
							:per-page="albumStore.photos_per_page"
							:total="albumStore.photos_total"
							:remaining="albumStore.photosRemainingCount"
							resource-type="photos"
							@load-more="albumStore.loadMorePhotos()"
							@go-to-page="goToPhotosPage"
						/>
					</template>
					<GalleryFooter v-once />
				</div>
			</UContextMenu>
			<ShareAlbum :key="`share_modal_${albumStore.album.id}`" v-model:open="is_share_album_visible" :title="albumStore.album.title" />
			<ApplyRenamerDialog
				v-model:open="is_apply_renamer_visible"
				:album-id="albumStore.album.id"
				:photo-ids="selectedPhotosIds"
				:album-ids="selectedAlbumsIds"
				@applied="emits('refresh')"
			/>
			<WatermarkConfirmDialog v-model:open="is_watermark_confirm_visible" :album-id="albumStore.album.id" @watermarked="emits('refresh')" />
			<DownloadAlbum v-model:open="is_download_album_visible" :album-ids="downloadAlbumIds" />
			<DownloadAlbum v-model:open="is_download_photo_visible" :photo-ids="downloadPhotoIds" :from-id="downloadFromId" />
		</template>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, ComponentPublicInstance } from "vue";
import AlbumThumbPanel from "@/v8/components/gallery/albumModule/AlbumThumbPanel.vue";
import PhotoThumbPanel from "@/v8/components/gallery/albumModule/PhotoThumbPanel.vue";
import ShareAlbum from "@/v8/components/modals/ShareAlbum.vue";
import AlbumHero from "@/v8/components/gallery/albumModule/AlbumHero.vue";
import AlbumEdit from "@/v8/components/drawers/AlbumEdit.vue";
import AlbumHeader from "@/v8/components/headers/AlbumHeader.vue";
import Spinner from "@/v8/components/Spinner.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useSelection } from "@/composables/selections/selections";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import FaceDetectionService from "@/services/face-detection-service";
import ModerationService from "@/services/moderation-service";
import { AlbumThumbConfig } from "@/v8/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import GalleryFooter from "@/v8/components/footers/GalleryFooter.vue";
import AlbumStatistics from "@/v8/components/drawers/AlbumStatistics.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useRouter } from "vue-router";
import SelectDrag from "@/v8/components/forms/album/SelectDrag.vue";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useBuyMeActions } from "@/composables/album/buyMeActions";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useUserStore } from "@/stores/UserState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useCatalogStore } from "@/stores/CatalogState";
import BuyMeDialog from "@/v8/components/forms/gallery-dialogs/BuyMeDialog.vue";
import ApplyRenamerDialog from "@/v8/components/forms/album/ApplyRenamerDialog.vue";
import WatermarkConfirmDialog from "@/v8/components/forms/album/WatermarkConfirmDialog.vue";
import DownloadAlbum from "@/v8/components/modals/DownloadAlbum.vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import Pagination from "@/v8/components/pagination/Pagination.vue";
import { trans } from "laravel-vue-i18n";
import type { ContextMenuItem } from "@nuxt/ui";

const router = useRouter();
const toast = useAppToast();

defineProps<{
	isPhotoOpen: boolean;
}>();

const userStore = useUserStore();
const albumStore = useAlbumStore();
const photosStore = usePhotosStore();
const catalogStore = useCatalogStore();
const albumsStore = useAlbumsStore();
const layoutStore = useLayoutStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const orderManagement = useOrderManagementStore();

if (!albumsStore.rootRights) {
	albumsStore.loadRootRights();
}

const emits = defineEmits<{
	refresh: [];
	toggleEdit: [];
	toggleSlideShow: [];
	scrollToTop: [];
	openSearch: [];
	goBack: [];
}>();

const { is_se_enabled } = storeToRefs(lycheeStore);
const noData = computed(() => {
	return !albumStore.isLoading && albumsStore.albums.length === 0 && photosStore.photos.length === 0;
});

const {
	is_share_album_visible,
	toggleDelete,
	toggleMergeAlbum,
	toggleMove,
	toggleRename,
	toggleShareAlbum,
	toggleEmbedCode,
	toggleTag,
	toggleLicense,
	toggleCopy,
	toggleUpload,
	toggleApplyRenamer,
	is_apply_renamer_visible,
	toggleWatermarkConfirm,
	is_watermark_confirm_visible,
} = useGalleryModals(togglableStore);

const { toggleBuyMe } = useBuyMeActions(albumStore, photosStore, orderManagement, catalogStore, toast);

const { selectedPhoto, selectedAlbum, selectedPhotos, selectedAlbums, selectedPhotosIds, selectedAlbumsIds, photoSelect, albumSelect, unselect } =
	useSelection(photosStore, albumsStore, togglableStore);

const { photoRoute, getParentId } = usePhotoRoute(router);

function photoClick(photoId: string, _e: MouseEvent) {
	router.push(photoRoute(photoId));
}

function openContextMenuFromHeader(e: MouseEvent): void {
	if (selectedPhotosIds.value.length > 0) {
		contextMenuPhotoOpen(selectedPhotosIds.value[0], e);
	} else if (selectedAlbumsIds.value.length > 0) {
		contextMenuAlbumOpen(e, selectedAlbumsIds.value[0]);
	}
}

function goToPhotosPage(page: number) {
	albumStore.loadPhotos(page, false);
}

function goToAlbumsPage(page: number) {
	albumStore.loadAlbums(page, false);
}

const areStatisticsOpen = ref(false);
function toggleStatistics() {
	if (is_se_enabled) {
		areStatisticsOpen.value = !areStatisticsOpen.value;
	}
}

const is_download_album_visible = ref(false);
const downloadAlbumIds = ref<string[]>([]);
const is_download_photo_visible = ref(false);
const downloadPhotoIds = ref<string[]>([]);
const downloadFromId = ref<string | null>(null);

function toggleDownloadAlbumFromHero() {
	if (albumStore.album === undefined) return;
	downloadAlbumIds.value = [albumStore.album.id];
	is_download_album_visible.value = true;
}

function toggleScanFacesFromHero() {
	if (albumStore.album === undefined) return;
	FaceDetectionService.scanAlbum(albumStore.album.id)
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("people.scan_success"),
				life: 3000,
			});
		})
		.catch((e) => {
			toast.add({
				severity: "error",
				summary: trans("toasts.error"),
				detail: e.response?.data?.message || trans("toasts.error"),
				life: 3000,
			});
		});
}

function toggleDownloadAlbumFromSelection() {
	downloadAlbumIds.value = selectedAlbumsIds.value;
	is_download_album_visible.value = true;
}

const albumPanelConfig = computed<AlbumThumbConfig>(() => ({
	album_thumb_css_aspect_ratio: albumStore.config?.album_thumb_css_aspect_ratio ?? "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

const photoCallbacks = {
	star: () => {
		PhotoService.highlight(selectedPhotosIds.value, true);
		// Update the photos in the store immediately to reflect the change
		selectedPhotosIds.value.forEach((photoId) => {
			const photo = photosStore.photos.find((p) => p.id === photoId);
			if (photo) {
				photo.is_highlighted = true;
			}
		});
		AlbumService.clearCache(albumStore.album?.id);
	},
	unstar: () => {
		PhotoService.highlight(selectedPhotosIds.value, false);
		// Update the photos in the store immediately to reflect the change
		selectedPhotosIds.value.forEach((photoId) => {
			const photo = photosStore.photos.find((p) => p.id === photoId);
			if (photo) {
				photo.is_highlighted = false;
			}
		});
		AlbumService.clearCache(albumStore.album?.id);
	},
	setAsCover: () => {
		if (albumStore.album === undefined) return;
		PhotoService.setAsCover(selectedPhoto.value!.id, albumStore.album.id);
		// Update the album's cover_id immediately to reflect the change (toggle behavior)
		if (albumStore.modelAlbum !== undefined) {
			albumStore.modelAlbum.cover_id = albumStore.modelAlbum.cover_id === selectedPhoto.value!.id ? null : selectedPhoto.value!.id;
		}
		if (albumStore.tagAlbum !== undefined) {
			albumStore.tagAlbum.cover_id = albumStore.tagAlbum.cover_id === selectedPhoto.value!.id ? null : selectedPhoto.value!.id;
		}
		AlbumService.clearCache(albumStore.album.id);
	},
	setAsHeader: () => {
		if (albumStore.album === undefined) return;
		PhotoService.setAsHeader(selectedPhoto.value!.id, albumStore.album.id, false);
		// Update the album's header_id immediately to reflect the change (toggle behavior)
		const isToggleOff = albumStore.modelAlbum?.header_id === selectedPhoto.value!.id;
		if (albumStore.modelAlbum !== undefined) {
			albumStore.modelAlbum.header_id = isToggleOff ? null : selectedPhoto.value!.id;
			if (albumStore.modelAlbum.preFormattedData) {
				albumStore.modelAlbum.preFormattedData.header_photo_focus = null;
			}
		}
		if (
			albumStore.album !== undefined &&
			"editable" in albumStore.album &&
			albumStore.album.editable !== undefined &&
			albumStore.album.editable !== null
		) {
			albumStore.album.editable.header_id = isToggleOff ? null : selectedPhoto.value!.id;
			albumStore.album.preFormattedData.header_photo_focus = null;
		}
		// Update the header image URL in the album's preFormattedData
		if (albumStore.album.preFormattedData) {
			if (isToggleOff) {
				albumStore.album.preFormattedData.url = null;
			} else {
				// Use medium or small variant for the header image
				const headerUrl = selectedPhoto.value!.size_variants.medium?.url ?? selectedPhoto.value!.size_variants.small?.url ?? null;
				albumStore.album.preFormattedData.url = headerUrl;
				albumStore.album.preFormattedData.header_photo_focus = null;
			}
		}
		AlbumService.clearCache(albumStore.album.id);
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
	toggleScanFaces: () => {
		FaceDetectionService.scanPhotos(selectedPhotosIds.value)
			.then(() => {
				toast.add({
					severity: "success",
					summary: trans("toasts.success"),
					detail: trans("people.scan_success"),
					life: 3000,
				});
			})
			.catch((e) => {
				toast.add({
					severity: "error",
					summary: trans("toasts.error"),
					detail: e.response?.data?.message || trans("toasts.error"),
					life: 3000,
				});
			});
	},
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

function togglePin() {
	if (!selectedAlbum.value) return;
	if (!albumStore.album) return;

	AlbumService.setPinned(selectedAlbum.value.id, !selectedAlbum.value.is_pinned).then(() => {
		if (albumStore.album === undefined) return; // should not happen, but hey...

		AlbumService.clearAlbums();
		AlbumService.clearCache(albumStore.album.id);
		emits("refresh");
		unselect();
	});
}

const albumCallbacks = {
	setAsCover: () => {
		if (albumStore.album === undefined) return;
		if (selectedAlbum.value?.thumb?.id === undefined) return;
		PhotoService.setAsCover(selectedAlbum.value!.thumb?.id, albumStore.album.id);
		// Update the album's cover_id immediately to reflect the change (toggle behavior)
		if (albumStore.modelAlbum !== undefined) {
			albumStore.modelAlbum.cover_id =
				albumStore.modelAlbum.cover_id === selectedAlbum.value!.thumb?.id ? null : selectedAlbum.value!.thumb?.id;
		}
		AlbumService.clearCache(albumStore.album.id);
		emits("refresh");
	},
	toggleRename: toggleRename,
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		toggleDownloadAlbumFromSelection();
	},
	togglePin: togglePin,
	toggleApplyRenamer: toggleApplyRenamer,
	toggleScanFaces: () => {
		if (albumStore.album === undefined) return;
		FaceDetectionService.scanAlbum(selectedAlbum.value!.id)
			.then(() => {
				toast.add({
					severity: "success",
					summary: trans("toasts.success"),
					detail: trans("people.scan_success"),
					life: 3000,
				});
			})
			.catch((e) => {
				toast.add({
					severity: "error",
					summary: trans("toasts.error"),
					detail: e.response?.data?.message || trans("toasts.error"),
					life: 3000,
				});
			});
	},
	copyHighlighted: () => {
		const highlighted = photosStore.photos.filter((p) => p.is_highlighted);
		const selectedNames = highlighted
			.map((p) => {
				const dotIndex = p.title.lastIndexOf(".");
				return dotIndex > 0 ? p.title.substring(0, dotIndex) : p.title;
			})
			.join(", ");
		navigator.clipboard
			.writeText(selectedNames)
			.then(() =>
				toast.add({
					severity: "info",
					summary: "Info",
					detail: trans("dialogs.selected_images.names_copied") + ". " + selectedNames,
					life: 3000,
				}),
			)
			.catch(() =>
				toast.add({
					severity: "error",
					summary: "Error",
					detail: "Failed to copy to clipboard",
					life: 3000,
				}),
			);
	},
	scrollToPaginatorTop: () => {
		if (photoPanel.value) {
			photoPanel.value.$el.scrollIntoView({ behavior: "smooth" });
		}
	},
};

const computedAlbum = computed(() => albumStore.album);
const computedConfig = computed(() => albumStore.config);
const photoPanel = ref<ComponentPublicInstance | null>(null);

const { Menu } = useContextMenu(
	{
		config: computedConfig,
		album: computedAlbum,
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIds: selectedPhotosIds,
		selectedAlbum: selectedAlbum,
		selectedAlbums: selectedAlbums,
		selectedAlbumsIds: selectedAlbumsIds,
	},
	photoCallbacks,
	albumCallbacks,
);

// The shared useContextMenu composable's photoMenuOpen/albumMenuOpen call an internal
// `menu.value.show(e)` that assumes a PrimeVue <ContextMenu ref="menu"> instance exists.
// In v8 the menu is opened declaratively by UContextMenu wrapping the gallery view, so we
// replicate only the selection side-effects here and let the native contextmenu event bubble
// up to UContextMenu's own trigger to open the menu.
//
// UContextMenu's `:disabled` is intentionally NOT `Menu.length === 0`: `Menu` only has
// items once something is selected, and that selection is set by this very handler in
// response to the same contextmenu event. Reka's ContextMenuTrigger reads its `disabled`
// prop synchronously (before Vue can flush the reactive update from the line below), so
// gating on `Menu.length` made right-click on a not-yet-selected photo/album silently
// no-op forever. `noData` (no photos/albums to right-click on at all) is stable across
// the click and doesn't race.
function contextMenuPhotoOpen(photoId: string, _e: MouseEvent): void {
	selectedAlbumsIds.value = [];
	if (!selectedPhotosIds.value.includes(photoId)) {
		selectedPhotosIds.value = [photoId];
	}
}

function contextMenuAlbumOpen(_e: MouseEvent, albumId: string): void {
	selectedPhotosIds.value = [];
	if (!selectedAlbumsIds.value.includes(albumId)) {
		selectedAlbumsIds.value = [albumId];
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
</script>
