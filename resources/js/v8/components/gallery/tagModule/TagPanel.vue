<template>
	<div class="h-svh overflow-y-hidden flex flex-col">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<div class="w-full border-0 h-14 flex items-center justify-between px-2" v-if="tagStore.tag">
			<GoBack @go-back="emits('goBack')" />
			<span class="absolute left-1/2 -translate-x-1/2 pointer-events-none">{{ tagStore.tag.name }}</span>
			<div></div>
		</div>

		<UContextMenu :items="menuSections" :disabled="photosStore.photos.length === 0" class="contents">
			<div id="galleryView" class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto h-full">
				<div v-if="photosStore.photos.length === 0" class="flex w-full flex-col h-full items-center justify-center text-xl text-muted gap-8">
					<span class="block">
						{{ $t("gallery.album.no_results") }}
					</span>
				</div>
				<PhotoThumbPanel
					v-else
					header="gallery.album.header_photos"
					:photos="photosStore.photos"
					:selected-photos="selectedPhotosIds"
					:with-control="true"
					@clicked="photoClick"
					@selected="selectPhoto"
					@contexted="contextMenuPhotoOpen"
				/>
				<GalleryFooter v-once />
			</div>
		</UContextMenu>

		<!-- Dialogs -->
		<DownloadAlbum v-model:open="is_download_photo_visible" :photo-ids="downloadPhotoIds" :from-id="downloadFromId" />
	</div>
</template>
<script setup lang="ts">
import PhotoThumbPanel from "@/v8/components/gallery/albumModule/PhotoThumbPanel.vue";
import { useSelection } from "@/composables/selections/selections";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";
import ModerationService from "@/services/moderation-service";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import GalleryFooter from "@/v8/components/footers/GalleryFooter.vue";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useRouter } from "vue-router";
import GoBack from "@/v8/components/headers/GoBack.vue";
import { usePhotosStore } from "@/stores/PhotosState";
import { useTagStore } from "@/stores/TagState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import DownloadAlbum from "@/v8/components/modals/DownloadAlbum.vue";
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import type { ContextMenuItem } from "@nuxt/ui";

const router = useRouter();
const togglableStore = useTogglablesStateStore();
const photosStore = usePhotosStore();
const tagStore = useTagStore();
const albumsStore = useAlbumsStore();

const emits = defineEmits<{
	refresh: [];
	goBack: [];
}>();

const { toggleDelete, toggleMove, toggleRename, toggleTag, toggleLicense, toggleCopy, toggleApplyRenamer } = useGalleryModals(togglableStore);

const { selectedPhoto, selectedPhotos, selectedPhotosIds, photoSelect: selectPhoto } = useSelection(photosStore, albumsStore, togglableStore);

const { photoRoute, getParentId } = usePhotoRoute(router);

const is_download_photo_visible = ref(false);
const downloadPhotoIds = ref<string[]>([]);
const downloadFromId = ref<string | null>(null);

function photoClick(photoId: string, _e: MouseEvent) {
	router.push(photoRoute(photoId));
}

const photoCallbacks = {
	star: () => {
		PhotoService.highlight(selectedPhotosIds.value, true);
		AlbumService.clearCache();
		emits("refresh");
	},
	unstar: () => {
		PhotoService.highlight(selectedPhotosIds.value, false);
		AlbumService.clearCache();
		emits("refresh");
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
	toggleRename: () => {},
	toggleMerge: () => {},
	toggleMove: () => {},
	toggleDelete: () => {},
	toggleDownload: () => {},
	togglePin: () => {},
	toggleApplyRenamer: () => {},
	toggleScanFaces: () => {},
};

const { Menu } = useContextMenu(
	{
		selectedPhoto: selectedPhoto,
		selectedPhotos: selectedPhotos,
		selectedPhotosIds: selectedPhotosIds,
	},
	photoCallbacks,
	albumCallbacks,
);

// See AlbumPanel.vue for why the composable's imperative photoMenuOpen/albumMenuOpen are
// bypassed in favor of a declarative UContextMenu wrapping the gallery view, and why
// `:disabled` above must not depend on `Menu.length` (that races against the selection
// side-effect this handler performs on the very same contextmenu event).
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
</script>
