import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type PhotoUpdateRequest = {
	title: string;
	description: string;
	tags: string[];
	license: App.Enum.LicenseType;
	upload_date: string;
};

const PhotoService = {
	get(photo_id: string): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.get(`${Constants.API_URL}Photo`, { params: { photo_id: photo_id }, data: {} });
	},

	importFromUrl(urls: string[], album_id: string | null): Promise<AxiosResponse<string>> {
		return axios.post(`${Constants.API_URL}Photo::fromUrl`, { urls: urls.filter(Boolean), album_id: album_id });
	},

	update(photo_id: string, data: PhotoUpdateRequest): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.patch(`${Constants.API_URL}Photo?photo_id=${photo_id}`, data);
	},

	move(destination_id: string | null, photo_ids: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Photo::move`, { album_id: destination_id, photo_ids: photo_ids });
	},

	delete(photo_ids: string[]): Promise<AxiosResponse> {
		return axios.delete(`${Constants.API_URL}Photo`, { data: { photo_ids: photo_ids } });
	},
};

export default PhotoService;
