import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type UpdateAbumData = {
	albumID: string;
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
	albumID: string;
	title: string;
	tags: string[];
	description: string | null;
	photo_sorting_column: App.Enum.ColumnSortingPhotoType | null;
	photo_sorting_order: App.Enum.OrderSortingType | null;
	copyright: string | null;
};

export type UpdateProtectionPolicyData = {
	albumID: string;
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

	get(albumId: string): Promise<AxiosResponse<App.Http.Resources.Models.AbstractAlbumResource>> {
		return axios.get(`${Constants.API_URL}Album::get`, { data: { albumID: albumId } });
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
