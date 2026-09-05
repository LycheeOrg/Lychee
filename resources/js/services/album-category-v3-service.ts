import axios, { type AxiosResponse } from "axios";
import { AxiosCacheInstance } from "axios-cache-interceptor";
import Constants from "./constants";

/**
 * Consumes Feature 062's five root/category listing endpoints — the full
 * root-gallery SoA adoption (2026-09-02 addendum, extended beyond the
 * original `/Albums/smart`-only scope per user direction: "the point of the
 * extension is to support Struct-of-Array on the root gallery page"):
 * `/Albums/smart` (flat), `/Albums/tags` + `/tags/rights` (flat, with
 * rights), `/Albums/persons?scope=` (flat, own+shared merged client-side —
 * no single combined-scope route exists), `/Albums/pinned?scope=` (flat,
 * same merge), and `/Albums/root[/buckets|/rights]?scope=` (bucketed, the
 * same tier1/2/3 shape Feature 061/063 already established for sub-album
 * children — `AlbumRootController` literally reuses `AlbumBucketResource`/
 * `AlbumDataResource`/`AlbumRightsResource`). Cached the
 * same way `AlbumChildrenV3Service` already is; invalidated by
 * `AlbumService.clearAlbums()`, the same call site every other
 * root-listing cache entry already relies on.
 */
const AlbumCategoryV3Service = {
	getSmart(): Promise<AxiosResponse<App.Http.Resources.V3.AlbumCategoryResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/smart`, {
			data: {},
			id: "albums_v3_smart",
		});
	},

	getTags(): Promise<AxiosResponse<App.Http.Resources.V3.AlbumCategoryResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/tags`, {
			data: {},
			id: "albums_v3_tags",
		});
	},

	getTagsRights(): Promise<AxiosResponse<App.Http.Resources.V3.AlbumCategoryRightsResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/tags/rights`, {
			data: {},
			id: "albums_v3_tags_rights",
		});
	},

	getPersons(scope: App.Enum.AlbumListingScope): Promise<AxiosResponse<App.Http.Resources.V3.AlbumCategoryResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/persons`, {
			params: { scope },
			data: {},
			id: `albums_v3_persons_${scope}`,
		});
	},

	getPinned(scope: App.Enum.AlbumListingScope): Promise<AxiosResponse<App.Http.Resources.V3.AlbumCategoryResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/pinned`, {
			params: { scope },
			data: {},
			id: `albums_v3_pinned_${scope}`,
		});
	},

	getRootBuckets(scope: App.Enum.AlbumListingScope): Promise<AxiosResponse<App.Http.Resources.V3.AlbumBucketResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/root/buckets`, {
			params: { scope },
			data: {},
			id: `albums_v3_root_buckets_${scope}`,
		});
	},

	getRootChildren(scope: App.Enum.AlbumListingScope): Promise<AxiosResponse<App.Http.Resources.V3.AlbumDataResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/root`, {
			params: { scope },
			data: {},
			id: `albums_v3_root_${scope}`,
		});
	},

	getRootRights(scope: App.Enum.AlbumListingScope): Promise<AxiosResponse<App.Http.Resources.V3.AlbumRightsResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		return requester.get(`${Constants.getApiUrlV3()}Albums/root/rights`, {
			params: { scope },
			data: {},
			id: `albums_v3_root_rights_${scope}`,
		});
	},
};

export default AlbumCategoryV3Service;
