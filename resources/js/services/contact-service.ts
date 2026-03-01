import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type ContactMessageSubmitRequest = {
	name: string;
	email: string;
	message: string;
	security_answer?: string;
	consent_agreed?: boolean;
};

export type ContactMessageSubmitResponse = {
	success: boolean;
	message: string;
};

const ContactService = {
	init(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.ContactConfig>> {
		return axios.get(`${Constants.getApiUrl()}Contact::Init`, { data: {} });
	},

	submit(data: ContactMessageSubmitRequest): Promise<AxiosResponse<ContactMessageSubmitResponse>> {
		return axios.post(`${Constants.getApiUrl()}Contact`, data);
	},

	list(
		params: { page?: number; per_page?: number; is_read?: boolean | null; search?: string } = {},
	): Promise<AxiosResponse<App.Http.Resources.Collections.ContactMessageCollectionResource>> {
		return axios.get(`${Constants.getApiUrl()}Contact`, { params, data: {} });
	},

	markRead(id: number, is_read: boolean): Promise<AxiosResponse<App.Http.Resources.Models.ContactMessageResource>> {
		return axios.patch(`${Constants.getApiUrl()}Contact`, { id, is_read });
	},

	deleteMessage(id: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Contact`, { data: { id } });
	},
};

export default ContactService;
