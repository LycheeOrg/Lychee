import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";
import { AxiosCacheInstance } from "axios-cache-interceptor";

export type CreateAlbumData = {
	title: string;
	parent_id: string | null;
};

export type CreateTagAlbumData = {
	title: string;
	tags: string[];
};

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
	clearCache(album_id: string | null = null): void {
		const axiosWithCache = axios as unknown as AxiosCacheInstance;
		if (!album_id) {
			// @ts-expect-error
			axiosWithCache.storage.data = {};
		} else {
			axiosWithCache.storage.remove("album_" + album_id);
		}
	},

	getAll(): Promise<AxiosResponse<App.Http.Resources.Collections.RootAlbumResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.API_URL}Albums`, { data: {}, id: "albums" });
	},

	get(album_id: string): Promise<AxiosResponse<App.Http.Resources.Models.AbstractAlbumResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.API_URL}Album`, { params: { album_id: album_id }, data: {}, id: "album_" + album_id });
	},

	getLayout(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>> {
		return axios.get(`${Constants.API_URL}Gallery::getLayout`, { data: {} });
	},

	getMapProvider(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.MapProviderData>> {
		return axios.get(`${Constants.API_URL}Gallery::getMapProvider`, { data: {} });
	},

	createAlbum(data: CreateAlbumData): Promise<AxiosResponse<string>> {
		return axios.post(`${Constants.API_URL}Album`, data);
	},

	createTag(data: CreateTagAlbumData): Promise<AxiosResponse<string>> {
		return axios.post(`${Constants.API_URL}TagAlbum`, data);
	},

	updateAlbum(data: UpdateAbumData): Promise<AxiosResponse> {
		return axios.patch(`${Constants.API_URL}Album`, data);
	},

	updateTag(data: UpdateTagAlbumData): Promise<AxiosResponse> {
		return axios.patch(`${Constants.API_URL}TagAlbum`, data);
	},

	updateProtectionPolicy(data: UpdateProtectionPolicyData): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Album::updateProtectionPolicy`, data);
	},

	delete(album_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Album::delete`, { album_ids: album_ids });
	},

	getTargetListAlbums(album_id: string | null): Promise<AxiosResponse<App.Http.Resources.Models.TargetAlbumResource[]>> {
		return axios.get(`${Constants.API_URL}Album::getTargetListAlbums`, { params: { album_id: album_id }, data: {} });
	},

	move(dest: string | null, album_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Album::move`, { album_id: dest, album_ids: album_ids });
	},

	merge(dest: string, album_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Album::merge`, { album_id: dest, album_ids: album_ids });
	},

	transfer(album_id: string, user_id: number): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Album::transfer`, { album_id: album_id, user_id: user_id });
	},
};

export default AlbumService;
