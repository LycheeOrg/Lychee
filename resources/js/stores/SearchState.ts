import SearchService from "@/services/search-service";
import { defineStore } from "pinia";
import { useAlbumStore } from "./AlbumState";
import { useAlbumsStore } from "./AlbumsState";
import { usePhotosStore } from "./PhotosState";
import { useLayoutStore } from "./LayoutState";

export type SearchStore = ReturnType<typeof useSearchStore>;

export const useSearchStore = defineStore("search-store", {
	state: () => ({
		isSearching: false,
		config: undefined as undefined | App.Http.Resources.Search.InitResource,
		searchTerm: undefined as undefined | string,

		// Pagination
		searchPage: 1,
		from: 0,
		perPage: 0,
		total: 0,
	}),
	actions: {
		reset() {
			this.isSearching = false;
			this.config = undefined;
			this.searchTerm = undefined;
			this.searchPage = 1;
			this.from = 0;
			this.perPage = 0;
			this.total = 0;
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

		search(terms: string): Promise<void> {
			const albumsStore = useAlbumsStore();
			const photosStore = usePhotosStore();
			const albumStore = useAlbumStore();

			if (terms.length < (this.config?.search_minimum_length ?? 3)) {
				albumsStore.albums = [];
				photosStore.photos = [];
				return Promise.resolve();
			}

			this.searchTerm = terms;
			this.isSearching = true;
			return SearchService.search(albumStore.albumId, this.searchTerm, this.searchPage)
				.then((response) => {
					albumsStore.albums = response.data.albums;
					photosStore.photos = response.data.photos;

					this.from = response.data.from;
					this.perPage = response.data.per_page;
					this.total = response.data.total;
				})
				.finally(() => {
					this.isSearching = false;
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
			return SearchService.search(albumStore.albumId, this.searchTerm, this.searchPage)
				.then((response) => {
					albumsStore.albums = response.data.albums;
					photosStore.photos = response.data.photos;
					this.from = response.data.from;
					this.perPage = response.data.per_page;
					this.total = response.data.total;
				})
				.finally(() => {
					this.isSearching = false;
				});
		},

		clear() {
			const albumsStore = useAlbumsStore();
			const photosStore = usePhotosStore();
			albumsStore.albums = [];
			photosStore.photos = [];

			this.from = 0;
			this.perPage = 0;
			this.total = 0;
		},
	},
});
