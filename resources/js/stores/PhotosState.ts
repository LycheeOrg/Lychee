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
	},
	getters: {},
});
