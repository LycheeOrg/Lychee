import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const InitService = {
	fetchLandingData(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.LandingPageResource>> {
		return axios.get(`${Constants.getApiUrl()}LandingPage`, { data: {} });
	},

	fetchInitData(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.InitConfig>> {
		return axios.get(`${Constants.getApiUrl()}Gallery::Init`, { data: {} });
	},

	fetchGlobalRights(): Promise<AxiosResponse<App.Http.Resources.Rights.GlobalRightsResource>> {
		return axios.get(`${Constants.getApiUrl()}Auth::rights`, { data: {} });
	},

	fetchVersion(): Promise<AxiosResponse<App.Http.Resources.Root.VersionResource>> {
		return axios.get(`${Constants.getApiUrl()}Version`, { data: {} });
	},
	fetchFooter(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.FooterConfig>> {
		return axios.get(`${Constants.getApiUrl()}Gallery::Footer`, { data: {} });
	},
};

export default InitService;
