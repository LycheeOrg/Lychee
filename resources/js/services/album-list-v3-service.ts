import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type AlbumListV3Params = {
	with_parent_id?: boolean;
	for_bulk_edit?: boolean;
};

const AlbumListV3Service = {
	getAlbums(params: AlbumListV3Params = {}): Promise<AxiosResponse<App.Http.Resources.V3.AlbumListResource>> {
		return axios.get(`${Constants.getApiUrlV3()}Albums`, { params: params, data: {} });
	},

	getAccessPermissions(): Promise<AxiosResponse<App.Http.Resources.V3.AlbumAccessPermissionResource>> {
		return axios.get(`${Constants.getApiUrlV3()}Albums::accessPermissions`, { data: {} });
	},
};

export default AlbumListV3Service;
