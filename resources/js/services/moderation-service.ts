/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const ModerationService = {
	list(page: number = 1, per_page: number = 30): Promise<AxiosResponse<App.Http.Resources.Collections.PaginatedModerationResource>> {
		return axios.get(`${Constants.getApiUrl()}Moderation`, { params: { page, per_page }, data: {} });
	},

	approve(photo_ids: string[]): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}Moderation::approve`, { photo_ids });
	},

	/** Deletes the photos themselves, whatever albums they are in (Feature 119, FR-119-10). */
	delete(photo_ids: string[]): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Moderation`, { data: { photo_ids } });
	},

	history(page: number, per_page: number = 30): Promise<AxiosResponse<App.Http.Resources.Collections.PaginatedModerationHistoryResource>> {
		return axios.get(`${Constants.getApiUrl()}Moderation::history`, { params: { page, per_page }, data: {} });
	},

	photoHistory(photo_id: string): Promise<AxiosResponse<App.Http.Resources.Models.ModerationEventResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Moderation::photoHistory`, { params: { photo_id }, data: {} });
	},

	getPhoto(photo_id: string): Promise<AxiosResponse<App.Http.Resources.Models.PhotoResource>> {
		return axios.get(`${Constants.getApiUrl()}Moderation::photo`, { params: { photo_id }, data: {} });
	},
};

export default ModerationService;
