<template>
	<div
		id="imageview"
		ref="swipe"
		class="absolute top-0 left-0 w-full h-full flex items-center justify-center overflow-hidden"
		:class="{
			'pt-14': imageViewMode === ImageViewMode.Pdf && !is_full_screen,
		}"
		@click="emits('rotateOverlay')"
	>
		<!--  This is a video file: put html5 player -->
		<video
			v-if="imageViewMode == ImageViewMode.Video"
			id="image"
			ref="videoElement"
			width="auto"
			height="auto"
			controls
			class="absolute m-auto w-auto h-auto"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			autobuffer
			:autoplay="lycheeStore.can_autoplay"
		>
			<source :src="props.photo.size_variants.original?.url ?? ''" />
			Your browser does not support the video tag.
		</video>
		<!-- This is a raw file: put a place holder -->
		<embed
			v-if="imageViewMode == ImageViewMode.Pdf"
			id="image"
			alt="pdf"
			:src="props.photo.size_variants.original?.url ?? ''"
			type="application/pdf"
			frameBorder="0"
			scrolling="auto"
			class="absolute m-auto bg-contain bg-center bg-no-repeat"
			height="90%"
			width="100%"
		/>
		<!-- This is a raw file: put a place holder -->
		<img
			v-if="imageViewMode == ImageViewMode.Raw"
			id="image"
			alt="placeholder"
			class="absolute m-auto w-auto h-auto bg-contain bg-center bg-no-repeat"
			:src="getPlaceholderIcon()"
		/>
		<!-- This is a normal image: medium or original -->
		<img
			v-if="imageViewMode == ImageViewMode.Medium"
			id="image"
			alt="medium"
			class="absolute m-auto w-auto h-auto bg-contain bg-center bg-no-repeat"
			:src="props.photo.size_variants.medium?.url ?? ''"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:srcset="srcSetMedium"
		/>
		<img
			v-if="imageViewMode == ImageViewMode.Original"
			id="image"
			alt="big"
			class="absolute m-auto w-auto h-auto bg-contain bg-center bg-no-repeat"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:style="style"
			:src="props.photo.size_variants.original?.url ?? ''"
		/>
		<!-- This is a livephoto : medium -->
		<div
			v-if="imageViewMode == ImageViewMode.LivePhotoMedium"
			id="livephoto"
			data-live-photo
			data-proactively-loads-video="true"
			:data-photo-src="photo?.size_variants.medium?.url"
			:data-video-src="photo?.live_photo_url"
			class="absolute m-auto w-auto h-auto"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:style="style"
		></div>
		<!-- This is a livephoto : full -->
		<div
			v-if="imageViewMode == ImageViewMode.LivePhotoOriginal"
			id="livephoto"
			data-live-photo
			data-proactively-loads-video="true"
			:data-photo-src="photo?.size_variants.original?.url"
			:data-video-src="photo?.live_photo_url"
			class="absolute m-auto w-auto h-auto"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:style="style"
		></div>
	</div>
</template>
<script setup lang="ts">
import { ImageViewMode, usePhotoBaseFunction } from "@/composables/photo/basePhoto";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useImageHelpers } from "@/utils/Helpers";
import { useSwipe, type UseSwipeDirection } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { computed } from "vue";
import { ref } from "vue";

import { useLtRorRtL } from "@/utils/Helpers";
const { isLTR } = useLtRorRtL();

const swipe = ref<HTMLElement | null>(null);
const videoElement = ref<HTMLVideoElement | null>(null);
const togglableStore = useTogglablesStateStore();

const lycheeStore = useLycheeStateStore();
const { is_swipe_vertically_to_go_back_enabled } = storeToRefs(lycheeStore);
const { is_slideshow_active, is_full_screen } = storeToRefs(togglableStore);

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const photo = computed(() => props.photo);

const { srcSetMedium, style, imageViewMode } = usePhotoBaseFunction(photo);
const { getPlaceholderIcon } = useImageHelpers();

const emits = defineEmits<{
	rotateOverlay: [];
	goBack: [];
	next: [];
	previous: [];
}>();

useSwipe(swipe, {
	onSwipe(_e: TouchEvent) {},
	onSwipeEnd(_e: TouchEvent, direction: UseSwipeDirection) {
		if (direction === "left" && isLTR()) {
			emits("next");
		} else if (direction === "right" && isLTR()) {
			emits("previous");
		} else if (direction === "left" && !isLTR()) {
			emits("previous");
		} else if (direction === "right" && !isLTR()) {
			emits("next");
		} else if (is_swipe_vertically_to_go_back_enabled.value) {
			emits("goBack");
		}
	},
});
</script>
