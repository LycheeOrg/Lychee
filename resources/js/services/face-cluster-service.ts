import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const FaceClusterService = {
	getClusters(page: number = 1): Promise<AxiosResponse<App.Http.Resources.Collections.PaginatedClustersResource>> {
		return axios.get(`${Constants.getApiUrl()}FaceDetection/clusters`, { params: { page }, data: {} });
	},

	assignCluster(label: number, data: { person_id?: string; new_person_name?: string }): Promise<AxiosResponse<{ assigned_count: number }>> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection/clusters/${label}/assign`, data);
	},

	dismissCluster(label: number): Promise<AxiosResponse<{ dismissed_count: number }>> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection/clusters/${label}/dismiss`, {});
	},

	unclusterFaces(label: number, faceIds: string[]): Promise<AxiosResponse<{ unclustered_count: number }>> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection/clusters/${label}/uncluster`, { face_ids: faceIds });
	},

	getClusterFaces(label: number, page: number = 1): Promise<AxiosResponse<{ data: App.Http.Resources.Models.FaceResource[]; meta: object }>> {
		return axios.get(`${Constants.getApiUrl()}FaceDetection/clusters/${label}/faces`, { params: { page }, data: {} });
	},

	runClustering(): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::runFaceClustering`, {});
	},
};

export default FaceClusterService;
