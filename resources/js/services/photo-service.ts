import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const PhotoService = {
	get(photoId: string): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.get(`${Constants.API_URL}Photo::get`, { data: { photoID: photoId } });
	},
};

export default PhotoService;
