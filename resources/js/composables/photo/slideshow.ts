import { Ref } from "vue";

export function useSlideshowFunction(
	delay: number,
	is_slideshow_active: Ref<boolean>,
	slide_show_interval: Ref<number>,
	videoElement: Ref<HTMLVideoElement | null>,
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
		startSlideShow();
	}

	function stop() {
		clearTimeouts();
		curtainUp();
		removeVideoElementListeners();
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

	function removeVideoElementListeners() {
		videoElement.value?.removeEventListener("ended", videoEndedEventListener);
	}

	function videoEndedEventListener(event: Event) {
		console.log("Video ended");
		curtainDown();
		window.setTimeout(() => {
			removeVideoElementListeners();
			getNext();
		}, delay);
		window.setTimeout(() => curtainUp(), delay * 2); // We wait 500ms for the next photo to be loaded.
		window.setTimeout(() => next(), delay * 2 + 1000 * slide_show_interval.value); // Set timeout for next iteration.
	}

	function curtainDown() {
		document.getElementById("shutter")?.classList?.remove("opacity-0");
	}

	function curtainUp() {
		document.getElementById("shutter")?.classList?.add("opacity-0");
	}

	function next(immediate: boolean = false) {
		removeVideoElementListeners();

		if (immediate && is_slideshow_active.value == false) {
			console.log("Immediate or not running slideshow");
			getNext();
			return;
		}

		if (immediate && is_slideshow_active.value == true) {
			console.log("Immediate and running slideshow");
			clearTimeouts();
			getNext();
			window.setTimeout(() => next(), delay * 2 + 1000 * slide_show_interval.value); // Set timeout for next iteration.
			return;
		}

		if (!immediate && is_slideshow_active.value == true) {
			if (videoElement.value && !videoElement.value.ended) {
				console.log("Not immediate but running slideshow and video not ended");
				clearTimeouts();
				videoElement.value?.addEventListener("ended", videoEndedEventListener);

				return;
			}
			console.log("Not immediate but running slideshow");
			clearTimeouts();
			continueSlideShow();
		}
	}

	function startSlideShow() {
		curtainDown();
		window.setTimeout(() => curtainUp(), delay); // We wait 500ms for the next photo to be loaded.
		window.setTimeout(() => next(), delay + 1000 * slide_show_interval.value); // Set timeout for next iteration.
	}

	function continueSlideShow() {
		curtainDown(); // takes 500ms
		window.setTimeout(() => getNext(), delay); // hence we wait 500ms for curtain to be down
		window.setTimeout(() => curtainUp(), delay * 2); // We wait 500ms for the next photo to be loaded.
		window.setTimeout(() => next(), delay * 2 + 1000 * slide_show_interval.value); // Set timeout for next iteration.
	}

	function previous(immediate: boolean = false) {
		removeVideoElementListeners();
		if (getPrevious === undefined) {
			return;
		}

		if (!is_slideshow_active.value || immediate) {
			getPrevious();
			return;
		}

		clearTimeouts();

		if (videoElement.value && !videoElement.value.ended) {
			videoElement.value?.addEventListener("ended", videoEndedEventListener);
		}

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
