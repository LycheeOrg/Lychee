import axios, { type AxiosResponse } from "axios";
import { AxiosCacheInstance } from "axios-cache-interceptor";
import Constants from "./constants";

export type PhotoBucketResource = App.Http.Resources.V3.PhotoBucketResource;
export type PhotoRatioResource = App.Http.Resources.V3.PhotoRatioResource;
export type PhotoDetailResource = App.Http.Resources.V3.PhotoDetailResource;

export type PhotoDetailsScope = { bucketId: string } | { photoIds: string[] };

/**
 * `ratios`' own optional, mutually exclusive scoping (FR-066-06) — `bucketIds`
 * (uncapped, any number of buckets in one request) or `photoIds` (capped at
 * 300 server-side, mirrors `PhotoDetailsScope`'s own `{photoIds}` variant).
 * Omitting the scope entirely preserves today's whole-scope behaviour
 * byte-for-byte (NFR-066-03) — every existing album caller of `getRatios()`
 * keeps calling it with zero arguments.
 */
export type PhotoRatiosScope = { bucketIds: string[] } | { photoIds: string[] };

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

	/**
	 * `scope` mirrors `getDetails()`'s own mutually-exclusive scoping — see
	 * `PhotoRatiosScope`. Omitted (every existing album caller), the request/
	 * cache `id` is byte-identical to before this feature (NFR-066-03); a
	 * scope folds a digest into the `id` so two different bucket windows (or
	 * a bucket window vs. the whole scope) for the same album never collide
	 * in the axios-cache-interceptor client cache — mirrors `getDetails()`'s
	 * own `cacheDigest` construction exactly (Feature 066, FR-066-11).
	 */
	getRatios(album_id: string, scope?: PhotoRatiosScope): Promise<AxiosResponse<PhotoRatioResource>> {
		const requester = axios as unknown as AxiosCacheInstance;

		let param = "";
		let cacheDigest = "";
		if (scope !== undefined) {
			if ("bucketIds" in scope) {
				const sortedIds = [...scope.bucketIds].sort();
				param = `?${sortedIds.map((id) => `bucket_ids[]=${encodeURIComponent(id)}`).join("&")}`;
				cacheDigest = `_buckets_${sortedIds.join(",")}`;
			} else {
				const sortedIds = [...scope.photoIds].sort();
				param = `?${sortedIds.map((id) => `photo_ids[]=${encodeURIComponent(id)}`).join("&")}`;
				cacheDigest = `_ids_${sortedIds.join(",")}`;
			}
		}

		return requester.get(`${Constants.getApiUrlV3()}Albums/${album_id}/Photos${param}`, {
			data: {},
			id: `photo_v3_ratios_${album_id}${cacheDigest}`,
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
