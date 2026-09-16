import axios, { type AxiosResponse } from "axios";
import { AxiosCacheInstance } from "axios-cache-interceptor";
import Constants from "./constants";

export type MapBucketResource = App.Http.Resources.V3.MapBucketResource;
export type MapPhotoResource = App.Http.Resources.V3.MapPhotoResource;
export type MapTrackResource = App.Http.Resources.Models.TrackResource;

export type MapViewportParams = {
	north: number;
	south: number;
	east: number;
	west: number;
	zoom: number;
};

function viewportQuery(viewport: MapViewportParams, albumId?: string): string {
	const parts = [`north=${viewport.north}`, `south=${viewport.south}`, `east=${viewport.east}`, `west=${viewport.west}`, `zoom=${viewport.zoom}`];
	if (albumId !== undefined) {
		parts.push(`album_id=${encodeURIComponent(albumId)}`);
	}

	return parts.join("&");
}

/**
 * Consumes Feature 067's three `GET /api/v3/Map/...` endpoints. Cached the
 * same way `PhotoChildrenV3Service`'s calls are — via
 * `axios-cache-interceptor`'s enumerable `id`s.
 */
const MapV3Service = {
	getBuckets(viewport: MapViewportParams, albumId?: string): Promise<AxiosResponse<MapBucketResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		const query = viewportQuery(viewport, albumId);

		return requester.get(`${Constants.getApiUrlV3()}Map/buckets?${query}`, {
			data: {},
			id: `map_v3_buckets_${query}`,
		});
	},

	getPhotos(viewport: MapViewportParams, albumId?: string): Promise<AxiosResponse<MapPhotoResource>> {
		const requester = axios as unknown as AxiosCacheInstance;
		const query = viewportQuery(viewport, albumId);

		return requester.get(`${Constants.getApiUrlV3()}Map/Photos?${query}`, {
			data: {},
			id: `map_v3_photos_${query}`,
		});
	},

	/**
	 * Viewport-independent (FR-067-13) — the album's track list, fetched
	 * once per album context.
	 */
	getTracks(albumId: string): Promise<AxiosResponse<MapTrackResource[]>> {
		const requester = axios as unknown as AxiosCacheInstance;

		return requester.get(`${Constants.getApiUrlV3()}Map/tracks?album_id=${encodeURIComponent(albumId)}`, {
			data: {},
			id: `map_v3_tracks_${albumId}`,
		});
	},
};

export default MapV3Service;
