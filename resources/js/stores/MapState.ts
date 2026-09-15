import { defineStore } from "pinia";
import { useLycheeStateStore } from "./LycheeState";
import MapV3Service, {
	type MapBucketResource,
	type MapPhotoResource,
	type MapTrackResource,
	type MapViewportParams,
} from "@/services/map-v3-service";

export type MapBounds = { north: number; south: number; east: number; west: number };

/** Mirrors `App\DTO\MapViewport::cellSizeForZoom()` (Q-067-13) exactly. */
function cellSizeForZoom(zoom: number): number {
	return 360.0 / Math.pow(2, zoom);
}

function snapUp(value: number, cell: number): number {
	return Math.ceil(Math.round((value / cell) * 1e9) / 1e9) * cell;
}

function snapDown(value: number, cell: number): number {
	return Math.floor(Math.round((value / cell) * 1e9) / 1e9) * cell;
}

/**
 * Client-side mirror of `App\DTO\MapViewport::snapToGrid()`, used only to
 * compute a stable dedup key — the server independently (and
 * authoritatively) re-snaps on every request, so this never needs to be
 * pixel-perfect, only consistent for identical input.
 */
function snappedRequestKey(bounds: MapBounds, zoom: number, albumId: string | null): string {
	const cell = cellSizeForZoom(zoom);
	const north = snapUp(bounds.north, cell);
	const south = snapDown(bounds.south, cell);
	const east = snapUp(bounds.east, cell);
	const west = snapDown(bounds.west, cell);

	return `${albumId ?? "root"}:z:${zoom}:n:${north}:s:${south}:e:${east}:w:${west}`;
}

const DEBOUNCE_MS = 300;
let debounceTimer: ReturnType<typeof setTimeout> | undefined;

export type MapStore = ReturnType<typeof useMapStore>;

/**
 * Feature 067's v3 (SoA) Map state: `bucketsV3`/`photosV3`/`tracksV3`,
 * gated behind `isMapSoaActive` (the same `is_struct_of_array_enabled` flag
 * every other SoA store reads), with a debounced, deduped `requestViewport()`
 * driven by `Map.vue`'s Leaflet `moveend`/`zoomend` listeners (FR-067-18,
 * FR-067-19).
 */
export const useMapStore = defineStore("map-store", {
	state: () => ({
		albumId: null as string | null,
		bucketsV3: undefined as MapBucketResource | undefined,
		photosV3: undefined as MapPhotoResource | undefined,
		tracksV3: [] as MapTrackResource[],
		isLoadingV3: false,
		/** The last snapped-viewport key requested (in flight or already resolved) — `requestViewport()`'s own dedup guard. */
		lastRequestedKeyV3: undefined as string | undefined,
	}),
	getters: {
		isMapSoaActive(): boolean {
			const lycheeStore = useLycheeStateStore();

			return lycheeStore.is_struct_of_array_enabled;
		},
	},
	actions: {
		/**
		 * Sets the current album scope (`null` for root) - resets the dedup
		 * guard so a scope change always triggers a fresh fetch even if the
		 * viewport itself is unchanged.
		 */
		setAlbumId(albumId: string | null): void {
			if (this.albumId === albumId) {
				return;
			}
			this.albumId = albumId;
			this.lastRequestedKeyV3 = undefined;
		},

		/**
		 * Debounced viewport request: coalesces rapid-fire pan/zoom events
		 * into a single fetch once the map settles, and skips a fetch
		 * entirely when the resulting snapped viewport is identical to the
		 * last one requested (in flight or already resolved) — no duplicate
		 * network calls for an already-loading/identical snapped viewport.
		 */
		requestViewport(bounds: MapBounds, zoom: number): void {
			if (debounceTimer !== undefined) {
				clearTimeout(debounceTimer);
			}

			debounceTimer = setTimeout(() => {
				void this.fetchViewportNow(bounds, zoom);
			}, DEBOUNCE_MS);
		},

		/**
		 * Immediate (non-debounced) fetch — exposed separately so tests and
		 * `requestViewport()`'s own debounced timer share one implementation.
		 */
		async fetchViewportNow(bounds: MapBounds, zoom: number): Promise<void> {
			const key = snappedRequestKey(bounds, zoom, this.albumId);
			if (key === this.lastRequestedKeyV3) {
				return;
			}
			this.lastRequestedKeyV3 = key;
			this.isLoadingV3 = true;

			const viewport: MapViewportParams = { north: bounds.north, south: bounds.south, east: bounds.east, west: bounds.west, zoom };
			const albumId = this.albumId ?? undefined;

			try {
				const [bucketsResponse, photosResponse] = await Promise.all([
					MapV3Service.getBuckets(viewport, albumId),
					MapV3Service.getPhotos(viewport, albumId),
				]);
				// A newer viewport request can resolve first and move
				// `lastRequestedKeyV3` on while this one is still in flight
				// (e.g. a fast pan). If that happened, this response is stale -
				// discard it instead of clobbering the newer viewport's data.
				if (key !== this.lastRequestedKeyV3) {
					return;
				}
				this.bucketsV3 = bucketsResponse.data;
				this.photosV3 = photosResponse.data;
			} catch (error) {
				if (key === this.lastRequestedKeyV3) {
					// Allow a retry of the same viewport on the next pan/zoom event.
					this.lastRequestedKeyV3 = undefined;
				}
				throw error;
			} finally {
				// Only the request that is still current clears the loading
				// flag - otherwise a stale request's `finally` could flip it
				// off while the superseding request is still in flight.
				if (key === this.lastRequestedKeyV3) {
					this.isLoadingV3 = false;
				}
			}
		},

		/**
		 * Fetched once per album context, independent of viewport changes
		 * (FR-067-22) — `Map.vue` calls this once, not from
		 * `requestViewport()`.
		 */
		async loadTracks(): Promise<void> {
			if (this.albumId === null) {
				this.tracksV3 = [];

				return;
			}
			const response = await MapV3Service.getTracks(this.albumId);
			this.tracksV3 = response.data;
		},

		reset(): void {
			if (debounceTimer !== undefined) {
				clearTimeout(debounceTimer);
				debounceTimer = undefined;
			}
			this.albumId = null;
			this.bucketsV3 = undefined;
			this.photosV3 = undefined;
			this.tracksV3 = [];
			this.isLoadingV3 = false;
			this.lastRequestedKeyV3 = undefined;
		},
	},
});
