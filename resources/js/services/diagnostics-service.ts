import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type UpdateProfileRequest = {
	old_password: string;
	username: string | null;
	password: string | null;
	password_confirmation: string | null;
	email: string | null;
};

const DiagnosticsService = {
	errors(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.ErrorLine[]>> {
		return axios.get(`${Constants.getApiUrl()}Diagnostics`, { data: {} });
	},

	info(): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.getApiUrl()}Diagnostics::info`, { data: {} });
	},

	space(): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.getApiUrl()}Diagnostics::space`, { data: {} });
	},

	config(): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.getApiUrl()}Diagnostics::config`, { data: {} });
	},

	permissions(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.Permissions>> {
		return axios.get(`${Constants.getApiUrl()}Diagnostics::permissions`, { data: {} });
	},
};

export default DiagnosticsService;
