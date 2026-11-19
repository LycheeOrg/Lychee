/**
 * Pan & zoom geometry for the v8 lightbox (Feature 078).
 *
 * Coordinates are CSS pixels relative to the top-left corner of `#imageview`
 * (the container). The zoom layer covers the container and is transformed by
 * `translate(x, y) scale(scale)` with `transform-origin: 0 0`, so a layer point
 * `p` is drawn at `p * scale + (x, y)`. At `scale = 1, x = y = 0` the image is
 * drawn at its fitted rectangle `fit`, which is what the lightbox shows today.
 *
 * Everything here is pure: the composable (`usePanZoom`) and the minimap only
 * wire DOM events to these functions.
 */

export type PanZoomState = { scale: number; x: number; y: number };
export type Point = { x: number; y: number };
export type Size = { width: number; height: number };
export type Rect = { left: number; top: number; width: number; height: number };

/** Normalised rectangle (0..1 on both axes) of the photo. */
export type NormalisedRect = { left: number; top: number; right: number; bottom: number };

export type HitTarget = "picture" | "overlay" | "none-zone" | "letterbox" | "nothing";

export type ZoomSource = { url: string; width: number };

export const IDENTITY: PanZoomState = { scale: 1, x: 0, y: 0 };

/** Scale reached by a click, a double-tap or the `z` key. */
export const CLICK_ZOOM = 2;
/** Step of the `+` / `-` keys. */
export const KEY_ZOOM_STEP = 1.5;
/** Maximum pointer travel (px) for a tap. */
export const TAP_SLOP = 5;
/** Double-tap window. */
export const DOUBLE_TAP_MS = 250;
export const DOUBLE_TAP_SLOP = 30;
/** Height (px) and width (fraction) of the hit zone that brings the overlay back from `none`. */
export const NONE_ZONE_HEIGHT = 96;
export const NONE_ZONE_WIDTH = 0.5;

/** Scales this close to 1 are treated as fit. */
const FIT_EPSILON = 0.01;

export function isZoomed(state: PanZoomState): boolean {
	return state.scale > 1 + FIT_EPSILON;
}

/**
 * Zoom ceiling: four times the source's native resolution, never below 4× fit (FR-078-03).
 */
export function maxScale(sourceWidth: number, fitWidth: number): number {
	if (fitWidth <= 0) {
		return 4;
	}
	return Math.max(4, (4 * sourceWidth) / fitWidth);
}

export function clampScale(scale: number, max: number): number {
	return Math.min(Math.max(scale, 1), max);
}

/**
 * Change the scale while keeping the layer point under `point` fixed on screen.
 */
export function zoomAround(state: PanZoomState, point: Point, scale: number, max: number): PanZoomState {
	const target = clampScale(scale, max);
	const ratio = target / state.scale;
	return {
		scale: target,
		x: point.x - (point.x - state.x) * ratio,
		y: point.y - (point.y - state.y) * ratio,
	};
}

/**
 * Resistance applied to a drag past the allowed range (iOS-like): grows slower
 * and slower, never beyond `dimension`.
 */
export function rubberBand(overshoot: number, dimension: number): number {
	if (dimension <= 0) {
		return 0;
	}
	const sign = Math.sign(overshoot);
	const distance = Math.abs(overshoot);
	return sign * dimension * (1 - 1 / ((distance * 0.55) / dimension + 1));
}

/**
 * Allowed offset on one axis: centred when the image is smaller than the
 * container, otherwise the image edges may not move inside the container.
 */
function axisRange(fitStart: number, fitLength: number, containerLength: number, scale: number): [number, number] {
	const length = fitLength * scale;
	if (length <= containerLength) {
		const centred = (containerLength - length) / 2 - fitStart * scale;
		return [centred, centred];
	}
	return [containerLength - (fitStart + fitLength) * scale, -fitStart * scale];
}

function clampAxis(value: number, range: [number, number], rubber: boolean, containerLength: number): number {
	const [min, max] = range;
	if (value < min) {
		return rubber ? min + rubberBand(value - min, containerLength) : min;
	}
	if (value > max) {
		return rubber ? max + rubberBand(value - max, containerLength) : max;
	}
	return value;
}

/**
 * Keep the image inside the container (FR-078-09). With `rubber`, an
 * out-of-range offset is damped instead of stopped (used while dragging).
 */
export function clampPan(state: PanZoomState, fit: Rect, container: Size, rubber: boolean): PanZoomState {
	return {
		scale: state.scale,
		x: clampAxis(state.x, axisRange(fit.left, fit.width, container.width, state.scale), rubber, container.width),
		y: clampAxis(state.y, axisRange(fit.top, fit.height, container.height, state.scale), rubber, container.height),
	};
}

export function distance(a: Point, b: Point): number {
	return Math.hypot(a.x - b.x, a.y - b.y);
}

export function midpoint(a: Point, b: Point): Point {
	return { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
}

/** A gesture is a tap when it used one pointer and barely moved (FR-078-07). */
/** Vertical wheel delta in pixels, whatever the event's delta mode (1 = `DOM_DELTA_LINE`, 2 = `DOM_DELTA_PAGE`). */
export function wheelDelta(event: { deltaY: number; deltaMode: number }, containerHeight: number): number {
	if (event.deltaMode === 1) {
		return event.deltaY * 16;
	}
	if (event.deltaMode === 2) {
		return event.deltaY * containerHeight;
	}
	return event.deltaY;
}

export function isTap(start: Point, end: Point, maxPointers: number): boolean {
	return maxPointers === 1 && distance(start, end) <= TAP_SLOP;
}

export type TapRecord = { time: number; point: Point };

export function isDoubleTap(previous: TapRecord | undefined, current: TapRecord): boolean {
	if (previous === undefined) {
		return false;
	}
	return current.time - previous.time <= DOUBLE_TAP_MS && distance(previous.point, current.point) <= DOUBLE_TAP_SLOP;
}

export function rectContains(rect: Rect, point: Point): boolean {
	return point.x >= rect.left && point.x <= rect.left + rect.width && point.y >= rect.top && point.y <= rect.top + rect.height;
}

/** Bottom-start box that stands in for the hidden overlay (FR-078-05). */
export function noneZone(container: Size, isLTR: boolean): Rect {
	const width = container.width * NONE_ZONE_WIDTH;
	return {
		left: isLTR ? 0 : container.width - width,
		top: container.height - NONE_ZONE_HEIGHT,
		width,
		height: NONE_ZONE_HEIGHT,
	};
}

export type HitTestInput = {
	point: Point;
	container: Size;
	/** Rendered (transformed) image rectangle. */
	image: Rect;
	/** Rendered overlay rectangle; `undefined` when no overlay is rendered. */
	overlay: Rect | undefined;
	isOverlayNone: boolean;
	isExifDisabled: boolean;
	isLTR: boolean;
};

/**
 * What a click lands on in `zoom` click mode (FR-078-05). All rectangles are
 * relative to the container.
 */
export function classifyHitTarget(input: HitTestInput): HitTarget {
	const onPicture = rectContains(input.image, input.point);
	if (input.isExifDisabled) {
		return onPicture ? "picture" : "nothing";
	}
	if (input.isOverlayNone && rectContains(noneZone(input.container, input.isLTR), input.point)) {
		return "none-zone";
	}
	if (input.overlay !== undefined && rectContains(input.overlay, input.point)) {
		return "overlay";
	}
	return onPicture ? "picture" : "letterbox";
}

/** Rendered image rectangle for a given state. */
export function imageRect(state: PanZoomState, fit: Rect): Rect {
	return {
		left: fit.left * state.scale + state.x,
		top: fit.top * state.scale + state.y,
		width: fit.width * state.scale,
		height: fit.height * state.scale,
	};
}

function clamp01(value: number): number {
	return Math.min(Math.max(value, 0), 1);
}

/** Part of the photo visible in the container, normalised to 0..1 (minimap rectangle). */
export function visibleRect(state: PanZoomState, fit: Rect, container: Size): NormalisedRect {
	const image = imageRect(state, fit);
	if (image.width <= 0 || image.height <= 0) {
		return { left: 0, top: 0, right: 1, bottom: 1 };
	}
	return {
		left: clamp01(-image.left / image.width),
		top: clamp01(-image.top / image.height),
		right: clamp01((container.width - image.left) / image.width),
		bottom: clamp01((container.height - image.top) / image.height),
	};
}

/** State that puts the normalised photo point `centre` in the middle of the container. */
export function centreOn(state: PanZoomState, fit: Rect, container: Size, centre: Point): PanZoomState {
	const width = fit.width * state.scale;
	const height = fit.height * state.scale;
	const imageLeft = container.width / 2 - centre.x * width;
	const imageTop = container.height / 2 - centre.y * height;
	return clampPan({ scale: state.scale, x: imageLeft - fit.left * state.scale, y: imageTop - fit.top * state.scale }, fit, container, false);
}

type ZoomSourcePhoto = {
	precomputed: { is_raw: boolean };
	size_variants: {
		original: { url: string | null; width: number } | null;
		medium2x: { url: string | null; width: number } | null;
		medium: { url: string | null; width: number } | null;
	};
};

function usable(variant: { url: string | null; width: number } | null): ZoomSource | undefined {
	if (variant === null || variant.url === null || variant.url === "") {
		return undefined;
	}
	return { url: variant.url, width: variant.width };
}

/**
 * Largest variant the viewer may see and the browser can display (FR-078-03):
 * the original only when its URL is exposed and the photo is not RAW (a RAW
 * photo's original is the RAW/PDF file itself).
 */
export function pickZoomSource(photo: ZoomSourcePhoto): ZoomSource | undefined {
	const original = photo.precomputed.is_raw ? undefined : usable(photo.size_variants.original);
	return original ?? usable(photo.size_variants.medium2x) ?? usable(photo.size_variants.medium);
}
