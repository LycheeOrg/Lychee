import TimelineService from "@/services/timeline-service";
import { AuthStore } from "@/stores/Auth";
import { AxiosError, AxiosResponse } from "axios";
import { Ref, ref } from "vue";
import { Router } from "vue-router";
import { useSplitter, type SplitData } from "@/composables/album/splitter";

const { spliter, merge } = useSplitter();

export function useTimelineRefresher(photoId: Ref<string | undefined>, router: Router, auth: AuthStore) {
	const isLoading = ref(false);
	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);

	const minPage = ref(0);
	const maxPage = ref(0);
	const lastPage = ref(0);
	const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);
	const photosTimeline = ref<SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined>(undefined);
	const photo = ref<undefined | App.Http.Resources.Models.PhotoResource>(undefined);

	const transition = ref<"slide-next" | "slide-previous">("slide-next");
	const layout = ref<App.Enum.PhotoLayoutType>("square");
	const isTimelineEnabled = ref(false);
	const rootConfig = ref<App.Http.Resources.GalleryConfigs.RootConfig | undefined>(undefined);
	const rootRights = ref<App.Http.Resources.Rights.RootAlbumRightsResource | undefined>(undefined);
	const dates = ref<App.Http.Resources.Models.Utils.TimelineData[]>([]);

	function loadTimelineConfig(): Promise<void> {
		return TimelineService.init().then((response) => {
			layout.value = response.data.photo_layout;
			isTimelineEnabled.value = response.data.is_timeline_page_enabled;
			rootConfig.value = response.data.config;
			rootRights.value = response.data.rights;
		});
	}

	function loadUser(): Promise<void> {
		return auth.getUser().then((data: App.Http.Resources.Models.UserResource) => {
			user.value = data;
		});
	}

	function initialLoad(date: string, photoId: string | undefined) {
		isLoading.value = true;
		if (photoId) {
			return TimelineService.photoIdedTimeline(photoId).then(_parseResponse).catch(_parseError);
		}

		return TimelineService.datedTimeline(date).then(_parseResponse).catch(_parseError);
	}

	function _parseResponse(response: AxiosResponse<App.Http.Resources.Timeline.TimelineResource>) {
		_processPhotos(response.data.photos);
		lastPage.value = response.data.last_page;
		maxPage.value = response.data.current_page;
		minPage.value = response.data.current_page;
		isLoading.value = false;
		loadDate();
		loadPhoto();
	}

	function _parseError(error: AxiosError<void>) {
		isLoading.value = false;
		if (error?.response?.status === 401) {
			router.push({ name: "gallery" });
		}
	}

	function loadLess() {
		if (minPage.value === 1) {
			return;
		}
		isLoading.value = true;
		minPage.value -= 1;
		return TimelineService.timeline(minPage.value)
			.then((response) => {
				photos.value.unshift(...response.data.photos);
				_processPhotos(photos.value);
				isLoading.value = false;
			})
			.catch((error) => {
				isLoading.value = false;
				if (error.response.status === 401) {
					router.push({ name: "gallery" });
				}
			});
	}

	function loadMore() {
		if (maxPage.value > lastPage.value) {
			return;
		}
		isLoading.value = true;
		maxPage.value += 1;
		return TimelineService.timeline(maxPage.value)
			.then((response) => {
				photos.value.push(...response.data.photos);
				_processPhotos(photos.value);
				lastPage.value = response.data.last_page;
				isLoading.value = false;
			})
			.catch((error) => {
				isLoading.value = false;
				if (error.response.status === 401) {
					router.push({ name: "gallery" });
				}
			});
	}

	function _processPhotos(photos_data: App.Http.Resources.Models.PhotoResource[]) {
		photosTimeline.value = spliter(
			photos_data,
			(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.time_date ?? "",
			(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
		);
		photos.value = merge(photosTimeline.value);
	}

	function loadDates() {
		return TimelineService.dates().then((response) => {
			dates.value = response.data;
		});
	}

	function loadPhoto() {
		if (photoId.value) {
			photo.value = photos.value.find((photo: App.Http.Resources.Models.PhotoResource) => photo.id === photoId.value);
		}
	}

	function loadDate(date: string | null = null) {
		if (date === null && router.currentRoute.value.params.date === undefined) {
			// We push the first date of the timeline, this ensures that the timeline is always loaded with a date
			router.push({ name: "timeline", params: { date: photos.value[0].timeline?.time_date } });
		}

		if (date) {
			router.push({ name: "timeline", params: { date } });
		}
	}

	function setTransition(photo_id: string | undefined | null) {
		if (photo_id === undefined || photo_id === null) {
			return;
		}

		if (photo.value !== undefined) {
			transition.value = photo.value.next_photo_id === photo_id ? "slide-next" : "slide-previous";
		} else {
			transition.value = "slide-next";
		}
	}

	return {
		user,
		dates,
		rootConfig,
		rootRights,
		isLoading,
		maxPage,
		minPage,
		lastPage,
		photos,
		photosTimeline,
		transition,
		photo,
		layout,
		isTimelineEnabled,
		loadTimelineConfig,
		initialLoad,
		loadLess,
		loadMore,
		loadDate,
		loadDates,
		loadUser,
		loadPhoto,
		setTransition,
	};
}
