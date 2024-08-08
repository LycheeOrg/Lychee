import axios, { type AxiosResponse } from "axios";
import Constants, { PaginatedResponse } from "./constants";

const MaintenanceService = {
	updateGet(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.UpdateInfo>> {
		return axios.get(`${Constants.API_URL}Maintenance::update`, { data: {} });
	},
	updateCheck(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.UpdateCheckInfo>> {
		return axios.post(`${Constants.API_URL}Maintenance::update`, {});
	},
};

export default MaintenanceService;
