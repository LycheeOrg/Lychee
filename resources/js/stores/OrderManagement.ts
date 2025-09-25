import WebshopService, { AddAlbum, AddPhoto } from "@/services/webshop-service";
import { defineStore } from "pinia";

export type OrderManagementStateStore = ReturnType<typeof useOrderManagementStore>;

export const useOrderManagementStore = defineStore("webshop-management-store", {
	state: () => ({
		options: {} as App.Http.Resources.Shop.CheckoutOptionResource,
		order: {} as App.Http.Resources.Shop.OrderResource,
	}),
	actions: {
		load(): Promise<void> {
			return WebshopService.Order.getCurrentBasket().then((response) => {
				this.order = response.data;
			});
		},
		addPhoto(photoData: AddPhoto): Promise<void> {
			return WebshopService.Order.addPhotoToBasket(photoData).then((response) => {
				this.order = response.data;
			});
		},
		addAlbum(albumData: AddAlbum): Promise<void> {
			return WebshopService.Order.addAlbumToBasket(albumData).then((response) => {
				this.order = response.data;
			});
		},
		removeItem(itemId: number): Promise<void> {
			return WebshopService.Order.removeItemFromBasket(itemId).then((response) => {
				this.order = response.data;
			});
		},
		async clear(): Promise<void> {
			await WebshopService.Order.clearBasket();
			return this.load();
		},
	},
});
