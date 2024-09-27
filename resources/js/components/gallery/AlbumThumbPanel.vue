<template>
	<Panel :header="$t(props.header)" :toggleable="!isAlone" :pt:header:class="headerClass" class="border-0 w-full">
		<div class="flex flex-wrap flex-row flex-shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pt-4">
			<template v-for="(album, idx) in props.albums">
				<AlbumThumb
					@click="maySelect(idx + props.idxShift, $event)"
					@contextmenu.prevent="menuOpen(idx + props.idxShift, $event)"
					:album="album"
					:cover_id="null"
					:config="props.config"
					v-if="!album.is_nsfw || props.areNsfwVisible"
					:is-selected="props.selectedAlbums.includes(album.id)"
				/>
			</template>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import Panel from "primevue/panel";
import AlbumThumb, { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import { computed } from "vue";

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
	(e: "clicked", idx: number, event: MouseEvent): void;
	(e: "contexted", idx: number, event: MouseEvent): void;
}>();
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

const headerClass = computed(() => {
	return props.isAlone ? "hidden" : "";
});
</script>
