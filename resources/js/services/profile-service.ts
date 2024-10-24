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
		return axios.post(`${Constants.API_URL}Profile::update`, data);
	},
	resetToken(): Promise<AxiosResponse<App.Http.Resources.Models.Utils.UserToken>> {
		return axios.post(`${Constants.API_URL}Profile::resetToken`, {});
	},
	unsetToken(): Promise<AxiosResponse<any>> {
		return axios.post(`${Constants.API_URL}Profile::unsetToken`, {});
	},
};

export default ProfileService;
