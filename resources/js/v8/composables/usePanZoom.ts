import { computed, onMounted, onUnmounted, readonly, ref, type Ref } from "vue";
import {
	CLICK_ZOOM,
	IDENTITY,
	KEY_ZOOM_STEP,
	clampPan,
	centreOn,
	distance,
	isTap,
	isZoomed,
	maxScale,
	midpoint,
	zoomAround,
	type PanZoomState,
	type Point,
	type Rect,
	type Size,
} from "@/v8/utils/panZoom";

/**
 * Pan & zoom for the v8 lightbox (Feature 078).
 *
 * One Pointer Events state machine on `#imageview` decides between tap, pan and
 * pinch, so the gestures never fight each other. Navigation swipes stay on
 * `useSwipe` (touch events, unaffected by the browser taking over native pinch
 * on non-zoomable photos); `isSwipeBlocked()` tells it to ignore gestures that
 * started zoomed or used two fingers. Taps are delivered
 * through the regular `click` event (face boxes stop it themselves); the click
 * that follows a drag or a multi-pointer gesture is swallowed in the capture
 * phase (FR-078-07). Transform writes are batched per animation frame
 * (NFR-078-01); the geometry lives in `utils/panZoom.ts`.
 */
export type PanZoomOptions = {
	container: Ref<HTMLElement | null>;
	image: Ref<HTMLElement | null>;
	/** FR-078-02. */
	zoomable: () => boolean;
	/** Width of the zoom source (FR-078-03), used for the zoom ceiling. */
	sourceWidth: () => number;
	/** `is_scroll_to_navigate_photos_enabled` (FR-078-10). */
	wheelNavigates: () => boolean;
	onTap: (point: Point) => void;
	/** Called when the container size changes while zoomed (zoom is reset). */
	onContainerResize: () => void;
};

type Gesture = {
	start: Point;
	startState: PanZoomState;
	startedZoomed: boolean;
	maxPointers: number;
	moved: boolean;
	pinchDistance: number;
	pinchMidpoint: Point;
};

const ANIMATION_MS = 200;
const CTRL_WHEEL_MAX_DELTA = 25;
/** A click within this delay after a drag ends belongs to that drag. */
const CLICK_SUPPRESS_MS = 400;

function prefersReducedMotion(): boolean {
	return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

function wheelDelta(event: WheelEvent, containerHeight: number): number {
	if (event.deltaMode === WheelEvent.DOM_DELTA_LINE) {
		return event.deltaY * 16;
	}
	if (event.deltaMode === WheelEvent.DOM_DELTA_PAGE) {
		return event.deltaY * containerHeight;
	}
	return event.deltaY;
}

export function usePanZoom(options: PanZoomOptions) {
	const state = ref<PanZoomState>({ ...IDENTITY });
	const fit = ref<Rect | undefined>(undefined);
	const containerSize = ref<Size>({ width: 0, height: 0 });
	const isPanning = ref(false);
	/** Mouse position over the container (cursor feedback, FR-078-06), `undefined` when outside. */
	const hoverPoint = ref<Point | undefined>(undefined);
	/** Incremented on every pan/zoom, read by the minimap fade (FR-078-19). */
	const activity = ref(0);

	const zoomed = computed(() => isZoomed(state.value));
	const ceiling = computed(() => maxScale(options.sourceWidth(), fit.value?.width ?? 0));

	const pointers = new Map<number, Point>();
	let gesture: Gesture | undefined = undefined;
	/** The current or last pointer sequence; read by `isSwipeBlocked()` on `touchend`. */
	let lastGesture: Gesture | undefined = undefined;
	let containerRect: DOMRect | undefined = undefined;
	let suppressClicksUntil = 0;
	let pendingState: PanZoomState | undefined = undefined;
	let frame = 0;
	let animation = 0;
	/** End state of the running animation: quick repeated key presses build on it, not on the in-between state. */
	let animationTarget: PanZoomState | undefined = undefined;

	function commit(next: PanZoomState) {
		state.value = next;
		activity.value++;
	}

	function flush() {
		frame = 0;
		if (pendingState !== undefined) {
			commit(pendingState);
			pendingState = undefined;
		}
	}

	function schedule(next: PanZoomState) {
		pendingState = next;
		if (frame === 0) {
			frame = requestAnimationFrame(flush);
		}
	}

	function stopAnimation() {
		animationTarget = undefined;
		if (animation !== 0) {
			cancelAnimationFrame(animation);
			animation = 0;
		}
	}

	function animateTo(target: PanZoomState) {
		stopAnimation();
		if (prefersReducedMotion()) {
			commit(target);
			return;
		}
		const from = { ...state.value };
		animationTarget = target;
		const startTime = performance.now();
		const step = (now: number) => {
			const t = Math.min((now - startTime) / ANIMATION_MS, 1);
			const eased = 1 - Math.pow(1 - t, 3);
			commit({
				scale: from.scale + (target.scale - from.scale) * eased,
				x: from.x + (target.x - from.x) * eased,
				y: from.y + (target.y - from.y) * eased,
			});
			animation = t < 1 ? requestAnimationFrame(step) : 0;
			if (animation === 0) {
				animationTarget = undefined;
			}
		};
		animation = requestAnimationFrame(step);
	}

	/** Settle a state: snap to fit near scale 1, otherwise keep the image in the container. */
	function settled(next: PanZoomState): PanZoomState {
		if (fit.value === undefined || !isZoomed(next)) {
			return { ...IDENTITY };
		}
		return clampPan(next, fit.value, containerSize.value, false);
	}

	function measure() {
		const container = options.container.value;
		const image = options.image.value;
		if (container === null) {
			return;
		}
		const size = { width: container.clientWidth, height: container.clientHeight };
		const resized = size.width !== containerSize.value.width || size.height !== containerSize.value.height;
		containerSize.value = size;
		containerRect = container.getBoundingClientRect();
		if (resized && zoomed.value) {
			stopAnimation();
			commit({ ...IDENTITY });
			options.onContainerResize();
		}
		fit.value =
			image === null || image.offsetWidth === 0
				? undefined
				: { left: image.offsetLeft, top: image.offsetTop, width: image.offsetWidth, height: image.offsetHeight };
	}

	function toContainer(event: { clientX: number; clientY: number }, rect: DOMRect | undefined): Point {
		return { x: event.clientX - (rect?.left ?? 0), y: event.clientY - (rect?.top ?? 0) };
	}

	function canZoom(): boolean {
		return options.zoomable() && fit.value !== undefined;
	}

	function zoomTo(point: Point, scale: number) {
		if (!canZoom()) {
			return;
		}
		animateTo(settled(zoomAround(animationTarget ?? state.value, point, scale, ceiling.value)));
	}

	function centre(): Point {
		return { x: containerSize.value.width / 2, y: containerSize.value.height / 2 };
	}

	function reset() {
		animateTo({ ...IDENTITY });
	}

	function toggleAt(point: Point) {
		if (isZoomed(animationTarget ?? state.value)) {
			reset();
			return;
		}
		zoomTo(point, CLICK_ZOOM);
	}

	function toggle() {
		toggleAt(centre());
	}

	function zoomIn() {
		zoomTo(centre(), (animationTarget ?? state.value).scale * KEY_ZOOM_STEP);
	}

	function zoomOut() {
		zoomTo(centre(), (animationTarget ?? state.value).scale / KEY_ZOOM_STEP);
	}

	/** Centre the viewport on a normalised photo point (minimap, FR-078-18). */
	function centreOnPhoto(point: Point) {
		if (fit.value === undefined || !zoomed.value) {
			return;
		}
		stopAnimation();
		schedule(centreOn(state.value, fit.value, containerSize.value, point));
	}

	function startGesture(point: Point) {
		gesture = {
			start: point,
			startState: { ...state.value },
			startedZoomed: zoomed.value,
			maxPointers: 1,
			moved: false,
			pinchDistance: 0,
			pinchMidpoint: point,
		};
		lastGesture = gesture;
	}

	function startPinch() {
		if (gesture === undefined) {
			return;
		}
		const [a, b] = [...pointers.values()];
		gesture.moved = true;
		gesture.startState = { ...state.value };
		gesture.pinchDistance = distance(a, b);
		gesture.pinchMidpoint = midpoint(a, b);
	}

	function onPointerDown(event: PointerEvent) {
		if (event.pointerType === "mouse" && event.button !== 0) {
			return;
		}
		stopAnimation();
		containerRect = options.container.value?.getBoundingClientRect();
		const point = toContainer(event, containerRect);
		pointers.set(event.pointerId, point);
		if (pointers.size === 1) {
			startGesture(point);
			return;
		}
		if (gesture !== undefined) {
			gesture.maxPointers = Math.max(gesture.maxPointers, pointers.size);
		}
		if (pointers.size === 2 && canZoom()) {
			startPinch();
		}
	}

	function pinch() {
		if (gesture === undefined || fit.value === undefined) {
			return;
		}
		const [a, b] = [...pointers.values()];
		const mid = midpoint(a, b);
		const ratio = gesture.pinchDistance > 0 ? distance(a, b) / gesture.pinchDistance : 1;
		const zoomedState = zoomAround(gesture.startState, gesture.pinchMidpoint, gesture.startState.scale * ratio, ceiling.value);
		const moved = { ...zoomedState, x: zoomedState.x + mid.x - gesture.pinchMidpoint.x, y: zoomedState.y + mid.y - gesture.pinchMidpoint.y };
		schedule(clampPan(moved, fit.value, containerSize.value, true));
	}

	function pan(point: Point) {
		if (gesture === undefined || fit.value === undefined) {
			return;
		}
		const next = {
			...gesture.startState,
			x: gesture.startState.x + point.x - gesture.start.x,
			y: gesture.startState.y + point.y - gesture.start.y,
		};
		schedule(clampPan(next, fit.value, containerSize.value, true));
	}

	function updateHover(event: PointerEvent) {
		if (event.pointerType !== "mouse") {
			return;
		}
		hoverPoint.value = toContainer(event, containerRect);
	}

	function onPointerMove(event: PointerEvent) {
		if (!pointers.has(event.pointerId)) {
			updateHover(event);
			return;
		}
		const point = toContainer(event, containerRect);
		pointers.set(event.pointerId, point);
		if (gesture === undefined) {
			return;
		}
		if (pointers.size >= 2 && canZoom()) {
			pinch();
			return;
		}
		if (!gesture.moved && !isTap(gesture.start, point, 1)) {
			gesture.moved = true;
			options.container.value?.setPointerCapture(event.pointerId);
		}
		if (gesture.moved && gesture.startedZoomed) {
			isPanning.value = true;
			pan(point);
		}
	}

	function endGesture() {
		const finished = gesture;
		gesture = undefined;
		isPanning.value = false;
		if (finished === undefined) {
			return;
		}
		if (finished.moved || finished.maxPointers > 1) {
			suppressClicksUntil = performance.now() + CLICK_SUPPRESS_MS;
		}
		if (finished.startedZoomed || finished.maxPointers > 1) {
			flush();
			animateTo(settled(state.value));
		}
	}

	/** Swipe navigation only applies at fit to one-finger gestures that started at fit (FR-078-09). */
	function isSwipeBlocked(): boolean {
		return zoomed.value || (lastGesture !== undefined && (lastGesture.startedZoomed || lastGesture.maxPointers > 1));
	}

	function onPointerUp(event: PointerEvent) {
		if (!pointers.has(event.pointerId)) {
			return;
		}
		pointers.delete(event.pointerId);
		if (pointers.size === 0) {
			endGesture();
			return;
		}
		// One finger left after a pinch: keep panning from where it is, never a tap or swipe.
		if (gesture !== undefined) {
			const [remaining] = [...pointers.values()];
			flush();
			gesture.start = remaining;
			gesture.startState = { ...state.value };
			gesture.startedZoomed = zoomed.value;
		}
	}

	function onClickCapture(event: MouseEvent) {
		if (performance.now() < suppressClicksUntil) {
			event.stopPropagation();
			event.preventDefault();
		}
	}

	function onClick(event: MouseEvent) {
		options.onTap(toContainer(event, options.container.value?.getBoundingClientRect()));
	}

	function onWheel(event: WheelEvent) {
		if (!canZoom()) {
			return;
		}
		if (!event.ctrlKey && !zoomed.value && options.wheelNavigates()) {
			return;
		}
		event.preventDefault();
		event.stopPropagation();
		stopAnimation();
		const point = toContainer(event, options.container.value?.getBoundingClientRect());
		// Trackpad pinch sends small ctrl+wheel deltas; a ctrl+mouse-wheel notch (~100 px) is capped so it steps ~1.3×.
		const delta = wheelDelta(event, containerSize.value.height);
		const step = event.ctrlKey ? Math.max(-CTRL_WHEEL_MAX_DELTA, Math.min(CTRL_WHEEL_MAX_DELTA, delta)) * 0.01 : delta * 0.002;
		const base = pendingState ?? state.value;
		const scale = base.scale * Math.exp(-step);
		schedule(settled(zoomAround(base, point, scale, ceiling.value)));
	}

	onMounted(() => {
		const container = options.container.value;
		if (container === null) {
			return;
		}
		container.addEventListener("pointerdown", onPointerDown);
		container.addEventListener("pointermove", onPointerMove);
		container.addEventListener("pointerup", onPointerUp);
		container.addEventListener("pointercancel", onPointerUp);
		container.addEventListener("click", onClickCapture, { capture: true });
		container.addEventListener("click", onClick);
		container.addEventListener("wheel", onWheel, { passive: false });
		container.addEventListener("pointerenter", () => (containerRect = container.getBoundingClientRect()));
		container.addEventListener("pointerleave", () => (hoverPoint.value = undefined));
	});

	onUnmounted(() => {
		stopAnimation();
		if (frame !== 0) {
			cancelAnimationFrame(frame);
		}
	});

	return {
		state: readonly(state),
		fit: readonly(fit),
		containerSize: readonly(containerSize),
		zoomed,
		isPanning: readonly(isPanning),
		hoverPoint: readonly(hoverPoint),
		activity: readonly(activity),
		measure,
		toggleAt,
		toggle,
		zoomIn,
		zoomOut,
		reset,
		centreOnPhoto,
		isSwipeBlocked,
	};
}
