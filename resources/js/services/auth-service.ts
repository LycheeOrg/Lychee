import axios, { AxiosRequestConfig, type AxiosResponse } from "axios";
import Constants from "./constants";

const AuthService = {
	login(username: string, password: string): Promise<AxiosResponse<any>> {
		return axios.post(
			`${Constants.API_URL}Auth::login`,
			{
				username: username,
				password: password,
			},
			{
				cache: {
					update: {
						albums: "delete",
					},
				},
			} as AxiosRequestConfig,
		);
	},

	logout(): Promise<AxiosResponse<any>> {
		return axios.post(`${Constants.API_URL}Auth::logout`, {});
	},

	user(): Promise<AxiosResponse<App.Http.Resources.Models.UserResource>> {
		return axios.get(`${Constants.API_URL}Auth::user`, { data: {} });
	},

	config(): Promise<AxiosResponse<App.Http.Resources.Root.AuthConfig>> {
		return axios.get(`${Constants.API_URL}Auth::config`, { data: {} });
	},
};

export default AuthService;
