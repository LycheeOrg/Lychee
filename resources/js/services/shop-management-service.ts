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
	print_sizes: PrintSizeAssignment[];
	pixel_sizes: PixelSizeAssignment[];
	applies_to_subalbums: boolean;
};

export type PrintSizeAssignment = {
	print_size_id: number;
	price: number; // in cents
};

export type PixelSizeAssignment = {
	pixel_size_id: number;
	price: number; // in cents
	license_type: App.Enum.PurchasableLicenseType;
};

export type UpdatePurchasablePricesRequest = {
	purchasable_id: number;
	description: string | null;
	note: string | null;
	prices: Price[];
	print_sizes: PrintSizeAssignment[];
	pixel_sizes: PixelSizeAssignment[];
};

export type PrintSizeRequest = {
	label: string;
	width: number;
	height: number;
	unit: string;
	paper_type: string | null;
	is_active: boolean;
};

export type PrintSizeUpdateRequest = PrintSizeRequest & { print_size_id: number };

export type PixelSizeRequest = {
	label: string;
	width: number;
	height: number;
	is_active: boolean;
};

export type PixelSizeUpdateRequest = PixelSizeRequest & { pixel_size_id: number };

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
	updatePurchasablePrices(request: UpdatePurchasablePricesRequest): Promise<AxiosResponse<App.Http.Resources.Shop.EditablePurchasableResource>> {
		return axios.put(`${Constants.getApiUrl()}Shop/Management/Purchasable/Price`, request);
	},
	deletePurchasable(id: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Shop/Management/Purchasables`, { data: {}, params: { purchasable_ids: [id] } });
	},

	// Print size catalogue management
	listPrintSizes(): Promise<AxiosResponse<App.Http.Resources.Shop.PrintSizeResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Management/PrintSize`, { data: {} });
	},
	createPrintSize(request: PrintSizeRequest): Promise<AxiosResponse<App.Http.Resources.Shop.PrintSizeResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Management/PrintSize`, request);
	},
	updatePrintSize(request: PrintSizeUpdateRequest): Promise<AxiosResponse<App.Http.Resources.Shop.PrintSizeResource>> {
		return axios.put(`${Constants.getApiUrl()}Shop/Management/PrintSize`, request);
	},
	deletePrintSize(id: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Shop/Management/PrintSize`, { data: {}, params: { print_size_id: id } });
	},

	// Pixel size catalogue management
	listPixelSizes(): Promise<AxiosResponse<App.Http.Resources.Shop.PixelSizeResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Management/PixelSize`, { data: {} });
	},
	createPixelSize(request: PixelSizeRequest): Promise<AxiosResponse<App.Http.Resources.Shop.PixelSizeResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Management/PixelSize`, request);
	},
	updatePixelSize(request: PixelSizeUpdateRequest): Promise<AxiosResponse<App.Http.Resources.Shop.PixelSizeResource>> {
		return axios.put(`${Constants.getApiUrl()}Shop/Management/PixelSize`, request);
	},
	deletePixelSize(id: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Shop/Management/PixelSize`, { data: {}, params: { pixel_size_id: id } });
	},
};

export default ShopManagementService;
