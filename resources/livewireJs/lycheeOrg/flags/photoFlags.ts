import { OverlayTypes, Photo } from "@/lycheeOrg/backend";

export default class PhotoFlags {
	isDownloadOpen: boolean;
	isDetailsOpen: boolean;
	isEditOpen: boolean;
	overlayType: OverlayTypes;

	constructor(isDetailsOpen: boolean, overlayType: OverlayTypes = "none") {
		this.isDownloadOpen = false;
		this.isDetailsOpen = isDetailsOpen;
		this.overlayType = overlayType;
		this.isEditOpen = false;
	}

	rotateOverlay() {
		const photo = Alpine.store("photo") as Photo;
		switch (this.overlayType) {
			case "exif":
				this.overlayType = "date";
				break;
			case "date":
				if (photo.description !== "") {
					this.overlayType = "desc";
				} else {
					this.overlayType = "none";
				}
				break;
			case "desc":
				this.overlayType = "none";
				break;
			default:
				this.overlayType = "exif";
		}
	}
}
