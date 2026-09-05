<template>
	<span
		class="thumbimg absolute w-full h-full bg-neutral-800 shadow-md shadow-black/25 ease-out transition-transform overflow-hidden"
		:class="[
			isDragging && !isSelectable ? '' : props.class,
			{ 'rounded-lg': is_rounded_corners_enabled, 'border-solid border border-accented': is_album_border_enabled },
		]"
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
		<Thumb
			v-if="coverId !== undefined && coverId !== null && albumId !== undefined"
			class="w-full h-full m-0 p-0 border-0 object-cover"
			:class="{ invisible: !isImageLoaded }"
			:album-id="albumId"
			:photo-id="coverId"
			type="small2x"
			@load="onImageLoad"
		/>
		<img
			v-else
			:alt="$t('gallery.thumbnail')"
			class="w-full h-full m-0 p-0 border-0 object-cover"
			:class="classObject"
			:src="src"
			:srcset="srcSet"
			data-overlay="false"
			draggable="false"
			loading="lazy"
			@load="onImageLoad"
		/>
	</span>
</template>
<script setup lang="ts">
import { useImageHelpers } from "@/utils/Helpers";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { watch, ref, computed } from "vue";
import { storeToRefs } from "pinia";
import Thumb from "@/v8/components/thumbs/Thumb.vue";

const { isNotEmpty, getPlayIcon, getPlaceholderIcon, getNoImageIcon, getPaswwordIcon } = useImageHelpers();

/**
 * `albumId`/`coverId`: optional, additive.
 * Every existing v2 caller passes neither, so `thumb` alone still drives
 * rendering exactly as before. When a caller (currently only root
 * smart-album tiles, `AlbumThumb.vue`) passes `coverId`
 * non-null, the cover resolves via `<Thumb>`/the Asset endpoint
 * instead — the same mechanism `AlbumThumbVirtual.vue` already
 * established for subalbum tiles. `coverId: null` (a v3 tile with no cached
 * cover) intentionally falls through to the existing `thumb`-based branch
 * below unchanged: the adapter already sets `thumb: null`, and `load()`'s
 * existing `isNotEmpty(thumb?.thumb) ? ... : isPasswordProtected ? ... : ...`
 * ternary already resolves that to the correct password/no-image icon with
 * no further change needed here.
 */
const props = defineProps<{
	thumb: App.Http.Resources.Models.ThumbResource | undefined | null;
	class: string;
	isPasswordProtected: boolean;
	isSelectable?: boolean;
	albumId?: string;
	coverId?: string | null;
}>();

const togglableStore = useTogglablesStateStore();
const { isDragging } = storeToRefs(togglableStore);
const lycheeStore = useLycheeStateStore();
const { is_rounded_corners_enabled, is_album_border_enabled } = storeToRefs(lycheeStore);

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
	(newThumb: App.Http.Resources.Models.ThumbResource | undefined | null, _oldThumb) => {
		load(newThumb, props.isPasswordProtected);
	},
);
</script>
