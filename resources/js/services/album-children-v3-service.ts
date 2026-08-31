import axios, { type AxiosResponse } from "axios";
import { AxiosCacheInstance } from "axios-cache-interceptor";
import Constants from "./constants";

/**
 * Consumes Feature 061's three `GET /api/v3/Albums/{album_id}/children*`
 * endpoints (FR-062-01/03). Cached the same way `AlbumService`'s v2 calls
 * are — via `axios-cache-interceptor`'s enumerable `id`s (FR-062-19) —
 * rather than a bespoke store-level cache; `AlbumService.clearCache()`
 * removes these same entries alongside the v2 ones it already clears.
 */
const AlbumChildrenV3Service = {
	getBuckets(album_id: string): Promise<AxiosResponse<App.Http.Resources.V3.AlbumBucketResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/children/buckets`, {
			data: {},
			id: `album_v3_children_buckets_${album_id}`,
		});
	},

	getChildren(album_id: string): Promise<AxiosResponse<App.Http.Resources.V3.AlbumChildrenDataResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/children`, {
			data: {},
			id: `album_v3_children_${album_id}`,
		});
	},

	getRights(album_id: string): Promise<AxiosResponse<App.Http.Resources.V3.AlbumChildrenRightsResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/children/rights`, {
			data: {},
			id: `album_v3_children_rights_${album_id}`,
		});
	},
};

export default AlbumChildrenV3Service;
