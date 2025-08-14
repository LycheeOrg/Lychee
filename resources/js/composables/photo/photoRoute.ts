import { ALL } from "@/config/constants";
import { Router } from "vue-router";

export function usePhotoRoute(router: Router) {
	function getParentId(): string | undefined {
		return router.currentRoute.value.params.albumId as string | undefined;
	}

	function photoRoute(photoId: string) {
		const currentRoute = router.currentRoute.value.name as string;
		const albumId = getParentId();

		if (currentRoute.startsWith("search")) {
			return { name: "search", params: { albumId: albumId ?? ALL, photoId: photoId } };
		}

		if (currentRoute === "tag") {
			const tagId = router.currentRoute.value.params.tagId as string;
			return { name: "tag", params: { tagId, photoId } };
		}

		if (currentRoute.startsWith("flow")) {
			return { name: "flow-album", params: { albumId: albumId ?? ALL, photoId: photoId } };
		}

		return { name: "album", params: { albumId: albumId ?? ALL, photoId: photoId } };
	}

	return { getParentId, photoRoute };
}
