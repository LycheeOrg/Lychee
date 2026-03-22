import axios, { type AxiosResponse } from "axios";
import Constants, { type PaginatedResponse } from "./constants";

const PeopleService = {
	getPeople(page: number = 1): Promise<AxiosResponse<PaginatedResponse<App.Http.Resources.Models.PersonResource>>> {
		return axios.get(`${Constants.getApiUrl()}People`, { params: { page }, data: {} });
	},

	getPerson(id: string): Promise<AxiosResponse<App.Http.Resources.Models.PersonResource>> {
		return axios.get(`${Constants.getApiUrl()}People/${id}`, { data: {} });
	},

	getPhotos(
		id: string,
		page: number = 1,
	): Promise<AxiosResponse<PaginatedResponse<App.Http.Resources.Models.PhotoResource>>> {
		return axios.get(`${Constants.getApiUrl()}People/${id}/photos`, { params: { page }, data: {} });
	},

	create(name: string): Promise<AxiosResponse<App.Http.Resources.Models.PersonResource>> {
		return axios.post(`${Constants.getApiUrl()}People`, { name });
	},

	update(
		id: string,
		data: { name?: string; is_searchable?: boolean },
	): Promise<AxiosResponse<App.Http.Resources.Models.PersonResource>> {
		return axios.patch(`${Constants.getApiUrl()}People/${id}`, data);
	},

	destroy(id: string): Promise<AxiosResponse> {
		return axios.delete(`${Constants.getApiUrl()}People/${id}`);
	},

	claim(id: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}People/${id}/claim`);
	},

	unclaim(id: string): Promise<AxiosResponse> {
		return axios.delete(`${Constants.getApiUrl()}People/${id}/claim`);
	},

	merge(targetId: string, sourcePersonId: string): Promise<AxiosResponse> {
		return axios.post(`${Constants.getApiUrl()}People/${targetId}/merge`, { source_person_id: sourcePersonId });
	},

	claimBySelfie(file: File): Promise<AxiosResponse<App.Http.Resources.Models.PersonResource>> {
		const form = new FormData();
		form.append("selfie", file);
		return axios.post(`${Constants.getApiUrl()}People/claim-by-selfie`, form, {
			headers: { "Content-Type": "multipart/form-data" },
		});
	},
};

export default PeopleService;
