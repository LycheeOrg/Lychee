/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { type InjectionKey, type Ref, ref, shallowReactive } from "vue";

type PhotoUrlResource = App.Http.Resources.V3.PhotoUrlResource;

/** Direct URLs of one tile: `grid` for grid tiles, `list` for list rows. `null` loads through `/Asset`. */
export type TileUrls = { grid: string | null; list: string | null };

export type TileUrlFetcher = (photoIds: string[]) => Promise<PhotoUrlResource>;

export type TileUrlLoader = {
	/** Asks for the URLs of ids not yet resolved nor in flight, in chunks of {@link TILE_URL_BATCH_SIZE}. */
	request: (photoIds: string[]) => void;
	/**
	 * `undefined` while pending (the tile shows its placeholder), the tile's
	 * URLs once resolved, `null` once the loader is disabled (every tile
	 * loads through `/Asset`).
	 */
	urlsOf: (photoId: string) => TileUrls | null | undefined;
	/** Forgets every entry and re-enables the loader; call it when the view's photos are reloaded. */
	reset: () => void;
	/** Set by a non-empty response whose entries are all `null`: the viewer is protected. */
	disabled: Ref<boolean>;
};

/** Upper bound of `photo_ids[]` accepted by `GET /Albums/{album_id}/Photos/urls`. */
export const TILE_URL_BATCH_SIZE = 300;

const NO_URLS: TileUrls = { grid: null, list: null };

/**
 * Loads direct tile URLs (Feature 102, FR-102-06, ADR-102-01) for the photos
 * a grid renders. Each id is asked for at most once until {@link TileUrlLoader.reset}.
 */
export function createTileUrlLoader(fetcher: TileUrlFetcher): TileUrlLoader {
	const resolved = shallowReactive(new Map<string, TileUrls>());
	const inFlight = new Set<string>();
	const disabled = ref(false);
	let generation = 0;

	function settle(chunk: string[], response: PhotoUrlResource | null): void {
		chunk.forEach((id) => inFlight.delete(id));
		if (response !== null && isAllNull(response)) {
			disabled.value = true;
		}
		const byId = new Map<string, TileUrls>();
		response?.ids.forEach((id, i) => byId.set(id, { grid: response.small2x_urls[i] ?? null, list: response.thumb_urls[i] ?? null }));
		chunk.forEach((id) => resolved.set(id, byId.get(id) ?? NO_URLS));
	}

	function send(chunk: string[]): void {
		const sent = generation;
		chunk.forEach((id) => inFlight.add(id));
		fetcher(chunk)
			.then((response) => (sent === generation ? settle(chunk, response) : undefined))
			.catch(() => (sent === generation ? settle(chunk, null) : undefined));
	}

	function request(photoIds: string[]): void {
		if (disabled.value) {
			return;
		}
		const missing = [...new Set(photoIds)].filter((id) => !resolved.has(id) && !inFlight.has(id));
		for (let i = 0; i < missing.length; i += TILE_URL_BATCH_SIZE) {
			send(missing.slice(i, i + TILE_URL_BATCH_SIZE));
		}
	}

	function urlsOf(photoId: string): TileUrls | null | undefined {
		return disabled.value ? null : resolved.get(photoId);
	}

	function reset(): void {
		generation++;
		resolved.clear();
		inFlight.clear();
		disabled.value = false;
	}

	return { request, urlsOf, reset, disabled };
}

/** A non-empty response with no URL at all: the viewer needs `/Asset` for every tile. */
function isAllNull(response: PhotoUrlResource): boolean {
	return response.ids.length > 0 && response.small2x_urls.every((url) => url === null) && response.thumb_urls.every((url) => url === null);
}

/** Provided by views whose grid tiles may use direct URLs (album views, timeline). */
export const TILE_URL_LOADER: InjectionKey<TileUrlLoader> = Symbol("tileUrlLoader");
