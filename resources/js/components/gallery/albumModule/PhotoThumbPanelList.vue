<template>
	<div :id="'photoListing' + props.groupIdx" class="relative flex flex-wrap flex-row shrink w-full justify-start align-top">
		<template v-for="(photo, idx) in props.photos" :key="photo.id">
			<PhotoThumb
				:photo="photo"
				:is-selected="props.selectedPhotos.includes(photo.id)"
				:is-lazy="idx + props.iter > 10"
				:is-cover-id="albumStore.modelAlbum?.cover_id === photo.id"
				:is-header-id="albumStore.modelAlbum?.header_id === photo.id"
				@click="maySelect(idx + props.iter, $event)"
				@contextmenu.prevent="menuOpen(idx + props.iter, $event)"
				:is-buyable="isBuyable"
				@toggleBuyMe="emits('toggleBuyMe', photo.id)"
			/>
		</template>
	</div>
</template>
<script setup lang="ts">
import { useLayouts, type TimelineData } from "@/layouts/PhotoLayout";
import { computed, onMounted, onUnmounted, onUpdated, watch } from "vue";
import PhotoThumb from "./thumbs/PhotoThumb.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { ctrlKeyState, metaKeyState, shiftKeyState } from "@/utils/keybindings-utils";
import { useDebounceFn } from "@vueuse/core";
import { useRoute } from "vue-router";
import { useLayoutStore } from "@/stores/LayoutState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useCatalogStore } from "@/stores/CatalogState";

const props = defineProps<{
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
	selectedPhotos: string[];
	iter: number;
	groupIdx: number;
	isTimeline?: boolean;
}>();

const lycheeStore = useLycheeStateStore();
const layoutStore = useLayoutStore();
const albumStore = useAlbumStore();
const catalogStore = useCatalogStore();

const isBuyable = computed(() => catalogStore.catalog?.album_purchasable !== undefined && catalogStore.catalog.album_purchasable !== null);
const { is_timeline_left_border_visible } = storeToRefs(lycheeStore);

const route = useRoute();

const timelineData: TimelineData = {
	isTimeline: props.isTimeline === true,
	isLeftBorderVisible: is_timeline_left_border_visible,
};

const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	selected: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
	toggleBuyMe: [idx: string];
}>();

function maySelect(idx: number, e: MouseEvent) {
	if (ctrlKeyState.value || metaKeyState.value || shiftKeyState.value) {
		emits("selected", idx, e);
		return;
	}
	emits("clicked", idx, e);
}
function menuOpen(idx: number, e: MouseEvent) {
	emits("contexted", idx, e);
}

// Layouts stuff
const { activateLayout } = useLayouts(layoutStore, timelineData, "photoListing" + props.groupIdx, route);

const debouncedActivateLayout = useDebounceFn(activateLayout, 100);

onMounted(() => {
	activateLayout();
	window.addEventListener("resize", debouncedActivateLayout);
});

watch(
	() => layoutStore.layout,
	() => {
		activateLayout();
	},
);

onUpdated(() => activateLayout());

onUnmounted(() => {
	window.removeEventListener("resize", debouncedActivateLayout);
});
</script>
