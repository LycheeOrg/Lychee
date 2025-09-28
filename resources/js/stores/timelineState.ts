import { defineStore } from "pinia";
import { PhotosStore, usePhotosStore } from "./PhotosState";
import { useLayoutStore } from "./LayoutState";
import TimelineService from "@/services/timeline-service";
import { AxiosResponse } from "axios";
import { useSplitter } from "@/composables/album/splitter";

const { spliter, merge } = useSplitter();

export type TimelineStore = ReturnType<typeof useTimelineStore>;

function _parseResponse(
	response: AxiosResponse<App.Http.Resources.Timeline.TimelineResource>,
	timelineState: TimelineStore,
	photosState: PhotosStore,
) {
	_processPhotos(response.data.photos, photosState);
	timelineState.lastPage = response.data.last_page;
	timelineState.maxPage = response.data.current_page;
	timelineState.minPage = response.data.current_page;
	timelineState.isLoading = false;
}

function _processPhotos(photos_data: App.Http.Resources.Models.PhotoResource[], photosState: PhotosStore) {
	photosState.photosTimeline = spliter(
		photos_data,
		(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.time_date ?? "",
		(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
	);
	photosState.photos = merge(photosState.photosTimeline);
}

export const useTimelineStore = defineStore("timeline-store", {
	state: () => ({
		isLoading: false,
		minPage: 0,
		maxPage: 0,
		lastPage: 0,

		isTimelineEnabled: undefined as undefined | boolean,
		layout: undefined as undefined | App.Enum.PhotoLayoutType,
		rootConfig: undefined as undefined | App.Http.Resources.GalleryConfigs.RootConfig,
		rootRights: undefined as undefined | App.Http.Resources.Rights.RootAlbumRightsResource,

		dates: [] as App.Http.Resources.Models.Utils.TimelineData[],
	}),
	actions: {
		load(): Promise<void> {
			const layoutState = useLayoutStore();

			if (this.layout !== undefined) {
				layoutState.layout = this.layout;
				return Promise.resolve();
			}
			return TimelineService.init().then((response) => {
				this.isTimelineEnabled = response.data.is_timeline_page_enabled;
				this.layout = response.data.photo_layout;
				this.rootConfig = response.data.config;
				this.rootRights = response.data.rights;
				layoutState.layout = this.layout;
			});
		},
		loadLess(): Promise<void> {
			const photosState = usePhotosStore();

			const prevPage = this.minPage - 1;
			if (prevPage < 1) {
				return Promise.resolve();
			}
			this.isLoading = true;
			return TimelineService.timeline(prevPage)
				.then((response) => {
					this.minPage -= 1;
					photosState.photos.unshift(...response.data.photos);
					_processPhotos(photosState.photos, photosState);
				})
				.finally(() => {
					this.isLoading = false;
				});
		},
		loadMore(): Promise<void> {
			const photosState = usePhotosStore();

			const nextPage = this.maxPage + 1;
			if (this.lastPage !== 0 && nextPage > this.lastPage) {
				return Promise.resolve();
			}
			this.isLoading = true;
			return TimelineService.timeline(nextPage)
				.then((response) => {
					this.maxPage = nextPage;
					photosState.photos.push(...response.data.photos);
					_processPhotos(photosState.photos, photosState);
					this.lastPage = response.data.last_page;
				})
				.finally(() => {
					this.isLoading = false;
				});
		},
		initialLoad(date: string, photoId: string | undefined): Promise<void> {
			const photosState = usePhotosStore();
			this.isLoading = true;
			if (photoId) {
				return TimelineService.photoIdedTimeline(photoId)
					.then((data) => _parseResponse(data, this, photosState))
					.finally(() => (this.isLoading = false));
			}

			return TimelineService.datedTimeline(date)
				.then((data) => _parseResponse(data, this, photosState))
				.finally(() => (this.isLoading = false));
		},
		loadDates(): Promise<void> {
			if (this.dates.length > 0) {
				return Promise.resolve();
			}
			return TimelineService.dates().then((response) => {
				this.dates = response.data;
			});
		},
	},
});
