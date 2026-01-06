<template>
	<template v-for="(album, idx) in props.albums" :key="`album-thumb-${idx + props.iter + props.idxShift}`">
		<AlbumThumb
			v-if="!album.is_nsfw || are_nsfw_visible"
			:album="album"
			:cover_id="null"
			:is-selected="props.selectedAlbums.includes(album.id)"
			@click="maySelect(idx + props.iter + props.idxShift, $event)"
			@contextmenu.prevent="menuOpen(idx + props.iter + props.idxShift, $event)"
		/>
	</template>
</template>
<script setup lang="ts">
import AlbumThumb from "@/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

const props = defineProps<{
	albums: App.Http.Resources.Models.ThumbAlbumResource[];
	iter: number;
	idxShift: number;
	selectedAlbums: string[];
}>();

// bubble up.
const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
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
</script>
