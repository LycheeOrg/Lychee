import axios, { AxiosRequestConfig, type AxiosResponse } from "axios";
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
	photo_layout: App.Enum.PhotoLayoutType | null;
	copyright: string | null;
	header_id: string | null;
	is_compact: boolean;
	album_timeline: App.Enum.TimelineAlbumGranularity | null;
	photo_timeline: App.Enum.TimelinePhotoGranularity | null;
};

export type UpdateTagAlbumData = {
	album_id: string;
	title: string;
	tags: string[];
	description: string | null;
	photo_sorting_column: App.Enum.ColumnSortingPhotoType | null;
	photo_sorting_order: App.Enum.OrderSortingType | null;
	copyright: string | null;
	photo_layout: App.Enum.PhotoLayoutType | null;
	photo_timeline: App.Enum.TimelinePhotoGranularity | null;
};

export type UpdateProtectionPolicyData = {
	album_id: string;
	password: string | undefined;
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
			axiosWithCache.storage.clear();
		} else {
			axiosWithCache.storage.remove("album_" + album_id);
		}
	},

	clearAlbums(): void {
		const axiosWithCache = axios as unknown as AxiosCacheInstance;
		axiosWithCache.storage.remove("albums");
	},

	getAll(): Promise<AxiosResponse<App.Http.Resources.Collections.RootAlbumResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrl()}Albums`, { data: {}, id: "albums" });
	},

	get(album_id: string): Promise<AxiosResponse<App.Http.Resources.Models.AbstractAlbumResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrl()}Album`, { params: { album_id: album_id }, data: {}, id: "album_" + album_id });
	},

	unlock(album_id: string, password: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::unlock`, { album_id: album_id, password: password });
	},

	getLayout(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.PhotoLayoutConfig>> {
		return axios.get(`${Constants.getApiUrl()}Gallery::getLayout`, { data: {} });
	},

	createAlbum(data: CreateAlbumData): Promise<AxiosResponse<string>> {
		return axios.post(`${Constants.getApiUrl()}Album`, data);
	},

	createTag(data: CreateTagAlbumData): Promise<AxiosResponse<string>> {
		return axios.post(`${Constants.getApiUrl()}TagAlbum`, data);
	},

	updateAlbum(data: UpdateAbumData): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Album`, data);
	},

	updateTag(data: UpdateTagAlbumData): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}TagAlbum`, data);
	},

	updateProtectionPolicy(data: UpdateProtectionPolicyData): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::updateProtectionPolicy`, data);
	},

	rename(album_id: string, title: string): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Album::rename`, { album_id: album_id, title: title });
	},

	delete(album_ids: string[]): Promise<AxiosResponse> {
		return axios.delete(`${Constants.getApiUrl()}Album`, { data: { album_ids: album_ids } });
	},

	getTargetListAlbums(album_ids: string[] | null): Promise<AxiosResponse<App.Http.Resources.Models.TargetAlbumResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Album::getTargetListAlbums`, { params: { album_ids: album_ids }, data: {} });
	},

	move(dest: string | null, album_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::move`, { album_id: dest, album_ids: album_ids });
	},

	merge(dest: string, album_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::merge`, { album_id: dest, album_ids: album_ids });
	},

	transfer(album_id: string, user_id: number): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::transfer`, { album_id: album_id, user_id: user_id });
	},

	frame(album_id: string | null): Promise<AxiosResponse<App.Http.Resources.Frame.FrameData>> {
		return axios.get(`${Constants.getApiUrl()}Frame`, { params: { album_id: album_id }, data: {} });
	},

	getMapProvider(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.MapProviderData>> {
		return axios.get(`${Constants.getApiUrl()}Map::provider`, { data: {} });
	},

	getMapData(album_id: string | undefined): Promise<AxiosResponse<App.Http.Resources.Collections.PositionDataResource>> {
		return axios.get(`${Constants.getApiUrl()}Map`, { params: { album_id: album_id }, data: {} });
	},

	download(album_ids: string[]): void {
		location.href = `${Constants.getApiUrl()}Zip?album_ids=${album_ids.join(",")}`;
	},

	uploadTrack(album_id: string, file: Blob): Promise<AxiosResponse> {
		const formData = new FormData();
		formData.append("album_id", album_id);
		formData.append("file", file);

		const config: AxiosRequestConfig<FormData> = {
			headers: {
				"Content-Type": "application/json",
			},
			transformRequest: [(data) => data],
		};

		return axios.post(`${Constants.getApiUrl()}Album::track`, formData, config);
	},

	deleteTrack(album_id: string): Promise<AxiosResponse> {
		return axios.delete(`${Constants.getApiUrl()}Album::track`, { params: { album_id: album_id }, data: {} });
	},
};

export default AlbumService;
