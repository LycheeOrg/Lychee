import SearchService from "@/services/search-service";
import SearchV3Service, { type SearchV3Query } from "@/services/search-v3-service";
import { adaptPhotoTile, mergePhotoDetail, type AdaptedPhotoTile } from "@/v8/utils/adaptPhotoTile";
import { adaptAlbumChildTile, combineAlbumChildRights, DEFAULT_ALBUM_CHILD_RIGHTS, type AdaptedAlbumTile } from "@/v8/utils/adaptAlbumChildTile";
import { defineStore } from "pinia";
import { useAlbumStore } from "./AlbumState";
import { useAlbumsStore } from "./AlbumsState";
import { usePhotosStore } from "./PhotosState";
import { useLayoutStore } from "./LayoutState";
import { useLycheeStateStore } from "./LycheeState";
import { useUserStore } from "./UserState";

export type SearchStore = ReturnType<typeof useSearchStore>;

/** Matches the backend's own `photo_ids[]` input cap (422 above). */
const DETAILS_CHUNK_SIZE = 300;

export const useSearchStore = defineStore("search-store", {
	state: () => ({
		isSearching: false,
		config: undefined as undefined | App.Http.Resources.Search.InitResource,
		searchTerm: undefined as undefined | string,

		// Sorting (v8 advanced search only; left undefined by v7, which never sets it).
		sortingColumn: undefined as undefined | App.Enum.SearchSortingType,
		sortingOrder: "ASC" as App.Enum.OrderSortingType,

		// Pagination
		searchPage: 1,
		from: 0,
		perPage: 0,
		total: 0,

		// Bumped on every search/refresh/clear so a late-resolving request can tell it's
		// been superseded and must not overwrite newer state.
		requestToken: 0,

		// ── v3 Struct-of-Arrays state (Feature 069) ──────────────────────
		//
		// Held store-locally rather than written straight into the shared
		// `AlbumsState.albums`/`PhotosState.photos` (FR-069-20). Those are the
		// same stores album browsing uses, which is why `Search.vue` carries so
		// many defensive comments about leftover browsing data bleeding into a
		// fresh search. `_syncPhotosStoreV3()` compacts into `photosStore` only
		// for lightbox compatibility, mirroring `TimelineState.ts`'s own split.
		photoTilesV3: [] as AdaptedPhotoTile[],
		albumTilesV3: [] as AdaptedAlbumTile[],
		/** True when the result hit the `search_result_limit` cap (FR-069-02). */
		isTruncatedV3: false,
		/** Ids whose tier-3 details have already been merged, so we never refetch. */
		photoDetailsResolvedIdsV3: new Set<string>(),
	}),
	getters: {
		/**
		 * Mirrors `isFlowSoaActive`/`isTimelineSoaActive`: one centralized
		 * getter every call site consults, rather than re-deriving the flag
		 * (Q-065-05's resolution, applied here too).
		 */
		isSearchSoaActive(): boolean {
			return useLycheeStateStore().is_struct_of_array_enabled;
		},
	},
	actions: {
		reset() {
			this.isSearching = false;
			this.config = undefined;
			this.searchTerm = undefined;
			this.sortingColumn = undefined;
			this.sortingOrder = "ASC";
			this.searchPage = 1;
			this.from = 0;
			this.perPage = 0;
			this.total = 0;
			this.requestToken++;
			this.resetV3();
		},

		resetV3() {
			this.photoTilesV3 = [];
			this.albumTilesV3 = [];
			this.isTruncatedV3 = false;
			this.photoDetailsResolvedIdsV3 = new Set<string>();
		},

		load(): Promise<void> {
			const albumStore = useAlbumStore();
			const layoutStore = useLayoutStore();

			if (this.config !== undefined) {
				return Promise.resolve();
			}

			return SearchService.init(albumStore.albumId).then((response) => {
				this.config = response.data;
				layoutStore.layout = this.config.photo_layout;
			});
		},

		search(terms: string, startPage: number = 1): Promise<void> {
			const albumsStore = useAlbumsStore();
			const photosStore = usePhotosStore();
			const albumStore = useAlbumStore();

			if (terms.length < (this.config?.search_minimum_length ?? 3)) {
				this.requestToken++;
				albumsStore.albums = [];
				photosStore.photos = [];
				return Promise.resolve();
			}

			this.searchTerm = terms;
			this.searchPage = startPage;
			this.isSearching = true;
			const token = ++this.requestToken;
			return SearchService.search(albumStore.albumId, this.searchTerm, this.searchPage, this.sortingColumn, this.sortingOrder)
				.then((response) => {
					if (token !== this.requestToken) {
						// Superseded by a newer search/refresh/clear; discard this response.
						return;
					}
					albumsStore.albums = response.data.albums;
					photosStore.setPhotos(response.data.photos, false, this.searchPage);

					this.from = response.data.from;
					this.perPage = response.data.per_page;
					this.total = response.data.total;
				})
				.finally(() => {
					if (token === this.requestToken) {
						this.isSearching = false;
					}
				});
		},

		refresh(): Promise<void> {
			const albumsStore = useAlbumsStore();
			const photosStore = usePhotosStore();
			const albumStore = useAlbumStore();

			if (this.searchTerm === undefined) {
				return Promise.resolve();
			}
			this.isSearching = true;
			this.searchPage = Math.ceil(this.from / this.perPage) + 1;
			const token = ++this.requestToken;
			return SearchService.search(albumStore.albumId, this.searchTerm, this.searchPage, this.sortingColumn, this.sortingOrder)
				.then((response) => {
					if (token !== this.requestToken) {
						// Superseded by a newer search/refresh/clear; discard this response.
						return;
					}
					albumsStore.albums = response.data.albums;
					photosStore.setPhotos(response.data.photos, false, this.searchPage);
					this.from = response.data.from;
					this.perPage = response.data.per_page;
					this.total = response.data.total;
				})
				.finally(() => {
					if (token === this.requestToken) {
						this.isSearching = false;
					}
				});
		},

		/**
		 * Everything that identifies the current search, in the shape the v3
		 * service takes. Sorting is only sent when the user actually picked one
		 * — the backend requires `sorting_order` alongside `sorting_column`.
		 */
		queryV3(terms: string): SearchV3Query {
			const albumStore = useAlbumStore();
			const albumId = SearchService.albumId(albumStore.albumId);

			return {
				terms: terms,
				albumId: albumId === "" ? undefined : albumId,
				sortingColumn: this.sortingColumn,
				sortingOrder: this.sortingColumn === undefined ? undefined : this.sortingOrder,
			};
		},

		/**
		 * v3 counterpart of `search()`. Fetches the album half and the photo
		 * half concurrently — they are independent endpoints and neither needs
		 * the other's result.
		 *
		 * There is no bucket tier to fetch first (spec.md NG1): the photo tier
		 * is whole-scope in one request, bounded by `search_result_limit`.
		 */
		searchV3(terms: string): Promise<void> {
			if (terms.length < (this.config?.search_minimum_length ?? 3)) {
				this.requestToken++;
				this.resetV3();
				this._syncPhotosStoreV3();
				return Promise.resolve();
			}

			this.searchTerm = terms;
			this.isSearching = true;
			const token = ++this.requestToken;
			const query = this.queryV3(terms);

			return Promise.all([SearchV3Service.getPhotos(query), SearchV3Service.getAlbums(query), SearchV3Service.getAlbumRights(query)])
				.then(([photosResponse, albumsResponse, rightsResponse]) => {
					if (token !== this.requestToken) {
						// Superseded by a newer search/refresh/clear; discard.
						return;
					}

					const photos = photosResponse.data;
					this.photoTilesV3 = photos.ids.map((_, i) => adaptPhotoTile(i, photos, photos.album_ids[i]));
					this.isTruncatedV3 = photos.is_truncated;
					this.photoDetailsResolvedIdsV3 = new Set<string>();

					this.albumTilesV3 = this._adaptAlbums(albumsResponse.data, rightsResponse.data);

					// `total` drives the existing result-count headers; on this
					// path it is the real distinct-photo count (FR-069-05),
					// which v2 over-reports for multi-album photos.
					this.total = this.photoTilesV3.length;
					this.from = 0;
					this.perPage = this.photoTilesV3.length;

					this._syncPhotosStoreV3();
				})
				.finally(() => {
					if (token === this.requestToken) {
						this.isSearching = false;
					}
				});
		},

		/**
		 * Combines the album half's two tiers into the `ThumbAlbumResource`
		 * shape every existing album tile component already reads.
		 *
		 * `rights.ids` is not assumed to be index-aligned with `data.ids`: both
		 * tiers run the same query, but they are separate requests and the
		 * rights tier groups by album id, so the pairing is made explicit here
		 * rather than trusted.
		 */
		_adaptAlbums(data: App.Http.Resources.V3.AlbumDataResource, rights: App.Http.Resources.V3.AlbumRightsResource): AdaptedAlbumTile[] {
			const albumsStore = useAlbumsStore();
			const userStore = useUserStore();
			const dateFormat = albumsStore.rootConfig?.date_format_album_thumb ?? "M Y";
			const dateOrder = albumsStore.rootConfig?.thumb_min_max_order ?? "younger_older";
			const mayUpload = albumsStore.rootRights?.can_upload;

			const rightsIndexById = new Map<string, number>();
			rights.ids.forEach((id, i) => rightsIndexById.set(id, i));

			return data.ids.map((id, i) => {
				const rightsIndex = rightsIndexById.get(id);
				// A search result spans many parents, so `owner_id` is omitted
				// from the rights tier entirely (FR-069-09). Ownership is
				// therefore derived per row from the data tier instead.
				const isOwner = userStore.user !== undefined && String(userStore.user.id) === data.owner_ids[i];
				const combined =
					rightsIndex === undefined ? DEFAULT_ALBUM_CHILD_RIGHTS : combineAlbumChildRights(rightsIndex, rights, isOwner, mayUpload);

				return adaptAlbumChildTile(i, data, combined, dateFormat, dateOrder);
			});
		},

		/**
		 * Compacts the store-local tiles into the shared photo store, which the
		 * lightbox and context menu read. Deliberately the only place the v3
		 * path writes to `photosStore` (FR-069-20).
		 */
		_syncPhotosStoreV3() {
			const photosStore = usePhotosStore();
			photosStore.photos = this.photoTilesV3;
			photosStore.photosTimeline = undefined;
			photosStore.rebuildNavigationLinks();
		},

		/**
		 * On-demand tier 3 (FR-069-21). Only ever called when a photo is
		 * actually opened — grid scrolling must never trigger it.
		 *
		 * Already-resolved ids are filtered out before the request, and what
		 * remains is chunked at the backend's own 300-id input cap.
		 */
		async loadPhotoDetailsV3(ids: string[]): Promise<void> {
			if (this.searchTerm === undefined) {
				return;
			}
			const pending = ids.filter((id) => !this.photoDetailsResolvedIdsV3.has(id));
			if (pending.length === 0) {
				return;
			}

			const query = this.queryV3(this.searchTerm);
			const token = this.requestToken;

			for (let offset = 0; offset < pending.length; offset += DETAILS_CHUNK_SIZE) {
				const chunk = pending.slice(offset, offset + DETAILS_CHUNK_SIZE);
				const response = await SearchV3Service.getPhotoDetails(query, chunk);
				if (token !== this.requestToken) {
					// The search changed under us; the merged tiles are gone.
					return;
				}

				const detail = response.data;
				const indexById = new Map<string, number>();
				this.photoTilesV3.forEach((tile, i) => indexById.set(tile.id, i));

				detail.ids.forEach((id, i) => {
					const tileIndex = indexById.get(id);
					if (tileIndex !== undefined) {
						mergePhotoDetail(this.photoTilesV3[tileIndex], detail, i);
					}
					this.photoDetailsResolvedIdsV3.add(id);
				});
			}
		},

		clear() {
			const albumsStore = useAlbumsStore();
			const photosStore = usePhotosStore();
			this.requestToken++;
			albumsStore.albums = [];
			photosStore.reset();

			this.sortingColumn = undefined;
			this.sortingOrder = "ASC";

			this.from = 0;
			this.perPage = 0;
			this.total = 0;
			this.resetV3();
		},
	},
});
