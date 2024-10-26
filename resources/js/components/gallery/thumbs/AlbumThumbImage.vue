<template>
	<span
		class="thumbimg absolute w-full h-full bg-neutral-800 shadow-md shadow-black/25 border-solid border border-neutral-400 ease-out transition-transform"
		:class="props.class"
	>
		<img
			:alt="$t('lychee.PHOTO_THUMBNAIL')"
			class="w-full h-full m-0 p-0 border-0 object-cover"
			:class="classList"
			:src="src"
			:srcset="srcSet"
			data-overlay="false"
			draggable="false"
			loading="lazy"
		/>
	</span>
</template>
<script setup lang="ts">
import Constants from "@/services/constants";
import { watch, ref, computed } from "vue";

const props = defineProps<{
	thumb: App.Http.Resources.Models.ThumbResource | undefined | null;
	class: string;
	isPasswordProtected: boolean;
}>();

const src = ref("");
const srcSet = ref("");
const assets_url = ref(window.assets_url ?? "");
const classList = computed(() => {
	if (src.value === Constants.BASE_URL + "/img/no_images.svg" || src.value === Constants.BASE_URL + "/img/password.svg") {
		return "invert brightness-25 dark:invert-0 dark:brightness-100";
	}
	return "";
});

function load(thumb: App.Http.Resources.Models.ThumbResource | undefined | null, isPasswordProtected: boolean) {
	if (thumb?.thumb === "uploads/thumb/") {
		src.value = Constants.BASE_URL + "/img/placeholder.png";
		if (thumb.type.includes("video")) {
			src.value = Constants.BASE_URL + "/img/play-icon.png";
		}
		if (thumb.type.includes("raw")) {
			src.value = Constants.BASE_URL + "/img/no_images.svg";
		}
	} else {
		src.value = isNotEmpty(thumb?.thumb)
			? (thumb?.thumb as string)
			: Constants.BASE_URL + (isPasswordProtected ? "/img/password.svg" : "/img/no_images.svg");
	}
	srcSet.value = isNotEmpty(thumb?.thumb2x) ? (thumb?.thumb2x as string) : "";
}

function isNotEmpty(link: string | null | undefined): boolean {
	return link !== "" && link !== null && link !== undefined;
}

load(props.thumb, props.isPasswordProtected);

watch(
	() => props.thumb,
	(newThumb: App.Http.Resources.Models.ThumbResource | undefined | null, _oldThumb: any) => {
		load(newThumb, props.isPasswordProtected);
	},
);
</script>
