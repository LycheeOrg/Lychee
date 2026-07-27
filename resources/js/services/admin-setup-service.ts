import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type AdminSetupRequest = {
	username: string;
	password: string;
	password_confirmation: string;
};

const AdminSetupService = {
	create(data: AdminSetupRequest): Promise<AxiosResponse<{ message: string }>> {
		return axios.post(`${Constants.getApiUrl()}Admin::Setup`, data);
	},
};

export default AdminSetupService;
