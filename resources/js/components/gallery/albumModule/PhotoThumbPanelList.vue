<template>
	<div :id="'photoListing' + props.groupIdx" class="relative flex flex-wrap flex-row shrink w-full justify-start align-top">
		<!-- List view -->
		<PhotoListView
			v-if="layoutStore.layout === 'list'"
			:photos="props.photos"
			:selected-photos="props.selectedPhotos"
			@clicked="(id: string, e: MouseEvent | KeyboardEvent) => emits('clicked', id, e as MouseEvent)"
			@selected="(id: string, e: MouseEvent | KeyboardEvent) => emits('selected', id, e as MouseEvent)"
			@contexted="(id: string, e: MouseEvent) => emits('contexted', id, e)"
			@toggle-buy-me="(id: string) => emits('toggleBuyMe', id)"
		/>
		<!-- Thumbnail views -->
		<template v-else v-for="photo in props.photos" :key="photo.id">
			<PhotoThumb
				:photo="photo"
				:is-selected="props.selectedPhotos.includes(photo.id)"
				:is-cover-id="albumStore.modelAlbum?.cover_id === photo.id"
				:is-header-id="albumStore.modelAlbum?.header_id === photo.id"
				@click="(e: MouseEvent) => maySelect(photo.id, e)"
				@contextmenu.prevent="(e: MouseEvent) => menuOpen(photo.id, e)"
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
import PhotoListView from "./PhotoListView.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { ctrlKeyState, metaKeyState, shiftKeyState } from "@/utils/keybindings-utils";
import { useDebounceFn } from "@vueuse/core";
import { useRoute } from "vue-router";
import { useLayoutStore } from "@/stores/LayoutState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useCatalogStore } from "@/stores/CatalogState";

const props = defineProps<{
	photos: App.Http.Resources.Models.PhotoResource[];
	selectedPhotos: string[];
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
	clicked: [id: string, event: MouseEvent];
	selected: [id: string, event: MouseEvent];
	contexted: [id: string, event: MouseEvent];
	toggleBuyMe: [id: string];
}>();

function maySelect(id: string, e: MouseEvent) {
	if (ctrlKeyState.value || metaKeyState.value || shiftKeyState.value) {
		emits("selected", id, e);
		return;
	}
	emits("clicked", id, e);
}
function menuOpen(id: string, e: MouseEvent) {
	emits("contexted", id, e);
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
