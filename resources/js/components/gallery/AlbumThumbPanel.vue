<template>
	<Panel v-if="props.isTimeline === false" :header="$t(props.header)" :toggleable="!isAlone" :pt:header:class="headerClass" class="border-0 w-full">
		<div class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pt-4">
			<AlbumThumbPanelList
				:albums="props.albums"
				:album="props.album"
				:config="props.config"
				:idx-shift="props.idxShift"
				:iter="0"
				:selected-albums="props.selectedAlbums"
				@clicked="propagateClicked"
				@contexted="propagateMenuOpen"
			/>
		</div>
	</Panel>
	<Panel v-else :header="'Timeline'" :toggleable="!isAlone" :pt:header:class="headerClass" class="border-0 w-full">
		<Timeline v-if="is_timeline_left_border_visible" :value="albumsTimeLine" :pt:eventopposite:class="'hidden'" class="mt-4">
			<template #content="slotProps">
				<div class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8">
					<div class="w-full text-left font-semibold text-muted-color-emphasis text-lg">{{ slotProps.item.header }}</div>
					<AlbumThumbPanelList
						:albums="slotProps.item.data"
						:album="props.album"
						:config="props.config"
						:idx-shift="props.idxShift"
						:iter="slotProps.item.iter"
						:selected-albums="props.selectedAlbums"
						@clicked="propagateClicked"
						@contexted="propagateMenuOpen"
					/>
				</div>
			</template>
		</Timeline>
		<div v-else>
			<template v-for="albumTimeline in albumsTimeLine">
				<div
					v-if="albumTimeline.data.filter((a) => !a.is_nsfw || are_nsfw_visible).length > 0"
					class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8"
				>
					<div class="w-full text-left font-semibold text-muted-color-emphasis text-lg">{{ albumTimeline.header }}</div>
					<AlbumThumbPanelList
						:albums="albumTimeline.data"
						:album="props.album"
						:config="props.config"
						:idx-shift="props.idxShift"
						:iter="albumTimeline.iter"
						:selected-albums="props.selectedAlbums"
						@clicked="propagateClicked"
						@contexted="propagateMenuOpen"
					/>
				</div>
			</template>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import Panel from "primevue/panel";
import { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import { computed } from "vue";
import { SplitData, useSplitter } from "@/composables/album/splitter";
import Timeline from "primevue/timeline";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import AlbumThumbPanelList from "./AlbumThumbPanelList.vue";

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible, is_timeline_left_border_visible } = storeToRefs(lycheeStore);

const props = defineProps<{
	header: string;
	album: App.Http.Resources.Models.AlbumResource | undefined | null;
	albums: { [key: number]: App.Http.Resources.Models.ThumbAlbumResource };
	config: AlbumThumbConfig;
	isAlone: boolean;
	idxShift: number;
	selectedAlbums: string[];
	isTimeline: boolean;
}>();

// bubble up.
const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
}>();

const { spliter } = useSplitter();

const propagateClicked = (idx: number, e: MouseEvent) => {
	emits("clicked", idx, e);
};

const propagateMenuOpen = (idx: number, e: MouseEvent) => {
	emits("contexted", idx, e);
};

const albumsTimeLine = computed<SplitData<App.Http.Resources.Models.ThumbAlbumResource>[]>(() =>
	spliter(
		props.albums as App.Http.Resources.Models.ThumbAlbumResource[],
		(a: App.Http.Resources.Models.ThumbAlbumResource) => a.timeline?.timeDate ?? "",
		(a: App.Http.Resources.Models.ThumbAlbumResource) => a.timeline?.format ?? "Others",
	),
);

const headerClass = computed(() => {
	return props.isAlone ? "hidden" : "";
});
</script>
