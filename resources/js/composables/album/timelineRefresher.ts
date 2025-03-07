import TimelineService from "@/services/timeline-service";
import { AuthStore } from "@/stores/Auth";
import { Ref, ref } from "vue";
import { Router } from "vue-router";

export function useTimelineRefresher(photoId: Ref<string | undefined>, router: Router, auth: AuthStore) {
	const isLoading = ref(false);
	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);

	const minPage = ref(0);
	const maxPage = ref(0);
	const lastPage = ref(0);
	const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);
	const photo = ref<undefined | App.Http.Resources.Models.PhotoResource>(undefined);

	const layout = ref<App.Enum.PhotoLayoutType>("square");
	const isTimelineEnabled = ref(false);
	const rootConfig = ref<App.Http.Resources.GalleryConfigs.RootConfig | undefined>(undefined);
	const rootRights = ref<App.Http.Resources.Rights.RootAlbumRightsResource | undefined>(undefined);
	const dates = ref<string[]>([]);

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

	function initialLoad(date: string) {
		isLoading.value = true;
		return TimelineService.datedTimeline(date)
			.then((response) => {
				photos.value = response.data.photos;
				lastPage.value = response.data.last_page;
				maxPage.value = response.data.current_page;
				minPage.value = response.data.current_page;
				isLoading.value = false;
				loadPhoto();
			})
			.catch((error) => {
				isLoading.value = false;
				if (error.response.status === 401) {
					router.push({ name: "gallery" });
				}
			});
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

	return {
		user,
		rootConfig,
		rootRights,
		isLoading,
		maxPage,
		minPage,
		lastPage,
		photos,
		photo,
		layout,
		isTimelineEnabled,
		loadTimelineConfig,
		initialLoad,
		loadLess,
		loadMore,
		loadDates,
		loadUser,
		loadPhoto,
	};
}
