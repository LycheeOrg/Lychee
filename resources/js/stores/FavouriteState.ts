import MetricsService from "@/services/metrics-service";
import { defineStore } from "pinia";

export type favouriteStore = ReturnType<typeof useFavouriteStore>;

export type PhotoFavourite = {
	albumId?: string;
	photoId: string;
	thumb?: string;
};

export const useFavouriteStore = defineStore("favourite-store", {
	state: () => ({
		// We define it as undefine to make sure that no matter what happens we are considering
		photos: undefined as App.Http.Resources.Models.PhotoResource[] | undefined,
	}),
	getters: {
		getPhotoIds(): string[] {
			return this.photos?.map((p) => p.id) ?? [];
		},
		getPhotos(): App.Http.Resources.Models.PhotoResource[] {
			return this.photos ?? [];
		},
	},
	actions: {
		addPhoto(photo: App.Http.Resources.Models.PhotoResource) {
			if (!this.photos) {
				this.photos = [];
			}
			this.photos.push(photo);
		},
		removePhoto(photoId: string) {
			if (!this.photos) {
				return;
			}
			this.photos = this.photos.filter((p: App.Http.Resources.Models.PhotoResource) => p.id !== photoId);
		},
		toggle(photo: App.Http.Resources.Models.PhotoResource) {
			if (!this.photos) {
				this.photos = [];
			}

			if (this.photos.some((p: App.Http.Resources.Models.PhotoResource) => p.id === photo.id)) {
				this.removePhoto(photo.id);
			} else {
				this.addPhoto(photo);
				MetricsService.favourite(photo.id);
			}
		},
	},
	persist: true,
});
