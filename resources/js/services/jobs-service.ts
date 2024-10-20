import axios, { type AxiosResponse } from "axios";
import Constants, { PaginatedResponse } from "./constants";

export type UpdateProfileRequest = {
	old_password: string;
	username: string | null;
	password: string | null;
	password_confirmation: string | null;
	email: string | null;
};

const JobService = {
	list(): Promise<AxiosResponse<PaginatedResponse<App.Http.Resources.Models.JobHistoryResource>>> {
		return axios.get(`${Constants.getApiUrl()}Jobs`, { data: {} });
	},
};

export default JobService;
