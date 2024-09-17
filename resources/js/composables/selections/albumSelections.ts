import { useKeyModifier } from "@vueuse/core";
import { computed, ref } from "vue";

export function useAlbumsSelection(props: {
	album: App.Http.Resources.Models.AlbumResource | undefined | null;
	albums: { [key: number]: App.Http.Resources.Models.ThumbAlbumResource };
}) {
	const albums = computed(() => props.albums);
	const album = computed(() => props.album);

	const ctrlKeyState = useKeyModifier("Control");
	const shiftKeyState = useKeyModifier("Shift");

	// We save the last clicked index so we can do selections with shift.
	const lastCLicked = ref(undefined as number | undefined);

	const selectedAlbums = ref([] as number[]);

	// Getter for the menu
	const getAlbum = () => album.value;
	function isAlbumSelected(idx: number) {
		return selectedAlbums.value.includes(idx);
	}
	function getSelectedAlbums(): App.Http.Resources.Models.ThumbAlbumResource[] {
		return (albums.value as App.Http.Resources.Models.ThumbAlbumResource[]).filter((_, idx) => selectedAlbums.value.includes(idx));
	}

	function getSelectedAlbumsIds(): string[] {
		return getSelectedAlbums().map((Album) => Album.id);
	}

	function addToAlbumSelection(idx: number) {
		selectedAlbums.value.push(idx);
	}
	function removeFromAlbumSelection(idx: number) {
		selectedAlbums.value = selectedAlbums.value.filter((i) => i !== idx);
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
		if (isAlbumSelected(idx)) {
			removeFromAlbumSelection(idx);
		} else {
			addToAlbumSelection(idx);
		}
		lastCLicked.value = idx;
	}

	function handleShift(idx: number, e: Event) {
		console.log("shift");
		if (selectedAlbums.value.length === 0) {
			addToAlbumSelection(idx);
			return;
		}

		// Picture is selected.
		// We remove all pictures from latest click till current idx
		if (isAlbumSelected(idx)) {
			// @ts-expect-error lastCLicked is always defined here
			const idx_min = Math.min(lastCLicked.value, idx);
			// @ts-expect-error lastCLicked is always defined here
			const idx_max = Math.max(lastCLicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				removeFromAlbumSelection(i);
			}
		} else if (lastCLicked.value === undefined) {
			addToAlbumSelection(idx);
		} else {
			const idx_min = Math.min(lastCLicked.value, idx);
			const idx_max = Math.max(lastCLicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				addToAlbumSelection(i);
			}
		}
		lastCLicked.value = idx;
	}

	return {
		albums,
		album,
		getAlbum,
		selectedAlbums,
		isAlbumSelected,
		getSelectedAlbums,
		getSelectedAlbumsIds,
		addToAlbumSelection,
		// removeFromAlbumSelection,
		maySelect,
	};
}
