/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type AdminUpdateStatusResource = App.Http.Resources.Models.AdminUpdateStatusResource;

const AdminStatsService = {
	getStats(force: boolean = false): Promise<AxiosResponse<App.Http.Resources.Models.AdminStatsResource>> {
		return axios.get(`${Constants.getApiUrl()}Admin/Stats`, {
			params: { force: force ? 1 : undefined },
			data: {},
		});
	},
	getUpdateStatus(): Promise<AxiosResponse<AdminUpdateStatusResource>> {
		return axios.get(`${Constants.getApiUrl()}Admin/UpdateStatus`, { data: {} });
	},
};

export default AdminStatsService;
