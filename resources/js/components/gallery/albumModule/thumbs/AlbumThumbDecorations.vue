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
import { AlbumThumbConfig } from "./AlbumThumb.vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";

const props = defineProps<{
	album: App.Http.Resources.Models.ThumbAlbumResource;
	config: AlbumThumbConfig;
}>();

const cssDirection = computed(() => {
	if (props.config.album_decoration_orientation === "column") {
		return "flex-col";
	}
	if (props.config.album_decoration_orientation === "column-reverse") {
		return "flex-col-reverse";
	}
	if (props.config.album_decoration_orientation === "row-reverse") {
		return "flex-row-reverse";
	}
	return "flex-row";
});

const showLayers = computed(() => {
	return props.album.has_subalbum && props.config.album_decoration === "layers";
});

const showPhotosCount = computed(() => {
	return props.album.num_photos > 0 && (props.config.album_decoration === "photo" || props.config.album_decoration === "all");
});

const showAlbumsCount = computed(() => {
	return props.album.num_subalbums > 0 && (props.config.album_decoration === "album" || props.config.album_decoration === "all");
});
</script>
