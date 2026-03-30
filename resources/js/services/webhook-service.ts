/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

export type CreateWebhookRequest = {
	name: string;
	event: App.Enum.PhotoWebhookEventType;
	method: App.Enum.WebhookMethodType;
	url: string;
	payload_format: App.Enum.WebhookPayloadFormatType;
	secret?: string | null;
	secret_header?: string | null;
	enabled: boolean;
	send_photo_id: boolean;
	send_album_id: boolean;
	send_title: boolean;
	send_size_variants: boolean;
	size_variant_types?: number[] | null;
};

export type UpdateWebhookRequest = CreateWebhookRequest & {
	webhook_id: string;
};

export type PatchWebhookRequest = Partial<CreateWebhookRequest> & {
	webhook_id: string;
};

export type PaginatedWebhooks = {
	data: App.Http.Resources.Models.WebhookResource[];
	current_page: number;
	per_page: number;
	total: number;
	last_page: number;
};

const WebhookService = {
	list(): Promise<AxiosResponse<PaginatedWebhooks>> {
		return axios.get(`${Constants.getApiUrl()}Webhook`, { data: {} });
	},

	get(id: string): Promise<AxiosResponse<App.Http.Resources.Models.WebhookResource>> {
		return axios.get(`${Constants.getApiUrl()}Webhook/${id}`, { data: {} });
	},

	create(data: CreateWebhookRequest): Promise<AxiosResponse<App.Http.Resources.Models.WebhookResource>> {
		return axios.post(`${Constants.getApiUrl()}Webhook`, data);
	},

	update(id: string, data: UpdateWebhookRequest): Promise<AxiosResponse<App.Http.Resources.Models.WebhookResource>> {
		return axios.put(`${Constants.getApiUrl()}Webhook/${id}`, data);
	},

	patch(id: string, data: PatchWebhookRequest): Promise<AxiosResponse<App.Http.Resources.Models.WebhookResource>> {
		return axios.patch(`${Constants.getApiUrl()}Webhook/${id}`, data);
	},

	delete(id: string): Promise<AxiosResponse<void>> {
		return axios.delete(`${Constants.getApiUrl()}Webhook/${id}`);
	},
};

export default WebhookService;
