import { TogglablesStateStore } from "@/stores/ModalsState";
import { getModKey } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { computed, ref } from "vue";
import { useAlbumActions } from "@/composables/album/albumActions";
import { PhotosStore } from "@/stores/PhotosState";
import { AlbumsStore } from "@/stores/AlbumsState";

export function useSelection(photosStore: PhotosStore, albumsStore: AlbumsStore, togglableStore: TogglablesStateStore) {
	const { canInteractAlbum, canInteractPhoto } = useAlbumActions();

	const { selectedPhotosIds, selectedAlbumsIds } = storeToRefs(togglableStore);
	const selectedPhoto = computed<App.Http.Resources.Models.PhotoResource | undefined>(() =>
		selectedPhotosIds.value.length === 1 ? (photosStore.photos.find((p) => p.id === selectedPhotosIds.value[0]) ?? undefined) : undefined,
	);
	const selectedAlbum = computed<App.Http.Resources.Models.ThumbAlbumResource | undefined>(() =>
		selectedAlbumsIds.value.length === 1
			? (albumsStore.selectableAlbums.find((a) => a.id === selectedAlbumsIds.value[0]) ?? undefined)
			: undefined,
	);
	const selectedPhotos = computed<App.Http.Resources.Models.PhotoResource[]>(
		() => photosStore.photos.filter((p) => selectedPhotosIds.value.includes(p.id)) ?? [],
	);
	const selectedAlbums = computed<App.Http.Resources.Models.ThumbAlbumResource[]>(
		() => albumsStore.selectableAlbums?.filter((a) => selectedAlbumsIds.value.includes(a.id)) ?? [],
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
		if (!isMod && !isShift) {
			return;
		}

		// We are able to edit.
		e.preventDefault();
		e.stopPropagation();

		if (photosStore.filteredPhotos.length === 0 || canInteractPhoto() === false) {
			return;
		}

		if (isMod) {
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

	function albumSelect(albumId: string, e: MouseEvent): void {
		// clear the Photo selection.
		selectedPhotosIds.value = [];

		// we do not support CTRL + SHIFT
		const { isMod, isShift } = getMouseModifiers(e);
		if (!isMod && !isShift) {
			return;
		}

		// We are able to edit.
		e.preventDefault();
		e.stopPropagation();

		const album = albumsStore.selectableAlbums.find((a) => a.id === albumId);
		if (!album || canInteractAlbum(album) === false) {
			return;
		}

		if (isMod) {
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
		const albums = albumsStore.selectableAlbums;
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
		if (selectedPhotosIds.value.length === filteredPhotos.length && albumsStore.selectableAlbums.length > 0) {
			// Flip and select albums
			selectedPhotosIds.value = [];
			selectedAlbumsIds.value = albumsStore.selectableAlbums.filter(canInteractAlbum).map((a) => a.id);
			return;
		}
		if (selectedAlbumsIds.value.length === albumsStore.selectableAlbums.length && filteredPhotos.length > 0) {
			selectedAlbumsIds.value = [];
			selectedPhotosIds.value = filteredPhotos.filter(canInteractPhoto).map((p) => p.id);
			// Flip and select photos
			return;
		}
		if (selectedAlbumsIds.value.length > 0 && albumsStore.selectableAlbums.length > 0) {
			selectedAlbumsIds.value = albumsStore.selectableAlbums.filter(canInteractAlbum).map((a) => a.id);
			return;
		}
		if (filteredPhotos.length > 0) {
			selectedPhotosIds.value = filteredPhotos.filter(canInteractPhoto).map((p) => p.id);
			return;
		}
		if (albumsStore.selectableAlbums.length > 0) {
			selectedAlbumsIds.value = albumsStore.selectableAlbums.filter(canInteractAlbum).map((a) => a.id);
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
		hasSelection,
	};
}
