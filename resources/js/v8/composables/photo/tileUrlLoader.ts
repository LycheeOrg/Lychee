/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { RouteLocationNormalizedLoaded } from "vue-router";

type PhotoUrlResource = App.Http.Resources.V3.PhotoUrlResource;

/** Direct URLs of one tile: `grid` for grid tiles, `list` for list rows. `null` loads through `/Asset`. */
export type TileUrls = { grid: string | null; list: string | null };

/** Upper bound of `photo_ids[]` accepted by `GET /Albums/{album_id}/Photos/urls`. */
export const TILE_URL_BATCH_SIZE = 300;

export const NO_TILE_URLS: TileUrls = { grid: null, list: null };

/**
 * The `{album_id}` the tiles of `route` ask direct URLs from (Feature 102,
 * FR-102-06): the album of the album routes, `timeline` on the timeline,
 * none elsewhere (search and every other grid use `/Asset`).
 */
export function tileUrlSource(route: Pick<RouteLocationNormalizedLoaded, "name" | "params">): string | undefined {
	if (route.name === "timeline") {
		return "timeline";
	}
	const album_id = route.params.albumId;
	if ((route.name === "album" || route.name === "flow-album") && typeof album_id === "string" && album_id !== "") {
		return album_id;
	}
	return undefined;
}

/** The URLs of each id `response` answers. */
export function tileUrlsById(response: PhotoUrlResource): Map<string, TileUrls> {
	return new Map(response.ids.map((id, i) => [id, { grid: response.grid_urls[i] ?? null, list: response.thumb_urls[i] ?? null }]));
}

/** A non-empty response with no URL at all: the viewer needs `/Asset` for every tile. */
export function isAllNull(response: PhotoUrlResource): boolean {
	return response.ids.length > 0 && response.grid_urls.every((url) => url === null) && response.thumb_urls.every((url) => url === null);
}
