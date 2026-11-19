import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const FlowService = {
	get(page: number = 1): Promise<AxiosResponse<App.Http.Resources.Flow.FlowResource>> {
		return axios.get(`${Constants.getApiUrl()}Flow`, { params: { page: page }, data: {} });
	},

	init(): Promise<AxiosResponse<App.Http.Resources.Flow.InitResource>> {
		return axios.get(`${Constants.getApiUrl()}Flow::init`, { data: {} });
	},

	/**
	 * Feature 068 (FR-068-01): the whole-scope, unpaginated v3 SoA listing —
	 * no `page` param, no pagination state on the caller's side at all.
	 */
	getV3(): Promise<AxiosResponse<App.Http.Resources.V3.FlowListResource>> {
		return axios.get(`${Constants.getApiUrlV3()}Flow`, { data: {} });
	},
};

export default FlowService;
