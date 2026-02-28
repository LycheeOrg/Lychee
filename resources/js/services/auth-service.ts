import axios, { AxiosRequestConfig, type AxiosResponse } from "axios";
import Constants from "./constants";

const AuthService = {
	login(username: string, password: string, rememberMe: boolean = false): Promise<AxiosResponse<void>> {
		return axios.post(
			`${Constants.getApiUrl()}Auth::login`,
			{
				username: username,
				password: password,
				remember_me: rememberMe,
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

	logout(): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}Auth::logout`, {});
	},

	user(): Promise<AxiosResponse<App.Http.Resources.Models.UserResource>> {
		return axios.get(`${Constants.getApiUrl()}Auth::user`, { data: {} });
	},

	config(): Promise<AxiosResponse<App.Http.Resources.Root.AuthConfig>> {
		return axios.get(`${Constants.getApiUrl()}Auth::config`, { data: {} });
	},
};

export default AuthService;
