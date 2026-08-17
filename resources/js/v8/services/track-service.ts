import axios, { AxiosRequestConfig, type AxiosResponse } from "axios";
import Constants from "@/services/constants";

/**
 * v8-only track management (FR-055-06/07/08). Forked from `album-service.ts`'s
 * legacy `uploadTrack`/`deleteTrack` rather than editing that shared module,
 * per NFR-055-01 / the v8-migration convention of forking shared modules.
 */
export default {
	uploadTracks(album_id: string, files: File[]): Promise<AxiosResponse> {
		const formData = new FormData();
		formData.append("album_id", album_id);
		files.forEach((file) => formData.append("files[]", file));

		const config: AxiosRequestConfig<FormData> = {
			headers: {
				"Content-Type": "application/json",
			},
			transformRequest: [(data) => data],
		};

		return axios.post(`${Constants.getApiUrl()}Album::tracks`, formData, config);
	},

	renameTrack(album_id: string, track_id: number, name: string): Promise<AxiosResponse> {
		return axios.patch(`${Constants.getApiUrl()}Album::tracks`, { album_id: album_id, track_id: track_id, name: name });
	},

	deleteTrack(album_id: string, track_id: number): Promise<AxiosResponse> {
		return axios.delete(`${Constants.getApiUrl()}Album::tracks`, { params: { album_id: album_id, track_id: track_id }, data: {} });
	},
};
