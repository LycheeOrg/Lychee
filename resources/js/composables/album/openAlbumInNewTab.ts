/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import type { Router } from "vue-router";
import { useAlbumRoute } from "@/composables/photo/albumRoute";

/**
 * URL of an album for the view the router is on: the route the album's own
 * tile links to (`album`, `flow-album` or `tag`, as `useAlbumRoute` picks it).
 */
export function albumHref(router: Router, albumId: string): string {
	const { albumRoutes } = useAlbumRoute(router);
	return router.resolve({ name: albumRoutes().album, params: { albumId: albumId } }).href;
}

/**
 * Opens a URL in a new browser tab. `noopener` on its own is the one window
 * feature that still requests a tab rather than a popup window, and it cuts
 * the new tab's `window.opener` link.
 */
export function openInNewTab(href: string): void {
	window.open(href, "_blank", "noopener");
}
