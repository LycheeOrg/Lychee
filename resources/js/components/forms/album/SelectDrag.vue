<template>
	<div
		v-if="initialPosition !== undefined"
		id="selector"
		class="absolute bg-blue-500/20 border border-sky-400 rounded-sm z-50"
		:style="position"
	></div>
</template>
<script setup lang="ts">
import { useDragAndSelect } from "@/composables/album/dragAndSelect";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotosStore } from "@/stores/PhotosState";
import { onMounted, onUnmounted } from "vue";

const togglableStore = useTogglablesStateStore();
const albumsStore = useAlbumsStore();
const photosStore = usePhotosStore();

const props = defineProps<{
	withScroll: boolean;
}>();

const { initialPosition, position, show } = useDragAndSelect(togglableStore, albumsStore, photosStore, props.withScroll ?? true);

onMounted(() => {
	document.getElementById("galleryView")?.addEventListener("mousedown", show);
});
onUnmounted(() => {
	document.getElementById("galleryView")?.removeEventListener("mousedown", show);
});
</script>
