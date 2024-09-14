import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

type UserManagementCreateRequest = {
	username: string;
	password: string | null | undefined;
	may_upload: boolean;
	may_edit_own_settings: boolean;
};

type HasId = {
	id: number;
};

const UserManagementService = {
	get(): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource[]>> {
		return axios.get(`${Constants.API_URL}UserManagement`, { data: {} });
	},

	create(data: UserManagementCreateRequest): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource>> {
		return axios.post(`${Constants.API_URL}UserManagement::create`, data);
	},

	edit(data: UserManagementCreateRequest & HasId): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource>> {
		return axios.post(`${Constants.API_URL}UserManagement::save`, data);
	},

	delete(data: HasId): Promise<AxiosResponse<App.Http.Resources.Models.UserManagementResource>> {
		return axios.post(`${Constants.API_URL}UserManagement::delete`, data);
	},
};

export default UserManagementService;
