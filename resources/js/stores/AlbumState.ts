import { ALL } from "@/config/constants";
import AlbumService from "@/services/album-service";
import { defineStore } from "pinia";
import { useTogglablesStateStore } from "./ModalsState";
import { usePhotosStore } from "./PhotosState";
import { useAlbumsStore } from "./AlbumsState";
import { useLycheeStateStore } from "./LycheeState";

export type AlbumStore = ReturnType<typeof useAlbumStore>;

export const useAlbumStore = defineStore("album-store", {
	state: () => ({
		albumId: undefined as string | undefined,
		modelAlbum: undefined as App.Http.Resources.Models.HeadAlbumResource | undefined,
		tagAlbum: undefined as App.Http.Resources.Models.HeadTagAlbumResource | undefined,
		smartAlbum: undefined as App.Http.Resources.Models.HeadSmartAlbumResource | undefined,

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
		showStarredOnly: false,

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
			this.showStarredOnly = false;
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

		/**
		 * Load album metadata without children/photos arrays.
		 * Uses the new /Album::head pagination endpoint for efficient loading.
		 *
		 * Handles:
		 * - Model albums (regular albums with children and photos)
		 * - Tag albums (albums based on photo tags)
		 * - Smart albums (Recent, Starred, On This Day, Unsorted, Untagged)
		 * - Password-protected albums
		 * - Race conditions when user navigates quickly between albums
		 */
		loadHead(): Promise<void> {
			const togglableState = useTogglablesStateStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			// Capture the album ID we're loading to detect race conditions
			const requestedAlbumId = this.albumId;
			// Track which album is currently being loaded
			this._loadingAlbumId = requestedAlbumId;
			this.isLoading = true;

			return AlbumService.getHead(requestedAlbumId)
				.then((data) => {
					// Race condition check #1: User navigated away while request was in flight
					if (this.albumId !== requestedAlbumId) {
						return;
					}

					// Exit early if the albumId changed while we were loading
					// (e.g. user clicked on another album, or went back/forward in history)
					// In that case, we don't want to override the state with the old album.
					this.isPasswordProtected = false;
					// Race condition check #2: Another load() call started for a different album
					if (this._loadingAlbumId !== requestedAlbumId) {
						return;
					}

					if (data.data.config.is_model_album) {
						this.config = data.data.config;
						this.modelAlbum = data.data.resource as App.Http.Resources.Models.HeadAlbumResource;
					} else {
						this.config = data.data.config;
						if (data.data.config.is_base_album) {
							this.tagAlbum = data.data.resource as App.Http.Resources.Models.HeadTagAlbumResource;
						} else {
							this.smartAlbum = data.data.resource as App.Http.Resources.Models.HeadSmartAlbumResource;
						}
					}
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
					if (this._loadingAlbumId === requestedAlbumId) {
						this._loadingAlbumId = undefined;
						this.isLoading = false;
					}
				});
		},

		/**
		 * Load paginated child albums for the current album.
		 *
		 * @param page - Page number to load (1-indexed)
		 * @param append - If true, merge with existing albums; if false, replace them
		 *
		 * The append parameter is critical for different UI modes:
		 * - append=false: Used when loading first page or navigating to specific page
		 * - append=true: Used by infinite scroll and "Load More" button
		 */
		loadAlbums(page: number = 1, append: boolean = false): Promise<void> {
			const albumsStore = useAlbumsStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			// Capture current album ID to detect navigation during loading
			const requestedAlbumId = this.albumId;
			this.albums_loading = true;

			return AlbumService.getAlbums(requestedAlbumId, page)
				.then((data) => {
					// Race condition guard: Don't update state if user navigated away
					if (this.albumId !== requestedAlbumId) {
						return;
					}
					// Append mode: Merge new albums with existing (for infinite scroll/load more)
					// Replace mode: Show only the new page (for page navigation)
					if (append) {
						albumsStore.albums = [...albumsStore.albums, ...data.data.data];
					} else {
						albumsStore.albums = data.data.data;
					}
					if (useLycheeStateStore().is_debug_enabled) {
						console.debug(`albums: ${albumsStore.albums.length}/${data.data.total}`);
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

		/**
		 * Load paginated photos for the current album.
		 *
		 * @param page - Page number to load (1-indexed)
		 * @param append - If true, merge with existing photos; if false, replace them
		 *
		 * Handles timeline mode: When append=true and timeline is enabled,
		 * PhotosState.appendPhotos() intelligently merges photos into existing
		 * timeline groups rather than creating duplicate date headers.
		 */
		loadPhotos(page: number = 1, append: boolean = false): Promise<void> {
			const photosState = usePhotosStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			// Capture current album ID to detect navigation during loading
			const requestedAlbumId = this.albumId;
			this.photos_loading = true;

			return AlbumService.getPhotos(requestedAlbumId, page)
				.then((data) => {
					// Race condition guard: Don't update state if user navigated away
					if (this.albumId !== requestedAlbumId) {
						return;
					}
					// appendPhotos handles timeline merging for append=true
					// setPhotos replaces all photos and rebuilds timeline for append=false
					if (append) {
						photosState.appendPhotos(data.data.photos, this.config?.is_photo_timeline_enabled ?? false);
					} else {
						photosState.setPhotos(data.data.photos, this.config?.is_photo_timeline_enabled ?? false);
					}
					if (useLycheeStateStore().is_debug_enabled) {
						console.debug(`photos: ${photosState.photos.length}/${data.data.total}`);
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

		/**
		 * Convenience method to load the next page of photos.
		 * Used by infinite scroll and "Load More" button components.
		 *
		 * Guards prevent:
		 * - Loading while already loading (rapid scrolling/clicking)
		 * - Loading beyond last page (when all content is loaded)
		 */
		loadMorePhotos(): Promise<void> {
			// Guard against duplicate requests while loading
			if (this.photos_loading) {
				return Promise.resolve();
			}
			// Guard against loading beyond last page
			if (this.photos_current_page >= this.photos_last_page) {
				return Promise.resolve();
			}
			// Load next page with append=true to merge with existing photos
			return this.loadPhotos(this.photos_current_page + 1, true);
		},

		/**
		 * Convenience method to load the next page of albums.
		 * Used by infinite scroll and "Load More" button components.
		 *
		 * Guards prevent:
		 * - Loading while already loading (rapid scrolling/clicking)
		 * - Loading beyond last page (when all content is loaded)
		 */
		loadMoreAlbums(): Promise<void> {
			// Guard against duplicate requests while loading
			if (this.albums_loading) {
				return Promise.resolve();
			}
			// Guard against loading beyond last page
			if (this.albums_current_page >= this.albums_last_page) {
				return Promise.resolve();
			}
			// Load next page with append=true to merge with existing albums
			return this.loadAlbums(this.albums_current_page + 1, true);
		},

		async load(): Promise<void> {
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

			return AlbumService.getHead(requestedAlbumId)
				.then(async (data) => {
					this.isPasswordProtected = false;
					this.config = undefined;
					this.modelAlbum = undefined;
					this.tagAlbum = undefined;
					this.smartAlbum = undefined;

					// Exit early if the albumId changed while we were loading
					// (e.g. user clicked on another album, or went back/forward in history)
					// In that case, we don't want to override the state with the old album.
					if (this._loadingAlbumId !== requestedAlbumId) {
						return;
					}

					// Clear existing data to avoid showing stale content from previous album
					albumsStore.reset();
					photosState.reset();

					// Model albums (regular albums): Load both children and photos in parallel
					// This is efficient because they're independent queries
					if (data.data.config.is_model_album) {
						this.config = data.data.config;
						await Promise.all([this.loadAlbums(1, false), this.loadPhotos(1, false)]);
						this.modelAlbum = data.data.resource as App.Http.Resources.Models.HeadAlbumResource;
					} else {
						// Tag/Smart albums: Only load photos (they don't have child albums)
						await this.loadPhotos(1, false);
						this.config = data.data.config;
						if (data.data.config.is_base_album) {
							// Tag album: Photos filtered by tag
							this.tagAlbum = data.data.resource as App.Http.Resources.Models.HeadTagAlbumResource;
						} else {
							// Smart album: Recent, Starred, On This Day, Unsorted, Untagged
							this.smartAlbum = data.data.resource as App.Http.Resources.Models.HeadSmartAlbumResource;
						}
					}
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
					if (this._loadingAlbumId === requestedAlbumId) {
						this._loadingAlbumId = undefined;
						this.isLoading = false;
					}
				});
		},
		setShowStarredOnly(value: boolean) {
			this.showStarredOnly = value;
		},
	},
	getters: {
		album(
			state,
		):
			| App.Http.Resources.Models.HeadAlbumResource
			| App.Http.Resources.Models.HeadSmartAlbumResource
			| App.Http.Resources.Models.HeadTagAlbumResource
			| undefined {
			return state.modelAlbum || state.tagAlbum || state.smartAlbum;
		},
		tagOrModelAlbum(state): App.Http.Resources.Models.HeadAlbumResource | App.Http.Resources.Models.HeadTagAlbumResource | undefined {
			return state.modelAlbum || state.tagAlbum;
		},
		rights(state): App.Http.Resources.Rights.AlbumRightsResource | undefined {
			return (state.modelAlbum || state.tagAlbum || state.smartAlbum)?.rights ?? undefined;
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
		showStarredOnlyValue(): boolean {
			return this.showStarredOnly;
		},
	},
});
