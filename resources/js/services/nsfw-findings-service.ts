/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

/** NSFW findings review (Feature 089). */
const NsfwFindingsService = {
	list(page: number, per_page: number = 30): Promise<AxiosResponse<App.Http.Resources.Collections.PaginatedNsfwFindingsResource>> {
		return axios.get(`${Constants.getApiUrl()}NsfwDetection/findings`, { params: { page, per_page }, data: {} });
	},

	dismiss(detection_ids: number[]): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}NsfwDetection/findings`, { data: { detection_ids } });
	},

	dismissPhotos(photo_ids: string[]): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}NsfwDetection/findings/photos`, { data: { photo_ids } });
	},
};

export default NsfwFindingsService;
