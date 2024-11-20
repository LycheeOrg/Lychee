import { TogglablesStateStore } from "@/stores/ModalsState";
import { Ref } from "vue";

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

		let e = document.getElementById("galleryView");
		if (e === null) {
			setTimeout(() => setScroll(_v, iter + 1), 100);
		} else {
			toggleableStore.recoverScrollPage(e, path.value);
		}
	}

	return {
		onScroll,
		setScroll,
	};
}
