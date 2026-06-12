import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const CatalogService = {
	getCatalog(albumId: string): Promise<AxiosResponse<App.Http.Resources.Shop.CatalogResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop`, { data: {}, params: { album_id: albumId } });
	},
	getCatalogueSizes(purchasableId: number): Promise<AxiosResponse<App.Http.Resources.Shop.CatalogueSizesResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Catalogue/Purchasable/${purchasableId}/Sizes`, { data: {} });
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

export type AddPrintPhoto = {
	photo_id: string;
	album_id?: string;
	print_size_id: number;
	notes?: string;
};

export type AddPixelPhoto = {
	photo_id: string;
	album_id?: string;
	pixel_size_id: number;
	license_type: App.Enum.PurchasableLicenseType;
	notes?: string;
};

export type CreateCheckout = {
	email: string | undefined;
	provider: App.Enum.OmnipayProviderType | undefined;
	shipping_street_name?: string;
	shipping_street_number?: string;
	shipping_additional_info?: string;
	shipping_city?: string;
	shipping_post_code?: string;
	shipping_country?: string;
};

export type OfflineCheckout = {
	email: string | undefined;
};

export type CardDetails = {
	number: string;
	expiryMonth: string;
	expiryYear: string;
	cvv: string;
};

export type ItemLink = {
	id: number;
	download_link: string;
};

const OrderService = {
	getCurrentBasket(): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Basket`, { data: {}, params: {} });
	},
	addPhotoToBasket(data: AddPhoto): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Basket/Photo`, data);
	},
	addAlbumToBasket(data: AddAlbum): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Basket/Album`, data);
	},
	addPrintPhotoToBasket(data: AddPrintPhoto): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Basket/Print`, data);
	},
	addPixelPhotoToBasket(data: AddPixelPhoto): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Basket/Pixel`, data);
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
	markAsPaid(orderId: number): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Order/${orderId}`, {});
	},
	markAsDelivered(orderId: number, items: ItemLink[]): Promise<AxiosResponse<void>> {
		return axios.put(`${Constants.getApiUrl()}Shop/Order/${orderId}`, { items });
	},
	forget(): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Shop/Order`, { data: {} });
	},
};

const CheckoutService = {
	getOptions(): Promise<AxiosResponse<App.Http.Resources.Shop.CheckoutOptionResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Checkout/Options`, { data: {} });
	},
	createSession(data: CreateCheckout): Promise<AxiosResponse<App.Http.Resources.Shop.OrderResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Checkout/Create-session`, data);
	},
	processCheckout(data: {
		additional_data: { card?: CardDetails; cardToken?: string; paymentToken?: string };
	}): Promise<AxiosResponse<App.Http.Resources.Shop.CheckoutResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Checkout/Process`, data);
	},
	cancelPayment(transactionId: string): Promise<AxiosResponse<App.Http.Resources.Shop.CheckoutResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Checkout/Cancel/${transactionId}`, { data: {} });
	},
	completePayment(transactionId: string, provider: string): Promise<AxiosResponse<App.Http.Resources.Shop.CheckoutResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop/Checkout/Finalize/${provider}/${transactionId}`, { data: {} });
	},
	offline(data: OfflineCheckout): Promise<AxiosResponse<App.Http.Resources.Shop.CheckoutResource>> {
		return axios.post(`${Constants.getApiUrl()}Shop/Checkout/Offline`, data);
	},
};

const WebshopService = {
	Catalog: CatalogService,
	Order: OrderService,
	Checkout: CheckoutService,
};

export default WebshopService;
