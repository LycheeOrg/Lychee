import init, { justified, masonry, grid, square } from "@lychee-org/layouts";
import wasmBase64 from "@lychee-org/layouts/layouts_bg.wasm";

function base64ToBytes(base64: string): Uint8Array {
	const binary = atob(base64);
	const bytes = new Uint8Array(binary.length);
	for (let i = 0; i < binary.length; i++) {
		bytes[i] = binary.charCodeAt(i);
	}
	return bytes;
}

let readyPromise: Promise<unknown> | undefined;

/**
 * The layout functions require the WASM module to be instantiated first.
 * Idempotent: subsequent calls reuse the same in-flight/resolved promise.
 *
 * Passed the wasm bytes directly (inlined as base64 by vite.embed.config.ts)
 * rather than letting the package fetch its default `import.meta.url`-relative
 * path, which doesn't resolve in this bundle's UMD output format.
 */
export function initLayouts(): Promise<unknown> {
	readyPromise ??= init({ module_or_path: base64ToBytes(wasmBase64) });
	return readyPromise;
}

export { justified, masonry, grid, square };
