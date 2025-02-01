<template>
	<div
		class=""
		:class="{
			'overlay absolute mb-[1px] mx-[1px] p-0 border-0 w-[calc(100%-2px)] bottom-0 bg-gradient-to-t from-[#00000099] text-shadow-sm': true,
			'opacity-0 group-hover:opacity-100 transition-all ease-out': props.config.display_thumb_album_overlay === 'hover',
		}"
	>
		<h1
			class="w-full pt-3 pb-1 pr-1 pl-2 sm:pl-3 md:pl-4 text-sm text-surface-0 font-bold text-ellipsis whitespace-nowrap overflow-x-hidden"
			:title="props.album.title"
		>
			{{ props.album.title }}
		</h1>
		<span
			class="hidden sm:block mt-0 mr-0 mb-1.5 sm:mb-3 ml-2 sm:ml-3 md:ml-4 text-2xs text-surface-300"
			v-if="props.config.album_subtitle_type === 'description'"
		>
			{{ props.album.description }}
		</span>
		<span
			class="hidden sm:block mt-0 mr-0 mb-1.5 sm:mb-3 ml-2 sm:ml-3 md:ml-4 text-2xs text-surface-300"
			v-if="props.config.album_subtitle_type === 'takedate'"
		>
			<i class="pi pi-pi-camera"></i>{{ props.album.formatted_min_max ?? props.album.created_at }}
		</span>
		<span
			class="hidden sm:block mt-0 mr-0 mb-1.5 sm:mb-3 ml-2 sm:ml-3 md:ml-4 text-2xs text-surface-300"
			v-if="props.config.album_subtitle_type === 'creation'"
		>
			<i class="pi pi-pi-camera"></i>{{ props.album.created_at }}
		</span>
		<span
			class="hidden sm:block mt-0 mr-0 mb-1.5 sm:mb-3 ml-2 sm:ml-3 md:ml-4 text-2xs text-surface-300"
			v-if="props.config.album_subtitle_type === 'oldstyle'"
		>
			{{ props.album.formatted_min_max ?? props.album.created_at }}
		</span>
	</div>
</template>
<script setup lang="ts">
import { AlbumThumbConfig } from "./AlbumThumb.vue";

const props = defineProps<{
	album: App.Http.Resources.Models.ThumbAlbumResource;
	config: AlbumThumbConfig;
}>();
</script>
