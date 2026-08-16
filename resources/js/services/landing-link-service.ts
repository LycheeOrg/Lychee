/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type CreateLandingLinkRequest = {
	label: string;
	url: string;
	placement: App.Enum.LandingLinkPlacement;
	open_in_new_tab: boolean;
	sort_order: number;
	enabled: boolean;
};

export type UpdateLandingLinkRequest = CreateLandingLinkRequest & {
	landing_link_id: string;
};

export type PatchLandingLinkRequest = Partial<CreateLandingLinkRequest> & {
	landing_link_id: string;
};

const LandingLinkService = {
	list(): Promise<AxiosResponse<App.Http.Resources.Collections.LandingLinkCollection>> {
		return axios.get(`${Constants.getApiUrl()}LandingLink`, { data: {} });
	},

	create(data: CreateLandingLinkRequest): Promise<AxiosResponse<App.Http.Resources.Models.LandingLinkResource>> {
		return axios.post(`${Constants.getApiUrl()}LandingLink`, data);
	},

	update(id: string, data: UpdateLandingLinkRequest): Promise<AxiosResponse<App.Http.Resources.Models.LandingLinkResource>> {
		return axios.put(`${Constants.getApiUrl()}LandingLink/${id}`, data);
	},

	patch(id: string, data: PatchLandingLinkRequest): Promise<AxiosResponse<App.Http.Resources.Models.LandingLinkResource>> {
		return axios.patch(`${Constants.getApiUrl()}LandingLink/${id}`, data);
	},

	delete(id: string): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}LandingLink/${id}`, { data: {} });
	},

	reorder(ids: string[]): Promise<AxiosResponse<App.Http.Resources.Collections.LandingLinkCollection>> {
		return axios.patch(`${Constants.getApiUrl()}LandingLink/Reorder`, { ids });
	},
};

export default LandingLinkService;
