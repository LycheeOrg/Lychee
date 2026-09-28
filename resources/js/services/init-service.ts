import axios, { AxiosRequestConfig, type AxiosResponse } from "axios";
import Constants from "./constants";

// Coalesces concurrent callers (e.g. many <Thumb> instances mounting at once, all needing the
// code for the first time) onto a single in-flight request.
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

	// Not coalesced here: GlobalRightsState shares one request between its callers, and must be able
	// to start a new one when the identity changes rather than join a request made for the previous one.
	fetchGlobalRights(): Promise<AxiosResponse<App.Http.Resources.Rights.GlobalRightsResource>> {
		return axios.get(`${Constants.getApiUrl()}Auth::rights`, { data: {} });
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
