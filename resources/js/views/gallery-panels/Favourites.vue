<template>
	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<Toolbar class="w-full border-0 h-14">
				<template #start>
					<Button icon="pi pi-angle-left" class="mr-2 border-none" severity="secondary" text @click="goBack" />
				</template>

				<template #center>
					{{ $t("gallery.favourites") }}
				</template>

				<template #end> </template>
			</Toolbar>
		</Collapse>
		<div
			id="galleryView"
			class="relative flex flex-wrap content-start w-full justify-start overflow-y-auto"
			:class="is_full_screen ? 'h-svh' : 'h-[calc(100vh-3.5rem)]'"
			v-on:scroll="onScroll"
		>
			<PhotoThumbPanel
				v-if="layoutConfig !== undefined && photos.length > 0"
				header="gallery.album.header_photos"
				:photos="photos"
				:album="undefined"
				:gallery-config="layoutConfig"
				:photo-layout="'square'"
				:selected-photos="selectedPhotosIds"
				:is-timeline="false"
				:with-control="false"
				@clicked="photoClick"
			/>
		</div>
	</div>
	<!-- @clicked="photoClick"
    @contexted="photoMenuOpen" -->
</template>
<script setup lang="ts">
import PhotoThumbPanel from "@/components/gallery/albumModule/PhotoThumbPanel.vue";
import { useScrollable } from "@/composables/album/scrollable";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import { useSelection } from "@/composables/selections/selections";
import { useGetLayoutConfig } from "@/layouts/PhotoLayout";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { onKeyStroke } from "@vueuse/core";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import { ref, computed, onMounted } from "vue";
import { Collapse } from "vue-collapsed";
import { useRouter } from "vue-router";

const albumId = ref("favourites");
const togglableStore = useTogglablesStateStore();
const { layoutConfig, loadLayoutConfig } = useGetLayoutConfig();
const { is_full_screen, is_login_open, is_slideshow_active, is_upload_visible, list_upload_files } = storeToRefs(togglableStore);
const { onScroll, setScroll, scrollToTop } = useScrollable(togglableStore, albumId);
const router = useRouter();

function goBack() {
	router.push({ name: "gallery" });
}

const favourites = useFavouriteStore();

const photos = computed(() => favourites.photos ?? []);
const children = ref([]);

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
	selectEverything,
	unselect,
	hasSelection,
} = useSelection(photos, children, togglableStore);

const { photoRoute } = usePhotoRoute(router);

function photoClick(idx: number, e: MouseEvent) {
	router.push(photoRoute(photos.value[idx].album_id ?? undefined, photos.value[idx].id));
}

onKeyStroke("f", () => !shouldIgnoreKeystroke() && togglableStore.toggleFullScreen());
onKeyStroke("Escape", () => {
	// 1. lose focus
	if (shouldIgnoreKeystroke() && document.activeElement instanceof HTMLElement) {
		document.activeElement.blur();
		return;
	}

	goBack();
});

onMounted(async () => {
	const results = await Promise.allSettled([loadLayoutConfig()]);

	results.forEach((result, index) => {
		if (result.status === "rejected") {
			console.warn(`Promise ${index} reject with reason: ${result.reason}`);
		}
	});

	if (results.every((result) => result.status === "fulfilled")) {
		setScroll();
	}
});
</script>
