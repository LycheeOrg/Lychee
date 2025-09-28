import { ALL } from "@/config/constants";
import AlbumService from "@/services/album-service";
import { defineStore } from "pinia";
import { useTogglablesStateStore } from "./ModalsState";
import { usePhotosStore } from "./PhotosState";
import { useAlbumsStore } from "./AlbumsState";

export type AlbumStore = ReturnType<typeof useAlbumStore>;

export const useAlbumStore = defineStore("album-store", {
	state: () => ({
		albumId: undefined as string | undefined,
		modelAlbum: undefined as App.Http.Resources.Models.AlbumResource | undefined,
		tagAlbum: undefined as App.Http.Resources.Models.TagAlbumResource | undefined,
		smartAlbum: undefined as App.Http.Resources.Models.SmartAlbumResource | undefined,

		isPasswordProtected: false as boolean,
		config: undefined as App.Http.Resources.GalleryConfigs.AlbumConfig | undefined,
		isLoading: false as boolean,
		_loadingAlbumId: undefined as undefined | string,
	}),
	actions: {
		refresh(): Promise<void> {
			this.reset();
			return this.load();
		},
		reset() {
			this.modelAlbum = undefined;
			this.tagAlbum = undefined;
			this.smartAlbum = undefined;
			this.isPasswordProtected = false;
			this.config = undefined;
			this.isLoading = false;
		},
		load(): Promise<void> {
			const togglableState = useTogglablesStateStore();
			const photosState = usePhotosStore();
			const albumsStore = useAlbumsStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			// Do not reload fully if we are already on the right album.
			if (this.albumId === this.album?.id && this.isLoaded) {
				return Promise.resolve();
			}

			// Exit early if we are already loading this album
			if (this._loadingAlbumId === this.albumId) {
				return Promise.resolve();
			}

			const requestedAlbumId = this.albumId;
			this._loadingAlbumId = requestedAlbumId;
			this.isLoading = true;
			this.isPasswordProtected = false;
			this.config = undefined;
			this.modelAlbum = undefined;
			this.tagAlbum = undefined;
			this.smartAlbum = undefined;

			return AlbumService.get(requestedAlbumId)
				.then((data) => {
					// Exit early if the albumId changed while we were loading
					// (e.g. user clicked on another album, or went back/forward in history)
					// In that case, we don't want to override the state with the old album.
					if (this._loadingAlbumId !== requestedAlbumId) {
						return;
					}

					this.config = data.data.config;
					if (data.data.resource === null) {
						return;
					}
					if (data.data.config.is_model_album) {
						this.modelAlbum = data.data.resource as App.Http.Resources.Models.AlbumResource;
						albumsStore.albums = this.modelAlbum.albums;
					} else if (data.data.config.is_base_album) {
						this.tagAlbum = data.data.resource as App.Http.Resources.Models.TagAlbumResource;
						albumsStore.albums = []; // Reset to avoid bad surprises.
					} else {
						this.smartAlbum = data.data.resource as App.Http.Resources.Models.SmartAlbumResource;
						albumsStore.albums = []; // Reset to avoid bad surprises.
					}
					photosState.setPhotos(data.data.resource.photos, data.data.config.is_photo_timeline_enabled);
				})
				.catch((error) => {
					if (this._loadingAlbumId !== requestedAlbumId) {
						return;
					}
					if (error.response && error.response.status === 401 && error.response.data.message === "Password required") {
						this.isPasswordProtected = true;
					} else if (error.response && error.response.status === 403 && error.response.data.message === "Password required") {
						this.isPasswordProtected = true;
					} else if (error.response && error.response.status === 401) {
						togglableState.is_login_open = true;
					} else {
						console.error(error);
					}
				})
				.finally(() => {
					this.isLoading = false;
					if (this._loadingAlbumId === requestedAlbumId) {
						this._loadingAlbumId = undefined;
						this.isLoading = false;
					}
				});
		},
	},
	getters: {
		album(
			state,
		):
			| App.Http.Resources.Models.AlbumResource
			| App.Http.Resources.Models.SmartAlbumResource
			| App.Http.Resources.Models.TagAlbumResource
			| undefined {
			return state.modelAlbum || state.tagAlbum || state.smartAlbum;
		},
		tagOrModelAlbum(state): App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | undefined {
			return state.modelAlbum || state.tagAlbum;
		},
		rights(state): App.Http.Resources.Rights.AlbumRightsResource | undefined {
			return (state.modelAlbum || state.tagAlbum || state.smartAlbum)?.rights ?? undefined;
		},
		isLoaded(): boolean {
			return this.config !== undefined && this.album !== undefined;
		},
	},
});
