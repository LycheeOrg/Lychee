import type { Alpine, AlpineComponent } from "alpinejs";
import { Photo } from "@/lycheeOrg/backend";
import { PhotoView } from "../views/photoView";

export type PhotoFormPanel = AlpineComponent<{
	photo: Photo; // Object Proxy link
	photoID: string;
	title: string;
	description: string;
	tagsWithComma: string;
	uploadDate: string;
	uploadTz: string;
	license: string;
	photoView: PhotoView;

	updatePhoto: () => Promise<void>;
	refreshForm: (photo: Photo) => void;
}>;

/**
 * This components updates the select property of albumView with the photos contains.
 * Otherwise we hare hitting race conditions between the rendering, class binding and the presence of properties in the component.
 */
export const photoFormPanel = (Alpine: Alpine) =>
	Alpine.data(
		"photoFormPanel",
		// @ts-expect-error
		(): PhotoFormPanel => ({
			init() {
				this.photo = Alpine.store("photo") as Photo;
				this.refreshForm(this.photo);
			},

			refreshForm(data: Photo) {
				this.photoID = data.id;
				this.title = data.title;
				this.description = data.description ?? "";
				this.tagsWithComma = data.tags.join(", ");
				this.uploadDate = (data.created_at ?? "").slice(0, 16);
				this.uploadTz = (data.created_at ?? "").slice(17);
				this.license = data.license;
			},

			async updatePhoto(): Promise<void> {
				const formData = {
					photoID: this.photoID,
					title: this.title,
					description: this.description,
					tagsWithComma: this.tagsWithComma,
					uploadDate: this.uploadDate,
					uploadTz: this.uploadTz,
					license: this.license,
				};

				// @ts-expect-error
				const data = await this.$wire.updatePhoto(formData);
				if (data !== null) {
					this.photo.title = data.title;
					this.photo.license = data.license;
					this.photo.description = data.description;
					this.photo.created_at = data.created_at;
					this.photo.tags = data.tags;

					// refresh data
					this.refreshForm(data);
				}
			},
		}),
	);
