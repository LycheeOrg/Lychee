import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type PhotoUpdateRequest = {
	title: string;
	description: string;
	tags: string[];
	license: App.Enum.LicenseType;
	upload_date: string;
	taken_at: string | null;
};

const PhotoService = {
	get(photo_id: string): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.get(`${Constants.getApiUrl()}Photo`, { params: { photo_id: photo_id }, data: {} });
	},

	importFromUrl(urls: string[], album_id: string | null): Promise<AxiosResponse<string>> {
		return axios.post(`${Constants.getApiUrl()}Photo::fromUrl`, { urls: urls.filter(Boolean), album_id: album_id });
	},

	update(photo_id: string, data: PhotoUpdateRequest): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.patch(`${Constants.getApiUrl()}Photo?photo_id=${photo_id}`, data);
	},

	rename(photo_id: string, title: string): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Photo::rename`, { photo_id: photo_id, title: title });
	},

	move(destination_id: string | null, photo_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::move`, { album_id: destination_id, photo_ids: photo_ids });
	},

	tags(photo_ids: string[], tags: string[], shall_override: boolean): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Photo::tags`, { photo_ids: photo_ids, tags: tags, shall_override: shall_override });
	},

	copy(destination_id: string | null, photo_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::copy`, { album_id: destination_id, photo_ids: photo_ids });
	},

	delete(photo_ids: string[]): Promise<AxiosResponse> {
		return axios.delete(`${Constants.getApiUrl()}Photo`, { data: { photo_ids: photo_ids } });
	},

	star(photo_ids: string[], is_starred: boolean): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::star`, { photo_ids: photo_ids, is_starred: is_starred });
	},

	duplicate(destination_id: string | null, photo_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::duplicate`, { album_id: destination_id, photo_ids: photo_ids });
	},

	rotate(photo_id: string, direction: "1" | "-1"): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::rotate`, { photo_id: photo_id, direction: direction });
	},

	setAsCover(photo_id: string, album_id: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::cover`, { photo_id: photo_id, album_id: album_id });
	},

	setAsHeader(photo_id: string, album_id: string, is_compact: boolean): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::header`, { header_id: photo_id, album_id: album_id, is_compact: is_compact });
	},

	download(photo_ids: string[], download_type: App.Enum.DownloadVariantType = "ORIGINAL"): void {
		window.open(`${Constants.getApiUrl()}Zip?photo_ids=${photo_ids.join(",")}&variant=${download_type}`, "_blank");
	},
};

export default PhotoService;
