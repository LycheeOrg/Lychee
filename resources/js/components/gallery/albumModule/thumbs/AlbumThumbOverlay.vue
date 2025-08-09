<template>
	<div
		class=""
		:class="{
			'overlay absolute mb-[1px] mx-[1px] p-0 border-0 w-[calc(100%-2px)] bottom-0 bg-gradient-to-t from-[#00000099] text-shadow-sm': true,
			'opacity-0 group-hover:opacity-100 transition-all ease-out': props.config.display_thumb_album_overlay === 'hover',
		}"
	>
		<h1
			:class="{
				'w-full pt-3 pb-1 text-sm text-surface-0 font-bold text-ellipsis whitespace-nowrap overflow-x-hidden': true,
				'pr-1 pl-2 sm:pl-3 md:pl-4': isLTR(),
				'pl-1 pr-2 sm:pr-3 md:pr-4': !isLTR(),
			}"
			:title="props.album.title"
		>
			{{ props.album.title }}
		</h1>
		<span
			:class="{
				'hidden sm:block mt-0 mb-1.5 sm:mb-3 text-2xs text-surface-300': true,
				'mr-0 ml-2 sm:ml-3 md:ml-4': isLTR(),
				'ml-0 mr-2 sm:mr-3 md:mr-4': !isLTR(),
			}"
		>
			<i v-if="props.config.album_subtitle_type === 'takedate' || props.config.album_subtitle_type === 'creation'" class="pi pi-pi-camera"></i>
			{{ subtitle }}
		</span>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { AlbumThumbConfig } from "./AlbumThumb.vue";
import { useLtRorRtL } from "@/utils/Helpers";

const { isLTR } = useLtRorRtL();

const subtitle = computed(() => {
	if (props.config.album_subtitle_type === "description") {
		return props.album.description;
	} else if (props.config.album_subtitle_type === "takedate") {
		return props.album.formatted_min_max ?? props.album.created_at;
	} else if (props.config.album_subtitle_type === "creation") {
		return props.album.created_at;
	} else if (props.config.album_subtitle_type === "oldstyle") {
		return props.album.formatted_min_max ?? props.album.created_at;
	}
	return "";
});

const props = defineProps<{
	album: App.Http.Resources.Models.ThumbAlbumResource;
	config: AlbumThumbConfig;
}>();
</script>
