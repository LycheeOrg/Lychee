import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const StatisticsService = {
	getUserSpace(): Promise<AxiosResponse<App.Http.Resources.Statistics.UserSpace[]>> {
		return axios.get(`${Constants.API_URL}Statistics::userSpace`, { data: {} });
	},
	getSizeVariantSpace(): Promise<AxiosResponse<App.Http.Resources.Statistics.Sizes[]>> {
		return axios.get(`${Constants.API_URL}Statistics::sizeVariantSpace`, { data: {} });
	},
	getAlbumSpace(albumId: string | null = null): Promise<AxiosResponse<App.Http.Resources.Statistics.Album[]>> {
		return axios.get(`${Constants.API_URL}Statistics::albumSpace`, { params: { albumId: albumId }, data: {} });
	},
	getTotalAlbumSpace(albumId: string | null = null): Promise<AxiosResponse<App.Http.Resources.Statistics.Album[]>> {
		return axios.get(`${Constants.API_URL}Statistics::totalAlbumSpace`, { params: { albumId: albumId }, data: {} });
	},
};

export default StatisticsService;
