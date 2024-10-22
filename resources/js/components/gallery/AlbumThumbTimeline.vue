<template>
	<Panel :header="'Timeline'" :toggleable="!isAlone" :pt:header:class="headerClass" class="border-0 w-full">
		<Timeline :value="albumsTimeLine" :pt:eventopposite:class="'hidden'" class="mt-4">
			<template #content="slotProps">
				<div class="flex flex-wrap flex-row flex-shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8">
					<div class="w-full text-left font-semibold text-muted-color-emphasis text-lg">{{ slotProps.item.header }}</div>
					<template v-for="(album, idx) in slotProps.item.data">
						<AlbumThumb
							@click="maySelect(idx + slotProps.item.iter + props.idxShift, $event)"
							@contextmenu.prevent="menuOpen(idx + slotProps.item.iter + props.idxShift, $event)"
							:album="album"
							:cover_id="null"
							:config="props.config"
							v-if="!album.is_nsfw || props.areNsfwVisible"
							:is-selected="props.selectedAlbums.includes(album.id)"
						/>
					</template>
				</div>
			</template>
		</Timeline>
	</Panel>
</template>
<script setup lang="ts">
import Panel from "primevue/panel";
import AlbumThumb, { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import { computed } from "vue";
import { SplitData, useSplitter } from "@/composables/album/splitter";
import Timeline from "primevue/timeline";

const props = defineProps<{
	areNsfwVisible: boolean;
	header: string;
	album: App.Http.Resources.Models.AlbumResource | undefined | null;
	albums: { [key: number]: App.Http.Resources.Models.ThumbAlbumResource };
	config: AlbumThumbConfig;
	isAlone: boolean;
	idxShift: number;
	selectedAlbums: string[];
}>();

// bubble up.
const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
}>();

const { spliter } = useSplitter();

const maySelect = (idx: number, e: MouseEvent) => {
	if (props.idxShift < 0) {
		return;
	}
	emits("clicked", idx, e);
};

const menuOpen = (idx: number, e: MouseEvent) => {
	if (props.idxShift < 0) {
		return;
	}
	emits("contexted", idx, e);
};

const albumsTimeLine = computed<SplitData<App.Http.Resources.Models.ThumbAlbumResource>[]>(() =>
	spliter(
		props.albums as App.Http.Resources.Models.ThumbAlbumResource[],
		(a: App.Http.Resources.Models.ThumbAlbumResource) => a.timeline?.timeDate ?? "",
		(a: App.Http.Resources.Models.ThumbAlbumResource) => a.timeline?.format ?? "",
	),
);

const headerClass = computed(() => {
	return props.isAlone ? "hidden" : "";
});
</script>
