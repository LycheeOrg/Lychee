import { ALL } from "@/config/constants";
import { Router } from "vue-router";

export function usePhotoRoute(router: Router) {
	function photoRoute(photoId: string) {
		const currentRoute = router.currentRoute.value.name as string;
		if (currentRoute.startsWith("search")) {
			return { name: "search-photo", params: { albumid: router.currentRoute.value.params.albumId ?? ALL, photoid: photoId } };
		}
		if (currentRoute.startsWith("timeline")) {
			return { name: "timeline-with-photo", params: { date: router.currentRoute.value.params.date as string, photoId: photoId } };
		}

		const albumId = router.currentRoute.value.params.albumId as string | undefined;
		return { name: "photo", params: { albumid: albumId ?? ALL, photoid: photoId } };
	}

	return { photoRoute };
}
