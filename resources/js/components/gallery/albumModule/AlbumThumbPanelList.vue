<template>
	<template v-for="album in props.albums" :key="`album-thumb-${album.id}`">
		<AlbumThumb
			v-if="!album.is_nsfw || are_nsfw_visible"
			:album="album"
			:cover_id="null"
			:is-selected="props.selectedAlbums.includes(album.id)"
			@click="(e: MouseEvent) => maySelect(album.id, e)"
			@contextmenu.prevent="(e: MouseEvent) => menuOpen(album.id, e)"
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
	isInteractive?: boolean;
	selectedAlbums: string[];
}>();

// bubble up.
const emits = defineEmits<{
	clicked: [id: string, event: MouseEvent];
	contexted: [id: string, event: MouseEvent];
}>();

const maySelect = (id: string, e: MouseEvent) => {
	if (props.isInteractive === false) {
		return;
	}
	emits("clicked", id, e);
};

const menuOpen = (id: string, e: MouseEvent) => {
	if (props.isInteractive === false) {
		return;
	}
	emits("contexted", id, e);
};
</script>
