import { ALL } from "@/config/constants";
import { Router } from "vue-router";

export function usePhotoRoute(router: Router) {
	function photoRoute(photoId: string) {
		const currentRoute = router.currentRoute.value.name as string;
		const albumId = router.currentRoute.value.params.albumId as string | undefined;

		if (currentRoute.startsWith("search")) {
			return { name: "search", params: { albumId: albumId ?? ALL, photoId: photoId } };
		}

		return { name: "album", params: { albumId: albumId ?? ALL, photoId: photoId } };
	}

	return { photoRoute };
}
