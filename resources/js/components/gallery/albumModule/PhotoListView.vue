<template>
	<div class="flex flex-col w-full divide-y divide-neutral-200 dark:divide-neutral-700" role="grid">
		<PhotoListItem
			v-for="photo in props.photos"
			:key="photo.id"
			:photo="photo"
			:is-selected="props.selectedPhotos.includes(photo.id)"
			:is-cover-id="albumStore.modelAlbum?.cover_id === photo.id"
			:is-header-id="albumStore.modelAlbum?.header_id === photo.id"
			@clicked="handleClicked"
			@contexted="handleContexted"
		/>
	</div>
</template>

<script setup lang="ts">
import PhotoListItem from "./PhotoListItem.vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { ctrlKeyState, metaKeyState, shiftKeyState } from "@/utils/keybindings-utils";

const props = defineProps<{
	photos: App.Http.Resources.Models.PhotoResource[];
	selectedPhotos: string[];
}>();

const emits = defineEmits<{
	clicked: [id: string, event: MouseEvent | KeyboardEvent];
	selected: [id: string, event: MouseEvent | KeyboardEvent];
	contexted: [id: string, event: MouseEvent];
	toggleBuyMe: [id: string];
}>();

const albumStore = useAlbumStore();

function handleClicked(event: MouseEvent | KeyboardEvent, id: string): void {
	if (ctrlKeyState.value || metaKeyState.value || shiftKeyState.value) {
		emits("selected", id, event);
		return;
	}
	emits("clicked", id, event);
}

function handleContexted(event: MouseEvent, id: string): void {
	emits("contexted", id, event);
}
</script>
