<template>
	<div class="relative flex flex-wrap flex-row shrink w-full justify-start align-top" :id="'photoListing' + props.iter">
		<template v-for="(photo, idx) in props.photos">
			<PhotoThumb
				@click="maySelect(idx + iter, $event)"
				@contextmenu.prevent="menuOpen(idx + iter, $event)"
				:is-selected="props.selectedPhotos.includes(photo.id)"
				:photo="photo"
				:album="props.album"
				:is-lazy="idx + iter > 10"
			/>
		</template>
	</div>
</template>
<script setup lang="ts">
import { useLayouts, type TimelineData } from "@/layouts/PhotoLayout";
import { onMounted, onUpdated, Ref } from "vue";
import PhotoThumb from "./thumbs/PhotoThumb.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const props = defineProps<{
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
	album:
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource
		| undefined;
	galleryConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
	selectedPhotos: string[];
	iter: number;
}>();

const lycheeStore = useLycheeStateStore();
const layout = defineModel("layout") as Ref<App.Enum.PhotoLayoutType>;
const isTimeline = defineModel("isTimeline") as Ref<boolean>;
const { is_timeline_left_border_visible } = storeToRefs(lycheeStore);

const timelineData: TimelineData = {
	isTimeline: isTimeline,
	isLeftBorderVisible: is_timeline_left_border_visible,
};

const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
}>();
const maySelect = (idx: number, e: MouseEvent) => emits("clicked", idx, e);
const menuOpen = (idx: number, e: MouseEvent) => emits("contexted", idx, e);

// Layouts stuff
const { activateLayout } = useLayouts(props.galleryConfig, layout, timelineData, "photoListing" + props.iter);
onMounted(() => activateLayout());
onUpdated(() => activateLayout());
</script>
