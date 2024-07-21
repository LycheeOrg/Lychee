import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type UpdateAbumData = {
	album_id: string;
	title: string;
	license: string | null;
	description: string | null;
	photo_sorting_column: App.Enum.ColumnSortingPhotoType | null;
	photo_sorting_order: App.Enum.OrderSortingType | null;
	album_sorting_column: App.Enum.ColumnSortingAlbumType | null;
	album_sorting_order: App.Enum.OrderSortingType | null;
	album_aspect_ratio: App.Enum.AspectRatioType | null;
	copyright: string | null;
};

export type UpdateTagAlbumData = {
	album_id: string;
	title: string;
	tags: string[];
	description: string | null;
	photo_sorting_column: App.Enum.ColumnSortingPhotoType | null;
	photo_sorting_order: App.Enum.OrderSortingType | null;
	copyright: string | null;
};

export type UpdateProtectionPolicyData = {
	album_id: string;
	password?: string | undefined;
	is_public: boolean;
	is_link_required: boolean;
	is_nsfw: boolean;
	grants_download: boolean;
	grants_full_photo_access: boolean;
};

const AlbumService = {
	getAll(): Promise<AxiosResponse<App.Http.Resources.Collections.RootAlbumResource>> {
		return axios.get(`${Constants.API_URL}Albums::get`, { data: {} });
	},

	get(album_id: string): Promise<AxiosResponse<App.Http.Resources.Models.AbstractAlbumResource>> {
		return axios.get(`${Constants.API_URL}Album::get`, { params: { album_id: album_id }, data: {} });
	},

	getLayout(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>> {
		return axios.get(`${Constants.API_URL}Gallery::getLayout`, { data: {} });
	},

	getMapProvider(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.MapProviderData>> {
		return axios.get(`${Constants.API_URL}Gallery::getMapProvider`, { data: {} });
	},

	updateAlbum(data: UpdateAbumData): Promise<AxiosResponse<any>> {
		return axios.post(`${Constants.API_URL}Album::update`, data);
	},

	updateTag(data: UpdateTagAlbumData): Promise<AxiosResponse<any>> {
		return axios.post(`${Constants.API_URL}Album::updateTag`, data);
	},

	updateProtectionPolicy(data: UpdateProtectionPolicyData): Promise<AxiosResponse<any>> {
		return axios.post(`${Constants.API_URL}Album::updateProtectionPolicy`, data);
	},
};

export default AlbumService;
