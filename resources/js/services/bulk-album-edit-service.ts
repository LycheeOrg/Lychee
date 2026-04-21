/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type IndexParams = {
	search?: string | null;
	page?: number;
	per_page?: number;
};

export type PatchPayload = {
	album_ids: string[];
	description?: string | null;
	copyright?: string | null;
	license?: string | null;
	photo_layout?: string | null;
	photo_sorting_col?: string | null;
	photo_sorting_order?: string | null;
	album_sorting_col?: string | null;
	album_sorting_order?: string | null;
	album_thumb_aspect_ratio?: string | null;
	album_timeline?: string | null;
	photo_timeline?: string | null;
	is_nsfw?: boolean;
	is_public?: boolean;
	is_link_required?: boolean;
	grants_full_photo_access?: boolean;
	grants_download?: boolean;
	grants_upload?: boolean;
};

export type SetOwnerPayload = {
	album_ids: string[];
	owner_id: number;
};

export type BulkAlbumResource = {
	id: string;
	title: string;
	owner_id: number;
	owner_name: string;
	description: string | null;
	copyright: string | null;
	license: string;
	photo_layout: string | null;
	photo_sorting_col: string | null;
	photo_sorting_order: string | null;
	album_sorting_col: string | null;
	album_sorting_order: string | null;
	album_thumb_aspect_ratio: string | null;
	album_timeline: string | null;
	photo_timeline: string | null;
	is_nsfw: boolean;
	_lft: number;
	_rgt: number;
	is_public: boolean;
	is_link_required: boolean;
	grants_full_photo_access: boolean;
	grants_download: boolean;
	grants_upload: boolean;
	created_at: string;
};

export type PaginatedBulkAlbumResource = {
	data: BulkAlbumResource[];
	current_page: number;
	last_page: number;
	per_page: number;
	total: number;
};

export type BulkAlbumIdsResource = {
	ids: string[];
	capped: boolean;
};

const BulkAlbumEditService = {
	getAlbums(params: IndexParams): Promise<AxiosResponse<PaginatedBulkAlbumResource>> {
		return axios.get(`${Constants.getApiUrl()}BulkAlbumEdit`, { params, data: {} });
	},

	getIds(search?: string | null): Promise<AxiosResponse<BulkAlbumIdsResource>> {
		const params = search ? { search } : {};
		return axios.get(`${Constants.getApiUrl()}BulkAlbumEdit::ids`, { params, data: {} });
	},

	patchAlbums(payload: PatchPayload): Promise<AxiosResponse<void>> {
		return axios.patch(`${Constants.getApiUrl()}BulkAlbumEdit`, payload);
	},

	setOwner(payload: SetOwnerPayload): Promise<AxiosResponse<void>> {
		return axios.post(`${Constants.getApiUrl()}BulkAlbumEdit::setOwner`, payload);
	},

	deleteAlbums(ids: string[]): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}BulkAlbumEdit`, { data: { album_ids: ids } });
	},
};

export default BulkAlbumEditService;
