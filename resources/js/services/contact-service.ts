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

export type ContactMessageResource = {
	id: number;
	name: string;
	email: string;
	message: string;
	is_read: boolean;
	created_at: string;
};

export type ContactMessagesListResponse = {
	data: ContactMessageResource[];
	pagination: {
		total: number;
		per_page: number;
		current_page: number;
	};
};

const ContactService = {
	submit(data: ContactMessageSubmitRequest): Promise<AxiosResponse<ContactMessageSubmitResponse>> {
		return axios.post(`${Constants.getApiUrl()}contact`, data);
	},

	list(params: { page?: number; per_page?: number; is_read?: boolean | null; search?: string } = {}): Promise<AxiosResponse<ContactMessagesListResponse>> {
		return axios.get(`${Constants.getApiUrl()}contact`, { params });
	},

	markRead(id: number, is_read: boolean): Promise<AxiosResponse<ContactMessageResource>> {
		return axios.patch(`${Constants.getApiUrl()}contact`, { id, is_read });
	},

	deleteMessage(id: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}contact`, { data: { id } });
	},
};

export default ContactService;
