import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

/**
 * Query of `GET /api/v3/Insights` (Feature 085). `owner_id` and
 * `whole_instance` are honoured for administrators only; `album_id` for the
 * album's owner and administrators.
 */
export type InsightsQuery = {
	period: App.Enum.InsightsPeriodType;
	year?: number;
	from?: string;
	to?: string;
	owner_id?: number;
	whole_instance?: 1;
	album_id?: string;
};

const InsightsService = {
	get(query: InsightsQuery): Promise<AxiosResponse<App.Http.Resources.Insights.InsightsResource>> {
		return axios.get(`${Constants.getApiUrlV3()}Insights`, { params: query, data: {} });
	},
};

export default InsightsService;
