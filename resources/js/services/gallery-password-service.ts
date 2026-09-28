import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const GalleryPasswordService = {
	unlock(password: string): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}Gallery::unlock`, { password: password });
	},
};

export default GalleryPasswordService;
