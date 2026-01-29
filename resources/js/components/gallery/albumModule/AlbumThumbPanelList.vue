<template>
	<template v-for="album in props.albums" :key="`album-thumb-${album.id}`">
		<AlbumThumb
			v-if="!album.is_nsfw || are_nsfw_visible"
			:album="album"
			:cover_id="null"
			:is-selected="props.selectedAlbums.includes(album.id)"
			@click="propagateClicked($event, album.id)"
			@contextmenu.prevent="propagateContexted($event, album.id)"
		/>
	</template>
</template>
<script setup lang="ts">
import AlbumThumb from "@/components/gallery/albumModule/thumbs/AlbumThumb.vue";
import { usePropagateAlbumEvents } from "@/composables/album/propagateEvents";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

const props = defineProps<{
	albums: App.Http.Resources.Models.ThumbAlbumResource[];
	selectedAlbums: string[];
}>();

// bubble up.
const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

const { propagateClicked, propagateContexted } = usePropagateAlbumEvents(emits);
</script>
