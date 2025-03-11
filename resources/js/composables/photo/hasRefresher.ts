import { Ref } from "vue";

export function usePhotoRefresher(
	photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>,
	photos: Ref<App.Http.Resources.Models.PhotoResource[] | undefined>,
	photoId: Ref<string | undefined>,
) {
	function refreshPhoto(): void {
		photo.value = photos.value?.find((photo: App.Http.Resources.Models.PhotoResource) => photo.id === photoId.value);
	}

	return { refreshPhoto };
}
