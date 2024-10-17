import AlbumService from "@/services/album-service";
import { AuthStore } from "@/stores/Auth";
import { LycheeStateStore } from "@/stores/LycheeState";
import { computed, ref, Ref } from "vue";

export function useAlbumsRefresher(auth: AuthStore, lycheeStore: LycheeStateStore, isLoginOpen: Ref<boolean>) {
	const user = ref(undefined) as Ref<undefined | App.Http.Resources.Models.UserResource>;
	const isKeybindingsHelpOpen = ref(false);
	const smartAlbums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
	const albums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
	const sharedAlbums = ref([]) as Ref<App.Http.Resources.Models.ThumbAlbumResource[]>;
	const rootConfig = ref(undefined) as Ref<undefined | App.Http.Resources.GalleryConfigs.RootConfig>;
	const rootRights = ref(undefined) as Ref<undefined | App.Http.Resources.Rights.RootAlbumRightsResource>;
	const selectableAlbums = computed(() => albums.value.concat(sharedAlbums.value));

	function refresh() {
		auth.getUser().then((data) => {
			user.value = data;

			const body_width = document.body.scrollWidth;
			// display popup if logged in and set..
			if (user.value.id && lycheeStore.show_keybinding_help_popup && body_width > 800) {
				isKeybindingsHelpOpen.value = true;
			}
		});

		AlbumService.getAll()
			.then((data) => {
				smartAlbums.value = (data.data.smart_albums as App.Http.Resources.Models.ThumbAlbumResource[]) ?? [];
				albums.value = data.data.albums as App.Http.Resources.Models.ThumbAlbumResource[];
				smartAlbums.value = smartAlbums.value.concat(data.data.tag_albums as App.Http.Resources.Models.ThumbAlbumResource[]);
				sharedAlbums.value = (data.data.shared_albums as App.Http.Resources.Models.ThumbAlbumResource[]) ?? [];
				rootConfig.value = data.data.config;
				rootRights.value = data.data.rights;

				if (albums.value.length === 0 && smartAlbums.value.length === 0 && sharedAlbums.value.length === 0) {
					isLoginOpen.value = true;
				}
			})
			.catch((error) => {
				// We are required to login :)
				if (error.response.status === 401) {
					isLoginOpen.value = true;
				} else {
					console.error(error);
				}
			});
	}

	return {
		user,
		isKeybindingsHelpOpen,
		smartAlbums,
		albums,
		sharedAlbums,
		rootConfig,
		rootRights,
		selectableAlbums,
		refresh,
	};
}
