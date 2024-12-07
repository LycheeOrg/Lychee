import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const UsersService = {
	count(): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.getApiUrl()}Users::count`, { data: {} });
	},

	get(): Promise<AxiosResponse<App.Http.Resources.Models.LightUserResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Users`, { data: {} });
	},
};

export default UsersService;
