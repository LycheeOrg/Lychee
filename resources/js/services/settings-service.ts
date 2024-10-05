import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type SetConfigRequest = {
	configs: App.Http.Resources.Editable.EditableConfigResource[];
};

const SettingsService = {
	getAll(): Promise<AxiosResponse<App.Http.Resources.Collections.ConfigCollectionResource>> {
		return axios.get(`${Constants.API_URL}Settings`, { data: {} });
	},
	setConfigs(data: SetConfigRequest): Promise<AxiosResponse<App.Http.Resources.Collections.ConfigCollectionResource>> {
		return axios.post(`${Constants.API_URL}Settings::setConfigs`, data);
	},
	getLanguages(): Promise<AxiosResponse<string[]>> {
		return axios.get(`${Constants.API_URL}Settings::getLanguages`, { data: {} });
	},
	setJs(jsData: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Settings::setJS`, { js: jsData });
	},
	setCss(cssData: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Settings::setCSS`, { css: cssData });
	},
	getJs(): Promise<AxiosResponse> {
		return axios.get(`${window.assets_url}/dist/custom.js`);
	},
	getCss(): Promise<AxiosResponse> {
		return axios.get(`${window.assets_url}/dist/user.css`);
	}
};

export default SettingsService;
