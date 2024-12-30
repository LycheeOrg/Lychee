import { Ref } from "vue";

export function useSlideshowFunction(
	delay: number,
	is_slideshow_active: Ref<boolean>,
	slide_show_interval: Ref<number>,
	getNext: () => void,
	getPrevious?: () => void,
) {
	function slideshow() {
		if (is_slideshow_active.value) {
			stop();
			return;
		}

		start();
	}

	function start() {
		is_slideshow_active.value = true;
		curtainDown();
		window.setTimeout(() => curtainUp(), delay); // We wait 500ms for the next photo to be loaded.
		window.setTimeout(() => next(), delay + 1000 * slide_show_interval.value); // Set timeout for next iteration.
	}

	function stop() {
		clearTimeouts();
		curtainUp();
		is_slideshow_active.value = false;
	}

	// We need to be able to clear all the timeouts so that the next and previous functions can be called without any issues.
	function clearTimeouts() {
		// https://stackoverflow.com/a/8860203
		var id = window.setTimeout(function () {}, 0);
		while (id--) {
			window.clearTimeout(id); // will do nothing if no timeout with id is present
		}
	}

	function curtainDown() {
		document.getElementById("shutter")?.classList?.remove("opacity-0");
	}

	function curtainUp() {
		document.getElementById("shutter")?.classList?.add("opacity-0");
	}

	function next(immediate: boolean = false) {
		if (!is_slideshow_active.value || immediate) {
			getNext();
			return;
		}

		clearTimeouts();
		curtainDown(); // takes 500ms
		window.setTimeout(() => getNext(), delay); // hence we wait 500ms for curtain to be down
		window.setTimeout(() => curtainUp(), delay * 2); // We wait 500ms for the next photo to be loaded.
		window.setTimeout(() => next(), delay * 2 + 1000 * slide_show_interval.value); // Set timeout for next iteration.
	}

	function previous(immediate: boolean = false) {
		if (getPrevious === undefined) {
			return;
		}

		if (!is_slideshow_active.value || immediate) {
			getPrevious();
			return;
		}

		clearTimeouts();
		curtainDown(); // takes 500ms
		window.setTimeout(() => getPrevious(), 500); // hence we wait 500ms for curtain to be down
		window.setTimeout(() => curtainUp(), 1000); // We wait 500ms for the next photo to be loaded.
		window.setTimeout(() => next(), 1000 * slide_show_interval.value); // Set timeout for next iteration.
	}

	return {
		slideshow,
		start,
		curtainDown,
		curtainUp,
		next,
		previous,
		clearTimeouts,
		stop,
	};
}
