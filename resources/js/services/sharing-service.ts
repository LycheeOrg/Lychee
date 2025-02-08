import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type CreateSharingData = {
	album_ids: string[];
	user_ids: number[];
	grants_download: boolean;
	grants_full_photo_access: boolean;
	grants_upload: boolean;
	grants_edit: boolean;
	grants_delete: boolean;
};

export type EditSharingData = {
	perm_id: number;
	grants_download: boolean;
	grants_full_photo_access: boolean;
	grants_upload: boolean;
	grants_edit: boolean;
	grants_delete: boolean;
};

export type PropagateSharingData = {
	album_id: string;
	shall_override: boolean;
};

const SharingService = {
	get(album_id: string): Promise<AxiosResponse<App.Http.Resources.Models.AccessPermissionResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Sharing`, { params: { album_id: album_id }, data: {} });
	},

	add(data: CreateSharingData): Promise<AxiosResponse<App.Http.Resources.Models.AccessPermissionResource[]>> {
		return axios.post(`${Constants.getApiUrl()}Sharing`, data);
	},

	edit(data: EditSharingData): Promise<AxiosResponse<App.Http.Resources.Models.AccessPermissionResource>> {
		return axios.patch(`${Constants.getApiUrl()}Sharing`, data);
	},

	propagate(data: PropagateSharingData): Promise<AxiosResponse> {
		return axios.put(`${Constants.getApiUrl()}Sharing`, data);
	},

	delete(sharing_id: number): Promise<AxiosResponse> {
		return axios.delete(`${Constants.getApiUrl()}Sharing`, { data: { perm_id: sharing_id } });
	},

	list(): Promise<AxiosResponse<App.Http.Resources.Models.AccessPermissionResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Sharing::all`, { data: {} });
	},
};

export default SharingService;
