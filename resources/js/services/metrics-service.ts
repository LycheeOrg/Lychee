import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const MetricsService = {
	get(): Promise<AxiosResponse<object>> {
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
