<template>
	<span
		class="thumbimg absolute w-full h-full bg-neutral-800 shadow-md shadow-black/25 border-solid border border-neutral-400 ease-out transition-transform"
		:class="props.class"
	>
		<img
			:alt="$t('lychee.PHOTO_THUMBNAIL')"
			class="w-full h-full m-0 p-0 border-0 object-cover"
			:src="src"
			:srcset="srcSet"
			data-overlay="false"
			draggable="false"
			loading="lazy"
		/>
	</span>
</template>
<script setup lang="ts">
import { watch } from "vue";
import { ref } from "vue";

const props = defineProps<{
	thumb: App.Http.Resources.Models.ThumbResource | undefined | null;
	class: string;
}>();

const src = ref("");
const srcSet = ref("");

function load(thumb: App.Http.Resources.Models.ThumbResource | undefined | null) {
	if (thumb?.thumb === "uploads/thumb/") {
		src.value = window.assets_url + "img/placeholder.png";
		if (thumb.type.includes("video")) {
			src.value = window.assets_url + "img/play-icon.png";
		}
		if (thumb.type.includes("raw")) {
			src.value = window.assets_url + "img/no_images.svg";
		}
	} else {
		src.value = isNotEmpty(thumb?.thumb) ? (thumb?.thumb as string) : window.assets_url + "img/no_images.svg";
	}
	srcSet.value = isNotEmpty(thumb?.thumb2x) ? (thumb?.thumb2x as string) : "";
}

function isNotEmpty(link: string | null | undefined): boolean {
	return link !== "" && link !== null && link !== undefined;
}

load(props.thumb);

watch(
	() => props.thumb,
	(newThumb: App.Http.Resources.Models.ThumbResource | undefined | null, _oldThumb: any) => {
		load(newThumb);
	},
);
</script>
