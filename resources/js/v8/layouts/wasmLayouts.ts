import init, { justified, masonry, grid, square } from "@lychee-org/layouts";

let readyPromise: Promise<unknown> | undefined;

/**
 * The layout functions require the WASM module to be instantiated first.
 * Idempotent: subsequent calls reuse the same in-flight/resolved promise.
 */
export function initLayouts(): Promise<unknown> {
	readyPromise ??= init();
	return readyPromise;
}

export { justified, masonry, grid, square };
