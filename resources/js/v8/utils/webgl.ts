let webgl2Supported: boolean | undefined = undefined;

/**
 * Whether this browser can create a WebGL2 context (Feature 082, FR-082-15).
 * Probed once; the probe context is released right away.
 */
export function isWebGL2Supported(): boolean {
	if (webgl2Supported !== undefined) {
		return webgl2Supported;
	}
	try {
		const gl = document.createElement("canvas").getContext("webgl2");
		webgl2Supported = gl !== null;
		gl?.getExtension("WEBGL_lose_context")?.loseContext();
	} catch {
		webgl2Supported = false;
	}
	return webgl2Supported;
}
