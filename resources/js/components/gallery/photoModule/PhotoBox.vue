<template>
	<div
		v-if="photoStore.photo"
		id="imageview"
		ref="swipe"
		class="absolute top-0 left-0 w-full h-full flex items-center justify-center overflow-hidden"
		:class="{
			'pt-14': photoStore.imageViewMode === ImageViewMode.Pdf && !is_full_screen,
		}"
		@click="emits('rotateOverlay')"
	>
		<!--  This is a video file: put html5 player -->
		<video
			v-if="photoStore.imageViewMode == ImageViewMode.Video"
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
			<source :src="photoStore.photo.size_variants.original?.url ?? ''" />
			Your browser does not support the video tag.
		</video>
		<!-- This is a raw file: put a place holder -->
		<embed
			v-if="photoStore.imageViewMode == ImageViewMode.Pdf"
			id="image"
			alt="pdf"
			:title="photoStore.photo.title"
			aria-label="PDF preview"
			:src="photoStore.photo.size_variants.original?.url ?? ''"
			type="application/pdf"
			frameBorder="0"
			scrolling="auto"
			class="absolute m-auto bg-contain bg-center bg-no-repeat"
			height="90%"
			width="100%"
		/>
		<!-- This is a raw file: put a place holder -->
		<img
			v-if="photoStore.imageViewMode == ImageViewMode.Raw"
			id="image"
			alt="placeholder"
			class="absolute m-auto w-auto h-auto bg-contain bg-center bg-no-repeat"
			:src="getPlaceholderIcon()"
		/>
		<!-- This is a normal image: medium or original -->
		<img
			v-if="photoStore.imageViewMode == ImageViewMode.Medium"
			id="image"
			alt="medium"
			class="absolute m-auto w-auto h-auto bg-contain bg-center bg-no-repeat"
			:src="photoStore.photo.size_variants.medium?.url ?? ''"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:srcset="photoStore.srcSetMedium"
		/>
		<img
			v-if="photoStore.imageViewMode == ImageViewMode.Original"
			id="image"
			alt="big"
			class="absolute m-auto w-auto h-auto bg-contain bg-center bg-no-repeat"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:style="photoStore.style"
			:src="photoStore.photo.size_variants.original?.url ?? ''"
		/>
		<!-- This is a livephoto : medium -->
		<div
			v-if="photoStore.imageViewMode == ImageViewMode.LivePhotoMedium"
			id="livephoto"
			data-live-photo
			data-proactively-loads-video="true"
			:data-photo-src="photoStore.photo.size_variants.medium?.url"
			:data-video-src="photoStore.photo.live_photo_url"
			class="absolute m-auto w-auto h-auto"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:style="photoStore.style"
		></div>
		<!-- This is a livephoto : full -->
		<div
			v-if="photoStore.imageViewMode == ImageViewMode.LivePhotoOriginal"
			id="livephoto"
			data-live-photo
			data-proactively-loads-video="true"
			:data-photo-src="photoStore.photo.size_variants.original?.url"
			:data-video-src="photoStore.photo.live_photo_url"
			class="absolute m-auto w-auto h-auto"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:style="photoStore.style"
		></div>
	</div>
</template>
<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useImageHelpers } from "@/utils/Helpers";
import { useSwipe, type UseSwipeDirection } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { ref } from "vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { ImageViewMode, usePhotoStore } from "@/stores/PhotoState";

const { isLTR } = useLtRorRtL();

const swipe = ref<HTMLElement | null>(null);
const videoElement = ref<HTMLVideoElement | null>(null);
const togglableStore = useTogglablesStateStore();

const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();

const { is_swipe_vertically_to_go_back_enabled } = storeToRefs(lycheeStore);
const { is_slideshow_active, is_full_screen } = storeToRefs(togglableStore);

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
