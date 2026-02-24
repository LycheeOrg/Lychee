import axios, { AxiosProgressEvent, AxiosRequestConfig, type AxiosResponse } from "axios";
import Constants from "./constants";

export type UploadData = {
	album_id: string | null;
	file_last_modified_time: number | null;
	file: Blob;
	meta: App.Http.Resources.Editable.UploadMetaResource;
	apply_watermark: boolean;

	onUploadProgress: (e: AxiosProgressEvent) => void;
};

const UploadService = {
	getSetUp(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.UploadConfig>> {
		return axios.get(`${Constants.getApiUrl()}Gallery::getUploadLimits`, { data: {} });
	},

	upload(info: UploadData, abortController: AbortController): Promise<AxiosResponse<App.Http.Resources.Editable.UploadMetaResource>> {
		const formData = new FormData();

		formData.append("file", info.file, info.meta.file_name);
		formData.append("file_name", info.meta.file_name);
		formData.append("album_id", info.album_id ?? "");
		formData.append("file_last_modified_time", info.file_last_modified_time?.toString() ?? "");
		formData.append("uuid_name", info.meta.uuid_name ?? "");
		formData.append("extension", info.meta.extension ?? "");
		formData.append("chunk_number", info.meta.chunk_number?.toString() ?? "");
		formData.append("total_chunks", info.meta.total_chunks?.toString() ?? "");
		formData.append("apply_watermark", info.apply_watermark ? "1" : "0");

		const config: AxiosRequestConfig<FormData> = {
			onUploadProgress: info.onUploadProgress,
			headers: {
				"Content-Type": "application/json",
			},
			signal: abortController.signal,
			transformRequest: [(data) => data],
		};

		return axios.post(`${Constants.getApiUrl()}Photo`, formData, config);
	},
};

export default UploadService;
