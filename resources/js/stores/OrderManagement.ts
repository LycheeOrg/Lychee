import WebshopService, { AddAlbum, AddPhoto } from "@/services/webshop-service";
import { defineStore } from "pinia";
import { useLycheeStateStore } from "./LycheeState";

export type OrderManagementStateStore = ReturnType<typeof useOrderManagementStore>;

export const useOrderManagementStore = defineStore("basket-management-store", {
	state: () => ({
		options: {} as App.Http.Resources.Shop.CheckoutOptionResource,
		order: undefined as App.Http.Resources.Shop.OrderResource | undefined,
	}),
	actions: {
		reset(): void {
			this.options = {} as App.Http.Resources.Shop.CheckoutOptionResource;
			this.order = undefined;
		},
		async load(): Promise<void> {
			// Guard for SE
			if (useLycheeStateStore().is_se_enabled !== true) {
				return Promise.resolve();
			}
			if (this.order !== undefined) {
				return Promise.resolve();
			}

			return WebshopService.Order.getCurrentBasket().then((response) => {
				this.order = response.data;
			});
		},
		async refresh(): Promise<void> {
			this.reset();
			return this.load();
		},
		addPhoto(photoData: AddPhoto): Promise<void> {
			// Guard for SE
			if (useLycheeStateStore().is_se_enabled !== true) {
				return Promise.resolve();
			}

			return WebshopService.Order.addPhotoToBasket(photoData).then((response) => {
				this.order = response.data;
			});
		},
		addAlbum(albumData: AddAlbum): Promise<void> {
			// Guard for SE
			if (useLycheeStateStore().is_se_enabled !== true) {
				return Promise.resolve();
			}

			return WebshopService.Order.addAlbumToBasket(albumData).then((response) => {
				this.order = response.data;
			});
		},
		removeItem(itemId: number): Promise<void> {
			// Guard for SE
			if (useLycheeStateStore().is_se_enabled !== true) {
				return Promise.resolve();
			}

			return WebshopService.Order.removeItemFromBasket(itemId).then((response) => {
				this.order = response.data;
			});
		},
		async clear(): Promise<void> {
			// Guard for SE
			if (useLycheeStateStore().is_se_enabled !== true) {
				return Promise.resolve();
			}
			this.reset();
			await WebshopService.Order.clearBasket();
			return this.load();
		},
	},
	getters: {
		hasItems(): boolean {
			return this.order?.items !== undefined && this.order?.items !== null && this.order.items.length > 0;
		},
	},
});
