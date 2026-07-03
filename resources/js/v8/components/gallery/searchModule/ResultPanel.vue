<template>
	<UContextMenu :items="menuSections" :disabled="Menu.length === 0" class="contents">
		<div class="contents">
			<AlbumThumbPanel
				v-if="albumsStore.albums.length > 0"
				:header="albumHeader"
				:albums="albumsStore.albums"
				:is-alone="false"
				:selected-albums="selectedAlbumsIds"
				:is-timeline="false"
				@clicked="albumSelect"
				@contexted="contextMenuAlbumOpen"
			/>
			<div v-if="photosStore.photos.length > 0" class="flex justify-center w-full">
				<UPagination v-model:page="page" :total="searchStore.total" :items-per-page="rows ?? 20" @update:page="emits('refresh')" />
			</div>
			<PhotoThumbPanel
				v-if="photosStore.photos.length > 0"
				:header="photoHeader"
				:photos="photosStore.photos"
				:photos-timeline="undefined"
				:selected-photos="selectedPhotosIds"
				:is-timeline="false"
				:with-control="true"
				@clicked="photoClick"
				@selected="selectPhoto"
				@contexted="contextMenuPhotoOpen"
			/>
			<div v-if="photosStore.photos.length > 0" class="flex justify-center w-full">
				<UPagination v-model:page="page" :total="searchStore.total" :items-per-page="rows ?? 20" @update:page="emits('refresh')" />
			</div>
		</div>
	</UContextMenu>
</template>
<script setup lang="ts">
import { useContextMenu, type PhotoCallbacks, type AlbumCallbacks, type Selectors } from "@/composables/contextMenus/contextMenu";
import { useSelection } from "@/composables/selections/selections";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import PhotoThumbPanel from "@/v8/components/gallery/albumModule/PhotoThumbPanel.vue";
import AlbumThumbPanel from "@/v8/components/gallery/albumModule/AlbumThumbPanel.vue";
import { AlbumThumbConfig } from "@/v8/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { sprintf } from "sprintf-js";
import { trans } from "laravel-vue-i18n";
import { computed } from "vue";
import { useSearchStore } from "@/stores/SearchState";
import type { ContextMenuItem } from "@nuxt/ui";

const togglableStore = useTogglablesStateStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();
const searchStore = useSearchStore();
const router = useRouter();

const props = defineProps<{
	// config
	albumPanelConfig: AlbumThumbConfig;
	isPhotoTimelineEnabled: boolean;
	// for menu
	photoCallbacks: PhotoCallbacks;
	albumCallbacks: AlbumCallbacks;
	selectors: Selectors;
}>();

const photoHeader = computed(() => {
	return sprintf(trans("gallery.search.photos"), searchStore.total);
});

const albumHeader = computed(() => {
	if (albumsStore.albums.length === 0) {
		return "";
	}
	return sprintf(trans("gallery.search.albums"), albumsStore.albums.length);
});

const first = defineModel<number | undefined>("first");
const rows = defineModel<number | undefined>("rows");

// UPagination is 1-indexed by page number; v7's Paginator used a 0-indexed row offset (`first`).
const page = computed<number>({
	get: () => Math.floor((first.value ?? 0) / (rows.value ?? 20)) + 1,
	set: (newPage: number) => {
		first.value = (newPage - 1) * (rows.value ?? 20);
	},
});

const emits = defineEmits<{
	refresh: [];
}>();

const { photoRoute } = usePhotoRoute(router);

function photoClick(photoId: string, _e: MouseEvent) {
	router.push(photoRoute(photoId));
}

const { selectedPhotosIds, selectedAlbumsIds, photoSelect: selectPhoto, albumSelect } = useSelection(photosStore, albumsStore, togglableStore);

const { Menu } = useContextMenu(props.selectors, props.photoCallbacks, props.albumCallbacks);

// See AlbumPanel.vue for why the composable's imperative photoMenuOpen/albumMenuOpen are
// bypassed in favor of a declarative UContextMenu wrapping the gallery view.
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
