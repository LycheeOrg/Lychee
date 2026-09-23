import { TogglablesStateStore } from "@/stores/ModalsState";
import { getModKey } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { computed, ref, type ComputedRef } from "vue";
import { useAlbumActions } from "@/composables/album/albumActions";
import { PhotosStore } from "@/stores/PhotosState";
import { AlbumsStore } from "@/stores/AlbumsState";

export function useSelection(
	photosStore: PhotosStore,
	albumsStore: AlbumsStore,
	togglableStore: TogglablesStateStore,
	/**
	 * Replaces `albumsStore.selectableAlbums` as the pool every album-side
	 * selection resolves against. Feature 069's v3 search keeps its album hits
	 * store-locally rather than in the browsing store (FR-069-20), so on that
	 * path `selectableAlbums` holds either nothing or the previously browsed
	 * albums — never what the user is actually looking at. A `undefined` value
	 * (the default, and what the override itself yields off the v3 path) keeps
	 * the browsing store, so every existing caller is unchanged.
	 */
	overrideSelectableAlbums?: ComputedRef<App.Http.Resources.Models.ThumbAlbumResource[] | undefined>,
) {
	const { canInteractAlbum, canInteractPhoto } = useAlbumActions();

	const selectableAlbums = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(
		() => overrideSelectableAlbums?.value ?? albumsStore.selectableAlbums,
	);

	const { selectedPhotosIds, selectedAlbumsIds } = storeToRefs(togglableStore);
	const selectedPhoto = computed<App.Http.Resources.Models.PhotoResource | undefined>(() =>
		selectedPhotosIds.value.length === 1 ? (photosStore.photos.find((p) => p.id === selectedPhotosIds.value[0]) ?? undefined) : undefined,
	);
	const selectedAlbum = computed<App.Http.Resources.Models.ThumbAlbumResource | undefined>(() =>
		selectedAlbumsIds.value.length === 1 ? (selectableAlbums.value.find((a) => a.id === selectedAlbumsIds.value[0]) ?? undefined) : undefined,
	);
	const selectedPhotos = computed<App.Http.Resources.Models.PhotoResource[]>(
		() => photosStore.photos.filter((p) => selectedPhotosIds.value.includes(p.id)) ?? [],
	);
	const selectedAlbums = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(() =>
		selectableAlbums.value.filter((a) => selectedAlbumsIds.value.includes(a.id)),
	);

	// We save the last clicked photo/album ID so we can do selections with shift.
	const lastPhotoClicked = ref<string | undefined>(undefined);
	const lastAlbumClicked = ref<string | undefined>(undefined);

	function isPhotoSelected(photoId: string) {
		return selectedPhotosIds.value.includes(photoId);
	}
	function isAlbumSelected(albumId: string) {
		return selectedAlbumsIds.value.includes(albumId);
	}

	function hasSelection(): boolean {
		return selectedPhotosIds.value.length > 0 || selectedAlbumsIds.value.length > 0;
	}

	function unselect(): void {
		selectedAlbumsIds.value = [];
		selectedPhotosIds.value = [];
	}

	/**
	 * Drop from the selection the photos and albums which are no longer part of the
	 * loaded collection.
	 *
	 * Called after a reload which keeps the selection alive (e.g. tagging), so that
	 * an item which disappeared in the meantime cannot be targeted by a later bulk
	 * action even though it is not displayed any more.
	 */
	function pruneSelection(): void {
		selectedPhotosIds.value = selectedPhotosIds.value.filter((id) => photosStore.photos.some((p) => p.id === id));
		selectedAlbumsIds.value = selectedAlbumsIds.value.filter((id) => selectableAlbums.value.some((a) => a.id === id));
	}

	function addToPhotoSelection(photoId: string): void {
		if (!selectedPhotosIds.value.includes(photoId)) {
			selectedPhotosIds.value.push(photoId);
		}
	}
	function removeFromPhotoSelection(photoId: string): void {
		selectedPhotosIds.value = selectedPhotosIds.value.filter((id) => id !== photoId);
	}

	function addToAlbumSelection(albumId: string): void {
		if (!selectedAlbumsIds.value.includes(albumId)) {
			selectedAlbumsIds.value.push(albumId);
		}
	}

	function removeFromAlbumSelection(albumId: string): void {
		selectedAlbumsIds.value = selectedAlbumsIds.value.filter((id) => id !== albumId);
	}

	function getMouseModifiers(e: MouseEvent): { isMod: boolean; isShift: boolean } {
		const modKey = getModKey();
		const isMod = modKey === "Meta" ? e.metaKey : e.ctrlKey;
		return { isMod, isShift: e.shiftKey };
	}

	function photoSelect(photoId: string, e: MouseEvent): void {
		// clear the Album selection.
		selectedAlbumsIds.value = [];

		// we do not support CTRL + SHIFT
		const { isMod, isShift } = getMouseModifiers(e);
		const isTouchSelect = togglableStore.is_touch_select_mode;

		if (!isMod && !isShift && !isTouchSelect) {
			return;
		}

		// We are able to edit.
		e.preventDefault();
		e.stopPropagation();

		if (photosStore.filteredPhotos.length === 0 || canInteractPhoto() === false) {
			return;
		}

		// Touch select mode or Ctrl/Meta: toggle individual photo
		if (isTouchSelect || isMod) {
			handlePhotoCtrl(photoId);
			return;
		}

		if (isShift) {
			handlePhotoShift(photoId);
			return;
		}
	}

	function handlePhotoCtrl(photoId: string): void {
		if (isPhotoSelected(photoId)) {
			removeFromPhotoSelection(photoId);
		} else {
			addToPhotoSelection(photoId);
		}
		lastPhotoClicked.value = photoId;
	}

	function handlePhotoShift(photoId: string): void {
		if (selectedPhotos.value.length === 0) {
			addToPhotoSelection(photoId);
			lastPhotoClicked.value = photoId;
			return;
		}

		// Find indices in the filtered photos array for range selection
		const filteredPhotos = photosStore.filteredPhotos;
		const currentIdx = filteredPhotos.findIndex((p) => p.id === photoId);
		const lastIdx = lastPhotoClicked.value !== undefined ? filteredPhotos.findIndex((p) => p.id === lastPhotoClicked.value) : -1;

		if (currentIdx === -1) {
			// Photo not found in filtered list
			return;
		}

		// Photo is selected - remove range
		if (isPhotoSelected(photoId)) {
			if (lastIdx === -1) {
				removeFromPhotoSelection(photoId);
			} else {
				const idx_min = Math.min(lastIdx, currentIdx);
				const idx_max = Math.max(lastIdx, currentIdx);
				for (let i = idx_min; i <= idx_max; i++) {
					removeFromPhotoSelection(filteredPhotos[i].id);
				}
			}
		} else if (lastIdx === -1) {
			addToPhotoSelection(photoId);
		} else {
			// Add range
			const idx_min = Math.min(lastIdx, currentIdx);
			const idx_max = Math.max(lastIdx, currentIdx);
			for (let i = idx_min; i <= idx_max; i++) {
				addToPhotoSelection(filteredPhotos[i].id);
			}
		}
		lastPhotoClicked.value = photoId;
	}

	function albumSelect(e: MouseEvent, albumId: string): void {
		// clear the Photo selection.
		selectedPhotosIds.value = [];

		// we do not support CTRL + SHIFT
		const { isMod, isShift } = getMouseModifiers(e);
		const isTouchSelect = togglableStore.is_touch_select_mode;

		if (!isMod && !isShift && !isTouchSelect) {
			return;
		}

		// We are able to edit.
		e.preventDefault();
		e.stopPropagation();

		const album = selectableAlbums.value.find((a) => a.id === albumId);
		if (!album || canInteractAlbum(album) === false) {
			return;
		}

		// Touch select mode or Ctrl/Meta: toggle individual album
		if (isTouchSelect || isMod) {
			handleAlbumCtrl(albumId);
			return;
		}

		if (isShift) {
			handleAlbumShift(albumId);
			return;
		}
	}

	function handleAlbumCtrl(albumId: string): void {
		if (isAlbumSelected(albumId)) {
			removeFromAlbumSelection(albumId);
		} else {
			addToAlbumSelection(albumId);
		}
		lastAlbumClicked.value = albumId;
	}

	function handleAlbumShift(albumId: string): void {
		if (selectedAlbums.value.length === 0) {
			addToAlbumSelection(albumId);
			lastAlbumClicked.value = albumId;
			return;
		}

		// Find indices in the selectableAlbums array for range selection
		const albums = selectableAlbums.value;
		const currentIdx = albums.findIndex((a) => a.id === albumId);
		const lastIdx = lastAlbumClicked.value !== undefined ? albums.findIndex((a) => a.id === lastAlbumClicked.value) : -1;

		if (currentIdx === -1) {
			// Album not found
			return;
		}

		// Album is selected - remove range
		if (isAlbumSelected(albumId)) {
			if (lastIdx === -1) {
				removeFromAlbumSelection(albumId);
			} else {
				const idx_min = Math.min(lastIdx, currentIdx);
				const idx_max = Math.max(lastIdx, currentIdx);
				for (let i = idx_min; i <= idx_max; i++) {
					removeFromAlbumSelection(albums[i].id);
				}
			}
		} else if (lastIdx === -1) {
			addToAlbumSelection(albumId);
		} else {
			// Add range
			const idx_min = Math.min(lastIdx, currentIdx);
			const idx_max = Math.max(lastIdx, currentIdx);
			for (let i = idx_min; i <= idx_max; i++) {
				addToAlbumSelection(albums[i].id);
			}
		}
		lastAlbumClicked.value = albumId;
	}

	function selectEverything(): void {
		const filteredPhotos = photosStore.filteredPhotos;
		if (selectedPhotosIds.value.length === filteredPhotos.length && selectableAlbums.value.length > 0) {
			// Flip and select albums
			selectedPhotosIds.value = [];
			selectedAlbumsIds.value = selectableAlbums.value.filter(canInteractAlbum).map((a) => a.id);
			return;
		}
		if (selectedAlbumsIds.value.length === selectableAlbums.value.length && filteredPhotos.length > 0) {
			selectedAlbumsIds.value = [];
			selectedPhotosIds.value = filteredPhotos.filter(canInteractPhoto).map((p) => p.id);
			// Flip and select photos
			return;
		}
		if (selectedAlbumsIds.value.length > 0 && selectableAlbums.value.length > 0) {
			selectedAlbumsIds.value = selectableAlbums.value.filter(canInteractAlbum).map((a) => a.id);
			return;
		}
		if (filteredPhotos.length > 0) {
			selectedPhotosIds.value = filteredPhotos.filter(canInteractPhoto).map((p) => p.id);
			return;
		}
		if (selectableAlbums.value.length > 0) {
			selectedAlbumsIds.value = selectableAlbums.value.filter(canInteractAlbum).map((a) => a.id);
		}
	}

	return {
		selectedPhoto,
		selectedAlbum,
		selectedPhotosIds,
		selectedAlbumsIds,
		selectedPhotos,
		selectedAlbums,
		photoSelect,
		albumSelect,
		selectEverything,
		unselect,
		pruneSelection,
		hasSelection,
	};
}
