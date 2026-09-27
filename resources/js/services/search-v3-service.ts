import axios, { type AxiosResponse } from "axios";
import { AxiosCacheInstance } from "axios-cache-interceptor";
import Constants from "./constants";

export type SearchPhotoResource = App.Http.Resources.V3.SearchPhotoResource;
export type SearchAlbumResource = App.Http.Resources.V3.AlbumDataResource;
export type SearchAlbumRightsResource = App.Http.Resources.V3.AlbumRightsResource;
export type PhotoDetailResource = App.Http.Resources.V3.PhotoDetailResource;

export type SearchSortingType = App.Enum.SearchSortingType;
export type OrderSortingType = App.Enum.OrderSortingType;

/**
 * Everything that identifies one search. Mirrors the four v3 endpoints' shared
 * query parameters exactly (Feature 069, FR-069-01).
 */
export type SearchV3Query = {
	terms: string;
	albumId?: string;
	sortingColumn?: SearchSortingType;
	sortingOrder?: OrderSortingType;
};

/**
 * UTF-8-safe base64, matching `search-service.ts`'s own helper.
 *
 * The v3 endpoints keep v2's base64 encoding of `terms` deliberately
 * (Q-069-06): the token grammar embeds `:`, `>=`, `"`, `#` and `*`, which are
 * either reserved or routinely mangled in a raw query string.
 */
function base64encode(str: string): string {
	const bytes = new TextEncoder().encode(str);
	const binary = Array.from(bytes, (byte) => String.fromCodePoint(byte)).join("");
	return btoa(binary);
}

/**
 * Builds the shared query string plus a stable cache-id digest for one search.
 * The digest uses the *encoded* term, so a raw search string never lands in an
 * axios-cache-interceptor id.
 */
function buildQuery(query: SearchV3Query): { params: string; digest: string } {
	const encoded = base64encode(query.terms);
	const parts: string[] = [`terms=${encodeURIComponent(encoded)}`];
	const digestParts: string[] = [encoded];

	if (query.albumId !== undefined && query.albumId !== "") {
		parts.push(`album_id=${encodeURIComponent(query.albumId)}`);
		digestParts.push(`a:${query.albumId}`);
	}
	// The backend requires `sorting_order` whenever `sorting_column` is sent,
	// so the two are only ever appended together.
	if (query.sortingColumn !== undefined && query.sortingOrder !== undefined) {
		parts.push(`sorting_column=${encodeURIComponent(query.sortingColumn)}`);
		parts.push(`sorting_order=${encodeURIComponent(query.sortingOrder)}`);
		digestParts.push(`s:${query.sortingColumn}:${query.sortingOrder}`);
	}

	return { params: `?${parts.join("&")}`, digest: digestParts.join("|") };
}

/**
 * Consumes Feature 069's four `GET /api/v3/Search/*` endpoints.
 *
 * Unlike `PhotoChildrenV3Service` there is **no** `getBuckets()`: search is the
 * one photo tier with no bucket level (spec.md NG1) — its result is one flat,
 * whole-scope list bounded by the `search_result_limit` cap, which the response
 * reports via `is_truncated`.
 */
const SearchV3Service = {
	getPhotos(query: SearchV3Query): Promise<AxiosResponse<SearchPhotoResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		const { params, digest } = buildQuery(query);
		return requester.get(`${Constants.getApiUrlV3()}Search/Photos${params}`, {
			data: {},
			id: `search_v3_photos_${digest}`,
		});
	},

	/**
	 * `photo_ids[]` is capped at 300 as input by the backend (422 above), so
	 * callers chunk. Ids are sorted into the cache id so the same set requested
	 * in a different order hits the same entry, matching the server's own key.
	 */
	getPhotoDetails(query: SearchV3Query, photoIds: string[]): Promise<AxiosResponse<PhotoDetailResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		const { params, digest } = buildQuery(query);
		const sortedIds = [...photoIds].sort();
		const idParams = sortedIds.map((id) => `photo_ids[]=${encodeURIComponent(id)}`).join("&");
		return requester.get(`${Constants.getApiUrlV3()}Search/Photos/details${params}&${idParams}`, {
			data: {},
			id: `search_v3_details_${digest}_${sortedIds.join(",")}`,
		});
	},

	getAlbums(query: SearchV3Query): Promise<AxiosResponse<SearchAlbumResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		const { params, digest } = buildQuery(query);
		return requester.get(`${Constants.getApiUrlV3()}Search/albums${params}`, {
			data: {},
			id: `search_v3_albums_${digest}`,
		});
	},

	getAlbumRights(query: SearchV3Query): Promise<AxiosResponse<SearchAlbumRightsResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		const { params, digest } = buildQuery(query);
		return requester.get(`${Constants.getApiUrlV3()}Search/albums/rights${params}`, {
			data: {},
			id: `search_v3_album_rights_${digest}`,
		});
	},
};

export default SearchV3Service;
