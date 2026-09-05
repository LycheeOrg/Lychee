import axios, { AxiosRequestConfig, type AxiosResponse } from "axios";
import Constants from "./constants";

// LeftMenu.vue and AlbumPanel.vue each independently fetch the same
// GlobalRightsResource into their own store field on first page load.
// Coalescing concurrent in-flight requests avoids firing `Auth::rights`
// once per caller for the exact same answer.
let globalRightsRequest: Promise<AxiosResponse<App.Http.Resources.Rights.GlobalRightsResource>> | null = null;

// Coalesces concurrent callers (e.g. many <Thumb> instances mounting at once, all needing the
// code for the first time) onto a single in-flight request, same as globalRightsRequest above.
let macRequest: Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.TemporaryLinkMacConfig>> | null = null;

const InitService = {
	fetchLandingData(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.LandingPageResource>> {
		return axios.get(`${Constants.getApiUrl()}LandingPage`, { data: {} });
	},

	fetchInitData(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.InitConfig>> {
		return axios.get(`${Constants.getApiUrl()}Gallery::Init`, { data: {} });
	},

	fetchMac(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.TemporaryLinkMacConfig>> {
		if (macRequest === null) {
			// The MAC lifetime is admin-configurable and can be set below axios-cache-interceptor's
			// 300s default TTL. A cached response would let the 401 retry in axios-config.ts's
			// response interceptor reuse the same expired MAC instead of fetching a fresh one.
			macRequest = axios
				.get(`${Constants.getApiUrl()}Gallery::getMac`, { data: {}, cache: { enabled: false } } as AxiosRequestConfig)
				.finally(() => {
					macRequest = null;
				});
		}
		return macRequest;
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
