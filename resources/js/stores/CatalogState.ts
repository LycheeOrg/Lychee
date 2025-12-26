import { ALL } from "@/config/constants";
import { defineStore } from "pinia";
import WebshopService from "@/services/webshop-service";
import { useLycheeStateStore } from "./LycheeState";

export type CatalogStore = ReturnType<typeof useCatalogStore>;

export const useCatalogStore = defineStore("catalog-store", {
	state: () => ({
		albumId: undefined as string | undefined,
		catalog: undefined as App.Http.Resources.Shop.CatalogResource | undefined,
		isLoading: false as boolean,
		_loadingAlbumId: undefined as string | undefined,
	}),
	actions: {
		reset() {
			this.albumId = undefined;
			this.catalog = undefined;
			this.isLoading = false;
			this._loadingAlbumId = undefined;
		},
		load(): Promise<void> {
			// Guard for SE
			if (useLycheeStateStore().is_pro_enabled !== true) {
				return Promise.resolve();
			}

			if (this.albumId === ALL || this.albumId === undefined) {
				return Promise.resolve();
			}

			// Do not reload fully if we are already on the right album.
			if (this.albumId === this.catalog?.album_purchasable?.album_id && this.isLoaded) {
				return Promise.resolve();
			}

			// Exit early if we are already loading this album
			if (this._loadingAlbumId === this.albumId) {
				return Promise.resolve();
			}

			const requestedAlbumId = this.albumId;
			this._loadingAlbumId = requestedAlbumId;
			this.isLoading = true;
			return WebshopService.Catalog.getCatalog(this.albumId)
				.then((catalogData) => {
					this.catalog = catalogData.data;
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
		isLoaded(): boolean {
			return this.catalog !== undefined;
		},
		description(): string | undefined {
			return this.catalog?.album_purchasable?.description;
		},
	},
});
