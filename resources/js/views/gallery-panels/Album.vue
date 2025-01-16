<template>
	<LoadingProgress v-model:loading="isLoading" />
	<UploadPanel v-if="album?.rights.can_upload" @refresh="refresh" key="upload_modal" />
	<LoginModal v-if="user?.id === null" @logged-in="refresh" />
	<WebauthnModal v-if="user?.id === null" @logged-in="refresh" />
	<AlbumCreateDialog v-if="album?.rights.can_upload && config?.is_model_album" v-model:parent-id="album.id" key="create_album_modal" />
	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<AlbumHeader
				v-if="album && config && user"
				:album="album"
				:config="config"
				:user="user"
				@refresh="refresh"
				@toggle-slide-show="toggleSlideShow"
				@toggle-details="toggleDetails"
			/>
		</Collapse>
		<Unlock :albumid="albumid" :visible="isPasswordProtected" @reload="refresh" @fail="is_login_open = true" />
		<template v-if="config && album">
			<div
				id="galleryView"
				class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto"
				:class="is_full_screen ? 'h-svh' : 'h-[calc(100vh-3.5rem)]'"
				v-on:scroll="onScroll"
			>
				<AlbumEdit v-if="album.rights.can_edit" :album="album" :config="config" />
				<div v-if="noData" class="flex w-full flex-col h-full items-center justify-center text-xl text-muted-color gap-8">
					<span class="block">
						{{ $t("gallery.album.no_results") }}
					</span>
					<Button
						v-if="album.rights.can_upload"
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
					@contexted="photoMenuOpen"
					:is-timeline="config.is_photo_timeline_enabled"
				/>
				<GalleryFooter v-once />
			</div>
			<SensitiveWarning v-if="showNsfwWarning" @click="consent" />
			<ShareAlbum v-model:visible="isShareAlbumVisible" :title="album.title" :key="'share_modal_' + album.id" />
			<!-- Dialogs -->
			<PhotoTagDialog
				v-model:visible="isTagVisible"
				:parent-id="albumid"
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
				v-model:visible="isCopyVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				@copied="
					() => {
						unselect();
						refresh();
					}
				"
			/>
			<MoveDialog
				v-model:visible="isMoveVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@moved="
					() => {
						unselect();
						refresh();
					}
				"
			/>
			<DeleteDialog
				v-model:visible="isDeleteVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@deleted="
					() => {
						unselect();
						refresh();
					}
				"
			/>

			<!-- Dialogs for albums -->
			<RenameDialog
				v-model:visible="isRenameVisible"
				:parent-id="undefined"
				:album="selectedAlbum"
				:photo="selectedPhoto"
				@renamed="
					() => {
						unselect();
						refresh();
					}
				"
			/>
			<AlbumMergeDialog
				v-model:visible="isMergeAlbumVisible"
				:parent-id="albumid"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@merged="
					() => {
						unselect();
						refresh();
					}
				"
			/>

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
import { useAuthStore } from "@/stores/Auth";
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import PhotoThumbPanel from "@/components/gallery/PhotoThumbPanel.vue";
import ShareAlbum from "@/components/modals/ShareAlbum.vue";
import AlbumHero from "@/components/gallery/AlbumHero.vue";
import AlbumEdit from "@/components/drawers/AlbumEdit.vue";
import AlbumHeader from "@/components/headers/AlbumHeader.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke } from "@vueuse/core";
import { getModKey, shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { useSelection } from "@/composables/selections/selections";
import Divider from "primevue/divider";
import ContextMenu from "primevue/contextmenu";
import { useAlbumRefresher } from "@/composables/album/albumRefresher";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import { Collapse } from "vue-collapsed";
import SensitiveWarning from "@/components/gallery/SensitiveWarning.vue";
import Unlock from "@/components/forms/album/Unlock.vue";
import LoginModal from "@/components/modals/LoginModal.vue";
import Button from "primevue/button";
import { useMouseEvents } from "@/composables/album/uploadEvents";
import GalleryFooter from "@/components/footers/GalleryFooter.vue";
import AlbumStatistics from "@/components/drawers/AlbumStatistics.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import UploadPanel from "@/components/modals/UploadPanel.vue";
import AlbumCreateDialog from "@/components/forms/album/AlbumCreateDialog.vue";
import { useScrollable } from "@/composables/album/scrollable";
import { useGetLayoutConfig } from "@/layouts/PhotoLayout";
import WebauthnModal from "@/components/modals/WebauthnModal.vue";
import LoadingProgress from "@/components/gallery/LoadingProgress.vue";

const route = useRoute();
const router = useRouter();

const props = defineProps<{
	albumid: string;
}>();

const albumid = ref(props.albumid);
// flag to open login modal if necessary
const auth = useAuthStore();
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
togglableStore.resetSearch();

const { onScroll, setScroll, scrollToTop } = useScrollable(togglableStore, albumid);
const { is_full_screen, is_login_open, is_slideshow_active, is_upload_visible, list_upload_files } = storeToRefs(togglableStore);
const { are_nsfw_visible, nsfw_consented, is_se_enabled } = storeToRefs(lycheeStore);

function toggleSlideShow() {
	if (album.value === undefined || album.value.photos.length === 0) {
		return;
	}

	is_slideshow_active.value = true;
	router.push({ name: "photo", params: { albumid: album.value.id, photoid: album.value.photos[0].id } });
}

const { layoutConfig, loadLayoutConfig } = useGetLayoutConfig();

// Set up Album ID reference. This one is updated at each page change.
const { isAlbumConsented, isPasswordProtected, isLoading, user, modelAlbum, album, rights, photos, config, hasHidden, refresh } = useAlbumRefresher(
	albumid,
	auth,
	is_login_open,
	nsfw_consented,
);

const children = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(() => modelAlbum.value?.albums ?? []);
const noData = computed(() => children.value.length === 0 && (photos.value === null || photos.value.length === 0));
const showNsfwWarning = computed(() => config.value?.is_nsfw_warning_visible && isAlbumConsented.value === false);

const {
	isDeleteVisible,
	toggleDelete,
	isMergeAlbumVisible,
	toggleMergeAlbum,
	isMoveVisible,
	toggleMove,
	isRenameVisible,
	toggleRename,
	isShareAlbumVisible,
	toggleShareAlbum,
	isTagVisible,
	toggleTag,
	isCopyVisible,
	toggleCopy,
	toggleUpload,
} = useGalleryModals(togglableStore);

const areStatisticsOpen = ref(false);

function toggleStatistics() {
	if (is_se_enabled) {
		areStatisticsOpen.value = !areStatisticsOpen.value;
	}
}

function toggleDetails() {
	scrollToTop();
}

const {
	selectedPhotosIdx,
	selectedAlbumsIdx,
	selectedPhoto,
	selectedAlbum,
	selectedPhotos,
	selectedAlbums,
	selectedPhotosIds,
	selectedAlbumsIds,
	photoClick,
	albumClick,
	selectEverything,
	unselect,
	hasSelection,
} = useSelection(photos, children);

const photoCallbacks = {
	star: () => {
		PhotoService.star(selectedPhotosIds.value, true);
		AlbumService.clearCache(albumid.value);
		refresh();
	},
	unstar: () => {
		PhotoService.star(selectedPhotosIds.value, false);
		AlbumService.clearCache(albumid.value);
		refresh();
	},
	setAsCover: () => {
		PhotoService.setAsCover(selectedPhoto.value!.id, albumid.value);
		AlbumService.clearCache(albumid.value);
		refresh();
	},
	setAsHeader: () => {
		PhotoService.setAsHeader(selectedPhoto.value!.id, albumid.value, false);
		AlbumService.clearCache(albumid.value);
		refresh();
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
		if (selectedAlbum.value?.thumb?.id === undefined) return;
		PhotoService.setAsCover(selectedAlbum.value!.thumb?.id, albumid.value);
		AlbumService.clearCache(albumid.value);
		refresh();
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

const albumPanelConfig = computed<AlbumThumbConfig>(() => ({
	album_thumb_css_aspect_ratio: config.value?.album_thumb_css_aspect_ratio ?? "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

function consent() {
	nsfw_consented.value.push(albumid.value);
	isAlbumConsented.value = true;
}

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
onKeyStroke("f", () => !shouldIgnoreKeystroke() && togglableStore.toggleFullScreen());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && unselect());

// Privileged actions
onKeyStroke("m", () => !shouldIgnoreKeystroke() && album.value?.rights.can_move && hasSelection() && toggleMove());
onKeyStroke(["Delete", "Backspace"], () => !shouldIgnoreKeystroke() && album.value?.rights.can_delete && hasSelection() && toggleDelete());

onKeyStroke([getModKey(), "a"], () => !shouldIgnoreKeystroke() && selectEverything());

const { onPaste, dragEnd, dropUpload } = useMouseEvents(rights, is_upload_visible, list_upload_files);

onMounted(() => {
	// Reset the slideshow.
	is_slideshow_active.value = false;

	window.addEventListener("paste", onPaste);
	window.addEventListener("dragover", dragEnd);
	window.addEventListener("drop", dropUpload);
});

onMounted(async () => {
	const results = await Promise.allSettled([loadLayoutConfig(), refresh()]);

	results.forEach((result, index) => {
		if (result.status === "rejected") {
			console.warn(`Promise ${index} reject with reason: ${result.reason}`);
		}
	});

	if (results.every((result) => result.status === "fulfilled")) {
		setScroll();
	}
});

onUnmounted(() => {
	window.removeEventListener("paste", onPaste);
	window.removeEventListener("dragover", dragEnd);
	window.removeEventListener("drop", dropUpload);
});

watch(
	() => route.params.albumid,
	(newId, _oldId) => {
		unselect();

		albumid.value = newId as string;
		refresh().then(setScroll);
	},
);
</script>
