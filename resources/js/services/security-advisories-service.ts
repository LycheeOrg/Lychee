/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const SecurityAdvisoriesService = {
	getAdvisories(): Promise<AxiosResponse<App.Http.Resources.Models.SecurityAdvisoryResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Security/Advisories`, { data: {} });
	},
};

export default SecurityAdvisoriesService;
