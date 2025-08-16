import { type TogglablesStateStore } from "@/stores/ModalsState";
import { type Ref } from "vue";

export function useScrollable(toggleableStore: TogglablesStateStore, path: Ref<string>) {
	function onScroll() {
		const e = document.getElementById("galleryView") as HTMLElement;
		toggleableStore.rememberScrollPage(e, path.value);
	}

	async function setScroll(_v: void, iter = 0) {
		// Abort. We tried 10 times (waited 1s).
		if (iter == 10) {
			return;
		}

		const e = document.getElementById("galleryView");
		if (e === null) {
			setTimeout(() => setScroll(_v, iter + 1), 100);
		} else {
			if (!toggleableStore.scroll_photo_id) {
				toggleableStore.recoverScrollPage(e, path.value);
				return;
			}

			const thumbPhotoElement = document.querySelector(`[data-photo-id="${toggleableStore.scroll_photo_id}"]`) as HTMLElement | null;
			if (!thumbPhotoElement) {
				toggleableStore.recoverScrollPage(e, path.value);
			} else {
				toggleableStore.recoverAndResetScrollThumb(thumbPhotoElement);
			}
		}
	}

	function scrollToTop() {
		const e = document.getElementById("galleryView") as HTMLElement;
		e.scrollTop = 0;
	}

	return {
		onScroll,
		setScroll,
		scrollToTop,
	};
}
