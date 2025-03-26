import { Ref } from "vue";
import { Router } from "vue-router";
import { usePhotoRoute } from "./photoRoute";

export function getNextPreviousPhoto(
	router: Router,
	albumId: Ref<string | undefined>,
	photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>,
) {
	const { photoRoute } = usePhotoRoute(router);

	function getNext() {
		router.push(photoRoute(albumId.value, photo.value?.next_photo_id ?? ""));
	}

	function getPrevious() {
		router.push(photoRoute(albumId.value, photo.value?.previous_photo_id ?? ""));
	}

	return {
		getNext,
		getPrevious,
	};
}
