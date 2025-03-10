<template>
	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<AlbumHeader
				v-if="album && config && user"
				:album="album"
				:config="config"
				:user="user"
				@refresh="emits('refresh')"
				@toggle-slide-show="emits('toggleSlideShow')"
				@toggle-edit="emits('toggleEdit')"
				@go-back="emits('goBack')"
			/>
		</Collapse>
		<template v-if="config && album">
			<div
				id="galleryView"
				class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto"
				:class="is_full_screen ? 'h-svh' : 'h-[calc(100vh-3.5rem)]'"
			>
				<AlbumEdit v-if="album.rights.can_edit" :album="album" :config="config" />
				<div v-if="noData" class="flex w-full flex-col h-full items-center justify-center text-xl text-muted-color gap-8">
					<span class="block">
						{{ $t("gallery.album.no_results") }}
					</span>
					<Button
						v-if="album.rights.can_upload && modelAlbum !== undefined"
						severity="warn"
						@click="toggleUpload"
						class="rounded max-w-xs w-full border-none font-bold"
						icon="pi pi-upload"
						>{{ $t("gallery.album.upload") }}</Button
					>
				</div>
				<AlbumHero
					v-if="!noData"
					:album="album"
					:has-hidden="hasHidden"
					@open-sharing-modal="toggleShareAlbum"
					@open-statistics="toggleStatistics"
				/>
				<template v-if="is_se_enabled && user?.id !== null">
					<AlbumStatistics
						:photos="photos"
						:config="config"
						:album="album"
						v-model:visible="areStatisticsOpen"
						:key="'statistics_' + album.id"
					/>
				</template>
				<AlbumThumbPanel
					v-if="children !== null && children.length > 0"
					header="gallery.album.header_albums"
					:album="modelAlbum"
					:albums="children"
					:config="albumPanelConfig"
					:is-alone="!photos?.length"
					@clicked="albumClick"
					@contexted="albumMenuOpen"
					:idx-shift="0"
					:selected-albums="selectedAlbumsIds"
					:is-timeline="config.is_album_timeline_enabled"
				/>
				<PhotoThumbPanel
					v-if="layoutConfig !== null && photos !== null && photos.length > 0"
					header="gallery.album.header_photos"
					:photos="photos"
					:album="album"
					:gallery-config="layoutConfig"
					:photo-layout="config.photo_layout"
					:selected-photos="selectedPhotosIds"
					@clicked="photoClick"
					@selected="photoSelect"
					@contexted="photoMenuOpen"
					:is-timeline="config.is_photo_timeline_enabled"
					:with-control="true"
				/>
				<GalleryFooter v-once />
				<ScrollTop v-if="!props.isPhotoOpen" target="parent" />
			</div>
			<ShareAlbum v-model:visible="is_share_album_visible" :title="album.title" :key="'share_modal_' + album.id" />

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
import { Collapse } from "vue-collapsed";
import Button from "primevue/button";
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import AlbumStatistics from "@/components/drawers/AlbumStatistics.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useRouter } from "vue-router";

const router = useRouter();

const props = defineProps<{
	modelAlbum: App.Http.Resources.Models.AlbumResource | undefined;
	album:
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource
		| undefined;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig | undefined;
	user: App.Http.Resources.Models.UserResource | undefined;
	layoutConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
	isPhotoOpen: boolean;
}>();

const modelAlbum = ref(props.modelAlbum);
const album = ref(props.album);
const hasHidden = computed(() => modelAlbum.value !== undefined && modelAlbum.value.albums.filter((album) => album.is_nsfw).length > 0);
const photos = computed<App.Http.Resources.Models.PhotoResource[]>(() => album.value?.photos ?? []);

const config = ref(props.config);

const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const layoutConfig = ref(props.layoutConfig);

const emits = defineEmits<{
	refresh: [];
	toggleEdit: [];
	toggleSlideShow: [];
	scrollToTop: [];
	goBack: [];
}>();

const { is_full_screen } = storeToRefs(togglableStore);
const { is_se_enabled } = storeToRefs(lycheeStore);

const children = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(() => modelAlbum.value?.albums ?? []);
const noData = computed(() => children.value.length === 0 && (photos.value === null || photos.value.length === 0));

const { is_share_album_visible, toggleDelete, toggleMergeAlbum, toggleMove, toggleRename, toggleShareAlbum, toggleTag, toggleCopy, toggleUpload } =
	useGalleryModals(togglableStore);

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
} = useSelection(photos, children, togglableStore);

const { photoRoute } = usePhotoRoute(togglableStore);

function photoClick(idx: number, e: MouseEvent) {
	router.push(photoRoute(album.value?.id, photos.value[idx].id));
}

const areStatisticsOpen = ref(false);
function toggleStatistics() {
	if (is_se_enabled) {
		areStatisticsOpen.value = !areStatisticsOpen.value;
	}
}

const albumPanelConfig = computed<AlbumThumbConfig>(() => ({
	album_thumb_css_aspect_ratio: config.value?.album_thumb_css_aspect_ratio ?? "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

const photoCallbacks = {
	star: () => {
		PhotoService.star(selectedPhotosIds.value, true);
		AlbumService.clearCache(album.value?.id);
		emits("refresh");
	},
	unstar: () => {
		PhotoService.star(selectedPhotosIds.value, false);
		AlbumService.clearCache(album.value?.id);
		emits("refresh");
	},
	setAsCover: () => {
		if (album.value === undefined) return;
		PhotoService.setAsCover(selectedPhoto.value!.id, album.value.id);
		AlbumService.clearCache(album.value.id);
		emits("refresh");
	},
	setAsHeader: () => {
		if (album.value === undefined) return;
		PhotoService.setAsHeader(selectedPhoto.value!.id, album.value.id, false);
		AlbumService.clearCache(album.value.id);
		emits("refresh");
	},
	toggleTag: toggleTag,
	toggleRename: toggleRename,
	toggleCopyTo: toggleCopy,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		PhotoService.download(selectedPhotosIds.value);
	},
};

const albumCallbacks = {
	setAsCover: () => {
		if (album.value === undefined) return;
		if (selectedAlbum.value?.thumb?.id === undefined) return;
		PhotoService.setAsCover(selectedAlbum.value!.thumb?.id, album.value.id);
		AlbumService.clearCache(album.value.id);
		emits("refresh");
	},
	toggleRename: toggleRename,
	toggleMerge: toggleMergeAlbum,
	toggleMove: toggleMove,
	toggleDelete: toggleDelete,
	toggleDownload: () => {
		AlbumService.download(selectedAlbumsIds.value);
	},
};

const { menu, Menu, photoMenuOpen, albumMenuOpen } = useContextMenu(
	{
		config: config,
		album: album,
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
