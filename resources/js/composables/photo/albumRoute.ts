import { Router } from "vue-router";

export function useAlbumRoute(router: Router) {
	function albumRoutes(): { home: string; album: string } {
		const currentRoute = router.currentRoute.value.name as string;
		const currentPhotoId = router.currentRoute.value.params.photoId as string | undefined;

		if (currentRoute.startsWith("flow")) {
			return {
				home: "flow",
				album: "flow-album",
			};
		}

		if (currentRoute.startsWith("tag") && currentPhotoId !== undefined) {
			return { home: "tags", album: "tag" };
		}

		if (currentRoute.startsWith("tag") && currentPhotoId === undefined) {
			return { home: "tags", album: "album" };
		}

		return { home: "gallery", album: "album" };
	}

	return { albumRoutes };
}
