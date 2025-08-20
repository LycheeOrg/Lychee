/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type CreateRenamerRuleRequest = {
	rule: string;
	description: string;
	needle: string;
	replacement: string;
	mode: App.Enum.RenamerModeType;
	order: number;
	is_enabled: boolean;
};

export type UpdateRenamerRuleRequest = CreateRenamerRuleRequest & {
	renamer_rule_id: number;
};

const RenamerService = {
	list(all: boolean = false): Promise<AxiosResponse<App.Http.Resources.Models.RenamerRuleResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Renamer`, { params: { }, data: { all: all } });
	},

	create(data: CreateRenamerRuleRequest): Promise<AxiosResponse<App.Http.Resources.Models.RenamerRuleResource>> {
		return axios.post(`${Constants.getApiUrl()}Renamer`, data);
	},

	update(data: UpdateRenamerRuleRequest): Promise<AxiosResponse<App.Http.Resources.Models.RenamerRuleResource>> {
		return axios.put(`${Constants.getApiUrl()}Renamer`, data);
	},

	delete(renamer_rule_id: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Renamer`, { data: { renamer_rule_id: renamer_rule_id } });
	},
};

export default RenamerService;
