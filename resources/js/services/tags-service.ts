import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";
import { AxiosCacheInstance } from "axios-cache-interceptor";

const TagsService = {
	clearCache(): void {
		const axiosWithCache = axios as unknown as AxiosCacheInstance;
		axiosWithCache.storage.remove("tags");
	},

	get(): Promise<AxiosResponse<App.Http.Resources.Tags.TagsResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrl()}Tags`, { data: {}, id: "tags" });
	},

	delete(tagId: number): Promise<AxiosResponse<void>> {
		this.clearCache();
		return axios.delete(`${Constants.getApiUrl()}Tag`, { data: { tags: [tagId] } });
	},

	rename(tagId: number, newName: string): Promise<AxiosResponse<void>> {
		this.clearCache();
		return axios.patch(`${Constants.getApiUrl()}Tag`, { tag_id: tagId, name: newName });
	},

	merge(tagId: number, destinationId: number): Promise<AxiosResponse<void>> {
		this.clearCache();
		return axios.put(`${Constants.getApiUrl()}Tag`, { tag_id: tagId, destination_id: destinationId });
	},
};

export default TagsService;
