/**
 * Camera and projection math for the 360° sphere view (Feature 082, NFR-082-03).
 *
 * Angles are in degrees. Longitude 0 is the horizontal centre of a full
 * equirectangular panorama and grows to the right (to +180° at the right
 * edge); latitude grows upwards (+90° at the top edge). The view looks at
 * (`yaw`, `pitch`) with a vertical field of view `fov`.
 *
 * Everything here is pure: `useSphereViewer` wires canvas, events and WebGL to
 * these functions, and `sphereShader.ts` applies the same mapping per pixel.
 */
import { CLICK_ZOOM, KEY_ZOOM_STEP, type ZoomSource } from "@/v8/utils/panZoom";

export { CLICK_ZOOM, KEY_ZOOM_STEP };

export type SphereView = { yaw: number; pitch: number; fov: number };

/** Part of the sphere covered by the image (FR-082-12). */
export type Coverage = {
	lonMin: number;
	lonMax: number;
	latMin: number;
	latMax: number;
	/** The image spans the full 360° horizontally and wraps around. */
	wraps: boolean;
	/** Share of the full panorama width covered by the image, in (0, 1]. */
	fraction: number;
};

export type PanoramaCrop = { full_width: number; full_height: number; crop_left: number; crop_top: number };

export const DEFAULT_FOV = 75;
export const MIN_FOV = 30;
export const MAX_FOV = 100;

export const FULL_COVERAGE: Coverage = { lonMin: -180, lonMax: 180, latMin: -90, latMax: 90, wraps: true, fraction: 1 };

const DEG = Math.PI / 180;

function clamp(value: number, min: number, max: number): number {
	return Math.min(max, Math.max(min, value));
}

/** Yaw in (-180°, 180°]. */
export function normaliseYaw(yaw: number): number {
	const wrapped = (((yaw + 180) % 360) + 360) % 360;
	return wrapped === 0 ? 180 : wrapped - 180;
}

/**
 * Coverage of an image of `width` × `height` pixels placed at `crop` in its full
 * panorama; a full sphere when `crop` is null.
 */
export function coverageOf(crop: PanoramaCrop | null, width: number, height: number): Coverage {
	if (crop === null || width <= 0 || height <= 0 || crop.full_width <= 0 || crop.full_height <= 0) {
		return FULL_COVERAGE;
	}
	const wraps = width >= crop.full_width;
	const latMax = clamp((0.5 - crop.crop_top / crop.full_height) * 180, -90, 90);
	const latMin = clamp((0.5 - (crop.crop_top + height) / crop.full_height) * 180, -90, 90);
	return {
		lonMin: wraps ? -180 : clamp((crop.crop_left / crop.full_width - 0.5) * 360, -180, 180),
		lonMax: wraps ? 180 : clamp(((crop.crop_left + width) / crop.full_width - 0.5) * 360, -180, 180),
		latMin,
		latMax,
		wraps,
		fraction: Math.min(1, width / crop.full_width),
	};
}

export function clampFov(fov: number): number {
	return clamp(fov, MIN_FOV, MAX_FOV);
}

/** Horizontal field of view for a vertical `fov` and a viewport `aspect` (width ÷ height). */
export function horizontalFov(fov: number, aspect: number): number {
	return (2 * Math.atan(aspect * Math.tan((fov * DEG) / 2))) / DEG;
}

/** Zoom factor relative to the default field of view (FR-082-11): 1 at 75°, 2 at about 42°. */
export function zoomFromFov(fov: number): number {
	return Math.tan((DEFAULT_FOV * DEG) / 2) / Math.tan((fov * DEG) / 2);
}

export function fovFromZoom(zoom: number): number {
	return clampFov((2 * Math.atan(Math.tan((DEFAULT_FOV * DEG) / 2) / zoom)) / DEG);
}

/** Zoomed in, i.e. narrower than the default field of view (rating keys off, `escape` resets). */
export function isSphereZoomed(view: SphereView): boolean {
	return view.fov < DEFAULT_FOV - 0.01;
}

/**
 * Keeps the view inside the covered area (FR-082-10, FR-082-12): on each axis
 * where the covered span is larger than the view, the view stays inside it;
 * where it is smaller, the view is centred on it. A full-height image allows
 * the view centre up to the poles.
 */
export function clampView(view: SphereView, coverage: Coverage, aspect: number): SphereView {
	const fov = clampFov(view.fov);
	return { yaw: clampYaw(view.yaw, coverage, horizontalFov(fov, aspect)), pitch: clampPitch(view.pitch, coverage, fov), fov };
}

function clampYaw(yaw: number, coverage: Coverage, hfov: number): number {
	if (coverage.wraps) {
		return normaliseYaw(yaw);
	}
	return clampAxis(yaw, coverage.lonMin, coverage.lonMax, hfov);
}

function clampPitch(pitch: number, coverage: Coverage, fov: number): number {
	if (coverage.latMin <= -90 && coverage.latMax >= 90) {
		return clamp(pitch, -90, 90);
	}
	return clampAxis(pitch, coverage.latMin, coverage.latMax, fov);
}

function clampAxis(value: number, min: number, max: number, span: number): number {
	if (max - min <= span) {
		return (min + max) / 2;
	}
	return clamp(value, min + span / 2, max - span / 2);
}

/** Initial view (FR-082-13): default field of view, centred on the covered area. */
export function initialView(coverage: Coverage, aspect: number): SphereView {
	const yaw = coverage.wraps ? 0 : (coverage.lonMin + coverage.lonMax) / 2;
	return clampView({ yaw, pitch: (coverage.latMin + coverage.latMax) / 2, fov: DEFAULT_FOV }, coverage, aspect);
}

/**
 * View after dragging by (`dx`, `dy`) CSS pixels from `start` (FR-082-10): the
 * image follows the pointer, one pixel being `fov ÷ viewportHeight` degrees.
 */
export function dragView(start: SphereView, dx: number, dy: number, viewportHeight: number): SphereView {
	const degreesPerPixel = viewportHeight > 0 ? start.fov / viewportHeight : 0;
	return { yaw: start.yaw - dx * degreesPerPixel, pitch: start.pitch + dy * degreesPerPixel, fov: start.fov };
}

/**
 * View zoomed to `zoom` (FR-082-11), keeping the direction under the
 * normalised viewport point (`nx`, `ny` in [-1, 1], `ny` up) where it is.
 */
export function zoomAround(view: SphereView, zoom: number, nx: number, ny: number, aspect: number): SphereView {
	const fov = fovFromZoom(zoom);
	const before = offsetAngles(view.fov, nx, ny, aspect);
	const after = offsetAngles(fov, nx, ny, aspect);
	return { yaw: view.yaw + before.x - after.x, pitch: view.pitch + before.y - after.y, fov };
}

/** Angles between the view centre and the normalised viewport point, for a vertical `fov`. */
function offsetAngles(fov: number, nx: number, ny: number, aspect: number): { x: number; y: number } {
	const tanVertical = Math.tan((fov * DEG) / 2);
	return { x: Math.atan(nx * aspect * tanVertical) / DEG, y: Math.atan(ny * tanVertical) / DEG };
}

/**
 * Texture width that shows the image at one texel per device pixel for this
 * view (FR-082-14), capped at the GPU limit.
 */
export function neededTextureWidth(
	containerWidth: number,
	devicePixelRatio: number,
	fov: number,
	aspect: number,
	coverage: Coverage,
	maxTextureSize: number,
): number {
	const pixelsPerDegree = (containerWidth * devicePixelRatio) / horizontalFov(fov, aspect);
	return Math.min(maxTextureSize, Math.ceil(pixelsPerDegree * 360 * coverage.fraction));
}

/**
 * Rotation from camera space (x right, y up, z forward) to sphere space, as a
 * column-major 3×3 matrix for `uniformMatrix3fv`: yaw around y, then pitch.
 */
export function rotationMatrix(view: SphereView): Float32Array {
	const cy = Math.cos(view.yaw * DEG);
	const sy = Math.sin(view.yaw * DEG);
	const cp = Math.cos(view.pitch * DEG);
	const sp = Math.sin(view.pitch * DEG);
	return new Float32Array([cy, 0, -sy, -sy * sp, cp, -cy * sp, sy * cp, sp, cy * cp]);
}

type SphereSourcePhoto = {
	precomputed: { is_raw: boolean };
	size_variants: {
		original: { url: string | null; width: number } | null;
		medium2x: { url: string | null; width: number } | null;
		medium: { url: string | null; width: number } | null;
		small2x: { url: string | null; width: number } | null;
		small: { url: string | null; width: number } | null;
	};
};

function usable(variant: { url: string | null; width: number } | null): ZoomSource | undefined {
	if (variant === null || variant.url === null || variant.url === "") {
		return undefined;
	}
	return { url: variant.url, width: variant.width };
}

/**
 * First texture of the sphere (FR-082-14): the medium variants the lightbox
 * already shows; for photos too small to have them, the original when it is
 * exposed and not RAW, else the small variants.
 */
export function pickSphereStartSource(photo: SphereSourcePhoto): ZoomSource | undefined {
	const variants = photo.size_variants;
	const original = photo.precomputed.is_raw ? undefined : usable(variants.original);
	return usable(variants.medium2x) ?? usable(variants.medium) ?? original ?? usable(variants.small2x) ?? usable(variants.small);
}
