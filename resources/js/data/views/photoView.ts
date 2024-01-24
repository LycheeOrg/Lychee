import type { Alpine, AlpineComponent } from "alpinejs";
import Selection from "@/lycheeOrg/actions/selection";
import { Photo } from "@/lycheeOrg/backend";
import PhotoFlagsView from "@/lycheeOrg/flags/photoFlags";

export type PhotoView = AlpineComponent<{
	photoFlags: PhotoFlagsView;
	photo: Photo;
	// photo_id: string;
	parent_id: string;

	style: string;
	srcSetMedium: string;
	mode: number;

	refreshPhotoView: (photo: Photo) => void;

	previousStyle: () => string;
	nextStyle: () => string;
	displayMap(): void;

	imageViewMode: () => number;
	getSrcSetMedium: () => string;
	getStyle: () => string;
}>;

export const photoView = (Alpine: Alpine) =>
	Alpine.data(
		"photoView",
		(
			// photo_id: string,
			photoFlags: PhotoFlagsView,
			parent_id: string,
			// @ts-expect-error
		): PhotoView => ({
			// photo_id: photo_id,
			photoFlags: photoFlags,
			parent_id: parent_id,
			srcSetMedium: "",
			style: "",
			mode: 0,

			init() {
				console.log("init photoView!");

				const photo = Alpine.store("photo") as Photo | undefined;
				if (photo === undefined) {
					throw new Error("not found!");
				}
				this.photo = photo;
				console.log(photo);
				console.log("wtf");
				this.refreshPhotoView(photo);
			},

			refreshPhotoView(photo: Photo) {
				console.log("refresh!");
				console.log(photo);
				this.photo = photo;
				// cascade
				this.srcSetMedium = this.getSrcSetMedium();
				this.style = this.getStyle();
				this.mode = this.imageViewMode();
			},

			previousStyle(): string {
				const previousId = this.photo.previous_photo_id;
				if (previousId === null || previousId === undefined) {
					return "";
				}
				const previousPhoto = Selection.getPhoto(previousId);
				if (previousPhoto === undefined) {
					return "";
				}
				return (
					"background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + previousPhoto.size_variants.thumb?.url + "')"
				);
			},

			nextStyle(): string {
				const nextId = this.photo.next_photo_id;
				if (nextId === null || nextId === undefined) {
					return "";
				}
				const nextPhoto = Selection.getPhoto(nextId);
				if (nextPhoto === undefined) {
					return "";
				}
				return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + nextPhoto.size_variants.thumb?.url + "')";
			},

			imageViewMode(): number {
				if (this.photo.precomputed.is_video) {
					return 0;
				}
				if (this.photo.precomputed.is_raw) {
					return 1;
				}

				if (!this.photo.precomputed.is_livephoto) {
					if (this.photo.size_variants.medium !== null) {
						return 2;
					}
					return 3;
				}
				if (this.photo.size_variants.medium !== null) {
					return 4;
				}
				return 5;
			},

			getSrcSetMedium(): string {
				if (this.photo.size_variants.medium2x === null || this.photo.size_variants.medium === null) {
					return "";
				}

				return (
					this.photo.size_variants.medium.url +
					" " +
					this.photo.size_variants.medium.width +
					"w, " +
					this.photo.size_variants.medium2x.url +
					" " +
					this.photo.size_variants.medium2x.width +
					"w"
				);
			},

			getStyle(): string {
				if (!this.photo.precomputed.is_livephoto) {
					return "background-image: url(" + this.photo.size_variants.small?.url + ")";
				}
				if (this.photo.size_variants.medium !== null) {
					return "width: " + this.photo.size_variants.medium.width + "px; height: " + this.photo.size_variants.medium.height + "px";
				}
				return "width: " + this.photo.size_variants.original.width + "px; height: " + this.photo.size_variants.original.height + "px";
			},
		}),
	);
