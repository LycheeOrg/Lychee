import ShopManagementService from "@/services/shop-management-service";
import { defineStore } from "pinia";
import { useLycheeStateStore } from "./LycheeState";

export type ShopManagementStateStore = ReturnType<typeof useShopManagementStore>;

export const useShopManagementStore = defineStore("webshop-management-store", {
	state: () => ({
		// flag to fetch data
		is_init: false,
		is_loading: false,

		currency: "EUR",
		default_price_cents: 0,
		default_license: "personal" as App.Enum.PurchasableLicenseType,
		default_size: "medium" as App.Enum.PurchasableSizeVariantType,
	}),
	actions: {
		init(): Promise<void> {
			// Guard for SE.
			if (useLycheeStateStore().is_se_enabled !== true) {
				return Promise.resolve();
			}

			// Check if already initialized
			if (this.is_init) {
				return Promise.resolve();
			}
			return this.load();
		},

		async load(): Promise<void> {
			// Guard for SE.
			if (useLycheeStateStore().is_se_enabled !== true) {
				return Promise.resolve();
			}

			// semaphore to avoid multiple calls
			if (this.is_loading) {
				while (this.is_loading) {
					await new Promise((resolve) => setTimeout(resolve, 100));
				}

				return Promise.resolve();
			}

			this.is_loading = true;

			return ShopManagementService.options()
				.then((response) => {
					this.is_init = true;
					this.is_loading = false;

					const data = response.data;

					this.currency = data.currency;
					this.default_price_cents = data.default_price_cents;
					this.default_license = data.default_license;
					this.default_size = data.default_size;
				})
				.catch((error) => {
					this.is_loading = false;
					throw error;
				});
		},
	},
});
