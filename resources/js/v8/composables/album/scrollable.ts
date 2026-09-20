import { type TogglablesStateStore } from "@/stores/ModalsState";
import { onMounted, onUnmounted, type Ref } from "vue";

/**
 * v8 fork of `@/composables/album/scrollable`.
 *
 * v8's album/photo grids are laid out with `useWindowVirtualizer` (the page/window
 * scrolls, `#galleryView` itself has no `overflow`), unlike v7 where `#galleryView`
 * is its own `overflow-y-auto` container. So, unlike the v7 original, this tracks
 * and restores `window.scrollY` instead of an element's `scrollTop`.
 */
export function useScrollable(toggleableStore: TogglablesStateStore, path: Ref<string | undefined>) {
	function onScroll() {
		if (path.value === undefined) {
			return;
		}

		toggleableStore.scroll_memory[path.value] = window.scrollY;
	}

	onMounted(() => window.addEventListener("scroll", onScroll, { passive: true }));
	onUnmounted(() => window.removeEventListener("scroll", onScroll));

	async function setScroll(_v: void, iter = 0) {
		if (path.value === undefined) {
			return;
		}

		if (toggleableStore.scroll_photo_id) {
			const thumbPhotoElement = document.querySelector(`[data-photo-id="${toggleableStore.scroll_photo_id}"]`) as HTMLElement | null;
			if (!thumbPhotoElement) {
				// The target thumbnail is not yet in the DOM (background-loaded page may
				// still be rendering). Retry instead of falling back to the saved scroll
				// position so that we scroll correctly once it appears.
				if (iter < 50) {
					setTimeout(() => setScroll(_v, iter + 1), 100);
				}
				return;
			}

			toggleableStore.recoverAndResetScrollThumb(thumbPhotoElement);
			return;
		}

		// No remembered position (e.g. this album has never been scrolled before):
		// make sure we start at the top rather than inheriting the previous album's
		// window scroll position.
		const target = toggleableStore.scroll_memory[path.value] ?? 0;
		if (target === 0) {
			window.scrollTo({ top: 0 });
			return;
		}

		// The virtualized grid may not have measured/rendered enough rows yet for the
		// target offset to be reachable. Wait until the page is tall enough, giving up
		// (and scrolling as far as we can) after 50 tries (5s).
		const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
		if (maxScroll < target && iter < 50) {
			setTimeout(() => setScroll(_v, iter + 1), 100);
			return;
		}

		window.scrollTo({ top: target });
	}

	function scrollToTop() {
		window.scrollTo({ top: 0 });
	}

	return {
		onScroll,
		setScroll,
		scrollToTop,
	};
}
