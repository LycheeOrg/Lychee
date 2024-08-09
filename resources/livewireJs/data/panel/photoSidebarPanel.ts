import type { Alpine, AlpineComponent } from "alpinejs";
import { Photo, PreComputed, PreFormatted } from "@/lycheeOrg/backend";
import SidebarMap from "../../lycheeOrg/actions/sidebarMap";

export type PhotoSidebarPanel = AlpineComponent<{
	photo: Photo;
	preformatted: PreFormatted | null;
	precomputed: PreComputed | null;
	map: SidebarMap | null;

	refreshSidebar: (photo: Photo) => void;
	displayMap: () => void;
}>;

/**
 * This components updates the select property of albumView with the photos contains.
 * Otherwise we hare hitting race conditions between the rendering, class binding and the presence of properties in the component.
 */
export const photoSidebarPanel = (Alpine: Alpine) =>
	Alpine.data(
		"photoSidebarPanel",
		// @ts-expect-error
		(): PhotoSidebarPanel => ({
			init() {
				this.refreshSidebar(this.$store.photo as Photo);
			},

			refreshSidebar(photo: Photo) {
				this.photo = photo;
				this.precomputed = photo.precomputed;
				this.preformatted = photo.preformatted;
			},

			displayMap(): void {
				if (this.precomputed?.has_location) {
					this.map = new SidebarMap();
					this.map.displayOnMap(this.photo.latitude as number, this.photo.longitude as number, this.photo.img_direction);
				}
			},
		}),
	);
