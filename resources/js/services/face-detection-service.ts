import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const FaceDetectionService = {
	scanPhotos(photoIds: string[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection::scan`, { photo_ids: photoIds });
	},

	scanAlbum(albumId: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection::scan`, { album_id: albumId });
	},

	bulkScan(): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}FaceDetection::bulk-scan`, {});
	},

	assignFace(
		faceId: string,
		data: { person_id?: string; new_person_name?: string },
	): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Face/${faceId}/assign`, data);
	},

	toggleDismissed(faceId: string): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Face/${faceId}`, {});
	},

	destroyDismissed(): Promise<AxiosResponse<{ deleted_count: number }>> {
		return axios.delete(`${Constants.getApiUrl()}Face/dismissed`);
	},
};

export default FaceDetectionService;
