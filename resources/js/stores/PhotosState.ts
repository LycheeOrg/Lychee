import { defineStore } from "pinia";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
const { spliter, merge } = useSplitter();

export type PhotosStore = ReturnType<typeof usePhotosStore>;

export type PhotoRatingFilter = null | 1 | 2 | 3 | 4 | 5 | "starred";

export const usePhotosStore = defineStore("photos-store", {
	state: () => ({
		photos: [] as App.Http.Resources.Models.PhotoResource[],
		photosTimeline: undefined as SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined,
		photoRatingFilter: null as PhotoRatingFilter,
	}),
	actions: {
		reset() {
			this.photos = [];
			this.photosTimeline = undefined;
			this.photoRatingFilter = null;
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
		setPhotos(photos: App.Http.Resources.Models.PhotoResource[], isTimeline: boolean) {
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
		},
		/**
		 * Append new photos to the existing collection.
		 * Handles both timeline and non-timeline modes.
		 *
		 * Critical: Fixes navigation links (next_photo_id/previous_photo_id) between
		 * the last photo of the existing collection and the first photo of the new batch.
		 * Without this fix, navigating between photos would break at page boundaries.
		 */
		appendPhotos(photos: App.Http.Resources.Models.PhotoResource[], isTimeline: boolean) {
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
		},
	},
	getters: {
		starredPhotosCount(): number {
			return this.photos.filter((p) => p.is_starred).length;
		},
		/**
		 * Check if any photo in the collection has a user rating.
		 * Used to determine whether to show the rating filter UI.
		 */
		hasRatedPhotos(): boolean {
			return this.photos.some((p) => p.rating !== null && p.rating.rating_user > 0);
		},
		/**
		 * Get filtered photos based on the current rating filter or starred photos filter.
		 * Returns all photos if no filter is active or no photos matches the filter.
		 */
		filteredPhotos(): App.Http.Resources.Models.PhotoResource[] {
			if (this.photoRatingFilter === null) {
				return this.photos;
			}

			if (this.photoRatingFilter === "starred") {
				return this.photos.filter((p) => p.is_starred);
			}

			if (!this.hasRatedPhotos && !this.starredPhotosCount) {
				return this.photos;
			}
			return this.photos.filter((p) => p.rating !== null && p.rating.rating_user >= (this.photoRatingFilter as number));
		},
		/**
		 * Get filtered timeline data based on the current rating filter or starred photos filter.
		 * Returns undefined if no timeline data exists.
		 */
		filteredPhotosTimeline(): SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined {
			if (this.photosTimeline === undefined) {
				return undefined;
			}
			if (this.photoRatingFilter === "starred") {
				return this.photosTimeline
					.map((group) => ({
						...group,
						data: group.data.filter((p) => p.is_starred),
					}))
					.filter((group) => group.data.length > 0);
			}

			if (this.photoRatingFilter === null || (!this.hasRatedPhotos && !this.starredPhotosCount)) {
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
