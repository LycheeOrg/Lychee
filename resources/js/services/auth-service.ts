import axios, { AxiosRequestConfig, type AxiosResponse } from "axios";
import Constants from "./constants";

// Several independent mount points (LeftMenu, gallery route panels, ...) each ask
// "who is logged in" on first page load. Coalescing concurrent in-flight requests
// into one avoids firing `Auth::user` more than once for the exact same answer.
let userRequest: Promise<AxiosResponse<App.Http.Resources.Models.UserResource>> | null = null;

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
		if (userRequest === null) {
			userRequest = axios.get(`${Constants.getApiUrl()}Auth::user`, { data: {} }).finally(() => {
				userRequest = null;
			});
		}

		return userRequest;
	},

	config(): Promise<AxiosResponse<App.Http.Resources.Root.AuthConfig>> {
		return axios.get(`${Constants.getApiUrl()}Auth::config`, { data: {} });
	},
};

export default AuthService;
