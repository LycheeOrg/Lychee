import { defineStore } from "pinia";
import { useLycheeStateStore } from "./LycheeState";
import MapV3Service, {
	type MapBucketResource,
	type MapPhotoResource,
	type MapTrackResource,
	type MapViewportParams,
} from "@/services/map-v3-service";

export type MapBounds = { north: number; south: number; east: number; west: number };

/** Mirrors `App\DTO\MapViewport::cellSizeForZoom()` (Q-067-13, amended) exactly - see its own doc comment for why the extra `GRID_ZOOM_OFFSET`. */
const GRID_ZOOM_OFFSET = 6;

function cellSizeForZoom(zoom: number): number {
	return 360.0 / Math.pow(2, zoom + GRID_ZOOM_OFFSET);
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

/**
 * True when `inner` is fully covered by `outer` on every side. Antimeridian
 * crossing (`west > east`) is rare enough for a map viewport that skipping
 * the optimization this backs entirely for either box (always treat as "not
 * contained") is simpler and safer than getting the wraparound math right.
 */
function boundsContain(outer: MapBounds, inner: MapBounds): boolean {
	if (outer.west > outer.east || inner.west > inner.east) {
		return false;
	}

	return outer.north >= inner.north && outer.south <= inner.south && outer.east >= inner.east && outer.west <= inner.west;
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
		/**
		 * Real (unsnapped) bounds of the last viewport whose `/Map/Photos`
		 * fetch was exhaustive - every distinct photo in that bbox is
		 * already sitting in `photosV3` (i.e. it came back under
		 * `MAX_VIEWPORT_PHOTOS`, not an aggregate-mode/empty response).
		 * `undefined` when the last fetch wasn't exhaustive, or none has
		 * happened yet.
		 */
		lastExhaustiveBoundsV3: undefined as MapBounds | undefined,
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
			this.lastExhaustiveBoundsV3 = undefined;
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
		 *
		 * Sequential, not `Promise.all`-parallel (Q-067-16, amended): under
		 * `MAX_VIEWPORT_PHOTOS`, `Map.vue`'s `renderAggregateMarkers()` never
		 * renders `bucketsV3` at all (`renderIndividualPhotos()` owns the
		 * view) - fetching buckets/counts in parallel every time paid for a
		 * whole extra backend query and response on every viewport change
		 * whose result was then simply discarded, in what's usually the
		 * *common* case (owner: "what is the point of bucket and counts if
		 * it is not used?"). `/Map/buckets` is now only requested once
		 * `/Map/Photos` has already confirmed the viewport is over the cap.
		 *
		 * Also skips the request entirely (regardless of the snapped-key
		 * dedup below) when `bounds` is fully contained in
		 * `lastExhaustiveBoundsV3` - every photo a smaller/equal viewport
		 * inside an already-exhaustively-fetched area could ask for is
		 * already in `photosV3`; Leaflet simply won't draw the ones now
		 * off-screen. Otherwise every zoom-in step re-queries the backend
		 * for data it's already holding (owner: "if we already have all the
		 * pictures in the current zoom selection, it does not make sense to
		 * requery again as we zoom in").
		 */
		async fetchViewportNow(bounds: MapBounds, zoom: number): Promise<void> {
			if (this.lastExhaustiveBoundsV3 !== undefined && boundsContain(this.lastExhaustiveBoundsV3, bounds)) {
				return;
			}

			const key = snappedRequestKey(bounds, zoom, this.albumId);
			if (key === this.lastRequestedKeyV3) {
				return;
			}
			this.lastRequestedKeyV3 = key;
			this.isLoadingV3 = true;

			const viewport: MapViewportParams = { north: bounds.north, south: bounds.south, east: bounds.east, west: bounds.west, zoom };
			const albumId = this.albumId ?? undefined;

			try {
				const photosResponse = await MapV3Service.getPhotos(viewport, albumId);
				// A newer viewport request can resolve first and move
				// `lastRequestedKeyV3` on while this one is still in flight
				// (e.g. a fast pan). If that happened, this response is stale -
				// discard it instead of clobbering the newer viewport's data.
				if (key !== this.lastRequestedKeyV3) {
					return;
				}

				if (photosResponse.data.ids.length > 0) {
					this.photosV3 = photosResponse.data;
					this.bucketsV3 = undefined;
					this.lastExhaustiveBoundsV3 = bounds;
					return;
				}

				// Over the cap (or the viewport is genuinely empty) - only now
				// is the aggregate view actually needed. Either way this
				// response wasn't exhaustive, so a later, larger-than-this
				// viewport must not skip its own fetch based on it.
				this.lastExhaustiveBoundsV3 = undefined;
				const bucketsResponse = await MapV3Service.getBuckets(viewport, albumId);
				if (key !== this.lastRequestedKeyV3) {
					return;
				}
				this.photosV3 = photosResponse.data;
				this.bucketsV3 = bucketsResponse.data;
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
			this.lastExhaustiveBoundsV3 = undefined;
		},
	},
});
