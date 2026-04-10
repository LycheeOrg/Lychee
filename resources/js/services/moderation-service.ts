/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const ModerationService = {
	list(page: number = 1, per_page: number = 30): Promise<AxiosResponse<App.Http.Resources.Collections.PaginatedModerationResource>> {
		return axios.get(`${Constants.getApiUrl()}Moderation`, { params: { page, per_page } });
	},

	approve(photo_ids: string[]): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}Moderation::approve`, { photo_ids });
	},
};

export default ModerationService;
