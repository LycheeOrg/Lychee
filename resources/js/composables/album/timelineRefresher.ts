import TimelineService from "@/services/timeline-service";
import { AuthStore } from "@/stores/Auth";
import { ref } from "vue";
import { Router } from "vue-router";

export function useTimelineRefresher(router: Router, auth: AuthStore) {
	const isLoading = ref(false);
	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);

	const page = ref(0);
	const lastPage = ref(0);
	const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);

	const layout = ref<App.Enum.PhotoLayoutType>("square");
	const isTimelineEnabled = ref(false);
	const rootConfig = ref<App.Http.Resources.GalleryConfigs.RootConfig | undefined>(undefined);
	const rootRights = ref<App.Http.Resources.Rights.RootAlbumRightsResource | undefined>(undefined);

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

	function loadMore() {
		if (page.value > lastPage.value) {
			return;
		}
		isLoading.value = true;
		page.value += 1;
		return TimelineService.timeline(page.value)
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

	return {
		user,
		rootConfig,
		rootRights,
		isLoading,
		page,
		lastPage,
		photos,
		layout,
		isTimelineEnabled,
		loadTimelineConfig,
		loadMore,
		loadUser,
	};
}
