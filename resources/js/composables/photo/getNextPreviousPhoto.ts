import { Router } from "vue-router";
import { usePhotoRoute } from "./photoRoute";
import { photoStore } from "@/stores/PhotoState";

export function getNextPreviousPhoto(router: Router, photoStore: photoStore) {
	const { photoRoute } = usePhotoRoute(router);

	function getNext() {
		if (photoStore.photo === undefined) {
			// nothing to do.
			return;
		}

		if (photoStore.photo.next_photo_id !== null) {
			router.push(photoRoute(photoStore.photo.next_photo_id));
			return;
		}

		// returns the current photo's id if there is no Next
		router.push(photoRoute(photoStore.photo.id));
	}

	function getPrevious() {
		if (photoStore.photo === undefined) {
			// nothing to do.
			return;
		}

		if (photoStore.photo.previous_photo_id !== null) {
			router.push(photoRoute(photoStore.photo.previous_photo_id));
			return;
		}

		router.push(photoRoute(photoStore.photo.id));
	}

	return {
		getNext,
		getPrevious,
	};
}
