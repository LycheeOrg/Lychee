import { ALL } from "@/config/constants";
import AlbumService from "@/services/album-service";
import { defineStore } from "pinia";
import { useTogglablesStateStore } from "./ModalsState";
import { usePhotosStore } from "./PhotosState";
import { useAlbumsStore } from "./AlbumsState";
import { useLycheeStateStore } from "./LycheeState";
import { useLayoutStore } from "./LayoutState";

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

		// New pagination state for photos (via /Album::photos endpoint)
		photos_current_page: 1,
		photos_last_page: 0,
		photos_per_page: 0,
		photos_total: 0,
		photos_loading: false as boolean,
		/** Earliest page that has been loaded into the photos store (used for background prepend tracking). */
		photos_min_page: 1,

		// New pagination state for albums (via /Album::albums endpoint)
		albums_current_page: 1,
		albums_last_page: 0,
		albums_per_page: 0,
		albums_total: 0,
		albums_loading: false as boolean,

		// Tag filter state for photos
		active_tag_filter: null as { tag_ids: number[]; tag_logic: string } | null,
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
			// Reset pagination state
			this.photos_current_page = 1;
			this.photos_last_page = 0;
			this.photos_per_page = 0;
			this.photos_total = 0;
			this.photos_loading = false;
			this.photos_min_page = 1;
			this.albums_current_page = 1;
			this.albums_last_page = 0;
			this.albums_per_page = 0;
			this.albums_total = 0;
			this.albums_loading = false;
			// Reset tag filter
			this.active_tag_filter = null;
		},

		/**
		 * Load album metadata without children/photos arrays.
		 * Uses the new /Album::head pagination endpoint for efficient loading.
		 *
		 * Handles:
		 * - Model albums (regular albums with children and photos)
		 * - Tag albums (albums based on photo tags)
		 * - Smart albums (Recent, Highlighted, On This Day, Unsorted, Untagged)
		 * - Password-protected albums
		 * - Race conditions when user navigates quickly between albums
		 */
		loadHead(): Promise<void> {
			const togglableState = useTogglablesStateStore();
			const layoutStore = useLayoutStore();

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
					this.config = data.data.config;
					layoutStore.layout = data.data.config.photo_layout;

					if (data.data.config.is_model_album) {
						this.modelAlbum = data.data.resource as App.Http.Resources.Models.HeadAlbumResource;
						return;
					}
					if (data.data.config.is_base_album) {
						this.tagAlbum = data.data.resource as App.Http.Resources.Models.HeadTagAlbumResource;
						return;
					}
					this.smartAlbum = data.data.resource as App.Http.Resources.Models.HeadSmartAlbumResource;
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
		 * @param prepend - If true, insert photos at the beginning (for background loading of previous pages)
		 *
		 * Handles timeline mode: When append=true and timeline is enabled,
		 * PhotosState.appendPhotos() intelligently merges photos into existing
		 * timeline groups rather than creating duplicate date headers.
		 */
		loadPhotos(page: number = 1, append: boolean = false, prepend: boolean = false): Promise<void> {
			const photosState = usePhotosStore();

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			// Capture current album ID to detect navigation during loading
			const requestedAlbumId = this.albumId;
			// Background prepend operations don't show a loading indicator to
			// avoid flickering and to not block the loadMorePhotos guard.
			if (!prepend) {
				this.photos_loading = true;
			}

			// Extract tag filter params from state
			const tag_ids = this.active_tag_filter?.tag_ids ?? null;
			const tag_logic = this.active_tag_filter?.tag_logic ?? "OR";

			return AlbumService.getPhotos(requestedAlbumId, page, tag_ids, tag_logic)
				.then((data) => {
					// Race condition guard: Don't update state if user navigated away
					if (this.albumId !== requestedAlbumId) {
						return;
					}
					// prependPhotos inserts before existing photos for prepend=true (background loading of previous pages)
					// appendPhotos handles timeline merging for append=true
					// setPhotos replaces all photos and rebuilds timeline for append=false
					if (prepend) {
						photosState.prependPhotos(data.data.photos, this.config?.is_photo_timeline_enabled ?? false, page);
						// Track the earliest page that has been prepended
						if (page < this.photos_min_page) {
							this.photos_min_page = page;
						}
					} else if (append) {
						photosState.appendPhotos(data.data.photos, this.config?.is_photo_timeline_enabled ?? false, page);
						this.photos_current_page = data.data.current_page;
						this.photos_last_page = data.data.last_page;
						this.photos_per_page = data.data.per_page;
						this.photos_total = data.data.total;
					} else {
						photosState.setPhotos(data.data.photos, this.config?.is_photo_timeline_enabled ?? false, page);
						this.photos_current_page = data.data.current_page;
						this.photos_last_page = data.data.last_page;
						this.photos_per_page = data.data.per_page;
						this.photos_total = data.data.total;
						this.photos_min_page = page;
					}
					if (useLycheeStateStore().is_debug_enabled) {
						console.debug(`photos: ${photosState.photos.length}/${data.data.total}`);
					}
				})
				.catch((error) => {
					console.error(error);
				})
				.finally(() => {
					if (!prepend) {
						this.photos_loading = false;
					}
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

		/**
		 * Set tag filter and reload photos.
		 * Resets to page 1 and replaces existing photos.
		 */
		setTagFilter(tag_ids: number[], tag_logic: string = "OR"): Promise<void> {
			this.active_tag_filter = { tag_ids, tag_logic };
			// Reset to page 1 and reload with filter
			return this.loadPhotos(1, false);
		},

		/**
		 * Clear tag filter and reload all photos.
		 * Resets to page 1 and replaces existing photos.
		 */
		clearTagFilter(): Promise<void> {
			this.active_tag_filter = null;
			// Reset to page 1 and reload without filter
			return this.loadPhotos(1, false);
		},

		/**
		 * Load the album metadata and first batch of photos.
		 *
		 * @param startPage - The page to load first. When provided (>1), that page is loaded
		 *                    immediately so a directly linked photo can be displayed, then
		 *                    pages 1…startPage-1 are loaded in the background (prepended).
		 */
		async load(startPage: number = 1): Promise<void> {
			const togglableState = useTogglablesStateStore();
			const photosState = usePhotosStore();
			const albumsStore = useAlbumsStore();
			const layoutStore = useLayoutStore();

			if (this.albumId === ALL || this.albumId === undefined) {
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

					this.config = data.data.config;
					layoutStore.layout = data.data.config.photo_layout;

					// Clamp startPage to a valid range (guard against bad query params)
					const resolvedStart = startPage > 1 ? startPage : 1;

					// Load the target page first so a directly linked photo can be displayed
					// immediately. Previous pages are prepended in background afterwards.
					await this.loadPhotos(resolvedStart, false);

					const loader: Promise<void>[] = [];

					if (data.data.config.is_model_album) {
						this.modelAlbum = data.data.resource as App.Http.Resources.Models.HeadAlbumResource;
						loader.push(this.loadAlbums(1, false));
					} else if (data.data.config.is_base_album) {
						this.tagAlbum = data.data.resource as App.Http.Resources.Models.HeadTagAlbumResource;
					} else {
						this.smartAlbum = data.data.resource as App.Http.Resources.Models.HeadSmartAlbumResource;
					}

					await Promise.all(loader);

					// Fire off background loading of immediately preceding pages (prepend).
					// Capped at the 5 most-recent previous pages to avoid issuing too many
					// concurrent requests when jumping to a high page number (e.g. page 50).
					// These are intentionally NOT awaited so the photo panel can render
					// while earlier pages stream in, without blocking albumStore.load().
					const backgroundPagesLimit = 5;
					const firstBackgroundPage = Math.max(1, resolvedStart - backgroundPagesLimit);
					for (let p = resolvedStart - 1; p >= firstBackgroundPage; p--) {
						void this.loadPhotos(p, false, true);
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
	},
});
