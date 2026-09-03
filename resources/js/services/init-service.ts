import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

// LeftMenu.vue and AlbumPanel.vue each independently fetch the same
// GlobalRightsResource into their own store field on first page load.
// Coalescing concurrent in-flight requests avoids firing `Auth::rights`
// once per caller for the exact same answer.
let globalRightsRequest: Promise<AxiosResponse<App.Http.Resources.Rights.GlobalRightsResource>> | null = null;

const InitService = {
	fetchLandingData(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.LandingPageResource>> {
		return axios.get(`${Constants.getApiUrl()}LandingPage`, { data: {} });
	},

	fetchInitData(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.InitConfig>> {
		return axios.get(`${Constants.getApiUrl()}Gallery::Init`, { data: {} });
	},

	fetchGlobalRights(): Promise<AxiosResponse<App.Http.Resources.Rights.GlobalRightsResource>> {
		if (globalRightsRequest === null) {
			globalRightsRequest = axios.get(`${Constants.getApiUrl()}Auth::rights`, { data: {} }).finally(() => {
				globalRightsRequest = null;
			});
		}

		return globalRightsRequest;
	},

	fetchVersion(): Promise<AxiosResponse<App.Http.Resources.Root.VersionResource>> {
		return axios.get(`${Constants.getApiUrl()}Version`, { data: {} });
	},
	fetchFooter(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.FooterConfig>> {
		return axios.get(`${Constants.getApiUrl()}Gallery::Footer`, { data: {} });
	},
	fetchChangeLog(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.ChangeLogInfo[]>> {
		return axios.get(`${Constants.getApiUrl()}ChangeLogs`, { data: {} });
	},
};

export default InitService;
