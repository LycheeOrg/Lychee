import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type UpdateTreeData = {
	id: string;
	_lft: number;
	_rgt: number;
};

const MaintenanceService = {
	updateGet(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.UpdateInfo>> {
		return axios.get(`${Constants.getApiUrl()}Maintenance::update`, { data: {} });
	},
	updateCheck(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.UpdateCheckInfo>> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::update`, {});
	},

	cleaningGet(path: string): Promise<AxiosResponse<App.Http.Resources.Diagnostics.CleaningState>> {
		return axios.get(`${Constants.getApiUrl()}Maintenance::cleaning`, { params: { path: path }, data: {} });
	},
	cleaningDo(path: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::cleaning`, { path: path });
	},

	jobsGet(): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.getApiUrl()}Maintenance::jobs`, { data: {} });
	},
	jobsDo(): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::jobs`, {});
	},

	treeGet(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.TreeState>> {
		return axios.get(`${Constants.getApiUrl()}Maintenance::tree`, { data: {} });
	},
	treeDo(): Promise<AxiosResponse<number>> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::tree`, {});
	},

	genSizeVariantsCheck(sv: App.Enum.SizeVariantType): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.getApiUrl()}Maintenance::genSizeVariants`, { data: {}, params: { variant: sv } });
	},
	genSizeVariantsDo(sv: App.Enum.SizeVariantType): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::genSizeVariants`, { variant: sv });
	},

	missingFileSizesCheck(): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.getApiUrl()}Maintenance::missingFileSize`, { data: {} });
	},
	missingFileSizesDo(): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::missingFileSize`, {});
	},

	optimizeDo(): Promise<AxiosResponse<string[]>> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::optimize`, {});
	},

	register(key: string): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.RegisterData>> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::register`, { key: key });
	},

	fullTreeGet(): Promise<AxiosResponse<App.Http.Resources.Diagnostics.AlbumTree[]>> {
		return axios.get(`${Constants.getApiUrl()}Maintenance::fullTree`, { data: {} });
	},

	updateFullTree(albums: UpdateTreeData[]): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}Maintenance::fullTree`, { albums: albums });
	},
};

export default MaintenanceService;
