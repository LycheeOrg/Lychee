import { ALL } from "@/config/constants";
import { Router } from "vue-router";

export function usePhotoRoute(router: Router) {
	function photoRoute(photoId: string) {
		const currentRoute = router.currentRoute.value.name as string;
		if (currentRoute.startsWith("search")) {
			return { name: "search-photo", params: { albumId: router.currentRoute.value.params.albumId ?? ALL, photoId: photoId } };
		}

		const albumId = router.currentRoute.value.params.albumId as string | undefined;
		return { name: "photo", params: { albumId: albumId ?? ALL, photoId: photoId } };
	}

	return { photoRoute };
}
