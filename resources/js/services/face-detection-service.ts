import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const FaceDetectionService = {
	scanPhotos(photoIds: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection/scan`, { photo_ids: photoIds });
	},

	scanAlbum(albumId: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection/scan`, { album_id: albumId });
	},

	bulkScan(): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection/bulk-scan`, {});
	},

	assignFace(faceId: string, data: { person_id?: string; new_person_name?: string }): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Face/${faceId}/assign`, data);
	},

	unassignFace(faceId: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Face/${faceId}/assign`, {});
	},

	batchFaces(data: {
		face_ids: string[];
		action: "unassign" | "assign";
		person_id?: string;
		new_person_name?: string;
	}): Promise<AxiosResponse<{ affected_count: number; person_id: string | null }>> {
		return axios.post(`${Constants.getApiUrl()}Face/batch`, data);
	},

	toggleDismissed(faceId: string): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Face/${faceId}`, {});
	},

	destroyDismissed(): Promise<AxiosResponse<{ deleted_count: number }>> {
		return axios.delete(`${Constants.getApiUrl()}Face/dismissed`);
	},

	unclusterFaces(clusterLabel: number, faceIds: string[]): Promise<AxiosResponse<{ unclustered_count: number }>> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection/clusters/${clusterLabel}/uncluster`, { face_ids: faceIds });
	},

	getAlbumPeople(albumId: string, page: number = 1): Promise<AxiosResponse<App.Http.Resources.Collections.PaginatedPersonsResource>> {
		return axios.get(`${Constants.getApiUrl()}Album/${albumId}/people`, { params: { page }, data: {} });
	},
};

export default FaceDetectionService;
