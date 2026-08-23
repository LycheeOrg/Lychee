import axios from "axios";
import Constants from "./constants";

type CacheEntry = {
	promise: Promise<string>;
	controller: AbortController;
	refCount: number;
	settled: boolean;
	// Set once `settled`; refreshed on every cache hit so idle (not just old) entries expire.
	expiresAt: number | undefined;
};

const cache = new Map<string, CacheEntry>();

// Keeps memory bounded for long browsing sessions: each cached entry pins one
// `URL.createObjectURL()` blob. Both are deliberately generous since eviction only ever
// targets entries with `refCount <= 0` (nothing currently rendering them).
const MAX_CACHE_SIZE = 6000; // ~6000 * 4 KiB = ~24 MiB, plus some overhead for the `Map` and `CacheEntry` objects
const TTL_MS = 60 * 60 * 1000; // 1 hour, long enough to cover a typical browsing session but not so long that a user can leave the tab open for days and accumulate a huge memory footprint.

function cacheKey(albumId: string, photoId: string, type: App.Enum.SizeVariantAssetType): string {
	return `${albumId}:${photoId}:${type}`;
}

// Revokes the object URL (once resolved) and drops the entry. Only call this for entries
// with `refCount <= 0` — revoking a URL still in use would break whatever is rendering it.
function evict(key: string, entry: CacheEntry): void {
	cache.delete(key);
	if (entry.settled) {
		void entry.promise.then((url) => URL.revokeObjectURL(url));
	}
}

function sweepExpired(now: number): void {
	for (const [key, entry] of cache) {
		if (entry.refCount <= 0 && entry.expiresAt !== undefined && entry.expiresAt <= now) {
			evict(key, entry);
		}
	}
}

// `cache` iterates in insertion order, and cache hits re-insert their entry (see `acquire`),
// so the front of the map is always the least-recently-used entry: plain LRU via `Map`.
function enforceCacheLimit(): void {
	if (cache.size <= MAX_CACHE_SIZE) {
		return;
	}
	for (const [key, entry] of cache) {
		if (cache.size <= MAX_CACHE_SIZE) {
			break;
		}
		if (entry.refCount <= 0) {
			evict(key, entry);
		}
	}
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
	 * from under a sibling that is still mounted. A resolved entry is not evicted by `release()`
	 * itself, but stays subject to the module's bounded LRU + TTL eviction (`URL.revokeObjectURL()`
	 * is called when an entry is evicted) once nothing is holding it (`refCount <= 0`).
	 */
	acquire(albumId: string, photoId: string, type: App.Enum.SizeVariantAssetType): { promise: Promise<string>; release: () => void } {
		const key = cacheKey(albumId, photoId, type);
		const now = Date.now();
		sweepExpired(now);

		let entry = cache.get(key);

		if (entry !== undefined) {
			// Bump recency for LRU, and slide the TTL forward since it's being reused.
			cache.delete(key);
			if (entry.settled) {
				entry.expiresAt = now + TTL_MS;
			}
			cache.set(key, entry);
		} else {
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
						entry.expiresAt = Date.now() + TTL_MS;
					}
					return URL.createObjectURL(response.data as Blob);
				})
				.catch((error: unknown) => {
					if (cache.get(key) === entry) {
						cache.delete(key);
					}
					throw error;
				});

			entry = { promise, controller, refCount: 0, settled: false, expiresAt: undefined };
			cache.set(key, entry);
			enforceCacheLimit();
		}

		entry.refCount++;
		const acquired = entry;

		return {
			promise: acquired.promise,
			release: () => {
				acquired.refCount--;
				if (acquired.refCount <= 0 && cache.get(key) === acquired) {
					if (!acquired.settled) {
						acquired.controller.abort();
						cache.delete(key);
					} else if (acquired.expiresAt !== undefined && acquired.expiresAt <= Date.now()) {
						evict(key, acquired);
					}
				}
			},
		};
	},
};

export default ThumbAssetService;
