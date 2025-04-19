import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const MetricsService = {
	get(): Promise<AxiosResponse<App.Http.Resources.Models.LiveMetricsResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Metrics`, { data: {} });
	},

	photo(photo_id: string): Promise<AxiosResponse<null>> {
		return axios.post(`${Constants.getApiUrl()}Metrics::photo`, { photo_ids: [photo_id] });
	},

	favourite(photo_id: string): Promise<AxiosResponse<null>> {
		return axios.post(`${Constants.getApiUrl()}Metrics::favourite`, { photo_ids: [photo_id] });
	},
};

export default MetricsService;
