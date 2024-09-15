import { useKeyModifier } from "@vueuse/core";
import { computed, ref } from "vue";

export function usePhotosSelection(props: {
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource | null;
	photos: { [key: number]: App.Http.Resources.Models.PhotoResource };
}) {
	const photos = computed(() => props.photos);
	const album = computed(() => props.album);

	const ctrlKeyState = useKeyModifier("Control");
	const shiftKeyState = useKeyModifier("Shift");

	// We save the last clicked index so we can do selections with shift.
	const lastCLicked = ref(undefined as number | undefined);

	const selectedPhotos = ref([] as number[]);

	// Getter for the menu
	const getAlbum = () => album.value;
	function isPhotoSelected(idx: number) {
		return selectedPhotos.value.includes(idx);
	}
	function getSelectedPhotos(): App.Http.Resources.Models.PhotoResource[] {
		return (photos.value as App.Http.Resources.Models.PhotoResource[]).filter((_, idx) => selectedPhotos.value.includes(idx));
	}

	function getSelectedPhotosIds(): string[] {
		return getSelectedPhotos().map((photo) => photo.id);
	}

	function addToPhotoSelection(idx: number) {
		selectedPhotos.value.push(idx);
	}
	function removeFromPhotoSelection(idx: number) {
		selectedPhotos.value = selectedPhotos.value.filter((i) => i !== idx);
	}

	function maySelect(idx: number, e: Event): void {
		if (!ctrlKeyState.value && !shiftKeyState.value) {
			return;
		}

		// For now we only consider editing.
		if (!album.value || !album.value.rights.can_edit) {
			return;
		}
		// We are able to edit.
		e.preventDefault();

		if (ctrlKeyState.value) {
			handleCtrl(idx, e);
			return;
		}

		if (shiftKeyState.value) {
			handleShift(idx, e);
			return;
		}
	}

	function handleCtrl(idx: number, e: Event) {
		if (isPhotoSelected(idx)) {
			removeFromPhotoSelection(idx);
		} else {
			addToPhotoSelection(idx);
		}
		lastCLicked.value = idx;
	}

	function handleShift(idx: number, e: Event) {
		console.log("shift");
		if (selectedPhotos.value.length === 0) {
			addToPhotoSelection(idx);
			return;
		}

		// Picture is selected.
		// We remove all pictures from latest click till current idx
		if (isPhotoSelected(idx)) {
			// @ts-expect-error lastCLicked is always defined here
			const idx_min = Math.min(lastCLicked.value, idx);
			// @ts-expect-error lastCLicked is always defined here
			const idx_max = Math.max(lastCLicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				removeFromPhotoSelection(i);
			}
		} else if (lastCLicked.value === undefined) {
			addToPhotoSelection(idx);
		} else {
			const idx_min = Math.min(lastCLicked.value, idx);
			const idx_max = Math.max(lastCLicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				addToPhotoSelection(i);
			}
		}
		lastCLicked.value = idx;
	}

	return {
		photos,
		album,
		getAlbum,
		selectedPhotos,
		isPhotoSelected,
		getSelectedPhotos,
		getSelectedPhotosIds,
		addToPhotoSelection,
		// removeFromPhotoSelection,
		maySelect,
	};
}
