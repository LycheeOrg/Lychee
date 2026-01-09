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
		albumHead: undefined as App.Http.Resources.Models.HeadAlbumResource | undefined,

		isPasswordProtected: false as boolean,
		config: undefined as App.Http.Resources.GalleryConfigs.AlbumConfig | undefined,
		isLoading: false as boolean,
		_loadingAlbumId: undefined as undefined | string,
		_loadingPage: undefined as undefined | number,

		// Legacy pagination state (for Smart albums via /Album endpoint)
		current_page: 1,
		last_page: 0,
		per_page: 0,
		total: 0,

		// New pagination state for photos (via /Album::photos endpoint)
		photos_current_page: 1,
		photos_last_page: 0,
		photos_per_page: 0,
		photos_total: 0,
		photos_loading: false as boolean,

		// New pagination state for albums (via /Album::albums endpoint)
		albums_current_page: 1,
		albums_last_page: 0,
		albums_per_page: 0,
		albums_total: 0,
		albums_loading: false as boolean,

		// Flag to use new paginated endpoints
		usePaginatedEndpoints: false as boolean,
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
			this.albumHead = undefined;
			this.isPasswordProtected = false;
			this.config = undefined;
			this.isLoading = false;
			// Reset pagination state
			this.photos_current_page = 1;
			this.photos_last_page = 0;
			this.photos_per_page = 0;
			this.photos_total = 0;
			this.photos_loading = false;
			this.albums_current_page = 1;
			this.albums_last_page = 0;
			this.albums_per_page = 0;
			this.albums_total = 0;
			this.albums_loading = false;
		},

		// Load album head metadata (without children/photos)
		loadHead(): Promise<void> {
			const togglableState = useTogglablesStateStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			const requestedAlbumId = this.albumId;
			this.isLoading = true;

			return AlbumService.getHead(requestedAlbumId)
				.then((data) => {
					if (this.albumId !== requestedAlbumId) {
						return;
					}
					// HeadAlbumResource includes config as a property
					this.albumHead = data.data;
					this.config = data.data.config;
					this.isPasswordProtected = false;
				})
				.catch((error) => {
					if (this.albumId !== requestedAlbumId) {
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
				});
		},

		// Load paginated child albums
		loadAlbums(page: number = 1, append: boolean = false): Promise<void> {
			const albumsStore = useAlbumsStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			const requestedAlbumId = this.albumId;
			this.albums_loading = true;

			return AlbumService.getAlbums(requestedAlbumId, page)
				.then((data) => {
					if (this.albumId !== requestedAlbumId) {
						return;
					}
					if (append) {
						albumsStore.albums = [...albumsStore.albums, ...data.data.data];
					} else {
						albumsStore.albums = data.data.data;
					}
					this.albums_current_page = data.data.current_page;
					this.albums_last_page = data.data.last_page;
					this.albums_per_page = data.data.per_page;
					this.albums_total = data.data.total;
				})
				.catch((error) => {
					console.error(error);
				})
				.finally(() => {
					this.albums_loading = false;
				});
		},

		// Load paginated photos
		loadPhotos(page: number = 1, append: boolean = false): Promise<void> {
			const photosState = usePhotosStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			const requestedAlbumId = this.albumId;
			this.photos_loading = true;

			return AlbumService.getPhotos(requestedAlbumId, page)
				.then((data) => {
					if (this.albumId !== requestedAlbumId) {
						return;
					}
					if (append) {
						photosState.appendPhotos(data.data.photos, this.config?.is_photo_timeline_enabled ?? false);
					} else {
						photosState.setPhotos(data.data.photos, this.config?.is_photo_timeline_enabled ?? false);
					}
					this.photos_current_page = data.data.current_page;
					this.photos_last_page = data.data.last_page;
					this.photos_per_page = data.data.per_page;
					this.photos_total = data.data.total;
				})
				.catch((error) => {
					console.error(error);
				})
				.finally(() => {
					this.photos_loading = false;
				});
		},

		// Load next page of photos (for infinite scroll / load more)
		loadMorePhotos(): Promise<void> {
			if (this.photos_current_page >= this.photos_last_page) {
				return Promise.resolve();
			}
			return this.loadPhotos(this.photos_current_page + 1, true);
		},

		// Load next page of albums (for infinite scroll / load more)
		loadMoreAlbums(): Promise<void> {
			if (this.albums_current_page >= this.albums_last_page) {
				return Promise.resolve();
			}
			return this.loadAlbums(this.albums_current_page + 1, true);
		},

		// Load album using new paginated endpoints (for regular albums only)
		// This loads head metadata, first page of albums, and first page of photos in parallel
		async loadPaginated(): Promise<void> {
			const albumsStore = useAlbumsStore();
			const photosState = usePhotosStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return;
			}

			// Check if already loaded
			if (this.albumId === this.albumHead?.id) {
				return;
			}

			this.isLoading = true;

			try {
				// Load head metadata first to get config
				await this.loadHead();

				// If not a model album (regular album), fall back to legacy load
				if (!this.config?.is_model_album) {
					this.isLoading = false;
					return this.load();
				}

				// Reset albums/photos stores before loading new data
				albumsStore.albums = [];
				photosState.reset();

				// Load first page of albums and photos in parallel
				await Promise.all([this.loadAlbums(1, false), this.loadPhotos(1, false)]);
			} finally {
				this.isLoading = false;
			}
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
			const requestedPage = this.current_page;
			this._loadingAlbumId = requestedAlbumId;
			this._loadingPage = requestedPage;
			this.isLoading = true;

			return AlbumService.get(requestedAlbumId, requestedPage)
				.then((data) => {
					this.isPasswordProtected = false;
					this.config = undefined;
					this.modelAlbum = undefined;
					this.tagAlbum = undefined;
					this.smartAlbum = undefined;

					// Exit early if the albumId changed while we were loading
					// (e.g. user clicked on another album, or went back/forward in history)
					// In that case, we don't want to override the state with the old album.
					if (this._loadingAlbumId !== requestedAlbumId || this._loadingPage !== requestedPage) {
						return;
					}

					this.config = data.data.config;
					if (data.data.resource === null) {
						return;
					}
					// Reset to avoid bad surprises.
					albumsStore.albums = [];
					albumsStore.tagAlbums = [];
					albumsStore.pinnedAlbums = [];
					albumsStore.sharedAlbums = [];
					// Load data.
					if (data.data.config.is_model_album) {
						this.modelAlbum = data.data.resource as App.Http.Resources.Models.AlbumResource;
						albumsStore.albums = this.modelAlbum.albums;
					} else if (data.data.config.is_base_album) {
						this.tagAlbum = data.data.resource as App.Http.Resources.Models.TagAlbumResource;
					} else {
						this.smartAlbum = data.data.resource as App.Http.Resources.Models.SmartAlbumResource;
						this.per_page = this.smartAlbum.per_page;
						this.total = this.smartAlbum.total;
						this.current_page = this.smartAlbum.current_page;
						this.last_page = this.smartAlbum.last_page;
					}
					photosState.setPhotos(data.data.resource.photos, data.data.config.is_photo_timeline_enabled);
				})
				.catch((error) => {
					if (this._loadingAlbumId !== requestedAlbumId || this._loadingPage !== requestedPage) {
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
			return (state.modelAlbum || state.tagAlbum || state.smartAlbum || state.albumHead)?.rights ?? undefined;
		},
		isLoaded(): boolean {
			return this.config !== undefined && this.album !== undefined;
		},
		hasPagination(): boolean {
			return this.smartAlbum !== undefined;
		},
		// New pagination getters for photos
		hasMorePhotos(state): boolean {
			return state.photos_current_page < state.photos_last_page;
		},
		photosRemainingCount(state): number {
			return Math.max(0, state.photos_total - state.photos_current_page * state.photos_per_page);
		},
		// New pagination getters for albums
		hasMoreAlbums(state): boolean {
			return state.albums_current_page < state.albums_last_page;
		},
		albumsRemainingCount(state): number {
			return Math.max(0, state.albums_total - state.albums_current_page * state.albums_per_page);
		},
		// Check if new paginated endpoints are available (photos have pagination info)
		hasPhotosPagination(state): boolean {
			return state.photos_last_page > 0;
		},
		hasAlbumsPagination(state): boolean {
			return state.albums_last_page > 0;
		},
	},
});
