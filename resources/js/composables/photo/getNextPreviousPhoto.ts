import { Ref } from "vue";
import { Router } from "vue-router";
import { usePhotoRoute } from "./photoRoute";

export function getNextPreviousPhoto(router: Router, photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>) {
	const { photoRoute } = usePhotoRoute(router);

	function getNext() {
		if (photo.value === undefined) {
			// nothing to do.
			return;
		}

		if (photo.value.next_photo_id !== null) {
			router.push(photoRoute(photo.value.next_photo_id));
			return;
		}

		// returns the current photo's id if there is no Next
		router.push(photoRoute(photo.value.id));
	}

	function getPrevious() {
		if (photo.value === undefined) {
			// nothing to do.
			return;
		}

		if (photo.value.previous_photo_id !== null) {
			router.push(photoRoute(photo.value.previous_photo_id));
			return;
		}

		router.push(photoRoute(photo.value.id));
	}

	return {
		getNext,
		getPrevious,
	};
}
