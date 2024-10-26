import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

type UserManagementCreateRequest = {
	username: string;
	password: string | null | undefined;
	may_upload: boolean;
	may_edit_own_settings: boolean;
	has_quota?: boolean;
	quota_kb?: number;
	note?: string;
};

type HasId = {
	id: number;
};

const UserManagementService = {
	get(): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource[]>> {
		return axios.get(`${Constants.getApiUrl()}UserManagement`, { data: {} });
	},

	create(data: UserManagementCreateRequest): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource>> {
		return axios.post(`${Constants.getApiUrl()}UserManagement::create`, data);
	},

	edit(data: UserManagementCreateRequest & HasId): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource>> {
		return axios.post(`${Constants.getApiUrl()}UserManagement::save`, data);
	},

	delete(data: HasId): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource>> {
		return axios.post(`${Constants.getApiUrl()}UserManagement::delete`, data);
	},
};

export default UserManagementService;
