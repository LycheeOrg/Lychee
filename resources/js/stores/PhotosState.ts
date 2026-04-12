import { defineStore } from "pinia";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
const { spliter, merge } = useSplitter();

export type PhotosStore = ReturnType<typeof usePhotosStore>;

export type PhotoRatingFilter = null | 1 | 2 | 3 | 4 | 5 | "highlighted";

export const usePhotosStore = defineStore("photos-store", {
	state: () => ({
		photos: [] as App.Http.Resources.Models.PhotoResource[],
		photosTimeline: undefined as SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined,
		photoRatingFilter: null as PhotoRatingFilter,
		/**
		 * Maps each loaded photo ID to the page number it was loaded from.
		 * Used by photoRoute() to include ?page=N in photo URLs so direct links
		 * open the correct page of a paginated album.
		 */
		photoPageMap: {} as Record<string, number>,
	}),
	actions: {
		reset() {
			this.photos = [];
			this.photosTimeline = undefined;
			this.photoRatingFilter = null;
			this.photoPageMap = {};
		},
		setPhotoRatingFilter(rating: PhotoRatingFilter) {
			this.photoRatingFilter = rating;
		},
		/**
		 * Rebuild navigation links for all photos based on their current order.
		 * This ensures next_photo_id and previous_photo_id are always correct,
		 * especially after timeline merge operations that reorder photos.
		 */
		rebuildNavigationLinks() {
			for (let i = 0; i < this.photos.length; i++) {
				const currentPhoto = this.photos[i];
				const previousPhoto = i > 0 ? this.photos[i - 1] : null;
				const nextPhoto = i < this.photos.length - 1 ? this.photos[i + 1] : null;

				currentPhoto.previous_photo_id = previousPhoto?.id ?? null;
				currentPhoto.next_photo_id = nextPhoto?.id ?? null;
			}
		},
		/**
		 * Record the page number for a batch of photos in photoPageMap.
		 */
		recordPhotoPages(photos: App.Http.Resources.Models.PhotoResource[], page: number) {
			photos.forEach((p) => {
				this.photoPageMap[p.id] = page;
			});
		},
		setPhotos(photos: App.Http.Resources.Models.PhotoResource[], isTimeline: boolean, page: number = 1) {
			if (isTimeline) {
				this.photosTimeline = spliter(
					photos,
					(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.time_date ?? "",
					(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
				);
				this.photos = merge(this.photosTimeline);
				// Rebuild navigation links after timeline merge since photos were reordered
				this.rebuildNavigationLinks();
			} else {
				// We are not using the timeline, so we can just use the photos as is.
				this.photos = photos;
				this.photosTimeline = undefined;
			}
			this.recordPhotoPages(photos, page);
		},
		/**
		 * Append new photos to the existing collection.
		 * Handles both timeline and non-timeline modes.
		 *
		 * Critical: Fixes navigation links (next_photo_id/previous_photo_id) between
		 * the last photo of the existing collection and the first photo of the new batch.
		 * Without this fix, navigating between photos would break at page boundaries.
		 */
		appendPhotos(photos: App.Http.Resources.Models.PhotoResource[], isTimeline: boolean, page: number = 1) {
			if (isTimeline) {
				// Append new photos to timeline and re-merge
				const newTimelinePhotos = spliter(
					photos,
					(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.time_date ?? "",
					(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
				);
				// Merge existing timeline with new timeline data
				if (this.photosTimeline) {
					// Append new timeline groups or merge into existing ones
					for (const newGroup of newTimelinePhotos) {
						const existingGroup = this.photosTimeline.find((g) => g.header === newGroup.header);
						if (existingGroup) {
							existingGroup.data = [...existingGroup.data, ...newGroup.data];
						} else {
							this.photosTimeline.push(newGroup);
						}
					}
				} else {
					this.photosTimeline = newTimelinePhotos;
				}
				this.photos = merge(this.photosTimeline);
				// Rebuild all navigation links after timeline merge since photos were reordered
				this.rebuildNavigationLinks();
			} else {
				// Remember where the old photos end so we can fix the boundary link
				const oldPhotoCount = this.photos.length;
				// Simply append photos to the existing array
				this.photos = [...this.photos, ...photos];

				// Fix navigation links at the boundary between old and new photos
				if (oldPhotoCount > 0 && oldPhotoCount < this.photos.length) {
					const lastOldPhoto = this.photos[oldPhotoCount - 1];
					const firstNewPhoto = this.photos[oldPhotoCount];

					// Connect the last old photo to the first new photo
					lastOldPhoto.next_photo_id = firstNewPhoto.id;
					// Connect the first new photo back to the last old photo
					firstNewPhoto.previous_photo_id = lastOldPhoto.id;
				}
			}
			this.recordPhotoPages(photos, page);
		},
		/**
		 * Prepend new photos to the beginning of the existing collection.
		 * Used when loading previous pages in background after jumping directly to a later page.
		 * Handles both timeline and non-timeline modes.
		 *
		 * Critical: Fixes navigation links (next_photo_id/previous_photo_id) between
		 * the last prepended photo and the first existing photo.
		 */
		prependPhotos(photos: App.Http.Resources.Models.PhotoResource[], isTimeline: boolean, page: number) {
			if (isTimeline) {
				const newTimelinePhotos = spliter(
					photos,
					(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.time_date ?? "",
					(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
				);
				// Prepend new timeline groups or merge into existing ones
				if (this.photosTimeline) {
					for (const newGroup of newTimelinePhotos) {
						const existingGroup = this.photosTimeline.find((g) => g.header === newGroup.header);
						if (existingGroup) {
							existingGroup.data = [...newGroup.data, ...existingGroup.data];
						} else {
							// Insert at the beginning (earlier pages have earlier/later dates depending on sort)
							this.photosTimeline.unshift(newGroup);
						}
					}
				} else {
					this.photosTimeline = newTimelinePhotos;
				}
				this.photos = merge(this.photosTimeline);
				// Rebuild all navigation links after timeline merge since photos were reordered
				this.rebuildNavigationLinks();
			} else {
				const oldPhotoCount = this.photos.length;
				// Prepend photos to the beginning of the array
				this.photos = [...photos, ...this.photos];

				// Fix navigation links within the prepended batch
				for (let i = 0; i < photos.length - 1; i++) {
					this.photos[i].next_photo_id = this.photos[i + 1].id;
					this.photos[i + 1].previous_photo_id = this.photos[i].id;
				}
				// Fix navigation links at the boundary between prepended and existing photos.
				// (Timeline mode uses rebuildNavigationLinks() for a full rebuild instead.)
				if (photos.length > 0 && oldPhotoCount > 0) {
					const lastPrependedPhoto = this.photos[photos.length - 1];
					const firstExistingPhoto = this.photos[photos.length];

					// Connect the last prepended photo to the first existing photo
					lastPrependedPhoto.next_photo_id = firstExistingPhoto.id;
					// Connect the first existing photo back to the last prepended photo
					firstExistingPhoto.previous_photo_id = lastPrependedPhoto.id;
				}
				if (photos.length > 0) {
					this.photos[0].previous_photo_id = null;
				}
			}
			this.recordPhotoPages(photos, page);
		},
	},
	getters: {
		highlightedPhotosCount(): number {
			return this.photos.filter((p) => p.is_highlighted).length;
		},
		/**
		 * Check if any photo in the collection has a user rating.
		 * Used to determine whether to show the rating filter UI.
		 */
		hasRatedPhotos(): boolean {
			return this.photos.some((p) => p.rating !== null && p.rating.rating_user > 0);
		},
		/**
		 * Get filtered photos based on the current rating filter or highlighted photos filter.
		 * Returns all photos if no filter is active or no photos matches the filter.
		 */
		filteredPhotos(): App.Http.Resources.Models.PhotoResource[] {
			if (this.photoRatingFilter === null) {
				return this.photos;
			}

			if (this.photoRatingFilter === "highlighted") {
				return this.photos.filter((p) => p.is_highlighted);
			}

			if (!this.hasRatedPhotos && !this.highlightedPhotosCount) {
				return this.photos;
			}
			return this.photos.filter((p) => p.rating !== null && p.rating.rating_user >= (this.photoRatingFilter as number));
		},
		/**
		 * Get filtered timeline data based on the current rating filter or highlighted photos filter.
		 * Returns undefined if no timeline data exists.
		 */
		filteredPhotosTimeline(): SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined {
			if (this.photosTimeline === undefined) {
				return undefined;
			}
			if (this.photoRatingFilter === "highlighted") {
				return this.photosTimeline
					.map((group) => ({
						...group,
						data: group.data.filter((p) => p.is_highlighted),
					}))
					.filter((group) => group.data.length > 0);
			}

			if (this.photoRatingFilter === null || (!this.hasRatedPhotos && !this.highlightedPhotosCount)) {
				return this.photosTimeline;
			}
			const filter = this.photoRatingFilter as number;
			return this.photosTimeline
				.map((group) => ({
					...group,
					data: group.data.filter((p) => p.rating !== null && p.rating.rating_user >= filter),
				}))
				.filter((group) => group.data.length > 0);
		},
	},
});
