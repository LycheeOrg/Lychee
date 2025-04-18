import AlbumService from "@/services/album-service";
import PhotoService from "@/services/photo-service";
import { LycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";
import { ToastServiceMethods } from "primevue/toastservice";
import { Ref } from "vue";

export function usePhotoActions(
	photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>,
	albumId: Ref<string | null>,
	toast: ToastServiceMethods,
	lycheeStore: LycheeStateStore,
) {
	function toggleStar() {
		if (photo.value === undefined) {
			return;
		}

		PhotoService.star([photo.value.id], !photo.value!.is_starred).then(() => {
			photo.value!.is_starred = !photo.value!.is_starred;
			AlbumService.clearCache(albumId.value);
		});
	}

	// Untested
	function rotatePhotoCCW() {
		if (photo.value === undefined) {
			return;
		}

		PhotoService.rotate(photo.value.id, "-1").then(() => {
			AlbumService.clearCache(albumId.value);
			// load();
		});
	}

	// Untested
	function rotatePhotoCW() {
		if (photo.value === undefined) {
			return;
		}

		PhotoService.rotate(photo.value.id, "1").then(() => {
			AlbumService.clearCache(albumId.value);
			// load();
		});
	}

	function setAlbumHeader() {
		if (photo.value === undefined) {
			return;
		}

		if (albumId.value === null) {
			return;
		}

		PhotoService.setAsHeader(photo.value.id, albumId.value, false).then(() => {
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
