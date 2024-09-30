<template>
	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<Toolbar class="w-full border-0 h-14">
				<template #start>
					<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="goBack" />
				</template>
				<template #center>
					<span class="sm:hidden font-bold">
						{{ $t("lychee.SEARCH") }}
					</span>
					<span class="hidden sm:block font-bold text-sm lg:text-base text-center w-full">{{ title }}</span>
				</template>
				<template #end> </template>
			</Toolbar>
		</Collapse>
		<SearchBox v-if="searchMinimumLengh !== undefined" :search-minimum-lengh="searchMinimumLengh" v-model:search="search_term" @search="search" />
		<template v-if="isSearching">
			<div class="flex w-full h-full items-center justify-center text-xl text-muted-color">
				<span class="block">
					{{ "Searching..." }}
				</span>
			</div>
		</template>
		<template v-if="noData">
			<div class="flex w-full h-full items-center justify-center text-xl text-muted-color">
				<span class="block">
					{{ "Nothing to see here" }}
				</span>
			</div>
		</template>
		<template v-if="!noData">
			<div
				class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto"
				:class="is_full_screen ? 'h-svh' : 'h-[calc(100vh-3.5rem)]'"
			>
				<AlbumThumbPanel
					v-if="albums.length > 0"
					:header="albumHeader"
					:album="null"
					:albums="albums"
					:config="albumPanelConfig"
					:is-alone="false"
					:are-nsfw-visible="are_nsfw_visible"
					@clicked="albumClick"
					@contexted="albumMenuOpen"
					:idx-shift="0"
					:selected-albums="selectedAlbumsIds"
				/>
				<div class="flex justify-center w-full" v-if="photos.length > 0">
					<Paginator :total-records="total" :rows="per_page" v-model:first="from" @update:first="refresh" :always-show="false" />
				</div>
				<PhotoThumbPanel
					v-if="layout !== null && photos.length > 0"
					:header="photoHeader"
					:photos="photos"
					:album="undefined"
					:gallery-config="layout"
					:selected-photos="selectedPhotosIds"
					@clicked="photoClick"
					@contexted="photoMenuOpen"
				/>
			</div>

			<!-- Dialogs -->
			<PhotoTagDialog
				v-model:visible="isTagVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				@tagged="refresh"
			/>
			<PhotoCopyDialog
				v-model:visible="isCopyVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				@copied="refresh"
			/>
			<MoveDialog
				v-model:visible="isMoveVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@moved="refresh"
			/>
			<DeleteDialog
				v-model:visible="isDeleteVisible"
				:parent-id="albumid"
				:photo="selectedPhoto"
				:photo-ids="selectedPhotosIds"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@deleted="refresh"
			/>
			<RenameDialog v-model:visible="isRenameVisible" :parent-id="undefined" :album="selectedAlbum" :photo="selectedPhoto" @renamed="refresh" />
			<AlbumMergeDialog
				v-model:visible="isMergeAlbumVisible"
				:parent-id="albumid"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@merged="refresh"
			/>

			<ContextMenu ref="menu" :model="Menu" :class="Menu.length === 0 ? 'hidden' : ''">
				<template #item="{ item, props }">
					<Divider v-if="item.is_divider" />
					<a v-else v-ripple v-bind="props.action" @click="item.callback">
						<span :class="item.icon" />
						<span class="ml-2">{{ $t(item.label) }}</span>
					</a>
				</template>
			</ContextMenu>
		</template>
	</div>
</template>
<script setup lang="ts">
import SearchBox from "@/components/forms/search/SearchBox.vue";
import AlbumThumbPanel from "@/components/gallery/AlbumThumbPanel.vue";
import PhotoThumbPanel from "@/components/gallery/PhotoThumbPanel.vue";
import { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import { useAlbumRefresher } from "@/composables/album/albumRefresher";
import { useContextMenu } from "@/composables/contextMenus/contextMenu";
import { useGalleryModals } from "@/composables/modalsTriggers/galleryModals";
import { useSelection } from "@/composables/selections/selections";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke } from "@vueuse/core";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import Paginator from "primevue/paginator";
import { computed, Ref, ref } from "vue";
import { Collapse } from "vue-collapsed";
import { useRoute, useRouter } from "vue-router";
import { useSearch } from "@/composables/album/searchRefresher";
import { trans } from "laravel-vue-i18n";
import Divider from "primevue/divider";
import ContextMenu from "primevue/contextmenu";
import AlbumMergeDialog from "@/components/forms/gallery-dialogs/AlbumMergeDialog.vue";
import RenameDialog from "@/components/forms/gallery-dialogs/RenameDialog.vue";
import PhotoTagDialog from "@/components/forms/photo/PhotoTagDialog.vue";
import PhotoCopyDialog from "@/components/forms/photo/PhotoCopyDialog.vue";
import MoveDialog from "@/components/forms/gallery-dialogs/MoveDialog.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import PhotoService from "@/services/photo-service";
import AlbumService from "@/services/album-service";

const router = useRouter();
const props = defineProps<{
	albumid?: string;
}>();

const albumid = ref(props.albumid ?? "");
function goBack() {
	if (props.albumid !== undefined) {
		router.push({ name: "album", params: { albumid: props.albumid } });
	} else {
		router.push({ name: "gallery" });
	}
}

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { are_nsfw_visible, is_full_screen, search_page, search_term, is_login_open } = storeToRefs(lycheeStore);
const { albums, photos, noData, searchMinimumLengh, isSearching, from, per_page, total, photoHeader, albumHeader, searchInit, search, refresh } =
	useSearch(albumid, lycheeStore, search_term, search_page);
const { album, config, layout, loadAlbum, loadLayout } = useAlbumRefresher(albumid, auth, is_login_open);

const configForMenu = computed<App.Http.Resources.GalleryConfigs.AlbumConfig>(() => {
	if (config.value !== undefined) {
		return config.value;
	}
	return {
		is_base_album: false,
		is_model_album: false,
		is_accessible: true,
		is_password_protected: false,
		is_map_accessible: false,
		is_mod_frame_enabled: false,
		is_search_accessible: false,
		is_nsfw_warning_visible: false,
		album_thumb_css_aspect_ratio: "aspect-square",
	};
});
const albumForMenu = albumid.value !== "" ? album : null;

const title = computed<string>(() => {
	if (album.value === undefined) {
		return trans(lycheeStore.title);
	}
	return album.value.title;
});

const {
	isDeleteVisible,
	toggleDelete,
	isMergeAlbumVisible,
	toggleMergeAlbum,
	isMoveVisible,
	toggleMove,
	isRenameVisible,
	toggleRename,
	isTagVisible,
	toggleTag,
	isCopyVisible,
	toggleCopy,
} = useGalleryModals();

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
} = useSelection(photos, albums);

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

const { menu, Menu, photoMenuOpen, albumMenuOpen } = useContextMenu(
	{
		config: configForMenu,
		album: albumForMenu,
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
	album_thumb_css_aspect_ratio: "aspect-square",
	album_subtitle_type: lycheeStore.album_subtitle_type,
	display_thumb_album_overlay: lycheeStore.display_thumb_album_overlay,
	album_decoration: lycheeStore.album_decoration,
	album_decoration_orientation: lycheeStore.album_decoration_orientation,
}));

if (albumid.value !== "") {
	loadAlbum();
}

searchInit();
loadLayout();

if (lycheeStore.isSearchActive) {
	search(lycheeStore.search_term);
}

onKeyStroke("Escape", () => {
	goBack();
});
</script>
