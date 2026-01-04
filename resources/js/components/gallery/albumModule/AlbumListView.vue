<template>
	<div class="flex flex-col gap-0 w-full">
		<template v-for="album in props.albums" :key="`album-thumb-list-${album.id}`">
			<AlbumListItem
				v-if="!album.is_nsfw || are_nsfw_visible"
				:album="album"
				:is-selected="selectedIds.includes(album.id)"
				@clicked="(event, album) => $emit('album-clicked', event, album)"
				@contexted="(event, album) => $emit('album-contexted', event, album)"
			/>
		</template>
	</div>
</template>

<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumListItem from "./AlbumListItem.vue";
import { storeToRefs } from "pinia";

const props = defineProps<{
	albums: App.Http.Resources.Models.ThumbAlbumResource[];
	selectedIds: string[];
}>();

defineEmits<{
	"album-clicked": [event: MouseEvent, album: App.Http.Resources.Models.ThumbAlbumResource];
	"album-contexted": [event: MouseEvent, album: App.Http.Resources.Models.ThumbAlbumResource];
}>();

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);
</script>
