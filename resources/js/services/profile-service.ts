import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type UpdateProfileRequest = {
	old_password: string;
	username: string | null;
	password: string | null;
	password_confirmation: string | null;
	email: string | null;
};

const ProfileService = {
	update(data: UpdateProfileRequest): Promise<AxiosResponse<App.Http.Resources.Models.UserResource>> {
		return axios.post(`${Constants.getApiUrl()}Profile::update`, data);
	},
	resetToken(): Promise<AxiosResponse<App.Http.Resources.Models.Utils.UserToken>> {
		return axios.post(`${Constants.getApiUrl()}Profile::resetToken`, {});
	},
	unsetToken(): Promise<AxiosResponse<any>> {
		return axios.post(`${Constants.getApiUrl()}Profile::unsetToken`, {});
	},
};

export default ProfileService;
