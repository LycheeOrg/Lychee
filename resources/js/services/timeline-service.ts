import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const TimelineService = {
	timeline(page: number = 1): Promise<AxiosResponse<App.Http.Resources.Timeline.TimelineResource>> {
		return axios.get(`${Constants.getApiUrl()}Timeline`, { params: { page: page }, data: {} });
	},

	init(): Promise<AxiosResponse<App.Http.Resources.Timeline.InitResource>> {
		return axios.get(`${Constants.getApiUrl()}Timeline::init`, { data: {} });
	},
};

export default TimelineService;
