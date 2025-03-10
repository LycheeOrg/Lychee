import { ALL } from "@/config/constants";
import { Router } from "vue-router";

export function usePhotoRoute(router: Router) {
	function photoRoute(albumId: string | undefined, photoId: string) {
		const currentRoute = router.currentRoute.value.name as string;
		if (currentRoute.startsWith("search")) {
			return { name: "search-photo", params: { albumid: albumId ?? ALL, photoid: photoId } };
		}
		return { name: "photo", params: { albumid: albumId ?? ALL, photoid: photoId } };
	}

	return { photoRoute };
}
