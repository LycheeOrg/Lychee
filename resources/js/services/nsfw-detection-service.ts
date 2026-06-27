/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

const NsfwDetectionService = {
	bulkScan(albumId?: string): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}NsfwDetection/bulk-scan`, {
			album_id: albumId ?? null,
		});
	},
};

export default NsfwDetectionService;
