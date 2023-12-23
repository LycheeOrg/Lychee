import type { Alpine, AlpineComponent } from "alpinejs";
import Selection from "@/lycheeOrg/actions/selection";
import { Photo } from "@/lycheeOrg/backend";

export type PhotoListingPanel = AlpineComponent<{
	select: Selection;
	photos: Photo[];
}>;

/**
 * This components updates the select property of albumView with the photos contains.
 * Otherwise we hare hitting race conditions between the rendering, class binding and the presence of properties in the component.
 */
export const photoListingPanel = (Alpine: Alpine) =>
	Alpine.data(
		"photoListingPanel",
		// @ts-expect-error
		(photos: Photo[], select: Selection): PhotoListingPanel => ({
			photos: photos,
			select: select,

			init() {
				select.updatePhotos(photos);
			},
		}),
	);
