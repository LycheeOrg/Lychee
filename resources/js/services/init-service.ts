import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const InitService = {
	fetchLandingData(): Promise<AxiosResponse<App.Http.Resources.LandingPageResource>> {
		return axios.get(`${Constants.API_URL}LandingPage`, { data: {} });
	},

	fetchInitData(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.InitConfig>> {
		return axios.get(`${Constants.API_URL}Gallery::Init`, { data: {} });
	},

	fetchGlobalRights(): Promise<AxiosResponse<App.Http.Resources.Rights.GlobalRightsResource>> {
		return axios.get(`${Constants.API_URL}Auth::rights`, { data: {} });
	},

	fetchVersion(): Promise<AxiosResponse<App.Http.Resources.Root.VersionResource>> {
		return axios.get(`${Constants.API_URL}Version`, { data: {} });
	},
};

export default InitService;
