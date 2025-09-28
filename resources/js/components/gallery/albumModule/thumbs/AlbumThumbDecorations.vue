<template>
	<div class="absolute right-2 top-1.5 flex flex-row gap-1 justify-end text-right font-bold font-sans" :class="cssDirection">
		<div v-if="showLayers">
			<MiniIcon icon="layers" class="fill-white w-3 h-3" />
		</div>
		<div v-if="showPhotosCount">
			<i class="pi pi-images text-shadow-sm text-xs" />
			<span class="ml-1 text-xs text-shadow-sm">{{ props.album.num_photos }}</span>
		</div>
		<div v-if="showAlbumsCount">
			<i class="pi pi-folder text-shadow-sm text-xs" />
			<span class="ml-1 text-xs text-shadow-sm">{{ props.album.num_subalbums }}</span>
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import { storeToRefs } from "pinia";
import { useLycheeStateStore } from "@/stores/LycheeState";

const props = defineProps<{
	album: App.Http.Resources.Models.ThumbAlbumResource;
}>();

const lycheeStore = useLycheeStateStore();
const { album_decoration_orientation, album_decoration } = storeToRefs(lycheeStore);
const cssDirection = computed(() => {
	if (album_decoration_orientation.value === "column") {
		return "flex-col";
	}
	if (album_decoration_orientation.value === "column-reverse") {
		return "flex-col-reverse";
	}
	if (album_decoration_orientation.value === "row-reverse") {
		return "flex-row-reverse";
	}
	return "flex-row";
});

const showLayers = computed(() => {
	return props.album.has_subalbum && album_decoration.value === "layers";
});

const showPhotosCount = computed(() => {
	return props.album.num_photos > 0 && (album_decoration.value === "photo" || album_decoration.value === "all");
});

const showAlbumsCount = computed(() => {
	return props.album.num_subalbums > 0 && (album_decoration.value === "album" || album_decoration.value === "all");
});
</script>
