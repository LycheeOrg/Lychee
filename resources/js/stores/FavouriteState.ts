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
		photos: undefined as PhotoFavourite[] | undefined,
	}),
	getters: {
		getPhotoIds(): string[] {
			return this.photos?.map((p) => p.photoId) ?? [];
		},
		getPhotos(): PhotoFavourite[] {
			return this.photos ?? [];
		},
	},
	actions: {
		addPhoto(photo: App.Http.Resources.Models.PhotoResource) {
			if (!this.photos) {
				this.photos = [];
			}
			this.photos.push({
				photoId: photo.id,
				albumId: photo.album_id ?? undefined,
				thumb:
					photo.size_variants.small2x?.url ??
					photo.size_variants.small?.url ??
					photo.size_variants.thumb2x?.url ??
					photo.size_variants.thumb?.url ??
					undefined,
			});
		},
		removePhoto(photoId: string) {
			if (!this.photos) {
				return;
			}
			this.photos = this.photos.filter((p: PhotoFavourite) => p.photoId !== photoId);
		},
		toggle(photo: App.Http.Resources.Models.PhotoResource) {
			if (!this.photos) {
				this.photos = [];
			}

			if (this.photos.some((p: PhotoFavourite) => p.photoId === photo.id)) {
				this.removePhoto(photo.id);
			} else {
				this.addPhoto(photo);
			}
		},
	},
	persist: true,
});
