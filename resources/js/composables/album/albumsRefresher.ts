import AlbumService from "@/services/album-service";
import { AuthStore } from "@/stores/Auth";
import { LycheeStateStore } from "@/stores/LycheeState";
import { computed, ref, Ref } from "vue";
import { SplitData, useSplitter } from "./splitter";

export function useAlbumsRefresher(auth: AuthStore, lycheeStore: LycheeStateStore, isLoginOpen: Ref<boolean>) {
	const { spliter } = useSplitter();
	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);
	const isKeybindingsHelpOpen = ref(false);
	const isLoading = ref(false);
	const smartAlbums = ref<App.Http.Resources.Models.ThumbAlbumResource[]>([]);
	const albums = ref<App.Http.Resources.Models.ThumbAlbumResource[]>([]);
	const sharedAlbums = ref<SplitData<App.Http.Resources.Models.ThumbAlbumResource>[]>([]);
	const rootConfig = ref<App.Http.Resources.GalleryConfigs.RootConfig | undefined>(undefined);
	const rootRights = ref<App.Http.Resources.Rights.RootAlbumRightsResource | undefined>(undefined);
	const selectableAlbums = computed(() => albums.value.concat(sharedAlbums.value.map((album) => album.data).flat()));
	const hasHidden = computed(() => selectableAlbums.value.filter((album) => album.is_nsfw).length > 0);

	function refresh(): Promise<[void, void]> {
		isLoading.value = true;

		const getUser = auth.getUser().then((data) => {
			user.value = data;

			const body_width = document.body.scrollWidth;
			// display popup if logged in and set..
			if (user.value.id && lycheeStore.show_keybinding_help_popup && body_width > 800) {
				isKeybindingsHelpOpen.value = true;
			}
		});

		const getAlbums = AlbumService.getAll()
			.then((data) => {
				smartAlbums.value = (data.data.smart_albums as App.Http.Resources.Models.ThumbAlbumResource[]) ?? [];
				albums.value = data.data.albums as App.Http.Resources.Models.ThumbAlbumResource[];
				smartAlbums.value = smartAlbums.value.concat(data.data.tag_albums as App.Http.Resources.Models.ThumbAlbumResource[]);
				sharedAlbums.value = spliter(
					(data.data.shared_albums as App.Http.Resources.Models.ThumbAlbumResource[]) ?? [],
					(d) => d.owner as string, // mapper
					(d) => d.owner as string, // formatter
					albums.value.length,
				);

				rootConfig.value = data.data.config;
				rootRights.value = data.data.rights;

				if (albums.value.length === 0 && smartAlbums.value.length === 0 && sharedAlbums.value.length === 0) {
					isLoginOpen.value = true;
				}
			})
			.catch((error) => {
				// We are required to login :)
				if (error.response && error.response.status === 401) {
					isLoginOpen.value = true;
					console.error("require login");
				} else {
					console.error(error);
				}
			});

		return Promise.all([getUser, getAlbums]).finally(() => {
			isLoading.value = false;
		});
	}

	return {
		user,
		isKeybindingsHelpOpen,
		isLoading,
		smartAlbums,
		albums,
		sharedAlbums,
		rootConfig,
		rootRights,
		selectableAlbums,
		hasHidden,
		refresh,
	};
}
