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

const SharingService = {
	get(album_id: string): Promise<AxiosResponse<App.Http.Resources.Models.AccessPermissionResource[]>> {
		return axios.get(`${Constants.API_URL}Sharing`, { params: { album_id: album_id }, data: {} });
	},

	add(data: CreateSharingData): Promise<AxiosResponse<App.Http.Resources.Models.AccessPermissionResource[]>> {
		return axios.post(`${Constants.API_URL}Sharing`, data);
	},

	edit(data: EditSharingData): Promise<AxiosResponse<App.Http.Resources.Models.AccessPermissionResource>> {
		return axios.patch(`${Constants.API_URL}Sharing`, data);
	},

	delete(sharing_id: number): Promise<AxiosResponse> {
		console.log(sharing_id);
		return axios.delete(`${Constants.API_URL}Sharing`, { data: { perm_id: sharing_id } });
	},

	list(): Promise<AxiosResponse<App.Http.Resources.Models.AccessPermissionResource[]>> {
		return axios.get(`${Constants.API_URL}Sharing::all`, { data: {} });
	},
};

export default SharingService;
