import AlbumService from "@/services/album-service";
import { type AuthStore } from "@/stores/Auth";
import { type LycheeStateStore } from "@/stores/LycheeState";
import { computed, ref, type Ref } from "vue";
import { type SplitData, useSplitter } from "./splitter";
import { type Router } from "vue-router";

export function useAlbumsRefresher(auth: AuthStore, lycheeStore: LycheeStateStore, isLoginOpen: Ref<boolean>, router: Router) {
	const { spliter } = useSplitter();
	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);
	const isKeybindingsHelpOpen = ref(false);
	const isLoading = ref(false);
	const smartAlbums = ref<App.Http.Resources.Models.ThumbAlbumResource[]>([]);
	const albums = ref<App.Http.Resources.Models.ThumbAlbumResource[]>([]);
	const pinnedAlbums = ref<App.Http.Resources.Models.ThumbAlbumResource[]>([]);
	const unpinnedAlbums = ref<App.Http.Resources.Models.ThumbAlbumResource[]>([]);
	const sharedAlbums = ref<SplitData<App.Http.Resources.Models.ThumbAlbumResource>[]>([]);
	const rootConfig = ref<App.Http.Resources.GalleryConfigs.RootConfig | undefined>(undefined);
	const rootRights = ref<App.Http.Resources.Rights.RootAlbumRightsResource | undefined>(undefined);
	const selectableAlbums = computed(() =>
		pinnedAlbums.value.concat(unpinnedAlbums.value.concat(sharedAlbums.value.map((album) => album.data).flat())),
	); // selectableAlbums has to reflect the same order as pinned/unpinned albums
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
				smartAlbums.value = smartAlbums.value.concat(data.data.tag_albums as App.Http.Resources.Models.ThumbAlbumResource[]);
				sharedAlbums.value = spliter(
					(data.data.shared_albums as App.Http.Resources.Models.ThumbAlbumResource[]) ?? [],
					(d) => d.owner as string, // mapper
					(d) => d.owner as string, // formatter
					albums.value.length,
				);
				albums.value = data.data.albums as App.Http.Resources.Models.ThumbAlbumResource[];
				pinnedAlbums.value = [];
				unpinnedAlbums.value = [];
				for (const album of albums.value) {
					if (album.is_pinned) {
						pinnedAlbums.value.push(album);
					} else {
						unpinnedAlbums.value.push(album);
					}
				}

				rootConfig.value = data.data.config;
				rootRights.value = data.data.rights;

				// If we are not logged in and there are no albums, we redirect to the login page.
				if (
					(auth.user?.id === undefined || auth.user?.id === null) &&
					albums.value.length === 0 &&
					smartAlbums.value.length === 0 &&
					sharedAlbums.value.length === 0
				) {
					router.push({ name: "login" });
				}
			})
			.catch((error) => {
				// We are required to login :)
				// We use the modal instead of the login page to avoid the redirect back.
				// Once logged in, we just refresh the page.
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
		pinnedAlbums,
		unpinnedAlbums,
		sharedAlbums,
		rootConfig,
		rootRights,
		selectableAlbums,
		hasHidden,
		refresh,
	};
}
