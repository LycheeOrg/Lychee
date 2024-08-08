// Route::get('/Diagnostics', [Admin\DiagnosticsController::class, 'errors']);
// Route::get('/Diagnostics::info', [Admin\DiagnosticsController::class, 'info']);
// Route::get('/Diagnostics::space', [Admin\DiagnosticsController::class, 'space']);
// Route::get('/Diagnostics::config', [Admin\DiagnosticsController::class, 'config']);

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
		return axios.get(`${Constants.API_URL}Diagnostics`, { data: {} });
	},

	info(): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.API_URL}Diagnostics::info`, { data: {} });
	},

	space(): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.API_URL}Diagnostics::space`, { data: {} });
	},

	config(): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.API_URL}Diagnostics::config`, { data: {} });
	},

	permissions(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.Permissions>> {
		return axios.get(`${Constants.API_URL}Diagnostics::permissions`, { data: {} });
	},
};

export default DiagnosticsService;
