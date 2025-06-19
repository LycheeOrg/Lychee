import { ALL } from "@/config/constants";
import { Router } from "vue-router";

export function useAlbumRoute(router: Router) {
	function albumRoutes(): { home: string; album: string } {
		const currentRoute = router.currentRoute.value.name as string;

		if (currentRoute.startsWith("flow")) {
			return {
				home: "flow",
				album: "flow-album",
			};
		}

		return { home: "gallery", album: "album" };
	}

	return { albumRoutes };
}
