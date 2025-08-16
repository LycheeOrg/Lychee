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
				v-if="layoutConfig !== undefined && photos.length > 0"
				header="gallery.album.header_photos"
				:photos="photos"
				:gallery-config="layoutConfig"
				:photos-timeline="undefined"
				:photo-layout="'square'"
				:selected-photos="selectedPhotosIds"
				:is-timeline="false"
				:with-control="false"
				:cover-id="undefined"
				:header-id="undefined"
				@clicked="photoClick"
			/>
		</div>
	</div>
	<!-- @clicked="photoClick"
    @contexted="photoMenuOpen" -->
</template>
<script setup lang="ts">
import PhotoThumbPanel from "@/components/gallery/albumModule/PhotoThumbPanel.vue";
import GoBack from "@/components/headers/GoBack.vue";
import { useScrollable } from "@/composables/album/scrollable";
import { useSelection } from "@/composables/selections/selections";
import { ALL } from "@/config/constants";
import { useGetLayoutConfig } from "@/layouts/PhotoLayout";
import { useFavouriteStore } from "@/stores/FavouriteState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { onKeyStroke } from "@vueuse/core";
import { storeToRefs } from "pinia";
import Toolbar from "primevue/toolbar";
import { ref, computed, onMounted } from "vue";
import { Collapse } from "vue-collapsed";
import { useRouter } from "vue-router";

const albumId = ref("favourites");
const togglableStore = useTogglablesStateStore();
const { layoutConfig, loadLayoutConfig } = useGetLayoutConfig();
const { is_full_screen } = storeToRefs(togglableStore);
const { onScroll, setScroll } = useScrollable(togglableStore, albumId);
const router = useRouter();

function goBack() {
	router.push({ name: "gallery" });
}

const favourites = useFavouriteStore();

const photos = computed(() => favourites.photos ?? []);

const { selectedPhotosIds } = useSelection({ photos }, togglableStore);

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
