import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type FaceMaintenanceParams = {
	sort_by?: "confidence" | "laplacian_variance";
	sort_dir?: "ASC" | "DESC";
	dismissed_only?: boolean;
	unassigned_only?: boolean;
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

	batchAssign(
		faceIds: string[],
		data: { person_id?: string; new_person_name?: string },
	): Promise<AxiosResponse<{ assigned_count: number; person_id: string }>> {
		return axios.post(`${Constants.getApiUrl()}Face/maintenance/batch-assign`, { face_ids: faceIds, ...data });
	},
};

export default FaceMaintenanceService;
