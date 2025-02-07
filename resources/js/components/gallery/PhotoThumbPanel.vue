<template>
	<Panel id="lychee_view_content" :header="$t(props.header)" class="w-full border-0">
		<template #icons>
			<PhotoThumbPanelControl v-model:layout="layout" />
		</template>
		<PhotoThumbPanelList
			v-if="isTimeline === false"
			:photos="props.photos"
			:layout="layout"
			:album="props.album"
			:galleryConfig="props.galleryConfig"
			:selectedPhotos="props.selectedPhotos"
			:iter="0"
			@clicked="propagateClicked"
			@contexted="propagateMenuOpen"
			:isTimeline="isTimeline"
		/>
		<template v-else>
			<Timeline v-if="is_timeline_left_border_visible" :value="photosTimeLine" :pt:eventopposite:class="'hidden'" class="mt-4">
				<template #content="slotProps">
					<div class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8">
						<div class="w-full text-left font-semibold text-muted-color-emphasis text-lg">{{ slotProps.item.header }}</div>
						<PhotoThumbPanelList
							:photos="slotProps.item.data"
							:layout="layout"
							:album="props.album"
							:galleryConfig="props.galleryConfig"
							:selectedPhotos="props.selectedPhotos"
							:iter="slotProps.item.iter"
							:isTimeline="isTimeline"
							@clicked="propagateClicked"
							@contexted="propagateMenuOpen"
						/>
					</div>
				</template>
			</Timeline>
			<div v-else>
				<template v-for="photoTimeline in photosTimeLine">
					<div class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8">
						<div class="w-full text-left font-semibold text-muted-color-emphasis text-lg">{{ photoTimeline.header }}</div>
						<PhotoThumbPanelList
							:photos="photoTimeline.data"
							:layout="layout"
							:album="props.album"
							:galleryConfig="props.galleryConfig"
							:selectedPhotos="props.selectedPhotos"
							:iter="photoTimeline.iter"
							:isTimeline="isTimeline"
							@clicked="propagateClicked"
							@contexted="propagateMenuOpen"
						/>
					</div>
				</template>
			</div>
		</template>
	</Panel>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import Panel from "primevue/panel";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { SplitData, useSplitter } from "@/composables/album/splitter";
import Timeline from "primevue/timeline";
import PhotoThumbPanelList from "./PhotoThumbPanelList.vue";
import PhotoThumbPanelControl from "./PhotoThumbPanelControl.vue";

const lycheeStore = useLycheeStateStore();
const { is_timeline_left_border_visible } = storeToRefs(lycheeStore);

const props = defineProps<{
	header: string;
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
	photoLayout: App.Enum.PhotoLayoutType;
	album:
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource
		| undefined;
	galleryConfig: App.Http.Resources.GalleryConfigs.PhotoLayoutConfig;
	selectedPhotos: string[];
	isTimeline: boolean;
}>();

const layout = ref(props.photoLayout);
const isTimeline = ref(props.isTimeline);

// bubble up.
const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
}>();

const propagateClicked = (idx: number, e: MouseEvent) => {
	emits("clicked", idx, e);
};

const propagateMenuOpen = (idx: number, e: MouseEvent) => {
	emits("contexted", idx, e);
};

const { spliter } = useSplitter();

const photosTimeLine = computed<SplitData<App.Http.Resources.Models.PhotoResource>[]>(() =>
	spliter(
		props.photos as App.Http.Resources.Models.PhotoResource[],
		(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.timeDate ?? "",
		(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
	),
);
</script>
