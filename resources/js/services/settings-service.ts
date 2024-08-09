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
};

export default SettingsService;
