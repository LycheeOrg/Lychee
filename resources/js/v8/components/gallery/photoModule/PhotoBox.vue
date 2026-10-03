<template>
	<div
		v-if="photoStore.photo"
		id="imageview"
		ref="containerEl"
		class="absolute top-0 left-0 w-full h-full flex items-center justify-center overflow-hidden"
		:class="{
			'pt-14': photoStore.imageViewMode === ImageViewMode.Pdf && !is_full_screen,
			'touch-none': isZoomable,
		}"
		:style="{ cursor }"
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
			:loop="lycheeStore.is_video_loop_enabled"
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
		<!-- Zoom layer (Feature 078): the image and the boxes drawn on it are panned and zoomed together. -->
		<div class="absolute inset-0 flex items-center justify-center select-none" :style="layerStyle">
			<!-- This is a normal image: medium or original -->
			<img
				v-if="photoStore.imageViewMode == ImageViewMode.Medium"
				ref="imageEl"
				id="image"
				alt="medium"
				draggable="false"
				class="absolute m-auto w-auto h-auto bg-contain bg-center bg-no-repeat"
				:src="zoomSrc ?? photoStore.photo.size_variants.medium?.url ?? ''"
				:class="
					is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'
				"
				:style="lockedSize"
				:srcset="zoomSrc === null ? photoStore.srcSetMedium : undefined"
				:sizes="zoomSrc === null ? photoStore.sizesMedium : undefined"
				@load="updateFaceOverlay"
			/>
			<img
				v-if="photoStore.imageViewMode == ImageViewMode.Original"
				ref="imageEl"
				id="image"
				alt="big"
				draggable="false"
				class="absolute m-auto w-auto h-auto bg-contain bg-center bg-no-repeat"
				:class="
					is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'
				"
				:style="[photoStore.style, lockedSize]"
				:src="zoomSrc ?? photoStore.photo.size_variants.original?.url ?? ''"
				@load="updateFaceOverlay"
			/>
			<!-- Face overlay: positioned to exactly match the rendered image via its layout offsets -->
			<div
				v-if="isFaceEnabled && (loadedFaces.length > 0 || hiddenFaceCount > 0)"
				class="absolute z-10 pointer-events-none"
				:style="faceOverlayStyle"
			>
				<FaceOverlay :faces="loadedFaces" :hidden-face-count="hiddenFaceCount" @faces-updated="handleFacesUpdated" />
			</div>
			<!-- NSFW detection overlay: positioned to match the rendered image -->
			<div v-if="isNsfwEnabled && loadedNsfwDetections.length > 0" class="absolute z-10 pointer-events-none" :style="faceOverlayStyle">
				<NsfwDetectionOverlay :detections="loadedNsfwDetections" :image-width="nsfwImageWidth" :image-height="nsfwImageHeight" />
			</div>
		</div>
		<!-- This is a livephoto : medium -->
		<div
			v-if="photoStore.imageViewMode == ImageViewMode.LivePhotoMedium"
			ref="livePhotoEl"
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
			ref="livePhotoEl"
			id="livephoto"
			data-live-photo
			data-proactively-loads-video="true"
			:data-photo-src="photoStore.photo.size_variants.original?.url"
			:data-video-src="photoStore.photo.live_photo_url"
			class="absolute m-auto w-auto h-auto"
			:class="is_full_screen || is_slideshow_active ? 'max-w-full max-h-full' : 'max-w-full md:max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]'"
			:style="photoStore.style"
		></div>
		<ZoomMinimap
			v-if="isMinimapVisible && panZoom.fit.value !== undefined"
			:src="minimapSrc"
			:srcset="minimapSrcset"
			:aspect="panZoom.fit.value.width / panZoom.fit.value.height"
			:visible="visibleRect(panZoom.state.value, panZoom.fit.value, panZoom.containerSize.value)"
			:scale="panZoom.state.value.scale"
			:activity="panZoom.activity.value"
			:idle-opacity="minimapIdleOpacity"
			:fade-delay="lycheeStore.photo_minimap_fade_delay"
			@centre="panZoom.centreOnPhoto"
		/>
		<!-- Single face assignment modal, opened from FaceOverlay or PhotoDetails through the shared modal state -->
		<FaceAssignmentModal
			v-if="face_for_assignment"
			v-model:open="is_face_assignment_visible"
			:face="face_for_assignment"
			@assigned="handleFacesUpdated"
			@dismissed="handleFacesUpdated"
		/>
	</div>
</template>
<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotoFacesStore } from "@/stores/PhotoFacesState";
import { usePhotoNsfwDetectionsStore } from "@/stores/PhotoNsfwDetectionsState";
import { useImageHelpers } from "@/utils/Helpers";
import { useSwipe, type UseSwipeDirection } from "@vueuse/core";
import * as LivePhotosKit from "livephotoskit";
import { storeToRefs } from "pinia";
import { computed, markRaw, nextTick, reactive, watch, watchEffect, onUnmounted, ref } from "vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { ImageViewMode, usePhotoStore, type ZoomControls } from "@/stores/PhotoState";
import FaceOverlay from "./FaceOverlay.vue";
import FaceAssignmentModal from "@/v8/components/modals/faceRecog/FaceAssignmentModal.vue";
import NsfwDetectionOverlay from "./NsfwDetectionOverlay.vue";
import ZoomMinimap from "./ZoomMinimap.vue";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { usePanZoom } from "@/v8/composables/usePanZoom";
import {
	DOUBLE_TAP_MS,
	classifyHitTarget,
	imageRect,
	isDoubleTap,
	pickZoomSource,
	rectContains,
	visibleRect,
	type HitTarget,
	type Point,
	type Rect,
	type TapRecord,
} from "@/v8/utils/panZoom";

const { isLTR } = useLtRorRtL();

const containerEl = ref<HTMLElement | null>(null);
const videoElement = ref<HTMLVideoElement | null>(null);
const livePhotoEl = ref<HTMLElement | null>(null);
const togglableStore = useTogglablesStateStore();

const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();

const isFaceEnabled = computed(() => lycheeStore.is_face_recognition_enabled);
const isNsfwEnabled = computed(() => lycheeStore.is_nsfw_classifier_enabled);

const { is_swipe_vertically_to_go_back_enabled } = storeToRefs(lycheeStore);
const { is_slideshow_active, is_full_screen, is_face_assignment_visible, face_for_assignment } = storeToRefs(togglableStore);

const { getPlaceholderIcon } = useImageHelpers();

// Face overlay: tracks the actual rendered image position/size
const imageEl = ref<HTMLImageElement | null>(null);
const faceOverlayStyle = reactive({ top: "0px", left: "0px", width: "0px", height: "0px" });
let imageResizeObserver: ResizeObserver | null = null;

const facesStore = usePhotoFacesStore();
const faceData = computed(() => facesStore.get(photoStore.photo?.id ?? ""));
const loadedFaces = computed(() => faceData.value.faces);
const hiddenFaceCount = computed(() => faceData.value.hiddenFaceCount);

const nsfwDetectionsStore = usePhotoNsfwDetectionsStore();
const nsfwData = computed(() => nsfwDetectionsStore.get(photoStore.photo?.id ?? ""));
const loadedNsfwDetections = computed(() => nsfwData.value.detections);
const nsfwImageWidth = computed(() => nsfwData.value.imageWidth);
const nsfwImageHeight = computed(() => nsfwData.value.imageHeight);

const props = defineProps<{
	/** Pan & zoom (Feature 078) is on in the main lightbox only; Flow and Moderation keep their click handlers. */
	isZoomEnabled?: boolean;
}>();

const emits = defineEmits<{
	rotateOverlay: [];
	goBack: [];
	next: [];
	previous: [];
	facesUpdated: [];
}>();

// ---- Pan & zoom (Feature 078) ----

/** FR-078-02. */
const isZoomable = computed(
	() =>
		props.isZoomEnabled === true &&
		(photoStore.imageViewMode === ImageViewMode.Medium || photoStore.imageViewMode === ImageViewMode.Original) &&
		!is_slideshow_active.value,
);
const isClickZoom = computed(() => lycheeStore.photo_click_action === "zoom");
const zoomSource = computed(() => (photoStore.photo === undefined ? undefined : pickZoomSource(photoStore.photo)));

/** Sharper source swapped in while zoomed (FR-078-12); `null` while the regular variant is shown. */
const zoomSrc = ref<string | null>(null);
let loadingZoomSrc = false;
let failedZoomSrc = false;

const panZoom = usePanZoom({
	container: containerEl,
	image: imageEl,
	zoomable: () => isZoomable.value,
	sourceWidth: () => zoomSource.value?.width ?? 0,
	wheelNavigates: () => lycheeStore.is_scroll_to_navigate_photos_enabled,
	onTap,
	onContainerResize: () => {
		// Zoom was reset: release the locked size so the image fits the new container.
		zoomSrc.value = null;
		nextTick(updateFaceOverlay);
	},
});

const layerStyle = computed(() => {
	if (!isZoomable.value) {
		return undefined;
	}
	const { scale, x, y } = panZoom.state.value;
	return { transform: `translate(${x}px, ${y}px) scale(${scale})`, transformOrigin: "0 0" };
});

/** Once a larger source is shown, keep the image at its fitted size so nothing jumps. */
const lockedSize = computed(() => {
	const fit = panZoom.fit.value;
	if (zoomSrc.value === null || fit === undefined) {
		return undefined;
	}
	return { width: `${fit.width}px`, height: `${fit.height}px` };
});

function maybeLoadZoomSource(scale: number) {
	const image = imageEl.value;
	const fit = panZoom.fit.value;
	const source = zoomSource.value;
	if (image === null || fit === undefined || source === undefined || zoomSrc.value !== null || loadingZoomSrc || failedZoomSrc) {
		return;
	}
	if (source.width <= image.naturalWidth || scale * fit.width * window.devicePixelRatio <= image.naturalWidth) {
		return;
	}
	loadingZoomSrc = true;
	const loader = new Image();
	loader.src = source.url;
	loader
		.decode()
		.then(() => (zoomSrc.value = source.url))
		.catch(() => (failedZoomSrc = true))
		.finally(() => (loadingZoomSrc = false));
}

watch(() => panZoom.state.value.scale, maybeLoadZoomSource);

/** Overlay rectangle relative to the container, `undefined` when no overlay is rendered. */
function overlayRect(): Rect | undefined {
	const overlay = document.getElementById("image_overlay");
	const container = containerEl.value;
	if (overlay === null || container === null) {
		return undefined;
	}
	const o = overlay.getBoundingClientRect();
	const c = container.getBoundingClientRect();
	return { left: o.left - c.left, top: o.top - c.top, width: o.width, height: o.height };
}

let cachedOverlayRect: Rect | undefined = undefined;

function hitTarget(point: Point, overlay: Rect | undefined): HitTarget {
	const fit = panZoom.fit.value;
	if (fit === undefined) {
		return "nothing";
	}
	return classifyHitTarget({
		point,
		container: panZoom.containerSize.value,
		image: imageRect(panZoom.state.value, fit),
		overlay,
		isOverlayNone: lycheeStore.image_overlay_type === "none",
		isExifDisabled: lycheeStore.is_exif_disabled,
		isLTR: isLTR(),
	});
}

/** FR-078-06: magnifier over the picture in zoom mode, grab while panning or zoomed in overlay mode. */
const cursor = computed(() => {
	if (!isZoomable.value) {
		return undefined;
	}
	if (panZoom.isPanning.value) {
		return "grabbing";
	}
	if (!isClickZoom.value) {
		return panZoom.zoomed.value ? "grab" : undefined;
	}
	const point = panZoom.hoverPoint.value;
	if (point === undefined || hitTarget(point, cachedOverlayRect) !== "picture") {
		return undefined;
	}
	return panZoom.zoomed.value ? "zoom-out" : "zoom-in";
});

watch(
	() => panZoom.hoverPoint.value === undefined,
	(isOutside) => {
		if (!isOutside) {
			cachedOverlayRect = overlayRect();
		}
	},
);

let pendingTap: { record: TapRecord; timer: ReturnType<typeof setTimeout> } | undefined = undefined;

function clearPendingTap() {
	if (pendingTap !== undefined) {
		clearTimeout(pendingTap.timer);
		pendingTap = undefined;
	}
}

function rotateOverlay() {
	emits("rotateOverlay");
	nextTick(() => (cachedOverlayRect = overlayRect()));
}

/** FR-078-05: in zoom mode the click target decides. */
function onZoomModeTap(point: Point) {
	const target = hitTarget(point, overlayRect());
	if (target === "picture") {
		panZoom.toggleAt(point);
		return;
	}
	if (target !== "nothing") {
		rotateOverlay();
	}
}

/** FR-078-04: in overlay mode a tap rotates after the double-tap window, a double-tap toggles zoom. */
function onOverlayModeTap(point: Point) {
	const record = { time: performance.now(), point };
	if (pendingTap !== undefined && isDoubleTap(pendingTap.record, record)) {
		clearPendingTap();
		const fit = panZoom.fit.value;
		if (panZoom.zoomed.value || (fit !== undefined && rectContains(imageRect(panZoom.state.value, fit), point))) {
			panZoom.toggleAt(point);
		}
		return;
	}
	clearPendingTap();
	pendingTap = {
		record,
		timer: setTimeout(() => {
			pendingTap = undefined;
			rotateOverlay();
		}, DOUBLE_TAP_MS),
	};
}

function onTap(point: Point) {
	if (!isZoomable.value || panZoom.fit.value === undefined) {
		rotateOverlay();
		return;
	}
	if (isClickZoom.value) {
		onZoomModeTap(point);
		return;
	}
	onOverlayModeTap(point);
}

// Keyboard (FR-078-11): the panel shortcuts drive the zoom through these controls.
const zoomControls: ZoomControls = markRaw({
	toggle: panZoom.toggle,
	zoomIn: panZoom.zoomIn,
	zoomOut: panZoom.zoomOut,
	reset: panZoom.reset,
});

watch(
	isZoomable,
	(zoomable) => {
		if (zoomable) {
			photoStore.zoom_controls = zoomControls;
			return;
		}
		panZoom.reset();
		if (photoStore.zoom_controls === zoomControls) {
			photoStore.zoom_controls = undefined;
		}
	},
	{ immediate: true },
);

watch(panZoom.zoomed, (zoomed) => {
	if (photoStore.zoom_controls === zoomControls) {
		photoStore.is_zoomed = zoomed;
	}
});

// Minimap (FR-078-16..20)
const isTouch = isTouchDevice();
const isMinimapEnabled = computed(() => (isTouch ? lycheeStore.is_photo_minimap_enabled_mobile : lycheeStore.is_photo_minimap_enabled));
const minimapIdleOpacity = computed(() => (isTouch ? lycheeStore.photo_minimap_idle_opacity_mobile : lycheeStore.photo_minimap_idle_opacity));
const isMinimapVisible = computed(() => isMinimapEnabled.value && isZoomable.value && panZoom.zoomed.value);
const minimapSrc = computed(() => {
	const variants = photoStore.photo?.size_variants;
	return variants?.small?.url ?? imageEl.value?.currentSrc ?? "";
});
const minimapSrcset = computed(() => {
	const small = photoStore.photo?.size_variants.small ?? null;
	const small2x = photoStore.photo?.size_variants.small2x ?? null;
	if (small?.url === undefined || small.url === null || small2x?.url === undefined || small2x.url === null) {
		return undefined;
	}
	return `${small.url} ${small.width}w, ${small2x.url} ${small2x.width}w`;
});

onUnmounted(() => {
	clearPendingTap();
	// The next photo's box mounts before this one leaves (slide transition): only clear our own registration.
	if (photoStore.zoom_controls === zoomControls) {
		photoStore.zoom_controls = undefined;
		photoStore.is_zoomed = false;
	}
});

// ---- Faces & NSFW ----

function handleFacesUpdated() {
	emits("facesUpdated");
	const photoId = photoStore.photo?.id;
	if (photoId !== undefined) {
		facesStore.invalidate(photoId);
	}
}

function updateFaceOverlay() {
	panZoom.measure();
	const img = imageEl.value;
	if (!img) return;
	// Use layout offset values (not getBoundingClientRect) so CSS transforms on
	// parent containers (e.g. animate-zoomIn on first open, the zoom layer) don't
	// affect positioning. img.offsetParent is the zoom layer, which also holds the overlay.
	faceOverlayStyle.top = img.offsetTop + "px";
	faceOverlayStyle.left = img.offsetLeft + "px";
	faceOverlayStyle.width = img.offsetWidth + "px";
	faceOverlayStyle.height = img.offsetHeight + "px";
}

// Re-runs whenever imageEl changes (photo/mode switch) — flush:'post' ensures DOM is ready.
watchEffect(
	() => {
		imageResizeObserver?.disconnect();
		const img = imageEl.value;
		if (!img) return;
		imageResizeObserver = new ResizeObserver(updateFaceOverlay);
		imageResizeObserver.observe(img);
		// Also observe the container so that sidebar open/close (which resizes the
		// imageview container but may not resize the image itself when it is
		// height-constrained) still triggers an overlay recompute.
		if (containerEl.value) {
			imageResizeObserver.observe(containerEl.value);
		}
		// rAF ensures browser layout is complete (handles both cached and uncached images)
		requestAnimationFrame(updateFaceOverlay);
	},
	{ flush: "post" },
);

onUnmounted(() => imageResizeObserver?.disconnect());

// Live Photos aren't scanned for automatically past initial page load (LivePhotosKit only
// auto-augments `[data-live-photo]` elements present at DOMContentLoaded), and the div here
// is destroyed/recreated by v-if on every mode switch, so it must be augmented explicitly
// each time it (re)appears. flush:'post' ensures the data-photo-src/data-video-src attribute
// bindings have already been applied to the element before LivePhotosKit reads them.
watchEffect(
	() => {
		const el = livePhotoEl.value;
		if (!el) {
			return;
		}
		LivePhotosKit.augmentElementAsPlayer(el);
	},
	{ flush: "post" },
);

watch(
	[() => photoStore.photo?.id, isFaceEnabled],
	([photoId, faceEnabled]) => {
		if (!faceEnabled || photoId === undefined || (photoStore.photo?.face_count ?? 0) <= 0) {
			return;
		}

		facesStore.fetch(photoId);
	},
	{ immediate: true },
);

// Lazy fetch: only load NSFW detections when the overlay is toggled visible
watch(
	[() => photoStore.photo?.id, () => lycheeStore.nsfw_overlay_mode, isNsfwEnabled],
	([photoId, mode, nsfwEnabled]) => {
		if (!nsfwEnabled || photoId === undefined || mode === "hidden") {
			return;
		}
		nsfwDetectionsStore.fetch(photoId);
	},
	{ immediate: true },
);

// ---- Swipe navigation ----

// While the user has pinch-zoomed the page (native viewport zoom, see meta.blade.php
// `maximum-scale=4.0 user-scalable=yes`, kept on non-zoomable photos), a single-finger
// drag is panning around the page, not a navigation swipe. Gestures that started
// zoomed or used two fingers belong to pan & zoom (FR-078-09).
function isPageZoomed(): boolean {
	return typeof window !== "undefined" && window.visualViewport !== null && window.visualViewport.scale > 1.01;
}

useSwipe(containerEl, {
	onSwipe(_e: TouchEvent) {},
	onSwipeEnd(_e: TouchEvent, direction: UseSwipeDirection) {
		if (isPageZoomed() || panZoom.isSwipeBlocked()) {
			return;
		}
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
