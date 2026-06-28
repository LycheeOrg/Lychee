/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const NsfwConfigService = {
	getConfig(): Promise<AxiosResponse<App.Http.Resources.GalleryConfigs.Nsfw.NsfwConfigResource>> {
		return axios.get(`${Constants.getApiUrl()}NsfwDetection/config`, { data: {} });
	},
};

export default NsfwConfigService;
