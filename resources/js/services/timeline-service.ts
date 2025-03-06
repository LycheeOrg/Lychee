import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const TimelineService = {
	datedTimeline(date: string): Promise<AxiosResponse<App.Http.Resources.Timeline.TimelineResource>> {
		return axios.get(`${Constants.getApiUrl()}Timeline`, { params: { date: date }, data: {} });
	},

	timeline(page: number = 1): Promise<AxiosResponse<App.Http.Resources.Timeline.TimelineResource>> {
		return axios.get(`${Constants.getApiUrl()}Timeline`, { params: { page: page }, data: {} });
	},

	init(): Promise<AxiosResponse<App.Http.Resources.Timeline.InitResource>> {
		return axios.get(`${Constants.getApiUrl()}Timeline::init`, { data: {} });
	},

	dates(): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.getApiUrl()}Timeline::dates`, { data: {} });
	},
};

export default TimelineService;
