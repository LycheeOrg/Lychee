/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import axios, { type AxiosResponse } from "axios";
import Constants from "./constants";

/** Size variants accepted by `GET /api/v3/Faces/{face_id}/photo/{variant}` (ADR-093-01). */
export type FacePhotoVariant = "medium" | "small2x" | "small" | "thumb";

/**
 * Face quick naming (Feature 093): the v3 queue and people list, the face-scoped photo route,
 * and the v2 assign / dismiss calls with an explicit target value.
 */
const FaceQuickNamingService = {
	getQueue(clusterLabel: number | null): Promise<AxiosResponse<App.Http.Resources.V3.FaceQueueResource>> {
		const params = clusterLabel === null ? {} : { cluster_label: clusterLabel };
		return axios.get(`${Constants.getApiUrlV3()}Faces/queue`, { params, data: {} });
	},

	getPeople(): Promise<AxiosResponse<App.Http.Resources.V3.PeopleListResource>> {
		return axios.get(`${Constants.getApiUrlV3()}People`, { data: {} });
	},

	/** Resolves to an object URL; the caller revokes it. */
	getPhoto(faceId: string, variant: FacePhotoVariant, signal?: AbortSignal): Promise<string> {
		return axios
			.get(`${Constants.getApiUrlV3()}Faces/${faceId}/photo/${variant}`, { responseType: "blob", signal, data: {} })
			.then((response) => URL.createObjectURL(response.data as Blob));
	},

	assignPerson(faceId: string, personId: string): Promise<AxiosResponse<App.Http.Resources.Models.FaceResource>> {
		return axios.post(`${Constants.getApiUrl()}Face/${faceId}/assign`, { person_id: personId });
	},

	assignNewPerson(faceId: string, name: string): Promise<AxiosResponse<App.Http.Resources.Models.FaceResource>> {
		return axios.post(`${Constants.getApiUrl()}Face/${faceId}/assign`, { new_person_name: name });
	},

	unassign(faceId: string): Promise<AxiosResponse<App.Http.Resources.Models.FaceResource>> {
		return axios.post(`${Constants.getApiUrl()}Face/${faceId}/assign`, {});
	},

	/** Idempotent set (API-093-02), safe to retry. */
	setDismissed(faceId: string, isDismissed: boolean): Promise<AxiosResponse<App.Http.Resources.Models.FaceResource>> {
		return axios.patch(`${Constants.getApiUrl()}Face/${faceId}`, { is_dismissed: isDismissed });
	},
};

export default FaceQuickNamingService;
