/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type NsfwLabelSet = {
	labels: string[];
	confidence: number | null;
	area_ratio: number | null;
	label_thresholds: Record<string, number>;
};

export type NsfwPresetConfig = {
	name: string;
	description: string;
	block: NsfwLabelSet;
	review: NsfwLabelSet;
	sensitive: NsfwLabelSet;
};

export type NsfwConfigResponse = {
	config: Record<string, string>;
	presets: Record<string, NsfwPresetConfig>;
};

const NsfwConfigService = {
	getConfig(): Promise<AxiosResponse<NsfwConfigResponse>> {
		return axios.get(`${Constants.getApiUrl()}NsfwDetection/config`, { data: {} });
	},
};

export default NsfwConfigService;
