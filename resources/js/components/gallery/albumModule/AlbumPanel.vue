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
		/>
		<template v-if="albumStore.album && albumStore.config && userStore.isLoaded">
			<div id="galleryView" class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto h-full select-none">
				<SelectDrag :with-scroll="true" />
				<AlbumEdit v-if="albumStore.rights?.can_edit" />
				<div v-if="noData" class="flex w-full flex-col h-full items-center justify-center text-xl text-muted-color gap-8">
					<span class="block">
						{{ $t("gallery.album.no_results") }}
					</span>
					<Button
						v-if="albumStore.rights?.can_upload && albumStore.modelAlbum !== undefined"
						severity="warn"
						class="rounded max-w-xs w-full border-none font-bold"
						icon="pi pi-upload"
						@click="toggleUpload"
						>{{ $t("gallery.album.upload") }}</Button
					>
				</div>
				<AlbumHero
					v-if="!noData"
					@open-sharing-modal="toggleShareAlbum"
					@open-embed-code="toggleEmbedCode"
					@open-statistics="toggleStatistics"
					@toggle-slide-show="emits('toggleSlideShow')"
					@toggle-apply-renamer="toggleApplyRenamer"
					@toggle-watermark-confirm="toggleWatermarkConfirm"
				/>
				<template v-if="is_se_enabled && userStore.isLoggedIn">
					<AlbumStatistics
						v-if="photosStore.photos.length > 0"
						:key="`statistics_${albumStore.album?.id}`"
						v-model:visible="areStatisticsOpen"
					/>
				</template>
				<AlbumThumbPanel
					v-if="albumsStore.albums.length > 0"
					header="gallery.album.header_albums"
					:albums="albumsStore.albums"
					:config="albumPanelConfig"
					:is-alone="photosStore.photos.length === 0"
					:selected-albums="selectedAlbumsIds"
					:is-timeline="albumStore.config.is_album_timeline_enabled"
					@clicked="albumSelect"
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
				<ScrollTop v-if="!props.isPhotoOpen" target="parent" />
				<GalleryFooter v-once />
			</div>
			<ShareAlbum :key="`share_modal_${albumStore.album.id}`" v-model:visible="is_share_album_visible" :title="albumStore.album.title" />
			<ApplyRenamerDialog
				v-model:visible="is_apply_renamer_visible"
				:album-id="albumStore.album.id"
				:photo-ids="selectedPhotosIds"
				:album-ids="selectedAlbumsIds"
				@applied="emits('refresh')"
			/>
			<WatermarkConfirmDialog v-model:visible="is_watermark_confirm_visible" :album-id="albumStore.album.id" @watermarked="emits('refresh')" />

			<!-- Dialogs -->
			<ContextMenu ref="menu" :model="Menu" :class="Menu.length === 0 ? 'hidden' : ''">
				<template #item="{ item, props }">
					<Divider v-if="item.is_divider" />
					<a v-else v-ripple v-bind="props.action" @click="item.callback">
						<span :class="item.icon" />
						<span class="ml-2">
							<!-- @vue-ignore -->
							{{ $t(item.label) }}
						</span>
					</a>
				</template>
			</ContextMenu>
		</template>
	</div>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import AlbumThumbPanel from "@/components/gallery/albumModule/AlbumThumbPanel.vue";
import PhotoThumbPanel from "@/components/gallery/albumModule/PhotoThumbPanel.vue";
import ShareAlbum from "@/components/modals/ShareAlbum.vue";
import AlbumHero from "@/components/gallery/albumModule/AlbumHero.vue";
import AlbumEdit from "@/components/drawers/AlbumEdit.vue";
import AlbumHeader from "@/components/headers/AlbumHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useSelection } from "@/composables/selections/selections";
import Divider from "primevue/divider";
import ScrollTop from "primevue/scrolltop";
import ContextMenu from "primevue/contextmenu";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { AlbumThumbConfig } from "@/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import Button from "primevue/button";
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import AlbumStatistics from "@/components/drawers/AlbumStatistics.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useRouter } from "vue-router";
import SelectDrag from "@/components/forms/album/SelectDrag.vue";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useBuyMeActions } from "@/composables/album/buyMeActions";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useUserStore } from "@/stores/UserState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useCatalogStore } from "@/stores/CatalogState";
import BuyMeDialog from "@/components/forms/gallery-dialogs/BuyMeDialog.vue";
import ApplyRenamerDialog from "@/components/forms/album/ApplyRenamerDialog.vue";
import WatermarkConfirmDialog from "@/components/forms/album/WatermarkConfirmDialog.vue";
import { useToast } from "primevue/usetoast";
import Pagination from "@/components/pagination/Pagination.vue";
import { trans } from "laravel-vue-i18n";

const router = useRouter();
const toast = useToast();

const props = defineProps<{
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
	return albumsStore.albums.length === 0 && photosStore.photos.length === 0;
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
		AlbumService.clearCache(albumStore.album.id);
	},
	setAsHeader: () => {
		if (albumStore.album === undefined) return;
		PhotoService.setAsHeader(selectedPhoto.value!.id, albumStore.album.id, false);
		// Update the album's header_id immediately to reflect the change (toggle behavior)
		const isToggleOff = albumStore.modelAlbum?.header_id === selectedPhoto.value!.id;
		if (albumStore.modelAlbum !== undefined) {
			albumStore.modelAlbum.header_id = isToggleOff ? null : selectedPhoto.value!.id;
		}
		// Update the header image URL in the album's preFormattedData
		if (albumStore.album.preFormattedData) {
			if (isToggleOff) {
				albumStore.album.preFormattedData.url = null;
			} else {
				// Use medium or small variant for the header image
				const headerUrl = selectedPhoto.value!.size_variants.medium?.url ?? selectedPhoto.value!.size_variants.small?.url ?? null;
				albumStore.album.preFormattedData.url = headerUrl;
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
		PhotoService.download(selectedPhotosIds.value, getParentId());
	},
	toggleApplyRenamer: toggleApplyRenamer,
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
		AlbumService.download(selectedAlbumsIds.value);
	},
	togglePin: togglePin,
	toggleApplyRenamer: toggleApplyRenamer,
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
};

const computedAlbum = computed(() => albumStore.album);
const computedConfig = computed(() => albumStore.config);

const {
	menu,
	Menu,
	photoMenuOpen: contextMenuPhotoOpen,
	albumMenuOpen: contextMenuAlbumOpen,
} = useContextMenu(
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
</script>
<style lang="css">
/* Kill the border of ScrollTop */
.p-scrolltop {
	border: none;
}
</style>
