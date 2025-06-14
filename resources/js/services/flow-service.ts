import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const FlowService = {
	get(page: number = 1): Promise<AxiosResponse<App.Http.Resources.Flow.FlowResource>> {
		return axios.get(`${Constants.getApiUrl()}Flow`, { params: { page: page }, data: {} });
	},

	init(): Promise<AxiosResponse<App.Http.Resources.Flow.InitResource>> {
		return axios.get(`${Constants.getApiUrl()}Flow::init`, { data: {} });
	},
};

export default FlowService;
