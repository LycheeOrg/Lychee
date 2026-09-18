import { watchEffect } from "vue";
import { trans } from "laravel-vue-i18n";
import { usePhotoStore } from "@/stores/PhotoState";
import { useLycheeStateStore } from "@/stores/LycheeState";

/**
 * Keeps the browser tab title in sync with the photo currently open in the
 * full-screen viewer, falling back to the site title otherwise.
 *
 * Every gallery panel (Album, Timeline, Search, Tag, PersonDetail, ...) reads
 * and writes the same shared `photoStore`, clearing `photo` via `reset()`
 * when the viewer is closed - so watching it here, once, is enough to cover
 * every place the viewer can be opened without touching each panel.
 */
export function useDocumentTitle(): void {
	const photoStore = usePhotoStore();
	const lycheeStore = useLycheeStateStore();

	watchEffect(() => {
		document.title = photoStore.photo?.title || trans(lycheeStore.title);
	});
}
