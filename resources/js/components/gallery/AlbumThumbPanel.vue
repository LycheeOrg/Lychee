<template>
	<Panel :header="$t(props.header)" :toggleable="!isAlone" :pt:header:class="headerClass" class="border-0 w-full">
		<div class="flex flex-wrap flex-row flex-shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pt-4">
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
</template>
<script setup lang="ts">
import Panel from "primevue/panel";
import { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import { computed } from "vue";
import AlbumThumbPanelList from "./AlbumThumbPanelList.vue";

const props = defineProps<{
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

const propagateClicked = (idx: number, e: MouseEvent) => {
	emits("clicked", idx, e);
};

const propagateMenuOpen = (idx: number, e: MouseEvent) => {
	emits("contexted", idx, e);
};

const headerClass = computed(() => {
	return props.isAlone ? "hidden" : "";
});
</script>
