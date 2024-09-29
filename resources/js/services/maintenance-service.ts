import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const MaintenanceService = {
	updateGet(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.UpdateInfo>> {
		return axios.get(`${Constants.API_URL}Maintenance::update`, { data: {} });
	},
	updateCheck(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.UpdateCheckInfo>> {
		return axios.post(`${Constants.API_URL}Maintenance::update`, {});
	},

	cleaningGet(path: string): Promise<AxiosResponse<App.Http.Resources.Diagnostics.CleaningState>> {
		return axios.get(`${Constants.API_URL}Maintenance::cleaning`, { params: { path: path }, data: {} });
	},
	cleaningDo(path: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Maintenance::cleaning`, { path: path });
	},

	jobsGet(): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.API_URL}Maintenance::jobs`, { data: {} });
	},
	jobsDo(): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Maintenance::jobs`, {});
	},

	treeGet(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.TreeState>> {
		return axios.get(`${Constants.API_URL}Maintenance::tree`, { data: {} });
	},
	treeDo(): Promise<AxiosResponse<number>> {
		return axios.post(`${Constants.API_URL}Maintenance::tree`, {});
	},

	genSizeVariantsCheck(sv: App.Enum.SizeVariantType): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.API_URL}Maintenance::genSizeVariants`, { data: {}, params: { variant: sv } });
	},
	genSizeVariantsDo(sv: App.Enum.SizeVariantType): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Maintenance::genSizeVariants`, { variant: sv });
	},

	missingFileSizesCheck(): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.API_URL}Maintenance::missingFileSize`, { data: {} });
	},
	missingFileSizesDo(): Promise<AxiosResponse> {
		return axios.post(`${Constants.API_URL}Maintenance::missingFileSize`, {});
	},

	optimizeDo(): Promise<AxiosResponse<string[]>> {
		return axios.post(`${Constants.API_URL}Maintenance::optimize`, {});
	},
};

export default MaintenanceService;
