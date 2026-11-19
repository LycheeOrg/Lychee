import { Ref } from "vue";

/**
 * Photo cycle of the v8 Frame: curtain down, fetch the next photo, curtain up,
 * wait the refresh interval, repeat.
 *
 * Forked from the shared `useSlideshowFunction` for the Frame only: that one
 * clears every timeout of the page between slides, which would also stop the
 * clock box's minute timer (Feature 088, NFR-088-05). This one clears only the
 * timeouts it set. The Frame never shows videos, so there is no video handling.
 */
export function useFrameSlideshow(delay: number, refreshSeconds: Ref<number>, getNext: () => void) {
	const timeouts = new Set<number>();

	function later(callback: () => void, ms: number) {
		const id = window.setTimeout(() => {
			timeouts.delete(id);
			callback();
		}, ms);
		timeouts.add(id);
	}

	function clearTimeouts() {
		timeouts.forEach((id) => window.clearTimeout(id));
		timeouts.clear();
	}

	function curtainDown() {
		document.getElementById("shutter")?.classList?.remove("opacity-0");
	}

	function curtainUp() {
		document.getElementById("shutter")?.classList?.add("opacity-0");
	}

	function next() {
		clearTimeouts();
		curtainDown();
		later(getNext, delay);
		later(curtainUp, delay * 2);
		later(next, delay * 2 + 1000 * refreshSeconds.value);
	}

	function start() {
		curtainDown();
		later(curtainUp, delay);
		later(next, delay + 1000 * refreshSeconds.value);
	}

	return { start, clearTimeouts };
}
