import { computed, onMounted, onUnmounted, readonly, ref, watch, type Ref, type WatchStopHandle } from "vue";
import { distance, isTap, midpoint, wheelDelta, type Point, type ZoomSource } from "@/v8/utils/panZoom";
import {
	CLICK_ZOOM,
	DEFAULT_FOV,
	KEY_ZOOM_STEP,
	clampView,
	dragView,
	fovFromZoom,
	initialView,
	isSphereZoomed,
	neededTextureWidth,
	rotationMatrix,
	zoomAround,
	zoomFromFov,
	type Coverage,
	type SphereView,
} from "@/v8/utils/sphere";
import { FRAGMENT_SHADER, VERTEX_SHADER } from "@/v8/utils/sphereShader";

/**
 * 360° sphere renderer for the v8 lightbox (Feature 082, ADR-082-01).
 *
 * Owns the canvas: WebGL2 context, program and texture, Pointer Events (drag
 * to look around, pinch to zoom), wheel zoom, keyboard zoom controls, context
 * loss and disposal. Gestures stop at the canvas so `usePanZoom` and the
 * lightbox wheel navigation never see them; a plain tap is left to bubble as a
 * regular `click`, and the click that follows a drag is swallowed. Drawing is
 * on demand, at most once per animation frame (NFR-082-04). The geometry lives
 * in `utils/sphere.ts`.
 */
export type SphereViewerOptions = {
	canvas: Ref<HTMLCanvasElement | null>;
	/** Part of the sphere covered by the photo (FR-082-12). */
	coverage: () => Coverage;
	/**
	 * First texture (FR-082-14). Reactive: a photo synthesised from a Struct-of-Arrays
	 * listing has no size variants (nor crop) until its details are merged.
	 */
	startSource: () => ZoomSource | undefined;
	/** Largest source the viewer may see, loaded when the view needs it (FR-082-14). */
	upgradeSource: () => ZoomSource | undefined;
	/** WebGL2 or the first texture failed (FR-082-15). */
	onUnavailable: (reason: string) => void;
};

type Gesture = {
	start: Point;
	startView: SphereView;
	moved: boolean;
	maxPointers: number;
	pinchDistance: number;
	pinchMidpoint: Point;
};

type Uniforms = {
	texture: WebGLUniformLocation | null;
	rotation: WebGLUniformLocation | null;
	tanHalf: WebGLUniformLocation | null;
	coverage: WebGLUniformLocation | null;
	background: WebGLUniformLocation | null;
};

const DEG = Math.PI / 180;
const ANIMATION_MS = 200;
const CTRL_WHEEL_MAX_DELTA = 25;
/** A click within this delay after a drag ends belongs to that drag. */
const CLICK_SUPPRESS_MS = 400;

function prefersReducedMotion(): boolean {
	return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

async function decode(url: string, maxTextureSize: number): Promise<ImageBitmap> {
	const image = new Image();
	image.crossOrigin = "anonymous";
	image.src = url;
	await image.decode();
	const scale = Math.min(1, maxTextureSize / image.naturalWidth, maxTextureSize / image.naturalHeight);
	if (scale >= 1) {
		return createImageBitmap(image);
	}
	return createImageBitmap(image, {
		resizeWidth: Math.max(1, Math.floor(image.naturalWidth * scale)),
		resizeHeight: Math.max(1, Math.floor(image.naturalHeight * scale)),
		resizeQuality: "high",
	});
}

function compile(gl: WebGL2RenderingContext, type: number, source: string): WebGLShader | null {
	const shader = gl.createShader(type);
	if (shader === null) {
		return null;
	}
	gl.shaderSource(shader, source);
	gl.compileShader(shader);
	if (gl.getShaderParameter(shader, gl.COMPILE_STATUS) !== true) {
		console.warn(gl.getShaderInfoLog(shader));
		gl.deleteShader(shader);
		return null;
	}
	return shader;
}

function link(gl: WebGL2RenderingContext): WebGLProgram | null {
	const vertex = compile(gl, gl.VERTEX_SHADER, VERTEX_SHADER);
	const fragment = compile(gl, gl.FRAGMENT_SHADER, FRAGMENT_SHADER);
	const program = gl.createProgram();
	if (vertex === null || fragment === null || program === null) {
		return null;
	}
	gl.attachShader(program, vertex);
	gl.attachShader(program, fragment);
	gl.linkProgram(program);
	gl.deleteShader(vertex);
	gl.deleteShader(fragment);
	if (gl.getProgramParameter(program, gl.LINK_STATUS) !== true) {
		console.warn(gl.getProgramInfoLog(program));
		gl.deleteProgram(program);
		return null;
	}
	return program;
}

export function useSphereViewer(options: SphereViewerOptions) {
	const view = ref<SphereView>({ yaw: 0, pitch: 0, fov: DEFAULT_FOV });
	const isDragging = ref(false);
	const zoomed = computed(() => isSphereZoomed(view.value));

	let gl: WebGL2RenderingContext | null = null;
	let program: WebGLProgram | null = null;
	let texture: WebGLTexture | null = null;
	let vao: WebGLVertexArrayObject | null = null;
	let uniforms: Uniforms | undefined = undefined;
	let maxTextureSize = 4096;
	let lost = false;
	let disposed = false;

	let size = { width: 0, height: 0 };
	let hasInitialView = false;
	let frame = 0;
	let resizeObserver: ResizeObserver | undefined = undefined;

	/** Source currently on the GPU, reloaded after a context loss. */
	let currentSource: ZoomSource | undefined = undefined;
	let textureWidth = 0;
	let upgradeStarted = false;

	const pointers = new Map<number, Point>();
	let gesture: Gesture | undefined = undefined;
	let canvasRect: DOMRect | undefined = undefined;
	let suppressClicksUntil = 0;

	let animation = 0;
	/** Field of view the running animation ends at: repeated key presses build on it. */
	let animationTarget: number | undefined = undefined;

	function aspect(): number {
		return size.height > 0 ? size.width / size.height : 1;
	}

	function fail(reason: string) {
		if (disposed) {
			return;
		}
		console.warn(`360° view unavailable: ${reason}`);
		options.onUnavailable(reason);
	}

	// ---- WebGL ----

	function setUp(): boolean {
		if (gl === null) {
			return false;
		}
		program = link(gl);
		texture = gl.createTexture();
		vao = gl.createVertexArray();
		if (program === null || texture === null || vao === null) {
			return false;
		}
		uniforms = {
			texture: gl.getUniformLocation(program, "u_texture"),
			rotation: gl.getUniformLocation(program, "u_rotation"),
			tanHalf: gl.getUniformLocation(program, "u_tan_half"),
			coverage: gl.getUniformLocation(program, "u_coverage"),
			background: gl.getUniformLocation(program, "u_background"),
		};
		maxTextureSize = gl.getParameter(gl.MAX_TEXTURE_SIZE) as number;
		return true;
	}

	function upload(bitmap: ImageBitmap): boolean {
		if (gl === null || texture === null) {
			return false;
		}
		gl.bindTexture(gl.TEXTURE_2D, texture);
		gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false);
		gl.pixelStorei(gl.UNPACK_PREMULTIPLY_ALPHA_WEBGL, false);
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, bitmap);
		gl.generateMipmap(gl.TEXTURE_2D);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, options.coverage().wraps ? gl.REPEAT : gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR_MIPMAP_LINEAR);
		gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
		const anisotropic = gl.getExtension("EXT_texture_filter_anisotropic");
		if (anisotropic !== null) {
			gl.texParameterf(
				gl.TEXTURE_2D,
				anisotropic.TEXTURE_MAX_ANISOTROPY_EXT,
				gl.getParameter(anisotropic.MAX_TEXTURE_MAX_ANISOTROPY_EXT) as number,
			);
		}
		return gl.getError() === gl.NO_ERROR;
	}

	/** Loads `source` into the texture; resolves false when it could not be decoded or uploaded. */
	async function load(source: ZoomSource): Promise<boolean> {
		let bitmap: ImageBitmap;
		try {
			bitmap = await decode(source.url, maxTextureSize);
		} catch {
			return false;
		}
		if (disposed || lost) {
			bitmap.close();
			return true;
		}
		const uploaded = upload(bitmap);
		if (uploaded) {
			currentSource = source;
			textureWidth = bitmap.width;
		}
		bitmap.close();
		ensureInitialView();
		requestDraw();
		return uploaded;
	}

	let startSourceRequested = false;
	let stopStartSourceWatch: WatchStopHandle | undefined = undefined;

	function loadStartSource(source: ZoomSource | undefined) {
		if (source === undefined || startSourceRequested) {
			return;
		}
		startSourceRequested = true;
		load(source).then((loaded) => {
			if (!loaded) {
				fail("texture");
				return;
			}
			maybeUpgrade();
		});
	}

	/** FR-082-14: the original only when the view needs more pixels than the current texture has. */
	function maybeUpgrade() {
		const source = options.upgradeSource();
		if (disposed || upgradeStarted || textureWidth === 0 || size.width === 0 || source === undefined || source.width <= textureWidth) {
			return;
		}
		const needed = neededTextureWidth(size.width, window.devicePixelRatio || 1, view.value.fov, aspect(), options.coverage(), maxTextureSize);
		if (needed <= textureWidth) {
			return;
		}
		upgradeStarted = true;
		// A failed upgrade keeps the current texture.
		load(source);
	}

	function draw() {
		frame = 0;
		const canvas = options.canvas.value;
		if (gl === null || lost || disposed || canvas === null || uniforms === undefined || textureWidth === 0) {
			return;
		}
		const current = view.value;
		const coverage = options.coverage();
		const tanVertical = Math.tan((current.fov * DEG) / 2);
		gl.viewport(0, 0, canvas.width, canvas.height);
		gl.useProgram(program);
		gl.bindVertexArray(vao);
		gl.uniformMatrix3fv(uniforms.rotation, false, rotationMatrix(current));
		gl.uniform2f(uniforms.tanHalf, aspect() * tanVertical, tanVertical);
		gl.uniform4f(
			uniforms.coverage,
			coverage.lonMin * DEG,
			(coverage.lonMax - coverage.lonMin) * DEG,
			coverage.latMax * DEG,
			(coverage.latMax - coverage.latMin) * DEG,
		);
		gl.uniform3f(uniforms.background, 0, 0, 0);
		gl.activeTexture(gl.TEXTURE0);
		gl.bindTexture(gl.TEXTURE_2D, texture);
		gl.uniform1i(uniforms.texture, 0);
		gl.drawArrays(gl.TRIANGLES, 0, 3);
	}

	function requestDraw() {
		if (frame === 0 && !disposed) {
			frame = requestAnimationFrame(draw);
		}
	}

	function setView(next: SphereView) {
		view.value = clampView(next, options.coverage(), aspect());
		requestDraw();
		maybeUpgrade();
	}

	function resize() {
		const canvas = options.canvas.value;
		if (canvas === null || canvas.clientWidth === 0 || canvas.clientHeight === 0) {
			return;
		}
		size = { width: canvas.clientWidth, height: canvas.clientHeight };
		const ratio = window.devicePixelRatio || 1;
		canvas.width = Math.round(size.width * ratio);
		canvas.height = Math.round(size.height * ratio);
		ensureInitialView();
		setView(view.value);
	}

	/** FR-082-13, once both the canvas size and the photo (crop included) are known. */
	function ensureInitialView() {
		if (hasInitialView || size.width === 0 || textureWidth === 0) {
			return;
		}
		hasInitialView = true;
		view.value = initialView(options.coverage(), aspect());
	}

	// ---- Keyboard zoom (FR-082-11) ----

	function stopAnimation() {
		animationTarget = undefined;
		if (animation !== 0) {
			cancelAnimationFrame(animation);
			animation = 0;
		}
	}

	function animateFov(target: number) {
		stopAnimation();
		if (prefersReducedMotion()) {
			setView({ ...view.value, fov: target });
			return;
		}
		const from = view.value.fov;
		const startTime = performance.now();
		animationTarget = target;
		const step = (now: number) => {
			const t = Math.min((now - startTime) / ANIMATION_MS, 1);
			const eased = 1 - Math.pow(1 - t, 3);
			setView({ ...view.value, fov: from + (target - from) * eased });
			animation = t < 1 ? requestAnimationFrame(step) : 0;
			if (animation === 0) {
				animationTarget = undefined;
			}
		};
		animation = requestAnimationFrame(step);
	}

	function targetFov(): number {
		return animationTarget ?? view.value.fov;
	}

	function reset() {
		animateFov(DEFAULT_FOV);
	}

	function toggle() {
		if (isSphereZoomed({ ...view.value, fov: targetFov() })) {
			reset();
			return;
		}
		animateFov(fovFromZoom(CLICK_ZOOM));
	}

	function zoomIn() {
		animateFov(fovFromZoom(zoomFromFov(targetFov()) * KEY_ZOOM_STEP));
	}

	function zoomOut() {
		animateFov(fovFromZoom(zoomFromFov(targetFov()) / KEY_ZOOM_STEP));
	}

	// ---- Pointer gestures (FR-082-10, FR-082-11) ----

	function toCanvas(event: { clientX: number; clientY: number }): Point {
		return { x: event.clientX - (canvasRect?.left ?? 0), y: event.clientY - (canvasRect?.top ?? 0) };
	}

	/** Normalised viewport coordinates, y up. */
	function normalised(point: Point): Point {
		return { x: (point.x / size.width) * 2 - 1, y: 1 - (point.y / size.height) * 2 };
	}

	function startPinch() {
		if (gesture === undefined) {
			return;
		}
		const [a, b] = [...pointers.values()];
		gesture.moved = true;
		gesture.startView = { ...view.value };
		gesture.pinchDistance = distance(a, b);
		gesture.pinchMidpoint = midpoint(a, b);
	}

	function pinch() {
		if (gesture === undefined) {
			return;
		}
		const [a, b] = [...pointers.values()];
		const ratio = gesture.pinchDistance > 0 ? distance(a, b) / gesture.pinchDistance : 1;
		const centre = normalised(gesture.pinchMidpoint);
		setView(zoomAround(gesture.startView, zoomFromFov(gesture.startView.fov) * ratio, centre.x, centre.y, aspect()));
	}

	function onPointerDown(event: PointerEvent) {
		if (event.pointerType === "mouse" && event.button !== 0) {
			return;
		}
		event.stopPropagation();
		stopAnimation();
		canvasRect = options.canvas.value?.getBoundingClientRect();
		options.canvas.value?.setPointerCapture(event.pointerId);
		const point = toCanvas(event);
		pointers.set(event.pointerId, point);
		if (pointers.size === 1) {
			gesture = { start: point, startView: { ...view.value }, moved: false, maxPointers: 1, pinchDistance: 0, pinchMidpoint: point };
			return;
		}
		if (gesture !== undefined) {
			gesture.maxPointers = Math.max(gesture.maxPointers, pointers.size);
		}
		if (pointers.size === 2) {
			startPinch();
		}
	}

	function onPointerMove(event: PointerEvent) {
		if (!pointers.has(event.pointerId)) {
			return;
		}
		event.stopPropagation();
		const point = toCanvas(event);
		pointers.set(event.pointerId, point);
		if (gesture === undefined) {
			return;
		}
		if (pointers.size >= 2) {
			pinch();
			return;
		}
		if (!gesture.moved && !isTap(gesture.start, point, 1)) {
			gesture.moved = true;
		}
		if (gesture.moved) {
			isDragging.value = true;
			setView(dragView(gesture.startView, point.x - gesture.start.x, point.y - gesture.start.y, size.height));
		}
	}

	function onPointerUp(event: PointerEvent) {
		if (!pointers.has(event.pointerId)) {
			return;
		}
		event.stopPropagation();
		pointers.delete(event.pointerId);
		if (pointers.size > 0) {
			// One finger left after a pinch: keep dragging from where it is.
			if (gesture !== undefined) {
				const [remaining] = [...pointers.values()];
				gesture.start = remaining;
				gesture.startView = { ...view.value };
			}
			return;
		}
		if (gesture !== undefined && (gesture.moved || gesture.maxPointers > 1)) {
			suppressClicksUntil = performance.now() + CLICK_SUPPRESS_MS;
		}
		gesture = undefined;
		isDragging.value = false;
	}

	function onClickCapture(event: MouseEvent) {
		if (performance.now() < suppressClicksUntil) {
			event.stopPropagation();
			event.preventDefault();
		}
	}

	function onWheel(event: WheelEvent) {
		event.preventDefault();
		event.stopPropagation();
		stopAnimation();
		canvasRect = options.canvas.value?.getBoundingClientRect();
		// Trackpad pinch sends small ctrl+wheel deltas; a ctrl+mouse-wheel notch is capped like Feature 078.
		const delta = wheelDelta(event, size.height);
		const step = event.ctrlKey ? Math.max(-CTRL_WHEEL_MAX_DELTA, Math.min(CTRL_WHEEL_MAX_DELTA, delta)) * 0.01 : delta * 0.002;
		const cursor = normalised(toCanvas(event));
		setView(zoomAround(view.value, zoomFromFov(view.value.fov) * Math.exp(-step), cursor.x, cursor.y, aspect()));
	}

	// ---- Context loss (FR-082-15) ----

	function onContextLost(event: Event) {
		event.preventDefault();
		lost = true;
		if (frame !== 0) {
			cancelAnimationFrame(frame);
			frame = 0;
		}
	}

	function onContextRestored() {
		lost = false;
		textureWidth = 0;
		if (!setUp()) {
			fail("WebGL2 could not be restored");
			return;
		}
		const source = currentSource ?? options.startSource();
		if (source !== undefined) {
			load(source);
		}
	}

	// ---- Lifecycle (FR-082-16) ----

	const listeners: [string, EventListener, AddEventListenerOptions | boolean][] = [
		["pointerdown", onPointerDown as EventListener, false],
		["pointermove", onPointerMove as EventListener, false],
		["pointerup", onPointerUp as EventListener, false],
		["pointercancel", onPointerUp as EventListener, false],
		["click", onClickCapture as EventListener, { capture: true }],
		["wheel", onWheel as EventListener, { passive: false }],
		["webglcontextlost", onContextLost, false],
		["webglcontextrestored", onContextRestored, false],
	];

	function dispose() {
		disposed = true;
		stopAnimation();
		if (frame !== 0) {
			cancelAnimationFrame(frame);
			frame = 0;
		}
		resizeObserver?.disconnect();
		stopStartSourceWatch?.();
		const canvas = options.canvas.value;
		listeners.forEach(([type, listener, listenerOptions]) => canvas?.removeEventListener(type, listener, listenerOptions));
		if (gl !== null && !lost) {
			gl.deleteTexture(texture);
			gl.deleteProgram(program);
			gl.deleteVertexArray(vao);
			gl.getExtension("WEBGL_lose_context")?.loseContext();
		}
		gl = null;
	}

	onMounted(() => {
		const canvas = options.canvas.value;
		if (canvas === null) {
			return;
		}
		gl = canvas.getContext("webgl2", { alpha: false, antialias: false, depth: false, stencil: false });
		if (gl === null || !setUp()) {
			fail("WebGL2");
			return;
		}
		listeners.forEach(([type, listener, listenerOptions]) => canvas.addEventListener(type, listener, listenerOptions));
		resizeObserver = new ResizeObserver(resize);
		resizeObserver.observe(canvas);
		resize();
		stopStartSourceWatch = watch(() => options.startSource(), loadStartSource, { immediate: true });
	});

	onUnmounted(dispose);

	return {
		view: readonly(view),
		zoomed,
		isDragging: readonly(isDragging),
		toggle,
		zoomIn,
		zoomOut,
		reset,
	};
}
