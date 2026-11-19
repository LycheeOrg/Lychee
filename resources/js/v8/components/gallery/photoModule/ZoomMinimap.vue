<template>
	<div
		class="absolute top-7 ltr:left-7 rtl:right-7 z-30 select-none transition-opacity duration-300"
		:class="{ 'pointer-events-none': isFaded && props.idleOpacity === 0 }"
		:style="{ opacity: isFaded ? props.idleOpacity / 100 : 1 }"
		@pointerdown.stop
		@pointermove.stop
		@pointerup.stop
		@click.stop
		@pointerenter="onEnter"
		@pointerleave="onLeave"
	>
		<div
			ref="mapEl"
			class="relative overflow-hidden rounded shadow-lg ring-1 ring-white/60 touch-none cursor-pointer"
			:class="props.aspect >= 1 ? 'w-28 sm:w-40' : 'h-28 sm:h-40'"
			:style="{ aspectRatio: props.aspect }"
			@pointerdown="onDown"
			@pointermove="onMove"
			@pointerup="onUp"
			@pointercancel="onUp"
		>
			<img :src="props.src" :srcset="props.srcset" sizes="160px" alt="" draggable="false" class="w-full h-full object-fill" />
			<div class="absolute border-2 border-white shadow-[0_0_0_9999px_rgba(0,0,0,0.45)]" :style="rectangleStyle"></div>
		</div>
		<div class="mt-1 text-xs text-white text-shadow text-center">{{ label }}</div>
	</div>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import type { NormalisedRect, Point } from "@/v8/utils/panZoom";

/**
 * Feature 078 minimap (FR-078-17..19): the whole photo, the visible area as a
 * rectangle and the zoom level. Tapping centres the view there, dragging the
 * rectangle moves it without a jump. Fades to `idleOpacity` % after
 * `fadeDelay` s without activity; a hovering mouse keeps it opaque.
 */
const props = defineProps<{
	src: string;
	srcset?: string;
	/** Photo width / height. */
	aspect: number;
	visible: NormalisedRect;
	scale: number;
	/** Changes on every pan/zoom of the photo. */
	activity: number;
	idleOpacity: number;
	fadeDelay: number;
}>();

const emits = defineEmits<{
	centre: [point: Point];
}>();

const mapEl = ref<HTMLElement | null>(null);
const isFaded = ref(false);
let isHovered = false;
let fadeTimer: ReturnType<typeof setTimeout> | undefined = undefined;
let dragOffset: Point | undefined = undefined;
let mapRect: DOMRect | undefined = undefined;

const label = computed(() => `${props.scale.toFixed(1)}×`);

const rectangleStyle = computed(() => ({
	left: `${props.visible.left * 100}%`,
	top: `${props.visible.top * 100}%`,
	width: `${(props.visible.right - props.visible.left) * 100}%`,
	height: `${(props.visible.bottom - props.visible.top) * 100}%`,
}));

function clearFadeTimer() {
	if (fadeTimer !== undefined) {
		clearTimeout(fadeTimer);
		fadeTimer = undefined;
	}
}

function wake() {
	isFaded.value = false;
	clearFadeTimer();
	if (!isHovered) {
		fadeTimer = setTimeout(() => (isFaded.value = true), props.fadeDelay * 1000);
	}
}

function onEnter(event: PointerEvent) {
	if (event.pointerType !== "mouse") {
		return;
	}
	isHovered = true;
	wake();
}

function onLeave(event: PointerEvent) {
	if (event.pointerType !== "mouse") {
		return;
	}
	isHovered = false;
	wake();
}

function normalised(event: PointerEvent): Point {
	const rect = mapRect ?? mapEl.value?.getBoundingClientRect();
	if (rect === undefined || rect.width === 0 || rect.height === 0) {
		return { x: 0.5, y: 0.5 };
	}
	return { x: (event.clientX - rect.left) / rect.width, y: (event.clientY - rect.top) / rect.height };
}

function isInsideRectangle(point: Point): boolean {
	return point.x >= props.visible.left && point.x <= props.visible.right && point.y >= props.visible.top && point.y <= props.visible.bottom;
}

function onDown(event: PointerEvent) {
	mapRect = mapEl.value?.getBoundingClientRect();
	mapEl.value?.setPointerCapture(event.pointerId);
	const point = normalised(event);
	const centre = { x: (props.visible.left + props.visible.right) / 2, y: (props.visible.top + props.visible.bottom) / 2 };
	dragOffset = isInsideRectangle(point) ? { x: centre.x - point.x, y: centre.y - point.y } : { x: 0, y: 0 };
	emits("centre", { x: point.x + dragOffset.x, y: point.y + dragOffset.y });
	wake();
}

function onMove(event: PointerEvent) {
	if (dragOffset === undefined) {
		return;
	}
	const point = normalised(event);
	emits("centre", { x: point.x + dragOffset.x, y: point.y + dragOffset.y });
	wake();
}

function onUp() {
	dragOffset = undefined;
	mapRect = undefined;
}

watch(() => props.activity, wake);

onMounted(wake);
onUnmounted(clearFadeTimer);
</script>
