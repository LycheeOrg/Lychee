<template>
	<Dialog
		v-model:visible="showBuyMeDialog"
		:modal="true"
		:closable="true"
		class="w-md"
		pt:root:class="border-none"
		pt:mask:style="backdrop-filter: blur(2px)"
		close-on-escape
		@hide="resetBuyMeDialog"
	>
		<template #container>
			<div class="px-8 pt-6 pb-4">
				<div v-if="catalogStore.description" class="text-center text-muted-color mb-4">
					{{ catalogStore.description }}
				</div>
				<div>
					<div
						v-for="price in prices"
						:key="`${price.size_variant}-${price.license_type}`"
						class="border-b last:border-b-0 border-surface-300 dark:border-surface-700 flex flex-row justify-between items-center gap-4 py-1"
					>
						<div class="flex flex-col w-1/3">
							<div class="font-bold">{{ price.size_variant }}</div>
							<div class="text-sm text-muted-color">{{ price.license_type }}</div>
						</div>
						<div class="font-bold text-center text-lg">{{ price.price }}</div>
						<Button
							severity="primary"
							text
							class="rounded border-none font-bold"
							icon="pi pi-cart-arrow-down"
							@click="addPhotoToOrder(price.size_variant, price.license_type)"
						/>
					</div>
				</div>
			</div>
			<Button severity="secondary" class="rounded-b-xl font-bold" @click="resetBuyMeDialog">Cancel</Button>
			<!-- ADD text later that explains which license to chose -->
			<!-- <div class="text-center text-muted-color mt-4" v-if="[...prices.reduce((acc, e) => acc.set(e.license_type, (acc.get(e.license_type) || 0) + 1), new Map()).keys()].length > 1"> -->
			<!-- </div> -->
		</template>
	</Dialog>
	<div class="h-svh overflow-y-hidden flex flex-col">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<AlbumHeader
			v-if="albumStore.isLoaded && userStore.isLoaded"
			@refresh="emits('refresh')"
			@toggle-edit="emits('toggleEdit')"
			@open-search="emits('openSearch')"
			@go-back="emits('goBack')"
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
					:idx-shift="0"
					:selected-albums="selectedAlbumsIds"
					:is-timeline="albumStore.config.is_album_timeline_enabled"
					@clicked="albumClick"
					@contexted="albumMenuOpen"
				/>
				<div v-if="photosStore.photos.length > 0 && albumStore.hasPagination" class="flex justify-center w-full -mb-[100%]">
					<Paginator
						v-model:first="firstValue"
						:rows="albumStore.per_page"
						:total-records="albumStore.total"
						:always-show="false"
						:pt:pcRowPerPageDropdown:class="'hidden'"
					/>
				</div>
				<PhotoThumbPanel
					v-if="layoutStore.config && photosStore.photos.length > 0"
					header="gallery.album.header_photos"
					:photos="photosStore.photos"
					:photos-timeline="photosStore.photosTimeline"
					:selected-photos="selectedPhotosIds"
					:is-timeline="albumStore.config.is_photo_timeline_enabled"
					:with-control="true"
					@clicked="photoClick"
					@selected="photoSelect"
					@contexted="photoMenuOpen"
					@toggle-buy-me="toggleBuyMe"
				/>
				<div v-if="photosStore.photos.length > 0 && albumStore.hasPagination" class="flex justify-center w-full">
					<Paginator
						v-model:first="firstValue"
						:rows="albumStore.per_page"
						:total-records="albumStore.total"
						:always-show="false"
						:pt:pcRowPerPageDropdown:class="'hidden'"
					/>
				</div>
				<ScrollTop v-if="!props.isPhotoOpen" target="parent" />
				<GalleryFooter v-once />
			</div>
			<ShareAlbum :key="`share_modal_${albumStore.album.id}`" v-model:visible="is_share_album_visible" :title="albumStore.album.title" />

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
import Dialog from "primevue/dialog";
import Paginator from "primevue/paginator";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useUserStore } from "@/stores/UserState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useCatalogStore } from "@/stores/CatalogState";

const router = useRouter();

const props = defineProps<{
	isPhotoOpen: boolean;
	first: number | undefined;
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
	"update:first": [value: number];
}>();

const { is_se_enabled } = storeToRefs(lycheeStore);
const noData = computed(() => albumsStore.albums.length === 0 && photosStore.photos.length === 0);

const {
	is_share_album_visible,
	toggleDelete,
	toggleMergeAlbum,
	toggleMove,
	toggleRename,
	toggleShareAlbum,
	toggleEmbedCode,
	toggleTag,
	toggleCopy,
	toggleUpload,
} = useGalleryModals(togglableStore);

const { prices, toggleBuyMe, addPhotoToOrder, showBuyMeDialog, resetBuyMeDialog } = useBuyMeActions(albumStore, orderManagement, catalogStore);

const {
	selectedPhotosIdx,
	selectedAlbumsIdx,
	selectedPhoto,
	selectedAlbum,
	selectedPhotos,
	selectedAlbums,
	selectedPhotosIds,
	selectedAlbumsIds,
	photoSelect,
	albumClick,
	unselect,
} = useSelection(photosStore, albumsStore, togglableStore);

const { photoRoute, getParentId } = usePhotoRoute(router);

function photoClick(idx: number, _e: MouseEvent) {
	router.push(photoRoute(photosStore.photos[idx].id));
}

const areStatisticsOpen = ref(false);
function toggleStatistics() {
	if (is_se_enabled) {
		areStatisticsOpen.value = !areStatisticsOpen.value;
	}
}

const firstValue = computed({
	get: () => props.first,
	set: (val: number) => {
		albumStore.current_page = val;
		emits("update:first", val);
	},
});

const albumPanelConfig = computed<AlbumThumbConfig>(() => ({
	album_thumb_css_aspect_ratio: albumStore.config?.album_thumb_css_aspect_ratio ?? "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

const photoCallbacks = {
	star: () => {
		PhotoService.star(selectedPhotosIds.value, true);
		AlbumService.clearCache(albumStore.album?.id);
		emits("refresh");
	},
	unstar: () => {
		PhotoService.star(selectedPhotosIds.value, false);
		AlbumService.clearCache(albumStore.album?.id);
		emits("refresh");
	},
	setAsCover: () => {
		if (albumStore.album === undefined) return;
		PhotoService.setAsCover(selectedPhoto.value!.id, albumStore.album.id);
		AlbumService.clearCache(albumStore.album.id);
		emits("refresh");
	},
	setAsHeader: () => {
		if (albumStore.album === undefined) return;
		PhotoService.setAsHeader(selectedPhoto.value!.id, albumStore.album.id, false);
		AlbumService.clearCache(albumStore.album.id);
		emits("refresh");
	},
	toggleTag: toggleTag,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		PhotoService.download(selectedPhotosIds.value, getParentId());
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
};

const computedAlbum = computed(() => albumStore.album);
const computedConfig = computed(() => albumStore.config);

const { menu, Menu, photoMenuOpen, albumMenuOpen } = useContextMenu(
	{
		config: computedConfig,
		album: computedAlbum,
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIdx: selectedPhotosIdx,
		selectedAlbum: selectedAlbum,
		selectedAlbums: selectedAlbums,
		selectedAlbumIdx: selectedAlbumsIdx,
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
