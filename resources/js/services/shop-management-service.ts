import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type Price = {
	size_variant_type: App.Enum.PurchasableSizeVariantType;
	license_type: App.Enum.PurchasableLicenseType;
	price: number; // in cents
};

export type PurchasableRequest = {
	album_ids: string[];
	description: string | null;
	note: string | null;
	prices: Price[];
	applies_to_subalbums: boolean;
};

const ShopManagementService = {
	options(): Promise<AxiosResponse<App.Http.Resources.Shop.ConfigOptionResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Management/Options`, { data: {} });
	},

	list(albumIds: string[] = []): Promise<AxiosResponse<App.Http.Resources.Shop.EditablePurchasableResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Management/List`, { data: {}, params: { album_ids: albumIds } });
	},

	createPurchasableAlbum(request: PurchasableRequest): Promise<AxiosResponse<App.Http.Resources.Shop.PurchasableResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Management/Purchasable/Album`, request);
	},
	createPurchasablePhoto(request: PurchasableRequest): Promise<AxiosResponse<App.Http.Resources.Shop.PurchasableResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Management/Purchasable/Photo`, request);
	},
	updatePurchasable(request: PurchasableRequest): Promise<AxiosResponse<App.Http.Resources.Shop.PurchasableResource>> {
		return axios.put(`${Constants.getApiUrl()}Shop/Management/Purchasable`, request);
	},
	deletePurchasable(id: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Shop/Management/Purchasables`, { data: {}, params: { purchasable_ids: [id] } });
	},
};

export default ShopManagementService;
