import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";
import { AxiosCacheInstance } from "axios-cache-interceptor";

const TagsService = {
	clearCache(): void {
		const axiosWithCache = axios as unknown as AxiosCacheInstance;
		axiosWithCache.storage.remove("tags");
	},

	get(): Promise<AxiosResponse<App.Http.Resources.Tags.TagResource[]>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrl()}Tags`, { data: {}, id: "tags" });
	},
};

export default TagsService;
