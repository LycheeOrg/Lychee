import AlbumService from "@/services/album-service";
import { AuthStore } from "@/stores/Auth";
import { computed, Ref, ref } from "vue";
import axios, { AxiosError } from 'axios'

export function useAlbumRefresher(albumId: Ref<string>, auth: AuthStore, isLoginOpen: Ref<boolean>, nsfw_consented: Ref<string[]>) {
	const isPasswordProtected = ref(false);
	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);
	const modelAlbum = ref<App.Http.Resources.Models.AlbumResource | undefined>(undefined);
	const tagAlbum = ref<App.Http.Resources.Models.TagAlbumResource | undefined>(undefined);
	const smartAlbum = ref<App.Http.Resources.Models.SmartAlbumResource | undefined>(undefined);
	const album = computed(() => modelAlbum.value || tagAlbum.value || smartAlbum.value);
	const isAlbumConsented = ref(false);

	const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);
	const config = ref<App.Http.Resources.GalleryConfigs.AlbumConfig | undefined>(undefined);
	const rights = computed(() => album.value?.rights ?? undefined);

	async function loadUser(): Promise<void> {
		const userResponse = await auth.getUser();
		user.value = userResponse;
		await loadAlbum();
	}

	async function loadAlbum(): Promise<void> {
		try {
			const albumResponse = await AlbumService.get(albumId.value);

			isPasswordProtected.value = false;
			config.value = albumResponse.data.config;
			modelAlbum.value = undefined;
			tagAlbum.value = undefined;
			smartAlbum.value = undefined;
			if (albumResponse.data.config.is_model_album) {
				modelAlbum.value = albumResponse.data.resource as App.Http.Resources.Models.AlbumResource;
			} else if (albumResponse.data.config.is_base_album) {
				tagAlbum.value = albumResponse.data.resource as App.Http.Resources.Models.TagAlbumResource;
			} else {
				smartAlbum.value = albumResponse.data.resource as App.Http.Resources.Models.SmartAlbumResource;
			}
			photos.value = album.value?.photos ?? [];
			isAlbumConsented.value = nsfw_consented.value.find((e) => e === albumId.value) !== undefined;
		} catch (error: unknown) {
			if (axios.isAxiosError(error)) {
				const axiosError = error as AxiosError;
				if (axiosError.response?.status === 401 && axiosError.response?.data?.message === "Password required") {
					isPasswordProtected.value = true;
				} else if (axiosError.response?.status === 403 && axiosError.response?.data?.message === "Password required") {
					isPasswordProtected.value = true;
				} else if (axiosError.response?.status === 401) {
					isLoginOpen.value = true;
				}
			} else {
				console.error(error);
			}
		}
	}

	function refresh(): Promise<void> {
		return loadUser();
	}

	return {
		isAlbumConsented,
		isPasswordProtected,
		albumId,
		user,
		modelAlbum,
		tagAlbum,
		smartAlbum,
		album,
		rights,
		photos,
		config,
		loadUser,
		loadAlbum,
		refresh,
	};
}
