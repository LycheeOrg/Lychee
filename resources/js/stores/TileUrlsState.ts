import { defineStore } from "pinia";
import PhotoChildrenV3Service from "@/services/photo-children-v3-service";
import { screenDensity } from "@/v8/utils/screenDensity";
import { isAllNull, NO_TILE_URLS, TILE_URL_BATCH_SIZE, tileUrlsById, type TileUrls } from "@/v8/composables/photo/tileUrlLoader";

/**
 * Ids asked for and not answered yet. Module-scoped (not store state) since
 * it's pure async bookkeeping, never read by a template.
 */
const inFlight = new Set<string>();

/** Bumped by every viewer change: responses asked for by the previous viewer are dropped. */
let viewerEpoch = 0;

export type TileUrlsStore = ReturnType<typeof useTileUrlsStore>;

/**
 * Direct tile URLs (Feature 102, FR-102-06, ADR-102-01), keyed by photo id.
 * A photo's file URL is the same in every album and in the timeline for one
 * viewer, so entries live for the page's lifetime and only a viewer change
 * empties them. A URL gone stale fails to load and `Thumb` falls back to `/Asset`.
 */
export const useTileUrlsStore = defineStore("tile-urls-store", {
	state: () => ({
		/** `{album_id}` of `GET /Albums/{album_id}/Photos/urls`; `undefined`: no direct URLs, every tile uses `/Asset`. */
		source: undefined as string | undefined,
		/** Id of the logged-in user, `null` for a guest. */
		viewer: null as number | null,
		resolved: new Map<string, TileUrls>(),
		/** Ids the source left out or failed on: they use `/Asset` until the source changes. */
		missed: new Set<string>(),
		/** Set by a non-empty response whose entries are all `null`: the viewer is protected. */
		disabled: false,
		/** Bumped by every source or viewer change, so a grid asks again for ids it already rendered. */
		generation: 0,
	}),
	getters: {
		/**
		 * `undefined` while pending (the tile shows its placeholder), the tile's
		 * URLs once resolved, `null` without a source or once disabled.
		 */
		urlsOf(state): (photoId: string) => TileUrls | null | undefined {
			return (photoId) => {
				if (state.source === undefined || state.disabled) {
					return null;
				}
				return state.resolved.get(photoId) ?? (state.missed.has(photoId) ? NO_TILE_URLS : undefined);
			};
		},
	},
	actions: {
		setSource(source: string | undefined): void {
			if (source === this.source) {
				return;
			}
			this.source = source;
			this.startOver();
		},

		setViewer(viewer: number | null): void {
			if (viewer === this.viewer) {
				return;
			}
			this.viewer = viewer;
			viewerEpoch++;
			this.resolved.clear();
			this.startOver();
		},

		/** Asks the source for the URLs of ids not yet known nor in flight, in chunks of {@link TILE_URL_BATCH_SIZE}. */
		request(photoIds: string[]): void {
			if (this.source === undefined || this.disabled) {
				return;
			}
			const missing = [...new Set(photoIds)].filter((id) => !this.resolved.has(id) && !this.missed.has(id) && !inFlight.has(id));
			for (let i = 0; i < missing.length; i += TILE_URL_BATCH_SIZE) {
				this.send(this.source, missing.slice(i, i + TILE_URL_BATCH_SIZE));
			}
		},

		send(source: string, chunk: string[]): void {
			const generation = this.generation;
			const epoch = viewerEpoch;
			chunk.forEach((id) => inFlight.add(id));
			PhotoChildrenV3Service.getUrls(source, chunk, screenDensity())
				.then((response) => (epoch === viewerEpoch ? this.settle(chunk, response.data, generation) : undefined))
				.catch(() => (epoch === viewerEpoch ? this.settle(chunk, null, generation) : undefined));
		},

		/** URLs are kept whatever the source is now; misses and the protected flag only for the source that asked. */
		settle(chunk: string[], response: App.Http.Resources.V3.PhotoUrlResource | null, generation: number): void {
			if (response !== null) {
				tileUrlsById(response).forEach((urls, id) => this.resolved.set(id, urls));
			}
			if (generation !== this.generation) {
				return;
			}
			chunk.forEach((id) => inFlight.delete(id));
			chunk.filter((id) => !this.resolved.has(id)).forEach((id) => this.missed.add(id));
			if (response !== null && isAllNull(response)) {
				this.disabled = true;
			}
		},

		startOver(): void {
			this.generation++;
			this.missed.clear();
			inFlight.clear();
			this.disabled = false;
		},
	},
});
