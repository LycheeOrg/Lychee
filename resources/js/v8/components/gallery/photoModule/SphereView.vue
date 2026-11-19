<template>
	<canvas
		ref="canvasEl"
		class="absolute inset-0 w-full h-full touch-none select-none"
		:class="sphere.isDragging.value ? 'cursor-grabbing' : 'cursor-grab'"
		:aria-label="$t('gallery.photo.edit.is_360')"
	/>
</template>
<script setup lang="ts">
import { markRaw, onMounted, onUnmounted, ref, watch } from "vue";
import { usePhotoStore, type ZoomControls } from "@/stores/PhotoState";
import { useSphereViewer } from "@/v8/composables/useSphereViewer";
import { pickZoomSource } from "@/v8/utils/panZoom";
import { coverageOf, pickSphereStartSource } from "@/v8/utils/sphere";

/**
 * 360° sphere view of the photo (Feature 082, FR-082-08). Loaded as a lazy
 * chunk by `PhotoBox`; registers its zoom controls for the panel shortcuts
 * like the flat view does (FR-082-11).
 */
const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const emits = defineEmits<{
	unavailable: [];
}>();

const photoStore = usePhotoStore();
const canvasEl = ref<HTMLCanvasElement | null>(null);

const sphere = useSphereViewer({
	canvas: canvasEl,
	coverage: () => coverageOf(props.photo.panorama, props.photo.size_variants.original?.width ?? 0, props.photo.size_variants.original?.height ?? 0),
	startSource: () => pickSphereStartSource(props.photo),
	upgradeSource: () => pickZoomSource(props.photo),
	onUnavailable: () => emits("unavailable"),
});

const zoomControls: ZoomControls = markRaw({
	toggle: sphere.toggle,
	zoomIn: sphere.zoomIn,
	zoomOut: sphere.zoomOut,
	reset: sphere.reset,
});

onMounted(() => {
	photoStore.zoom_controls = zoomControls;
	photoStore.is_zoomed = sphere.zoomed.value;
});

watch(sphere.zoomed, (zoomed) => {
	if (photoStore.zoom_controls === zoomControls) {
		photoStore.is_zoomed = zoomed;
	}
});

onUnmounted(() => {
	// The next photo's box mounts before this one leaves (slide transition): only clear our own registration.
	if (photoStore.zoom_controls === zoomControls) {
		photoStore.zoom_controls = undefined;
		photoStore.is_zoomed = false;
	}
});
</script>
