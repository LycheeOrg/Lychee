/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type CreateLandingFeaturedItemRequest = {
	item_type: App.Enum.LandingFeaturedItemType;
	item_id: string;
	sort_order?: number;
	enabled?: boolean;
};

export type PatchLandingFeaturedItemRequest = Partial<CreateLandingFeaturedItemRequest> & {
	landing_featured_item_id: string;
};

const LandingFeaturedItemService = {
	list(): Promise<AxiosResponse<App.Http.Resources.Collections.LandingFeaturedItemCollection>> {
		return axios.get(`${Constants.getApiUrl()}LandingFeaturedItem`, { data: {} });
	},

	create(data: CreateLandingFeaturedItemRequest): Promise<AxiosResponse<App.Http.Resources.Models.LandingFeaturedItemResource>> {
		return axios.post(`${Constants.getApiUrl()}LandingFeaturedItem`, data);
	},

	patch(id: string, data: PatchLandingFeaturedItemRequest): Promise<AxiosResponse<App.Http.Resources.Models.LandingFeaturedItemResource>> {
		return axios.patch(`${Constants.getApiUrl()}LandingFeaturedItem/${id}`, data);
	},

	delete(id: string): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}LandingFeaturedItem/${id}`, { data: {} });
	},

	reorder(ids: string[]): Promise<AxiosResponse<App.Http.Resources.Collections.LandingFeaturedItemCollection>> {
		return axios.patch(`${Constants.getApiUrl()}LandingFeaturedItem/Reorder`, { ids });
	},
};

export default LandingFeaturedItemService;
