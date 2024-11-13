<template>
	<template v-for="(album, idx) in props.albums">
		<AlbumThumb
			@click="maySelect(idx + props.iter + props.idxShift, $event)"
			@contextmenu.prevent="menuOpen(idx + props.iter + props.idxShift, $event)"
			:album="album"
			:cover_id="null"
			:config="props.config"
			v-if="!album.is_nsfw || are_nsfw_visible"
			:is-selected="props.selectedAlbums.includes(album.id)"
		/>
	</template>
</template>
<script setup lang="ts">
import AlbumThumb, { AlbumThumbConfig } from "@/components/gallery/thumbs/AlbumThumb.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | undefined | null;
	albums: { [key: number]: App.Http.Resources.Models.ThumbAlbumResource };
	config: AlbumThumbConfig;
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
