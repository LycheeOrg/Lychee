import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const WebshopService = {
	getCatalog(albumId: string): Promise<AxiosResponse<App.Http.Resources.Shop.CatalogResource>> {
		return axios.get(`${Constants.getApiUrl()}Shop`, { data: {}, params: { album_id: albumId } });
	},
};

export default WebshopService;
