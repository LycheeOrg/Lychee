<template>
	<div class="relative flex flex-wrap flex-row shrink w-full justify-start align-top" :id="'photoListing' + props.groupIdx">
		<template v-for="(photo, idx) in props.photos" :key="photo.id">
			<PhotoThumb
				@click="maySelect(idx + props.iter, $event)"
				@contextmenu.prevent="menuOpen(idx + props.iter, $event)"
				:photo="photo"
				:is-selected="props.selectedPhotos.includes(photo.id)"
				:is-lazy="idx + props.iter > 10"
				:is-cover-id="props.coverId === photo.id"
				:is-header-id="props.headerId === photo.id"
			/>
		</template>
	</div>
</template>
<script setup lang="ts">
import { useLayouts, type TimelineData } from "@/layouts/PhotoLayout";
import { onMounted, onUnmounted, onUpdated, Ref } from "vue";
import PhotoThumb from "./thumbs/PhotoThumb.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { ctrlKeyState, metaKeyState, shiftKeyState } from "@/utils/keybindings-utils";
import { useDebounceFn } from "@vueuse/core";
import { useRoute } from "vue-router";

const props = defineProps<{
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
	galleryConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
	selectedPhotos: string[];
	iter: number;
	groupIdx: number;
	coverId: string | undefined;
	headerId: string | undefined;
}>();

const lycheeStore = useLycheeStateStore();
const layout = defineModel("layout") as Ref<App.Enum.PhotoLayoutType>;
const isTimeline = defineModel("isTimeline") as Ref<boolean>;
const { is_timeline_left_border_visible } = storeToRefs(lycheeStore);

const route = useRoute();

const timelineData: TimelineData = {
	isTimeline: isTimeline,
	isLeftBorderVisible: is_timeline_left_border_visible,
};

const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	selected: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
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
const { activateLayout } = useLayouts(props.galleryConfig, layout, timelineData, "photoListing" + props.groupIdx, route);

const debouncedActivateLayout = useDebounceFn(activateLayout, 100);

onMounted(() => {
	activateLayout();
	window.addEventListener("resize", debouncedActivateLayout);
});

onUpdated(() => activateLayout());

onUnmounted(() => {
	window.removeEventListener("resize", debouncedActivateLayout);
});
</script>
