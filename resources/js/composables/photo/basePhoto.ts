import { Ref } from "vue";

export function usePhotoBaseFunction(
	photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>,
	videoElement: Ref<HTMLVideoElement | null> | undefined = undefined,
) {
	function refresh(): void {
		if (videoElement === undefined) {
			return;
		}

		// handle videos.
		const videoElementValue = videoElement.value;
		if (photo.value?.precomputed?.is_video && videoElementValue) {
			videoElementValue.src = photo.value?.size_variants?.original?.url ?? "";
			videoElementValue.load();
		}
	}

	return {
		refresh,
	};
}
