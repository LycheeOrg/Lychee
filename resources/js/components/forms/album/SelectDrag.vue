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
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { onMounted, onUnmounted } from "vue";

const togglableStore = useTogglablesStateStore();

const props = defineProps<{
	photos?: App.Http.Resources.Models.PhotoResource[];
	albums?: App.Http.Resources.Models.ThumbAlbumResource[];
	withScroll: boolean;
}>();

const { initialPosition, position, show } = useDragAndSelect(togglableStore, props.photos, props.albums, props.withScroll ?? true);

onMounted(() => {
	document.getElementById("galleryView")?.addEventListener("mousedown", show);
});
onUnmounted(() => {
	document.getElementById("galleryView")?.removeEventListener("mousedown", show);
});
</script>
