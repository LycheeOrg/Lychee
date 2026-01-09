import { defineStore } from "pinia";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
const { spliter, merge } = useSplitter();

export type PhotosStore = ReturnType<typeof usePhotosStore>;

export const usePhotosStore = defineStore("photos-store", {
	state: () => ({
		photos: [] as App.Http.Resources.Models.PhotoResource[],
		photosTimeline: undefined as SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined,
	}),
	actions: {
		reset() {
			this.photos = [];
			this.photosTimeline = undefined;
		},
		setPhotos(photos: App.Http.Resources.Models.PhotoResource[], isTimeline: boolean) {
			if (isTimeline) {
				this.photosTimeline = spliter(
					photos,
					(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.time_date ?? "",
					(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
				);
				this.photos = merge(this.photosTimeline);
			} else {
				// We are not using the timeline, so we can just use the photos as is.
				this.photos = photos;
				this.photosTimeline = undefined;
			}
		},
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
			} else {
				// Simply append photos to the existing array
				this.photos = [...this.photos, ...photos];
			}
		},
	},
	getters: {},
});
