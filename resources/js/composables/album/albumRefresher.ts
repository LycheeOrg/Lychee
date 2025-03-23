import { ALL } from "@/config/constants";
import AlbumService from "@/services/album-service";
import { AuthStore } from "@/stores/Auth";
import { computed, Ref, ref } from "vue";

export function useAlbumRefresher(albumId: Ref<string>, photoId: Ref<string | undefined>, auth: AuthStore, isLoginOpen: Ref<boolean>) {
	const isPasswordProtected = ref(false);
	const isLoading = ref(false);

	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);
	const modelAlbum = ref<App.Http.Resources.Models.AlbumResource | undefined>(undefined);
	const tagAlbum = ref<App.Http.Resources.Models.TagAlbumResource | undefined>(undefined);
	const smartAlbum = ref<App.Http.Resources.Models.SmartAlbumResource | undefined>(undefined);
	const album = computed(() => modelAlbum.value || tagAlbum.value || smartAlbum.value);

	const photo = ref<App.Http.Resources.Models.PhotoResource | undefined>(undefined);
	const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);

	const config = ref<App.Http.Resources.GalleryConfigs.AlbumConfig | undefined>(undefined);
	const rights = computed(() => album.value?.rights ?? undefined);

	function loadUser(): Promise<void> {
		return auth.getUser().then((data: App.Http.Resources.Models.UserResource) => {
			user.value = data;
		});
	}

	function loadAlbum(): Promise<void> {
		if (albumId.value === ALL) {
			return Promise.resolve();
		}

		isLoading.value = true;

		return AlbumService.get(albumId.value)
			.then((data) => {
				isPasswordProtected.value = false;
				config.value = data.data.config;
				modelAlbum.value = undefined;
				tagAlbum.value = undefined;
				smartAlbum.value = undefined;
				if (data.data.config.is_model_album) {
					modelAlbum.value = data.data.resource as App.Http.Resources.Models.AlbumResource;
				} else if (data.data.config.is_base_album) {
					tagAlbum.value = data.data.resource as App.Http.Resources.Models.TagAlbumResource;
				} else {
					smartAlbum.value = data.data.resource as App.Http.Resources.Models.SmartAlbumResource;
				}
				photos.value = album.value?.photos ?? [];
			})
			.catch((error) => {
				if (error.response && error.response.status === 401 && error.response.data.message === "Password required") {
					isPasswordProtected.value = true;
				} else if (error.response && error.response.status === 403 && error.response.data.message === "Password required") {
					isPasswordProtected.value = true;
				} else if (error.response && error.response.status === 401) {
					isLoginOpen.value = true;
				} else {
					console.error(error);
				}
			})
			.finally(() => {
				isLoading.value = false;
			});
	}

	function refresh(): Promise<void> {
		return Promise.all([loadUser(), loadAlbum()]).then(() => {
			if (photoId.value) {
				photo.value = photos.value.find((photo: App.Http.Resources.Models.PhotoResource) => photo.id === photoId.value);
			}
		});
	}

	return {
		isPasswordProtected,
		isLoading,
		albumId,
		user,
		modelAlbum,
		tagAlbum,
		smartAlbum,
		album,
		rights,
		photo,
		photos,
		config,
		loadUser,
		loadAlbum,
		refresh,
	};
}
