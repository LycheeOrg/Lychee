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

export type PhotoMove = {
	photo_ids: string[];
	album_id: string | null;
	from_id: string | null;
};

const PhotoService = {
	get(photo_id: string): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.get(`${Constants.getApiUrl()}Photo`, { params: { photo_id: photo_id }, data: {} });
	},

	importFromUrl(urls: string[], album_id: string | null): Promise<AxiosResponse<string>> {
		return axios.post(`${Constants.getApiUrl()}Photo::fromUrl`, { urls: urls.filter(Boolean), album_id: album_id });
	},

	update(photo_id: string, album_id: string | null, data: PhotoUpdateRequest): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.patch(`${Constants.getApiUrl()}Photo?photo_id=${photo_id}&from_id=${album_id ? album_id : ""}`, data);
	},

	rename(photo_id: string, title: string): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Photo::rename`, { photo_id: photo_id, title: title });
	},

	move(data: PhotoMove): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::move`, data);
	},

	tags(photo_ids: string[], tags: string[], shall_override: boolean): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Photo::tags`, { photo_ids: photo_ids, tags: tags, shall_override: shall_override });
	},

	license(photo_ids: string[], license: App.Enum.LicenseType): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Photo::license`, { photo_ids: photo_ids, license: license });
	},

	copy(destination_id: string | null, photo_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::copy`, { album_id: destination_id, photo_ids: photo_ids });
	},

	delete(photo_ids: string[], from_id: string): Promise<AxiosResponse> {
		return axios.delete(`${Constants.getApiUrl()}Photo`, { data: { photo_ids: photo_ids, from_id: from_id } });
	},

	highlight(photo_ids: string[], is_highlighted: boolean): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::highlight`, { photo_ids: photo_ids, is_highlighted: is_highlighted });
	},

	duplicate(destination_id: string | null, photo_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::duplicate`, { album_id: destination_id, photo_ids: photo_ids });
	},

	rotate(photo_id: string, direction: "1" | "-1", album_id: string | null): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::rotate`, { photo_id: photo_id, direction: direction, from_id: album_id });
	},

	setAsCover(photo_id: string, album_id: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::cover`, { photo_id: photo_id, album_id: album_id });
	},

	setAsHeader(photo_id: string, album_id: string, is_compact: boolean): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Album::header`, { header_id: photo_id, album_id: album_id, is_compact: is_compact });
	},

	download(photo_ids: string[], from_id: string | undefined, download_type: App.Enum.DownloadVariantType = "ORIGINAL"): void {
		location.href = `${Constants.getApiUrl()}Zip?photo_ids=${photo_ids.join(",")}&variant=${download_type}&from_id=${from_id ?? null}`;
	},

	watermark(photo_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Photo::watermark`, { photo_ids: photo_ids });
	},

	setRating(photo_id: string, rating: 0 | 1 | 2 | 3 | 4 | 5): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.post(`${Constants.getApiUrl()}Photo::setRating`, { photo_id: photo_id, rating: rating });
	},
};

export default PhotoService;
