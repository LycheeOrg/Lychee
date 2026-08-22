import axios from "axios";
import Constants from "./constants";

type CacheEntry = {
	promise: Promise<string>;
	controller: AbortController;
	refCount: number;
	settled: boolean;
};

const cache = new Map<string, CacheEntry>();

function cacheKey(albumId: string, photoId: string, type: App.Enum.SizeVariantAssetType): string {
	return `${albumId}:${photoId}:${type}`;
}

const ThumbAssetService = {
	/**
	 * Resolves `GET /api/v3/Asset/{albumId}/{photoId}/{type}` to an object URL, de-duplicating
	 * concurrent/repeated callers for the same `(albumId, photoId, type)` onto one request and
	 * one cached object URL.
	 *
	 * Returns a `release()` alongside the promise instead of taking the caller's own
	 * `AbortSignal` directly: several `<Thumb>` instances can be simultaneously waiting on the
	 * same still-in-flight entry, so only aborting once every caller has released it (and only
	 * while it hasn't resolved yet) avoids one unmounting instance cancelling the request out
	 * from under a sibling that is still mounted. A resolved entry is never evicted by
	 * `release()` — it stays cached for the rest of the session (no eviction policy).
	 */
	acquire(albumId: string, photoId: string, type: App.Enum.SizeVariantAssetType): { promise: Promise<string>; release: () => void } {
		const key = cacheKey(albumId, photoId, type);
		let entry = cache.get(key);

		if (entry === undefined) {
			const controller = new AbortController();
			const promise = axios
				.get(`${Constants.getApiUrlV3()}Asset/${albumId}/${photoId}/${type}`, {
					responseType: "blob",
					signal: controller.signal,
					data: {},
				})
				.then((response) => {
					if (entry !== undefined) {
						entry.settled = true;
					}
					return URL.createObjectURL(response.data as Blob);
				})
				.catch((error: unknown) => {
					if (cache.get(key) === entry) {
						cache.delete(key);
					}
					throw error;
				});

			entry = { promise, controller, refCount: 0, settled: false };
			cache.set(key, entry);
		}

		entry.refCount++;
		const acquired = entry;

		return {
			promise: acquired.promise,
			release: () => {
				acquired.refCount--;
				if (acquired.refCount <= 0 && !acquired.settled && cache.get(key) === acquired) {
					acquired.controller.abort();
					cache.delete(key);
				}
			},
		};
	},
};

export default ThumbAssetService;
