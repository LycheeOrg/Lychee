<template>
	<div class="flex flex-col gap-0 w-full">
		<template v-for="album in props.albums" :key="`album-thumb-list-${album.id}`">
			<AlbumListItem
				v-if="!album.is_nsfw || are_nsfw_visible"
				:album="album"
				:is-selected="selectedIds.includes(album.id)"
				@clicked="propagateClicked"
				@contexted="propagateContexted"
			/>
		</template>
	</div>
</template>

<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumListItem from "./AlbumListItem.vue";
import { storeToRefs } from "pinia";
import { usePropagateAlbumEvents } from "@/composables/album/propagateEvents";

const props = defineProps<{
	albums: App.Http.Resources.Models.ThumbAlbumResource[];
	selectedIds: string[];
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

const { propagateClicked, propagateContexted } = usePropagateAlbumEvents(emits);

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);
</script>
