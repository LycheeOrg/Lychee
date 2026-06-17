import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const PhotoFacesService = {
	getPhotoFaces(photoId: string): Promise<AxiosResponse<App.Http.Resources.Models.PhotoFacesResource>> {
		return axios.get(`${Constants.getApiUrl()}Photo/${photoId}/faces`, { data: {} });
	},
};

export default PhotoFacesService;
