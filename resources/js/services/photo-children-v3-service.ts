import axios, { type AxiosResponse } from "axios";
import { AxiosCacheInstance } from "axios-cache-interceptor";
import Constants from "./constants";

/**
 * Response body of `GET /api/v3/Albums/{album_id}/Photos/buckets`.
 * Mirrors `App\Http\Resources\V3\PhotoBucketResource` — not yet generated
 * into `lychee.d.ts` (Feature 064 shipped without a `php artisan
 * typescript:transform` run in a dev environment), so hand-transcribed here
 * from the PHP source directly. Replace with the generated
 * `App.Http.Resources.V3.PhotoBucketResource` type once that command has
 * been run.
 */
export type PhotoBucketResource = {
	bucket_ids: string[];
	counts: number[];
	labels: string[];
	bucketable: boolean;
};

/**
 * Response body of `GET /api/v3/Albums/{album_id}/Photos` — mirrors
 * `App\Http\Resources\V3\PhotoRatioResource`. Conditionally-present fields
 * are simply absent from the parsed JSON object when the server omitted
 * them (`Optional::create()`), so they're typed optional here rather than
 * nullable.
 */
export type PhotoRatioResource = {
	ids: string[];
	titles: string[];
	types: string[];
	bucket_ids: string[];
	ratios: number[];
	owner_ids: number[];
	is_highlighteds: boolean[];
	is_validateds: boolean[];
	is_videos: boolean[];
	is_raws: boolean[];
	is_live_photos: boolean[];
	taken_ats: (string | null)[];
	created_ats: string[];
	taken_at_orig_tzs: (string | null)[];
	rating_avgs?: number[];
	rating_users?: (number | null)[];
	thumb_infos?: (string | null)[];
	tags?: string[][];
};

/**
 * Response body of `GET /api/v3/Albums/{album_id}/Photos/details` — mirrors
 * `App\Http\Resources\V3\PhotoDetailResource`.
 */
export type PhotoDetailResource = {
	ids: string[];
	descriptions: (string | null)[];
	tags: string[][];
	rating_avgs: (number | null)[];
	licenses: string[];
	owner_ids: number[];
	nsfw_statuses: (string | null)[];
	checksums: string[];
	original_checksums: string[];
	updated_ats: string[];
	live_photo_checksums: (string | null)[];
	live_photo_content_ids: (string | null)[];
	live_photo_urls: (string | null)[];
	face_counts: number[];
	palette: (App.Http.Resources.Models.ColourPaletteResource | null)[];
	size_variants: (App.Http.Resources.Models.SizeVariantsResouce | null)[];
	statistics: (App.Http.Resources.Models.PhotoStatisticsResource | null)[];
	makes?: (string | null)[];
	models?: (string | null)[];
	lenses?: (string | null)[];
	apertures?: (string | null)[];
	shutters?: (string | null)[];
	focals?: (string | null)[];
	isos?: (string | null)[];
	latitudes?: (number | null)[];
	longitudes?: (number | null)[];
	altitudes?: (number | null)[];
	locations?: (string | null)[];
};

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
