import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";
import { AxiosCacheInstance } from "axios-cache-interceptor";

const SearchService = {
	init(album_id: string | undefined): Promise<AxiosResponse<App.Http.Resources.Search.InitResource>> {
		// We hard tag the request so that it is not repeated per album.
		// We do not need to query this for each different album as the result will be the same.
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.API_URL}Search::init?album_id=${album_id === undefined ? "" : album_id}`, { data: {}, id: "search_init" });
	},

	search(album_id: string | undefined, terms: string, page: number = 1): Promise<AxiosResponse<App.Http.Resources.Search.ResultsResource>> {
		return axios.get(`${Constants.API_URL}Search?page=${page}&album_id=${album_id === undefined ? "" : album_id}&terms=${btoa(terms)}`, {
			data: {},
		});
	},
};

export default SearchService;
