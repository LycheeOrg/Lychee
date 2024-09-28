<template>
	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<Toolbar class="w-full border-0 h-14">
				<template #start>
					<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text @click="goBack" />
				</template>
				<template #center>
					{{ $t(lycheeStore.title) }}
				</template>
				<template #end> </template>
			</Toolbar>
		</Collapse>
		<SearchBox v-if="searchMinimumLengh !== undefined" :search-minimum-lengh="searchMinimumLengh" v-model:search="search_term" @search="search" />
		<template v-if="searching">
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
					header="lychee.ALBUMS"
					:album="null"
					:albums="albums"
					:config="albumPanelConfig"
					:is-alone="photos.length === 0"
					:are-nsfw-visible="are_nsfw_visible"
					@clicked="albumClick"
					@contexted="albumMenuOpen"
					:idx-shift="0"
					:selected-albums="selectedAlbumsIds"
				/>
				<div class="flex justify-center w-full" v-if="photos.length > 0">
					<Paginator :total-records="total" :rows="per_page" v-model:first="from" @update:first="switchPage" :always-show="false" />
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
			<!-- <PhotoTagDialog
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
			/> -->
			<!-- <RenameDialog v-model:visible="isRenameVisible" :parent-id="undefined" :album="selectedAlbum" :photo="selectedPhoto" @renamed="refresh" />
			<AlbumMergeDialog
				v-model:visible="isMergeAlbumVisible"
				:parent-id="albumid"
				:album="selectedAlbum"
				:album-ids="selectedAlbumsIds"
				@merged="refresh"
			/>

			<ContextMenu ref="menu" :model="Menu">
				<template #item="{ item, props }">
					<Divider v-if="item.is_divider" />
					<a v-else v-ripple v-bind="props.action" @click="item.callback">
						<span :class="item.icon" />
						<span class="ml-2">{{ $t(item.label) }}</span>
					</a>
				</template>
			</ContextMenu> -->
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
import AlbumService from "@/services/album-service";
import SearchService from "@/services/search-service";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { onKeyStroke } from "@vueuse/core";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import Paginator from "primevue/paginator";
import { computed, Ref, ref } from "vue";
import { Collapse } from "vue-collapsed";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
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

const { album, loadAlbum } = useAlbumRefresher(albumid, auth, is_login_open);

const albums = ref<App.Http.Resources.Models.ThumbAlbumResource[]>([]);
const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);
const noData = computed<boolean>(() => albums.value.length === 0 && photos.value.length === 0);
const searchMinimumLengh = ref(undefined as number | undefined);
const layout = ref(null) as Ref<null | App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>;
const searching = ref(false);

const from = ref(0);
const per_page = ref(0);
const total = ref(0);

const photoHeader = computed(() => {
	return trans("lychee.PHOTOS") + " (" + total.value + ")";
});

function loadLayout() {
	AlbumService.getLayout().then((data) => {
		layout.value = data.data;
	});
}

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
} = useGalleryModals();

function search(terms: string) {
	if (terms.length < 3) {
		albums.value = [];
		photos.value = [];
		return;
	}
	lycheeStore.search_album_id = props.albumid;
	search_term.value = terms;
	searching.value = true;
	SearchService.search(props.albumid, search_term.value, search_page.value).then((response) => {
		albums.value = response.data.albums;
		photos.value = response.data.photos;
		from.value = response.data.from;
		per_page.value = response.data.per_page;
		total.value = response.data.total;
		searching.value = false;
	});
}

function switchPage() {
	search_page.value = Math.ceil(from.value / per_page.value) + 1;
	SearchService.search(props.albumid, search_term.value, search_page.value).then((response) => {
		albums.value = response.data.albums;
		photos.value = response.data.photos;
		from.value = response.data.from;
		per_page.value = response.data.per_page;
		total.value = response.data.total;
		searching.value = false;
	});
}

SearchService.init(props.albumid).then((response) => {
	searchMinimumLengh.value = response.data.search_minimum_length;
});

const nullish = ref(null);
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
} = useSelection(nullish, undefined, photos, albums);

const photoCallbacks = {
	star: () => {
		// PhotoService.star(selectedPhotosIds.value, true);
		// AlbumService.clearCache();
		// refresh();
	},
	unstar: () => {
		// PhotoService.star(selectedPhotosIds.value, false);
		// AlbumService.clearCache();
		// refresh();
	},
	setAsCover: () => {
		// PhotoService.setAsCover(selectedPhoto.value!.id, albumid.value);
		// AlbumService.clearCache(albumid.value);
		// refresh();
	},
	setAsHeader: () => {
		// PhotoService.setAsHeader(selectedPhoto.value!.id, albumid.value, false);
		// AlbumService.clearCache(albumid.value);
		// refresh();
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
		config: null,
		album: null,
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

loadLayout();

if (lycheeStore.isSearchActive) {
	search(lycheeStore.search_term);
}

onKeyStroke("Escape", () => {
	goBack();
});
</script>
