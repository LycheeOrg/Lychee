import axios, { type AxiosResponse } from "axios";
import { AxiosCacheInstance } from "axios-cache-interceptor";
import Constants from "./constants";

/**
 * Consumes the three `GET /api/v3/Albums/{album_id}*`
 * endpoints. Cached the same way `AlbumService`'s v2 calls
 * are — via `axios-cache-interceptor`'s enumerable `id`s —
 * rather than a bespoke store-level cache; `AlbumService.clearCache()`
 * removes these same entries alongside the v2 ones it already clears.
 */
const AlbumChildrenV3Service = {
	getBuckets(album_id: string): Promise<AxiosResponse<App.Http.Resources.V3.AlbumBucketResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/buckets`, {
			data: {},
			id: `album_v3_children_buckets_${album_id}`,
		});
	},

	getChildren(album_id: string): Promise<AxiosResponse<App.Http.Resources.V3.AlbumDataResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}`, {
			data: {},
			id: `album_v3_children_${album_id}`,
		});
	},

	getRights(album_id: string): Promise<AxiosResponse<App.Http.Resources.V3.AlbumRightsResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/rights`, {
			data: {},
			id: `album_v3_children_rights_${album_id}`,
		});
	},
};

export default AlbumChildrenV3Service;
