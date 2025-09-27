import AlbumService from "@/services/album-service";
import PhotoService from "@/services/photo-service";
import { type LycheeStateStore } from "@/stores/LycheeState";
import { photoStore } from "@/stores/PhotoState";
import { trans } from "laravel-vue-i18n";
import { type ToastServiceMethods } from "primevue/toastservice";
import { type Ref } from "vue";

export function usePhotoActions(photoStore: photoStore, albumId: Ref<string | undefined>, toast: ToastServiceMethods, lycheeStore: LycheeStateStore) {
	function toggleStar() {
		if (photoStore.photo === undefined) {
			return;
		}

		PhotoService.star([photoStore.photo.id], !photoStore.photo.is_starred).then(() => {
			photoStore.photo!.is_starred = !photoStore.photo!.is_starred;
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
		toggleStar,
		rotatePhotoCCW,
		rotatePhotoCW,
		setAlbumHeader,
		rotateOverlay,
	};
}
