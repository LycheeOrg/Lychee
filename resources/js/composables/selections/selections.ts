import { TogglablesStateStore } from "@/stores/ModalsState";
import { modKey, shiftKeyState } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { computed, Ref, ref } from "vue";
import { useAlbumActions } from "@/composables/album/albumActions";

export type Selectables = {
	photos?: Ref<App.Http.Resources.Models.PhotoResource[]>;
	albums?: Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
};

const { canInteractAlbum, canInteractPhoto } = useAlbumActions();

export function useSelection(selectables: Selectables, togglableStore: TogglablesStateStore) {
	const { selectedPhotosIdx, selectedAlbumsIdx } = storeToRefs(togglableStore);
	const selectedPhoto = computed<App.Http.Resources.Models.PhotoResource | undefined>(() =>
		selectedPhotosIdx.value.length === 1 ? (selectables.photos?.value[selectedPhotosIdx.value[0]] ?? undefined) : undefined,
	);
	const selectedAlbum = computed<App.Http.Resources.Models.ThumbAlbumResource | undefined>(() =>
		selectedAlbumsIdx.value.length === 1 ? (selectables.albums?.value[selectedAlbumsIdx.value[0]] ?? undefined) : undefined,
	);
	const selectedPhotos = computed<App.Http.Resources.Models.PhotoResource[]>(
		() => selectables.photos?.value.filter((_, idx) => selectedPhotosIdx.value.includes(idx)) ?? [],
	);
	const selectedAlbums = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(
		() => selectables.albums?.value.filter((_, idx) => selectedAlbumsIdx.value.includes(idx)) ?? [],
	);
	const selectedPhotosIds = computed(() => selectedPhotos.value.map((p) => p.id));
	const selectedAlbumsIds = computed(() => selectedAlbums.value.map((a) => a.id));

	// We save the last clicked index so we can do selections with shift.
	const lastPhotoClicked = ref<number | undefined>(undefined);
	const lastAlbumClicked = ref<number | undefined>(undefined);

	function isPhotoSelected(idx: number) {
		return selectedPhotosIdx.value.includes(idx);
	}
	function isAlbumSelected(idx: number) {
		return selectedAlbumsIdx.value.includes(idx);
	}

	function hasSelection(): boolean {
		return selectedPhotosIdx.value.length > 0 || selectedAlbumsIdx.value.length > 0;
	}

	function unselect(): void {
		selectedAlbumsIdx.value = [];
		selectedPhotosIdx.value = [];
	}

	function addToPhotoSelection(idx: number): void {
		selectedPhotosIdx.value.push(idx);
	}
	function removeFromPhotoSelection(idx: number): void {
		selectedPhotosIdx.value = selectedPhotosIdx.value.filter((i) => i !== idx);
	}

	function addToAlbumSelection(idx: number): void {
		selectedAlbumsIdx.value.push(idx);
	}

	function removeFromAlbumSelection(idx: number): void {
		selectedAlbumsIdx.value = selectedAlbumsIdx.value.filter((i) => i !== idx);
	}

	function photoSelect(idx: number, e: Event): void {
		// clear the Album selection.
		selectedAlbumsIdx.value = [];

		// we do not support CTRL + SHIFT
		if (!modKey().value && !shiftKeyState.value) {
			return;
		}

		// We are able to edit.
		e.preventDefault();
		e.stopPropagation();

		if (selectables.photos === undefined || canInteractPhoto(selectables.photos.value[idx]) === false) {
			return;
		}

		if (modKey().value) {
			handlePhotoCtrl(idx, e);
			return;
		}

		if (shiftKeyState.value) {
			handlePhotoShift(idx, e);
			return;
		}
	}

	function handlePhotoCtrl(idx: number, _e: Event): void {
		if (isPhotoSelected(idx)) {
			removeFromPhotoSelection(idx);
		} else {
			addToPhotoSelection(idx);
		}
		lastPhotoClicked.value = idx;
	}

	function handlePhotoShift(idx: number, _e: Event): void {
		if (selectedPhotos.value.length === 0) {
			addToPhotoSelection(idx);
			lastPhotoClicked.value = idx;
			return;
		}

		// Picture is selected.
		// We remove all pictures from latest click till current idx
		if (isPhotoSelected(idx)) {
			// @ts-expect-error lastPhotoClicked is always defined here
			const idx_min = Math.min(lastPhotoClicked.value, idx);
			// @ts-expect-error lastPhotoClicked is always defined here
			const idx_max = Math.max(lastPhotoClicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				removeFromPhotoSelection(i);
			}
		} else if (lastPhotoClicked.value === undefined) {
			addToPhotoSelection(idx);
		} else {
			const idx_min = Math.min(lastPhotoClicked.value, idx);
			const idx_max = Math.max(lastPhotoClicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				addToPhotoSelection(i);
			}
		}
		lastPhotoClicked.value = idx;
	}

	function albumClick(idx: number, e: Event): void {
		// clear the Photo selection.
		selectedPhotosIdx.value = [];

		// we do not support CTRL + SHIFT
		if (!modKey().value && !shiftKeyState.value) {
			return;
		}

		// We are able to edit.
		e.preventDefault();
		e.stopPropagation();

		if (selectables.albums === undefined || canInteractAlbum(selectables.albums.value[idx]) === false) {
			return;
		}

		if (modKey().value) {
			handleAlbumCtrl(idx, e);
			return;
		}

		if (shiftKeyState.value) {
			handleAlbumShift(idx, e);
			return;
		}
	}

	function handleAlbumCtrl(idx: number, _e: Event): void {
		if (isAlbumSelected(idx)) {
			removeFromAlbumSelection(idx);
		} else {
			addToAlbumSelection(idx);
		}
		lastAlbumClicked.value = idx;
	}

	function handleAlbumShift(idx: number, _e: Event): void {
		if (selectedAlbums.value.length === 0) {
			addToAlbumSelection(idx);
			lastAlbumClicked.value = idx;
			return;
		}

		// Album is selected.
		// We remove all albums from latest click till current idx
		if (isAlbumSelected(idx)) {
			// @ts-expect-error lastAlbumClicked is always defined here
			const idx_min = Math.min(lastAlbumClicked.value, idx);
			// @ts-expect-error lastAlbumClicked is always defined here
			const idx_max = Math.max(lastAlbumClicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				removeFromAlbumSelection(i);
			}
		} else if (lastAlbumClicked.value === undefined) {
			addToAlbumSelection(idx);
		} else {
			const idx_min = Math.min(lastAlbumClicked.value, idx);
			const idx_max = Math.max(lastAlbumClicked.value, idx);
			for (let i = idx_min; i <= idx_max; i++) {
				addToAlbumSelection(i);
			}
		}
		lastAlbumClicked.value = idx;
	}

	function getKeysFromPredicate<A>(items: A[], predicate: (i: A) => boolean): number[] {
		return Array.from(items.keys()).filter((k: number) => predicate(items[k]));
	}

	function selectEverything(): void {
		if (selectedPhotosIdx.value.length === (selectables.photos?.value.length ?? 0) && selectables.albums) {
			// Flip and select albums
			selectedPhotosIdx.value = [];
			selectedAlbumsIdx.value = getKeysFromPredicate(selectables.albums.value, canInteractAlbum);
			return;
		}
		if (selectedAlbumsIdx.value.length === (selectables.albums?.value.length ?? 0) && selectables.photos) {
			selectedAlbumsIdx.value = [];
			selectedPhotosIdx.value = getKeysFromPredicate(selectables.photos.value, canInteractPhoto);
			// Flip and select photos
			return;
		}
		if (selectedAlbumsIdx.value.length > 0 && selectables.albums) {
			selectedAlbumsIdx.value = getKeysFromPredicate(selectables.albums.value, canInteractAlbum);
			return;
		}
		if ((selectables.photos?.value.length ?? 0) > 0 && selectables.photos) {
			selectedPhotosIdx.value = getKeysFromPredicate(selectables.photos.value, canInteractPhoto);
			return;
		}
		if (selectables.albums) {
			selectedAlbumsIdx.value = getKeysFromPredicate(selectables.albums.value, canInteractAlbum);
		}
	}

	return {
		selectedPhoto,
		selectedAlbum,
		selectedPhotosIdx,
		selectedAlbumsIdx,
		selectedPhotos,
		selectedAlbums,
		selectedPhotosIds,
		selectedAlbumsIds,
		photoSelect,
		albumClick,
		selectEverything,
		unselect,
		hasSelection,
	};
}
