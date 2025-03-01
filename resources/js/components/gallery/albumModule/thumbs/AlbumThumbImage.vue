<template>
	<span
		class="thumbimg absolute w-full h-full bg-neutral-800 shadow-md shadow-black/25 border-solid border border-neutral-400 ease-out transition-transform overflow-hidden"
		:class="props.class"
	>
		<img
			v-show="placeholderSrc"
			:alt="$t('gallery.placeholder')"
			class="absolute w-full h-full top-0 left-0 blur-md"
			:class="{ 'animate-fadeout animate-fill-forwards': isImageLoaded }"
			:src="placeholderSrc"
			data-overlay="false"
			draggable="false"
			loading="lazy"
		/>
		<img
			:alt="$t('gallery.thumbnail')"
			class="w-full h-full m-0 p-0 border-0 object-cover"
			:class="classObject"
			:src="src"
			:srcset="srcSet"
			@load="onImageLoad"
			data-overlay="false"
			draggable="false"
			loading="lazy"
		/>
	</span>
</template>
<script setup lang="ts">
import { useImageHelpers } from "@/utils/Helpers";
import { watch, ref, computed } from "vue";

const { isNotEmpty, getPlayIcon, getPlaceholderIcon, getNoImageIcon, getPaswwordIcon } = useImageHelpers();

const props = defineProps<{
	thumb: App.Http.Resources.Models.ThumbResource | undefined | null;
	class: string;
	isPasswordProtected: boolean;
}>();

const isImageLoaded = ref(false);
const src = ref("");
const srcSet = ref("");
const placeholderSrc = ref("");
const classObject = computed(() => ({
	"invert brightness-25 dark:invert-0 dark:brightness-100": src.value === getNoImageIcon() || src.value === getPaswwordIcon(),
	invisible: !isImageLoaded.value,
}));

function onImageLoad() {
	isImageLoaded.value = true;
}

function load(thumb: App.Http.Resources.Models.ThumbResource | undefined | null, isPasswordProtected: boolean) {
	if (isNotEmpty(thumb?.placeholder)) {
		placeholderSrc.value = thumb.placeholder;
	}
	if (thumb?.thumb === "uploads/thumb/") {
		src.value = getPlaceholderIcon();
		if (thumb.type.includes("video")) {
			src.value = getPlayIcon();
		}
		if (thumb.type.includes("raw")) {
			src.value = getNoImageIcon();
		}
	} else {
		src.value = isNotEmpty(thumb?.thumb) ? thumb.thumb : isPasswordProtected ? getPaswwordIcon() : getNoImageIcon();
	}
	srcSet.value = isNotEmpty(thumb?.thumb2x) ? thumb.thumb2x : "";
}

load(props.thumb, props.isPasswordProtected);

watch(
	() => props.thumb,
	(newThumb: App.Http.Resources.Models.ThumbResource | undefined | null, _oldThumb: any) => {
		load(newThumb, props.isPasswordProtected);
	},
);
</script>
