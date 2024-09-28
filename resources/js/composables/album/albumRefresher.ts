import AlbumService from "@/services/album-service";
import { AuthStore } from "@/stores/Auth";
import { computed, Ref, ref } from "vue";

export function useAlbumRefresher(albumId: Ref<string>, auth: AuthStore, isLoginOpen: Ref<boolean>) {
	const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;
	const modelAlbum = ref(undefined as undefined | App.Http.Resources.Models.AlbumResource);
	const tagAlbum = ref(undefined as undefined | App.Http.Resources.Models.TagAlbumResource);
	const smartAlbum = ref(undefined as undefined | App.Http.Resources.Models.SmartAlbumResource);
	const album = computed(() => modelAlbum.value || tagAlbum.value || smartAlbum.value);
	const layout = ref(null) as Ref<null | App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>;

	const photos = ref([]) as Ref<App.Http.Resources.Models.PhotoResource[]>;
	const config = ref(undefined) as Ref<undefined | App.Http.Resources.GalleryConfigs.AlbumConfig>;

	function loadUser() {
		auth.getUser().then((data: App.Http.Resources.Models.UserResource) => {
			user.value = data;
			loadAlbum();
		});
	}

	function loadAlbum() {
		AlbumService.get(albumId.value)
			.then((data) => {
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
				// We are required to login :)
				if (error.response.status === 401 && user.value?.id === null) {
					isLoginOpen.value = true;
				} else {
					console.error(error);
				}
			});
	}

	function loadLayout() {
		AlbumService.getLayout().then((data) => {
			layout.value = data.data;
		});
	}

	function refresh() {
		loadUser();
	}

	return {
		albumId,
		user,
		modelAlbum,
		tagAlbum,
		smartAlbum,
		album,
		layout,
		photos,
		config,
		loadUser,
		loadAlbum,
		loadLayout,
		refresh,
	};
}
