import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const PhotoNsfwDetectionsService = {
	getPhotoNsfwDetections(photoId: string): Promise<AxiosResponse<App.Http.Resources.Models.PhotoNsfwDetectionsResource>> {
		return axios.get(`${Constants.getApiUrl()}Photo/${photoId}/nsfw-detections`, { data: {} });
	},
};

export default PhotoNsfwDetectionsService;
