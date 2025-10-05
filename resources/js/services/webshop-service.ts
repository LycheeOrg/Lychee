import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const CatalogService = {
	getCatalog(albumId: string): Promise<AxiosResponse<App.Http.Resources.Shop.CatalogResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop`, { data: {}, params: { album_id: albumId } });
	},
};

export type AddPhoto = {
	photo_id: string;
	album_id?: string;
	size_variant: App.Enum.PurchasableSizeVariantType;
	license_type: App.Enum.PurchasableLicenseType;
	email?: string;
	notes?: string;
};

export type AddAlbum = {
	album_id: string;
	size_variant: App.Enum.PurchasableSizeVariantType;
	license_type: App.Enum.PurchasableLicenseType;
	email?: string;
	notes?: string;
	include_subalbums?: boolean;
};

export type CreateCheckout = {
	email: string | undefined;
	provider: App.Enum.OmnipayProviderType | undefined;
};

export type CardDetails = {
	number: string;
	expiryMonth: string;
	expiryYear: string;
	cvv: string;
};

const OrderService = {
	getCurrentBasket(orderId?: number): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Basket`, { data: {}, params: { order_id: orderId } });
	},
	addPhotoToBasket(data: AddPhoto): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Basket/Photo`, data);
	},
	addAlbumToBasket(data: AddAlbum): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Basket/Album`, data);
	},
	removeItemFromBasket(itemId: number): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.delete(`${Constants.getApiUrl()}Shop/Basket/item`, { data: {}, params: { item_id: itemId } });
	},
	clearBasket(): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Shop/Basket`, { data: {} });
	},
	list(): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Order/List`, { data: {} });
	},
	get(orderId: number, transactionId?: string): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Order/${orderId}`, { data: {}, params: { transaction_id: transactionId } });
	},
};

const CheckoutService = {
	getOptions(): Promise<AxiosResponse<App.Http.Resources.Shop.CheckoutOptionResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Checkout/Options`, { data: {} });
	},
	createSession(data: CreateCheckout): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Checkout/Create-session`, data);
	},
	processCheckout(data: { additional_data: { card?: CardDetails } }): Promise<AxiosResponse<App.Http.Resources.Shop.CheckoutResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Checkout/Process`, data);
	},
};

const WebshopService = {
	Catalog: CatalogService,
	Order: OrderService,
	Checkout: CheckoutService,
};

export default WebshopService;
