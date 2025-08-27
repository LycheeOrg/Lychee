import axios, { AxiosResponse } from "axios";
import Constants from "./constants";

export type ImportFromServerRequest = {
	directories: string[];
	album_id: string | null;
	delete_imported: boolean;
	import_via_symlink: boolean;
	skip_duplicates: boolean;
	resync_metadata: boolean;
	delete_missing_photos: boolean;
	delete_missing_albums: boolean;
};

/**
 * Service for handling server import related operations
 */
const ImportService = {
	/**
	 * Get import from server options
	 * @returns Promise with import options
	 */
	getOptions(): Promise<AxiosResponse<App.Http.Resources.Admin.ImportFromServerOptionsResource>> {
		return axios.get(`${Constants.getApiUrl()}Import`, { data: {} });
	},

	/**
	 * Import files from server directories
	 * @param request Import request data
	 * @returns Promise with the import result
	 */
	importFromServer(request: ImportFromServerRequest): Promise<AxiosResponse<App.Http.Resources.Admin.ImportFromServerResource>> {
		return axios.post(`${Constants.getApiUrl()}Import`, request);
	},

	browse(directory: string): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.getApiUrl()}Import::browse`, { params: { directory }, data: {} });
	},
};

export default ImportService;
