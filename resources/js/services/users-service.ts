import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";
import { AxiosCacheInstance } from "axios-cache-interceptor";

const UsersService = {
	clearCount(): void {
		const axiosWithCache = axios as unknown as AxiosCacheInstance;
		axiosWithCache.storage.remove("users::count");
	},

	count(): Promise<AxiosResponse<number>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrl()}Users::count`, { data: {}, id: "users::count" });
	},

	get(): Promise<AxiosResponse<App.Http.Resources.Models.LightUserResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Users`, { data: {} });
	},
};

export default UsersService;
