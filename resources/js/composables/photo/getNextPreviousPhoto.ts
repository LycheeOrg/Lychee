import { Ref } from "vue";
import { Router } from "vue-router";
import { usePhotoRoute } from "./photoRoute";
import { TogglablesStateStore } from "@/stores/ModalsState";

export function getNextPreviousPhoto(
	togglableStore: TogglablesStateStore,
	router: Router,
	albumId: Ref<string | undefined>,
	photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>,
) {
	const { photoRoute } = usePhotoRoute(togglableStore);

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
