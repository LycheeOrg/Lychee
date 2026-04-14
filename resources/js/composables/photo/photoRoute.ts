import { ALL } from "@/config/constants";
import { Router } from "vue-router";
import { usePhotosStore } from "@/stores/PhotosState";

export function usePhotoRoute(router: Router) {
	function getParentId(): string | undefined {
		return router.currentRoute.value.params.albumId as string | undefined;
	}

	/**
	 * Build the route object for a given photo.
	 * For album and flow routes the ?page=N query param is included when the
	 * photo's page is known, so direct links always open the correct page.
	 */
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

		const photosStore = usePhotosStore();
		const page = photosStore.photoPageMap[photoId];
		// Only include ?page=N when the stored value is a valid positive integer
		const pageQuery = page !== undefined && Number.isInteger(page) && page >= 1 ? { page: String(page) } : {};

		if (currentRoute.startsWith("flow")) {
			return { name: "flow-album", params: { albumId: albumId ?? ALL, photoId: photoId }, query: pageQuery };
		}

		if (currentRoute.startsWith("timeline")) {
			return { name: "timeline", params: { date: router.currentRoute.value.params.date as string, photoId: photoId } };
		}

		return { name: "album", params: { albumId: albumId ?? ALL, photoId: photoId }, query: pageQuery };
	}

	return { getParentId, photoRoute };
}
