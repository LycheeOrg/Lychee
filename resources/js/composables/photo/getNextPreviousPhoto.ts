import { Ref } from "vue";
import { Router } from "vue-router";
import { usePhotoRoute } from "./photoRoute";

export function getNextPreviousPhoto(router: Router, photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>) {
	const { photoRoute } = usePhotoRoute(router);

	function getNext() {
		router.push(photoRoute(photo.value?.next_photo_id ?? ""));
	}

	function getPrevious() {
		router.push(photoRoute(photo.value?.previous_photo_id ?? ""));
	}

	return {
		getNext,
		getPrevious,
	};
}
