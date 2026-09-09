import { defineStore } from "pinia";
import { usePhotosStore } from "./PhotosState";
import { useAlbumStore } from "./AlbumState";

export enum ImageViewMode {
	Original = "original",
	Medium = "medium",
	Raw = "raw",
	Video = "video",
	LivePhotoMedium = "livephoto-medium",
	LivePhotoOriginal = "livephoto-original",
	Pdf = "pdf",
}

export type PhotoStore = ReturnType<typeof usePhotoStore>;

export const usePhotoStore = defineStore("photo-store", {
	state: () => ({
		photoId: undefined as string | undefined,
		photo: undefined as App.Http.Resources.Models.PhotoResource | undefined,
		transition: "slide-next" as "slide-next" | "slide-previous",
	}),
	actions: {
		reset() {
			this.photoId = undefined;
			this.photo = undefined;
			this.transition = "slide-next";
		},
		setTransition(photo_id: string | undefined | null) {
			if (photo_id === undefined || photo_id === null) {
				return;
			}

			if (this.photo !== undefined) {
				this.transition = this.photo.next_photo_id === photo_id ? "slide-next" : "slide-previous";
			} else {
				this.transition = "slide-next";
			}
		},
		load() {
			if (this.photoId === undefined) {
				this.photo = undefined;
				return;
			}

			if (this.photo?.id === this.photoId) {
				// Already loaded
				return;
			}

			const photosState = usePhotosStore();
			if (photosState.photos.length === 0) {
				this.photo = undefined;
				return;
			}
			this.photo = photosState.photos.find((p) => p.id === this.photoId);

			// On-demand tier-3 (`details`) fetch (G5), gated on `isPhotoSoaActive`
			// (Q-065-05, Option A): only fires on the SoA path, where the tile
			// found above is a lightweight, `ratios`-derived object still
			// missing description/EXIF/size-variant-URL/etc. On the v2 path
			// (or a TagAlbum/PersonAlbum/filtered view) `this.photo` is already
			// a full `PhotoResource` — calling `loadPhotoDetails()` there would
			// either 404 (Feature 064's endpoints reject non-Album parents) or
			// be a wasted round trip against already-complete data, so it is
			// never called at all in that case. Neighbor ids improve
			// swipe-transition preview quality, mirroring how v2's own
			// already-fully-loaded page gives adjacent photos' full data for
			// free — `previous_photo_id`/`next_photo_id` are already correct at
			// this point (`loadPhotosV3()` runs `rebuildNavigationLinks()`
			// immediately after populating the array).
			if (this.photo !== undefined) {
				const albumStore = useAlbumStore();
				if (albumStore.isPhotoSoaActive) {
					const ids = [this.photo.id, this.photo.previous_photo_id, this.photo.next_photo_id].filter(
						(id): id is string => id !== null && id !== undefined,
					);
					void albumStore.loadPhotoDetails(ids);
				}
			}
		},
	},
	getters: {
		isLoaded(): boolean {
			return this.photo !== undefined;
		},
		hasPrevious(): boolean {
			return this.photo?.previous_photo_id !== null && this.photo?.previous_photo_id !== undefined;
		},
		hasNext(): boolean {
			return this.photo?.next_photo_id !== null && this.photo?.next_photo_id !== undefined;
		},
		// For displaying purposes
		style(): string {
			if (!this.photo?.precomputed.is_livephoto) {
				return `background-image: url(${this.photo?.size_variants.small?.url})`;
			}
			if (this.photo?.size_variants.medium !== null) {
				return `width: ${this.photo?.size_variants.medium.width}px; height: ${this.photo?.size_variants.medium.height}px`;
			}
			if (this.photo?.size_variants.original === null) {
				return "";
			}
			return `width: ${this.photo?.size_variants.original.width}px; height: ${this.photo?.size_variants.original.height}px`;
		},
		imageViewMode(): ImageViewMode {
			if (this.photo?.precomputed.is_video) {
				return ImageViewMode.Video;
			}

			if (this.photo?.precomputed.is_raw) {
				if (this.photo?.size_variants.medium !== null) {
					return ImageViewMode.Medium;
				}
				if (this.photo?.size_variants.original?.url?.endsWith(".pdf")) {
					return ImageViewMode.Pdf;
				}
				return ImageViewMode.Raw;
			}

			if (this.photo?.precomputed.is_livephoto === true) {
				if (this.photo?.size_variants.medium !== null) {
					return ImageViewMode.LivePhotoMedium;
				}
				return ImageViewMode.LivePhotoOriginal;
			}

			if (this.photo?.size_variants.medium !== null) {
				return ImageViewMode.Medium;
			}
			return ImageViewMode.Original;
		},
		srcSetMedium(): string {
			const medium = this.photo?.size_variants.medium ?? null;
			const medium2x = this.photo?.size_variants.medium2x ?? null;
			if (medium === null || medium2x === null) {
				return "";
			}

			return `${medium.url} ${medium.width}w, ${medium2x.url} ${medium2x.width}w`;
		},
		previousStyle(): string {
			const photosState = usePhotosStore();
			if (!this.hasPrevious || photosState.photos.length === 0) {
				return "";
			}

			const previousId = this.photo?.previous_photo_id;
			const previousPhoto = photosState.photos.find((p) => p.id === previousId);
			if (previousPhoto === undefined) {
				return "";
			}
			return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + previousPhoto.size_variants.thumb?.url + "')";
		},

		nextStyle(): string {
			const photosState = usePhotosStore();
			if (!this.hasNext || photosState.photos.length === 0) {
				return "";
			}

			const nextId = this.photo?.next_photo_id;
			const nextPhoto = photosState.photos.find((p) => p.id === nextId);
			if (nextPhoto === undefined) {
				return "";
			}
			return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + nextPhoto.size_variants.thumb?.url + "')";
		},
	},
});
