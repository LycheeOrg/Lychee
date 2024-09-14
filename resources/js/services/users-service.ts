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

const UsersService = {
	count(): Promise<AxiosResponse<number>> {
		return axios.get(`${Constants.API_URL}Users::count`, { data: {} });
	},

	get(): Promise<AxiosResponse<App.Http.Resources.Models.LightUserResource[]>> {
		return axios.get(`${Constants.API_URL}Users`, { data: {} });
	},
};

export default UsersService;
