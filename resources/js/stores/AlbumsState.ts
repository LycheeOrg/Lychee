// import { ALL } from "@/config/constants";
// import AlbumService from "@/services/album-service";
import { defineStore } from "pinia";
// import { useTogglablesStateStore } from "./ModalsState";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
import AlbumService from "@/services/album-service";
import { useTogglablesStateStore } from "./ModalsState";
import { useUserStore } from "./UserState";
import { Router } from "vue-router";
import InitService from "@/services/init-service";

const { spliter } = useSplitter();

export type { SplitData };
export type AlbumsStore = ReturnType<typeof useAlbumsStore>;

export const useAlbumsStore = defineStore("albums-store", {
	state: () => ({
		isLoading: false as boolean,
		baseSmartAlbums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		tagAlbums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		albums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		pinnedAlbums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		sharedAlbums: [] as SplitData<App.Http.Resources.Models.ThumbAlbumResource>[],
		rootConfig: undefined as App.Http.Resources.GalleryConfigs.RootConfig | undefined,
		rootRights: undefined as App.Http.Resources.Rights.RootAlbumRightsResource | undefined,
	}),
	getters: {
		smartAlbums(state): App.Http.Resources.Models.ThumbAlbumResource[] {
			return state.baseSmartAlbums.concat(state.tagAlbums);
		},
		// We use state here because we want the RETURN type inference
		selectableAlbums(state): App.Http.Resources.Models.ThumbAlbumResource[] {
			// Note that selectableAlbums has to reflect the same order as pinned/unpinned albums
			return state.pinnedAlbums.concat(state.albums.concat(state.sharedAlbums.map((album) => album.data).flat()));
		},
		// We use `this` in this one because we want the type inference of selectableAlbums
		hasHidden(): boolean {
			return this.selectableAlbums.filter((album) => album.is_nsfw).length > 0;
		},
	},
	actions: {
		reset() {
			this.isLoading = false;
			this.baseSmartAlbums = [];
			this.tagAlbums = [];
			this.albums = [];
			this.pinnedAlbums = [];
			this.sharedAlbums = [];
			this.rootConfig = undefined;
			this.rootRights = undefined;
		},
		loadRootRights(): Promise<void> {
			return InitService.fetchGlobalRights().then((data) => {
				this.rootRights = data.data.root_album;
			});
		},
		load(router: Router): Promise<void> {
			const togglableState = useTogglablesStateStore();
			const userStore = useUserStore();

			if (this.isLoading) {
				return Promise.resolve();
			}

			this.isLoading = true;
			return AlbumService.getAll()
				.then((data) => {
					this.baseSmartAlbums = data.data.smart_albums ?? [];
					this.tagAlbums = data.data.tag_albums;
					this.albums = data.data.albums;
					this.pinnedAlbums = data.data.pinned_albums;
					this.sharedAlbums = spliter(
						data.data.shared_albums ?? [],
						(d) => d.owner ?? "(unknown)", // mapper
						(d) => d.owner ?? "(unknown)", // formatter
						this.albums.length,
					);

					this.rootConfig = data.data.config;
					this.rootRights = data.data.rights;

					// If we are not logged in and there are no albums, we redirect to the login page.
					if (
						(userStore.user?.id === undefined || userStore.user?.id === null) &&
						this.albums.length === 0 &&
						this.smartAlbums.length === 0 &&
						this.sharedAlbums.length === 0
					) {
						router.push({ name: "login" });
					}
				})
				.catch((error) => {
					// We are required to login :)
					// We use the modal instead of the login page to avoid the redirect back.
					// Once logged in, we just refresh the page.
					if (error.response && error.response.status === 401) {
						togglableState.is_login_open = true;
						console.error("require login");
					} else {
						console.error(error);
					}
				})
				.finally(() => {
					this.isLoading = false;
				});
		},
	},
});
