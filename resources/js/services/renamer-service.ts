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
	is_photo_rule: boolean;
	is_album_rule: boolean;
};

export type UpdateRenamerRuleRequest = CreateRenamerRuleRequest & {
	rule_id: number;
};

export type TestRenamerRequest = {
	candidate: string;
	is_photo: boolean;
	is_album: boolean;
};

export type TestRenamerResponse = {
	original: string;
	result: string;
};

export type PreviewRenameRequest = {
	album_id?: string;
	target: "photos" | "albums";
	scope: "current" | "descendants";
	rule_ids: number[];
	photo_ids?: string[];
	album_ids?: string[];
};

export type PreviewRenameItem = {
	id: string;
	original: string;
	new: string;
};

export type RenameApplyRequest = {
	photo_ids?: string[];
	album_ids?: string[];
	rule_ids?: number[];
};

const RenamerService = {
	list(all: boolean = false): Promise<AxiosResponse<App.Http.Resources.Models.RenamerRuleResource[]>> {
		return axios.get(`${Constants.getApiUrl()}Renamer`, { params: {}, data: { all: all } });
	},

	create(data: CreateRenamerRuleRequest): Promise<AxiosResponse<App.Http.Resources.Models.RenamerRuleResource>> {
		return axios.post(`${Constants.getApiUrl()}Renamer`, data);
	},

	update(data: UpdateRenamerRuleRequest): Promise<AxiosResponse<App.Http.Resources.Models.RenamerRuleResource>> {
		return axios.put(`${Constants.getApiUrl()}Renamer`, data);
	},

	delete(rule_id: number): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Renamer`, { data: { rule_id } });
	},

	test(data: TestRenamerRequest): Promise<AxiosResponse<TestRenamerResponse>> {
		return axios.post(`${Constants.getApiUrl()}Renamer::test`, data);
	},

	preview(data: PreviewRenameRequest): Promise<AxiosResponse<PreviewRenameItem[]>> {
		return axios.post(`${Constants.getApiUrl()}Renamer::preview`, data);
	},

	rename(data: RenameApplyRequest): Promise<AxiosResponse<void>> {
		return axios.patch(`${Constants.getApiUrl()}Renamer`, data);
	},
};

export default RenamerService;
