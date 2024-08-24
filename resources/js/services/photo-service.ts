import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const PhotoService = {
	get(photo_id: string): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.get(`${Constants.API_URL}Photo`, { params: { photo_id: photo_id }, data: {} });
	},

	importFromUrl(urls: string[], album_id: string | null): Promise<AxiosResponse<string>> {
		return axios.post(`${Constants.API_URL}Photo::fromUrl`, { urls: urls.filter(Boolean), album_id: album_id });
	},
};

export default PhotoService;
