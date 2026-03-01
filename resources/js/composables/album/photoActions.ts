import AlbumService from "@/services/album-service";
import PhotoService from "@/services/photo-service";
import { type LycheeStateStore } from "@/stores/LycheeState";
import { PhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumStore } from "@/stores/AlbumState";
import { trans } from "laravel-vue-i18n";
import { type ToastServiceMethods } from "primevue/toastservice";
import { type Ref } from "vue";

export function usePhotoActions(photoStore: PhotoStore, albumId: Ref<string | undefined>, toast: ToastServiceMethods, lycheeStore: LycheeStateStore) {
	const photosStore = usePhotosStore();
	const albumStore = useAlbumStore();

	function toggleHighlight() {
		if (photoStore.photo === undefined) {
			return;
		}

		const newStarValue = !photoStore.photo.is_highlighted;
		PhotoService.highlight([photoStore.photo.id], newStarValue).then(() => {
			// Update the current photo store
			photoStore.photo!.is_highlighted = newStarValue;

			// Update the photo in the album list (photosStore) to keep it in sync
			const photoIndex = photosStore.photos.findIndex((p) => p.id === photoStore.photo!.id);
			if (photoIndex !== -1) {
				photosStore.photos[photoIndex].is_highlighted = newStarValue;
			}

			AlbumService.clearCache(albumId.value);
		});
	}

	// Untested
	function rotatePhotoCCW() {
		if (photoStore.photo === undefined) {
			return;
		}

		PhotoService.rotate(photoStore.photo.id, "-1", albumId.value ?? null).then(() => {
			AlbumService.clearCache(albumId.value);
			location.reload();
		});
	}

	// Untested
	function rotatePhotoCW() {
		if (photoStore.photo === undefined) {
			return;
		}

		PhotoService.rotate(photoStore.photo.id, "1", albumId.value ?? null).then(() => {
			AlbumService.clearCache(albumId.value);
			location.reload();
		});
	}

	function setAlbumHeader() {
		if (photoStore.photo === undefined) {
			return;
		}

		if (albumId.value === undefined) {
			return;
		}

		PhotoService.setAsHeader(photoStore.photo.id, albumId.value, false).then(() => {
			// Update the album's header_id to reflect the change (toggle behavior)
			const isToggleOff = albumStore.modelAlbum?.header_id === photoStore.photo!.id;
			if (albumStore.modelAlbum !== undefined) {
				albumStore.modelAlbum.header_id = isToggleOff ? null : photoStore.photo!.id;
				if (albumStore.modelAlbum.preFormattedData) {
					albumStore.modelAlbum.preFormattedData.header_photo_focus = null;
				}
			}
			if (
				albumStore.album !== undefined &&
				"editable" in albumStore.album &&
				albumStore.album.editable !== undefined &&
				albumStore.album.editable !== null
			) {
				albumStore.album.editable.header_id = isToggleOff ? null : photoStore.photo!.id;
				albumStore.album.preFormattedData.header_photo_focus = null;
			}

			// Update the header image URL in the album's preFormattedData
			if (albumStore.album?.preFormattedData) {
				albumStore.album.preFormattedData.header_photo_focus = null;
				if (isToggleOff) {
					albumStore.album.preFormattedData.url = null;
				} else {
					// Use medium or small variant for the header image
					const headerUrl = photoStore.photo!.size_variants.medium?.url ?? photoStore.photo!.size_variants.small?.url ?? null;
					albumStore.album.preFormattedData.url = headerUrl;
				}
			}

			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("gallery.photo.actions.header_set"), life: 2000 });
			AlbumService.clearCache(albumId.value);
			// refresh();
		});
	}

	function rotateOverlay() {
		const overlays = ["none", "desc", "date", "exif"] as App.Enum.ImageOverlayType[];
		for (let i = 0; i < overlays.length; i++) {
			if (lycheeStore.image_overlay_type === overlays[i]) {
				lycheeStore.image_overlay_type = overlays[(i + 1) % overlays.length];
				return;
			}
		}
	}

	return {
		toggleHighlight,
		rotatePhotoCCW,
		rotatePhotoCW,
		setAlbumHeader,
		rotateOverlay,
	};
}
