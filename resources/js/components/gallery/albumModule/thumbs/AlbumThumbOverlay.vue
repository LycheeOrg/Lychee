<template>
	<div
		class=""
		:class="{
			'overlay absolute mb-[1px] mx-[1px] p-0 border-0 w-[calc(100%-2px)] bottom-0 bg-gradient-to-t from-[#00000099] text-shadow-sm': true,
			'opacity-0 group-hover:opacity-100 transition-all ease-out': display_thumb_album_overlay === 'hover',
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
			<i v-if="album_subtitle_type === 'takedate' || album_subtitle_type === 'creation'" class="pi pi-pi-camera"></i>
			{{ subtitle }}
		</span>
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const { isLTR } = useLtRorRtL();

const lycheeStore = useLycheeStateStore();
const { album_subtitle_type, display_thumb_album_overlay } = storeToRefs(lycheeStore);

const subtitle = computed(() => {
	switch (album_subtitle_type.value) {
		case "description":
			return props.album.description;
		case "takedate":
			return props.album.formatted_min_max ?? props.album.created_at;
		case "creation":
			return props.album.created_at;
		case "oldstyle":
			return props.album.formatted_min_max ?? props.album.created_at;
		case "num_photos":
			return `${props.album.num_photos} ${trans("gallery.album.hero.images")}`;
		case "num_albums":
			return `${props.album.num_subalbums} ${trans("gallery.album.hero.subalbums")}`;
		case "num_photos_albums":
			const photos = `${props.album.num_photos} ${trans("gallery.album.hero.images")}`;
			const albums = `${props.album.num_subalbums} ${trans("gallery.album.hero.subalbums")}`;
			if (props.album.num_photos > 0 && props.album.num_subalbums > 0) {
				return `${photos}, ${albums}`;
			} else if (props.album.num_photos > 0) {
				return photos;
			} else if (props.album.num_subalbums > 0) {
				return albums;
			} else {
				return "";
			}
		default:
			return "";
	}
});

const props = defineProps<{
	album: App.Http.Resources.Models.ThumbAlbumResource;
}>();
</script>
