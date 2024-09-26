import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const SearchService = {
	init(album_id: string | undefined): Promise<AxiosResponse<App.Http.Resources.Search.InitResource>> {
		return axios.get(`${Constants.API_URL}Search::init?album_id=${album_id === undefined ? "" : album_id}`, { data: {} });
	},

	search(album_id: string | undefined, terms: string, page: number = 1): Promise<AxiosResponse<App.Http.Resources.Search.ResultsResource>> {
		return axios.get(`${Constants.API_URL}Search?page=${page}&album_id=${album_id === undefined ? "" : album_id}&terms=${btoa(terms)}`, {
			data: {},
		});
	},
};

export default SearchService;
