import axios, { type AxiosResponse } from "axios";
import { AxiosCacheInstance } from "axios-cache-interceptor";
import Constants from "./constants";

export type PhotoBucketResource = App.Http.Resources.V3.PhotoBucketResource;
export type PhotoRatioResource = App.Http.Resources.V3.PhotoRatioResource;
export type PhotoDetailResource = App.Http.Resources.V3.PhotoDetailResource;

export type PhotoDetailsScope = { bucketId: string } | { photoIds: string[] };

/**
 * Consumes Feature 064's three `GET /api/v3/Albums/{album_id}/Photos*`
 * endpoints. Cached the same way `AlbumChildrenV3Service`'s calls are — via
 * `axios-cache-interceptor`'s enumerable `id`s — rather than a bespoke
 * store-level cache.
 */
const PhotoChildrenV3Service = {
	getBuckets(album_id: string): Promise<AxiosResponse<PhotoBucketResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/Photos/buckets`, {
			data: {},
			id: `photo_v3_buckets_${album_id}`,
		});
	},

	getRatios(album_id: string): Promise<AxiosResponse<PhotoRatioResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/Photos`, {
			data: {},
			id: `photo_v3_ratios_${album_id}`,
		});
	},

	/**
	 * `scope` is exactly-one-of `{bucketId}` (uncapped, no truncation) or
	 * `{photoIds}` (capped at 300 as input by the backend, 422 above) —
	 * mirrors `GetPhotoDetailsRequest`'s own mutually-exclusive validation.
	 * This feature only ever calls it in `{photoIds}` mode (Q-065-03), a
	 * handful of ids at a time.
	 */
	getDetails(album_id: string, scope: PhotoDetailsScope): Promise<AxiosResponse<PhotoDetailResource>> {
		const requester = axios as unknown as AxiosCacheInstance;

		let param = "";
		let cacheDigest = "";
		if ("bucketId" in scope) {
			param = `?bucket_id=${encodeURIComponent(scope.bucketId)}`;
			cacheDigest = `bucket_${scope.bucketId}`;
		} else {
			const sortedIds = [...scope.photoIds].sort();
			param = `?${sortedIds.map((id) => `photo_ids[]=${encodeURIComponent(id)}`).join("&")}`;
			cacheDigest = `ids_${sortedIds.join(",")}`;
		}

		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/Photos/details${param}`, {
			data: {},
			id: `photo_v3_details_${album_id}_${cacheDigest}`,
		});
	},
};

export default PhotoChildrenV3Service;
