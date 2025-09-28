<template>
	<div class="h-svh overflow-y-hidden">
		<!-- Trick to avoid the scroll bar to appear on the right when switching to full screen -->
		<Collapse :when="!is_full_screen">
			<Toolbar class="w-full border-0 h-14">
				<template #start>
					<GoBack @go-back="goBack" />
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
			@scroll="onScroll"
		>
			<PhotoThumbPanel
				v-if="layoutStore.config && photos.length > 0"
				header="gallery.album.header_photos"
				:photos="photos"
				:photos-timeline="undefined"
				:selected-photos="selectedPhotosIds"
				:is-timeline="false"
				:with-control="false"
				@clicked="photoClick"
			/>
		</div>
	</div>
</template>
<script setup lang="ts">
import PhotoThumbPanel from "@/components/gallery/albumModule/PhotoThumbPanel.vue";
import GoBack from "@/components/headers/GoBack.vue";
import { useScrollable } from "@/composables/album/scrollable";
import { useSelection } from "@/composables/selections/selections";
import { ALL } from "@/config/constants";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotosStore } from "@/stores/PhotosState";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { onKeyStroke } from "@vueuse/core";
import { storeToRefs } from "pinia";
import Toolbar from "primevue/toolbar";
import { ref, computed, onMounted } from "vue";
import { Collapse } from "vue-collapsed";
import { useRouter } from "vue-router";

const albumId = ref("favourites");
const togglableStore = useTogglablesStateStore();
const layoutStore = useLayoutStore();
const photosStore = usePhotosStore();
const albumsStore = useAlbumsStore();
const { is_full_screen } = storeToRefs(togglableStore);
const { onScroll, setScroll } = useScrollable(togglableStore, albumId);
const router = useRouter();

function goBack() {
	router.push({ name: "gallery" });
}

const favourites = useFavouriteStore();
layoutStore.layout = "square";

const photos = computed(() => favourites.photos ?? []);

const { selectedPhotosIds } = useSelection(photosStore, albumsStore, togglableStore);

function photoClick(idx: number, _e: MouseEvent) {
	router.push({ name: "album", params: { albumId: photos.value[idx].album_id ?? ALL, photoId: photos.value[idx].id } });
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
	const results = await Promise.allSettled([layoutStore.load()]);

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
