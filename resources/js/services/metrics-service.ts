import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";
import { ALL } from "@/config/constants";

function isValidAlbumId(album_id: string | undefined): boolean {
	if (album_id === undefined || album_id === ALL) {
		return false;
	}
	// We check if the length of album_id is 24 characters, that excldes all the smarts albums
	return album_id.length === 24;
}

const MetricsService = {
	get(): Promise<AxiosResponse<App.Http.Resources.Models.LiveMetricsResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Metrics`, { data: {} });
	},

	photo(photo_id: string, album_id: string | undefined): Promise<AxiosResponse<null> | null> {
		if (!photo_id) {
			// TODO: figure out why this sometimes happens...
			// Do not send a request if no photo_id is provided, otherwise it breaks in front-end.
			return Promise.resolve(null);
		}
		if (!isValidAlbumId(album_id)) {
			return Promise.resolve(null);
		}

		return axios.post(`${Constants.getApiUrl()}Metrics::photo`, { photo_ids: [photo_id], from_id: album_id });
	},

	favourite(photo_id: string, album_id: string | undefined): Promise<AxiosResponse<null> | null> {
		if (!isValidAlbumId(album_id)) {
			return Promise.resolve(null);
		}

		return axios.post(`${Constants.getApiUrl()}Metrics::favourite`, { photo_ids: [photo_id], from_id: album_id });
	},
};

export default MetricsService;
