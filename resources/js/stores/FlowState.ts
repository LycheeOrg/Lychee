import { defineStore } from "pinia";
import FlowService from "@/services/flow-service";
import PhotoChildrenV3Service, { type PhotoDetailResource } from "@/services/photo-children-v3-service";
import { adaptFlowTiles, type AdaptedFlowTile } from "@/v8/utils/adaptFlowTile";
import { adaptPhotoTile, mergePhotoDetail, type AdaptedPhotoTile } from "@/v8/utils/adaptPhotoTile";
import { useLycheeStateStore } from "@/stores/LycheeState";

export type FlowStateStore = ReturnType<typeof useFlowStateStore>;

// Feature 068 (Decision Card Q-068-07): fixed frontend constant, no config
// key — the album's full photo count remains visible via `numPhotos`
// regardless of this cap; opening the album itself shows every photo via
// the existing, unaffected per-album photo-listing path.
export const FLOW_CAROUSEL_PHOTO_LIMIT = 12;

type CardLoadState = "idle" | "loading" | "loaded" | "failed";

export const useFlowStateStore = defineStore("flow-store", {
	state: () => ({
		are_nsfw_blurred: false,
		are_nsfw_consented: false,

		// Feature 068 (FR-068-01): the whole-scope v3 listing, fetched once on
		// load - no pagination/cursor state to manage at all.
		flowV3: [] as AdaptedFlowTile[],
		flowV3Loaded: false,

		// Feature 068 (FR-068-04/FR-068-09): per-card photo preview, cached
		// centrally here (not per-card component-local state) so a card
		// derendered and re-rendered by the virtualizer reuses its
		// already-fetched data instead of re-fetching (S-068-15) - and a
		// loading-state map driving the skeleton (FR-068-09).
		cardPhotosV3: {} as Record<string, AdaptedPhotoTile[] | undefined>,
		cardLoadStateV3: {} as Record<string, CardLoadState | undefined>,

		// On-demand tier-3 (`details`) fetch cache, keyed by photo id - see
		// `loadPhotoDetailsV3()`. Unlike `AlbumState.ts`'s/`TimelineState.ts`'s
		// own boolean-only `photoDetailsResolvedIds`, this stores the actual
		// resolved detail payload (plus its row index into it): a Flow card's
		// tile isn't the single, stable object those two stores' own
		// `photosState.photos` array element is - `Flow.vue`'s direct-fetch
		// fallback (a photo id outside a card's capped preview) builds a
		// brand-new `adaptPhotoTile()` instance every time it's opened, so a
		// bare "already resolved" flag would permanently skip re-merging into
		// that fresh, still-placeholder-only instance on a second open.
		photoDetailsCacheV3: {} as Record<string, { detail: PhotoDetailResource; index: number } | undefined>,

		// Bug fix: bumped by resetV3(). `loadV3()`/`requestCardPhotos()` both
		// await a network request bracketed by an unconditional write to this
		// store's state - if Flow.vue unmounts (resetV3()) and remounts while
		// an old request is still in flight, that stale request's eventual
		// success/failure would otherwise overwrite whatever a fresh request
		// (started after the reset) already wrote, since resetV3()'s cleared
		// maps make the old request's own duplicate-request guard useless
		// (it sees an empty map, not "loading"/"loaded"). Both actions
		// capture this value before awaiting and re-check it before writing
		// their result, discarding it entirely if a reset happened meanwhile.
		generationV3: 0,
	}),
	getters: {
		isFlowSoaActive(): boolean {
			return useLycheeStateStore().is_struct_of_array_enabled;
		},
	},
	actions: {
		async loadV3(): Promise<"loaded" | "obsolete"> {
			const generation = this.generationV3;
			const response = await FlowService.getV3();
			if (generation !== this.generationV3) {
				// A reset happened while this request was in flight - discard
				// it entirely rather than overwriting whatever a subsequent
				// load (started after the reset) already wrote. Report this
				// distinctly from a genuinely empty result so the caller
				// (Flow.vue) doesn't mistake an obsolete/cancelled load for
				// "no albums" and redirect away.
				return "obsolete";
			}
			this.flowV3 = adaptFlowTiles(response.data);
			this.flowV3Loaded = true;
			return "loaded";
		},

		/**
		 * Fetches (once) a card's first `FLOW_CAROUSEL_PHOTO_LIMIT` photos.
		 * No-ops if already loading/loaded for this album id - the
		 * virtualizer may call this again for a card scrolling back into
		 * view, and this must never re-fetch (S-068-15).
		 */
		async requestCardPhotos(albumId: string): Promise<void> {
			const state = this.cardLoadStateV3[albumId];
			if (state === "loading" || state === "loaded") {
				return;
			}

			const generation = this.generationV3;
			this.cardLoadStateV3[albumId] = "loading";
			try {
				const response = await PhotoChildrenV3Service.getRatios(albumId, { limit: FLOW_CAROUSEL_PHOTO_LIMIT });
				if (generation !== this.generationV3) {
					// Obsolete generation (resetV3() ran while this request was
					// in flight) - the duplicate-request guard above can't
					// catch this on its own, since resetV3() already cleared
					// cardLoadStateV3 back to an empty map, making a fresh
					// request for the same album id look unguarded. Discard
					// this result rather than clobbering whatever the fresh
					// request already wrote.
					return;
				}
				const ratios = response.data;
				const tiles = ratios.ids.map((_, i) => adaptPhotoTile(i, ratios, albumId));
				// adaptPhotoTile() always sets next/previous_photo_id to null
				// (SoA-sourced tiles have no such link from the backend) -
				// link them by array position within this capped preview, the
				// same way PhotosState.ts.rebuildNavigationLinks() does for
				// the per-album photo listing.
				for (let i = 0; i < tiles.length; i++) {
					tiles[i].previous_photo_id = i > 0 ? tiles[i - 1].id : null;
					tiles[i].next_photo_id = i < tiles.length - 1 ? tiles[i + 1].id : null;
				}
				this.cardPhotosV3[albumId] = tiles;
				this.cardLoadStateV3[albumId] = "loaded";
			} catch {
				if (generation !== this.generationV3) {
					return;
				}
				this.cardPhotosV3[albumId] = [];
				this.cardLoadStateV3[albumId] = "failed";
			}
		},

		/**
		 * On-demand tier-3 (`details`) fetch (G5) for a card's lightbox
		 * selection - mirrors `AlbumState.ts`'s/`TimelineState.ts`'s own
		 * `loadPhotoDetails()` (same dedup-by-id, same in-place
		 * `mergePhotoDetail()` merge, same 300-id chunking), called by
		 * `Flow.vue`'s `openSelectionV3()` since a `cardPhotosV3` tile is
		 * only ever `ratios`-derived (never merged with `details` the way
		 * an opened album's own photo listing is) - without this, the
		 * lightbox has no real image URL to render. `tiles` is passed in
		 * (rather than looked up from `cardPhotosV3`) so this works
		 * uniformly whether the tile lives there or is a one-off fetched
		 * outside the card's capped preview (`Flow.vue`'s own direct-fetch
		 * fallback for a photo id outside it, e.g. an explicit cover) -
		 * either way, mutating the exact object `photoStore.photo` already
		 * references updates it in place with zero extra wiring, the same
		 * way `AlbumState.ts`'s/`TimelineState.ts`'s in-place merges do for
		 * their own already-referenced `photosState.photos` elements. Each
		 * Flow card is its own distinct album, so the request is scoped per
		 * `albumId` rather than to one shared photo listing.
		 */
		async loadPhotoDetailsV3(albumId: string, tiles: AdaptedPhotoTile[]): Promise<void> {
			const idsToFetch: string[] = [];
			for (const tile of tiles) {
				const cached = this.photoDetailsCacheV3[tile.id];
				if (cached !== undefined) {
					// Already resolved by a previous call - merge the cached
					// payload straight into this tile rather than re-fetching.
					// `tile` may be a brand-new object even for an already-
					// resolved id (Flow.vue's direct-fetch fallback builds a
					// fresh `adaptPhotoTile()` instance every time a photo
					// outside a card's capped preview is reopened), so this
					// merge can't be skipped just because the id is resolved.
					mergePhotoDetail(tile, cached.detail, cached.index);
				} else if (!idsToFetch.includes(tile.id)) {
					idsToFetch.push(tile.id);
				}
			}
			if (idsToFetch.length === 0) {
				return;
			}

			const generation = this.generationV3;
			const CHUNK_SIZE = 300;
			const chunks: string[][] = [];
			for (let i = 0; i < idsToFetch.length; i += CHUNK_SIZE) {
				chunks.push(idsToFetch.slice(i, i + CHUNK_SIZE));
			}

			try {
				const responses = await Promise.all(chunks.map((chunk) => PhotoChildrenV3Service.getDetails(albumId, { photoIds: chunk })));
				if (generation !== this.generationV3) {
					return;
				}

				for (const response of responses) {
					const detail = response.data;
					for (let i = 0; i < detail.ids.length; i++) {
						const id = detail.ids[i];
						// Cache the payload (by id, before marking it resolved
						// is even a separate step - the cache entry's presence
						// *is* "resolved") so a tile rebuilt later for this same
						// id - see the comment above - can still be merged.
						this.photoDetailsCacheV3[id] = { detail, index: i };
						const photo = tiles.find((p) => p.id === id);
						if (photo !== undefined) {
							mergePhotoDetail(photo, detail, i);
						}
					}
				}
			} catch (error) {
				console.error(error);
			}
		},

		resetV3(): void {
			this.generationV3++;
			this.flowV3 = [];
			this.flowV3Loaded = false;
			this.cardPhotosV3 = {};
			this.cardLoadStateV3 = {};
			this.photoDetailsCacheV3 = {};
		},
	},
});
