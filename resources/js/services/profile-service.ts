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
	unsetToken(): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}Profile::unsetToken`, {});
	},
	register(
		data: {
			username: string;
			email: string;
			password: string;
			password_confirmation: string;
		},
		signature: string,
		expires: string,
	): Promise<AxiosResponse<{ success: boolean; message: string }>> {
		return axios.put(`${Constants.getApiUrl()}Profile?expires=${expires}&signature=${signature}`, data);
	},
};

export default ProfileService;
