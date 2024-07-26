import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type SetLoginRequest = {
	old_password: string;
	username: string | null;
	password: string;
	password_confirmation: string;
};

export type SetEmailRequest = {
	email: string;
};

const ProfileService = {
	updateLogin(data: SetLoginRequest): Promise<AxiosResponse<App.Http.Resources.Models.UserResource>> {
		return axios.post(`${Constants.API_URL}Profile::updateLogin`, data);
	},
	setEmail(data: SetEmailRequest): Promise<AxiosResponse<number>> {
		return axios.post(`${Constants.API_URL}Profile::setEmail`, data);
	},
	resetToken(): Promise<AxiosResponse<App.Http.Resources.Models.Utils.UserToken>> {
		return axios.post(`${Constants.API_URL}Profile::resetToken`, {});
	},
	unsetToken(): Promise<AxiosResponse<any>> {
		return axios.post(`${Constants.API_URL}Profile::unsetToken`, {});
	},
};

export default ProfileService;
