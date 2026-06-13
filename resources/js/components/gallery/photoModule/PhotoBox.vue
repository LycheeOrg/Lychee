<template>
	<div
		v-if="photoStore.photo"
		id="imageview"
		ref="swipe"
		class="absolute top-0 left-0 w-full h-full flex items-center justify-center overflow-hidden"
		:class="{
			'pt-14': photoStore.imageViewMode === ImageViewMode.Pdf && !is_full_screen,
		}"
		@click="handleClick"
		@touchstart.passive="onTouchStart"
		@touchmove="onTouchMove"
		@touchend.passive="onTouchEnd"
	>
		<div class="absolute inset-0 flex items-center justify-center" :style="zoomStyle">
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
				:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
				:src="photoStore.photo.size_variants.medium?.url ?? ''"
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
	</div>
</template>
<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useImageHelpers } from "@/utils/Helpers";
import { useSwipe, type UseSwipeDirection } from "@vueuse/core";
import { storeToRefs } from "pinia";
import { ref, computed, watch } from "vue";
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

// ── Zoom / pan state ────────────────────────────────────────────────────────
const MAX_ZOOM = 5;
const DOUBLE_TAP_MS = 300;

const zoomScale = ref(1);
const panX = ref(0); // screen-space pixels
const panY = ref(0);
const hasPanned = ref(false);
const isZoomed = computed(() => zoomScale.value > 1.01);

// Non-reactive touch tracking
let touchStartX = 0;
let touchStartY = 0;
let lastPinchDist = 0;
let lastTapTime = 0;

const zoomStyle = computed(() => {
	if (!isZoomed.value) return {};
	// translate is applied after scale so panX/panY are in screen pixels
	return { transform: `translate(${panX.value}px, ${panY.value}px) scale(${zoomScale.value})` };
});

function touchDist(a: Touch, b: Touch): number {
	return Math.hypot(b.clientX - a.clientX, b.clientY - a.clientY);
}

function clampPan(scale: number, x: number, y: number): { x: number; y: number } {
	const el = swipe.value;
	if (!el) return { x, y };
	// Maximum shift before the image edge passes the container edge
	const maxX = (el.clientWidth * (scale - 1)) / 2;
	const maxY = (el.clientHeight * (scale - 1)) / 2;
	return { x: Math.max(-maxX, Math.min(maxX, x)), y: Math.max(-maxY, Math.min(maxY, y)) };
}

function resetZoom() {
	zoomScale.value = 1;
	panX.value = 0;
	panY.value = 0;
}

function onTouchStart(e: TouchEvent) {
	hasPanned.value = false;
	if (e.touches.length === 2) {
		lastPinchDist = touchDist(e.touches[0], e.touches[1]);
	} else if (e.touches.length === 1) {
		touchStartX = e.touches[0].clientX;
		touchStartY = e.touches[0].clientY;
	}
}

function onTouchMove(e: TouchEvent) {
	// Let the browser/element handle video and PDF natively
	if (photoStore.imageViewMode === ImageViewMode.Video || photoStore.imageViewMode === ImageViewMode.Pdf) {
		return;
	}

	if (e.touches.length === 2) {
		e.preventDefault();
		const dist = touchDist(e.touches[0], e.touches[1]);
		const factor = dist / lastPinchDist;
		lastPinchDist = dist;
		zoomScale.value = Math.max(1, Math.min(MAX_ZOOM, zoomScale.value * factor));
		if (zoomScale.value <= 1) {
			panX.value = 0;
			panY.value = 0;
		}
		hasPanned.value = true;
	} else if (e.touches.length === 1 && isZoomed.value) {
		e.preventDefault();
		const dx = e.touches[0].clientX - touchStartX;
		const dy = e.touches[0].clientY - touchStartY;
		touchStartX = e.touches[0].clientX;
		touchStartY = e.touches[0].clientY;
		const clamped = clampPan(zoomScale.value, panX.value + dx, panY.value + dy);
		panX.value = clamped.x;
		panY.value = clamped.y;
		hasPanned.value = true;
	}
}

function onTouchEnd(_e: TouchEvent) {
	if (zoomScale.value <= 1.01) {
		resetZoom();
	}
}

function handleClick() {
	// Suppress the click that fires at the end of a pan gesture
	if (hasPanned.value) {
		hasPanned.value = false;
		return;
	}

	// Double-tap: zoom in at 2× or reset if already zoomed
	const now = Date.now();
	if (now - lastTapTime < DOUBLE_TAP_MS) {
		lastTapTime = 0;
		if (isZoomed.value) {
			resetZoom();
		} else {
			zoomScale.value = 2;
		}
		return;
	}
	lastTapTime = now;

	emits("rotateOverlay");
}

// Reset zoom when navigating to another photo
watch(
	() => photoStore.photo?.id,
	() => {
		resetZoom();
		hasPanned.value = false;
	},
);

// ── Swipe-to-navigate (disabled while zoomed) ───────────────────────────────
useSwipe(swipe, {
	onSwipe(_e: TouchEvent) {},
	onSwipeEnd(_e: TouchEvent, direction: UseSwipeDirection) {
		if (isZoomed.value) return;
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
