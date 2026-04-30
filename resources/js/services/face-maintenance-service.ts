import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type FaceMaintenanceParams = {
	sort_by?: "confidence" | "laplacian_variance";
	sort_dir?: "asc" | "desc";
	page?: number;
	per_page?: number;
};

export type PaginatedFacesResponse = {
	data: App.Http.Resources.Models.FaceResource[];
	current_page: number;
	last_page: number;
	per_page: number;
	total: number;
};

const FaceMaintenanceService = {
	getFaces(params: FaceMaintenanceParams = {}): Promise<AxiosResponse<PaginatedFacesResponse>> {
		return axios.get(`${Constants.getApiUrl()}Face/maintenance`, { params, data: {} });
	},

	batchDismiss(faceIds: string[]): Promise<AxiosResponse<{ dismissed_count: number }>> {
		return axios.post(`${Constants.getApiUrl()}Face/maintenance/batch-dismiss`, { face_ids: faceIds });
	},
};

export default FaceMaintenanceService;
